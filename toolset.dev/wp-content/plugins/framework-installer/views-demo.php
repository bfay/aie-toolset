<?php
/*
  Plugin Name: Framework Installer
  Plugin URI: http://wp-types.com/documentation/views-demos-downloader/
  Description: Download complete reference designs for Types and Views to your local test site.
  Author: OnTheGoSystems
  Author URI: http://wp-types.com
  Version: 1.6.0
 */
define('WPVDEMO_VERSION', '1.6.0');
define('WPVDEMO_ABSPATH', dirname(__FILE__));
define('WPVDEMO_WPCONTENTDIR',WP_CONTENT_DIR);
define('WPVDEMO_RELPATH', plugins_url() . '/' . basename(WPVDEMO_ABSPATH));

if (!defined('WPVDEMO_URL')) {
    define('WPVDEMO_URL', 'http://ref.wp-types.com');
}
if (!defined('WPVDEMO_DOWNLOAD_URL')) {
    define('WPVDEMO_DOWNLOAD_URL', 'http://ref.wp-types.com/_wpv_demo');
}
if (!defined('WPVDEMO_DEBUG')) {
    define('WPVDEMO_DEBUG', false);
}
if (!(get_option('wpv_import_is_done'))) {
  error_reporting(0);
}
//The Basic Hooks
add_action('after_setup_theme', 'wpvdemo_init_embedded_code', 9999); // Original priority is 999
add_action('plugins_loaded', 'wpvdemo_plugins_loaded_hook', 2);
add_action('init', 'wpvdemo_init_hook');
add_action('init','register_color_taxonomy_bootcommerce');
add_action('init','wpvdemo_refresh_rewrite_rules_on_firstload',50);
add_action('admin_menu', 'wpvdemo_admin_menu_hook');
add_action('wp_ajax_wpvdemo_download', 'wpvdemo_download');
add_action('wp_ajax_wpvdemo_post_count', 'wpvdemo_get_post_count');
add_filter('wp_get_nav_menu_items', 'wpvdemo_wp_get_nav_menu_items_filter', 10,3);
add_action( 'admin_head', 'viewsdemo_admin_render_js_settings' );
add_action('plugins_loaded', 'wpv_demo_views_init', 2);
add_action('plugins_loaded','wpv_demo_disable_admin_notices_demo');
register_activation_hook(__FILE__, 'wpvdemo_activation_hook');
add_filter('theme_mod_custom_logo', 'wpvdemo_fix_estates_logo', 10, 1);

if (!(is_multisite())) {
	add_filter('wpvdemo_blank_site_message','customize_message_WP_reset');
}

//Require files and functions
require_once WPVDEMO_ABSPATH . '/includes/messages.php';
require_once WPVDEMO_ABSPATH . '/view_demo_text.php';
require_once WPVDEMO_ABSPATH . '/class.wordpress_reset.php';
require_once WPVDEMO_ABSPATH . '/includes/main_functions/views_demo_functions.php';