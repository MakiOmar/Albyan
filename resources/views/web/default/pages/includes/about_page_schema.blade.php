{{-- Organization + BreadcrumbList JSON-LD for the about page --}}
@php
    $orgName = !empty($generalSettings['site_name']) ? $generalSettings['site_name'] : 'أكاديمية البيان';
    $orgUrl = url('/');
    $aboutPageUrl = url()->current();
    $logoUrl = !empty($generalSettings['logo']) ? url($generalSettings['logo']) : (!empty($generalSettings['fav_icon']) ? url($generalSettings['fav_icon']) : $orgUrl);
    $schemaSameAs = $schemaSameAs ?? [];
    $schemaPhones = $schemaPhones ?? [];
    $schemaEmails = $schemaEmails ?? [];

    $organization = [
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => $orgName,
        'url' => $orgUrl,
        'logo' => $logoUrl,
        'description' => $pageDescription ?? '',
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'United Arab Emirates',
        ],
        'address' => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Dubai',
            'addressCountry' => 'AE',
        ],
    ];

    if (!empty($schemaSameAs)) {
        $organization['sameAs'] = array_values($schemaSameAs);
    }

    $contactPoints = [];
    foreach ($schemaPhones as $phone) {
        $contactPoints[] = [
            '@type' => 'ContactPoint',
            'telephone' => $phone,
            'contactType' => 'customer service',
            'areaServed' => 'AE',
            'availableLanguage' => ['Arabic', 'English'],
        ];
    }
    foreach ($schemaEmails as $email) {
        $contactPoints[] = [
            '@type' => 'ContactPoint',
            'email' => $email,
            'contactType' => 'customer service',
            'areaServed' => 'AE',
            'availableLanguage' => ['Arabic', 'English'],
        ];
    }
    if (!empty($contactPoints)) {
        $organization['contactPoint'] = $contactPoints;
    }

    $breadcrumb = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => $orgName,
                'item' => $orgUrl,
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => trans('site.about_breadcrumb_title'),
                'item' => $aboutPageUrl,
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($organization, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
