<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LineItem
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $order_id
 * @property int|null $shopify_order_id
 * @property int|null $shopify_lineitem_id
 * @property int|null $shopify_fulfillment_order_id
 * @property int|null $variant_id
 * @property int|null $product_id
 * @property string|null $title
 * @property int|null $quantity
 * @property int|null $grams
 * @property string|null $sku
 * @property float|null $price
 * @property int|null $fulfillable_quantity
 * @property string|null $fulfillment_status
 * @property string|null $fulfillment_service
 * @property string|null $fulfillment_response
 * @property string|null $variant_title
 * @property string|null $properties
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereFulfillableQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereFulfillmentResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereFulfillmentService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereFulfillmentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereGrams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereShopifyFulfillmentOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereShopifyLineitemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereShopifyOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineItem whereVariantTitle($value)
 * @mixin \Eloquent
 */
class LineItem extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
