<?php

namespace App\Http\Controllers;


use A6digital\Image\DefaultProfileImage;
use App\Jobs\AllFulfillmentCreateUpdateJob;
use App\Jobs\AllOrderCreateUpdateJob;

use App\Jobs\CreateUpdateManualOrderJob;
use App\Jobs\OrderSyncJob;
use App\Jobs\UpdateStoreOrderTrackingJob;
use App\Mail\CheckShippingStatus;
use App\Models\ApiSetting;
use App\Models\Carrier;
use App\Models\CarrierMapping;
use App\Models\CustomChargeLog;
use App\Models\EmailLog;
use App\Models\ErrorMessage;
use App\Models\Fulfillment;
use App\Models\LineItem;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Session;

use App\Models\Status;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use App\Services\ShopifyTokenService;
use Mockery\Exception;
use Shopify\Clients\Rest;

class SyncController extends HelperController
{
    public function sync_fresh_orders_tracking()
    {
//        'created_at_min'=>'2012-08-01 12:00'
        Session::where('role', 0)->where('id', 28)->orderBy('id')->chunk(2, function ($users) {
            if ($users->count()) {
                foreach ($users as $user) {
                    $shop = $user;
                    $next_page = '';
                    $days_30 = date('c', strtotime('-30 days'));

                    $order_count_api = $this->getShopApi($shop->shop)->rest('GET', '/admin/orders/count.json'
                        , [
                            'created_at_min' => $days_30, 'status' => 'any'
                        ]
                    )['body']['count'];
                    $count = ceil($order_count_api / 250);

                    for ($i = 1; $i <= $count; $i++) {
                        if (isset($next_page)) {
                            if ($next_page == '') {
                                $params = ['limit' => 250, 'status' => 'any', 'created_at_min' => $days_30];
//                    $params = ['limit' => 250, 'page_info' => $next_page];
                            } else {
                                $params = ['limit' => 250, 'page_info' => $next_page];
                            }

                            $order_api = $this->getShopApi($shop->shop)->rest('GET', '/admin/orders.json', $params);

                            if ($order_api['errors'] == false) {
                                if (isset($order_api['link']['next'])) {
                                    $next_page = $order_api['link']['next'];
                                } else {
                                    $next_page = null;
                                }
                                $order_api = json_decode(json_encode($order_api['body']['orders']), FALSE);

                                foreach ($order_api as $order) {
                                    dispatch(new AllOrderCreateUpdateJob($order, $shop));
                                    if (!empty($order->fulfillments)) {
                                        foreach ($order->fulfillments as $fulfillment_api) {
                                            dispatch(new AllFulfillmentCreateUpdateJob($fulfillment_api, $shop));
                                        }
                                    }
//                                    if(!empty($order->fulfillments)){
//                                        foreach ($order->fulfillments as $fulfillment_api) {
//                                            $this->createUpdateFufillment($fulfillment_api, $shop);
//                                        }
//                                    }
                                }
                            }
                        }
                    }
                }
            }
        });

        return Redirect::tokenRedirect('orders', ['notice' => 'All orders will sync with in some time!']);

    }

    public function refresh_order_tracking_status()
    {
        $orders = Order::where('last_tracking_status_check_time', '!=', null)->get();
        if ($orders->count()) {
            foreach ($orders as $order) {
                $order->last_tracking_status_check_time = null;
                $order->save();
            }
        }

        dd('done');

    }

    /*public function sync_order_trackings()
    {
        try {
            Order::whereHas('fulfillments', function ($q) {
                $q->whereNotNull('tracking_number');
            })->chunk(100, function ($orders) {
                if ($orders->count()) {
                    foreach ($orders as $order) {
                        $last_status_updated_time_diff = now()->diffInDays(Carbon::parse($order->last_tracking_status_check_time));

//                        if ($last_status_updated_time_diff > 0 || $order->last_tracking_status_check_time == null) {
                        $country = null;

                        if (json_decode($order->shipping_address) != '' && json_decode($order->shipping_address) != null) {
                            $shipp_add = json_decode($order->shipping_address);
                            $country = $shipp_add->country;
                        } elseif (json_decode($order->billing_address) != '' && json_decode($order->billing_address) != null) {
                            $bill_add = json_decode($order->billing_address);
                            $country = $bill_add->country;
                        }

                        if ($order->fulfillments->isNotEmpty()) {
                            foreach ($order->fulfillments as $fulfillment) {
//                            if (isset($fulfillment->tracking_number) && isset($country) && (($fulfillment->shipment_status == null) || ($fulfillment->shipment_status != 'delivered'))) {
                                if (isset($fulfillment->tracking_number)) {

                                    $fulfillment_controller = new FulfillmentController();
                                    $klaviyo_controller = new KlaviyoController();

                                    $tracking_company = Carrier::where(function ($query) use ($fulfillment) {
                                        $query->where('name', $fulfillment->tracking_company)->orWhere('code', $fulfillment->tracking_company);
                                    })->first();

                                    if (isset($tracking_company->code)) {
                                        $carrier_status = $fulfillment_controller->carrier_register($fulfillment->tracking_number, $tracking_company->code);

                                        if ($carrier_status === true || $carrier_status == "already exist") {
                                            $shop = Session::find($order->user_id);
                                            if ($carrier_status === true && isset($shop)) {
//                                                    $fulfillment_controller->add_usage_charge($shop->shop);
                                            }
                                            //                    maitain the tracktory api success request logs
//                                                $common_controller = new CommonController();
//                                                $common_controller->api_statistics($order->user_id, $fulfillment->order_id, $fulfillment->fulfillment_id);

                                            $shipping_response = $fulfillment_controller->shipping_status($fulfillment->fulfillment_id, $fulfillment->tracking_number, $tracking_company->code);
//                                             dd($shipping_response);
                                            if ($shipping_response != false) {
                                                $fulfillment_controller->shippingStatusUpdate($shipping_response,$fulfillment,$shop,$country);

                                            }
                                        }
                                    } else {
//                                        $msg = new ErrorMessage();
//                                        $msg->message = 'tracking code not exist, fulfillment_id' . isset($fulfillment->fulfillment_id) ? $fulfillment->fulfillment_id : null;
//                                        $msg->save();
                                    }


                                }
                            }
                            $order->last_tracking_status_check_time = Carbon::now();
                            $order->save();
                        }

//                        }
                    }
                }
            });
        } catch (\Exception $exception) {
            dd($exception->getMessage());
//            $msg = new ErrorMessage();
//            $msg->message = 'error in update order tracking detail cron job: ' . json_encode($exception->getMessage());
//            $msg->save();
        }

//        return Redirect::tokenRedirect('orders', ['notice' => 'Order trackings sync successfully !']);
    }*/

