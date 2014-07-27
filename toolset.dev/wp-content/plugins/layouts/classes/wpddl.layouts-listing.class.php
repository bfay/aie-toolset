<?php

class WPDD_LayoutsListing
{

	private $args = array();
	private $layouts_query = null;
	private $layouts_list = array();
	private $count_what = '';
	private $mod_url = array();
	private $s_param = null;
	private $column_active = '';
	private $column_sort_to = 'ASC';
	private $column_sort_now = 'ASC';
	private $column_sort_date_to = 'DESC';
	private $column_sort_date_now = 'DESC';

    public static $OPTIONS_ALERT_TEXT;

	public function __construct()
	{
        self::$OPTIONS_ALERT_TEXT = __('* There are unsaved changes', 'ddl-layouts');

		add_action('wp_ajax_duplicate_layout', array(&$this, 'duplicate_layout_callback'));
		add_action('wp_ajax_set_layout_status', array(&$this, 'set_layout_status_callback'));
		add_action('wp_ajax_delete_layout_record', array(&$this, 'delete_layout_record_callback'));
		add_action('wp_ajax_change_layout_usage_box', array(&$this, 'set_change_layout_usage_box') );

		add_action('wp_ajax_change_layout_usage_for_post_types_js', array(&$this, 'set_layouts_post_types_on_usage_change_js') );
        add_action('wp_ajax_change_layout_usage_for_archives_js', array(&$this, 'set_layouts_archives_on_usage_change_js') );
        add_action('wp_ajax_change_layout_usage_for_others_js', array(&$this, 'set_layouts_others_on_usage_change_js') );



		add_action('wp_ajax_get_ddl_listing_data', array(&$this, 'get_ddl_listing_data'));

		if (isset($_GET['page']) && $_GET['page'] == 'dd_layouts') {
			add_action('admin_enqueue_scripts', array($this, 'listing_scripts'));
		}
	}

	public function init()
	{
		$this->include_creation_box();
		$this->set_mod_url();
		$this->set_args();
		$this->set_count_what();
		$this->set_count();
		//$this->change_query_vars();
		//$this->set_search_query();
		//$this->set_sort();
		//$this->set_layout_list();
		$this->display_list();
	}

	public function set_change_layout_usage_box( )
	{

		global $wpddlayout;

		if( $_POST && wp_verify_nonce( $_POST['layout-select-set-change-nonce'], 'layout-select-set-change-nonce' ) )
		{
			$nonce = wp_create_nonce( 'layout-set-change-post-types-nonce' );

			$html = $this->print_dialog_checkboxes( $_POST['layout_id'], false, 'change' );
			$send = json_encode( array( 'message' => array( 'html_data' => $html, 'nonce' => $nonce, 'layout_id' => $_POST['layout_id'] ) ) );
		}
		else
		{
			$send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
		}

		die( $send );
	}

	public function print_dialog_checkboxes( $current = false, $do_not_show = false, $id = "", $show_ui = true )
	{
		global $wpddlayout;
		$html = '';
        $html .= $this->print_single_posts_assign_section();
		$html .= $wpddlayout->post_types_manager->print_post_types_checkboxes( $current, $do_not_show, $id, $show_ui );
		$html .= $wpddlayout->layout_post_loop_cell_manager->display_loops( $current, $id, $show_ui);
        $html .= $wpddlayout->layout_post_loop_cell_manager->display_others( $current, $id, $show_ui );
		return $html;
	}

    public function print_single_posts_assign_section()
    {
        ob_start();
        include WPDDL_GUI_ABSPATH . 'editor/templates/individual-posts.box.tpl.php';
        return ob_get_clean();
    }

