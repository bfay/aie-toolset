<script type="text/html" id="container-row-template">
	<# if ( invisibility === undefined || invisibility === false ) { #>
	<div class="row-toolbar js-row-toolbar">
		<?php
			// FIXME: I've changed icon-move to icon-resize-vertical
			// But it looks like some events was bound to icon-move class
			// Let's use js- prefixed classes
		?>
		<i class="icon-resize-vertical js-move-row"></i>
		<span class="js-element-name element-name">{{name}}</span>
		<div class="row-actions js-row-actions">
			<i class="icon-pencil js-row-edit" data-tooltip-text="Edit row"></i>  <?php // TODO: Localize data attribute  ?>
			<i class="icon-remove icon-remove-enabled js-row-remove" data-tooltip-text="Remove row"></i>  <?php // TODO: Localize data attribute  ?>
		</div>
	</div>
	<# } #>
	<div class="row row-{{layout_type}} container-row-view">

	</div>
	<# if ( invisibility === undefined || invisibility === false ) { #>
	<p class="add-row">
		<button class="button-secondary add-row-button js-add-row js-highlight-row<#if ( layout_type == 'fixed' ) { #> add-row-button-fixed<# } else { #> add-row-button-fluid<# } #>" type="button"><i class="icon-plus"></i></button><#if ( layout_type == 'fluid' ) { #><button class="button-secondary js-show-add-row-menu js-highlight-row add-row-menu-toggle" type="button"><i class="icon-caret-down js-icon-caret"></i></button><# } #>
	</p>
	<# } #>
</script>
