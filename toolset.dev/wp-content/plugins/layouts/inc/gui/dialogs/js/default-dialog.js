// default-dialog.js

// TODO: ADD JS PREFIXES
DDLayout.DefaultDialog = function($)
{
	var self = this;

	self.init = function() {
		self._cleanup_required = false;

		self._dialog_defaults = {};
		self._repeating_fields = new DDLayout.DialogRepeatingFields();

		jQuery(document).on('click', '.js-show-cell-dialog', {dialog: self}, function(event) {
			event.preventDefault();
			event.stopImmediatePropagation();
			event.data.dialog._show_new_dialog( this );
		});

		jQuery(document).on('click', '.js-dialog-edit-save', {dialog: self}, function(event) {
			event.preventDefault();
			event.stopImmediatePropagation();

			var $css_input_id = jQuery( 'input.js-edit-css-id', jQuery(this).parent().parent()),
				value = $css_input_id.val();

			if( DDLayout.ddl_admin_page.cssEditor.check_id_exists( $css_input_id, value ) )
			{
				event.data.dialog._save();
			}

		});

		jQuery(document).on('change', '.js-layout-type-selector', {dialog: self}, function(event) {
			event.preventDefault();
			event.stopImmediatePropagation();
			self._manage_layout_selection();
		});

        // this trigger on any dialog when closes up.
        WPV_Toolset.Utils.eventDispatcher.listenTo(WPV_Toolset.Utils.eventDispatcher, 'color_box_closed', self.generic_dialog_close_callback);
	};

    self.generic_dialog_close_callback = function( event )
    {
        if( jQuery('.js-element-box-message-container').is('p') )
        jQuery('.js-element-box-message-container').wpvToolsetMessage('destroy');
    };

	self._show_new_dialog = function ( caller ) {

		self._clear_any_errors();

		var cell_type = jQuery(caller).data('cell-type'),
			main_layout = DDLayout.ddl_admin_page.get_layout(),
			target_cell_view = DDLayout.ddl_admin_page.get_new_target_cell(),
			allow_multiples = jQuery(caller).data('allow-multiple'),
			cell_name = self._create_default_cell_name(jQuery(caller).data('cell-name'), main_layout);


        jQuery('.js-element-box-message-container').wpvToolsetMessage('destroy');

		if (allow_multiples === false) {
			var layout = DDLayout.ddl_admin_page.get_layout();
			if (layout.has_cell_of_type(cell_type)) {
				jQuery('.js-element-box-message-container').wpvToolsetMessage({
					text: DDLayout_settings.DDL_JS.strings.only_one_cell,
					stay: true,
					close: true,
					type: 'info'
				});
				return;
			}
		}


		var dialog_title = jQuery(caller).data('dialog-title-create');
		var cellDescription = function() {
			var desc = jQuery(caller).data('cell-description');
			if ( desc === null ) {
				desc = '';
			}
			return desc;
		}();
		var $editWindow = jQuery('#ddl-default-edit');
        
        var $save_button = $editWindow.find('.js-dialog-edit-save');
        $save_button.html($save_button.data('create-text'));

		$editWindow
			.data('mode', 'new-cell')
			.data('cell-type', cell_type);

		$editWindow.find('.js-dialog-title').html(dialog_title);

		jQuery('input[name="ddl-default-edit-cell-name"]').val(cell_name);
		jQuery('input[name="ddl-default-edit-class-name"]').val("");
		jQuery('input[name="ddl-default-edit-css-id"]').val("");
		$editWindow.find('select[name="ddl_tag_name"]').val( 'div' );

		self._display_info_box( cell_name, cellDescription, cell_type );

		self._set_dialog_content(cell_type);

		if (cell_type == 'ddl-container') {
			self._initialize_container();
		} else {
			if (cell_type in self._dialog_defaults) {
				self._initialize_dialog_from_content(self._dialog_defaults[cell_type]);
			}
		}

		self._show_colorbox();

	};

	self._create_default_cell_name = function (cell_name, layout) {
		var cells = layout.getLayoutCells();
		var test_name = cell_name;
		var count = 2;
		var found = false;

		do {
			found = false;
			for (var i = 0; i < cells.length; i++) {
				var cell = cells[i];
				if (cell.get('name') == test_name) {
					found = true;
					test_name = cell_name + ' ' + count;
					count++;
					break;
				}

			}
		} while (found)

		return test_name;
	};

	self._show_colorbox = function () {

		jQuery.colorbox({
			href: '#ddl-default-edit',
			closeButton: false,
			escKey : false,
            overlayClose: false,

			onComplete: function() {

				self._cleanup_required = true;

				self._repeating_fields.initialize_events();

				if ( jQuery('#ddl-default-edit .js-toggle-front-end-options').data('expanded') ) {
					jQuery('#ddl-default-edit .js-toggle-front-end-options').trigger('click');
				}

				// prevent tinyMCE to bother when dialog opens
				// jQuery("#celltexteditor").focus();
				if (jQuery('#ddl-default-edit [name="ddl-layout-content"]').length) {
					jQuery("#celltexteditor").css("visibility", "visible");
				}
                
				self.disable_save_button(false); // just in case it's disabled.

				jQuery('#ddl-default-edit').trigger('ddl-default-dialog-open');


				var val = 'cell';
				if ( self._cell_type == 'ddl-container') {
					val = 'row';
				}
				jQuery('.js-change-name').each(function(){
					var $this = jQuery(this);
					$this.text( $this.data( val ) );
				});

				// Tabs
				jQuery('.js-popup-tabs').tabs( ); // Initialize tabs

				jQuery('.js-popup-tabs').tabs( 'option', 'active', 0 ); // Activate the first tab

				jQuery.colorbox.resize();

				self._fire_event('dialog-open');

			},
			onLoad: function()
			{

				if (jQuery('#ddl-default-edit [name="ddl-layout-content"]').length) {
					jQuery("#celltexteditor").focus();
				}
			},
			onCleanup: function () {
				self.clean_up();
			},
            onClosed: function () {
                self._fire_event('dialog-closed');
            }
		});

	};



	self.clean_up = function () {
		if (self._cleanup_required) {

			//prevent tinyMCE to bother when dialog opens
			if (jQuery('#ddl-default-edit [name="ddl-layout-content"]').length) {
				if( tinyMCE.get("celltexteditor") ) {
					tinyMCE.get("celltexteditor").remove();
				}
				//jQuery("#celltexteditor-html").trigger("click");
			}

			jQuery('#ddl-default-edit .ddl-dialog-content .js-default-dialog-content').children().appendTo('#ddl-cell-dialog-' + self._cell_type);

			self._fire_event('dialog-close');

			self._repeating_fields.close_events();
			self._cleanup_required = false;
		}
	};

	self._set_dialog_content = function (cell_type) {
		if( tinyMCE.get("celltexteditor") ) {
			tinyMCE.get("celltexteditor").remove();
		}

		self._cell_type = cell_type;

		jQuery('#ddl-default-edit .ddl-dialog-content .js-default-dialog-content').empty();

		jQuery('#ddl-cell-dialog-' + cell_type).children().appendTo('#ddl-default-edit .ddl-dialog-content .js-default-dialog-content');

		if (!(cell_type in self._dialog_defaults)) {
			self._dialog_defaults[cell_type] = self._get_content_from_dialog();
		}

	};

	self.show = function(mode, cell_view) {

		self._clear_any_errors();

	//	console.log( mode ); // It is not executed for newly created cells.

		if ( mode === 'edit' ) {

			var $editWindow = jQuery('#ddl-default-edit'),
			   cellName = cell_view.model.get('name'),
			   cell_type = cell_view.model.get('cell_type'),
			   cellSettings = jQuery('[data-cell-type="' + cell_type + '"]'),
			   dialog_title = cellSettings.data('dialog-title-edit'),
			   cellDescription = function() {
				var desc = cellSettings.data('cell-description');
				if ( desc === null ) {
					desc = '';
				}
				return desc;
			}();
			var content = cell_view.model.get('content');

			$editWindow
				.data('mode', 'edit-cell')
				.data('cell_view', cell_view)
				.data('cell-type', cell_type);
                
            var $save_button = $editWindow.find('.js-dialog-edit-save');
            $save_button.html($save_button.data('update-text'));
                

			jQuery('input[name="ddl-default-edit-cell-name"]').val( cellName );
            var css_classes = cell_view.model.get('additionalCssClasses');
			jQuery('input[name="ddl-default-edit-class-name"]').val( css_classes.replace(/ /g, ',') );
			jQuery('input[name="ddl-default-edit-css-id"]').val( cell_view.model.get('cssId') );
			$editWindow.find('select[name="ddl_tag_name"]').val( cell_view.model.get('tag') );
			$editWindow.find('.js-dialog-title').html(dialog_title);

			self._display_info_box( cellName, cellDescription, cell_type );

			self._set_dialog_content(cell_type);

			// initialize the content
			self._initialize_dialog_from_content(content);

		}

		self._show_colorbox();
	};

	self._save = function () {

		var cell_type = jQuery('#ddl-default-edit').data('cell-type');

		var target_cell_view = null;

		if (self.is_new_cell()) {

			target_cell_view = DDLayout.ddl_admin_page.get_new_target_cell();

            jQuery('#ddl-default-edit').data('cell_view', target_cell_view);

		} else if (jQuery('#ddl-default-edit').data('mode') == 'edit-cell') {

			target_cell_view = jQuery('#ddl-default-edit').data('cell_view');

		}
		if (cell_type == 'ddl-container') {
			self._handle_container_save( target_cell_view );
		} else {

			if (target_cell_view) {

				DDLayout.ddl_admin_page.save_undo();

				var target_cell = target_cell_view.model,
					old_width = target_cell.get('width');

				target_cell.set('cell_type', cell_type);
				target_cell.set('editorVisualTemplateID', cell_type + '-template');

				target_cell.set('name', jQuery('input[name="ddl-default-edit-cell-name"]').val());
				target_cell.set('additionalCssClasses', jQuery('input[name="ddl-default-edit-class-name"]').val() );
				target_cell.set('cssId', jQuery('input[name="ddl-default-edit-css-id"]').val());
				target_cell.set('tag', jQuery('#ddl-default-edit select[name="ddl_tag_name"]').val());
				target_cell.set('row_divider', target_cell_view.model.get('row_divider'));

				self._content = self._get_content_from_dialog();

				self._fire_event('get-content-from-dialog');

				target_cell.set('content', self._content);

				//target_cell_view.selectElement();
				target_cell.selected_cell = true;

				if( self.is_new_cell() )
				{
					var width = DDLayout.ddl_admin_page._add_cell.getColumnsToAdd();
					DDLayout.ddl_admin_page.replace_selected_cell(target_cell, width);

                    DDLayout.ddl_admin_page.instance_layout_view.eventDispatcher.trigger('created_new_cell', target_cell );

				} else {
					DDLayout.ddl_admin_page.replace_selected_cell(target_cell);
				}

				self._handle_css_save();
			}
		}
		jQuery.colorbox.close();
		return false;
	};

	self.is_new_cell = function () {
		return jQuery('#ddl-default-edit').data('mode') == 'new-cell';
	};


	self._handle_css_save = function()
	{
		var css_editor = DDLayout.ddl_admin_page.cssEditor;

		try{
			css_editor.setLayoutCss();
		}
		catch( e )
		{
			//console.log( e.message );
		}
	};

	self._get_content_from_dialog = function () {
		var content = {};

		jQuery('#ddl-default-edit [name^="ddl-layout-"]').each( function (){
			var data = jQuery(this).attr('name');
			data = data.substr(11);

			var array_data = false;
			if (data.substr(data.length - 2, 2) == '[]') {
				array_data = true;
				var data_key = data.substr(0, data.length - 2);
				var group_name_match = /\[(.*?)\]/.exec(data_key);
				var array_data_name = group_name_match[1];
				data_key = data_key.substr(array_data_name.length + 2);

				if (typeof content[array_data_name] == 'undefined') {
					content[array_data_name] = Array();
				}
			}
			switch (jQuery(this).attr('type')) {
				case 'checkbox':
					if (array_data) {
						self._get_array_content_from_dialog(content, array_data_name, data_key, jQuery(this).is(':checked'));
					} else {
						content[data] = jQuery(this).is(':checked');
					}
					break;

				case 'radio':
					if (jQuery(this).is(':checked')) {
						if (array_data) {
							self._get_array_content_from_dialog(content, array_data_name, data_key, jQuery('#ddl-default-edit [name="ddl-layout-' + data + '"]:checked').val());
						} else {
							content[data] = jQuery('#ddl-default-edit [name="ddl-layout-' + data + '"]:checked').val();
						}
					}
					break;

				default:
					var data_val = '';
					if (jQuery(this).hasClass('wp-editor-area') && 'celltexteditor' in tinyMCE.editors) {
						if ( tinyMCE.editors['celltexteditor'].isHidden() ) {
							data_val = jQuery(this).val();
						} else {

							data_val = tinyMCE.editors['celltexteditor'].getContent();
							data_val = window.switchEditors.pre_wpautop(data_val);
						}
					} else {
						data_val = jQuery(this).val();
					}
					if (array_data) {
						self._get_array_content_from_dialog(content, array_data_name, data_key, data_val);
					} else {
						content[data] = data_val;
					}
					break;
			}
		});

		return content;
	};

	self._get_array_content_from_dialog = function (content, array_data_name, data_key, value) {
		for (var i = 0; i < content[array_data_name].length; i++) {
			if (typeof content[array_data_name][i][data_key] == 'undefined') {
				// add the value to this index.
				content[array_data_name][i][data_key] = value;
				return;
			}
		}

		// we didn't find an empty position so add a new element to the array.
		var data = {};
		data[data_key] = value;

		content[array_data_name].push(data);

	};

	self._initialize_dialog_from_content = function (content) {

		self._content = content;

		self._repeating_fields.initilize_from_content(content, self);

		jQuery('#ddl-default-edit [name^="ddl-layout-"]').each( function (){
			var data = jQuery(this).attr('name');
			data = data.substr(11);
			if (self._repeating_fields.not_repeating(data)) {
				self.set_element_value(this, content[data]);
			}
		});

		self._fire_event('init-dialog-from-content');
	};

	self.set_element_value = function(element, value) {

		if (typeof value !== 'undefined') {
			switch (jQuery(element).attr('type') ) {
				case 'checkbox':
					if (((typeof value == 'string') && (value == 'true')) || value === true) {
						jQuery(element).prop('checked', true);
					}
					else if( ((typeof value == 'string') && (value == 'false')) || value === false )
					{
						jQuery(element).prop('checked', false);
					}
					break;

				case 'radio':
					if (jQuery(element).val() == value) {
						jQuery(element).prop('checked', true);
					}
					else
					{
						jQuery(element).prop('checked', false);
					}
					break;

				default:
					jQuery(element).val(value);
					break;
			}
		}

	};
	self._handle_container_save = function(target_cell_view) {

		DDLayout.ddl_admin_page.save_undo();

		var number_of_rows = 1,
			layout_type = jQuery('#ddl-default-edit .js-layout-type-selector:checked').val(),
			container_width = DDLayout.ddl_admin_page._add_cell.getColumnsToAdd(),
			row_divider = 1,
			container = new DDLayout.models.cells.Container({
				name : jQuery('input[name="ddl-default-edit-cell-name"]').val(),
				cssClass : "",
				kind : "Container",
				width : container_width
			}),
			container_columns = container_width,
			$grid = null;

		// FIXME:
		// Please review. I'm not are values for number_of_rows, container_columns and row_divider correct.

		if (layout_type === 'fluid') {
			$grid = jQuery('#js-fluid-grid-designer');
			number_of_rows = $grid.data('rows');
			container_columns = $grid.data('max-cols');
			row_divider = $grid.data('max-cols') / $grid.data('cols');
		}
		else if (layout_type === 'fixed') {
			$grid = jQuery('#js-fixed-grid-designer');
			number_of_rows = $grid.data('rows');
			container_columns *= target_cell_view.model.get('row_divider');
		}

		container.addRows(number_of_rows, container_columns, layout_type, row_divider);
		container.set('additionalCssClasses', jQuery('input[name="ddl-default-edit-class-name"]').val());
		container.set('cssId', jQuery('input[name="ddl-default-edit-css-id"]').val());
		container.set('tag', jQuery('#ddl-default-edit select[name="ddl_tag_name"]').val());
		container.set('row_divider', target_cell_view.model.get('row_divider'));

		var target_cell = target_cell_view.model;
		target_cell.selected_cell = true;

		DDLayout.ddl_admin_page.replace_selected_cell(container, container_width);
	};

	self._initialize_container = function() {

		var main_layout = DDLayout.ddl_admin_page.get_layout();
		var target_cell_view = DDLayout.ddl_admin_page.get_new_target_cell();

		var target_row_view = target_cell_view.get_parent_view();
		var allow_fixed = target_row_view.model.get('layout_type') == 'fixed';
		if (allow_fixed && !target_row_view.can_add_fixed_row_below_this()) {
			allow_fixed = false;
		}

		var layout_type_select = jQuery('.js-layout-type-selector');
		var $message = jQuery('.js-diabled-fixed-rows-info');

		layout_type_select.prop('checked', false); // reset selection

		if (allow_fixed) {
			layout_type_select.prop('disabled', false);
			jQuery('.js-layout-type-selector-fixed').prop('checked', true);
			$message.hide();
		}
		else {
			layout_type_select.prop('disabled', true);
			jQuery('.js-layout-type-selector-fluid').prop('checked', true);
			$message.show();
		}

		self._manage_layout_selection();
	};

	self._manage_layout_selection = function () {

		var layout_type = jQuery('.js-layout-type-selector:checked').val();
		var numberOfColumns = DDLayout.ddl_admin_page._add_cell.getColumnsToAdd();
		var $fluidGrid = jQuery('.js-fluid-grid-designer');
		var $fixedGrid = jQuery('.js-fixed-grid-designer');

		if (layout_type === 'fluid') {
			$fluidGrid.show();
			$fixedGrid.hide();
			// Do not asign #js-fluid-grid-designer' to a variable because 'destroy' method removes DOM element
			jQuery('#js-fluid-grid-designer').ddlDrawGrid('destroy');
			jQuery('#js-fluid-grid-designer').ddlDrawGrid();

			jQuery('.js-grid-fixed-message').hide();
		}
		else if (layout_type === 'fixed') {
			$fluidGrid.hide();
			$fixedGrid.show();
			jQuery('#js-fixed-grid-designer').ddlDrawGrid('destroy');
			jQuery('#js-fixed-grid-designer').ddlDrawGrid({
				cols: numberOfColumns,
				maxCols: numberOfColumns
			});

			jQuery('.js-grid-fixed-message').show();
		}

	};

	self._clear_any_errors = function () {
		// Not sure what this is for. Commenting it out for now.
		//jQuery('.ddl-dialog .toolset-alert').remove(); // FIXME: Add JS prefix for the toolest-alert or use .hide() instead of .remove(). This line removes .js-cells-tree-message too
	};

	self._fire_event = function (name) {
		var event_name = self._cell_type + '.' + name;
		jQuery(document).trigger(event_name, [self._content, self]);
	};

	self._display_info_box = function( header, content, type ) {

		if ( jQuery.jStorage.get( 'info-box' + type ) !== 'disabled' ) {

			var template = jQuery("#js-info-box").html();
			jQuery("#js-info-box-container").html( _.template( template, {
				header: header,
				content: content,
				type: type
			}));

			jQuery('.js-remove-info-box').on( 'click', function() {
				var $box = jQuery( '.js-info-box' ).filter( function() {
					return jQuery(this).data( 'cell-type' ) === type;
				});
				$box.fadeOut( 'fast', function() {
					$box.remove();
					jQuery.jStorage.set( 'info-box' + type, 'disabled' );
				});
			});

		}

	};
    
    self.get_target_cell_view = function () {
        return jQuery('#ddl-default-edit').data('cell_view');
    }
    
    self.get_cell_type = function () {
        return self._cell_type;
    }
	
	self.disable_save_button = function (state) {
		jQuery('#ddl-default-edit .js-dialog-edit-save').prop('disabled', state);
	}
	
	self.insert_spinner_before = function (element) {
		return jQuery('<div class="spinner ajax-loader"></div>').insertBefore(element).show();
	}

	self.insert_spinner_after = function (element) {
		return jQuery('<div class="spinner ajax-loader"></div>').insertAfter(element).show();
	}

	self.init();
};