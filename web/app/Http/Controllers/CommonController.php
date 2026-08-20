<?php

namespace App\Http\Controllers;


use App\Models\ApiStatistics;
use App\Models\Charge;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function api_statistics($shop_id,$order_id,$fulfillment_id){
        $api_stats = new ApiStatistics();
        $api_stats->session_id = $shop_id;
        $api_stats->order_id = $order_id;
        $api_stats->fulfillment_id = $fulfillment_id;
        $api_stats->request_count = 1;
        $api_stats->save();
    }
    public function get_api_statistics($shop){
        $charge=Charge::where('session_id',$shop->id)
            ->where('status','active')
            ->latest()->first();
        $date = $shop->created_at;

        if($charge){
            $date = $charge->created_at;
        }
        $created_day = Carbon::parse($date)->day;
        $current_day = Carbon::now()->day;
        if($current_day>=$created_day){
            $current_month = Carbon::now()->format('Y-m');
            $start_date = Carbon::parse("{$current_month}-{$created_day}")->startOfDay();
            $end_date = $start_date->copy()->addDays(29);
        }else{
            $current_month = Carbon::now()->format('m')-1;
            $current_year = Carbon::now()->format('Y');
            $start_date = Carbon::parse("{$current_year}-{$current_month}-{$created_day}")->startOfDay();
            $end_date = $start_date->copy()->addDays(29);
        }

        $api_stats =  ApiStatistics::where('session_id',$shop->id)
            ->whereBetween('created_at', [$start_date, $end_date])
            ->count();
        return $api_stats;
    }
}
