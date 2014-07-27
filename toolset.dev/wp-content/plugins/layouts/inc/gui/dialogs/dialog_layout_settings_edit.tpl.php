<?php
global $wpddlayout, $wpddl_features;

function display_breadcrumb_tree ( $parent_id, $available_parents ) {

	global $wpddlayout;

	if ($parent_id == 0) {
		if ( isset($available_parents[$parent_id]) && sizeof($available_parents[$parent_id]) > 0 ) {

			// Add No Parent to the start.

			?>
			<li class="js-tree-category-item">
				<p class="item-name-wrap js-item-name-wrap">
					<a href="#">
						<span class="js-item-name"
							  data-layout-slug=""
							  data-layout-id=""><?php _e('(no parent)', 'ddl-layouts'); ?></span>
					</a>
				</p>
			<?php
			foreach ( $available_parents[$parent_id] as $parent) {
				display_breadcrumb_tree ( $parent, $available_parents );
			}
		}
	} else {

		$layout = $wpddlayout->get_layout_settings($parent_id, true);

		?>
			<li class="js-tree-category-item <?php if ( isset($available_parents[$parent_id]) ) : ?>js-tree-category<?php endif; ?>">
				<p class="item-name-wrap js-item-name-wrap">
					<?php if ( isset($available_parents[$parent_id]) ) : ?>
					<i class="js-tree-toggle icon-collapse-alt" data-expanded="true" data-text-expanded="Collapse" data-text-collapsed="Expand" title="Collapse"></i>
					<?php endif; ?>
					<a href="#">
						<span class="js-item-name"
							  data-layout-slug="<?php echo $layout->slug; ?>"
							  data-layout-id="<?php echo $layout->id; ?>"><?php echo esc_html($layout->name); ?></span>
					</a>
				</p>

				<?php if ( isset($available_parents[$parent_id]) && sizeof($available_parents[$parent_id]) > 0 ): ?>
					<ul class="js-tree-category-items">
						<?php
							foreach ( $available_parents[$parent_id] as $parent) {
								display_breadcrumb_tree ( $parent, $available_parents );
							}
						?>
					</ul> <!-- .js-tree-category-items -->
				<?php endif; ?>
			</li> <!-- .js-tree-category-item -->

		<?php
	}
}

?>

<div class="ddl-dialogs-container">

	<div class="ddl-dialog" id="ddl-layout-settings-dialog">

		<div class="ddl-dialog-header">
			<h2 class="js-dialog-edit-title"><?php _e('Edit Layout Settings', 'ddl-layouts'); ?></h2>
			<i class="icon-remove js-edit-dialog-close"></i>
		</div>

		<div class="ddl-dialog-content">

			<ul class="ddl-form">
				<li>

					<?php $name = 'ddl-layout-settings-layout-type';
					require WPDDL_GUI_ABSPATH . 'templates/layout-layout-type-selector.box.tpl.php';
					?>
					<?php if ($wpddl_features->is_feature('fixed-layout')): ?>
					<div class="fields-group">
						<p class="toolset-alert toolset-alert-info js-diabled-fluid-rows-info">
							<?php _e("This layout can't be changed to fluid because it contains a grid with fixed-width columns.", 'ddl-layouts'); ?>
						</p>
					</div>
					<?php endif; ?>
				</li>
				<li>
					<label for="ddl-layout-width"><?php _e('Layout width','ddl-layouts'); ?></label>
					<div class="fields-group">
						<select name="ddl-layout-width" id="ddl-layout-width">
							<?php for ($i = 1; $i <= 12; $i++): ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
							<?php endfor; ?>
						</select>
						<?php // FIXME: This message should be shown using .wpvToolsetMessage() ?>
						<p class="toolset-alert toolset-alert-info js-diabled-width">
							<?php _e("The Layout width is always 12 columns for fluid Layouts.", 'ddl-layouts'); ?>
						</p>
					</div>
				</li>

				<?php $available_parents = $wpddlayout->get_available_parent_layouts(); ?>

				<?php if (sizeof($available_parents)): ?>

					<li>
						<fieldset>
							<legend for="breadcrumbs-tree-search"><?php _e('Parent layout', 'ddl-layouts'); ?></legend>
							<div class="fields-group">

								<p>
									<input class="js-breadcrumbs-tree-search" type="text" id="breadcrumbs-tree-search"
										data-default-val="<?php _e('Search', 'ddl-layouts'); ?>&hellip;"
										data-message-container=".js-breadcrumbs-message-container"
										data-target="#js-layouts-tree"
										value="<?php _e('Search', 'ddl-layouts'); ?>&hellip;
									">
								</p>

								<ul class="tree" id="js-layouts-tree">
									<li class="js-tree-category">
										<h3 class="tree-category-title js-tree-category-title">
											<?php _e('Layouts:', 'ddl-layouts'); ?>
										</h3>
										<ul class="js-tree-category-items">
											<?php
												$available_parents = @array_diff($available_parents, array($_GET['layout_id']));
												display_breadcrumb_tree(0, $available_parents)
											?>
										</ul>
									</li>
								</ul>

								<div class="js-breadcrumbs-message-container" data-message-text="<?php _e( 'Nothing found', 'ddl-layouts' ); ?>"></div>

							</div>
						</fieldset>
					</li>
				<?php endif; ?>

			</ul>

		</div>

		<div class="ddl-dialog-footer">
			<?php wp_nonce_field('wp_nonce_layout_settings_edit', 'wp_nonce_layout_settings_edit'); ?>
			<button class="button js-edit-dialog-close"><?php _e('Cancel','ddl-layouts') ?></button>
			<button class="button button-primary js-row-dialog-edit-save js-save-dialog-settings"><?php _e('Save','ddl-layouts') ?></button>
		</div>

	</div>

</div>