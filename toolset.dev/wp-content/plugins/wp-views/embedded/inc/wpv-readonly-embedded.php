<?php

/**
* wpv-readonly-embedded.php
*
* Readonly generators
*
* @since 1.6.2
*/

/**
* views_embedded_html
*
* Renders the readonly page to test the summaries and readonly textareas
*
* @since 1.6.2
*/

function views_embedded_html() {
	global $WP_Views, $post;
	
	if ( isset( $_GET['view_id'] ) && is_numeric( $_GET['view_id'] ) ) {
		$view_id = (int)$_GET['view_id'];
		$view = get_post( $view_id );
		if ( null == $view ) {
			wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
		} elseif ( 'view'!= $view->post_type ) {
			wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
		} else {
			$view_settings = $WP_Views->get_view_settings($_GET['view_id']);
			$view_layout_settings = get_post_meta($_GET['view_id'], '_wpv_layout_settings', true);
			if (isset($view_settings['view-query-mode']) && ('normal' ==  $view_settings['view-query-mode'])) {
				$post = $view;
				if ( get_post_status( $view_id ) == 'trash' ) {
					wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You can�t edit this View because it is in the Trash. Please restore it and try again.', 'wpv-views') . '</p></div>' );
				}
			} else {
				wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
			}
		}
	} else {
		wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
	}
	?>
	<div class="wrap toolset-views">
		<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
		<h2><?php echo __( 'Preview View','wpv-views' ); ?></h2>
		<?php if( !isset( $view_settings['view_purpose'] ) ) {
			$view_settings['view_purpose'] = 'full';
		} ?>
		<input type="hidden" class="js-wpv-view-purpose" value="<?php echo esc_attr( $view_settings['view_purpose'] ); ?>" />
		<?php if ( isset( $_GET['in-iframe-for-layout'] ) ) {
			$in_iframe = 'yes';
		} else {
			$in_iframe = '';
		} ?>
		<input type="hidden" class="js-wpv-display-in-iframe" value="<?php echo $in_iframe; ?>" />
		<div class="wpv-title-section">
			<div class="wpv-setting-container wpv-settings-title-and-desc">
				<div class="wpv-settings-header">
					<h3>
						<?php _e( 'Title and description', 'wpv-views' ) ?>
					</h3>
				</div>
				<div class="wpv-setting">
					<h3 style="margin-top:8px;font-size:18px;line-height:1.5;">
						<?php echo get_the_title( $view_id ); ?>
					</h3>
					<p>
						<?php _e( 'Slug of this View: ', 'wpv-views' ); echo '<code>' . esc_attr( $view->post_name ) . '</code>'; ?>
					</p>
					<?php
					$view_description = get_post_meta($_GET['view_id'], '_wpv_description', true);
					if ( isset( $view_description ) && !empty( $view_description ) ) {
					?>
					<p>
					<?php echo $view_description; ?>
					</p>
					<?php
					}
					?>
				</div>
			</div>
		</div> <!-- .wpv-title-section -->
		
		<div class="wpv-query-section">
			<?php
			wpv_get_embedded_view_introduction_data();
			?>
			<h3 class="wpv-section-title"><?php _e('The query section determines what content the View loads from the database','wpv-views') ?></h3>
			<?php do_action('view-embedded-section-query', $view_settings, $view_id); ?>
		</div>
		
		<div class="wpv-filter-section">
			<h3 class="wpv-section-title"><?php _e('The filter section lets you set up pagination and parametric search, which let visitors control the View query','wpv-views') ?></h3>
			<?php
			wpv_get_embedded_view_filter_introduction_data();
			?>
			<?php do_action('view-embedded-section-filter', $view_settings, $view_id); ?>
		</div>
		
		<div class="wpv-layout-section">
			<h3 class="wpv-section-title"><?php _e('The layout section styles the View output on the page.','wpv-views') ?></h3>
			<?php
			$data = wpv_get_embedded_view_layout_introduction_data();
			wpv_toolset_help_box($data);
			?>
			<?php do_action('view-embedded-section-layout', $view_settings, $view_layout_settings, $view_id); ?>
			<?php do_action('view-embedded-section-extra', $view_settings, $view_id); ?>
		</div>

		<div class="wpv-help-section">
			<div class="js-show-toolset-message" data-close="false" data-tutorial-button-text="<?php echo htmlentities( __('Learn how to display Views','wpv-views'), ENT_QUOTES ) ?>" data-tutorial-button-url="http://wp-types.com/documentation/user-guides/views/?utm_source=viewsplugin&utm_campaign=views&utm_medium=embedded-view-readonly-info&utm_term=What to see this in action?#2.5">
				<h2><?php _e('What to see this in action?','wpv-views') ?></h2>
			</div>
		</div>
	</div>
	<?php
}

