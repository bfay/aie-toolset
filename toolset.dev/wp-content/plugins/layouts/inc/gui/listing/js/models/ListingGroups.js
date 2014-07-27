DDLayout.listing.models.ListingGroups = Backbone.Collection.extend({
	model: DDLayout.listing.models.ListingGroup
	,initialize: function(){
		//console.log('LISTING GROUPS')
	},
	parse:function(data)
	{
		return data;
	}
});