
<?php
$layout = WPDD_Layouts::get_layout_settings_raw_not_cached($current);
$disabled = '';?>

 <?php
 if( count($loops) > 0 ):?>
<div class="js-change-wrap-box">
 <h2 class="js-change-layout-use-section-title change-layout-use-section-title-outer"><span  class="change-layout-use-section-title js-collapse-group-individual"><?php _e('Others:', 'ddl-layouts'); ?></span>
     <i class="icon-caret-up js-collapse-group-in-dialog change-layout-use-section-title-icon-collapse"></i></h2>

 <ul class="post-types-list-in-layout-editor js-post-types-list-in-layout-editor js-change-layout-use-section change-layout-use-section">

<?php
foreach( $loops as $archive ):
    if( count($archive->loop) > 0 ):
?>
<!--<li class="change-layout-use-section-title change-layout-use-section-title-inner"><?php echo $archive->title; ?></li> -->

	<?php
	foreach( $archive->loop as $loop => $label ): ?>
	<?php
		$checked = $this->archive_has_layout( $loop, $current ) ? 'checked' : '';
		$unique_id = uniqid($id_string, true);
	?>
		<li class="js-checkboxes-elements-wrap">
			<label for="post-loop-<?php echo $unique_id . $loop; ?>">
				<input <?php echo $disabled;?> type="checkbox" <?php echo $checked;?> name="<?php echo $archive->name;?>" class="js-ddl-post-type-checkbox<?php echo $id_string ? '-'.$id_string: '';?> js-ddl-other-loop-checkbox" value="<?php echo $loop;?>" id="post-loop-<?php echo $unique_id . $loop;?>">
				<?php echo $label;?>
			</label>
		</li>
	<?php endforeach;?>
    <?php endif;?>

<?php endforeach; ?>
   <li class="save-archives-options-wrap"> <input disabled id="js-save-archives-options-<?php echo $current;?>" name="save_archives_options" class="button button-secondary button-large js-save-others-options js-buttons-change-update buttons-change-update" value="<?php _e('Update', 'ddl-layouts');?>" type="submit"></li>
</ul>
</div>
<?php endif;?>