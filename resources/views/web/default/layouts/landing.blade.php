{{-- Minimal layout for /landing: no navbar, no footer, full-page background image --}}
@php
    $isRtl = web_layout_is_rtl($generalSettings ?? null);
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
    @include('web.default.includes.landing_google_cairo_font')
    @stack('styles_top')
    @stack('scripts_top')
    <style>
        .landing-page-wrap {
            min-height: 100vh;
            background: url('/store/1/1.png') center center no-repeat;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .landing-page-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 10px;
            margin-top: 20px;
            max-width: 560px;
            width: 100%;
        }
    </style>
    @include('web.default.includes.gtm_head')
</head>
<body class="landing-google-cairo {{ $isRtl ? 'rtl' : '' }}">
    @include('web.default.includes.gtm_noscript')
<div class="landing-page-wrap">
    <div class="landing-page-content">
        @yield('content')
    </div>
</div>
@if(!empty(turnstile_site_key()))
    {{-- Cloudflare Turnstile --}}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <script>window.turnstileSiteKey = @json(turnstile_site_key());</script>
@endif
<script src="/assets/default/js/app.min.js"></script>
<script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
<script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>
@stack('scripts_bottom')
</body>
</html>
