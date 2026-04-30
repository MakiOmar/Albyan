/**
 * Lightweight canvas particles for SEO Expert hero (respects reduced motion).
 * Backing store size is clamped to avoid "Canvas exceeds max size" DOMException.
 */
(function () {
	'use strict';

	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	var canvas = document.querySelector('.seo-expert-hero__particles');
	if (!canvas || !canvas.getContext) {
		return;
	}

	var hero = canvas.closest('.seo-expert-hero');
	if (!hero) {
		return;
	}

	var ctx = canvas.getContext('2d');
	var particles = [];
	var w = 0;
	var h = 0;
	var raf = 0;
	var resizeRaf = 0;
	var lastCssW = -1;
	var lastCssH = -1;
	var count = 52;

	/** Ignore absurd layout values from hero getBoundingClientRect(). */
	var MAX_CSS_EDGE = 8192;
	/** Per-edge backing limit (some mobile engines are stricter than 4096). */
	var MAX_BACKING_EDGE = 2048;
	/** Total pixel budget (area limits are separate from per-edge in some browsers). */
	var MAX_BACKING_PIXELS = 8 * 1024 * 1024;

	function rand(min, max) {
		return min + Math.random() * (max - min);
	}

	function safeCssDim(v) {
		if (!isFinite(v) || v <= 0) {
			return 1;
		}
		return Math.min(MAX_CSS_EDGE, Math.max(1, Math.floor(v)));
	}

	/**
	 * Compute backing dimensions so canvas allocation stays within browser limits.
	 *
	 * @return {{ bw: number, bh: number }}
	 */
	function getBackingDimensions(cssW, cssH) {
		var rawDpr = window.devicePixelRatio || 1;
		var dpr = Math.min(Math.max(rawDpr, 1), 2);
		var bw = Math.floor(cssW * dpr);
		var bh = Math.floor(cssH * dpr);
		var maxDim = Math.max(bw, bh, 1);
		var scale = MAX_BACKING_EDGE / maxDim;
		if (scale < 1) {
			bw = Math.max(1, Math.floor(bw * scale));
			bh = Math.max(1, Math.floor(bh * scale));
		}
		var area = bw * bh;
		if (area > MAX_BACKING_PIXELS) {
			var areaScale = Math.sqrt(MAX_BACKING_PIXELS / area);
			bw = Math.max(1, Math.floor(bw * areaScale));
			bh = Math.max(1, Math.floor(bh * areaScale));
		}
		return { bw: bw, bh: bh };
	}

	function initParticles() {
		particles.length = 0;
		var i;
		for (i = 0; i < count; i++) {
			particles.push({
				x: rand(0, w),
				y: rand(0, h),
				r: rand(0.6, 2.2),
				o: rand(0.12, 0.45),
				vx: rand(-0.18, 0.18),
				vy: rand(-0.12, 0.12),
			});
		}
	}

	function applyCanvasTransform(bw, bh) {
		if (typeof ctx.resetTransform === 'function') {
			ctx.resetTransform();
		} else {
			ctx.setTransform(1, 0, 0, 1, 0, 0);
		}
		ctx.setTransform(bw / w, 0, 0, bh / h, 0, 0);
	}

	/**
	 * Drop inline dimensions (older script versions set these and caused runaway height).
	 */
	function clearCanvasInlineBox() {
		canvas.style.removeProperty('width');
		canvas.style.removeProperty('height');
		if (canvas.getAttribute && canvas.getAttribute('style') === '') {
			canvas.removeAttribute('style');
		}
	}

	/**
	 * Use the hero section's border box — abs-positioned canvas must not drive sizing via clientHeight.
	 */
	function resize() {
		clearCanvasInlineBox();

		var rect = hero.getBoundingClientRect();
		var cw = rect.width;
		var ch = rect.height;
		if (cw < 1 || ch < 1) {
			return;
		}
		w = safeCssDim(cw);
		h = safeCssDim(ch);
		if (w === lastCssW && h === lastCssH) {
			return;
		}
		lastCssW = w;
		lastCssH = h;

		var dims = getBackingDimensions(w, h);
		var bw = dims.bw;
		var bh = dims.bh;

		canvas.width = bw;
		canvas.height = bh;

		try {
			applyCanvasTransform(bw, bh);
		} catch (err) {
			canvas.width = 1;
			canvas.height = 1;
			if (typeof ctx.resetTransform === 'function') {
				ctx.resetTransform();
			} else {
				ctx.setTransform(1, 0, 0, 1, 0, 0);
			}
			throw err;
		}
		initParticles();
	}

	function scheduleResize() {
		if (resizeRaf) {
			window.cancelAnimationFrame(resizeRaf);
		}
		resizeRaf = window.requestAnimationFrame(function () {
			resizeRaf = 0;
			try {
				resize();
			} catch (err) {
				if (window.console && window.console.warn) {
					window.console.warn('seo-expert-hero-particles resize:', err);
				}
			}
		});
	}

	function step() {
		if (w < 1 || h < 1) {
			raf = window.requestAnimationFrame(step);
			return;
		}
		var i, p;
		ctx.clearRect(0, 0, w, h);
		for (i = 0; i < particles.length; i++) {
			p = particles[i];
			p.x += p.vx;
			p.y += p.vy;
			if (p.x < 0) {
				p.x = w;
			}
			if (p.x > w) {
				p.x = 0;
			}
			if (p.y < 0) {
				p.y = h;
			}
			if (p.y > h) {
				p.y = 0;
			}
			ctx.beginPath();
			ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
			ctx.fillStyle = 'rgba(255, 255, 255, ' + p.o + ')';
			ctx.fill();
		}
		raf = window.requestAnimationFrame(step);
	}

	function start() {
		try {
			clearCanvasInlineBox();
			scheduleResize();
			if (raf) {
				window.cancelAnimationFrame(raf);
			}
			raf = window.requestAnimationFrame(step);
		} catch (err) {
			if (window.console && window.console.warn) {
				window.console.warn('seo-expert-hero-particles:', err);
			}
		}
	}

	function onResize() {
		scheduleResize();
	}

	window.addEventListener('resize', onResize, { passive: true });

	if (window.ResizeObserver) {
		try {
			/* Hero border box changes; canvas bitmap attrs must not affect this. */
			var ro = new ResizeObserver(onResize);
			ro.observe(hero);
		} catch (e) {}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
