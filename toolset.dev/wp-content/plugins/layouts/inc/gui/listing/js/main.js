var DDLayout = DDLayout || {};

DDLayout.listing = {};
DDLayout.listing.views = {};
DDLayout.listing.models = {};
DDLayout.listing.views.abstract = {};

DDLayout_settings.DDL_JS.ns = head;
DDLayout_settings.DDL_JS.listing_open = {1:true, 2:true, 3:true};

DDLayout_settings.DDL_JS.ns.js(
	DDLayout_settings.DDL_JS.lib_path + "jstorage.min.js"
	, DDLayout_settings.DDL_JS.lib_path + "prototypes.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingItem.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingItems.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingGroup.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingGroups.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "models/ListingTable.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/abstract/CollectionView.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingGroupView.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingGroupsView.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingItemView.js"
	, DDLayout_settings.DDL_JS.listing_lib_path + "views/ListingTableView.js"
    , DDLayout_settings.DDL_JS.res_path + "/js/ddl_change_layout_use_helper.js"
    , DDLayout_settings.DDL_JS.res_path + "/js/ddl-individual-assignment-manager.js"
);

(function($){
	DDLayout_settings.DDL_JS.ns.ready(function(){
		DDLayout.listing_manager = new DDLayout.ListingMain($);
	});
}(jQuery));


DDLayout.ListingMain = function($)
{
	var self = this
		, post_types_change_button = $('.js-post-types-options')
        , archives_change_button = $('.js-save-archives-options')
        , others_change_button = $('.js-save-others-options');

	self._current_layout = null;
	self._post_types_change_nonce = null;

	self.init = function()
	{
			// create a namespace for our js templates to prevent conflict with reserved names in the global namespace
			_.templateSettings.variable = "ddl";

			var json = jQuery.parseJSON( jQuery('.js-hidden-json-textarea').text() ),
				listing_table = DDLayout.listing.models.ListingTable.get_instance( json );
			    self.listing_table_view = new DDLayout.listing.views.ListingTableView({model:listing_table});

            self.handle_layout_post_types_change();
	};

	self.loadChangeUseDialog = function( data_obj )
	{
		var nonce = data_obj.nonce,
			layout_id = data_obj.layout_id,
			params = {
				action:'change_layout_usage_box',
				'layout-select-set-change-nonce':nonce,
				layout_id:layout_id
			};


		WPV_Toolset.Utils.do_ajax_post( params, {success:function(response){
			self._current_layout = response.message.layout_id;
			self._post_types_change_nonce = response.message.nonce;

			var dialog = $('#ddl-change-layout-use-for-post-types-box-'+self._current_layout+'-'+data_obj.group +' .ddl-dialog-content'),
                dialog_wrap = $('#ddl-change-layout-use-for-post-types-box-'+self._current_layout+'-'+data_obj.group);

			dialog.html( response.message.html_data );

			jQuery.colorbox({
				href: '#ddl-change-layout-use-for-post-types-box-'+self._current_layout+'-'+data_obj.group,
				inline: true,
				open: true,
				closeButton:false,
				fixed: true,
				top: '50px',
                onLoad:function(){
                    DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('on-assignment_dialog-open', dialog_wrap );
                },
                onOpen:function()
                {
                    var checkboxes = jQuery('.js-ddl-post-type-checkbox-change', $('#ddl-change-layout-use-for-post-types-box-'+self._current_layout+'-'+data_obj.group) );
                    DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('change-layout-use-open', dialog_wrap, self._current_layout, checkboxes );
                },
				onComplete: function() {
                    DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('on-assignment_dialog-complete', dialog_wrap);
				},
                onClosed:function(){
                   self.listing_table_view.eventDispatcher.trigger('changes_in_dialog_done');
                },
				onCleanup: function() {
                    DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('assignment_dialog_close');
				}
			});
		}});
	};


    self.handle_layout_post_types_change = function()
    {
        jQuery(document).on('click', post_types_change_button.selector, function(event){
            var send = {};
            send[DDLayout_settings.DDL_JS.POST_TYPES_OPTION] = DDLayout.changeLayoutUseHelper.getLayoutOption(DDLayout_settings.DDL_JS.POST_TYPES_OPTION);
            self.do_ajax_change_call( event, send, 'change_layout_usage_for_post_types_js', DDLayout_settings.DDL_JS.POST_TYPES_OPTION );
        });

        jQuery(document).on('click', archives_change_button.selector, function(event){
            var send = {};
            send[DDLayout_settings.DDL_JS.ARCHIVES_OPTION] = DDLayout.changeLayoutUseHelper.getLayoutOption(DDLayout_settings.DDL_JS.ARCHIVES_OPTION);
            self.do_ajax_change_call( event, send, 'change_layout_usage_for_archives_js', DDLayout_settings.DDL_JS.ARCHIVES_OPTION );
        });

        jQuery(document).on('click', others_change_button.selector, function(event){
            var send = {};
            send[DDLayout_settings.DDL_JS.OTHERS_OPTION] = DDLayout.changeLayoutUseHelper.getLayoutOption(DDLayout_settings.DDL_JS.OTHERS_OPTION);
            self.do_ajax_change_call( event, send, 'change_layout_usage_for_others_js', DDLayout_settings.DDL_JS.OTHERS_OPTION );
        });
    };


    self.do_ajax_change_call = function( event, data, action, name )
    {
        var params = {
                action:action,
                'layout-set-change-post-types-nonce':self._post_types_change_nonce,
                layout_id:self._current_layout
            };

        params = _.extend( params, data );

        jQuery( event.target ).prop( 'disabled', true).removeClass('button-primary').addClass('button-secondary');

        DDLayout.ChangeLayoutUseHelper.manageSpinner.addSpinner( event.target );

        self.listing_table_view.model.trigger('make_ajax_call',  params, function( model, response, object, args ){
            self.listing_table_view.current = +params.layout_id;
            DDLayout.ChangeLayoutUseHelper.manageSpinner.removeSpinner();
            DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('data_sent_to_server', name);
        });
    };

	self.init();
};