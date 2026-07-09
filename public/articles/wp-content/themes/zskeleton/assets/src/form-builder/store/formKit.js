import { defineStore } from 'pinia';
import {
	repairLayoutTree,
	seedFieldCounter,
	defaultField,
	createRow,
	getFieldAtPath,
	setFieldAtPath,
	normalizeField,
} from '../composables/useFormBuilder.js';

function ensureEventUid( event ) {
	if ( ! event._uid ) {
		event._uid = 'ev_' + Date.now().toString( 36 ) + '_' + Math.random().toString( 36 ).slice( 2, 8 );
	}
	return event;
}

function migrateLegacyRedirect( events, redirectUrl ) {
	const list = Array.isArray( events ) ? events.map( ( ev ) => ensureEventUid( { ...ev } ) ) : [];
	const url = typeof redirectUrl === 'string' ? redirectUrl.trim() : '';
	if ( '' === url ) {
		return list;
	}
	const hasRedirect = list.some( ( ev ) => ev.type === 'redirect' );
	if ( hasRedirect ) {
		return list;
	}
	list.push(
		ensureEventUid( {
			type: 'redirect',
			enabled: true,
			url,
		} )
	);
	return list;
}

export const useFormKitStore = defineStore( 'formKit', {
	state: () => ( {
		postId: 0,
		formId: '',
		shortcode: '',
		layoutTree: [],
		settings: {
			allowPublic: true,
			honeypot: 'company_website',
			successMessage: '',
			redirectUrl: '',
			submitButtonText: '',
			submissionsManagerRoles: '',
			submissionsManagerUsers: '',
			mobileStackRows: true,
		},
		events: [],
		selectedPath: null,
		fieldTypes: [],
		eventTypes: [],
		ajaxUrl: '',
		nonce: '',
		strings: {},
		assetDebug: {},
		activeTab: 'builder',
	} ),

	getters: {
		selectedField( state ) {
			return getFieldAtPath( state.layoutTree, state.selectedPath );
		},

		schemaJson( state ) {
			return {
				context: 'public',
				allow_public_submission: state.settings.allowPublic,
				use_ajax: true,
				fallback: 'long_page',
				layout_tree: state.layoutTree,
				honeypot: state.settings.honeypot,
				success_message: state.settings.successMessage,
				submit_button_text: state.settings.submitButtonText,
				submissions_manager_roles: state.settings.submissionsManagerRoles,
				submissions_manager_users: state.settings.submissionsManagerUsers,
				layout: { mobile_stack_rows: state.settings.mobileStackRows !== false },
			};
		},
	},

	actions: {
		hydrate( bootstrap ) {
			this.postId = bootstrap.postId || 0;
			this.formId = bootstrap.formId || '';
			this.shortcode = bootstrap.shortcode || '';
			this.layoutTree = repairLayoutTree( bootstrap.layoutTree || [] );
			seedFieldCounter( this.layoutTree );
			this.settings = {
				allowPublic: bootstrap.settings?.allowPublic !== false,
				honeypot: bootstrap.settings?.honeypot || 'company_website',
				successMessage: bootstrap.settings?.successMessage || '',
				redirectUrl: bootstrap.settings?.redirectUrl || '',
				submitButtonText: bootstrap.settings?.submitButtonText || '',
				submissionsManagerRoles: bootstrap.settings?.submissionsManagerRoles || '',
				submissionsManagerUsers: bootstrap.settings?.submissionsManagerUsers || '',
				mobileStackRows: bootstrap.settings?.mobileStackRows !== false,
			};
			this.events = migrateLegacyRedirect( bootstrap.events, bootstrap.settings?.redirectUrl );
			this.fieldTypes = bootstrap.fieldTypes || [];
			this.eventTypes = bootstrap.eventTypes || [];
			this.ajaxUrl = bootstrap.ajaxUrl || '';
			this.nonce = bootstrap.nonce || '';
			this.strings = bootstrap.strings || {};
			this.assetDebug = bootstrap.assetDebug || {};
		},

		addField( type, target = 'canvas' ) {
			const field = defaultField( type );
			const fieldNode = { type: 'field', field };

			if ( target === 'canvas' ) {
				this.layoutTree.push( fieldNode );
				this.selectedPath = { kind: 'root', index: this.layoutTree.length - 1 };
				return;
			}

			const { rowIndex, colIndex } = target;
			const row = this.layoutTree[ rowIndex ];
			if ( row?.type === 'row' && row.children?.[ colIndex ] ) {
				const fields = row.children[ colIndex ].fields;
				fields.push( field );
				this.selectedPath = {
					kind: 'column',
					rowIndex,
					colIndex,
					fieldIndex: fields.length - 1,
				};
			}
		},

		addRow( columns ) {
			this.layoutTree.push( createRow( columns ) );
			this.selectedPath = null;
		},

		removeRootNode( index ) {
			if ( this.selectedPath?.kind === 'root' && this.selectedPath.index === index ) {
				this.selectedPath = null;
			}
			this.layoutTree.splice( index, 1 );
		},

		removeColumnField( rowIndex, colIndex, fieldIndex ) {
			const row = this.layoutTree[ rowIndex ];
			if ( row?.type === 'row' ) {
				row.children[ colIndex ]?.fields.splice( fieldIndex, 1 );
			}
			const p = this.selectedPath;
			if (
				p?.kind === 'column' &&
				p.rowIndex === rowIndex &&
				p.colIndex === colIndex &&
				p.fieldIndex === fieldIndex
			) {
				this.selectedPath = null;
			}
		},

		selectField( path ) {
			this.selectedPath = path;
		},

		/**
		 * Keep field selection aligned after drag-and-drop reorder.
		 *
		 * @param {object} payload Reorder metadata.
		 */
		adjustSelectionAfterReorder( payload ) {
			const p = this.selectedPath;
			if ( ! p || ! payload ) {
				return;
			}

			if ( payload.scope === 'root' && p.kind === 'root' ) {
				if ( p.index === payload.oldIndex ) {
					this.selectedPath = { kind: 'root', index: payload.newIndex };
				} else if ( p.index > payload.oldIndex && p.index <= payload.newIndex ) {
					this.selectedPath = { kind: 'root', index: p.index - 1 };
				} else if ( p.index < payload.oldIndex && p.index >= payload.newIndex ) {
					this.selectedPath = { kind: 'root', index: p.index + 1 };
				}
				return;
			}

			if (
				payload.scope === 'column' &&
				p.kind === 'column' &&
				p.rowIndex === payload.rowIndex &&
				p.colIndex === payload.colIndex
			) {
				if ( p.fieldIndex === payload.oldIndex ) {
					this.selectedPath = {
						...p,
						fieldIndex: payload.newIndex,
					};
				} else if ( p.fieldIndex > payload.oldIndex && p.fieldIndex <= payload.newIndex ) {
					this.selectedPath = {
						...p,
						fieldIndex: p.fieldIndex - 1,
					};
				} else if ( p.fieldIndex < payload.oldIndex && p.fieldIndex >= payload.newIndex ) {
					this.selectedPath = {
						...p,
						fieldIndex: p.fieldIndex + 1,
					};
				}
			}
		},

		updateSelectedField( patch ) {
			if ( ! this.selectedPath ) {
				return;
			}
			const current = getFieldAtPath( this.layoutTree, this.selectedPath );
			if ( ! current ) {
				return;
			}
			const updated = normalizeField( { ...current, ...patch } );
			this.layoutTree = setFieldAtPath( this.layoutTree, this.selectedPath, updated );
		},

		addEvent( type = 'save_submission' ) {
			const ev = { type, enabled: type === 'save_submission' || type === 'redirect' };
			if ( type === 'email_admin' ) {
				ev.subject = this.strings.defaultAdminSubject || 'New form submission';
			}
			this.events.push( ensureEventUid( ev ) );
		},

		setEvents( events ) {
			this.events = Array.isArray( events ) ? events.map( ( ev ) => ensureEventUid( { ...ev } ) ) : [];
		},

		removeEvent( index ) {
			this.events.splice( index, 1 );
		},

		updateEvent( index, patch ) {
			if ( ! this.events[ index ] ) {
				return;
			}
			this.events.splice( index, 1, { ...this.events[ index ], ...patch } );
		},
	},
} );
