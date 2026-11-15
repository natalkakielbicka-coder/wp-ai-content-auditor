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

    public function test_counts_unique_internal_links(): void {

        $html = '
            <a href="/kontakt/">Kontakt</a>
            <a href="/kontakt/">Kontakt ponownie</a>
            <a href="/o-nas/">O nas</a>
        ';

        $stats = $this->analyzer->get_statistics($html);

        $this->assertSame(3, $stats['internal_total']);
        $this->assertSame(2, $stats['internal_unique']);
        $this->assertSame(1, $stats['internal_repeated']);
    }

    public function test_counts_unique_external_links(): void {

        $html = '
            <a href="https://wordpress.org/">WordPress</a>
            <a href="https://wordpress.org/">WordPress ponownie</a>
            <a href="https://github.com/">GitHub</a>
        ';

        $stats = $this->analyzer->get_statistics($html);

        $this->assertSame(3, $stats['external_total']);
        $this->assertSame(2, $stats['external_unique']);
        $this->assertSame(1, $stats['external_repeated']);
    }

    public function test_counts_mixed_links(): void {

        $html = '
            <a href="/kontakt/">Kontakt</a>
            <a href="/kontakt/">Kontakt ponownie</a>
            <a href="https://wordpress.org/">WordPress</a>
            <a href="https://github.com/">GitHub</a>
            <a href="https://github.com/">GitHub ponownie</a>
        ';

        $stats = $this->analyzer->get_statistics($html);

        $this->assertSame([
            'internal_total' => 2,
            'external_total' => 3,
            'internal_unique' => 1,
            'external_unique' => 2,
            'internal_repeated' => 1,
            'external_repeated' => 1,
        ], $stats);
    }

    public function test_empty_content_has_zero_link_statistics(): void {

        $stats = $this->analyzer->get_statistics('');

        $this->assertSame([
            'internal_total' => 0,
            'external_total' => 0,
            'internal_unique' => 0,
            'external_unique' => 0,
            'internal_repeated' => 0,
            'external_repeated' => 0,
        ], $stats);
    }

    public function test_treats_different_url_formats_as_unique(): void {

        $html = '
            <a href="/kontakt/">Kontakt</a>
            <a href="http://localhost:8080/kontakt/">Kontakt</a>
        ';

        $stats = $this->analyzer->get_statistics($html);

        $this->assertSame(2, $stats['internal_total']);
        $this->assertSame(2, $stats['internal_unique']);
        $this->assertSame(0, $stats['internal_repeated']);
    }

    public function test_detects_duplicate_internal_links(): void {

        $html = '
            <a href="/kontakt/">Kontakt</a>
            <a href="/kontakt/">Kontakt ponownie</a>
        ';

        $this->assertTrue(
            $this->analyzer->has_duplicate_internal_links($html)
        );
    }

    public function test_detects_no_duplicate_internal_links(): void {

        $html = '
            <a href="/kontakt/">Kontakt</a>
            <a href="/o-nas/">O nas</a>
        ';

        $this->assertFalse(
            $this->analyzer->has_duplicate_internal_links($html)
        );
    }

    public function test_ignored_links_do_not_affect_statistics(): void {

        $html = '
            <a href="mailto:test@example.com">E-mail</a>
            <a href="tel:+48123456789">Telefon</a>
            <a href="">Pusty link</a>
            <a href="/kontakt/">Kontakt</a>
        ';

        $stats = $this->analyzer->get_statistics($html);

        $this->assertSame(1, $stats['internal_total']);
        $this->assertSame(1, $stats['internal_unique']);
        $this->assertSame(0, $stats['external_total']);
    }
}