	public function set_layouts_post_types_on_usage_change_js()
	{
		global $wpddlayout;

		if( $_POST && wp_verify_nonce( $_POST['layout-set-change-post-types-nonce'], 'layout-set-change-post-types-nonce' ) )
		{
			$post_types = isset( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ? array_unique( $_POST['post_types'] ) : array();


            $wpddlayout->post_types_manager->handle_post_type_data_save( array( "layout_".$_POST['layout_id'] => $post_types ) );


			$status = isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish';

			$send = $this->set_up_send_data( $status );

		}
		else
		{
			$send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
		}

		die($send);
	}

    public function set_layouts_archives_on_usage_change_js( )
    {
        global $wpddlayout;

        if( $_POST && wp_verify_nonce( $_POST['layout-set-change-post-types-nonce'], 'layout-set-change-post-types-nonce' ) )
        {

            $default_archives = isset( $_POST[WPDD_layout_post_loop_cell_manager::WORDPRESS_DEFAULT_LOOPS_NAME] ) ? $_POST[WPDD_layout_post_loop_cell_manager::WORDPRESS_DEFAULT_LOOPS_NAME] : array();
           /* $types_archives = isset( $_POST[WPDD_layout_post_loop_cell_manager::POST_TYPES_LOOPS_NAME] ) ? $_POST[WPDD_layout_post_loop_cell_manager::POST_TYPES_LOOPS_NAME] : array();
            $taxonomy_archives = isset( $_POST[WPDD_layout_post_loop_cell_manager::TAXONOMY_LOOPS_NAME] ) ? $_POST[WPDD_layout_post_loop_cell_manager::TAXONOMY_LOOPS_NAME] : array();*/

            //$wpddlayout->layout_post_loop_cell_manager->handle_archives_data_save( array_merge( $default_archives, $types_archives, $taxonomy_archives ), $_POST['layout_id'] );

            $wpddlayout->layout_post_loop_cell_manager->handle_archives_data_save( $default_archives, $_POST['layout_id'] );

            $status = isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish';

            $send = $this->set_up_send_data( $status );

        }
        else
        {
            $send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
        }

        die($send);
    }

    public function set_layouts_others_on_usage_change_js()
    {
        global $wpddlayout;

        if( $_POST && wp_verify_nonce( $_POST['layout-set-change-post-types-nonce'], 'layout-set-change-post-types-nonce' ) )
        {

            $others_section = isset( $_POST[WPDD_layout_post_loop_cell_manager::WORDPRESS_OTHERS_SECTION] ) ? $_POST[WPDD_layout_post_loop_cell_manager::WORDPRESS_OTHERS_SECTION] : array();

            $wpddlayout->layout_post_loop_cell_manager->handle_others_data_save( $others_section, $_POST['layout_id'] );

            $status = isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish';

            $send = $this->set_up_send_data( $status );

        }
        else
        {
            $send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
        }

        die($send);
    }

    public function set_up_send_data( $status )
    {
        $data = $this->get_grouped_layouts( $status );

        if( defined('JSON_UNESCAPED_UNICODE') ) {
            $send = json_encode(array('Data' => $data), JSON_UNESCAPED_UNICODE );
        } else {
            $send = json_encode(array('Data' => $data));
        }

        return $send;
    }

	public function get_ddl_listing_data()
	{
		global $wpddlayout;
		// Clear any errors that may have been rendered that we don't have control of.
		ob_clean();

		if ($_POST && wp_verify_nonce($_POST['ddl_listing_nonce'], 'ddl_listing_nonce')) {
			$data = $this->get_grouped_layouts($_POST['status']);
			if( defined('JSON_UNESCAPED_UNICODE') ) {
				$send = json_encode(array('Data' => $data), JSON_UNESCAPED_UNICODE );
			} else {
				$send = json_encode(array('Data' => $data));
			}
		} else {
			$send = $wpddlayout->json_encode(array('error' => __(sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__), 'ddl-layouts')));
		}

		die($send);
	}

