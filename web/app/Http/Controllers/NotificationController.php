<?php

namespace App\Http\Controllers;

use App\Events\WebpushedEvent;
use App\Models\Email;
use App\Models\GeneralEmail;
use App\Models\GeneralSlackNotification;
use App\Models\GeneralSmsNotification;
use App\Models\GeneralWebNotification;
use App\Models\Notification;
use App\Models\Session;
use App\Models\Setting;
use App\Models\SlackNotification;
use App\Models\SlackSetting;
use App\Models\SmsNotification;
use App\Models\WebNotification;
use Illuminate\Http\Request;
use Pusher\Pusher;

class NotificationController extends HelperController
{
    public function notifications(Request $request)
    {
        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $this->createUpdateNotifications($session);
        $notifications = Notification::where('session_id', $session->id)->orderBy('priority', 'asc')->get();
        $setting = Setting::where('session_id', $session->id)->first();

        $data = [
            'notifications' => $notifications,
            'setting' => $setting,
        ];
        return response()->json($data);
    }

    public function notifications_save(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);

        $notification_ids = isset($request->notification_ids) && $request->notification_ids != "" ? explode(',', $request->notification_ids) : [];
        $notification_statuses = isset($request->notification_statuses) && $request->notification_statuses != "" ? explode(',', $request->notification_statuses) : [];
        if (!empty($notification_ids)) {
            foreach ($notification_ids as $key => $notification_id) {
                $notification = Notification::where('session_id', $session->id)->where('id', $notification_id)->first();
                if (isset($notification)) {
                    $notification->session_id = $session->id;
                    $notification->active_status = isset($notification_statuses[$key]) ? $notification_statuses[$key] : 0;
                    $notification->save();
                }
            }
        }
        $data = [
            'status' => 'success',
            'message' => 'Successfully saved!'
        ];
        return response()->json($data);

    }

    public function email_detail(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $email = Email::where('session_id', $session->id)->where('id', $id)->first();

        $data = [
            'email_data' => $email,
        ];
        return response()->json($data);

    }

    public function email_save(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $email = Email::where('session_id', $session->id)->where('id', $id)->first();

        if (isset($email)) {

            $email->session_id = $session->id;
            $email->data = (isset($request->data) && $request->data != "") ? json_encode($request->data) : null;
            $email->active_status = isset($request->active_status) ? $request->active_status : 0;
            if (isset($request->logo) && $request->logo != "" && is_file($request->logo)) {
                $image = $request->logo;
                $destinationPath = 'images/';
                $filename = now()->format('YmdHi') . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $filename);
                $email->logo = "images/" . $filename;
            } elseif (isset($request->old_logo) && $request->old_logo != "") {
                $email->logo = $request->old_logo;
            }elseif($request->logo == null || $request->logo == ""){
                $email->logo = null;
            }
            $email->save();

            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'This email not found!'
            ];
        }

        return response()->json($data);

    }

    public function email_setting_save(Request $request){
        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $setting = Setting::where('session_id', $session->id)->first();
        if ($setting == null) {
            $setting = new Setting();
        }
        $setting->session_id = $session->id;
        $setting->sender_name = $request->sender_name;
        $setting->sender_email = $request->sender_email;
        $setting->merchant_email = $request->merchant_email;
        $setting->save();
        $data = [
            'status' => 'success',
            'message' => 'Successfully saved!'
        ];
        return response()->json($data);
    }

    public function sms_setting_save(Request $request){
        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $setting = Setting::where('session_id', $session->id)->first();
        if ($setting == null) {
            $setting = new Setting();
        }
        $setting->session_id = $session->id;
        $setting->from_sms_number = $request->from_sms_number;
        $setting->save();
        $data = [
            'status' => 'success',
            'message' => 'Successfully saved!'
        ];
        return response()->json($data);
    }

    public function email_status_save(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $email = Email::where('session_id', $session->id)->where('id', $id)->first();
        if (isset($email)) {
            $email->session_id = $session->id;
            $email->active_status = isset($request->active_status) ? $request->active_status : 0;
            $email->save();
            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'This email not found!'
            ];
        }

        return response()->json($data);
    }

    public function notifications_detail(Request $request, $id)
    {
        //        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $data = [];
        $notification = Notification::find($id);
        $setting = Setting::where('session_id',$session->id)->first();
        $slack_setting = SlackSetting::where('session_id',$session->id)->first();
        if (isset($notification)) {
            if ($notification->notification_type === 'Email Notifications') {
                $general_emails = GeneralEmail::get();
                if (!empty($general_emails)) {
                    foreach ($general_emails as $general_email) {
                        $email = Email::where('session_id', $session->id)->where('email_type', $general_email->email_type)->first();
                        if ($email == null) {
                            $email = new Email();
                            $email->session_id = $session->id;
                            $email->notification_id = $id;
                            $email->email_type = $general_email->email_type;
                            $email->title = $general_email->title;
                            $email->description = $general_email->description;
                            $email->data = $general_email->data;
                            $email->priority = $general_email->priority;
                            $email->save();
                        }
                    }
                }

                $emails = Email::where('session_id', $session->id)->orderBy('priority', 'asc')->get();
                $data = [
                    'emails' => $emails,
                    'notification_type' => $notification->notification_type,
                    'setting' => $setting,
                ];
            } elseif ($notification->notification_type === 'Web-Based Push Notification') {

                $general_web_notis = GeneralWebNotification::get();
                if (!empty($general_web_notis)) {
                    foreach ($general_web_notis as $general_web_noti) {
                        $web_not = WebNotification::where('session_id', $session->id)
                            ->where('notification_type', $general_web_noti->notification_type)->first();
                        if ($web_not == null) {
                            $web_not = new WebNotification();
                            $web_not->session_id = $session->id;
                            $web_not->notification_id = $id;
                            $web_not->notification_type = $general_web_noti->notification_type;
                            $web_not->title = $general_web_noti->title;
                            $web_not->description = $general_web_noti->description;
                            $web_not->data = $general_web_noti->data;
                            $web_not->priority = $general_web_noti->priority;
                            $web_not->save();
                        }
                    }
                }

                $web_notifications = WebNotification::where('session_id', $session->id)->orderBy('priority', 'asc')->get();

                $data = [
                    'web_notifications' => $web_notifications,
                    'notification_type' => $notification->notification_type,
                    'setting' => $setting,
                ];
            } elseif ($notification->notification_type === 'SMS Notifications') {
                $general_sms_notis = GeneralSmsNotification::get();
                if (!empty($general_sms_notis)) {
                    foreach ($general_sms_notis as $general_sms_noti) {
                        $sms_not = SmsNotification::where('session_id', $session->id)
                            ->where('sms_type', $general_sms_noti->sms_type)->first();
                        if ($sms_not == null) {
                            $sms_not = new SmsNotification();
                            $sms_not->session_id = $session->id;
                            $sms_not->notification_id = $id;
                            $sms_not->sms_type = $general_sms_noti->sms_type;
                            $sms_not->title = $general_sms_noti->title;
                            $sms_not->description = $general_sms_noti->description;
                            $sms_not->data = $general_sms_noti->data;
                            $sms_not->priority = $general_sms_noti->priority;
                            $sms_not->save();
                        }
                    }
                }

                $sms_notifications = SmsNotification::where('session_id', $session->id)->orderBy('priority', 'asc')->get();

                $data = [
                    'sms_notifications' => $sms_notifications,
                    'notification_type' => $notification->notification_type,
                    'setting' => $setting,
                ];
            } elseif ($notification->notification_type === 'Slack Notification') {
                $general_slack_notis = GeneralSlackNotification::get();
                if (!empty($general_slack_notis)) {
                    foreach ($general_slack_notis as $general_slack_noti) {
                        $slack_not = SlackNotification::where('session_id', $session->id)
                            ->where('slack_type', $general_slack_noti->slack_type)->first();
                        if ($slack_not == null) {
                            $slack_not = new SlackNotification();
                            $slack_not->session_id = $session->id;
                            $slack_not->notification_id = $id;
                            $slack_not->slack_type = $general_slack_noti->slack_type;
                            $slack_not->title = $general_slack_noti->title;
                            $slack_not->description = $general_slack_noti->description;
                            $slack_not->data = $general_slack_noti->data;
                            $slack_not->priority = $general_slack_noti->priority;
                            $slack_not->save();
                        }
                    }
                }

                $slack_notifications = SlackNotification::where('session_id', $session->id)->orderBy('priority', 'asc')->get();

                $data = [
                    'slack_notifications' => $slack_notifications,
                    'notification_type' => $notification->notification_type,
                    'slack_setting' => $slack_setting,
                    'setting' => $setting,
                ];
            }
        }

        return response()->json($data);
    }

    public function createUpdateNotifications($session)
    {
        $notifications_array = ['Email Notifications', 'SMS Notifications', 'Web-Based Push Notification', 'Slack Notification'];
//        , 'Slack Notification'
        foreach ($notifications_array as $key => $notification_type) {
            $notification = Notification::where('session_id', $session->id)->where('notification_type', $notification_type)->first();
            if ($notification == null) {
                $notification = new Notification();
            }
            $notification->session_id = $session->id;
            $notification->notification_type = $notification_type;
            $notification->title = $notification_type;
            $notification->priority = $key + 1;
            $notification->save();
        }
    }

    public function web_notification_detail(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = WebNotification::where('session_id', $session->id)->where('id', $id)->first();

        $data = [
            'web_notification_data' => $web_notification,
        ];
        return response()->json($data);

    }

    public function web_notification_save(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = WebNotification::where('session_id', $session->id)->where('id', $id)->first();
        if (isset($web_notification)) {
            $web_notification->session_id = $session->id;
            $web_notification->data = isset($request->data) && $request->data != "" ? json_encode($request->data) : null;
            $web_notification->active_status = isset($request->active_status) ? $request->active_status : 0;
            if (isset($request->logo) && $request->logo != "" && is_file($request->logo)) {
                $image = $request->logo;
                $destinationPath = 'images/';
                $filename = now()->format('YmdHi') . "." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $filename);
                $web_notification->logo = "images/" . $filename;
            } elseif (isset($request->old_logo) && $request->old_logo != "") {
                $web_notification->logo = $request->old_logo;
            }elseif($request->logo == null || $request->logo == ""){
                $web_notification->logo = null;
            }
            $web_notification->save();
            $notification = Notification::find($web_notification->notification_id);
            if (isset($web_notification->active_status) && $web_notification->active_status == 1 &&
                isset($notification) && isset($notification->active_status) && $notification->active_status == 1) {
                $this->webBasedNotificationEventTrigger($web_notification, $notification);
            }

            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'This Web Based Notification not found!'
            ];
        }

        return response()->json($data);

    }

    public function web_notification_status_save(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = WebNotification::where('session_id', $session->id)->where('id', $id)->first();
        if (isset($web_notification)) {
            $web_notification->session_id = $session->id;
            $web_notification->active_status = isset($request->active_status) ? $request->active_status : 0;
            $web_notification->save();
            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'This Web Based Notification not found!'
            ];
        }

        return response()->json($data);

    }

    public function sms_notification_detail(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = SmsNotification::where('session_id', $session->id)->where('id', $id)->first();

        $data = [
            'sms_notification_data' => $web_notification,
        ];
        return response()->json($data);

    }

    public function sms_notification_save(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = SmsNotification::where('session_id', $session->id)->where('id', $id)->first();
        if (isset($web_notification)) {
            $web_notification->session_id = $session->id;
            $web_notification->data = isset($request->data) && $request->data != "" ? json_encode($request->data) : null;
            $web_notification->active_status = isset($request->active_status) ? $request->active_status : 0;

            $web_notification->save();
            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'This SMS Notification not found!'
            ];
        }

        return response()->json($data);

    }

    public function sms_notification_status_save(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = SmsNotification::where('session_id', $session->id)->where('id', $id)->first();
        if (isset($web_notification)) {
            $web_notification->session_id = $session->id;
            $web_notification->active_status = isset($request->active_status) ? $request->active_status : 0;
            $web_notification->save();
            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'This SMS Notification not found!'
            ];
        }

        return response()->json($data);

    }

    public function slack_notification_detail(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = SlackNotification::where('session_id', $session->id)->where('id', $id)->first();

        $data = [
            'slack_notification_data' => $web_notification,
        ];
        return response()->json($data);

    }

    public function slack_notification_save(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = SlackNotification::where('session_id', $session->id)->where('id', $id)->first();
        if (isset($web_notification)) {
            $web_notification->session_id = $session->id;
            $web_notification->data = isset($request->data) && $request->data != "" ? json_encode($request->data) : null;
            $web_notification->active_status = isset($request->active_status) ? $request->active_status : 0;

            $web_notification->save();
            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'This Slack Notification not found!'
            ];
        }

        return response()->json($data);

    }

    public function slack_notification_status_save(Request $request, $id)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $web_notification = SlackNotification::where('session_id', $session->id)->where('id', $id)->first();
        if (isset($web_notification)) {
            $web_notification->session_id = $session->id;
            $web_notification->active_status = isset($request->active_status) ? $request->active_status : 0;
            $web_notification->save();
            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } else {
            $data = [
                'status' => 'error',
                'message' => 'This Slack Notification not found!'
            ];
        }

        return response()->json($data);

    }

    public function webBasedNotificationEventTrigger($responseData)
    {
        $session = Session::find($responseData->session_id);
        if (isset($session)) {
            $message = 'Hello, this is a real-time notification!';
            $data = [
                'message' => $message,
                'notification_type' => $responseData->notification_type,
                'web_based_notification_data' => $responseData,
                'shop_name' => $session->shop,
            ];
            $options = array(
                'cluster' => 'ap2',
                'useTLS' => true
            );
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                $options
            );
            $pusher->trigger("my-channel-$session->shop", "WebpushedEvent", $data);

            return response()->json('Notification sent!');
        }


    }
}
