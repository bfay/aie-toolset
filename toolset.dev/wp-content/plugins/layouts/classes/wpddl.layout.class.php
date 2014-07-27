<?php

// This is used to create layouts from PHP.

class WPDD_layout {

    private $rows;
    private $width;
    private $name;
    private $parent_layout_name;
    private $post_id;
    private $post_slug;
    private $cssframework;

    function __construct($width, $cssframework = 'bootstrap'){
        global $wpddlayout;

        $this->rows = array();
        $this->width = $width;
        $this->name = '';
        $this->parent_layout_name = '';
        $this->post_id = 0;
        $this->post_slug = '';
        $this->cssframework = $wpddlayout->get_css_framework();
    }

    function add_row($row) {
        if ($row->get_layout_type() == 'fixed' && ($row->get_width() != $this->width)) {
            global $wpddlayout;
            $wpddlayout->record_render_error(__('The row width is different from the layout width. This happens when the child layout does not contain the same number of columns as the child placeholder in the parent layout.', 'ddl-layouts'));
        }
        $this->rows[] = $row;
    }

    function get_width() {
        return $this->width;
    }

    function get_css_framework()
    {
        return $this->cssframework;
    }

    function get_json() {
        return json_encode($this->get_as_array());
    }

    function get_as_array() {
        $rows_array = array();
        foreach($this->rows as $row) {
            $rows_array[] = $row->get_as_array();
        }

        return array('Rows' => $rows_array);
    }

    function frontend_render($target) {
        $target->push_current_layout($this);
        foreach($this->rows as $row) {
            $row->frontend_render($target);
        }
        $target->pop_current_layout($this);
    }

    function set_name($name) {
        $this->name = $name;
    }
    function get_name() {
        return $this->name;
    }

    function set_parent_name($parent) {
        $this->parent_layout_name = $parent;
    }
    function get_parent_name() {
        return $this->parent_layout_name;
    }
    function get_parent_layout() {
        global $wpddlayout;

        return $wpddlayout->get_layout($this->parent_layout_name);
    }

    function set_post_id ($id) {
        $this->post_id = $id;
    }

    function get_post_id () {
        return $this->post_id;
    }

    function set_post_slug ($slug) {
        $this->post_slug = $slug;
    }

    function get_post_slug () {
        return $this->post_slug;
    }

    function get_width_of_child_layout_cell() {

        foreach($this->rows as $row) {
            $child_width = $row->get_width_of_child_layout_cell();
            if ($child_width > 0) {
                return $child_width;
            }
        }

        return 0;

    }

    function get_row_count() {
        return sizeof($this->rows);
    }

    function get_children() {
        global $wpddlayout;

        $children = array();

        $layout_list = $wpddlayout->get_layout_list();

        foreach($layout_list as $layout_id) {
            $layout = $wpddlayout->get_layout_settings($layout_id, true);
            if ($layout) {
                if ( property_exists ( $layout , 'parent' ) && $layout->parent == $this->get_post_slug()) {
                    $children[] = $layout_id;
                }
            }

        }

        return $children;

    }
}

// Base class for all elements

class WPDD_layout_element {
    private $name;
    private $css_class_name;
    private $editor_visual_template_id;

    function __construct($name, $css_class_name = '', $editor_visual_template_id = '', $css_id = '', $tag = 'div' ) {
        $this->name = $name;
        $this->css_class_name = $css_class_name;
        $this->editor_visual_template_id = $editor_visual_template_id;
        $this->css_id = $css_id;
        if (!$tag) {
            $tag = 'div';
        }
        $this->tag = $tag;
    }

    function get_as_array() {
        return array(
            'name' => $this->name,
            'cssClass' => $this->css_class_name,
            'cssId' => $this->css_id,
            'editorVisualTemplateID' => $this->editor_visual_template_id,
            'kind' => null
        );
    }
    function get_name() {
        return $this->name;
    }

    function get_css_class_name() {
        return $this->css_class_name;
    }
    function get_css_id()
    {
        return $this->css_id;
    }
    function get_tag() {
        return $this->tag;
    }

