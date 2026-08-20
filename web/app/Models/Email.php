<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Email
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $notification_id
 * @property string|null $email_type
 * @property string|null $title
 * @property string|null $description
 * @property string|null $data
 * @property string|null $logo
 * @property int|null $active_status
 * @property int|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Email newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Email newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Email query()
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereEmailType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Email whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Email extends Model
{
    use HasFactory;
}
