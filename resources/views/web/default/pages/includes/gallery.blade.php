{{-- Gallery heading: locale from session / language switcher --}}
<strong class="w-100 d-block mt-4" style="text-align:center;font-weight:bold;font-size: 35px;">{{ trans('site.graduation_celebration_title') }}</strong>
<!-- Swiper Carousel -->
<div class="albyan-gallery">
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <?php for ($i = 1; $i <= 24; $i++) : ?>
                <div class="swiper-slide">
                    <a href="/store/1/graduation-party/<?php echo $i; ?>.jpg" data-lightbox="gallery">
                        <img src="/store/1/graduation-party/<?php echo $i; ?>.jpg" alt="{{ trans('site.gallery_slide_alt', ['num' => $i]) }}">
                    </a>
                </div>
            <?php endfor; ?>
            
        </div>

        <!-- Pagination -->
        <div class="swiper-pagination"></div>
    </div>
</div>