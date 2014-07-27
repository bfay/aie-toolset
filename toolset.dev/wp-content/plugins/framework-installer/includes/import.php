<?php
if (defined('WPVDEMO_DEBUG')) {	
	$wpvdemo_debug_mode=WPVDEMO_DEBUG;
	if (!($wpvdemo_debug_mode)) {		
		error_reporting(0);
	}	
}
/*
 * Import functions.
 */

$wpvdemo_import = null;

/**
 * Imports site.
 * 
 * @global type $wpvdemo_import
 * @global type $wpdb
 * @param type $site_id
 * @param type $step 
 */
function wpvdemo_import($site_id, $step) {
	
    if (!wpvdemo_is_safe_mode()) {
        set_time_limit(0);
    }

    if ($step == 1 && !wpvdemo_check_if_blank_site()) {
        wpvdemo_check_if_blank_site_message();
        die();
    }

    $step = intval($step);
    $sites = wpvdemo_admin_get_sites_index(false);
    if (empty($sites->site)) {
        echo wpvdemo_error_message('data', true);
        die();
    }
    $settings = false;
    foreach ($sites->site as $check_site) {
        if ($check_site->ID == $site_id) {
            $settings = $check_site;
        }
    }
    if (empty($settings) || !wpvdemo_admin_check_required_site_settings($settings)) {
        echo wpvdemo_error_message('data', true);
        die();
    }

    $import_settings = (object) array(
                'fetch_attachments' => true,
                'download_theme' => true,
                'activate_plugins' => true,
    );
    $import_settings = apply_filters('wpvdemo_import_settings', $import_settings);

    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/import-export.php';
    require_once WPV_PATH_EMBEDDED . '/inc/wpv-import-export-embedded.php';

    do_action('wpvdemo_import_before_step_' . $step, $settings);
    
    //WP 3.5.2 &WP3.6 compatibility on testing reference sites locally
    if (defined('WPVDEMO_LOCALHOST_MODE')) {
    		
    	add_filter( 'http_request_args', 'wpdemo_localhost_reference_site' );
    		
    }
        
    //Add hook for refresh CRED Forms after import
    add_action('wpv_demo_refresh_cred_forms','wpv_refresh_cred_form_after_import',10,1);    
    
    //Add hook for auto-activating Types and Views plugin for sites with modules import
    add_action('wpv_demo_activate_types_views_after_import','wpvdemo_activate_types_views_modules_import',11,1);
    
    switch ($step) {
        case 1:     
        	   	
            update_option('wpvdemo-post-count', 0);
            update_option('wpvdemo-post-total', 0);

            // Delete default posts
            wp_delete_post(1, true);
            wp_delete_post(2, true);
            // Import Types and initialize post types!
            $success = wpvdemo_import_types($settings->download_url);            
            if (!$success) {
                echo wpvdemo_error_message('importing_types', true);
                die();
            }
            
            // Activate plugins
            ob_start();  
            $original_plugin_lists=wpvdemo_format_display_plugins($settings->plugins->plugin,false);
            $the_final_plugins=wpvdemo_check_if_wpml_will_be_skipped($original_plugin_lists,$settings->download_url,false,false);        
            $the_final_plugins=$the_final_plugins['activate'];
            if (!empty($the_final_plugins)) {
                $plugins = array();
                foreach ($the_final_plugins as $plugin) {
                    // Skip Views and Types
                    if (!wpvdemo_is_allowed_plugin((string) $plugin->file)) {
                        continue;
                    }
                  
                    $plugins['plugin_file_stream'][]=(string) $plugin->file;
                    $plugins['plugin_name_stream'][]=(string) $plugin->title;
                }
                //print_r($plugins);               

                //Get site shortname for additional options
                $activated_plugins_siteshortname=$settings->shortname;
                
                //Don't activate plugins that are already network activated in Discover-wp multisite
                $inclusive_sites_for_check=array('bc','cl','bre');                         
                if ((in_array($activated_plugins_siteshortname, $inclusive_sites_for_check)) && (is_multisite())) {	
                	$site_complete_url= get_site_url();
                	
                	// get host name from URL
                	preg_match('@^(?:http://)?([^/]+)@i',$site_complete_url, $preg_matches);
                	$host_match = $preg_matches[1];
                	
                	// get last two segments of host name
                	preg_match('/[^.]+\.[^.]+$/', $host_match, $preg_matches);                	
                	
                	$site_host=$preg_matches[0];

                	$already_network_activated=array('discover-wp.com','discover-wp.dev','views-live-demo.local');
                	
                	if (in_array($site_host, $already_network_activated)) {
                		
                	    //Site is either discover-wp.com or discover-wp.dev
                	    //Exclude CRED and Types in plugins array
                	    
                		foreach ($plugins['plugin_file_stream'] as $filestream_key=>$filestream_value) {
                			
                			$already_network_activated_plugins=array(
                					'cred-trunk/plugin.php',
                					'types-access/types-access.php',
                					'wpml_tm/plugin.php',
                					'wpml/sitepress.php',
                					'wpml-string-translation/plugin.php'
                					 );            					
                					          			
                			if (in_array($filestream_value, $already_network_activated_plugins)) {
                				//Remove from activation
                				unset($plugins['plugin_file_stream'][$filestream_key]);                				
                				
                			}                				
                			
                		}
                		
                		foreach ($plugins['plugin_name_stream'] as $namestream_key=>$namestream_value) {
                			
                			$already_network_activated_plugin_name=array(
                					'CRED Frontend Editor',
                					'Access',
                					'WPML Translation Management',
                					'WPML Multilingual CMS',
                					'WPML String Translation'
                			);                			               			
                			
                			if (in_array($namestream_value, $already_network_activated_plugin_name)) {
                				unset($plugins['plugin_name_stream'][$namestream_key]);
                			
                			}                			
                			
                			
                		}
                		
                		
                	}
                	
                } 
                add_action( 'activated_plugin', 'wpv_demo_remove_wpml_recently_activated_option',1000,2);                
                $errors = wpvdemo_activate_plugins($plugins,$activated_plugins_siteshortname);
                if (!empty($errors)) {
                    ob_end_clean();
                    echo '<div class="message error"><p>';
                    foreach ($errors as $plugin => $error) {
                        echo sprintf(wpvdemo_error_message('plugin_activation'),
                                $plugin, $error) . '<br />';
                    }
                    echo '</p></div>';
                    die();
                }
            }                       
            ob_end_clean();
            
            break;

        case 2:
        	        	
        	// Import Posts
            // Allow updating post count
            session_write_close();
            ob_start();
            //Disable Kses filters as well, issues on importing Classifieds WooCommerce custom functionality
            if (function_exists('kses_remove_filters')) {
            	kses_remove_filters();
            }           
            wpvdemo_import_posts($settings, $import_settings);
            ob_end_clean();   
                     
            break;
            

        case 3:
            // Import Views
            
            // EMERSON fix: Disable filters to correctly import Views containing HTML tags in post_content        	
        	if (function_exists('kses_remove_filters')) {
        	kses_remove_filters();
        	}
        	
            $success = wpvdemo_import_views($settings->download_url, $settings);
            if (!($success)) {
                echo sprintf(wpvdemo_error_message('importing_views', true));
                die();
            }
            // Update post relationships
            global $wpvdemo_import;
            if (!empty($wpvdemo_import->processed_posts)) {
                foreach ($wpvdemo_import->processed_posts as $old_id => $new_id) {
                    $post = get_post($new_id);
                    if (!empty($post)) {
                        $update_posts = get_posts('meta_key=_wpcf_belongs_'
                                . $post->post_type . '_id&meta_value='
                                . $old_id . '&numberposts=-1&post_type=any');
                        if (!empty($update_posts)) {
                            foreach ($update_posts as $update_post) {
                                update_post_meta($update_post->ID,
                                        '_wpcf_belongs_'
                                        . $post->post_type . '_id', $new_id);
                            }
                        }
                    }
                }
            }
            
            break;

        case 4:
            // Import CRED forms
            
            //EMERSON: Disable Filters to allow important of HTML tags in CRED Form Content
        	if (function_exists('kses_remove_filters')) {
        		kses_remove_filters();
        	}
            $success = wpvdemo_import_cred($settings->download_url, $settings);
            
            break;
            
        case 5:
           	// Import Types Access
           	
           	$success = wpvdemo_import_access($settings->download_url, $settings);

           	break;

        case 6:
            // Import WPML settings and strings
            
            $success = wpvdemo_import_wpml($settings->download_url, $settings);
           
            break;
        // START :Import Inline documentation and settings
        case 7:        	
        	
        	$success = inline_doc_content_import($settings->download_url, $settings);
        	
        	break;       	
        // END :Import Inline documentation and settings      
        case 8:
        	
        	// Download theme
        	$template = basename($settings->theme_url, '.zip');
        	$stylesheet = basename($settings->theme_url, '.zip');
        	$success = true;
        	
        	if (is_multisite()) {
        		//Re-define
        		$import_settings->download_theme=true;
        	}
        	
        	if ($import_settings->download_theme) {
        		$success = wpvdemo_download_theme($settings->theme_url);
        		if (!$success) {
        			echo sprintf(wpvdemo_error_message('download_theme', true),
        					$settings->theme_url);
        			die();
        		} else if ($success && !empty($settings->theme_parent_url)) {
        			$success = wpvdemo_download_theme($settings->theme_parent_url);
        			if (!$success) {
        				echo sprintf(wpvdemo_error_message('download_theme_parent',
        						true), $settings->theme_parent_url);
        				die();
        			}
        			$template = basename($settings->theme_parent_url, '.zip');
        		}
        	} else {
        		if (!empty($settings->theme_parent_url)) {
        			$template = basename($settings->theme_parent_url, '.zip');
        		}
        	}
        	if ($success) {
        		switch_theme($template, $stylesheet);
        	}      	

        	// Set homepage and posts page settings
        	if (!empty($settings->show_on_front)) {
        		update_option('show_on_front', (string) $settings->show_on_front);
        		if ((string) $settings->show_on_front == 'page') {
        			global $wpdb;
        			if (!empty($settings->page_on_front)) {
        			$page = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'",
        				(string) $settings->page_on_front));
        				if ($page) {
        				update_option('page_on_front', $page);
        				}
        			}
        			if (!empty($settings->page_for_posts)) {
        			$page = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'",
        					(string) $settings->page_for_posts));
        					if ($page) {
        							update_option('page_for_posts', $page);
        					}
        			}
        		}
        	}         	

        break;        	
        
        case 9:
        	
        	if (function_exists('kses_remove_filters')) {
        		kses_remove_filters();
        	}
        	//START: Module manager import
        	$success = module_manager_views_demo_import($settings->download_url, $settings);        	 
        	break;
        	// END : Module manager import
        	
        case 10:
        	//Bootmag site, unique import settings
        	 
        	$bootmagsite_title=$settings->shortname;
        	$bootstrap_sites_shortname_array=array('bm','bt','bc','ai','cl','bre');
        	
        	if (in_array($bootmagsite_title,$bootstrap_sites_shortname_array)) {
        		 
        		//Import options framework
        		if (!empty($settings->optionsframework)) {
        			 
        			$options_framework_array = array();
        			foreach ($settings->optionsframework as $mods) {
        				foreach ($mods as $mod_name => $mod_value) {
        					if ((string) $mod_name=='id') {
        	
        						$options_framework_array[(string) $mod_name] = (string)$mod_value;
        	
        					} else {
        	
        						$options_framework_array[(string) $mod_name] = $mod_value;
        	
        					}
        				}
        			}
        			 
        			//Get known options
        			$bootmag_known_options_imported=$options_framework_array['knownoptions'];
        			 
        			//Rename to the correct keys after import
        			$new_options_framework_array=array();
        			 
        			foreach ($bootmag_known_options_imported as $key=>$value) {
        				 
        				$new_options_framework_array[] =(string)$value;
        			}
        			 
        			//Delete previous options
        			unset($options_framework_array['knownoptions']);
        				
        			//Add new known options with correct keys
        			$options_framework_array['knownoptions']=$new_options_framework_array;
        			 
        			//Update theme option settings in database
        			update_option('optionsframework', $options_framework_array);
        		}
        		 
        		//Import Bootstrap theme settings
        	
        		//Get Bootstrap template
        		$bootmag_optionsframework_imported= get_option('optionsframework');
        		$bootmag_stylesheet_template=$bootmag_optionsframework_imported['id'];
        		 
        		if ($bootmag_stylesheet_template=='bootstrap_magazine') {
        			 
        			//Child theme implementation
        			if (!empty($settings->bootstrap_magazine)) {
        	
        				$bootmag_import_data=$settings->bootstrap_magazine;
        				$bootmag_theme_options_settings=array();
        	
        				//Convert Bootmag settings to array
        				if (function_exists('bootmag_xml2array')) {
        					$bootmag_theme_options_settings=bootmag_xml2array($bootmag_import_data);
        					$bootmag_theme_options_settings_final=$bootmag_theme_options_settings['bootstrap_magazine'];
        					 
        				}
        				 
        				update_option('bootstrap_magazine', $bootmag_theme_options_settings_final);
        				 
        			}
        			 
        		} elseif ($bootmag_stylesheet_template=='toolset_classifieds') {
        			 
        			$bootmag_import_data=$settings->bootstrap_classifieds;
        			$bootmag_theme_options_settings=array();
        			 
        			//Convert Bootmag settings to array
        			if (function_exists('bootmag_xml2array')) {
        				$bootmag_theme_options_settings=bootmag_xml2array($bootmag_import_data);
        				$bootmag_theme_options_settings_final=$bootmag_theme_options_settings['bootstrap_classifieds'];
        			}
        			 
        			update_option('toolset_classifieds', $bootmag_theme_options_settings_final);
        			 
        			//Removing of unused classified sites widget to prevent template distortion after import
        			update_options_classified_widgets();
        			 
        			//Import WooCommerce Settings for Classifieds Site
        			$success_import_classifieds_woocommerce=wpvdemo_import_classifieds_woocommerce($settings->download_url, $settings);
        			 
        			if (!$success_import_classifieds_woocommerce) {
        				echo wpvdemo_error_message('importing_classifieds_woocommerce', true);
        				die();
        			}
        			 
        			//Import user roles for classifieds site
        			global $wpdb;
        			$define_table_prefix=$wpdb->prefix;
        			 
        			$success_import_classified_user_roles=wpvdemo_import_classifieds_user_roles($settings->download_url, $define_table_prefix);
        			if (!$success_import_classified_user_roles) {
        				echo wpvdemo_error_message('importing_classifieds_user_roles', true);
        				die();
        			}
        	
        			//Configure CRED notification settings for CRED commerce
        			wpvdemo_config_notification_classifieds_site($settings->download_url, $define_table_prefix);
        			 
        			//Import CRED custom fields for Classifieds site
        			$check_if_credcustomfields_exist=get_option('__CRED_CUSTOM_FIELDS');
        			if (!($check_if_credcustomfields_exist)) {
        				 
        				//options does not exist, import
        				$success_classifieds_credcustomfields=wpvdemo_import_classifieds_credcustomfields($settings->download_url, $settings);
        				if (!$success_classifieds_credcustomfields) {
        					echo wpvdemo_error_message('importing_cred_custom_fields_classifieds', true);
        					die();
        				}
        			}
        			 
        			//Import CSS and activate the WooCommerce Styling
        			//Fixed issues relating to WooCommerce 2.1+. Changed in WC path in this version
        			global $woocommerce;
        			if (!empty($woocommerce)) {
        	
        				//Get WooCommerce version
        				$woocommerce_version_import=$woocommerce->version;
        	
        				if (version_compare($woocommerce_version_import, '2.1.0', '<')) {
        					 
        					//WooCommerce version 2.1.0 and below
        					require_once $woocommerce->plugin_path() . '/admin/woocommerce-admin-functions.php';
        					woocommerce_compile_less_styles();
        	
        				} else {
        					 
        					//WooCommerce version 2.1.0 and beyond
        					require_once $woocommerce->plugin_path() . '/includes/admin/wc-admin-functions.php';
        					woocommerce_compile_less_styles();
        	
        				}
        			}
        			 
        			//Fixed Bootstrap Classifieds no dropdown issue on navigation
        			$nav_term_information=get_term_by('name', 'Main navigation', 'nav_menu');
        			 
        			//Get term_id of main navigation
        			$term_id_nav=$nav_term_information->term_id;
        			$theme_mods_bootstrap_classifieds=array(0=>false,'nav_menu_locations'=>array('header-menu'=>$term_id_nav));
        			update_option( 'theme_mods_toolset-classifieds', $theme_mods_bootstrap_classifieds );
        			 
        			//Don't run wizard on WooCommerce Views 2.1 after import
        			update_option( 'wc_views_user_completed_wizard', 'yes');
        				
        		} elseif ($bootmag_stylesheet_template=='toolset_real_estate') {
        			 
        			$bootmag_import_data=$settings->bootstrap_realestate;
        			$bootmag_theme_options_settings=array();
        			 
        			//Convert Bootmag settings to array
        			if (function_exists('bootmag_xml2array')) {
        				$bootmag_theme_options_settings=bootmag_xml2array($bootmag_import_data);
        				$bootmag_theme_options_settings_final=$bootmag_theme_options_settings['bootstrap_realestate'];
        			}
        			 
        			update_option('toolset_real_estate', $bootmag_theme_options_settings_final);
        			 
        			//Fixed Bootstrap Real Estate no dropdown issue on navigation
        			$nav_term_information=get_term_by('name', 'Header', 'nav_menu');
        			 
        			//Get term_id of main navigation
        			$term_id_nav=$nav_term_information->term_id;
        			$theme_mods_bootstrap_realestate=array(0=>false,'nav_menu_locations'=>array('header-menu'=>$term_id_nav));
        			update_option( 'theme_mods_toolset-real-estate', $theme_mods_bootstrap_realestate );
        			
        			//Manual fix for Views taxonomies (current limitation in auto-export in reference site)
        			bootstrap_estate_fix_properties_taxonomiesview_settings();
        			         				
        		} elseif ($bootmag_stylesheet_template=='bootstrap_theme') {
        			//Main theme implementation
        			if ($bootmagsite_title=='bt') {
        				if (!empty($settings->bootstrap_plain)) {
        						
        					$bootmag_import_data=$settings->bootstrap_plain;
        					$bootmag_theme_options_settings=array();
        						
        					//Convert Bootstrap plain settings to array
        					if (function_exists('bootmag_xml2array')) {
        						$bootmag_theme_options_settings=bootmag_xml2array($bootmag_import_data);
        						$bootmag_theme_options_settings_final=$bootmag_theme_options_settings['bootstrap_plain'];
        	
        					}
        						
        					update_option('bootstrap_theme', $bootmag_theme_options_settings_final);
        					 
        					//Update home URL for plain vanilla
        					//Get ID of home URL
        					global $wpdb;
        					@$home_url_id_vanilla= $wpdb->get_var("select ID from $wpdb->posts where post_name='home-2'");
        					$site_url_imported_vanilla = get_bloginfo('url');
        					$success_meta_update=update_post_meta($home_url_id_vanilla, '_menu_item_url', $site_url_imported_vanilla);
        					 
        					if (!(is_multisite())) {
        						//not multisite, fix image URLs
        						fix_image_urls_bootstrap_vanilla_standalone($site_url_imported_vanilla);
        					}
        					 
        					//Fixed Bootstrap no dropdown issue on navigation
        					$theme_mods_bootstrap_theme=array(0=>false,'nav_menu_locations'=>array('header-menu'=>175));
        					update_option( 'theme_mods_bootstrap-theme', $theme_mods_bootstrap_theme );
        				}
        			}
        			if 	($settings->shortname=='ai') {
        				if (!empty($settings->bootstrap_views_tutorial)) {
        					 
        					$bootmag_import_data=$settings->bootstrap_views_tutorial;
        					$bootmag_theme_options_settings=array();
        						
        					//Convert Bootstrap plain settings to array
        					if (function_exists('bootmag_xml2array')) {
        						$bootmag_theme_options_settings=bootmag_xml2array($bootmag_import_data);
        						$bootmag_theme_options_settings_final=$bootmag_theme_options_settings['bootstrap_views_tutorial'];
        						 
        					}
        						
        					update_option('bootstrap_theme', $bootmag_theme_options_settings_final);
        					 
        					//Views tutorial reference site has no sidebar widgets
        					$no_sidebar_widgets_array=array('wp_inactive_widgets'=>array(),'sidebar'=>array(),'header-widgets'=>array(),'footer-widgets'=>array(),'array_version'=>3);
        					update_option('sidebars_widgets',$no_sidebar_widgets_array);
        				}
        					
        					
        			}
        			if 	($settings->shortname=='bc') {
        					
        				if (!empty($settings->bootstrap_commerce)) {
        						
        					$bootmag_import_data=$settings->bootstrap_commerce;
        					$bootmag_theme_options_settings=array();
        						
        					//Convert Bootstrap plain settings to array
        					if (function_exists('bootmag_xml2array')) {
        						$bootmag_theme_options_settings=bootmag_xml2array($bootmag_import_data);
        						$bootmag_theme_options_settings_final=$bootmag_theme_options_settings['bootstrap_commerce'];
        							
        					}
        	
        					$success_updating_bootcommerce_settings=update_option('bootstrap_theme', $bootmag_theme_options_settings_final);
        					 
        					//Fixed header menu not imported with WPML
        					$menu_array_wpml=array(0=>false,'auto_add'=>array());
        					$success_updating_menu_array_wpml=update_option( 'nav_menu_options', $menu_array_wpml );
        					 
        					//Get term_id of main navigation
        					$nav_term_information_bc=get_term_by('name', 'Main', 'nav_menu');
        					$term_id_nav_bc=$nav_term_information_bc->term_id;
        					$theme_mods_bootstrap_commerce=array(0=>false,'nav_menu_locations'=>array('header-menu'=>$term_id_nav_bc));
        					update_option( 'theme_mods_bootstrap-theme', $theme_mods_bootstrap_commerce );
        					 
        					//Customize WooCommerce Views settings
        					//Set woocommerce_views_theme_template_file
        					 
        					$theme_information=wp_get_theme();
        					$name_of_template=$theme_information->template;
        					$theme_root_template=$theme_information->theme_root;
        					$path_to_pagetemplate=$theme_root_template.'/'.$name_of_template.'/'.'page.php';
        					if (file_exists($path_to_pagetemplate)) {
        						$woocommerce_views_template_file_setting=array($name_of_template=>$path_to_pagetemplate);
        						$success_wc_views_template_updating=update_option('woocommerce_views_theme_template_file',$woocommerce_views_template_file_setting);
        					}
        					 
        					global $woocommerce;
        					if (!empty($woocommerce)) {
        						 
        						//Get WooCommerce version
        						$woocommerce_version_import=$woocommerce->version;
        						 
        						if (version_compare($woocommerce_version_import, '2.1.0', '<')) {
        	
        							//WooCommerce version 2.1.0 and below
        							require_once $woocommerce->plugin_path() . '/admin/woocommerce-admin-functions.php';
        							woocommerce_compile_less_styles();
        	
        						} else {
        	
        							//WooCommerce version 2.1.0 and beyond
        							require_once $woocommerce->plugin_path() . '/includes/admin/wc-admin-functions.php';
        							woocommerce_compile_less_styles();
        						}
        					}
        					//Import general WooCommerce settings from reference site
        					$success_import_bootcommerce_woocommerce=wpvdemo_import_classifieds_woocommerce($settings->download_url, $settings);
        					 
        					//Import and configure BootCommerce WooCommerce setting pages
        					setup_woocommerce_setting_pages_bootcommerce();
        				}
        				 
        			}
        		}
        		//Set permalinks for bootmag site
        		 
        		 
        		global $wp_rewrite;
        		require_once(ABSPATH . 'wp-admin/includes/misc.php');
        		require_once(ABSPATH . 'wp-admin/includes/file.php');
        	
        		// Prepare WordPress Rewrite object in case it hasn't been initialized yet
        		if (empty($wp_rewrite) || !($wp_rewrite instanceof WP_Rewrite))
        		{
        			$wp_rewrite = new WP_Rewrite();
        		}
        	
        		// Update permalink structure
        		if ($settings->shortname != 'bc') {
        	
        			$permalink_structure = '/%year%/%monthnum%/%day%/%postname%/';
        	
        		} elseif ($settings->shortname == 'bc') {
        	
        			$permalink_structure = '/%postname%/';
        		}
        		 
        		$wp_rewrite->set_permalink_structure($permalink_structure);
        	
        		// Recreate rewrite rules
        		$wp_rewrite->flush_rules();
        		 
        		 
        		//Import default grid XML for updated Bootstrap theme
        		wpbootstrap_default_grid_xml_import();
        	
        	} else {
        		 
        		//Not a bootmag site, normal theme option settings import
        		$template = basename($settings->theme_url, '.zip');
        		$stylesheet = basename($settings->theme_url, '.zip');
        		
        		// Set theme mods
        		if (!empty($settings->theme_mods)) {
        			$update_mods = array();
        			foreach ($settings->theme_mods as $mods) {
        				foreach ($mods as $mod_name => $mod_value) {
        					$mod_value = wpvdemo_convert_url((string) $mod_value,$settings);
        					$update_mods[(string) $mod_name] = $mod_value;
        				}
        			}
        			update_option('theme_mods_' . $stylesheet, $update_mods);
        		}
        	
        	
        		// Set menus
        		if (!empty($settings->menus)) {
        			$menus = array();
        			foreach ($settings->menus as $position => $menu) {
        				foreach ($menu as $menu_position => $menu_name) {
        					$menu_obj = wp_get_nav_menu_object((string) $menu_name);
        					if (!empty($menu_obj->term_id)) {
        						$menus[(string) $menu_position] = $menu_obj->term_id;
        					} else {
        						$menus[(string) $menu_position] = 0;
        					}
        				}
        			}
        			set_theme_mod('nav_menu_locations', $menus);
        		}
        		 
        		 
        	}     
        	// Set title and tagline
        	update_option('blogname', (string) $settings->title);
        	update_option('blogdescription',
        	wpvdemo_convert_url((string) $settings->tagline, $settings));
        	
            // Import widgets
            if (!empty($settings->sidebars_widgets)) {
                
            	wpvdemo_import_widgets($settings->sidebars_widgets);

            }
            $demo_settings = array();
            $demo_settings['ID'] = (string) $settings->ID;
            $demo_settings['title'] = (string) $settings->title;
            $demo_settings['tutorial_title'] = (string) $settings->tutorial_title;
            $demo_settings['tutorial_url'] = (string) $settings->tutorial_url;
            $demo_settings['installed'] = 1;
            update_option('wpvdemo', $demo_settings);
            
            //Enable resizing of remote images for Views commerce ML
            
            $downloaded_referencesitename=$settings->title;
            if ($downloaded_referencesitename=='Views Commerce Multilingual') {
            	if (function_exists('wpcf_get_settings')) {
            		$types_remotesettings = wpcf_get_settings();
            		if ($types_remotesettings['images_remote'] <> 1) {
            			$types_remotesettings['images_remote'] = 1;
            			update_option('wpcf_settings', $types_remotesettings);
            		}
            	}
            }
            //Fix product comparison slug for BootCommerce site
            if ($settings->shortname == 'bc') {
            	/*Fix product comparison slug*/
            	//Execute only when downloading the WPML version of the site
            	if (defined('ICL_SITEPRESS_VERSION')) {
            		$results_updating_postslug=bootcommerce_fix_productcomparison_afterimport();
            	}
            	
            	//Refresh permalinks after import
            	global $wp_rewrite;
            	$wp_rewrite->flush_rules(false);
            }
            
            //Indicate import is done
            update_option( 'wpv_import_is_done', 'yes');
            
            //Refresh CRED Forms for some sites
            do_action('wpv_demo_refresh_cred_forms',$settings->shortname);
            remove_action('wpv_demo_refresh_cred_forms','wpv_refresh_cred_form_after_import',10,1);
                        
            //Activate Types and Views plugin for some sites
            do_action('wpv_demo_activate_types_views_after_import',$settings->shortname);
            remove_action('wpv_demo_activate_types_views_after_import','wpvdemo_activate_types_views_modules_import',99,1);
            
            //delete_option('wpvdemo_check_if_blank');
            echo '<br /><strong>' . __('Done!', 'wpvdemo') . '</strong> <span class="wpvdemo-green-check">&nbsp;&nbsp;&nbsp;&nbsp;</span><br /><br />';
            if (function_exists('wp_get_theme')) {
                $theme = wp_get_theme();
                $theme_name = $theme->Name;
            } else {
                $theme_name = get_current_theme();
            }
            printf(__("The reference site was successfully imported. We've activated the theme: %s. This test site should look the same as our reference site.",
                            'wpvdemo'), $theme_name);
            echo '<br /><br />';
            $links = array();
            $links[] = sprintf(__("%sVisit your site%s", 'wpvdemo'),
                    '<a href="' . get_site_url() . '" title="'
                    . get_bloginfo('name') . '">', '</a>');
            if (!empty($settings->tutorial_title) && !empty($settings->tutorial_url)) {
                $links[] = sprintf(__('%sView site tutorial%s', 'wpvdemo'),
                        '<a href="' . (string) $settings->tutorial_url
                        . '" target="_blank" title="'
                        . (string) $settings->tutorial_title . '">',
                        '</a>');
            }
            $links = apply_filters('wpvdemo_complete_links', $links);
            echo implode(' ', $links);
            echo '<script type="text/javascript"></script>';
            break;

        default:
            break;
    }

    if ($step < 10) {
        wpvdemo_import_next_step_js($site_id, $step);
    }

    do_action('wpvdemo_import_after_step_' . $step, $settings);
}

