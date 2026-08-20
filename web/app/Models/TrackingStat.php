<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TrackingStat
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $order_id
 * @property int|null $shopify_order_id
 * @property int|null $fulfillment_id
 * @property int|null $shopify_fulfillment_id
 * @property int|null $count
 * @property string|null $status
 * @property string|null $ip_address
 * @property string|null $browser
 * @property string|null $country
 * @property string|null $city
 * @property string|null $state
 * @property string|null $operating_system
 * @property string|null $device
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat query()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereBrowser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereDevice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereFulfillmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereOperatingSystem($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereShopifyFulfillmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereShopifyOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingStat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TrackingStat extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
