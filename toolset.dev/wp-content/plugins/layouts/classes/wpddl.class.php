<?php
class WPDD_Layouts {
	public $header_added = false;
	private $cell_factory = array();
	private $registed_cells = null;
	private $render_errors = array();
	private $layouts_editor_page = false;
	public $css_manager;
	private $scripts_manager;
	public $post_types_manager;
	public $frameworks_options_manager;
	private $css_framework;
	public $listing_page;
	public $layout_post_loop_cell_manager;

	function __construct(){

        $this->plugin_localization();
	
		$this->registed_cells = new WPDD_registed_cell_types();

		$this->layout_post_loop_cell_manager = new WPDD_layout_post_loop_cell_manager;

		$this->registered_theme_sections = new WPDD_register_layout_theme_section();

		$this->render_errors = array();

		$this->scripts_manager = new WPDDL_scripts_manager();

		$this->upload_options = new WPDDL_Options_Manager( 'upload_options' );

		$this->post_types_manager = new WPDD_Layouts_PostTypesManager();
		
		$this->individual_assignment_manager = new WPDD_Layouts_IndividualAssignmentManager();

		global $wpdd_gui_editor;

		$this->wpddl_init();

		$this->css_manager = WPDD_Layouts_CSSManager::getInstance();

		$this->frameworks_options_manager = WPDD_Layouts_CSSFrameworkOptions::getInstance();

		$this->set_css_framework( $this->frameworks_options_manager->get_current_framework() );

		add_action('wp_ajax_nopriv_'.WPDDL_LAYOUTS_CSS, array(&$this, 'handle_layout_css_from_db_print'), 10 );

		$this->listing_page = new WPDD_LayoutsListing();

		if( is_admin()){

			$this->fix_up_views_slugs();
			
			if ($this->layouts_editor_page) {
				new WPDD_GUI_DIALOGS();
			}

			global $pagenow;

			$wpdd_gui_editor = new WPDD_GUI_EDITOR();

			/**/
			add_action('admin_menu', array($this, 'add_layouts_admin_menu'));

			/*Actions at admin page for layout edit and layout*/
			if ($pagenow == 'post.php' || $pagenow == 'post-new.php' || $pagenow == 'admin-ajax.php') {
				add_action('admin_head', array($this,'wpddl_edit_template_options'));
				add_action('admin_enqueue_scripts', array($this, 'page_edit_scripts'));
				add_action('admin_enqueue_scripts', array($this, 'page_edit_styles'));
			}

			if ($pagenow == 'plugins.php') {
				add_action('admin_enqueue_scripts', array($this, 'plugin_page_styles'));
			}

			/*Saving layout settings at post/page edit page*/
			if ($pagenow == 'post.php' || $pagenow == 'post-new.php' || $pagenow == 'admin-ajax.php') {
				add_action('save_post', array($this,'wpddl_save_post'), 10, 2);
			}

			if (isset( $_GET['page'] ) && ( $_GET['page']=='dd_layouts' ||
					$_GET['page']=='dd_layout_theme_export' ||
					$_GET['page'] == 'dd_layouts_settings' ||
					$_GET['page'] == 'dd_layouts_edit' )) {

				add_action('admin_enqueue_scripts', array($this, 'preload_styles'));
				add_action('admin_enqueue_scripts', array($this, 'preload_scripts'));

			}
			if( isset( $_GET['page'] ) && ( $_GET['page']=='dd_tutorial_videos') )
			{
				add_action('admin_enqueue_scripts', array($this, 'help_page_scripts'));
			}
			
			if( isset( $_GET['in-iframe-for-layout']) && $_GET['in-iframe-for-layout'] == 1 &&
			   (isset( $_GET['page'] ) && (('views-editor' == $_GET['page']) ||
										   ('views-embedded' == $_GET['page']) ||
										   ('view-archives-embedded' == $_GET['page']) ||
										   ('view-archives-editor' == $_GET['page']) ))) {
				add_action('admin_enqueue_scripts', array($this, 'views_in_iframe_scripts'));
			}
			
			add_action('wp_ajax_ddl_create_layout', array($this, 'create_layout_callback') );
			add_action('wp_ajax_ddl_dismiss_template_message', array($this, 'ddl_dismiss_template_message') );


		} else {
		   // add_action('wp_head', array(&$this,'handle_layout_css_fe'));

			if(isset($_GET['ddl_style'])){
				header('Content-Type:text/css');
				$this->wpddl_frontent_styles($_GET['ddl_style']);
				die();
			}

			add_action('wp_head', array($this,'wpddl_frontend_header_init'));
			add_action('wpddl_before_header', array($this, 'before_header_hook'));
			add_action('wp_enqueue_scripts', array($this, 'load_frontend_js'));
			add_action('wp_enqueue_scripts', array($this, 'load_frontend_css'));
		}


	}
	function __destruct(){

	}
	
    // Localization
    function plugin_localization(){
        $locale = get_locale();
        load_textdomain( 'ddl-layouts', WPDDL_ABSPATH . '/locale/layouts-' . $locale . '.mo');
    }
	

	function set_css_framework( $framework )
	{
		$this->css_framework = $framework;
	}

	function get_css_framework()
	{
		return $this->css_framework;
	}

	public function enqueue_scripts ( $handles ) {
		$this->scripts_manager->enqueue_scripts( $handles );
	}

	public function enqueue_styles ( $handles ) {
		$this->scripts_manager->enqueue_styles ( $handles );
	}

	public function deregister_styles ( $handles ) {
		$this->scripts_manager->deregister_styles ( $handles );
	}

	public function deregister_scripts ( $handles ) {
		$this->scripts_manager->deregister_styles ( $handles );
	}

	public function localize_script ( $handle, $object, $args ) {
		$this->scripts_manager->localize_script( $handle, $object, $args );
	}

