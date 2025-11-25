<?php
/**
 * Oxygen Builder Integration
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register Oxygen Builder custom conditions
 */
function lhd_register_oxygen_conditions() {
	if ( ! function_exists( 'oxygen_vsb_register_condition' ) ) {
		return;
	}

	/**
	 * Register Oxygen condition: Previous Post URL Empty
	 */
	oxygen_vsb_register_condition(
		'Previous Post URL Empty',
		array( 'options' => array(), 'custom' => true ),
		array( '==', '!=' ),
		'lhd_condition_prev_post_url_empty_callback',
		'Post'
	);

	/**
	 * Register Oxygen condition: Next Post URL Empty
	 */
	oxygen_vsb_register_condition(
		'Next Post URL Empty',
		array( 'options' => array(), 'custom' => true ),
		array( '==', '!=' ),
		'lhd_condition_next_post_url_empty_callback',
		'Post'
	);

	

}
add_action( 'init', 'lhd_register_oxygen_conditions', 20 );

/**
 * Callback for Previous Post URL Empty condition
 *
 * @param string $value Value to compare.
 * @param string $operator Comparison operator.
 * @return bool Condition result.
 */
function lhd_condition_prev_post_url_empty_callback( $value, $operator ) {
	$prev_post_url = lhd_get_previous_post_url();
	$is_empty = empty( $prev_post_url );

	if ( $operator === '==' ) {
		return $is_empty;
	} else {
		return ! $is_empty;
	}
}


if( function_exists('oxygen_vsb_register_condition') ) {
	/**
	 * Register Oxygen condition: Current Post ID
	 * Compares current post/page ID with a provided ID
	 */
	oxygen_vsb_register_condition(
		'Current Post ID',
		array( 'options' => array(), 'custom' => true ),
		array( '==', '!=', '>=', '<=', '>', '<' ),
		'lhd_condition_current_post_id_callback',
		'Post'
	);


	/**
	 * Callback for Current Post ID condition
	 * Compares current post/page ID with a provided ID
	 *
	 * @param string $value    Post ID to compare.
	 * @param string $operator Comparison operator.
	 * @return bool Condition result.
	 */
	function lhd_condition_current_post_id_callback( $value, $operator ) {
		// Get current post ID using helper function
		$current_post_id = lhd_get_post_id();
		
		// If no post ID found, return false
		if ( ! $current_post_id ) {
			return false;
		}
		
		// Convert value to integer
		$value = intval( $value );

		// Compare and return true or false
		// Following the same pattern as the example
		if ( $operator == "==" ) {
			if ( $current_post_id == $value ) {
				return true;
			} else {
				return false;
			}
		} else if ( $operator == "!=" ) {
			if ( $current_post_id != $value ) {
				return true;
			} else {
				return false;
			}
		} else if ( $operator == ">=" ) {
			if ( $current_post_id >= $value ) {
				return true;
			} else {
				return false;
			}
		} else if ( $operator == "<=" ) {
			if ( $current_post_id <= $value ) {
				return true;
			} else {
				return false;
			}
		} else if ( $operator == ">" ) {
			if ( $current_post_id > $value ) {
				return true;
			} else {
				return false;
			}
		} else if ( $operator == "<" ) {
			if ( $current_post_id < $value ) {
				return true;
			} else {
				return false;
			}
		}

		return false;
	}
}