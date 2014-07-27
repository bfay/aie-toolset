DDLayout.views.RowView = DDLayout.views.abstract.ElementView.extend({
	events:{
		'mousedown':'_handleMouseDown'
	},
	initialize:function(options)
	{
		var self = this;
		self.options = options;
		self.errors_div = jQuery(".js-ddl-message-container");
		//call parent constructor
		DDLayout.views.abstract.ElementView.prototype.initialize.call(self, options);
		

	},
	render:function(args)
	{

		var self = this,
			itemEditorCssBaseClass = self.model.get('kind').toLowerCase()
			, rowCells, prefix = self.compound ? self.compound.toLowerCase() + '-' : '',
			options;

		self.template = _.template( jQuery('#'+ prefix + itemEditorCssBaseClass + '-template').html() );

		self.$el.html( self.template( _.extend( self.model.toJSON(), {layout_type:self.model.getLayoutType()} ) ) );

		rowCells = self.$el.find('div.row');

		self.$el.removeClass('row');

		self.$el.addClass('row-container');

		//make sure we garbage collected previous instances of cells class
		if( self.cells !== null )
		{
			self.cells = null;
		}

		options = _.extend({
			model:self.model.get("Cells"),
			el:rowCells, compound:'',
			invisibility:self.options.invisibility,
			current:self.options.current,
			parentDOM:self.$el
		}, args);

		self.cells = new DDLayout.views.CellsView( options );

		rowCells.customSortable({
			connectWith : '.row',
			tolerance : 'pointer',
			cursor : 'move',
			forcePlaceholderSize : true,
			forceHelperSize : true,
			appendTo : document.body,
			helper : 'clone',
			cancel:'.placeholder',
			scrollSensitivity:100
		});

		self._toggleRowHiglight();

		self._initializeRemoveRowHandler( );

		self._initializeAddRowHandler( );

		self._initializeEditRowHandler( );

		self._makeElementNameEditable();

		self._displayRowPlaceholderOnHover();

		return self;
	},
	_toggleRowHiglight: function () {
		var self = this;

		jQuery(self.el).find('.js-row-toolbar .js-move-row').eq(0)
			.on( 'mouseenter', function (event) {
				// mouse enter handler
				event.stopPropagation();

				jQuery(this)
					.closest('.js-row')
					.addClass('is-hovered');

				jQuery(event.target)
					.parent()
					.trigger('mouseleave');

			})
			.on( 'mouseleave', function (event) {
				// mouse leave handler
				event.stopPropagation();
				jQuery(this)
					.closest('.js-row')
					.removeClass('is-hovered');

				jQuery(event.target)
					.parent()
					.trigger('mouseenter');

			}
		);
	},
	_initializeRemoveRowHandler:function()
	{
		var self = this;

		jQuery( '.js-row-remove', self.el ).on('click', function( event ){
			event.stopImmediatePropagation();

			self._manageCellTooltip( jQuery(this), 'hide' );
			DDLayout.views.abstract.ElementView.prototype._manageCellTooltip.call(self, jQuery(event.target), 'hide' );

			self.eventDispatcher.trigger( 'ddl-remove-row', self, jQuery(this) );
		});
	},
	deleteTheRow : function () {
		
		var self = this;
			
		DDLayout.ddl_admin_page.save_undo();

		var without = _.without( self.model.collection.models, self.model );

		self.model.collection.reset( without );

		self.$el.fadeOut(100, function () {self.eventDispatcher.trigger("re_render_all");});

		
	},
	hasChildLayoutCellAndChildren : function() {
		if (this.model.find_cell_of_type('child-layout')) {
			if (jQuery('.js-child-layout-list').length) {
				return true;
			}
		}
		return false;
	},
	_initializeAddRowHandler:function()
	{
		var self = this;

		jQuery('.js-show-add-row-menu', self.el).on('click', function(e){
			e.stopImmediatePropagation();
			jQuery(e.target).trigger("open_row_context_menu", self);
		});

		jQuery('.js-show-add-special-row-menu', self.el).on('click', function(e){
			e.stopImmediatePropagation();
			jQuery(e.target).trigger("open_special_row_context_menu", self);
		});

		// the + button
		jQuery( '.js-add-row', self.el ).on('click', function(event){
			event.stopImmediatePropagation();

			DDLayout.ddl_admin_page.save_undo();

			var count = self.model.collection.length + 1;
			var new_cells = new DDLayout.models.collections.Cells,
				layout_type = self.model.get('layout_type'),
				row_name = "Row " + count;

			new_cells.layout = self.model.layout;

			var cells = self.model.get("Cells")
				, len = cells ? cells.length : DDLayout.ddl_admin_page.get_layout().get_width();

			for (var i=0; i < len; i++) {
				if( cells )
				{
					var cell = cells.at(i);
					new_cells.addCells( 'Cell', 'spacer', cell.get('width') * cell.get('row_divider'), layout_type, cell.get('row_divider'));
				}
				else
				{
					new_cells.addCells( 'Cell', 'spacer', 1, layout_type, 1 );
				}
			}

			self.model.collection.addRowAfterAnother( self.model, new_cells, row_name, '', layout_type, self.getRowDivider());

			self.eventDispatcher.trigger("re_render_all");

		});
	},
	_initializeEditRowHandler:function()
	{
		var self = this;
		jQuery( '.js-row-edit', self.el ).on('click', function(event){
			event.stopImmediatePropagation();

			DDLayout.ddl_admin_page.show_row_dialog('edit', self);
		});
		jQuery( '.js-row-css', self.el ).on('click', function(event){
			event.stopImmediatePropagation();
			DDLayout.ddl_admin_page.show_css_dialog(self);
		});

		jQuery( '.js-row-edit, .js-row-remove', self.el )
			.on('mouseenter', function(event) {
				event.stopImmediatePropagation();
				jQuery(this)
					.closest('.js-row')
					.addClass('is-hovered row-actions-hovered');

				//self._manageCellTooltip( jQuery(event.target), 'show' );
				DDLayout.views.abstract.ElementView.prototype._manageCellTooltip.call(self, jQuery(event.target), 'show' );
			})
			.on('mouseleave', function(event) {
				event.stopImmediatePropagation();
				jQuery(this)
					.closest('.js-row')
					.removeClass('is-hovered row-actions-hovered');
				DDLayout.views.abstract.ElementView.prototype._manageCellTooltip.call(self, jQuery(event.target), 'hide' );
				//self._manageCellTooltip( jQuery(event.target), 'hide' );
			});

	},
	addRow: function( row_name, additional_css, layout_type, row_divider_in)
	{
		var self = this,
			row_divider = row_divider_in ? row_divider_in : self.getRowDivider();

		DDLayout.ddl_admin_page.save_undo();

		var
			cells = new DDLayout.models.collections.Cells,
			row_width = (layout_type == 'fixed') ? self.model.getWidth() : 12;

		cells.layout = self.model.layout;

		cells.addCells( 'Cell', 'spacer', row_width, layout_type, row_divider);

		self.model.collection.addRowAfterAnother( self.model, cells, row_name, additional_css, layout_type, row_divider);

		self.eventDispatcher.trigger("re_render_all");
	},
	addThemeSectionRow:function( row_name, type, row_type, layout_type )
	{
		var self = this;

		DDLayout.ddl_admin_page.save_undo();

		self.model.collection.addThemeSectionRowAfterAnother( self.model, row_name, type, row_type, layout_type );

		self.eventDispatcher.trigger("re_render_all");
	},

	_handleMouseDown : function (event) {
	},

	get_parent_view : function () {
		return this.$el.parent().data('view');
	},

	can_add_fixed_row_below_this : function () {
		// search through the parents.
		var parent = this.get_parent_view();

		while (typeof parent != 'undefined') {
			if (parent instanceof DDLayout.views.RowView || parent instanceof DDLayout.views.ContainerView) {
				if (parent.model.get('layout_type') == 'fluid') {
					return false;
				}
			}

			parent = parent.get_parent_view();

		}

		var layout = DDLayout.ddl_admin_page.get_layout();
		return layout.get('type') == 'fixed';

	},

	is_top_level_row : function () {
		return this instanceof DDLayout.views.ContainerRowView === false;
	},

	get_cells_top : function () {
		return this.cells.get_cells_top();
	},

	set_cells_height : function() {
		return this.cells.setCellsHeight();
	},

	setCellsHeightForPreview : function () {

		this.cells._setCellsHeightForPreview();
	},

	get_cell_rows : function () {
		return this.cells.get_cell_rows();
	},

	disable_delete : function (e) {
		this.$el
			.find('.js-row-remove:first')
			.removeClass('js-row-remove')
			.addClass('disabled')
			.off('click');
	},

	disable_row_move : function (e) {
		this.$el
			.find('.js-move-row:first')
			.removeClass('js-move-row')
			.addClass('disabled')
			.off('click')
			.off('hover');
	},

	getRowDivider : function () {
		return this.model.collection.get('row_divider');
	},
	_displayRowPlaceholderOnHover : function() {
		var self = this;
		jQuery('.js-highlight-row', self.$el ).hover(function() {
			jQuery(this)
				.closest('.js-row')
				.addClass('show-row-placeholder');
		}, function(){
			jQuery(this)
				.closest('.js-row')
				.removeClass('show-row-placeholder');
		});
	}

});