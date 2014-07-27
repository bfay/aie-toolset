DDLayout.models.cells.Row = DDLayout.models.abstract.Element.extend({
	defaults:{
		Cells: DDLayout.models.collections.Cells
        ,cssClass:'row-fixed'
		,kind: 'Row'
        ,layout_type:'fixed'
		,mode:'normal'
	}
	/**
	 * @access: public
	 * @return: int
	 * returns the width in span of row instance
	 */
	, getWidth:function()
	{
		var self = this, width = 0;

		if( !self.get('Cells') ) return 1;
		
		_.each( self.get('Cells').models, function( item, index, list ) {
				width += item.get('width') * item.get('row_divider');
		});
		
		return width;
	},
    isEmpty:function()
    {
        return _.isEmpty( this.get('Cells') );
    },
    setLayoutType:function( type )
    {
        this.set( 'layout_type', type );
    },
    getLayoutType:function()
    {
        return this.get('layout_type');
    },
	get_parent_width: function ( row ) {
		// see if the given row is inside a container in this row
		var self = this;
		
		var cells = self.get('Cells');

		if( !cells ) return 0;

		for (var i = 0; i < cells.length; i++) {
			var test_cell = cells.at(i);
			if (test_cell.get('kind') == 'Container') {
				var rows_in_container = test_cell.get('Rows');
				var parent_width = test_cell.get('width');
				parent_width = rows_in_container.get_parent_width( row, parent_width);
				if (parent_width > 0) {
					return parent_width;
				}
			}
		}

		return 0;
	},
	get_empty_space_to_right_of_cell: function ( cell ) {
		var self = this;
		
		var cells = self.get('Cells');

		if( !cells ) return -1;

		for (var i = 0; i < cells.length; i++) {
			var test_cell = cells.at(i);
			if (_.isEqual(test_cell, cell)) {
				var count = 0;
				for (var j = i + 1; j < cells.length; j++) {
					test_cell = cells.at(j);
					if (!test_cell.isEmpty()) {
						return count;
					}
					count++;
				}
				return count;
			}
			if (test_cell.get('kind') == 'Container') {
				var rows_in_container = test_cell.get('Rows');
				var space = rows_in_container.get_empty_space_to_right_of_cell(cell);
				if (space >= 0) {
					return space;
				}
			}
		}
		
		return -1;
	},
	
	find_cell_of_type : function ( cell_type ) {
		var self = this;

		var cells = self.get('Cells');

		if( !cells ) return false;

		for (var i = 0; i < cells.length; i++) {
			var test_cell = cells.at(i);
			if (test_cell.get('cell_type') == cell_type) {
				return test_cell;
			}
			
			if (test_cell.get('kind') == 'Container') {
				var rows_in_container = test_cell.get('Rows');
				var cell = rows_in_container.find_cell_of_type(cell_type)
				if (cell) {
					return cell;
				}
			}
		}

		return false;
	},
	
	changeWidth : function (new_width) {
		var self = this;

		var cells = self.get('Cells');
		var current_width = self.getWidth();

		if( !cells ) return;
		
		if (new_width > current_width) {
			var amount_to_add = new_width - current_width;
			cells.addCells( 'Cell', 'spacer', amount_to_add, self.getLayoutType(), 1);
		}
		if (new_width < current_width) {
			var width = 0;
			var cells_to_remove = [];
			for (var i = 0; i < cells.length; i++) {
				var test_cell = cells.at(i);
				if (width >= new_width) {
					cells_to_remove.push(test_cell);
				}
				width += test_cell.get('width');
			}
			
			for (var i = 0; i < cells_to_remove.length; i++) {
				cells.remove(cells_to_remove[i]);
			}
		}
	},
	getMinWidth : function ()
	{
		var self = this;

		var cells = self.get('Cells');

		if( !cells ) return 1;

		for (var i = cells.length - 1; i >= 0; i--) {
			var test_cell = cells.at(i);
			if (test_cell.get('cell_type') != 'spacer') {
				var min_width = 0;
				for (var j = 0; j <= i; j++) {
					var test_cell = cells.at(j);
					min_width += test_cell.get('width');
				}
				return min_width;
			}
		}
	
		return 1;
	}
});