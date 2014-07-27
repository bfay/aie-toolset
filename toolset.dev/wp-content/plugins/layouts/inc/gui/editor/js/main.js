var DDLayout = DDLayout || {};

//Models namespace / paths
DDLayout.models = {};
DDLayout.models.abstract = {};
DDLayout.models.cells = {};
DDLayout.models.collections = {};

//Views namespaces / paths
DDLayout.views = {};
DDLayout.views.abstract = {};

//Messages namespace
WPV_Toolset.messages = {};

DDLayout.MINIMUM_CONTAINER_OFFSET = 69;
DDLayout.CELL_MIN_WIDTH = 50;
DDLayout.MARGIN_BETWEEN_CELLS = 16;
DDLayout.MAXIMUM_SPAN = 12;

DDLayout.utils = {};

DDLayout_settings.DDL_JS.ns = head;

DDLayout_settings.DDL_JS.ns.js(
	DDLayout_settings.DDL_JS.lib_path + "jstorage.min.js"
	, DDLayout_settings.DDL_JS.lib_path + "prototypes.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/abstract/Element.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Cell.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Spacer.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/collections/Cells.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Row.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/collections/Rows.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Container.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/Layout.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "models/cells/ThemeSectionRow.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/abstract/ElementView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/abstract/CollectionView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/CellsView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/RowsView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/RowView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/CellView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/ContainerRowView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/ContainerView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/SpacerView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/LayoutView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/ThemeSectionRowView.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/UndoRedo.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/KeyHandler.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/Breadcrumbs.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/RowTooltip.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/CellDropPlaceholder.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/AddCellHandler.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/SaveState.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "ddl-tree-filter.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "ddl-types-views-popup.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "preview-manager.js"
	, DDLayout_settings.DDL_JS.editor_lib_path + "views/UserHelp.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "default-dialog.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "css-cell-dialog.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "css-row-dialog.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "row-edit-dialog.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "container-edit-dialog.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "dialog-yes-no-cancel.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "layout-settings-dialog.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "dialog-repeating-fields.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path + "css-editor/CssEditor.js"
	, DDLayout_settings.DDL_JS.dialogs_lib_path +'theme-section-row-edit-dialog.js'
	, DDLayout_settings.DDL_JS.dialogs_lib_path +'child-layout-manager.js'
    , DDLayout_settings.DDL_JS.editor_lib_path + "views/ViewLayoutManager.js"
    , DDLayout_settings.DDL_JS.res_path + "/js/ddl_change_layout_use_helper.js"
    , DDLayout_settings.DDL_JS.editor_lib_path + "ddl-post-types-options.js"
    , DDLayout_settings.DDL_JS.res_path + "/js/ddl-individual-assignment-manager.js"

	, function () {
		_.each(DDLayout.models.cells, function (item, key, list) {
			if (list.hasOwnProperty(key) ) {
				_.defaults(DDLayout.models.cells[key].prototype.defaults, DDLayout.models.abstract.Element.prototype.defaults);
			}
			else {
				console.info("Your model should inherit from Element object");
			}
		});
	}
);

(function ($) {
	WPV_Toolset.Utils.loader = new WPV_Toolset.Utils.Loader;
	DDLayout_settings.DDL_JS.ns.ready(function () {
		WPV_Toolset.messages.container = jQuery(".js-ddl-message-container");
		DDLayout.ddl_admin_page = new DDLayout.AdminPage($);
		jQuery(document).trigger('DLLayout.admin.ready');
			_.delay(function () {
				jQuery(document).trigger( 'play-video', 'editorBasics' );
			}, 2000);

	});


}( jQuery ) );

