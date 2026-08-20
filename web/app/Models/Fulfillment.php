<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Fulfillment
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $order_id
 * @property int|null $shopify_order_id
 * @property int|null $fulfillment_id
 * @property int|null $location_id
 * @property string|null $shipment_id
 * @property string|null $name
 * @property string|null $tracking_company
 * @property string|null $tracking_number
 * @property string|null $fulfillment_status
 * @property string|null $status
 * @property string|null $service
 * @property string|null $shipment_status
 * @property string|null $mobile_number
 * @property string|null $tracking_url
 * @property string|null $country
 * @property string|null $first_date
 * @property string|null $last_date
 * @property string|null $tracking_complete_info
 * @property string|null $track_info
 * @property string|null $line_items
 * @property string|null $shipment_register_time
 * @property string|null $shipment_last_event
 * @property string|null $original_country
 * @property string|null $shipment_last_update_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Carrier|null $carrier_code_base
 * @property-read \App\Models\Carrier|null $carrier_name_base
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EmailLog> $email_logs
 * @property-read int|null $email_logs_count
 * @property-read mixed $details
 * @property-read mixed $status_bg_color
 * @property-read mixed $status_mut
 * @property-read mixed $status_text_color
 * @property-read mixed $tracking_company_mapping
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereFirstDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereFulfillmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereFulfillmentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereLastDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereLineItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereMobileNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereOriginalCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereService($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereShipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereShipmentLastEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereShipmentLastUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereShipmentRegisterTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereShipmentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereShopifyOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereTrackInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereTrackingCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereTrackingCompleteInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereTrackingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Fulfillment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Fulfillment extends Model
{
    protected $dates = [
        'start_date', 'end_date',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class, 'shopify_order_id', 'shopify_order_id');
    }

    public function carrier_name_base()
    {
        return $this->belongsTo(Carrier::class, 'tracking_company', 'name');
    }
    public function carrier_code_base()
    {
        return $this->belongsTo(Carrier::class, 'tracking_company', 'code');
    }

    public function email_logs()
    {
        return $this->hasMany(EmailLog::class);
    }

//    public function getShippingStatusAttribute($value){
//        dd($this->shipment_status);
//        $status = Status::where('status',$this->shipment_status)->first();
//        if($status != null){
//            return $status->status;
//        }
//        return null;
//    }
    public function getStatusBgColorAttribute()
    {

        $status = ReportStatus::where('status', $this->shipment_status)->first();
        if ($status != null) {
            return $status->background;
        }
        return null;
    }
    public function getStatusTextColorAttribute()
    {

        $status = ReportStatus::where('status', $this->shipment_status)->first();
        if ($status != null) {
            return $status->colors;
        }
        return null;
    }

    public function getStatusMutAttribute()
    {
        $status = ReportStatus::where('status', $this->shipment_status)->first();
        if ($status != null) {
            return $status;
        }
        return null;
    }

    public function getTrackingCompanyMappingAttribute($value)
    {
        $carrier_mapping = CarrierMapping::where('user_id', Auth::user()->id)->where('shopify_carrier', $this->tracking_company)->first();
        if ($carrier_mapping != null) {
            return isset($carrier_mapping->actual_carrier) ? $carrier_mapping->actual_carrier : $this->tracking_company;
        } else {
            return $this->tracking_company;
        }
    }

    public function getDetailsAttribute($value)
    {
        return str_replace(',', ', ', $value);
    }
}
