<?php

/**
* wpv-summary-embedded.php
*
* Summary functions for sections and filters
*
* @since 1.6.2
*/

/**
* ## Sections summaries ##
*/

/**
* wpv_get_query_type_summary
*
* Returns the query type summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since 1.6.0
*/

function wpv_get_query_type_summary( $view_settings, $context = 'listing' ) {
	$view_settings = wpv_post_default_settings( $view_settings );
	$return = '';
	if ( !isset( $view_settings['query_type'] ) || ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'posts' ) ) {
		$selected = isset( $view_settings['post_type'] ) ? $view_settings['post_type'] : array();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$selected_post_types = sizeof( $selected );
		switch ( $selected_post_types ) {
			case 0:
				if ( $context == 'embedded-info' ) {
					$return .= __('all post types', 'wpv-views');
				} else {
					$return .= __('All post types', 'wpv-views');
				}
				break;
			case 1:
				if ( isset( $post_types[$selected[0]] ) ) {
					$name = $post_types[$selected[0]]->labels->name;
				} else {
					$name = $selected[0];
				}
				if ( $name == 'any' ) {
					$name = __('All post types', 'wpv-views');
				}
				$return .= sprintf( __( '%s', 'wpv-views' ), $name );
				break;
			default:
				for ( $i = 0; $i < $selected_post_types - 1; $i++ ) {
					if ( isset( $post_types[$selected[$i]] ) ) {
						$name = $post_types[$selected[$i]]->labels->name;
					} else {
						$name = $selected[$i];
					}
					if ( $i > 0 ) {
						$return .= ', ';
					}
					$return .= $name;
				}
				if ( isset( $post_types[$selected[$i]] ) ) {
					$name = $post_types[$selected[$i]]->labels->name;
				} else {
					$name = $selected[$i];
				}
				$return .= ', ' . $name;
				break;
		}
	}
	if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'taxonomy' ) {
		$view_settings = wpv_taxonomy_default_settings( $view_settings );
		$selected = $view_settings['taxonomy_type'];
		if ( isset( $selected[0] ) && !empty( $selected[0] ) && taxonomy_exists( $selected[0] ) ) {
			$taxonomies = get_taxonomies( '', 'objects' );
			if ( isset( $taxonomies[$selected[0]] ) ) {
				$name = $taxonomies[$selected[0]]->labels->name;
			} else {
				$name = $selected[0];
			}
			if ( $context == 'embedded-info' ) {
				$return .= sprintf( __( 'terms of the taxonomy %s', 'wpv-views' ), $name );
			} else {
				$return .= sprintf( __( 'This View selects terms of the taxonomy %s', 'wpv-views' ), $name );
			}
		} else {
			if ( $context == 'embedded-info' ) {
				$return .= __( 'terms of a taxonomy that no longer exists', 'wpv-views' );
			} else {
				$return .= __( 'This View selects terms of a taxonomy that no longer exists', 'wpv-views' );
			}
		}
	}
	if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'users' ) {
		$user_role = '';
		if ( isset( $view_settings['roles_type'][0] ) ) {
			$user_role = $view_settings['roles_type'][0];
		}
		if ( $context == 'embedded-info' ) {
			if ( $user_role == 'any' ) {
				$return .= __( 'users with any role', 'wpv-views' );
			} else {
				$return .= sprintf( __( 'users with role %s', 'wpv-views' ),  $user_role );
			}
		} else {
			if ( $user_role == 'any' ) {
				$return .= __( 'This View selects users with any role', 'wpv-views' );
			} else {
				$return .= sprintf( __( 'This View selects users with role %s', 'wpv-views' ),  $user_role );
			}
		}
    }
	return $return;
}

/**
* wpv_get_ordering_summary
*
* Returns the sorting summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since 1.6.0
*/

function wpv_get_ordering_summary( $view_settings, $context = 'listing' ) {
	$view_settings = wpv_order_by_default_settings( $view_settings );
	$view_settings = wpv_taxonomy_order_by_default_settings( $view_settings );
	$view_settings = wpv_users_order_by_default_settings( $view_settings );
	$return = '';
	if ( !isset( $view_settings['query_type'] ) || ( isset($view_settings['query_type'] ) && $view_settings['query_type'][0] == 'posts' ) ) {
		switch( $view_settings['orderby'] ) {
			case 'post_date':
				$order_by = __('post date', 'wpv-views');
				break;
			case 'post_title':
				$order_by = __('post title', 'wpv-views');
				break;
			case 'ID':
				$order_by = __('post ID', 'wpv-views');
				break;
			case 'menu_order':
				$order_by = __('menu order', 'wpv-views');
				break;
			case 'rand':
				$order_by = __('random order', 'wpv-views');
				break;
			default:
				$order_by = str_replace('field-', '', $view_settings['orderby']);
				$order_by = sprintf(__('Field - %s', 'wpv-views'), $order_by);
				break;
		}
		$order = __('descending', 'wpv-views');
		if ( $view_settings['order'] == 'ASC' ) {
			$order = __( 'ascending', 'wpv-views' );
		}
    }
    if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'taxonomy' ) {
		$order_by = '';
		switch( $view_settings['taxonomy_orderby'] ) {
			case 'count':
				$order_by = __('term count', 'wpv-views');
				break;
			case 'name':
				$order_by = __('term name', 'wpv-views');
				break;
			case 'slug':
				$order_by = __('term slug', 'wpv-views');
				break;
			case 'term_group':
				$order_by = __('term group', 'wpv-views');
				break;
			case 'none':
				$order_by = __('no specific criteria', 'wpv-views');
				break;
		}
		$order = __('descending', 'wpv-views');
		if ( $view_settings['taxonomy_order'] == 'ASC' ) {
			$order = __( 'ascending', 'wpv-views' );
		}
    }
    if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'users' ) {
		$order_by = '';
		switch( $view_settings['users_orderby'] ) {
			case 'user_login':
				$order_by = __('user login', 'wpv-views');
				break;
			case 'ID':
				$order_by = __('user ID', 'wpv-views');
				break;
			case 'user_name':
				$order_by = __('user name', 'wpv-views');
				break;
			case 'display_name':
				$order_by = __('display name', 'wpv-views');
				break;
			case 'user_nicename':
				$order_by = __('user nicename', 'wpv-views');
				break;
			case 'user_email':
				$order_by = __('user email', 'wpv-views');
				break;
			case 'user_url':
				$order_by = __('user url', 'wpv-views');
				break;
			case 'user_registered':
				$order_by = __('user registered date', 'wpv-views');
				break;
			case 'post_count':
				$order_by = __('user post count', 'wpv-views');
				break;
		}
		$order = __('descending', 'wpv-views');
		if ( $view_settings['users_order'] == 'ASC' ) {
			$order = __( 'ascending', 'wpv-views' );
		}
	}
	if ( $context == 'embedded-info' ) {
		$return .= sprintf( __( 'ordered by <strong>%s</strong> in <strong>%s</strong> order', 'wpv-views' ), $order_by, $order );
	} else {
		$return .= sprintf( __( ' ordered by %s, %s', 'wpv-views' ), $order_by, $order );
	}
	return $return;
}

