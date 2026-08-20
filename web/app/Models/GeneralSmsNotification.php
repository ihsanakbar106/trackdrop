<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GeneralSmsNotification
 *
 * @property int $id
 * @property string|null $sms_type
 * @property string|null $title
 * @property string|null $description
 * @property string|null $data
 * @property int|null $active_status
 * @property int|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification whereSmsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSmsNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralSmsNotification extends Model
{
    use HasFactory;
}
