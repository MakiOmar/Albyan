<?php
/**
 * SEO Expert landing: scalar post meta boxes + repeater registration.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scalar meta for SEO Expert page template.
 */
class ZSkeleton_Seo_Expert_Meta {

	const META_PREFIX = '_zskeleton_seo_';

	/**
	 * Meta key for a scalar field.
	 *
	 * @param string $key Short key (e.g. expert_name).
	 * @return string
	 */
	public static function meta_key( $key ) {
		return self::META_PREFIX . sanitize_key( $key );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_page', array( $this, 'save_post' ), 10, 2 );
		add_action( 'after_setup_theme', array( $this, 'register_repeater_groups' ), 25 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Media modal for SEO Expert image fields (page edit only).
	 *
	 * @param string $hook_suffix Current admin screen.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'page' !== $screen->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Scalar profile field definitions (render + save use the same source).
	 *
	 * Textarea fields default to a visual editor (`editor` => `wysiwyg`).
	 * Set `editor` => `textarea` for a plain multiline field (no TinyMCE).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private static function get_profile_field_config() {
		$fields = array(
			'expert_name'             => array( 'label' => __( 'Expert display name', 'zskeleton' ), 'type' => 'text' ),
			'hero_title'              => array( 'label' => __( 'Hero title (H1)', 'zskeleton' ), 'type' => 'text' ),
			'hero_subtitle'           => array( 'label' => __( 'Hero subtitle', 'zskeleton' ), 'type' => 'textarea' ),
			'hero_image_id'           => array( 'label' => __( 'Hero image — Media Library ID (optional)', 'zskeleton' ), 'type' => 'text' ),
			'hero_image_url'          => array( 'label' => __( 'Hero image — Custom URL (optional, used if no ID)', 'zskeleton' ), 'type' => 'text' ),
			'hero_image_alt'          => array( 'label' => __( 'Hero image — Alt text (optional)', 'zskeleton' ), 'type' => 'text' ),
			'years_experience'        => array( 'label' => __( 'Years experience (single number)', 'zskeleton' ), 'type' => 'text' ),
			'primary_cta_label'       => array( 'label' => __( 'Primary CTA label', 'zskeleton' ), 'type' => 'text' ),
			'secondary_cta_label'     => array( 'label' => __( 'Secondary CTA label', 'zskeleton' ), 'type' => 'text' ),
			'landing_term_slug'       => array( 'label' => __( 'Landing term slug (services grid; Landings taxonomy)', 'zskeleton' ), 'type' => 'text' ),
			'brand_or_team_name'      => array( 'label' => __( 'Brand / team name (footer)', 'zskeleton' ), 'type' => 'text' ),
			'intro_body'              => array( 'label' => __( 'Intro body (after hero)', 'zskeleton' ), 'type' => 'textarea' ),
			'pricing_disclaimer_body' => array( 'label' => __( 'Pricing section body', 'zskeleton' ), 'type' => 'textarea' ),
			'pricing_section_side_image_id' => array(
				'label'       => __( 'Pricing section — optional image (opposite the icon)', 'zskeleton' ),
				'type'        => 'image',
				'description' => __( 'Shown beside the pricing text when set; the circular icon stays on the other side.', 'zskeleton' ),
			),
			'prose_arabic_market'     => array( 'label' => __( 'Why Arabic market (prose)', 'zskeleton' ), 'type' => 'textarea' ),
			'prose_arabic_market_side_image_id' => array(
				'label'       => __( 'Why Arabic market — optional side image', 'zskeleton' ),
				'type'        => 'image',
				'description' => __( 'Placed on the side opposite the section icon when set.', 'zskeleton' ),
			),
			'prose_results_steps'     => array( 'label' => __( 'How expert achieves results (prose)', 'zskeleton' ), 'type' => 'textarea' ),
			'prose_results_steps_side_image_id' => array(
				'label'       => __( 'Search results section — optional side image', 'zskeleton' ),
				'type'        => 'image',
				'description' => __( 'Placed on the side opposite the section icon when set.', 'zskeleton' ),
			),
			'prose_success_factors'   => array( 'label' => __( 'Campaign success factors (prose)', 'zskeleton' ), 'type' => 'textarea' ),
			'prose_success_factors_side_image_id' => array(
				'label'       => __( 'Success factors — optional side image', 'zskeleton' ),
				'type'        => 'image',
				'description' => __( 'Placed on the side opposite the section icon when set.', 'zskeleton' ),
			),
			'prose_how_to_choose'     => array( 'label' => __( 'How to choose an expert (prose)', 'zskeleton' ), 'type' => 'textarea' ),
			'prose_how_to_choose_side_image_id' => array(
				'label'       => __( 'How to choose expert — optional side image', 'zskeleton' ),
				'type'        => 'image',
				'description' => __( 'Placed on the side opposite the section icon when set.', 'zskeleton' ),
			),
			'prose_closing_cta'       => array( 'label' => __( 'Closing CTA block', 'zskeleton' ), 'type' => 'textarea' ),
			'ai_lead_title'           => array( 'label' => __( 'Contact / AI lead — main heading (H2)', 'zskeleton' ), 'type' => 'text' ),
			'ai_lead_intro'           => array( 'label' => __( 'Contact / AI lead — intro paragraph (HTML: spans, links)', 'zskeleton' ), 'type' => 'textarea' ),
			'ai_lead_subhead_warn'    => array( 'label' => __( 'Contact / AI lead — subhead “beware”', 'zskeleton' ), 'type' => 'text' ),
			'ai_lead_warn_body'       => array( 'label' => __( 'Contact / AI lead — “beware” body (HTML; use %%CASE_STUDY_URL%% for case-study link)', 'zskeleton' ), 'type' => 'textarea' ),
			'ai_lead_subhead_why'     => array( 'label' => __( 'Contact / AI lead — subhead “why us” (leave empty for “لماذا {expert}؟”)', 'zskeleton' ), 'type' => 'text' ),
			'ai_lead_why_p1'          => array( 'label' => __( 'Contact / AI lead — “why” paragraph 1 (use %%EXPERT_NAME%%)', 'zskeleton' ), 'type' => 'textarea' ),
			'ai_lead_why_p2'          => array( 'label' => __( 'Contact / AI lead — “why” paragraph 2 (HTML)', 'zskeleton' ), 'type' => 'textarea' ),
			'ai_lead_form_heading'    => array( 'label' => __( 'Contact / AI lead — form card title', 'zskeleton' ), 'type' => 'text' ),
			'ai_lead_case_study_url'  => array( 'label' => __( 'Contact / AI lead — case study URL (optional; overrides %%CASE_STUDY_URL%% default)', 'zskeleton' ), 'type' => 'text' ),
			'blog_links_mode'         => array(
				'label'   => __( 'Related articles — source', 'zskeleton' ),
				'type'    => 'select',
				'default' => 'recent',
				'options' => array(
					'recent'   => __( 'Most recent blog posts', 'zskeleton' ),
					'selected' => __( 'Hand-picked posts (IDs below)', 'zskeleton' ),
				),
			),
			'blog_links_recent_count' => array(
				'label'       => __( 'Related articles — how many (recent mode)', 'zskeleton' ),
				'type'        => 'text',
				'description' => __( 'Between 1 and 12.', 'zskeleton' ),
			),
			'blog_links_post_ids'     => array(
				'label'       => __( 'Related articles — post IDs', 'zskeleton' ),
				'type'        => 'text',
				'description' => __( 'Comma-separated WordPress post IDs (published posts only). Used when source is “Hand-picked”.', 'zskeleton' ),
			),
		);

		/**
		 * Filter SEO Expert scalar meta fields (labels, types, `editor` => `textarea` for plain multiline).
		 *
		 * @param array<string,array<string,mixed>> $fields Field key => config.
		 */
		return apply_filters( 'zskeleton_seo_expert_profile_fields', $fields );
	}

	/**
	 * Register repeater schemas (after theme repeater registry exists).
	 * These are shaped for this landing (stats, why-us, …), not generic glossaries.
	 * For term+definition lists, use {@see zskeleton_register_glossary_group()} in the theme repeater API.
	 */
	public function register_repeater_groups() {
		$show_if = array( 'page_template' => 'page-seo-expert.php' );

		zskeleton_register_repeater_group(
			'seo_stats',
			array(
				'label'      => __( 'Hero statistics', 'zskeleton' ),
				'post_types' => array( 'page' ),
				'show_if'    => $show_if,
				'fields'     => array(
					'figure' => array( 'type' => 'text', 'label' => __( 'Figure', 'zskeleton' ) ),
					'label'  => array( 'type' => 'text', 'label' => __( 'Label', 'zskeleton' ) ),
				),
			)
		);

		zskeleton_register_repeater_group(
			'seo_ratings',
			array(
				'label'      => __( 'Ratings strip', 'zskeleton' ),
				'post_types' => array( 'page' ),
				'show_if'    => $show_if,
				'fields'     => array(
					'score'    => array( 'type' => 'text', 'label' => __( 'Score', 'zskeleton' ) ),
					'platform' => array( 'type' => 'text', 'label' => __( 'Platform', 'zskeleton' ) ),
					'count'    => array( 'type' => 'text', 'label' => __( 'Count text', 'zskeleton' ) ),
				),
			)
		);

		zskeleton_register_repeater_group(
			'seo_why_us',
			array(
				'label'      => __( 'Why choose us (points)', 'zskeleton' ),
				'post_types' => array( 'page' ),
				'show_if'    => $show_if,
				'fields'     => array(
					'title' => array( 'type' => 'text', 'label' => __( 'Title', 'zskeleton' ) ),
					'body'  => array( 'type' => 'textarea', 'label' => __( 'Body', 'zskeleton' ) ),
				),
			)
		);

		zskeleton_register_repeater_group(
			'seo_methodology',
			array(
				'label'      => __( 'Methodology steps', 'zskeleton' ),
				'post_types' => array( 'page' ),
				'show_if'    => $show_if,
				'fields'     => array(
					'step_title' => array( 'type' => 'text', 'label' => __( 'Step title', 'zskeleton' ) ),
					'step_body'  => array( 'type' => 'textarea', 'label' => __( 'Step body', 'zskeleton' ) ),
				),
			)
		);

		zskeleton_register_repeater_group(
			'seo_tools',
			array(
				'label'      => __( 'Tools & technologies', 'zskeleton' ),
				'post_types' => array( 'page' ),
				'show_if'    => $show_if,
				'fields'     => array(
					'name'        => array( 'type' => 'text', 'label' => __( 'Name', 'zskeleton' ) ),
					'description' => array( 'type' => 'textarea', 'label' => __( 'Description', 'zskeleton' ) ),
				),
			)
		);

	}

	/**
	 * Add profile meta box for SEO Expert template pages.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'zskeleton-seo-expert-profile',
			__( 'SEO Expert profile', 'zskeleton' ),
			array( $this, 'render_profile_box' ),
			'page',
			'normal',
			'high'
		);
	}

	/**
	 * @param WP_Post $post Post.
	 */
	public function render_profile_box( $post ) {
		if ( 'page' !== $post->post_type ) {
			return;
		}

		$tpl = (string) get_page_template_slug( $post );
		if ( 'page-seo-expert.php' !== $tpl ) {
			echo '<p class="description">' . esc_html__( 'Switch this page to the “SEO Expert” template to use these fields.', 'zskeleton' ) . '</p>';
			return;
		}

		wp_nonce_field( 'zskeleton_seo_expert_save', 'zskeleton_seo_expert_nonce' );

		$fields = self::get_profile_field_config();

		echo '<p class="description">' . esc_html__( 'Used by the “SEO Expert” page template. Expert display name is the single source for headings and structured data. Hero image: set a Media Library attachment ID, or a full image URL, or leave both empty to use the theme default (hero-man.webp). Long fields use the visual editor unless a field is registered as a plain textarea in theme code.', 'zskeleton' ) . '</p>';
		echo '<div class="zskeleton-seo-expert-fields">';

		foreach ( $fields as $key => $conf ) {
			$mk   = self::meta_key( $key );
			$val  = get_post_meta( $post->ID, $mk, true );
			$val  = is_string( $val ) ? $val : '';
			// HTML id: letters, numbers, underscores only (required for wp_editor / TinyMCE).
			$id   = 'zskeleton_seo_' . $key;
			$lab  = $conf['label'];
			$type = isset( $conf['type'] ) ? $conf['type'] : 'text';

			echo '<div class="zskeleton-seo-field"><label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $lab ) . '</strong></label>';

			if ( 'select' === $type && isset( $conf['options'] ) && is_array( $conf['options'] ) ) {
				$sel_val = $val;
				if ( '' === $sel_val && isset( $conf['default'] ) ) {
					$sel_val = (string) $conf['default'];
				}
				echo '<select class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $mk ) . '">';
				foreach ( $conf['options'] as $opt_val => $opt_label ) {
					$ov = (string) $opt_val;
					echo '<option value="' . esc_attr( $ov ) . '"' . selected( $sel_val, $ov, false ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
			} elseif ( 'image' === $type ) {
				$img_id = absint( $val );
				$img_url = '';
				if ( $img_id && wp_attachment_is_image( $img_id ) ) {
					$img_url = (string) wp_get_attachment_image_url( $img_id, 'medium' );
				}
				echo '<div class="zskeleton-seo-expert-image-field" data-field-id="' . esc_attr( $id ) . '">';
				echo '<input type="hidden" class="zskeleton-seo-expert-image-id" id="' . esc_attr( $id ) . '" name="' . esc_attr( $mk ) . '" value="' . esc_attr( $img_id ? (string) $img_id : '' ) . '" />';
				echo '<div class="zskeleton-seo-expert-image-preview" style="margin:8px 0;min-height:1px;">';
				if ( $img_url !== '' ) {
					echo '<img src="' . esc_url( $img_url ) . '" alt="" style="max-width:220px;height:auto;display:block;border-radius:8px;border:1px solid #c3c4c7;" />';
				}
				echo '</div>';
				echo '<p><button type="button" class="button zskeleton-seo-expert-upload-img">' . esc_html__( 'Select image', 'zskeleton' ) . '</button> ';
				echo '<button type="button" class="button zskeleton-seo-expert-clear-img"' . ( $img_id ? '' : ' style="display:none;"' ) . '>' . esc_html__( 'Remove image', 'zskeleton' ) . '</button></p>';
				echo '</div>';
			} elseif ( 'textarea' === $type && function_exists( 'zskeleton_field_config_uses_wysiwyg' ) && zskeleton_field_config_uses_wysiwyg( $conf ) ) {
				$rows = ( 0 === strpos( $key, 'ai_lead_' ) ) ? 8 : 6;
				zskeleton_render_meta_wysiwyg(
					$id,
					$mk,
					$val,
					array( 'textarea_rows' => (int) $rows )
				);
			} elseif ( 'textarea' === $type ) {
				$rows = ( 0 === strpos( $key, 'ai_lead_' ) ) ? 8 : 4;
				echo '<textarea class="widefat" rows="' . (int) $rows . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $mk ) . '">' . esc_textarea( $val ) . '</textarea>';
			} else {
				echo '<input type="text" class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $mk ) . '" value="' . esc_attr( $val ) . '" />';
			}
			if ( ! empty( $conf['description'] ) ) {
				echo '<p class="description">' . esc_html( $conf['description'] ) . '</p>';
			}
			echo '</div>';
		}

		echo '<p><button type="button" class="button" id="zskeleton-seo-apply-defaults">' . esc_html__( 'Apply README-shaped defaults (empty fields only)', 'zskeleton' ) . '</button>';
		wp_nonce_field( 'zskeleton_seo_apply_defaults', 'zskeleton_seo_apply_defaults_nonce', false );
		echo '<input type="hidden" name="zskeleton_seo_apply_defaults_flag" id="zskeleton_seo_apply_defaults_flag" value="0" />';
		echo '</div>';
		?>
		<script>
		(function(){
			var btn = document.getElementById('zskeleton-seo-apply-defaults');
			var flag = document.getElementById('zskeleton_seo_apply_defaults_flag');
			if (btn && flag) {
				btn.addEventListener('click', function(){
					if (window.confirm(<?php echo wp_json_encode( __( 'Fill empty fields from default Arabic copy for أحمد مكي?', 'zskeleton' ) ); ?>)) {
						flag.value = '1';
					}
				});
			}
		})();
		</script>
		<script>
		jQuery(function($){
			var frame;
			$(document).on('click', '.zskeleton-seo-expert-upload-img', function(e){
				e.preventDefault();
				var $wrap = $(this).closest('.zskeleton-seo-expert-image-field');
				var $input = $wrap.find('.zskeleton-seo-expert-image-id');
				var $prev = $wrap.find('.zskeleton-seo-expert-image-preview');
				var $clear = $wrap.find('.zskeleton-seo-expert-clear-img');
				if (frame) { frame.dispose(); }
				frame = wp.media({ title: <?php echo wp_json_encode( __( 'Choose image', 'zskeleton' ) ); ?>, button: { text: <?php echo wp_json_encode( __( 'Use this image', 'zskeleton' ) ); ?> }, multiple: false });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					$input.val(att.id ? String(att.id) : '');
					$prev.empty();
					var u = (att.sizes && att.sizes.medium && att.sizes.medium.url) ? att.sizes.medium.url : (att.url || '');
					if (u) {
						var $im = $('<img />').attr('src', u).attr('alt', '').css({ maxWidth: '220px', height: 'auto', display: 'block', borderRadius: '8px', border: '1px solid #c3c4c7' });
						$prev.append($im);
					}
					$clear.show();
				});
				frame.open();
			});
			$(document).on('click', '.zskeleton-seo-expert-clear-img', function(e){
				e.preventDefault();
				var $wrap = $(this).closest('.zskeleton-seo-expert-image-field');
				$wrap.find('.zskeleton-seo-expert-image-id').val('');
				$wrap.find('.zskeleton-seo-expert-image-preview').empty();
				$(this).hide();
			});
		});
		</script>
		<?php
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['zskeleton_seo_expert_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_seo_expert_nonce'] ) ), 'zskeleton_seo_expert_save' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( 'page' !== $post->post_type ) {
			return;
		}

