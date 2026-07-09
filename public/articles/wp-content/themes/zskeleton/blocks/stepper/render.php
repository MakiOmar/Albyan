<?php
/**
 * Dynamic render: Stepper block.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'zskeleton_stepper_hex' ) ) {
	/**
	 * Sanitize hex color with safe fallback.
	 *
	 * @param mixed  $raw Raw value.
	 * @param string $fallback Fallback color.
	 * @return string
	 */
	function zskeleton_stepper_hex( $raw, string $fallback ): string {
		$fallback = sanitize_hex_color( $fallback );
		$fallback = is_string( $fallback ) ? $fallback : '#000000';
		if ( ! is_string( $raw ) ) {
			return $fallback;
		}
		$hex = sanitize_hex_color( trim( $raw ) );
		return is_string( $hex ) ? $hex : $fallback;
	}
}

if ( ! function_exists( 'zskeleton_stepper_int' ) ) {
	/**
	 * Clamp integer attribute.
	 *
	 * @param mixed $raw Raw value.
	 * @param int   $min Minimum.
	 * @param int   $max Maximum.
	 * @param int   $default Default.
	 * @return int
	 */
	function zskeleton_stepper_int( $raw, int $min, int $max, int $default ): int {
		$n = isset( $raw ) ? (int) $raw : $default;
		if ( $n < $min ) {
			return $min;
		}
		if ( $n > $max ) {
			return $max;
		}
		return $n;
	}
}

$a             = is_array( $attributes ) ? $attributes : array();
$style_variant = isset( $a['styleVariant'] ) ? sanitize_key( (string) $a['styleVariant'] ) : 'style-1';
if ( 'style-1' !== $style_variant ) {
	$style_variant = 'style-1';
}

$steps_raw = isset( $a['steps'] ) && is_array( $a['steps'] ) ? $a['steps'] : array();
$steps     = array();
foreach ( $steps_raw as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$label  = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
	$number = isset( $row['number'] ) ? sanitize_text_field( (string) $row['number'] ) : '';
	$accent = zskeleton_stepper_hex( isset( $row['accentColor'] ) ? $row['accentColor'] : '', '#d1d5db' );
	if ( '' === $label && '' === $number ) {
		continue;
	}
	$steps[] = array(
		'label'       => $label,
		'number'      => '' !== $number ? $number : (string) ( count( $steps ) + 1 ),
		'accentColor' => $accent,
	);
}

if ( empty( $steps ) ) {
	$steps = array(
		array(
			'label'       => __( 'Step', 'zskeleton' ),
			'number'      => '1',
			'accentColor' => '#d1d5db',
		),
	);
}

$current_step          = zskeleton_stepper_int( $a['currentStep'] ?? 1, 1, 20, 1 );
$mobile_vertical       = ! empty( $a['mobileVertical'] );
$mobile_breakpoint     = zskeleton_stepper_int( $a['mobileBreakpointPx'] ?? 782, 320, 1280, 782 );
$max_width             = zskeleton_stepper_int( $a['maxWidthPx'] ?? 980, 320, 1600, 980 );
$padding_y             = zskeleton_stepper_int( $a['sectionPaddingYpx'] ?? 18, 0, 120, 18 );
$padding_x             = zskeleton_stepper_int( $a['sectionPaddingXpx'] ?? 18, 0, 120, 18 );
$item_gap              = zskeleton_stepper_int( $a['itemGapPx'] ?? 26, 0, 120, 26 );
$line_thickness        = zskeleton_stepper_int( $a['lineThicknessPx'] ?? 2, 1, 12, 2 );
$circle_size           = zskeleton_stepper_int( $a['circleSizePx'] ?? 34, 18, 80, 34 );
$title_font            = zskeleton_stepper_int( $a['titleFontSizePx'] ?? 14, 10, 42, 14 );
$title_gap_top         = zskeleton_stepper_int( $a['titleGapTopPx'] ?? 10, 0, 48, 10 );
$underline_width       = zskeleton_stepper_int( $a['underlineWidthPx'] ?? 52, 8, 240, 52 );
$underline_height      = zskeleton_stepper_int( $a['underlineHeightPx'] ?? 3, 1, 16, 3 );
$section_bg            = zskeleton_stepper_hex( $a['sectionBackground'] ?? '', '#ffffff' );
$circle_bg             = zskeleton_stepper_hex( $a['circleBackground'] ?? '', '#f3f4f6' );
$circle_text           = zskeleton_stepper_hex( $a['circleTextColor'] ?? '', '#4b5563' );
$active_circle_bg      = zskeleton_stepper_hex( $a['activeCircleBackground'] ?? '', '#e5e7eb' );
$active_circle_text    = zskeleton_stepper_hex( $a['activeCircleTextColor'] ?? '', '#111827' );
$step_text             = zskeleton_stepper_hex( $a['stepTextColor'] ?? '', '#111827' );
$active_step_text      = zskeleton_stepper_hex( $a['activeStepTextColor'] ?? '', '#111827' );
$underline_color       = zskeleton_stepper_hex( $a['underlineColor'] ?? '', '#cbd5e1' );
$active_underline      = zskeleton_stepper_hex( $a['activeUnderlineColor'] ?? '', '#94a3b8' );
$connector_color       = zskeleton_stepper_hex( $a['connectorColor'] ?? '', '#d1d5db' );

