<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CollectionProduct
 *
 * @property int $id
 * @property int|null $product_id
 * @property int|null $collection_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionProduct whereCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CollectionProduct whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CollectionProduct extends Model
{
    protected $guarded = [];
}
