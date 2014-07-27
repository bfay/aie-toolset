DDLayout.PostTypes_Options = function(adm)
{
	var self = this, admin = adm,
        layout_view = admin.instance_layout_view,
        layout_model = layout_view.model,
        post_types_change_button = jQuery('.js-post-types-options')
        , archives_change_button = jQuery('.js-save-archives-options')
        , others_change_button = jQuery('.js-save-others-options')
        ,  $open_dialog = jQuery('.js-layout-content-assignment-button');


	self.init = function( )
	{
        // opens dialog in editor only
        self.openDialog();
        self.handle_re_render();
        self.handle_layout_post_types_change();
	};


    self.handle_re_render = function(event)
    {
        var where_ui = jQuery('.js-where-used-ui');
        where_ui.show();
    };

    self.handle_layout_post_types_change = function()
    {
        jQuery(document).on('click', post_types_change_button.selector, function(event){
            var send = {};
            send[DDLayout_settings.DDL_JS.POST_TYPES_OPTION] = DDLayout.changeLayoutUseHelper.getLayoutOption(DDLayout_settings.DDL_JS.POST_TYPES_OPTION);
            self.send_data_to_server( event, send, 'change_layout_usage_for_post_types_js', DDLayout_settings.DDL_JS.POST_TYPES_OPTION );
        });

        jQuery(document).on('click', archives_change_button.selector, function(event){
            var send = {};
            send[DDLayout_settings.DDL_JS.ARCHIVES_OPTION] = DDLayout.changeLayoutUseHelper.getLayoutOption(DDLayout_settings.DDL_JS.ARCHIVES_OPTION);
            self.send_data_to_server( event, send, 'change_layout_usage_for_archives_js', DDLayout_settings.DDL_JS.ARCHIVES_OPTION );
        });

        jQuery(document).on('click', others_change_button.selector, function(event){
            var send = {};
            send[DDLayout_settings.DDL_JS.OTHERS_OPTION] = DDLayout.changeLayoutUseHelper.getLayoutOption(DDLayout_settings.DDL_JS.OTHERS_OPTION);
            self.send_data_to_server( event, send, 'change_layout_usage_for_others_js', DDLayout_settings.DDL_JS.OTHERS_OPTION );
        });
    };

    self.send_data_to_server = function( event, data, action, name )
    {
        var params = {
            action:action,
            'layout-set-change-post-types-nonce': jQuery('#layout-set-change-post-types-nonce').val(),
            layout_id:layout_model.get('id')
        };

        jQuery( event.target ).prop( 'disabled', true).removeClass('button-primary').addClass('button-secondary');

        params = _.extend( params, data );

        DDLayout.ChangeLayoutUseHelper.manageSpinner.addSpinner( event.target );

        WPV_Toolset.Utils.do_ajax_post(params, {success:function(response){
            DDLayout.ChangeLayoutUseHelper.manageSpinner.removeSpinner();
            self._refresh_where_used_ui(false);
            DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('data_sent_to_server', name);
        }});
    };

    self.layout_has_post_content = function( )
    {
        return layout_model.has_cell_of_type("cell-post-content") || layout_model.has_cell_of_type("cell-content-template");
    };

    self.layout_has_loop_cell = function()
    {
        return layout_model.has_cell_of_type("post-loop-cell") || layout_model.has_cell_of_type("post-loop-views-cell");
    }

	self._refresh_where_used_ui = function (include_spinner) {
		DDLayout.ddl_admin_page.initialize_where_used_ui(layout_model.get('id'), include_spinner);
	};

    self.openDialog = function()
    {
        if( $open_dialog.is('button') )
        {
            jQuery(document).on('click', $open_dialog.selector, function(event){

                var dialog_content = jQuery('.js-layout-content-assignment-dialog');

                self.close_dialog_cancel( dialog_content );

                var args = {
                    has_content_cell : self.layout_has_post_content(),
                    has_post_loop_cell : self.layout_has_loop_cell()
                };

                jQuery.colorbox({
                    href: dialog_content.selector,
                    inline: true,
                    open: true,
                    closeButton:false,
                    fixed: true,
                    top: '50px',
                    onLoad:function()
                    {
                        dialog_content.fadeIn('fast');
                        DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('on-assignment_dialog-open', dialog_content );
                    },
                    onOpen:function()
                    {
                        var checkboxes = jQuery('.js-ddl-post-type-checkbox');
                        DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('change-layout-use-open', dialog_content, layout_model.get('id'), checkboxes, args );
                    },
                    onComplete: function() {
                        DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('on-assignment_dialog-complete', dialog_content);
                    },
                    onCleanup: function() {
                        dialog_content.hide();
                        DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('assignment_dialog_close');
                    },
                    onClosed:function(){

                    }
                });
            });
        }
    };

    self.close_dialog_cancel = function( dialog )
    {
        var cancel = jQuery('.js-layout-content-assignment-sidebar', dialog );

        jQuery(document).on('click', cancel.selector, function(event){
            jQuery('.js-edit-dialog-close', dialog).trigger('click')
            jQuery('.js-edit-dialog-close', dialog).trigger('click')
        });
    };


	self.init();
};