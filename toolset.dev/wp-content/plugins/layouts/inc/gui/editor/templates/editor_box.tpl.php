<?php

function ddl_render_editor($inline) {
	global $wpdd_gui_editor, $wpddlayout;

	// Get layout
	if ($inline) {
		$post = get_post($_GET['post']);
		$layout_json = get_post_meta($post->ID, '_dd_layouts_settings', true);
		if (!$layout_json) {
			// This post doesn't have a layout so create an empty one
			$preset_dir = WPDDL_RES_ABSPATH . '/preset-layouts/';
			$layout = $wpddlayout->load_layout($preset_dir . '4-evenly-spaced-columns.ddl');

			// Force fluid when using in post editor.
			$layout['type'] = 'fluid';
			for ($i = 0; $i < sizeof($layout['Rows']); $i++) {
				$layout['Rows'][$i]['layout_type'] = 'fluid';
			}
			$layout_json = json_encode($layout);
		}
	} else {
		$post = get_post($_GET['layout_id']);
		$layout_json = WPDD_Layouts::get_layout_settings($post->ID);
	}



	WPDD_GUI_EDITOR::load_js_templates('/js/templates');
	?>

	<div class="dd-layouts-wrap">

		<p class="dd-layouts-breadcrumbs js-dd-layouts-breadcrumbs-wrap">
			<span><?php _e('You are editing:','ddl-layouts'); ?></span>
			<span class="js-dd-layouts-breadcrumbs"></span>
			<span class="layout-title js-layout-title active">
				<?php echo apply_filters("layout_title", $post->post_title); ?>
			</span>
		</p>

	<!--	<p class="dd-layouts-info">
			<i class="icon-keyboard js-show-keyboard-shortcuts" title="<?php _e('Show keyboard shortcuts','ddl-layouts'); ?>"></i>
		</p>
	-->
	</div>

	<div class="dd-layouts-wrap">

		<div class="editor-toolbar js-editor-toolbar">
			<?php if ($inline): ?>
				<p class="use-layout-wrap">
					<input type="checkbox" value="layout-enabled" name="ddl-use-layout-for-this-post">
					<label for="ddl-use-layout-for-this-post"><?php _e('Use this layout for displaying this post', 'ddl-layout'); ?></label>
				</p>
			<?php else: ?>
				<p class="save-button-wrap">
					<input name="save_layout" class="button button-primary button-large" value="<?php _e('Save','ddl-layouts'); ?>" type="submit">
				</p>
			<?php endif; ?>
			<p class="undo-redo-wrap">
				<button class="js-undo-button button button-large hidden" value="Undo" name="undo"><i class="icon-undo"></i> <?php _e('undo','ddl-layouts'); ?></button>
				<button class="js-redo-button button button-large hidden" name="redo"><i class="icon-repeat"></i> <?php _e('redo','ddl-layouts'); ?></button>
				<input id="hide-containers" class="button button-large hide-containers" type="button" value="<?php _e('Hide grid edit', 'ddl-layouts'); ?>" name="hide-containers">
			</p>
		</div>

	</div>

<?php // TEMP. Don't remove it. Put is somewhere as a reference. ?>
<?php if ( isset($_GET["forms"]) && $_GET["forms"] === '1' ) : ?>

		<p>Reference form</p>
		<div class="ddl-dialog test-dialog">

			<div class="ddl-dialog-header">
				<h2><?php _e('Test dialog','ddl-layouts') ?></h2>
				<i class="icon-remove js-close-dialog"></i>
			</div> <!-- .ddl-dialog-header -->

			<div class="ddl-dialog-content">

				<div class="info-box info-box-info">
					<h3 class="info-box-header">Basic form markup. Unordered list.</h3>
					<p><strong>ul.ddl-form > li</strong></p>
				</div>

				<ul class="ddl-form">

					<li>
						<label for="Input-1">Input-1</label>
						<input type="text" id="Input-1">
					</li>

					<li>
						<label for="Input-2">Input-2</label>
						<input type="text" id="Input-2">
					</li>

				</ul> <!-- .ddl-form -->

				<div class="info-box info-box-info">
					<h3 class="info-box-header">Basic form markup. Paragraphs.</h3>
					<p><strong>div.ddl-form > p</strong> - Use it if you want to put something else inside .ddl-form except form elements (for example validation message). </p>
				</div>

				<div class="ddl-form">

					<p>
						<label for="Input-3">Input-3</label>
						<input type="text" id="Input-3">
					</p>

					<p>
						<label for="Input-4">Input-4</label>
						<input type="text" id="Input-4">
					</p>

					<p class="toolset-alert toolset-alert-info">
						Message inside the form
					</p>

				</div>  <!-- .ddl-form -->

				<div class="info-box info-box-info">
					<h3 class="info-box-header">Basic form markup (alternative markup)</h3>
					<p><strong>div.ddl-form > div.ddl-form-item</strong> - Use it if for any reason you cant't use P or LI as form items. </p>
				</div>

				<div class="ddl-form">

					<div class="ddl-form-item">
						<label for="Input-5">Input-5</label>
						<input type="text" id="Input-5">
					</div>

					<div class="ddl-form-item">
						<label for="Input-6">Input-6</label>
						<input type="text" id="Input-6">
					</div>

				</div>  <!-- .ddl-form -->

				<div class="info-box info-box-info">
					<h3 class="info-box-header">Group of fields (fieldset)</h3>
					<p><strong>.ddl-form > fieldset > legend + .fields-group > [form-item] </strong></p>
					<p> <strong>[form-item]</strong> could be P, LI or .ddl-form-item </p>
				</div>

				<div class="ddl-form">

					<fieldset>
						<legend>Legend</legend>
						<div class="fields-group">
							<!-- Use ul>li, P or .ddl-form-item -->

							<!-- Unordered list -->
							<ul>
								<li>
									<label for="Input-7">Input-7</label>
									<input type="text" id="Input-7">
								</li>

								<li>
									<label for="Input-8">Input-8</label>
									<input type="text" id="Input-8">
								</li>
							</ul>

							<!-- Paragraphs -->
							<p>
								<label for="Input-9">Input-9</label>
								<input type="text" id="Input-9">
							</p>
							<p>
								<label for="Input-10">Input-10</label>
								<input type="text" id="Input-10">
							</p>

							<!-- any element with .ddl-form-item class -->
							<div class="ddl-form-item">
								<label for="Input-11">Input-11</label>
								<input type="text" id="Input-11">
							</div>

							<div class="ddl-form-item">
								<label for="Input-12">Input-12</label>
								<input type="text" id="Input-12">
							</div>

						</div> <!-- .fields-group -->
					</fieldset>

				</div>  <!-- .ddl-form -->

				<div class="info-box info-box-info">
					<h3 class="info-box-header">Fullwidth form</h3>
					<p><strong>.ddl-form > .ddl-form-item-fullwidth </strong></p>
				</div>

				<div class="ddl-form">
					<p class="ddl-form-item-fullwidth">
						<label for="full-width-input-1">Full width label</label>
						<input type="text" id="full-width-input-1">
						<span class="desc">Single full width item</span>
					</p>
					<div class="ddl-form-item-fullwidth">
						<fieldset>
							<legend>Legend</legend>
							<p class="desc">Multiple full width items</p>
							<div class="fields-group">
								<!-- Use ul>li, P or .ddl-form-item -->

								<!-- Unordered list -->
								<ul>
									<li>
										<label for="full-width-input-2">Full width input label 2</label>
										<input type="text" id="full-width-input-2">
									</li>

									<li>
										<label for="full-width-input-3">Full width input label 3</label>
										<input type="text" id="full-width-input-3">
									</li>
								</ul>

								<!-- Paragraphs -->
								<p>
									<label for="full-width-input-4">Full width input label 4</label>
									<input type="text" id="full-width-input-4">
								</p>
								<p>
									<label for="full-width-input-5">Full width input label 5</label>
									<input type="text" id="full-width-input-5">
								</p>

								<!-- any element with .ddl-form-item class -->
								<div class="ddl-form-item">
									<label for="full-width-input-6">Full width input label 6</label>
									<input type="text" id="full-width-input-6">
								</div>

								<div class="ddl-form-item">
									<label for="full-width-input-7">Full width input label 7</label>
									<input type="text" id="full-width-input-7">
								</div>

							</div> <!-- .fields-group -->
						</fieldset>
					</div>
				</div>

				<div class="info-box info-box-info">
					<h3 class="info-box-header">Basic form elements</h3>
					<p>Select, Input, Checkbox, Radio, Textarea</p>
				</div>

				<div class="ddl-form">

					<p>
						<label for="input-text">input[type=text]</label>
						<input type="text" id="input-text">
					</p>

					<p>
						<label for="input-number">input[type=number]</label>
						<input type="text" id="input-number">
					</p>

					<p>
						<label for="selct">select</label>
						<select name="" id="select">
							<option value="Option 1">option 1</option>
							<option value="Option 2">option 2</option>
							<option value="Option 3">option 3</option>
						</select>
					</p>

					<p>
						<label for="select2">select2</label>
						<select name="" id="select2" class="js-select2">
							<option value="Option 4">option 4</option>
							<option value="Option 5">option 5</option>
							<option value="Option 6">option 6</option>
						</select>
					</p>

					<p class="desc">
						Separate radios/checbkoxes:
					</p>
					<p>
						<label class="radio" for="radio-1">
							<input type="radio" id="radio-1">
							input[type=radio]
						</label>
					</p>

					<p>
						<label class="checkbox" for="radio-2">
							<input type="checkbox" id="radio-2">
							input[type=radio]
						</label>
					</p>

					<p class="desc">
						radios/checbkoxes group:
					</p>

					<p>
						<label class="radio" for="radio-3">
							<input type="radio" id="radio-3">
							input[type=radio]
						</label>

						<label class="radio" for="radio-5">
							<input type="radio" id="radio-5">
							input[type=radio]
						</label>

						<label class="checkbox" for="radio-6">
							<input type="checkbox" id="radio-6">
							input[type=radio]
						</label>

						<label class="checkbox" for="radio-7">
							<input type="checkbox" id="radio-7">
							input[type=radio]
						</label>
					</p>

					<p>
						<label for="textarea">Textarea</label>
						<textarea name="" id="textarea" cols="30" rows="10"></textarea>
					</p>

				</div>  <!-- .ddl-form -->

				<div class="info-box info-box-info">
					<h3 class="info-box-header">Basic form elements</h3>
					<p><strong>.ddl-form > ul > li > fieldset > legend + .fields-group.ddl-inputs-inline</strong> or <strong>.ddl-form > div.ddl-form-item > fieldset > legend + .fields-group.ddl-inputs-inline</strong></p>
				</div>

				<div class="ddl-form">
					<ul>
						<li>
							<fieldset>
								<legend>Legend</legend>
								<div class="fields-group">
									<p class="ddl-form-inputs-inline">
										<label class="radio" for="inline-radio-1">
											<input type="radio" name="inline-name" id="inline-radio-1" >
											<?php _e('Inline radio', 'ddl-layouts'); ?>
										</label>
										<label class="radio" for="inline-radio-2">
											<input type="radio" name="inline-name" id="inline-radio-2" checked>
											<?php _e('Inline radio', 'ddl-layouts'); ?>
										</label>
									</p>
									<p class="ddl-form-inputs-inline">
										<label class="checkbox" for="inline-checkbox-1">
											<input type="checkbox" name="inline-name" id="inline-checkbox-1" >
											<?php _e('Inline checkbox', 'ddl-layouts'); ?>
										</label>
										<label class="checkbox" for="inline-checkbox-2">
											<input type="checkbox" name="inline-name" id="inline-checkbox-2" checked>
											<?php _e('Inline checkbox', 'ddl-layouts'); ?>
										</label>
									</p>
									<p class="ddl-form-inputs-inline">
										<input type="text">
										<label for="inline-select-1">Inline label</label>
										<select name="inline-select-1" id="inline-select-1">
											<option value="Option 1">option 1</option>
											<option value="Option 2">option 2</option>
											<option value="Option 3">option 3</option>
										</select>
										<label for="inline-select-2">Inline label</label>
										<select name="inline-select-2" id="inline-select-2">
											<option value="Option 4">option 4</option>
											<option value="Option 5">option 5</option>
											<option value="Option 6">option 6</option>
										</select>
									</p>
								</div>
							</fieldset>
						</li>
					</ul>
				</div> <!-- .ddl-form -->

			</div> <!-- .ddl-dialog-content -->

			<div class="ddl-dialog-footer">
				<button class="button js-close-dialog"><?php _e('Cancel','ddl-layouts') ?></button>
				<button class="button button-primary"><?php _e('Apply','ddl-layouts') ?></button>
			</div> <!-- .ddl-dialog-footer -->

		</div> <!-- .ddl-dialog -->

<?php endif; ?>
<?php // TEMP END ?>

	<div class="dd-layouts-wrap main-ddl-editor">

		<div class="layout-container js-layout-container rows">

			<div class="progress progress-striped active">
				<div class="bar"  role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
			</div>

		</div>

	</div> <!-- .main-ddl-editor -->

	<div class="clear"></div>

	<textarea id="layouts-hidden-content" name="layouts-hidden-content"  class="js-hidden-json-textarea hidden-json-textarea" <?php if(!WPDDL_DEBUG) echo 'style="display:none"'; ?>><?php echo $layout_json; ?></textarea>

	<div class="hidden">
		<?php // TODO: We should move it to separate template file ?>

		<div class="js-context-menu ddl-context-menu">

			<ul>
				<li class="js-edit-params"><i class="icon-edit"></i> <?php _e('Edit cell','ddl-layouts') ?></li>
				<li class="js-edit-css"><i class="icon-css3"></i> <?php _e('Edit CSS','ddl-layouts') ?></li>
				<li class="js-remove-cell"><i class="icon-trash"></i> <?php _e('Remove','ddl-layouts') ?></li>
			</ul>

		</div>

		<div class="js-add-row-menu ddl-context-menu">

			<ul>
				<li class="row12 add-row js-add-row js-add-row-item" data-row-type="normal-row" data-cell-width="1"><?php _e('12 columns','ddl-layouts') ?></li>
				<li class="row6 add-row js-add-row js-add-row-item" data-row-type="normal-row" data-cell-width="2"><?php _e('6 columns','ddl-layouts') ?></li>
				<li class="row4 add-row js-add-row js-add-row-item" data-row-type="normal-row" data-cell-width="3"><?php _e('4 columns','ddl-layouts') ?></li>
				<li class="row3 add-row js-add-row js-add-row-item" data-row-type="normal-row" data-cell-width="4"><?php _e('3 columns','ddl-layouts') ?></li>
				<li class="row2 add-row js-add-row js-add-row-item" data-row-type="normal-row" data-cell-width="6"><?php _e('2 columns','ddl-layouts') ?></li>
				<li class="row1 add-row js-add-row js-add-row-item" data-row-type="normal-row" data-cell-width="12"><?php _e('1 column','ddl-layouts') ?></li>
			</ul>

		</div>

		<div class="js-add-special-row-menu ddl-context-menu ddl-special-row-context-menu">

			<ul>
				<li class="js-add-row js-add-row-item" data-row-type="normal-row"><?php _e('Add cells row','ddl-layouts') ?></li>

				<?php
				global $wpddlayout;
				if( $wpddlayout->has_theme_sections() ):?>
					<li class="js-add-row js-add-row-item" data-row-type="theme-section-row"><?php _e('Add theme section','ddl-layouts') ?></li>
				<?php endif;?>
			</ul>

		</div>

	</div>

	<?php
}
?>