<?php 
class Class_WooCommerce_Views {
	function __construct() {

		//Hook on __construct()		
		add_action('plugins_loaded', array(&$this,'wcviews_init'),2);		
		define('WPV_WOOCOMERCE_VIEWS_SHORTCODE', 'wpv-wooaddcart');
		define('WPV_WOOCOMERCEBOX_VIEWS_SHORTCODE', 'wpv-wooaddcartbox');
		add_action( 'admin_menu', array(&$this,'woocommerce_views_add_this_menupage'));
		add_action('system_cron_execution_hook',array(&$this,'ajax_process_wc_views_batchprocessing'));		
		add_action('wp_enqueue_scripts', array(&$this,'woocommerce_views_scripts_method'));		
		add_action('wp_ajax_wc_views_ajax_response_admin',array(&$this,'ajax_process_wc_views_batchprocessing'));	
		
		//Ajax hooks for Shortcode GUI
		add_action('wp_ajax_wcviewsgui_wpv_woo_buy_or_select', 'wcviewsgui_wpv_woo_buy_or_select_func');		
		add_action('wp_ajax_wcviewsgui_wpv_woo_buy_options', 'wcviewsgui_wpv_woo_buy_options_func');		
		add_action('wp_ajax_wcviewsgui_wpv_woo_product_image','wcviewsgui_wpv_woo_product_image_func');
		
		add_action('admin_enqueue_scripts', array(&$this,'woocommerce_views_scripts_method_backend'));
		add_action('init',array(&$this,'prefix_setup_schedule'));	
		add_action('admin_init',array(&$this,'reset_all_wc_admin_screen'));
		
		//Old shortcodes		
		add_shortcode('wpv-wooaddcart', array(&$this,'wpv_woo_add_to_cart'));
		add_shortcode('wpv-wooaddcartbox', array(&$this,'wpv_woo_add_to_cart_box'));
		add_shortcode('wpv-wooremovecart', array(&$this,'wpv_woo_remove_from_cart'));
		add_shortcode('wpv-woo-carturl', array(&$this,'wpv_woo_cart_url'));
						
		//New shortcodes
		
		add_shortcode('wpv-woo-buy-or-select', array(&$this,'wpv_woo_buy_or_select_func'));		
		add_shortcode('wpv-woo-product-price', array(&$this,'wpv_woo_product_price_func'));	
		add_shortcode('wpv-woo-product-image', array(&$this,'wpv_woo_product_image_func'));
		add_shortcode('wpv-woo-buy-options', array(&$this,'wpv_woo_buy_options_func'));
		add_shortcode('wpv-add-to-cart-message', array(&$this,'wpv_show_add_cart_success_func'));
		add_shortcode('wpv-woo-display-tabs',array(&$this,'wpv_woo_display_tabs_func'));
		
		//Template loading
		
		add_action( 'template_redirect', array(&$this,'woocommerce_views_activate_template_redirect' ));
		
		//Save post meta values when saving the products or updating
		
		add_action('save_post',array(&$this,'compute_postmeta_of_products_woocommerce_views'));
		
		//Finally hook to Views
		
		add_filter('editor_addon_menus_wpv-views', array(&$this,'wpv_woo_add_shortcode_in_views_popup'));
		
		//Register the computed values as Types fields		
		add_action('wp_loaded',array(&$this,'wpv_register_typesfields_func'));
	}

/* Master init */
function wcviews_init(){
	
    add_action('wp_loaded',array(&$this,'run_wp_loaded_check_required_plugins'));
	    	
	if(get_option('dismiss_wcviews_notice') == 'no' || !get_option('dismiss_wcviews_notice')){
		add_action('admin_notices', array(&$this,'wcviews_help_admin_notice'));
	}
		
	//add_filter('wpv_add_media_buttons', 'add_media_button');
	add_action('init', array(&$this,'additional_css_js'));
	
	//Remove this hook so users can customize add to cart messages in main shop pages, etc.
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_show_messages', 10 );
	
	//Hook on reducing stock level in WooCommerce
	add_action('woocommerce_reduce_order_stock',array(&$this,'ajax_process_wc_views_batchprocessing'),10,1);
}

function run_wp_loaded_check_required_plugins() {
	
	$this->run_woocommerce_views_required_plugins();
	
}

function check_missing_plugins_for_woocommerce_views() {
	
	$missing_required_plugin=array();
	
	//Check plugin requirements
	if (!class_exists('woocommerce')){
	
		//WooCommerce plugin is not activated
		$missing_required_plugin[]='woocommerce';
	}
	if (!(defined('WPV_VERSION'))){
	
		//Views plugin is not activated
		$missing_required_plugin[]='views';
	}
	if (!(defined('WPCF_VERSION'))){
	
		//Types plugin is not activated
		$missing_required_plugin[]='types';
	}

	return $missing_required_plugin;
	
}

function missing_plugins_wcviews_check() {
	
 global $custom_missing_required_plugin;
	
 ?>
	<div class="message wcviews_plugin_error error">
	<p><?php _e('The following plugins are required for WooCommerce Views to run properly:','woocommerce_views');?></p>
	<ol>
	<?php 
		  foreach ($custom_missing_required_plugin as $k=>$v) {
	?>
	<li>
	<?php
	if ($v=='views') {
	?>
	<a target="_blank" href="http://wp-types.com/home/views-create-elegant-displays-for-your-content/">Views</a>
     <?php
    } elseif ($v=='types') {
    ?>
   <a target="_blank" href="http://wordpress.org/plugins/types/">Types</a>
    <?php
	} elseif ($v=='woocommerce') {
	?>
	<a target="_blank" href="http://wordpress.org/plugins/woocommerce/">WooCommerce</a>
	 <?php
	}
	?>
	</li>
	<?php
	 }
	 ?>
	 </ol>
	</div>
 <?php						
			
}

function run_woocommerce_views_required_plugins() {
	global $custom_missing_required_plugin;
    $custom_missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();
	
	//Check if there is a missing plugin required
	if (!(empty($custom_missing_required_plugin))) {
	
		//Some required plugin is missing, pass
		add_action('admin_notices',array(&$this,'missing_plugins_wcviews_check'));

		return false;
  }	
}

/* Enqueue script on front end */
function woocommerce_views_scripts_method() {
    global $post,$woocommerce;
    $lightbox_en_woocommerce= get_option( 'woocommerce_enable_lightbox' ) == 'yes' ? true : false;
    $suffix_woocommerce	= defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    $woocommerce_plugin_url=$woocommerce->plugin_url();
    $woocommerce_version=$woocommerce->version;
    
    //Enqueue prettyPhoto   
    if ($lightbox_en_woocommerce)  {  
    	wp_enqueue_script( 'prettyPhoto', $woocommerce_plugin_url . '/assets/js/prettyPhoto/jquery.prettyPhoto' . $suffix_woocommerce . '.js', array( 'jquery' ), '3.1.5', true );
    	wp_enqueue_script( 'prettyPhoto-init', $woocommerce_plugin_url . '/assets/js/prettyPhoto/jquery.prettyPhoto.init' . $suffix_woocommerce . '.js', array( 'jquery' ), $woocommerce->version, true );
    	wp_enqueue_style( 'woocommerce_prettyPhoto_css', $woocommerce_plugin_url . '/assets/css/prettyPhoto.css' );   
    }
    
	wp_enqueue_script('woocommerce_views_custom_script', plugins_url( 'res/js/woocommerce_custom_js_frontend.js', __FILE__ ), array( 'jquery' ));

}

/* Enqueue script on backend */
function woocommerce_views_scripts_method_backend() {
	
	$screen_output_wc_views= get_current_screen();
	$screen_output_id= $screen_output_wc_views->id;
	if ($screen_output_id=='toplevel_page_wpv_wc_views') {
		//Enqueue only on WC Views admin screen	
		wp_enqueue_script('woocommerce_views_custom_script_backend', plugins_url( 'res/js/woocommerce_custom_js_backend.js', __FILE__ ), array( 'jquery' ));
		
		wp_localize_script('woocommerce_views_custom_script_backend', 'the_ajax_script_wc_views',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'wc_views_ajax_response_admin_nonce'=> wp_create_nonce('wc_views_ajax_response_admin'),
			'wc_views_ajax_ajax_loader_gif' =>plugins_url( 'res/img/ajax-loader.gif', __FILE__),
			'wc_views_last_run_translatable_text' => __('Calculated Product fields were last updated: ','woocommerce_views'),
			)
		);	
	
	}
	
