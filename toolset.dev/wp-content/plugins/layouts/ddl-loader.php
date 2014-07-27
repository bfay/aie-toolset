<?php
if( defined('WPDDL_VERSION') ) return;
define('WPDDL_VERSION', '0.9.2');

define( 'WPDDL_ABSPATH', dirname( __FILE__ ) );
define( 'WPDDL_ONTHEGO_RESOURCES', WPDDL_ABSPATH . '/onthego-resources/');
define( 'WPDDL_INC_ABSPATH', WPDDL_ABSPATH . '/inc' );
define( 'WPDDL_INC_RELPATH', WPDDL_RELPATH . '/inc' );
define( 'WPDDL_CLASSES_ABSPATH', WPDDL_ABSPATH . '/classes' );
define( 'WPDDL_CLASSES_RELPATH', WPDDL_RELPATH . '/classes' );
define( 'WPDDL_RES_ABSPATH', WPDDL_ABSPATH . '/resources' );
define( 'WPDDL_RES_RELPATH', WPDDL_RELPATH . '/resources' );
define( 'WPDDL_GUI_ABSPATH', WPDDL_ABSPATH . '/inc/gui/' );
define( 'WPDDL_GUI_RELPATH', WPDDL_RELPATH . '/inc/gui/' );

define( 'WPDDL_EMBEDDED_ABSPATH', WPDDL_ABSPATH  . '/embedded' );
define( 'WPDDL_COMMON_ABSPATH', WPDDL_EMBEDDED_ABSPATH  . '/common' );

define( 'WPDDL_EMBEDDED_REL', WPDDL_RELPATH  . '/embedded' );
define( 'WPDDL_COMMON_REL', WPDDL_EMBEDDED_REL  . '/common' );

define( 'WPDDL_TOOLSET_COMMON_RELPATH', WPDDL_RELPATH  . '/toolset-common' );

if( !defined('WPDDL_DEBUG') ) define('WPDDL_DEBUG', false);

//TODO: this is used for archives / loops it is better to use it only for this data. Should we rename it not to get confused..
define('WPDDL_GENERAL_OPTIONS', 'ddlayouts_options');
define('WPDDL_CSS_OPTIONS', 'layout_css_settings');
define('WPDDL_LAYOUTS_CSS', 'layout_css_styles');


define('DDL_ITEMS_PER_PAGE', 10 );

require_once WPDDL_ONTHEGO_RESOURCES . 'onthegosystems-branding-loader.php';
ont_set_on_the_go_systems_uri_and_start( WPDDL_RELPATH . '/onthego-resources/' );

require_once WPDDL_INC_ABSPATH . '/constants.php';
require_once WPDDL_INC_ABSPATH . '/help_links.php';

require_once WPDDL_ABSPATH . '/toolset-common/WPML/wpml-string-shortcode.php';

require_once WPDDL_CLASSES_ABSPATH . '/wpddl.layout.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.json2layout.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.layout-render.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.registered_cell.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.registered_layout_theme_section.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.editor.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.file-manager.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.cssmanager.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.optionsmanager.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.scripts.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.post-types-manager.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.individual-assignment-manager.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.cssframerwork.options.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.layouts-listing.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl.views-support.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/wpddl-common-messages-class.php';

require_once WPDDL_GUI_ABSPATH . '/dialogs/dialogs.php';
require_once WPDDL_GUI_ABSPATH . '/editor/editor.php';

require_once WPDDL_INC_ABSPATH . '/api/ddl-fields-api.php';

require_once WPDDL_INC_ABSPATH . '/api/ddl-theme-api.php';

require_once WPDDL_INC_ABSPATH . '/api/ddl-features-api.php';

include_once WPDDL_RES_ABSPATH. '/log_console.php';

add_action( 'init', 'init_layouts_plugin', 9 );

function init_layouts_plugin()
{
	global $wpddlayout;
	$wpddlayout = new WPDD_Layouts();
}

