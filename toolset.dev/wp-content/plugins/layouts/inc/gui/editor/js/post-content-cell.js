// post-content-cell.js

// Handles both post content and Views Content Template cells.

DDLayout.PostContentCell = function($)
{
    var self = this;

    self.init = function() {

        self._cell_content = null;
        self._preview = {};

        // Handle the dialog open.

        jQuery(document).on('cell-post-content.dialog-open cell-content-template.dialog-open', function(e, content, dialog) {

            self._dialog = dialog;

            if (!jQuery('#ddl-default-edit input[name="ddl-layout-page"]:checked').length) {
                jQuery('#ddl-default-edit input[name="ddl-layout-page"]').each( function () {
                    if (jQuery(this).val() == 'current_page') {
                        jQuery(this).prop('checked', true);
                    }
                });
            }

            jQuery('#ddl-default-edit .js-ddl-post-content-post-type').off('change');
            jQuery('#ddl-default-edit .js-ddl-post-content-post-type').on('change', self._handle_post_type_change);

            jQuery('#ddl-default-edit input[name="ddl-layout-page"]').off('change');
            jQuery('#ddl-default-edit input[name="ddl-layout-page"]').on('change', self.adjust_specific_page_state);

            self._initialize_post_selector();

            var select_post_type = jQuery('#ddl-default-edit .js-ddl-post-content-post-type').val();
            if (select_post_type != jQuery('#ddl-default-edit #ddl-layout-selected_post').data('post-type')) {
                self._cell_content = content;
                jQuery('#ddl-default-edit .js-ddl-post-content-post-type').trigger('change');
            }

            self.adjust_specific_page_state();

        });
        jQuery(document).on('cell-post-content.dialog-close cell-content-template.dialog-close', function(e) {
            jQuery('#ddl-default-edit #ddl-layout-selected_post').select2('destroy');

        });

    };

    self._handle_post_type_change = function() {
        var data = {
            post_type : jQuery(this).val(),
            action : 'get_posts_for_post_content',
            nonce : jQuery(this).data('nonce')
        };

        var spinnerContainer = jQuery('<div class="spinner ajax-loader">').insertAfter(jQuery(this)).show();
        jQuery('#ddl-default-edit #ddl-layout-selected_post').hide();
        jQuery('#ddl-default-edit #ddl-layout-selected_post').select2('destroy');

        jQuery.ajax({
            type:'post',
            url:ajaxurl,
            data:data,
            success: function(response){ // TODO: success is deprecated http://api.jquery.com/jQuery.ajax/
                jQuery('#ddl-default-edit #ddl-layout-selected_post').replaceWith(response);
                if (self._cell_content) {
                    self._select_post(self._cell_content.selected_post);
                }
                spinnerContainer.remove();
                self._initialize_post_selector();
                jQuery('#ddl-default-edit #ddl-layout-selected_post').fadeIn(200);
                self._handle_post_select_change();
            }
        });
    };

    self.adjust_specific_page_state = function () {
        if (self.get_display_mode() == 'current_page') {
            jQuery('#ddl-default-edit #js-post-content-specific-page').hide();

            var disable_save = false;
            if (self._dialog.get_cell_type() == 'cell-content-template') {
                disable_save = !DDLayout.content_template_cell.is_save_ok();
            }
            self._dialog.disable_save_button(disable_save);
        } else {
            jQuery('#ddl-default-edit #js-post-content-specific-page').show();

            self._handle_post_select_change();
        }
    };

    self.get_display_mode = function () {
        if (jQuery('#ddl-default-edit input[name="ddl-layout-page"]:checked').length) {
            return jQuery('#ddl-default-edit input[name="ddl-layout-page"]:checked').val();
        } else {
            return '';
        }
    }

    self.get_selected_post = function () {
        return jQuery('#ddl-default-edit #ddl-layout-selected_post').val();
    }

    self._handle_post_select_change = function () {
        if (self.get_display_mode() == 'this_page') {
            if( self._dialog.get_cell_type() == 'cell-content-template' )
            {
                self._dialog.disable_save_button(self.get_selected_post() == '' ||
                    !DDLayout.content_template_cell.is_save_ok());
            }
            else
            {
                self._dialog.disable_save_button(self.get_selected_post() == '' );
            }
        }
    }

    self._initialize_post_selector = function () {
        jQuery('#ddl-default-edit #ddl-layout-selected_post').off('change');
        jQuery('#ddl-default-edit #ddl-layout-selected_post').on('change', self._handle_post_select_change);

        jQuery('#ddl-default-edit #ddl-layout-selected_post').select2({
            'width' : 'resolve'
        });
    }

    self._select_post = function (selected_post) {
        var select = jQuery('#ddl-default-edit #ddl-layout-selected_post');
        select.val(selected_post);
        if (select.val() != selected_post) {
            select.val('');
        }
    }


    self.get_preview = function ( content, current_text, specific_text, loading_text ){

        if (content.page == 'current_page') {
            return '<div>'+ current_text +'</div>';
        } else {
            var post_id = content.selected_post;
            var divclass = 'js-post_content-' + post_id;

            //Return if view data cached
            if ( typeof(self._preview[post_id]) !== 'undefined' && self._preview[post_id] != null){
                var out = '<div class="'+ divclass +'">'+ self._preview[post_id] +'</div>';
                return out;
            }

            //If view not cached, get data using Ajax
            var out = '<div class="'+ divclass +'">'+ loading_text +'</div>';

            if (typeof(self._preview[post_id]) == 'undefined') {

                self._preview[post_id] = null;

                var data = {
                    action : 'ddl_post_content_get_post_title',
                    post_id: post_id,
                    wpnonce : jQuery('#ddl_layout_view_nonce').attr('value')
                };
                jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: data,
                    cache: false,
                    success: function(data) {
                        //cache view id data
                        self._preview[post_id] = specific_text.replace('%s', '<strong>' + data + '</strong>');
                        jQuery('.' + divclass).html(self._preview[post_id]);

                    }
                });
            }

            return out;
        }
    }

    self.init();
};

jQuery(document).ready(function($){
	DDLayout.post_content_cell = new DDLayout.PostContentCell($);
});