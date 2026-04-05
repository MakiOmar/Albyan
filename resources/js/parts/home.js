(function ($) {
    "use strict";


    const defaultBreakpoints = {
        991: {
            slidesPerView: 3,
        },

        660: {
            slidesPerView: 2,
        },
    }

    const sliders = [
        {
            container: 'features-swiper-container',
            pagination: "features-swiper-pagination",
            breakpoints: false
        },
        {
            container: 'upcoming-courses-swiper',
            pagination: "upcoming-courses-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'latest-webinars-swiper',
            pagination: "latest-webinars-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'latest-bundle-swiper',
            pagination: "bundle-webinars-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'best-sales-webinars-swiper',
            pagination: "best-sales-webinars-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'best-rates-webinars-swiper',
            pagination: "best-rates-webinars-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'has-discount-webinars-swiper',
            pagination: "has-discount-webinars-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'free-webinars-swiper',
            pagination: "free-webinars-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'new-products-swiper',
            pagination: "new-products-swiper-pagination",
            breakpoints: {
                1200: {
                    slidesPerView: 4,
                },

                991: {
                    slidesPerView: 3,
                },

                660: {
                    slidesPerView: 2,
                },
            }
        },
        {
            container: 'testimonials-swiper',
            pagination: "testimonials-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'subscribes-swiper',
            pagination: "subscribes-swiper-pagination",
            breakpoints: defaultBreakpoints
        },
        {
            container: 'organization-swiper-container',
            pagination: "organization-swiper-pagination",
            breakpoints: {
                991: {
                    slidesPerView: 4,
                },

                660: {
                    slidesPerView: 2,
                },
            }
        },
        {
            container: 'trend-categories-swiper',
            pagination: "trend-categories-swiper-pagination",
            breakpoints: {
                1200: {
                    slidesPerView: 6,
                },
                991: {
                    slidesPerView: 4,
                },
                660: {
                    slidesPerView: 2,
                },
            }
        },
    ]

    for (const slider of sliders) {
        const swip = new Swiper('.' + slider.container, {
            slidesPerView: 1,
            spaceBetween: 16,
            loop: false,
            autoplay: {
                delay: 5000,
                disableOnInteraction: true,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: '.' + slider.pagination,
                clickable: true,
            },
            breakpoints: slider.breakpoints
        });

        const $el = $("." + slider.container);

        $el.mouseenter(() => {
            swip.autoplay.stop();
        });

        $el.mouseleave(() => {
            swip.autoplay.start();
        });
    }

    // Category courses sections: dynamic count, one Swiper per section
    document.querySelectorAll('.category-courses-swiper').forEach(function (container) {
        const section = container.closest('section');
        const paginationEl = section ? section.querySelector('.category-courses-swiper-pagination') : null;
        if (!paginationEl) return;
        const swip = new Swiper(container, {
            slidesPerView: 1,
            spaceBetween: 16,
            loop: false,
            autoplay: {
                delay: 5000,
                disableOnInteraction: true,
                pauseOnMouseEnter: true,
            },
            pagination: {
                el: paginationEl,
                clickable: true,
            },
            breakpoints: defaultBreakpoints,
        });
        $(container).mouseenter(() => {
            swip.autoplay.stop();
        });
        $(container).mouseleave(() => {
            swip.autoplay.start();
        });
    });

    var $instructorsOwl = $('.instructors-swiper-container');
    $instructorsOwl.on('initialized.owl.carousel', function () {
        if (window.imageLazyLoader && typeof window.imageLazyLoader.refresh === 'function') {
            window.imageLazyLoader.refresh();
        }
    });
    $instructorsOwl.owlCarousel({
        loop: true,
        center: true,
        items: 3,
        margin: 0,
        autoplay: true,
        dots: true,
        autoplayTimeout: 5000,
        smartSpeed: 450,
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 2
            },
            1170: {
                items: 4
            }
        }
    });

    $(document).ready(function () {
        for (var i = 1; i <= 6; i++) {
            new Parallax(document.getElementById('parallax' + i), {
                relativeInput: true
            });
        }
    });
})(jQuery);
