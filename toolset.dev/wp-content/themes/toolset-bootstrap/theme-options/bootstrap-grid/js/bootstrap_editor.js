jQuery(function($){
    if($('#post-body-content').length) {

    	/*EMERSON
    	You are viewing a WP editor page, such as page edit, post edit, post type edit or adding new content on pages/posts/post types.
    	Even any Toolset plugins edit pages.This code will run on any page load event.
        */

        if(bootstrap_config_object.syntax_highlighter==true && typeof bootstrap_config_object.syntax_highlighter !== 'undefined') {

        	 //This is not a CRED or View edit page and Syntax highlighter is set to TRUE or not set to undefined"

            if((bootstrap_config_object.post_type!=='cred-form') && (!$('#views_template_html_extra').length)){

            	/*
            	If post type is not a CRED Form edit page, it will add the "Syntax" highlighter button in addition to
            	Visual and Text options in the editor*/
                
            	//Compatibility of inserting the syntax tab in WP3.8
            	if(bootstrap_config_object.wordpress_version_three_eight_above==1){
            		
            		//Compatibility with Types WYSIWYG custom fields editor
            		//Add syntax tab to each editor tabs added by these custom fields
            		var wysiyg_types_object=$(".wpcf-wysiwyg textarea"); 
            		var the_wysiwyg_text_areas=types_wysiwyg_text_area_object.get_all_text_areas(wysiyg_types_object);
            		
            		//Append syntax tab to WYSIWYG text areas, define additional parameters            		
            		var selector_before='#wp-';
            		var selector_after='-wrap .wp-editor-tabs';
            		var html_before='<a onclick="switchEditors.switchto(this);" id="';
            		var html_after= '" class="wp-switch-editor switch-html content-bootstrap" rel="popover">Syntax</a>';   				
            		var insertion_mode='append'
            		var unique_identifier='-content-html';             		
            		
            		//Call the method
            		types_wysiwyg_text_area_object.append_prepend_to_relevant_areas(
            				the_wysiwyg_text_areas,	    											
							selector_before,
							selector_after,
							html_before,
							html_after,
							insertion_mode,
							unique_identifier);
 
            		//Add syntax tab to main editor
            		$('#wp-content-wrap .wp-editor-tabs').append('<a onclick="switchEditors.switchto(this);" id="content-html" class="wp-switch-editor switch-html content-bootstrap" rel="popover">Syntax</a>');
            		
            	} else {
            		
            		$('<a onclick="switchEditors.switchto(this);" id="content-html" class="wp-switch-editor switch-html content-bootstrap" rel="popover">Syntax</a>').insertBefore('#wp-content-media-buttons');           		
            	}
            }

            //If bootstrap_config_object.tmce_editor_status is set to 1, show "Visual" button option in editor

            bootstrap_object.disable_tmce_editor(bootstrap_config_object.tmce_editor_status);

            //First time users of Toolset bootstrap theme will see a pop up in the editor "Syntax highlight editing mode" introducing this feature.

            if(bootstrap_config_object.first_time_editor==1){
                bootstrap_object.msg_popup('.content-bootstrap', translations.syntax_notification_title, translations.grid_dialog_content, 'left');
            }

            /*
            If a user clicks the help or question mark icon on Toolset Bootstrap meta box "Disable Visual Editor" on affected edit pages,
            Show the popup message when clicked.
            */

            if($('#syntax-mode-configuration').length){
                $('.help-holder .ico-questionmark').on("click", function(){
                    bootstrap_object.msg_popup(this, translations.syntax_dialog_title, translations.syntax_dialog_content, 'right');
                });
            }

            //Insert "Add Media" button in the editor when user is in the Syntax highlighting mode and using updated WP version.

            if(bootstrap_config_object.wp_old_version==0){
            	
            	var retrieved_wysiyg_types_object=$(".wpcf-wysiwyg textarea");
            	var retrieved_wysiwyg_text_areas=types_wysiwyg_text_area_object.get_all_text_areas(retrieved_wysiyg_types_object);
        		var selector_before='#wp-';
        		var selector_after='-wrap .wp-media-buttons';
        		var html_before='<a id="';
        		var html_after= '" title="Add Media" data-editor="bootstrap_text" class="element-hide button add_media codemirror_editor_media_button" href="#"><span class="wp-media-buttons-icon"></span> '+translations.add_media_button_title+'</a>';   				
        		var insertion_mode='prepend'
        		var unique_identifier='-codemirror-insert-media';             		
        		
        		//Call the method
        		types_wysiwyg_text_area_object.append_prepend_to_relevant_areas(
        				retrieved_wysiwyg_text_areas,	    											
						selector_before,
						selector_after,
						html_before,
						html_after,
						insertion_mode,
						unique_identifier);
        		
        		//Add media to main editor
            	$('#wp-content-wrap #wp-content-media-buttons').prepend('<a id="codemirror-insert-media" title="Add Media" data-editor="bootstrap_text" class="element-hide button add_media codemirror_editor_media_button" href="#"><span class="wp-media-buttons-icon"></span> '+translations.add_media_button_title+'</a>');
            }

            // add textarea if icl_editor-script not enqueue
            if(bootstrap_config_object.codemirror_status==1){

                $('#wp-content-editor-container').append('<div id="wrap-code-bootstrap" class="element-hide">'+
                    '<textarea id="bootstrap_text" name="code-bootstrap-text"></textarea>'+
                    '</div>');
            }

            //Add the complete grid popup where user can select grid layout options in the editor.

            $('#wp-content-editor-container').append('\
                    <div id="wraper-boot-select-structures-popup">'+
                    '<div class="boot-popup-content">'+
                        '<div class="grid-select-header">'+
                            '<span class="ico-close"></span>'+
                            '<p>'+translations.select_grid_columns_includes.replace("%s",bootstrap_config_object.available_columns)+'</p>'+
                        '<div class="select-grid-type">'+
                            '<p><label><input type="radio" name="grid_size" class="select-grid-type-row-fluid" value="row-fluid" checked="checked"> '+translations.select_grid_row_fluid+'</label></p>'+
                            '<p><label><input type="radio" name="grid_size" class="select-grid-type-row" value="row"> '+translations.select_grid_row+'</label></p>'+
                            '<p class="show-grids-columns-holder element-hide"><label><input type="checkbox" value="'+bootstrap_config_object.available_columns+'" id="show-grids-columns" name="show_grids_columns"> '+translations.select_grid_columns_checkbox.replace("%s",bootstrap_config_object.available_columns)+'</label></p>'+
                        '</div></div>'+ /*I don't know why this closing tag is here, but without it page header breaks. */
                        '<ul class="boot-grid-list">'+bootstrap_grid_object.render_grid()+'</ul>'+
                        '</div>'+
                        '</div>'+
                    '</div>');

            //Options for selecting the column sizes based on set template

            var available_columns_size=0;
            if($('#page_template').length) {

                if($('#page_template').val() == "page-fullwidth.php"){

                    available_columns_size=12;
                }else{

                    available_columns_size=bootstrap_config_object.available_columns;
                }
                $('.grid-select-header > p span, .show-grids-columns-holder > label span').text(available_columns_size);
                $(this).change(function() {

                    if($('#page_template').val() == "page-fullwidth.php"){

                        available_columns_size=12;
                    }else{

                        available_columns_size=bootstrap_config_object.available_columns;
                    }
                    $('.grid-select-header > p span, .show-grids-columns-holder > label span').text(available_columns_size);
                });
            }else{

                available_columns_size=bootstrap_config_object.available_columns;
            }

            // hide bad grid items set option
            if(bootstrap_config_object.selected_columns_show==1){

                $('#show-grids-columns').attr('checked','checked');
                bootstrap_object.show_grid_for_columns();
            }else{

                $('#show-grids-columns').removeAttr('checked');
            }

            // select gtid type row/row-fluid
            if(bootstrap_config_object.selected_grid_type==0){

                $('.select-grid-type-row').attr('checked','checked');
                $('.show-grids-columns-holder').removeClass('element-hide');
            }else{

                $('.select-grid-type-row-fluid').attr('checked','checked');
            }

            // filter grid list
            $('.select-grid-type-row').on("click", function(){

                $('.show-grids-columns-holder').removeClass('element-hide');
                if($('#show-grids-columns').is(':checked')){

                    bootstrap_object.show_grid_for_columns();
                }else{

                    $('.boot-grid-list li').removeClass('element-hide');
                }
            });

            // show all grid items
            $('.select-grid-type-row-fluid').on("click", function(){

                $('.show-grids-columns-holder').addClass('element-hide');
                $('.boot-grid-list li').removeClass('element-hide');
            });

            // show/hide grid list
            $(document).on('click','#show-grids-columns', function() {

                if($(this).is(':checked')){

                    bootstrap_object.show_grid_for_columns();
                }else{

                    $('.boot-grid-list li').removeClass('element-hide');
                }
            });

            $('#bootstrap-grid-popup,.bootstrap-grid-popup').on("click", function(e){
                e.preventDefault();
                // show grid popup

                //Increase compatibility of inserting plugin shortcodes with Bootstrap theme
            	//By avoiding conflict with those using colorbox method

                var $bootSelectPopup = $('#wraper-boot-select-structures-popup');
                $bootSelectPopup.show();

                $.colorbox({
                    opacity: 0.3,
                    transition: 'fade',
                    speed: 150,
                    fadeOut : 0,
                    inline: true,
                    closeButton: false,
                    onClosed: function() {
                        $bootSelectPopup.hide();

                        //Assign focus to Bootstrap grid at Text editor
                        var activated_content_editor=$('#'+window.wpActiveEditor);
                        var tinymce_enabled_editing=icl_editor.isTinyMce(activated_content_editor);
                        var codemirror_enabled_editing=icl_editor.isCodeMirror(activated_content_editor);
                        if ((!(tinymce_enabled_editing)) && (!(codemirror_enabled_editing))) {
                        	var focustextarea_active="#"+window.wpActiveEditor;
                            var html_grid_content_inserted = $(focustextarea_active).val();
                            $(focustextarea_active).focus().val("").val(html_grid_content_inserted);
                        }
                    },
                    href: $bootSelectPopup
                });
            });

            $('.grid-select-header .ico-close').on('click',function(){
                $.colorbox.close();
            });

            // codemirror insert media
            var custom_uploader;
            $('#codemirror-insert-media,.codemirror_editor_media_button').on("click", function(e) {

                e.preventDefault();
                //If the uploader object has already been created, reopen the dialog
                if (custom_uploader) {

                    custom_uploader.open();
                    return;
                }
                //Extend the wp.media object
                custom_uploader = wp.media.frames.file_frame = wp.media({//translations.grid_dialog_title
                    frame      : 'post',
                    state: 'insert',
                    title: translations.insert_media_title,
                    button: {
                        text: translations.insert_media_title
                    },
                    library     : {
                        type : 'image'
                    },
                    multiple    : false

                });

                //When a file is selected wp.media Alignment
                custom_uploader.on('insert', function() {

                    attachment = custom_uploader.state().get('selection').first().toJSON();
                    link_to ='';
                    if($('.link-to').val()=='none'){

                        link_to='#';
                    }else{

                        link_to=$('.link-to-custom').val();
                    }
                    media='<a href="'+link_to+'"><img src="'+attachment.sizes[$('.size').val()].url+'" alt="'+attachment.alt+'" width="'+attachment.sizes[$('.size').val()].width+'" height="'+attachment.sizes[$('.size').val()].height+'" class="align'+$('.alignment').val()+' size-'+$('.size').val()+' wp-image-'+attachment.id+'" /></a>';
                    if(attachment.caption!==''){

                        media='[caption id="attachment_'+attachment.id+'" align="align'+$('.alignment').val()+'" width="'+attachment.sizes[$('.size').val()].width+'"]'+media+' '+attachment.caption+'[/caption]';
                    }
                    //Insert media using icl_editor
                    icl_editor.insert(media);
                });
                //Open the uploader dialog
                custom_uploader.open();

            });


            if(bootstrap_config_object.post_type!=='cred-form'){

                //This will do the actual insertion of grid layout as selected by the user in the editor
                $('.boot-grid-list > li').on("click", function(){

                    var html_grid=bootstrap_grid_object.insert_grid($(this).attr('rel'), $('input[name=grid_size]:checked').val(), available_columns_size);

           
                    var areacheck_codemirror_texteditor=jQuery('#'+window.wpActiveEditor);                     
                    icl_editor.InsertAtCursor( areacheck_codemirror_texteditor, html_grid);

                    $.colorbox.close();

                });

            }else{
                //Insert grid in editor cred
                $('.boot-grid-list > li').on("click", function(){

                    var html_grid=bootstrap_grid_object.insert_grid($(this).attr('rel'), $('input[name=grid_size]:checked').val(), available_columns_size);
                    cred_cred.app.insert(html_grid);
                    $.colorbox.close();
                });
            }

            //WP does not provide a filter to change the onclick attribute value on switching tabs at editor.
            //Example this one: onclick="switchEditors.switchto(this);"

            //So let's do it using jQuery and manually override tab switching CSS
            $('.switch-tmce').attr('onclick', '');
            $('a.wp-switch-editor:first').attr('onclick', '');
            $('.content-bootstrap').attr('onclick', '');

            $('.switch-tmce, .wpcf-wysiwyg .switch-tmce').click(function() {                
            	//Visual editor is activated                
                
                //Get the text area object
                var objecttext_area=$(this);                
                var editor_mode='visualmode';
                var editor_wrap_id_array_data=tb_helper_object.return_tab_switching_text_areas_array(objecttext_area,editor_mode);
                tb_helper_object.return_proper_tab_styling_on_switch(editor_wrap_id_array_data,editor_mode);

            	//Set active tab to TinyMCE            	
            	$('#editor-active-tab').val('tmce');
            	
           });
            
            $("a.switch-html:not(.content-bootstrap),.wpcf-wysiwyg a.switch-html:not(.content-bootstrap)").click(function() {               
            	//HTML text editor is activated

                 //Get the text area object
                var objecttext_area=$(this);
                
                var editor_mode='textmode';
                var editor_wrap_id_array_data=tb_helper_object.return_tab_switching_text_areas_array(objecttext_area,editor_mode);                
                tb_helper_object.return_proper_tab_styling_on_switch(editor_wrap_id_array_data,editor_mode);

            	//Set active tab to text editor
            	$('#editor-active-tab').val('html');

            });

            //Code mirror checking on page load            
            if	(!(bootstrap_config_object.editor_active_tab==2)) {                

    	    	var editor_wrap_id_array_data_loaded_non_syntax=[]
    	    	tb_helper_object.return_proper_tab_styling_on_switch(editor_wrap_id_array_data_loaded_non_syntax,'non_syntax_pageload_mode')

            } else {

                //Page load with codemirror enabled            	
            	window.tinyMCE=false;            	
            	
        		var wysiyg_types_object=$(".wpcf-wysiwyg textarea"); 
        		
        		//Retrieved all text areas
        		/*Toggle code mirror if needed*/
        		if( (typeof wysiyg_types_object == "object") && (wysiyg_types_object !== null) )
        		{
            		for (var key in wysiyg_types_object) {
          			  if (wysiyg_types_object.hasOwnProperty(key)) {              			    

                    	  var text_area_objects=wysiyg_types_object[key];
                    	  if ($(text_area_objects).attr('id')) {
                    		  //Text areas
                    		  var text_area_id_check=$(text_area_objects).attr('id');                   		  

                    		  var codemirror_field_test=icl_editor.isCodeMirror(text_area_id_check);
                    		  
                    		  if (!(codemirror_field_test)) {
                    			  icl_editor.toggleCodeMirror(text_area_id_check, true);
                    		  }

                    	  }
          			  }
          			}
        		}
        		
            	//Main editor enabling
            	icl_editor.toggleCodeMirror('content', true);
            	var editor_mode_loaded='syntaxmode';
            	
        		if( (typeof the_wysiwyg_text_areas == "object") && (the_wysiwyg_text_areas !== null) )
        		{        			
            		for (var key in the_wysiwyg_text_areas) {
          			  if (the_wysiwyg_text_areas.hasOwnProperty(key)) {              			    

                    	  var text_area_objects_loaded=the_wysiwyg_text_areas[key];
                    	  var editor_wrap_id_array_loaded=tb_helper_object.return_editor_wrap_id(text_area_objects_loaded);    
                          tb_helper_object.return_proper_tab_styling_on_switch(editor_wrap_id_array_loaded,editor_mode_loaded); 
          			  }
          			}
        		}       		          			

        		//Main editor
        		var editor_wrap_id_array_loaded_main=tb_helper_object.return_editor_wrap_id('content');
        		tb_helper_object.return_proper_tab_styling_on_switch(editor_wrap_id_array_loaded_main,editor_mode_loaded); 

            	//Set active tab to syntax
            	$('#editor-active-tab').val('syntax');
            }

            $('.content-bootstrap,.wpcf-wysiwyg .content-bootstrap').click(function() {             
            	//Syntax mode editor is activated
                //Get the text area object
                var objecttext_area=$(this);
                var editor_mode='syntaxmode';
                var editor_wrap_id_array_data=tb_helper_object.return_tab_switching_text_areas_array(objecttext_area,editor_mode);       
                tb_helper_object.return_proper_tab_styling_on_switch(editor_wrap_id_array_data,editor_mode);           

            	//Set active tab to syntax
            	$('#editor-active-tab').val('syntax');


           });

        }
    }

    // correct grid item size
    $('ul > li > div.row-fluid > .holder').css('height', ($('ul > li > div.row-fluid > .holder').height()+3));

});

