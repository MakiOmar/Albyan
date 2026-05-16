<?php
/**
 * SEO Expert landing kit bootstrap.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-seo-expert-defaults.php';
require_once __DIR__ . '/class-seo-expert-meta.php';
require_once __DIR__ . '/seo-expert-icons.php';

/**
 * Comma-separated RGB for rgba() in inline CSS.
 *
 * @param string $hex Hex color.
 * @return string
 */
function zskeleton_seo_expert_hex_rgb_csv( $hex ) {
	$rgb = zskeleton_hex_to_rgb( $hex );
	if ( ! $rgb ) {
		return '100, 116, 188';
	}
	return (int) $rgb[0] . ', ' . (int) $rgb[1] . ', ' . (int) $rgb[2];
}

/**
 * Inline design tokens from ZSkeleton Settings / Customizer colors (scoped to SEO Expert page).
 *
 * @return string Non-empty CSS.
 */
function zskeleton_seo_expert_get_inline_theme_css() {
	if ( ! function_exists( 'zskeleton_get_resolved_theme_colors' ) || ! function_exists( 'zskeleton_mix_hex_colors' ) ) {
		return '';
	}

	$c            = zskeleton_get_resolved_theme_colors();
	$primary      = $c['primary'];
	$secondary    = $c['secondary'];
	$accent       = $c['accent'];
	$bg           = $c['background'];
	$navy         = $c['navy'];
	$bg_soft      = $c['background_soft'];
	$border_soft  = $c['border_soft'];
	$card         = $c['card_surface'];
	// SEO Expert CTAs: secondary fill + black label (distinct from global theme button option).
	$btn_bg       = $secondary;
	$btn_hover    = zskeleton_mix_hex_colors( $secondary, $navy, 0.2 );
	$btn_txt      = '#000000';
	$counter      = $c['counter_text'];

	$muted = zskeleton_mix_hex_colors( $navy, '#64748b', 0.42 );
	$hero2 = zskeleton_mix_hex_colors( $navy, $primary, 0.38 );
	$hero3 = zskeleton_mix_hex_colors( $navy, $secondary, 0.22 );

	$pr_rgb = zskeleton_seo_expert_hex_rgb_csv( $primary );
	$ac_rgb = zskeleton_seo_expert_hex_rgb_csv( $accent );
	$sd_rgb = zskeleton_seo_expert_hex_rgb_csv( $secondary );

	$css  = '.seo-expert-homepage{';
	$css .= '--seo-expert-brand:' . esc_attr( $primary ) . ';';
	$css .= '--seo-expert-secondary:' . esc_attr( $secondary ) . ';';
	$css .= '--seo-expert-mint:' . esc_attr( $accent ) . ';';
	$css .= '--seo-expert-page-bg:' . esc_attr( $bg ) . ';';
	$css .= '--seo-expert-ink:' . esc_attr( $navy ) . ';';
	$css .= '--seo-expert-muted:' . esc_attr( $muted ) . ';';
	$css .= '--seo-expert-bg-soft:' . esc_attr( $bg_soft ) . ';';
	$css .= '--seo-expert-border:' . esc_attr( $border_soft ) . ';';
	$css .= '--seo-expert-surface:' . esc_attr( $card ) . ';';
	$css .= '--seo-expert-btn-bg:' . esc_attr( $btn_bg ) . ';';
	$css .= '--seo-expert-btn-bg-hover:' . esc_attr( $btn_hover ) . ';';
	$css .= '--seo-expert-btn-fg:' . esc_attr( $btn_txt ) . ';';
	$css .= '--seo-expert-counter:' . esc_attr( $counter ) . ';';
	$css .= '--seo-expert-hero-mid:' . esc_attr( $hero2 ) . ';';
	$css .= '--seo-expert-hero-deep:' . esc_attr( $hero3 ) . ';';
	$css .= '--seo-expert-brand-rgb:' . esc_attr( $pr_rgb ) . ';';
	$css .= '--seo-expert-accent-rgb:' . esc_attr( $ac_rgb ) . ';';
	$css .= '--seo-expert-secondary-rgb:' . esc_attr( $sd_rgb ) . ';';
	$css .= '}';

	$cta_mid = zskeleton_mix_hex_colors( $navy, $primary, 0.18 );
	$strip_t = zskeleton_mix_hex_colors( $navy, $secondary, 0.28 );

	$css .= '.seo-expert-homepage .seo-expert-hero{';
	$css .= 'background:';
	$css .= 'radial-gradient(115% 75% at 100% 0%,rgba(' . $pr_rgb . ',0.38) 0%,transparent 52%),';
	$css .= 'radial-gradient(85% 55% at 0% 100%,rgba(' . $ac_rgb . ',0.24) 0%,transparent 50%),';
	$css .= 'linear-gradient(162deg,' . esc_attr( $navy ) . ' 0%,' . esc_attr( $hero2 ) . ' 45%,' . esc_attr( $hero3 ) . ' 100%);';
	$css .= '}';

	$css .= '.seo-expert-homepage .seo-expert-cta-band{';
	$css .= 'background:radial-gradient(ellipse 100% 85% at 50% 0%,rgba(' . $pr_rgb . ',0.22) 0%,transparent 55%),';
	$css .= 'linear-gradient(168deg,' . esc_attr( $navy ) . ' 0%,' . esc_attr( $cta_mid ) . ' 100%);';
	$css .= '}';

	$css .= '.seo-expert-homepage .seo-expert-end-strip:not(.seo-expert-end-strip--in-copy){';
	$css .= 'background:linear-gradient(185deg,' . esc_attr( $strip_t ) . ' 0%,' . esc_attr( $navy ) . ' 100%);';
	$css .= 'border-top-color:rgba(' . $pr_rgb . ',0.25);';
	$css .= '}';

	return $css;
}

