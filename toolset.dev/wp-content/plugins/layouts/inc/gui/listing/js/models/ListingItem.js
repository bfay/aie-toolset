DDLayout.listing.models.ListingItem = Backbone.Model.extend({
	url:ajaxurl,
	view_rendered:false,
	initialize: function(){
		// code here
	},
	has_parent: function()
	{
		return this.get('parent');
	},
	is_parent:function()
	{
		return this.get('is_parent');
	},
	has_active_children:function()
	{
		var self = this;

		if( self.is_parent() && self.get('children') && self.get('children').length > 0 )
		{
			return true;
		}

		return false;
	},
	is_assigned:function()
	{
		return ( this.get('types') && this.get('types').length ) || ( this.get('posts') && this.get('posts').length ) || ( this.get('loops') && this.get('loops').length );
	}
});