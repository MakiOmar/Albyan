<?php

namespace Database\Seeders;

use App\Http\Controllers\Web\HomeController;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

/**
 * Seeds English (en) copy for home_content_blocks from lang/en defaults.
 *
 * - Does not touch Arabic or other locales
 * - Fills only missing / empty English text fields
 * - Copies shared structure (images, links, layout, IDs) from Arabic when EN lacks them
 *
 * php artisan db:seed --class=HomeContentBlocksEnglishSeeder
 */
class HomeContentBlocksEnglishSeeder extends Seeder
{
    private const LOCALE = 'en';

    private const STRUCTURAL_PATHS = [
        'trending_categories.layout',
        'trending_categories.all_button_link',
        'trending_categories.card_border_radius',
        'trending_categories.card_shadow',
        'trust_badges.background',
        'trust_badges.side_image',
        'trust_badges.button1.link',
        'trust_badges.button2.link',
        'trust_badges.1.image',
        'trust_badges.2.image',
        'trust_badges.3.image',
        'trust_badges.4.image',
        'trust_badges.5.image',
        'training_domains.category_ids',
        'training_modality.in_person.link',
        'training_modality.in_person.image',
        'training_modality.in_person.features.1.image',
        'training_modality.in_person.features.2.image',
        'training_modality.in_person.features.3.image',
        'training_modality.online.link',
        'training_modality.online.image',
        'training_modality.online.features.1.image',
        'training_modality.online.features.2.image',
        'training_modality.online.features.3.image',
        'why_albyan.background',
        'why_albyan.image',
        'why_albyan.overlay_opacity',
        'help_cta_band.whatsapp',
        'help_cta_band.phone',
        'help_cta_band.map_url',
        'help_cta_band.classes_url',
    ];