	public function get_grouped_layouts($status)
	{
		global $wpddlayout;

		// property name is too long let's store it in a var for easier typing
		$loop_manager = $wpddlayout->layout_post_loop_cell_manager;

		$defaults = array(
			'numberposts' => -1,
			'post_type' => 'dd_layouts',
			'suppress_filters' => true,
			'order' => 'DESC',
			'orderby' => 'date',
			'post_status' => $status,
			'posts_per_page' => -1
		);

		$post_types = array();
        $post_types_temp = array();
		$to_single = array();
		$not_assigned = array();
		$to_loops = array();

		$query = new WP_Query($defaults);
        $this->layouts_query = $query;
		$list = $query->get_posts();
        $this->layouts_list = $list;
		$blacklist = array('post_parent', 'post_password', 'comment_count', 'comment_status', 'guid', 'menu_order', 'pinged', 'ping_status', 'post_author', 'post_content', 'post_content_filtered', 'post_date_gmt', 'post_excerpt', 'post_mime_type', 'post_modified', 'post_modified_gmt', 'to_ping');

		foreach ($list as $item) {
			$types = $wpddlayout->post_types_manager->get_layout_post_types_object( $item->ID );
			$posts = $wpddlayout->get_where_used($item->ID, $item->post_name);
			$layout = WPDD_Layouts::get_layout_settings($item->ID, true);
			$loops = $loop_manager->get_layout_loops_labels( $item->ID );

			if ($layout) {

				if( property_exists ( $layout , 'has_child' ) === false ) $layout->has_child = false;
			
				foreach( $blacklist as $remove )
				{
					unset( $item->{$remove} );
				}
	
				$item->kind = 'Item';
				$item->post_name = urldecode($item->post_name);
				$item->id = $item->ID;
				$item->is_parent = $layout->has_child;
				$item->date_formatted = get_the_time(get_option('date_format'), $item->ID);
				$item->post_title = str_replace('\\\"', '\"', $item->post_title);
				$item->has_loop = property_exists($layout, 'has_loop') ? $layout->has_loop : false;
				$item->has_post_content_cell = property_exists($layout, 'has_post_content_cell') ? $layout->has_post_content_cell : false;

				if( $item->is_parent )
				{
					$item->children = $this->get_children($layout, $list);
				}
	
				if ( property_exists( $layout, 'parent') && $layout->parent) {
					$parent = get_post(WPDD_Layouts::get_layout_parent($item->ID, $layout));
					$item->is_child = true;
					if (is_object( $parent ) && $parent->post_status == $item->post_status){
						$item->parent = $parent->ID;
					}
				}
				else
				{
					$item->is_child = false;
				}
	
	
				if ($types) {
					$item->types = $types;
					//$item->group = 3;
					$post_types[] = (array)$item;
                    $post_types_temp[] = $item->ID;
				}

				if( $loops )
				{
					$item->loops = $loops;
					$to_loops[] = (array)$item;
				}

				if ( $posts && count($posts) > 0 ) {
                    $item->posts = array();
					if ( in_array( $item->ID, $post_types_temp) ) {

						foreach ($posts as $post) {
								if( $this->check_post_is_in_post_types_array($post, $post_types ) === true && $this->get_post_type_was_batched( $item->ID, $post->post_type ) === true )
								{
									continue;
								}
								else
								{
                                     $item->posts[] = $this->_filter_post($post, $blacklist);
								}
						}
						if( sizeof($item->posts) > 0 ) {
							$to_single[] = (array) $item;
						}
					} else {

						foreach ($posts as $key=>$post) {

							    $post = $this->_filter_post( $post, $blacklist );
                                $item->posts[] = $post;

						}

                            $to_single[] = (array)$item;
					}
				} elseif (!$posts && !$types && !$loops ) {
					//$item->group = 1;
					$not_assigned[] = (array)$item;
				}
			}
		}

		return
			array(
				array(
					'id' => 1,
					'name' => __("Layouts not being used anywhere", 'ddl_layouts'),
					'kind' => 'Group',
					'items' => $not_assigned
				),
				array(
					'id' => 2,
					'name' => __('Layouts being used to display single posts or pages', 'ddl_layouts'),
					'kind' => 'Group',
					'items' => $to_single
				),
				array(
					'id' => 3,
					'name' => __('Layouts being used as templates for post types', 'ddl_layouts'),
					'kind' => 'Group',
					'items' => $post_types
				),
				array(
					'id' => 4,
					'name' => __('Layouts being used to customize archives', 'ddl_layouts'),
					'kind' => 'Group',
					'items' => $to_loops
				)
			);
	}
	private function check_post_is_in_post_types_array( $post, $post_types)
	{
		foreach( $post_types as $post_type )
		{
			if( array_key_exists('types', $post_type ) && count($post_type['types']) > 0 )
			{
				foreach( $post_type['types'] as $type )
				{
					if( $type['post_type'] == $post->post_type ) return true;
				}
			}
		}

		return false;
	}

