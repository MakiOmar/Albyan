@extends(getTemplate().'.layouts.app')

@push('styles_top')
<style>
    /* Styling for the Albyan Gallery */
    .albyan-gallery {
        max-width: 1200px;
        height: 440px;
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
<link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
<!-- Lightbox2 CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">

@endpush


@section('content')
<div class="container">

<div class="row">
    @include('web.default.pages.includes.gallery')
    @include('web.default.pages.includes.about_text')
    <h1 class="text-center w-100 mt-2 mb-2">تحصل على أربع شهادات من معهد البيان وبطاقة عضوية</h1>
    <!-- Swiper Carousel -->
    <div class="albyan-gallery" style="height: 320px">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <a href="/store/1/certs/333.jpg" data-lightbox="gallery">
                        <img src="/store/1/certs/333.jpg" alt="Slide 1">
                    </a>
                </div>
                <div class="swiper-slide">
                    <a href="/store/1/certs/dsgdsfd898.jpg" data-lightbox="gallery">
                        <img src="/store/1/certs/dsgdsfd898.jpg" alt="Slide 2">
                    </a>
                </div>
                <div class="swiper-slide">
                    <a href="/store/1/certs/1copy.jpg" data-lightbox="gallery">
                        <img src="/store/1/certs/1copy.jpg" alt="Slide 2">
                    </a>
                </div>
                <div class="swiper-slide">
                    <a href="/store/1/certs/fsfsdflkl.jpg" data-lightbox="gallery">
                        <img src="/store/1/certs/fsfsdflkl.jpg" alt="Slide 4">
                    </a>
                </div>
                <div class="swiper-slide">
                    <a href="store/1/certs/foi87.png" data-lightbox="gallery">
                        <img src="store/1/certs/foi87.png" alt="Slide 4">
                    </a>
                </div>
            </div>

            <!-- Pagination -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
</div>

</div>

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
            var swiper = new Swiper(".albyan-gallery .mySwiper", {
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
                    640: { slidesPerView: 1, spaceBetween: 10 },  // Mobile: 1 slide
                    768: { slidesPerView: 2, spaceBetween: 20 },  // Tablet: 2 slides
                    1024: { slidesPerView: 3, spaceBetween: 30 }  // Desktop: 4 slides
                }
            });
        });
    </script>
@endpush
