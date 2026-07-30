{{-- Early: define which gestures unlock deferred assets (must run before any unlock listeners). --}}
@php
    $__perfStrictInteraction = perfStrictInteractionMode();
@endphp
<script>
    (function () {
        var strict = @json($__perfStrictInteraction);
        // Lab/Lighthouse often fires scroll/mousemove without a real click — ignore those when ?lab=1 or ?strict_interaction=1.
        var full = ['pointerdown', 'touchstart', 'keydown', 'wheel', 'scroll', 'mousemove'];
        var strictEvents = ['pointerdown', 'touchstart', 'keydown'];
        window.__perfStrictInteraction = !!strict;
        window.__perfUnlockEvents = strict ? strictEvents.slice() : full.slice();
        window.__perfUnlockEventsWithClick = strict
            ? ['pointerdown', 'touchstart', 'keydown', 'click']
            : ['scroll', 'wheel', 'touchstart', 'pointerdown', 'keydown', 'click', 'mousemove'];
    })();
</script>