    public function run(): void
    {
        $setting = Setting::firstOrCreate(
            ['name' => Setting::$homeContentBlocksName],
            [
                'page' => 'personalization',
                'updated_at' => time(),
            ]
        );

        $existingEn = SettingTranslation::where('setting_id', $setting->id)
            ->where('locale', self::LOCALE)
            ->first();

        $enValue = $this->decodeValue($existingEn?->value);

        $donor = SettingTranslation::where('setting_id', $setting->id)
            ->where('locale', '!=', self::LOCALE)
            ->orderByRaw("CASE WHEN locale = 'ar' THEN 0 ELSE 1 END")
            ->first();

        $donorValue = $this->decodeValue($donor?->value);

        // Shared assets / config from Arabic (or other locale) without copying Arabic copy
        foreach (self::STRUCTURAL_PATHS as $path) {
            $donorField = $this->getPath($donorValue, $path);
            if ($donorField === null || $this->isBlank($donorField)) {
                continue;
            }
            if ($this->isBlank($this->getPath($enValue, $path))) {
                $this->setPath($enValue, $path, $donorField);
            }
        }

        $previousLocale = App::getLocale();
        App::setLocale(self::LOCALE);

        try {
            $defaults = $this->englishTextDefaults();
        } finally {
            App::setLocale($previousLocale);
        }

        $before = json_encode($enValue);
        $enValue = $this->fillMissing($enValue, $defaults);
        $after = json_encode($enValue);

        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $setting->id,
                'locale' => self::LOCALE,
            ],
            [
                'value' => $after,
            ]
        );

        cache()->forget('settings.' . Setting::$homeContentBlocksName);
        HomeController::clearHomePageCache();

        $filled = $before !== $after;
        $this->command?->info($filled
            ? 'English home_content_blocks seeded (missing fields filled from lang/en).'
            : 'English home_content_blocks already complete — no changes.');
    }

    /**
     * Text fields that map to lang/en via the admin home_content_blocks form.
     */
    private function englishTextDefaults(): array
    {
        $sectionTitleKeys = [
            'featured_classes' => [
                'title' => 'home.featured_classes',
                'hint' => 'home.featured_classes_hint',
                'view_all' => 'home.view_all',
                'details_cta' => 'site.program_details',
                'inquire_cta' => 'site.inquire_now',
            ],
            'latest_classes' => [
                'title' => 'home.latest_webinars',
                'hint' => 'home.latest_webinars_hint',
                'view_all' => 'home.view_all',
            ],
            'latest_bundles' => [
                'title' => 'update.latest_bundles',
                'hint' => 'update.latest_bundles_hint',
                'view_all' => 'home.view_all',
            ],
            'upcoming_courses' => [
                'title' => 'update.upcoming_courses',
                'hint' => 'update.upcoming_courses_home_section_hint',
                'view_all' => 'home.view_all',
            ],
            'best_rates' => [
                'title' => 'home.best_rates',
                'hint' => 'home.best_rates_hint',
                'view_all' => 'home.view_all',
            ],
            'best_sellers' => [
                'title' => 'home.best_sellers',
                'hint' => 'home.best_sellers_hint',
                'view_all' => 'home.view_all',
            ],
            'discount_classes' => [
                'title' => 'home.discount_classes',
                'hint' => 'home.discount_classes_hint',
                'view_all' => 'home.view_all',
            ],
            'free_classes' => [
                'title' => 'home.free_classes',
                'hint' => 'home.free_classes_hint',
                'view_all' => 'home.view_all',
            ],
            'store_products' => [
                'title' => 'update.store_products',
                'hint' => 'update.store_products_hint',
                'view_all' => 'update.all_products',
            ],
            'category_courses' => [
                'view_all' => 'home.view_all',
            ],
            'testimonials' => [
                'title' => 'home.testimonials',
                'hint' => 'home.testimonials_hint',
                'show_more' => 'site.show_more_ellipsis',
                'show_less' => 'site.show_less_ellipsis',
            ],
            'subscribes' => [
                'title' => 'home.subscribe_now',
                'hint' => 'home.subscribe_now_hint',
            ],
            'instructors' => [
                'title' => 'home.instructors',
                'hint' => 'home.instructors_hint',
                'view_all' => 'home.all_instructors',
            ],
            'organizations' => [
                'title' => 'home.organizations',
                'hint' => 'home.organizations_hint',
                'view_all' => 'home.all_organizations',
            ],
            'blog' => [
                'title' => 'home.blog',
                'hint' => 'home.blog_hint',
                'view_all' => 'home.all_blog',
            ],
            'faq_section' => [
                'title' => 'home.faq_section_title',
            ],
        ];

        $sectionTitles = [];
        foreach ($sectionTitleKeys as $sectionKey => $fields) {
            foreach ($fields as $field => $transKey) {
                $sectionTitles[$sectionKey][$field] = (string) trans($transKey);
            }
        }

        $whyItems = trans('update.why_albyan_default_items');
        if (is_array($whyItems)) {
            $whyItems = implode("\n", $whyItems);
        }

        return [
            'locale' => self::LOCALE,
            'section_titles' => $sectionTitles,
            'google_rating' => [
                'title' => (string) trans('site.albyan_institute_full_name'),
                'based_on' => (string) trans('update.home_google_rating_based_on_default'),
                'cta' => (string) trans('site.rate_us_on_google'),
            ],
            'wp_blog' => [
                'title' => (string) trans('update.wp_blog_section_title'),
                'hint' => (string) trans('update.wp_blog_section_hint'),
                'view_all' => (string) trans('update.wp_blog_section_all'),
                'members_only' => (string) trans('update.wp_blog_members_only'),
            ],
            'trending_categories' => [
                'title' => (string) trans('home.trending_categories'),
                'hint' => (string) trans('home.trending_categories_hint'),
                'all_button_title' => (string) trans('public.all_categories'),
                'course_label' => (string) trans('product.course'),
            ],
            'trust_badges' => [
                'button1' => [
                    'title' => (string) trans('site.contact_training_advisor'),
                ],
                'button2' => [
                    'title' => (string) trans('site.explore_courses_diplomas'),
                ],
                1 => ['title' => (string) trans('update.trust_badge_licensed')],
                2 => ['title' => (string) trans('update.trust_badge_hybrid')],
                3 => ['title' => (string) trans('update.trust_badge_specialties')],
                4 => ['title' => (string) trans('update.trust_badge_trainers')],
                5 => ['title' => (string) trans('update.trust_badge_certificate')],
            ],
            'training_domains' => [
                'title' => (string) trans('update.training_domains_title_default'),
                'all_button_title' => (string) trans('public.all_categories'),
                'empty_message' => (string) trans('update.training_domains_empty'),
            ],
            'training_modality' => [
                'title' => (string) trans('update.training_modality_title_default'),
                'in_person' => [
                    'title' => (string) trans('update.modality_in_person'),
                ],
                'online' => [
                    'title' => (string) trans('update.modality_online'),
                ],
            ],
            'why_albyan' => [
                'title' => (string) trans('update.why_albyan_title_default'),
                'items' => (string) $whyItems,
            ],
            'help_cta_band' => [
                'title' => (string) trans('update.help_cta_band_title_default'),
                'advisor_button' => (string) trans('site.contact_training_advisor'),
                'classes_button' => (string) trans('site.explore_courses_diplomas'),
                'whatsapp_button' => (string) trans('update.help_cta_whatsapp'),
                'call_button' => (string) trans('update.help_cta_call_us'),
                'map_button' => (string) trans('update.help_cta_map'),
            ],
        ];
    }

    private function fillMissing(array $target, array $source): array
    {
        foreach ($source as $key => $value) {
            if (is_array($value)) {
                $current = (isset($target[$key]) && is_array($target[$key])) ? $target[$key] : [];
                $target[$key] = $this->fillMissing($current, $value);
                continue;
            }

            if ($this->isBlank($target[$key] ?? null)) {
                $target[$key] = $value;
            }
        }

        return $target;
    }

    private function decodeValue(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function isBlank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return $value === [];
        }

        return false;
    }

    private function getPath(array $data, string $path): mixed
    {
        $segments = explode('.', $path);
        $cursor = $data;

        foreach ($segments as $segment) {
            if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
                return null;
            }
            $cursor = $cursor[$segment];
        }

        return $cursor;
    }

    private function setPath(array &$data, string $path, mixed $value): void
    {
        $segments = explode('.', $path);
        $cursor = &$data;

        foreach ($segments as $index => $segment) {
            if ($index === count($segments) - 1) {
                $cursor[$segment] = $value;
                return;
            }

            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }

            $cursor = &$cursor[$segment];
        }
    }
}
