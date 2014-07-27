DDLayout.ViewLayoutManager = function( layout_id, layout_name )
{
    var self = this
        , $button = jQuery('.js-view-layout')
        , id = layout_id
        , name = layout_name;

    self.init = function()
    {
           jQuery(document).on('mousedown', $button.selector, function(event){
                event.preventDefault();
               WPV_Toolset.Utils.loader.loadShow( jQuery(this).parent().parent() );
               self.load_items();
           });
    }

    self.load_items = function( )
    {
        var params = {
            action:'view_layout_from_editor'
            , 'ddl-view-layout-nonce' : DDLayout_settings.DDL_JS['ddl-view-layout-nonce']
            , layout_id : id
        };

        WPV_Toolset.Utils.do_ajax_post(params, {
            success: function (response) {
                WPV_Toolset.Utils.loader.loadHide();
                self.route_actions( response );
            },
            error: function (response) {
                WPV_Toolset.Utils.loader.loadHide();
                WPV_Toolset.messages.container.wpvToolsetMessage({

                    text: response.error,
                    type: 'error',
                    stay: true,
                    close: false,
                    onOpen: function() {
                        jQuery('html').addClass('toolset-alert-active');
                    },
                    onClose: function() {
                        jQuery('html').removeClass('toolset-alert-active');
                    }
                });
            }
        });
    };

    self.route_actions = function( data )
    {
        if (data.hasOwnProperty('Data') && data.Data.length === 1) {
            self.handleLink(data.Data[0], "#ddl-layout-not-assigned-to-any", data.message );
        }
        else if (data.hasOwnProperty('Data') && data.Data.length > 1) {
            self.handle_links(data.Data, "#ddl-layout-assigned-to-many");
        }
        else
        {
            self.handle_message(data.message, "#ddl-layout-not-assigned-to-any");
        }
    };

    self.handle_dialog= function( data, template_id )
    {
        var template = jQuery(template_id).html();

        jQuery("#js-view-layout-dialog-container").html( _.template(template, data ) );

        jQuery.colorbox({
            href: '#js-view-layout-dialog-container',
            inline: true,
            open: true,
            closeButton: false,
            fixed: true,
            top: false,

            onComplete: function () {

            },
            onCleanup: function () {

            }
        });
    };

    self.handle_links = function( data, template_id )
    {
        var links = {links:data, layout_name:name};
        self.handle_dialog( links, template_id );
    };

    self.handle_message = function( data, template_id ){
        var message = {message:data, layout_name:name};
        self.handle_dialog( message, template_id );
    };

    self.handleLink = function( data, template_id, message )
    {
        if( data.href == '' )
        {
            self.handle_message( message, template_id );
        }
        else
        {
            window.open( data.href );
        }

    };

    self.init();
};