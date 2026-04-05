@php
    $userLanguages = !empty($generalSettings['site_language']) ? [$generalSettings['site_language'] => getLanguages($generalSettings['site_language'])] : [];

    if (!empty($generalSettings['user_languages']) && is_array($generalSettings['user_languages'])) {
        $userLanguages = getLanguages($generalSettings['user_languages']);
    }

    $localLanguage = [];

    foreach($userLanguages as $key => $userLanguage) {
        $localLanguage[localeToCountryCode($key)] = $userLanguage;
    }
@endphp

<div class="top-navbar d-flex border-bottom blue-bg">
    <div id="top-nav-container" class="container d-flex justify-content-between align-items-center flex-lg-row">
        <div class="top-contact-box border-bottom d-flex flex-column flex-md-row align-items-center justify-content-center">

            @if(getOthersPersonalizationSettings('platform_phone_and_email_position') == 'header')
                <div class="d-flex align-items-center justify-content-center mr-15 mr-md-30">
                    @if(!empty($generalSettings['site_phone']))
                        <div class="d-flex align-items-center py-10 py-lg-0 text-dark-blue font-14">
                            <i data-feather="phone" width="20" height="20" class="mr-10"></i>
                            {{ $generalSettings['site_phone'] }}
                        </div>
                    @endif

                    @if(!empty($generalSettings['site_email']))
                        <div class="border-left mx-5 mx-lg-15 h-100"></div>

                        <div class="d-flex align-items-center py-10 py-lg-0 text-dark-blue font-14">
                            <i data-feather="mail" width="20" height="20" class="mr-10"></i>
                            {{ $generalSettings['site_email'] }}
                        </div>
                    @endif
                </div>
            @endif

            <div class="d-flex align-items-center justify-content-between justify-content-md-center">

                {{-- Currency --}}
                @include('web.default.includes.top_nav.currency')

                @if(!empty($localLanguage) && count($localLanguage) > 1)
                    <form action="/locale" method="post" class="mr-15 mx-md-20">
                        {{ csrf_field() }}

                        <input type="hidden" name="locale">

                        @if(!empty($previousUrl))
                            <input type="hidden" name="previous_url" value="{{ $previousUrl }}">
                        @endif

                        <div class="language-select">
                            <div id="localItems"
                                 data-selected-country="{{ localeToCountryCode(mb_strtoupper(app()->getLocale())) }}"
                                 data-countries='{{ json_encode($localLanguage) }}'
                            ></div>
                        </div>
                    </form>
                @else
                    <div class="mr-15 mx-md-20"></div>
                @endif

                <form action="/search" method="get" class="form-inline my-2 my-lg-0 navbar-search position-relative" style="min-height: 40px;min-width: 180px;">
                    <input class="blue-bg form-control mr-5 rounded-pill text-white" type="text" name="search" placeholder="{{ trans('navbar.search_anything') }}" aria-label="Search" style="min-height: 40px; height: 40px; width: 100%;">

                    <button type="submit" class="btn-transparent d-flex align-items-center justify-content-center search-icon text-white" aria-label="{{ trans('navbar.search') }}" style="min-width: 40px; min-height: 40px; width: 40px; height: 40px; position: absolute; right: 0; top: 0;">
                        <i data-feather="search" width="20" height="20" class="mr-10" style="display: block;"></i>
                    </button>
                </form>
            </div>
        </div>
        
        @php
            $socials = getSocials();
            
            if (!empty($socials) && count($socials)) {
                $socials = collect($socials)->sortBy('order')->values()->toArray();

                foreach ($socials as $id => $social) {
                    if ($social['title'] == 'Facebook') {
                        $socials[$id]['image'] = '/store/1/socials/211902_social_facebook.png';
                    } elseif ($social['title'] == 'Twitter') {
                        $socials[$id]['image'] = '/store/1/socials/11244080_x_twitter.png';
                    } elseif ($social['title'] == 'Instagram') {
                        $socials[$id]['image'] = '/store/1/socials/1161953_instagram_icon.png';
                    } elseif ($social['title'] == 'Whatsapp') {
                        $socials[$id]['image'] = '/store/1/socials/7156624_whatsapp.png';
                    } elseif ($social['title'] == 'Snapchat') {
                        $socials[$id]['image'] = '/store/1/socials/1851684_snap chat.png';
                    } elseif ($social['title'] == 'Linkedin') {
                        $socials[$id]['image'] = '/store/1/socials/367593_linkedin.png';
                    } elseif ($social['title'] == 'Tik Tok') {
                        $socials[$id]['image'] = '/store/1/socials/8547041_tiktok.png';
                    } elseif ($social['title'] == 'Youtube') {
                        $socials[$id]['image'] = '/store/1/socials/4375133_youtube.png';
                    }
                }
            }
        @endphp
        
        <div class="footer-social d-flex align-items-center" style="height: 40px; width: 100%; flex-wrap: nowrap;">
            @if(!empty($socials) && count($socials))
                @foreach($socials as $social)
                    <a href="{{ $social['link'] }}" target="_blank" class="mr-15 border border-white rounded-circle m-1 p-1" style="min-width: 40px; min-height: 40px; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ $social['image'] }}" alt="{{ $social['title'] }}" width="24" height="24" style="display: block;">
                    </a>
                @endforeach
            @endif
        </div>
        
        <div class="xs-w-100 d-flex align-items-center justify-content-between">
            <div class="d-flex">
                @include(getTemplate().'.includes.shopping-cart-dropdwon')

                <div class="border-left mx-5 mx-lg-15"></div>

                @include(getTemplate().'.includes.notification-dropdown')
            </div>

            {{-- User Menu --}}
            @include('web.default.includes.top_nav.user_menu')
        </div>
    </div>
</div>

@push('scripts_bottom')
    {{-- Flagstrap: load on first interaction with language control or after idle — shortens critical path --}}
    <script>
        (function () {
            var mount = document.getElementById('localItems');
            var wrap = document.querySelector('.language-select');
            if (!mount || !wrap) {
                return;
            }
            var done = false;
            function loadFlagStrap() {
                if (done) {
                    return;
                }
                done = true;
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = '/assets/default/vendors/flagstrap/css/flags.css';
                document.head.appendChild(link);
                var s1 = document.createElement('script');
                s1.src = '/assets/default/vendors/flagstrap/js/jquery.flagstrap.min.js';
                s1.onload = function () {
                    var s2 = document.createElement('script');
                    s2.src = '/assets/default/js/parts/top_nav_flags.min.js';
                    document.body.appendChild(s2);
                };
                s1.onerror = function () {
                    done = false;
                };
                document.body.appendChild(s1);
            }
            ['pointerdown', 'mouseenter', 'focusin', 'touchstart'].forEach(function (ev) {
                wrap.addEventListener(ev, loadFlagStrap, { capture: true, passive: true, once: true });
            });
            if (window.requestIdleCallback) {
                window.requestIdleCallback(function () {
                    loadFlagStrap();
                }, { timeout: 4000 });
            } else {
                window.setTimeout(loadFlagStrap, 4000);
            }
        })();
    </script>
@endpush
