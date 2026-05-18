@extends('web.default.layouts.diploma_landing')

@section('content')

    @include('web.default.landing.partials.video_section')

    @include('web.default.landing.partials.courses_grid')

    @include('web.default.landing.partials.certificates_gallery')

    @include('web.default.landing.partials.google_reviews')

    {{-- Lead generation form --}}
    <section class="dl-section dl-form-section" id="dl-register">
        <div class="dl-container">
            <h2 class="text-center text-white font-28 font-weight-bold mb-2">{{ $form->title }}</h2>
            <p class="text-center mb-4" style="opacity: 0.85;">املأ النموذج وسيتواصل معك فريق القبول في أقرب وقت</p>

            <div class="dl-form-wrap">
                @if(!empty($form->heading_title))
                    <h3 class="font-24 mb-2">{{ $form->heading_title }}</h3>
                @endif
                @if(!empty($form->description))
                    <div class="font-14 text-gray mb-3">{!! $form->description !!}</div>
                @endif

                @if(!empty($form->end_date))
                    <div class="alert alert-warning font-12 mb-3">
                        {{ trans('update.this_form_will_be_expired_on_date',['date' => dateTimeFormat($form->end_date, 'j M Y')]) }}
                    </div>
                @endif

                <form action="{{ url('/landing/diplomas/store') }}" method="post">
                    {{ csrf_field() }}
                    @include('web.default.forms.handle_field', ['fields' => $form->fields])
                    @include('web.default.includes.turnstile_widget')
                    <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 mt-4">
                        <button type="button" class="js-clear-form btn btn-outline-secondary flex-fill">{{ trans('update.clear_form') }}</button>
                        <button type="submit" class="btn btn-primary flex-fill font-weight-bold">{{ trans('update.submit_form') }}</button>
                    </div>
                </form>

                @php
                    $dlWa = $diplomaLandingWhatsapp ?? config('diploma_landing.whatsapp_number');
                    $dlWaDigits = !empty($dlWa) ? preg_replace('/\D/', '', $dlWa) : '';
                    $dlCall = $diplomaLandingCall ?? config('diploma_landing.call_number');
                @endphp
                <div class="mt-4 d-flex flex-column gap-2">
                    @if(!empty($dlWaDigits))
                        <a href="https://wa.me/{{ $dlWaDigits }}" target="_blank" rel="noopener" class="dl-btn dl-btn-whatsapp w-100">
                            <i class="fab fa-whatsapp"></i> {{ trans('update.contact_on_whatsapp') }}
                        </a>
                    @endif
                    @if(!empty($dlCall))
                        <a href="tel:{{ $dlCall }}" class="dl-btn dl-btn-call w-100">
                            <i data-feather="phone" width="16" height="16"></i> {{ trans('update.call_us') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

@endsection

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
@endpush

@push('scripts_bottom')
    <script>
        (function () {
            function applyDeferredSectionBackgrounds() {
                document.querySelectorAll('.js-deferred-section-bg[data-deferred-bg]').forEach(function (el) {
                    var url = el.getAttribute('data-deferred-bg');
                    if (!url) return;
                    el.style.backgroundImage = 'url(' + JSON.stringify(url) + ')';
                    el.removeAttribute('data-deferred-bg');
                });
            }
            var unlocked = false;
            function unlock() {
                if (unlocked) return;
                unlocked = true;
                ['pointerdown', 'touchstart', 'keydown', 'wheel'].forEach(function (ev) {
                    document.removeEventListener(ev, unlock, true);
                });
                applyDeferredSectionBackgrounds();
            }
            ['pointerdown', 'touchstart', 'keydown', 'wheel'].forEach(function (ev) {
                document.addEventListener(ev, unlock, { capture: true, passive: true });
            });
        })();
        (function (reviewToggleLabels) {
            window.toggleText = function (button) {
                var hiddenText = button.previousElementSibling;
                if (hiddenText.classList.contains('d-none')) {
                    hiddenText.classList.remove('d-none');
                    button.textContent = reviewToggleLabels.less;
                } else {
                    hiddenText.classList.add('d-none');
                    button.textContent = reviewToggleLabels.more;
                }
            };
        })(@json(['more' => trans('site.show_more_ellipsis'), 'less' => trans('site.show_less_ellipsis')]));
    </script>
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script src="/assets/default/js/parts/home.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var isRtl = document.documentElement.getAttribute('dir') === 'rtl';
            if (document.querySelector('.dl-certificates-swiper')) {
                new Swiper('.dl-certificates-swiper', {
                    rtl: isRtl,
                    slidesPerView: 1,
                    spaceBetween: 10,
                    pagination: { el: '.dl-gallery .swiper-pagination', clickable: true },
                    breakpoints: {
                        640: { slidesPerView: 1, spaceBetween: 10 },
                        768: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 3, spaceBetween: 30 }
                    }
                });
            }
        });
    </script>
    <script src="/assets/default/js/admin/form_submissions_details.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/js/parts/forms.min.js"></script>
@endpush
