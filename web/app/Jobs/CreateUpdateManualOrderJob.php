<?php

namespace App\Jobs;

use App\Http\Controllers\SyncController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Request;

class CreateUpdateManualOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 100000000000;
    /**
     * Create a new job instance.
     *
     * @return void
     */
//    public $request;
    public $shop_name;
    public $specific_date;

    public function __construct($shop_name,$specific_date)
    {
//        $this->request = $request;
        $this->shop_name = $shop_name;
        $this->specific_date = $specific_date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shop_name = $this->shop_name;
        $specific_date = $this->specific_date;
        $sync_controller = new SyncController();
        $sync_controller->sync_orders($shop_name,$specific_date);
    }
}
