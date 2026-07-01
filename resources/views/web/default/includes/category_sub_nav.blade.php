{{-- Top-level category sub-navigation below the main navbar --}}
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

        $categorySubNavRtl = web_layout_is_rtl($generalSettings ?? null);
    @endphp

    {{-- Inline styles: include runs in <body> after <head>, so @push('styles_top') never applies --}}
    <style>
        #categorySubNav.category-sub-nav {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            z-index: 490;
        }
        #categorySubNav .category-sub-nav-bar {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 8px;
            padding: 10px 0;
        }
        #categorySubNav .category-sub-nav-scroll {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            flex-wrap: nowrap;
            align-items: stretch;
            gap: 6px;
            overflow-x: auto;
            overflow-y: visible;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 2px 0;
        }
        #categorySubNav .category-sub-nav-scroll::-webkit-scrollbar {
            display: none;
        }
        #categorySubNav .category-sub-nav-item {
            flex: 0 0 auto;
            min-width: 0;
        }
        #categorySubNav .category-sub-nav-link {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 6px;
            height: 100%;
            min-height: 44px;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #1e3a5f;
            background: transparent;
            white-space: nowrap !important;
            line-height: 1.4;
            text-decoration: none;
            box-sizing: border-box;
        }
        #categorySubNav .category-sub-nav-link:hover {
            color: #01477d;
            background: rgba(1, 71, 125, 0.07);
            text-decoration: none;
        }
        #categorySubNav .category-sub-nav-link.active {
            color: #01477d;
            background: #ffffff;
            box-shadow: 0 1px 4px rgba(1, 71, 125, 0.12);
        }
        #categorySubNav .category-sub-nav-link span {
            white-space: nowrap !important;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.4;
        }
        #categorySubNav .category-sub-nav-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            flex-shrink: 0;
        }
        #categorySubNav .category-sub-nav-btn {
            flex: 0 0 36px;
            width: 36px;
            height: 36px;
            padding: 0;
            border: 1px solid #d8e0ea;
            border-radius: 50%;
            background: #fff;
            color: #01477d;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            -webkit-appearance: none;
            appearance: none;
            flex-shrink: 0;
        }
        #categorySubNav .category-sub-nav-btn svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2.5;
            pointer-events: none;
        }
        #categorySubNav .category-sub-nav-btn:hover:not(:disabled) {
            background: #01477d;
            border-color: #01477d;
            color: #fff;
        }
        #categorySubNav .category-sub-nav-btn:disabled {
            opacity: 0.35;
            cursor: default;
        }
        @media (max-width: 991px) {
            #categorySubNav .category-sub-nav-bar {
                gap: 6px;
                padding: 8px 0;
            }
            #categorySubNav .category-sub-nav-scroll {
                scroll-snap-type: x mandatory;
                gap: 8px;
            }
            #categorySubNav .category-sub-nav-item {
                flex: 0 0 calc((100% - 8px) / 2);
                width: calc((100% - 8px) / 2);
                scroll-snap-align: start;
                scroll-snap-stop: always;
            }
            #categorySubNav .category-sub-nav-link {
                width: 100%;
                min-height: 48px;
                padding: 10px 6px;
                font-size: 11px;
            }
            #categorySubNav .category-sub-nav-icon {
                width: 18px;
                height: 18px;
            }
            #categorySubNav .category-sub-nav-btn {
                flex: 0 0 32px;
                width: 32px;
                height: 32px;
            }
        }
    </style>

    <nav id="categorySubNav" class="category-sub-nav" aria-label="{{ trans('categories.categories') }}">
        <div class="{{ (!empty($isPanel) and $isPanel) ? 'container-fluid' : 'container' }}">
            <div class="category-sub-nav-bar">
                <button type="button" class="category-sub-nav-btn category-sub-nav-btn--prev" disabled aria-label="{{ trans('webinars.previous') }}">
                    @if($categorySubNavRtl)
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    @else
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    @endif
                </button>

                <div class="category-sub-nav-scroll" tabindex="0" @if($categorySubNavRtl) dir="rtl" @endif>
                    @foreach($categories as $topCategory)
                        <div class="category-sub-nav-item">
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

                <button type="button" class="category-sub-nav-btn category-sub-nav-btn--next" aria-label="{{ trans('webinars.next') }}">
                    @if($categorySubNavRtl)
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    @else
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    @endif
                </button>
            </div>
        </div>
    </nav>

    @push('scripts_bottom')
        <script>
            (function () {
                "use strict";

                function initCategorySubNav() {
                    var root = document.getElementById('categorySubNav');
                    if (!root) {
                        return;
                    }

                    var scrollEl = root.querySelector('.category-sub-nav-scroll');
                    var prevBtn = root.querySelector('.category-sub-nav-btn--prev');
                    var nextBtn = root.querySelector('.category-sub-nav-btn--next');

                    if (!scrollEl || !prevBtn || !nextBtn) {
                        return;
                    }

                    var isRtl = scrollEl.getAttribute('dir') === 'rtl'
                        || document.documentElement.getAttribute('dir') === 'rtl';

                    function isMobile() {
                        return window.matchMedia('(max-width: 991px)').matches;
                    }

                    function maxScroll() {
                        return Math.max(0, scrollEl.scrollWidth - scrollEl.clientWidth);
                    }

                    function normalizedScrollPos() {
                        var max = maxScroll();
                        if (max <= 1) {
                            return 0;
                        }

                        var sl = scrollEl.scrollLeft;

                        if (isRtl) {
                            if (sl < 0) {
                                return Math.min(max, Math.abs(sl));
                            }
                            return Math.min(max, Math.max(0, max - sl));
                        }

                        return Math.min(max, Math.max(0, sl));
                    }

                    function scrollState() {
                        var max = maxScroll();
                        if (max <= 1) {
                            return { atStart: true, atEnd: true };
                        }

                        var pos = normalizedScrollPos();
                        return {
                            atStart: pos <= 2,
                            atEnd: pos >= max - 2
                        };
                    }

                    function updateButtons() {
                        var state = scrollState();
                        prevBtn.disabled = state.atStart;
                        nextBtn.disabled = state.atEnd;
                    }

                    function scrollByPage(direction) {
                        var max = maxScroll();
                        if (max <= 1) {
                            return;
                        }

                        var step = isMobile() ? scrollEl.clientWidth : Math.round(scrollEl.clientWidth * 0.85);
                        var pos = normalizedScrollPos();
                        var target;

                        if (direction === 'next') {
                            target = Math.min(max, pos + step);
                        } else {
                            target = Math.max(0, pos - step);
                        }

                        if (isRtl) {
                            if (scrollEl.scrollLeft < 0) {
                                scrollEl.scrollTo({ left: -target, behavior: 'smooth' });
                            } else {
                                scrollEl.scrollTo({ left: max - target, behavior: 'smooth' });
                            }
                        } else {
                            scrollEl.scrollTo({ left: target, behavior: 'smooth' });
                        }
                    }

                    prevBtn.addEventListener('click', function () {
                        scrollByPage('prev');
                    });

                    nextBtn.addEventListener('click', function () {
                        scrollByPage('next');
                    });

                    scrollEl.addEventListener('scroll', updateButtons, { passive: true });
                    window.addEventListener('resize', updateButtons);

                    var activeLink = root.querySelector('.category-sub-nav-link.active');
                    if (activeLink) {
                        var activeItem = activeLink.closest('.category-sub-nav-item');
                        if (activeItem && isMobile()) {
                            activeItem.scrollIntoView({ behavior: 'auto', inline: 'nearest', block: 'nearest' });
                        }
                    }

                    updateButtons();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initCategorySubNav);
                } else {
                    initCategorySubNav();
                }
            })();
        </script>
    @endpush
@endif
