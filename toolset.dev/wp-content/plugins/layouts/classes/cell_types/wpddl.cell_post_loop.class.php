<?php
class WPDD_layout_loop_cell extends WPDD_layout_cell {

	function __construct($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		parent::__construct($name, $width, $css_class_name, 'post-loop-cell', $content, $css_id, $tag);
		$this->set_cell_type('post-loop-cell');
	}

	function frontend_render_cell_content($target) {
		
		global $ddl_fields_api;
		$ddl_fields_api->set_current_cell_content( $this->get_content() );

		ob_start();

        if( $target->is_layout_argument_set( 'post-loop-callback' ) && function_exists( $target->get_layout_arguments( 'post-loop-callback' ) ) ) {
            remove_all_actions( 'loop_start' );
            remove_all_actions('loop_end' );
            call_user_func( $target->get_layout_arguments( 'post-loop-callback' ) );
        } else {

            if (have_posts()) {

                while (have_posts()) {
                    the_post();
                    get_template_part( 'content', get_post_format() );
                }
            }
        }

		$target->cell_content_callback( ob_get_clean() );
	}
}

class WPDD_layout_loop_cell_factory extends WPDD_layout_cell_factory{

	public function build($name, $width, $css_class_name = '', $content = null, $css_id, $tag) {
		return new WPDD_layout_loop_cell($name, $width, $css_class_name, $content, $css_id, $tag);
	}

	public function get_cell_info($template) {
		$template['icon-css'] = 'icon-cog';
		//	$template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/child-layout.png';
		$template['name'] = __('Post loop', 'ddl-layouts');
		$template['description'] = __('A cell that displays a WordPress post loop. This cell can be used for displaying a list of posts. eg. blogs, archives and search pages.', 'ddl-layouts');
		$template['button-text'] = __('Assign Post Loop cell', 'ddl-layouts');
		$template['dialog-title-create'] = __('Create a Post Loop cell', 'ddl-layouts');
		$template['dialog-title-edit'] = __('Edit Post Loop cell', 'ddl-layouts');
		$template['dialog-template'] = $this->_dialog_template();
		$template['allow-multiple'] = false;
		$template['cell-class'] = 'post-loop-cell';
		$template['category'] = __('Post display', 'ddl-layouts');
		$template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/post-loop-cell.png';
		return $template;
	}

	public function get_editor_cell_template() {
		ob_start(); ?>
		<div class="cell-content">
			<p class="cell-name">{{ name}} &ndash; <?php _e('Post Loop Cell', 'ddl-layouts'); ?></p>
			<p><?php _e('This cell displays the content of the WordPress loop', 'ddl-layouts'); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}


	private function _dialog_template() {
        ob_start();
		ddl_add_help_link_to_dialog(WPDLL_LOOP_CONTENT_CELL, __('Learn about the Post Loop cell', 'ddl-layouts'));
        return ob_get_clean();
	}

	public function enqueue_editor_scripts() {
		//wp_register_script('post-loop-cell', WPDDL_RELPATH . '/inc/gui/editor/js/post-loop-cell.js', array('jquery'), WPDDL_VERSION, true);
		//wp_enqueue_script('post-loop-cell');
	}
}

class  WPDD_layout_post_loop_cell_manager{

	private $layouts_options;
	const OPTION_BLOG = 'layouts_home-blog-page';
	const OPTION_SEARCH = 'layouts_search-page';
	const OPTION_YEAR = 'layouts_year-page';
	const OPTION_MONTH = 'layouts_month-page';
	const OPTION_DAY = 'layouts_day-page';
    const OPTION_404 = 'layouts_404_page';
	const OPTION_AUTHOR = 'layouts_author-page';
	const OPTION_TAXONOMY_PREFIX = 'layouts_taxonomy_loop_';
	const OPTION_TYPES_PREFIX = 'layouts_cpt_';

	const TAXONOMY_LOOPS_NAME = 'wordpress_archive_loops';
	const POST_TYPES_LOOPS_NAME = 'wordpress_archive_loops';
	const WORDPRESS_DEFAULT_LOOPS_NAME = 'wordpress_archive_loops';
    const WORDPRESS_OTHERS_SECTION = 'wordpress_others_section';

