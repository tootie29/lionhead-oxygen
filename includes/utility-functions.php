<?php
/**
 * Utility Functions
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get title from current request context
 *
 * @return string Page/post/taxonomy title.
 */
function lhd_get_title_from_request() {
	if ( is_archive() || is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		return isset( $term->name ) ? $term->name : '';
	}

	if ( is_home() || is_front_page() ) {
		return __( 'Blog', 'lionhead-oxygen' );
	}

	if ( is_single() ) {
		return get_the_title();
	}

	return '';
}

/**
 * Get next post URL
 *
 * @return string Next post URL or empty string.
 */
function lhd_get_next_post_url() {
	$next_post = get_next_post();
	if ( $next_post ) {
		return get_permalink( $next_post->ID );
	}
	return '';
}

/**
 * Get previous post URL
 *
 * @return string Previous post URL or empty string.
 */
function lhd_get_previous_post_url() {
	$prev_post = get_previous_post();
	if ( $prev_post ) {
		return get_permalink( $prev_post->ID );
	}
	return '';
}

/**
 * Get next post title
 *
 * @return string Next post title or empty string.
 */
function lhd_get_next_post_title() {
	$next_post = get_next_post();
	if ( $next_post ) {
		return get_the_title( $next_post->ID );
	}
	return '';
}

/**
 * Get previous post title
 *
 * @return string Previous post title or empty string.
 */
function lhd_get_previous_post_title() {
	$prev_post = get_previous_post();
	if ( $prev_post ) {
		return get_the_title( $prev_post->ID );
	}
	return '';
}

/**
 * Get page permalink by ID
 *
 * @param int $page_id Page ID.
 * @return string|false Page permalink or false on failure.
 */
function lhd_get_page_permalink( $page_id ) {
	$page_id = absint( $page_id );
	if ( ! $page_id ) {
		return false;
	}
	return get_permalink( $page_id );
}

/**
 * Get current tag ID safely
 *
 * @return int Tag ID or 0 if not a tag archive.
 */
function lhd_get_current_tag_id_safe() {
	if ( is_tag() ) {
		$tag_id = get_queried_object_id();
		if ( $tag_id > 0 ) {
			return $tag_id;
		}
	}
	return 0;
}

/**
 * Get search parameter value from URL
 *
 * @return string|null Search query or null if not set.
 */
function lhd_get_search_parameter_value() {
	if ( isset( $_GET['s'] ) ) {
		return sanitize_text_field( wp_unslash( $_GET['s'] ) );
	}
	return null;
}

