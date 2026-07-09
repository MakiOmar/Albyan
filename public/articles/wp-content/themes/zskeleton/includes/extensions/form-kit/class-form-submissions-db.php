<?php
/**
 * Form Kit submissions custom table installer.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Creates and upgrades {prefix}zskeleton_form_submissions.
 */
class ZSkeleton_Form_Submissions_DB {

	const DB_VERSION = '1.0.0';

	const OPTION_KEY = 'zskeleton_form_submissions_db_version';

	/**
	 * Register install hooks.
	 */
	public static function init() {
		add_action( 'after_switch_theme', array( __CLASS__, 'install' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_install' ) );
	}

	/**
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'zskeleton_form_submissions';
	}

	/**
	 * Create or upgrade the submissions table.
	 */
	public static function install() {
		global $wpdb;

		$table   = self::get_table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_id varchar(100) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'new',
			payload longtext NOT NULL,
			ip_hash varchar(64) NOT NULL DEFAULT '',
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			meta longtext NULL,
			PRIMARY KEY  (id),
			KEY form_created (form_id, created_at),
			KEY status (status),
			KEY form_status (form_id, status)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::OPTION_KEY, self::DB_VERSION );
	}

	/**
	 * Install when version changes.
	 */
	public static function maybe_install() {
		if ( self::DB_VERSION !== get_option( self::OPTION_KEY, '' ) ) {
			self::install();
		}
	}
}