/**
 * Resolve hero image for SEO Expert template: Media Library ID, custom URL, or theme default asset.
 *
 * @param int $post_id Page ID.
 * @return array{attachment_id:int,url:string,alt:string}
 */
function zskeleton_seo_expert_get_hero_image( $post_id ) {
	$post_id     = (int) $post_id;
	$default_url = get_template_directory_uri() . '/assets/hero-man.webp';
	$expert_name = zskeleton_seo_expert_get( $post_id, 'expert_name' );
	$alt_default = '' !== $expert_name
		? sprintf(
			/* translators: %s: expert display name */
			__( 'Portrait of %s', 'zskeleton' ),
			$expert_name
		)
		: __( 'SEO consultant portrait', 'zskeleton' );

	$id = absint( get_post_meta( $post_id, ZSkeleton_Seo_Expert_Meta::meta_key( 'hero_image_id' ), true ) );
	if ( $id > 0 && wp_attachment_is_image( $id ) ) {
		$alt = get_post_meta( $post_id, ZSkeleton_Seo_Expert_Meta::meta_key( 'hero_image_alt' ), true );
		$alt = is_string( $alt ) ? trim( $alt ) : '';
		if ( '' === $alt ) {
			$alt = $alt_default;
		}
		return array(
			'attachment_id' => $id,
			'url'           => '',
			'alt'           => $alt,
		);
	}

	$url_meta = get_post_meta( $post_id, ZSkeleton_Seo_Expert_Meta::meta_key( 'hero_image_url' ), true );
	$url_meta = is_string( $url_meta ) ? trim( $url_meta ) : '';
	$alt      = get_post_meta( $post_id, ZSkeleton_Seo_Expert_Meta::meta_key( 'hero_image_alt' ), true );
	$alt      = is_string( $alt ) ? trim( $alt ) : '';
	if ( '' === $alt ) {
		$alt = $alt_default;
	}

	if ( '' !== $url_meta ) {
		$validated = esc_url_raw( $url_meta );
		if ( '' !== $validated ) {
			return array(
				'attachment_id' => 0,
				'url'           => $validated,
				'alt'           => $alt,
			);
		}
	}

	return array(
		'attachment_id' => 0,
		'url'           => $default_url,
		'alt'           => $alt,
	);
}

/**
 * Case study link target for the AI lead “beware” paragraph (page meta or theme filter).
 *
 * @param int $post_id Post ID.
 * @return string Escaped URL.
 */
function zskeleton_seo_expert_get_ai_lead_case_study_url( $post_id ) {
	$post_id = (int) $post_id;
	$u       = trim( zskeleton_seo_expert_get( $post_id, 'ai_lead_case_study_url', '' ) );
	if ( '' !== $u ) {
		$raw = esc_url_raw( $u );
		if ( '' !== $raw ) {
			return esc_url( $raw );
		}
	}

	return esc_url( apply_filters( 'zskeleton_seo_ar_case_study_portfolio_url', home_url( '/' ) ) );
}

