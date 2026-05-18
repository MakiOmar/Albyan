{{-- Course card with call + WhatsApp (diploma inquiry message in Arabic) --}}
@php
    $waDigits = !empty($diplomaLandingWhatsapp) ? preg_replace('/\D/', '', $diplomaLandingWhatsapp) : '';
    $callNumber = $diplomaLandingCall ?? '';
    $courseTitle = $webinar->title ?? '';
    $waMessage = rawurlencode('مرحبًا، أرغب بالاستفسار عن دبلومة ' . $courseTitle);
@endphp
<div class="diploma-landing-course-card">
    @include('web.default.includes.webinar.grid-card', ['webinar' => $webinar])
    @if(!empty($waDigits) || !empty($callNumber))
        <div class="diploma-landing-course-actions">
            @if(!empty($callNumber))
                <a href="tel:{{ $callNumber }}" class="dl-btn dl-btn-call">
                    <i data-feather="phone" width="16" height="16"></i>
                    {{ trans('update.call_us') }}
                </a>
            @endif
            @if(!empty($waDigits))
                <a href="https://wa.me/{{ $waDigits }}?text={{ $waMessage }}" target="_blank" rel="noopener" class="dl-btn dl-btn-whatsapp">
                    <i class="fab fa-whatsapp"></i>
                    {{ trans('public.whatsapp') }}
                </a>
            @endif
        </div>
    @endif
</div>
