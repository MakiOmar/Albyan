/**
 * ZSkeleton Form Kit: AJAX submit, wizard steps, optional step validation, media picker.
 */
(function () {
	'use strict';

	var cfg = typeof zskeletonFormKit !== 'undefined' ? zskeletonFormKit : {};
	var ajaxUrl = cfg.ajaxUrl || '';

	function parseJsonSafe(text) {
		try {
			return JSON.parse(text);
		} catch (e) {
			return null;
		}
	}

	function showNotice(form, msg, isError) {
		var el = form.querySelector('.zs-form__notices');
		if (!el) {
			return;
		}
		el.hidden = false;
		el.textContent = msg;
		el.classList.toggle('is-error', !!isError);
		el.classList.toggle('is-success', !isError);
		// Below submit: scroll into view so users see success/errors on long forms.
		window.requestAnimationFrame(function () {
			var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			try {
				el.scrollIntoView({
					behavior: reduce ? 'auto' : 'smooth',
					block: 'center',
					inline: 'nearest'
				});
			} catch (err) {
				el.scrollIntoView();
			}
			try {
				el.focus({ preventScroll: true });
			} catch (err2) {
				/* IE / older */
			}
		});
	}

	function clearFieldErrors(form) {
		form.querySelectorAll('.zs-field__error').forEach(function (e) {
			e.hidden = true;
			e.textContent = '';
		});
		form.querySelectorAll('.zs-field__control, .zs-field__fieldset').forEach(function (c) {
			c.classList.remove('is-invalid');
		});
	}

	function applyFieldErrors(form, errors) {
		if (!errors || typeof errors !== 'object') {
			return;
		}
		Object.keys(errors).forEach(function (name) {
			var wrap = form.querySelector('[data-zs-field="' + name + '"]');
			if (!wrap) {
				return;
			}
			var err = wrap.querySelector('.zs-field__error');
			var input = wrap.querySelector('.zs-field__control, .zs-field__fieldset');
			if (err) {
				err.textContent = errors[name];
				err.hidden = false;
			}
			if (input) {
				input.classList.add('is-invalid');
			}
		});
	}

	function postAjax(form, extraFields) {
		var fd = new FormData(form);
		if (extraFields) {
			Object.keys(extraFields).forEach(function (k) {
				fd.set(k, extraFields[k]);
			});
		}
		return fetch(ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: fd
		}).then(function (r) {
			return r.text().then(function (text) {
				var json = parseJsonSafe(text);
				return { ok: r.ok, status: r.status, json: json, raw: text };
			});
		});
	}

	function setActiveStep(form, index) {
		var steps = form.querySelectorAll('.zs-form__step');
		var progress = form.querySelectorAll('.zs-form__progress-item');
		steps.forEach(function (s, i) {
			var on = i === index;
			s.classList.toggle('is-active', on);
			s.hidden = !on;
		});
		progress.forEach(function (p, i) {
			p.classList.toggle('is-active', i === index);
			p.classList.toggle('is-done', i < index);
		});
		var btnBack = form.querySelector('[data-zs-back]');
		var btnNext = form.querySelector('[data-zs-next]');
		var btnSubmit = form.querySelector('[data-zs-submit]');
		if (btnBack) {
			btnBack.hidden = index === 0;
		}
		var last = index === steps.length - 1;
		if (btnNext) {
			btnNext.hidden = last;
		}
		if (btnSubmit) {
			btnSubmit.hidden = !last;
		}
	}

	function validateStepClient(form) {
		var step = form.querySelector('.zs-form__step.is-active');
		if (!step) {
			return true;
		}
		var valid = true;
		step.querySelectorAll('[required]').forEach(function (el) {
			if (el.type === 'checkbox' || el.type === 'radio') {
				if (el.type === 'radio') {
					var nm = el.name;
					var group = step.querySelectorAll('input[type="radio"][name="' + nm.replace(/"/g, '\\"') + '"]');
					var any = false;
					group.forEach(function (r) {
						if (r.checked) {
							any = true;
						}
					});
					if (!any) {
						valid = false;
					}
				} else if (!el.checked) {
					valid = false;
				}
			} else if (!el.value || !String(el.value).trim()) {
				valid = false;
			}
		});
		return valid;
	}

	function validateStepServer(form, stepIndex) {
		return postAjax(form, {
			action: 'zskeleton_form_validate_step',
			zs_step_index: String(stepIndex)
		}).then(function (res) {
			var j = res.json;
			if (!j || !j.success) {
				return { ok: false };
			}
			return { ok: true, data: j.data || {} };
		}).catch(function () {
			return { ok: false };
		});
	}

	function bindWizardFixed(form) {
		if (form.getAttribute('data-zs-form-wizard') !== '1') {
			return;
		}
		var steps = form.querySelectorAll('.zs-form__step');
		if (steps.length < 2) {
			return;
		}
		var idx = 0;
		setActiveStep(form, idx);

		var nextBtn = form.querySelector('[data-zs-next]');
		if (nextBtn) {
			nextBtn.addEventListener('click', function () {
				if (!validateStepClient(form)) {
					showNotice(form, (cfg.i18n && cfg.i18n.pleaseFillRequired) || (cfg.i18n && cfg.i18n.invalid) || '', true);
					return;
				}
				clearFieldErrors(form);
				var useAjax = form.getAttribute('data-zs-form-ajax') === '1';
				if (useAjax) {
					validateStepServer(form, idx).then(function (r) {
						if (!r.ok) {
							showNotice(form, (cfg.i18n && cfg.i18n.genericError) || (cfg.i18n && cfg.i18n.errorShort) || '', true);
							return;
						}
						if (r.data && r.data.valid === false && r.data.errors) {
							applyFieldErrors(form, r.data.errors);
							showNotice(form, (cfg.i18n && cfg.i18n.invalid) || (cfg.i18n && cfg.i18n.invalidShort) || '', true);
							return;
						}
						idx += 1;
						setActiveStep(form, idx);
						var n = form.querySelector('.zs-form__notices');
						if (n) {
							n.hidden = true;
						}
					});
				} else {
					idx += 1;
					setActiveStep(form, idx);
				}
			});
		}

		var backBtn = form.querySelector('[data-zs-back]');
		if (backBtn) {
			backBtn.addEventListener('click', function () {
				if (idx > 0) {
					idx -= 1;
					setActiveStep(form, idx);
				}
			});
		}
	}

	function bindAjaxSubmit(form) {
		if (form.getAttribute('data-zs-form-ajax') !== '1') {
			return;
		}
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			clearFieldErrors(form);
			var fd = new FormData(form);
			fd.set('action', 'zskeleton_form_submit');
			fetch(ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: fd
			})
				.then(function (r) {
					return r.text().then(function (text) {
						return { ok: r.ok, json: parseJsonSafe(text) };
					});
				})
				.then(function (res) {
					var j = res.json;
					if (!j) {
						showNotice(form, (cfg.i18n && cfg.i18n.genericError) || (cfg.i18n && cfg.i18n.errorShort) || '', true);
						return;
					}
					if (!j.success) {
						showNotice(form, (j.data && j.data.message) || (cfg.i18n && cfg.i18n.genericError) || (cfg.i18n && cfg.i18n.errorShort) || '', true);
						return;
					}
					var data = j.data || {};
					if (data.saved === false && data.errors) {
						applyFieldErrors(form, data.errors);
						showNotice(form, (cfg.i18n && cfg.i18n.invalid) || (cfg.i18n && cfg.i18n.invalidShort) || '', true);
						return;
					}
					if (data.saved) {
						showNotice(form, data.message || (cfg.i18n && cfg.i18n.successOk) || '', false);
						form.reset();
						var wiz = form.getAttribute('data-zs-form-wizard') === '1';
						if (wiz) {
							idxReset(form);
						}
					}
				})
				.catch(function () {
					showNotice(form, (cfg.i18n && cfg.i18n.genericError) || (cfg.i18n && cfg.i18n.errorShort) || '', true);
				});
		});
	}

	function idxReset(form) {
		var steps = form.querySelectorAll('.zs-form__step');
		if (steps.length) {
			var idx = 0;
			steps.forEach(function (s, i) {
				s.classList.toggle('is-active', i === 0);
				s.hidden = i !== 0;
			});
			var progress = form.querySelectorAll('.zs-form__progress-item');
			progress.forEach(function (p, i) {
				p.classList.toggle('is-active', i === 0);
				p.classList.toggle('is-done', false);
			});
			var btnBack = form.querySelector('[data-zs-back]');
			var btnNext = form.querySelector('[data-zs-next]');
			var btnSubmit = form.querySelector('[data-zs-submit]');
			if (btnBack) {
				btnBack.hidden = true;
			}
			if (btnNext) {
				btnNext.hidden = false;
			}
			if (btnSubmit) {
				btnSubmit.hidden = true;
			}
		}
	}

	function bindRangeOutput(form) {
		form.querySelectorAll('input[type="range"].zs-field__control').forEach(function (input) {
			var span = form.querySelector('[data-zs-range-for="' + input.id + '"]');
			if (!span) {
				return;
			}
			function sync() {
				span.textContent = input.value;
			}
			input.addEventListener('input', sync);
			sync();
		});
	}

	function bindMediaFields(form) {
		if (typeof wp === 'undefined' || !wp.media) {
			return;
		}
		form.querySelectorAll('[data-zs-media-field]').forEach(function (wrap) {
			var input = wrap.querySelector('.zs-field__media-input');
			var prev = wrap.querySelector('.zs-field__media-preview');
			var openBtn = wrap.querySelector('[data-zs-media-open]');
			var clearBtn = wrap.querySelector('[data-zs-media-clear]');
			if (!input || !openBtn) {
				return;
			}
			openBtn.addEventListener('click', function () {
				var frame = wp.media({
					title: (cfg.i18n && cfg.i18n.mediaTitle) || '',
					button: { text: (cfg.i18n && cfg.i18n.mediaButton) || '' },
					multiple: false
				});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					input.value = String(att.id);
					if (prev && att.url) {
						if (att.type === 'image') {
							prev.innerHTML = '<img src="' + att.url + '" alt="" />';
						} else {
							prev.textContent = att.filename || '';
						}
					}
				});
				frame.open();
			});
			if (clearBtn) {
				clearBtn.addEventListener('click', function () {
					input.value = '';
					if (prev) {
						prev.innerHTML = '';
					}
				});
			}
		});
	}

	function initForm(form) {
		bindRangeOutput(form);
		bindMediaFields(form);
		bindWizardFixed(form);
		bindAjaxSubmit(form);
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.zs-form').forEach(initForm);
	});
})();
