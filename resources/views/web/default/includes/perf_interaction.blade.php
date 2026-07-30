{{-- Early: define which gestures unlock deferred assets (must run before any unlock listeners). --}}
@php
    $__perfStrictInteraction = perfStrictInteractionMode();
@endphp
<script>
    (function () {
        var lab = @json($__perfStrictInteraction);
        // Page scroll/wheel are not treated as interaction (Lighthouse/auto-scroll). Mousemove still unlocks.
        var unlockEvents = ['pointerdown', 'touchstart', 'keydown', 'mousemove'];
        window.__perfStrictInteraction = !!lab;
        window.__perfUnlockEvents = unlockEvents.slice();
        window.__perfUnlockEventsWithClick = ['pointerdown', 'touchstart', 'keydown', 'click', 'mousemove'];
        // ?lab=1 additionally skips idle auto-load of GTM/vendors/feather (gesture-only).
    })();
</script>
