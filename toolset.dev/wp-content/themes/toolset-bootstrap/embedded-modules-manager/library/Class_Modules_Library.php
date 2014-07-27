<?php
class Class_Modules_Library {

	function __construct()
	{

     //Retrieve modules XML
	 $modules_library_published=$this->retrieve_modules_xml_library_refwptypes();
		
     //Process modules library inputs
     if (!(empty($modules_library_published))) {
     	
     	$modules_library_processed=$this->process_modules_library_inputs($modules_library_published); 	

     	if (!(empty($modules_library_processed))) {
     	//Retrieve unique module categories     	
     	$unique_module_categories=$this->get_module_active_module_categories_from_library($modules_library_processed);
     
     	} else {
     		
     	wp_die('Modules library processed is empty.');
     		
     	}
     	//Retrieved installed modules
     	$currently_installed_modules=$this->get_installed_modules_in_database();
          
     	if (isset($_GET['module_cat'])) {
     		
     		$module_category_for_display=trim($_GET['module_cat']);
     		
     		//Filter by category
     		$modules_library_processed_filtered=$this->filter_module_library_categories($modules_library_processed,$module_category_for_display);
     		
     		//Render filtered modules library
     		$modules_library_rendered=$this->render_modules_library($modules_library_processed_filtered,$unique_module_categories,$module_category_for_display,$currently_installed_modules);
     	
     	} else {
     		//Render modules library
     		if (!(empty($modules_library_processed))) {
		
	  	   	$modules_library_rendered=$this->render_modules_library($modules_library_processed,$unique_module_categories,$module_category_for_display='',$currently_installed_modules);
	     	
 	  	  } 

     	}
     
	} else {
		
	wp_die('Modules library published variable is empty.');	
		
	}	
	
	
	}
	
	function retrieve_modules_xml_library_refwptypes() {
		
		if (defined('MODMAN_LIBRARYXML_PATH')) {

			if ($this->is_user_internet_connected_referencesites()) {
				
				$modules_xml_exported = file_get_contents(MODMAN_LIBRARYXML_PATH);
			
		    	if (($modules_xml_exported==TRUE)) {
		    		$sites_modules_index_library = simplexml_load_string($modules_xml_exported);		    	
		    		
		    	} else {
		    		wp_die(__('<div class="error">Unable to retrieve contents from the server. Please report this.</div>'));
		    	}
		    
		    	if ($sites_modules_index_library) {  
		
					return $sites_modules_index_library;
		    	
		    	}
			} else {
				
				wp_die(__('<div class="error">Make sure you are connected to the Internet to access our modules library server.</div>'));
				
			}	
		}
	}
	
	function process_modules_library_inputs($modules_library_published) {

		    $modules_library_array=array();
			foreach ($modules_library_published as $k1=>$v1) {

			   foreach ($v1 as $k2=>$v2) {			   	
			   	$catch_module=(array)$v2;			   	
			   	foreach ($catch_module as $k3=>$v3) {
			   		$catch_specific_module=(string)$k3;
			   		if ($catch_specific_module=='module') {
			   			
			   			//Fix bug on module manager 1.1. which the library does not allow sites with only
			   			//one module published from reference sites.
			   						   			
			   			if ((is_array($v3)) && (!(empty($v3)))) {
			   				foreach ($v3 as $k4=>$v4) {			   				
			   					$modules_library_array[]=(array)$v4;			   				
			   				}
			   			} else {
			   				$v3=array($v3);
			   				foreach ($v3 as $k4=>$v4) {
			   					$modules_library_array[]=(array)$v4;
			   				}			   				
			   			}
			   		}
			   	}
			   }
			}
			$modules_library_array_clean = json_decode(json_encode($modules_library_array),true);
			return $modules_library_array_clean;
	}	
	
