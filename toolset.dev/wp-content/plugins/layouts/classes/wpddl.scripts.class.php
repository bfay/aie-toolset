<?php

class WPDDL_style
{

	public function __construct($handle, $path = 'wordpress_default', $deps = array(), $ver = false, $media = 'screen')
	{
		$this->handle = $handle;
		$this->path = $path;
		$this->deps = $deps;
		$this->ver = $ver;
		$this->media = $media;

		if ($this->is_registered() === false && $this->path != 'wordpress_default') {
			wp_register_style($this->handle, $this->path, $this->deps, $this->ver, $this->media );
		}

	}

	public function enqueue()
	{
		if ($this->is_enqueued() === false) {
			wp_enqueue_style($this->handle);
		}
	}

	public function deregister()
	{
		if ($this->is_registered() !== false) wp_deregister_style($this->handle);
	}


	private function is_registered()
	{
		return wp_style_is($this->handle, 'registered');
	}

	private function is_enqueued()
	{
		return wp_style_is($this->handle, 'enqueued');
	}
}

class WPDDL_script
{
	public function __construct($handle, $path = 'wordpress_default', $deps = array(), $ver = false, $in_footer = false)
	{
		$this->handle = $handle;
		$this->path = $path;
		$this->deps = $deps;
		$this->ver = $ver;
		$this->in_footer = $in_footer;

		if ($this->is_registered() === false && $this->path != 'wordpress_default') {
			wp_register_script($this->handle, $this->path, $this->deps, $this->ver, $this->in_footer);
		}
	}

	public function enqueue()
	{

		if ($this->is_enqueued() === false) {
			wp_enqueue_script($this->handle);
		}
	}

	public function localize($object, $args)
	{

		if ($this->is_registered() && $this->is_enqueued()) {
			wp_localize_script($this->handle, $object, $args);
		}
	}

	public function deregister()
	{
		if ($this->is_registered() !== false) wp_deregister_script($this->handle);
	}

	private function is_registered()
	{
		return wp_script_is($this->handle, 'registered');
	}

	private function is_enqueued()
	{
		return wp_script_is($this->handle, 'enqueued');
	}
}

class WPDDL_scripts_manager
{

	private $styles = array();
	private $scripts = array();

	public function __construct()
	{
		add_action( 'init', array($this, 'init') );
		//be
		add_action( 'admin_enqueue_scripts', array($this, 'get_rid_of_default_scripts') );
		add_action( 'admin_enqueue_scripts', array($this, 'get_rid_of_default_styles') );
		//fe
		add_action( 'wp_enqueue_scripts', array($this, 'get_rid_of_default_scripts') );
		add_action( 'wp_enqueue_scripts', array($this, 'get_rid_of_default_styles') );
	}

	public function init()
	{
		$this->__initialize_styles();
		$this->__initialize_scripts();
	}

	/*
	 * @return void
	 * pushes to our scripts array other scripts so we can enqueue using our methods
	 */
	public function get_rid_of_default_scripts()
	{
		global $wp_scripts;
		if( is_array($wp_scripts->registered) )
		{
			foreach ($wp_scripts->registered as $registered) {
				$this->scripts[$registered->handle] = new WPDDL_script($registered->handle);
			}
		}
	}

	/*
	 * @return void
	 * pushes to our scripts array other scripts so we can enqueue using our methods
	 */
	public function get_rid_of_default_styles()
	{
		global $wp_styles;

		if( is_array($wp_styles->registered) )
		{
			foreach ($wp_styles->registered as $registered) {
				$this->styles[$registered->handle] = new WPDDL_style($registered->handle);
			}
		}
	}

