@php
    // Keep the noscript fallback aligned with gtm_head: homepage only for now.
    $gtmCurrentAction = optional(request()->route())->getActionName();
    $gtmIsHomepage = $gtmCurrentAction === 'App\Http\Controllers\Web\HomeController@index' || request()->is('/');

    $gtmEnabled = $gtmIsHomepage
        && config('services.gtm.enabled')
        && !empty(config('services.gtm.container_id'));
    $gtmId = config('services.gtm.container_id');
@endphp
@if($gtmEnabled)
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
            height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
@endif
