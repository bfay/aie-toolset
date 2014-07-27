<div class="ddl-dialogs-container"> <!-- The create a new layout popup -->

	<div class="ddl-dialog" id="ddl-container-edit">
		<div class="ddl-dialog-header">
			<h2 class="js-dialog-edit-title"><?php _e('Edit grid', 'ddl-layouts'); ?></h2>
			<h2 class="js-dialog-add-title"><?php _e('Add grid', 'ddl-layouts'); ?></h2>
			<i class="icon-remove js-edit-dialog-close"></i>
		</div>

		<div class="ddl-dialog-content">

			<?php $unique_id = uniqid(); ?>
			<div class="js-popup-tabs">

				<ul>
					<li><a href="#js-grid-content-<?php echo $unique_id; ?>"><?php _e('Content', 'ddl-layouts'); ?></a></li>
					<li class="ddl-tab-right"><a href="#js-grid-settings-<?php echo $unique_id; ?>"><?php _e('CSS styling', 'ddl-layouts'); ?></a></li>
				</ul>

				<div class="ddl-dialog-content-main ddl-popup-tab" id="js-grid-content-<?php echo $unique_id; ?>">

					<div class="ddl-form">
						<p>
							<label for="ddl-container-edit-container-name"><?php _e('Grid name:', 'ddl-layouts'); ?></label>
							<input type="text" name="ddl-container-edit-container-name" id="ddl-container-edit-container-name">
						</p>
						<p>
							<a class="fieldset-inputs" href="<?php echo WPDLL_LEARN_ABOUT_GRIDS; ?>" target="_blank">
								<?php _e('Learn about creating and using grids', 'ddl-layouts'); ?> &raquo;
							</a>
						</p>
						
					</div>

				</div> <!-- .ddl-popup-tab -->

				<div class="ddl-popup-tab" id="js-grid-settings-<?php echo $unique_id; ?>">
					<?php
						$dialog_type = 'container';
						include 'cell_display_settings_tab.tpl.php';
					?>
				</div> <!-- .ddl-popup-tab -->

			</div> <!-- .js-popup-tabs -->

		</div>

		<div class="ddl-dialog-footer">
			<?php wp_nonce_field('wp_nonce_edit_css', 'wp_nonce_edit_css'); ?>
			<button class="button js-edit-dialog-close"><?php _e('Cancel','ddl-layouts') ?></button>
		  <!--  <button class="button button-primary js-container-dialog-edit-add-container"><?php _e('Add container','ddl-layouts') ?></button> -->
			<button class="button button-primary js-container-dialog-edit-save"><?php _e('Save','ddl-layouts') ?></button>
		</div>

	</div> <!-- .ddl-dialog -->

</div>