	private function get_post_type_was_batched( $layout_id, $post_type )
	{
		global $wpddlayout;
		return $wpddlayout->post_types_manager->get_post_type_was_batched( $layout_id, $post_type );
	}

	private function _filter_post ( $post, $black = false ) {
        if( $black )
        {
            $blacklist = $black;
        }
        else
        {
            $blacklist = array('post_parent', 'post_password', 'comment_count', 'comment_status', 'guid', 'menu_order', 'pinged', 'ping_status', 'post_author', 'post_content', 'post_content_filtered', 'post_date_gmt', 'post_excerpt', 'post_mime_type', 'post_modified', 'post_modified_gmt', 'to_ping');
        }
		$post->post_name = urldecode($post->post_name);
		foreach( $blacklist as $remove )
		{
			unset( $post->{$remove} );
		}
		$edit_link = get_edit_post_link($post->ID);
		if ($edit_link) {
			$post->post_title = '<a href="' . $edit_link . '">' . $post->post_title . '</a>';
		}
		
		return $post;
	}
	
	public function get_children($layout, $layouts_list, $previous_slug = null)
	{
		global $wpddlayout;

		$ret = array();

		if( isset($layout->has_child) && ($layout->has_child === 'true' || $layout->has_child === true) )
		{

			$layout_slug = $previous_slug === null ? $layout->slug : $previous_slug;

			foreach ($layouts_list as $post) {
				$layout = $wpddlayout->get_layout_settings($post->ID, true);
				if ($layout) {
					if ( property_exists ( $layout , 'parent' ) && $layout->parent == $layout_slug) {
						$ret[] = $post->ID;
					}
				}
			}
			return $ret;
		}
		return $ret;
	}

    //TODO:this method is depracated and should be removed
	private function set_sort()
	{
		if ($this->args['orderby'] === 'title') {
			$this->column_active = ' views-list-sort-active';
			$this->column_sort_to = ($this->args['order'] === 'ASC') ? 'DESC' : 'ASC';
			$this->column_sort_now = $this->args['order'];
		}

		if ($this->args['orderby'] === 'date') {
			$this->column_active = ' views-list-sort-active';
			$this->column_sort_date_to = ($this->args['order'] === 'ASC') ? 'DESC' : 'ASC';
			$this->column_sort_date_now = $this->args['order'];
		}

	}

	public function listing_scripts()
	{
		global $wpddlayout;

		$wpddlayout->enqueue_scripts( array('dd-listing-page-main', 'ddl-post-types') );
		$wpddlayout->localize_script('dd-listing-page-main', 'DDLayout_settings', array(
			'DDL_JS' => array(
                'res_path' => WPDDL_RES_RELPATH,
				'listing_lib_path' => WPDDL_GUI_RELPATH . "/listing/js/",
				'editor_lib_path' => WPDDL_GUI_RELPATH."editor/js/",
				'ddl_listing_nonce' => wp_create_nonce('ddl_listing_nonce'),
				'ddl_listing_status' => isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish',
				'lib_path' => WPDDL_RES_RELPATH . '/js/external_libraries/',
				'strings' => $this->get_listing_js_strings(),
                'is_listing_page' => true
                , 'ARCHIVES_OPTION' => WPDD_layout_post_loop_cell_manager::POST_TYPES_LOOPS_NAME
                , 'POST_TYPES_OPTION' => WPDD_Layouts_PostTypesManager::POST_TYPES_OPTION_NAME
                , 'OTHERS_OPTION' => WPDD_layout_post_loop_cell_manager::WORDPRESS_OTHERS_SECTION
                , 'INDIVIDUAL_POSTS_OPTION' => WPDD_Layouts_IndividualAssignmentManager::INDIVIDUAL_POST_ASSIGN_CHECKBOXES_NAME
			),
			'items_per_page' => DDL_ITEMS_PER_PAGE
		));
		$wpddlayout->enqueue_styles(array('views-pagination-style', 'dd-listing-page-style'));
	}

