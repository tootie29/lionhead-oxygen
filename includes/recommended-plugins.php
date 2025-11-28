<?php
/**
 * Recommended Plugins
 * Allows installation of optional/recommended plugins
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get list of recommended plugins
 *
 * @return array Array of recommended plugins with their details
 */
function lhd_get_recommended_plugins() {
	return apply_filters( 'lhd_recommended_plugins', array(
		array(
			'name'        => 'Advanced Custom Fields',
			'slug'        => 'advanced-custom-fields',
			'required'    => false,
			'description' => 'Customize WordPress with powerful, professional and intuitive fields. ACF is the perfect solution for adding custom data to WordPress.',
			'source'      => 'wordpress',
			'version'     => 'latest',
			'zip_url'     => '',
		),
		array(
			'name'        => 'Redirection',
			'slug'        => 'redirection',
			'required'    => false,
			'description' => 'Manage all your 301 redirects and monitor 404 errors. Simple and powerful redirection management for WordPress.',
			'source'      => 'wordpress',
			'version'     => 'latest',
			'zip_url'     => '',
		),
		array(
			'name'        => 'Performance Lab',
			'slug'        => 'performance-lab',
			'required'    => false,
			'description' => 'Performance Lab is a collection of performance-related features and experiments for WordPress.',
			'source'      => 'wordpress',
			'version'     => 'latest',
			'zip_url'     => '',
		),
		array(
			'name'        => 'GenerateBlocks',
			'slug'        => 'generateblocks',
			'required'    => false,
			'description' => 'A small collection of lightweight WordPress blocks that can accomplish nearly anything.',
			'source'      => 'wordpress',
			'version'     => 'latest',
			'zip_url'     => '',
		),
		// Add more recommended plugins here
		// Example for uploaded plugin:
		// array(
		//     'name'        => 'Custom Plugin',
		//     'slug'        => 'custom-plugin',
		//     'required'    => false,
		//     'description' => 'Description of custom plugin.',
		//     'source'      => 'upload',
		//     'zip_url'     => 'https://example.com/plugin.zip', // URL to ZIP file
		// ),
	) );
}

/**
 * Check if a plugin is installed
 *
 * @param string $plugin_slug Plugin slug (e.g., 'performance-lab/performance-lab.php')
 * @return bool True if plugin is installed
 */
function lhd_is_plugin_installed( $plugin_slug ) {
	$installed_plugins = get_plugins();
	return isset( $installed_plugins[ $plugin_slug ] );
}

/**
 * Check if a plugin is active
 *
 * @param string $plugin_slug Plugin slug (e.g., 'performance-lab/performance-lab.php')
 * @return bool True if plugin is active
 */
function lhd_is_plugin_active( $plugin_slug ) {
	return is_plugin_active( $plugin_slug );
}

/**
 * Get plugin status
 *
 * @param array $plugin Plugin array from lhd_get_recommended_plugins()
 * @return string Status: 'not_installed', 'installed', 'active'
 */
function lhd_get_plugin_status( $plugin ) {
	// Try to find the plugin file
	$plugin_file = $plugin['slug'] . '/' . $plugin['slug'] . '.php';
	
	// Some plugins have different file structures
	$possible_files = array(
		$plugin_file,
		$plugin['slug'] . '.php',
	);
	
	foreach ( $possible_files as $file ) {
		if ( lhd_is_plugin_installed( $file ) ) {
			if ( lhd_is_plugin_active( $file ) ) {
				return 'active';
			}
			return 'installed';
		}
	}
	
	return 'not_installed';
}

/**
 * Install a plugin from uploaded ZIP file
 *
 * @param string $zip_url URL to the plugin ZIP file
 * @return array|WP_Error Array with success message or WP_Error on failure
 */
function lhd_install_plugin_from_zip( $zip_url ) {
	// Check if user has permission
	if ( ! current_user_can( 'install_plugins' ) ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to install plugins.', 'lionhead-oxygen' ) );
	}

	// Check if DISALLOW_FILE_MODS is enabled
	if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
		return new WP_Error( 'file_mods_disabled', __( 'Plugin installation is disabled. DISALLOW_FILE_MODS is set to true in wp-config.php. Please install plugins manually or temporarily disable this setting.', 'lionhead-oxygen' ) );
	}

	// Include required files
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	// Create upgrader
	$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );

	// Install from URL
	$result = $upgrader->install( $zip_url );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	if ( $result === false ) {
		return new WP_Error( 'install_failed', __( 'Plugin installation failed.', 'lionhead-oxygen' ) );
	}

	// Get installed plugin file
	$plugins = $upgrader->result;
	if ( empty( $plugins ) ) {
		return new WP_Error( 'no_plugin_file', __( 'Could not determine installed plugin file.', 'lionhead-oxygen' ) );
	}

	// Handle both single plugin and array of plugins
	$plugin_file = is_array( $plugins ) ? $plugins[0] : $plugins;

	return array(
		'success'     => true,
		'message'     => __( 'Plugin installed successfully.', 'lionhead-oxygen' ),
		'plugin_file' => $plugin_file,
	);
}

/**
 * Install a plugin from WordPress repository
 *
 * @param string $plugin_slug Plugin slug
 * @return array|WP_Error Array with success message or WP_Error on failure
 */
