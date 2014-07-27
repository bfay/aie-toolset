<?php

class WPDD_json2layout {
        
    private $layout;
	private $_preset_mode;
    function __construct($preset = false){
		$this->_preset_mode = $preset;
        $this->layout = null;
    }
    
    function json_decode($json, $already_decoded = false) {
        if ($already_decoded) {
			$json_array = $json;
		} else {
			$json_array = json_decode($json, true);
		}
        $this->layout = new WPDD_layout( $json_array['width'], $json_array['cssframework'] );
        $this->layout->set_name($json_array['name']);
        if( isset( $json_array['parent'] ) ) $this->layout->set_parent_name( $json_array['parent'] );
        $this->add_rows( $json_array['Rows'], $this->layout );
        
        return $this->layout;
    }
   
    private function add_rows($rows, $target) {
        if ($rows) {
            foreach($rows as $row) {
                
                if ($this->_preset_mode) {
                    $row['layout_type'] = 'fluid';
                }
    
                if( method_exists ( $this , $row['kind'] ) )
                {
                    $row_object = $this->{$row['kind']}($row);
                    $target->add_row($row_object);
                }
            }
        }        
    }
   
    private function create_cell($cell) {
        global $wpddlayout;
		
		if (!isset($cell['tag']) || $cell['tag'] == '') {
			$cell['tag'] = 'div';
		}
        switch ($cell['kind']) {
            case 'Container':
                $container = new WPDD_layout_container( $cell['name'], $cell['width'], $cell['additionalCssClasses'], $cell['editorVisualTemplateID'], $cell['cssId'], $cell['tag'], $this->layout->get_css_framework() );
                $this->add_rows($cell['Rows'], $container);
                return $container;
            
            case 'Cell':

                $layout = $wpddlayout->create_cell(
	                                    isset( $cell['cell_type'] ) ? $cell['cell_type'] : 'spacer',
	                                    isset( $cell['name'] ) ? $cell['name'] : '',
	                                    isset( $cell['width'] ) ? $cell['width'] : 1,
	                                    isset( $cell['additionalCssClasses'] ) ? $cell['additionalCssClasses'] : '',
	                                    isset( $cell['content'] ) ? $cell['content'] : '',
	                                    isset( $cell['cssId'] ) ? $cell['cssId'] : '',
	                                    isset( $cell['tag'] ) ? $cell['tag'] : ''
                                    );
                if (!$layout) {
                        if( 'spacer' == $cell['cell_type'] || 'undefined' == $cell['cell_type'])
                        {
                            $layout = new WPDD_layout_spacer(
	                                            $cell['name'],
	                                            $cell['width'],
	                                            isset( $cell['additionalCssClasses'] ) ? $cell['additionalCssClasses'] : '',
	                                            '',
	                                            $this->_preset_mode
                                               );
                        }
                        else
                        {
                            $layout = new WPDD_layout_cell( $cell['name'], $cell['width'], $cell['additionalCssClasses'], '',$cell['content'], $cell['cssId'], $cell['tag'] );
                        }

                }
                return $layout;

            default:
                if( 'spacer' == $cell['cell_type'] || 'undefined' == $cell['cell_type'] )
                {
                    $layout = new WPDD_layout_spacer( $cell['name'], $cell['width'], $cell['additionalCssClasses'], '', $this->_preset_mode );
                }
                else
                {
                    $layout = new WPDD_layout_cell( $cell['name'], $cell['width'], $cell['additionalCssClasses'], '',$cell['content'], $cell['cssId'], $cell['tag'] );
                }
                return $layout;
        }
        
    }

	// the following methods take their names from the kind attribute of our Row models
	// so to have js file name == js model class name == 'kind' == render method name
	private function Row( $row )
	{
		$row_object = new WPDD_layout_row(
			$row['name'],
			$row['cssClass'],
			$row['editorVisualTemplateID'],
			isset($row['layout_type']) ? $row['layout_type'] :'' ,
			isset( $row['cssId'] ) ? $row['cssId'] : '',
			isset($row['additionalCssClasses']) ? $row['additionalCssClasses'] : '',
			isset( $row['tag'] ) ? $row['tag'] : '',
			isset( $row['mode'] ) ? $row['mode'] : 'normal'
		);
		if( isset( $row['Cells'] ) )
		{
			foreach($row['Cells'] as $cell) {
				if (isset($cell['row_divider'])) {
					$cell['width'] *= $cell['row_divider'];
				}

				$row_object->add_cell($this->create_cell($cell));

			}
		}
		return $row_object;
	}

	private function ThemeSectionRow($row)
	{
		global $wpddlayout;

		$row_object = new WPDD_layout_theme_section(
					$row['type'],
					$row['name'],
					$row,
					$wpddlayout->registered_theme_sections->get_theme_section_info( $row['type'] )
		);
		return $row_object;
	}
}
