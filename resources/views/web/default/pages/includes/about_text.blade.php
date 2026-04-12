@php
    /* Institute intro copy: Admin → Settings → Personalization → Other (others_personalization). */
    $layout = $layout ?? 'default';
    $aboutTitle = trim((string) (getOthersPersonalizationSettings('institute_about_title') ?? ''));
    if ($aboutTitle === '') {
        $aboutTitle = 'عن المعهد';
    }
    $aboutBody = trim((string) (getOthersPersonalizationSettings('institute_about_text') ?? ''));
    if ($aboutBody === '') {
        $aboutBody = 'معهد البيان للخدمات التعليمية يقدم تجربة تعليمية متميزة مع نخبة من المحاضرين والخبراء في مختلف المجالات. '
            . 'يقدم المعهد مئات الدبلومات التدريبية الاحترافية المصممة لتلبية احتياجات سوق العمل، مع خيارات مرنة في الحضور من مقر المعهد أو الدراسة أون لاين. '
            . 'يمنح المعهد شهادات معتمدة محلياً ودولياً تعزز من مكانتك المهنية وينظم حفل تخرج سنوي ضخم لتكريم أعداد كبيرة من خريجي المعهد بمختلف التخصصات بحضور شخصيات هامة. '
            . 'انضم إلى معهد البيان للارتقاء بمسارك المهني.';
    }
    $rawFooter = getOthersPersonalizationSettings('institute_about_footer');
    $aboutFooter = null;
    if (!is_null($rawFooter)) {
        $aboutFooter = trim((string) $rawFooter);
    }
    if ($layout === 'home_blockquote' && is_null($rawFooter)) {
        $aboutFooter = 'معهد البيان للخدمات التعليمية';
    }
@endphp

@if($layout === 'home_blockquote')
    {{-- Homepage: blockquote strip (same content as about/contact, optional footer line). --}}
    <blockquote class="blockquote text-center p-4 border-start border-4" style="min-height: 200px; width: 100%;">
        <h1 style="margin-bottom: 1rem;">{{ $aboutTitle }}</h1>
        <p class="mb-0" style="font-size: 16px; max-width: 768px; margin: auto; line-height: 1.6; min-height: 120px; height: auto; overflow: hidden;">
            {!! nl2br(e($aboutBody)) !!}
        </p>
        @if(!is_null($aboutFooter) && $aboutFooter !== '')
            <footer class="blockquote-footer mt-2" style="margin-top: 1rem !important;">{{ $aboutFooter }}</footer>
        @endif
    </blockquote>
@else
    {{-- About & contact: centered section --}}
    <div class="col-12 contact-us-about text-center">
        <h1 class="section-title-bg p-2">{{ $aboutTitle }}</h1>
        <p class="text-center">
            {!! nl2br(e($aboutBody)) !!}
        </p>
    </div>
@endif
