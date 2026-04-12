(function ($) {
    "use strict";

    /** Match `<html dir="ltr|rtl">` so carousels track locale, not cached RTL-only defaults */
    function pageDirIsRtl() {
        return typeof document !== "undefined" && document.documentElement.getAttribute("dir") === "rtl";
    }

    const defaultBreakpoints = {
        991: {
            slidesPerView: 3,
        },

        660: {
            slidesPerView: 2,
        },
    };

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
    ];

    function initSwipersAndOwl() {
        for (const slider of sliders) {
            const root = document.querySelector('.' + slider.container);
            if (!root || !root.querySelector('.swiper-slide')) {
                continue;
            }

            const swip = new Swiper('.' + slider.container, {
                rtl: pageDirIsRtl(),
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

        document.querySelectorAll('.category-courses-swiper').forEach(function (container) {
            if (!container.querySelector('.swiper-slide')) {
                return;
            }
            const section = container.closest('section');
            const paginationEl = section ? section.querySelector('.category-courses-swiper-pagination') : null;
            if (!paginationEl) return;
            const swip = new Swiper(container, {
                rtl: pageDirIsRtl(),
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
        if (!$instructorsOwl.length) {
            return;
        }
        $instructorsOwl.on('initialized.owl.carousel', function () {
            if (window.imageLazyLoader && typeof window.imageLazyLoader.refresh === 'function') {
                window.imageLazyLoader.refresh();
            }
        });
        $instructorsOwl.owlCarousel({
            rtl: pageDirIsRtl(),
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
    }

    function initParallaxDeferred() {
        function run() {
            if (typeof Parallax === 'undefined') {
                return;
            }
            for (var i = 1; i <= 6; i++) {
                var el = document.getElementById('parallax' + i);
                if (!el) {
                    continue;
                }
                try {
                    new Parallax(el, {
                        relativeInput: true
                    });
                } catch (e) {
                    /* ignore */
                }
            }
        }
        if (window.requestIdleCallback) {
            window.requestIdleCallback(run, { timeout: 4500 });
        } else {
            window.setTimeout(run, 200);
        }
    }

    /**
     * Double rAF: run after the next paint so Swiper/Owl measure after layout settles
     * (reduces forced reflow / layout thrashing vs sync init right after script parse).
     * @see https://developer.chrome.com/docs/performance/insights/forced-reflow
     */
    function scheduleHomeCarousels() {
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                initSwipersAndOwl();
                initParallaxDeferred();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scheduleHomeCarousels);
    } else {
        scheduleHomeCarousels();
    }
})(jQuery);
