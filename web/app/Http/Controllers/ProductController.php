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
        // Supports both shapes:
        // - GraphQL edge ResponseAccess: node['container'] => product fields array
        // - productCreateUpdateJob: ['node']['container'] = product ResponseAccess/array
        // - plain edge array: ['node'] => product fields (no container key)
        $node = is_array($productData) || $productData instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess
            ? ($productData['node'] ?? null)
            : null;
        if ($node === null) {
            return false;
        }

        if ($node instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
            $productNode = $node['container'];
        } elseif (is_array($node)) {
            $productNode = $node['container'] ?? $node;
        } elseif (is_object($node)) {
            $asArray = json_decode(json_encode($node), true) ?: [];
            $productNode = $asArray['container'] ?? $asArray;
        } else {
            return false;
        }

        if ($productNode instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
            $productNode = $productNode['container'];
        } elseif (is_object($productNode)) {
            $productNode = json_decode(json_encode($productNode), true) ?: [];
        }

        if (!is_array($productNode) || empty($productNode)) {
            return false;
        }

        $shop_id = $shop->id;
        $pId = $productNode['id'] ?? null;
        if (!$pId) {
            return false;
        }
        $numericPId = substr($pId, strrpos($pId, '/') + 1);
        $product_save = Product::where('shopify_product_id', $numericPId)->where('session_id', $shop->id)->first();
        if ($product_save === null) {
            $product_save = new Product();
        }

        $variantEdges = data_get($productNode, 'variants.edges', []);
        $firstVariantId = data_get($variantEdges, '0.node.id');
        $numeric_variantId = $firstVariantId
            ? substr($firstVariantId, strrpos($firstVariantId, '/') + 1)
            : null;

        $tags = $productNode['tags'] ?? [];
        if ($tags instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
            $tags = $tags->toArray();
        } elseif (!is_array($tags)) {
            $tags = $tags === null || $tags === '' ? [] : (array) $tags;
        }

        $options = $productNode['options'] ?? [];
        if ($options instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
            $options = $options->toArray();
        }

        $product_save->shopify_product_id = $numericPId;
        $product_save->shopify_variant_id = $numeric_variantId;
        $product_save->session_id = $shop->id;
        $product_save->is_gifted = data_get($productNode, 'isGiftCard') == true ? 1 : 0;
        $product_save->body_html = $productNode['description'] ?? null;
        $product_save->title = $productNode['title'] ?? null;
        $product_save->product_type = $productNode['productType'] ?? null;
        $product_save->handle = $productNode['handle'] ?? null;
        $product_save->product_status = $productNode['status'] ?? null;
        $product_save->tags = implode(',', $tags);
        $product_save->vendor = $productNode['vendor'] ?? null;
        $product_save->image = data_get($productNode, 'featuredMedia.image.url');
        $product_save->options = json_encode($options);
        $product_save->created_at = $productNode['createdAt'] ?? now();
        $product_save->save();

        foreach ($variantEdges as $variant) {
            if ($variant instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                $variant = $variant->toArray();
            }
            $variantNode = $variant['node'] ?? null;
            if (!$variantNode) {
                continue;
            }
            if ($variantNode instanceof \Gnikyt\BasicShopifyAPI\ResponseAccess) {
                $variantNode = $variantNode->toArray();
            }

            $variantId = $variantNode['id'] ?? null;
            if (!$variantId) {
                continue;
            }
            $numericVariantId = substr($variantId, strrpos($variantId, '/') + 1);
            $inventoryId = null;
            $db_variant = Variant::where('shopify_variant_id', $numericVariantId)->first();
            if ($db_variant == null) {
                $db_variant = new Variant();
            }
            $db_variant->session_id = $shop_id;
            $db_variant->shopify_variant_id = $numericVariantId;
            $db_variant->shopify_product_id = $product_save->shopify_product_id;
            $db_variant->product_id = $product_save->id;
            $db_variant->inventory_item_id = $inventoryId;
            $db_variant->title = $variantNode['title'] ?? null;
            $db_variant->price = $variantNode['price'] ?? null;
            $db_variant->inventory_quantity = $variantNode['inventoryQuantity'] ?? null;
            $db_variant->sku = $variantNode['sku'] ?? null;
            $db_variant->save();
        }

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
