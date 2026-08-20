<?php

namespace App\Http\Controllers;


use App\Models\Carrier;
use App\Models\Charge;
use App\Models\CustomChargeLog;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\ReportStatus;
use App\Models\Session;

use App\Models\UsageChargeSetting;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Shopify\Clients\Rest;

//use Jenssegers\Agent\Agent;


class OrderController extends HelperController
{

    public function orders(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);

        $fulfillment_status_array = null;
        $carrier_array = null;
        $search = null;
        $date = null;

        $orders = Order::with(['lineitems', 'fulfillments', 'tracking_stats'])
            ->where('orders.session_id', $session->id)->newQuery();

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

            $orders->where(function ($q) use ($search) {
                $q->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhereHas('fulfillments', function ($q2) use ($search) {
                    $q2->where('tracking_number', 'like', '%' . $search . '%');
                });
            });
        }
        if (isset($request->shipment_status)) {
            $shipment_status_array = $request->shipment_status;
            $orders->whereHas('fulfillments', function ($q) use ($shipment_status_array) {
                $q->whereIn('shipment_status', $shipment_status_array);
            })->get();
        }
        if (isset($request->destinations)) {
            $destinations_array = $request->destinations;
            $orders->whereHas('fulfillments', function ($q) use ($destinations_array) {
                $q->whereIn('country', $destinations_array);
            })->get();
        }
        if (isset($request->origins)) {
            $origins_array = $request->origins;
            $orders->whereHas('fulfillments', function ($q) use ($origins_array) {
                $q->whereIn('original_country', $origins_array);
            })->get();
        }
        if (isset($request->carrier)) {
            $carrier_array = $request->carrier;
            $orders->whereHas('fulfillments', function ($q) use ($carrier_array) {
                $q->whereIn('tracking_company', $carrier_array);
            });
        }
        if (isset($request->fulfillment_status)) {
            $fulfillment_status = $request->fulfillment_status;
            $orders->whereHas('lineitems', function ($q) use ($fulfillment_status) {
                if($fulfillment_status === 'fulfilled') {
                    $q->where('fulfillment_status','fulfilled');
                }else{
                    $q->whereNull('fulfillment_status');
                }

            });
        }
        if (isset($request->order_date_datefilter)) {
            $datefilter = $request->query('order_date_datefilter');

            if ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7), Carbon::now()];
            }elseif ($datefilter == 'Today') {
                $date_range = [Carbon::now()->subDays(1), Carbon::now()];
            }elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30), Carbon::now()];
            }elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60), Carbon::now()];
            }elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90), Carbon::now()];
            }elseif ($datefilter == 'Custom') {
                $start_date = Carbon::parse($request->order_date_starting)->format('Y-m-d') . ' 00:00:00';
                $end_date = Carbon::parse($request->order_date_ending)->format('Y-m-d') . ' 23:59:59';
                $date_range = [$start_date, $end_date];
            }
            $orders->whereBetween('orders.created_at',$date_range);
        }
        if (isset($request->shipment_date_datefilter)) {
            $datefilter = $request->query('shipment_date_datefilter');

            if ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7), Carbon::now()];
            }elseif ($datefilter == 'Today') {
                $date_range = [Carbon::now()->subDays(1), Carbon::now()];
            }elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30), Carbon::now()];
            }elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60), Carbon::now()];
            }elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90), Carbon::now()];
            }elseif ($datefilter == 'Custom') {
                $start_date = Carbon::parse($request->shipment_date_starting)->format('Y-m-d') . ' 00:00:00';
                $end_date = Carbon::parse($request->shipment_date_ending)->format('Y-m-d') . ' 23:59:59';
                $date_range = [$start_date, $end_date];
            }
            $orders->whereHas('fulfillments', function ($q) use ($date_range) {
                $q->whereBetween('created_at',$date_range);
            });
        }
        if (isset($request->last_update_datefilter)) {
            $datefilter = $request->query('last_update_datefilter');

            if ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7), Carbon::now()];
            }elseif ($datefilter == 'Today') {
                $date_range = [Carbon::now()->subDays(1), Carbon::now()];
            }elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30), Carbon::now()];
            }elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60), Carbon::now()];
            }elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90), Carbon::now()];
            }elseif ($datefilter == 'Custom') {
                $start_date = Carbon::parse($request->shipment_date_starting)->format('Y-m-d') . ' 00:00:00';
                $end_date = Carbon::parse($request->shipment_date_ending)->format('Y-m-d') . ' 23:59:59';
                $date_range = [$start_date, $end_date];
            }
            $orders->whereHas('fulfillments', function ($q) use ($date_range) {
                $q->whereBetween('shipment_last_update_time',$date_range);
            });
        }
        if (isset($request->transit_time) && !empty($request->transit_time)) {
            $transit_times = $request->transit_time;
            $orders->whereHas('fulfillments', function ($query) use ($transit_times) {
                $query->where(function ($q) use ($transit_times) {
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
            });
        }

        if (isset($request->order_by) && $request->order_by != '') {
            $order_by = $request->order_by === 'Oldest to newest' ? 'asc' : 'desc';
            $sort_by = $request->sort_by;
            if(isset($sort_by) && $sort_by != ''){
                if($sort_by === 'Latest event'){
                    $orders->join('fulfillments', 'orders.shopify_order_id', '=', 'fulfillments.shopify_order_id')
                        ->orderBy("fulfillments.shipment_last_update_time", $order_by);

                }elseif($sort_by === 'Order date'){
                    $orders->orderBy("created_at", $order_by);

                }elseif($sort_by === 'Fulfillment date'){
                    $orders->join('fulfillments', 'orders.shopify_order_id', '=', 'fulfillments.shopify_order_id')
                        ->orderBy("fulfillments.created_at", $order_by);
                }

            }else{
                $orders->orderBy('created_at', $order_by);
            }
        } else {
            $orders->orderBy('created_at', 'desc');
        }
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
            $orders = $orders->paginate($request->per_page);
        } else {
            $orders = $orders->paginate(15);
            $per_page = 15;
        }


        $data = array(
            'orders' => $orders,
            'fulfillment_status_array' => $fulfillment_status_array,
            'carrier_array' => $carrier_array,
            'search' => $search,
            'date' => $date,
            'per_page' => $per_page,
            'carriers' => $carriers,
            'destinations' => $destinations,
            'origins' => $origins,
            'shipment_statuses' => $shipment_statuses,
        );

        return response()->json($data);
//        return view('module.orders')->with($data);
    }

    public function shop_orders(Request $request, $id)
    {

        $fulfillment_status_array = null;
        $carrier_array = null;
        $search = null;
        $date = null;
        $per_page = null;

        $fulfillment_status = ReportStatus::orderBy('status')->get();
        $carriers = Fulfillment::where('user_id', $id)->where('tracking_company', '!=', null)->orderBy('tracking_company')->select('tracking_company')->distinct()->get();

        $order_query = Order::where('user_id', $id)->where('user_id', $id)->newQuery();

        if ($request->input('search')) {
            $search = $request->search;

            $order_query->where(function ($q) use ($search) {
                $q->orWhere('name', 'like', '%' . $search . '%')->orWhereHas('fulfillments', function ($q2) use ($search) {
                    $q2->where('tracking_number', 'like', '%' . $search . '%');
                });
            });
            //            $order_query->where('name','like','%'.$search.'%')->get();
//            $order_query->whereHas('fulfillments', function ($q) use ($search) {
//                $q->where('tracking_number','like','%'.$search.'%');
//            })->get();
        }
        if (isset($request->fulfillment_status)) {
            $fulfillment_status_array = $request->fulfillment_status;
            $order_query->whereHas('fulfillments', function ($q) use ($fulfillment_status_array) {
                $q->whereIn('shipment_status', $fulfillment_status_array);
            })->get();
        }
        if (isset($request->carrier)) {
            $carrier_array = $request->carrier;
            $order_query->whereHas('fulfillments', function ($q) use ($carrier_array) {
                $q->whereIn('tracking_company', $carrier_array);
            })->get();
        }
        if (isset($request->datefilter)) {
            $date = $request->query('datefilter');
            $date_range = explode('-', $request->input('datefilter'));
            $start_date = $date_range[0];
            $end_date = $date_range[1];
            $start_date = Carbon::parse($start_date)->format('Y-m-d') . ' 00:00:00';
            $end_date = Carbon::parse($end_date)->format('Y-m-d') . ' 23:59:59';

            $order_query->whereBetween('created_at', [$start_date, $end_date])->get();
        }
        if (isset($request->per_page)) {
            $per_page = $request->per_page;
            $orders = $order_query->orderBy('created_at', 'desc')->paginate($request->per_page);
        } else {
            $orders = $order_query->orderBy('created_at', 'desc')->paginate(50);
            $per_page = 50;
        }

        $data = array(
            'fulfillment_status_array' => $fulfillment_status_array,
            'carrier_array' => $carrier_array,
            'search' => $search,
            'date' => $date,
            'per_page' => $per_page,
            'carriers' => $carriers,
            'fulfillment_status' => $fulfillment_status,
            'id' => $id,
            'orders' => $orders
        );
        return view('module.shop_orders')->with($data);
    }

    public function order_detail(Request $request, $id)
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

        $order_data = Order::with('tracking_stats', 'lineitems', 'fulfillments')->find($id);

        $data = [
            'order_data' => $order_data,
            'plans_status' => $plans_status,
        ];

        return response($data);

//        return view('module.order_detail', compact('order_data','line_item_data','plans_status'));
    }


}