/**
 * JSON-LD Person for SEO Expert page.
 *
 * @param int $post_id Page ID.
 */
function zskeleton_seo_expert_print_json_ld( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id < 1 ) {
		return;
	}

	$name = zskeleton_seo_expert_get( $post_id, 'expert_name' );
	if ( '' === $name ) {
		return;
	}

	$url   = get_permalink( $post_id );
	$years = zskeleton_seo_expert_get( $post_id, 'years_experience' );

	$data = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Person',
		'name'        => $name,
		'url'         => $url,
		'description' => wp_strip_all_tags( zskeleton_seo_expert_get( $post_id, 'hero_subtitle' ) ),
	);

	if ( '' !== $years && is_numeric( $years ) ) {
		$data['knowsAbout'] = 'SEO';
	}

	$data = apply_filters( 'zskeleton_seo_expert_json_ld', $data, $post_id );

	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}

/**
 * Published posts for the “related articles” section (recent or hand-picked IDs).
 *
 * @param int $landing_page_id SEO Expert page ID.
 * @return WP_Post[]
 */
function zskeleton_seo_expert_get_related_blog_posts( $landing_page_id ) {
	$landing_page_id = (int) $landing_page_id;
	if ( $landing_page_id < 1 ) {
		return array();
	}

	$mode = zskeleton_seo_expert_get( $landing_page_id, 'blog_links_mode', 'recent' );
	if ( ! in_array( $mode, array( 'recent', 'selected' ), true ) ) {
		$mode = 'recent';
	}

	$count_raw = zskeleton_seo_expert_get( $landing_page_id, 'blog_links_recent_count', '4' );
	$count     = absint( $count_raw );
	if ( $count < 1 ) {
		$count = 4;
	}
	$count = min( 12, $count );

	if ( 'selected' === $mode ) {
		$raw = zskeleton_seo_expert_get( $landing_page_id, 'blog_links_post_ids', '' );
		$ids = array_unique( array_filter( array_map( 'absint', preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY ) ) ) );
		if ( empty( $ids ) ) {
			return array();
		}

		$args = array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'post__in'               => $ids,
			'orderby'                => 'post__in',
			'posts_per_page'         => count( $ids ),
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		/**
		 * Filter WP_Query arguments for hand-picked related posts on the SEO Expert template.
		 *
		 * @param array $args              Query arguments.
		 * @param int   $landing_page_id Landing page ID.
		 */
		$args  = apply_filters( 'zskeleton_seo_expert_selected_blog_query_args', $args, $landing_page_id );
		$query = new WP_Query( $args );
		$posts = $query->posts;
	} else {
		$args = array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => $count,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		/**
		 * Filter WP_Query arguments for “most recent” related posts on the SEO Expert template.
		 *
		 * @param array $args              Query arguments.
		 * @param int   $landing_page_id Landing page ID.
		 */
		$args  = apply_filters( 'zskeleton_seo_expert_recent_blog_query_args', $args, $landing_page_id );
		$query = new WP_Query( $args );
		$posts = $query->posts;
	}

	$posts = array_values( array_filter( $posts, static function ( $p ) {
		return $p instanceof WP_Post;
	} ) );

	/**
	 * Filter related blog posts resolved for the SEO Expert landing.
	 *
	 * @param WP_Post[] $posts             Posts.
	 * @param int       $landing_page_id Landing page ID.
	 * @param string    $mode              `recent` or `selected`.
	 */
	return apply_filters( 'zskeleton_seo_expert_related_blog_posts', $posts, $landing_page_id, $mode );
}

/**
 * Front assets for the SEO Expert landing template (`page-seo-expert.php`) only.
 */
