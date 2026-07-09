<?php
/**
 * Primary sidebar widgets (membership, search, recent posts, tags, contact).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a widget instance to a sidebar.
 *
 * @param string               $id_base    Widget id base.
 * @param array<string, mixed> $instance   Widget settings.
 * @param string               $sidebar_id Sidebar id.
 * @return string Widget id (e.g. zskeleton_sidebar_search-3).
 */
function zskeleton_sidebar_add_widget_instance( string $id_base, array $instance, string $sidebar_id = 'sidebar-1' ): string {
	$option    = 'widget_' . $id_base;
	$instances = get_option( $option, array() );
	if ( ! is_array( $instances ) ) {
		$instances = array();
	}

	$next = 0;
	foreach ( array_keys( $instances ) as $key ) {
		if ( is_numeric( $key ) ) {
			$next = max( $next, (int) $key );
		}
	}
	++$next;

	$instances[ $next ]        = $instance;
	$instances['_multiwidget'] = 1;
	update_option( $option, $instances );

	$sidebars = get_option( 'sidebars_widgets', array() );
	if ( ! is_array( $sidebars ) ) {
		$sidebars = array();
	}
	if ( ! isset( $sidebars[ $sidebar_id ] ) || ! is_array( $sidebars[ $sidebar_id ] ) ) {
		$sidebars[ $sidebar_id ] = array();
	}

	$widget_id                    = $id_base . '-' . $next;
	$sidebars[ $sidebar_id ][]    = $widget_id;
	$sidebars['array_version']    = 3;
	update_option( 'sidebars_widgets', $sidebars );

	return $widget_id;
}

/**
 * Seed default primary sidebar widgets once (replaces legacy hardcoded sidebar.php blocks).
 *
 * @return void
 */
function zskeleton_seed_primary_sidebar_widgets(): void {
	if ( get_option( 'zskeleton_primary_sidebar_widgets_v1' ) ) {
		return;
	}

	$sidebars = wp_get_sidebars_widgets();
	$current  = isset( $sidebars['sidebar-1'] ) && is_array( $sidebars['sidebar-1'] ) ? $sidebars['sidebar-1'] : array();
	$current  = array_values(
		array_filter(
			$current,
			static function ( $widget_id ) {
				return is_string( $widget_id ) && '' !== $widget_id;
			}
		)
	);

	if ( ! empty( $current ) ) {
		update_option( 'zskeleton_primary_sidebar_widgets_v1', 1, false );
		return;
	}

	$mship_enabled = function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled();

	if ( $mship_enabled ) {
		zskeleton_sidebar_add_widget_instance(
			'zskeleton_sidebar_membership',
			array(
				'title'      => __( 'Membership Status', 'zskeleton' ),
				'join_title' => function_exists( 'zskeleton_sprintf_site_name' )
					? zskeleton_sprintf_site_name( __( 'Join %s', 'zskeleton' ) )
					: __( 'Join', 'zskeleton' ),
			)
		);
	}

	zskeleton_sidebar_add_widget_instance(
		'zskeleton_sidebar_search',
		array(
			'title'             => __( 'Search Resources', 'zskeleton' ),
			'show_browse_links' => '1',
		)
	);

	zskeleton_sidebar_add_widget_instance(
		'zskeleton_sidebar_recent_posts',
		array(
			'title'          => __( 'Latest Updates', 'zskeleton' ),
			'posts_per_page' => 5,
			'show_view_all'  => '1',
		)
	);

	zskeleton_sidebar_add_widget_instance(
		'zskeleton_sidebar_tags',
		array(
			'title'       => __( 'Tags', 'zskeleton' ),
			'number'      => 20,
			'orderby'     => 'count',
			'order'       => 'DESC',
			'hide_empty'  => '1',
		)
	);

	zskeleton_sidebar_add_widget_instance(
		'zskeleton_sidebar_contact',
		array(
			'title'       => __( 'Get in Touch', 'zskeleton' ),
			'description' => __( 'Have questions about membership or your account?', 'zskeleton' ),
		)
	);

	update_option( 'zskeleton_primary_sidebar_widgets_v1', 1, false );
}
add_action( 'widgets_init', 'zskeleton_seed_primary_sidebar_widgets', 20 );

