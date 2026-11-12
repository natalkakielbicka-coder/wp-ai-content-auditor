<?php
namespace WPAuditor;

use DOMDocument;
use DOMXPath;

if (!defined('ABSPATH')) {
    exit;
}

class ImageAnalyzer {

    public function analyze(string $html): array {

        $result = [
            'total' => 0,
            'with_alt' => 0,
            'empty_alt' => 0,
            'missing_alt' => 0,
            'whitespace_alt' => 0,
        ];

        if (trim($html) === '') {
            return $result;
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
            return $result;
        }

        $xpath = new DOMXPath($document);

        $images = $xpath->query('//img');

        foreach ($images as $image) {

            $result['total']++;

            if (!$image->hasAttribute('alt')) {
                $result['missing_alt']++;
                continue;
            }

            $alt = $image->getAttribute('alt');

            if ($alt === '') {
                $result['empty_alt']++;
                continue;
            }

            if (trim($alt) === '') {
                $result['whitespace_alt']++;
                continue;
            }

            $result['with_alt']++;
        }

        return $result;
    }

    public function get_issues(string $html): array {

        $result = $this->analyze($html);

        $issues = [];

        if ($result['missing_alt'] > 0) {
            $issues[] = [
                'category' => 'Obrazy',
                'message' => 'Niektóre obrazki nie mają atrybutu ALT.',
                'penalty' => 10,
            ];
        }

        if ($result['whitespace_alt'] > 0) {
            $issues[] = [
                'category' => 'Obrazy',
                'message' => 'Niektóre atrybuty ALT zawierają tylko spacje.',
                'penalty' => 5,
            ];
        }

        return $issues;
    }
}