{{-- Organization + BreadcrumbList JSON-LD for the about page --}}
@php
    $schemaService = app(\App\Services\SchemaOrgService::class);
    $organization = array_merge(
        ['@context' => 'https://schema.org'],
        $schemaService->organizationNode(false)
    );

    $orgName = $organization['name'] ?? (getGeneralSettings('site_name') ?: 'معهد البيان');
    $orgUrl = url('/');
    $aboutPageUrl = url()->current();

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
<script type="application/ld+json">{!! $schemaService->toJson($organization) !!}</script>
<script type="application/ld+json">{!! $schemaService->toJson($breadcrumb) !!}</script>