/**
* content_templates_embedded_html
*
* Renders the readonly Content Template summary
*
* @since 1.6.2
*/

function content_templates_embedded_html() {
	global $post;
	
	if ( isset( $_GET['view_id'] ) && is_numeric( $_GET['view_id'] ) ) {
		$view_id = (int)$_GET['view_id'];
		$view = get_post( $view_id );
		if ( null == $view ) {
			wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
		} elseif ( 'view-template'!= $view->post_type ) {
			wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
		} else {
			$post = $view;
			if ( get_post_status( $view_id ) == 'trash' ) {
				wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You can�t edit this View because it is in the Trash. Please restore it and try again.', 'wpv-views') . '</p></div>' );
			}
		}
	} else {
		wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
	}
	?>
	<div class="wrap toolset-views">
		<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
		<h2><?php echo __( 'Preview Content Template','wpv-views' ); ?></h2>
		<?php if ( isset( $_GET['in-iframe-for-layout'] ) ) {
			$in_iframe = 'yes';
		} else {
			$in_iframe = '';
		} ?>
		<input type="hidden" class="js-wpv-display-in-iframe" value="<?php echo $in_iframe; ?>" />
		<div class="wpv-setting-container">
			<h3 style="margin-top:8px;font-size:18px;line-height:1.5;">
				<?php echo get_the_title( $view_id ); ?>
			</h3>
			<p>
				<?php _e( 'Slug of this Content Template: ', 'wpv-views' ); echo '<code>' . esc_attr( $view->post_name ) . '</code>'; ?>
			</p>
			<?php
			$view_description = get_post_meta( $view_id, '_wpv-content-template-decription', true );
			if ( isset( $view_description ) && !empty( $view_description ) ) {
			?>
			<p>
			<?php echo $view_description; ?>
			</p>
			<?php
			}
			?>
			<?php
			wpv_get_embedded_content_template_introduction_data();
			?>
			<div class="wpv-ct-editors">
				<?php
					$full_view = get_post( $view_id );
					$content = $full_view->post_content;
				?>
				<textarea cols="30" rows="10" id="wpv_content" name="wpv_content"><?php echo $content; ?></textarea>
			</div>
		</div>
		
	</div>
	<?php
}

/**
* wpv-readonly-embedded.php
*
* Readonly generators
*
* @since 1.6.2
*/

/**
* view_archives_embedded_html
*
* Renders the readonly page to test the summaries and readonly textareas
*
* @since 1.6.2
*/

