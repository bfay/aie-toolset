<?php

function register_bs_footer_init() {
    if ( function_exists('register_dd_layout_cell_type') ) {
        register_dd_layout_cell_type (
            'bs-footer-cell',
            array ( 'name' => __('Theme Footer', 'wpbootstrap'),
                    'description' => __('BootStrap Theme Footer.', 'wpbootstrap'),
                    'category' => __('Theme cells', 'wpbootstrap'),
                    'category-icon-url' => get_template_directory_uri() . '/theme-options/bootstrap-grid/img/icon-16-insert-grid.png',
                    'button-text' => __('Assign Theme Footer Box', 'wpbootstrap'),
                    'dialog-title-create' => __('Create a new Footer Cell', 'wpbootstrap'),
                    'dialog-title-edit' => __('Edit Footer Cell', 'wpbootstrap'),
                    'dialog-template-callback' => 'bs_footer_dialog_template_callback',
                    'cell-content-callback' => 'bs_footer_cell_content_callback',
                    'cell-template-callback' => 'bs_footer_cell_template_callback'
                  )
        );
    }
}
add_action( 'init', 'register_bs_footer_init' );
    
function bs_footer_cell_content_callback($cell_settings) {
    ob_start();
    ?>
        <?php
            if ( $cell_settings['show_widgets'] == 'true' ) {
                get_sidebar('footer-widgets');
            }
        ?>

        <?php do_action( 'wpbootstrap_before_footer' ); ?>
        <?php if ( wpbootstrap_get_setting('general_settings', 'display_footer' ) ): ?>
            <?php if (of_get_option('display_credit_footer')) : ?>
            <footer id="footer" class="muted">
                <p class="pull-left"><?php echo of_get_option('display_credit_footer_left'); ?></p>
                <p class="pull-right"><?php echo of_get_option('display_credit_footer_right'); ?></p>
            </footer>
            <?php endif; ?>
        <?php endif; ?>
        <?php do_action( 'wpbootstrap_after_footer' ); ?>
        
    <?php
    $content = ob_get_clean();

    return $content;    
}

function bs_footer_cell_template_callback() {
    return '';
}

function bs_footer_dialog_template_callback() {
    ob_start();
    ?>

        <p>
            <?php // allow the user to choose if the widgets should be shown. ?>
            <?php $checkbox_name = get_ddl_name_attr('show_widgets'); ?>
            <label for="<?php echo $checkbox_name; ?>"><?php _e('Show footer widgets', 'wpbootstrap'); ?></label>
            <input type="checkbox" name="<?php echo $checkbox_name; ?>">
        </p>
        
    <?php
    
    return ob_get_clean();
}

