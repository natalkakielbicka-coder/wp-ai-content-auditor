<?php
namespace WPAuditor\Admin;

use WPAuditor\AuditResult;

if (!defined('ABSPATH')) {
    exit;
}

class ResultRenderer {

    public function render(AuditResult $result): void {

        echo '<hr>';
        echo '<h2>Wynik audytu</h2>';

        $this->render_basic_info($result);
        $this->render_links($result);
        $this->render_images($result);
        $this->render_seo($result);
        $this->render_score($result);
        $this->render_issues($result);
    }

    private function render_basic_info(AuditResult $result): void {

        echo '<h3>Podstawowe informacje</h3>';

        echo '<p><strong>Tytuł:</strong> ';
        echo esc_html($result->title);
        echo '</p>';

        echo '<p><strong>Liczba słów:</strong> ';
        echo esc_html((string) $result->word_count);
        echo '</p>';

        echo '<p><strong>Obrazek wyróżniający:</strong> ';
        echo $result->has_thumbnail ? 'TAK' : 'NIE';
        echo '</p>';
    }

    private function render_links(AuditResult $result): void {

        echo '<h3>Linki</h3>';

        echo '<p><strong>Linki wewnętrzne:</strong> ';
        echo esc_html((string) $result->internal_links);
        echo '</p>';

        echo '<p><strong>Linki zewnętrzne:</strong> ';
        echo esc_html((string) $result->external_links);
        echo '</p>';

        if (empty($result->link_stats)) {
            return;
        }

        $stats = $result->link_stats;

        echo '<p><strong>Unikalne linki wewnętrzne:</strong> ';
        echo esc_html((string) $stats['internal_unique']);
        echo '</p>';

        echo '<p><strong>Powtórzenia linków wewnętrznych:</strong> ';
        echo esc_html((string) $stats['internal_repeated']);
        echo '</p>';

        echo '<p><strong>Unikalne linki zewnętrzne:</strong> ';
        echo esc_html((string) $stats['external_unique']);
        echo '</p>';

        echo '<p><strong>Powtórzenia linków zewnętrznych:</strong> ';
        echo esc_html((string) $stats['external_repeated']);
        echo '</p>';
    }

    private function render_images(AuditResult $result): void {

        if (empty($result->images)) {
            return;
        }

        $images = $result->images;

        echo '<h3>Analiza obrazków</h3>';

        $labels = [
            'total' => 'Wszystkie obrazki',
            'with_alt' => 'Z opisem ALT',
            'empty_alt' => 'Z pustym ALT',
            'missing_alt' => 'Bez atrybutu ALT',
            'whitespace_alt' => 'ALT zawierający tylko spacje',
        ];

        foreach ($labels as $key => $label) {

            echo '<p><strong>';
            echo esc_html($label);
            echo ':</strong> ';

            echo esc_html((string) ($images[$key] ?? 0));
            echo '</p>';
        }
    }

    private function render_seo(AuditResult $result): void {

        if (empty($result->seo_meta)) {
            return;
        }

        $meta = $result->seo_meta;

        echo '<hr>';
        echo '<h2>Metadane SEO</h2>';

        if (($meta['provider'] ?? 'none') !== 'yoast') {
            echo '<p>Yoast SEO nie jest aktywny.</p>';
            return;
        }

        $length_labels = [
            'short' => 'Tekst może być zbyt krótki.',
            'ok' => 'Długość mieści się w orientacyjnym zakresie.',
            'long' => 'Tekst może być zbyt długi.',
            'not_checked' => 'Nie oceniono długości.',
        ];

        echo '<p><strong>SEO Title:</strong> ';

        if (($meta['title_status'] ?? '') === 'custom') {
            echo esc_html($meta['title'] ?? '');
        } else {
            echo 'Brak indywidualnej wartości (szablon Yoast).';
        }

        echo '</p>';

        echo '<p><strong>Długość zapisanego tytułu:</strong> ';
        echo esc_html((string) ($meta['title_length'] ?? 0));
        echo ' znaków</p>';

        $title_status = $meta['title_length_status']
            ?? 'not_checked';

        echo '<p><strong>Ocena długości tytułu:</strong> ';
        echo esc_html(
            $length_labels[$title_status]
                ?? $length_labels['not_checked']
        );
        echo '</p>';

        echo '<p><strong>Meta Description:</strong> ';

        if (($meta['description_status'] ?? '') === 'custom') {
            echo esc_html($meta['description'] ?? '');
        } else {
            echo 'Brak indywidualnej wartości (możliwy fallback).';
        }

        echo '</p>';

        echo '<p><strong>Długość zapisanego opisu:</strong> ';
        echo esc_html((string) ($meta['description_length'] ?? 0));
        echo ' znaków</p>';

        $description_status = $meta['description_length_status']
            ?? 'not_checked';

        echo '<p><strong>Ocena długości opisu:</strong> ';
        echo esc_html(
            $length_labels[$description_status]
                ?? $length_labels['not_checked']
        );
        echo '</p>';
    }

    private function render_score(AuditResult $result): void {

        echo '<hr>';

        echo '<h2>Ocena treści: ';
        echo esc_html((string) $result->get_score());
        echo '/100</h2>';

        echo '<p><strong>Liczba problemów:</strong> ';
        echo esc_html((string) $result->get_issues_count());
        echo '</p>';
    }

    private function render_issues(AuditResult $result): void {

        $summary = $result->get_issue_summary();

        echo '<h3>Podsumowanie problemów</h3>';

        if (empty($summary)) {

            echo '<p>Nie wykryto problemów.</p>';

        } else {

            echo '<ul>';

            foreach ($summary as $category => $count) {

                echo '<li><strong>';
                echo esc_html($category);
                echo ':</strong> ';

                echo esc_html((string) $count);
                echo '</li>';
            }

            echo '</ul>';
        }

        if (!$result->has_issues()) {
            echo '<p>Nie wykryto problemów w podstawowym audycie.</p>';
            return;
        }

        echo '<h3>Wykryte problemy</h3>';
        echo '<ul>';

        foreach ($result->issues as $issue) {

            echo '<li><strong>';
            echo esc_html($issue['category']);
            echo ':</strong> ';

            echo esc_html($issue['message']);

            echo ' (-';
            echo esc_html((string) $issue['penalty']);
            echo ' pkt)</li>';
        }

        echo '</ul>';
    }
}