function view_archives_embedded_html() {
	global $WP_Views, $post;
	
	if ( isset( $_GET['view_id'] ) && is_numeric( $_GET['view_id'] ) ) {
		$view_id = (int)$_GET['view_id'];
		$view = get_post( $view_id );
		if ( null == $view ) {
			wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
		} elseif ( 'view'!= $view->post_type ) {
			wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
		} else {
			$view_settings = $WP_Views->get_view_settings($_GET['view_id']);
			$view_layout_settings = get_post_meta($_GET['view_id'], '_wpv_layout_settings', true);
			if ( isset( $view_settings['view-query-mode'] ) && (
						( 'archive' ==  $view_settings['view-query-mode'] ) ||
						( 'layouts-loop' ==  $view_settings['view-query-mode'] ) // We'll use archive mode editor for the layouts-loop View
						)
			   ) {
				$post = $view;
				if ( get_post_status( $view_id ) == 'trash' ) {
					wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You can�t edit this View because it is in the Trash. Please restore it and try again.', 'wpv-views') . '</p></div>' );
				}
			} else {
				wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
			}
		}
	} else {
		wp_die( '<div class="wpv-setting-container"><p class="toolset-alert toolset-alert-error">' . __('You attempted to edit a View that doesn&#8217;t exist. Perhaps it was deleted?', 'wpv-views') . '</p></div>' );
	}
	?>
	<div class="wrap toolset-views">
		<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
		<h2>
		<?php
		if ('archive' ==  $view_settings['view-query-mode']) {
			echo __('Preview WordPress Archive','wpv-views');
		} else if ( 'layouts-loop' ==  $view_settings['view-query-mode'] ) {
			echo __('Preview Layouts Loop View','wpv-views');
		}
		?>
		</h2>
		<?php if( !isset( $view_settings['view_purpose'] ) ) {
			$view_settings['view_purpose'] = 'full';
		} ?>
		<input type="hidden" class="js-wpv-view-purpose" value="<?php echo esc_attr( $view_settings['view_purpose'] ); ?>" />
		<?php if ( isset( $_GET['in-iframe-for-layout'] ) ) {
			$in_iframe = 'yes';
		} else {
			$in_iframe = '';
		} ?>
		<input type="hidden" class="js-wpv-display-in-iframe" value="<?php echo $in_iframe; ?>" />
		<div class="wpv-title-section">
			<div class="wpv-setting-container wpv-settings-title-and-desc">
				<div class="wpv-settings-header">
					<h3>
						<?php _e( 'Title and description', 'wpv-views' ) ?>
					</h3>
				</div>
				<div class="wpv-setting">
					<h3 style="margin-top:8px;font-size:18px;line-height:1.5;">
						<?php echo get_the_title( $view_id ); ?>
					</h3>
					<p>
						<?php _e( 'Slug of this WordPress Archive: ', 'wpv-views' ); echo '<code>' . esc_attr( $view->post_name ) . '</code>'; ?>
					</p>
					<?php
					$view_description = get_post_meta($_GET['view_id'], '_wpv_description', true);
					if ( isset( $view_description ) && !empty( $view_description ) ) {
					?>
					<p>
					<?php echo $view_description; ?>
					</p>
					<?php
					}
					?>
				</div>
			</div>
		</div> <!-- .wpv-title-section -->
		
		<?php if ( 'archive' ==  $view_settings['view-query-mode'] ) { ?>
			<div class="wpv-query-section">
				<?php
				wpv_get_embedded_wordpress_archive_introduction_data();
				?>
				<h3 class="wpv-section-title"><?php _e('The loop section determines which listing page to customize','wpv-views') ?></h3>
				<?php do_action('view-embedded-section-archive-loop', $view_settings, $view_id); ?>
			</div>
		<?php } else if ( 'layouts-loop' ==  $view_settings['view-query-mode'] ) { ?>
			<div class="wpv-query-section">
				<?php
				wpv_get_embedded_layouts_loop_introduction_data();
				?>
			</div>
		<?php } ?>
		
		<div class="wpv-layout-section">
			<h3 class="wpv-section-title"><?php _e('The layout section styles the View output on the page.','wpv-views') ?></h3>
			<?php
			$data = wpv_get_embedded_view_layout_introduction_data();
			wpv_toolset_help_box($data);
			?>
			<?php do_action('view-embedded-section-layout', $view_settings, $view_layout_settings, $view_id); ?>
			<?php do_action('view-embedded-section-extra', $view_settings, $view_id); ?>
		</div>

		<div class="wpv-help-section">
			<div class="js-show-toolset-message" data-close="false" data-tutorial-button-text="<?php echo htmlentities( __('Learn how to display Views','wpv-views'), ENT_QUOTES ) ?>" data-tutorial-button-url="http://wp-types.com/documentation/user-guides/views/?utm_source=viewsplugin&utm_campaign=views&utm_medium=embedded-archive-view-readonly-link&utm_term=What to see this in action?#2.5">
				<h2><?php _e('What to see this in action?','wpv-views') ?></h2>
			</div>
		</div>
	</div>
	<?php
}

