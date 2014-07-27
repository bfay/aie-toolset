<?php

/**
 * Creates the HTML version for wpvToolsetHelp()
 * 
 * @param data array() containing the attributes
 * @return echo HTML box
 *
 * TODO when wpvToolsetHelp() is moved to a common utils.js file, this can be moved to a comon file too
 */

function wpv_toolset_help_box($data) { 
	if (is_array($data) && !empty($data)) { ?>
	<div class="js-show-toolset-message"<?php foreach ($data as $key => $value) {if ('text' != $key) { ?> data-<?php echo $key; ?>="<?php echo $value; ?>"<?php } } ?>>
	<?php if (isset($data['text'])) echo $data['text']; ?>
	</div>
	<?php }
}

if( !defined('WPV_MESSAGE_SPACE_CHAR') ) define('WPV_MESSAGE_SPACE_CHAR', '&nbsp;');

/**
* ToolSet Help Box for Views
* General explanation
*
* @return echo the main help boxes for the Query section
*
* @link
*/
/**
*	- Creating paginated listings with Views
*/
if( !defined('WPV_LINK_CREATE_PAGINATED_LISTINGS') ) define('WPV_LINK_CREATE_PAGINATED_LISTINGS', 'http://wp-types.com/documentation/user-guides/views-pagination/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-create-paginated-listing-helpbox&utm_term=Creating paginated listings with Views');
/**
*	- Creating sliders with Views
*/
if( !defined('WPV_LINK_CREATE_SLIDERS') ) define('WPV_LINK_CREATE_SLIDERS', 'http://wp-types.com/documentation/user-guides/creating-sliders-with-types-and-views/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-create-sliders-helpbox&utm_term=Creating sliders with Views');
/**
*	- Creating parametric searches with Views
*/
if( !defined('WPV_LINK_CREATE_PARAMETRIC_SEARCH') ) define('WPV_LINK_CREATE_PARAMETRIC_SEARCH', 'http://wp-types.com/documentation/user-guides/front-page-filters/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-create-parametric-search-helpbox&utm_term=Creating parametric searches with Views');
/**
* NOTE htmlentities applied to data-attributes
*/

