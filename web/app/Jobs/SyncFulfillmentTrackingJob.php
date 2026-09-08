<?php

namespace App\Jobs;

use App\Http\Controllers\SyncController;
use App\Models\Fulfillment;
use App\Models\Plan;
use App\Models\Session;
use App\Support\ShopSyncLock;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

/**
 * Phase 3 after images: Cargo / Track123 pull in batches inside one job.
 * Only re-queues a continuation if the time budget runs out.
 */
class SyncFulfillmentTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Must stay below database queue retry_after (3600) and cron --max-time (3500).
     */
    public $timeout = 2400;
    public $tries = 1;

    public $shop;
    public $chunkSize;
    /** Cursor: process fulfillments with id > afterId. */
    public $afterId;
    public $wave;
    public $specificDate;

    public function __construct(
        string $shop,
        int $chunkSize = 15,
        int $afterId = 0,
        int $wave = 1,
        $specificDate = 'Last 90 days'
    ) {
        $this->shop = $shop;
        $this->chunkSize = $chunkSize;
        $this->afterId = $afterId;
        $this->wave = $wave;
        $this->specificDate = $specificDate;
    }

    public function handle()
    {
        $startedAt = microtime(true);
        $timeBudgetSec = max(60, $this->timeout - 120);
        $cursor = (int) $this->afterId;

        try {
            // Fresh phase-3 start only — continuations (wave>1 / afterId>0) keep going.
            if ($this->wave === 1 && $cursor === 0) {
                $acquiredPipelineLock = false;
                if (!ShopSyncLock::isLocked($this->shop)) {
                    if (!ShopSyncLock::acquire($this->shop, [
                        'job' => self::class,
                        'wave' => $this->wave,
                        'specificDate' => $this->specificDate,
                    ])) {
                        return;
                    }
                    $acquiredPipelineLock = true;
                }

                $activeKey = 'sync_tracking_active:' . md5($this->shop);
                if (!Cache::add($activeKey, 1, now()->addHours(6))) {
                    // Only release if *this* job took the pipeline lock (manual start race).
                    if ($acquiredPipelineLock) {
                        ShopSyncLock::release($this->shop, 'tracking_active_race');
                    }
                    return;
                }
                // Fresh chain: drop stale continuation locks from an earlier aborted run.
                Cache::forget($this->continuationChainKey());
            } else {
                // Continuations (and mid-deploy reclaim): keep / take pipeline lock + active flag.
                if (!ShopSyncLock::isLocked($this->shop)) {
                    ShopSyncLock::acquire($this->shop, [
                        'job' => self::class,
                        'wave' => $this->wave,
                        'afterId' => $cursor,
                        'reclaim' => true,
                    ]);
                } else {
                    ShopSyncLock::touch($this->shop);
                }
                Cache::add('sync_tracking_active:' . md5($this->shop), 1, now()->addHours(6));
            }

            $session = Session::where('shop', $this->shop)->first();
            if (!$session || !$session->plan_id) {
                ShopSyncLock::release($this->shop, 'tracking_no_session');
                Cache::forget('sync_tracking_active:' . md5($this->shop));
                return;
            }

            $maxContinuations = 40;
            if ($this->wave > $maxContinuations) {
                ShopSyncLock::release($this->shop, 'tracking_max_continuations');
                Cache::forget('sync_tracking_active:' . md5($this->shop));
                return;
            }

            $plan = Plan::where('id', $session->plan_id)->first();
            if (!$plan) {
                ShopSyncLock::release($this->shop, 'tracking_plan_missing');
                Cache::forget('sync_tracking_active:' . md5($this->shop));
                return;
            }

            $createdAfter = $this->resolveCreatedAfter($this->specificDate);
            $sync = new SyncController();
            $batch = 0;
            $pulledTotal = 0;

            while (true) {
                if ((microtime(true) - $startedAt) >= $timeBudgetSec) {
                    $this->dispatchContinuation($cursor);
                    return;
                }

                $fulfillments = Fulfillment::query()
                    ->where('session_id', $session->id)
                    ->where('enable_tracking', 1)
                    ->whereNotNull('tracking_number')
                    ->where('tracking_number', '!=', '')
                    ->where('id', '>', $cursor)
                    ->whereHas('order', function ($q) use ($session, $createdAfter) {
                        $q->where('session_id', $session->id);
                        if ($createdAfter) {
                            $q->where('created_at', '>=', $createdAfter);
                        }
                    })
                    ->orderBy('id')
                    ->limit($this->chunkSize)
                    ->get();

                if ($fulfillments->isEmpty()) {
                    Cache::put('sync_tracking_progress:' . $this->shop, [
                        'phase' => 'tracking_done',
                        'shop' => $this->shop,
                        'wave' => $this->wave,
                        'batches' => $batch,
                        'pulled_total' => $pulledTotal,
                        'updated_at' => now()->toDateTimeString(),
                    ], now()->addHours(12));
                    Cache::forget('sync_tracking_active:' . md5($this->shop));
                    ShopSyncLock::release($this->shop, 'tracking_done');
                    return;
                }

                $batch++;
                $pulled = 0;

                foreach ($fulfillments as $fulfillment) {
                    try {
                        if (!$sync->shouldAllowShipmentTracking($session, $plan)) {
                            if ((int) $plan->id === 1) {
                                $fulfillment->enable_tracking = 0;
                                $fulfillment->save();
                            }
                            continue;
                        }

                        if ($sync->pullCarrierTrackingForFulfillment($fulfillment, $session, $plan)) {
                            $pulled++;
                            $pulledTotal++;
                        }
                    } catch (\Throwable $e) {
                        // Skip one bad fulfillment; keep scanning.
                    }
                }

                $cursor = (int) $fulfillments->last()->id;

                if ($batch <= 5 || $batch % 10 === 0) {
                    Cache::put('sync_tracking_progress:' . $this->shop, [
                        'phase' => 'tracking_running',
                        'shop' => $this->shop,
                        'wave' => $this->wave,
                        'batch' => $batch,
                        'afterId' => $cursor,
                        'pulled_batch' => $pulled,
                        'pulled_total' => $pulledTotal,
                        'updated_at' => now()->toDateTimeString(),
                    ], now()->addHours(12));
                }
            }
        } catch (\Throwable $e) {
            $this->dispatchContinuation($cursor + max(1, $this->chunkSize));
        }
    }

    protected function continuationChainKey(): string
    {
        return 'sync_phase3_chain:' . md5($this->shop . '|' . (string) $this->specificDate);
    }

    /**
     * Queue next wave from the furthest known cursor.
     * Stale once-locks / failed()-vs-budget races must not drop a higher afterId.
     */
    protected function dispatchContinuation(int $nextAfterId): void
    {
        $progress = Cache::get('sync_tracking_progress:' . $this->shop);
        if (is_array($progress)) {
            $nextAfterId = max($nextAfterId, (int) ($progress['afterId'] ?? 0));
        }

        $chainKey = $this->continuationChainKey();
        $existing = Cache::get($chainKey);
        if (is_array($existing)
            && (int) ($existing['nextAfterId'] ?? 0) >= $nextAfterId
            && (int) ($existing['fromWave'] ?? 0) >= $this->wave
        ) {
            return;
        }

        Cache::put($chainKey, [
            'nextAfterId' => $nextAfterId,
            'fromWave' => $this->wave,
            'fromAfterId' => $this->afterId,
            'updated_at' => now()->toDateTimeString(),
        ], now()->addHours(6));

        try {
            self::dispatch($this->shop, $this->chunkSize, $nextAfterId, $this->wave + 1, $this->specificDate)
                ->onConnection('database');
        } catch (\Throwable $e) {
            Cache::forget($chainKey);
        }
    }

    public function failed(\Throwable $exception = null)
    {
        // Prefer progress cursor over job start afterId (long wave-1 runs advance far past 0).
        $this->dispatchContinuation(max(
            (int) $this->afterId + max(1, $this->chunkSize),
            (int) (Cache::get('sync_tracking_progress:' . $this->shop)['afterId'] ?? 0)
        ));
    }

    protected function resolveCreatedAfter($specificDate): ?string
    {
        if (is_string($specificDate) && str_starts_with($specificDate, 'Last 90 days')) {
            return Carbon::now()->subDays(90)->toDateTimeString();
        }
        if ($specificDate == 'Today') {
            return Carbon::today()->toDateTimeString();
        }
        if ($specificDate == 'Last 7 days') {
            return Carbon::now()->subDays(7)->toDateTimeString();
        }
        if ($specificDate == 'Last 15 days') {
            return Carbon::now()->subDays(15)->toDateTimeString();
        }
        if ($specificDate == 'Last 30 days') {
            return Carbon::now()->subDays(30)->toDateTimeString();
        }
        if ($specificDate == 'Last 60 days') {
            return Carbon::now()->subDays(60)->toDateTimeString();
        }
        if ($specificDate == 'Last 90 days') {
            return Carbon::now()->subDays(90)->toDateTimeString();
        }

        return Carbon::now()->subDays(30)->toDateTimeString();
    }
}