	function render_modules_library($modules_library_processed,$unique_module_categories,$module_category_for_display,$currently_installed_modules) {	
		//Define Install path
		$install_script_path=MODMAN_PLUGIN_URL.'/library/install_module_library.php';	
		// Compose $installed_modules_data with the actual data for installed modules, needed to display links to read-only summaries for its elements
		if (!(empty($currently_installed_modules)) && (is_array($currently_installed_modules))) {
			$installed_modules_data = ModMan_Loader::get('MODEL/Modules')->getModules();//print_r($installed_modules_data);
		} else {
			$installed_modules_data = array();
		}
		// Include the pagination class
        include 'pagination.class.php';        

		?>		
		<section id="modules-manager-library-list-content">
        
        <?php $this->display_categories_on_top_of_library($unique_module_categories,$module_category_for_display); ?>
		
		<ul class="modules-list">
		<?php 	
		// If we have an array with items
		if (count($modules_library_processed)) {		
	
        //modules per page
        $modules_per_page=10;
        
		// Create the pagination object
		$library_pagination = new pagination($modules_library_processed, (isset($_GET['library_page']) ? $_GET['library_page'] : 1), $modules_per_page);
	
		// Decide if the first and last links should show
		$library_pagination ->setShowFirstAndLast(false);
	
		// You can overwrite the default seperator
		$library_pagination ->setMainSeperator(' | ');
	
		// Parse through the pagination class
		$productPages = $library_pagination ->getResults();
	
		// If we have items
		if (count($productPages) != 0) {	
		echo $pageNumbers = '<div id="library_pagination_numbers">'.$library_pagination->getLinks($_GET).'</div>';
			
			//Loop through the published modules in reference sites
			foreach ($productPages as $key=>$modules_rendered) {
		?>
		  	<li class="modules-list-item">
		  	<div class="thumb">
		  	<img src="<?php echo $modules_rendered['moduleimage'];?>" title="" alt="<?php echo $modules_rendered['name'];?>">
		  	</div>
		  	<div class="modules-list-item-content">
			<h3 class="post-title"><?php echo $modules_rendered['name'];?></h3>
          	<p class="post-categories">
            <span id="special-category">Category:</span>
     		<?php 
     		$retrieved_categories_list=array();
     		//Remove pagination arguments from URL
     		$without_paginated_url_inpage=remove_query_arg('library_page', $this->get_admin_url_custom());
     		
     		if ($without_paginated_url_inpage) {
     		   if ($modules_rendered['modulecategories']) {
     			foreach ($modules_rendered['modulecategories'] as $category_key=>$category_values) {
                    if ((is_array($category_values)) && (!(empty($category_values)))) {
       					foreach ($category_values as $key_inner=>$category_value) {
                    	    //Form category hyperlink
                     	   $category_value=trim($category_value);
                     	   $category_url_modules_inpage=add_query_arg( 'module_cat',$category_value,$without_paginated_url_inpage);
                     	   if ($module_category_for_display==$category_value) {
                     	       //Render as plain text
								$category_inpage_link="<span class='plaintextcatgory'>$category_value</span>";                            
                   	     	} else {
                    	    	//Render as hyperlink
                    	        $category_inpage_link="<a href='$category_url_modules_inpage'>$category_value</a>";                        
                    	    }
       						$retrieved_categories_list[]=$category_inpage_link;
       					}
       			    } else {
                    //Text not an array!
 							//Form category hyperlink
							$category_value=trim($category_values);
							$category_url_modules_inpage=add_query_arg( 'module_cat',$category_value,$without_paginated_url_inpage);
							if ($module_category_for_display==$category_value) {
							//Render as plain text
							$category_inpage_link="<span class='plaintextcatgory'>$category_value</span>";
							} else {
							//Render as hyperlink
							$category_inpage_link="<a href='$category_url_modules_inpage'>$category_value</a>";
							}
							$retrieved_categories_list[]=$category_inpage_link;                 

                    }
     			}  
     			} else {

                wp_die('Module categories does not exist in Modules rendered variable.');
                
                }   			
     		$comma_separated_categories = implode(",", $retrieved_categories_list);
     		echo $comma_separated_categories;
     		} else {

            wp_die('Remove_query_arg fails');
            
            }
     		?>
          	</p>
          	<div class="entry"><p><?php echo $modules_rendered['description'];?></p>
     		</div>
			<ul class="checked">
     		<?php 
     		$retrieved_attributes_list=array();
     		foreach ($modules_rendered['moduleattributes'] as $attribute_key=>$attribute_values) {
                if ((is_array($attribute_values)) && (!(empty($attribute_values)))) {
       				foreach ($attribute_values as $key_inner_attribute=>$attribute_value) {
       					$retrieved_attributes_list[]=$attribute_value;
       				}
       			} else {
                //string not array!
                        $retrieved_attributes_list[]=$attribute_values;
                }
     		}
     		foreach ($retrieved_attributes_list as $final_attribute_key=>$final_attribute_value) { 
     		?>				
				<li><?php echo $final_attribute_value;?></li>
     		<?php } ?>
			</ul>
            <?php //Check if already installed     		
            
            if (!(empty($currently_installed_modules)) && (is_array($currently_installed_modules))) {
                //There are installed modules
            	$clean_module_name=$this->stripoffdates_from_modulefilename_mm($modules_rendered['path']);
     			if (in_array($clean_module_name,$currently_installed_modules)){
     			   //Module is already installed, show update button
     			?>
				<p class="module-download-button"><a href="<?php echo $install_script_path.'?module_path='.$modules_rendered['path'];?>&mode_install=update&mm_install_name=<?php echo trim($modules_rendered['name']);?>" class="button button-large button-primary-toolset"><i class=" icon-refresh"></i> Update</a></p>                    
					<?php
					// Action to display links to read-only modes of elements contained in this Module
					do_action( 'wpmodules_library_link_components', $modules_rendered, $installed_modules_data );
					?>
				<?php } else { ?>
				<p class="module-download-button"><a href="<?php echo $install_script_path.'?module_path='.$modules_rendered['path'];?>&mode_install=new&mm_install_name=<?php echo trim($modules_rendered['name']);?>" class="button button-large button-primary-toolset"><i class=" icon-download-alt"></i> Install</a></p>	
				<?php } ?>			
			<?php 		   	
	        } else {
                //No installed modules, show default Install button
            ?>
				<p class="module-download-button"><a href="<?php echo $install_script_path.'?module_path='.$modules_rendered['path'];?>&mode_install=new&mm_install_name=<?php echo trim($modules_rendered['name']);?>" class="button button-large button-primary-toolset"><i class=" icon-download-alt"></i> Install</a></p>            
	 
			<?php			           
			}
            ?>
            </div>
			</li>			
			<?php
            }
			echo $pageNumbers;		
			?>
			</ul>
			</section>
			<?php 			
		  
	   }
	  }

	}
	function get_module_active_module_categories_from_library($modules_library_processed) {

       $complete_categories_list=array();
       
       foreach ($modules_library_processed as $key=>$modules_categories_rendered) {

			foreach ($modules_categories_rendered['modulecategories'] as $module_category_key=>$module_category_values) {
				if ((is_array($module_category_values)) && (!(empty($module_category_values)))) {
                	foreach ($module_category_values as $module_key_inner=>$module_category_value) {
						$complete_categories_list[]=trim($module_category_value);
					}
				} else {
                        $complete_categories_list[]=trim($module_category_values);   
                }
			}

      }
      //Get unique categories
      $unique_categories_array=array_unique($complete_categories_list);
     
      if ((is_array($unique_categories_array)) && (!(empty($unique_categories_array)))) {

           return $unique_categories_array;
      
      } else {

      print_r('Complete categories list');
      print_r('<br />');
      print_r($complete_categories_list);
      print_r('<br />');
      wp_die('Unable to generate unique categories.');
      
      }
  	}
    
