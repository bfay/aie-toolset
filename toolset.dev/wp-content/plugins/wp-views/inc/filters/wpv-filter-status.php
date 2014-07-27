<?php

if(is_admin()){

	/**
	* Add the status filter to the list and to the popup select
	*/
	
	add_action('wpv_add_filter_list_item', 'wpv_add_filter_status_list_item', 1, 1);
	add_filter('wpv_filters_add_filter', 'wpv_filters_add_filter_status', 1,1);

	function wpv_filters_add_filter_status($filters) {
		$filters['post_status'] = array('name' => __('Post status', 'wpv-views'),
						'present' => 'post_status',
						'callback' => 'wpv_add_new_filter_status_list_item'
						);

		return $filters;
	}
	
	/**
	* Create status filter callback
	*/

	function wpv_add_new_filter_status_list_item() {
		$args = array(
			'post_status' => array()
		);
		wpv_add_filter_status_list_item($args);
	}
	
	/**
	* Render status filter item in the filters list
	*/

	function wpv_add_filter_status_list_item($view_settings) {
		if (isset($view_settings['post_status'])) {
			$li = wpv_get_list_item_ui_post_status($view_settings['post_status']);
			echo '<li id="js-row-post_status" class="js-filter-row js-filter-row-simple js-filter-for-posts js-filter-status js-filter-row-post_status">' . $li . '</li>';
		}
	}
	
	/**
	* Render status filter item content in the filters list
	*/

	function wpv_get_list_item_ui_post_status( $selected, $view_settings = null ) {

		if ( isset( $_POST['checkboxes'] ) ) {
		// From ajax.
			$selected = $_POST['checkboxes'];
		} elseif ( !is_array( $selected ) ) {
			$selected = array();
		}
		$passing = array( 'post_status' => $selected );
		ob_start();
		?>
		<p class='wpv-filter-status-summary js-wpv-filter-summary js-wpv-filter-status-summary'>
			<?php echo wpv_get_filter_status_summary_txt( $passing ); ?>
		</p>
		<p class='edit-filter js-wpv-filter-edit-controls'>
			<i class='button-secondary icon-edit icon-large js-wpv-filter-edit-open js-wpv-filter-status-edit-open' title='<?php echo esc_attr( __('Edit','wpv-views') ); ?>'></i>
			<i class='button-secondary icon-trash icon-large js-filter-remove' title='<?php echo esc_attr( __('Delete this filter','wpv-views') ); ?>' data-nonce='<?php echo wp_create_nonce( 'wpv_view_filter_status_delete_nonce' ); ?>'></i>
		</p>
		<div id="wpv-filter-status-edit" class="wpv-filter-edit js-wpv-filter-edit">
			<fieldset>
				<p><strong><?php echo __('Post Status', 'wpv-views'); ?>:</strong></p>
				<div id="wpv-filter-status" class="js-filter-status-list">
					<?php echo wpv_render_status_checkboxes(
						array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash', 'any' ),
						$selected,
						'post_status'
					); ?>
				</div>
			</fieldset>
			<p>
				<input class="button-secondary js-wpv-filter-edit-ok js-wpv-filter-status-edit-ok" type="button" value="<?php echo htmlentities( __('Close', 'wpv-views'), ENT_QUOTES ); ?>" data-save="<?php echo htmlentities( __('Save', 'wpv-views'), ENT_QUOTES ); ?>" data-close="<?php echo htmlentities( __('Close', 'wpv-views'), ENT_QUOTES ); ?>" data-success="<?php echo htmlentities( __('Updated', 'wpv-views'), ENT_QUOTES ); ?>" data-unsaved="<?php echo htmlentities( __('Not saved', 'wpv-views'), ENT_QUOTES ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_status_nonce' ); ?>" />
			</p>
		</div>
		<?php
		$res = ob_get_clean();
		return $res;
		
	}
	
	/**
	* Update status filter callback
	*/

	add_action('wp_ajax_wpv_filter_status_update', 'wpv_filter_status_update_callback');

	function wpv_filter_status_update_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_status_nonce') ) die("Security check");
		$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
		if ( empty( $_POST['filter_status'] ) ) {
			if ( isset( $view_array['post_status'] ) ) {
				unset ($view_array['post_status']);
				update_post_meta($_POST["id"], '_wpv_settings', $view_array);
			}
		} else {
			parse_str($_POST['filter_status'], $filter_status);
			if ( !isset( $view_array['post_status'] ) || $view_array['post_status'] != $filter_status['post_status'] ) {
				$view_array['post_status'] = $filter_status['post_status'];
				$result = update_post_meta($_POST["id"], '_wpv_settings', $view_array);
			}
		}
		if ( !isset($filter_status['post_status']) ) {
			$filter_status['post_status'] = array();
		}
		echo wpv_get_filter_status_summary_txt($filter_status);
		die();
	}
	
	/**
	* Update status filter summary callback
	*/

	// TODO This might not be needed here, maybe for summary filter
	add_action('wp_ajax_wpv_filter_status_sumary_update', 'wpv_filter_status_sumary_update_callback');

	function wpv_filter_status_sumary_update_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_status_nonce') ) die("Security check");
		parse_str($_POST['filter_status'], $filter_status);
		if ( !isset($filter_status['post_status']) ) {
			$filter_status['post_status'] = array();
		}
		echo wpv_get_filter_status_summary_txt($filter_status);
		die();

	}
	
	/**
	* Delete status filter callback
	*/

	add_action('wp_ajax_wpv_filter_post_status_delete', 'wpv_filter_status_delete_callback');

	function wpv_filter_status_delete_callback() {
		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce($nonce, 'wpv_view_filter_status_delete_nonce') ) die("Security check");
		$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
		if ( isset( $view_array['post_status'] ) ) {
			unset( $view_array['post_status'] );
		}
		update_post_meta($_POST["id"], '_wpv_settings', $view_array);
		echo $_POST['id'];
		die();

	}
	
	/**
	* Add a filter to show the filter on the summary
	*/
    
	add_filter('wpv-view-get-summary', 'wpv_status_summary_filter', 5, 3);

	function wpv_status_summary_filter($summary, $post_id, $view_settings) {
		if(isset($view_settings['query_type']) && $view_settings['query_type'][0] == 'posts' && isset($view_settings['post_status'])) {			
			$result = wpv_get_filter_status_summary_txt($view_settings, true);
			if ($result != '' && $summary != '') {
				$summary .= '<br />';
			}
			$summary .= $result;
		}
		
		return $summary;
	}
    
}

/**
* Render status filter options
*/

function wpv_render_status_checkboxes($values, $selected, $name) {
	$checkboxes = '<ul>';
	foreach($values as $value) {

		if (in_array($value, $selected)) {
			$checked = ' checked="checked"';
		} else {
			$checked = '';
		}
		$checkboxes .= '<li><label><input type="checkbox" name="' . $name . '[]" value="' . $value . '"' . $checked . ' />&nbsp;' . $value . '</label></li>';

	}
	$checkboxes .= '</ul>';

	return $checkboxes;
}