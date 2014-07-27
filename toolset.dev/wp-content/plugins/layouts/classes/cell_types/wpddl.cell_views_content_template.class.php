<?php

class WPDD_layout_cell_post_content_views extends WPDD_layout_cell {

	function __construct($name, $width, $css_class_name = '', $content = null, $css_id = '') {
		parent::__construct($name, $width, $css_class_name, 'cell-content-template', $content, $css_id);

		$this->set_cell_type('cell-content-template');
	}

	function frontend_render_cell_content($target) {
		global $WPV_templates;
		
		$content = '';
		
		$cell_content = $this->get_content();
		
		if ($cell_content['page'] == 'current_page') {
			do_action('ddl-layouts-render-start-post-content');
		}
		
		
		if (isset($WPV_templates) && isset($cell_content['ddl_view_template_id']) && $cell_content['ddl_view_template_id'] != 'None') {
			$content_template_id = $cell_content['ddl_view_template_id'];
			if ($cell_content['page'] == 'current_page') {
				global $post;
				$content = render_view_template($content_template_id, $post );
			} elseif ($cell_content['page'] == 'this_page') {
				$post = get_post( $cell_content['selected_post'] );
				$content = render_view_template($content_template_id, $post );
			}
		}
        else{
            $content = WPDDL_Messages::views_missing_message();
        }

		$target->cell_content_callback($content);
	
		if ($cell_content['page'] == 'current_page') {
			do_action('ddl-layouts-render-end-post-content');
		}
		
	}

}

class WPDD_layout_cell_post_content_views_factory extends WPDD_layout_cell_factory{

	function __construct() {
		if( is_admin()){
			add_action('wp_ajax_dll_refresh_ct_list', array($this, 'get_ct_select_box'));
			add_action('wp_ajax_ddl_content_template_preview', array($this, 'get_content_template'));
			add_action('wp_ajax_ddl_ct_loader_inline_preview', array($this, 'get_ct_editor_preview'));
		}

	}

	public function build($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		return new WPDD_layout_cell_post_content_views($name, $width, $css_class_name, $content, $css_id, $tag);
	}

	public function get_cell_info($template) {
		//$template['icon-url'] = WPDDL_RES_RELPATH .'/images/views-icon-color_16X16.png';
        $template['icon-css'] = 'icon-views ont-color-orange ont-icon-22';
		$template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/post-content.png';
		$template['name'] = __('Content Template', 'ddl-layouts');
		$template['description'] = __('Displays the post content using a Views Content Template.', 'ddl-layouts');
		$template['button-text'] = __('Assign Content Template Box', 'ddl-layouts');
		$template['dialog-title-create'] = __('Create a new Content Template Cell', 'ddl-layouts');
		$template['dialog-title-edit'] = __('Edit Content Template Cell', 'ddl-layouts');
		$template['dialog-template'] = $this->_dialog_template();
		$template['category'] = __('Post display', 'ddl-layouts');
		return $template;
	}

	public function get_editor_cell_template() {
		ob_start();
		?>
			<div class="cell-content">
				<p class="cell-name">{{ name }}</p>
				<# if( content ) { #>
				<div class="cell-preview">
					<#
					var preview = DDLayout.content_template_cell.display_post_content_info(content, '<?php _e('Displays the content of the current page', 'ddl-layouts'); ?>', '<?php _e('Displays the content of %s', 'ddl-layouts'); ?>', '<?php _e('Loading...', 'ddl-layouts'); ?>');
					print( preview );
					#>
				</div>
			<# } #>
			</div>
		<?php
		return ob_get_clean();
	}