	//Juan: Check the Views version so we load the dialogs.css and utils.js script files that were moved in 1.3.1 -> 1.4 release and will not be loaded by default in post.php and post-new.php pages
	$screen_output_base = $screen_output_wc_views->base;
        if( defined( 'WPV_VERSION' ) && defined( 'WPV_URL' ) && $screen_output_base == 'post') {
		if ( version_compare( WPV_VERSION, '1.4' ) < 0 ) {
			
			wp_deregister_script( 'toolset-colorbox' );
			wp_register_script( 'toolset-colorbox' , WPV_URL . '/res/js/redesign/lib/jquery.colorbox-min.js', array('jquery'), WPV_VERSION);
			wp_deregister_script( 'views-select2-script' );
			wp_register_script( 'views-select2-script' , WPV_URL . '/res/js/redesign/lib/select2/select2.min.js', array('jquery'), WPV_VERSION);
			wp_deregister_script( 'views-utils-script' );
			wp_register_script( 'views-utils-script' , WPV_URL . '/res/js/redesign/utils.js', array('jquery','toolset-colorbox', 'views-select2-script'), WPV_VERSION);
			if ( !wp_script_is( 'views-utils-script' ) ) {
				wp_enqueue_script( 'views-utils-script');
				$help_box_translations = array(
				'wpv_dont_show_it_again' => __("Got it! Don't show this message again", 'wpv-views'),
				'wpv_close' => __("Close", 'wpv-views')
				);
				wp_localize_script( 'views-utils-script', 'wpv_help_box_texts', $help_box_translations );
			}
			
			wp_deregister_style( 'views-dialogs-css' );
			wp_register_style( 'views-dialogs-css', WPV_URL . '/res/css/dialogs.css', array(), WPV_VERSION );
			if ( !wp_style_is( 'views-dialogs-css' ) ) {
				wp_enqueue_style('views-dialogs-css');
			}
			
		}
        }
}

/*Register the computed values as Types fields*/
function wpv_register_typesfields_func() {
	
	//Define WC Views canonical custom field array
	$wc_views_custom_fields_array=array('views_woo_price','views_woo_on_sale','views_woo_in_stock');
	
	//Preparation to Types control
	$wc_views_fields_array=array();
	$string_wpcf_not_controlled=md5( 'wpcf_not_controlled');
	foreach ($wc_views_custom_fields_array as $key=>$value) {
		$wc_views_fields_array[]=$value.'_'.$string_wpcf_not_controlled;		
	}
	
   if (defined('WPCF_INC_ABSPATH')) {
   	   //First, check if WC Views Types Group field does not exist
   	   if (!($this->check_if_types_group_exist('WooCommerce Views filter fields'))) {
       	require_once WPCF_INC_ABSPATH . '/fields.php';
       	//Part 1: Assign to Types Control
       	//Get Fields
       	$fields = wpcf_admin_fields_get_fields(false, true);
       	$fields_bulk = wpcf_types_cf_under_control('add',array('fields' => $wc_views_fields_array));
       
       	foreach ($fields_bulk as $field_id) {
	
        	  if (isset($fields[$field_id])) {
        	        $fields[$field_id]['data']['disabled'] = 0;
        	  }
	
       	}
       	//Save fields
       	wpcf_admin_fields_save_fields($fields);    

       	//Retrieve updated fields
       	$fields = wpcf_admin_fields_get_fields(false, false);
       
       	//Assign names
       	foreach ($fields as $key=>$value) {
       		  if ($key=='views_woo_price') {
       		  	$fields['views_woo_price']['name']='WooCommerce Product Price';
       		  } elseif ($key=='views_woo_on_sale') {
       		  	$fields['views_woo_on_sale']['name']='Product On Sale Status';
       		  } elseif ($key=='views_woo_in_stock') {
       		  	$fields['views_woo_in_stock']['name']='Product In Stock Status';
       	 	 }       	
       	}
       
       	//Save fields
       	wpcf_admin_fields_save_fields($fields);
       	
       	//Define group
       	$group=array(
       	'name' => 'WooCommerce Views filter fields',
       	'description' => '',
       	'filters_association' => 'any',
       	'conditional_display' => array('relation'=>'AND','custom'=>''),
       	'preview' =>	'edit_mode',
       	'admin_html_preview' =>'',
       	'admin_styles' =>'',
       	'slug' => 'wc-views-types-groups-fields');
       
       	//Save group
       	$group_id=wpcf_admin_fields_save_group($group);
       
       	//Save group fields       
       	wpcf_admin_fields_save_group_fields($group_id,$fields_bulk);  
   		}   
       
   }
}

function check_if_types_group_exist( $title ) {
	global $wpdb;
	$return = $wpdb->get_row( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $title . "' && post_status = 'publish' && post_type = 'wp-types-group' ", 'ARRAY_N' );
	if( empty( $return ) ) {
		return false;
	} else {
		return true;
	}
}

/* Optional saved message */
function woocommerce_views_settings_saved_message() {
	?>
    <div class="updated">
        <p><?php _e( 'Updated!', 'woocommerce_views' ); ?></p>
    </div>
    <?php
}

/* Add menu page and callback to admin screen */
function woocommerce_views_add_this_menupage() {
	    
	$missing_required_plugin=$this->check_missing_plugins_for_woocommerce_views();
	
    //Add admin screen only when all required plugins are activated
    if (empty($missing_required_plugin)) {
		$hook=add_menu_page( 'WooCommerce Views', 'WooCommerce Views', 'manage_options', 'wpv_wc_views', array(&$this,'woocommerce_views_admin_screen'));
	}		
	 	
}

/* Setup WP Cron processing for custom field batch updates */
function prefix_setup_schedule() {
	
	//Retrieved current batch processing settings	
	$batch_processing_settings_saved=get_option('woocommerce_views_batch_processing_settings');
	$settings_set=$batch_processing_settings_saved['woocommerce_views_batchprocessing_settings'];
	$intervals_set=$batch_processing_settings_saved['batch_processing_intervals_woocommerce_views'];

	//Retrieved available schedules and formulate cron hook name dynamically
	$available_cron_schedules=wp_get_schedules();
	$cron_hookname=array();
	foreach ($available_cron_schedules as $key_cron=>$value_cron) {
		$cron_hookname['prefix_'.trim($key_cron).'_event']=$key_cron;
	}
	
	//Run this function only if using wordpress cron
	if ($settings_set=='using_wordpress_cron') {
		//Using WP cron
		//Dynamically scheduled events based on user settings
		if (is_array($cron_hookname) && (!(empty($cron_hookname)))) {
			
			foreach ($cron_hookname as $key_hookname=>$value_hookname) {
				//If hook is not scheduled AND also the current settings; schedule this event
				if ((!wp_next_scheduled($key_hookname)) && ($intervals_set==$value_hookname)) {
					wp_schedule_event( time(), $value_hookname, $key_hookname);
				}		
			}
			
		}
		//Dynamically add hooks based on user settings
		if (is_array($cron_hookname) && (!(empty($cron_hookname)))) {
			foreach ($cron_hookname as $key_hookname=>$value_hookname) {
				if ($intervals_set==$value_hookname) {
					add_action($key_hookname, array(&$this,'ajax_process_wc_views_batchprocessing'));					
				}	
			}			
		}
	} else {
		//Not using WP Cron, make sure all schedules are cleared
		if (is_array($cron_hookname) && (!(empty($cron_hookname)))) {
			foreach ($cron_hookname as $key_hookname=>$value_hookname) {				
				wp_clear_scheduled_hook($key_hookname);		
			}
		}
	}	

}
/*Reset WooCommerce Views products content template*/
function wc_views_reset_products_content_template() {
	global $wpdb;
	$all_product_ids=$wpdb->get_results("SELECT ID FROM $wpdb->posts where post_status='publish' AND post_type='product'",ARRAY_N);
	$clean_ids_for_processing=array();
	if ((is_array($all_product_ids)) && (!(empty($all_product_ids)))) {
		foreach ($all_product_ids as $key=>$value) {
			$clean_ids_for_processing[]=reset($value);
		}
	}

	//Reset product template to none
	//Set their templates to none
	if ((is_array($clean_ids_for_processing)) && (!(empty($clean_ids_for_processing)))) {
		foreach ($clean_ids_for_processing as $k=>$v) {
			$success_updating_template=update_post_meta($v, '_views_template', '');
		}
	}

}

/*Runtime template checker*/
function wc_views_runtime_template_checker() {

	//Runtime template checker

	$runtime_active_template=get_stylesheet();
	$template_in_db_wc_template=get_option('woocommerce_views_theme_template_file');
	if ((is_array($template_in_db_wc_template)) && (!(empty($template_in_db_wc_template))))  {
		$template_in_db_wc_template_value=key($template_in_db_wc_template);
	
		if ($runtime_active_template != $template_in_db_wc_template_value) {
			 
			//User must have been switched to a different template, use default
			//Update to dB
			$runtime_settings_value=array();
			$runtime_option_name='woocommerce_views_theme_template_file';
			$runtime_settings_value[$runtime_active_template]='Use WooCommerce Default Templates';
			$runtime_updating_success=update_option( $runtime_option_name, $runtime_settings_value);
			
			//Reset content templates
			$this->wc_views_reset_products_content_template();	
		}
	}

}

