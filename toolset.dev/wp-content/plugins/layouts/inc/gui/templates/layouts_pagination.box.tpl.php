<div class="wpv-listing-pagination tablenav">
	<div class="tablenav-pages">
				<span class="displaying-num">
					<?php _e('Displaying ', 'wpv-views');
					echo $items_start; ?> - <?php echo $items_end;
					_e(' of ', 'wpv-views');
					echo $ddl_found_items; ?>
				</span>
		<?php if ($page > 1) { ?>
			<a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $context . $mod_url['orderby'] . $mod_url['order'] . $mod_url['search'] . $mod_url['items_per_page'] . $mod_url['status']; ?>&amp;paged=<?php echo $page - 1; ?>"
			   class="wpv-filter-navigation-link">&laquo; <?php echo __('Previous page', 'wpv-views'); ?></a>
		<?php } ?>
		<?php
		for ($i = 1; $i <= $pages_count; $i++) {
			$active = 'wpv-filter-navigation-link-inactive';
			if ($page == $i) $active = 'js-active active current'; ?>
			<a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $context . $mod_url['orderby'] . $mod_url['order'] . $mod_url['search'] . $mod_url['items_per_page'] . $mod_url['status']; ?>&amp;paged=<?php echo $i; ?>"
			   class="<?php echo $active; ?>"><?php echo $i; ?></a>
		<?php } ?>
		<?php if ($page < $pages_count) { ?>
			<a href="<?php echo admin_url('admin.php'); ?>?page=<?php echo $context . $mod_url['orderby'] . $mod_url['order'] . $mod_url['search'] . $mod_url['items_per_page'] . $mod_url['status']; ?>&amp;paged=<?php echo $page + 1; ?>"
			   class="wpv-filter-navigation-link"><?php echo __('Next page', 'wpv-views'); ?> &raquo;</a>
		<?php } ?>
		<?php _e('Items per page', 'wpv-views'); ?>
		<select class="js-items-per-page">
			<option value="10"<?php if ($ddl_items_per_page == '10') echo ' selected="selected"'; ?>>10
			</option>
			<option value="20"<?php if ($ddl_items_per_page == '20') echo ' selected="selected"'; ?>>20
			</option>
			<option value="50"<?php if ($ddl_items_per_page == '50') echo ' selected="selected"'; ?>>50
			</option>
		</select>
		<a href="#" class="js-wpv-display-all-items"><?php _e('Display all items', 'wpv-views'); ?></a>
	</div>
	<!-- .tablenav-pages -->
</div><!-- .wpv-listing-pagination -->