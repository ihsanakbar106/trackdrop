<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SmsNotification
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $notification_id
 * @property string|null $sms_type
 * @property string|null $title
 * @property string|null $description
 * @property string|null $data
 * @property int|null $active_status
 * @property int|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereSmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SmsNotification extends Model
{
    use HasFactory;
}
