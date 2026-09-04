<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ApiSetting
 *
 * @property int $id
 * @property string|null $api_name
 * @property string|null $api_key
 * @property string|null $customer_code
 * @property int|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting whereApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting whereApiName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ApiSetting extends Model
{
    //
}
