/**
 * Client registration + inspector controls for ZSkeleton blog dynamic blocks (no build step).
 *
 * Server bootstraps block metadata; the editor script attaches `edit` + `registerBlockType`
 * and inspector panels. Unregister/merge keeps `attributes` and metadata from `block.json`.
 *
 * @package ZSkeleton_Theme
 */
(function (wp) {
	var registerBlockType = wp.blocks.registerBlockType;
	var unregisterBlockType = wp.blocks.unregisterBlockType;
	var getBlockType = wp.blocks.getBlockType;
	var addFilter = wp.hooks.addFilter;
	var createElement = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var ToggleControl = wp.components.ToggleControl;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var RangeControl = wp.components.RangeControl;
	var CheckboxControl = wp.components.CheckboxControl;
	var Spinner = wp.components.Spinner;
	var Button = wp.components.Button;
	var ServerSideRender = wp.serverSideRender;
	var useSelect = wp.data.useSelect;
	var __ = wp.i18n.__;

	var CATEGORY_HANDPICK_QUERY = {
		per_page: -1,
		orderby: 'name',
		order: 'asc',
		hide_empty: false,
	};

	/** Hand-pick post categories for the Blog: Categories block (empty = theme “top by count” behaviour). */
	function BlogCategoryTermsHandPickPanel(props) {
		var a = props.attributes || {};
		var setAttributes = props.setAttributes;
		var categoryIds = Array.isArray(a.categoryIds)
			? a.categoryIds.map(function (x) {
					return parseInt(x, 10);
				}).filter(function (n) {
					return !isNaN(n) && n > 0;
				})
			: [];
		var categories = useSelect(
			function (select) {
				return select('core').getEntityRecords('taxonomy', 'category', CATEGORY_HANDPICK_QUERY);
			},
			[]
		);
		var children = [];
		children.push(
			createElement(
				'p',
				{ className: 'components-base-control__help', key: 'help' },
				__(
					'Leave all unchecked to list top-level categories by post count (theme Content setting). When you check any, only those categories are shown, sorted A–Z, up to the “Number of categories” limit above (0 uses the theme default). Categories with no posts are hidden on the front.',
					'zskeleton'
				)
			)
		);
		if (!Array.isArray(categories)) {
			children.push(
				createElement(
					'div',
					{ key: 'spin', style: { display: 'flex', justifyContent: 'center', padding: '1rem 0' } },
					createElement(Spinner, null)
				)
			);
			return createElement(Fragment, null, children);
		}
		if (categories.length === 0) {
			children.push(
				createElement(
					'p',
					{ key: 'none', className: 'components-notice is-warning' },
					__('No categories found.', 'zskeleton')
				)
			);
			return createElement(Fragment, null, children);
		}
		var toggleId = function (id) {
			var n = parseInt(id, 10);
			if (isNaN(n) || n < 1) {
				return;
			}
			var idx = categoryIds.indexOf(n);
			var next = idx >= 0 ? categoryIds.filter(function (x) { return x !== n; }) : categoryIds.concat([n]);
			setAttributes({ categoryIds: next });
		};
		var list = createElement(
			'div',
			{
				key: 'list',
				className: 'zskeleton-blog-category-terms-handpick',
				style: { maxHeight: '280px', overflowY: 'auto', marginTop: '0.5rem' },
			},
			categories.map(function (term) {
				var tid = parseInt(term.id, 10);
				return createElement(CheckboxControl, {
					key: 'cat-' + tid,
					label: term.name,
					checked: categoryIds.indexOf(tid) !== -1,
					onChange: function () {
						toggleId(tid);
					},
				});
			})
		);
		children.push(list);
		if (categoryIds.length > 0) {
			children.push(
				createElement(
					Button,
					{
						key: 'clear',
						variant: 'secondary',
						style: { marginTop: '0.75rem' },
						onClick: function () {
							setAttributes({ categoryIds: [] });
						},
					},
					__('Clear hand-picked categories', 'zskeleton')
				)
			);
		}
		return createElement(Fragment, null, children);
	}

	/** Title row (Dashicon + accent bar) options for Blog: Latest posts grid — same set as testimonials / Expert-style blocks. */
	var BLOG_GRID_TITLE_DASHICON_OPTIONS = [
		{ label: __('None', 'zskeleton'), value: '' },
		{ label: __('User', 'zskeleton'), value: 'admin-users' },
		{ label: __('ID card', 'zskeleton'), value: 'id' },
		{ label: __('ID (alt)', 'zskeleton'), value: 'id-alt' },
		{ label: __('Business', 'zskeleton'), value: 'businessman' },
		{ label: __('Nametag', 'zskeleton'), value: 'nametag' },
		{ label: __('Star', 'zskeleton'), value: 'star-filled' },
		{ label: __('Award', 'zskeleton'), value: 'awards' },
		{ label: __('Book', 'zskeleton'), value: 'book-alt' },
		{ label: __('Megaphone', 'zskeleton'), value: 'megaphone' },
		{ label: __('Chart', 'zskeleton'), value: 'chart-area' },
		{ label: __('Groups', 'zskeleton'), value: 'groups' },
		{ label: __('Heart', 'zskeleton'), value: 'heart' },
		{ label: __('Site / globe', 'zskeleton'), value: 'admin-site' },
		{ label: __('Learn more', 'zskeleton'), value: 'welcome-learn-more' },
		{ label: __('Portfolio', 'zskeleton'), value: 'portfolio' },
		{ label: __('Lightbulb', 'zskeleton'), value: 'lightbulb' },
		{ label: __('Clipboard', 'zskeleton'), value: 'clipboard' },
	];

	function blogGridClampSepWidth(n) {
		var v = parseInt(n, 10);
		if (isNaN(v)) {
			return 72;
		}
		return Math.min(480, Math.max(4, v));
	}

	function blogGridClampSepHeight(n) {
		var v = parseInt(n, 10);
		if (isNaN(v)) {
			return 4;
		}
		return Math.min(64, Math.max(1, v));
	}

	function blogGridClampSepRadius(n) {
		var v = parseInt(n, 10);
		if (isNaN(v)) {
			return 999;
		}
		return Math.min(999, Math.max(0, v));
	}

	function blogGridClampTitleListingGapPx(n) {
		var v = parseInt(n, 10);
		if (isNaN(v)) {
			return 20;
		}
		return Math.min(200, Math.max(0, v));
	}

	/**
	 * Title icon, separator, spacing under title — shared by Latest / Featured / Trending blocks.
	 *
	 * @param {{}} a Attributes.
	 * @param {Function} setAttributes Block setAttributes.
	 * @param {{ includeShowHeading?: boolean }} opts When true, prepend “Show section title”.
	 * @returns {*}
	 */
	function renderBlogHubTitleAppearancePanel(a, setAttributes, opts) {
		opts = opts || {};
		return createElement(
			PanelBody,
			{ title: __('Title appearance', 'zskeleton'), initialOpen: false },
			opts.includeShowHeading
				? createElement(ToggleControl, {
						label: __('Show section title', 'zskeleton'),
						help: __(
							'Turn off to show only the listing. The editor still saves a section title text for accessibility labels.',
							'zskeleton'
						),
						checked: a.showHeading !== false,
						onChange: function (v) {
							setAttributes({ showHeading: !!v });
						},
				  })
				: null,
			createElement(SelectControl, {
				label: __('Title icon', 'zskeleton'),
				help: __(
					'Optional Dashicon before the heading (same accent bar as Latest posts grid).',
					'zskeleton'
				),
				value: a.titleDashicon || '',
				options: BLOG_GRID_TITLE_DASHICON_OPTIONS,
				onChange: function (v) {
					setAttributes({ titleDashicon: v || '' });
				},
			}),
			createElement(ToggleControl, {
				label: __('Show accent bar under title', 'zskeleton'),
				checked: a.titleShowSeparator !== false,
				onChange: function (v) {
					setAttributes({ titleShowSeparator: !!v });
				},
			}),
			createElement(RangeControl, {
				label: __('Separator width (px)', 'zskeleton'),
				value: blogGridClampSepWidth(a.titleSeparatorWidthPx),
				onChange: function (v) {
					setAttributes({ titleSeparatorWidthPx: v });
				},
				min: 4,
				max: 480,
				step: 1,
			}),
			createElement(RangeControl, {
				label: __('Separator height (px)', 'zskeleton'),
				value: blogGridClampSepHeight(a.titleSeparatorHeightPx),
				onChange: function (v) {
					setAttributes({ titleSeparatorHeightPx: v });
				},
				min: 1,
				max: 64,
				step: 1,
			}),
			createElement(RangeControl, {
				label: __('Separator border radius (px)', 'zskeleton'),
				value: blogGridClampSepRadius(a.titleSeparatorRadiusPx),
				onChange: function (v) {
					setAttributes({ titleSeparatorRadiusPx: v });
				},
				min: 0,
				max: 999,
				step: 1,
			}),
			createElement(
				'div',
				{ className: 'components-base-control', style: { marginBottom: '12px' } },
				createElement(
					'label',
					{
						className: 'components-base-control__label',
						htmlFor: 'zskeleton-blog-hub-title-sep-color',
					},
					__('Separator color', 'zskeleton')
				),
				createElement('input', {
					id: 'zskeleton-blog-hub-title-sep-color',
					type: 'color',
					value: /^#[0-9A-Fa-f]{6}$/.test(a.titleSeparatorColor || '')
						? a.titleSeparatorColor
						: '#b8d4eb',
					onChange: function (e) {
						setAttributes({ titleSeparatorColor: e.target.value });
					},
					'aria-label': __('Separator color', 'zskeleton'),
					style: { width: '100%', maxWidth: '120px', height: '32px', cursor: 'pointer' },
				})
			),
			createElement(RangeControl, {
				label: __('Space between title and listing (px)', 'zskeleton'),
				help: __(
					'Vertical space from the title row (including accent bar) to the post cards grid.',
					'zskeleton'
				),
				value: blogGridClampTitleListingGapPx(a.titleListingGapPx),
				onChange: function (v) {
					setAttributes({ titleListingGapPx: v });
				},
				min: 0,
				max: 200,
				step: 1,
			})
		);
	}

	/** Same editor script as blog hub blocks; hub must call `registerBlockType` so bootstrapped server metadata merges into the block store. */
	var THEME_SLIDER_BLOCK = 'zskeleton/theme-slider';

	var dynamicBlockNames = [
		'zskeleton/blog-featured',
		'zskeleton/blog-posts-grid',
		'zskeleton/blog-trending',
		'zskeleton/blog-category-terms',
		'zskeleton/blog-lead-gen',
		THEME_SLIDER_BLOCK,
	];

	function createDynamicEdit(blockName) {
		return function DynamicEdit(props) {
			var blockProps = useBlockProps();
			return createElement(
				'div',
				blockProps,
				createElement(ServerSideRender, {
					block: blockName,
					attributes: props.attributes,
					// POST avoids huge/fragile GET query strings (encoding, URL limits) so REST returns `rendered` reliably.
					httpMethod: 'POST',
				})
			);
		};
	}

	dynamicBlockNames.forEach(function (name) {
		var existing = getBlockType(name);
		if (existing) {
			unregisterBlockType(name);
		}
		registerBlockType(
			name,
			Object.assign({}, existing || {}, {
				apiVersion: 3,
				edit: createDynamicEdit(name),
				save: function () {
					return null;
				},
			})
		);
	});

	function getThemeSliderSelectOptions() {
		var raw =
			window.zskeletonSliderBlockData && Array.isArray(window.zskeletonSliderBlockData.sliders)
				? window.zskeletonSliderBlockData.sliders
				: [];
		var options = [{ label: __('Select a slider', 'zskeleton'), value: '0' }];
		raw.forEach(function (row) {
			if (row && row.value !== undefined && row.label) {
				options.push({ label: String(row.label), value: String(row.value) });
			}
		});
		return options;
	}

	function wrapBlockEdit(BlockEdit) {
		return function (props) {
			var a = props.attributes || {};
			if (props.name === 'zskeleton/blog-featured') {
				return createElement(
					Fragment,
					null,
					createElement(BlockEdit, props),
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __('Featured posts', 'zskeleton'), initialOpen: true },
							createElement(ToggleControl, {
								label: __('Respect “Show featured” in ZSkeleton Content', 'zskeleton'),
								help: __(
									'Turn off to show this block even when the theme option hides the featured strip.',
									'zskeleton'
								),
								checked: a.useThemeVisibility !== false,
								onChange: function (v) {
									props.setAttributes({ useThemeVisibility: v });
								},
							}),
							createElement(ToggleControl, {
								label: __('Use theme post count', 'zskeleton'),
								help: __(
									'When off, set card count on this block. Only posts flagged in “Blog listing” on each edit screen, plus sticky posts—newest posts are not added.',
									'zskeleton'
								),
								checked: a.useThemeCount !== false,
								onChange: function (v) {
									props.setAttributes({ useThemeCount: v });
								},
							}),
							(a.useThemeCount === false
								? createElement(RangeControl, {
										label: __('Number of posts', 'zskeleton'),
										value: a.postCount != null ? a.postCount : 3,
										onChange: function (v) {
											props.setAttributes({ postCount: v });
										},
										min: 1,
										max: 12,
									})
								: null),
							createElement(TextControl, {
								label: __('Section title (optional)', 'zskeleton'),
								help: __(
									'Leave empty to use the default “Featured” label (or filters).',
									'zskeleton'
								),
								value: a.sectionHeading || '',
								onChange: function (v) {
									props.setAttributes({ sectionHeading: v });
								},
							})
						),
						renderBlogHubTitleAppearancePanel(a, props.setAttributes, { includeShowHeading: true })
					)
				);
			}
			if (props.name === 'zskeleton/blog-trending') {
				return createElement(
					Fragment,
					null,
					createElement(BlockEdit, props),
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __('Trending / most read', 'zskeleton'), initialOpen: true },
							createElement(ToggleControl, {
								label: __('Respect “Show trending” in ZSkeleton Content', 'zskeleton'),
								checked: a.useThemeVisibility !== false,
								onChange: function (v) {
									props.setAttributes({ useThemeVisibility: v });
								},
							}),
							createElement(ToggleControl, {
								label: __('Use theme post count', 'zskeleton'),
								checked: a.useThemeCount !== false,
								onChange: function (v) {
									props.setAttributes({ useThemeCount: v });
								},
							}),
							(a.useThemeCount === false
								? createElement(RangeControl, {
										label: __('Number of posts', 'zskeleton'),
										value: a.postCount != null ? a.postCount : 5,
										onChange: function (v) {
											props.setAttributes({ postCount: v });
										},
										min: 1,
										max: 12,
									})
								: null),
							createElement(ToggleControl, {
								label: __('Use theme ranking mode', 'zskeleton'),
								help: __(
									'When off, pick comments (engagement) or views (if view tracking is on).',
									'zskeleton'
								),
								checked: a.useThemeMode !== false,
								onChange: function (v) {
									props.setAttributes({ useThemeMode: v });
								},
							}),
							(a.useThemeMode === false
								? createElement(SelectControl, {
										label: __('Ranking', 'zskeleton'),
										value: a.rankingMode || 'comments',
										options: [
											{ label: __('Most comments', 'zskeleton'), value: 'comments' },
											{ label: __('Most views', 'zskeleton'), value: 'views' },
										],
										onChange: function (v) {
											props.setAttributes({ rankingMode: v });
										},
									})
								: null),
							createElement(TextControl, {
								label: __('Section title (optional)', 'zskeleton'),
								value: a.sectionHeading || '',
								onChange: function (v) {
									props.setAttributes({ sectionHeading: v });
								},
							})
						),
						renderBlogHubTitleAppearancePanel(a, props.setAttributes, { includeShowHeading: true })
					)
				);
			}
			if (props.name === 'zskeleton/blog-lead-gen') {
				return createElement(
					Fragment,
					null,
					createElement(BlockEdit, props),
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __('Lead / newsletter CTA', 'zskeleton'), initialOpen: true },
							createElement(ToggleControl, {
								label: __('Respect “Show lead block” in ZSkeleton Content', 'zskeleton'),
								checked: a.useThemeVisibility !== false,
								onChange: function (v) {
									props.setAttributes({ useThemeVisibility: v });
								},
							}),
							createElement(ToggleControl, {
								label: __('Use copy from ZSkeleton Content (Newsletter/lead)', 'zskeleton'),
								checked: a.useThemeCopy !== false,
								onChange: function (v) {
									props.setAttributes({ useThemeCopy: v });
								},
							}),
							(a.useThemeCopy === false
								? createElement(
										Fragment,
										null,
										createElement(TextControl, {
											label: __('Title', 'zskeleton'),
											value: a.leadTitle || '',
											onChange: function (v) {
												props.setAttributes({ leadTitle: v });
											},
										}),
										createElement(TextareaControl, {
											label: __('Description', 'zskeleton'),
											value: a.leadText || '',
											onChange: function (v) {
												props.setAttributes({ leadText: v });
											},
										}),
										createElement(TextControl, {
											label: __('Button label', 'zskeleton'),
											value: a.buttonText || '',
											onChange: function (v) {
												props.setAttributes({ buttonText: v });
											},
										}),
										createElement(TextControl, {
											label: __('Button URL', 'zskeleton'),
											value: a.buttonUrl || '',
											onChange: function (v) {
												props.setAttributes({ buttonUrl: v });
											},
										})
									)
								: null)
						)
					)
				);
			}
			if (props.name === 'zskeleton/blog-posts-grid') {
				return createElement(
					Fragment,
					null,
					createElement(BlockEdit, props),
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __('Latest posts grid', 'zskeleton'), initialOpen: true },
							createElement(ToggleControl, {
								label: __('Show heading', 'zskeleton'),
								checked: props.attributes.showHeading !== false,
								onChange: function (v) {
									props.setAttributes({ showHeading: v });
								},
							}),
							createElement(TextControl, {
								label: __('Heading text', 'zskeleton'),
								help: __(
									'Leave empty to use the default “Latest articles” title (or the filtered theme string).',
									'zskeleton'
								),
								value: props.attributes.heading || '',
								onChange: function (v) {
									props.setAttributes({ heading: v });
								},
							}),
							createElement(ToggleControl, {
								label: __(
									'Match theme: hide posts already in the featured strip',
									'zskeleton'
								),
								checked: props.attributes.matchThemeExcludeFeatured !== false,
								onChange: function (v) {
									props.setAttributes({ matchThemeExcludeFeatured: v });
								},
							}),
							createElement(RangeControl, {
								label: __('Posts per page (0 = use theme / Reading)', 'zskeleton'),
								help: __(
									'Overrides how many cards load per page on this block’s query.',
									'zskeleton'
								),
								value: a.postsPerPage != null ? a.postsPerPage : 0,
								onChange: function (v) {
									props.setAttributes({ postsPerPage: v });
								},
								min: 0,
								max: 50,
							}),
							createElement(SelectControl, {
								label: __('Grid columns (0 = theme default)', 'zskeleton'),
								value: String(a.columns != null ? a.columns : 0),
								options: [
									{ label: __('Default', 'zskeleton'), value: '0' },
									{ label: '1', value: '1' },
									{ label: '2', value: '2' },
									{ label: '3', value: '3' },
									{ label: '4', value: '4' },
								],
								onChange: function (v) {
									props.setAttributes({ columns: parseInt(v, 10) || 0 });
								},
							})
						),
						renderBlogHubTitleAppearancePanel(a, props.setAttributes, {})
					)
				);
			}
			if (props.name === 'zskeleton/blog-category-terms') {
				return createElement(
					Fragment,
					null,
					createElement(BlockEdit, props),
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __('Categories', 'zskeleton'), initialOpen: true },
							createElement(ToggleControl, {
								label: __('Respect “Show category blocks” in ZSkeleton Content', 'zskeleton'),
								checked: a.useThemeVisibility !== false,
								onChange: function (v) {
									props.setAttributes({ useThemeVisibility: v });
								},
							}),
							createElement(ToggleControl, {
								label: __('Show section heading', 'zskeleton'),
								checked: a.showSectionHeading !== false,
								onChange: function (v) {
									props.setAttributes({ showSectionHeading: v });
								},
							}),
							(a.showSectionHeading !== false
								? createElement(TextControl, {
										label: __('Section title (optional)', 'zskeleton'),
										help: __(
											'Leave empty for the default “Browse by category” (or filters).',
											'zskeleton'
										),
										value: a.sectionHeading || '',
										onChange: function (v) {
											props.setAttributes({ sectionHeading: v });
										},
									})
								: null),
							createElement(SelectControl, {
								label: __('Layout style', 'zskeleton'),
								help: __(
									'Default uses ZSkeleton → Content → Category listing style.',
									'zskeleton'
								),
								value: a.layout || '',
								options: [
									{ label: __('Theme default', 'zskeleton'), value: '' },
									{ label: __('Thumbnails', 'zskeleton'), value: 'thumbnails' },
									{ label: __('Icons', 'zskeleton'), value: 'icons' },
									{ label: __('Simple', 'zskeleton'), value: 'simple' },
								],
								onChange: function (v) {
									props.setAttributes({ layout: v });
								},
							}),
							createElement(RangeControl, {
								label: __('Number of categories', 'zskeleton'),
								help: __(
									'Set to 0 to use the theme Content setting. When hand-picking categories below, this caps how many are shown (A–Z among checked).',
									'zskeleton'
								),
								value: a.maxTerms || 0,
								onChange: function (v) {
									props.setAttributes({ maxTerms: v });
								},
								min: 0,
								max: 12,
							}),
							createElement('hr', { key: 'hr-cats', style: { margin: '1rem 0', border: 0, borderTop: '1px solid #ddd' } }),
							createElement(
								'p',
								{ key: 'handpick-title', style: { fontWeight: 600, marginBottom: '0.25rem' } },
								__('Hand-pick categories', 'zskeleton')
							),
							createElement(BlogCategoryTermsHandPickPanel, { key: 'handpick', attributes: a, setAttributes: props.setAttributes })
						)
					)
				);
			}
			if (props.name === THEME_SLIDER_BLOCK) {
				var sliderOpts = getThemeSliderSelectOptions();
				var sid = a.sliderId !== undefined && a.sliderId !== null ? String(a.sliderId) : '0';
				return createElement(
					Fragment,
					null,
					createElement(BlockEdit, props),
					createElement(
						InspectorControls,
						null,
						createElement(
							PanelBody,
							{ title: __('Slider', 'zskeleton'), initialOpen: true },
							createElement(SelectControl, {
								label: __('Select slider', 'zskeleton'),
								value: sid,
								options: sliderOpts,
								onChange: function (v) {
									var n = parseInt(v, 10);
									props.setAttributes({ sliderId: !isNaN(n) && n > 0 ? n : 0 });
								},
							})
						)
					)
				);
			}
			return createElement(BlockEdit, props);
		};
	}

	addFilter('editor.BlockEdit', 'zskeleton/blog-hub-blocks-inspector', wrapBlockEdit);
})(window.wp);