/**
 * Append Tags widget to primary sidebar once (for sites seeded before tags existed).
 *
 * @return void
 */
function zskeleton_seed_primary_sidebar_tags_widget(): void {
	if ( get_option( 'zskeleton_primary_sidebar_tags_widget_v1' ) ) {
		return;
	}

	$sidebars = wp_get_sidebars_widgets();
	$current  = isset( $sidebars['sidebar-1'] ) && is_array( $sidebars['sidebar-1'] ) ? $sidebars['sidebar-1'] : array();

	foreach ( $current as $widget_id ) {
		if ( is_string( $widget_id ) && str_starts_with( $widget_id, 'zskeleton_sidebar_tags' ) ) {
			update_option( 'zskeleton_primary_sidebar_tags_widget_v1', 1, false );
			return;
		}
	}

	zskeleton_sidebar_add_widget_instance(
		'zskeleton_sidebar_tags',
		array(
			'title'      => __( 'Tags', 'zskeleton' ),
			'number'     => 20,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => '1',
		)
	);

	update_option( 'zskeleton_primary_sidebar_tags_widget_v1', 1, false );
}
add_action( 'widgets_init', 'zskeleton_seed_primary_sidebar_tags_widget', 21 );

/**
 * Membership / join CTA sidebar widget.
 */
class ZSkeleton_Widget_Sidebar_Membership extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_sidebar_membership',
			__( 'ZSkeleton: Sidebar Membership', 'zskeleton' ),
			array(
				'description' => __( 'Shows membership status for logged-in users or a join/login prompt for guests.', 'zskeleton' ),
				'classname'   => 'widget membership-widget',
			)
		);
	}

	/**
	 * @return array<string, string>
	 */
	private function defaults(): array {
		return array(
			'title'      => __( 'Membership Status', 'zskeleton' ),
			'join_title' => function_exists( 'zskeleton_sprintf_site_name' )
				? zskeleton_sprintf_site_name( __( 'Join %s', 'zskeleton' ) )
				: __( 'Join', 'zskeleton' ),
		);
	}

	/**
	 * @param array<string, string> $args     Display args.
	 * @param array<string, string> $instance Settings.
	 * @return void
	 */
	public function widget( $args, $instance ): void {
		if ( ! function_exists( 'zskeleton_is_memberships_feature_enabled' ) || ! zskeleton_is_memberships_feature_enabled() ) {
			return;
		}

		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( is_user_logged_in() ) {
			$user_id         = get_current_user_id();
			$has_membership  = class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( $user_id );
			$membership_type = class_exists( 'ZSkeleton_User_Profile_Fields' ) ? ZSkeleton_User_Profile_Fields::get_user_membership_type( $user_id ) : 'none';
			$title           = trim( (string) $instance['title'] );

			if ( '' !== $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( $has_membership ) {
				?>
				<div class="membership-active">
					<div class="membership-badge">
						<span class="member-type"><?php echo esc_html( ucfirst( $membership_type ) ); ?> <?php esc_html_e( 'Member', 'zskeleton' ); ?></span>
						<span class="status-active"><?php esc_html_e( 'Active', 'zskeleton' ); ?></span>
					</div>
					<div class="quick-links">
						<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'profile' ) ) ); ?>" class="btn btn-secondary btn-small">
							<?php esc_html_e( 'View Profile', 'zskeleton' ); ?>
						</a>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="membership-inactive">
					<p><?php echo esc_html( zskeleton_sprintf_site_name( __( 'Unlock exclusive %s content with a membership.', 'zskeleton' ) ) ); ?></p>
					<a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Join Now', 'zskeleton' ); ?>
					</a>
				</div>
				<?php
			}
		} else {
			$join_title = trim( (string) $instance['join_title'] );
			if ( '' !== $join_title ) {
				echo $args['before_title'] . esc_html( $join_title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
			<p><?php echo esc_html( zskeleton_sprintf_site_name( __( 'Become a member of %s and access exclusive content and resources.', 'zskeleton' ) ) ); ?></p>
			<div class="join-actions">
				<a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
					<?php esc_html_e( 'Learn More', 'zskeleton' ); ?>
				</a>
				<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="btn btn-secondary">
					<?php esc_html_e( 'Member Login', 'zskeleton' ); ?>
				</a>
			</div>
			<?php
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, string> $instance Current settings.
	 * @return void
	 */
	public function form( $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (logged-in members):', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'join_title' ) ); ?>"><?php esc_html_e( 'Title (guests):', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'join_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'join_title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['join_title'] ); ?>" />
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New settings.
	 * @param array<string, string> $old_instance Old settings.
	 * @return array<string, string>
	 */
	public function update( $new_instance, $old_instance ): array {
		unset( $old_instance );
		return array(
			'title'      => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'join_title' => sanitize_text_field( (string) ( $new_instance['join_title'] ?? '' ) ),
		);
	}
}

/**
 * Search form + browse links sidebar widget.
 */
class ZSkeleton_Widget_Sidebar_Search extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_sidebar_search',
			__( 'ZSkeleton: Sidebar Search', 'zskeleton' ),
			array(
				'description' => __( 'Search form with optional “Browse by Page” links from theme settings.', 'zskeleton' ),
				'classname'   => 'widget search-widget',
			)
		);
	}

	/**
	 * @return array<string, string>
	 */
	private function defaults(): array {
		return array(
			'title'             => __( 'Search Resources', 'zskeleton' ),
			'show_browse_links' => '1',
		);
	}

	/**
	 * @param array<string, string> $args     Display args.
	 * @param array<string, string> $instance Settings.
	 * @return void
	 */
	public function widget( $args, $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$title    = trim( (string) $instance['title'] );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		get_search_form();

		$show_browse = ! empty( $instance['show_browse_links'] );
		if ( $show_browse && function_exists( 'zskeleton_sidebar_has_browse_links' ) && zskeleton_sidebar_has_browse_links() ) {
			$mship_enabled = function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled();
			?>
			<div class="search-categories">
				<h4><?php esc_html_e( 'Browse by Page', 'zskeleton' ); ?></h4>
				<ul class="category-links">
					<?php if ( zskeleton_sidebar_browse_link_enabled( 'about' ) ) : ?>
					<li><a href="<?php echo esc_url( zskeleton_get_page_url( 'about' ) ); ?>"><?php esc_html_e( 'About', 'zskeleton' ); ?></a></li>
					<?php endif; ?>
					<?php if ( zskeleton_sidebar_browse_link_enabled( 'faqs' ) ) : ?>
					<li><a href="<?php echo esc_url( zskeleton_get_page_url( 'faqs' ) ); ?>"><?php esc_html_e( 'FAQs', 'zskeleton' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $mship_enabled && zskeleton_sidebar_browse_link_enabled( 'memberships' ) ) : ?>
					<li><a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>"><?php esc_html_e( 'Memberships', 'zskeleton' ); ?></a></li>
					<?php endif; ?>
					<?php if ( zskeleton_sidebar_browse_link_enabled( 'contact' ) ) : ?>
					<li><a href="<?php echo esc_url( function_exists( 'zskeleton_get_theme_contact_page_url' ) ? zskeleton_get_theme_contact_page_url() : zskeleton_get_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'zskeleton' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
			<?php
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, string> $instance Current settings.
	 * @return void
	 */
	public function form( $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_browse_links' ) ); ?>" value="1" <?php checked( $instance['show_browse_links'], '1' ); ?> />
				<?php esc_html_e( 'Show “Browse by Page” links', 'zskeleton' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New settings.
	 * @param array<string, string> $old_instance Old settings.
	 * @return array<string, string>
	 */
	public function update( $new_instance, $old_instance ): array {
		unset( $old_instance );
		return array(
			'title'             => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'show_browse_links' => ! empty( $new_instance['show_browse_links'] ) ? '1' : '0',
		);
	}
}

/**
 * Recent posts sidebar widget.
 */
class ZSkeleton_Widget_Sidebar_Recent_Posts extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_sidebar_recent_posts',
			__( 'ZSkeleton: Sidebar Recent Posts', 'zskeleton' ),
			array(
				'description' => __( 'Lists recent posts with membership lock indicators.', 'zskeleton' ),
				'classname'   => 'widget recent-content-widget',
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function defaults(): array {
		return array(
			'title'          => __( 'Latest Updates', 'zskeleton' ),
			'posts_per_page' => 5,
			'show_view_all'  => '1',
		);
	}

	/**
	 * @param array<string, string> $args     Display args.
	 * @param array<string, string> $instance Settings.
	 * @return void
	 */
	public function widget( $args, $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$title    = trim( (string) $instance['title'] );
		$count    = max( 1, min( 20, (int) $instance['posts_per_page'] ) );

		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => $count,
				'post_status'         => 'publish',
				'orderby'             => 'date',
				'order'               => 'DESC',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( $query->have_posts() ) {
			echo '<ul class="recent-content-list">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$user_id    = get_current_user_id();
				$has_access = true;
				if ( class_exists( 'ZSkeleton_Access_Control' ) ) {
					$access_control = new ZSkeleton_Access_Control();
					$has_access     = $access_control->user_has_content_access( $user_id, get_the_ID() );
				}
				$type_obj = get_post_type_object( get_post_type() );
				$type_lbl = $type_obj ? $type_obj->labels->singular_name : '';
				?>
				<li class="recent-item">
					<h4 class="recent-title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h4>
					<?php if ( '' !== $type_lbl || ( ! $has_access && ! current_user_can( 'administrator' ) ) ) : ?>
					<p class="recent-meta">
						<?php if ( '' !== $type_lbl ) : ?>
							<span class="recent-type"><?php echo esc_html( $type_lbl ); ?></span>
						<?php endif; ?>
						<?php if ( ! $has_access && ! current_user_can( 'administrator' ) ) : ?>
							<span class="member-only-small" aria-hidden="true">&#128274;</span>
						<?php endif; ?>
					</p>
					<?php endif; ?>
				</li>
				<?php
			}
			echo '</ul>';
			wp_reset_postdata();
		}

		if ( ! empty( $instance['show_view_all'] ) ) {
			$blog_url = function_exists( 'zskeleton_get_theme_blog_listing_url' )
				? zskeleton_get_theme_blog_listing_url()
				: zskeleton_get_page_url( 'blog' );
			?>
			<div class="view-all">
				<a href="<?php echo esc_url( $blog_url ); ?>" class="btn btn-secondary btn-small">
					<?php esc_html_e( 'View All Posts', 'zskeleton' ); ?>
				</a>
			</div>
			<?php
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, string> $instance Current settings.
	 * @return void
	 */
	public function form( $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'posts_per_page' ) ); ?>"><?php esc_html_e( 'Number of posts:', 'zskeleton' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'posts_per_page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'posts_per_page' ) ); ?>" type="number" min="1" max="20" value="<?php echo esc_attr( (string) $instance['posts_per_page'] ); ?>" />
		</p>
		<p>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_view_all' ) ); ?>" value="1" <?php checked( $instance['show_view_all'], '1' ); ?> />
				<?php esc_html_e( 'Show “View all posts” button', 'zskeleton' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New settings.
	 * @param array<string, string> $old_instance Old settings.
	 * @return array<string, mixed>
	 */
	public function update( $new_instance, $old_instance ): array {
		unset( $old_instance );
		return array(
			'title'          => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'posts_per_page' => max( 1, min( 20, (int) ( $new_instance['posts_per_page'] ?? 5 ) ) ),
			'show_view_all'  => ! empty( $new_instance['show_view_all'] ) ? '1' : '0',
		);
	}
}

/**
 * Post tags cloud/list sidebar widget.
 */
class ZSkeleton_Widget_Sidebar_Tags extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_sidebar_tags',
			__( 'ZSkeleton: Sidebar Tags', 'zskeleton' ),
			array(
				'description' => __( 'Lists post tags as clickable links.', 'zskeleton' ),
				'classname'   => 'widget tags-widget',
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function defaults(): array {
		return array(
			'title'      => __( 'Tags', 'zskeleton' ),
			'number'     => 20,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => '1',
		);
	}

	/**
	 * @param array<string, string> $args     Display args.
	 * @param array<string, string> $instance Settings.
	 * @return void
	 */
	public function widget( $args, $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$title    = trim( (string) $instance['title'] );
		$number   = max( 1, min( 50, (int) $instance['number'] ) );
		$orderby  = in_array( (string) $instance['orderby'], array( 'count', 'name' ), true )
			? (string) $instance['orderby']
			: 'count';
		$order    = 'ASC' === strtoupper( (string) $instance['order'] ) ? 'ASC' : 'DESC';

		$tags = get_terms(
			array(
				'taxonomy'   => 'post_tag',
				'number'     => $number,
				'orderby'    => $orderby,
				'order'      => $order,
				'hide_empty' => ! empty( $instance['hide_empty'] ),
			)
		);

		if ( is_wp_error( $tags ) || empty( $tags ) ) {
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<div class="tags-list" role="list">
			<?php foreach ( $tags as $tag ) : ?>
				<a
					href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"
					class="tag-item"
					role="listitem"
				>
					<?php echo esc_html( $tag->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>
		<?php

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, string> $instance Current settings.
	 * @return void
	 */
	public function form( $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of tags:', 'zskeleton' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" min="1" max="50" value="<?php echo esc_attr( (string) $instance['number'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order by:', 'zskeleton' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
				<option value="count" <?php selected( $instance['orderby'], 'count' ); ?>><?php esc_html_e( 'Post count', 'zskeleton' ); ?></option>
				<option value="name" <?php selected( $instance['orderby'], 'name' ); ?>><?php esc_html_e( 'Name', 'zskeleton' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>"><?php esc_html_e( 'Order:', 'zskeleton' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>">
				<option value="DESC" <?php selected( strtoupper( (string) $instance['order'] ), 'DESC' ); ?>><?php esc_html_e( 'Descending', 'zskeleton' ); ?></option>
				<option value="ASC" <?php selected( strtoupper( (string) $instance['order'] ), 'ASC' ); ?>><?php esc_html_e( 'Ascending', 'zskeleton' ); ?></option>
			</select>
		</p>
		<p>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'hide_empty' ) ); ?>" value="1" <?php checked( $instance['hide_empty'], '1' ); ?> />
				<?php esc_html_e( 'Hide tags with no posts', 'zskeleton' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New settings.
	 * @param array<string, string> $old_instance Old settings.
	 * @return array<string, mixed>
	 */
	public function update( $new_instance, $old_instance ): array {
		unset( $old_instance );
		$orderby = (string) ( $new_instance['orderby'] ?? 'count' );
		return array(
			'title'      => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'number'     => max( 1, min( 50, (int) ( $new_instance['number'] ?? 20 ) ) ),
			'orderby'    => in_array( $orderby, array( 'count', 'name' ), true ) ? $orderby : 'count',
			'order'      => 'ASC' === strtoupper( (string) ( $new_instance['order'] ?? 'DESC' ) ) ? 'ASC' : 'DESC',
			'hide_empty' => ! empty( $new_instance['hide_empty'] ) ? '1' : '0',
		);
	}
}

/**
 * Contact links sidebar widget.
 */
class ZSkeleton_Widget_Sidebar_Contact extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_sidebar_contact',
			__( 'ZSkeleton: Sidebar Contact', 'zskeleton' ),
			array(
				'description' => __( 'Contact page link and optional membership/media email links.', 'zskeleton' ),
				'classname'   => 'widget contact-widget',
			)
		);
	}

	/**
	 * @return array<string, string>
	 */
	private function defaults(): array {
		return array(
			'title'       => __( 'Get in Touch', 'zskeleton' ),
			'description' => __( 'Have questions about membership or your account?', 'zskeleton' ),
		);
	}

	/**
	 * @param array<string, string> $args     Display args.
	 * @param array<string, string> $instance Settings.
	 * @return void
	 */
	public function widget( $args, $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$title    = trim( (string) $instance['title'] );
		$desc     = trim( (string) $instance['description'] );

		$membership_email = function_exists( 'zskeleton_get_contact' )
			? sanitize_email( (string) zskeleton_get_contact( 'membership_email' ) )
			: sanitize_email( (string) get_option( 'zskeleton_membership_email', '' ) );
		$media_email      = function_exists( 'zskeleton_get_contact' )
			? sanitize_email( (string) zskeleton_get_contact( 'media_email' ) )
			: sanitize_email( (string) get_option( 'zskeleton_media_email', '' ) );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( '' !== $desc ) {
			echo '<p>' . esc_html( $desc ) . '</p>';
		}
		?>
		<div class="contact-links">
			<a href="<?php echo esc_url( function_exists( 'zskeleton_get_theme_contact_page_url' ) ? zskeleton_get_theme_contact_page_url() : zskeleton_get_page_url( 'contact' ) ); ?>" class="contact-link">
				<span class="contact-icon" aria-hidden="true">&#9993;&#65039;</span>
				<span class="contact-text"><?php esc_html_e( 'Contact Us', 'zskeleton' ); ?></span>
			</a>
			<?php if ( '' !== $membership_email ) : ?>
			<a href="<?php echo esc_url( 'mailto:' . $membership_email ); ?>" class="contact-link">
				<span class="contact-icon" aria-hidden="true">&#128101;</span>
				<span class="contact-text"><?php esc_html_e( 'Membership Inquiries', 'zskeleton' ); ?></span>
			</a>
			<?php endif; ?>
			<?php if ( '' !== $media_email ) : ?>
			<a href="<?php echo esc_url( 'mailto:' . $media_email ); ?>" class="contact-link">
				<span class="contact-icon" aria-hidden="true">&#128240;</span>
				<span class="contact-text"><?php esc_html_e( 'Media & Press', 'zskeleton' ); ?></span>
			</a>
			<?php endif; ?>
		</div>
		<?php

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, string> $instance Current settings.
	 * @return void
	 */
	public function form( $instance ): void {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', 'zskeleton' ); ?></label>
			<textarea class="widefat" rows="3" id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>"><?php echo esc_textarea( (string) $instance['description'] ); ?></textarea>
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New settings.
	 * @param array<string, string> $old_instance Old settings.
	 * @return array<string, string>
	 */
	public function update( $new_instance, $old_instance ): array {
		unset( $old_instance );
		return array(
			'title'       => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'description' => sanitize_text_field( (string) ( $new_instance['description'] ?? '' ) ),
		);
	}
}

/**
 * Register primary sidebar widgets.
 *
 * @return void
 */
function zskeleton_register_sidebar_widgets(): void {
	register_widget( 'ZSkeleton_Widget_Sidebar_Membership' );
	register_widget( 'ZSkeleton_Widget_Sidebar_Search' );
	register_widget( 'ZSkeleton_Widget_Sidebar_Recent_Posts' );
	register_widget( 'ZSkeleton_Widget_Sidebar_Tags' );
	register_widget( 'ZSkeleton_Widget_Sidebar_Contact' );
}
add_action( 'widgets_init', 'zskeleton_register_sidebar_widgets', 11 );
