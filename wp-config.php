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
define( 'DB_NAME', 'vionis' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         '@gp4G~ACONeo!74EXO9G;}.!SZ-Py Vt2u=oIfhjmwR|nRQO^&R<S)<EGr[F/*=~' );
define( 'SECURE_AUTH_KEY',  'P6{usU^V? ^++V>+r$}V-j9e9^CJ5Yz5ubVp`xnO=xyc4H@A}|RElp%d;MaP=M|O' );
define( 'LOGGED_IN_KEY',    'Ei6O8TdA0x/Yec}]D!pu~hZ:+C-]<_yq+*_uzJ&r6HE7q03Ya]{8 Wv!dls&|pP,' );
define( 'NONCE_KEY',        'ihLw{M_;?*R&d[iOO#%z&(^[P%*iGY&5gc@j&Md!9{WR5e+2u90eSlm4NG(s1f}$' );
define( 'AUTH_SALT',        ')7g>rS?)1.fu`U?[eOIe+tY*2bo5p3Na_8~q(E?Z$|S9@4oG-q^i8A&}bl[6R9?A' );
define( 'SECURE_AUTH_SALT', '`>dMuQENY-&;(7%U=oJB>Kd>TWIlFrDPfm~e:-YXFns{6+,y]s=t/wKx&Jc1}X?l' );
define( 'LOGGED_IN_SALT',   'Q>$TjNo2JK[g+05]RL2KHt.]6^ENv,fuLzGH*^);p)5}4I/,dr{ESUWG6B}hQVD;' );
define( 'NONCE_SALT',       'L%sy<~V8cSz/Z~}nK5Bk_PvhVEi7T![J|a7lWE_LHJ!GC:s#:P2{O7].v|)FD|W#' );

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
 /*
define( 'WP_HOME', 'https://vionistravel.maiatech.com.vn' );
define( 'WP_SITEURL', 'https://vionistravel.maiatech.com.vn' );
define( 'WP_MEMORY_LIMIT', '512M' );
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
  $_SERVER['HTTPS'] = 'ON';
*/
$table_prefix = 'wp_';

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

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
