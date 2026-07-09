<?php
/**
 * Default attribute values for the Arabic SEO AI lead block.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize HTML id / anchor fragment (ASCII letters, digits, hyphen, underscore).
 *
 * @param string $id Raw id.
 * @return string
 */
function zskeleton_seo_ar_ai_lead_sanitize_html_id( $id ) {
	$s = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $id );
	return is_string( $s ) ? $s : '';
}

/**
 * Whether stored block body HTML is empty for fallback purposes (RichText often saves `<p></p>`).
 *
 * @param string $html Raw HTML.
 * @return bool
 */
function zskeleton_seo_ar_ai_lead_content_is_effectively_empty( $html ) {
	$t = trim( (string) $html );
	if ( '' === $t ) {
		return true;
	}
	$plain = trim( wp_strip_all_tags( $t, true ) );

	return '' === $plain;
}

/**
 * Sanitize Dashicons glyph slug (letters, digits, hyphen only).
 *
 * @param string $slug Raw slug without `dashicons-` prefix.
 * @return string
 */
function zskeleton_seo_ar_ai_lead_sanitize_dashicon_slug( $slug ) {
	$s = preg_replace( '/[^a-z0-9-]/', '', strtolower( (string) $slug ) );

	return is_string( $s ) ? $s : '';
}

/**
 * Allowed title separator layout keys.
 *
 * @return string[]
 */
function zskeleton_seo_ar_ai_lead_title_separator_styles(): array {
	return array( 'none', 'line', 'gradient', 'dots', 'character' );
}

/**
 * Allowed title separator alignment keys (logical start/center/full width).
 *
 * @return string[]
 */
function zskeleton_seo_ar_ai_lead_title_separator_aligns(): array {
	return array( 'start', 'center', 'full' );
}

/**
 * Sanitize title icon type.
 *
 * @param string $type Raw.
 * @return string `dashicon` or `image`.
 */
function zskeleton_seo_ar_ai_lead_sanitize_title_icon_type( $type ) {
	$t = strtolower( trim( (string) $type ) );

	return 'image' === $t ? 'image' : 'dashicon';
}

/**
 * Sanitize short plain-text separator character / string.
 *
 * @param string $text Raw.
 * @return string
 */
