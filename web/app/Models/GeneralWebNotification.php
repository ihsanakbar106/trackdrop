<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GeneralWebNotification
 *
 * @property int $id
 * @property string|null $notification_type
 * @property string|null $title
 * @property string|null $description
 * @property string|null $data
 * @property int|null $active_status
 * @property int|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification whereNotificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralWebNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralWebNotification extends Model
{
    use HasFactory;
}
