/**
 * Editor: Stepper block.
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
	var RangeControl = wp.components.RangeControl;
	var ToggleControl = wp.components.ToggleControl;
	var SelectControl = wp.components.SelectControl;
	var Button = wp.components.Button;
	var ServerSideRender = wp.serverSideRender;

	function hexOr( value, fallback ) {
		return typeof value === 'string' && /^#[0-9A-Fa-f]{6}$/.test( value ) ? value : fallback;
	}

	function normalizeSteps( steps ) {
		if ( ! Array.isArray( steps ) || ! steps.length ) {
			return [
				{ label: 'Step 1', number: '1', accentColor: '#facc15' },
				{ label: 'Step 2', number: '2', accentColor: '#ec4899' },
				{ label: 'Step 3', number: '3', accentColor: '#06b6d4' },
				{ label: 'Step 4', number: '4', accentColor: '#d1d5db' },
			];
		}
		return steps.map( function ( step, index ) {
			var row = step && typeof step === 'object' ? step : {};
			return {
				label: typeof row.label === 'string' ? row.label : '',
				number: typeof row.number === 'string' ? row.number : String( index + 1 ),
				accentColor: hexOr( row.accentColor, '#d1d5db' ),
			};
		} );
	}

	function colorField( id, label, attrs, key, fallback, setAttributes ) {
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
				value: hexOr( attrs[ key ], fallback ),
				onChange: function ( event ) {
					var patch = {};
					patch[ key ] = event.target.value;
					setAttributes( patch );
				},
				'aria-label': label,
				style: { width: '100%', maxWidth: '120px', height: '32px', cursor: 'pointer' },
			} )
		);
	}

	var existingStepper = getBlockType( 'zskeleton/stepper' );

	registerBlockType(
		'zskeleton/stepper',
		Object.assign( {}, existingStepper || {}, {
			edit: function ( props ) {
				var attrs = props.attributes || {};
				var setAttributes = props.setAttributes;
				var steps = normalizeSteps( attrs.steps );
				var blockProps = useBlockProps( { className: 'zskeleton-stepper-editor-root' } );

				function updateStep( index, key, value ) {
					var next = steps.slice();
					next[ index ] = Object.assign( {}, next[ index ], ( function () {
						var patch = {};
						patch[ key ] = value;
						return patch;
					} )() );
					setAttributes( { steps: next } );
				}

				function addStep() {
					if ( steps.length >= 10 ) {
						return;
					}
					setAttributes( {
						steps: steps.concat( [
							{
								label: '',
								number: String( steps.length + 1 ),
								accentColor: '#d1d5db',
							},
						] ),
					} );
				}

				function removeStep( index ) {
					if ( steps.length <= 1 ) {
						return;
					}
					setAttributes( {
						steps: steps.filter( function ( _row, i ) {
							return i !== index;
						} ),
					} );
				}

				return createElement(
					Fragment,
					null,
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __( 'Style', 'zskeleton' ), initialOpen: true },
							createElement( SelectControl, {
								label: __( 'Variant', 'zskeleton' ),
								value: attrs.styleVariant || 'style-1',
								options: [ { label: __( 'Style 1', 'zskeleton' ), value: 'style-1' } ],
								onChange: function ( value ) {
									setAttributes( { styleVariant: value || 'style-1' } );
								},
							} ),
							createElement( RangeControl, {
								label: __( 'Current step (active)', 'zskeleton' ),
								value: parseInt( attrs.currentStep, 10 ) || 1,
								onChange: function ( value ) {
									setAttributes( { currentStep: parseInt( value, 10 ) || 1 } );
								},
								min: 1,
								max: Math.max( 1, steps.length ),
								step: 1,
							} ),
							createElement( ToggleControl, {
								label: __( 'Vertical on mobile', 'zskeleton' ),
								checked: attrs.mobileVertical !== false,
								onChange: function ( value ) {
									setAttributes( { mobileVertical: !!value } );
								},
							} ),
							createElement( RangeControl, {
								label: __( 'Mobile breakpoint (px)', 'zskeleton' ),
								value: parseInt( attrs.mobileBreakpointPx, 10 ) || 782,
								onChange: function ( value ) {
									setAttributes( { mobileBreakpointPx: parseInt( value, 10 ) || 782 } );
								},
								min: 320,
								max: 1280,
								step: 1,
							} )
						),
						createElement(
							PanelBody,
							{ title: __( 'Steps', 'zskeleton' ), initialOpen: true },
							steps.map( function ( step, index ) {
								return createElement(
									'div',
									{
										key: 'step-' + index,
										style: {
											border: '1px solid #e5e7eb',
											borderRadius: '6px',
											padding: '10px',
											marginBottom: '10px',
										},
									},
									createElement( TextControl, {
										label: __( 'Label', 'zskeleton' ) + ' #' + ( index + 1 ),
										value: step.label,
										onChange: function ( value ) {
											updateStep( index, 'label', value );
										},
									} ),
									createElement( TextControl, {
										label: __( 'Circle text/number', 'zskeleton' ),
										value: step.number,
										onChange: function ( value ) {
											updateStep( index, 'number', value );
										},
									} ),
									createElement(
										'div',
										{ className: 'components-base-control', style: { marginBottom: '10px' } },
										createElement(
											'label',
											{
												className: 'components-base-control__label',
												htmlFor: 'zs-step-accent-' + index,
											},
											__( 'Accent color', 'zskeleton' )
										),
										createElement( 'input', {
											id: 'zs-step-accent-' + index,
											type: 'color',
											value: hexOr( step.accentColor, '#d1d5db' ),
											onChange: function ( event ) {
												updateStep( index, 'accentColor', event.target.value );
											},
											'aria-label': __( 'Accent color', 'zskeleton' ),
											style: { width: '100%', maxWidth: '120px', height: '32px', cursor: 'pointer' },
										} )
									),
									createElement(
										Button,
										{
											variant: 'secondary',
											isDestructive: true,
											onClick: function () {
												removeStep( index );
											},
										},
										__( 'Remove step', 'zskeleton' )
									)
								);
							} ),
							createElement(
								Button,
								{ variant: 'secondary', onClick: addStep },
								__( 'Add step', 'zskeleton' )
							)
						),
						createElement(
							PanelBody,
							{ title: __( 'Layout', 'zskeleton' ), initialOpen: false },
							createElement( RangeControl, {
								label: __( 'Max width (px)', 'zskeleton' ),
								value: parseInt( attrs.maxWidthPx, 10 ) || 980,
								onChange: function ( value ) {
									setAttributes( { maxWidthPx: parseInt( value, 10 ) || 980 } );
								},
								min: 320,
								max: 1600,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Vertical padding (px)', 'zskeleton' ),
								value: parseInt( attrs.sectionPaddingYpx, 10 ) || 18,
								onChange: function ( value ) {
									setAttributes( { sectionPaddingYpx: parseInt( value, 10 ) || 18 } );
								},
								min: 0,
								max: 120,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Horizontal padding (px)', 'zskeleton' ),
								value: parseInt( attrs.sectionPaddingXpx, 10 ) || 18,
								onChange: function ( value ) {
									setAttributes( { sectionPaddingXpx: parseInt( value, 10 ) || 18 } );
								},
								min: 0,
								max: 120,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Gap between steps (px)', 'zskeleton' ),
								value: parseInt( attrs.itemGapPx, 10 ) || 26,
								onChange: function ( value ) {
									setAttributes( { itemGapPx: parseInt( value, 10 ) || 26 } );
								},
								min: 0,
								max: 120,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Connector thickness (px)', 'zskeleton' ),
								value: parseInt( attrs.lineThicknessPx, 10 ) || 2,
								onChange: function ( value ) {
									setAttributes( { lineThicknessPx: parseInt( value, 10 ) || 2 } );
								},
								min: 1,
								max: 12,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Circle size (px)', 'zskeleton' ),
								value: parseInt( attrs.circleSizePx, 10 ) || 34,
								onChange: function ( value ) {
									setAttributes( { circleSizePx: parseInt( value, 10 ) || 34 } );
								},
								min: 18,
								max: 80,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Title font size (px)', 'zskeleton' ),
								value: parseInt( attrs.titleFontSizePx, 10 ) || 14,
								onChange: function ( value ) {
									setAttributes( { titleFontSizePx: parseInt( value, 10 ) || 14 } );
								},
								min: 10,
								max: 42,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Gap above title (px)', 'zskeleton' ),
								value: parseInt( attrs.titleGapTopPx, 10 ) || 10,
								onChange: function ( value ) {
									setAttributes( { titleGapTopPx: parseInt( value, 10 ) || 10 } );
								},
								min: 0,
								max: 48,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Underline width (px)', 'zskeleton' ),
								value: parseInt( attrs.underlineWidthPx, 10 ) || 52,
								onChange: function ( value ) {
									setAttributes( { underlineWidthPx: parseInt( value, 10 ) || 52 } );
								},
								min: 8,
								max: 240,
								step: 1,
							} ),
							createElement( RangeControl, {
								label: __( 'Underline height (px)', 'zskeleton' ),
								value: parseInt( attrs.underlineHeightPx, 10 ) || 3,
								onChange: function ( value ) {
									setAttributes( { underlineHeightPx: parseInt( value, 10 ) || 3 } );
								},
								min: 1,
								max: 16,
								step: 1,
							} )
						),
						createElement(
							PanelBody,
							{ title: __( 'Colors', 'zskeleton' ), initialOpen: false },
							colorField( 'zs-step-section-bg', __( 'Section background', 'zskeleton' ), attrs, 'sectionBackground', '#ffffff', setAttributes ),
							colorField( 'zs-step-circle-bg', __( 'Circle background', 'zskeleton' ), attrs, 'circleBackground', '#f3f4f6', setAttributes ),
							colorField( 'zs-step-circle-fg', __( 'Circle text', 'zskeleton' ), attrs, 'circleTextColor', '#4b5563', setAttributes ),
							colorField( 'zs-step-circle-bg-a', __( 'Active circle background', 'zskeleton' ), attrs, 'activeCircleBackground', '#e5e7eb', setAttributes ),
							colorField( 'zs-step-circle-fg-a', __( 'Active circle text', 'zskeleton' ), attrs, 'activeCircleTextColor', '#111827', setAttributes ),
							colorField( 'zs-step-text', __( 'Step title text', 'zskeleton' ), attrs, 'stepTextColor', '#111827', setAttributes ),
							colorField( 'zs-step-text-a', __( 'Active step title text', 'zskeleton' ), attrs, 'activeStepTextColor', '#111827', setAttributes ),
							colorField( 'zs-step-underline', __( 'Underline color', 'zskeleton' ), attrs, 'underlineColor', '#cbd5e1', setAttributes ),
							colorField( 'zs-step-underline-a', __( 'Active underline color', 'zskeleton' ), attrs, 'activeUnderlineColor', '#94a3b8', setAttributes ),
							colorField( 'zs-step-connector', __( 'Default connector color', 'zskeleton' ), attrs, 'connectorColor', '#d1d5db', setAttributes )
						)
					),
					createElement(
						'div',
						blockProps,
						createElement( ServerSideRender, {
							block: 'zskeleton/stepper',
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
