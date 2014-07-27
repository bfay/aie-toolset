<?php

class WPBT_theme {

    function __construct() {
        global $wp_version;
        // enqueue theme js/css
        add_action('admin_enqueue_scripts', array($this, 'wpbootstrap_js'));
        add_action('admin_enqueue_scripts', array($this, 'wpbootstrap_css'));
        add_action('admin_enqueue_scripts',array($this,'check_if_to_be_loaded'));

        // theme activation massage
        add_action("after_switch_theme", array($this, 'wpbootstrap_theme_activation'), 1);

        // Add xml file support
        add_filter('upload_mimes', array($this, 'wpbootstrap_add_extended_mime_types'));

        // add support WP Pointers
        add_action('admin_enqueue_scripts', array($this, 'wpbootstrap_pointer'));

        // add content media buttons
        add_action('media_buttons_context', array($this, 'wpbootstrap_insert_grid_button'), 20, 1);

        // configuration of syntax mode in theme
        add_action('wp_print_scripts', array($this, 'wpbootstrap_syntax_mode_configuration'));

        // save insert grid dialog settings
        add_action('save_post', array($this, 'wpbootstrap_insert_grid_configuration'));

        // add support of new media uploader
        add_action('admin_enqueue_scripts', array($this, 'wpbootstrap_media_uploader'));

        // load default grid from xml file
        add_action('optionsframework_custom_scripts', array($this, 'wpbootstrap_load_default_grid'));

        // config tinymce editor
        add_filter('tiny_mce_before_init', array($this, 'wpbootstrap_tinymce_config'));

        // add hidden field to post form
        add_action('dbx_post_sidebar', array($this, 'wpbootstrap_active_tab_field'));

        // save active editor tad
        add_action('save_post', array($this, 'wpbootstrap_save_active_tab'));

        if (version_compare($wp_version, '3.1.4', '<')) {
        // insert media
        add_action('media_send_to_editor', array($this, 'wpbootstrap_insert_media'));
        }
    }

    public static function wpbootstrap_cpt_support() {// return list supported CPT
        $post_type_all = get_post_types();
        unset($post_type_all['attachment'], $post_type_all['revision'], $post_type_all['nav_menu_item'], $post_type_all['acf'], $post_type_all['optionsframework'], $post_type_all['view'], $post_type_all['view-template'], $post_type_all['dd_layouts'], $post_type_all['wp-types-group'], $post_type_all['cred-form'], $post_type_all['wp-types-user-group'], $post_type_all['product_variation'], $post_type_all['shop_order'], $post_type_all['shop_coupon']);
        return $post_type_all;
    }

