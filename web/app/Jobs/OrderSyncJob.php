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

class OrderSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 100000000000;

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
    }
}
