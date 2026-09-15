<?php

namespace App\Support;

use App\Models\ErrorMessage;
use Illuminate\Http\JsonResponse;

/**
 * Fallback early ACK when the Laravel router handles a Shopify webhook
 * (public/index.php early path is preferred and runs first on Cloudways).
 */
final class ShopifyWebhookAck
{
    public static function then(callable $work): JsonResponse
    {
        ignore_user_abort(true);

        $json = '{"status":"ok"}';
        if (!headers_sent()) {
            http_response_code(200);
            header('Content-Type: application/json; charset=UTF-8');
            header('Connection: close');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Content-Length: ' . strlen($json));
            echo $json;
        }

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

        try {
            $work();
        } catch (\Throwable $e) {
            try {
                $log = new ErrorMessage();
                $log->message = 'Shopify webhook after-ack: ' . $e->getMessage() . ' line:' . $e->getLine();
                $log->save();
            } catch (\Throwable $ignored) {
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
