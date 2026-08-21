<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use App\Services\ShopifyTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopify\Clients\Rest;

class SettingController extends HelperController
{
    public function checkPlan(Request $request)
    {
        $session = $this->getShop($request);

        $data = [
            'plan_id' => $session->plan_id
        ];

        return response()->json($data);
    }
    public function settings(Request $request): JsonResponse
    {
        $session = $this->getShop($request);
        $translation = Translation::where('shop_id',$session->id)->first();
        if($translation==null){
            $translation=new Translation();
            $translation->shop_id=$session->id;
            $translation->save();
        }
        $data = [
            'dropshipping_mode' => $session->dropshipping_mode,
            'tracking_link' => $session->tracking_link,
            'dropshipping_keyword' => $session->dropshipping_keyword,
            'translation' => $translation,
            'plan_id' => $session->plan_id
        ];

        return response()->json($data);
    }
    public function settings_save(Request $request): JsonResponse
    {
        $session = $this->getShop($request);
        $session->tracking_link=$request->tracking_link;
        $session->dropshipping_mode=$request->dropshipping_mode;
        $session->dropshipping_keyword=$request->dropshipping_keyword;
        $session->save();
        $data = [
            'status' => 'success',
            'message' => 'Successfully saved!',
        ];
        return response()->json($data);
    }
    public function translation_save(Request $request): JsonResponse
    {
        $session = $this->getShop($request);
//        dd($request->translation);
        Translation::where('shop_id',$session->id)->update(['is_default'=>0]);
        $translation = Translation::find($request->translation['id']);
        if ($translation) {
            $updatedTranslation = $request->translation;
            $updatedTranslation['is_default'] = 1;  // Set is_default to 1
            $translation->update($updatedTranslation);
        }
        $client = new Rest($session->shop, (new ShopifyTokenService())->getValidAccessToken($session->shop));

        $shop_metafield = $client->post('/admin/metafields.json', [
            "metafield" => array(
                "key" => 'translation',
                "value" => json_encode($translation),
                "type" => "json_string",
                "namespace" => "autotrack"
            )
        ]);
//        $response = $shop_metafield->getDecodedBody();
//        dd($response);
        $data = [
            'status' => 'success',
            'message' => 'Successfully saved!',
        ];
        return response()->json($data);
    }
}
