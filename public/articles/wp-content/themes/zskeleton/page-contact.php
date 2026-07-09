<?php
/**
 * Template Name: Contact (modern)
 * Description: Full contact page with details grid and Form Kit message form.
 *
 * @package ZSkeleton_Theme
 */

get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$meta = zskeleton_get_contact_page_meta();
		zskeleton_the_page_title_bar(
			array(
				'post_id'  => get_the_ID(),
				'title'    => get_the_title(),
				'subtitle' => $meta['subtitle'],
			)
		);

		$raw_content = get_post()->post_content;
		$intro_html  = '';
		if ( is_string( $raw_content ) && '' !== trim( $raw_content ) ) {
			$intro_html = apply_filters( 'the_content', $raw_content );
			$intro_html = is_string( $intro_html ) ? wp_kses_post( $intro_html ) : '';
		}
		?>
		<div class="site-content zs-contact-site">
			<div class="<?php echo zskeleton_page_main_container_class( 'container', '', get_the_ID() ); ?>">
				<?php
				zskeleton_render_contact_page_layout(
					array(
						'intro_html' => $intro_html,
					)
				);
				?>
			</div>
		</div>
		<?php
	}
}

get_footer();
