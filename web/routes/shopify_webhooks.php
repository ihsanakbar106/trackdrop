<?php

/**
 * Fallback Shopify webhook routes (if a request bypasses public/index.php early ACK).
 * Normal Cloudways traffic is handled in public/index.php BEFORE Laravel boots.
 */

use App\Support\ShopifyWebhookAck;
use App\Support\ShopifyWebhookInbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$ackInbox = function (string $topic) {
    return function (Request $request) use ($topic) {
        $shop = (string) $request->header('x-shopify-shop-domain', '');
        $body = $request->getContent();
        $hmac = (string) $request->header('x-shopify-hmac-sha256', '');

        return ShopifyWebhookAck::then(function () use ($topic, $shop, $body, $hmac) {
            ShopifyWebhookInbox::dispatchEnvelope((object) [
                'topic' => $topic,
                'shop' => $shop,
                'body_b64' => base64_encode($body),
                'hmac' => $hmac,
            ]);
        });
    };
};

Route::post('/webhooks/app-uninstall', $ackInbox('app-uninstall'));
Route::post('/webhooks/order-create', $ackInbox('order-create'));
Route::post('/webhooks/order-update', $ackInbox('order-update'));
Route::post('/webhooks/product-create', $ackInbox('product-create'));
Route::post('/webhooks/product-update', $ackInbox('product-update'));
Route::post('/webhooks/product-delete', $ackInbox('product-delete'));
Route::post('/webhooks/fulfillment-create', $ackInbox('fulfillment-create'));
Route::post('/webhooks/fulfillment-update', $ackInbox('fulfillment-update'));
Route::post('/webhooks/collection-create', $ackInbox('collection-create'));
Route::post('/webhooks/collection-update', $ackInbox('collection-update'));
