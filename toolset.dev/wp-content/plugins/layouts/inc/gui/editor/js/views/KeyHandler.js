// KeyHandler.js

DDLayout.KeyHandler = function($)
{
    var self = this;

    self.init = function()
    {
        jQuery(document).on( 'keydown', self.registerKeyPress );
    };
    
	self.registerKeyPress = function (event) {
        // don't handle key press if we have a popup.
        if (!jQuery('#cboxOverlay').is(':visible')) {
            event.stopImmediatePropagation();
            self.handle_key_press(event, event.which, event.keyCode);
        }
    };
    self.handle_key_press = function (event, key, keyCode) {
		//event.preventDefault();
        
        switch(key) {
            case 90: // Ctrl-Z
                if (event.ctrlKey) {
                    event.preventDefault();
                    DDLayout.ddl_admin_page.do_undo();
                }
                break;

            case 89: // Ctrl-Y
                if (event.ctrlKey) {
                    //to avoid problems in Chrome since Ctrl-y is its shortcut for History
                    event.preventDefault();
                    DDLayout.ddl_admin_page.do_redo();
                }
                break;
            
        }
        
        switch(keyCode) {
            case 37: // Left arrow
                DDLayout.ddl_admin_page.move_selected_cell_left(event);
                break;
            
            case 39: // Right arrow
                DDLayout.ddl_admin_page.move_selected_cell_right(event);
                break;
            
            case 46: // Delete key
                DDLayout.ddl_admin_page.delete_selected_cell(event);
                break;
        }
    };	
    
	self.destroy = function()
	{
		jQuery(document).off( 'keydown', self.registerKeyPress );
	}

    self.init();
};