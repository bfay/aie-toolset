// Breadcrumbs.js

DDLayout.Breadcrumbs = function(layout)
{
	var self = this;
	self.parents = null;
    self._current_parent = '';

	self.init = function (layout) {

        self._current_parent = '';
		self.parents = Array();
        
		jQuery('#ddl-layout-settings-dialog .js-item-name').each( function () {
			self.parents[jQuery(this).data('layout-slug')] = jQuery(this).data('layout-id');
		});

		self.display_breadcrumbs(layout);

		jQuery(document).on('click', '.js-layout-parent-parent,.js-layout-parent', {}, function(event) {
			self._edit_parent_layout( this );
		});

	};

	self.display_breadcrumbs = function (layout) {

        jQuery('.js-layout-width-error').remove();
    
		var parent_layout = layout.get_parent_layout();
        if (parent_layout != self._current_parent) {
            
            self._current_parent = parent_layout;

            if (parent_layout != '') {
                var link = '<a href="#" class="js-layout-parent" data-layout-slug="' + parent_layout + '" data-post-name="' + parent_layout + '">' + self._get_post_title(parent_layout) + '</a> <span class="separator">&raquo;</span>';
                jQuery('.js-dd-layouts-breadcrumbs').html(link);
                jQuery('.dd-layouts-breadcrumbs').show();
    
                // get the grandparents.
    
                var data = {
                        layout_name : parent_layout,
                        action : 'get_layout_parents'
                };
    
                jQuery.ajax({
                        type:'post',
                        url:ajaxurl,
                        data:data,
                        success:function(response){
                            response = jQuery.parseJSON(response);
    
                            var new_element = jQuery('.js-layout-parent');
                            for (var i = 0; i < response.length; i++) {
                                new_element.before('<a class="breadcrumbs-line layout-parent-parent js-layout-parent-parent" data-post-name="' + response[i] + '" >' + self._get_post_title(response[i]) + '</a> <span class="separator">&raquo;</span> ');
                                new_element = jQuery('.js-layout-parent-parent').first();
                            }
    
    
                        },
                    });
    
                // Check for correct number of cells in parent child layout cell
                
                if (parent_layout != '' && layout.getType() == 'fixed') {
                    data = {
                            layout_name : layout.get_name(),
                            parent_layout_name: parent_layout,
                            parent_layout_title: self._get_post_title(parent_layout),
                            width: layout.get_width(),
                            action : 'check_for_parent_child_layout_width',
                    };
    
                    jQuery.ajax({
                            type:'post',
                            url:ajaxurl,
                            data:data,
                            success:function(response){
                                response = jQuery.parseJSON(response);
    
                                if ( response.error !== '' ) {
                                    jQuery('.js-ddl-message-container').wpvToolsetMessage({
                                        text: response.error,
                                        classname: 'js-layout-width-error',
                                        type: 'error',
                                        stay: true,
                                        close: true,
                                        onOpen: function() {
                                            jQuery('html').addClass('toolset-alert-active');
                                        },
                                        onClose: function() {
                                            jQuery('html').removeClass('toolset-alert-active');
                                        }
                                    });
                                }
    
                            },
                        });
                }
            } else {
                jQuery('.dd-layouts-breadcrumbs').hide();
                jQuery('.js-dd-layouts-breadcrumbs').empty();
            }
        }
	};

	self._edit_parent = function ( ) {
		self.current_parent = jQuery('.js-layout-parent').data('layout-slug');

		if (DDLayout.ddl_admin_page.is_save_required()) {

			dialog = DDLayout.DialogYesNoCancel(DDLayout_settings.DDL_JS.strings.save_required,
												DDLayout_settings.DDL_JS.strings.save_before_edit_parent,
												{'yes' : DDLayout_settings.DDL_JS.strings.save_layout_yes,
												'no' : DDLayout_settings.DDL_JS.strings.save_layout_no},
												function(result) {
													if (result == 'yes') {
														DDLayout.ddl_admin_page.save_layout(self._switch_to_parent(self.current_parent));
													} else if (result == 'no') {
														self._switch_to_parent(self.current_parent);
													}
												});

		} else {
			jQuery.colorbox.close();

			self._switch_to_parent(self.current_parent);
		}
	}

	self._switch_to_parent = function (name) {

		DDLayout.ddl_admin_page.switch_to_layout(self.parents[name]);

	}

	self._edit_parent_layout = function ( item ) {
		var name = jQuery(item).data('post-name');

		if (DDLayout.ddl_admin_page.is_save_required()) {

			dialog = DDLayout.DialogYesNoCancel(DDLayout_settings.DDL_JS.strings.save_required,
												DDLayout_settings.DDL_JS.strings.save_before_edit_parent,
												{'yes' : DDLayout_settings.DDL_JS.strings.save_layout_yes,
												'no' : DDLayout_settings.DDL_JS.strings.save_layout_no},
												function(result) {
													if (result == 'yes') {
														DDLayout.ddl_admin_page.save_layout(self._switch_to_parent(name));
													} else if (result == 'no') {
														self._switch_to_parent(name);
													}
												});

		} else {
			self._switch_to_parent(name);
		}
	}

	self._get_post_title = function (post_name) {
		var post_title = '';
		jQuery('#ddl-layout-settings-dialog .js-item-name').each( function () {
			if (jQuery(this).data('layout-slug') == post_name)  {
				post_title = jQuery(this).text();
			}
		});

		return post_title;
	}

	self.init(layout);
};