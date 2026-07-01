{{-- Top-level category sub-navigation (swiper carousel) below the main navbar --}}
@if(!empty($categories) && count($categories))
    @php
        $activeTopCategorySlug = null;

        if (!empty($category)) {
            $activeTopCategorySlug = !empty($category->parent_id)
                ? (optional($category->category)->slug ?? $category->slug)
                : $category->slug;
        } elseif (!empty($course) && !empty($course->category)) {
            $webinarCategory = $course->category;
            $activeTopCategorySlug = !empty($webinarCategory->parent_id)
                ? (optional($webinarCategory->category)->slug ?? $webinarCategory->slug)
                : $webinarCategory->slug;
        } elseif (request()->is('categories/*')) {
            $activeTopCategorySlug = request()->segment(2);
        }
    @endphp

    <nav id="categorySubNav" class="category-sub-nav" aria-label="{{ trans('categories.categories') }}">
        <div class="{{ (!empty($isPanel) and $isPanel) ? 'container-fluid' : 'container' }}">
            <div class="category-sub-nav-inner position-relative">
                <button type="button" class="category-sub-nav-arrow category-sub-nav-prev" aria-label="{{ trans('webinars.previous') }}">
                    <i data-feather="chevron-left" width="18" height="18"></i>
                </button>

                <div class="swiper-container category-sub-nav-swiper">
                    <div class="swiper-wrapper">
                        @foreach($categories as $topCategory)
                            <div class="swiper-slide">
                                <a href="{{ $topCategory->getUrl() }}"
                                   class="category-sub-nav-link {{ $activeTopCategorySlug === $topCategory->slug ? 'active' : '' }}">
                                    @if(!empty($topCategory->icon))
                                        <img src="{{ $topCategory->icon }}" class="category-sub-nav-icon" alt="" width="20" height="20">
                                    @endif
                                    <span>{{ $topCategory->title }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="button" class="category-sub-nav-arrow category-sub-nav-next" aria-label="{{ trans('webinars.next') }}">
                    <i data-feather="chevron-right" width="18" height="18"></i>
                </button>
            </div>
        </div>
    </nav>

    @push('scripts_bottom')
        <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
        <script>
            (function ($) {
                "use strict";

                function pageDirIsRtl() {
                    return document.documentElement.getAttribute('dir') === 'rtl';
                }

                function initCategorySubNavSwiper() {
                    var el = document.querySelector('.category-sub-nav-swiper');
                    if (!el || !el.querySelector('.swiper-slide') || typeof Swiper === 'undefined') {
                        return;
                    }

                    if (el.swiper) {
                        return;
                    }

                    var swiper = new Swiper(el, {
                        rtl: pageDirIsRtl(),
                        slidesPerView: 2,
                        spaceBetween: 8,
                        freeMode: true,
                        watchOverflow: true,
                        navigation: {
                            nextEl: '.category-sub-nav-next',
                            prevEl: '.category-sub-nav-prev',
                        },
                        breakpoints: {
                            768: {
                                slidesPerView: 4,
                                spaceBetween: 10,
                            },
                            992: {
                                slidesPerView: 6,
                                spaceBetween: 12,
                            },
                        },
                    });

                    requestAnimationFrame(function () {
                        try {
                            swiper.update();
                        } catch (e) { /* ignore */ }
                    });
                }

                if (window.lazyCSSLoader && typeof window.lazyCSSLoader.onVendorCssReady === 'function') {
                    window.lazyCSSLoader.onVendorCssReady('swiper', initCategorySubNavSwiper);
                } else {
                    $(initCategorySubNavSwiper);
                }
            })(jQuery);
        </script>
    @endpush
@endif