/**
 * Renders JS that triggers next import step.
 * 
 * @param type $site_id
 * @param type $step 
 */
function wpvdemo_import_next_step_js($site_id, $step) {
    $step = intval($step) + 1;
    echo '<script type="text/javascript">wpvdemoDownloadStep('
    . $site_id . ', ' . $step . ');</script>';
}

/**
 * Imports types.
 * 
 * @param type $baseurl
 * @return boolean 
 */
function wpvdemo_import_types($baseurl) {
    $_POST['overwrite-groups'] = 1;
    $_POST['overwrite-fields'] = 1;
    $_POST['overwrite-types'] = 1;
    $_POST['overwrite-tax'] = 1;
//                    $_POST['delete-groups'] = 0;
//                    $_POST['delete-fields'] = 0;
//                    $_POST['delete-types'] = 0;
//                    $_POST['delete-tax'] = 0;
    $_POST['post_relationship'] = 1;
    $file = $baseurl . '/types.xml';    
    
    //Parse remote XML
    $data=wpv_remote_xml_get($file);
    if (!($data)) {
        return false;
    }
    
    //Parameter wpvdemo is added in Types 1.3 to prevent errors in import Types fields to reference sites
    $success = wpcf_admin_import_data($data, false,'wpvdemo');
    if ($success === false) {
        return false;
    }
    return true;
}

