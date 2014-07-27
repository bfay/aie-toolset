<?php

function register_bs_nav_bar_init() {
    if ( function_exists('register_dd_layout_cell_type') ) {
        register_dd_layout_cell_type (
            'bs-nav-bar-cell',
            array ( 'name' => __('Theme Navigation Bar', 'wpbootstrap'),
                    'description' => __('BootStrap Theme Navigation Bar.', 'wpbootstrap'),
                    'category' => __('Theme cells', 'wpbootstrap'),
                    'category-icon-url' => get_template_directory_uri() . '/theme-options/bootstrap-grid/img/icon-16-insert-grid.png',
                    'button-text' => __('Assign Theme Navigation Bar Box', 'wpbootstrap'),
                    'dialog-title-create' => __('Create a new Theme Navigation Bar Cell', 'wpbootstrap'),
                    'dialog-title-edit' => __('Edit Theme Navigation Bar Cell', 'wpbootstrap'),
                    'dialog-template-callback' => 'bs_nav_bar_dialog_template_callback',
                    'cell-content-callback' => 'bs_nav_bar_cell_content_callback',
                    'cell-template-callback' => 'bs_nav_bar_cell_template_callback'
                  )
        );
    }
}
add_action( 'init', 'register_bs_nav_bar_init' );


function bs_nav_bar_cell_content_callback($cell_settings) {
    ob_start();
    $nav_bar_classes = wpbootstrap_get_nav_menu_classes();
    $nav_bar_classes = str_replace(' span12', '', $nav_bar_classes);

    ?>

    <div class="clearfix <?php echo $nav_bar_classes ?>">
        <div class="navbar-inner">

            <div class="container">

                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>

                <?php if ( of_get_option( 'navbar_title' ) ): ?>
                    <a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                <?php endif; ?>

                <div class="nav-collapse collapse">

                    <nav id="nav-main" role="navigation">
                        <?php
                        if ( has_nav_menu( 'header-menu' ) ) :
                            wp_nav_menu( array( 'theme_location' => 'header-menu', 'menu_class' => 'nav' ) );
                        else:
                            wp_nav_menu( array( 'menu_class' => 'nav', 'depth' => '1', 'walker' => null ) );
                        endif;
                        ?>
                    </nav> <!-- #nav-main -->

                    <?php if ( of_get_option( 'navbar_search' ) ): ?>
                    <form class="navbar-form pull-right" role="search" method="get" action="<?php echo home_url( '/' ); ?>">
                        <input type="text" name="s" id="s" class="input-medium">
                        <button type="submit" class="btn">Search</button>
                    </form><!-- .navbar-form -->
                    <?php endif; ?>

                </div>

        </div>
    </div>

    <?php
    return ob_get_clean();

}

function bs_nav_bar_dialog_template_callback() {
    return '';
}

function bs_nav_bar_cell_template_callback() {
    return '';
}