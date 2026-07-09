/**
 * International telephone input for Form Kit (public forms + admin preview).
 */
import intlTelInput from 'intl-tel-input/intlTelInputWithUtils';

const instances = new WeakMap();

/**
 * @param {HTMLInputElement} input Tel input.
 * @return {object}
 */
function buildOptions( input ) {
	const country = input.getAttribute( 'data-zs-intl-tel-country' ) || 'auto';
	const separateDial = input.getAttribute( 'data-zs-intl-tel-separate-dial' ) !== '0';

	return {
		initialCountry: '' === country ? 'auto' : country,
		separateDialCode: separateDial,
		nationalMode: ! separateDial,
		formatAsYouType: true,
	};
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
		const iti = intlTelInput( input, buildOptions( input ) );
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
