<?php
/**
 * MainClass
 *
 * Main class of the plugin
 * Class encapsulates all hook handlers
 *
 */
class ModuleManager
{

    public static $messages=array();
    public static $page;
/**
 * Initialize plugin enviroment
 */
   public static function init()
   {
        // init them
        add_action('init', array(__CLASS__, '_init_'), 100); // late init in order to have all custom post types and taxonomies registered
   }

   public static function _init_()
   {
        // load translations from locale
        ModMan_Loader::loadLocale('module-manager');
        
        // set up models and db settings
        self::prepareDB();

        if(is_admin())
        {
            // do this on admin only
            self::prepareFolders();

            // setup js, css assets
            add_action('admin_enqueue_scripts', array(__CLASS__,'onAdminEnqueueScripts'));
            add_action('wpmodules_inline_element_gui', array(__CLASS__, 'inlineElementGUI'), 5, 1);

            // add plugin menus
            add_action('admin_menu', array(__CLASS__, 'addMenuItems'));
            // make page a parameter
            self::$page=admin_url('admin.php').'?page=ModuleManager_Modules';
            
            // Add settings link on plugin page
            add_filter("plugin_action_links_".MODMAN_PLUGIN, array(__CLASS__, 'addSettingsLink'));
        }
        // user settings
        ModMan_Settings::userSettings(array('module-manager'));
        
        // include router for REST API
        ModMan_Ajax_Router::addRoutes('modman', array(
            'Modules'=>0 // Modules controller
        ));
   }

    public static function onAdminEnqueueScripts()
    {
        global $pagenow, $post_type, $wp_version;
		
        // setup css js
        if
            (
               $pagenow=='admin.php' && isset($_GET['page']) &&
               'ModuleManager_Modules'==$_GET['page']
            )
        {
            if (defined('MODMAN_DEV')&&MODMAN_DEV)
            {
                ModMan_Loader::loadAsset('STYLE/module-manager-dev', 'module-manager');
                ModMan_Loader::loadAsset('SCRIPT/module-manager-dev', 'module-manager');
            }
            else
            {
                ModMan_Loader::loadAsset('STYLE/module-manager', 'module-manager');
                ModMan_Loader::loadAsset('SCRIPT/module-manager', 'module-manager');
            }
            
            // Translations
            // insert into javascript
            wp_localize_script( 'module-manager', 'ModuleManagerConfig', array(
                'Settings'=>array(
                    'ajaxurl' => self::route('/Modules/saveModules'),
                    'exportModuleRoute' => self::route('/Modules/exportModule'),
                    'moduleInfoKey' => MODMAN_MODULE_INFO
                ),
                'Locale'=>array(
                    'onPageExit' => __('Settings have changed, if you leave the page they will not be saved!','module-manager'),
                    'onModuleRemove' => __('Do you want to completely remove this module?','module-manager'),
                    'addNewModuleTip' => '<h3>'.__('Add new module','module-manager').'</h3>'.'<p>'.__('Click here to add a new module','module-manager').'</p>',
                    'newModuleName' => __('Module Name','modman'),
                    'addElementsTip' =>	'<h3>' . __( 'How to add elements', 'module-manager' ) . '</h3>'.'<p>' . __( 'Drag elements here to add them to this module.', 'module-manager' ) . '</p>',
                    'exportErrorMsg' => __('An error occurred please try again','module-manager'),
                    'moduleEmptyMsg' => __('Module is empty','module-manager'),
                    'itemNotAvailableTip' => '<h3>'.__('Item Not Available','module-manager').'</h3>'.'<p>'.__('This item has been removed from your site or is not available, so it will not be included in the exported module.','module-manager').'</p>'
                )
            ));
        }
    }

