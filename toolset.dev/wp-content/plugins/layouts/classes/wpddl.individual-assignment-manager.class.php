<?php
class WPDD_Layouts_IndividualAssignmentManager
{

    const INDIVIDUAL_POST_ASSIGN_CHECKBOXES_NAME = 'individual_posts_assign';

	public function __construct() {
		add_action('wp_ajax_ddl_fetch_post_for_layout', array($this, 'fetch_posts_used_by_layout'));
		add_action('wp_ajax_ddl_remove_layout_from_post', array($this, 'remove_layout_from_post'));
		add_action('wp_ajax_ddl_assign_layout_to_posts', array($this, 'assign_layout_to_posts'));
		add_action('wp_ajax_ddl_get_individual_post_checkboxes', array($this, 'get_post_checkboxes_callback'));
	}
	
	
	public function fetch_posts_used_by_layout () {
		global $wpddlayout;
		
		$this->_check_nonce();

		$result = array();
		
		$layout_id = $_POST['layout_id'];

        $posts = $wpddlayout->get_where_used( $layout_id, false, true );
		$post_types = $this->get_post_types( $layout_id );
		
		ob_start();
		
		?>
		<ul class="individual-pages-list">
			<?php foreach($posts as $post): ?>
				<?php if (!(in_array($post->post_type, $post_types) && $wpddlayout->post_types_manager->get_post_type_was_batched( $layout_id, $post->post_type ))): ?>
					<li><i class="icon-remove js-remove-individual-page" data-id="<?php echo $post->ID; ?>"></i> <?php echo $this->encode_title($post->post_title); ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		
		<?php
		
		$result['posts'] = ob_get_clean();
		
		echo json_encode($result);
		
		die();
	}

    private function get_post_types( $layout_id )
    {
        global $wpddlayout;

        $post_types = $wpddlayout->post_types_manager->get_layout_post_types_object( $layout_id );

        if( $post_types === false ) return array();

        foreach ($post_types as $key => $type) {
            $post_types[$key] = $type['post_type'];
        }

        return $post_types;
    }
	
	public function remove_layout_from_post () {
		$this->_check_nonce();
		
		if (isset($_POST['post_id'])) {
			update_post_meta($_POST['post_id'], '_layouts_template', 0);
            update_post_meta( $_POST['post_id'], '_wp_page_template', 'default' );
		}

        if( isset( $_POST['in_listing_page'] ) && $_POST['in_listing_page'] === 'yes' )
        {
            global $wpddlayout;
            $send = $wpddlayout->listing_page->set_up_send_data( $_POST['current_page_status'] );
            die( $send );
        }
		else{
            echo json_encode( array('ok' => true) );
            die();
        }

	}
	
	public function assign_layout_to_posts () {
		global $wpddlayout;

		$this->_check_nonce();
		
		if (isset($_POST['posts']) && isset($_POST['layout_id'])) {
			$layout_id = $_POST['layout_id'];
			$layout = $wpddlayout->get_layout_from_id( $layout_id );
			$layout_slug = $layout->get_post_slug();
			
			foreach($_POST['posts'] as $post_id) {
				update_post_meta($post_id, '_layouts_template', $layout_slug);
			}

            $wpddlayout->post_types_manager->update_post_meta_for_post_type( $_POST['posts'], $layout_id );
		}

        if( isset( $_POST['in_listing_page'] ) && $_POST['in_listing_page'] === 'yes' )
        {
            $send = $wpddlayout->listing_page->set_up_send_data( $_POST['current_page_status'] );
            die( $send );
        }
	}

	private function _check_nonce () {
		if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'],
							'wp_nonce_individual-pages-assigned')) {
			die('verification failed');
		}
	}
	
	public function get_post_checkboxes_callback() {
		$this->_check_nonce();
		
		$search = '';
		if (isset($_POST['search'])) {
			$search = $_POST['search'];
		}
		
		$sort = true;
		
		if (isset($_POST['sort'])) {
			$sort = $_POST['sort'] == 'true' ? true : false;
		}
	
		echo $this->get_posts_checkboxes($_POST['post_type'], $_POST['count'], $search, $sort);
		die();
	}
	
	public function filter_query_fields ($fields) {
		global $wpdb;
		
		$fields = $wpdb->posts . '.ID,' . $wpdb->posts . '.post_title';
		return $fields;
	}
	
	public function get_posts_checkboxes($post_type, $count = -1, $search = '', $sort = true) {
        global $wpddlayout;

        if( $post_type === 'any' )
        {
            $post_type = $wpddlayout->post_types_manager->get_post_types_with_templates( );
        }
        else
        {
            if( $wpddlayout->post_types_manager->check_layout_template_page_exists( get_post_type_object( $post_type ) ) === false ) return '';
        }

		$get_posts = new WP_Query;
		$recent_args = array(
			'post_type' => $post_type,
			'posts_per_page' => $count
			);
		
		if ($sort) {
			$recent_args = array_merge( $recent_args, array( 'orderby' => 'post_date', 'order' => 'DESC') );
		} else {
			$recent_args = array_merge( $recent_args, array( 'orderby' => 'post_title', 'order' => 'ASC') );
		}
		
		if ($search) {
			$recent_args['s'] = $search;
		}
//		add_filter('posts_fields_request', array($this, 'filter_query_fields'));
		$most_recent = $get_posts->query( $recent_args );
//		remove_filter('posts_fields_request', array($this, 'filter_query_fields'));
		ob_start();
		?>
			<ul class="ddl-posts-check-list">
				<?php foreach ($most_recent as $recent): ?>
					<li><input name="<?php echo self::INDIVIDUAL_POST_ASSIGN_CHECKBOXES_NAME; ?>" class="js-ddl-individual-posts" type="checkbox" value="<?php echo $recent->ID; ?>" data-title="<?php echo $this->encode_title($recent->post_title); ?>" /><?php echo $this->encode_title($recent->post_title); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php
		
		if ($search && sizeof($most_recent) == 0) {
			_e('No results found', 'ddl-layouts');
		}
		
		return ob_get_clean();
	}
	
	private function encode_title($title) {
		return htmlentities( trim( $title ) ? $title : __( '(no title)', 'ddl-layouts' ) );
	}
	
}