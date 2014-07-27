DDLayout.models.cells.Layout = DDLayout.models.abstract.Element.extend({
	defaults:{
		  type:''
		, name:''
		, cssframework:''
		, template:''
		, parent:0
		, Rows:DDLayout.models.collections.Rows
		, width:12
        , cssClass:'span12'
        , id: 0
        , kind: 'Layout'
		, has_child: false
		, slug: ''
		, children_to_delete : null
		, child_delete_mode : null
		, has_loop:false
		, has_post_content_cell: false
	}
	, url:ajaxurl
	, cssString:''
	, layout_slug_cached:null
	, setCssString:function( css_string )
	{
		this.cssString = css_string;
	}
	, getCssString:function()
	{
		return this.cssString;
	}
    , is_layout:function()
    {
        return true;
    }
	, numRows:function()
	{
		return this.get('Rows').length
	},
    isEmpty:function()
    {
        return _.isEmpty( this.get('Rows') );
    },
    getType:function()
    {
        return this.get('type');
    },
	get_id: function () {
		return this.get('id');
	},
	get_name: function () {
		return this.get('name');
	},
	get_width: function () {
		return this.get('width');
	},
	get_slug: function () {
		return this.get('slug');
	},
    /**
     * @override
     */
	initialize:function( json )
	{

		var self = this, data;

        DDLayout.models.abstract.Element.prototype.initialize.call(self);

        data = self.parse(json);

        if( data )
        {
			// remove the children delete info.
			data.children_to_delete = null;
			
            self.populate_self_on_first_load( data );
        }

	//	self.listenTo(self, 'created_new_cell', self.cellCreatedCallback)

        return self;
	},
	cellCreatedCallback:function( cell_model )
	{
		//
	},
    populate_self_on_first_load: function( data )
    {
        var self = this;

        if( !data ) return self;

        _.each(data, function( item, index, object ){
              if( object.hasOwnProperty( index ) )
              {
                  self.set( index, item );
              }
        });

        self.set( 'id', DDLayout_settings.DDL_JS.layout_id );

        return self;
    },
    /**
     * performs an ajax call to get json data
     */
    get_data_from_server: function () {
        var self = this;
        self.fetch({
                data: jQuery.param({action: 'get_layout_data', layout_id: DDLayout_settings.DDL_JS.layout_id}),
                type: 'POST',
                success: function (model) {
                    self.set('id', DDLayout_settings.DDL_JS.layout_id);
                    self.eventDispatcher.trigger("json_fetched", arguments[0]);
                },
                error: function () {
                console.error(arguments);
            }
        });
    }
    /**
     * @override
     */
	,parse:function( json, xhr )
	{
		try
		{
			var self = this, data = json;

			if( data !== null )
			{	
				if( !data.Rows ) return null;


				self.set('Rows', self._scan_json( data.Rows, {name:data.name, cssframerwork:data.cssframework, type:data.type, width:data.width} ) );
				delete data.Rows;
			}
		}
		catch( e )
		{
			console.error( e.message, e );
		}
		
		return data;
	}
    /**
     * @param:json - json data
     * @return:rows - model structure
     * @access:private
     */
	, _scan_json: function(json, properties)
	{
		var self = this, tmpRows = null, data = json, layout = properties;

        if( data )
        {
            tmpRows = new DDLayout.models.collections.Rows;


            _.each(data, function(r, i, rows){

                var tmp = undefined, row = r, row_model;



                if( row && row.Cells )
                {

	                tmp = new DDLayout.models.collections.Cells

                    _.each(row.Cells, function( element, j, cells ){
                        var cell = element, kind = cell.kind;

                        delete cell.kind;

                        if( cell.hasOwnProperty('Rows') )
                        {
                            var container, cell_tmp = _.extend({}, cell);
                                //we don't want wo make it copy twice one as an object and one as a model
                                delete cell_tmp.Rows;
                                container = new DDLayout.models.cells[kind](cell_tmp);

                            try
                            {

                                container.set('Rows', self._scan_json( cell.Rows, layout ) );
                                container.layout = layout;
                                tmp.push( container );
                            }
                            catch( e )
                            {
                                console.error( e.message );

                            }
                        }
                        else
                        {
                            try
                            {
                                var cell = new DDLayout.models.cells[kind]( cell );
                                cell.layout = layout;
                                tmp.push( cell );
                            }
                            catch( e )
                            {
                                console.error( e.message );

                            }
                        }
                    });

		                tmp.layout = layout;
		                row_model = new DDLayout.models.cells.Row( {Cells:tmp} );
		                //remove cells
		                delete row.Cells;

                }
                else
                {
	                if( row.kind === 'ThemeSectionRow' )
	                {
		                row_model = new DDLayout.models.cells.ThemeSectionRow();

	                }
                }

	            row_model.layout = layout;

	            //override default attributes if necessary
	            _.extend(row_model.attributes, row);
	            //add it to the Layout rows collection
	            tmpRows.push( row_model );
	            tmpRows.layout = layout;
            });
        }

		return tmpRows;
	},
	
	toJSON: function () {
		this.set( 'has_child', this.has_cell_of_type('child-layout') );
		this.set( 'has_loop', this.has_cell_of_type("post-loop-cell") || this.has_cell_of_type("post-loop-views-cell") );
        this.set('has_post_content_cell', this.has_cell_of_type("cell-post-content") || this.has_cell_of_type("cell-content-template") );
		return DDLayout.models.abstract.Element.prototype.toJSON.call(this);		
	},
	
	get_parent_width: function ( row ) {
		
		var rows = this.get('Rows');
		return rows.get_parent_width( row , this.get('width') );
		
	},
	
	get_empty_space_to_right_of_cell : function ( cell ) {

		var rows = this.get('Rows');
		return rows.get_empty_space_to_right_of_cell(cell);
		
	},
	
	has_cell_of_type : function ( cell_type ) {
		
		return this.find_cell_of_type( cell_type ) != false;
	},
	
	find_cell_of_type : function ( cell_type ) {
		
		var rows = this.get('Rows');
		
		return rows.find_cell_of_type( cell_type );
	},
	
	set_parent_layout : function ( parent_layout ) {
		this.set('parent', parent_layout);
	},
	
	get_parent_layout : function ( ) {
		return this.get('parent');
    },
    getLayoutCells:function()
    {
        return DDLayout.models.cells.Layout.getCells( this );
    },
    getLayoutContainers:function( )
    {
        return DDLayout.models.cells.Layout.getContainers( this );
    },
    getLayoutSelected:function( )
    {
        var self = this,
            cells = self.getLayoutCells( );

        if( !cells || cells == null || cells == false ) return null;

        return _.filter(cells, function(item){
                return item.selected_cell === true;
        });
    },
	changeLayoutType : function (new_type)
	{
		
		var self = this;
		
		self.set('type', new_type);
		
		var rows = this.get('Rows');
		rows.changeLayoutType(new_type);

		if (new_type == 'fluid' && self.get('width') != 12) {
			self.changeWidth(12);
		}
	},
	getMinWidth : function ()
	{
		var rows = this.get('Rows');
		return rows.getMinWidth();
		
	},
	changeWidth : function (new_width)
	{
		var self = this;

		if (self.get('width') != new_width) {
			self.set('width', new_width);

			var rows = this.get('Rows');
			rows.changeWidth(new_width);
			DDLayout.ddl_admin_page.render_all();
		}
	},
	setChildrenToDelete: function( children, mode )
	{

		if( children === null )
		{
			this.set('children_to_delete', null);
			return;
		}

		children = JSON.parse( children );

		if( 'children_layouts' in children )
		{
			this.set('children_to_delete', children['children_layouts']);
		}
		else
		{
			this.set('children_to_delete', null);
		}
		this.set('child_delete_mode', mode);
	},
	getChildrenToDelete:function()
	{
		return this.get('children_to_delete');
	}
});


/*
** @access: Static
** @return: Array
** @param: layout or container model
 */
DDLayout.models.cells.Layout.getContainers = function( check )
{
    if( check == undefined || check == null || check == false || !check || !check.has("Rows") ) return null;

    var
        rows = check.get("Rows"),
        containers = [];

    rows.each(function(item){
	    var cells = item.get("Cells");

		if( cells )
		{
			cells.each(function( r ){

				if( r.hasRows() )
				{
					containers.push( r );
					containers = _.union( containers, DDLayout.models.cells.Layout.getContainers( r ) );
				}
			});
		}
    });

    return containers;
};
/*
 ** @access: Static
 ** @return: Array
 ** @param: layout or container model
 */
DDLayout.models.cells.Layout.getCells = function( check )
{
    if( check == undefined || check == null || check == false || !check || !check.has("Rows") ) return null;

    var
        rows = check.get("Rows"),
        cells = [];

    rows.each(function(item){
		var cells = item.get("Cells");

	    if( cells )
	    {
		    cells.map(function( r ){

			    if( r.hasRows() )
			    {
				    cells.push( r );
				    cells = _.union( cells, DDLayout.models.cells.Layout.getCells( r ) );
			    }
			    cells.push( r );
		    });
	    }

    });
    return cells;
};
