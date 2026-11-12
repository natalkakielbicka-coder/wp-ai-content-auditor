<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WPAuditor\HeadingAnalyzer;

final class HeadingAnalyzerTest extends TestCase {

    public function test_extracts_heading_levels(): void {

        $analyzer = new HeadingAnalyzer();

        $html = '<h2>Tytuł</h2><p>Tekst</p><h3>Sekcja</h3>';

        $this->assertSame(
            [2, 3],
            $analyzer->get_heading_levels($html)
        );
    }

    public function test_empty_html_has_no_headings(): void {

        $analyzer = new HeadingAnalyzer();

        $this->assertSame(
            [],
            $analyzer->get_heading_levels('')
        );
    }

    public function test_correct_headings_have_no_issues(): void {

        $analyzer = new HeadingAnalyzer();

        $html = '<h2>Start</h2><h3>Sekcja</h3><h4>Szczegóły</h4>';

        $this->assertSame(
            [],
            $analyzer->get_issues($html)
        );
    }

    public function test_detects_skipped_heading_level(): void {

        $analyzer = new HeadingAnalyzer();

        $html = '<h2>Start</h2><h4>Szczegóły</h4>';

        $issues = $analyzer->get_issues($html);

        $this->assertCount(1, $issues);
        $this->assertSame(10, $issues[0]['penalty']);
    }

    public function test_detects_multiple_h1_headings(): void {

        $analyzer = new HeadingAnalyzer();

        $html = '<h1>Pierwszy</h1><h2>Sekcja</h2><h1>Drugi</h1>';

        $issues = $analyzer->get_issues($html);

        $this->assertCount(1, $issues);
        $this->assertSame(15, $issues[0]['penalty']);
    }

    public function test_missing_h1_is_not_an_issue(): void {

        $analyzer = new HeadingAnalyzer();

        $html = '<h2>Wprowadzenie</h2><h3>Szczegóły</h3>';

        $this->assertSame(
            [],
            $analyzer->get_issues($html)
        );
    }
}