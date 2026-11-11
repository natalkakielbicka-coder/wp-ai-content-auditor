<?php
namespace WPAuditor\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class AdminPage {

    public function register(): void {
        add_action('admin_menu', [$this, 'add_menu']);
    }

    public function add_menu(): void {
        add_menu_page(
            'WP AI Content Auditor',
            'AI Auditor',
            'manage_options',
            'wp-ai-content-auditor',
            [$this, 'render'],
            'dashicons-search',
            30
        );
    }

    public function render(): void {

        if (!current_user_can('manage_options')) {
            wp_die('Nie masz uprawnień do tej strony.');
        }

        echo '<div class="wrap">';

        echo '<h1>WP AI Content Auditor</h1>';

        echo '<p>Witaj w panelu audytora treści.</p>';

        echo '<p>Tutaj w przyszłości uruchomisz analizę wpisów i stron WordPressa.</p>';

        echo '</div>';
    }
}
