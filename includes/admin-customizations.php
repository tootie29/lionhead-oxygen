<?php
/**
 * Admin Customizations
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Add Page ID column to pages list table
 */
function lhd_add_page_id_column( $columns ) {
	$columns['page_id'] = __( 'Page ID', 'lionhead-oxygen' );
	return $columns;
}
add_filter( 'manage_pages_columns', 'lhd_add_page_id_column' );

/**
 * Populate Page ID column
 */
function lhd_show_page_id_column( $column, $post_id ) {
	if ( 'page_id' === $column ) {
		echo esc_html( $post_id );
	}
}
add_action( 'manage_pages_custom_column', 'lhd_show_page_id_column', 10, 2 );

/**
 * Make Page ID column sortable
 */
function lhd_add_page_id_column_sortable( $columns ) {
	$columns['page_id'] = 'page_id';
	return $columns;
}
add_filter( 'manage_edit-page_sortable_columns', 'lhd_add_page_id_column_sortable' );

