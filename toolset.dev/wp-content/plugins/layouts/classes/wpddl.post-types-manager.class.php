<?php
class WPDD_Layouts_PostTypesManager
{
	private $post_types;
	private $post_types_options;
	const DDL_POST_TYPES_OPTIONS = 'ddl_post_types_options';
	const DDL_POST_TYPES_WAS_BATCHED = '_ddl_post_types_was_batched';
	const META_KEY = '_layouts_template';
	const KEY_PREFIX = 'layout_';
    const POST_TYPES_OPTION_NAME = 'post_types';

	public function __construct()
	{
		$this->post_types_options = new WPDDL_Options_Manager( self::DDL_POST_TYPES_OPTIONS );

		add_action('admin_init', array(&$this,'init_admin'), 99 );

		add_action('wp_ajax_set_layout_for_post_type_meta', array(&$this, 'set_layout_for_post_type_meta_callback') );

		add_action('wp_ajax_change_layout_usage_for_post_types', array(&$this, 'set_layouts_post_types_on_usage_change') );

	}
	// debug only
	public function post_type_single_template($tpl)
	{
		global $post;
		print 'post_type_single_template <br />';
		print_r( $post->post_type );
		print '<br>'.$tpl;
		return $tpl;
	}
	// debug only
	public function post_type_page_template($tpl)
	{
		global $post;
		print 'page_template <br />';
		print_r( $post->post_type );
		print '<br>'.$tpl;
		return $tpl;
	}

	public function init_admin()
	{
		$this->post_types = $this->get_post_types_from_wp();
	}


	public function get_post_types_from_wp( $out = 'objects' )
	{
		$args = array(
			'public'   => true,
			//'_builtin' => false
		);

		$output = $out; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'

		$post_types = get_post_types( $args, $output, $operator );

		unset( $post_types['attachment'] );

		return $post_types;
	}

    public function no_templates_at_all()
    {
        $post_types = $this->get_post_types_from_wp();

        $bool = true;

        foreach( $post_types as $post_type )
        {
            if( $this->check_layout_template_page_exists( $post_type ) === true )
            {
                $bool = false;
                break;
            }
        }

        return $bool;
    }

    public function get_post_types_with_templates( )
    {
        $post_types = $this->get_post_types_from_wp();

        $ret = array();

        foreach( $post_types as $post_type )
        {
            if( $this->check_layout_template_page_exists( $post_type ) === true )
            {
                $ret[] = $post_type->name;
            }
        }

        return $ret;
    }

	public function get_post_types_options()
	{
		$options = $this->post_types_options->get_options( self::DDL_POST_TYPES_OPTIONS );
		if ($options === '') {
			$options = array();
		}
		
		return $options;
	}

	public function get_post_types()
	{
		return $this->post_types;
	}

	public function handle_post_type_data_save( $post_types_obj )
	{

		if( null === $post_types_obj || !$post_types_obj ) return false;

		$layout  = array_keys( $post_types_obj );

		if ($post_types_obj[$layout[0]]) {

			$post_types = array_values( $post_types_obj[$layout[0]] );

			foreach( $post_types as $post_type )
			{
				$check = $this->get_layout_to_type_object( $post_type );

				if ($check !== null) {

					if( self::KEY_PREFIX.$check->layout_id != $layout[0]  )
					{
						//	print 'case 1';
						if( $this->get_post_type_was_batched( $check->layout_id, $post_type ) ) {
							$this->remove_post_meta_for_post_type( $post_type, $check->layout_id );
						}
						$post_types_obj[ self::KEY_PREFIX.$check->layout_id ] = $this->post_types_options->remove_options_item( self::KEY_PREFIX.$check->layout_id , $post_type, self::DDL_POST_TYPES_OPTIONS );
					}
					else
					{
						$before_change = $this->get_post_types_options();
						$post_types = array_diff($before_change[self::KEY_PREFIX.$check->layout_id], $post_types_obj[self::KEY_PREFIX.$check->layout_id]);

						//	print 'case 2';

						foreach( $post_types as $post_type )
						{
							if( $this->get_post_type_was_batched( $check->layout_id, $post_type ) ) {
								$this->remove_post_meta_for_post_type( $post_type, $check->layout_id );
							}
						}
					}
				}
				//TODO: this one is needed to prevent errors, needs some other debug
				else {
					//print 'case 3';
					$layout_id = explode('layout_', $layout[0] );
					$layout_id = isset( $layout_id[1] ) ? $layout_id[1] : $layout[0];
				}
			}
		}
		else
		{
            $before_change = $this->get_post_types_options();

            if (is_array($before_change) && isset($before_change[$layout[0]])) {
                $post_types = $before_change[$layout[0]];
                $layout_id = explode('layout_', $layout[0]);
                $layout_id = isset($layout_id[1]) ? $layout_id[1] : $layout[0];

                if (is_array($post_types)) {
                    foreach ($post_types as $post_type) {
                        //	print_r( $this->get_post_type_was_batched( $layout_id, $post_type ) );
                        if ($this->get_post_type_was_batched($layout_id, $post_type)) {
                            $this->remove_post_meta_for_post_type($post_type, $layout[0]);
                        }
                    }
                }
            }
        }


		$ret =  $this->post_types_options->update_options( self::DDL_POST_TYPES_OPTIONS, $post_types_obj  );

		return $ret;
	}

