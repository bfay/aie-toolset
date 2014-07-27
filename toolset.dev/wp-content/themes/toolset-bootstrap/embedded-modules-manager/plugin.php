<?php

// current version
if (!(defined('MODMAN_VERSION'))) {
define('MODMAN_VERSION','1.4');
}

if (!(defined('MODMAN_NAME'))) {
define('MODMAN_NAME','MODMAN');
}

if (!(defined('MODMAN_CAPABILITY'))) {
define('MODMAN_CAPABILITY','manage_options');
}

if (!(defined('MODMAN_PLUGIN_PATH'))) {
if ( function_exists('realpath') )
    define('MODMAN_PLUGIN_PATH', realpath(dirname(__FILE__)));
else
    define('MODMAN_PLUGIN_PATH', dirname(__FILE__));
}

if (!(defined('MODMAN_PLUGIN'))) {
define('MODMAN_PLUGIN', plugin_basename(__FILE__));
}

if (!(defined('MODMAN_PLUGIN_FOLDER'))) {
define('MODMAN_PLUGIN_FOLDER', basename(MODMAN_PLUGIN_PATH));
}

if (!(defined('MODMAN_PLUGIN_NAME'))) {
define('MODMAN_PLUGIN_NAME',MODMAN_PLUGIN_FOLDER.'/'.basename(__FILE__));
}

if (!(defined('MODMAN_PLUGIN_BASENAME'))) {
define('MODMAN_PLUGIN_BASENAME',MODMAN_PLUGIN);
}

//Define correct URL with embedded MM implementation
if (!(defined('MODMAN_PLUGIN_URL'))) {
if (defined('MODMAN_RUN_MODE')) {
	define('MODMAN_PLUGIN_URL',plugins_url().'/'.MODMAN_PLUGIN_FOLDER);
} else {	
	define('MODMAN_PLUGIN_URL',get_template_directory_uri().'/'.MODMAN_PLUGIN_FOLDER);	
}
}

if (!(defined('MODMAN_ASSETS_URL'))) {
define('MODMAN_ASSETS_URL',MODMAN_PLUGIN_URL.'/assets');
}

if (!(defined('MODMAN_ASSETS_PATH'))) {
define('MODMAN_ASSETS_PATH',MODMAN_PLUGIN_PATH.'/assets');
}

if (!(defined('MODMAN_VIEWS_PATH'))) {
define('MODMAN_VIEWS_PATH',MODMAN_PLUGIN_PATH.'/views');
}

if (!(defined('MODMAN_TEMPLATES_PATH'))) {
define('MODMAN_TEMPLATES_PATH',MODMAN_PLUGIN_PATH.'/views/templates');
}

if (!(defined('MODMAN_CLASSES_PATH'))) {
define('MODMAN_CLASSES_PATH',MODMAN_PLUGIN_PATH.'/classes');
}

if (!(defined('MODMAN_COMMON_PATH'))) {
define('MODMAN_COMMON_PATH',MODMAN_PLUGIN_PATH.'/common');
}

if (!(defined('MODMAN_TABLES_PATH'))) {
define('MODMAN_TABLES_PATH',MODMAN_PLUGIN_PATH.'/views/tables');
}

if (!(defined('MODMAN_CONTROLLERS_PATH'))) {
define('MODMAN_CONTROLLERS_PATH',MODMAN_PLUGIN_PATH.'/controllers');
}

if (!(defined('MODMAN_MODELS_PATH'))) {
define('MODMAN_MODELS_PATH',MODMAN_PLUGIN_PATH.'/models');
}

if (!(defined('MODMAN_LOGS_PATH'))) {
define('MODMAN_LOGS_PATH',MODMAN_PLUGIN_PATH.'/logs');
}

if (!(defined('MODMAN_LOCALE_PATH'))) {
define('MODMAN_LOCALE_PATH',MODMAN_PLUGIN_FOLDER.'/locale');
}

if (!(defined('MODMAN_LIBRARYCLASS_PATH'))) {
//Define library class path
define('MODMAN_LIBRARYCLASS_PATH',MODMAN_PLUGIN_PATH.'/library');
}

