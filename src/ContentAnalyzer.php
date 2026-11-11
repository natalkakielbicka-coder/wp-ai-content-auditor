<?php

namespace WPAuditor;

if (!defined('ABSPATH')) {
    exit;
}

class ContentAnalyzer extends BaseAnalyzer implements AnalyzerInterface {

    protected function normalize_content(string $content): string {

        $content = str_replace("\u{00A0}", ' ', $content);

        return parent::normalize_content($content);
    }

    public function is_long_content(string $content): bool {
        return $this->count_words($content) >= 300;
    }

    public function has_content(string $content): bool {
        return $this->count_words($content) >= 1;
    }
}