	private function remove_post_meta_for_post_type( $post_type, $layout_string )
	{
		$layout_id = explode('layout_', $layout_string );
		$layout_id = isset( $layout_id[1] ) ? $layout_id[1] : $layout_string;
		$posts = $this->get_all_posts_of_post_type_obj( $post_type );
		$layout = get_post( $layout_id );

		foreach( $posts->ids as $id )
		{
			$meta = get_post_meta( $id, self::META_KEY, true );
			if( $layout->post_name === $meta )
			{
				delete_post_meta($id, self::META_KEY, $layout->post_name);
				update_post_meta($id, '_wp_page_template', 'default');
			}
		}

		$this->remove_track_batched_post_types( $post_type, $layout_id );
	}

	public function purge_layout_post_type_data( $layout_id )
	{
		// get everyone not only the ones directly associated with the current layout
		// so if there are single associations with current they will be purged as well
		$post_types = $this->get_post_types_from_wp();

		if( is_array($post_types) && count($post_types) > 0 )
		{
			$this->clean_layout_post_type_option( $layout_id );

			foreach( $post_types as $post_type )
			{
				$this->remove_track_batched_post_types( $post_type->name, $layout_id );
				$this->remove_post_meta_for_post_type( $post_type->name, $layout_id );
			}
		}
	}

	public function post_type_is_in_layout( $slug, $current = false )
	{
		global $post;

		if( $current === false && is_object( $post ) === false ) return false;

		$id = $current ? $current : $post->ID;

		$options = $this->get_post_types_options();

		if( isset( $options[self::KEY_PREFIX.$id] ) && in_array( $slug, $options[self::KEY_PREFIX.$id] ) )
		{
			return true;
		}

		return false;
	}

	public function post_type_assigned_in_layout( $post_type, $layout_id = false )
	{
		global $post;

		$id = $layout_id ? $layout_id : $post->ID;

		$options = $this->get_post_types_options();

		foreach( $options as $layout => $post_type_arr  )
		{
			if( in_array( $post_type, $post_type_arr) && self::KEY_PREFIX.$id == $layout )
			{
				return true;
			}
			else if( in_array( $post_type, $post_type_arr ) && self::KEY_PREFIX.$id != $layout )
			{
				return false;
			}

		}
		return true;
	}

	public function get_layout_to_type_object( $post_type )
	{
		$ret = new stdClass();
		$options = $this->get_post_types_options();

		foreach( $options as $layout => $post_type_arr  )
		{
			if( is_array($post_type_arr) && in_array( $post_type, $post_type_arr) ) {
				$layout = explode(self::KEY_PREFIX, $layout);
				$ret->layout_id = $layout[1];
				return $ret;
			}
		}

		return null;
	}

