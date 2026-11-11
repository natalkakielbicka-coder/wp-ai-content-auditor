<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WPAuditor\ContentAnalyzer;

final class ContentAnalyzerTest extends TestCase {

    public function test_counts_words(): void {
        $analyzer = new ContentAnalyzer();

        $result = $analyzer->count_words('Ala ma kota');

        $this->assertSame(3, $result);
    }

    public function test_empty_content_has_zero_words(): void {
        $analyzer = new ContentAnalyzer();

        $result = $analyzer->count_words('');

        $this->assertSame(0, $result);
    }

    public function test_counts_words_inside_html(): void {
        $analyzer = new ContentAnalyzer();

        $result = $analyzer->count_words('<p>Ala ma kota</p>');

        $this->assertSame(3, $result);
    }

    public function test_short_content_is_not_long(): void {
        $analyzer = new ContentAnalyzer();

        $result = $analyzer->is_long_content('Ala ma kota');

        $this->assertFalse($result);
    }

    public function test_content_with_300_words_is_long(): void {
        $analyzer = new ContentAnalyzer();

        $content = str_repeat('słowo ', 300);

        $result = $analyzer->is_long_content($content);

        $this->assertTrue($result);
    }

    public function test_empty_content_has_no_content(): void {
        $analyzer = new ContentAnalyzer();

        $result = $analyzer->has_content('   ');

        $this->assertFalse($result);
    }

    public function test_text_has_content(): void {
        $analyzer = new ContentAnalyzer();

        $result = $analyzer->has_content('Witaj WordPress');

        $this->assertTrue($result);
    }
}
