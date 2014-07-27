<!-- VIEWS BOX -->
<div id="wrapper-views-box">
	<div class="ddl-dialog ddl-dialog-views">

		<div class="ddl-dialog-header">
			<h2><?php _e('Views box','ddl-layouts') ?></h2>
			<i class="icon-remove js-close-dialog"></i>
		</div>

		<div class="ddl-dialog-content">

			<p>
				<button class="button button-primary">+ <?php _e('Add new view','ddl-layouts') ?></button>
			</p>

			<form class="ddl-form">
				<legend>Lorem ipsum</legend>
				<ul class="fields-group">
					<li>
						<div class="radio">
							<label for="view-name-id[num]">
								<input type="radio" name="views" id="view-name-id[num]">
								Lorem ipsum view name
							</label>
						</div>
					</li>
					<li>
						<div class="radio">
							<label>
								<input type="radio" name="views">
								Lorem ipsum view name
							</label>
						</div>
					</li>
					<li>
						<div class="radio">
							<label>
								<input type="radio" name="views">
								Lorem ipsum view name
							</label>
						</div>
					</li>

				</ul>
			</form>

		</div> <!-- .ddl-dialog-content -->

		<div class="ddl-dialog-footer">
			<button class="button js-close-dialog"><?php _e('Cancel','ddl-layouts') ?></button>
			<button class="button button-primary js-save-dialog-settings"><?php _e('Save element','ddl-layouts') ?></button>
		</div>

	</div> <!-- .ddl-dialog -->
</div>