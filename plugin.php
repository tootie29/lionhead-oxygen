<?php
/**
 * Plugin Name: Lionhead Digital Custom Functionality
 * Plugin URI: https://lionhead.com
 * Description: Custom functionality for Lionhead.
 * Version: 1.0.0
 * Author: Lionhead Digital - Richard Medina
 * Author URI: https://lionheadmarketing.com/
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants
define( 'LHD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LHD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'LHD_PLUGIN_VERSION', '1.0.0' );

// Include required files
$includes_dir = LHD_PLUGIN_DIR . 'includes/';

// Load security first (highest priority)
include_once $includes_dir . 'security.php';
include_once $includes_dir . 'security-config.php';
include_once $includes_dir . 'oxygen-fonts.php';
include_once $includes_dir . 'query-modifications.php';
include_once $includes_dir . 'post-types.php';
include_once $includes_dir . 'taxonomies.php';
include_once $includes_dir . 'helper-functions.php';
include_once $includes_dir . 'scripts-styles.php';
include_once $includes_dir . 'performance-optimization.php';
include_once $includes_dir . 'image-optimization.php';
// include_once $includes_dir . 'critical-css.php'; // Disabled - breaking website
include_once $includes_dir . 'utility-functions.php';
include_once $includes_dir . 'oxygen-integration.php';
include_once $includes_dir . 'lazy-content.php';
include_once $includes_dir . 'admin-customizations.php';
include_once $includes_dir . 'shortcodes.php';
include_once $includes_dir . 'recommended-plugins.php';

// Register activation hooks for security configuration
register_activation_hook( __FILE__, 'lhd_activate_htaccess_security' );
register_activation_hook( __FILE__, 'lhd_activate_wpconfig_security' );

/**
 * Activation hook to add wp-config.php security constants
 * This function must be defined here to ensure it's available when activation runs
 */
function lhd_activate_wpconfig_security() {
	// Ensure the function exists (it should be loaded from security-config.php)
	if ( function_exists( 'lhd_add_wpconfig_security_constants' ) ) {
		$result = lhd_add_wpconfig_security_constants();
		
		// Log errors if any
		if ( is_wp_error( $result ) ) {
			// Store error in transient to show admin notice
			set_transient( 'lhd_wpconfig_error', $result->get_error_message(), 30 );
		}
	} else {
		// Function not loaded - store error
		set_transient( 'lhd_wpconfig_error', 'Security config function not available. Please try using the Security Config page.', 30 );
	}
}
