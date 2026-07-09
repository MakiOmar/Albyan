/**
 * Editor: About company hero block.
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
	var RichText = wp.blockEditor.RichText;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var URLInput = wp.blockEditor.URLInput;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var Button = wp.components.Button;
	var ServerSideRender = wp.serverSideRender;

	function hexOr( value, fallback ) {
		if ( typeof value === 'string' && /^#[0-9A-Fa-f]{6}$/.test( value ) ) {
			return value;
		}
		return fallback;
	}

	function clamp( value, min, max, fallback ) {
		var n = parseInt( value, 10 );
		if ( isNaN( n ) ) {
			return fallback;
		}
		return Math.min( max, Math.max( min, n ) );
	}

	function colorField( attrs, setAttributes, id, label, attrKey, fallback ) {
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

	var existing = getBlockType( 'zskeleton/about-company-hero' );

	registerBlockType(
		'zskeleton/about-company-hero',
		Object.assign( {}, existing || {}, {
			edit: function ( props ) {
				var attrs = props.attributes || {};
				var setAttributes = props.setAttributes;
				var blockProps = useBlockProps( { className: 'zskeleton-about-company-hero-editor-root' } );

				function imageControl( title, imageKey, idKey, altKey ) {
					return createElement(
						PanelBody,
						{ title: title, initialOpen: false },
						createElement(
							MediaUploadCheck,
							null,
							createElement( MediaUpload, {
								onSelect: function ( media ) {
									var patch = {};
									patch[ idKey ] = media && media.id ? media.id : 0;
									patch[ imageKey ] = media && media.url ? media.url : '';
									patch[ altKey ] = media && media.alt ? media.alt : '';
									setAttributes( patch );
								},
								allowedTypes: [ 'image' ],
								value: attrs[ idKey ] || 0,
								render: function (obj) {
									return createElement(
										Button,
										{ variant: 'secondary', onClick: obj.open },
										attrs[ imageKey ] ? __( 'Replace image', 'zskeleton' ) : __( 'Select image', 'zskeleton' )
									);
								},
							} )
						),
						attrs[ imageKey ]
							? createElement(
								Button,
								{
									variant: 'tertiary',
									onClick: function () {
										var patch = {};
										patch[ idKey ] = 0;
										patch[ imageKey ] = '';
										patch[ altKey ] = '';
										setAttributes( patch );
									},
								},
								__( 'Remove image', 'zskeleton' )
							)
							: null,
						createElement( TextControl, {
							label: __( 'Image alt text', 'zskeleton' ),
							value: attrs[ altKey ] || '',
							onChange: function ( v ) {
								var patch = {};
								patch[ altKey ] = v;
								setAttributes( patch );
							},
						} )
					);
				}

				function buttonPanel( index, prefix ) {
					var labelKey = prefix + 'Label';
					var urlKey = prefix + 'Url';
					var targetKey = prefix + 'Target';
					return createElement(
						PanelBody,
						{ title: __( 'Button', 'zskeleton' ) + ' ' + index, initialOpen: false },
						createElement( TextControl, {
							label: __( 'Label', 'zskeleton' ),
							value: attrs[ labelKey ] || '',
							onChange: function ( v ) {
								var patch = {};
								patch[ labelKey ] = v;
								setAttributes( patch );
							},
						} ),
						createElement(
							'div',
							{ style: { marginBottom: '12px' } },
							createElement( 'p', { style: { marginBottom: '8px' } }, __( 'URL', 'zskeleton' ) ),
							createElement( URLInput, {
								value: attrs[ urlKey ] || '',
								onChange: function ( v ) {
									var patch = {};
									patch[ urlKey ] = v;
									setAttributes( patch );
								},
							} )
						),
						createElement( SelectControl, {
							label: __( 'Open in', 'zskeleton' ),
							value: attrs[ targetKey ] || '_self',
							options: [
								{ label: __( 'Same tab', 'zskeleton' ), value: '_self' },
								{ label: __( 'New tab', 'zskeleton' ), value: '_blank' },
							],
							onChange: function ( v ) {
								var patch = {};
								patch[ targetKey ] = v;
								setAttributes( patch );
							},
						} )
					);
				}

				return createElement(
					Fragment,
					null,
					createElement(
						InspectorControls,
						null,
						imageControl( __( 'Background image', 'zskeleton' ), 'backgroundImageUrl', 'backgroundImageId', 'backgroundImageAlt' ),
						imageControl( __( 'Profile image', 'zskeleton' ), 'profileImageUrl', 'profileImageId', 'profileImageAlt' ),
						createElement(
							PanelBody,
							{ title: __( 'Content', 'zskeleton' ), initialOpen: true },
							createElement( TextareaControl, {
								label: __( 'Short description', 'zskeleton' ),
								help: __( 'A short paragraph shown below the title.', 'zskeleton' ),
								value: attrs.description || '',
								onChange: function ( v ) {
									setAttributes( { description: v } );
								},
							} )
						),
						createElement(
							PanelBody,
							{ title: __( 'Background', 'zskeleton' ), initialOpen: false },
							createElement( SelectControl, {
								label: __( 'Background size', 'zskeleton' ),
								value: attrs.backgroundSize || 'cover',
								options: [
									{ label: __( 'Cover', 'zskeleton' ), value: 'cover' },
									{ label: __( 'Contain', 'zskeleton' ), value: 'contain' },
									{ label: __( 'Auto', 'zskeleton' ), value: 'auto' },
								],
								onChange: function ( v ) {
									setAttributes( { backgroundSize: v } );
								},
							} ),
							createElement( SelectControl, {
								label: __( 'Background position', 'zskeleton' ),
								value: attrs.backgroundPosition || 'center center',
								options: [
									{ label: __( 'Center Center', 'zskeleton' ), value: 'center center' },
									{ label: __( 'Top Center', 'zskeleton' ), value: 'center top' },
									{ label: __( 'Bottom Center', 'zskeleton' ), value: 'center bottom' },
									{ label: __( 'Center Left', 'zskeleton' ), value: 'left center' },
									{ label: __( 'Center Right', 'zskeleton' ), value: 'right center' },
								],
								onChange: function ( v ) {
									setAttributes( { backgroundPosition: v } );
								},
							} )
						),
						createElement(
							PanelBody,
							{ title: __( 'Title separator', 'zskeleton' ), initialOpen: false },
							createElement( ToggleControl, {
								label: __( 'Show separator under title', 'zskeleton' ),
								checked: attrs.titleShowSeparator !== false,
								onChange: function ( v ) {
									setAttributes( { titleShowSeparator: !!v } );
								},
							} ),
							createElement( RangeControl, {
								label: __( 'Separator width (px)', 'zskeleton' ),
								value: clamp( attrs.titleSeparatorWidthPx, 20, 400, 120 ),
								onChange: function ( v ) {
									setAttributes( { titleSeparatorWidthPx: v } );
								},
								min: 20,
								max: 400,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Separator height (px)', 'zskeleton' ),
								value: clamp( attrs.titleSeparatorHeightPx, 1, 24, 4 ),
								onChange: function ( v ) {
									setAttributes( { titleSeparatorHeightPx: v } );
								},
								min: 1,
								max: 24,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Separator radius (px)', 'zskeleton' ),
								value: clamp( attrs.titleSeparatorRadiusPx, 0, 999, 999 ),
								onChange: function ( v ) {
									setAttributes( { titleSeparatorRadiusPx: v } );
								},
								min: 0,
								max: 999,
								step: 1,
							} )
						),
						buttonPanel( 1, 'buttonOne' ),
						buttonPanel( 2, 'buttonTwo' ),
						createElement(
							PanelBody,
							{ title: __( 'Colors', 'zskeleton' ), initialOpen: false },
							colorField( attrs, setAttributes, 'ach-title-c', __( 'Title text', 'zskeleton' ), 'titleColor', '#101520' ),
							colorField( attrs, setAttributes, 'ach-desc-c', __( 'Description text', 'zskeleton' ), 'descriptionColor', '#2a2f37' ),
							colorField( attrs, setAttributes, 'ach-bg-c', __( 'Section background', 'zskeleton' ), 'sectionBackgroundColor', '#ececec' ),
							colorField( attrs, setAttributes, 'ach-pf-br', __( 'Profile border', 'zskeleton' ), 'profileBorderColor', '#eb6b2d' ),
							colorField( attrs, setAttributes, 'ach-sep-c', __( 'Title separator', 'zskeleton' ), 'titleSeparatorColor', '#eb6b2d' ),
							colorField( attrs, setAttributes, 'ach-b1-fg', __( 'Button 1 text', 'zskeleton' ), 'buttonOneTextColor', '#1b1f27' ),
							colorField( attrs, setAttributes, 'ach-b1-bg', __( 'Button 1 background', 'zskeleton' ), 'buttonOneBackgroundColor', '#f8f8f8' ),
							colorField( attrs, setAttributes, 'ach-b1-br', __( 'Button 1 border', 'zskeleton' ), 'buttonOneBorderColor', '#eb6b2d' ),
							colorField( attrs, setAttributes, 'ach-b2-fg', __( 'Button 2 text', 'zskeleton' ), 'buttonTwoTextColor', '#ffffff' ),
							colorField( attrs, setAttributes, 'ach-b2-bg', __( 'Button 2 background', 'zskeleton' ), 'buttonTwoBackgroundColor', '#eb6b2d' ),
							colorField( attrs, setAttributes, 'ach-b2-br', __( 'Button 2 border', 'zskeleton' ), 'buttonTwoBorderColor', '#eb6b2d' )
						)
					),
					createElement(
						'div',
						blockProps,
						createElement( RichText, {
							tagName: 'h2',
							className: 'zskeleton-about-company-hero-editor-title',
							placeholder: __( 'Add title...', 'zskeleton' ),
							value: attrs.title || '',
							onChange: function ( v ) {
								setAttributes( { title: v } );
							},
							allowedFormats: [],
						} ),
						createElement( RichText, {
							tagName: 'div',
							className: 'zskeleton-about-company-hero-editor-description',
							placeholder: __( 'Add short description...', 'zskeleton' ),
							value: attrs.description || '',
							onChange: function ( v ) {
								setAttributes( { description: v } );
							},
							allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
						} ),
						createElement( ServerSideRender, {
							block: 'zskeleton/about-company-hero',
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
