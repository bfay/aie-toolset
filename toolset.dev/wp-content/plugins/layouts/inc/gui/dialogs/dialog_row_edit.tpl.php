<div class="ddl-dialogs-container">

	<div class="ddl-dialog" id="ddl-row-edit">

		<div class="ddl-dialog-header">
			<h2 class="js-dialog-edit-title"><?php _e('Edit Row', 'ddl-layouts'); ?></h2>
			<h2 class="js-dialog-add-title"><?php _e('Add Row', 'ddl-layouts'); ?></h2>
			<i class="icon-remove js-edit-dialog-close"></i>
		</div>

		<div class="ddl-dialog-content">

			<?php $unique_id = uniqid(); ?>
			<div class="js-popup-tabs">

				<ul>
					<li><a href="#js-row-basic-settings-<?php echo $unique_id; ?>"><?php _e('Content', 'ddl-layouts'); ?></a></li>
					<li class="ddl-tab-right"><a href="#js-row-design-<?php echo $unique_id; ?>"><?php _e('CSS styling', 'ddl-layouts'); ?></a></li>
				</ul>

				<div class="ddl-dialog-content-main ddl-popup-tab" id="js-row-basic-settings-<?php echo $unique_id; ?>">

					<ul class="ddl-form">
						<li>
							<label for="ddl-row-edit-row-name"><?php _e('Row name:', 'ddl-layouts'); ?> <span class="opt">(<?php _e('optional', 'ddl-layouts'); ?>)</span></label>
							<input type="text" name="ddl-row-edit-row-name" id="ddl-row-edit-row-name">
						</li>
						<li>
							<label for="ddl-row-edit-layout-type" for="ddl-row-edit-layout-type"><?php _e('Row layout type:', 'ddl-layouts'); ?></label>
							<select id="ddl-row-edit-layout-type" name="ddl-row-edit-layout-type">
								<option value="fixed"><?php _e('Fixed', 'ddl-layouts'); ?></option>
								<option value="fluid"><?php _e('Fluid', 'ddl-layouts'); ?></option>
							</select>

						</li>

						<!--<li class="toolset-alert toolset-alert-info js-only-fluid-message">
							<?php //_e('Only fluid rows are allowed here because the parent row or layout are fluid.', 'ddl-layouts'); ?>
						</li>-->

						<li class="js-preset-layouts-rows" id="js-row-edit-mode">
							<label for="ddl-row-edit-mode"><?php _e('Row type:', 'ddl-layouts'); ?></label>

							<?php // previews for row types ?>
							<ul class="presets-list row-types fields-group">
								<li>
									<figure class="row-type selected">
										<img class="item-preview" data-name="row-normal" src="<?php echo WPDDL_GUI_RELPATH; ?>dialogs/img/tn-boxed.png" alt="<?php _e('Normal', 'ddl-layouts'); ?>">
										<span><?php _e('Row same as container width', 'ddl-layouts'); ?></span>
									</figure>
									<label class="radio" data-target="row-normal" for="row_type_normal" style="display:none">
										<input type="radio" name="row_type" id="row_type_normal" value="normal" checked>
										<?php _e('Normal', 'ddl-layouts'); ?>
									</label>
								</li>
								<li>
									<figure class="row-type">
										<img class="item-preview" data-name="row-full-fixed" src="<?php echo WPDDL_GUI_RELPATH; ?>dialogs/img/tn-full-fixed.png" alt="<?php _e('Full width background', 'ddl-layouts'); ?>">
										<span><?php _e('Row background extends to full width', 'ddl-layouts'); ?></span>
									</figure>
									<label class="radio" data-target="row-full-fixed" for="row_type_full_width_background" style="display:none">
										<input type="radio" name="row_type" id="row_type_full_width_background" value="full-width-background">
										<?php _e('Full width background', 'ddl-layouts'); ?>
									</label>
								</li>
								<li>
									<figure class="row-type">
										<img class="item-preview" data-name="row-full-width" src="<?php echo WPDDL_GUI_RELPATH; ?>dialogs/img/tn-full-fluid.png" alt="<?php _e('Full width', 'ddl-layouts'); ?>">
										<span><?php _e('Cells extend to the full width', 'ddl-layouts'); ?></span>
									</figure>
									<label class="radio" data-target="row-full-width" for="row_type_full_width" style="display:none">
										<input type="radio" name="row_type" id="row_type_full_width" value="full-width">
										<?php _e('Full width', 'ddl-layouts'); ?>
									</label>
								</li>
							</ul>

							<p class="desc">
								<a class="fieldset-inputs" href="<?php echo WPDLL_LEARN_ABOUT_ROW_MODES; ?>" target="_blank">
									<?php _e('Learn about how rows can be displayed in different ways', 'ddl-layouts'); ?> &raquo;
								</a>
							</p>

						</li>
					</ul>



				</div> <!-- .ddl-popup-tab -->

				<div class="ddl-popup-tab" id="js-row-design-<?php echo $unique_id; ?>">
					<?php
						$dialog_type = 'row';
						include 'cell_display_settings_tab.tpl.php';
					?>
				</div><!-- .ddl-popup-tab -->

			</div> <!-- .js-popup-tabs -->

		</div> <!-- .ddl-dialog-content -->

		<div class="ddl-dialog-footer">
			<?php wp_nonce_field('wp_nonce_edit_css', 'wp_nonce_edit_css'); ?>
			<button class="button js-edit-dialog-close"><?php _e('Cancel','ddl-layouts') ?></button>
			<button class="button button-primary js-row-dialog-edit-add-row"><?php _e('Add row','ddl-layouts') ?></button>
			<button class="button button-primary js-row-dialog-edit-save js-save-dialog-settings"><?php _e('Save','ddl-layouts') ?></button>
		</div>

	</div>

</div>