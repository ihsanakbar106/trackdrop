<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Session;
use App\Models\Setting;
use App\Models\SlackNotification;
use App\Models\SlackSetting;
use App\Models\WebNotification;
use Illuminate\Http\Request;

class SlackController extends HelperController
{
    public function connect_slack($session = null)
    {
        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        if ($session == null) {
            $session = Session::first();
        }
        $setting = Setting::first();
        if (isset($setting)) {
            $url = "https://slack.com/oauth/v2/authorize?state=$session->shop&client_id=$setting->slack_client_id&scope=channels:history,channels:join,channels:manage,channels:write.invites,channels:write.topic,chat:write,chat:write.public,users:read,channels:read,groups:read,mpim:read,im:read,incoming-webhook";
            return response()->json([
                'status' => 'success',
                'url' => $url
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Slack APP keys not found!'
            ]);
        }

    }

    public function slack_redirect(Request $request)
    {

        $error = new \App\Models\ErrorMessage();
        $error->message = "Error message:" . json_encode($request->all());
        $error->save();

        $code = $request->code;
        $shop_name = $request->state;

        $session = Session::where('shop', $shop_name)->first();
        if ($session == null) {
            return response()->json([
                'status' => 'error',
                'message' => "Slack code not found!",
                'response' => null
            ]);
        }
        $notification = Notification::where('notification_type', 'Slack Notification')->where('session_id', $session->id)->first();
        $domain_name = explode('.myshopify.com', $session->shop);

        if (isset($code)) {

            $codeToAccessToken = $this->codeToAccessToken($code);
            if ($codeToAccessToken->ok == true) {

//                $accessTokenToRegenerateToken = $this->accessTokenToRegenerateToken($codeToAccessToken);
                $slack_setting = SlackSetting::where('session_id', $session->id)->first();
                if ($slack_setting == null) {
                    $slack_setting = new SlackSetting();
                }
                $slack_setting->session_id = $session->id;
                $slack_setting->code = $code;
                $slack_setting->slack_access_token = $codeToAccessToken->access_token;
                $slack_setting->slack_refresh_token = $codeToAccessToken->refresh_token;
                $slack_setting->slack_webhook_url = optional($codeToAccessToken->incoming_webhook)->url;
                $slack_setting->channel_name = optional($codeToAccessToken->incoming_webhook)->channel;
                $slack_setting->channel_id = optional($codeToAccessToken->incoming_webhook)->channel_id;
                $slack_setting->save();

                return redirect("https://admin.shopify.com/store/$domain_name[0]/apps/ultimate-order-tracking-v16/notifications/$notification->id");
//                return response()->json([
//                    'status' => 'success',
//                    'message' => "Slack successfully connected!",
//                ]);
            } else {
                return redirect("https://admin.shopify.com/store/$domain_name[0]/apps/ultimate-order-tracking-v16/notifications/$notification->id");
                return response()->json([
                    'status' => 'error',
                    'message' => "Slack not connected!",
                    'response' => $codeToAccessToken
                ]);
            }
        } else {

            return redirect("https://admin.shopify.com/store/$domain_name[0]/apps/ultimate-order-tracking-v16/notifications/$notification->id");

        }

    }

    public function codeToAccessToken($code)
    {

        $setting = Setting::first();

        if (isset($setting)) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://slack.com/api/oauth.v2.access',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('code' => "$code", 'client_id' => $setting->slack_client_id, 'client_secret' => "$setting->slack_secret_id"),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response, false);

            return $response;
        }
    }

    public function sendMessageToSlack($session = null)
    {

        if ($session == null) {
            $session = Session::first();
        }

        $slack_setting = SlackSetting::where('session_id', $session->id)->whereNotNull('slack_webhook_url')->first();
        if (isset($slack_setting)) {

            $curl = curl_init();

            $message = [
                "text" => "Hi Buddy! New Paid Time Off request from <https://example.com|Fred Enriquez>\n\n<https://example.com|View request> :white_check_mark:"
            ];

            curl_setopt_array($curl, array(
                CURLOPT_URL => $slack_setting->slack_webhook_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($message),
                CURLOPT_HTTPHEADER => array(
                    'Content-type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response, false);

            return $response;

        }


    }

    public function discountSlackApp(Request $request)
    {
        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);

        $slack_setting = SlackSetting::where('session_id', $session->id)->whereNotNull('slack_webhook_url')->first();
        if (isset($slack_setting)) {

            $codeToAccessToken = $this->accessTokenToRegenerateToken($slack_setting);
            $slack_setting = SlackSetting::where('session_id', $session->id)->first();

            if (isset($slack_setting) && isset($codeToAccessToken) && isset($codeToAccessToken->ok) && $codeToAccessToken->ok == true) {

                $slack_setting->session_id = $session->id;
                $slack_setting->slack_access_token = $codeToAccessToken->access_token;
                $slack_setting->slack_refresh_token = $codeToAccessToken->refresh_token;
                $slack_setting->save();
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://slack.com/api/apps.uninstall?client_id=$setting->slack_client_id&client_secret=$setting->slack_secret_id",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'DELETE',
                    CURLOPT_HTTPHEADER => array(
                        "client_id: $setting->slack_client_id",
                        "client_secret: $setting->slack_secret_id",
                        "Authorization: Bearer $slack_setting->slack_access_token"
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                $response = json_decode($response, false);
                if(isset($response) && $response->ok == false){
                    $slack_setting->delete();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Slack already deleted!'
                    ]);
                }else{
                    $slack_setting->delete();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Slack has been deleted!'
                    ]);
                }

            }else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Slack settings error!'
                ]);
            }


        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Slack settings not exist!'
            ]);
        }


    }

    public function accessTokenToRegenerateToken($codeToAccessToken)
    {

        $setting = Setting::first();

        if (isset($setting)) {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://slack.com/api/oauth.v2.access',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => "client_id=$setting->slack_client_id&client_secret=$setting->slack_secret_id&grant_type=refresh_token&refresh_token=$codeToAccessToken->slack_refresh_token",
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $response = json_decode($response, false);

            return $response;
        }


    }
}
