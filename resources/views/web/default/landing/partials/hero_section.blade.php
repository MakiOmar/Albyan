{{-- Exact copy of homepage hero (slider-container + video background) --}}
@if(!empty($heroSectionData))

    @if(!empty($heroSectionData['has_lottie']) and $heroSectionData['has_lottie'] == "1")
        @push('scripts_bottom')
            <script src="/assets/default/vendors/lottie/lottie-player.js"></script>
        @endpush
    @endif

    @php
        $__heroSectionStyle = 'min-height: 300px; padding: 0;';
        if (empty($heroSectionData['is_video_background']) && !empty($heroSectionData['hero_background'])) {
            $__heroSectionStyle .= " background-image: url('" . e($heroSectionData['hero_background']) . "');";
        }
    @endphp
    <section class="{{ ($heroSection == "2") ? 'slider-hero-section2 ' : '' }}slider-container" style="{{ $__heroSectionStyle }}">
        <h2 class="slider-heading">{{ trans('site.graduation_celebration_title') }}</h2>
        @if($heroSection == "1")
            @if(!empty($heroSectionData['is_video_background']))
                @php
                    $heroVideoPoster = trim((string) ($heroSectionData['hero_video_poster'] ?? ''));
                    $heroVideoPreload = $heroVideoPoster !== '' ? 'none' : 'metadata';
                @endphp
                <video
                    id="homeHeroVideoBackground"
                    class="img-cover"
                    playsinline
                    muted
                    loop
                    preload="{{ $heroVideoPreload }}"
                    fetchpriority="high"
                    @if($heroVideoPoster !== '') poster="{{ $heroVideoPoster }}" @endif
                >
                    <source src="{{ $heroSectionData['hero_background'] }}" type="video/mp4">
                </video>
                @push('scripts_bottom')
                    <script>
                        (function () {
                            var video = document.getElementById('homeHeroVideoBackground');
                            if (!video) return;
                            var unlocked = false;
                            function startHeroVideoOnInteraction() {
                                if (unlocked) return;
                                unlocked = true;
                                ['pointerdown', 'touchstart', 'keydown', 'wheel', 'scroll'].forEach(function (ev) {
                                    document.removeEventListener(ev, startHeroVideoOnInteraction, true);
                                });
                                var playPromise = video.play();
                                if (playPromise && typeof playPromise.catch === 'function') {
                                    playPromise.catch(function () {});
                                }
                            }
                            ['pointerdown', 'touchstart', 'keydown', 'wheel', 'scroll'].forEach(function (ev) {
                                document.addEventListener(ev, startHeroVideoOnInteraction, { capture: true, passive: true });
                            });
                        })();
                    </script>
                @endpush
            @endif

            <div class="mask"></div>
        @endif
    </section>
@endif
