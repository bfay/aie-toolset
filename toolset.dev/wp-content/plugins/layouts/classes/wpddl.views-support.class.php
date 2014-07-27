<?php

class WPDDL_views_support
{

	public function __construct() {
		add_action( 'init', array($this, 'init'));
		add_action('admin_notices', array($this, 'admin_notice'));
		add_action('wp_ajax_dll_dismiss_views_notice', array($this, 'dismiss_notice'));
	}
	
	public function init() {
	}

	public function admin_notice() {
		global $current_user, $pagenow ;
		
		if (!defined('WPV_VERSION')) {
			
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			
			if ($pagenow == 'plugins.php' ||
					($pagenow == 'admin.php' && ($page == 'dd_layouts' || $page == 'dd_layouts_edit'))) {
				$user_id = $current_user->ID;
				/* Check that the user hasn't already clicked to ignore the message */
				if ( ! get_user_meta($user_id, 'views_required_ignore_notice') ) {
					?>
					
					<div class="update-nag">
						<p>
                            <i class="icon-views-logo ont-color-orange ont-icon-24"></i>&nbsp;<strong><span style="vertical-align: -6px"><?php _e('Layouts works best with the Views plugin.'); ?></span></strong>
						</p>
						<p>
							<?php _e('The Views plugin will allow you to customize the Post Content and Content Grid cells. If you have a Toolset account, you can download and activate <strong>Views</strong> plugin. If you only have a Layouts account, you can get the <strong>Embedded Views</strong> plugin with read-only cell content.', 'ddl-layouts'); ?>
						</p>
						<p>
							<a class="fieldset-inputs button button-primary-toolset" href="http://wp-types.com/home/views-create-elegant-displays-for-your-content?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=<?php echo $pagenow; ?>&utm_term=get-views" target="_blank">
								<?php _e('Get Views plugin', 'ddl-layouts');?>
							</a>
							&nbsp;&nbsp;
							<a class="ddl_dismiss_views_notice" href="#"><?php _e('Dismiss', 'ddl-layouts'); ?></a>
							
						</p>
					</div>
					
					<script type="text/javascript">
						jQuery('.ddl_dismiss_views_notice').on('click', function() {
							jQuery(this).closest('.update-nag').fadeOut(500);
							var data = {
								action : 'dll_dismiss_views_notice',
							};
				
							jQuery.ajax({
								type:'post',
								url:ajaxurl,
								data:data,
								success: function(response){
								}
							});
								
						});
					</script>
					<?php
				}
			}
		} else {
			if (!version_compare(WPV_VERSION, '1.6.1', '>')) {
				$page = isset($_GET['page']) ? $_GET['page'] : '';
				
				if ($pagenow == 'plugins.php' ||
						($pagenow == 'admin.php' && ($page == 'dd_layouts' || $page == 'dd_layouts_edit'))) {
					?>
						<div class="update-nag">
							<p>
                                <i class="icon-views-logo ont-color-orange ont-icon-24"></i>&nbsp;<strong><span style="vertical-align: -6px"><?php _e('Layouts requires version 1.6.2 or higher of the Views plugin.'); ?></span></strong>
							</p>
							<p>
								<a class="fieldset-inputs button button-primary-toolset" href="http://wp-types.com/home/views-create-elegant-displays-for-your-content?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=<?php echo $pagenow; ?>&utm_term=get-views" target="_blank">
									<?php _e('Get Views plugin', 'ddl-layouts');?>
								</a>
							</p>
						</div>
					<?php
				}
			}
		}
	}
	
	public function dismiss_notice () {
		global $current_user;
		
		$user_id = $current_user->ID;
		add_user_meta($user_id, 'views_required_ignore_notice', 'true', true);		
	}
	
}

global $wpddl_views_support;
$wpddl_views_support = new WPDDL_views_support();