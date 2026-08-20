<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TrackingPage
 *
 * @property int $id
 * @property int|null $session_id
 * @property string|null $page_name
 * @property string|null $page_handle
 * @property string|null $feature_image
 * @property string|null $theme_type
 * @property int|null $shopify_page_id
 * @property string|null $shopify_page_template
 * @property string|null $edit_page_url
 * @property string|null $editor_url
 * @property string|null $permalink
 * @property string|null $uuid
 * @property string|null $store_uuid
 * @property string|null $page_code
 * @property string|null $page_url
 * @property int|null $automatic_transfer_on_new_theme_publish
 * @property int|null $active_status
 * @property string|null $data
 * @property string|null $tracking_page_published_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereActiveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereAutomaticTransferOnNewThemePublish($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereEditPageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereEditorUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereFeatureImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage wherePageCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage wherePageHandle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage wherePageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage wherePageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage wherePermalink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereShopifyPageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereShopifyPageTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereStoreUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereThemeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereTrackingPagePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TrackingPage whereUuid($value)
 * @mixin \Eloquent
 */
class TrackingPage extends Model
{
    use HasFactory;
}
