<?php

namespace App\Http\Controllers;

use App\Http\Middleware\OrderPlanCharge;
use App\Models\Charge;
use App\Models\Plan;
use App\Models\Response;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Shopify\Clients\Graphql;

class PlanController extends HelperController
{

    public function all_plans(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $active_plan = null;
        $common_controller = new CommonController();
        $total_req=$common_controller->get_api_statistics($session);
//        $all_plans = Plan::all();
        $starter_plans = Plan::where('category','Starter')->get();
        $growth_plans = Plan::where('category','Growth')->get();
        $advance_plans = Plan::where('category','Advanced')->get();
        if (isset($session) && isset($session->plan_id)) {
            $active_plan = Plan::find($session->plan_id);
        }
//            $active_plan = Plan::first();
        $data = [
//            'all_plans' => $all_plans,
            'starter_plans' => $starter_plans,
            'growth_plans' => $growth_plans,
            'advance_plans' => $advance_plans,
            'active_plan' => $active_plan,
            'total_req' => $total_req
        ];
        return response()->json($data);
    }

    public function account_data(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $active_plan = null;

        if (isset($session) && isset($session->plan_id)) {
            $active_plan = Plan::find($session->plan_id);
        }

        $charge = Charge::where('session_id', $session->id)->where('plan_id', $session->plan_id)->orderBy('created_at', 'desc')->first();
        $charge_temp = false;
        if ((isset($charge) && isset($charge->trial_ends_on) && ($charge->status == "active" || $charge->status == "ACTIVE")) ? true : false) {
            $active_plan = Plan::find($charge->plan_id);
            $charge_temp = true;
            $now = time();
            $your_date = strtotime($charge->trial_ends_on);
            $date_diff = $now - $your_date;
            $billing_days_diff = round($date_diff / (60 * 60 * 24));


            $survey_percentage = null;
            $days_elapsed_percentage = null;
            $responses_count = 0;

            if ($billing_days_diff <= 30) {
                $responses_count = Response::where('session_id', $session->id)
                    ->where('created_at', '>=', $charge->trial_ends_on)->count(); // Assuming you're using Carbon for
                if ($active_plan->response_limit == 10000000000) {

                } else {
                    // Calculate percentage of completed survey quota
                    $survey_percentage = ($responses_count / $active_plan->response_limit) * 100;
                    $trial_ends_on = strtotime($charge->trial_ends_on);
                    $total_days = ($trial_ends_on - $now) / (60 * 60 * 24);
                    $days_elapsed_percentage = min(($total_days / 30) * 100, 100);
                }
            }
        }

        unset($session['access_token']);

        $data = [
            'active_plan' => $active_plan,
            'active_charge' => $charge_temp == true ? $charge : null,
            'responses_count' => $responses_count,
            'survey_percentage' => $survey_percentage,
            'days_elapsed_percentage' => $days_elapsed_percentage,
            'user' => $session,
            'shop_name' => explode('.myshopify.com', $session->shop)[0],
        ];
        return response()->json($data);
    }

    public function shop_data(Request $request)
    {
        $session = $this->getShop($request);
        try {
            if (isset($session) && isset($session->user_email)) {
                $shop_data_api_res = $this->getShopApi($session->shop)
                    ->rest('get','/admin/shop.json');
                if($shop_data_api_res['errors'] == false){
                    $shop_data_api_res = json_decode(json_encode($shop_data_api_res),false);
                    $shop_data_api_res = $shop_data_api_res->body->shop;
                    $session->user_first_name = $shop_data_api_res->name;
                    $session->user_email = $shop_data_api_res->email;
                    $session->user_last_name = $shop_data_api_res->shop_owner;
                    $session->save();
                }
            }
        }catch (\Exception $e) {

        }
        unset($session['access_token']);
//        $percentage = ($session->used_credits / $session->credits) * 100;
        $data = [
            'shop_name' => $session->shop,
            'session' => $session,
        ];
        return response()->json($data);
    }

