{{-- Initializes .js-obfuscated-mailto links: decodes data-email-b64 and opens mailto on click (no plaintext address in source). --}}
@once
    <script>
        (function () {
            'use strict';
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('a.js-obfuscated-mailto[data-email-b64]').forEach(function (anchor) {
                    var b64 = anchor.getAttribute('data-email-b64');
                    if (!b64) {
                        return;
                    }
                    var email;
                    try {
                        email = atob(b64);
                    } catch (e) {
                        return;
                    }
                    if (!email) {
                        return;
                    }
                    var label = anchor.querySelector('.js-obfuscated-mailto-label');
                    if (label) {
                        label.textContent = email;
                    } else if (!anchor.textContent.trim()) {
                        anchor.appendChild(document.createTextNode(email));
                    }
                    anchor.addEventListener('click', function (event) {
                        event.preventDefault();
                        window.location.href = 'mailto:' + email;
                    });
                });
            });
        })();
    </script>
@endonce
