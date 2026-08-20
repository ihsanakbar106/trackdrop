<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Carrier
 *
 * @property int $id
 * @property int|null $carrier_service_id
 * @property string|null $name
 * @property string|null $code
 * @property string|null $picture
 * @property string|null $homepage
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier query()
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier whereCarrierServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier whereHomepage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carrier whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Carrier extends Model
{
    //
}
