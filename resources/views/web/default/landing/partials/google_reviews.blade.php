{{-- Exact copy of homepage testimonials + Google rating card --}}
@if(!empty($testimonials) && $testimonials->isNotEmpty())
    <div class="position-relative home-sections testimonials-container">

        <div id="parallax1" class="ltr d-none">
            <div data-depth="0.2" class="gradient-box left-gradient-box"></div>
        </div>

        <section class="container home-sections home-sections-swiper">
            <div>
                <h2 class="section-title">{{ trans('home.testimonials') }}</h2>
            </div>

            <div class="position-relative">
                <svg width="60" height="63" style="position: absolute;top: -10px; left: 0" viewBox="0 0 80 83" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M50.3152 3.04623C58.9597 -3.38967 71.3295 1.94584 72.5809 12.6501L78.9162 66.8407C80.1676 77.545 69.362 85.5899 59.4661 81.3215L9.36805 59.7127C-0.527853 55.4443 -2.09209 42.0639 6.5524 35.628L50.3152 3.04623Z" fill="#BFE3C6"/>
                </svg>
                <svg width="62" height="61" style="position: absolute;bottom: -40px;right: -40px;" viewBox="0 0 82 81" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M70.3577 13.3654C80.8282 15.9179 84.6092 28.8479 77.1634 36.6394L39.4691 76.0842C32.0234 83.8758 18.9352 80.6852 15.9104 70.3412L0.597296 17.9746C-2.4275 7.63056 6.87973 -2.10887 17.3503 0.443587L70.3577 13.3654Z" fill="#BFE3C6"/>
                </svg>
                <div class="swiper-container testimonials-swiper px-12">
                    <div class="swiper-wrapper">
                        @foreach($testimonials as $index => $testimonial)
                            <div class="swiper-slide">
                                <div class="testimonials-card light-gray-bg position-relative py-15 py-lg-30 px-10 px-lg-20 rounded-sm text-center">
                                    <img class="google-icon" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="/store/1/icons/google.png" width="24" height="24" alt="{{ trans('public.google_icon') }}" loading="lazy" decoding="async" fetchpriority="low">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="testimonials-user-avatar">
                                            <img
                                                src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                                data-src="{{ $testimonial->user_avatar }}"
                                                alt="{{ $testimonial->user_name }}"
                                                class="img-cover rounded-circle"
                                                width="80"
                                                height="80"
                                                loading="lazy"
                                                decoding="async"
                                                fetchpriority="low"
                                            >
                                        </div>
                                        <h3 class="font-16 font-weight-bold text-secondary mt-30">{{ $testimonial->user_name }}</h3>
                                        <span class="d-block font-14 text-gray">{{ $testimonial->user_bio }}</span>
                                        @include('web.default.includes.webinar.rate',['rate' => $testimonial->rate, 'dontShowRate' => true])
                                    </div>
                                    @php
                                        $comment = $testimonial->comment;
                                        $comment = strip_tags($comment);
                                        $words = explode(' ', $comment);
                                        $maxWords = 25;
                                        $visibleText = implode(' ', array_slice($words, 0, $maxWords));
                                        $hiddenText = implode(' ', array_slice($words, $maxWords));
                                    @endphp
                                    <p class="mt-25 font-14">
                                        {!! e($visibleText) !!}
                                        @if(!empty($hiddenText))
                                            <span class="hidden-text d-none">{!! e($hiddenText) !!}</span>
                                            <button type="button" class="show-more-btn text-blue" onclick="toggleText(this)">{{ trans('site.show_more_ellipsis') }}</button>
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

        <div id="parallax2" class="ltr d-none">
            <div data-depth="0.4" class="gradient-box right-gradient-box"></div>
        </div>

        <div id="parallax3" class="ltr d-none">
            <div data-depth="0.8" class="gradient-box bottom-gradient-box"></div>
        </div>
    </div>
    <div class="container position-relative home-sections">
        <div class="row">
            <div class="col-12">
                <div class="card text-center p-4 shadow-lg">
                    <h1 class="fw-bold">{{ trans('site.albyan_institute_full_name') }}</h1>

                    <div class="d-flex justify-content-center align-items-center">
                        <div class="ms-2 d-flex">
                            <img class="google-icon" src="/store/1/icons/google.png" width="24" height="24" alt="{{ trans('public.google_icon') }}">
                            @include('web.default.includes.webinar.rate',['rate' => $rating_reviews['rating'] ?? 0, 'dontShowRate' => false])
                        </div>
                    </div>

                    <p class="text-muted mb-2">{{ trans_choice('site.google_rating_based_on_reviews', $rating_reviews['reviews'] ?? 0, ['count' => $rating_reviews['reviews'] ?? 0]) }}</p>
                    <a href="https://g.page/r/CbrkDak1U-1ZEAE/review" target="_blank" style="width: 170px;margin: auto;" class="btn btn-primary">
                        <i class="fab fa-google"></i> {{ trans('site.rate_us_on_google') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
