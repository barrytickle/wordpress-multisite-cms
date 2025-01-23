<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'F%F-Ct)#?LV9y?Po:Aw%^BW8V>q>/xn9%O8se*]f+3_VLrmI}C{j(Wc:e3ETVQv!' );
define( 'SECURE_AUTH_KEY',   '=`?^FebkCPqSh4W$I&KlXaB;u,+OMhp?.4N![-4>sym23|gYl3r;Y.bvS@#YDMd,' );
define( 'LOGGED_IN_KEY',     '+<vZfm*>w_zs{4%G|t]`mP#,s1??d:{wxRG5K^Ug{(=U%WnwSR)2D X:$ofPe:#/' );
define( 'NONCE_KEY',         'oEtj[CDMJ FM&b1e]L#RThjd}=Q;.-^ptoQPI53q.KX~S8<Dt(o`=xL&Z;aCL65s' );
define( 'AUTH_SALT',         '&e27(RFaVc)I7gl)cF->?myAd)M=t|~(b}SQ)_57(4}fGIva){M87v;ZZyOm?v?V' );
define( 'SECURE_AUTH_SALT',  'T+tZ55=a_5by+Y1W]BAy~i+MlTsgO$:z0U.XB6L(Mu$nGBKHY?WMW(]!5OFGY-(O' );
define( 'LOGGED_IN_SALT',    '<^5<+{miIvO=3G$Y1fVa__rbc7Ef`b;`/eQ1DSCxNTb}Nyn/%=4%KEpvWNwy6gS7' );
define( 'NONCE_SALT',        '^)~g?6{MNe&>b>nwz4~1i =l2&wohDO7(KR}=At{Ehb6W?`HNExuTGiY+pO[3~|A' );
define( 'WP_CACHE_KEY_SALT', '(1/y>ir(?jw]pzep*;/wMDSoEvfGRx~_);a[Gz$li`!N%Dqd?7G3Cw10OV/NQ,Z*' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define('WP_DEFAULT_THEME', 'barrytickle-main');

define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/';
define( 'DOMAIN_CURRENT_SITE', 'admin.local' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
