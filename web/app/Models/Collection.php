<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Collection
 *
 * @property int $id
 * @property int|null $shopify_collection_id
 * @property int|null $session_id
 * @property string|null $title
 * @property string|null $handle
 * @property string|null $image
 * @property int|null $sync_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $has_products
 * @property-read int|null $has_products_count
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection query()
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereHandle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereShopifyCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereSyncStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Collection whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Collection extends Model
{
    protected $guarded = [];
    public function has_products()
    {
        return $this->belongsToMany(Product::class, 'collection_products');
    }
}