	private function _dialog_template() {
		global $WPV_templates, $WP_Views;

		$views_1_6_available = defined('WPV_VERSION') && version_compare(WPV_VERSION, '1.6.1', '>') && isset($WP_Views) && class_exists('WP_Views') && !$WP_Views->is_embedded();
		$views_1_6_embedded_available = defined('WPV_VERSION') && version_compare(WPV_VERSION, '1.6.1', '>') && isset($WP_Views) && class_exists('WP_Views') && $WP_Views->is_embedded();
		$view_tempates_available = $this->_get_view_templates_available();

		ob_start();
		?>
		<script type="text/javascript">
			var ddl_new_ct_default_name = '<?php echo __('Content Template for %s Layout', 'ddl-layouts'); ?>';
			var ddl_views_1_6_available = <?php echo $views_1_6_available ? 'true' : 'false'; ?>;
			var ddl_views_1_6_embedded_available = <?php echo $views_1_6_embedded_available ? 'true' : 'false'; ?>;
		</script>

		<ul class="ddl-form">
			<li>
				<fieldset>
					<legend><?php _e('Display content for:', 'ddl-layouts'); ?></legend>
					<div class="fields-group">
						<ul>
							<li>
								<label class="post-content-page">
									<input type="radio" name="<?php the_ddl_name_attr('page'); ?>" value="current_page" checked="checked"/>
									<?php _e('Current page', 'ddl-layouts'); ?>
								</label>
							</li>
							<li>
								<label class="post-content-page">
									<input type="radio" name="<?php the_ddl_name_attr( 'page' ); ?>" value="this_page" />
									<?php _e( 'A Specific page:', 'ddl-layouts' ); ?>
								</label>
							</li>
							<li id="js-post-content-specific-page">
								<select name="<?php the_ddl_name_attr( 'post_content_post_type' ); ?>" class="js-ddl-post-content-post-type" data-nonce="<?php echo wp_create_nonce( 'ddl-post-content-post-type-select' ); ?>">
									<option value="ddl-all-post-types"><?php _e('All post types', 'ddl-layouts'); ?></option>
									<?php
									$post_types = get_post_types( array( 'public' => true ), 'objects' );
									foreach ( $post_types as $post_type ) {
										$count_posts = wp_count_posts($post_type->name);
										if ($count_posts->publish > 0) {
											?>
												<option value="<?php echo $post_type->name; ?>"<?php if($post_type->name == 'page') { echo ' selected="selected"';} ?>>
													<?php echo $post_type->labels->singular_name; ?>
												</option>
											<?php
										}
									}
									?>
								</select>
								<?php
									$keys = array_keys( $post_types );
									$post_types_array = array_shift(  $keys  );
									$this->show_posts_dropdown( $post_types_array, get_ddl_name_attr( 'selected_post' ) );
								?>
							</li>
						</ul>
					</div>
				</fieldset>
			</li>

			<?php if ($views_1_6_available || (defined('WPV_VERSION') && sizeof($view_tempates_available) > 0)): ?>
			<li>
				<fieldset>
					<div class="fields-group">
						<ul>
							<li class="js-post-content-ct js-ct-selector js-ct-select-box">
								<?php echo $this->_get_view_template_select_box($view_tempates_available); ?>
							</li>
							<?php if ($views_1_6_available): ?>
								<li class="js-post-content-ct js-ct-selector">
									<?php _e('or', 'ddl-layouts'); ?> <a href="#" class="js-create-new-ct"><?php _e('Create a new one', 'ddl-layout'); ?></a>
								</li>
							<?php endif; ?>
						</ul>
					</div>
				</fieldset>
			</li>

			<?php endif; ?>


			<?php if( $views_1_6_available ): ?>
				<li class="js-post-content-ct js-ct-edit">
					<input class="js-ct-edit-name" type="text" style="float: left; width: 50%" /><span class="js-ct-editing"><strong><?php _e('Editing', 'ddl-layouts'); ?> :</strong> <span class="js-ct-name"></span></span> <a style="float: right" href="#" class="js-load-different-ct"><?php _e('Load a different Content Template', 'ddl-layouts'); ?></a>
				</li>
				<li class="js-post-content-ct js-ct-edit">
			        <div class="js-wpv-ct-inline-edit wpv-ct-inline-edit wpv-ct-inline-edit hidden"></div>
				</li>
			<?php else: ?>
				<li>
					<div class="toolset-alert toolset-alert-info">
						<?php if (sizeof($view_tempates_available) > 0): ?>
							<p>
								<i class="icon-views-logo ont-color-orange ont-icon-24"></i>
								<?php _e('This cell can display the post content using fields. Install and activate the Views plugin and you will be able to create Content Templates to display post fields.', 'ddl-layouts'); ?>
								<br>
								<br>
								<a class="fieldset-inputs button button-primary-toolset" href="http://wp-types.com/home/views-create-elegant-displays-for-your-content?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=content-template-cell&utm_term=get-views" target="_blank">
									<?php _e('Get Views plugin', 'ddl-layouts');?> &raquo;
								</a>
							</p>
						<?php else: ?>
							<p>
								<i class="icon-module-logo ont-color-orange ont-icon-24"></i>
								<?php _e('This cell can display the post content using fields and there are no Content Templates available.', 'ddl-layouts'); ?>
								<br />
								<?php _e('You can download pre-built modules using the Module Manager plugin.', 'ddl-layouts'); ?>
								<br />
								<br />
								<?php if (defined( 'MODMAN_CAPABILITY' )): ?>
									<a class="fieldset-inputs button button-primary-toolset" href="<?php echo admin_url('admin.php?page=ModuleManager_Modules&amp;tab=library&amp;module_cat=layouts'); ?>" target="_blank">
										<i class="icon-download-alt"></i> <?php _e('Download Modules', 'ddl-layouts');?>
									</a>
								<?php else: ?>
									<a class="fieldset-inputs button button-primary-toolset" href="http://wp-types.com/home/module-manager?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=content-template-cell&utm_term=get-module-manager" target="_blank">
										<?php _e('Get Module Manager plugin', 'ddl-layouts');?>
									</a>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>
				</li>
				<li class="js-post-content-ct js-ct-edit">
			        <div class="js-wpv-ct-inline-edit wpv-ct-inline-edit wpv-ct-inline-edit hidden"></div>
				</li>
				
			<?php endif; ?>

			

		</ul>
		<?php ddl_add_help_link_to_dialog(WPDLL_CONTENT_TEMPLATE_CELL, __('Learn about the Content Template cell', 'ddl-layouts')); ?>
		<?php wp_nonce_field( 'wpv-ct-inline-edit', 'wpv-ct-inline-edit' ); ?>

		<?php
		return ob_get_clean();
	}

