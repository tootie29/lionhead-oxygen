<?php
/**
 * Shortcodes
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Shortcode: Display current year
 *
 * @return string Current year.
 */
function lhd_get_current_year() {
	return date( 'Y' );
}
add_shortcode( 'current_year', 'lhd_get_current_year' );