function lhd_install_plugin_from_repo( $plugin_slug ) {
	// Check if user has permission
	if ( ! current_user_can( 'install_plugins' ) ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to install plugins.', 'lionhead-oxygen' ) );
	}

	// Check if DISALLOW_FILE_MODS is enabled
	if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
		return new WP_Error( 'file_mods_disabled', __( 'Plugin installation is disabled. DISALLOW_FILE_MODS is set to true in wp-config.php. Please install plugins manually or temporarily disable this setting.', 'lionhead-oxygen' ) );
	}

	// Check if plugin is already installed
	$plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
	if ( lhd_is_plugin_installed( $plugin_file ) ) {
		return new WP_Error( 'already_installed', __( 'Plugin is already installed.', 'lionhead-oxygen' ) );
	}

	// Include required files
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';

	// Get plugin information
	$api = plugins_api(
		'plugin_information',
		array(
			'slug'   => $plugin_slug,
			'fields' => array(
				'short_description' => false,
				'sections'          => false,
				'requires'          => false,
				'rating'            => false,
				'ratings'           => false,
				'downloaded'       => false,
				'last_updated'     => false,
				'added'             => false,
				'tags'              => false,
				'compatibility'     => false,
				'homepage'          => false,
				'donate_link'       => false,
			),
		)
	);

	if ( is_wp_error( $api ) ) {
		return new WP_Error( 'api_error', sprintf( __( 'Error fetching plugin information: %s', 'lionhead-oxygen' ), $api->get_error_message() ) );
	}

	// Install plugin
	$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
	$result   = $upgrader->install( $api->download_link );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	if ( $result === false ) {
		return new WP_Error( 'install_failed', __( 'Plugin installation failed.', 'lionhead-oxygen' ) );
	}

	return array(
		'success' => true,
		'message' => sprintf( __( 'Plugin %s installed successfully.', 'lionhead-oxygen' ), $api->name ),
		'plugin_file' => $plugin_slug . '/' . $plugin_slug . '.php',
	);
}

/**
 * Activate a plugin
 *
 * @param string $plugin_file Plugin file path (e.g., 'performance-lab/performance-lab.php')
 * @return array|WP_Error Array with success message or WP_Error on failure
 */
function lhd_activate_plugin( $plugin_file ) {
	// Check if user has permission
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to activate plugins.', 'lionhead-oxygen' ) );
	}

	// Check if plugin is installed
	if ( ! lhd_is_plugin_installed( $plugin_file ) ) {
		return new WP_Error( 'not_installed', __( 'Plugin is not installed.', 'lionhead-oxygen' ) );
	}

	// Check if already active
	if ( lhd_is_plugin_active( $plugin_file ) ) {
		return new WP_Error( 'already_active', __( 'Plugin is already active.', 'lionhead-oxygen' ) );
	}

	// Activate plugin
	$result = activate_plugin( $plugin_file );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	return array(
		'success' => true,
		'message' => __( 'Plugin activated successfully.', 'lionhead-oxygen' ),
	);
}

/**
 * Handle AJAX requests for plugin installation
 */
function lhd_handle_plugin_install_ajax() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'lhd_install_plugin' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'lionhead-oxygen' ) ) );
	}

	// Check permissions
	if ( ! current_user_can( 'install_plugins' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'lionhead-oxygen' ) ) );
	}

	$plugin_slug = isset( $_POST['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_slug'] ) ) : '';
	$source      = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : 'wordpress';
	$zip_url     = isset( $_POST['zip_url'] ) ? esc_url_raw( wp_unslash( $_POST['zip_url'] ) ) : '';

	if ( 'upload' === $source ) {
		if ( empty( $zip_url ) ) {
			wp_send_json_error( array( 'message' => __( 'ZIP file URL is required for upload source.', 'lionhead-oxygen' ) ) );
		}
		$result = lhd_install_plugin_from_zip( $zip_url );
	} else {
		if ( empty( $plugin_slug ) ) {
			wp_send_json_error( array( 'message' => __( 'Plugin slug is required.', 'lionhead-oxygen' ) ) );
		}
		$result = lhd_install_plugin_from_repo( $plugin_slug );
	}

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => $result['message'], 'plugin_file' => $result['plugin_file'] ) );
}
add_action( 'wp_ajax_lhd_install_plugin', 'lhd_handle_plugin_install_ajax' );

/**
 * Toggle DISALLOW_FILE_MODS in wp-config.php
 *
 * @param bool $enable True to enable (set to true), false to disable (set to false)
 * @return array|WP_Error Array with success message or WP_Error on failure
 */
