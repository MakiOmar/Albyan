{{-- Email shown via JS so static HTML does not contain a harvestable user@domain string (base64 in data attribute only). --}}
@php
    $email = $email ?? '';
    $linkClass = trim('js-obfuscated-mailto ' . ($class ?? ''));
    $aria = $ariaLabel ?? trans('footer.obfuscated_mailto_aria');
@endphp
@if($email !== '')
    <a href="#email-contact"
       class="{{ $linkClass }}"
       data-email-b64="{{ base64_encode($email) }}"
       rel="nofollow"
       aria-label="{{ $aria }}"><span class="js-obfuscated-mailto-label" dir="ltr" style="unicode-bidi: embed;"></span></a>
@endif
