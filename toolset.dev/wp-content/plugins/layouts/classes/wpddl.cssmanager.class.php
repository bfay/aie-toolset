<?php
class WPDD_Layouts_CSSManager{

	private static $instance;
	public $options_manager;
    private $layout_for_render = 0;
	const API_QUERY_STRING = 'ddl_layouts_css_api';
	const INITIAL_CSS = '/*Layouts css goes here*/';
	const CSS_TEMP_DIR = '/ddl-layouts-tmp';

	//Since we have single css file for all layouts our class is singleton, the instance is called statically with: WPDD_Layouts_CSSManager::getInstance()
	private function __construct( )
	{
		$this->options_manager = new WPDDL_Options_Manager( WPDDL_CSS_OPTIONS );

		//add the rewrite rule to load css fallback
		add_action( 'init', array(&$this, 'ddl_layouts_css_init_internal') );

		if( is_admin () )
		{
			add_action('wp_ajax_check_system_credentials', array(&$this, 'check_credentials'), 10 );

		}
		else
		{
			add_filter('get_layout_id_for_render', array($this,'wpddl_frontend_header_init'), 10, 2);
			add_action('template_redirect', array($this, 'layout_style_router'));
		}
	}

    public function wpddl_frontend_header_init($id, $layout)
    {
        if( $id !== 0 ) $this->handle_layout_css_fe();
        return $id;
    }

	function handle_layout_css_save( $css )
	{

		$options = $this->options_manager->get_options();

		if( isset( $options['mode']['db_ok'] ) && $options['mode']['db_ok'] === true )
		{
			return $this->save_layout_css_to_db( $css, $options );
		}
		return false;
	}

	function is_css_dir()
	{
		return wp_mkdir_p( $this->css_dir() );
	}

	function css_dir()
	{
		return $this->uploads_obj()->basedir . self::CSS_TEMP_DIR;
	}

	function is_css_possible()
	{
		return $this->is_css_dir() || $this->is_using_permalinks();
	}

	function uploads_obj()
	{
		$upload = wp_upload_dir();
		return (object) $upload;
	}

	function handle_layout_css_fe()
	{

		$options = $this->options_manager->get_options();

		if( isset( $options['mode']['db_ok'] ) )
		{
			// Create a file in the uploads directory.
			$file_ok = false;

			if ( $this->is_css_dir() ) {
				$css = $this->options_manager->get_options(WPDDL_LAYOUTS_CSS);
				$md5 = md5($css);
				$file_name = $this->css_dir() .'/'. $md5 . '.css';
				if (!is_file($file_name)) {
					// create the file.
					$file_ok = file_put_contents($file_name, $css);
				}
				else if( is_file($file_name) )
				{
					$file_ok = true;
				}
				
				if ($file_ok) {
					wp_enqueue_style('wp_ddl_layout_fe_css', $this->uploads_obj()->baseurl . self::CSS_TEMP_DIR . '/' . $md5 . '.css', array(), WPDDL_VERSION, 'screen' );
				}

 			}

			if ( !$file_ok && $this->is_using_permalinks() ) {
				
				// we couldn't create a file in the uploads directory.
				// Use the method that uses the template_redirect hook.
				wp_enqueue_style('wp_ddl_layout_fe_css', site_url() . '/ddl-layouts-load-styles.css?c=1', array(), WPDDL_VERSION, 'screen' );
			}
		}

	}

	public function save_layout_css_to_db( $css, $options, $force = false ){

		if( $this->options_manager->update_options( $options['mode']['css_option_record'], $css, $force ) )
		{
			return array(
				'db_ok' => true,
				'css_option_record' => $options['mode']['css_option_record'],
				'db_success' => sprintf( __( 'CSS was successfully saved in %s option in database.', 'ddl-layouts' ), $options['mode']['css_option_record'] )
			);
		}

		return null;
	}

