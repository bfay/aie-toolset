<?php
// This is for an empty cell
?>

<?php if (!WPDDL_DEBUG): ?>

	<script type="text/html" id="cell-template">
		<div class="cell-content">
		</div>
	</script>

<?php else: ?>

	<script type="text/html" id="cell-template">
		<div class="cell-content">
			<p class="cell-name">{{ name }} &ndash; {{cid}} &ndash; {{ kind }}</p>
			<# if( content ){ #>
			<div class="cell-preview">
				{{ content }}
			</div>
			<# } #>
		</div>
	</script>

<?php endif; ?>