    public function check_plan(Request $request)
    {
        $session = $this->getShop($request);
        if($session->plan_id==1 ||$session->plan_id==null){
            $data = [
                'plan_selected' => 0
            ];
        }else{
            $data = [
                'plan_selected' => 0
            ];
        }

        return response()->json($data);
    }
    public function active_plan(Request $request)
    {
        $session = $this->getShop($request);
        try {
            $id = $request['plan_id'];
            $res = $this->planCreate($id, $session);
            if($res != 'error'){
                $data = [
                    'confirmation_url' => $res,
                    'status' => 'success'
                ];
            }else{
                $data = [
                    'message' => "Error!",
                    'status' => 'error',
                ];
            }


        } catch (\Exception $exception) {
            $data = [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }

        return response($data);
    }

    public function oneTimePlanCreate($id, $shop)
    {

        $plan = Plan::find($id);

        $shop_url = env('APP_URL') . "/api/check-onetime-charge?&shop=$shop->shop";
//        $shop_url = "https://until-analytical-drag-bobby.trycloudflare.com/api/check-charge?&shop=$shop->shop";

        $query2 = <<<QUERY
  mutation appPurchaseOneTimeCreate(\$name: String!, \$price: MoneyInput!, \$returnUrl: URL!, \$test: Boolean) {
  appPurchaseOneTimeCreate(name: \$name, price: \$price, returnUrl: \$returnUrl, test: \$test) {
    appPurchaseOneTime {
      id
      createdAt
      name
      price {
        amount
        currencyCode
      }
      status
      test
    }
    confirmationUrl
    userErrors {
      field
      message
    }
  }
}
QUERY;


        $variables = [
            "name" => "$plan->name",
            "price" => [
                "amount" => floatval($plan->price),
                "currencyCode" => "USD",
            ],
            "returnUrl" => $shop_url,
            "test" => isset($plan->test) && $plan->test == 1 ? true : false
        ];

        $response = $this->getShopApi($shop->shop)->graph($query2,$variables);
//        dd($response);
        if($response['errors'] == false){
            $response = json_decode(json_encode($response['body']),false);
//            dd($response);
            if(isset($response) && isset($response->data) && isset($response->data->appPurchaseOneTimeCreate) && isset($response->data->appPurchaseOneTimeCreate->confirmationUrl)){
                $confirmation_url = $response->data->appPurchaseOneTimeCreate->confirmationUrl;

                $oneTimeNode = $response->data->appPurchaseOneTimeCreate;

                $plan = Plan::where('name',$oneTimeNode->appPurchaseOneTime->name)->first();

                $charge = new Charge();
                $charge->name = $oneTimeNode->appPurchaseOneTime->name;
                $charge->charge_id = intval(explode('gid://shopify/AppPurchaseOneTime/',$oneTimeNode->appPurchaseOneTime->id)[1]);
                $charge->plan_id = isset($plan) ? $plan->id : null;
                $charge->status = isset($oneTimeNode->status)?$oneTimeNode->status:null;
                $charge->price = $plan->price;
                $charge->type = "ONE_TIME";
                $charge->test = $oneTimeNode->appPurchaseOneTime->status;
                $charge->shop_id = $shop->id;
                $charge->save();

                return $confirmation_url;
            }else{
                return 'error';
            }

        }else{
            return 'error';
        }

    }

    public function planCreate($id, $shop)
    {

        $plan = Plan::find($id);

        $shop_url = env('APP_URL') . "/api/check-charge?&shop=$shop->shop";
//        $shop_url = "https://until-analytical-drag-bobby.trycloudflare.com/api/check-charge?&shop=$shop->shop";
        $terms=$plan->terms;
        /*if(isset($plan->usage_charges)){
            if(($plan->usage_charges)>0){
                $extra_text=($plan->unlimited)?"":"extra ";
                $terms=$plan->terms."<br> $".$plan->usage_charges."  per ".$extra_text."shipping";
            }
        }*/
        $plan_data = [
            "recurring_application_charge" => [
                "name" => $plan->name,
                "price" => $plan->price,
                "return_url" => $shop_url,
                "trial_days" => $plan->trial_days,
                "test" => ($plan->test == 0) ? false : true,
                "terms" => $terms,
                "capped_amount" => $plan->capped_amount,

            ]
        ];

        $response = $this->getShopApi($shop->shop)->rest('post', '/admin/recurring_application_charges.json', $plan_data);
//        $response=json_decode(json_encode($response),false);
//         dd($response);
        if ($response['errors'] == false) {
            $response = $response['body']['recurring_application_charge'];

            $plan = Plan::where('name', $response->name)->first();

            $charge = new Charge();
            $charge->name = $response->name;
            $charge->charge_id = $response->id;
            $charge->plan_id = isset($plan) ? $plan->id : null;
            $charge->status = $response->status;
            $charge->price = $response->price;
            $charge->type = "ONE_TIME";
            $charge->capped_amount = isset($response->capped_amount) ? $response->capped_amount : null;
            $charge->trial_days = $response->trial_days;
            $charge->billing_on = $response->billing_on;
            $charge->trial_ends_on = $response->trial_ends_on;
            $charge->test = $response->test;
            $charge->terms = isset($plan->terms) ? $plan->terms : null;
            $charge->activated_on = $response->activated_on;
            $charge->cancelled_on = $response->cancelled_on;
            $charge->session_id = $shop->id;
            $charge->save();

            return $response->confirmation_url;
        } else {
            return "error";
        }


    }

    public function CheckCharge(Request $request)
    {

        $shop = Session::where('shop', $request['shop'])->first();

        $response = $this->getShopApi($shop->shop)->rest('GET', '/admin/recurring_application_charges/' . $request['charge_id'] . '.json');

        if ($response['errors'] == false) {
            $response = $response['body']['recurring_application_charge'];

            if ($response->status == 'active') {
                $charge = Charge::where('charge_id', $response->id)->first();
                if ($charge == null) {
                    $charge = new Charge();
                }
                $charge->status = 'active';
                $charge->capped_amount = isset($response->capped_amount) ? $response->capped_amount : null;
                $charge->billing_on = $response->billing_on;
                $charge->activated_on = $response->activated_on;
                $charge->cancelled_on = $response->cancelled_on;
                $charge->trial_ends_on = $response->trial_ends_on;
                $charge->save();

            }
            $this->check_app_active_plan($shop['shop']);

        }

        return redirect("https://admin.shopify.com/store/" . explode('.myshopify.com', $shop->shop)[0] . "/apps/".env('APP_HANDLE'));
    }

    public function check_app_active_plan($shop)
    {
        $shop = Session::where('shop', $shop)->first();

        $response = $this->getShopApi($shop->shop)->rest('get', '/admin/recurring_application_charges.json');

        if ($response['errors'] == false) {
            $existing_ids = [];
            $charges_response = $response['body']['recurring_application_charges'];
            foreach ($charges_response as $response) {
                $charge = Charge::where('charge_id', $response->id)->first();

                array_push($existing_ids,$response->id);
                $plan = Plan::where('name', $response->name)->where('price',$response->price)->first();
                if ($charge == null) {
                    $charge = new Charge();
                }
                $charge->name = $response->name;
                $charge->charge_id = $response->id;
                $charge->plan_id = isset($plan) ? $plan->id : null;
                $charge->status = $response->status;
                $charge->price = $response->price;
                $charge->type = "RECURRING";
                $charge->capped_amount = isset($response->capped_amount) ? $response->capped_amount : null;
                $charge->trial_days = $response->trial_days;
                $charge->billing_on = $response->billing_on;
                $charge->trial_ends_on = $response->trial_ends_on;
                $charge->test = $response->test;
                $charge->terms = isset($plan->terms) ? $plan->terms : null;
                $charge->activated_on = $response->activated_on;
                $charge->cancelled_on = $response->cancelled_on;
                $charge->session_id = $shop->id;
                $charge->created_at = Carbon::createFromTimeString($response->created_at)->format('Y-m-d H:i:s');;
                $charge->save();
            }


            $delete_non_existing_charges = Charge::where('session_id',$shop->id)->whereNotIn('charge_id',$existing_ids)->get();
            if($delete_non_existing_charges->count()){
                foreach ($delete_non_existing_charges as $delete_non_existing){
                    $delete_non_existing->delete();
                }
            }

        }


        $active_charge = Charge::where('status', 'active')->where('session_id', $shop->id)->first();
        if (isset($active_charge)) {
            $plan = Plan::find($active_charge->plan_id);
            $shop->plan_id = isset($plan)?$plan->id:1;
            $shop->save();
        } else {
            $shop->plan_id = 1;
            $shop->save();
        }

        return $response;
    }

    public function billing_redirect_url($user)
    {
        $charge = \App\Models\Charge::where('session_id', $user->id)->latest()->first();

        $billing_page_url = null;

        if ($user->plan_id == null || $charge == null || $charge->status != 'active') {

            $check_active_plan = $this->check_app_active_plan($user->shop);

            $shop_to_check_plan = \App\Models\Session::where('shop', $user->shop)->first();

            if (isset($shop_to_check_plan) && isset($shop_to_check_plan->plan_id)) {

            } else {
                $billing_page_url = $this->PlanCreate($user->shop,null);

                if ($billing_page_url == 'error') {
                    $billing_page_url = null;
                }

            }
        }


        return $billing_page_url;


    }

    public function createUpdateChargeTable($user)
    {

        $response = $this->getShopApi($user->shop)->rest('get', '/admin/recurring_application_charges.json');

        if ($response['errors'] == false) {

            $all_recurring_charges = $response['body']['recurring_application_charges']['container'];
//            we reverse the charges response, because last record of the charge will be the lastest status of the plan charge.
            $all_recurring_charges = array_reverse($all_recurring_charges);

            foreach ($all_recurring_charges as $all_recurring_charge) {
//                $plan = Plan::where('name',$all_recurring_charge->name)->first();
                $all_recurring_charge = json_decode(json_encode($all_recurring_charge), false);
                $plan = Plan::where('name', $all_recurring_charge->name)->first();
                $charge = Charge::where('session_id', $user->id)->where('charge_id', $all_recurring_charge->id)->first();
                if ($charge == null) {
                    $charge = new Charge();
                }
                $charge->session_id = $user->id;
                $charge->charge_id = $all_recurring_charge->id;
                $charge->plan_id = $plan->id;
                $charge->terms = $plan->terms;
                $charge->type = $plan->type;
                $charge->price = $all_recurring_charge->price;
                $charge->status = $all_recurring_charge->status;
                $charge->name = $all_recurring_charge->name;
                $charge->billing_on = $all_recurring_charge->billing_on;
                $charge->created_at = $all_recurring_charge->created_at;
                $charge->updated_at = $all_recurring_charge->updated_at;
                $charge->activated_on = $all_recurring_charge->activated_on;
                $charge->cancelled_on = $all_recurring_charge->cancelled_on;
                $charge->trial_days = $all_recurring_charge->trial_days;
                $charge->capped_amount = isset($all_recurring_charge->capped_amount) ? $all_recurring_charge->capped_amount : 0;
                $charge->trial_ends_on = $all_recurring_charge->trial_ends_on;
                $charge->test = (isset($all_recurring_charge->test) && $all_recurring_charge->test == true) ? 1 : 0;
                $charge->save();
            }

            $active_charge = Charge::where('session_id', $user->id)->orderBy('created_at', 'desc')->first();
            if ((isset($active_charge) && ($active_charge->status == "ACTIVE" || $active_charge->status == "active")) ? true : false) { #if plan is active
                $plan = Plan::find($active_charge->plan_id);
                $user->plan_id = $plan->id;
                $user->save();
            } else {
                $user->plan_id = null;
                $user->save();
            }

        } else {
            $user->plan_id = null;
            $user->save();
        }
    }
}