/* WooCommerce Views admin screen */
function woocommerce_views_admin_screen() {
	if (isset($_POST['woocommerce_views_nonce'])) {
		if (( wp_verify_nonce( $_POST['woocommerce_views_nonce'], 'woocommerce_views_nonce' )) && (isset($_POST['woocommerce_views_template_to_override'])))  {
            //Save template settings to options table
            $option_name='woocommerce_views_theme_template_file';
            $template_associated=get_stylesheet();
            $settings_value=array();
            $settings_value[$template_associated]=stripslashes(trim($_POST['woocommerce_views_template_to_override']));
            $success=update_option( $option_name, $settings_value);
            
            //Reset content templates to none if using Default WooCommerce Template
            //Template saved
            $template_saved= stripslashes(trim($_POST['woocommerce_views_template_to_override']));
            
            global $wpdb;
            $all_product_ids=$wpdb->get_results("SELECT ID FROM $wpdb->posts where post_status='publish' AND post_type='product'",ARRAY_N);
            $clean_ids_for_processing=array();
            if ((is_array($all_product_ids)) && (!(empty($all_product_ids)))) {
            	foreach ($all_product_ids as $key=>$value) {
            		$clean_ids_for_processing[]=reset($value);
            	}
            }
                       
            if ($template_saved=='Use WooCommerce Default Templates') {
               
               //Reset product template to none 				
               //Set their templates to none
				if ((is_array($clean_ids_for_processing)) && (!(empty($clean_ids_for_processing)))) {
                     foreach ($clean_ids_for_processing as $k=>$v) {
						$success_updating_template=update_post_meta($v, '_views_template', '');
                     }
                } 
            } else {
                    //Non-Default, switch back to content templates
            		global $WP_Views;
					$content_template_options = $WP_Views->get_options();
					if (isset($content_template_options)) {
 							if (!(empty($content_template_options))) {
                                 if (isset($content_template_options['views_template_for_product'])) {
                                   //Product content template is set, re-assigned
                                   $content_template_products=$content_template_options['views_template_for_product'];
                                   if ($content_template_products) {
                                   		if ((is_array($clean_ids_for_processing)) && (!(empty($clean_ids_for_processing)))) {
                                   			foreach ($clean_ids_for_processing as $k=>$v) {
                                   				$success_updating_template=update_post_meta($v, '_views_template', $content_template_products);
                                   			}
                                   		}
                                   }

                                 }

                             }

                    }

            }
            //Save batch processing related settings
            $option_name_batch_processing_settings='woocommerce_views_batch_processing_settings';            
            $woocommerce_views_batchprocessing_settings=trim($_POST['woocommerce_views_batchprocessing_settings']);
            $batch_processing_intervals_woocommerce_views=trim($_POST['batch_processing_intervals_woocommerce_views']);
            $system_cron_access_url=stripslashes(trim($_POST['system_cron_access_url']));
            
            $batch_processing_settings_value=array();
            if (isset($woocommerce_views_batchprocessing_settings)) {
            	$batch_processing_settings_value['woocommerce_views_batchprocessing_settings']=$woocommerce_views_batchprocessing_settings;
            }
            if (isset($batch_processing_intervals_woocommerce_views)) {
				$batch_processing_settings_value['batch_processing_intervals_woocommerce_views']=$batch_processing_intervals_woocommerce_views;
            }
            if (isset($system_cron_access_url)) {
				$batch_processing_settings_value['system_cron_access_url']=$system_cron_access_url;
            }
            //Update options
            $success_batch_processing_settings=update_option( $option_name_batch_processing_settings, $batch_processing_settings_value);               
			header("Location: admin.php?page=wpv_wc_views&update=true");
			
								
		}
	}			
?>	
    <?php 
    $this->wc_views_runtime_template_checker();
    ?>	
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2><?php _e('WooCommerce Views','woocommerce_views');?></h2>				

		<form id="woocommerce_views_form" action="<?php echo admin_url('admin.php?page=wpv_wc_views&noheader=true'); ?>" method="post">
			<?php wp_nonce_field( 'woocommerce_views_nonce', 'woocommerce_views_nonce'); ?>				
			<?php 
				$woocommerce_views_supported_templates= $this->load_correct_template_files_for_editing_wc();					
				?>		
				<h3><?php _e('Assign page template for WooCommerce single products','woocommerce_views');?></h3>	
				<div id="phptemplateassignment_wc_views">	
				<p><?php _e('Select one of your theme templates to be used for WooCommerce single products:','woocommerce_views');?></p>
				<p>
				<select name="woocommerce_views_template_to_override"> 
				<?php 
				$get_current_settings_wc_template=get_option('woocommerce_views_theme_template_file');
				if 	($get_current_settings_wc_template) {
                    //Settings initialized	
					$get_key_template=key($get_current_settings_wc_template);
					$get_current_settings_wc_template_path=$get_current_settings_wc_template[$get_key_template];
					if ((is_array($woocommerce_views_supported_templates)) && (!(empty($woocommerce_views_supported_templates)))) {
                	     foreach ($woocommerce_views_supported_templates as $template_file_name=>$theme_server_path) {				
					?>
						  <option value="<?php echo $theme_server_path?>" <?php if ($get_current_settings_wc_template_path==$theme_server_path) { echo "SELECTED";} ?>><?php echo $template_file_name;?></option>              
                	<?php }
                	} else {
                	//not loaded                  
                	?>
                			<option value="Use WooCommerce Default Templates" >Use WooCommerce Default Templates</option> 
                	<?php }?>
                <?php } else {
                	//Not initialized 
                	if ((is_array($woocommerce_views_supported_templates)) && (!(empty($woocommerce_views_supported_templates)))) {
                		foreach ($woocommerce_views_supported_templates as $template_file_name=>$theme_server_path) {
                ?>
 						<option value="<?php echo $theme_server_path?>" <?php if ($template_file_name=='Use WooCommerce Default Templates') { echo "SELECTED";} ?>><?php echo $template_file_name;?></option>
                	<?php }     
                	}    
                }
                ?>                
				</select>
				</p>	
				</div>
				<h3><?php _e('Batch processing options for updating calculated Product fields','woocommerce_views');?></h3>
				<div id="ajax_result_batchprocessing"></div>
				<div id="ajax_result_batchprocessing_logging">
				<div id="ajax_result_batchprocessing_time">	
				<?php
				$updated_batch_processing_time=get_option('woocommerce_last_run_update');
				if ((isset($updated_batch_processing_time)) && (!(empty($updated_batch_processing_time)))) {
	 	           $last_run_text=__('Calculated Product fields were last updated: ','woocommerce_views');
	 	           echo $last_run_text.$updated_batch_processing_time;
	            } else {
                   $default_run_text_without_set=__('Calculated Product fields have not been set.','woocommerce_views');
                   echo $default_run_text_without_set;
                }
				?>		
				</div>
				</div>				
				<div id="batchprocessing_woocommerce_views">
				    <?php 
				    //Retrieved settings from database
				    $batch_processing_settings_from_db=get_option('woocommerce_views_batch_processing_settings');
				    if (!(empty($batch_processing_settings_from_db))) {
                      //Settings set
                      
                      if (isset($batch_processing_settings_from_db['woocommerce_views_batchprocessing_settings'])) {
 	                    $form_woocommerce_views_batchprocessing_settings=$batch_processing_settings_from_db['woocommerce_views_batchprocessing_settings'];
                      } else {
                        //Default to manually
						$form_woocommerce_views_batchprocessing_settings='manually';
                      }
                      if (isset($batch_processing_settings_from_db['batch_processing_intervals_woocommerce_views'])) {
                      	$form_batch_processing_intervals_woocommerce_views=$batch_processing_settings_from_db['batch_processing_intervals_woocommerce_views'];
                      } else {
                      	//Default to daily
                      	$form_batch_processing_intervals_woocommerce_views='daily';
                      }
                      if (isset($batch_processing_settings_from_db['system_cron_access_url'])) {
                      	$form_system_cron_access_url=$batch_processing_settings_from_db['system_cron_access_url'];
                      } else {
                      	//Default 
						//$plugin_abs_path_retrieved=plugin_dir_path( __FILE__ );
						//Revise to URL path
						$plugin_abs_path_retrieved=plugins_url( 'system_cron/run_wc_views_cron.php', __FILE__ );
						$form_system_cron_access_url=$plugin_abs_path_retrieved;
                      }
                      
                    } else {
                        //Batch processing options not set, define defaults
                        $form_woocommerce_views_batchprocessing_settings='manually';
                        $form_batch_processing_intervals_woocommerce_views='daily';                        
                        //$plugin_abs_path_retrieved=plugin_dir_path( __FILE__ );
                        //Revise to URL path
                        //First time executed, generate secret key
                        $length=12;
                        $generated_secret_key=$this->wc_views_generaterandomkey($length);
                        //Store this secret key as options for easy verification
                        $value_changed=update_option('wc_views_sys_cron_key',$generated_secret_key);
                        $plugin_abs_path_retrieved=plugins_url( 'system_cron/run_wc_views_cron.php?cron_key='.$generated_secret_key, __FILE__ );
                        $form_system_cron_access_url=$plugin_abs_path_retrieved;                        
                        
                    }
				    ?>
					<p><?php _e('To filter Views by Product price, Stock or On-sale status, you need to update the WooCommerce Views calculated fields.','woocommerce_views');?></p>
					<p><?php _e('Select when your calculated Product fields will be updated:','woocomerce_views');?></p>
					<p><input type="radio" name="woocommerce_views_batchprocessing_settings" id="system_cron_id_wc_views" value="using_system_cron" <?php if ($form_woocommerce_views_batchprocessing_settings=='using_system_cron') { echo "checked"; }?>> <?php _e('Using a system cron - see access path:','woocommerce_views');?><input readonly="readonly" type="text" name="system_cron_access_url" id="wc_views_sys_cron_path" value="<?php echo $form_system_cron_access_url;?>"></p>									
					<p><input type="radio" name="woocommerce_views_batchprocessing_settings" id="wp_cron_id_wc_views" value="using_wordpress_cron" <?php if ($form_woocommerce_views_batchprocessing_settings=='using_wordpress_cron') { echo "checked"; }?>> <?php _e('Using the WordPress cron - select when:','woocommerce_views');?>
					<select name="batch_processing_intervals_woocommerce_views">
					<?php 
					//Dynamically retrieved available schedules for cron
					$available_schedules_for_cron=wp_get_schedules();					
					foreach ($available_schedules_for_cron as $key_schedule=>$value_schedule) {
					?>
					<option <?php if ($form_batch_processing_intervals_woocommerce_views==$key_schedule) { echo 'selected';}?> value="<?php echo $key_schedule;?>"><?php echo $available_schedules_for_cron[$key_schedule]['display'];?></option>					
                    <?php } ?>
					</select></p>
					<p><input type="radio" name="woocommerce_views_batchprocessing_settings" id="manual_id_wc_views" value="manually" <?php if ($form_woocommerce_views_batchprocessing_settings=='manually') { echo "checked"; }?>> <?php _e('Manually','woocommerce_views');?></p>									
				</div>
		</form>
		<form id="requestformanualbatchprocessing" method="post" action="">
		<input id="woocommerce_batchprocessing_submit" type="submit" name="Submit" class="button-secondary" onclick="return confirm( '<?php print esc_js('Are you sure you want to manually run this batch processing?'); ?>' );"
				value="<?php _e('Calculate product attributes for Views filters','woocommerce_views');?>" />
		</form>
		<?php 
 		if (isset($_GET['update_needed'])) {
			$updateneeded_value=trim($_GET['update_needed']);
			if ($updateneeded_value=='true') {
        ?>
         <div id="update_needed_wcviews" class="error">
         <?php _e('Select "Manually" and then please click "Calculate product attributes for Views filters" to update fields.','woocommerce_views');?>         
         </div>
         <?php 
            }      
         }			
		?>		
		<p class="submit">
					<input id="woocommerce_views_submit" form="woocommerce_views_form" type="submit" name="Submit" class="button-primary" value="<?php _e('Save all Settings','woocommerce_views');?>" />
		</p>
		<?php
		 if (isset($_GET['update'])) {
           $update_value=trim($_GET['update']);
           if ($update_value=='true') {
         ?>
         <div id="update_settings_div_wc_views" class="updated">
         <?php _e('Settings have been updated.','woocommerce_views');?>         
         </div>
         <?php                                  
         	}
         } elseif (isset($_GET['reset'])) {
			$reset_value=trim($_GET['reset']);
			if ($reset_value=='true') {
         ?>
         <div id="update_settings_div_wc_views" class="updated">
         <?php _e('Resetting successful.','woocommerce_views');?>         
         </div>
         <?php 
            }         
         }
         ?>		
		<form id="resetformwoocommerce" method="post" action="">
		<?php wp_nonce_field( 'woocommerce_views_resetnonce', 'woocommerce_views_resetnonce'); ?>	
		    <input type="submit" class="button" id="wc_viewsresetbutton" value="Restore default settings" onclick="return confirm( '<?php print esc_js('Are you sure? This will revert to default settings and your own settings will be lost!'); ?>' );" name="reset"> 
            <input type="hidden" name="wc_views_resetrequestactivated" value="reset" />
        </form> 
	</div>
<?php				
}

