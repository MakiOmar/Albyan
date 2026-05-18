@extends('web.default.layouts.diploma_landing')

@section('content')

    @include('web.default.landing.partials.hero_section')

    @include('web.default.landing.partials.courses_grid')

    @include('web.default.landing.partials.certificates_gallery')

    @include('web.default.landing.partials.google_reviews')

    {{-- Lead generation form --}}
    <section class="dl-form-section" id="dl-register">
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
                        <a href="https://wa.me/{{ $dlWaDigits }}" target="_blank" rel="noopener" class="btn btn-success w-100">
                            <i class="fab fa-whatsapp"></i> {{ trans('update.contact_on_whatsapp') }}
                        </a>
                    @endif
                    @if(!empty($dlCall))
                        <a href="tel:{{ $dlCall }}" class="btn btn-outline-primary w-100">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script>
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
        (function () {
            var carouselUrls = [
                '/assets/default/vendors/swiper/swiper-bundle.min.js',
                '/assets/default/vendors/owl-carousel2/owl.carousel.min.js',
                '/assets/default/vendors/parallax/parallax.min.js',
                '/assets/default/js/parts/home.min.js'
            ];
            var started = false;
            function afterHomeLibs() {
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        document.querySelectorAll('.swiper-slide').forEach(function (slide) {
                            slide.addEventListener('click', function () {
                                var showMoreBtn = this.querySelector('.show-more-btn');
                                if (showMoreBtn) {
                                    showMoreBtn.click();
                                }
                            });
                        });
                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    });
                });
            }
            function loadScriptChain(index) {
                if (index >= carouselUrls.length) {
                    afterHomeLibs();
                    return;
                }
                var sc = document.createElement('script');
                sc.src = carouselUrls[index];
                sc.async = false;
                sc.onload = function () { loadScriptChain(index + 1); };
                sc.onerror = function () { loadScriptChain(index + 1); };
                document.body.appendChild(sc);
            }
            function startCarouselLibs() {
                if (started) return;
                started = true;
                var loader = window.lazyCSSLoader;
                var runChain = function () { loadScriptChain(0); };
                if (loader && typeof loader.loadMultipleCSS === 'function') {
                    loader.loadMultipleCSS(['swiper', 'owl-carousel']).then(runChain).catch(runChain);
                } else {
                    runChain();
                }
            }
            var probe = document.querySelector('.swiper-container, .owl-carousel');
            if (!probe) return;
            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (e) {
                        if (e.isIntersecting) {
                            io.disconnect();
                            startCarouselLibs();
                        }
                    });
                }, { rootMargin: '280px 0px', threshold: 0.01 });
                io.observe(probe);
            } else {
                startCarouselLibs();
            }
            if (window.requestIdleCallback) {
                window.requestIdleCallback(function () { startCarouselLibs(); }, { timeout: 3500 });
            } else {
                window.setTimeout(startCarouselLibs, 3200);
            }
        })();
        document.addEventListener('DOMContentLoaded', function () {
            function initCertificatesGallerySwiper() {
                if (!document.querySelector('.dl-certificates-gallery .mySwiper')) {
                    return;
                }
                new Swiper('.dl-certificates-gallery .mySwiper', {
                    rtl: document.documentElement.getAttribute('dir') === 'rtl',
                    slidesPerView: 1,
                    spaceBetween: 10,
                    pagination: {
                        el: '.dl-certificates-gallery .swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        640: { slidesPerView: 1, spaceBetween: 10 },
                        768: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 3, spaceBetween: 30 }
                    }
                });
            }
            if (window.lazyCSSLoader && typeof window.lazyCSSLoader.onVendorCssReady === 'function') {
                window.lazyCSSLoader.onVendorCssReady('swiper', initCertificatesGallerySwiper);
            } else {
                initCertificatesGallerySwiper();
            }
        });
    </script>
    <script src="/assets/default/js/admin/form_submissions_details.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/default/js/parts/forms.min.js"></script>
@endpush
