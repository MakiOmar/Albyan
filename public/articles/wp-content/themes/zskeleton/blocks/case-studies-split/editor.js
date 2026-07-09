/**
 * Case study split testimonial - inspector + SSR preview.
 *
 * @package ZSkeleton_Theme
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var getBlockType = wp.blocks.getBlockType;
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var RichText = wp.blockEditor.RichText;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var ServerSideRender = wp.serverSideRender;
	var PanelBody = wp.components.PanelBody;
	var TextareaControl = wp.components.TextareaControl;
	var TextControl = wp.components.TextControl;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var Button = wp.components.Button;
	var BaseControl = wp.components.BaseControl;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;

	var TITLE_DASHICON_OPTIONS = [
		{ label: __( 'None', 'zskeleton' ), value: '' },
		{ label: __( 'User', 'zskeleton' ), value: 'admin-users' },
		{ label: __( 'Business', 'zskeleton' ), value: 'businessman' },
		{ label: __( 'Chart', 'zskeleton' ), value: 'chart-area' },
		{ label: __( 'Star', 'zskeleton' ), value: 'star-filled' },
		{ label: __( 'Award', 'zskeleton' ), value: 'awards' },
		{ label: __( 'Groups', 'zskeleton' ), value: 'groups' },
		{ label: __( 'Site / globe', 'zskeleton' ), value: 'admin-site' },
		{ label: __( 'Portfolio', 'zskeleton' ), value: 'portfolio' },
		{ label: __( 'Megaphone', 'zskeleton' ), value: 'megaphone' },
	];

	function hexOr( val, fb ) {
		return typeof val === 'string' && /^#[0-9A-Fa-f]{6}$/.test( val ) ? val : fb;
	}

	function tryCommitHex( raw ) {
		var t = String( raw || '' ).trim();
		if ( t.charAt( 0 ) !== '#' ) {
			t = '#' + t;
		}
		if ( /^#[0-9a-f]{6}$/i.test( t ) ) {
			return t.toLowerCase();
		}
		if ( /^#[0-9a-f]{3}$/i.test( t ) ) {
			var h = t.slice( 1 );
			return (
				'#' +
				h.charAt( 0 ) +
				h.charAt( 0 ) +
				h.charAt( 1 ) +
				h.charAt( 1 ) +
				h.charAt( 2 ) +
				h.charAt( 2 )
			).toLowerCase();
		}
		return null;
	}

	/**
	 * Text field + picker for #RRGGBB (matches Section Title block UX).
	 */
	function CssplitHexColorField( propsFld ) {
		var idField = propsFld.id;
		var labelField = propsFld.label;
		var attrKey = propsFld.attrKey;
		var fallback = propsFld.fallbackHex;
		var attrsFld = propsFld.attrs;
		var setAttrs = propsFld.setAttributes;

		var committed = hexOr( attrsFld[ attrKey ], fallback );
		var st = useState( committed );

		useEffect(
			function sync() {
				st[ 1 ]( hexOr( attrsFld[ attrKey ], fallback ) );
			},
			[ attrsFld[ attrKey ], attrKey, fallback ]
		);

		function blurFix() {
			var commit = tryCommitHex( st[ 0 ] );
			if ( ! commit ) {
				st[ 1 ]( hexOr( attrsFld[ attrKey ], fallback ) );
			}
		}

		return el(
			'div',
			{ key: idField, className: 'zskeleton-cssplit-color-field', style: { marginBottom: '12px' } },
			el(
				BaseControl,
				{
					id: idField + '-wrap',
					label: labelField,
					help: __( 'Enter #RRGGBB or #RGB beside the picker.', 'zskeleton' ),
				},
				el(
					'div',
					{
						style: {
							display: 'flex',
							gap: '8px',
							alignItems: 'center',
							flexWrap: 'wrap',
						},
					},
					el( TextControl, {
						id: idField,
						hideLabelFromVision: true,
						label: labelField,
						value: st[ 0 ],
						placeholder: fallback,
						onChange: function ( v ) {
							st[ 1 ]( v );
							var okHex = tryCommitHex( v );
							if ( okHex ) {
								var ch = {};
								ch[ attrKey ] = okHex;
								setAttrs( ch );
							}
						},
						onBlur: blurFix,
						autoComplete: 'off',
						style: { minWidth: '9rem', flex: '1 1 auto' },
					} ),
					el( 'input', {
						type: 'color',
						'aria-label': __( 'Visual color picker', 'zskeleton' ),
						title: __( 'Visual color picker', 'zskeleton' ),
						value: committed,
						onChange: function ( evt ) {
							var ch2 = {};
							ch2[ attrKey ] = evt.target.value;
							setAttrs( ch2 );
							st[ 1 ]( evt.target.value );
						},
						style: {
							width: '42px',
							height: '32px',
							padding: 0,
							border: '1px solid #949494',
							borderRadius: '2px',
							cursor: 'pointer',
							flexShrink: '0',
						},
					} ),
					el(
						Button,
						{
							isSmall: true,
							variant: 'tertiary',
							onClick: function flush() {
								var z = {};
								z[ attrKey ] = '';
								setAttrs( z );
							},
						},
						__( 'Default', 'zskeleton' )
					)
				)
			)
		);
	}

	function sepWidthClamp( raw ) {
		raw = parseInt( raw, 10 );
		return isNaN( raw ) ? 72 : Math.min( 480, Math.max( 4, raw ) );
	}

	function sepHeightClamp( raw ) {
		raw = parseInt( raw, 10 );
		return isNaN( raw ) ? 4 : Math.min( 64, Math.max( 1, raw ) );
	}

	function sepRadiusClamp( raw ) {
		raw = parseInt( raw, 10 );
		return isNaN( raw ) ? 999 : Math.min( 999, Math.max( 0, raw ) );
	}

	var existingBlockMeta = getBlockType( 'zskeleton/case-studies-split' );

	registerBlockType(
		'zskeleton/case-studies-split',
		Object.assign( {}, existingBlockMeta || {}, {
			edit: function ( propsEditor ) {
				var attrsEditor = propsEditor.attributes || {};
				var setAttrs = propsEditor.setAttributes;
				var blockPropsWrap = useBlockProps( {
					className: 'zskeleton-cssplit-editor-root',
				} );

				function mediaBlock( pid, purl, palt, labelPanel, helperAlt ) {
					var hasPic = !!( attrsEditor[ purl ] && attrsEditor[ pid ] );
					var idNumeric = attrsEditor[ pid ] || 0;

					var panelChildren = [];

					panelChildren.push(
						el(
							MediaUploadCheck,
							{ key: 'upl' },
							el( MediaUpload, {
								onSelect: function ( med ) {
									var upd = {};
									upd[ pid ] = med && med.id ? med.id : 0;
									upd[ purl ] = med && med.url ? med.url : '';
									setAttrs( upd );
								},
								allowedTypes: [ 'image' ],
								value: hasPic ? idNumeric : 0,
								render: function ( picker ) {
									return el(
										Button,
										{ variant: 'secondary', onClick: picker.open },
										hasPic
											? __( 'Replace image', 'zskeleton' )
											: __( 'Choose image...', 'zskeleton' )
									);
								},
							} )
						)
					);

					panelChildren.push(
						el( TextControl, {
							key: 'alt',
							label: __( 'Alt text', 'zskeleton' ),
							value: attrsEditor[ palt ] || '',
							onChange: function ( t ) {
								var o = {};
								o[ palt ] = t;
								setAttrs( o );
							},
							help: helperAlt || '',
						} )
					);

					if ( hasPic ) {
						panelChildren.push(
							el(
								Button,
								{
									key: 'rm',
									variant: 'tertiary',
									onClick: function () {
										var clr = {};
										clr[ pid ] = 0;
										clr[ purl ] = '';
										clr[ palt ] = '';
										setAttrs( clr );
									},
								},
								__( 'Remove image', 'zskeleton' )
							)
						);
					}

					return el( Fragment, null, panelChildren );
				}

				return el(
					Fragment,
					null,
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: __( 'Section headline', 'zskeleton' ), initialOpen: true },
							el( TextControl, {
								label: __( 'Title', 'zskeleton' ),
								value: attrsEditor.sectionTitle || '',
								onChange: function ( t ) {
									setAttrs( { sectionTitle: t } );
								},
							} ),
							el( TextareaControl, {
								label: __( 'Description', 'zskeleton' ),
								value: attrsEditor.sectionDescription || '',
								onChange: function ( t ) {
									setAttrs( { sectionDescription: t } );
								},
								rows: 3,
							} ),
							el( SelectControl, {
								label: __( 'Title accent icon', 'zskeleton' ),
								options: TITLE_DASHICON_OPTIONS,
								value: attrsEditor.titleDashicon || '',
								onChange: function ( v ) {
									setAttrs( { titleDashicon: v } );
								},
							} ),
							el( ToggleControl, {
								label: __( 'Show separator beneath title cluster', 'zskeleton' ),
								checked: attrsEditor.titleShowSeparator !== false,
								onChange: function ( chk ) {
									setAttrs( { titleShowSeparator: chk } );
								},
							} ),
							el( RangeControl, {
								label: __( 'Separator width (px)', 'zskeleton' ),
								value: sepWidthClamp( attrsEditor.titleSeparatorWidthPx ),
								min: 4,
								max: 480,
								step: 1,
								onChange: function ( vv ) {
									setAttrs( { titleSeparatorWidthPx: vv } );
								},
							} ),
							el( RangeControl, {
								label: __( 'Separator height (px)', 'zskeleton' ),
								value: sepHeightClamp( attrsEditor.titleSeparatorHeightPx ),
								min: 1,
								max: 64,
								step: 1,
								onChange: function ( vv ) {
									setAttrs( { titleSeparatorHeightPx: vv } );
								},
							} ),
							el( RangeControl, {
								label: __( 'Separator border radius (px)', 'zskeleton' ),
								value: sepRadiusClamp( attrsEditor.titleSeparatorRadiusPx ),
								min: 0,
								max: 999,
								step: 1,
								onChange: function ( vv ) {
									setAttrs( { titleSeparatorRadiusPx: vv } );
								},
							} ),
							el(
								'div',
								{
									className: 'components-base-control',
									style: { marginBottom: '12px' },
								},
								el(
									'label',
									{
										className: 'components-base-control__label',
										htmlFor: 'zskeleton-cssplit-separator-color-field',
									},
									__( 'Separator color', 'zskeleton' )
								),
								el( 'input', {
									id: 'zskeleton-cssplit-separator-color-field',
									type: 'color',
									value:
										/^#[0-9A-Fa-f]{6}$/.test( attrsEditor.titleSeparatorColor || '' ) ?
											attrsEditor.titleSeparatorColor :
											'#b8d4eb',
									onChange: function ( ee ) {
										setAttrs( { titleSeparatorColor: ee.target.value } );
									},
									'aria-label': __( 'Separator color', 'zskeleton' ),
									style: {
										width: '100%',
										maxWidth: '120px',
										height: '32px',
										cursor: 'pointer',
									},
								} )
							)
						),
						el(
							PanelBody,
							{ title: __( 'Layout & backdrop', 'zskeleton' ), initialOpen: true },
							el( SelectControl, {
								label: __( 'Text direction', 'zskeleton' ),
								options: [
									{ label: __( 'Auto / inherit document', 'zskeleton' ), value: 'auto' },
									{ label: __( 'Left-to-right', 'zskeleton' ), value: 'ltr' },
									{ label: __( 'Right-to-left', 'zskeleton' ), value: 'rtl' },
								],
								value: attrsEditor.textDirection || 'auto',
								onChange: function ( vv ) {
									setAttrs( { textDirection: vv } );
								},
							} ),
							el( RangeControl, {
								label: __( 'Split minimum height - desktop', 'zskeleton' ),
								help: __(
									'Screens roughly 783px+. Columns stay side-by-side and absorb this vertical space.',
									'zskeleton'
								),
								value: attrsEditor.stageMinHeightDesktop || 460,
								min: 200,
								max: 1200,
								step: 5,
								onChange: function ( n ) {
									setAttrs( {
										stageMinHeightDesktop: parseInt( n, 10 ) || 460,
									} );
								},
							} ),
							el( RangeControl, {
								label: __( 'Split minimum height - mobile', 'zskeleton' ),
								value: attrsEditor.stageMinHeightMobile || 480,
								min: 200,
								max: 1200,
								step: 5,
								onChange: function ( n ) {
									setAttrs( {
										stageMinHeightMobile: parseInt( n, 10 ) || 480,
									} );
								},
							} ),
							el( RangeControl, {
								label: __( 'Portrait max height - desktop', 'zskeleton' ),
								help: __( 'Natural image width is preserved; only max height is limited.', 'zskeleton' ),
								value: attrsEditor.portraitMaxHeightDesktop || 460,
								min: 120,
								max: 1200,
								step: 5,
								onChange: function ( n ) {
									setAttrs( {
										portraitMaxHeightDesktop: parseInt( n, 10 ) || 460,
									} );
								},
							} ),
							el( RangeControl, {
								label: __( 'Portrait max height - mobile', 'zskeleton' ),
								value: attrsEditor.portraitMaxHeightMobile || 260,
								min: 80,
								max: 800,
								step: 5,
								onChange: function ( n ) {
									setAttrs( {
										portraitMaxHeightMobile: parseInt( n, 10 ) || 260,
									} );
								},
							} ),
							el( RangeControl, {
								label: __( 'Card corner radius', 'zskeleton' ),
								value: attrsEditor.borderRadiusPx || 20,
								min: 0,
								max: 64,
								step: 1,
								onChange: function ( n ) {
									setAttrs( { borderRadiusPx: n } );
								},
							} ),
							el( RangeControl, {
								label: __( 'Backdrop blur intensity (px)', 'zskeleton' ),
								value: attrsEditor.blurRadiusPx || 18,
								min: 0,
								max: 48,
								step: 1,
								onChange: function ( n ) {
									setAttrs( { blurRadiusPx: n } );
								},
							} ),
							el(
								CssplitHexColorField,
								{
									key: 'g1',
									id: 'cssplit-g-start',
									label: __( 'Gradient start color', 'zskeleton' ),
									attrKey: 'gradientColor1',
									fallbackHex: '#f8fafc',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							),
							el(
								CssplitHexColorField,
								{
									key: 'g2',
									id: 'cssplit-g-end',
									label: __( 'Gradient end color', 'zskeleton' ),
									attrKey: 'gradientColor2',
									fallbackHex: '#e2e8f0',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							),
							el( RangeControl, {
								label: __( 'Gradient angle (degrees)', 'zskeleton' ),
								value: attrsEditor.gradientAngleDeg || 160,
								min: 0,
								max: 359,
								step: 1,
								onChange: function ( d ) {
									setAttrs( { gradientAngleDeg: parseInt( d, 10 ) || 0 } );
								},
							} )
						),
						el(
							PanelBody,
							{
								title: __( 'Portrait & overlay images', 'zskeleton' ),
								initialOpen: false,
							},
							el( 'p', { className: 'components-help', style: { marginTop: 0 } }, __( 'Sharp photograph on the left; optional separate texture for blurred column background (defaults to the portrait image). Brand mark sits above testimonial body.', 'zskeleton' ) ),
							el( 'hr', null ),
							el(
								'p',
								{ className: 'components-base-control__label' },
								__( 'Portrait (left column)', 'zskeleton' )
							),
							mediaBlock(
								'leftImageId',
								'leftImageUrl',
								'leftImageAlt',
								'l',
								__( 'Briefly describe the person for accessibility.', 'zskeleton' )
							),
							el( 'hr', null ),
							el(
								'p',
								{ className: 'components-base-control__label' },
								__( 'Blurred backdrop (optional)', 'zskeleton' )
							),
							mediaBlock( 'rightBlurImageId', 'rightBlurImageUrl', 'rightBlurImageAlt', 'b', __( 'Separate art for the frosted pane; clears to portrait.', 'zskeleton' ) ),
							el( 'hr', null ),
							el(
								'p',
								{ className: 'components-base-control__label' },
								__( 'Brand / logo', 'zskeleton' )
							),
							mediaBlock( 'brandLogoId', 'brandLogoUrl', 'brandLogoAlt', 'logo', __( 'Leave alt empty only if ornamental.', 'zskeleton' ) )
						),
						el(
							PanelBody,
							{
								title: __( 'Story copy', 'zskeleton' ),
								initialOpen: false,
							},
							el( TextControl, {
								label: __( 'Supporting line above quote', 'zskeleton' ),
								value: attrsEditor.cardSubtitle || '',
								onChange: function ( t ) {
									setAttrs( { cardSubtitle: t } );
								},
							} ),
							el( RichText, {
								tagName: 'div',
								className: 'zskeleton-cssplit-editor-quote',
								allowedFormats: [ 'core/bold', 'core/italic' ],
								multiline: 'p',
								placeholder: __( 'Testimonial quotation...', 'zskeleton' ),
								value: attrsEditor.quoteHtml || '',
								onChange: function ( html ) {
									setAttrs( { quoteHtml: html } );
								},
							} ),
							el( TextControl, {
								label: __( 'Full name', 'zskeleton' ),
								value: attrsEditor.personName || '',
								onChange: function ( t ) {
									setAttrs( { personName: t } );
								},
							} ),
							el( TextControl, {
								label: __( 'Role / title', 'zskeleton' ),
								value: attrsEditor.personRole || '',
								onChange: function ( t ) {
									setAttrs( { personRole: t } );
								},
							} )
						),
						el(
							PanelBody,
							{
								title: __( 'Colours', 'zskeleton' ),
								initialOpen: false,
							},
							el(
								CssplitHexColorField,
								{
									key: 'c-title',
									id: 'cssplit-c-heading',
									label: __( 'Section headline', 'zskeleton' ),
									attrKey: 'colorSectionTitle',
									fallbackHex: '#101828',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							),
							el(
								CssplitHexColorField,
								{
									key: 'c-intro',
									id: 'cssplit-c-desc',
									label: __( 'Lead paragraph', 'zskeleton' ),
									attrKey: 'colorSectionDesc',
									fallbackHex: '#475569',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							),
							el(
								CssplitHexColorField,
								{
									key: 'c-muted',
									id: 'cssplit-c-cardsub',
									label: __( 'Supporting line above quote', 'zskeleton' ),
									attrKey: 'colorCardSubtitle',
									fallbackHex: '#0f172a',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							),
							el(
								CssplitHexColorField,
								{
									key: 'c-quote',
									id: 'cssplit-c-quote',
									label: __( 'Quote body', 'zskeleton' ),
									attrKey: 'colorQuote',
									fallbackHex: '#475569',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							),
							el(
								CssplitHexColorField,
								{
									key: 'c-name',
									id: 'cssplit-c-name',
									label: __( 'Printed name', 'zskeleton' ),
									attrKey: 'colorPersonName',
									fallbackHex: '#101828',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							),
							el(
								CssplitHexColorField,
								{
									key: 'c-role',
									id: 'cssplit-c-role',
									label: __( 'Role subtitle', 'zskeleton' ),
									attrKey: 'colorPersonRole',
									fallbackHex: '#64748b',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							),
							el(
								CssplitHexColorField,
								{
									key: 'c-tint',
									id: 'cssplit-tint',
									label: __( 'Glossy veil over blurry column', 'zskeleton' ),
									attrKey: 'colorRightPanelTint',
									fallbackHex: '#ffffff',
									attrs: attrsEditor,
									setAttributes: setAttrs,
								}
							)
						)
					),

					el(
						'div',
						blockPropsWrap,
						el( ServerSideRender, {
							block: 'zskeleton/case-studies-split',
							attributes: attrsEditor,
							LoadingResponsePlaceholder: function () {
								return el(
									'p',
									{ className: 'components-spinner__help' },
									__( 'Loading preview...', 'zskeleton' )
								);
							},
						} )
					)
				);
			},
		} )
	);
} )( window.wp );

