var DDLayout = DDLayout || {};

jQuery(document).ready(function($)  {

	DDLayout.NewLayoutDialog = function($)
	{
		var self = this;

		self.postTypesHandler = new DDLayout.NewLayoutDialogPostTypesHandler($);

		self.init = function() {
			$.extend($.colorbox.settings, { // override some Colorbox defaults
				transition: 'fade',
				opacity: 0.3,
				speed: 150,
				fadeOut : 0,
				onComplete: function() {

				},
				onCleanup: function() {
				}
			});

			// close dialog
			$(document).on('click','.js-new-layout-dialog-close',function(e) {
				e.preventDefault();
				$.colorbox.close();
				return false;
			});

			$('.js-dd-layout-type').on('change', function (event) {
				var width = jQuery('.js-create-new-layout').data('width');
				self._show_presets(width);
			});

			// Trigger save button click when ENTER key is pressed
			$(document).on('cbox_complete', function() {
				$(document).on('keyup.colorbox', function(e) {
					var keyCode = parseInt((e.keyCode ? e.keyCode : e.which),10);
					if ((typeof keyCode != 'undefined') && (keyCode === 13)) {
						if (!$('#cboxWrapper .js-create-new-layout').is(':disabled')) {
							$('#cboxWrapper .js-create-new-layout').trigger('click');
						}
					}
				});
			});

			// Unbind keyup.colorbox event on colorbox close
			$(document).on('cbox_cleanup', function() {
				$(document).off('keyup.colorbox');
			});

		};

		self.show_create_new_layout_dialog = function(parent_layout_id, width, parent_layout_type) {

			if (!width) {
				width = 12;
			}

			jQuery.colorbox({
				inline:true,
				closeButton:false,
				href: '.js-create-layout-form-dialog',
				open:true,
				onComplete: function() {
					// set initial states
					jQuery('.js-create-new-layout')
						.prop('disabled', true)
						.removeClass('button-primary')
						.addClass('button-secondary');
					jQuery('input.js-new-layout-title')
						.val('')
						.focus();
					jQuery('.js-error-container').empty();

					jQuery('.js-create-new-layout').data('parent_layout_id', parent_layout_id);
					jQuery('.js-create-new-layout').data('width', width);

					if (parent_layout_type && parent_layout_type == 'fluid') {
						jQuery('input[name="dd-layout-type"]').each( function () {
							if(jQuery(this).val() == 'fluid') {
								jQuery(this).prop('checked', true);
							} else {
								jQuery(this).prop('checked', false);
							}
						})
						jQuery('input[name="dd-layout-type"]').prop('disabled', true);
						jQuery('.js-diabled-fixed-rows-info').show();
					} else {
						jQuery('input[name="dd-layout-type"]').prop('disabled', false);
						jQuery('.js-diabled-fixed-rows-info').hide();
					}

					self.postTypesHandler.setInitialState( jQuery('.js-create-layout-form-dialog') );

					self._show_presets(width);


				},
				onClosed : function() {
				}
			});

		}

		self._show_presets = function (width) {
			if (jQuery('.js-dd-layout-type:checked').val() == 'fluid') {
				width = 12; // Force width of 12 for fluid layouts
			}

			var any_visible = false;
			jQuery('.js-presets-list-item').each( function () {
				if (jQuery(this).data('width') == width) {
					jQuery(this).show();
					any_visible = true;
				} else {
					jQuery(this).hide();
				}
			});

			if (any_visible) {
				jQuery('.js-preset-layouts-items').show();

				// Make sure one is selected
				var $layout_preset = $('.js-presets-list-item:visible').filter(function() {
					return $(this).data('selected');
				});
				if (!$layout_preset.length) {
					$('.js-presets-list-item:visible:first').trigger('click');
				}

			} else {
				jQuery('.js-preset-layouts-items').hide();
			}
		};

		self.init();
	};

	DDLayout.new_layout_dialog = new DDLayout.NewLayoutDialog($);


	// Change the WP menu for "Add a menu" to open the popup instead of redirecting
	var new_layout_menu_link = jQuery('a[href="admin.php?page=dd_layouts&new_layout=true"]');
	new_layout_menu_link.addClass('js-layout-add-new-top');

	// Create the new layout popup.

	if ($('.js-create-layout-form-dialog').length > 0) {
		$(document).on('click', '.js-layout-add-new-top', function(e, parent_layout_id) {
			e.preventDefault();
			DDLayout.new_layout_dialog.show_create_new_layout_dialog(parent_layout_id, null, null);
		});
	}
	
	// handle the title change in the new layout popup.

	$(document).on('change keyup input cut paste', '.js-new-layout-title', function(){
		$('.js-error-container').find('.toolset-alert').remove();
		$newLayoutButton = $('.js-create-new-layout');
		if ( $('input.js-new-layout-title').val() != '' ) {
			$newLayoutButton
				.prop('disabled', false)
				.addClass('button-primary')
				.removeClass('button-secondary');
		} else {
			$newLayoutButton
				.prop('disabled', true)
				.removeClass('button-primary')
				.addClass('button-secondary');
		}
	});

	// handle creating a new layout

	$(document).on('click', '.js-create-new-layout', function(e){
		e.preventDefault();

		var spinnerContainer = $('<div class="spinner ajax-loader">').insertAfter($(this)).show();
		var title = $('.js-new-layout-title').val();
		var layout_type = $('input[name="dd-layout-type"]:checked').val();
		var $layout_preset = $('.js-presets-list-item:visible').filter(function() {
			return $(this).data('selected');
		});
		var layout_parent = $('.js-create-new-layout').data('parent_layout_id');
		if (typeof layout_parent == 'undefined') {
			layout_parent = 0;
		}

		var columns = $('.js-create-new-layout').data('width');
		var data = {
			action: 'ddl_create_layout',
			title: title,
			layout_type: layout_type,
			layout_preset: $layout_preset.data('file'),
			layout_parent: layout_parent,
			columns: columns,
			post_types:DDLayout.new_layout_dialog.postTypesHandler.getPostTypesArray(),
			wpnonce : $('#wp_nonce_create_layout').attr('value')
		};

		$('.js-create-new-layout')
			.addClass('button-secondary')
			.removeClass('button-primary');
		$(this).prop('disabled',true);

		$.post(ajaxurl, data, function(response) {
			if ( (typeof(response) !== 'undefined') ) {
				temp_res = jQuery.parseJSON(response);

				if ( temp_res.error == 'error' ){

					show_layout_create_error_message(spinnerContainer, temp_res.error_message);
				}
				else if (typeof temp_res.id !== 'undefined' && temp_res.id !== 0) {
					var url = $('.js-layout-new-redirect').val();
					$(location).attr('href',url + temp_res.id + '&new=true');
				}
				else {
					console.log( "Error: WordPress AJAX returned ", response );
					show_layout_create_error_message(spinnerContainer, ddl_create_layout_error);
				}
			} else {
				$('<span class="updated">error</span>').insertAfter($('.js-create-new-layout')).hide().fadeIn(500).delay(1500).fadeOut(500, function(){
					$(this).remove();
				});

			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			show_layout_create_error_message(spinnerContainer, ddl_create_layout_error);
			console.log( "Error: ", textStatus, errorThrown );
		})
		.always(function() {

		});
	});

	function show_layout_create_error_message(spinnerContainer, message) {
		spinnerContainer.remove();
		$('.js-create-new-layout')
			.addClass('button-primary')
			.removeClass('button-secondary')
			.prop('disabled', false);

		$('.js-ddl-message-container').wpvToolsetMessage({
			text: message,
			type: 'error',
			stay: true
		});
	}

	$('.js-presets-list-item').on('click', function(event) {
		$('.js-presets-list-item')
			.data('selected', false)
			.removeClass('selected');

		$(this)
			.data('selected', true)
			.addClass('selected');
	});

	// add preset-container classes to the preset preview.
	$('.presets-list .row-fluid [class*="span-preset"]').each(function() {
		if ($(this).find('.row-fluid').length) {
			// it contains rows so it's a container.
			$(this).addClass('preset-container');
		}
	});

});

jQuery(window).load( function() {
	if (typeof ddl_layouts_create_new_layout_trigger != 'undefined' && ddl_layouts_create_new_layout_trigger) {
		DDLayout.new_layout_dialog.show_create_new_layout_dialog(null, null, null);
	}
})

DDLayout.NewLayoutDialogPostTypesHandler = function($)
{

	var self = this, open = false, dropdown_list = $('.ddl-post-types-dropdown-list');

	self._checked = [];

	self._checkboxes = jQuery('.js-ddl-post-type-checkbox');

	self._deselect = $('.js-dont-assign-post-type');

	self._open_close = $('.js-ddl-for-post-types-open');

	self._apply_to_all = $('.js-apply-layout-for-all-posts');

	self.init = function()
	{
		self.handle_show_hide_post_types();
		self.manage_check_box_change();
		self.manage_batch_selection();
	};

	self.handle_show_hide_post_types = function()
	{
		self._open_close.on('click', function(event){

			if( open === false )
			{

                $('i.icon-caret-down').removeClass('icon-caret-down').addClass('icon-caret-up');
				dropdown_list.slideDown(function(){
					open = true;
				});
			}
			else if( open === true )
			{
                $('i.icon-caret-up').removeClass('icon-caret-up').addClass('icon-caret-down')
				dropdown_list.slideUp(function(){
					open = false;
				});
			}
		});
	};

	self.manage_check_box_change = function()
	{
		self._checkboxes.on('change', function(event){

			if( jQuery(this).is(':checked') === true )
			{
				self._checked.push( jQuery(this).val() );
				self._deselect.prop('checked', false );
			}
			else if( jQuery(this).is(':checked') === false )
			{
				self._checked = _.without( self._checked, jQuery(this).val() );

				if( self._checked.length === 0 )
				{
					self._deselect.prop('checked', true );
				}
			}
		});
	};

	self.manage_batch_selection = function()
	{
		self._deselect.on('change', function(){
			if( $(this).is(':checked') === true )
			{
				self._checkboxes.each(function(i){
					$(this).prop('checked', false );
					self._checked = [];
				});
				open = true;
			}
			else
			{
				open = false;
			}

			self._open_close.trigger('click');
		});
	};

	self.setInitialState = function( parent )
	{
		var dialog = parent;

		dialog.find('input.js-ddl-post-type-checkbox').each(function(i){
			$(this).prop('checked', false );
			$(this).parent().siblings('span.js-alret-icon-hide-post').each(function(){
				$(this).remove();
			});
		});
	};

	self.getPostTypesArray = function()
	{
		return self._checked;
	};

	self.init();
};