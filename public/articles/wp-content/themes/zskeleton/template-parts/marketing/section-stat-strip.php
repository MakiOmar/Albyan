<?php
/**
 * Stat strip (three-column style cards).
 *
 * @package ZSkeleton_Theme
 *
 * Args: rows (array of figure + label), container_class, class_prefix, aria_label.
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
		'rows'             => array(),
		'container_class'  => 'wide-container',
		'class_prefix'     => 'seo-expert',
		'aria_label'       => '',
	)
);

$p = preg_replace( '/[^a-z0-9_-]/i', '', $a['class_prefix'] );
if ( '' === $p ) {
	$p = 'seo-expert';
}

$rows = is_array( $a['rows'] ) ? $a['rows'] : array();
if ( empty( $rows ) ) {
	return;
}

$aria = $a['aria_label'] !== '' ? $a['aria_label'] : __( 'Key figures', 'zskeleton' );

$stat_icon_keys = array( 'chart', 'globe', 'star' );
$si               = 0;
?>
<section class="<?php echo esc_attr( $p ); ?>-stats" aria-label="<?php echo esc_attr( $aria ); ?>">
	<div class="<?php echo esc_attr( $a['container_class'] ); ?>">
		<div class="<?php echo esc_attr( $p ); ?>-stats__grid">
			<?php foreach ( $rows as $row ) : ?>
				<?php
				if ( ! is_array( $row ) ) {
					continue;
				}
				$fig   = isset( $row['figure'] ) ? (string) $row['figure'] : '';
				$label = isset( $row['label'] ) ? (string) $row['label'] : '';
				if ( $fig === '' && $label === '' ) {
					continue;
				}
				$icon_key = $stat_icon_keys[ $si % count( $stat_icon_keys ) ];
				++$si;
				?>
				<div class="<?php echo esc_attr( $p ); ?>-stats__card">
					<?php if ( function_exists( 'zskeleton_seo_expert_icon' ) ) : ?>
						<span class="<?php echo esc_attr( $p ); ?>-stats__icon" aria-hidden="true"><?php echo zskeleton_seo_expert_icon( $icon_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG via wp_kses(). ?></span>
					<?php endif; ?>
					<strong class="<?php echo esc_attr( $p ); ?>-stats__figure"><?php echo esc_html( $fig ); ?></strong>
					<span class="<?php echo esc_attr( $p ); ?>-stats__label"><?php echo esc_html( $label ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
