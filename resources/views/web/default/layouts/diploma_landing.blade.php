{{-- Full-page diploma landing: homepage sections + lead form (no site navbar/footer) --}}
@php
    $isRtl = web_layout_is_rtl($generalSettings ?? null);
    $dlWhatsapp = $diplomaLandingWhatsapp ?? config('diploma_landing.whatsapp_number');
    $dlWhatsappDigits = !empty($dlWhatsapp) ? preg_replace('/\D/', '', $dlWhatsapp) : '';
    $dlCall = $diplomaLandingCall ?? config('diploma_landing.call_number');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    @include('web.default.includes.metas')
    <meta name="theme" content="{{ str_replace('web.', '', getTemplate()) }}">
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>
    <link rel="stylesheet" href="/assets/default/css/app.css">
    @if($isRtl)
        <link rel="stylesheet" href="/assets/default/css/rtl-app.css">
    @endif
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/owl-carousel2/owl.carousel.min.css">
    @include('web.default.includes.landing_google_cairo_font')
    @stack('styles_top')
    <style>
        body.dl-page { margin: 0; }
        .slider-heading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: absolute;
            z-index: 999;
            background-color: #ffffffa6;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            box-sizing: border-box;
            min-height: 4.25rem;
        }
        @media screen and (max-width: 480px) {
            .slider-heading {
                font-size: 16px;
                padding: 10px;
                min-height: 3.5rem;
            }
        }
        .category-courses-home-section {
            margin-bottom: 90px;
        }
        .diploma-landing-course-actions .btn { min-width: 120px; }
        .dl-topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(1, 71, 125, 0.1);
            padding: 12px 0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .dl-topbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .dl-topbar .logo img { max-height: 48px; width: auto; }
        .dl-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none !important;
            border: none;
            cursor: pointer;
        }
        .dl-btn-primary {
            background: linear-gradient(135deg, #01477d, #023a66);
            color: #fff !important;
        }
        .dl-btn-whatsapp {
            background: #25d366;
            color: #fff !important;
        }
        .dl-form-section {
            background: linear-gradient(180deg, #01477d 0%, #023a66 100%);
            color: #fff;
            padding: 56px 0;
        }
        .dl-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .dl-form-wrap {
            background: #fff;
            color: #1a2b3c;
            border-radius: 16px;
            padding: 28px;
            max-width: 640px;
            margin: 0 auto;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        .albyan-gallery {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }
        .albyan-gallery .swiper-slide img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }
        .albyan-gallery .swiper-slide img:hover { transform: scale(1.05); }
        .albyan-gallery .swiper-pagination {
            display: flex !important;
            flex-wrap: wrap;
            justify-content: center !important;
            align-items: center;
            column-gap: 14px;
            width: 100% !important;
            left: 0 !important;
            right: 0 !important;
            margin-inline: auto !important;
            text-align: center !important;
            position: relative;
            margin-top: 16px;
        }
        .albyan-gallery .swiper-pagination .swiper-pagination-bullet {
            margin: 0 !important;
        }
        .dl-certificates-gallery .swiper {
            width: 100%;
            height: 100%;
        }
        .dl-certificates-gallery .swiper-slide {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dl-certificates-gallery .swiper-slide a {
            display: block;
            width: 100%;
        }
    </style>
    @if(!empty($heroSectionData) && !empty($heroSectionData['is_video_background']))
        @php
            $__homeHeroPoster = trim((string) ($heroSectionData['hero_video_poster'] ?? ''));
        @endphp
        @if($__homeHeroPoster !== '')
            <link rel="preload" as="image" href="{{ $__homeHeroPoster }}" fetchpriority="high">
        @endif
    @endif
    @include('web.default.includes.gtm_head')
</head>
<body class="dl-page landing-google-cairo {{ $isRtl ? 'rtl' : '' }}">
@include('web.default.includes.gtm_noscript')

<header class="dl-topbar">
    <div class="dl-topbar-inner">
        <a href="/" class="logo">
            @if(!empty($generalSettings['logo']))
                <img src="{{ $generalSettings['logo'] }}" alt="{{ $generalSettings['site_name'] ?? '' }}">
            @else
                <span class="font-weight-bold text-dark-blue">{{ $generalSettings['site_name'] ?? '' }}</span>
            @endif
        </a>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if(!empty($dlWhatsappDigits))
                <a href="https://wa.me/{{ $dlWhatsappDigits }}" target="_blank" rel="noopener" class="dl-btn dl-btn-whatsapp">
                    <i class="fab fa-whatsapp"></i> {{ trans('public.whatsapp') }}
                </a>
            @endif
            <a href="#dl-register" class="dl-btn dl-btn-primary">سجل الآن</a>
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

@if(!empty(turnstile_site_key()))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script>window.turnstileSiteKey = @json(turnstile_site_key());</script>
@endif
<script src="/assets/default/js/app.min.js"></script>
<script src="/assets/default/js/image-lazy-loader.js" defer></script>
<script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
<script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>
<script>if (typeof feather !== 'undefined') { feather.replace(); }</script>
@stack('scripts_bottom')
</body>
</html>
