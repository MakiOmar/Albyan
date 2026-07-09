<?php
/**
 * CTA band with heading, text, and button.
 *
 * @package ZSkeleton_Theme
 *
 * Args: heading, body, button_label, button_url, container_class, class_prefix.
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
		'heading'          => '',
		'body'             => '',
		'button_label'     => '',
		'button_url'       => '#',
		'container_class'  => 'wide-container',
		'class_prefix'     => 'seo-expert',
	)
);

$p = preg_replace( '/[^a-z0-9_-]/i', '', $a['class_prefix'] );
if ( '' === $p ) {
	$p = 'seo-expert';
}

if ( $a['heading'] === '' && $a['body'] === '' && $a['button_label'] === '' ) {
	return;
}
?>
<section class="<?php echo esc_attr( $p ); ?>-cta-band">
	<div class="<?php echo esc_attr( $a['container_class'] ); ?>">
		<?php if ( $a['heading'] !== '' ) : ?>
			<h2 class="<?php echo esc_attr( $p ); ?>-cta-band__title"><?php echo esc_html( $a['heading'] ); ?></h2>
		<?php endif; ?>
		<?php if ( $a['body'] !== '' ) : ?>
			<div class="<?php echo esc_attr( $p ); ?>-cta-band__text"><?php echo wp_kses_post( wpautop( $a['body'] ) ); ?></div>
		<?php endif; ?>
		<?php if ( $a['button_label'] !== '' ) : ?>
			<p class="<?php echo esc_attr( $p ); ?>-cta-band__action">
				<a class="<?php echo esc_attr( $p ); ?>-btn <?php echo esc_attr( $p ); ?>-btn--primary" href="<?php echo esc_url( $a['button_url'] ); ?>"><?php echo esc_html( $a['button_label'] ); ?></a>
			</p>
		<?php endif; ?>
	</div>
</section>
