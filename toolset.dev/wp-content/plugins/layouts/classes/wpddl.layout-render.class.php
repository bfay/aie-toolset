<?php

class WPDD_layout_render {

    protected $layout;
    protected $child_renderer;
    protected $output;
    protected $offset = 0; //set offset member to 0
    protected $current_layout;
    protected $current_row_mode;
    protected $is_child;
    protected $layout_args = array();


    function __construct($layout, $child_renderer = null){
        $this->layout = $layout;
        $this->child_renderer = $child_renderer;
        $this->output = '';
        $this->current_layout = array($layout);

        $this->current_row_mode = array();

        $this->is_child = false;
    }

    function render_child() {
        if ($this->child_renderer) {
            return $this->child_renderer->render_to_html(false);
        } else {
            return '';
        }
    }

    function render_to_html($render_parent = true) {

        if ($render_parent) {
            $parent_layout = $this->layout->get_parent_layout();
            $this->is_child = false;
        } else {
            $parent_layout = false;
            $this->is_child = true;
        }

        if ($parent_layout) {
            $manager = new WPDD_layout_render_manager($parent_layout, $this);
            $parent_render = $manager->get_renderer( );
            $parent_render->set_layout_arguments($this->layout_args);
            return $parent_render->render_to_html();
        } else {
            $this->layout->frontend_render($this);
            return $this->output;
        }
    }

    function row_start_callback( $cssClass, $layout_type = 'fixed', $cssId = '', $additionalCssClasses = '', $tag = 'div', $mode = 'normal') {
        $this->offset = 0; // reset offset at the beginning of the row

        // if this is not a top level row then we should force full width.
        if (sizeof($this->current_row_mode) > 0 || $this->is_child) {
            $mode = 'full-width';
        }

        array_push($this->current_row_mode, $mode);

        $type = '';
        switch ($layout_type) {
            case 'fixed':
            case '';
                $type = '';
                break;

            default:
                $type = '-'.$layout_type;
                break;
        }

        ob_start();

        switch($mode) {
            case 'normal':
                ?>
                <div class="container">
                <<?php echo $tag; ?> class="<?php echo 'row'.$type; if( $additionalCssClasses ) {echo ' '.$additionalCssClasses;} ?>"<?php if( $cssId ) { echo ' id="' . $cssId .'"'; }?>>
                <?php
                break;

            case 'full-width-background':
                ?>
                <<?php echo $tag; ?> class="<?php if( $additionalCssClasses ) {echo $additionalCssClasses;} ?>"<?php if( $cssId ) { echo ' id="' . $cssId .'"'; }?>>
                <div class="container">
                <div class="<?php echo 'row'.$type; ?>">
                <?php
                break;

            case 'full-width':
                ?>
                <<?php echo $tag; ?> class="<?php echo 'row'.$type; if( $additionalCssClasses ) {echo ' '.$additionalCssClasses;} ?>"<?php if( $cssId ) { echo ' id="' . $cssId .'"'; }?>>
                <?php
                break;
        }

        $this->output .= ob_get_clean();
    }

    function row_end_callback($tag = 'div') {
        $mode = end($this->current_row_mode);

        switch($mode) {
            case 'normal':
                $this->output .= '</' . $tag . '>';
                $this->output .= '</div>';
                break;

            case 'full-width-background':
                $this->output .= '</div>';
                $this->output .= '</div>';
                $this->output .= '</' . $tag . '>';
                break;

            case 'full-width':
                $this->output .= '</' . $tag . '>';
                break;
        }

        array_pop($this->current_row_mode);
    }

    function cell_start_callback($cssClass, $width, $cssId = '', $tag = 'div') {

        $this->output .= '<' . $tag . ' class="' . $this->get_class_name_for_width($width);
        if ($cssClass) {
            $this->output .= ' ' . $cssClass;
        }
        $this->output .= $this->set_cell_offset_class().'"';

        if( $cssId )
        {
            $this->output .= 'id="' . $cssId .'"';
        }

        $this->output .= '>';
    }

