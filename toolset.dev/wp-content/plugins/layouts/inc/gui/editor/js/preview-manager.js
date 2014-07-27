// preview-manager.js


DDLayout.PreviewManager = function($)
{
	var self = this;
	self._cell_heights = {};
	self._views = {};
	self._rerender = false;
	self._ignore_reset = false;

	self.init = function() {
		//jQuery(window).load(self.recheck_sizes);
		self._rerender = false;
		self._ignore_reset = false;
	};
	
	self.reset = function () {
		
		if ( !self._ignore_reset ) {
			self._cell_heights = {};
			self._rerender = false;
		}
		
		self._ignore_reset = false;
		
	}
	
	self.get_preview_height = function (view) {
		
		var cid = view.model.cid;

		if (!self._rerender) {
			var preview_height = self._get_preview_height_of_cell(view);			
			
			if (preview_height > 0) {
				self._cell_heights[cid] = preview_height;
				self._views[cid] = view;
			}
		}
		return self._cell_heights[cid];
	}
	
	self._get_preview_height_of_cell = function (view) {
		var preview_height = 0;
		
		var style = 'position: absolute !important; top: -1000 !important; ';
		var $target = jQuery(view.$el).clone().
					attr( 'style', style ).
					appendTo( 'body' );
		var main_width = view.model.get('width') * view.model.get('row_divider') * 50; //jQuery(view.$el).width();
		$target.width(main_width);
		$target.css({display: 'block'})
		$target.find('img').each(function () {
			jQuery(this).remove();
			preview_height += main_width * 3 / 4;
		});
		$target.find('.cell-content').children().each( function () {

			preview_height += jQuery(this).height();
			
		});
		$target.remove();
		
		return preview_height + 4;
	}

	self.recheck_sizes = function (event) {
		if (!jQuery.isEmptyObject(self._cell_heights)) {
			for (var key in self._views) {
				if (self._views.hasOwnProperty(key)) {
					var view = self._views[key];

					var preview_height = self._get_preview_height_of_cell(view);
					
					if (preview_height != self._cell_heights[key]) {
						console.log('Height change ' + self._cell_heights[key] + ' - ' + preview_height);

						self._cell_heights[key] = preview_height;
						self._rerender = true;
						self._ignore_reset = true;
					}
					
				}
			}
			
			if (self._rerender) {
				DDLayout.ddl_admin_page.render_all();
			}
		}
	}

	self.init();
	
};


DDLayout.preview_manager = new DDLayout.PreviewManager($);

