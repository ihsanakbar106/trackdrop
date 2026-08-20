<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GeneralEmail
 *
 * @property int $id
 * @property string|null $email_type
 * @property string|null $title
 * @property string|null $description
 * @property string|null $data
 * @property int|null $active_status
 * @property int|null $priority
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail whereEmailType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralEmail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralEmail extends Model
{
    use HasFactory;
}