function wpvdemo_view_imported_hook($old_view_id, $new_view_id) {
    global $wpvdemo_import;
    $wpvdemo_import->processed_posts[$old_view_id] = $new_view_id;
}

/**
 * Imports views.
 * 
 * @global type $wpdb
 * @param type $baseurl
 * @return type 
 */
function wpvdemo_import_views($baseurl, $settings) {
    global $wpdb, $wpvdemo_import;
    
    define('WP_LOAD_IMPORTERS', true);
    require_once WPVDEMO_ABSPATH . '/class.importer.php';
    require_once ABSPATH . 'wp-admin/includes/post.php';
    require_once ABSPATH . 'wp-admin/includes/comment.php';
    $wpvdemo_import = new WPVDemo_Importer($settings->site_url);

    $_POST['view-templates-overwrite'] = 'on';
//    $_POST['view-templates-delete'] = 'on';
    $_POST['views-overwrite'] = 'on';
//    $_POST['views-delete'] = 'on';
    $_POST['view-settings-overwrite'] = 'on'; // isset;
    $file = $baseurl . '/views.xml';
        
    //Parse remote XML
    $data=wpv_remote_xml_get($file);
    if (!($data)) {
    	return false;
    }
    
    $xml = simplexml_load_string($data);
    $import_data = wpv_admin_import_export_simplexml2array($xml);

    // import view templates first.   
    $error = wpv_admin_import_view_templates($import_data);
    if ($error) {
        return $error;
    }

    // import views next.   
    add_action('wpv_view_imported', 'wpvdemo_view_imported_hook', 10, 2);
    $error = wpv_admin_import_views($import_data);
    if ($error) {
        return $error;
    }
    $wpvdemo_import->process_wpml();
    
    remove_action('wpv_view_imported', 'wpvdemo_view_imported_hook', 10, 2);


    // import views next.   
    $error = wpv_admin_import_settings($import_data);
    if ($error) {
        return $error;
    }

    // Update template IDs in posts
    if (!empty($import_data['view-templates'])) {
        // check for a single view template
        $view_templates = $import_data['view-templates']['view-template'];

        // check for a single view template
        if (!isset($view_templates[0])) {
            $view_templates = array($view_templates);
        }

        foreach ($view_templates as $view_template) {
            $view_template_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'view-template' AND post_name = %s",
                            $view_template['post_name']));
            if (!empty($view_template_id)) {
                $posts = get_posts('meta_key=_views_template&meta_value='
                        . $view_template['ID'] . '&numberposts=-1&post_type=any');
                if (!empty($posts)) {
                    foreach ($posts as $post) {
                        update_post_meta($post->ID, '_views_template',
                                $view_template_id);
                    }
                }
            }
        }
    }
    return true;
}