	public function get_layout_post_types( $layout_id )
	{
		$options = $this->get_post_types_options();

		return isset( $options[self::KEY_PREFIX.$layout_id] ) ? $options[self::KEY_PREFIX.$layout_id] : array();
	}

	public function print_layout_post_types( $layout_id )
	{
		?>
		<ul>
			<?php
				$post_types = $this->get_layout_post_types( $layout_id );
				$has_one = false;
				
				if( sizeof($post_types) === 0 )
				{
					?>
						<li><?php echo _e('Not assigned to any post type.', 'ddl-layouts'); ?></li>
					<?php
				}
				else
				{
					foreach( $post_types as $post_type )
					{
						$count = $this->check_post_meta_assigned_for_post_type( $layout_id, $post_type );
		
					//	if( $count === -1 ) return;
		
						$post_type_obj = get_post_type_object( $post_type );
		
						// check in case the user changes theme or deactivates plugin and post type is not available anymore
						if( is_object( $post_type_obj ) )
						{
							$has_one = true;
		
							if( is_object($count) && $count->count_posts > 0 )
							{
								?>
									<li>
										<?php $this->print_post_meta_assigned_to_post_type( $layout_id, $post_type, $count, $post_type_obj ); ?>
									</li>
								<?php
							}
							else
							{
								?>
									<li><?php echo $post_type_obj->labels->name . ' '; ?></li>
								<?php
								
							}
						}
						else
						{
							if( $has_one === false ) {
								?>
									<li><?php echo _e('Not assigned to any post type.', 'ddl-layouts'); ?></li>
								<?php
							}
						}
					}
				}
			?>
		</ul>
		<?php
	}

	public function print_apply_to_all_link_in_layout_editor( $type, $checked, $current = false )
	{
		global $post;

		if( $current === false && is_object( $post ) === false ) return;

		$id = $current ? $current : $post->ID;

		$count = $this->check_post_meta_assigned_for_post_type( $id, $type->name, $current );

		if( !$checked || $count === -1 || $count === 0 ) return;

		$this->print_post_meta_assigned_to_post_type( $id, $type->name, $count, $type, true );
	}

	private function check_post_meta_assigned_for_post_type( $layout_id, $post_type )
	{
		global $wpdb;

		$posts =  $this->get_all_posts_of_post_type_obj( $post_type );

		if( $posts->count ===  0 ) return -1;

		$key = self::META_KEY;

		$layout = get_post($layout_id);
		$layout_slug = $layout->post_name;

		$count_meta = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE
					meta_key='{$key}' AND meta_value='{$layout_slug}'
					AND post_id IN ({$posts->list})" );


		if( ( $count_meta - $posts->count ) >= 0 )
		{
			return 0;
		}

		$ret = new stdClass();

		$ret->count_posts = $posts->count;
		$ret->count_meta = $count_meta;
		$ret->post_list = $posts->list;

		return $ret;
	}

	private function get_all_posts_of_post_type_obj( $post_type )
	{
		global $wpdb;

		$cpts = $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE post_type='{$post_type}' AND post_status!='auto-draft'" );

		return (object) array(
			'ids' => $cpts,
			'count' => sizeof( $cpts ),
			//'list' => "'" . implode( "','", $cpts ) . "'"
			'list' => implode( ",", $cpts )
		);
	}

	public function get_layout_post_types_object( $layout_id )
	{
		$post_types = $this->get_layout_post_types( $layout_id );
		$has_one = false;
		$ret = array();

		if( sizeof($post_types) === 0 )
		{

			return false;

		}
		else
		{
			foreach( $post_types as $post_type )
			{
				$count = $this->check_post_meta_assigned_for_post_type( $layout_id, $post_type );

				$post_type_obj = get_post_type_object( $post_type );

				// check in case the user changes theme or deactivates plugin and post type is not available anymore
				if( is_object( $post_type_obj ) )
				{
					$has_one = true;
					$ret[] = $this->get_post_types_data_object( $layout_id, $post_type, $count, $post_type_obj );
				}
				else
				{
					if( $has_one === false ) {
						$ret[] = false;
					}
				}
			}
		}
		return $ret;
	}

