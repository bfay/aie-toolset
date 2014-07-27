<?php

function register_bs_site_title_init() {
    if ( function_exists('register_dd_layout_cell_type') ) {
        register_dd_layout_cell_type (
            'bs-site-title-cell',
            array ( 'name' => __('Theme Site Title', 'wpbootstrap'),
                    'description' => __('BootStrap Theme Site Title.', 'wpbootstrap'),
                    'category' => __('Theme cells', 'wpbootstrap'),
                    'category-icon-url' => get_template_directory_uri() . '/theme-options/bootstrap-grid/img/icon-16-insert-grid.png',
                    'button-text' => __('Assign Theme Site Title Box', 'wpbootstrap'),
                    'dialog-title-create' => __('Create a new Theme Site Title Cell', 'wpbootstrap'),
                    'dialog-title-edit' => __('Edit Theme Site Title Cell', 'wpbootstrap'),
                    'dialog-template-callback' => 'bs_site_title_dialog_template_callback',
                    'cell-content-callback' => 'bs_site_title_cell_content_callback',
                    'cell-template-callback' => 'bs_site_title_cell_template_callback'
                  )
        );
    }
}
add_action( 'init', 'register_bs_site_title_init' );


function bs_site_title_cell_content_callback($cell_data) {
    ob_start();
    ?>
    
        <h1 class="site-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
        </h1>
        <h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
        
    <?php
    return ob_get_clean();
}

function bs_site_title_dialog_template_callback() {
    return '';
}

function bs_site_title_cell_template_callback() {
    return '';
}

