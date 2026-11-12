<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WPAuditor\LinkAnalyzer;

final class LinkAnalyzerTest extends TestCase {

    private LinkAnalyzer $analyzer;

    protected function setUp(): void {
        $this->analyzer = new LinkAnalyzer(
            'http://localhost:8080'
        );
    }

    public function test_counts_internal_links(): void {

        $html = '
            <a href="/kontakt/">Kontakt</a>
            <a href="http://localhost:8080/o-nas/">O nas</a>
        ';

        $this->assertSame(
            2,
            $this->analyzer->count_internal($html)
        );
    }

    public function test_counts_external_links(): void {

        $html = '
            <a href="https://wordpress.org/">WordPress</a>
            <a href="https://github.com/">GitHub</a>
        ';

        $this->assertSame(
            2,
            $this->analyzer->count_external($html)
        );
    }

    public function test_ignores_email_and_phone_links(): void {

        $html = '
            <a href="mailto:test@example.com">E-mail</a>
            <a href="tel:+48123456789">Telefon</a>
        ';

        $result = $this->analyzer->analyze($html);

        $this->assertSame([], $result['internal']);
        $this->assertSame([], $result['external']);
    }

    public function test_empty_html_has_no_links(): void {

        $result = $this->analyzer->analyze('');

        $this->assertSame([], $result['internal']);
        $this->assertSame([], $result['external']);
    }

    public function test_counts_duplicate_links(): void {

        $html = '
            <a href="/kontakt/">Kontakt</a>
            <a href="/kontakt/">Kontakt ponownie</a>
        ';

        $this->assertSame(
            2,
            $this->analyzer->count_internal($html)
        );
    }

    public function test_ignores_anchor_without_href(): void {

        $html = '<a>Tekst bez linku</a>';

        $result = $this->analyzer->analyze($html);

        $this->assertSame([], $result['internal']);
        $this->assertSame([], $result['external']);
    }

    public function test_detects_external_links(): void {

        $html = '<a href="https://wordpress.org/">WordPress</a>';

        $this->assertTrue(
            $this->analyzer->has_external_links($html)
        );
    }

    public function test_detects_no_external_links(): void {

        $html = '<a href="/kontakt/">Kontakt</a>';

        $this->assertFalse(
            $this->analyzer->has_external_links($html)
        );
    }

    public static function linkTypesProvider(): array {

        return [
            'relative path' => ['/kontakt/', 'internal'],
            'relative page' => ['o-nas/', 'internal'],
            'current host' => [
                'http://localhost:8080/kontakt/',
                'internal'
            ],
            'fragment' => ['#formularz', 'internal'],
            'external host' => [
                'https://wordpress.org/',
                'external'
            ],
            'protocol relative' => [
                '//wordpress.org/docs/',
                'external'
            ],
            'subdomain' => [
                'https://blog.localhost/',
                'external'
            ],
            'mailto' => ['mailto:test@example.com', null],
            'telephone' => ['tel:+48123456789', null],
            'javascript' => ['javascript:alert(1)', null],
            'empty URL' => ['', null],
            'whitespace' => ['   ', null],
        ];
    }

    #[DataProvider('linkTypesProvider')]
    public function test_classifies_links(
        string $url,
        ?string $expected
    ): void {

        $this->assertSame(
            $expected,
            $this->analyzer->get_link_type($url)
        );
    }
}