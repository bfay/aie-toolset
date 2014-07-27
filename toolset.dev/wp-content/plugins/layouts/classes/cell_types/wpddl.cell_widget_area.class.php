<?php

class WPDD_layout_cell_widget_area extends WPDD_layout_cell {

	function __construct($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		parent::__construct($name, $width, $css_class_name, 'cell-widget-area-template', $content, $css_id, $tag);

		$this->set_cell_type('cell-widget-area');
	}

	function frontend_render_cell_content($target) {
		ob_start();

		$widget_area = $this->get('widget_area');
		if ($widget_area != '') {
			dynamic_sidebar($widget_area);
		}

		$content = ob_get_clean();

		$target->cell_content_callback($content);
	}

}

class WPDD_layout_cell_widget_area_factory extends WPDD_layout_cell_factory{

	public function build($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		return new WPDD_layout_cell_widget_area($name, $width, $css_class_name, $content, $css_id, $tag);
	}

	public function get_cell_info($template) {
		$template['icon-css'] = 'icon-cogs';
		$template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/widget-area.png';
		$template['name'] = __('Widget Area', 'ddl-layouts');
		$template['description'] = __('A cell that displays a WordPress Widget area.', 'ddl-layouts');
		$template['button-text'] = __('Assign Widget Area Box', 'ddl-layouts');
		$template['dialog-title-create'] = __('Create a new Widget Area Cell', 'ddl-layouts');
		$template['dialog-title-edit'] = __('Edit Widget Area Cell', 'ddl-layouts');
		$template['dialog-template'] = $this->_dialog_template();
		$template['category'] = __('WordPress UI', 'ddl-layouts');
		return $template;
	}

	public function get_editor_cell_template() {
		ob_start();
		?>
			<div class="cell-content">
				<p class="cell-name">{{ name }}</p>
			<# if( content.widget_area ){ #>
				<div class="cell-preview">{{ content.widget_area }}</div>
			<# } #>
			</div>
		<?php
		return ob_get_clean();
	}

	public function enqueue_editor_scripts() {
		wp_register_script( 'wp-widget-area-editor', ( WPDDL_GUI_RELPATH . "editor/js/widget-area.js" ), array('jquery'), null, true );
		wp_enqueue_script( 'wp-widget-area-editor' );
	}

	private function _dialog_template() {

		global $wp_registered_sidebars;

		ob_start();
		?>

		<div class="ddl-form">
			<p class="js-widget-area-select">
				<label for="<?php echo $this->element_name('widget_area'); ?>"><?php _e('Select an existing widget area', 'ddl-layouts'); ?></label>
				<select name="<?php echo $this->element_name('widget_area'); ?>">
				<?php foreach($wp_registered_sidebars as $sidebar): ?>
					<option val="<?php echo $sidebar['id']; ?>"><?php echo $sidebar['name']; ?></option>
				<?php endforeach; ?>
				</select>
			</p>

			<?php ddl_add_help_link_to_dialog(WPDLL_WIDGET_AREA_CELL,
											  __('Learn about the Widget Area cell', 'ddl-layouts'));
			?>
			
			<?php // Don't show the create new widget area button in this version
				  // We'll complete this later ?>
			<p class="js-create-new-widget-area-button ddl-form hidden">
				<label><?php _e('Or', 'ddl-layouts'); ?></label>
				<button class="js-create-new-sidebar button-secondary"><?php _e('Create a new widget area', 'ddl-layouts'); ?></button>
			</p>
		</div>

		<ul class="create-new-sidebar-div js-create-new-sidebar-div ddl-form hidden">
			<li>
				<label for="ddl-sidebar-name"><?php _e('Name:', 'ddl-layouts'); ?></label>
				<input type='text' name="ddl-sidebar-name">
			</li>
			<li>
				<label for="ddl-sidebar-description"><?php _e('Description:', 'ddl-layouts'); ?></label>
				<input type='text' name="ddl-sidebar-description">
			</li>
			<li>
				<label for="ddl-sidebar-class"><?php _e('Class:', 'ddl-layouts'); ?></label>
				<input type='text' name="ddl-sidebar-class">
			</li>
			<li>
				<label for="ddl-sidebar-before-widget"><?php _e('Before widget:', 'ddl-layouts'); ?></label>
				<input type='text' name="ddl-sidebar-before-widget" value="">
			</li>
			<li>
				<label for="ddl-sidebar-after-widget"><?php _e('After widget:', 'ddl-layouts'); ?></label>
				<input type='text' name="ddl-sidebar-after-widget" value="">
			</li>
			<li>
				<label for="ddl-sidebar-before-title"><?php _e('Before title:', 'ddl-layouts'); ?></label>
				<input type='text' name="ddl-sidebar-before-title" value="">
			</li>
			<li>
				<label for="ddl-sidebar-after-title"><?php _e('After title:', 'ddl-layouts'); ?></label>
				<input type='text' name="ddl-sidebar-after-title" value="">
			</li>
			<li>
				<a href="http://codex.wordpress.org/Function_Reference/register_sidebar" target="_blank"><?php _e('Information about these settings', 'ddl-layouts'); ?></a>
			</li>

		</ul>

		<?php // TODO: Review this. I don't understand why this footer is displayed here ?>
		<div class="ddl-dialog-footer js-widget-area-footer hidden">
			<input type="button" class="button-secondary js-cancel-create-new-sidebar" value="<?php _e('Cancel', 'ddl-layouts'); ?>">
			<input type="button" class="button-primary js-create-the-new-sidebar js-save-dialog-settings" value="<?php _e('Create the new widget area', 'ddl-layouts'); ?>">
		</div>

		<?php

		return ob_get_clean();
	}


}

add_filter('dd_layouts_register_cell_factory', 'dd_layouts_register_cell_widget_area_factory');
function dd_layouts_register_cell_widget_area_factory($factories) {
	$factories['cell-widget-area'] = new WPDD_layout_cell_widget_area_factory;
	return $factories;
}
