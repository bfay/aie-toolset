DDLayout.listing.views.ListingItemView = Backbone.View.extend({
	tagName:'tr',
	initialize:function(options)
	{
		var self = this;

		_.bindAll( self, 'render', 'afterRender');

		self.render = _.wrap(self.render, function(render, args) {
			render(args);
			_.defer(self.afterRender, _.bind(self.afterRender, self) );
			return self;
		});

		self.options = options;

		self.$el.data( 'view', self );

		self.$el.addClass("type-dd_layouts status-"+self.model.get('post_status')+" hentry alternate iedit");

		self.restore_or_trash_layout_from_link();
		self.delete_permanently_from_link();
		self.manageSelection();

		self.render( options );
	},
	render: function( option )
	{
		var self = this,
			options = option || {};

	//	console.log( self.model.get('post_name'), self.model.get('group') )

		if( DDLayout_settings.DDL_JS.ddl_listing_status === 'publish')
		{
			if( self.model.get('is_parent') && self.model.has_active_children() )
			{
				self.template = _.template( jQuery('#table-listing-item-parent').html() );
			}
			else if( self.model.has_parent() )
			{
				self.template = _.template( jQuery('#table-listing-item').html() );
				self.$el.addClass('child-layout js-child-layout')
			}
			else
			{
				self.template = _.template( jQuery('#table-listing-item').html() );
			}

			if( self.model.get('types') )
			{
				options.post_types = self.model.get('types');
			}

			options.is_assigned = self.model.is_assigned() ? true : false;
		}
		else if( DDLayout_settings.DDL_JS.ddl_listing_status === 'trash' )
		{
			self.template = _.template( jQuery('#table-listing-item').html() );
		}

		self.$el.html( self.template( _.extend( self.model.toJSON(), options) ) );

		return self;
	},
	afterRender:function()
	{
		var self = this;
		self.manage_tooltip();
		self.highlight();
	},
	restore_or_trash_layout_from_link: function()
	{
	var self = this,
		restore_link = jQuery('.js-layout-listing-restore-link');

		self.$el.on('click', restore_link.selector, function(event){
			event.preventDefault();

			var data_object = jQuery(this).data('object');

			if( ( data_object.value == 'trash' || data_object.value == 'change') && jQuery( event.target, self.$el).hasClass('strike') )
			{
				return false;
			}
			else
			{
				self.eventDispatcher.trigger('manage_count_items', data_object, data_object.value );

				self.$el.fadeOut(200, function(){
					self.eventDispatcher.trigger('changeLayoutStatus', data_object, data_object.value, function(){
						self.model.collection.remove( self.model );
                        self.eventDispatcher.trigger('changes_in_dialog_done');
					});
				});
			}
		});
	},

	manage_tooltip:function()
	{
		var self = this,
			el = jQuery('span.strike a', self.$el),
			message = '';

			el.tooltip({
				position:{ my: "left top+15", at: "left middle", collision: "flipfit" },
				open:function( e, ui )
				{
					var data = 	jQuery( e.target).data('object');

					if( self.model.is_parent() )
					{

						if( data && data.value == 'trash' && self.model.has_active_children() )
						{
							message = DDLayout_settings.DDL_JS.strings.is_a_parent_layout;
						}
						else
						{
							message = DDLayout_settings.DDL_JS.strings.is_a_parent_layout_and_cannot_be_changed;
						}
					}
					else
					{
						switch( self.model.get('group') )
						{
                            case 4:
                                var len = self.model.get('loops').length;
                                if( len === 1 )
                                {
                                    message = DDLayout_settings.DDL_JS.strings.to_an_archive;
                                }
                                else
                                {
                                    message = DDLayout_settings.DDL_JS.strings.to_archives.printf( len.toString() );
                                }
                                break;
							case 3:
								var len = self.model.get('types').length;
								if( len === 1 )
								{
									message = DDLayout_settings.DDL_JS.strings.to_a_post_type;
								}
								else
								{
									message = DDLayout_settings.DDL_JS.strings.to_post_types.printf( len.toString() );
								}
								break;
							case 2:
								var len = self.model.get('posts').length;
								if( len === 1 )
								{
									message = DDLayout_settings.DDL_JS.strings.to_a_post_item;
								}
								else
								{
									message = DDLayout_settings.DDL_JS.strings.to_posts_items.printf( len.toString() );
								}
								break;
						}

						message = message;
					}

					jQuery( e.target).tooltip( "option", "content", message );
				}
			});
	},

	delete_permanently_from_link: function()
	{
		var self = this,
			delete_permanently_link = jQuery('.js-layout-listing-delete-permanently-link');

		self.$el.on('click', delete_permanently_link.selector, function(event){
			event.preventDefault();
			var data_object = jQuery(this).data('object');
			self.eventDispatcher.trigger('delete_forever', data_object);
		})
	},

	manageSelection : function()
	{
		var self = this,
			select = jQuery('.js-select-layout-action-in-listing-page');

		self.$el.on('click', select.selector, function(event){

			var data_object = jQuery(this).data('object');

			if( data_object.value === 'change' )
			{
				DDLayout.listing_manager.loadChangeUseDialog( data_object )
			}
			else if( data_object.value === 'trash' || data_object.value === 'publish' )
			{
				jQuery( '.js-layout-listing-restore-link', self.$el ).trigger('click');
			}
			else if( data_object.value === 'delete' )
			{
				jQuery( '.js-layout-listing-delete-permanently-link', self.$el ).trigger('click');
			}
			else if( data_object.value === 'duplicate' )
			{
				self.duplicate( data_object );
			}
		});

		self.$el.on('blur', select.selector, function(event){
				jQuery(this).val("");
		});
	},
	duplicate:function( data_obj )
	{
        var self = this;
		var params = {
			action: 'duplicate_layout',
			'layout-duplicate-layout-nonce':data_obj.duplicate_nonce,
			layout_id:data_obj.layout_id
		};

		DDLayout.listing_manager.listing_table_view.$el.loaderOverlay('show');
		DDLayout.listing_manager.listing_table_view.model.trigger( 'make_ajax_call', params, function(model, response, object, args){
			DDLayout.listing_manager.listing_table_view.current = response.added;
			DDLayout.listing_manager.listing_table_view.$el.loaderOverlay('hide');
			DDLayout.listing_manager.listing_table_view.manage_count_items( data_obj );
            DDLayout.listing_manager.listing_table_view.eventDispatcher.trigger('changes_in_dialog_done');
		});
	},
	highlight:function()
	{
		var self = this;
		try
		{
			if( self.model.get( 'id' ) && DDLayout.listing_manager.listing_table_view.current && DDLayout.listing_manager.listing_table_view.current === self.model.get( 'id' ) )
			{
				self.eventDispatcher.trigger( 'do_what_you_have_to_on_scroll', self );
			}
		}
		catch( e )
		{
			console.log( e.message );
		}

	}
});