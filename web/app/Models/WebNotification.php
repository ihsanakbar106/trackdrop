<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\WebNotification
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $notification_id
 * @property string|null $notification_type
 * @property string|null $title
 * @property string|null $description
 * @property string|null $data
 * @property string|null $logo
 * @property int|null $active_status
 * @property int|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereNotificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WebNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WebNotification extends Model
{
    use HasFactory;
}
