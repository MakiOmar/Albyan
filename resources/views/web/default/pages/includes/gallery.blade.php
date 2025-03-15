<h1 class="w-100 d-block mt-4" style="text-align:center;font-weight:bold;font-size: 35px;">حفلة تخرج طلاب البيان 2023/2024</h1>
<!-- Swiper Carousel -->
<div class="albyan-gallery">
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <?php for ($i = 1; $i <= 24; $i++) : ?>
                <div class="swiper-slide">
                    <a href="/store/1/graduation-party/<?php echo $i; ?>.jpg" data-lightbox="gallery">
                        <img src="/store/1/graduation-party/<?php echo $i; ?>.jpg" alt="Slide <?php echo $i; ?>">
                    </a>
                </div>
            <?php endfor; ?>
            
        </div>

        <!-- Pagination -->
        <div class="swiper-pagination"></div>
    </div>
</div>