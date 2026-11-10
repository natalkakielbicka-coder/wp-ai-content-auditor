<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Content_Auditor_Plugin {

    private $plugin_name;

    private $version;

    public function __construct() {
        $this->plugin_name = 'WP AI Content Auditor';
        $this->version = '0.1.0';
    }

    public function run() {
        add_action('admin_notices', array($this, 'show_notice'));
    }

    public function show_notice() {
        echo '<div class="notice notice-success"><p>';

        echo esc_html(
            $this->plugin_name . ' v' . $this->version . ' działa!'
        );

        echo '</p></div>';
    }
}
