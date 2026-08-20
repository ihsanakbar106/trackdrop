<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EmailLog
 *
 * @property int $id
 * @property int|null $session_id
 * @property int|null $order_id
 * @property int|null $fulfillment_id
 * @property string|null $email_status
 * @property string|null $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereEmailStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereFulfillmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailLog extends Model
{
    use HasFactory;
}
