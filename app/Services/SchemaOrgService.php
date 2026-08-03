<?php

namespace App\Services;

use App\Models\Webinar;
use App\Models\WebinarExtraDescription;

class SchemaOrgService
{
    /**
     * Merged locale copy: config defaults ← dashboard schema_settings.
     *
     * @return array<string, mixed>
     */
    public function settings(?string $locale = null): array
    {
        $locale = $this->normalizeLocale($locale ?? app()->getLocale());
        $defaults = config('schema.defaults.' . $locale)
            ?? config('schema.defaults.en', []);

        $stored = [];
        try {
            $stored = getSchemaSettings() ?: [];
        } catch (\Throwable $e) {
            $stored = [];
        }

        if (!is_array($stored)) {
            $stored = [];
        }

        $merged = $defaults;
        foreach ($stored as $key => $value) {
            if (is_string($value) && trim($value) !== '') {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public function origin(): string
    {
        return rtrim(url('/'), '/');
    }

    public function logoUrl(): string
    {
        $path = (string) config('schema.logo_path', '/store/1/Logos/albyan-institute-logo-512.png');

        return url($path);
    }

    public function organizationId(): string
    {
        return $this->origin() . config('schema.organization_id_fragment', '#organization');
    }

    public function logoId(): string
    {
        return $this->origin() . config('schema.logo_id_fragment', '#logo');
    }

    public function websiteId(): string
    {
        return $this->origin() . config('schema.website_id_fragment', '#website');
    }

    public function homeWebpageId(): string
    {
        return $this->origin() . config('schema.webpage_id_fragment', '#webpage');
    }

    /**
     * Map app locale to Schema.org inLanguage (ar → ar-AE).
     */
    public function inLanguage(?string $locale = null): string
    {
        $locale = $this->normalizeLocale($locale ?? app()->getLocale());

        $map = [
            'ar' => 'ar-AE',
            'en' => 'en-AE',
            'es' => 'es',
        ];

        return $map[$locale] ?? $locale;
    }

    /**
     * Convert webinar duration (minutes) to ISO-8601 duration.
     */
    public function minutesToIso8601Duration($minutes): ?string
    {
        $minutes = (int) $minutes;
        if ($minutes <= 0) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $remain = $minutes % 60;

        if ($hours > 0 && $remain === 0) {
            return 'PT' . $hours . 'H';
        }
        if ($hours > 0) {
            return 'PT' . $hours . 'H' . $remain . 'M';
        }

        return 'PT' . $remain . 'M';
    }

    /**
     * @return array<string, mixed>
     */
    public function organizationNode(bool $compact = false): array
    {
        $copy = $this->settings();
        $general = getGeneralSettings();
        $orgName = !empty($general['site_name']) ? $general['site_name'] : 'معهد البيان';
        $logoUrl = $this->logoUrl();

        if ($compact) {
            return [
                '@type' => 'EducationalOrganization',
                '@id' => $this->organizationId(),
                'name' => $orgName,
                'legalName' => $copy['legal_name'] ?? null,
                'url' => $this->origin() . '/',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $logoUrl,
                ],
            ];
        }

        $alternateNames = $this->parseLines($copy['alternate_names'] ?? '');
        $email = $this->resolveEmail();
        $telephones = $this->resolveTelephones();

        $node = [
            '@type' => 'EducationalOrganization',
            '@id' => $this->organizationId(),
            'name' => $orgName,
            'legalName' => $copy['legal_name'] ?? null,
            'url' => $this->origin() . '/',
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => $this->logoId(),
                'url' => $logoUrl,
                'contentUrl' => $logoUrl,
                'caption' => $copy['logo_caption'] ?? null,
            ],
            'image' => [
                '@id' => $this->logoId(),
            ],
            'description' => $copy['org_description'] ?? null,
            'email' => $email,
            'telephone' => $telephones,
            'address' => array_merge(['@type' => 'PostalAddress'], config('schema.address', [])),
            'areaServed' => config('schema.area_served'),
            'contactPoint' => $this->contactPoints($copy, $email, $telephones),
            'sameAs' => $this->sameAsLinks(),
        ];

        if (!empty($alternateNames)) {
            $node['alternateName'] = $alternateNames;
        }

        return $this->filterNulls($node);
    }

    /**
     * @return array<string, mixed>
     */
    public function websiteNode(): array
    {
        $general = getGeneralSettings();
        $orgName = !empty($general['site_name']) ? $general['site_name'] : 'معهد البيان';

        return [
            '@type' => 'WebSite',
            '@id' => $this->websiteId(),
            'url' => $this->origin() . '/',
            'name' => $orgName,
            'alternateName' => 'Albyan Institute',
            'publisher' => [
                '@id' => $this->organizationId(),
            ],
            'inLanguage' => ['ar-AE', 'en-AE'],
        ];
    }

    /**
     * Homepage @graph payload (without @context).
     *
     * @return list<array<string, mixed>>
     */
    public function homeGraph(?string $pageTitle = null, ?string $pageDescription = null): array
    {
        $copy = $this->settings();
        $seoHome = getSeoMetas('home') ?: [];

        $name = $copy['home_webpage_name']
            ?? ($pageTitle ?: ($seoHome['title'] ?? null))
            ?? null;
        $description = $copy['home_webpage_description']
            ?? ($pageDescription ?: ($seoHome['description'] ?? null))
            ?? null;

        $webpage = $this->filterNulls([
            '@type' => 'WebPage',
            '@id' => $this->homeWebpageId(),
            'url' => $this->origin() . '/',
            'name' => $name,
            'description' => $description,
            'isPartOf' => [
                '@id' => $this->websiteId(),
            ],
            'about' => [
                '@id' => $this->organizationId(),
            ],
            'primaryImageOfPage' => [
                '@id' => $this->logoId(),
            ],
            'inLanguage' => $this->inLanguage(),
        ]);

        return [
            $this->organizationNode(false),
            $this->websiteNode(),
            $webpage,
        ];
    }

    /**
     * Course page @graph payload (without @context).
     *
     * @return list<array<string, mixed>>
     */
    public function courseGraph(Webinar $course): array
    {
        $copy = $this->settings();
        $courseUrl = $course->getUrl();
        $courseId = $courseUrl . '#course';
        $webpageId = $courseUrl . '#webpage';
        $breadcrumbId = $courseUrl . '#breadcrumb';
        $offerId = $courseUrl . '#offer';

        $name = (string) $course->title;
        $description = $this->courseDescription($course);
        $image = $this->absoluteMediaUrl($course->getImage() ?: $course->getImageCover());
        $price = (float) $course->getPrice();
        $currencyCode = currency() ?: getDefaultCurrency() ?: 'AED';
        $durationMinutes = (int) ($course->duration ?? 0);
        $timeRequired = $this->minutesToIso8601Duration($durationMinutes);
        $workload = $this->formatWorkload($copy['course_workload_template'] ?? '{hours} ساعة تدريبية', $durationMinutes);

        $instructor = null;
        if (!empty($course->teacher)) {
            $instructor = $this->filterNulls([
                '@type' => 'Person',
                'name' => $course->teacher->full_name,
                'url' => url($course->teacher->getProfileUrl()),
            ]);
        }

        $startDate = null;
        if (!empty($course->start_date)) {
            $startDate = date('Y-m-d', (int) $course->start_date);
        }

        $teaches = $this->learningOutcomes($course);
        $level = $this->educationalLevel($course);

        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $copy['breadcrumb_home_name'] ?? 'Home',
                'item' => $this->origin() . '/',
            ],
        ];

