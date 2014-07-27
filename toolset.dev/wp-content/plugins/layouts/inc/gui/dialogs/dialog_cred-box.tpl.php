<div class="ddl-dialogs ddl-dialogs-cred">

	<div class="ddl-dialog-header">
		<h2><?php _e('CRED forms box','ddl-layouts') ?></h2>
		<button class="btn btn-close js-close-dialog">x</button>
	</div>

	<div class="ddl-dialog-content">

		<p>
			<button class="button button-primary">+ <?php _e('Add new CRED form','ddl-layouts') ?></button>
		</p>

		<form class="ddl-form">
			<ul>
				<li>
					<input type="radio" name="cred" id="view-name-id[num]">
					<label for="view-name-id[num]">Lorem ipsum CRED form <span>(ID #123)</span></label>
				</li>
				<li>
					<input type="radio" name="cred">
					<label>Lorem ipsum CRED form <span>(ID #123)</span></label>
				</li>
				<li>
					<input type="radio" name="cred">
					<label>Lorem ipsum CRED form <span>(ID #123)</span></label>
				</li>
				<li>
					<input type="radio" name="cred">
					<label>Lorem ipsum CRED form <span>(ID #123)</span></label>
				</li>
				<li>
					<input type="radio" name="cred">
					<label>Lorem ipsum CRED form <span>(ID #123)</span></label>
				</li>
			</ul>
		</form>

	</div> <!-- .ddl-dialog-content -->

	<div class="ddl-dialog-footer">
		<button class="button"><?php _e('Cancel','ddl-layouts') ?></button>
		<button class="button button-primary"><?php _e('Save element','ddl-layouts') ?></button>
	</div>

</div> <!-- .ddl-dialogs -->