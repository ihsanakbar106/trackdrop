<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ErrorMessage
 *
 * @property int $id
 * @property string|null $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorMessage whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ErrorMessage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ErrorMessage extends Model
{
    use HasFactory;
}
