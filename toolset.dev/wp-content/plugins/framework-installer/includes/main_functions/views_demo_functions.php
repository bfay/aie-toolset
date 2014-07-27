<?php
function wpv_demo_views_init() {
	if (!defined('WPV_VERSION')) {		
		require_once WPVDEMO_ABSPATH . '/embedded-views/views.php';
	}
}

/**
 * Activation hook.
 */
function wpvdemo_activation_hook() {
	add_option('wpvdemo_do_activation_redirect', true);
}

/**
 * Inits Types embedded code.
 */
function wpvdemo_init_embedded_code() {
	if (!defined('WPCF_EMBEDDED_ABSPATH')) {		
		require_once WPVDEMO_ABSPATH . '/embedded-types/types.php';
		//        wpcf_embedded_init();
	}
}

function wpvdemo_plugins_loaded_hook() {
	// Include embedded code.
	if (!defined('WPV_VERSION')) {		
		require_once WPVDEMO_ABSPATH . '/embedded-views/views.php';
	}
}

/**
 * Init hook function.
 */
function wpvdemo_init_hook() {
	global $wpvdemo;

	wpvdemo_plugin_localization();

	$wpvdemo = get_option('wpvdemo');
	if (!empty($wpvdemo['title'])
	&& !empty($wpvdemo['tutorial_title'])
	&& !empty($wpvdemo['tutorial_url'])
	&& !defined('WPVLIVE_VERSION')) {
		add_action('admin_notices', 'wpvdemo_demo_message');
	}
	$wpvdemo['requirements'] = wpvdemo_check_requirements();
	if (empty($wpvdemo['requirements']['themes_dir_writeable'])
	&& empty($wpvdemo['requirements']['media_dir_writeable'])) {
		add_action('admin_notices',
		'wpvdemo_requirements_dirs_writeable_error_message');
	} else {
		if (empty($wpvdemo['requirements']['themes_dir_writeable'])) {
			add_action('admin_notices',
			'wpvdemo_requirements_themes_writeable_error_message');
		}
		if (empty($wpvdemo['requirements']['media_dir_writeable'])) {
			add_action('admin_notices',
			'wpvdemo_requirements_media_writeable_error_message');
		}
	}
	if (defined('WPVDEMO_WPCONTENTDIR')) {		
		if (empty($wpvdemo['requirements']['wpcontent_dir_writeable'])) {
		add_action('admin_notices', 'wpvdemo_requirements_wpcontent_writeable_error_message');
		} 
	}

	if (empty($wpvdemo['requirements']['zip'])) {
		add_action('admin_notices', 'wpvdemo_requirements_zip_error_message');
	}
	//Add check if allow_url_open is enabled
	if (empty($wpvdemo['requirements']['enabled_native_PHP_remote_parsing_functions'])) {
		add_action('admin_notices', 'wpvdemo_disabled_native_PHP_remote_parsing_functions_message');
	}
	if (get_option('wpvdemo_do_activation_redirect', false)) {
		delete_option('wpvdemo_do_activation_redirect');
		wp_redirect(admin_url() . 'admin.php?page=wpvdemo');
		exit;
	}
}

/**
 * Admin menu hook.
 */
function wpvdemo_admin_menu_hook() {
	$views_demo_icon=plugins_url('images/discover_wp_icon.png', dirname(dirname(__FILE__)));
	$hook=add_menu_page('Manage Sites', 'Manage Sites', 'manage_options',
			'wpvdemo', 'wpvdemo_admin_menu_import',
			$views_demo_icon);
	 
	add_action('load-' . $hook, 'wpvdemo_admin_menu_import_hook');
}

/**
 * Admin menu page hook.
 */
