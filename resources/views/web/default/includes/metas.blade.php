<meta charset="utf-8">
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name='robots' content="{{ $pageRobot ?? 'index, follow, all' }}">

@if (isset($pageDescription) and !empty($pageDescription))
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:description" content="{{ (!empty($ogDescription)) ? $ogDescription : $pageDescription }}">
    <meta name='twitter:description' content='{{ (!empty($ogDescription)) ? $ogDescription : $pageDescription }}'>
@endif

<!-- Favicon Configuration -->
@if(!empty($generalSettings['fav_icon']))
    <!-- Standard favicon -->
    <link rel="icon" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="shortcut icon" href="{{ url($generalSettings['fav_icon']) }}">
    
    <!-- Modern browsers -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url($generalSettings['fav_icon']) }}">
    
    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" sizes="57x57" href="{{ url($generalSettings['fav_icon']) }}">
    <link rel="apple-touch-icon" href="{{ url($generalSettings['fav_icon']) }}">
@else
    <!-- Fallback favicon -->
    <link rel="icon" href="{{ url('/favicon.ico') }}">
    <link rel="shortcut icon" href="{{ url('/favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ url('/favicon.ico') }}">
@endif

<link rel="manifest" href="/mix-manifest.json?v=4">
<meta name="theme-color" content="#FFF">
<!-- Windows Phone -->
<meta name="msapplication-starturl" content="/">
<meta name="msapplication-TileColor" content="#FFF">
<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
<!-- iOS Safari -->
<meta name="apple-mobile-web-app-title" content="{{ !empty($generalSettings['site_name']) ? $generalSettings['site_name'] : '' }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<!-- Android -->
<meta name="application-name" content="{{ !empty($generalSettings['site_name']) ? $generalSettings['site_name'] : '' }}">
<meta name="mobile-web-app-capable" content="yes">
<!-- Other -->
<meta name="layoutmode" content="fitscreen/standard">
<link rel="home" href="{{ url('') }}">

<!-- Open Graph -->
<meta property='og:title' content='{{ $pageTitle ?? '' }}'>
<meta name='twitter:card' content='summary'>
<meta name='twitter:title' content='{{ $pageTitle ?? '' }}'>

@php
    if (empty($pageMetaImage)) {
        $pageMetaImage = !empty($generalSettings['fav_icon']) ? $generalSettings['fav_icon'] : '/';
    }
@endphp

<meta property='og:site_name' content='{{ url(!empty($generalSettings['site_name']) ? $generalSettings['site_name'] : '') }}'>
<meta property='og:image' content='{{ url($pageMetaImage) }}'>
<meta name='twitter:image' content='{{ url($pageMetaImage) }}'>
<meta property='og:locale' content='{{ url(!empty($generalSettings['locale']) ? $generalSettings['locale'] : 'en_US') }}'>
<meta property='og:type' content='website'>

{{-- Multilingual SEO: canonical + hreflang (only when URL is locale-prefixed) --}}
<link rel="canonical" href="{{ url()->current() }}">
@php
    $supportedLocalesMap = getUserLanguagesLists();
    $supportedLocaleCodes = array_values(array_unique(array_map(function ($code) {
        return mb_strtolower($code);
    }, array_keys($supportedLocalesMap))));

    $firstSegment = mb_strtolower((string) request()->segment(1));
    $isLocalePrefixed = !empty($firstSegment) && in_array($firstSegment, $supportedLocaleCodes, true);

    $hreflangUrlByLocale = [];
    if ($isLocalePrefixed) {
        $pathSegments = array_values(array_filter(explode('/', request()->path())));

        // Remove existing locale prefix segment so we can re-apply it for each hreflang.
        if (!empty($pathSegments) && in_array(mb_strtolower($pathSegments[0]), $supportedLocaleCodes, true)) {
            array_shift($pathSegments);
        }

        $buildUrlForLocale = function ($localeCode) use ($pathSegments) {
            $segments = array_merge([$localeCode], $pathSegments);
            return url('/') . '/' . implode('/', $segments);
        };

        foreach ($supportedLocaleCodes as $localeCode) {
            $hreflangUrlByLocale[$localeCode] = $buildUrlForLocale($localeCode);
        }
    }
@endphp
@foreach($hreflangUrlByLocale as $localeCode => $href)
    <link rel="alternate" hreflang="{{ $localeCode }}" href="{{ $href }}">
@endforeach


{!! getSeoMetas('extra_meta_tags') !!}