/**
* wpv_get_limit_offset_summary
*
* Returns the limit and offset summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since 1.6.0
*/

function wpv_get_limit_offset_summary( $view_settings, $context = 'listing' ) {
	$view_settings = wpv_limit_default_settings( $view_settings );
	$output = '';
	$limit = 0;
	$offset = 0;
	if ( !isset( $view_settings['query_type'] ) || ( isset($view_settings['query_type'] ) && $view_settings['query_type'][0] == 'posts' ) ) {
		$limit = intval( $view_settings['limit'] );
		$offset = intval( $view_settings['offset'] );
	}
	if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'taxonomy' ) {
		$limit = intval( $view_settings['taxonomy_limit'] );
		$offset = intval( $view_settings['taxonomy_offset'] );
	}
	if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'users' ) {
		$limit = intval( $view_settings['users_limit'] );
		$offset = intval( $view_settings['users_offset'] );
	}
	if ( $context == 'embedded-info' ) {
		if ( $limit > 0 || $offset > 0 ) {
			if ( $offset > 0 ) {
				$output .= sprintf( _n( 'First result skipped', 'First %d results skipped', $offset, 'wpv-views' ), $offset );
				if ( $limit > 0 ) {
					$output .= sprintf( _n( ', then one result loaded', ', then %d results loaded', $limit, 'wpv-views' ), $limit );
				}
			} else if ( $limit > 0 ) {
				$output .= sprintf( _n( 'First result loaded', 'First %d results loaded', $limit, 'wpv-views' ), $limit );
			}
		} else {
			$output .= __( 'All results loaded', 'wpv-views' );
		}
	} else {
		if ( $limit > 0 ) {
			$output .= sprintf( _n( ', limit to 1 item', ', limit to %d items', $limit, 'wpv-views' ), $limit );
		}
		if ( $offset > 0 ) {
			$output .= sprintf( _n( ', skip first item', ', skip %d items', $offset, 'wpv-views' ), $offset );
		}
	}
	return $output;
}

/**
* wpv_get_pagination_summary
*
* Returns the pagination summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since 1.6.2
*
* @todo add AJAX effect
*/

function wpv_get_pagination_summary( $view_settings, $context = 'listing' ) {
	$return = '';
	if ( isset( $view_settings['pagination'] ) && isset( $view_settings['pagination'][0] ) && $view_settings['pagination'][0] != 'disable' ) {
		$posts_per_page = 0;
		$pagination_type = '';
		$pagination_effect = '';
		if ( isset( $view_settings['pagination']['mode'] ) && $view_settings['pagination']['mode'] == 'paged' ) {
			$posts_per_page = intval( $view_settings['posts_per_page'] );
			if ( isset( $view_settings['ajax_pagination'] ) && isset( $view_settings['ajax_pagination'][0] ) && $view_settings['ajax_pagination'][0] == 'enable' ) {
				$pagination_type = 'ajax';
				$ajax_effects = array(
					'fade' => __('Fade', 'wpv-views'),
					'fadefast' => __('Fade', 'wpv-views'),
					'fadeslow' => __('Fade', 'wpv-views'),
					'slideh' => __('Slide horizontally', 'wpv-views'),
					'slidev' => __('Slide vertically', 'wpv-views'),
				);
				$selected_effect = isset( $view_settings['ajax_pagination']['style'] ) ? $view_settings['ajax_pagination']['style'] : 'none';
				$pagination_effect = isset( $ajax_effects[$selected_effect] ) ? $ajax_effects[$selected_effect] : '';
			} else {
				$pagination_type = 'manual';
			}
		} else if ( isset( $view_settings['pagination']['mode'] ) && $view_settings['pagination']['mode'] == 'rollover' && isset( $view_settings['rollover'] ) && isset( $view_settings['rollover']['posts_per_page'] ) ) {
			$posts_per_page = intval( $view_settings['rollover']['posts_per_page'] );
			$pagination_type = 'rollover';
			$rollover_effects = array(
				'fade' => __('Fade', 'wpv-views'),
				'slideleft' => __('Slide Left', 'wpv-views'),
				'slideright' => __('Slide Right', 'wpv-views'),
				'slideup' => __('Slide Up', 'wpv-views'),
				'slidedown' => __('Slide Down', 'wpv-views'),
			);
			$selected_effect = isset( $view_settings['rollover']['effect'] ) ? $view_settings['rollover']['effect'] : 'none';
			$pagination_effect = isset( $rollover_effects[$selected_effect] ) ? $rollover_effects[$selected_effect] : '';
		}
		if ( '' != $pagination_type ) {
			switch ( $pagination_type ) {
				case 'manual':
					if ( $context == 'embedded-info' ) {
						$return .= sprintf( _n( 'Manual pagination, 1 item per page', 'Manual pagination, %s items per page', $posts_per_page, 'wpv-views' ), $posts_per_page );
					} else {
						$return .= ', ' . sprintf( _n( '1 item per page with manual pagination', '%s items per page with manual pagination', $posts_per_page, 'wpv-views' ), $posts_per_page );
					}
					break;
				case 'ajax':
					if ( $context == 'embedded-info' ) {
						$return .= sprintf( _n( '%s, 1 item per page', '%s, %s items per page', $posts_per_page, 'wpv-views' ), $pagination_effect, $posts_per_page );
					} else {
						$return .= ', ' . sprintf( _n( '1 item per page with manual AJAX', '%s items per page with manual AJAX', $posts_per_page, 'wpv-views' ), $posts_per_page );
					}
					break;
				case 'rollover':
					if ( $context == 'embedded-info' ) {
						$return .= sprintf( _n( '%s automatically, 1 item per page', '%s automatically, %s items per page', $posts_per_page, 'wpv-views' ), $pagination_effect, $posts_per_page );
					} else {
						$return .= ', ' . sprintf( _n( '1 item per page with automatic AJAX', '%s items per page with automatic AJAX', $posts_per_page, 'wpv-views' ), $posts_per_page );
					}
					break;
			}
		}
	} else {
		if ( $context == 'embedded-info' ) {
			$return .= __( 'No pagination', 'wpv-views' );
		}
	}
	return $return;
}

