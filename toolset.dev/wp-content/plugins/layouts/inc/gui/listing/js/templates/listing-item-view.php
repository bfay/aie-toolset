<script type="text/html" id="table-listing-item">

	<#
		var indent = 0,
			used_on_class = ''
			show_used_on = true
			colspan = '';

		if( typeof ddl.status != 'undefined' && ddl.status == 'trash' )
		{
				show_used_on = false;
				colspan = 'colspan=2';
		}

	   if( ddl.parent && ddl.status != 'trash' )
	   {
	   		for(var i=1;i<ddl.depth+1;i++)
	   		{

	   			indent = i;
	   		}
	   }

	   var strike = ddl.is_assigned ? 'strike' : ''
	       , disabled = ddl.is_assigned ? 'disabled' : '';
	#>
	<td class="select-listed-item">
		<input {{disabled}} type="checkbox" name="items[]" class="js-selected-items" value="{{ ddl.ID }}"/>
	</td>
		<td class="post-title page-title column-title" style="padding-left:{{{ 16 * indent }}}px">
			<# if(ddl.post_status == 'publish' ){ #>
				<strong><a class="row-title" href="admin.php?page=dd_layouts_edit&layout_id={{{ ddl.ID }}}&action=edit" title="<?php _e('Edit ', 'ddl-layouts'); ?>{{{ ddl.post_title.replace(/"/g, '&quot') }}}">{{{ ddl.post_title }}}</a></strong>
			<# }else{ #>
				<strong>{{{ ddl.post_title }}}</strong>
			<# } #>

			<div class="row-actions">
					<#
					var data = {
						layout_id : ddl.ID,
						trash_nonce : "<?php echo wp_create_nonce( 'layout-select-trash-nonce' ); ?>",
						delete_nonce : "<?php echo wp_create_nonce( 'layout-delete-layout-nonce' ); ?>",
						duplicate_nonce : "<?php echo wp_create_nonce( 'layout-duplicate-layout-nonce' );?>",
						nonce : "<?php echo wp_create_nonce( 'layout-select-set-change-nonce' );?>",
						group:ddl.group
						};#>
	                        <# if( ddl.post_status == 'publish' ){

	                        	data.value = 'trash'; #>

								<span class="edit"><a href="admin.php?page=dd_layouts_edit&layout_id={{ ddl.ID }}&action=edit" title="<?php _e('Edit this layout', 'ddl-layouts'); ?>"><?php _e('Edit', 'ddl-layouts');?></a> | </span>

					<span class="restore"><a <# data.value = 'duplicate'#> class="select-layout-action-in-listing-page js-select-layout-action-in-listing-page" data-object="{{ JSON.stringify(data) }}"><?php _e('Duplicate', 'ddl-layouts');?></a> | </span>

					<#
				if( ddl.is_parent === false ){ #>

				<span class="restore"><a <# data.value = "change"#> class="select-layout-action-in-listing-page js-select-layout-action-in-listing-page" data-object="{{ JSON.stringify(data) }}"><?php _e('Change layout use', 'ddl-layouts');?></a> | </span>
				<# } else { #>
				<span class="restore strike"><a <# data.value = "change"#> class="select-layout-action-in-listing-page js-layout-listing-restore-link strike" data-object="{{ JSON.stringify(data) }}" title="SHOULD DISPLAY THIS TEXT"><?php _e('Change layout use', 'ddl-layouts');?></a></span> |

				<# } #>

					<span class="restore {{strike}}" title="SHOULD DISPLAY THIS TEXT"><a  href="" class="js-layout-listing-restore-link {{strike}}" data-object="<# data.value = 'trash'#>{{ JSON.stringify(data) }}" title="<?php _e('Trash', 'ddl-layouts'); ?>"><?php _e('Trash', 'ddl-layouts');?></a>  </span>

							<#
							}
							else{
								data.value = 'publish';
							#>

			<span class="restore"><a href="" class="js-layout-listing-restore-link" data-object="{{ JSON.stringify(data) }}" title="<?php _e('Restore', 'ddl-layouts'); ?>"><?php _e('Restore', 'ddl-layouts');?></a> | </span>
			<span class="delete"><a href="" class="submitdelete js-layout-listing-delete-permanently-link" data-object="<# data.value = 'delete' #>{{ JSON.stringify(data) }}" title="<?php _e('Delete permanently', 'ddl-layouts'); ?>"><?php _e('Delete permanently', 'ddl-layouts');?></a></span>

				<# } #>

			<div class="ddl-dialogs-container">
				<div id="ddl-change-layout-use-for-post-types-box-{{{ ddl.ID }}}-{{{ ddl.group }}}" class="ddl-dialog">
					<div class="ddl-dialog-header">
						<h2><?php _e('Change {{{ ddl.post_title }}} Layout use','ddl-layouts'); ?></h2>
						<i class="icon-remove js-edit-dialog-close"></i>
					</div>
					<div class="ddl-dialog-content">

					</div>

					<div class="ddl-dialog-footer">
                        <div class="dialog-change-use-messages" data-text="<?php echo WPDD_LayoutsListing::$OPTIONS_ALERT_TEXT; ?>"></div>
						<button class="button js-edit-dialog-close close-change-use"><?php _e('Close', 'ddl-layouts') ?></button>
					<!--	<button class="button button-primary js-ddl-update-post-types-change">
						<?php echo sprintf(__('Update %s now', 'ddl-layouts'), 'Layout') ?></button>-->
					</div>
				</div>
			</div>

      </div>
		</td>
		<#
		if( typeof ddl.post_types != 'undefined' && ddl.group === 3 ){ #>

				<td class="column-used-on {{{ used_on_class }}} wpv-admin-listing-col-usage">
				<ul class="wpv-taglike-list">
					<#
                        _.each( ddl.post_types, function( v ){
							if( +v.missing === 0 ){
					#>

                			<li>{{{ ' '+v.label }}}</li>

           			 <#
           			 	} else { #>
<li>{{{ ' '+v.label+' ' }}}<span class="js-alret-icon-hide-post"><a data-object="{{ JSON.stringify(v) }}" class="apply-for-all js-apply-layout-for-all-posts-js js-alert-icon-hide-{{{ v.type }}} button button-small button-leveled icon-warning-sign js-apply-for-all-posts js-alret-icon-hide-post"> <?php _e('Use this layout for ', 'ddl-layouts'); ?>{{{ v.missing }}} {{{ v.label }}} </a></span></li>
           			 <#	}
           			 }); #>
           			 <ul>
           		</td>

		<#
		}
		else if( typeof ddl.posts != 'undefined' && ddl.group === 2 )
		 {
		 	var used_on = '';

			_.each( ddl.posts, function(v, i, l){
					var len = l.length;
					if( i < len-1 && len > 1 )
					{
						used_on += v.post_title +', ';
					}
					else
					{
						used_on += v.post_title;
					}
				}); #>
				<# if( show_used_on ){ #>
				<td class="column-used-on {{{ used_on_class }}}">{{{ used_on }}}</td>
				<# } #>
		  <# }
		  else if( typeof ddl.loops != 'undefined' && ddl.group === 4 && ddl.loops.length > 0 )
		 {
		 	var used_on = '';

			_.each( ddl.loops, function(v, i, l){
					var len = l.length;
					if( i < len-1 && len > 1 )
					{
						used_on += v +', ';
					}
					else
					{
						used_on += v;
					}
				}); #>
				<# if( show_used_on ){ #>
				<td class="column-used-on {{{ used_on_class }}}">{{{ used_on }}}</td>
				<# } #>
		  <# }
			else
			{
				used_on = ' - ';
				used_on_class = ' not-used-yet'; #>
				<# if( show_used_on ){ #>
				<td class="column-used-on {{{ used_on_class }}}">{{{ used_on }}}</td>
				<# } #>
		<#	}
		 #>

		<td class="post-title page-title column-date">{{{ ddl.date_formatted }}}</td>

</script>