/*START: INLINE DOCUMENTATION IMPORT INTEGRATION
 * 
 */
function inline_doc_content_import($baseurl, $settings) {	
	$file = $baseurl . '/inlinedoc.xml';	
	$file_headers_inline_doc = @get_headers($file);
		
	//Check if inline doc XML exist, if yes then import
	if(strpos($file_headers_inline_doc[0],'200 OK')) {	
	$xml_import_data=file_get_contents($file);
	
	if (class_exists('Class_Inline_Documentation')) {
	$Class_Inline_Documentation = new Class_Inline_Documentation;
	$Class_Inline_Documentation->inline_doc_import($xml_import_data);
	}
		
	}
	return true;
}
/*END: INLINE DOCUMENTATION IMPORT INTEGRATION
 *
*/

/*START: MODULE MANAGER IMPORT INTEGRATION
 *
*/
function module_manager_views_demo_import($baseurl, $settings) {

	$all_plugins_found=get_plugins();
	$compatible_types_views_found=wpvdemo_get_types_views_pluginlist($all_plugins_found);	
	$compatible_types_views_found_in_pluginsdir=false;
	
	if ((is_array($compatible_types_views_found)) && (!(empty($compatible_types_views_found)))) {
		$compatible_types_views_found_in_pluginsdir=true;
	}
	
	//Import modules only if using compatible Types and Views in plugins directory; 
	//Just as it was when importing modules manually.
	
	if ((class_exists('ModuleManager')) && ($compatible_types_views_found_in_pluginsdir)) {
		//Module manager plugin class exists

		$module_manager_plugin_path=MODMAN_PLUGIN_PATH;
		$embedded_module_library_install_class_path=$module_manager_plugin_path.'/library/Class_Install_Library.php';
		require_once($embedded_module_library_install_class_path);
		$Class_Install_Library = new Class_Install_Library;

		if (!empty($settings->modules)) {

			$modules_data=$settings->modules;

			foreach ($modules_data as $modules_imported) {

				foreach ($modules_imported as $key=>$exported_modules) {

					$modules_exported_path=$exported_modules->path;
					 
					//Define the URL location of the modules zip file
					$file=(string)$modules_exported_path;
					$parameters['module_path']=$file;
						
					$imported_id_in_database=$Class_Install_Library->mm_automatic_install_wc_views($parameters);
					//Don't bombard server with request.
					sleep(2);
				}

			}
				
		}
	}
		
}

/*END: MODULE MANAGER IMPORT INTEGRATION
 *
*/
/**
 * Imports cred.
 * 
 * @global type $wpdb
 * @param type $baseurl
 * @return type 
 */
function wpvdemo_import_cred($baseurl, $settings) {
    global $wpdb;

    if (defined('CRED_FE_VERSION')) {
        
        $file = $baseurl . '/cred.xml';
      
        //Parse remote XML        
        $data=wpv_remote_xml_get($file);
        if (!($data)) {
        	return false;
        }       
       
        $cred_evaluate_shortname=$settings->shortname;
        
        if ($cred_evaluate_shortname=='cl') {
        	
        	//Importing Classifieds site, enable CRED commerce        
        	 
        	$cred_commerce_path_required=get_cred_commerce_plugin_path_import();
        	
        	if (!(empty($cred_commerce_path_required))) { 

        	require_once $cred_commerce_path_required;
        	CRED_Commerce::init('woocommerce',true);
        
        	$result = cred_import_xml_from_string($data, array('overwrite_forms'=>1, 'overwrite_settings'=>1, 'overwrite_custom_fields'=>1));
        
        	if (false===$result || is_wp_error($result))
        	    return (false===$result)?__('Error during CRED import','wpvdemo'):$result->get_error_message($result->get_error_code());
        	
        	} else {
        		
        		die('It seems you really do not have CRED commerce in your plugins directory.');
        		
        	}
    
        } else {
        	
        //Import CRED normally
        	
        	$result = cred_import_xml_from_string($data);
        	
        	if (false===$result || is_wp_error($result))
        		return (false===$result)?__('Error during CRED import','wpvdemo'):$result->get_error_message($result->get_error_code());    	

        }
    }

    return true;
}

function get_cred_commerce_plugin_path_import() {	
	
	//Get active plugins
	$active_plugins_cred_commerce = get_option('active_plugins', array());    
	$probable_credcommerce_path=array();

	//Loop through array and find CRED commerce plugin possible path
	foreach ($active_plugins_cred_commerce as $k=>$v) {
		
		if (strpos($v,'plugin.php')) {
			
			$probable_credcommerce_path[]=dirname(WPVDEMO_ABSPATH).'/'.$v;

		}

	}
	if (!(empty($probable_credcommerce_path))) { 
		//Loop through the $probable_credcommerce_path and find the exact CRED commerce path
		foreach ($probable_credcommerce_path as $key=>$value) {
		
			$cred_commerce_handle = fopen($value, "r");
			$cred_commerce_contents = fread($cred_commerce_handle,filesize($value));
			$cred_commerce_pieces = explode("\n", $cred_commerce_contents);
			$cred_commerce_key = array_find('Plugin Name', $cred_commerce_pieces);
			$cred_commerce_value=trim($cred_commerce_pieces[$cred_commerce_key]);
			$cred_commerce_value_exploded=explode(":",$cred_commerce_value);;
			$cred_commerce_plugin_name_extract=$cred_commerce_value_exploded[1];
		
			//This is the actual plugin name of plugin.php found in plugins directory
			$cred_commerce_plugin_name_extract=trim($cred_commerce_plugin_name_extract);
	
			if ($cred_commerce_plugin_name_extract=='CRED Commerce') {
	
				//Check first if file exists
				if (file_exists($value)) {
				
					return $value;
				} else {
					
					return '';
				}
			} 
		
		}
	} else {
		
		return '';
		
	} 

}
/**
 * Imports Types Access
 */

function wpvdemo_import_access($baseurl, $settings) {
	global $wpdb;

	if (defined('TACCESS_VERSION') && function_exists('taccess_import')) {

		$file = $baseurl . '/access.xml';

		//Parse remote XML
		$data=wpv_remote_xml_get($file);
		if (!($data)) {
			return false;
		}		
		
        $options_access=array();
		$result = taccess_import($data,$options_access);
		if (false===$result || is_wp_error($result))
			return (false===$result)?__('Error during Access import','wpvdemo'):$result->get_error_message($result->get_error_code());
	}

	return true;
}

/**
 * Imports Classifieds Site and BootCommerce WooCommerce settings
 */
