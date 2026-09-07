<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\TrackingPage;
use App\Models\Translation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shopify\Rest\Admin2022_04\Page;

class TrackingController extends HelperController
{
    public function tracking_pages(Request $request)
    {

//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $tracking_pages = TrackingPage::where('session_id', $session->id)->orderBy('created_at', 'desc')->get();
        $themes_res = $this->getShopApi($session->shop)
            ->rest('GET', '/admin/themes.json', ['query' => ['role' => 'main']]);
        $active_theme_id = null;
        if ($themes_res['errors'] == false) {
            $themes_res = json_decode(json_encode($themes_res['body']['themes']), false);
            if(!empty($themes_res)){
                $active_theme_id = $themes_res[0]->id;
            }
        }
        $translations = Translation::where('shop_id',$session->id)->get();

        $data = [
            'tracking_pages' => $tracking_pages,
            'active_theme_id' => $active_theme_id,
            'translations' => $translations,
            'plan_id' => $session->plan_id,
            'modern_tracking_path' => app_proxy_modern_path(),
            'modern_tracking_url' => app_proxy_modern_url($session->shop),
        ];
        return response()->json($data);
    }

    private function validateModernStoreName(Request $request, $themeType = null)
    {
        $themeType = $themeType ?? $request->theme_type;
        if ($themeType !== 'Modern') {
            return null;
        }

        $data = $request->data;
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }
        if (!is_array($data)) {
            $data = [];
        }

        $pageData = $data['pageData'] ?? [];
        if (is_string($pageData)) {
            $decodedPageData = json_decode($pageData, true);
            $pageData = json_last_error() === JSON_ERROR_NONE ? $decodedPageData : [];
        }
        if (!is_array($pageData)) {
            $pageData = [];
        }

