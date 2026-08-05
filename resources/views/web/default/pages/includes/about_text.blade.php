@php
    /* Institute intro: Admin → Settings → Personalization → Other (others_personalization). */
    $layout = $layout ?? 'default';
    $aboutTitle = trim((string) (getOthersPersonalizationSettings('institute_about_title') ?? ''));
    $aboutBody = trim((string) (getOthersPersonalizationSettings('institute_about_text') ?? ''));
    $rawFooter = getOthersPersonalizationSettings('institute_about_footer');
    $aboutFooter = !is_null($rawFooter) ? trim((string) $rawFooter) : null;
    $showInstituteBlock = ($aboutTitle !== '' || $aboutBody !== '');
    $advisorLeadUrl = getLeadGenerationFormUrl();
    $ctaClasses = settingOrTrans(getOthersPersonalizationSettings('institute_about_cta_classes') ?? '', 'site.explore_courses_diplomas');
    $ctaAdvisor = settingOrTrans(getOthersPersonalizationSettings('institute_about_cta_advisor') ?? '', 'site.contact_training_advisor');
@endphp

@if($showInstituteBlock)
    @if($layout === 'home_blockquote')
        {{-- Homepage: blockquote strip (optional footer line from settings only). --}}
        <blockquote class="blockquote text-center p-4 border-start border-4" style="height: 390px; width: 100%;">
            @if($aboutTitle !== '')
                <h2 style="margin-bottom: 1rem;">{{ $aboutTitle }}</h2>
            @endif
            @if($aboutBody !== '')
                <p class="mb-0" style="font-size: 16px; max-width: 768px; margin: auto; line-height: 1.6; min-height: 120px; height: auto; overflow: hidden;">
                    {!! nl2br(e($aboutBody)) !!}
                </p>
            @endif
            @if(!is_null($aboutFooter) && $aboutFooter !== '')
                <footer class="blockquote-footer mt-2" style="margin-top: 1rem !important;">{{ $aboutFooter }}</footer>
            @endif
            {{-- Dual CTAs under institute intro (admin-translatable) --}}
            <div class="d-flex flex-wrap justify-content-center align-items-center mt-4" style="gap: 12px;">
                <a href="/classes" class="btn btn-primary">{{ $ctaClasses }}</a>
                <a href="{{ $advisorLeadUrl }}" class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer">{{ $ctaAdvisor }}</a>
            </div>
        </blockquote>
    @else
        {{-- About & contact: centered section --}}
        <div class="col-12 contact-us-about text-center">
            @if($aboutTitle !== '')
                <h1 class="section-title-bg p-2">{{ $aboutTitle }}</h1>
            @endif
            @if($aboutBody !== '')
                <p class="text-center">
                    {!! nl2br(e($aboutBody)) !!}
                </p>
            @endif
        </div>
    @endif
@endif
