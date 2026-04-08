{{-- Cloudflare Turnstile: requires TURNSTILE_SITE_KEY; server validates when TURNSTILE_SECRET_KEY is set --}}
@php
    $__tsKey = turnstile_site_key();
@endphp
@if(!empty($__tsKey))
    <div class="form-group turnstile-widget-wrap mb-0">
        <div class="cf-turnstile" data-sitekey="{{ $__tsKey }}"></div>
        @error('cf-turnstile-response')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
@endif
