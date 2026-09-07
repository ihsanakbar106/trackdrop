<?php

namespace App\Http\Controllers;


use App\Models\ApiSetting;
use App\Models\Carrier;
use App\Models\Charge;
use App\Models\Collection;
use App\Models\EmailLog;
use App\Models\ErrorMessage;
use App\Models\Fulfillment;
use App\Models\LineItem;
use App\Models\Order;
use App\Models\PageView;
use App\Models\Plan;
use App\Models\RecommendedProductView;
use App\Models\ReportStatus;
use App\Models\Session;
use App\Models\Status;
use App\Models\TrackingPage;
use App\Models\Translation;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use App\Services\ShopifyTokenService;
use Mockery\Exception;
use Shopify\Clients\Graphql;
use Shopify\Clients\Rest;
use Stichoza\GoogleTranslate\GoogleTranslate;

class FulfillmentController extends HelperController
{

    public function fulfillments(Request $request)
    {

//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);

        $all_shipment_statuses = ['all', 'pending','info received', 'transit', 'pickup', 'delivered', 'out for delivery', 'exception', 'expired'];

        $fulfillment_status_array = null;
        $carrier_array = null;
        $search = null;
        $date = null;

        $fulfillments = Fulfillment::with(['order', 'carrier_name_base', 'carrier_code_base'])->where('session_id', $session->id)->newQuery();
        $check_fulfillments = clone $fulfillments;

        $carriers = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('tracking_company')->select('tracking_company')->whereNotNull('tracking_company')
            ->distinct()->get();
        $shipment_statuses = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('shipment_status')->select('shipment_status')->whereNotNull('shipment_status')
            ->distinct()->get();
        $destinations = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('country')->select('country')->whereNotNull('country')
            ->distinct()->get();
        $origins = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('original_country')->select('original_country')->whereNotNull('original_country')
            ->distinct()->get();

        if ($request->input('search')) {
            $search = $request->search;

            if ($check_fulfillments->where('tracking_number', 'like', '%' . $search . '%')->exists()) {
                $fulfillments->where('tracking_number', 'like', '%' . $search . '%');
            } else {
                $fulfillments->whereHas('order', function ($q) use ($search) {
                    $q->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('customer_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
//                dd($fulfillments->get());
            }


//            $fulfillments->orWhere('tracking_number', 'like', '%' . $search . '%')
//                ->whereHas('order',function ($q) use ($search) {
//                    $q->where(function ($p) use ($search){
//                        $p->orWhere('name', 'like', '%' . $search . '%')
//                            ->orWhere('customer_name', 'like', '%' . $search . '%')
//                            ->orWhere('email', 'like', '%' . $search . '%');
//                    });
//                });
        }
        if (isset($request->select_all_shipment_status) && !empty($request->select_all_shipment_status)) {
            $shipment_status_array = explode(',', $request->select_all_shipment_status);
            if (in_array('all', $shipment_status_array)) {

            } elseif (in_array('pending', $shipment_status_array)) {
                $shipment_status_array[] = null;
                $shipment_status_array[] = 'notfound';

                $fulfillments->whereIn('shipment_status', $shipment_status_array)->orWhereNull('shipment_status');
            } else {
                $fulfillments->whereIn('shipment_status', $shipment_status_array);
            }
        }
        if (isset($request->shipment_status) && !empty($request->shipment_status)) {
            $shipment_status_array = explode(',', $request->shipment_status);
            if (in_array('pending', $shipment_status_array)) {
                $shipment_status_array[] = null;
                $shipment_status_array[] = 'notfound';

                $fulfillments->whereIn('shipment_status', $shipment_status_array)->orWhereNull('shipment_status');
            } else {
                $fulfillments->whereIn('shipment_status', $shipment_status_array);
            }
        }
        if (isset($request->destinations)) {
            $destinations_array = explode(',', $request->destinations);
            $fulfillments->whereIn('country', $destinations_array);
        }
        if (isset($request->origins)) {
            $origin_array = explode(',', $request->origins);
            $fulfillments->whereIn('original_country', $origin_array);
        }
        if (isset($request->carrier) && !empty($request->carrier)) {
            $carrier_array = explode(',', $request->carrier);
            $fulfillments->whereIn('tracking_company', $carrier_array);
        }
        if (isset($request->fulfillment_status)) {
            $fulfillment_status = $request->fulfillment_status;
            $fulfillments->whereHas('order', function ($p) use ($fulfillment_status) {
                $p->whereHas('lineitems', function ($q) use ($fulfillment_status) {
                    if ($fulfillment_status === 'fulfilled') {
                        $q->where('fulfillment_status', 'fulfilled');
                    } else {
                        $q->whereNull('fulfillment_status');
                    }
                });
            });
        }
        if (isset($request->note) && $request->note != "") {
            $note = $request->note;
            $fulfillments->whereHas('order', function ($p) use ($note) {
                if ($note == 'Without note') {
                    $p->whereNull('note');
                } elseif ($note == 'With note') {
                    $p->whereNotNull('note');
                }
            });
        }
        if (isset($request->order_date_datefilter)) {
            $datefilter = $request->query('order_date_datefilter');

            if ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7), Carbon::now()];
            } elseif ($datefilter == 'Today') {
                $date_range = [Carbon::now()->subDays(1), Carbon::now()];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30), Carbon::now()];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60), Carbon::now()];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90), Carbon::now()];
            } elseif ($datefilter == 'Custom') {
                $start_date = Carbon::parse($request->order_date_starting)->format('Y-m-d') . ' 00:00:00';
                $end_date = Carbon::parse($request->order_date_ending)->format('Y-m-d') . ' 23:59:59';
                $date_range = [$start_date, $end_date];
            }
            $fulfillments->whereBetween('created_at', $date_range);
        }
        if (isset($request->shipment_date_datefilter)) {
            $datefilter = $request->query('shipment_date_datefilter');

            if ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7), Carbon::now()];
            } elseif ($datefilter == 'Today') {
                $date_range = [Carbon::now()->subDays(1), Carbon::now()];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30), Carbon::now()];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60), Carbon::now()];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90), Carbon::now()];
            } elseif ($datefilter == 'Custom') {
                $start_date = Carbon::parse($request->shipment_date_starting)->format('Y-m-d') . ' 00:00:00';
                $end_date = Carbon::parse($request->shipment_date_ending)->format('Y-m-d') . ' 23:59:59';
                $date_range = [$start_date, $end_date];
            }
            $fulfillments->whereBetween('created_at', $date_range);
        }
        if (isset($request->last_update_datefilter)) {
            $datefilter = $request->query('last_update_datefilter');

            if ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7), Carbon::now()];
            } elseif ($datefilter == 'Today') {
                $date_range = [Carbon::now()->subDays(1), Carbon::now()];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30), Carbon::now()];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60), Carbon::now()];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90), Carbon::now()];
            } elseif ($datefilter == 'Custom') {
                $start_date = Carbon::parse($request->shipment_date_starting)->format('Y-m-d') . ' 00:00:00';
                $end_date = Carbon::parse($request->shipment_date_ending)->format('Y-m-d') . ' 23:59:59';
                $date_range = [$start_date, $end_date];
            }
            $fulfillments->whereBetween('shipment_last_update_time', $date_range);
        }
        if (isset($request->transit_time) && !empty($request->transit_time)) {
            $transit_times = $request->transit_time;
            $fulfillments->where(function ($q) use ($transit_times) {
                foreach ($transit_times as $index => $condition) {
                    $subQuery = function ($subQ) use ($condition) {
                        switch ($condition) {
                            case 'Fast 1-5':
                                $subQ->whereRaw('DATEDIFF(last_date, first_date) BETWEEN 1 AND 5');
                                break;
                            case 'Normal 6-11':
                                $subQ->whereRaw('DATEDIFF(last_date, first_date) BETWEEN 6 AND 11');
                                break;
                            case 'Slow (12 - 20)':
                                $subQ->whereRaw('DATEDIFF(last_date, first_date) BETWEEN 12 AND 20');
                                break;
                            case 'Very Slow (20~)':
                                $subQ->whereRaw('DATEDIFF(last_date, first_date) > 20');
                                break;
                        }
                    };

                    if ($index == 0) {
                        $q->where($subQuery);
                    } else {
                        $q->orWhere($subQuery);
                    }
                }
            });
        }
        $fulfillments = $fulfillments->orderBy('created_at', 'desc')->paginate(20);

        $data = array(
            'fulfillments' => $fulfillments,
            'fulfillment_status_array' => $fulfillment_status_array,
            'carrier_array' => $carrier_array,
            'search' => $search,
            'date' => $date,
            'carriers' => $carriers,
            'destinations' => $destinations,
            'origins' => $origins,
            'shipment_statuses' => $shipment_statuses,
            'all_shipment_statuses' => $all_shipment_statuses,
            'all_carriers' => Carrier::get(),
            'plan_id' => $session->plan_id
        );

        return response()->json($data);
    }

    public function fulfillment_detail(Request $request, $id)
    {

        $plans_status = null;
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);

        $charge = Charge::where('session_id', $session->id)->first();
        if (isset($charge)) {
            $plan_status = Charge::where('plan_id', $session->plan_id)->where('session_id', $session->id)
                ->where('status', 'ACTIVE')->first();
            if (isset($plan_status)) {
                $getplan = Plan::where('id', $plan_status->plan_id)->first();
//                $custom_charge_log = CustomChargeLog::where('session_id',$session->id)
//                    ->where('order_status',1)->where('plan_id',$plan_status->plan_id)->first();

                $originalDate = $plan_status->activated_on;
                $expiryDate = $plan_status->expires_on;
                if ($expiryDate == null) {
                    $next_due_date = date('Y-m-d H:i:s', strtotime($originalDate . ' +30 days'));
                    $getcountorder = Order::where('session_id', $session->id)
                        ->whereBetween('created_at', [$originalDate, $next_due_date])->count();
                } else {
                    $getcountorder = Order::where('session_id', $session->id)
                        ->whereBetween('created_at', [$originalDate, $expiryDate])->count();
                }

                $orders_count = 0;
//                if(isset($custom_charge_log)){
//                    $orders_count = $custom_charge_log->order_count;
//                }else{
//                    $orders_count = $getplan->orders;
//                }

                if ($getcountorder > $orders_count) {
                    $plans_status = 'inactive';
                } else {
                    $plans_status = 'active';
                }

            } else {
                $plans_status = 'inactive';
            }
        } else {
            $plans_status = 'inactive';
        }

        $fulfillment_data = Fulfillment::with('order', 'email_logs', 'order.lineitems')->find($id);

        $carrier_detail = null;
        if (isset($fulfillment_data) && isset($fulfillment_data->tracking_company)) {
            $carrier_detail = Carrier::where(function ($query) use ($fulfillment_data) {
                $query->where('name', $fulfillment_data->tracking_company)->orWhere('code', $fulfillment_data->tracking_company);
            })->first();
        }

        $data = [
            'fulfillment_data' => $fulfillment_data,
            'carrier_detail' => $carrier_detail,
            'plans_status' => $plans_status,
        ];

        return response()->json($data);

