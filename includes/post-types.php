<?php
/**
 * Custom Post Types
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register Case Result Custom Post Type
 */
function lhd_register_case_result_cpt() {
	$labels = array(
		'name'                  => _x( 'Case Results', 'Post Type General Name', 'lionhead-oxygen' ),
		'singular_name'         => _x( 'Case Result', 'Post Type Singular Name', 'lionhead-oxygen' ),
		'menu_name'             => __( 'Case Results', 'lionhead-oxygen' ),
		'all_items'             => __( 'All Case Results', 'lionhead-oxygen' ),
		'add_new_item'          => __( 'Add New Case Result', 'lionhead-oxygen' ),
		'new_item'              => __( 'New Case Result', 'lionhead-oxygen' ),
		'edit_item'             => __( 'Edit Case Result', 'lionhead-oxygen' ),
		'update_item'           => __( 'Update Case Result', 'lionhead-oxygen' ),
		'view_item'             => __( 'View Case Result', 'lionhead-oxygen' ),
		'search_items'          => __( 'Search Case Results', 'lionhead-oxygen' ),
		'not_found'             => __( 'Not Found', 'lionhead-oxygen' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'lionhead-oxygen' ),
	);

	$args = array(
		'label'                 => __( 'Case Result', 'lionhead-oxygen' ),
		'description'           => __( 'Custom post type for showcasing legal case results.', 'lionhead-oxygen' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'custom-fields' ),
		'taxonomies'            => array( 'case_result_category', 'case_type' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-saved',
		'has_archive'           => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true,
	);

	register_post_type( 'case_result', $args );
}
add_action( 'init', 'lhd_register_case_result_cpt', 0 );