	function get_post_types_data_object( $layout_id, $type, $count, $post_type )
	{
		$message = '';

		if( is_object( $count ) )
		{
			$missing = $count->count_posts - $count->count_meta;
			$post_num = $count->count_posts;
			$meta_num = $count->count_meta;
			$post_list = $count->post_list;
		}
		else
		{
			$missing = 0;
			$post_num = 0;
			$meta_num = 0;
			$post_list = '';
		}


		if ( ( $missing ) == 1 ) {
			$type_label = $post_type->labels->singular_name;
			$message = sprintf(__('%d %s uses a different Layout.', 'ddl-layouts'), $missing, $type_label);
		} elseif ( (  $missing ) > 1 ) {
			$type_label = $post_type->labels->name;
			$message = sprintf(__('%d %s use a different layout.', 'ddl-layouts'), $missing, $type_label);
		}

		$data = array(
			'layout_id' => $layout_id,
			'post_type' => $type,
			'post_num'=> $post_num,
			'meta_num' => $meta_num,
			'post_list' => $post_list,
			'missing' => $missing,
			'label' => $post_type->label,
			'singular' => $post_type->labels->singular_name,
			'plural' => $post_type->labels->name,
			'nonce' => wp_create_nonce( 'set-layout-for-cpt-nonce' ),
			'template_exists' => $this->check_layout_template_page_exists( $post_type ),
			'message' => $message
		);

		return $data;
	}

	private function print_post_meta_assigned_to_post_type( $layout_id, $type, $count, $post_type, $in_layout_page = false )
	{
		$data = $this->get_post_types_data_object( $layout_id, $type, $count, $post_type );

		ob_start(); ?>

		<?php echo $in_layout_page ? '' : $post_type->labels->name; ?>

		 <span class="js-alret-icon-hide-post"><a data-object="<?php echo htmlspecialchars( json_encode( $data ) ); ?>" class="apply-for-all js-apply-layout-for-all-posts js-alert-icon-hide-<?php echo $type; ?> button button-small button-leveled icon-warning-sign"> <?php echo sprintf(__('Use this layout for %d %s', 'ddl-layouts'), $data['missing'], $data['plural']); ?> </a></span></li>

		<?php ob_end_flush();

		include WPDDL_INC_ABSPATH.'/gui/templates/layout-assign-to-post-types.box.tpl.php';
	}

	public function check_layout_template_page_exists( $post_type )
	{
		global $wpddlayout;

		$template = $this->get_single_template( $post_type->name );

		$layout_template = $wpddlayout->templates_have_layout( array_flip($template) );

		if( sizeof( $layout_template ) > 0 )
		{
			return true;
		}

		return false;
	}

	private function get_layout_template_name_for_post_type( $post_type )
	{
		$template = 'default';

		if( $post_type === 'page' )
		{
			$template = "{$post_type}.php";
		}
		else if( $post_type === 'post' )
		{
			$template = "single.php";
		}
		else{
			$template = "single-{$post_type}.php";
		}

		return $template;
	}

	public function get_layout_template_for_post_type( $post_type )
	{
		global $wpddlayout;

		$main_template = $this->get_layout_template_name_for_post_type( $post_type );

		$template = $this->get_single_template( $post_type );

		$layout_template = $wpddlayout->templates_have_layout( array_flip($template) );

		if( in_array( $main_template, $layout_template) ) return $main_template;

		return isset( $layout_template[0] ) ? $layout_template[0] : 'default';
	}

	private function get_single_template( $post_type )
	{
		$templates = array();

		if( $post_type === 'page' )
		{
			$templates += get_page_templates();
			$templates[$post_type] = "{$post_type}.php";
		}
		else if( $post_type === 'post' )
		{
			$templates['single'] = "single.php";
		}
		else{
			$templates["single-{$post_type}"] = "single-{$post_type}.php";
			$templates['single'] = "single.php";
		}

		$templates['index'] = 'index.php';

       return $templates;
	}