//        return view('module.order_detail', compact('order_data','line_item_data','plans_status'));
    }


    public function fulfill_items(Request $request, $shopify_order_id)
    {
        $line_items = null;
        //        $session_obj = $request->get('shopifySession');
//        $shop = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);

        $order_data = Order::with(['lineitems' => function ($q) {
            $q->whereNull('fulfillment_status');
        }])->where('session_id', $session->id)->where('shopify_order_id', $shopify_order_id)->first();

        $carriers = Carrier::get();
        $data = array(
            'order_data' => $order_data,
            'carriers' => $carriers,
        );
        return response()->json($data);
//        return view('module.fulfill_items')->with($data);

    }

    public function line_item_fulfilled(Request $request)
    {

        //        $session_obj = $request->get('shopifySession');
//        $shop = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $order = Order::where('session_id', $session->id)->where('shopify_order_id', $request->shopify_order_id)->first();

        if (isset($order)) {
            $line_item_which_contains_fulfillment_lineitem_ids = $order->lineitems()->whereNotNull('shopify_fulfillment_order_id')->get();

            if (isset($order->shopify_fulfillment_order_id) && !empty($line_item_which_contains_fulfillment_lineitem_ids)) {

                if (is_null($request['tracking_number'])) {
                    $data = [
                        "fulfillment" => [
                            "notify_customer" => true,
                            "tracking_info" => [
                                "number" => null,
                                "company" => null
                            ],
                            "line_items_by_fulfillment_order" => [

                            ],

                        ]
                    ];
                } //6122438719
                else {
                    $data = [
                        "fulfillment" => [
                            "notify_customer" => true,
                            "tracking_info" => [
                                "number" => $request['tracking_number'],
                                "company" => $request['shipping_carrier']
                            ],
                            "line_items_by_fulfillment_order" => [

                            ],


                        ]
                    ];
                }

                $line_item_ids = [];
                $fulfillment_line_item_ids = [];
                $quantity = [];

                if (gettype($request['line_items']) == 'string') {
                    $line_item_ids = explode(', ', $request['line_items']);
                    $quantity = explode(', ', $request['quantity']);
                } else {
                    $line_item_ids = $request['line_items'];
                    $quantity = $request['quantity'];
                }

                if (isset($order->shopify_fulfillment_order_id)) {

                    $fulfillment_order_line_items = [];

                    if (!empty($line_item_ids)) {

                        foreach ($line_item_ids as $index => $item) {

//                        $line_item = LineItem::where('session_id',$session->id)->where('shopify_lineitem_id',$item)->first();
                            $line_item = LineItem::where('session_id', $session->id)->where('shopify_lineitem_id', $item)->first();

                            if (isset($line_item) && $line_item->fulfillment_status != "fulfilled" && $quantity[$index] > 0) {
                                array_push($fulfillment_order_line_items, [
                                    "id" => $line_item->shopify_fulfillment_order_id,
                                    "quantity" => $quantity[$index],
                                ]);
                            }
                        }
                    }

                    if (!empty($fulfillment_order_line_items)) {
                        array_push($data['fulfillment']['line_items_by_fulfillment_order'], [
                            "fulfillment_order_id" => $order->shopify_fulfillment_order_id,
                            "fulfillment_order_line_items" => $fulfillment_order_line_items
                        ]);
//                    return response()->json($data);
                        $fulfillment_api_response = $this->getShopApi($shop->shop)->rest('post', '/admin/fulfillments.json', $data);

                        if ($fulfillment_api_response['errors'] === false) {
                            $fulfillment_api_response = json_decode(json_encode($fulfillment_api_response['body']['fulfillment']), false);
                            return response()->json([
                                'message' => 'Your order fulfillment request will be completed with in sometime!',
                                'status' => 'success',
                                'fulfillment_api_response:' => $fulfillment_api_response
                            ]);
                        } else {
                            return response()->json([
                                'message' => '1: Order fulfillment failed!',
                                'status' => 'error',
                                'fulfillment_api_response:' => $fulfillment_api_response
                            ]);
                        }


                    } else {
                        return response()->json([
                            'message' => '2: Order fulfillment failed!',
                            'status' => 'error'
                        ]);
                    }

                }

                return response()->json([
                    'message' => '3: Order fulfillment failed!',
                    'status' => 'error'
                ]);

            }
            return response()->json([
                'message' => '4: Order fulfillment failed!',
                'status' => 'error'
            ]);
        }

        return response()->json([
            'message' => 'Order fulfillment failed because order not found!',
            'status' => 'error'
        ]);
    }

    public function tracking_add(Request $request)
    {
        //        $session_obj = $request->get('shopifySession');
//        $shop = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);

        $order = Order::where('session_id', $session->id)->where('shopify_order_id', $request->shopify_order_id)->first();

        if (isset($order)) {
            $line_item_which_contains_fulfillment_lineitem_ids = $order->lineitems()->
            whereNotNull('shopify_fulfillment_order_id')->get();

            if (isset($order->shopify_fulfillment_order_id) &&
                !empty($line_item_which_contains_fulfillment_lineitem_ids)) {

                $data = [
                    "fulfillment" => [
                        "tracking_info" => [
                            "number" => $request['tracking_number'],
                            "company" => $request['shipping_carrier']
                        ],

                    ]
                ];

                $fulfillment_api_response = $this->getShopApi($shop->shop)->rest('post', "/admin/fulfillments/" . $request->fulfillment_id . "/update_tracking.json", $data);

                if ($fulfillment_api_response['errors'] === false) {
                    $fulfillment_api_response = json_decode(json_encode($fulfillment_api_response['body']['fulfillment']), false);
                    return response()->json([
                        'message' => 'Your tracking detail request will be added with in sometime!',
                        'status' => 'success',
                        'fulfillment_api_response:' => $fulfillment_api_response
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Order tracking detail failed!',
                        'status' => 'error',
                        'fulfillment_api_response:' => $fulfillment_api_response
                    ]);
                }

            }
        }
    }
    public function find_carrier($tracking_number,$courier_name)
    {
//Trackmore
        $api_setting = ApiSetting::where('status', 1)->first();

        $data = array(
            "tracking_number" => "$tracking_number",
        );

//        dump($data);
        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $data = json_encode($data);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.trackingmore.com/v4/couriers/detect',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
//                testing
//                'Tracktry-Api-Key: d49ea6dc-f182-4f43-92f5-9fedacf31625',
//                "tracktry-api-key: 7da2ec9e-fc9c-4252-97ba-8615e261acf9",
                    "Tracking-Api-Key: $api_setting->api_key",
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response);
//            dd($response);
            try{
                if(isset($response->data[0]->courier_code)){
                    return $response->data[0]->courier_name;
                }else{
                    return $courier_name;
                }
            }catch (\Exception $e){
                return $courier_name;
            }


        }
    }
    public function isCargoCarrier($carrierName): bool
    {
        if ($carrierName === null || $carrierName === '') {
            return false;
        }
        $normalized = strtolower(trim((string) $carrierName));
        $normalized = preg_replace('/\s+/', '', $normalized);

        // Exact match only — do NOT use starts_with("cargo").
        // Shopify list carriers like "Cargo Expreso GT", "Southwest Air Cargo" must NOT hit cargo.co.il.
        return in_array($normalized, [
            'cargo',
            'cargo.co.il',
            'cargocoil',
            'cargologistics',
        ], true);
    }

    public function getCargoApiSetting()
    {
        return ApiSetting::where('api_name', 'Cargo')->where('status', 1)->first();
    }

    public function getTracktoryApiSetting()
    {
        return ApiSetting::where('api_name', 'Tracktory')->where('status', 1)->first();
    }

    public function ensureCargoCarrier()
    {
        $carrier = Carrier::where('code', 'cargo')->first();
        if (!$carrier) {
            $carrier = Carrier::where('name', 'Cargo')->first();
        }
        if ($carrier) {
            return $carrier;
        }

        $carrier = new Carrier();
        $carrier->name = 'Cargo';
        $carrier->code = 'cargo';
        $carrier->carrier_service_id = 1;
        $carrier->save();

        return $carrier;
    }

    public function mapCargoStatusCode($statusCode): string
    {
        $code = (int) $statusCode;
        $map = [
            1 => 'pending',
            12 => 'pending',
            2 => 'transit',
            4 => 'transit',
            7 => 'transit',
            9 => 'transit',
            25 => 'transit',
            50 => 'out for delivery',
            51 => 'out for delivery',
            52 => 'out for delivery',
            3 => 'delivered',
            55 => 'delivered',
            5 => 'exception',
            8 => 'exception',
        ];

        return $map[$code] ?? 'pending';
    }

    /**
     * Normalize Cargo get-status / webhook payload into shippingStatusUpdate shape.
     */
    public function normalizeCargoStatusResponse($payload, $trackingNumber)
    {
        $data = is_array($payload) ? $payload : (array) $payload;
        if (isset($data['data']) && (is_array($data['data']) || is_object($data['data']))) {
            $data = (array) $data['data'];
        }

        $shipmentId = $data['shipment_id'] ?? $trackingNumber;
        $statusCode = $data['status_code'] ?? null;
        $statusText = $data['status_text_en'] ?? ($data['status_text'] ?? 'No info');
        $statusDate = $data['status_date'] ?? now()->format('Y-m-d H:i:s');
        $city = $data['city'] ?? '';
        $state = $data['state'] ?? '';
        $deliveryStatus = $this->mapCargoStatusCode($statusCode);

        return (object) [
            'data' => [
                (object) [
                    'id' => (string) $shipmentId,
                    'tracking_number' => (string) ($trackingNumber ?: $shipmentId),
                    'courier_code' => 'cargo',
                    'delivery_status' => $deliveryStatus,
                    'substatus' => $statusCode !== null ? (string) $statusCode : null,
                    'latest_event' => $statusText,
                    'latest_checkpoint_time' => $statusDate,
                    'origin_country' => $state ?: $city,
                    'origin_info' => (object) [
                        'trackinfo' => [
                            (object) [
                                'checkpoint_date' => $statusDate,
                                'tracking_detail' => $statusText,
                                'location' => trim($city . ' ' . $state),
                                'checkpoint_delivery_status' => $deliveryStatus,
                            ],
                        ],
                        'milestone_date' => (object) [],
                    ],
                ],
            ],
        ];
    }

    public function carrier_register_cargo($tracking_number)
    {
        // Docs require Bearer token + customer_code for Cargo shipment APIs.
        $api_setting = $this->getCargoApiSetting();
        if (!$api_setting || !$api_setting->api_key || !$api_setting->customer_code) {
            return [
                'response' => false,
                'courier_code' => '',
                'message' => 'Cargo API requires api_key (Bearer token) and customer_code in api_settings.',
            ];
        }

        $this->ensureCargoCarrier();

        return [
            'response' => true,
            'courier_code' => 'cargo',
        ];
    }

    public function shipping_status_cargo($fulfillment_id, $tracking_number, $shop = null)
    {
        $api_setting = $this->getCargoApiSetting();
        if (!$api_setting || !$api_setting->api_key || !$api_setting->customer_code) {
            return false;
        }

        $shipmentId = is_numeric($tracking_number) ? (int) $tracking_number : null;
        if ($shipmentId === null) {
            $fulfillment = Fulfillment::where('fulfillment_id', $fulfillment_id)->first();
            if ($fulfillment && is_numeric($fulfillment->shipment_id)) {
                $shipmentId = (int) $fulfillment->shipment_id;
            }
        }
        if ($shipmentId === null) {
            return false;
        }

        $client = new Client();
        try {
            $response = $client->request('POST', 'https://api-v2.cargo.co.il/api/shipments/get-status', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_setting->api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'shipment_id' => $shipmentId,
                    'customer_code' => (int) $api_setting->customer_code,
                ],
                'http_errors' => false,
                'timeout' => 60,
            ]);
            $status = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);
            if ($status < 200 || $status >= 300 || !is_array($body) || !empty($body['errors'])) {
                return false;
            }

            return $this->normalizeCargoStatusResponse($body, $tracking_number);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function registerCargoWebhook($webhookUrl = null)
    {
        $api_setting = $this->getCargoApiSetting();
        if (!$api_setting || !$api_setting->api_key || !$api_setting->customer_code) {
            return [
                'success' => false,
                'message' => 'Cargo api_settings row missing (api_name=Cargo, api_key, customer_code, status=1).',
            ];
        }

        $url = $webhookUrl ?: (rtrim(env('APP_URL'), '/') . '/api/webhooks/cargo-status-update');
        $client = new Client();
        try {
            $response = $client->request('POST', 'https://api-v2.cargo.co.il/api/webhooks/create', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_setting->api_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'type' => 'status-update',
                    'webhook_url' => $url,
                    'customer_code' => (int) $api_setting->customer_code,
                ],
                'http_errors' => false,
                'timeout' => 60,
            ]);
            $status = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);
            $ok = $status >= 200 && $status < 300;
            if (is_array($body)) {
                $first = isset($body[0]) && is_array($body[0]) ? $body[0] : $body;
                if (array_key_exists('errors', $first) && !empty($first['errors'])) {
                    $ok = false;
                }
            } else {
                $ok = false;
            }

            return [
                'success' => $ok,
                'status' => $status,
                'body' => $body,
                'webhook_url' => $url,
                'message' => $ok ? 'Webhook registered.' : 'Cargo rejected webhook registration.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function handleCargoStatusWebhook(Request $request)
    {
        $secret = env('CARGO_WEBHOOK_SECRET');
        if (empty($secret) && app()->environment('production')) {
            return response()->json(['status' => 'error', 'message' => 'Webhook secret not configured'], 503);
        }
        if (!empty($secret)) {
            $provided = $request->header('X-Cargo-Webhook-Secret')
                ?: $request->query('secret');
            if ($provided !== $secret) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
            }
        }

        $payload = $request->all();
        if (empty($payload)) {
            $decoded = json_decode($request->getContent(), true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        $data = $payload['data'] ?? $payload;
        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0]['data'] ?? $data[0];
        }
        $shipmentId = $data['shipment_id'] ?? ($payload['shipment_id'] ?? null);
        if ($shipmentId === null || $shipmentId === '') {
            return response()->json(['status' => 'error', 'message' => 'shipment_id required'], 422);
        }

        $shipmentIdStr = (string) $shipmentId;
        $matches = Fulfillment::query()
            ->where(function ($q) use ($shipmentIdStr) {
                $q->where('shipment_id', $shipmentIdStr)
                    ->orWhere('tracking_number', $shipmentIdStr);
            })
            ->where(function ($q) {
                $q->whereRaw('LOWER(tracking_company) LIKE ?', ['%cargo%'])
                    ->orWhere('tracking_company', 'cargo');
            })
            ->orderByDesc('updated_at')
            ->get();

        if ($matches->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Fulfillment not found'], 404);
        }
        if ($matches->count() > 1) {
            $sessionIds = $matches->pluck('session_id')->unique();
            if ($sessionIds->count() > 1) {
                return response()->json(['status' => 'error', 'message' => 'Ambiguous fulfillment match'], 409);
            }
        }

        $fulfillment = $matches->first();

        $shop = Session::find($fulfillment->session_id);
        $normalized = $this->normalizeCargoStatusResponse(
            ['data' => $data, 'errors' => false],
            $fulfillment->tracking_number ?: $shipmentIdStr
        );
        $this->shippingStatusUpdate($normalized, $fulfillment, $shop);

        return response()->json(['status' => 'success']);
    }

    public function carrier_register($tracking_number, $carrier=null)
    {
        if ($this->isCargoCarrier($carrier)) {
            return $this->carrier_register_cargo($tracking_number);
        }

//Track123
        $api_setting = $this->getTracktoryApiSetting();

        $data = [[
            "trackNo" => "$tracking_number",
//            "courier_code" => "$carrier",
        ]];

//        dump($data);
        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $data = json_encode($data);
//            dump($data);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.track123.com/gateway/open-api/tk/v2/track/import',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
//                testing
//                'Tracktry-Api-Key: d49ea6dc-f182-4f43-92f5-9fedacf31625',
//                "tracktry-api-key: 7da2ec9e-fc9c-4252-97ba-8615e261acf9",
                    "Track123-Api-Secret: $api_setting->api_key",
                    'Content-Type: application/json',
                    'User-Agent: PostmanRuntime/7.28.4'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response);