/* Reset admin screen settings to default */
function reset_all_wc_admin_screen() {

	if(isset($_REQUEST['wc_views_resetrequestactivated']))
	{
		//Verify nonce
		if (isset($_POST['woocommerce_views_resetnonce'])) {
			if ( wp_verify_nonce( $_POST['woocommerce_views_resetnonce'], 'woocommerce_views_resetnonce' ))  {		
		
				//reset to defaults
       		 //Option names
				$option_name_one='woocommerce_views_theme_template_file';
				$option_name_two='woocommerce_views_batch_processing_settings';
				$option_name_three='woocommerce_last_run_update';
		
				delete_option($option_name_one);
				delete_option($option_name_two);
				delete_option($option_name_three);
		
				global $wpdb;
				$all_product_ids_reset=$wpdb->get_results("SELECT ID FROM $wpdb->posts where post_status='publish' AND post_type='product'",ARRAY_N);
				$clean_ids_for_processing_reset=array();
				if ((is_array($all_product_ids_reset)) && (!(empty($all_product_ids_reset)))) {
					foreach ($all_product_ids_reset as $key=>$value) {
						$clean_ids_for_processing_reset[]=reset($value);
					}
				}		
				//Reset product template to none
						
				if ((is_array($clean_ids_for_processing_reset)) && (!(empty($clean_ids_for_processing_reset)))) {
					foreach ($clean_ids_for_processing_reset as $k=>$v) {
						$success_updating_template_reset=update_post_meta($v, '_views_template', '');
					}
				}			
				//redirect to reset =true
				header("Location: admin.php?page=wpv_wc_views&reset=true");
				
			}
		}
	}
}

function ajax_process_wc_views_batchprocessing($wc_view_woocommerce_orderobject='') {

	global $wpdb,$woocommerce;
 
	//Define custom field names
	$views_woo_price = 'views_woo_price';
	$views_woo_on_sale = 'views_woo_on_sale';
	$views_woo_in_stock = 'views_woo_in_stock';
	
	$response=array();
	
	//Get all product ids
	$woocommerce_product_ids=$wpdb->get_results("SELECT ID FROM $wpdb->posts where post_status='publish' AND post_type='product'",ARRAY_N);
	$woocommerce_clean_ids=array();	
	if ((is_array($woocommerce_product_ids)) && (!(empty($woocommerce_product_ids)))) {
		foreach ($woocommerce_product_ids as $key=>$value) {
			$woocommerce_clean_ids[]=reset($value);
		}
	} else {
        $response['status']='error';
		$response['batch_processing_output'] = __('Batch processing output is not successful because it looks like you do not have products yet.','woocommerce_views');
    }
	
	if ((is_array($woocommerce_clean_ids)) && (!(empty($woocommerce_clean_ids)))) {
        //Loop through individual products, get the updated product data needed and save to custom fields
		foreach ($woocommerce_clean_ids as $k=>$v) {
        $post=get_post($v);         
        $product = $this->wcviews_setup_product_data($post);
        	if (isset($product)) {
	           //Retrieve product price
	           $product_price=$product->get_price();
	           
	           //Check if product is on sale
	           $product_on_sale_boolean=$product->is_on_sale();
	           
	           //Check if product is in stock
	           $product_on_stock_boolean=$product->is_in_stock();
	           
	           //"0" adjustment
	           $product_on_stock_boolean=$this->for_views_null_equals_zero_adjustment($product_on_stock_boolean);
	           $product_on_sale_boolean=$this->for_views_null_equals_zero_adjustment($product_on_sale_boolean);
	           
	           //Save to custom fields
	           $success_price=update_post_meta($v,$views_woo_price,$product_price);
	           $success_on_sale=update_post_meta($v,$views_woo_on_sale,$product_on_sale_boolean);
	           $success_stock=update_post_meta($v,$views_woo_in_stock,$product_on_stock_boolean);

	           $response['status']='updated';
	           $response['batch_processing_output'] = __('Batch processing output completed.','woocommerce_views');   

	        } else {

			   $response['status']='error';
			   $response['batch_processing_output'] = __('Batch processing output is not successful because it looks like you do not have products yet.','woocommerce_views');
           }
        }    	
    } else {
		$response['status']='error';
		$response['batch_processing_output'] = __('Batch processing output is not successful because it looks like you do not have products yet.','woocommerce_views');
    }

	/*AJAX SECTION WORKING ON THE BACKEND ADMIN SCREEN*/    
   
	if ($response['status']='updated') {
	   $response['last_run'] = date_i18n('Y-m-d G:i:s');
	   $success_last_run_update=update_option( 'woocommerce_last_run_update',$response['last_run']);
	}
	
	//Run the ajax response only if object is not set
	if (!(isset($wc_view_woocommerce_orderobject->status))) {
		if (defined('DOING_AJAX') && DOING_AJAX ) {
		    echo json_encode($response);
			die();
		}
	}
	
} 

