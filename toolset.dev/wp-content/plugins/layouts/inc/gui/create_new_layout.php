<?php

function display_available_parents_in_select($available_parents, $parent_id, $selected, $depth = 0) {
	global $wpddlayout;

	if (isset($available_parents[$parent_id])) {
		foreach ($available_parents[$parent_id] as $child_id) {
			$layout = $wpddlayout->get_layout_from_id($child_id);
			if ($layout->get_post_id() == $selected) {
				$is_selected = 'selected';
			} else {
				$is_selected = '';
			}
			?>
				<option <?php echo $is_selected; ?> value="<?php echo $layout->get_post_id(); ?>" data-child-width="<?php echo $layout->get_width_of_child_layout_cell(); ?>" ><?php echo ($depth ? '&nbsp;' : '') . str_repeat('-', $depth) . ($depth ? '&nbsp;' : '') . $layout->get_name(); ?></option>
			<?php
			display_available_parents_in_select($available_parents, $child_id, $selected, $depth + 1);
		}
	}
}

global $wpddl_features, $wpddlayout;

// get the presets.
$preset_layouts = array();

$preset_dir = WPDDL_RES_ABSPATH . '/preset-layouts/';
$dir = opendir( $preset_dir );
while( ( $currentFile = readdir($dir) ) !== false )
{
	if ( $currentFile == '.' || $currentFile == '..' || $currentFile[0] == '.' )
	{
		continue;
	}

	$currentFile = $preset_dir . $currentFile;
	$layout = $wpddlayout->load_layout($currentFile);
	$preset_layouts[$layout['name']] = array('file' => $currentFile,
											 'layout' => $layout);
}
closedir($dir);
asort($preset_layouts);

?>


<script type="application/javascript">
	var ddl_create_layout_error = '<?php echo esc_js( __('Failed to create the layout.', 'ddl-layouts') ); ?>';
</script>

<div class="ddl-dialogs-container"> <!-- The create a new layout popup -->

	<div class="ddl-dialog create-layout-form-dialog js-create-layout-form-dialog">
		<?php wp_nonce_field('wp_nonce_create_layout', 'wp_nonce_create_layout'); ?>
		<input class="js-layout-new-redirect" name="layout_creation_redirect" type="hidden" value="<?php echo admin_url( 'admin.php?page=dd_layouts_edit&amp;layout_id='); ?>" />
		<div class="ddl-dialog-header">
			<h2><?php _e('Add a new Layout','ddl-layouts') ?></h2>
			<i class="icon-remove js-new-layout-dialog-close"></i>
		</div>
		<div class="ddl-dialog-content">

			<div class="info-box info-box-info">
				<p class="">
					<?php _e('A layout can be used to layout your content.', 'ddl-layouts'); ?>
				</p>
			</div>

			<ul class="ddl-form">

				<li>

					<?php $name = 'dd-layout-type';
						  require WPDDL_GUI_ABSPATH . 'templates/layout-layout-type-selector.box.tpl.php';
					?>
					<?php if ($wpddl_features->is_feature('fixed-layout')): ?>
						<p class="toolset-alert toolset-alert-info js-diabled-fixed-rows-info">
							<?php _e('Only fluid layouts are allowed because the parent layout is fluid.', 'ddl-layouts'); ?>
						</p>
					<?php endif; ?>
				</li>

				<li class="js-preset-layouts-items">
					<label for="dd-layout-preset"><?php _e('Preset Layouts','ddl-layouts'); ?></label>
					<?php // Previews for layout presets ?>
					<ul class="presets-list fields-group">
						<?php $count = 0; ?>
						<?php foreach ($preset_layouts as $name => $details) : ?>
							<?php
								$file = $details['file'];
								$decoder = new WPDD_json2layout(true);
								$layout = $decoder->json_decode($details['layout'], true);
								$renderer = new WPDD_layout_preset_render($layout);
							?>
							<li class="js-presets-list-item <?php if ( $count === 0 ) : ?>selected<?php endif; ?>" <?php if ( $count === 0 ) : ?>data-selected="true"<?php endif; ?> data-file="<?php echo $file; ?>" data-width="<?php echo $layout->get_width(); ?>">
								<?php if ($layout->get_name() == 'Empty'): $count++; ?>
									<div class="row-fluid  row-count-1">
										<div class="span-preset12 empty">
											<?php _e('Empty layout','ddl-layouts'); ?>
										</div>
									</div>
								<?php else: ?>
									<?php echo $renderer->render_to_html(); $count++; ?>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>

				</li>

				<li>
					<fieldset>
						<legend><?php _e('Layout assignment','ddl-layouts'); ?></legend>
						<div class="fields-group">

							<ul>
								<li>
									<label class="checkbox">
										<input type="checkbox" class="js-dont-assign-post-type" checked name="ddl-new-layout-post-type-do-not-assign" value="0">
										<?php _e("Don't assign to any post type", 'ddl-layouts'); ?>
									</label>
								</li>

								<li>
									<p>
										<label class="js-ddl-for-post-types-open ddl-for-post-types-open" title="Click to toggle"><?php _e('Post types:','ddl-layouts'); ?> <i class="icon-caret-down"></i></label>
									</p>

									<div class="js-ddl-post-types-dropdown-list ddl-post-types-dropdown-list hidden">
										<?php echo $wpddlayout->post_types_manager->print_post_types_checkboxes(false, true, '', false );?>
									</div>
								</li>
							</ul>

						</div>
					</fieldset>
				</li>
				<li>
					<label for="layout_new_name"><?php _e('Name this layout','ddl-layouts'); ?></label>
					<input type="text" name="layout_new_name" id="layout_new_name" class="js-new-layout-title" placeholder="<?php echo htmlentities( __('Enter title here', 'ddl-layouts'), ENT_QUOTES ); ?>" data-highlight="<?php echo htmlentities( __('Now give this View a name', 'ddl-layouts'), ENT_QUOTES ); ?>" />
				</li>

			</ul>

			<div class="js-error-container js-ddl-message-container"></div>

		</div> <!-- .ddl-dialog-content -->

		<div class="ddl-dialog-footer">
			<?php wp_nonce_field('wp_nonce_create_layout', 'wp_nonce_create_layout'); ?>
			<button class="button js-new-layout-dialog-close"><?php _e('Cancel','ddl-layouts') ?></button>
			<button class="button button-primary js-create-new-layout"><?php _e('Create Layout','ddl-layouts') ?></button>
		</div>

	</div> <!-- .create-layout-form-dialog -->
</div>

<?php if ( isset( $_GET['new_layout'] ) && $_GET['new_layout'] == 'true'): ?>

	<script type="application/javascript">
		var ddl_layouts_create_new_layout_trigger = true;
	</script>

<?php endif; ?>