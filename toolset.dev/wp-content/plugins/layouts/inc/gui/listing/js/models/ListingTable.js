DDLayout.listing.models.ListingTable = Backbone.Model.extend({
	url:ajaxurl,
	Groups:null,
	NOT_ASSIGNED:1,
	TO_PAGE:2,
	TO_TYPES:3
	, initialize:function( json )
	{
		// very rough singleton implementation;
		if( DDLayout.listing.models.ListingTable.instance === this ) return this;

		var self = this,
			data = json;

		DDLayout.listing.models.ListingTable.instance = self;

		self.parse( data );
		self.listenTo(self, 'make_ajax_call', self.get_data_from_server, self);
	},
	/**
	 * performs an ajax call to get json data
	 */
	get_data_from_server: function (params, callback, args, scope) {

		var self = this,
			send = params || {
				action: 'get_ddl_listing_data',
				ddl_listing_nonce: DDLayout_settings.DDL_JS.ddl_listing_nonce,
				status: DDLayout_settings.DDL_JS.ddl_listing_status
			};

		params.current_page_status = DDLayout_settings.DDL_JS.ddl_listing_status;

		self.fetch({
			data: jQuery.param(send),
			type: 'POST',
			success: function ( model, response, object ) {
				//if (typeof jQuery.colorbox != 'undefined') jQuery.colorbox.close();
				if( typeof callback != 'undefined' && typeof callback == 'function') {
					callback.call( scope || self, model, response, object, args );
				}
			},
			error: function () {
				//console.error(arguments);
			}
		});
	},
	parse:function( data, attrs )
	{
		if( this.get('Groups') === null && data.Data === undefined ) return null;
		if( this.get('Groups') !== null && data.Data === undefined ) return this.get('Groups');

		this.set('Groups', new DDLayout.listing.models.ListingGroups(data.Data, {
			parse: true
		}) );

		this.set('id', 0 );
		this.set('name', 'master');
		this.unset('Data');

		return data.Data;
	},
	get_by_id:function( value )
	{
		var self = this, ret = null, groups;

		groups = self.get('Groups');
		groups.each(function(v, k, l){
			if( v.get('items').get(value) )
			{
				ret = v.get('items').get(value);
			}
		});

		return ret;
	},
	remove_by_id:function( array_of_ids, data )
	{
		var self = this;
		_.each(array_of_ids, function (v) {
			var remove = self.get_by_id(v);
			if (remove && remove.hasOwnProperty('collection')) {
				remove.collection.remove(remove, {silent: true});
				self.trigger('removed_batched_items', data );
			}
		});
	},
	search:function(s)
	{
		//if( s === '' || !s ) return;

		var self = this, search = s,  push = [], parents = [];

		 self.get('Groups').each(function(g,k,l){
			  var to_json = g.get('items')
				  , items
				  , term = search.toLocaleLowerCase(/*better explicitly pass locale as argument*/)
				  , cache = self.cache;

			 if( cache && self.cache[k] && cache[k].hasOwnProperty('items') ) to_json.reset(cache[k].items, {silent:true})
			// to be refines
			 items = to_json.filter(function(model) {
				 if( model && model.get('post_name') && model.get('post_title') )
				 return model.get('post_name').indexOf(term) === 0 ||
				        model.get('post_title').toLocaleLowerCase().indexOf(term) === 0 ||
				        model.get('post_title').indexOf(term) === 0 ||
				        model.get('post_title').toLocaleLowerCase().indexOf(term) !== -1
			 });

			 _.each(items, function(v){
				 if( v && v.get('parent') ) self.get_parents( v.get('parent'), parents );
			 });

			 push[k] = items;
		 });

		_.each(push, function(v,k,l){
			self.get('Groups').models[k].get('items').reset( push[k], {silent:true} );
		});

		self.get('Groups').models[0].get('items').add(parents, {silent:true})

		self.trigger('done_searching');
	},
	set_depth: function (element) {
		var self = this,
			el = element,
			depth = 0;

		if (!el.get('is_child')) {
			return depth;
		}

		var rec_depth = function (v) {
			var by_id = self.get_by_id( v.get('parent') );
			if( !v || !by_id ) return depth;
			if (v.get('is_child') && !by_id.get('is_parent')) {
				return depth;
			}
			else if (v.get('is_child') && by_id.get('is_parent')) {
				depth++;
				return rec_depth( by_id );
			}

			return depth;
		}

		return rec_depth(el);
	},
	set_depths_and_group:function()
	{
		var self = this;

		self.get('Groups').each(function(v, k, l){
			var group = v.get('id');
			v.get('items').group = group;
			v.get('items').map(function(i){
				i.set('group', group);
				i.set( 'depth', self.set_depth( i ) );
			});
		});
	},
	get_parents:function( id, parents )
	{
		var self = this, parent = self.get_by_id( id );

		if( null === parent ) return parents;

		if( !parent.get('parent') ) parents.push( parent );

		if( parent.get('parent') )
		{
			var next = self.get_by_id( parent.get('parent') );
			parents.push( parent );
			if( null != next )
			{
				parents.push( next );
				self.get_parents( next.get('parent'), parents );
			}
		}
		return parents;
	}
});

// make a singleton of the main model
DDLayout.listing.models.ListingTable.get_instance = function( json )
{

	if( !json && !DDLayout.listing.models.ListingTable.instance)
	{
		throw new ReferenceError("You should supply a valid json data object to ListingTable object singleton accessor.");
	}

	if( typeof DDLayout.listing.models.ListingTable.instance === 'undefined' || !DDLayout.listing.models.ListingTable.instance )
	{
		DDLayout.listing.models.ListingTable.instance = new DDLayout.listing.models.ListingTable( json );
	}
	return DDLayout.listing.models.ListingTable.instance;
};