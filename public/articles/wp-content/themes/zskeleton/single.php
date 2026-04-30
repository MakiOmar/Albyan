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
					
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'formal-card page-content' ); ?>>
						
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
						<section class="comments-section page-content" style="margin-top: 2rem;">
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

<style>
/* Single Post Styles */

/* Page Hero Section */
.page-hero {
	background: linear-gradient(135deg, var(--primary-blue), var(--academic-navy));
	color: white;
	padding: 80px 0;
	text-align: center;
	margin-bottom: 0;
}

.hero-content {
	max-width: 1200px;
	margin: 0 auto;
	padding: 0 20px;
}

.hero-title {
	color: white;
	font-size: 3rem;
	font-weight: 700;
	margin: 0 0 20px 0;
	line-height: 1.2;
}

.hero-description {
	font-size: 1.25rem;
	color: rgba(255,255,255,0.9);
	margin-bottom: 30px;
	line-height: 1.6;
	max-width: 800px;
	margin-left: auto;
	margin-right: auto;
}

.hero-meta {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 15px;
	font-size: 0.875rem;
	color: rgba(255,255,255,0.8);
	flex-wrap: wrap;
	margin-top: 20px;
}

.hero-date,
.hero-author,
.hero-category {
	color: rgba(255,255,255,0.8);
}

.meta-separator {
	color: rgba(255,255,255,0.6);
}

/* Page Layout */
.page-layout {
	display: flex;
	gap: 2.5rem;
	align-items: flex-start;
}

.main-content {
	flex: 1;
	min-width: 0;
	margin-top: 3rem;
}

.page-sidebar {
	flex: 0 0 300px;
	max-width: 25%;
}

.page-content {
	background: white;
	border-radius: 8px;
	box-shadow: 0 2px 10px rgba(0,0,0,0.1);
	overflow: hidden;
	margin-top: 0;
}

.member-only {
	background: var(--accent-gold);
	color: var(--academic-navy);
	padding: 6px 12px;
	border-radius: 20px;
	font-size: 0.75rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Featured Image */
.post-featured-image {
	width: 100%;
	margin: 0;
}

.post-featured-image img {
	width: 100%;
	height: auto;
	display: block;
}

.page-content-body {
	padding: 40px 30px;
	margin: 0;
}

.page-content-body h2 {
	color: var(--primary-blue);
	font-size: 1.75rem;
	margin: 40px 0 20px 0;
	font-weight: 600;
	padding-bottom: 10px;
	border-bottom: 2px solid var(--border-light);
}

.page-content-body h2:first-child {
	margin-top: 0;
}

.page-content-body h3 {
	color: var(--academic-navy);
	font-size: 1.5rem;
	margin: 30px 0 15px 0;
	font-weight: 600;
}

.page-content-body h4 {
	color: var(--academic-navy);
	font-size: 1.25rem;
	margin: 25px 0 12px 0;
	font-weight: 600;
}

.page-content-body p {
	margin-bottom: 20px;
	line-height: 1.7;
	color: var(--professional-gray);
	font-size: 1rem;
}

.page-content-body ul,
.page-content-body ol {
	margin: 20px 0;
	padding-left: 30px;
}

.page-content-body li {
	margin-bottom: 12px;
	line-height: 1.6;
	color: var(--professional-gray);
	font-size: 1rem;
}

/* Post Tags */
.post-tags {
	margin-top: 40px;
	padding-top: 30px;
	border-top: 2px solid var(--border-light);
}

.post-tags h4 {
	color: var(--primary-blue);
	font-size: 1rem;
	margin-bottom: 15px;
	font-weight: 600;
}

.tags-list {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
}

.tag-item {
	display: inline-block;
	padding: 6px 15px;
	background: var(--background-light);
	color: var(--primary-blue);
	text-decoration: none;
	border-radius: 20px;
	font-size: 0.875rem;
	transition: all 0.3s ease;
	border: 1px solid var(--border-light);
}

.tag-item:hover {
	background: var(--primary-blue);
	color: white;
	border-color: var(--primary-blue);
}

/* Comments spacing (replaces previous formal-card padding). */
.comments-section {
	padding: 24px;
}

.comments-section .comment-list > li {
	padding: 14px 0;
}

.comments-section .comment-list > li:first-child {
	padding-top: 0;
}

/* Post Navigation */
.post-navigation {
	margin-top: 40px;
}

.post-nav-links {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
	padding: 30px;
}

.nav-previous,
.nav-next {
	display: flex;
	flex-direction: column;
	gap: 10px;
}

.nav-next {
	text-align: right;
}

.nav-subtitle {
	font-size: 0.875rem;
	color: var(--professional-gray);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	font-weight: 600;
}

.nav-title {
	color: var(--primary-blue);
	font-size: 1.125rem;
	font-weight: 600;
	text-decoration: none;
	transition: color 0.3s ease;
}

.nav-title:hover {
	color: var(--academic-navy);
}

.page-links {
	margin: 40px 0;
	text-align: center;
	padding: 20px;
	background: var(--background-light);
	border-radius: 8px;
}

.page-links a {
	display: inline-block;
	padding: 10px 16px;
	margin: 0 8px 8px 0;
	background: white;
	color: var(--primary-blue);
	text-decoration: none;
	border-radius: 6px;
	border: 2px solid var(--border-light);
	transition: all 0.3s ease;
	font-weight: 500;
}

.page-links a:hover {
	background: var(--primary-blue);
	color: white;
	border-color: var(--primary-blue);
}

.member-access-notice {
	text-align: center;
	padding: 32px 28px;
	background: var(--background-light);
	border-radius: 16px;
	border: 1px solid rgba(1, 50, 95, 0.18);
	box-shadow: 0 10px 30px rgba(1, 50, 95, 0.08);
	margin: 30px 0;
	color: var(--primary-blue);
}

.member-access-notice .icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 72px;
	height: 72px;
	border-radius: 50%;
	background: rgba(1, 50, 95, 0.08);
	color: var(--primary-blue);
	font-size: 2.5rem;
	margin-bottom: 20px;
}