function wpvdemo_import_classifieds_woocommerce($baseurl, $settings) {
	global $wpdb;

	$site_imported_shortname=(string)$settings->shortname;	
	$array_of_woocommerce_export_files=array('cl'=>'classifieds_woocommerce.xml','bc'=>'bootcommerce_woocommerce.xml');
	
	if (isset($array_of_woocommerce_export_files[$site_imported_shortname])) {
	 
		$woocommerce_export_file_name=$array_of_woocommerce_export_files[$site_imported_shortname];
		$file = $baseurl . '/'.$woocommerce_export_file_name;	
	
		//Parse remote XML
		$data=wpv_remote_xml_get($file);
		if (!($data)) {
			return false;
		}
	
		$xml = simplexml_load_string($data);
		$import_data = wpv_admin_import_export_simplexml2array($xml);
	
		//Loop through the settings and update WooCommerce options
		foreach ($import_data as $key=>$value) {
			update_option( $key, $value);		
		}
	
		//Define WooCommerce shop page ID
		$existing_shop_page=get_option('woocommerce_shop_page_id');
		if ((!($existing_shop_page)) || (empty($existing_shop_page))) {
			
			//Shop page not yet defined
			$posttable=$wpdb->posts;
			$shop_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='shop' AND post_type='page'");
			if (!(empty($shop_page_id))) {				
				update_option( 'woocommerce_shop_page_id', $shop_page_id );				
			}			
		}
		//Finishing touches
		global $current_user;
		$user_current_email=$current_user->user_email;
	
		if ($user_current_email) {

			update_option( 'woocommerce_stock_email_recipient', $user_current_email);
	 	    update_option( 'woocommerce_new_order_email_recipient', $user_current_email);	
	
		}
    }
	return true;
}

/**
 * Imports Classifieds CRED custom field settings
 */
function wpvdemo_import_classifieds_credcustomfields($baseurl, $settings) {
	global $wpdb;

	$file = $baseurl . '/classifieds_credcustomfields.xml';

	//Parse remote XML
	$data=wpv_remote_xml_get($file);
	if (!($data)) {
		return false;
	}	

	$xml = simplexml_load_string($data);
	$import_data = wpv_admin_import_export_simplexml2array($xml);

	//Loop through the settings and update WooCommerce options
	foreach ($import_data as $key=>$value) {
		update_option( $key, $value);
	}

	return true;
}

/**
 * Imports Classifieds Site User Roles
 */
function wpvdemo_import_classifieds_user_roles($baseurl, $define_table_prefix) {
	global $wpdb;

	$file = $baseurl . '/classifieds_user_roles.xml';

	//Parse remote XML
	$data=wpv_remote_xml_get($file);
	if (!($data)) {
		return false;
	}
	
	$xml = simplexml_load_string($data);
	$import_data = wpv_admin_import_export_simplexml2array($xml);

	$user_role_option_name=$define_table_prefix.'user_roles';
	
	$import_user_role_settings=array();
	
	foreach ($import_data as $key=>$value) {
		
		$import_user_role_settings=$value;
	} 	

	//Save the imported role settings to the database
	update_option( $user_role_option_name, $import_user_role_settings);	

	return true;
}

function wpvdemo_config_notification_classifieds_site($baseurl, $define_table_prefix) {

	global $wpdb;
	global $current_user;
	
	$post_table_name=$define_table_prefix.'posts';
	
	//Define CRED forms array with notifications	
	/*Framework Installer 1.5.3 -updated with Classifieds site new Add package forms*/
	$cred_forms_array_name=array(
								0=> 'add-new-free-ad',
								1=>	'add-new-premium-ad',
								2=>	'edit-product',
								3=>	'add-another-premium-ad',
								4=>	'add-new-ad-package'
								);
	
	//Query database for Post ID of these forms given post_name and "cred-form" post_type
	$cred_form_id_db=array();
	
	foreach ($cred_forms_array_name as $k=>$v) { 
	
		$cred_form_id_db[] = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $post_table_name WHERE post_name = %s AND post_type='cred-form'",$v));
	
	}	

	//Get the notification array given ID
	
	$cred_notification_big_array=array();
	foreach ($cred_form_id_db as $key=>$value) { 

		$cred_notification_big_array[$value]=get_post_meta($value,'_cred_notification',TRUE);
	
	}
	
	//Define current user email	
	
	$replacement_email=$current_user->user_email;
		
	//Serialize each one of big notification array
	$serialized_version=array();
	foreach ($cred_notification_big_array as $k_serialize=>$v_serialize) { 
	
		$serialized_version[$k_serialize]=serialize($v_serialize);
	
	}
	
	//Search and replace email with user email
	$replaced_serialized_array=array();
	foreach ($serialized_version as $k_replace=>$v_replace) { 
		
		$replaced_serialized=str_replace('classifieds_website@mailinator.com',$replacement_email,$v_replace);	
		$replaced_serialized_array[$k_replace] = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $replaced_serialized);
	
	}
	
	//Convert back to PHP array
	$unserialized_array=array();
	foreach ($replaced_serialized_array as $k_unserialized=>$v_unserialized) {
	
		$unserialized_array[$k_unserialized]=unserialize($v_unserialized);	
	
	}
	
	//Update post meta
	$updated_value_array=array();
	foreach ($unserialized_array as $k_updated_value=>$v_updated_value) {
		
		update_post_meta($k_updated_value, '_cred_notification', $v_updated_value);
		
	}

}
/**
 * Imports WPML settings and strings.
 * 
 * @global type $wpdb
 * @param type $baseurl
 * @return type 
 */
function wpvdemo_import_wpml($baseurl, $settings) {
    global $wpdb;
    $wpdb->suppress_errors=true;
    $file = $baseurl . '/wpml.xml';
    //Prevent notices in reference sites when it does not have WPML implementation
    $file_headers_wpml = @get_headers($file);
    
    if ((defined('ICL_SITEPRESS_VERSION')) && (strpos($file_headers_wpml[0],'200 OK'))) {

        //Parse remote XML
        $data=wpv_remote_xml_get($file);
        if (!($data)) {
        	return false;
        }
        //Read only if it exist
        $xml = simplexml_load_string($data);
        
        // We can use the Views function to convert to an array
        $data = wpv_admin_import_export_simplexml2array($xml); 
        
		// Fix array indexes
		
        if ((is_array($data)) && (!(empty($data)))) {
        
        //Read array data if defined and not empty
        	
        $data['translation-management']['__custom_fields_readonly_config_prev'] = $data['translation-management']['__custom_fields_readonly_config_prev']['item'];               
        $data['translation-management']['custom_fields_readonly_config'] = $data['translation-management']['custom_fields_readonly_config']['item'];        
        
        //Compatibility with WPML 2.9.3 exporter
        if (isset($data['languages_order'])) {
        	foreach ($data['languages_order'] as $key_import_order=>$value_import_order) {
        		$data['languages_order'][]=$value_import_order;
        		unset($data['languages_order'][$key_import_order]);
        	}
        }

        if (isset($data['st']['theme_localization_domains'])) {
        	foreach ($data['st']['theme_localization_domains'] as $key1_import_order=>$value1_import_order) {
        		$data['st']['theme_localization_domains'][]=$value1_import_order;
        		unset($data['st']['theme_localization_domains'][$key1_import_order]);
        	}
        }
        
        // Set the active langauges.
        global $wpdb;
        foreach($data['wpv_active_languages'] as $code => $active) {
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}icl_languages SET active=%d WHERE code='%s'", $active, $code ));
        }
        
        unset($data['wpv_active_languages']);
        
        update_option('icl_sitepress_settings', $data);

        global $sitepress;
        
        $sitepress->icl_translations_cache->clear();
        $sitepress->icl_locale_cache->clear();
        $sitepress->icl_flag_cache->clear();
        $sitepress->icl_language_name_cache->clear();
        $sitepress->icl_term_taxonomy_cache->clear();
        
        $file = $baseurl . '/wpml_strings.xml.zip';
        
        $data_strings_translation=wpv_remote_xml_get($file);
        if (!($data_strings_translation)) {
        	return false;
        }
                
        $tmp_name = tempnam("tmp", "zip");
        $handle = fopen($tmp_name, 'w');
        fwrite($handle, $data_strings_translation);
        fclose($handle);

        $zip = zip_open($tmp_name);
        if (is_resource($zip)) {
            while (($zip_entry = zip_read($zip)) !== false) {
                if (zip_entry_name($zip_entry) == 'wpml_strings.xml') {
                    $data = @zip_entry_read($zip_entry,
                                    zip_entry_filesize($zip_entry));
                }
            }
            zip_close($zip);
            unlink($tmp_name);
        } else {
            $file = $baseurl . '/wpml_strings.xml';              
            $data=wpv_remote_xml_get($file);
            if (!(data)) {
            	return false;
            }
        }
         
        $xml = simplexml_load_string($data);
        // We can use the Views function to convert to an array
        $data = wpv_admin_import_export_simplexml2array($xml);
        
        foreach ($data['strings']['item'] as $string) {
			$wpdb->insert($wpdb->prefix.'icl_strings', $string);
        }
      
        foreach ($data['translations']['item'] as $string) {

			//Fix warning: mysql_real_escape_string() expects parameter 1 to be string on Views commerce ML 
			//Make sure all inserted values are in string format 
			
			$array_free_string=array();
			
			foreach ($string as $k=>$v) {
				
				if (is_array($v)) {					
					
					$array_free_string[$k]=serialize($v);
					
				} else {
					
					$array_free_string[$k]=$v;
				}
				
			} 
			
			//Now insert
			$wpdb->insert($wpdb->prefix.'icl_string_translations', $array_free_string,array('%d','%d','%s','%d','%s','%d','%s'));

        }
        
        //Special processing for CRED forms context after import
        //IDS will change after import and the context needs to be updated as well
        
        if (defined('CRED_FE_VERSION')) {
        	//CRED plugin enabled, probably has forms
        	wpv_demo_cred_forms_context_update_after_import();
        }

      
        }        
        
    }
    
    //Import WPML locale map settings
    
    $file_locale_map_url = $baseurl . '/wpml_locale_settings.xml';    
    $file_locale_map_headers = @get_headers($file_locale_map_url);
    
    if ((defined('ICL_SITEPRESS_VERSION')) && (strpos($file_locale_map_headers[0],'200 OK'))) {
    	
    	//Parse remote XML
    	$data_wpml_locale=wpv_remote_xml_get($file_locale_map_url);
    	
    	if (!($data_wpml_locale)) {
    		return false;
    	}
    	
    	$xml_locale_settings = simplexml_load_string($data_wpml_locale);
    	$import_data_wpml_locale_map = wpv_admin_import_export_simplexml2array($xml_locale_settings);
    	
    	//Prepare data
    	foreach ($import_data_wpml_locale_map as $key_map=>$values_map) {
    		$import_data_wpml_locale_map[]=$values_map;
    		unset($import_data_wpml_locale_map[$key_map]);
    	}
    	
    	//Loop through the settings and insert to database
    	foreach ($import_data_wpml_locale_map as $map_locale_settings) {
    		$wpdb->insert($wpdb->prefix.'icl_locale_map', $map_locale_settings);
    	}    	
    	
    }
    
    //Import icl translations status
    $file_icl_translations_status_url = $baseurl . '/wpml_translations_status_export.xml';
    $file_icl_translations_status_headers = @get_headers($file_icl_translations_status_url);
    
    if ((defined('ICL_SITEPRESS_VERSION')) && (strpos($file_icl_translations_status_headers[0],'200 OK'))) {
    	 
    	//Parse remote XML
    	$data_wpml_icl_translations_status=wpv_remote_xml_get($file_icl_translations_status_url);
    	 
    	if (!($data_wpml_icl_translations_status)) {
    		return false;
    	}
    	 
    	$xml_icl_translations_status_settings = simplexml_load_string($data_wpml_icl_translations_status);
    	$import_data_wpml_icl_translations_status = wpv_admin_import_export_simplexml2array($xml_icl_translations_status_settings);
    	 
    	//Prepare data
    	foreach ($import_data_wpml_icl_translations_status as $key_status=>$values_status) {
    		$import_data_wpml_icl_translations_status[]=$values_status;
    		unset($import_data_wpml_icl_translations_status[$key_status]);
    	}
    	 
    	//Loop through the settings and insert to database
    	foreach ($import_data_wpml_icl_translations_status as $icl_translations_status_settings_array) {
    		$wpdb->insert($wpdb->prefix.'icl_translation_status', $icl_translations_status_settings_array);
    	}
    	 
    }
        
    return true;
}

