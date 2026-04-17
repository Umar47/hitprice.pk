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
define( 'AUTH_KEY',          'L-IW|+=uw1[n=tIkY ;K9M*O^>D ciPQ}EN|b@2I(!,wZ=&@2F<~Z#E$o2/M~[53' );
define( 'SECURE_AUTH_KEY',   '-I/I<?&vxmYv7;hI][Cc.ilk`eJINZ42g7s_7eg ^!qS3Ns%B/ya:R%JR_E%94z1' );
define( 'LOGGED_IN_KEY',     'o%H[.BVVotxf~@6WCot###%7dS[F,*k2]F,KGG>pcG:nbCsG31s}kt]9nI-FkA?[' );
define( 'NONCE_KEY',         'HXs@Vu&l1pPn_|A{w+&tz:mT:|5tuJlMF|995No&3jo2JH1$@nEeRkaUj9|dKT!|' );
define( 'AUTH_SALT',         '6vSA6(rrBfEp1A+.e7iUW@z;<Pdv)maQFCEZX~=%]2tL,I]gI)VK*@oLKD1v^.<[' );
define( 'SECURE_AUTH_SALT',  'Qt--k6cR=Oz8U7>b |U&1R~j%tJB{>YiZ^):;/lE:y=v]H6oo-#4kYhz-^|:[MOm' );
define( 'LOGGED_IN_SALT',    '$vtT2+5?/*QCPWcQ%A@%dF`>E#N{obmLj#=t=Xs]H<F)tYq!fCXrkTbQ<wYf||w7' );
define( 'NONCE_SALT',        'yOHHWfhdf WHoMv%iWrL<7iE|LT699Qk}rA1/I 8*KGN+_u`z4_<~OKv.bHA?.0~' );
define( 'WP_CACHE_KEY_SALT', '|]rx|n2/`vTl|:2i|Q]qkxl|QNWpoVEb@ZcXAgu9$TmQQPzA1&TAS;ytg_A2S5g:' );


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
	define( 'WP_DEBUG', true );
}
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 1 );

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
