{{-- Graduation gallery: semantic h2 + descriptive alt text --}}
<section class="col-12 about-graduation-gallery-section" aria-labelledby="{{ $galleryHeadingId ?? 'graduation-gallery-heading' }}">
<h2 id="{{ $galleryHeadingId ?? 'graduation-gallery-heading' }}" class="w-100 d-block mt-4 text-center section-title-bg p-2">{{ trans('site.graduation_celebration_title') }}</h2>
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
</section>