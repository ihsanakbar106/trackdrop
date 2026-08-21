<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\FulfillmentController;
use App\Http\Controllers\KlaviyoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SlackController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\SettingController;
use App\Jobs\fulfillmentCreateUpdateJob;
use App\Jobs\unistallAppJob;
use App\Models\Carrier;
use App\Models\ErrorMessage;
use App\Models\Fulfillment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Session;
use App\Models\Status;
use DougSisk\CountryState\CountryState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Shopify\Clients\Graphql;
use Shopify\Clients\Rest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return "Hello API";
});

Route::get('/test', function () {
//    $carrier = Carrier::select('name')->pluck('name')->toArray();
//    dd($carrier);
//    $cnt = new FulfillmentController();
//    $cnt->find_carrier('XLT999302309');
//    dd(123);
//    $sync_controller = new \App\Http\Controllers\FulfillmentController();
//    $sync_controller->carrier_register('XLT999302309','');
//    dd('tracking sync');
    $sync_controller = new SyncController();
    $sync_controller->sync_carriers();
//    $sync_controller->sync_order_fulfillments_tracking();
    dd('sync');
});

Route::any('/save-recommended-click', [FulfillmentController::class, 'save_recommended_click']);
Route::any('/check-charge', [PlanController::class, 'CheckCharge']);

