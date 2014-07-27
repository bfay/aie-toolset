<?php
// ddl-fields-api.php

function the_ddl_field($field_name) {
    echo get_ddl_field($field_name);
}

function get_ddl_field($field_name) {
    global $ddl_fields_api;
    return $ddl_fields_api->get_field($field_name);
}

function has_ddl_repeater($group_name) {
    global $ddl_fields_api;
    return $ddl_fields_api->has_repeater($group_name);
}

function the_ddl_repeater($group_name) {
    global $ddl_fields_api;
    return $ddl_fields_api->the_repeater($group_name);
}

function the_ddl_repeater_index($group_name = null) {
    echo get_ddl_repeater_index($group_name);
}

function get_ddl_repeater_index($group_name = null) {
    global $ddl_fields_api;
    return $ddl_fields_api->the_repeater_index($group_name);
}

function the_ddl_sub_field($field_name) {
    echo get_ddl_sub_field($field_name);
}

function get_ddl_sub_field($field_name) {
    global $ddl_fields_api;
    return $ddl_fields_api->get_sub_field($field_name);
}

function ddl_rewind_repeater($group_name) {
    global $ddl_fields_api;
    $ddl_fields_api->rewind($group_name);
}

global $ddl_repeats;
$ddl_repeats = array();

function ddl_repeat_start($group_name, $button_text, $max_items = -1) {
	global $ddl_repeats;

	array_push($ddl_repeats, array('group' => $group_name,
								   'button' => $button_text));

	?>
		<div class="js-repeat-field-container ddl-repeat-field-container" data-max-items="<?php echo $max_items; ?>">
			<div class="ddl-repeat-field js-ddl-repeat-field" name="<?php echo md5($button_text);?>">
				<div class="ddl-repeat-field-toolbar">
					<i class="icon-resize-vertical js-ddl-repeat-field-move"></i>
					<i class="icon-remove js-ddl-repeat-field-remove"></i>

				</div>
	<?php
}

function ddl_repeat_end() {
	global $ddl_repeats;
	$group_info = array_pop($ddl_repeats);
	$button_text = $group_info['button'];
	?>
			</div> <!-- .js-ddl-repeat-field -->
		</div> <!-- .js-repeat-field-container -->
		<div class="ddl-form-button-wrap">
			<button class="button button-secondary js-ddl-repeat-field-button" >
				<i class="icon-plus"></i> <?php echo $button_text; ?>
			</button>
		</div>
	<?php

}

function get_ddl_name_attr($name) {
	global $ddl_repeats;

	if (sizeof($ddl_repeats)) {
		$group_info = end($ddl_repeats);
		$name = '[' . $group_info['group'] . ']' . $name . '[]'; // Add array indicator
	}

	return 'ddl-layout-' . $name;
}

function the_ddl_name_attr($name) {
	echo get_ddl_name_attr($name);
}

function get_ddl_cell_info($name) {
	global $wpddlayout;

	$info = $wpddlayout->get_current_cell_info();

	if (isset($info[$name])) {
		return $info[$name];
	} else {
		'';
	}
}

function the_ddl_cell_info($name) {
	echo get_ddl_cell_info($name);
}


class DDLFieldsAPI {


    function set_current_cell_content($content) {
        $this->content = $content;
        $this->repeaters = array();
        $this->current_group = '';
    }


    function get_field($field_name) {
		if ( isset($this->content[$field_name]) ){
			return $this->content[$field_name];
		}
    }
    function has_repeater($group_name) {
        if (isset($this->repeaters[$group_name]['index'])) {
            if ($this->repeaters[$group_name]['count'] == 0) {
                return false;
            } else {
                return $this->repeaters[$group_name]['index'] + 1 < $this->repeaters[$group_name]['count'];
            }
        } else {

            if (isset($this->content[$group_name])) {
                $this->repeaters[$group_name] = array('index' => -1, 'count' => sizeof($this->content[$group_name]));
                return $this->repeaters[$group_name]['count'] > 0;
            } else {
                return false;
            }
        }
    }
    function the_repeater($group_name) {
        $this->current_group = $group_name;
        if (isset($this->repeaters[$group_name]['index'])) {
            $this->repeaters[$group_name]['index']++;
        }
    }

    function the_repeater_index($group_name = null) {
        if (!$group_name) {
            if ($this->current_group) {
                $group_name = $this->current_group;
            } else {
                return 0;
            }
        }
        if (isset($this->repeaters[$group_name]['index'])) {
            return $this->repeaters[$group_name]['index'];
        } else {
            return 0;
        }

    }

    function get_sub_field($field_name) {
        $temp = $this->content[$this->current_group][$this->repeaters[$this->current_group]['index']][$field_name];
        return $temp;
    }

    function rewind($group_name) {
        unset($this->repeaters[$group_name]);
    }

}

global $ddl_fields_api;

$ddl_fields_api = new DDLFieldsAPI();
