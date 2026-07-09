/**
 * Copy text to clipboard with fallback for older browsers.
 *
 * @param {string} text Text to copy.
 * @return {Promise<boolean>}
 */
export async function copyToClipboard( text ) {
	if ( ! text ) {
		return false;
	}
	try {
		if ( navigator.clipboard?.writeText ) {
			await navigator.clipboard.writeText( text );
			return true;
		}
	} catch ( e ) {
		// Fall through.
	}
	const ta = document.createElement( 'textarea' );
	ta.value = text;
	ta.setAttribute( 'readonly', '' );
	ta.style.position = 'absolute';
	ta.style.left = '-9999px';
	document.body.appendChild( ta );
	ta.select();
	let ok = false;
	try {
		ok = document.execCommand( 'copy' );
	} catch ( e ) {
		ok = false;
	}
	ta.remove();
	return ok;
}