    public function sync_store_order_fulfillments_tracking(Request $request)
    {
        return true;
        try {
            //        $session_obj = $request->get('shopifySession');
//        $shop = Session::where('shop', $session_obj->getShop())->first();
          $shop = $this->getShop($request);
            if (isset($shop)) {
                UpdateStoreOrderTrackingJob::dispatch($shop, $request['datefilter'])->onConnection("database");
            }

        } catch (\Exception $exception) {
//            $msg = new ErrorMessage();
//            $msg->message = 'error in update order tracking detail cron job: ' . json_encode($exception->getMessage());
//            $msg->save();
        }

//        return Redirect::tokenRedirect('orders', ['notice' => 'Order trackings sync successfully !']);
    }

    /**
     * During bulk OrderSyncJob, skip carrier API only for already-delivered rows.
     * Active statuses (pending/transit/out for delivery/…) still refresh — same as before.
     * Removes only the expensive re-pull of delivered history (plus we no longer double-call updateCarrier).
     */
    protected function fulfillmentNeedsTrackingPull($fulfillment): bool
    {
        if (!$fulfillment || empty($fulfillment->tracking_number)) {
            return false;
        }

        $status = strtolower(trim((string) ($fulfillment->shipment_status ?? '')));
        $trackInfo = trim((string) ($fulfillment->track_info ?? ''));
        $hasTrackInfo = $trackInfo !== '' && $trackInfo !== '[]' && $trackInfo !== 'null';

        // Match existing refresh jobs: do not re-hit carriers for delivered shipments.
        if ($status === 'delivered' && $hasTrackInfo) {
            return false;
        }

        return true;
    }

