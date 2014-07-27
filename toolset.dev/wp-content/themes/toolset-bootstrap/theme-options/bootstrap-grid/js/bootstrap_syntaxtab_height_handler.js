jQuery(document).ready(function($) {

	/*Emerson: WordPress Core JS (as of WP 3.8.1) does not have filters to allow us to control the Code Mirror text height at Syntax editor tab
	 * By default, it is using the WordPress built-in content-resize-handle div
	/* We still use this handle by writing our own JS here. 
	 * From this todo: https://icanlocalize.basecamphq.com/projects/11629195-toolset-peripheral-work/todo_items/178014092/comments
	 */
	
	function wpbootstrap_resizable_text_height(textarea) {
		
		// No point for touch devices
		if ( !textarea.length || 'ontouchstart' in window )
			return;

		function dragging(e) {
			textarea.height( Math.max(50, offset + e.pageY) + 'px' );
			return false;
		}

		function endDrag() {
			var height;

			textarea.focus();
			$(document).unbind('mousemove', dragging).unbind('mouseup', endDrag);

			height = parseInt( textarea.css('height'), 10 );

			// sanity check
			if ( height && height > 50 && height < 5000 )
				setUserSetting( 'ed_size', height );
		}

		textarea.css('resize', 'none');
		el = $('<div id="content-resize-handle"><br></div>');
		$('#wp-content-wrap').append(el);
		el.on('mousedown', function(e) {
			offset = textarea.height() - e.pageY;
			textarea.blur();
			$(document).mousemove(dragging).mouseup(endDrag);
			return false;
		});
		
	}	
	(function() {
		
		$('.content-bootstrap').click(function() {			
		
		var textarea = $('.CodeMirror'), offset = null, el;

		wpbootstrap_resizable_text_height(textarea);
		
	  });
		
		$("a.wp-switch-editor:first").click(function() {		
			
			var textarea = $('textarea#content'), offset = null, el;
			wpbootstrap_resizable_text_height(textarea);			

		  });		
		
	})();
	
	if	(bootstrap_config_object.editor_active_tab==2) {
		
		var textarea = $('.CodeMirror'), offset = null, el;

		wpbootstrap_resizable_text_height(textarea);		
		
		
	}

});