    function wpbootstrap_js() {// register theme js
        global $wp_scripts;

        $condition_for_loading=$this->check_if_to_be_loaded();

        # add codemirror js if not registered views-layout-meta-html-codemirror-script or layouts-meta-html-codemirror-script
        if (!isset($wp_scripts->registered['views-layout-meta-html-codemirror-script']) & !isset($wp_scripts->registered['layouts-meta-html-codemirror-script'])) {
            wp_register_script('wpbootstrap-codemirror-script', get_template_directory_uri() . '/theme-options/bootstrap-grid/resources/codemirror/lib/codemirror.js', array('jquery'));
            wp_register_script('wpbootstrap-codemirror-overlay-script', get_template_directory_uri() . '/theme-options/bootstrap-grid/resources/codemirror/addon/mode/overlay.js', array('wpbootstrap-codemirror-script'));
            wp_register_script('wpbootstrap-codemirror-xml-script', get_template_directory_uri() . '/theme-options/bootstrap-grid/resources/codemirror/mode/xml/xml.js', array('wpbootstrap-codemirror-overlay-script'));
            wp_register_script('wpbootstrap-codemirror-css-script', get_template_directory_uri() . '/theme-options/bootstrap-grid/resources/codemirror/mode/css/css.js', array('wpbootstrap-codemirror-overlay-script'));
            wp_register_script('wpbootstrap-codemirror-js-script', get_template_directory_uri() . '/theme-options/bootstrap-grid/resources/codemirror/mode/javascript/javascript.js', array('wpbootstrap-codemirror-overlay-script'));
            wp_register_script('wpbootstrap-codemirror-config-script', get_template_directory_uri() . '/theme-options/bootstrap-grid/resources/codemirror/views_codemirror_conf.js', array('jquery'));

            if ($condition_for_loading) {
            wp_enqueue_script('wpbootstrap-codemirror-script');
            wp_enqueue_script('wpbootstrap-codemirror-overlay-script');
            wp_enqueue_script('wpbootstrap-codemirror-xml-script');
            wp_enqueue_script('wpbootstrap-codemirror-css-script');
            wp_enqueue_script('wpbootstrap-codemirror-js-script');
            wp_enqueue_script('wpbootstrap-codemirror-config-script');
            }
        }

        //Load icl_editor script only on pages where its needed. Other icl_editor script from plugins will also be loaded if activated.
        if ($condition_for_loading) {
			if ( !wp_script_is('icl_editor-script', 'registered')  ){
				wp_register_script('icl_editor-script', get_template_directory_uri() . '/theme-options/bootstrap-grid/js/common-js/editor-js/icl_editor_addon_plugin.js', array('wpbootstrap-codemirror-script'));
				wp_enqueue_script('icl_editor-script');
			}
        }

        // Register main JS
        if ($condition_for_loading) {
        wp_register_script('wpbootstrap-main', get_template_directory_uri() . '/theme-options/bootstrap-grid/js/bootstrap_editor.js', array('wpbootstrap-codemirror-script'));        
        wp_deregister_script( 'toolset-colorbox-js' );
        wp_register_script( 'toolset-colorbox-js', get_template_directory_uri() . '/theme-options/bootstrap-grid/js/jquery.colorbox-min.js', array('jquery'));
        }
        //Custom CSS
        $custom_css_localize_js=of_get_option('css_loading_method_selected_user');
        if (!($custom_css_localize_js)) {
        	$custom_css_localize_js='loading_from_theme';
        }
        //localized js
        $l10n_obj = array(
            'syntax_notification_title' => __('Syntax Highlight Editing Mode', 'wpbootstrap'),
            'syntax_notification_content' => __('Correct Response!', 'wpbootstrap'),
            'grid_dialog_title' => __('Click the row style to select it, then click the insert button to add it to your page.', 'wpbootstrap'),
            'grid_dialog_content' => __('Toolset Bootstrap added an editing mode that includes syntax highlighting. This lets you edit HTML content a lot easier. You can enable this mode for different content types from the theme', 'wpbootstrap').' <a href="http://%s/wp-admin/themes.php?page=options-framework#of-option-syntaxhighlight">'.__('options page','wpbootstrap').'</a>.',
            'select_grid_row_fluid' => __('Use fluid layout to distribute columns evenly', 'wpbootstrap'),
            'select_grid_row' => __('Resize columns and use fixed-width layout', 'wpbootstrap'),
            'select_grid_columns_includes' => __('The content area where this layout will be inserted has','wpbootstrap').' <strong> <span>%s</span> columns</strong>', 
            'select_grid_columns_checkbox' => __('Show only layouts designed for','wpbootstrap').' <span>%s</span> '.__('columns', 'wpbootstrap'),
            'syntax_dialog_title' => __('Toolset Bootstrap', 'wpbootstrap'),
            'syntax_dialog_content' => __('You can disable visual editing globally in the','wpbootstrap').' <a href="http://%s/wp-admin/themes.php?page=options-framework#of-option-syntaxhighlight">'.__('theme options','wpbootstrap').'</a>.',
            'insert_media_title' => __('Choose Image', 'wpbootstrap'),
            'custom_css_customize'=>$custom_css_localize_js,
            'add_media_button_title' => __('Add Media', 'wpbootstrap')
        );
        wp_localize_script('wpbootstrap-main', 'translations', $l10n_obj);
        if ($condition_for_loading) {
        wp_enqueue_script('wpbootstrap-main');        
        wp_enqueue_script('toolset-colorbox-js');
        wp_enqueue_script('wp-pointer');
        }
    }

    function wpbootstrap_css() {// register theme css
    	$condition_for_loading=$this->check_if_to_be_loaded();

        if ($condition_for_loading) {
    	wp_enqueue_style('wp-pointer');
        wp_register_style('wpbootstrap-main', get_template_directory_uri() . '/theme-options/bootstrap-grid/css/bootstrap_editor.css');
        wp_register_style('wpbootstrap-codemirror', get_template_directory_uri() . '/theme-options/bootstrap-grid/resources/codemirror/lib/codemirror.css', array());
        wp_deregister_style( 'toolset-colorbox-css' );
        wp_register_style( 'toolset-colorbox-css' , get_template_directory_uri() . '/theme-options/bootstrap-grid/css/colorbox.css');
        wp_enqueue_style('toolset-colorbox-css');
        wp_enqueue_style('wpbootstrap-main');
        wp_enqueue_style('wpbootstrap-codemirror');
        }

    }

