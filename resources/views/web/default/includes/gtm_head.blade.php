@php
    $gtmEnabled = config('services.gtm.enabled') && !empty(config('services.gtm.container_id'));
    $gtmId = config('services.gtm.container_id');
    $gtmStrategy = config('services.gtm.load_strategy', 'interaction');
    $gtmIdleTimeout = max(0, (int) config('services.gtm.idle_timeout_ms', 12000));
@endphp
@if($gtmEnabled)
    {{-- dns-prefetch only (no preconnect) — avoids unused-preconnect when tags load late. --}}
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://www.google-analytics.com">
    @if($gtmStrategy === 'eager')
        <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
        <script>
            (function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l !== 'dataLayer' ? '&l=' + l : '';
                j.async = true;
                j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', @json($gtmId));
        </script>
    @else
        {{--
            interaction (default) / idle:
            - Intentionally ignore scroll/wheel so Lighthouse (and auto-scroll) does not pull GTM/FB/Clarity into the LCP/TBT window.
            - Real users unlock via pointer/touch/key; fallback idle after idle_timeout_ms (default 12s).
            - GTM Custom Event "site_interactive" is pushed when gtm.js starts — wire FB Pixel / Clarity to that event.
        --}}
        <script>
            (function (w, d) {
                w.dataLayer = w.dataLayer || [];
                var id = @json($gtmId);
                var timeoutMs = {{ $gtmIdleTimeout }};
                function loadGtm() {
                    if (w.__gtmScriptLoaded) {
                        return;
                    }
                    w.__gtmScriptLoaded = true;
                    ['pointerdown', 'touchstart', 'keydown'].forEach(function (ev) {
                        w.removeEventListener(ev, onInteract, true);
                    });
                    (function (w, d, s, l, i) {
                        w[l] = w[l] || [];
                        w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                        w[l].push({ event: 'site_interactive' });
                        var f = d.getElementsByTagName(s)[0],
                            j = d.createElement(s),
                            dl = l !== 'dataLayer' ? '&l=' + l : '';
                        j.async = true;
                        j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                        f.parentNode.insertBefore(j, f);
                    })(w, d, 'script', 'dataLayer', id);
                }
                function onInteract() {
                    loadGtm();
                }
                function scheduleIdle() {
                    /* Delay scheduling until after load so LCP/FCP window stays clear. */
                    w.setTimeout(function () {
                        if (w.requestIdleCallback) {
                            w.requestIdleCallback(loadGtm, { timeout: Math.max(1000, timeoutMs - 2000) });
                        } else {
                            w.setTimeout(loadGtm, Math.max(1000, timeoutMs - 2000));
                        }
                    }, 2000);
                }
                ['pointerdown', 'touchstart', 'keydown'].forEach(function (ev) {
                    w.addEventListener(ev, onInteract, { capture: true, passive: true });
                });
                if (d.readyState === 'complete') {
                    scheduleIdle();
                } else {
                    w.addEventListener('load', scheduleIdle, { once: true });
                }
            })(window, document);
        </script>
    @endif

    <script>
        window.dataLayer = window.dataLayer || [];
        if (typeof window.gtag !== 'function') {
            window.gtag = function () { window.dataLayer.push(arguments); };
        }
    </script>

    <script>
        function gtagSendEvent(url) {
            var callback = function () {
                if (typeof url === 'string') {
                    window.location = url;
                }
            };
            gtag('event', 'ads_conversion___1', {
                'event_callback': callback,
                'event_timeout': 2000
            });
            return false;
        }
    </script>
@endif
