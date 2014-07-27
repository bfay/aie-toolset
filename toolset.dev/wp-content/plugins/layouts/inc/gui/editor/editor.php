<?php

class WPDD_GUI_EDITOR{


	private $layout_id = null;
    const AMOUNT_OF_POSTS_TO_SHOW = 5;

	function __construct(){

		$this->layout_id = isset( $_GET['layout_id'] ) ? $_GET['layout_id'] : null;

		if (isset($_GET['page']) and $_GET['page']=='dd_layouts_edit') {

			add_action('wpddl_pre_render_editor', array($this,'pre_render_editor'), 10, 1);
			add_action('wpddl_render_editor', array($this,'render_editor'), 10, 1);
			add_action('wpddl_after_render_editor', array($this,'after_render_editor'), 10, 1);


			//add_action('wpddl_after_render_editor', array($this,'print_where_used_links'), 11, 1);
            add_action('wpddl_after_render_editor', array($this,'add_empty_where_used_ui'), 11, 1);
			add_action('wpddl_after_render_editor', array($this,'add_video_toolbar'), 11, 1);




			add_action('wpddl_layout_actions', array($this,'layout_actions'));

			add_action('admin_enqueue_scripts', array($this, 'preload_styles'));
			add_action('admin_enqueue_scripts', array($this, 'preload_scripts'));

			//add_action('admin_enqueue_scripts', array($this, 'load_latest_backbone'), -1000);

			do_action('wpddl_layout_actions');
		}

		//leave wp_ajax out of the **** otherwise it won't be fired
		add_action('wp_ajax_get_layout_data', array($this, 'get_layout_data_callback') );
		add_action('wp_ajax_save_layout_data', array($this, 'save_layout_data_callback') );
		add_action('wp_ajax_get_layout_parents', array($this, 'get_layout_parents_callback') );
		add_action('wp_ajax_check_for_parents_loop', array($this, 'check_for_parents_loop_callback') );
		add_action('wp_ajax_check_for_parent_child_layout_width', array($this, 'check_for_parent_child_layout_width_callback') );
        add_action('wp_ajax_view_layout_from_editor', array($this, 'view_layout_from_editor_callback') );
        add_action('wp_ajax_show_all_posts', array($this, 'show_all_posts_callback') );

        add_action('wp_ajax_ddl_get_where_used_ui', array($this, 'get_where_used_ui_callback') );
	}

	function __destruct(){
	}
	
	public function add_empty_where_used_ui() {
        ob_start();
        $this->add_select_post_types();
        $output = ob_get_clean();
		?>
		
		<div class="where-used-ui js-where-used-ui">
			<?php echo $output;?>
		</div>
		
		<?php 
		
	}