    function wpbootstrap_theme_activation() {// theme activation
        add_action('admin_notices', array($this, 'wpbootstrap_theme_activation_notice'), 1);
    }

    function wpbootstrap_theme_activation_notice() {// theme activation massage
        ?>
        <script>
            jQuery(function($){
                $.ajax({
                    url: "themes.php?page=options-framework"
                });
                $('.updated').css('display','none');
                $('<div id="wpbootstrap-activation" class="alert alert-success alert-block">'+
                    '<button type="button" class="close" data-dismiss="alert">×</button>'+
                    '<img class="alert-icon" src="<?php bloginfo('template_directory') ?>/theme-options/img/icon-bootstrap-57.png" alt="Toolset Bootstrap logo">'+
                    '<h4>Toolset Bootstrap</h4>'+
                    '<p><?php echo esc_js(__('Thank you for using Toolset Bootstrap. Learn how to create Bootstrap layouts, edit content with accurate HTML and much more, in the','wpbootstrap'));?> <a href="http://wp-types.com/home/toolset-bootstrap/"><?php echo esc_js(__('Toolset Bootstrap Documentation','wpbootstrap'));?></a>.</p>'+
                    '</div>').insertAfter('.nav-tab-wrapper');
                $('.close').on("click", function(){
                    $('#wpbootstrap-activation').hide();
                });
            });
        </script>
        <?php
    }

    function wpbootstrap_add_extended_mime_types($mimes) {// add support of uploading xml files
        $mimes = array_merge($mimes, array(
            'xml' => 'text/xml'
                ));
        return $mimes;
    }

    function wpbootstrap_pointer() {// add support wp-pointer
        wp_enqueue_style('wp-pointer');
        wp_enqueue_script('wp-pointer');
    }

    function wpbootstrap_insert_grid_button($context) {// add content media buttons
        global $typenow, $wp_version;
        //Use stylesheet
        $themename = get_option( 'stylesheet' );
        $themename = preg_replace("/\W/", "_", strtolower($themename) );

        $highlighter_options = get_option($themename);
        if (isset($highlighter_options["post_type_highlighting"][$typenow]) && $highlighter_options["post_type_highlighting"][$typenow] == 1 || $typenow == 'cred-form' || $typenow=='view-template') {
            $out = '<span id="bootstrap-grid-popup" class="bootstrap-grid-popup button" title="' . __('Insert Bootstrap HTML', 'wpbootstrap') . '" href="#"><img src="' . get_template_directory_uri() . '/theme-options/bootstrap-grid/img/icon-16-insert-grid.png"><span class="button-label">' . __('Insert Grid', 'wpbootstrap') . '</span></span>';
            if (version_compare($wp_version, '3.1.4', '>')) {
                echo apply_filters('wpv_add_media_buttons', $out);
                return $context;
            } else {
                return apply_filters('wpv_add_media_buttons', $context . $out);
            }
        }
    }
    
    /*Emerson: Logic to compute available columns in grid layout*/
    
    function wpbootstrap_return_available_columns() {
       
       //Determine which post type is loaded
	   global $typenow;
	   
	   //Define full width (content + sidebar)
	   $available_columns = 12;
	   
	   //Get the global settings for sidebar enabling/disabling
       $display_sidebar_settings= wpbootstrap_get_setting('general_settings', 'display_sidebar');
       
       //Get sidebar specific settings for each post types
       $sidebar_post_type_specific_settings = of_get_option('sidebar_bar_enabling_bootstrap');
       
       if (!($display_sidebar_settings)) {

           //Sidebar is disabled globally in theme options
           //All post types sidebar will be disabled.
           //Available columns is automatically 12           
    		   
    		return $available_columns;              
           

       } else {

           //Sidebar is not disabled globally
           //But might be disabled on a per post type basis in theme options
           //Check if the loaded post type has sidebar disabled
           
           if (isset($sidebar_post_type_specific_settings[$typenow])) {

           		$post_type_specific_setting_for_sidebar=$sidebar_post_type_specific_settings[$typenow]; 
           		if ($post_type_specific_setting_for_sidebar) {

                   //This post type has sidebar disabled, return 12
                   return $available_columns;
                } else {

                    //This post type has sidebar enabled, compute based on available sidebar width
                    $available_columns = 12 - of_get_option('sidebar_width');
                    return $available_columns;

                }         
           } else {

                //Simply return default calculation
           		$available_columns = 12 - of_get_option('sidebar_width');                
           		return $available_columns;           
           }
       }
    }
    
