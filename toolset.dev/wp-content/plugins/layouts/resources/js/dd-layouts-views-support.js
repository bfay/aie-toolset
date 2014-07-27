// dd-layouts-views-support.js

var DDLayout = DDLayout || {};

DDLayout.layouts_views_support = function($)
{
    var self = this;

    self.init = function( ) {
        // If this file is included then Views will be running in an iframe
        
        // Hide the admin menu.
        
        $('#adminmenuback').hide();
        $('#adminmenuwrap').hide();
        $('#wpadminbar').hide();
        $('#wpcontent').css({'margin-left' : '10px'});
        // hide the footer
        $('#wpfooter').hide();
        
        window.parent.DDLayout.views_in_iframe.views_frame_ready();
        
        self._handle_save_state();

        if (window.location.href.indexOf('page=view-archives-editor') != -1) {         

            // Add buttons to the toolbar for pagination.        
            var toolbar = $('.js-wpv-settings-content .js-code-editor-toolbar ul');
            toolbar.append('<li><button class="button-secondary js-code-editor-toolbar-button js-ddl-older-posts-button"><i class="icon-chevron-left"></i><span class="button-label">Older posts</span></button></li>');
            toolbar.append('<li><button class="button-secondary js-code-editor-toolbar-button js-ddl-newer-posts-button"><i class="icon-chevron-right"></i><span class="button-label">Newer posts</span></button></li>');
            
            $('.js-ddl-older-posts-button').on('click', self._add_prev_shortcode);
            $('.js-ddl-newer-posts-button').on('click', self._add_next_shortcode);
        }

        if (!window.parent.DDLayout.views_in_iframe.is_new_cell() && $('.js-wpv-content-template-open').length == 1) {
            _.delay(function() {
                $('.js-wpv-content-template-open').trigger('click');
            }, 1000);
        }
        
    }

    self._add_prev_shortcode = function () {
        window.wpcfActiveEditor = 'wpv_content';
		window.icl_editor.insert('[ddl-pager-prev-page][wpml-string context="ddl-layouts"]Older posts[/wpml-string][/ddl-pager-prev-page]');
    }

    self._add_next_shortcode = function () {
        window.wpcfActiveEditor = 'wpv_content';
		window.icl_editor.insert('[ddl-pager-next-page][wpml-string context="ddl-layouts"]Newer posts[/wpml-string][/ddl-pager-next-page]');
    }

    self._handle_save_state = function () {
        // Tell the main window when it's OK to close the iframe
        setInterval( function () {
            window.parent.DDLayout.views_in_iframe.enable_ifame_close(!$('.js-wpv-view-save-all').prop('disabled'));
        }, 500);
    }

    self.save_view = function (callback) {
        if(!$('.js-wpv-view-save-all').prop('disabled')) {
            $('.js-wpv-view-save-all').click();

            var timer_id = setInterval( function () {
            
                if ($('.js-wpv-section-unsaved').length == 0) {
                    clearInterval(timer_id);
                    callback();
                }
            }, 500);
        } else {
            callback();
        }
    }
    
    self.init();
    
}

jQuery(document).ready(function($) {
    DDLayout.layouts_views = new DDLayout.layouts_views_support($);
});