	function save_css_settings() {

		if( $_POST && $_POST['action'] == 'ddl_layout_save_css_settings' )
		{
			if ( !wp_verify_nonce($_POST['ddl_layout_css_settings_nonce'], 'ddl_layout_css_settings_nonce') ) die("Security check");

			$mode = $_POST['layout_css_option'];

			$save_in = $this->css_settings_handle_mode( $mode );

			if( isset( $save_in['db_ok'] ) && $save_in['db_ok'] === false )
			{
				die( json_encode( array( "error" =>  __("There are problems saving this option in the database.", 'ddl-layouts') ) ) );
			}
			else if( isset( $save_in['db_ok'] ) && $save_in['db_ok'] )
			{
				$message = array( "message" =>  __("CSS option saved.", 'ddl-layouts') );
			}

			$copy_css = $save_in != $this->options_manager->get_options('mode');

			if ($copy_css) {
				// we need to copy the css.
				$css = $this->get_layouts_css();
			}

			$this->options_manager->update_options( 'mode', $save_in, true );

			if ($copy_css) {
				$this->handle_layout_css_save($css);
			}

			die(  json_encode( $message )  );
		}

		die( json_encode( array( "error" => __("Something went wrong communicating with the server", 'ddl-layouts' ) ) )  );
	}

	function css_settings_handle_mode( $mode )
	{
		switch( $mode )
		{
			case 'db':
				return $this->css_db_handle();
			default:
				return $this->css_db_handle();
		}

		return false;
	}

	function css_db_handle()
	{
		if( $this->is_using_permalinks() ){
			$this->ddl_layouts_css_init_internal();
		}
		$this->options_manager->update_options( WPDDL_LAYOUTS_CSS, self::INITIAL_CSS );
		return array( 'db_ok' => true, 'css_option_record' => WPDDL_LAYOUTS_CSS );
	}

	function ddl_layouts_css_init_internal()
	{
		global $wp_rewrite;

		if( $wp_rewrite->using_permalinks() ){
			add_rewrite_rule( 'ddl-layouts-load-styles.css$', 'index.php?' .self::API_QUERY_STRING. '=1', 'top' );
			$wp_rewrite->flush_rules( false );
		}
		return $wp_rewrite->rules;
	}

	private function is_using_permalinks()
	{
		global $wp_rewrite;

		return $wp_rewrite->using_permalinks();
	}

	public static function getInstance( )
	{
		if (!self::$instance)
		{
			self::$instance = new WPDD_Layouts_CSSManager();
		}

		return self::$instance;
	}
	
	function layout_style_router() {
		$bits =explode("/", esc_attr($_SERVER['REQUEST_URI']) );
		for ($i = 0; $i < sizeof($bits); $i++) {

			if (strpos($bits[$i], 'ddl-layouts-load-styles.css') === 0) {
				$css = $this->options_manager->get_options(WPDDL_LAYOUTS_CSS);
				include_once WPDDL_RES_ABSPATH . '/load-styles.php';
				exit();
			}
		}
	}
	
	public function get_layouts_css()
	{

		$options = $this->options_manager->get_options();

		if( !isset( $options['mode'] ) )
		{
			$this->css_settings_init();
			return $this->get_layouts_css();
		}
		elseif( isset( $options['mode'] ) )
		{
			$option = $options['mode'];

			if( isset($option['db_ok']) && $option['db_ok'] === true )
			{
				return $this->options_manager->get_options( $option['css_option_record'] );
			}
		}
	}
	public function css_settings_init()
	{
		$options = $this->options_manager->get_options();
		$css_opt = isset( $options['mode'] ) ? $options['mode'] : false;

		if( $css_opt === false )
		{
			$ret = $this->where_is_css_saved();
			$this->options_manager->update_options( 'mode', $ret );
			return $ret;
		}

		return null;
	}

	private function where_is_css_saved()
	{
		return $this->css_db_handle();
	}
	
	function import_css_from_theme( $source_dir )
	{

		$file = $source_dir. '/layouts.css';

		if( !file_exists( $file  ) ) return;

		$import_css = file_get_contents($file);

		if( !$import_css ) return;

		$css = $this->get_layouts_css();

		if( $css == $import_css ) return;
		
		if ($css == '' || $css == self::INITIAL_CSS) {

			$options = $this->options_manager->get_options();
	
			$this->save_layout_css_to_db( $import_css, $options, true );
		}
	}
	
}