function wpv_get_view_introduction_data() {
	$all = array(
		'text'			=> '<p>' . __('A View loads content from the database and displays it anyway you choose.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.', 'wpv-views') . '</li>'
						. '<li>' . __('At the bottom of this page you will find the Layout section, where you control the output.', 'wpv-views') . '</li></ul>',
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-all'
	);
	wpv_toolset_help_box($all);
	$pagination = array(
		'text'			=> '<p>' . __('A View loads content from the database and displays it anyway you choose.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.', 'wpv-views') . '</li>'
						. '<li>' . __('The Front-end Filter section includes the pagination controls, allowing visitors to choose which results page to show.', 'wpv-views') . '</li>'
						. '<li>' . __('At the bottom of this page you will find the Layout section, where you control the output.', 'wpv-views') . '</li></ul>',
		'tutorial-button-text'	=> htmlentities( __('Creating paginated listings with Views', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_CREATE_PAGINATED_LISTINGS,
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-pagination'
	);
	wpv_toolset_help_box($pagination);
	$slider = array(
		'text'			=> '<p>' . __('A View loads content from the database and displays it anyway you choose.','wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.','wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.','wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.','wpv-views') . '</li>'
						. '<li>' . __('The Front-end Filter section includes the slider controls, allowing visitors switch between slides.','wpv-views') . '</li>'
						. '<li>' . __('At the bottom of this page you will find a slide Content Template, where you design slides.', 'wpv-views') . '</li></ul>',
		'tutorial-button-text'	=> htmlentities( __('Creating sliders with Views', 'wpv-views'),ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_CREATE_SLIDERS,
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-slider'
	);
	wpv_toolset_help_box($slider);
	$parametric = array(
		'text'			=> '<p>' . __('Building a View for a parametric search:', 'wpv-views') . '</p>'
						. '<ol><li>' . __('Select which content to load in the \'Content selection\' section.', 'wpv-views') . '</li>'
						. '<li>' . __('Add filter inputs to the \'Filter HTML/CSS/JS\' section.', 'wpv-views') . '</li>'
						. '<li>' . __('Select advanced search options in the \'Parametric search settings\' section.', 'wpv-views') . '</li>'
						. '<li>' . __('Design the search results output in the \'Layout HTML/CSS/JS\' section.', 'wpv-views') . '</li></ol>'
						. '<p>' . __('Remember to click on Update after you complete each section and before you continue to the next section.', 'wpv-views') . '</p>',
		'tutorial-button-text'	=> htmlentities( __('Creating parametric searches with Views', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_CREATE_PARAMETRIC_SEARCH,
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-parametric'
	);
	wpv_toolset_help_box($parametric);
	$full = array(
		'text'			=> '<p>' . __('A View loads content from the database and displays it anyway you choose.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.','wpv-views') . '</li>'
						. '<li>' . __('The Front-end filter section lets you add pagination, slider controls and parametric search to the View.', 'wpv-views') . '</li>'
						. '<li>' .  __('At the bottom of this page you will find the Layout section, where you control the output.', 'wpv-views') . '</li></ul>',
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-full'
	);
	wpv_toolset_help_box($full);
}

/**
* ToolSet Help Box for Views
* Pagination introduction
*
* @return echo the main help boxes for the Filter section
*
* @link
* 	- Learn more about using the filter section
*/
/**
*	- Creating sliders with Views
*/
if( !defined('WPV_LINK_DESIGN_SLIDER_TRANSITIONS') ) define('WPV_LINK_DESIGN_SLIDER_TRANSITIONS', 'http://wp-types.com/documentation/user-guides/creating-sliders-with-types-and-views/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-create-sliders-transitions-helpbox&utm_term=Creating sliders with Views');

function wpv_get_view_filter_introduction_data() {
	$pagination = array(
		'text'			=> '<p>' . __('The pagination section lets you break the results into separate pages.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('This way, you can display a large number of results, in shorter pages.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You can insert next/previous links and page selectors, for navigating directly to a specific page.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('The first part of this section lets you choose how pagination works.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Select how many results to show in each page and how pages transition.', 'wpv-views') . '</li>'
						. '<li>' . __('The second part of this section lets you design the pagination controls that would appear on the page for visitors.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The toolbar above the HTML editor includes buttons for inserting various controls.', 'wpv-views') . '</li>'
						. '<li>' . __('Besides pagination, you can also insert parametric search filters and content search controls.', 'wpv-views') . '</li></ul>',
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-filter js-for-view-purpose-pagination'
	);
	wpv_toolset_help_box($pagination);
	$slider = array(
		'text'			=> '<p>' . __('The pagination section lets you build sliders with Views.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The View will display each slide at a time and allow visitors to switch between slides using next/previous links and slide selectors.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('The first part of this section lets you choose how the slider pagination works.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Select how many results to show in each slide and how slides transition.', 'wpv-views') . '</li>'
						. '<li>' . __('The second part of this section lets you design the transition controls for the slider.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The toolbar above the HTML editor includes buttons for inserting the slide transition controls.', 'wpv-views') . '</li></ul>',
		'tutorial-button-text'	=> htmlentities( __('Creating sliders with Views', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_DESIGN_SLIDER_TRANSITIONS,
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-filter js-for-view-purpose-slider'
	);
	wpv_toolset_help_box($slider);
	$full = array(
		'text'			=> '<p>' . __('The pagination section lets you break the results into separate pages.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('This way, you can display a large number of results, in shorter pages.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You can insert next/previous links and page selectors, for navigating directly to a specific page.','wpv-views') . '</p><p>'
						. __('Using pagination you can also implement sliders.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. '<a href="' . WPV_LINK_CREATE_SLIDERS . '" target="_blank">' . __('Learn how to create sliders with Views.', 'wpv-views') . '</a></p>'
						. '<ul><li>' . __('The first part of this section lets you choose how pagination works.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Select how many results to show in each page and how pages transition.', 'wpv-views') . '</li>'
						. '<li>' . __('The second part of this section lets you design the pagination controls that would appear on the page for visitors.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The toolbar above the HTML editor includes buttons for inserting various controls.', 'wpv-views') . '</li>'
						. '<li>' . __('Besides pagination and slider transition controls, you can also insert parametric search filters and content search controls.', 'wpv-views') . '</li></ul>'
						. '<p><a href="' . WPV_LINK_CREATE_PARAMETRIC_SEARCH . '" target="_blank">' . __('Learn how to create parametric searches with Views.', 'wpv-views') . '</a></p>',
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-filter js-for-view-purpose-full'
	);
	wpv_toolset_help_box($full);
	$parametric = array(
		'text'			=> '<p>' . sprintf(__('To create a parametric search, position the cursor between the %s and %s shortcodes and click on the ‘Filters’ button to insert filter elements.', 'wpv-views'),'<strong>[wpv-filter-controls]</strong>','<strong>[/wpv-filter-controls]</strong>') . WPV_MESSAGE_SPACE_CHAR
						. __('Your parametric search can contain any custom field or taxonomy that this View queries.', 'wpv-views') . '</p>'
						. '<p>' . __('You can also click on the "Search" button to add a search box for visitors', 'wpv-views') . '</p>'
						. '<p>'. __('Use HTML and CSS to style the filter.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Remember to include the ‘Submit’ button for the form.', 'wpv-views'). '</p>'
						. '<p><a href="' . WPV_LINK_CREATE_PARAMETRIC_SEARCH . '" target="_blank">' . __('Learn how to create parametric searches with Views.', 'wpv-views') . '</a></p>'
						. ' <input id="wpv-parametric-hint-dismiss" type="hidden" class="js-wpv-parametric-hint-dismiss" data-nonce="' . wp_create_nonce( 'wpv_view_parametric_hint_dismiss_nonce')  . '" /> ',
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-filter js-for-view-purpose-parametric',
	);
	wpv_toolset_help_box($parametric);
}

/**
* ToolSet Help Box for Views
* Pagination hint when activating pagination and no pagination shortcodes in HTML
*
* @return echo the help boxes for the pagination flow
*/

function wpv_get_view_pagination_hint_data() {
	$pagination = array(
		'text'			=> '<p>' . __('This View uses pagination, but pagination controls are still not inserted into the filter HTML section.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Would you like to insert them now?', 'wpv-views') . '</p><p>' . '
						<button class="button-primary js-wpv-open-pagination-hint-popup">'
						. __('Automatically insert pagination controls', 'wpv-views')
						. '</button> <button class="button-secondary js-wpv-close-pagination-hint">'
						. __('No - I will add pagination controls manually', 'wpv-views')
						. '</button></p>',
		'close'			=> 'false',
		'classname'		=> 'js-wpv-pagination-hint-message js-wpv-pagination-hint-message-for-paged'
	);
	wpv_toolset_help_box($pagination);
	$slider = array(
		'text'			=> '<p>' . __('This View uses AJAX pagination to implement a slider.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('However, pagination controls are still not inserted into the filter HTML section.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Would you like to insert them now?', 'wpv-views') . '</p><p>'
						. '<button class="button-primary js-wpv-open-pagination-hint-popup">'
						. __('Automatically insert pagination controls', 'wpv-views')
						. '</button> <button class="button-secondary js-wpv-close-pagination-hint">'
						. __('No - I will add pagination controls manually', 'wpv-views')
						. '</button></p>',
		'close'			=> 'false',
		'classname'		=> 'js-wpv-pagination-hint-message js-wpv-pagination-hint-message-for-rollover'
	);
	wpv_toolset_help_box($slider);
}

/**
* ToolSet Help Box for Views
* Pagination hint after inserting shortcodes
*
* @return array to be passed to wpv_toolset_help_box()
*/

function wpv_get_view_pagination_hint_result_data() {
	$user_ID = get_current_user_id();
	$pag_hint_result_class = '';
	$user_help_setting = get_user_meta($user_ID, 'wpv_view_editor_help_dismiss');
	if (isset($user_help_setting[0]['pagination']) && $user_help_setting[0]['pagination'] == 'disable') $pag_hint_result_class = ' js-toolset-help-dismissed';
	$result = array(
		'text'			=> '<p>' . __('We\'ve just inserted shortcodes that display the pagination.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You will see the following shortcodes inside the filter HTML box below.', 'wpv-views') . '</p>'
						. '<dl class="js-wpv-pagination-hint-shortcode-meaning">LIST SHORTCODES AND THEIR PURPOSE</dl><p>'
						. __('To style the pagination, add your HTML around the pagination shortcodes.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You can always insert these shortcodes manually by clicking on the ‘Pagination’ button below.', 'wpv-views')
						. ' <input id="wpv-pagination-hint-result-dismiss" type="hidden" class="js-wpv-pagination-hint-result-dismiss" data-nonce="' . wp_create_nonce( 'wpv_view_pagination_hint_result_dismiss_nonce')  . '" /> ' . '</p>',
		'close'			=> 'false',
		'classname'		=> 'js-wpv-pagination-hint-message js-wpv-pagination-hint-message-result' . $pag_hint_result_class,
		'footer'		=> 'true'
	);
	return $result;
}

/**
* ToolSet Help Box for Views
* Layout introduction
*
* @return echo the main help boxes for the Layout section
*
* @link
* 	- Learn more by reading the Views Loop documentation
*/
if( !defined('WPV_LINK_LOOP_DOCUMENTATION') ) define('WPV_LINK_LOOP_DOCUMENTATION', 'http://wp-types.com/documentation/user-guides/digging-into-view-outputs/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-view-edit-layouts-helpbox&utm_term=Learn more by reading the Views Loop documentation.');

function wpv_get_view_layout_introduction_data() {
	$result = array(
		'text'			=> '<p>' . __('The layout HTML box lets you output your View results and style them on your page.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						.  __('Click on the Layout Wizard to select the style of your Views loop and the fields you want to display.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						.  sprintf(__('You can also insert fields manually by positioning the cursor inside between the %s and %s tags, and clicking on the Fields button.', 'wpv-views'),'<strong>&lt;wpv-loop&gt;</strong>','<strong>&lt;/wpv-loop&gt;</strong>') . '</p>'
						. '<p>' . __('The Content Template button will let you add, or even create, a Content template to insert directly into your view.', 'wpv-views') . '</p>'
						. '<p><a href="#" class="js-wpv-layout-help-extra-show">' . __('Learn more about layouts and building your View loop', 'wpv-views') . '</a></p>'
						. '<div class="js-wpv-layout-help-extra hidden">'
						. '<ul><li><strong>' . __('Layout wizard', 'wpv-views') . '</strong> - ' . __('a guided wizard that lets you create layouts, with different styles and content.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
							. __('Recommended if you are new to Views.', 'wpv-views') . '</li>'
						. '<li><strong>' . __('Fields', 'wpv-views') . '</strong> - ' . __('once you know how the layout loop works, add any field to it, using any formating style.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
							. __('Good for building your own unique loops.', 'wpv-views') . '</li>'
						. '<li><strong>' . __('Content Template', 'wpv-views') . '</strong> - ' . __('add complete blocks into the View loop using Content Templates.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
							. __('This method makes it easy to create complex layouts with simple editing.', 'wpv-views') . '</li>'
						. '<li><strong>' . __('Media', 'wpv-views') . '</strong> - ' . __('add images and other media items to the View.', 'wpv-views') . '</li></ul>'
						. '<p>' . __('Besides these buttons, you can edit the HTML content yourself by writing your own HTML and CSS.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The View will iterate through the the results and display them one by one.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. sprintf( __('Learn more by reading the %sViews Loop documentation%s.', 'wpv-views'), '<a href="' . WPV_LINK_LOOP_DOCUMENTATION . '" target="_blank">', '</a>' ) . '</p>'
						. '<p><a href="#" class="js-wpv-layout-help-extra-hide">' . __('Hide these instructions', 'wpv-views') . '</a></p>'
						. '</div>'
						. ' <input id="wpv-layout-hint-dismiss" type="hidden" class="js-wpv-layout-hint-dismiss" data-nonce="' . wp_create_nonce( 'wpv_view_layout_hint_dismiss_nonce')  . '" /> ',
		'close'			=> 'true',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-layout js-for-view-purpose-all js-for-view-purpose-pagination js-for-view-purpose-slider js-for-view-purpose-parametric js-for-view-purpose-full',
	);
	return $result;
}

/**
* ToolSet Help Box for Views
* Layout wizard hints
*
* @return array to be passed to wpv_toolset_help_box()
*/

function wpv_get_view_layout_wizard_hint_data() {
	$user_ID = get_current_user_id();
	$param_hint_result_class = '';
	$user_help_setting = get_user_meta($user_ID, 'wpv_view_editor_help_dismiss');
	if (isset($user_help_setting[0]['layout_wizard']) && $user_help_setting[0]['layout_wizard'] == 'disable') $param_hint_result_class = ' js-toolset-help-dismissed';
	$result = array(
		'text'			=> '<p>' . __('The Layout Wizard just added shortcodes for the fields that you selected to the HTML box.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('See how the shortcodes appear inside the loop.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You can change the appearance by adding HTML and CSS around these shortcodes.', 'wpv-views'). '</p>'
						. '<p>' . __('You can add HTML code, fields, media and entire Content Templates to the editor.', 'wpv-views') . '</p>'
						. ' <input id="wpv-layout-wizard-hint-dismiss" type="hidden" class="js-wpv-layout-wizard-dismiss" data-nonce="' . wp_create_nonce( 'wpv_view_layout_wizard_hint_dismiss_nonce')  . '" /> ',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-wpv-layout-wizard-hint' . $param_hint_result_class,
		'footer'		=> 'true'
	);
	return $result;
}

/**
* ToolSet Help Box for Views
* Content Template search hints
*
* @return array to be passed to wpv_toolset_help_box()
*/

function wpv_get_view_content_template_hint_data() {
	$user_ID = get_current_user_id();
	$param_hint_result_class = '';
	$user_help_setting = get_user_meta($user_ID, 'wpv_view_editor_help_dismiss');
	if (isset($user_help_setting[0]['content_template']) && $user_help_setting[0]['content_template'] == 'disable') $param_hint_result_class = ' js-toolset-help-dismissed';
	$result = array(
		'text'			=> '<p class="js-wpv-ct-was-inserted">' . __('You just added a shortcode for a Content Template to this View.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A Content Template works like a subroutine.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You can edit its content in one place and use it in several places in the View.', 'wpv-views'). '</p>'
						. '<p class="js-wpv-ct-was-not-inserted">' . __('You just connected a Content Template to this View.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A Content Template works like a subroutine.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You can edit its content in one place and use it in several places in the View.', 'wpv-views'). '</p>'
					//	. '<p class="js-wpv-ct-was-not-inserted">' . __('To display this Content Template in the View output, click again on the "Content Template" button.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
					//	. __('You will see the newly created template available to insert, without having to create it again.', 'wpv-views') . '</p>'
						. '<p><a href="#attached-content-templates" class="js-wpv-go-to-content-template">' . __('Edit the new Content Template', 'wpv-views') . '</a></p>'
						. '<input id="wpv-content-template-hint-dismiss" type="hidden" class="js-wpv-content-template-dismiss" data-nonce="' . wp_create_nonce( 'wpv_view_content_template_hint_dismiss_nonce')  . '" /> ',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-wpv-content-template-hint' . $param_hint_result_class,
		'footer'		=> 'true'
	);
	return $result;
}

/**
* ToolSet Help Box for Views
* Content Template: View slider mode
*
* @return echo the main help boxes for the Content template section section
*
*/
function wpv_get_view_ct_slider_introduction_data() {
    $result = array(
        'text'          => '<p class="js-wpv-ct-was-inserted">' . __('This Content Template lets you design slides in this slider. Add any field you need to display and design them using HTML and CSS. To style the slide transition controls, scroll up the the filter section.', 'wpv-views') . '</p>' . WPV_MESSAGE_SPACE_CHAR,                      
        'close'         => 'false',
        'hidden'        => 'false',
        'classname'     => 'js-wpv-content-template-slider-hint',
        'footer'        => 'false'
    );
    return $result;
}

/**
* ToolSet Help Box for Views
* Content Template: View Bootstrap Gird mode
*
* @return echo the main help boxes for the Content template section section
*
*/
function wpv_get_view_ct_bootstrap_grid_introduction_data( $query_mode ) {
	
	if ($query_mode == 'layouts-loop') {
		$text = __('This Content Template lets you design how each item in this grid will be displayed. A default shortcode that displays the post link has been added. You can edit this and add any field you need to display and design them using HTML and CSS.', 'wpv-views');
	} else {
		$text = __('This Content Template lets you design how each item in this grid will be displayed. A default shortcode that displays the post link has been added. You can edit this and add any field you need to display and design them using HTML and CSS.', 'wpv-views') .
								'<br /><br />' .
								__('To change what gets displayed, scroll up the the filter section.', 'wpv-views');
	}
	
    $result = array(
        'text'          => '<p class="js-wpv-ct-was-inserted">' .
								$text .
								'</p>' . WPV_MESSAGE_SPACE_CHAR,                      
        'close'         => 'false',
        'hidden'        => 'false',
        'classname'     => 'js-wpv-content-template-bootstrap-grid-hint',
        'footer'        => 'false'
    );
    return $result;
}

/**
* # Embedded mode help boxes
*/

function wpv_get_embedded_view_introduction_data() {
	$promotional = '<p class="toolset-promotional">' . __( 'You are viewing the read-only version of this View. To edit it, you need to get Views plugin.', 'wpv-views' )
				. '&nbsp;&nbsp;&nbsp;<a href="http://wp-types.com/home/views-create-elegant-displays-for-your-content/?utm_source=viewsplugin&utm_campaign=views&utm_medium=embedded-view-promotional-link&utm_term=Get Views" title="" class="button button-primary-toolset">' . __( 'Get Views', 'wpv-views' ) . '</a>'
				. '</p>';
	$all = array(
		'text'			=> $promotional . '<p>' . __('A View loads content from the database and displays it anyway you choose.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.', 'wpv-views') . '</li>'
						. '<li>' . __('At the bottom of this page you will find the Layout section, where you control the output.', 'wpv-views') . '</li></ul>',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-all'
	);
	wpv_toolset_help_box($all);
	$pagination = array(
		'text'			=> $promotional . '<p>' . __('A View loads content from the database and displays it anyway you choose.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.', 'wpv-views') . '</li>'
						. '<li>' . __('The Front-end Filter section includes the pagination controls, allowing visitors to choose which results page to show.', 'wpv-views') . '</li>'
						. '<li>' . __('At the bottom of this page you will find the Layout section, where you control the output.', 'wpv-views') . '</li></ul>',
		'tutorial-button-text'	=> htmlentities( __('Creating paginated listings with Views', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_CREATE_PAGINATED_LISTINGS,
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-pagination'
	);
	wpv_toolset_help_box($pagination);
	$slider = array(
		'text'			=> $promotional . '<p>' . __('A View loads content from the database and displays it anyway you choose.','wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.','wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.','wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.','wpv-views') . '</li>'
						. '<li>' . __('The Front-end Filter section includes the slider controls, allowing visitors switch between slides.','wpv-views') . '</li>'
						. '<li>' . __('At the bottom of this page you will find a slide Content Template, where you design slides.', 'wpv-views') . '</li></ul>',
		'tutorial-button-text'	=> htmlentities( __('Creating sliders with Views', 'wpv-views'),ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_CREATE_SLIDERS,
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-slider'
	);
	wpv_toolset_help_box($slider);
	$parametric = array(
		'text'			=> $promotional . '<p>' . __('Building a View for a parametric search:', 'wpv-views') . '</p>'
						. '<ol><li>' . __('Select which content to load in the \'Content selection\' section.', 'wpv-views') . '</li>'
						. '<li>' . __('Add filter inputs to the \'Filter HTML/CSS/JS\' section.', 'wpv-views') . '</li>'
						. '<li>' . __('Select advanced search options in the \'Parametric search settings\' section.', 'wpv-views') . '</li>'
						. '<li>' . __('Design the search results output in the \'Layout HTML/CSS/JS\' section.', 'wpv-views') . '</li></ol>'
						. '<p>' . __('Remember to click on Update after you complete each section and before you continue to the next section.', 'wpv-views') . '</p>',
		'tutorial-button-text'	=> htmlentities( __('Creating parametric searches with Views', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_CREATE_PARAMETRIC_SEARCH,
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-parametric'
	);
	wpv_toolset_help_box($parametric);
	$full = array(
		'text'			=> $promotional . '<p>' . __('A View loads content from the database and displays it anyway you choose.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.','wpv-views') . '</li>'
						. '<li>' . __('The Front-end filter section lets you add pagination, slider controls and parametric search to the View.', 'wpv-views') . '</li>'
						. '<li>' .  __('At the bottom of this page you will find the Layout section, where you control the output.', 'wpv-views') . '</li></ul>',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-full'
	);
	wpv_toolset_help_box($full);
	$bootstrap_grid = array(
		'text'			=> $promotional . '<p>' . __('A View loads content from the database and displays it anyway you choose.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The Query section lets you choose the content to load.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('A basic query selects all items of a chosen type.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('You can refine the selection by adding filters.','wpv-views') . '</li>'
						. '<li>' . __('The Front-end filter section lets you add pagination, slider controls and parametric search to the View.', 'wpv-views') . '</li>'
						. '<li>' .  __('At the bottom of this page you will find the Layout section, where you control the output.', 'wpv-views') . '</li></ul>',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-query js-for-view-purpose-bootstrap-grid'
	);
	wpv_toolset_help_box($bootstrap_grid);
}

if( !defined('WPV_LINK_CONTENT_TEMPLATE_DOCUMENTATION') ) define('WPV_LINK_CONTENT_TEMPLATE_DOCUMENTATION', 'http://wp-types.com/documentation/user-guides/view-templates/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-content-template-page&utm_term=Content Template documentation#tutorial');

function wpv_get_embedded_content_template_introduction_data() {
	$promotional = '<p class="toolset-promotional">' . __( 'You are viewing the read-only version of this Content Template. To edit it, you need to get Views plugin.', 'wpv-views' )
				. '&nbsp;&nbsp;&nbsp;<a href="http://wp-types.com/home/views-create-elegant-displays-for-your-content/?utm_source=viewsplugin&utm_campaign=views&utm_medium=embedded-content-template-promitional-link&utm_term=Get Views" title="" class="button-primary button-primary-toolset">' . __( 'Get Views', 'wpv-views' ) . '</a>'
				. '</p>';
	$all = array(
		'text'			=> $promotional . '<p>' . __('This Content Template replaces the content area of the posts that you assign it to.', 'wpv-views')
						. WPV_MESSAGE_SPACE_CHAR .  __('It can be used to tweak the content of a post when it is displayed alone or in an archive page.', 'wpv-views') . '</p>'
						. '<p>' . __( 'You can also call this Template using a shortcode [wpv-post-body view_template="XX"] to render specific information about the current post.', 'wpv-views' ) . '</p>'
						. '<p>' . __('You can add shortcodes to post fields, and also your own HTML and CSS to style the fields and design the page template.', 'wpv-views') . '</p>',
		'tutorial-button-text'	=> htmlentities( __('Content Template documentation', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_CONTENT_TEMPLATE_DOCUMENTATION,
		'close'			=> 'false',
		'hidden'		=> 'false'
	);
	wpv_toolset_help_box($all);
}

if( !defined('WPV_LINK_WORDPRESS_ARCHIVE_DOCUMENTATION') ) define('WPV_LINK_WORDPRESS_ARCHIVE_DOCUMENTATION', 'http://wp-types.com/documentation/user-guides/normal-vs-archive-views/?utm_source=viewsplugin&utm_campaign=views&utm_medium=edit-wordpress-archive-page&utm_term=WordPress Archive documentation');

function wpv_get_embedded_wordpress_archive_introduction_data() {
	$promotional = '<p class="toolset-promotional">' . __( 'You are viewing the read-only version of this WordPress Archive. To edit it, you need to get Views plugin.', 'wpv-views' )
				. '&nbsp;&nbsp;&nbsp;<a href="http://wp-types.com/home/views-create-elegant-displays-for-your-content/?utm_source=viewsplugin&utm_campaign=views&utm_medium=embedded-archive-view-promitional-link&utm_term=Get Views" title="" class="button-primary button-primary-toolset">' . __( 'Get Views', 'wpv-views' ) . '</a>'
				. '</p>';
	$all = array(
		'text'			=> $promotional . '<p>' . __('This WordPress Archive replaces the natural archive loops created by WordPress.', 'wpv-views') . '</p>',
		'tutorial-button-text'	=> htmlentities( __('WordPress Archives documentation', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_WORDPRESS_ARCHIVE_DOCUMENTATION,
		'close'			=> 'false',
		'hidden'		=> 'false'
	);
	wpv_toolset_help_box($all);
}

function wpv_get_embedded_layouts_loop_introduction_data() {
	$promotional = '<p class="toolset-promotional">' . __( 'OneYou are viewing the read-only version of this WordPress Archive. To edit it, you need to get Views plugin.', 'wpv-views' )
				. '&nbsp;&nbsp;&nbsp;<a href="http://wp-types.com/home/views-create-elegant-displays-for-your-content/?utm_source=viewsplugin&utm_campaign=views&utm_medium=embedded-arhive-loop-promitional-link&utm_term=Get Views" title="" class="button-primary button-primary-toolset">' . __( 'Get Views', 'wpv-views' ) . '</a>'
				. '</p>';
	$all = array(
		'text'			=> $promotional . '<p>' . __('This WordPress Archive replaces the natural archive loops created by WordPress.', 'wpv-views') . '</p>',
		'tutorial-button-text'	=> htmlentities( __('WordPress Archives documentation', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_WORDPRESS_ARCHIVE_DOCUMENTATION,
		'close'			=> 'false',
		'hidden'		=> 'false'
	);
	wpv_toolset_help_box($all);
}

function wpv_get_embedded_view_filter_introduction_data() {
	$pagination = array(
		'text'			=> '<p>' . __('The pagination section lets you break the results into separate pages.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('This way, you can display a large number of results, in shorter pages.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You can insert next/previous links and page selectors, for navigating directly to a specific page.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('The first part of this section lets you choose how pagination works.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Select how many results to show in each page and how pages transition.', 'wpv-views') . '</li>'
						. '<li>' . __('The second part of this section lets you design the pagination controls that would appear on the page for visitors.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The toolbar above the HTML editor includes buttons for inserting various controls.', 'wpv-views') . '</li>'
						. '<li>' . __('Besides pagination, you can also insert parametric search filters and content search controls.', 'wpv-views') . '</li></ul>',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-filter js-for-view-purpose-pagination'
	);
	wpv_toolset_help_box($pagination);
	$slider = array(
		'text'			=> '<p>' . __('The pagination section lets you build sliders with Views.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The View will display each slide at a time and allow visitors to switch between slides using next/previous links and slide selectors.', 'wpv-views') . '</p>'
						. '<ul><li>' . __('The first part of this section lets you choose how the slider pagination works.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Select how many results to show in each slide and how slides transition.', 'wpv-views') . '</li>'
						. '<li>' . __('The second part of this section lets you design the transition controls for the slider.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The toolbar above the HTML editor includes buttons for inserting the slide transition controls.', 'wpv-views') . '</li></ul>',
		'tutorial-button-text'	=> htmlentities( __('Creating sliders with Views', 'wpv-views'), ENT_QUOTES ),
		'tutorial-button-url'	=> WPV_LINK_DESIGN_SLIDER_TRANSITIONS,
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-filter js-for-view-purpose-slider'
	);
	wpv_toolset_help_box($slider);
	$full = array(
		'text'			=> '<p>' . __('The pagination section lets you break the results into separate pages.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('This way, you can display a large number of results, in shorter pages.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('You can insert next/previous links and page selectors, for navigating directly to a specific page.','wpv-views') . '</p><p>'
						. __('Using pagination you can also implement sliders.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. '<a href="' . WPV_LINK_CREATE_SLIDERS . '" target="_blank">' . __('Learn how to create sliders with Views.', 'wpv-views') . '</a></p>'
						. '<ul><li>' . __('The first part of this section lets you choose how pagination works.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Select how many results to show in each page and how pages transition.', 'wpv-views') . '</li>'
						. '<li>' . __('The second part of this section lets you design the pagination controls that would appear on the page for visitors.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The toolbar above the HTML editor includes buttons for inserting various controls.', 'wpv-views') . '</li>'
						. '<li>' . __('Besides pagination and slider transition controls, you can also insert parametric search filters and content search controls.', 'wpv-views') . '</li></ul>'
						. '<p><a href="' . WPV_LINK_CREATE_PARAMETRIC_SEARCH . '" target="_blank">' . __('Learn how to create parametric searches with Views.', 'wpv-views') . '</a></p>',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-filter js-for-view-purpose-full'
	);
	wpv_toolset_help_box($full);
	$parametric = array(
		'text'			=> '<p>' . sprintf(__('To create a parametric search, position the cursor between the %s and %s shortcodes and click on the ‘Filters’ button to insert filter elements.', 'wpv-views'),'<strong>[wpv-filter-controls]</strong>','<strong>[/wpv-filter-controls]</strong>') . WPV_MESSAGE_SPACE_CHAR
						. __('Your parametric search can contain any custom field or taxonomy that this View queries.', 'wpv-views') . '</p>'
						. '<p>' . __('You can also click on the "Search" button to add a search box for visitors', 'wpv-views') . '</p>'
						. '<p>'. __('Use HTML and CSS to style the filter.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('Remember to include the ‘Submit’ button for the form.', 'wpv-views'). '</p>'
						. '<p><a href="' . WPV_LINK_CREATE_PARAMETRIC_SEARCH . '" target="_blank">' . __('Learn how to create parametric searches with Views.', 'wpv-views') . '</a></p>'
						. ' <input id="wpv-parametric-hint-dismiss" type="hidden" class="js-wpv-parametric-hint-dismiss" data-nonce="' . wp_create_nonce( 'wpv_view_parametric_hint_dismiss_nonce')  . '" /> ',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-filter js-for-view-purpose-parametric',
	);
	wpv_toolset_help_box($parametric);
}

function wpv_get_embedded_view_layout_introduction_data() {
	$result = array(
		'text'			=> '<p>' . __('The layout HTML box lets you output your View results and style them on your page.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						.  __('Views will provide a Layout Wizard to select the style of your Views loop and the fields you want to display.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						.  __('You can also insert fields manually.', 'wpv-views') . '</p>'
						. '<p>' . __('The full Views plugin will let you add, or even create, a Content template to insert directly into your Layout.', 'wpv-views') . '</p>'
						. '<p>' . __('Besides these helpers, you can edit the HTML content yourself by writing your own HTML and CSS.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. __('The View will iterate through the the results and display them one by one.', 'wpv-views') . WPV_MESSAGE_SPACE_CHAR
						. sprintf( __('Learn more by reading the %sViews Loop documentation%s.', 'wpv-views'), '<a href="' . WPV_LINK_LOOP_DOCUMENTATION . '" target="_blank">', '</a>' ) . '</p>',
		'close'			=> 'false',
		'hidden'		=> 'true',
		'classname'		=> 'js-metasection-help-layout js-for-view-purpose-all js-for-view-purpose-pagination js-for-view-purpose-slider js-for-view-purpose-parametric js-for-view-purpose-full js-for-view-purpose-bootstrap-grid',
	);
	return $result;
}

/**
* Help messages
*
* $views_edit_help global
*/

global $views_edit_help;
$views_edit_help = 
    array(
        'title_and_description' => 
            array(
                'title' => htmlentities( __('Title and description', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __("Each View has a title and an optional description. These are used for you, to identify different Views. The title and the description don't appear anywhere on the site's public pages.", 'wpv-views'), ENT_QUOTES )
            ),
        'content_section' => 
            array(
                'title' => htmlentities( __('Content to load', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __('Choose between posts, taxonomy and users and then select the specific content type to load. For posts, you can select multiple content types.', 'wpv-views'), ENT_QUOTES )
            ),
        'query_options' => 
            array(
                'title' => htmlentities( __('Query options', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __('This section includes additional options for what content to load. You will see different options for posts, taxonomy and users.', 'wpv-views'), ENT_QUOTES )
            ),
        'ordering' => 
            array(
                'title' => htmlentities( __('Ordering', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __('Choose how to order the results that the View gets from the database. You can select the sorting key and direction.', 'wpv-views'), ENT_QUOTES )
            ),
        'limit_and_offset' => 
            array(
                'title' => htmlentities( __('Limit and offset', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __('You can limit the number of results returned by the query and set an offset. Please note that this option is not intended for pagination and sliders, but for static limit and offset settings.', 'wpv-views'), ENT_QUOTES )
            ),
        'filter_the_results' => 
            array(
                'title' => htmlentities( __('Query filter', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __("You can filter the View query by status, custom fields, taxonomy, users fields and even content search depending on the content that you are going to load. Click on 'Add another filter' and then select the filter type. A View may have as many filters as you like.", 'wpv-views'), ENT_QUOTES )
            ),
        'pagination_and_sliders_settings' => 
            array(
                'title' => htmlentities( __('Pagination and sliders settings', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __("You can use a View to display paginated results and sliders. Both are built using 'Pagination'. For paginated listings, choose to update the entire page. For sliders, choose to update only the View.", 'wpv-views'), ENT_QUOTES )
            ),
        'filters_html_css_js' => 
            array(
                'title' => htmlentities( __('Filter HTML/CSS/JS', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __("In this section you can add pagination controls, slider controls and parametric searches. If you enabled pagination, you need to insert the pagination controls here. They are used for both paged results and sliders. For parametric searches, insert 'filter' elements. The output of this section is displayed via the [wpv-filter-meta-html] shortcode in the Combined Output section.", 'wpv-views'), ENT_QUOTES )
            ),
		'parametric_search' => 
            array(
                'title' => htmlentities( __('Parametric search', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __("In this section you can choose when to refresh the Views results and which options to show in form inputs.", 'wpv-views'), ENT_QUOTES )
            ),
        'layout_html_css_js' => 
            array(
                'title' => htmlentities( __('View HTML output', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __('This HTML determines what the View outputs for the query results. Use the Layout wizard to create a new layout. Then, edit it by adding fields, HTML, media and anything else in the toolbar. The output of this section is displayed via the [wpv-layout-meta-html] in the Combined Output section.', 'wpv-views'), ENT_QUOTES )
            ),
        'templates_for_view' => 
            array(
                'title' => htmlentities( __('Templates for this View', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __("A View may include templates. These templates make it easy to output complex structures without having to repeat them in the View HTML. Click on the 'Content Template' button in the Layout HTML section to add Content Templates here.", 'wpv-views'), ENT_QUOTES ),          
            ),
        'complete_output' => 
            array(
                'title' => htmlentities( __('Combined Output', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __('This HTML box lets you control how the Filter and Layout sections of this Views are displayed. The [wpv-filter-meta-html] shortcode displays the output of the Filter section. The [wpv-layout-meta-html] shortcode displays the output of the Layout section. You can add your HTML and fields to rearrange and style the output.', 'wpv-views'), ENT_QUOTES )
            ),         
        'loops_selection' => 
            array(
                'title' => htmlentities( __('Loop selection', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __("Choose which listing page to customize. The WordPress archive will display the exact same content as WordPress normally does, but you can design it using the View HTML.", 'wpv-views'), ENT_QUOTES )
            ),
        'module_manager' => 
            array(
                'title' => htmlentities( __('Module Manager', 'wpv-views'), ENT_QUOTES ), 
                'content' => htmlentities( __("With Modules, you can easily reuse your designs in different websites and create your own library of building blocks.", 'wpv-views'), ENT_QUOTES )
            ),
    );
