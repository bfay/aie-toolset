// This code handles the Views Content Grid and the Post Loop when Views is enabled

var DDLayout = DDLayout || {};

DDLayout.ViewsGrid = function($)
{
    var self = this;
    
    self._save_required = false;
    self._current_cols = 0;

    self.init = function( initial_layout_json) {
        self._edit_new_view = false;
        self._views_list_options = null;
 	 
        $(document).on('views-content-grid-cell.get-content-from-dialog post-loop-views-cell.get-content-from-dialog', function(event, content, dialog){
            //Create new new
            if ( $('#ddl-default-edit .js-ddl-views-grid-create').prop('checked') ) {

                var cell_name = $('#ddl-default-edit #ddl-default-edit-cell-name').val();
                var current_cell = jQuery('#ddl-default-edit').data('cell_view');
                jQuery('#ddl-default-edit').find('#ddl-default-edit .ddl_existing_views_content').show(); 
                
                $thiz = $(this);
                var data = {
                    action : 'ddl_create_new_view',
                    wpnonce : $('#ddl_layout_view_nonce').attr('value'),
                    cell_name : cell_name,
                    cols: self._grid.data('cols')
                };
                
                var view_type = 'normal';

                if (self._cell_type == 'post-loop-views-cell') {
                    data['layouts-loop'] = 1;
                    view_type = 'layouts-loop';
                }
                
                var spinner = self._dialog.insert_spinner_after('#ddl-default-edit .js-create-and-edit-view').show();                
                $thiz.find('#ddl-default-edit select[name="ddl-layout-ddl_layout_view_id"] options').prop('checked',false);
                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: data,
                    cache: false,
                    async: false,
                    success: function(data) {
                        data = jQuery.parseJSON(data);
                        $thiz.find('#ddl-default-edit select[name="ddl-layout-ddl_layout_view_id"]').append( $("<option/>", {
                            value: data.id,
                            text: data.post_title,
                            'data-id': data.id,
                            'data-mode' : view_type
                        }));
                        
                        self._views_list_options.push($('#ddl-default-edit .js-ddl-view-select option').last());
    
                        content.ddl_layout_view_id = data.id;
                        
                        $thiz.find('#ddl-default-edit [name="view-grid-view-action"]').prop('checked',false);
                        $thiz.find('#ddl-default-edit .js-ddl-views-grid-existing').prop('checked',true);
                        DDLayout.ddl_admin_page.render_all();
                        
                        spinner.remove();
                    }
                });
               
            } else {
                
                if (self._current_cols != 0) {
                    var cols = self._grid.data('cols');
                    if (cols != self._current_cols) {
                        // we need to update the columns in the View
                        self._save_views_columns(cols);
                        DDLayout['views-preview-cache'] = {};
                    }
                }
                
            }
        });
        
        
        $(document).on('views-content-grid-cell.dialog-open post-loop-views-cell.dialog-open', function(event, content, dialog) {
            
            self._dialog = dialog;
            self._cell_type = dialog.get_cell_type();

            self._initialize_view_selector();
            
            self._grid = $('#ddl-default-edit .js-fluid-views-grid-designer');
            
            self._dialog_initialized = false;
            self._initial_view_selected = $('#ddl-default-edit [name="ddl-layout-ddl_layout_view_id"] option:checked').data('id');
            $('#ddl-default-edit .js-ddl-edit-view-link').show();

            
            if ( $('.js-views-content-grid_is_views_installed').val() == 0 ){
                $('.ddl-form').hide();
            }else{
            
                var $fluidGrid = jQuery('#ddl-default-edit .js-fluid-grid-designer');
                var $fixedGrid = jQuery('#ddl-default-edit .js-fixed-grid-designer');
            
                $fluidGrid.show();
                $fixedGrid.hide();
                
                self._create_new_grid(2);
                
                $('#ddl-default-edit .js-ddl-select-existing-view,#ddl-default-edit .js-fluid-grid-designer').hide();		

                if ( dialog.is_new_cell() && ddl_views_1_6_available){				
                    $('#ddl-default-edit .js-ddl-views-grid-create').prop('checked', true);
                    $('#ddl-default-edit .js-ddl-views-grid-existing').prop('checked', false);
                    $('#ddl-default-edit .js-create-and-edit-view').show();
                } else {
                    $('#ddl-default-edit .js-ddl-views-grid-create').prop('checked', false);
                    $('#ddl-default-edit .js-ddl-views-grid-existing').prop('checked', true);
                    $('#ddl-default-edit .js-create-and-edit-view').hide();
                }
                
                if ( $('#ddl-default-edit .js-ddl-views-grid-create').prop('checked') ){
                    $('#ddl-default-edit .js-fluid-grid-designer').show();
                    self._dialog.disable_save_button(true);
                    $('#ddl-default-edit .js-ddl-edit-view-link').hide();
                }
                if ( $('#ddl-default-edit .js-ddl-views-grid-existing').prop('checked') ){				
                    $('#ddl-default-edit .js-ddl-select-existing-view').show();						
                    self._handle_view_change(null, false);
                }
                
            }
            
            self._dialog_initialized = true;
            self._save_required = false;
            
           
            if (self._edit_new_view) {
                self._save_views_columns_and_open();
                self._edit_new_view = false;
            }

        
            $('#ddl-default-edit .js-ddl-edit-view-link').off('click');
            $('#ddl-default-edit .js-ddl-edit-view-link').on('click', self._save_views_columns_and_open);
			
			if (!ddl_views_1_6_available) {
				jQuery('.js-dialog-edit-save').prop('disabled', true);
			}
            
        
        });
        
        $(document).on('views-content-grid-cell.dialog-close post-loop-views-cell.dialog-close', function(event, content, dialog) {
            if ( $('.js-views-content-grid_is_views_installed').val() == 0 ){
                $('.ddl-form').show();
            }
            $('.js-dialog-edit-save,.ui-tabs-nav').prop('disabled',false);
            
            jQuery(window).off('beforeunload.views-grid-cell');
            
            self._restore_view_selector();
            
        });

        $(document).on('views-content-grid-cell.dialog-closed post-loop-views-cell.dialog-closed', function(event, content, dialog) {
            if (self._edit_new_view) {
                DDLayout.ddl_admin_page.show_default_dialog('edit', dialog.get_target_cell_view());
            }
        });        
        
        $(document).on('click', '#ddl-default-edit .js-ddl-views-grid-create, #ddl-default-edit .js-ddl-views-grid-existing', function(e) {
            $('#ddl-default-edit .js-ddl-select-existing-view,#ddl-default-edit .js-fluid-grid-designer').hide();		
            if ( $('#ddl-default-edit .js-ddl-views-grid-create').prop('checked') ){
                self._show_grid_designer_new_mode()
                $('#ddl-default-edit .js-fluid-grid-designer').show();
                $('#ddl-default-edit .js-dll-edit-view-link-section').hide();
                $('#ddl-default-edit .js-ddl-edit-view-link').hide();
                self._dialog.disable_save_button(true);
                $('#ddl-default-edit #ddl-default-edit-cell-name').focus();
            }
            if ( $('#ddl-default-edit .js-ddl-views-grid-existing').prop('checked') ){				
                $('#ddl-default-edit .js-ddl-select-existing-view').show();
                $('#ddl-default-edit .js-dll-edit-view-link-section').show();
                $('#ddl-default-edit .js-ddl-view-select').trigger('change');
                $('#ddl-default-edit .js-ddl-edit-view-link').show();
            }
        });
        
        $(document).on('change', '#ddl-default-edit .js-ddl-view-select', self._handle_view_change);
        
        $(document).on('click', '#ddl-default-edit .js-create-and-edit-view', function (e) {
            self._edit_new_view = true;
            self._dialog.disable_save_button(false);
            $('.js-dialog-edit-save').trigger('click');
        });

    }

	self._handle_view_change = function (event, async) {
		if (async === null) {
			async = true;
		}
		
		var view_selected = $('#ddl-default-edit .js-ddl-view-select').val();
		self._dialog.disable_save_button(view_selected == '');
		self._disable_edit_view_button(view_selected == '');

		if (self._dialog_initialized) {
			
			// The views selected has changed
			// stop the browser from navigating away without a warning.
			
			jQuery(window).on('beforeunload.views-grid-cell', function(){
				return DDLayout_settings.DDL_JS.strings.page_leave_warning;
			});
			
		}

		$('#ddl-default-edit .js-dll-edit-view-link-section').hide();
		
		if ( typeof($('#ddl-default-edit [name="ddl-layout-ddl_layout_view_id"] option:checked').data('id')) !== 'undefined'){
			$('#ddl-default-edit .js-dll-edit-view-link-section').show();
		}

		// Get the View settings.
		
		var data = {
			action : 'ddl_get_settings_for_view',
			wpnonce : $('#ddl_layout_view_nonce').attr('value'),
			view_id : self._get_view_selected(),
		};
		self._hide_grid_designer();
		$('#ddl-default-edit .js-ddl-edit-view-link-first').hide();
		
		var spinner = self._dialog.insert_spinner_after('#ddl-default-edit .js-ddl-view-select').show();                
		
		$.ajax({
			url: ajaxurl,
			type: 'post',
			data: data,
			cache: false,
			async: async,
			success: function(data) {
				data = jQuery.parseJSON(data);
				
				if ( $('#ddl-default-edit .js-ddl-views-grid-existing').prop('checked') ){				
				
					if ('grid_settings' in data) {
						self._create_new_grid( data['grid_settings'] );

						self._show_grid_designer_edit_mode();
						self._current_cols = data['grid_settings'];
						
						if (!ddl_views_1_6_available) {
							$('.js-fluid-views-grid-slider-horizontal').hide();
						}
						
					} else {
						self._current_cols = 0;
						$('#ddl-default-edit .js-ddl-edit-view-link-first').show();
					}
					
				}
				
				spinner.remove();
			}
		});
	}
	
    self._disable_edit_view_button = function (state) {
        $('#ddl-default-edit .js-ddl-edit-view-link').prop('disabled', state);
    }
    self._save_views_columns_and_open = function () {
        
        if (self._current_cols != 0) {
            var cols = jQuery('#ddl-default-edit .js-fluid-views-grid-designer').data('cols');
            if (cols != self._current_cols) {
                // we need to update the columns in the View
                self._save_views_columns(cols);
            }
        }
        
        DDLayout.views_in_iframe.open_view_in_iframe( $('#ddl-default-edit [name="ddl-layout-ddl_layout_view_id"] option:checked').data('id'),
                                                     self._cell_type,
                                                     self._edit_new_view);
    }
    
    self._show_grid_designer_edit_mode = function () {
        $('#ddl-default-edit .js-fluid-grid-designer').show();
        $('#ddl-default-edit .js-ddl-edit-view-link-first').hide();
        $('#ddl-default-edit .js-create-and-edit-view').hide();
    }
        
    self._show_grid_designer_new_mode = function () {
        self._create_new_grid(2)

        $('#ddl-default-edit .js-fluid-grid-designer').show();
        $('#ddl-default-edit .js-create-and-edit-view').show();
    }
        
    self._hide_grid_designer = function () {
        $('#ddl-default-edit .js-fluid-grid-designer').hide();
    }
        
    self._save_views_columns = function (cols) {
        
        var spinner = self._dialog.insert_spinner_after('#ddl-default-edit .js-ddl-edit-view-link:visible').show();                
        
        var data = {
            action : 'ddl_save_view_columns',
            wpnonce : $('#ddl_layout_view_nonce').attr('value'),
            view_id : self._get_view_selected(),
            cols: cols
        };
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: data,
            cache: false,
            async: false,
            success: function(data) {
                
                spinner.remove();
                
                data = jQuery.parseJSON(data);
                
                if ('error' in data) {
                    alert(data['error']);
                }
                
                
            }
        });
        
    }
    
    self._get_view_selected = function () {
        return $('#ddl-default-edit [name="ddl-layout-ddl_layout_view_id"] option:checked').val();
    }
    
    self._create_new_grid = function ( cols ) {
        self._grid.ddlDrawGrid('destroy');
        self._grid = $('#ddl-default-edit .js-fluid-views-grid-designer');
        self._grid.data('cols', cols);
        self._grid.ddlDrawGrid();
    }
    
    self._initialize_view_selector = function () {
        // We need to only show Views for normal or loops depending on the cell type
        // hiding options don't work in ie so we need to remove and add options

        var selected = $('#ddl-default-edit .js-ddl-view-select option:checked').val();
        
        if (!self._views_list_options) {
            
            self._views_list_options = Array();
            
            $('#ddl-default-edit .js-ddl-view-select option').each( function () {
                self._views_list_options.push($(this));
                
                $(this).detach();
            });
        }
        
        $('#ddl-default-edit .js-ddl-view-select option').each( function () {
            $(this).remove();
        });
        
        for (var i = 0; i < self._views_list_options.length; i++) {
            var mode = self._views_list_options[i].data('mode');
            
            if (self._cell_type == 'views-content-grid-cell' && mode != 'layouts-loop') {
                $('#ddl-default-edit .js-ddl-view-select').append(self._views_list_options[i].clone());
            }
            
            if (self._cell_type == 'post-loop-views-cell' && mode != 'normal') {
                $('#ddl-default-edit .js-ddl-view-select').append(self._views_list_options[i].clone());
            }
        }
        
        $('#ddl-default-edit .js-ddl-view-select').val(selected);
        
    }

    self._restore_view_selector = function () {

        var selected = $('#ddl-default-edit .js-ddl-view-select option:checked').val();
        
        $('#ddl-default-edit .js-ddl-view-select option').each( function () {
            $(this).remove();
        });
        
        for (var i = 0; i < self._views_list_options.length; i++) {
            $('#ddl-default-edit .js-ddl-view-select').append(self._views_list_options[i].clone());
        }
        
        $('#ddl-default-edit .js-ddl-view-select').val(selected);
        
    }
    
    self.iframe_has_closed = function () {
        
        // Check if the View title has changed.
        
        var view_id = self._get_view_selected();
        
        var data = {
            action : 'ddl_get_settings_for_view',
            wpnonce : $('#ddl_layout_view_nonce').attr('value'),
            view_id : view_id,
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: data,
            cache: false,
            success: function(data) {
                data = jQuery.parseJSON(data);
                
                if ('title' in data) {
                    $('.js-ddl-view-select option').each( function () {
                        if ($(this).val() == view_id) {
                            $(this).html(data['title']);
                        }
                    })
                }
            }
        });
        
    }
    
    self.init();
}

