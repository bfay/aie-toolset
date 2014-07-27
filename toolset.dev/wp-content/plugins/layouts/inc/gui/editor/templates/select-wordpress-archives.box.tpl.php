
<?php
$layout = WPDD_Layouts::get_layout_settings_raw_not_cached($current);
$has_loop = property_exists($layout, 'has_loop') ? $layout->has_loop : false;
$disabled = $has_loop === false ? ' disabled ' : '';
$class = $disabled ? 'post-loops-list-in-layout-editor-alerted' : '';?>

 <?php
 if( count($loops) > 0 ):?>
<div class="js-change-wrap-box">
 <h2 class="js-change-layout-use-section-title change-layout-use-section-title-outer"><span  class="change-layout-use-section-title js-collapse-group-individual"><?php _e('Archives:', 'ddl-layouts'); ?></span>
     <i class="icon-caret-up js-collapse-group-in-dialog change-layout-use-section-title-icon-collapse"></i></h2>

 <ul class="post-types-list-in-layout-editor js-post-types-list-in-layout-editor js-change-layout-use-section change-layout-use-section <?php echo $class;?>">

<li> <div class="alert-no-loops toolset-alert <?php if( $has_loop ):?>hidden<?php endif;?>">
       <!-- <i class="icon-remove js-remove-alert-message remove-alert-message" data-has_right_cell="<?php echo $has_loop; ?>"></i> -->
        <?php echo sprintf( __("This layout doesn't have a Post Loop or Views Post Loop cell, so you cannot use it for archives. %s",
                'ddl-layouts'), '<p><a href="" id="dismiss-loop-'.$current.'" class="dismiss-alert-message-loop js-dismiss-alert-message-loop">Ignore and use anyway.</a></p>') ?>
    </div></li>

<?php
foreach( $loops as $archive ):
    if( count($archive->loop) > 0 ):
?>
<li class="change-layout-use-section-title change-layout-use-section-title-inner"><?php echo $archive->title; ?></li>

	<?php
	foreach( $archive->loop as $loop => $label ): ?>
	<?php
		$checked = $this->archive_has_layout( $loop, $current ) ? 'checked' : '';
		$unique_id = uniqid($id_string, true);
	?>
		<li class="js-checkboxes-elements-wrap">
			<label for="post-loop-<?php echo $unique_id . $loop; ?>">
				<input <?php echo $disabled;?> type="checkbox" <?php echo $checked;?> name="<?php echo $archive->name;?>" class="js-ddl-post-type-checkbox<?php echo $id_string ? '-'.$id_string: '';?> js-ddl-archive-loop-checkbox" value="<?php echo $loop;?>" id="post-loop-<?php echo $unique_id . $loop;?>">
				<?php echo $label;?>
			</label>
		</li>
	<?php endforeach;?>
    <?php endif;?>

<?php endforeach; ?>
   <li class="save-archives-options-wrap"> <input <?php echo $disabled;?> id="js-save-archives-options-<?php echo $current;?>" name="save_archives_options" class="button button-secondary button-large js-save-archives-options js-buttons-change-update buttons-change-update" value="<?php _e('Update', 'ddl-layouts');?>" type="submit"></li>
</ul>
</div>
<?php endif;?>