	private function get_listing_js_strings()
	{
		return array(
			'is_a_parent_layout' => __("This layout has children. It can't be deleted.", 'ddl-layouts'),
			'is_a_parent_layout_and_cannot_be_changed' => __("This layout has children. It can't be assigned directly to posts or posts types.<br>You should assign one of its children instead.", 'ddl-layouts'),
			'to_a_post_type' => __("This layout is assigned to a post type. It can't be deleted.", 'ddl-layouts'),
            'to_an_archive' => __("This layout is assigned to an archive. It can't be deleted.", 'ddl-layouts'),
            'to_archives' => __("This layout is assigned to %s archives. It can't be deleted.", 'ddl-layouts'),
			'to_post_types' => __('This layout is assigned to %s post types. It can\'t be deleted.', 'ddl-layouts'),
			'to_a_post_item' => __('This layout is assigned to a post. It can\'t be deleted.', 'ddl-layouts'),
			'to_posts_items' => __("This layout is assigned to %s posts. It can't be deleted.", 'ddl-layouts')
		);
	}

    //TODO:this method is depracated and should be removed
	private function set_search_query()
	{
		if (isset($_GET["search"]) && '' != $_GET["search"]) { // perform the search in Views titles and decriptions and return an array to be used in post__in
			$this->s_param = urldecode(sanitize_text_field($_GET["search"]));
			$this->args['s'] = $this->s_param;
			$this->mod_url['search'] = '&amp;search=' . sanitize_text_field($_GET["search"]);
		}
	}

	private function set_args($args = array())
	{
		$defaults = array(
			'post_type' => 'dd_layouts',
			'suppress_filters' => true,
			'posts_per_page' => DDL_ITEMS_PER_PAGE,
			'order' => 'ASC',
			'orderby' => 'title',
			'post_status' => isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish',
			'paged' => isset($_GET['paged']) ? $_GET['paged'] : 1
		);

		$this->args = wp_parse_args($args, $defaults);
	}

	private function get_args()
	{
		return $this->args;
	}
    //TODO:this method is depracated and should be removed
	private function set_mod_url($args = array())
	{
		$mod_url = array( // array of URL modifiers
			'orderby' => '',
			'order' => '',
			'search' => '',
			'items_per_page' => '',
			'paged' => '',
			'status' => ''
		);
		$this->mod_url = wp_parse_args($args, $mod_url);
	}
    //TODO:this method is depracated and should be removed
	private function get_mod_url()
	{
		return $this->mod_url;
	}
    //TODO:this method is depracated and should be removed
	private function set_layout_list()
	{
		$this->layouts_query = new WP_Query( $this->get_args() );
		$this->layouts_list = $this->layouts_query->posts;
	}

	private function found_posts()
	{
		return is_object($this->layouts_query) ? $this->layouts_query->found_posts : 0;
	}

	private function post_count()
	{
		return is_object($this->layouts_query) ? $this->layouts_query->post_count : 0;
	}

	private function get_layout_list()
	{
		return $this->layouts_list;
	}

	private function set_count()
	{
		global $wpdb;

		$this->count_published = $wpdb->get_var("SELECT COUNT(ID) from $wpdb->posts WHERE post_type = 'dd_layouts' AND post_status = 'publish'");
		$this->count_trash = $wpdb->get_var("SELECT COUNT(ID) from $wpdb->posts WHERE post_type = 'dd_layouts' AND post_status = 'trash'");
	}

