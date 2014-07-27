<?php
/*
 * Theme Views content grid cell type.
 * Displays current theme basic footer with two credits area.
 *
 */

/*
 * Render preview for view
 */
add_action('wp_ajax_ddl_views_content_grid_preview', 'ddl_views_content_grid_preview');
function ddl_views_content_grid_preview(){
	if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                        'ddl_layout_view_nonce')) {
            die('verification failed');
        }	
		
	global $wpdb;

	if ( isset($_POST['view_id']) ){
		$view_id = $_POST['view_id'];
	}else{
		return __('View not set','ddl-layouts');
	}
	$layout_style = array(
		'unformatted' => __('Unformatted','ddl-layouts'),
		'bootstrap-grid' => __('Unformatted','ddl-layouts'),
		'table' => __('Table-based grid','ddl-layouts'),
		'table_of_fields' => __('Table','ddl-layouts'),
		'un_ordered_list' => __('Unordered list','ddl-layouts'),
		'ordered_list' => __('Ordered list','ddl-layouts')
	);
	$view = $wpdb->get_results( $wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE ID = %d AND post_type='view'",$view_id) );
	if ( isset($view[0]) ){
		$post_title = $view[0]->post_title;
		$id = $view[0]->ID;
		$view_settings = get_post_meta($id,'_wpv_settings',true);
		$meta = get_post_meta($id,'_wpv_layout_settings',true);
		
		if ($view_settings['view-query-mode'] == 'normal') {
			$view_output = get_view_query_results($id);
		} else {
			$view_output = array();
			
			if ($meta['style'] == 'bootstrap-grid' || $meta['style'] == 'table') {
				if ($meta['style'] == 'bootstrap-grid') {
					$col_number = $meta['bootstrap_grid_cols'];
				} else {
					$col_number = $meta['table_cols'];
				}
				
				// add 2 rows of items.
				for ($i = 1; $i <= 2 * $col_number; $i++) {
					$item = new stdClass();
					$item->post_title = sprintf(__('Post %d', 'ddl-layouts'), $i);
					$view_output[] = $item;
				}
				
			} else {
				// just add 3 items
				for ($i = 1; $i <= 3; $i++) {
					$item = new stdClass();
					$item->post_title = sprintf(__('Post %d', 'ddl-layouts'), $i);
					$view_output[] = $item;
				}
			}
			
		}
		ddl_views_generate_cell_preview( $post_title, $id, $meta, $view_output );
	}

	die();

}

/*
 * Create new view and output view info
 * $id, $slug, $title
 */
add_action('wp_ajax_ddl_create_new_view', 'ddl_create_new_view');
function ddl_create_new_view(){
	global $wpdb;

	if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                        'ddl_layout_view_nonce')) {
            die('verification failed');
    }
	
	$view_type = 'normal';
	if (isset($_POST['layouts-loop'])) {
		$view_type = 'layouts-loop';
	}
	
	$name = $original_name = $_POST['cell_name'];
	$i = 0;
	$name_in_use = true;
	while( $name_in_use ){
		$i++;
		$postid = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $name . "' AND post_type='view'" );
		if ( $postid ) {
			$name = $original_name . ' ' . $i;
		}
		else{
			$name_in_use = false;
		}
	}
	$args = array(
		'title' => $name,
		'settings' => array('purpose' => 'bootstrap-grid',
							'view-query-mode' => $view_type),
		'cols' => $_POST['cols'],
		''
	);
	$view_id = wpv_create_view( $args );
    if ( isset( $view_id['success']) ){

		$id = $view_id['success'];
		
		// set it to filter posts by default.
		$view_settings = get_post_meta($id, '_wpv_settings', true);
		$view_settings['post_type'] = array('post');

		if ($view_type == 'layouts-loop') {
			// Add pagination shortcodes for the Views layout loop
			$view = get_post($id);
			$pagination = "\n";
			$pagination .= '[ddl-pager-prev-page][wpml-string context="ddl-layouts"]Older posts[/wpml-string][/ddl-pager-prev-page]';
			$pagination .= ' [ddl-pager-next-page][wpml-string context="ddl-layouts"]Newer posts[/wpml-string][/ddl-pager-next-page]';
			$view->post_content .= $pagination;
			wp_update_post($view);
			
			// show the content section for pagination.
			unset($view_settings['sections-show-hide']['content']);
		}

		update_post_meta($id, '_wpv_settings', $view_settings);
		
		$res = $wpdb->get_results( "SELECT post_name FROM $wpdb->posts WHERE ID = '" . $id . "' AND post_type='view'" );
		$post_name = $res[0]->post_name;
		$output = json_encode(array( 'id'=>$id, 'post_name' => $post_name, 'post_title'=> $name));
		print json_encode(array( 'id'=>$id, 'post_name' => $post_name, 'post_title'=> $name));
		die();
	}

	die();
}

