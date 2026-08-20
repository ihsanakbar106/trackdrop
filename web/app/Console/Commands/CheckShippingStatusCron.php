<?php

namespace App\Console\Commands;

use App\Http\Controllers\CommonController;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\Status;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class CheckShippingStatusCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping_status:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /*Order::orderBy('id')->chunk(100, function ($orders) {
            if ($orders->count()) {
                foreach ($orders as $order) {
                    $order_fulfilments = $order->fulfillments()->get();
                    foreach ($order_fulfilments as $fulfillment) {
                        if (($fulfillment->shipment_status == null) || ($fulfillment->shipment_status != 'delivered') || ($fulfillment->shipment_status != 'expired')) {
                            $fulfillment_controller = new \App\Http\Controllers\FulfillmentController();
                            $shipping_response = $fulfillment_controller->shipping_status($fulfillment->fulfillment_id, $fulfillment->tracking_number, $fulfillment->tracking_company);
                            if ($shipping_response != false) {

                                //                    maitain the tracktory api success request logs
                                $common_controller = new CommonController();
                                $common_controller->api_statistics($order->session_id,$fulfillment->order_id,$fulfillment->fulfillment_id);

                                $fulfillment_controller->shippingStatusUpdate($shipping_response,$fulfillment);

                            }
                        }
                    }
                }
            }
        });*/
    }
}
