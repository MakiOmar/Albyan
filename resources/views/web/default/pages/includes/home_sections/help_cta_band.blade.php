{{-- Final help CTA band --}}
@php
    $help = getHomeContentBlocksSettings('help_cta_band') ?? [];
    $helpTitle = trim((string) ($help['title'] ?? ''));
    if ($helpTitle === '') {
        $helpTitle = trans('update.help_cta_band_title_default');
    }
    $classesUrl = trim((string) ($help['classes_url'] ?? '')) ?: '/classes';
    $whatsapp = trim((string) ($help['whatsapp'] ?? ''));
    $phone = trim((string) ($help['phone'] ?? ''));
    $hours = trim((string) ($help['hours'] ?? ''));
    $mapUrl = trim((string) ($help['map_url'] ?? ''));
    $advisorUrl = getLeadGenerationFormUrl();

    // Button labels: admin override per locale, then lang defaults
    $advisorLabel = trim((string) ($help['advisor_button'] ?? '')) ?: trans('site.contact_training_advisor');
    $classesLabel = trim((string) ($help['classes_button'] ?? '')) ?: trans('site.explore_courses_diplomas');
    $whatsappLabel = trim((string) ($help['whatsapp_button'] ?? '')) ?: trans('update.help_cta_whatsapp');
    $callLabel = trim((string) ($help['call_button'] ?? '')) ?: trans('update.help_cta_call_us');
    $mapLabel = trim((string) ($help['map_button'] ?? '')) ?: trans('update.help_cta_map');
@endphp

<section class="home-sections container mt-40 mb-40">
    <div class="border rounded p-30 text-center">
        <h2 class="section-title">{{ $helpTitle }}</h2>
        <div class="d-flex flex-wrap justify-content-center align-items-center mt-25" style="gap: 12px;">
            <a href="{{ $advisorUrl }}" class="btn btn-primary" target="_blank" rel="noopener noreferrer">{{ $advisorLabel }}</a>
            <a href="{{ $classesUrl }}" class="btn btn-outline-primary">{{ $classesLabel }}</a>
            @if($whatsapp !== '')
                <a href="{{ $whatsapp }}" class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer">{{ $whatsappLabel }}</a>
            @endif
            @if($phone !== '')
                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
                   class="btn btn-outline-primary"
                   title="{{ $phone }}">{{ $callLabel }}</a>
            @endif
            @if($mapUrl !== '')
                <a href="{{ $mapUrl }}" class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer">{{ $mapLabel }}</a>
            @endif
        </div>
        @if($hours !== '')
            <p class="font-14 text-gray mt-20 mb-0">{{ $hours }}</p>
        @endif
    </div>
</section>
