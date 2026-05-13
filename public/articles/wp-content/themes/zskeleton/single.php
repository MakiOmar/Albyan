<?php
/**
 * The template for displaying single blog posts
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
	
	<?php
	// Check if this post requires membership using the access control system.
	$user_id        = get_current_user_id();
	$has_access     = true;
	
	if ( class_exists( 'ZSkeleton_Access_Control' ) ) {
		$access_control = new ZSkeleton_Access_Control();
		$has_access     = $access_control->user_has_content_access( $user_id, get_the_ID() );
	}
	?>
	
	<!-- Full-Width Hero Section -->
	<section class="page-hero">
		<div class="hero-content">
			<?php
			$breadcrumbs_html = function_exists( 'zskeleton_get_blog_breadcrumbs_html' ) ? zskeleton_get_blog_breadcrumbs_html( get_the_ID() ) : '';
			if ( '' !== trim( (string) $breadcrumbs_html ) ) :
				?>
				<div class="hero-breadcrumbs">
					<?php echo wp_kses_post( $breadcrumbs_html ); ?>
				</div>
			<?php endif; ?>
			<h1 class="hero-title"><?php the_title(); ?></h1>
			
			<?php if ( get_the_excerpt() ) : ?>
				<p class="hero-description"><?php the_excerpt(); ?></p>
			<?php endif; ?>
			
			<div class="hero-meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" class="hero-date">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
				
				<?php
				$categories = get_the_category();
				if ( ! empty( $categories ) ) :
					?>
					<span class="meta-separator">•</span>
					<a class="hero-category" href="<?php echo esc_url( get_category_link( $categories[0]->term_id ) ); ?>">
						<?php echo esc_html( $categories[0]->name ); ?>
					</a>
				<?php endif; ?>
				
				<?php if ( ! $has_access && ! current_user_can( 'administrator' ) ) : ?>
					<span class="meta-separator">•</span>
					<span class="member-only">Members Only</span>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Main Content with Sidebar -->
	<main id="main" class="site-main" tabindex="-1">
		<?php do_action( 'zskeleton_before_main_content' ); ?>
		<div class="<?php echo zskeleton_page_main_container_class( 'wide-container', '', get_the_ID() ); ?>">
			<div class="<?php echo zskeleton_page_layout_class( '', get_the_ID() ); ?>">
				<div class="main-content">
					
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>
						
						<!-- Featured Image (or default placeholder when none is set) -->
						<?php if ( $has_access ) : ?>
							<div class="post-featured-image">
								<?php echo zskeleton_get_post_thumbnail_or_placeholder_html( null, 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' ) ); ?>
							</div>
						<?php endif; ?>
						
						<!-- Post Content -->
						<div class="page-content-body">
							<?php if ( $has_access ) : ?>
								<?php the_content(); ?>
								
								<?php
								// Page links for paginated content.
								wp_link_pages(
									array(
										'before' => '<div class="page-links">' . __( 'Pages:', 'zskeleton' ),
										'after'  => '</div>',
									)
								);
								?>
								
								<!-- Post Tags -->
								<?php
								$tags = get_the_tags();
								if ( $tags ) :
									?>
									<div class="post-tags">
										<h4><?php _e( 'Tags:', 'zskeleton' ); ?></h4>
										<div class="tags-list">
											<?php foreach ( $tags as $tag ) : ?>
												<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="tag-item">
													<?php echo esc_html( $tag->name ); ?>
												</a>
											<?php endforeach; ?>
										</div>
									</div>
								<?php endif; ?>
								
							<?php else : ?>
								<!-- Member Access Notice -->
								<div class="member-access-notice">
									<div class="icon">🔒</div>
									<h3><?php _e( 'Member Access Required', 'zskeleton' ); ?></h3>
									<p><?php _e( 'This content is available exclusively to ZSkeleton members. To access our comprehensive library, reports, and professional resources, please consider joining our membership.', 'zskeleton' ); ?></p>
									
									<div class="member-access-actions">
										<?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
										<a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
											<?php echo is_user_logged_in() ? __( 'Upgrade Membership', 'zskeleton' ) : __( 'Learn About Membership', 'zskeleton' ); ?>
										</a>
										<?php endif; ?>
										<?php if ( ! is_user_logged_in() ) : ?>
										<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="btn btn-secondary">
											<?php _e( 'Member Login', 'zskeleton' ); ?>
										</a>
										<?php endif; ?>
									</div>
									
									<div class="free-content-notice">
										<p><small><?php _e( 'New to ZSkeleton? Check out our', 'zskeleton' ); ?> <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>"><?php _e( 'free articles', 'zskeleton' ); ?></a> <?php _e( 'to get started.', 'zskeleton' ); ?></small></p>
									</div>
								</div>
							<?php endif; ?>
						</div>
						
					</article>

					<?php if ( $has_access && ( comments_open() || get_comments_number() ) ) : ?>
						<section class="comments-section page-content comments-section-spaced">
							<?php comments_template(); ?>
						</section>
					<?php endif; ?>
					
					<?php if ( $has_access ) : ?>
						<!-- Post Navigation -->
						<div class="post-navigation">
							<?php
							$prev_post = get_previous_post();
							$next_post = get_next_post();
							?>
							
							<?php if ( $prev_post || $next_post ) : ?>
								<div class="post-nav-links formal-card">
									<?php if ( $prev_post ) : ?>
										<div class="nav-previous">
											<span class="nav-subtitle"><?php _e( '← Previous Post', 'zskeleton' ); ?></span>
											<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="nav-title" rel="prev">
												<?php echo esc_html( $prev_post->post_title ); ?>
											</a>
										</div>
									<?php endif; ?>
									
									<?php if ( $next_post ) : ?>
										<div class="nav-next">
											<span class="nav-subtitle"><?php _e( 'Next Post →', 'zskeleton' ); ?></span>
											<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="nav-title" rel="next">
												<?php echo esc_html( $next_post->post_title ); ?>
											</a>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( $has_access ) : ?>
						<?php
						$related_posts = function_exists( 'zskeleton_blog_hub_related_posts_query' ) ? zskeleton_blog_hub_related_posts_query( get_the_ID(), 3 ) : null;
						?>
						<?php if ( $related_posts instanceof WP_Query && $related_posts->have_posts() ) : ?>
							<section class="blog-hub-section blog-hub-related" aria-labelledby="blog-hub-related-heading">
								<div class="blog-hub-section__head">
									<h2 id="blog-hub-related-heading" class="blog-hub-section__title"><?php esc_html_e( 'Related articles', 'zskeleton' ); ?></h2>
								</div>
								<div class="blog-hub-trending__grid practices-grid">
									<?php
									while ( $related_posts->have_posts() ) :
										$related_posts->the_post();
										get_template_part( 'template-parts/blog/blog', 'card', array( 'post' => get_post() ) );
									endwhile;
									wp_reset_postdata();
									?>
								</div>
							</section>
						<?php endif; ?>
					<?php endif; ?>
					
				</div><!-- .main-content -->
				
				<div class="page-sidebar">
					<?php get_sidebar(); ?>
				</div>
				
			</div><!-- .page-layout -->
		</div><!-- .wide-container -->
		<?php do_action( 'zskeleton_after_main_content' ); ?>
	</main><!-- #main -->
	
<?php endwhile; ?>

<?php get_footer(); ?>