//Definition of bootstrap_object
var bootstrap_object = {

    show_grid_for_columns: function() {// show grid only for n-columns
        Array.prototype.each = function (handler) {
            for (var i = 0; i < this.length; i++) {
                handler.call(this[i], i, this[i]);

            }
            return this;
        };
        jQuery('.boot-grid-list > li').each(function() {

            var grid_cols = jQuery('#show-grids-columns').val();
            var design_cols_mass_full = jQuery(this).attr('class').split(/\s+/);
            var design_cols_mass = [];
            for (var i = 0; i < design_cols_mass_full.length; i++) {

                if ( i in design_cols_mass_full ) {

                    design_cols_mass.push(design_cols_mass_full[i]);
                }
            }
            if(design_cols_mass.length==1){
                var design_cols = design_cols_mass[0].replace('grid-columns-','');
                if(grid_cols % design_cols != 0){
                    jQuery(this).addClass('element-hide');
                }
            }else{
                design_cols_mass.each(function (i) {
                    if (!jQuery(this).hasClass('element-hide')) {
                        var design_cols = design_cols_mass[i].replace('grid-columns-','');
                        // only allow designs that fit exactly with no remainder
                        if(grid_cols % design_cols != 0){

                            jQuery(this).addClass('element-hide');
                        }
                    }
                });
            }
        });
    },

    span_size: function(grid_size, xml_value, available_columns, parent_size){// convert row-fluid to row

        if(typeof(parent_size)==='undefined') parent_size = 0;

        if(grid_size=='row'){
            var size=0;
            if(parent_size==0){
                size=Math.floor(xml_value*(available_columns/12));
            }else{
                cof=Math.floor(parent_size*(available_columns/12))/12;
                if(xml_value % 2 == 0){
                    size=Math.floor(xml_value*cof);
                }else{
                    switch(xml_value)
                    {
                        case 1:
                            new_xml_value=2;
                            break;
                        case 3:
                            new_xml_value=4;
                            break;
                        case 5:
                            new_xml_value=6;
                            break;
                        case 7:
                            new_xml_value=6;
                            break;
                        case 9:
                            new_xml_value=8;
                            break;
                        case 11:
                            new_xml_value=10;
                            break;
                    }
                    size=Math.floor(new_xml_value*cof);
                }
            }
            return size;
        }else{
            return xml_value;
        }
    },

    disable_tmce_editor: function(status) {//Disable standard tmce editor
        if(status==0){
            document.getElementById("content-tmce").onclick = 'none';
            jQuery('#content-tmce').remove();
        }
    },

    msg_popup: function(selector, title, content, position_edge) {// create new popup
        var tip_options = {
            "content":"<h3>"+title+"<\/h3><p>"+content.replace("%s",window.location.hostname)+"<\/p>",
            "position":{
                "edge":position_edge,
                "align":"center"
            }
        };
        tip_options = jQuery.extend( tip_options, {
            close: function() {}
        });
        return jQuery(selector).pointer( tip_options ).pointer("open");
    }
};

