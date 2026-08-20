<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EmailCount
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCount query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailCount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailCount extends Model
{
    use HasFactory;
}
