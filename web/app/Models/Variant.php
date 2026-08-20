<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Variant
 *
 * @property int $id
 * @property int $session_id
 * @property int $shopify_variant_id
 * @property int $shopify_product_id
 * @property int $product_id
 * @property string $title
 * @property string|null $image
 * @property int|null $position
 * @property float $price
 * @property string|null $sku
 * @property string|null $inventory_policy
 * @property string|null $compare_at_price
 * @property string|null $fulfillment_service
 * @property string|null $inventory_management
 * @property mixed|null $taxable
 * @property string|null $barcode
 * @property float|null $weight
 * @property string|null $weight_unit
 * @property string|null $admin_graphql_api_id
 * @property int|null $inventory_item_id
 * @property int|null $inventory_quantity
 * @property int|null $old_inventory_quantity
 * @property mixed|null $requires_shipping
 * @property float|null $grams
 * @property string|null $option1
 * @property string|null $option2
 * @property string|null $option3
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Variant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Variant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Variant query()
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereAdminGraphqlApiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereCompareAtPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereFulfillmentService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereGrams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereInventoryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereInventoryManagement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereInventoryPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereInventoryQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereOldInventoryQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereOption1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereOption2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereOption3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereRequiresShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereShopifyProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereShopifyVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereTaxable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variant whereWeightUnit($value)
 * @mixin \Eloquent
 */
class Variant extends Model
{
    //
}