function lhd_toggle_file_mods( $enable ) {
	// Check if user has permission
	if ( ! current_user_can( 'manage_options' ) ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to modify wp-config.php.', 'lionhead-oxygen' ) );
	}

	// Get wp-config.php path
	$wpconfig_path = ABSPATH . 'wp-config.php';
	if ( ! file_exists( $wpconfig_path ) ) {
		$wpconfig_path = dirname( ABSPATH ) . '/wp-config.php';
	}

	// Check if wp-config.php exists and is readable
	if ( ! file_exists( $wpconfig_path ) || ! is_readable( $wpconfig_path ) ) {
		return new WP_Error( 'wpconfig_not_found', __( 'wp-config.php file not found or not readable.', 'lionhead-oxygen' ) );
	}

	// Check if file is writable
	if ( ! is_writable( $wpconfig_path ) ) {
		return new WP_Error( 'wpconfig_not_writable', __( 'wp-config.php file is not writable. Please check file permissions.', 'lionhead-oxygen' ) );
	}

	// Read current content
	$wpconfig_content = file_get_contents( $wpconfig_path );
	if ( $wpconfig_content === false ) {
		return new WP_Error( 'read_failed', __( 'Failed to read wp-config.php file.', 'lionhead-oxygen' ) );
	}

	// Create backup
	$backup_path = $wpconfig_path . '.backup.' . date( 'Y-m-d-H-i-s' );
	if ( ! @copy( $wpconfig_path, $backup_path ) ) {
		return new WP_Error( 'backup_failed', __( 'Failed to create backup of wp-config.php.', 'lionhead-oxygen' ) );
	}

	$value = $enable ? 'true' : 'false';
	$new_constant = "define( 'DISALLOW_FILE_MODS', {$value} );";
	
	// Log what we're trying to do
	error_log( 'LHD Toggle: Attempting to set DISALLOW_FILE_MODS to ' . $value );
	
	// Check if constant already exists with the desired value (only check active, not commented)
	// We need to check if it's actually active, not just present in the file
	$desired_pattern = "/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*{$value}\s*\)\s*;/i";
	
	// First, check if it exists at all (with any value) - simple check
	$current_pattern = "/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*(true|false)\s*\)\s*;/i";
	$current_match = preg_match( $current_pattern, $wpconfig_content, $current_matches );
	
	if ( $current_match && ! empty( $current_matches[1] ) ) {
		$current_value = strtolower( trim( $current_matches[1] ) );
		$desired_value = strtolower( trim( $value ) );
		
		if ( $current_value === $desired_value ) {
			// Constant already has the desired value, no change needed
			error_log( 'LHD Toggle: Constant already has desired value (' . $value . '), skipping update' );
			return array(
				'success' => true,
				'message' => $enable 
					? __( 'Plugin installation is already disabled (DISALLOW_FILE_MODS is already set to true).', 'lionhead-oxygen' )
					: __( 'Plugin installation is already enabled (DISALLOW_FILE_MODS is already set to false).', 'lionhead-oxygen' ),
			);
		} else {
			error_log( 'LHD Toggle: Constant exists with value ' . $current_value . ', changing to ' . $desired_value );
		}
	} else {
		error_log( 'LHD Toggle: Constant does not exist, will add it' );
	}

	// SIMPLE APPROACH: Just replace the existing constant value
	// This is much more reliable than remove-and-add
	
	// Pattern to match DISALLOW_FILE_MODS with any value
	$replace_pattern = "/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*(true|false)\s*\)\s*;/i";
	
	// Find insertion point in case we need to add the constant
	$insertion_markers = array(
		"That's all, stop editing! Happy blogging.",
		"That's all, stop editing! Happy publishing.",
		"That's all, stop editing!",
	);

	$insertion_pos = false;
	foreach ( $insertion_markers as $marker ) {
		$pos = strpos( $wpconfig_content, $marker );
		if ( $pos !== false ) {
			$before_pos = substr( $wpconfig_content, 0, $pos );
			$open_comments = substr_count( $before_pos, '/*' );
			$close_comments = substr_count( $before_pos, '*/' );

			// Make sure we're not inside a comment block
			if ( $open_comments === $close_comments ) {
				$insertion_pos = $pos;
				break;
			}
		}
	}
	
	// Check if constant exists and replace it
	if ( preg_match( $replace_pattern, $wpconfig_content, $matches ) ) {
		// Constant exists - just replace it
		error_log( 'LHD Toggle: Found constant, replacing it' );
		$wpconfig_content = preg_replace( $replace_pattern, $new_constant, $wpconfig_content );
		error_log( 'LHD Toggle: Replacement done. New content length: ' . strlen( $wpconfig_content ) );
		
		// Check if multiple instances were replaced (shouldn't happen, but handle it)
		$file_mods_count = substr_count( $wpconfig_content, "define( 'DISALLOW_FILE_MODS', {$value} );" );
		error_log( 'LHD Toggle: Found ' . $file_mods_count . ' instances of constant with value ' . $value );
		if ( $file_mods_count > 1 ) {
			// Multiple instances found, remove duplicates - keep only one
			$lines = explode( "\n", $wpconfig_content );
			$new_lines = array();
			$file_mods_added = false;
			
			foreach ( $lines as $line ) {
				if ( preg_match( "/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*(true|false)\s*\)\s*;/i", $line ) ) {
					if ( ! $file_mods_added ) {
						$new_lines[] = $new_constant;
						$file_mods_added = true;
					}
					// Skip duplicate instances
				} else {
					$new_lines[] = $line;
				}
			}
			
			$wpconfig_content = implode( "\n", $new_lines );
		}
	} else {
		// Constant doesn't exist - add it along with other missing constants
		error_log( 'LHD Toggle: Constant not found, adding it' );
		// Check if DISALLOW_FILE_EDIT and AUTOMATIC_UPDATER_DISABLED exist, add them if missing
		$file_edit_exists = preg_match( "/define\s*\(\s*['\"]DISALLOW_FILE_EDIT['\"]\s*,\s*(true|false)\s*\)\s*;/i", $wpconfig_content );
		$updater_exists = preg_match( "/define\s*\(\s*['\"]AUTOMATIC_UPDATER_DISABLED['\"]\s*,\s*(true|false)\s*\)\s*;/i", $wpconfig_content );
		
		$constants_to_add = array( $new_constant );
		
		if ( ! $file_edit_exists ) {
			$constants_to_add[] = "define( 'DISALLOW_FILE_EDIT', true );";
		}
		if ( ! $updater_exists ) {
			$constants_to_add[] = "define( 'AUTOMATIC_UPDATER_DISABLED', true );";
		}
		
		$constants_string = implode( "\n", $constants_to_add );

		if ( $insertion_pos !== false ) {
			$before_marker = substr( $wpconfig_content, 0, $insertion_pos );
			$after_marker = substr( $wpconfig_content, $insertion_pos );
			
			// Trim trailing whitespace and add constants with proper spacing
			$before_marker = rtrim( $before_marker );
			// Add constants before the marker
			$wpconfig_content = $before_marker . "\n\n" . $constants_string . "\n\n" . ltrim( $after_marker );
		} else {
			// If marker not found, try to find before require_once wp-settings.php
			$wp_settings_pos = strpos( $wpconfig_content, "require_once(ABSPATH . 'wp-settings.php');" );
			if ( $wp_settings_pos !== false ) {
				$before_settings = substr( $wpconfig_content, 0, $wp_settings_pos );
				$after_settings = substr( $wpconfig_content, $wp_settings_pos );
				$wpconfig_content = rtrim( $before_settings ) . "\n\n" . $constants_string . "\n\n" . ltrim( $after_settings );
			} else {
				// Last resort: append to end
				$wpconfig_content = rtrim( $wpconfig_content ) . "\n\n" . $constants_string . "\n";
			}
		}
	}
	
	// CRITICAL VALIDATION: Ensure wp-config.php still has essential elements
	$required_elements = array(
		'ABSPATH',
		'wp-settings.php',
		'DB_NAME',
		'DB_USER',
	);
	
	foreach ( $required_elements as $element ) {
		if ( stripos( $wpconfig_content, $element ) === false ) {
			// Restore backup - wp-config.php is broken
			@copy( $backup_path, $wpconfig_path );
			return new WP_Error( 'wpconfig_broken', sprintf( __( 'wp-config.php appears to be missing required element: %s. Changes were not applied and backup was restored.', 'lionhead-oxygen' ), $element ) );
		}
	}
	
	// Verify DISALLOW_FILE_MODS is in the content with correct value
	$file_mods_count_final = substr_count( $wpconfig_content, "define( 'DISALLOW_FILE_MODS', {$value} );" );
	if ( $file_mods_count_final === 0 ) {
		// Constant not found, something went wrong
		return new WP_Error( 'constant_not_found', __( 'Failed to update DISALLOW_FILE_MODS constant in wp-config.php.', 'lionhead-oxygen' ) );
	} elseif ( $file_mods_count_final > 1 ) {
		// Multiple instances found, remove duplicates - keep only one
		$lines = explode( "\n", $wpconfig_content );
		$new_lines = array();
		$file_mods_added = false;
		
		foreach ( $lines as $line ) {
			if ( preg_match( "/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*(true|false)\s*\)\s*;/i", $line ) ) {
				if ( ! $file_mods_added ) {
					$new_lines[] = $new_constant;
					$file_mods_added = true;
				}
				// Skip duplicate instances
			} else {
				$new_lines[] = $line;
			}
		}
		
		$wpconfig_content = implode( "\n", $new_lines );
	}

	// Write to file
	// Clear any cached file status
	clearstatcache( true, $wpconfig_path );
	
	// Store original content length and backup size for comparison
	$original_size = strlen( $wpconfig_content );
	$backup_size = filesize( $backup_path );
	
	error_log( 'LHD Toggle: About to write. Content size: ' . $original_size . ', Backup size: ' . $backup_size );
	
	// Double-check file is writable before attempting write
	if ( ! is_writable( $wpconfig_path ) ) {
		error_log( 'LHD Toggle: File is not writable!' );
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_failed', __( 'wp-config.php is not writable. Please check file permissions.', 'lionhead-oxygen' ) );
	}
	
	// Try using fopen/fwrite instead of file_put_contents for better control
	error_log( 'LHD Toggle: Opening file for writing' );
	$handle = @fopen( $wpconfig_path, 'wb' );
	if ( ! $handle ) {
		$error = error_get_last();
		$error_msg = __( 'Failed to open wp-config.php for writing. Backup restored.', 'lionhead-oxygen' );
		if ( $error ) {
			$error_msg .= ' ' . sprintf( __( 'Error: %s', 'lionhead-oxygen' ), $error['message'] );
		}
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_failed', $error_msg );
	}
	
	// Lock the file
	if ( ! flock( $handle, LOCK_EX ) ) {
		fclose( $handle );
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_failed', __( 'Failed to lock wp-config.php for writing. Backup restored.', 'lionhead-oxygen' ) );
	}
	
	// Write the content
	error_log( 'LHD Toggle: Writing content to file' );
	$bytes_written = @fwrite( $handle, $wpconfig_content );
	error_log( 'LHD Toggle: Wrote ' . $bytes_written . ' bytes' );
	
	// Unlock and close
	flock( $handle, LOCK_UN );
	fclose( $handle );
	
	if ( $bytes_written === false || $bytes_written === 0 ) {
		error_log( 'LHD Toggle: Write failed! Bytes written: ' . var_export( $bytes_written, true ) );
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_failed', __( 'Failed to write content to wp-config.php. Backup restored.', 'lionhead-oxygen' ) );
	}
	
	// Verify the file was actually written (check file size)
	clearstatcache( true, $wpconfig_path );
	if ( ! file_exists( $wpconfig_path ) ) {
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_verify_failed', __( 'File write verification failed. wp-config.php does not exist after write. Backup restored.', 'lionhead-oxygen' ) );
	}
	
	$file_size = filesize( $wpconfig_path );
	if ( $file_size === 0 ) {
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_verify_failed', __( 'File write verification failed. wp-config.php is empty after write. Backup restored.', 'lionhead-oxygen' ) );
	}
	
	// Check if bytes written matches what we expected (allow some tolerance for line endings)
	if ( abs( $bytes_written - $original_size ) > 10 && $bytes_written < $original_size * 0.9 ) {
		// We wrote significantly less than expected
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_verify_failed', sprintf( __( 'File write verification failed. Expected to write %d bytes but only wrote %d bytes. Backup restored.', 'lionhead-oxygen' ), $original_size, $bytes_written ) );
	}
	
	// If file size didn't change at all and we wrote different content, that's suspicious
	// But allow for the case where content is the same size
	if ( abs( $file_size - $backup_size ) < 5 ) {
		// File size is almost the same - check if content actually changed
		$backup_content_check = file_get_contents( $backup_path );
		if ( $backup_content_check === $wpconfig_content ) {
			// Content is identical, so no change needed
			// But we should still verify our constant is there
		} else {
			// Content should be different but file size is the same - suspicious
			// Don't fail yet, let verification check the content
		}
	}

	// Clear stat cache to ensure we read the fresh file
	clearstatcache( true, $wpconfig_path );
	
	// Wait a moment for file system to sync (some systems need this)
	usleep( 200000 ); // 0.2 seconds
	
	// Verify the write was successful by checking if constant exists in file
	error_log( 'LHD Toggle: Starting verification. Bytes written: ' . $bytes_written . ', File size: ' . $file_size . ', Backup size: ' . $backup_size );
	
	$verify_content = file_get_contents( $wpconfig_path );
	if ( $verify_content === false ) {
		error_log( 'LHD Toggle: Failed to read file for verification' );
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'verify_read_failed', __( 'Failed to read wp-config.php after write. Backup restored.', 'lionhead-oxygen' ) );
	}
	
	// SIMPLIFIED VERIFICATION: Just check if the constant exists with the correct value
	// Since false works, the write mechanism is fine - we just need to verify correctly
	
	// If write clearly succeeded (bytes written and file size changed), we can be more lenient
	$write_clearly_succeeded = ( $bytes_written > 0 && abs( $file_size - $backup_size ) > 10 );
	error_log( 'LHD Toggle: Write clearly succeeded: ' . ( $write_clearly_succeeded ? 'yes' : 'no' ) );
	
	// Wait a bit longer for file system to fully sync
	usleep( 300000 ); // 0.3 seconds
	clearstatcache( true, $wpconfig_path );
	
	// Re-read the file to make sure we have the latest
	$verify_content = file_get_contents( $wpconfig_path );
	if ( $verify_content === false ) {
		error_log( 'LHD Toggle: Failed to re-read file for verification' );
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'verify_read_failed', __( 'Failed to read wp-config.php after write. Backup restored.', 'lionhead-oxygen' ) );
	}
	
	error_log( 'LHD Toggle: Verification content length: ' . strlen( $verify_content ) );
	
	$constant_found = false;
	
	// Check if the constant exists at all (with any value) - be very flexible with the pattern
	$constant_exists = preg_match( "/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*(true|false|TRUE|FALSE|1|0)\s*\)\s*;/i", $verify_content, $matches );
	error_log( 'LHD Toggle: Constant exists check: ' . ( $constant_exists ? 'yes' : 'no' ) );
	if ( $constant_exists && ! empty( $matches[1] ) ) {
		error_log( 'LHD Toggle: Found constant with value: ' . $matches[1] );
	}
	
	if ( $constant_exists && ! empty( $matches[1] ) ) {
		$found_value_raw = trim( $matches[1] );
		$found_value = strtolower( $found_value_raw );
		
		// Normalize values: true/TRUE/1 -> true, false/FALSE/0 -> false
		if ( $found_value === '1' || $found_value === 'true' ) {
			$found_value = 'true';
		} elseif ( $found_value === '0' || $found_value === 'false' ) {
			$found_value = 'false';
		}
		
		$expected_value = strtolower( trim( $value ) );
		
		// If the value matches (case-insensitive), we're good!
		if ( $found_value === $expected_value ) {
			$constant_found = true;
			error_log( 'LHD Toggle: Verification SUCCESS! Constant found with correct value: ' . $found_value );
		} else {
			error_log( 'LHD Toggle: Verification FAILED! Expected: ' . $expected_value . ', Found: ' . $found_value );
			// It's there but with a different value
			// If bytes were written successfully and file size changed, the write probably worked
			// Maybe verification is reading stale data - let's be more lenient
			if ( $bytes_written > 0 && abs( $file_size - $backup_size ) > 10 ) {
				// Write succeeded and file changed - maybe verification is just seeing old data
				// Log a warning but don't fail
				error_log( 'LHD Toggle: Value mismatch but write succeeded. Expected: ' . $value . ', Found: ' . $found_value_raw . ', Bytes written: ' . $bytes_written );
				// Actually, let's fail this - we need the correct value
				@copy( $backup_path, $wpconfig_path );
				return new WP_Error( 'verify_failed', sprintf( __( 'Failed to verify wp-config.php update. Expected DISALLOW_FILE_MODS to be %s but found %s in the file. Backup restored.', 'lionhead-oxygen' ), $value, $found_value_raw ) );
			} else {
				@copy( $backup_path, $wpconfig_path );
				return new WP_Error( 'verify_failed', sprintf( __( 'Failed to verify wp-config.php update. Expected DISALLOW_FILE_MODS to be %s but found %s in the file. Backup restored.', 'lionhead-oxygen' ), $value, $found_value_raw ) );
			}
		}
	} else {
		// Constant doesn't exist in the file at all
		// Check if we expected it to be there
		$expected_in_content = strpos( $wpconfig_content, "define( 'DISALLOW_FILE_MODS', {$value} );" ) !== false;
		
		if ( $expected_in_content ) {
			// We wrote it but it's not there - something went wrong
			// Log for debugging
			error_log( 'LHD Toggle: Constant was in prepared content but not found in file. Expected: ' . $value . ', Bytes written: ' . $bytes_written . ', File size: ' . filesize( $wpconfig_path ) );
			
			// Before restoring, let's check one more time with a simpler search
			if ( stripos( $verify_content, 'DISALLOW_FILE_MODS' ) !== false ) {
				// The constant name is there, let's see what value it has
				$lines = explode( "\n", $verify_content );
				foreach ( $lines as $line_num => $line ) {
					if ( stripos( $line, 'DISALLOW_FILE_MODS' ) !== false ) {
						error_log( 'LHD Toggle: Found DISALLOW_FILE_MODS on line ' . ( $line_num + 1 ) . ': ' . trim( $line ) );
						// Check if this line has our value
						if ( stripos( $line, $value ) !== false ) {
							// The value is on this line - verification might have missed it due to formatting
							$constant_found = true;
							break;
						}
					}
				}
			}
			
			// If write clearly succeeded and we still can't find it, maybe it's a verification timing issue
			// But if we found it in the line-by-line check above, we're good
			if ( ! $constant_found ) {
				if ( $write_clearly_succeeded ) {
					// Write succeeded but verification failed - might be a timing/caching issue
					// Let's check one more time after another short wait
					usleep( 200000 ); // 0.2 more seconds
					clearstatcache( true, $wpconfig_path );
					$verify_content_retry = file_get_contents( $wpconfig_path );
					if ( $verify_content_retry && preg_match( "/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*{$value}\s*\)\s*;/i", $verify_content_retry ) ) {
						$constant_found = true;
					}
				}
				
				if ( ! $constant_found ) {
					@copy( $backup_path, $wpconfig_path );
					return new WP_Error( 'verify_failed', sprintf( __( 'Failed to verify wp-config.php update. The constant was in our prepared content but not found in the written file. This may indicate a file system issue. Backup restored.', 'lionhead-oxygen' ), $value ) );
				}
			}
		}
		
		// If we get here, the constant isn't in the file at all
		// But maybe it was added in a different format - let's be lenient and check if the file was at least modified
		// If the file size changed significantly, the write probably worked
		$backup_size_check = filesize( $backup_path );
		$current_size_check = filesize( $wpconfig_path );
		
		if ( abs( $current_size_check - $backup_size_check ) < 50 ) {
			// File size didn't change much, probably nothing was written
			@copy( $backup_path, $wpconfig_path );
			return new WP_Error( 'verify_failed', sprintf( __( 'Failed to verify wp-config.php update. Expected DISALLOW_FILE_MODS to be %s but it was not found in the file and file size did not change. Backup restored.', 'lionhead-oxygen' ), $value ) );
		}
		
		// File size changed, so something was written, but we can't find our constant
		// This is suspicious - restore backup to be safe
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'verify_failed', sprintf( __( 'Failed to verify wp-config.php update. Expected DISALLOW_FILE_MODS to be %s but it was not found in the file. File was modified but constant not found. Backup restored.', 'lionhead-oxygen' ), $value ) );
	}

	// CRITICAL: Verify that we ONLY modified DISALLOW_FILE_MODS and didn't touch other constants
	// Check that DISALLOW_FILE_EDIT and AUTOMATIC_UPDATER_DISABLED are still present and unchanged
	$backup_content = file_get_contents( $backup_path );
	
	// Extract DISALLOW_FILE_EDIT from backup
	preg_match( "/define\s*\(\s*['\"]DISALLOW_FILE_EDIT['\"]\s*,\s*(true|false)\s*\)\s*;/i", $backup_content, $file_edit_backup );
	preg_match( "/define\s*\(\s*['\"]AUTOMATIC_UPDATER_DISABLED['\"]\s*,\s*(true|false)\s*\)\s*;/i", $backup_content, $updater_backup );
	
	// Extract from new content
	preg_match( "/define\s*\(\s*['\"]DISALLOW_FILE_EDIT['\"]\s*,\s*(true|false)\s*\)\s*;/i", $verify_content, $file_edit_new );
	preg_match( "/define\s*\(\s*['\"]AUTOMATIC_UPDATER_DISABLED['\"]\s*,\s*(true|false)\s*\)\s*;/i", $verify_content, $updater_new );
	
	// If they existed in backup, they should still exist with same value
	if ( ! empty( $file_edit_backup ) ) {
		if ( empty( $file_edit_new ) || $file_edit_backup[1] !== $file_edit_new[1] ) {
			// DISALLOW_FILE_EDIT was changed or removed - restore backup
			@copy( $backup_path, $wpconfig_path );
			return new WP_Error( 'other_constant_changed', __( 'DISALLOW_FILE_EDIT was unexpectedly modified. Changes were not applied and backup was restored.', 'lionhead-oxygen' ) );
		}
	}
	
	if ( ! empty( $updater_backup ) ) {
		if ( empty( $updater_new ) || $updater_backup[1] !== $updater_new[1] ) {
			// AUTOMATIC_UPDATER_DISABLED was changed or removed - restore backup
			@copy( $backup_path, $wpconfig_path );
			return new WP_Error( 'other_constant_changed', __( 'AUTOMATIC_UPDATER_DISABLED was unexpectedly modified. Changes were not applied and backup was restored.', 'lionhead-oxygen' ) );
		}
	}

	// Store backup location
	update_option( 'lhd_wpconfig_backup', $backup_path );
	
	// Set transient to prevent auto-add function from running right after toggle
	set_transient( 'lhd_file_mods_toggled', true, 60 ); // 60 seconds

	// Return success message based on what we set
	$message = $enable 
		? __( 'Plugin installation has been disabled (DISALLOW_FILE_MODS set to true).', 'lionhead-oxygen' )
		: __( 'Plugin installation has been enabled (DISALLOW_FILE_MODS set to false).', 'lionhead-oxygen' );
	
	return array(
		'success' => true,
		'message' => $message,
	);
}

/**
 * Handle AJAX requests for toggling DISALLOW_FILE_MODS
 */
function lhd_handle_toggle_file_mods_ajax() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'lhd_toggle_file_mods' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'lionhead-oxygen' ) ) );
	}

	// Check permissions
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to modify wp-config.php.', 'lionhead-oxygen' ) ) );
	}

	$enable = isset( $_POST['enable'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['enable'] ) );

	$result = lhd_toggle_file_mods( $enable );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 
			'message' => $result->get_error_message(),
			'error_code' => $result->get_error_code()
		) );
	}

	wp_send_json_success( array( 'message' => $result['message'] ) );
}
add_action( 'wp_ajax_lhd_toggle_file_mods', 'lhd_handle_toggle_file_mods_ajax' );

/**
 * Handle AJAX requests for plugin activation
 */
function lhd_handle_plugin_activate_ajax() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'lhd_activate_plugin' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'lionhead-oxygen' ) ) );
	}

	// Check permissions
	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json_error( array( 'message' => __( 'You do not have permission to activate plugins.', 'lionhead-oxygen' ) ) );
	}

	$plugin_file = isset( $_POST['plugin_file'] ) ? sanitize_text_field( wp_unslash( $_POST['plugin_file'] ) ) : '';

	if ( empty( $plugin_file ) ) {
		wp_send_json_error( array( 'message' => __( 'Plugin file is required.', 'lionhead-oxygen' ) ) );
	}

	$result = lhd_activate_plugin( $plugin_file );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => $result['message'] ) );
}
add_action( 'wp_ajax_lhd_activate_plugin', 'lhd_handle_plugin_activate_ajax' );