	public function get_where_used_ui_callback() {
		
		if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
							'ddl_layout_view_nonce')) {
				die('verification failed');
        }
		
		echo $this->get_where_used_output( $_POST['layout_id'] );
		die();
	}

    function get_where_used_output( $layout_id )
    {
        ob_start();

        $this->layout_id = $layout_id;
        $this->add_select_post_types();

        $output = ob_get_clean();

        return $output;
    }
	
	public function get_layout_data_callback()
	{
		echo get_post_meta( $_POST['layout_id'], 'dd_layouts_settings', true );
		die(  );
	}
	private function slug_exists( $slug, $layout_id )
	{
		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_name=%s AND ID != %d", $slug, $layout_id) );

		if ( !empty( $id ) ) return true;

		return false;
	}
	public function save_layout_data_callback()
	{
		global $wpddlayout;

		if( $_POST && wp_verify_nonce( $_POST['save_layout_nonce'], 'save_layout_nonce' ) )
		{
				if( $_POST['layout_model'] && $_POST['layout_id'] )
				{
					$raw = stripslashes( $_POST['layout_model'] );
					$json = json_decode( $raw, true );
					$children_to_delete = $json['children_to_delete'];
					$child_delete_mode = $json['child_delete_mode'];
					// we don't want to save these to the db
					//TODO:this is not affecting data saved in DB
					unset($json['children_to_delete']); 
					unset($json['child_delete_mode']);

					$post = get_post( $_POST['layout_id'] );

                    $msg = array( "Data" => array() );

					if( $post->post_title != $json['name'] || $post->post_name != $json['slug'] )
					{

						 if( $this->slug_exists( $json['slug'], $_POST['layout_id'] ) )
						{
							echo json_encode(array( "Data" => array( 'error' =>  __( sprintf('The layout %s cannot be saved, the post name  %s is already taken. Please try with a different name.', $json['name'], $json['slug'] ), 'wpv-views') ) ) );

							die();
						}
						else
						{
							$postarr = array(
									'ID' => $_POST['layout_id'],
									'post_title' => $json['name'],
									'post_name' => $json['slug']
								);

							$updated_id = wp_update_post($postarr);
                            $updated_post = get_post( $updated_id );

                            $json['slug'] = $updated_post->post_name;

							if( $this->normalize_layout_slug_if_changed( $_POST['layout_id'],  $json, $post->post_name ) )
                            {
                                $msg['Data']['slug'] = urldecode( $updated_post->post_name );
                            }

						}

					}
					if ( $raw === WPDD_Layouts::get_layout_settings( $_POST['layout_id'] ) ) {
						// no need to save as it hasn't changed.
						$up = false;
					} else {
						$layout_previous_settings = get_post_meta($_POST['layout_id'], 'dd_layouts_settings', true);
						$up = update_post_meta($_POST['layout_id'], 'dd_layouts_settings', addslashes( json_encode( $json ) ), $layout_previous_settings );
					}


					// I commented out !empty( $_POST['layout_css'] ) to allow users to erase css entirely
					if( isset( $_POST['layout_css'] ) /*&& !empty( $_POST['layout_css'] )*/ )
					{
						$msg['Data']['css_saved'] = $this->handle_layout_css( stripslashes($_POST['layout_css']) );
					}

					if( $children_to_delete && !empty($children_to_delete) )
					{
						$delete_children = $this->purge_layout_children( $children_to_delete, $child_delete_mode );
						if( $delete_children ) $msg['Data']['layout_children_deleted'] = $delete_children;
					}

					$msg['message']['layout_changed'] = $up;

					$send = json_encode( $msg );
				}
		}
		else
		{
			$send = json_encode(array( "Data" => array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) ) );
		}
		echo $send;
		die();
	}

	private function handle_archives_data_save($archives, $layout_id)
	{
		global $wpddlayout;
		$check = $wpddlayout->layout_post_loop_cell_manager->get_layout_loops( $layout_id );

		if( $archives !== $check )
		{
			$wpddlayout->layout_post_loop_cell_manager->handle_archives_data_save( $archives, $layout_id );
		}
	}

	private function normalize_layout_slug_if_changed( $layout_id, $layout_data, $previous_slug)
	{

			$current = (object) $layout_data;

			if( $current->slug === $previous_slug ) return false;

			$this->normalize_posts_where_used_data_on_slug_change( $current->slug, $previous_slug );

			if( property_exists($current, 'has_child') && $current->has_child === true )
			{
				$this->normalize_children_on_slug_change( $current, $current->slug, $previous_slug );
			}

          return true;
	}

	private function normalize_posts_where_used_data_on_slug_change( $slug, $previous_slug )
	{
		global $wpdb;

		$sql = $wpdb->prepare("UPDATE $wpdb->postmeta SET meta_value = %s WHERE meta_key = %s AND meta_value = %s", $slug, WPDD_Layouts_PostTypesManager::META_KEY, $previous_slug  );

		$wpdb->query( $sql );
	}

	private function normalize_children_on_slug_change( $layout, $slug, $previous_slug )
	{
		global $wpddlayout;

		$defaults = array(
			'numberposts' => -1,
			'post_type' => 'dd_layouts',
			'suppress_filters' => true,
			'post_status' => 'publish',
			'posts_per_page' => -1
		);

		$query = new WP_Query($defaults);

		$list = $query->get_posts();

		$children = $wpddlayout->listing_page->get_children( $layout, $list, $previous_slug);

		if( !is_array($children) || sizeof($children) === 0 ) return;

		if( is_array($children) && sizeof($children) > 0 )
		{
			foreach( $children as $child )
			{
				$current = WPDD_Layouts::get_layout_settings( $child, true );
				$current->parent = $slug;
				WPDD_Layouts::save_layout_settings( $child, $current );
			}
		}
	}

	private function purge_layout_children( $children, $action )
	{
		global $wpddlayout;
		
		if( !is_array( $children ) ) return false;

		$ret = array();

		foreach( $children as $child )
		{
			$id = intval($child);
			$layout = WPDD_Layouts::get_layout_settings($id, true);
			$layout->parent = '';
			WPDD_Layouts::save_layout_settings( $id, $layout );

			if( $action === 'delete' ) {
				// We also need to delete grandchildren
				$layout = $wpddlayout->get_layout_from_id($id);
				$grand_children = $layout->get_children();
				$this->purge_layout_children($grand_children, $action);
				$wpddlayout->post_types_manager->purge_layout_post_type_data( $id );
				$ret[] = wp_trash_post( $id );
			}
		}

		return true;
	}

	private function handle_layout_css( $css )
	{
		global $wpddlayout;
		return $wpddlayout->css_manager->handle_layout_css_save( $css );
	}

	private function handle_post_type_data_save( $post_types, $layout_id )
	{
		global $wpddlayout;

		$save = $post_types['layout_'.$layout_id];
		$check = $wpddlayout->post_types_manager->get_layout_post_types( $layout_id );

		if( $save === $check || $post_types === null || !$post_types )
		{
			return false;
		}

		 return $wpddlayout->post_types_manager->handle_post_type_data_save( $post_types );
	}

	public function get_layout_parents_callback() {
		global $wpddlayout;

		$parents = array();

		$layout = $wpddlayout->get_layout( $_POST['layout_name'] );

		if ($layout) {
			$parent_layout = $layout->get_parent_layout();


			while ($parent_layout) {
				$parents[] = $parent_layout->get_post_slug();

				$parent_layout = $parent_layout->get_parent_layout();
			}
		}

		echo json_encode($parents);

		die();
	}

	public function check_for_parents_loop_callback () {
		global $wpddlayout;

		$loop_found = false;

		$layout = $wpddlayout->get_layout( $_POST['new_parent_layout_name'] );

		if ($layout) {
			$parent_layout = $layout->get_parent_layout();

			while ($parent_layout) {
				if ($_POST['layout_name'] == $parent_layout->get_name()) {
					$loop_found = true;
					break;
				}

				$parent_layout = $parent_layout->get_parent_layout();
			}
		}

		if ($loop_found) {
			echo json_encode(array('error' => sprintf(__("You can't use %s as a parent layout as it or one of its parents has the current layout as a parent.", 'ddl-layouts'), '<strong>' . $_POST['new_parent_layout_name'] . '</strong>') ) );
		} else {
			echo json_encode(array('error' => ''));
		}

		die();

	}

	public function check_for_parent_child_layout_width_callback () {
		global $wpddlayout;

		$layout = $wpddlayout->get_layout( $_POST['parent_layout_name'] );

		$result = json_encode(array('error' => ''));

		if ($layout) {
			$child_layout_width = $layout->get_width_of_child_layout_cell();

			if ($child_layout_width != $_POST['width']) {
				$result = json_encode(array('error' => sprintf(__("This layout width is %d and the child layout width in %s is %d. This layout may not display correctly.", 'ddl-layouts'), $_POST['width'], '<strong>' . $_POST['parent_layout_title'] . '</strong>', $child_layout_width) ) );
			}
		}

		echo $result;

		die();
	}

	function preload_styles(){
		global $wpddlayout;

		$wpddlayout->enqueue_styles(
			array(
				'progress-bar-css' ,
				'toolset-font-awesome',
				'toolset-utils',
				'jq-snippet-css',
				'jquery-ui',
				'wp-editor-layouts-css',
				'toolset-colorbox',
				'toolset-common',
				'ddl-dialogs-css',
				'wp-pointer' ,
				'toolset-select2-css',
				'layouts-select2-overrides-css',
				'wp-mediaelement',
			)
		);

		$wpddlayout->enqueue_cell_styles();
	}

	function preload_scripts(){
		global $wpddlayout;

		$wpddlayout->enqueue_scripts(
			array(
				'jquery-ui-cell-sortable',
				'jquery-ui-custom-sortable',
				'jquery-ui-resizable',
				'jquery-ui-tabs',
				'wp-pointer',
				'backbone',
				'select2',
				'toolset-utils',
				'wp-pointer',
				'wp-mediaelement',
				'ddl-sanitize-html',
				'ddl-sanitize-helper',
				'ddl-post-types',
				//'ddl-individual-assignment-manager',
				'ddl-editor-main',
				'media_uploader_js',
				//'ddl-post-type-options-script'
			)
		);

		$wpddlayout->localize_script('ddl-editor-main', 'DDLayout_settings', array(
			'DDL_JS' => array(
				'res_path' => WPDDL_RES_RELPATH,
				'lib_path' => WPDDL_RES_RELPATH . '/js/external_libraries/',
				'editor_lib_path' => WPDDL_GUI_RELPATH."editor/js/",
				'dialogs_lib_path' => WPDDL_GUI_RELPATH."dialogs/js/",
				'layout_id' => $this->layout_id,
				'create_layout_nonce' => wp_create_nonce('create_layout_nonce'),
				'save_layout_nonce' => wp_create_nonce('save_layout_nonce'),
                'ddl-view-layout-nonce' => wp_create_nonce('ddl-view-layout-nonce'),
                'ddl_show_all_posts_nonce' => wp_create_nonce('ddl_show_all_posts_nonce'),
				'DEBUG' => WPDDL_DEBUG,
				'strings' => $this->get_editor_js_strings(),
				'has_theme_sections' => $wpddlayout->has_theme_sections(),
                'AMOUNT_OF_POSTS_TO_SHOW' => self::AMOUNT_OF_POSTS_TO_SHOW,
				'is_css_enabled' => $wpddlayout->css_manager->is_css_possible()
				, 'current_framework' => $wpddlayout->frameworks_options_manager->get_current_framework()
                , 'ARCHIVES_OPTION' => WPDD_layout_post_loop_cell_manager::POST_TYPES_LOOPS_NAME
                , 'POST_TYPES_OPTION' => WPDD_Layouts_PostTypesManager::POST_TYPES_OPTION_NAME
                , 'OTHERS_OPTION' => WPDD_layout_post_loop_cell_manager::WORDPRESS_OTHERS_SECTION
                , 'INDIVIDUAL_POSTS_OPTION' => WPDD_Layouts_IndividualAssignmentManager::INDIVIDUAL_POST_ASSIGN_CHECKBOXES_NAME
				)
			)
		);

		$wpddlayout->enqueue_cell_scripts();

	}

	function load_latest_backbone() {
		// load our own version of backbone for the editor.
		wp_dequeue_script('backbone');
		wp_deregister_script('backbone');
		wp_register_script('backbone', WPDDL_RES_RELPATH . '/js/external_libraries/backbone-min.js', array('underscore','jquery'), '1.1.0');
		wp_enqueue_script('backbone');

	}

	function pre_render_editor($inline) { ?>

		<div class="wrap" id="js-dd-layout-editor">

			<?php

			global $post;
			$post = $post ? $post : get_post( $this->layout_id );

			if (!$inline) {
				include_once 'templates/editor_header_box.tpl.php';
			}

	}

	function render_editor($inline){
		include WPDDL_GUI_ABSPATH . 'create_new_layout.php';
		include_once 'templates/editor_box.tpl.php';
		ddl_render_editor($inline);
	}

	function after_render_editor() {

		?>
		</div> <!-- .wrap -->

	<?php
	}

	function layout_actions(){
		if(isset($_REQUEST['action'])){
			switch ($_REQUEST['action']) {
				case 'trash':
					$this->delete_layout($_REQUEST['post']);
					break;
				default:
					break;
			}
		}
	}

	function delete_layout($layout_id){
		$post_id = $layout_id;
		wp_delete_post($post_id, true);
		delete_post_meta($post_id, 'dd_layouts_settings');
		delete_post_meta($post_id, 'dd_layouts_header');
		delete_post_meta($post_id, 'dd_layouts_styles');
		$url = home_url( 'wp-admin').'/admin.php?page=dd_layouts';
		header("Location: $url", true, 302);
		die();
	}

	public static function load_js_templates( $tpls_dir )
	{
		global $wpddlayout;

		WPDD_FileManager::include_files_from_dir( dirname(__FILE__), $tpls_dir );

		echo apply_filters("ddl_print_cells_templates_in_editor_page", $wpddlayout->get_cell_templates() );
	}

	function get_editor_js_strings () {
		return array(
			'only_one_cell' => __('Only one cell of this type is allowed per layout.', 'ddl-layouts'),
			'save_required' => __('This layout has changed', 'ddl-layouts'),
			'page_leave_warning' => __('This layout are changed. Are you sure you want to leave this page?', 'ddl-layouts'),
			'save_before_edit_parent' => __('Do you want to save the current layout before editing the parent layout?', 'ddl-layouts'),
			'save_required_edit_child' => __('Switching to the child layout', 'ddl-layouts'),
			'save_before_edit_child' => __('Do you want to save the current layout before editing the child layout?', 'ddl-layouts'),
			'save_layout_yes' => __('Save layout', 'ddl-layouts'),
			'save_layout_no' => __('Discard changes', 'ddl-layouts'),
			'save_required_new_child' => __('Creating a new child layout', 'ddl-layouts'),
			'save_before_creating_new_child' => __('Do you want to save the current layout before creating a new child layout?', 'ddl-layouts'),
			'no_parent' => __('No parent set', 'ddl-layouts'),
			'content_template' => __('Content Template', 'ddl-layouts'),
			'save_complete' => __('The layout has been saved.', 'ddl-layouts'),
			'one_column' => __('1 Column', 'ddl-layouts'),
			'columns' => __('Columns', 'ddl-layouts'),
			'at_least_class_or_id' => __('You should define either an ID or one class for this cell to style its CSS', 'ddl-layouts'),
			'select_range_one_column' => __('1 column', 'ddl-layouts'),
			'select_range_more_columns' => __('%d columns', 'ddl-layouts'),
			'dialog_yes' => __('Yes', 'ddl-layouts'),
			'dialog_no' => __('No', 'ddl-layouts'),
			'dialog_cancel' => __('Cancel', 'ddl-layouts'),
			'slug_unwanted_character' => __("The slug should contain only lower case letters", 'ddl-layouts' ),
			'save_and_also_save_css' => __('The layout has been saved. Layouts CSS has been updated.', 'ddl-layouts'),
			'save_and_save_css_problem' => __('The layout has been saved. Layouts CSS has NOT been updated. Check credentials for the file at ', 'ddl-layouts'),
			'invalid_slug' => __("The slug should contain only lower case letters and should not be an empty string.",'ddl-layouts'),
			'title_not_empty_string' => __("The title shouldn't be an empty string.", 'ddl-layouts'),
			'more_than_4_rows' => __('If you need more than 4 rows you can add them later in the editor', 'ddl-layouts'),
			'id_duplicate' => __("This id is already used in the current layout, please select a unique id for this element", 'ddl-layouts'),
			'edit_cell' => __('Edit cell', 'ddl-layouts'),
			'remove_cell' => __('Remove cell', 'ddl-layouts'),
			'set_cell_type' => __('Select cell type', 'ddl-layouts'),
			'show_grid_edit' => __('Show grid edit', 'ddl-layouts'),
			'hide_grid_edit' => __('Hide grid edit', 'ddl-layouts'),
			'css_file_loading_problem' => __('It is not possible to handle CSS loading in the front end. You should either make your uploads directory writable by the server, or use permalinks.', 'ddl-layouts'),
			'save_required_open_view' => __('Switching to the View', 'ddl-layouts'),
			'save_before_open_view' => __('The layout has changed. Do you want to save the current layout before switching to the View?', 'ddl-layouts'),
			'close_view_iframe' => __('Close this View and return to the Layout', 'ddl-layouts'),
			'save_and_close_view_iframe' => __('Save and Close this View and return to the Layout', 'ddl-layouts'),
			'close_view_iframe_without_save' => __('Close this View and discard the changes', 'ddl-layouts'),
            'video_message_text' => __( 'The YouTube video URL is missing', 'ddl-layouts' )
		);
	}

	public static function print_layouts_css()
	{
		global $wpddlayout;
		echo $wpddlayout->get_layout_css();
	}

	public function add_where_used_links( $layout_id = false, $all = false ) {
		
		return '';

		global $wpddlayout;

        $get = $layout_id ? $layout_id :$_GET['layout_id'];

        $post_types = $wpddlayout->post_types_manager->get_post_types_from_wp();

        $items = $this->get_where_used_x_amount_of_posts( $get, $all );

        $posts = $items->posts;

        ob_start();

		include_once WPDDL_GUI_ABSPATH.'editor/templates/list-layouts-where_used.box.tpl.php';

        return ob_get_clean();
	}

    public function show_all_posts_callback()
    {
        if( $_POST && wp_verify_nonce( $_POST['ddl_show_all_posts_nonce'], 'ddl_show_all_posts_nonce' ) )
        {
            $amount = $_POST['amount'] == 'all' ? true : false;
            $send = json_encode( array( 'Data' => array( 'where_used_html' => $this->add_where_used_links( $_POST['layout_id'], $amount ) ) ) );
        }
        else
        {
            $send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
        }

        die($send);
    }

    public function print_where_used_links()
    {
        echo '<div id="js-print_where_used_links dd-layouts-wrap">' . $this->add_where_used_links() . '</div>';
    }

    public function get_where_used_x_amount_of_posts( $layout_id, $all = false, $amount = self::AMOUNT_OF_POSTS_TO_SHOW )
    {
        global $wpddlayout;

        $ret = new stdClass();
        $ret->posts = array();
        $temp = array();

        $posts = $wpddlayout->get_where_used( $layout_id, false, true );

        $ret->found_posts = count( $posts );
        $ret->shown_posts = 0;

        if( $all === true ) $amount = count( $posts );

        foreach( $posts as $post )
        {
            if( !isset($temp[$post->post_type]) )
            {
                $temp[$post->post_type] = array();
            }

            $len = count( $temp[$post->post_type] );

            if( $len < $amount )
            {
                $item = new stdClass();
                $item->post_title = $post->post_title;
                $item->ID = $post->ID;
                $item->post_name = $post->post_name;
                $item->post_type = $post->post_type;
                $item->edit_link = get_edit_post_link( $post->ID);
                $item->permalink = get_permalink( $post->ID );
                $ret->posts[] = $item;
                $ret->shown_posts++;
            }

            $temp[$post->post_type][] = $post->ID;
        }

        $keys = array_keys($temp);

        foreach( $keys as $key )
        {
            $ret->{$key} = count($temp[$key]);
        }


        return $ret;
    }

    public function view_layout_from_editor_callback( )
    {
        global $wpddlayout;

        if( $_POST && wp_verify_nonce( $_POST['ddl-view-layout-nonce'], 'ddl-view-layout-nonce' ) )
        {
			
			$layout = WPDD_Layouts::get_layout_settings($_POST['layout_id'], true);
			if ($layout && isset($layout->has_child) && ($layout->has_child === 'true' || $layout->has_child === true)) {
				$send = json_encode( array( 'message' =>  __( "This layout contains a child layout and can't be viewed directly.", 'ddl-layouts') .
															'<br />'.
															__( "You'll need to switch to one of the child layouts and view it.", 'ddl-layouts')
															) );
			} else {
			
				$items = $this->get_where_used_x_amount_of_posts( $_POST['layout_id'], false, 3 );
				$posts = $items->posts;
	
				$loops = $wpddlayout->layout_post_loop_cell_manager->get_layout_loops( $_POST['layout_id'] );
	
				if( count($posts) === 0 && count($loops) === 0 )
				{
					$send = json_encode( array( 'message' =>  __( sprintf("This layout is not assigned to any content. %sFirst, assign it to content and then you can view it on the site's front-end. %sYou can assign this layout to content at the bottom of the layout editor.", '<br>', '<br>' ), 'ddl-layouts') ) );
				}
				else
				{
					$items = array();
	
					foreach( $posts as $post )
					{
						$post_types = $wpddlayout->post_types_manager->get_post_types_from_wp();
						$label = $post_types[$post->post_type]->labels->singular_name;
						$labels = $post_types[$post->post_type]->labels->name;
						$items[] = array( 'href' => $post->permalink, 'title' => $post->post_title, 'type' => $label, 'types' => $labels  );
					}
	
					foreach( $loops as $loop )
					{
						$push = $wpddlayout->layout_post_loop_cell_manager->get_loop_display_object( $loop );
						if( null !== $push  )
						array_push( $items, $push );
					}
	
					$send = json_encode( array(
											'Data' =>  $items,
											'message' =>  __( sprintf("This layout is not assigned to any content. %sFirst, assign it to content and then you can view it on the site's front-end. %sYou can assign this layout to content at the bottom of the layout editor.", '<br>', '<br>' ), 'ddl-layouts')
										)
					);
	
				}
			}
        }
        else
        {
            $send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
        }

        die($send);

    }

	public function add_video_toolbar()
	{
		include_once WPDDL_GUI_ABSPATH.'editor/templates/tutorial-video-bar.box.tpl.php';
	}

    private function get_where_used_lists( $layout_id = null )
    {
        global $wpddlayout;

        $id = $layout_id ? $layout_id : $this->layout_id;

        $post_types = $wpddlayout->post_types_manager->get_layout_post_types_object( $id );

        $items = $this->get_where_used_x_amount_of_posts( $id, true );

        $posts = $items->posts;

        $loops = $wpddlayout->layout_post_loop_cell_manager->get_layout_loops( $id );

        if( (!$post_types || count( $post_types ) === 0) && count( $posts  ) === 0 && count( $loops ) === 0 )
        {
            return null;
        }

        $ret = new stdClass();

        if( count( $posts  ) > 0 )
        {
            $ret->posts = $posts;
        }

        if( $post_types && count( $post_types ) )
        {
            $ret->post_types = $post_types;
        }

        if( count( $loops ) > 0 )
        {
            $loops_display = array();

            foreach( $loops as $loop )
            {
                $push = $wpddlayout->layout_post_loop_cell_manager->get_loop_display_object( $loop );
                if( null !== $push  )
                    array_push( $loops_display, $push );

            }

            $ret->loops = $loops_display;
        }

        return $ret;
    }

    public function get_first_post_of_type( $post_type, $layout_id  )
    {

        global $wpddlayout;

        $layout = $wpddlayout->get_layout_from_id( $layout_id );

        $args = array(
            'posts_per_page' => 1,
            'post_type' => $post_type,
            'meta_query' => array (
                array (
                    'key' => '_layouts_template',
                    'value' => $layout->get_post_slug(),
                    'compare' => '=',
                )
            ) );

        $new_query = new WP_Query( $args );

        $posts = $new_query->get_posts();

        return count( $posts ) > 0 && isset( $posts[0] ) ? $posts[0] : null;
    }

    public function print_assign_to_layout_section( $layout_id = null )
    {
        global $wpddlayout;

        ob_start();

        $current = $layout_id ? $layout_id : $this->layout_id;

        $lists = $this->get_where_used_lists( $current );

        $title_display = $lists === null ? __('This layout is not used for any content.', 'ddl-layout' ) : __('This layout is used for:', 'ddl-layout' );

        include WPDDL_GUI_ABSPATH . 'editor/templates/layout-content-assignment.box.tpl.php';

        return  ob_get_clean();
    }

	public function add_select_post_types()
	{
		global $wpddlayout;

        $this->layout_id = $this->layout_id ? $this->layout_id : $_GET['layout_id'];

		$layout = WPDD_Layouts::get_layout_settings($this->layout_id, true);

		if( is_object($layout) && property_exists ( $layout , 'has_child' ) === false ) $layout->has_child = false;

		if( $layout->has_child === false ):
		?>
            <div class="dd-layouts-wrap">
                <div class="dd-layouts-where-used">
                    <?php echo $this->print_assign_to_layout_section(); ?>
                </div>
            </div><!-- .dd-layouts-wrap -->

            <div class="ddl-dialog hidden layout-content-assignment-dialog js-layout-content-assignment-dialog">
            <div class="js-selected-post-types-in-layout-div">

                <div class="ddl-dialog-header">
                    <h2 class="js-dialog-title"><?php _e('Assign to content', 'ddl-layout'); ?></h2>
                    <i class="icon-remove js-edit-dialog-close js-remove-video"></i>
                </div>

                <div class="ddl-dialog-content js-ddl-dialog-content">
                    <?php
                    $html = $wpddlayout->listing_page->print_dialog_checkboxes($this->layout_id, false, '', false);
                    echo $html;
                    ?>
                </div>


                <div class="ddl-dialog-footer js-dialog-footer">
                    <div class="dialog-change-use-messages" data-text="<?php echo WPDD_LayoutsListing::$OPTIONS_ALERT_TEXT; ?>"></div>
                    <input type="button" class="button js-edit-dialog-close close-change-use"
                           value="<?php _e('Close', 'ddl-layouts'); ?>">
                </div>
            </div>
                <?php wp_nonce_field('layout-set-change-post-types-nonce', 'layout-set-change-post-types-nonce'); ?>
                <?php wp_nonce_field('ddl_layout_view_nonce', 'ddl_layout_view_nonce'); ?>
            </div>
	<?php
		endif;
	}
}