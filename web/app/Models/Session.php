<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Session
 *
 * @property int $id
 * @property string $session_id
 * @property int|null $plan_id
 * @property int|null $meta_field_id
 * @property string|null $survey_limit
 * @property int|null $credits
 * @property int|null $used_credits
 * @property string|null $shopifyPlus
 * @property string|null $partnerDevelopment
 * @property int|null $shop_widgets_metafield_id
 * @property int|null $warrenty_insurance_metafield_id
 * @property int|null $custom_checkout_metafield_id
 * @property int|null $gift_wrap_metafield_id
 * @property int|null $delivery_date_metafield_id
 * @property int|null $address_validation_metafield_id
 * @property int|null $age_verif_metafield_id
 * @property string $shop
 * @property int $is_online
 * @property string $state
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $scope
 * @property string|null $access_token
 * @property string|null $expires_at
 * @property int|null $user_id
 * @property string|null $user_first_name
 * @property string|null $user_last_name
 * @property string|null $user_email
 * @property int|null $user_email_verified
 * @property int|null $account_owner
 * @property string|null $locale
 * @property int|null $collaborator
 * @property int|null $dropshipping_mode
 * @method static \Illuminate\Database\Eloquent\Builder|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereAccountOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereAddressValidationMetafieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereAgeVerifMetafieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereCollaborator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereCustomCheckoutMetafieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereDeliveryDateMetafieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereDropshippingMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereGiftWrapMetafieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereMetaFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session wherePartnerDevelopment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereShop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereShopWidgetsMetafieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereShopifyPlus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereSurveyLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUsedCredits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserEmailVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUserLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereWarrentyInsuranceMetafieldId($value)
 * @mixin \Eloquent
 */
class Session extends Model
{
    use HasFactory;

    protected $hidden = ['access_token', 'refresh_token'];
}