	private function _get_view_templates_available() {
		global $wpdb;

		return $wpdb->get_results("SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_type='view-template' AND post_status in ('publish')");
	}

	private function _get_view_template_select_box($view_tempates_available) {

		// Add a "None" type to the list.
		$none = new stdClass();
		$none->ID = 0;
		$none->post_name = 'None';
		$none->post_title = __('None', 'ddl-layouts');
		array_unshift($view_tempates_available, $none);

		ob_start();
		?>
		<label for="post-content-view-template"><?php _e('Choose an existing Content Template:', 'ddl-layouts'); ?> </label>
		<select class="views_template_select" name="<?php echo $this->element_name('ddl_view_template_id'); ?>" id="post-content-view-template">';

		<?php
		foreach($view_tempates_available as $template) {
			$title = $template->post_title;
			if (!$title) {
				$title = $template->post_name;
			}

			?>
			<option value="<?php echo $template->ID; ?>" data-ct-id="<?php echo $template->ID; ?>" ><?php echo $template->post_title; ?></option>
			<?php
		}
		?>
		</select>

		<?php

		return ob_get_clean();
	}

	public function enqueue_editor_scripts() {
		wp_register_script( 'wp-content-template-editor', ( WPDDL_GUI_RELPATH . "editor/js/content-template-cell.js" ), array('jquery'), null, true );
		wp_enqueue_script( 'wp-content-template-editor' );

		wp_localize_script('wp-content-template-editor', 'DDLayout_content_template_strings', array(
				'current_post' => __('This cell will display the content of the post which uses the layout.', 'ddl-layouts'),
				'this_post' => __('This cell will display the content of a specific post.', 'ddl-layouts'),
				)
		);

	}

