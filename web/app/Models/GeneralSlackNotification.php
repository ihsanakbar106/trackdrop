<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GeneralSlackNotification
 *
 * @property int $id
 * @property string|null $slack_type
 * @property string|null $title
 * @property string|null $description
 * @property string|null $data
 * @property int|null $active_status
 * @property int|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification whereSlackType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralSlackNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralSlackNotification extends Model
{
    use HasFactory;
}
