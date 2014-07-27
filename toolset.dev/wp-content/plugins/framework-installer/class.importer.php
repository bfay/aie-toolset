<?php
/*
 * Importer class
 */
if (!class_exists('WP_Import')) {
    require_once dirname(__FILE__) . '/wordpress-importer/wordpress-importer.php';
}

class WPVDemo_Importer extends WP_Import
{
	var $processed_terms_wpml = array();

	function __construct($site_url) {
		
		$this->site_url = $site_url;

		$this->post_number = 0;
		
		$this->wpml = get_option('wpvdemo_wpml_data', null);
		
	}
	

    /**
     * Registered callback function for the WordPress Importer
     *
     * Manages the three separate stages of the WXR import process
     */
    function dispatch($file) {
    	
    	//Reconfirm sites that needs fetching of images
    	$check_if_sites_needs_imagefetching= $this->wpvdemo_need_to_fetch_images($file);    	
   	
    	if ($check_if_sites_needs_imagefetching) {
    			
    		//Enable fetching of attachments
    		$this->fetch_attachments = true;
    	}   	

        $this->id = mt_rand();
        
        //$file = get_attached_file($this->id);
        
        set_time_limit(0);
        $this->import($file);
        
    }

    /**
     * The main controller for the actual import stage.
     *
     * @param string $file Path to the WXR file for importing
     */
    function import($file) { 

    	//Check if site has WPML implementation
    	$check_if_wpml_implemented= wpvdemo_has_wpml_implementation($file,TRUE);
    	
        add_filter('import_post_meta_key', array($this, 'is_valid_meta_key'));
        add_filter('http_request_timeout',
                array(&$this, 'bump_request_timeout'));

        $this->import_start($file);        
        $this->get_author_mapping();

        wp_suspend_cache_invalidation(true);        
        $this->process_categories($check_if_wpml_implemented);        
        $this->process_tags();        
        $this->process_terms($check_if_wpml_implemented);        
        $this->process_posts($check_if_wpml_implemented);        
        wp_suspend_cache_invalidation(false);

        // update incorrect/missing information in the DB
        $this->backfill_parents();
        $this->backfill_attachment_urls();
        $this->remap_featured_images();    
        
        if ($check_if_wpml_implemented) {
        	
        	global $wpdb;
        	$wpml_icl_translations_table=$wpdb->prefix.'icl_translations';
        	
        	//Clear translations table in advance
        	$wpdb->query(
        			$wpdb->prepare(
        					"
        					DELETE FROM $wpml_icl_translations_table
        					WHERE translation_id > 0
        					"
        			)
        		);
        	
        }
        
		$this->process_wpml();

        $this->import_end();
    }