//            dd($response);
            if (!isset($response->code)) {
                return [
                    'response'=>false,
                    'courier_code'=>""
                ];
            } elseif ($response->code == 4101) {
                return [
                    'response'=>"already exist",
                    'courier_code'=>""
                ];
            } elseif ($response->code == 00000) {
                if (empty($response->data->accepted)) {
                    return [
                        'response'=>"already exist",
                        'courier_code'=>$response->data->rejected[0]->courierCode
                    ];
                }
//            $msg = new ErrorMessage();
//            $msg->message = $tracking_number.$carrier.' carrier register '. json_encode($response);
//            $msg->save();
                return [
                    'response'=>true,
                    'courier_code'=>$response->data->accepted[0]->courierCode
                ];
            } else {
                return [
                    'response'=>false,
                    'courier_code'=>""
                ];
            }
        } else {
            return [
                'response'=>false,
                'courier_code'=>""
            ];
        }
    }
    public function carrier_register1($tracking_number, $carrier)
    {
//Trackmore
        $api_setting = ApiSetting::where('status', 1)->first();

        $data = array(
            "tracking_number" => "$tracking_number",
            "courier_code" => "$carrier",
        );

//        dump($data);
        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $data = json_encode($data);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.trackingmore.com/v4/trackings/create',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
//                testing
//                'Tracktry-Api-Key: d49ea6dc-f182-4f43-92f5-9fedacf31625',
//                "tracktry-api-key: 7da2ec9e-fc9c-4252-97ba-8615e261acf9",
                    "Tracking-Api-Key: $api_setting->api_key",
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response);
//            dd($response);
            if (!isset($response->meta->code)) {
                return false;
            } elseif ($response->meta->code == 4101) {
                return 'already exist';
            } elseif ($response->meta->code == 200) {
                if (empty($response->data)) {
                    return 'already exist';
                }
//            $msg = new ErrorMessage();
//            $msg->message = $tracking_number.$carrier.' carrier register '. json_encode($response);
//            $msg->save();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function carrier_register2($tracking_number, $carrier)
    {
        //Tracktry
        $api_setting = ApiSetting::where('status', 1)->first();

        $data = array(
            "tracking_number" => "$tracking_number",
            "carrier_code" => "$carrier",
        );
//        dump($data);
        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $data = json_encode($data);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://api.tracktry.com/v1/trackings/post',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
//                testing
//                'Tracktry-Api-Key: d49ea6dc-f182-4f43-92f5-9fedacf31625',
//                "tracktry-api-key: 7da2ec9e-fc9c-4252-97ba-8615e261acf9",
                    "Tracktry-Api-Key: $api_setting->api_key",
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response);

            if (!isset($response->meta->code)) {
                return false;
            } elseif ($response->meta->code == 4016) {
                return 'already exist';
            } elseif ($response->meta->code == 200) {
                if (empty($response->data)) {
                    return 'already exist';
                }
//            $msg = new ErrorMessage();
//            $msg->message = $tracking_number.$carrier.' carrier register '. json_encode($response);
//            $msg->save();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function shipping_status_trackmore($fulfillment_id, $tracking_number, $carrier)
    {

        $api_setting = ApiSetting::where('status', 1)->first();
        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $data = array(
                "tracking_number" => "$tracking_number",
                "courier_code" => "$carrier"
            );
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.trackingmore.com/v4/trackings/get?tracking_numbers=$tracking_number",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
//                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "postman-token: 1f29af0c-a885-168c-3c54-09637c0c56fc",
//testing key
//                "tracktry-api-key: d49ea6dc-f182-4f43-92f5-9fedacf31625"
//            live keys
//                "tracktry-api-key: 7da2ec9e-fc9c-4252-97ba-8615e261acf9"
                    "Tracking-Api-Key: $api_setting->api_key"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response);
//            dd($response);
//        $msg = new ErrorMessage();
//        $msg->message = $tracking_number.$carrier.'shipping status response '. json_encode($response);
//        $msg->save();
//        dd($response);
            if (isset($response->meta) && $response->meta->code == 200) {
                return $response;
            } else {
                return false;
            }
        } else {
            return false;
        }


    }
    public function shipping_status($fulfillment_id, $tracking_number, $carrier,$shop)
    {
        if ($this->isCargoCarrier($carrier)) {
            return $this->shipping_status_cargo($fulfillment_id, $tracking_number, $shop);
        }

        $api_setting = $this->getTracktoryApiSetting();
        $exclude_keywords=[];
//        if($shop) {
            if ($shop && $shop->dropshipping_mode && $shop->dropshipping_keyword) {
                $exclude_keywords = explode(',', $shop->dropshipping_keyword);

            }
//        }

        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $data = [
                "trackNos" => ["$tracking_number"]
//                "courier_code" => "$carrier"
            ];
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.track123.com/gateway/open-api/tk/v2/track/query",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 600,
                CURLOPT_CONNECTTIMEOUT => 30, // Connection timeout
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "Connection: keep-alive",
                    'User-Agent: PostmanRuntime/7.28.4',
//testing key
//                "tracktry-api-key: d49ea6dc-f182-4f43-92f5-9fedacf31625"
//            live keys
//                "tracktry-api-key: 7da2ec9e-fc9c-4252-97ba-8615e261acf9"
                    "Track123-Api-Secret: $api_setting->api_key"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response);