        $storeName = $pageData['store_name'] ?? '';
        if (!is_string($storeName) || trim($storeName) === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Store name is required',
            ], 422);
        }

        return null;
    }

    public function create_tracking_page(Request $request)
    {

//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();

        $session = $this->getShop($request);

        if ($validationError = $this->validateModernStoreName($request)) {
            return $validationError;
        }

        DB::beginTransaction();
        try {

            $tracking_page = new TrackingPage();
            $tracking_page->session_id = $session->id;
            $tracking_page->page_name = $request->page_name;
            $tracking_page->theme_type = $request->theme_type;
            $tracking_page->data = isset($request->data) && $request->data != '' ? json_encode($request->data) : null;

            $tracking_page->save();

            DB::commit();
            if ($tracking_page->theme_type === 'Traditional') {

                $data = $this->createUpdatePage($tracking_page);
//                dd($data);
                if(isset($data) && $data['status'] === 'success' && $data['api_type'] === 'create page' &&
                isset($data['res']) && isset($data['res']['errors'])
                ){
                    $page_res = json_decode(json_encode($data['res']['body']['page']),false);
                    $tracking_page->shopify_page_template = $page_res->template_suffix;
                    $tracking_page->page_name = $page_res->title;
                    $tracking_page->shopify_page_id = $page_res->id;
                    $tracking_page->page_handle = $page_res->handle;
                    $tracking_page->tracking_page_published_at = isset($page_res->published_at)?Carbon::createFromTimeString($page_res->published_at)->format('Y-m-d H:i:s'):null;
                    $tracking_page->save();
                    $data['tracking_page'] = $tracking_page;
                }
                return $data;
            }elseif($tracking_page->theme_type === 'Modern'){
                if(isset($request->logo) && $request->logo != ""  && is_file($request->logo)){
                    $image = $request->logo;
                    $destinationPath = 'images/';
                    $filename =  uniqid()."_".now()->format('YmdHi').".".$image->getClientOriginalExtension();
                    $image->move($destinationPath, $filename);
                    $tracking_page->logo = "images/".$filename;
                }elseif(isset($request->old_logo) && $request->old_logo != ""){
                    $tracking_page->logo = $request->old_logo;
                }elseif($request->logo == null || $request->logo == ""){
                    $tracking_page->logo = null;
                }
                if(isset($request->icon) && $request->icon != ""  && is_file($request->icon)){
                    $image = $request->icon;
                    $destinationPath = 'images/';
                    $filename = uniqid()."_".now()->format('YmdHi').".".$image->getClientOriginalExtension();
                    $image->move($destinationPath, $filename);
                    $tracking_page->icon = "images/".$filename;
                }elseif(isset($request->old_icon) && $request->old_icon != ""){
                    $tracking_page->icon = $request->old_icon;
                }elseif($request->icon == null || $request->icon == ""){
                    $tracking_page->icon = null;
                }
                $tracking_page->save();
            }
            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!',
                'tracking_page' => $tracking_page
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $data = [
                'status' => 'error',
                'message' => "Error: " . $e->getMessage()
            ];
        }

        return response()->json($data);
    }

    public function publish_tracking_page(Request $request,$id){

//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $pages = TrackingPage::where('session_id',$session->id)->get();
        if($pages->count()){
            foreach ($pages as $page){
                $page->active_status = 0;
                $page->save();
            }
        }
        $page = TrackingPage::find($id);
        $page->active_status = 1;
        $page->save();
        $data = [
            'status' => 'success',
            'message' => 'Successfully published!'
        ];
        return  response()->json($data);
    }

    public function tracking_page_detail_save(Request $request,$id)
    {

//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $tracking_page = TrackingPage::find($id);

        $themeType = $request->theme_type ?: ($tracking_page->theme_type ?? null);
        if ($validationError = $this->validateModernStoreName($request, $themeType)) {
            return $validationError;
        }

        try {
//            if($tracking_page->theme_type === 'Modern'){
                $tracking_page->page_name = $request->page_name;
                $tracking_page->data = isset($request->data) && $request->data != '' ? json_encode($request->data) : null;

                if(isset($request->logo) && $request->logo != "" && is_file($request->logo)){
                    $image = $request->logo;
                    $destinationPath = 'images/';
                    $filename =  uniqid()."_".now()->format('YmdHi').".".$image->getClientOriginalExtension();
                    $image->move($destinationPath, $filename);
                    $tracking_page->logo = "images/".$filename;
                }elseif(isset($request->old_logo) && $request->old_logo != ""){
                    $tracking_page->logo = $request->old_logo;
                }elseif($request->logo == null || $request->logo == ""){
                    $tracking_page->logo = null;
                }

                if(isset($request->icon) && $request->icon != ""  && is_file($request->icon)){
                    $image = $request->icon;
                    $destinationPath = 'images/';
                    $filename =  uniqid()."_".now()->format('YmdHi').".".$image->getClientOriginalExtension();
                    $image->move($destinationPath, $filename);
                    $tracking_page->icon = "images/".$filename;
                }elseif(isset($request->old_icon) && $request->old_icon != ""){
                    $tracking_page->icon = $request->old_icon;
                }elseif($request->icon == null || $request->icon == ""){
                    $tracking_page->icon = null;
                }
                $tracking_page->save();
//            }
            $data = [
                'status' => 'success',
                'message' => 'Successfully saved!'
            ];
        } catch (\Exception $e) {
            $data = [
                'status' => 'error',
                'message' => "Error: " . $e->getMessage()
            ];
        }

        return response()->json($data);
    }

    public function tracking_page_detail(Request $request,$id){
        $page = TrackingPage::find($id);
        return response()->json([
           'tracking_page_data'=>$page
        ]);
    }

    public function duplicate_tracking_page(Request $request,$id)
    {

//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);

        DB::beginTransaction();
        try {
            $old_tracking_page = TrackingPage::find($id);
            if(isset($old_tracking_page)){
                if(isset($old_tracking_page->page_name) && $old_tracking_page->page_name === $request->page_name){
                    $data = [
                        'status' => 'error',
                        'message' => 'This page name is already exist!'
                    ];
                }else{
                    $tracking_page = new TrackingPage();
                    $tracking_page->session_id = $session->id;
                    $tracking_page->page_name = $request->page_name;
                    $tracking_page->theme_type = $old_tracking_page->theme_type;
                    $tracking_page->data = isset($old_tracking_page->data) && $old_tracking_page->data != '' ? $old_tracking_page->data : null;

                    $tracking_page->save();

                    DB::commit();
                    if ($tracking_page->theme_type === 'Traditional') {

                        $data = $this->createUpdatePage($tracking_page);
                        if(isset($data) && $data['status'] === 'success' && $data['api_type'] === 'create page' &&
                            isset($data['res']) && isset($data['res']['errors'])
                        ){
                            $page_res = json_decode(json_encode($data['res']['body']['page']),false);
                            $tracking_page->shopify_page_template = $page_res->template_suffix;
                            $tracking_page->page_name = $page_res->title;
                            $tracking_page->shopify_page_id = $page_res->id;
                            $tracking_page->page_handle = $page_res->handle;
                            $tracking_page->tracking_page_published_at = isset($page_res->published_at)?Carbon::createFromTimeString($page_res->published_at)->format('Y-m-d H:i:s'):null;
                            $tracking_page->save();
                        }
                        return $data;
                    }
                    $data = [
                        'status' => 'success',
                        'message' => 'Successfully saved!'
                    ];
                }

            }else{
                $data = [
                    'status' => 'error',
                    'message' => "This tracking page is not found!"
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $data = [
                'status' => 'error',
                'message' => "Error: " . $e->getMessage()
            ];
        }

        return response()->json($data);
    }

    public function createUpdatePage($tracking_page)
    {
        $session = Session::find($tracking_page->session_id);

        $themes_res = $this->getShopApi($session->shop)
            ->rest('GET', '/admin/themes.json', ['query' => ['role' => 'main']]);

        if ($themes_res['errors'] == false) {
            $data = isset($tracking_page->data)?json_decode($tracking_page->data):null;
            $themes_res = json_decode(json_encode($themes_res['body']['themes']), false);
//            $content=view('simple_tracking_page')->render();
            $content = file_get_contents(public_path('simple_tracking_page.text')); // Read the content of the file
            $content = str_replace('https://app.theautotrack.com', app_public_url(), $content);

            if (!empty($themes_res)) {
                foreach ($themes_res as $theme) {
                    $resp = $this->getShopApi($session->shop)
                        ->rest('PUT', '/admin/themes/' . $theme->id . '/assets.json', [
                        "asset" => [
                            "key" => "templates/page.auto-track-".generateSlug($data->handle).".liquid",
//                            "key" => "templates/page.order-tracking-".generateSlug($tracking_page->page_name).".liquid",
                            "value" => "{% section 'auto-track-".generateSlug($data->handle)."' %}"
                        ]
                    ]);
                    $resp2 = $this->getShopApi($session->shop)
                        ->rest('PUT', '/admin/themes/' . $theme->id . '/assets.json', [
                        "asset" => [
                            "key" => "sections/auto-track-".generateSlug($data->handle).".liquid",
//                            "key" => "templates/page.order-tracking-".generateSlug($tracking_page->page_name).".liquid",
                            "value" => "$content"
                        ]
                    ]);
//                    dd($resp,$resp2);
                    if ($resp['errors'] === false) {
                        $resp = $resp['body']['asset'];
                        $pattern = '/templates\/page\.(.*?)\.(json|liquid)/';
                        if (preg_match($pattern, $resp['key'], $matches)) {
                            $data = [
                                'page' => [
                                    'title' => $tracking_page->page_name,
                                    'template_suffix' => $matches[1]
                                ]
                            ];
                            $res = $this->getShopApi($session->shop)->rest('post', '/admin/pages.json', $data);
                            $data = [
                                'status' => 'success',
                                'api_type' => 'create page',
                                'res' => $res
                            ];
                            return $data;
                        }
                    }else{
                        $data = [
                            'status' => 'success',
                            'api_type' => 'create template',
                            'res' => $resp
                        ];
                        return $data;
                    }
                }
            }
        }

        $data = [
            'status' => 'error',
            'api_type' => 'random error',
        ];
        return $data;
    }

    public function update_tracking_page(Request $request, $id)
    {

//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);


        $data = [
            'status' => 'success',
            'message' => 'Successfully saved!'
        ];
        return response()->json($data);
    }

    public function delete_tracking_page(Request $request,$id)
    {

//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        $page = TrackingPage::find($id);
//
        if(isset($page->theme_type) && $page->theme_type == 'Traditional'){
            $this->getShopApi($session->shop)->rest('delete', "/admin/pages/$page->shopify_page_id.json");

            $themes_res = $this->getShopApi($session->shop)
                ->rest('GET', '/admin/themes.json', ['query' => ['role' => 'main']]);

            if ($themes_res['errors'] == false) {
                $data = isset($page->data)?json_decode($page->data):null;
                $themes_res = json_decode(json_encode($themes_res['body']['themes']), false);

                if (!empty($themes_res)) {
                    foreach ($themes_res as $theme) {
                        $resp = $this->getShopApi($session->shop)
                            ->rest('PUT', '/admin/themes/' . $theme->id . '/assets.json', [
                                "asset" => [
                                    "key" => "templates/page.auto-track-".generateSlug($data->handle).".liquid",
//                            "key" => "templates/page.order-tracking-".generateSlug($tracking_page->page_name).".liquid",
                                    "value" => "<div></div>"
                                ]
                            ]);
                        $resp2 = $this->getShopApi($session->shop)
                            ->rest('PUT', '/admin/themes/' . $theme->id . '/assets.json', [
                                "asset" => [
                                    "key" => "sections/auto-track-".generateSlug($data->handle).".liquid",
//                            "key" => "templates/page.order-tracking-".generateSlug($tracking_page->page_name).".liquid",
                                    "value" => "<div></div>"
                                ]
                            ]);

                    }
                }
            }

            $page->delete();
            $data = [
                'status' => 'success',
                'message' => 'Successfully deleted!'
            ];
        }elseif(isset($page->theme_type) && $page->theme_type == 'Modern'){
            $page->delete();
            $data = [
                'status' => 'success',
                'message' => 'Successfully deleted!'
            ];
        }else{
            $data = [
                'status' => 'error',
                'message' => 'Not deleted!'
            ];
        }
        return response()->json($data);
    }
}
