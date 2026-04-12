{{-- Visible link text only; address lives in data-email-b64 and is used on click (no user@host in HTML source). --}}
@php
    $email = $email ?? '';
    $linkClass = trim('js-obfuscated-mailto ' . ($class ?? ''));
    $text = $linkText ?? trans('footer.obfuscated_mailto_link_text');
@endphp
@if($email !== '')
    {{-- Accessible name comes from visible link text; optional aria-label if callers pass $ariaLabel. --}}
    <a href="#email-contact"
       class="{{ $linkClass }}"
       data-email-b64="{{ base64_encode($email) }}"
       rel="nofollow"
       @if(!empty($ariaLabel))
           aria-label="{{ $ariaLabel }}"
       @endif>{{ $text }}</a>
@endif
