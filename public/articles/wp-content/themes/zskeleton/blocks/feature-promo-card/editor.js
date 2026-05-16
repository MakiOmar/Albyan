/**
 * Editor: Feature promo card (inspector + live ServerSideRender preview).
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
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var URLInput = wp.blockEditor.URLInput;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var ToggleControl = wp.components.ToggleControl;
	var SelectControl = wp.components.SelectControl;
	var RangeControl = wp.components.RangeControl;
	var Button = wp.components.Button;
	var ServerSideRender = wp.serverSideRender;

	var ICON_DASHICONS = [
		{ label: __( 'Groups / meeting', 'zskeleton' ), value: 'groups' },
		{ label: __( 'Business', 'zskeleton' ), value: 'businessman' },
		{ label: __( 'Microphone / speak', 'zskeleton' ), value: 'microphone' },
		{ label: __( 'Phone', 'zskeleton' ), value: 'phone' },
		{ label: __( 'Email', 'zskeleton' ), value: 'email' },
		{ label: __( 'Chart', 'zskeleton' ), value: 'chart-area' },
		{ label: __( 'Lightbulb', 'zskeleton' ), value: 'lightbulb' },
		{ label: __( 'Welcome / learn', 'zskeleton' ), value: 'welcome-learn-more' },
		{ label: __( 'Star', 'zskeleton' ), value: 'star-filled' },
		{ label: __( 'Heart', 'zskeleton' ), value: 'heart' },
		{ label: __( 'Book', 'zskeleton' ), value: 'book-alt' },
		{ label: __( 'Clipboard', 'zskeleton' ), value: 'clipboard' },
	];

	var TITLE_DASHICON_OPTIONS = [ { label: __( 'None', 'zskeleton' ), value: '' } ].concat( ICON_DASHICONS );

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

	function normalizeList( items ) {
		if ( ! Array.isArray( items ) ) {
			return [ '' ];
		}
		return items.map( function ( x ) {
			return typeof x === 'string' ? x : '';
		} );
	}

	var existingFpc = getBlockType( 'zskeleton/feature-promo-card' );

	registerBlockType(
		'zskeleton/feature-promo-card',
		Object.assign( {}, existingFpc || {}, {
		edit: function ( props ) {
			var attrs = props.attributes || {};
			var setAttributes = props.setAttributes;
			var list = normalizeList( attrs.listItems );

			var blockProps = useBlockProps( { className: 'zskeleton-feature-promo-card-block-root' } );

			function setListItem( index, value ) {
				var next = list.slice();
				next[ index ] = value;
				setAttributes( { listItems: next } );
			}

			function addListRow() {
				setAttributes( { listItems: list.concat( [ '' ] ) } );
			}

			function removeListRow( index ) {
				if ( list.length < 2 ) {
					return;
				}
				var next = list.filter( function ( _x, i ) {
					return i !== index;
				} );
				setAttributes( { listItems: next } );
			}

			function colorField( id, label, attrKey, fallback ) {
				return createElement(
					'div',
					{ key: id, className: 'components-base-control', style: { marginBottom: '12px' } },
					createElement(
						'label',
						{ className: 'components-base-control__label', htmlFor: id },
						label
					),
					createElement( 'input', {
						id: id,
						type: 'color',
						value: hexOr( attrs[ attrKey ], fallback ),
						onChange: function ( e ) {
							var patch = {};
							patch[ attrKey ] = e.target.value;
							setAttributes( patch );
						},
						'aria-label': label,
						style: { width: '100%', maxWidth: '120px', height: '32px', cursor: 'pointer' },
					} )
				);
			}

			return createElement(
				Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: __( 'Icon', 'zskeleton' ), initialOpen: true },
						createElement( SelectControl, {
							label: __( 'Icon type', 'zskeleton' ),
							value: attrs.iconMode || 'dashicon',
							options: [
								{ label: __( 'Dashicon', 'zskeleton' ), value: 'dashicon' },
								{ label: __( 'Image from Media Library', 'zskeleton' ), value: 'image' },
								{ label: __( 'None', 'zskeleton' ), value: 'none' },
							],
							onChange: function ( v ) {
								setAttributes( { iconMode: v } );
							},
						} ),
						( attrs.iconMode || 'dashicon' ) === 'dashicon'
							? createElement( SelectControl, {
								label: __( 'Dashicon', 'zskeleton' ),
								value: attrs.iconDashicon || 'groups',
								options: ICON_DASHICONS,
								onChange: function ( v ) {
									setAttributes( { iconDashicon: v } );
								},
							} )
							: null,
						( attrs.iconMode || 'dashicon' ) === 'image'
							? createElement(
								Fragment,
								null,
								createElement(
									MediaUploadCheck,
									null,
									createElement( MediaUpload, {
										onSelect: function ( media ) {
											setAttributes( {
												iconImageId: media && media.id ? media.id : 0,
												iconImageUrl: media && media.url ? media.url : '',
											} );
										},
										allowedTypes: [ 'image' ],
										value: attrs.iconImageId || 0,
										render: function ( obj ) {
											return createElement(
												Button,
												{ variant: 'secondary', onClick: obj.open },
												attrs.iconImageUrl
													? __( 'Replace icon image', 'zskeleton' )
													: __( 'Select icon image', 'zskeleton' )
											);
										},
									} )
								),
								attrs.iconImageUrl
									? createElement(
										Button,
										{
											variant: 'tertiary',
											onClick: function () {
												setAttributes( { iconImageId: 0, iconImageUrl: '' } );
											},
										},
										__( 'Remove image', 'zskeleton' )
									)
									: null,
								createElement( TextControl, {
									label: __( 'Icon image alt text', 'zskeleton' ),
									value: attrs.iconImageAlt || '',
									onChange: function ( v ) {
										setAttributes( { iconImageAlt: v } );
									},
								} )
							)
							: null
					),
					createElement(
						PanelBody,
						{ title: __( 'Typography', 'zskeleton' ), initialOpen: false },
						createElement( TextControl, {
							label: __( 'Title font stack (optional)', 'zskeleton' ),
							help: __(
								'Example: "Cairo", "Tajawal", sans-serif — leave empty to use the theme default.',
								'zskeleton'
							),
							value: attrs.titleFontFamily || '',
							onChange: function ( v ) {
								setAttributes( { titleFontFamily: v } );
							},
						} ),
						createElement( TextControl, {
							label: __( 'Body & list font stack (optional)', 'zskeleton' ),
							value: attrs.bodyFontFamily || '',
							onChange: function ( v ) {
								setAttributes( { bodyFontFamily: v } );
							},
						} ),
						createElement(
							'p',
							{
								key: 'dir-help',
								className: 'components-base-control__help',
								style: { marginTop: '0' },
							},
							__(
								'Text direction matches the site language (WordPress RTL setting), including list bullets.',
								'zskeleton'
							)
						)
					),
					createElement(
						PanelBody,
						{ title: __( 'Title appearance', 'zskeleton' ), initialOpen: false },
						createElement( SelectControl, {
							label: __( 'Title icon', 'zskeleton' ),
							help: __(
								'Optional Dashicon before the card title (same accent bar as Expert Profile CTA).',
								'zskeleton'
							),
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
									htmlFor: 'fpc-sep-color',
								},
								__( 'Separator color', 'zskeleton' )
							),
							createElement( 'input', {
								id: 'fpc-sep-color',
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
						colorField( 'fpc-card-bg', __( 'Card background', 'zskeleton' ), 'cardBackground', '#ffffff' ),
						colorField(
							'fpc-header-bg',
							__( 'Header strip background', 'zskeleton' ),
							'headerBackground',
							'#f0f4f7'
						),
						colorField( 'fpc-title-c', __( 'Title text', 'zskeleton' ), 'titleColor', '#1e293b' ),
						colorField( 'fpc-body-c', __( 'Body text', 'zskeleton' ), 'bodyColor', '#475569' ),
						colorField( 'fpc-list-c', __( 'List text', 'zskeleton' ), 'listColor', '#475569' ),
						colorField(
							'fpc-bullet-c',
							__( 'List bullet', 'zskeleton' ),
							'listBulletColor',
							'#64748b'
						),
						colorField( 'fpc-icon-c', __( 'Icon (Dashicon)', 'zskeleton' ), 'iconColor', '#334e68' ),
						colorField(
							'fpc-btn-bg',
							__( 'Button background', 'zskeleton' ),
							'buttonBackground',
							'#5086b3'
						),
						colorField(
							'fpc-btn-fg',
							__( 'Button text', 'zskeleton' ),
							'buttonTextColor',
							'#ffffff'
						)
					),
					createElement(
						PanelBody,
						{ title: __( 'Layout', 'zskeleton' ), initialOpen: false },
						createElement( RangeControl, {
							label: __( 'Card corner radius (px)', 'zskeleton' ),
							value: parseInt( attrs.cardBorderRadiusPx, 10 ) || 24,
							onChange: function ( v ) {
								setAttributes( { cardBorderRadiusPx: v } );
							},
							min: 0,
							max: 80,
							step: 1,
						} ),
						createElement( RangeControl, {
							label: __( 'Inner padding (px)', 'zskeleton' ),
							value: parseInt( attrs.cardPaddingPx, 10 ) || 32,
							onChange: function ( v ) {
								setAttributes( { cardPaddingPx: v } );
							},
							min: 8,
							max: 80,
							step: 1,
						} ),
						createElement( RangeControl, {
							label: __( 'Header top padding (px)', 'zskeleton' ),
							value: parseInt( attrs.headerPaddingTopPx, 10 ) || 28,
							onChange: function ( v ) {
								setAttributes( { headerPaddingTopPx: v } );
							},
							min: 0,
							max: 120,
							step: 1,
						} ),
						createElement( RangeControl, {
							label: __( 'Header wave depth (px)', 'zskeleton' ),
							help: __(
								'Controls the curved bottom of the header strip.',
								'zskeleton'
							),
							value: parseInt( attrs.headerWaveDepthPx, 10 ) || 26,
							onChange: function ( v ) {
								setAttributes( { headerWaveDepthPx: v } );
							},
							min: 8,
							max: 80,
							step: 1,
						} ),
						createElement( SelectControl, {
							label: __( 'Card shadow', 'zskeleton' ),
							value: attrs.shadowStrength || 'medium',
							options: [
								{ label: __( 'None', 'zskeleton' ), value: 'none' },
								{ label: __( 'Soft', 'zskeleton' ), value: 'soft' },
								{ label: __( 'Medium', 'zskeleton' ), value: 'medium' },
								{ label: __( 'Strong', 'zskeleton' ), value: 'strong' },
							],
							onChange: function ( v ) {
								setAttributes( { shadowStrength: v } );
							},
						} )
					),
					createElement(
						PanelBody,
						{ title: __( 'List items', 'zskeleton' ), initialOpen: false },
						list.map( function ( row, index ) {
							return createElement(
								'div',
								{ key: 'li-' + index, style: { display: 'flex', gap: '8px', alignItems: 'flex-start' } },
								createElement( TextControl, {
									label: __( 'Bullet', 'zskeleton' ) + ' ' + ( index + 1 ),
									value: row,
									onChange: function ( v ) {
										setListItem( index, v );
									},
									style: { flex: '1 1 auto' },
								} ),
								createElement(
									Button,
									{
										variant: 'secondary',
										isDestructive: true,
										onClick: function () {
											removeListRow( index );
										},
										style: { marginTop: '24px' },
									},
									__( 'Remove', 'zskeleton' )
								)
							);
						} ),
						createElement(
							Button,
							{ variant: 'secondary', onClick: addListRow, style: { marginTop: '8px' } },
							__( 'Add list item', 'zskeleton' )
						)
					),
					createElement(
						PanelBody,
						{ title: __( 'Button', 'zskeleton' ), initialOpen: false },
						createElement( TextControl, {
							label: __( 'Label', 'zskeleton' ),
							value: attrs.buttonLabel || '',
							onChange: function ( v ) {
								setAttributes( { buttonLabel: v } );
							},
						} ),
						createElement(
							'div',
							{ style: { marginBottom: '12px' } },
							createElement( 'p', { style: { marginBottom: '8px' } }, __( 'URL', 'zskeleton' ) ),
							createElement( URLInput, {
								value: attrs.buttonUrl || '',
								onChange: function ( v ) {
									setAttributes( { buttonUrl: v } );
								},
							} )
						),
						createElement( SelectControl, {
							label: __( 'Open in', 'zskeleton' ),
							value: attrs.buttonTarget || '_self',
							options: [
								{ label: __( 'Same tab', 'zskeleton' ), value: '_self' },
								{ label: __( 'New tab', 'zskeleton' ), value: '_blank' },
							],
							onChange: function ( v ) {
								setAttributes( { buttonTarget: v } );
							},
						} ),
						createElement( TextControl, {
							label: __( 'Aria label (optional)', 'zskeleton' ),
							value: attrs.buttonAriaLabel || '',
							onChange: function ( v ) {
								setAttributes( { buttonAriaLabel: v } );
							},
						} ),
						createElement( ToggleControl, {
							label: __( 'rel="nofollow"', 'zskeleton' ),
							checked: !!attrs.buttonRelNoFollow,
							onChange: function ( v ) {
								setAttributes( { buttonRelNoFollow: v } );
							},
						} ),
						createElement( ToggleControl, {
							label: __( 'rel="sponsored"', 'zskeleton' ),
							checked: !!attrs.buttonRelSponsored,
							onChange: function ( v ) {
								setAttributes( { buttonRelSponsored: v } );
							},
						} )
					)
				),
				createElement(
					'div',
					blockProps,
					createElement(
						'div',
						{ style: { marginBottom: '12px' } },
						createElement(
							RichText,
							{
								tagName: 'h2',
								className: 'zskeleton-feature-promo-card-editor-title',
								placeholder: __( 'Card title…', 'zskeleton' ),
								value: attrs.title || '',
								onChange: function ( v ) {
									setAttributes( { title: v } );
								},
								allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
							}
						),
						createElement(
							RichText,
							{
								tagName: 'div',
								className: 'zskeleton-feature-promo-card-editor-body',
								placeholder: __( 'Supporting text (optional)…', 'zskeleton' ),
								value: attrs.bodyHtml || '',
								onChange: function ( v ) {
									setAttributes( { bodyHtml: v } );
								},
								allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
							}
						)
					),
					createElement( ServerSideRender, {
						block: 'zskeleton/feature-promo-card',
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
