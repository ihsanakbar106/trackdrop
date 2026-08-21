<?php

namespace App\Http\Controllers;

use App\Jobs\afterAppInstallationJob;
use App\Jobs\syncCollectionJob;
use App\Jobs\syncProductRecommendationJob;
use App\Models\Collection;
use App\Models\Image;
use App\Models\Product;
use App\Models\Recommendation;
use App\Models\RelatedProduct;
use App\Models\Session;
use App\Models\SessionWidget;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shopify\Clients\Graphql;

class ProductController extends HelperController
{
    public function product_list(Request $request)
    {
        // $session_obj = $request->get('shopifySession');
        // $session = Session::where('shop',$session_obj->getShop())->first();
        $session = $this->getShop($request);
        $search = $request->search;

        $products = Product::where('session_id', $session->id)->where('product_status', 'active');
        if (isset($search) && $search != '') {
            $products->where('title', 'like', '%' . $search . '%');
        }
        $products = $products->orderBy('created_at', 'desc')->take(50)->get();
        $data = [
            'products' => $products
        ];
        return response()->json($data);
    }


    public function sync_store_products(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//       $session = Session::where('shop', $session_obj->getShop())->first();
        $session = $this->getShop($request);
        return $this->sync_products($session->shop, null);
    }



    public function sync_products($shop_name = null, $endCursor = null)
    {
        $session = Session::where('shop', $shop_name)->first();
        if (isset($session)) {
            $pagination = null;
            if ($endCursor) {
                $pagination = ', after: "' . $endCursor . '"';
            }

            $api = $this->getShopApi($session->shop);
            $query = <<<GRAPHQL
                query {
                    products(first: 250$pagination) {
                        edges {
                            node {
                                id
                                title
                                status
                                tags
                                vendor
                                createdAt
                                hasOnlyDefaultVariant
                                description
                                isGiftCard
                                featuredMedia {
                                    ... on MediaImage {
                                        image {
                                            altText
                                            id
                                            height
                                            width
                                            url
                                        }
                                    }
                                }

                                options {
                                    id
                                    name
                                    position
                                    values
                                }
                                variants(first: 1) {
                                    edges {
                                        node {
                                            id
                                            title
                                            sku
                                            barcode
                                            price
                                            compareAtPrice
                                            inventoryQuantity
                                            image{
                                                id
                                                url
                                                altText
                                            }
                                            inventoryPolicy
                                            inventoryItem{
                                                    id
                                                    tracked
                                                    requiresShipping
                                                    measurement {
                                                        weight {
                                                            value
                                                            unit
                                                        }
                                                    }

                                            }
                                        }
                                    }
                                    pageInfo {
                                        startCursor
                                        hasPreviousPage
                                        hasNextPage
                                        endCursor
                                    }
                                }
                                category {
                                            id
                                }
                                productType
                                hasOutOfStockVariants
                                tracksInventory
                                totalInventory
                                handle
                            }
                        }
                        pageInfo {
                            startCursor
                            hasPreviousPage
                            hasNextPage
                            endCursor
                        }
                    }

                }
            GRAPHQL;
            $response = $api->graph($query);
//            dd($response);

            if ($response['errors'] == false) {
                $paginfo = optional($response['body']['data']['products'])['pageInfo'];
                if ($response['body']['data']['products']['edges']) {
                    $products = $response['body']['data']['products']['edges'];
                    foreach ($products as $productData) {
                        $response = $this->CreateUpdateProduct($productData, $session);
                    }
                }
                if ($paginfo['hasNextPage']) {
                    $endCursor = $paginfo['endCursor'];
                    return $this->syncProducts($session, $endCursor);
                }
            }

            dispatch(new syncCollectionJob($session->shop))->onConnection('database');

            return response()->json([
                'status' => 'success',
                'message' => 'Products will be synchronize with in some time!'
            ]);
        }else {
            return response()->json([
                'status' => 'error',
                'message' => "This shop not found!"
            ]);
        }
    }
    function createUpdateProduct($productData, $shop)
    {
        $productNode = $productData['node']['container'];

//        dump($productNode);
        $shop_id = $shop->id;
        $pId =  $productNode['id'];
        $numericPId = substr($pId, strrpos($pId, '/') + 1);
        $product_save = Product::where('shopify_product_id', $numericPId)->where('session_id', $shop->id)->first();
        if ($product_save === null) {
            $product_save = new Product();
        }
        $variant_id =  $productNode['variants']['edges'][0]['node']['id'];
        $numeric_variantId = substr($variant_id, strrpos($variant_id, '/') + 1);

        $product_save->shopify_product_id = $numericPId;
        $product_save->shopify_variant_id = $numeric_variantId;
        $product_save->session_id = $shop->id;
        $product_save->is_gifted = data_get($productNode, 'isGiftCard') == true ? 1 : 0;
        $product_save->body_html = $productNode['description'];
        $product_save->title = $productNode['title'];
        $product_save->product_type = $productNode['productType'];
        $product_save->handle = $productNode['handle'];
        $product_save->product_status = $productNode['status'];
        $product_save->tags = implode(',', $productNode['tags']);
        $product_save->vendor = $productNode['vendor'];
        $product_save->image = data_get($productNode, 'featuredMedia.image.url');
        $product_save->options = json_encode($productNode['options']);
        $product_save->created_at = $productNode['createdAt'];
        $product_save->save();
//dd($product);
        // Variants Save
        $variants =  $productNode['variants']['edges'];

        foreach ($variants as $key => $variant) {
//            dd($variant);
            $variantNode = $variant['node'];
            $variantId = $variantNode['id'];
            $numericVariantId = substr($variantId, strrpos($variantId, '/') + 1);
//            $inventoryId = $variantNode['inventoryItem']['id'];
//            $inventoryId = substr($inventoryId, strrpos($inventoryId, '/') + 1);
            $inventoryId=null;
            $db_variant = Variant::where('shopify_variant_id', $numericVariantId)->first();
            if ($db_variant == null) {
                $db_variant = new Variant();
            }
            $db_variant->session_id = $shop_id;
            $db_variant->shopify_variant_id = $numericVariantId;
            $db_variant->shopify_product_id = $product_save->shopify_product_id;
            $db_variant->product_id = $product_save->id;
            $db_variant->inventory_item_id = $inventoryId;
            $db_variant->title = $variantNode['title'];
            $db_variant->price = $variantNode['price'];
            $db_variant->inventory_quantity = $variantNode['inventoryQuantity'];
            $db_variant->sku = $variantNode['sku'];
            $db_variant->save();

        }

//            dump($productData);

        return true;
    }




    public function all_collections(Request $request)
    {
//        $session_obj = $request->get('shopifySession');
//        $session = Session::where('shop',$session_obj->getShop())->first();
        $session = $this->getShop($request);

        $all_collections = Collection::where('session_id', $session->id)->get();

        $data = [
            'all_collections' => $all_collections,
        ];

        return response()->json($data);
    }
}