	public function __construct()
	{
		$this->layouts_options = new WPDDL_Options_Manager(WPDDL_GENERAL_OPTIONS);

		add_filter('dd_layouts_register_cell_factory', array(&$this,'dd_layouts_register_loop_cell_factory') );
	}

	public function get_option( $option )
	{
		return $this->layouts_options->get_options( $option );
	}

	public function dd_layouts_register_loop_cell_factory($factories) {
		$factories['post-loop-cell'] = new WPDD_layout_loop_cell_factory;
		if (class_exists('WPDD_layout_loop_views_cell_factory')) {
			$factories['post-loop-views-cell'] = new WPDD_layout_loop_views_cell_factory;
		}
		return $factories;
	}

	private function _get_default_archive_loops() {
		$loops = array(
			self::OPTION_BLOG => __('Home/Blog', 'ddl-layouts'),
			self::OPTION_SEARCH => __('Search results', 'ddl-layouts'),
			self::OPTION_AUTHOR => __('Author archives', 'ddl-layouts'),
			self::OPTION_YEAR => __('Year archives', 'ddl-layouts'),
			self::OPTION_MONTH => __('Month archives', 'ddl-layouts'),
			self::OPTION_DAY => __('Day archives', 'ddl-layouts'),

		);
		return $loops;
	}

    private function _get_others( )
    {
        if( $this->is_using_permalinks() === false ) return array();

        return array(
            self::OPTION_404 => __('404 Not Found Page', 'ddl-layouts')
        );
    }

	private function _get_post_types_loops()
	{
		$loops = array();
		// Only offer loops for post types that already have an archive
		$post_types = get_post_types(array('public'=>true, 'has_archive' => true), 'objects');
		foreach($post_types as $post_type) {
			if (!in_array($post_type->name, array('post', 'page', 'attachment'))) {
				$type = self::OPTION_TYPES_PREFIX . $post_type->name;
				$name = $post_type->labels->name;
				$loops[$type] = $name;
			}
		}
		return $loops;
	}

	private function get_loops_to_display( )
	{
		return array(
			(object)array(
							'title' => __('Custom Post Archives:', 'ddl-layouts'),
							'loop' => $this->_get_post_types_loops(),
							'name' => self::POST_TYPES_LOOPS_NAME,
						),
			(object)array(
							'title' => __('Taxonomy Archives:', 'ddl-layouts'),
							'loop' => $this->_get_taxonomies_loops(),
							'name' => self::TAXONOMY_LOOPS_NAME
						),
			(object)array(
							'title' => __('Main Archives:', 'ddl-layouts'),
							'loop' => $this->_get_default_archive_loops(),
							'name' => self::WORDPRESS_DEFAULT_LOOPS_NAME
						)
				);
	}

    private function get_others_to_display()
    {
        if( $this->is_using_permalinks() === false ) return array();

        return array(
            (object) array(
                'title' => __('Others:', 'ddl-layouts'),
                'loop' => $this->_get_others( ),
                'name' => self::WORDPRESS_OTHERS_SECTION
            )
        );
    }

	public function display_loops( $current = false, $id_string = "", $show_ui = true )
	{
		$loops = $this->get_loops_to_display( );

		ob_start();
		if ( sizeof( $loops ) > 0 ) {
			include WPDDL_GUI_ABSPATH.'editor/templates/select-wordpress-archives.box.tpl.php';
		}
		return ob_get_clean();
	}

    public function display_others( $current = false, $id_string = "", $show_ui = true )
    {
        $loops = $this->get_others_to_display( );

        ob_start();
        if ( sizeof( $loops ) > 0 ) {
            include WPDDL_GUI_ABSPATH.'editor/templates/select-wordpress-others.box.tpl copy.php';
        }
        return ob_get_clean();
    }

	private function _get_taxonomies_loops()
	{
		$taxonomies = get_taxonomies('', 'objects');
		$loops = array();
		$exclude_tax_slugs = array();
		$exclude_tax_slugs = apply_filters( 'layouts_admin_exclude_tax_slugs', $exclude_tax_slugs );
		foreach ($taxonomies as $category_slug => $category) {
			if ( in_array( $category_slug, $exclude_tax_slugs ) ) {
				continue;
			}
			if ( !$category->show_ui ) {
				continue; // Only show taxonomies with show_ui set to TRUE
			}
			$type = self::OPTION_TAXONOMY_PREFIX . $category->name;
			$label = $category->label;
			$loops[$type] = $label;
		}

		return $loops;
	}

