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
            $isLocalHost = in_array(strtolower(explode(':', $raw)[0]), ['127.0.0.1', 'localhost'], true);
            $raw = ($isLocalHost ? 'http://' : 'https://') . $raw;
        }

        return $raw;
    }
}
