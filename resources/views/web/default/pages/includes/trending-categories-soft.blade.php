@php
    // Soft floating cards layout (white card, no trend color background).
    $trendingCategoriesSettings = $trendingCategoriesSettings ?? [];
    $trendingCategoriesTitle = trim((string) ($trendingCategoriesSettings['title'] ?? ''))
        ?: trans('home.trending_categories');
    $trendingCategoriesHint = trim((string) ($trendingCategoriesSettings['hint'] ?? ''))
        ?: trans('home.trending_categories_hint');

    // Style controls from Home content blocks settings
    $cardRadius = (int) ($trendingCategoriesSettings['card_border_radius'] ?? 24);
    if ($cardRadius < 0) {
        $cardRadius = 0;
    }
    if ($cardRadius > 48) {
        $cardRadius = 48;
    }

    $shadowKey = trim((string) ($trendingCategoriesSettings['card_shadow'] ?? 'soft'));
    $shadowMap = [
        'none' => 'none',
        'soft' => '0 12px 30px rgba(15, 42, 89, 0.08)',
        'medium' => '0 18px 40px rgba(15, 42, 89, 0.14)',
        'strong' => '0 24px 50px rgba(15, 42, 89, 0.22)',
    ];
    $cardShadow = $shadowMap[$shadowKey] ?? $shadowMap['soft'];
@endphp

<section class="home-sections home-sections-swiper container trending-soft-section">
    <h2 class="section-title">{{ $trendingCategoriesTitle }}</h2>
    @if($trendingCategoriesHint !== '')
        <p class="section-hint">{{ $trendingCategoriesHint }}</p>
    @endif

    {{-- Fixed all-categories column + scrolling categories swiper --}}
    <div class="trending-categories-row mt-10">
        <div class="trending-categories-swiper-col">
            <div class="swiper-container trend-categories-swiper">
                <div class="swiper-wrapper py-30">
                    @foreach($trendCategories as $trend)
                        <div class="swiper-slide">
                            <a href="{{ $trend->category->getUrl() }}" class="trending-soft-link d-block text-decoration-none">
                                <div class="trending-soft-card text-center"
                                     style="--trending-soft-radius: {{ $cardRadius }}px; --trending-soft-shadow: {{ $cardShadow }};">
                                    <div class="trending-soft-media d-flex align-items-center justify-content-center">
                                        {{-- Placeholder + data-src so deferred image-lazy-loader controls the fetch --}}
                                        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                             data-src="{{ $trend->getIcon() }}"
                                             class="trending-soft-image"
                                             alt="{{ $trend->category->title }}"
                                             width="180"
                                             height="180"
                                             loading="lazy"
                                             decoding="async">

                                        {{-- Count pill overlaid on the bottom edge of the media area --}}
                                        <span class="trending-soft-count">
                                            {{ $trend->category->webinars_count }} {{ trans('product.course') }}
                                        </span>
                                    </div>
                                </div>

                                <h3 class="trending-soft-title">{{ $trend->category->title }}</h3>
                                @if(!empty($trend->category->description))
                                    <p class="trending-soft-description mb-0">{{ $trend->category->description }}</p>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @include('web.default.pages.includes.trending-all-categories-slide', [
            'trendingCategoriesSettings' => $trendingCategoriesSettings,
        ])
    </div>

    <div class="d-flex justify-content-center">
        <div class="swiper-pagination trend-categories-swiper-pagination"></div>
    </div>
</section>

@push('styles_top')
    <style>
        .trending-soft-card {
            position: relative;
            background: #fff;
            border-radius: var(--trending-soft-radius, 24px);
            box-shadow: var(--trending-soft-shadow, 0 12px 30px rgba(15, 42, 89, 0.08));
            transition: transform 0.35s ease, box-shadow 0.35s ease;
            overflow: hidden;
        }

        .trending-soft-media {
            position: relative;
            min-height: 210px;
            padding: 10px;
            border-radius: var(--trending-soft-radius, 24px);
            /* Soft tint so empty/lazy placeholders are not invisible white-on-white */
            background: #f3f6fb;
        }

        .trending-soft-image {
            width: auto;
            max-width: 100%;
            height: auto;
            max-height: 180px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }

        .trending-soft-count {
            position: absolute;
            left: 50%;
            bottom: 20px;
            transform: translateX(-50%);
            white-space: nowrap;
            background: #fff;
            color: #171347;
            border-radius: 999px;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(15, 42, 89, 0.12);
            z-index: 2;
        }

        .trending-soft-title {
            margin-top: 15px;
            font-size: 16px;
            font-weight: 600;
            line-height: 1.35;
            color: #171347;
            text-align: center;
        }

        .trending-soft-description {
            margin-top: 8px;
            font-size: 12px;
            line-height: 1.5;
            color: #7c8698;
            text-align: center;
            padding: 0 8px;
        }

        .trending-soft-link:hover .trending-soft-card {
            transform: translateY(-8px);
        }

        .trend-categories-swiper .swiper-pagination,
        .trend-categories-swiper-pagination {
            bottom: 5px;
        }

        /* Match swiper-wrapper py-30 so the CTA aligns with soft category cards */
        .trending-soft-section .trending-all-categories-col {
            padding-top: 30px;
        }

        @media (max-width: 767px) {
            .trending-soft-section.container {
                padding-left: 16px;
                padding-right: 16px;
            }

            .trending-soft-media {
                min-height: 180px;
            }

            .trending-soft-image {
                max-height: 140px;
            }

            .trending-soft-title {
                font-size: 15px;
                padding: 0 8px;
            }

            .trending-soft-section .trending-all-categories-col {
                padding-top: 0;
            }

            /* Keep soft card fully inside the mobile viewport */
            .trending-soft-section .swiper-slide {
                padding: 0 2px;
                box-sizing: border-box;
            }
        }
    </style>
@endpush
