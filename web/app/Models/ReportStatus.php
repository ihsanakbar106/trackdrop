<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ReportStatus
 *
 * @property int $id
 * @property int|null $carrier_service_id
 * @property string|null $status
 * @property string|null $public_text
 * @property string|null $background
 * @property string|null $colors
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus whereBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus whereCarrierServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus whereColors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus wherePublicText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ReportStatus extends Model
{
    //
}
