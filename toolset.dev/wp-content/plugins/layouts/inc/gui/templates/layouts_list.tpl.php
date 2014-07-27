<?php
    //TODO: this file is depracated and should be removed
	global $wpddlayout;
?>

<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-dd_layouts"></div>
    <h2>
        <?php _e('Layouts', 'ddl-layouts');?>
        <a href="#" class="add-new-h2 js-layout-add-new-top"><?php _e('Add a new Layout', 'ddl-layouts');?></a>
	    <?php if ( $this->s_param !== null ) {
		    $search_message = __('Search results for "%s"','ddl-layouts');
		    if ( isset( $_GET["status"] ) && 'trash' == sanitize_text_field( $_GET["status"] ) ) {
			    $search_message = __('Search results for "%s" in trashed Views', 'ddl-layouts');
		    } ?>
		    <span class="subtitle">
						<?php echo sprintf( $search_message, $this->s_param ); ?>
					</span>
	    <?php } ?>
    </h2>

	<ul class="subsubsub" style="clear:left"><!-- links to lists WPA in different statuses -->
		<li><a href="<?php echo admin_url('admin.php'); ?>?page=dd_layouts&amp;status=publish"<?php if ( $this->get_arg('post_status') == 'publish' && !isset( $_GET["search"] ) ) echo ' class="current"'; ?>><?php _e('Published', 'ddl-layouts'); ?></a> (<?php echo  $this->get_count_published(); ?>) | </li>
		<li><a href="<?php echo admin_url('admin.php'); ?>?page=dd_layouts&amp;status=trash"<?php if ( $this->get_arg('post_status') == 'trash' && !isset( $_GET["search"] ) ) echo ' class="current"'; ?>><?php _e('Trash', 'ddl-layouts'); ?></a> (<?php echo $this->get_count_trash(); ?>)</li>
	</ul>

	<?php if ( $this->found_posts() > 0 ) { ?>

	   <div class="tablenav top">
		   <div class="alignleft actions bulkactions">
			   <select name="select-bulk-action" class="js-select-bulk-action">
				   <option value="-1" selected="selected"><?php _e('Bulk Actions', 'ddl-layouts');?></option>
				   <?php if( $this->get_count_what() === 'trash' ): ?>
					<option value="trash"><?php _e('Move to Trash', 'ddl-layouts');?></option>
				  <?php elseif( $this->get_count_what() === 'publish' ): ?>
					   <option value="publish"><?php _e('Restore', 'ddl-layouts');?></option>
					   <option value="delete"><?php _e('Delete permanently', 'ddl-layouts');?></option>
				 <?php endif; ?>

			   </select>

			   <?php
			        $data = array(

				        'trash_nonce' => wp_create_nonce( 'layout-select-trash-nonce' ),
				        'delete_nonce' => wp_create_nonce( 'layout-delete-layout-nonce' )
			        );
			   ?>

			   <input data-object="<?php echo htmlspecialchars( json_encode( $data ) ); ?>" type="submit" name="" id="doaction" class="button action js-do-bulk-action" value="<?php _e('Apply', 'ddl-layouts');?>">
		   </div>

		   <div class="alignright">
			   <form id="posts-filter" action="" method="get"><!-- form to search Views-->
				   <p class="search-box">
					   <input type="hidden" name="page" value="dd_layouts" />
					   <input type="hidden" name="status" value="<?php echo $this->get_arg('post_status');?>" />
					   <label class="screen-reader-text" for="post-search-input"><?php _e('Search Views','ddl-layouts'); ?>:</label>
					   <input type="search" id="post-search-input" name="search" value="<?php echo isset( $this->s_param ) ? $this->s_param : ''; ?>" />
					   <input type="submit" name="" id="search-submit" class="button" value="<?php echo htmlentities( __('Search Layouts','ddl-layouts'), ENT_QUOTES ); ?>" />
					   <input type="hidden" name="paged" value="1" />
				   </p>
			   </form>
			</div>

	   </div>
	<input type="hidden" name="page" value="dd_layouts" />
	<input type="hidden" name="status" value="<?php echo $this->get_arg('post_status');?>" />
	<input type="hidden" name="paged" value="1" />
    <table class="wp-list-table widefat fixed posts dd-layouts-list" cellspacing="0">
        <thead>
            <tr>
	            <th scope="col" id="bulk-action-all" class="manage-column column-bulk-actions js-column-bulk-actions" style=""><input type="checkbox" class="js-select-all-layouts" name="bulk_select" /></th>
	            <th scope="col" id="title" class="toolset-admin-listing-col-title column-title" style=""><a href="<?php echo admin_url('admin.php'); ?>?page=dd_layouts&amp;orderby=title&amp;order=<?php echo $this->column_sort_to . $this->mod_url['search'] . $this->mod_url['items_per_page'] . $this->mod_url['paged'] . $this->mod_url['status']; ?>" class="js-views-list-sort views-list-sort<?php echo $this->column_active; ?>" data-orderby="title"><?php _e('Title','ddl-layouts') ?> <i class="icon-sort-by-alphabet<?php if ( $this->column_sort_now === 'DESC') echo '-alt'; ?>"></i></a></th>
	            <th scope="col" id="used-on" class="manage-column column-used-on" style=""><?php _e('Used on', 'ddl-layouts');?></th>
	            <th scope="col" id="action" class="manage-column column-action" style=""><?php _e('Action', 'ddl-layouts');?></th>
                <th scope="col" id="date" class="toolset-admin-listing-col-date column-date" style=""><a href="<?php echo admin_url('admin.php'); ?>?page=dd_layouts&amp;orderby=date&amp;order=<?php echo $this->column_sort_date_to . $this->mod_url['search'] . $this->mod_url['items_per_page'] . $this->mod_url['paged'] . $this->mod_url['status']; ?>" class="js-views-list-sort views-list-sort<?php echo $this->column_active; ?>" data-orderby="date"><?php _e('Date','dd_layouts') ?> <i class="icon-sort-by-attributes<?php if ( $this->column_sort_date_now === 'DESC') echo '-alt'; ?>"></i></a></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
	            <th scope="col" id="bulk-action-all-foot" class="manage-column column-bulk-actions js-column-bulk-actions" style=""><input type="checkbox" class="js-select-all-layouts" name="bulk_select" /></th>
                <th scope="col" id="title-foot" class="toolset-admin-listing-col-title column-title" style=""><?php _e('Title', 'ddl-layouts');?></th>
	            <th scope="col" id="used-on-foot" class="manage-column column-used-on" style=""><?php _e('Used on', 'ddl-layouts');?></th>
	            <th scope="col" id="action-foot" class="manage-column column-action" style=""><?php _e('Action', 'ddl-layouts');?></th>
                <th scope="col" id="date-foot" class="toolset-admin-listing-col-date column-date" style=""><?php _e('Date', 'ddl-layouts');?></th>
            </tr>
        </tfoot>

        <tbody id="the-list" class="listing-page-table-list">
            <?php foreach ($this->get_layout_list() as $layout_info){
                if(trim($layout_info->post_title)==''){$layout_info->post_title = '(no title)';}
                ?>
                <tr class="type-dd_layouts status-publish hentry alternate iedit" valign="top">
	                <td class="select-listed-item">
		                <input type="checkbox" name="items[]" class="js-selected-items" value="<?php echo $layout_info->ID;?>"/>
	                </td>
                    <td class="post-title page-title column-title">
	                    <?php if( $this->get_count_what() === 'trash' ):

		                    ?>
                            <strong><a class="row-title" href="admin.php?page=dd_layouts_edit&layout_id=<?php echo $layout_info->ID;?>&action=edit" title="<?php _e('Edit', 'ddl-layouts'); ?>"<?php echo htmlentities($layout_info->post_title);?>\""><?php echo $layout_info->post_title;?></a></strong>
		                <?php elseif( $this->get_count_what() === 'publish' ): ?>
                            <strong><?php echo $layout_info->post_title;?></strong>
                        <?php endif; ?>

                        <div class="row-actions">
	                        <?php if( $this->get_count_what() === 'trash' ):
		                        $this->print_edit_and_trash_links( $layout_info->ID );
		                    elseif( $this->get_count_what() === 'publish' ):
		                        $this->print_delete_and_restore_links( $layout_info->ID );
	                         endif; ?>
                        </div>
                    </td>
	                <td class="column-used-on"><?php $wpddlayout->post_types_manager->print_layout_post_types($layout_info->ID);?></td>

	                <td class="column-action"><?php $this->print_layout_action_select( $layout_info->ID, $this->get_arg('post_status') );?>
		                <div class="ddl-dialogs-container">
			                <div id="ddl-change-layout-use-for-post-types-box-<?php echo $layout_info->ID;?>" class="ddl-dialog auto-width">
				                <div class="ddl-dialog-header">
					                <h2><?php _e('Change Layout use','ddl-layouts'); ?></h2>
					                <i class="icon-remove js-edit-dialog-close"></i>
				                </div>
				                <div class="ddl-dialog-content">

				                </div>
				                <div class="ddl-dialog-footer">
					                <button class="button js-edit-dialog-close"><?php _e('Cancel', 'ddl-layouts') ?></button>
					                <button class="button button-primary js-ddl-update-post-types-change">
						                <?php echo sprintf(__('Update %s now', 'ddl-layouts'), 'Layout') ?></button>
				                </div>
			                </div>
		                </div>

	                </td>
                    <td class="date column-date"><abbr title="<?php echo $layout_info->post_date;?>"><?php echo $layout_info->post_date;?></abbr></td>
                </tr>

            <?php } ?>
        </tbody>
    </table>
    <div class="clear"></div>

	<?php
		$this->ddl_admin_listing_pagination('dd_layouts', $this->found_posts(), $this->get_arg('posts_per_page'), $this->get_mod_url() );
	?>

	<?php } else { // No Views matches the criteria ?>
		<div class="clear"></div>
		<div class="ddl-layouts-listing views-empty-list">
			<?php if ( isset( $_GET["status"] ) && $_GET["status"] == 'trash' && isset( $_GET["search"] ) && $_GET["search"] != '' ) { ?>
				<p><?php echo __('No Layouts in trash matched your criteria.','ddl-layouts'); ?><div class="clear"></div> <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=dd_layouts<?php echo $this->mod_url['orderby'] . $this->mod_url['order'] . $this->mod_url['items_per_page']; ?>&amp;paged=1&amp;status=trash"><?php _e('View all layouts', 'ddl-layouts'); ?></a></p>
			<?php } else if ( isset( $_GET["status"] ) && $_GET["status"] == 'trash' ) { ?>
				<p><?php echo __('No Layouts in trash.','ddl-layouts'); ?> <div class="clear"></div><a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=dd_layouts<?php echo $this->mod_url['orderby'] . $this->mod_url['order'] . $this->mod_url['items_per_page']; ?>&amp;paged=1"><?php _e('View all layouts', 'ddl-layouts'); ?></a></p>
			<?php } else if ( isset( $_GET["search"] ) && $_GET["search"] != '' ) { ?>
				<p><?php echo __('No Layouts matched your criteria.','ddl-layouts'); ?><div class="clear"></div> <a class="button-secondary" href="<?php echo admin_url('admin.php'); ?>?page=dd_layouts<?php echo $this->mod_url['orderby'] . $this->mod_url['order'] . $this->mod_url['items_per_page']; ?>&amp;paged=1"><?php _e('View all layouts', 'ddl-layouts'); ?></a></p>
			<?php } ?>
		</div>
	<?php } ?>

</div>

