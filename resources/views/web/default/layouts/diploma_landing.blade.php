{{-- Full-page diploma landing: home sections + lead form (no site navbar/footer) --}}
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
    @include('web.default.includes.landing_google_cairo_font')
    @stack('styles_top')
    <style>
        body.dl-page { margin: 0; background: #f8fafb; color: #1a2b3c; }
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
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .dl-btn:hover { transform: translateY(-1px); text-decoration: none !important; }
        .dl-btn-primary {
            background: linear-gradient(135deg, #01477d, #023a66);
            color: #fff !important;
        }
        .dl-btn-whatsapp {
            background: #25d366;
            color: #fff !important;
        }
        .dl-btn-call {
            background: #fff;
            color: #01477d !important;
            border: 2px solid #01477d;
        }
        .dl-section { padding: 56px 0; }
        .dl-section-alt { background: #fff; }
        .dl-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .diploma-landing-course-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            height: 100%;
        }
        .diploma-landing-course-card .webinar-card { box-shadow: none; margin: 0; }
        .diploma-landing-course-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 12px 16px;
        }
        .diploma-landing-course-actions .dl-btn { flex: 1; min-width: 120px; font-size: 13px; padding: 8px 14px; }
        .dl-form-section {
            background: linear-gradient(180deg, #01477d 0%, #023a66 100%);
            color: #fff;
        }
        .dl-form-wrap {
            background: #fff;
            color: #1a2b3c;
            border-radius: 16px;
            padding: 28px;
            max-width: 640px;
            margin: 0 auto;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        .albyan-gallery.dl-gallery {
            max-width: 1200px;
            height: 380px;
            margin: auto;
            padding: 20px 0;
            overflow: hidden;
            position: relative;
        }
        .albyan-gallery.dl-gallery .swiper-slide img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }
        .albyan-gallery.dl-gallery .swiper-slide img:hover { transform: scale(1.03); }
    </style>
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
<script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
<script>if (typeof feather !== 'undefined') { feather.replace(); }</script>
@stack('scripts_bottom')
@include('web.default.includes.gtm_body')
</body>
</html>
