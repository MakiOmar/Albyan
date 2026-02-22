{{-- Minimal layout for /landing: no navbar, no footer, full-page background image --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
@php
    $rtlLanguages = !empty($generalSettings['rtl_languages']) ? $generalSettings['rtl_languages'] : [];
    $isRtl = ((in_array(mb_strtoupper(app()->getLocale()), $rtlLanguages)) or (!empty($generalSettings['rtl_layout']) and $generalSettings['rtl_layout'] == 1));
@endphp
<head>
    @include('web.default.includes.metas')
    <meta name="theme" content="{{ str_replace('web.', '', getTemplate()) }}">
    <title>{{ $pageTitle ?? '' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/default/css/app.min.css?v={{ time() }}">
    @if($isRtl)
        <link rel="stylesheet" href="/assets/default/css/rtl-app.min.css">
    @endif
    @stack('styles_top')
    @stack('scripts_top')
    <style>
        body { font-family: 'Cairo', sans-serif; }
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
</head>
<body class="{{ $isRtl ? 'rtl' : '' }}">
<div class="landing-page-wrap">
    <div class="landing-page-content">
        @yield('content')
    </div>
</div>
<script src="/assets/default/js/app.js"></script>
<script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
<script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
<script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>
@stack('scripts_bottom')
</body>
</html>
