jQuery( document ).ready( function( $ ) {
	$('#ajax_result_batchprocessing').hide();
	$('#update_settings_div_wc_views').fadeOut(5000);
	var status=$('input[name=woocommerce_views_batchprocessing_settings]:checked', '#woocommerce_views_form').val();
	if (!(status=='manually')) {
		$('#woocommerce_batchprocessing_submit').hide();
	}
	$('#manual_id_wc_views').click(function(){
		$('#woocommerce_batchprocessing_submit').show();		
	});
	$('#wp_cron_id_wc_views').click(function(){
		$('#woocommerce_batchprocessing_submit').hide();		
	});
	$('#system_cron_id_wc_views').click(function(){
		$('#woocommerce_batchprocessing_submit').hide();		
	});	
	
	$("#requestformanualbatchprocessing").submit(function (e) {
	    e.preventDefault();    
        //AJAX request for batch processing
	    
	    //Retrieve the manual parameter
	    var manual_parameter=$('#woocommerce_batchprocessing_submit').val();
	    
	    $('#ajax_result_batchprocessing').removeClass('updated');
	    $('#ajax_result_batchprocessing').removeClass('error');
	    $('#ajax_result_batchprocessing').css('display','block');
		var html_ajax_loader='<img src="'+the_ajax_script_wc_views.wc_views_ajax_ajax_loader_gif+'" />';
		$('#ajax_result_batchprocessing').html(html_ajax_loader);	  
			
	    var data = {
	      			action: 'wc_views_ajax_response_admin',
	   				dataType: 'json',
	 	    		wpv_wc_views_ajax_response_admin_nonce: the_ajax_script_wc_views.wc_views_ajax_response_admin_nonce,
	 		    	wpv_manual_parameter:manual_parameter	 			    					
	    			};	

	        //Do an ajax request 			

	 	     $.post(the_ajax_script_wc_views.ajaxurl,data, function(response) {
	 		 var myObj_wc_views_status = $.parseJSON(response);
	         console.log(response);
	 		 if (myObj_wc_views_status.status=='updated'){
	 			$('#ajax_result_batchprocessing').show();
	 			$('#ajax_result_batchprocessing').addClass(myObj_wc_views_status.status);
	 			$('#ajax_result_batchprocessing').text(myObj_wc_views_status.batch_processing_output);
	 			$('#ajax_result_batchprocessing').fadeOut(5000);
	 			//Remove first update if it exist
	 			$('#update_needed_wcviews').remove();
	 			//Display last run date and time
	 			$('#ajax_result_batchprocessing_time').text(the_ajax_script_wc_views.wc_views_last_run_translatable_text +myObj_wc_views_status.last_run);
	 		 }
	 		 if (myObj_wc_views_status.status=='error') {
		 			$('#ajax_result_batchprocessing').addClass(myObj_wc_views_status.status);
		 			$('#ajax_result_batchprocessing').text(myObj_wc_views_status.batch_processing_output);		 			
		 		 }	 		 
	 	     });	     
	    
	});	
	
});