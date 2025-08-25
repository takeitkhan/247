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
define( 'DB_NAME', 'pet' );

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

define('WP_MEMORY_LIMIT', '256M');


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
define( 'AUTH_KEY',         '(3}98R?w%dPgWoT6)(t[b*-oh=rXriI&!OLXzILDU,{0b~v{K^4]HAjMsSEnezY@' );
define( 'SECURE_AUTH_KEY',  'Ek/%)X0n)NcHw=?n~L)I2ir=FhK2<-Gawm:)lgovp<n:)=)l}Y=47z+FCXY_{>*6' );
define( 'LOGGED_IN_KEY',    'o8P&DfG4a&XCw,N6$NC;?V~r+GK}oCRf$p=F;8CxLH0,nYA@#niox@p9}ehKLi4k' );
define( 'NONCE_KEY',        'os._5$Zvh[r 2 a@;, qWRo&Gc[$q+iAuri+L)#H$8hrSv2bI33BORSOv=TeV6ab' );
define( 'AUTH_SALT',        '8G s-Ah_DBHG;JERC!^N[I1A&5QtseVu9|8Aiv i}cUk&fF:F(![cMDtg^q@BpZr' );
define( 'SECURE_AUTH_SALT', 'c@UPmGnR*K$oG5uv!Q5IV5CZ/o0HAhdxg>Zk_:4xbwLtF1qO[Dy]S]2(43=ZeUam' );
define( 'LOGGED_IN_SALT',   '6!Yd6k4MPF+i7!Yjr4+~>i~u<wc=ITKa4^E;z]%P}}/)w.D1o~}hAGgf>C#}xD9W' );
define( 'NONCE_SALT',       'CEA?k&^U62r`AygxW9jm<3W`F}!S#fDF?0}Q6weyB+=T7mW#iA{wUZ9S.D(VUH;s' );

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
$table_prefix = 'pet_';

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
define( 'WP_DEBUG', true );
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true); // Set to false in production
@ini_set('display_errors', 0);

error_log('functions.php loaded');
/* Add any custom values between this line and the "stop editing" line. */

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
