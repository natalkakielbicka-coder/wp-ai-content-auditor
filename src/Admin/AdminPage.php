<?php
namespace WPAuditor\Admin;

use WPAuditor\AnalyzerInterface;
use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

class AdminPage {

    private AnalyzerInterface $analyzer;

    public function __construct(AnalyzerInterface $analyzer) {
        $this->analyzer = $analyzer;
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
                $this->render_result($selected_id);
            }
        }

        echo '</div>';
    }

    private function render_result(int $post_id): void {

        $content = get_post_field('post_content', $post_id, 'raw');

        if (!is_string($content)) {
            return;
        }

        $word_count = $this->analyzer->count_words($content);

        $is_long = $this->analyzer->is_long_content($content);

        $post_type = get_post_type($post_id);

        $type_label = $post_type === 'post' ? 'Wpis' : 'Strona';

        echo '<h2>Wynik analizy</h2>';

        echo '<p><strong>Typ treści:</strong> ';
        echo esc_html($type_label);
        echo '</p>';

        echo '<p><strong>Tytuł:</strong> ';
        echo esc_html(get_the_title($post_id));
        echo '</p>';

        echo '<p><strong>Liczba słów:</strong> ';
        echo esc_html((string) $word_count);
        echo '</p>';

        echo '<p><strong>Długi tekst:</strong> ';
        echo $is_long ? 'TAK' : 'NIE';
        echo '</p>';
    }
}