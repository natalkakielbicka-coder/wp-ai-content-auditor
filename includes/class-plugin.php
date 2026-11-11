<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Content_Auditor_Plugin {

    private string $plugin_name;

    private string $version;

    private array $supported_post_types = [
        'post',
        'page',
        'product',
        'event',
        'portfolio'
    ];

    public function __construct() {
        $this->plugin_name = 'WP AI Content Auditor';
        $this->version = '0.1.0';
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
        add_action('admin_notices', array($this, 'show_notice'));
    }
    
    public function show_notice(): void {

        $message = $this->get_plugin_info() . ' działa!';

        echo '<div class="notice notice-success"><p>';
        echo esc_html($message);
        echo '</p>';

        echo '<p>Obsługiwane typy treści:</p>';
        echo '<ul>';

        foreach ($this->get_supported_post_types() as $post_type) {
            echo '<li>' . esc_html($post_type) . '</li>';
        }

        echo '</ul></div>';
    }
}