/**
 * Editor: testimonials image slider (dynamic block + ServerSideRender).
 *
 * @package ZSkeleton_Theme
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var __ = wp.i18n.__;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var Button = wp.components.Button;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var URLInput = wp.blockEditor.URLInput;
	var ServerSideRender = wp.serverSideRender;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var useBlockProps = wp.blockEditor.useBlockProps;

	function normalizeSlides( slides ) {
		if ( ! Array.isArray( slides ) ) {
			return [];
		}
		return slides
			.map( function ( row ) {
				if ( ! row || typeof row !== 'object' ) {
					return null;
				}
				var id = parseInt( row.id, 10 ) || 0;
				if ( id < 1 ) {
					return null;
				}
				return {
					id: id,
					alt: typeof row.alt === 'string' ? row.alt : '',
					caption: typeof row.caption === 'string' ? row.caption : '',
				};
			} )
			.filter( Boolean );
	}

	function updateSlide( slides, index, patch ) {
		var next = normalizeSlides( slides ).slice();
		if ( ! next[ index ] ) {
			return next;
		}
		next[ index ] = Object.assign( {}, next[ index ], patch || {} );
		return next;
	}

	function moveSlide( slides, from, to ) {
		var next = normalizeSlides( slides ).slice();
		if ( from < 0 || from >= next.length || to < 0 || to >= next.length ) {
			return next;
		}
		var item = next.splice( from, 1 )[ 0 ];
		next.splice( to, 0, item );
		return next;
	}

	/**
	 * Color picker row (empty attribute = theme default in CSS).
	 *
	 * @param {Object} props       Block props.
	 * @param {string} key         Attribute key.
	 * @param {string} label       Visible label.
	 * @param {string} fallbackHex Fallback for color input when unset.
	 */
	function tisColorRow( props, key, label, fallbackHex ) {
		var attrs = props.attributes || {};
		var raw = typeof attrs[ key ] === 'string' ? attrs[ key ] : '';
		var display = /^#[0-9A-Fa-f]{6}$/i.test( raw ) ? raw : fallbackHex;
		return el(
			'div',
			{
				key: 'tis-color-row-' + key,
				className: 'components-base-control',
				style: { marginBottom: '12px' },
			},
			el(
				'label',
				{
					className: 'components-base-control__label',
					htmlFor: 'tis-color-' + key,
				},
				label
			),
			el( 'input', {
				id: 'tis-color-' + key,
				type: 'color',
				value: display,
				'aria-label': label,
				style: { width: '100%', maxWidth: '120px', height: '32px', cursor: 'pointer' },
				onChange: function ( e ) {
					var o = {};
					o[ key ] = e.target.value;
					props.setAttributes( o );
				},
			} ),
			el(
				Button,
				{
					key: 'tis-color-reset-' + key,
					isSmall: true,
					variant: 'tertiary',
					onClick: function () {
						var o2 = {};
						o2[ key ] = '';
						props.setAttributes( o2 );
					},
				},
				__( 'Default', 'zskeleton' )
			)
		);
	}

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

	function tisClampSepWidth( n ) {
		var v = parseInt( n, 10 );
		if ( isNaN( v ) ) {
			return 72;
		}
		return Math.min( 480, Math.max( 4, v ) );
	}

	function tisClampSepHeight( n ) {
		var v = parseInt( n, 10 );
		if ( isNaN( v ) ) {
			return 4;
		}
		return Math.min( 64, Math.max( 1, v ) );
	}

	function tisClampSepRadius( n ) {
		var v = parseInt( n, 10 );
		if ( isNaN( v ) ) {
			return 999;
		}
		return Math.min( 999, Math.max( 0, v ) );
	}

	registerBlockType( 'zskeleton/testimonials-image-slider', {
		edit: function ( props ) {
			var attrs = props.attributes || {};
			var slides = normalizeSlides( attrs.slides );
			var blockProps = useBlockProps( { className: 'zskeleton-tis-editor' } );

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Section text', 'zskeleton' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Badge title', 'zskeleton' ),
							value: attrs.badgeTitle || '',
							onChange: function ( v ) {
								props.setAttributes( { badgeTitle: v || '' } );
							},
						} ),
						el( TextControl, {
							label: __( 'Title (H2)', 'zskeleton' ),
							value: attrs.title || '',
							onChange: function ( v ) {
								props.setAttributes( { title: v || '' } );
							},
						} ),
						el( TextareaControl, {
							label: __( 'Subtitle / paragraph', 'zskeleton' ),
							value: attrs.paragraph || '',
							onChange: function ( v ) {
								props.setAttributes( { paragraph: v || '' } );
							},
							rows: 4,
						} ),
						el( SelectControl, {
							label: __( 'Text direction', 'zskeleton' ),
							value: attrs.textDirection || 'auto',
							options: [
								{ label: __( 'Auto (inherit)', 'zskeleton' ), value: 'auto' },
								{ label: __( 'Right to left', 'zskeleton' ), value: 'rtl' },
								{ label: __( 'Left to right', 'zskeleton' ), value: 'ltr' },
							],
							onChange: function ( v ) {
								props.setAttributes( { textDirection: v || 'auto' } );
							},
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Title appearance', 'zskeleton' ), initialOpen: false },
						el( SelectControl, {
							label: __( 'Title icon', 'zskeleton' ),
							help: __(
								'Optional Dashicon before the H2 (same accent bar options as Expert Profile CTA).',
								'zskeleton'
							),
							value: attrs.titleDashicon || '',
							options: TITLE_DASHICON_OPTIONS,
							onChange: function ( v ) {
								props.setAttributes( { titleDashicon: v || '' } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Show accent bar under title', 'zskeleton' ),
							checked: attrs.titleShowSeparator !== false,
							onChange: function ( v ) {
								props.setAttributes( { titleShowSeparator: !!v } );
							},
						} ),
						el( RangeControl, {
							label: __( 'Separator width (px)', 'zskeleton' ),
							value: tisClampSepWidth( attrs.titleSeparatorWidthPx ),
							onChange: function ( v ) {
								props.setAttributes( { titleSeparatorWidthPx: v } );
							},
							min: 4,
							max: 480,
							step: 1,
						} ),
						el( RangeControl, {
							label: __( 'Separator height (px)', 'zskeleton' ),
							value: tisClampSepHeight( attrs.titleSeparatorHeightPx ),
							onChange: function ( v ) {
								props.setAttributes( { titleSeparatorHeightPx: v } );
							},
							min: 1,
							max: 64,
							step: 1,
						} ),
						el( RangeControl, {
							label: __( 'Separator border radius (px)', 'zskeleton' ),
							value: tisClampSepRadius( attrs.titleSeparatorRadiusPx ),
							onChange: function ( v ) {
								props.setAttributes( { titleSeparatorRadiusPx: v } );
							},
							min: 0,
							max: 999,
							step: 1,
						} ),
						el(
							'div',
							{ className: 'components-base-control', style: { marginBottom: '12px' } },
							el(
								'label',
								{
									className: 'components-base-control__label',
									htmlFor: 'tis-sep-color',
								},
								__( 'Separator color', 'zskeleton' )
							),
							el( 'input', {
								id: 'tis-sep-color',
								type: 'color',
								value: /^#[0-9A-Fa-f]{6}$/.test( attrs.titleSeparatorColor || '' )
									? attrs.titleSeparatorColor
									: '#b8d4eb',
								onChange: function ( e ) {
									props.setAttributes( { titleSeparatorColor: e.target.value } );
								},
								'aria-label': __( 'Separator color', 'zskeleton' ),
								style: { width: '100%', maxWidth: '120px', height: '32px', cursor: 'pointer' },
							} )
						)
					),
					el(
						PanelBody,
						{ title: __( 'Colors', 'zskeleton' ), initialOpen: false },
						tisColorRow( props, 'colorSectionBg', __( 'Section background', 'zskeleton' ), '#f5f6f8' ),
						tisColorRow( props, 'colorBadgeBg', __( 'Badge background', 'zskeleton' ), '#1a2744' ),
						tisColorRow( props, 'colorBadgeText', __( 'Badge text', 'zskeleton' ), '#ffffff' ),
						tisColorRow( props, 'colorTitleText', __( 'Title text', 'zskeleton' ), '#2b2f36' ),
						tisColorRow( props, 'colorIntroText', __( 'Intro / paragraph text', 'zskeleton' ), '#4a5058' ),
						tisColorRow( props, 'colorCardBg', __( 'Slide card background', 'zskeleton' ), '#ffffff' ),
						tisColorRow( props, 'colorFigcaptionText', __( 'Caption text', 'zskeleton' ), '#4a5058' ),
						tisColorRow( props, 'colorDotInactive', __( 'Pagination dot (inactive)', 'zskeleton' ), '#c5cad3' ),
						tisColorRow( props, 'colorDotActive', __( 'Pagination dot (active)', 'zskeleton' ), '#1a2744' ),
						tisColorRow( props, 'colorCtaText', __( 'Text above button', 'zskeleton' ), '#1a2744' ),
						tisColorRow( props, 'colorButtonBg', __( 'Button background', 'zskeleton' ), '#2563eb' ),
						tisColorRow( props, 'colorButtonText', __( 'Button text', 'zskeleton' ), '#ffffff' )
					),
					el(
						PanelBody,
						{ title: __( 'Carousel images', 'zskeleton' ), initialOpen: true },
						el( RangeControl, {
							label: __( 'Slides visible at once (desktop/tablet)', 'zskeleton' ),
							help: __(
								'Use fewer than your total images so the strip can scroll; dots then move between slides. If visible is greater than or equal to total images, dots are hidden.',
								'zskeleton'
							),
							value: Math.min( 6, Math.max( 1, parseInt( attrs.slidesPerView, 10 ) || 1 ) ),
							onChange: function ( v ) {
								var n = parseInt( v, 10 );
								if ( isNaN( n ) ) {
									n = 1;
								}
								props.setAttributes( { slidesPerView: Math.min( 6, Math.max( 1, n ) ) } );
							},
							min: 1,
							max: 6,
							step: 1,
						} ),
						el( RangeControl, {
							label: __( 'Slides visible at once (mobile)', 'zskeleton' ),
							help: __( 'Applies on screens up to 767px.', 'zskeleton' ),
							value: Math.min( 6, Math.max( 1, parseInt( attrs.slidesPerViewMobile, 10 ) || 1 ) ),
							onChange: function ( v ) {
								var nMobile = parseInt( v, 10 );
								if ( isNaN( nMobile ) ) {
									nMobile = 1;
								}
								props.setAttributes( { slidesPerViewMobile: Math.min( 6, Math.max( 1, nMobile ) ) } );
							},
							min: 1,
							max: 6,
							step: 1,
						} ),
						el( ToggleControl, {
							label: __( 'Autoplay', 'zskeleton' ),
							help: __(
								'Advance slides automatically. Pauses on hover or while focus is inside the carousel. Disabled when the visitor prefers reduced motion.',
								'zskeleton'
							),
							checked: !!attrs.autoplay,
							onChange: function ( v ) {
								props.setAttributes( { autoplay: !!v } );
							},
						} ),
						attrs.autoplay
							? el( RangeControl, {
								label: __( 'Autoplay interval (seconds)', 'zskeleton' ),
								value: Math.min( 60, Math.max( 2, parseInt( attrs.autoplayIntervalSec, 10 ) || 5 ) ),
								onChange: function ( v ) {
									var s = parseInt( v, 10 );
									if ( isNaN( s ) ) {
										s = 5;
									}
									props.setAttributes( { autoplayIntervalSec: Math.min( 60, Math.max( 2, s ) ) } );
								},
								min: 2,
								max: 60,
								step: 1,
							} )
							: null,
						el(
							MediaUploadCheck,
							null,
							el( MediaUpload, {
								onSelect: function ( selected ) {
									var list = Array.isArray( selected ) ? selected : [ selected ];
									var mapped = list
										.map( function ( m ) {
											if ( ! m || ! m.id ) {
												return null;
											}
											return {
												id: m.id,
												alt: m.alt || '',
												caption: m.caption || '',
											};
										} )
										.filter( Boolean );
									props.setAttributes( { slides: mapped } );
								},
								allowedTypes: [ 'image' ],
								multiple: true,
								value: slides.map( function ( s ) {
									return s.id;
								} ),
								render: function ( renderProps ) {
									var open = renderProps && typeof renderProps.open === 'function' ? renderProps.open : function () {};
									return el(
										Button,
										{ variant: 'primary', onClick: open },
										slides.length
											? __( 'Replace gallery', 'zskeleton' )
											: __( 'Select images', 'zskeleton' )
									);
								},
							} )
						),
						slides.length
							? el(
								Button,
								{
									variant: 'tertiary',
									onClick: function () {
										props.setAttributes( { slides: [] } );
									},
								},
								__( 'Clear all images', 'zskeleton' )
							)
							: null
					),
					slides.map( function ( slide, index ) {
						return el(
							PanelBody,
							{
								key: 'slide-panel-' + slide.id,
								title: __( 'Image', 'zskeleton' ) + ' ' + ( index + 1 ),
								initialOpen: false,
							},
							el( TextControl, {
								label: __( 'Alt text (SEO)', 'zskeleton' ),
								value: slide.alt || '',
								onChange: function ( v ) {
									props.setAttributes( { slides: updateSlide( slides, index, { alt: v } ) } );
								},
							} ),
							el( TextControl, {
								label: __( 'Caption (optional)', 'zskeleton' ),
								value: slide.caption || '',
								onChange: function ( v ) {
									props.setAttributes( { slides: updateSlide( slides, index, { caption: v } ) } );
								},
							} ),
							el(
								'div',
								{ style: { display: 'flex', gap: '8px', flexWrap: 'wrap', marginTop: '8px' } },
								index > 0
									? el(
										Button,
										{
											variant: 'secondary',
											isSmall: true,
											onClick: function () {
												props.setAttributes( {
													slides: moveSlide( slides, index, index - 1 ),
												} );
											},
										},
										__( 'Move up', 'zskeleton' )
									)
									: null,
								index < slides.length - 1
									? el(
										Button,
										{
											variant: 'secondary',
											isSmall: true,
											onClick: function () {
												props.setAttributes( {
													slides: moveSlide( slides, index, index + 1 ),
												} );
											},
										},
										__( 'Move down', 'zskeleton' )
									)
									: null,
								el(
									Button,
									{
										variant: 'tertiary',
										isSmall: true,
										isDestructive: true,
										onClick: function () {
											var next = slides.slice();
											next.splice( index, 1 );
											props.setAttributes( { slides: next } );
										},
									},
									__( 'Remove', 'zskeleton' )
								)
							)
						);
					} ),
					el(
						PanelBody,
						{ title: __( 'Call to action', 'zskeleton' ), initialOpen: false },
						el( TextareaControl, {
							label: __( 'Text below carousel', 'zskeleton' ),
							value: attrs.ctaText || '',
							onChange: function ( v ) {
								props.setAttributes( { ctaText: v || '' } );
							},
							rows: 2,
						} ),
						el( TextControl, {
							label: __( 'Button label', 'zskeleton' ),
							value: attrs.buttonLabel || '',
							onChange: function ( v ) {
								props.setAttributes( { buttonLabel: v || '' } );
							},
						} ),
						el(
							'div',
							{ style: { marginBottom: '12px' } },
							el( 'p', { style: { marginBottom: '8px' } }, __( 'Button URL', 'zskeleton' ) ),
							el( URLInput, {
								value: attrs.buttonUrl || '',
								onChange: function ( v ) {
									props.setAttributes( { buttonUrl: v || '' } );
								},
							} )
						),
						el( SelectControl, {
							label: __( 'Button target', 'zskeleton' ),
							value: attrs.buttonTarget || '_self',
							options: [
								{ label: __( 'Same tab', 'zskeleton' ), value: '_self' },
								{ label: __( 'New tab', 'zskeleton' ), value: '_blank' },
							],
							onChange: function ( v ) {
								props.setAttributes( { buttonTarget: v || '_self' } );
							},
						} ),
						el( TextControl, {
							label: __( 'Link title attribute (SEO)', 'zskeleton' ),
							value: attrs.buttonTitleAttr || '',
							onChange: function ( v ) {
								props.setAttributes( { buttonTitleAttr: v || '' } );
							},
						} ),
						el( TextControl, {
							label: __( 'Button aria-label', 'zskeleton' ),
							value: attrs.buttonAriaLabel || '',
							onChange: function ( v ) {
								props.setAttributes( { buttonAriaLabel: v || '' } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Add rel="nofollow" to button link', 'zskeleton' ),
							checked: !!attrs.buttonRelNofollow,
							onChange: function ( v ) {
								props.setAttributes( { buttonRelNofollow: !!v } );
							},
						} ),
						el( ToggleControl, {
							label: __( 'Add rel="sponsored" to button link', 'zskeleton' ),
							checked: !!attrs.buttonRelSponsored,
							onChange: function ( v ) {
								props.setAttributes( { buttonRelSponsored: !!v } );
							},
						} )
					)
				),
				el(
					'div',
					blockProps,
					el( ServerSideRender, {
						block: 'zskeleton/testimonials-image-slider',
						attributes: attrs,
						httpMethod: 'POST',
					} )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp );
