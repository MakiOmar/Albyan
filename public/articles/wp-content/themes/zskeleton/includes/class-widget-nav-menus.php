<?php
/**
 * Widgets: horizontal, vertical, and plain-ul WordPress menus (no list bullets).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output a nav menu for widgets as a ul only (no container div or nav).
 *
 * @param int $menu_id Nav menu term ID.
 * @return void
 */
function zskeleton_widget_nav_menu_render_plain_ul( $menu_id ) {
	$menu_id = absint( $menu_id );
	if ( ! $menu_id ) {
		return;
	}

	$menu_obj = wp_get_nav_menu_object( $menu_id );
	if ( ! $menu_obj ) {
		return;
	}

	wp_nav_menu(
		array(
			'menu'         => $menu_id,
			'container'    => false,
			'menu_class'   => 'menu zskeleton-nav-menu__list zskeleton-nav-menu__list--plain',
			'fallback_cb'  => false,
			'depth'        => 0,
		)
	);
}

/**
 * Output a nav menu for widgets.
 *
 * @param int    $menu_id     Nav menu term ID.
 * @param string $orientation `horizontal` or `vertical`.
 * @return void
 */
function zskeleton_widget_nav_menu_render( $menu_id, $orientation ) {
	$menu_id = absint( $menu_id );
	if ( ! $menu_id ) {
		return;
	}

	$menu_obj = wp_get_nav_menu_object( $menu_id );
	if ( ! $menu_obj ) {
		return;
	}

	$orientation = ( 'horizontal' === $orientation ) ? 'horizontal' : 'vertical';

	wp_nav_menu(
		array(
			'menu'            => $menu_id,
			'container'       => 'nav',
			'container_class' => 'zskeleton-nav-menu zskeleton-nav-menu--' . $orientation,
			'container_aria_label' => $menu_obj->name,
			'menu_class'      => 'zskeleton-nav-menu__list',
			'fallback_cb'     => false,
			'depth'           => 0,
		)
	);
}

/**
 * Horizontal nav menu widget (flex row; no bullets).
 */
class ZSkeleton_Widget_Nav_Menu_Horizontal extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_nav_menu_horizontal',
			__( 'ZSkeleton: Menu (horizontal)', 'zskeleton' ),
			array(
				'description' => __( 'Display a menu as a horizontal row (list bullets hidden).', 'zskeleton' ),
				'classname'   => 'widget_zskeleton_nav_horizontal',
			)
		);
	}

	/**
	 * @param array<string, string> $args     Display args.
	 * @param array<string, mixed>  $instance Widget settings.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'nav_menu' => 0 ) );
		$menu_id  = absint( $instance['nav_menu'] );

		if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<p class="zskeleton-widget-placeholder">' . esc_html__( 'Select a menu in this widget’s settings.', 'zskeleton' ) . '</p>';
				echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = isset( $instance['title'] ) ? trim( (string) $instance['title'] ) : '';
		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		zskeleton_widget_nav_menu_render( $menu_id, 'horizontal' );

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, mixed> $instance Current settings.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'nav_menu' => 0 ) );
		$nav_menu = absint( $instance['nav_menu'] );
		$menus    = wp_get_nav_menus();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>"><?php esc_html_e( 'Menu:', 'zskeleton' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nav_menu' ) ); ?>">
				<option value="0"><?php esc_html_e( '— Select a menu —', 'zskeleton' ); ?></option>
				<?php foreach ( $menus as $menu ) : ?>
					<option value="<?php echo esc_attr( (string) $menu->term_id ); ?>" <?php selected( $nav_menu, (int) $menu->term_id ); ?>><?php echo esc_html( $menu->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * @param array<string, mixed> $new_instance New values.
	 * @param array<string, mixed> $old_instance Previous values.
	 * @return array<string, mixed>
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'    => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'nav_menu' => absint( $new_instance['nav_menu'] ?? 0 ),
		);
	}
}

/**
 * Vertical nav menu widget (stacked; no bullets).
 */
