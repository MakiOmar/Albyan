{{-- Google testimonials swiper + rating summary (from homepage) --}}
@if(!empty($testimonials) && $testimonials->isNotEmpty())
    <div class="position-relative home-sections testimonials-container dl-section-alt">
        <section class="dl-container home-sections home-sections-swiper py-4">
            <div>
                <h2 class="section-title">{{ trans('home.testimonials') }}</h2>
            </div>
            <div class="position-relative mt-3">
                <div class="swiper-container testimonials-swiper px-12">
                    <div class="swiper-wrapper">
                        @foreach($testimonials as $testimonial)
                            <div class="swiper-slide">
                                <div class="testimonials-card light-gray-bg position-relative py-15 py-lg-30 px-10 px-lg-20 rounded-sm text-center">
                                    <img class="google-icon" src="/store/1/icons/google.png" width="24" height="24" alt="{{ trans('public.google_icon') }}" loading="lazy">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="testimonials-user-avatar">
                                            <img src="{{ $testimonial->user_avatar }}" alt="{{ $testimonial->user_name }}" class="img-cover rounded-circle" width="80" height="80" loading="lazy">
                                        </div>
                                        <h3 class="font-16 font-weight-bold text-secondary mt-30">{{ $testimonial->user_name }}</h3>
                                        <span class="d-block font-14 text-gray">{{ $testimonial->user_bio }}</span>
                                        @include('web.default.includes.webinar.rate',['rate' => $testimonial->rate, 'dontShowRate' => true])
                                    </div>
                                    @php
                                        $comment = strip_tags($testimonial->comment);
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
                <div class="d-flex justify-content-center mt-3">
                    <div class="swiper-pagination testimonials-swiper-pagination"></div>
                </div>
            </div>
        </section>
    </div>

    @if(!empty($rating_reviews))
        <div class="dl-container position-relative home-sections pb-5">
            <div class="row">
                <div class="col-12">
                    <div class="card text-center p-4 shadow-lg">
                        <h2 class="fw-bold font-20">{{ trans('site.albyan_institute_full_name') }}</h2>
                        <div class="d-flex justify-content-center align-items-center mt-3">
                            <div class="ms-2 d-flex">
                                <img class="google-icon" src="/store/1/icons/google.png" width="24" height="24" alt="{{ trans('public.google_icon') }}">
                                @include('web.default.includes.webinar.rate',['rate' => $rating_reviews['rating'] ?? 0, 'dontShowRate' => false])
                            </div>
                        </div>
                        <p class="text-muted mb-2">{{ trans_choice('site.google_rating_based_on_reviews', $rating_reviews['reviews'] ?? 0, ['count' => $rating_reviews['reviews'] ?? 0]) }}</p>
                        <a href="https://g.page/r/CbrkDak1U-1ZEAE/review" target="_blank" rel="noopener" style="width: 170px;margin: auto;" class="btn btn-primary">
                            <i class="fab fa-google"></i> {{ trans('site.rate_us_on_google') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
