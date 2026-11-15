<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use WPAuditor\AuditResult;

final class AuditResultTest extends TestCase {

    public function test_result_without_issues(): void {

        $result = new AuditResult(
            1,
            'Testowy wpis',
            350,
            true,
            []
        );

        $this->assertFalse($result->has_issues());
        $this->assertSame(0, $result->get_issues_count());
        $this->assertSame(100, $result->get_score());
    }

    public function test_result_with_issues(): void {

        $result = new AuditResult(
            2,
            'Krótki wpis',
            50,
            false,
            [
                [
                    'category' => 'Treść',
                    'message' => 'Za krótka treść.',
                    'penalty' => 30,
                ],
                [
                    'category' => 'Obrazy',
                    'message' => 'Brak obrazka.',
                    'penalty' => 20,
                ],
            ]
        );

        $this->assertTrue($result->has_issues());
        $this->assertSame(2, $result->get_issues_count());
        $this->assertSame(50, $result->get_score());
    }

    public function test_result_stores_post_data(): void {

        $result = new AuditResult(
            5,
            'Przykładowa strona',
            120,
            true,
            []
        );

        $this->assertSame(5, $result->post_id);
        $this->assertSame('Przykładowa strona', $result->title);
        $this->assertSame(120, $result->word_count);
    }

    public function test_score_cannot_be_negative(): void {

        $result = new AuditResult(
            3,
            '',
            0,
            false,
            [
                [
                    'category' => 'Test',
                    'message' => 'Duża kara.',
                    'penalty' => 120,
                ],
            ]
        );

        $this->assertSame(0, $result->get_score());
    }

    public function test_score_with_one_issue(): void {

        $result = new AuditResult(
            4,
            'Przykładowy wpis',
            200,
            true,
            [
                [
                    'category' => 'Treść',
                    'message' => 'Za krótka treść.',
                    'penalty' => 30,
                ],
            ]
        );

        $this->assertSame(70, $result->get_score());
    }

    public function test_empty_issue_summary(): void {

        $result = new AuditResult(
            10,
            'Przykładowy tytuł',
            350,
            true,
            []
        );

        $this->assertSame(
            [],
            $result->get_issue_summary()
        );

        $this->assertSame(
            0,
            $result->get_issues_count()
        );

        $this->assertFalse(
            $result->has_issues()
        );
    }

    public function test_groups_issues_from_same_category(): void {

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
            [
                'category' => 'Nagłówki',
                'message' => 'Więcej niż jeden H1.',
                'penalty' => 15,
            ],
        ];

        $result = new AuditResult(
            10,
            'Przykładowy tytuł',
            350,
            true,
            $issues
        );

        $this->assertSame(
            ['Nagłówki' => 3],
            $result->get_issue_summary()
        );

        $this->assertSame(
            3,
            $result->get_issues_count()
        );

        $this->assertTrue(
            $result->has_issues()
        );
    }

    public function test_groups_multiple_issue_categories(): void {

        $issues = [
            [
                'category' => 'Tytuł',
                'message' => 'Za krótki tytuł.',
                'penalty' => 20,
            ],
            [
                'category' => 'Obrazy',
                'message' => 'Brak ALT.',
                'penalty' => 10,
            ],
            [
                'category' => 'Nagłówki',
                'message' => 'Przeskok H2 do H4.',
                'penalty' => 10,
            ],
            [
                'category' => 'Obrazy',
                'message' => 'Pusty opis.',
                'penalty' => 5,
            ],
        ];

        $result = new AuditResult(
            10,
            'Przykładowy tytuł',
            350,
            true,
            $issues
        );

        $this->assertSame(
            [
                'Tytuł' => 1,
                'Obrazy' => 2,
                'Nagłówki' => 1,
            ],
            $result->get_issue_summary()
        );

        $this->assertSame(
            4,
            $result->get_issues_count()
        );
    }

    public function test_issue_summary_does_not_modify_issues(): void {

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

        $result = new AuditResult(
            10,
            'Przykładowy tytuł',
            150,
            false,
            $issues
        );

        $result->get_issue_summary();

        $this->assertSame(
            $issues,
            $result->issues
        );
    }

    public function test_supports_new_issue_categories(): void {

        $issues = [
            [
                'category' => 'Dostępność',
                'message' => 'Brak etykiety.',
                'penalty' => 5,
            ],
            [
                'category' => 'SEO',
                'message' => 'Problem z metadanymi.',
                'penalty' => 10,
            ],
        ];

        $result = new AuditResult(
            10,
            'Przykładowy tytuł',
            350,
            true,
            $issues
        );

        $this->assertSame(
            [
                'Dostępność' => 1,
                'SEO' => 1,
            ],
            $result->get_issue_summary()
        );
    }

    public function test_issue_summary_does_not_change_score(): void {

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

        $result = new AuditResult(
            10,
            'Przykładowy tytuł',
            150,
            false,
            $issues
        );

        $score_before = $result->get_score();

        $result->get_issue_summary();

        $score_after = $result->get_score();

        $this->assertSame(60, $score_before);

        $this->assertSame(
            $score_before,
            $score_after
        );
    }
}