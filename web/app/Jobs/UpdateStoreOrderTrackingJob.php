<?php

namespace App\Jobs;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\FulfillmentController;
use App\Http\Controllers\SyncController;
use App\Models\Carrier;
use App\Models\CarrierMapping;
use App\Models\EmailLog;
use App\Models\ErrorMessage;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\Session;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Mockery\Exception;

class UpdateStoreOrderTrackingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $shop;
    public $datefilter;

    public function __construct(Session $shop, $datefilter)
    {
        $this->shop = $shop;
        $this->datefilter = $datefilter;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $shop = $this->shop;
        $datefilter = $this->datefilter;

        $sync_controller = new SyncController();
        $sync_controller->UpdateStoreOrderTrackings($shop, $datefilter);
    }
}
