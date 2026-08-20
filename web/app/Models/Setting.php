<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Setting
 *
 * @property int $id
 * @property int|null $session_id
 * @property string|null $sender_name
 * @property string|null $sender_email
 * @property string|null $merchant_email
 * @property string|null $from_sms_number
 * @property string|null $slack_client_id
 * @property string|null $slack_secret_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereFromSmsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereMerchantEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSenderEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSenderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSlackClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereSlackSecretId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Setting extends Model
{
    use HasFactory;
}
