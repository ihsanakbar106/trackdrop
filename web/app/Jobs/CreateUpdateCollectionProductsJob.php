<?php

namespace App\Jobs;

use App\Models\Collection;
use App\Models\CollectionDelivery;
use App\Models\CollectionProduct;
use App\Models\Product;
use App\Models\ProductDelivery;
use App\Models\ProductProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateUpdateCollectionProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 3600;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $product;
    public $shop;
    public $collection_id;

    public function __construct($product, $shop, $collection_id)
    {
        $this->product = $product;
        $this->shop = $shop;
        $this->collection_id = $collection_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $product = $this->product;
            $shop = $this->shop;
            $collection_id = $this->collection_id;
            $db_product = Product::where('shopify_product_id', $product->id)->where('session_id', $shop->id)->first();
            if (isset($db_product)) {
                $collectionProduct = CollectionProduct::where('product_id',$db_product->id)->
                where('collection_id', $collection_id)->first();
                if ($collectionProduct == null) {
                    $collectionProduct = new CollectionProduct();
                }
                $collectionProduct->product_id = $db_product->id;
                $collectionProduct->collection_id = $collection_id;
                $collectionProduct->save();

            }
        } catch (\Exception $exception) {

        }

    }
}
