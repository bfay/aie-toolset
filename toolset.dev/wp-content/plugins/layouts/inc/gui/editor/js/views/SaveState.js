// SaveState.js

DDLayout.SaveState = function($)
{
    var self = this
        , $save_button = jQuery('input[name="save_layout"]');

    self.requires_save = false;
    self.last_save_json = '';

    self.init = function( initial_layout_json)
    {
        self.requires_save = false;
        self.last_save_json = initial_layout_json;

        self.eventDispatcher.listenTo(self.eventDispatcher, 'save_state_change', self.button_save_set_state);

        self.eventDispatcher.trigger('save_state_change', self.requires_save );
    };

    self.set_save_required = function () {
        self.requires_save = true;
        jQuery(window).bind('beforeunload', function(){
            return DDLayout_settings.DDL_JS.strings.page_leave_warning;
        });

        self.eventDispatcher.trigger('save_state_change', self.requires_save );
    };

    self.clear_save_required = function () {
        self.requires_save = false;
        jQuery('.js-ddl-message-container .toolset-alert').fadeOut(500, function() {jQuery(this).remove()});

        self.last_save_json = DDLayout.ddl_admin_page.get_layout_as_JSON();

        jQuery(window).unbind('beforeunload');

        self.eventDispatcher.trigger('save_state_change', self.requires_save );
    };

    self.is_save_required = function () {
        return self.requires_save;
    };

    self.button_save_set_state = function( state )
    {
        var disable = state ? false : true;
        $save_button.prop( 'disabled', disable );
    };

    self.init();
};

DDLayout.SaveState.prototype.eventDispatcher = _.extend({}, Backbone.Events);