		$tpl = (string) get_page_template_slug( $post );
		if ( 'page-seo-expert.php' !== $tpl ) {
			return;
		}

		$field_config = self::get_profile_field_config();

		foreach ( array_keys( $field_config ) as $key ) {
			$conf = $field_config[ $key ];
			$mk   = self::meta_key( $key );
			if ( 'blog_links_mode' === $key ) {
				if ( ! isset( $_POST[ $mk ] ) ) {
					continue;
				}
				$v = sanitize_text_field( wp_unslash( $_POST[ $mk ] ) );
				if ( ! in_array( $v, array( 'recent', 'selected' ), true ) ) {
					$v = 'recent';
				}
				update_post_meta( $post_id, $mk, $v );
				continue;
			}
			if ( 'blog_links_recent_count' === $key ) {
				$rawc = isset( $_POST[ $mk ] ) ? wp_unslash( $_POST[ $mk ] ) : '4';
				$cn   = absint( $rawc );
				if ( $cn < 1 ) {
					$cn = 4;
				}
				$cn = min( 12, $cn );
				update_post_meta( $post_id, $mk, (string) $cn );
				continue;
			}
			if ( 'blog_links_post_ids' === $key ) {
				if ( ! isset( $_POST[ $mk ] ) ) {
					continue;
				}
				$raw = (string) wp_unslash( $_POST[ $mk ] );
				$ids = array_unique( array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY ) ) ) );
				if ( empty( $ids ) ) {
					delete_post_meta( $post_id, $mk );
				} else {
					update_post_meta( $post_id, $mk, implode( ',', $ids ) );
				}
				continue;
			}
			if ( 'hero_image_id' === $key || ( isset( $conf['type'] ) && 'image' === $conf['type'] ) ) {
				if ( ! isset( $_POST[ $mk ] ) ) {
					continue;
				}
				$hid = absint( wp_unslash( $_POST[ $mk ] ) );
				if ( $hid > 0 ) {
					update_post_meta( $post_id, $mk, $hid );
				} else {
					delete_post_meta( $post_id, $mk );
				}
				continue;
			}
			if ( 'hero_image_url' === $key ) {
				if ( ! isset( $_POST[ $mk ] ) ) {
					continue;
				}
				$url_raw = esc_url_raw( trim( wp_unslash( $_POST[ $mk ] ) ) );
				if ( '' !== $url_raw ) {
					update_post_meta( $post_id, $mk, $url_raw );
				} else {
					delete_post_meta( $post_id, $mk );
				}
				continue;
			}
			if ( 'ai_lead_case_study_url' === $key ) {
				if ( ! isset( $_POST[ $mk ] ) ) {
					continue;
				}
				$url_raw = esc_url_raw( trim( wp_unslash( $_POST[ $mk ] ) ) );
				if ( '' !== $url_raw ) {
					update_post_meta( $post_id, $mk, $url_raw );
				} else {
					delete_post_meta( $post_id, $mk );
				}
				continue;
			}
			if ( ! isset( $_POST[ $mk ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $mk ] );
			if ( 'landing_term_slug' === $key ) {
				update_post_meta( $post_id, $mk, sanitize_title( $raw ) );
				continue;
			}
			$ftype = isset( $conf['type'] ) ? $conf['type'] : 'text';
			if ( 'textarea' === $ftype ) {
				if ( function_exists( 'zskeleton_field_config_uses_wysiwyg' ) && zskeleton_field_config_uses_wysiwyg( $conf ) ) {
					update_post_meta( $post_id, $mk, wp_kses_post( $raw ) );
				} else {
					update_post_meta( $post_id, $mk, sanitize_textarea_field( $raw ) );
				}
				continue;
			}
			update_post_meta( $post_id, $mk, sanitize_text_field( $raw ) );
		}

		if ( isset( $_POST['zskeleton_seo_apply_defaults_flag'] ) && '1' === $_POST['zskeleton_seo_apply_defaults_flag']
			&& isset( $_POST['zskeleton_seo_apply_defaults_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_seo_apply_defaults_nonce'] ) ), 'zskeleton_seo_apply_defaults' ) ) {
			zskeleton_seo_expert_apply_defaults_if_empty( $post_id );
		}
	}
}
