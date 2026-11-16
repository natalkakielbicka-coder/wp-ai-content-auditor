<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WPAuditor\AuditResult;
use WPAuditor\Admin\ResultRenderer;

if (!function_exists('esc_html')) {
    function esc_html($text): string {
        return htmlspecialchars(
            (string) $text,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}

final class ResultRendererTest extends TestCase {

    private ResultRenderer $renderer;

    protected function setUp(): void {
        $this->renderer = new ResultRenderer();
    }

    private function render_html(AuditResult $result): string {

        $level = ob_get_level();

        ob_start();

        try {
            $this->renderer->render($result);

            return (string) ob_get_clean();

        } finally {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }
    }

    private function create_result(
        array $issues = [],
        string $title = 'Przykładowy wpis'
    ): AuditResult {

        return new AuditResult(
            10,
            $title,
            350,
            true,
            $issues
        );
    }

    public function test_displays_basic_information(): void {

        $result = $this->create_result();

        $html = $this->render_html($result);

        $this->assertStringContainsString(
            'Przykładowy wpis',
            $html
        );

        $this->assertStringContainsString(
            'Liczba słów:',
            $html
        );

        $this->assertStringContainsString(
            '350',
            $html
        );

        $this->assertStringContainsString(
            'Obrazek wyróżniający:',
            $html
        );
    }

    public function test_displays_score_and_issue_count(): void {

        $issues = [
            [
                'category' => 'Treść',
                'message' => 'Za mało słów.',
                'penalty' => 30,
            ],
            [
                'category' => 'Obrazy',
                'message' => 'Brak ALT.',
                'penalty' => 10,
            ],
        ];

        $result = $this->create_result($issues);

        $html = $this->render_html($result);

        $this->assertStringContainsString(
            'Ocena treści: ',
            $html
        );

        $this->assertStringContainsString(
            '60',
            $html
        );

        $this->assertStringContainsString(
            'Liczba problemów:',
            $html
        );

        $this->assertStringContainsString(
            'Za mało słów.',
            $html
        );
    }

    public function test_displays_issue_summary(): void {

        $issues = [
            [
                'category' => 'Nagłówki',
                'message' => 'Przeskok H2 do H4.',
                'penalty' => 10,
            ],
            [
                'category' => 'Nagłówki',
                'message' => 'Przeskok H3 do H5.',
                'penalty' => 10,
            ],
        ];

        $result = $this->create_result($issues);

        $html = $this->render_html($result);

        $this->assertStringContainsString(
            'Podsumowanie problemów',
            $html
        );

        $this->assertStringContainsString(
            'Nagłówki',
            $html
        );

        $this->assertStringContainsString(
            'Przeskok H2 do H4.',
            $html
        );
    }

    public function test_displays_no_issues_message(): void {

        $result = $this->create_result();

        $html = $this->render_html($result);

        $this->assertStringContainsString(
            'Nie wykryto problemów.',
            $html
        );

        $this->assertStringNotContainsString(
            '<h3>Wykryte problemy</h3>',
            $html
        );
    }

    public function test_escapes_html_in_title(): void {

        $result = $this->create_result(
            [],
            '<script>alert("test")</script>'
        );

        $html = $this->render_html($result);

        $this->assertStringNotContainsString(
            '<script>',
            $html
        );

        $this->assertStringContainsString(
            '&lt;script&gt;',
            $html
        );
    }

    public function test_escapes_html_in_issue_message(): void {

        $issues = [
            [
                'category' => '<strong>Niebezpieczna kategoria</strong>',
                'message' => '<img src=x onerror=alert(1)>',
                'penalty' => 10,
            ],
        ];

        $result = $this->create_result($issues);

        $html = $this->render_html($result);

        $this->assertStringNotContainsString(
            '<img src=x onerror=alert(1)>',
            $html
        );

        $this->assertStringContainsString(
            '&lt;img',
            $html
        );

        $this->assertStringContainsString(
            '&lt;strong&gt;',
            $html
        );
    }

    public function test_displays_link_statistics(): void {

        $result = new AuditResult(
            10,
            'Przykładowy wpis',
            350,
            true,
            [],
            4,
            3,
            [],
            [],
            [
                'internal_total' => 4,
                'external_total' => 3,
                'internal_unique' => 2,
                'external_unique' => 2,
                'internal_repeated' => 2,
                'external_repeated' => 1,
            ]
        );

        $html = $this->render_html($result);

        $this->assertStringContainsString(
            'Unikalne linki wewnętrzne:',
            $html
        );

        $this->assertStringContainsString(
            'Powtórzenia linków zewnętrznych:',
            $html
        );
    }

    public function test_displays_image_statistics(): void {

        $result = new AuditResult(
            10,
            'Przykładowy wpis',
            350,
            true,
            [],
            0,
            0,
            [
                'total' => 3,
                'with_alt' => 1,
                'empty_alt' => 1,
                'missing_alt' => 1,
                'whitespace_alt' => 0,
            ]
        );

        $html = $this->render_html($result);

        $this->assertStringContainsString(
            'Analiza obrazków',
            $html
        );

        $this->assertStringContainsString(
            'Bez atrybutu ALT:',
            $html
        );

        $this->assertStringContainsString(
            'Z pustym ALT:',
            $html
        );
    }

    public function test_displays_yoast_metadata(): void {

        $result = new AuditResult(
            10,
            'Przykładowy wpis',
            350,
            true,
            [],
            0,
            0,
            [],
            [
                'provider' => 'yoast',
                'title' => 'Poradnik WordPress',
                'description' => 'Opis przykładowej strony.',
                'title_status' => 'custom',
                'description_status' => 'custom',
                'title_length' => 18,
                'description_length' => 25,
                'title_length_status' => 'short',
                'description_length_status' => 'short',
            ]
        );

        $html = $this->render_html($result);

        $this->assertStringContainsString(
            'Poradnik WordPress',
            $html
        );

        $this->assertStringContainsString(
            'Opis przykładowej strony.',
            $html
        );

        $this->assertStringContainsString(
            'Ocena długości tytułu:',
            $html
        );

        $this->assertStringContainsString(
            'Tekst może być zbyt krótki.',
            $html
        );
    }

    public function test_handles_missing_optional_data(): void {

        $result = $this->create_result();

        $html = $this->render_html($result);

        $this->assertStringNotContainsString(
            'Analiza obrazków',
            $html
        );

        $this->assertStringNotContainsString(
            '<h2>Metadane SEO</h2>',
            $html
        );

        $this->assertStringContainsString(
            'Wynik audytu',
            $html
        );
    }

    public function test_displays_no_featured_image(): void {

        $result = new AuditResult(
            10,
            'Przykładowy wpis',
            350,
            false,
            []
        );

        $html = $this->render_html($result);

        $this->assertStringContainsString(
            '<strong>Obrazek wyróżniający:</strong> NIE',
            $html
        );
    }
}