    function getKind()
    {
        $obj = (object) $this->get_as_array();
        return $obj->kind;
    }

}

class WPDD_layout_row extends WPDD_layout_element {

    private $cells;
    function __construct($name, $css_class_name = '', $editor_visual_template_id = '', $layout_type = 'fixed', $css_id = '', $additionalCssClasses = '', $tag = 'div', $mode = 'normal') {
        parent::__construct($name, $css_class_name, $editor_visual_template_id, $css_id, $tag);
        $this->cells = array();
        $this->additionalCssClasses = $additionalCssClasses;
        $this->set_layout_type( $layout_type );
        $this->mode = $mode;
    }

    function add_cell($cell) {
        $this->cells[] = $cell;
    }

    function get_additional_css_classes()
    {
        return $this->additionalCssClasses;
    }

    function get_as_array() {
        $data = parent::get_as_array();

        $cells_array = array();
        foreach($this->cells as $cell) {
            $cells_array[] = $cell->get_as_array();
        }

        $data['kind'] = 'Row';
        $data['Cells'] = $cells_array;
        $data['layout_type'] = $this->get_layout_type();
        $data['additionalCssClasses'] = $this->get_additional_css_classes();
        $data['mode'] = $this->get_mode();
        return $data;
    }

    function get_mode () {
        return $this->mode;
    }

    function get_width() {
        $width = 0;
        foreach($this->cells as $cell) {
            $width += $cell->get_width();
        }

        return $width;
    }

    function frontend_render($target) {
        $target->row_start_callback( $this->get_css_class_name(), $this->get_layout_type(), $this->get_css_id(), $this->get_additional_css_classes(), $this->get_tag(), $this->get_mode());

        foreach($this->cells as $cell) {
            $cell->frontend_render($target);
        }

        $target->row_end_callback($this->get_tag());
    }

    function set_layout_type( $layout_type )
    {
        $this->layout_type = $layout_type;
    }

    function get_layout_type( )
    {
        return $this->layout_type;
    }

    function get_width_of_child_layout_cell() {
        foreach ($this->cells as $cell) {
            $width = $cell->get_width_of_child_layout_cell();
            if ($width > 0) {
                return $width;
            }
        }
        return 0;
    }

    public function get_cells()
    {
        return $this->cells;
    }
}

class WPDD_layout_cell extends WPDD_layout_element {

    private $width;
    private $content;
    private $cell_type;

    function __construct($name, $width, $css_class_name = '', $editor_visual_template_id = '', $content = null, $css_id = '', $tag = 'div' ) {

        parent::__construct($name, $css_class_name, $editor_visual_template_id, $css_id, $tag);

        $this->width = $width;
        $this->content = $content;
        $this->cell_type = null;
    }

    function set_content($content) {
        $this->content = $content;
    }

    function get_content() {
        return $this->content;
    }

    function get_width() {
        return $this->width;
    }

    function set_cell_type($cell_type) {
        $this->cell_type = $cell_type;
    }

    function get_cell_type()
    {
        return $this->cell_type;
    }

    function get_as_array() {
        $data = parent::get_as_array();

        $data['kind'] = 'Cell';
        $data['width'] = $this->width;
        $data['content'] =  $this->content;
        $data['cell_type'] = $this->cell_type;

        return $data;
    }

    function get($param) {
        if (isset($this->content[$param])) {
            return $this->content[$param];
        } else {
            return null;
        }
    }

    function frontend_render($target) {
        $target->cell_start_callback($this->get_css_class_name(), $this->width, $this->get_css_id(), $this->get_tag() );

        $this->frontend_render_cell_content($target);

        $target->cell_end_callback($this->get_tag());
    }

    function frontend_render_cell_content($target) {
    }

    function get_width_of_child_layout_cell() {
        return 0;
    }
}

class WPDD_layout_container extends WPDD_layout_cell {

    private $layout;

    function __construct($name, $width, $css_class_name = '', $editor_visual_template_id = '', $css_id = '', $tag = 'div', $cssframework = 'bootstrap' ) {
        parent::__construct($name, $width, $css_class_name, $editor_visual_template_id, null, $css_id, $tag);
        $this->layout = new WPDD_layout( $width, $cssframework);
    }

