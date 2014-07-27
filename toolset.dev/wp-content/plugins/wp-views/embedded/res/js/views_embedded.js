var filter_html_embedded,
layout_html_embedded,
combined_output_embedded;

jQuery( document ).ready( function() {
	var view_purpose = jQuery( '.js-wpv-view-purpose' ).val();
	jQuery( '.toolset-help.js-for-view-purpose-' + view_purpose ).show();
	
	if ( jQuery( '#wpv_filter_meta_html_content' ).length ) {
		filter_html_embedded = CodeMirror.fromTextArea(document.getElementById( "wpv_filter_meta_html_content" ), {
			mode: "myshortcodes",
			lineNumbers: true,
			lineWrapping: true,
			//viewportMargin: Infinity
			readOnly: "nocursor"
		});
	}
	if ( jQuery( '#wpv_layout_meta_html_content' ).length ) {
		layout_html_embedded = CodeMirror.fromTextArea(document.getElementById( "wpv_layout_meta_html_content" ), {
			mode: "myshortcodes",
			lineNumbers: true,
			lineWrapping: true,
			//viewportMargin: Infinity
			readOnly: "nocursor"
		});
	}
	if ( jQuery( '#wpv_content' ).length ) {
		combined_output_embedded = CodeMirror.fromTextArea(document.getElementById( "wpv_content" ), {
			mode: "myshortcodes",
			lineNumbers: true,
			lineWrapping: true,
			//viewportMargin: Infinity
			readOnly: "nocursor"
		});
	}
	
	if ( jQuery( '.js-wpv-display-in-iframe' ).length == 1 ) {
		if ( jQuery( '.js-wpv-display-in-iframe' ).val() == 'yes' ) {
			jQuery( '.toolset-help a, .wpv-setting a' ).attr("target","_blank");
		}
	}
});

// wp-pointers

jQuery('.js-display-tooltip').click(function(){
	var $thiz = jQuery(this);

	// hide this pointer if other pointer is opened.
	jQuery('.wp-pointer').fadeOut(100);

	jQuery(this).pointer({
		content: '<h3>'+$thiz.data('header')+'</h3><p>'+$thiz.data('content')+'</p>',
		position: {
			edge: 'left',
			align: 'center',
			offset: '15 0'
		}
	}).pointer('open');
});
/*
jQuery('.CodeMirror').css({'border':'1px solid #eee', 'height':'auto'});
jQuery('.CodeMirror-scroll').css({'overflow-y':'hidden', 'overflow-x':'auto'});
*/