    public function sync_orders($shop_name, $specificDate = 'Last 30 days')
    {
        try {
            $shop = Session::where('shop', $shop_name)->first();
            if (!$shop) {
                return false;
            }
            $fulfillment_controller = new FulfillmentController();
            $common_controller = new CommonController();
            $plan = $shop->plan_id ? Plan::where('id', $shop->plan_id)->first() : null;

            $days = null;
            if (isset($specificDate) && $specificDate != "") {
                if ($specificDate == 'Today') {
                    $days = date('c');
                } elseif ($specificDate == 'Last 7 days') {
                    $days = date('c', strtotime('-7 days'));
                } elseif ($specificDate == 'Last 15 days') {
                    $days = date('c', strtotime('-15 days'));
                } elseif ($specificDate == 'Last 30 days') {
                    $days = date('c', strtotime('-30 days'));
                } elseif ($specificDate == 'Last 60 days') {
                    $days = date('c', strtotime('-60 days'));
                } elseif ($specificDate == 'Last 90 days') {
                    $days = date('c', strtotime('-90 days'));
                }
            } else {
                $days = date('c', strtotime('-30 days'));
            }


            $sync_controller = new SyncController();
            $next_page = '';
            $completedWithoutApiError = true;

            do {
                    if ($next_page === '') {
                        $params = ['limit' => 250, 'status' => 'any', 'created_at_min' => $days];
                    } else {
                        $params = ['limit' => 250, 'page_info' => $next_page];
                    }

                    $order_api = $this->getShopApi($shop->shop)->rest('get', '/admin/orders', $params);

                    if ($order_api['errors'] !== false) {
                        $completedWithoutApiError = false;
                        break;
                    }

                    $orders = $order_api['body']['orders'] ?? [];
                    if (!empty($orders)) {
                        foreach ($orders as $order) {
                            $sync_controller->createUpdateOrder($order, $shop);
                            if (!empty($order['fulfillments'])) {
                                foreach ($order['fulfillments'] as $fulfillment_api) {
                                    $fulfillment_api = json_decode(json_encode($fulfillment_api), false);
                                    $sync_controller->createUpdateFufillment($fulfillment_api, $shop);
                                    if (isset($fulfillment_api) && isset($fulfillment_api->tracking_company) && $shop && $shop->plan_id && $plan) {
                                        $fulfillment = Fulfillment::with('order')->where('fulfillment_id', $fulfillment_api->id)->first();
                                        $dbOrder = $fulfillment?->order;
                                        if (isset($fulfillment) && isset($dbOrder)) {
                                            $country = null;
                                            if (json_decode($dbOrder->shipping_address) != '' && json_decode($dbOrder->shipping_address) != null) {
                                                $shipp_add = json_decode($dbOrder->shipping_address);
                                                $country = $shipp_add->country;
                                            } elseif (json_decode($dbOrder->billing_address) != '' && json_decode($dbOrder->billing_address) != null) {
                                                $bill_add = json_decode($dbOrder->billing_address);
                                                $country = $bill_add->country;
                                            }
                                            $total_req = $common_controller->get_api_statistics($shop);

                                            $track_shipping = 0;
                                            $add_usagecharges = 0;
                                            if ($plan->response_limit > $total_req) {
                                                $track_shipping = 1;
                                            } elseif ($total_req >= $plan->response_limit && $plan->id != 1) {
                                                $track_shipping = 1;
                                                $add_usagecharges = 1;
                                            }
                                            $get_shop = Session::where('id', $shop->id)->first();
                                            $c_date = Carbon::now();
                                            $n_date = Carbon::parse($get_shop->created_at)->addDay(5);
                                            if (!$n_date->greaterThan($c_date)) {
                                                if ($get_shop->total_shipment_track < 100) {
                                                    $track_shipping = 1;
                                                } else {
                                                    $track_shipping = 0;
                                                }
                                            }
                                            if ($track_shipping) {
                                                $fulfillment->enable_tracking = 1;
                                                $fulfillment->save();

                                                // Delivered+track_info: skip carrier APIs (same idea as other refresh jobs).
                                                if (!$this->fulfillmentNeedsTrackingPull($fulfillment)) {
                                                    continue;
                                                }

                                                // Keep Shopify carrier name; Track123 must not overwrite tracking_company.
                                                $original_carrier = $fulfillment_api->tracking_company ?? $fulfillment->tracking_company;
                                                $carrier_status = $fulfillment_controller->carrier_register($fulfillment->tracking_number, $original_carrier);
                                                $carrier_status = json_decode(json_encode($carrier_status), false);
                                                if (is_object($carrier_status) && ($carrier_status->response === true || $carrier_status->response == "already exist")) {

                                                    if ($carrier_status->response === true && isset($shop)) {
                                                        $get_shop->total_shipment_track = $get_shop->total_shipment_track + 1;
                                                        $get_shop->save();
                                                        $common_controller->api_statistics($dbOrder->session_id, $fulfillment->order_id, $fulfillment->fulfillment_id);
                                                        if ($add_usagecharges || $plan->unlimited) {
                                                            $fulfillment_controller->add_usage_charge($shop->shop, $plan);
                                                        }

                                                    }
                                                    $shipping_status = $fulfillment_controller->shipping_status(
                                                        $fulfillment_api->id,
                                                        $fulfillment_api->tracking_number,
                                                        $original_carrier,
                                                        $shop
                                                    );

                                                    if ($shipping_status != false) {
                                                        // Do NOT call updateCarrier here — it repeats shipping_status for the same order.
                                                        $fulfillment_controller->shippingStatusUpdate($shipping_status, $fulfillment, $shop, $country);
                                                    }
                                                }
                                            }

                                        }
                                    }

                                }
                            }
                        }
                    }

                    if (!empty($order_api['link']['next'])) {
                        $next_page = $order_api['link']['next'];
                    } else {
                        $next_page = null;
                    }
                } while ($next_page);

            return $completedWithoutApiError;
        } catch (\Exception $e) {
            \Log::error('sync_orders failed', [
                'shop' => $shop_name,
                'specificDate' => $specificDate,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Queue a one-time last-90-days order sync on app install only (plan not required).
     * Flag is set only after OrderSyncJob succeeds so a failed run can retry.
     */
    public function triggerInitialOrderSyncIfNeeded(Session $session): bool
    {
        if (!$session || $session->initial_orders_synced_at) {
            return false;
        }

        $lockKey = 'initial_order_sync:' . $session->id;
        if (!Cache::add($lockKey, 1, now()->addHours(24))) {
            return false;
        }

        OrderSyncJob::dispatch($session->shop, 'Last 90 days', true)->onConnection('database');

        return true;
    }

    public function sync_order_on_btn_click(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        try {
//            CreateUpdateManualOrderJob::dispatch($request['shop'],$request['specific_date])->onConnection("database");
//            $this->sync_orders($session->shop, $request->specific_date);
            OrderSyncJob::dispatch($session->shop, $request->specific_date)->onConnection('database');
            $data = [
                'status' => 'success',
                'message' => 'All orders will sync with in some time!'
            ];
        } catch (\Exception $exception) {
            $data = [
                'status' => 'error', 'notice' => 'All orders will sync with in some time!',
                'message' => $exception->getMessage()];
        }

        return response()->json($data);
    }

    public function createUpdateOrder($order, $shop)
    {
        try {

            $order = json_decode(json_encode($order), false);

            $order_data = Order::where('shopify_order_id', $order->id)->where('session_id', $shop->id)->first();
            $order_data = $order_data ? $order_data : new Order();

            $customer = $order->customer;
            $shipping_address = $order->shipping_address;
            $billing_address = $order->billing_address;
            $created_at = Carbon::createFromTimeString($order->created_at)->format('Y-m-d H:i:s');
            $updated_at = Carbon::createFromTimeString($order->updated_at)->format('Y-m-d H:i:s');
            $processed_at = Carbon::createFromTimeString($order->processed_at)->format('Y-m-d H:i:s');
            $customer_name = optional($customer)->first_name . " " . optional($customer)->last_name;

            $order_data->fill([
                'session_id' => $shop->id,
                'email' => $order->email,
                'shopify_order_id' => $order->id,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
                'name' => $order->name,
                'customer_name' => $customer_name,
                'country' => optional($shipping_address)->country,
                'order_status_url' => $order->order_status_url,
                'fulfillment_status' => $order->fulfillment_status,
                'financial_status' => $order->financial_status,
                'customer' => $customer ? json_encode($customer) : null,
                'total_price' => $order->total_price,
                'subtotal_price' => $order->subtotal_price,
                'currency' => $order->currency,
                'total_line_items_price' => optional($order)->total_line_items_price,
                'location_id' => optional($order)->location_id,
                'checkout_token' => $order->checkout_token,
                'processed_at' => $processed_at,
                'shipping_address' => $shipping_address ? json_encode($shipping_address) : null,
                'billing_address' => $billing_address ? json_encode($billing_address) : null,
            ]);

            if (is_null($order->location_id)) {
                $client = new Rest($shop->shop, (new ShopifyTokenService())->getValidAccessToken($shop->shop));
                $locations_response = $client->get('locations.json', []);
                $locations = $locations_response->getDecodedBody()['locations'] ? $locations_response->getDecodedBody()['locations'] : [];

                if (!empty($locations)) {
                    $order_data->location_id = $locations[0]['id'];
                }
            }

            $order_data->save();


            foreach ($order->line_items as $item) {
                $line = LineItem::where('shopify_lineitem_id', $item->id)->where('session_id', $shop->id)->first();
                if ($line == null) {
                    $line = new LineItem();
                }

                $line->order_id = $order_data->id;
                $line->shopify_lineitem_id = $item->id;
                $line->session_id = $shop->id;
                $line->variant_id = $item->variant_id;
                $line->product_id = $item->product_id;
                $line->title = $item->title;
                $line->quantity = $item->quantity;
                $line->grams = $item->grams;
                $line->sku = $item->sku;
                $line->price = $item->price;
                $line->shopify_order_id = $order->id;
                $line->fulfillable_quantity = $item->fulfillable_quantity;
                $line->fulfillment_status = $item->fulfillment_status;
                $line->fulfillment_service = $item->fulfillment_service;
                $line->variant_title = $item->variant_title;
                $line->properties = isset($item->properties) && !empty($item->properties) ? json_encode($item->properties) : null;

                $line->save();
            }


            $this->sync_lineitem_images($order, $shop);
            $this->sync_fulfillment_order_ids($order_data, $shop);

        } catch (\Exception $exception) {
//            dd('Error in function createUpdateOrder: ', $exception->getMessage());
//            $msg = new ErrorMessage();
//            $msg->message = 'error in order create update function: ' . json_encode($exception->getMessage());
//            $msg->save();
        }
//        }


    }

    public function sync_lineitem_images($order, $shop)
    {
        $response_query = <<<QUERY
            {
  order(id: "gid://shopify/Order/$order->id") {
    lineItems(first: 50) {
      nodes {
        image {
          url
        }
        id
      }
    }
  }
}
QUERY;
        $response = $this->getShopApi($shop->shop)->graph($response_query);

        if ($response['errors'] == false) {
            $response = json_decode(json_encode($response), false);
            if (isset($response) && isset($response->body) && isset($response->body->data) && isset($response->body->data->order)) {
                $lineitems = $response->body->data->order->lineItems->nodes;
                foreach ($lineitems as $lineitem) {
                    $db_lineitem = LineItem::where('shopify_lineitem_id', intval(str_replace("gid://shopify/LineItem/", "", $lineitem->id)))->first();
                    if (isset($db_lineitem)) {
                        $db_lineitem->image = isset($lineitem) && isset($lineitem->image) && isset($lineitem->image->url) ? $lineitem->image->url : null;
                        $db_lineitem->save();
                    }
                }
            }
        }

    }

    public function sync_fulfillment_order_ids(Order $db_order, Session $shop)
    {
        try {
            $client = new Rest($shop->shop, (new ShopifyTokenService())->getValidAccessToken($shop->shop));
            $fulfillments_orders = $client->get('orders/' . $db_order->shopify_order_id . '/fulfillment_orders.json');
            $fulfillments_orders = $fulfillments_orders->getDecodedBody();

            if (isset($fulfillments_orders) && !empty($fulfillments_orders)) {
                foreach ($fulfillments_orders as $fulfillments_order_array) {
                    if (isset($fulfillments_order_array) && !empty($fulfillments_order_array)) {
                        foreach ($fulfillments_order_array as $fulfillments_order) {
                            $fulfillments_order = json_decode(json_encode($fulfillments_order), false);
                            $db_order->shopify_fulfillment_order_id = $fulfillments_order->id;

                            if (!empty($fulfillments_order->line_items)) {
                                foreach ($fulfillments_order->line_items as $line_item) {
                                    $db_line_item = LineItem::where('shopify_lineitem_id', $line_item->line_item_id)->first();
                                    if (isset($db_line_item)) {
                                        $db_line_item->shopify_fulfillment_order_id = $line_item->id;
                                        $db_line_item->save();
                                    }
                                }
                            }
                            $db_order->save();
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
//            $msg = new ErrorMessage();
//            $msg->message = "webhook order create sync_fulfillment_order_ids api data: " . json_encode($exception->getMessage());
//            $msg->save();
        }

    }

    public function sync_fulfillments()
    {
        $shop = Auth::user();
        $orders = Order::where('session_id', $shop->id)->select('shopify_order_id')->get();
        foreach ($orders as $order) {
            $fulfillment_apis = $this->getShopApi($shop->shop)->rest('GET', '/admin/orders/' . $order->shopify_order_id . '/fulfillments.json')['body']['fulfillments'];
            if ($fulfillment_apis->count()) {
                foreach ($fulfillment_apis as $fulfillment_api) {
                    $this->createUpdateFufillment($fulfillment_api, $shop);
                }
            }
        }
        return Redirect::tokenRedirect('orders', ['notice' => 'Fulfillments Synced Successfully !']);

    }

    public function createUpdateFufillment($fulfillment_api, $shop)
    {

        try {
            //        if (isset($shop->plan_id)) {
            $fulfillment_save = Fulfillment::where('session_id', $shop->id)
                ->where('fulfillment_id', $fulfillment_api->id)
                ->first();
            if ($fulfillment_save == null) {
                $fulfillment_save = new Fulfillment();
            }

            $country = null;
            $order = Order::where('shopify_order_id', $fulfillment_api->order_id)->first();
            if (json_decode($order->shipping_address) != '' && json_decode($order->shipping_address) != null) {
                $shipp_add = json_decode($order->shipping_address);
                $country = $shipp_add->country;
            } elseif (json_decode($order->billing_address) != '' && json_decode($order->billing_address) != null) {
                $bill_add = json_decode($order->billing_address);
                $country = $bill_add->country;
            }
            $fulfillment_tracking_company=$fulfillment_api->tracking_company;
//            if($fulfillment_api->tracking_company && $fulfillment_api->tracking_number){
//                $cnt = new FulfillmentController();
//                $fulfillment_tracking_company=$cnt->find_carrier($fulfillment_api->tracking_number,$fulfillment_api->tracking_company);
//            }


            $fulfillment_save->session_id = $shop->id;
            $fulfillment_save->fulfillment_id = $fulfillment_api->id;
            $fulfillment_save->shopify_order_id = $fulfillment_api->order_id;
            $fulfillment_save->location_id = $fulfillment_api->location_id;
            $fulfillment_save->status = $fulfillment_api->status;
            $fulfillment_save->service = $fulfillment_api->service;
            $fulfillment_save->tracking_number = $fulfillment_api->tracking_number;
            $fulfillment_save->tracking_company = $fulfillment_tracking_company;
//        $fulfillment_save->shipment_status = $fulfillment_api->shipment_status;
            $fulfillment_save->tracking_url = $fulfillment_api->tracking_url;
            if (isset($country) && $country != "") {
                $fulfillment_save->country = $country;
            }
            $fulfillment_save->name = $fulfillment_api->name;
            $fulfillment_save->line_items = json_encode($fulfillment_api->line_items);
            $fulfillment_save->created_at = Carbon::createFromTimeString($fulfillment_api->created_at)->format('Y-m-d H:i:s');
            $fulfillment_save->updated_at = Carbon::createFromTimeString($fulfillment_api->updated_at)->format('Y-m-d H:i:s');
            $fulfillment_save->save();
//        }
        } catch (\Exception $exception) {
//            $msg = new ErrorMessage();
//            $msg->message = "order create fulfillment webhook error:" . $exception->getMessage();
//            $msg->save();
        }


    }
    public function generateCourierImage($courierName, $courierCode)
    {
        // Create the courier folder if it doesn't exist
        $courierFolderPath = public_path('courier');
        if (!file_exists($courierFolderPath)) {
            mkdir($courierFolderPath, 0777, true);  // Ensure folder is writable
        }

        // Generate the image using the courier name
        $image = DefaultProfileImage::create($courierCode, 250, '#000000', '#ffffff');

        // Define the path to save the image
        $filePath = public_path("courier/{$courierCode}.png");

        // Save the image to the public/courier folder
        $image->save($filePath);

        return asset("courier/{$courierCode}.png");
    }
    public function sync_carriers()
    {
        $api_setting = ApiSetting::where('api_name', 'Tracktory')->where('status', 1)->first();
//        dump($api_setting);
        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.track123.com/gateway/open-api/tk/v2/courier/list',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Track123-Api-Secret: e198fe1eda804c57a7fa18d9103f7f81',
                    'Content-Type: application/json',
                    'User-Agent: PostmanRuntime/7.28.4'
                ),
                CURLOPT_SSL_VERIFYHOST => 0,  // Bypass SSL (for testing)
                CURLOPT_SSL_VERIFYPEER => 0,  // Bypass SSL (for testing)
                CURLOPT_VERBOSE => true       // Enable verbose output for debugging
            ));

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                dd( 'Error: ' . curl_error($curl));
            }
            $response = json_decode($response);
//            dd($response);
            if (isset($response->code) && ($response->code == 00000)) {
                foreach ($response->data as $data) {

//                   dd($img);
                    $carrier = Carrier::where('code', $data->courierCode)->first();
                    if ($carrier == null) {
                        $carrier = new Carrier();

                    }
                    $carrier->carrier_service_id = 1;
                    $carrier->name = $data->courierNameEN;
                    $carrier->code = $data->courierCode;
//                    $carrier->picture = $data->courier_logo;
                    $carrier->homepage = $data->courierHomePage;
//                    $carrier->type = $data->courier_type;
                    $carrier->save();
                   /* if($carrier->picture==null){
                        $img=$this->generateCourierImage($data->courierNameEN, $data->courierCode);
                        $carrier->picture = $img;
                        $carrier->save();
                    }*/
                }
//            return \redirect()->route('home');
            }
        }
    }
    public function sync_carriers_trackmore()
    {
        $api_setting = ApiSetting::where('status', 1)->first();
        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.trackingmore.com/v4/couriers/all',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    "Tracking-Api-Key: $api_setting->api_key",
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response);
//            dd($response);
            if (isset($response->meta->code) && ($response->meta->code == 200)) {
                foreach ($response->data as $data) {
                    $carrier = Carrier::where('code', $data->courier_code)->first();
                    if ($carrier == null) {
                        $carrier = new Carrier();
                    }
                    $carrier->name = $data->courier_name;
                    $carrier->code = $data->courier_code;
                    $carrier->picture = $data->courier_logo;
                    $carrier->homepage = $data->courier_url;
                    $carrier->type = $data->courier_type;
                    $carrier->save();
                }
//            return \redirect()->route('home');
            }
        }
    }
    public function sync_carriers_tracktry()
    {
        $api_setting = ApiSetting::first();
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.tracktry.com/v1/carriers',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Tracktry-Api-Key: $api_setting->api_key",
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);
        if (isset($response->meta->code) && ($response->meta->code == 200)) {
            foreach ($response->data as $data) {
                $carrier = Carrier::where('code', $data->code)->first();
                if ($carrier == null) {
                    $carrier = new Carrier();
                }
                $carrier->name = $data->name;
                $carrier->code = $data->code;
                $carrier->picture = $data->picture;
                $carrier->homepage = $data->homepage;
                $carrier->type = $data->type;
                $carrier->save();
            }
//            return \redirect()->route('home');
        }

    }

