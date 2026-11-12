<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use WPAuditor\SeoMetaAnalyzer;

if (!function_exists('get_post_meta')) {
    function get_post_meta(
        int $post_id,
        string $key,
        bool $single = false
    ): mixed {
        return $GLOBALS['seo_test_meta'][$post_id][$key] ?? '';
    }
}

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SeoMetaAnalyzerTest extends TestCase {

    private SeoMetaAnalyzer $analyzer;

    protected function setUp(): void {
        $this->analyzer = new SeoMetaAnalyzer();
        $GLOBALS['seo_test_meta'] = [];
    }

    private function enable_yoast(): void {
        define('WPSEO_VERSION', 'test');
    }

    public function test_returns_unavailable_without_yoast(): void {

        $result = $this->analyzer->analyze(10);

        $this->assertSame('none', $result['provider']);
        $this->assertSame('unavailable', $result['title_status']);
        $this->assertSame('unavailable', $result['description_status']);
        $this->assertSame(0, $result['title_length']);
        $this->assertSame(0, $result['description_length']);
    }

    public function test_uses_defaults_for_empty_metadata(): void {

        $this->enable_yoast();

        $result = $this->analyzer->analyze(10);

        $this->assertSame('yoast', $result['provider']);
        $this->assertSame('default', $result['title_status']);
        $this->assertSame('default', $result['description_status']);
        $this->assertSame('', $result['title']);
        $this->assertSame('', $result['description']);
    }

    public function test_reads_custom_seo_title(): void {

        $this->enable_yoast();

        $GLOBALS['seo_test_meta'][10] = [
            '_yoast_wpseo_title' => 'Poradnik WordPress',
        ];

        $result = $this->analyzer->analyze(10);

        $this->assertSame('custom', $result['title_status']);
        $this->assertSame('Poradnik WordPress', $result['title']);
        $this->assertSame(18, $result['title_length']);
    }

    public function test_reads_custom_meta_description(): void {

        $this->enable_yoast();

        $GLOBALS['seo_test_meta'][10] = [
            '_yoast_wpseo_metadesc' => 'Opis testowy strony.',
        ];

        $result = $this->analyzer->analyze(10);

        $this->assertSame('custom', $result['description_status']);
        $this->assertSame('Opis testowy strony.', $result['description']);
        $this->assertSame(20, $result['description_length']);
    }

    public function test_trims_whitespace(): void {

        $this->enable_yoast();

        $GLOBALS['seo_test_meta'][10] = [
            '_yoast_wpseo_title' => '  Mój tytuł  ',
            '_yoast_wpseo_metadesc' => '   ',
        ];

        $result = $this->analyzer->analyze(10);

        $this->assertSame('Mój tytuł', $result['title']);
        $this->assertSame('custom', $result['title_status']);
        $this->assertSame('default', $result['description_status']);
        $this->assertSame(0, $result['description_length']);
    }

    public function test_counts_polish_characters(): void {

        $this->enable_yoast();

        $GLOBALS['seo_test_meta'][10] = [
            '_yoast_wpseo_title' => 'Żółć',
        ];

        $result = $this->analyzer->analyze(10);

        $this->assertSame(4, $result['title_length']);
    }

    public function test_ignores_non_string_values(): void {

        $this->enable_yoast();

        $GLOBALS['seo_test_meta'][10] = [
            '_yoast_wpseo_title' => ['Niepoprawna wartość'],
            '_yoast_wpseo_metadesc' => 123,
        ];

        $result = $this->analyzer->analyze(10);

        $this->assertSame('', $result['title']);
        $this->assertSame('', $result['description']);
        $this->assertSame('default', $result['title_status']);
        $this->assertSame('default', $result['description_status']);
    }

    public function test_keeps_yoast_template_variables(): void {

        $this->enable_yoast();

        $GLOBALS['seo_test_meta'][10] = [
            '_yoast_wpseo_title' => '%%title%% %%sep%% %%sitename%%',
        ];

        $result = $this->analyzer->analyze(10);

        $this->assertSame(
            '%%title%% %%sep%% %%sitename%%',
            $result['title']
        );

        $this->assertSame('custom', $result['title_status']);
        $this->assertSame(
            mb_strlen('%%title%% %%sep%% %%sitename%%'),
            $result['title_length']
        );
    }

    public function test_detects_custom_description(): void {

        $this->enable_yoast();

        $GLOBALS['seo_test_meta'][10] = [
            '_yoast_wpseo_metadesc' => 'Własny opis SEO',
        ];

        $this->assertTrue(
            $this->analyzer->has_custom_description(10)
        );
    }

    public function test_detects_no_custom_description(): void {

        $this->enable_yoast();

        $this->assertFalse(
            $this->analyzer->has_custom_description(10)
        );
    }
}