	private function __initialize_styles()
	{
		$this->styles['toolset-select2-css'] = new WPDDL_style('toolset-select2-css', WPDDL_RES_RELPATH . '/css/external_libraries/select2/select2.css');
		$this->styles['layouts-select2-overrides-css'] = new WPDDL_style('layouts-select2-overrides-css', WPDDL_RES_RELPATH . '/css/external_libraries/select2/select2-overrides.css');
		$this->styles['ddl_post_edit_page_css'] = new WPDDL_style('wp-layouts-pages', WPDDL_RES_RELPATH . '/css/dd-general.css');
		$this->styles['progress-bar-css'] = new WPDDL_style('progress-bar-css', WPDDL_RES_RELPATH . '/css/progress.css');
		$this->styles['toolset-colorbox'] = new WPDDL_style('toolset-colorbox', WPDDL_RES_RELPATH . '/css/colorbox.css');
		$this->styles['toolset-font-awesome'] = new WPDDL_style('toolset-font-awesome', WPDDL_RES_RELPATH . '/css/external_libraries/font-awesome/css/font-awesome.min.css');
		$this->styles['toolset-notifications-css'] = new WPDDL_style('toolset-notifications-css', WPDDL_RES_RELPATH . '/css/notifications.css');
		$this->styles['toolset-utils'] = new WPDDL_style('toolset-utils', WPDDL_RES_RELPATH . '/css/notifications.css');
		$this->styles['layouts-global-css'] = new WPDDL_style('layouts-global-css', WPDDL_GUI_RELPATH . 'global/css/dd-general.css');
		$this->styles['wp-editor-layouts-css'] = new WPDDL_style('wp-editor-layouts-css', WPDDL_GUI_RELPATH . 'editor/css/editor.css');
		$this->styles['wp-layouts-pages'] = new WPDDL_style('wp-layouts-pages', WPDDL_RES_RELPATH . '/css/dd-general.css');
		$this->styles['toolset-common'] = new WPDDL_style('toolset-common', WPDDL_TOOLSET_COMMON_RELPATH. '/res/css/toolset-common.css');

		# dialogs css
		$this->styles['layouts-meta-html-codemirror-css'] = new WPDDL_style('layouts-meta-html-codemirror-css', WPDDL_RES_RELPATH . '/codemirror/lib/codemirror.css');
		$this->styles['layouts-meta-html-codemirror-css-hint-css'] = new WPDDL_style('layouts-meta-html-codemirror-css-hint-css', WPDDL_RES_RELPATH . '/codemirror/addon/hint/show-hint.css');

		$this->styles['wp-layouts-jquery-ui-slider'] = new WPDDL_style('wp-layouts-jquery-ui-slider', WPDDL_GUI_RELPATH . 'dialogs/css/jquery-ui-slider.css');
		$this->styles['ddl-dialogs-forms-css'] = new WPDDL_style('ddl-dialogs-forms-css', WPDDL_RES_RELPATH  . '/css/dd-dialogs-forms.css');
		if (defined('WPV_URL_EMBEDDED')) {
			$this->styles['views-dialogs-css'] = new WPDDL_style('views-dialogs-css', WPV_URL_EMBEDDED . '/res/css/dialogs.css', array(), WPV_VERSION);
		}
		$this->styles['ddl-dialogs-general-css'] = new WPDDL_style('ddl-dialogs-general-css', WPDDL_RES_RELPATH . "/css/dd-dialogs-general.css");
		$this->styles['ddl-dialogs-css'] = new WPDDL_style('ddl-dialogs-css', WPDDL_RES_RELPATH . "/css/dd-dialogs.css", array('ddl-dialogs-general-css'));

		# common
		if (defined('WPV_URL_EMBEDDED')) {
			$this->styles['views-pagination-style'] = new WPDDL_style( 'views-pagination-style', WPV_URL_EMBEDDED . '/res/css/wpv-pagination.css');
		}
		

		#listing pages

		$this->styles['dd-listing-page-style'] = new WPDDL_style('dd-listing-page-style', WPDDL_RES_RELPATH . '/css/dd-listing-page-style.css', array());

		#FE styles

        $this->styles['ddl-front-end'] = new WPDDL_style('ddl-front-end', WPDDL_RES_RELPATH . "/css/ddl-front-end.css");
		$this->styles['menu-cells-front-end'] = new WPDDL_style('menu-cells-front-end', WPDDL_RES_RELPATH . "/css/cell-menu-css.css");
	}


