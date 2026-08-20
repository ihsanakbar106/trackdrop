<?php

namespace App\Jobs;

use App\Http\Controllers\SyncController;
use App\Models\Session;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OrderSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 100000000000;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $shop;
    public $specific_date;
    public function __construct($shop,$specific_date)
    {
        $this->shop = $shop;
        $this->specific_date = $specific_date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $specific_date = $this->specific_date;
        $shop = $this->shop;

        $sync_controller = new SyncController();
        $sync_controller->sync_orders($shop, $specific_date);
    }
}
