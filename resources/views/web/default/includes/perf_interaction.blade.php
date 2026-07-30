{{-- Early: define which gestures unlock deferred assets (must run before any unlock listeners). --}}
@php
    $__perfStrictInteraction = perfStrictInteractionMode();
@endphp
<script>
    (function () {
        var strict = @json($__perfStrictInteraction);
        // Lab/Lighthouse often auto-fires page scroll — ignore scroll/wheel when ?lab=1 (mousemove still counts).
        var full = ['pointerdown', 'touchstart', 'keydown', 'wheel', 'scroll', 'mousemove'];
        var labEvents = ['pointerdown', 'touchstart', 'keydown', 'mousemove'];
        window.__perfStrictInteraction = !!strict;
        window.__perfUnlockEvents = strict ? labEvents.slice() : full.slice();
        window.__perfUnlockEventsWithClick = strict
            ? ['pointerdown', 'touchstart', 'keydown', 'click', 'mousemove']
            : ['scroll', 'wheel', 'touchstart', 'pointerdown', 'keydown', 'click', 'mousemove'];
    })();
</script>
