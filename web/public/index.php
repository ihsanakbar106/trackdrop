<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Early Shopify webhook ACK (before Laravel boot)
|--------------------------------------------------------------------------
|
| Shopify times out at ~5s. We ACK first, persist payload + HMAC to inbox,
| then boot Laravel and enqueue. Strongest HTTP-delivery pattern on PHP-FPM.
|
*/

$shopifyWebhookTopic = null;
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
// Normalize (some hosts prefix /index.php).
$requestPath = preg_replace('#/index\.php#', '', $requestPath) ?: $requestPath;

if ($requestMethod === 'POST' && preg_match(
    '#/api/webhooks/(order-create|order-update|product-create|product-update|product-delete|fulfillment-create|fulfillment-update|collection-create|collection-update|app-uninstall)/?$#',
    $requestPath,
    $webhookMatches
)) {
    $shopifyWebhookTopic = $webhookMatches[1];
}

if ($shopifyWebhookTopic !== null) {
    $webhookBody = file_get_contents('php://input');
    if ($webhookBody === false) {
        $webhookBody = '';
    }
    $webhookShop = (string) ($_SERVER['HTTP_X_SHOPIFY_SHOP_DOMAIN'] ?? '');
    $webhookHmac = (string) ($_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] ?? '');

    ignore_user_abort(true);

    // 1) ACK Shopify immediately — before Composer/Laravel boot.
    $ack = '{"status":"ok"}';
    http_response_code(200);
    header('Content-Type: application/json; charset=UTF-8');
    header('Connection: close');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Content-Length: ' . strlen($ack));
    echo $ack;

    if (function_exists('fastcgi_finish_request')) {
        @fastcgi_finish_request();
    } elseif (function_exists('litespeed_finish_request')) {
        @litespeed_finish_request();
    } else {
        while (ob_get_level() > 0) {
            @ob_end_flush();
        }
        @flush();
    }

    $envelope = [
        'topic' => $shopifyWebhookTopic,
        'shop' => $webhookShop,
        'body_b64' => base64_encode($webhookBody),
        'hmac' => $webhookHmac,
        'received_at' => gmdate('c'),
    ];

    // 2) Durable inbox (survives bootstrap/queue failure after ACK).
    $inboxDir = __DIR__ . '/../storage/app/shopify_webhook_inbox';
    if (!is_dir($inboxDir)) {
        @mkdir($inboxDir, 0775, true);
    }
    try {
        $inboxName = gmdate('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.json';
    } catch (Throwable $e) {
        $inboxName = gmdate('YmdHis') . '_' . str_replace('.', '', uniqid('', true)) . '.json';
    }
    $inboxFile = $inboxDir . '/' . $inboxName;
    $encoded = json_encode($envelope, JSON_INVALID_UTF8_SUBSTITUTE);
    $wroteInbox = ($encoded !== false) && (@file_put_contents($inboxFile, $encoded) !== false);

    // 3) Boot Laravel and enqueue.
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    try {
        if ($wroteInbox) {
            App\Support\ShopifyWebhookInbox::dispatchFile($inboxFile);
        } else {
            // Disk write failed — still enqueue from memory so webhook is not lost.
            App\Support\ShopifyWebhookInbox::dispatchEnvelope((object) $envelope);
        }
    } catch (Throwable $e) {
        // Inbox file (if written) kept for shopify:process-webhook-inbox.
    }

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
