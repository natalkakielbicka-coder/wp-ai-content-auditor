<?php

if (!defined('ABSPATH')) {
    exit;
}

class WP_AI_Content_Auditor_Plugin {

    public function run() {
        add_action('admin_notices', array($this, 'show_notice'));
    }

    public function show_notice() {
        echo '<div class="notice notice-success"><p>';
        echo esc_html('WP AI Content Auditor działa!');
        echo '</p></div>';
    }
}
