{{-- Attaches click handler: opens mailto from data-email-b64 (link label stays human-readable text only). --}}
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
                    anchor.addEventListener('click', function (event) {
                        event.preventDefault();
                        window.location.href = 'mailto:' + email;
                    });
                });
            });
        })();
    </script>
@endonce
