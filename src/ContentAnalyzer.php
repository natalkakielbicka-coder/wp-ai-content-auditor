<?php

namespace WPAuditor;

if (!defined('ABSPATH')) {
    exit;
}

class ContentAnalyzer {

    public function count_words(string $content): int {

        $content = strip_shortcodes($content);

        $content = wp_strip_all_tags($content);

        $content = trim($content);

        if ($content === '') {
            return 0;
        }

        $words = preg_split('/\s+/u', $content);

        if ($words === false) {
            return 0;
        }

        return count($words);
    }

    public function is_long_content(string $content): bool {
        return $this->count_words($content) >= 300;
    }
}
