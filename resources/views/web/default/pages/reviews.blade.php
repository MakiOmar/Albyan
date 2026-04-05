@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
<style>
    /* Styling for the Albyan Gallery */
    .albyan-gallery {
        max-width: 1200px;
        margin: auto;
        padding: 20px;
        overflow: hidden;
        position: relative;
    }

    .albyan-gallery .swiper-slide img {
        width: 100%;
        height: auto;
        border-radius: 10px;
    }
    .albyan-gallery .swiper-slide img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            cursor: pointer; /* Makes images clickable */
            transition: transform 0.2s ease-in-out;
        }

        .albyan-gallery .swiper-slide img:hover {
            transform: scale(1.05);
        }
</style>
</head>
<!-- Lightbox2 CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">

@endpush


@section('content')

@include('web.default.pages.includes.gallery')

@if(!empty($testimonials) and !$testimonials->isEmpty())
            <div class="position-relative mt-4 testimonials-container">

                <div id="parallax1" class="ltr">
                    <div data-depth="0.2" class="gradient-box left-gradient-box"></div>
                </div>

                <section class="container home-sections-swiper">
                    <div class="text-center">
                        <h2 class="section-title">{{ trans('home.testimonials') }}</h2>
                        <p class="section-hint">{{ trans('home.testimonials_hint') }}</p>
                    </div>

                    <div class="position-relative">
                        <div class="swiper-container testimonials-swiper px-12">
                            <div class="swiper-wrapper">

                                @foreach($testimonials as $testimonial)
                                    <div class="swiper-slide">
                                        <div class="testimonials-card position-relative py-15 py-lg-30 px-10 px-lg-20 rounded-sm shadow bg-white text-center">
                                            <img class="google-icon" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="/store/1/icons/google.png" alt="Google" width="24" height="24">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="testimonials-user-avatar">
                                                    <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="{{ $testimonial->user_avatar }}" alt="{{ $testimonial->user_name }}" class="img-cover rounded-circle" width="50" height="50">
                                                </div>
                                                <h4 class="font-16 font-weight-bold text-secondary mt-30">{{ $testimonial->user_name }}</h4>
                                                <span class="d-block font-14">{{ $testimonial->user_bio }}</span>
                                                @include('web.default.includes.webinar.rate',['rate' => $testimonial->rate, 'dontShowRate' => true])
                                            </div>
                                            @php
                                                $comment = $testimonial->comment;
                                                $words = explode(' ', $comment);
                                                $maxWords = 25; // Set max words to display initially
                                                $visibleText = implode(' ', array_slice($words, 0, $maxWords));
                                                $hiddenText = implode(' ', array_slice($words, $maxWords));
                                            @endphp
                                            <p class="mt-25 font-14">
                                                {!! nl2br(e($visibleText)) !!}
                                                @if(!empty($hiddenText))
                                                    <span class="hidden-text d-none">{!! nl2br(e($hiddenText)) !!}</span>
                                                    <button class="show-more-btn text-blue" onclick="toggleText(this)">... عرض المزيد</button>
                                                @endif
                                            </p>

                                            <div class="bottom-gradient"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>

                        <div class="d-flex justify-content-center">
                            <div class="swiper-pagination testimonials-swiper-pagination"></div>
                        </div>
                    </div>
                </section>

                <div id="parallax2" class="ltr">
                    <div data-depth="0.4" class="gradient-box right-gradient-box"></div>
                </div>

                <div id="parallax3" class="ltr">
                    <div data-depth="0.8" class="gradient-box bottom-gradient-box"></div>
                </div>
            </div>
            <div class="container position-relative home-sections">
                <div class="row">
                    <div class="col-12">
                        <div class="card text-center p-4 shadow-lg">
                            <h1 class="fw-bold">معهد البيان للخدمات التعليمية</h1>
                        
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="ms-2 d-flex">
                                    <img class="google-icon" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="/store/1/icons/google.png" alt="Google" width="24" height="24">
                                    @include('web.default.includes.webinar.rate',['rate' => $rating_reviews['rating'], 'dontShowRate' => false])
                                </div>
                            </div>
                        
                            <p class="text-muted mb-2">بناءً على {{ $rating_reviews['reviews'] }} مراجعة</p>
                            @php
                            $plac_id = env('GOOGLE_PLACE_ID');
                            @endphp
                            <a href="https://g.page/r/CbrkDak1U-1ZEAE/review" target="_blank" style="width: 170px;margin: auto;" class="btn btn-primary">
                                <i class="fab fa-google"></i> قيمنا على جوجل
                            </a>
                        </div>
                        
                    </div>
                </div>
            </div>
        @endif

@endsection

@push('scripts_bottom')
    <!-- Lightbox2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

    <script>
        var leafletApiPath = '{{ getLeafletApiPath() }}';
    </script>
    <script src="/assets/default/js/parts/contact.min.js"></script>
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            function initReviewsGallerySwiper() {
                new Swiper(".albyan-gallery .mySwiper", {
                    slidesPerView: 1,
                    spaceBetween: 10,
                    pagination: {
                        el: ".albyan-gallery .swiper-pagination",
                        clickable: true,
                    },
                    navigation: {
                        nextEl: ".albyan-gallery .swiper-button-next",
                        prevEl: ".albyan-gallery .swiper-button-prev",
                    },
                    breakpoints: {
                        640: { slidesPerView: 1, spaceBetween: 10 },
                        768: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 4, spaceBetween: 30 }
                    }
                });
            }
            function initReviewsTestimonialsSwiper() {
                new Swiper(".testimonials-swiper", {
                    slidesPerView: 1,
                    spaceBetween: 10,
                    pagination: {
                        el: ".testimonials-swiper-pagination",
                        clickable: true,
                    },
                    navigation: {
                        nextEl: ".testimonials-swiper .swiper-button-next",
                        prevEl: ".testimonials-swiper .swiper-button-prev",
                    },
                    breakpoints: {
                        640: { slidesPerView: 1, spaceBetween: 10 },
                        768: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 3, spaceBetween: 30 }
                    }
                });
            }
            if (window.lazyCSSLoader && typeof window.lazyCSSLoader.onVendorCssReady === 'function') {
                window.lazyCSSLoader.onVendorCssReady('swiper', initReviewsGallerySwiper);
                window.lazyCSSLoader.onVendorCssReady('swiper', initReviewsTestimonialsSwiper);
            } else {
                initReviewsGallerySwiper();
                initReviewsTestimonialsSwiper();
            }
        });
    </script>
@endpush