DDLayout.ViewsInIfame = function($)
{
    var self = this;

    self.init = function( ) {
            
    }
    
    self.open_view_in_iframe = function (view_id, cell_type, new_cell) {
        
        self._new_cell = new_cell;
        
        self.dialog_pos = $('#ddl-default-edit').parent().offset();
        self.dialog_width = $('#ddl-default-edit').width();
        
        content_height = $('#ddl-default-edit .ddl-dialog-content').height();
        $('#ddl-default-edit .ddl-dialog-header .js-edit-dialog-close ').hide();
        $('#ddl-default-edit .ddl-dialog-content').hide();
        $('#ddl-default-edit .ddl-dialog-footer button').hide();
        $('<button class="button js-close-view-iframe-no-save" style="display:none;margin-right:2px;">' + DDLayout_settings.DDL_JS.strings.close_view_iframe_without_save + '</button>')
            .appendTo('#ddl-default-edit .ddl-dialog-footer');
        $('<button class="button button-primary js-close-view-iframe">' + DDLayout_settings.DDL_JS.strings.close_view_iframe + '</button>')
            .appendTo('#ddl-default-edit .ddl-dialog-footer');
        $('.js-close-view-iframe').prop('disabled', true);
		$('<div style="display:block; height:' + content_height + 'px;" class="js-layouts-views-loading"><div class="spinner ajax-loader-bar" style="display:block; height:100%"></div></div>').insertAfter($('#ddl-default-edit .ddl-dialog-content')).show();
        
        var views_editor_type = ddl_views_1_6_embedded_available ? 'views-embedded' : 'views-editor';
        if (cell_type == 'post-loop-views-cell') {
            views_editor_type = ddl_views_1_6_embedded_available ? 'view-archives-embedded' : 'view-archives-editor';
        }
        $('<iframe id="ddl-layout-views-iframe" class="layouts-views-loading" style="display:none" width="100%" height="1200px" src="admin.php?page=' + views_editor_type + '&view_id=' + view_id + '&in-iframe-for-layout=1"></iframe>')
            .insertAfter('#ddl-default-edit .ddl-dialog-content');

        $('#ddl-default-edit').parent().css({'left' : self.dialog_pos.left - (980 - self.dialog_width) / 2 + 'px'});
        $('#ddl-default-edit').css({'width' : '980px'});
        
        $('#ddl-default-edit .js-close-view-iframe').on('click', self._close_iframe);

        $('#ddl-default-edit .js-close-view-iframe-no-save').on('click', self._close_iframe_without_saving);
        
        // Stop the enter key from closing the dialog
        $(document).off('keyup.colorbox');
        
    };
    
    self._close_iframe = function (e) {
        self._spinner = jQuery('<div class="spinner ajax-loader"></div>').insertBefore('#ddl-default-edit .js-close-view-iframe').show();
        
        // save the View if required
        document.getElementById("ddl-layout-views-iframe").contentWindow.DDLayout.layouts_views.save_view(self.save_view_complete);
	}
	
	self.save_view_complete = function () {
        
        self._spinner.remove();
        
        self._restore_dialog();
    }
    
    self._restore_dialog = function () {
        $('#ddl-layout-views-iframe').remove();
        $('#ddl-default-edit .js-close-view-iframe').off('click');
        $('#ddl-default-edit .js-close-view-iframe').remove();
        $('#ddl-default-edit .js-close-view-iframe-no-save').off('click');
        $('#ddl-default-edit .js-close-view-iframe-no-save').remove();
        
        $('#ddl-default-edit .ddl-dialog-header .js-edit-dialog-close ').show();
        $('#ddl-default-edit .ddl-dialog-content').show();
        $('#ddl-default-edit .ddl-dialog-footer button').show();
        
        $('#ddl-default-edit').parent().css({'left' : self.dialog_pos.left + 'px'});
        $('#ddl-default-edit').css({'width' : self.dialog_width + 'px'});
        
        // delete the Views cache for peviews
        DDLayout['views-preview-cache'] = {};
        
        DDLayout.views_grid.iframe_has_closed();
    }
    
    self._close_iframe_without_saving = function () {
        self._restore_dialog();
    }
    
    self.views_frame_ready = function () {
        $('#ddl-layout-views-iframe').show();
        $('.js-layouts-views-loading').hide().remove();
        $('#ddl-default-edit .js-close-view-iframe').prop('disabled', false);
    }
    
    self.enable_ifame_close = function (state) {
		
		if (!ddl_views_1_6_available) {
			// Save is not required if Views editor is not available
			state = false;
		}
		
        if (state != self._save_required) {
            if (state) {
                $('#ddl-default-edit .js-close-view-iframe').html(DDLayout_settings.DDL_JS.strings.save_and_close_view_iframe);
                self._save_required = true;
                $('#ddl-default-edit .js-close-view-iframe-no-save').show();
            } else {
                $('#ddl-default-edit .js-close-view-iframe').html(DDLayout_settings.DDL_JS.strings.close_view_iframe);
                self._save_required = false;
                $('#ddl-default-edit .js-close-view-iframe-no-save').hide();
            }
        }
    }
    
    self.is_new_cell = function () {
        return self._new_cell;
    }
    
    self.init();
}

