<?php
	global $wpddlayout_theme;

	$wpddlayout_theme->file_manager_export->check_theme_dir_is_writable( __('You can either make it writable by the server or download the exported layouts and save them yourself.', 'ddl-layouts') );
?>

<div class="wrap">

	<div id="icon-tools" class="icon32 icon32-posts-dd_layouts"><br></div>
	<h2><?php _e('Export Layouts', 'ddl-layouts'); ?></h2>

	<div class="ddl-settings-wrap">

		<?php if ($wpddlayout_theme->file_manager_export->get_dir_message() ): ?>
		<div class="ddl-settings">
			<p class="toolset alert toolset-alert-error">
				<?php $wpddlayout_theme->file_manager_export->print_dir_message(); ?>
			</p>
		</div>
		<?php endif; ?>

		<div class="ddl-settings">
			<div class="ddl-settings-header">
				<h3><?php _e('Export layouts to theme directory', 'ddl-layouts'); ?></h3>
			</div>

			<div class="ddl-settings-content">

				<form method="post" action="">
					<?php wp_nonce_field('wp_nonce_export_layouts_to_theme', 'wp_nonce_export_layouts_to_theme'); ?>
					<p>
						<strong><?php _e('Files will be saved in:', 'ddl-layouts'); ?></strong>
						<code><?php echo $wpddlayout_theme->file_manager_export->get_layouts_theme_dir(); ?></code>
					</p>
					<p>
						<input type="submit" class="button button-secondary" name="export_to_theme_dir" value="<?php _e('Export', 'ddl-layouts'); ?>" <?php if (!$wpddlayout_theme->file_manager_export->dir_is_writable()) : ?>disabled<?php endif ?> >
					</p>
				</form>

				<?php
					if (isset($_POST['export_to_theme_dir'])) {
						$nonce = $_POST["wp_nonce_export_layouts_to_theme"];

						if ( wp_verify_nonce( $nonce, 'wp_nonce_export_layouts_to_theme' ) ) {

							$results = $wpddlayout_theme->export_layouts_to_theme( $wpddlayout_theme->file_manager_export->get_layouts_theme_dir() );

							?>

							<?php if (sizeof($results)): ?>
								<p>
									<?php _e('The following layouts have been exported.', 'ddl-layouts'); ?>
								</p>

								<ul>
									<?php foreach ($results as $result): ?>
										<li>
											<?php if ($result['file_ok']): ?>
												<i class='icon-ok-sign toolset-alert-success'></i>
											<?php else: ?>
												<i class='icon-remove-sign toolset-alert-error'></i>
											<?php endif; ?>
											<?php echo $result['title']; ?>
											<?php echo $result['file_name']; ?>
											<?php if (!$result['file_ok']): ?>
												<p class="toolset-alert-error">
													<?php _e('The file is not writable.', 'ddl-layouts'); ?>
												</p>
											<?php endif; ?>
										</li>
									<?php endforeach; ?>
								<ul>
							<?php endif ?>

							<?php

						}
					}
				?>

			</div> <!-- .ddl-settings-content -->
		</div> <!-- .ddl-settings -->

		<div class="ddl-settings">
			<div class="ddl-settings-header">
				<h3><?php _e('Export and download layouts', 'ddl-layouts'); ?></h3>
			</div>

			<div class="ddl-settings-content">

				<form method="post" action="">
					<?php wp_nonce_field('wp_nonce_export_layouts', 'wp_nonce_export_layouts'); ?>
					<p>
						<input type="submit" class="button button-secondary" name="export_and_download" value="<?php _e('Export', 'ddl-layouts'); ?>">
					</p>
				</form>

			</div> <!-- .ddl-settings-content -->
		</div> <!-- .ddl-settings -->

	</div> <!-- .ddl-settings-wrap -->

</div> <!-- .wrap -->

<div class="clear"></div>