	function preload_styles(){

			$this->enqueue_styles( array (
					'toolset-select2-css',
					'ddl-dialogs-forms-css',
					'ddl-dialogs-general-css',
					'ddl-dialogs-css',
					'wp-layouts-pages',
					'toolset-font-awesome',
					'toolset-colorbox',
					'toolset-common',
					'toolset-notifications-css',
					'views-dialogs-css'
				)
		);
	}

	function help_page_scripts()
	{

		$this->enqueue_styles(array(
			'toolset-select2-css',
			'ddl-dialogs-forms-css',
			'ddl-dialogs-general-css',
			'ddl-dialogs-css',
			'wp-layouts-pages',
			'toolset-font-awesome',
			'toolset-colorbox',
			'toolset-common',
			'wp-mediaelement'
		));

		$this->enqueue_scripts(array(
			'select2',
			'wp-layouts-colorbox-script',
			'wp-layouts-dialogs-script',
			'wp-mediaelement',
			'ddl_common_scripts',
			'wp-layouts-video-js',
			'wp-layouts-help-js'
		));

		$this->localize_script('wp-layouts-help-js', 'DDLayout_settings', array(
				'DDL_JS' => array(
					'res_path' => WPDDL_RES_RELPATH,
					'lib_path' => WPDDL_RES_RELPATH . '/js/external_libraries/',
					'editor_lib_path' => WPDDL_GUI_RELPATH."editor/js/",
					'dialogs_lib_path' => WPDDL_GUI_RELPATH."dialogs/js/",
					'DEBUG' => WPDDL_DEBUG,
				)
			)
		);
	}
	
	function views_in_iframe_scripts () {
		$this->enqueue_scripts(array(
			'ddl-layouts-views-support'
		));
	}

	function enqueue_cell_styles() {

		foreach( $this->cell_factory as $factory ) {

			if ( method_exists( $factory, 'enqueue_editor_styles') ) {
				$factory->enqueue_editor_styles();
			}
		}
		$this->registed_cells->enqueue_cell_styles();
	}

	function preload_scripts(){

		$this->enqueue_scripts( array (
									   'select2',
									   'ddl_create_new_layout',
									   'wp-layouts-colorbox-script',
									   'toolset-utils',
										'ddl_common_scripts',
										'wp-layouts-dialogs-script'
									  )
							  );
	}

	function page_edit_scripts() {

		$this->enqueue_scripts( array (
									   'select2',
									   'ddl_post_edit_page',
									  )
							  );

		$opts = $this->layout_get_templates_options_object( );

		$this->localize_script('ddl_post_edit_page', 'DDLayout_settings_editor', array(
			'strings' => array(
							   'content_template_diabled' => __('Since this page uses a Layout, styling with a Content Template is disabled.', 'ddl-layouts')
							  ),
			'layout_templates' => $opts->layout_templates,
			'layout_template_defaults' => $opts->template_option,
		) );


	}
	
	function layout_get_templates_options_object( )
	{
		// Determine which templates support layouts.

		$ret = new stdClass();

        $template_option = $this->get_option('templates');
        if ($template_option) {
            foreach($template_option as $file => $layout) {
                $layout_templates[] = $file;
            }
        }

		$templates = wp_get_theme()->get_page_templates();

		$ret->layout_templates = $this->templates_have_layout( $templates );
		$ret->template_option = $template_option;

		return $ret;
	}

	function templates_have_layout( $templates )
	{
		$layout_templates = array();

		foreach ($templates as $file => $name) {
			if (!in_array($file, $layout_templates)) {
				$file_data = @file_get_contents(get_template_directory() . '/' . $file);
				if ($file_data === false) {
					// try child theme.
					$file_data = @file_get_contents(get_stylesheet_directory() . '/' . $file);
				}
				if ($file_data !== false) {
					if (strpos($file_data, 'the_ddlayout') !== false) {
						$layout_templates[] = $file;
					}
				}
			}
		}

		return $layout_templates;
	}

	function template_have_layout( $file )
	{

		$bool = false;

		$file_abs = get_template_directory() . '/' . $file;

		if ( file_exists( $file_abs ) ) {
			$file_data = @file_get_contents( $file_abs );
			if ($file_data === false) {
				// try child theme.
				$file_data = @file_get_contents(get_stylesheet_directory() . '/' . $file);
			}
			if ($file_data !== false) {
				if (strpos($file_data, 'the_ddlayout') !== false) {
					$bool = true;
				}
			}
		}

		return $bool;
	}

	function page_edit_styles() {
		$this->enqueue_styles( array ('toolset-select2-css','ddl_post_edit_page_css') );

	}

	function plugin_page_styles() {
		$this->enqueue_styles( array ('toolset-common') );
	}

	function enqueue_cell_scripts() {

		foreach( $this->cell_factory as $factory ) {

			if ( method_exists( $factory, 'enqueue_editor_scripts') ) {
				$factory->enqueue_editor_scripts();
			}
		}

		$this->registed_cells->enqueue_cell_scripts();
	}
	/*
	 * this registers and enqueue those scripts to be used everywhere
	 */
	function register_and_enqueue_global_scripts()
	{
		if( is_admin() )
		{
			$this->enqueue_scripts( array (
					'headjs',
					'ddl_common_scripts'
				)
			);
		}
		$this->enqueue_scripts( 'jquery' );
	}

	function wpddl_init(){

		//check if css options are already set and set defaults if necessary
		// $this->css_settings_init();

		// Check for editor page.
		$this->layouts_editor_page = false;
		if (isset($_GET['page']) and $_GET['page']=='dd_layouts_edit') {
			if(isset($_GET['layout_id']) and $_GET['layout_id']>0){
				$this->layouts_editor_page = true;
			}
		}

		$this->wpddl_register_post_type_for_layouts();

		$this->register_and_enqueue_global_scripts();

		$this->cell_factory = apply_filters('dd_layouts_register_cell_factory', array());
		$this->cell_factory = apply_filters('dd_layouts_de_register_cell_factory', $this->cell_factory);
	}

