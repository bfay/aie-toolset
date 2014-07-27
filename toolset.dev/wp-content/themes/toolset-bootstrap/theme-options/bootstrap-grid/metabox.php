<?php
class WPBT_metabox {

    function __construct() {
        //disable/enable metabox for CPT
        add_action('admin_init', array($this, 'wpbootstrap_syntax_mode_metabox'));

        // save metabox CPT option
        add_action('save_post', array($this, 'wpbootstrap_syntax_mode_metabox_save'));
    }

    function wpbootstrap_syntax_mode_metabox() {//disable/enable metabox for CPT
    	//Use stylesheet
    	$themename = get_option( 'stylesheet' );
    	$themename = preg_replace("/\W/", "_", strtolower($themename) );

        $highlighter_options = get_option($themename);
        foreach (WPBT_theme::wpbootstrap_cpt_support() as $posttype) {
            if (isset($highlighter_options["post_type_editor_no_highlighting"][$posttype]) && $highlighter_options["post_type_editor_no_highlighting"][$posttype] == 0) {

                if (isset($highlighter_options["post_type_highlighting"][$posttype]) && $highlighter_options["post_type_highlighting"][$posttype] == 1) {
                    //Enabled post type highlighting, show "Disable Visual Editor" meta box
                    add_meta_box('syntax-mode-configuration', __('Toolset Bootstrap', 'wpbootstrap'), array($this, 'wpbootstrap_syntax_mode_metabox_content'), $posttype, 'side', 'default');
                }
            }
        }
    }

    function wpbootstrap_syntax_mode_metabox_content() { // show metabox CPT content
        $checked = "";
        if (isset($_GET['post']) && get_post_meta($_GET['post'], 'visual_mode_editor') != false) {
            $checked = ' checked="checked" ';
        }
        echo '<input type="checkbox" id="visual_mode_editor" name="visual_mode_editor" ' . $checked . '/><label for="visual_mode_editor"> ' . __("Disable Visual Editor", 'wpbootstrap') . '</label> <div class="option help-holder"><span class="ico-questionmark"></span></div>';
    }

    function wpbootstrap_syntax_mode_metabox_save($post_id) { // save metabox CPT option
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!isset($_POST['post_type'])) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['visual_mode_editor'])) {
            if (!get_post_meta($post_id, 'visual_mode_editor')) {
                update_post_meta($post_id, 'visual_mode_editor', 1);
            }
        }
        else {
            delete_post_meta($post_id, 'visual_mode_editor');
        }
    }

}