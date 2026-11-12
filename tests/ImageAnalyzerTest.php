<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WPAuditor\ImageAnalyzer;

final class ImageAnalyzerTest extends TestCase {

    private ImageAnalyzer $analyzer;

    protected function setUp(): void {
        $this->analyzer = new ImageAnalyzer();
    }

    public function test_empty_content_has_no_images(): void {

        $result = $this->analyzer->analyze('');

        $this->assertSame(0, $result['total']);
        $this->assertSame(0, $result['with_alt']);
        $this->assertSame(0, $result['empty_alt']);
        $this->assertSame(0, $result['missing_alt']);
        $this->assertSame(0, $result['whitespace_alt']);
    }

    public function test_image_with_alt(): void {

        $html = '<img src="kot.jpg" alt="Czarny kot">';

        $result = $this->analyzer->analyze($html);

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['with_alt']);
        $this->assertSame(0, $result['missing_alt']);
    }

    public function test_image_with_empty_alt(): void {

        $html = '<img src="tlo.jpg" alt="">';

        $result = $this->analyzer->analyze($html);

        $this->assertSame(1, $result['empty_alt']);
        $this->assertSame(0, $result['missing_alt']);
        $this->assertSame([], $this->analyzer->get_issues($html));
    }

    public function test_image_without_alt(): void {

        $html = '<img src="produkt.jpg">';

        $result = $this->analyzer->analyze($html);

        $this->assertSame(1, $result['missing_alt']);
        $this->assertSame(0, $result['with_alt']);
    }

    public function test_image_with_whitespace_alt(): void {

        $html = '<img src="baner.jpg" alt="   ">';

        $result = $this->analyzer->analyze($html);

        $this->assertSame(1, $result['whitespace_alt']);
        $this->assertSame(0, $result['empty_alt']);
    }

    public function test_counts_multiple_images(): void {

        $html = '
            <img src="a.jpg" alt="Zdjęcie A">
            <img src="b.jpg" alt="">
            <img src="c.jpg">
            <img src="d.jpg" alt="   ">
        ';

        $result = $this->analyzer->analyze($html);

        $this->assertSame(4, $result['total']);
        $this->assertSame(1, $result['with_alt']);
        $this->assertSame(1, $result['empty_alt']);
        $this->assertSame(1, $result['missing_alt']);
        $this->assertSame(1, $result['whitespace_alt']);
    }

    public function test_ignores_text_without_images(): void {

        $html = '<p>To jest zwykły tekst.</p>';

        $result = $this->analyzer->analyze($html);

        $this->assertSame(0, $result['total']);
    }

    public function test_detects_missing_alt_issue(): void {

        $html = '<img src="produkt.jpg">';

        $issues = $this->analyzer->get_issues($html);

        $this->assertCount(1, $issues);
        $this->assertSame('Obrazy', $issues[0]['category']);
        $this->assertSame(10, $issues[0]['penalty']);
    }

    public function test_detects_whitespace_alt_issue(): void {

        $html = '<img src="baner.jpg" alt="   ">';

        $issues = $this->analyzer->get_issues($html);

        $this->assertCount(1, $issues);
        $this->assertSame(5, $issues[0]['penalty']);
    }

    public function test_reports_missing_alt_only_once(): void {

        $html = '
            <img src="a.jpg">
            <img src="b.jpg">
            <img src="c.jpg">
        ';

        $issues = $this->analyzer->get_issues($html);

        $this->assertCount(1, $issues);
        $this->assertSame(10, $issues[0]['penalty']);
    }

    public function test_detects_two_types_of_issues(): void {

        $html = '
            <img src="a.jpg">
            <img src="b.jpg" alt="   ">
        ';

        $issues = $this->analyzer->get_issues($html);

        $this->assertCount(2, $issues);
        $this->assertSame(10, $issues[0]['penalty']);
        $this->assertSame(5, $issues[1]['penalty']);
    }

    public function test_has_missing_alt(): void {

        $html = '<img src="a.jpg">';

        $this->assertTrue(
            $this->analyzer->has_missing_alt($html)
        );
    }

    public function test_has_no_missing_alt(): void {

        $html = '<img src="a.jpg" alt="">';

        $this->assertFalse(
            $this->analyzer->has_missing_alt($html)
        );
    }

    public function test_alt_with_polish_characters(): void {

        $html = '<img src="ogrod.jpg" alt="Żółte róże w ogrodzie">';

        $result = $this->analyzer->analyze($html);

        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['with_alt']);
        $this->assertSame(0, $result['missing_alt']);
    }

    public static function imageProvider(): array {

        return [
            'descriptive alt' => [
                '<img src="a.jpg" alt="Opis">',
                1, 0, 0, 0
            ],
            'empty alt' => [
                '<img src="a.jpg" alt="">',
                0, 1, 0, 0
            ],
            'missing alt' => [
                '<img src="a.jpg">',
                0, 0, 1, 0
            ],
            'whitespace alt' => [
                '<img src="a.jpg" alt="   ">',
                0, 0, 0, 1
            ],
        ];
    }

    #[DataProvider('imageProvider')]
    public function test_classifies_images(
        string $html,
        int $with_alt,
        int $empty_alt,
        int $missing_alt,
        int $whitespace_alt
    ): void {

        $result = $this->analyzer->analyze($html);

        $this->assertSame($with_alt, $result['with_alt']);
        $this->assertSame($empty_alt, $result['empty_alt']);
        $this->assertSame($missing_alt, $result['missing_alt']);
        $this->assertSame($whitespace_alt, $result['whitespace_alt']);
    }
}