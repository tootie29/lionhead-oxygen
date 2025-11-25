<?php
/**
 * Custom Taxonomies
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register Case Result Category Taxonomy
 */
function lhd_register_case_result_category_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Case Result Categories', 'Taxonomy General Name', 'lionhead-oxygen' ),
		'singular_name'              => _x( 'Case Result Category', 'Taxonomy Singular Name', 'lionhead-oxygen' ),
		'menu_name'                  => __( 'Categories', 'lionhead-oxygen' ),
		'all_items'                  => __( 'All Categories', 'lionhead-oxygen' ),
		'parent_item'                => __( 'Parent Category', 'lionhead-oxygen' ),
		'parent_item_colon'          => __( 'Parent Category:', 'lionhead-oxygen' ),
		'new_item_name'              => __( 'New Category Name', 'lionhead-oxygen' ),
		'add_new_item'               => __( 'Add New Category', 'lionhead-oxygen' ),
		'edit_item'                  => __( 'Edit Category', 'lionhead-oxygen' ),
		'update_item'                => __( 'Update Category', 'lionhead-oxygen' ),
		'view_item'                  => __( 'View Category', 'lionhead-oxygen' ),
		'separate_items_with_commas' => __( 'Separate categories with commas', 'lionhead-oxygen' ),
		'add_or_remove_items'        => __( 'Add or remove categories', 'lionhead-oxygen' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'lionhead-oxygen' ),
		'popular_items'              => __( 'Popular Categories', 'lionhead-oxygen' ),
		'search_items'               => __( 'Search Categories', 'lionhead-oxygen' ),
		'not_found'                  => __( 'Not Found', 'lionhead-oxygen' ),
		'no_terms'                   => __( 'No categories', 'lionhead-oxygen' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'case_result_category', array( 'case_result' ), $args );
}
add_action( 'init', 'lhd_register_case_result_category_taxonomy', 0 );

/**
 * Register Case Type Taxonomy
 */
function lhd_register_case_type_taxonomy() {
	$labels = array(
		'name'                       => _x( 'Case Types', 'Taxonomy General Name', 'lionhead-oxygen' ),
		'singular_name'              => _x( 'Case Type', 'Taxonomy Singular Name', 'lionhead-oxygen' ),
		'menu_name'                  => __( 'Case Types', 'lionhead-oxygen' ),
		'all_items'                  => __( 'All Case Types', 'lionhead-oxygen' ),
		'parent_item'                => __( 'Parent Case Type', 'lionhead-oxygen' ),
		'parent_item_colon'          => __( 'Parent Case Type:', 'lionhead-oxygen' ),
		'new_item_name'              => __( 'New Case Type Name', 'lionhead-oxygen' ),
		'add_new_item'               => __( 'Add New Case Type', 'lionhead-oxygen' ),
		'edit_item'                  => __( 'Edit Case Type', 'lionhead-oxygen' ),
		'update_item'                => __( 'Update Case Type', 'lionhead-oxygen' ),
		'view_item'                  => __( 'View Case Type', 'lionhead-oxygen' ),
		'add_or_remove_items'        => __( 'Add or remove Case Types', 'lionhead-oxygen' ),
		'search_items'               => __( 'Search Case Types', 'lionhead-oxygen' ),
		'not_found'                  => __( 'Not Found', 'lionhead-oxygen' ),
		'no_terms'                   => __( 'No Case Types', 'lionhead-oxygen' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'case_type', array( 'case_result' ), $args );
}
add_action( 'init', 'lhd_register_case_type_taxonomy', 0 );

