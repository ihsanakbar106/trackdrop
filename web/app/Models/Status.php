<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Status
 *
 * @property int $id
 * @property int|null $fulfillment_id
 * @property string|null $tracking_number
 * @property string|null $carrier_api_id
 * @property string|null $carrier_code
 * @property string|null $status
 * @property string|null $track_info
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Status query()
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereCarrierApiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereCarrierCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereFulfillmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereTrackInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Status whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Status extends Model
{
    //
}