/**
* Read-only sections
*/

/**
* wpv_embedded_archive_loop
*
* Loop selection read-only section
*
* @param $view_settings
* @param $view_id
*
* @since 1.6.2
*/

add_action( 'view-embedded-section-archive-loop', 'wpv_embedded_archive_loop', 10, 2 );

function wpv_embedded_archive_loop( $view_settings, $view_id ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Loop selection', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['loops_selection']['title'] ?>" data-content="<?php echo $views_edit_help['loops_selection']['content'] ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<?php
			global $WP_Views, $WPV_view_archive_loop;
			$options = $WP_Views->get_options();
			$loops = $WPV_view_archive_loop->_get_post_type_loops();
			$builtin_loops = array('home-blog-page' => __('Home/Blog', 'wpv-views'),
					'search-page' => __('Search results', 'wpv-views'),
					'author-page' => __('Author archives', 'wpv-views'),
					'year-page' => __('Year archives', 'wpv-views'),
					'month-page' => __('Month archives', 'wpv-views'),
					'day-page' => __('Day archives', 'wpv-views')
			);
			$taxonomies = get_taxonomies('', 'objects');
			$exclude_tax_slugs = array();
			$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
			
			$selected = array();
			foreach ($loops as $loop => $loop_name) {
				if (isset($options['view_' . $loop]) && $options['view_' . $loop] == $view_id) {
					$not_built_in = '';
					if ( !isset( $builtin_loops[$loop] ) ) {
						$not_built_in = __(' (post type archive)', 'wpv-views');
					}
					$selected[] = '<li>' . $loop_name . $not_built_in . '</li>';
				}
			}
			foreach ($taxonomies as $category_slug => $category) {
				if ( in_array( $category_slug, $exclude_tax_slugs ) ) {
					continue;
				}
				if ( !$category->show_ui ) {
					continue; // Only show taxonomies with show_ui set to TRUE
				}
				$name = $category->name;
				if (isset ($options['view_taxonomy_loop_' . $name ]) && $options['view_taxonomy_loop_' . $name ] == $view_id) {
					$selected[] = '<li>' . $category->labels->name . __(' (taxonomy archive)', 'wpv-views') . '</li>';
				}
			}
			if ( empty( $selected ) ) { ?>
			<p>
				<?php _e( 'This WordPress Archive is not used on any archive loops', 'wpv-views' ); ?>
			</p>
			<?php } else { ?>
			<p>
				<?php _e( 'This WordPress Archive is used in the following archive loops: ', 'wpv-views' ); ?>
			</p>
			<ul class="wpv-taglike-list js-list-views-loops">
			<?php
				echo implode( $selected );
			?>
			</ul>
			<?php
			}
			?>
		</div>
	</div>
	<?php
}

/**
* wpv_embedded_content_selection
*
* Content selection read-only section
*
* @param $view_settings
*
* @since 1.6.2
*/

add_action( 'view-embedded-section-query', 'wpv_embedded_content_selection', 10 );

function wpv_embedded_content_selection( $view_settings ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Content selection', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['content_section']['title']; ?>" data-content="<?php echo $views_edit_help['content_section']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<p>
			<?php echo sprintf( __( 'This View loads <strong>%s</strong>', 'wpv-views' ), wpv_get_query_type_summary( $view_settings, 'embedded-info' ) ); ?>
			</p>
		</div>
	</div>
	<?php
}

/**
* wpv_embedded_ordering
*
* Sorting read-only section
*
* @param $view_settings
*
* @since 1.6.2
*/

add_action( 'view-embedded-section-query', 'wpv_embedded_ordering', 20 );

