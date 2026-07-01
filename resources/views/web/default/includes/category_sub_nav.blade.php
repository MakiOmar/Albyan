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

    @push('styles_top')
        <style>
            /* Critical layout: arrows + carousel row (avoids flash before app.css) */
            #categorySubNav .category-sub-nav-shell {
                display: grid;
                grid-template-columns: 40px minmax(0, 1fr) 40px;
                align-items: center;
                column-gap: 10px;
                direction: ltr;
            }
            #categorySubNav .category-sub-nav-arrow {
                appearance: none;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                padding: 0;
                border: none;
                border-radius: 50%;
                background: #f0f5fa;
                color: #01477d;
            }
            #categorySubNav .category-sub-nav-link,
            #categorySubNav .category-sub-nav-link span {
                white-space: nowrap;
            }
        </style>
    @endpush

    <nav id="categorySubNav" class="category-sub-nav" aria-label="{{ trans('categories.categories') }}">
        <div class="{{ (!empty($isPanel) and $isPanel) ? 'container-fluid' : 'container' }}">
            <div class="category-sub-nav-shell">
                <button type="button" class="category-sub-nav-arrow category-sub-nav-prev is-disabled" aria-label="{{ trans('webinars.previous') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>

                <div class="category-sub-nav-viewport">
                    <div class="category-sub-nav-fade category-sub-nav-fade--start"></div>
                    <div class="swiper-container category-sub-nav-swiper">
                        <div class="swiper-wrapper">
                            @foreach($categories as $topCategory)
                                <div class="swiper-slide">
                                    <a href="{{ $topCategory->getUrl() }}"
                                       class="category-sub-nav-link {{ $activeTopCategorySlug === $topCategory->slug ? 'active' : '' }}"
                                       title="{{ $topCategory->title }}">
                                        @if(!empty($topCategory->icon))
                                            <img src="{{ $topCategory->icon }}" class="category-sub-nav-icon" alt="" width="20" height="20">
                                        @endif
                                        <span>{{ $topCategory->title }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="category-sub-nav-fade category-sub-nav-fade--end"></div>
                </div>

                <button type="button" class="category-sub-nav-arrow category-sub-nav-next is-disabled" aria-label="{{ trans('webinars.next') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
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
                    if (!root) {
                        return;
                    }

                    var el = root.querySelector('.category-sub-nav-swiper');
                    var prevBtn = root.querySelector('.category-sub-nav-prev');
                    var nextBtn = root.querySelector('.category-sub-nav-next');
                    var fadeStart = root.querySelector('.category-sub-nav-fade--start');
                    var fadeEnd = root.querySelector('.category-sub-nav-fade--end');

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
                        spaceBetween: 6,
                        watchOverflow: true,
                        breakpoints: {
                            992: {
                                slidesPerView: 'auto',
                                slidesPerGroup: 1,
                                spaceBetween: 4,
                            },
                        },
                    });

                    function updateControls() {
                        var noOverflow = swiper.isBeginning && swiper.isEnd;

                        if (prevBtn) {
                            prevBtn.classList.toggle('is-disabled', swiper.isBeginning || noOverflow);
                        }

                        if (nextBtn) {
                            nextBtn.classList.toggle('is-disabled', swiper.isEnd || noOverflow);
                        }

                        if (fadeStart) {
                            fadeStart.classList.toggle('is-visible', !swiper.isBeginning && !noOverflow);
                        }

                        if (fadeEnd) {
                            fadeEnd.classList.toggle('is-visible', !swiper.isEnd && !noOverflow);
                        }
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

                    var activeSlide = root.querySelector('.category-sub-nav-link.active');
                    if (activeSlide) {
                        var activeEl = activeSlide.closest('.swiper-slide');
                        var activeIdx = activeEl ? Array.prototype.indexOf.call(swiper.slides, activeEl) : -1;
                        if (activeIdx > 0) {
                            swiper.slideTo(activeIdx, 0);
                        }
                    }

                    swiper.on('slideChange', updateControls);
                    swiper.on('resize', updateControls);
                    swiper.on('reachBeginning', updateControls);
                    swiper.on('reachEnd', updateControls);
                    swiper.on('fromEdge', updateControls);
                    updateControls();

                    requestAnimationFrame(function () {
                        try {
                            swiper.update();
                            updateControls();
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
