DDLayout.ChildLayoutManager = function( cell_view, remove_icon, delete_event )
{
	var self = this
		, remove = remove_icon;
	
	self.view = cell_view
	self.delete_event = delete_event;

	self.init = function()
	{
		if (jQuery('.js-child-layout-list').length) {
			self.open_dialog( self.view.model );	
		} else {
			self.view.eventDispatcher.trigger( self.delete_event );
		}
		
	};

	self.open_dialog = function (model) {
		
		var type = '#js-child-layout-box-';
		if (model.get('kind') == 'Row') {
			type += 'row-';
		}
		
		var template = jQuery(type + 'tpl').html()
			, tpl_data = model.toJSON()
			, remove_association
			, remove_and_delete;

		tpl_data.cid = model.cid;

		jQuery("#js-child-layout-box-container").html(_.template(template, tpl_data ));

		remove_association = jQuery('.js-delete-child-layout-and-remove-association', jQuery(type + tpl_data.cid) );
		remove_and_delete = jQuery('.js-delete-child-layout-and-delete-association', jQuery(type + tpl_data.cid) );

		jQuery.colorbox({
			href: '#js-child-layout-box-container',
			inline: true,
			open: true,
			closeButton: false,
			fixed: true,
			top: false,

			onComplete: function () {
				self.handle_remove_association_and_delete( remove_association, 'remove' );
				self.handle_remove_association_and_delete( remove_and_delete, 'delete' );
			},
			onCleanup: function () {
				remove_association.off('click');
				remove_and_delete.off('click');
			}
		});
	};

	self.handle_remove_association_and_delete = function( button, action )
	{
		button.on('click', function(e){
			if (self.view.model.get('kind') == 'Row') {
				self.view.deleteTheRow();
			} else {
				self.view.eventDispatcher.trigger( self.delete_event );
			}
			var children_data = jQuery('#js-layout-children').text();
			self.view.eventDispatcher.trigger( 'ddl-delete-child-layout-cell',  action, children_data );
			jQuery.colorbox.close();
		});
	};

	self.init( );
};