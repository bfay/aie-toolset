<?php

/**
*  WooCommerce Views Shortcode GUI callback
*/

if(is_admin()){ 
	add_action('init', 'wcviews_shortcodes_gui_init');
}

function wcviews_shortcodes_gui_init() {
	add_action('admin_head', 'wcviews_shortcodes_gui_js_init');            
}

function wcviews_shortcodes_gui_js_init() {
?>
	<script type="text/javascript">
		//<![CDATA[
		function wcviews_insert_wpv_woo_buy_or_select() {        
		jQuery.colorbox({
			href: '<?php echo admin_url('admin-ajax.php'); ?>' + '?_wpnonce=' + '<?php echo wp_create_nonce('wcviews_editor_callback'); ?>' + '&action=wcviewsgui_wpv_woo_buy_or_select',
			inline : false,
			onComplete: function() {

			}
		});
		}
		function wcviews_insert_wpv_woo_buy_options() {        
			jQuery.colorbox({
				href: '<?php echo admin_url('admin-ajax.php'); ?>' + '?_wpnonce=' + '<?php echo wp_create_nonce('wcviews_editor_callback'); ?>' + '&action=wcviewsgui_wpv_woo_buy_options',
				inline : false,
				onComplete: function() {

				}
			});
		}
		function wcviews_insert_wpv_woo_product_image() {        
			jQuery.colorbox({
				href: '<?php echo admin_url('admin-ajax.php'); ?>' + '?_wpnonce=' + '<?php echo wp_create_nonce('wcviews_editor_callback'); ?>' + '&action=wcviewsgui_wpv_woo_product_image',
				inline : false,
				onComplete: function() {

				}
			});
		}		
		var wpcfFieldsEditorCallback_redirect = null;
		
		function wpcfFieldsEditorCallback_set_redirect(function_name, params) {		
		wpcfFieldsEditorCallback_redirect = {'function' : function_name, 'params' : params};
		}
		
		//]]>
	</script>
<?php
}
function wcviewsgui_wpv_woo_buy_or_select_func() {

	if (wp_verify_nonce($_GET['_wpnonce'], 'wcviews_editor_callback')) {
				?>
			<div class="wpv-dialog js-insert-wpv-woo-buy-or-select">
				<div class="wpv-dialog-header">
					<h2><?php echo __('Configure this shortcode:', 'woocommerce_views');?> <span id="wc_viewsguiheader"><?php _e('Buy or select product for listing pages','woocommerce_views');?></span></h2>
				</div>				
				<div class="wpv-dialog-content">
				        <p id="wc_viewsshortcode_gui_description"><span id="descriptionheader_gui_wcviews"><?php _e("Description:","woocommerce_views");?></span> <span id="descriptiontext_gui_wcviews"><?php _e("Displays 'Add to cart' or 'Select options' button in product listing pages.","woocommerce_views");?></span></p>						
						<p id="addtocarttext_wcviews_gui"><?php _e('Add to Cart Text:','woocommerce_views');?></p>						
						<p id="add_to_cart_text_wcviewsenclosure"><input type="text" name="add_to_cart_text_wc_views_shortcodegui" id="add_to_cart_text_wc_views_shortcodegui" value=""></p>
						<p id="defaulttext_wcviews_gui"><?php _e('Optional. Defaults to "Add to cart"','woocommerce_views');?></p>
						<p id="linktoproducttext_wcviews_gui"><?php _e('Link to Product Text:','woocommerce_views');?></p>										
						<p id="linktoproduct_text_wcviewsenclosure"><input type="text" name="linktoproduct_text_wc_views_shortcodegui" id="linktoproduct_text_wc_views_shortcodegui" value=""></p>
						<p id="defaulttext_wcviews_gui"><?php _e('Optional. Defaults to "Select options"','woocommerce_views');?></p>
				</div>
					<div class="wpv-dialog-footer">
						<button class="button-secondary js-dialog-close"><?php _e('Cancel','woocommerce_views') ?></button>					
						<button class="button-primary js-wpv-insert-wpv_woo_buy_or_select_shortcode" onclick="wcviews_insert_wpv_woo_buy_or_select_shortcode()"><?php echo __('Insert shortcode', 'wpv-views'); ?></button>
					</div>			
				<script type="text/javascript">				     
					//<![CDATA[
					function wcviews_insert_wpv_woo_buy_or_select_shortcode() {						
						jQuery('.js-wpv-insert-wpv_woo_buy_or_select_shortcode').removeClass('button-primary').addClass('button-secondary').prop('disabled', true);
						var spinnerContainer = jQuery('<div class="spinner ajax-loader">').insertAfter(jQuery('.js-wpv-insert-wpv_woo_buy_or_select_shortcode')).show(),
						addtocarttext= jQuery('#add_to_cart_text_wc_views_shortcodegui').val();
						window.icl_editor.insert('[wpv-woo-buy-or-select add_to_cart_text="' + addtocarttext + '" link_to_product_text="' + jQuery('#linktoproduct_text_wc_views_shortcodegui').val() + '"]');
						jQuery.colorbox.close();
					}
					
					//]]>
				</script>
			</div> 
	        <?php
		}        
		die();
}
function wcviewsgui_wpv_woo_buy_options_func() {

	if (wp_verify_nonce($_GET['_wpnonce'], 'wcviews_editor_callback')) {
		?>
			<div class="wpv-dialog js-insert-wpv-woo-buy-options">
				<div class="wpv-dialog-header">
					<h2><?php echo __('Configure this shortcode:', 'woocommerce_views');?> <span id="wc_viewsguiheader"><?php _e('Purchase options for single product','woocommerce_views');?></span></h2>
				</div>				
				<div class="wpv-dialog-content">
				        <p id="wc_viewsshortcode_gui_description"><span id="descriptionheader_gui_wcviews"><?php _e("Description:","woocommerce_views");?></span> <span id="descriptiontext_gui_wcviews"><?php _e("Displays 'add to cart' (for simple products) or 'select options' box (for variation products) in single product pages.","woocommerce_views");?></span></p>						
						<p id="addtocarttext_wcviews_gui"><?php _e('Add to Cart Text:','woocommerce_views');?></p>						
						<p id="add_to_cart_text_wcviewsenclosure"><input type="text" name="add_to_cart_textproductpage_wc_views_shortcodegui" id="add_to_cart_textproductpage_wc_views_shortcodegui" value=""></p>
						<p id="defaulttext_wcviews_gui"><?php _e('Optional. Defaults to "Add to cart"','woocommerce_views');?></p>
				</div>
					<div class="wpv-dialog-footer">
						<button class="button-secondary js-dialog-close"><?php _e('Cancel','woocommerce_views') ?></button>					
						<button class="button-primary js-wpv-insert-wpv_woo_buy_options_shortcode" onclick="wcviews_insert_wpv_woo_buy_options_shortcode()"><?php echo __('Insert shortcode', 'wpv-views'); ?></button>
					</div>			
				<script type="text/javascript">				     
					//<![CDATA[
					function wcviews_insert_wpv_woo_buy_options_shortcode() {						
						jQuery('.js-wpv-insert-wpv_woo_buy_options_shortcode').removeClass('button-primary').addClass('button-secondary').prop('disabled', true);
						var spinnerContainer = jQuery('<div class="spinner ajax-loader">').insertAfter(jQuery('.js-wpv-insert-wpv_woo_buy_options_shortcode')).show(),
						productaddtocarttext= jQuery('#add_to_cart_textproductpage_wc_views_shortcodegui').val();
						window.icl_editor.insert('[wpv-woo-buy-options add_to_cart_text="' + productaddtocarttext + '"]');
						jQuery.colorbox.close();
					}
					
					//]]>
				</script>
			</div> 
	        <?php
		}        
		die();
}
function wcviewsgui_wpv_woo_product_image_func() {

	if (wp_verify_nonce($_GET['_wpnonce'], 'wcviews_editor_callback')) {
		?>
			<div class="wpv-dialog js-insert-wpv-woo-product-image">
				<div class="wpv-dialog-header">
					<h2><?php echo __('Configure this shortcode:', 'woocommerce_views');?> <span id="wc_viewsguiheader"><?php _e('Product image','woocommerce_views');?></span></h2>
				</div>				
				<div class="wpv-dialog-content">
				        <p id="wc_viewsshortcode_gui_description"><span id="descriptionheader_gui_wcviews"><?php _e("Description:","woocommerce_views");?></span> <span id="descriptiontext_gui_wcviews"><?php _e("Display the product image on single product and product listing pages. It will use the product featured image if set or output a placeholder if empty. This will also display variation images.","woocommerce_views");?></span></p>						
						<p id="imagesetting_wcviews_gui"><?php _e('Select image size:','woocommerce_views');?></p>						
						<?php 
						global $Class_WooCommerce_Views;
						
						//Retrieve available image sizes usable for WooCommerce Product Images
						$available_images_for_wcviews=$Class_WooCommerce_Views->wc_views_list_image_sizes();
						
						//Loop through the sizes and display as options
						if ((is_array($available_images_for_wcviews)) && (!(empty($available_images_for_wcviews)))) {
                        ?>
 	                       <select id="wcviews_available_image_sizes" name="wcviews_available_image_sizes">
 	                       <?php 
 	                       //Set the clean name array
 	                       $clean_image_name_array=array(
													'thumbnail'=>		__('WordPress thumbnail size','woocommerce_views'),
													'medium'=>   		__('WordPress medium image size','woocommerce_views'),
												    'large'=>    		__('WordPress large image size','woocommerce_views'),
													'shop_thumbnail'=>  __('WooCommerce product thumbnail size','woocommerce_views'),
													'shop_catalog'  =>  __('WooCommerce shop catalog image size','woocommerce_views'),
													'shop_single'   =>  __('WooCommerce single product image size','woocommerce_views'));

							foreach ($available_images_for_wcviews as $key=>$value) {
                                if (isset($clean_image_name_array[$key])) {
									$image_name_set=$clean_image_name_array[$key];
                                } else {
									$image_name_set='['.__('Custom size','woocommerce_views').']-'.$key;
                                }   
                            	
 	                       ?>
                        		<option value="<?php echo $key;?>" <?php if ($key=='shop_single') { echo "SELECTED";} ?>><?php echo $image_name_set;?></option>
                            <?php 
                           	}
                           	?>
                           	</select>
                           	<?php 
                           	//Retrieve default image size
                           	if (isset($available_images_for_wcviews['shop_single'])) {
 								if ((is_array($available_images_for_wcviews['shop_single'])) && (!(empty($available_images_for_wcviews['shop_single'])))) {
                           			$default_image_size_php=$available_images_for_wcviews['shop_single'];                           			
                           			$default_imagewidth_size_set=$default_image_size_php[0];
                           			$default_imageheight_size_set=$default_image_size_php[1];
                           	?>
									<span id="imagesizes_outputtext_wcviews"><?php echo $default_imagewidth_size_set; ?> x <?php echo $default_imageheight_size_set; ?> ( <?php _e('in pixels','woocommerce_views');?> )</span>
							<?php                            	
                           		}                           		
                           	} else {
                            ?>
									<span id="imagesizes_outputtext_wcviews"></span>                            
                            <?php 
                            }

                        }					
						?>
						<p id="defaulttext_wcviews_gui"><?php _e('Optional. Defaults to "Single Product Image"','woocommerce_views');?></p>
						<p id="imagesetting_wcviews_gui"><?php _e('Select output format:','woocommerce_views');?></p>
						<select id="wcviews_available_output_format" name="wcviews_available_output_format">
						<option value="" SELECTED>WooCommerce default</option>
						<option value="img_tag">Output image tag only</option>
						<option value="raw">Output image URL only</option>
						</select>
						<p id="defaulttext_wcviews_gui"><?php _e('Optional. Defaults to WooCommerce image format which is an image link and popup when clicked.','woocommerce_views');?></p>						
				</div>
					<div class="wpv-dialog-footer">
						<button class="button-secondary js-dialog-close"><?php _e('Cancel','woocommerce_views') ?></button>					
						<button class="button-primary js-wpv-insert-wpv_woo_product_image_shortcode" onclick="wcviews_insert_wpv_woo_product_image_shortcode()"><?php echo __('Insert shortcode', 'wpv-views'); ?></button>
					</div>			
				<script type="text/javascript">				     
					//<![CDATA[
					function wcviews_insert_wpv_woo_product_image_shortcode() {						
						jQuery('.js-wpv-insert-wpv_woo_product_image_shortcode').removeClass('button-primary').addClass('button-secondary').prop('disabled', true);
						var spinnerContainer = jQuery('<div class="spinner ajax-loader">').insertAfter(jQuery('.js-wpv-insert-wpv_woo_product_image_shortcode')).show(),
						user_image_size_selected= jQuery('#wcviews_available_image_sizes').val();
						user_image_format_selected= jQuery('#wcviews_available_output_format').val();
						window.icl_editor.insert('[wpv-woo-product-image size="' + user_image_size_selected + '" output="' + user_image_format_selected +'"]');
						jQuery.colorbox.close();
					}
					jQuery('#wcviews_available_image_sizes').change(function() {
						<?php if (is_array($available_images_for_wcviews) && (!(empty($available_images_for_wcviews)))) { ?>
						
						 var available_sizes_array_canonical=<?php echo json_encode($available_images_for_wcviews);?>;						 
					     var setting_used_sizes=jQuery('#wcviews_available_image_sizes').val();
					     var image_sizes_unprocessed=available_sizes_array_canonical[setting_used_sizes];					     
					     var image_height_set=image_sizes_unprocessed[1];
					     var image_width_set=image_sizes_unprocessed[0];
					     var output_text_image_note=image_width_set+'  x  '+image_height_set+' ( <?php echo esc_js(__('in pixels','woocommerce_views'));?> )';                         
					     jQuery('#imagesizes_outputtext_wcviews').text(output_text_image_note);
					     
					     <?php } ?>
					});						
					//]]>
				</script>
			</div> 
	        <?php
		}        
		die();
}