/**
 * Imports posts.
 * 
 * @global WPVDemo_Importer $wpvdemo_import
 * @param type $baseurl
 * @param type $site_url 
 */
function wpvdemo_import_posts($settings, $import_settings) {
    add_action('import_post_meta', 'wpvdemo_import_check_meta', 10, 3);
    define('WP_LOAD_IMPORTERS', true);
    require_once WPVDEMO_ABSPATH . '/class.importer.php';
    require_once ABSPATH . 'wp-admin/includes/post.php';
    require_once ABSPATH . 'wp-admin/includes/comment.php';
    remove_action('admin_init', 'wordpress_importer_init', 10, 1);
    global $wpvdemo_import;    
    $wpvdemo_import = new WPVDemo_Importer($settings->site_url);    
    $wpvdemo_import->fetch_attachments = $import_settings->fetch_attachments;    
    $wpvdemo_import->current_site_settings = $settings; 
    $original_plugin_lists=wpvdemo_format_display_plugins($settings->plugins->plugin,false);
    $skip_wpml_during_post_import=wpvdemo_check_if_wpml_will_be_skipped($original_plugin_lists,$settings->download_url,false,true);
    $eligible_sites=array('Toolset Classifieds','Bootstrap Commerce','Bootstrap Real Estate');
    $current_site=(string)$settings->title;
    
    if (in_array($current_site,$eligible_sites)) {
    	if ($skip_wpml_during_post_import) {
    		$file = $settings->download_url . '/posts_no_wpml.xml';
    	} else {
    		$file = $settings->download_url . '/posts.xml';
    	}
	} else {
		
		$file = $settings->download_url . '/posts.xml';
	}   
	   
    $wpvdemo_import->dispatch($file);    
}

/**
 * Fixes imported posts meta if necessary.
 * 
 * @todo NOT NEEDED? Bruce added handle in Importer.
 * 
 * @global type $wpvdemo_import
 * @param type $post_id
 * @param type $key
 * @param type $value 
 */
function wpvdemo_import_check_meta($post_id, $key, $value) {
//    global $wpvdemo_import;
//    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
//    if (strpos($key, 'wpcf-') === 0) {
//        $field_id = str_replace('wpcf-', '', $key);
//        $field = wpcf_admin_fields_get_field($field_id);
//        $blog_base_url = $wpvdemo_import->base_blog_url;
//        if (!empty($field) && in_array($field['type'], array('file', 'image'))
//                && strpos($value, $blog_base_url) === 0) {
//            $new_value = str_replace($blog_base_url, get_bloginfo_rss('url'),
//                    $value);
//            update_post_meta($post_id, $key, $new_value);
//        }
//    }
}

/**
 * Downloads theme.
 * 
 * @param type $url
 * @return boolean 
 */
function wpvdemo_download_theme($url) {
    $themes_dir = dirname(get_stylesheet_directory());
    if (!is_writeable($themes_dir)) {
        wpvdemo_requirements_themes_writeable_error_message();
        return false;
    }
    //EMERSON: Rewrite theme download function
    //Prevent any issues like error in downloading theme
    //Using wp_get_http before caused some issues with WP 3.6
    
    //Define the URL location of the theme zip file in reference site
    $file=(string)$url;
    	
    //Define download path to local
    $new_file = $themes_dir . '/' . basename($url);    
    
    //File headers
    $file_headers_theme_zip = @get_headers($file);
    
    //Delete existing theme if to update it
    $info = pathinfo($new_file);
    $template_name_install=$info['filename'];
    $check_if_theme_exist=$info['dirname'].'/'.$template_name_install;
    
    if (file_exists($check_if_theme_exist)) {  
    	//Make sure we can delete  	 
    	//Make an exception for Multisite
    	if (!(is_multisite())) {    	   	
    	chmod_R($check_if_theme_exist, 0777, 0777);
    	
    	//Now delete    	
    	rmdir_recursive($check_if_theme_exist);
    	}
    }
    
    //Download theme file
    //Don't open if zip does not exist
    
    if(strpos($file_headers_theme_zip[0],'200 OK')) {
    
    	//For multisite like discover-wp, dont re-download theme if already exists
    	//Do this for standalone installation only
    	
    	if ((!(is_multisite())) || (!(file_exists($check_if_theme_exist)))) {
    				
    	//Set context
    	$context=stream_context_create(array('http'=>
    			array(
    					'timeout' => 1200
    			)
    	));
    	$success=file_put_contents($new_file, fopen($file, 'r',false,$context));
    	    	   	
    	if ($success) {
    		//Unzip    		
    		$is_zip = $info['extension'] == 'zip' ? true : false;
    		if ($is_zip) {
    			$zip = new ZipArchive;
    			$res = $zip->open($new_file);
    			if ($res === TRUE) {
    				$zip->extractTo($themes_dir);
    				$zip->close();
    				unlink($new_file);
    				return true;
    			} else {
    				echo __('Unable to open zip file', 'wpcf') . '<br />';
    			}
    		}
    		unlink($new_file);    		
    		
    	}    	
    	}
    	return true;
    
    } else {
    	
    	echo __('Unable to fetch zip file', 'wpvdemo') . '<br />';
    	
    }
    //END   

    return false;
}

/**
 * Imports widgets.
 * 
 * @global type $wpdb
 * @param type $widgets 
 */
function wpvdemo_import_widgets($widgets) {
    global $wpdb;
    $current_sidebars = get_option('sidebars_widgets');
    if (!empty($widgets->sidebars->sidebar)) {
        foreach ($widgets->sidebars->sidebar as $sidebar) {
            $current_sidebars[(string) $sidebar->name] = array();
            foreach ($sidebar->widgets->widget as $widget) {
                $current_sidebars[(string) $sidebar->name][] = (string) $widget;
            }
        }
        update_option('sidebars_widgets', $current_sidebars);
    }
    if (!empty($widgets->widgets->widget)) {
        $update_widgets = array();
        foreach ($widgets->widgets->widget as $widget) {
            $widget_name = 'widget_' . (string) $widget->type;
            $widget_index = (int) $widget->type_index;
            $update_widgets[$widget_name]['_multiwidget'] = (int) $widget->_multiwidget;
            // If widget is views
            if (((string) $widget->type == 'wp_views'
                    || (string) $widget->type == 'wp_views_filter')
                    && !empty($widget->view_post_name)) {
                $view_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'view' AND post_name = %s",
                                (string) $widget->view_post_name));
                if (!empty($view_id)) {
                    $widget->value->view = $view_id;
                }
            }
            if ((string) $widget->type == 'wp_views_filter'
                    && !empty($widget->target_post_name)) {
                $target_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'page' AND post_name = %s",
                                (string) $widget->target_post_name));
                if (!empty($target_id)) {
                    $widget->value->target_id = $target_id;
                }
            }
            foreach ($widget->value as $value) {
                foreach ($value as $value_title => $value_data) {
                    $update_widgets[$widget_name][$widget_index][(string) $value_title] = (string) $value_data;
                }
            }
        }
        foreach ($update_widgets as $widget_title => $widget_data) {
            update_option($widget_title, $widget_data);
        }
    }
}

/**
 * Checks safe_mode.
 * 
 * @return boolean 
 */
function wpvdemo_is_safe_mode() {
    $my_boolean = ini_get('safe_mode');
    if ((int) $my_boolean > 0) {
        $my_boolean = true;
    } else {
        $my_lowered_boolean = strtolower($my_boolean);
        if ($my_lowered_boolean === "true" || $my_lowered_boolean === "on" || $my_lowered_boolean === "yes") {
            $my_boolean = true;
        } else {
            $my_boolean = false;
        }
    }
    return $my_boolean;
}

function bootmag_xml2array($xml)
{
	$arr = array();

	foreach ($xml as $element)
	{
		$tag = $element->getName();
		$e = get_object_vars($element);
		if (!empty($e))
		{
			$arr[$tag] = $element instanceof SimpleXMLElement ? bootmag_xml2array($element) : $e;
		}
		else
		{
			$arr[$tag] = trim($element);
		}
	}

	return $arr;
}

function update_options_classified_widgets() {

//Define widget variables for import

	$import_widget_categories = array('_multiwidget' => '1');
	$import_widget_search = array('_multiwidget' => '1');
	$import_widget_recent_posts = array('_multiwidget' => '1');
	$import_widget_recent_comments = array('_multiwidget' => '1');
	$import_widget_archives= array('_multiwidget' => '1');
	$import_widget_meta= array('_multiwidget' => '1');
	$import_sidebars_widgets= array('wp_inactive_widgets' => array(),
			                        'header_sidebar' => array(),
									'center_foot_sidebar' => array(),
			                        'foot_sidebar_1' => array(),
			                        'array_version' => '3'			
	 								);
	
//Update options
	update_option( 'widget_categories', $import_widget_categories );
	update_option( 'widget_search', $import_widget_search );
	update_option( 'widget_recent-posts', $import_widget_recent_posts );
	update_option( 'widget_recent-comments', $import_widget_recent_comments );
	update_option( 'widget_archives', $import_widget_archives );
	update_option( 'widget_meta', $import_widget_meta );
	update_option( 'sidebars_widgets', $import_sidebars_widgets );	
}

