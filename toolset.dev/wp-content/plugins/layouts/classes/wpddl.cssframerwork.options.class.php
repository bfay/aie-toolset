<?php
class WPDD_Layouts_CSSFrameworkOptions{

	private $options_manager;
	private static $instance;
	const FRAMEWORK_OPTION = 'ddl_layouts_css_framework_options';
	const FRAMEWORK_SET = 'framework_setup';
	const DEFAULT_FRAMEWORK = 'bootstrap-2';
	private $supported_frameworks = null;

	private function __construct( )
	{
		$this->options_manager = new WPDDL_Options_Manager( self::FRAMEWORK_OPTION );

		$this->set_default_framework( );
		$this->set_up_frameworks();
		$this->set_up_features();

		if( is_admin() && isset($_GET['page']) && $_GET['page'] == 'dd_layouts_settings' ){

			add_action('admin_enqueue_scripts', array($this, 'settings_page_scripts'));
		}
		
		if( is_admin() && isset($_GET['page']) && $_GET['page'] == 'dd_layouts' ){
			add_action('admin_notices', array($this, 'show_admin_messages'));
		}

		add_action('switch_theme', array($this, 'reset_framework_set'));

		add_action( 'wp_ajax_save_layouts_css_framework_option',  array($this, 'save_layouts_css_framework_option_callback') );
	}

	function show_admin_messages () {
		$options = $this->options_manager->get_options();
		
		if (!isset($options[self::FRAMEWORK_SET])) {
			?>
			<div class="error">
				<h3><?php _e('Your theme has not set the Bootstrap version for Layouts.', 'ddl-layouts'); ?> </h3>
				<p>
					<?php 
						echo sprintf( __( 'Please go to the %sLayouts settings page%s and select which Bootstrap version your site uses. Alternatively, you can add this to your theme\'s code, by following the %sinstructions for setting Bootstrap version in the theme%s.', 'ddl-layouts'),
									   '<a href="' . admin_url('admin.php?page=dd_layouts_settings') . '">',
									   '</a>',
									   '<a href="' . WPDLL_THEME_INTEGRATION_QUICK . '#BS-version" target="_blank">',
									   '</a>');
									   
					?>
				</p>
			</div>
			
			<?php
		}
	}
	
	public function settings_page_scripts()
	{
		global $wpddlayout;

		$wpddlayout->enqueue_scripts('ddl-cssframework-settings-script');
	}

	private function set_up_features()
	{
		$framework = $this->get_current_framework();

		switch( $framework )
		{
			case 'bootstrap-3':
				remove_ddl_support('fixed-layout');
				break;
		}
	}

	private function set_framework( $framework )
	{
		return $this->options_manager->update_options( self::FRAMEWORK_OPTION, $framework, true );
	}

	private function set_default_framework(  )
	{
		if( !is_array( $this->options_manager->get_options() ) )
		{
			$this->set_framework( self::DEFAULT_FRAMEWORK  );
		}
	}

	public static function getInstance( )
	{
		if (!self::$instance)
		{
			self::$instance = new WPDD_Layouts_CSSFrameworkOptions();
		}

		return self::$instance;
	}

	private function set_up_frameworks()
	{
		$this->supported_frameworks = array();
		$this->supported_frameworks['bootstrap-2'] = (object) array('label' => 'Bootstrap 2');
		$this->supported_frameworks['bootstrap-3'] = (object) array('label' => 'Bootstrap 3');
	}

	public function get_supported_frameworks( )
	{
		return $this->supported_frameworks;
	}

	public function save_layouts_css_framework_option_callback()
	{
		if( $_POST && wp_verify_nonce( $_POST['set-layout-css-framework-nonce'], 'set-layout-css-framework-nonce' ) )
		{
			$framework_saved = $this->set_framework( $_POST['css_framework'] );
			$current = $this->get_current_framework_name();
			$send = json_encode( array( 'message' => array(
														   'text' => sprintf(__('The CSS framework has been set to %s. Please make sure that your theme supports %s.', 'ddl-layouts'), $current, $current),
														   'is_saved' => $framework_saved ) ) );
			$this->set_up_features();
			
			$this->options_manager->update_options( self::FRAMEWORK_SET, true, true );
			
		}
		else
		{
			$send = json_encode( array( 'error' =>  __( sprintf('Nonce problem: apparently we do not know from where the request comes from. %s', __METHOD__ ), 'ddl-layouts') ) );
		}

		die( $send );
	}

	public function print_frameworks_settings()
	{
			$data = array(
				'action' => 'save_layouts_css_framework_option',
				'set-layout-css-framework-nonce' => wp_create_nonce( 'set-layout-css-framework-nonce' )
			);
		?>

		<form id="layouts-css-framework-settings-form" class="js-layouts-css-framework-settings-form" data-object="<?php echo htmlspecialchars( json_encode( $data ) ); ?>">
			<?php wp_nonce_field( 'ddl_layout_css_framework_settings_nonce', 'ddl_layout_css_framework_settings_nonce' );?>

			<?php foreach( $this->get_supported_frameworks( ) as $framework => $framework_data): ?>
				<p>
					<input type="radio" name="layouts-framework" id="layouts-framework-<?php echo $framework;?>" value="<?php echo $framework;?>" <?php if( ( $this->get_current_framework() == $framework ) ): ?>checked<?php endif;?> />
					<label for="layouts-framework-<?php echo $framework;?>"><?php echo $framework_data->label; ?></label>
				</p>
			<?php endforeach; ?>

			<div class="js-css-ajax-messages"></div>

			<p class="toolset-alert-error js-dir-messages css-dir-messages">

			</p>
			
			<?php 
				$options = $this->options_manager->get_options();
				if (!isset($options[self::FRAMEWORK_SET])) {
					?>
						<p id="ddl-framework-warning" class="toolset-alert-info toolset-alert">
							<strong><?php _e('Your theme has not specified a framework.', 'ddl-layouts'); ?></strong>
							<br />
							<?php _e( 'Please select and save the framework that your theme uses.', 'ddl-layouts'); ?>
						</p>
					<?php
				}
			?>

			<p class="buttons-wrap">
				<button class="button-primary js-save-layouts-css-framework-settings"><?php _e('Save CSS Framework', 'ddl-layouts');?></button>
			</p>
		</form>

		<?php

	}

	public function get_current_framework()
	{
		$opts = $this->options_manager->get_options();
		return $opts[self::FRAMEWORK_OPTION];
	}
	
	public function get_current_framework_name() {
		$current = $this->get_current_framework();
		
		return $this->supported_frameworks[$current]->label;
	}
	
	public function theme_set_framework ( $framework ) {
		if (array_key_exists($framework, $this->supported_frameworks)) {
			$this->set_framework ( $framework );

			$this->options_manager->update_options( self::FRAMEWORK_SET, true, true );
		}
	}
	
	function reset_framework_set () {
		$this->options_manager->delete_options( null, self::FRAMEWORK_SET);
	}
	
}