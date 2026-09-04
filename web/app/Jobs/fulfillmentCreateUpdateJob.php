<?php

namespace App\Jobs;

use App\Http\Controllers\CommonController;
use App\Http\Controllers\FulfillmentController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\KlaviyoController;
use App\Http\Controllers\SyncController;
use App\Mail\CheckShippingStatus;
use App\Models\Carrier;
use App\Models\CarrierMapping;
use App\Models\EmailLog;
use App\Models\ErrorMessage;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Session;
use App\Models\Status;

use App\Models\TrackingPage;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class fulfillmentCreateUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 100000000000;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $shop;
    public $fulfillment_api;

    public function __construct($fulfillment_api, Session $shop)
    {
        $this->shop = $shop;
        $this->fulfillment_api = $fulfillment_api;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $fulfillment_api = $this->fulfillment_api;
            $shop = $this->shop;

            $sync_controller = new SyncController();
            $helper_controller = new HelperController();
            $sync_controller->createUpdateFufillment($fulfillment_api, $shop);

            $fulfillment_controller = new FulfillmentController();

            if (isset($fulfillment_api) && isset($fulfillment_api->tracking_company) && $shop && $shop->plan_id) {
                $fulfillment = Fulfillment::with('order')->where('fulfillment_id', $fulfillment_api->id)->first();
                $order = $fulfillment->order;
                if (isset($fulfillment) && isset($order)) {
                    $shopify_order_id=$order->shopify_order_id;
//                    $tracking_company = Carrier::where(function ($query) use ($fulfillment) {
//                        $query->where('name', $fulfillment->tracking_company)->orWhere('code', $fulfillment->tracking_company);
//                    })->first();

//                        $msg = new ErrorMessage();
//                        $msg->message = 'fulfilment tracking start';
//                        $msg->save();
                        $country = null;
                        if (json_decode($order->shipping_address) != '' && json_decode($order->shipping_address) != null) {
                            $shipp_add = json_decode($order->shipping_address);
                            $country = $shipp_add->country;
                        } elseif (json_decode($order->billing_address) != '' && json_decode($order->billing_address) != null) {
                            $bill_add = json_decode($order->billing_address);
                            $country = $bill_add->country;
                        }
                        $plan=Plan::where('id',$shop->plan_id)->first();
                        $common_controller = new CommonController();
                        $total_req=$common_controller->get_api_statistics($shop);


                        $track_shipping=0;
                        $add_usagecharges=0;
                        if($plan->response_limit >$total_req){
                            $track_shipping=1;
                        }elseif ($total_req >= $plan->response_limit && $plan->id!=1){
                            $track_shipping=1;
                            $add_usagecharges=1;
                        }
                        $get_shop=Session::where('id',$shop->id)->first();
                        $c_date=Carbon::now();
                        $n_date=Carbon::parse($get_shop->created_at)->addDay(5);
                        if (!$n_date->greaterThan($c_date)) {
                            if($get_shop->total_shipment_track < 100){
                                $track_shipping=1;
                            }else{
                                $track_shipping=0;
                            }
                        }
                        if($track_shipping) {
                            $fulfillment->enable_tracking=1;
                            $fulfillment->save();
                            // Keep Shopify carrier name; Track123 must not overwrite tracking_company.
                            $original_carrier = $fulfillment_api->tracking_company ?? $fulfillment->tracking_company;
                            $carrier_status = $fulfillment_controller->carrier_register($fulfillment->tracking_number, $original_carrier);
                            $carrier_status=json_decode(json_encode($carrier_status),false);
                            if ($carrier_status->response === true || $carrier_status->response == "already exist") {

                                if ($carrier_status->response === true && isset($shop)) {
                                    $get_shop->total_shipment_track=$get_shop->total_shipment_track+1;
                                    $get_shop->save();
                                    $common_controller->api_statistics($order->session_id, $fulfillment->order_id, $fulfillment->fulfillment_id);
                                    if ($add_usagecharges || $plan->unlimited) {
                                        $fulfillment_controller->add_usage_charge($shop->shop,$plan);
                                    }

                                }
    //                    maitain the tracktory api success request logs
    //                        $common_controller = new CommonController();
    //                        $common_controller->api_statistics($shop->id, $fulfillment->order_id, $fulfillment->fulfillment_id);

                                $shipping_status = $fulfillment_controller->shipping_status(
                                    $fulfillment_api->id,
                                    $fulfillment_api->tracking_number,
                                    $original_carrier,
                                    $shop
                                );
//                                $msg = new ErrorMessage();
//                                $msg->message = 'fulfilment tracking get';
//                                $msg->save();
//                                $msg = new ErrorMessage();
//                                $msg->message = json_encode($shipping_status);
//                                $msg->save();
                                if ($shipping_status != false) {
                                    //                    maitain the tracktory api success request logs
    //                            $common_controller = new CommonController();
    //                            $common_controller->api_statistics($shop->id, $fulfillment->order_id, $fulfillment->fulfillment_id);

                                    $fulfillment_controller->shippingStatusUpdate($shipping_status, $fulfillment, $shop, $country);

                                    $sync_controller->updateCarrier($shopify_order_id);
//                                    $msg = new ErrorMessage();
//                                    $msg->message = 'fulfilment tracking end';
//                                    $msg->save();
                                }
                            }
                        }

                } else {
//            $msg = new ErrorMessage();
//            $msg->message = 'tracking joc code not exist';
//            $msg->save();
                }
            }
            $fulfillment_save = Fulfillment::where('session_id', $shop->id)
                ->where('fulfillment_id', $fulfillment_api->id)
                ->first();
            if($fulfillment_save && $fulfillment_save->update_tracking_url==0 && $shop->tracking_link==1){
                $tracking_page=TrackingPage::where('session_id',$shop->id)
                    ->where('active_status',1)->first();
                if($tracking_page) {
                    if($tracking_page->theme_type=="Modern"){
                        $tracking_url = app_proxy_modern_url($shop->shop, $fulfillment_save->tracking_number);
                    }else{
                        $tracking_url='https://'.$shop->shop.'/pages/'.$tracking_page->page_handle.'?tracking_number='.$fulfillment_save->tracking_number;
                    }

                    $api = $helper_controller->getShopApi($shop->shop);
                    $query = 'mutation fulfillmentTrackingInfoUpdate($fulfillmentId: ID!, $trackingInfoInput: FulfillmentTrackingInput!, $notifyCustomer: Boolean) {
  fulfillmentTrackingInfoUpdate(fulfillmentId: $fulfillmentId, trackingInfoInput: $trackingInfoInput, notifyCustomer: $notifyCustomer) {
    fulfillment {
      id
      status
      trackingInfo {
        company
        number
        url
      }
    }
    userErrors {
      field
      message
    }
  }
}';

                    $variable = [
                        'fulfillmentId' => "gid://shopify/Fulfillment/" . $fulfillment_api->id,
                        'notifyCustomer' => false,
                        'trackingInfoInput' => [
                            "company" => $fulfillment_save->tracking_company,
                            "number" => $fulfillment_save->tracking_number,
                            "url" => $tracking_url,
                        ]
                    ];
                    $response = $api->graph($query, $variable);
                    if($response['errors']==false){
                        $fulfillment_save->update_tracking_url=1;
                        $fulfillment_save->save();
                    }

                }

            }
        } catch (\Exception $e) {
            $msg = new ErrorMessage();
            $msg->message = "fulfillmentCreateUpdateJob error:" . $e->getMessage()." line:". $e->getLine();
            $msg->save();
        }
    }
}
