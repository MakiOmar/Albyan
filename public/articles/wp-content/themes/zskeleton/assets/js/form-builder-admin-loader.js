/**
 * ZSkeleton Form Builder loader — loads the Vite bundle outside WP script concat.
 */
(function (w) {
	'use strict';

	function showError(message) {
		var el = w.document.getElementById('zs-form-kit-app');
		if (el) {
			el.innerHTML =
				'<p class="notice notice-error"><strong>Form builder failed to load.</strong> ' +
				message +
				'</p>';
		}
	}

	function loadBundle() {
		if (w.zsFormKitMounted) {
			return;
		}

		var url = w.zsFormKitBuilderUrl;
		if (!url) {
			showError('Builder script URL is missing.');
			return;
		}

		if (w.zsFormKitBundleLoading) {
			return;
		}

		if (w.document.querySelector('script[data-zs-form-kit-bundle="1"]')) {
			return;
		}

		w.zsFormKitBundleLoading = true;

		var script = w.document.createElement('script');
		script.src = url;
		script.async = false;
		script.setAttribute('data-zs-form-kit-bundle', '1');

		script.onerror = function () {
			w.zsFormKitBundleLoading = false;
			showError('Could not load form-builder-admin.js. Check the file exists on the server.');
		};

		script.onload = function () {
			w.zsFormKitBundleLoading = false;
		};

		(w.document.head || w.document.documentElement).appendChild(script);
	}

	function schedule() {
		loadBundle();
	}

	if (w.document.readyState === 'loading') {
		w.document.addEventListener('DOMContentLoaded', schedule);
	} else {
		schedule();
	}

	w.addEventListener('load', schedule);
	w.zsFormKitLoadBundle = loadBundle;
})(window);
