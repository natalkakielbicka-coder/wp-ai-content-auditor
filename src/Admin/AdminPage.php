<?php
namespace WPAuditor\Admin;

use WPAuditor\ContentAuditor;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

class AdminPage {

    private ContentAuditor $auditor;
    private ResultRenderer $renderer;

    public function __construct(
        ContentAuditor $auditor,
        ?ResultRenderer $renderer = null
    ) {
        $this->auditor = $auditor;
        $this->renderer = $renderer ?? new ResultRenderer();
    }

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

        $query = new WP_Query([
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => 'title',
            'order' => 'ASC',
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        $posts = $query->posts;

        $selected_id = 0;

        if (isset($_GET['post_id']) && is_string($_GET['post_id'])) {
            $selected_id = absint(wp_unslash($_GET['post_id']));
        }

        echo '<div class="wrap">';

        echo '<h1>WP AI Content Auditor</h1>';
        echo '<h2>Pierwszy audyt</h2>';
        echo '<p>Wybierz wpis lub stronę do analizy.</p>';

        echo '<form method="get">';

        echo '<input type="hidden" name="page" value="wp-ai-content-auditor">';

        echo '<label for="auditor-post">Wpis lub strona:</label> ';

        echo '<select name="post_id" id="auditor-post" required>';

        echo '<option value="">Wybierz treść</option>';

        foreach ($posts as $post_id) {

            $title = get_the_title($post_id);

            echo '<option value="' . esc_attr((string) $post_id) . '"';

            echo selected($selected_id, $post_id, false);

            echo '>' . esc_html($title) . '</option>';
        }

        echo '</select> ';

        submit_button('Analizuj treść', 'primary', 'submit', false);

        echo '</form>';

        if ($selected_id > 0) {

            if (!in_array($selected_id, $posts, true)) {

                echo '<div class="notice notice-error inline"><p>';
                echo 'Nie znaleziono wybranej treści na liście.';
                echo '</p></div>';

            } elseif (!current_user_can('edit_post', $selected_id)) {

                echo '<div class="notice notice-error inline"><p>';
                echo 'Nie masz uprawnień do analizy tej treści.';
                echo '</p></div>';

            } else {

                $result = $this->auditor->audit($selected_id);

                $this->renderer->render($result);
            }
        }

        echo '</div>';
    }
}