//            dd($response);
//        $msg = new ErrorMessage();
//        $msg->message = $tracking_number.$carrier.'shipping status response '. json_encode($response);
//        $msg->save();
//            return ($response);
            if (isset($response->code) && $response->code == 00000) {
                if(empty($response->data->accepted)){
                    return false;
                }
//                dd($response);
//                return ($response);

                $tracking_id=$response->traceId;
                $response = json_decode(json_encode($response),true);
                $transformedData = array_map(function ($content) use ($tracking_id,$exclude_keywords) {

                    $delivery_status=isset($content['transitStatus']) ? ($content['transitStatus']) : "pending";
                    $delivery_status_c=$delivery_status;

                    if($delivery_status){
                        if($delivery_status=="INIT"){
                            $delivery_status="pending";
                        }elseif($delivery_status=="NO_RECORD"){
                            $delivery_status="pending";
                        }elseif($delivery_status=="INFO_RECEIVED"){
                            $delivery_status="INFO RECEIVED";
                        }elseif($delivery_status=="IN_TRANSIT"){
                            $delivery_status="TRANSIT";
                        }elseif($delivery_status=="WAITING_DELIVERY"){
                            $delivery_status="OUT FOR DELIVERY";
                        }elseif($delivery_status=="DELIVERY_FAILED" || $delivery_status=="ABNORMAL"){
                            $delivery_status="exception";
                        }
                        $delivery_status=strtolower($delivery_status);
                    }
                    if($delivery_status_c=="NO_RECORD"){
                        return [
                            'id' => $tracking_id,
                            'tracking_number' => isset($content['trackNo']) ? $content['trackNo'] : null,
                            'courier_code' => isset($content['localLogisticsInfo']['courierCode']) ? $content['localLogisticsInfo']['courierCode'] : null,
                            'order_number' => isset($content['id']) ? $content['id'] : null,
                            'order_date' => isset($content['orderTime']) ? $content['orderTime'] : null, // Map if available
                            'created_at' => isset($content['createTime']) ? $content['createTime'] : now()->toIso8601String(),
                            'update_at' => isset($content['lastTrackingTime']) ? $content['lastTrackingTime'] : now()->toIso8601String(),
                            'delivery_status' => $delivery_status,
                            'archived' => 'tracking',
                            'updating' => false,
                            'source' => 'API',
                            'destination_country' => isset($content['shipTo']) ? $content['shipTo'] : null,
                            'destination_state' => '', // Hardcoded for example
                            'destination_city' => '', // Hardcoded for example
                            'origin_country' => isset($content['shipFrom']) ? $content['shipFrom'] : null,
                            'origin_state' => '', // Hardcoded for example
                            'origin_city' => '', // Hardcoded for example
                            'tracking_postal_code' => null,
                            'tracking_ship_date' => null,
                            'tracking_destination_country' => null,
                            'tracking_origin_country' => null,
                            'tracking_key' => null,
                            'tracking_courier_account' => null,
                            'customer_name' => null,
                            'customer_email' => null,
                            'customer_sms' => null,
                            'recipient_postcode' => null,
                            'order_id' => isset($content['id']) ? $content['id'] : null,
                            'title' => null,
                            'logistics_channel' => null,
                            'note' => null,
                            'label' => null,
                            'signed_by' => '', // Hardcoded for example
                            'service_code' => isset($content['shipmentType']) ? $content['shipmentType'] : null,
                            'weight' => null,
                            'weight_kg' => null,
                            'product_type' => null,
                            'pieces' => null,
                            'dimension' => null,
                            'previously' => null,
                            'destination_track_number' => null,
                            'exchange_number' => null,
                            'scheduled_delivery_date' => null,
                            'scheduled_address' => null,
                            'substatus' => isset($content['transitSubStatus']) ? strtolower($content['transitSubStatus']) : null,
                            'status_info' => null,
                            'latest_event' => null,
                            'latest_checkpoint_time' => null,
                            'transit_time' => 0,
                            'origin_info' => [
                                'courier_code' => isset($content['localLogisticsInfo']['courierCode']) ? $content['localLogisticsInfo']['courierCode'] : null,
                                'courier_phone' => '',
                                'weblink' => isset($content['localLogisticsInfo']['courierHomePage']) ? $content['localLogisticsInfo']['courierHomePage'] : null,
                                'reference_number' => null,
                                'milestone_date' => [
                                    'inforeceived_date' =>  null,
                                    'pickup_date' => null,
                                    'outfordelivery_date' => null,
                                    'delivery_date' => null,
                                    'returning_date' => null,
                                    'returned_date' => null
                                ],
                                'trackinfo' =>  []
                            ],
                            'destination_info' => [
                                'courier_code' => null,
                                'courier_phone' => null,
                                'weblink' => null,
                                'reference_number' => null,
                                'milestone_date' => [
                                    'inforeceived_date' => null,
                                    'pickup_date' => null,
                                    'outfordelivery_date' => null,
                                    'delivery_date' => null,
                                    'returning_date' => null,
                                    'returned_date' => null
                                ],
                                'trackinfo' => []
                            ]
                        ];
                    }
                    $reversedTrackingDetails = array_reverse($content['localLogisticsInfo']['trackingDetails']);
                    $statuses = [
                        'INFO_RECEIVED_01' => null,
                        'IN_TRANSIT_01' => null,
                        'WAITING_DELIVERY' => null,
                    ];
                    foreach ($reversedTrackingDetails as $detail){
                        $status = $detail['transitSubStatus'];
                        // Check if it's INFO_RECEIVED_01 or IN_TRANSIT_01
                        if ($status === 'INFO_RECEIVED_01' && !$statuses['INFO_RECEIVED_01']) {
                            $statuses['INFO_RECEIVED_01'] = $detail['eventTime'];
                        } elseif ($status === 'IN_TRANSIT_01' && !$statuses['IN_TRANSIT_01']) {
                            $statuses['IN_TRANSIT_01'] = $detail['eventTime'];
                        }

                        // Handle WAITING_DELIVERY_01 and WAITING_DELIVERY_02
                        if (str_starts_with($status, 'WAITING_DELIVERY')) {
                            if ($status === 'WAITING_DELIVERY_01') {
                                $statuses['WAITING_DELIVERY'] = $detail['eventTime'];
                            } elseif ($status === 'WAITING_DELIVERY_02' && !$statuses['WAITING_DELIVERY']) {
                                $statuses['WAITING_DELIVERY'] = $detail['eventTime'];
                            } elseif ($status === 'WAITING_DELIVERY_03' && !$statuses['WAITING_DELIVERY']) {
                                $statuses['WAITING_DELIVERY'] = $detail['eventTime'];
                            }
                        }
                    }
//                    return $statuses;
                    $info_received_date=isset($content['orderTime']) ? $content['orderTime'] : null;
                    $pickup_date=isset($statuses['IN_TRANSIT_01']) ? $statuses['IN_TRANSIT_01'] : null;
                    $outfordelivery_date=isset($statuses['WAITING_DELIVERY']) ? $statuses['WAITING_DELIVERY'] : null;
                    $delivery_date=isset($content['deliveredTime']) ? $content['deliveredTime'] : null;
                    $returning_date=null;
                    $returned_date=null;
                    return [
                        'id' => $tracking_id,
                        'tracking_number' => isset($content['trackNo']) ? $content['trackNo'] : null,
                        'courier_code' => isset($content['localLogisticsInfo']['courierCode']) ? $content['localLogisticsInfo']['courierCode'] : null,
                        'order_number' => isset($content['id']) ? $content['id'] : null,
                        'order_date' => isset($content['orderTime']) ? $content['orderTime'] : null, // Map if available
                        'created_at' => isset($content['createTime']) ? $content['createTime'] : now()->toIso8601String(),
                        'update_at' => isset($content['lastTrackingTime']) ? $content['lastTrackingTime'] : now()->toIso8601String(),
                        'delivery_status' => $delivery_status,
                        'archived' => 'tracking',
                        'updating' => false,
                        'source' => 'API',
                        'destination_country' => isset($content['shipTo']) ? $content['shipTo'] : null,
                        'destination_state' => '', // Hardcoded for example
                        'destination_city' => '', // Hardcoded for example
                        'origin_country' => isset($content['shipFrom']) ? $content['shipFrom'] : null,
                        'origin_state' => '', // Hardcoded for example
                        'origin_city' => '', // Hardcoded for example
                        'tracking_postal_code' => null,
                        'tracking_ship_date' => null,
                        'tracking_destination_country' => null,
                        'tracking_origin_country' => null,
                        'tracking_key' => null,
                        'tracking_courier_account' => null,
                        'customer_name' => null,
                        'customer_email' => null,
                        'customer_sms' => null,
                        'recipient_postcode' => null,
                        'order_id' => isset($content['id']) ? $content['id'] : null,
                        'title' => null,
                        'logistics_channel' => null,
                        'note' => null,
                        'label' => null,
                        'signed_by' => '', // Hardcoded for example
                        'service_code' => isset($content['shipmentType']) ? $content['shipmentType'] : null,
                        'weight' => null,
                        'weight_kg' => null,
                        'product_type' => null,
                        'pieces' => null,
                        'dimension' => null,
                        'previously' => null,
                        'destination_track_number' => null,
                        'exchange_number' => null,
                        'scheduled_delivery_date' => null,
                        'scheduled_address' => null,
                        'substatus' => isset($content['transitSubStatus']) ? strtolower($content['transitSubStatus']) : null,
                        'status_info' => null,
                        'latest_event' => isset($content['localLogisticsInfo']['trackingDetails'][0]['eventDetail'])
                            ? $content['localLogisticsInfo']['trackingDetails'][0]['eventDetail'] . ',' . $content['localLogisticsInfo']['trackingDetails'][0]['address'] . ',' . $content['localLogisticsInfo']['trackingDetails'][0]['eventTime']
                            : null,
                        'latest_checkpoint_time' => isset($content['localLogisticsInfo']['trackingDetails'][0]['eventTimeZeroUTC'])
                            ? $content['localLogisticsInfo']['trackingDetails'][0]['eventTimeZeroUTC']
                            : null,
                        'transit_time' => 0,
                        'origin_info' => [
                            'courier_code' => isset($content['localLogisticsInfo']['courierCode']) ? $content['localLogisticsInfo']['courierCode'] : null,
                            'courier_phone' => '',
                            'weblink' => isset($content['localLogisticsInfo']['courierHomePage']) ? $content['localLogisticsInfo']['courierHomePage'] : null,
                            'reference_number' => null,
                            'milestone_date' => [
                                'inforeceived_date' =>  $info_received_date,
                                'pickup_date' => $pickup_date,
                                'outfordelivery_date' => $outfordelivery_date,
                                'delivery_date' => $delivery_date,
                                'returning_date' => $returning_date,
                                'returned_date' => $returned_date
                            ],
                            'trackinfo' => isset($content['localLogisticsInfo']['trackingDetails'])
                                ? array_map(function ($detail) use($exclude_keywords) {
                                    return [
                                        'checkpoint_date' => isset($detail['eventTimeZeroUTC']) ? $detail['eventTimeZeroUTC'] : null,
                                        'checkpoint_delivery_status' => isset($detail['eventDetail']) ? strtolower($detail['eventDetail']) : null,
                                        'checkpoint_delivery_substatus' => isset($detail['transitSubStatus']) ? strtolower($detail['transitSubStatus']) : null,
                                        'tracking_detail' => isset($detail['eventDetail']) ? str_ireplace($exclude_keywords, '', $detail['eventDetail']): null,
                                        'location' => isset($detail['address']) ? str_ireplace($exclude_keywords, '', $detail['address']): null,
                                        'country_iso2' => '',
                                        'state' => '',
                                        'city' => '',
                                        'zip' => '',
                                        'raw_status' => null,
                                    ];
                                }, $content['localLogisticsInfo']['trackingDetails'])
                                : []
                        ],
                        'destination_info' => [
                            'courier_code' => null,
                            'courier_phone' => null,
                            'weblink' => null,
                            'reference_number' => null,
                            'milestone_date' => [
                                'inforeceived_date' => null,
                                'pickup_date' => null,
                                'outfordelivery_date' => null,
                                'delivery_date' => null,
                                'returning_date' => null,
                                'returned_date' => null
                            ],
                            'trackinfo' => []
                        ]
                    ];
                }, $response['data']['accepted']['content']);

                $data=['data'=>$transformedData];

                $data = json_decode(json_encode($data),false);

//                dd($exclude_keywords,$data);
                return $data;
            } else {
                return false;
            }
        } else {
            return false;
        }


    }
    public function shipping_status_test($fulfillment_id, $tracking_number, $carrier,$shop)
    {

        $api_setting = ApiSetting::where('status', 1)->first();
        $exclude_keywords=[];
//        if($shop) {
            if ($shop->dropshipping_mode && $shop->dropshipping_keyword) {
                $exclude_keywords = explode(',', $shop->dropshipping_keyword);

            }
//        }

        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $data = [
                "trackNos" => ["$tracking_number"]
//                "courier_code" => "$carrier"
            ];
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.track123.com/gateway/open-api/tk/v2/track/query",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 600,
                CURLOPT_CONNECTTIMEOUT => 30, // Connection timeout
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "Connection: keep-alive",
                    'User-Agent: PostmanRuntime/7.28.4',
//testing key
//                "tracktry-api-key: d49ea6dc-f182-4f43-92f5-9fedacf31625"
//            live keys
//                "tracktry-api-key: 7da2ec9e-fc9c-4252-97ba-8615e261acf9"
                    "Track123-Api-Secret: $api_setting->api_key"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response);