	private function get_arg($arg)
	{
		return isset($this->args[$arg]) ? $this->args[$arg] : null;
	}

	private function get_count_published()
	{
		return $this->count_published;
	}

	private function get_count_trash()
	{
		return $this->count_trash;
	}

	private function get_count_what()
	{
		return $this->count_what;
	}

	private function set_count_what()
	{
		$this->count_what = $this->get_arg('post_status') == 'publish' ? 'trash' : 'publish';
	}

    //TODO: this method is depracated and should be removed
	private function change_query_vars()
	{
		if (isset($_GET["items_per_page"]) && '' != $_GET["items_per_page"]) {
			$this->args['posts_per_page'] = (int)$_GET["items_per_page"];
			$this->mod_url['items_per_page'] = '&amp;items_per_page=' . (int)$_GET["items_per_page"];
		}

		if (isset($_GET["orderby"]) && '' != $_GET["orderby"]) {
			$this->args['orderby'] = sanitize_text_field($_GET["orderby"]);
			$this->mod_url['orderby'] = '&amp;orderby=' . sanitize_text_field($_GET["orderby"]);
			if (isset($_GET["order"]) && '' != $_GET["order"]) {
				$this->args['order'] = sanitize_text_field($_GET["order"]);
				$this->mod_url['order'] = '&amp;order=' . sanitize_text_field($_GET["order"]);
			}
		}

		if (isset($_GET["paged"]) && '' != $_GET["paged"]) {
			$this->args['paged'] = (int)$_GET["paged"];
			$this->mod_url['paged'] = '&amp;paged=' . (int)$_GET["paged"];
		}

		if (isset($_GET["status"]) && '' != $_GET["status"]) {
			$this->mod_url['status'] = '&amp;status=' . sanitize_text_field($_GET["status"]);
			$this->args['status'] = $_GET["status"];
		}
	}


    //TODO: this method is depracated and should be removed
	private function ddl_admin_listing_pagination($context = 'dd_layouts', $ddl_found_items, $ddl_items_per_page = DDL_ITEMS_PER_PAGE, $mod_url = array())
	{
		$page = (isset($_GET["paged"])) ? (int)$_GET["paged"] : 1;
		$pages_count = ceil((int)$ddl_found_items / (int)$ddl_items_per_page);

		if ($pages_count > 1) {
			$items_start = ((($page - 1) * (int)$ddl_items_per_page) + 1);
			$items_end = ((($page - 1) * (int)$ddl_items_per_page) + (int)$ddl_items_per_page);
			if ($page == $pages_count) {
				$items_end = $ddl_found_items;
			}
			$mod_url_defaults = array(
				'orderby' => '',
				'order' => '',
				'search' => '',
				'items_per_page' => '',
				'status' => ''
			);
			$mod_url = wp_parse_args($mod_url, $mod_url_defaults);

			include WPDDL_GUI_ABSPATH . '/templates/layouts_pagination.box.tpl.php';

		} else if (DDL_ITEMS_PER_PAGE != $ddl_items_per_page && $ddl_found_items > DDL_ITEMS_PER_PAGE && $ddl_found_items != $ddl_items_per_page) {
			include WPDDL_GUI_ABSPATH . '/templates/layouts_pagination_default_item.box.tpl.php';
		}
	}

	private function display_list()
	{
		global $wpddlayout;
		$status = isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish';
		$data = $this->get_grouped_layouts($status);
		if( defined('JSON_UNESCAPED_UNICODE') ) {
			$init_json_listing = json_encode(array('Data' => $data), JSON_UNESCAPED_UNICODE );
		} else {
			$init_json_listing = json_encode(array('Data' => $data));
		}
		include WPDDL_GUI_ABSPATH . 'templates/layouts_list_new.tpl.php';
		//include WPDDL_GUI_ABSPATH . 'templates/layouts_list.tpl.php';
		$this->load_js_templates();
	}

	private function load_js_templates()
	{
		WPDD_FileManager::include_files_from_dir(WPDDL_GUI_ABSPATH . "/listing/", "js/templates", $this );
	}