function wpvdemo_admin_menu_import_hook() {
	$importing_text = '<div id="wpvdemo_step_1">' . __('1. Downloading and importing Types...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_2">' . __('2. Downloading and importing Posts and Images...',
			'wpvdemo') . ' <span class="wpvdemo-post-count" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_3">' . __('3. Downloading and importing Views...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_4">' . __('4. Downloading and importing CRED forms...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_5">' . __('5. Downloading and importing Types Access...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_6">' . __('6. Downloading and importing WPML settings...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_7">' . __('7. Downloading inline documentation...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_8">' . __('8. Downloading theme...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_9">' . __('9. Downloading and importing module manager...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';
	$importing_text .= '<div id="wpvdemo_step_10">' . __('10. Downloading and importing widgets...',
			'wpvdemo') . ' <span class="wpcf-ajax-loading-small" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="wpvdemo-green-check" style="display:none">&nbsp;&nbsp;&nbsp;&nbsp;</span></div>';

	wp_enqueue_style('wpvdemo', WPVDEMO_RELPATH . '/css/basic.css', array(),
	WPVDEMO_VERSION);
	wp_enqueue_script('wpvdemo', WPVDEMO_RELPATH . '/js/basic.js',
	array('jquery'), WPVDEMO_VERSION);
	viewdemo_admin_add_js_settings('wpvdemo_nonce',
	"'"
			. wp_create_nonce('wpvdemo') . "'");
			viewdemo_admin_add_js_settings('wpvdemo_download_step_one_txt',
			"'"
					. $importing_text . "'");
					viewdemo_admin_add_js_settings('wpvdemo_confirm_download_txt',
					"'"
							. esc_js(__('Your site has some sample content. The installer will remove that content and replace it will our own demo content.',
							'wpvdemo')) . "'");
					wpvdemo_check_if_blank_site();

					do_action('wpvdemo_admin_load');
}
//Special JS functions from embedded types
//START
function viewdemo_admin_add_js_settings( $id, $setting = '' ) {
	static $settings = array();
	$settings['wpcf_nonce_ajax_callback'] = '\'' . wp_create_nonce( 'execute' ) . '\'';
	$settings['wpcf_cookiedomain'] = '\'' . COOKIE_DOMAIN . '\'';
	$settings['wpcf_cookiepath'] = '\'' . COOKIEPATH . '\'';
	if ( $id == 'get' ) {
		$temp = $settings;
		$settings = array();
		return $temp;
	}
	$settings[$id] = $setting;
}

function viewsdemo_admin_render_js_settings() {
	$settings = viewdemo_admin_add_js_settings( 'get' );
	if ( empty( $settings ) ) {
		return '';
	}

	?>
    <script type="text/javascript">
        //<![CDATA[
    <?php
    foreach ( $settings as $id => $setting ) {
        if ( is_string( $setting ) ) {
            $setting = trim( $setting, '\'' );
            $setting = "'" . $setting . "'";
        } else {
            $setting = intval( $setting );
        }
        echo 'var ' . $id . ' = ' . $setting . ';' . "\r\n";
    }

    ?>
        //]]>
    </script>
    <?php
}
//END

/**
 * Gets settings from http://ref.wp-types.com/ or from local DB.
 * 
 * @return boolean 
 */
function wpvdemo_admin_get_sites_index($refresh_check = true) {

    $xml = get_option('wpvdemo-index-xml');
    $time = get_option('wpvdemo-refresh', 0);
    $config_file = defined('WPVDEMO_DEBUG') && WPVDEMO_DEBUG ? WPVDEMO_DOWNLOAD_URL . '/demos-index-debug.xml' : WPVDEMO_DOWNLOAD_URL . '/demos-index.xml';

    $wait = 43200;
    if(defined('WPVDEMO_DEBUG') && WPVDEMO_DEBUG) {
        $wait = 60;
    }
    
    	if (!$xml || ($refresh_check && time() - intval($time) > $wait)) {        
	
     	   //EMERSON: Use file_get_contents to fetch XML file.
     	   //Prevent issues like PHP Warning:  simplexml_load_string() Entity: line 24: parser error     	   
     	       	   
     	   $xml = wpv_remote_xml_get($config_file);        
      	  
      	  if ($xml) {    
      	  	         	
       	         update_option('wpvdemo-index-xml', $xml);
       	         update_option('wpvdemo-refresh', time());               
        	    
        	} else {
        		if (ini_get('allow_url_fopen')) {
        	    echo wpvdemo_error_message('connect', true);
        	    return false;
        		}
        	}
    	}    
    	$sites_index = simplexml_load_string($xml);
    	return apply_filters('wpvdemo_sites_index', $sites_index);	
    
}

/**
 * Gets settings for site.
 * 
 * @param type $site_id
 * @return boolean 
 */
function wpvdemo_get_site_settings($site_id) {
    $sites = wpvdemo_admin_get_sites_index();
    if (empty($sites->site)) {
        return false;
    }
    foreach ($sites->site as $check_site) {
        if (intval($check_site->ID) == intval($site_id)) {
            return $check_site;
        }
    }
    return false;
}

/**
 * Checks required downloaded settings for each site.
 * 
 * @param type $settings
 * @return boolean 
 */
function wpvdemo_admin_check_required_site_settings($settings,
        $show_error = true) {
    $required_settings = array('title', 'download_url', 'title', 'tagline', 'theme_url', 'tutorial_title', 'tutorial_url', 'fileupload_url', 'shortname');
    foreach ($required_settings as $setting) {
        if (empty($settings->$setting)) {

            if (WPVDEMO_DEBUG && $show_error) {
            	//echo sprintf(wpvdemo_error_message('site_configuration_missing',
            	//                true), $settings->title);            	
            }       
            return false;
        }
    }
    return true;
}

/**
 * Admin menu page render. 
 */
function wpvdemo_admin_menu_import() {
    $settings = wpvdemo_admin_get_sites_index();    
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');    
    echo "\r\n" . '<div class="wrap" style="width:700px;">
	<div id="' . 'icon-wpvdemo' . '" class="icon32"><br /></div>
    <h2>' . 'Framework Installer' . '</h2>' . "\r\n";
    do_action('wpvdemo_start_demo_page');
    if (empty($settings->site)) {
    	if (ini_get('allow_url_fopen')) {    		
    	//return this error only if it make sense
        echo wpvdemo_error_message('data', true);
    	}
    } else {
        foreach ($settings->site as $site) {
            if (!wpvdemo_admin_check_required_site_settings($site)) {
                continue;
            }
            if (apply_filters('wpvdemo_hide_site_download', false, $site)) {
                continue;
            }

            if (isset($site->title)) {
                $site->title = wpv_demo_get_tutorial_text($site->title);
            }
            if (isset($site->tagline)) {
                $site->tagline = wpv_demo_get_tutorial_text($site->tagline);
            }
            if (isset($site->tutorial_title)) {
                $site->tutorial_title = wpv_demo_get_tutorial_text($site->tutorial_title);
            }
            if (isset($site->short_description)) {
                $site->short_description = wpv_demo_get_tutorial_text($site->short_description);
            }

            ob_start();
            
            echo '<h1>' . $site->title . '</h1>';
            echo '<div class="wrap" style="width:500px;">';
            if (isset($site->image)) {
                $site->image = wpvdemo_convert_to_cloud_url($site->image, $site);
                echo '<img src="' . $site->image . '" title="' . $site->title
                . '" alt="' . $site->title
                . '" style="border: 1px solid #DBDBDB; float: left; margin: 0 15px 15px 0;" />';
            }
            echo isset($site->short_description) ? wpautop(stripslashes($site->short_description)) : '';
            // Plugins
            if (!empty($site->plugins->plugin)) {

            	$display_plugins=wpvdemo_format_display_plugins($site->plugins->plugin,true);     	

                if (!empty($display_plugins)) {

                    $required_plugin_failed = array();
                    $optional_plugins=array();
                    $mode_of_import='';
                    $display_plugins=wpvdemo_check_if_wpml_will_be_skipped($display_plugins,$site->download_url,false,false);
                    
                    if (isset($display_plugins['mode_of_import'])) {
                    	$mode_of_import=$display_plugins['mode_of_import'];
                    }
                                        
                    if (isset($display_plugins['optional'])) {
                    	$optional_plugins=$display_plugins['optional'];
                    }
                    
                    if (isset($display_plugins['required'])) {
                    	$display_plugins=$display_plugins['required'];
                    }
                    
                    if (!empty($display_plugins)) {
                    	$required_plugin_output=wpv_demo_functionalized_display($display_plugins,'required');
                    	echo $required_plugin_output['html'];
                    	$required_plugin_failed=$required_plugin_output['required_plugin_failed'];
                    }
                    
                    if (!empty($optional_plugins)) {
                    	$optional_plugin_output=wpv_demo_functionalized_display($optional_plugins,'optional');
                    	echo $optional_plugin_output['html'];                    	
                    }                  
                    
                    //Show incompatible plugin notice
                    if ((!(empty($incompatible_plugins_notice_array))) && (!(is_multisite()))) {
                    	//Some incompatible plugins there, show notice
                    	echo '<p><strong>'.__('Warning:','wpvdemo').'</strong>'.'&nbsp;&nbsp;'.__('Some incompatible required plugins are found in your plugins directory. It is recommended to use the tested version indicated in red font for compatibility. Please do this before clicking the download button.','wpvdemo').'</p><br />';
                    }

                    if (!empty($required_plugin_failed)) {
                        echo '<p>' . sprintf(wpvdemo_error_message('required_plugins_disabled_download',
                                        true),
                                '</p><ul><li>' . implode('</li><li>',
                                        $required_plugin_failed) . '</li></ul>');
                    }
                }
            }
            echo '</div>';
            echo '<div style="clear:both;"></div>';

            $site_info = ob_get_clean();            
            echo apply_filters('wpvdemo_site_info', $site_info, intval($site->ID));

            ob_start(); 
            
            //Clear $required_plugin_failed array for any sites with no required plugins
            if (empty($site->plugins->plugin)) { 
            	$required_plugin_failed=array();
            } 
            
            echo '<a ';
            echo!wpvdemo_download_requirements_failed() && empty($required_plugin_failed) ? 'href="' . $site->ID
                    . '" class="wpvdemo-download button-primary"' : 'href="#" class="button-primary" disabled="disabled"';          
            echo ' id="wpvdemo-download-button-' . $site->ID . '">';
            if (($mode_of_import=='nonwpml') && (!(is_multisite()))) {
            	echo __('Download', 'wpvdemo') . '</a>';
            } elseif (($mode_of_import=='wpml') && (!(is_multisite()))) {
            	echo __('Download multilingual version', 'wpvdemo') . '</a>';            	
            } else {
            	echo __('Download', 'wpvdemo') . '</a>';
            }

            $download_button = ob_get_clean();
            echo apply_filters('wpvdemo_download_button', $download_button,
                    intval($site->ID));

            echo '<div id="wpvdemo-download-response-' . $site->ID
            . '" class="wpvdemo-download-response" style="clear:both;"></div>';
            echo '<div class="wpvdemo-download-loading" style="clear:both;">&nbsp;</div>';
        }
    }
    echo "\r\n" . '</div>' . "\r\n";

    do_action('wpvdemo_end_demo_page');
}

function wpv_demo_functionalized_display($display_plugins,$mode) {
	$html='';
	$required_plugin_failed=array();
	if ($mode=='required') {
	$html .= '<div style="clear:both;"></div><strong>'
			. __('Required plugins:', 'wpvdemo') . '</strong>'
					. '<ul style="list-style-type: square; list-style-position:inside;">';
	} elseif(($mode=='optional')) {
		
		$html .= '<div style="clear:both;"></div><strong>'
				. __('Optional plugins (For downloading multilingual version of the site):', 'wpvdemo') . '</strong>'
						. '<ul style="list-style-type: square; list-style-position:inside;">';		
		
	}
	
	$incompatible_plugins_notice_array=array();
	foreach ($display_plugins as $plugin) {
		// Skip Views and Types
		if (in_array(basename((string) $plugin->file),
				array('wp-views.php', 'wpcf.php'))) {
			continue;
		}
		$html .= '<li><a href="' . $plugin->url . '" target="_blank" title="'
				. $plugin->title . '">' . $plugin->title . '</a>';
		$active = false;
		$found = false;
	
		$plugin_file_string_active=(string) $plugin->file;
		$plugin_name_string_active=(string) $plugin->title;
	
		$available_plugin_parameters_active=array('Plugin_file_active'   =>$plugin_file_string_active,
				'Plugin_name_active'   =>$plugin_name_string_active
		);
	
		$active = wpvdemo_is_active_plugin($available_plugin_parameters_active);
		if (!$active) {
			 
			//Plugins not active, typical standalone Framework Installer import setup
			$plugin_file_string=(string) $plugin->file;
			$plugin_name_string=(string) $plugin->title;
			$plugin_version_string=(string) $plugin->plugin_version_tested;
			 
			$available_plugin_parameters=array(
					'Plugin_file'=>$plugin_file_string,
					'Plugin_name'=>$plugin_name_string,
					'Plugin_version'=>$plugin_version_string
			);
	
			$found = wpvdemo_is_available_plugin($available_plugin_parameters);
			 
			if ($found) {
				//Plugin is found
				if ((isset($found['compatibility'])) && (!(is_multisite()))) {
					$compatibility_result=$found['compatibility'];
	
					if ($compatibility_result=='yes') {
						 
						//Plugin fully compatible
						$html .= '&nbsp;<span class="wpvdemo-green-check">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
						 
					} else {
						 
						//Plugin found but not the tested version
						if (isset($found['tested_version'])) {
							$tested_version=$found['tested_version'];
							$incompatible_plugins_notice_array[]=$tested_version;
							$html .= '&nbsp;<span class="wpvdemo-green-check">&nbsp;&nbsp;&nbsp;&nbsp;</span>'.'<font color="red">'.__('&nbsp;&nbsp;-Update to tested version: ','wpvdemo').'&nbsp;'.$tested_version.'</font>';
						} else {
							$html .= '&nbsp;<span class="wpvdemo-green-check">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
						}
					}
				} else {
					$html .= '&nbsp;<span class="wpvdemo-green-check">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
				}
			} else {
				if(strpos($plugin->url, 'wordpress.org/extend/plugins/')){
					$plugin_name_url = str_replace("http://wordpress.org/extend/plugins/", '', substr($plugin->url, 0, -1));
					$required_plugin_failed[] = '<a href="'
							. $plugin->url . '" target="_blank" title="'
									. $plugin->title . '">' . $plugin->title . '</a>&nbsp;&nbsp;&nbsp;
                                            <a href="'.site_url().'/wp-admin/update.php?action=install-plugin&plugin='.$plugin_name_url.'&_wpnonce='.wp_create_nonce('install-plugin_'.$plugin_name_url).'" tutle="">Quick Install</a> &nbsp;&nbsp;&nbsp;
                                            <a class="thickbox" href="'.site_url().'/wp-admin/plugin-install.php?tab=plugin-information&plugin='.$plugin_name_url.'&TB_iframe=true&width=600&height=550" tutle="">Details</a>';
	
				}else{
					$required_plugin_failed[] = '<a href="'
							. $plugin->url . '" target="_blank" title="'
									. $plugin->title . '">' . $plugin->title . '</a>';
				}
				$html .= '&nbsp;<span style="color:Red;">' . wpvdemo_error_message('required_plugin_warning') . '</span>';
			}
		} else {
			$html .= '&nbsp;<span class="wpvdemo-green-check">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
		}
		$html .= '</li>';
	}
	
	$html .= '</ul>';

	$output=array('html'=>$html,'required_plugin_failed'=>$required_plugin_failed);
	return $output;
}
/**
 * Triggers import action.
 */
function wpvdemo_download() {
    $wpvdemo = get_option('wpvdemo');
    if (!empty($wpvdemo['installed'])) {
        die();
    }
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'],
                    'wpvdemo') && isset($_POST['site_id'])
            && isset($_POST['step'])) {
        require_once WPVDEMO_ABSPATH . '/includes/import.php';
        wpvdemo_import($_POST['site_id'], $_POST['step']);
    }
    die();
}

/**
 * Checks if site is fresh install.
 * 
 * @return boolean 
 */
function wpvdemo_check_if_blank_site() {
    if (defined('WPVDEMO_DEBUG') && WPVDEMO_DEBUG) {
        return true;
    }
    static $check = null;
    if ($check !== null) {
        return $check;
    }
    $posts = get_posts('post_type=any');
    if (count($posts) > 2) {
        add_action('admin_notices', 'wpvdemo_check_if_blank_site_message');
        $check = false;
        return false;
    }
    $check = true;
    return true;
}

/**
 * Echoes importing posts progress. 
 */
function wpvdemo_get_post_count() {
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'],
                    'wpvdemo_nonce')) {
        return false;
    }
    $total = get_option('wpvdemo-post-total');
    $count = get_option('wpvdemo-post-count');

    if ($count != '0') {
        echo sprintf(__('%s of %s', 'wpvdemo'), $count, $total) . ' ';
    }

    die();
}

/**
 * Checks requirements.
 * 
 * @return type 
 */
function wpvdemo_check_requirements() {
    $check = array();    
    if (defined('WPVDEMO_WPCONTENTDIR')) {
    	$user_wpcontent=WPVDEMO_WPCONTENTDIR;
    	$check['wpcontent_dir_writeable'] = is_writeable($user_wpcontent);
    }   
    $check['themes_dir_writeable'] = is_writeable(dirname(get_stylesheet_directory()));
    $wp_upload_dir = wp_upload_dir();
    $check['media_dir_writeable'] = empty($wp_upload_dir['error']);
    $check['zip'] = class_exists('ZipArchive');
    if (ini_get('allow_url_fopen')) {
    	$check['enabled_native_PHP_remote_parsing_functions']=true;
    } 
    return $check;
}

/**
 * Converts URLs.
 * 
 * @param type $value
 * @param type $upload_dir_url
 * @return type 
 */
function wpvdemo_convert_url($url, $settings) {
    // Check uploaded files and other files
    if (strpos($url, (string) $settings->fileupload_url) !== false) {
        $upload_dir = wp_upload_dir();
        $url = str_replace((string) $settings->fileupload_url,
                $upload_dir['baseurl'], $url);
    } else if (strpos($url, (string) $settings->site_url) !== false) {
        $url = str_replace((string) $settings->site_url, get_site_url(), $url);
    }
    return $url;
}

/**
 * Fixes menu items.
 * 
 * @global type $wpvdemo
 * @global type $wpdb
 * @param type $items
 * @param type $menu
 * @param type $args
 * @return type 
 */
function wpvdemo_wp_get_nav_menu_items_filter($items, $menu, $args) {
    global $wpvdemo, $wpdb;
    if (isset($wpvdemo['ID'])) {
        $settings = wpvdemo_get_site_settings($wpvdemo['ID']);
        if (!empty($settings)) {
            foreach ($items as $key => $item) {
                if (strpos($item->url, (string) $settings->site_url) === 0) {
                    $post_name = basename($item->url);
                    $post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s",
                                    $post_name));
                    if ($post_id) {
                        $items[$key]->url = get_post_permalink($post_id);
                    }
                }
            }
        }
    }
    return $items;
}

/**
 * Activates plugins if necessary.
 * 
 * @param type $plugins 
 */

//IMPROVISED
function wpvdemo_activate_plugins($plugins,$activated_plugins_siteshortname) {
	$errors = false;

	if (!is_array($plugins)) {
		$plugins = (array) $plugins;
	}
	$plugins= apply_filters('wpvdemo_activate_plugins', $plugins,$activated_plugins_siteshortname);
 	$handle_file=array();
	$handle_name=array();
	 
	foreach ($plugins['plugin_file_stream'] as $plugin) {

		$handle_file[]=$plugin;
		 
	}
	 
	foreach ($plugins['plugin_name_stream'] as $plugin_name) {
		 
		$handle_name[]=$plugin_name;
		 
	}
	 
	$plugins_array_forchecking=array_combine($handle_name,$handle_file);	 
	 
	foreach ($plugins_array_forchecking as $key=>$value) {
		//$value= plugin file path
		//$key= Plugin name
		 
		$required_active = wpvdemo_is_active_plugin(array('Plugin_file_active' => $value,'Plugin_name_active' =>$key));		
		if (!$required_active) {
			$available = wpvdemo_is_available_plugin(array('Plugin_file' => $value,'Plugin_name' =>$key));
			
			if ($available) {
				
     			//Retain this activate_plugin format to avoid downloading errors in Views commerce ML in localhost
     			$success = activate_plugin($available['file']);
				
				if (is_wp_error($success)) {
					$errors[$available['file']] = $success->get_error_message();
				} else {
					do_action('wpvdemo_activate_plugin', $available['file']);
				}

				//use original reference site shop permalink
				if (basename($available['file'])=='woocommerce.php') {		

					global $woocommerce;					
					
					if (!empty($woocommerce)) {
						
						//Fixed issue on WooCommerce 2.1.0+ changes on dependency for /admin/woocommerce-admin-install.php file that is now removed on this version
						//Get WooCommerce version
						$woocommerce_version_import=$woocommerce->version;					

						if (version_compare($woocommerce_version_import, '2.1.0', '<')) {
								
							//WooCommerce version 2.1.0 and below
							require_once $woocommerce->plugin_path() . '/admin/woocommerce-admin-install.php';
							//Some recent versions of Woocommerce plugin did not have this function
							if (function_exists('activate_woocommerce')) {
								activate_woocommerce();
							}
							//Woocommerce 2.0 installation of pages, removing this warning
							if (function_exists('woocommerce_create_pages')) {
								$activated_plugins_siteshortname_array=array('cl','vc','bc');
								if (in_array($activated_plugins_siteshortname,$activated_plugins_siteshortname_array)) {
									//Don't anymore create WooCommerce pages
									//Use the imported WooCommerce pages
									//We no longer need to install pages
										
									delete_option( '_wc_needs_pages' );
									delete_transient( '_wc_activation_redirect' );
							
								} else {
							
									woocommerce_create_pages();
							
									// We no longer need to install pages
									delete_option( '_wc_needs_pages' );
									delete_transient( '_wc_activation_redirect' );
								}
							}							
								
						} else {								
							//WooCommerce version 2.1.0 and beyond
							//Woocommerce 2.0 installation of pages, removing this warning							
							$activated_plugins_siteshortname_array=array('cl','vc','bc');
							if (in_array($activated_plugins_siteshortname,$activated_plugins_siteshortname_array)) {
								//Don't anymore create WooCommerce pages
								//Use the imported WooCommerce pages
								//We no longer need to install pages
							
								delete_option( '_wc_needs_pages' );
								delete_transient( '_wc_activation_redirect' );
										
							}														
						}
					}
					
					update_option( 'woocommerce_prepend_shop_page_to_products', 'no' );	
                    
                    //Set early to prevent PHP notices on Woocommerce
                    $transient_woocommerce_data=array('woocommerce_cache_excluded_uris');
					set_transient( 'woocommerce_cache_excluded_uris', $transient_woocommerce_data );
				}			
				//END
			}
		}
		 
	}
    
	return $errors;
}

/**
 * Checks if plugin is active.
 * 
 * @param type $plugin
 * @return boolean 
 */
function wpvdemo_is_active_plugin($plugin) {
	$pluginfile_active=$plugin['Plugin_file_active'];
	$plugin_title_active=$plugin['Plugin_name_active'];	
	
    $active_plugins = get_option('active_plugins', array());  
    
    // Checks exact match
    foreach ($active_plugins as $plugin_file) {
        if ($plugin_file == $pluginfile_active) {
            return array('match' => 'exact', 'file' => $plugin_file);
            break;
        }
    }
    // Checks similar match
    reset($active_plugins);    
    foreach ($active_plugins as $plugin_file) {
        if (basename($plugin_file) == basename($pluginfile_active)) {
        	if (basename($plugin_file) <> 'plugin.php') {
            return array('match' => 'similar', 'file' => $plugin_file);
            break;
            } else {
            	$plugin_realpath=dirname(WPVDEMO_ABSPATH).'/'.$plugin_file;
            	$handle = fopen($plugin_realpath, "r");
            	$contents = fread($handle,filesize($plugin_realpath));
            	$pieces = explode("\n", $contents);
            	$key = array_find('Plugin Name', $pieces);
            	$value=trim($pieces[$key]);
            	$value_exploded=explode(":",$value);;
            	$plugin_name_extract=$value_exploded[1];
            	//This is the actual plugin name of plugin.php found in plugins directory
            	$plugin_name_extract=trim($plugin_name_extract);
            	//This is the plugin name of exported file with plugin.php file name
            	$exported_plugin_title=trim($plugin_title_active);
            		if ($plugin_name_extract==$exported_plugin_title) {
            			return array('match' => 'similar', 'file' => $plugin_file);
            			break;
            		}
            	fclose($handle);            	
            }
        }         
    }
    return false;
}

/**
 * Checks if plugin is available.
 * 
 * @param type $plugin
 * @return boolean 
 */
function wpvdemo_is_available_plugin($plugin) {
	$pluginfile=$plugin['Plugin_file'];
	$plugin_title=$plugin['Plugin_name'];	
	
	if (isset($plugin['Plugin_version'])) {
		$plugin_version=$plugin['Plugin_version'];
	}
	
    $all_plugins = get_plugins();

    // Check exact match-changes OK
    foreach ($all_plugins as $plugin_file => $plugin_data) {
        if ($plugin_file == $pluginfile) {
        	if (!(isset($plugin_version))) {
        		
            	return array('match' => 'exact', 'file' => $plugin_file);
            	
        	} else {
        		
        		//Plugin version is set, check if compatible
        		//Get version of the exact match        
        		if (isset($plugin_data['Version'])) {		
        			$exact_match_plugin_version=$plugin_data['Version'];
        			if ($exact_match_plugin_version==$plugin_version) {
        				$compatibility='yes';
        			} else {
        				$compatibility='no';
        			}
        			return array('match' => 'exact', 'file' => $plugin_file,'compatibility'=>$compatibility,'tested_version'=>$plugin_version,'installed_version'=>$exact_match_plugin_version);
        		}
        	}
            break;
        }
    }
    // Check similar match
    reset($all_plugins);
    
    foreach ($all_plugins as $plugin_file => $plugin_data) {    	
        //Changes OK here
    	if (basename($plugin_file) == basename($pluginfile)) {
    		if (basename($plugin_file) <> 'plugin.php') {

            	if (!(isset($plugin_version))) {
            		
            		//Plugin versions not set
            		return array('match' => 'similar', 'file' => $plugin_file);    	
            	} else {
            		
            		//Plugin versions are set, check for compatibility
            		if (isset($plugin_data['Version'])) {
            			$similar_match_plugin_version=$plugin_data['Version'];
            			
            			if ($similar_match_plugin_version==$plugin_version) {
            				$compatibility='yes';
            			} else {
            				$compatibility='no';
            			}      
            			return array('match' => 'similar', 'file' => $plugin_file,'compatibility'=>$compatibility,'tested_version'=>$plugin_version,'installed_version'=>$similar_match_plugin_version);
            		}            		
            	}
            	
            	break;
    		} else {
            $plugin_realpath=dirname(WPVDEMO_ABSPATH).'/'.$plugin_file;
            $handle = fopen($plugin_realpath, "r");
            $contents = fread($handle,filesize($plugin_realpath));
            $pieces = explode("\n", $contents);
            $key = array_find('Plugin Name', $pieces);
            $value=trim($pieces[$key]);
            $value_exploded=explode(":",$value);;
            $plugin_name_extract=$value_exploded[1];
            //This is the actual plugin name of plugin.php found in plugins directory
            $plugin_name_extract=trim($plugin_name_extract);
            //This is the plugin name of exported file with plugin.php file name
            $exported_plugin_title=trim($plugin_title);
            if ($plugin_name_extract==$exported_plugin_title) {
            	
            	if (!(isset($plugin_version))) {
            	   //Plugin version is not set
            		return array('match' => 'similar', 'file' => $plugin_file);
            	} else {
            		if (isset($plugin_data['Version'])) {
            			$another_similar_match_plugin_version=$plugin_data['Version'];
            			
            			if ($another_similar_match_plugin_version==$plugin_version) {
            				$compatibility='yes';
            			} else {
            				$compatibility='no';
            			}            			
            			return array('match' => 'similar', 'file' => $plugin_file,'compatibility'=>$compatibility,'tested_version'=>$plugin_version,'installed_version'=>$another_similar_match_plugin_version);
            		}           		
            	}
            	
            	break;            	
            }             
            fclose($handle);
    		}
        }
    
    }
    return false;
}
function array_find($needle, $haystack, $search_keys = false) {
	if(!is_array($haystack)) return false;
	foreach($haystack as $key=>$value) {
		$what = ($search_keys) ? $key : $value;
		if(strpos($what, $needle)!==false) return $key;
	}
	return false;
}

/**
 * Checks if plugin is allowed.
 * 
 * @param type $plugin
 * @return type 
 */
function wpvdemo_is_allowed_plugin($plugin) {
    return in_array(basename($plugin),
                    array('wp-views.php', 'wpcf.php', 'wordpress-importer.php', 'w3-total-cache.php', 'views-demo.php')) ? false : true;
}

/**
 * Converts to cloud URL.
 * 
 * @param type $url
 * @param type $settings
 * @return type 
 */
function wpvdemo_convert_to_cloud_url($url, $settings) {
    $real_url = str_replace($settings->fileupload_url,
            $settings->wp_upload_dir_url, $url);
    $url = str_replace($settings->site_url . '/', $settings->cloud_url . '/',
            $real_url);
    return $url;
}

/**
 * Checks if download requirements failed.
 * 
 * @global type $wpvdemo
 * @return boolean 
 */
function wpvdemo_download_requirements_failed() {
    global $wpvdemo;
    if (empty($wpvdemo['requirements']['themes_dir_writeable'])
            || empty($wpvdemo['requirements']['media_dir_writeable'])) {
        return true;
    }
    if (empty($wpvdemo['requirements']['wpcontent_dir_writeable'])) {
    	return true;
    } 
    if (!wpvdemo_check_if_blank_site()) {
        return true;
    }
    if (empty($wpvdemo['requirements']['zip'])) {
        return true;
    }
    return false;
}

// Localization
function wpvdemo_plugin_localization() {
    $locale = get_locale();
    load_textdomain('wpvdemo',
            WPVDEMO_ABSPATH . '/locale/views-demo-' . $locale . '.mo');
}

/**
 * Converts Types images URLs.
 * 
 * @global type $wpdb
 * @param type $settings 
 */
function wpvdemo_convert_types_images_url($settings) {
    global $wpdb;
    $fields = wpcf_admin_fields_get_fields();
    if (!empty($fields)) {
        foreach ($fields as $field) {
            if ($field['type'] == 'image' || $field['type'] == 'file') {
                $results = $wpdb->get_results($wpdb->prepare("SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE meta_key=%s",
                                wpcf_types_get_meta_prefix($field) . $field['slug']));
                if (!empty($results)) {
                    foreach ($results as $result) {
                        $new_url = apply_filters('types_images_convert_url',
                                wpvdemo_convert_url($result->meta_value,
                                        $settings), $result->meta_value);
                        $wpdb->update($wpdb->postmeta,
                                array('meta_value' => $new_url,
                                ), array('meta_id' => $result->meta_id),
                                array('%s'), array('%d')
                        );
                    }
                }
            }
        }
    }
}

function wpvdemo_fix_estates_logo($logo) {

    if (function_exists('get_blog_details')) {
    
        global $blog_id;
    
        $site_data = get_blog_details($blog_id);
        if ($site_data->blogname == "Real Estate" && $logo == $site_data->siteurl . '/files/2012/06/logo.png') {
            $logo = 'http://ref.wp-types.com/estates/files/2012/06/logo.png';
        }
    }
    
    return $logo;
}

//EMERSON: Disable some admin messages hooks
function wpv_demo_disable_admin_notices_demo() {
	global $sitepress;
	
	//Disable WPML admin notice
	remove_action('admin_notices', array($sitepress, 'help_admin_notice'));
	
	//Disable WooCommerce Views admin notice
	$site_name_loaded=get_bloginfo('name');
	
	if ($site_name_loaded=='Toolset Classifieds') {
	remove_action('admin_notices', 'wcviews_help_admin_notice');
	}
}

function customize_message_WP_reset($message) {

	$wp_reset_url=admin_url().'admin.php?page=wpvdemo-reset';
	$message = __("To be on the safe side, content import only works on fresh sites. We really don't want to accidentally delete content on live sites. To use this content importer again, please ","wpvdemo")."<a href='".$wp_reset_url."'>".__('reset this WordPress website database.','wpvdemo')."</a>";

	return $message;

}

/*EMERSON: Framework Installer remote XML parser*/
/*Use file_get_contents by default, or cURL if not available*/

function wpv_remote_xml_get($file) {

	$file_headers_wpvdemo = @get_headers($file);

	//Check if remote XML file exist, if yes then import
	if(strpos($file_headers_wpvdemo[0],'200 OK')) {
        
		    //Socket timeout for very slow connections
		    $context=stream_context_create(array('http'=>
			array(
				'timeout' => 1200 
			)
			));

			$xml_import_data=@file_get_contents($file,false,$context);

		if 	($xml_import_data) {

			return $xml_import_data;
				
		} else {
				
			return FALSE;
		}

	} else {

		return FALSE;

	}

}

/* WooCommerce Taxonomy*/
function register_color_taxonomy_bootcommerce() {
	$label='Color';
	$show_in_nav_menus=false;
	$hierarchical = true;
	
	$args= array(
			'hierarchical' 				=> 'true',
			'update_count_callback' 	=> '_update_post_term_count',
			'labels' => array(
					'name' 						=> $label,
					'singular_name' 			=> $label,
					'search_items' 				=> __( 'Search', 'woocommerce') . ' ' . $label,
					'all_items' 				=> __( 'All', 'woocommerce') . ' ' . $label,
					'parent_item' 				=> __( 'Parent', 'woocommerce') . ' ' . $label,
					'parent_item_colon' 		=> __( 'Parent', 'woocommerce') . ' ' . $label . ':',
					'edit_item' 				=> __( 'Edit', 'woocommerce') . ' ' . $label,
					'update_item' 				=> __( 'Update', 'woocommerce') . ' ' . $label,
					'add_new_item' 				=> __( 'Add New', 'woocommerce') . ' ' . $label,
					'new_item_name' 			=> __( 'New', 'woocommerce') . ' ' . $label
			),
			'show_ui' 					=> false,
			'query_var' 				=> true,
			'capabilities'			=> array(
					'manage_terms' 		=> 'manage_product_terms',
					'edit_terms' 		=> 'edit_product_terms',
					'delete_terms' 		=> 'delete_product_terms',
					'assign_terms' 		=> 'assign_product_terms',
			),
			'show_in_nav_menus' 		=> $show_in_nav_menus,
			'rewrite' 					=> array( 'slug' => 'color', 'with_front' => false, 'hierarchical' => $hierarchical ),
				);
	
	register_taxonomy( 'pa_color', array('product'), $args );
}

/* EMERSON: Check if WPML import will be skipped for importing multilingual sites but users does not have WPML plugins
 */

function wpvdemo_check_if_wpml_will_be_skipped($display_plugins=array(),$download_url='',$import=false,$silent_call=false) {
   
    //Download url
    $download_url=(string)$download_url . '/posts.xml';
    
    $parsed_url=parse_url($download_url);
    $get_path=$parsed_url['path'];    
    
    //Check if site has WPML implementation
    $has_wpml_implementation=wpvdemo_has_wpml_implementation($download_url,$import);
    
    //Prepare $display_plugins
    if (!(is_array($display_plugins))) {

		$display_plugins=$display_plugins->plugin;
	
    }
    
    if ($has_wpml_implementation) {
    	//Define the WPML plugins array
    	if ($get_path=='/_wpv_demo/bootstrap-estate/posts.xml') {
				$the_wpml_plugins=array(
					'WPML Multilingual CMS',
					'WPML Translation Management',					
					'WPML Media',
					'WPML String Translation'
				);
        } else {
			$the_wpml_plugins=array(
							'WPML Multilingual CMS',							
							'WPML Translation Management',							
							'WooCommerce Multilingual',
							'WPML Media',
							'WPML String Translation'
							);
        }
    	//Let's check if the user has all WPML plugins available   
    	$wpml_plugins_found=array();
     
		foreach ($display_plugins as $plugin) {

			$plugin_file_string=(string) $plugin->file;
			$plugin_name_string=(string) $plugin->title;
		
			if (in_array($plugin_name_string,$the_wpml_plugins)) {
          
       	   //Its a WPML plugin, check if user got this one.
			  $available_plugin_parameters=array('Plugin_file'=>$plugin_file_string,'Plugin_name'=>$plugin_name_string);
			  $found = wpvdemo_is_available_plugin($available_plugin_parameters);
		  
		 	 if ((is_array($found)) && (!(empty($found)))) {

          		$wpml_plugins_found[]=$plugin_name_string;

          	}
			}		
    	}
    
   	 	$check_result=wpvdemo_wpml_plugins_all_available($wpml_plugins_found,$the_wpml_plugins);

   	 	if ((isset($check_result['status'])) && ($check_result['status']=='all_true') && (isset($check_result['merge']))) {

            //All plugins there including WPML
             if (!($silent_call)) {

                $display_plugins_new=wpvdemo_filter_wpml_plugins_from_list($display_plugins,$the_wpml_plugins);
                $the_merge=$check_result['merge'];
                $check_result=wpvdemo_convert_to_object_optional_plugins($the_merge,$display_plugins);
                $display_plugins_final=array('required'=>$display_plugins_new,'optional'=>$check_result,'mode_of_import'=>'wpml','activate'=>$display_plugins);
             	
                return $display_plugins_final;
             	
             } else {
                 
                //Return FALSE if there is WPML implementation
                return FALSE;
             }
        
		} elseif ((isset($check_result['status'])) && ($check_result['status']=='not_all')&& (isset($check_result['merge']))) {

            //User does not have all of it           
            $display_plugins_new=wpvdemo_filter_wpml_plugins_from_list($display_plugins,$the_wpml_plugins);   
           
            //Check if you want to filter posts.xml with non-WPML implementation            
            if (!($silent_call)) {
				
                //Append optional WPML components missing
                //Bring to object optional plugins
                $the_merge=$check_result['merge'];
                $check_result=wpvdemo_convert_to_object_optional_plugins($the_merge,$display_plugins);
                $display_plugins_final=array('required'=>$display_plugins_new,'optional'=>$check_result,'mode_of_import'=>'nonwpml','activate'=>$display_plugins_new);

            	return $display_plugins_final;
            	
            } else {
              
              //Return TRUE if importer will need to skip WPML
              return TRUE;

            }
        }
    } else {
            //site does not have WPML implementation, return usual plugin list
			if (!($silent_call)) {
            	return $display_plugins;
            } else {
                //Return TRUE no WPML
                return TRUE;
            }
    }   
}

function wpvdemo_convert_to_object_optional_plugins($check_result,$display_plugins) {
   
   foreach ($display_plugins as $k=>$plugin_elements) {
       $plugin_title=(string)$plugin_elements->title;
       if (!(in_array($plugin_title,$check_result))) {
		 //Not found, unset
		 unset($display_plugins[$k]);		 
       }
   }
   return $display_plugins;
}
function wpvdemo_filter_wpml_plugins_from_list($display_plugins,$the_wpml_plugins) {

   foreach ($display_plugins as $k=>$plugin_name) {

      $plugin_name_string=(string) $plugin_name->title;      
      if (in_array($plugin_name_string,$the_wpml_plugins)) {

		unset($display_plugins[$k]);
      }
   }
   
   return $display_plugins;
}

function wpvdemo_wpml_plugins_all_available($array1, $array2) {

	sort($array1);
	sort($array2);
	$the_merge=array_unique(array_merge($array2,$array1));
	
	if ($array1==$array2) {
       /*All plugins found*/	
		$result=array('status'=>'all_true','merge'=>$the_merge);
		return $result;
    } else {
       //Not all of them
       $result=array('status'=>'not_all','merge'=>$the_merge);
       return $result;
    }
	
}

function wpvdemo_has_wpml_implementation($file,$wpml_plugin_activation_check=false) {

	$parsed_url=parse_url($file);
	$get_path=$parsed_url['path'];

	//Define sites with WPML implementation
	$has_wpml_array= array('/_wpv_demo/bootcommerce/posts.xml','/_wpv_demo/classifieds/posts.xml','/_wpv_demo/bootstrap-estate/posts.xml');
	
	if (in_array($get_path,$has_wpml_array)) {

		//Site has WPML implementation
		$state=TRUE;
			
	} else {
			
		$state=FALSE;
			
	}
	
	if (!($wpml_plugin_activation_check)) {

        return $state;
    } else {

        if ((defined('ICL_SITEPRESS_VERSION')) && ($state))  { 
            
			return TRUE;

        } else {

            return FALSE;
            
        }    
    }
}

function wpvdemo_format_display_plugins($data,$check_allowed=true) {

	$display_plugins = array();
	foreach ($data as $plugin) {
        if ($check_allowed) {
			// Skip Views and Types
			if (!wpvdemo_is_allowed_plugin((string) $plugin->file)) {
				continue;
			}
		}
		$display_plugins[] = $plugin;
	}
	
	return $display_plugins;
}

function wpvdemo_refresh_rewrite_rules_on_firstload() {

	//Flushing rewrite rules once immediately after site first load after import
	$flush_rewrite_after_import=get_option('classifieds_flush_rewrite_after_import');
	$wcsites_flush_rewrite_after_import=get_option('wcsites_flush_rewrite_after_import');
	$import_done= get_option( 'wpv_import_is_done');

	$site_url=get_site_url();

	if (!($flush_rewrite_after_import)) {
	//Except classifieds

	    global $woocommerce;
	    
		//Run if import is done, flushing is still not performed and is using WooCommerce
		if (($import_done=='yes') && (!($wcsites_flush_rewrite_after_import)) && (is_object($woocommerce))) {
			//Not yet flushed
			global $wp_rewrite;
			$wp_rewrite->flush_rules(false);
			//Update option
			$success_updating=update_option('wcsites_flush_rewrite_after_import',$site_url);
		}
	}
}