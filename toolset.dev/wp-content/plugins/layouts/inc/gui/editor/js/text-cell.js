// text-cell.js


jQuery(document).on('DLLayout.admin.ready', function($){

	DDLayout.TextCell = function($)
	{
		var self = this;
	
		self.init = function() {
			jQuery(document).on('cell-text.dialog-open', self._dialog_open);
			jQuery(document).on('cell-text.dialog-close', self._dialog_close);
			jQuery(document).on('cell-text.get-content-from-dialog', self._get_content_from_dialog);
		};

		self._get_content_from_dialog = function (event, content, dialog) {
			content['visual_mode'] = jQuery('#ddl-default-edit .wp-editor-wrap').hasClass('tmce-active');
		}
		
		self._dialog_open = function (event, content, dialog) {
			
			// disable full screen save.
			jQuery('#wp-fullscreen-save').hide();
			
			var visual_mode = content.visual_mode;
			if (typeof visual_mode  == 'undefined'){
				visual_mode = true;
			}
			if (visual_mode) {
				jQuery("#celltexteditor-tmce").trigger("click");
			} else {
				jQuery("#celltexteditor-html").trigger("click");
			}
			
			// We need special handling to install View forms as this uses colorbox.
			self._views_insert_form_function = window.wpv_insert_view_form_popup;
			window.wpv_insert_view_form_popup = self._wpv_insert_form_shortcode;
			
			// Add special handling for Types popup.
			self._wpcfFieldsEditorCallback_function = window.wpcfFieldsEditorCallback;
			window.wpcfFieldsEditorCallback = self._wpcfFieldsEditorCallback;
			
			if (dialog.is_new_cell()) {
				jQuery('[name="ddl-layout-responsive_images"]').prop('checked', true);
			}
			
		}

		self._wpv_insert_form_shortcode = function (id) {
			self._override_jquery_colorbox_functions();
			
			self._views_insert_form_function(id);
		}
		
		self._wpcfFieldsEditorCallback = function (fieldID , metaType, postID) {
			self._override_jquery_colorbox_functions();
			
			self._wpcfFieldsEditorCallback_function (fieldID , metaType, postID);
		}

		self._colorbox = function(params) {

			if (params['iframe']) {

				self._create_color_box_elements(true);
				
				jQuery('#ddl-colorbox-2').html('<iframe id="ddl-types-popup" src="' + params['href'] + '" width="' + params['width'] + '"></iframe>');
						
				self._position_popup(params);
				
			} else if (params['href']){
    
				jQuery.ajax({
						type:'post',
						url:params['href'],
						success:function(response){
			
							self._create_color_box_elements(false);				
	
							jQuery('#ddl-colorbox-2').html(response);
							
							jQuery('#ddl-colorbox-2 .js-dialog-close').on('click', self._colorbox_close)
							
							self._position_popup(params);
							
							if (params['onComplete']) {
								params['onComplete']();
							}
						},
					});
				}
				
				
		}

		self._create_color_box_elements = function (add_shadow) {
			jQuery('body').append('<div id="ddl-colorbox-2-overlay" class="ddl-colorbox-2-overlay">');
			jQuery('body').append('<div id="ddl-colorbox-2" class="ddl-colorbox-2">');

			var z_index = parseInt(jQuery('#colorbox').css('z-index')) + 1;
			jQuery('#ddl-colorbox-2-overlay').css({'z-index' : z_index});
			jQuery('#ddl-colorbox-2').css({'z-index' : z_index});
			
			if (add_shadow) {
				jQuery('#ddl-colorbox-2').css({	'box-shadow': '0 0 15px rgba(0, 0, 0, 0.4)' });
			}
			
			jQuery('#ddl-colorbox-2-overlay').on('click', function (event) {
				if (add_shadow) {
					jQuery('#ddl-colorbox-2').css({	'box-shadow': '0 0 15px #21759B' });
					_.delay(function () {jQuery('#ddl-colorbox-2').css({	'box-shadow': '0 0 15px rgba(0, 0, 0, 0.4)' });} , 500);
				} else {
					jQuery('#ddl-colorbox-2 .wpv-dialog').css({	'box-shadow': '0 0 15px #21759B' });
					_.delay(function () {jQuery('#ddl-colorbox-2 .wpv-dialog').css({	'box-shadow': '0 0 15px rgba(0, 0, 0, 0.4)' });} , 500);
				}
			})
		}

		self._position_popup = function (params) {
			var offset = jQuery('#ddl-default-edit .js-wpv-shortcode-post-icon-wpv-views').offset();
				
			jQuery('#ddl-colorbox-2').css({top : offset.top + 16,
										   left : offset.left,
										   width: params['width']});
		}
		
		self._insert_content = function (content) {
			window.wpcfActiveEditor = 'celltexteditor';
			self._icl_editor_insert_function(content);
		}
		
		self._colorbox_close = function () {
			self._close_popup();	

			self._restore_overrides();
		}
		
		self._restore_overrides = function() {
			
			jQuery.colorbox = self._jquery_colorbox_function;
			jQuery.colorbox.close = self._jquery_colorbox_close_function;
			jQuery.colorbox.resize = self._jquery_colorbox_resize_function;
			
			icl_editor.insert = self._icl_editor_insert_function;
		}
		
		self._close_popup = function () {
			jQuery('#ddl-colorbox-2').remove();
			jQuery('#ddl-colorbox-2-overlay').remove();
		}

		self._dialog_close = function (event) {
			// enable full screen save.
			jQuery('#wp-fullscreen-save').show();

			window.wpv_insert_view_form_popup = self._views_insert_form_function;
			window.wpcfFieldsEditorCallback = self._wpcfFieldsEditorCallback_function;
		}

		self._override_jquery_colorbox_functions = function () {
			
			// We're overriding colorbox so that it calls member functions
			// here instead. We then create our own colorbox.
			
			self._jquery_colorbox_close_function = jQuery.colorbox.close;
			self._jquery_colorbox_resize_function = jQuery.colorbox.resize;
			self._jquery_colorbox_function = jQuery.colorbox;
			
			jQuery.colorbox = self._colorbox;
			jQuery.colorbox.close = self._colorbox_close;
			jQuery.colorbox.resize = self._colorbox_resize;

			self._icl_editor_insert_function = icl_editor.insert;
			icl_editor.insert = self._insert_content;
			
		}
		
		self._colorbox_resize = function (params) {
			if (params['innerHeight']) {
				jQuery('#ddl-colorbox-2 #ddl-types-popup').each (function () {
					jQuery(this).css({height : params['innerHeight']});
				});
			}
		}
		
		self.init();
	};

	
    DDLayout.text_cell = new DDLayout.TextCell($);

});

