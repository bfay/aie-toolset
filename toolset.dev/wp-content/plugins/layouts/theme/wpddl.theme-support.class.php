<?php

add_action( 'init', 'init_layouts_theme_support', 9 );

function init_layouts_theme_support()
{
	global $wpddlayout_theme;
	$wpddlayout_theme = new WPDD_Layouts_Theme();
}

class WPDD_Layouts_Theme {
	function __construct(){
		$this->file_manager_export = new WPDD_FileManager('/theme-dd-layouts', 'wp_nonce_export_layouts_to_theme');
        
        if ( is_admin() ) {
			if (isset($_GET['page']) && $_GET['page']=='dd_layout_theme_export') {
				add_action('wp_loaded', array($this, 'export_and_download_layouts'));
			}
            
			add_action('admin_menu', array($this, 'add_layouts_admin_menu'), 11);
            
        }
    }
    
	function export_layouts_to_theme($target_dir) {
		global $wpdb, $wpddlayout;

		$results = array();

		$layouts =  $wpdb->get_results("SELECT ID, post_name, post_title FROM {$wpdb->posts} WHERE post_type='dd_layouts'");

		foreach ($layouts as $layout) {

			$layout_json = $wpddlayout->get_layout_settings($layout->ID);

			$results[] = $this->file_manager_export->save_file( $layout->post_name, '.ddl', $layout_json, array('title' => $layout->post_title), true );
		}

		$css = $this->get_layout_css();

		if( $css )
		{
			$results[] = $this->file_manager_export->save_file( 'layouts', '.css', $css, array('title' => 'Layouts CSS'), true );
		}

		return $results;
	}

	function get_layout_css()
	{
		global $wpddlayout;
		return $wpddlayout->get_layout_css();
	}
    
	function export_for_download() {
		global $wpdb, $wpddlayout;

		$results = array();

		$layouts =  $wpdb->get_results("SELECT ID, post_name, post_title FROM {$wpdb->posts} WHERE post_type='dd_layouts'");

		foreach ($layouts as $layout) {

			$layout_json = $wpddlayout->get_layout_settings($layout->ID);

			$file_name = $layout->post_name . '.ddl';

			$results[] = array(
				'file_data' => $layout_json,
				'file_name' => $file_name,
				'title' => $layout->post_title,
			);
		}

		$css = $this->get_layout_css();

		if( $css )
		{
			$results[] = array(
				'file_data' => $this->get_layout_css(),
				'file_name' => 'layouts.css',
				'title' => 'Layouts CSS',
			);
		}

		return $results;
	}
    
	function export_and_download_layouts() {
		if (isset($_POST['export_and_download'])) {

			$nonce = $_POST["wp_nonce_export_layouts"];

			if ( wp_verify_nonce( $nonce, 'wp_nonce_export_layouts' ) ) {
				$results = $this->export_for_download();

				$sitename = sanitize_key(get_bloginfo('name'));
				if (!empty($sitename)) {
					$sitename .= '.';
				}
				if (class_exists('ZipArchive')) {
					$zipname = $sitename . 'dd-layouts.' . date('Y-m-d') . '.zip';
					$zip = new ZipArchive();
					$file = tempnam(sys_get_temp_dir(), "zip");
					$zip->open($file, ZipArchive::OVERWRITE);

					foreach ($results as $file_data) {
						$zip->addFromString($file_data['file_name'], $file_data['file_data']);
					}
					$zip->close();
					$data = file_get_contents($file);
					header("Content-Description: File Transfer");
					header("Content-Disposition: attachment; filename=" . $zipname);
					header("Content-Type: application/zip");
					header("Content-length: " . strlen($data) . "\n\n");
					header("Content-Transfer-Encoding: binary");
					echo $data;
					unlink($file);
				}
			}
			die();
		}
	}
    
	function add_layouts_admin_menu() {
    
		add_submenu_page('dd_layouts', __('Theme export', 'ddl-layouts'), __('Theme export', 'ddl-layouts'), 'manage_options', 'dd_layout_theme_export', array($this, 'dd_layouts_theme_export'));
    }
    
	function dd_layouts_theme_export(){
		include dirname(__FILE__) . '/templates/layout_theme_export.tpl.php';
	}
}