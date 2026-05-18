{{-- Graduation certificates carousel (from about page gallery) --}}
<section class="dl-section" id="dl-certificates">
    <div class="dl-container">
        <strong class="w-100 d-block text-center font-weight-bold" style="font-size: 28px;">{{ trans('site.graduation_celebration_title') }}</strong>
        <div class="albyan-gallery dl-gallery mt-4">
            <div class="swiper dl-certificates-swiper">
                <div class="swiper-wrapper">
                    @for($i = 1; $i <= 24; $i++)
                        <div class="swiper-slide">
                            <a href="/store/1/graduation-party/{{ $i }}.jpg" data-lightbox="dl-gallery">
                                <img src="/store/1/graduation-party/{{ $i }}.jpg" alt="{{ trans('site.gallery_slide_alt', ['num' => $i]) }}" loading="lazy">
                            </a>
                        </div>
                    @endfor
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </div>
</section>
