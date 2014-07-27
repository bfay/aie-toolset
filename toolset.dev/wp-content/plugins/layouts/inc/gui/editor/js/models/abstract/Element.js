DDLayout.models.abstract.Element = Backbone.Model.extend({
	defaults:{
		name: '' 
		, cssClass: 'span1'
		, cssId: ''
		, tag: 'div'
		, kind: 'Element'
		, width: 1
		, row_divider: 1
        , additionalCssClasses: ''
		, editorVisualTemplateID: ''
		}
    /**
     * makes available for all child classes the Backbone
     * event object to trigger and listen to custom events
     */
	, initialize: function(){
          this.setWidthToInt();
	},
    /**
     * cast all widths to integer
     * @return:integer
     */
    setWidthToInt:function()
    {
        var self = this, int = 1;
        int = parseInt( self.get('width') );
        self.set('width', int );
        return self.get('width');
    },
    getIntWidth:function()
    {
        return parseInt( this.get('width') );
    },
    hasRows:function()
    {
        return this.has("Rows");
    }
    , hasContent:function()
    {
        return this.has("Content");
    },
    hasSomeContent:function()
    {
        return this.hasRows() || this.hasContent();
    }
    , isSpacer:function()
    {
        return this instanceof DDLayout.models.cells.Spacer;
    },
    isEmpty:function()
    {
        return true;
    },
    set:function(attributes, options)
    {
        if( attributes == 'width')
        {
            if(options) options = parseInt( options );
        }
	    else if( attributes == 'additionalCssClasses' )
        {
	        if(options){
		        options = jQuery.trim( options.replace(/,/g, ' ') );
	        }
        }
        return Backbone.Model.prototype.set.call(this, attributes, options);
    },
	get:function( attribute )
	{
		if( attribute === 'additionalCssClasses' ){
			this.attributes[attribute] = jQuery.trim( this.attributes[attribute].replace(/[ ]/g, ',') )
		}
		return Backbone.Model.prototype.get.call(this, attribute );
	}
});