function for_views_null_equals_zero_adjustment($boolean_test) {

	if (!($boolean_test)) {
	//False
	return '0';
	
	} else {
	//True
	return '1';
	}

}
//Generate random key
function wc_views_generaterandomkey($length) {

	$string='';
	$characters = "0123456789abcdef";
	for ($p = 0; $p < $length ; $p++) {
		$string .= $characters[mt_rand(0, strlen($characters) - 1)];
	}

	return $string;
}

/* Method for loading correct template files for using with WooCommerce Views */
function load_correct_template_files_for_editing_wc() {

$theme = wp_get_theme();
$complete_template_files_list = $theme->get_files( 'php', 0,true);

	if ((is_array($complete_template_files_list)) && (!(empty($complete_template_files_list)))) {
    $correct_templates_list= array();
	foreach ($complete_template_files_list as $key=>$values) {
       $pos_single = stripos($key, 'single');
       $pos_page =  stripos($key, 'page');
       if (($pos_single !== false) || ($pos_page !== false)) {

          //Emerson: Qualified theme templates should contain WP loops for WC hooks and Views to work
          $is_theme_template_looped= $this->check_if_php_template_contains_wp_loop($values);
          if ($is_theme_template_looped) {
          $correct_templates_list[$key]=$values;
          }
       }      
	}
       
	   if (!(empty($correct_templates_list))) {
       $correct_templates_list['Use WooCommerce Default Templates']='Use WooCommerce Default Templates';
       return $correct_templates_list;       
       } else {
       return "";
       }
	}
}

function check_if_php_template_contains_wp_loop($template) {

	$handle = fopen($template, "r");
	$contents = fread($handle,filesize($template));
	$pieces = explode("\n", $contents);
	$have_post_key = $this->wcviews_array_find('have_posts()', $pieces);
	$the_post_key = $this->wcviews_array_find('the_post()', $pieces);
	
	fclose($handle);
	
	if (($have_post_key) && ($the_post_key)) {
	    return TRUE;
	} else {
        return FALSE;
    }  
}

function wcviews_array_find($needle, $haystack, $search_keys = false) {
	if(!is_array($haystack)) return false;
	foreach($haystack as $key=>$value) {
		$what = ($search_keys) ? $key : $value;
		if(strpos($what, $needle)!==false) return $key;
	}
	return false;
}

/**
 * Adds admin notice.
 */
function wcviews_help_admin_notice(){
	global $pagenow;
	if ( $pagenow == 'plugins.php' ) {
        //Show this only in plugins page
		if(!get_option('dismiss_wcviews_notice')){
        //Fresh plugin activation, now check if products exists
 			//WooCommerce activated, check if products exists
			global $wpdb;

			$products_exists = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type='product' AND post_status='publish'");
			
            //Show this notice if products exists and not using embedded Types/Views
			if ((!(empty($products_exists))) && (!defined('WPVDEMO_VERSION'))){
				//Products exists
				//Admin URL to plugins page
				$admin_url_wcviews=admin_url().'admin.php?page=wpv_wc_views&update_needed=true';
?>
				<div id="message" class="updated message fade" style="clear:both; margin-top: 5px;">
				<p><?php _e('WooCommerce Views needs to scan your products once and create calculated fields for Views filters.','woocommerce_views');?> <a href="<?php echo $admin_url_wcviews;?>"><strong><?php _e('Run this scan now','woocommerce_views');?></strong></a></p>
				</div>
<?php
			    //Show this message only once		    				

			} 
			update_option('dismiss_wcviews_notice', 'yes');
		}
   }
}

//Reset dismiss_wcviews_notice option after deactivation
function wcviews_request_to_reset_field_option() {
  delete_option('dismiss_wcviews_notice');
}
/**
 * Adds question mark icon
 * @return <type>
 */
function add_media_button($output){
	// avoid duplicated question mark icons (post-new.php)
	$pos = strpos($output, "Insert Types Shortcode");
	
	if($pos == false && !(isset($_GET['post_type']) && $_GET['post_type'] == 'view')){
		$output .= '<ul class="editor_addon_wrapper"><li><img src="'. plugins_url() . '/' . basename(dirname(__FILE__)) . "/res/img/question-mark-icon.png" .'"><ul class="editor_addon_dropdown"><li><div class="title">Learn how to use these Views</div><div class="close">&nbsp;</div></li><li><div>These Views let you insert product sliders, grids and tables to your content. <br /><br /><a href="http://wp-types.com/documentation/views-inside/woocommerce-views/" target="_blank" style="text-decoration: underline; font-weight: bold; color: blue;">Learn how to use these Views</a></div></li></ul></li></ul>';
	}

	return $output;
}

/**
 * Adds "OLD" CSS and Custom JS for Views
 */
function additional_css_js() {
	$stylesheet = plugins_url() . '/' . basename(dirname(__FILE__)) . '/res/css/wcviews-style.css';	
	wp_enqueue_style('wcviews-style', $stylesheet);
	wp_enqueue_script('jquery');	
}

//
//
//
//
//
//
// Merged with other plugin
/*Not anymore used starting version 2.0, function remains for backward compatibility*/
function wpv_woo_add_to_cart($atts) {

	global $post, $wp_query, $wpdb;
	
	$product_id = $post->ID;
	$current_page = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	$current_page = strpos($current_page, '?') ? $current_page . '&' : $current_page . '?';
	
	$get_variation_term_name = $wpdb->get_row("SELECT term_id FROM $wpdb->terms WHERE name = 'variable'");
	$get_variation_term_id = $get_variation_term_name->term_id;
	
	$is_product_with_variations = $wpdb->get_row("SELECT * FROM $wpdb->term_relationships WHERE object_id = '$product_id' AND term_taxonomy_id = '$get_variation_term_id'");
	if(!empty($is_product_with_variations)) $is_product_with_variations = TRUE;
	
	if(!$is_product_with_variations){
		$out = '<a href="'. $current_page .'add-to-cart='. $product_id .'" rel="nofollow" data-product_id="'. $product_id .'" class="button add_to_cart_button product_type_simple">'. __('Add to cart', 'woocommerce') .'</a>';
	} else {
		$out = '<a href="'. get_permalink($product_id) .'" rel="nofollow" data-product_id="'. $product_id .'" class="button add_to_cart_button product_type_variable">'. __('Select options', 'woocommerce') .'</a>';
	}
	
	return $out;
}

/**Emerson: NEW VERSION
[wpv-woo-buy-or-select]
Description: Displays 'add to cart' or 'select' button in product listings.
Will work only in product listing or main shop page.

Attributes/Parameters:

add_to_cart_text = Set the text in the simple product button if desired.
link_to_product_text = Set the text in the variation product button if desired.

Example using the two attributes: 

[wpv-woo-buy-or-select add_to_cart_text="Buy this now" link_to_product_text="Product options"]

Defaults to WooCommerce text.
**/
function add_to_cart_buy_or_select_closures($argument_one=null,$argument_two=null) {

    $is_using_revised_wc= $this->wcviews_using_woocommerce_two_point_one_above();
    
    if ($is_using_revised_wc) {
    	//Check product type
    	$product_type_passed=$argument_two->product_type;
    	if ($product_type_passed=='simple') {

			global $add_to_cart_text_product_listing_translated;
			return $add_to_cart_text_product_listing_translated;
			
		} else {
	
    	    return $argument_one;
    	}
    } else {
       //Old WC
		global $add_to_cart_text_product_listing_translated;
		return $add_to_cart_text_product_listing_translated;       
        
    }

}

function add_to_cart_buy_or_select_closures_listing($argument_one=null,$argument_two=null) {

	$is_using_revised_wc= $this->wcviews_using_woocommerce_two_point_one_above();
	
	if ($is_using_revised_wc) {
		//Check product type
		$product_type_passed=$argument_two->product_type;
		if ($product_type_passed=='variable') {

			global $link_product_listing_translated;
			return $link_product_listing_translated;

        } else {
            return $argument_one;

        }

    } else {
      //Old WC
	  global $link_product_listing_translated;
	  return $link_product_listing_translated;
	  
    }
}

