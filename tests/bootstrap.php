<?php

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/../');

require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('strip_shortcodes')) {
    function strip_shortcodes(string $content): string {
        return $content;
    }
}

if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags(string $content): string {
        return strip_tags($content);
    }
}