add_shortcode('ddl-pager-prev-page', 'ddl_pagination_previous_shortcode');

function ddl_pagination_previous_shortcode($atts, $value) {
	
	return get_next_posts_link(do_shortcode($value));
}

add_shortcode('ddl-pager-next-page', 'ddl_pagination_next_shortcode');

function ddl_pagination_next_shortcode($atts, $value) {
	
	return get_previous_posts_link(do_shortcode($value));
}

/*
 * Get settings about the View
 * $id, $slug, $title
 */
add_action('wp_ajax_ddl_get_settings_for_view', 'ddl_get_settings_for_view');
function ddl_get_settings_for_view(){
	global $wpdb;

	if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                        'ddl_layout_view_nonce')) {
            die('verification failed');
        }

	$result = array();
	
	if ( isset($_POST['view_id']) ){
		$view_id = $_POST['view_id'];
		$view = $wpdb->get_results( $wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE ID = %d AND post_type='view'",$view_id) );
		if ( isset($view[0]) ){
			$id = $view[0]->ID;
			$meta = get_post_meta($id,'_wpv_layout_settings',true);
			if (ddl_confirm_ok_to_change_grid_cols($meta)) {
				$result['grid_settings'] = $meta['bootstrap_grid_cols'];
			}
			$result['title'] = $view[0]->post_title;
			
		}
	}
	
	print json_encode($result);
		
	die();
}

/*
 * Save the View settings for columns
 */

add_action('wp_ajax_ddl_save_view_columns', 'ddl_save_view_columns');
function ddl_save_view_columns(){
	global $wpdb;

	if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
                        'ddl_layout_view_nonce')) {
            die('verification failed');
        }

	$result = array();
		
	if ( isset($_POST['view_id']) ) {
		$view_id = $_POST['view_id'];
		$view = $wpdb->get_results( $wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE ID = %d AND post_type='view'",$view_id) );
		if ( isset($view[0]) ){
			$id = $view[0]->ID;
			$meta = get_post_meta($id,'_wpv_layout_settings',true);
			if (ddl_confirm_ok_to_change_grid_cols($meta)) {
				if ($_POST['cols'] != $meta['bootstrap_grid_cols']) {
					$meta_html_current = $meta['layout_meta_html'];
					// find the content template used
					$match = array();
					
			        if (preg_match('/\[wpv-post-body view_template="(.*?)\"\]/', $meta_html_current, $match)) {
						$template = $match[1];
						$meta['bootstrap_grid_cols'] = $_POST['cols'];
						$new_meta_html = wpv_create_bootstrap_meta_html( $meta['bootstrap_grid_cols'],
																$template,
																$meta_html_current);
						$meta['layout_meta_html'] = $new_meta_html;
					}
					
					
					update_post_meta($id, '_wpv_layout_settings', $meta);
				}
			}
			
		}
	}

	print json_encode($result);
	
	die();
}
	
