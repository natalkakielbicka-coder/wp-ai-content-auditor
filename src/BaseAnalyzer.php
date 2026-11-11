<?php

namespace WPAuditor;

if (!defined('ABSPATH')) {
    exit;
}

class BaseAnalyzer {

    protected function normalize_content(string $content): string {

        $content = strip_shortcodes($content);

        $content = wp_strip_all_tags($content);

        return trim($content);
    }

    public function count_words(string $content): int {

        $content = $this->normalize_content($content);

        if ($content === '') {
            return 0;
        }

        $words = preg_split('/\s+/u', $content);

        if ($words === false) {
            return 0;
        }

        return count($words);
    }
}