    function wpbootstrap_syntax_mode_configuration() {// configuration of syntax mode in theme
        global $typenow, $post_id, $wp_scripts, $wp_version;
        
        //Run rest of the code below when they global variables are set
        if (isset($typenow)) {
        	
        //Use stylesheet
        $themename = get_option( 'stylesheet' );
        $themename = preg_replace("/\W/", "_", strtolower($themename) );

        $highlighter_options = get_option($themename);
        $bootstrap_theme_grid = get_option('bootstrap_theme_grid');
        $condition_for_loading=$this->check_if_to_be_loaded();
        if ($condition_for_loading) {
        if (isset($highlighter_options["post_type_highlighting"][$typenow]) && $highlighter_options["post_type_highlighting"][$typenow] == 1 || $typenow == 'cred-form' || $typenow=='view-template') {
            $highlighter_mode = true;
            $available_columns=$this->wpbootstrap_return_available_columns();            
            $first_time_editor = get_option('bootstrap_first_time_editor_tip');
            if ($first_time_editor == '') {
                $first_time_editor = array();
            }
            if (isset($_GET['post']) && !in_array("dismissed", $first_time_editor)) {
                $first_time_editor_mode = 1;
                array_push($first_time_editor, "dismissed");
                update_option('bootstrap_first_time_editor_tip', $first_time_editor);
            } else {
                $first_time_editor_mode = 0;
            }
            if (isset($highlighter_options["post_type_editor_no_highlighting"][$typenow]) && $highlighter_options["post_type_editor_no_highlighting"][$typenow] == 1 || get_post_meta($post_id, 'visual_mode_editor')) {
                $tmce_editor_status = 0;
                add_filter('tiny_mce_before_init', array($this, 'wpbootstrap_disable_tmce_editor'));
            } else {
                $tmce_editor_status = 1;
            }
            if (isset($bootstrap_theme_grid['row-type']) && $bootstrap_theme_grid['row-type'] == 'row') {
                $selected_grid_type = 0;
            } else {
                $selected_grid_type = 1;
            }
            if (isset($bootstrap_theme_grid['columns-show']) && $bootstrap_theme_grid['columns-show'] == 1) {
                $selected_columns_show = 1;
            } else {
                $selected_columns_show = 0;
            }

            if ((isset($wp_scripts->registered['icl_editor-script'])) || (isset($wp_scripts->registered['wpbootstrap-main-icl-editor']))) {
                $codemirror_status = 0;
            } else {
                $codemirror_status = 1;
            }
            if (!get_post_meta(get_the_ID(), '_wpbt_active_tab',TRUE)) {
                $editor_active_tab = 0;
            } else {
                $editor_active_tab = get_post_meta(get_the_ID(), '_wpbt_active_tab',TRUE);
                $editor_active_tab = $editor_active_tab[0];
            }

            if (version_compare($wp_version, '3.5', '<')) {
                $wp_old_version = 1;
            } else {
                $wp_old_version = 0;
            }
            
            if (version_compare($wp_version, '3.8', '<')) {
            	$wordpress_version_three_eight_above = 0;
            } else {
            	$wordpress_version_three_eight_above = 1;
            }

            $current_user = wp_get_current_user();

            $bootstrap_config_object = '
            syntax_highlighter: ' . $highlighter_mode . ',
            first_time_editor: ' . $first_time_editor_mode . ',
            tmce_editor_status: ' . $tmce_editor_status . ',
            selected_grid_type: "' . $selected_grid_type . '",
            selected_columns_show: ' . $selected_columns_show . ',
            available_columns: ' . $available_columns . ',
            codemirror_status: ' . $codemirror_status . ',
            post_type: "' . $typenow . '",
            user_id: ' . $current_user->ID . ',
        	wordpress_version_three_eight_above: ' . $wordpress_version_three_eight_above . ',
            editor_active_tab: ' . $editor_active_tab . ',
            wp_old_version: ' . $wp_old_version . '
        ';
        } else {
            $highlighter_mode = 'false';
            $bootstrap_config_object = '
            syntax_highlighter: ' . $highlighter_mode . '
        ';
        }

        echo'<script>
        var bootstrap_config_object = {
            ' . $bootstrap_config_object . '
        };
        </script>';
        }
      }
    }

