<script type="text/html" id="row-template">

	<div class="row-toolbar js-row-toolbar">
		<i class="icon-resize-vertical js-move-row"></i>
		<span class="js-element-name element-name">{{name}}</span>
		<div class="row-actions js-row-actions">
			<i class="icon-pencil js-row-edit" data-tooltip-text="Edit row"></i>  <?php // TODO: Localize data attribute  ?>
			<i class="icon-remove icon-remove-enabled js-row-remove" data-tooltip-text="Remove row"></i>  <?php // TODO: Localize data attribute  ?>
		</div>
	</div>

	<div class="row js-row row-{{layout_type}}"></div>

	<p class="add-row">
		<?php // Do not add a line break between buttons! ?>
		<button class="button-secondary js-highlight-row js-add-row add-row-button add-row-button-fluid" type="button"><i class="icon-plus"></i></button><button class="button-secondary js-highlight-row js-show-add-special-row-menu add-row-menu-toggle" type="button"><i class="icon-caret-down js-icon-caret"></i></button>
	</p>
</script>