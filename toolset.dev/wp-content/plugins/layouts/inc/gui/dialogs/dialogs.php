<?php

class WPDD_GUI_DIALOGS {

	function __construct() {
		# add dialogs css/js
		add_action('admin_enqueue_scripts', array($this,'preload_styles'));
		add_action('admin_enqueue_scripts', array($this,'preload_scripts_dialogs'), 100); // needs to run after Views

		# show dialogs
		add_action('wpddl_after_render_editor', array($this,'render_dialog_wrapper_opening_tag'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_element_box_type'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_text_box_type'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_wrapper_closing_tag'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_default_edit'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_yes_no_cancel'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_row_edit'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_container_edit'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_layout_settings'));
		add_action('wpddl_after_render_editor', array($this,'render_dialog_video_player'));
		add_action('wpddl_after_render_editor', array($this, 'render_registered_cell_dialogs'));
		add_action('wpddl_after_render_editor', array($this, 'render_dialog_theme_section_row_edit'));

	}

	function __destruct() {

	}
	function preload_styles() {
		global $wpddlayout;

		$wpddlayout->enqueue_styles(
			array(
				'layouts-meta-html-codemirror-css' ,
				'layouts-meta-html-codemirror-css-hint-css',
				'ddl-dialogs-forms-css',
				'wp-layouts-jquery-ui-slider',
				'toolset-font-awesome',
			)
		);
	
	}

	function preload_scripts_dialogs()
	{
		global $wpddlayout;

		$wpddlayout->enqueue_scripts(
			array(
				'jquery',
				'editor',
				'thickbox',
				'media-upload',
				'layouts-js-widgets',
				#codemirror
				'views-codemirror-script',
				'layouts-meta-html-codemirror-overlay-script',
				'layouts-meta-html-codemirror-xml-script',
				'layouts-meta-html-codemirror-css-script',
				'layouts-meta-html-codemirror-js-script',
				'layouts-meta-html-codemirror-utils-search',
				'layouts-meta-html-codemirror-utils-search-cursor',
				'layouts-meta-html-codemirror-utils-hint',
				'layouts-meta-html-codemirror-utils-hint-css',
				'icl_editor-script',
				# add dialogs js
				'wp-layouts-dialogs-script',

				# add jQuery Colorbox plugin
				'wp-layouts-colorbox-script',

				# add jQuery hoverIntent plugin
				'hoverIntent',

				# add jQuery UI Slider
				'jquery-ui-slider',
			)
		);
        
		if( isset( $_GET['page'] ) && 'dd_layouts_edit' == $_GET['page'] ) {
			wp_enqueue_script('views-codemirror-conf-script');
			
			if ( !wp_script_is( 'suggest' ) ) {
				wp_enqueue_script('suggest');
			}
			
        }
		
	}
	# render choose box type dialog
	function render_dialog_wrapper_opening_tag() { ?>
		<div class="ddl-dialogs-container">
	<?php
	}
	function render_dialog_wrapper_closing_tag() { ?>
		</div> <!-- /.ddl-dialogs-container -->
	<?php
	}

	# render elemnt box type dialog
	function render_dialog_element_box_type() {
		//$this->dd_layouts_register_standard_elements();
		//$register_cells = apply_filters('dd_layouts_register_cells', null);
		include_once 'dialog_element_box_type.tpl.php';
	}

	function render_dialog_layout_settings() {
		include_once 'dialog_layout_settings_edit.tpl.php';
	}

	# render text box dialog
	function render_dialog_text_box_type() {
		include_once 'dialog_text_box_type.tpl.php';
	}

	# render default edit dialog
	function render_dialog_default_edit() {
		include_once 'dialog_default.tpl.php';
	}

	function render_dialog_yes_no_cancel() {
		include_once 'dialog_yes_no_cancel.tpl.php';
	}

	# render row edit dialog
	function render_dialog_row_edit() {
		include_once 'dialog_row_edit.tpl.php';
	}

	function render_dialog_container_edit(){
		include_once 'dialog_container_edit.tpl.php';
	}

	function render_dialog_video_player() {
		include_once 'dialog_video_player.tpl.php';
	}

	function render_dialog_theme_section_row_edit()
	{
		include_once 'dialog_theme_section_row_edit.tpl.php';
	}

	function render_registered_cell_dialogs() {
		global $wpddlayout;
		foreach ($wpddlayout->get_cell_types() as $cell_type) {
			$cell_info = $wpddlayout->get_cell_info($cell_type);

			?>
			<div id="ddl-cell-dialog-<?php echo $cell_type; ?>">
				<?php echo $cell_info['dialog-template']; ?>
			</div>
		<?php

		}
	}
}