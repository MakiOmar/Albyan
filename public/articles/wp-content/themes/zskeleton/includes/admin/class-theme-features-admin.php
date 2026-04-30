<?php
/**
 * Theme Features — admin hub (separate from Appearance → ZSkeleton Theme Settings).
 *
 * Child menus can be added later; glossary uses CPT under this menu.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Top-level “Theme Features” menu and overview screen.
 */
class ZSkeleton_Theme_Features_Admin {

	/**
	 * Parent menu slug (must match CPT show_in_menu).
	 */
	const MENU_SLUG = 'zskeleton-theme-features';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 3 );
	}

	/**
	 * Register top-level menu and Overview submenu.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Theme Features', 'zskeleton' ),
			__( 'Theme Features', 'zskeleton' ),
			'edit_theme_options',
			self::MENU_SLUG,
			array( $this, 'render_dashboard' ),
			'dashicons-admin-tools',
			61
		);

		remove_submenu_page( self::MENU_SLUG, self::MENU_SLUG );

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Overview', 'zskeleton' ),
			__( 'Overview', 'zskeleton' ),
			'edit_theme_options',
			self::MENU_SLUG,
			array( $this, 'render_dashboard' )
		);
	}

	/**
	 * Overview landing (links to feature areas).
	 */
	public function render_dashboard() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'zskeleton' ) );
		}

		$glossary_url = admin_url( 'edit.php?post_type=' . ZSkeleton_Glossary_Terms::POST_TYPE );
		$sliders_url  = admin_url( 'edit.php?post_type=' . ZSkeleton_Sliders::POST_TYPE );
		?>
		<div class="wrap zskeleton-theme-features-wrap">
			<h1><?php esc_html_e( 'Theme Features', 'zskeleton' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Structured tools and content that ship with ZSkeleton. This area is separate from ZSkeleton Theme Settings under Appearance.', 'zskeleton' ); ?>
			</p>

			<!-- Theme features: module cards; add more links as new sub-features ship. -->
			<div class="zskeleton-theme-features-cards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;margin-top:24px;max-width:920px;">
				<div class="card" style="padding:0;overflow:hidden;border:1px solid #c3c4c7;background:#fff;">
					<div class="card-header" style="padding:12px 16px;border-bottom:1px solid #dcdcde;background:#f6f7f7;">
						<h2 class="title" style="margin:0;font-size:15px;"><?php esc_html_e( 'Sliders', 'zskeleton' ); ?></h2>
					</div>
					<div class="card-body" style="padding:16px;">
						<p style="margin-top:0;">
							<?php esc_html_e( 'Build animated sliders with slides (optional text and two buttons). Embed with the shortcode on any page.', 'zskeleton' ); ?>
						</p>
						<p style="margin:0 0 8px;font-size:13px;color:#50575e;">
							<code>[zskeleton_slider id="123"]</code>
							<?php esc_html_e( 'or', 'zskeleton' ); ?>
							<code>[zskeleton_slider slug="hero"]</code>
						</p>
						<p style="margin-bottom:0;">
							<a class="button button-primary" href="<?php echo esc_url( $sliders_url ); ?>">
								<?php esc_html_e( 'Manage sliders', 'zskeleton' ); ?>
							</a>
						</p>
					</div>
				</div>
				<div class="card" style="padding:0;overflow:hidden;border:1px solid #c3c4c7;background:#fff;">
					<div class="card-header" style="padding:12px 16px;border-bottom:1px solid #dcdcde;background:#f6f7f7;">
						<h2 class="title" style="margin:0;font-size:15px;"><?php esc_html_e( 'Glossary', 'zskeleton' ); ?></h2>
					</div>
					<div class="card-body" style="padding:16px;">
						<p style="margin-top:0;">
							<?php esc_html_e( 'Create and order term + definition entries. Use the entry title for the term and the main editor for the definition.', 'zskeleton' ); ?>
						</p>
						<p style="margin-bottom:0;">
							<a class="button button-primary" href="<?php echo esc_url( $glossary_url ); ?>">
								<?php esc_html_e( 'Manage glossary', 'zskeleton' ); ?>
							</a>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
