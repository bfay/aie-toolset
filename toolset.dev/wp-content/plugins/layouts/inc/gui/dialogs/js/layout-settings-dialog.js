// layout-settings-dialog.js

DDLayout.LayoutSettingsDialog = function($)
{
    var self = this;

    self.init = function() {

        jQuery(document).on('click', '.js-edit-layout-settings', {}, function(event) {
            self.show();
        });
        
        jQuery('#ddl-layout-settings-dialog .js-save-dialog-settings').on('click', self._save_settings);

        jQuery('#ddl-layout-settings-dialog .js-item-name').on('click', self._select_parent);
        
    };

    self.show = function() {

        jQuery.colorbox({
            href: '#ddl-layout-settings-dialog',
            closeButton:false,
            onComplete: function() {
                jQuery('#ddl-layout-settings-dialog .js-diabled-fluid-rows-info').hide();
                
                self._layout = DDLayout.ddl_admin_page.get_layout();
                
                self._fill_width_select_with_available();
                
                // set the current layout type radio
                self._layout_type = self._layout.getType();
                jQuery('input[name="ddl-layout-settings-layout-type"]').each( function () {
					jQuery(this).prop('checked', jQuery(this).val() == self._layout_type);
                })
                
                // Disable changing to fluid if the layout has a container with fixed width.
                if (self._layout_type == 'fixed') {
                    jQuery('input[name="ddl-layout-settings-layout-type"]').prop('disabled', false);
                    var containers = self._layout.getLayoutContainers();
                    for (var i = 0; i < containers.length; i++) {
                        var container = containers[i];
                        if (container.hasRowsOfKind('fixed')) {
                            jQuery('input[name="ddl-layout-settings-layout-type"]').prop('disabled', true);
                            jQuery('#ddl-layout-settings-dialog .js-diabled-fluid-rows-info').show();
                            break;
                        }
                    }
                }
                
                self._handle_layout_type_change(self._layout_type);
                
                jQuery('input[name="ddl-layout-settings-layout-type"]').on('change', function () {
                    self._handle_layout_type_change(jQuery(this).val());
                });
                
                self._initialize_parent();
                
            },
            onCleanup: function() {
            }
        });
    };

    self._save_settings = function () {
        var selected_layout_type = jQuery('input[name="ddl-layout-settings-layout-type"]:checked').val();

        DDLayout.ddl_admin_page.take_undo_snapshot();
        
        var something_changed = false;
        
        // Save layout type
        if (selected_layout_type != self._layout_type) {
            
            self._layout.changeLayoutType(selected_layout_type);
            something_changed = true;
        }
        
        // Save width
        var new_width = jQuery('select[name="ddl-layout-width"]').val();
        if (selected_layout_type == 'fixed' && self._layout.get_width() != new_width) {
            
            self._layout.changeWidth(new_width);
            something_changed = true;
        }

        // Save parent
        var new_parent = jQuery('#ddl-layout-settings-dialog .js-item-name.selected').data('layout-slug');
        if (new_parent != self._layout.get_parent_layout()) {
        
            self._layout.set_parent_layout(new_parent);
            DDLayout.ddl_admin_page.render_all();
            something_changed = true;    
        }
        
        if (something_changed) {
            DDLayout.ddl_admin_page.add_snapshot_to_undo();
        }
        
        jQuery.colorbox.close();
    }
    
    self._handle_layout_type_change = function (layout_type) {
        if (layout_type == 'fluid') {
            jQuery('select[name="ddl-layout-width"]').val(12);
            jQuery('select[name="ddl-layout-width"]').prop('disabled', true);
            jQuery('.js-diabled-width').show();
        } else if (layout_type == 'fixed') {
            jQuery('select[name="ddl-layout-width"]').val(self._layout.get_width());
            jQuery('select[name="ddl-layout-width"]').prop('disabled', false);
            jQuery('.js-diabled-width').hide();
            self._fill_width_select_with_available();
        }
    }
    
    self._fill_width_select_with_available = function () {
        var min_width = self._layout.getMinWidth();
        jQuery('select[name="ddl-layout-width"] > option').each( function () {
            jQuery(this).prop('disabled', jQuery(this).val() < min_width);
        })
    }
    
    self._initialize_parent = function () {
        var parent = self._layout.get_parent_layout();
        
        jQuery('#ddl-layout-settings-dialog .js-item-name').each( function () {
            if (jQuery(this).data('layout-slug') == parent) {
                jQuery(this).addClass('selected');
            } else {
                jQuery(this).removeClass('selected');
            }
        })
        
    }
    
    self._select_parent = function (event) {

        jQuery('#ddl-layout-settings-dialog .js-item-name').removeClass('selected');
        jQuery(this).addClass('selected');

    }
    
    self.init();
};