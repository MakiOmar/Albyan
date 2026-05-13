/**
 * Editor: Contact form block (inspector + ServerSideRender).
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
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender;

	var existing = getBlockType( 'zskeleton/contact-form' );

	registerBlockType(
		'zskeleton/contact-form',
		Object.assign( {}, existing || {}, {
			edit: function ( props ) {
				var attrs = props.attributes || {};
				var setAttributes = props.setAttributes;
				var blockProps = useBlockProps( { className: 'zs-contact-form-block-editor-root' } );

				return createElement(
					Fragment,
					null,
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __( 'Contact form', 'zskeleton' ), initialOpen: true },
							createElement( ToggleControl, {
								label: __( 'Show title and introduction', 'zskeleton' ),
								checked: attrs.showHeading !== false,
								onChange: function ( v ) {
									setAttributes( { showHeading: v } );
								},
							} ),
							attrs.showHeading !== false
								? createElement(
									Fragment,
									null,
									createElement( TextControl, {
										label: __( 'Heading', 'zskeleton' ),
										help: __(
											'Leave empty to use the default heading (same as the Contact page).',
											'zskeleton'
										),
										value: attrs.heading || '',
										onChange: function ( v ) {
											setAttributes( { heading: v } );
										},
									} ),
									createElement( TextControl, {
										label: __( 'Introduction', 'zskeleton' ),
										help: __(
											'Leave empty to use the default introduction (same as the Contact page).',
											'zskeleton'
										),
										value: attrs.lead || '',
										onChange: function ( v ) {
											setAttributes( { lead: v } );
										},
									} )
								)
								: null
						)
					),
					createElement(
						'div',
						blockProps,
						createElement( ServerSideRender, {
							block: 'zskeleton/contact-form',
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
