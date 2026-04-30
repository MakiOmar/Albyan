/**
 * ZSkeleton slider — autoplay, touch swipe, fade / slide / zoom modes.
 */
(function () {
	'use strict';

	function qs(root, sel) {
		return root.querySelector(sel);
	}

	function qsa(root, sel) {
		return Array.prototype.slice.call(root.querySelectorAll(sel));
	}

	function parseBool(v) {
		return v === '1' || v === 1 || v === true;
	}

	function initSlider(root) {
		if (!root || root.dataset.zskeletonSliderInit === '1') {
			return;
		}
		root.dataset.zskeletonSliderInit = '1';

		var track = qs(root, '.zskeleton-slider__track');
		var slides = qsa(root, '.zskeleton-slider__slide');
		if (!track || slides.length === 0) {
			return;
		}

		var effect = root.getAttribute('data-effect') || 'fade';
		var autoplayMs = parseInt(root.getAttribute('data-autoplay'), 10) || 0;
		var showNav = parseBool(root.getAttribute('data-show-nav'));
		var showDots = parseBool(root.getAttribute('data-show-dots'));
		var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		var index = 0;
		var timer = null;
		var touchStartX = null;

		function setActive(i) {
			var n = slides.length;
			index = ((i % n) + n) % n;
			slides.forEach(function (el, j) {
				var on = j === index;
				el.classList.toggle('is-active', on);
				el.setAttribute('aria-hidden', on ? 'false' : 'true');
			});
			if (effect === 'slide') {
				var frac = 100 / slides.length;
				track.style.transform = 'translateX(' + -index * frac + '%)';
			}
			qsa(root, '.zskeleton-slider__dot').forEach(function (dot, j) {
				dot.classList.toggle('is-active', j === index);
				dot.setAttribute('aria-selected', j === index ? 'true' : 'false');
			});
		}

		function next() {
			setActive(index + 1);
		}

		function prev() {
			setActive(index - 1);
		}

		function startAutoplay() {
			stopAutoplay();
			if (reducedMotion || autoplayMs <= 0 || slides.length < 2) {
				return;
			}
			timer = window.setInterval(next, autoplayMs);
		}

		function stopAutoplay() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		if (effect === 'slide') {
			track.style.display = 'flex';
			track.style.width = slides.length * 100 + '%';
			slides.forEach(function (el) {
				el.style.flex = '0 0 ' + 100 / slides.length + '%';
			});
			setActive(0);
		} else {
			setActive(0);
		}

		if (showNav) {
			var prevBtn = qs(root, '.zskeleton-slider__nav--prev');
			var nextBtn = qs(root, '.zskeleton-slider__nav--next');
			if (prevBtn) {
				prevBtn.addEventListener('click', function () {
					prev();
					startAutoplay();
				});
			}
			if (nextBtn) {
				nextBtn.addEventListener('click', function () {
					next();
					startAutoplay();
				});
			}
		}

		if (showDots) {
			qsa(root, '.zskeleton-slider__dot').forEach(function (dot) {
				dot.addEventListener('click', function () {
					var go = parseInt(dot.getAttribute('data-go-to'), 10);
					if (!isNaN(go)) {
						setActive(go);
						startAutoplay();
					}
				});
			});
		}

		root.addEventListener('mouseenter', stopAutoplay);
		root.addEventListener('mouseleave', startAutoplay);
		root.addEventListener('focusin', stopAutoplay);
		root.addEventListener('focusout', startAutoplay);

		root.addEventListener(
			'touchstart',
			function (e) {
				if (!e.touches || !e.touches[0]) {
					return;
				}
				touchStartX = e.touches[0].clientX;
			},
			{ passive: true }
		);

		root.addEventListener(
			'touchend',
			function (e) {
				if (touchStartX === null || !e.changedTouches || !e.changedTouches[0]) {
					return;
				}
				var dx = e.changedTouches[0].clientX - touchStartX;
				touchStartX = null;
				if (Math.abs(dx) < 40) {
					return;
				}
				if (dx < 0) {
					next();
				} else {
					prev();
				}
				startAutoplay();
			},
			{ passive: true }
		);

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				stopAutoplay();
			} else {
				startAutoplay();
			}
		});

		startAutoplay();
	}

	function boot() {
		qsa(document, '.zskeleton-slider').forEach(initSlider);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