.member-access-notice h3 {
	color: var(--primary-blue);
	font-size: 1.6rem;
	margin-bottom: 16px;
	font-weight: 700;
}

.member-access-notice p {
	color: var(--primary-blue);
	margin: 0 auto 24px;
	max-width: 560px;
	font-size: 1rem;
	line-height: 1.6;
}

.member-access-actions {
	display: flex;
	gap: 20px;
	justify-content: center;
	margin-bottom: 25px;
	flex-wrap: wrap;
}

.free-content-notice {
	margin-top: 25px;
	padding-top: 25px;
	border-top: 1px solid var(--border-light);
}

.free-content-notice a {
	color: var(--primary-blue);
	text-decoration: none;
	font-weight: 500;
}

.free-content-notice a:hover {
	text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 1024px) {
	.hero-title {
		font-size: 2.5rem;
	}
	
	.hero-description {
		font-size: 1.125rem;
	}
	
	.page-layout {
		gap: 2rem;
	}
	
	.page-sidebar {
		flex: 0 0 280px;
	}
	
	.page-content-body {
		padding: 30px 25px;
	}
}

@media (max-width: 768px) {
	.page-hero {
		padding: 60px 0;
	}
	
	.hero-title {
		font-size: 2rem;
	}
	
	.hero-description {
		font-size: 1rem;
	}
	
	.hero-meta {
		flex-direction: column;
		align-items: center;
		gap: 8px;
	}
	
	.page-layout {
		flex-direction: column;
		gap: 1.5rem;
	}
	
	.page-sidebar {
		flex: none;
		max-width: 100%;
		order: 2;
	}
	
	.main-content {
		order: 1;
	}
	
	.post-nav-links {
		grid-template-columns: 1fr;
		gap: 30px;
	}
	
	.nav-next {
		text-align: left;
	}
	
	.member-access-actions {
		flex-direction: column;
		align-items: center;
	}
	
	.page-content-body {
		padding: 25px 20px;
	}
	
	.page-content-body h2 {
		font-size: 1.5rem;
	}
	
	.page-content-body h3 {
		font-size: 1.25rem;
	}
	
	.page-content-body h4 {
		font-size: 1.125rem;
	}
}

@media (max-width: 480px) {
	.page-hero {
		padding: 50px 0;
	}
	
	.hero-title {
		font-size: 1.75rem;
	}
	
	.hero-description {
		font-size: 0.95rem;
	}
	
	.member-access-notice {
		padding: 28px 20px;
	}
	
	.member-access-notice .icon {
		width: 60px;
		height: 60px;
		font-size: 2.1rem;
	}
	
	.page-content-body {
		padding: 20px 15px;
	}
}
</style>

<?php get_footer(); ?>

