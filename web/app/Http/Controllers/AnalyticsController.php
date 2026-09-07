<?php

namespace App\Http\Controllers;

use App\Models\Fulfillment;
use App\Models\PageView;
use App\Models\Plan;
use App\Models\RecommendedProductView;
use App\Models\Session;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends HelperController
{
    public function dashboard(Request $request)
    {

        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        (new PlanController())->ensureBillingFreeShopPlan($session);
        $carrier_array = null;
        $common_controller = new CommonController();
        $total_req=$common_controller->get_api_statistics($session);
        $active_plan = Plan::find($session->plan_id);
        $country_name=$session->country;
        if(!$country_name){
            try{
                $query = <<<QUERY
            query {
                shop {
                    id
                    name
                    email
                    myshopifyDomain
                    shopOwnerName
                    shopAddress{
                        address1
                        address2
                        city
                        countryCodeV2
                        latitude
                        longitude
                        phone
                        province
                        provinceCode
                        country
                        zip
                    }
                }
            }
        QUERY;
                $response =  $this->getShopApi($session->shop)->graph($query);

                if ($response['errors'] == false) {
                    $country_name = $response['body']['data']['shop']['shopAddress']['country'];
                    $session->country=$country_name;
                    $session->save();
                }else{
                    $country_name='random';
                }
            }catch(\Exception $e){

            }

        }



        $carriers = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('tracking_company')->select('tracking_company')->whereNotNull('tracking_company')
            ->distinct()->get();

        $destinations = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('country')->select('country')->whereNotNull('country')
            ->distinct()->get();

        $date_range = [Carbon::now()->subDays(90)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];

        if (isset($request->shipment_date_datefilter)) {
            $datefilter = $request->query('shipment_date_datefilter');
            if ($datefilter == 'Today') {
                $date_range = [Carbon::now()->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Custom') {
//                $start_date = Carbon::createFromFormat('D M d Y H:i:s', preg_replace('/ \(.+\)$/', '', $request->shipment_date_starting))->format('Y-m-d H:i:s');
//                $end_date = Carbon::createFromFormat('D M d Y H:i:s', $request->shipment_date_startingpreg_replace('/ \(.+\)$/', '', $request->shipment_date_ending))->format('Y-m-d H:i:s');
                $shipment_date_starting = $request->shipment_date_starting;
                $shipment_date_ending = $request->shipment_date_ending;
                $clean_starting_date = preg_replace('/GMT.*$/', '', $shipment_date_starting);
                $clean_ending_date = preg_replace('/GMT.*$/', '', $shipment_date_ending);
                $start_date = Carbon::parse($clean_starting_date)->startOfDay()->format('Y-m-d  H:i:s');
                $end_date = Carbon::parse($clean_ending_date)->endOfDay()->format('Y-m-d  H:i:s');
                $date_range = [$start_date, $end_date];
            }
        }
        $shipments = Fulfillment::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as shipments'))
            ->where('session_id', $session->id)->whereBetween('created_at', $date_range)->newQuery();

        $shipment_statuses = DB::table('fulfillments')->where('session_id', $session->id)->whereBetween('created_at', $date_range);
        if (isset($request->carrier)) {
            $carrier_array = explode(',', $request->carrier);
            $shipments->whereIn('tracking_company', $carrier_array);
            $shipment_statuses->whereIn('tracking_company',$carrier_array);
        }

        if (isset($request->destinations)) {
            $destinations_array = explode(',', $request->destinations);
            $shipments->whereIn('country',$destinations_array);
            $shipment_statuses->whereIn('country',$destinations_array);
        }

        $shipments = $shipments->groupBy('date')->get()->toArray();


        $shipment_statuses = $shipment_statuses->select(
            DB::raw('IFNULL(shipment_status, "notfound") as shipment_status'),
            DB::raw('COUNT(*) as count')
        ) ->groupBy(DB::raw('IFNULL(shipment_status, "notfound")'))
            ->whereBetween('created_at', $date_range)->get()->toArray();
        $no_update_shipment = DB::table('fulfillments')
            ->where('session_id', $session->id)
            ->where('updated_at', '<=', Carbon::now()->subWeeks(2))
            ->count();
        $undelivered_shipment = DB::table('fulfillments')
            ->where('session_id', $session->id)
            ->where('shipment_status', 'out for delivery')
            ->where('updated_at', '<=', Carbon::now()->subDays(3))
            ->count();
        $exception_shipment = DB::table('fulfillments')
            ->where('session_id', $session->id)
            ->where('shipment_status', 'exception')
            ->where('updated_at', '<=', Carbon::now()->subDays(3))
            ->count();
        $avgRating=0;
        $ratings = [
            ['label' => '5 stars', 'value' => 0, 'percent' => 0],
            ['label' => '4 stars', 'value' => 0, 'percent' => 0],
            ['label' => '3 stars', 'value' => 0, 'percent' => 0],
            ['label' => '2 stars', 'value' => 0, 'percent' => 0],
            ['label' => '1 star',  'value' => 0, 'percent' => 0],
        ];
        /*$totalRatings = DB::table('ratings')->count();
        // Fetch count of ratings for each star
        $starRatings = DB::table('ratings')
            ->select('rating', DB::raw('count(*) as value'))
            ->groupBy('rating')
            ->pluck('value', 'rating');

        // Calculate average rating
        $avgRating = DB::table('ratings')->avg('rating');
        // Calculate the values and percentages
        foreach ($ratings as &$rating) {
            $stars = intval(substr($rating['label'], 0, 1)); // Extract the star number (5, 4, 3, etc.)

            // Check if this star rating has any values in the database
            $rating['value'] = $starRatings->get($stars, 0);

            // Calculate percentage if totalRatings is greater than 0
            $rating['percent'] = $totalRatings > 0 ? round(($rating['value'] / $totalRatings) * 100, 2) : 0;
        }*/
        $data = [
            'active_plan' => $active_plan,
            'total_req' => $total_req,
            'carriers' => $carriers,
            'destinations' => $destinations,
            'shipments' => $shipments,
            'shipment_statuses' => $shipment_statuses,
            'no_update_shipment' => $no_update_shipment,
            'undelivered_shipment' => $undelivered_shipment,
            'exception_shipment' => $exception_shipment,
            'star_rating' => $ratings,
            'avg_rating' => round($avgRating, 1),
            'date_range' => $date_range,
            'plan_id' => $session->plan_id,
            'country_name' => $country_name,
        ];

        return response()->json($data);
    }
    public function analytics(Request $request)
    {

        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $carrier_array = null;

        $shipments = Fulfillment::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as shipments'))->where('session_id',$session->id) ->newQuery();

        $shipment_statuses = DB::table('fulfillments');

        $carriers = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('tracking_company')->select('tracking_company')->whereNotNull('tracking_company')
            ->distinct()->get();

        $destinations = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('country')->select('country')->whereNotNull('country')
            ->distinct()->get();


        if (isset($request->shipment_date_datefilter)) {
            $datefilter = $request->query('shipment_date_datefilter');
            if ($datefilter == 'Today') {
                $date_range = [Carbon::now()->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Custom') {
//                $start_date = Carbon::createFromFormat('D M d Y H:i:s', preg_replace('/ \(.+\)$/', '', $request->shipment_date_starting))->format('Y-m-d H:i:s');
//                $end_date = Carbon::createFromFormat('D M d Y H:i:s', $request->shipment_date_startingpreg_replace('/ \(.+\)$/', '', $request->shipment_date_ending))->format('Y-m-d H:i:s');
                $shipment_date_starting = $request->shipment_date_starting;
                $shipment_date_ending = $request->shipment_date_ending;
                $clean_starting_date = preg_replace('/GMT.*$/', '', $shipment_date_starting);
                $clean_ending_date = preg_replace('/GMT.*$/', '', $shipment_date_ending);
                $start_date = Carbon::parse($clean_starting_date)->startOfDay()->format('Y-m-d  H:i:s');
                $end_date = Carbon::parse($clean_ending_date)->endOfDay()->format('Y-m-d  H:i:s');
                $date_range = [$start_date, $end_date];
            }

            $shipments->whereBetween('created_at', $date_range);
            $shipment_statuses->whereBetween('created_at', $date_range);
        }

        if (isset($request->carrier)) {
            $carrier_array = explode(',', $request->carrier);
            $shipments->whereIn('tracking_company', $carrier_array);
            $shipment_statuses->whereIn('tracking_company',$carrier_array);
        }

        if (isset($request->destinations)) {
            $destinations_array = explode(',', $request->destinations);
            $shipments->whereIn('country',$destinations_array);
            $shipment_statuses->whereIn('country',$destinations_array);
        }

        $shipments = $shipments->groupBy('date')->get()->toArray();


        $shipment_statuses = $shipment_statuses->select(
            DB::raw('IFNULL(shipment_status, "notfound") as shipment_status'),
            DB::raw('COUNT(*) as count')
        )
            ->where('session_id',$session->id)
            ->groupBy(DB::raw('IFNULL(shipment_status, "notfound")'))->get()->toArray();

        $data = [
          'carriers' => $carriers,
          'destinations' => $destinations,
          'shipments' => $shipments,
          'shipment_statuses' => $shipment_statuses
        ];

        return response()->json($data);

        $day_deliver_by_carrier = Fulfillment::where('session_id', $session->id)
            ->whereNotNull('first_date')
            ->whereNotNull('last_date')->get()
            ->groupBy(function ($data) {
                return $data->tracking_company;
            }
            );

        if ($day_deliver_by_carrier != null) {
            $total_carrier_days = [];
            foreach ($day_deliver_by_carrier as $carrier_name => $deliver_by_carrier) {
                $total_days = null;
                $count = 1;
                $total_days_array = [];
                foreach ($deliver_by_carrier as $carrier) {
//                dd($carrier);
                    $first_date = new DateTime($carrier->first_date);;
                    $end_date = new DateTime($carrier->end_date);
                    $days = $first_date->diff($end_date);
                    $days = $days->days;
                    $total_days = $total_days + $days;

                }
                if (count($deliver_by_carrier)) {
                    $count = count($deliver_by_carrier);
                } else {
                    $count = 1;
                }
                array_push($total_carrier_days, [$carrier_name => round(($total_days / $count))]);

            }

        }

        $day_deliver_by_country = Fulfillment::where('session_id', $session->id)->whereNotNull('country')->whereNotNull('first_date')->whereNotNull('last_date')->get()->groupBy(function ($data) {
            return $data->country;
        });

        if ($day_deliver_by_country != null) {
            $total_country_carrier_days = [];
            foreach ($day_deliver_by_country as $carrier_name => $deliver_by_carrier) {
                $total_days = 0;
                $count = null;
                $total_days_array = [];
                foreach ($deliver_by_carrier as $carrier) {
//                dd($carrier);
                    $first_date = new DateTime($carrier->first_date);;
                    $end_date = new DateTime($carrier->end_date);
                    $days = $first_date->diff($end_date);
                    $days = $days->days;
                    $total_days = $total_days + $days;

                }
                if (count($deliver_by_carrier)) {
                    $count = count($deliver_by_carrier);
                } else {
                    $count = 1;
                }
                array_push($total_country_carrier_days, [$carrier_name => round(($total_days / $count))]);

            }
        }


        $carriers = DB::table('fulfillments')->where('session_id', $session->id)
            ->select(['tracking_company', DB::raw('COUNT(*) AS total')])
            ->groupBy('tracking_company')
            ->orderBy('total', 'desc') // defaults to ASC
            ->paginate(5);

        $carriers_country = DB::table('fulfillments')->where('session_id', $session->id)->whereNotNull('country')
            ->select(['country', DB::raw('COUNT(*) AS total')])
            ->groupBy('country')
            ->orderBy('total', 'desc') // defaults to ASC
            ->paginate(5);

        return response()->json([
            'carriers' => $carriers,
            'total_carrier_days' => $total_carrier_days,
            'total_country_carrier_days' => $total_country_carrier_days,
            'carriers_country' => $carriers_country
        ]);

    }
    public function analytics_order_to_delivery(Request $request)
    {

        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $carrier_array = null;

        $shipments = Fulfillment::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('(MAX(DATEDIFF( first_date,created_at))) as processing_time'), // Use MIN or MAX to aggregate,Calculate time in days
            DB::raw('CASE WHEN MAX(last_date) IS NOT NULL THEN MAX(DATEDIFF( last_date,created_at)) END as order_to_delivery_time'), // Aggregated case for order-to-delivery time,Calculate time in days
            DB::raw('count(*) as shipments'),
            // Calculate counts for each range based on processing time
            DB::raw('SUM(CASE WHEN DATEDIFF(first_date, created_at) BETWEEN 0 AND 3 THEN 1 ELSE 0 END) as processing_time_0_3'),
            DB::raw('SUM(CASE WHEN DATEDIFF(first_date, created_at) BETWEEN 4 AND 7 THEN 1 ELSE 0 END) as processing_time_4_7'),
            DB::raw('SUM(CASE WHEN DATEDIFF(first_date, created_at) BETWEEN 8 AND 11 THEN 1 ELSE 0 END) as processing_time_8_11'),
            DB::raw('SUM(CASE WHEN DATEDIFF(first_date, created_at) BETWEEN 12 AND 15 THEN 1 ELSE 0 END) as processing_time_12_15'),
            DB::raw('SUM(CASE WHEN DATEDIFF(first_date, created_at) BETWEEN 16 AND 30 THEN 1 ELSE 0 END) as processing_time_16_30'),
            DB::raw('SUM(CASE WHEN DATEDIFF(first_date, created_at) > 30 THEN 1 ELSE 0 END) as processing_time_30_plus'),
            // Calculate counts for each range based on order-to-delivery time
            DB::raw('SUM(CASE WHEN last_date IS NOT NULL AND DATEDIFF(last_date, created_at) BETWEEN 0 AND 3 THEN 1 ELSE 0 END) as order_to_delivery_0_3'),
            DB::raw('SUM(CASE WHEN last_date IS NOT NULL AND DATEDIFF(last_date, created_at) BETWEEN 4 AND 7 THEN 1 ELSE 0 END) as order_to_delivery_4_7'),
            DB::raw('SUM(CASE WHEN last_date IS NOT NULL AND DATEDIFF(last_date, created_at) BETWEEN 8 AND 11 THEN 1 ELSE 0 END) as order_to_delivery_8_11'),
            DB::raw('SUM(CASE WHEN last_date IS NOT NULL AND DATEDIFF(last_date, created_at) BETWEEN 12 AND 15 THEN 1 ELSE 0 END) as order_to_delivery_12_15'),
            DB::raw('SUM(CASE WHEN last_date IS NOT NULL AND DATEDIFF(last_date, created_at) BETWEEN 16 AND 30 THEN 1 ELSE 0 END) as order_to_delivery_16_30'),
            DB::raw('SUM(CASE WHEN last_date IS NOT NULL AND DATEDIFF(last_date, created_at) > 30 THEN 1 ELSE 0 END) as order_to_delivery_30_plus')


        )
            ->whereNotNull('first_date') // Exclude records with no delivery date
            ->where('session_id',$session->id)
            ->newQuery();


        $carriers = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('tracking_company')->select('tracking_company')->whereNotNull('tracking_company')
            ->distinct()->get();

        $destinations = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('country')->select('country')->whereNotNull('country')
            ->distinct()->get();


        if (isset($request->shipment_date_datefilter)) {
            $datefilter = $request->query('shipment_date_datefilter');
            if ($datefilter == 'Today') {
                $date_range = [Carbon::now()->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Custom') {
//                $start_date = Carbon::createFromFormat('D M d Y H:i:s', preg_replace('/ \(.+\)$/', '', $request->shipment_date_starting))->format('Y-m-d H:i:s');
//                $end_date = Carbon::createFromFormat('D M d Y H:i:s', $request->shipment_date_startingpreg_replace('/ \(.+\)$/', '', $request->shipment_date_ending))->format('Y-m-d H:i:s');
                $shipment_date_starting = $request->shipment_date_starting;
                $shipment_date_ending = $request->shipment_date_ending;
                $clean_starting_date = preg_replace('/GMT.*$/', '', $shipment_date_starting);
                $clean_ending_date = preg_replace('/GMT.*$/', '', $shipment_date_ending);
                $start_date = Carbon::parse($clean_starting_date)->startOfDay()->format('Y-m-d  H:i:s');
                $end_date = Carbon::parse($clean_ending_date)->endOfDay()->format('Y-m-d  H:i:s');
                $date_range = [$start_date, $end_date];
            }

            $shipments->whereBetween('created_at', $date_range);
        }

        if (isset($request->carrier)) {
            $carrier_array = explode(',', $request->carrier);
            $shipments->whereIn('tracking_company', $carrier_array);
        }

        if (isset($request->destinations)) {
            $destinations_array = explode(',', $request->destinations);
            $shipments->whereIn('country',$destinations_array);
        }

        $shipments = $shipments->groupBy('date')->get()->toArray();




        $data = [
          'carriers' => $carriers,
          'destinations' => $destinations,
          'shipments' => $shipments,
        ];

        return response()->json($data);

    }
    public function analytics_transit_time(Request $request)
    {

        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $carrier_array = null;

        $shipments = Fulfillment::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('(MAX(DATEDIFF( last_date,first_date))) as processing_time'), // Use MIN or MAX to aggregate,Calculate time in days
            DB::raw('AVG(DATEDIFF(last_date, first_date)) as avg_processing_time'), // Average processing time

            DB::raw('count(*) as shipments'),
            // Calculate counts for each range based on transit time
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 0 AND 3 THEN 1 ELSE 0 END) as processing_time_0_3'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 4 AND 7 THEN 1 ELSE 0 END) as processing_time_4_7'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 8 AND 11 THEN 1 ELSE 0 END) as processing_time_8_11'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 12 AND 15 THEN 1 ELSE 0 END) as processing_time_12_15'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 16 AND 30 THEN 1 ELSE 0 END) as processing_time_16_30'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) > 30 THEN 1 ELSE 0 END) as processing_time_30_plus'),
        )
            ->whereNotNull('last_date') // Exclude records with no delivery date
            ->where('session_id',$session->id) // Exclude records with no delivery date
            ->newQuery();

        $shipments_by_carrier  = Fulfillment::select(

            'tracking_company',
            DB::raw('MAX(country) as country'),
            DB::raw('MIN(DATEDIFF(last_date, first_date)) as min_transit_time'), // Maximum processing time
            DB::raw('MAX(DATEDIFF(last_date, first_date)) as max_transit_time'), // Maximum processing time
            DB::raw('AVG(DATEDIFF(last_date, first_date)) as avg_transit_time'), // Average processing time
            DB::raw('count(*) as shipments'),
            // Calculate counts for each range based on transit time
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 0 AND 3 THEN 1 ELSE 0 END) as day_0_3'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 4 AND 7 THEN 1 ELSE 0 END) as day_4_7'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 8 AND 11 THEN 1 ELSE 0 END) as day_8_11'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 12 AND 15 THEN 1 ELSE 0 END) as day_12_15'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) BETWEEN 16 AND 30 THEN 1 ELSE 0 END) as day_16_30'),
            DB::raw('SUM(CASE WHEN DATEDIFF(last_date, first_date) > 30 THEN 1 ELSE 0 END) as day_30_plus')
        )
            ->whereNotNull('last_date') // Exclude records with no delivery date
            ->where('session_id',$session->id)
            ->newQuery();
        $carriers = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('tracking_company')->select('tracking_company')->whereNotNull('tracking_company')
            ->distinct()->get();

        $destinations = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('country')->select('country')->whereNotNull('country')
            ->distinct()->get();


        if (isset($request->shipment_date_datefilter)) {
            $datefilter = $request->query('shipment_date_datefilter');
            if ($datefilter == 'Today') {
                $date_range = [Carbon::now()->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Custom') {
//                $start_date = Carbon::createFromFormat('D M d Y H:i:s', preg_replace('/ \(.+\)$/', '', $request->shipment_date_starting))->format('Y-m-d H:i:s');
//                $end_date = Carbon::createFromFormat('D M d Y H:i:s', $request->shipment_date_startingpreg_replace('/ \(.+\)$/', '', $request->shipment_date_ending))->format('Y-m-d H:i:s');
                $shipment_date_starting = $request->shipment_date_starting;
                $shipment_date_ending = $request->shipment_date_ending;
                $clean_starting_date = preg_replace('/GMT.*$/', '', $shipment_date_starting);
                $clean_ending_date = preg_replace('/GMT.*$/', '', $shipment_date_ending);
                $start_date = Carbon::parse($clean_starting_date)->startOfDay()->format('Y-m-d  H:i:s');
                $end_date = Carbon::parse($clean_ending_date)->endOfDay()->format('Y-m-d  H:i:s');
                $date_range = [$start_date, $end_date];
            }

            $shipments->whereBetween('created_at', $date_range);
            $shipments_by_carrier->whereBetween('created_at', $date_range);
        }

        if (isset($request->carrier)) {
            $carrier_array = explode(',', $request->carrier);
            $shipments->whereIn('tracking_company', $carrier_array);
            $shipments_by_carrier->whereIn('tracking_company', $carrier_array);
        }

        if (isset($request->destinations)) {
            $destinations_array = explode(',', $request->destinations);
            $shipments->whereIn('country',$destinations_array);
            $shipments_by_carrier->whereIn('country',$destinations_array);
        }

        $shipments = $shipments->groupBy('date')->get()->toArray();
        $shipments_by_carrier = $shipments_by_carrier->groupBy('tracking_company')->get()->toArray();




        $data = [
          'carriers' => $carriers,
          'destinations' => $destinations,
          'shipments' => $shipments,
          'shipments_by_carrier' => $shipments_by_carrier,
        ];
        return response()->json($data);
    }
    public function analytics_tracking_page(Request $request)
    {

        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $page_click=PageView::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(click_count) as click_count'),
        )->where('shop_id',$session->id)->newQuery();
        $total_page_click=PageView::where('shop_id',$session->id)->count();
        $recommended_product_click=RecommendedProductView::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(click_count) as click_count'),
        )->where('shop_id',$session->id)->newQuery();
        $total_recommended_product_click=RecommendedProductView::where('shop_id',$session->id)->count();
        if (isset($request->shipment_date_datefilter)) {
            $datefilter = $request->query('shipment_date_datefilter');
            if ($datefilter == 'Today') {
                $date_range = [Carbon::now()->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Custom') {
//                $start_date = Carbon::createFromFormat('D M d Y H:i:s', preg_replace('/ \(.+\)$/', '', $request->shipment_date_starting))->format('Y-m-d H:i:s');
//                $end_date = Carbon::createFromFormat('D M d Y H:i:s', $request->shipment_date_startingpreg_replace('/ \(.+\)$/', '', $request->shipment_date_ending))->format('Y-m-d H:i:s');
                $shipment_date_starting = $request->shipment_date_starting;
                $shipment_date_ending = $request->shipment_date_ending;
                $clean_starting_date = preg_replace('/GMT.*$/', '', $shipment_date_starting);
                $clean_ending_date = preg_replace('/GMT.*$/', '', $shipment_date_ending);
                $start_date = Carbon::parse($clean_starting_date)->startOfDay()->format('Y-m-d  H:i:s');
                $end_date = Carbon::parse($clean_ending_date)->endOfDay()->format('Y-m-d  H:i:s');
                $date_range = [$start_date, $end_date];
            }

            $page_click->whereBetween('created_at', $date_range);
            $recommended_product_click->whereBetween('created_at', $date_range);
        }


        $page_click = $page_click->groupBy('date')->get()->toArray();
        $recommended_product_click = $recommended_product_click->groupBy('date')->get()->toArray();




        $data = [
          'carriers' => [],
          'destinations' => [],
          'total_page_click' => $total_page_click,
          'page_click' => $page_click,
          'total_recommended_product_click' => $total_recommended_product_click,
          'recommended_product_click' => $recommended_product_click,
        ];
        return response()->json($data);
    }
    public function analytics_exception_page(Request $request)
    {

        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $carrier_array = null;


        $carriers = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('tracking_company')->select('tracking_company')->whereNotNull('tracking_company')
            ->distinct()->get();

        $destinations = Fulfillment::where('session_id', $session->id)->whereNotNull('tracking_company')
            ->orderBy('country')->select('country')->whereNotNull('country')
            ->distinct()->get();

        $date_range = [Carbon::now()->subDays(90)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];

        if (isset($request->shipment_date_datefilter)) {
            $datefilter = $request->query('shipment_date_datefilter');
            if ($datefilter == 'Today') {
                $date_range = [Carbon::now()->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 7 days') {
                $date_range = [Carbon::now()->subDays(7)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 30 days') {
                $date_range = [Carbon::now()->subDays(30)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 60 days') {
                $date_range = [Carbon::now()->subDays(60)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Last 90 days') {
                $date_range = [Carbon::now()->subDays(90)->startOfDay()->format('Y-m-d  H:i:s'), Carbon::now()->endOfDay()->format('Y-m-d  H:i:s')];
            } elseif ($datefilter == 'Custom') {
//                $start_date = Carbon::createFromFormat('D M d Y H:i:s', preg_replace('/ \(.+\)$/', '', $request->shipment_date_starting))->format('Y-m-d H:i:s');
//                $end_date = Carbon::createFromFormat('D M d Y H:i:s', $request->shipment_date_startingpreg_replace('/ \(.+\)$/', '', $request->shipment_date_ending))->format('Y-m-d H:i:s');
                $shipment_date_starting = $request->shipment_date_starting;
                $shipment_date_ending = $request->shipment_date_ending;
                $clean_starting_date = preg_replace('/GMT.*$/', '', $shipment_date_starting);
                $clean_ending_date = preg_replace('/GMT.*$/', '', $shipment_date_ending);
                $start_date = Carbon::parse($clean_starting_date)->startOfDay()->format('Y-m-d  H:i:s');
                $end_date = Carbon::parse($clean_ending_date)->endOfDay()->format('Y-m-d  H:i:s');
                $date_range = [$start_date, $end_date];
            }
        }
        $shipments = Fulfillment::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as shipments'))
            ->where('session_id', $session->id)->where('shipment_status','exception')->whereBetween('created_at', $date_range)->newQuery();
        $shipments_by_carrier = Fulfillment::select(
            'tracking_company', // Include tracking_company in the select
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as shipments')
        )
            ->where('session_id', $session->id)
            ->where('shipment_status','exception')
            ->whereBetween('created_at', $date_range)->newQuery();

        $total_shipments = Fulfillment::whereBetween('created_at', $date_range)->newQuery();

        $shipment_statuses = DB::table('fulfillments')->select(
            DB::raw('shipment_substatus'),
            DB::raw('COUNT(*) as count')
        )->where('shipment_status','exception')
            ->where('session_id', $session->id)->whereBetween('created_at', $date_range);
        if (isset($request->carrier)) {
            $carrier_array = explode(',', $request->carrier);
            $shipments_by_carrier->whereIn('tracking_company', $carrier_array);
            $shipments->whereIn('tracking_company', $carrier_array);
            $shipment_statuses->whereIn('tracking_company',$carrier_array);
            $total_shipments->whereIn('tracking_company',$carrier_array);
        }

        if (isset($request->destinations)) {
            $destinations_array = explode(',', $request->destinations);
            $shipments_by_carrier->whereIn('country',$destinations_array);
            $shipments->whereIn('country',$destinations_array);
            $shipment_statuses->whereIn('country',$destinations_array);
            $total_shipments->whereIn('country',$destinations_array);
        }

        $total_shipments = $total_shipments->count();
        $shipments = $shipments->groupBy('date')->get()->toArray();
        $shipments_by_carrier = $shipments_by_carrier->groupBy('tracking_company','date')->get()->toArray();
        $shipment_statuses = $shipment_statuses->groupBy('shipment_substatus')->get()->toArray();


        $data = [
            'carriers' => $carriers,
            'destinations' => $destinations,
            'total_shipments' => $total_shipments,
            'shipments' => $shipments,
            'shipments_by_carrier' => $shipments_by_carrier,
            'shipment_statuses' => $shipment_statuses,
            'date_range' => $date_range,
            'plan_id' => $session->plan_id
        ];

        return response()->json($data);
    }

    public function analytics_detail(Request $request, $analytics_type)
    {
        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);


        $day_deliver_by_carrier = Fulfillment::where('session_id', $session->id)->whereNotNull('first_date')->whereNotNull('tracking_number')
//        $day_deliver_by_carrier = Fulfillment::whereNotNull('first_date')->whereNotNull('tracking_number')
            ->whereNotNull('last_date')->get()->groupBy(function ($data) {
                return $data->tracking_company;
            });
        if ($day_deliver_by_carrier != null) {
            $total_carrier_days = [];
            foreach ($day_deliver_by_carrier as $carrier_name => $deliver_by_carrier) {
                $total_days = null;
                $count = 1;
                $total_days_array = [];
                foreach ($deliver_by_carrier as $carrier) {
//                dd($carrier);
                    $first_date = new DateTime($carrier->first_date);
                    $end_date = new DateTime($carrier->end_date);
                    $days = $first_date->diff($end_date);
                    $days = $days->days;
                    $total_days = $total_days + $days;

                }
                if (count($deliver_by_carrier)) {
                    $count = count($deliver_by_carrier);
                } else {
                    $count = 1;
                }
                array_push($total_carrier_days, [$carrier_name => round(($total_days / $count))]);

            }

        }

        $day_deliver_by_country = Fulfillment::where('session_id', $session->id)->whereNotNull('country')
//        $day_deliver_by_country = Fulfillment::whereNotNull('country')
            ->whereNotNull('first_date')->whereNotNull('last_date')->get()->groupBy(function ($data) {
                return $data->country;
            });
        if ($day_deliver_by_country != null) {
            $total_country_carrier_days = [];
            foreach ($day_deliver_by_country as $carrier_name => $deliver_by_carrier) {
                $total_days = 0;
                $count = null;
                $total_days_array = [];
                foreach ($deliver_by_carrier as $carrier) {
//                dd($carrier);
                    $first_date = new DateTime($carrier->first_date);;
                    $end_date = new DateTime($carrier->end_date);
                    $days = $first_date->diff($end_date);
                    $days = $days->days;
                    $total_days = $total_days + $days;

                }
                if (count($deliver_by_carrier)) {
                    $count = count($deliver_by_carrier);
                } else {
                    $count = 1;
                }
                array_push($total_country_carrier_days, [$carrier_name => round(($total_days / $count))]);

            }
        }


        $carriers = DB::table('fulfillments')->where('session_id', $session->id)
//        $carriers = DB::table('fulfillments')
            ->select(['tracking_company', DB::raw('COUNT(*) AS total')])
            ->groupBy('tracking_company')
            ->orderBy('total', 'desc') // defaults to ASC
            ->get();

        $carriers_country = DB::table('fulfillments')->where('session_id', $session->id)->whereNotNull('country')
//        $carriers_country = DB::table('fulfillments')->whereNotNull('country')
            ->select(['country', DB::raw('COUNT(*) AS total')])
            ->groupBy('country')
            ->orderBy('total', 'desc') // defaults to ASC
            ->get();

        $data = array(
            'carriers' => $carriers,
            'total_carrier_days' => $total_carrier_days,
            'total_country_carrier_days' => $total_country_carrier_days,
            'carriers_country' => $carriers_country,
            'analytics_type' => $analytics_type
        );
        return response()->json($data);

    }


}