/**
* ## Filter summaries ##
*/

/**
* wpv_get_filter_status_summary_txt
*
* Returns the status filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_status_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['post_status'] ) ) {
		return;
	} else {
		$selected = $view_settings['post_status'];
	}
	ob_start();
	if ( sizeof( $selected ) ) {
		if ( $short ) {
			_e( 'status of ', 'wpv-views' );
		} else {
			_e( 'Select posts with status of ', 'wpv-views' );
		}
		$first = true;
		foreach( $selected as $value ) {
			if ( $first ) {
				echo '<strong>' . $value . '</strong>';
				$first = false;
			} else {
				_e( ' or ', 'wpv-views' );
				echo '<strong>' . $value . '</strong>';
			}
		}
	} else { // !TODO review this wording: this filter is not applied and indeed disapears from the edit screen on save
		if ( $short ) {
			_e( 'any status.', 'wpv-views' );
		} else {
			_e( 'Do not apply any filter based on status.', 'wpv-views' );
		}
	}
	$data = ob_get_clean();
	return $data;
}

/**
* wpv_get_filter_author_summary_txt
*
* Returns the author filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_author_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['author_mode'] ) ) {
		return;
	}
	if ( isset( $_GET['post'] ) ) {
		$view_name = get_the_title( $_GET['post']);
	} else {
		if ( isset( $_GET['view_id'] ) ) {
			$view_name = get_the_title( $_GET['view_id'] );
		} else {
			$view_name = 'view-name';
		}
	}
	ob_start();
	switch ( $view_settings['author_mode'] ) {
		case 'current_user':
			_e( 'Select posts with the <strong>author</strong> the same as the <strong>current logged in user</strong>.', 'wpv-views' );
			break;
		case 'this_user':
			if ( isset( $view_settings['author_id'] ) && $view_settings['author_id'] > 0 ) {
				global $wpdb;
				$selected_author = $wpdb->get_var( $wpdb->prepare( "SELECT display_name FROM {$wpdb->users} WHERE ID=%d", $view_settings['author_id'] ) );
			} else {
				$selected_author = 'None';
			}
			echo sprintf( __( 'Select posts with <strong>%s</strong> as the <strong>author</strong>.', 'wpv-views'), $selected_author );
			break;
		case 'parent_view':
			_e( 'Select posts with the <strong>author set by the parent View</strong>.', 'wpv-views' );
			break;
		case 'current_page':
			_e( 'Select posts with the <strong>author the same as the current page</strong>.', 'wpv-views' );
			break;
		case 'by_url':
			if ( isset( $view_settings['author_url'] ) && '' != $view_settings['author_url'] ) {
				$url_author = $view_settings['author_url'];
			} else {
				$url_author = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			if ( isset( $view_settings['author_url_type'] ) && '' != $view_settings['author_url_type'] ) {
				$url_author_type = $view_settings['author_url_type'];
				switch ( $url_author_type ) {
					case 'id':
						$example = '1';
						break;
					case 'username':
						$example = 'admin';
						break;
				}
			} else {
				$url_author_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				$example = '';
			}
			echo sprintf( __( 'Select posts with the author\'s <strong>%s</strong> determined by the URL parameter <strong>"%s"</strong>', 'wpv-views' ), $url_author_type, $url_author );
			if ( '' != $example ) {
				echo sprintf( __( ' eg. yoursite/page-with-this-view/?<strong>%s</strong>=%s', 'wpv-views' ), $url_author, $example );
			}
			break;
		case 'shortcode':
			if ( isset( $view_settings['author_shortcode'] ) && '' != $view_settings['author_shortcode'] ) {
				$auth_short = $view_settings['author_shortcode'];
			} else {
				$auth_short = __( 'None', 'wpv-views' );
			}
			if ( isset( $view_settings['author_shortcode_type'] ) && '' != $view_settings['author_shortcode_type'] ) {
				$shortcode_author_type = $view_settings['author_shortcode_type'];
				switch ( $shortcode_author_type ) {
					case 'id':
						$example = '1';
						break;
					case 'username':
						$example = 'admin';
						break;
				}
			} else {
				$shortcode_author_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				$example = '';
			}
			echo sprintf( __( 'Select posts which author\'s <strong>%s</strong> is set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views' ), $shortcode_author_type, $auth_short );
			if ( '' != $example ) {
				echo sprintf( __( ' eg. [wpv-view name="%s" <strong>%s</strong>="%s"]', 'wpv-views' ), $view_name, $auth_short, $example );
			}
			break;
	}
	$data = ob_get_clean();
	if ( $short ) {
		// this happens on the Views table under Filter column
		if ( substr( $data, -1 ) == '.' ) {
			$data = substr( $data, 0, -1 );
		}
	}
	return $data;
}

/**
* wpv_get_filter_custom_field_summary_txt
*
* Returns the custom fields filter summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_custom_field_summary_txt( $view_settings ) {
	$result = '';
	if( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'posts' ) {
		$count = 0;
		foreach ( array_keys( $view_settings ) as $key ) {
			if ( strpos( $key, 'custom-field-' ) === 0 && strpos( $key, '_compare' ) === strlen( $key ) - strlen( '_compare' ) ) {
				$name = substr( $key, 0, strlen( $key ) - strlen( '_compare' ) );
				$count++;
				if ( $result != '' ) {
					if ( isset( $view_settings['custom_fields_relationship'] ) && $view_settings['custom_fields_relationship'] == 'OR' ) {
						$result .= __( ' OR', 'wpv-views' );
					} else {
						$result .= __( ' AND', 'wpv-views' );
					}
				}
				$result .= wpv_get_custom_field_summary( $name, $view_settings );
			}
		}
	}
	return $result;
}

/**
* wpv_get_custom_field_summary
*
* Returns each custom field filter summary for a View
*
* @param $type (string) custom-field-{field-slug}
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_custom_field_summary( $type, $view_settings = array() ) {
	$field_name = substr( $type, strlen( 'custom-field-' ) );
	//$args = array( 'name' => $field_name );
	$all_types_fields = get_option( 'wpcf-fields', array() );
	$field_nicename = '';
	if ( stripos( $field_name, 'wpcf-' ) === 0 ) {
		if ( isset( $all_types_fields[substr( $field_name, 5 )] ) && isset( $all_types_fields[substr( $field_name, 5 )]['name'] ) ) {
			$field_nicename = $all_types_fields[substr( $field_name, 5 )]['name'];
		} else {
			$field_nicename = $field_name;
		}
	} else if ( stripos( $field_name, 'views_woo_' ) === 0 ) {
		if ( isset( $all_types_fields[$field_name] ) && isset( $all_types_fields[$field_name]['name'] ) ) {
			$field_nicename = $all_types_fields[$field_name]['name'];
		} else {
			$field_nicename = $field_name;
		}
	} else {
		$field_nicename = $field_name;
	}
	// Check if the field is in a Types group - if not, register with the full $key
	if ( function_exists( 'wpcf_admin_fields_get_groups_by_field' ) ) {
		$g = '';
		foreach( wpcf_admin_fields_get_groups_by_field( $field_nicename ) as $gs ) {
			$g = $gs['name'];
		}
		$field_nicename = $g ? $field_nicename : $field_name;
	}
	ob_start();
	?>
	<span class="wpv-filter-multiple-summary-item">
	<strong><?php echo $field_nicename . ' ' . $view_settings[$type . '_compare'] . ' ' . str_replace( ',', ', ', $view_settings[$type . '_value'] ); ?></strong>
	</span>
	<?php
	$buffer = ob_get_clean();
	return $buffer;
}

/**
* wpv_get_filter_taxonomy_summary_txt
*
* Returns the taxonomies filter summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*
* @todo improve, avoid loading the taxonomies if there is no filter at all
*/