$style_vars = sprintf(
	'--zstep-bg:%1$s;--zstep-maxw:%2$dpx;--zstep-py:%3$dpx;--zstep-px:%4$dpx;--zstep-gap:%5$dpx;--zstep-line:%6$dpx;--zstep-circle:%7$dpx;--zstep-title-size:%8$dpx;--zstep-title-gap:%9$dpx;--zstep-underline-w:%10$dpx;--zstep-underline-h:%11$dpx;--zstep-circle-bg:%12$s;--zstep-circle-fg:%13$s;--zstep-circle-bg-active:%14$s;--zstep-circle-fg-active:%15$s;--zstep-text:%16$s;--zstep-text-active:%17$s;--zstep-underline:%18$s;--zstep-underline-active:%19$s;--zstep-connector:%20$s;--zstep-mobile-break:%21$dpx;',
	$section_bg,
	$max_width,
	$padding_y,
	$padding_x,
	$item_gap,
	$line_thickness,
	$circle_size,
	$title_font,
	$title_gap_top,
	$underline_width,
	$underline_height,
	$circle_bg,
	$circle_text,
	$active_circle_bg,
	$active_circle_text,
	$step_text,
	$active_step_text,
	$underline_color,
	$active_underline,
	$connector_color,
	$mobile_breakpoint
);

$classes = array(
	'zskeleton-stepper',
	'zskeleton-stepper--' . $style_variant,
);
if ( $mobile_vertical ) {
	$classes[] = 'zskeleton-stepper--mobile-vertical';
}
$instance_class = 'zskeleton-stepper--inst-' . wp_unique_id();
$classes[]      = $instance_class;

$wrapper_args = array(
	'class' => implode( ' ', $classes ),
	'style' => $style_vars,
);
if ( function_exists( 'is_rtl' ) && is_rtl() ) {
	$wrapper_args['dir'] = 'rtl';
}

$wrapper = isset( $block ) && $block instanceof WP_Block
	? get_block_wrapper_attributes( $wrapper_args, '', $block )
	: get_block_wrapper_attributes( $wrapper_args );
?>
<?php if ( $mobile_vertical ) : ?>
<style>
@media (max-width: <?php echo (int) $mobile_breakpoint; ?>px) {
	.<?php echo esc_html( $instance_class ); ?> .zskeleton-stepper__track {
		flex-direction: column;
		gap: calc(var(--zstep-gap, 26px) * 0.75);
	}
	.<?php echo esc_html( $instance_class ); ?> .zskeleton-stepper__item {
		width: 100%;
	}
	.<?php echo esc_html( $instance_class ); ?> .zskeleton-stepper__head {
		justify-content: flex-start;
	}
	.<?php echo esc_html( $instance_class ); ?> .zskeleton-stepper__item:not(:last-child) .zskeleton-stepper__head::after {
		top: calc(var(--zstep-circle, 34px) + 6px);
		inset-inline-start: calc(var(--zstep-circle, 34px) / 2 - (var(--zstep-line, 2px) / 2));
		inset-inline-end: auto;
		width: var(--zstep-line, 2px);
		height: calc(100% + (var(--zstep-gap, 26px) * 0.8));
		transform: none;
	}
	.<?php echo esc_html( $instance_class ); ?> .zskeleton-stepper__body {
		margin-top: calc(var(--zstep-title-gap, 10px) * -1 + 2px);
		margin-inline-start: calc(var(--zstep-circle, 34px) + 12px);
		align-items: flex-start;
		text-align: start;
	}
}
</style>
<?php endif; ?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="zskeleton-stepper__inner">
		<ol class="zskeleton-stepper__track" role="list">
			<?php foreach ( $steps as $index => $step ) : ?>
				<?php
				$step_pos   = $index + 1;
				$is_active  = $step_pos === $current_step;
				$is_done    = $step_pos < $current_step;
				$item_class = 'zskeleton-stepper__item';
				if ( $is_active ) {
					$item_class .= ' is-active';
				} elseif ( $is_done ) {
					$item_class .= ' is-done';
				}
				?>
				<li class="<?php echo esc_attr( $item_class ); ?>" style="--zstep-accent:<?php echo esc_attr( $step['accentColor'] ); ?>;">
					<div class="zskeleton-stepper__head">
						<span class="zskeleton-stepper__circle" aria-hidden="true"><?php echo esc_html( $step['number'] ); ?></span>
					</div>
					<div class="zskeleton-stepper__body">
						<span class="zskeleton-stepper__title"<?php echo $is_active ? ' aria-current="step"' : ''; ?>>
							<?php echo esc_html( $step['label'] ); ?>
						</span>
						<span class="zskeleton-stepper__underline" aria-hidden="true"></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