function wpbootstrap_default_grid_xml_import() {

	$layout_xml_path=WPVDEMO_ABSPATH.'/includes/layout-grid.xml';
	$xml2array_bootstrap_path=WPVDEMO_ABSPATH.'/includes/XML2Array.class.php';
	
	if ((file_exists($layout_xml_path)) && (file_exists($xml2array_bootstrap_path))) {

		$bootstrap_xml_grid_list_exist=get_option('bootstrap_xml_grid_list');
		if (!(class_exists('XML2Array'))) {
			require_once $xml2array_bootstrap_path;
		}
		if (empty($bootstrap_xml_grid_list_exist)) {
			$file = file_get_contents($layout_xml_path);
			if ($file == true) {
				$grid_array = XML2Array::createArray($file);
				add_option('bootstrap_xml_grid_list', $grid_array["GridList"], '', 'yes');
			}
		}
	}
}

function wpdemo_localhost_reference_site($r) {
	
	$r['reject_unsafe_urls']=false;
	
	return $r;
}
function fix_image_urls_bootstrap_vanilla_standalone($site_url_imported_vanilla) {
	global $wpdb;
	
	$problem_image_path=$site_url_imported_vanilla.'/files';
	$uploads_constants_of_this_site=wp_upload_dir();
	$correct_uploads_url_image_path=$uploads_constants_of_this_site['baseurl'];
	
	//search and replace
	
	$success_replace= $wpdb->query(
						$wpdb->prepare(
					"
					UPDATE $wpdb->posts
					SET post_content = replace(post_content,'%s','%s')					
					",
					$problem_image_path, $correct_uploads_url_image_path
			)
	);	
}

/*Recursive CHMOD for theme directory and files*/
function chmod_R($path, $filemode, $dirmode) {
	if (is_dir($path) ) {
		if (!chmod($path, $dirmode)) {
			$dirmode_str=decoct($dirmode);
			print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
			print "  `-> the directory '$path' will be skipped from recursive chmod\n";
			return;
		}
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false) {
		if($file != '.' && $file != '..') {  // skip self and parent pointing directories
		$fullpath = $path.'/'.$file;
		chmod_R($fullpath, $filemode,$dirmode);
		}
		}
		closedir($dh);
		} else {
		if (is_link($path)) {
		print "link '$path' is skipped\n";
		return;
		}
		if (!chmod($path, $filemode)) {
		$filemode_str=decoct($filemode);
		print "Failed applying filemode '$filemode_str' on file '$path'\n";
		return;
		}
		}
}

/*Recursive delete for theme directory and files*/
function rmdir_recursive($dir) {
	foreach(scandir($dir) as $file) {
		if ('.' === $file || '..' === $file) continue;
		if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
		else unlink("$dir/$file");
	}
	rmdir($dir);
}

/*Setup WooCommerce pages for BootCommerce site*/
function setup_woocommerce_setting_pages_bootcommerce() {
	global $wpdb;
	$posttable=$wpdb->posts;
	
	/*Retrieve page IDs of different WooCommerce setting pages*/
	
	//Get cart page ID
	$cart_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='cart' AND post_type='page'");
	
	//Get checkout page ID
	$checkout_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='checkout' AND post_type='page'");
	
	//Get pay page ID	
	$pay_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='pay' AND post_type='page'");
	
	//Get order received page ID
	$orderreceived_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='order-received' AND post_type='page'");
	
	//My account page ID
	$myaccount_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='my-account' AND post_type='page'");
	
	//Edit address page ID
	$editaddress_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='edit-address' AND post_type='page'");	
	
	//Edit view-order page ID
	$vieworder_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='view-order' AND post_type='page'");
		
	//Edit address page ID
	$changepassword_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='change-password' AND post_type='page'");
	
	//Edit address page ID
	$lostpassword_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='lost-password' AND post_type='page'");		
	
	//Get shop page ID
	$shop_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='shop' AND post_type='page'");
	
	/*Update WooCommerce setting options with these ID*/
	
	$success_cart_page_id=update_option('woocommerce_cart_page_id',$cart_page_id);
	$success_checkout_page_id=update_option('woocommerce_checkout_page_id',$checkout_page_id);
	$success_pay_page_id=update_option('woocommerce_pay_page_id',$pay_page_id);
	$success_orderreceived_page_id=update_option('woocommerce_thanks_page_id',$orderreceived_page_id);
	$success_myaccount_page_id=update_option('woocommerce_myaccount_page_id',$myaccount_page_id);
	$success_editaddress_page_id=update_option('woocommerce_edit_address_page_id',$editaddress_page_id);
	$success_vieworder_page_id=update_option('woocommerce_view_order_page_id',$vieworder_page_id);
	$success_changepassword_page_id=update_option('woocommerce_change_password_page_id',$changepassword_page_id);
	$success_lostpassword_page_id=update_option('woocommerce_lost_password_page_id',$lostpassword_page_id);
	$success_shop_page_id=update_option('woocommerce_shop_page_id',$shop_page_id);
    
    /*Fix issues on Featured products with variation*/
    
    //Get term "featured"
    $terms_featured=get_term_by('slug', 'featured', 'product_cat',ARRAY_A);
    
    //Get post id of the "slider" view
    $slider_view_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='slider' AND post_type='view'");
    
    if ((isset($slider_view_id)) && (isset($terms_featured))) {
    	//Get view settings for this view
    	$slider_view_settings=get_post_meta( $slider_view_id, '_wpv_settings',TRUE);
    	
    	//Get "featured" term id
    	$featured_term_id=$terms_featured['term_id'];
    	
    	//Define featured taxonomy view for insertion
     	
    	$slider_view_settings['tax_input_product_cat']= array(0=>$featured_term_id); 
    	
    	//Update back
    	$success_updating_view_slider=update_post_meta($slider_view_id,'_wpv_settings',$slider_view_settings);  	

    }
    
    /*Fix issues on WooCommerce color attributes not imported*/    

    //Define WooCommerce product attributes
    $wc_attribute = array(
    		'attribute_label'   => 'Color',
    		'attribute_name'    => 'color',
    		'attribute_type'    => 'select',
    		'attribute_orderby' => 'menu_order'
    );
    
    $success_wc_attribute_insert=$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $wc_attribute );
    
    //Clear transient
    delete_option('_transient_wc_attribute_taxonomies');
    
    //Enable Display 'Languages' as the widget title
    $bootstrap_commerce_wpml_settings=get_option('icl_sitepress_settings');
    $bootstrap_commerce_wpml_settings['icl_widget_title_show']=1;    
    $success_updating_lang_widget_title=update_option( 'icl_sitepress_settings',$bootstrap_commerce_wpml_settings);    
}

/*WordPress default importer won't allow having two post_names in the same post type*/
/*But WPML and WCML allows this in the reference site having two exact post_names in same post type but different translation*/
/*After import we need to adjust the post name back to its original so the product comparison feature will work */

function bootcommerce_fix_productcomparison_afterimport() {

	global $wpdb;
	$posttable=$wpdb->posts;
	$correct_slug='product-comparison';
	
	//Get ID for original product comparison post
	$product_comparison_page_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_title='Product Comparison' AND post_type='page'");
	
	//Get the translated ID in Spanish
	$translated_id = icl_object_id($product_comparison_page_id, 'page', false, 'es');
	
	//wp_update_post cannot be used here since it won't allow inserting two post_names in same post type
	
	$success_updating_postname= $wpdb->query( $wpdb->prepare("UPDATE $posttable SET post_name = %s WHERE ID = %d", $correct_slug, $translated_id) );
		
	if ($success_updating_postname) {
		return TRUE;
	} else {
		return FALSE;
	}
	
}

/*Function to update CRED string translation context to the correct form ID after import*/
function wpv_demo_cred_forms_context_update_after_import() {

	//CRED plugin is activated, check if forms exists

	global $wpdb;

	$post_table_name = $wpdb->prefix.'posts';
	$icl_strings_table_name=$wpdb->prefix.'icl_strings';

	//Retrieve updated form IDs and the post title from the post table
	$results = $wpdb->get_results("SELECT ID,post_title FROM $post_table_name WHERE post_type='cred-form'", ARRAY_A);

	if ($results) {
		//Forms exists, prepare data
		$updated_cred_form_id_array=array();

		foreach ($results as $key=>$inner_array) {
			$updated_cred_form_id_array[$inner_array['post_title']]=$inner_array['ID'];
		}

		//Loop through each CRED forms array and check if context exists on translation table then update
		foreach ($updated_cred_form_id_array as $k=>$v) {

			//Formulate context to search
			$context_to_search='cred-form-'.$k.'-';
			$like="%$context_to_search%";
			$sql_query = "SELECT DISTINCT context FROM $icl_strings_table_name WHERE context LIKE %s";

			//Get results from icl strings table
			$string_results_query = $wpdb->get_results($wpdb->prepare($sql_query, $like),ARRAY_A);
			if ($string_results_query) {

				//Queried context exists
				$old_context_retrieved=$string_results_query[0]['context'];

				//Formulate new context
				$new_context_for_updating=$context_to_search.$v;
				 
				//Update
				$wpdb->query( $wpdb->prepare( "UPDATE $icl_strings_table_name SET context=%s WHERE context=%s",$new_context_for_updating, $old_context_retrieved ));
			}

		}

	}
}

/* Function to remove the option just_reactivated when importing multilingual sites with WPML 3.1.4+ to prevent fatal error*/
function wpv_demo_remove_wpml_recently_activated_option($plugin,$network_activated) {

	global $sitepress;
	if (is_object($sitepress)) {

		//Get activated WPML plugin folder
		$wpml_plugin_folder=ICL_PLUGIN_FOLDER;

		//Get plugin folder name of passed $plugin variable in the hook
		$activated_wpml_plugin_folder=dirname($plugin);
			
		if ($activated_wpml_plugin_folder==$wpml_plugin_folder) {

			$iclsettings = get_option('icl_sitepress_settings');
			if ($iclsettings) {
				$iclsettings['just_reactivated'] = 0;
				update_option('icl_sitepress_settings',$iclsettings);
			}
		}
	}
}

