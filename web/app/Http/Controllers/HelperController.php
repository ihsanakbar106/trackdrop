<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Services\ShopifyTokenService;
use Gnikyt\BasicShopifyAPI\BasicShopifyAPI;
use Gnikyt\BasicShopifyAPI\Options;
use Illuminate\Http\Request;

class HelperController extends Controller
{
    public function updateShopifyApp($shop_name)
    {
        $session=Session::where('shop',$shop_name)->first();
        $query='query AccessScopeList {
  currentAppInstallation {
    accessScopes {
      handle
    }
  }
}';
        $scopes_response = $this->getShopApi($session->shop)->graph($query);
        $envScopes=explode(",",env('SCOPES'));
        $redirect_url=urlencode("https://app.theautotrack.com/api/auth/callback");
        $api_key=env('SHOPIFY_API_KEY');
        $shop_name=str_replace('.myshopify.com','',$session->shop);
        if ($scopes_response['errors'] == false) {
            $apiScopes = json_decode(json_encode($scopes_response['body']['data']['currentAppInstallation']['accessScopes']), false);
            $apiScopes = collect($apiScopes)->pluck('handle')->toArray();

            $missingInAPI = array_diff($envScopes, $apiScopes);
            $extraInAPI = array_diff($apiScopes, $envScopes);

            if (!empty($missingInAPI) || !empty($extraInAPI)) {

//            updateAppScopes($missingInAPI, $extraInAPI);

                $update_url="https://admin.shopify.com/store/$shop_name/oauth/authorize?client_id=$api_key&scope=".env('SCOPES')."&redirect_uri=$redirect_url&state={nonce}&grant_options[]={access_mode}";
                return ($update_url);
            }else{
                $apiScopes=implode(',',$apiScopes);
                $session->scope=$apiScopes;
                $session->save();
            }
        }
        return null;
    }
    public function getShop($request)
    {
        $session_obj = $request->get('shopifySession');
        if($session_obj){
            $session = Session::where('shop', $session_obj->getShop())->first();
        }else{
            $session = Session::first();
        }
        return $session;
    }
    public function getShopApi($shop_name)
    {
        $options = new Options();
        $options->setType(true);
        $options->setVersion(env('SHOPIFY_API_VERSION', '2026-07'));
        $options->setApiKey(env('SHOPIFY_API_KEY'));
        $options->setApiSecret(env('SHOPIFY_API_SECRET'));
        $options->setApiPassword((new ShopifyTokenService())->getValidAccessToken($shop_name));
        // Default package timeout is 10s; large order pages (limit=250) often need longer.
        $options->setGuzzleOptions([
            'timeout' => 60.0,
            'connect_timeout' => 15.0,
        ]);

        $api = new BasicShopifyAPI($options);
        $api->setSession(new \Gnikyt\BasicShopifyAPI\Session($shop_name));

        return $api;
    }
}
