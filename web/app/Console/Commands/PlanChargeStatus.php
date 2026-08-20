<?php

namespace App\Console\Commands;


use App\Http\Controllers\HelperController;

use App\Http\Controllers\WidgetController;
use App\Http\Middleware\OrderPlanCharge;
use App\Models\Charge;
use App\Models\Plan;
use App\Models\Session;
use Illuminate\Console\Command;
use Shopify\Clients\Graphql;

class PlanChargeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PlanChargeStatus:cron';

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

        Session::orderBy('id')->chunk(50, function ($users) {
            $helper_controller = new HelperController();
            foreach ($users as $user) {
                if (isset($user) && isset($user->plan_id)) {

                    $response = $helper_controller->getShopApi($user->shop)->rest('get', '/admin/recurring_application_charges.json');
                    $all_recurring_charges = $response;

                    if ($all_recurring_charges['errors'] == false) {
                        $all_recurring_charges = $all_recurring_charges['body']['recurring_application_charges']['container'];
                        if (isset($all_recurring_charges[0]) && (strtoupper($all_recurring_charges[0]['status']) == 'ACTIVE')) {

                        } else {
                            //                    first status is not active create update the charge table
                            $this->createUpdateChargeTable($user);
                        }

                        $charge = Charge::where('session_id', $user->id)->where('plan_id', $user->plan_id)->orderBy('created_at', 'desc')->first();
                        if (isset($charge)) {
                            if ((strtoupper($charge->status) == "ACTIVE") ? true : false) {
                                if (isset($charge->billing_on)) {
                                    $now = time(); // or your date as well
                                    $your_date = strtotime($charge->billing_on);
                                    $date_diff = $now - $your_date;
                                    $billing_days_diff = round($date_diff / (60 * 60 * 24));

                                    if ($billing_days_diff > 30) {
                                        $this->createUpdateChargeTable($user);
                                    } else {
                                        $active_plan = Plan::find($charge->plan_id);
                                        $user->plan_id = $active_plan->id;
                                        $user->save();
                                    }
                                } else {
                                    $user->plan_id = null;
                                    $user->save();
                                }
                            }
                        } else {
                            $user->plan_id = null;
                            $user->save();
                        }
                    } else {
                        $user->plan_id = null;
                        $user->save();
                    }

                } else {
                    $user->plan_id = null;
                    $user->save();
                }

            }
        });

        return 0;
    }


    public function createUpdateChargeTable($user)
    {
        $helper_controller = new HelperController();
        $response = $helper_controller->getShopApi($user->shop)->rest('get', '/admin/recurring_application_charges.json');

        if ($response['errors'] == false) {

            $all_recurring_charges = $response['body']['recurring_application_charges']['container'];
//            we reverse the charges response, because last record of the charge will be the lastest status of the plan charge.
            $all_recurring_charges = array_reverse($all_recurring_charges);
            $existing_ids = [];

            foreach ($all_recurring_charges as $all_recurring_charge) {
//                $plan = Plan::where('name',$all_recurring_charge->name)->first();
                $all_recurring_charge = json_decode(json_encode($all_recurring_charge), false);
                $plan = Plan::where('name', $all_recurring_charge->name)->first();
                array_push($existing_ids, $all_recurring_charge->id);
                $charge = Charge::where('user_id', $user->id)->where('charge_id', $all_recurring_charge->id)->first();
                if ($charge == null) {
                    $charge = new Charge();
                }
                $charge->user_id = $user->id;
                $charge->charge_id = $all_recurring_charge->id;
                $charge->plan_id = $plan->id;
                $charge->terms = $plan->terms;
                $charge->type = $plan->type;
                $charge->price = $all_recurring_charge->price;
                $charge->status = strtoupper($all_recurring_charge->status);
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


            $delete_non_existing_charges = Charge::where('user_id', $user->id)->whereNotIn('charge_id', $existing_ids)->get();
            if ($delete_non_existing_charges->count()) {
                foreach ($delete_non_existing_charges as $delete_non_existing) {
                    $delete_non_existing->delete();
                }
            }

            $active_charge = Charge::where('user_id', $user->id)->where('status','active')->orderBy('created_at', 'desc')->first();
//            dd($active_charge);
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

        $order_plan = new OrderPlanCharge();
        $order_plan->createUpdateFeedbackResponses($user);
    }
}
