<?php
namespace WPAuditor;

use DOMDocument;
use DOMXPath;

if (!defined('ABSPATH')) {
    exit;
}

class LinkAnalyzer {

    public function __construct(
        private string $site_url
    ) {
    }

    public function analyze(string $html): array {

        $result = [
            'internal' => [],
            'external' => [],
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

        $links = $xpath->query('//a[@href]');

        foreach ($links as $link) {

            $href = trim($link->getAttribute('href'));

            $type = $this->get_link_type($href);

            if ($type === null) {
                continue;
            }

            $result[$type][] = $href;
        }

        return $result;
    }

    public function get_link_type(string $url): ?string {

        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '#')) {
            return 'internal';
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return null;
        }

        $scheme = strtolower($parts['scheme'] ?? '');

        if ($scheme !== '' && !in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        if (!isset($parts['host'])) {

            if ($scheme !== '') {
                return null;
            }

            return 'internal';
        }

        $link_host = strtolower(rtrim($parts['host'], '.'));

        $site_host = parse_url($this->site_url, PHP_URL_HOST);

        if (!is_string($site_host) || $site_host === '') {
            return null;
        }

        $site_host = strtolower(rtrim($site_host, '.'));

        return $link_host === $site_host
            ? 'internal'
            : 'external';
    }

    public function count_internal(string $html): int {
        $result = $this->analyze($html);

        return count($result['internal']);
    }

    public function count_external(string $html): int {
        $result = $this->analyze($html);

        return count($result['external']);
    }

    public function has_external_links(string $html): bool {
        return $this->count_external($html) > 0;
    }
}