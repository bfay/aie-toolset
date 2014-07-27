<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 */

function optionsframework_option_name() {

	// This gets the theme name from the stylesheet
	$themename = get_option( 'stylesheet' );
	$themename = preg_replace("/\W/", "_", strtolower($themename) );

	$optionsframework_settings = get_option( 'optionsframework' );
	$optionsframework_settings['id'] = $themename;
	update_option( 'optionsframework', $optionsframework_settings );
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 */

function optionsframework_options() {

	// If using image radio buttons, define a directory path
	$imagepath =  get_template_directory_uri() . '/theme-options/inc/images/';

	$options = array();

	// WP-Bootstrap general settings
	$options[] = array(
		'name' => 'General settings',
		'translation' => __('General settings', 'wpbootstrap'),
		'type' => 'heading'
	);

	// WP-Bootstrap general settings
	$generalsettings_array = array(
		'display_header_site_title' =>		__( 'Enable site title and tagline', 'wpbootstrap' ),
		'display_header_nav' =>				__( 'Enable top menu', 'wpbootstrap' ),
		'display_sidebar' =>				__( 'Enable sidebar', 'wpbootstrap' ),
		'display_header_widgets' =>			__( 'Enable header widgets area', 'wpbootstrap' ),
		'display_footer_widgets' =>			__( 'Enable footer widgets area', 'wpbootstrap' ),
		'display_footer' =>					__( 'Enable footer', 'wpbootstrap' ),
		'display_comments' =>				__( 'Enable comments', 'wpbootstrap' ),
		'display_comments_closed_info' =>	__( 'Enable "Comments are closed" message', 'wpbootstrap' ),
		'display_postmeta' =>				__( 'Enable post meta data section for posts', 'wpbootstrap' ),
		'display_postmeta_cpt' =>			__( 'Enable post meta data section in custom post types', 'wpbootstrap' ),
		'display_thumbnails' =>				__( 'Enable post thumbnails', 'wpbootstrap' )
	);

	// WP-Bootstrap general settings defaults
	$generalsettings_defaults = array(
		'display_header_site_title' => '1',
		'display_header_nav' => '1',
		'display_sidebar' => '1',
		'display_header_widgets' => '1',
		'display_footer_widgets' => '1',
		'display_footer' => '1',
		'display_comments' => '1',
		'display_comments_closed_info'=> '0',
		'display_postmeta' => '1',
		'display_postmeta_cpt' => '1',
		'display_thumbnails' => '1',
		'favicon' => get_stylesheet_directory().'favicon.ico'
	);

	$options[] = array(
		'name' => __('Favicon', 'wpbootstrap'),
		'desc' => null,
		'id' => 'favicon',
		'type' => 'upload',
        'desc' =>__('Your favicon should be an .ico file type and normally 16 x 16 pixels.  Read more about','wpbootstrap').' <a target="_blank" href="http://codex.wordpress.org/Creating_a_Favicon">'.__('creating a Favicon','wpbootstrap').'</a>.'
	);

	$options[] = array(
		'name' => __( 'Select which page sections are displayed', 'wpbootstrap' ),
		'id' => 'general_settings',
		'std' => $generalsettings_defaults,
		'type' => 'multicheck',
		'options' => $generalsettings_array,
	);

	$options[] = array(
		"name" => __('Display a credit footer','wpbootstrap'),
		"id" => "display_credit_footer",
		"std" => '1',
		"type" => "checkbox"

	);
	$options[] = array(
		"name" =>  __( 'Left side', 'wpbootstrap' ),
		"id" => "display_credit_footer_left",
		"std" => "<a href='http://wp-types.com/home/toolset-bootstrap/'>". __('Toolset Bootstrap','wpbootstrap') ."</a>",
		"type" => "textarea",
	);

	$options[] = array(
		"name" => __( 'Right side', 'wpbootstrap' ),
		"id" => "display_credit_footer_right",
		"std" => __( 'OnTheGo Systems', 'wpbootstrap' ),
		"type" => "text"

	);
	// WP-Bootstrap Page titles tab
	$options[] = array(
		'name' => 'Page titles', 'wpbootstrap',
		'translation' => __('Page titles', 'wpbootstrap'),
		'type' => 'heading'
	);

	// WP-Bootstrap page titles
	$displaytitles_array = array(
		'display_single_post_titles' =>		__( 'Page title for single posts', 'wpbootstrap' ),
		'display_single_post_titles_cpt' =>	__( 'Page title for custom post types', 'wpbootstrap' ),
		'display_pages_titles' =>			__( 'Page title', 'wpbootstrap' ),
		'display_pages_titles_on_homepage' =>__( 'Homepage page titles', 'wpbootstrap' ),			
		'display_home_post_titles' =>		__( 'Homepage blog posts titles', 'wpbootstrap' ),
		'display_archives_post_titles' =>	__( 'Archive posts titles', 'wpbootstrap' ),
		'display_archives_header' =>		__( 'Archive page title', 'wpbootstrap' ),
		'display_search_post_titles' =>		__( 'Search results posts titles', 'wpbootstrap' ),
		'display_search_header' =>			__( 'Search results page title', 'wpbootstrap' ),
		'display_categories_post_titles' =>	__( 'Category posts titles', 'wpbootstrap' ),
		'display_categories_header' =>		__( 'Categories page title', 'wpbootstrap' ),
		'display_tags_post_titles' =>		__( 'Tag posts titles', 'wpbootstrap' ),
		'display_tags_header' =>			__( 'Tags page title', 'wpbootstrap' ),
	);

	// WP-Bootstrap page titles defaults
	$displaytitles_defaults = array(
		'display_single_post_titles' => '1',
		'display_single_post_titles_cpt' => '1',
		'display_pages_titles' => '1',
		'display_pages_titles_on_homepage' =>'1',
		'display_home_post_titles' => '1',
		'display_archives_post_titles' => '1',
		'display_archives_header' => '1',
		'display_search_post_titles' => '1',
		'display_search_header' => '1',
		'display_categories_post_titles' => '1',
		'display_categories_header' => '1',
		'display_tags_post_titles' => '1',
		'display_tags_header' => '1',
	);

	$options[] = array(
		'name' => __( 'Select which page titles are displayed', 'wpbootstrap' ),
		'id' => 'titles_settings',
		'std' => $displaytitles_defaults,
		'type' => 'multicheck',
		'options' => $displaytitles_array,
		'desc' => __('You can control if the theme displays different an H1 titles for pages. When enabled, these different pages will have title outputs. If not, no H1 title is displayed automatically, allowing you to output your own titles.','wpbootstrap')
	);

	// WP-Bootstrap Menu settings tab
	$options[] = array(
		'name' => 'Menu settings',
		'translation'=>__( 'Menu settings', 'wpbootstrap' ),
		'type' => 'heading'
	);

	// WP-Bootstrap menu defaults
	$menu_array = array(
		'menu_static' => __('Static top navbar','wpbootstrap'),
		'menu_fixed_top' => __('Fixed top navbar','wpbootstrap'),
		'menu_fixed_bottom' => __('Fixed bottom navbar','wpbootstrap')
	);

	// WP-Bootstrap menu settings
	$options[ ] = array(
		'name' => __( 'Navbar position', 'wpbootstrap' ),
		'id' => 'navbar_style',
		'std' => 'menu_static',
		'type' => 'select',
		'options' => $menu_array
	);

	// Display navbar site title
	$options[ ] = array(
		'name' => __( 'Display site title', 'wpbootstrap' ),
		'desc' => __( 'Show site title next to nav menu items', 'wpbootstrap' ),
		'id' => 'navbar_title',
		'std' => '0',
		'type' => 'checkbox',
	);

	// Display navbar site title
	$options[ ] = array(
		'name' => __( 'Display search form', 'wpbootstrap' ),
		'desc' => __( 'Show search form inside the navbar', 'wpbootstrap' ),
		'id' => 'navbar_search',
		'std' => '0',
		'type' => 'checkbox'
	);

	// Alternative colors for navbar
	$options[ ] = array(
		'name' => __( 'Alternative navbar style', 'wpbootstrap' ),
		'desc' => __( 'Display navbar in inverted colors', 'wpbootstrap' ),
		'id' => 'navbar_inverted',
		'std' => '0',
		'type' => 'checkbox'
	);

	// WP-Bootstrap sidebar settings tab
	$options[ ] = array(
		'name' =>'Sidebar/Widgets',
		'translation'=>__( 'Sidebar/Widgets', 'wpbootstrap' ),
		'type' => 'heading'
	);

	// WP-Bootstrap sidebar defaults

	$widths_array = array(
		'1' => '1 '.__('column','wpbootstrap').' = 70px',
		'2' => '2 '.__('columns','wpbootstrap').' = 170px',
		'3' => '3 '.__('columns','wpbootstrap').' = 270px',
		'4' => '4 '.__('columns','wpbootstrap').' = 370px',
		'5' => '5 '.__('columns','wpbootstrap').' = 470px',
		'6' => '6 '.__('columns','wpbootstrap').' = 570px',
		'7' => '7 '.__('columns','wpbootstrap').' = 670px',
		'8' => '8 '.__('columns','wpbootstrap').' = 770px',
		'9' => '9 '.__('columns','wpbootstrap').' = 870px',
		'10' =>'10 '.__('columns','wpbootstrap').' = 970px',
		'11' =>'11 '.__('columns','wpbootstrap').' = 1070px'
	);

	// WP-Bootstrap sidebar settings
	$options[ ] = array(
		'name' => __( 'Sidebar width', 'wpbootstrap' ),
		'id' => 'sidebar_width',
		'std' => '4',
		'type' => 'select',
		'options' => $widths_array,
		'desc' => __('Page width = sidebar width + content width. Changing sidebar width will also affect content width.','wpbootstrap')
	);

	$widths_array['12'] = '12 '.__('columns','wpbootstrap').' = 1170px';

	$options[ ] = array(
		'name' => __( 'Header widget width', 'wpbootstrap' ),
		'id' => 'header_widget_width',
		'std' => '4',
		'type' => 'select',
		'options' => $widths_array,
		'desc' => __('Width of the single header widget.','wpbootstrap')
	);
	$options[ ] = array(
		'name' => __( 'Footer widget width', 'wpbootstrap' ),
		'id' => 'footer_widget_width',
		'std' => '4',
		'type' => 'select',
		'options' => $widths_array,
		'desc' => __('Width of the single footer widget.','wpbootstrap')
	);

	/*Customize sidebar display for post types*/
	$post_type_array = array();
	foreach (WPBT_theme::wpbootstrap_cpt_support() as $post_type) {
		$obj = get_post_type_object($post_type);
		$post_type_array[$obj->name]=$obj->labels->name;
	}

	foreach ($post_type_array as $value) {
		$post_type_array_sidebar_disable[$value] = '0';
	}

	$options[ ] = array(
			'name' => __( 'Disable sidebar for the following post types:', 'wpbootstrap' ),
			'id' => 'sidebar_bar_enabling_bootstrap',
			'std' => $post_type_array_sidebar_disable,
			'type' => 'multicheck',
			'options' => $post_type_array,
			'desc' => __('You can control which post types the sidebar will be available for','wpbootstrap')
	);

	$post_type_array_syntax = array();
	foreach (WPBT_theme::wpbootstrap_cpt_support() as $post_type) {
		$obj = get_post_type_object($post_type);
		$post_type_array_syntax[$obj->name]=$obj->labels->name;
	}
	if (isset($post_type_array_syntax['product'])) {
		//WooCommerce is activated before activating the theme
		//Don't include products in syntax editor
		unset($post_type_array_syntax['product']);
	}

    // WP-Bootstrap highlighting settings tab
	$options[ ] = array(
		'name' => 'Syntax Highlight',
		'translation'=>__( 'Syntax Highlight', 'wpbootstrap' ),
		'type' => 'heading'
	);

	$options[ ] = array(
		'name' => __( 'Enable syntax highlighting for:', 'wpbootstrap' ),
		'id' => 'post_type_highlighting',
		'std' => $post_type_array_syntax,
		'type' => 'multicheck',
		'options' => $post_type_array_syntax,
		'desc' => __('You can control which post types the syntax highlighting will be available for','wpbootstrap')
	);

	foreach ($post_type_array as $value) {
		$post_type_array_data[$value] = '0';
	}

	$options[ ] = array(
		'name' => __( 'Disable Visual Editor for:', 'wpbootstrap' ),
		'id' => 'post_type_editor_no_highlighting',
		'std' => $post_type_array_data,
		'type' => 'multicheck',
		'options' => $post_type_array_syntax,
		'desc' => __('You can control which post types the visual editor will be available for','wpbootstrap')
	);

	/*EMERSON start*/
	$loading_settings_array = array(
		'loading_from_theme' => __('Load from theme CSS files','wpbootstrap'),
		'loading_from_database_css'=> __('Save CSS to database and load it for editing','wpbootstrap')
	);

	$options[ ] = array(
			'name' => 'CSS',
			'translation'=>__( 'CSS', 'wpbootstrap' ),
			'type' => 'heading'
	);

	$options[ ] = array(
			'name' => __( 'Select the CSS loading method:', 'wpbootstrap' ),
			'id' => 'css_loading_method_selected_user',
			'std' => 'loading_from_theme',
			'type' => 'radio',
			'options' => $loading_settings_array,
			'desc' => __('You can choose whether to load CSS from theme files or save to dB for easy editing.','wpbootstrap')
	);

	$options[] = array(
			"name" =>  __( 'CSS code:', 'wpbootstrap' ),
			"id" => "css_loading_method_selected_user_textarea",
			"std" => wpbootstrap_copy_css_from_theme_dir_to_db(),
			"type" => "textarea"

	);

	/*EMERSON end*/

    // WP-Bootstrap Layouts settings tab
	$options[ ] = array(		
		'name' => 'Layouts',
		'translation'=>__( 'Layouts', 'wpbootstrap' ),
		'type' => 'heading'
	);

	$options[ ] = array(
		'name' => __( 'List of available layouts', 'wpbootstrap' ),
		'id' => 'grid_list_xml',
		'type' => 'info',
		'desc' => WPBT_grid_xml::wpbootstrap_grid_list()
	);

    $options[ ] = array(
		'name' => __( 'Upload grid', 'wpbootstrap' ),
		'id' => 'grid_list_xml_file',
		'type' => 'upload',
		'desc' =>__('Upload xml file with layouts grid.', 'wpbootstrap')
	);

	$options[ ] = array(
		'id' => 'grid_list_info',
		'type' => 'info',
		'desc' =>
				'
				<p>
					<a href="?page=options-framework&restore_grid_to_default" class="button-secondary" type="button">'.__('Restore grid to default', 'wpbootstrap').'</a>
				</p>
				<p>'.__( 'Learn more about', 'wpbootstrap').'
					<a href="http://wp-types.com/documentation/user-guides/create-and-upload-custom-grid-layouts/">'.__( 'creating and uploading custom grid layouts', 'wpbootstrap' ).'</a>
				</p>
				'
    );

	return $options;

}

/*
 * Add custom scripts to the options panel.
 */

add_action( 'optionsframework_custom_scripts', 'optionsframework_custom_scripts' );

function optionsframework_custom_scripts() {

	// This gets the theme name from the stylesheet
	$themename_input = get_option( 'stylesheet' );
	$themename_input = preg_replace("/\W/", "_", strtolower($themename_input) );
	$themename_input=trim($themename_input);

?>

	<script type="text/javascript">
		jQuery(document).ready(function($) {

			var $navManuTab = $('#of-option-menusettings-tab');
			var $widgetsMenuTab = $('#of-option-widgetssettings-tab');
			var $navMenuCheckbox = $('#<?php echo $themename_input; ?>-general_settings-display_header_nav');
			var $widgetsMenuCheckbox = $('#<?php echo $themename_input; ?>-general_settings-display_sidebar,#<?php echo $themename_input; ?>-general_settings-display_header_widgets,#<?php echo $themename_input; ?>-general_settings-display_footer_widgets');

	        var go_to_tab = window.location.hash.replace("#","");
	        if (go_to_tab){
	            $('#'+go_to_tab+'-tab').click();
	        }

			$widgetsMenuTab.hide();
			//$navManuTab.hide();
			if ( $navMenuCheckbox.is(':checked')) {
				$navManuTab.show();
			}
			if ( $widgetsMenuCheckbox.is(':checked')) {
				$widgetsMenuTab.show();
			}

			$navMenuCheckbox.click(function() {
				if( $(this).is(':checked') ) {
					$navManuTab.fadeIn(100);
				}
				else {
					$navManuTab.fadeOut(100);
				}
			});

			$widgetsMenuCheckbox.click(function() {
				if( $widgetsMenuCheckbox.is(':checked') ) {
					$widgetsMenuTab.fadeIn(100);
				}
				else {
					$widgetsMenuTab.fadeOut(100);
				}
			});

			// WP-Pointers
			var templateDir = "<?php bloginfo('template_directory') ?>";
			var $MenuSettingsinputs = [
				$('#navbar_title'),
				$('#navbar_search'),
				$('#navbar_inverted')
			];
			var $GeneralSettingsinputs = {
				'headerSiteTitle' : {
					'el' : $('#<?php echo $themename_input; ?>-general_settings-display_header_site_title'),
					'text': '<?php echo esc_js(__('When enabled, the theme will include a site title and a site tagline in the header.','wpbootstrap')); ?>'
				},
				'headerNav' : {
					'el' : $('#<?php echo $themename_input; ?>-general_settings-display_header_nav'),
					'text': '<?php echo esc_js(__('When enabled, the theme will include a top menu. You can change top menu settings in the Menu settings tab in this admin screen.','wpbootstrap')); ?>'
				},
				'sidebar' : {
					'el': $('#<?php echo $themename_input; ?>-general_settings-display_sidebar'),
					'text' : '<?php echo esc_js(__('When enabled, the theme will include a sidebar that can contain WordPress widgets. You can set the sidebar width in the Sidebar/Widgets tab in this admin screen.','wpbootstrap')); ?>'
				},
				'headerWidgets' : {
					'el': $('#<?php echo $themename_input; ?>-general_settings-display_header_widgets'),
					'text' : '<?php echo esc_js(__('When enabled, the theme will include a widgets area above the header. You can set the single widget width in the Sidebar/Widgets tab in this admin screen.','wpbootstrap')); ?>'
				},
				'footerWidgets' : {
					'el': $('#<?php echo $themename_input; ?>-general_settings-display_footer_widgets'),
					'text' : '<?php echo esc_js(__('When enabled, the theme will include a widgets area above the footer. You can set the single widget width in the Sidebar/Widgets tab in this admin screen.','wpbootstrap')); ?>'
				},
				'footer' : {
					'el' : $('#<?php echo $themename_input; ?>-general_settings-display_footer'),
					'text': '<?php echo esc_js(__('When enabled, the theme will include footer credits.','wpbootstrap')); ?>'
				},
				'comments' : {
					'el' : $('#<?php echo $themename_input; ?>-general_settings-display_comments'),
					'text': '<?php echo esc_js(__('When enabled, posts and pages comments will be displayed.','wpbootstrap')); ?>'
				},
				'commentsClosed' : {
					'el' : $('#<?php echo $themename_input; ?>-general_settings-display_comments_closed_info'),
					'text': '<?php echo esc_js(__('When enabled "Comments are closed" will be displayed on pages and posts that have comments disabled.','wpbootstrap')); ?>'
				},
				'postMeta' : {
					'el' : $('#<?php echo $themename_input; ?>-general_settings-display_postmeta'),
					'text': '<?php echo esc_js(__('When enabled posts meta data section for posts will be shown. Meta data section shows: published date, post author, categories and tags.','wpbootstrap')); ?>'
				},
				'postMetaCPT' : {
					'el' : $('#<?php echo $themename_input; ?>-general_settings-display_postmeta_cpt'),
					'text': '<?php echo esc_js(__('When enabled, it will show meta data section for custom post types. Meta data section shows: published date, post author, categories and tags.','wpbootstrap')); ?>'
				},
				'postThumbnail' : {
					'el' : $('#<?php echo $themename_input; ?>-general_settings-display_thumbnails'),
					'text': '<?php echo esc_js(__('When enabled posts thumbnails will be shown.','wpbootstrap')); ?>'
				}
			};
			var $pageTitlesSettingsinputs = {
				'singlePostsTitles' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_single_post_titles'),
					'text': '<?php echo esc_js(__('When enabled, all single posts will include an H1 title. The text will come from the title of the page. When not enabled, the theme will not output the H1 title, allowing you to output it manually via the post content.','wpbootstrap')); ?>'
				},
				'singlePostsTitlesCPT' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_single_post_titles_cpt'),
					'text': '<?php echo esc_js(__('When enabled, all custom post types will include an H1 title. The text will come from the title of the custom post type. When not enabled, the theme will not output the H1 title, allowing you to add it manually in your page content.','wpbootstrap')); ?>'
				},
				'pageTitles' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_pages_titles'),
					'text': '<?php echo esc_js(__('When enabled, all pages will include an H1 title. The text will come from the title of the page. When not enabled, the theme will not output the H1 title, allowing you to add it manually in your page content.','wpbootstrap')); ?>'
				},
				'homepageTitles' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_pages_titles_on_homepage'),
					'text': '<?php echo esc_js(__('When unchecked, this will disable the H1 title of your page in the homepage. This is particulary useful if you want to disable the H1 page title on the homepage. This will work only if the front page display setting in WordPress is set to Static Page.','wpbootstrap')); ?>'
				},				
				'homeTitles' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_home_post_titles'),
					'text': '<?php echo esc_js(__('When enabled, all home page posts will include an H1 title. When not enabled, the theme will not output the H1 title, allowing you to output it manually via the post content.','wpbootstrap')); ?>'
				},
				'archiveTitles' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_archives_post_titles'),
					'text': '<?php echo esc_js(__('When enabled, all posts in archive pages will include a H2 title. When not enabled, the theme will not output the H2 title, allowing you to output it manually via the post content.','wpbootstrap')); ?>'
				},
				'archiveHeader' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_archives_header'),
					'text': '<?php echo esc_js(__('When enabled, all archive pages will include a H1 header above the posts list. When not enabled, the theme will not output the H1 header, allowing you to output it manually.','wpbootstrap')); ?>'
				},
				'searchResultsPostsTitles' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_search_titles'),
					'text': '<?php echo esc_js(__('When enabled, all posts in the search resutls pages will include a H2 title. When not enabled, the theme will not output the H2 header, allowing you to output it manually.','wpbootstrap')); ?>'
				},
				'searchResultsHeader' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_search_header'),
					'text': '<?php echo esc_js(__('When enabled, search result page will include a H1 header above the posts list. When not enabled, the theme will not output the H1 header, allowing you to output it manually.','wpbootstrap')); ?>'
				},
				'categoriesTitles' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_categories_post_titles'),
					'text': '<?php echo esc_js(__('When enabled, all posts in categories archive pages will include a H2 title. When not enabled, the theme will not output the H2 title, allowing you to output it manually via the post content.','wpbootstrap')); ?>'
				},
				'categoriesHeader' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_categories_header'),
					'text': '<?php echo esc_js(__('When enabled, all posts in categories archive pages will include a H1 header above the posts list. When not enabled, the theme will not output the H1 header, allowing you to output it manually.','wpbootstrap')); ?>'
				},
				'tagsTitles' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_tags_post_titles'),
					'text': '<?php echo esc_js(__('When enabled, all posts in tags archive pages will include a H2 title. When not enabled, the theme will not output the H2 title, allowing you to output it manually via the post content.','wpbootstrap')); ?>'
				},
				'tagsHeader' : {
					'el' : $('#<?php echo $themename_input; ?>-titles_settings-display_tags_header'),
					'text': '<?php echo esc_js(__('When enabled, all posts in tags archive will include a H1 header above the posts list. When not enabled, the theme will not output the H1 header, allowing you to output it manually.','wpbootstrap')); ?>'
				}
			};

			var showWpPointer = function($el,$args) {

				var defaults = {
					$pointerHeader : $el.prev('label').text(),
					$pointerContent : '<img src="'+templateDir+'/theme-options/img/'+$el.siblings('input').attr('id')+'.jpg">'
				};
				var opts = $.extend(defaults, $args);

				$el.pointer({
					content: '<h3>'+opts.$pointerHeader+'</h3><p>'+opts.$pointerContent+'</p>',
					position: {
						edge: 'left',
						align: 'center',
						offset: '15 0'
					}
				}).pointer('open');
			};

			$.each($MenuSettingsinputs,function(){
				var $questionMarkIco = $('<span class="ico-questionmark">');
				$(this).parent().append($questionMarkIco);
				$questionMarkIco.click(function(){
					$(this).addClass('active');
					$('.wp-pointer').hide();
					showWpPointer($questionMarkIco);
				});
			});

			$.each($.extend({},$GeneralSettingsinputs,$pageTitlesSettingsinputs),function(indx,obj){
				var $questionMarkIco = $('<span class="ico-questionmark">');
				obj.el.next().after($questionMarkIco);
				$questionMarkIco.click(function(){
					$(this).addClass('active');
					$('.wp-pointer').hide();
					showWpPointer($questionMarkIco,{
						$pointerContent : obj.text
					});
				});
			});

			// Hide wp-tooltip when shwitching tab
			$('#optionsframework-wrap .nav-tab, #optionsframework .controls label').click(function(){
				$('.wp-pointer').hide();
				$('.ico-questionmark').removeClass('active');
			});

			$(document).on('click','.wp-pointer-buttons .close, .ico-questionmark, #optionsframework .controls label',function(e){
				$('.ico-questionmark').removeClass('active');
				if ($(this).is('.ico-questionmark')) {
					$(this).addClass('active');
				}
			});

			// Enable/Disable checkbox for "Comments are closed"
			var $displayComments =  $('#<?php echo $themename_input; ?>-general_settings-display_comments');
			var $displayCommentsClosedInfo = $('#<?php echo $themename_input; ?>-general_settings-display_comments_closed_info');
			var $displayCommentsClosedInfoLabel = $displayCommentsClosedInfo.next('label');
			var $displayCommentsClosedInfoIcon = $displayCommentsClosedInfoLabel.next('.ico-questionmark');
			if (!$displayComments.is(':checked')) {
				 $displayCommentsClosedInfo.hide();
				 $displayCommentsClosedInfoLabel.hide();
				 $displayCommentsClosedInfoIcon.hide();
			}
			$displayComments.click(function(){
				if ($(this).is(':checked')) {
					$displayCommentsClosedInfo.fadeIn('fast');
					$displayCommentsClosedInfoLabel.fadeIn('fast');
					$displayCommentsClosedInfoIcon.fadeIn('fast');
				} else {
					$displayCommentsClosedInfo.hide();
					$displayCommentsClosedInfoLabel.hide();
					$displayCommentsClosedInfoIcon.hide();
				}
			});

			// Show/Hide footer texts
			var displayCredits = $('#display_credit_footer');
			var displayCreditsTextareas = $('#section-display_credit_footer_left, #section-display_credit_footer_right');

			displayCredits.on('click', function() {
				if ($(this).is(':checked')) {
					displayCreditsTextareas.fadeIn('fast');
				}
				else {
					displayCreditsTextareas.hide();
				}
			});

			// Show/Hide credits footer on page load.
			if (displayCredits.is(':checked')) {
				displayCreditsTextareas.show();
			}
			else {
				displayCreditsTextareas.hide();
			}
			//Hide Custom CSS box if not used
			if (translations.custom_css_customize=='loading_from_theme') {
	            $('#section-css_loading_method_selected_user_textarea').addClass('hidden');
			} else {
				$('#section-css_loading_method_selected_user_textarea').removeClass('hidden');
			}
		});
	</script>

<?php //END OF optionsframework_custom_scripts
}