Route::middleware(['shopify.auth'])->group(function () {


    Route::any('/shop-data', [PlanController::class, 'shop_data'])->name('shop-data');
    Route::any('/checkPlan', [SettingController::class, 'checkPlan'])->name('checkPlan');
//});

    Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics', [AnalyticsController::class, 'analytics'])->name('analytics');
    Route::get('/analytics/analytics_order_to_delivery', [AnalyticsController::class, 'analytics_order_to_delivery'])->name('analytics_order_to_delivery');
    Route::get('/analytics/transit_time', [AnalyticsController::class, 'analytics_transit_time'])->name('analytics_transit_time');
    Route::get('/analytics/tracking_page', [AnalyticsController::class, 'analytics_tracking_page'])->name('analytics_tracking_page');
    Route::get('/analytics/exception_page', [AnalyticsController::class, 'analytics_exception_page'])->name('analytics_exception_page');

    Route::get('/analytics/detail/{id}', [AnalyticsController::class, 'analytics_detail'])->name('analytics-detail');

    Route::get('/settings', [SettingController::class, 'settings'])->name('settings');
    Route::post('/save-settings', [SettingController::class, 'settings_save'])->name('settings.save');
    Route::post('/save-translation', [SettingController::class, 'translation_save'])->name('translation.save');

    Route::any('/all_plans', [PlanController::class, 'all_plans'])->name('all_plans');
    Route::any('/active_plan', [PlanController::class, 'active_plan'])->name('plan_active');
    Route::get('/check-onetime-charge', [PlanController::class, 'CheckCharge']);
    Route::get('/orders', [OrderController::class, 'orders'])->name('orders');
    Route::get('/order_detail/{id}', [OrderController::class, 'order_detail'])->name('order-detail');

    Route::get('/tracking-pages', [TrackingController::class, 'tracking_pages'])->name('tracking-pages');
    Route::any('/create-tracking-page', [TrackingController::class, 'create_tracking_page'])->name('create-tracking-page');
    Route::any('/tracking-page-detail/{id}', [TrackingController::class, 'tracking_page_detail'])->name('tracking_page_detail');
    Route::any('/tracking-page-detail-save/{id}', [TrackingController::class, 'tracking_page_detail_save'])->name('tracking_page_detail_save');
    Route::any('/publish-tracking-page/{id}', [TrackingController::class, 'publish_tracking_page'])->name('publish_tracking_page');
    Route::any('/duplicate-tracking-page/{id}', [TrackingController::class, 'duplicate_tracking_page'])->name('duplicate-tracking-page');
    Route::any('/delete-tracking-page/{id}', [TrackingController::class, 'delete_tracking_page'])->name('delete-tracking-page');

    Route::any('/notifications', [NotificationController::class, 'notifications'])->name('notifications');
    Route::any('/notification-save', [NotificationController::class, 'notifications_save'])->name('notifications_save');
    Route::any('/notification-detail/{id}', [NotificationController::class, 'notifications_detail'])->name('notifications_detail');

// email notification
    Route::any('/email-detail/{id}', [NotificationController::class, 'email_detail'])->name('email_detail');
    Route::any('/email-save/{id}', [NotificationController::class, 'email_save'])->name('email_save');
    Route::any('/email-status-save/{id}', [NotificationController::class, 'email_status_save'])->name('email_status_save');
    Route::any('/email-setting-save', [NotificationController::class, 'email_setting_save'])->name('email_setting_save');
    Route::any('/sms-setting-save', [NotificationController::class, 'sms_setting_save'])->name('sms_setting_save');

// web pushed notification
    Route::any('/web-notification-detail/{id}', [NotificationController::class, 'web_notification_detail'])->name('web_notification_detail');
    Route::any('/web-notification-save/{id}', [NotificationController::class, 'web_notification_save'])->name('web_notification_save');
    Route::any('/web-notification-status-save/{id}', [NotificationController::class, 'web_notification_status_save'])->name('web_notification_status_save');

// sms notifications
    Route::any('/sms-notification-detail/{id}', [NotificationController::class, 'sms_notification_detail'])->name('sms_notification_detail');
    Route::any('/sms-notification-save/{id}', [NotificationController::class, 'sms_notification_save'])->name('sms_notification_save');
    Route::any('/sms-notification-status-save/{id}', [NotificationController::class, 'sms_notification_status_save'])->name('sms_notification_status_save');

// slack notifications
    Route::any('/slack-notification-detail/{id}', [NotificationController::class, 'slack_notification_detail'])->name('slack_notification_detail');
    Route::any('/slack-notification-save/{id}', [NotificationController::class, 'slack_notification_save'])->name('slack_notification_save');
    Route::any('/slack-notification-status-save/{id}', [NotificationController::class, 'slack_notification_status_save'])->name('slack_notification_status_save');

    Route::any('/connect-slack', [SlackController::class,'connect_slack'])->name('connect-slack');
    Route::any('/slack-redirect', [SlackController::class,'slack_redirect'])->name('slack-redirect');
    Route::any('/send-slack-message', [SlackController::class,'sendMessageToSlack'])->name('send-slack-message');
    Route::any('/disconnect-slack-app', [SlackController::class,'discountSlackApp'])->name('disconnect-slack-app');

    Route::get('/fulfillments', [FulfillmentController::class, 'fulfillments'])->name('FulfillmentController');
    Route::get('/fulfillment_detail/{id}', [FulfillmentController::class, 'fulfillment_detail'])->name('fulfillment_detail');

    Route::get('/fulfill_items/{id}', [FulfillmentController::class, 'fulfill_items'])->name('fulfill-items');
    Route::post('/lineitem_fulfilled', [FulfillmentController::class, 'line_item_fulfilled'])->name('line-item-fulfilled');

    Route::post('/tracking_add', [FulfillmentController::class, 'tracking_add'])->name('tracking-add');

    Route::any('/sync-products', [ProductController::class, 'sync_products'])->name('sync.products');
    Route::any('/sync-store-products', [ProductController::class, 'sync_store_products'])->name('sync.store.products');
    Route::any('/product-list', [ProductController::class, 'product_list'])->name('product.list');

    Route::any('/get-all-collections', [ProductController::class, 'all_collections'])->name('all.collections');
    Route::any('/sync-collections/{id}', [CollectionController::class, 'sync_collections'])->name('sync.collection');

    Route::get('/sync_orders', [SyncController::class, 'sync_order_on_btn_click'])->name('sync-orders');
    Route::get(
        '/sync-fresh-orders-and-trackings',
        [SyncController::class, 'sync_fresh_orders_tracking']
    )->name('sync_fresh_orders_tracking');
    Route::get('/sync-order-trackings', [SyncController::class, 'sync_order_trackings'])->name('sync_order_trackings');

    Route::get('/sync-store-orders-tracking', [SyncController::class, 'sync_store_order_fulfillments_tracking'])
    ->name('sync_store_order_fulfillments_tracking');
    Route::get('/manual-sync-store-orders-tracking', [SyncController::class, 'ManualUpdateStoreOrderTrackings'])
    ->name('ManualUpdateStoreOrderTrackings');



    Route::any('general-settings', [KlaviyoController::class, 'general_settings'])->name('general-settings');
    Route::any('klaviyo-api-save', [KlaviyoController::class, 'klaviyo_api_save'])->name('klaviyo-api-save');

    Route::get('/createEvent', [KlaviyoController::class, 'createEvent']);

    Route::any('/klaviyoAppRedirect', function (Request $request) {
        return response()->json($request->all());
    });
    Route::any('/klaviyo/oauth/authorize', function (Request $request) {
        return response()->json($request->all());
    });

    Route::get('update-carrier/{shopify_order_id}', function ($shopify_order_id) {
        $cnt=new SyncController();
        $cnt->updateCarrier($shopify_order_id);

        return response()->json(['status' => 'error']);
    });
});