function wpv_woo_buy_or_select_func($atts) {	
	
	/*Add to cart in loops	  
	 */

	global $post, $wpdb, $woocommerce;
	
	if ( 'product' == $post->post_type ) {        
       
        //Run only on single product page        
		
		$product =$this->wcviews_setup_product_data($post);
		
		if (isset($atts['add_to_cart_text'])) {
            //User is setting add to cart text customized	
             if (!(empty($atts['add_to_cart_text']))) {
				$add_to_cart_text_product_listing=trim($atts['add_to_cart_text']);
				
				//START support for string translation
				if (function_exists('icl_register_string')) {
					//Register add to cart text product listing for translation
					icl_register_string('woocommerce_views', 'add_to_cart_text',$add_to_cart_text_product_listing);
				}
				global $add_to_cart_text_product_listing_translated;  
				if (!function_exists('icl_t')) {
					//String translation plugin not available use original text
					$add_to_cart_text_product_listing_translated=$add_to_cart_text_product_listing;
					 
				} else {
					//String translation plugin available return translation
					$add_to_cart_text_product_listing_translated=icl_t('woocommerce_views', 'add_to_cart_text',$add_to_cart_text_product_listing);
				}				
				$is_using_revised_wc_simple=$this->wcviews_using_woocommerce_two_point_one_above();
				
				if ($is_using_revised_wc_simple) {
                    
                    //Updated WC
					add_filter('woocommerce_product_add_to_cart_text',array(&$this,'add_to_cart_buy_or_select_closures'),10,2);
					
                } else {

				    //Old WC
					add_filter('add_to_cart_text', array(&$this,'add_to_cart_buy_or_select_closures'));
					
			    }
		
			}
        }
        
        if (isset($atts['link_to_product_text'])) {
        	//User is setting link to product text customized
 		   if (!(empty($atts['link_to_product_text']))) {       
    		$link_product_listing=trim($atts['link_to_product_text']);

    		//START support for string translation
    		if (function_exists('icl_register_string')) {
    			//Register add to cart text product listing for translation
    			icl_register_string('woocommerce_views', 'link_to_product_text',$link_product_listing);
    		}
    	    global $link_product_listing_translated;
    		if (!function_exists('icl_t')) {
    			//String translation plugin not available use original text
    			$link_product_listing_translated=$link_product_listing;
    		 
    		} else {
    			//String translation plugin available return translation
    			$link_product_listing_translated=icl_t('woocommerce_views', 'link_to_product_text',$link_product_listing);
    		}
    		
    		//END support for string translation        
        
        	$is_using_revised_wc=$this->wcviews_using_woocommerce_two_point_one_above();
            
            if ($is_using_revised_wc) {
        	
				add_filter('woocommerce_product_add_to_cart_text',array(&$this,'add_to_cart_buy_or_select_closures_listing'),10,2);
            
			} else {

				add_filter('variable_add_to_cart_text',array(&$this,'add_to_cart_buy_or_select_closures_listing'));

            }

          }
        
        }
        
		if (isset($product)) {
			ob_start();
			//woocommerce_template_single_add_to_cart();
			woocommerce_template_loop_add_to_cart();	
			return ob_get_clean();	
		} else {
             return '';
        }  	
	}
}

/**Emerson: NEW VERSION
[wpv-woo-product-price]
Description: Displays the product price in product listing and single product pages.
**/

function wpv_woo_product_price_func($atts) {	
	 
   global $post,$woocommerce;
   $product =$this->wcviews_setup_product_data($post);
   
   if (isset($product)) {

   $product_price=$product->get_price_html();
   
   return $product_price;   
   }

}

//
/**Emerson: NEW VERSION
 [wpv-woo-product-image]
Description: Displays the product image, which starts with the featured image and changes to the variation image.

$atts: size
Options:

WordPress image sizes (configured at Settings --> Media --> Image sizes):

thumbnail = Wordpress image thumbnail size e.g. 150x150
medium    = Wordpress image medium size e.g. 300 x 300
large = Wordpress full image size e.g. 1024 x 1024

WooCommerce specific image sizes (configured at WooCommerce --> Settings --> Catalog --> Image Options)
shop_single = single product page size equivalent to medium ("Single Product Image")
shop_catalog= smaller than thumbnail images ("Catalog Images").
shop_thumbnail =similar to Wordpress thumbnail size ("Product Thumbnails").

Example usage:
[wpv-woo-product-image size="thumbnail"]
[wpv-woo-product-image size="shop_single"]
[wpv-woo-product-image size="medium"]

Defaults to shop_single
**/

function wcviews_set_image_size_closures() {
	global $attribute_image_size;	

	return $attribute_image_size;
}

function wpv_woo_product_image_func($atts) {    
    	
    	if ((isset($atts)) && (!(empty($atts)))) {
    		
            //Process size attributes
            if (isset($atts['size'])) {  		
                    global $attribute_image_size;
                    $attribute_image_size=$atts['size'];		
					add_filter('single_product_large_thumbnail_size',array(&$this,'wcviews_set_image_size_closures'));
            }
                        
            //Filter for raw image output, not image link
            if (isset($atts['output'])) {
            	if ($atts['output']=='img_tag') {
            		add_filter('woocommerce_single_product_image_html',array(&$this,'show_raw_image_html_wc_views'),10,2);            
            	} elseif ($atts['output']=='raw') {
					add_filter('woocommerce_single_product_image_html',array(&$this,'show_raw_image_url_wc_views'),20,2);
                }   
            }   
   		
		}

	//Reordered
	ob_start();	
	global $post,$woocommerce;	
	$product =$this->wcviews_setup_product_data($post);
	
	//Fix placeholder image size for those without featured image set
	if (!(has_post_thumbnail())) {
		add_filter('woocommerce_single_product_image_html',array(&$this,'adjust_wc_views_image_placeholder'),10,2);		
	} else {
		remove_filter('woocommerce_single_product_image_html',array(&$this,'adjust_wc_views_image_placeholder'),10,2);
    }
	
	if (isset($product)) {
		woocommerce_show_product_images();
		$image_content = ob_get_contents();
		//Image processing to remove Woocommerce <div> tags around the image HTML if user wants to output img_tag only or raw URL
		if (isset($atts['output'])) {
           if (($atts['output']=='img_tag') || ($atts['output']=='raw')) {
				$image_content=trim(strip_tags($image_content, '<img>'));
            }
        }
		ob_end_clean();
	} else {
		$image_content = ob_get_contents();
		ob_end_clean();
    }
	return $image_content;	
	
}
function adjust_wc_views_image_placeholder($imagehtml,$postid) {

	//Get user image size
	$user_image_size_set=apply_filters( 'single_product_large_thumbnail_size', 'shop_single' );
	
	//Get available image sizes	
	$image_sizes_available=$this->wc_views_list_image_sizes();
	
	//Get image size for user settings
	if (isset($image_sizes_available[$user_image_size_set])) {
       $image_dimensions_for_place_holder=$image_sizes_available[$user_image_size_set];

    } else {
        //Default to thumbnail
		$image_dimensions_for_place_holder=array(0=>'150',1=>'150');
    }   
    $placeholder_width=$image_dimensions_for_place_holder[0];
    $placeholder_height=$image_dimensions_for_place_holder[1];
    $image_src_source=simplexml_load_string($imagehtml);
    $image_src_source_url= (string) $image_src_source->attributes()->src;
    $output_image_placeholder_html='<img src="'.$image_src_source_url.'" alt="Placeholder" width="'.$placeholder_width.'" height="'.$placeholder_height.'" />';
    return $output_image_placeholder_html;
	
}

function wc_views_list_image_sizes(){
	global $_wp_additional_image_sizes;
	$sizes = array();
	foreach( get_intermediate_image_sizes() as $s ){
		$sizes[ $s ] = array( 0, 0 );
		if( in_array( $s, array( 'thumbnail', 'medium', 'large' ) ) ){
			$sizes[ $s ][0] = get_option( $s . '_size_w' );
			$sizes[ $s ][1] = get_option( $s . '_size_h' );
		}else{
			if( isset( $_wp_additional_image_sizes ) && isset( $_wp_additional_image_sizes[ $s ] ) )
				$sizes[ $s ] = array( $_wp_additional_image_sizes[ $s ]['width'], $_wp_additional_image_sizes[ $s ]['height'], );
		}
	}

	return $sizes;
}

function show_raw_image_html_wc_views($imagehtml,$id) {
    //Convert image link to raw image src output
	preg_match_all('#<img\b[^>]*>#', $imagehtml, $match);
	$img_tag_html = implode("\n", $match[0]);
	$img_tag_html_array=explode("\n",$img_tag_html);
	if (isset($img_tag_html_array[0])) {
		$imagehtml=$img_tag_html_array[0];
	}
	
	//Return raw output
	return $imagehtml;	

}
function show_raw_image_url_wc_views($imagehtml,$id) {
	preg_match_all('#<img\b[^>]*>#', $imagehtml, $match);
	$img_tag_html = implode("\n", $match[0]);
	$img_tag_html_array=explode("\n",$img_tag_html);
	if (isset($img_tag_html_array[0])) {
		$imagehtml=$img_tag_html_array[0];
	}

	$image_src_source=simplexml_load_string($imagehtml);
	$image_src_source_url= (string) $image_src_source->attributes()->src;	
	return $image_src_source_url; 
}
//

/**Emerson: NEW VERSION
[wpv-woo-buy-options]
Description: Displays 'add to cart' or 'select options' box for single product pages.
Attributes: add_to_cart_text
**/
function single_add_to_cart_text_closure_func() {
	global $add_to_cart_text_product_page_translated;
	return $add_to_cart_text_product_page_translated;
}

