<?php
namespace WPAuditor;

use DOMDocument;
use DOMXPath;

if (!defined('ABSPATH')) {
    exit;
}

class HeadingAnalyzer {

    public function get_heading_levels(string $html): array {

        if (trim($html) === '') {
            return [];
        }

        $document = new DOMDocument();

        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8"?>' . $html,
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (!$loaded) {
            return [];
        }

        $xpath = new DOMXPath($document);

        $headings = $xpath->query(
            '//h1 | //h2 | //h3 | //h4 | //h5 | //h6'
        );

        $levels = [];

        foreach ($headings as $heading) {
            $levels[] = (int) substr($heading->nodeName, 1);
        }

        return $levels;
    }

    public function get_issues(string $html): array {

        $levels = $this->get_heading_levels($html);

        $issues = [];

        $h1_count = count(
            array_filter($levels, fn($level) => $level === 1)
        );

        if ($h1_count > 1) {
            $issues[] = [
                'category' => 'Nagłówki',
                'message' => 'W treści znajduje się więcej niż jeden H1.',
                'penalty' => 15,
            ];
        }

        $previous_level = null;

        foreach ($levels as $level) {

            if (
                $previous_level !== null &&
                $level > $previous_level + 1
            ) {
                $issues[] = [
                    'category' => 'Nagłówki',
                    'message' => 'Wykryto przeskok poziomu nagłówków.',
                    'penalty' => 10,
                ];

                break;
            }

            $previous_level = $level;
        }

        return $issues;
    }
}