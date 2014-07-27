<?php

require_once 'XML2Array.class.php';

class WPBT_grid_xml {

    function __construct() {
        // restore grid to default
        add_action('init', array($this, 'wpbootstrap_restore_grid_to_default'));

        // render grid for inserting
        add_action('wp_print_scripts', array($this, 'wpbootstrap_insert_grid'));
    }

    function wpbootstrap_insert_grid() {// render grid for inserting
    	
    	global $typenow;
    	
    	if (isset($typenow)) {
    		 	
        $grid_array = get_option('bootstrap_xml_grid_list', '');

        //Emerson fix: Prevent fatal error in theme activation, only run the script below if $grid_array is defined and has value.
        $grid_conditional_loading=$this->check_if_to_be_loaded_grid();
        if ($grid_conditional_loading) {
        if ((!(empty($grid_array))) && (is_array($grid_array))) {
        echo '<script>
           var bootstrap_grid_object = {
            insert_grid: function(grid_type, grid_size, available_columns_size){
                var grid="";
                var grid_type_id = grid_type.replace("grid-type-","");
                switch(grid_type_id){
                ';
        if (isset($grid_array["item"][0])) {
            foreach ($grid_array as $items) {
                foreach ($items as $grid_key => $item) {
                    echo 'case "' . $grid_key . '" : grid = "<div class=\""+grid_size+"\">\r\n\r\n';
                    if (isset($item["row"]["cell"][0])) {
                        foreach ($item["row"]["cell"] as $c_level_1) {
                            if (!empty($c_level_1['row'])) {
                                echo '    <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $c_level_1["@attributes"]['size'] . ', available_columns_size)+"\">\r\n';
                                foreach ($c_level_1['row'] as $r_level_2) {
                                    echo '\r\n      <div class=\""+grid_size+" \">\r\n';
                                    if (isset($r_level_2["cell"][0])) {
                                        foreach ($r_level_2["cell"] as $c_level_2) {
                                            echo '        <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $c_level_2["@attributes"]['size'] . ', available_columns_size, ' . $c_level_1["@attributes"]['size'] . ')+"\">\r\n          ' . $c_level_2["@value"] . '\r\n        </div>\r\n';
                                        }
                                    } else {
                                        echo '        <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $r_level_2["cell"]["@attributes"]['size'] . ', available_columns_size, ' . $c_level_1["@attributes"]['size'] . ')+"\">\r\n        ' . $r_level_2["cell"]["@value"] . '\r\n        </div>\r\n';
                                    }
                                    echo '      </div>\r\n';
                                }
                                echo '\r\n    </div>\r\n\r\n';
                            } else {
                                echo '    <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $c_level_1["@attributes"]['size'] . ', available_columns_size)+"\">\r\n      ' . $c_level_1["@value"] . '\r\n    </div>\r\n';
                            }
                        }
                    } else {
                        echo '  <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $item["row"]["cell"]["@attributes"]['size'] . ', available_columns_size)+"\">\r\n      ' . $item["row"]["cell"]["@value"] . '\r\n    </div>\r\n';
                    }
                    echo '\r\n</div>\r\n"; break;';
                }
            }
        } else {
            echo 'case "0" : grid = "<div class=\""+grid_size+"\">\r\n\r\n';
            
            if (isset($grid_array["item"]["row"]["cell"][0])) {
                foreach ($grid_array["item"]["row"]["cell"] as $c_level_1) {
                    if (!empty($c_level_1['row'])) {
                        echo '    <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $c_level_1["@attributes"]['size'] . ', available_columns_size)+"\">\r\n';
                        foreach ($c_level_1['row'] as $r_level_2) {
                            echo '\r\n      <div class=\""+grid_size+" \">\r\n';
                            if (isset($r_level_2["cell"][0])) {
                                foreach ($r_level_2["cell"] as $c_level_2) {
                                    echo '        <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $c_level_2["@attributes"]['size'] . ', available_columns_size, ' . $c_level_1["@attributes"]['size'] . ')+"\">\r\n          ' . $c_level_2["@value"] . '\r\n        </div>\r\n';
                                }
                            } else {
                                echo '        <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $r_level_2["cell"]["@attributes"]['size'] . ', available_columns_size, ' . $c_level_1["@attributes"]['size'] . ')+"\">\r\n        ' . $r_level_2["cell"]["@value"] . '\r\n        </div>\r\n';
                            }
                            echo '      </div>\r\n';
                        }
                        echo '\r\n    </div>\r\n\r\n';
                    } else {
                        echo '    <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $c_level_1["@attributes"]['size'] . ', available_columns_size)+"\">\r\n      ' . $c_level_1["@value"] . '\r\n    </div>\r\n';
                    }
                }
            } else {
                echo '  <div class=\"span"+bootstrap_object.span_size(grid_size, ' . $grid_array["item"]["row"]["cell"]["@attributes"]['size'] . ', available_columns_size)+"\">\r\n      ' . $grid_array["item"]["row"]["cell"]["@value"] . '\r\n    </div>\r\n';
            }
            
            echo '\r\n</div>\r\n"; break;';
        }
        echo '  }     
                return grid;
            },
            
        render_grid: function(){
            var grid="";
            ';

        // show grid list in popup
        if (isset($grid_array["item"][0])) {
            foreach ($grid_array as $items) {
                foreach ($items as $grid_key => $item) {
                    $grid_columns = explode(',', $item["@attributes"]["columns"]);
                    $grid_columns_item_list = '';
                    if (count($grid_columns) > 1) {
                        foreach ($grid_columns as $grid_columns_item) {
                            $grid_columns_item_list .=' grid-columns-' . trim($grid_columns_item);
                        }
                    } else {
                        $grid_columns_item_list .='grid-columns-' . $grid_columns[0];
                    }
                    echo 'grid += "<li  class=\"' . $grid_columns_item_list . '\" rel=\"grid-type-' . $grid_key . '\"><div class=\"row-fluid\">';
                    if (isset($item["row"]["cell"][0])) {
                        foreach ($item["row"]["cell"] as $c_level_1) {
                            if (!empty($c_level_1['row'])) {
                                echo '<div class=\"child span' . $c_level_1["@attributes"]['size'] . '\">';
                                foreach ($c_level_1['row'] as $r_level_2) {
                                    echo '<div class=\"row-fluid\">';
                                    if (isset($r_level_2["cell"][0])) {
                                        foreach ($r_level_2["cell"] as $c_level_2) {
                                            echo '<div class=\"holder span' . $c_level_2["@attributes"]['size'] . '\">' . $c_level_2["@value"] . '</div>';
                                        }
                                    } else {
                                        echo '<div class=\"holder span' . $r_level_2["cell"]["@attributes"]['size'] . '\">' . $r_level_2["cell"]["@value"] . '</div>';
                                    }
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<div class=\"holder span' . $c_level_1["@attributes"]['size'] . '\">' . $c_level_1["@value"] . '</div>';
                            }
                        }
                    } else {
                        echo '<div class=\"holder span' . $item["row"]["cell"]["@attributes"]['size'] . '\">' . $item["row"]["cell"]["@value"] . '</div>';
                    }
                    echo '</div></li>";';
                }
            }
        } else {
            $grid_columns = explode(',', $grid_array["item"]["@attributes"]["columns"]);
            $grid_columns_item_list = '';
            if (count($grid_columns) > 1) {
                foreach ($grid_columns as $grid_columns_item) {
                    $grid_columns_item_list .=' grid-columns-' . trim($grid_columns_item);
                }
            } else {
                $grid_columns_item_list .='grid-columns-' . $grid_columns[0];
            }
            echo 'grid += "<li  class=\"grid-columns-' . $grid_columns_item_list . '\" rel=\"grid-type-0\"><div class=\"row-fluid\">';
            if (isset($grid_array["item"]["row"]["cell"][0])) {
                foreach ($grid_array["item"]["row"]["cell"] as $c_level_1) {
                    if (!empty($c_level_1['row'])) {
                        echo '<div class=\"child span' . $c_level_1["@attributes"]['size'] . '\">';
                        foreach ($c_level_1['row'] as $r_level_2) {
                            echo '<div class=\"row-fluid\">';
                            if (isset($r_level_2["cell"][0])) {
                                foreach ($r_level_2["cell"] as $c_level_2) {
                                    echo '<div class=\"holder span' . $c_level_2["@attributes"]['size'] . '\">' . $c_level_2["@value"] . '</div>';
                                }
                            } else {
                                echo '<div class=\"holder span' . $r_level_2["cell"]["@attributes"]['size'] . '\">' . $r_level_2["cell"]["@value"] . '</div>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<div class=\"holder span' . $c_level_1["@attributes"]['size'] . '\">' . $c_level_1["@value"] . '</div>';
                    }
                }
            } else {
                echo '<div class=\"holder span' . $grid_array["item"]["row"]["cell"]["@attributes"]['size'] . '\">' . $grid_array["item"]["row"]["cell"]["@value"] . '</div>';
            }
            echo '</div></li>";';
        }
        echo ' return grid;
        }
        };
        </script>';
      }
      }
     }
    }

    function wpbootstrap_restore_grid_to_default() {// restore grid to default
        if (isset($_GET['restore_grid_to_default'])) {
        	//Emerson: Use get_template_directory() instead of get_template_directory_uri()
            $file = file_get_contents(get_template_directory() . '/theme-options/bootstrap-grid/resources/layout-grid.xml');
            $grid_array = XML2Array::createArray($file);
            update_option('bootstrap_xml_grid_list', $grid_array["GridList"], '', 'yes');
            
            //Use stylesheet
            $themename = get_option( 'stylesheet' );
            $themename = preg_replace("/\W/", "_", strtolower($themename) );
            
            $grid_list = get_option($themename);
            $grid_list['grid_list_xml_file'] = '';
            update_option($themename, $grid_list, '', 'yes');
            header('Location: ' . admin_url('themes.php?page=options-framework'));
        }
    }

    public static function wpbootstrap_Array2Grid($array) {// conver array to bootstrap grid list
        $result = '<ul class="grid-list-compact">';
        if (isset($array["item"][0])) {
            foreach ($array as $items) {
                foreach ($items as $grid_key => $item) {
                    $result .= '<li class="for-columns-' . $item["@attributes"]["columns"] . '"><div class="row-fluid">';
                    if (isset($item["row"]["cell"][0])) {
                        foreach ($item["row"]["cell"] as $c_level_1) {
                            if (!empty($c_level_1['row'])) {
                                $result .= '<div class="child span' . $c_level_1["@attributes"]['size'] . '">';
                                foreach ($c_level_1['row'] as $r_level_2) {
                                    $result .= '<div class="row-fluid">';
                                    if (isset($r_level_2["cell"][0])) {
                                        foreach ($r_level_2["cell"] as $c_level_2) {
                                            $result .= '<div class="holder span' . $c_level_2["@attributes"]['size'] . '">' . $c_level_2["@value"] . '</div>';
                                        }
                                    } else {
                                        $result .= '<div class="holder span' . $r_level_2["cell"]["@attributes"]['size'] . '">' . $r_level_2["cell"]["@value"] . '</div>';
                                    }
                                    $result .= '</div>';
                                }
                                $result .='</div>';
                            } else {
                                $result .= '<div class="holder span' . $c_level_1["@attributes"]['size'] . '">' . $c_level_1["@value"] . '</div>';
                            }
                        }
                    } else {
                        $result .= '<div class="holder span' . $item["row"]["cell"]["@attributes"]['size'] . '">' . $item["row"]["cell"]["@value"] . '</div>';
                    }
                    $result .= '</div></li>';
                }
            }
        } else {
            $result .= '<li class="for-columns-' . $array['item']["@attributes"]["columns"] . '"><div class="row-fluid">';
            if (isset($array['item']["row"]["cell"][0])) {
                foreach ($array['item']["row"]["cell"] as $c_level_1) {
                    if (!empty($c_level_1['row'])) {
                        $result .= '<div class="child span' . $c_level_1["@attributes"]['size'] . '">';
                        foreach ($c_level_1['row'] as $r_level_2) {
                            $result .= '<div class="row-fluid">';
                            if (isset($r_level_2["cell"][0])) {
                                foreach ($r_level_2["cell"] as $c_level_2) {
                                    $result .= '<div class="holder span' . $c_level_2["@attributes"]['size'] . '">' . $c_level_2["@value"] . '</div>';
                                }
                            } else {
                                $result .= '<div class="holder span' . $r_level_2["cell"]["@attributes"]['size'] . '">' . $r_level_2["cell"]["@value"] . '</div>';
                            }
                            $result .= '</div>';
                        }
                        $result .='</div>';
                    } else {
                        $result .= '<div class="holder span' . $c_level_1["@attributes"]['size'] . '">' . $c_level_1["@value"] . '</div>';
                    }
                }
            } else {
                $result .= '<div class="holder span' . $array['item']["row"]["cell"]["@attributes"]['size'] . '">' . $array['item']["row"]["cell"]["@value"] . '</div>';
            }
            $result .= '</div></li>';
        }
        $result .='</ul>';
        return $result;
    }

    public static function wpbootstrap_default_grid_xml() {
    	//Set default grid from xml file   		
    	//Emerson: Revision, use get_template_directory() instead of get_template_directory_uri() 
    	//to prevent errors like failed to open stream: HTTP request failed! HTTP/1.1 403 Forbidden
    	
        $file = file_get_contents(get_template_directory() . '/theme-options/bootstrap-grid/resources/layout-grid.xml');
        
        if ($file == false) {
            $parent_theme = get_option('_site_transient_theme_roots');
            $parent_theme = key(array_slice($parent_theme, 1, 1));
            $file = file_get_contents(get_site_url() . '/wp-content/themes/' . $parent_theme . '/theme-options/bootstrap-grid/resources/layout-grid.xml');
        }
        if ($file == true) {
            $grid_array = XML2Array::createArray($file);
            add_option('bootstrap_xml_grid_list', $grid_array["GridList"], '', 'yes');
        }
    }

    public static function wpbootstrap_grid_list() {// show default grid list
        $default_grid_list_xml = get_option('bootstrap_xml_grid_list', '');
        $result = '';
        if ($default_grid_list_xml == '') {
            WPBT_grid_xml::wpbootstrap_default_grid_xml();
        } else {
            $result = WPBT_grid_xml::wpbootstrap_Array2Grid($default_grid_list_xml);
        }
        return $result;
    }
    //Conditional loading function
    function check_if_to_be_loaded_grid() {
    
    	$screen_output_bootstrap= get_current_screen();
    	$current_screen_loaded=$screen_output_bootstrap->id;
    	$current_screen_base=$screen_output_bootstrap->base;
    	$loaded_post_editor_type=get_post_type();
    	$dont_load_bt_icl=array('view','product','types_page_wpcf-edit');
    
    	if ($loaded_post_editor_type) {
    		//This is a post type, check if not Views , View template or Types edit page
    		if (!(in_array($current_screen_loaded,$dont_load_bt_icl))) {
    
    			if ($current_screen_base=='edit') {
    
    				return FALSE;
    				 
    			} else {
    				return TRUE;
    				 
    			}
    
    		} else {
    
    			return FALSE;
    
    		}
    
    	} elseif ($current_screen_loaded=='appearance_page_options-framework') {
    
    		return TRUE;
    
    	} else {
    
    		return FALSE;
    	}
    
    }    

}