	function register_dd_layout_cell_type($cell_type, $data) {
		return $this->registed_cells->register_dd_layout_cell_type($cell_type, $data);
	}

	function register_dd_layout_theme_section($theme_section, $args){
		$this->registered_theme_sections->register_dd_layout_theme_section($theme_section, $args);
	}

	function has_theme_sections()
	{
		return sizeof( $this->registered_theme_sections->get_theme_sections() ) > 0;
	}

	function get_current_cell_info() {
		return $this->registed_cells->get_current_cell_info();
	}

	function create_cell($cell_type, $name, $width, $css_class_name = '', $content = null, $cssId = '', $tag = 'div') {
		if (isset($this->cell_factory[$cell_type])) {
			return $this->cell_factory[$cell_type]->build($name, $width, $css_class_name, $content, $cssId, $tag);
		}

		return $this->registed_cells->create_cell($cell_type, $name, $width, $css_class_name, $content, $cssId, $tag);
	}

	function get_cell_templates() {
		$templates = '';

		foreach ($this->cell_factory as $cell_type => $factory) {
			$templates .= '<script type="text/html" id="' . $cell_type . '-template">'."\n";
			$templates .= $factory->get_editor_cell_template()."\n";
			$templates .= '</script>'."\n";
		}

		$templates .= $this->registed_cells->get_cell_templates();

		return $templates;
	}

	function get_cell_types() {
		
		global $wpddl_features;
		
		$cell_types = array_keys($this->cell_factory);
		$cell_types = array_merge($cell_types, $this->registed_cells->get_cell_types());
		
		foreach ($cell_types as $index => $cell_type) {
			if ($cell_type == 'cell-post-content' && !$wpddl_features->is_feature('post-content-cell')) {
				unset($cell_types[$index]);
			}
			if ($cell_type == 'post-loop-cell' && !$wpddl_features->is_feature('post-loop-cell')) {
				unset($cell_types[$index]);
			}

		}
		return $cell_types;
	}

	function get_cell_info($cell_type) {
		static $cell_info_cache = array();

		if (!isset($cell_info_cache[$cell_type])) {
			$template['icon-css'] = '';
			$template['name'] = '';
			$template['description'] = '';
			$template['button-text'] = '';
			$template['dialog-template'] = '';
			$template['allow-multiple'] = true;

			if (isset($this->cell_factory[$cell_type])) {
				$cell_info_cache[$cell_type] = $this->cell_factory[$cell_type]->get_cell_info($template);
			} else {
				$cell_info_cache[$cell_type] = $this->registed_cells->get_cell_info($cell_type);
			}

			if (!isset($cell_info_cache[$cell_type]['category'])) {
				$cell_info_cache[$cell_type]['category'] = __('Text and Media', 'ddl-layouts');
				$cell_info_cache[$cell_type]['category-icon-css'] = 'icon-cog';
				$cell_info_cache[$cell_type]['category-icon-url'] = '';
			}

			if (!isset($cell_info_cache[$cell_type]['icon-url'])) {
				$cell_info_cache[$cell_type]['icon-url'] = '';
			}

			if (!$cell_info_cache[$cell_type]['icon-css'] && !$cell_info_cache[$cell_type]['icon-url']) {
				$cell_info_cache[$cell_type]['icon-css'] = 'icon-circle-blank';
			}

		}

		return $cell_info_cache[$cell_type];
	}

	function get_cell_categories() {
		$categories = array();

		foreach ($this->get_cell_types() as $cell_type) {
			$cell_info = $this->get_cell_info($cell_type);

			if (!isset($cell_info['category-icon-css'])) {
				$cell_info['category-icon-css'] = '';
			}
			
			if (!isset($cell_info['category-icon-url'])) {
				$cell_info['category-icon-url'] = '';
			}
			
			if (!$cell_info['category-icon-css'] && !$cell_info['category-icon-url']) {
				$cell_info['category-icon-css'] = 'icon-cog';
			}

			$categories[$cell_info['category']] = array('name' => $cell_info['category'],
				'icon-css' => $cell_info['category-icon-css'],
				'icon-url' => $cell_info['category-icon-url']);
		}

		return $categories;
	}


	function wpddl_frontent_styles($post_id){
		$styles = get_post_meta($post_id, 'dd_layouts_styles', true);
		echo $styles;
	}

	function wpddl_frontend_header_init(){
		$this->header_added = TRUE;

        $queried_object = $this->get_queried_object();
        $post = $this->get_query_post_if_any( $queried_object);

        if( null === $post ) return;
		// if there is a css enqueue it here
		$post_id = $post->ID;

		$layout_selected = get_post_meta($post_id, '_layouts_template', true);

		if( $layout_selected > 0 ){
			$header_content = get_post_meta($layout_selected, 'dd_layouts_header');
			echo isset($header_content[0]) ? $header_content[0] : '';
		}
	}

	function add_layouts_admin_menu() {

		add_menu_page('Layouts', 'Layouts', 'administrator', 'dd_layouts', array($this, 'dd_layouts_list'), 'none' );

		if ($this->layouts_editor_page) {
			add_submenu_page('dd_layouts', __('Edit layout', 'ddl-layouts'), __('Edit layout', 'ddl-layouts'), 'manage_options', 'dd_layouts_edit', array($this, 'dd_layouts_edit'));
		}

		add_submenu_page('dd_layouts', __('Add new layout', 'ddl-layouts'), __('Add new layout', 'ddl-layouts'), 'manage_options', 'admin.php?page=dd_layouts&amp;new_layout=true');
		add_submenu_page('dd_layouts', __('Help', 'ddl-layouts'), __('Tutorial Videos', 'ddl-layouts'), 'manage_options', 'dd_tutorial_videos', array($this, 'dd_layouts_help'));
		add_submenu_page('dd_layouts', __('Settings', 'ddl-layouts'), __('Settings', 'ddl-layouts'), 'manage_options', 'dd_layouts_settings', array($this, 'dd_layouts_settings'));
	}

