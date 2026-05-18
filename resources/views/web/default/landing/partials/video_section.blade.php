{{-- Homepage video / image promo section --}}
@if(!empty($boxVideoOrImage))
    <section class="home-sections home-sections-swiper position-relative">
        <div class="home-video-mask"></div>
        <div class="container home-video-container d-flex flex-column align-items-center justify-content-center position-relative{{ !empty($boxVideoOrImage['background']) ? ' js-deferred-section-bg' : '' }}" @if(!empty($boxVideoOrImage['background'])) data-deferred-bg="{{ $boxVideoOrImage['background'] }}" @endif>
            @if(!empty($boxVideoOrImage['link']))
                <a href="{{ $boxVideoOrImage['link'] }}" class="home-video-play-button d-flex align-items-center justify-content-center position-relative">
                    <i data-feather="play" width="36" height="36"></i>
                </a>
            @endif
            <div class="mt-50 pt-10 text-center">
                @if(!empty($boxVideoOrImage['title']))
                    <h1 class="home-video-title">{{ $boxVideoOrImage['title'] }}</h1>
                @endif
                @if(!empty($boxVideoOrImage['description']))
                    <p class="home-video-hint mt-10">{{ $boxVideoOrImage['description'] }}</p>
                @endif
            </div>
        </div>
    </section>
@endif
