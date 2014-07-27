<?php
/* 
 * Messages.
 */

function wpvdemo_error_message($error, $wrap = false) {
    $messages = array(
        'connect' => sprintf(__('Connecting to %s failed', 'wpvdemo'),
                WPVDEMO_URL),
        'data' => __('Configuration data is corrupted', 'wpvdemo'),
        'site_configuration_missing' => __('Missing configuration data for %s',
                'wpvdemo'),
        'importing_types' => __('Error importing Types', 'wpvdemo'),
        'importing_views' => __('Error importing Views', 'wpvdemo'),
    	'importing_cred' => __('Error importing CRED', 'wpvdemo'),
    	'importing_wpml' => __('Error importing WPML', 'wpvdemo'),
    	'importing_classifieds_woocommerce' =>__('Error importing WooCommerce settings for Classifieds Site', 'wpvdemo'),
    	'importing_access' => __('Error importing Access', 'wpvdemo'),
    	'importing_classifieds_user_roles'=> __('Error importing user roles for Classifieds Site', 'wpvdemo'),
    	'importing_cred_custom_fields_classifieds' => __('Error importing CRED custom fields for Classifieds Site', 'wpvdemo'),
        'download_theme' => __('Error downloading theme %s', 'wpvdemo'),
        'download_theme_parent' => __('Error downloading parent theme %s',
                'wpvdemo'),
        'plugin_activation' => __('Error activating plugin %s: %s', 'wpvdemo'),
        'required_plugin_warning' => __('- not found in the plugins directory'),
        'required_plugins_disabled_download' => __('You cannot download this demo because some required plugins are not available. Please download and place them in the Plugins directory, but do not activate the plugins.%s'),
    );
    if ($wrap && isset($messages[$error])) {

        switch ($error) {
            default:
                return '<div class="message error"><p>'
                    . $messages[$error]
                    . '</p></div>';
            
            case 'required_plugin_warning':
            case 'required_plugins_disabled_download':
                return '<div class="wpvdemo-error"><p>'
                    . $messages[$error]
                    . '</p></div>';
        }
    }
    return isset($messages[$error]) ? $messages[$error] : '';
}


/**
 * Admin notice. 
 */
function wpvdemo_check_if_blank_site_message() {
    $message = apply_filters('wpvdemo_blank_site_message',
            __("To be on the safe side, content import only works on fresh sites. We really don't want to accidentally delete content on live sites. To use this content importer, please install a fresh WordPress site and run it there.",
                    'wpvdemo'));
    if ($message != '') {
        echo '<div class="message error wpvdemo-warning"><p style="margin-left:40px;">'
                . $message . '</p></div>';
    }
}


/**
 * Admin notice. 
 */
function wpvdemo_requirements_themes_writeable_error_message() {
    echo '<div class="message error"><p>'
    . sprintf(__("The theme directory is not writable and the theme for the demo sites can’t be installed automatically. Please change the ownership of directory <strong>%s</strong> so that the web server can write to it.",
            'wpvdemo'), get_theme_root()) . '<br /><br /><a href="http://wp-types.com/documentation/views-demos-downloader/" target="_blank">' . __("Instructions for setting up demo sites",
            'wpvdemo') . '</a></p></div>';
}

/**
 * Admin notice. 
 */
function wpvdemo_requirements_media_writeable_error_message() {
    $wp_upload_dir = wp_upload_dir();
    if (isset($wp_upload_dir['error'])) {
        $wp_upload_dir['basedir'] = WP_CONTENT_DIR . '/uploads';
    }
    echo '<div class="message error"><p>'
    . sprintf(__("The media directory is not writable and the images for the demo sites can’t be installed. Please change the ownership of directory <strong>%s</strong> so that the web server can write to it.",
            'wpvdemo'), $wp_upload_dir['basedir']) . '<br /><br /><a href="http://wp-types.com/documentation/views-demos-downloader/" target="_blank">' . __("Instructions for setting up demo sites",
            'wpvdemo') . '</a></p></div>';
}

/**
 * Admin notice. 
 */
function wpvdemo_requirements_dirs_writeable_error_message() {
    $wp_upload_dir = wp_upload_dir();
    if (isset($wp_upload_dir['error'])) {
        $wp_upload_dir['basedir'] = WP_CONTENT_DIR . '/uploads';
    }
    echo '<div class="message error"><p>'
    . sprintf(__("The theme and media directories are not writable and the theme and images for the demo sites can’t be installed automatically. Please change the ownership of directory <strong>%s</strong> and <strong>%s</strong> so that the web server can write to them.",
            'wpvdemo'), get_theme_root(), $wp_upload_dir['basedir']) . '<br /><br /><a href="http://wp-types.com/documentation/views-demos-downloader/" target="_blank">' . __("Instructions for setting up demo sites",
            'wpvdemo') . '</a></p></div>';
}
function wpvdemo_requirements_wpcontent_writeable_error_message() {

	if (defined('WPVDEMO_WPCONTENTDIR')) {
		$user_wpcontent=WPVDEMO_WPCONTENTDIR;		
	
		echo '<div class="message error"><p>'
			. sprintf(__("The WordPress wp-content directory is not writable and the modules for the demo sites can’t be installed automatically. Please assign proper permissions and ownership of the directory <strong>%s</strong> so that the web server can write to them. If you have no idea about this, please check with your web host.",
					'wpvdemo'), $user_wpcontent).'<br /><br /><a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">' . __("Read WordPress guide on properly setting file permissions",
            'wpvdemo') . '</a></p></div>';
	
	}
}
/**
 * Admin notice. 
 */
function wpvdemo_requirements_zip_error_message() {
    echo '<div class="message error"><p>'
    . __("PHP ZipArchive extension missing.",
            'wpvdemo') . '</p></div>';
}
/**
 * Admin notice.
 */
function wpvdemo_disabled_native_PHP_remote_parsing_functions_message() {
	echo '<div class="message error"><p>'
			. __("Framework Installer plugin requires PHP allow_url_fopen to be enabled. Please enabled it in your php.ini. Contact your webhost if you are not sure how to change this.",
					'wpvdemo') . '</p></div>';
}
/**
 * Admin notice. 
 */
function wpvdemo_demo_message() {
    global $wpvdemo;
    
    if (isset($_POST['dismiss_wpvdemosite_notice'])) {
    	update_option('dismiss_wpvdemosite_notice', 'yes');
    }
    
    $dismiss_wpvdemosite_notice=get_option('dismiss_wpvdemosite_notice');
    
    if ($dismiss_wpvdemosite_notice != 'yes') {
    echo '<div class="message updated"><p>'
    . sprintf(__("This is a test copy of the %s demo for Types and Views. Learn how to use it in the %s.",
                    'wpvdemo'), $wpvdemo['title'],
            '<a href="' . $wpvdemo['tutorial_url'] . '" target="_blank">' . $wpvdemo['tutorial_title'] . '</a>');
?>
<form action="" method="post">
			<input type="submit" name="dismiss_wpvdemosite_notice" value="<?php _e('Dismiss', 'wpvdemo') ?>" class="button-primary" />
</form>
</p>
</div>
<?php 
    }    
}