function zskeleton_seo_ar_ai_lead_sanitize_separator_character( $text ) {
	$t = preg_replace( '/[\r\n\t\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $text );
	if ( function_exists( 'mb_substr' ) ) {
		$t = mb_substr( $t, 0, 48, 'UTF-8' );
	} else {
		$t = substr( $t, 0, 48 );
	}

	return trim( $t );
}

/**
 * Echo heading row: optional icon + H2 + decorative separator (replaces legacy full-width section borders on this band).
 *
 * @param array<string, mixed> $args Arguments: heading_id (string), title (string), title_icon_enabled (bool),
 *                                   title_icon_type (string), title_icon_dashicon (string), title_icon_image_id (int),
 *                                   title_separator_style (string), title_separator_align (string),
 *                                   title_separator_color (string hex or empty), title_separator_character (string).
 * @return void
 */
function zskeleton_seo_ar_ai_lead_render_title_stack( array $args ): void {
	$defaults = array(
		'heading_id'                => 'seo-ar-ai-lead-heading',
		'title'                     => '',
		'title_icon_enabled'        => false,
		'title_icon_type'           => 'dashicon',
		'title_icon_dashicon'       => 'chart-line',
		'title_icon_image_id'       => 0,
		'title_separator_style'     => 'line',
		'title_separator_align'     => 'start',
		'title_separator_color'     => '',
		'title_separator_character' => '—',
	);
	$a = wp_parse_args( $args, $defaults );

	$heading_id = zskeleton_seo_ar_ai_lead_sanitize_html_id( (string) $a['heading_id'] );
	if ( '' === $heading_id ) {
		$heading_id = 'seo-ar-ai-lead-heading';
	}

	$title = (string) $a['title'];

	$icon_on   = ! empty( $a['title_icon_enabled'] );
	$icon_type = zskeleton_seo_ar_ai_lead_sanitize_title_icon_type( (string) $a['title_icon_type'] );
	$dash_slug = zskeleton_seo_ar_ai_lead_sanitize_dashicon_slug( (string) $a['title_icon_dashicon'] );
	if ( '' === $dash_slug ) {
		$dash_slug = 'chart-line';
	}
	$img_id = (int) $a['title_icon_image_id'];

	$sep_style = strtolower( trim( (string) $a['title_separator_style'] ) );
	if ( ! in_array( $sep_style, zskeleton_seo_ar_ai_lead_title_separator_styles(), true ) ) {
		$sep_style = 'line';
	}
	$sep_align = strtolower( trim( (string) $a['title_separator_align'] ) );
	if ( ! in_array( $sep_align, zskeleton_seo_ar_ai_lead_title_separator_aligns(), true ) ) {
		$sep_align = 'start';
	}
	$sep_color = trim( (string) $a['title_separator_color'] );
	if ( '' !== $sep_color && ! preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $sep_color ) ) {
		$sep_color = '';
	}
	$sep_char = zskeleton_seo_ar_ai_lead_sanitize_separator_character( (string) $a['title_separator_character'] );
	if ( '' === $sep_char && 'character' === $sep_style ) {
		$sep_char = '—';
	}

	if ( $icon_on && ( 'dashicon' === $icon_type || $img_id < 1 ) ) {
		wp_enqueue_style( 'dashicons' );
	}

	$stack_classes = array( 'seo-ar-ai-lead-title-stack', 'seo-ar-ai-lead-title-stack--sep-' . sanitize_html_class( $sep_style ) );
	?>
	<div class="<?php echo esc_attr( implode( ' ', $stack_classes ) ); ?>">
		<div class="seo-ar-ai-lead-title-row">
			<?php if ( $icon_on ) : ?>
				<span class="seo-ar-ai-lead-title-icon" aria-hidden="true">
					<?php if ( 'image' === $icon_type && $img_id > 0 ) : ?>
						<?php
						echo wp_get_attachment_image(
							$img_id,
							'thumbnail',
							false,
							array(
								'class'   => 'seo-ar-ai-lead-title-icon__img',
								'loading' => 'lazy',
								'decoding' => 'async',
							)
						);
						?>
					<?php else : ?>
						<span class="dashicons dashicons-<?php echo esc_attr( $dash_slug ); ?>"></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
			<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></h2>
		</div>
		<?php if ( 'none' !== $sep_style ) : ?>
			<?php
			$sep_classes = array(
				'seo-ar-ai-lead-title-separator',
				'seo-ar-ai-lead-title-separator--' . sanitize_html_class( $sep_style ),
				'seo-ar-ai-lead-title-separator--align-' . sanitize_html_class( $sep_align ),
			);
			$sep_style_attr = '';
			if ( '' !== $sep_color ) {
				$sep_style_attr = '--seo-ar-ai-lead-sep-color:' . esc_attr( $sep_color ) . ';';
			}
			?>
			<div class="<?php echo esc_attr( implode( ' ', $sep_classes ) ); ?>" style="<?php echo esc_attr( $sep_style_attr ); ?>" aria-hidden="true">
				<?php if ( 'character' === $sep_style ) : ?>
					<span class="seo-ar-ai-lead-title-separator__char"><?php echo esc_html( $sep_char ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Apply URL/name tokens to combined lead HTML (legacy + new content field).
 *
 * @param string $html Raw HTML.
 * @param string $case_url Resolved case-study URL.
 * @param string $site_name Site name for name tokens.
 * @return string
 */
function zskeleton_seo_ar_ai_lead_apply_content_tokens( $html, $case_url, $site_name ) {
	$html = str_replace( '%%CASE_STUDY_URL%%', esc_url( $case_url ), (string) $html );
	return str_replace(
		array( '%%EXPERT_NAME%%', '%%SITE_NAME%%' ),
		esc_html( $site_name ),
		$html
	);
}

/**
 * Build one HTML blob from pre-simplified block attributes (backward compatibility).
 *
 * @param array<string, mixed> $raw Original block attributes from the saved block.
 * @return string
 */
function zskeleton_seo_ar_ai_lead_build_legacy_combined_html( array $raw ) {
	$site_name = get_bloginfo( 'name' );
	$case_url  = trim( (string) ( $raw['caseStudyUrl'] ?? '' ) );
	if ( '' === $case_url ) {
		$case_url = (string) apply_filters( 'zskeleton_seo_ar_case_study_portfolio_url', home_url( '/' ) );
	} else {
		$case_url = esc_url( $case_url );
	}

	$intro      = (string) ( $raw['introHtml'] ?? '' );
	$sub_warn   = (string) ( $raw['subheadWarn'] ?? '' );
	$warn_body  = (string) ( $raw['warnBodyHtml'] ?? '' );
	$sub_why_in = (string) ( $raw['subheadWhy'] ?? '' );
	$why_p1     = (string) ( $raw['whyP1Html'] ?? '' );
	$why_p2     = (string) ( $raw['whyP2Html'] ?? '' );

	$sub_why = '' === trim( $sub_why_in )
		? sprintf( 'لماذا %s؟', $site_name )
		: $sub_why_in;

	$warn_body = str_replace( '%%CASE_STUDY_URL%%', esc_url( $case_url ), $warn_body );
	$why_p1    = str_replace( array( '%%EXPERT_NAME%%', '%%SITE_NAME%%' ), esc_html( $site_name ), $why_p1 );

	$parts = array();
	if ( '' !== trim( wp_strip_all_tags( $intro ) ) ) {
		$parts[] = '<p class="seo-ar-lead-text">' . wp_kses_post( $intro ) . '</p>';
	}
	if ( '' !== trim( $sub_warn ) ) {
		$parts[] = '<h3 class="seo-ar-ai-lead-subhead">' . esc_html( $sub_warn ) . '</h3>';
	}
	if ( '' !== trim( wp_strip_all_tags( $warn_body ) ) ) {
		$parts[] = '<p class="seo-ar-lead-text">' . wp_kses_post( $warn_body ) . '</p>';
	}
	if ( '' !== trim( $sub_why ) ) {
		$parts[] = '<h3 class="seo-ar-ai-lead-subhead">' . esc_html( $sub_why ) . '</h3>';
	}
	if ( '' !== trim( wp_strip_all_tags( $why_p1 ) ) ) {
		$parts[] = '<p class="seo-ar-lead-text">' . wp_kses_post( $why_p1 ) . '</p>';
	}
	if ( '' !== trim( wp_strip_all_tags( $why_p2 ) ) ) {
		$parts[] = '<p class="seo-ar-lead-text">' . wp_kses_post( $why_p2 ) . '</p>';
	}

	return implode( '', $parts );
}

/**
 * Resolve final content HTML: new `content` attribute, else legacy fields, else default.
 *
 * @param array<string, mixed> $raw Attributes as saved (REST may send empty strings for new blocks).
 * @return string
 */
function zskeleton_seo_ar_ai_lead_resolve_content_html( array $raw ) {
	$site_name = get_bloginfo( 'name' );
	$case_url  = trim( (string) ( $raw['caseStudyUrl'] ?? '' ) );
	if ( '' === $case_url ) {
		$case_url = (string) apply_filters( 'zskeleton_seo_ar_case_study_portfolio_url', home_url( '/' ) );
	} else {
		$case_url = esc_url( $case_url );
	}

	$content = isset( $raw['content'] ) ? (string) $raw['content'] : '';
	if ( '' !== $content && ! zskeleton_seo_ar_ai_lead_content_is_effectively_empty( $content ) ) {
		return zskeleton_seo_ar_ai_lead_apply_content_tokens( $content, $case_url, $site_name );
	}

	$legacy_keys = array( 'introHtml', 'subheadWarn', 'warnBodyHtml', 'subheadWhy', 'whyP1Html', 'whyP2Html' );
	foreach ( $legacy_keys as $k ) {
		if ( ! empty( trim( (string) ( $raw[ $k ] ?? '' ) ) ) ) {
			return zskeleton_seo_ar_ai_lead_build_legacy_combined_html( $raw );
		}
	}

	// REST often sends content: "" for new blocks; use PHP defaults, not the merged empty string.
	$fallback = zskeleton_seo_ar_ai_lead_get_default_attributes();

	return zskeleton_seo_ar_ai_lead_apply_content_tokens(
		(string) ( $fallback['content'] ?? '' ),
		$case_url,
		$site_name
	);
}

/**
 * Default attributes for zskeleton/seo-ar-ai-lead.
 *
 * @return array<string, string|bool>
 */
function zskeleton_seo_ar_ai_lead_get_default_attributes(): array {
	$d_intro = 'استراتيجيات <span class="seo-ar-text-highlight">سيو</span> و<span class="seo-ar-text-highlight">تصميم المواقع</span> و<span class="seo-ar-text-highlight">PPC</span> و<span class="seo-ar-text-highlight">وسائل التواصل</span> و<span class="seo-ar-text-highlight">البريد الإلكتروني</span> لدينا مبنية على بيانات صلبة وتقنيات مختبرة علمياً. ونحن أيضاً في طليعة <span class="seo-ar-text-highlight">سيو الذكاء الاصطناعي</span>، لتحسين زيارات الموقع من نماذج لغوية كبيرة مثل ChatGPT وClaude وPerplexity وGemini.';
	$d_warn  = 'بعض الشركات وما يُسمّى «خبراء» حضروا دورة أو قرأوا مدونات ويستخدمون مصطلحات مثل «SEO» و«PPC» ويظنون أنهم يقدمون الخدمات. احذر أي وكالة لا تملك أمثلة عمل قوية مثل <a class="seo-ar-text-link" href="%%CASE_STUDY_URL%%">معرض دراسات الحالة</a> لدينا.';
	$d_why1  = 'أعضاء فريقنا لديهم مجموعاً يزيد عن ثمانية عشر قرناً من خبرة وكالات التسويق الرقمي. لا توجد شركة سيو أخرى بفريق مؤهل مثل %%EXPERT_NAME%%.';
	$d_why2  = 'نوظّف فقط أفضل 1% من المتقدمين الذين اختبرنا مهاراتهم، وتدريبهم الصارم يشمل دوراتنا على مستوى جامعي. لدينا أنظمة متقدمة وأدوات مدعومة بالذكاء الاصطناعي لضمان أعلى جودة في عملنا—بما في ذلك التميز في <span class="seo-ar-text-highlight">التسويق الرقمي</span>.';

	$default_content = sprintf(
		'<p class="seo-ar-lead-text">%s</p><h3 class="seo-ar-ai-lead-subhead">%s</h3><p class="seo-ar-lead-text">%s</p><h3 class="seo-ar-ai-lead-subhead">%s</h3><p class="seo-ar-lead-text">%s</p><p class="seo-ar-lead-text">%s</p>',
		$d_intro,
		'احذر المدّعين',
		$d_warn,
		'لماذا %%SITE_NAME%%؟',
		$d_why1,
		$d_why2
	);

	return array(
		'title'                     => 'شركة خدمات سيو لعصر الذكاء الاصطناعي',
		'content'                   => $default_content,
		'titleIconEnabled'          => false,
		'titleIconType'             => 'dashicon',
		'titleIconDashicon'         => 'chart-line',
		'titleIconImageId'          => 0,
		'titleSeparatorStyle'       => 'line',
		'titleSeparatorAlign'       => 'start',
		'titleSeparatorColor'       => '',
		'titleSeparatorCharacter'   => '—',
		'sectionHtmlId'             => 'ai-lead',
		'headingWrapperId'          => 'seo-ar-ai-lead-heading',
		'sectionAriaLabel'          => '',
		'innerContainerClass'       => '',
		'structuredDataEnabled'     => false,
		'schemaDescription'         => '',
		'speakableJsonLdEnabled'    => false,
		'speakableCssSelectors'     => '#seo-ar-ai-lead-heading,.seo-ar-ai-lead-body',
	);
}