function wpv_get_filter_taxonomy_summary_txt( $view_settings ) {
	$result = '';
	$taxonomies = get_taxonomies( '', 'objects' );
	foreach ( $taxonomies as $category_slug => $category ) {
		$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;
		$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
		if ( isset( $view_settings[$relationship_name] ) ) {
			if ( !isset( $view_settings[$save_name] ) ) {
				$view_settings[$save_name] = array();
			}
			$name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input[' . $category->name . ']';
			if ( $result != '' ) {
				if ( $view_settings['taxonomy_relationship'] == 'OR' ) {
					$result .= __( ' OR ', 'wpv-views' );
				} else {
					$result .= __( ' AND ', 'wpv-views' );
				}
			}
			
			$result .= wpv_get_taxonomy_summary( $name, $view_settings, $view_settings[$save_name] );
				
		}
	}
	return $result;
}

/**
* wpv_get_taxonomy_summary
*
* Returns each taxonomy filter summary for a View
*
* @param $type (string) post_category | tax_input[{$category->name}]
* @param $view_settings
* @param $category_selected (array) selected terms when using IN or AND modes
*
* @returns (string) $summary
*
* @since unknown
*
* @todo improve this, we should not need to loop over all the taxes (we already checked all of this on the previous function, FGS
*/

function wpv_get_taxonomy_summary( $type, $view_settings, $category_selected ) {
	// find the matching category/taxonomy
	//$taxonomy = 'category';
	$taxonomy = '';
	$taxonomy_name = __( 'Categories', 'wpv-views' );
	$taxonomies = get_taxonomies( '', 'objects' );
	foreach ( $taxonomies as $category_slug => $category ) {
		$name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input[' . $category->name . ']';
		if ( $name == $type ) {
			// it's a category type.
			$taxonomy = $category->name;
			$taxonomy_name = $category->label;
			break;
		}
	}
	if ( '' == $taxonomy ) {
		return;
	}
	if ( !isset( $view_settings['tax_' . $taxonomy . '_relationship'] ) ) {
		$view_settings['tax_' . $taxonomy . '_relationship'] = 'IN';
	}
	if ( !isset( $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] ) ) {
		$view_settings['taxonomy-' . $taxonomy . '-attribute-url'] = '';
	}
	if ( !isset( $view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] ) ) {
		$view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] = 'IN';
	}
	$relationship = __( 'is <strong>One</strong> of these', 'wpv-views' );
	switch ( $view_settings['tax_' . $taxonomy . '_relationship'] ) {
		case "AND":
			$relationship = __( 'is <strong>All</strong> of these', 'wpv-views' );
			break;
		case "NOT IN":
			$relationship = __( 'is <strong>Not one</strong> of these', 'wpv-views' );
			break;
		case "FROM PAGE":
			$relationship = __( 'the same as the <strong>current page</strong>', 'wpv-views' );
			break;
		case "FROM ATTRIBUTE":
			$relationship = __( 'set by the View shortcode attribute ', 'wpv-views' );
			break;
		case "FROM URL":
			$relationship = __( 'set by the URL parameter ', 'wpv-views' );
			break;
		case "FROM PARENT VIEW":
			$relationship = ', ' . __( 'set by the parent view.', 'wpv-views' );
			break;
	}
	ob_start();
	echo '<span class="wpv-filter-multiple-summary-item">';
	if ( $view_settings['tax_' . $taxonomy . '_relationship'] == "FROM PAGE" ) {
		echo '<strong>' . $taxonomy_name . ' </strong>' . $relationship;
	} else if ( $view_settings['tax_' . $taxonomy . '_relationship'] == "FROM ATTRIBUTE" || $view_settings['tax_' . $taxonomy . '_relationship'] == "FROM URL" ) {
		echo '<strong>' . $taxonomy_name . ' </strong>' . $relationship;
		echo '<strong>"' . $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] . '"</strong> ';
		echo __( 'using the operator', 'wpv-views' ) . ' <strong>' . $view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] .  '</strong> ';
		if ( $view_settings['tax_' . $taxonomy . '_relationship'] == "FROM ATTRIBUTE" ) {
			echo '<br /><code>' . sprintf( __( 'eg. [wpv-view name="view-name" <strong>%s="xxxx"</strong>]', 'wpv-views' ), $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] ) . '</code>';
		} else {
			echo '<br /><code>' . sprintf( __( 'eg. http://www.example.com/page/?<strong>%s=xxxx</strong>', 'wpv-views' ), $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] ) . '</code>';
		}
	} else if ( $view_settings['tax_' . $taxonomy . '_relationship'] == "FROM PARENT VIEW" ) {
		echo '<strong>' . $taxonomy_name . ' </strong>' . $relationship;
	} else {
		?>
		<strong><?php echo $taxonomy_name . ' </strong>' . $relationship . ' <strong>(';
		$cat_text = '';
		foreach ( $category_selected as $cat ) {
			$term = get_term( $cat, $taxonomy );
			if ( $term ) {
				if ( $cat_text != '' ) {
					$cat_text .= ', ';
				}
				$cat_text .= $term->name;
			}
		}
		echo $cat_text;
		?>)</strong>
		<?php
	}
	echo '</span>';
	$buffer = ob_get_clean();
	return $buffer;
}

