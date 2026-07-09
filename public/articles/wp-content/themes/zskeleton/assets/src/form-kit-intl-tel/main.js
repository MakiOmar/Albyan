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
 * @return {Promise<string>}
 */
async function lookupVisitorCountry() {
	const config = getConfig();
	const geoUrl = typeof config.geoUrl === 'string' && config.geoUrl ? config.geoUrl : 'https://ipapi.co/json/';

	try {
		const response = await fetch( geoUrl, { credentials: 'omit' } );
		if ( ! response.ok ) {
			throw new Error( 'geo lookup failed' );
		}
		const data = await response.json();
		const code = String( data.country_code || data.country || '' ).toLowerCase();
		if ( code ) {
			return code;
		}
	} catch ( error ) {
		// Fall back to locale-based default below.
	}

	return typeof config.fallbackCountry === 'string' && config.fallbackCountry
		? config.fallbackCountry
		: 'us';
}

/**
 * @param {HTMLInputElement} input Tel input.
 * @return {object}
 */
function buildOptions( input ) {
	const config = getConfig();
	const attrCountry = input.getAttribute( 'data-zs-intl-tel-country' ) || '';
	const separateDial = input.getAttribute( 'data-zs-intl-tel-separate-dial' ) !== '0';
	const isRtl =
		input.getAttribute( 'data-zs-intl-tel-rtl' ) === '1' ||
		input.closest( '[dir="rtl"]' ) !== null ||
		document.documentElement.getAttribute( 'dir' ) === 'rtl' ||
		!! config.isRtl;

	const options = {
		separateDialCode: separateDial,
		nationalMode: ! separateDial,
		formatAsYouType: true,
	};

	if ( attrCountry && 'auto' !== attrCountry ) {
		options.initialCountry = attrCountry;
	} else if ( config.autoDetect !== false ) {
		options.initialCountryLookup = lookupVisitorCountry;
	} else if ( typeof config.fallbackCountry === 'string' && config.fallbackCountry ) {
		options.initialCountry = config.fallbackCountry;
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