//Definition of Types types_wysiwyg_text_area_object that might need syntax editor implementation
var types_wysiwyg_text_area_object = {
		
	    get_all_text_areas: function(wysiyg_types_object) {
	    	if( (typeof wysiyg_types_object == "object") && (wysiyg_types_object !== null) )
	    	{	    		
	    		var get_all_text_areas_array = new Object();
	    		
	    		for (var key in wysiyg_types_object) {
	    			  if (wysiyg_types_object.hasOwnProperty(key)) {              			    

	    				  var text_area_objects=wysiyg_types_object[key];
	    				  if (jQuery(text_area_objects).attr('id')) {
	    					  var text_area_id_check=jQuery(text_area_objects).attr('id');                  		  	    					  	 
	    					  Array.prototype.push.call(get_all_text_areas_array, text_area_id_check);
	    				  }
	    			  }
	    		}
	    	}
	    	return get_all_text_areas_array;
	    },
	    append_prepend_to_relevant_areas: function(the_wysiwyg_text_areas,	    											
	    											selector_before,
	    											selector_after,
	    											html_before,
	    											html_after,
	    											insertion_mode,
	    											unique_identifier	    											
	    											) {	    	
	    	
    		if( (typeof the_wysiwyg_text_areas == "object") && (the_wysiwyg_text_areas !== null) )
    		{
        		for (var key in the_wysiwyg_text_areas) {
      			  if (the_wysiwyg_text_areas.hasOwnProperty(key)) {            			    

                		  var text_area_id_check=the_wysiwyg_text_areas[key];                        		  
                		  var syntax_editor_tab_id= text_area_id_check+unique_identifier;
                		  var target_selectors_field=selector_before+text_area_id_check+selector_after;
                		   
                		   if (!jQuery(target_selectors_field).find('#'+syntax_editor_tab_id).length) {  

                			   if (insertion_mode=='append') {
                				   jQuery(target_selectors_field).append(html_before+syntax_editor_tab_id+html_after); 
                			   } else if (insertion_mode=='prepend'){                				   
                				   jQuery(target_selectors_field).prepend(html_before+syntax_editor_tab_id+html_after); 
                			   }
                		   }
                	 
      			  }
      			}
    		}	  	
	    }
};

