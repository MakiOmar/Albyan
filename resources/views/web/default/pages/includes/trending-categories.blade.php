@php
    // Settings are translated per locale; language files remain the fallback.
    $trendingCategoriesSettings = $trendingCategoriesSettings ?? [];
    $trendingCategoriesTitle = trim((string) ($trendingCategoriesSettings['title'] ?? ''))
        ?: trans('home.trending_categories');
    $trendingCategoriesHint = trim((string) ($trendingCategoriesSettings['hint'] ?? ''))
        ?: trans('home.trending_categories_hint');
@endphp

<section class="home-sections home-sections-swiper container">
    <h2 class="section-title">{{ $trendingCategoriesTitle }}</h2>
    @if($trendingCategoriesHint !== '')
        <p class="section-hint">{{ $trendingCategoriesHint }}</p>
    @endif

    {{-- Fixed all-categories column + scrolling categories swiper --}}
    <div class="trending-categories-row mt-10">
        <div class="trending-categories-swiper-col">
            <div class="swiper-container trend-categories-swiper">
                <div class="swiper-wrapper py-20">
                    @foreach($trendCategories as $trend)
                        <div class="swiper-slide">
                            <a href="{{ $trend->category->getUrl() }}">
                                <div class="trending-card d-flex flex-column align-items-center w-100">
                                    <div class="trending-image d-flex align-items-center justify-content-center w-100" style="background-color: {{ $trend->color }}">
                                        <div class="icon mb-3">
                                            {{-- Placeholder + data-src so deferred image-lazy-loader controls the fetch --}}
                                            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                                 data-src="{{ $trend->getIcon() }}"
                                                 width="10"
                                                 height="10"
                                                 class="img-cover"
                                                 alt=""
                                                 loading="lazy"
                                                 decoding="async">
                                        </div>
                                    </div>

                                    <div class="item-count px-10 px-lg-20 py-5 py-lg-10">{{ $trend->category->webinars_count }} {{ settingOrTrans(getHomeContentBlocksSettings('trending_categories')['course_label'] ?? '', 'product.course') }}</div>

                                    <h3>{{ $trend->category->title }}</h3>
                                    @if(!empty($trend->category->description))
                                        <p class="trending-card-description font-12 text-gray mt-10 mb-0 text-center">{{ $trend->category->description }}</p>
                                    @endif
                                </div>
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
        .trend-categories-swiper .swiper-pagination,
        .trend-categories-swiper-pagination {
            bottom: 5px;
        }

        /* Match swiper-wrapper py-20 so the CTA aligns with category cards */
        .trending-all-categories-col {
            padding-top: 20px;
        }
    </style>
@endpush
