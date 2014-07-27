<?php
class WPDD_layout_loop_views_cell extends WPDD_layout_cell {

	function __construct($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		parent::__construct($name, $width, $css_class_name, 'post-loop-views-cell', $content, $css_id, $tag);
		$this->set_cell_type('post-loop-views-cell');
	}

	function frontend_render_cell_content($target) {
		
		global $ddl_fields_api;
		$ddl_fields_api->set_current_cell_content($this->get_content());
		
        if( function_exists('render_view') )
        {
            $target->cell_content_callback( render_view( array( 'id' => get_ddl_field('ddl_layout_view_id') ) ) );
        }
        else
        {
            $target->cell_content_callback( WPDDL_Messages::views_missing_message() );
        }

	}
}

class WPDD_layout_loop_views_cell_factory extends WPDD_layout_cell_factory{

	public function build($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		return new WPDD_layout_loop_views_cell($name, $width, $css_class_name, $content, $css_id, $tag);
	}

	public function get_cell_info($template) {
		//$template['icon-url'] = WPDDL_RES_RELPATH .'/images/views-icon-color_16X16.png';
		//	$template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/child-layout.png';
        $template['icon-css'] = 'icon-views ont-color-orange ont-icon-22';
		$template['name'] = __('Views post loop', 'ddl-layouts');
		$template['description'] = __('A cell that displays a WordPress post loop using a View. This cell can be used for displaying a list of posts. eg. blogs, archives and search pages.', 'ddl-layouts');
		$template['button-text'] = __('Assign Views Post Loop cell', 'ddl-layouts');
		$template['dialog-title-create'] = __('Create a Views Post Loop cell', 'ddl-layouts');
		$template['dialog-title-edit'] = __('Edit Views Post Loop cell', 'ddl-layouts');
		$template['dialog-template'] = $this->_dialog_template();
		$template['allow-multiple'] = false;
		$template['cell-class'] = 'post-loop-views-cell';
		$template['category'] = __('Post display', 'ddl-layouts');
		$template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/views_post_loop.png';
		
		return $template;
	}

	public function get_editor_cell_template() {
		ob_start(); ?>
		<div class="cell-content">
			<p class="cell-name">{{ name }} &ndash; <?php _e('Displays a WordPress loop using Views', 'ddl-layouts'); ?></p>
			<div class="cell-preview">
				<#
					if (content) {
						var preview = ddl_views_content_grid_preview( content.ddl_layout_view_id, '<?php _e('Updating', 'ddl-layouts'); ?>...', '<?php _e('Loading', 'ddl-layouts'); ?>...' );
						print( preview );
					}
				#>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}


	private function _dialog_template() {
		global $WPV_templates, $WP_Views;

		$output = views_content_grid_dialog_template_callback();
		
		// Fix the help link for Views Post Loop cell
		$output = str_replace(WPDLL_VIEWS_CONTENT_GRID_CELL, WPDLL_VIEWS_LOOP_CELL, $output);
		
		$output = str_replace(__('Learn about the Views Content Grid cell', 'ddl-layouts'),
							  __('Learn about the Views Post Loop cell', 'ddl-layouts'),
							  $output);

		return $output;
	}

	public function enqueue_editor_scripts() {
	}
}

