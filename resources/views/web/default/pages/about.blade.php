@extends(getTemplate().'.layouts.app')

@push('styles_top')
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
<style>
    .about-page-content .about-page-list,
    .about-page-content .about-page-features,
    .about-page-content .about-page-values {
        padding-right: 1.25rem;
        line-height: 1.8;
    }
    .about-page-content .about-page-list li,
    .about-page-content .about-page-features li,
    .about-page-content .about-page-values li {
        margin-bottom: 0.75rem;
    }
    .about-page-contact-links a {
        font-weight: 500;
    }
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
        cursor: pointer;
        transition: transform 0.2s ease-in-out;
    }

    .albyan-gallery .swiper-slide img:hover {
        transform: scale(1.05);
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
@endpush

@push('scripts_top')
    @include('web.default.pages.includes.about_page_schema')
@endpush

@section('content')
<div class="container">
    <nav class="mt-3 mb-2" aria-label="breadcrumb">
        <ol class="breadcrumb p-0 m-0 bg-transparent">
            <li class="breadcrumb-item font-12"><a href="{{ url('/') }}">{{ !empty($generalSettings['site_name']) ? $generalSettings['site_name'] : trans('navbar.home') }}</a></li>
            <li class="breadcrumb-item font-12 active" aria-current="page">{{ trans('site.about_breadcrumb_title') }}</li>
        </ol>
    </nav>

    <main id="about-page" class="row">
        @include('web.default.pages.includes.about_page_content')

        @include('web.default.pages.includes.gallery', ['galleryHeadingId' => 'about-graduation-gallery-heading'])

        @include('web.default.pages.includes.about_certificates_gallery')
    </main>
</div>
@endsection

@push('scripts_bottom')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
<script>
    var leafletApiPath = '{{ getLeafletApiPath() }}';
</script>
<script src="/assets/default/js/parts/contact.min.js"></script>
<script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        function initAboutGallerySwiper() {
            document.querySelectorAll(".albyan-gallery .mySwiper").forEach(function (el) {
                var root = el.closest(".albyan-gallery");
                new Swiper(el, {
                    rtl: document.documentElement.getAttribute("dir") === "rtl",
                    slidesPerView: 1,
                    spaceBetween: 10,
                    pagination: {
                        el: root ? root.querySelector(".swiper-pagination") : ".swiper-pagination",
                        clickable: true,
                    },
                    breakpoints: {
                        640: { slidesPerView: 1, spaceBetween: 10 },
                        768: { slidesPerView: 2, spaceBetween: 20 },
                        1024: { slidesPerView: 3, spaceBetween: 30 }
                    }
                });
            });
        }
        if (window.lazyCSSLoader && typeof window.lazyCSSLoader.onVendorCssReady === 'function') {
            window.lazyCSSLoader.onVendorCssReady('swiper', initAboutGallerySwiper);
        } else {
            initAboutGallerySwiper();
        }
    });
</script>
@endpush