	function dd_layouts_list(){
		$this->listing_page->init();
	}

	function dd_layouts_edit(){
		new WPDD_EDITOR();
	}

	function dd_layouts_settings(){
		include WPDDL_GUI_ABSPATH . 'templates/layout_settings.tpl.php';
	}

	function dd_layouts_help(){
		include WPDDL_GUI_ABSPATH . 'templates/layout_help.tpl.php';
		include WPDDL_GUI_ABSPATH . 'dialogs/dialog_video_player.tpl.php';
	}

	function wpddl_edit_template_options(){
		global $post;

		if( !is_object($post) ) return;

		$post_object = get_post_type_object($post->post_type);
		if ($post_object->publicly_queryable || $post_object->public) {
			add_meta_box('wpddl_template', __('Layout', 'wpdd-layout'), array($this,'meta_box'), $post->post_type, 'side', 'high');
		}
	}

	function wpddl_save_post($pidd){
		if( isset($_POST['layouts_template']) ){

			$layout_selected = $_POST['layouts_template'];

			if( isset($_POST['page_template']) && $this->template_have_layout($_POST['page_template'] ) === false )
			{
				update_post_meta($pidd, '_layouts_template', 0);
			}
			else
			{

				update_post_meta($pidd, '_layouts_template', $layout_selected);
			}

		}
		else
		{
			// when we set a non-layout template after a layout has been set
			$meta = get_post_meta($pidd, '_layouts_template', true);

			if( $meta )
			{
				delete_post_meta( $pidd, '_layouts_template', $meta );
			}
		}
	}

	function meta_box($post) {

		global $wpdb, $WP_layouts, $sitepress;
		global $wpddl_features;

		$layout_tempates_available = $wpdb->get_results("SELECT ID, post_name, post_title FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_status in ('publish')");

		if (isset($_GET['post'])) {
			$template_selected = get_post_meta($_GET['post'], '_layouts_template', true);
		} else {
			$template_selected = '';
		}
		
		if ($post->post_type != 'page') {
			$none = new stdClass();
			$none->ID = 0;
			$none->post_name = 0;
			$none->post_title = __('None', 'ddl-layouts');
			
			array_unshift($layout_tempates_available, $none);
		}

		$post_type_obj = get_post_type_object( $post->post_type );
		
		?>

		<div class="js-dd-layout-selector">

			<script type="text/javascript">
				var ddl_old_template_text = "<?php _e('Template', 'ddl-layouts'); ?>";
			</script>

			
			<?php if($post->post_type == 'page'): ?>
				<p>
					<i class="icon-layouts ont-icon-24 ont-color-orange"></i> <strong><?php _e('Template and Layout', 'ddl-layouts') ?></strong>
				</p>
			<?php endif; ?>
			<p>


					<?php

					if (isset($sitepress) && function_exists('icl_object_id')) {
						$template_selected = icl_object_id($template_selected, 'layouts-template', true);
					}


					$template_option = $this->get_option( 'templates' );

					$post_type_theme = $this->post_types_manager->get_layout_template_for_post_type( $post->post_type );

					$theme_template = $post_type_theme == 'default' ? basename( get_page_template() ) : $post_type_theme;

					$post_type_layout = $this->post_types_manager->get_layout_to_type_object($post->post_type);

					?>

					<input type="hidden" name="ddl-namespace-post-type-tpl" value="<?php echo $post_type_theme == 'default' ? 'default' : $theme_template;?>" class="js-ddl-namespace-post-type-tpl" />
					<select name="layouts_template" id="js-layout-template-name">
					<?php
					if (isset($template_option[$theme_template])) {
						$theme_default_layout = $template_option[$theme_template];
					} else {
						$theme_default_layout = '';
					}

					foreach ($layout_tempates_available as $template) {

						$layout = self::get_layout_settings($template->ID, true);
                        $has_loop = is_object($layout) && property_exists($layout, 'has_loop') ? $layout->has_loop : false;

						$supported = true;
						$warning = '';
						if ($layout && property_exists( $layout, 'type') && $layout->type == 'fixed' && !$wpddl_features->is_feature('fixed-layout')) {
							$warning = __("This layout is a fixed layout. The current theme doesn't support fixed layouts and might not display correctly", 'ddl-layouts');
						}
						if ($layout && property_exists( $layout, 'type') && $layout->type == 'fluid' && !$wpddl_features->is_feature('fluid-layout')) {
							$warning = __("This layout is a fluid layout. The current theme doesn't support fluid layouts and might not display correctly", 'ddl-layouts');
						}
						if ($layout && isset($layout->has_child) && ($layout->has_child === 'true' || $layout->has_child === true)) {

							$supported = false;
						}

						if ($supported) {

							$force_layout = ' data-force-layout="false"';
							
							if ($template_selected == $template->post_name){

								$selected = ' selected="selected"';
							} 
							// for new posts let's assign the Layout if there's one
							elseif( !isset( $_GET['post'] ) && is_object( $post_type_layout ) && property_exists( $post_type_layout, 'layout_id') && (int)$template->ID === (int)$post_type_layout->layout_id ) {

								$selected = ' selected="selected"';
								$force_layout = ' data-force-layout="true"';
							} else{

								$selected = '';
							}

							$title = $template->post_title;
							if ($title == '') {
								$title = $template->post_name;
							}

							if ( $template->post_name == $theme_default_layout && $post->post_type == 'page' ) {
								$title .= __(' - Template default', 'ddl-layouts');
							}

                            $data_object = array(
                                'layout_has_loop' => $has_loop,
                                'post_type' => $post->post_type
                            );

							?>
							<option data-object="<?php echo htmlspecialchars( json_encode( $data_object ) ); ?>" value="<?php echo $template->post_name; ?>"<?php echo $selected . $force_layout; ?> data-id="<?php echo $template->ID; ?>" data-ddl-warning="<?php echo $warning; ?>"><?php echo $title; ?></option>
						<?php
						}
					}
					?>

				</select>
				<?php if($post->post_type == 'page'): ?>
					<select name="combined_layouts_template" id="js-combined-layout-template-name">

				</select>
				<?php endif; ?>
				<br />
				<a data-href="<?php echo admin_url() . 'admin.php?page=dd_layouts_edit&amp;action=edit&layout_id='; ?>" class="edit-layout-template js-edit-layout-template"><?php _e('Edit this layout', 'ddl-layouts'); ?></a>
				
			</p>
	
			<p class="toolset-alert toolset-alert-warning js-layout-support-warning" style="display:none">
			</p>

			<?php wp_nonce_field('wp_nonce_ddl_dismiss', 'wp_nonce_ddl_dismiss'); ?>
		</div>

		
		<?php if( $this->post_types_manager->check_layout_template_page_exists( $post_type_obj ) === false ): ?>
		
			<p class=" toolset-alert toolset-alert-warning js-layout-support-missing">
				<?php echo sprintf(__("A template file that supports layouts is not available for the %s post type.", 'ddl-layouts'), '<strong>"' . $post_type_obj->labels->singular_name . '"</strong>') ?><br>
				<?php printf(__('Please check %shere%s to see how to set one up.', 'ddl-layouts'), '<a href="' . WPDLL_LEARN_ABOUT_SETTING_UP_TEMPLATE . '" target="_blank">', '</a>'); ?>
			</p>
		<?php endif; ?>
		

	<?php
	}


