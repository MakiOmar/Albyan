/**
 * International telephone input for Form Kit (public forms + admin preview).
 */
import intlTelInput from 'intl-tel-input/intlTelInputWithUtils';
import { ar } from 'intl-tel-input/locale';

const instances = new WeakMap();

/**
 * @return {Record<string, unknown>}
 */
function getConfig() {
	return window.zskeletonIntlTelConfig && typeof window.zskeletonIntlTelConfig === 'object'
		? window.zskeletonIntlTelConfig
		: {};
}

/**
 * @return {string}
 */
function getFallbackCountry() {
	const config = getConfig();
	if ( typeof config.fallbackCountry === 'string' && /^[a-z]{2}$/i.test( config.fallbackCountry ) ) {
		return config.fallbackCountry.toLowerCase();
	}
	return config.isArabic ? 'ae' : 'us';
}

/**
 * Normalize a country attribute to a valid iso2 code, or empty for auto-detect.
 *
 * @param {string} value Raw attribute / setting value.
 * @return {string}
 */
function normalizeCountryCode( value ) {
	const code = String( value || '' ).trim().toLowerCase();
	if ( ! code || code === 'auto' ) {
		return '';
	}
	return /^[a-z]{2}$/.test( code ) ? code : '';
}

/**
 * @return {Promise<string>}
 */
async function lookupVisitorCountry() {
	const config = getConfig();
	const fallback = getFallbackCountry();
	const endpoints = [];

	if ( typeof config.geoUrl === 'string' && config.geoUrl ) {
		endpoints.push( config.geoUrl );
	}
	endpoints.push( 'https://ipapi.co/json/', 'https://ipwho.is/' );

	for ( const geoUrl of endpoints ) {
		try {
			const response = await fetch( geoUrl, { credentials: 'omit' } );
			if ( ! response.ok ) {
				continue;
			}
			const data = await response.json();
			const code = normalizeCountryCode( data.country_code || data.countryCode || data.country );
			if ( code ) {
				return code;
			}
		} catch ( error ) {
			// Try next endpoint.
		}
	}

	return fallback;
}

/**
 * @param {HTMLInputElement} input Tel input.
 * @return {{ options: Record<string, unknown>, isRtl: boolean }}
 */
function buildOptions( input ) {
	const config = getConfig();
	const attrCountry = normalizeCountryCode( input.getAttribute( 'data-zs-intl-tel-country' ) || '' );
	const separateDial = input.getAttribute( 'data-zs-intl-tel-separate-dial' ) !== '0';
	const isRtl =
		input.getAttribute( 'data-zs-intl-tel-rtl' ) === '1' ||
		input.closest( '[dir="rtl"]' ) !== null ||
		document.documentElement.getAttribute( 'dir' ) === 'rtl' ||
		!! config.isRtl;
	const fallback = getFallbackCountry();
	const useAutoDetect = ! attrCountry && config.autoDetect !== false;

	// intl-tel-input v29+: initialCountry must be a valid iso2 code (never "auto").
	// nationalMode was removed — do not pass it.
	const options = {
		separateDialCode: separateDial,
		formatAsYouType: true,
		initialCountry: attrCountry || fallback,
	};

	if ( useAutoDetect ) {
		options.initialCountryLookup = lookupVisitorCountry;
	}

	if ( config.isArabic ) {
		options.uiTranslations = ar;
		options.countryNameLocale = 'ar';
	}

	return { options, isRtl };
}

/**
 * @param {HTMLInputElement} input Tel input.
 * @param {boolean} isRtl Whether to enable RTL layout on the widget container.
 */
function applyRtl( input, isRtl ) {
	if ( ! isRtl ) {
		return;
	}
	const wrap = input.closest( '.iti' );
	if ( wrap ) {
		wrap.setAttribute( 'dir', 'rtl' );
	}
}

/**
 * @param {ParentNode|null} container Root element.
 */
function init( container ) {
	const root = container || document;
	root.querySelectorAll( 'input[data-zs-intl-tel="1"]' ).forEach( ( input ) => {
		if ( instances.has( input ) ) {
			return;
		}
		const { options, isRtl } = buildOptions( input );
		const iti = intlTelInput( input, options );
		applyRtl( input, isRtl );
		instances.set( input, iti );
	} );
}

/**
 * @param {ParentNode|null} container Root element.
 */
function destroy( container ) {
	const root = container || document;
	root.querySelectorAll( 'input[data-zs-intl-tel="1"]' ).forEach( ( input ) => {
		const iti = instances.get( input );
		if ( iti ) {
			iti.destroy();
			instances.delete( input );
		}
	} );
}

/**
 * Write E.164 values back to inputs before native/AJAX submit.
 *
 * @param {HTMLFormElement} form Form element.
 */
function syncForm( form ) {
	if ( ! form ) {
		return;
	}
	form.querySelectorAll( 'input[data-zs-intl-tel="1"]' ).forEach( ( input ) => {
		const iti = instances.get( input );
		if ( ! iti ) {
			return;
		}
		const number = iti.getNumber();
		if ( number ) {
			input.value = number;
		}
	} );
}

window.zskeletonIntlTel = {
	init,
	destroy,
	syncForm,
	getInstance( input ) {
		return instances.get( input ) || null;
	},
};
