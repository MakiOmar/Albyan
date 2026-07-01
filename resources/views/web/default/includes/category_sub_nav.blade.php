{{-- Top-level category sub-navigation (swiper carousel) below the main navbar --}}
@if(!empty($categories) && count($categories))
    @php
        $categorySubNavRtl = web_layout_is_rtl($generalSettings ?? null);
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
            <div class="category-sub-nav-inner">
                <button type="button" class="category-sub-nav-arrow category-sub-nav-prev is-disabled" aria-label="{{ trans('webinars.previous') }}">
                    <i data-feather="{{ $categorySubNavRtl ? 'chevron-right' : 'chevron-left' }}" width="18" height="18"></i>
                </button>

                <div class="swiper-container category-sub-nav-swiper">
                    <div class="swiper-wrapper">
                        @foreach($categories as $topCategory)
                            <div class="swiper-slide">
                                <a href="{{ $topCategory->getUrl() }}"
                                   class="category-sub-nav-link {{ $activeTopCategorySlug === $topCategory->slug ? 'active' : '' }}">
                                    @if(!empty($topCategory->icon))
                                        <img src="{{ $topCategory->icon }}" class="category-sub-nav-icon" alt="" width="22" height="22">
                                    @endif
                                    <span>{{ $topCategory->title }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="button" class="category-sub-nav-arrow category-sub-nav-next" aria-label="{{ trans('webinars.next') }}">
                    <i data-feather="{{ $categorySubNavRtl ? 'chevron-left' : 'chevron-right' }}" width="18" height="18"></i>
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
                    var root = document.getElementById('categorySubNav');
                    var el = root ? root.querySelector('.category-sub-nav-swiper') : null;
                    var prevBtn = root ? root.querySelector('.category-sub-nav-prev') : null;
                    var nextBtn = root ? root.querySelector('.category-sub-nav-next') : null;

                    if (!el || !el.querySelector('.swiper-slide') || typeof Swiper === 'undefined') {
                        return;
                    }

                    if (el.swiper) {
                        return;
                    }

                    var swiper = new Swiper(el, {
                        rtl: pageDirIsRtl(),
                        slidesPerView: 2,
                        slidesPerGroup: 2,
                        spaceBetween: 8,
                        watchOverflow: true,
                        breakpoints: {
                            768: {
                                slidesPerView: 4,
                                slidesPerGroup: 1,
                                spaceBetween: 10,
                            },
                            992: {
                                slidesPerView: 6,
                                slidesPerGroup: 1,
                                spaceBetween: 12,
                            },
                        },
                    });

                    function updateArrows() {
                        if (!prevBtn || !nextBtn) {
                            return;
                        }

                        var noOverflow = swiper.isBeginning && swiper.isEnd;
                        prevBtn.classList.toggle('is-disabled', swiper.isBeginning || noOverflow);
                        nextBtn.classList.toggle('is-disabled', swiper.isEnd || noOverflow);
                    }

                    if (prevBtn) {
                        prevBtn.addEventListener('click', function () {
                            swiper.slidePrev();
                        });
                    }

                    if (nextBtn) {
                        nextBtn.addEventListener('click', function () {
                            swiper.slideNext();
                        });
                    }

                    swiper.on('slideChange', updateArrows);
                    swiper.on('resize', updateArrows);
                    updateArrows();

                    requestAnimationFrame(function () {
                        try {
                            swiper.update();
                            updateArrows();
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
