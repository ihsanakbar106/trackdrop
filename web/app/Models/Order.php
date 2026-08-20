<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $shopify_order_id
 * @property int|null $checkout_id
 * @property int|null $location_id
 * @property int|null $shopify_fulfillment_order_id
 * @property string|null $name
 * @property string|null $customer_name
 * @property string|null $country
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $order_status_url
 * @property string|null $fulfillment_status
 * @property string|null $financial_status
 * @property string|null $customer
 * @property float|null $total_line_items_price
 * @property float|null $total_price
 * @property float|null $subtotal_price
 * @property string|null $currency
 * @property string|null $checkout_token
 * @property string|null $last_tracking_status_check_time
 * @property string|null $cancelled_at
 * @property string|null $processed_at
 * @property string|null $shipping_address
 * @property string|null $billing_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfillment> $fulfillments
 * @property-read int|null $fulfillments_count
 * @property-read mixed $status_mut
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\LineItem> $lineitems
 * @property-read int|null $lineitems_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApiStatistics> $request_counts
 * @property-read int|null $request_counts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TrackingStat> $tracking_stats
 * @property-read int|null $tracking_stats_count
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCheckoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCheckoutToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereFinancialStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereFulfillmentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereLastTrackingStatusCheckTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderStatusUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShopifyFulfillmentOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShopifyOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSubtotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalLineItemsPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    public $guarded = [];

    public function request_counts()
    {
        return $this->hasMany(ApiStatistics::class,'order_id','shopify_order_id');
    }

    public function fulfillments()
    {
        return $this->hasMany(Fulfillment::class,'shopify_order_id','shopify_order_id');
    }

    public function lineitems()
    {
        return $this->hasMany(LineItem::class,'shopify_order_id','shopify_order_id');
    }

    public function tracking_stats()
    {
        return $this->hasMany(TrackingStat::class,'shopify_order_id','shopify_order_id');
    }

    public function getStatusMutAttribute(){
        $status = ReportStatus::where('status',$this->shipment_status)->first();
        if($status != null){
            return $status;
        }
        return null;
    }
}