	private function include_creation_box()
	{
		include WPDDL_GUI_ABSPATH . 'create_new_layout.php';
	}

    //TODO: this method is depracated and should be removed
	public function print_layout_action_select($layout_id, $status)
	{
		$data = array(
			'layout_id' => $layout_id,
			'nonce' => wp_create_nonce('layout-select-set-change-nonce'),
			'trash_nonce' => wp_create_nonce('layout-select-trash-nonce'),
			'delete_nonce' => wp_create_nonce('layout-delete-layout-nonce'),
			'duplicate_nonce' => wp_create_nonce('layout-duplicate-layout-nonce')
		);

		?>
		<select name="select-layout-action-in-listing-page"
		        class="select-layout-action-in-listing-page js-select-layout-action-in-listing-page"
		        data-object="<?php echo htmlspecialchars(json_encode($data)); ?>">

			<option value=""><?php _e('Choose', 'ddl-layouts'); ?></option>
			<?php if ($status === 'publish'): ?>
				<option value="change"><?php _e('Change layout use', 'ddl-layouts'); ?></option>
				<option value="duplicate"><?php _e('Duplicate', 'ddl-layouts'); ?></option>
				<option value="trash"><?php _e('Move to trash', 'ddl-layouts'); ?></option>
			<?php elseif ($status === 'trash'): ?>
				<option value="publish"><?php _e('Restore', 'ddl-layouts'); ?></option>
				<option value="permanent"><?php _e('Delete permanently', 'ddl-layouts'); ?></option>
			<?php endif; ?>
		</select>
	<?php
	}
    //TODO: this method is depracated and should be removed
	public function print_edit_and_trash_links($layout_id)
	{
		$data = array(
			'layout_id' => $layout_id,
			'trash_nonce' => wp_create_nonce('layout-select-trash-nonce'),
			'delete_nonce' => wp_create_nonce('layout-delete-layout-nonce'),
			'value' => 'trash'
		);
		?>
		<span class="edit"><a href="admin.php?page=dd_layouts_edit&layout_id=<?php echo $layout_id; ?>&action=edit"
		                      title="<?php _e('Edit this layout', 'ddl-layouts'); ?>"><?php _e('Edit', 'ddl-layouts'); ?></a> | </span>
		<span class="restore"><a href="" class="js-layout-listing-restore-link"
		                         data-object="<?php echo htmlspecialchars(json_encode($data)); ?>"
		                         title="<?php _e('Trash', 'ddl-layouts'); ?>"><?php _e('Trash', 'ddl-layouts'); ?></a> | </span>
	<?php
	}
    //TODO: this method is depracated and should be removed
	public function print_delete_and_restore_links($layout_id)
	{
		$data = array(
			'layout_id' => $layout_id,
			'trash_nonce' => wp_create_nonce('layout-select-trash-nonce'),
			'delete_nonce' => wp_create_nonce('layout-delete-layout-nonce')
		);
		?>

		<span class="restore"><a href="" class="js-layout-listing-restore-link"
		                         data-object="<?php $data['value'] = 'publish';
		                         echo htmlspecialchars(json_encode($data)); ?>"
		                         title="<?php _e('Restore', 'ddl-layouts'); ?>"><?php _e('Restore', 'ddl-layouts'); ?></a> | </span>
		<span class="delete"><a href="" class="submitdelete js-layout-listing-delete-permanently-link"
		                        data-object="<?php echo htmlspecialchars(json_encode($data)); ?>"
		                        title="<?php _e('Delete permanently', 'ddl-layouts'); ?>"><?php _e('Delete permanently', 'ddl-layouts'); ?></a></span>

	<?php
	}

