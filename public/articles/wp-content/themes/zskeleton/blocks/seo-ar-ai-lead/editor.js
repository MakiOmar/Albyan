/**
 * Block editor: Arabic SEO AI lead section (dynamic render + inspector).
 *
 * @package ZSkeleton_Theme
 */
( function ( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var unregisterBlockType = wp.blocks.unregisterBlockType;
	var getBlockType = wp.blocks.getBlockType;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var RichText = wp.blockEditor.RichText;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var ToggleControl = wp.components.ToggleControl;
	var SelectControl = wp.components.SelectControl;
	var Button = wp.components.Button;
	var ServerSideRender = wp.serverSideRender;

	var BLOCK = 'zskeleton/seo-ar-ai-lead';

	var SEP_STYLE_OPTIONS = [
		{ label: __( 'None', 'zskeleton' ), value: 'none' },
		{ label: __( 'Accent line', 'zskeleton' ), value: 'line' },
		{ label: __( 'Gradient bar', 'zskeleton' ), value: 'gradient' },
		{ label: __( 'Dot rhythm', 'zskeleton' ), value: 'dots' },
		{ label: __( 'Text / symbol', 'zskeleton' ), value: 'character' },
	];

	var SEP_ALIGN_OPTIONS = [
		{ label: __( 'Start (inline with title edge)', 'zskeleton' ), value: 'start' },
		{ label: __( 'Center', 'zskeleton' ), value: 'center' },
		{ label: __( 'Full width', 'zskeleton' ), value: 'full' },
	];

	var ICON_TYPE_OPTIONS = [
		{ label: __( 'Dashicon', 'zskeleton' ), value: 'dashicon' },
		{ label: __( 'Image', 'zskeleton' ), value: 'image' },
	];

	function SeoArAiLeadEdit( props ) {
		var a = props.attributes || {};
		var setAttributes = props.setAttributes;
		var blockProps = useBlockProps( { className: 'zskeleton-seo-ar-ai-lead-block-edit' } );
		var body = a.content || a.introHtml || '';
		var sepStyle = a.titleSeparatorStyle || 'line';
		var iconType = a.titleIconType || 'dashicon';
		var sepColorValue = a.titleSeparatorColor && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( a.titleSeparatorColor )
			? a.titleSeparatorColor
			: '#0b5f97';

		return createElement(
			Fragment,
			null,
			createElement(
				InspectorControls,
				null,
				createElement(
					PanelBody,
					{ title: __( 'Title', 'zskeleton' ), initialOpen: true },
					createElement( TextControl, {
						label: __( 'Title (H2)', 'zskeleton' ),
						value: a.title || '',
						onChange: function ( v ) {
							setAttributes( { title: v } );
						},
					} ),
					createElement( SelectControl, {
						label: __( 'Title separator', 'zskeleton' ),
						help: __(
							'Visual divider under the heading (replaces the old full-width section border on this band).',
							'zskeleton'
						),
						value: sepStyle,
						options: SEP_STYLE_OPTIONS,
						onChange: function ( v ) {
							setAttributes( { titleSeparatorStyle: v } );
						},
					} ),
					'none' !== sepStyle
						? createElement(
							Fragment,
							null,
							createElement( SelectControl, {
								label: __( 'Separator width / alignment', 'zskeleton' ),
								value: a.titleSeparatorAlign || 'start',
								options: SEP_ALIGN_OPTIONS,
								onChange: function ( v ) {
									setAttributes( { titleSeparatorAlign: v } );
								},
							} ),
							createElement(
								'div',
								{ className: 'zskeleton-seo-ar-ai-lead-sep-color' },
								createElement(
									'label',
									{ className: 'components-base-control__label', htmlFor: 'zskeleton-seo-ar-ai-lead-sep-color-input' },
									__( 'Separator color', 'zskeleton' )
								),
								createElement( 'input', {
									id: 'zskeleton-seo-ar-ai-lead-sep-color-input',
									type: 'color',
									value: sepColorValue,
									onChange: function ( e ) {
										setAttributes( { titleSeparatorColor: e.target.value } );
									},
									'aria-label': __( 'Separator color', 'zskeleton' ),
								} ),
								createElement(
									Button,
									{
										variant: 'link',
										onClick: function () {
											setAttributes( { titleSeparatorColor: '' } );
										},
									},
									__( 'Use theme default', 'zskeleton' )
								)
							),
							'character' === sepStyle
								? createElement( TextControl, {
									label: __( 'Separator text / symbol', 'zskeleton' ),
									value: a.titleSeparatorCharacter || '—',
									onChange: function ( v ) {
										setAttributes( { titleSeparatorCharacter: v } );
									},
								} )
								: null
						)
						: null,
					createElement( ToggleControl, {
						label: __( 'Show icon before title', 'zskeleton' ),
						checked: !! a.titleIconEnabled,
						onChange: function ( v ) {
							setAttributes( { titleIconEnabled: v } );
						},
					} ),
					a.titleIconEnabled
						? createElement(
							Fragment,
							null,
							createElement( SelectControl, {
								label: __( 'Icon type', 'zskeleton' ),
								value: iconType,
								options: ICON_TYPE_OPTIONS,
								onChange: function ( v ) {
									setAttributes( { titleIconType: v } );
								},
							} ),
							'dashicon' === iconType || ( 'image' === iconType && ! ( a.titleIconImageId > 0 ) )
								? createElement( TextControl, {
									label: __( 'Dashicon slug', 'zskeleton' ),
									help: __( 'Name without the dashicons- prefix (e.g. chart-line, megaphone).', 'zskeleton' ),
									value: a.titleIconDashicon || 'chart-line',
									onChange: function ( v ) {
										setAttributes( { titleIconDashicon: v } );
									},
								} )
								: null,
							'image' === iconType
								? createElement(
									MediaUploadCheck,
									null,
									createElement( MediaUpload, {
										onSelect: function ( media ) {
											if ( media && media.id ) {
												setAttributes( { titleIconImageId: media.id } );
											}
										},
										allowedTypes: [ 'image' ],
										value: a.titleIconImageId || 0,
										render: function ( obj ) {
											return createElement(
												'div',
												{ className: 'zskeleton-seo-ar-ai-lead-icon-media' },
												createElement(
													Button,
													{
														variant: a.titleIconImageId ? 'secondary' : 'primary',
														onClick: obj.open,
													},
													a.titleIconImageId
														? __( 'Replace title icon image', 'zskeleton' )
														: __( 'Choose title icon image', 'zskeleton' )
												),
												a.titleIconImageId
													? createElement(
														Button,
														{
															isDestructive: true,
															variant: 'link',
															onClick: function () {
																setAttributes( { titleIconImageId: 0 } );
															},
														},
														__( 'Remove image', 'zskeleton' )
													)
													: null
											);
										},
									} )
								)
								: null
						)
						: null
				),
				createElement(
					PanelBody,
					{ title: __( 'Body', 'zskeleton' ), initialOpen: true },
					createElement( TextareaControl, {
						label: __( 'Body (HTML)', 'zskeleton' ),
						help: sprintf(
							/* translators: 1: CASE_STUDY_URL token, 2: SITE_NAME token, 3: EXPERT_NAME token (each includes percent delimiters). */
							__(
								'Edit HTML here or in the rich text box in the canvas. Optional merge tokens (copy exactly): %1$s, %2$s, %3$s.',
								'zskeleton'
							),
							'%%CASE_STUDY_URL%%',
							'%%SITE_NAME%%',
							'%%EXPERT_NAME%%'
						),
						value: body,
						onChange: function ( v ) {
							setAttributes( { content: v } );
						},
						rows: 14,
						className: 'zskeleton-seo-ar-ai-lead-body-textarea',
					} ),
					createElement(
						'p',
						{ className: 'components-base-control__help' },
						__( 'The preview below updates from this field. Tokens are replaced on the published page.', 'zskeleton' )
					)
				),
				createElement(
					PanelBody,
					{ title: __( 'Layout & accessibility', 'zskeleton' ), initialOpen: false },
					createElement( TextControl, {
						label: __( 'Section HTML id (anchor)', 'zskeleton' ),
						help: __( 'Letters, digits, hyphen, underscore only. Default: ai-lead.', 'zskeleton' ),
						value: a.sectionHtmlId || 'ai-lead',
						onChange: function ( v ) {
							setAttributes( { sectionHtmlId: v } );
						},
					} ),
					createElement( TextControl, {
						label: __( 'Title HTML id (aria-labelledby)', 'zskeleton' ),
						value: a.headingWrapperId || '',
						onChange: function ( v ) {
							setAttributes( { headingWrapperId: v } );
						},
					} ),
					createElement( TextControl, {
						label: __( 'Section aria-label (optional)', 'zskeleton' ),
						value: a.sectionAriaLabel || '',
						onChange: function ( v ) {
							setAttributes( { sectionAriaLabel: v } );
						},
					} ),
					createElement( TextControl, {
						label: __( 'Inner container class', 'zskeleton' ),
						help: __(
							'Leave empty to use the same container class as the Arabic SEO homepage template for this page.',
							'zskeleton'
						),
						value: a.innerContainerClass || '',
						onChange: function ( v ) {
							setAttributes( { innerContainerClass: v } );
						},
					} )
				),
				createElement(
					PanelBody,
					{ title: __( 'SEO — structured data', 'zskeleton' ), initialOpen: false },
					createElement( ToggleControl, {
						label: __( 'Output WebPageElement JSON-LD for this section', 'zskeleton' ),
						checked: !! a.structuredDataEnabled,
						onChange: function ( v ) {
							setAttributes( { structuredDataEnabled: v } );
						},
					} ),
					createElement( TextareaControl, {
						label: __( 'Schema description (plain text, optional)', 'zskeleton' ),
						help: __( 'Used in JSON-LD; when empty, a short excerpt is taken from the body HTML.', 'zskeleton' ),
						value: a.schemaDescription || '',
						onChange: function ( v ) {
							setAttributes( { schemaDescription: v } );
						},
						rows: 3,
					} ),
					createElement( ToggleControl, {
						label: __( 'Output SpeakableSpecification JSON-LD', 'zskeleton' ),
						help: __(
							'Optional; enable only if it fits your overall SEO strategy (avoid duplicate speakable markup).',
							'zskeleton'
						),
						checked: !! a.speakableJsonLdEnabled,
						onChange: function ( v ) {
							setAttributes( { speakableJsonLdEnabled: v } );
						},
					} ),
					createElement( TextareaControl, {
						label: __( 'Speakable CSS selectors', 'zskeleton' ),
						help: __( 'One per line or comma-separated (e.g. #seo-ar-ai-lead-heading).', 'zskeleton' ),
						value: a.speakableCssSelectors || '',
						onChange: function ( v ) {
							setAttributes( { speakableCssSelectors: v } );
						},
						rows: 3,
					} )
				)
			),
			createElement(
				'div',
				blockProps,
				createElement( RichText, {
					tagName: 'div',
					className: 'seo-ar-ai-lead-body seo-ar-lead-text seo-ar-ai-lead-body--rich-editor',
					value: body,
					onChange: function ( v ) {
						setAttributes( { content: v } );
					},
					allowedFormats: [
						'core/bold',
						'core/italic',
						'core/link',
						'core/text-color',
						'core/strikethrough',
						'core/list',
						'core/ordered-list',
					],
					placeholder: __( 'Lead body…', 'zskeleton' ),
				} ),
				createElement( ServerSideRender, {
					block: BLOCK,
					attributes: props.attributes,
					httpMethod: 'POST',
				} )
			)
		);
	}

	var existing = getBlockType( BLOCK );
	if ( existing ) {
		unregisterBlockType( BLOCK );
	}
	registerBlockType(
		BLOCK,
		Object.assign( {}, existing || {}, {
			apiVersion: 3,
			edit: SeoArAiLeadEdit,
			save: function () {
				return null;
			},
		} )
	);
} )( window.wp );