/**
 * Add admin menu for recommended plugins
 */
function lhd_add_recommended_plugins_menu() {
	// Use manage_options instead of install_plugins so the page is always accessible
	// even when DISALLOW_FILE_MODS is true (needed to toggle the setting)
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	add_submenu_page(
		'plugins.php',
		__( 'Recommended Plugins', 'lionhead-oxygen' ),
		__( 'Recommended', 'lionhead-oxygen' ),
		'manage_options',
		'lhd-recommended-plugins',
		'lhd_recommended_plugins_page'
	);
}
add_action( 'admin_menu', 'lhd_add_recommended_plugins_menu' );

/**
 * Recommended plugins admin page
 */
function lhd_recommended_plugins_page() {
	// Use manage_options so page is accessible even when DISALLOW_FILE_MODS is true
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'lionhead-oxygen' ) );
	}

	$recommended_plugins = lhd_get_recommended_plugins();
	
	// Check if DISALLOW_FILE_MODS is enabled (blocks plugin installation)
	$file_mods_disabled = defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS;
	$wpconfig_writable = false;
	$wpconfig_path = ABSPATH . 'wp-config.php';
	if ( ! file_exists( $wpconfig_path ) ) {
		$wpconfig_path = dirname( ABSPATH ) . '/wp-config.php';
	}
	if ( file_exists( $wpconfig_path ) && is_writable( $wpconfig_path ) ) {
		$wpconfig_writable = true;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Recommended Plugins', 'lionhead-oxygen' ); ?></h1>
		<p><?php esc_html_e( 'Install and activate these optional plugins to enhance your website functionality.', 'lionhead-oxygen' ); ?></p>

		<?php if ( $file_mods_disabled ) : ?>
			<div class="notice notice-warning" style="margin-top: 20px;">
				<p><strong><?php esc_html_e( 'Plugin Installation Disabled', 'lionhead-oxygen' ); ?></strong></p>
				<p>
					<?php esc_html_e( 'Plugin installation is currently disabled because DISALLOW_FILE_MODS is set to true in wp-config.php. This is a security setting that prevents plugin installation through the WordPress admin.', 'lionhead-oxygen' ); ?>
				</p>
				<?php if ( $wpconfig_writable ) : ?>
					<p>
						<button 
							type="button" 
							class="button button-primary lhd-toggle-file-mods" 
							data-set-disallow-file-mods="false"
							data-current-status="disabled"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'lhd_toggle_file_mods' ) ); ?>"
						>
							<?php esc_html_e( 'Enable Plugin Installation', 'lionhead-oxygen' ); ?>
						</button>
						<span class="lhd-file-mods-message" style="margin-left: 10px;"></span>
					</p>
					<p class="description">
						<?php esc_html_e( 'This will set DISALLOW_FILE_MODS to false in wp-config.php, allowing plugin installation. A backup will be created automatically.', 'lionhead-oxygen' ); ?>
					</p>
				<?php else : ?>
					<p>
						<?php esc_html_e( 'wp-config.php is not writable. Please set DISALLOW_FILE_MODS to false manually in wp-config.php to enable plugin installation.', 'lionhead-oxygen' ); ?>
					</p>
					<p>
						<a href="<?php echo esc_url( admin_url( 'tools.php?page=lhd-security-config' ) ); ?>" class="button">
							<?php esc_html_e( 'Go to Security Config', 'lionhead-oxygen' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<?php if ( $wpconfig_writable ) : ?>
				<div class="notice notice-info" style="margin-top: 20px;">
					<p>
						<strong><?php esc_html_e( 'Plugin Installation Enabled', 'lionhead-oxygen' ); ?></strong>
						<?php esc_html_e( 'You can install plugins below. For additional security, you can disable plugin installation.', 'lionhead-oxygen' ); ?>
					</p>
					<p>
						<button 
							type="button" 
							class="button lhd-toggle-file-mods" 
							data-set-disallow-file-mods="true"
							data-current-status="enabled"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'lhd_toggle_file_mods' ) ); ?>"
						>
							<?php esc_html_e( 'Disable Plugin Installation (Security)', 'lionhead-oxygen' ); ?>
						</button>
						<span class="lhd-file-mods-message" style="margin-left: 10px;"></span>
					</p>
					<p class="description">
						<?php esc_html_e( 'This will set DISALLOW_FILE_MODS to true in wp-config.php, preventing plugin installation for enhanced security. A backup will be created automatically.', 'lionhead-oxygen' ); ?>
					</p>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="lhd-recommended-plugins" style="margin-top: 20px;">
			<?php foreach ( $recommended_plugins as $plugin ) : ?>
				<?php
				$status = lhd_get_plugin_status( $plugin );
				$plugin_file = $plugin['slug'] . '/' . $plugin['slug'] . '.php';
				?>
				<div class="plugin-card" style="background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); padding: 20px; margin-bottom: 20px;">
					<div style="display: flex; justify-content: space-between; align-items: start;">
						<div style="flex: 1;">
							<h3 style="margin-top: 0;"><?php echo esc_html( $plugin['name'] ); ?></h3>
							<p><?php echo esc_html( $plugin['description'] ); ?></p>
							<?php if ( isset( $plugin['required'] ) && $plugin['required'] ) : ?>
								<span style="color: #d63638; font-weight: bold;"><?php esc_html_e( 'Required', 'lionhead-oxygen' ); ?></span>
							<?php else : ?>
								<span style="color: #2271b1;"><?php esc_html_e( 'Optional', 'lionhead-oxygen' ); ?></span>
							<?php endif; ?>
						</div>
						<div style="margin-left: 20px;">
							<?php if ( 'active' === $status ) : ?>
								<span class="button button-disabled" style="cursor: default;">
									<?php esc_html_e( 'Active', 'lionhead-oxygen' ); ?>
								</span>
							<?php elseif ( 'installed' === $status ) : ?>
								<button 
									type="button" 
									class="button button-primary lhd-activate-plugin" 
									data-plugin-file="<?php echo esc_attr( $plugin_file ); ?>"
									data-nonce="<?php echo esc_attr( wp_create_nonce( 'lhd_activate_plugin' ) ); ?>"
								>
									<?php esc_html_e( 'Activate', 'lionhead-oxygen' ); ?>
								</button>
							<?php else : ?>
								<?php if ( $file_mods_disabled ) : ?>
									<span class="button button-disabled" style="cursor: default;" title="<?php esc_attr_e( 'Plugin installation is disabled. See notice above.', 'lionhead-oxygen' ); ?>">
										<?php esc_html_e( 'Installation Disabled', 'lionhead-oxygen' ); ?>
									</span>
								<?php else : ?>
									<button 
										type="button" 
										class="button button-primary lhd-install-plugin" 
										data-plugin-slug="<?php echo esc_attr( $plugin['slug'] ); ?>"
										data-source="<?php echo esc_attr( isset( $plugin['source'] ) ? $plugin['source'] : 'wordpress' ); ?>"
										data-zip-url="<?php echo esc_attr( isset( $plugin['zip_url'] ) ? $plugin['zip_url'] : '' ); ?>"
										data-nonce="<?php echo esc_attr( wp_create_nonce( 'lhd_install_plugin' ) ); ?>"
									>
										<?php esc_html_e( 'Install', 'lionhead-oxygen' ); ?>
									</button>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
					<div class="lhd-plugin-message" style="margin-top: 10px; display: none;"></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<script type="text/javascript">
	jQuery(document).ready(function($) {
		// Handle DISALLOW_FILE_MODS toggle
		$('.lhd-toggle-file-mods').on('click', function(e) {
			e.preventDefault();
			var $button = $(this);
			var $message = $('.lhd-file-mods-message');
			// Get the value we want to set DISALLOW_FILE_MODS to
			// 'true' = set DISALLOW_FILE_MODS to true (disable plugin installation)
			// 'false' = set DISALLOW_FILE_MODS to false (enable plugin installation)
			var setDisallowFileMods = $button.data('set-disallow-file-mods') === 'true';
			var currentStatus = $button.data('current-status');
			var nonce = $button.data('nonce');

			// Update button text to show processing state
			var originalText = $button.text();
			$button.prop('disabled', true);
			if (setDisallowFileMods) {
				// Setting to true = disabling plugin installation
				$button.text('<?php esc_html_e( 'Disabling...', 'lionhead-oxygen' ); ?>');
			} else {
				// Setting to false = enabling plugin installation
				$button.text('<?php esc_html_e( 'Enabling...', 'lionhead-oxygen' ); ?>');
			}
			
			$message.hide().removeClass('notice-success notice-error');

			// Pass the value to set DISALLOW_FILE_MODS to
			// enable=true means set DISALLOW_FILE_MODS to true (disable installation)
			// enable=false means set DISALLOW_FILE_MODS to false (enable installation)
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'lhd_toggle_file_mods',
					enable: setDisallowFileMods ? 'true' : 'false',
					nonce: nonce
				},
				success: function(response) {
					if (response.success) {
						// Update button text and data attributes based on new status
						if (setDisallowFileMods) {
							// We just disabled, so now show enable button
							$button.text('<?php esc_html_e( 'Enable Plugin Installation', 'lionhead-oxygen' ); ?>')
								.data('set-disallow-file-mods', 'false')
								.data('current-status', 'disabled')
								.removeClass('button')
								.addClass('button button-primary');
						} else {
							// We just enabled, so now show disable button
							$button.text('<?php esc_html_e( 'Disable Plugin Installation (Security)', 'lionhead-oxygen' ); ?>')
								.data('set-disallow-file-mods', 'true')
								.data('current-status', 'enabled')
								.removeClass('button-primary')
								.addClass('button');
						}
						
						$message.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
						// Reload page after 1.5 seconds to show updated status and allow wp-config to be reloaded
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						var errorMsg = response.data.message || '<?php esc_html_e( 'An unknown error occurred.', 'lionhead-oxygen' ); ?>';
						if (response.data.error_code) {
							errorMsg += ' (Error: ' + response.data.error_code + ')';
						}
						$message.addClass('notice notice-error').html('<p>' + errorMsg + '</p>').show();
						$button.text(originalText).prop('disabled', false);
					}
				},
				error: function() {
					$message.addClass('notice notice-error').html('<p><?php esc_html_e( 'An error occurred. Please try again.', 'lionhead-oxygen' ); ?></p>').show();
					$button.text(originalText).prop('disabled', false);
				}
			});
		});

		// Handle plugin installation
		$('.lhd-install-plugin').on('click', function(e) {
			e.preventDefault();
			var $button = $(this);
			var $card = $button.closest('.plugin-card');
			var $message = $card.find('.lhd-plugin-message');
			var pluginSlug = $button.data('plugin-slug');
			var source = $button.data('source') || 'wordpress';
			var zipUrl = $button.data('zip-url') || '';
			var nonce = $button.data('nonce');

			$button.prop('disabled', true).text('<?php esc_html_e( 'Installing...', 'lionhead-oxygen' ); ?>');
			$message.hide().removeClass('notice-success notice-error');

			var ajaxData = {
				action: 'lhd_install_plugin',
				source: source,
				nonce: nonce
			};

			if (source === 'upload') {
				ajaxData.zip_url = zipUrl;
			} else {
				ajaxData.plugin_slug = pluginSlug;
			}

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: ajaxData,
				success: function(response) {
					if (response.success) {
						$message.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
						$button.text('<?php esc_html_e( 'Activate', 'lionhead-oxygen' ); ?>')
							.removeClass('lhd-install-plugin')
							.addClass('lhd-activate-plugin')
							.data('plugin-file', response.data.plugin_file)
							.data('nonce', '<?php echo esc_js( wp_create_nonce( 'lhd_activate_plugin' ) ); ?>')
							.prop('disabled', false);
					} else {
						$message.addClass('notice notice-error').html('<p>' + response.data.message + '</p>').show();
						$button.prop('disabled', false).text('<?php esc_html_e( 'Install', 'lionhead-oxygen' ); ?>');
					}
				},
				error: function() {
					$message.addClass('notice notice-error').html('<p><?php esc_html_e( 'An error occurred. Please try again.', 'lionhead-oxygen' ); ?></p>').show();
					$button.prop('disabled', false).text('<?php esc_html_e( 'Install', 'lionhead-oxygen' ); ?>');
				}
			});
		});

		// Handle plugin activation
		$('.lhd-activate-plugin').on('click', function(e) {
			e.preventDefault();
			var $button = $(this);
			var $card = $button.closest('.plugin-card');
			var $message = $card.find('.lhd-plugin-message');
			var pluginFile = $button.data('plugin-file');
			var nonce = $button.data('nonce');

			$button.prop('disabled', true).text('<?php esc_html_e( 'Activating...', 'lionhead-oxygen' ); ?>');
			$message.hide().removeClass('notice-success notice-error');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'lhd_activate_plugin',
					plugin_file: pluginFile,
					nonce: nonce
				},
				success: function(response) {
					if (response.success) {
						$message.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
						$button.text('<?php esc_html_e( 'Active', 'lionhead-oxygen' ); ?>')
							.removeClass('button-primary lhd-activate-plugin')
							.addClass('button-disabled')
							.prop('disabled', true);
						// Reload page after 1 second to show updated status
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						$message.addClass('notice notice-error').html('<p>' + response.data.message + '</p>').show();
						$button.prop('disabled', false).text('<?php esc_html_e( 'Activate', 'lionhead-oxygen' ); ?>');
					}
				},
				error: function() {
					$message.addClass('notice notice-error').html('<p><?php esc_html_e( 'An error occurred. Please try again.', 'lionhead-oxygen' ); ?></p>').show();
					$button.prop('disabled', false).text('<?php esc_html_e( 'Activate', 'lionhead-oxygen' ); ?>');
				}
			});
		});
	});
	</script>
	<?php
}

