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

    public function test_allows_return_to_parent_heading(): void {

        $html = '
            <h2>Oferta</h2>
            <h3>WordPress</h3>
            <h2>Kontakt</h2>
        ';

        $analyzer = new HeadingAnalyzer();

        $this->assertSame(
            [],
            $analyzer->get_heading_jumps($html)
        );
    }

    public function test_allows_multiple_nested_headings(): void {

        $html = '
            <h2>Oferta</h2>
            <h3>Strony internetowe</h3>
            <h4>WordPress</h4>
            <h3>Sklepy</h3>
            <h4>WooCommerce</h4>
            <h2>Kontakt</h2>
        ';

        $analyzer = new HeadingAnalyzer();

        $this->assertSame(
            [],
            $analyzer->get_heading_jumps($html)
        );
    }

    public function test_detects_single_heading_jump(): void {

        $html = '
            <h2>Oferta</h2>
            <h4>WordPress</h4>
        ';

        $analyzer = new HeadingAnalyzer();

        $jumps = $analyzer->get_heading_jumps($html);

        $this->assertSame(
            [
                [
                    'from' => 2,
                    'to' => 4,
                ],
            ],
            $jumps
        );
    }

    public function test_detects_multiple_heading_jumps(): void {

        $html = '
            <h2>Oferta</h2>
            <h4>WordPress</h4>
            <h3>Sklepy</h3>
            <h5>WooCommerce</h5>
        ';

        $analyzer = new HeadingAnalyzer();

        $jumps = $analyzer->get_heading_jumps($html);

        $this->assertSame(
            [
                [
                    'from' => 2,
                    'to' => 4,
                ],
                [
                    'from' => 3,
                    'to' => 5,
                ],
            ],
            $jumps
        );
    }

    public function test_empty_content_has_no_heading_jumps(): void {

        $analyzer = new HeadingAnalyzer();

        $this->assertSame(
            [],
            $analyzer->get_heading_jumps('')
        );
    }

    public function test_text_without_headings_has_no_jumps(): void {

        $html = '<p>Zwykła treść bez nagłówków.</p>';

        $analyzer = new HeadingAnalyzer();

        $this->assertSame(
            [],
            $analyzer->get_heading_jumps($html)
        );
    }

    public function test_heading_jump_message_contains_levels(): void {

        $html = '
            <h2>Oferta</h2>
            <h4>WordPress</h4>
        ';

        $analyzer = new HeadingAnalyzer();

        $issues = $analyzer->get_issues($html);

        $this->assertCount(1, $issues);

        $this->assertSame(
            'Nieprawidłowy przeskok z H2 do H4.',
            $issues[0]['message']
        );

        $this->assertSame(
            10,
            $issues[0]['penalty']
        );
    }

    public function test_multiple_h1_and_jump_generate_two_issues(): void {

        $html = '
            <h1>Pierwszy tytuł</h1>
            <h3>Podsekcja</h3>
            <h1>Drugi tytuł</h1>
        ';

        $analyzer = new HeadingAnalyzer();

        $issues = $analyzer->get_issues($html);

        $this->assertCount(2, $issues);

        $this->assertSame(
            15,
            $issues[0]['penalty']
        );

        $this->assertSame(
            10,
            $issues[1]['penalty']
        );
    }
}