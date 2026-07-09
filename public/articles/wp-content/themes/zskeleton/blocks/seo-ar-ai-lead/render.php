<?php
/**
 * Dynamic render: Arabic SEO homepage AI lead section (title + body HTML + lead form).
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var string        $content    Inner blocks (unused).
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

require_once trailingslashit( get_template_directory() ) . 'includes/blocks/seo-ar-ai-lead-defaults.php';

$raw      = is_array( $attributes ) ? $attributes : array();
$defaults = zskeleton_seo_ar_ai_lead_get_default_attributes();
// Empty strings from the editor / block.json must not replace theme default copy (fixes empty ServerSideRender).
if ( ! isset( $raw['title'] ) || '' === trim( (string) $raw['title'] ) ) {
	unset( $raw['title'] );
}
if ( ! isset( $raw['content'] ) || zskeleton_seo_ar_ai_lead_content_is_effectively_empty( (string) $raw['content'] ) ) {
	unset( $raw['content'] );
}
$attrs = wp_parse_args( $raw, $defaults );

$section_id = zskeleton_seo_ar_ai_lead_sanitize_html_id( (string) $attrs['sectionHtmlId'] );
if ( '' === $section_id ) {
	$section_id = 'ai-lead';
}

$heading_id = zskeleton_seo_ar_ai_lead_sanitize_html_id( (string) $attrs['headingWrapperId'] );
if ( '' === $heading_id ) {
	$heading_id = 'seo-ar-ai-lead-heading';
}

$lead_heading = (string) $attrs['title'];
$content_html = zskeleton_seo_ar_ai_lead_resolve_content_html( $raw );

$inner_class = trim( (string) $attrs['innerContainerClass'] );
if ( '' === $inner_class && function_exists( 'zskeleton_page_main_container_class' ) && is_singular() ) {
	$inner_class = zskeleton_page_main_container_class( 'seo-ar-container', '', (int) get_queried_object_id() );
}
if ( '' === $inner_class ) {
	$inner_class = 'seo-ar-container';
}

$aria_label = trim( (string) $attrs['sectionAriaLabel'] );
$aria_attrs = array(
	'aria-labelledby' => $heading_id,
);
if ( '' !== $aria_label ) {
	$aria_attrs['aria-label'] = $aria_label;
}

$wrapper = get_block_wrapper_attributes(
	array_merge(
		array(
			'class' => 'seo-ar-ai-lead zskeleton-block-seo-ar-ai-lead',
			'id'    => $section_id,
			'lang'  => 'ar',
		),
		$aria_attrs
	),
	'',
	isset( $block ) ? $block : null
);

$ld_json = array();

if ( ! empty( $attrs['structuredDataEnabled'] ) ) {
	$permalink = is_singular() ? get_permalink() : home_url( '/' );
	$frag_url  = untrailingslashit( (string) $permalink ) . '#' . rawurlencode( $section_id );
	$desc      = trim( (string) $attrs['schemaDescription'] );
	if ( '' === $desc ) {
		$desc = wp_strip_all_tags( $content_html );
		$desc = function_exists( 'mb_substr' ) ? mb_substr( $desc, 0, 320 ) : substr( $desc, 0, 320 );
	}
	$ld_json[] = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'WebPageElement',
		'name'        => wp_strip_all_tags( $lead_heading ),
		'url'         => esc_url_raw( $frag_url ),
		'description' => $desc,
		'inLanguage'  => 'ar',
	);
}

if ( ! empty( $attrs['speakableJsonLdEnabled'] ) ) {
	$sel = trim( (string) $attrs['speakableCssSelectors'] );
	if ( '' === $sel ) {
		$sel = (string) $defaults['speakableCssSelectors'];
	}
	$parts = preg_split( '/[\r\n,]+/', $sel );
	$parts = is_array( $parts ) ? array_values( array_filter( array_map( 'trim', $parts ) ) ) : array();
	if ( ! empty( $parts ) ) {
		$ld_json[] = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'SpeakableSpecification',
			'cssSelector' => $parts,
		);
	}
}

ob_start();
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes. ?>>
	<div class="<?php echo esc_attr( $inner_class ); ?>">
		<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- flash query args after lead form redirect (same as static template).
		$lead_status = isset( $_GET['lead'] ) ? sanitize_text_field( wp_unslash( $_GET['lead'] ) ) : '';
		if ( 'sent' === $lead_status ) {
			echo '<p class="seo-ar-lead-notice seo-ar-lead-notice--success" role="status">' . esc_html( 'شكراً لك — تم استلام رسالتك.' ) . '</p>';
		} elseif ( 'error' === $lead_status ) {
			echo '<p class="seo-ar-lead-notice seo-ar-lead-notice--error" role="alert">' . esc_html( 'يرجى تعبئة جميع الحقول المطلوبة بشكل صحيح.' ) . '</p>';
		}
		?>
		<div class="seo-ar-ai-lead-grid">
			<div class="seo-ar-ai-lead-copy">
				<?php
				zskeleton_seo_ar_ai_lead_render_title_stack(
					array(
						'heading_id'                => $heading_id,
						'title'                     => $lead_heading,
						'title_icon_enabled'        => ! empty( $attrs['titleIconEnabled'] ),
						'title_icon_type'         => isset( $attrs['titleIconType'] ) ? (string) $attrs['titleIconType'] : 'dashicon',
						'title_icon_dashicon'     => isset( $attrs['titleIconDashicon'] ) ? (string) $attrs['titleIconDashicon'] : 'chart-line',
						'title_icon_image_id'     => isset( $attrs['titleIconImageId'] ) ? (int) $attrs['titleIconImageId'] : 0,
						'title_separator_style'   => isset( $attrs['titleSeparatorStyle'] ) ? (string) $attrs['titleSeparatorStyle'] : 'line',
						'title_separator_align'   => isset( $attrs['titleSeparatorAlign'] ) ? (string) $attrs['titleSeparatorAlign'] : 'start',
						'title_separator_color'   => isset( $attrs['titleSeparatorColor'] ) ? (string) $attrs['titleSeparatorColor'] : '',
						'title_separator_character' => isset( $attrs['titleSeparatorCharacter'] ) ? (string) $attrs['titleSeparatorCharacter'] : '—',
					)
				);
				?>
				<div class="seo-ar-ai-lead-body seo-ar-lead-text"><?php echo wp_kses_post( $content_html ); ?></div>
			</div>
			<div class="seo-ar-ai-lead-form-col">
				<div class="seo-ar-custom-form-box">
					<div class="seo-ar-custom-form-box__inner">
						<div class="seo-ar-custom-form-box__form">
							<?php
							if ( function_exists( 'zskeleton_seo_ar_render_lead_form_column' ) ) {
								zskeleton_seo_ar_render_lead_form_column();
							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php foreach ( $ld_json as $one ) : ?>
		<script type="application/ld+json"><?php echo wp_json_encode( $one, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
	<?php endforeach; ?>
</section>
<?php
$html = (string) ob_get_clean();

if ( function_exists( 'zskeleton_blog_hub_stash_dynamic_blog_block_html' ) ) {
	zskeleton_blog_hub_stash_dynamic_blog_block_html( 'zskeleton/seo-ar-ai-lead', $html );
}

return $html;
