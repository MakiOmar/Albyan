<?php
/**
 * SEO Expert landing page sections (أحمد مكي / configurable via meta).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

global $zskeleton_seo_expert_context;
$ctx = ( isset( $zskeleton_seo_expert_context ) && is_array( $zskeleton_seo_expert_context ) ) ? $zskeleton_seo_expert_context : array();
$post_id = isset( $ctx['post_id'] ) ? (int) $ctx['post_id'] : get_the_ID();
$c       = isset( $ctx['container_class'] ) ? (string) $ctx['container_class'] : 'wide-container';

$name    = zskeleton_seo_expert_get( $post_id, 'expert_name' );
$hero_t  = zskeleton_seo_expert_get( $post_id, 'hero_title' );
$hero_s  = zskeleton_seo_expert_get( $post_id, 'hero_subtitle' );
$years   = zskeleton_seo_expert_get( $post_id, 'years_experience' );
$primary = zskeleton_seo_expert_get( $post_id, 'primary_cta_label' );
$second  = zskeleton_seo_expert_get( $post_id, 'secondary_cta_label' );
$slug    = zskeleton_seo_expert_get( $post_id, 'landing_term_slug' );
if ( '' === $slug ) {
	$slug = 'ahmad-maki';
}

$stats    = zskeleton_get_repeater( $post_id, 'seo_stats' );
$ratings  = zskeleton_get_repeater( $post_id, 'seo_ratings' );
$why_us   = zskeleton_get_repeater( $post_id, 'seo_why_us' );
$method   = zskeleton_get_repeater( $post_id, 'seo_methodology' );
$tools    = zskeleton_get_repeater( $post_id, 'seo_tools' );
$blog_posts = function_exists( 'zskeleton_seo_expert_get_related_blog_posts' )
	? zskeleton_seo_expert_get_related_blog_posts( $post_id )
	: array();

$cta_primary_url   = '#seo-expert-contact';
$cta_secondary_url = '#seo-expert-why';

$hero_media = zskeleton_seo_expert_get_hero_image( $post_id );

global $zskeleton_template_part_args;
$zskeleton_template_part_args = array(
	'title'              => $hero_t,
	'subtitle'           => $hero_s,
	'primary_label'      => $primary,
	'primary_url'        => $cta_primary_url,
	'secondary_label'    => $second,
	'secondary_url'      => $cta_secondary_url,
	'container_class'    => $c,
	'class_prefix'       => 'seo-expert',
	'hero_attachment_id' => isset( $hero_media['attachment_id'] ) ? (int) $hero_media['attachment_id'] : 0,
	'hero_image_url'     => isset( $hero_media['url'] ) ? (string) $hero_media['url'] : '',
	'hero_image_alt'     => isset( $hero_media['alt'] ) ? (string) $hero_media['alt'] : '',
);
get_template_part( 'template-parts/marketing/section-hero-marketing' );

$zskeleton_template_part_args = array(
	'rows'            => $stats,
	'container_class' => $c,
	'class_prefix'    => 'seo-expert',
	'aria_label'      => 'أرقام مختصرة',
);
get_template_part( 'template-parts/marketing/section-stat-strip' );

if ( ! empty( $ratings ) ) :
	?>
	<section class="seo-expert-ratings" aria-label="<?php echo esc_attr( 'تقييمات' ); ?>">
		<div class="<?php echo esc_attr( $c ); ?>">
			<div class="seo-expert-ratings__grid">
				<?php foreach ( $ratings as $r ) : ?>
					<?php
					if ( ! is_array( $r ) ) {
						continue;
					}
					?>
					<div class="seo-expert-ratings__col">
						<p class="seo-expert-ratings__score"><?php echo esc_html( isset( $r['score'] ) ? $r['score'] : '' ); ?> <span aria-hidden="true">★</span></p>
						<p class="seo-expert-ratings__platform"><?php echo esc_html( isset( $r['platform'] ) ? $r['platform'] : '' ); ?></p>
						<p class="seo-expert-ratings__count"><?php echo esc_html( isset( $r['count'] ) ? $r['count'] : '' ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
endif;

$intro = zskeleton_seo_expert_get( $post_id, 'intro_body' );
if ( $intro !== '' ) :
	?>
	<section class="seo-expert-section seo-expert-intro seo-expert-section--mesh" id="seo-expert-intro">
		<div class="<?php echo esc_attr( $c ); ?> seo-expert-intro__grid">
			<div class="seo-expert-intro__main">
				<?php if ( function_exists( 'zskeleton_seo_expert_icon' ) ) : ?>
					<div class="seo-expert-intro__badge" aria-hidden="true"><?php echo zskeleton_seo_expert_icon( 'sparkles' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG via wp_kses(). ?></div>
				<?php endif; ?>
				<div class="seo-expert-prose"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
			</div>
			<?php if ( '' !== $years || '' !== $name ) : ?>
				<aside class="seo-expert-intro__aside">
					<div class="seo-expert-trust-card">
						<?php if ( '' !== $years ) : ?>
							<p class="seo-expert-trust-card__figure"><span class="seo-expert-trust-card__num"><?php echo esc_html( $years ); ?></span><span class="seo-expert-trust-card__plus" aria-hidden="true">+</span></p>
							<p class="seo-expert-trust-card__label"><?php echo esc_html( 'سنوات خبرة' ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $name ) : ?>
							<p class="seo-expert-trust-card__name"><?php echo esc_html( $name ); ?></p>
						<?php endif; ?>
						<a class="seo-expert-trust-card__cta seo-expert-btn seo-expert-btn--primary" href="<?php echo esc_url( $cta_primary_url ); ?>"><?php echo esc_html( $primary ); ?></a>
					</div>
				</aside>
			<?php endif; ?>
		</div>
	</section>
	<?php
endif;
?>

<section class="seo-expert-section seo-expert-section--brand-tint" id="seo-expert-why">
	<div class="<?php echo esc_attr( $c ); ?>">
		<?php
		if ( function_exists( 'zskeleton_seo_expert_section_heading' ) ) {
			zskeleton_seo_expert_section_heading( 'لماذا تختارنا؟', 'target' );
		} else {
			echo '<h2 class="seo-expert-section__title">' . esc_html( 'لماذا تختارنا؟' ) . '</h2>';
		}
		?>
		<ol class="seo-expert-why-list">
			<?php foreach ( $why_us as $i => $row ) : ?>
				<?php
				if ( ! is_array( $row ) ) {
					continue;
				}
				$t = isset( $row['title'] ) ? $row['title'] : '';
				$b = isset( $row['body'] ) ? $row['body'] : '';
				if ( $t === '' && $b === '' ) {
					continue;
				}
				?>
				<li class="seo-expert-why-list__item">
					<?php if ( function_exists( 'zskeleton_seo_expert_icon' ) ) : ?>
						<span class="seo-expert-why-list__icon" aria-hidden="true"><?php echo zskeleton_seo_expert_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG via wp_kses(). ?></span>
					<?php endif; ?>
					<div class="seo-expert-why-list__body">
					<h3 class="seo-expert-why-list__title"><?php echo esc_html( $t ); ?></h3>
					<?php if ( $b !== '' ) : ?>
						<div class="seo-expert-prose"><?php echo wp_kses_post( wpautop( $b ) ); ?></div>
					<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<section class="seo-expert-section seo-expert-section--alt seo-expert-section--mesh" id="seo-expert-services">
	<div class="<?php echo esc_attr( $c ); ?>">
		<?php
		if ( function_exists( 'zskeleton_seo_expert_section_heading' ) ) {
			zskeleton_seo_expert_section_heading( 'خدماتنا', 'briefcase' );
		} else {
			echo '<h2 class="seo-expert-section__title">' . esc_html( 'خدماتنا' ) . '</h2>';
		}
		?>
		<div class="seo-expert-services-grid">
			<?php
			$svcs = zskeleton_get_services_by_landing( $slug, 20 );
			if ( ! empty( $svcs ) ) :
				foreach ( $svcs as $sp ) :
					?>
					<article class="seo-expert-service-card">
						<?php if ( function_exists( 'zskeleton_seo_expert_icon' ) ) : ?>
							<span class="seo-expert-service-card__icon" aria-hidden="true"><?php echo zskeleton_seo_expert_icon( 'briefcase' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG via wp_kses(). ?></span>
						<?php endif; ?>
						<h3 class="seo-expert-service-card__title"><?php echo esc_html( get_the_title( $sp ) ); ?></h3>
						<?php if ( has_excerpt( $sp ) ) : ?>
							<p class="seo-expert-service-card__excerpt"><?php echo esc_html( get_the_excerpt( $sp ) ); ?></p>
						<?php endif; ?>
					</article>
					<?php
				endforeach;
			else :
				?>
				<p class="seo-expert-empty"><?php echo esc_html( 'أضف خدماتاً ووسمها بـ Landings لهذه الصفحة.' ); ?></p>
				<?php
			endif;
			?>
		</div>
	</div>
</section>

<section class="seo-expert-section seo-expert-section--pair seo-expert-section--mesh" id="seo-expert-process" aria-label="<?php echo esc_attr( 'المنهجية والأدوات' ); ?>">
	<div class="<?php echo esc_attr( $c ); ?>">
		<div class="seo-expert-pair-layout">
			<div class="seo-expert-pair-layout__col seo-expert-pair-layout__col--method" id="seo-expert-method">
				<?php
				if ( function_exists( 'zskeleton_seo_expert_section_heading' ) ) {
					zskeleton_seo_expert_section_heading( 'كيف نعمل؟', 'route' );
				} else {
					echo '<h2 class="seo-expert-section__title">' . esc_html( 'كيف نعمل؟' ) . '</h2>';
				}
				?>
				<ol class="seo-expert-steps">
					<?php foreach ( $method as $row ) : ?>
						<?php
						if ( ! is_array( $row ) ) {
							continue;
						}
						$t = isset( $row['step_title'] ) ? $row['step_title'] : '';
						$b = isset( $row['step_body'] ) ? $row['step_body'] : '';
						if ( $t === '' && $b === '' ) {
							continue;
						}
						?>
						<li class="seo-expert-steps__item">
							<strong class="seo-expert-steps__title"><?php echo esc_html( $t ); ?></strong>
							<?php if ( $b !== '' ) : ?>
								<div class="seo-expert-steps__body seo-expert-prose"><?php echo wp_kses_post( wpautop( $b ) ); ?></div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
			<div class="seo-expert-pair-layout__col seo-expert-pair-layout__col--tools" id="seo-expert-tools">
				<?php
				if ( function_exists( 'zskeleton_seo_expert_section_heading' ) ) {
					zskeleton_seo_expert_section_heading( 'أدوات وتقنيات', 'wrench' );
				} else {
					echo '<h2 class="seo-expert-section__title">' . esc_html( 'أدوات وتقنيات' ) . '</h2>';
				}
				?>
				<ul class="seo-expert-tools">
					<?php foreach ( $tools as $row ) : ?>
						<?php
						if ( ! is_array( $row ) ) {
							continue;
						}
						$n = isset( $row['name'] ) ? $row['name'] : '';
						$d = isset( $row['description'] ) ? $row['description'] : '';
						if ( $n === '' ) {
							continue;
						}
						?>
						<li class="seo-expert-tools__item">
							<?php if ( function_exists( 'zskeleton_seo_expert_icon' ) ) : ?>
								<span class="seo-expert-tools__check" aria-hidden="true"><?php echo zskeleton_seo_expert_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG via wp_kses(). ?></span>
							<?php endif; ?>
							<div class="seo-expert-tools__text">
								<strong class="seo-expert-tools__name"><?php echo esc_html( $n ); ?></strong>
								<?php if ( $d !== '' ) : ?>
									<div class="seo-expert-tools__desc seo-expert-prose"><?php echo wp_kses_post( wpautop( $d ) ); ?></div>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</section>

<?php
$blocks = array(
	'prose_arabic_market'   => 'لماذا تحتاج خبيراً يفهم السوق العربي؟',
	'prose_results_steps'   => 'كيف نحقق نتائج في البحث؟',
	'prose_success_factors' => 'عوامل نجاح الحملة',
	'prose_how_to_choose'   => 'كيف تختار خبير سيو؟',
);
$block_icons = array(
	'prose_arabic_market'   => 'globe',
	'prose_results_steps'   => 'chart',
	'prose_success_factors' => 'target',
	'prose_how_to_choose'   => 'help',
);
$block_i = 0;
foreach ( $blocks as $meta_key => $heading ) {
	$body = zskeleton_seo_expert_get( $post_id, $meta_key );
	if ( '' === $body ) {
		continue;
	}
	$icon_key = isset( $block_icons[ $meta_key ] ) ? $block_icons[ $meta_key ] : 'sparkles';
	$sec_mod  = ( 0 === $block_i % 2 ) ? ' seo-expert-section--mesh' : ' seo-expert-section--brand-tint';
	$flip     = ( 0 === $block_i % 2 ) ? ' seo-expert-prose-split--flip' : '';
	$side_img = absint( get_post_meta( $post_id, ZSkeleton_Seo_Expert_Meta::meta_key( $meta_key . '_side_image_id' ), true ) );
	$has_side = ( $side_img > 0 && wp_attachment_is_image( $side_img ) );
	++$block_i;
	?>
	<section class="seo-expert-section<?php echo esc_attr( $sec_mod ); ?>">
		<div class="<?php echo esc_attr( $c ); ?>">
			<?php
			if ( function_exists( 'zskeleton_seo_expert_section_heading' ) ) {
				zskeleton_seo_expert_section_heading( $heading, $icon_key );
			} else {
				echo '<h2 class="seo-expert-section__title">' . esc_html( $heading ) . '</h2>';
			}
			?>
			<?php if ( $has_side ) : ?>
			<div class="seo-expert-prose-split seo-expert-prose-split--has-image<?php echo esc_attr( $flip ); ?>">
				<div class="seo-expert-prose-split__photo">
					<?php
					echo wp_get_attachment_image(
						$side_img,
						'large',
						false,
						array(
							'class'    => 'seo-expert-prose-split__img',
							'loading'  => 'lazy',
							'decoding' => 'async',
						)
					);
					?>
				</div>
				<?php if ( function_exists( 'zskeleton_seo_expert_icon' ) ) : ?>
					<div class="seo-expert-prose-split__accent" aria-hidden="true"><?php echo zskeleton_seo_expert_icon( $icon_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG via wp_kses(). ?></div>
				<?php endif; ?>
				<div class="seo-expert-prose-split__main seo-expert-prose seo-expert-prose--inset"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
			</div>
			<?php else : ?>
			<div class="seo-expert-prose-split<?php echo esc_attr( $flip ); ?>">
				<?php if ( function_exists( 'zskeleton_seo_expert_icon' ) ) : ?>
					<div class="seo-expert-prose-split__accent" aria-hidden="true"><?php echo zskeleton_seo_expert_icon( $icon_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG via wp_kses(). ?></div>
				<?php endif; ?>
				<div class="seo-expert-prose seo-expert-prose--inset"><?php echo wp_kses_post( wpautop( $body ) ); ?></div>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

$price = zskeleton_seo_expert_get( $post_id, 'pricing_disclaimer_body' );
if ( $price !== '' ) :
	$pricing_img_id = absint( get_post_meta( $post_id, ZSkeleton_Seo_Expert_Meta::meta_key( 'pricing_section_side_image_id' ), true ) );
	$has_pricing_img = ( $pricing_img_id > 0 && wp_attachment_is_image( $pricing_img_id ) );
	?>
	<section class="seo-expert-section seo-expert-section--alt seo-expert-section--mesh" id="seo-expert-pricing">
		<div class="<?php echo esc_attr( $c ); ?>">
			<?php
			if ( function_exists( 'zskeleton_seo_expert_section_heading' ) ) {
				zskeleton_seo_expert_section_heading( 'الأسعار', 'currency' );
			} else {
				echo '<h2 class="seo-expert-section__title">' . esc_html( 'الأسعار' ) . '</h2>';
			}
			?>
			<div class="seo-expert-pricing-layout<?php echo $has_pricing_img ? ' seo-expert-pricing-layout--has-image' : ''; ?>">
				<div class="seo-expert-prose seo-expert-prose--inset seo-expert-pricing-layout__prose"><?php echo wp_kses_post( wpautop( $price ) ); ?></div>
				<?php if ( function_exists( 'zskeleton_seo_expert_icon' ) ) : ?>
					<div class="seo-expert-pricing-layout__visual" aria-hidden="true"><?php echo zskeleton_seo_expert_icon( 'currency' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG via wp_kses(). ?></div>
				<?php endif; ?>
				<?php if ( $has_pricing_img ) : ?>
					<div class="seo-expert-pricing-layout__photo">
						<?php
						echo wp_get_attachment_image(
							$pricing_img_id,
							'large',
							false,
							array(
								'class'    => 'seo-expert-pricing-layout__img',
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
endif;
?>

<?php
$seo_expert_home_plans = class_exists( 'ZSkeleton_Membership_Plans' ) ? ZSkeleton_Membership_Plans::get_active_plans() : array();
if ( ! empty( $seo_expert_home_plans ) ) :
	?>
	<div class="seo-expert-section seo-expert-membership-plans" id="seo-expert-memberships">
		<div class="<?php echo esc_attr( $c ); ?>">
			<?php
			get_template_part(
				'template-parts/membership-plans-pricing',
				null,
				array(
					'plans'   => $seo_expert_home_plans,
					'heading' => __( 'Choose Your Membership', 'zskeleton' ),
				)
			);
			?>
		</div>
	</div>
	<?php
endif;
?>

<section class="seo-expert-section seo-expert-section--brand-tint" id="seo-expert-faq">
	<div class="<?php echo esc_attr( $c ); ?>">
		<?php
		if ( function_exists( 'zskeleton_seo_expert_section_heading' ) ) {
			zskeleton_seo_expert_section_heading( 'أسئلة شائعة', 'help' );
		} else {
			echo '<h2 class="seo-expert-section__title">' . esc_html( 'أسئلة شائعة' ) . '</h2>';
		}
		?>
		<div class="faq-list-simple seo-expert-faq-list" role="list">
			<?php
			$faqs = function_exists( 'zskeleton_get_seo_expert_faqs' )
				? zskeleton_get_seo_expert_faqs( null )
				: array();
			if ( ! empty( $faqs ) ) :
				foreach ( $faqs as $fp ) :
					if ( ! isset( $fp->ID ) ) {
						continue;
					}
					?>
					<article class="faq-item" role="listitem">
						<details class="faq-details">
							<summary class="faq-question">
								<?php echo esc_html( get_the_title( $fp ) ); ?>
								<span class="faq-icon" aria-hidden="true"></span>
							</summary>
							<div class="faq-answer">
								<?php echo wp_kses_post( wpautop( $fp->post_content ) ); ?>
							</div>
						</details>
					</article>
					<?php
				endforeach;
			else :
				?>
				<p class="seo-expert-empty"><?php echo esc_html( 'لا توجد أسئلة شائعة منشورة بعد. أضف أسئلة من نوع FAQs في لوحة التحكم.' ); ?></p>
				<?php
			endif;
			?>
		</div>
	</div>
</section>

<?php
$closing = zskeleton_seo_expert_get( $post_id, 'prose_closing_cta' );
$zskeleton_template_part_args = array(
	'heading'          => sprintf( 'ابدأ مع %s', $name ),
	'body'             => $closing,
	'button_label'     => $primary,
	'button_url'       => $cta_primary_url,
	'container_class'  => $c,
	'class_prefix'     => 'seo-expert',
);
get_template_part( 'template-parts/marketing/section-cta-band' );
?>

<?php
/* AI-era copy + lead form (matches page-home-seo-ar #ai-lead layout & classes). */
$ai_lead_title       = zskeleton_seo_expert_get( $post_id, 'ai_lead_title' );
$ai_lead_intro       = zskeleton_seo_expert_get( $post_id, 'ai_lead_intro' );
$ai_lead_sub_warn    = zskeleton_seo_expert_get( $post_id, 'ai_lead_subhead_warn' );
$ai_lead_warn_body   = zskeleton_seo_expert_get( $post_id, 'ai_lead_warn_body' );
$ai_lead_sub_why     = zskeleton_seo_expert_get( $post_id, 'ai_lead_subhead_why' );
$ai_lead_why_p1      = zskeleton_seo_expert_get( $post_id, 'ai_lead_why_p1' );
$ai_lead_why_p2      = zskeleton_seo_expert_get( $post_id, 'ai_lead_why_p2' );
$ai_lead_form_h      = zskeleton_seo_expert_get( $post_id, 'ai_lead_form_heading' );
$case_study_url      = function_exists( 'zskeleton_seo_expert_get_ai_lead_case_study_url' ) ? zskeleton_seo_expert_get_ai_lead_case_study_url( $post_id ) : esc_url( home_url( '/' ) );
if ( '' === trim( $ai_lead_sub_why ) ) {
	$ai_lead_sub_why = sprintf( 'لماذا %s؟', $name );
}
$ai_lead_warn_body = str_replace( '%%CASE_STUDY_URL%%', esc_url( $case_study_url ), $ai_lead_warn_body );
$ai_lead_why_p1    = str_replace( '%%EXPERT_NAME%%', esc_html( $name ), $ai_lead_why_p1 );
$brand             = zskeleton_seo_expert_get( $post_id, 'brand_or_team_name' );
?>
<section class="seo-ar-ai-lead" id="seo-expert-contact" aria-labelledby="seo-expert-ai-lead-heading">
	<div class="<?php echo esc_attr( $c ); ?>">
		<?php
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
				if ( function_exists( 'zskeleton_seo_ar_ai_lead_render_title_stack' ) ) {
					zskeleton_seo_ar_ai_lead_render_title_stack(
						array(
							'heading_id'            => 'seo-expert-ai-lead-heading',
							'title'                 => $ai_lead_title,
							'title_separator_style' => 'line',
						)
					);
				} else {
					echo '<h2 id="seo-expert-ai-lead-heading">' . esc_html( $ai_lead_title ) . '</h2>';
				}
				?>
				<p class="seo-ar-lead-text"><?php echo wp_kses_post( $ai_lead_intro ); ?></p>

				<h3 class="seo-ar-ai-lead-subhead"><?php echo esc_html( $ai_lead_sub_warn ); ?></h3>
				<p class="seo-ar-lead-text"><?php echo wp_kses_post( $ai_lead_warn_body ); ?></p>

				<h3 class="seo-ar-ai-lead-subhead"><?php echo esc_html( $ai_lead_sub_why ); ?></h3>
				<p class="seo-ar-lead-text"><?php echo wp_kses_post( $ai_lead_why_p1 ); ?></p>
				<p class="seo-ar-lead-text"><?php echo wp_kses_post( $ai_lead_why_p2 ); ?></p>

				<section class="seo-expert-end-strip seo-expert-end-strip--in-copy" aria-label="<?php echo esc_attr( 'تذييل الصفحة' ); ?>">
					<p class="seo-expert-end-strip__brand">
						<?php if ( $brand !== '' ) : ?>
							<?php echo esc_html( $brand ); ?> —
						<?php endif; ?>
						<?php echo esc_html( sprintf( 'خبير سيو: %s', $name ) ); ?>
					</p>
					<p class="seo-expert-end-strip__social">
						<?php if ( zskeleton_get_contact( 'twitter' ) !== '' ) : ?>
							<a href="<?php echo esc_url( zskeleton_get_contact( 'twitter' ) ); ?>">X</a>
						<?php endif; ?>
						<?php if ( zskeleton_get_contact( 'linkedin' ) !== '' ) : ?>
							<a href="<?php echo esc_url( zskeleton_get_contact( 'linkedin' ) ); ?>">LinkedIn</a>
						<?php endif; ?>
						<?php if ( zskeleton_get_contact( 'facebook' ) !== '' ) : ?>
							<a href="<?php echo esc_url( zskeleton_get_contact( 'facebook' ) ); ?>">Facebook</a>
						<?php endif; ?>
					</p>
				</section>
			</div>
			<div class="seo-ar-ai-lead-form-col">
				<div class="seo-ar-custom-form-box">
					<div class="seo-ar-custom-form-box__inner">
						<h3 class="seo-ar-custom-form-box__heading"><?php echo esc_html( $ai_lead_form_h ); ?></h3>
						<div class="seo-ar-custom-form-box__form">
							<?php zskeleton_seo_expert_render_lead_column(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php if ( ! empty( $blog_posts ) ) : ?>
	<section class="seo-expert-section seo-expert-section--alt seo-expert-section--mesh" id="seo-expert-bloglinks">
		<div class="<?php echo esc_attr( $c ); ?>">
			<?php
			if ( function_exists( 'zskeleton_seo_expert_section_heading' ) ) {
				zskeleton_seo_expert_section_heading( 'مقالات ذات صلة', 'book' );
			} else {
				echo '<h2 class="seo-expert-section__title">' . esc_html( 'مقالات ذات صلة' ) . '</h2>';
			}
			?>
			<div class="seo-expert-bloglinks" role="list">
				<?php foreach ( $blog_posts as $blog_post ) : ?>
					<?php
					if ( ! $blog_post instanceof WP_Post ) {
						continue;
					}
					$bp_id    = (int) $blog_post->ID;
					$bp_url   = get_permalink( $blog_post );
					$bp_title = get_the_title( $blog_post );
					if ( ! $bp_url || $bp_title === '' ) {
						continue;
					}
					$thumb_url = get_the_post_thumbnail_url( $blog_post, 'medium_large' );
					if ( ! $thumb_url ) {
						$thumb_url = get_the_post_thumbnail_url( $blog_post, 'medium' );
					}
					$excerpt = get_the_excerpt( $blog_post );
					if ( '' === trim( wp_strip_all_tags( $excerpt ) ) ) {
						$excerpt = wp_trim_words( wp_strip_all_tags( $blog_post->post_content ), 22, '…' );
					} else {
						$excerpt = wp_trim_words( wp_strip_all_tags( $excerpt ), 22, '…' );
					}
					?>
					<article class="seo-expert-blog-card" role="listitem">
						<a class="seo-expert-blog-card__link" href="<?php echo esc_url( $bp_url ); ?>">
							<div class="seo-expert-blog-card__media" aria-hidden="true">
								<?php if ( $thumb_url ) : ?>
									<img
										class="seo-expert-blog-card__img"
										src="<?php echo esc_url( $thumb_url ); ?>"
										alt=""
										loading="lazy"
										decoding="async"
									/>
								<?php else : ?>
									<span class="seo-expert-blog-card__placeholder"><?php echo esc_html( 'مقال' ); ?></span>
								<?php endif; ?>
							</div>
							<div class="seo-expert-blog-card__body">
								<time class="seo-expert-blog-card__date" datetime="<?php echo esc_attr( get_the_date( 'c', $blog_post ) ); ?>">
									<?php echo esc_html( get_the_date( '', $blog_post ) ); ?>
								</time>
								<h3 class="seo-expert-blog-card__title"><?php echo esc_html( $bp_title ); ?></h3>
								<?php if ( $excerpt !== '' ) : ?>
									<p class="seo-expert-blog-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
								<?php endif; ?>
								<span class="seo-expert-blog-card__cta"><?php echo esc_html( 'اقرأ المقال' ); ?></span>
							</div>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
