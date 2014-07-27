<?php

class WPDD_layout_cell_text extends WPDD_layout_cell {

	function __construct($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		parent::__construct($name, $width, $css_class_name, 'cell-text-template', $content, $css_id, $tag);

		$this->set_cell_type('cell-text');
	}

	function frontend_render_cell_content($target) {
		$content = $this->get('content');
		if ($this->get('responsive_images')) {
			// stript hieght="xx" and width="xx" from images.
			$regex = '/<img[^>]*?(width="[^"]*")/siU';
			if(preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $val) {
					$found = str_replace($val[1], '', $val[0]);
					$content = str_replace($val[0], $found, $content);
				}
			}
			$regex = '/<img[^>]*?(height="[^"]*")/siU';
			if(preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $val) {
					$found = str_replace($val[1], '', $val[0]);
					$content = str_replace($val[0], $found, $content);
				}
			}

			// Process the caption shortcode
			$regex = '/\[caption.*?\[\/caption\]/siU';
			if(preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
				foreach ($matches as $val) {
					$shortcode = $val[0];
					$result = do_shortcode($shortcode);

					// set the generated div to 100% width
					$regex = '/<div[^>]*?width:([^"^;]*?)/siU';
					if(preg_match_all($regex, $result, $new_matches, PREG_SET_ORDER)) {
						foreach ($new_matches as $val) {
							$found = str_replace($val[1], '100%', $val[0]);
							$result = str_replace($val[0], $found, $result);
						}
					}
					$content = str_replace($shortcode, $result, $content);
				}

			}

			$content = $target->make_images_responsive($content);

		}
		
		if ($this->get('disable_auto_p') && has_filter('the_content', 'wpautop')) {
			remove_filter('the_content', 'wpautop');
			$content = apply_filters( 'the_content', $content );
			add_filter('the_content', 'wpautop');
		} else {
			$content = apply_filters( 'the_content', $content );
		}

		$target->cell_content_callback($content);
	}

}

class WPDD_layout_cell_text_factory extends WPDD_layout_cell_factory{

	public function build($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		return new WPDD_layout_cell_text($name, $width, $css_class_name, $content, $css_id, $tag);
	}

	public function get_cell_info($template) {
		$template['icon-css'] = 'icon-font';
		$template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/rich-content.png';
		$template['name'] = __('Visual editor (text, images, HTML)', 'ddl-layouts');
		$template['description'] = __('The Visual editor box can contain any HTML code or plain text.', 'ddl-layouts');
		$template['button-text'] = __('Assign Visual editor box', 'ddl-layouts');
		$template['dialog-title-create'] = __('Create a Visual editor Cell', 'ddl-layouts');
		$template['dialog-title-edit'] = __('Edit Visual editor Cell', 'ddl-layouts');
		$template['dialog-template'] = $this->_dialog_template();

		return $template;
	}

	public function get_editor_cell_template() {
		ob_start();
		?>
			<div class="cell-content">

				<p class="cell-name">{{ name }} &ndash; <?php _e('Visual editor Cell', 'ddl-layouts'); ?></p>

				<# if( content.content ){ #>
					<div class="cell-preview">
						<#
						var preview = content.content;
						if (typeof content.disable_auto_p != 'undefined' && !content.disable_auto_p) {
							// display the content with auto paragraphs
							preview = window.switchEditors.wpautop(preview);
						}
						preview = DDL_Helper.sanitizeHelper.stringToDom( preview );
						print( preview.innerHTML );
						#>
					</div>
				<# } #>
			</div>
		<?php
		return ob_get_clean();
	}

	public function enqueue_editor_scripts() {
		wp_enqueue_script('page');
		wp_enqueue_script('editor');
		add_thickbox();
		wp_enqueue_script('media-upload');
		wp_enqueue_script('word-count');

		wp_register_script('text_cell_js', WPDDL_RELPATH . '/inc/gui/editor/js/text-cell.js', array('jquery'), WPDDL_VERSION, true);
		wp_enqueue_script('text_cell_js');

	}


	private function _dialog_template() {
		add_filter('user_can_richedit', array(__CLASS__, '__true'), 100);
		ob_start();

		?>
			<div class="ddl-form">

				<div class="ddl-form-item">
					<fieldset>
						<p class="fields-group">
							<label class="checkbox" for="<?php the_ddl_name_attr('responsive_images'); ?>">
								<input type="checkbox" name="<?php the_ddl_name_attr('responsive_images'); ?>" id="<?php the_ddl_name_attr('responsive_images'); ?>">
								<?php _e('Display images with responsive size', 'ddl-layouts'); ?>
							</label>
						</p>
					</fieldset>
				</div>

				<div class="ddl-form-item">
					<fieldset>
						<p class="fields-group">
							<label class="checkbox" for="<?php the_ddl_name_attr('disable_auto_p'); ?>">
								<input type="checkbox" name="<?php the_ddl_name_attr('disable_auto_p'); ?>" id="<?php the_ddl_name_attr('disable_auto_p'); ?>">
								<?php _e('Disable automatic paragraphs', 'ddl-layouts'); ?>
							</label>
						</p>
						
					</fieldset>
				</div>
				
			</div>
		<?php

		$options = array(
			'textarea_name' => $this->element_name('content'),
			'dfw' => true,
			'drag_drop_upload' => true,
			'tabfocus_elements' => 'insert-media-button,save-post',
			'editor_height' => 360,
			'tinymce' => array(
				'resize' => false,
				'add_unload_trigger' => false,
			)
		);
		wp_editor( '', 'celltexteditor', $options );
		remove_filter('user_can_richedit', array(__CLASS__, '__true'), 100);

		?>		
			<div class="ddl-form">
				<div class="ddl-form-item">
					<br />
					<?php ddl_add_help_link_to_dialog(WPDLL_RICH_CONTENT_CELL,
													  __('Learn about the Rich content cell', 'ddl-layouts'));
					?>
				</div>
			</div>
		<?php
		
		return ob_get_clean();
	}

	// auxiliary functions
	public static function __true()
	{
		return true;
	}

	public static function __false()
	{
		return false;
	}


}

add_filter('dd_layouts_register_cell_factory', 'dd_layouts_register_cell_text_factory');
function dd_layouts_register_cell_text_factory($factories) {
	$factories['cell-text'] = new WPDD_layout_cell_text_factory;
	return $factories;
}
