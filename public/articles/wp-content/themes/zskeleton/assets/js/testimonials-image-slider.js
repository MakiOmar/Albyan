/**
 * Testimonials image carousel: scroll only the viewport (not the page).
 *
 * @package ZSkeleton_Theme
 */
( function () {
	'use strict';

	/**
	 * Scroll horizontal viewport so the slide is centered; does not call scrollIntoView on ancestors.
	 *
	 * @param {HTMLElement} viewport
	 * @param {HTMLElement} slide
	 * @param {boolean}     smooth
	 */
	function scrollViewportToSlide( viewport, slide, smooth ) {
		if ( ! viewport || ! slide ) {
			return;
		}
		var vp = viewport.getBoundingClientRect();
		var sl = slide.getBoundingClientRect();
		var delta = sl.left + sl.width / 2 - ( vp.left + vp.width / 2 );
		var next = viewport.scrollLeft + delta;
		var maxScroll = Math.max( 0, viewport.scrollWidth - viewport.clientWidth );
		var cs = window.getComputedStyle ? window.getComputedStyle( viewport ).direction : '';
		var isRtlViewport = cs === 'rtl';
		if ( isRtlViewport ) {
			next = Math.max( -maxScroll, Math.min( maxScroll, next ) );
		} else {
			if ( next < 0 ) {
				next = 0;
			}
			if ( next > maxScroll ) {
				next = maxScroll;
			}
		}
		if ( viewport.scrollTo ) {
			viewport.scrollTo( { left: next, behavior: smooth ? 'smooth' : 'auto' } );
		} else {
			viewport.scrollLeft = next;
		}
	}

	/**
	 * @param {HTMLElement} root Carousel root ([data-zskeleton-tis-carousel]).
	 */
	function initCarousel( root ) {
		var viewport = root.querySelector( '[data-zskeleton-tis-viewport]' );
		var track = root.querySelector( '[data-zskeleton-tis-track]' );
		if ( ! viewport || ! track ) {
			return;
		}
		var slides = root.querySelectorAll( '[data-zskeleton-tis-slide]' );
		var dots = root.querySelectorAll( '[data-zskeleton-tis-dot]' );
		if ( slides.length < 2 ) {
			return;
		}

		var hasDots = dots.length >= 2;
		var index = 0;

		function syncDots() {
			if ( ! hasDots ) {
				return;
			}
			for ( var d = 0; d < dots.length; d++ ) {
				dots[ d ].setAttribute( 'aria-selected', d === index ? 'true' : 'false' );
				dots[ d ].classList.toggle( 'is-active', d === index );
			}
		}

		function setActive( i, smooth ) {
			if ( i < 0 || i >= slides.length ) {
				return;
			}
			index = i;
			var slide = slides[ i ];
			scrollViewportToSlide( viewport, slide, !!smooth );
			syncDots();
		}

		if ( hasDots ) {
			for ( var j = 0; j < dots.length; j++ ) {
				dots[ j ].classList.toggle( 'is-active', j === 0 );
				dots[ j ].addEventListener( 'click', function ( ev ) {
					var btn = ev.currentTarget;
					var idx = parseInt( btn.getAttribute( 'data-index' ) || '0', 10 );
					if ( ! isNaN( idx ) ) {
						setActive( idx, true );
					}
				} );
			}
		}

		viewport.addEventListener( 'keydown', function ( ev ) {
			if ( ev.key === 'ArrowRight' ) {
				ev.preventDefault();
				setActive( index + 1 >= slides.length ? 0 : index + 1, true );
			} else if ( ev.key === 'ArrowLeft' ) {
				ev.preventDefault();
				setActive( index - 1 < 0 ? slides.length - 1 : index - 1, true );
			}
		} );

		if ( hasDots ) {
			viewport.addEventListener(
				'scroll',
				function () {
					var vpRect = viewport.getBoundingClientRect();
					var centerX = vpRect.left + vpRect.width / 2;
					var best = 0;
					var bestDist = Infinity;
					for ( var s = 0; s < slides.length; s++ ) {
						var r = slides[ s ].getBoundingClientRect();
						var mid = r.left + r.width / 2;
						var dist = Math.abs( mid - centerX );
						if ( dist < bestDist ) {
							bestDist = dist;
							best = s;
						}
					}
					if ( best !== index ) {
						index = best;
						syncDots();
					}
				},
				{ passive: true }
			);
		}

		var autoplayOn = root.getAttribute( 'data-autoplay' ) === '1';
		var intervalMs = parseInt( root.getAttribute( 'data-autoplay-interval-ms' ) || '5000', 10 );
		if ( isNaN( intervalMs ) || intervalMs < 2000 ) {
			intervalMs = 2000;
		}
		if ( intervalMs > 60000 ) {
			intervalMs = 60000;
		}

		var reducedMotion =
			window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var timer = null;

		function pauseAutoplay() {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function resumeAutoplay() {
			pauseAutoplay();
			if ( ! autoplayOn || reducedMotion || slides.length < 2 ) {
				return;
			}
			timer = setInterval( function () {
				setActive( index + 1 >= slides.length ? 0 : index + 1, true );
			}, intervalMs );
		}

		if ( autoplayOn && ! reducedMotion ) {
			root.addEventListener( 'mouseenter', pauseAutoplay );
			root.addEventListener( 'mouseleave', resumeAutoplay );
			root.addEventListener( 'focusin', pauseAutoplay );
			root.addEventListener( 'focusout', function ( ev ) {
				if ( ! root.contains( ev.relatedTarget ) ) {
					resumeAutoplay();
				}
			} );
			document.addEventListener( 'visibilitychange', function () {
				if ( document.hidden ) {
					pauseAutoplay();
				} else {
					resumeAutoplay();
				}
			} );
			resumeAutoplay();
		}
	}

	function boot() {
		var nodes = document.querySelectorAll( '[data-zskeleton-tis-carousel]' );
		for ( var i = 0; i < nodes.length; i++ ) {
			initCarousel( nodes[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