    public function webhooks()
    {
        $webhook = Auth::user()->api()->rest('GET', '/admin/webhooks.json');
        dd($webhook);
    }

    public function get_browser_name($user_agent)
    {
        $t = strtolower($user_agent);
        $t = " " . $t;
        if (strpos($t, 'opera') || strpos($t, 'opr/')) return 'Opera';
        elseif (strpos($t, 'edge')) return 'Edge';
        elseif (strpos($t, 'chrome')) return 'Chrome';
        elseif (strpos($t, 'safari')) return 'Safari';
        elseif (strpos($t, 'firefox')) return 'Firefox';
        elseif (strpos($t, 'msie') || strpos($t, 'trident/7')) return 'Internet Explorer';
        return 'Unkown';
    }

    public function getRealIpAddr()
    {
        if (isset($_SERVER['HTTP_X_SHOPIFY_CLIENT_IP'])) {
            return $_SERVER['HTTP_X_SHOPIFY_CLIENT_IP'];
        }
//        else if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
//        {
//            $ip = $_SERVER['HTTP_CLIENT_IP'];
//        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
//        {
//            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
//        } else {
//            $ip = $_SERVER['REMOTE_ADDR'];
//        }
//        return $ip;
    }


    public function custom_charge($order_count, $extra_order_count, $getcountorder)
    {
//        dd($shop,$extra_order_count,$getplan,$active_plan_charge);
        $shop = Auth::user();
        $getplan = Plan::where('id', $shop->plan_id)->first();

        $custom_charge_prev = CustomChargeLog::where('user_id', Auth::user()->id)->where('plan_id', $getplan->id)->where('order_status', 1)->first();
        if (isset($custom_charge_prev)) {
            //              $order_count
//            $extra_order_count = $order_count - $custom_charge_prev->order_count;
        } else {
            //              $order_count
//            $extra_order_count = $order_count - $getplan->orders;
//            if($extra_order_count == 0){
//                $extra_order_count = 1;
//            }
        }

        $total_charge = $extra_order_count * $getplan->per_order;
//        dd($total_charge);
        $query = 'mutation AppPurchaseOneTimeCreate($name: String!, $price: MoneyInput!, $returnUrl: URL!, $test: Boolean!){
                    appPurchaseOneTimeCreate(name: $name, returnUrl: $returnUrl, price: $price, test: $test) {
                      userErrors {
                        field
                        message
                      }
                      appPurchaseOneTime {
                        createdAt,
                        id,
                      },
                      confirmationUrl
                    }
                  }';

