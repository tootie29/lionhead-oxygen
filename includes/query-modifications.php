<?php
/**
 * Query Variables & Custom Query Modifications
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register custom query variables
 */
function lhd_register_query_vars( $vars ) {
	$vars[] = 'case_result_category';
	$vars[] = 'target_case_category';
	$vars[] = 'title_number_first';
	$vars[] = 'no_found_rows';
	return $vars;
}
add_filter( 'query_vars', 'lhd_register_query_vars' );

/**
 * Modify query to support numeric title sorting for case results
 */
function lhd_custom_title_number_first_query( WP_Query $query ) {
	if ( is_admin() ) {
		return;
	}

	// Get query parameters - works for both main query and custom queries
	$tnf = $query->get( 'title_number_first' );
	if ( ! $tnf && $query->is_main_query() ) {
		$tnf = get_query_var( 'title_number_first' );
	}

	// If title_number_first is not set, don't modify the query
	if ( ! $tnf ) {
		return;
	}

	// Get other query parameters
	$pt      = $query->get( 'post_type' );
	$term_id = $query->get( 'target_case_category' );
	$nfrows  = $query->get( 'no_found_rows' );

	// For main query, also check query vars from URL
	if ( $query->is_main_query() ) {
		if ( ! $pt ) {
			$pt = get_query_var( 'post_type' );
		}
		if ( ! $term_id ) {
			$term_id = get_query_var( 'target_case_category' );
		}
		if ( ! $nfrows ) {
			$nfrows = get_query_var( 'no_found_rows' );
		}
	}

	// Check if post_type is case_result
	$post_type_match = false;
	if ( is_array( $pt ) ) {
		$post_type_match = in_array( 'case_result', $pt, true );
	} else {
		$post_type_match = ( 'case_result' === $pt );
	}

	if ( ! $post_type_match ) {
		return;
	}

	$query->set( 'post_type', 'case_result' );

	// Set tax_query if target_case_category is provided
	if ( $term_id ) {
		$term_id = absint( $term_id );
		$query->set(
			'tax_query',
			array(
				array(
					'taxonomy' => 'case_result_category',
					'field'    => 'term_id',
					'terms'    => $term_id,
				),
			)
		);
	}

	$query->set( 'posts_per_page', -1 );

	if ( $nfrows ) {
		$query->set( 'no_found_rows', true );
	}

	$query->set( 'title_number_first', true );
	add_filter( 'posts_orderby', 'lhd_numeric_title_orderby', 10, 2 );
}
add_action( 'pre_get_posts', 'lhd_custom_title_number_first_query' );

/**
 * Custom orderby to sort titles by numeric value first
 * Handles: $25,000,000 -> 25000000, $4.500,000 -> 4500000, Case 1 -> 1
 */
function lhd_numeric_title_orderby( $orderby, WP_Query $q ) {
	global $wpdb;

	if ( ! $q->get( 'title_number_first' ) ) {
		return $orderby;
	}

	// Extract numeric value from title by removing formatting characters
	$clean_title = "
		REPLACE(
			REPLACE(
				REPLACE(
					REPLACE({$wpdb->posts}.post_title, '$', ''),
					',', ''
				),
				'.', ''
			),
			' ', ''
		)
	";

	// Find the position of the first digit (0-9) in the cleaned title
	$first_digit_pos = "
		COALESCE(
			LEAST(
				NULLIF(LOCATE('0', {$clean_title}), 0),
				NULLIF(LOCATE('1', {$clean_title}), 0),
				NULLIF(LOCATE('2', {$clean_title}), 0),
				NULLIF(LOCATE('3', {$clean_title}), 0),
				NULLIF(LOCATE('4', {$clean_title}), 0),
				NULLIF(LOCATE('5', {$clean_title}), 0),
				NULLIF(LOCATE('6', {$clean_title}), 0),
				NULLIF(LOCATE('7', {$clean_title}), 0),
				NULLIF(LOCATE('8', {$clean_title}), 0),
				NULLIF(LOCATE('9', {$clean_title}), 0)
			),
			999999
		)
	";

	// Extract numeric value starting from first digit
	// CAST will automatically stop at first non-digit character
	$expr = "
		CAST(
			SUBSTRING(
				{$clean_title},
				CASE 
					WHEN {$first_digit_pos} < 999999 THEN {$first_digit_pos}
					ELSE 1
				END
			) AS UNSIGNED
		)
	";

	// Sort by numeric value descending (largest numbers first), then alphabetically
	return "{$expr} DESC, {$wpdb->posts}.post_title ASC";
}

