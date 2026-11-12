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

        return $result;
    }
}