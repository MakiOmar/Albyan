/**
 * Sync hidden form inputs and hook WordPress post save.
 *
 * @param {Function} syncFn Called before post submit.
 */
export function useWpSave( syncFn ) {
	const form = document.getElementById( 'post' );
	if ( form ) {
		form.addEventListener( 'submit', () => {
			syncFn();
		} );
	}
}
