<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Plan
 *
 * @property int $id
 * @property int|null $type
 * @property string|null $name
 * @property string|null $price
 * @property int|null $priority
 * @property string|null $capped_amount
 * @property string|null $terms
 * @property int|null $trial_days
 * @property int $test
 * @property int $on_install
 * @property int|null $sort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereCappedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereOnInstall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereTrialDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Plan extends Model
{
    use HasFactory;
}