	private function save_archive_loop_option( $option, $layout_id )
	{
		$this->layouts_options->update_options( $option, $layout_id, true );
	}

	private function archive_has_layout( $archive, $layout_id )
	{
		return $this->layouts_options->get_options( $archive ) === $layout_id;
	}

	public function handle_archives_data_save( $archives, $layout_id )
	{

        if( $archives === null || !is_array($archives) ) return;

		$options = $this->layouts_options->get_options();

		$types = $this->_get_post_types_loops();
		$taxonomies = $this->_get_taxonomies_loops();
		$wp_defaults = $this->_get_default_archive_loops();

		$check_options = array_merge( $wp_defaults, $taxonomies, $types );

		// remove loop if present in options and then unchecked
		foreach( $check_options as $check => $label )
		{
			if( isset($options[$check]) && (int) $options[$check] === (int) $layout_id && !in_array( $check, $archives ) )
			{
				$this->layouts_options->delete_option( $check );
			}
		}

		// then save what's there for this layout

            foreach( $archives as $archive )
            {
                $this->save_archive_loop_option( $archive, $layout_id );
            }

	}

    public function handle_others_data_save( $archives, $layout_id )
    {

        if( $archives === null || !is_array($archives) ) return;

        $options = $this->layouts_options->get_options();

        $others = $this->_get_others( );

        $check_options = $others;

        // remove loop if present in options and then unchecked
        foreach( $check_options as $check => $label )
        {
            if( isset($options[$check]) && (int) $options[$check] === (int) $layout_id && !in_array( $check, $archives ) )
            {
                $this->layouts_options->delete_option( $check );
            }
        }

        // then save what's there for this layout

        foreach( $archives as $archive )
        {
            $this->save_archive_loop_option( $archive, $layout_id );
        }

    }

	public function get_layout_loops( $layout_id )
	{
		$options = $this->layouts_options->get_options( );

		if( is_array( $options ) === false || sizeof($options) === 0 ) return array();

		$res = array_keys( $options, $layout_id );

		return $res;
	}