/**
* wpv_get_filter_post_relationship_summary_txt
*
* Returns the post relationship filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_post_relationship_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['post_relationship_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['post_relationship_mode'] ) ) {
		$view_settings['post_relationship_mode'] = $view_settings['post_relationship_mode'][0];
	}
	if ( !isset( $view_settings['post_relationship_shortcode_attribute'] ) ) {
		$view_settings['post_relationship_shortcode_attribute'] = '';
	}
	if ( !isset( $view_settings['post_relationship_url_parameter'] ) ) {
		$view_settings['post_relationship_url_parameter'] = '';
	}
	ob_start();
	if ( $view_settings['post_relationship_mode'] == 'current_page' ) {
		_e( 'Select posts that are <strong>children</strong> of the <strong>Post where this View is inserted</strong>.', 'wpv-views' );
	} else if ( $view_settings['post_relationship_mode'] == 'parent_view' ) {
		_e( 'Select posts that are a <strong>children</strong> of the <strong>Post set by parent View</strong>.', 'wpv-views' );
	} else if ( $view_settings['post_relationship_mode'] == 'shortcode_attribute' ) {
		echo sprintf( __( 'Select posts that are <strong>children</strong> of the <strong>Post with ID set by the shortcode attribute %s</strong>.', 'wpv-views' ), $view_settings['post_relationship_shortcode_attribute'] );
		echo '<br /><code>' . sprintf( __( ' eg. [wpv-view name="view-name" <strong>%s="123"</strong>]', 'wpv-views' ), $view_settings['post_relationship_shortcode_attribute'] ) . '</code>';
	} else if ( $view_settings['post_relationship_mode'] == 'url_parameter' ) {
		echo sprintf( __( 'Select posts that are <strong>children</strong> of the <strong>Post with ID set by the URL parameter %s</strong>.', 'wpv-views' ), $view_settings['post_relationship_url_parameter'] );
		echo '<br /><code>' . sprintf( __( ' eg. http://www.example.com/my-page/?<strong>%s=123</strong>', 'wpv-views' ), $view_settings['post_relationship_url_parameter'] ) . '</code>';
	} else {
		if ( isset( $view_settings['post_relationship_id'] ) && $view_settings['post_relationship_id'] > 0) {
			global $wpdb;
			$selected_title = $wpdb->get_var( $wpdb->prepare( "SELECT post_title FROM {$wpdb->posts} WHERE ID=%d", $view_settings['post_relationship_id'] ) );
		} else {
			$selected_title = 'None';
		}
		echo sprintf( __( 'Select posts that are children of <strong>%s</strong>.', 'wpv-views' ), $selected_title );
	}
	$data = ob_get_clean();
	if ( $short ) {
		if ( substr( $data, -1 ) == '.' ) {
			$data = substr( $data, 0, -1 );
		}
	}
	return $data;
}

/**
* wpv_get_filter_id_summary_txt
*
* Returns the post id filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_id_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['id_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['id_mode'] ) ) {
		$view_settings['id_mode'] = $view_settings['id_mode'][0];
	}
	if ( isset( $_GET['post'] ) ) {
		$view_name = get_the_title( $_GET['post'] );
	} else {
		$view_name = 'view-name';
	}
	$defaults = array(
		'id_in_or_out' => 'in',
		'id_mode' => 'by_ids',
		'post_id_ids_list' =>'',
		'post_ids_url' => 'post_ids',
		'post_ids_shortcode' => 'ids'
	);
	$view_settings = wp_parse_args( $view_settings, $defaults );
	ob_start();
	switch ( $view_settings['id_in_or_out'] ) {
		case 'in':
			echo __( 'Include only posts ', 'wpv-views' );
			break;
		case 'out':
			echo __( 'Exclude posts ', 'wpv-views' );
			break;
	}
	switch ( $view_settings['id_mode'] ) {
		case 'by_ids':
			if ( isset( $view_settings['post_id_ids_list'] ) && '' != $view_settings['post_id_ids_list'] ) {
				$ids_list = $view_settings['post_id_ids_list'];
			} else {
				$ids_list = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			echo sprintf( __( 'with the following <strong>IDs</strong>: %s', 'wpv-views' ), $ids_list );
			break;
		case 'by_url':
			if ( isset( $view_settings['post_ids_url'] ) && '' != $view_settings['post_ids_url'] ) {
				$url_ids = $view_settings['post_ids_url'];
			} else {
				$url_ids = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			echo sprintf( __( 'with IDs determined by the URL parameter <strong>"%s"</strong>', 'wpv-views' ), $url_ids );
			echo sprintf( __( ' eg. yoursite/page-with-this-view/?<strong>%s</strong>=1', 'wpv-views' ), $url_ids );
			break;
		case 'shortcode':
			if ( isset( $view_settings['post_ids_shortcode'] ) && '' != $view_settings['post_ids_shortcode'] ) {
				$id_short = $view_settings['post_ids_shortcode'];
			} else {
				$id_short = __( 'None', 'wpv-views' );
			}
			echo sprintf( __( 'with IDs is set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views' ), $id_short );
			echo sprintf( __( ' eg. [wpv-view name="%s" <strong>%s</strong>="1"]', 'wpv-views' ), $view_name, $id_short );
			break;
		}
	$data = ob_get_clean();
	if ( $short ) {
		// this happens on the Views table under Filter column
		if ( substr( $data, -1 ) == '.' ) {
			$data = substr( $data, 0, -1 );
		}
	}
	return $data;
}

/**
* wpv_get_filter_parent_summary_txt
*
* Returns the parent filter summary for a View
*
* @param $view_settings
* @param $short (bool) maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_parent_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['parent_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['parent_mode'] ) ) {
		$view_settings['parent_mode'] = $view_settings['parent_mode'][0];
	}
	ob_start();
	if ( $view_settings['parent_mode'] == 'current_page' ) {
		if ( $short ) {
			_e( 'parent is the <strong>current page</strong>', 'wpv-views' );
		} else {
			_e( 'Select posts whose parent is the <strong>current page</strong>.', 'wpv-views' );
		}
	} else {
		if ( isset( $view_settings['parent_id'] ) && $view_settings['parent_id'] > 0 ) {
			global $wpdb;
			$selected_title = $wpdb->get_var( $wpdb->prepare( "SELECT post_title FROM {$wpdb->posts} WHERE ID=%d", $view_settings['parent_id'] ) );
		} else {
			$selected_title = __( 'None', 'wpv-views' );
		}
		if ( $short ) {
			echo sprintf( __( 'parent is <strong>%s</strong>', 'wpv-views' ), $selected_title );
		} else {
			echo sprintf( __( 'Select posts whose parent is <strong>%s</strong>.', 'wpv-views' ), $selected_title );
		}
	}
	$data = ob_get_clean();
	return $data;
}

/**
* wpv_get_filter_taxonomy_parent_summary_txt
*
* Returns the taxonomy parent filter summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_taxonomy_parent_summary_txt( $view_settings ) {
	global $sitepress;	
	if ( !isset( $view_settings['taxonomy_type'] ) ) {
		return;
	} elseif ( is_array( $view_settings['taxonomy_type'] ) && sizeof( $view_settings['taxonomy_type'] ) > 0 ) {
		$view_settings['taxonomy_type'] = $view_settings['taxonomy_type'][0];
		if ( ! taxonomy_exists( $view_settings['taxonomy_type'] ) ) {
			return;
		}
	}
	if ( !isset( $view_settings['taxonomy_parent_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['taxonomy_parent_mode'] ) ) {
		$view_settings['taxonomy_parent_mode'] = $view_settings['taxonomy_parent_mode'][0];
	}
	if ( isset($sitepress) && function_exists('icl_object_id') && isset( $view_settings['taxonomy_parent_id'] ) && !empty( $view_settings['taxonomy_parent_id'] ) ) {
		// Adjust for WPML support
		$view_settings['taxonomy_parent_id'] = icl_object_id( $view_settings['taxonomy_parent_id'], $view_settings['taxonomy_type'], true );
	}
	ob_start();
	if ( $view_settings['taxonomy_parent_mode'] == 'current_view' ) {
		_e( 'Select taxonomy whose parent is the value set by the <strong>parent view</strong>.', 'wpv-views' );
	} else {
		if ( isset( $view_settings['taxonomy_parent_id'] ) && $view_settings['taxonomy_parent_id'] > 0 ) {
			$selected_taxonomy = get_term( $view_settings['taxonomy_parent_id'], $view_settings['taxonomy_type'] );
			if ( null ==  $selected_taxonomy ) { // TODO Review this
				$selected_taxonomy = __( 'None', 'wpv-views' );
			} else {
				$selected_taxonomy = $selected_taxonomy->name;
			}
		} else {
			$selected_taxonomy = __( 'None', 'wpv-views' );
		}
		echo sprintf( __( 'Select taxonomy whose parent is <strong>%s</strong>.', 'wpv-views' ), $selected_taxonomy );
	}
	$data = ob_get_clean();
	return $data;
}

/**
* wpv_get_filter_search_summary_txt
*
* Returns the search filter summary for a View
*
* @param $view_settings
* @param $short maybe DEPRECATED
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_search_summary_txt( $view_settings, $short = false ) {
	if ( !isset( $view_settings['search_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['search_mode'] ) ) {
		$view_settings['search_mode'] = $view_settings['search_mode'][0];
	}
	if ( !isset( $view_settings['post_search_value'] ) ) {
		$view_settings['post_search_value'] = '';
	}
	ob_start();
	switch ( $view_settings['search_mode'] ) {
		case 'specific':
			$term = $view_settings['post_search_value'];
			if ( $term == '' ) {
				$term = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			if ( $short ) {
				echo sprintf( __( 'Filter by <strong>search</strong> term: <strong>%s</strong>', 'wpv-views' ), $term );
			} else {
				echo sprintf( __( 'Filter by this search term: <strong>%s</strong>.', 'wpv-views' ), $term );
			}
			break;
		case 'visitor':
			if ( $short ) {
				echo __( 'Show a <strong>search box</strong> for visitors', 'wpv-views' );
			} else {
				echo __( 'Show a <strong>search box</strong> for visitors.', 'wpv-views' );
			}
			break;
		case 'manual':
			if ( $short ) {
				echo __( 'Filter by <strong>search box</strong>', 'wpv-views' );
			} else {
				echo __( 'The search box will be added <strong>manually</strong>.<br /><code>eg. [wpv-filter-search-box]</code>', 'wpv-views' );
			}
			break;
	}
	$data = ob_get_clean();
	return $data;
}

/**
* wpv_get_filter_taxonomy_search_summary_txt
*
* Returns the taxonomy search filter summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_taxonomy_search_summary_txt( $view_settings ) {
	if ( !isset( $view_settings['taxonomy_search_mode'] ) ) {
		return;
	} elseif ( is_array( $view_settings['taxonomy_search_mode'] ) ) {
		$view_settings['taxonomy_search_mode'] = $view_settings['taxonomy_search_mode'][0];
	}
	if ( !isset( $view_settings['taxonomy_search_value'] ) ) {
		$view_settings['taxonomy_search_value'] = '';
	}
	ob_start();
	switch ( $view_settings['taxonomy_search_mode'] ) {
		case 'specific':
			$term = $view_settings['taxonomy_search_value'];
			if ( $term == '' ) {
				$term = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			echo sprintf( __( 'Filter by this search term: <strong>%s</strong>.', 'wpv-views' ), $term );
			break;
		case 'visitor':
			echo __( 'Show a <strong>search box</strong> for visitors.', 'wpv-views' );
			break;
		case 'manual':
			echo __( 'The search box will be added <strong>manually</strong>.<br /><code>eg. [wpv-filter-search-box]</code>', 'wpv-views' );
			break;
	}
	$data = ob_get_clean();
	return $data;
}

/**
* wpv_get_filter_taxonomy_term_summary_txt
*
* Returns the taxonomy term filter summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_taxonomy_term_summary_txt( $view_settings ) {
	global $sitepress;	
	if ( !isset( $view_settings['taxonomy_type'] ) ) {
		return;
	} elseif ( is_array( $view_settings['taxonomy_type'] ) ) {
		$view_settings['taxonomy_type'] = $view_settings['taxonomy_type'][0];
		if ( ! taxonomy_exists( $view_settings['taxonomy_type'] ) ) {
			return;
		}
	}
	if ( !isset( $view_settings['taxonomy_terms_mode'] ) ) {
		return;
	}
	if ( !isset( $view_settings['taxonomy_terms'] ) ) {
		$view_settings['taxonomy_terms'] = array();
	}
	if ( isset($sitepress) && function_exists('icl_object_id') && !empty( $view_settings['taxonomy_terms'] ) ) {
	// Adjust for WPML support
		$trans_term_ids = array();
		foreach ( $view_settings['taxonomy_terms'] as $untrans_term_id ) {
			$trans_term_ids[] = icl_object_id( $untrans_term_id, $view_settings['taxonomy_type'], true );
		}
		$view_settings['taxonomy_terms'] = $trans_term_ids;
	}
	ob_start();
	if ( $view_settings['taxonomy_terms_mode'] == 'THESE' ) {
		echo __( 'Taxonomy is <strong>One</strong> of these', 'wpv-views' );
		echo '<strong> (';
		$cat_text = '';
		$category_selected = $view_settings['taxonomy_terms'];
		$taxonomy = $view_settings['taxonomy_type'];
		foreach ( $category_selected as $cat ) {
			$term_check = term_exists( (int) $cat, $taxonomy );
			if ( $term_check !== 0 && $term_check !== null ) {
				$term = get_term( $cat, $taxonomy );
				if ( $cat_text != '' ) {
					$cat_text .= ', ';
				}
				$cat_text .= $term->name;
			}
		}
		echo $cat_text;
		echo ')</strong>';
	} else if ( $view_settings['taxonomy_terms_mode'] == 'CURRENT_PAGE' ) {
		echo __( 'Taxonomy is set by the current page', 'wpv-views' );
	}
	$data = ob_get_clean();
	return $data;
}

/**
* wpv_get_filter_users_summary_txt
*
* Returns the users filter summary for a View
*
* @param $view_settings
* @param $short maybe DEPRECATED
* @param $post_id
*
* @returns (string) $summary
*
* @since unknown
*
* @todo check where this $post_id comes from
* @todo check where all those $_GET and $_POST are coming from
*/

