<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Content_Auditor_Plugin {

    private string $plugin_name;

    private string $version;

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

        if ($post_type === 'post') {
            return true;
        }

        if ($post_type === 'page') {
            return true;
        }

        if ($post_type === 'product') {
            return true;
        }

        return false;
    }

    public function run(): void {
        add_action('admin_notices', array($this, 'show_notice'));
    }

    public function show_notice(): void {

        $post_supported = $this->is_supported_post_type('post');
        $page_supported = $this->is_supported_post_type('page');
        $product_supported = $this->is_supported_post_type('product');

        $message = $this->get_plugin_info() . ' działa!';

        echo '<div class="notice notice-success"><p>';
        echo esc_html($message);
        echo '</p>';

        echo '<p>Obsługiwane typy treści:</p>';

        echo '<ul>';

        echo '<li>Post: ' . ($post_supported ? 'TAK' : 'NIE') . '</li>';
        echo '<li>Page: ' . ($page_supported ? 'TAK' : 'NIE') . '</li>';
        echo '<li>Product: ' . ($product_supported ? 'TAK' : 'NIE') . '</li>';

        echo '</ul></div>';
    }
}