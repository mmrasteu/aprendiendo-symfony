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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'aprendiendo-wp' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
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
define( 'AUTH_KEY',         'o`]WEVSxvQI8jHmd&&S:4ksOmYQmVnAthpFrHVx.zVTj,Yq={lGXe3{J)N$EDOGL' );
define( 'SECURE_AUTH_KEY',  'bL;DyO=~}KL@<.MOaWW_n-c8g_ggc(l;wurLoSwhv?pxbCJ?MZ;dpl(m|0!2IKj2' );
define( 'LOGGED_IN_KEY',    'nwZH2_>]8:/qDpzy9(%>M_tv#r_tX358#@-m ;Ya,0FjGieaS.`kzQ2D`_e.rk2p' );
define( 'NONCE_KEY',        'L(wHoSAVl3_=X}Z4Zp#H1WH1(1JhG[F!M~U8ldB)&<2J]TPsEe bA:d*<H00F`H|' );
define( 'AUTH_SALT',        'Ky6L^UJH|EOq]E]jMbiO+AC@{>O*Z{fsh}%3/8}Lv!WU^#!A(F)e!LRGi%G7CP},' );
define( 'SECURE_AUTH_SALT', '.V[Rptx*cv[2! MXQ(/WUm~`L>0btfu$u-~){3J, l0<TOp7tBlAEv:#Hz!gHSg:' );
define( 'LOGGED_IN_SALT',   'S*f^6A9>=Rz>DNA{qjEbX%rz7~nUB;{lp* wAY1`gKB(0:h9(Cq5%?Itj6j%ff_{' );
define( 'NONCE_SALT',       '8sp9a_`;J@q8R@MIXM=L1261UyWm.On>N#:,92%L&S3s!gE;(fDOOb5O_K.em4%>' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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
