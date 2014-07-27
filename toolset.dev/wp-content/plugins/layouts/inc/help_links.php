<?php

define ( 'WPDDL_CSS_STYLING_LINK', 'http://wp-types.com/documentation/user-guides/using-html-css-style-layout-cells?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=css-styling-tab&utm_term=help-link' );
define ( 'WPDLL_LEARN_ABOUT_FIXED_AND_FLUID', 'http://wp-types.com/documentation/user-guides/learn-fluid-fixed-width-layouts?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=layout-settings&utm_term=help-link' );
define ( 'WPDLL_LEARN_ABOUT_SETTING_UP_TEMPLATE', 'http://wp-types.com/documentation/user-guides/adding-layout-support-theme-templates?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=post-editor&utm_term=help-link' );
define ( 'WPDLL_LEARN_ABOUT_ROW_MODES', 'http://wp-types.com/documentation/user-guides/learn-how-rows-can-displayed-different-ways?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=row-edit&utm_term=help-link' );
define ( 'WPDLL_LEARN_ABOUT_GRIDS', 'http://wp-types.com/documentation/user-guides/learn-creating-using-grids?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=grid-cell&utm_term=help-link' );
define ( 'WPDLL_RICH_CONTENT_CELL', 'http://wp-types.com/documentation/user-guides/rich-content-cell-text-images-html?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=visual-editor-cell&utm_term=help-link' );
define ( 'WPDLL_POST_CONTENT_CELL', 'http://wp-types.com/documentation/user-guides/post-content-cell?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=post-content-cell&utm_term=help-link' );
define ( 'WPDLL_LOOP_CONTENT_CELL', 'http://wp-types.com/documentation/user-guides/post-loop-cell?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=post-loop-cell&utm_term=help-link' );
define ( 'WPDLL_WIDGET_AREA_CELL', 'http://wp-types.com/documentation/user-guides/widget-area-cell?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=widget-area-cell&utm_term=help-link' );
define ( 'WPDLL_WIDGET_CELL', 'http://wp-types.com/documentation/user-guides/widget-cell?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=widget-cell&utm_term=help-link' );
define ( 'WPDLL_CHILD_LAYOUT_CELL', 'http://wp-types.com/documentation/user-guides/hierarchical-layouts?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=child-layout-cell&utm_term=help-link' );
define ( 'WPDLL_THEME_INTEGRATION_QUICK', 'http://wp-types.com/documentation/user-guides/layouts-theme-integration-quick-start-guide?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=framework-options&utm_term=help-link' );
define ( 'WPDLL_CONTENT_TEMPLATE_CELL', 'http://wp-types.com/documentation/user-guides/content-template-cell/?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=content-template-cell&utm_term=help-link' );
define ( 'WPDLL_VIEWS_CONTENT_GRID_CELL', 'http://wp-types.com/documentation/user-guides/views-content-grid?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=views-content-grid-cell&utm_term=help-link' );
define ( 'WPDLL_VIEWS_LOOP_CELL', 'http://wp-types.com/documentation/user-guides/views-post-loop-cell?utm_source=layoutsplugin&utm_campaign=layouts&utm_medium=views-post-loop-cell&utm_term=help-link' );

function ddl_add_help_link_to_dialog($link, $text) {
    ?>
        <p>
            <a href="<?php echo $link; ?>" target="_blank">
                <?php echo $text; ?> &raquo;
            </a>
        </p>
    <?php
}

