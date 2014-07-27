<?php
/**
 *
 * the_ddlayout
 *
 * Renders and echos the layout.
 *
 */

function the_ddlayout($layout = '', $args = array() ) {
    echo get_the_ddlayout($layout, $args);
}

/**
 * get_the_ddlayout
 *
 * Gets the layout
 *
 */

function get_the_ddlayout($layout = '', $args = array()) {

    global $wpddlayout;

    $queried_object = $wpddlayout->get_queried_object();
    $post = $wpddlayout->get_query_post_if_any( $queried_object);

    if( null !== $post && $post->post_type === 'page' )
    {
        $template = basename( get_page_template() );

        $wpddlayout->save_option(array('templates' => array($template => $layout)));
    }

    $content = $wpddlayout->get_layout_content_for_render( $layout, $args );

    return $content;
}

/**
 * @return bool
 * to be used in template files or with template redirect hook to check whether current page has a layout template
 */
function is_ddlayout_template( )
{
    global $wpddlayout;

    $temp = get_page_template();

    $pos = strrpos ( $temp , '/' );

    $template = substr ($temp , $pos+1 );

    return in_array( $template, $wpddlayout->templates_have_layout( array( $template => 'name') ) );
}

/**
 * generic version of the preceeding
 * @return bool
 */
function has_current_post_ddlayout_template( )
{
    global $template, $wpddlayout;
    $template = basename($template);
    return in_array( $template, $wpddlayout->templates_have_layout( array( $template => 'name') ) );
}

function is_ddlayout_assigned()
{
    global $post;

    $assigned_template = get_post_meta($post->ID, '_layouts_template', true);

    if( !$assigned_template ) return false;

    return $assigned_template !== 'none';
}

function ddlayout_set_framework ( $framework ) {
    $framework_manager = WPDD_Layouts_CSSFrameworkOptions::getInstance();

    $framework_manager->theme_set_framework( $framework );
}