	public function get_layout_loops_labels( $layout_id )
	{
		$layout_loops = $this->get_layout_loops( $layout_id );

		if( is_array($layout_loops) === false || sizeof($layout_loops) === 0 ) return false;

		$ret = array();
		$types = $this->_get_post_types_loops();
		$taxonomies = $this->_get_taxonomies_loops();
		$wp_defaults = $this->_get_default_archive_loops();
        $wp_others = $this->_get_others();

		$check_options = array_merge( $wp_defaults, $taxonomies, $types, $wp_others );

		foreach( $layout_loops as $loop )
		{
            if( isset( $check_options[$loop] ) )
            {
                $ret[] = $check_options[$loop];
            }
		}

		return $ret;
	}
    public function get_loop_display_object( $loop )
    {

        $ret = null;

        if( $loop == self::OPTION_BLOG )
        {
            $ret = array(
                'href' => get_bloginfo('url'),
                'title' => __('Home/Blog', 'ddl-layouts'),
                'type' => 'archives',
                'types' => __('Archives', 'ddl-layouts')
            ) ;
        }
        elseif( self::OPTION_404 === $loop)
        {
            $ret = array(
                'href' => get_bloginfo('url') . '/A_BROKEN_LINK',
                'title' => __('Not Found 404', 'ddl-layouts'),
                'type' => 'archives',
                'types' => __('Archives', 'ddl-layouts')
            ) ;
        }
        else if( $loop == self::OPTION_SEARCH  )
        {
            $ret = array(
                'href' => get_bloginfo('url') . '?s=',
                'title' => __('Search archives', 'ddl-layouts'),
                'type' => 'archives',
                'types' => __('Archives', 'ddl-layouts')
            ) ;
        }
        elseif( $loop == self::OPTION_AUTHOR )
        {

            $user_ID = get_current_user_id();

            $ret = array(
                'href' => get_author_posts_url( $user_ID ),
                'title' => __('Author archives', 'ddl-layouts'),
                'type' => 'archives',
                'types' => __('Archives', 'ddl-layouts')
            ) ;
        }
        elseif( $loop == self::OPTION_YEAR )
        {

            $date = $this->get_last_post_date();

            $ret = array(
                'href' => get_year_link( $date->year ),
                'title' => __('Year archives', 'ddl-layouts'),
                'type' => 'archives',
                'types' => __('Archives', 'ddl-layouts')
            ) ;
        }
        elseif( $loop == self::OPTION_MONTH )
        {
            $date = $this->get_last_post_date();

            $ret = array(
                       'href' => get_month_link( $date->year, $date->month ),
                       'title' => __('Month archives', 'ddl-layouts'),
                        'type' => 'archives',
                        'types' => __('Archives', 'ddl-layouts')
                      ) ;
        }
        elseif( $loop == self::OPTION_DAY )
        {
            $date = $this->get_last_post_date();

            $ret = array(
                'href' => get_day_link( $date->year, $date->month, $date->day ),
                'title' => __('Day archives', 'ddl-layouts'),
                'type' => 'archives',
                'types' => __('Archives', 'ddl-layouts')
            ) ;
        }
        elseif( sizeof( explode( self::OPTION_TAXONOMY_PREFIX, $loop ) ) > 1 )
        {
            $tax = explode( self::OPTION_TAXONOMY_PREFIX, $loop );
            $taxonomy = get_taxonomy( $tax[1] );
            $slug = $taxonomy->rewrite['slug'];
            $terms = array_values( get_terms( $tax[1], array( 'hide_empty'    => true ) ) );

			$ret = array(
				'href' => '',
				'title' => __( sprintf("%s archives", $taxonomy->labels->singular_name ), 'ddl-layouts'),
				'type' => 'archives',
				'types' => __('Archives', 'ddl-layouts')
			);

            if( is_array($terms) && count( $terms ) > 0 && isset($terms[0]) && is_object($terms[0]) && property_exists($terms[0], 'slug') )
            {


                if( $this->is_using_permalinks() )
                {
                    $link = get_bloginfo('url') . '/' . $slug .'/'. $terms[0]->slug;
                }
                else{

                    $link = get_bloginfo('url') . '?'.$slug.'=' . $terms[0]->slug;
                }

                $ret['href'] = $link;

            }
        }

        elseif( sizeof( explode( self::OPTION_TYPES_PREFIX, $loop ) ) > 1 )
        {
            $type = explode( self::OPTION_TYPES_PREFIX, $loop );

            $post_type = get_post_type_object( $type[1] );

            if( $this->is_using_permalinks() )
            {
                $link = get_bloginfo('url') . '/' . $post_type->rewrite['slug'];
            }
            else{
                $link = get_bloginfo('url') . '?post_type=' . $post_type->rewrite['slug'];
            }

            $ret = array(
                'href' => $link,
                'title' => __( sprintf("%s archives", $post_type->labels->singular_name ), 'ddl-layouts'),
                'type' => 'archives',
                'types' => __('Archives', 'ddl-layouts'),
            );
        }

        return $ret;
    }
    function get_last_post_date()
    {

        $ret = array();

        // get_lastpostdate built in function retrieves date for whatever post type while we
        // want posts not to break the archive display
        // if no posts are available we use get_lastpostdate to prevent interpreter to throw errors
        // and we expect a 404 page in FE
        $args = array(
            'posts_per_page'   => 1,
            'offset'           => 0,
            'category'         => '',
            'orderby'          => 'post_date',
            'order'            => 'DESC',
            'include'          => '',
            'exclude'          => '',
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => 'post',
            'post_mime_type'   => '',
            'post_parent'      => '',
            'post_status'      => 'publish',
            'suppress_filters' => false );

        $posts = get_posts( $args );

        if( $posts && isset($posts[0]) )
        {
            $date = $posts[0]->post_date;
        }
        else{
            $date =   get_lastpostdate('blog');
        }

        $lastpost = strtotime( $date );
        $ret['year'] = date('Y', $lastpost);
        $ret['month'] = date('m', $lastpost);
        $ret['day'] = date('d', $lastpost);

        return (object) $ret;
    }
    private function is_using_permalinks()
    {
        global $wp_rewrite;

        return $wp_rewrite->using_permalinks();
    }
}