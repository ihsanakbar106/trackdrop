<?php

namespace App\Jobs;

use App\Http\Controllers\SyncController;
use App\Models\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Must stay <= queue:work --timeout on Cloudways.
     * Huge values still get killed by the worker and then retry → MaxAttemptsExceededException.
     */
    public $timeout = 3600;

    /** Full-shop sync must not restart from page 1 after a timeout. */
    public $tries = 1;

    public $failOnTimeout = true;

    public $shop;
    public $specific_date;
    public $isInitialSync;

    public function __construct($shop, $specific_date, $isInitialSync = false)
    {
        $this->shop = $shop;
        $this->specific_date = $specific_date;
        $this->isInitialSync = (bool) $isInitialSync;
    }

    public function handle()
    {
        Log::info('OrderSyncJob started', [
            'shop' => $this->shop,
            'specific_date' => $this->specific_date,
            'isInitialSync' => $this->isInitialSync,
        ]);

        $sync_controller = new SyncController();
        $ok = $sync_controller->sync_orders($this->shop, $this->specific_date);

        if ($this->isInitialSync) {
            $session = Session::where('shop', $this->shop)->first();
            if ($session) {
                Cache::forget('initial_order_sync:' . $session->id);
                if ($ok) {
                    $session->initial_orders_synced_at = now();
                    $session->save();
                }
            }
        }

        Log::info('OrderSyncJob finished', [
            'shop' => $this->shop,
            'ok' => $ok,
        ]);
    }
}
