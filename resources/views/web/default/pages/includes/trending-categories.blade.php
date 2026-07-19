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

    <div class="swiper-container trend-categories-swiper px-12 mt-40">
        <div class="swiper-wrapper py-20">
            @foreach($trendCategories as $trend)
                <div class="swiper-slide">
                    <a href="{{ $trend->category->getUrl() }}">
                        <div class="trending-card d-flex flex-column align-items-center w-100">
                            <div class="trending-image d-flex align-items-center justify-content-center w-100" style="background-color: {{ $trend->color }}">
                                <div class="icon mb-3">
                                    <img src="{{ $trend->getIcon() }}" width="10" class="img-cover" alt="">
                                </div>
                            </div>

                            <div class="item-count px-10 px-lg-20 py-5 py-lg-10">{{ $trend->category->webinars_count }} {{ trans('product.course') }}</div>

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

    <div class="d-flex justify-content-center">
        <div class="swiper-pagination trend-categories-swiper-pagination"></div>
    </div>
</section>
