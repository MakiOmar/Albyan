{{-- Full-page landing layout: Cyber Security Diploma (RTL, no site navbar/footer) --}}
@php
    $isRtl = web_layout_is_rtl($generalSettings ?? null);
    $cslWhatsapp = config('cyber_security_landing.whatsapp_number');
    $cslWhatsappDigits = !empty($cslWhatsapp) ? preg_replace('/\D/', '', $cslWhatsapp) : '';
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    @include('web.default.includes.metas')
    <meta name="theme" content="{{ str_replace('web.', '', getTemplate()) }}">
    <title>{{ $pageTitle ?? 'دبلومة الأمن السيبراني' }}{{ !empty($generalSettings['site_name']) ? (' | '.$generalSettings['site_name']) : '' }}</title>
    <link rel="stylesheet" href="/assets/default/css/app.css">
    @if($isRtl)
        <link rel="stylesheet" href="/assets/default/css/rtl-app.css">
    @endif
    @include('web.default.includes.landing_google_cairo_font')
    @stack('styles_top')
    <style>
        :root {
            --csl-navy: #041428;
            --csl-navy-mid: #0a2540;
            --csl-blue: #01477d;
            --csl-cyan: #00d4aa;
            --csl-cyan-soft: rgba(0, 212, 170, 0.15);
            --csl-gold: #c9a227;
            --csl-white: #ffffff;
            --csl-muted: rgba(255, 255, 255, 0.72);
            --csl-card: rgba(255, 255, 255, 0.06);
            --csl-border: rgba(255, 255, 255, 0.12);
        }
        body.csl-page {
            background: var(--csl-navy);
            color: var(--csl-white);
            margin: 0;
            line-height: 1.7;
        }
        .csl-topbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(4, 20, 40, 0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--csl-border);
            padding: 12px 0;
        }
        .csl-topbar-inner {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .csl-logo img { max-height: 44px; width: auto; }
        .csl-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none !important;
            border: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .csl-btn:hover { transform: translateY(-2px); text-decoration: none !important; }
        .csl-btn-primary {
            background: linear-gradient(135deg, var(--csl-cyan), #00a884);
            color: #041428 !important;
            box-shadow: 0 8px 24px rgba(0, 212, 170, 0.35);
        }
        .csl-btn-outline {
            background: transparent;
            color: var(--csl-white) !important;
            border: 2px solid rgba(255,255,255,0.35);
        }
        .csl-btn-whatsapp {
            background: #25d366;
            color: #fff !important;
        }
        .csl-hero {
            position: relative;
            padding: 56px 20px 72px;
            overflow: hidden;
            background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(0, 212, 170, 0.12) 0%, transparent 55%),
                        linear-gradient(180deg, var(--csl-navy-mid) 0%, var(--csl-navy) 100%);
        }
        .csl-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(0, 212, 170, 0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0, 212, 170, 0.03) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none;
        }
        .csl-container {
            max-width: 1140px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        .csl-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            background: var(--csl-cyan-soft);
            border: 1px solid rgba(0, 212, 170, 0.35);
            color: var(--csl-cyan);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .csl-hero h1 {
            font-size: clamp(1.75rem, 4vw, 2.35rem);
            font-weight: 800;
            line-height: 1.35;
            margin-bottom: 16px;
        }
        .csl-hero-lead {
            font-size: clamp(1.05rem, 2.5vw, 1.25rem);
            color: var(--csl-muted);
            max-width: 720px;
            margin-bottom: 28px;
        }
        .csl-hero-subtitle {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--csl-cyan);
            margin-bottom: 24px;
        }
        .csl-highlights {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 32px;
        }
        .csl-highlight-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 14px 16px;
            background: var(--csl-card);
            border: 1px solid var(--csl-border);
            border-radius: 12px;
            font-size: 14px;
        }
        .csl-highlight-item::before {
            content: '✓';
            color: var(--csl-cyan);
            font-weight: 800;
            flex-shrink: 0;
        }
        .csl-tech-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 20px;
        }
        .csl-tech-tag {
            padding: 6px 14px;
            background: rgba(1, 71, 125, 0.6);
            border: 1px solid var(--csl-border);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }
        .csl-section {
            padding: 64px 20px;
        }
        .csl-section-alt {
            background: var(--csl-navy-mid);
        }
        /* Light sections: white background, dark typography */
        .csl-section-light {
            background: #f4f7fb;
            color: #1a2b3c;
        }
        .csl-section-light .csl-section-title {
            color: #041428;
        }
        .csl-section-light .csl-section-title::after {
            background: linear-gradient(90deg, var(--csl-blue), transparent);
        }
        .csl-section-light .csl-section-desc,
        .csl-section-light p,
        .csl-section-light li,
        .csl-section-light .text-white {
            color: #4a5568 !important;
        }
        .csl-section-light h2,
        .csl-section-light h3 {
            color: #041428;
        }
        .csl-section-light .csl-job-pill {
            background: #ffffff;
            border: 1px solid #dce4ee;
            color: #01477d;
            box-shadow: 0 2px 8px rgba(1, 71, 125, 0.06);
        }
        .csl-section-light .csl-salary-box {
            background: linear-gradient(135deg, #e6faf5 0%, #e8f2fa 100%);
            border: 1px solid rgba(1, 71, 125, 0.15);
            color: #1a2b3c;
        }
        .csl-section-light .csl-salary-box strong {
            color: #01477d;
        }
        .csl-section-light .csl-salary-box span {
            color: #5a6b7c;
        }
        .csl-section-light .csl-pricing-card {
            background: #ffffff;
            border: 1px solid #dce4ee;
            box-shadow: 0 16px 48px rgba(1, 71, 125, 0.1);
            color: #1a2b3c;
        }
        .csl-section-light .csl-price {
            color: #01477d;
        }
        .csl-section-light .csl-pricing-card ul,
        .csl-section-light .csl-pricing-card p {
            color: #4a5568 !important;
        }
        .csl-section-light .csl-faq .card {
            background: #ffffff;
            border: 1px solid #dce4ee;
            box-shadow: 0 2px 8px rgba(1, 71, 125, 0.05);
        }
        .csl-section-light .csl-faq .btn-link {
            color: #041428 !important;
        }
        .csl-section-light .csl-faq .card-body {
            color: #5a6b7c;
            border-top-color: #e8edf3;
        }
        .csl-section-light .csl-highlight-item {
            background: #ffffff;
            border-color: #dce4ee;
            color: #1a2b3c;
        }
        .csl-section-light .csl-highlight-item::before {
            color: #01477d;
        }
        .csl-section-title {
            font-size: clamp(1.5rem, 3vw, 1.85rem);
            font-weight: 800;
            margin-bottom: 12px;
            position: relative;
            padding-bottom: 12px;
        }
        .csl-section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 64px;
            height: 4px;
            background: linear-gradient(90deg, var(--csl-cyan), transparent);
            border-radius: 2px;
        }
        body.ltr .csl-section-title::after { right: auto; left: 0; }
        .csl-section-desc {
            color: var(--csl-muted);
            margin-bottom: 36px;
            max-width: 640px;
        }
        .csl-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .csl-card {
            background: var(--csl-card);
            border: 1px solid var(--csl-border);
            border-radius: 16px;
            padding: 24px;
            height: 100%;
        }
        .csl-card h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--csl-cyan);
        }
        .csl-card ul {
            margin: 0;
            padding: 0 20px 0 0;
            list-style: none;
        }
        body.ltr .csl-card ul { padding: 0 0 0 20px; }
        .csl-card li {
            position: relative;
            padding: 6px 0;
            padding-right: 18px;
            font-size: 14px;
            color: var(--csl-muted);
        }
        body.ltr .csl-card li { padding-right: 0; padding-left: 18px; }
        .csl-card li::before {
            content: '•';
            position: absolute;
            right: 0;
            color: var(--csl-cyan);
        }
        body.ltr .csl-card li::before { right: auto; left: 0; }
        .csl-jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .csl-job-pill {
            padding: 14px 18px;
            background: var(--csl-card);
            border: 1px solid var(--csl-border);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }
        .csl-salary-box {
            padding: 28px;
            background: linear-gradient(135deg, rgba(0, 212, 170, 0.12), rgba(1, 71, 125, 0.4));
            border: 1px solid rgba(0, 212, 170, 0.3);
            border-radius: 16px;
            text-align: center;
        }
        .csl-salary-box strong {
            display: block;
            font-size: 1.75rem;
            color: var(--csl-cyan);
            margin-top: 8px;
        }
        .csl-pricing-card {
            max-width: 520px;
            margin: 0 auto;
            padding: 36px;
            background: linear-gradient(160deg, rgba(1, 71, 125, 0.5), var(--csl-card));
            border: 1px solid var(--csl-border);
            border-radius: 20px;
            text-align: center;
        }
        .csl-price {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--csl-cyan);
            line-height: 1.2;
        }
        .csl-testimonials {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .csl-testimonial {
            padding: 24px;
            background: var(--csl-card);
            border-radius: 16px;
            border: 1px solid var(--csl-border);
            font-style: italic;
            color: var(--csl-muted);
        }
        .csl-testimonial::before {
            content: '"';
            font-size: 2.5rem;
            color: var(--csl-cyan);
            line-height: 0;
            display: block;
            margin-bottom: 8px;
        }
        .csl-faq .card {
            background: var(--csl-card);
            border: 1px solid var(--csl-border);
            border-radius: 12px;
            margin-bottom: 10px;
            overflow: hidden;
        }
        .csl-faq .card-header {
            background: transparent;
            border: none;
            padding: 0;
        }
        .csl-faq .btn-link {
            width: 100%;
            text-align: right;
            color: var(--csl-white) !important;
            font-weight: 600;
            padding: 18px 20px;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        body.ltr .csl-faq .btn-link { text-align: left; }
        .csl-faq .card-body {
            color: var(--csl-muted);
            padding: 0 20px 18px;
            border-top: 1px solid var(--csl-border);
        }
        .csl-form-section {
            background: linear-gradient(180deg, var(--csl-navy-mid), var(--csl-navy));
        }
        .csl-form-wrap {
            max-width: 560px;
            margin: 0 auto;
            padding: 36px;
            background: #fff;
            border-radius: 20px;
            color: #1a1a2e;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.35);
        }
        .csl-form-wrap .form-control,
        .csl-form-wrap .custom-select {
            border-radius: 10px;
            border-color: #dee2e6;
        }
        .csl-form-wrap .input-label { color: #333; font-weight: 600; }
        .csl-form-wrap h3 { color: var(--csl-blue); font-weight: 800; }
        .csl-sticky-cta {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            background: rgba(4, 20, 40, 0.96);
            border-top: 1px solid var(--csl-border);
            gap: 10px;
        }
        .csl-sticky-cta .csl-btn { flex: 1; padding: 14px; font-size: 14px; }
        @media (max-width: 768px) {
            .csl-sticky-cta { display: flex; }
            body.csl-page { padding-bottom: 80px; }
            .csl-topbar .csl-btn-outline { display: none; }
        }
        .csl-footer-note {
            text-align: center;
            padding: 32px 20px;
            color: #041428;;
            font-size: 13px;
            border-top: 1px solid var(--csl-border);
        }
    </style>
    @include('web.default.includes.gtm_head')
</head>
<body class="landing-google-cairo csl-page {{ $isRtl ? 'rtl' : 'ltr' }}">
    @include('web.default.includes.gtm_noscript')

    <header class="csl-topbar">
        <div class="csl-topbar-inner">
            <a href="/" class="csl-logo">
                @if(!empty($generalSettings['logo']))
                    <img src="/store/1/Logos/Untitled-1.png6523.png" alt="{{ $generalSettings['site_name'] ?? '' }}">
                @else
                    <span class="font-weight-bold text-white">{{ $generalSettings['site_name'] ?? '' }}</span>
                @endif
            </a>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="#register" class="csl-btn csl-btn-primary">سجل الحين</a>
                @if(!empty($cslWhatsappDigits))
                    <a href="https://wa.me/{{ $cslWhatsappDigits }}" target="_blank" rel="noopener" class="csl-btn csl-btn-whatsapp">واتساب</a>
                @endif
            </div>
        </div>
    </header>

    @yield('content')

    <div class="csl-sticky-cta">
        <a href="#register" class="csl-btn csl-btn-primary">سجل الحين</a>
        @if(!empty($cslWhatsappDigits))
            <a href="https://wa.me/{{ $cslWhatsappDigits }}" target="_blank" rel="noopener" class="csl-btn csl-btn-whatsapp">واتساب</a>
        @endif
    </div>

    @if(!empty(turnstile_site_key()))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <script>window.turnstileSiteKey = @json(turnstile_site_key());</script>
    @endif
    <script src="/assets/default/js/app.min.js"></script>
    <script src="/assets/default/vendors/feather-icons/dist/feather.min.js"></script>
    <script src="/assets/default/vendors/sweetalert2/dist/sweetalert2.min.js"></script>
    <script src="/assets/default/vendors/toast/jquery.toast.min.js"></script>
    @stack('scripts_bottom')
    <script>if (typeof feather !== 'undefined') feather.replace();</script>
</body>
</html>
