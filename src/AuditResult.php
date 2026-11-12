<?php
namespace WPAuditor;

if (!defined('ABSPATH')) {
    exit;
}

class AuditResult {

    public function __construct(
        public readonly int $post_id,
        public readonly string $title,
        public readonly int $word_count,
        public readonly bool $has_thumbnail,
        public readonly array $issues
    ) {
    }

    public function has_issues(): bool {
        return count($this->issues) > 0;
    }

    public function get_issues_count(): int {
        return count($this->issues);
    }
}