function zskeleton_seo_expert_enqueue_assets() {
	if ( is_admin() ) {
		return;
	}

	$load = is_page_template( 'page-seo-expert.php' );

	/**
	 * Whether to enqueue SEO Expert front CSS, fonts, and hero script on this request.
	 *
	 * Default is true only when the page uses `page-seo-expert.php`. Return true to load
	 * on additional templates (not recommended unless markup matches).
	 *
	 * @param bool $load Whether to enqueue (after template gate).
	 */
	$load = (bool) apply_filters( 'zskeleton_enqueue_seo_expert_css', $load );
	if ( ! $load ) {
		return;
	}

	wp_enqueue_style(
		'zskeleton-seo-expert-font-tajawal',
		'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	$use_min     = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$seo_css     = $use_min && is_readable( get_template_directory() . '/assets/css/seo-expert.min.css' )
		? 'seo-expert.min.css'
		: 'seo-expert.css';
	$seo_css_path = get_template_directory() . '/assets/css/' . $seo_css;

	$seo_theme_dep = function_exists( 'zskeleton_theme_css_handle_for_style_dependency' ) ? zskeleton_theme_css_handle_for_style_dependency() : 'zskeleton-style';
	wp_enqueue_style(
		'zskeleton-seo-expert',
		get_template_directory_uri() . '/assets/css/' . $seo_css,
		array( 'zskeleton-seo-expert-font-tajawal', $seo_theme_dep ),
		is_readable( $seo_css_path ) ? (string) filemtime( $seo_css_path ) : ZSkeleton_VERSION
	);

	$inline = zskeleton_seo_expert_get_inline_theme_css();
	if ( '' !== $inline ) {
		wp_add_inline_style( 'zskeleton-seo-expert', $inline );
	}

	$use_minified   = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$particles_file = $use_minified && is_readable( get_template_directory() . '/assets/js/seo-expert-hero-particles.min.js' )
		? 'seo-expert-hero-particles.min.js'
		: 'seo-expert-hero-particles.js';
	$particles_path = get_template_directory() . '/assets/js/' . $particles_file;
	$particles_ver    = is_readable( $particles_path ) ? (string) filemtime( $particles_path ) : ZSkeleton_VERSION;
	wp_enqueue_script(
		'zskeleton-seo-expert-hero-particles',
		get_template_directory_uri() . '/assets/js/' . $particles_file,
		array(),
		$particles_ver,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'zskeleton_seo_expert_enqueue_assets', 20 );

/**
 * Lead form column: reuse Arabic SEO lead pipeline + alias filter.
 */
function zskeleton_seo_expert_render_lead_column() {
	if ( function_exists( 'zskeleton_seo_ar_render_lead_form_column' ) ) {
		zskeleton_seo_ar_render_lead_form_column();
	}
}

/**
 * On first save with SEO Expert template, merge defaults if expert name empty.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function zskeleton_seo_expert_maybe_apply_on_save( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
		return;
	}
	if ( ! $post || 'page' !== $post->post_type ) {
		return;
	}

	$tpl = get_page_template_slug( $post );
	if ( 'page-seo-expert.php' !== $tpl ) {
		return;
	}

	$name = get_post_meta( $post_id, ZSkeleton_Seo_Expert_Meta::meta_key( 'expert_name' ), true );
	if ( '' !== $name && false !== $name ) {
		return;
	}

	zskeleton_seo_expert_apply_defaults_if_empty( $post_id );
}
add_action( 'save_post_page', 'zskeleton_seo_expert_maybe_apply_on_save', 30, 2 );

/**
 * Create default SEO Expert page once (أحمد مكي) if missing.
 */
function zskeleton_seo_expert_bootstrap_page() {
	$stored = get_option( 'zskeleton_seo_expert_page_id', 0 );
	$stored = absint( $stored );
	if ( $stored && get_post_status( $stored ) ) {
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => __( 'خبير سيو — أحمد مكي', 'zskeleton' ),
			'post_status'  => 'publish',
			'post_type'      => 'page',
			'post_content' => '',
		),
		true
	);

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	update_post_meta( (int) $page_id, '_wp_page_template', 'page-seo-expert.php' );
	zskeleton_seo_expert_apply_defaults_if_empty( (int) $page_id );
	update_option( 'zskeleton_seo_expert_page_id', (int) $page_id );
}


/**
 * Ensure demo page exists on first admin visit (if theme was already active).
 */
function zskeleton_seo_expert_bootstrap_page_admin() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	zskeleton_seo_expert_bootstrap_page();
}

/**
 * Output JSON-LD on SEO Expert template.
 */
function zskeleton_seo_expert_wp_head_json_ld() {
	if ( ! is_page_template( 'page-seo-expert.php' ) ) {
		return;
	}
	zskeleton_seo_expert_print_json_ld( (int) get_queried_object_id() );
}
add_action( 'wp_head', 'zskeleton_seo_expert_wp_head_json_ld', 5 );

new ZSkeleton_Seo_Expert_Meta();