if (!(defined('MODMAN_LIBRARYXML_PATH'))) {
//Define modules library XML download path
define('MODMAN_LIBRARYXML_PATH','http://ref.wp-types.com/_wpv_demo/demos-index-modules.xml');
}

if (!(defined('MODMAN_ORIGINATING_HOST'))) {
	//Define modules library XML download path
	define('MODMAN_ORIGINATING_HOST','ref.wp-types.com');
}

// save temp module zips
if (!(defined('MODMAN_TMP_PATH'))) {
define('MODMAN_TMP_PATH',WP_CONTENT_DIR.'/_modulemanager_tmp_');
}

if (!(defined('MODMAN_TMP_LOCK'))) {
define('MODMAN_TMP_LOCK','______lock_____');
}

// clear all tmps after this time
if (!(defined('MODMAN_PURGE_TIME'))) {
define('MODMAN_PURGE_TIME', 86400); // 24 hours
}

if (!(defined('MODMAN_MODULE_INFO'))) {
define('MODMAN_MODULE_INFO','__module_info__');
}

if (!(defined('MODMAN_MODULE_TMP_FILE'))) {
define('MODMAN_MODULE_TMP_FILE','__module_tmp_file__');
}

// load on the go resources
require_once MODMAN_PLUGIN_PATH . '/onthego-resources/onthegosystems-branding-loader.php';
ont_set_on_the_go_systems_uri_and_start( MODMAN_PLUGIN_URL. '/onthego-resources/' );

/*
if (!(defined('MODMAN_DEBUG'))) {
define('MODMAN_DEBUG',true);
}

if (!(defined('MODMAN_DEV'))) {
define('MODMAN_DEV',true);
}
*/
	
// logging function
if (!function_exists('modman_log'))
{
if (defined('MODMAN_DEBUG')&&MODMAN_DEBUG)
{
    function modman_log($message, $file=null, $type=null, $level=1)
    {
        // debug levels
        $dlevels=array(
            'default' => defined('MODMAN_DEBUG') && MODMAN_DEBUG
        );

        // check if we need to log..
        if (!$dlevels['default']) return false;
        if ($type==null) $type='default';
        if (!isset($dlevels[$type]) || !$dlevels[$type]) return false;
        
        // full path to log file
        if ($file==null)
        {
            $file='debug.log';
        }
        $file=MODMAN_LOGS_PATH.DIRECTORY_SEPARATOR.$file;

        /* backtrace */
        $bTrace = debug_backtrace(); // assoc array

        /* Build the string containing the complete log line. */
        $line = PHP_EOL.sprintf('[%s, <%s>, (%d)]==> %s', 
                                date("Y/m/d h:i:s", mktime()),
                                basename($bTrace[0]['file']), 
                                $bTrace[0]['line'], 
                                print_r($message,true) );
        
        if ($level>1)
        {
            $i=0;
            $line.=PHP_EOL.sprintf('Call Stack : ');
            while (++$i<$level && isset($bTrace[$i]))
            {
                $line.=PHP_EOL.sprintf("\tfile: %s, function: %s, line: %d".PHP_EOL."\targs : %s", 
                                    isset($bTrace[$i]['file'])?basename($bTrace[$i]['file']):'(same as previous)', 
                                    isset($bTrace[$i]['function'])?$bTrace[$i]['function']:'(anonymous)', 
                                    isset($bTrace[$i]['line'])?$bTrace[$i]['line']:'UNKNOWN',
                                    print_r($bTrace[$i]['args'],true));
            }
            $line.=PHP_EOL.sprintf('End Call Stack').PHP_EOL;
        }
        // log to file
        file_put_contents($file,$line,FILE_APPEND);
        
        return true;
    }
}
else
{
    function modman_log()  { }
}
}

// <<<<<<<<<<<< includes --------------------------------------------------
include(MODMAN_PLUGIN_PATH.'/loader.php');
// include basic classes
ModMan_Loader::load('CLASS/ModuleManager');
// init
ModuleManager::init();