function wpv_get_filter_users_summary_txt( $view_settings, $short=false, $post_id='' ) {
	if ( isset( $_GET['post'] ) ) {
		$view_name = get_the_title( $_GET['post'] );
	} else {
		if ( isset( $_GET['view_id'] ) ) {
			$view_name = get_the_title( $_GET['view_id'] );
		} else {
			$view_name = 'view-name';
		}
	}
	if ( !isset( $view_settings['users_mode'] ) ) {
        return;
    }elseif ( is_array( $view_settings['users_mode'] ) ) {
		$view_settings['users_mode'] = $view_settings['users_mode'][0];
	}
	ob_start();
	if ( isset( $_GET['view_id'] ) ) {
		$_view_settings = get_post_meta( $_GET['view_id'], '_wpv_settings', true );
	}
	if ( isset( $_POST['id'] ) ) {
		$_view_settings = get_post_meta( $_POST["id"], '_wpv_settings', true );
	}
    if ( !isset( $_view_settings ) && !empty( $post_id ) ) {
        $_view_settings = get_post_meta( $post_id, '_wpv_settings', true );
    }
	if ( isset( $view_settings['roles_type'][0] ) ) {
		$user_role = $view_settings['roles_type'][0];
	} else {
		$user_role = $_view_settings['roles_type'][0];
	}
	if ( !isset( $user_role ) ) {
		$user_role = 'administrator';
	}
	switch ( $view_settings['users_mode'] ) {
		case 'this_user':
			if ( isset( $view_settings['users_id'] ) && $view_settings['users_id'] > 0 ) {
				if ( $view_settings['users_query_in'] == 'include' ) {
					echo sprintf( __( 'Select users <strong>(%s)</strong> who have role <strong>%s</strong>', 'wpv-views' ), $_view_settings['users_name'], $user_role );
				} else {
					echo sprintf( __( 'Select all users with role <strong>%s</strong>, except of <strong>(%s)</strong>', 'wpv-views' ), $user_role , $_view_settings['users_name'] );
				}
			} else {
				echo sprintf( __( 'Select all users with role <strong>%s</strong>', 'wpv-views' ), $user_role );
			}
			break;
		case 'by_url':
			if ( isset( $view_settings['users_url'] ) && '' != $view_settings['users_url'] ) {
				$url_users = $view_settings['users_url'];
			} else {
				$url_users = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			}
			if ( isset( $view_settings['users_url_type'] ) && '' != $view_settings['users_url_type'] ) {
				$url_users_type = $view_settings['users_url_type'];
				switch ( $url_users_type ) {
					case 'id':
						$example = '1';
						break;
					case 'username':
						$example = 'admin';
						break;
				}
			} else {
				$url_users_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
				$example = '';
			}

			if ( $view_settings['users_query_in'] == 'include' ) {
				echo sprintf( __( 'Select users with the <strong>%s1</strong> determined by the URL parameter <strong>"%s2"</strong> and with role <strong>"%s3"</strong>', 'wpv-views' ), $url_users_type, $url_users, $user_role );
			} else {
				echo sprintf( __( 'Select all users with role <strong>%s1</strong>, except of <strong>%s2</strong> determined by the URL parameter <strong>"%s3"</strong>', 'wpv-views' ), $user_role, $url_users_type, $url_users );
			}
			if ( '' != $example ) {
				echo '<br /><code>' . sprintf( __( ' eg. yoursite/page-with-this-view/?<strong>%s</strong>=%s', 'wpv-views' ), $url_users, $example ) . '</code>';
			}
			break;
	case 'shortcode':
		if ( isset( $view_settings['users_shortcode'] ) && '' != $view_settings['users_shortcode'] ) {
			$auth_short = $view_settings['users_shortcode'];
		} else {
			$auth_short = __( 'None', 'wpv-views' );
		}
		if ( isset( $view_settings['users_shortcode_type'] ) && '' != $view_settings['users_shortcode_type'] ) {
			$shortcode_users_type = $view_settings['users_shortcode_type'];
			switch ( $shortcode_users_type ) {
				case 'id':
					$example = '1';
					break;
				case 'username':
					$example = 'admin';
					break;
			}
		} else {
			$shortcode_users_type = '<i>' . __( 'None set', 'wpv-views' ) . '</i>';
			$example = '';
		}
		if ( $view_settings['users_query_in'] == 'include' ) {
			echo sprintf( __( 'Select users with <strong>%s</strong> set by the View shortcode attribute <strong>"%s"</strong> and with role <strong>"%s"</strong>', 'wpv-views' ), $shortcode_users_type, $auth_short, $user_role );
		} else {
			echo sprintf( __( 'Select all users with role <strong>%s</strong>, except of <strong>%s</strong> set by the View shortcode attribute <strong>"%s"</strong>', 'wpv-views' ), $user_role, $shortcode_users_type, $auth_short );
		}
		if ( '' != $example ) {
			echo '<br /><code>' . sprintf( __( ' eg. [wpv-view name="%s" <strong>%s</strong>="%s"]', 'wpv-views' ), $view_name, $auth_short, $example ) . '</code>';
		}
		break;
	}
	$data = ob_get_clean();
	if ( $short ) {
		// this happens on the Views table under Filter column
		if ( substr( $data, -1 ) == '.' ) {
			$data = substr( $data, 0, -1 );
		}
	}
	return $data;
}