DDLayout.AdminPage = function($)
{
	var self = this;

	self.instance_layout_view = null;
	self.undo_redo = null;
	self.key_handler = null;
	self.breadcrumbs = null;
	self.row_tooltip = null;
	self._new_cell_target = null;
	self._default_dialog = null;
	self._row_dialog = null;
	self._theme_section_row_dialog = null;
	self._container_dialog = null;
	self._save_state = null;
	self._layout_settings_dialog = null;
	self._tree_filter = null;
	self._add_cell = null;
	self.is_colorbox_opened = false;

	self.initial_render = false;
	self.element_name_editable_now = [];
	self.is_in_editable_state = false;

	self.init = function()
	{
		// get the layout from the json textarea.
		var json = jQuery.parseJSON( jQuery('.js-hidden-json-textarea').text() );
		var layout = new DDLayout.models.cells.Layout( json )
            , view_layout = new DDLayout.ViewLayoutManager( layout.get('id'), layout.get('name') );

		self.instance_layout_view = new DDLayout.views.LayoutView({model:layout});

		self.undo_redo = new DDLayout.UndoRedo();
		self.key_handler = new DDLayout.KeyHandler();
		self.breadcrumbs = new DDLayout.Breadcrumbs(layout);
		self.row_tooltip = new DDLayout.RowTooltip();
		self._default_dialog = new DDLayout.DefaultDialog();
		//self._cssCellDialog = new DDLayout.CSSCellDialog;
		self._cssRowDialog = new DDLayout.CSSRowDialog;
		self._save_state = new DDLayout.SaveState();
		self._layout_settings_dialog = new DDLayout.LayoutSettingsDialog();
		self.cssEditor = new DDLayout.CssEditor;

		self.post_types_options_manager = new DDLayout.PostTypes_Options(self);

		self._add_cell = new DDLayout.AddCellHandler();

		self._tree_filter = new DDLayout.treeFilter();

		self._user_help = new DDLayout.UserHelp();

		self.change_layout_title();

		self.deselect_cell();

		self._new_cell_target = null;

		jQuery(document).ready(self._fix_edit_layout_menu_link);

		self._initialize_post_edit();

		self.instance_layout_view.listenTo(self.instance_layout_view.eventDispatcher, 'ddl-remove-cell', self.remove_cell_callback, self );
		self.instance_layout_view.listenTo(self.instance_layout_view.eventDispatcher, 'ddl-delete-cell', self.delete_cell_callback, self );

		self.instance_layout_view.listenTo(self.instance_layout_view.eventDispatcher, 'ddl-remove-row', self.remove_row_callback, self );
		
	//	self.initialize_where_used_ui(layout.get('id'), false);
	};

	self.initialize_where_used_ui = function (layout_id, include_spinner) {
		var where_used_ui = jQuery('.js-where-used-ui');

		if (where_used_ui.length) {
			
			if (include_spinner) {
				var child_div = where_used_ui.find('.dd-layouts-where-used');
				if (child_div.length) {
					child_div.html('<div class="spinner ajax-loader" style="float:none; display:inline-block"></div>');
				}
			}
			
			var data = {
					action : 'ddl_get_where_used_ui',
					layout_id: layout_id,
					wpnonce : jQuery('#ddl_layout_view_nonce').val()
			};
			jQuery.ajax({
				url: ajaxurl,
				type: 'post',
				data: data,
				cache: false,
				success: function(data) {
                    where_used_ui.empty().html(data);
                   // self.post_types_options_manager.openDialog();
				}
			});
		}
	};
	
	self._initialize_post_edit = function () {
		if (jQuery('#post').length) {
			jQuery('#post').submit(function (e) {
				jQuery('.js-hidden-json-textarea').text(JSON.stringify(self.get_layout_as_JSON()));
				self._save_state.clear_save_required();
			});
		}
	};

	self.remove_cell_callback = function( view, handler )
	{
		var model = view.model;

		if( model.get('cell_type') === "child-layout" )
		 {
		    var child_dialog = new DDLayout.ChildLayoutManager( view, handler, 'ddl-delete-cell');
		 }
		 else
		 {
			 view.eventDispatcher.trigger( 'ddl-delete-cell' );
		 }

        self.instance_layout_view.eventDispatcher.trigger('cell_removed', view.model, 'remove' );

	};

	self.remove_row_callback = function( row_view, handler )
	{
		if (row_view.hasChildLayoutCellAndChildren()) {
		    var child_dialog = new DDLayout.ChildLayoutManager( row_view, handler, 'ddl-delete-row');
		} else {
			row_view.deleteTheRow();
		}
	}
	
	self.delete_cell_callback = function()
	{
		self.delete_selected_cell(null);
	};

	self.get_framework = function()
	{
		return DDLayout_settings.DDL_JS.current_framework;
	};

	self.deselect_cell_handler = function(event)
	{
		var rightclick = false,
			is_mouse_tooltip = jQuery( event.target ).closest('.wp-pointer').length > 0,
			is_text_edit = event.target.id == "celltexteditor-tmce",
			is_colorbox = jQuery("#colorbox").css("display") == "block";
		if (event.which) rightclick = (event.which == 3);
		else if (event.button) rightclick = (event.button == 2);

		if ( !rightclick && is_mouse_tooltip === false && is_text_edit === false && is_colorbox === false) {
			event.stopImmediatePropagation();
			event.data.self.instance_layout_view.eventDispatcher.trigger("deselect_element");
		}
	};
	self.deselect_cell = function()
	{
		var self = this;
		jQuery(document).on("click", {self:self}, self.deselect_cell_handler);
	};

	self.take_undo_snapshot = function() {
		var modelJSON = self.instance_layout_view.getLayoutModelToJs();
		self.undo_redo.take_undo_snapshot(modelJSON);
	};

	self.add_snapshot_to_undo = function() {
		self.undo_redo.add_snapshot_to_undo();
	};

	self.save_undo = function() {
		var modelJSON = self.instance_layout_view.getLayoutModelToJs();
		self.undo_redo.save_undo( modelJSON );
	};

	self.get_layout_as_JSON = function() {
		return self.instance_layout_view.getLayoutModelToJs();
	};

	self.get_layout = function () {
		return self.instance_layout_view.model;
	};

	self.set_layout = function(layout) {
		self.instance_layout_view.model.parse(layout);
		self.instance_layout_view.model.populate_self_on_first_load(layout);
		self.render_all();
	};

	self.save_layout = function (callback) {
		self.instance_layout_view.saveLayout(null, callback);
	};

	self.render_all = function ( options ) {
		self.instance_layout_view.render( options );
		self.breadcrumbs.display_breadcrumbs(self.get_layout());
	};

	self.do_undo = function() {
		self.undo_redo.handle_undo();
	};

	self.do_redo = function() {
		self.undo_redo.handle_redo();
	};

	self.move_selected_cell_left = function(event) {
		self.instance_layout_view.eventDispatcher.trigger('move_selected_cell_left', event);
	};

	self.move_selected_cell_right = function(event) {
		self.instance_layout_view.eventDispatcher.trigger('move_selected_cell_right', event );
	};

	self.delete_selected_cell = function(event) {
		self.save_undo();
		self.instance_layout_view.eventDispatcher.trigger('delete_selected_cell', event);
	};


	self.set_new_target_cell = function (cell_view) {
		self._new_cell_target = cell_view;
	};

	self.get_new_target_cell = function () {
		return self._new_cell_target;
	};

	self.replace_selected_cell = function (new_cell, new_width) {
		self.instance_layout_view.eventDispatcher.trigger('replace_selected_cell', new_cell, new_width);
	};

	self.show_default_dialog = function (mode, cell_view) {
		self._default_dialog.show(mode, cell_view);
	};
	self.clean_up_default_dialog = function () {
		self._default_dialog.clean_up();
	};

	// TODO: We can probably remove it, because the icon was removed: https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/176486575/comments
	self.show_css_dialog = function( view )
	{
		console.log('self.show_css_dialog');
		if( view.model instanceof DDLayout.models.cells.Row )
		{
			self._cssRowDialog.show( view );
		}
		else
		{
			self._cssCellDialog.show( view );
		}
	};

	self.show_row_dialog = function (mode, row_view) {
		if (!self._row_dialog) {
			self._row_dialog = new DDLayout.RowDialog();
		}

		self._row_dialog.show(mode, row_view);
	};

	self.show_theme_section_row_dialog = function( mode, row_view )
	{
		if (!self._theme_section_row_dialog) {
			self._theme_section_row_dialog = new DDLayout.ThemeSectionRowDialog(jQuery);
		}

		self._theme_section_row_dialog.show( mode, row_view );
	};

	self.show_container_dialog = function( mode, container_view)
	{
		if (!self._container_dialog) {
			self._container_dialog = new DDLayout.ContainerDialog();
		}

		self._container_dialog.show(mode, container_view);
	};
	self.getLayoutType = function()
	{
		return self.instance_layout_view.getLayoutType();
	};

	self.set_parent_layout = function ( parent_layout ) {
		self.save_undo();
		var layout = self.get_layout();
		layout.set_parent_layout(parent_layout);
	};

	self.get_parent_layout = function () {
		return self.get_layout().get_parent_layout();
	};

	self.set_save_required = function () {
		self._save_state.set_save_required();
	};

	self.clear_save_required = function () {
		self._save_state.clear_save_required();
	};

	self.is_save_required = function () {
		return self._save_state.is_save_required();
	};



	self.change_layout_title = function () {
		var self = this,
			el = jQuery('.js-edit-layout-slug')
            , edit_button = jQuery('.js-edit-slug')
            , $ok_button_wrap = jQuery('.js-edit-slug-buttons-active')
            , $ok_button = jQuery('.js-edit-slug-save')
            , $cancel_link = jQuery('.js-cancel-edit-slug');

        jQuery(document).on('click', edit_button.selector, function(event){
                event.preventDefault();
                el.trigger('click');
        });

		el.on('click', function (event) {
			event.stopImmediatePropagation();
			DDLayout.ddl_admin_page.take_undo_snapshot();

			var parent = jQuery(this).parent(),
				old_title = jQuery(this).text(),
				index = jQuery(this).index(),
                input = jQuery('<input id="layout-slug" name="layout-slug" type="text" class="edit-layout-slug js-edit-layout-slug" />'),
                $me = jQuery(this);

            edit_button.parent().hide();
            $ok_button_wrap.show();

            $ok_button.on('click', function(event){
                event.preventDefault();
                jQuery(document).not(input).trigger('mouseup');
                jQuery(this).off('click');
            });

            jQuery( document).on('click', $cancel_link.selector, function(event){
                event.preventDefault();
                $me.text( old_title );
                edit_button.parent().show();
                $ok_button_wrap.hide();
                jQuery(this).off('click');
            });

			input.val(old_title);
			jQuery(this).addClass('hidden');

			parent.insertAtIndex(index, input);

			parent.css("position", "relative");

			input.keydown(function (e) {
				var key = e.keyCode || 0;
				// on enter, just save the new slug, don't save the post
				if (13 == key) {
					jQuery(document).not(input).trigger('mouseup');
					return false;
				}
				if (27 == key) {
					jQuery(document).not(input).trigger('mouseup', {cancel: true, val: value});
					return false;
				}
			}).focus();
			// .val(value);

			jQuery(document).not(input).on("mouseup", {
				el: el,
				input: input,
				self: self.instance_layout_view,
				is_title: true,
				old_title:old_title,
                edit_button:edit_button,
                ok_button_wrap:$ok_button_wrap
			}, DDLayout.AdminPage.manageDeselectElementName);
		});
	};

	self._fix_edit_layout_menu_link = function() {
		var current_url = window.location.href;

		jQuery('a.current').each( function() {
			var link = jQuery(this).attr('href');
			if (link.indexOf('page=dd_layouts_edit') != -1) {
				jQuery(this).attr('href', current_url);
			}
		});
	};

	self.handle_add_cell_click = function (cell_view) {
		return self._add_cell.handle_click(cell_view);
	};

	self.handle_cell_enter = function (cell_view) {
		return self._add_cell.handle_enter(cell_view);
	};

    self.show_create_new_cell_dialog = function (cell_view, columns) {
		self._add_cell.show_create_new_cell_dialog(cell_view, columns);
	};
	
	self.switch_to_layout = function (post_id) {

		self.clear_save_required();

		var current_url = window.location.href;
		var post_pos = current_url.indexOf('layout_id=');
		var post_pos_end = current_url.indexOf('&', post_pos);
		if (post_pos_end == -1) {
			post_pos_end = current_url.length;
		}
		var post_data = current_url.substr(post_pos, post_pos_end - post_pos);
		current_url = current_url.replace(post_data, 'layout_id=' + post_id);

		window.location.href = current_url;
	};

	self.init();
};