        $position = 2;
        if (!empty($course->category)) {
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $course->category->title,
                'item' => url($course->category->getUrl()),
            ];
            $position++;
        }

        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
            'item' => $courseUrl,
        ];

        $onlineSuffix = $copy['online_instance_name_suffix'] ?? 'Live Online';
        $onsiteSuffix = $copy['onsite_instance_name_suffix'] ?? 'Onsite in Dubai';

        $onlineInstance = $this->filterNulls([
            '@type' => 'CourseInstance',
            '@id' => $courseUrl . '#online-instance',
            'name' => $name . ' – ' . $onlineSuffix,
            'courseMode' => ['online', 'synchronous'],
            'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode',
            'courseWorkload' => $workload,
            'startDate' => $startDate,
            'instructor' => $instructor,
        ]);

        $onsiteInstance = $this->filterNulls([
            '@type' => 'CourseInstance',
            '@id' => $courseUrl . '#onsite-instance',
            'name' => $name . ' – ' . $onsiteSuffix,
            'courseMode' => ['onsite', 'part-time'],
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'courseWorkload' => $workload,
            'startDate' => $startDate,
            'location' => [
                '@type' => 'Place',
                'name' => $copy['place_name'] ?? $name,
                'address' => array_merge(['@type' => 'PostalAddress'], config('schema.address', [])),
            ],
            'instructor' => $instructor,
        ]);

        $courseNode = $this->filterNulls([
            '@type' => 'Course',
            '@id' => $courseId,
            'url' => $courseUrl,
            'mainEntityOfPage' => [
                '@id' => $webpageId,
            ],
            'name' => $name,
            'description' => $description,
            'image' => $image,
            'provider' => [
                '@id' => $this->organizationId(),
            ],
            'inLanguage' => $this->inLanguage(),
            'educationalLevel' => $level,
            'learningResourceType' => $copy['learning_resource_type'] ?? 'Professional Training Course',
            'timeRequired' => $timeRequired,
            'teaches' => !empty($teaches) ? $teaches : null,
            'audience' => [
                '@type' => 'EducationalAudience',
                'educationalRole' => 'student',
            ],
            'isAccessibleForFree' => $price <= 0,
            'offers' => [
                '@type' => 'Offer',
                '@id' => $offerId,
                'url' => $courseUrl,
                'price' => (string) (int) round($price),
                'priceCurrency' => $currencyCode,
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@id' => $this->organizationId(),
                ],
            ],
            'hasCourseInstance' => [$onlineInstance, $onsiteInstance],
        ]);

        $webpage = $this->filterNulls([
            '@type' => 'WebPage',
            '@id' => $webpageId,
            'url' => $courseUrl,
            'name' => $name,
            'description' => $description,
            'isPartOf' => [
                '@id' => $this->websiteId(),
            ],
            'mainEntity' => [
                '@id' => $courseId,
            ],
            'breadcrumb' => [
                '@id' => $breadcrumbId,
            ],
            'inLanguage' => $this->inLanguage(),
        ]);

        $breadcrumb = [
            '@type' => 'BreadcrumbList',
            '@id' => $breadcrumbId,
            'itemListElement' => $breadcrumbItems,
        ];

        return [
            $this->organizationNode(true),
            $webpage,
            $breadcrumb,
            $courseNode,
        ];
    }

    /**
     * Full JSON-LD document with @context + @graph.
     *
     * @param list<array<string, mixed>> $graph
     * @return array<string, mixed>
     */
    public function document(array $graph): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values($graph),
        ];
    }

    public function toJson(array $document): string
    {
        return json_encode($document, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    }

    private function courseDescription(Webinar $course): string
    {
        $seo = trim(strip_tags((string) ($course->seo_description ?? '')));
        if ($seo !== '') {
            return $seo;
        }

        $body = trim(strip_tags((string) ($course->description ?? '')));

        return \Illuminate\Support\Str::limit($body, 300, '…');
    }

    /**
     * @return list<string>
     */
    private function learningOutcomes(Webinar $course): array
    {
        $items = [];
        $extras = $course->webinarExtraDescription ?? collect();

        foreach ($extras as $row) {
            if (($row->type ?? null) !== WebinarExtraDescription::$LEARNING_MATERIALS) {
                continue;
            }
            $value = trim(strip_tags((string) ($row->value ?? '')));
            if ($value !== '') {
                $items[] = $value;
            }
        }

        return array_values(array_unique($items));
    }

    private function educationalLevel(Webinar $course): ?string
    {
        $rows = $course->filterOptions ?? collect();
        $optionIds = $rows->pluck('filter_option_id')->filter()->unique()->values()->all();
        if ($optionIds === []) {
            return null;
        }

        $options = \App\Models\FilterOption::query()
            ->whereIn('id', $optionIds)
            ->get();

        foreach ($options as $option) {
            $title = trim((string) ($option->title ?? ''));
            if ($title === '') {
                continue;
            }
            $hay = mb_strtolower($title);
            if (
                str_contains($hay, 'level')
                || str_contains($hay, 'مستوى')
                || str_contains($hay, 'مبتدئ')
                || str_contains($hay, 'متوسط')
                || str_contains($hay, 'متقدم')
                || str_contains($hay, 'beginner')
                || str_contains($hay, 'intermediate')
                || str_contains($hay, 'advanced')
            ) {
                return $title;
            }
        }

        return null;
    }

    private function formatWorkload(string $template, int $minutes): ?string
    {
        if ($minutes <= 0) {
            return null;
        }

        $hours = max(1, (int) round($minutes / 60));

        return str_replace(
            ['{hours}', '{minutes}'],
            [(string) $hours, (string) $minutes],
            $template
        );
    }

    private function absoluteMediaUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return url('/' . ltrim($path, '/'));
    }

    /**
     * @return list<string>
     */
    private function parseLines(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $out[] = $line;
            }
        }

        return array_values(array_unique($out));
    }

    private function resolveEmail(): string
    {
        $email = getSiteContactEmail();
        if (!empty($email)) {
            return $email;
        }

        $general = getGeneralSettings();
        if (!empty($general['site_email'])) {
            return $general['site_email'];
        }

        return (string) config('schema.email', 'info@albyaninstitute.net');
    }

    /**
     * @return list<string>
     */
    private function resolveTelephones(): array
    {
        $phones = [];
        $contact = getContactPageSettings();
        $raw = (string) ($contact['phones'] ?? '');
        foreach (preg_split('/[,;\n]+/', $raw) ?: [] as $item) {
            $digits = preg_replace('/[^\d+]/', '', trim($item));
            if ($digits !== '') {
                if ($digits[0] !== '+') {
                    $digits = '+' . ltrim($digits, '0');
                }
                $phones[] = $digits;
            }
        }

        if (empty($phones)) {
            $phones = config('schema.telephones', []);
        }

        return array_values(array_unique($phones));
    }

    /**
     * @param array<string, mixed> $copy
     * @param list<string> $telephones
     * @return list<array<string, mixed>>
     */
    private function contactPoints(array $copy, string $email, array $telephones): array
    {
        $points = [];
        $admissionsPhone = $telephones[1] ?? ($telephones[0] ?? null);

        if (!empty($admissionsPhone) || !empty($email)) {
            $points[] = $this->filterNulls([
                '@type' => 'ContactPoint',
                'contactType' => $copy['admissions_contact_type'] ?? 'Admissions',
                'telephone' => $admissionsPhone,
                'email' => $email,
                'availableLanguage' => ['Arabic', 'English'],
                'areaServed' => 'AE',
            ]);
        }

        $whatsapp = config('schema.whatsapp', []);
        if (!empty($whatsapp['telephone'])) {
            $points[] = $this->filterNulls([
                '@type' => 'ContactPoint',
                'contactType' => $copy['whatsapp_contact_type'] ?? 'WhatsApp Admissions',
                'telephone' => $whatsapp['telephone'],
                'url' => $whatsapp['url'] ?? null,
                'availableLanguage' => ['Arabic', 'English'],
                'areaServed' => 'AE',
            ]);
        }

        return $points;
    }

    /**
     * @return list<string>
     */
    private function sameAsLinks(): array
    {
        $links = config('schema.same_as', []);
        foreach (getSocials() as $social) {
            if (!empty($social['link'])) {
                $links[] = $social['link'];
            }
        }

        return array_values(array_unique(array_filter($links)));
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = strtolower(trim($locale));
        if (str_contains($locale, '_')) {
            $locale = explode('_', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }

        return $locale !== '' ? $locale : 'en';
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterNulls(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if (is_array($value)) {
                $value = $this->filterNulls($value);
                if ($value === []) {
                    continue;
                }
            }
            $out[$key] = $value;
        }

        return $out;
    }
}