	public function update_post_meta_for_post_type( $posts, $layout_id )
	{
		$ret = array();

		foreach( $posts as $id )
		{
			$meta_value = get_post( $layout_id );
			$post = get_post( $id );
			$ret[] = update_post_meta ( $id, self::META_KEY, $meta_value->post_name, get_post_meta($id, self::META_KEY, true ) );
			update_post_meta($id, '_wp_page_template', $this->get_layout_template_for_post_type( $post->post_type  ) );
		}

		return $ret;
	}

	public function set_layout_for_post_type_meta_callback()
	{
		if( $_POST && wp_verify_nonce( $_POST['set-layout-for-cpt-nonce'], 'set-layout-for-cpt-nonce' ) )
		{
            global $wpddlayout, $wpdd_gui_editor;
			extract( $_POST, EXTR_SKIP );

			$posts = explode(',', stripcslashes($post_list) );

			$res = $this->update_post_meta_for_post_type( $posts, $layout_id );

			$this->track_batched_post_types( $_POST['post_type'], $layout_id );

			$data = $_POST;

			$data['results'] = $res;

			if( isset($_POST['in_listing_page']) && $_POST['in_listing_page'] == 'yes' )
			{

				$send = json_encode( array( 'Data' => $wpddlayout->listing_page->get_grouped_layouts(isset($_GET['status']) && $_GET['status'] === 'trash' ? $_GET['status'] : 'publish'), 'message' => $data ) );
			}
			else
			{
				$send = json_encode( array('message' => $data, 'where_used_html' => $wpdd_gui_editor->get_where_used_output( $layout_id ) ) );
			}
		}
		else
		{
			$send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
		}

		die( $send );
	}

	private function track_batched_post_types( $post_type, $layout_id )
	{
		$meta_key = self::DDL_POST_TYPES_WAS_BATCHED;

		$meta = get_post_meta($layout_id, $meta_key, true );

		if( empty( $meta ) || $meta == '' )
		{
			$push = array();
			$push[] = $post_type;
		}
		else
		{
			$push = $meta;
			$push[] = $post_type;
		}

		update_post_meta( $layout_id, $meta_key, $push );
	}

	private function remove_track_batched_post_types( $post_type, $layout_id )
	{
		$meta_key = self::DDL_POST_TYPES_WAS_BATCHED;

		$meta = get_post_meta($layout_id, $meta_key, true );

		if ($meta) {
			$push = array_diff( $meta, array( $post_type ) );
	
			update_post_meta( $layout_id, $meta_key, $push );
		}
	}

	public function get_post_type_was_batched( $layout_id, $post_type )
	{
		$meta = get_post_meta($layout_id, self::DDL_POST_TYPES_WAS_BATCHED, true );
		//( $meta );
		if( !is_array( $meta ) ) return false;
		return in_array( $post_type, $meta );
	}

	public function print_post_types_checkboxes( $current = false, $do_not_show = false, $id_string = "", $show_ui = true, $show_edit = true )
	{
		$types = $this->get_post_types();
		ob_start();
		if ( sizeof($types) > 0 ) {
			include WPDDL_GUI_ABSPATH.'editor/templates/select-post-types.box.tpl.php';
		}
		return ob_get_clean();
	}



	public function set_layouts_post_types_on_usage_change()
	{
		if( $_POST && wp_verify_nonce( $_POST['layout-set-change-post-types-nonce'], 'layout-set-change-post-types-nonce' ) )
		{
            $post_types = isset( $_POST[self::POST_TYPES_OPTION_NAME] ) && is_array( $_POST[self::POST_TYPES_OPTION_NAME] ) ? array_unique( $_POST[self::POST_TYPES_OPTION_NAME] ) : array();
			$send = json_encode( array( 'message'=> array( 'changed' => $this->handle_post_type_data_save( array( "layout_".$_POST['layout_id'] => $post_types ) ), 'done' => 'yes' ) ) );
		}
		else
		{
			$send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
		}

		die($send);
	}

	public function clean_layout_post_type_option( $layout_id )
	{
		return $this->post_types_options->delete_options( self::DDL_POST_TYPES_OPTIONS, 'layout_'.$layout_id );
	}
}