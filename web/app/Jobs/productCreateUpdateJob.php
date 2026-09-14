<?php

namespace App\Jobs;

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\ProductController;
use App\Models\Session;
use App\Product;
use App\User;
use App\Variant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class productCreateUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 120;
    public $tries = 3;

    /** @var string */
    public $shopDomain;
    public $shopify_id;

    public function __construct($sessionOrDomain, $shopify_id)
    {
        $this->shopify_id = $shopify_id;
        $this->shopDomain = is_object($sessionOrDomain) && isset($sessionOrDomain->shop)
            ? (string) $sessionOrDomain->shop
            : (string) $sessionOrDomain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $session = Session::where('shop', $this->shopDomain)->first();
        $p_controller = new ProductController();
        $helper = new HelperController();
        if(isset($session)){
            $api = $helper->getShopApi($session->shop);
            $query = <<<GRAPHQL
                query {
                    product(id: "gid://shopify/Product/$this->shopify_id"){

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
                                variants(first: 250) {
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


                                        }
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
            GRAPHQL;
            $response = $api->graph($query);
            if($response['errors']==false) {
                if ($response['body']['data']['product']) {
                    $product = $response['body']['data']['product'];
                    $productData['node']['container']=$product;
                    $response = $p_controller->CreateUpdateProduct($productData, $session);
                }
            }
        }

    }
}
