DDLayout.listing.views.ListingGroupsView = DDLayout.listing.views.abstract.CollectionView.extend({
	tagName:'tbody',
	el:'.js-listing-table',
	initialize:function( options )
	{
		var self = this;
		self.options = options;
		self.$el.data( 'view', self );

		DDLayout.listing.views.abstract.CollectionView.prototype.initialize.call(self, options);


	},
	render: function (option) {
		var self = this,
			options = _.extend({}, option );

		self._cleanBeforeRender( self.$el );

		self.fragment = document.createDocumentFragment();

		self.appendModelElement( options );

		self.$el.find('thead').after( self.fragment );

		return self;
	}
});
