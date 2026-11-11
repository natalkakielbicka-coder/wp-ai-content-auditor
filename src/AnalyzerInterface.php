<?php

namespace WPAuditor;

if (!defined('ABSPATH')) {
    exit;
}

interface AnalyzerInterface {

    public function count_words(string $content): int;

    public function is_long_content(string $content): bool;

    public function has_content(string $content): bool;
}
