<?php
/**
 * Generic marketing hero (RTL-friendly).
 *
 * @package ZSkeleton_Theme
 *
 * Args: title, subtitle, primary_label, primary_url, secondary_label, secondary_url, container_class, class_prefix (default seo-expert).
 * Optional hero: hero_attachment_id, hero_image_url, hero_image_alt — when set, split layout with image (URL or attachment).
 */

defined( 'ABSPATH' ) || exit;

global $zskeleton_template_part_args;
$a = array();
if ( isset( $args ) && is_array( $args ) && ! empty( $args ) ) {
	$a = $args;
} elseif ( isset( $zskeleton_template_part_args ) && is_array( $zskeleton_template_part_args ) ) {
	$a = $zskeleton_template_part_args;
}
$a = wp_parse_args(
	$a,
	array(
		'title'              => '',
		'subtitle'           => '',
		'primary_label'      => '',
		'primary_url'        => '#',
		'secondary_label'    => '',
		'secondary_url'      => '#',
		'container_class'    => 'wide-container',
		'class_prefix'       => 'seo-expert',
		'hero_attachment_id' => 0,
		'hero_image_url'     => '',
		'hero_image_alt'     => '',
	)
);

$p = preg_replace( '/[^a-z0-9_-]/i', '', $a['class_prefix'] );
if ( '' === $p ) {
	$p = 'seo-expert';
}

$hero_att = absint( $a['hero_attachment_id'] );
$hero_url = is_string( $a['hero_image_url'] ) ? trim( $a['hero_image_url'] ) : '';
$hero_alt = is_string( $a['hero_image_alt'] ) ? $a['hero_image_alt'] : '';

$has_attachment = ( $hero_att > 0 && wp_attachment_is_image( $hero_att ) );
$has_static_url = ( ! $has_attachment && '' !== $hero_url );
$has_visual     = $has_attachment || $has_static_url;

$section_class = array( $p . '-hero' );
if ( $has_visual ) {
	$section_class[] = $p . '-hero--split';
}
?>
<section class="<?php echo esc_attr( implode( ' ', $section_class ) ); ?>" aria-labelledby="<?php echo esc_attr( $p ); ?>-hero-heading">
	<?php if ( $has_visual ) : ?>
		<canvas class="<?php echo esc_attr( $p ); ?>-hero__particles" aria-hidden="true" role="presentation"></canvas>
		<div class="<?php echo esc_attr( $a['container_class'] ); ?> <?php echo esc_attr( $p ); ?>-hero__shell">
			<div class="<?php echo esc_attr( $p ); ?>-hero__inner">
				<div class="<?php echo esc_attr( $p ); ?>-hero__content">
					<h1 id="<?php echo esc_attr( $p ); ?>-hero-heading" class="<?php echo esc_attr( $p ); ?>-hero__title"><?php echo esc_html( $a['title'] ); ?></h1>
					<?php if ( $a['subtitle'] !== '' ) : ?>
						<div class="<?php echo esc_attr( $p ); ?>-hero__subtitle"><?php echo wp_kses_post( wpautop( $a['subtitle'] ) ); ?></div>
					<?php endif; ?>
					<div class="<?php echo esc_attr( $p ); ?>-hero__actions">
						<?php if ( $a['primary_label'] !== '' ) : ?>
							<a class="<?php echo esc_attr( $p ); ?>-btn <?php echo esc_attr( $p ); ?>-btn--primary" href="<?php echo esc_url( $a['primary_url'] ); ?>"><?php echo esc_html( $a['primary_label'] ); ?></a>
						<?php endif; ?>
						<?php if ( $a['secondary_label'] !== '' ) : ?>
							<a class="<?php echo esc_attr( $p ); ?>-btn <?php echo esc_attr( $p ); ?>-btn--outline" href="<?php echo esc_url( $a['secondary_url'] ); ?>"><?php echo esc_html( $a['secondary_label'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<figure class="<?php echo esc_attr( $p ); ?>-hero__figure">
			<?php if ( $has_attachment ) : ?>
				<?php
				echo wp_get_attachment_image(
					$hero_att,
					'large',
					false,
					array(
						'class'    => $p . '-hero__img',
						'alt'      => $hero_alt,
						'loading'  => 'eager',
						'decoding' => 'async',
					)
				);
				?>
			<?php else : ?>
				<img
					class="<?php echo esc_attr( $p ); ?>-hero__img"
					src="<?php echo esc_url( $hero_url ); ?>"
					alt="<?php echo esc_attr( $hero_alt ); ?>"
					loading="eager"
					decoding="async"
					fetchpriority="high"
				/>
			<?php endif; ?>
		</figure>
	<?php else : ?>
		<div class="<?php echo esc_attr( $a['container_class'] ); ?>">
			<h1 id="<?php echo esc_attr( $p ); ?>-hero-heading" class="<?php echo esc_attr( $p ); ?>-hero__title"><?php echo esc_html( $a['title'] ); ?></h1>
			<?php if ( $a['subtitle'] !== '' ) : ?>
				<div class="<?php echo esc_attr( $p ); ?>-hero__subtitle"><?php echo wp_kses_post( wpautop( $a['subtitle'] ) ); ?></div>
			<?php endif; ?>
			<div class="<?php echo esc_attr( $p ); ?>-hero__actions">
				<?php if ( $a['primary_label'] !== '' ) : ?>
					<a class="<?php echo esc_attr( $p ); ?>-btn <?php echo esc_attr( $p ); ?>-btn--primary" href="<?php echo esc_url( $a['primary_url'] ); ?>"><?php echo esc_html( $a['primary_label'] ); ?></a>
				<?php endif; ?>
				<?php if ( $a['secondary_label'] !== '' ) : ?>
					<a class="<?php echo esc_attr( $p ); ?>-btn <?php echo esc_attr( $p ); ?>-btn--outline" href="<?php echo esc_url( $a['secondary_url'] ); ?>"><?php echo esc_html( $a['secondary_label'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
