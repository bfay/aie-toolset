<?php

function register_dd_layout_cell_type($cell_type, $args) {
	global $wpddlayout;
	return $wpddlayout->register_dd_layout_cell_type($cell_type, $args);
}

class WPDD_registed_cell_types{

	private $cell_types;
	private $current_cell_type;

	function __construct(){
		$this->cell_types = array();
		$this->current_cell_type = '';
	}

	function register_dd_layout_cell_type($cell_type, $data) {
		if (array_key_exists($cell_type, $this->cell_types)) {
			return false;
		} else {
			$this->current_cell_type = $cell_type;

			if (!isset($data['icon-css'])) { $data['icon-css'] = ''; }
			if (!isset($data['name'])) { $data['name'] = ''; }
			if (!isset($data['description'])) { $data['description'] = ''; }
			if (!isset($data['preview-image-url'])) { $data['preview-image-url'] = ''; }
			if (!isset($data['button-text'])) { $data['button-text'] = ''; }
			if (!isset($data['dialog-template'])) { $data['dialog-template'] = ''; }
			if (!isset($data['allow-multiple'])) { $data['allow-multiple'] = true; }
			if (!isset($data['cell-class'])) { $data['cell-class'] = ''; }
			if (!isset($data['register-styles'])) { $data['register-styles'] = array(); }
			if (!isset($data['register-scripts'])) { $data['register-scripts'] = array(); }

			$this->cell_types[$cell_type] = $data; // Initialize here so it can be accessed during dialog template callback.

			if (isset($data['dialog-template-callback'])) {
				$data['dialog-template'] = call_user_func($data['dialog-template-callback']);
			}

			$this->cell_types[$cell_type] = $data;

			$this->current_cell_type = '';

			return true;
		}
	}

	function get_input_name($name) {
		return 'ddl-layout-' . $name;
	}

	function get_cell_types () {
		return array_keys($this->cell_types);
	}

	function get_cell_templates () {
		$templates = '';

		foreach ($this->cell_types as $cell_type => $data) {
			$templates .= '<script type="text/html" id="' . $cell_type . '-template">';
			$templates .= $this->get_cell_template($data);
			$templates .= '</script>';
		}

		return $templates;
	}

	public static function clean_js_variables( $js_string )
	{
		// strip js not allowed characters
		$clean = preg_replace('/[\|&;\$%@"<>\(\)\+,]/', '', $js_string);

		// strip spaces
		$clean = str_replace(' ', '', $clean );

		return $clean;
	}

	function get_cell_template($cell_data) {
		$data_to_display = call_user_func($cell_data['cell-template-callback']);
		if (strpos($data_to_display, '<div class="cell-content">') !== false) {
			return $data_to_display;
		} else {
			ob_start();
			?>
				<div class="cell-content">
					<p class="cell-name">{{ name }}</p>
					<?php if ($data_to_display): $data_to_display = WPDD_registed_cell_types::clean_js_variables( $data_to_display ); ?>
						<div class="cell-content">
							<#
							/*
							 * fails silently with a console message if
							 * content is undefined or null
							 * anyway it prints cells on the screen
							 * if content.<?php echo $data_to_display; ?>
							 * is undefined _.template handles
							 * the issue internally an print empty string silently
							 */
							try {
									var element = DDL_Helper.sanitizeHelper.stringToDom( content.<?php echo $data_to_display; ?> );
									print( element.innerHTML );
								}
								catch(e) {
									 console.log( e.message );
								}
							#>
						</div>
					<?php endif; ?>
				</div>
			<?php
			return ob_get_clean();
		}
	}

	function get_cell_info($cell_type) {
		return $this->cell_types[$cell_type];
	}

	function get_current_cell_info() {
		if ($this->current_cell_type) {
			return $this->cell_types[$this->current_cell_type];
		} else {
			return array();
		}

	}

	function create_cell($cell_type, $name, $width, $css_class_name, $content, $css_id, $tag) {
		if (isset($this->cell_types[$cell_type])) {
			return new WPDD_registered_cell($cell_type, $name, $width, $css_class_name, $content, $this->cell_types[$cell_type], $css_id, $tag);
		} else {
			return null;
		}
	}

	function enqueue_cell_styles() {
		foreach ($this->cell_types as $cell_type => $data) {
			foreach ($data['register-styles'] as $style_data) {
				call_user_func_array('wp_register_style', $style_data);
				wp_enqueue_style($style_data[0]);
			}
		}
	}

	function enqueue_cell_scripts() {
		foreach ($this->cell_types as $cell_type => $data) {
			foreach ($data['register-scripts'] as $script_data) {
				call_user_func_array('wp_register_script', $script_data);
				wp_enqueue_script($script_data[0]);
			}
		}

	}
}

class WPDD_registered_cell extends WPDD_layout_cell {

	private $cell_data;
	function __construct($cell_type, $name, $width, $css_class_name, $content, $cell_data, $css_id, $tag) {
		parent::__construct($name, $width, $css_class_name, '', $content, $css_id, $tag);
		$this->set_cell_type($cell_type);
		$this->cell_data = $cell_data;
	}

	function frontend_render($target) {
		$css = $this->get_css_class_name();
		if ($this->cell_data['cell-class']) {
			$css .= ' ' . $this->cell_data['cell-class'];
		}
		$target->cell_start_callback( $css, $this->get_width(), $this->get_css_id(), $this->get_tag() );

		$this->frontend_render_cell_content($target);

		$target->cell_end_callback($this->get_tag());
	}

	function frontend_render_cell_content($target) {
		if (isset($this->cell_data['cell-content-callback'])) {
			global $ddl_fields_api;
			$ddl_fields_api->set_current_cell_content($this->get_content());
			$content = call_user_func($this->cell_data['cell-content-callback'], $this->get_content());
		} else {
			$content = '';
		}
		$target->cell_content_callback($content);
	}

}