    function get_class_name_for_width ($width) {
        return 'span' . (string)$width;
    }

    function cell_end_callback($tag = 'div') {
        $this->output .= '</' . $tag . '>';
        $this->offset = 0; //reset offset after the cell is rendered
    }

    function cell_content_callback($content) {
        $this->output .= $content;
    }

    function theme_section_content_callback($content)
    {
        $this->output .= $content;
    }

    function spacer_start_callback($width){
        $this->offset += $width; //keep track of the spaces and calculate offset for following content cell
    }
    function set_cell_offset_class( )
    {
        $offset_class = '';

        if( $this->offset > 0 )
        {
            switch( $this->layout->get_css_framework() )
            {
                case 'bootstrap':
                    $offset_class .= ' offset'.$this->offset;
                    break;
                case 'bootstrap3':
                    $offset_class .= ' offset-'.$this->offset;
                    break;
                default:
                    $offset_class .= ' offset'.$this->offset;
                    break;
            }
        }
        return $offset_class;
    }

    function push_current_layout($layout) {
        array_push($this->current_layout, $layout);
    }

    function pop_current_layout() {
        array_pop($this->current_layout);
    }

    function get_row_count() {
        $last = end($this->current_layout);
        return $last->get_row_count();
    }

    function make_images_responsive ($content) {
        return $content;
    }

    function set_property( $property, $value )
    {
        if( is_numeric($property) )
        {
           throw new InvalidArgumentException('Property should be valid string and not a numeric index. Input was: '.$property);
        }
        $this->{$property} = $value;
    }

    function set_layout_arguments( $args ) {
        $this->layout_args = $args;
    }

    function get_layout_arguments( $property ) {
        if (isset($this->layout_args[$property])) {
            return $this->layout_args[$property];
        } else {
            return null;
        }
    }

    function is_layout_argument_set( $property )
    {
        return isset( $this->layout_args[$property] );
    }

    function render( )
    {
        $content = $this->render_to_html();
        return do_shortcode( $content );
    }
}

// for rendering presets in the new layout dialog
class WPDD_layout_preset_render extends WPDD_layout_render {

    function __construct($layout){
        parent::__construct($layout);
    }

    function cell_start_callback($cssClass, $width, $cssId = '', $tag = 'div') {

        return parent::cell_start_callback($cssClass . ' holder', $width, $cssId, $tag);
    }

    function get_class_name_for_width ($width) {
        return 'span-preset' . (string)$width;
    }

    function row_start_callback( $cssClass, $layout_type = 'fixed', $cssId = '', $additionalCssClasses = '', $tag = 'div', $mode = 'normal') {
        $row_count = $this->get_row_count();
        $additionalCssClasses .= ' row-count-' . $row_count;
        $this->offset = 0; // reset offset at the beginning of the row

        $this->output .= '<' . $tag . ' class="row-fluid ' . $additionalCssClasses . '">';

    }

    function row_end_callback($tag = 'div') {
        $this->output .= '</' . $tag . '>';
    }


}

class WPDD_BootstrapTwo_render extends WPDD_layout_render
{

    function __construct($layout, $child_layout = null){

        parent::__construct($layout, $child_layout);
    }

    function get_class_name_for_width ($width) {
        return 'span' . (string)$width;
    }

    function set_cell_offset_class( )
    {
        $offset_class = '';

        if( $this->offset > 0 )
        {
            $offset_class .= ' offset'.$this->offset;
        }
        return $offset_class;
    }