//            dd($response);
//        $msg = new ErrorMessage();
//        $msg->message = $tracking_number.$carrier.'shipping status response '. json_encode($response);
//        $msg->save();
            return ($response);
            if (isset($response->code) && $response->code == 00000) {
                if(empty($response->data->accepted)){
                    return false;
                }
//                dd($response);
//                return ($response);

                $tracking_id=$response->traceId;
                $response = json_decode(json_encode($response),true);
                $transformedData = array_map(function ($content) use ($tracking_id,$exclude_keywords) {

                    $delivery_status=isset($content['transitStatus']) ? ($content['transitStatus']) : "pending";
                    $delivery_status_c=$delivery_status;
                    if($delivery_status){
                        if($delivery_status=="INIT"){
                            $delivery_status="pending";
                        }elseif($delivery_status=="NO_RECORD"){
                            $delivery_status="pending";
                        }elseif($delivery_status=="INFO_RECEIVED"){
                            $delivery_status="INFO RECEIVED";
                        }elseif($delivery_status=="IN_TRANSIT"){
                            $delivery_status="TRANSIT";
                        }elseif($delivery_status=="WAITING_DELIVERY"){
                            $delivery_status="OUT FOR DELIVERY";
                        }elseif($delivery_status=="DELIVERY_FAILED" || $delivery_status=="ABNORMAL"){
                            $delivery_status="exception";
                        }
                        $delivery_status=strtolower($delivery_status);
                    }
                    if($delivery_status_c=="NO_RECORD"){
                        return [
                            'id' => $tracking_id,
                            'tracking_number' => isset($content['trackNo']) ? $content['trackNo'] : null,
                            'courier_code' => isset($content['localLogisticsInfo']['courierCode']) ? $content['localLogisticsInfo']['courierCode'] : null,
                            'order_number' => isset($content['id']) ? $content['id'] : null,
                            'order_date' => isset($content['orderTime']) ? $content['orderTime'] : null, // Map if available
                            'created_at' => isset($content['createTime']) ? $content['createTime'] : now()->toIso8601String(),
                            'update_at' => isset($content['lastTrackingTime']) ? $content['lastTrackingTime'] : now()->toIso8601String(),
                            'delivery_status' => $delivery_status,
                            'archived' => 'tracking',
                            'updating' => false,
                            'source' => 'API',
                            'destination_country' => isset($content['shipTo']) ? $content['shipTo'] : null,
                            'destination_state' => '', // Hardcoded for example
                            'destination_city' => '', // Hardcoded for example
                            'origin_country' => isset($content['shipFrom']) ? $content['shipFrom'] : null,
                            'origin_state' => '', // Hardcoded for example
                            'origin_city' => '', // Hardcoded for example
                            'tracking_postal_code' => null,
                            'tracking_ship_date' => null,
                            'tracking_destination_country' => null,
                            'tracking_origin_country' => null,
                            'tracking_key' => null,
                            'tracking_courier_account' => null,
                            'customer_name' => null,
                            'customer_email' => null,
                            'customer_sms' => null,
                            'recipient_postcode' => null,
                            'order_id' => isset($content['id']) ? $content['id'] : null,
                            'title' => null,
                            'logistics_channel' => null,
                            'note' => null,
                            'label' => null,
                            'signed_by' => '', // Hardcoded for example
                            'service_code' => isset($content['shipmentType']) ? $content['shipmentType'] : null,
                            'weight' => null,
                            'weight_kg' => null,
                            'product_type' => null,
                            'pieces' => null,
                            'dimension' => null,
                            'previously' => null,
                            'destination_track_number' => null,
                            'exchange_number' => null,
                            'scheduled_delivery_date' => null,
                            'scheduled_address' => null,
                            'substatus' => isset($content['transitSubStatus']) ? strtolower($content['transitSubStatus']) : null,
                            'status_info' => null,
                            'latest_event' => null,
                            'latest_checkpoint_time' => null,
                            'transit_time' => 0,
                            'origin_info' => [
                                'courier_code' => isset($content['localLogisticsInfo']['courierCode']) ? $content['localLogisticsInfo']['courierCode'] : null,
                                'courier_phone' => '',
                                'weblink' => isset($content['localLogisticsInfo']['courierHomePage']) ? $content['localLogisticsInfo']['courierHomePage'] : null,
                                'reference_number' => null,
                                'milestone_date' => [
                                    'inforeceived_date' =>  null,
                                    'pickup_date' => null,
                                    'outfordelivery_date' => null,
                                    'delivery_date' => null,
                                    'returning_date' => null,
                                    'returned_date' => null
                                ],
                                'trackinfo' =>  []
                            ],
                            'destination_info' => [
                                'courier_code' => null,
                                'courier_phone' => null,
                                'weblink' => null,
                                'reference_number' => null,
                                'milestone_date' => [
                                    'inforeceived_date' => null,
                                    'pickup_date' => null,
                                    'outfordelivery_date' => null,
                                    'delivery_date' => null,
                                    'returning_date' => null,
                                    'returned_date' => null
                                ],
                                'trackinfo' => []
                            ]
                        ];
                    }
                    $reversedTrackingDetails = array_reverse($content['localLogisticsInfo']['trackingDetails']);
                    $statuses = [
                        'INFO_RECEIVED_01' => null,
                        'IN_TRANSIT_01' => null,
                        'WAITING_DELIVERY' => null,
                    ];
                    foreach ($reversedTrackingDetails as $detail){
                        $status = $detail['transitSubStatus'];
                        // Check if it's INFO_RECEIVED_01 or IN_TRANSIT_01
                        if ($status === 'INFO_RECEIVED_01' && !$statuses['INFO_RECEIVED_01']) {
                            $statuses['INFO_RECEIVED_01'] = $detail['eventTime'];
                        } elseif ($status === 'IN_TRANSIT_01' && !$statuses['IN_TRANSIT_01']) {
                            $statuses['IN_TRANSIT_01'] = $detail['eventTime'];
                        }

                        // Handle WAITING_DELIVERY_01 and WAITING_DELIVERY_02
                        if (str_starts_with($status, 'WAITING_DELIVERY')) {
                            if ($status === 'WAITING_DELIVERY_01') {
                                $statuses['WAITING_DELIVERY'] = $detail['eventTime'];
                            } elseif ($status === 'WAITING_DELIVERY_02' && !$statuses['WAITING_DELIVERY']) {
                                $statuses['WAITING_DELIVERY'] = $detail['eventTime'];
                            } elseif ($status === 'WAITING_DELIVERY_03' && !$statuses['WAITING_DELIVERY']) {
                                $statuses['WAITING_DELIVERY'] = $detail['eventTime'];
                            }
                        }
                    }
//                    return $statuses;
                    $info_received_date=isset($content['orderTime']) ? $content['orderTime'] : null;
                    $pickup_date=isset($statuses['IN_TRANSIT_01']) ? $statuses['IN_TRANSIT_01'] : null;
                    $outfordelivery_date=isset($statuses['WAITING_DELIVERY']) ? $statuses['WAITING_DELIVERY'] : null;
                    $delivery_date=isset($content['deliveredTime']) ? $content['deliveredTime'] : null;
                    $returning_date=null;
                    $returned_date=null;
                    return [
                        'id' => $tracking_id,
                        'tracking_number' => isset($content['trackNo']) ? $content['trackNo'] : null,
                        'courier_code' => isset($content['localLogisticsInfo']['courierCode']) ? $content['localLogisticsInfo']['courierCode'] : null,
                        'order_number' => isset($content['id']) ? $content['id'] : null,
                        'order_date' => isset($content['orderTime']) ? $content['orderTime'] : null, // Map if available
                        'created_at' => isset($content['createTime']) ? $content['createTime'] : now()->toIso8601String(),
                        'update_at' => isset($content['lastTrackingTime']) ? $content['lastTrackingTime'] : now()->toIso8601String(),
                        'delivery_status' => $delivery_status,
                        'archived' => 'tracking',
                        'updating' => false,
                        'source' => 'API',
                        'destination_country' => isset($content['shipTo']) ? $content['shipTo'] : null,
                        'destination_state' => '', // Hardcoded for example
                        'destination_city' => '', // Hardcoded for example
                        'origin_country' => isset($content['shipFrom']) ? $content['shipFrom'] : null,
                        'origin_state' => '', // Hardcoded for example
                        'origin_city' => '', // Hardcoded for example
                        'tracking_postal_code' => null,
                        'tracking_ship_date' => null,
                        'tracking_destination_country' => null,
                        'tracking_origin_country' => null,
                        'tracking_key' => null,
                        'tracking_courier_account' => null,
                        'customer_name' => null,
                        'customer_email' => null,
                        'customer_sms' => null,
                        'recipient_postcode' => null,
                        'order_id' => isset($content['id']) ? $content['id'] : null,
                        'title' => null,
                        'logistics_channel' => null,
                        'note' => null,
                        'label' => null,
                        'signed_by' => '', // Hardcoded for example
                        'service_code' => isset($content['shipmentType']) ? $content['shipmentType'] : null,
                        'weight' => null,
                        'weight_kg' => null,
                        'product_type' => null,
                        'pieces' => null,
                        'dimension' => null,
                        'previously' => null,
                        'destination_track_number' => null,
                        'exchange_number' => null,
                        'scheduled_delivery_date' => null,
                        'scheduled_address' => null,
                        'substatus' => isset($content['transitSubStatus']) ? strtolower($content['transitSubStatus']) : null,
                        'status_info' => null,
                        'latest_event' => isset($content['localLogisticsInfo']['trackingDetails'][0]['eventDetail'])
                            ? $content['localLogisticsInfo']['trackingDetails'][0]['eventDetail'] . ',' . $content['localLogisticsInfo']['trackingDetails'][0]['address'] . ',' . $content['localLogisticsInfo']['trackingDetails'][0]['eventTime']
                            : null,
                        'latest_checkpoint_time' => isset($content['localLogisticsInfo']['trackingDetails'][0]['eventTimeZeroUTC'])
                            ? $content['localLogisticsInfo']['trackingDetails'][0]['eventTimeZeroUTC']
                            : null,
                        'transit_time' => 0,
                        'origin_info' => [
                            'courier_code' => isset($content['localLogisticsInfo']['courierCode']) ? $content['localLogisticsInfo']['courierCode'] : null,
                            'courier_phone' => '',
                            'weblink' => isset($content['localLogisticsInfo']['courierHomePage']) ? $content['localLogisticsInfo']['courierHomePage'] : null,
                            'reference_number' => null,
                            'milestone_date' => [
                                'inforeceived_date' =>  $info_received_date,
                                'pickup_date' => $pickup_date,
                                'outfordelivery_date' => $outfordelivery_date,
                                'delivery_date' => $delivery_date,
                                'returning_date' => $returning_date,
                                'returned_date' => $returned_date
                            ],
                            'trackinfo' => isset($content['localLogisticsInfo']['trackingDetails'])
                                ? array_map(function ($detail) use($exclude_keywords) {
                                    return [
                                        'checkpoint_date' => isset($detail['eventTimeZeroUTC']) ? $detail['eventTimeZeroUTC'] : null,
                                        'checkpoint_delivery_status' => isset($detail['eventDetail']) ? strtolower($detail['eventDetail']) : null,
                                        'checkpoint_delivery_substatus' => isset($detail['transitSubStatus']) ? strtolower($detail['transitSubStatus']) : null,
                                        'tracking_detail' => isset($detail['eventDetail']) ? str_ireplace($exclude_keywords, '', $detail['eventDetail']): null,
                                        'location' => isset($detail['address']) ? str_ireplace($exclude_keywords, '', $detail['address']): null,
                                        'country_iso2' => '',
                                        'state' => '',
                                        'city' => '',
                                        'zip' => '',
                                        'raw_status' => null,
                                    ];
                                }, $content['localLogisticsInfo']['trackingDetails'])
                                : []
                        ],
                        'destination_info' => [
                            'courier_code' => null,
                            'courier_phone' => null,
                            'weblink' => null,
                            'reference_number' => null,
                            'milestone_date' => [
                                'inforeceived_date' => null,
                                'pickup_date' => null,
                                'outfordelivery_date' => null,
                                'delivery_date' => null,
                                'returning_date' => null,
                                'returned_date' => null
                            ],
                            'trackinfo' => []
                        ]
                    ];
                }, $response['data']['accepted']['content']);

                $data=['data'=>$transformedData];

                $data = json_decode(json_encode($data),false);

//                dd($exclude_keywords,$data);
                return $data;
            } else {
                return false;
            }
        } else {
            return false;
        }


    }
    public function shippingStatusUpdate($shipping_response,$fulfillment,$shop=null,$country=null)
    {
//        dd($shipping_response);
        $count = (array)($shipping_response->data);

        if (!empty($count)) {
            $count = count($count) - 1;

            $fulfillment = Fulfillment::where('fulfillment_id', $fulfillment->fulfillment_id)->first();
            $prev_shipment_status = $fulfillment->shipment_status;
            if (isset($prev_shipment_status) && $prev_shipment_status != '') {

            } else {
                $prev_shipment_status = 'no status found';
            }

//                                        dump($shipping_response->data->items[$count]->status);

            $fulfillment->shipment_status = $shipping_response->data[$count]->delivery_status;
            $fulfillment->enable_tracking = 1;
            $fulfillment->shipment_substatus = $shipping_response->data[$count]->substatus;

            if(isset($shipping_response->data[$count]->origin_info->trackinfo)
                && count($shipping_response->data[$count]->origin_info->trackinfo)) {

                $data_count = count($shipping_response->data[$count]->origin_info->trackinfo);
                $first_date = date_create($shipping_response->data[$count]->origin_info->trackinfo[$data_count - 1]->checkpoint_date);
                $fulfillment->first_date = date_format($first_date, "Y-m-d H:i:s");
            }

            if (($shipping_response->data[$count]->delivery_status == 'delivered')) {
                $end_date=date_create($shipping_response->data[$count]->origin_info->trackinfo[0]->checkpoint_date);
                $fulfillment->last_date = date_format($end_date,"Y-m-d H:i:s");
            }
            if (isset($shipping_response->data[$count]->origin_info->trackinfo) &&
                $shipping_response->data[$count]->origin_info->trackinfo != '') {
                $fulfillment->track_info = json_encode($shipping_response->data[$count]->origin_info->trackinfo);
            }
            if (isset($shipping_response->data) && $shipping_response->data != '') {
                $fulfillment->tracking_complete_info = json_encode($shipping_response->data);
            }

            if (isset($shipping_response->data[$count]->id) && $shipping_response->data[$count]->id != '') {
                $fulfillment->shipment_id = $shipping_response->data[$count]->id;
            }

            if (isset($country) && $country != "") {
                $fulfillment->country = $country;
            }


            $shipment_register_time = null;
            if(isset($shipping_response->data[$count]->origin_info->milestone_date->pickup_date)){
                $shipment_register_time = date_create($shipping_response->data[$count]->origin_info->milestone_date->pickup_date);
                $shipment_register_time = date_format($shipment_register_time,"Y-m-d H:i:s");
            }
            $fulfillment->shipment_register_time = $shipment_register_time;

            $shipment_last_update_time = null;

            if (isset($shipping_response->data[$count]->latest_checkpoint_time) && $shipping_response->data[$count]->latest_checkpoint_time != "") {
                $shipment_last_update_time = date_create($shipping_response->data[$count]->latest_checkpoint_time);
                $shipment_last_update_time = date_format($shipment_last_update_time, "Y-m-d H:i:s");
            }
            $fulfillment->shipment_last_update_time = $shipment_last_update_time;

            $fulfillment->shipment_last_event = isset($shipping_response->data[$count]->latest_event) && $shipping_response->data[$count]->latest_event != "" ? $shipping_response->data[$count]->latest_event : null;
            $fulfillment->original_country = isset($shipping_response->data[$count]->origin_country) && $shipping_response->data[$count]->origin_country != "" ? $shipping_response->data[$count]->origin_country : null;

            $fulfillment->save();

            $carrier_status = new Status();
            $carrier_status->fulfillment_id = $fulfillment->fulfillment_id;
            $carrier_status->tracking_number = $shipping_response->data[$count]->tracking_number;
            $carrier_status->carrier_api_id = $shipping_response->data[$count]->id;
            $carrier_status->carrier_code = $shipping_response->data[$count]->courier_code;
            $carrier_status->status = $shipping_response->data[$count]->delivery_status;
            if (isset($shipping_response->data[$count]->origin_info->trackinfo) && $shipping_response->data[$count]->origin_info->trackinfo != '') {
                $carrier_status->track_info = json_encode($shipping_response->data[$count]->origin_info->trackinfo);
            }
            $carrier_status->save();

            /*if (($shop!=null) && ($prev_shipment_status != 'pending') && ($prev_shipment_status != $shipping_response->data[$count]->delivery_status)) {
                if (isset($order->email) && $order->email != '') {
                    try {
                        $this->triggerShopifyFlow($fulfillment->order, $fulfillment->shipment_status, $shop);
                    } catch (\Exception $e) {
                        $msg = new ErrorMessage();
                        $msg->message = 'triggerShopifyFlow issue2: ' . $e->getMessage();
                        $msg->save();
                    }
                    try {
                        $klaviyo_controller=new KlaviyoController();
                        $klaviyo_controller->createKlaviyoEvent($fulfillment, $fulfillment->order, $fulfillment->shipment_status, $shop);
                    } catch (\Exception $e) {
                        $msg = new ErrorMessage();
                        $msg->message = 'createEvent issue2: ' . $e->getMessage();
                        $msg->save();
                    }
                    try {
                        Mail::to($order->email)->send(new \App\Mail\CheckShippingStatus($fulfillment, $order));
                        $mail_log = new EmailLog();
                        $mail_log->session_id = $session->id;
                        $mail_log->order_id = $order->id;
                        $mail_log->fulfillment_id = $fulfillment->id;
                        $mail_log->email_status = 'sent';
                        $mail_log->message = 'Successfully sent!';
                        $mail_log->save();


                    } catch (Exception $exception) {
                        $mail_log = new EmailLog();
                        $mail_log->session_id = $session->id;
                        $mail_log->order_id = $order->id;
                        $mail_log->fulfillment_id = $fulfillment->id;
                        $mail_log->email_status = 'not sent';
                        $mail_log->message = 'Error: ' . $exception->getMessage();
                        $mail_log->save();
                    }

                }
            }*/
        }

    }
    public function shipping_status2($fulfillment_id, $tracking_number, $carrier)
    {

        $api_setting = ApiSetting::where('status', 1)->first();
        if (isset($api_setting) && isset($api_setting->api_name) && $api_setting->api_name === 'Tracktory') {
            $data = array(
                "tracking_number" => "$tracking_number",
                "carrier_code" => "$carrier"
            );
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.tracktry.com/v1/trackings/realtime",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "postman-token: 1f29af0c-a885-168c-3c54-09637c0c56fc",
//testing key
//                "tracktry-api-key: d49ea6dc-f182-4f43-92f5-9fedacf31625"
//            live keys
//                "tracktry-api-key: 7da2ec9e-fc9c-4252-97ba-8615e261acf9"
                    "tracktry-api-key: $api_setting->api_key"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response);
//        $msg = new ErrorMessage();
//        $msg->message = $tracking_number.$carrier.'shipping status response '. json_encode($response);
//        $msg->save();
//        dd($response);
            if (isset($response->meta) && $response->meta->code == 200) {
                return $response;
            } else {
                return false;
            }
        } else {
            return false;
        }


    }

    public function triggerShopifyFlow($order, $shipment_status, $session)
    {
        $customer = isset($order->customer) && $order->customer != "" ? json_decode($order->customer) : null;
        if (isset($customer) && isset($shipment_status) && $shipment_status != "") {
            $query = 'mutation flowTriggerReceive($handle: String, $payload: JSON) {
  flowTriggerReceive(handle: $handle, payload: $payload) {
    userErrors {
      field
      message
    }
  }
}
';
            $variables = [
                "handle" => 'order-tracking-app',
                "payload" => [
                    "Shipment Status" => "$shipment_status",
                    "customer_id" => $customer->id,
                ]
            ];

            $client = new Graphql($session->shop, (new ShopifyTokenService())->getValidAccessToken($session->shop));
            $shopify_flow = $client->query(["query" => $query, "variables" => $variables]);
            $shopify_flow = $shopify_flow->getDecodedBody();
        }

    }

    public function add_usage_charge($shop_name,$plan)
    {
        if (is_billing_free_shop($shop_name)) {
            return true;
        }
        {/*change 7 start*/
        }
        return true;
        {/*change 7 end*/
        }
        try {
            $shop = Session::where('shop', $shop_name)->latest()->first();
            $client = new Rest($shop->shop, (new ShopifyTokenService())->getValidAccessToken($shop->shop));
            $data = [
                "usage_charge" => [
                    'description' => "You had been charged $".$plan->usage_charges."/shipping.",
                    'price' => $plan->usage_charges
                ]
            ];
            if (isset($shop)) {
                $charge = Charge::where('session_id', $shop->id)->where('status', 'active')->latest()->first();
                if (isset($charge)) {
                    $response = $client->post("/recurring_application_charges/" . $charge->charge_id . "/usage_charges.json", $data);
                    $response = $response->getDecodedBody();


                    if ($response['usage_charge']) {
                        $usage_charge_setting = UsageChargeSetting::where('shop_id', $shop->id)->first();
                        if ($usage_charge_setting == null) {
                            $usage_charge_setting = new UsageChargeSetting();
                        }
                        $usage_charge_setting->shop_id = $shop->id;
                        $usage_charge_setting->balance_used = $response['usage_charge']['balance_used'];
                        $usage_charge_setting->save();
                    }
//                   $msg = new ErrorMessage();
//                   $msg->message = "Usage Charge Response, Charge Id: $charge->charge_id and Response is:".json_encode($response);
//                   $msg->save();
                }
            }
        } catch (\Exception $exception) {

        }
    }

    public function fetch_tracking_page(Request $request){
        try {
            $fulfillments = [];
            $shop = Session::where('shop', $request['shop'])->first();
//            if (isset($request['shop']) && $request['shop'] != "") {
            if ($shop) {
                $theme_type = $request->theme_type;

                $trackingPage = TrackingPage::where('session_id', $shop->id)
                    ->where('active_status', 1)
                    ->where('theme_type', $theme_type)
                    ->first();
                $recomendation=[];
                if ($trackingPage) {

                    if ($trackingPage->theme_type === $theme_type) {
                        $data = array(
                            'status' => 'success',
                            'enable' => 1,
                        );
                    }else{
                        $data = array(
                            'status' => 'error',
                            'message' => 'Tracking page not published!'
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'message' => 'No tracking page published!'
                    );
                }
            }
            else {
                $data = array(
                    'status' => 'error',
                    'message' => 'Missing parameters!'
                );
            }
        } catch (\Exception $exception) {
            $data = array(
                'status' => 'error',
                'message' => $exception->getMessage()
            );
        }

        return response()->json($data);
    }
    public function save_recommended_click(Request $request)
    {
        $shop = Session::where('shop', $request['shop'])->first();
//            if (isset($request['shop']) && $request['shop'] != "") {
        if ($shop) {
            $page_view = new RecommendedProductView();
            $page_view->shop_id = $shop->id;
            $page_view->click_count = 1;
            $page_view->save();
        }
        return response()->json([
            'status' => 'success'
        ]);

    }
    public function translateText($text, $targetLanguage)
    {
        $tr = new GoogleTranslate($targetLanguage); // Target language
        $tr->setSource('en'); // Source language (optional)
        return $tr->translate($text);
        return $text;
    }
    function translateTrackInfo($jsonString, $targetLang = 'he') {
        // Initialize Google Translate
        $translator = new GoogleTranslate($targetLang);

        // Decode the JSON string into an array
        $data = json_decode($jsonString, true);

        // Iterate over each object in the array
        foreach ($data as &$checkpoint) {
            $checkpoint['checkpoint_delivery_status'] = $translator->translate($checkpoint['checkpoint_delivery_status']);
            $checkpoint['tracking_detail'] = $translator->translate($checkpoint['tracking_detail']);
        }

        // Encode the translated array back into JSON
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    public function search_tracking_number(Request $request)
    {

        try {
            $fulfillments = [];
            $shop = Session::where('shop', $request['shop'])->first();
//            if (isset($request['shop']) && $request['shop'] != "") {
            if ($shop) {
                $page_view=new PageView();
                $page_view->shop_id=$shop->id;
                $page_view->click_count=1;
                $page_view->save();
                $translation_code=Translation::select('language')->where('shop_id',$shop->id)->where('is_default',1)->first();
                if($translation_code){
                    $translation_code=$translation_code->language;
                }else{
                    $translation_code='en';
                }
                $trackingPage = TrackingPage::where('session_id', $shop->id)->where('active_status', 1)->first();
                $recomendation=[];
                if (isset($trackingPage)) {

                    $requests = $request->all();
                    $this->refreshTracking($request,$shop);
                    if ($trackingPage->theme_type === 'Modern') {
                        $data = json_decode($trackingPage->data,false);
//                        $searchType = $data->pageData->search;
                        // Frontend may send "tracking-number" (Modern) or "tracking_number" (Traditional templates).
                        $trackType = (string) ($request->track_type ?? '');
                        $isTrackingNumberSearch = in_array($trackType, ['tracking-number', 'tracking_number'], true)
                            || ($request->filled('tracking_number') && !$request->filled('order_number'));
                        if ($isTrackingNumberSearch) {
                            $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])
                                ->where('tracking_number', $request->tracking_number)
                                ->where('session_id', $shop->id)->whereNotNull('tracking_number')->latest()->get();

                        }
                        else{
//                            if ($searchType == 'any' || !$searchType ) {
                               $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])->where('session_id', $shop->id)
                                ->whereHas('order', function ($query) use ($requests, $shop) {
                                    $query->where(function ($query) use ($requests) {
                                        $query->where('email', $requests['email'])
                                            ->orWhere('phone', $requests['email']);
                                    })->where('name', strpos($requests['order_number'], '#') === false ? '#' . $requests['order_number'] : $requests['order_number']);
                                })->where('session_id', $shop->id)->whereNotNull('tracking_number')->latest()->get();
                                /* }
                                 elseif ($searchType === 'order_with_email') {
                                $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])->
                                whereHas('order', function ($query) use ($requests, $shop) {
                                    $query->where(function ($query) use ($requests) {
                                        $query->where('email', $requests['email'])
                                            ->where('name', strpos($requests['order_number'], '#') === false ? '#' . $requests['order_number'] : $requests['order_number']);
                                    });
                                })->where('session_id', $shop->id)->whereNotNull('tracking_number')->get();
                            }
                            elseif ($searchType === 'order') {
                                $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])->
                                whereHas('order', function ($query) use ($requests, $shop) {
                                    $query->where(function ($query) use ($requests) {
                                        $query->where('name', strpos($requests['order_number'], '#') === false ? '#' . $requests['order_number'] : $requests['order_number']);
                                    });
                                })->where('session_id', $shop->id)->whereNotNull('tracking_number')->get();
                            }
                            elseif ($searchType === 'email') {
                                $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])->
                                whereHas('order', function ($query) use ($requests, $shop) {
                                    $query->where(function ($query) use ($requests) {
                                        $query->where('email', $requests['email']);
                                    });
                                })->where('session_id', $shop->id)->whereNotNull('tracking_number')->get();
                            }*/
                        }
                        if($data->selectedProductsType=="manual_product"){
                            $recomendation=$data->selected_manual_product??[];
                        }elseif($data->selectedProductsType=="collection"){
                            $collection=Collection::with('has_products')->where('id',$data->selected_collection->id)->first();
                            $recomendation=$collection->has_products;
                        }elseif ($data->selectedProductsType=="automatic_products"){

                        }

                    }
                    else {
                        if (isset($request['track_type']) && $request['track_type'] == 'order-number') {

                            $requests = $request->all();
                            $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])->where('session_id', $shop->id)
                                ->whereHas('order', function ($query) use ($requests, $shop) {
                                    $query->where(function ($query) use ($requests) {
                                        $query->where('email', $requests['email'])
                                            ->orWhere('phone', $requests['email']);
                                    })->where('name', strpos($requests['order_number'], '#') === false ? '#' . $requests['order_number'] : $requests['order_number']);
                                })->where('session_id', $shop->id)->whereNotNull('tracking_number')->latest()->get();
                        } else {
                            $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])
                                ->where('tracking_number', $request['tracking_number'])
                                ->where('session_id', $shop->id)->whereNotNull('tracking_number')->latest()->get();
                        }
