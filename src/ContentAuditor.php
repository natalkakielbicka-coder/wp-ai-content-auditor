<?php
namespace WPAuditor;

use InvalidArgumentException;

if (!defined('ABSPATH')) {
    exit;
}

class ContentAuditor {

    public function __construct(
        private AnalyzerInterface $analyzer
    ) {
    }

    public function audit(int $post_id): AuditResult {

        $post = get_post($post_id);

        if (!$post instanceof \WP_Post) {
            throw new InvalidArgumentException(
                'Nie znaleziono wskazanego wpisu.'
            );
        }

        if (!in_array($post->post_type, ['post', 'page'], true)) {
            throw new InvalidArgumentException(
                'Nieobsługiwany typ treści.'
            );
        }

        $title = trim($post->post_title);
        $content = $post->post_content;

        $word_count = $this->analyzer->count_words($content);
        $has_thumbnail = has_post_thumbnail($post_id);

        $issues = [];

        if ($title === '') {
            $issues[] = [
                'category' => 'Tytuł',
                'message' => 'Brak tytułu wpisu.',
                'penalty' => 30,
            ];
        } elseif (mb_strlen($title) < 15) {
            $issues[] = [
                'category' => 'Tytuł',
                'message' => 'Tytuł jest bardzo krótki.',
                'penalty' => 20,
            ];
        }

        if ($word_count < 300) {
            $issues[] = [
                'category' => 'Treść',
                'message' => 'Treść zawiera mniej niż 300 słów.',
                'penalty' => 30,
            ];
        }

        if (!$has_thumbnail) {
            $issues[] = [
                'category' => 'Obrazy',
                'message' => 'Brak obrazka wyróżniającego.',
                'penalty' => 20,
            ];
        }

        return new AuditResult(
            $post_id,
            $title,
            $word_count,
            $has_thumbnail,
            $issues
        );
    }
}