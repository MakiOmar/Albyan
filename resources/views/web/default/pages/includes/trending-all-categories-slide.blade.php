{{-- All-categories CTA slide: gradient card, centered label, animated circulating border --}}
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

<div class="swiper-slide">
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
            .trending-all-categories-link {
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

            @media (max-width: 767px) {
                .trending-all-categories-card {
                    min-height: 170px;
                }

                .trending-all-categories-label {
                    font-size: 16px;
                }
            }
        </style>
    @endpush
@endonce
