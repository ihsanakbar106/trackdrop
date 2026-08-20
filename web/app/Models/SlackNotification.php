<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SlackNotification
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $notification_id
 * @property string|null $slack_type
 * @property string|null $title
 * @property string|null $description
 * @property string|null $data
 * @property int|null $active_status
 * @property int|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereSlackType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SlackNotification extends Model
{
    use HasFactory;
}