function wpv_embedded_ordering( $view_settings ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Ordering', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['ordering']['title']; ?>" data-content="<?php echo $views_edit_help['ordering']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<p>
			<?php echo __( 'Results are ', 'wpv-views' ) . wpv_get_ordering_summary( $view_settings, 'embedded-info' ); ?>
			</p>
		</div>
	</div>
	<?php
}

add_action( 'view-embedded-section-query', 'wpv_embedded_limit_offset', 30 );

function wpv_embedded_limit_offset( $view_settings ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Limit and offset', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['limit_and_offset']['title']; ?>" data-content="<?php echo $views_edit_help['limit_and_offset']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<p>
			<?php echo wpv_get_limit_offset_summary( $view_settings, 'embedded-info' ); ?>
			</p>
		</div>
	</div>
	<?php
}

/**
* wpv_embedded_query_filter
*
* Filters read-only section
*
* @param $view_settings
*
* @since 1.6.2
*/

add_action( 'view-embedded-section-query', 'wpv_embedded_query_filter', 40 );

function wpv_embedded_query_filter( $view_settings ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Query filter', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['filter_the_results']['title']; ?>" data-content="<?php echo $views_edit_help['filter_the_results']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting wpv-settings-content-filter">
			<?php
			$filters_summary = '';
			$status_filter = wpv_get_filter_status_summary_txt( $view_settings );
			if ( !empty( $status_filter ) ) {
				$filters_summary .= '<li>' . $status_filter . '</li>';
			}
			$author_filter = wpv_get_filter_author_summary_txt( $view_settings );
			if ( !empty( $author_filter ) ) {
				$filters_summary .= '<li>' . $author_filter . '</li>';
			}
			$id_filter = wpv_get_filter_id_summary_txt( $view_settings );
			if ( !empty( $id_filter ) ) {
				$filters_summary .= '<li>' . $id_filter . '</li>';
			}
			$search_filter = wpv_get_filter_search_summary_txt( $view_settings );
			if ( !empty( $search_filter ) ) {
				$filters_summary .= '<li>' . $search_filter . '</li>';
			}
			$taxonomy_search_filter = wpv_get_filter_taxonomy_search_summary_txt( $view_settings );
			if ( !empty( $taxonomy_search_filter ) ) {
				$filters_summary .= '<li>' . $taxonomy_search_filter . '</li>';
			}
			$custom_field_filter = wpv_get_filter_custom_field_summary_txt( $view_settings );
			if ( !empty( $custom_field_filter ) ) {
				$filters_summary .= '<li class="filter-row-multiple">' . __( 'Select posts with custom field:', 'wpv-views' ) . $custom_field_filter . '</li>';
			}
			$taxonomy_filter = wpv_get_filter_taxonomy_summary_txt( $view_settings );
			if ( !empty( $taxonomy_filter ) ) {
				$filters_summary .= '<li class="filter-row-multiple">' . __( 'Select posts with taxonomy:', 'wpv-views' ) . $taxonomy_filter . '</li>';
			}
			$post_relationship_filter = wpv_get_filter_post_relationship_summary_txt( $view_settings );
			if ( !empty( $post_relationship_filter ) ) {
				$filters_summary .= '<li>' . $post_relationship_filter . '</li>';
			}
			$parent_filter = wpv_get_filter_parent_summary_txt( $view_settings );
			if ( !empty( $parent_filter ) ) {
				$filters_summary .= '<li>' . $parent_filter . '</li>';
			}
			$taxonomy_parent_filter = wpv_get_filter_taxonomy_parent_summary_txt( $view_settings );
			if ( !empty( $taxonomy_parent_filter ) ) {
				$filters_summary .= '<li>' . $taxonomy_parent_filter . '</li>';
			}
			$taxonomy_terms_filter = wpv_get_filter_taxonomy_term_summary_txt( $view_settings );
			if ( !empty( $taxonomy_terms_filter ) ) {
				$filters_summary .= '<li>' . $taxonomy_terms_filter . '</li>';
			}
			$users_filter = wpv_get_filter_users_summary_txt( $view_settings );
			if ( !empty( $users_filter ) ) {
				$filters_summary .= '<li>' . $users_filter . '</li>';
			}
			$usermeta_field_filter = wpv_get_filter_usermeta_field_summary_txt( $view_settings );
			if ( !empty( $usermeta_field_filter ) ) {
				$filters_summary .= '<li class="filter-row-multiple">' . __( 'Select users with usermeta field:', 'wpv-views' ) . $usermeta_field_filter . '</li>';
			}
			if ( '' != $filters_summary ) {
			?>
			<ul class="filter-list">
			<?php echo $filters_summary; ?>
			</ul>
			<?php } else { ?>
			<p>
			<?php _e( 'No filters set', 'wpv-views'); ?>
			</p>
			<?php } ?>
		</div>
	</div>
	<?php
}

