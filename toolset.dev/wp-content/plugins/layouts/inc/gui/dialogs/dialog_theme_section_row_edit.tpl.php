<?php
global $wpddlayout;
$sections = $wpddlayout->registered_theme_sections->get_theme_sections();
?>

<div class="ddl-dialogs-container">

	<div class="ddl-dialog" id="ddl-theme-section-row-edit">
		<div class="ddl-dialog-header">
			<h2 class="js-dialog-edit-title hidden"><?php _e('Edit Theme Section', 'ddl-layouts'); ?></h2>
			<h2 class="js-dialog-add-title hidden"><?php _e('Add Theme Section', 'ddl-layouts'); ?></h2>
			<i class="icon-remove js-edit-dialog-close"></i>
		</div>

		<div class="ddl-dialog-content">

			<?php $unique_id = uniqid(); ?>


				<div class="ddl-dialog-content-main ddl-popup-tab" id="js-row-basic-settings-<?php echo $unique_id; ?>">

					<ul class="ddl-form">
						<li>
							<label for="ddl-theme-section-row-edit-row-name"><?php _e('Theme Section name:', 'ddl-layouts'); ?> <span class="opt">(<?php _e('optional', 'ddl-layouts'); ?>)</span></label>
							<input type="text" name="ddl-theme-section-row-edit-row-name" id="ddl-theme-section-row-edit-row-name">
						</li>
						<li>
							<label for="ddl-theme-section-row-edit-type" for="ddl-theme-section-row-edit-type"><?php _e('Theme Section:', 'ddl-layouts'); ?></label>
							<select id="ddl-theme-section-row-edit-type" name="ddl-theme-section-row-edit-type" class="js-ddl-theme-section-row-edit-type">
								<?php foreach( $sections as $section ):
									$info = (object)$wpddlayout->registered_theme_sections->get_theme_section_info($section);
									?>
								<option value="<?php echo $section;?>"><?php echo $info->name; ?></option>

								<?php endforeach;?>
							</select>
						</li>
					</ul>

					<div id="js-theme-section-description-container"></div>

				</div> <!-- .ddl-popup-tab -->





		</div> <!-- .ddl-dialog-content -->

		<div class="ddl-dialog-footer">
			<?php wp_nonce_field('wp_nonce_edit_css', 'wp_nonce_edit_css'); ?>
			<button class="button js-edit-dialog-close"><?php _e('Cancel','ddl-layouts') ?></button>
			<button class="button button-primary js-theme-section-row-dialog-edit-add-row"><?php _e('Add row','ddl-layouts') ?></button>
			<button class="button button-primary js-theme-section-row-dialog-edit-save js-save-dialog-settings"><?php _e('Save','ddl-layouts') ?></button>
		</div>

	</div>

</div>

<script type="text/javascript">
	var DDLayout = DDLayout || {};
	DDLayout.themeSectionsRow_data = {};
	<?php foreach( $sections as $section ):
				$info = (object)$wpddlayout->registered_theme_sections->get_theme_section_info($section);
				?>

	DDLayout.themeSectionsRow_data['<?php echo $section;?>'] = '<?php echo $info->description;?>';
	<?php endforeach;?>
</script>
<script type="text/html" id="theme-section-description-template">
	<div class="info-box info-box-info js-info-box" data-cell-type="bs-header-cell">
		<i class="icon-remove js-remove-info-box"></i>
		<div class="info-box-header"><?php _e('Theme section description','ddl-layouts') ?></div>
		<p>{{ description }}</p>
	</div>
</script>