<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\App;
use Tests\TestCase;

class WebLayoutIsRtlTest extends TestCase
{
    public function test_ltr_locale_when_rtl_languages_configured_even_if_rtl_layout_enabled(): void
    {
        App::setLocale('en');

        $settings = [
            'rtl_languages' => ['AR'],
            'rtl_layout' => 1,
        ];

        $this->assertFalse(web_layout_is_rtl($settings));
    }

    public function test_rtl_locale_matches_rtl_languages_list(): void
    {
        App::setLocale('ar');

        $settings = [
            'rtl_languages' => ['AR', 'EN'],
            'rtl_layout' => 0,
        ];

        $this->assertTrue(web_layout_is_rtl($settings));
    }

    public function test_rtl_languages_codes_are_normalized_to_uppercase(): void
    {
        App::setLocale('ar');

        $settings = [
            'rtl_languages' => ['ar'],
            'rtl_layout' => 0,
        ];

        $this->assertTrue(web_layout_is_rtl($settings));
    }

    public function test_legacy_rtl_layout_when_rtl_languages_empty(): void
    {
        App::setLocale('en');

        $settings = [
            'rtl_languages' => [],
            'rtl_layout' => 1,
        ];

        $this->assertTrue(web_layout_is_rtl($settings));
    }

    public function test_ltr_when_rtl_layout_off_and_rtl_languages_empty(): void
    {
        App::setLocale('en');

        $settings = [
            'rtl_languages' => [],
            'rtl_layout' => 0,
        ];

        $this->assertFalse(web_layout_is_rtl($settings));
    }
}
