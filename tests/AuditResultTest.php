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
}