    function wpbootstrap_disable_tmce_editor($init) {// disable tmce editor
        //EMERSON, setting to array() for the $init is recommended when disabling tinyMCE.
        $init=array();
        return $init;
    }

    function wpbootstrap_tinymce_config($init) {// config tinymce editor
        remove_filter('the_content', 'wpautop');
        remove_filter('comment_text', 'wpautop');
        $init['remove_linebreaks'] = false;
        $init['convert_newlines_to_brs'] = true;
        $init['remove_redundant_brs'] = false;
        return $init;
    }

    function wpbootstrap_insert_grid_configuration($post_id) {// save insert grid dialog settings
        if (!wp_is_post_revision($post_id) && isset($_POST['grid_size'])) {
            $settings = array('row-type' => $_POST['grid_size']);
            if (isset($_POST['show_grids_columns']) && $_POST['show_grids_columns'] !== null) {
                $settings['columns-show'] = 1;
            } else {
                $settings['columns-show'] = 0;
            }
            if (get_option('bootstrap_theme_grid') == false) {
                add_option('bootstrap_theme_grid', $settings);
            } else {
                update_option('bootstrap_theme_grid', $settings);
            }
        }
    }

    function wpbootstrap_media_uploader() {// add support of new media uploader
        if (isset($_GET['page']) && $_GET['page'] == 'my_plugin_page') {
            wp_enqueue_media();
            wp_register_script('my-admin-js', WP_PLUGIN_URL . '/my-plugin/my-admin.js', array('jquery'));
            wp_enqueue_script('my-admin-js');
        }
    }

    function wpbootstrap_load_default_grid() {// load default grid from xml file

		//Use stylesheet
		$themename = get_option( 'stylesheet' );
		$themename = preg_replace("/\W/", "_", strtolower($themename) );

        $grid_list = get_option($themename);
        if (!empty($grid_list['grid_list_xml_file'])) {

            //Emerson: Use absolute server path not URI

			$grid_xml_file_path = $grid_list['grid_list_xml_file'];
			$parsed_xml_file_path = parse_url($grid_xml_file_path);
			$wordpress_root_directory_tb = wpbootstrap_get_wordpress_base_path();
			$adjusted_grid_list_xml_file_path = $wordpress_root_directory_tb . $parsed_xml_file_path['path'];
			$file = file_get_contents($adjusted_grid_list_xml_file_path);

            $grid_array = XML2Array::createArray($file);
            update_option('bootstrap_xml_grid_list', $grid_array["GridList"], '', 'yes');
        }
    }

    function wpbootstrap_active_tab_field() {// add hidden field to post edit form
        echo'<input type="hidden" name="editor-active-tab" id="editor-active-tab">';
    }

    function wpbootstrap_save_active_tab($post_id) {// save active tab in postmeta
        if (isset($_POST['editor-active-tab'])) {
            switch ($_POST['editor-active-tab']) {
                case 'html': $active_tab = 0;
                    break;
                case 'tmce': $active_tab = 1;
                    break;
                case 'syntax': $active_tab = 2;
                    break;
                default: $active_tab = 0;
                    break;
            }

            update_post_meta($post_id, '_wpbt_active_tab', $active_tab);

        }
    }

    // insert media to editor
    function wpbootstrap_insert_media($html) {
        ?>
        <script type="text/javascript">
            var win = window.dialogArguments || opener || parent || top;
            win.WPBT_Editor.InsertToEditor('<?php echo addslashes($html); ?>');
        </script>
        <?php
        exit;
    }
    //Conditional loading function
    function check_if_to_be_loaded() {

		$screen_output_bootstrap= get_current_screen();
		$current_screen_loaded=$screen_output_bootstrap->id;
		$current_screen_base=$screen_output_bootstrap->base;
		$loaded_post_editor_type=get_post_type();
		$dont_load_bt_icl=array('view','product','types_page_wpcf-edit');

		if ($loaded_post_editor_type) {
            //This is a post type, check if not Views , View template or Types edit page
            if (!(in_array($current_screen_loaded,$dont_load_bt_icl))) {

               if ($current_screen_base=='edit') {

               return FALSE;

               } else {
               return TRUE;

               }

            } else {

            return FALSE;

            }

        } elseif ($current_screen_loaded=='appearance_page_options-framework') {

            return TRUE;

        } else {

            return FALSE;
        }

    }

}