/**
* wpv_embedded_pagination
*
* Pagination read-only section
*
* @param $view_settings
*
* @since 1.6.2
*/

add_action( 'view-embedded-section-filter', 'wpv_embedded_pagination', 10 );

function wpv_embedded_pagination( $view_settings ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Pagination and Slider settings', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['pagination_and_sliders_settings']['title']; ?>" data-content="<?php echo $views_edit_help['pagination_and_sliders_settings']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<p>
			<?php echo __( '', 'wpv-views' ) . wpv_get_pagination_summary( $view_settings, 'embedded-info' ); ?>
			</p>
		</div>
	</div>
	<?php
}

/**
* wpv_embedded_filter_extra
*
* Filter HTML read-only section
*
* @param $view_settings
*
* @since 1.6.2
*/

add_action( 'view-embedded-section-filter', 'wpv_embedded_filter_extra', 20 );

function wpv_embedded_filter_extra( $view_settings ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container wpv-setting-container-horizontal">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Filter HTML', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['filters_html_css_js']['title']; ?>" data-content="<?php echo $views_edit_help['filters_html_css_js']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<textarea cols="30" rows="10" id="wpv_filter_meta_html_content" name="wpv_filter_meta_html"><?php echo ( isset( $view_settings['filter_meta_html'] ) ) ? $view_settings['filter_meta_html'] : ''; ?></textarea>
		</div>
	</div>
	<?php
}

/**
* wpv_embedded_layout_extra
*
* Layout HTML read-only section
*
* @param $view_settings
* @param $view_layout_settings
*
* @since 1.6.2
*/

add_action( 'view-embedded-section-layout', 'wpv_embedded_layout_extra', 10, 2 );

function wpv_embedded_layout_extra(  $view_settings, $view_layout_settings ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container wpv-setting-container-horizontal">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Layout HTML', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['layout_html_css_js']['title']; ?>" data-content="<?php echo $views_edit_help['layout_html_css_js']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<textarea cols="30" rows="10" id="wpv_layout_meta_html_content" name="wpv_layout_layout_meta_html"><?php echo ( isset( $view_layout_settings['layout_meta_html'] ) ) ? $view_layout_settings['layout_meta_html'] : ''; ?></textarea>
		</div>
	</div>
	<?php
}

/**
* wpv_embedded_combined_output
*
* Content read-only section
*
* @param $view_settings
* @param $view_layout_settings
* @param $view_id
*
* @since 1.6.2
*/

add_action( 'view-embedded-section-layout', 'wpv_embedded_combined_output', 20, 3 );

function wpv_embedded_combined_output(  $view_settings, $view_layout_settings, $view_id ) {
	global $views_edit_help;
	?>
	<div class="wpv-setting-container wpv-setting-container-horizontal">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Combined Output', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['complete_output']['title']; ?>" data-content="<?php echo $views_edit_help['complete_output']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<?php
				$full_view = get_post( $view_id );
				$content = $full_view->post_content;
			?>
			<textarea cols="30" rows="10" id="wpv_content" name="wpv_content"><?php echo $content; ?></textarea>
		</div>
	</div>
	<?php
}