Route::any('search-tracking-number', [FulfillmentController::class, 'search_tracking_number'])->name('search-tracking-number');
Route::any('fetch_tracking_page', [FulfillmentController::class, 'fetch_tracking_page'])->name('fetch_tracking_page');
//Route::any('search-tracking-number', function (Request $request){
//    return response()->json(['status' => 'success']);
//    $html = '<p>track number </p>';
//    return response($html)->withHeaders(['Content-Type' => 'application/liquid']);
//})->name('search-tracking-number');


Route::any('test-flow', function (Request $request) {
    $session = Session::first();
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
            "Shipment Status" => "Delivered",
            "customer_id" => 6802469617880,
        ]
    ];

    $client = new Graphql($session->shop, (new \App\Services\ShopifyTokenService())->getValidAccessToken($session->shop));
    $shopify_flow = $client->query(["query" => $query, "variables" => $variables]);
    $shopify_flow = $shopify_flow->getDecodedBody();
    dd($shopify_flow);
});


//webhooks
Route::post('/webhooks/app-uninstall', function (Request $request) {
    try {
        $shop_name = $request->header('x-shopify-shop-domain');
//        $logs = new \App\Models\ErrorMessage();
//        $logs->message = '$shop_name' . json_encode($shop_name);
//        $logs->save();
        $session = Session::where('shop', $shop_name)->first();

        dispatch(new unistallAppJob($session->id))->onConnection('database');

        \Illuminate\Support\Facades\DB::table('sessions')->where('shop', $shop_name)->delete();
    } catch (\Exception $e) {
        $error_log = new \App\Models\ErrorMessage();
        $error_log->message = 'Uninstall catch' . json_encode($e->getMessage());
        $error_log->save();
    }
});

Route::post('/webhooks/order-create', function (Request $request) {
    try {
//        $logs = new \App\Models\ErrorMessage();
//        $logs->message = 'order create res: ' . json_encode($request->getContent());
//        $logs->save();
//        $order = json_decode($request->getContent());
        $order = $request->getContent();

        $shop = $request->header('x-shopify-shop-domain');
        $shop = Session::where('shop', $shop)->first();
        $webhook_controller = new \App\Http\Controllers\SyncController();
        $order = json_decode($order);
        $webhook_controller->createUpdateOrder($order, $shop);
    } catch (\Exception $e) {
        $error_log = new \App\Models\ErrorMessage();
        $error_log->message = 'Order Create webhook error: ' . json_encode($e->getMessage());
        $error_log->save();
    }
});

Route::post('/webhooks/order-update', function (Request $request) {
    try {
//        $logs = new \App\Models\ErrorMessage();
//        $logs->message = 'order update res: ' . json_encode($request->getContent());
//        $logs->save();
//        $order = json_decode($request->getContent());
        $order = $request->getContent();
        $order = json_decode($order);

        $shop = $request->header('x-shopify-shop-domain');
        $shop = Session::where('shop', $shop)->first();
        $webhook_controller = new \App\Http\Controllers\SyncController();
        $webhook_controller->createUpdateOrder($order, $shop);
    } catch (\Exception $e) {
        $error_log = new \App\Models\ErrorMessage();
        $error_log->message = 'Order update webhook error: ' . json_encode($e->getMessage());
        $error_log->save();
    }
});

Route::post('/webhooks/product-create', function (Request $request) {
    try {
//        $logs = new \App\Models\ErrorMessage();
//        $logs->message = 'product create res: '.json_encode($request->getContent());
//        $logs->save();
        $product_controler = new ProductController();
        $shop = $request->header('x-shopify-shop-domain');
        $session = Session::where('shop', $shop)->first();
        $data = $request->getContent();
        $data = json_decode($data);
        \App\Jobs\productCreateUpdateJob::dispatch($session,$data->id);
    } catch (\Exception $e) {
        $error_log = new \App\Models\ErrorMessage();
        $error_log->message = 'product create webhook error: ' . json_encode($e->getMessage());
        $error_log->save();
    }
});

