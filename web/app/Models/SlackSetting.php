<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SlackSetting
 *
 * @property int $id
 * @property int|null $session_id
 * @property string|null $code
 * @property string|null $slack_access_token
 * @property string|null $slack_refresh_token
 * @property string|null $slack_webhook_url
 * @property string|null $channel_id
 * @property string|null $channel_name
 * @property string|null $klaviyo_api_keys
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereChannelName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereKlaviyoApiKeys($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereSlackAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereSlackRefreshToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereSlackWebhookUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SlackSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SlackSetting extends Model
{
    use HasFactory;
}
