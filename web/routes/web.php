<?php

use App\Exceptions\ShopifyProductCreatorException;
use App\Http\Controllers\SyncController;
use App\Jobs\afterAppInstallationJob;
use App\Lib\AuthRedirection;
use App\Lib\EnsureBilling;
use App\Lib\ExpiringOfflineOAuth;
use App\Lib\ProductCreator;
use App\Models\Fulfillment;
use App\Models\Plan;
use App\Models\Session;
use App\Models\TrackingPage;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Shopify\Auth\Session as AuthSession;
use Shopify\Clients\HttpHeaders;
use Shopify\Clients\Rest;
use Shopify\Context;
use Shopify\Exception\InvalidWebhookException;
use Shopify\Utils;
use Shopify\Webhooks\Registry;
use Shopify\Webhooks\Topics;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
| If you are adding routes outside of the /api path, remember to also add a
| proxy rule for them in web/frontend/vite.config.js
|
*/

Route::get('/test2', function (Request $request) {
    $sync_controller = new \App\Http\Controllers\FulfillmentController();
    $shop=Session::find(33);
    $fulfillment_id="";
    $tracking_number="1ST06003333726";
    $carrier="";
    $shipment=$sync_controller->shipping_status_test($fulfillment_id, $tracking_number, $carrier,$shop);
    dd($shipment);

});
Route::get('/test', function (Request $request) {

    $helper = new \App\Http\Controllers\HelperController();
    $session=Session::first();
//    $sync_controller = new \App\Http\Controllers\ProductController();
//    $sync_controller->sync_products($session->shop ,null);


    $session=Session::where('shop',$session->shop)->first();
    $query='query AccessScopeList {
  currentAppInstallation {
    accessScopes {
      handle
    }
  }
}';
    $scopes_response = $helper->getShopApi($session->shop)->graph($query);
    if ($scopes_response['errors'] == false) {
        $apiScopes = json_decode(json_encode($scopes_response['body']['data']['currentAppInstallation']['accessScopes']), false);
        $apiScopes = collect($apiScopes)->pluck('handle')->toArray();
        $apiScopes=implode(',',$apiScopes);
        $session->scope=$apiScopes;
        $session->save();
        dd($apiScopes);

    }
    dd($scopes_response);
    $sessions = Session::all();
    foreach ($sessions as $session){
        try {
            $records = [
                [
                    'shop_id' => $session->id,
                    'language' => 'en',
                    'is_default' => ($session->id==13)?0:1,
                    'track_your_order' => 'TRACK YOUR ORDER',
                    'order_number' => 'Order number',
                    'tracking_number' => 'Tracking number',
                    'email_phone_number' => 'Email or Phone number',
                    'track_btn' => 'Track',
                    'order_number_placeholder' => 'Enter your order number',
                    'order_number_error' => 'Please enter order number',
                    'tracking_number_placeholder' => 'Enter your tracking number',
                    'tracking_number_error' => 'Please enter tracking number',
                    'email_phone_number_placeholder' => 'Enter your email or phone number',
                    'email_phone_number_error' => 'Please enter your email or phone number',
                    'order_status_text' => 'Your order is',
                    'carrier_title' => 'Carrier',
                    'product_title' => 'Product (s)',
                    'page_not_publish' => 'Tracking page not published!',
                    'package_content' => 'Package Contents',
                    'pb_ordered' => 'Ordered',
                    'pb_in_transit' => 'In Transit',
                    'pb_out_for_delivery' => 'Out for Delivery',
                    'pb_delivered' => 'Delivered',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'shop_id' => $session->id,
                    'language' => 'he',
                    'is_default' => ($session->id==13)?1:0,
                    'track_your_order' => 'מעקב משלוחים',
                    'order_number' => 'מספר הזמנה',
                    'tracking_number' => 'מספר מעקב',
                    'email_phone_number' => 'מייל',
                    'track_btn' => 'מעקב',
                    'order_number_placeholder' => 'הוסף מספר הזמנה',
                    'order_number_error' => 'בבקשה הוסף מספר הזמנה',
                    'tracking_number_placeholder' => 'הוסף מספר מעקב',
                    'tracking_number_error' => 'בבקשה הוסף מספר מעקב תקין',
                    'email_phone_number_placeholder' => 'הוסף מייל',
                    'email_phone_number_error' => 'בבקשה הוסף מייל',
                    'order_status_text' => 'ההזמנה שלך:',
                    'carrier_title' => 'מוֹבִיל',
                    'product_title' => 'מוצר(ים)',
                    'page_not_publish' => 'דף המעקב לא פורסם!',
                    'package_content' => 'תכולת החבילה',
                    'pb_ordered' => 'שהוזמן',
                    'pb_in_transit' => 'בְּמַעֲבָר',
                    'pb_out_for_delivery' => 'יוצא למשלוח',
                    'pb_delivered' => 'נמסר',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($records as $record) {
                Translation::updateOrCreate(
                // Match condition
                    ['shop_id' => $record['shop_id'], 'language' => $record['language']],
                    // Update or create values
                    $record
                );
            }
            $translation = Translation::where('shop_id',$session->id)->where('is_default',1)->first();
            if($translation==null){
                $translation=new Translation();
                $translation->shop_id=$session->id;
                $translation->is_default=1;
                $translation->save();
            }
            $client = new Rest($session->shop, (new \App\Services\ShopifyTokenService())->getValidAccessToken($session->shop));

            $shop_metafield = $client->post('metafields.json', [
                "metafield" => array(
                    "key" => 'translation',
                    "value" => json_encode($translation),
                    "type" => "json_string",
                    "namespace" => "autotrack"
                )
            ]);
        }catch (Exception $e){
            dump($e->getMessage());
            continue;
        }
    }


    dd('data sync');

});
Route::get('/pricing', function (Request $request) {
    $starter_plans = Plan::where('category','Starter')->get();
    $growth_plans = Plan::where('category','Growth')->get();
    $advance_plans = Plan::where('category','Advanced')->get();
    return view('plans',compact('starter_plans','growth_plans','advance_plans'));

});
Route::get('/simple_tracking_page', function (Request $request) {

    $section = json_decode(json_encode([ 'settings' => [ 'theme_color' => '#000000'] ]), false);
    return view('simple_tracking_page',compact('section'));

});
Route::get('/privacy_policy', function (Request $request) {
    return view('privacy_policy');

});
Route::get('/track', function (Request $request) {
    $shop = Session::where('shop', $request['shop'])->first();
    if ($shop) {
        $trackingPage = TrackingPage::where('session_id', $shop->id)
            ->where('theme_type', 'Modern')
            ->where('active_status', 1)->first();
        $translation = Translation::where('shop_id',$shop->id)->where('is_default',1)->first();
        $title="";
        $shop_name=$request['shop'];
        $storeLocations=[];
        $pageData=null;
        $custom_css="";
        if($trackingPage){
            $data = json_decode($trackingPage->data,false);
            $title=$trackingPage->name;
            if($data){
                $pageData=$data->pageData;
                if(isset($pageData->custom_css)){
                    $custom_css=$pageData->custom_css;
                }
                array_push($storeLocations,[
                    'name' => $pageData->store_name,
                    'lat' => 40.7128,
                    'lng' => -74.0060,
                ]);
            }
        }
        return view('modren_tracking_page',compact('shop_name','translation','custom_css','pageData','storeLocations','title','trackingPage'));

    }
     return response()->json([
         'status' => 'error',
         'message' => 'Not Found'
     ]);

});
Route::get('/api/track', function (Request $request) {

    $shop = Session::where('shop', $request['shop'])->first();
    if ($shop) {
        $trackingPage = TrackingPage::where('session_id', $shop->id)->where('theme_type', 'Modern')->first();
        if($trackingPage){
            $data = json_decode($trackingPage->data,false);
            $title=$trackingPage->name;
            $shop_name=$request['shop'];
            $storeLocations=[];
            if($data){

                array_push($storeLocations,[
                    'name' => $data->pageData->store_name,
                    'lat' => 40.7128,
                    'lng' => -74.0060,
                ]);
            }
            return view('modren_tracking_page',compact('shop_name','storeLocations','title','trackingPage'));
        }

    }
    return response()->json([
        'status' => 'error',
        'message' => 'Not Found'
    ]);
});
Route::get('/track/order', function (Request $request) {
    $tracking_number=$request->tracking_number;
    $html=view('proxy_page',compact('tracking_number'));
//    return ($html);
//    return response($html->render())->withHeaders(['Content-Type' => 'application/liquid','Transfer-Encoding'=> 'chunked']);
    return response($html->render()) ->withHeaders([
        'Content-Type' => 'application/liquid',
//        'Transfer-Encoding' => 'chunked',
//        'Content-Security-Policy' => "frame-ancestors 'self' https://*.myshopify.com https://admin.shopify.com;",
//        'X-Frame-Options' => 'ALLOW-FROM https://*.myshopify.com'
    ]);
})->middleware(['csp']);
//Route::get('/js/proxy_script', function (Request $request) {
//
//    $html=view('proxy_script');
//    return response($html->render())->withHeaders(['Content-Type' => 'application/javascript']);
//
//    return ($html);
//});
Route::fallback(function (Request $request) {


//    dd($request->all());
    if($request->shop){
        $helper = new \App\Http\Controllers\HelperController();
        $updateApp = $helper->updateShopifyApp($request->shop);
        if($updateApp){
            return view( 'update_app',compact('updateApp'));
        }
    }
    if (Context::$IS_EMBEDDED_APP &&  $request->query("embedded", false) === "1") {
        if (env('APP_ENV') === 'production') {
            return file_get_contents(public_path('index.html'));
        } else {
            return file_get_contents(base_path('frontend/index.html'));
        }
    } else {
        return redirect(Utils::getEmbeddedAppUrl($request->query("host", null)) . "/" . $request->path());
    }
})->middleware(['shopify.installed','csp']);
//,'order-plan-charge'

Route::get('/api/auth', function (Request $request) {
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    // Delete any previously created OAuth sessions that were not completed (don't have an access token)
    Session::where('shop', $shop)->where('access_token', null)->delete();

    return AuthRedirection::redirect($request);
});

Route::get('/api/auth/callback', function (Request $request) {
    $session = ExpiringOfflineOAuth::callback(
        $request->cookie(),
        $request->query(),
        ['App\Lib\CookieHandler', 'saveShopifyCookie']
    );

    $host = $request->query('host');
    $shop = Utils::sanitizeShopDomain($request->query('shop'));

    $access_token = $session->getAccessToken();
    $response = Registry::register('/api/webhooks/app-uninstall', Topics::APP_UNINSTALLED, $shop,$access_token);

//    dispatch(new afterAppInstallationJob($shop))->onConnection('database');
    Registry::register('/api/webhooks/order-create', Topics::ORDERS_CREATE, $shop, $session->getAccessToken());
    Registry::register('/api/webhooks/order-update', Topics::ORDERS_UPDATED, $shop, $session->getAccessToken());
    Registry::register('/api/webhooks/fulfillment-create', Topics::FULFILLMENTS_CREATE, $shop,$access_token);
    Registry::register('/api/webhooks/fulfillment-update', Topics::FULFILLMENTS_UPDATE, $shop,$access_token);

    Registry::register('/api/webhooks/product-create', Topics::PRODUCTS_CREATE, $shop,$access_token);
    Registry::register('/api/webhooks/product-update', Topics::PRODUCTS_UPDATE, $shop,$access_token);
    Registry::register('/api/webhooks/product-delete', Topics::PRODUCTS_DELETE, $shop,$access_token);

    Registry::register('/api/webhooks/collection-create', Topics::COLLECTIONS_CREATE, $shop,$access_token);
    Registry::register('/api/webhooks/collection-update', Topics::COLLECTIONS_UPDATE, $shop,$access_token);

    dispatch(new afterAppInstallationJob($shop))->onConnection('database');
    $redirectUrl = Utils::getEmbeddedAppUrl($host);
    if (Config::get('shopify.billing.required')) {
        list($hasPayment, $confirmationUrl) = EnsureBilling::check($session, Config::get('shopify.billing'));

        if (!$hasPayment) {
            $redirectUrl = $confirmationUrl;
        }
    }

    return redirect($redirectUrl);
});

Route::get('/api/products/count', function (Request $request) {
    /** @var AuthSession */
    $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

    $client = new Rest($session->getShop(), (new \App\Services\ShopifyTokenService())->getValidAccessToken($session->getShop()));
    $result = $client->get('products/count');

    return response($result->getDecodedBody());
})->middleware('shopify.auth');

Route::get('/api/products/create', function (Request $request) {
    /** @var AuthSession */
    $session = $request->get('shopifySession'); // Provided by the shopify.auth middleware, guaranteed to be active

    $success = $code = $error = null;
    try {
        ProductCreator::call($session, 5);
        $success = true;
        $code = 200;
        $error = null;
    } catch (\Exception $e) {
        $success = false;

        if ($e instanceof ShopifyProductCreatorException) {
            $code = $e->response->getStatusCode();
            $error = $e->response->getDecodedBody();
            if (array_key_exists("errors", $error)) {
                $error = $error["errors"];
            }
        } else {
            $code = 500;
            $error = $e->getMessage();
        }

        Log::error("Failed to create products: $error");
    } finally {
        return response()->json(["success" => $success, "error" => $error], $code);
    }
})->middleware('shopify.auth');

Route::post('/api/webhooks', function (Request $request) {
    try {
        $topic = $request->header(HttpHeaders::X_SHOPIFY_TOPIC, '');

        $response = Registry::process($request->header(), $request->getContent());
        if (!$response->isSuccess()) {
            Log::error("Failed to process '$topic' webhook: {$response->getErrorMessage()}");
            return response()->json(['message' => "Failed to process '$topic' webhook"], 500);
        }
    } catch (InvalidWebhookException $e) {
        Log::error("Got invalid webhook request for topic '$topic': {$e->getMessage()}");
        return response()->json(['message' => "Got invalid webhook request for topic '$topic'"], 401);
    } catch (\Exception $e) {
        Log::error("Got an exception when handling '$topic' webhook: {$e->getMessage()}");
        return response()->json(['message' => "Got an exception when handling '$topic' webhook"], 500);
    }
});

