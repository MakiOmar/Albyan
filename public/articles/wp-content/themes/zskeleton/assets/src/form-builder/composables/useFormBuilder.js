let fieldCounter = 0;

/**
 * Repair labels corrupted by broken JSON attribute encoding (u0627...).
 *
 * @param {string} label Label text.
 * @return {string}
 */
export function repairUnicodeLabel( label ) {
	if ( ! label || typeof label !== 'string' ) {
		return label || '';
	}
	if ( ! /^u[0-9a-f]{4}/i.test( label ) ) {
		return label;
	}
	return label.replace( /u([0-9a-f]{4})/gi, ( match, code ) =>
		String.fromCharCode( parseInt( code, 16 ) )
	);
}

/**
 * @param {string} text Raw name text.
 * @return {string}
 */
export function slugifyName( text ) {
	const s = String( text || '' )
		.toLowerCase()
		.replace( /[^a-z0-9_]+/g, '_' )
		.replace( /^_+|_+$/g, '' )
		.replace( /_+/g, '_' );
	if ( ! s ) {
		return uniqueFieldName( 'field' );
	}
	return s;
}

/**
 * @param {string} base Base name.
 * @return {string}
 */
export function uniqueFieldName( base ) {
	fieldCounter += 1;
	return slugifyName( base ) + '_' + fieldCounter;
}

/**
 * @param {string} type Field type.
 * @return {object}
 */
export function defaultField( type ) {
	return {
		name: uniqueFieldName( type ),
		type,
		label: type.charAt( 0 ).toUpperCase() + type.slice( 1 ),
		required: type === 'email',
	};
}

/**
 * @param {object} field Field config.
 * @return {object}
 */
export function normalizeField( field ) {
	const normalized = { ...field };
	normalized.label = repairUnicodeLabel( normalized.label || normalized.name || '' );
	if ( normalized.name ) {
		normalized.name = slugifyName( normalized.name );
	}
	if ( ! normalized.placeholder ) {
		delete normalized.placeholder;
	}
	if ( ! normalized.description ) {
		delete normalized.description;
	}
	if ( ! normalized.required ) {
		delete normalized.required;
	}
	if ( normalized.rules && typeof normalized.rules === 'object' ) {
		const rules = { ...normalized.rules };
		if ( ! rules.pattern ) {
			delete rules.pattern;
		}
		if ( ! rules.pattern_message ) {
			delete rules.pattern_message;
		}
		if ( Object.keys( rules ).length === 0 ) {
			delete normalized.rules;
		} else {
			normalized.rules = rules;
		}
	}
	if ( ! normalized.intl_tel ) {
		delete normalized.intl_tel;
		delete normalized.initial_country;
	} else if ( ! normalized.initial_country ) {
		delete normalized.initial_country;
	}
	return normalized;
}

/**
 * @param {Array} tree Layout tree.
 * @return {Array}
 */
export function repairLayoutTree( tree ) {
	if ( ! Array.isArray( tree ) ) {
		return [];
	}
	return tree.map( ( node ) => {
		if ( node.type === 'row' ) {
			const children = Array.isArray( node.children ) ? node.children : [];
			return {
				...node,
				children: children.map( ( col ) => ( {
					type: 'column',
					fields: ( col.fields || [] ).map( ( f ) => normalizeField( f ) ),
				} ) ),
			};
		}
		if ( node.type === 'field' && node.field ) {
			return { type: 'field', field: normalizeField( node.field ) };
		}
		return node;
	} );
}

/**
 * @param {number} columns Column count.
 * @return {object}
 */
export function createRow( columns ) {
	const cols = Math.max( 2, Math.min( 4, columns ) );
	const children = [];
	for ( let i = 0; i < cols; i += 1 ) {
		children.push( { type: 'column', fields: [] } );
	}
	return {
		type: 'row',
		id: 'row_' + Date.now() + '_' + Math.random().toString( 36 ).slice( 2, 7 ),
		columns: cols,
		mobile_stack: true,
		children,
	};
}

/**
 * @param {Array} tree Layout tree.
 * @param {object|null} path Selection path.
 * @return {object|null}
 */
export function getFieldAtPath( tree, path ) {
	if ( ! path ) {
		return null;
	}
	if ( path.kind === 'root' ) {
		const node = tree[ path.index ];
		return node && node.type === 'field' ? node.field : null;
	}
	if ( path.kind === 'column' ) {
		const row = tree[ path.rowIndex ];
		if ( ! row || row.type !== 'row' ) {
			return null;
		}
		const col = row.children?.[ path.colIndex ];
		return col?.fields?.[ path.fieldIndex ] || null;
	}
	return null;
}

/**
 * @param {Array} tree Layout tree.
 * @param {object|null} path Selection path.
 * @param {object} field Updated field.
 * @return {Array}
 */
export function setFieldAtPath( tree, path, field ) {
	const next = JSON.parse( JSON.stringify( tree ) );
	if ( ! path ) {
		return next;
	}
	if ( path.kind === 'root' ) {
		if ( next[ path.index ]?.type === 'field' ) {
			next[ path.index ].field = normalizeField( field );
		}
	} else if ( path.kind === 'column' ) {
		const row = next[ path.rowIndex ];
		if ( row?.type === 'row' && row.children?.[ path.colIndex ] ) {
			row.children[ path.colIndex ].fields[ path.fieldIndex ] = normalizeField( field );
		}
	}
	return next;
}

/**
 * Reset counter from existing tree (for unique names after load).
 *
 * @param {Array} tree Layout tree.
 */
export function seedFieldCounter( tree ) {
	let max = 0;
	const walk = ( fields ) => {
		fields.forEach( ( f ) => {
			const m = String( f.name || '' ).match( /_(\d+)$/ );
			if ( m ) {
				max = Math.max( max, parseInt( m[ 1 ], 10 ) );
			}
		} );
	};
	tree.forEach( ( node ) => {
		if ( node.type === 'field' && node.field ) {
			walk( [ node.field ] );
		} else if ( node.type === 'row' ) {
			( node.children || [] ).forEach( ( col ) => walk( col.fields || [] ) );
		}
	} );
	fieldCounter = max;
}
