/**
 * Editor: Section title (inspector + ServerSideRender).
 *
 * @package ZSkeleton_Theme
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var getBlockType = wp.blocks.getBlockType;
	var __ = wp.i18n.__;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var RichText = wp.blockEditor.RichText;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var BaseControl = wp.components.BaseControl;
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;

	/** Curated Dashicons (slug without `dashicons-` prefix), same set as Expert Profile CTA. */
	var TITLE_DASHICON_OPTIONS = [
		{ label: __( 'None', 'zskeleton' ), value: '' },
		{ label: __( 'User', 'zskeleton' ), value: 'admin-users' },
		{ label: __( 'ID card', 'zskeleton' ), value: 'id' },
		{ label: __( 'ID (alt)', 'zskeleton' ), value: 'id-alt' },
		{ label: __( 'Business', 'zskeleton' ), value: 'businessman' },
		{ label: __( 'Nametag', 'zskeleton' ), value: 'nametag' },
		{ label: __( 'Star', 'zskeleton' ), value: 'star-filled' },
		{ label: __( 'Award', 'zskeleton' ), value: 'awards' },
		{ label: __( 'Book', 'zskeleton' ), value: 'book-alt' },
		{ label: __( 'Megaphone', 'zskeleton' ), value: 'megaphone' },
		{ label: __( 'Chart', 'zskeleton' ), value: 'chart-area' },
		{ label: __( 'Groups', 'zskeleton' ), value: 'groups' },
		{ label: __( 'Heart', 'zskeleton' ), value: 'heart' },
		{ label: __( 'Site / globe', 'zskeleton' ), value: 'admin-site' },
		{ label: __( 'Learn more', 'zskeleton' ), value: 'welcome-learn-more' },
		{ label: __( 'Portfolio', 'zskeleton' ), value: 'portfolio' },
		{ label: __( 'Lightbulb', 'zskeleton' ), value: 'lightbulb' },
		{ label: __( 'Clipboard', 'zskeleton' ), value: 'clipboard' },
	];

	function clampSepWidth( n ) {
		var v = parseInt( n, 10 );
		if ( isNaN( v ) ) {
			return 72;
		}
		return Math.min( 480, Math.max( 4, v ) );
	}

	function clampSepHeight( n ) {
		var v = parseInt( n, 10 );
		if ( isNaN( v ) ) {
			return 4;
		}
		return Math.min( 64, Math.max( 1, v ) );
	}

	function clampSepRadius( n ) {
		var v = parseInt( n, 10 );
		if ( isNaN( v ) ) {
			return 999;
		}
		return Math.min( 999, Math.max( 0, v ) );
	}

	function hexOr( v, fallback ) {
		if ( typeof v === 'string' && /^#[0-9A-Fa-f]{6}$/.test( v ) ) {
			return v;
		}
		return fallback;
	}

	/**
	 * @param {string} v Raw user input.
	 * @return {string|null} Normalized #rrggbb or null.
	 */
	function tryCommitHex( v ) {
		var t = String( v || '' ).trim();
		if ( t.charAt( 0 ) !== '#' ) {
			t = '#' + t;
		}
		if ( /^#[0-9a-f]{6}$/i.test( t ) ) {
			return t.toLowerCase();
		}
		if ( /^#[0-9a-f]{3}$/i.test( t ) ) {
			var h = t.slice( 1 );
			return ( '#' + h.charAt( 0 ) + h.charAt( 0 ) + h.charAt( 1 ) + h.charAt( 1 ) + h.charAt( 2 ) + h.charAt( 2 ) ).toLowerCase();
		}
		return null;
	}

	/**
	 * Hex text field (primary) + small native swatch for accessible visual pick.
	 *
	 * @param {Object} props Block editor props subset.
	 */
	function HexColorField( props ) {
		var id = props.id;
		var label = props.label;
		var attrKey = props.attrKey;
		var fallback = props.fallback;
		var attrs = props.attrs;
		var setAttributes = props.setAttributes;

		var committed = hexOr( attrs[ attrKey ], fallback );
		var st = useState( committed );
		var draft = st[ 0 ];
		var setDraft = st[ 1 ];

		useEffect(
			function () {
				setDraft( hexOr( attrs[ attrKey ], fallback ) );
			},
			[ attrs[ attrKey ], attrKey, fallback ]
		);

		function onBlurDraft() {
			var c = tryCommitHex( draft );
			if ( ! c ) {
				setDraft( hexOr( attrs[ attrKey ], fallback ) );
			}
		}

		return createElement(
			'div',
			{ key: id, className: 'zskeleton-section-title-color-row', style: { marginBottom: '12px' } },
			createElement(
				BaseControl,
				{
					id: id + '-wrap',
					label: label,
					help: __( 'Enter #RRGGBB or #RGB. Preview updates when the value is valid.', 'zskeleton' ),
				},
				createElement(
					'div',
					{ style: { display: 'flex', gap: '8px', alignItems: 'center', flexWrap: 'wrap' } },
					createElement( TextControl, {
						id: id,
						hideLabelFromVision: true,
						label: label,
						value: draft,
						placeholder: fallback,
						onChange: function ( v ) {
							setDraft( v );
							var c = tryCommitHex( v );
							if ( c ) {
								var patch = {};
								patch[ attrKey ] = c;
								setAttributes( patch );
							}
						},
						onBlur: onBlurDraft,
						autoComplete: 'off',
						style: { minWidth: '9rem', flex: '1 1 auto' },
					} ),
					createElement( 'input', {
						type: 'color',
						className: 'zskeleton-section-title-color-swatch',
						'aria-label': __( 'Visual color picker', 'zskeleton' ),
						title: __( 'Visual color picker', 'zskeleton' ),
						value: committed,
						onChange: function ( e ) {
							var patch = {};
							patch[ attrKey ] = e.target.value;
							setAttributes( patch );
							setDraft( e.target.value );
						},
						style: {
							width: '42px',
							height: '32px',
							padding: 0,
							border: '1px solid #949494',
							borderRadius: '2px',
							cursor: 'pointer',
							flex: '0 0 auto',
						},
					} )
				)
			)
		);
	}

	var existing = getBlockType( 'zskeleton/section-title' );

	registerBlockType(
		'zskeleton/section-title',
		Object.assign( {}, existing || {}, {
			edit: function ( props ) {
				var attrs = props.attributes || {};
				var setAttributes = props.setAttributes;
				var blockProps = useBlockProps( { className: 'zskeleton-section-title-block-root' } );

				return createElement(
					Fragment,
					null,
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __( 'Layout', 'zskeleton' ), initialOpen: true },
							createElement( TextControl, {
								label: __( 'Container width (CSS)', 'zskeleton' ),
								help: __( 'Examples: min(1200px, 100%), 960px, 90%', 'zskeleton' ),
								value: attrs.containerWidth || '',
								onChange: function ( v ) {
									setAttributes( { containerWidth: v } );
								},
							} ),
							createElement( TextControl, {
								label: __( 'Min height (CSS)', 'zskeleton' ),
								help: __( 'Optional. Examples: 120px, 8rem, clamp(4rem, 20vh, 12rem)', 'zskeleton' ),
								value: attrs.minHeight || '',
								onChange: function ( v ) {
									setAttributes( { minHeight: v } );
								},
							} ),
							createElement( TextControl, {
								label: __( 'Padding (CSS)', 'zskeleton' ),
								value: attrs.padding || '',
								onChange: function ( v ) {
									setAttributes( { padding: v } );
								},
							} ),
							createElement( TextControl, {
								label: __( 'Border radius (CSS)', 'zskeleton' ),
								value: attrs.borderRadius || '',
								onChange: function ( v ) {
									setAttributes( { borderRadius: v } );
								},
							} ),
							createElement( SelectControl, {
								label: __( 'Position', 'zskeleton' ),
								value: attrs.textPosition || 'center',
								options: [
									{ label: __( 'Left', 'zskeleton' ), value: 'left' },
									{ label: __( 'Center', 'zskeleton' ), value: 'center' },
									{ label: __( 'Right', 'zskeleton' ), value: 'right' },
								],
								onChange: function ( v ) {
									setAttributes( { textPosition: v } );
								},
							} ),
							createElement( SelectControl, {
								label: __( 'Heading level', 'zskeleton' ),
								value: String( attrs.headingLevel || 2 ),
								options: [
									{ label: 'H2', value: '2' },
									{ label: 'H3', value: '3' },
									{ label: 'H4', value: '4' },
								],
								onChange: function ( v ) {
									setAttributes( { headingLevel: parseInt( v, 10 ) || 2 } );
								},
							} )
						),
						createElement(
							PanelBody,
							{ title: __( 'Title appearance', 'zskeleton' ), initialOpen: false },
							createElement( SelectControl, {
								label: __( 'Title icon', 'zskeleton' ),
								help: __( 'Optional Dashicon before the heading (same style as Expert Profile CTA).', 'zskeleton' ),
								value: attrs.titleDashicon || '',
								options: TITLE_DASHICON_OPTIONS,
								onChange: function ( v ) {
									setAttributes( { titleDashicon: v || '' } );
								},
							} ),
							createElement( ToggleControl, {
								label: __( 'Show accent bar under title', 'zskeleton' ),
								checked: attrs.titleShowSeparator !== false,
								onChange: function ( v ) {
									setAttributes( { titleShowSeparator: !!v } );
								},
							} ),
							createElement( RangeControl, {
								label: __( 'Separator width (px)', 'zskeleton' ),
								value: clampSepWidth( attrs.titleSeparatorWidthPx ),
								onChange: function ( v ) {
									setAttributes( { titleSeparatorWidthPx: v } );
								},
								min: 4,
								max: 480,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Separator height (px)', 'zskeleton' ),
								value: clampSepHeight( attrs.titleSeparatorHeightPx ),
								onChange: function ( v ) {
									setAttributes( { titleSeparatorHeightPx: v } );
								},
								min: 1,
								max: 64,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Separator border radius (px)', 'zskeleton' ),
								value: clampSepRadius( attrs.titleSeparatorRadiusPx ),
								onChange: function ( v ) {
									setAttributes( { titleSeparatorRadiusPx: v } );
								},
								min: 0,
								max: 999,
								step: 1,
							} ),
							createElement(
								'div',
								{ className: 'components-base-control', style: { marginBottom: '12px' } },
								createElement(
									'label',
									{
										className: 'components-base-control__label',
										htmlFor: 'sts-sep-color',
									},
									__( 'Separator color', 'zskeleton' )
								),
								createElement( 'input', {
									id: 'sts-sep-color',
									type: 'color',
									value: /^#[0-9A-Fa-f]{6}$/.test( attrs.titleSeparatorColor || '' )
										? attrs.titleSeparatorColor
										: '#b8d4eb',
									onChange: function ( e ) {
										setAttributes( { titleSeparatorColor: e.target.value } );
									},
									'aria-label': __( 'Separator color', 'zskeleton' ),
									style: { width: '100%', maxWidth: '120px', height: '32px', cursor: 'pointer' },
								} )
							)
						),
						createElement(
							PanelBody,
							{ title: __( 'Colors', 'zskeleton' ), initialOpen: false },
							createElement( HexColorField, {
								id: 'sts-bg',
								label: __( 'Background', 'zskeleton' ),
								attrKey: 'backgroundColor',
								fallback: '#f1f5f9',
								attrs: attrs,
								setAttributes: setAttributes,
							} ),
							createElement( HexColorField, {
								id: 'sts-fg',
								label: __( 'Text', 'zskeleton' ),
								attrKey: 'textColor',
								fallback: '#0f172a',
								attrs: attrs,
								setAttributes: setAttributes,
							} )
						)
					),
					createElement(
						'div',
						blockProps,
						createElement(
							'div',
							{ style: { marginBottom: '10px' } },
							createElement( RichText, {
								tagName: 'div',
								className: 'zskeleton-section-title-editor-preview-label',
								placeholder: __( 'Section heading…', 'zskeleton' ),
								value: attrs.title || '',
								onChange: function ( v ) {
									setAttributes( { title: v } );
								},
								allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
							} )
						),
						createElement( ServerSideRender, {
							block: 'zskeleton/section-title',
							attributes: attrs,
							httpMethod: 'POST',
						} )
					)
				);
			},
			save: function () {
				return null;
			},
		} )
	);
} )( window.wp );
