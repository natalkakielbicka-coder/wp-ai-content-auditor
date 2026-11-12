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
    }

    public function test_result_with_issues(): void {

        $result = new AuditResult(
            2,
            'Krótki wpis',
            50,
            false,
            [
                'Treść jest za krótka.',
                'Brak obrazka wyróżniającego.',
            ]
        );

        $this->assertTrue($result->has_issues());

        $this->assertSame(2, $result->get_issues_count());
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
}