Route::post('/webhooks/product-update', function (Request $request) {
    try {
       /* $logs = new \App\Models\ErrorMessage();
        $logs->message = 'product update res: ' . json_encode($request->getContent());
        $logs->save();*/

        $product_controler = new ProductController();
        $shop = $request->header('x-shopify-shop-domain');
        $session = Session::where('shop', $shop)->first();
        $data = $request->getContent();
        $data = json_decode($data);

        \App\Jobs\productCreateUpdateJob::dispatch($session,$data->id);
    } catch (\Exception $e) {
        $error_log = new \App\Models\ErrorMessage();
        $error_log->message = 'product update webhook error: ' . json_encode($e->getMessage());
        $error_log->save();
    }
});

Route::post('/webhooks/product-delete', function (Request $request) {
    try {
       /* $logs = new \App\Models\ErrorMessage();
        $logs->message = 'product delete res: ' . json_encode($request->getContent());
        $logs->save();*/

        $shop_name = $request->header('x-shopify-shop-domain');
        $shop = Session::where('shop', $shop_name)->first();

        $response = $request->getContent();
        $response = json_decode($response);
        $product = Product::where('shopify_product_id', $response->id)->first();

        if (isset($product)) {
            if ($product->variants->count()) {
                foreach ($product->variants as $variant) {
                    $variant->forceDelete();
                }
            }
        }

        $product->forceDelete();
    } catch (\Exception $e) {
        $error_log = new \App\Models\ErrorMessage();
        $error_log->message = 'product delete webhook error: ' . json_encode($e->getMessage());
        $error_log->save();
    }
});

Route::post('/webhooks/fulfillment-create', function (Request $request) {
    try {
        $fulfillment_api = json_decode($request->getContent());

        $shop = $request->header('x-shopify-shop-domain');
        $shop = Session::where('shop', $shop)->first();

//        $msg = new ErrorMessage();
//        $msg->message = "webhook fulfillment create api data: ".json_encode($fulfillment_api);
//        $msg->save();

        fulfillmentCreateUpdateJob::dispatch($fulfillment_api, $shop)->onConnection("database");

//        return true;
    } catch (Exception $exception) {
//        $msg = new ErrorMessage();
//        $msg->message = "Fulfillment create Webhook Exception: " . json_encode($exception->getMessage());
//        $msg->save();
        return true;
    }
});

Route::post('/webhooks/fulfillment-update', function (Request $request) {
    try {
        $fulfillment_api = json_decode($request->getContent());

        $shop = $request->header('x-shopify-shop-domain');
        $shop = Session::where('shop', $shop)->first();

//        $msg = new ErrorMessage();
//        $msg->message = "webhook fulfillment update api data: " . json_encode($fulfillment_api);
//        $msg->save();
        fulfillmentCreateUpdateJob::dispatch($fulfillment_api, $shop)->onConnection("database");
    } catch (Exception $exception) {
        $msg = new ErrorMessage();
        $msg->message = "Fulfillment create Webhook Exception: " . $exception->getMessage();
        $msg->save();
        return true;
    }
});

Route::post('/webhooks/collection-create', function (Request $request) {
    try {
        /*$logs = new \App\Models\ErrorMessage();
        $logs->message = 'collection create res: ' . json_encode($request->getContent());
        $logs->save();*/

        $shop_name = $request->header('x-shopify-shop-domain');
        $session = Session::where('shop', $shop_name)->first();

        $response = $request->getContent();
        $response = json_decode($response);
        \App\Jobs\collectionCreateUpdateJob::dispatch($session,$response->id);

    } catch (\Exception $e) {
        $error_log = new \App\Models\ErrorMessage();
        $error_log->message = 'collection create webhook error: ' . json_encode($e->getMessage());
        $error_log->save();
    }
});

