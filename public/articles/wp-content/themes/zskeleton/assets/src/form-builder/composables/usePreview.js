/**
 * Fetch form preview HTML from WordPress admin-ajax.
 *
 * @param {object} opts Options.
 * @return {Promise<string>}
 */
export async function fetchPreview( opts ) {
	const body = new URLSearchParams();
	body.append( 'action', 'zskeleton_form_builder_preview' );
	body.append( 'nonce', opts.nonce );
	body.append( 'post_id', String( opts.postId || 0 ) );
	body.append( 'layout_tree', JSON.stringify( opts.layoutTree || [] ) );

	const res = await fetch( opts.ajaxUrl, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: body.toString(),
		credentials: 'same-origin',
	} );

	const data = await res.json();
	if ( data.success && data.data?.html ) {
		return data.data.html;
	}
	const msg =
		data.data?.message ||
		opts.fallbackMessage ||
		'Preview failed.';
	throw new Error( msg );
}
