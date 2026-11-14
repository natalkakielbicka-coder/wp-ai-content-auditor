<?php
namespace WPAuditor;

if (!defined('ABSPATH')) {
    exit;
}

class SeoMetaAnalyzer {

    public function analyze(int $post_id): array {

        $result = [
            'provider' => 'none',
            'title' => '',
            'description' => '',
            'title_status' => 'unavailable',
            'description_status' => 'unavailable',
            'title_length' => 0,
            'description_length' => 0,
            'title_length_status' => 'not_checked',
            'description_length_status' => 'not_checked',
        ];

        if (!defined('WPSEO_VERSION')) {
            return $result;
        }

        $result['provider'] = 'yoast';

        $title = get_post_meta(
            $post_id,
            '_yoast_wpseo_title',
            true
        );

        $description = get_post_meta(
            $post_id,
            '_yoast_wpseo_metadesc',
            true
        );

        $title = is_string($title) ? trim($title) : '';

        $description = is_string($description)
            ? trim($description)
            : '';

        $result['title'] = $title;
        $result['description'] = $description;

        $result['title_status'] = $title === ''
            ? 'default'
            : 'custom';

        $result['description_status'] = $description === ''
            ? 'default'
            : 'custom';

        $result['title_length'] = mb_strlen($title);
        $result['description_length'] = mb_strlen($description);

        if (
            $title !== '' &&
            !$this->has_template_variables($title)
        ) {
            $result['title_length_status'] = $this->check_length(
                $result['title_length'],
                30,
                60
            );
        }

        if (
            $description !== '' &&
            !$this->has_template_variables($description)
        ) {
            $result['description_length_status'] = $this->check_length(
                $result['description_length'],
                70,
                160
            );
        }

        return $result;
    }

    public function has_custom_description(int $post_id): bool {

        $result = $this->analyze($post_id);

        return $result['description_status'] === 'custom';
    }

    private function has_template_variables(string $value): bool {

        return preg_match('/%%[^%]+%%/', $value) === 1;
    }

    private function check_length(
        int $length,
        int $minimum,
        int $maximum
    ): string {

        if ($length < $minimum) {
            return 'short';
        }

        if ($length > $maximum) {
            return 'long';
        }

        return 'ok';
    }
}