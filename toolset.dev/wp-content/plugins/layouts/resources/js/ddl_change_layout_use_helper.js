var DDLayout = DDLayout || {};

DDLayout.ChangeLayoutUseHelper = function ($) {
    var self = this
        , post_types_change_button = null
        , archives_change_button = null
        , cache = {}
        , track_option_cache = {}
        , $message_div;

    self.layoutOptions = null;
    self._current_layout = null;
    self.has_message = false;

    self._has_loop_cell = false;
    self._has_post_content_cell = false;


    self.init = function () {
        self.eventDispatcher.listenTo(self.eventDispatcher, 'assignment_dialog_close', self.clean_up_dialog_events);
        self.eventDispatcher.listenTo(self.eventDispatcher, 'change-layout-use-open', self.handle_on_open);
        self.eventDispatcher.listenTo(self.eventDispatcher, 'checkboxes_changed', self.handle_checkboxes_change);
        self.eventDispatcher.listenTo(self.eventDispatcher, 'data_sent_to_server', self.ajax_response_callback);

    };

    self.set_layout_has_cells_of_type = function (args) {
        if (typeof args !== 'undefined') {
            self._has_loop_cell = args.has_post_loop_cell;
            self._has_post_content_cell = args.has_content_cell;
        }
    };

    self.handle_on_open = function (dialog, layout_id, checkboxes, args) {

        self.set_current_layout(layout_id);
        self.set_layout_has_cells_of_type(args)
        self.setInitialStateLayoutOptions(dialog, checkboxes);
        self.build_cache();
        self.set_buttons(dialog);

        self.setChangeEvents();
        self.handle_button_on_open();

        self.collapse_group(dialog);
        self.dialog_opens(dialog);

        self.dismissPostContentCellWarning(dialog);

        $message_div = jQuery('div.dialog-change-use-messages');
    };

    self.ajax_response_callback = function(name)
    {
        self.set_track_option_cache(name, false);

        if( self.track_option_cache_true() === true )
        {
            self.has_message = false;
            $message_div.wpvToolsetMessage('destroy');
        }

        // reset cache
        cache['layout_'+self.get_current_layout()] = null;

        // and build new one
        self.build_cache();
    };

    self.build_cache = function () {
        var  temp = {}
            , types = self.getLayoutOption(DDLayout_settings.DDL_JS.POST_TYPES_OPTION)
            , loops = self.getLayoutOption(DDLayout_settings.DDL_JS.ARCHIVES_OPTION)
            , others = self.getLayoutOption(DDLayout_settings.DDL_JS.OTHERS_OPTION);

        // copy arrays into temporary object
        self.setTrackOptionCacheForCurrent();

        if( !self.getCurrentTrackOptionCacheOption(DDLayout_settings.DDL_JS.POST_TYPES_OPTION) || self.getCurrentTrackOptionCacheOption(DDLayout_settings.DDL_JS.POST_TYPES_OPTION) === false )
        {
            temp[DDLayout_settings.DDL_JS.POST_TYPES_OPTION] = types ? types : [];
            self.set_track_option_cache(DDLayout_settings.DDL_JS.POST_TYPES_OPTION, false);
        }

        if( !self.getCurrentTrackOptionCacheOption(DDLayout_settings.DDL_JS.ARCHIVES_OPTION) || self.getCurrentTrackOptionCacheOption(DDLayout_settings.DDL_JS.ARCHIVES_OPTION) === false )
        {
            temp[DDLayout_settings.DDL_JS.ARCHIVES_OPTION] = loops ? loops : [];
            self.set_track_option_cache(DDLayout_settings.DDL_JS.ARCHIVES_OPTION, false);
        }

        if( !self.getCurrentTrackOptionCacheOption(DDLayout_settings.DDL_JS.OTHERS_OPTION) || self.getCurrentTrackOptionCacheOption(DDLayout_settings.DDL_JS.OTHERS_OPTION) === false )
        {
            temp[DDLayout_settings.DDL_JS.OTHERS_OPTION] = others ? others : [];
            self.set_track_option_cache(DDLayout_settings.DDL_JS.OTHERS_OPTION, false);
        }

        if( cache === null ) cache = {};

        if( typeof cache['layout_'+self.get_current_layout()] === 'undefined' ) cache['layout_'+self.get_current_layout()] = {};

        // shallow copy the temporary object to cache a loose reference of the original object
        cache['layout_'+self.get_current_layout()] = jQuery.extend( true, cache['layout_'+self.get_current_layout()], temp );

        // release
        temp = null;
    };

    self.setTrackOptionCacheForCurrent = function()
    {
        if( typeof track_option_cache['layout_'+self.get_current_layout()] === 'undefined' )
        {
            track_option_cache['layout_'+self.get_current_layout()] = {};
        }
    };

    self.getCurrentTrackOptionCache = function( )
    {
        return track_option_cache['layout_'+self.get_current_layout()];
    };

    self.getCurrentTrackOptionCacheOption = function( name )
    {
        var ret = track_option_cache['layout_'+self.get_current_layout()];

        return ret[name];
    };

    // track if there are messages for other groups
    self.track_option_cache_true = function()
    {
        var check_true = _.filter(self.getCurrentTrackOptionCache(), function(v) {return v === true})
            , size = _.size( check_true );
         return size === 0;
    };

    self.set_track_option_cache = function( name, value )
    {
        track_option_cache['layout_'+self.get_current_layout()][name] = value;
    };

    self.get_cache = function (name) {

        if( typeof cache['layout_'+self.get_current_layout()] === 'undefined' ) return null;

        if (typeof name === 'undefined') return cache['layout_'+self.get_current_layout()];

        var ret = cache['layout_'+self.get_current_layout()];

        return ret[name];
    };

    self.set_buttons = function (dialog) {
        post_types_change_button = jQuery('.js-post-types-options', dialog)
        archives_change_button = jQuery('.js-save-archives-options', dialog);
    };

    self.handle_button_on_open = function () {
        post_types_change_button.prop('disabled', true).removeClass('button-primary').addClass('button-secondary');
        archives_change_button.prop('disabled', true).removeClass('button-primary').addClass('button-secondary');
    };



    self.handle_checkboxes_change = function ( ul, name, length ) {
        var $update = ul.find('.js-buttons-change-update');

        if ( self.enable_disable_button_on_change(name) === false || ( typeof length !== 'undefined' && length > 0 ) ) {

            self.set_track_option_cache( name, true );

            $update.prop('disabled', false).removeClass('button-secondary').addClass('button-primary');

            if (self.has_message === false) {

                $message_div.wpvToolsetMessage({
                    text: $message_div.data('text'),
                    stay: true,
                    close: false,
                    type: 'notice'
                });
                self.has_message = true;
            }

        }
        else {

            self.set_track_option_cache( name, false );

            $update.prop('disabled', true).removeClass('button-primary').addClass('button-secondary');

            if( self.track_option_cache_true() === true )
            {
                $message_div.wpvToolsetMessage('destroy');
                self.has_message = false;
            }
        }
    };

    self.dismissPostContentCellWarning = function ($context) {
        var $dismiss_post_content_warning = $('.js-dismiss-alert-message-post-content', $context)
            , $dismiss_loop_warning = $('.js-dismiss-alert-message-loop', $context)
            , $checkboxes_post_content = $('.js-ddl-post-content-checkbox', $context)
            , $checkboxes_archive_loop = $('.js-ddl-archive-loop-checkbox', $context)
            , class_warning_types = 'post-types-list-in-layout-editor-alerted'
            , class_warning_loops = 'post-loops-list-in-layout-editor-alerted'
            , layout_id = self.get_current_layout();


        $(document).on('click', $dismiss_post_content_warning.selector, function (event, set) {
            event.preventDefault();
            var $me = $(this);

            $me.parent().parent('div').parent('li').parent('ul').removeClass(class_warning_types);
            $me.parent().parent('div').slideUp('fast');
            //   $update_post_type.prop('disabled', false);
            $checkboxes_post_content.each(function (v) {
                $(this).prop('disabled', false);
                if (set === undefined) jQuery.jStorage.set('dismiss_alert_post_content_' + layout_id, 'yes')
            });
        });

        $(document).on('click', $dismiss_loop_warning.selector, function (event, set) {
            event.preventDefault();
            var $me = $(this);

            $me.parent().parent('div').parent('li').parent('ul').removeClass(class_warning_loops);
            $me.parent().parent('div').slideUp('fast');
            //   $update_archives.prop('disabled', false);
            $checkboxes_archive_loop.each(function (v) {
                $(this).prop('disabled', false);
                if (set === undefined) jQuery.jStorage.set('dismiss_alert_loop_' + layout_id, 'yes')
            });
        });


        if (jQuery.jStorage.get('dismiss_alert_post_content_' + layout_id) === 'yes' || ( self._has_post_content_cell === true && adminpage == 'layouts_page_dd_layouts_edit')) {
            $dismiss_post_content_warning.parent().parent('div').hide();
            $dismiss_post_content_warning.parent().parent('div').parent('li').parent('ul').removeClass(class_warning_types);
            //    $update_post_type.prop('disabled', false);
            $checkboxes_post_content.each(function (v) {
                $(this).prop('disabled', false);
            });
        }
        else if (jQuery.jStorage.get('dismiss_alert_post_content_' + layout_id) !== 'yes' && ( self._has_post_content_cell === false && adminpage == 'layouts_page_dd_layouts_edit')) {
            $dismiss_post_content_warning.parent().parent('div').show();
            $dismiss_post_content_warning.parent().parent('div').parent('li').parent('ul').addClass(class_warning_types);
            //    $update_post_type.prop('disabled', false);
            $checkboxes_post_content.each(function (v) {
                $(this).prop('disabled', true);
            });
        }

        if (jQuery.jStorage.get('dismiss_alert_loop_' + layout_id) === 'yes' || ( self._has_loop_cell === true && adminpage == 'layouts_page_dd_layouts_edit' )) {

            $dismiss_loop_warning.parent().parent('div').hide();
            $dismiss_loop_warning.parent().parent('div').parent('li').parent('ul').removeClass(class_warning_loops);
            //    $update_archives.prop('disabled', false);
            $checkboxes_archive_loop.each(function (v) {
                $(this).prop('disabled', false);
            });
        }
        else if (jQuery.jStorage.get('dismiss_alert_loop_' + layout_id) !== 'yes' && ( self._has_loop_cell === false && adminpage == 'layouts_page_dd_layouts_edit' )) {
            $dismiss_loop_warning.parent().parent('div').show();
            $dismiss_loop_warning.parent().parent('div').parent('li').parent('ul').addClass(class_warning_loops);
            //    $update_archives.prop('disabled', false);
            $checkboxes_archive_loop.each(function (v) {
                $(this).prop('disabled', true);
            });
        }


        if ($('.js-ddl-archive-loop-checkbox:checked', $context).length > 0) {
            $dismiss_loop_warning.trigger('click', 'no');
        }

        if ($('.js-ddl-post-content-checkbox:checked', $context).length > 0) {
            $dismiss_post_content_warning.trigger('click', 'no');
        }

    };

    self.collapse_group = function (dialog) {
        var caret = jQuery('.js-collapse-group-in-dialog,.js-collapse-group-individual', dialog);

        caret.map(function (index, el) {
            jQuery(el).data('open', true)/*.addClass('icon-caret-up')*/;
            /*jQuery(el).parent().next('ul').hide();*/
        });

        jQuery(document).on('click', caret.selector, function (event) {

            var $me = jQuery(this)
                , $ul = $me.parent().next();
                
            if ($me.prop('tagName') != 'I') {
                $me = $me.next();
            }

            if ($me.data('open')) {
                $me.removeClass('icon-caret-up').addClass('icon-caret-down');
                $ul.slideUp('fast', function (event) {
                    $me.data('open', false);
                });
            }
            else {
                $me.removeClass('icon-caret-down').addClass('icon-caret-up');
                $ul.slideDown('fast', function (event) {
                    $me.data('open', true);
                });
            }
        });

    };

    self.dialog_opens = function (dialog) {
        self.open_close(dialog);
    };

    self.open_close = function (dialog) {
        var caret = jQuery('.js-collapse-group-in-dialog', dialog);

        caret.each(function (i) {
            if (self.get_caret_siblings_checked_count(jQuery(this)) === 0) {
                var $me = jQuery(this)
                    , $ul = $me.parent().parent().find('ul');

                $me.removeClass('icon-caret-up').addClass('icon-caret-down');
                $ul.slideUp('fast', function (event) {
                    $me.data('open', false);
                });
            }
        });
    };

    self.get_caret_siblings_checked_count = function (caret) {
        var $caret = caret, $ul = $caret.parent().parent().find('ul'), $checked = $ul.find('input[type="checkbox"]:checked');

        return $checked.length;
    };

    self.get_current_layout = function () {
        return self._current_layout;
    };

    self.set_current_layout = function (layout_id) {
        self._current_layout = +layout_id;
    };

    self.manageOptions = function (opt, what_to_do, input_name) {
        var self = this,
            name = input_name,
            add = what_to_do,
            option = opt;

        // create object if not already created
        if (self.layoutOptions === null) {
            self.layoutOptions = {};
        }

        // create object for this layout (this is necessary in listing page where the same instance is handling multiple layouts)
        if (typeof self.layoutOptions["layout_" + self.get_current_layout()] === 'undefined') {
            self.layoutOptions["layout_" + self.get_current_layout()] = {};
        }

        // create array for specific group if not already crated
        if (typeof self.layoutOptions["layout_" + self.get_current_layout()][name] === 'undefined') {
            self.layoutOptions["layout_" + self.get_current_layout()][name] = [];
        }

        // add item to layout if not already present
        if (add === true && self.layoutOptions["layout_" + self.get_current_layout()][name] && self.layoutOptions["layout_" + self.get_current_layout()][name].indexOf(option) === -1) {
            self.layoutOptions["layout_" + self.get_current_layout()][name].push(option);
        }

        // remove item if unchecked
        else if (add === false) {
            self.layoutOptions["layout_" + self.get_current_layout()][name] = _.without(self.layoutOptions["layout_" + self.get_current_layout()][name], option)
        }
    };


    self.getLayoutOption = function (name) {
        var self = this;
        return self.layoutOptions && self.layoutOptions.hasOwnProperty( "layout_" + self.get_current_layout() ) ? self.layoutOptions[ "layout_" + self.get_current_layout()][name] : null;
    };

    self.getLayoutOptions = function()
    {
        return self.layoutOptions && self.layoutOptions.hasOwnProperty( "layout_" + self.get_current_layout() ) ? self.layoutOptions[ "layout_" + self.get_current_layout()] : null;
    };

    self.setLayoutOption = function (option, add, name) {
        self.manageOptions(option, add, name);
    };

    self.setLayoutOptions = function( options )
    {
        if( self.layoutOptions === null ) return;

        self.layoutOptions[ "layout_" + self.get_current_layout()] = options;
    };

    self.setInitialStateLayoutOptions = function (dialog, checkboxes) {
        self._checkboxes = checkboxes;

        self._checkboxes.each(function (i) {
            if (jQuery(this).is(':checked')) {
                self.setLayoutOption(jQuery(this).val(), jQuery(this).is(':checked'), jQuery(this).prop('name'));
            }
        });
    };

    self.setChangeEvents = function () {
        jQuery(document).on('change', self._checkboxes.selector, function (event) {
            self.setLayoutOption(jQuery(this).val(), jQuery(this).is(':checked'), jQuery(this).prop('name'));
            self.eventDispatcher.trigger('checkboxes_changed', jQuery(this).parent().parent().parent(), jQuery(this).prop('name') );
        });
    };

    self.enable_disable_button_on_change = function (name) {
        var check = self.getLayoutOption(name) ,
            cache = self.get_cache(name);

        if (typeof check == 'undefined' || check === null ) check = [];
        if (typeof cache === 'undefined' || cache === null ) cache = [];

        var equals = _.isEqual( check.sort(), cache.sort() );

        // arrays should sorted to be compared
        return equals;
    };

    // turn off events handling when dialog closes
    self.clean_up_dialog_events = function () {
        self.clean_up_options_data();
        jQuery(document).off('change', self._checkboxes.selector, false);
        jQuery(document).off('click', post_types_change_button.selector, false);
        jQuery(document).off('click', archives_change_button.selector, false);
        self._checkboxes = [];
    };

    // clear all checkboxes related data
    // (this is meant for Editor page since we don't have a view.render() method there - the benefits of using MVC :-) )
    self.clean_up_options_data = function( )
    {
        var cache = self.get_cache();

        self._checkboxes.each(function ( i, v ){
            var name = jQuery(v).prop('name'), value = jQuery(v).val();
            if( cache[name].indexOf(value) === -1 )
            {
                jQuery(v).prop( 'checked', false );
            }
        });

        self.setLayoutOptions( {} );
        cache = null;
    };

    self.init();
};

DDLayout.ChangeLayoutUseHelper.prototype.eventDispatcher = _.extend({}, Backbone.Events);

DDLayout.ChangeLayoutUseHelper.manageSpinner = {
    spinnerContainer: jQuery('<div class="spinner ajax-loader">'),
    addSpinner: function (target) {
        var self = this;
        jQuery(target).parent().insertAtIndex(0,
            self.spinnerContainer.css({float: 'none', display: 'inline-block', marginTop: '4px'})
        );
    },
    removeSpinner: function () {
        this.spinnerContainer.hide().remove();
    }
};


(function ($) {
    $(function () {
        DDLayout.changeLayoutUseHelper = new DDLayout.ChangeLayoutUseHelper($);
    })
}(jQuery));