//maybe to be moved in utils library
DDLayout.AdminPage.setCaretPosition = function(elem, caretPos) {
	var el = elem;

	el.value = el.value;
	// ^ this is used to not only get "focus", but
	// to make sure we don't have it everything -selected-
	// (it causes an issue in chrome, and having it doesn't hurt any other browser)

	if (el !== null) {

		if (el.createTextRange) {
			var range = el.createTextRange();
			range.move('character', caretPos);
			range.select();
			return true;
		}

		else {
			// (el.selectionStart === 0 added for Firefox bug)
			if (el.selectionStart || el.selectionStart === 0) {
				el.focus();
				el.setSelectionRange(caretPos, caretPos);
				return true;
			}

			else  { // fail city, fortunately this never happens (as far as I've tested) :)
				el.focus();
				return false;
			}
		}
	}
};

// some static methods to be used everywehere regardless of the instance
DDLayout.AdminPage.manageDeselectElementName = function( event, args )
{
	event.stopPropagation();

	var self = event.data.self,
		input = event.data.input,
		el = event.data.el,
		old_title = event.data.old_title,
		value = '';

	if ( event.target === input[0] ) {

		if(!event.data.is_title) DDLayout.AdminPage.setCaretPosition( input[0], self.mouse_caret );
		return true;
	}

	if ( args && args.cancel )
	{
		el.text( args.val ).show();
	}
	else
	{
		DDLayout.ddl_admin_page.add_snapshot_to_undo();
		var new_val = input.val();

        if( event.data.edit_button && event.data.ok_button_wrap)
        {
            event.data.edit_button.parent().show();
            event.data.ok_button_wrap.hide();
        }

		if( (new_val.replace(/[^A-Z]/g, "").length || new_val == '') && event.data.is_title )
		{
			input.val( old_title );
			value = old_title;

			WPV_Toolset.messages.container.wpvToolsetMessage({
				text: DDLayout_settings.DDL_JS.strings.invalid_slug,
				type: 'error',
				stay: false,
				close: false,
				onOpen: function() {
					jQuery('html').addClass('toolset-alert-active');
				},
				onClose: function() {
					jQuery('html').removeClass('toolset-alert-active');
				}
			});
		}
		else
		{
            if( event.data.is_title )
            {
                self.model.set( 'slug', new_val );
            }
            else
            {
                self.model.set( 'name', new_val );
            }

			value = new_val;
		}

	}

	input.remove();

	el
		.text(value)
		.removeClass('hidden')
		.css('visibility', 'visible');

	if ( event.data.is_title ) {
		jQuery(".js-edit-layout-slug").text( value );
	}

	DDLayout.ddl_admin_page.element_name_editable_now.pop();
	DDLayout.ddl_admin_page.is_in_editable_state = false;

	jQuery(document).not(input).off( "mouseup", DDLayout.AdminPage.manageDeselectElementName );
	return true;
};