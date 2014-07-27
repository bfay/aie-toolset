<div class="ddl-form">
	<p>
		<label for="ddl_tag_name"><?php _e('HTML Tag:', 'ddl-layouts'); ?></label>
		<select class="js-select2 js-ddl-tag-name" id="ddl_tag_name" name="ddl_tag_name">
			<option value="aside">&lt;aside&gt;</option>
			<option value="blockquote">&lt;blockquote&gt;</option>
			<option value="button">&lt;button&gt;</option>
			<option value="div" selected>&lt;div&gt;</option>
			<option value="figure">&lt;figure&gt;</option>
			<option value="footer">&lt;footer&gt;</option>
			<option value="h1">&lt;h1&gt;</option>
			<option value="h2">&lt;h2&gt;</option>
			<option value="h3">&lt;h3&gt;</option>
			<option value="h4">&lt;h4&gt;</option>
			<option value="h5">&lt;h5&gt;</option>
			<option value="h6">&lt;h6&gt;</option>
			<option value="header">&lt;header&gt;</option>
			<option value="section">&lt;section&gt;</option>
		</select>
		<span class="desc"><?php _e('Choose the HTML tag to use when rendering this cell.','ddl-layouts') ?></span>
	</p>
	<p>
		<label for="ddl-<?php echo $dialog_type; ?>-edit-css-id"><?php _e('Tag ID:', 'ddl-layouts'); ?> <span class="opt">(<?php _e('optional', 'ddl-layouts'); ?>)</span></label>
		<input type="text" name="ddl-<?php echo $dialog_type; ?>-edit-css-id" id="ddl-<?php echo $dialog_type; ?>-edit-css-id" class="js-edit-css-id">
		<span class="desc"><?php _e('Set an ID for the cell if you want to specify a unique style for it.','ddl-layouts') ?></span>
	</p>
	<p>
		<label for="ddl-<?php echo $dialog_type; ?>-edit-class-name"><?php _e('Tag classes:', 'ddl-layouts'); ?> <span class="opt">(<?php _e('optional', 'ddl-layouts'); ?>)</span></label>
		<input type="text" name="ddl-<?php echo $dialog_type; ?>-edit-class-name" id="ddl-<?php echo $dialog_type; ?>-edit-class-name" class="js-select2-tokenizer js-edit-css-class">
	</p>
	<p class="ddl-form-item-fullwidth">
		<label ><?php _e('Custom CSS:', 'ddl-layouts'); ?></label>
		<!--<span class="desc"><?php _e('Before you can edit the CSS for this cell, please set its tag ID or class.','ddl-layouts') ?></span>-->
	</p>

	<div class="js-css-editor-message-container message-container"></div>

	<div class="js-code-editor code-editor layout-css-editor">
		<div class="code-editor-toolbar js-code-editor-toolbar">
			<ul>
				<li></li>
			</ul>
		</div>
		<!-- THERE SHOULDN'T BE ANY NEW LINE IN TEXT AREA TAG OTHERWISE CREATES A VISUAL BUG -->
		<ul class="codemirror-bookmarks js-codemirror-bookmarks"></ul>
		<textarea name="ddl-<?php echo $dialog_type; ?>-css-editor" id="ddl-<?php echo $dialog_type; ?>-css-editor" class="js-ddl-css-editor-area"><?php WPDD_GUI_EDITOR::print_layouts_css(); ?></textarea>
	</div>

	<p>
		<?php _e('Need help with CSS styling?', 'ddl-layouts'); ?>&nbsp;<a href="<?php echo WPDDL_CSS_STYLING_LINK; ?>" target="_blank"><?php _e('Using HTML and CSS to style Layout cells', 'ddl-layouts'); ?> &raquo;</a>
	</p>
</div>