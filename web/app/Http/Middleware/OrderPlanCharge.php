<?php

namespace App\Http\Middleware;


use App\Http\Controllers\HelperController;
use App\Http\Controllers\PlanController;
use App\Models\Charge;
use App\Models\ErrorMessage;
use App\Models\Feedback;
use App\Models\Plan;
use App\Models\Response;
use App\Models\Session;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Shopify\Clients\Graphql;


class OrderPlanCharge
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public $helper;

    function __construct()
    {
        $this->helper = new HelperController();
    }

    public function handle($request, Closure $next)
    {

        $user = Session::where('shop', $request->query('shop'))->first();
        $plan_controller = new PlanController();

        if (isset($user)) {
            if (is_billing_free_shop($user->shop)) {
                $plan_controller->ensureBillingFreeShopPlan($user);
                return $next($request);
            }

            $response = $this->helper->getShopApi($user->shop)->rest('get', '/admin/recurring_application_charges.json');

            if ($response['errors'] == false) {
                $all_recurring_charges = json_decode(json_encode($response['body']['recurring_application_charges']), false);
                $plan_controller->createUpdateChargeTable($user);
                if (isset($user->plan_id)) {
                    $charge = Charge::where('session_id', $user->id)->where('plan_id', $user->plan_id)->orderBy('created_at', 'desc')->first();
                    if ((isset($charge) && ($charge->status == "active" || $charge->status == "ACTIVE")) ? true : false) {

                        if (isset($charge->trial_ends_on)) {
                            $now = time();
                            $your_date = strtotime($charge->trial_ends_on);
                            $date_diff = $now - $your_date;
                            $billing_days_diff = round($date_diff / (60 * 60 * 24));

                            if ($billing_days_diff > 30) {
                                $plan_controller->createUpdateChargeTable($user);
                            } else {
                                $plan = Plan::find($charge->plan_id);
                                $user->plan_id = $plan->id;
                                $user->save();
                                $this->createUpdateFeedbackResponses($user);
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


//            $widget_controller = new WidgetController();
//            $widget_controller->createUpdateShopWidgetStatuses($user);
        }


        return $next($request);
    }

    public function createUpdateFeedbackResponses($user)
    {
        if (isset($user->plan_id)) {
            $charge = Charge::where('session_id', $user->id)->where('plan_id', $user->plan_id)->orderBy('created_at', 'desc')->first();
            if (isset($user->plan_id)) {
                $active_plan = Plan::find(intval($user->plan_id));
                if (isset($active_plan)) {
                    $feedbacks_count = Feedback::where('session_id', $user->id)->count();

                    if ($feedbacks_count >= $active_plan->response_limit) {
                        $user->survey_limit = 'limit reached';
                        $user->save();
                    } else {
                        $user->survey_limit = 'no limit reached';
                        $user->save();
                    }
                }
            } else if ((isset($charge) && isset($charge->trial_ends_on) && ($charge->status == "active" || $charge->status == "ACTIVE")) ? true : false) {
                $active_plan = Plan::find($charge->plan_id);
                $now = time();
                $your_date = strtotime($charge->trial_ends_on);
                $date_diff = $now - $your_date;
                $billing_days_diff = round($date_diff / (60 * 60 * 24));

                if ($billing_days_diff <= 30) {
                    $feedbacks_count = Feedback::where('session_id', $user->id)
                        ->where('created_at', '>=', $charge->trial_ends_on)->count(); // Assuming you're using Carbon for
                    if ($active_plan->response_limit == 10000000000) {
                        $user->survey_limit = 'no limit reached';
                        $user->save();
                    } else {
                        if ($feedbacks_count < $active_plan->response_limit) {
                            $user->survey_limit = 'no limit reached';
                            $user->save();
                        } else {
                            $user->survey_limit = 'limit reached';
                            $user->save();
                        }
                    }
                }
            }
        } else {
            if (isset($user)) {
                $user->survey_limit = 'no limit reached';
                $user->save();
            }
        }
    }
}
