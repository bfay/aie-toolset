<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'toolsetDB2sajs');

/** MySQL database username */
define('DB_USER', 'toolsetDB2sajs');

/** MySQL database password */
define('DB_PASSWORD', 'aLfQ1QccRF');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'AqTEaH;+hO9#taK1_tP6#xiP9]xiSD;+lkVC:@oVG0!oZJ4|dO9[whS8[whRC:-@o');
define('SECURE_AUTH_KEY',  'G!sgVGJ7}^vfUI7{^rfUI7{$rfF4>zncRF4>zncQF0,zncQueTH6]*ueTH6]*qeTH');
define('LOGGED_IN_KEY',    'vQB0,vjUJ7}^rfUI7{2<ymbPA;.ymbLA;.xmaL{^ujUI7{^ufTI6{*ufTIL9]~thW');
define('NONCE_KEY',        'jI3WL5]~teSH5]~pdSG5[~pPD2#+maPD2#+laPD2#xlodRG0|-odRC0|zoZNC0|lZ');
define('AUTH_SALT',        '>gN|wdN4|wcN8[vgRrXI3.qbM3.ubM6<gQB}yjUB{$jUE{$+iTD;+mWD;+lWD;qbI');
define('SECURE_AUTH_SALT', 'Z1~oVG1@oVGfM7>yfQB<yjUA<kUB}@nUF0^rYJ3^*mXH2.qaL2.teL6yfQA{yjTE{');
define('LOGGED_IN_SALT',   'yYI7{^uf.xmWL9;_xiWL9;_thWL9*qeTH6{*ueTH6]*qeTH6]:~shVK8:~shVK8:@');
define('NONCE_SALT',       'A*qeTH2#:_thWK9:~thWK9:~shVK]+peSH5]*peSH5]+peSH58:@scRG4[zocRF4>');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);define('FS_METHOD', 'direct');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