  	 function display_categories_on_top_of_library($unique_module_categories,$module_category_for_display) {
    ?>
		<div class="modules-list-categories">
		<p>Browse modules by category:</p>
		<ul>
		<?php //Loop through the unique categories
         
        //Remove pagination arguments from URL
        $without_paginated_url=remove_query_arg('library_page', $this->get_admin_url_custom());
        
        if ($without_paginated_url) {
        
			if (!(empty($unique_module_categories))) {
				foreach ($unique_module_categories as $k=>$v) {
         	   
        	    $category_url_modules=add_query_arg( 'module_cat',$v,$without_paginated_url);
			?>
			         	<li>
		 	        	<?php 
		  		       	if ((trim($v))==$module_category_for_display) {
          	              //Render as plain text
            	            echo trim($v);
		    	     	} else {
            	            //Render as hyperlink
            	        ?>		         	
		    	       	<a href="<?php echo $category_url_modules;?>"><?php echo $v;?></a>
		    	       	<?php }?>
		    	    	</li>        
		    	    <?php     
		   	         }
				 } else {

                 wp_die('Unique module categories are empty.');

                 }
			} else {

            wp_die('Remove_query_arg fails.');
            
            }
				?>    
		</ul>
		</div>
     <?php 

     }
     function filter_module_library_categories($modules_library_processed,$module_category_for_display) {
     
     	$filtered_content_by_categories=array();
     	 
     	foreach ($modules_library_processed as $key=>$modules_categories_for_filtering) {
    		$specific_module_categories=array();
     		foreach ($modules_categories_for_filtering['modulecategories'] as $module_category_key_filtered=>$module_category_values_filtered) {
     			 if (is_array($module_category_values_filtered) && (!(empty($module_category_values_filtered)))) {
                 	foreach ($module_category_values_filtered as $module_key_inner_filtered=>$module_category_value_filtered) {
                 	$specific_module_categories[]=trim($module_category_value_filtered);
     			 	}
     			 } else {
                 //String not an array!
                    $specific_module_categories[]=trim($module_category_values_filtered);
                 }

     		}
     		//Check requested category is in array if not unset
     		if (!(in_array($module_category_for_display,$specific_module_categories))) {
     			unset($modules_library_processed[$key]);
     		}
     
     	}
     	//Finally get content array filtered by categories
     	$filtered_content_by_categories=$modules_library_processed;
     	 
     	if ((is_array($filtered_content_by_categories)) && (!(empty($filtered_content_by_categories)))) {
     
     		return $filtered_content_by_categories;
     
     	}
     }
     function get_installed_modules_in_database() {

		//Load modules from dB
		global $wpdb;
		$modules_db_table=$wpdb->prefix."options";
		@$modules_installed_from_db= $wpdb->get_var("SELECT option_value FROM $modules_db_table where option_name='modman_modules'");
		$modules_installed_from_db_unserialized=unserialize($modules_installed_from_db);
		
		if ((!(empty($modules_installed_from_db_unserialized))) && is_array($modules_installed_from_db_unserialized)) {
            $modules_installed_array=array();
			foreach ($modules_installed_from_db_unserialized as $key=>$value) {

				//Define module name
				//Get lowercase
				$lowercase_key=trim(strtolower($key));				
				$modules_installed_array[]=str_replace(' ', '', $lowercase_key);

			}
		}
		
		if ((!(empty($modules_installed_array))) && is_array($modules_installed_array)) {

          return $modules_installed_array;   

        }

     }
     function stripoffdates_from_modulefilename_mm($module_url_passed) {
     
     	preg_match("/.*([0-9]{4}-[0-9]{2}-[0-9]{2}).*/", $module_url_passed, $matches);
     	$output_no_dates=str_ireplace($matches[1],'',$module_url_passed);
     	
     	//Get base name from URL
     	$basename_of_zip=basename($output_no_dates);
        
        //Get compressed name only
     	$compressed_file_name = substr($basename_of_zip, 0, -12);
     	
     	return $compressed_file_name;
     
     }    
     function get_admin_url_custom() {     

		if (!isset($_SERVER['REQUEST_URI']))
		{
			$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
			if (isset($_SERVER['QUERY_STRING'])) {
                $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
            }
            $get_admin_url_custom=$_SERVER['REQUEST_URI'];	

            return $get_admin_url_custom;
		} else {

			$get_admin_url_custom=$_SERVER['REQUEST_URI'];
			
			return $get_admin_url_custom;
			
        }

     }
     function is_user_internet_connected_referencesites() {
         
		$connected=@fsockopen("ref.wp-types.com",80);
		$is_conn=false;

		if ($connected) {

			$is_conn=true;
		
		} else {

			//Emerson: START Not connected online, check if running the reference site locally
			if (defined('MODMAN_ORIGINATING_HOST')) {
		
				$modulemanager_originating_host=MODMAN_ORIGINATING_HOST;
				if ($modulemanager_originating_host != 'ref.wp-types.com') {
		
					//Running reference site locally
					$connected=@fsockopen($modulemanager_originating_host,80);
					if ($connected) {
						$is_conn=true;
					} else {
						$is_conn= false;
					}
				}
		
			}
		
		}

		return $is_conn;

     }   
}
?>