//Definition of Toolset Bootstrap helper object for methods
var tb_helper_object = {
		
		return_tinymce_status: function() {

			if (!(window.tinyMCE)) {		
				window.tinyMCE=tinymce;
			}			
	    },
	    
	    return_editor_wrap_id: function(text_area_scope_id) {
	    	
	        if (text_area_scope_id=='content') {    	
	        	//main content editor
	        	var editor_wrap_id_syntax='#wp-content-wrap .content-bootstrap';
	        	var editor_wrap_id_texteditor='#wp-content-wrap a.switch-html:not(.content-bootstrap)';
	        	var editor_wrap_id_cm_media_button='#wp-content-wrap #codemirror-insert-media';
	        	var editor_wrap_id_wp_media_button='#wp-content-wrap .insert-media';
	        	var editor_wrap_id_quicktags='#wp-content-wrap .quicktags-toolbar';
	        } else {
	        	//Types WYSIWYG editor    	
	        	var editor_wrap_id_syntax='#wp-'+text_area_scope_id+'-wrap .content-bootstrap';
	        	var editor_wrap_id_texteditor='#wp-'+text_area_scope_id+'-wrap a.switch-html:not(.content-bootstrap)';
	        	var editor_wrap_id_cm_media_button='#wp-'+text_area_scope_id+'-wrap .codemirror_editor_media_button';
	        	var editor_wrap_id_wp_media_button='#wp-'+text_area_scope_id+'-wrap .insert-media';
	        	var editor_wrap_id_quicktags='#wp-'+text_area_scope_id+'-wrap .quicktags-toolbar';
	        }	
	        
	        var editor_wrap_id_array = [];
	        editor_wrap_id_array['editor_wrap_id_syntax']=editor_wrap_id_syntax;
	        editor_wrap_id_array['editor_wrap_id_texteditor']=editor_wrap_id_texteditor;
	        editor_wrap_id_array['editor_wrap_id_cm_media_button']=editor_wrap_id_cm_media_button;
	        editor_wrap_id_array['editor_wrap_id_wp_media_button']=editor_wrap_id_wp_media_button;
	        editor_wrap_id_array['editor_wrap_id_quicktags']=editor_wrap_id_quicktags;
	    	
	        return editor_wrap_id_array;	    	
	    },
	    
	    return_tab_switching_text_areas_array: function(objecttext_area,editor_mode) {	    	

            var textarea_scope=objecttext_area['context'];                
            var scope_id=jQuery(textarea_scope).attr('id');             
                 
            //Retrieve the editor text area id
            
            if (editor_mode=='syntaxmode') {
            	if (scope_id.indexOf("wpcf-wysiwyg") != -1) {
            		//WYSIWYG Code mirror editor            	    
            		var text_area_scope_id = scope_id.replace('-content-html','');  
            	} else {
            		//Main code mirror editor
            		var text_area_scope_id = scope_id.replace('-html','');
            	}
            } else if (editor_mode=='visualmode') {
            	
            	var text_area_scope_id = scope_id.replace('-tmce','');
            	
            } else if (editor_mode=='textmode') {
            	
            	var text_area_scope_id = scope_id.replace('-html','');
            }            
            var editor_wrap_id_array_data=tb_helper_object.return_editor_wrap_id(text_area_scope_id);
        	icl_editor.toggleCodeMirror(text_area_scope_id, false);
        	tb_helper_object.return_tinymce_status();  
        	if (editor_mode=='syntaxmode') {
        		switchEditors.go( text_area_scope_id, 'html' );
            	window.tinyMCE=false;             	
            	icl_editor.toggleCodeMirror(text_area_scope_id, true);
        	} else if (editor_mode=='visualmode'){
        		switchEditors.go( text_area_scope_id,'tmce'); 
        	} else if (editor_mode=='textmode'){
        		switchEditors.go( text_area_scope_id,'html'); 
        	}
        	
            return editor_wrap_id_array_data; 
  
	    },
	    
	    return_proper_tab_styling_on_switch: function(editor_wrap_id_array_data,editor_mode) {	

	    	/*Syntax tab*/
	    	if ((editor_mode=='visualmode') || (editor_mode=='textmode')) {        	
	    		//Inactivate syntax tab
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_syntax']).attr('style', 'background-color: #EBEBEB;border-color:#DFDFDF #DFDFDF #CCCCCC !important;color:#999999;');
        	
	    	} else if (editor_mode=='syntaxmode') {
	    		//Activate syntax tab
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_syntax']).attr('style', 'background-color: #F5F5F5 !important;border-color:#CCCCCC #CCCCCC #F4F4F4 !important;');	    		
	    	
	    	} else if (editor_mode=='non_syntax_pageload_mode') {
	    		//Inactivate syntax tab
	    		jQuery('.content-bootstrap').attr('style', 'background-color: #EBEBEB;border-color:#DFDFDF #DFDFDF #CCCCCC !important;color:#999999;');	    		
	    	}
	    	
	    	/*Text editor tab*/
	    	if ((editor_mode=='visualmode') || (editor_mode=='syntaxmode')) {
	    		//Inactivate text editor
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_texteditor']).attr('style', 'background-color: #EBEBEB;border-color:#DFDFDF #DFDFDF #CCCCCC !important;');
	    	
	    	} else if (editor_mode=='textmode') {
	    		//Activate text tab
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_texteditor']).attr('style', 'background-color: #F5F5F5 !important;border-color:#CCCCCC #CCCCCC #F4F4F4 !important;');	    		
	    	}
        	
	    	/*Code mirror media*/
	    	if ((editor_mode=='visualmode') || (editor_mode=='textmode')) {  
	    		//Hide codemirror add media
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_cm_media_button']).hide();
	    	
	    	} else if (editor_mode=='syntaxmode') {
	    		//Show code mirror add media
            	jQuery(editor_wrap_id_array_data['editor_wrap_id_cm_media_button']).show();
            	jQuery(editor_wrap_id_array_data['editor_wrap_id_cm_media_button']).css('display','inline-block');	    		
	    	
	    	} else if (editor_mode=='non_syntax_pageload_mode') {
	    		//Hide codemirror add media
	    		jQuery(".codemirror_editor_media_button").hide();	    		
	    	}
	    	
	    	/*Default media*/
	    	if ((editor_mode=='visualmode') || (editor_mode=='textmode')) {
	    		//Show WP default media add button
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_wp_media_button']).show();
	    	
	    	} else if (editor_mode=='syntaxmode') {
	    		//Hide default WP media
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_wp_media_button']).hide();
	    	
	    	} else if (editor_mode=='non_syntax_pageload_mode') {
	    		
	    		//Show WP default media add button
	    		jQuery(".insert-media").show();	    		
	    	}
	    	
	    	/*Text Toolbar*/
	    	if ((editor_mode=='visualmode') || (editor_mode=='syntaxmode')) {
	    		//Hide quick tags toolbar
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_quicktags']).hide();
	    	} else if (editor_mode=='textmode') {
	    		//Show quick tags toolbar
	    		jQuery(editor_wrap_id_array_data['editor_wrap_id_quicktags']).show();
	    	}
	    	
	    	/*Assigning active tab to hidden inputs*/
	    	if (editor_mode=='non_syntax_pageload_mode') {
	    		
	        	if (jQuery('#wp-content-wrap').hasClass('tmce-active')) {

	        		//TinyMCE loading on pageload
	            	//Set active tab to tinyMCE

	            	jQuery('#editor-active-tab').val('tmce');

	        	} else {

	        		//Obviously, text editor
	        		jQuery('#editor-active-tab').val('html');
	        	}  		  		
	    	}
	    	
	    }
};