	private function show_posts_dropdown($post_type, $name, $selected = 0) {
		if ($post_type == 'ddl-all-post-types') {
			$post_type = 'any';
		}

		$attr = array('name'=> $name,
					  'post_type' => $post_type,
					  'show_option_none' => __('None', 'ddl-layouts'),
					  'selected' => $selected);


		add_filter('posts_clauses_request', array($this, 'posts_clauses_request_filter'), 10, 2 );

		$defaults = array(
			'depth' => 0, 'child_of' => 0,
			'selected' => $selected, 'echo' => 1,
			'name' => 'page_id', 'id' => '',
			'show_option_none' => '', 'show_option_no_change' => '',
			'option_none_value' => ''
		);
		$r = wp_parse_args( $attr, $defaults );
		extract( $r, EXTR_SKIP );

		$pages = get_posts(array('numberposts' => -1, 'post_type' => $post_type, 'suppress_filters' => false));
		$output = '';
		// Back-compat with old system where both id and name were based on $name argument
		if ( empty($id) )
			$id = $name;

		if ( ! empty($pages) ) {
			$output = "<select name='" . esc_attr( $name ) . "' id='" . esc_attr( $id ) . "' data-post-type='" . esc_attr( $post_type ). "'>\n";
			if ( $show_option_no_change )
				$output .= "\t<option value=\"-1\">$show_option_no_change</option>";
			if ( $show_option_none )
				$output .= "\t<option value=\"" . esc_attr($option_none_value) . "\">$show_option_none</option>\n";
			$output .= walk_page_dropdown_tree($pages, $depth, $r);
			$output .= "</select>\n";
		}

		echo $output;

		remove_filter('posts_clauses_request', array($this, 'posts_clauses_request_filter'), 10, 2 );

	}

	function posts_clauses_request_filter($pieces, $query ) {
		global $wpdb;
		// only return the fields required for the dropdown.
		$pieces['fields'] = "$wpdb->posts.ID, $wpdb->posts.post_parent, $wpdb->posts.post_title";

		return $pieces;
	}

	function get_ct_select_box () {
		if ( !isset($_POST["wpnonce"]) || !wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");

		$view_tempates_available = $this->_get_view_templates_available();
		echo $this->_get_view_template_select_box($view_tempates_available);

		die();
	}
	
	function get_content_template () {
		if ( !isset($_POST["wpnonce"]) || !wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");
		
		global $WPV_templates;
		if (isset($WPV_templates) && isset($_POST['view_template'])) {
			$content_template_id = $_POST['view_template'];
			$content = $WPV_templates->get_template_content($content_template_id);
			
			echo $content;
		}
		
		die();
		
	}
	
	function get_ct_editor_preview() {
	    if ( !isset($_POST["wpnonce"]) || !wp_verify_nonce($_POST["wpnonce"], 'wpv-ct-inline-edit') ) die("Undefined Nonce.");

		global $WPV_templates;
		if (isset($WPV_templates) && isset($_POST['id'])) {
			$content_template_id = $_POST['id'];
			$content = $WPV_templates->get_template_content($content_template_id);
			
			$content;
			?>
			<strong><?php _e('Content Template preview:', 'ddl-layouts'); ?></strong>
			<textarea name="name" rows="10" id="wpv-ct-inline-editor-<?php echo $content_template_id; ?>"><?php echo $content;?></textarea>
			<?php
		}
		
		die();
		
	}
}

add_filter('dd_layouts_register_cell_factory', 'dd_layouts_register_cell_post_content_views_factory');
function dd_layouts_register_cell_post_content_views_factory($factories) {
	$factories['cell-content-template'] = new WPDD_layout_cell_post_content_views_factory;
	return $factories;
}


