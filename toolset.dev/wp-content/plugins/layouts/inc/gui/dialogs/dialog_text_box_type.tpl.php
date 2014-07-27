<?php // TODO: This file probably can be removed ?>
<div class="ddl-dialog ddl-dialog-text">
	<div class="ddl-dialog-header">
		<h2><?php _e('Text box','ddl-layouts') ?></h2>
		<i class="icon-remove js-edit-dialog-close"></i>
	</div>
	<div class="ddl-dialog-content">
		<textarea id="code-text-editor" name="code-text-editor"></textarea>

		<?php // the_editor('');?>

			 <?php //wp_editor( '', 'wp_editor'); ?>
	</div>

	<div class="ddl-dialog-footer">
		<button class="button js-close-dialog"><?php _e('Cancel','ddl-layouts') ?></button>
		<button class="button button-primary js-text-box-save js-save-dialog-settings"><?php _e('Save','ddl-layouts') ?></button>
	</div>
</div>