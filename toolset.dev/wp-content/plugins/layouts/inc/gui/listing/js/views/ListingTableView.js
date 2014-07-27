DDLayout.listing.views.ListingTableView = Backbone.View.extend({
	el: ".js-dd-layout-listing",
	initialize: function (options) {
		var self = this;

		_.bindAll( self, 'render', 'afterRender');

		self.render = _.wrap(self.render, function(render, args) {
			render(args);
			_.defer(self.afterRender, _.bind(self.afterRender, self) );
			return self;
		});

		self.options = options;
		self.$el.data('view', self);
		self.model.is_searching = false;
	//	self.listenTo(self.model, 'sync', self.render, options);
        self.listenTo(self.eventDispatcher, 'changes_in_dialog_done', self.render, options);
		self.listenTo(self.model, 'changed_usage', self.render, options);
		self.listenTo(self.eventDispatcher, 'resort', self.render, options);
		self.listenTo(self.eventDispatcher, 'changeLayoutStatus', self.changeLayoutStatus);
		self.listenTo(self.eventDispatcher, 'delete_forever', self.deleteForever);
		self.listenTo(self.model, 'removed_batched_items', self.manage_count_items);
		self.listenTo(self.eventDispatcher, 'manage_count_items', self.manage_count_items);
		self.listenTo( self.model, 'done_searching', self.render );
		self.listenTo( self.eventDispatcher, 'do_what_you_have_to_on_scroll', self.highlight );

		self.manage_sort_icon_init();

		self.resort_by();
		self.manage_search();

		self.render(options);
	},
	render: function ( option ) {

		//console.log('render the full table');

		var self = this,
			options = option || {};

		self.model.set_depths_and_group();

		if( self.model.is_searching === false )
		{
			//console.log('table render', 'caching')
			self.model.cache = self.model.get('Groups').toJSON();
		}
		else if( self.model.is_searching )
		{
		//	console.log('table render', 'not caching')
			self.model.is_searching = false;
		}

	//	self.clean_up_relationship_data();

		self._cleanBeforeRender(self.$el)

		if ( self.model.get('Groups') ) {
			var groups = self.model.get('Groups');
			options = _.extend(options, { model: groups});
			self.groups_view = new DDLayout.listing.views.ListingGroupsView(options);
		}

		self.select_bulk_action();
		self.select_single_item_in_list();
		self.handle_bulk_action();

		return self;
	},
	afterRender:function()
	{
		var self = this;
		self.eventDispatcher.trigger('table_view_after_render');
	},
	manage_sort_icon_init:function()
	{
			var self = this, direction = jQuery.jStorage.get( 'reverseSortDirection' ) || false;

			if( direction )
			{
				jQuery( '.icon-sort-by-alphabet').removeClass('icon-sort-by-alphabet').addClass('icon-sort-by-alphabet-alt');
				jQuery( '.icon-sort-by-attributes').removeClass('icon-sort-by-attributes').addClass('icon-sort-by-attributes-alt');
				self.icon_title = 'icon-sort-by-alphabet-alt';
				self.icon_date = 'icon-sort-by-attributes-alt';
			}
			else
			{
				jQuery( '.icon-sort-by-alphabet-alt').removeClass('icon-sort-by-alphabet-alt').addClass('icon-sort-by-alphabet');
				jQuery( '.icon-sort-by-attributes-alt').removeClass('icon-sort-by-attributes-alt').addClass('icon-sort-by-attributes');
				self.icon_title = 'icon-sort-by-alphabet';
				self.icon_date = 'icon-sort-by-attributes';
			}

			if( jQuery.jStorage.get( 'sortKey') == 'post_title' ) {
				jQuery( '.js-icon-sort-title').addClass('sort-icon-active');
				jQuery( '.js-icon-sort-date').addClass('sort-icon-inactive');
			}
			else
			{
				jQuery( '.js-icon-sort-title').addClass('sort-icon-inactive');
				jQuery( '.js-icon-sort-date').addClass('sort-icon-active');
			}
	},
	/*
	 ** remove all the children view to clean event queue
	 */
	_cleanBeforeRender: function (el) {
		var self = this;

		el.find('tbody').each(function (i, v) {
			if (jQuery(v).data('view')) {
				self._cleanBeforeRender(jQuery(v));
				jQuery(v).data('view').remove();
			}

		});
	},
	resort_by: function () {
		var self = this,
			$button = jQuery('.js-views-list-sort');

		jQuery(document).on('click', $button.selector, function (event) {
			event.preventDefault();
			var sort_by = jQuery(this).data('orderby')
				, order_by = 'post_title'
				, icon = '';

		//	console.log( jQuery(this).data(), sort_by );

			if (sort_by == 'title') {
				order_by = 'post_title';
				jQuery('a[data-orderby="date"]').find('i').removeClass('sort-icon-active').addClass('sort-icon-inactive');
				jQuery(this).find('i').removeClass('sort-icon-inactive').addClass('sort-icon-active');
				icon = self.icon_title;
			}
			else if (order_by = 'date') {
				order_by = 'post_date';
				jQuery('a[data-orderby="title"]').find('i').removeClass('sort-icon-active').addClass('sort-icon-inactive');
				jQuery(this).find('i').removeClass('sort-icon-inactive').addClass('sort-icon-active');
				icon = self.icon_date;
			}

			self.resort(order_by, jQuery(this), icon)
		});
	},
	resort: function (by, target, icon) {
		var self = this, sort_by = by;

		self.model.get('Groups').each(function (v, k, l) {
			if (v.get('items')) {
				var collect = v.get('items');

				collect.sortKey = sort_by;

				if (collect.reverseSortDirection === false) {
					collect.reverseSortDirection = true;
				}
				else {
					collect.reverseSortDirection = false;
				}

				if( icon.indexOf('-alt') === -1 ){
					target.find('i').removeClass(icon).addClass(icon + '-alt');
					self.icon_title = 'icon-sort-by-alphabet-alt';
					self.icon_date = 'icon-sort-by-attributes-alt';
				}
				else
				{
					var new_ico = icon.split('-alt');
					target.find('i').removeClass(icon).addClass(new_ico[0]);
					self.icon_title = 'icon-sort-by-alphabet';
					self.icon_date = 'icon-sort-by-attributes';
				}

				jQuery.jStorage.set( 'sortKey', collect.sortKey );
				jQuery.jStorage.set( 'reverseSortDirection', collect.reverseSortDirection );
				collect.sort();
			}
		});
		self.eventDispatcher.trigger('resort');
	},
	select_bulk_action: function () {
		var self = this
			, select_all = jQuery('.js-select-all-layouts', self.$el)
			, checkboxes = jQuery('.js-selected-items', self.$el);

		jQuery(document).on('change', select_all.selector, function (event) {
			if (jQuery(this).is(':checked') === true) {
				select_all.each(function (i) {
					jQuery(this).prop('checked', true);
				});

				checkboxes.each(function (i) {
					if(!jQuery(this).is(':disabled')) {
						jQuery(this).prop('checked', true);
					}
				});
			}
			else if (jQuery(this).is(':checked') === false) {
				select_all.each(function (i) {
					jQuery(this).prop('checked', false);
				});
				checkboxes.each(function (i) {
					jQuery(this).prop('checked', false);
				});
			}
		});
	},
	select_single_item_in_list: function () {
		var self = this
			, select_all = jQuery('.js-select-all-layouts', self.$el)
			, checkboxes = jQuery('.js-selected-items', self.$el);

		jQuery(document).on('change', checkboxes.selector, function (event) {
			var len = checkboxes.length;

			if (jQuery(this).is(':checked') === false) {
				if (select_all.prop('checked') === true) {
					select_all.prop('checked', false);
				}
			}
			else if (jQuery(this).is(':checked') === true && jQuery(checkboxes.selector + ':checked').length === len) {
				select_all.prop('checked', true);
			}
		});
	},
	changeLayoutStatus: function (data_obj, value, callaback) {
		var self = this;

		var params = {
			action: 'set_layout_status',
			'layout-select-trash-nonce': data_obj.trash_nonce,
			status: value,
			layout_id: data_obj.layout_id
		};

		self.model.trigger('make_ajax_call', params, callaback);
	},
	handle_bulk_action: function () {
		var self = this,
			select_bulk = jQuery('.js-select-bulk-action')
			, checkboxes = jQuery('.js-selected-items')
			, select_bulk = jQuery('.js-select-bulk-action')
			, apply_bulk = jQuery('.js-do-bulk-action');

		jQuery(document).on('click', apply_bulk.selector, function (event) {
			event.preventDefault();


			if (+select_bulk.val() === -1 || jQuery(checkboxes.selector + ':checked').length === 0) {
				return false;
			}
			else if (select_bulk.val() === "trash" || select_bulk.val() === "publish") {
				var data = jQuery(this).data('object'),
					to_delete = [];

				jQuery(checkboxes.selector + ':checked').each(function () {
					to_delete.push(+jQuery(this).val());
				});

				data.layout_id = to_delete;
				data.value = select_bulk.val();

				self.changeLayoutStatus(data, select_bulk.val(), function (model, response) {
					if (response && response.message) {
						//self.model.remove_by_id( response.message, data )
						_.each(response.message, function (v) {
							self.model.trigger('removed_batched_items', data );
						});
                        self.eventDispatcher.trigger('changes_in_dialog_done');
					}
				});
			}
			else if (select_bulk.val() === "delete") {
				var data = jQuery(this).data('object'),
					to_delete = [];

				jQuery(checkboxes.selector + ':checked').each(function () {
					to_delete.push(+jQuery(this).val());
				});

				data.layout_id = to_delete;
				data.value = select_bulk.val();

				self.deleteForever(data);
			}
			select_bulk.val(-1);
		});

	},
	deleteForever: function ( data_obj ) {

		var self = this, params = {
			action: 'delete_layout_record',
			'layout-delete-layout-nonce': data_obj.delete_nonce,
			layout_id: data_obj.layout_id
		};

		self.model.trigger('make_ajax_call', params, function (model, response) {
			if (response && response.message) {
				//self.model.remove_by_id( response.message, data_obj )
				_.each(response.message, function (v) {
					self.model.trigger('removed_batched_items', data_obj );
				});
                self.eventDispatcher.trigger('changes_in_dialog_done');
			}
		});
	},
	manage_count_items:function( data_object )
	{

		//console.log( 'should count ', data_object )

		var trash = jQuery('.count-trash')
			, publish =  jQuery('.count-published')
			, trash_count = +trash.text()
			, publish_count = +publish.text();

		if( data_object.value == 'trash' )
		{
			trash.text( trash_count + 1 );
			publish.text( publish_count -1 );
		}
		else if( data_object.value == 'publish' )
		{
			trash.text( trash_count - 1 );
			publish.text( publish_count + 1 );
		}
		else if( data_object.value == 'delete' )
		{
			trash.text( trash_count - 1 );
		}
		else if( data_object.value == 'duplicate' ){
			publish.text( publish_count + 1 );
		}
	},
	manage_search:function()
	{
		var self = this,
			$button= jQuery( '#search-submit'),
			$input = jQuery('#post-search-input', self.$el),
			$current = jQuery( 'a.current', self.$el),
			init_val = $input.val();

		self.$el.on('click', $button.selector, function(event){
			event.preventDefault();
			self.model.is_searching = true;
			self.model.search( $input.val() );
			$input.val('');
		});

		$input.on('focus', function(){
			if( jQuery(this).val() == '' || jQuery(this).val() == init_val )
			jQuery(this).val('');
		});

		$input.on('blur', function(){
			if( jQuery(this).val() === '' )
			jQuery(this).val( init_val );
		});

		$input.on('keyup', function() {

			if( jQuery(this).val() )
			{
				self.model.is_searching = true;
				self.model.search( jQuery(this).val() );
			}
			else
			{
				$current.trigger('click');
			}
		});
		jQuery(document).on('click', $current.selector, function(event){
			var $me = jQuery(event.target);
			if( $me.prop('name') == DDLayout_settings.DDL_JS.ddl_listing_status )
			{
				event.preventDefault();
				self.model.parse( {Data:self.model.cache} );
				self.model.trigger('done_searching');

			}
		});
	},
	highlight:function( view, avoid )
	{
		var self = this;

		window.scrollTo( 0, view.$el.offset().top - ( view.$el.height() * 1.5 )  );
		view.$el.css({background:"#FFFFE0", opacity:0.2});
		view.$el.animate({
			opacity:1,
			specialEasing: {
				background: "easeOutBounce"
			}
		}, 1600, function() {
			view.$el.animate({
				opacity:0.9,
				specialEasing: {
					background: "linear"
				}
			}, 400, function(){
				view.$el.css({background:"#f9f9f9", opacity:1})
			});
			DDLayout.listing_manager.listing_table_view.current = null;
		});
	}
});