Route::post('/webhooks/collection-update', function (Request $request) {
    try {
       /* $logs = new \App\Models\ErrorMessage();
        $logs->message = 'collection update res: ' . json_encode($request->getContent());
        $logs->save();*/

        $shop_name = $request->header('x-shopify-shop-domain');
        $session = Session::where('shop', $shop_name)->first();

        $response = $request->getContent();
        $response = json_decode($response);
        \App\Jobs\collectionCreateUpdateJob::dispatch($session,$response->id);

    } catch (\Exception $e) {
        $error_log = new \App\Models\ErrorMessage();
        $error_log->message = 'collection update webhook error: ' . json_encode($e->getMessage());
        $error_log->save();
    }
});


Route::get('delete/webhooks', function () {
    $shops = Session::get();
    if ($shops->count()) {
        foreach ($shops as $shop) {
            $helper = new \App\Http\Controllers\HelperController();
            $response = $helper->getShopApi($shop->shop)->rest('GET', '/admin/webhooks.json', null, [], true);

            $webhook_ids = [];
            foreach ($response['body']['webhooks'] as $webhook) {
                array_push($webhook_ids, $webhook->id);
            }

            foreach ($webhook_ids as $id) {
                $response2 = $helper->getShopApi($shop->shop)->rest('DELETE', '/admin/webhooks/' . $id . '.json');
                dump($response2);
            }
        }
    }
    dd('all webhooks delete');
});

Route::any('/get/webhooks', function (Request $request) {

    $session = Session::where('shop', $request->shop)->first();
    if (isset($session)) {
        $client = new Rest($session->shop, (new \App\Services\ShopifyTokenService())->getValidAccessToken($session->shop));
        $webhooks = $client->get('webhooks.json');
        $webhooks = $webhooks->getDecodedBody();
        return response()->json($webhooks);
    } else {
        return response()->json([
            'status' => 'Error',
            'message' => ''
        ]);
    }
});

Route::get('/create/webhook', function () {
    $shops = Session::get();
    if ($shops->count()) {
        $helper = new \App\Http\Controllers\HelperController();
        foreach ($shops as $shop) {
            $data = [
                "webhook" => [
                    "topic" => "orders/create",
                    "address" => env("APP_URL") . "/api/webhooks/order-create",
                    "format" => "json",
                ]
            ];
            $response1 = $helper->getShopApi($shop->shop)->rest('POST', '/admin/webhooks.json', $data, [], true);

            $data = [
                "webhook" => [
                    "topic" => "orders/updated",
                    "address" => env("APP_URL") . "/api/webhooks/order-update",
                    "format" => "json",
                ]
            ];
            $response2 = $helper->getShopApi($shop->shop)->rest('POST', '/admin/webhooks.json', $data, [], true);

            $data = [
                "webhook" => [
                    "topic" => "products/delete",
                    "address" => env("APP_URL") . "/api/webhooks/product-delete",
                    "format" => "json",
                ]
            ];
            $response3 = $helper->getShopApi($shop->shop)->rest('POST', '/admin/webhooks.json', $data, [], true);

            $data = [
                "webhook" => [
                    "topic" => "products/create",
                    "address" => env("APP_URL") . "/api/webhooks/product-create",
                    "format" => "json",
                ]
            ];
            $response4 = $helper->getShopApi($shop->shop)->rest('POST', '/admin/webhooks.json', $data, [], true);
            $data = [
                "webhook" => [
                    "topic" => "products/update",
                    "address" => env("APP_URL") . "/api/webhooks/product-update",
                    "format" => "json",
                ]
            ];
            $response4 = $helper->getShopApi($shop->shop)->rest('POST', '/admin/webhooks.json', $data, [], true);
        }
    }


    dd('done');
});

// GDPR request routes
Route::any('/webhooks/customers-data-request', [WebhookController::class, 'verifyWebhook']);
Route::any('/webhooks/customers-redact', [WebhookController::class, 'verifyWebhook']);
Route::any('/webhooks/shop-redact', [WebhookController::class, 'verifyWebhook']);


Route::any('test-email', function (Request $request) {
    try {
//       Mail::to("haseebtanveerbutt1999@gmail.com")->send(new \App\Mail\TestMail());
        Mail::to("tanveerhaseeb1999@gmail.com")->send(new \App\Mail\TestMail());

        dd('done');
    } catch (Exception $e) {
        dd('error', $e->getMessage());
    }
});



Route::get('/tracking', function (Request $request) {
    $html = '<p>track - </p>' . $request->v;
    return response($html)->withHeaders(['Content-Type' => 'application/liquid']);
});
