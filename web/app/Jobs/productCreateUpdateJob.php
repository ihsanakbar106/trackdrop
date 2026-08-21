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
    public $timeout = 36000000; // 2 minute
    public $tries = 5;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $session;
    public $shopify_id;
    public function __construct($session,$shopify_id)
    {
        $this->session = $session;
        $this->shopify_id = $shopify_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $session = $this->session;
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
