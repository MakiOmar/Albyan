<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "byanartic" );
/** Database username */
define( 'DB_USER', "albyandb" );
/** Database password */
define( 'DB_PASSWORD', "StrongPass123!" );
/** Database hostname */
define( 'DB_HOST', "localhost" );
/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );
/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '`mOQs1*;o)PY[vz-TG.E|$c31UnL{Efv_oh*;u -cZNNJ- t4w~=5(V+1aKI]*wK' );
define( 'SECURE_AUTH_KEY',  'Z7]]48eW|4EeqAa/:?/EZ5=fT?Y)2Poe5nY^u}!,i+S-;GJ>2T0C0ZPx_ZTZ@C6I' );
define( 'LOGGED_IN_KEY',    's$o8o L1L|O|*`m>?D&%JoW5Gkxsq~E%OGRr!$zt{E%OR2|I!#b=ER$ET]UNDCfa' );
define( 'NONCE_KEY',        'BKr<k;px3Mz&v?LW@_lY8Rp3b!vEr}&F<J^]2EB-mGPQWF!]BI.s-gbmV<;N$wy.' );
define( 'AUTH_SALT',        '-jB5!v:,5cp)>SEtar/IFUm(~eTxB#*/0*s<{u>A~Trhy~L?83w^oy0tTMs&*e`2' );
define( 'SECURE_AUTH_SALT', '9WK#@)MF$kmllvX6iU1+,r5!bru0ZUD-U_UZ?uXK=(AqQ2pWzzOv^7t;R|j3#7Jt' );
define( 'LOGGED_IN_SALT',   '*uJ3LW )5I&;00M<;<trKjaC?zf&E!Zx:TNiL&yX].C;@Mrg6ye{RC[x4M#[o>qM' );
define( 'NONCE_SALT',       'nr2Pn+rvTyM3p6spgRo%OuR)4aSt;&<lJ7@pk?A.k{|cI,.>EVl9@8auKZr`ac!:' );
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'byna_';
/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);
define('RT_WP_NGINX_HELPER_CACHE_PATH', '/var/cache/nginx/fastcgi_cache');
define('NGINX_HELPER_LOG', false);
define('FS_METHOD', 'direct');
/* Add any custom values between this line and the "stop editing" line. */
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';