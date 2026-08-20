<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ApiStatistics
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $order_id
 * @property int|null $fulfillment_id
 * @property int|null $request_count
 * @property string|null $shipment_status
 * @property string|null $message
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics query()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereFulfillmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereRequestCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereShipmentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiStatistics whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ApiStatistics extends Model
{
    //
}
