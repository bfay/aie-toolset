jQuery(function($) {

	// call chose box type dialog
	$.extend($.colorbox.settings, { // override some Colorbox defaults
		transition: 'fade',
		opacity: 0.3,
		speed: 150,
		fadeOut : 0,
		inline : true,
		fixed: true,
		top: '50px',
		trapFocus: false // Required for select2 compatibility
	});

	$.extend($.fn.select2.defaults, { // override Select2 defaults
		'width': 250
		,selectOnBlur:true
//		dropdownAutoWidth: true
	});

	$(document).on('cbox_complete', function(event) {

		if( DDLayout.ddl_admin_page !== undefined )
		DDLayout.ddl_admin_page.is_colorbox_opened = true;


		$(document).trigger('ddl-editor-dialog-complete');

		
		$('#cboxWrapper .js-select2').select2({
			'width': 'resolve'
		});

		// Fix for Select2 and Colorbox incopatibility issue
		$(document).on('mousedown.colorbox','#cboxLoadedContent, #cboxOverlay', function(e){
			if ( $(e.target).parents('.js-select2').length === 0 ) {
				$('select.js-select2').select2('close');
			}
			if( $(e.target).parents('.js-select2-tokenizer').length === 0 )
			{
				$('input.js-select2-tokenizer').select2('close');
			}
		});

	});

	$(document).on('cbox_cleanup', function() {

		// Unbind keyup.colorbox event on colorbox close
		$(document).off('keyup.colorbox');

		// Unbind select2 workaround
		$(document).off('mousedown.colorbox');

		// Destroy select2 obj
		$('.js-select2').select2('destroy');
		$('.js-select2-tokenizer').select2('destroy');

	});

	$(document).on('cbox_closed', function(event) {
		if( DDLayout.ddl_admin_page !== undefined )
		DDLayout.ddl_admin_page.is_colorbox_opened = false;
		$(this).trigger('color_box_closes', event);
        WPV_Toolset.Utils.eventDispatcher.trigger('color_box_closed', event, this );
	});


	$(document).on('color_box_closes', function(event){
		// make sure there is only one editable target at a time in the editor, whatever kind it is
		jQuery('#ddl-row-edit').data('row_view', undefined);
		jQuery('#ddl-default-edit').data('cell_view', undefined);
		jQuery('#ddl-container-edit').data('container_view', undefined);
		jQuery('#ddl-theme-section-row-edit').data('row_view',  undefined)
	});


	jQuery(document).on('click', '.js-edit-dialog-close', function(event){
		event.preventDefault();
		event.stopImmediatePropagation();
		$.colorbox.close();

		return false;
	});

	jQuery(document).on('tabsactivate', function(event, ui){
		jQuery( event.target ).trigger( 'activate_tab', {
			tabIndex: ui.newTab.index()
		});
	});

	jQuery(document).on('tabsbeforeactivate', function(event, ui){
		jQuery( event.target ).trigger( 'before-activate_tab', {
			tabIndex: ui.newTab.index(),
			cssClassEl: jQuery('input.js-edit-css-class', event.target),
			cssIdEl: jQuery('input.js-edit-css-id', event.target),
			textArea: jQuery('.js-ddl-css-editor-area', event.target)
		});
	});
});