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

	// Check if constant already exists
	$patterns = array(
		"/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*(true|false)\s*\)\s*;/i",
		"/define\s*\(\s*['\"]DISALLOW_FILE_MODS['\"]\s*,\s*(true|false)\s*\)\s*;/i",
	);

	$found = false;
	foreach ( $patterns as $pattern ) {
		if ( preg_match( $pattern, $wpconfig_content ) ) {
			// Replace existing constant
			$wpconfig_content = preg_replace( $pattern, $new_constant, $wpconfig_content );
			$found = true;
			break;
		}
	}

	if ( ! $found ) {
		// Constant doesn't exist, add it before "That's all, stop editing!"
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

				if ( $open_comments === $close_comments ) {
					$insertion_pos = $pos;
					break;
				}
			}
		}

		if ( $insertion_pos !== false ) {
			$before_marker = substr( $wpconfig_content, 0, $insertion_pos );
			$after_marker = substr( $wpconfig_content, $insertion_pos );
			$wpconfig_content = rtrim( $before_marker ) . "\n" . $new_constant . "\n\n" . $after_marker;
		} else {
			// Append to end if marker not found
			$wpconfig_content = rtrim( $wpconfig_content ) . "\n" . $new_constant . "\n";
		}
	}

	// Write to file
	$result = @file_put_contents( $wpconfig_path, $wpconfig_content, LOCK_EX );

	if ( $result === false ) {
		// Restore backup on failure
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_failed', __( 'Failed to write to wp-config.php. Backup restored.', 'lionhead-oxygen' ) );
	}

	// Store backup location
	update_option( 'lhd_wpconfig_backup', $backup_path );

	return array(
		'success' => true,
		'message' => $enable 
			? __( 'Plugin installation has been disabled (DISALLOW_FILE_MODS set to true).', 'lionhead-oxygen' )
			: __( 'Plugin installation has been enabled (DISALLOW_FILE_MODS set to false).', 'lionhead-oxygen' ),
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
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
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
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	add_submenu_page(
		'plugins.php',
		__( 'Recommended Plugins', 'lionhead-oxygen' ),
		__( 'Recommended', 'lionhead-oxygen' ),
		'install_plugins',
		'lhd-recommended-plugins',
		'lhd_recommended_plugins_page'
	);
}
add_action( 'admin_menu', 'lhd_add_recommended_plugins_menu' );

/**
 * Recommended plugins admin page
 */
function lhd_recommended_plugins_page() {
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
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
							class="button button-primary lhd-enable-file-mods" 
							data-enable="false"
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
							class="button lhd-disable-file-mods" 
							data-enable="true"
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
		$('.lhd-enable-file-mods, .lhd-disable-file-mods').on('click', function(e) {
			e.preventDefault();
			var $button = $(this);
			var $message = $('.lhd-file-mods-message');
			var enable = $button.data('enable') === 'true';
			var nonce = $button.data('nonce');

			$button.prop('disabled', true);
			$message.hide().removeClass('notice-success notice-error');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'lhd_toggle_file_mods',
					enable: enable ? 'true' : 'false',
					nonce: nonce
				},
				success: function(response) {
					if (response.success) {
						$message.addClass('notice notice-success').html('<p>' + response.data.message + '</p>').show();
						// Reload page after 1 second to show updated status
						setTimeout(function() {
							location.reload();
						}, 1000);
					} else {
						$message.addClass('notice notice-error').html('<p>' + response.data.message + '</p>').show();
						$button.prop('disabled', false);
					}
				},
				error: function() {
					$message.addClass('notice notice-error').html('<p><?php esc_html_e( 'An error occurred. Please try again.', 'lionhead-oxygen' ); ?></p>').show();
					$button.prop('disabled', false);
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

