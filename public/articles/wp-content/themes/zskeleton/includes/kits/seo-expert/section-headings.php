<?php
/**
 * SEO Expert section headings — editable H2 (and related labels) via post meta.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Section heading field keys, default copy, and icon keys for front-end rendering.
 *
 * @return array<string,array{label:string,default:string,icon:string}>
 */
function zskeleton_seo_expert_section_heading_registry() {
	$registry = array(
		'heading_why_us'            => array(
			'label'   => __( 'Why choose us', 'zskeleton' ),
			'default' => 'لماذا تختارنا؟',
			'icon'    => 'target',
		),
		'heading_services'          => array(
			'label'   => __( 'Services', 'zskeleton' ),
			'default' => 'خدماتنا',
			'icon'    => 'briefcase',
		),
		'heading_methodology'       => array(
			'label'   => __( 'How we work', 'zskeleton' ),
			'default' => 'كيف نعمل؟',
			'icon'    => 'route',
		),
		'heading_tools'             => array(
			'label'   => __( 'Tools & technologies', 'zskeleton' ),
			'default' => 'أدوات وتقنيات',
			'icon'    => 'wrench',
		),
		'heading_arabic_market'     => array(
			'label'   => __( 'Why Arabic market', 'zskeleton' ),
			'default' => 'لماذا تحتاج خبيراً يفهم السوق العربي؟',
			'icon'    => 'globe',
		),
		'heading_results_steps'     => array(
			'label'   => __( 'Search results', 'zskeleton' ),
			'default' => 'كيف نحقق نتائج في البحث؟',
			'icon'    => 'chart',
		),
		'heading_success_factors'   => array(
			'label'   => __( 'Campaign success factors', 'zskeleton' ),
			'default' => 'عوامل نجاح الحملة',
			'icon'    => 'target',
		),
		'heading_how_to_choose'     => array(
			'label'   => __( 'How to choose an expert', 'zskeleton' ),
			'default' => 'كيف تختار خبير سيو؟',
			'icon'    => 'help',
		),
		'heading_pricing'           => array(
			'label'   => __( 'Pricing', 'zskeleton' ),
			'default' => 'الأسعار',
			'icon'    => 'currency',
		),
		'heading_memberships'       => array(
			'label'   => __( 'Membership plans', 'zskeleton' ),
			'default' => 'اختر عضويتك',
			'icon'    => 'sparkles',
		),
		'heading_faq'               => array(
			'label'   => __( 'FAQ', 'zskeleton' ),
			'default' => 'أسئلة شائعة',
			'icon'    => 'help',
		),
		'heading_cta_band'          => array(
			'label'   => __( 'CTA band heading (use %%EXPERT_NAME%%)', 'zskeleton' ),
			'default' => 'ابدأ مع %%EXPERT_NAME%%',
			'icon'    => 'sparkles',
		),
		'heading_blog_links'        => array(
			'label'   => __( 'Related articles', 'zskeleton' ),
			'default' => 'مقالات ذات صلة',
			'icon'    => 'book',
		),
		'heading_stats_aria'        => array(
			'label'   => __( 'Stats strip — accessibility label', 'zskeleton' ),
			'default' => 'أرقام مختصرة',
			'icon'    => '',
		),
		'heading_ratings_aria'      => array(
			'label'   => __( 'Ratings strip — accessibility label', 'zskeleton' ),
			'default' => 'تقييمات',
			'icon'    => '',
		),
		'heading_process_aria'      => array(
			'label'   => __( 'Methodology + tools — accessibility label', 'zskeleton' ),
			'default' => 'المنهجية والأدوات',
			'icon'    => '',
		),
		'intro_trust_years_label'   => array(
			'label'   => __( 'Intro trust card — years label', 'zskeleton' ),
			'default' => 'سنوات خبرة',
			'icon'    => '',
		),
	);

	/**
	 * @param array<string,array{label:string,default:string,icon:string}> $registry
	 */
	return apply_filters( 'zskeleton_seo_expert_section_heading_registry', $registry );
}

/**
 * Meta box field config (text inputs only).
 *
 * @return array<string,array<string,mixed>>
 */
function zskeleton_seo_expert_section_heading_field_config() {
	$out = array();
	foreach ( zskeleton_seo_expert_section_heading_registry() as $key => $row ) {
		$out[ $key ] = array(
			'label' => $row['label'],
			'type'  => 'text',
		);
	}
	return $out;
}

/**
 * Default scalar values for merge-on-empty.
 *
 * @return array<string,string>
 */
function zskeleton_seo_expert_section_heading_defaults() {
	$out = array();
	foreach ( zskeleton_seo_expert_section_heading_registry() as $key => $row ) {
		$out[ $key ] = $row['default'];
	}
	return $out;
}

/**
 * Get a section heading (falls back to registry default).
 *
 * @param int    $post_id Post ID.
 * @param string $key     Registry key (e.g. heading_why_us).
 * @return string
 */
function zskeleton_seo_expert_get_section_heading( $post_id, $key ) {
	$post_id = (int) $post_id;
	$key     = sanitize_key( $key );
	$registry = zskeleton_seo_expert_section_heading_registry();
	if ( ! isset( $registry[ $key ] ) ) {
		return '';
	}

	$fallback = $registry[ $key ]['default'];
	return zskeleton_seo_expert_get( $post_id, $key, $fallback );
}

/**
 * Icon key for a section heading.
 *
 * @param string $key Registry key.
 * @return string
 */
function zskeleton_seo_expert_section_heading_icon( $key ) {
	$registry = zskeleton_seo_expert_section_heading_registry();
	return isset( $registry[ $key ]['icon'] ) ? (string) $registry[ $key ]['icon'] : 'sparkles';
}

/**
 * Render an H2 with theme icon wrapper (skips output when title empty).
 *
 * @param int    $post_id Post ID.
 * @param string $key     Registry key.
 */
function zskeleton_seo_expert_render_section_heading( $post_id, $key ) {
	$title = zskeleton_seo_expert_get_section_heading( $post_id, $key );
	if ( '' === trim( $title ) ) {
		return;
	}

	$icon = zskeleton_seo_expert_section_heading_icon( $key );
	if ( function_exists( 'zskeleton_seo_expert_section_heading' ) && '' !== $icon ) {
		zskeleton_seo_expert_section_heading( $title, $icon );
		return;
	}

	echo '<h2 class="seo-expert-section__title"><span class="seo-expert-section__title-text">' . esc_html( $title ) . '</span></h2>';
}

/**
 * CTA band heading with %%EXPERT_NAME%% replaced.
 *
 * @param int    $post_id     Post ID.
 * @param string $expert_name Expert display name.
 * @return string
 */
function zskeleton_seo_expert_get_cta_band_heading( $post_id, $expert_name ) {
	$raw = zskeleton_seo_expert_get_section_heading( $post_id, 'heading_cta_band' );
	if ( false !== strpos( $raw, '%%EXPERT_NAME%%' ) ) {
		return str_replace( '%%EXPERT_NAME%%', $expert_name, $raw );
	}
	if ( '' !== trim( $raw ) ) {
		return $raw;
	}
	return sprintf( 'ابدأ مع %s', $expert_name );
}