    function add_row($row) {
        if ($row->get_layout_type() == 'fixed' && ($row->get_width() != $this->layout->get_width())) {
            global $wpddlayout;
            $wpddlayout->record_render_error(__('The row width is different from the layout width. This happens when the child layout does not contain the same number of columns as the child placeholder in the parent layout.', 'ddl-layouts'));
        }
        $this->layout->add_row($row);
    }

    function get_width() {
        return $this->layout->get_width();
    }


    function get_as_array() {
        $data = parent::get_as_array();

        $data['kind'] = 'Container';
        $data = array_merge($data, $this->layout->get_as_array());

        return $data;
    }

    function frontend_render_cell_content($target) {
        $this->layout->frontend_render($target);
    }

    function get_width_of_child_layout_cell() {
        return $this->layout->get_width_of_child_layout_cell();
    }
}

class WPDD_layout_spacer extends WPDD_layout_element {

    private $width;
    private $_preset_mode;

    function __construct($name, $width, $css_class_name = '', $css_id = '', $preset_mode = false) {
        parent::__construct($name, $css_class_name, $css_id);
        $this->width = $width;
        $this->_preset_mode = $preset_mode;
    }

    function get_width() {
        return $this->width;
    }

    function get_as_array() {
        $data = parent::get_as_array();

        $data['kind'] = 'Cell';
        $data['width'] = $this->width;
        $data['cell_type'] = 'spacer';

        return $data;
    }

    function frontend_render($target) {
        if ($this->_preset_mode) {
            // render as a div for display in the preset selection on new layouts dialog.
            $target->cell_start_callback($this->get_css_class_name(), $this->width, $this->get_css_id(), 'div' );

            $target->cell_content_callback($this->get_name());

            $target->cell_end_callback('div');
        } else {
            $target->spacer_start_callback( $this->width );
        }
    }

    function get_width_of_child_layout_cell() {
        return 0;
    }

}

// Cell factory class to be extended
class WPDD_layout_cell_factory {

    public function get_editor_cell_template() {
        // return an empty cell template if this function is not
        // overriden

        return '';
    }

    public function element_name($param) {
        // returns the name of the input element used in the dialog
        return 'ddl-layout-' . $param;
    }

}

// register the container
class WPDD_layout_container_factory extends WPDD_layout_cell_factory{

    public function get_cell_info($template) {
        $template['icon-css'] = 'icon-table';
        $template['preview-image-url'] = WPDDL_RES_RELPATH . '/images/grid.png';
        $template['name'] = __('Grid (for other cells)', 'ddl-layouts');
        $template['description'] = __('A grid contains rows and cells.', 'ddl-layouts');
        $template['button-text'] = __('Assign a Grid', 'ddl-layouts');
        $template['dialog-title-create'] = __('Create new Grid', 'ddl-layouts');
        $template['dialog-title-edit'] = __('Edit Grid', 'ddl-layouts');
        $template['dialog-template'] = $this->_dialog_template();
		$template['category'] = __('Grids', 'ddl-layouts');
        return $template;
    }

