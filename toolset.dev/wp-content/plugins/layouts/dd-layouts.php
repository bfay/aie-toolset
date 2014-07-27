<?php
/*
Plugin Name: Layouts
Plugin URI: http://wp-types.com/
Description:
Author: ICanLocalize
Author URI: http://wpml.org
Version: 0.9.2
*/

define( 'WPDDL_RELPATH', plugins_url() . '/' . basename( dirname( __FILE__ ) ) );

if (!defined ('WPDDL_IN_THEME_MODE')) { // This check is only needed when the plugin is being activated while the bootstrap theme is in use.
	require_once dirname(__FILE__) . '/ddl-loader.php';
}
