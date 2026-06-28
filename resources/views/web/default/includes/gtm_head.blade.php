@php
    $gtmEnabled = config('services.gtm.enabled') && !empty(config('services.gtm.container_id'));
    $gtmId = config('services.gtm.container_id');
    $gtmStrategy = config('services.gtm.load_strategy', 'idle');
    $gtmIdleTimeout = max(0, (int) config('services.gtm.idle_timeout_ms', 2500));
@endphp
@if($gtmEnabled)
    {{-- dns-prefetch: cheap hint for when gtm.js eventually loads (including idle strategy). --}}
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://www.google-analytics.com">
    @if($gtmStrategy === 'eager')
        {{-- preconnect only when gtm.js runs during initial load (idle-delayed loads trigger "unused preconnect" in Lighthouse). --}}
        <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
        {{-- Standard async GTM: earliest tag execution (stronger analytics, heavier main thread during load) --}}
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
        {{-- dataLayer immediately; gtm.js after load + idle (or timeout) — lighter PageSpeed, tags still run soon --}}
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
                    (function (w, d, s, l, i) {
                        w[l] = w[l] || [];
                        w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                        var f = d.getElementsByTagName(s)[0],
                            j = d.createElement(s),
                            dl = l !== 'dataLayer' ? '&l=' + l : '';
                        j.async = true;
                        j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                        f.parentNode.insertBefore(j, f);
                    })(w, d, 'script', 'dataLayer', id);
                }
                function schedule() {
                    if (w.requestIdleCallback) {
                        w.requestIdleCallback(loadGtm, { timeout: timeoutMs });
                    } else {
                        w.setTimeout(loadGtm, Math.min(timeoutMs, 2000));
                    }
                }
                if (d.readyState === 'complete') {
                    schedule();
                } else {
                    w.addEventListener('load', schedule, { once: true });
                }
            })(window, document);
        </script>
    @endif

    {{-- Ensure gtag() exists for the delayed-navigation helper below (queues into the same dataLayer GTM uses). --}}
    <script>
        window.dataLayer = window.dataLayer || [];
        if (typeof window.gtag !== 'function') {
            window.gtag = function () { window.dataLayer.push(arguments); };
        }
    </script>

    <!-- Google tag (gtag.js) event - delayed navigation helper -->
    <script>
        // Helper function to delay opening a URL until a gtag event is sent.
        // Call it in response to an action that should navigate to a URL.
        function gtagSendEvent(url) {
            var callback = function () {
                if (typeof url === 'string') {
                    window.location = url;
                }
            };
            gtag('event', 'ads_conversion___1', {
                'event_callback': callback,
                'event_timeout': 2000,
                // <event_parameters>
            });
            return false;
        }
    </script>
@endif
