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
        public readonly array $issues,
        public readonly int $internal_links = 0,
        public readonly int $external_links = 0,
        public readonly array $images = [],
        public readonly array $seo_meta = [],
        public readonly array $link_stats = []
    ) {
    }

    public function has_issues(): bool {
        return count($this->issues) > 0;
    }

    public function get_issues_count(): int {
        return count($this->issues);
    }

    public function get_score(): int {

        $score = 100;

        foreach ($this->issues as $issue) {
            $score -= $issue['penalty'];
        }

        return max(0, $score);
    }

    public function get_issue_summary(): array {

        $summary = [];

        foreach ($this->issues as $issue) {

            $category = $issue['category'];

            if (!isset($summary[$category])) {
                $summary[$category] = 0;
            }

            $summary[$category]++;
        }

        return $summary;
    }

    public function get_issue_count(): int {
        return count($this->issues);
    }
}