/**
* wpv_get_filter_usermeta_field_summary_txt
*
* Returns the usermeta fields filter summary for a View
*
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_filter_usermeta_field_summary_txt( $view_settings ) {
	$result = '';
	if ( isset( $view_settings['query_type'] ) && $view_settings['query_type'][0] == 'users' ) {
		$count = 0;
		foreach ( array_keys( $view_settings ) as $key ) {
			if ( strpos( $key, 'usermeta-field-' ) === 0 && strpos( $key, '_compare' ) === strlen( $key ) - strlen( '_compare' ) ) {
				$name = substr( $key, 0, strlen( $key ) - strlen( '_compare' ) );
				$count++;
				if ( $result != '' ) {
					if ( isset( $view_settings['usermeta_fields_relationship'] ) && $view_settings['usermeta_fields_relationship'] == 'OR' ) {
						$result .= __( ' OR', 'wpv-views' );
					} else {
						$result .= __( ' AND', 'wpv-views' );
					}
				}
				$result .= wpv_get_usermeta_field_summary( $name, $view_settings );
			}
		}
	}
	return $result;
}

/**
* wpv_get_usermeta_field_summary
*
* Returns each usermeta field filter summary for a View
*
* @paran $type (string) usermeta-field-{$field-name}
* @param $view_settings
*
* @returns (string) $summary
*
* @since unknown
*/

function wpv_get_usermeta_field_summary( $type, $view_settings = array() ) {
	$field_name = substr( $type, strlen( 'usermeta-field-' ) );
	//$args = array( 'name' => $field_name );
	$all_types_fields = get_option( 'wpcf-fields', array() );
	$field_nicename = '';
	if ( stripos( $field_name, 'wpcf-' ) === 0 ) {
		if ( isset( $all_types_fields[substr( $field_name, 5 )] ) && isset( $all_types_fields[substr( $field_name, 5 )]['name'] ) ) {
			$field_nicename = $all_types_fields[substr( $field_name, 5 )]['name'];
		} else {
			$field_nicename = $field_name;
		}
	} else {
		$field_nicename = $field_name;
	}
	ob_start();
	?>
	<span class="wpv-filter-multiple-summary-item">
	<strong><?php echo $field_nicename . ' ' . $view_settings[$type . '_compare'] . ' ' . str_replace( ',', ', ', $view_settings[$type . '_value'] ); ?></strong>
	</span>
	<?php
	$buffer = ob_get_clean();
	return $buffer;
}