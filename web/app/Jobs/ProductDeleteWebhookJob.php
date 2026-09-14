<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProductDeleteWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shopifyProductId;

    public function __construct($shopifyProductId)
    {
        $this->shopifyProductId = $shopifyProductId;
    }

    public function handle()
    {
        $product = Product::where('shopify_product_id', $this->shopifyProductId)->first();
        if (!$product) {
            return;
        }

        if ($product->variants && $product->variants->count()) {
            foreach ($product->variants as $variant) {
                $variant->forceDelete();
            }
        }

        $product->forceDelete();
    }
}
