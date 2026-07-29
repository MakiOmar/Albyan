{{-- All-categories CTA: fixed column beside the categories swiper --}}
@php
    $trendingCategoriesSettings = $trendingCategoriesSettings ?? [];
    $trendingAllTitle = trim((string) ($trendingCategoriesSettings['all_button_title'] ?? ''))
        ?: trans('public.all_categories');
    $trendingAllLink = trim((string) ($trendingCategoriesSettings['all_button_link'] ?? ''))
        ?: '/categories';
    $allCardRadius = (int) ($trendingCategoriesSettings['card_border_radius'] ?? 24);
    if ($allCardRadius < 0) {
        $allCardRadius = 0;
    }
    if ($allCardRadius > 48) {
        $allCardRadius = 48;
    }
@endphp

{{-- Fixed column outside the swiper so the CTA stays visible while categories scroll --}}
<div class="trending-all-categories-col">
    <a href="{{ $trendingAllLink }}"
       class="trending-all-categories-link d-block text-decoration-none"
       style="--trending-all-radius: {{ $allCardRadius }}px;">
        <div class="trending-all-categories-card">
            <span class="trending-all-categories-label">{{ $trendingAllTitle }}</span>
        </div>
    </a>
</div>

@once
    @push('styles_top')
        <style>
            /* Row: swiper + fixed all-categories column */
            .trending-categories-row {
                display: flex;
                align-items: flex-start;
                gap: 16px;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
            }

            .trending-categories-swiper-col {
                flex: 1 1 0;
                min-width: 0;
                max-width: 100%;
                overflow: hidden;
            }

            .trending-categories-swiper-col .trend-categories-swiper {
                width: 100%;
                max-width: 100%;
                overflow: hidden;
                box-sizing: border-box;
            }

            .trending-all-categories-col {
                flex: 0 0 160px;
                width: 160px;
                max-width: 160px;
            }

            .trending-all-categories-link {
                display: block;
                height: 100%;
            }

            .trending-all-categories-card {
                position: relative;
                min-height: 210px;
                border-radius: var(--trending-all-radius, 24px);
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                isolation: isolate;
                background: linear-gradient(135deg, var(--primary, #01477d) 0%, #0a6aad 55%, #1a8fd1 100%);
                box-shadow: 0 12px 30px rgba(15, 42, 89, 0.12);
                transition: transform 0.35s ease, box-shadow 0.35s ease;
            }

            /* Animated circulating border via rotating conic gradient */
            .trending-all-categories-card::before {
                content: '';
                position: absolute;
                inset: -60%;
                background: conic-gradient(
                    from 0deg,
                    transparent 0%,
                    rgba(255, 255, 255, 0.95) 8%,
                    transparent 18%,
                    transparent 50%,
                    rgba(255, 255, 255, 0.7) 58%,
                    transparent 68%,
                    transparent 100%
                );
                animation: trending-all-border-spin 3.5s linear infinite;
                z-index: 0;
            }

            .trending-all-categories-card::after {
                content: '';
                position: absolute;
                inset: 3px;
                border-radius: calc(var(--trending-all-radius, 24px) - 2px);
                background: linear-gradient(135deg, var(--primary, #01477d) 0%, #0a6aad 55%, #1a8fd1 100%);
                z-index: 1;
            }

            .trending-all-categories-label {
                position: relative;
                z-index: 2;
                color: #fff;
                font-size: 18px;
                font-weight: 700;
                line-height: 1.35;
                text-align: center;
                padding: 0 18px;
            }

            .trending-all-categories-link:hover .trending-all-categories-card {
                transform: translateY(-8px);
                box-shadow: 0 18px 40px rgba(15, 42, 89, 0.2);
            }

            @keyframes trending-all-border-spin {
                to {
                    transform: rotate(360deg);
                }
            }

            @media (min-width: 992px) {
                .trending-all-categories-col {
                    flex-basis: 180px;
                    width: 180px;
                    max-width: 180px;
                }
            }

            @media (min-width: 1200px) {
                .trending-all-categories-col {
                    flex-basis: 200px;
                    width: 200px;
                    max-width: 200px;
                }
            }

            /* Stack on small screens so the swiper keeps full width */
            @media (max-width: 767px) {
                .trending-categories-row {
                    flex-direction: column;
                    gap: 12px;
                    /* Avoid double horizontal inset with .container */
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }

                .trending-categories-swiper-col {
                    flex: none;
                    width: 100%;
                    max-width: 100%;
                    min-width: 0;
                    overflow: hidden;
                }

                .trending-categories-swiper-col .trend-categories-swiper,
                .trending-categories-swiper-col .swiper-wrapper {
                    width: 100%;
                    max-width: 100%;
                }

                .trending-categories-swiper-col .swiper-slide {
                    box-sizing: border-box;
                    max-width: 100%;
                }

                .trending-all-categories-col {
                    flex: none;
                    width: 100%;
                    max-width: none;
                    order: -1;
                }

                .trending-all-categories-card {
                    min-height: 120px;
                }

                .trending-all-categories-label {
                    font-size: 16px;
                }
            }
        </style>
    @endpush
@endonce