	public function duplicate_layout_callback()
	{

		// Clear any errors that may have been rendered that we don't have control of.
		ob_clean();

		if ($_POST && wp_verify_nonce($_POST['layout-duplicate-layout-nonce'], 'layout-duplicate-layout-nonce')) {
			global $wpdb, $wpddlayout;

			$result = $wpdb->get_row($wpdb->prepare("SELECT ID, post_name FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND ID=%d AND post_status = 'publish'", $_POST['layout_id']));
			if ($result) {
				$layout_json = $wpddlayout->get_layout_settings($result->ID);
				$layout_array = json_decode($layout_json, true);


				$layout_name_base = __('Copy of ', 'ddl_layouts') . str_replace('\\', '\\\\', $layout_array['name']);
				$layout_name = $layout_name_base;

				$count = 1;
				while ($wpddlayout->does_layout_with_this_name_exist($layout_name)) {
					$layout_name = $layout_name_base . ' - ' . $count;
					$count++;
				}

				$postarr = array(
					'post_title' => $layout_name,
					'post_content' => '',
					'post_status' => 'publish',
					'post_type' => 'dd_layouts'
				);
				$post_id = wp_insert_post($postarr);

				$post_slug = $wpdb->get_var($wpdb->prepare("SELECT post_name FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND ID=%d", $post_id));
				
 				$layout_array['name'] = $layout_name;
				$layout_array['slug'] = $post_slug;

				$layout_json = $wpddlayout->json_encode($layout_array);

				update_post_meta($post_id, 'dd_layouts_settings', $layout_json);

				$message = $post_id;

			}

			$data = $this->get_grouped_layouts(isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish');

			if( defined('JSON_UNESCAPED_UNICODE') ) {
				$send = json_encode(array('Data' => $data, 'added' => $message), JSON_UNESCAPED_UNICODE );
			} else {
				$send = json_encode(array('Data' => $data, 'added' => $message));
			}
		} else {
			$send = json_encode(array('error' => __(sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__), 'ddl-layouts')));
		}

		die($send);
	}

	public function set_layout_status_callback()
	{

		// Clear any errors that may have been rendered that we don't have control of.
		ob_clean();

		if ($_POST && wp_verify_nonce($_POST['layout-select-trash-nonce'], 'layout-select-trash-nonce')) {
			$http_id = $_POST['layout_id'];
			$status = $_POST['status'];
			$current_page_status = isset( $_POST['current_page_status'] ) ? $_POST['current_page_status'] : 'publish';

			if (is_array($http_id)) {
				$ids = $http_id;
			} else {
				$ids = array($http_id);
			}

			$message = array();

			foreach ($ids as $id) {
				$data = array(
					'ID' => $id,
					'post_status' => $status
				);

				$message[] = wp_update_post($data);
			}

			$data = $this->get_grouped_layouts($current_page_status);
			if( defined('JSON_UNESCAPED_UNICODE') ) {
				$send = json_encode(array('Data' => $data, 'message' => $message), JSON_UNESCAPED_UNICODE );
			} else {
				$send = json_encode(array('Data' => $data, 'message' => $message));
			}
			
		} else {
			$send = json_encode(array('error' => __(sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__), 'ddl-layouts')));
		}

		die($send);
	}

	public function delete_layout_record_callback()
	{

		// Clear any errors that may have been rendered that we don't have control of.
		ob_clean();

		global $wpddlayout;

		if ($_POST && wp_verify_nonce($_POST['layout-delete-layout-nonce'], 'layout-delete-layout-nonce')) {
			$layout_id = $_POST['layout_id'];
			$current_page_status = isset( $_POST['current_page_status'] ) ? $_POST['current_page_status'] : 'trash';

			if (!is_array($layout_id)) {
				$layout_id = array($layout_id);
			}

			$message = array();

			foreach ($layout_id as $id) {
				$res = wp_delete_post($id, true);
				// if deleted clean from options
				if ($res !== false) {
					$wpddlayout->post_types_manager->clean_layout_post_type_option($id);
					$message[] = $res->ID;
				}

			}

			$send = json_encode( array( 'Data' => $this->get_grouped_layouts($current_page_status), 'message' => $message));

		} else {
			$send = json_encode(array('error' => __(sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__), 'ddl-layouts')));
		}

		die($send);
	}

}