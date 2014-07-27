// content-template-cell.js
jQuery(document).ready(function($){
	DDLayout.content_template_cell = new DDLayout.ContentTemplateCell($);
});

DDLayout.ContentTemplateCell = function($)
{
    var self = this;

    self.init = function() {

        self._ct_editor = null;
        self._ct_code_mirror = null;

        self._preview_cache = {};

        self._cell_content = null;

        jQuery('.js-ct-name').on('click', self._switch_to_edit_ct_name);

        jQuery('.js-create-new-ct').on('click', self._create_new_ct);

        jQuery('.js-ct-edit-name').on('blur', self._end_ct_name_edit);

        jQuery('.js-load-different-ct').on('click', self._switch_to_select_different_ct)

        jQuery('#post-content-view-template').on('change', self._handle_ct_change);

        // Handle the dialog open.

        jQuery(document).on('cell-content-template.dialog-open', function(e, content, dialog) {

			self._dialog = dialog;
			
            DDLayout.types_views_popup_manager.start();

            self._original_ct_name = '';
            self._original_ct_value = ''

	        jQuery('.js-ct-edit').hide();

			//jQuery('.js-post-content-ct').show();
			//jQuery('#post-content-view-template').trigger('change');
			
			if (dialog.is_new_cell()) {
				self._create_new_ct();
			} else {
				// Show the current Content Template
				jQuery('#post-content-view-template').trigger('change');
			}
			
			if (!ddl_views_1_6_available) {
				jQuery('.js-dialog-edit-save').prop('disabled', true);
			}
			
        });
        jQuery(document).on('cell-content-template.dialog-close', function(e) {

            self._close_codemirror();

            DDLayout.types_views_popup_manager.end();

        });

        jQuery(document).on('cell-content-template.get-content-from-dialog', function(e, content, dialog) {
            self._save_ct(content);
        });



    };

    self._handle_ct_change = function() {
        if (jQuery(this).val() == 'None') {
			self._dialog.disable_save_button(true);
        } else {
            self._dialog.disable_save_button(!self._is_post_selected_ok());

			var ct_id = jQuery(this).find('option:selected').data('ct-id');
			var ct_name = jQuery(this).find('option:selected').text();
			
            if (jQuery('.js-create-new-ct').length > 0) {

                // Only show CT editor if Views plugin is available.

                self._open_ct_editor(ct_id, ct_name);
                jQuery('.js-ct-selector').hide();
                jQuery('.js-ct-edit').hide();
            } else {
				self._show_ct_preview(ct_id, ct_name)
			}
			
        }
    };


    self._save_ct = function (content) {
        if (ddl_views_1_6_available && self._ct_editor) {

            var ct_title = jQuery('.js-ct-edit-name').val();
            var ct_value = self._ct_code_mirror.getValue();

            self._preview_cache[content.ddl_view_template_id] = ct_value;

            if (self._original_ct_name != ct_title || self._original_ct_value != ct_value) {

                var data = {
                    action : 'wpv_ct_update_inline',
                    ct_value : ct_value,
                    ct_id : self._ct_editor,
                    ct_title : ct_title,
                    wpnonce : $('#wpv-ct-inline-edit').attr('value')
                };
                $.post(ajaxurl, data, function(response) {

                    if (self._original_ct_name != ct_title) {
                        // we need to refresh the ct drop down.
                        self._refresh_ct_dropdown(0);
                    }


                });
            }
        }

    }

    self._refresh_ct_dropdown = function (select_id) {
        var data = {
            action : 'dll_refresh_ct_list',
            wpnonce : $('#wpv-ct-inline-edit').attr('value')
        };
        $.post(ajaxurl, data, function(response) {

            jQuery('.js-ct-select-box').html(response);

            if (select_id) {
                jQuery('#post-content-view-template option').each( function () {
                    if (jQuery(this).data('ct-id') == select_id) {
                        jQuery('#post-content-view-template').val(jQuery(this).val());
                    }
                })
            }

            jQuery('#post-content-view-template').on('change', self._handle_ct_change);
        });
    }

    self._setup_ct_mode = function () {
		var no_ct_selected = jQuery('#post-content-view-template').val() == 0;

		if (no_ct_selected) {
			jQuery('.js-ct-edit').hide();
			jQuery('.js-ct-selector').show();
		}

		self._dialog.disable_save_button(no_ct_selected || !self._is_post_selected_ok());

		if (jQuery('#post-content-view-template option').length == 1) {
			// Only the "None" option
			// Create a new CT automatically

			self._create_new_ct();

		}

    }

    self._close_codemirror = function () {
        self._ct_value = '';
        if (self._ct_editor) {
            self._ct_value = self._ct_code_mirror.getValue();
            icl_editor.codemirror('wpv-ct-inline-editor-' + self._ct_editor, false);
            self._ct_editor = null;
        }
    }

    self.display_post_content_info = function(content, current_text, specific_text,  loading_text) {
        var preview = '';

        if (content.ddl_view_template_id != 0) {
            preview += '<br />';

            var div_place_holder = 'js-content-template-preview-' + content.ddl_view_template_id;

            if (typeof (self._preview_cache[content.ddl_view_template_id]) !== 'undefined' && self._preview_cache[content.ddl_view_template_id] != null) {
                // get it from the cache.
                preview += '<div class="' + div_place_holder + '">' + self._preview_cache[content.ddl_view_template_id] + '</div>';
            } else {
                // create a place holder and fetch it.
                preview += '<div class="' + div_place_holder + '">' + loading_text + '</div>';

                if ( typeof (self._preview_cache[content.ddl_view_template_id]) == 'undefined' ) {
                    self._preview_cache[content.ddl_view_template_id] = null;

                    var data = {
                        action : 'ddl_content_template_preview',
                        view_template: content.ddl_view_template_id,
                        wpnonce : $('#wpv-ct-inline-edit').attr('value'),
                    };
                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: data,
                        cache: false,
                        success: function(data) {
                            //cache view id data
                            self._preview_cache[content.ddl_view_template_id] = data;
                            jQuery(div_place_holder).html(data);

                            // If we have received all the previews we need to refresh
                            // the layout display to re-calculate the heights.

                            var all_previews_ready = true;
                            for (var key in self._preview_cache) {
                                if (self._preview_cache.hasOwnProperty(key)) {
                                    if (self._preview_cache[key] == null) {
                                        all_previews_ready = false;
                                    }
                                }
                            }

                            if (all_previews_ready) {
                                DDLayout.ddl_admin_page.render_all();
                            }
                        }
                    });

                }
            }
        }

		// Add the post content cell preview at the start.
		preview = DDLayout.post_content_cell.get_preview(content, current_text, specific_text,  loading_text) + preview;
        return preview;
    };

    self._open_ct_editor = function (id, name) {
        $('<div class="spinner ajax-loader-bar js-ct-loading">').insertBefore($('.js-ct-selector:first')).show();
        self._dialog.disable_save_button(true);

        if (id == 0) {
            // we need to create a new one
            data = {
                action : 'dll_add_view_template',
                ct_name : name,
                wpnonce : $('#wpv-ct-inline-edit').attr('value')
            };
            $.post(ajaxurl, data, function(response) {
                response = jQuery.parseJSON(response);
                id = response['id'];
                self._fetch_ct_and_show_editor(id, name, true, false);

                self._refresh_ct_dropdown(id);
            });

        } else {
            self._fetch_ct_and_show_editor(id, name, false, false);
        }
    }
	
	self._show_ct_preview = function (id, name) {
		self._fetch_ct_and_show_editor(id, name, false, true);
	}

    self._fetch_ct_and_show_editor = function (id, name, focus_on_name, preview_mode) {

        data = {
            action : preview_mode ? 'ddl_ct_loader_inline_preview' : 'wpv_ct_loader_inline',
            id : id,
            wpnonce : $('#wpv-ct-inline-edit').attr('value')
        };
		
        $.post(ajaxurl, data, function(response) {

            self._dialog.disable_save_button(!self._is_post_selected_ok());

            $('.js-wpv-ct-inline-edit').html(response).show().attr('id', "wpv_ct_inline_editor_" + id);
            $('.js-wpv-ct-inline-edit .js-wpv-ct-update-inline').remove();

            if( typeof cred_cred != 'undefined'){
                cred_cred.posts();
            }

            self._ct_editor = id;
			if (preview_mode) {
				self._ct_code_mirror = CodeMirror.fromTextArea(document.getElementById( 'wpv-ct-inline-editor-'+id ), {
					mode: "myshortcodes",
					lineNumbers: true,
					lineWrapping: true,
					//viewportMargin: Infinity
					readOnly: "nocursor"
				});
			} else {
				self._ct_code_mirror = icl_editor.codemirror('wpv-ct-inline-editor-'+id, true);
			}

            // Hide the "Media" button (it doesn't work at the moment)
            jQuery('.js-wpv-media-manager').hide();

            // Hide "CRED forms" button (it doesn't work at the moment)
            jQuery('.cred-form-shortcode-button2').hide();

            jQuery('.js-ct-edit-name').hide();
            jQuery('.js-ct-name').html(name);
            jQuery('.js-ct-edit-name').val(name);
            jQuery('.js-ct-edit').show();

            jQuery('.js-ct-loading').remove();

            self._original_ct_name = name;
            self._original_ct_value = self._ct_code_mirror.getValue();

            self._ct_code_mirror.refresh();

            DDLayout.types_views_popup_manager.set_position_and_target(
                jQuery('#ddl-default-edit .js-code-editor-toolbar-button-v-icon'),
                'wpv-ct-inline-editor-'+id);

            if (focus_on_name) {
                self._switch_to_edit_ct_name();
            }

        });


    }

    self._switch_to_ct_select_mode = function () {
        jQuery('.js-ct-selector').show();
        jQuery('.js-ct-editor').hide();
    }

    self._create_new_ct = function () {
		if (ddl_views_1_6_available) {
			jQuery('.js-ct-selector').hide();
			var name = self._get_unique_name(ddl_new_ct_default_name);
			self._open_ct_editor(0, name);
		}
    }

    self._get_unique_name = function (name) {
        var count = 0;
        name = name.replace('%s', DDLayout.ddl_admin_page.get_layout().get_name());
        var test_name = name;

        do {
            in_use = false;

            jQuery('#post-content-view-template option').each(function () {
                if (jQuery(this).html() == test_name) {
                    in_use=true;
                }
            });

            if (in_use) {
                count++;
                test_name = name + ' - ' + count;
            }
        } while (in_use);

        return test_name;
    }

    self._switch_to_edit_ct_name = function () {
        jQuery('.js-ct-editing').hide();
        jQuery('.js-ct-edit-name').val(jQuery('.js-ct-name').html());
        jQuery('.js-ct-edit-name').show().focus();
    }

    self._end_ct_name_edit = function () {
        jQuery('.js-ct-edit-name').hide();
        jQuery('.js-ct-name').html(jQuery('.js-ct-edit-name').val());
        jQuery('.js-ct-editing').show();
    }

    self._switch_to_select_different_ct = function () {
        jQuery('#post-content-view-template').val(0);
		self._dialog.disable_save_button(true);
        jQuery('.js-ct-edit').hide();
        self._close_codemirror();
        self._switch_to_ct_select_mode();
    }
	
	self.is_save_ok = function () {
		return jQuery('#post-content-view-template').val() != 0;
	}
	
	self._is_post_selected_ok = function () {
		return DDLayout.post_content_cell.get_display_mode() == 'current_page' ||
					DDLayout.post_content_cell.get_selected_post() != '';
	}

    self.init();
};