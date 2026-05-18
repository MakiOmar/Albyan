{{-- Exact copy of homepage category courses swiper (+ contact buttons on cards) --}}
@if(!empty($webinars) && $webinars->isNotEmpty())
    @php
        $catCategory = $category;
        $catWebinars = $webinars;
    @endphp
    <section class="home-sections home-sections-swiper container category-courses-home-section" id="dl-courses">
        <div class="d-flex justify-content-between ">
            <div>
                <h2 class="section-title">{{ !empty($catCategory) ? $catCategory->title : trans('home.latest_classes') }}</h2>
            </div>
            @if(!empty($catCategory))
                <a href="{{ $catCategory->getUrl() }}" class="btn btn-border-white">{{ trans('home.view_all') }}</a>
            @endif
        </div>
        <div class="mt-10 position-relative">
            <div class="swiper-container category-courses-swiper px-12">
                <div class="swiper-wrapper py-20">
                    @foreach($catWebinars as $catWebinar)
                        <div class="swiper-slide">
                            @include('web.default.includes.webinar.grid-card-landing', [
                                'webinar' => $catWebinar,
                                'diplomaLandingWhatsapp' => $diplomaLandingWhatsapp ?? null,
                                'diplomaLandingCall' => $diplomaLandingCall ?? null,
                            ])
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <div class="swiper-pagination category-courses-swiper-pagination"></div>
            </div>
        </div>
    </section>
@endif