    function row_start_callback( $cssClass, $layout_type = 'fixed', $cssId = '', $additionalCssClasses = '', $tag = 'div', $mode = 'normal') {
        $this->offset = 0; // reset offset at the beginning of the row

        // if this is not a top level row then we should force full width.
        if (sizeof($this->current_row_mode) > 0) {
            $mode = 'full-width';
        }

        array_push($this->current_row_mode, $mode);

        $type = '';
        switch ($layout_type) {
            case 'fixed':
            case '';
                if ($mode == 'full-width' && count($this->current_row_mode) == 1) {
                    $type = '-fluid';
                } else {
                    $type = '';
                }
                break;

            default:
                $type = '-'.$layout_type;
                break;
        }

        ob_start();

        switch($mode) {
            case 'normal':
                ?>
                <div class="container">
                <<?php echo $tag; ?> class="<?php echo 'row'.$type; if( $additionalCssClasses ) {echo ' '.$additionalCssClasses;} ?>"<?php if( $cssId ) { echo ' id="' . $cssId .'"'; }?>>
                <?php
                break;

        case 'full-width-background':
            ?>
            <<?php echo $tag; ?> class="<?php if( $additionalCssClasses ) {echo $additionalCssClasses;} ?>"<?php if( $cssId ) { echo ' id="' . $cssId .'"'; }?>>
            <div class="container">
            <div class="<?php echo 'row'.$type; ?>">
            <?php
            break;

            case 'full-width':
                ?>
                <<?php echo $tag; ?> class="<?php echo 'row'.$type; if( $additionalCssClasses ) {echo ' '.$additionalCssClasses;} ?>"<?php if( $cssId ) { echo ' id="' . $cssId .'"'; }?>>
                <?php
                break;
        }

        $this->output .= ob_get_clean();
    }

}


class WPDD_BootstrapThree_render extends WPDD_layout_render
{

    function __construct($layout, $child_layout = null){

        parent::__construct($layout, $child_layout);
    }

    function row_start_callback( $cssClass, $layout_type = '', $cssId = '', $additionalCssClasses = '', $tag = 'div', $mode = 'normal') {
        parent::row_start_callback($cssClass, '', $cssId, $additionalCssClasses, $tag, $mode);
    }

    function get_class_name_for_width ($width) {

        // Set column to sm. This will causes cells to be stacked on mobile devices
        // and then becomes horizontal on tablets and desktops.
        $ret ='col-sm-'.(string)$width;

        return $ret;
    }

    function set_cell_offset_class( )
    {
        $offset_class = '';

        if( $this->offset > 0 )
        {
            $offset_class .= ' col-sm-offset-'.$this->offset;
        }
        return $offset_class;
    }

    function make_images_responsive ($content) {

        $regex = '/<img[^>]*?/siU';
        if(preg_match_all($regex, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $image) {
                $image = $image[0];
                $regex = '/<img[^>]*?class="([^"]*)"/siU';
                if(preg_match_all($regex, $image, $image_match, PREG_SET_ORDER)) {
                    foreach ($image_match as $val) {
                        // add img-responsive to the class.
                        $new_image = str_replace($val[1], $val[1] . ' img-responsive', $val[0]);
                        $content = str_replace($val[0], $new_image, $content);
                    }
                } else {
                    // no class attribute on img. we need to add one.
                    $new_image = str_replace('<img ', '<img class="img-responsive" ', $image);
                    $content = str_replace($image, $new_image, $content);
                }
            }
        }

        return $content;
    }

}

class WPDD_layout_render_manager{

    private $layout = null;
    private $child_renderer = null;

    public function __construct($layout, $child_renderer = null)
    {
        $this->layout = $layout;
        $this->child_renderer = $child_renderer;
    }

    public function get_renderer( )
    {
        $framework = $this->layout->get_css_framework();

        $renderer = null;

        switch( $framework )
        {
            case 'bootstrap-2':
                $renderer = new WPDD_BootstrapTwo_render(  $this->layout, $this->child_renderer );
                break;
            case 'bootstrap-3':
                $renderer = new WPDD_BootstrapThree_render(  $this->layout, $this->child_renderer );
                break;
            default:
                $renderer = new WPDD_BootstrapThree_render(  $this->layout, $this->child_renderer );
        }

        return apply_filters('get_renderer',$renderer, $this);
    }
}