<?php

if (!function_exists('generateSlug')) {
    function generateSlug($string) {
        // Convert to lowercase
        $string = strtolower($string);

        // Replace non-alphanumeric characters with a space
        $string = preg_replace('/[^a-z0-9\s-]/', ' ', $string);

        // Replace multiple spaces or hyphens with a single space
        $string = preg_replace('/[\s-]+/', ' ', $string);

        // Trim spaces from the beginning and end
        $string = trim($string);

        // Replace spaces with hyphens
        $string = str_replace(' ', '-', $string);

        return $string;
    }
}

if (!function_exists('app_public_url')) {
    /**
     * Public app origin for tracking-page JS.
     * Local: Shopify CLI HOST (tunnel). Production: APP_URL. No env swap on deploy.
     */
    function app_public_url()
    {
        if (env('APP_ENV') === 'local') {
            $raw = env('HOST') ?: env('APP_URL') ?: (request()->getSchemeAndHttpHost() ?? 'http://127.0.0.1');
        } else {
            $raw = env('APP_URL') ?: env('HOST');
        }

        $raw = rtrim((string) $raw, '/');
        if ($raw === '') {
            return 'https://app.theautotrack.com';
        }

        if (!preg_match('#^https?://#i', $raw)) {
            $raw = 'https://' . $raw;
        }

        return $raw;
    }
}

if (!function_exists('app_proxy_subpath')) {
    /**
     * Storefront app-proxy subpath.
     * Prod default: track (never breaks live Modern URL).
     * Local default: track-dev (avoids colliding with production proxy).
     * Override anytime: APP_PROXY_SUBPATH in .env
     */
    function app_proxy_subpath(): string
    {
        $configured = trim((string) env('APP_PROXY_SUBPATH', ''));
        if ($configured !== '') {
            return trim($configured, '/');
        }

        // Production-safe: only local uses track-dev when env is unset.
        if (env('APP_ENV') === 'local') {
            return 'track-dev';
        }

        return 'track';
    }
}

if (!function_exists('app_proxy_modern_path')) {
    /** e.g. /a/track/order or /a/track-dev/order */
    function app_proxy_modern_path(): string
    {
        return '/a/' . app_proxy_subpath() . '/order';
    }
}

if (!function_exists('app_proxy_modern_url')) {
    function app_proxy_modern_url(string $shop, ?string $trackingNumber = null): string
    {
        $shop = preg_replace('#^https?://#i', '', rtrim($shop, '/'));
        $url = 'https://' . $shop . app_proxy_modern_path();
        if ($trackingNumber !== null && $trackingNumber !== '') {
            $url .= '?tracking_number=' . urlencode($trackingNumber);
        }

        return $url;
    }
}

if (!function_exists('billing_free_shops')) {
    /**
     * Shops that get full app access with no Shopify billing charges.
     * Env: BILLING_FREE_SHOPS=goom.myshopify.com,other.myshopify.com
     */
    function billing_free_shops(): array
    {
        $raw = (string) env('BILLING_FREE_SHOPS', '');
        if (trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map(function ($shop) {
            $shop = strtolower(trim($shop));
            if ($shop === '') {
                return null;
            }
            if (!str_ends_with($shop, '.myshopify.com')) {
                $shop .= '.myshopify.com';
            }
            return $shop;
        }, explode(',', $raw))));
    }
}

if (!function_exists('is_billing_free_shop')) {
    function is_billing_free_shop($shop): bool
    {
        if (!$shop) {
            return false;
        }
        $shop = strtolower(trim((string) $shop));
        if (!str_ends_with($shop, '.myshopify.com')) {
            $shop .= '.myshopify.com';
        }

        return in_array($shop, billing_free_shops(), true);
    }
}
