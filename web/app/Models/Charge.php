<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Charge
 *
 * @property int $id
 * @property int|null $charge_id
 * @property int|null $session_id
 * @property int|null $plan_id
 * @property int|null $reference_charge
 * @property string|null $status
 * @property string|null $name
 * @property string|null $terms
 * @property string|null $type
 * @property float|null $price
 * @property string|null $interval
 * @property string|null $capped_amount
 * @property string|null $description
 * @property int|null $trial_days
 * @property int $test
 * @property string|null $billing_on
 * @property string|null $activated_on
 * @property string|null $trial_ends_on
 * @property string|null $cancelled_on
 * @property string|null $expires_on
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Charge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Charge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Charge query()
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereActivatedOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereBillingOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereCancelledOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereCappedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereChargeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereExpiresOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereReferenceCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereTrialDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereTrialEndsOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Charge whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Charge extends Model
{
    use HasFactory;
}
