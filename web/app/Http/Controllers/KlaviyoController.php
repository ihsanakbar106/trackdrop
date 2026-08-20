<?php

namespace App\Http\Controllers;

use App\Models\ErrorMessage;
use App\Models\Product;
use App\Models\Session;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KlaviyoController extends HelperController
{
    public function general_settings(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $setting = Setting::where('session_id', $session->id)->first();

        $data = [
            'setting' => $setting,
        ];
        return response()->json($data);
    }

    public function klaviyo_api_save(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $setting = Setting::where('session_id',$session->id)->first();

        $res = $this->getAndSearchProfile($request->klaviyo_api_keys, null);

        if (isset($res->errors) && isset($res->errors[0]) && isset($res->errors[0]->detail)) {
            return response()->json([
                'status' => 'error',
                'message' => $res->errors[0]->detail
            ]);
        }

        $setting->klaviyo_api_keys = isset($request->klaviyo_api_keys) && $request->klaviyo_api_keys != '' ? $request->klaviyo_api_keys : null;
        $setting->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Saved!'
        ]);
    }



    public function createKlaviyoEvent($fulfillment,$order,$shipment_status,$session){
        $setting = Setting::where('session_id',$session->id)->whereNotNull('klaviyo_api_keys')->first();
        $customer = isset($order->customer) && $order->customer != "" ? json_decode($order->customer):null;
        $shipping_address = isset($order->shipping_address) && $order->shipping_address != "" ? json_decode($order->shipping_address):null;
        $tracking_complete_info = isset($fulfillment->tracking_complete_info) && $fulfillment->tracking_complete_info != "" ? json_decode($fulfillment->tracking_complete_info):null;

        if(isset($customer) && isset($fulfillment) && isset($setting) && isset($shipment_status) && isset($tracking_complete_info) && $shipment_status != ""){
            $transitTime = null;
            if(isset($tracking_complete_info->first_date) && isset($tracking_complete_info->last_date)){
                $firstDate = Carbon::parse($tracking_complete_info->first_date);
                $lastDate = Carbon::parse($tracking_complete_info->last_date);
                $transitTime = $lastDate->diffForHumans($firstDate, ['syntax' => Carbon::DIFF_ABSOLUTE]);
            }

            $lineitems = [];
            $lineitem_decode = isset($fulfillment->line_items)?json_decode($fulfillment->line_items):[];
            if(!empty($lineitem_decode)){
                foreach ($lineitem_decode as $fulfillment_item){
                    $product = Product::where('shopify_product_id',$fulfillment_item->product_id)->first();
                    array_push($lineitems,[
                        "product_name" => $fulfillment_item->title,
                        "quantity" => $fulfillment_item->quantity,
                        "variant_image" => isset($product) && isset($product->image)?$product->image:null,
                        "price" => $fulfillment_item->price,
                        "variant_name" => isset($fulfillment_item) && isset($fulfillment_item->variant_title)?$fulfillment_item->variant_title:null,
                        "sku" => $fulfillment_item->sku,
                        "handle" => isset($product) && isset($product->handle)?$product->handle:null
                    ]);
                }
            }

            $data = [
                "data" => [
                    "type" => "event",
                    "attributes" => [
                        "properties" => [
                            "shipment_status" => "$shipment_status",
//                            "tracking_link" => "good hain4",
//                            "expected_delivery_date" => "good hain4",
                            "tracking_number" => isset($fulfillment->tracking_number)?$fulfillment->tracking_number:null,
                            "carrier_name" => isset($fulfillment->tracking_company)?strtoupper($fulfillment->tracking_company):null,
                            "carrier_contact" => null,
                            "last_check_point" => isset($tracking_complete_info->lastEvent)?$tracking_complete_info->lastEvent:null,
                            "last_checkpoint_time" => isset($tracking_complete_info->lastUpdateTime)?$tracking_complete_info->lastUpdateTime:null,
                            "transit_time" => isset($transitTime)?$transitTime:null,
//                            "residence_time" => "good hain4",
                            "order_number" => isset($order->name)?$order->name:null,
                            "order_created_at" => isset($order->created_at)?$order->created_at:null,
                            "customer_email" => isset($order->email)?$order->email:null,
                            "customer_phone" => isset($customer) && isset($customer->default_address) && isset($customer->default_address->phone)?$customer->default_address->phone:null,
                            "first_name" => isset($customer) && isset($customer->default_address) && isset($customer->default_address->first_name)?$customer->default_address->first_name:null,
                            "last_name" => isset($customer) && isset($customer->default_address) && isset($customer->default_address->last_name)?$customer->default_address->last_name:null,
                            "fulfillment_created_at" => $fulfillment->created_at,
                            "shipping_country" => isset($shipping_address) && $shipping_address->country ? $shipping_address->country:null,
                            "shipping_province" => isset($shipping_address) && $shipping_address->province ? $shipping_address->province:null,
                            "shipping_city" => isset($shipping_address) && $shipping_address->city ? $shipping_address->city:null,
                            "shipping_address1" => isset($shipping_address) && $shipping_address->address1 ? $shipping_address->address1:null,
                            "shipping_address2" => isset($shipping_address) && $shipping_address->address2 ? $shipping_address->address2:null,
                            "shipping_zip" => isset($shipping_address) && $shipping_address->zip ? $shipping_address->zip:null,
                            "lineitems" => $lineitems
                        ],
                        "metric" => [
                            "data" => [
                                "type" => "metric",
                                "attributes" => [
                                    "name" => "Order Tracking Event"
                                ]
                            ]
                        ],
                        "profile" => [
                            "data" => [
                                "type" => "profile",
                                "attributes" => [
                                    "email" => isset($order) && isset($order->email)?$order->email:null,
                                    "phone_number" => isset($order) && isset($order->phone) ?$order->phone:null,
                                    "first_name" => isset($shipping_address) && isset($shipping_address->first_name)?$shipping_address->first_name:null,
                                    "last_name" => isset($shipping_address) && isset($shipping_address->last_name)?$shipping_address->last_name:null,
                                    "organization" => isset($shipping_address) && isset($shipping_address->company)?$shipping_address->company:null,
                                ],
                            ]
                        ],
                        "time" => null,
                        "value" => null,
                        "unique_id" => time()
                    ]
                ]
            ];
            $data_save = new ErrorMessage();
            $data_save->message = "Klaviyo data save: ". json_encode($data);
            $data_save->save();
//            $data = [
//                "data" => [
//                    "type" => "event",
//                    "attributes" => [
//                        "properties" => [
//                            "shipment_status" => "Pending",
//                            "tracking_link" => "good hain4",
//                            "expected_delivery_date" => "good hain4",
//                            "tracking_number" => "good hain4",
//                            "carrier_name" => "good hain4",
//                            "carrier_contact" => "good hain4",
//                            "last_check_point" => "good hain4",
//                            "last_checkpoint_time" => "good hain4",
//                            "transit_time" => "good hain4",
//                            "residence_time" => "good hain4",
//                            "order_number" => "good hain4",
//                            "order_created_at" => "good hain4",
//                            "product_name" => "good hain4",
//                            "customer_email" => "good hain4",
//                            "customer_phone" => "good hain4",
//                            "first_name" => "good hain4",
//                            "last_name" => "good hain4",
//                            "fulfillment_created_at" => "good hain4",
//                            "shipping_country" => "good hain4",
//                            "shipping_province" => "good hain4",
//                            "shipping_city" => "good hain4",
//                            "shipping_address1" => "good hain4",
//                            "shipping_address2" => "good hain4",
//                            "shipping_zip" => "good hain4",
//                            "lineitems" => [
//                                [
//                                    "product_name" => "abc",
//                                    "quantity" => "1",
//                                    "variant_image" => "abc",
//                                    "price" => "abc",
//                                    "variant_name" => "abc",
//                                    "sku" => "abc",
//                                    "handle" => "abc"
//                                ]
//                            ]
//                        ],
//                        "metric" => [
//                            "data" => [
//                                "type" => "metric",
//                                "attributes" => [
//                                    "name" => "Order Tracking Event"
//                                ]
//                            ]
//                        ],
//                        "profile" => [
//                            "data" => [
//                                "type" => "profile",
//                                "attributes" => [
//                                    "email" => "haseebtanveerbutt1999@gmail.com",
//                                    "phone_number" => "+12345678901",
//                                    "first_name" => "Haseeb Tanveer",
//                                    "last_name" => "Butt",
//                                    "organization" => "Tetralogicx",
//                                    "title" => "title in profile",
//                                    "properties" => [
//                                        "profileProperty1" => "good hain",
//                                        "profileProperty2" => "good hain 2"
//                                    ]
//                                ],
//                            ]
//                        ],
//                        "time" => null,
//                        "value" => null,
//                        "unique_id" => "435345"
//                    ]
//                ]
//            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://a.klaviyo.com/api/events/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'revision: 2023-08-15',
                    'Content-Type: application/json',
                    'Accept: application/json',
                    "Authorization: Klaviyo-API-Key $setting->klaviyo_api_keys"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $response = json_decode($response, false);

            return $response;
        }


    }

    public function getAndSearchProfile($privateKey = null, $search = null)
    {

        $filter = null;

        if (isset($search)) {
            $filter = 'equals(email,"' . $search . '")';

        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://a.klaviyo.com/api/profiles/?filter=$filter",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: Klaviyo-API-Key $privateKey",
                'accept: application/json',
                'revision: 2024-07-15'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response, false);

        return $response;
    }

}
