{{-- Homepage grid card + call / WhatsApp (Arabic diploma inquiry message) --}}
@php
    $waDigits = !empty($diplomaLandingWhatsapp) ? preg_replace('/\D/', '', $diplomaLandingWhatsapp) : '';
    $callNumber = $diplomaLandingCall ?? '';
    $courseTitle = $webinar->title ?? '';
    $waMessage = rawurlencode('مرحبًا، أرغب بالاستفسار عن دبلومة ' . $courseTitle);
@endphp
@include('web.default.includes.webinar.grid-card', ['webinar' => $webinar])
@if(!empty($waDigits) || !empty($callNumber))
    <div class="diploma-landing-course-actions d-flex justify-content-center flex-wrap gap-2 mt-15 px-3 pb-3">
        @if(!empty($callNumber))
            <a href="tel:{{ $callNumber }}" class="btn btn-outline-primary btn-sm">
                <i data-feather="phone" width="16" height="16"></i>
                {{ trans('update.call_us') }}
            </a>
        @endif
        @if(!empty($waDigits))
            <a href="https://wa.me/{{ $waDigits }}?text={{ $waMessage }}" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                <i class="fab fa-whatsapp"></i>
                {{ trans('public.whatsapp') }}
            </a>
        @endif
    </div>
@endif