	function wpddl_register_post_type_for_layouts() {
		$labels = array(
			'name' => _x('Layouts', 'post type general name'),
			'singular_name' => _x('Layout', 'post type singular name'),
			'add_new' => _x('Add New', 'book'),
			'add_new_item' => __('Add New Layout', 'ddl-layouts'),
			'edit_item' => __('Edit Layout', 'ddl-layouts'),
			'new_item' => __('New Layout', 'ddl-layouts'),
			'view_item' => __('View Layouts', 'ddl-layouts'),
			'search_items' => __('Search Layouts', 'ddl-layouts'),
			'not_found' =>  __('No layouts found', 'ddl-layouts'),
			'not_found_in_trash' => __('No layouts found in Trash', 'ddl-layouts'),
			'parent_item_colon' => '',
			'menu_name' => 'Layouts'
		);
		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'query_var' => false,
			'rewrite' => false,
			'can_export' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 90,
			'supports' => array('title')
		);
		register_post_type('dd_layouts',$args);
	}

	function does_layout_with_this_name_exist($layout_name) {
		global $wpdb;

		$post_id =  $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_title=%s", $layout_name));
		return $post_id > 0;
	}

	function create_layout_callback() {
		// Clear any errors that may have been rendered that we don't have control of.		
		if ( ob_get_length() > 0 ) ob_clean();

		$nonce = $_POST["wpnonce"];
		if (! wp_verify_nonce( $nonce, 'wp_nonce_create_layout' ) ) {
			$result = array('error' => 'error',
				'error_message' => __('Security check failed', 'ddl-layouts'));
		} else {

			// Check for duplicate layout name.

			$layout_name = str_replace('\\\\', '##DDL-SLASH##', $_POST['title']);
			$layout_name = stripslashes_deep($layout_name);
			$layout_name = str_replace('##DDL-SLASH##', '\\\\', $layout_name);
			if ($this->does_layout_with_this_name_exist($layout_name)) {
				$result = array('error' => 'error',
					'error_message' => __('A layout with this name already exists. Please use a different name.', 'ddl-layouts'));

			} else {

				$parent_post_name = '';
				if ($_POST['layout_parent']) {
					global $wpdb;

					$parent_post_name =  $wpdb->get_var($wpdb->prepare("SELECT post_name FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND ID=%d", $_POST['layout_parent']));
				}

				if (isset( $_POST['layout_preset']) && $_POST['layout_preset'] ) {
					$layout = $this->load_layout($_POST['layout_preset'], $_POST['layout_type']);
				} else {
					$layout = $this->create_layout($_POST['columns'], $_POST['layout_type']);
				}

				$layout['type'] = $_POST['layout_type'];
				$layout['cssframework'] = $this->get_css_framework();
				$layout['template'] = '';
				$layout['parent'] = $parent_post_name;
				$layout['name'] = $layout_name;

				$layout_json = $this->json_encode($layout);

				$postarr = array(
					'post_title'	=> $layout_name,
					'post_content'	=> '',
					'post_status'	=> 'publish',
					'post_type'	=> 'dd_layouts'
				);
				$post_id = wp_insert_post($postarr);

				if( isset($_POST['post_types']) && !empty($_POST['post_types']) && is_array( $_POST['post_types'] ) )
				{
                    $post_types = count( $_POST['post_types'] ) === 0 ? array() : array_unique( $_POST['post_types'] );
					$this->post_types_manager->handle_post_type_data_save( array( "layout_".$post_id => $post_types ) );
				}

				update_post_meta( $post_id, 'dd_layouts_settings',  $layout_json);
				$result['id'] = $post_id;
			}

		}
		die( $this->json_encode($result) );
	}

	/*
	 * wrapper function for json encode to support unicode character with a fallback for php < 5.3
	 * @param: $array:array
	 * @return: json_string:string
	 */
	static function json_encode( $array )
	{
		$array = self::json_encode_string($array);
		
		// php > 5.3 do not escape utf-8 characters using native constant argument
		if( defined('JSON_UNESCAPED_UNICODE') )
		{
			return json_encode($array, JSON_UNESCAPED_UNICODE );
		}
		// fallback for php < 5.3 to support unicode characters in json string
		else
		{
			if (function_exists('mb_decode_numericentity')) {
				return self::json_encode_unescaped_unicode( $array );
			} else {
				return json_encode( $array );
			}
		}
	}
	
	static function json_encode_string ($data) {
		foreach ($data as $key => $data_value) {
			if (is_string($data_value)) {
				$data[$key] = str_replace('"', '\"', $data_value);
			} else if (is_array($data_value)) {
				$data[$key] = self::json_encode_string($data_value);
			} else if (is_object($data_value)) {
				$data[$key] = self::json_encode_string((array) $data_value);
			}
		}
		
		return $data;
		
	}

	/**
	 * @param $arr
	 * @return string
	 * courtesy from: http://www.php.net/manual/ru/function.json-encode.php#105789
	 */
	public static function json_encode_unescaped_unicode($arr)
	{

		array_walk_recursive($arr, 'ddl_json_unescaped_unicode_walk_callback' );

		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}

	function create_layout($width, $type) {
		$layout = new WPDD_layout($width);
		$row = new WPDD_layout_row('1', '', '', $type);
		for ($i = 0; $i < $width; $i++) {
			$cell = new WPDD_layout_spacer('', 1);
			$row->add_cell($cell);
		}
		$layout->add_row($row);

		$layout = $layout->get_as_array();
		$layout['width'] = $width;

		return $layout;
	}

	function ddl_dismiss_template_message() {
		$nonce = $_POST["wpnonce"];
		if ( wp_verify_nonce( $nonce, 'wp_nonce_ddl_dismiss' ) ) {
			$this->save_option(array('dismiss_layout_message' => true));
		}

		die();
	}

	public function load_layout($preset_file, $layout_type = null)
	{

		$layout_json = file_get_contents($preset_file);

		$layout = json_decode(str_replace('\\\"', '\"' , $layout_json), true);

		if ($layout_type) {
			$layout['type'] = $layout_type;
			for ($i = 0; $i < sizeof($layout['Rows']); $i++) {
				$layout['Rows'][$i]['layout_type'] = $layout_type;
				$layout['Rows'][$i]['cssClass'] = 'row-' . $layout_type;

			}
		}

		return $layout;

	}

	function load_frontend_js() {
		$this->enqueue_scripts('ddl-layouts-frontend');
		$this->localize_script('ddl-layouts-frontend', 'DDLayout_fe_settings', array(
				'DDL_JS' => array(
					'css_framework' => $this->get_css_framework(),
					'DEBUG' => WPDDL_DEBUG,
				)
			)
		);
	}

	function load_frontend_css()
	{
		if( $this->get_css_framework() == 'bootstrap-3' )
		{
			$this->enqueue_styles('menu-cells-front-end');
		}
        $this->enqueue_styles('ddl-front-end');
	}

	function before_header_hook(){
		if (isset($_GET['layout_id'])) {
			$layout_selected = $_GET['layout_id'];
		} else {
			$post_id = get_the_ID();
			$layout_selected = get_post_meta($post_id, '_layouts_template', true);
		}
		if($layout_selected>0){
			$layout_content = get_post_meta($layout_selected, 'dd_layouts_settings');

			if (sizeof($layout_content) > 0) {
				$test = new WPDD_json2layout();
				$layout = $test->json_decode($layout_content[0]);
				$manager = new WPDD_layout_render_manager($layout);
				$renderer = $manager->get_renderer( );
				$html = $renderer->render_to_html();

				echo $html;
			}
		}
	}

	public static function get_layout_settings( $post_id, $as_array = false ) {
		static $layouts_raw = array();
		static $layouts_decoded = array();

		if (!isset($layouts_raw[$post_id])) {
			$layouts_raw[$post_id] = get_post_meta($post_id, 'dd_layouts_settings', true);
		}

		if ($as_array && !isset($layouts_decoded[$post_id])) {

			if ($layouts_raw[$post_id]) {
				$layouts_decoded[$post_id] = json_decode(  $layouts_raw[$post_id]  );
			} else {
				$layouts_decoded[$post_id] = null;
			}
		}

		if ($as_array) {
			return $layouts_decoded[$post_id];
		} else {
			return $layouts_raw[$post_id];
		}
	}

    // I added this 'cause in ajax calls after saving the static property of self::get_layout_settings
    // is not updated so the settings you get are outdated
    public static function get_layout_settings_raw_not_cached( $layout_id ) {
        $settings = get_post_meta($layout_id, 'dd_layouts_settings', true);
        return json_decode( $settings );
    }

	public static function save_layout_settings( $post_id, $settings )
	{
		if (is_string($settings)) {
			$settings = self::json_encode( json_decode($settings, true) );
		} else if (is_array($settings) || is_object($settings)) {
			$settings = self::json_encode( (array)$settings );
		}
		
		static $layouts_raw = array();

		if (!isset($layouts_raw[$post_id])) {
			$layouts_raw[$post_id] = get_post_meta($post_id, 'dd_layouts_settings', true);
		}

		update_post_meta( $post_id, 'dd_layouts_settings', $settings, $layouts_raw[$post_id] );
	}

	public static function get_layout_parent($id, $layout_search = false )
	{
		global $wpdb;

		$layout = $layout_search ? $layout_search : self::get_layout_settings($id, true);
		$parent = $layout->parent;

		if (!empty( $parent )) {
			$parent =  $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_name=%s", $parent));
		}
		if (!$parent) {
			$parent = 0;
		}

		return $parent;
	}

	function get_layout ( $layout_name ) {
		global $wpdb;

		$layout = null;
		$result =  $wpdb->get_row($wpdb->prepare("SELECT ID, post_name FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_name=%s", $layout_name));
		if ($result) {
			$layout_json = self::get_layout_settings($result->ID);

			$json_parser = new WPDD_json2layout();
			$layout = $json_parser->json_decode($layout_json);
			$layout->set_post_id($result->ID);
			$layout->set_post_slug($result->post_name);
		}

		return $layout;
	}

	function get_layout_from_id ( $id ) {
		global $wpdb;

		$layout = null;
		$result =  $wpdb->get_row($wpdb->prepare("SELECT ID, post_name FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND ID=%d AND post_status = 'publish'", $id));
		if ($result) {
			$layout_json = self::get_layout_settings($result->ID);

			$json_parser = new WPDD_json2layout();
			$layout = $json_parser->json_decode($layout_json);
			$layout->set_post_id($result->ID);
			$layout->set_post_slug($result->post_name);
		}

		return $layout;

	}

	function get_available_parent_layouts () {
		static $layouts = null;

		if ($layouts === null) {
			global $wpdb;

			$layouts = array();
			$post_ids =  $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts'");
			foreach ($post_ids as $post_id) {
				$layout = self::get_layout_settings($post_id, true);
				if ($layout && isset($layout->has_child) && ($layout->has_child === 'true' || $layout->has_child === true)) {
					$parent = $this->get_layout_parent($post_id);
					if (!isset($layouts[$parent])) {
						$layouts[$parent] = array();
					}
					$layouts[$parent][] = $post_id;
				}
			}
		}

		return $layouts;
	}
	
	function get_layout_list() {
		global $wpdb;
		
		$results =  $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts'");
		
		return $results;
	}

	function save_option($option) {
		$options = get_option('ddlayout_settings');
		if (!$options) {
			$options = array();
		}
		$options = array_merge($options, $option);
		update_option('ddlayout_settings', $options);
	}

	function get_option($option, $default = false) {
		$options = get_option('ddlayout_settings');
		if ($options && isset($options[$option])) {
			return $options[$option];
		} else {
			return $default;
		}
	}

	function import_layouts_from_theme($source_dir) {
		global $wpdb;

		if (is_dir($source_dir)) {

			$layouts = glob($source_dir . '/*.ddl');

			foreach ($layouts as $layout) {
				$file_details = pathinfo($layout);
				$layout_name = $file_details['filename'];

				$layout_json = file_get_contents($layout);

				$layout = json_decode(str_replace('\\\"', '\"' , $layout_json));

				$id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_name=%s", $layout_name));

				if (!$id) {

					$postarr = array(
						'post_title'	=> $layout->name,
						'post_name'		=> $layout_name,
						'post_content'	=> '',
						'post_status'	=> 'publish',
						'post_type'	=> 'dd_layouts'
					);

					$post_id = wp_insert_post($postarr);

					$layout_json = addslashes( $layout_json );

					update_post_meta( $post_id, 'dd_layouts_settings',  $layout_json);

				}

			}

			$this->css_manager->import_css_from_theme( $source_dir );
		}
	}

	function record_render_error($data) {
		if ( !in_array($data, $this->render_errors) ) {
			$this->render_errors[] = $data;
		}
	}

	function get_render_errors() {
		return $this->render_errors;
	}

	function layout_type_selector($name) {
		global $wpddl_features;

		?>


		<?php

	}

	function get_layout_css()
	{
		return $this->css_manager->get_layouts_css();
	}

	function get_where_used( $layout_id, $slug = false, $group = false, $posts_per_page = -1 )
	{
		// Get the posts where this is used.
		$layout = $this->get_layout_from_id( $layout_id );

		if( is_object( $layout ) === false && method_exists($layout,'get_post_slug') === false ) return;

		$args = array(
			'posts_per_page' => $posts_per_page,
			'post_type' => 'any',
			'meta_query' => array (
				array (
					'key' => '_layouts_template',
					'value' => $slug ? $slug : $layout->get_post_slug(),
					'compare' => '=',
				)
			) );

		$new_query = new WP_Query( $args );

        if( $group === true )
        {
            add_filter('posts_orderby', array(&$this, 'order_by_post_type'), 10, 2);
            $new_query->group_posts_by_type = $group;
        }

		$posts = $new_query->get_posts();

		return $posts;
	}

    function order_by_post_type($orderby, $query) {
        global $wpdb;
        if ( property_exists($query, 'group_posts_by_type') &&  $query->group_posts_by_type === true) {
            unset( $query->group_posts_by_type );
            $orderby = $wpdb->posts . '.post_type ASC';
        }
        // provide a default fallback return if the above condition is not true
        return $orderby;
    }

	public static function get_layout_children($id)
	{
		global $wpdb;

		if (!$id) return null;

		$layout = self::get_layout_settings($_GET['layout_id'], true);

		$children = array();

		if ($layout && isset($layout->has_child) && ($layout->has_child === 'true' || $layout->has_child === true)) {
			$layout_slug = $layout->slug;

			$post_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts'");
			foreach ($post_ids as $post_id) {
				$layout = self::get_layout_settings($post_id, true);
				if ($layout) {
					if ( property_exists ( $layout , 'parent' ) && $layout->parent == $layout_slug) {
						$children[] = $post_id;
					}
				}
			}
		}

		return $children;
	}

    function get_layout_renderer( $layout, $args )
    {
        $manager = new WPDD_layout_render_manager($layout );
        $renderer = $manager->get_renderer( );
        // set properties  and callbacks dynamically to current renderer
        if( is_array($args) && count($args) > 0 )
        {
            $renderer->set_layout_arguments( $args );
        }
        return $renderer;
    }

    function get_query_post_if_any( $queried_object)
    {
        return 'object' === gettype( $queried_object ) && get_class( $queried_object ) === 'WP_Post' ? $queried_object : null;
    }

    function get_queried_object()
    {
        global $wp_query;

        $queried_object = $wp_query->get_queried_object();

        return $queried_object;
    }

    function get_layout_id_for_render( $layout )
    {
        global $wpdb;

        $id = 0;

        if ($layout) {

            $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_name=%s", $layout));

            if (!$id) {
                // try the id.
                $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND ID=%d", (int)$layout));
            }

            if (!$id) {
                // try the post title
                $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_title=%s", $layout));
            }
        }

        $queried_object = $this->get_queried_object();

        $post = $this->get_query_post_if_any( $queried_object);

        if( $post !== null )
        {

            $post_id = $post->ID;

            $layout_selected = get_post_meta( $post_id, '_layouts_template', true );

            if ($layout_selected) {

                $id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type='dd_layouts' AND post_name=%s", $layout_selected));

                $option = $this->post_types_manager->get_layout_to_type_object($post->post_type);

                if( is_object( $option ) && property_exists( $option, 'layout_id') && (int) $option->layout_id === (int) $id )
                {
                    $id = $option->layout_id;
                }
            }
        }
        else if( $post === null && is_front_page() && is_home() && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_BLOG) )
        {

            $id = (int) $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_BLOG);
        }
        elseif ( $post === null && is_post_type_archive() ) {

            $post_type_object = $queried_object;
            if ( $post_type_object && property_exists( $post_type_object, 'public' ) && $post_type_object->public && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_TYPES_PREFIX.$post_type_object->name) ) {
                $id = $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_TYPES_PREFIX.$post_type_object->name);
            }
        }
        elseif ( $post === null && is_archive() && ( is_tax() || is_category() || is_tag() ) ) {

                $term = $queried_object;
                if ( $term && property_exists( $term, 'taxonomy' ) && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_TAXONOMY_PREFIX.$term->taxonomy) ) {
                    $id = $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_TAXONOMY_PREFIX.$term->taxonomy);
                }

        }
        // Check other archives
        elseif ( $post === null && is_search()  && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_SEARCH) ) {

            $id = $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_SEARCH);
        }
        elseif ( $post === null && is_author() && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_AUTHOR ) ) {

            $id = $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_AUTHOR );
        }
        elseif ( $post === null && is_year() && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_YEAR) ) {

            $id = $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_YEAR);
        }
        elseif ( $post === null && is_month() && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_MONTH) ) {

            $id = $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_MONTH);
        }
        elseif ( $post === null && is_day() && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_DAY) ) {

            $id = $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_DAY);
        }
        elseif( $post === null && is_404() && $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_404 ) )
        {

            $id = $this->layout_post_loop_cell_manager->get_option( WPDD_layout_post_loop_cell_manager::OPTION_404 );
        }

        return apply_filters('get_layout_id_for_render', $id, $layout );
    }

    function get_layout_content_for_render( $layout, $args )
    {
        $id = $this->get_layout_id_for_render($layout);

        $content = '';

        if ($id) {

            // Check for preview mode
            $old_id = $id;
            if (isset($_GET['layout_id'])) {
                $id = $_GET['layout_id'];
            }

            $layout = $this->get_layout_from_id($id);
            if (!$layout && isset($_GET['layout_id'])) {
                if ($id != $old_id) {
                    $layout = $this->get_layout_from_id($old_id);
                }
            }
            if ($layout) {

                $renderer = $this->get_layout_renderer( $layout, $args );
                //$renderer = new WPDD_layout_render($layout);
                $content = $renderer->render( );

                $render_errors = $this->get_render_errors();
                if (sizeof($render_errors)) {
                    $content .= '<p class="alert alert-error"><strong>' . __('There were errors while rendering this layout.', 'ddl-layouts') . '</strong></p>';
                    foreach($render_errors as $error) {
                        $content .= '<p class="alert alert-error">' . $error . '</p>';
                    }
                }
            }
        } else {
            if (!$layout) {
                $content = '<p>' . __('You need to select a layout for this page. The layout selection is available in the page editor.', 'ddl-layouts') . '</p>';
            }
        }

        return apply_filters('get_layout_content_for_render', $content, $this, $layout, $args );
    }

    public static function flattenArray( $array ){
        $ret_array = array();

        if( is_array($array) )
        {
            foreach(new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value)
            {
                $ret_array[] = $value;
            }
        }
        else
        {
            $ret_array = array('error' => __( sprintf('Argument should be an array %s', __METHOD__), 'wpv-views') );
        }

        return $ret_array;
    }
	
	private function fix_up_views_slugs () {
		global $wpdb;
		
		$fixed = $this->get_option('views_and_template_slugs_fixed_0.9.2');
		
		if (!$fixed) {
			
			// From 0.9.2 we're using the View ID instead of the slug
			// We need to check all layouts and update them as required.
			$layout_tempates_available = $wpdb->get_results("SELECT ID, post_name, post_title FROM {$wpdb->posts} WHERE post_type='dd_layouts'");
			foreach ($layout_tempates_available as $template) {

				$layout = self::get_layout_settings($template->ID);
				$found = false;
				
				if (preg_match_all('/"ddl_layout_view_slug":"(.*?)"/', $layout, $matches)) {
					$found = true;
					for ($i = 0; $i < sizeof($matches[0]); $i++) {
						$slug = $matches[1][$i];
						$id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = '%s' AND post_type='view'", $slug) );
						if ($id > 0) {
							$new = '"ddl_layout_view_id":"' . $id . '"';
							$layout = str_replace($matches[0][$i], $new, $layout);
						}
					}
					
				}

				if (preg_match_all('/"view_template":"(.*?)"/', $layout, $matches)) {
					$found = true;
					for ($i = 0; $i < sizeof($matches[0]); $i++) {
						$slug = $matches[1][$i];
						$id = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = '%s' AND post_type='view-template'", $slug) );
						if ($id > 0) {
							$new = '"ddl_view_template_id":"' . $id . '"';
							$layout = str_replace($matches[0][$i], $new, $layout);
						}
					}
					
				}
				
				if ($found) {
					self::save_layout_settings($template->ID, $layout);
				}
				
			}
			
			$this->save_option(array('views_and_template_slugs_fixed_0.9.2' => true));
			
		}
		
	}

}

/*
 * Helper function to json_encode with UTF-8 support for php < 5.3
 */
function ddl_json_unescaped_unicode_walk_callback (&$item, $key){
	if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
}