//Refresh all CRED Forms after import
//With WPML 3.1.4 implementation some IDS were changed and needs refreshing after import

function wpv_refresh_cred_form_after_import($site_shortname) {

	$applicable_sites=array('cl');
	if (in_array($site_shortname,$applicable_sites)) {
		
		if (defined('ICL_SITEPRESS_VERSION')) {
			//Run on WPML version only
			$original_translation_status=wpv_demo_original_translation_status_before_refresh();
		}
		
		$wpv_import_is_done=get_option('wpv_import_is_done');
		$wpv_cred_form_refreshed=get_option('wpv_cred_form_is_refreshed');

		if ((defined('CRED_FE_VERSION')) && ($wpv_import_is_done=='yes') && (!($wpv_cred_form_refreshed))) {
			$cred_loader_forms=CRED_Loader::get('MODEL/Forms');
				
			$forms=$cred_loader_forms->getAllForms();
	
			foreach ($forms as $form)
			{
				$data=array(
						'post'=>$form,
						'message'=>'',
						'messages'=>array(),
						'notification'=>(object)array(
								'enable'=>0,
								'notifications'=>array()
						));

				$fields=$cred_loader_forms->getFormCustomFields($form->ID,array('form_settings','notification','extra'));
				$settings=isset($fields['form_settings'])?$fields['form_settings']:false;
				$notification=isset($fields['notification'])?$fields['notification']:false;
				$extra=isset($fields['extra'])?$fields['extra']:false;

				// register settings
				if ($settings && isset($settings->form['action_message']))
					$data['message'] = $settings->form['action_message'];
	
				// register Notification Data also
				if ($notification)
				{
					$data['notification']=$notification;
				}

				// register extra fields
				if ($extra && isset($extra->messages))
				{
					// register messages also
					$data['messages']=$extra->messages;
				}

				$cred_loader_form_translator = CRED_Loader::get('CLASS/Form_Translator');
				$data=$cred_loader_form_translator->processForm($data);
				$post_id=$form->ID;
				$cred_loader_forms->updateFormCustomField($post_id, 'notification', $data['notification']);
				$success_refreshing=update_option('wpv_cred_form_is_refreshed','yes');				
			}
			
			if (defined('ICL_SITEPRESS_VERSION')) {
				//Run on WPML version only
				//Restore original translation status after form refresh
				wpv_demo_restore_original_translation_status($original_translation_status);			
			}
		}
	}
}

//Retrieve original translation status before refresh
function wpv_demo_original_translation_status_before_refresh() {

	global $wpdb;

	$post_table_name = $wpdb->prefix.'posts';
	$icl_strings_table_name=$wpdb->prefix.'icl_strings';

	//Retrieve updated form IDs and the post title from the post table
	$results = $wpdb->get_results("SELECT ID,post_title FROM $post_table_name WHERE post_type='cred-form'", ARRAY_A);

	if ($results) {
		//Forms exists, prepare data
		$updated_cred_form_id_array=array();

		foreach ($results as $key=>$inner_array) {
			$updated_cred_form_id_array[$inner_array['post_title']]=$inner_array['ID'];
		}

		//Loop through each CRED forms array and retrieve original translation status
		$original_translation_status_array=array();
		foreach ($updated_cred_form_id_array as $k=>$v) {

			//Formulate context to search
			$context_to_search='cred-form-'.$k.'-';
			$like="%$context_to_search%";
			$sql_query = "SELECT id,status FROM $icl_strings_table_name WHERE context LIKE %s";

			//Get results from icl strings table
			$string_results_query = $wpdb->get_results($wpdb->prepare($sql_query, $like),ARRAY_A);
			if (!(empty($string_results_query))) {
				
				foreach ($string_results_query as $k=>$orig_array_info) {
					
					$string_trans_id=$orig_array_info['id'];
					$string_orig_status=$orig_array_info['status'];
					
					$original_translation_status_array[$string_trans_id]=$string_orig_status;
				}

			}

		}
		return $original_translation_status_array;

	}
}
//Restore original translation status
function wpv_demo_restore_original_translation_status($original_translation_status) {
		
	if ((is_array($original_translation_status)) && (!(empty($original_translation_status)))) {
		
		global $wpdb;
		$icl_strings_table_name=$wpdb->prefix.'icl_strings';	
		$icl_string_translations_table_name=$wpdb->prefix.'icl_string_translations';
			
		//Loop and update
		foreach ($original_translation_status as $id=>$translation_status_value) {
			$wpdb->query( $wpdb->prepare( "UPDATE $icl_strings_table_name SET status=%d WHERE id=%d",$translation_status_value, $id ));
			$wpdb->query( $wpdb->prepare( "UPDATE $icl_string_translations_table_name SET status=%d WHERE string_id=%d",$translation_status_value, $id ));
		}

	}
}
//Special activation of Types and Views plugin for modules import
function wpvdemo_activate_types_views_modules_import($site_imported) {
	
	$site_imported=(string)$site_imported;
	
	//Inclusive sites are sites with modules to be imported.
	//Activate Types and Views so overall modules functionality will be rendered correctly before import
	$inclusive_sites=array('bre','cl','bm','bc','ws');
		
	//Don't do this in discover-wp multisite and if not an inclusive site
	if ((!(is_multisite())) && (in_array($site_imported,$inclusive_sites))) {		
		
		//Get all plugins
		$all_plugins_found=get_plugins();
		$compatible_types_views_found=wpvdemo_get_types_views_pluginlist($all_plugins_found);		
		
		if ((is_array($compatible_types_views_found)) && (!(empty($compatible_types_views_found)))) {
			
			//Compatible version of Types and Views is found in plugins directory			
			$executing_dir = plugin_dir_path( __FILE__ );
			$plugins_directory= dirname(dirname($executing_dir));
			
			foreach ($compatible_types_views_found as $plugin_path=>$plugin_name) {			
				
				$complete_plugin_path=$plugins_directory.'/'.$plugin_path;
				$success = activate_plugin($plugin_path, $redirect = '', $network_wide = false, $silent = true);				
			
			}
			
		} 		
	} 	
	
}

//Sort Types and Views plugin from the plugin list
function wpvdemo_get_types_views_pluginlist($all_plugins) {
	
	$compatible_types_views=array();
	
	//Get Types and Views embedded version running
	if ((defined('WPCF_VERSION')) && (defined('WPV_VERSION'))) {
		$types_version_embedded=WPCF_VERSION;
		$views_version_embedded=WPV_VERSION;
         if ((is_array($all_plugins)) && (!(empty($all_plugins)))) {
         	
         	$compatible_types_views=array();
         	foreach ($all_plugins as $plugin_path=>$plugin_details) {
         		
         		$plugin_basename=basename($plugin_path);
         		
         		//This is a Views or a Types plugin
         		//Check if version is compatible
         		if ($plugin_basename =='wpcf.php') {
         				
         			$version_in_plugins_directory=$plugin_details['Version'];
         			if ($version_in_plugins_directory==$types_version_embedded) {
         				//Compatible Types plugin version
         				$compatible_types_views[$plugin_path]=$plugin_details['Name'];
         			}         				
         		} elseif ($plugin_basename =='wp-views.php') {
         			
         			$version_in_plugins_directory=$plugin_details['Version'];
         			if ($version_in_plugins_directory==$views_version_embedded) {
         				//Compatible Views plugin version
         				$compatible_types_views[$plugin_path]=$plugin_details['Name'];
         			}        				
         		}         		
         	}      	
         	
         }
		
	}
	return $compatible_types_views;
	
}
//Bootstrap Estate query settings for Views using property taxonomies
function bootstrap_estate_fix_properties_taxonomiesview_settings() {
	
	global $wpdb;
	$posttable=$wpdb->prefix."posts";
	
	//Get ID of Sidebar – Sponsored Property View
	$sponsored_property_view_id = $wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='sidebar-sponsored-property' AND post_type='view'");
	
	//Get ID of featured-slider View
	$featured_slider_view_id=$wpdb->get_var("SELECT ID FROM $posttable WHERE post_name='featured-slider' AND post_type='view'");
	
	//Get term IDs
	$sidebar_term=get_term_by( 'slug', 'sidebar', 'property-categories',ARRAY_A);
	
	$sidebar_term_id='';
	$featured_term_id='';
	
	if (isset($sidebar_term['term_id'])) {
		$sidebar_term_id= $sidebar_term['term_id'];
	}
	
	$featured_term=get_term_by( 'slug', 'featured', 'property-categories',ARRAY_A);
	
	if (isset($featured_term['term_id'])) {
		$featured_term_id= $featured_term['term_id'];
	}
		
	//Sidebar sponsored view fix
	if ((isset($sponsored_property_view_id)) && (!(empty($featured_term_id))) && (!(empty($sidebar_term_id)))) {
		$view_setting_sponsored=get_post_meta($sponsored_property_view_id,'_wpv_settings',TRUE);
		if (!(isset($view_setting_sponsored['taxonomy-property-categories-attribute-url-format']))) {
			$view_setting_sponsored['taxonomy-property-categories-attribute-url-format']=array('0'=>'slug');
			$success_updating_attribute_format=update_post_meta($sponsored_property_view_id, '_wpv_settings', $view_setting_sponsored);
		}
		if (!(isset($view_setting_sponsored['tax_input_property-categories']))) {
			$view_setting_sponsored['tax_input_property-categories']=array('0'=>$sidebar_term_id);
			$success_updating_term_id=update_post_meta($sponsored_property_view_id, '_wpv_settings', $view_setting_sponsored);
		}			
	}
	//Featured view fix
	if ((isset($featured_slider_view_id)) && (!(empty($featured_term_id))) && (!(empty($sidebar_term_id)))) {
		$view_setting_featured=get_post_meta($featured_slider_view_id,'_wpv_settings',TRUE);
		if (!(isset($view_setting_featured['taxonomy-property-categories-attribute-url-format']))) {			
			$view_setting_featured['taxonomy-property-categories-attribute-url-format']=array('0'=>'slug');
			$success_updating_featured_att_format=update_post_meta($featured_slider_view_id, '_wpv_settings', $view_setting_featured);
		}
		if (!(isset($view_setting_featured['tax_input_property-categories']))) {
			$view_setting_featured['tax_input_property-categories']=array('0'=>$featured_term_id);
			$success_updating_term_id_featured=update_post_meta($featured_slider_view_id, '_wpv_settings', $view_setting_featured);
		}
	}	
}