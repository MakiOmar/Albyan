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
@endphp

<section class="home-sections container mt-40 mb-40">
    <div class="border rounded p-30 text-center">
        <h2 class="section-title">{{ $helpTitle }}</h2>
        <div class="d-flex flex-wrap justify-content-center align-items-center mt-25" style="gap: 12px;">
            <a href="{{ $advisorUrl }}" class="btn btn-primary" target="_blank" rel="noopener noreferrer">{{ trans('site.contact_training_advisor') }}</a>
            <a href="{{ $classesUrl }}" class="btn btn-outline-primary">{{ trans('site.explore_courses_diplomas') }}</a>
            @if($whatsapp !== '')
                <a href="{{ $whatsapp }}" class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer">{{ trans('update.help_cta_whatsapp') }}</a>
            @endif
            @if($phone !== '')
                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}"
                   class="btn btn-outline-primary"
                   title="{{ $phone }}">{{ trans('update.help_cta_call_us') }}</a>
            @endif
            @if($mapUrl !== '')
                <a href="{{ $mapUrl }}" class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer">{{ trans('update.help_cta_map') }}</a>
            @endif
        </div>
        @if($hours !== '')
            <p class="font-14 text-gray mt-20 mb-0">{{ $hours }}</p>
        @endif
    </div>
</section>
