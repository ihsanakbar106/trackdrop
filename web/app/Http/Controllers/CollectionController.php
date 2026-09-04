<?php

namespace App\Http\Controllers;

use App\Jobs\CreateUpdateCollectionProductsJob;
use App\Models\Collection;
use App\Models\CollectionProduct;
use App\Models\ErrorMessage;
use App\Models\Product;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public $helper;

    function __construct()
    {
        $this->helper = new HelperController();
    }
    public function sync_collections($shop_name = null, $endCursor = null)
    {
        if ($shop_name == null) {
            $shop = Session::first();
        }else{
            $shop=Session::where('shop',$shop_name)->first();
        }
        $pagination = null;
        if ($endCursor) {
            $pagination = ', after: "' . $endCursor . '"';
        }
        $api = $this->helper->getShopApi($shop->shop);
        $query = <<<GRAPHQL
                query {
                    collections(first: 250$pagination) {
                        edges {
                            node {
                                id
                                handle
                                title
                                description
                                image{
                                    url
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

                }
            GRAPHQL;
        $response = $api->graph($query);
//        dd($response);
        if ($response['errors'] == false) {
            $paginfo = optional($response['body']['data']['collections'])['pageInfo'];
            if ($response['body']['data']['collections']['edges']) {
                $collections = $response['body']['data']['collections']['edges'];
                foreach ($collections as $collectionData) {
//                    dd($productData);
                    $response = $this->CreateUpdateCollection($collectionData, $shop);
                }
            }
            if ($paginfo['hasNextPage']) {
                $endCursor = $paginfo['endCursor'];
                return $this->sync_collections($shop_name, $endCursor);
            }
        }

    }
    function CreateUpdateCollection($collectionData, $shop)
    {
        $collectionNode = $collectionData['node']['container'] ?? $collectionData['node'];

        $shop_id = $shop->id;
        $pId = $collectionNode['id'];
        $numericPId = substr($pId, strrpos($pId, '/') + 1);
        $collection = Collection::where('shopify_collection_id', $numericPId)
            ->where('session_id', $shop_id)
            ->first();
        if (!$collection) {
            $collection = new Collection();
        }

        $collection->shopify_collection_id = $numericPId;
        $collection->session_id = $shop_id;
        $collection->title = $collectionNode['title'];
        $collection->handle = $collectionNode['handle'];
        $collection->image = $collectionNode['image']!= null ? $collectionNode['image']['url'] : null;
        $collection->save();
        $this->CollectionsProductsNew($numericPId, $collection->id, $shop);
        return true;
    }




    public function CollectionsProductsNew($collectionId,$collectionDbId,$shop)
    {
        $productIds = [];
        $cursor = null;
        $hasNextPage = true;
        $collectionGid = str_starts_with((string) $collectionId, 'gid://')
            ? $collectionId
            : 'gid://shopify/Collection/' . $collectionId;

        while ($hasNextPage) {
            $GET_COLLECTION_PRODUCTS_QUERY = '
            query {
                collection(id: "' . $collectionGid . '") {
                    products(first: 250' . ($cursor ? ', after: "' . $cursor . '"' : '') . ') {
                        edges {
                            node {
                                id
                            }
                        }
                        pageInfo {
                            hasNextPage
                            endCursor
                        }
                    }
                }
            }';

            $api = $this->helper->getShopApi($shop->shop);
            $response = $api->graph($GET_COLLECTION_PRODUCTS_QUERY);

            if ($response['errors'] == false && isset($response['body']['data']['collection']['products'])) {
                $productsData = $response['body']['data']['collection']['products'];
                foreach ($productsData['edges'] as $edge) {
                    $product_id = str_replace('gid://shopify/Product/', '', $edge['node']['id']);
                    $db_product = Product::where('shopify_product_id', $product_id)->where('session_id', $shop->id)->first();
                    if($db_product) {
                        $productIds[] = $db_product->id;
                    }
                }
                $hasNextPage = (bool) $productsData['pageInfo']['hasNextPage'];
                $cursor = $productsData['pageInfo']['endCursor'];
            } else {
                $hasNextPage = false;
            }
        }
        foreach ($productIds as $productId) {
            CollectionProduct::updateOrCreate(
                ['collection_id' => $collectionDbId, 'product_id' => $productId]
            );
        }
    }

}
