<?php

function register_widget_cell_init() {

	register_dd_layout_cell_type (
		'widget-cell',
		array (
				'name' => __('Widget', 'ddl-layouts'),
				'icon-css' => 'icon-cog',
				'description' => __('A cell that displays a single WordPress Widget.', 'ddl-layouts'),
				'button-text' => __('Assign Widget cell', 'ddl-layouts'),
				'dialog-title-create' => __('Create a Widget cell', 'ddl-layouts'),
				'dialog-title-edit' => __('Edit Widget cell', 'ddl-layouts'),
				'dialog-template-callback' => 'widget_cell_dialog_template_callback',
				'cell-content-callback' => 'widget_cell_content_callback',
				'cell-template-callback' => 'widget_cell_template_callback',
				'cell-class' => 'widget-cell',
				'preview-image-url' => WPDDL_RES_RELPATH . '/images/single-widget.png',
				'register-scripts' => array(
					array('widget_cell_js', WPDDL_RELPATH . '/inc/gui/editor/js/widget-cell.js', array('jquery'), WPDDL_VERSION, true)
				),
				'category'					=> __('WordPress UI', 'ddl-layouts'),
				
			  )
	);
}
add_action( 'init', 'register_widget_cell_init' );

/*
 * Callback function that returns the user interface for the cell dialog.
 * Notice that we are using 'text_data' for the input element name.
 * This can then be accessed during frontend render using
 * $cell_settings['text_data']
 */

function widget_cell_dialog_template_callback() {
	global $wp_registered_widgets;

	add_action('wp_ajax_get_widget_controls', 'widget_cell_get_controls' );

	ob_start();
	?>

		<?php
			/*
			 * Use the the_ddl_name_attr function to get the
			 * name of the text box. Layouts will then handle loading and
			 * saving of this UI element automatically.
			 */
		?>

		<h3>
			<?php the_ddl_cell_info('name'); ?>
		</h3>

		<ul class="ddl-form widget-cell">
			<li>
				<label for="<?php the_ddl_name_attr('widget_type'); ?>"><?php _e('Widget type:', 'ddl-layouts' ); ?></label>
				<select name="<?php the_ddl_name_attr('widget_type'); ?>" data-nonce="<?php echo wp_create_nonce( 'ddl-get-widget' ); ?>">
					<?php foreach($wp_registered_widgets as $widget): ?>
					<?php if(  !is_array($widget['classname'] ) &&  !is_array( $widget['name'] ) ): ?>
					<option value="<?php echo $widget['classname']; ?>"><?php echo $widget['name']; ?></option>
					<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</li>
			<li>
				<fieldset class="js-widget-cell-fieldset hidden">
					<legend><?php _e('Widget settings', 'ddl-layouts' ); ?>:</legend>
					<div class="fields-group widget-cell-controls js-widget-cell-controls">
					</div>
				</fieldset>
			</li>

			<li>
				<?php ddl_add_help_link_to_dialog(WPDLL_WIDGET_CELL,
												  __('Learn about the Widget cell', 'ddl-layouts'));
				?>
			</li>			
			
		</ul>

	<?php
	return ob_get_clean();
}

function widget_cell_get_controls() {
	if (wp_verify_nonce( $_POST['nonce'], 'ddl-get-widget' )) {
		global $wp_widget_factory;
		foreach ($wp_widget_factory->widgets as $widget) {
			if ($widget->widget_options['classname'] == $_POST['widget']) {
				$widget->form(null);

				// Output a field so we can work out how the fields are named.
				// We use this in JS to load and save the settings to the layout.
				?>
					<input type="hidden" id="ddl-widget-name-ref" value="<?php echo $widget->get_field_name('ddl-layouts'); ?>">
				<?php
				break;
			}
		}
	}
	die();
}

// Callback function for displaying the cell in the editor.
function widget_cell_template_callback() {

	ob_start();
	?>
		<div class="cell-content">
			<p class="cell-name">{{ name }}</p>
			<div class="cell-content">
				<#
					var element = DDLayout.widget_cell.get_widget_name( content.widget_type );
					print( element );
				#>
			</div>
		</div>

	<?php 
	return ob_get_clean();

}

// Callback function for display the cell in the front end.
function widget_cell_content_callback($cell_settings) {
	ob_start();

	global $wp_widget_factory;
	foreach ($wp_widget_factory->widgets as $widget) {
		if ($widget->widget_options['classname'] == $cell_settings['widget_type']) {
			the_widget(get_class($widget), $cell_settings['widget']);
			break;
		}
	}

	return ob_get_clean();
}