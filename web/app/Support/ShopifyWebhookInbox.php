<?php

namespace App\Support;

use App\Jobs\AllOrderCreateUpdateJob;
use App\Jobs\collectionCreateUpdateJob;
use App\Jobs\fulfillmentCreateUpdateJob;
use App\Jobs\ProductDeleteWebhookJob;
use App\Jobs\productCreateUpdateJob;
use App\Jobs\unistallAppJob;
use App\Models\ErrorMessage;
use App\Models\Session;
use Illuminate\Support\Facades\DB;

/**
 * Durable Shopify webhook inbox — ACK + file write in public/index.php (no Laravel boot).
 * Cron `shopify:process-webhook-inbox` enqueues jobs then deletes files.
 */
final class ShopifyWebhookInbox
{
    public static function directory(): string
    {
        return storage_path('app/shopify_webhook_inbox');
    }

    /**
     * Persist envelope to inbox only (no job dispatch). Safe after ACK.
     *
     * @param  array{topic:string,shop?:string,body_b64?:string,body?:string,hmac?:string}  $envelope
     */
    public static function persistEnvelope(array $envelope): bool
    {
        $dir = self::directory();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $envelope['received_at'] = $envelope['received_at'] ?? gmdate('c');

        try {
            $name = gmdate('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.json';
        } catch (\Throwable $e) {
            $name = gmdate('YmdHis') . '_' . str_replace('.', '', uniqid('', true)) . '.json';
        }

        $encoded = json_encode($envelope, JSON_INVALID_UTF8_SUBSTITUTE);
        if ($encoded === false) {
            return false;
        }

        return @file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $encoded) !== false;
    }

    /**
     * Verify Shopify HMAC after Laravel boot.
     * Empty SHOPIFY_API_SECRET → allow (avoid blackholing if env misconfigured).
     */
    public static function verifyHmac(string $rawBody, string $hmacHeader): bool
    {
        $secret = (string) env('SHOPIFY_API_SECRET', '');
        if ($secret === '') {
            return true;
        }
        if ($hmacHeader === '') {
            return false;
        }

        $calculated = base64_encode(hash_hmac('sha256', $rawBody, $secret, true));

        return hash_equals($calculated, $hmacHeader);
    }

    /**
     * @param  object  $envelope  {topic, shop, body|body_b64, hmac?}
     */
    public static function dispatchEnvelope(object $envelope): void
    {
        $topic = (string) ($envelope->topic ?? '');
        $shop = (string) ($envelope->shop ?? '');
        $hmac = (string) ($envelope->hmac ?? '');

        // Prefer base64 body (exact bytes for HMAC). Fallback to legacy plain body.
        if (!empty($envelope->body_b64)) {
            $decoded = base64_decode((string) $envelope->body_b64, true);
            $body = $decoded === false ? '' : $decoded;
        } else {
            $body = (string) ($envelope->body ?? '');
        }

        if (!self::verifyHmac($body, $hmac)) {
            try {
                $log = new ErrorMessage();
                $log->message = 'ShopifyWebhookInbox HMAC rejected topic=' . $topic . ' shop=' . $shop;
                $log->save();
            } catch (\Throwable $ignored) {
            }

            return;
        }

        $payload = $body !== '' ? json_decode($body) : null;

        switch ($topic) {
            case 'order-create':
            case 'order-update':
                if ($shop !== '' && $payload) {
                    AllOrderCreateUpdateJob::dispatch($payload, $shop)->onConnection('database');
                }
                break;

            case 'product-create':
            case 'product-update':
                if ($shop !== '' && is_object($payload) && isset($payload->id)) {
                    productCreateUpdateJob::dispatch($shop, $payload->id)->onConnection('database');
                }
                break;

            case 'product-delete':
                if (is_object($payload) && isset($payload->id)) {
                    ProductDeleteWebhookJob::dispatch($payload->id)->onConnection('database');
                }
                break;

            case 'fulfillment-create':
            case 'fulfillment-update':
                if ($shop !== '' && $payload) {
                    fulfillmentCreateUpdateJob::dispatch($payload, $shop)->onConnection('database');
                }
                break;

            case 'collection-create':
            case 'collection-update':
                if ($shop !== '' && is_object($payload) && isset($payload->id)) {
                    collectionCreateUpdateJob::dispatch($shop, $payload->id)->onConnection('database');
                }
                break;

            case 'app-uninstall':
                if ($shop !== '') {
                    $session = Session::where('shop', $shop)->first();
                    if ($session) {
                        dispatch(new unistallAppJob($session->id))->onConnection('database');
                        DB::table('sessions')->where('shop', $shop)->delete();
                    }
                }
                break;
        }
    }

    public static function dispatchFile(string $path): bool
    {
        $dir = realpath(self::directory());
        $real = realpath($path);
        if ($dir === false || $real === false || !is_file($real)) {
            return false;
        }
        if (strpos($real, $dir . DIRECTORY_SEPARATOR) !== 0) {
            return false;
        }

        try {
            $envelope = json_decode((string) file_get_contents($real));
            if (!is_object($envelope) || empty($envelope->topic)) {
                @unlink($real);

                return false;
            }

            self::dispatchEnvelope($envelope);
            @unlink($real);

            return true;
        } catch (\Throwable $e) {
            try {
                $log = new ErrorMessage();
                $log->message = 'ShopifyWebhookInbox: ' . $e->getMessage() . ' file:' . basename($path);
                $log->save();
            } catch (\Throwable $ignored) {
            }

            return false;
        }
    }

    /** Process leftover inbox files (crash / DB down after ACK). */
    public static function processPending(int $limit = 50): int
    {
        $dir = self::directory();
        if (!is_dir($dir)) {
            return 0;
        }

        $files = glob($dir . '/*.json') ?: [];
        sort($files);
        $done = 0;
        foreach (array_slice($files, 0, $limit) as $file) {
            $claimed = $file . '.processing';
            if (!@rename($file, $claimed)) {
                continue;
            }
            if (self::dispatchFile($claimed)) {
                $done++;
            } elseif (is_file($claimed)) {
                @rename($claimed, $file);
            }
        }

        return $done;
    }
}
