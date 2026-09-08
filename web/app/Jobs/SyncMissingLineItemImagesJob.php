<?php

namespace App\Jobs;

use App\Http\Controllers\SyncController;
use App\Models\LineItem;
use App\Models\Order;
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
 * Phase 2 after OrderSyncJob: backfill product images, then auto-trigger phase 3 tracking.
 * One job loops batches internally — only re-queues if the time budget runs out.
 * Phase 3 must always run even if images fail (once per shop+date window).
 */
class SyncMissingLineItemImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Must stay below database queue retry_after (3600) and cron --max-time (3500),
     * or a second worker can steal the job / Ctrl+C leaves it reserved for 1h.
     */
    public $timeout = 2400;
    public $tries = 1;

    public $shop;
    public $chunkSize;
    /** Continuation counter if a long run needs a second job. */
    public $wave;
    /** Passed through to phase 3 so tracking only covers this sync window. */
    public $specificDate;

    public function __construct(string $shop, int $chunkSize = 15, int $wave = 1, $specificDate = 'Last 90 days')
    {
        $this->shop = $shop;
        $this->chunkSize = $chunkSize;
        $this->wave = $wave;
        $this->specificDate = $specificDate;
    }

    public function handle()
    {
        // Skip phase 3 only when we successfully queue a continuation job.
        $dispatchPhase3 = true;
        $startedAt = microtime(true);
        // Leave headroom before PHP/queue kill.
        $timeBudgetSec = max(60, $this->timeout - 120);

        try {
            // Wave 1: take pipeline lock if missing (manual restart). Never start images
            // while tracking is already active for this shop.
            if ($this->wave === 1) {
                if (Cache::has('sync_tracking_active:' . md5($this->shop))) {
                    $dispatchPhase3 = false;
                    return;
                }
                if (!ShopSyncLock::isLocked($this->shop)
                    && !ShopSyncLock::acquire($this->shop, [
                        'job' => self::class,
                        'wave' => $this->wave,
                        'specificDate' => $this->specificDate,
                    ])
                ) {
                    $dispatchPhase3 = false;
                    return;
                }
            } else {
                if (!ShopSyncLock::isLocked($this->shop)) {
                    ShopSyncLock::acquire($this->shop, [
                        'job' => self::class,
                        'wave' => $this->wave,
                        'reclaim' => true,
                    ]);
                } else {
                    ShopSyncLock::touch($this->shop);
                }
            }

            $session = Session::where('shop', $this->shop)->first();
            if (!$session) {
                return;
            }

            $maxContinuations = 20;
            if ($this->wave > $maxContinuations) {
                return;
            }

            $createdAfter = $this->resolveCreatedAfter($this->specificDate);
            $sync = new SyncController();
            $batch = 0;
            $missingStart = $this->missingImageCount($session->id, $createdAfter);
            $skipCacheKey = 'sync_images_skip:' . $this->shop;
            /** @var array<int, int|string> */
            $skipOrderIds = Cache::get($skipCacheKey, []);
            if (!is_array($skipOrderIds)) {
                $skipOrderIds = [];
            }
            // Fresh sync wave 1: don't inherit stale skips from an older run.
            if ($this->wave === 1) {
                $skipOrderIds = [];
                Cache::forget($skipCacheKey);
            }

            while (true) {
                if ((microtime(true) - $startedAt) >= $timeBudgetSec) {
                    $remaining = $this->missingImageCount($session->id, $createdAfter);
                    if ($remaining > 0) {
                        Cache::put($skipCacheKey, array_values(array_unique($skipOrderIds)), now()->addHours(12));
                        $this->rememberProgress([
                            'phase' => 'images_continue',
                            'wave' => $this->wave,
                            'batch' => $batch,
                            'missing_remaining' => $remaining,
                            'skipped_undownloadable' => count($skipOrderIds),
                        ]);
                        self::dispatch($this->shop, $this->chunkSize, $this->wave + 1, $this->specificDate)
                            ->onConnection('database');
                        $dispatchPhase3 = false;
                    } else {
                        Cache::forget($skipCacheKey);
                    }
                    break;
                }

                $orderIdsQuery = LineItem::query()
                    ->where('session_id', $session->id)
                    ->where(function ($q) {
                        $q->whereNull('image')->orWhere('image', '');
                    })
                    ->whereHas('order', function ($q) use ($session, $createdAfter) {
                        $q->where('session_id', $session->id);
                        if ($createdAfter) {
                            $q->where('created_at', '>=', $createdAfter);
                        }
                    });
                if (!empty($skipOrderIds)) {
                    $orderIdsQuery->whereNotIn('shopify_order_id', $skipOrderIds);
                }
                $orderIds = $orderIdsQuery
                    ->distinct()
                    ->orderByDesc('shopify_order_id')
                    ->limit($this->chunkSize)
                    ->pluck('shopify_order_id')
                    ->filter()
                    ->values();

                if ($orderIds->isEmpty()) {
                    $remaining = $this->missingImageCount($session->id, $createdAfter);
                    Cache::forget($skipCacheKey);
                    $this->rememberProgress([
                        'phase' => $remaining > 0 ? 'images_partial_done' : 'images_done',
                        'wave' => $this->wave,
                        'batch' => $batch,
                        'missing_remaining' => $remaining,
                        'missing_start' => $missingStart,
                        'skipped_undownloadable' => count($skipOrderIds),
                    ]);
                    break;
                }

                $batch++;
                $filledBefore = $this->missingImageCount($session->id, $createdAfter);

                foreach ($orderIds as $shopifyOrderId) {
                    try {
                        $order = Order::where('session_id', $session->id)
                            ->where('shopify_order_id', $shopifyOrderId)
                            ->first();
                        if (!$order) {
                            $skipOrderIds[] = $shopifyOrderId;
                            continue;
                        }
                        $apiOrder = (object) ['id' => $order->shopify_order_id];
                        $sync->sync_lineitem_images($apiOrder, $session);
                    } catch (\Throwable $e) {
                        $skipOrderIds[] = $shopifyOrderId;
                    }
                }

                $filledAfter = $this->missingImageCount($session->id, $createdAfter);
                $progress = $filledBefore - $filledAfter;

                // Any order in this batch that still has empty images cannot be filled via GraphQL —
                // exclude it so the next batches reach other orders (avoids early images_stalled).
                foreach ($orderIds as $shopifyOrderId) {
                    $stillMissing = LineItem::query()
                        ->where('session_id', $session->id)
                        ->where('shopify_order_id', $shopifyOrderId)
                        ->where(function ($q) {
                            $q->whereNull('image')->orWhere('image', '');
                        })
                        ->exists();
                    if ($stillMissing) {
                        $skipOrderIds[] = $shopifyOrderId;
                    }
                }
                $skipOrderIds = array_values(array_unique($skipOrderIds));

                if ($batch <= 5 || $batch % 10 === 0) {
                    $this->rememberProgress([
                        'phase' => 'images_running',
                        'wave' => $this->wave,
                        'batch' => $batch,
                        'missing_before' => $filledBefore,
                        'missing_remaining' => $filledAfter,
                        'filled_this_batch' => $progress,
                        'missing_start' => $missingStart,
                        'orders_in_batch' => $orderIds->count(),
                        'skipped_undownloadable' => count($skipOrderIds),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Phase 3 still runs via finally.
        } finally {
            if ($dispatchPhase3) {
                Cache::forget('sync_images_skip:' . $this->shop);
                $this->dispatchTrackingJobOnce();
            }
        }
    }

    protected function dispatchTrackingJobOnce(): void
    {
        $lockKey = 'sync_phase3_dispatched:' . md5($this->shop . '|' . (string) $this->specificDate);
        if (!Cache::add($lockKey, 1, now()->addHours(6))) {
            return;
        }

        SyncFulfillmentTrackingJob::dispatch($this->shop, 15, 0, 1, $this->specificDate)
            ->onConnection('database');
    }

    public function failed(\Throwable $exception = null)
    {
        try {
            $this->dispatchTrackingJobOnce();
        } catch (\Throwable $e) {
            // Best-effort phase-3 dispatch after hard failure.
        }
    }

    protected function missingImageCount(int $sessionId, ?string $createdAfter = null): int
    {
        return (int) LineItem::query()
            ->where('session_id', $sessionId)
            ->where(function ($q) {
                $q->whereNull('image')->orWhere('image', '');
            })
            ->whereHas('order', function ($q) use ($sessionId, $createdAfter) {
                $q->where('session_id', $sessionId);
                if ($createdAfter) {
                    $q->where('created_at', '>=', $createdAfter);
                }
            })
            ->count();
    }

    /** Cache snapshot for ops (same idea as sync_orders_progress). */
    protected function rememberProgress(array $data): void
    {
        $payload = array_merge([
            'shop' => $this->shop,
            'specificDate' => $this->specificDate,
            'updated_at' => now()->toDateTimeString(),
        ], $data);
        Cache::put('sync_images_progress:' . $this->shop, $payload, now()->addHours(12));
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
