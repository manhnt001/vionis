<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

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
define( 'AUTH_KEY',         'P)VjoVXh[&Hmm{b<!]35<.op ({2w7Q/9U5w0>D|>H1A,0>9wXP$Eq?B*`3::I&k' );
define( 'SECURE_AUTH_KEY',  'RP@A [VUW }|zFfOZ=2GG;$ 8<g9 4il+gUUKNEF_w?%>$pa!c8K>@$%oSfs^RGr' );
define( 'LOGGED_IN_KEY',    'woFG)adSL>57Jq8~RN!kuMV?x8iCDzee(] >_hNL.:Xkk{Z0SA(;%L %vm!EjKN7' );
define( 'NONCE_KEY',        'X0xphnVAMv*Ms&{RF$+Azh@pu5QC~kjqkY!T3)q`a=V v1=L,g>t|T@Ukd25T[U|' );
define( 'AUTH_SALT',        'qtY]S<C<RN[&tY!]e@UrUcMIYvBhLtH+,B22<]1b;D0QjV(QsKjwVeH;iXtcp Hw' );
define( 'SECURE_AUTH_SALT', ';N91Xi=Lw2)`g9~K!KmI8w!+$4$9fgOjDRJNprsGy:K()C*Zn[NynqN |I@S*B#B' );
define( 'LOGGED_IN_SALT',   'AMEjf/%(#7](Cr5lChZP^^ZO!z)).@sbJHU09sq>gvh(m*hi9}9en7F[C4%C8${z' );
define( 'NONCE_SALT',       'n@SqdOg64f$v[t!L#Xl-CIKrRA4S<SX1w1+eopa4x6/[u#8rf@]p#as%2+ky3mL/' );
define('CONCATENATE_SCRIPTS', false);

/*
define( 'WP_HOME', 'https://vionistravel.com' );
define( 'WP_SITEURL', 'https://vionistravel.com' );
define( 'WP_MEMORY_LIMIT', '512M' );
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
  $_SERVER['HTTPS'] = 'ON';
*/
/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
define('WP_DEBUG', true);

/* Add any custom values between this line and the "stop editing" line. */

define( 'RT_WP_NGINX_HELPER_CACHE_PATH', '/dev/shm/nginx-cache/wp' );



ini_set('display_errors', 'off');
define('WP_DEBUG_DISPLAY', false);
ini_set('log_errors', 'off');
define('WP_DEBUG_LOG', false);
define('SAVEQUERIES', true);
define('SCRIPT_DEBUG', false);
define('WP_AUTO_UPDATE_CORE', true);
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';


