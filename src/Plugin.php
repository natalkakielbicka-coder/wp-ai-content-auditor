<?php

namespace WPAuditor;

if (!defined('ABSPATH')) {
    exit;
}

class Plugin {

    private string $plugin_name;

    private string $version;

    private array $supported_post_types = [
        'post',
        'page',
        'product',
        'event',
        'portfolio'
    ];

    private ContentAnalyzer $analyzer;

    public function __construct(ContentAnalyzer $analyzer) {
        $this->plugin_name = 'WP AI Content Auditor';
        $this->version = '0.1.0';
        $this->analyzer = $analyzer;
    }

    public function get_plugin_name(): string {
        return $this->plugin_name;
    }

    public function get_version(): string {
        return $this->version;
    }

    public function get_plugin_info(): string {
        return $this->get_plugin_name()
            . ' - wersja '
            . $this->get_version();
    }

    public function is_supported_post_type(string $post_type): bool {
        return in_array($post_type, $this->supported_post_types, true);
    }

    public function get_supported_post_types(): array {
        return $this->supported_post_types;
    }

    public function run(): void {
        add_action('admin_notices', [$this, 'show_notice']);
    }
    
    public function show_notice(): void {

        $content = 'WordPress pozwala tworzyć własne wtyczki PHP.';

        $word_count = $this->analyzer->count_words($content);

        $is_long = $this->analyzer->is_long_content($content);

        echo '<div class="notice notice-success">';

        echo '<p>';
        echo esc_html($this->get_plugin_info() . ' działa!');
        echo '</p>';

        echo '<p>Analizowany tekst: ';
        echo esc_html($content);
        echo '</p>';

        echo '<p>Liczba słów: ';
        echo esc_html((string) $word_count);
        echo '</p>';

        echo '<p>Długi tekst: ';
        echo $is_long ? 'TAK' : 'NIE';
        echo '</p>';

        echo '</div>';
    }
}