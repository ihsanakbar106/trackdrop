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
