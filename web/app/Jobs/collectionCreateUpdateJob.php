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

class collectionCreateUpdateJob implements ShouldQueue
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
        $c_controller = new CollectionController();
        $helper = new HelperController();
        if(isset($session)){
            $api = $helper->getShopApi($session->shop);
            $query = <<<GRAPHQL
                query {
                    collection(id: "gid://shopify/Collection/$this->shopify_id"){
                    id
                    handle
                    title
                    description
                    image{
                        url
                    }
                }
            GRAPHQL;
            $response = $api->graph($query);
            if($response['errors']==false) {
                if ($response['body']['data']['collection']) {
                    $collection = $response['body']['data']['collection'];
                    $collectionData['node']['container']=$collection;
                    $response = $c_controller->CreateUpdateCollection($collectionData, $session);
                }
            }
        }

    }
}
