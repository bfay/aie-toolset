<?php

class WPDD_layout_cell_child_layout extends WPDD_layout_cell {

	function __construct($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		parent::__construct($name, $width, $css_class_name, 'child-layout', $content, $css_id, $tag);

		$this->set_cell_type('child-layout');
	}

	function frontend_render_cell_content($target) {

		$target->cell_content_callback($target->render_child());
	}

	function get_width_of_child_layout_cell() {
		return $this->get_width();
	}

}

class WPDD_layout_cell_child_layout_factory extends WPDD_layout_cell_factory{


	public function build($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		return new WPDD_layout_cell_child_layout($name, $width, $css_class_name, $content, $css_id, $tag);
	}

	public function get_cell_info($template) {
		$template['icon-css'] = 'icon-th';
		$template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/child-layout.png';
		$template['name'] = __('Child Layout', 'ddl-layouts');
		$template['description'] = __('This cell is a placeholder for a Child Layout used when creating Hierarchical Layouts.', 'ddl-layouts');
		$template['button-text'] = __('Assign Child Layout', 'ddl-layouts');
		$template['dialog-title-create'] = __('Create a Child Layout Cell', 'ddl-layouts');
		$template['dialog-title-edit'] = __('Edit Child Layout Cell', 'ddl-layouts');
		$template['dialog-template'] = $this->_dialog_template();
		$template['allow-multiple'] = false;
		$template['category'] = __('Grids', 'ddl-layouts');
		return $template;
	}

	public function get_editor_cell_template() {
		ob_start();
		?>
			<div class="cell-content">
				<p class="cell-name">{{{ name }}} &ndash; {{ kind }}</p>
				<div class="cell-preview"><?php _e('Child Layout', 'ddl-layouts'); ?></div>
			</div>
		<?php
		return ob_get_clean();
	}


	private function _dialog_template() {
		global $wpdb, $wpddlayout;
		ob_start();
		?>

				<?php if (isset($_GET['layout_id'])) {
					$layout = $wpddlayout->get_layout_settings($_GET['layout_id'], true);

					if ($layout && isset($layout->has_child) && ($layout->has_child === 'true' || $layout->has_child === true)) {

						?>
						<ul class="tree js-child-layout-list">
							 <li class="js-tree-category js-tree-category-title">
								<h3 class="tree-category-title">
									<?php _e( 'Select Child Layout for editing', 'ddl-layouts' ); ?>
								</h3>

								<ul class="js-tree-category-items">

									<?php

										$layout_slug = $layout->slug;

										$post_ids =  $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts'");
										foreach ($post_ids as $post_id) {
											$layout = $wpddlayout->get_layout_settings($post_id, true);
											if ( is_object($layout) && property_exists($layout, 'parent') && $layout->parent == $layout_slug ) {
												?>

												<li class="js-tree-category-item">
													<p class="item-name-wrap js-item-name-wrap">
														<a href="#" class="js-switch-to-layout" data-layout-id="<?php echo $post_id; ?>">
															<span class="js-item-name"><?php echo $layout->name; ?></span>
														</a>
													</p>
												</li> <!-- .js-tree-category-item -->

												<?php
											}

										}
									?>

									<?php // ( while ( has_child_ddl_layouts() ) : the_child_layout(); ) ?>
								</ul> <!-- . js-tree-category-items -->
							</li>
						</ul> <!-- .js-tree-category-items -->
						<?php
						}
					}?>

					<input type="button" class="button js-create-new-child-layout" data-url="<?php echo admin_url().'admin.php?page=dd_layouts&new_layout=true'; ?>" value="<?php _e('Create a new child layout', 'ddl-layouts'); ?>">
					
					<?php ddl_add_help_link_to_dialog(WPDLL_CHILD_LAYOUT_CELL,
												  __('Learn about the Child layout cell', 'ddl-layouts'));
					?>
					

		<?php
		return ob_get_clean();
	}

	public function enqueue_editor_scripts() {
		wp_register_script( 'wp-child-layout-editor', ( WPDDL_GUI_RELPATH . "editor/js/child-cell.js" ), array('jquery'), null, true );
		wp_enqueue_script( 'wp-child-layout-editor' );
	}


}

add_filter('dd_layouts_register_cell_factory', 'dd_layouts_register_cell_child_layout_factory');
function dd_layouts_register_cell_child_layout_factory($factories) {
	$factories['child-layout'] = new WPDD_layout_cell_child_layout_factory;
	return $factories;
}