//                        $fulfillments = Fulfillment::with(['order', 'order.lineitems'])->
//                        whereHas('order', function ($query) use ($requests, $shop) {
//                            $query->where(function ($query) use ($requests) {
//                                $query->where('email', $requests['search'])
//                                    ->orWhere('name', strpos($requests['search'], '#') === false ? '#' . $requests['search'] : $requests['search']);
//                            });
//                        })->where('session_id', $shop->id)->whereNotNull('tracking_number')->get();
//
//
//                        if (!$fulfillments->count()) {
//                            $fulfillments = Fulfillment::with(['order', 'order.lineitems'])->
//                            where('tracking_number', $request['search'])
//                                ->where('session_id', $shop->id)->get();
//                        }
                    }
                    if (isset($fulfillments[0]->id)) {
                        if($fulfillments[0]->track_info ){
                            if($translation_code !="en") {
                                foreach ($fulfillments as &$fulfill) {
                                $fulfill->shipment_status_t=$this->translateText($fulfill->shipment_status,$translation_code);
                                    $fulfill->track_info = $this->translateTrackInfo($fulfill->track_info, $translation_code);
                                }
                            }
//                        if($fulfillments[0]->track_info && !empty($fulfillments[0]->track_info) && ($fulfillments[0]->track_info) !="[]"){
                            $data = array(
                                'status' => 'success',
                                'search' => $request['search'],
                                'email' => $request['email'],
                                'name' => $request['name'],
                                'fulfillments' => $fulfillments,
                                'trackingPage' => $trackingPage,
                                'recomendation' => $recomendation,
                            );
                        }else{
                            $data = array(
                                'status' => 'error',
                                'message' => 'No traking detail found!'
                            );
                        }

                    } else {
                        $data = array(
                            'status' => 'error',
                            'message' => 'No fulfillments found!'
                        );
                    }
                } else {
                    $data = array(
                        'status' => 'error',
                        'message' => 'No tracking page published!'
                    );
                }

            }
            else {
                $data = array(
                    'status' => 'error',
                    'message' => 'Missing parameters!'
                );
            }
        } catch (\Exception $exception) {
            $data = array(
                'status' => 'error',
                'message' => $exception->getMessage()
            );
        }

        return response()->json($data);
    }


     public function refreshTracking($request,$shop){
         $trackType = (string) ($request['track_type'] ?? '');
         if ($trackType === 'order-number') {
             $requests = $request->all();
             $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])->where('session_id', $shop->id)
                 ->whereHas('order', function ($query) use ($requests, $shop) {
                     $query->where(function ($query) use ($requests) {
                         $query->where('email', $requests['email'])
                             ->orWhere('phone', $requests['email']);
                     })->where('name', strpos($requests['order_number'], '#') === false ? '#' . $requests['order_number'] : $requests['order_number']);
                 })->where('session_id', $shop->id)->whereNotNull('tracking_number')->latest()->get();
         } else {
             $fulfillments = Fulfillment::with(['order', 'order.lineitems','carrier_name_base', 'carrier_code_base'])
                 ->where('tracking_number', $request['tracking_number'])
                 ->where('session_id', $shop->id)->whereNotNull('tracking_number')->latest()->get();
         }


             if ($fulfillments->count()) {
                 $fulfillment_controller = new \App\Http\Controllers\FulfillmentController();
                 foreach ($fulfillments as $fulfillment) {
                     if($fulfillment->shipment_status!="delivered") {
                         $shipping_status = $fulfillment_controller->shipping_status(
                             $fulfillment->fulfillment_id,
                             $fulfillment->tracking_number,
                             $fulfillment->tracking_company,
                             $shop
                         );
//                        dd($shipping_status);
                         if (isset($shipping_status) && isset($shipping_status->data) && !empty($shipping_status->data)) {
                             if ($shipping_status != false) {
                                 $fulfillment_controller->shippingStatusUpdate($shipping_status, $fulfillment);
                                 return true;
                             }
                         }
                     }
                 }
             }
    }
}


// 2022-10 fulfillment api

// 2022-10 fulfillment api (token must come from ShopifyTokenService / getShopApi)
//$fulfillments_orders = $client->get('orders/' . $order->shopify_order_id . '/fulfillment_orders');
//$fulfillments_orders = $fulfillments_orders->getDecodedBody();
//foreach ($fulfillments_orders as $fulfillments_order_array) {
//    if (count($fulfillments_order_array)) {
//        foreach ($fulfillments_order_array as $fulfillments_order) {
//            $fulfillments_order = json_decode(json_encode($fulfillments_order), false);
//
//            $fulfillment_order_line_items = [];
//            if (!empty($fulfillments_order->line_items)) {
//                foreach ($fulfillments_order->line_items as $line_item) {
//                    array_push($fulfillment_order_line_items, [
//                        "id" => $line_item->id,
//                        "quantity" => $line_item->quantity
////                                        "quantity" => 1
//                    ]);
//                }
//            }
//
//            array_push($data['fulfillment']['line_items_by_fulfillment_order'], [
//                "fulfillment_order_id" => $fulfillments_order->id,
//                "fulfillment_order_line_items" => $fulfillment_order_line_items
//            ]);
//
//        }
//
//    }
//
//}
