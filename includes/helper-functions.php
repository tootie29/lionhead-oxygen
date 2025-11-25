<?php
/**
 * Case Result Helper Functions
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get case result category name for a post
 *
 * @param int|WP_Post|null $post Post ID, post object, or null for current post.
 * @return string Category name or empty string.
 */
function lhd_get_case_result_category_name( $post = null ) {
	$post_id = $post ? ( is_object( $post ) ? $post->ID : (int) $post ) : get_the_ID();

	$terms = get_the_terms( $post_id, 'case_result_category' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	return $terms[0]->name;
}

/**
 * Get case type name for a post
 *
 * @param int|WP_Post|null $post Post ID, post object, or null for current post.
 * @return string Case type name or empty string.
 */
function lhd_get_case_type_name( $post = null ) {
	if ( ! $post ) {
		$post_id = get_the_ID();
	} elseif ( is_object( $post ) && isset( $post->ID ) ) {
		$post_id = $post->ID;
	} else {
		$post_id = (int) $post;
	}

	$terms = get_the_terms( $post_id, 'case_type' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}

	return $terms[0]->name;
}

/**
 * Get permalink for a specific post ID
 *
 * @param int|WP_Post|null $post_id Post ID, post object, or null for current post.
 * @param bool              $leavename Optional. Whether to keep post name. Default false.
 * @return string|false The permalink URL or false if post does not exist.
 */
function lhd_get_permalink( $post_id = null, $leavename = false ) {
	// If no post ID provided, use current post
	if ( $post_id === null ) {
		$post_id = get_the_ID();
	}
	
	// Handle post object
	if ( is_object( $post_id ) && isset( $post_id->ID ) ) {
		$post_id = $post_id->ID;
	}
	
	// Ensure we have a valid post ID
	$post_id = (int) $post_id;
	if ( $post_id <= 0 ) {
		return false;
	}
	
	// Get the permalink
	$permalink = get_permalink( $post_id, $leavename );
	
	return $permalink;
}

/**
 * Get post ID for a specific post
 *
 * @param int|WP_Post|null $post Post ID, post object, or null for current post.
 * @return int|false The post ID or false if post does not exist.
 */
function lhd_get_post_id( $post = null ) {
	// If no post provided, try to get current post ID
	if ( $post === null ) {
		// Try get_queried_object_id() first (works for posts, pages, and custom post types)
		$post_id = get_queried_object_id();
		
		// If not found, try get_the_ID()
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}
		
		// If still not found, return false
		if ( ! $post_id ) {
			return false;
		}
		
		return (int) $post_id;
	}
	
	// Handle post object
	if ( is_object( $post ) && isset( $post->ID ) ) {
		$post_id = (int) $post->ID;
		if ( $post_id <= 0 ) {
			return false;
		}
		return $post_id;
	}
	
	// Handle post ID (integer or string)
	$post_id = (int) $post;
	if ( $post_id <= 0 ) {
		return false;
	}
	
	// Verify the post exists
	if ( ! get_post( $post_id ) ) {
		return false;
	}
	
	return $post_id;
}

// Backward compatibility aliases
if ( ! function_exists( 'get_case_result_category_name' ) ) {
	/**
	 * Backward compatibility alias for get_case_result_category_name
	 *
	 * @param int|WP_Post|null $post Post ID, post object, or null for current post.
	 * @return string Category name or empty string.
	 */
	function get_case_result_category_name( $post = null ) {
		return lhd_get_case_result_category_name( $post );
	}
}

if ( ! function_exists( 'get_case_type_name' ) ) {
	/**
	 * Backward compatibility alias for get_case_type_name
	 *
	 * @param int|WP_Post|null $post Post ID, post object, or null for current post.
	 * @return string Case type name or empty string.
	 */
	function get_case_type_name( $post = null ) {
		return lhd_get_case_type_name( $post );
	}
}

function rm_get_the_id() {
	return get_the_ID();
}

/**
 * Get current location for a post
 *
 * @param int|WP_Post|null $post Post ID, post object, or null for current post.
 * @return string Location value or empty string.
 */
function lhd_get_current_location( $post = null ) {
	$post_id = get_queried_object_id();
	
	$locations = get_field('location', $post_id);
	
	if ($locations) {
		foreach ( $locations as $location ) {
			$label = $field_object['choices'][$location];
			return $location;
		}
	}
	
	return '';
}

/**
 * Get all unique location_page values from all pages
 * Uses transient caching to avoid expensive queries on every call
 *
 * @return array Array of unique location_page values.
 */
function lhd_get_all_location_pages() {
	// Check cache first (1 hour cache)
	$cache_key = 'lhd_all_location_pages';
	$cached = get_transient( $cache_key );
	
	if ( $cached !== false ) {
		return $cached;
	}
	
	$location_pages = [];
	
	// Query all pages using WP_Query
	$query = new WP_Query( array(
		'post_type'      => 'page',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		'fields'         => 'ids', // Only get IDs for better performance
	) );
	
	// Loop through page IDs
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $page_id ) {
			// Get location_page field (ACF select field can return array or single value)
			$location_page = get_field( 'location_page', $page_id );
			
			// Skip if empty, null, or false
			if ( empty( $location_page ) ) {
				continue;
			}
			
			// Handle arrays (multiple selected values)
			if ( is_array( $location_page ) ) {
				// Merge array values into main array
				$location_pages = array_merge( $location_pages, $location_page );
			} else {
				// Single value - add it to array
				$location_pages[] = $location_page;
			}
		}
		
		wp_reset_postdata();
	}
	
	// Filter out empty values
	$location_pages = array_filter( $location_pages );
	
	// Remove duplicates
	$location_pages = array_unique( $location_pages );
	
	// Re-index array
	$location_pages = array_values( $location_pages );
	
	// Cache for 1 hour
	set_transient( $cache_key, $location_pages, HOUR_IN_SECONDS );
	
	return $location_pages;
}

/**
 * Clear location_pages cache when a page is saved
 * COMPLETELY DISABLED during Oxygen builder saves to prevent slow saves
 * 
 * Cache will be automatically cleared when cache expires (1 hour) or manually via:
 * delete_transient( 'lhd_all_location_pages' );
 *
 * @param int $post_id Post ID.
 */
function lhd_clear_location_pages_cache( $post_id ) {
	// FASTEST CHECK FIRST: Skip autosaves and revisions (Oxygen does many autosaves)
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	
	// FASTEST OXYGEN DETECTION: Check for ct_builder_shortcodes first (most common)
	if ( isset( $_POST['ct_builder_shortcodes'] ) || isset( $_POST['ct_inner_content'] ) ) {
		return;
	}
	
	// Use lightweight post type check (faster than get_post())
	$post_type = get_post_type( $post_id );
	if ( $post_type !== 'page' ) {
		return;
	}
	
	// Additional Oxygen checks (only if we got this far)
	if ( ( isset( $_REQUEST['action'] ) && strpos( $_REQUEST['action'], 'oxygen' ) !== false ) ||
		 ( defined( 'CT_VERSION' ) && isset( $_GET['ct_builder'] ) ) ) {
		return;
	}
	
	// Only clear cache on actual publish/update outside of builder
	delete_transient( 'lhd_all_location_pages' );
}
// Use lower priority to run after other save_post hooks
add_action( 'save_post', 'lhd_clear_location_pages_cache', 99 );
add_action( 'delete_post', 'lhd_clear_location_pages_cache', 99 );

