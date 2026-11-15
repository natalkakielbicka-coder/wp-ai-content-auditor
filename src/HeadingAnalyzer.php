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
            array_filter(
                $levels,
                static fn(int $level): bool => $level === 1
            )
        );

        if ($h1_count > 1) {
            $issues[] = [
                'category' => 'Nagłówki',
                'message' => 'W treści znajduje się więcej niż jeden nagłówek H1.',
                'penalty' => 15,
            ];
        }

        $jumps = $this->get_heading_jumps($html);

        foreach ($jumps as $jump) {

            $issues[] = [
                'category' => 'Nagłówki',
                'message' => sprintf(
                    'Nieprawidłowy przeskok z H%d do H%d.',
                    $jump['from'],
                    $jump['to']
                ),
                'penalty' => 10,
            ];
        }

        return $issues;
    }

    public function get_heading_jumps(string $html): array {

        $levels = $this->get_heading_levels($html);

        $jumps = [];
        $previous_level = null;

        foreach ($levels as $level) {

            if (
                $previous_level !== null &&
                $level > $previous_level + 1
            ) {
                $jumps[] = [
                    'from' => $previous_level,
                    'to' => $level,
                ];
            }

            $previous_level = $level;
        }

        return $jumps;
    }
}