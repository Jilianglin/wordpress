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
define( 'DB_NAME', 'jasonlin_wp274' );

/** MySQL database username */
define( 'DB_USER', 'jasonlin_wp274' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Y[65p66K[S' );

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
define( 'AUTH_KEY',         'ngjntuj9agd77kd9go0pgo44u0ecq2hefjmo4oc1ad3sdcbszmmtzhtwq2dvpywm' );
define( 'SECURE_AUTH_KEY',  'phpzegh27vbqm990dg4rbpudkdtqdrlq046peje2enceegfi6otykmdbkefwcqjh' );
define( 'LOGGED_IN_KEY',    'lqzhcgztypha1yaiaatc8gl2b0hkmgapsc10ditzys780oxu1makbebt2bbmhtkf' );
define( 'NONCE_KEY',        'pinkspm4vdyekogd4aeb5tzveexmayzaraekpeg7f8mtnp0izo8kk4hrryoyaxd8' );
define( 'AUTH_SALT',        'qqsyjlkkfmf8tqqloqslplaxi9289ov8dllln9s4aubpc0syekvhjuidfq4t8p2d' );
define( 'SECURE_AUTH_SALT', 'qrkfrxgbesfpdrip9pa0a6t6henxrlvldxsclrz3krdokt7knpvovzte6ybalvde' );
define( 'LOGGED_IN_SALT',   'qj1qsp5nlowdvjer3sabvdvrpa9cuwpwj6bncluqtr8ssbxtlsq3ctc4govzliuz' );
define( 'NONCE_SALT',       'kkremyuihvleqj4wy21dlq2duww51u3ufboodrstxffegbdva7qxk5p5k31ncewd' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp7h_';

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
