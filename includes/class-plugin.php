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

    public function run(): void {
        add_action('admin_notices', array($this, 'show_notice'));
    }

    public function show_notice(): void {
        $message = $this->get_plugin_info() . ' działa!';

        echo '<div class="notice notice-success"><p>';
        echo esc_html($message);
        echo '</p></div>';
    }
}