    /**
     * Parses the WXR file and prepares us for the task of processing parsed data
     *
     * @param string $file Path to the WXR file for importing
     */
    function import_start($file) {
//        if (!is_file($file)) {
//            echo '<p><strong>' . __('Sorry, there has been an error.',
//                    'wordpress-importer') . '</strong><br />';
//            echo __('The file does not exist, please try again.',
//                    'wordpress-importer') . '</p>';
//            $this->footer();
//            die();
//        }

        $import_data = $this->parse($file);
		
        if (is_wp_error($import_data)) {
            echo '<p><strong>' . __('Sorry, there has been an error.',
                    'wordpress-importer') . '</strong><br />';
            echo esc_html($import_data->get_error_message()) . '</p>';
            $this->footer();
            die();
        }

        $this->version = $import_data['version'];
        $this->get_authors_from_import($import_data);
        $this->posts = $import_data['posts'];
        $this->terms = $import_data['terms'];
        $this->categories = $import_data['categories'];
        $this->tags = $import_data['tags'];
        $this->base_url = esc_url($import_data['base_url']);
		
		// Import WPML data.
		$this->wpml = null;
        if (function_exists('simplexml_load_string')) {
			$xml = simplexml_load_file( $file );

			$data = array();
                        //Emerson-Check if XML is true
                        //Prevent errors Fatal error: Call to a member function xpath() on a non-object                   
                              
                        if ($xml) {
                           //Run loop
			foreach ( $xml->xpath('/rss/channel/wpml/translations/item') as $wpml_item ) {
				$item = array(
							  'element_id' => (int) $wpml_item->element_id,
							  'element_type' => (string) $wpml_item->element_type,
							  'language_code' => (string) $wpml_item->language_code,
							  'translation_id' => (int) $wpml_item->translation_id,
							  'trid' => (int) $wpml_item->trid,
							  'source_language_code' => (string) $wpml_item->source_language_code
							  );
				
				$data[] = $item;
			}
			
			$this->wpml = $data;
			update_option('wpvdemo_wpml_data', $this->wpml);
                        } 
		}

		

        wp_defer_term_counting(true);
        wp_defer_comment_counting(true);

        do_action('import_start');
    }
       
    
    /**
	 * Performs post-import cleanup of files and the cache
	 */
	function import_end() {
		wp_import_cleanup( $this->id );

		wp_cache_flush();
		foreach ( get_taxonomies() as $tax ) {
			delete_option( "{$tax}_children" );
			_get_term_hierarchy( $tax );
		}

		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );
		
		$this->fix_types_image_urls();

//		echo '<p>' . __( 'All done.', 'wordpress-importer' ) . ' <a href="' . admin_url() . '">' . __( 'Have fun!', 'wordpress-importer' ) . '</a>' . '</p>';
//		echo '<p>' . __( 'Remember to update the passwords and roles of imported users.', 'wordpress-importer' ) . '</p>';