        $variables = [
            "name" => isset($getplan->terms) ? $getplan->terms : null,
//            trackify-local
//        trackify-7
            "returnUrl" => 'https://' . Auth::user()->name . '/admin/apps/trackify-7/orders',
            "test" => false,
            "price" => [
                "amount" => $total_charge,
                "currencyCode" => "USD"
            ]
        ];

        $response = $this->getShopApi($shop->shop)->graph($query, $variables);
//        dd($response);
        if ($response["errors"] == false) {
            $confirmation_url = $response['body']['data']['appPurchaseOneTimeCreate']['confirmationUrl'];
            $custom_charge = new CustomChargeLog();
            $custom_charge->user_id = $shop->id;
            $custom_charge->plan_id = $getplan->id;
            $custom_charge->total_charge_price = $total_charge;
            $custom_charge->per_order_price = $getplan->per_order;
            $custom_charge->custom_charge_response = json_encode($response['body']['data']['appPurchaseOneTimeCreate']);
//            $order_count
            $custom_charge->order_count = $order_count;
            $custom_charge->extra_order_count = $extra_order_count;
            $custom_charge->order_status = 0;
            $custom_charge->save();
            return view('module.single_charge', compact('confirmation_url', 'custom_charge', 'getcountorder'));
        }
    }


    public function ManualUpdateStoreOrderTrackings(Request $request)
    {
        //        $session_obj = $request->get('shopifySession');
//        $shop = Session::where('shop', $session_obj->getShop())->first();
        return true;
        $shop  = $this->getShop($request);
        $this->UpdateStoreOrderTrackings($shop, $request->datefilter);
        dd('done');
    }

    public function updateCarrier($shopify_order_id){
        $order = Order::where('shopify_order_id', $shopify_order_id)->first();
        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
            ]);
        }

        $shop = Session::find($order->session_id);
        $fulfillments = $order->fulfillments()->whereNotNull('tracking_number')
            ->whereNotNull('tracking_company')->get();

        if (!$fulfillments->count()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tracking number to refresh.',
            ]);
        }

        $fulfillment_controller = new \App\Http\Controllers\FulfillmentController();
        foreach ($fulfillments as $fulfillment) {
            $shipping_status = $fulfillment_controller->shipping_status(
                $fulfillment->fulfillment_id,
                $fulfillment->tracking_number,
                $fulfillment->tracking_company,
                $shop
            );

            if (isset($shipping_status) && isset($shipping_status->data) && !empty($shipping_status->data)) {
                if ($shipping_status != false) {
                    $fulfillment_controller->shippingStatusUpdate($shipping_status,$fulfillment);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Successfully refreshed!',
                    ]);
                }
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No tracking updates found.',
        ]);
    }
    public function UpdateStoreOrderTrackings($shop, $datefilter)
    {
        /*if (isset($datefilter) && $datefilter != "") {
            $date_range = null;
            if ($datefilter == '3days') {
                $date_range = [Carbon::now()->subDays(3), Carbon::now()];
            } elseif ($datefilter == '7days') {
                $date_range = [Carbon::now()->subDays(7), Carbon::now()];
            } elseif ($datefilter == '14days') {
                $date_range = [Carbon::now()->subDays(14), Carbon::now()];
            } elseif ($datefilter == '30days') {
                $date_range = [Carbon::now()->subDays(30), Carbon::now()];
            } elseif ($datefilter == '45days') {
                $date_range = [Carbon::now()->subDays(45), Carbon::now()];
            } elseif ($datefilter == '60days') {
                $date_range = [Carbon::now()->subDays(60), Carbon::now()];
            } else {
                $date_range = [Carbon::now()->subDays(30), Carbon::now()];
            }

            Order::where('session_id', $shop->id)->whereBetween('created_at', $date_range)->whereHas('fulfillments', function ($q) {
                $q->whereNotNull('tracking_number');
            })->chunk(100, function ($orders) {
                if ($orders->count()) {
                    foreach ($orders as $order) {

                        $order_fulfilments = $order->fulfillments()->get();

                        $country = null;
                        if (json_decode($order->shipping_address) != '' && json_decode($order->shipping_address) != null) {
                            $shipp_add = json_decode($order->shipping_address);
                            $country = $shipp_add->country;
                        } elseif (json_decode($order->billing_address) != '' && json_decode($order->billing_address) != null) {
                            $bill_add = json_decode($order->billing_address);
                            $country = $bill_add->country;
                        }

                        $fulfillment_controller = new FulfillmentController();
                        $klaviyo_controller = new KlaviyoController();
                        foreach ($order_fulfilments as $fulfillment) {
                            if (isset($fulfillment->tracking_number)) {
                                $tracking_company = null;

                                $tracking_company = Carrier::where(function ($query) use ($fulfillment) {
                                    $query->where('name', $fulfillment->tracking_company)->orWhere('code', $fulfillment->tracking_company);
                                })->first();

                                if (isset($tracking_company->code)) {
                                    $carrier_status = $fulfillment_controller->carrier_register($fulfillment->tracking_number, $tracking_company->code);

                                    if ($carrier_status === true || $carrier_status == "already exist") {
                                        $shop = Session::find($order->session_id);
                                        if ($carrier_status === true && isset($shop)) {
//                                            $fulfillment_controller->add_usage_charge($shop->shop);
                                        }
                                        //                    maitain the tracktory api success request logs
//                                        $common_controller = new CommonController();
//                                        $common_controller->api_statistics($order->session_id, $fulfillment->order_id, $fulfillment->fulfillment_id);

                                        $shipping_response = $fulfillment_controller->shipping_status($fulfillment->fulfillment_id, $fulfillment->tracking_number, $tracking_company->code);
//                                             dd($shipping_response);
                                        if ($shipping_response != false) {
                                            $fulfillment_controller->shippingStatusUpdate($shipping_response,$fulfillment,$shop,$country);

                                        }
                                    }
                                } else {
                                    $msg = new ErrorMessage();
                                    $msg->message = 'tracking code not exist, fulfillment_id' . isset($fulfillment->fulfillment_id) ? $fulfillment->fulfillment_id : null;
                                    $msg->save();
                                }


                            }
                        }
//                            $order->last_tracking_status_check_time = Carbon::now();
//                            $order->save();
                    }
                }
            });
        }*/
    }

    public function sync_order_fulfillments_tracking()
    {
//        return true;
//        dd(1);
//        try {
            Order::whereHas('fulfillments', function ($q) {
                $q->where('tracking_number', '!=', null);
                $q->where('shipment_status', '!=', 'delivered');
                $q->where('enable_tracking',  1);
            })->chunk(100, function ($orders) {
//                dd($orders);
                if ($orders->count()) {
                    foreach ($orders as $order) {
//                        dump('order');
                        try {
                            $last_status_updated_time_diff = now()->diffInDays(Carbon::parse($order->last_tracking_status_check_time));
                            if ($last_status_updated_time_diff > 0 || $order->last_tracking_status_check_time == null) {
                                $order_fulfilments = $order->fulfillments()->get();
                                $country = null;
//                            dump('updating tracking');
                                if (json_decode($order->shipping_address) != '' && json_decode($order->shipping_address) != null) {
                                    $shipp_add = json_decode($order->shipping_address);
                                    $country = $shipp_add->country;
                                } elseif (json_decode($order->billing_address) != '' && json_decode($order->billing_address) != null) {
                                    $bill_add = json_decode($order->billing_address);
                                    $country = $bill_add->country;
                                }


                                foreach ($order_fulfilments as $fulfillment) {

//                            if (isset($fulfillment->tracking_number) && isset($country) && (($fulfillment->shipment_status == null) || ($fulfillment->shipment_status != 'delivered'))) {
                                    if (isset($fulfillment->tracking_number)) {

                                        $fulfillment_controller = new FulfillmentController();

                                        $tracking_company = null;

//                                    $tracking_company = Carrier::where(function ($query) use ($fulfillment) {
//                                        $query->where('name', $fulfillment->tracking_company)->orWhere('code', $fulfillment->tracking_company);
//                                    })->first();
//
//                                    if (isset($tracking_company->code)) {
//                                    dump('updating tracking2');

                                        $shop = Session::find($order->session_id);
                                        if ($shop && $shop->plan_id) {
                                            $plan = Plan::where('id', $shop->plan_id)->first();
                                            $common_controller = new CommonController();
                                            $total_req = $common_controller->get_api_statistics($shop);
                                            $track_shipping = 0;
                                            $add_usagecharges = 0;
                                            if ($plan->response_limit > $total_req) {
                                                $track_shipping = 1;
                                            } elseif ($total_req >= $plan->response_limit && $plan->id != 1) {
                                                $track_shipping = 1;
                                                $add_usagecharges = 1;
                                            }
                                            $tracking_company_code = "";
                                            $original_carrier = $fulfillment->tracking_company;
                                            if ($track_shipping) {
//                                            dump('updating tracking3');

                                                $carrier_status = $fulfillment_controller->carrier_register($fulfillment->tracking_number, $original_carrier);
                                                $carrier_status = json_decode(json_encode($carrier_status), false);
//                                            dump($carrier_status);
                                                if (is_object($carrier_status) && ($carrier_status->response === true || $carrier_status->response == "already exist")) {
                                                    // Keep Shopify carrier name; do not overwrite from Track123.
                                                    $tracking_company_code = $original_carrier;
                                                    if ($carrier_status->response === true && isset($shop)) {

                                                        $common_controller->api_statistics($order->session_id, $fulfillment->order_id, $fulfillment->fulfillment_id);
                                                        if ($add_usagecharges || $plan->unlimited) {
                                                            $fulfillment_controller->add_usage_charge($shop->shop, $plan);
                                                        }

                                                    }
                                                }
                                            }
//                                        dd($carrier_status);
                                            //                    maitain the tracktory api success request logs
                                            $shipping_response = $fulfillment_controller->shipping_status(
                                                $fulfillment->fulfillment_id,
                                                $fulfillment->tracking_number,
                                                $tracking_company_code ?: $original_carrier,
                                                $shop
                                            );
//                                    dump($shipping_response);
                                            if ($shipping_response != false) {
                                                $fulfillment_controller->shippingStatusUpdate($shipping_response, $fulfillment, $shop, $country);
//                                            dump('data updated');
                                            }
//                                    }
                                        }

                                    }
                                }
                                $order->last_tracking_status_check_time = Carbon::now();
                                $order->save();
//                            dd(123);
                            }
                        }catch (\Exception $e){
                            $msg = new ErrorMessage();
                            $msg->message = 'error in update tracking  cron job: '.json_encode($e->getMessage()." line:".$e->getLine());
                            $msg->save();
                            continue;
                        }
                    }
                }
            });
        /*}
        catch (\Exception $exception) {
//            $msg = new ErrorMessage();
//            $msg->message = 'error in update order tracking detail cron job: ' . json_encode($exception->getMessage());
//            $msg->save();
            dd($exception->getMessage());
        }*/

//        return Redirect::tokenRedirect('orders', ['notice' => 'Order trackings sync successfully !']);
    }
    /*public function sync_order_fulfillments_tracking_old()
    {
//        try {
            Order::whereHas('fulfillments', function ($q) {
                $q->where('tracking_number', '!=', null);
            })->chunk(100, function ($orders) {
                if ($orders->count()) {
                    foreach ($orders as $order) {

                        $last_status_updated_time_diff = now()->diffInDays(Carbon::parse($order->last_tracking_status_check_time));
                        if ($last_status_updated_time_diff > 0 || $order->last_tracking_status_check_time == null) {
                            $order_fulfilments = $order->fulfillments()->get();
                            $country = null;

                            if (json_decode($order->shipping_address) != '' && json_decode($order->shipping_address) != null) {
                                $shipp_add = json_decode($order->shipping_address);
                                $country = $shipp_add->country;
                            } elseif (json_decode($order->billing_address) != '' && json_decode($order->billing_address) != null) {
                                $bill_add = json_decode($order->billing_address);
                                $country = $bill_add->country;
                            }


                            foreach ($order_fulfilments as $fulfillment) {

//                            if (isset($fulfillment->tracking_number) && isset($country) && (($fulfillment->shipment_status == null) || ($fulfillment->shipment_status != 'delivered'))) {
                                if (isset($fulfillment->tracking_number)) {

                                    $fulfillment_controller = new FulfillmentController();

                                    $tracking_company = null;

                                    $tracking_company = Carrier::where(function ($query) use ($fulfillment) {
                                        $query->where('name', $fulfillment->tracking_company)->orWhere('code', $fulfillment->tracking_company);
                                    })->first();

                                    if (isset($tracking_company->code)) {
                                        $carrier_status = $fulfillment_controller->carrier_register2($fulfillment->tracking_number, $tracking_company->code);


                                        if ($carrier_status === true || $carrier_status == "already exist") {
                                            $shop = Session::find($order->user_id);
                                            if ($carrier_status === true && isset($shop)) {
                                                $fulfillment_controller->add_usage_charge($shop->shop);
                                            }
                                            //                    maitain the tracktory api success request logs
                                            $common_controller = new CommonController();
                                            $common_controller->api_statistics($order->session_id, $fulfillment->order_id, $fulfillment->fulfillment_id);
                                            $shipping_response = $fulfillment_controller->shipping_status2($fulfillment->fulfillment_id, $fulfillment->tracking_number, $tracking_company->code);
                                            if ($shipping_response != false) {
                                                $count = (array)($shipping_response->data->items);

                                                if (!empty($count)) {
//                                        dump($count);
                                                    $count = count($count) - 1;
                                                    $fulfillment = Fulfillment::where('fulfillment_id', $fulfillment->fulfillment_id)->first();
                                                    $prev_shipment_status = $fulfillment->shipment_status;
                                                    if (isset($prev_shipment_status) && $prev_shipment_status != '') {

                                                    } else {
                                                        $prev_shipment_status = 'no status found';
                                                    }
//                                        dump($shipping_response->data->items[$count]->status);

                                                    $fulfillment->shipment_status = $shipping_response->data->items[$count]->status;

                                                    if (isset($shipping_response->data->items[$count]->origin_info->trackinfo)
                                                        && count($shipping_response->data->items[$count]->origin_info->trackinfo)) {
                                                        $data_count = count($shipping_response->data->items[$count]->origin_info->trackinfo);
                                                        $first_date = date_create($shipping_response->data->items[$count]->origin_info->trackinfo[$data_count - 1]->Date);
                                                        $fulfillment->first_date = date_format($first_date, "Y-m-d H:i:s");
                                                    }

                                                    if (($shipping_response->data->items[$count]->status == 'delivered')) {
                                                        $end_date=date_create($shipping_response->data->items[$count]->origin_info->trackinfo[0]->Date);
                                                        $fulfillment->last_date = date_format($end_date,"Y-m-d H:i:s");
                                                    }


                                                    if (isset($shipping_response->data->items[$count]->origin_info->trackinfo) &&
                                                        $shipping_response->data->items[$count]->origin_info->trackinfo != '') {
                                                        $fulfillment->track_info = json_encode($shipping_response->data->items[$count]->origin_info->trackinfo);
                                                    }


                                                    if (isset($shipping_response->data->items) && $shipping_response->data->items != '') {
                                                        $fulfillment->tracking_complete_info = json_encode($shipping_response->data->items);
                                                    }
                                                    if (isset($shipping_response->data->items[$count]->id) && $shipping_response->data->items[$count]->id != '') {
                                                        $fulfillment->shipment_id = $shipping_response->data->items[$count]->id;
                                                    }

                                                    if (isset($country) &&  $country != "") {
                                                        $fulfillment->country = $country;
                                                    }
//                                                    $msg = new ErrorMessage();
//                                                    $msg->message = 'tracking info saved ' . json_encode($tracking_company);
//                                                    $msg->save();

                                                    $shipment_register_time = null;
                                                    if(isset($shipping_response->data->items[$count]->origin_info->ItemReceived)){
                                                        $shipment_register_time = date_create($shipping_response->data->items[$count]->origin_info->ItemReceived);
                                                        $shipment_register_time = date_format($shipment_register_time,"Y-m-d H:i:s");
                                                    }
                                                    $fulfillment->shipment_register_time = $shipment_register_time;
                                                    $fulfillment->save();

                                                    $carrier_status = new Status();
                                                    $carrier_status->fulfillment_id = $fulfillment->fulfillment_id;
                                                    $carrier_status->tracking_number = $shipping_response->data->items[$count]->tracking_number;
                                                    $carrier_status->carrier_api_id = $shipping_response->data->items[$count]->id;
                                                    $carrier_status->carrier_code = $shipping_response->data->items[$count]->carrier_code;
                                                    $carrier_status->status = $shipping_response->data->items[$count]->status;
                                                    if (isset($shipping_response->data->items[$count]->origin_info->trackinfo) && $shipping_response->data->items[$count]->origin_info->trackinfo != '') {
                                                        $carrier_status->track_info = json_encode($shipping_response->data->items[$count]->origin_info->trackinfo);
                                                    }
                                                    $carrier_status->save();
//                                        if (($prev_shipment_status != 'pending') && ($prev_shipment_status != $shipping_response->data->items[$count]->status)) {
//                                            if (isset($order->email) && $order->email != '') {
//                                                Mail::to($order->email)->send(new \App\Mail\CheckShippingStatus($fulfillment, $order));
//                                            }
//                                        }
                                                }
                                            }
                                        }
                                    } else {
//                                        $msg = new ErrorMessage();
//                                        $msg->message = 'tracking code not exist, fulfillment_id' . isset($fulfillment->fulfillment_id) ? $fulfillment->fulfillment_id : null;
//                                        $msg->save();
                                    }


                                }
                            }
                            $order->last_tracking_status_check_time = Carbon::now();
                            $order->save();
                        }
                    }
                }
            });
        //}
        //catch (\Exception $exception) {
//            $msg = new ErrorMessage();
//            $msg->message = 'error in update order tracking detail cron job: ' . json_encode($exception->getMessage());
//            $msg->save();
       //     dd($exception->getMessage());
       // }

//        return Redirect::tokenRedirect('orders', ['notice' => 'Order trackings sync successfully !']);
    }*/

}