    private function _dialog_template() {
        global $wpddl_features;
        $hide = $wpddl_features->is_feature('fixed-layout') ? '' : ' class="hidden" ';
        ob_start();
        ?>
        <ul class="ddl-form">
            <li>
                <fieldset <?php echo $hide;?> >
                    <legend><?php _e('Grid type:', 'ddl-layouts'); ?></legend>
                    <div class="fields-group ddl-form-inputs-inline">
                        <label class="radio" for="cell_nested_type_fixed">
                            <input type="radio" name="cell_nested_type" class="js-layout-type-selector js-layout-type-selector-fixed" id="cell_nested_type_fixed" value="fixed" checked>
                            <?php _e('Fixed', 'ddl-layouts'); ?>
                        </label>
                        <label class="radio" for="cell_nested_type_fluid">
                            <input type="radio" name="cell_nested_type" class="js-layout-type-selector js-layout-type-selector-fluid" id="cell_nested_type_fluid" value="fluid">
                            <?php _e('Fluid', 'ddl-layouts'); ?>
                        </label>
                        <p class="toolset-alert toolset-alert-info js-diabled-fixed-rows-info">
                            <?php _e('Only fluid rows are allowed here because the parent row or layout are fluid.', 'ddl-layouts'); ?>
                        </p>
                    </div>
                </fieldset>
                <p class="desc js-grid-fixed-message"><?php _e('In fixed-width mode, the width of the grid determines the number of columns.', 'ddl-layouts'); ?></p>
            </li>
            <li class="js-fluid-grid-designer">
                <fieldset>
                    <legend><?php _e('Grid size', 'ddl-layouts'); ?>:</legend>
                    <div class="fields-group">
                        <div id="js-fluid-grid-slider-horizontal" class="horizontal-slider"></div>
                        <div id="js-fluid-grid-slider-vertical" class="vertical-slider"></div>
                        <div class="grid-designer-wrap">
                            <div class="grid-info-wrap">
                                <span id="js-fluid-grid-info-container" class="grid-info"></span>
                            </div>
                            <div id="js-fluid-grid-designer" class="grid-designer"
                                 data-rows="2"
                                 data-cols="4"
                                 data-max-cols="12"
                                 data-max-rows="4"
                                 data-slider-horizontal="#js-fluid-grid-slider-horizontal"
                                 data-slider-vertical="#js-fluid-grid-slider-vertical"
                                 data-info-container="#js-fluid-grid-info-container"
                                 data-message-container="#js-fluid-grid-message-container"
                                 data-fluid="true">
                            </div>
                        </div>
                        <div id="js-fluid-grid-message-container"></div>
                    </div>
                </fieldset>
            </li>
            <li class="js-fixed-grid-designer">
                <fieldset>
                    <legend><?php _e('Choose number of rows', 'ddl-layouts'); ?></legend>
                    <div class="fields-group">
                        <div id="js-fixed-grid-slider-vertical" class="vertical-slider"></div>
                        <div class="grid-designer-wrap">
                            <div class="grid-info-wrap">
                                <span id="js-fixed-grid-info-container" class="grid-info"></span>
                            </div>
                            <div id="js-fixed-grid-designer" class="grid-designer"
                                 data-rows="2"
                                 data-max-rows="4"
                                 data-slider-vertical="#js-fixed-grid-slider-vertical"
                                 data-info-container="#js-fixed-grid-info-container"
                                 data-message-container="#js-fixed-grid-message-container">
                            </div>
                        </div>
                        <div id="js-fixed-grid-message-container"></div>
                    </div>
                </fieldset>
            </li>
            <li class="extra-top">

                <span></span><a class="fieldset-inputs" href="<?php echo WPDLL_LEARN_ABOUT_GRIDS; ?>" target="_blank">
                    <?php _e('Learn about creating and using grids', 'ddl-layouts'); ?> &raquo;
                </a></span>
            </li>
        </ul>

        <?php
        return ob_get_clean();
    }

}

// include real cell types

require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_text.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_slider.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_video.class.php';

add_filter('dd_layouts_register_cell_factory', 'dd_layouts_register_container_factory');
function dd_layouts_register_container_factory($factories) {
    $factories['ddl-container'] = new WPDD_layout_container_factory;
    return $factories;
}

require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_post_content.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_views_content_template.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_post_loop.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_views_loop.class.php';

require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_menu.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_widget.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_widget_area.class.php';

require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.cell_views-grid-cell.class.php';
require_once WPDDL_CLASSES_ABSPATH . '/cell_types/wpddl.child_layout.class.php';

//require_once WPDDL_ABSPATH . '/reference-cell/reference-cell.php';


// Some test code

function test_layout_code() {
    $layout_test = new WPDD_layout(12);
    $row = new WPDD_layout_row('row 1');
    for ($i = 0; $i < 12; $i++) {
        $cell = new WPDD_layout_cell('cell:' . (string)($i + 1), 1);
        $row->add_cell($cell);
    }
    $layout_test->add_row($row);
    $json = $layout_test->get_json();
}

//test_layout_code();