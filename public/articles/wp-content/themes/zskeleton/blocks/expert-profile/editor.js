/**
 * Editor registration for Expert Profile CTA block.
 *
 * @package ZSkeleton_Theme
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var __ = wp.i18n.__;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var Button = wp.components.Button;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var URLInput = wp.blockEditor.URLInput;
	var RichText = wp.blockEditor.RichText;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;

	/** Curated Dashicons (slug without `dashicons-` prefix) for the title row. */
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
		{ label: __( 'Products', 'zskeleton' ), value: 'products' },
		{ label: __( 'Analytics', 'zskeleton' ), value: 'analytics' },
		{ label: __( 'Clipboard', 'zskeleton' ), value: 'clipboard' },
		{ label: __( 'Lightbulb', 'zskeleton' ), value: 'lightbulb' },
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

	function normalizeButtons( buttons ) {
		var defaults = {
			label: '',
			url: '',
			target: '_self',
			relNoFollow: false,
			relSponsored: false,
			relUgc: false,
			ariaLabel: '',
			title: '',
		};
		var out = Array.isArray( buttons ) ? buttons.slice( 0, 3 ) : [];
		while ( out.length < 3 ) {
			out.push( Object.assign( {}, defaults ) );
		}
		return out.map( function ( item ) {
			return Object.assign( {}, defaults, item || {} );
		} );
	}

	function buildRel( item ) {
		var rel = [];
		if ( item.relNoFollow ) {
			rel.push( 'nofollow' );
		}
		if ( item.relSponsored ) {
			rel.push( 'sponsored' );
		}
		if ( item.relUgc ) {
			rel.push( 'ugc' );
		}
		if ( item.target === '_blank' ) {
			rel.push( 'noopener', 'noreferrer' );
		}
		return rel.join( ' ' );
	}

	function updateButton( buttons, index, nextData ) {
		var next = normalizeButtons( buttons );
		next[ index ] = Object.assign( {}, next[ index ], nextData || {} );
		return next;
	}

	function titleSeparatorStyle( attrs ) {
		var hex = attrs.titleSeparatorColor && /^#[0-9A-Fa-f]{6}$/.test( attrs.titleSeparatorColor )
			? attrs.titleSeparatorColor
			: '#b8d4eb';
		return {
			width: clampSepWidth( attrs.titleSeparatorWidthPx ) + 'px',
			height: clampSepHeight( attrs.titleSeparatorHeightPx ) + 'px',
			borderRadius: clampSepRadius( attrs.titleSeparatorRadiusPx ) + 'px',
			backgroundColor: hex,
		};
	}

	registerBlockType( 'zskeleton/expert-profile', {
		edit: function ( props ) {
			var attrs = props.attributes || {};
			var buttons = normalizeButtons( attrs.buttons );
			var blockProps = useBlockProps( {
				className: 'zskeleton-expert-profile',
			} );

			var imageEl = attrs.imageUrl
				? createElement( 'img', {
					src: attrs.imageUrl,
					alt: attrs.imageAlt || '',
					title: attrs.imageTitle || '',
					className: 'zskeleton-expert-profile__img',
				} )
				: createElement(
					'div',
					{ className: 'zskeleton-expert-profile__img-placeholder' },
					__( 'No image selected', 'zskeleton' )
				);

			var buttonPreview = buttons
				.filter( function (btn) {
					return btn.label && btn.url;
				} )
				.map( function ( btn, idx ) {
					var rel = buildRel( btn );
					return createElement(
						'a',
						{
							key: 'btn-preview-' + idx,
							className: 'zskeleton-expert-profile__btn',
							href: btn.url,
							target: btn.target || '_self',
							rel: rel || undefined,
							'aria-label': btn.ariaLabel || undefined,
							title: btn.title || undefined,
						},
						btn.label
					);
				} );

			return createElement(
				Fragment,
				null,
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{ title: __( 'Title appearance', 'zskeleton' ), initialOpen: true },
						createElement( SelectControl, {
							label: __( 'Title icon (Dashicon)', 'zskeleton' ),
							help: __(
								'Icon appears before the title text. Edit the title directly in the block preview.',
								'zskeleton'
							),
							value: attrs.titleDashicon || '',
							options: TITLE_DASHICON_OPTIONS,
							onChange: function ( v ) {
								props.setAttributes( { titleDashicon: v || '' } );
							},
						} ),
						createElement( RangeControl, {
							label: __( 'Separator width (px)', 'zskeleton' ),
							value: clampSepWidth( attrs.titleSeparatorWidthPx ),
							onChange: function ( v ) {
								props.setAttributes( { titleSeparatorWidthPx: v } );
							},
							min: 4,
							max: 480,
							step: 1,
						} ),
						createElement( RangeControl, {
							label: __( 'Separator height (px)', 'zskeleton' ),
							value: clampSepHeight( attrs.titleSeparatorHeightPx ),
							onChange: function ( v ) {
								props.setAttributes( { titleSeparatorHeightPx: v } );
							},
							min: 1,
							max: 64,
							step: 1,
						} ),
						createElement( RangeControl, {
							label: __( 'Separator border radius (px)', 'zskeleton' ),
							value: clampSepRadius( attrs.titleSeparatorRadiusPx ),
							onChange: function ( v ) {
								props.setAttributes( { titleSeparatorRadiusPx: v } );
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
									htmlFor: 'zskeleton-expert-profile-sep-color',
								},
								__( 'Separator color', 'zskeleton' )
							),
							createElement( 'input', {
								id: 'zskeleton-expert-profile-sep-color',
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
					createElement(
						PanelBody,
						{ title: __( 'Image + SEO', 'zskeleton' ), initialOpen: false },
						createElement(
							MediaUploadCheck,
							null,
							createElement( MediaUpload, {
								onSelect: function ( media ) {
									props.setAttributes( {
										imageId: media && media.id ? media.id : 0,
										imageUrl: media && media.url ? media.url : '',
										imageAlt: media && media.alt ? media.alt : '',
										imageTitle: media && media.title ? media.title : '',
									} );
								},
								allowedTypes: [ 'image' ],
								value: attrs.imageId || 0,
								render: function ( mediaProps ) {
									return createElement(
										Button,
										{
											variant: 'secondary',
											onClick: mediaProps.open,
										},
										attrs.imageUrl ? __( 'Replace image', 'zskeleton' ) : __( 'Select image', 'zskeleton' )
									);
								},
							} )
						),
						attrs.imageUrl
							? createElement(
								Button,
								{
									variant: 'tertiary',
									onClick: function () {
										props.setAttributes( {
											imageId: 0,
											imageUrl: '',
											imageAlt: '',
											imageTitle: '',
										} );
									},
								},
								__( 'Remove image', 'zskeleton' )
							)
							: null,
						createElement( TextControl, {
							label: __( 'Image alt text', 'zskeleton' ),
							value: attrs.imageAlt || '',
							onChange: function ( v ) {
								props.setAttributes( { imageAlt: v } );
							},
						} ),
						createElement( TextControl, {
							label: __( 'Image title attribute', 'zskeleton' ),
							value: attrs.imageTitle || '',
							onChange: function ( v ) {
								props.setAttributes( { imageTitle: v } );
							},
						} ),
						createElement( SelectControl, {
							label: __( 'Image loading', 'zskeleton' ),
							value: attrs.imageLoading || 'lazy',
							options: [
								{ label: __( 'Lazy', 'zskeleton' ), value: 'lazy' },
								{ label: __( 'Eager', 'zskeleton' ), value: 'eager' },
							],
							onChange: function ( v ) {
								props.setAttributes( { imageLoading: v } );
							},
						} ),
						createElement( SelectControl, {
							label: __( 'Image decoding', 'zskeleton' ),
							value: attrs.imageDecoding || 'async',
							options: [
								{ label: __( 'Async', 'zskeleton' ), value: 'async' },
								{ label: __( 'Auto', 'zskeleton' ), value: 'auto' },
								{ label: __( 'Sync', 'zskeleton' ), value: 'sync' },
							],
							onChange: function ( v ) {
								props.setAttributes( { imageDecoding: v } );
							},
						} )
					),
					buttons.map( function ( btn, index ) {
						return createElement(
							PanelBody,
							{
								key: 'panel-btn-' + index,
								title: __( 'Button', 'zskeleton' ) + ' ' + ( index + 1 ),
								initialOpen: false,
							},
							createElement( TextControl, {
								label: __( 'Label', 'zskeleton' ),
								value: btn.label || '',
								onChange: function ( v ) {
									props.setAttributes( { buttons: updateButton( buttons, index, { label: v } ) } );
								},
							} ),
							createElement(
								'div',
								{ style: { marginBottom: '12px' } },
								createElement( 'p', { style: { marginBottom: '8px' } }, __( 'URL', 'zskeleton' ) ),
								createElement( URLInput, {
									value: btn.url || '',
									onChange: function ( v ) {
										props.setAttributes( { buttons: updateButton( buttons, index, { url: v } ) } );
									},
								} )
							),
							createElement( SelectControl, {
								label: __( 'Target', 'zskeleton' ),
								value: btn.target || '_self',
								options: [
									{ label: __( 'Same tab', 'zskeleton' ), value: '_self' },
									{ label: __( 'New tab', 'zskeleton' ), value: '_blank' },
								],
								onChange: function ( v ) {
									props.setAttributes( { buttons: updateButton( buttons, index, { target: v } ) } );
								},
							} ),
							createElement( TextControl, {
								label: __( 'Link title attribute (SEO hint)', 'zskeleton' ),
								value: btn.title || '',
								onChange: function ( v ) {
									props.setAttributes( { buttons: updateButton( buttons, index, { title: v } ) } );
								},
							} ),
							createElement( TextControl, {
								label: __( 'Aria label (accessibility)', 'zskeleton' ),
								value: btn.ariaLabel || '',
								onChange: function ( v ) {
									props.setAttributes( { buttons: updateButton( buttons, index, { ariaLabel: v } ) } );
								},
							} ),
							createElement( ToggleControl, {
								label: __( 'Add rel="nofollow"', 'zskeleton' ),
								checked: !!btn.relNoFollow,
								onChange: function ( v ) {
									props.setAttributes( { buttons: updateButton( buttons, index, { relNoFollow: v } ) } );
								},
							} ),
							createElement( ToggleControl, {
								label: __( 'Add rel="sponsored"', 'zskeleton' ),
								checked: !!btn.relSponsored,
								onChange: function ( v ) {
									props.setAttributes( { buttons: updateButton( buttons, index, { relSponsored: v } ) } );
								},
							} ),
							createElement( ToggleControl, {
								label: __( 'Add rel="ugc"', 'zskeleton' ),
								checked: !!btn.relUgc,
								onChange: function ( v ) {
									props.setAttributes( { buttons: updateButton( buttons, index, { relUgc: v } ) } );
								},
							} )
						);
					} )
				),
				createElement(
					'section',
					blockProps,
					createElement(
						'div',
						{ className: 'zskeleton-expert-profile__media' },
						imageEl
					),
					createElement(
						'div',
						{ className: 'zskeleton-expert-profile__content' },
						createElement(
							'div',
							{ className: 'zskeleton-expert-profile__title-head' },
							attrs.titleDashicon
								? createElement( 'span', {
									className:
										'zskeleton-expert-profile__title-icon dashicons dashicons-' +
										attrs.titleDashicon,
									'aria-hidden': 'true',
								} )
								: null,
							createElement(
								'div',
								{ className: 'zskeleton-expert-profile__title-text-wrap' },
								createElement(
									RichText,
									{
										tagName: 'h2',
										className: 'zskeleton-expert-profile__title',
										placeholder: __( 'Add title...', 'zskeleton' ),
										value: attrs.title || '',
										onChange: function ( v ) {
											props.setAttributes( { title: v } );
										},
										allowedFormats: [],
									}
								),
								createElement( 'span', {
									className: 'zskeleton-expert-profile__title-separator',
									style: titleSeparatorStyle( attrs ),
									'aria-hidden': 'true',
								} )
							)
						),
						createElement(
							RichText,
							{
								tagName: 'div',
								className: 'zskeleton-expert-profile__description',
								placeholder: __( 'Add description (WYSIWYG)...', 'zskeleton' ),
								value: attrs.description || '',
								onChange: function ( v ) {
									props.setAttributes( { description: v } );
								},
								allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
							}
						),
						createElement(
							'div',
							{ className: 'zskeleton-expert-profile__buttons' },
							buttonPreview.length
								? buttonPreview
								: createElement(
									'p',
									{ className: 'zskeleton-expert-profile__empty' },
									__( 'Add at least one button label + URL from sidebar settings.', 'zskeleton' )
								)
						)
					)
				)
			);
		},
		save: function ( props ) {
			var attrs = props.attributes || {};
			var buttons = normalizeButtons( attrs.buttons ).filter( function ( btn ) {
				return btn.label && btn.url;
			} );
			var blockProps = wp.blockEditor.useBlockProps.save( {
				className: 'zskeleton-expert-profile',
			} );

			return createElement(
				'section',
				blockProps,
				createElement(
					'div',
					{ className: 'zskeleton-expert-profile__media' },
					attrs.imageUrl
						? createElement( 'img', {
							className: 'zskeleton-expert-profile__img',
							src: attrs.imageUrl,
							alt: attrs.imageAlt || '',
							title: attrs.imageTitle || undefined,
							loading: attrs.imageLoading || 'lazy',
							decoding: attrs.imageDecoding || 'async',
						} )
						: null
				),
				createElement(
					'div',
					{ className: 'zskeleton-expert-profile__content' },
					attrs.title || attrs.titleDashicon
						? createElement(
							'div',
							{ className: 'zskeleton-expert-profile__title-head' },
							attrs.titleDashicon
								? createElement( 'span', {
									className:
										'zskeleton-expert-profile__title-icon dashicons dashicons-' +
										attrs.titleDashicon,
									'aria-hidden': 'true',
								} )
								: null,
							createElement(
								'div',
								{ className: 'zskeleton-expert-profile__title-text-wrap' },
								attrs.title
									? createElement( 'h2', {
										className: 'zskeleton-expert-profile__title',
										dangerouslySetInnerHTML: { __html: attrs.title },
									} )
									: null,
								createElement( 'span', {
									className: 'zskeleton-expert-profile__title-separator',
									style: titleSeparatorStyle( attrs ),
									'aria-hidden': 'true',
								} )
							)
						)
						: null,
					attrs.description
						? createElement( 'div', {
							className: 'zskeleton-expert-profile__description',
							dangerouslySetInnerHTML: { __html: attrs.description },
						} )
						: null,
					buttons.length
						? createElement(
							'div',
							{ className: 'zskeleton-expert-profile__buttons' },
							buttons.map( function ( btn, index ) {
								var rel = buildRel( btn );
								return createElement(
									'a',
									{
										key: 'save-btn-' + index,
										className: 'zskeleton-expert-profile__btn',
										href: btn.url,
										target: btn.target || '_self',
										rel: rel || undefined,
										'aria-label': btn.ariaLabel || undefined,
										title: btn.title || undefined,
									},
									btn.label
								);
							} )
						)
						: null
				)
			);
		},
	} );
} )( window.wp );
