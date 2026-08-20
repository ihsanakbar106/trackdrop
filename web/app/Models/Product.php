<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $shopify_product_id
 * @property int|null $shopify_variant_id
 * @property string|null $body_html
 * @property string|null $title
 * @property string|null $product_type
 * @property string|null $handle
 * @property string|null $published_scope
 * @property string|null $tags
 * @property string|null $vendor
 * @property string|null $image
 * @property string|null $options
 * @property string|null $published_at
 * @property int $status
 * @property int $is_gifted
 * @property string|null $product_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereBodyHtml($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereHandle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereIsGifted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereProductStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereProductType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePublishedScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereShopifyProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereShopifyVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereVendor($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
    protected $guarded = [];
}
