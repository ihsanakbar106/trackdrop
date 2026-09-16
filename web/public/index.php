<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Early Shopify webhook ACK (before Laravel boot)
|--------------------------------------------------------------------------
|
| Shopify times out at ~5s and deletes subscriptions after repeated failures.
| We ACK + write inbox, then EXIT — do NOT boot Laravel in this request.
| Booting Laravel after ACK keeps PHP-FPM busy under webhook bursts → 502 /
| "No response from app". Cron `shopify:process-webhook-inbox` drains inbox.
|
*/

$shopifyWebhookTopic = null;
$shopifyGdprAckOnly = false;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$requestPath = preg_replace('#/index\.php#', '', $requestPath) ?: $requestPath;
$requestPath = '/' . trim(preg_replace('#/+#', '/', $requestPath), '/');

$webhookTopicPattern = 'order-create|order-update|product-create|product-update|product-delete|fulfillment-create|fulfillment-update|collection-create|collection-update|app-uninstall';
$gdprTopicPattern = 'customers-data-request|customers-redact|shop-redact';

if ($requestMethod === 'POST' && preg_match(
    '#(?:^|/)api/webhooks/(' . $webhookTopicPattern . ')/?$#i',
    $requestPath,
    $webhookMatches
)) {
    $shopifyWebhookTopic = strtolower($webhookMatches[1]);
} elseif ($requestMethod === 'POST' && preg_match(
    '#(?:^|/)api/webhooks/(' . $gdprTopicPattern . ')/?$#i',
    $requestPath
)) {
    $shopifyGdprAckOnly = true;
}

if ($shopifyWebhookTopic !== null || $shopifyGdprAckOnly) {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    $webhookBody = '';
    try {
        $raw = file_get_contents('php://input');
        if ($raw !== false) {
            $webhookBody = $raw;
        }
    } catch (Throwable $e) {
        $webhookBody = '';
    }

    $webhookShop = (string) ($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] ?? '');
    $webhookHmac = (string) ($_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '');

    ignore_user_abort(true);

    // 1) ACK Shopify immediately — before Composer/Laravel boot.
    $ack = '{"status":"ok"}';
    if (!headers_sent()) {
        http_response_code(200);
        header('Content-Type: application/json; charset=UTF-8');
        header('Connection: close');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Content-Length: ' . strlen($ack));
    }
    echo $ack;

    if (function_exists('fastcgi_finish_request')) {
        @fastcgi_finish_request();
    } elseif (function_exists('litespeed_finish_request')) {
        @litespeed_finish_request();
    } else {
        @flush();
    }

    if ($shopifyGdprAckOnly) {
        exit(0);
    }

    // 2) Durable inbox only — cron enqueues jobs.
    try {
        $envelope = [
            'topic' => $shopifyWebhookTopic,
            'shop' => $webhookShop,
            'body_b64' => base64_encode($webhookBody),
            'hmac' => $webhookHmac,
            'received_at' => gmdate('c'),
        ];

        $inboxDir = __DIR__ . '/../storage/app/shopify_webhook_inbox';
        if (!is_dir($inboxDir)) {
            @mkdir($inboxDir, 0775, true);
        }
        try {
            $inboxName = gmdate('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.json';
        } catch (Throwable $e) {
            $inboxName = gmdate('YmdHis') . '_' . str_replace('.', '', uniqid('', true)) . '.json';
        }
        $encoded = json_encode($envelope, JSON_INVALID_UTF8_SUBSTITUTE);
        if ($encoded !== false) {
            @file_put_contents($inboxDir . '/' . $inboxName, $encoded, LOCK_EX);
        }
    } catch (Throwable $e) {
        // Already ACK'd — never rethrow.
    }

    // 3) Free PHP-FPM immediately. Never boot Laravel here.
    exit(0);
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
*/

if (file_exists(__DIR__ . '/../storage/framework/maintenance.php')) {
    require __DIR__ . '/../storage/framework/maintenance.php';
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
    $request = Request::capture()
))->send();

$kernel->terminate($request, $response);