jQuery(document).ready(function($) {
    DDLayout.views_grid = new DDLayout.ViewsGrid($);
    DDLayout.views_in_iframe = new DDLayout.ViewsInIfame($);
});


DDLayout['views-preview-cache'] = {};

function ddl_views_content_grid_preview( view_id, error_text, loading_text ){
	
    if ( view_id == '' ){
		return '<div>'+ error_text +'</div>';
	}else{
		var divclass = 'js-views-content-grid-'+view_id;
		var divplaceholder = '.'+divclass;
		
		//Return if view data cached
		if ( typeof(DDLayout['views-preview-cache'][view_id]) !== 'undefined' && DDLayout['views-preview-cache'][view_id] != null){
			var out = '<div class="'+ divclass +'">'+ DDLayout['views-preview-cache'][view_id] +'</div>';
			return out;
		} 
		
		//If view not cached, get data using Ajax
        var out = '<div class="'+ divclass +'">'+ loading_text +'</div>';
    
        if (typeof(DDLayout['views-preview-cache'][view_id]) == 'undefined') {
    
            DDLayout['views-preview-cache'][view_id] = null;
    
            var data = {
                    action : 'ddl_views_content_grid_preview',
                    view_id: view_id,
                    wpnonce : jQuery('#ddl_layout_view_nonce').attr('value')
            };
            jQuery.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: data,
                    cache: false,
                    success: function(data) {
                        //cache view id data
                        DDLayout['views-preview-cache'][view_id] = data;
                        jQuery(divplaceholder).html(data);
                        
                        // If we have received all the previews we need to refresh
                        // the layout display to re-calculate the heights.
                        
                        var all_previews_ready = true;
                        for (var key in DDLayout['views-preview-cache']) {
                             if (DDLayout['views-preview-cache'].hasOwnProperty(key)) {
                                  if (DDLayout['views-preview-cache'][key] == null) {
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

		return out; 
	}
}