import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import './styles/tailwind.css';
import './styles/form-builder.css';
import './styles/form-builder-portal.css';

let mountAttempts = 0;
let mounted = false;

function readBootstrap() {
	if ( window.zsFormKitBootstrap && typeof window.zsFormKitBootstrap === 'object' ) {
		return window.zsFormKitBootstrap;
	}
	const el = document.getElementById( 'zs-form-kit-bootstrap' );
	if ( ! el ) {
		return null;
	}
	try {
		return JSON.parse( el.textContent || '{}' );
	} catch ( e ) {
		return null;
	}
}

function showMountError( mountEl, message ) {
	if ( ! mountEl ) {
		return;
	}
	mountEl.innerHTML =
		'<p class="notice notice-error"><strong>Form builder failed to load.</strong> ' +
		message +
		'</p>';
}

function mount() {
	if ( mounted ) {
		return;
	}

	mountAttempts += 1;
	const mountEl = document.getElementById( 'zs-form-kit-app' );

	if ( ! mountEl ) {
		if ( mountAttempts < 30 ) {
			setTimeout( mount, 150 );
			return;
		}
		return;
	}

	const bootstrap = readBootstrap();

	if ( ! bootstrap || typeof bootstrap !== 'object' ) {
		showMountError(
			mountEl,
			'Bootstrap data is missing. Try reloading the page.'
		);
		return;
	}

	try {
		const pinia = createPinia();
		const app = createApp( App, { bootstrap } );
		app.use( pinia );
		app.config.errorHandler = ( err ) => {
			showMountError(
				mountEl,
				err && err.message ? err.message : 'Vue render error'
			);
		};
		app.mount( mountEl );
		mounted = true;
		window.zsFormKitMounted = true;
		mountEl.dataset.zsFormKitMounted = '1';
	} catch ( err ) {
		showMountError( mountEl, err && err.message ? err.message : 'Unknown error' );
	}
}

function scheduleMount() {
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', mount );
	} else {
		mount();
	}
	window.addEventListener( 'load', mount );
}

scheduleMount();
