// widget-area.js


jQuery(document).ready(function($){

	DDLayout.WidgetArea = function($)
	{
		var self = this;

		self.init = function() {

			jQuery(document).on('click', '.js-create-new-sidebar', {dialog: self}, function(event){
				event.stopImmediatePropagation();
				event.data.dialog.show_create_sidebar_controls( );
			});

			jQuery(document).on('click', '.js-cancel-create-new-sidebar', {dialog: self}, function(event){
				event.stopImmediatePropagation();
				event.data.dialog.hide_create_sidebar_controls( );
			});

			jQuery(document).on('change keyup input cut paste', '[name="ddl-sidebar-name"]', {dialog: self}, function(event) {
				var name = jQuery('[name="ddl-sidebar-name"]').val();
				jQuery('.js-create-the-new-sidebar').prop('disabled', name == '');

			});

			jQuery(document).on('ddl-default-dialog-open', '#ddl-default-edit', {dialog: self}, function(event) {
				// check if we have the right dialog.

				if (jQuery('#ddl-default-edit .js-create-new-sidebar-div').length) {
					event.data.dialog.hide_create_sidebar_controls();
				}
			});

		};

		self.show_create_sidebar_controls = function() {
			jQuery('.js-create-new-sidebar-div input[type=text]').val('');
			jQuery('.js-create-the-new-sidebar').prop('disabled', true);

			// TODO: Review this. I don't know why to do it in such a strange way
			jQuery('#ddl-default-edit .ddl-dialog-footer:not(.js-widget-area-footer)').hide();
			jQuery('#ddl-default-edit .ddl-dialog-content-head').hide();
			jQuery('.js-create-new-widget-area-button').hide();
			jQuery('.js-create-new-sidebar-div').show();
			jQuery('.js-widget-area-select').hide();
		};

		self.hide_create_sidebar_controls = function() {
			jQuery('#ddl-default-edit .ddl-dialog-footer:not(.js-widget-area-footer)').show();
			jQuery('#ddl-default-edit .ddl-dialog-content-head').show();
			//jQuery('.js-create-new-widget-area-button').show();
			jQuery('.js-create-new-sidebar-div').hide();
			jQuery('.js-widget-area-select').show();

		}

		self.init();
	};


    DDLayout.widget_area = new DDLayout.WidgetArea($);

});

