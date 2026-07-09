/**
 * Slider slides meta: add/remove rows + Media Library for background image.
 */
(function ($) {
	'use strict';

	function bindImageField($card, setBtnSel, clearBtnSel, hiddenSel, previewSel, titleText) {
		$card.find(setBtnSel).off('click').on('click', function (e) {
			e.preventDefault();
			var $c = $(this).closest('.zskeleton-slider-slide-card');
			var frame = wp.media({
				title: titleText || (window.zskeletonSliderAdmin && zskeletonSliderAdmin.chooseImage) || 'Choose image',
				multiple: false,
				library: { type: 'image' }
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				$c.find(hiddenSel).val(att.id ? String(att.id) : '');
				var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
				$c.find(previewSel).html(
					url ? '<img src="' + url + '" alt="" style="max-width:160px;height:auto;border-radius:4px;border:1px solid #dcdcde;" />' : ''
				);
			});
			frame.open();
		});

		$card.find(clearBtnSel).off('click').on('click', function (e) {
			e.preventDefault();
			var $c = $(this).closest('.zskeleton-slider-slide-card');
			$c.find(hiddenSel).val('');
			$c.find(previewSel).empty();
		});
	}

	function bindCard($card) {
		bindImageField(
			$card,
			'.zskeleton-slider-set-image',
			'.zskeleton-slider-clear-image',
			'input.zskeleton-slide-image-id',
			'.zskeleton-slider-image-preview',
			window.zskeletonSliderAdmin && zskeletonSliderAdmin.chooseImage ? zskeletonSliderAdmin.chooseImage : ''
		);
		bindImageField(
			$card,
			'.zskeleton-slider-set-content-image',
			'.zskeleton-slider-clear-content-image',
			'input.zskeleton-slide-content-image-id',
			'.zskeleton-slider-content-image-preview',
			window.zskeletonSliderAdmin && zskeletonSliderAdmin.chooseContentImage ? zskeletonSliderAdmin.chooseContentImage : ''
		);
	}

	function nextIndex($list) {
		var max = -1;
		$list.find('.zskeleton-slider-slide-card').each(function () {
			var i = parseInt($(this).attr('data-slide-index'), 10);
			if (!isNaN(i)) {
				max = Math.max(max, i);
			}
		});
		return max + 1;
	}

	function appendFromTemplate($list, $tpl, idx) {
		var html = $tpl.html().replace(/__IDX__/g, String(idx));
		var $node = $(html.trim());
		$list.append($node);
		bindCard($node);
	}

	$(function () {
		var $list = $('#zskeleton-slider-slides-list');
		var $tpl = $('#zskeleton-slider-slide-template');
		if (!$list.length || !$tpl.length) {
			return;
		}

		$list.find('.zskeleton-slider-slide-card').each(function () {
			bindCard($(this));
		});

		$('#zskeleton-slider-add-slide').on('click', function (e) {
			e.preventDefault();
			appendFromTemplate($list, $tpl, nextIndex($list));
		});

		$list.on('click', '.zskeleton-slider-remove-slide', function (e) {
			e.preventDefault();
			$(this).closest('.zskeleton-slider-slide-card').remove();
			if (!$list.find('.zskeleton-slider-slide-card').length) {
				appendFromTemplate($list, $tpl, 0);
			}
		});
	});

	// Slider display: WordPress color pickers (title, description, accent, overlay).
	$(function () {
		if (!$.fn.wpColorPicker) {
			return;
		}
		$('.zskeleton-slider-color-field').each(function () {
			var $el = $(this);
			if ($el.data('zsWpColorInit')) {
				return;
			}
			$el.data('zsWpColorInit', true);
			// Keep the named input in sync with Iris; debounce close so the panel collapses after the user finishes (avoids empty POST when the picker stayed open).
			$el.wpColorPicker({
				width: 255,
				change: function (event, ui) {
					var $t = $(event.target);
					if (ui && ui.color && typeof ui.color.toString === 'function') {
						$t.val(ui.color.toString());
					}
					var prev = $t.data('zsPickerCloseTimer');
					if (prev) {
						clearTimeout(prev);
					}
					$t.data(
						'zsPickerCloseTimer',
						setTimeout(function () {
							$t.removeData('zsPickerCloseTimer');
							try {
								$t.wpColorPicker('close');
							} catch (ignoreClose) {
								// Picker may already be torn down.
							}
						}, 350)
					);
				}
			});
		});
		// Before POST: close picker and, if still empty, read current widget color into the input.
		var $postForm = $('#post');
		if ($postForm.length) {
			$postForm.on('submit.zskeletonSliderColorFlush', function () {
				function looksLikeHex(v) {
					return /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test((v || '').trim());
				}
				function rgbCssToHex(css) {
					if (!css || css === 'transparent') {
						return '';
					}
					css = String(css).trim();
					if (css.charAt(0) === '#') {
						return looksLikeHex(css) ? css : '';
					}
					var m = css.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i);
					if (!m) {
						return '';
					}
					function h(n) {
						var x = Math.max(0, Math.min(255, parseInt(n, 10)));
						var s = x.toString(16);
						return s.length === 1 ? '0' + s : s;
					}
					return '#' + h(m[1]) + h(m[2]) + h(m[3]);
				}
				// Each wpColorPicker wraps this input + a .wp-color-result swatch; that swatch matches what the user sees.
				// Iris / wpColorPicker('color') can leak another field's value when several pickers exist on one admin screen.
				function hexFromPickerPreview($inp) {
					var $wrap = $inp.closest('.wp-picker-container');
					if (!$wrap.length) {
						return '';
					}
					var $sw = $wrap.find('.wp-color-result').first();
					if (!$sw.length) {
						return '';
					}
					var style = $sw.attr('style') || '';
					var hash = style.match(/#[0-9a-fA-F]{6}\b/i) || style.match(/#[0-9a-fA-F]{3}\b/i);
					if (hash && looksLikeHex(hash[0])) {
						return hash[0];
					}
					return rgbCssToHex($sw.css('background-color'));
				}
				$('.zskeleton-slider-color-field').each(function () {
					var $inp = $(this);
					if (!$inp.data('zsWpColorInit')) {
						return;
					}
					var sync = hexFromPickerPreview($inp);
					if (!sync || !looksLikeHex(sync)) {
						try {
							if ($inp.data('iris') && typeof $inp.iris === 'function') {
								var col = $inp.iris('color');
								if (col && typeof col.toString === 'function') {
									sync = col.toString();
								}
							}
						} catch (ignoreIris) {
							// Iris may be absent on edge builds.
						}
					}
					if (sync && looksLikeHex(sync)) {
						$inp.val(sync);
					} else {
						var raw = ($inp.val() || '').trim();
						if (!looksLikeHex(raw)) {
							try {
								var c = $inp.wpColorPicker('color');
								var s = typeof c === 'string' ? c : (c && typeof c.toString === 'function' ? c.toString() : '');
								if (s && looksLikeHex(s)) {
									$inp.val(s);
								}
							} catch (ignore2) {
								// Getter not supported in this WP build.
							}
						}
					}
					try {
						$inp.wpColorPicker('close');
					} catch (ignore) {
						// Picker may be absent in edge admin contexts.
					}
				});
			});
		}
	});
})(jQuery);