function ddl_confirm_ok_to_change_grid_cols ($meta) {
	$ok_to_update = false;
	
	if (isset($meta['style']) && $meta['style'] == 'bootstrap-grid') {
		if (function_exists('wpv_create_bootstrap_meta_html')) {
			$meta_html_current = $meta['layout_meta_html'];
			// find the content template used
			$match = array();
			
			if (preg_match('/\[wpv-post-body view_template="(.*?)\"\]/', $meta_html_current, $match)) {
				$template = $match[1];
				$old_test = wpv_create_bootstrap_meta_html( $meta['bootstrap_grid_cols'],
															$template,
															$meta_html_current);
				
				if (preg_replace('/\s+/', '', $old_test) == preg_replace('/\s+/', '', $meta_html_current)) {
					$ok_to_update = true;
				}
			}
		} else {
			// set it to true so that the column select or shown.
			$ok_to_update = true;
		}
	}
	
	return $ok_to_update;
	
}


if ( ! function_exists('register_views_content_grid_cell_init') ) {

	function register_views_content_grid_cell_init() {
		if ( function_exists('register_dd_layout_cell_type') ) {
			register_dd_layout_cell_type('views-content-grid-cell',
				array(
					'name'						=> __('Views Content Grid', 'ddl-layouts'),
					'description'				=> __('Content driven list, displayed as a grid. This cell is powered by the Views plugin, where you can customize the content of cells, as well as the grid itself.', 'ddl-layouts'),
					'category'					=> __('Grids', 'ddl-layouts'),
					'button-text'				=> __('Assign Views Content Grid', 'ddl-layouts'),
					'dialog-title-create'		=> __('Create a new Views Content Grid', 'ddl-layouts'),
					'dialog-title-edit'			=> __('Edit Views Content Grid cell', 'ddl-layouts'),
					'dialog-template-callback'	=> 'views_content_grid_dialog_template_callback',
					'cell-content-callback'		=> 'views_content_grid_content_callback',
					'cell-template-callback'	=> 'views_content_grid_template_callback',
					'category-icon-css'		   => 'icon-table',
					'preview-image-url'			=>  WPDDL_RES_RELPATH . '/images/views_grid.png',
                    'icon-css' => 'icon-views ont-color-orange ont-icon-22',
					'register-scripts'		   => array(
						array( 'ddl_views_content_grid_js', WPDDL_RELPATH . '/inc/gui/dialogs/js/views-grid-cell.js', array( 'jquery' ), WPDDL_VERSION, true ),
					),
				)
			);
		}
	}
	add_action( 'init', 'register_views_content_grid_cell_init' );


	function views_content_grid_dialog_template_callback() {
		global $WP_Views;
		if( class_exists('WP_Views') ){
			$show_existing_views_dropdown = '';
			$i = 0;
			ob_start();
			?>
			<p>
				<label class="radio">
					<?php $checked = ( get_ddl_field('ddl_layout_view_id') == '' )?' checked="checked" ':'';?>
					<input type="radio" name="view-grid-view-action" class="js-ddl-views-grid-existing" value="existing_layout" <?php echo $checked?> >
					<?php _e('Use an existing View', 'ddl-layouts');?>
				</label>
			</p>
			<p class="js-ddl-select-existing-view">
				<select name="<?php the_ddl_name_attr('ddl_layout_view_id'); ?>" class="ddl-view-select js-ddl-view-select">
				<option value="" data-mode="both"><?php _e('None','ddl-layouts');?></option>';
				<?php
				$wpv_args = array( // array of WP_Query parameters
					'post_type' => 'view',
					'posts_per_page' => -1,
					'order' => 'ASC',
					'orderby' => 'title',
					'post_status' => 'publish'
				);
				$wpv_query = get_posts( $wpv_args );
				$wpv_count_posts = count($wpv_query);
				if ( $wpv_count_posts > 0 ) {
					foreach ( $wpv_query as $post ) :
						if (!$WP_Views->is_archive_view($post->ID)){
							$view_settings = $WP_Views->get_view_settings($post->ID);

							$i++;
							?>
							<option data-id="<?php echo $post->ID; ?>" value="<?php echo $post->ID; ?>" data-mode="<?php echo $view_settings['view-query-mode']; ?>"><?php echo $post->post_title; ?></option>
							<?php
						}
					endforeach;
				}
				?>
				</select>
				<?php if ( isset($WP_Views) && class_exists('WP_Views') && $WP_Views->is_embedded()):?>
					<button class="button js-ddl-edit-view-link js-ddl-edit-view-link-first"><?php _e('Examine the View settings', 'ddl-layouts'); ?></button>
				<?php else: ?>
					<button class="button js-ddl-edit-view-link js-ddl-edit-view-link-first"><?php _e('Edit the View settings', 'ddl-layouts'); ?></button>
				<?php endif; ?>
			</p>
			<?php

			$show_existing_views_dropdown = ob_get_clean();
			$count_existing_views = $i;
			if ( $i == 0){
			 	$show_existing_views_dropdown = '<div class="ddl_existing_views_content" style="display:none">'.$show_existing_views_dropdown.'</div>';
			}else{
			 	$show_existing_views_dropdown = '<div class="ddl_existing_views_content">'.$show_existing_views_dropdown.'</div>';
			}

		}

		ob_start();

		//If Views activated
		if( defined('WPV_VERSION') && version_compare(WPV_VERSION, '1.6.1', '<=') ){ ?>
			<input type="hidden" value="0" class="js-views-content-grid_is_views_installed" />
			<p>
				<i class="icon-views-logo ont-color-orange ont-icon-24"></i>
<?php echo sprintf(__('This cell requires the Views plugin. Install and activate the Views plugin and you will be able to create custom content-driven grids.', 'ddl-layouts'), WPV_VERSION); ?>
			</p>
		<?php }
		else{

			?>
			<input type="hidden" value="1" class="js-views-content-grid_is_views_installed" />
			<?php 
			
			if ( isset($WP_Views) && class_exists('WP_Views') && ( !$WP_Views->is_embedded() || ($WP_Views->is_embedded() && $count_existing_views>0)) ){
			?>
			<div class="ddl-form">
				<fieldset>
					<legend><?php _e('View:', 'ddl-layouts'); ?></legend>
					
					<div class="fields-group">
						<?php 
						$disabled = '';
						if ( isset($WP_Views) && class_exists('WP_Views') && $WP_Views->is_embedded()){
							$disabled = ' style="display: none;"';
						}
						
						?>
						<p<?php echo $disabled?>>
							<label class="radio">
								<?php $checked = ( get_ddl_field('ddl_layout_view_id') == '' )?' checked="checked" ':'';?>
								<input type="radio" name="view-grid-view-action" class="js-ddl-views-grid-create" checked="checked" <?php echo $checked?> value="new_layout" >
								<?php _e('Create new View', 'ddl-layouts');?>
							</label>
						</p>
						<?php echo $show_existing_views_dropdown?>
					</div>
					
				</fieldset>
			</div>
			<?php }?>
			<div class="ddl-form">
				<div class="js-fluid-grid-designer">
					<fieldset>
						<legend><?php _e('Grid size:', 'ddl-layouts'); ?></legend>
						<div class="fields-group">
							<div class="js-fluid-views-grid-slider-horizontal horizontal-slider"></div>
							<div class="js-fluid-views-grid-slider-vertical vertical-slider"></div>
							<div class="grid-designer-wrap grid-designer-wrap-views">
								<div class="grid-info-wrap">
									<span class="js-fluid-views-grid-info-container grid-info"></span>
								</div>
								<div class="js-fluid-views-grid-designer grid-designer"
									data-rows="1"
									data-cols="2"
									data-max-cols="12"
									data-max-rows="1"
									data-slider-horizontal="#ddl-default-edit .js-fluid-views-grid-slider-horizontal"
									data-slider-vertical=""
									data-info-container="#ddl-default-edit .js-fluid-views-grid-info-container"
									data-message-container="#ddl-default-edit .js-views-fluid-grid-message-container"
									data-fluid="true">
								</div>
							</div>
							<button class="views-grid-button button js-create-and-edit-view"><?php _e('Create the View and edit it', 'ddl-layouts'); ?></button>
							<?php if( isset($WP_Views) && class_exists('WP_Views') && $WP_Views->is_embedded() ): ?>
								<button class="views-grid-button button js-ddl-edit-view-link"><?php _e('Examine the View settings', 'ddl-layouts'); ?></button>
							<?php else: ?>
								<button class="views-grid-button button js-ddl-edit-view-link"><?php _e('Edit the View settings', 'ddl-layouts'); ?></button>
							<?php endif; ?>
							<div id="js-fluid-views-grid-message-container"></div>
						</div>
					</fieldset>
				</div>
			</div>

		<?php
		 echo wp_nonce_field('ddl_layout_view_nonce', 'ddl_layout_view_nonce', true, false);
		}

		if( isset($WP_Views) && class_exists('WP_Views') && $WP_Views->is_embedded() ){
			?>
			<div class="toolset-alert toolset-alert-info">
				<?php if ($count_existing_views != 0): ?>
					<p>
						<?php _e('You are using the embedded version of Views. Install and activate the full version of Views and you will be able to create custom content-driven grids.', 'ddl-layouts'); ?>
						<br>
						<br>
						<a class="fieldset-inputs button button-primary-toolset" href="http://wp-types.com/home/views-create-elegant-displays-for-your-content?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=views-content-grid-cell&utm_term=get-views" target="_blank">
							<?php _e('Get Views plugin', 'ddl-layouts');?> &raquo;
						</a>
					</p>
				<?php endif; ?>
				<?php if ($count_existing_views == 0): ?>
					<p>
						<?php _e('You are using the embedded version of the Views Plugin and there are no Views available.', 'ddl-layouts'); ?>
						<br />
						<?php _e('You can download pre-built modules using the Module Manager plugin.', 'ddl-layouts'); ?>
						<br />
						<br />
						<?php if (defined( 'MODMAN_CAPABILITY' )): ?>
							<a class="fieldset-inputs button button-primary-toolset" href="<?php echo admin_url('admin.php?page=ModuleManager_Modules&amp;tab=library&amp;module_cat=layouts'); ?>" target="_blank">
								<i class="icon-download-alt"></i> <?php _e('Download Modules', 'ddl-layouts');?>
							</a>
						<?php else: ?>
							<a class="fieldset-inputs button button-primary-toolset" href="http://wp-types.com/home/module-manager?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=views-content-grid-cell&utm_term=get-module-manager" target="_blank">
								<?php _e('Get Module Manager plugin', 'ddl-layouts');?>
							</a>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>
			<?php
		}

		if( !defined('WPV_VERSION') ) {
			?>
				<div class="toolset-alert toolset-alert-info">
					<p>
						<i class="icon-views-logo ont-color-orange ont-icon-24"></i>
						<?php _e('This cell requires Views plugin. Install and activate Views and you will be able to create custom content-driven grids.', 'ddl-layouts'); ?>
						<br>
						<br>
						<a class="fieldset-input button button-primary-toolset" href="http://wp-types.com/home/views-create-elegant-displays-for-your-content?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=views-content-grid-cell&utm_term=get-views" target="_blank">
							<?php _e('Get Views plugin', 'ddl-layouts');?>
						</a>
					</p>
				</div>
			<?php
		}
		
		?>
			<div class="js-views-content-grid-help">
				<?php ddl_add_help_link_to_dialog(WPDLL_VIEWS_CONTENT_GRID_CELL, __('Learn about the Views Content Grid cell', 'ddl-layouts')); ?>
			</div>
		<?php


		return ob_get_clean();

	}

	function views_content_grid_template_callback() {
        global $WP_Views;
		if( class_exists('WP_Views') ){

	        ob_start();

	        ?> <div class="cell-content">
	                <p class="cell-name">{{ name }} </p>
	                <div class="cell-preview">
	                    <#
							if (content) {
								var preview = ddl_views_content_grid_preview( content.ddl_layout_view_id, '<?php _e('Updating', 'ddl-layouts'); ?>...', '<?php _e('Loading', 'ddl-layouts'); ?>...' );
								print( preview );
							}
	                    #>
	                </div>
	            </div>
	       <?php
	       return ob_get_clean();
	   }
	}



	function views_content_grid_content_callback() {
		//Render View
        if( function_exists('render_view') )
        {
            return render_view( array( 'id' => get_ddl_field('ddl_layout_view_id') ) ) ;
        }
        else
        {
            return WPDDL_Messages::views_missing_message();
        }

	}

}
function ddl_views_generate_cell_preview( $post_title, $id, $meta, $view_output ){
	$count_view_output = count($view_output);
	//Generate preview for bootstrap grid and table based grid
	if ( !isset($meta['style']) ){
		$meta['style'] = 'unformatted';	
	}
	if ( $meta['style'] == 'bootstrap-grid'  ):
		$col_number = $meta['bootstrap_grid_cols'];
		$i=$k=0;
		$col_width = 12/$col_number;
	?>
		<i class="icon-th-large ddl-view-layout-icon"></i><?php _e('Bootstrap grid', 'ddl-layouts'); ?>
		<br />
		<div class="presets-list fields-group">
		<?php
		$total_rows = 0;

		if ( $count_view_output > 0 ){
			for ($j = 0, $limit=$count_view_output; $j < $limit; $j++){
			$view_post = $view_output[$j];
			$cell_content = ddl_view_content_grid_get_title( $view_post );
			$i++;
			if ($i == 1){
				$total_rows++;
				if ( $total_rows > 3){
					$j = $count_view_output+1;
					$hidden_items_count = $limit-$k;
					$hidden_rows = ceil($hidden_items_count/$col_number);
					?>
					<div class="row-fluid">
						<div class="span-preset12 views-cell-preview views-cell-preview-more">
							<?php echo sprintf(__('Plus %s more rows - %s items in total', 'ddl-layouts'), $hidden_rows, $limit); ?>
						</div>
					</div>
					<?php
					continue;
				}
				?>
				<div class="row-fluid">
				<?php
				}
				?>
				<div class="span-preset<?php echo $col_width; ?> views-cell-preview" ><?php echo $cell_content; ?></div>
					<?php
					if ( $i == $col_number){
						$i=0;
						?></div><?php
					}
					$k++;
				}
				if ( $i != 0 ){
					?></div><?php
				}
		} else {
			//Show empty grid when no posts
			?>
			<div class="row-fluid">
			<?php
			for( $i=0; $i<$col_number; $i++){
			?>
				<div class="span-preset<?php echo $col_width;?> views-cell-preview" ></div>
			<?php
			}
			?>
			</div>
			<div class="row-fluid">
				<div class="span-preset12 views-cell-preview views-cell-preview-more">
					<?php _e('No items where returned by the View', 'ddl-layouts'); ?>
				</div>
			</div>
		<?php
		}
		?></div><?php
	elseif ( $meta['style'] == 'table' ):
		$col_number = $meta['table_cols'];
		$i=$k=0;
		$col_width = round(100/$col_number, 2)-2;
		$total_rows = 0;
		?>
		<i class="icon-th ddl-view-layout-icon"></i><?php _e('Table-based grid', 'ddl-layouts'); ?>
		<br />
		<?php
		if ( $count_view_output > 0 ){
			$total_rows = 0;
			for ($j = 0, $limit=$count_view_output; $j < $limit; $j++){
				$view_post = $view_output[$j];
				$cell_content = ddl_view_content_grid_get_title( $view_post );
				$i++;
				if ( $i == 1){
					$total_rows++;
					if ( $total_rows > 3){
						$j = $count_view_output+1;
						$hidden_items_count = $limit-$k;
						$hidden_rows = ceil($hidden_items_count/$col_number);
						?>
						<div class="row-fluid row">
							<div class="views-cell-table-preview views-cell-preview views-cell-preview-more views-cell-table-preview-more" style="width:100%;">
								<?php echo sprintf(__('Plus %s more rows - %s items in total', 'ddl-layouts'), $hidden_rows, $limit); ?>
							</div>
						</div>
						<?php
						continue;
					}
						?>
					<div class="row-fluid">
					<?php }	?>
					<div class="views-cell-preview views-cell-table-preview" style="width:<?php echo $col_width?>%;"><?php echo $cell_content;?></div>
					<?php
					if ( $i == $col_number ){
					$i = 0;
						?>
						</div>
					<?php }

				}
				if ( $i != 0 ){
					?></div><?php
				}
		} else {
			//If table 0 posts
			?>
			<div class="row-fluid">
			<?php
				for( $i=0; $i<$col_number; $i++){
					?>
					<div class="views-cell-preview views-cell-table-preview" style="width:<?php echo $col_width?>%;"></div>
				<?php
				}
				?>
			</div>
			<div class="row-fluid row">
				<div class="views-cell-table-preview views-cell-preview views-cell-preview-more views-cell-table-preview-more" style="width:100%;">
					<?php _e('No items where returned by the View', 'ddl-layouts'); ?>
				</div>
			</div>
			<?php
			}
	elseif ( $meta['style'] == 'unformatted' ||  $meta['style'] == 'un_ordered_list' || $meta['style'] == 'ordered_list' ):
		switch ($meta['style']) {
			case 'unformatted':
				$style_icon = 'icon-code';
				$style_name = __('Unformated', 'ddl-layouts');
				break;

			case 'un_ordered_list':
				$style_icon = 'icon-list-ul';
				$style_name = __('Unordered list', 'ddl-layouts');
				break;

			case 'ordered_list':
				$style_icon = 'icon-list-ol';
				$style_name = __('Ordered list', 'ddl-layouts');
				break;

		}
		?>
		<i class="<?php echo $style_icon; ?> ddl-view-layout-icon"></i><?php echo $style_name; ?>
		<br />
		<div class="presets-list fields-group">
		<?php
		for ( $i=0; $i<3; $i++ ){
			if (isset($view_output[$i])) {
				$view_post = $view_output[$i];
				$cell_content = ddl_view_content_grid_get_title( $view_post );
			} else {
				$cell_content = '';
			}
			?>
			<div class="row-fluid row">
				<?php if ( $meta['style'] == 'unformatted' ){?>
				<div class="span-preset12 views-cell-preview" >
					<?php echo $cell_content;?>
				</div>
				<?php }elseif(  $meta['style'] == 'un_ordered_list' || $meta['style'] == 'ordered_list' ){
					$list = '&#149;';
					if ( $meta['style'] == 'ordered_list' ){
						$list = $i+1;
					}
					?>
					<div class="views-cell-preview views-cell-table-preview views-cell-table-preview-no-border" style="width:8%;">
						<?php echo $list;?>
					</div>
					<div class="views-cell-preview views-cell-table-preview" style="width:85%;">
						<?php echo $cell_content;?>
					</div>
					<?php }?>
				</div>
				<?php
			}
			if ($count_view_output) {
				$cell_message = '';
				$limit = $count_view_output;
				if ( $limit > 3 ){
					$limit -= 3;
					$cell_message = sprintf(__('Plus %s more items', 'ddl-layouts'), $limit);
				}
			} else {
				$cell_message = __('No items where returned by the View', 'ddl-layouts');
			}
		?>
		<?php if ($cell_message): ?>
			<div class="row-fluid">
				<div class="span-preset12 views-cell-preview views-cell-preview-more">
					<?php echo $cell_message ?>
				</div>
			</div>
		<?php endif; ?>
		</div>
		<?php
	elseif ( $meta['style'] == 'table_of_fields' ):
		$col_number = (count($meta['fields'])+1)/5-1;
		$i=$k=0;
		$col_width = round(100/$col_number, 2)-2;
		$total_rows = 0;
		?>
		<i class="icon-table ddl-view-layout-icon"></i><?php _e('Table', 'ddl-layouts'); ?>
		<br />
		<div class="presets-list fields-group">
		<table class="ddl-view-table-preview" width="100%">
			<thead>
				<tr>

					<?php
					for ( $i=0,$limit=$col_number; $i<$limit; $i++ ){
						$col_title = __('Column ', 'ddl-layouts').' '.$i;
						if ( isset($meta['fields']['row_title_'.$i]) && !empty($meta['fields']['row_title_'.$i]) ){
							$col_title = $meta['fields']['row_title_'.$i];
						}
						?>
						<td width="<?php echo 100/count($meta['fields']); ?>%"><?php echo $col_title;?></td>
						<?php
					}
					?>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="<?php echo count($meta['fields']); ?>">

						<?php
						for ( $i=0; $i<3; $i++ ){
							if (isset($view_output[$i])) {
								$view_post = $view_output[$i];
								$cell_content = ddl_view_content_grid_get_title( $view_post );
							} else {
								$cell_content = '';
							}
							?>
							<div class="row-fluid row">
								<div class="span-preset12 views-cell-preview" >
									<?php echo $cell_content;?>
								</div>
							</div>
						<?php
						}
						$cell_message = __('No items where returned by the View', 'ddl-layouts');
						$limit = $count_view_output;
						if ( $limit > 3 ){
							$limit -= 3;
							$cell_message = sprintf(__('Plus %s more items', 'ddl-layouts'), $limit);
						}
						?>

					</td>
				</tr>
			</tbody>
		</table>
		<div class="presets-list fields-group">
			<div class="row-fluid">
				<div class="span-preset12 views-cell-preview views-cell-preview-more">
					<?php echo $cell_message ?>
				</div>
			</div>
		</div>
		<?php
	else:
		$view_count = $count_view_output;
		?>
		<?php _e('View name', 'ddl-layouts'); ?>: <?php echo $post_title; ?><br>
		<?php _e('Layout Style', 'ddl-layouts'); ?>: <?php echo isset($layout_style[$meta['style']])?$layout_style[$meta['style']]:'Undefined'; ?><br>
		<?php if ( $meta['style'] == 'bootstrap-grid' ) : ?>
			<?php _e('Columns', 'ddl-layouts'); ?>: <?php echo $meta['bootstrap_grid_cols']; ?><br>
		<?php endif; ?>
		<?php if ( $meta['style'] == 'table' ): ?>
			<?php _e('Columns', 'ddl-layouts'); ?> <?php echo $meta['table_cols']; ?><br>
		<?php endif; ?>
		<?php _e('Items to display', 'ddl-layouts'); ?>: <?php echo $view_count; ?><br>
		<?php
	endif;
}

function ddl_view_content_grid_get_title( $view_post ){
	$cell_content = '';
	if ( isset($view_post->post_title) ){
		$cell_content = $view_post->post_title;
	}
	if ( isset($view_post->name) ){
		$cell_content = $view_post->name;
	}
	if ( isset($view_post->user_login) ){
		$cell_content = $view_post->user_login;
	}
	return $cell_content;
}