		do_action( 'import_end' );
	}
	
	function pre_process_postdata($postdata) {
		$postdata['post_content'] = str_replace($this->site_url, get_bloginfo('url'), $postdata['post_content']);
		
		return $postdata;
	}
	
	function pre_process_postmeta($postmeta) {
		
		//foreach ( $postmeta as $key => $meta ) {
		//	$meta['value'] = str_replace($this->site_url, get_bloginfo('url'), $meta['value']);
		//	$postmeta[$key] = $meta;
		//}
		//
		return $postmeta;
		
	}
	
	function fix_types_image_urls() {
		global $wpdb;
		// make sure we do the longest urls first, in case one is a substring of another
		uksort( $this->url_remap, array(&$this, 'cmpr_strlen') );

		foreach ( $this->url_remap as $from_url => $to_url ) {
			echo $from_url . ' - ' . $to_url . '<br />';
			$result = $wpdb->query( $wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = '%s' WHERE meta_value = '%s'", $to_url, $from_url) );
		}
	}
	
	function next_post() {
		$this->post_number++;		
        update_option('wpvdemo-post-count', $this->post_number);
        update_option('wpvdemo-post-total', sizeof($this->posts));
	}
    
    /**
	 * Attempt to download a remote file attachment
	 *
	 * @param string $url URL of item to fetch
	 * @param array $post Attachment details
	 * @return array|WP_Error Local file location details on success, WP_Error otherwise
	 */
	function fetch_remote_file( $url, $post ) {
        
        // Convert to cloud URL
        $url = wpvdemo_convert_to_cloud_url($url, $this->current_site_settings);
        
		// extract the file name and extension from the url
		$file_name = basename( $url );

		// get placeholder file in the upload dir with a unique, sanitized filename
		$upload = wp_upload_bits( $file_name, 0, '', $post['upload_date'] );
		if ( $upload['error'] )
			return new WP_Error( 'upload_dir_error', $upload['error'] );

		// Fetch the remote url and write it to the placeholder file
		//Emerson: Use new method
		$headers = $this->wp_get_http_wpvdemo( $url, $upload['file'],$upload['url']);

		// request failed
		if ( ! $headers ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Remote server did not respond', 'wordpress-importer') );
		}

		// make sure the fetch was successful		
		if (!(strpos($headers[0],'200 OK'))) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s', 'wordpress-importer'), esc_html($headers['response']), get_status_header_desc($headers['response']) ) );
		}

		$filesize = filesize( $upload['file'] );

		if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'wordpress-importer') );
		}

		if ( 0 == $filesize ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', __('Zero size file downloaded', 'wordpress-importer') );
		}

		$max_size = (int) $this->max_attachment_size();
		if ( ! empty( $max_size ) && $filesize > $max_size ) {
			@unlink( $upload['file'] );
			return new WP_Error( 'import_file_error', sprintf(__('Remote file is too large, limit is %s', 'wordpress-importer'), size_format($max_size) ) );
		}

		// keep track of the old and new urls so we can substitute them later
		$this->url_remap[$url] = $upload['url'];
		$this->url_remap[$post['guid']] = $upload['url']; // r13735, really needed?
		// keep track of the destination if the remote url is redirected somewhere else
		if ( isset($headers['x-final-location']) && $headers['x-final-location'] != $url )
			$this->url_remap[$headers['x-final-location']] = $upload['url'];

		return $upload;
	}
	
	/*EMERSON rewrite importing of WPML icl_translations
	 * 
	*/	
	
	function get_existing_original_ids() {
		global $wpdb;
		$table_name = $wpdb->prefix.'icl_translations';
		$existing_ids = $wpdb->get_results("SELECT translation_id FROM $table_name", ARRAY_A);
		$orig_clean_existing_ids=array();
		$output_array_combined=array();
		
		if ((is_array($existing_ids)) && (!(empty($existing_ids)))) {
			foreach ($existing_ids as $key=>$inner_array) {
				foreach ($inner_array as $innert_key=>$value) {
					$orig_clean_existing_ids[]=$value;
				}
			}
			$maximum_ids=max($orig_clean_existing_ids);
			$output_array_combined['max']=$maximum_ids;
			$output_array_combined['clean_id_array']=$orig_clean_existing_ids;
			return $output_array_combined;
			
		} elseif ((is_array($existing_ids)) && (empty($existing_ids)))	 {
			//Empty table
			$output_array_combined['max']=0;
			$output_array_combined['clean_id_array']=array();
			return $output_array_combined;
		}	
		
	}
	
	function process_wpml() {
		global $sitepress, $wpdb;		
		
		$wpml_icl_translations_table=$wpdb->prefix.'icl_translations';
		
		if ($this->wpml && $sitepress) {
			// add the translations to the icl_translations table.
			foreach ($this->wpml as $translation_item) {
				if ($translation_item['source_language_code'] == 'NULL') {
					$translation_item['source_language_code'] = null;
				}
				
				$translation_id = null;				

				// check for posts
				if (strpos($translation_item['element_type'], 'post_') === 0 && isset($this->processed_posts[$translation_item['element_id']])) {
					
					$data_for_max=$this->get_existing_original_ids();
					$current_maximum=$data_for_max['max'];
					$translation_item['translation_id']=$current_maximum+1;
					
					$translation_item['element_id'] = $this->processed_posts[$translation_item['element_id']];
					
					if ($translation_item['source_language_code'] == null) {
						//Remove source language code from array, so it will use the correct NULL default value in the table.						
						unset($translation_item['source_language_code']);
					}
					$wpdb->suppress_errors=true;
					$wpdb->insert($wpdb->prefix.'icl_translations', $translation_item);
					$translation_id = $wpdb->insert_id;
					
				}

				// check for taxonomy
				
				if (strpos($translation_item['element_type'], 'tax_') === 0 && isset($this->processed_terms_wpml[$translation_item['element_id']])) {
					$translation_item['element_id'] = $this->processed_terms_wpml[$translation_item['element_id']];
					
					if ($translation_item['source_language_code'] == null) {
						//Remove source language code from array, so it will use the correct NULL default value in the table.
						unset($translation_item['source_language_code']);
					}

					//Preventing blocked ids due to duplicates
					$data_for_tax=$this->get_existing_original_ids();
					$clean_tax_ids_to_check=$data_for_tax['clean_id_array'];
					$max_tax_ids_to_use=$data_for_tax['max'];
					$current_translation_id_tax_processed=$translation_item['translation_id'];
					
					if (!(in_array($current_translation_id_tax_processed,$clean_tax_ids_to_check))) {
						//Translation id safe to use
						$wpdb->suppress_errors=true;
						$wpdb->insert($wpdb->prefix.'icl_translations', $translation_item);
						$translation_id = $wpdb->insert_id;
					} else {
						//Duplicate detected
						$translation_item['translation_id']=$max_tax_ids_to_use+1;
						$wpdb->suppress_errors=true;
						$wpdb->insert($wpdb->prefix.'icl_translations', $translation_item);
						$translation_id = $wpdb->insert_id;						
					}
				}
				
			}
			
		}
		
	}
	/*EMERSON rewrite method of fetching attachments to minimize issues with timeout and WP 3.6 issues.
	 * Uses native PHP functions like file_put_contents and fopen
	*/
	function wp_get_http_wpvdemo($url,$file_path,$target_url) {
		 
		//Check if image exist on remote site
		$file_headers_image = @get_headers($url);
		 
		if(strpos($file_headers_image[0],'200 OK')) {
			//Image exist, fetch it
	
			$context=stream_context_create(array('http'=>
					array(
							'timeout' => 1200
					)
			));
			$success=@file_put_contents($file_path, fopen($url, 'r',false,$context));
	
			if ($success) {
				//fetch OK, return headers
				$network_site_url=network_site_url();
				$network_site_url=rtrim($network_site_url,'/');
				$views_demo_installation_abs_path=$this->viewsdemo_get_wordpress_base_path();
				$alternative_url_via_root=str_replace($views_demo_installation_abs_path,$network_site_url,$file_path);
				$file_headers_image_return = @get_headers($alternative_url_via_root);
				 
				return $file_headers_image_return;
			} else {
				return FALSE;
			}
	
		} else {
	
			return FALSE;
		}
	}
	
	function viewsdemo_get_wordpress_base_path() {
		$dir = dirname(__FILE__);
		do {
			if( file_exists($dir."/wp-load.php") ) {
				return $dir;
			}
		} while( $dir = realpath("$dir/..") );
		return null;
	}
	
	function wpvdemo_need_to_fetch_images($file) {
		
		$parsed_url=parse_url($file);
		$get_path=$parsed_url['path'];
		
		//Define sites that needs fetching of images
		
		$needs_fetching_array = array( '/_wpv_demo/bootcommerce/posts.xml',
				                        '/_wpv_demo/classifieds/posts.xml',
				                        '/_wpv_demo/bootstrap-estate/posts.xml',
				                        '/_wpv_demo/toolset-bootstrap/posts.xml',
				                        '/_wpv_demo/book-review/posts.xml',
				                        '/_wpv_demo/estates/posts.xml',
				                        '/_wpv_demo/directory/posts.xml',
				                        '/_wpv_demo/bootmag/posts.xml',
										'/_wpv_demo/views-tutorial/posts.xml'
		                              );
		
		if (in_array($get_path,$needs_fetching_array)) {		
			
			return TRUE;
				
		} else {
				
			return FALSE;
				
		}		
	}
	
	//Emerson: Override default process post functionality
	function process_posts($has_wpml_implementation) {
		foreach ( $this->posts as $post ) {
				
			$this->next_post();
				
			if ( ! post_type_exists( $post['post_type'] ) ) {
				printf( __( 'Failed to import &#8220;%s&#8221;: Invalid post type %s', 'wordpress-importer' ),
				esc_html($post['post_title']), esc_html($post['post_type']) );
				echo '<br />';
				continue;
			}
	
			if ( isset( $this->processed_posts[$post['post_id']] ) && ! empty( $post['post_id'] ) )
				continue;
	
			if ( $post['status'] == 'auto-draft' )
				continue;
	
			if ( 'nav_menu_item' == $post['post_type'] ) {
				$this->process_menu_item( $post );
				continue;
			}
	
			$post_type_object = get_post_type_object( $post['post_type'] );			 
				
			$post_exists = post_exists( $post['post_title'], '', $post['post_date'] );
			
			if (( $post_exists && get_post_type( $post_exists ) == $post['post_type'] ) && (!($has_wpml_implementation))) {
				printf( __('%s &#8220;%s&#8221; already exists.', 'wordpress-importer'), $post_type_object->labels->singular_name, esc_html($post['post_title']) );
				echo '<br />';
				$comment_post_ID = $post_id = $post_exists;
			} else {
				$post_parent = (int) $post['post_parent'];
				if ( $post_parent ) {
					// if we already know the parent, map it to the new local ID
					if ( isset( $this->processed_posts[$post_parent] ) ) {
						$post_parent = $this->processed_posts[$post_parent];
						// otherwise record the parent for later
					} else {
						$this->post_orphans[intval($post['post_id'])] = $post_parent;
						$post_parent = 0;
					}
				}
			
				// map the post author
				$author = sanitize_user( $post['post_author'], true );
				if ( isset( $this->author_mapping[$author] ) )
					$author = $this->author_mapping[$author];
				else
					$author = (int) get_current_user_id();
	
				$postdata = array(
						'import_id' => $post['post_id'], 'post_author' => $author, 'post_date' => $post['post_date'],
						'post_date_gmt' => $post['post_date_gmt'], 'post_content' => $post['post_content'],
						'post_excerpt' => $post['post_excerpt'], 'post_title' => $post['post_title'],
						'post_status' => $post['status'], 'post_name' => $post['post_name'],
						'comment_status' => $post['comment_status'], 'ping_status' => $post['ping_status'],
						'guid' => $post['guid'], 'post_parent' => $post_parent, 'menu_order' => $post['menu_order'],
						'post_type' => $post['post_type'], 'post_password' => $post['post_password']
				);
	
				$postdata = $this->pre_process_postdata($postdata);
	
				if ( 'attachment' == $postdata['post_type'] ) {
					$remote_url = ! empty($post['attachment_url']) ? $post['attachment_url'] : $post['guid'];
	
					// try to use _wp_attached file for upload folder placement to ensure the same location as the export site
					// e.g. location is 2003/05/image.jpg but the attachment post_date is 2010/09, see media_handle_upload()
					$postdata['upload_date'] = $post['post_date'];
					if ( isset( $post['postmeta'] ) ) {
						foreach( $post['postmeta'] as $meta ) {
							if ( $meta['key'] == '_wp_attached_file' ) {
								if ( preg_match( '%^[0-9]{4}/[0-9]{2}%', $meta['value'], $matches ) )
									$postdata['upload_date'] = $matches[0];
								break;
							}
						}
					}
	
					$comment_post_ID = $post_id = $this->process_attachment( $postdata, $remote_url );
				} else {
					$comment_post_ID = $post_id = wp_insert_post( $postdata, true );
				}
	
				if ( is_wp_error( $post_id ) ) {
					printf( __( 'Failed to import %s &#8220;%s&#8221;', 'wordpress-importer' ),
					$post_type_object->labels->singular_name, esc_html($post['post_title']) );
					if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
						echo ': ' . $post_id->get_error_message();
					echo '<br />';
					continue;
				}
	
				if ( $post['is_sticky'] == 1 )
					stick_post( $post_id );
			}
	
			// map pre-import ID to local ID
			$this->processed_posts[intval($post['post_id'])] = (int) $post_id;
	
			// add categories, tags and other terms
			if ( ! empty( $post['terms'] ) ) {
				$terms_to_set = array();
				foreach ( $post['terms'] as $term ) {
					// back compat with WXR 1.0 map 'tag' to 'post_tag'
					$taxonomy = ( 'tag' == $term['domain'] ) ? 'post_tag' : $term['domain'];
					$term_exists = term_exists( $term['slug'], $taxonomy );
					$term_id = is_array( $term_exists ) ? $term_exists['term_id'] : $term_exists;
					if ( ! $term_id ) {
						$t = wp_insert_term( $term['name'], $taxonomy, array( 'slug' => $term['slug'] ) );
						if ( ! is_wp_error( $t ) ) {
							$term_id = $t['term_id'];
						} else {
							printf( __( 'Failed to import %s %s', 'wordpress-importer' ), esc_html($taxonomy), esc_html($term['name']) );
							if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
								echo ': ' . $t->get_error_message();
							echo '<br />';
							continue;
						}
					}
					$terms_to_set[$taxonomy][] = intval( $term_id );
				}
	
				foreach ( $terms_to_set as $tax => $ids ) {
					$tt_ids = wp_set_post_terms( $post_id, $ids, $tax );
				}
				unset( $post['terms'], $terms_to_set );
			}
	
			// add/update comments
			if ( ! empty( $post['comments'] ) ) {
				$num_comments = 0;
				$inserted_comments = array();
				foreach ( $post['comments'] as $comment ) {
					$comment_id	= $comment['comment_id'];
					$newcomments[$comment_id]['comment_post_ID']      = $comment_post_ID;
					$newcomments[$comment_id]['comment_author']       = $comment['comment_author'];
					$newcomments[$comment_id]['comment_author_email'] = $comment['comment_author_email'];
					$newcomments[$comment_id]['comment_author_IP']    = $comment['comment_author_IP'];
					$newcomments[$comment_id]['comment_author_url']   = $comment['comment_author_url'];
					$newcomments[$comment_id]['comment_date']         = $comment['comment_date'];
					$newcomments[$comment_id]['comment_date_gmt']     = $comment['comment_date_gmt'];
					$newcomments[$comment_id]['comment_content']      = $comment['comment_content'];
					$newcomments[$comment_id]['comment_approved']     = $comment['comment_approved'];
					$newcomments[$comment_id]['comment_type']         = $comment['comment_type'];
					$newcomments[$comment_id]['comment_parent'] 	  = $comment['comment_parent'];
					$newcomments[$comment_id]['commentmeta']          = isset( $comment['commentmeta'] ) ? $comment['commentmeta'] : array();
					if ( isset( $this->processed_authors[$comment['comment_user_id']] ) )
						$newcomments[$comment_id]['user_id'] = $this->processed_authors[$comment['comment_user_id']];
				}
				ksort( $newcomments );
	
				foreach ( $newcomments as $key => $comment ) {
					// if this is a new post we can skip the comment_exists() check
					if ( ! $post_exists || ! comment_exists( $comment['comment_author'], $comment['comment_date'] ) ) {
						if ( isset( $inserted_comments[$comment['comment_parent']] ) )
							$comment['comment_parent'] = $inserted_comments[$comment['comment_parent']];
						$comment = wp_filter_comment( $comment );
						$inserted_comments[$key] = wp_insert_comment( $comment );
	
						foreach( $comment['commentmeta'] as $meta ) {
							$value = maybe_unserialize( $meta['value'] );
							add_comment_meta( $inserted_comments[$key], $meta['key'], $value );
						}
	
						$num_comments++;
					}
				}
				unset( $newcomments, $inserted_comments, $post['comments'] );
			}
	
			// add/update post meta
			if ( isset( $post['postmeta'] ) ) {
	
				$post['postmeta'] = $this->pre_process_postmeta($post['postmeta']);
	
				foreach ( $post['postmeta'] as $meta ) {
					$key = apply_filters( 'import_post_meta_key', $meta['key'] );
					$value = false;
	
					if ( '_edit_last' == $key ) {
						if ( isset( $this->processed_authors[intval($meta['value'])] ) )
							$value = $this->processed_authors[intval($meta['value'])];
						else
							$key = false;
					}
	
					if ( $key ) {
						// export gets meta straight from the DB so could have a serialized string
						if ( ! $value )
							$value = maybe_unserialize( $meta['value'] );
	
						add_post_meta( $post_id, $key, $value );
						do_action( 'import_post_meta', $post_id, $key, $value );
	
						// if the post has a featured image, take note of this in case of remap
						if ( '_thumbnail_id' == $key )
							$this->featured_images[$post_id] = (int) $value;
					}
				}
			}
		}
	
		unset( $this->posts );
	}
	
	//Emerson: Override process terms function to correctly import WPML terms to database
	
	function process_categories($has_wpml_implementation) {

		if ($has_wpml_implementation) {
				
			$wpml_term_taxonomy_array=$this->wpml_term_taxonomy_data_func();
				
		}
				
		if ( empty( $this->categories ) )
			return;
	
		foreach ( $this->categories as $cat ) {
			// if the category already exists leave it alone
			$term_id = term_exists( $cat['category_nicename'], 'category' );			
			
			if ( $term_id ) {
				if ( is_array($term_id) ) {
					$term_id = $term_id['term_id'];
				}
				if ( isset($cat['term_id']) ) {
					$this->processed_terms[intval($cat['term_id'])] = (int) $term_id;
					 
					if ($has_wpml_implementation) {
						$term_taxonomy_id_from_referencex_site=$wpml_term_taxonomy_array[$term_id];
						$this->processed_terms_wpml[$term_taxonomy_id_from_referencex_site] = $term_id;
					}
				}
				continue;
			}			
			//DONE ABOVE			
	
			$category_parent = empty( $cat['category_parent'] ) ? 0 : category_exists( $cat['category_parent'] );
			$category_description = isset( $cat['category_description'] ) ? $cat['category_description'] : '';
			$catarr = array(
					'category_nicename' => $cat['category_nicename'],
					'category_parent' => $category_parent,
					'cat_name' => $cat['cat_name'],
					'category_description' => $category_description
			);
	
			$id = wp_insert_category( $catarr );
			if ( ! is_wp_error( $id ) ) {				
				
				if ( isset($cat['term_id']) ) {
					$this->processed_terms[intval($cat['term_id'])] = $id;
						
					if ($has_wpml_implementation) {
							
						//Get term taxonomy ID passed from reference site given its original term ID
						$term_taxonomy_id_from_reference_site=$wpml_term_taxonomy_array[intval($cat['term_id'])];
				
						//Get the new term taxonomy ID after term insertion to database
						$term_taxonomy_id_from_term_insertion=$id;
				
						//Add to process terms for WPML processing
						$this->processed_terms_wpml[$term_taxonomy_id_from_reference_site] = $term_taxonomy_id_from_term_insertion;
					}
				}
				//DONE ABOVE
			} else {
				printf( __( 'Failed to import category %s', 'wordpress-importer' ), esc_html($cat['category_nicename']) );
				if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
					echo ': ' . $id->get_error_message();
				echo '<br />';
				continue;
			}
		}
	
		unset( $this->categories );
	}
	
	function process_terms($has_wpml_implementation) {
		
		if ($has_wpml_implementation) {
			
		   $wpml_term_taxonomy_array=$this->wpml_term_taxonomy_data_func();	
			
		}
		
		if ( empty( $this->terms ) )
			return;
	
		foreach ( $this->terms as $term ) {
			// if the term already exists in the correct taxonomy leave it alone
			$term_id = term_exists( $term['slug'], $term['term_taxonomy'] );	

			if ( $term_id ) {	
          	  if ( is_array($term_id) ) {
         	        $term_id = $term_id['term_id'];
          	  }	
          	  if ( isset($term['term_id']) ) {
           	     $this->processed_terms[intval($term['term_id'])] = (int) $term_id; 
                 
                 if ($has_wpml_implementation) {
           	     	$this->processed_terms_wpml[intval($term['term_id'])] = $id['term_id'];
                 }
           	  }          
            	continue;
        	}			

			if ( empty( $term['term_parent'] ) ) {
				$parent = 0;
			} else {
				$parent = term_exists( $term['term_parent'], $term['term_taxonomy'] );
				if ( is_array( $parent ) ) $parent = $parent['term_id'];
			}
			$description = isset( $term['term_description'] ) ? $term['term_description'] : '';
			$termarr = array( 'slug' => $term['slug'], 'description' => $description, 'parent' => intval($parent) );
	
			$id = wp_insert_term( $term['term_name'], $term['term_taxonomy'], $termarr );
			if ( ! is_wp_error( $id ) ) {
				if ( isset($term['term_id']) ) {
					$this->processed_terms[intval($term['term_id'])] = $id['term_id'];
					
					if ($has_wpml_implementation) {	
							
						//Get term taxonomy ID passed from reference site given its original term ID
						$term_taxonomy_id_from_reference_site=$wpml_term_taxonomy_array[intval($term['term_id'])];
						
						//Get the new term taxonomy ID after term insertion to database
						$term_taxonomy_id_from_term_insertion=$id['term_taxonomy_id'];
						
						//Add to process terms for WPML processing
						$this->processed_terms_wpml[$term_taxonomy_id_from_reference_site] = $term_taxonomy_id_from_term_insertion;
					}						
				}
			} else {
				printf( __( 'Failed to import %s %s', 'wordpress-importer' ), esc_html($term['term_taxonomy']), esc_html($term['term_name']) );
				if ( defined('IMPORT_DEBUG') && IMPORT_DEBUG )
					echo ': ' . $id->get_error_message();
				echo '<br />';
				continue;
			}
		}
	
		unset( $this->terms );
	}
	
	function wpml_term_taxonomy_data_func() {
		
		$current_site_settings=$this->current_site_settings;
		$download_url_settings=$current_site_settings->download_url;
		$download_url_settings=(string)$download_url_settings;		
		
		$remote_xml_url = $download_url_settings . '/wpml_term_taxonomy_data.xml';
		$remote_xml_url_headers = @get_headers($remote_xml_url);
		
		if (strpos($remote_xml_url_headers[0],'200 OK')) {
			 
			//Parse remote XML
			$data_wpml_terms_remote=wpv_remote_xml_get($remote_xml_url);
			 
			if (!($data_wpml_terms_remote)) {
				return false;
			}
			 
			$xml_wpml_terms_settings = simplexml_load_string($data_wpml_terms_remote);
			$import_data_wpml_terms_taxonomy = wpv_admin_import_export_simplexml2array($xml_wpml_terms_settings);
			 
			//Prepare data
			foreach ($import_data_wpml_terms_taxonomy as $key_map=>$values_map) {
				$import_data_wpml_terms_map[]=$values_map;
				unset($import_data_wpml_terms_map[$key_map]);
			}
			
			$wp_terms_taxonomy_reference_site=array();
			
			/*
			 * Final array key: term_id
			 * Final array value: term_taxonomy_id
			 */
			foreach ($import_data_wpml_terms_map as $key=>$inner_array) {
			  $wp_terms_taxonomy_reference_site[$inner_array['term_id']]=$inner_array['term_taxonomy_id'];			
			}		
			
			return $wp_terms_taxonomy_reference_site;
			 
		}
		
	}
}