	private function __initialize_scripts()
	{
		//dependencies///////
		$this->scripts['headjs'] = new WPDDL_script('headjs', (WPDDL_RES_RELPATH . "/js/external_libraries/head.min.js"), array(), null, true);
		$this->scripts['ddl_common_scripts'] = new WPDDL_script('ddl_common_scripts', WPDDL_RES_RELPATH . "/js/dd_layouts_common_scripts.js", array('jquery', 'headjs', 'underscore'), null, true);

		$this->scripts['toolset-utils'] = new WPDDL_script('toolset-utils', (WPDDL_RES_RELPATH . "/js/external_libraries/utils.js"), array('jquery', 'underscore', 'backbone'), null, true);

		$this->scripts['jquery-ui-cell-sortable'] = new WPDDL_script('jquery-ui-cell-sortable', WPDDL_RES_RELPATH . '/js/external_libraries/jquery.ui.cell-sortable.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-sortable'), '', true);
		$this->scripts['jquery-ui-custom-sortable'] = new WPDDL_script('jquery-ui-custom-sortable', WPDDL_RES_RELPATH . '/js/jquery.ui.custom-sortable.js', array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-sortable'), '', true);
		$this->scripts['select2'] = new WPDDL_script('select2', (WPDDL_RES_RELPATH . "/js/external_libraries/select2.min.js"), array('jquery'), null, true);

		//listing//////
		$this->scripts['ddl_create_new_layout'] = new WPDDL_script('ddl_create_new_layout', (WPDDL_RES_RELPATH . "/js/dd_create_new_layout.js"), array('jquery'), null, true);
		$this->scripts['wp-layouts-colorbox-script'] = new WPDDL_script('wp-layouts-colorbox-script', WPDDL_RES_RELPATH . '/js/external_libraries/jquery.colorbox-min.js', array('jquery'));
		$this->scripts['ddl_post_edit_page'] = new WPDDL_script('ddl_post_edit_page', (WPDDL_RES_RELPATH . "/js/dd_layouts_post_edit_page.js"), array('jquery'), null, true);

		$this->scripts['wp-layouts-dialogs-script'] = new WPDDL_script('wp-layouts-dialogs-script', WPDDL_GUI_RELPATH . 'dialogs/js/dialogs.js', array('jquery', 'editor', 'thickbox', 'media-upload', 'toolset-utils'));

		$this->scripts['ddl-post-types'] = new WPDDL_script('ddl-post-types', WPDDL_RES_RELPATH . '/js/ddl-post-types.js', array('jquery'));


		// media
		$this->scripts['media_uploader_js'] = new WPDDL_script('ddl_media_uploader_js', WPDDL_RES_RELPATH . '/js/ddl-media-uploader.js', array('jquery'), WPDDL_VERSION, true);

		//video and help page//////
		$this->scripts['wp-layouts-video-js'] = new WPDDL_script('wp-layouts-video-js', WPDDL_GUI_RELPATH . 'editor/js/views/UserHelp.js', array('wp-mediaelement', 'jquery', 'underscore'), WPDDL_VERSION, true);
		$this->scripts['wp-layouts-help-js'] = new WPDDL_script('wp-layouts-help-js', WPDDL_RES_RELPATH . '/js/dd_layouts_help-page.js', array('wp-layouts-video-js'), WPDDL_VERSION, true);

		// settings page and scripts
		$this->scripts['ddl-cssframework-settings-script'] = new WPDDL_script('ddl-cssframework-settings-script', WPDDL_RES_RELPATH . '/js/dd_layouts_cssframework_settings.js',array('jquery','underscore'), WPDDL_VERSION, true);


		if( isset( $_GET['page'] ) && 'dd_layouts_edit' == $_GET['page'] )
		{
			#editor
			$this->scripts['ddl-editor-main'] = new WPDDL_script('ddl-editor-main', (WPDDL_GUI_RELPATH . "editor/js/main.js"), array('headjs', 'jquery', 'backbone', 'toolset-utils','jquery-ui-tabs'), null, true);

			#codemirror.js and related
			$this->scripts['views-codemirror-script'] = new WPDDL_script('views-codemirror-script', WPDDL_RES_RELPATH . '/codemirror/lib/codemirror.js', array('jquery'));
			$this->scripts['layouts-meta-html-codemirror-overlay-script'] = new WPDDL_script('layouts-meta-html-codemirror-overlay-script', WPDDL_RES_RELPATH . '/codemirror/addon/mode/overlay.js', array('views-codemirror-script'));
			$this->scripts['layouts-meta-html-codemirror-xml-script'] = new WPDDL_script('layouts-meta-html-codemirror-xml-script', WPDDL_RES_RELPATH . '/codemirror/mode/xml/xml.js', array('layouts-meta-html-codemirror-overlay-script'));
			$this->scripts['layouts-meta-html-codemirror-css-script'] = new WPDDL_script('layouts-meta-html-codemirror-css-script', WPDDL_RES_RELPATH . '/codemirror/mode/css/css.js', array('layouts-meta-html-codemirror-overlay-script'));
			$this->scripts['layouts-meta-html-codemirror-js-script'] = new WPDDL_script('layouts-meta-html-codemirror-js-script', WPDDL_RES_RELPATH . '/codemirror/mode/javascript/javascript.js', array('layouts-meta-html-codemirror-overlay-script'));
			$this->scripts['layouts-meta-html-codemirror-utils-search'] = new WPDDL_script('layouts-meta-html-codemirror-utils-search', WPDDL_RES_RELPATH . '/codemirror/addon/search/search.js', array() );
			$this->scripts['layouts-meta-html-codemirror-utils-search-cursor'] = new WPDDL_script('layouts-meta-html-codemirror-utils-search-cursor', WPDDL_RES_RELPATH . '/codemirror/addon/search/searchcursor.js', array() );
			$this->scripts['layouts-meta-html-codemirror-utils-hint'] = new WPDDL_script('layouts-meta-html-codemirror-utils-hint', WPDDL_RES_RELPATH . '/codemirror/addon/hint/show-hint.js', array() );
			$this->scripts['layouts-meta-html-codemirror-utils-hint-css'] = new WPDDL_script('layouts-meta-html-codemirror-utils-hint-css', WPDDL_RES_RELPATH . '/codemirror/addon/hint/css-hint.js', array() );
			$this->scripts['ddl-sanitize-html'] = new WPDDL_script('ddl-sanitize-html', WPDDL_RES_RELPATH . '/js/external_libraries/sanitize/sanitize.js', array() );
			$this->scripts['ddl-sanitize-helper'] = new WPDDL_script('ddl-sanitize-helper', WPDDL_GUI_RELPATH . 'editor/js/ddl-sanitize-helper.js', array('underscore', 'ddl-sanitize-html', 'jquery') );
			$this->scripts['icl_editor-script'] = new WPDDL_script('icl_editor-script',
				WPDDL_RELPATH . '/toolset-common/visual-editor/res/js/icl_editor_addon_plugin.js',
				array('views-codemirror-script'));
		}
		// listing
		if( isset($_GET['page']) && $_GET['page'] === 'dd_layouts' )
		{
			$this->styles['dd-listing-page-main'] = new WPDDL_script('dd-listing-page-main', (WPDDL_GUI_RELPATH . "listing/js/main.js"), array('headjs', 'jquery', 'backbone', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog', 'jquery-ui-tooltip', 'jquery', 'jquery-ui-tabs'), null, true);
		}

		// Common
		if (defined('WPV_URL_EMBEDDED')) {
			$this->scripts['views-utils-script'] = new WPDDL_script('views-utils-script' , WPV_URL_EMBEDDED . '/res/js/lib/utils.js', array('jquery','toolset-colorbox', 'views-select2-script'), WPV_VERSION);
		}

		// Front End Scripts
		$this->scripts['ddl-layouts-frontend'] = new WPDDL_script('ddl-layouts-frontend', WPDDL_RES_RELPATH . '/js/ddl-layouts-frontend.js', array('jquery'));
		
		// Views support
		if( isset( $_GET['in-iframe-for-layout']) && $_GET['in-iframe-for-layout'] == 1 &&
			(isset( $_GET['page'] ) && (('views-editor' == $_GET['page']) ||
										('views-embedded' == $_GET['page']) ||
										('view-archives-embedded' == $_GET['page']) ||
										('view-archives-editor' == $_GET['page']) ))) {
			$this->scripts['ddl-layouts-views-support'] = new WPDDL_script('ddl-layouts-views-support', WPDDL_RES_RELPATH . '/js/dd-layouts-views-support.js', array('jquery'));
		}

	}

	public function enqueue_scripts($handles)
	{
		if (is_array($handles)) {
			foreach ($handles as $handle) {
				if (isset($this->scripts[$handle])) {
					$this->scripts[$handle]->enqueue();
				}
			}
		} else if (is_string($handles)) {
			if (isset($this->scripts[$handles])) {
				$this->scripts[$handles]->enqueue();
			}
		}
	}

	public function enqueue_styles($handles)
	{
		if (is_array($handles)) {
			foreach ($handles as $handle) {
				if (isset($this->styles[$handle])) {
					$this->styles[$handle]->enqueue();
				}
			}
		} else if (is_string($handles)) {
			if (isset($this->styles[$handles])) $this->styles[$handles]->enqueue();
		}
	}

	public function deregister_scripts($handles)
	{
		if (is_array($handles)) {
			foreach ($handles as $handle) {
				if (isset($this->scripts[$handle])) {
					$this->scripts[$handle]->deregister();
					unset($this->scripts[$handle]);
				}
			}
		} else if (is_string($handles)) {
			if (isset($this->scripts[$handles])) {
				$this->scripts[$handles]->deregister();
				unset($this->scripts[$handles]);
			}
		}
	}

	public function deregister_styles($handles)
	{
		if (is_array($handles)) {
			foreach ($handles as $handle) {
				if (isset($this->styles[$handle])) {
					$this->styles[$handle]->deregister();
					unset($this->styles[$handle]);
				}
			}
		} else if (is_string($handles)) {
			if (isset($this->styles[$handles])) {
				$this->styles[$handles]->deregister();
				unset($this->styles[$handles]);
			}
		}
	}

	public function register_script( $handle, $path = '', $deps = array(), $ver = false, $in_footer = false )
	{
		if( !isset( $this->scripts[$handle] ) )
		{
			$this->scripts[$handle] = new WPDDL_script( $handle, $path, $deps, $ver, $in_footer );
		}
	}

	public function register_style( $handle, $path = '', $deps = array(), $ver = false, $media = 'screen' )
	{
		if( !isset( $this->styles[$handle] ) )
		{
			$this->scripts[$handle] = new WPDDL_style( $handle, $path, $deps, $ver, $media );
		}
	}

	public function localize_script($handle, $object, $args)
	{
		if (isset($this->scripts[$handle])) $this->scripts[$handle]->localize($object, $args);
	}
}