function wpv_woo_buy_options_func($atts) {

global $post, $wpdb, $woocommerce;
	
	if ( 'product' == $post->post_type ) {        
       
        //Run only on single product page
        if (is_product()) {			
			$product =$this->wcviews_setup_product_data($post);
			
			if (isset($atts['add_to_cart_text'])) {
              if (!(empty($atts['add_to_cart_text']))) {
				//User is setting add to cart text customized
			
				$add_to_cart_text_product_page=trim($atts['add_to_cart_text']);
			
				//START support for string translation
				if (function_exists('icl_register_string')) {
					//Register add to cart text product listing for translation
					icl_register_string('woocommerce_views', 'product_add_to_cart_text',$add_to_cart_text_product_page);
				}
				
				global $add_to_cart_text_product_page_translated;
					
				if (!function_exists('icl_t')) {
					//String translation plugin not available use original text
					$add_to_cart_text_product_page_translated=$add_to_cart_text_product_page;
			
				} else {
					//String translation plugin available return translation
					$add_to_cart_text_product_page_translated=icl_t('woocommerce_views', 'product_add_to_cart_text',$add_to_cart_text_product_page);
				}			
			
				$using_revised_woocommerce=$this->wcviews_using_woocommerce_two_point_one_above();
				
				if ($using_revised_woocommerce) {
					
					add_filter('woocommerce_product_single_add_to_cart_text',array(&$this,'single_add_to_cart_text_closure_func'));

				} else {

					add_filter('single_add_to_cart_text',array(&$this,'single_add_to_cart_text_closure_func'));
				
				}

			  }
			}
				
			ob_start();
		
			if ($product->product_type=='simple') {
							
			 	woocommerce_template_single_add_to_cart();
			 	//woocommerce_template_loop_add_to_cart();
			
			} elseif ($product->product_type=='variable') {
 				    
				do_action( 'woocommerce_variable_add_to_cart'); 
				
			}
			
			return ob_get_clean();
	  	}
	} 
}

/**Emerson: NEW VERSION
[wpv-add-to-cart-message]
Description: Displays add to cart success message and link to cart for product variation
Or you can add the hook directly to the theme template

do_action( 'woocommerce_before_single_product' );

preferably after get_header();
**/

function wpv_show_add_cart_success_func($atts) {
	global $post, $wpdb, $woocommerce;

	$check_if_using_revised_wc=$this->wcviews_using_woocommerce_two_point_one_above();

	if (!($check_if_using_revised_wc)) {
		if (( isset($woocommerce->messages) ) || (isset($woocommerce->errors))) {

			$html_result=$this->wcviews_add_to_cart_success_html();
			return $html_result;
		}
	} else {
		//Using revised WC
		 
		$cart_contents=$woocommerce->cart;
		$cart_contents_array=$cart_contents->cart_contents;
		 
		if (!(empty($cart_contents_array))) {

			$html_result=$this->wcviews_add_to_cart_success_html();
			return $html_result;
		}
	}

}

function wcviews_add_to_cart_success_html() {

	//Has message defined
	//Can be reordered anywhere
	ob_start();

	if (is_product()) {
		do_action( 'woocommerce_before_single_product' );
	} else {
		woocommerce_show_messages();
	}
	$add_to_cart_success_content = ob_get_contents();
	ob_end_clean();

	return $add_to_cart_success_content;

}

/**Emerson: NEW VERSION
Description: woo_product_on_sale() - This function returns true if the product is on sale
**/
function woo_product_on_sale() {

global $post, $woocommerce;

if ((isset($woocommerce)) && (isset($post))) {	
	$product =$this->wcviews_setup_product_data($post);
	
	if (isset($product)) {
		if ($product->is_on_sale()) {
	
			return TRUE;
	
		} else {
	
			return FALSE;
	
		}
	}
}
}

/**Emerson: NEW VERSION
 Description: woo_product_in_stock() - This function returns true if the product is on stock
**/

function woo_product_in_stock() {
	global $post;
	
	if (isset($post->ID)) {
		$post_id = $post->ID;
		$stock_status = get_post_meta($post_id, '_stock_status',true);
		
		if ($stock_status== 'outofstock') {
	    
 	     return FALSE;
 	     
 	   } elseif ($stock_status== 'instock') {
	
	      return TRUE;
	      
	    }
    }
}

/**Emerson: NEW VERSION
 Description: Allow user to set the PHP template for single products from the plugin admin
**/

function woocommerce_views_activate_template_redirect()
{			
    if (is_product()) {
    	//Single Product page!
    	//Get template settings
    	
    	$get_template_wc_template=get_option('woocommerce_views_theme_template_file');
    	
    	if ((is_array($get_template_wc_template)) && (!(empty($get_template_wc_template)))) {
    	
    	$live_active_template=get_stylesheet();    	
    	$template_name_for_redirect=key($get_template_wc_template);
    	$template_path_for_redirect=$get_template_wc_template[$template_name_for_redirect];
    	
    	    //Make sure this template change makes sense
            if ($live_active_template==$template_name_for_redirect) {      

            	//Template settings exists, but don't do anything unless specified
            	if (!($template_path_for_redirect=='Use WooCommerce Default Templates')) {

            	    //Template file selected, load it
            	    if (file_exists($template_path_for_redirect)) {
            	    include($template_path_for_redirect);
            	    exit();
            	    }
            	}
            }
        }	
	}
	
}

/**Emerson: NEW VERSION
[wpv-woo-display-tabs]
Description: Displays additional information and reviews tab.
For best results, you might want to disable comment section in products pages in your theme
so it will be replaced with this shortcode
This will replace the comment section for WooCommerce single product pages
**/

function wpv_woo_display_tabs_func() {

	if (is_product()) {

		global $woocommerce, $WPV_templates,$post;

		//Check for empty WooCommerce product content, if empty.
		//Apply the removal of filter for the_content only if content is set

		if (isset($post->post_content)) {
			$check_product_has_content=$post->post_content;
			if (!(empty($check_product_has_content))) {

				//Has content, Remove this filter, run only once -prevent endless loop due to WP core apply_filters on the_content hook
				remove_filter('the_content', array($WPV_templates, 'the_content'), 1, 1);
			}
		}

		ob_start();
		woocommerce_output_product_data_tabs();
		$version_quick_check=$this->wcviews_using_woocommerce_two_point_one_above();
		if ($version_quick_check) {
             //WC 2.1+
             add_filter('comments_template',array(&$this,'wc_views_comments_template_loader'),999);
        } elseif (!($version_quick_check)) {
            //Old WC
			remove_filter( 'comments_template', array( $woocommerce, 'comments_template_loader' ) );
		}
		$content = ob_get_contents();
		ob_end_clean();
		add_filter('the_content', array($WPV_templates, 'the_content'), 1, 1);

		return $content;
	}
}

