var DDLayout = DDLayout || {};

DDLayout.IndividualAssignmentManager = function($)
{
    var self = this;

    self.init = function() {
        DDLayout.changeLayoutUseHelper.eventDispatcher.listenTo(DDLayout.changeLayoutUseHelper.eventDispatcher, 'change-layout-use-open', self.dialog_open_complete );
	};

	self._refresh_where_used_ui = function (include_spinner) {
		DDLayout.ddl_admin_page.initialize_where_used_ui(self._current_layout, include_spinner);
	};
	
    self.get_current_layout = function()
    {
        return self._current_layout;
    };

    self.set_current_layout = function( layout_id )
    {
        self._current_layout = layout_id;
    };

	self.dialog_open_complete = function ( dialog, data ) {

        self.set_current_layout( data );

		self._nonce = $('#wp_nonce_individual-pages-assigned').attr('value');
		
		jQuery('.js-individual-popup-tabs').tabs({
            activate:function(event, ui){
                var checkboxes = jQuery(ui.newPanel[0]).find('input[type="checkbox"]');

                checkboxes.each(function(i, v){
                    jQuery(this).prop( 'checked', false );
                });

                DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('checkboxes_changed', jQuery('.js-individual-posts-update-wrap'), "individual_posts_assign", 0 );
            }
        });
        jQuery('.js-individual-popup-tabs').tabs( 'option', 'active', 0 ); // Activate the first tab
		
		self._get_posts_for_layout();
		
		$('.js-connect-to-layout').off('click');
		$('.js-connect-to-layout').on('click', self._handle_content_to_layout);
		$('.js-connect-to-layout').prop('disabled', true);

		self._initialize_checkbox_handling();
		
		self._fill_view_all_tab('page');
		self._initialize_quick_search();
		
		$('#ddl-individual-post-type-page').prop('checked', true);
		
		$('#ddl-individual-post-type-page,#ddl-individual-post-type-any').off('click');
		$('#ddl-individual-post-type-page,#ddl-individual-post-type-any').on('click', self._handle_post_type_change);
	}
	
	self._initialize_checkbox_handling = function () {
		$('.js-ddl-individual-posts').off('change');
		$('.js-ddl-individual-posts').on('change', self._handle_post_checkbox_click);
	};


    self._handle_post_checkbox_click = function (event) {
        DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('checkboxes_changed', jQuery('.js-individual-posts-update-wrap'), jQuery(event.target).prop('name'), $('.js-ddl-individual-posts:checked').length );
	};
	
	self._get_posts_for_layout = function () {
		var current_pages = $('.js-individual-pages-assigned');
		current_pages.html('');
		current_pages.append( self._get_spinner_code() ).show();
		
        var data = {
            action : 'ddl_fetch_post_for_layout',
            wpnonce : self._nonce,
			layout_id : self._current_layout
        };

        $.post(ajaxurl, data, function(result) {

			result = jQuery.parseJSON(result);
			current_pages.html(result['posts']);

			$('.js-remove-individual-page').on('click', self._handle_remove);
		});
	};
	
	self._handle_remove = function () {
		var button = $(this);
		var list_item = $(this).parent();
		list_item.fadeOut(300, function() {
			var data = {
				action : 'ddl_remove_layout_from_post',
				wpnonce : self._nonce,
				post_id : button.data('id'),
                layout_id:self.get_current_layout()
			};



            if( DDLayout_settings && DDLayout_settings.DDL_JS && DDLayout_settings.DDL_JS.is_listing_page === true )
            {
                data.in_listing_page = 'yes';
                DDLayout.listing_manager.listing_table_view.model.trigger('make_ajax_call',  data, function( model, result, object, args ){
                    DDLayout.listing_manager.listing_table_view.current = +data.layout_id;
                    list_item.remove();
                });
            }
            else
            {
                $.post(ajaxurl, data, function(result) {
                    list_item.remove();
					self._refresh_where_used_ui(false);

                });
            }
		});
	}
	
	self._handle_content_to_layout = function () {
		
		$('.js-connect-to-layout').prop('disabled', true);
		$('.js-connect-to-layout').addClass('button-secondary').removeClass('button-primary');

		var posts = Array();
		$('.js-ddl-individual-posts:checked').each( function () {
			var post_id = $(this).val();
			if (self._add_assigned_post(post_id, $(this).data('title'))) {
				posts.push(post_id);
			}
			$(this).prop('checked', false);
		})
		
		$('.js-individual-pages-assigned ul li').fadeIn(500);
		
		$('.js-remove-individual-page').off('click');
		$('.js-remove-individual-page').on('click', self._handle_remove);

        var data = {
            action : 'ddl_assign_layout_to_posts',
            wpnonce : self._nonce,
			layout_id : self._current_layout,
			posts : posts
        };
		
		var spinner = $(self._get_spinner_code());
		spinner.insertBefore('.js-connect-to-layout');

        if( DDLayout_settings && DDLayout_settings.DDL_JS && DDLayout_settings.DDL_JS.is_listing_page === true )
        {
            data.in_listing_page = 'yes';

            DDLayout.listing_manager.listing_table_view.model.trigger('make_ajax_call',  data, function( model, result, object, args ){
                DDLayout.listing_manager.listing_table_view.current = +data.layout_id;
				spinner.remove();
                DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('data_sent_to_server', DDLayout_settings.DDL_JS.INDIVIDUAL_POSTS_OPTION);
            });
        }
        else
        {
            $.post(ajaxurl, data, function(result) {
				self._refresh_where_used_ui(false);
				spinner.remove();
                DDLayout.changeLayoutUseHelper.eventDispatcher.trigger('data_sent_to_server', DDLayout_settings.DDL_JS.INDIVIDUAL_POSTS_OPTION);
            });
        }
	}
	
	self._add_assigned_post = function (post_id, post_title) {
		var list = $('.js-individual-pages-assigned ul');
		var found = false;
		list.find('.js-remove-individual-page').each( function () {
			if ($(this).data('id') == post_id) {
				found = true;
			}
		})
		if (!found) {
			list.append('<li style="display:none"><i class="icon-remove js-remove-individual-page" data-id="' + post_id + '"></i> ' + post_title + '</li>');
		}
		
		return !found;
	}
	
	self._fill_view_all_tab = function (post_type) {
		$('[id^=js-ddl-individual-view-all]').html(self._get_spinner_code());
		
        var data = {
            action : 'ddl_get_individual_post_checkboxes',
            wpnonce : self._nonce,
			post_type : post_type,
			sort : false,
			count : -1
        };
        $.post(ajaxurl, data, function(result) {
			$('[id^=js-ddl-individual-view-all]').html(result);
			self._initialize_checkbox_handling();
		});
	}

	self._fill_most_recent_tab = function (post_type) {
		$('[id^=js-ddl-individual-most-recent]').html(self._get_spinner_code());
		
        var data = {
            action : 'ddl_get_individual_post_checkboxes',
            wpnonce : self._nonce,
			post_type : post_type,
			count : 12
        };
        $.post(ajaxurl, data, function(result) {
			$('[id^=js-ddl-individual-most-recent]').html(result);
			self._initialize_checkbox_handling();
		});
	}
	
	self._get_spinner_code = function () {
		return '<div class="spinner ajax-loader" style="float:none; display:inline-block"></div>';
	}

	self._initialize_quick_search = function() {
		self._search_depth = 0;
		self._search_spinner = null;
		
		self._searchTimer = null;
		
		$('.js-individual-quick-search').keyup(self._handle_search_change).attr('autocomplete','off');
	}
	
	self._handle_search_change = function(e) {
		var t = $(this);

		if( 13 == e.keyCode ) {
			self._update_quick_search_results( t );
			return false;
		}

		if( self._searchTimer ) clearTimeout(self._searchTimer);

		self._searchTimer = setTimeout(function(){
			self._update_quick_search_results( t );
		}, 400);
	}
	
	self._update_quick_search_results = function (text) {
		text = text.val();
		
		if (text) {
			var post_type = $('[name="ddl-individual-post-type"]:checked').val();
			
			if (!self._search_spinner) {
				self._search_spinner = $(self._get_spinner_code()).insertAfter('.js-individual-quick-search');
			}
			
			$('[id^=ddl-individual-search-results]').html('');
			
			self._search_depth++;
			
			var data = {
				action : 'ddl_get_individual_post_checkboxes',
				wpnonce : self._nonce,
				post_type : post_type,
				search : text,
				count : -1
			};
			$.post(ajaxurl, data, function(result) {
				$('[id^=ddl-individual-search-results]').html(result);
				self._initialize_checkbox_handling();
			
				self._search_depth--;
				
				if (self._search_depth == 0) {
					self._search_spinner.remove();
					self._search_spinner = null;
				}
			});
		} else {
			$('[id^=ddl-individual-search-results]').html('');
		}
		
	};
	
	self._handle_post_type_change = function () {
		self._fill_most_recent_tab($(this).val());
		self._fill_view_all_tab($(this).val());
		self._update_quick_search_results($('.js-individual-quick-search'));
	}
	
	self.init();
};


jQuery(document).ready(function($){
    DDLayout.individual_assignment_manager = new DDLayout.IndividualAssignmentManager($);
});
