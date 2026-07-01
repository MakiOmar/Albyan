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

    @push('styles_top')
        <style>
            #categorySubNav.category-sub-nav {
                background: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
            }
            #categorySubNav .category-sub-nav-bar {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                gap: 8px;
                padding: 10px 0;
                min-height: 52px;
            }
            #categorySubNav .category-sub-nav-scroll {
                flex: 1 1 auto;
                min-width: 0;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }
            #categorySubNav .category-sub-nav-scroll::-webkit-scrollbar {
                display: none;
            }
            #categorySubNav .category-sub-nav-track {
                display: inline-flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center;
                gap: 6px;
            }
            #categorySubNav .category-sub-nav-item {
                flex: 0 0 auto;
            }
            #categorySubNav .category-sub-nav-link,
            #categorySubNav .category-sub-nav-link span {
                display: inline-flex;
                align-items: center;
                white-space: nowrap !important;
                text-decoration: none;
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
            }
            @media (max-width: 991px) {
                #categorySubNav .category-sub-nav-item {
                    flex: 0 0 calc(50% - 3px);
                    max-width: calc(50% - 3px);
                }
                #categorySubNav .category-sub-nav-link {
                    width: 100%;
                    justify-content: center;
                    font-size: 11px;
                }
                #categorySubNav .category-sub-nav-btn {
                    flex: 0 0 32px;
                    width: 32px;
                    height: 32px;
                }
            }
        </style>
    @endpush

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

                <div class="category-sub-nav-scroll" tabindex="0">
                    <div class="category-sub-nav-track">
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
                    var trackEl = root.querySelector('.category-sub-nav-track');
                    var prevBtn = root.querySelector('.category-sub-nav-btn--prev');
                    var nextBtn = root.querySelector('.category-sub-nav-btn--next');

                    if (!scrollEl || !trackEl || !prevBtn || !nextBtn) {
                        return;
                    }

                    var items = function () {
                        return Array.prototype.slice.call(trackEl.querySelectorAll('.category-sub-nav-item'));
                    };

                    function scrollState() {
                        var max = scrollEl.scrollWidth - scrollEl.clientWidth;
                        if (max <= 2) {
                            return { atStart: true, atEnd: true };
                        }

                        var sl = scrollEl.scrollLeft;
                        var atStart;
                        var atEnd;

                        if (sl < 0) {
                            atStart = Math.abs(sl) >= max - 2;
                            atEnd = Math.abs(sl) <= 2;
                        } else {
                            atStart = sl <= 2;
                            atEnd = sl >= max - 2;
                        }

                        return { atStart: atStart, atEnd: atEnd };
                    }

                    function updateButtons() {
                        var state = scrollState();
                        prevBtn.disabled = state.atStart;
                        nextBtn.disabled = state.atEnd;
                    }

                    function scrollByPage(direction) {
                        var list = items();
                        if (!list.length) {
                            return;
                        }

                        var containerRect = scrollEl.getBoundingClientRect();
                        var target = null;

                        if (direction === 'next') {
                            for (var i = 0; i < list.length; i++) {
                                var rect = list[i].getBoundingClientRect();
                                if (rect.right > containerRect.right + 2) {
                                    target = list[i];
                                    break;
                                }
                            }
                            if (!target) {
                                target = list[list.length - 1];
                            }
                        } else {
                            for (var j = list.length - 1; j >= 0; j--) {
                                var prevRect = list[j].getBoundingClientRect();
                                if (prevRect.left < containerRect.left - 2) {
                                    target = list[j];
                                    break;
                                }
                            }
                            if (!target) {
                                target = list[0];
                            }
                        }

                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                inline: direction === 'next' ? 'start' : 'end',
                                block: 'nearest'
                            });
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
                        if (activeItem) {
                            activeItem.scrollIntoView({ behavior: 'auto', inline: 'center', block: 'nearest' });
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