function wc_views_comments_template_loader($template) {

    if (isset($template)) {

       $basefile=basename($template);
       if ($basefile=='single-product-reviews.php') {
           //Don't show any redundant comment templates
           return '';           
       } else {
           //Return unfiltered
           return $template;
       }       
    } else {
       //Return unfiltered
       return $template;
    }
	return '';

}
/*Emerson: NEW VERSION
Function that runs through all products and calculates computed postmeta from WooCommerce functions
*/
function compute_postmeta_of_products_woocommerce_views() {

//Define custom field names
$views_woo_price = 'views_woo_price';
$views_woo_on_sale = 'views_woo_on_sale';
$views_woo_in_stock = 'views_woo_in_stock';

if (!(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) { 

	//Detection when saving and updating a post, not on autosave
	//Updated custom values is on the $_POST
	if ((isset($_POST)) && (!(empty($_POST)))) {
       
		//Run this hook on WooCommerce edit pages
		if (isset($_POST['post_type'])) {

          if ($_POST['post_type']=='product') {

            /*Handle Quick Edit Mode*/
            //Check if doing quick edit
            
            if (isset($_POST['woocommerce_quick_edit_nonce'])) {

                   //Doing quick edits!
                   define('WC_VIEWS_DOING_QUICK_EDIT', true);
                   
                   //Now lets define product type
                   if ((empty($_POST['_regular_price'])) && (empty($_POST['_sale_price']))) {
                       
                       //This must be a variation
                       $_POST['product-type']='variable';
                       
                   } else {

						//This must be a simple product
						$_POST['product-type']='simple';
                   }                  
            }
		
       		//$_POST is set
       		$post_id_transacted=trim($_POST['ID']);
       		$product_type_transacted= trim($_POST['product-type']);
       		if ($product_type_transacted=='simple') {
       			//Get the price of simple product
       			//Check if on sale or not
       			if (empty($_POST['_sale_price'])) {
                   //Not on sale, get regular price
       				$simple_product_price=trim($_POST['_regular_price']);
       				$onsale_status=FALSE;
       				$onsale_status=$this->for_views_null_equals_zero_adjustment($onsale_status);
       				$on_sale_success= update_post_meta($post_id_transacted,$views_woo_on_sale,$onsale_status);       				
       			} else {
					//On sale, get sales price
					$simple_product_price=trim($_POST['_sale_price']);
					//Save custom field on sale
					$onsale_status=TRUE;
					$onsale_status=$this->for_views_null_equals_zero_adjustment($onsale_status);
					$on_sale_success= update_post_meta($post_id_transacted,$views_woo_on_sale,$onsale_status);
                }
       			//Save as custom field of simple product
       			if ((!(empty($simple_product_price))) && ($simple_product_price != '0')) {
 	      			$success= update_post_meta($post_id_transacted,$views_woo_price,$simple_product_price);
       			}
       			//Save on stock status for simple products
       			if (isset($_POST['_stock_status'])) {
                    if (!(empty($_POST['_stock_status']))) {
                        $on_stock_status=trim($_POST['_stock_status']); 
                        if ($on_stock_status=='instock') {
                           $on_stock_status=TRUE;
                           $on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
                        } elseif ($on_stock_status=='outofstock') {
							$on_stock_status=FALSE;
							$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);							
                        }
                        if (isset($on_stock_status)) {
							$success_on_stock_status= update_post_meta($post_id_transacted,$views_woo_in_stock,$on_stock_status);
						}
                    }
                }
            } elseif ($product_type_transacted='variable') {
				
				//Variable price is only updated when NOT doing quick edit.
				
				if (!defined('WC_VIEWS_DOING_QUICK_EDIT')) {
				
					//Get the price of simple product
					$variable_product_price=array();
					$variable_product_price=$_POST['variable_regular_price'];
					
					//Find the minimum
					if (!(empty($variable_product_price))) {
 	                
 	               $minimum_variation_price_set=min($variable_product_price);
                
 	               }
					//Save as custom field of simple product
					if ((!(empty($minimum_variation_price_set))) && ($minimum_variation_price_set !='0')) {
						$success= update_post_meta($post_id_transacted,$views_woo_price,$minimum_variation_price_set);
					}
				
				}
				
				//Save on stock status for variation products
				if (isset($_POST['_stock_status'])) {
					if (!(empty($_POST['_stock_status']))) {
						$on_stock_status=trim($_POST['_stock_status']);
						
						//Doing quick edit mode
						if (defined('WC_VIEWS_DOING_QUICK_EDIT')) {
                            
                            if ($on_stock_status=='outofstock') {
								$on_stock_status=FALSE;
								$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
							} else {
								$on_stock_status=TRUE;
								$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
                            }
                            	$success_on_stock_status= update_post_meta($post_id_transacted,$views_woo_in_stock,$on_stock_status);                            
                        }
                        
						if (isset($_POST['variable_stock'])) {
                           if (is_array($_POST['variable_stock'])) {
                               $total_stock_qty_variation=array_sum($_POST['variable_stock']);
                               $variable_stock_quantity_wcviews=trim($_POST['_stock_status']);
                               if (($on_stock_status=='instock') && ($total_stock_qty_variation > 0)) {
                               	$on_stock_status=TRUE;
                               	$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
                               } elseif ($on_stock_status=='outofstock') {
                               	$on_stock_status=FALSE;
                               	$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
                               } elseif ($total_stock_qty_variation <= 0) {
								$on_stock_status=FALSE;
								$on_stock_status=$this->for_views_null_equals_zero_adjustment($on_stock_status);
                               }
                               if (isset($on_stock_status)) {
                               	$success_on_stock_status= update_post_meta($post_id_transacted,$views_woo_in_stock,$on_stock_status);
                               }
                           }

                        }
					}
				}	
			   //Logic on saving variation product is on_sale
			   if (isset($_POST['variable_sale_price'])) {
                 $variable_sales_price_array=array();
                 $variable_sales_price_array=$_POST['variable_sale_price'];
                 //Test if sales price exists
                 $sum_sales_test=array_sum($variable_sales_price_array);
                 if ($sum_sales_test==0) {
                    //Product is not on sale
					//Save custom field not on sale
					$onsale_status_variation=FALSE;
					$onsale_status_variation=$this->for_views_null_equals_zero_adjustment($onsale_status_variation);
					$on_sale_success_variation= update_post_meta($post_id_transacted,$views_woo_on_sale,$onsale_status_variation);                    
                 } else {
					//Product is on sale
					//Save custom field on sale
					$onsale_status_variation=TRUE;
					$onsale_status_variation=$this->for_views_null_equals_zero_adjustment($onsale_status_variation);
					$on_sale_success_variation= update_post_meta($post_id_transacted,$views_woo_on_sale,$onsale_status_variation);		

                 }

               }			
            }       		
       	
       	  }
       	
       	}      
	}

}
}

/*[wpv_woo_add_to_cart_box] is not anymore used starting version 2.0, function remains for backward compatibility*/
function wpv_woo_add_to_cart_box($atts) {    

global $post, $wpdb, $woocommerce;

if ( ! isset( $atts['style'] ) ) $atts['style'] = 'border:4px solid #ccc; padding: 12px;';

if ( 'product' == $post->post_type ) {
	
	$product =$this->wcviews_setup_product_data($post);

	ob_start();
	?>
		<p class="product woocommerce" style="<?php echo $atts['style']; ?>">

			<?php echo $product->get_price_html(); ?>

			<?php woocommerce_template_loop_add_to_cart(); ?>

		</p><?php

		return ob_get_clean();

	} elseif ( 'product_variation' == $post->post_type ) {

		$product = get_product( $post->post_parent );

		$GLOBALS['product'] = $product;

		$variation = get_product( $post );

		ob_start();
		?>
		<p class="product product-variation" style="<?php echo $atts['style']; ?>">

			<?php echo $product->get_price_html(); ?>

			<?php

			$link 	= $product->add_to_cart_url();

			$label 	= apply_filters('add_to_cart_text', __( 'Add to cart', 'woocommerce' ));

			$link = add_query_arg( 'variation_id', $variation->variation_id, $link );

			foreach ($variation->variation_data as $key => $data) {
				if ($data) $link = add_query_arg( $key, $data, $link );
			}

			printf('<a href="%s" rel="nofollow" data-product_id="%s" class="button add_to_cart_button product_type_%s">%s</a>', esc_url( $link ), $product->id, $product->product_type, $label);

			?>

		</p><?php

		return ob_get_clean();

	}
}

function wpv_woo_remove_from_cart($atts) {
	
}

function wpv_woo_cart_url($atts) {
	
}

function wpv_woo_add_shortcode_in_views_popup($items){
   /*Old shortcode, functions not removed for backward compatibility*/
   /*
	$items['WooCommerce']['image'] = array(
		'Add to cart button',
		'wpv-wooaddcart',
		'Basic',
		''
	);	
	$items['WooCommerce']['addcartbox'] = array(
		__('Add to cart box', 'wpv-views'),
		'wpv-wooaddcartbox',
		__('Basic', 'wpv-views'),
		''
	);
	*/

    //[wpv-woo-buy-or-select]
	$items['WooCommerce']['productbuyorselect'] = array(
		'Buy or select product for listing pages',
		'wpv-woo-buy-or-select',
		'Basic',
		'wcviews_insert_wpv_woo_buy_or_select(); return false;'
	);   
    //[wpv-woo-product-price] 
	$items['WooCommerce']['productpricedisplay'] = array(
		'Product price',
		'wpv-woo-product-price',
		'Basic',
		''
	);
	//[wpv-woo-buy-options]
	$items['WooCommerce']['productbuyoptions'] = array(
			'Purchase options for single product',
			'wpv-woo-buy-options',
			'Basic',
			'wcviews_insert_wpv_woo_buy_options(); return false;'
	);
	//[wpv-woo-product-image]
	$items['WooCommerce']['productimagewoocommerceviews'] = array(
			'Product image',
			'wpv-woo-product-image',
			'Basic',
			'wcviews_insert_wpv_woo_product_image(); return false;'
	);
	//[wpv-show-add-cart-success]
	$items['WooCommerce']['productaddtocartsuccess'] = array(
			'Add to cart message',
			'wpv-add-to-cart-message',
			'Basic',
			''
	);
    //[wpv-woo-display-tabs]
	$items['WooCommerce']['productwoodisplayingtabs'] = array(
			'Display WooCommerce Tabs',
			'wpv-woo-display-tabs',
			'Basic',
			''
	);

	return $items;
}

//WooCommerce Views setup product data function based on WooCommerce functions
//Updated to be compatible with WC version 2.1+ with backward compatibility

function wcviews_setup_product_data($post) {

   if (function_exists('wc_setup_product_data')) {
       //Using WooCommerce Plugin version 2.1+
       $product_information=wc_setup_product_data( $post );
       return $product_information;

   } else {

      //Probably still using older woocommerce versions
      global $woocommerce;
      
      if (is_object($woocommerce)) {
      	$product_information = $woocommerce->setup_product_data( $post );
      	return $product_information;
      }
   }
}

//NEW: Compatibilty function to check for WooCommerce versions
//Returns TRUE if using WooCommerce version 2.1.0+
function wcviews_using_woocommerce_two_point_one_above() {
	 
	global $woocommerce;
	if (is_object($woocommerce)) {

		$woocommerce_version_running=$woocommerce->version;

		if (version_compare($woocommerce_version_running, '2.1.0', '<')) {

			return FALSE;

		} else {

			return TRUE;

		}
	}

}
}