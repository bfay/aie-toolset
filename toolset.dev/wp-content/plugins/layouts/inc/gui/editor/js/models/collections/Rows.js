DDLayout.models.collections.Rows = Backbone.Collection.extend({
	model:DDLayout.models.cells.Row
	, kind:'Rows'
	, initialize:function()
	{
		//console.log( "Welcome to this wordls Rows, you are a collection ", this.toJSON() );
	},
    getWidth:function()
    {
        return this.collection.length;
    },
	addRowAfterAnother:function( prev_row, cells, row_name, additional_css, layout_type, row_divider, type )
	{
		var self = this,
			index = self.indexOf( prev_row ),
			len = self.length,
			row = new DDLayout.models.cells.Row( {kind : 'Row',
												  Cells : cells,
												  cssClass : 'row-' + layout_type,
												  name : row_name,
												  additionalCssClasses: additional_css,
												  row_divider: row_divider} );
			
			//row.layout = self.layout;
			
			row.setLayoutType( layout_type );

			self.add( row, {at:index+1} );
			
			return self;
	},
	addThemeSectionRowAfterAnother:function( prev_row, row_name, type, kind, layout_type )
	{
		var self = this,
			index = self.indexOf( prev_row ),
			row = null;

		switch( kind )
		{
			case 'ThemeSectionRow':
				row = new DDLayout.models.cells.ThemeSectionRow(
					{kind : kind,
					name : row_name,
					type:type,
					layout_type:layout_type
					} );
				break;
			default:
				row = new DDLayout.models.cells.ThemeSectionRow(
					{kind : kind,
						name : row_name,
						type:type,
						layout_type:layout_type
					} );
				break;
		}

		self.add( row, {at:index+1} );

		return self;
	},
	addRows:function( amount, width, layout_type, row_divider, cellKind, cellType )
	{
		var self = this,
			row,
			cells,
			row_width = width,
			cell_kind = cellKind ? cellKind : 'Cell',
			cell_type = cellType ? cellType : 'undefined',
            layout = layout_type || DDLayout.ddl_admin_page.getLayoutType();
		
		for( var i = amount; i > 0; i--)
		{
			
			cells = new DDLayout.models.collections.Cells;
			
			cells.layout = layout  || self.layout;

			cells.addCells( cell_kind, cell_type, row_width, layout_type, row_divider );
			
			row = new DDLayout.models.cells.Row( {	kind:'Row',
													Cells:cells,
													cssClass:'row-'+layout,
													name:'Row '+i,
													row_divider: row_divider} );
			
			row.setLayoutType( layout );
			
			self.push( row );
		}
		return self;
	},
    //FIXME:there is a problem it doesn't loop through all Rows
	get_parent_width : function ( row, parent_width ) {
		// If the row is in this "Rows" then return the parent width
		var self = this;

		// see if the row is in these rows
		for (var i = 0; i < self.length; i++) {
			var test_row = self.at(i);
			if (_.isEqual(test_row, row)) {
				return parent_width;
			}
		}
		
		// haven't found the row so we need to look deeper
		for (var i = 0; i < self.length; i++) {
			var test_row = self.at(i);
			var test_width = test_row.get_parent_width( row );
			if (test_width > 0) {
				return test_width;
			}
		}

		return parent_width;
	},
	
	get_empty_space_to_right_of_cell : function ( cell ) {
		var self = this;
		
		// see if the row is in these rows
		for (var i = 0; i < self.length; i++) {
			var test_row = self.at(i);
			var space = test_row.get_empty_space_to_right_of_cell(cell);
			if (space >= 0) {
				// found in this row
				return space;
			}
		}
			
		// not found in this row
		return -1;
		
	},
	
	find_cell_of_type : function ( cell_type ) {
		var self = this;
		
		// see if the row is in these rows
		for (var i = 0; i < self.length; i++) {
			var test_row = self.at(i);
			var cell = test_row.find_cell_of_type( cell_type );
			if (cell) {
				return cell;
			}
		}

		return false;
	},
	hasRowsOfKind:function( kind )
	{
		var self = this;

		var found = _.filter(self.models, function( row ){
			return row.get('layout_type') == kind;
		});
		
		return found.length > 0;
	},
	changeLayoutType : function (new_type)
	{
		var self = this;
		for (var i = 0; i < self.length; i++) {
			var row = self.at(i);
			row.setLayoutType(new_type);
		}
		
	},
	changeWidth : function (new_width) {
		var self = this;
		for (var i = 0; i < self.length; i++) {
			var row = self.at(i);
			row.changeWidth(new_width);
		}
	},
	getMinWidth : function ()
	{
		var self = this;
		var min_width = 0;
		for (var i = 0; i < self.length; i++) {
			var row = self.at(i);
			var row_min_width = row.getMinWidth();
			min_width = Math.max(min_width, row_min_width);
		}
		
		return min_width;
	}
});