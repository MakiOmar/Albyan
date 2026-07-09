/**
 * Field type metadata for palette icons and accent colors.
 */

const TYPE_META = {
	text: { icon: 'T', tw: 'fb:bg-slate-100 fb:text-slate-700', cls: 'text' },
	email: { icon: '@', tw: 'fb:bg-sky-100 fb:text-sky-700', cls: 'email' },
	tel: { icon: '#', tw: 'fb:bg-teal-100 fb:text-teal-700', cls: 'tel' },
	url: { icon: '↗', tw: 'fb:bg-cyan-100 fb:text-cyan-700', cls: 'url' },
	textarea: { icon: '¶', tw: 'fb:bg-violet-100 fb:text-violet-700', cls: 'textarea' },
	select: { icon: '▼', tw: 'fb:bg-indigo-100 fb:text-indigo-700', cls: 'select' },
	checkbox: { icon: '☑', tw: 'fb:bg-emerald-100 fb:text-emerald-700', cls: 'checkbox' },
	radio: { icon: '◉', tw: 'fb:bg-lime-100 fb:text-lime-800', cls: 'radio' },
	toggle: { icon: '◐', tw: 'fb:bg-amber-100 fb:text-amber-800', cls: 'toggle' },
	number: { icon: 'N', tw: 'fb:bg-orange-100 fb:text-orange-700', cls: 'number' },
	date: { icon: 'D', tw: 'fb:bg-rose-100 fb:text-rose-700', cls: 'date' },
};

/**
 * @param {string} type Field type slug.
 * @return {{ icon: string, tw: string, cls: string, badgeClass: string, iconClass: string }}
 */
export function fieldTypeMeta( type ) {
	const base = TYPE_META[ type ] || {
		icon: type.charAt( 0 ).toUpperCase(),
		tw: 'fb:bg-indigo-100 fb:text-indigo-700',
		cls: 'text',
	};
	return {
		...base,
		badgeClass: `zs-fb-field-card__badge--${ base.cls }`,
		iconClass: `zs-fb-palette-icon--${ base.cls }`,
	};
}

/**
 * @param {string} type Field type slug.
 * @return {string}
 */
export function fieldTypeLabel( type ) {
	return type.charAt( 0 ).toUpperCase() + type.slice( 1 );
}
