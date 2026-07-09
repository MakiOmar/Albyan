<?php
/**
 * Reusable taxonomy term grid (any taxonomy). Styles: simple | icons | thumbnails.
 *
 * Loaded via get_template_part(): WordPress exposes the third parameter as {@see $args}
 * in {@see load_template()} scope (keys are not auto-extracted to locals).
 * - terms        (WP_Term[]) Required.
 * - taxonomy     (string)    Taxonomy slug (for filters / semantics).
 * - style        (string)    simple | icons | thumbnails.
 * - section_class (string)   Extra class on <section>.
 * - heading      (string)    Optional section title (escaped when printed).
 * - heading_id   (string)    Optional id on heading for aria-labelledby.
 * - heading_tag  (string)    h2, h3, etc. Default h2.
 * - show_count   (bool)      Show assigned item count. Default true.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

$tp = isset( $args ) && is_array( $args ) ? $args : array();

$terms = isset( $tp['terms'] ) && is_array( $tp['terms'] ) ? $tp['terms'] : array();
if ( empty( $terms ) ) {
	return;
}

$taxonomy = isset( $tp['taxonomy'] ) ? sanitize_key( (string) $tp['taxonomy'] ) : 'category';
if ( ! taxonomy_exists( $taxonomy ) ) {
	$taxonomy = 'category';
}

$style = isset( $tp['style'] ) ? sanitize_key( (string) $tp['style'] ) : 'simple';
if ( ! in_array( $style, array( 'simple', 'icons', 'thumbnails' ), true ) ) {
	$style = 'simple';
}

$section_class = isset( $tp['section_class'] ) ? sanitize_text_field( (string) $tp['section_class'] ) : '';
$heading       = isset( $tp['heading'] ) ? (string) $tp['heading'] : '';
$heading_id    = isset( $tp['heading_id'] ) ? sanitize_html_class( (string) $tp['heading_id'] ) : '';
$heading_tag   = isset( $tp['heading_tag'] ) ? strtolower( preg_replace( '/[^a-z0-9]/', '', (string) $tp['heading_tag'] ) ) : 'h2';
if ( ! in_array( $heading_tag, array( 'h1', 'h2', 'h3', 'h4' ), true ) ) {
	$heading_tag = 'h2';
}

$show_count = array_key_exists( 'show_count', $tp ) ? (bool) $tp['show_count'] : true;

$list_classes = array(
	'zskeleton-term-list',
	'zskeleton-term-list--' . $style,
);
if ( 'thumbnails' === $style || 'simple' === $style ) {
	$list_classes[] = 'practices-grid';
}
if ( 'icons' === $style ) {
	$list_classes[] = 'zskeleton-term-list--icons-grid';
}

$list_classes = apply_filters( 'zskeleton_term_listing_list_classes', $list_classes, $taxonomy, $style, $terms );
$section_extra = apply_filters( 'zskeleton_term_listing_section_class', $section_class, $taxonomy, $style );

?>

<section class="zskeleton-term-listing-section blog-hub-term-listing<?php echo $section_extra ? ' ' . esc_attr( $section_extra ) : ''; ?>"<?php echo $heading && $heading_id ? ' aria-labelledby="' . esc_attr( $heading_id ) . '"' : ''; ?>>
	<?php if ( $heading ) : ?>
		<div class="blog-hub-section__head">
			<<?php echo esc_attr( $heading_tag ); ?> <?php echo $heading_id ? 'id="' . esc_attr( $heading_id ) . '"' : ''; ?> class="blog-hub-section__title"><?php echo esc_html( $heading ); ?></<?php echo esc_attr( $heading_tag ); ?>>
		</div>
	<?php endif; ?>

	<ul class="<?php echo esc_attr( implode( ' ', array_filter( $list_classes ) ) ); ?>">
		<?php
		foreach ( $terms as $term_item ) {
			if ( ! $term_item instanceof WP_Term ) {
				continue;
			}
			$term_link = get_term_link( $term_item );
			if ( is_wp_error( $term_link ) ) {
				continue;
			}

			$icon_html = zskeleton_term_listing_get_icon_html( $term_item, 'thumbnail' );
			$image_html = zskeleton_term_listing_get_image_html( $term_item, 'medium_large' );

			$count_label = '';
			if ( $show_count ) {
				$count = (int) $term_item->count;
				if ( 'category' === $taxonomy || 'post_tag' === $taxonomy ) {
					$count_label = sprintf(
						/* translators: %s: number of posts */
						_n( '%s post', '%s posts', $count, 'zskeleton' ),
						number_format_i18n( $count )
					);
				} else {
					$count_label = sprintf(
						/* translators: %s: number of assigned objects */
						_n( '%s item', '%s items', $count, 'zskeleton' ),
						number_format_i18n( $count )
					);
				}
				$count_label = apply_filters( 'zskeleton_term_listing_count_label', $count_label, $term_item, $taxonomy, $count );
			}

			$item_classes = array( 'zskeleton-term-list__item', 'formal-card' );
			if ( ! $image_html && 'thumbnails' === $style ) {
				$item_classes[] = 'zskeleton-term-list__item--no-image';
			}
			$item_classes = apply_filters( 'zskeleton_term_listing_item_classes', $item_classes, $term_item, $taxonomy, $style );
			?>
			<li class="<?php echo esc_attr( implode( ' ', array_filter( $item_classes ) ) ); ?>">
				<a class="zskeleton-term-list__link" href="<?php echo esc_url( $term_link ); ?>">
					<?php if ( 'thumbnails' === $style ) : ?>
						<span class="zskeleton-term-list__media zskeleton-term-list__media--thumb">
							<?php
							if ( $image_html ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attachment image HTML from core.
								echo $image_html;
							} else {
								$initial = function_exists( 'mb_substr' )
									? mb_substr( $term_item->name, 0, 1, 'UTF-8' )
									: substr( $term_item->name, 0, 1 );
								$initial = function_exists( 'mb_strtoupper' )
									? mb_strtoupper( $initial, 'UTF-8' )
									: strtoupper( $initial );
								echo '<span class="zskeleton-term-list__placeholder" aria-hidden="true">' . esc_html( $initial ) . '</span>';
							}
							?>
							<?php if ( $icon_html ) : ?>
								<span class="zskeleton-term-list__icon-badge" aria-hidden="true">
									<?php echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
							<?php endif; ?>
						</span>
					<?php elseif ( 'icons' === $style ) : ?>
						<span class="zskeleton-term-list__media zskeleton-term-list__media--icon" aria-hidden="true">
							<?php
							if ( $icon_html ) {
								echo $icon_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							} else {
								$initial = function_exists( 'mb_substr' )
									? mb_substr( $term_item->name, 0, 1, 'UTF-8' )
									: substr( $term_item->name, 0, 1 );
								$initial = function_exists( 'mb_strtoupper' )
									? mb_strtoupper( $initial, 'UTF-8' )
									: strtoupper( $initial );
								echo '<span class="zskeleton-term-list__placeholder zskeleton-term-list__placeholder--round">' . esc_html( $initial ) . '</span>';
							}
							?>
						</span>
					<?php endif; ?>

					<span class="zskeleton-term-list__body">
						<span class="zskeleton-term-list__name"><?php echo esc_html( $term_item->name ); ?></span>
						<?php if ( $show_count && '' !== $count_label ) : ?>
							<span class="zskeleton-term-list__count"><?php echo esc_html( $count_label ); ?></span>
						<?php endif; ?>
						<?php if ( $term_item->description && 'simple' === $style ) : ?>
							<span class="zskeleton-term-list__excerpt"><?php echo esc_html( wp_trim_words( $term_item->description, 16 ) ); ?></span>
						<?php endif; ?>
					</span>
				</a>
			</li>
			<?php
		}
		?>
	</ul>
</section>