class ZSkeleton_Widget_Nav_Menu_Vertical extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_nav_menu_vertical',
			__( 'ZSkeleton: Menu (vertical)', 'zskeleton' ),
			array(
				'description' => __( 'Display a menu as a vertical list (list bullets hidden).', 'zskeleton' ),
				'classname'   => 'widget_zskeleton_nav_vertical',
			)
		);
	}

	/**
	 * @param array<string, string> $args     Display args.
	 * @param array<string, mixed>  $instance Widget settings.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'nav_menu' => 0 ) );
		$menu_id  = absint( $instance['nav_menu'] );

		if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<p class="zskeleton-widget-placeholder">' . esc_html__( 'Select a menu in this widget’s settings.', 'zskeleton' ) . '</p>';
				echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = isset( $instance['title'] ) ? trim( (string) $instance['title'] ) : '';
		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		zskeleton_widget_nav_menu_render( $menu_id, 'vertical' );

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, mixed> $instance Current settings.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'nav_menu' => 0 ) );
		$nav_menu = absint( $instance['nav_menu'] );
		$menus    = wp_get_nav_menus();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>"><?php esc_html_e( 'Menu:', 'zskeleton' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nav_menu' ) ); ?>">
				<option value="0"><?php esc_html_e( '— Select a menu —', 'zskeleton' ); ?></option>
				<?php foreach ( $menus as $menu ) : ?>
					<option value="<?php echo esc_attr( (string) $menu->term_id ); ?>" <?php selected( $nav_menu, (int) $menu->term_id ); ?>><?php echo esc_html( $menu->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * @param array<string, mixed> $new_instance New values.
	 * @param array<string, mixed> $old_instance Previous values.
	 * @return array<string, mixed>
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'    => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'nav_menu' => absint( $new_instance['nav_menu'] ?? 0 ),
		);
	}
}

/**
 * Nav menu widget: ul only (no extra nav or div wrapper around the list).
 */
class ZSkeleton_Widget_Nav_Menu_Plain_Ul extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_nav_menu_plain_ul',
			__( 'ZSkeleton: Menu (plain list)', 'zskeleton' ),
			array(
				'description' => __( 'Output a selected WordPress menu as a ul only — no extra wrapper (use your own block or theme markup around it if needed).', 'zskeleton' ),
				'classname'   => 'widget_zskeleton_nav_plain_ul',
			)
		);
	}

	/**
	 * @param array<string, string> $args     Display args.
	 * @param array<string, mixed>  $instance Widget settings.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'nav_menu' => 0 ) );
		$menu_id  = absint( $instance['nav_menu'] );

		if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<p class="zskeleton-widget-placeholder">' . esc_html__( 'Select a menu in this widget’s settings.', 'zskeleton' ) . '</p>';
				echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = isset( $instance['title'] ) ? trim( (string) $instance['title'] ) : '';
		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		zskeleton_widget_nav_menu_render_plain_ul( $menu_id );

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, mixed> $instance Current settings.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'nav_menu' => 0 ) );
		$nav_menu = absint( $instance['nav_menu'] );
		$menus    = wp_get_nav_menus();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>"><?php esc_html_e( 'Menu:', 'zskeleton' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nav_menu' ) ); ?>">
				<option value="0"><?php esc_html_e( '— Select a menu —', 'zskeleton' ); ?></option>
				<?php foreach ( $menus as $menu ) : ?>
					<option value="<?php echo esc_attr( (string) $menu->term_id ); ?>" <?php selected( $nav_menu, (int) $menu->term_id ); ?>><?php echo esc_html( $menu->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}

	/**
	 * @param array<string, mixed> $new_instance New values.
	 * @param array<string, mixed> $old_instance Previous values.
	 * @return array<string, mixed>
	 */
	public function update( $new_instance, $old_instance ) {
		return array(
			'title'    => sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) ),
			'nav_menu' => absint( $new_instance['nav_menu'] ?? 0 ),
		);
	}
}

/**
 * Register nav menu widgets.
 */
function zskeleton_register_nav_menu_widgets() {
	register_widget( 'ZSkeleton_Widget_Nav_Menu_Horizontal' );
	register_widget( 'ZSkeleton_Widget_Nav_Menu_Vertical' );
	register_widget( 'ZSkeleton_Widget_Nav_Menu_Plain_Ul' );
}
add_action( 'widgets_init', 'zskeleton_register_nav_menu_widgets', 11 );