    // setup settings menu link on plugins page
    public static function addSettingsLink($links)
    {
        if (current_user_can(MODMAN_CAPABILITY))
        {
            $settings_link = '<a href="'.self::$page.'">'.__('Settings','module-manager').'</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    // setup Module Manager menus in admin
    public static function addMenuItems()
    {
		$menu_label = __( 'Module Manager','module-manager' );

        $mm_index = 'ModuleManager_Modules';
        $hook1=add_menu_page($menu_label, $menu_label, MODMAN_CAPABILITY, $mm_index, array(__CLASS__, 'ModulesMenuPage'), 'none', 120);
        $hook2=add_submenu_page($mm_index, __('Import Modules', 'module-manager'), __('Import Modules', 'module-manager'), MODMAN_CAPABILITY, 'admin.php?page=ModuleManager_Modules&tab=import');
        
        //Add modules library to menu
        $hook3=add_submenu_page($mm_index, __('Modules Library', 'module-manager'), __('Modules Library', 'module-manager'), MODMAN_CAPABILITY, 'admin.php?page=ModuleManager_Modules&tab=library');
        
        //self::addScreenHelp($hook1, '');
        //self::addScreenHelp($hook2, '');
   }

    public static function ModulesMenuPage()
    {
        ModMan_Loader::load('VIEW/modules');
    }
    
    /**
     * Adds help on admin pages.
     * 
     * @param type $contextual_help
     * @param type $screen_id
     * @param type $screen
     * @return type 
     */
    public static function addScreenHelp( $hook, $contextual_help = '' ) 
    {
        global $wp_version;
        $call = false;
        
        // WP 3.3 changes
        if ( version_compare( $wp_version, '3.2.1', '>' ) ) 
        {
            set_current_screen( $hook );
            $screen = get_current_screen();
            if ( !is_null( $screen ) ) 
            {
                $args = array(
                    'title' => __( 'Module Manager', 'module-manager' ),
                    'id' => $hook,
                    'content' => $contextual_help,
                    'callback' => false,
                );
                $screen->add_help_tab( $args );
            }
        } 
        else 
        {
            add_contextual_help( $hook, $contextual_help );
        }
    }
    
    public static function route($path='', $params=null, $raw=true)
    {
        return ModMan_Ajax_Router::getRoute('modman', $path, $params, $raw);
    }

    public static function sanitizeTags($html)
    {
        // allow p,br,bold,italic and links
        $allowed_html=array(
            'a' => array(
                'href' => array(),
                'title' => array(),
                'target'=>array()
            ),
            'p' => array(),
            'br' => array(),
            'em' => array(),
            'i' => array(),
            'b' => array(),
            'strong' => array()
        );

        return wp_kses($html, $allowed_html);
    }

    public static function inlineElementGUI($element)
    {
        $mm_element=(object)array(
            'id'=>$element['id'],
            'title'=>$element['title'],
            'section'=>$element['section'],
            'description'=>isset($element['description'])?$element['description']:''
        );
        echo ModMan_Loader::tpl('inline', array(
            'element'=>$mm_element,
            'modules'=>ModMan_loader::get('MODEL/Modules')->getModules()
        ));
    }

    public static function exportModule($modulename, $ajax=true)
    {
        $modules=ModMan_Loader::get('MODEL/Modules')->getModules();

        if (!isset($modules[$modulename]))
        {
            return new WP_Error('module_not_exist', __('Module does nor exist', 'module-manager'));
        }

        $module=$modules[$modulename];
        $xmls=array();
        if (!isset($module[MODMAN_MODULE_INFO]))
            $module[MODMAN_MODULE_INFO]=array();
        if (!isset($module[MODMAN_MODULE_INFO]['description']))
            $module[MODMAN_MODULE_INFO]['description']='';
        if (!isset($module[MODMAN_MODULE_INFO]['name']))
            $module[MODMAN_MODULE_INFO]['name']=$modulename;

        foreach ($module as $section_id=>$items)
        {
            if ( $section_id!=MODMAN_MODULE_INFO && is_array($items) && !empty($items) )
            {
                // pass full items data, and let override
                /*foreach ($items as $ii=>$item)
                {
                    $items[$ii]=$item['id'];
                }*/
                // make sure script does not timeout, 2 mins
                set_time_limit(120);
                
                //Make sure all group ID are of correct format before exporting!
                if ($section_id=='groups') {               	

                		foreach ($items as $outer_key=>$group_item_values_array) {
                			
                			foreach ($group_item_values_array as $inner_key=>$inner_value) {
                				
                				if ($inner_key=='id') {
                					
                					//Test for correct format
                					$arbitrary_value=intval(str_replace('12'.$section_id.'21','',$inner_value));
                					
                					if (!($arbitrary_value)) {
                						
                						//Wrong format, correct it
                						$post_arbitrary = get_page_by_title($group_item_values_array['title'], OBJECT, 'wp-types-group' );
                					    
                					    //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187254315/comments#287007203
                						if (isset($post_arbitrary)) {
                							$group_id_db= $post_arbitrary->ID;
                							$items[$outer_key][$inner_key]='12'.$section_id.'21'.$group_id_db;  
                						}    						
                					}
                				
                				}
                			}
                			
                		}
                }
                $res=apply_filters('wpmodules_export_items_'.$section_id, array('xml'=>'', 'items'=>$items), $items);              

                if (is_array($res) && isset($res['xml']))
                {
                    if (!empty($res['xml']))
                        $xmls[$section_id]=$res['xml'];
                    if (!empty($res['items']))
                        $module[$section_id]=$res['items'];
                }
                elseif (is_string($res) && !empty($res)) // compatibility if only string returned
                {
                    $xmls[$section_id]=$res;
                }
                //EMERSON: Add hooks for adding plugin version
                
                $plugin_version_exported=apply_filters('wpmodules_export_pluginversions_'.$section_id,$section_id);
                
                if (!(empty($plugin_version_exported))) {

                	if ($plugin_version_exported!=$section_id) {

                		//Include in modules info array
                		$module[MODMAN_MODULE_INFO][$section_id.'_plugin_version']=$plugin_version_exported;
                	
                	}
                
                }

            }
        }
        // add module info also
        
        $xmls[MODMAN_MODULE_INFO]=serialize($module);

        self::exportModuleZIP($modulename, $xmls, $ajax);
    }

    private static function exportModuleZIP($modulename, $xmls, $ajax=true)
    {
        if (empty($xmls))
        {
            return new WP_Error('nothing_to_export', __('Nothing to export', 'module-manager'));
        }

        if (!class_exists('ZipArchive'))
        {
            return new WP_Error('zip_not_supported', __('PHP does not support ZipArchive class', 'module-manager'));
        }

        $modulename = sanitize_key($modulename);
        if (!empty($modulename))
        {
            $modulename .= '-';
        }

        $zipname = $modulename . date('Y-m-d') . '.zip';
        $zip = new ZipArchive();
        /*
        $tmp='tmp';
        // http://php.net/manual/en/function.tempnam.php#93256
        if (function_exists('sys_get_temp_dir'))
            $tmp=sys_get_temp_dir();
        */
        /*EMERSON, generate tmp files in WP uploads directory*/
        $uploads_directory=wp_upload_dir();
        $uploads_directory_basedir=$uploads_directory['basedir'];        
            
        $file = tempnam($uploads_directory_basedir, "zip");
        $zip->open($file, ZipArchive::OVERWRITE);

        foreach ($xmls as $xml=>$xmlstring)
        {
            if ($xml!==MODMAN_MODULE_INFO)
                $res = $zip->addFromString($xml.'.xml', $xmlstring);
            else // modman specific info
                $res = $zip->addFromString($xml, $xmlstring);
            unset($xmls[$xml]);
        }
        unset($xmls);
        $zip->close();

        $data = file_get_contents($file);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=" . $zipname);
        header("Content-Type: application/zip");
        header("Content-length: " . strlen($data) . "\n\n");
        header("Content-Transfer-Encoding: binary");
        if ($ajax) header("Set-Cookie: __ModManExportDownload=true; path=/");
        echo $data;
        unlink($file);
        die();
    }

    private static function generateTmpName()
    {
        return MODMAN_TMP_PATH.DIRECTORY_SEPARATOR.time().'.'.uniqid();
    }

    public static function importModuleStepByStep($step=0, array $params)
    {
        $DS=DIRECTORY_SEPARATOR;
        $info=array();
        $items=array();
        $module_name='';
        switch ($step)
        {
            case 1:
                if (isset($params['file']))
                {
                    $xmls=self::importModuleZIP($params['file']);
                    if (is_wp_error($xmls) || !is_array($xmls))
                    {
                        return new WP_Error('import_error', $xmls->get_error_message($xmls->get_error_code()));
                    }

                    $tmp_dir=ModuleManager::generateTmpName();

                    if (@mkdir($tmp_dir))
                    {
                        if (isset($xmls[MODMAN_MODULE_INFO]))
                        {
                            $info=$xmls[MODMAN_MODULE_INFO];
                            if (is_serialized($xmls[MODMAN_MODULE_INFO]))
                                $info=unserialize($xmls[MODMAN_MODULE_INFO]);
                            //$module_name=$info['name'];
                            // save tmp dir for future steps
                            $info[MODMAN_MODULE_TMP_FILE]=$tmp_dir;
                            // save module info file
                            file_put_contents($tmp_dir.$DS.MODMAN_MODULE_INFO, $xmls[MODMAN_MODULE_INFO]);
                        }
                        $sections=ModMan_Loader::get('MODEL/Modules')->getRegisteredSections();
                        foreach (array_keys($sections) as $section_id)
                        {
                            if (isset($xmls[$section_id]))
                            {
                                // save xml files
                                file_put_contents($tmp_dir.$DS.$section_id.'.xml', $xmls[$section_id]);

                                // get existing items
                                $items=$info[$section_id];
                                $items=apply_filters('wpmodules_items_check_'.$section_id, $items);
                                $info[$section_id]=$items;
                            }
                        }
                        return $info;
                    }
                    else
                    {
                        return new WP_Error('import_error', __('Could not create tmp module folder','module-manager'));
                    }
                    return new WP_Error('import_error', __('Unknown error','module-manager'));
                }
                return new WP_Error('import_error', __('No module file given', 'module-manager'));
                break;
            case 2:
                $results=array();
                $info=array();
                $has_errors=false;
                if (isset($params['info']))
                {
                    if (is_dir($params['info']))
                    {
                        $tmp_dir=$params['info'];
                        $info=unserialize(file_get_contents($tmp_dir.$DS.MODMAN_MODULE_INFO));
                    }
                    else
                    {
                        return new WP_Error('import_error', __('Tmp module folder does not exist', 'module-manager'));
                    }
               }
                else
                {
                    return new WP_Error('import_error', __('No module info given', 'module-manager'));
                }

                if (isset($params['items']))
                {
                    $items=$params['items'];
                }
                else
                {
                    return new WP_Error('import_error', __('No module items given', 'module-manager'));
                }

                // get sections
                $sections=ModMan_Loader::get('MODEL/Modules')->getRegisteredSections();

                // get xml files from prev step
                $xmls=array_diff(scandir($tmp_dir), array('.','..'));

                foreach (array_keys($sections) as $section_id)
                {
                    // if file exists
                    if (in_array($section_id.'.xml', $xmls) && isset($items[$section_id]))
                    {
                         // make sure script does not timeout, 2 mins
                        set_time_limit(120);
                        $results[$section_id] = apply_filters('wpmodules_import_items_'.$section_id, null, file_get_contents($tmp_dir.$DS.$section_id.'.xml'), array_keys($items[$section_id]), array() /* not ready yet */);

                        if (null!==$results[$section_id]  && !empty($results[$section_id]['errors']))
                        {
                            $has_errors=true;
                        }
                        elseif (null!==$results[$section_id] && is_array($results[$section_id]))
                        {
                            // import new items into module
                            if (isset($results[$section_id]['items']))
                            {
                                foreach ($info[$section_id] as $ii=>$_item_)
                                {
                                    // set new item id
                                    if (isset(
                                        $results[
                                            $section_id][
                                                'items'][
                                                    $info[
                                                        $section_id][
                                                            $ii][
                                                                'id']])
                                    )
                                        $info[$section_id][$ii]['id']=$results[$section_id]['items'][$info[$section_id][$ii]['id']];
                                    // remove the not-imported items from module
                                    else
                                        unset($info[$section_id][$ii]);
                                }
                                if (empty($info[$section_id]))
                                    unset($info[$section_id]);
                            }
                        }
                   }
                }
                
                if (!$has_errors)
                {
                    $imported_sections=array_merge(
                        array_keys($results),
                        array(MODMAN_MODULE_INFO)
                    );
                    // remove sections not imported
                    foreach ($info as $sect=>$sectdata)
                    {
                        if (!in_array($sect, $imported_sections))
                            unset($info[$sect]);
                    }
                    unset($imported_sections);
                }
                
                // remove tmp module folder
                @self::delTree($tmp_dir);

                // remove the tmp file variable from info
                unset($info[MODMAN_MODULE_TMP_FILE]);

                //if ($has_errors)
                    //return new WP_Error('import_errors', implode('<br />',$errors));
                if (!$has_errors)
                {
                    // add module as a new module
                    $model=ModMan_Loader::get('MODEL/Modules');
                    $model->addNewModule($info[MODMAN_MODULE_INFO]['name'], $info);
                }
                // add module info
                $results[MODMAN_MODULE_INFO]=$info[MODMAN_MODULE_INFO];
                return $results;
                break;
            default:
                return new WP_Error('import_error', __('Wrong step', 'module-manager'));
                break;
        }
    }

    public static function importModule($file_path)
    {
        $xmls=self::importModuleZIP(array('name'=>$file_path,'tmp_name'=>$file_path));
        if (is_wp_error($xmls) || !is_array($xmls))
        {
            return array($xmls->get_error_message($xmls->get_error_code()));
        }
        if (!isset($xmls[MODMAN_MODULE_INFO]))
        {
            return array(__('Module Information does not exist in file', 'module-manager'));
        }
        
        $results=array();
        $info=maybe_unserialize($xmls[MODMAN_MODULE_INFO]);
        $has_errors=false;
        
        // get sections
        $sections=ModMan_Loader::get('MODEL/Modules')->getRegisteredSections();

        foreach (array_keys($sections) as $section_id)
        {
            // if file exists
            if (isset($xmls[$section_id]) && isset($info[$section_id]))
            {
                $all_items=array();
                foreach ($info[$section_id] as $ii=>$item)
                {
                    $all_items[]=$item['id'];
                }
                
                // make sure script does not timeout, 2 mins
                set_time_limit(120);
                $results[$section_id] = apply_filters('wpmodules_import_items_'.$section_id, null, $xmls[$section_id], $all_items, array() /* not ready yet */);

                if (null!==$results[$section_id]  && !empty($results[$section_id]['errors']))
                {
                    $has_errors=true;
                }
                elseif (null!==$results[$section_id] && is_array($results[$section_id]))
                {
                    // import new items into module
                    if (isset($results[$section_id]['items']))
                    {
                        foreach ($info[$section_id] as $ii=>$_item_)
                        {
                            // set new item id
                            if (isset(
                                $results[
                                    $section_id][
                                        'items'][
                                            $info[
                                                $section_id][
                                                    $ii][
                                                        'id']])
                            )
                                $info[$section_id][$ii]['id']=$results[$section_id]['items'][$info[$section_id][$ii]['id']];
                            // remove the not-imported items from module
                            else
                                unset($info[$section_id][$ii]);
                        }
                        if (empty($info[$section_id]))
                            unset($info[$section_id]);
                    }
                }
           }
        }
        
        if (!$has_errors)
        {
            $imported_sections=array_merge(
                array_keys($results),
                array(MODMAN_MODULE_INFO)
            );
            // remove sections not imported
            foreach ($info as $sect=>$sectdata)
            {
                if (!in_array($sect, $imported_sections))
                    unset($info[$sect]);
            }
            unset($imported_sections);
        }
        
        // remove the tmp file variable from info
        if (isset($info[MODMAN_MODULE_TMP_FILE]))
            unset($info[MODMAN_MODULE_TMP_FILE]);

        if (!$has_errors)
        {
            // add module as a new module
            $model=ModMan_Loader::get('MODEL/Modules');
            $model->addNewModule($info[MODMAN_MODULE_INFO]['name'], $info);
        }
        if ($has_errors)
        {
            $errors=array();
            foreach ($results as $sc=>$data)
            {
                if (isset($data['errors']))
                    $errors=array_merge($errors, $data['errors']);
            }
            return $errors;
        }
        return true;
    }

    private static function importModuleZIP($file)
    {
        $xmls = array();
        $not_one_xml=true;
        $info = pathinfo($file['name']);
        $is_zip = $info['extension'] == 'zip' ? true : false;
        if ($is_zip)
        {
            if (!function_exists('zip_open'))
            {
                return new WP_Error('zip_not_supported', __('PHP does not support zip_open function', 'module-manager'));
            }

            $zip = zip_open(urldecode($file['tmp_name']));
            if (is_resource($zip))
            {
                while($zip_entry = zip_read($zip))
                {
                    if (is_resource($zip_entry) /*&& zip_entry_open($zip, $zip_entry)*/)
                    {
                        $entry_name = zip_entry_name($zip_entry);
                        $zip_entry_info=pathinfo($entry_name);
                        if (isset($zip_entry_info['filename']))
                        {
                            if (isset($zip_entry_info['extension']) && 'xml'==$zip_entry_info['extension'])
                            {
                                $not_one_xml=false;
                                $data = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                                zip_entry_close ( $zip_entry );
                                $xmls[$zip_entry_info['filename']] = $data;
                            }
                            elseif (MODMAN_MODULE_INFO==$zip_entry_info['filename'])
                            {
                                $data = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                                zip_entry_close ( $zip_entry );
                                $xmls[$zip_entry_info['filename']] = $data;
                            }
                        }
                    }
                    else
                        return new WP_Error('could_not_open_file', __('No zip entry', 'module-manager'));
                }

                if ($not_one_xml)
                {
                    return new WP_Error('no_xml', __('No xml files in .zip.','module-manager'));
                }
                else
                {
                    return $xmls;
                }
            }
            else
            {
                return new WP_Error('could_not_open_file', __('Unable to open .zip file', 'module-manager'));
            }
        }
        else
        {
            return new WP_Error('file_not_zip_format', __('File is not .zip format', 'module-manager'));
        }

        return new WP_Error('unknown error', __('Unknown error during import','module-manager'));
    }

    // setup necessary DB model settings
    private static function prepareDB()
    {
        $modules_model = ModMan_Loader::get('MODEL/Modules');
        $modules_model->prepareDB();
    }

    private static function delTree($dir)
    {
        $DS=DIRECTORY_SEPARATOR;
        if (is_dir($dir)) {
        	$files = array_diff(scandir($dir), array('.','..'));
        	foreach ($files as $file)
        	{
         	   if (is_dir("${dir}${DS}${file}")) self::delTree("${dir}${DS}${file}");
         	   if (is_file("${dir}${DS}${file}")) @unlink("${dir}${DS}${file}");
        	}
        	return rmdir($dir);
		}
    }

    private static function purgeTmps()
    {
        $DS=DIRECTORY_SEPARATOR;
        $lock=MODMAN_TMP_PATH.$DS.MODMAN_TMP_LOCK;
        if (is_file($lock))
            return; // lock in progress
        // mutex pattern
        touch($lock);
        $dirs=array_diff(scandir(MODMAN_TMP_PATH), array('.','..'));
        foreach ($dirs as $dir)
        {
            $dtime=intval(substr($dir,0,strpos($dir,'.')+1));
            if ($dtime+MODMAN_PURGE_TIME < time())
            {
                @self::delTree(MODMAN_TMP_PATH.$DS.$dir);
            }
        }
        @unlink($lock); // clear mutex
    }

    // setup necessary folders
    private static function prepareFolders()
    {
        if (!is_dir(MODMAN_TMP_PATH))
        {
            if (! @mkdir(MODMAN_TMP_PATH, 0700))
            {
                self::$messages[]=__( 'Could not create TMP folder with necessary permissions', 'module-manager' );
                return false;
            }
            // create htacces if needed
            if (!is_file(MODMAN_TMP_PATH.'/.htaccess'))
            {
                $htaccess=array(
                    "order deny, allow",
                    "deny from all"
                );
                file_put_contents(MODMAN_TMP_PATH.'/.htaccess', implode(PHP_EOL, $htaccess));
            }
        }
        else
        {
            // create htacces if needed
            if (!is_file(MODMAN_TMP_PATH.'/.htaccess'))
            {
                $htaccess=array(
                    "order deny, allow",
                    "deny from all"
                );
                file_put_contents(MODMAN_TMP_PATH.'/.htaccess', implode(PHP_EOL, $htaccess));
            }
            @self::purgeTmps();
        }
    }
}
