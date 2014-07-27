<?php 
/*
  Plugin Name: WooCommerce Views
  Plugin URI: http://wp-types.com/documentation/views-inside/woocommerce-views/
  Description: Let's you display WooCommerce products in a table, a grid or with sliders. <a href="http://wp-types.com/documentation/views-inside/woocommerce-views/">Documentation</a>
  Author: ICanLocalize
  Author URI: http://www.wp-types.com/
  Version: 2.1
 */
/**
 * include plugin class
 */
if(defined('WOOCOMMERCE_VIEWS_PLUGIN_PATH')) return;

define('WOOCOMMERCE_VIEWS_PLUGIN_PATH', dirname(__FILE__));

if(defined('WOOCOMMERCE_VIEWS_PATH')) return;

define('WOOCOMMERCE_VIEWS_PATH', dirname(__FILE__) . '/Class_WooCommerce_Views.php');

if(!class_exists('Class_WooCommerce_Views'))
{
	require_once('Class_WooCommerce_Views.php');
}

/**
 *  instantiate new plugin object
 */
if(!isset($Class_WooCommerce_Views))
{

	$Class_WooCommerce_Views = new Class_WooCommerce_Views;
}

//Alias Functions for easy [wpv-if] implementation

function woo_product_on_sale() {

	global $Class_WooCommerce_Views; return $Class_WooCommerce_Views->woo_product_on_sale();
}
function woo_product_in_stock() {

	global $Class_WooCommerce_Views; return $Class_WooCommerce_Views->woo_product_in_stock();
}
//Reset custom fields updating when deactivated
register_deactivation_hook(__FILE__,array($Class_WooCommerce_Views,'wcviews_request_to_reset_field_option'));

//Shortcodes GUI
require WOOCOMMERCE_VIEWS_PLUGIN_PATH . '/inc/wcviews-shortcodes-gui.php';
?>