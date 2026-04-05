(function ($) {
    "use strict";

    function initCategoriesSwiper() {
        new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 16,
            loop: false,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                991: {
                    slidesPerView: 3,
                },

                660: {
                    slidesPerView: 2,
                },
            }
        });
    }

    if (window.lazyCSSLoader && typeof window.lazyCSSLoader.onVendorCssReady === 'function') {
        window.lazyCSSLoader.onVendorCssReady('swiper', initCategoriesSwiper);
    } else {
        initCategoriesSwiper();
    }

    $('body').on('change', '#topFilters input,#topFilters select', function (e) {
        e.preventDefault();
        $('#filtersForm').trigger('submit');
    });
})(jQuery);
