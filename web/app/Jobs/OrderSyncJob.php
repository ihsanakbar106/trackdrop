<?php

namespace App\Jobs;

use App\Http\Controllers\SyncController;
use App\Models\Session;
use App\Support\ShopSyncLock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class OrderSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Must stay <= queue:work --timeout on Cloudways.
     * Last 90 days runs as 3 monthly chunks (~7–8k orders each).
     */
    public $timeout = 3600;

    /** Full-shop sync must not restart from page 1 after a timeout. */
    public $tries = 1;

    public $failOnTimeout = true;

    public $shop;
    public $specific_date;
    public $isInitialSync;

    /**
     * Last 90 days only: 1 = days 0–30, 2 = 30–60, 3 = 60–90.
     * null on "Last 90 days" kicks off chunk 1 automatically.
     */
    public $monthChunk;

    /**
     * Unix timestamp frozen when chunk 1 starts so month boundaries do not drift
     * while earlier chunks are still running.
     */
    public $rangeAnchor;

    public function __construct(
        $shop,
        $specific_date,
        $isInitialSync = false,
        $monthChunk = null,
        $rangeAnchor = null
    ) {
        $this->shop = $shop;
        $this->specific_date = $specific_date;
        $this->isInitialSync = (bool) $isInitialSync;
        $this->monthChunk = $monthChunk !== null ? (int) $monthChunk : null;
        $this->rangeAnchor = $rangeAnchor !== null ? (int) $rangeAnchor : null;
    }

    public function handle()
    {
        // One pipeline per shop. Coordinator / non-90 sync acquire.
        // Month chunks 1–3: keep existing lock, or reclaim if lock missing (manual / crash recovery).
        if ($this->isShopSyncEntryPoint()) {
            if (!ShopSyncLock::acquire($this->shop, [
                'job' => self::class,
                'specific_date' => $this->specific_date,
                'monthChunk' => $this->monthChunk,
                'isInitialSync' => $this->isInitialSync,
            ])) {
                return;
            }
        } elseif (!ShopSyncLock::isLocked($this->shop)) {
            if (!ShopSyncLock::acquire($this->shop, [
                'job' => self::class,
                'specific_date' => $this->specific_date,
                'monthChunk' => $this->monthChunk,
                'reclaim' => true,
            ])) {
                return;
            }
        } else {
            ShopSyncLock::touch($this->shop);
        }

        // Last 90 → 3 sequential monthly jobs (avoids single 20k+ order timeout).
        if ($this->specific_date === 'Last 90 days' && $this->monthChunk === null) {
            $anchor = time();
            self::dispatch($this->shop, 'Last 90 days', $this->isInitialSync, 1, $anchor)
                ->onConnection('database');
            return;
        }

        $label = $this->specific_date;
        if ($this->specific_date === 'Last 90 days' && $this->monthChunk) {
            $label = 'Last 90 days (month ' . $this->monthChunk . '/3)';
        }

        $ok = false;
        $pipelineAdvanced = false;

        try {
            $sync_controller = new SyncController();
            $createdAtMin = null;
            $createdAtMax = null;
            if ($this->specific_date === 'Last 90 days' && $this->monthChunk) {
                [$createdAtMin, $createdAtMax] = $this->monthChunkDateRange($this->monthChunk);
            }

            $ok = (bool) $sync_controller->sync_orders(
                $this->shop,
                $label,
                true,
                $createdAtMin,
                $createdAtMax
            );
        } catch (\Throwable $e) {
            $ok = false;
        }

        try {
            $this->advancePipeline($ok);
            $pipelineAdvanced = true;
        } catch (\Throwable $e) {
            ShopSyncLock::release($this->shop, 'advance_pipeline_failed');
        }

        // Initial-sync flag only after a successful final chunk (or non-90 sync).
        $markInitialDone = $ok && $this->isInitialSync && $pipelineAdvanced && (
            $this->specific_date !== 'Last 90 days'
            || $this->monthChunk === 3
        );

        if ($markInitialDone) {
            $session = Session::where('shop', $this->shop)->first();
            if ($session) {
                Cache::forget('initial_order_sync:' . $session->id);
                $session->initial_orders_synced_at = now();
                $session->save();
            }
        } elseif ($this->isInitialSync && !$ok && (
            $this->specific_date !== 'Last 90 days' || $this->monthChunk === 3
        )) {
            // Final stage failed — release install lock so auto-sync can retry later.
            $this->releaseInitialSyncLock();
        }
    }

    /**
     * Always continue: next month chunk OR phase 2 images.
     * Never stop the whole Last-90 / images / tracking chain because one chunk errored.
     */
    protected function advancePipeline(bool $ok): void
    {
        $lockKey = 'order_sync_advanced:' . md5(implode('|', [
            $this->shop,
            (string) $this->specific_date,
            (string) $this->monthChunk,
            (string) $this->rangeAnchor,
        ]));
        if (!Cache::add($lockKey, 1, now()->addHours(6))) {
            return;
        }

        if ($this->specific_date === 'Last 90 days' && $this->monthChunk && $this->monthChunk < 3) {
            self::dispatch(
                $this->shop,
                'Last 90 days',
                $this->isInitialSync,
                $this->monthChunk + 1,
                $this->rangeAnchor ?: time()
            )->onConnection('database');
            return;
        }

        // Clear phase-3 once-locks so a fresh sync can run tracking again.
        Cache::forget('sync_phase3_dispatched:' . md5($this->shop . '|' . (string) $this->specific_date));
        Cache::forget('sync_phase3_chain:' . md5($this->shop . '|' . (string) $this->specific_date));

        SyncMissingLineItemImagesJob::dispatch($this->shop, 15, 1, $this->specific_date)
            ->onConnection('database');
    }

    /**
     * Chunk 1: last 0–30 days (newest first).
     * Chunk 2: 30–60 days ago.
     * Chunk 3: 60–90 days ago.
     *
     * @return array{0: string, 1: string|null} [created_at_min, created_at_max]
     */
    protected function monthChunkDateRange(int $chunk): array
    {
        $t = $this->rangeAnchor ?: time();

        if ($chunk === 1) {
            return [date('c', $t - (30 * 86400)), null];
        }
        if ($chunk === 2) {
            return [date('c', $t - (60 * 86400)), date('c', $t - (30 * 86400))];
        }

        return [date('c', $t - (90 * 86400)), date('c', $t - (60 * 86400))];
    }

    /**
     * First job of a sync only — monthly chunk 2/3 must not try to re-acquire.
     */
    protected function isShopSyncEntryPoint(): bool
    {
        if ($this->specific_date === 'Last 90 days') {
            return $this->monthChunk === null;
        }

        return true;
    }

    protected function releaseInitialSyncLock(): void
    {
        if (!$this->isInitialSync) {
            return;
        }
        $session = Session::where('shop', $this->shop)->first();
        if ($session) {
            Cache::forget('initial_order_sync:' . $session->id);
        }
    }

    /**
     * Worker timeout / hard fail: still try to continue next month or phase 2.
     */
    public function failed(\Throwable $exception = null)
    {
        // Coordinator split job has nothing to advance.
        if ($this->specific_date === 'Last 90 days' && $this->monthChunk === null) {
            ShopSyncLock::release($this->shop, 'coordinator_failed');
            $this->releaseInitialSyncLock();
            return;
        }

        try {
            $this->advancePipeline(false);
        } catch (\Throwable $e) {
            ShopSyncLock::release($this->shop, 'failed_advance_pipeline');
            $this->releaseInitialSyncLock();
        }
    }
}
