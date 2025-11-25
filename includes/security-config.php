<?php
/**
 * Security Configuration for wp-config.php and .htaccess
 * Adds security settings to wp-config.php and .htaccess files
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ============================================================================
// WP-CONFIG.PHP SECURITY SETTINGS
// ============================================================================

/**
 * Automatically add security constants to wp-config.php
 * Adds DISALLOW_FILE_EDIT, DISALLOW_FILE_MODS, and AUTOMATIC_UPDATER_DISABLED
 *
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function lhd_add_wpconfig_security_constants() {
	// Get wp-config.php path - try multiple locations
	$wpconfig_path = ABSPATH . 'wp-config.php';
	
	// Some setups have wp-config.php one level up
	if ( ! file_exists( $wpconfig_path ) ) {
		$wpconfig_path = dirname( ABSPATH ) . '/wp-config.php';
	}
	
	// Check if wp-config.php exists and is readable
	if ( ! file_exists( $wpconfig_path ) || ! is_readable( $wpconfig_path ) ) {
		return new WP_Error( 'wpconfig_not_found', sprintf( __( 'wp-config.php file not found or not readable at: %s', 'lionhead-oxygen' ), $wpconfig_path ) );
	}

	// Check if file is writable
	if ( ! is_writable( $wpconfig_path ) ) {
		return new WP_Error( 'wpconfig_not_writable', sprintf( __( 'wp-config.php file is not writable. Please check file permissions. Path: %s', 'lionhead-oxygen' ), $wpconfig_path ) );
	}

	// Read current content
	$wpconfig_content = file_get_contents( $wpconfig_path );
	
	if ( $wpconfig_content === false ) {
		return new WP_Error( 'read_failed', __( 'Failed to read wp-config.php file.', 'lionhead-oxygen' ) );
	}
	
	// Constants to add
	$constants = array(
		"define( 'DISALLOW_FILE_MODS', true );",
		"define( 'DISALLOW_FILE_EDIT', true );",
		"define( 'AUTOMATIC_UPDATER_DISABLED', true );",
	);

	// Check which constants are missing
	$constants_to_add = array();
	foreach ( $constants as $constant ) {
		// Check if constant already exists (with both single and double quotes)
		$single_quote = strpos( $wpconfig_content, $constant ) !== false;
		$double_quote = strpos( $wpconfig_content, str_replace( "'", '"', $constant ) ) !== false;
		
		// Also check for variations with spaces
		$constant_variations = array(
			$constant,
			str_replace( "'", '"', $constant ),
			str_replace( " '", ' "', $constant ),
			str_replace( "' ", '" ', $constant ),
		);
		
		$found = false;
		foreach ( $constant_variations as $variation ) {
			if ( strpos( $wpconfig_content, $variation ) !== false ) {
				$found = true;
				break;
			}
		}
		
		if ( ! $found ) {
			$constants_to_add[] = $constant;
		}
	}

	// If all constants are already present, return success
	if ( empty( $constants_to_add ) ) {
		return true;
	}

	// Create backup
	$backup_path = $wpconfig_path . '.backup.' . date( 'Y-m-d-H-i-s' );
	if ( ! @copy( $wpconfig_path, $backup_path ) ) {
		return new WP_Error( 'backup_failed', __( 'Failed to create backup of wp-config.php. Please check file permissions.', 'lionhead-oxygen' ) );
	}

	// Find the insertion point (before "That's all, stop editing!")
	$insertion_markers = array(
		"That's all, stop editing!",
		"That's all, stop editing! Happy publishing.",
		"/* That's all, stop editing!",
	);
	
	$insertion_pos = false;
	foreach ( $insertion_markers as $marker ) {
		$pos = strpos( $wpconfig_content, $marker );
		if ( $pos !== false ) {
			$insertion_pos = $pos;
			break;
		}
	}
	
	if ( $insertion_pos === false ) {
		// If marker not found, try to find the end of the file (before closing PHP tag if exists)
		$php_close_pos = strrpos( $wpconfig_content, '?>' );
		if ( $php_close_pos !== false ) {
			$insertion_pos = $php_close_pos;
			$before_marker = substr( $wpconfig_content, 0, $insertion_pos );
			$after_marker = substr( $wpconfig_content, $insertion_pos );
			
			$new_content = rtrim( $before_marker ) . "\n\n// Security constants added by Lionhead Digital Custom Functionality Plugin\n";
			foreach ( $constants_to_add as $constant ) {
				$new_content .= $constant . "\n";
			}
			$new_content .= "\n" . $after_marker;
		} else {
			// If no PHP closing tag, append to end of file
			$new_content = rtrim( $wpconfig_content ) . "\n\n// Security constants added by Lionhead Digital Custom Functionality Plugin\n";
			foreach ( $constants_to_add as $constant ) {
				$new_content .= $constant . "\n";
			}
		}
	} else {
		// Insert before the marker
		$before_marker = substr( $wpconfig_content, 0, $insertion_pos );
		$after_marker = substr( $wpconfig_content, $insertion_pos );
		
		$new_content = rtrim( $before_marker ) . "\n\n// Security constants added by Lionhead Digital Custom Functionality Plugin\n";
		foreach ( $constants_to_add as $constant ) {
			$new_content .= $constant . "\n";
		}
		$new_content .= "\n" . $after_marker;
	}

	// Write to file with file locking
	$result = @file_put_contents( $wpconfig_path, $new_content, LOCK_EX );
	
	if ( $result === false ) {
		// Restore backup on failure
		@copy( $backup_path, $wpconfig_path );
		return new WP_Error( 'write_failed', __( 'Failed to write to wp-config.php. Backup restored. Please check file permissions.', 'lionhead-oxygen' ) );
	}

	// Store backup location
	update_option( 'lhd_wpconfig_backup', $backup_path );
	
	return true;
}

/**
 * Check and recommend wp-config.php security settings
 * Note: wp-config.php cannot be modified directly by plugins for security reasons
 * This function checks if recommended settings are present and shows admin notice
 */
function lhd_check_wpconfig_security() {
	// Only show to admins
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$wpconfig_path = ABSPATH . 'wp-config.php';
	
	// Check if wp-config.php exists and is readable
	if ( ! file_exists( $wpconfig_path ) || ! is_readable( $wpconfig_path ) ) {
		return;
	}

	$wpconfig_content = file_get_contents( $wpconfig_path );
	$recommendations = array();

	// Check for security keys
	if ( ! defined( 'AUTH_KEY' ) || AUTH_KEY === 'put your unique phrase here' ) {
		$recommendations[] = 'Security keys should be set (AUTH_KEY, SECURE_AUTH_KEY, etc.)';
	}

	// Check for DISALLOW_FILE_EDIT
	if ( strpos( $wpconfig_content, "define( 'DISALLOW_FILE_EDIT', true );" ) === false && 
		 strpos( $wpconfig_content, 'define( "DISALLOW_FILE_EDIT", true );' ) === false ) {
		$recommendations[] = 'DISALLOW_FILE_EDIT should be set to true';
	}

	// Check for DISALLOW_FILE_MODS
	if ( strpos( $wpconfig_content, "define( 'DISALLOW_FILE_MODS', true );" ) === false && 
		 strpos( $wpconfig_content, 'define( "DISALLOW_FILE_MODS", true );' ) === false ) {
		$recommendations[] = 'DISALLOW_FILE_MODS should be set to true';
	}

	// Check for AUTOMATIC_UPDATER_DISABLED
	if ( strpos( $wpconfig_content, "define( 'AUTOMATIC_UPDATER_DISABLED', true );" ) === false && 
		 strpos( $wpconfig_content, 'define( "AUTOMATIC_UPDATER_DISABLED", true );' ) === false ) {
		$recommendations[] = 'AUTOMATIC_UPDATER_DISABLED should be set to true';
	}

	// Check for WP_DEBUG
	if ( strpos( $wpconfig_content, "define( 'WP_DEBUG', true );" ) !== false ) {
		$recommendations[] = 'WP_DEBUG should be set to false on production sites';
	}

	// Check for database table prefix
	global $wpdb;
	if ( $wpdb->prefix === 'wp_' ) {
		$recommendations[] = 'Database table prefix should be changed from default "wp_"';
	}

	// Show recommendations if any
	if ( ! empty( $recommendations ) ) {
		add_action( 'admin_notices', function() use ( $recommendations ) {
			?>
			<div class="notice notice-warning">
				<p><strong>Security Recommendations for wp-config.php:</strong></p>
				<ul style="list-style: disc; margin-left: 20px;">
					<?php foreach ( $recommendations as $rec ) : ?>
						<li><?php echo esc_html( $rec ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p>See the plugin documentation for instructions on adding these settings manually.</p>
			</div>
			<?php
		});
	}
}
add_action( 'admin_init', 'lhd_check_wpconfig_security' );

/**
 * Attempt to add wp-config constants on admin init if they're missing
 * Only runs once per day to avoid performance issues
 */
function lhd_auto_add_wpconfig_constants() {
	// Only for admins
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Check if we've already tried today
	$last_attempt = get_option( 'lhd_wpconfig_last_attempt' );
	if ( $last_attempt && ( time() - $last_attempt ) < DAY_IN_SECONDS ) {
		return;
	}

	// Check if constants are already in wp-config.php
	$wpconfig_path = ABSPATH . 'wp-config.php';
	if ( ! file_exists( $wpconfig_path ) || ! is_readable( $wpconfig_path ) ) {
		return;
	}

	$wpconfig_content = file_get_contents( $wpconfig_path );
	$all_present = strpos( $wpconfig_content, "define( 'DISALLOW_FILE_MODS', true );" ) !== false &&
				   strpos( $wpconfig_content, "define( 'DISALLOW_FILE_EDIT', true );" ) !== false &&
				   strpos( $wpconfig_content, "define( 'AUTOMATIC_UPDATER_DISABLED', true );" ) !== false;

	// If all constants are present, update last attempt and return
	if ( $all_present ) {
		update_option( 'lhd_wpconfig_last_attempt', time() );
		return;
	}

	// Try to add them if file is writable
	if ( is_writable( $wpconfig_path ) ) {
		$result = lhd_add_wpconfig_security_constants();
		update_option( 'lhd_wpconfig_last_attempt', time() );
		
		if ( is_wp_error( $result ) ) {
			set_transient( 'lhd_wpconfig_error', $result->get_error_message(), 30 );
		}
	}
}
add_action( 'admin_init', 'lhd_auto_add_wpconfig_constants', 20 );

/**
 * Show admin notice if wp-config.php modification failed during activation
 */
function lhd_show_wpconfig_activation_notice() {
	$error = get_transient( 'lhd_wpconfig_error' );
	if ( $error ) {
		?>
		<div class="notice notice-error is-dismissible">
			<p><strong><?php esc_html_e( 'Lionhead Digital Plugin:', 'lionhead-oxygen' ); ?></strong> <?php echo esc_html( $error ); ?></p>
			<p><?php esc_html_e( 'You can manually add the security constants using the Security Config page under Tools.', 'lionhead-oxygen' ); ?></p>
		</div>
		<?php
		delete_transient( 'lhd_wpconfig_error' );
	}
}
add_action( 'admin_notices', 'lhd_show_wpconfig_activation_notice' );

/**
 * Get recommended wp-config.php security settings
 * Returns an array of settings that should be added to wp-config.php
 *
 * @return array Array of security settings with descriptions
 */
function lhd_get_wpconfig_security_settings() {
	return array(
		'DISALLOW_FILE_EDIT' => array(
			'code' => "define( 'DISALLOW_FILE_EDIT', true );",
			'description' => 'Prevents file editing through WordPress admin',
			'location' => 'Add before "That\'s all, stop editing!" line',
		),
		'FORCE_SSL_ADMIN' => array(
			'code' => "define( 'FORCE_SSL_ADMIN', true );",
			'description' => 'Forces SSL for admin area (requires SSL certificate)',
			'location' => 'Add before "That\'s all, stop editing!" line',
		),
		'WP_DEBUG' => array(
			'code' => "define( 'WP_DEBUG', false );",
			'description' => 'Disables debug mode on production (should be false)',
			'location' => 'Add before "That\'s all, stop editing!" line',
		),
		'WP_DEBUG_LOG' => array(
			'code' => "define( 'WP_DEBUG_LOG', false );",
			'description' => 'Disables debug logging on production',
			'location' => 'Add before "That\'s all, stop editing!" line',
		),
		'WP_DEBUG_DISPLAY' => array(
			'code' => "define( 'WP_DEBUG_DISPLAY', false );",
			'description' => 'Hides debug errors from displaying on frontend',
			'location' => 'Add before "That\'s all, stop editing!" line',
		),
		'DISALLOW_FILE_MODS' => array(
			'code' => "define( 'DISALLOW_FILE_MODS', true );",
			'description' => 'Prevents installation/update of plugins and themes (optional, use with caution)',
			'location' => 'Add before "That\'s all, stop editing!" line',
		),
		'AUTOMATIC_UPDATER_DISABLED' => array(
			'code' => "define( 'AUTOMATIC_UPDATER_DISABLED', true );",
			'description' => 'Disables automatic updates (optional, for manual control)',
			'location' => 'Add before "That\'s all, stop editing!" line',
		),
	);
}

// ============================================================================
// .HTACCESS SECURITY SETTINGS
// ============================================================================

/**
 * Add security rules to .htaccess file
 * Creates backup before modifying
 */
function lhd_add_htaccess_security() {
	// Only run if user has permission
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$htaccess_path = ABSPATH . '.htaccess';
	
	// Check if .htaccess exists, create if not
	if ( ! file_exists( $htaccess_path ) ) {
		// Create basic .htaccess with WordPress rules
		$basic_htaccess = "# BEGIN WordPress\n";
		$basic_htaccess .= "<IfModule mod_rewrite.c>\n";
		$basic_htaccess .= "RewriteEngine On\n";
		$basic_htaccess .= "RewriteBase /\n";
		$basic_htaccess .= "RewriteRule ^index\.php$ - [L]\n";
		$basic_htaccess .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
		$basic_htaccess .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
		$basic_htaccess .= "RewriteRule . /index.php [L]\n";
		$basic_htaccess .= "</IfModule>\n";
		$basic_htaccess .= "# END WordPress\n";
		
		file_put_contents( $htaccess_path, $basic_htaccess );
	}

	// Read current .htaccess
	$htaccess_content = file_get_contents( $htaccess_path );
	
	// Check if our security rules are already added
	if ( strpos( $htaccess_content, '# BEGIN Lionhead Security' ) !== false ) {
		return; // Already added
	}

	// Create backup
	$backup_path = $htaccess_path . '.backup.' . date( 'Y-m-d-H-i-s' );
	copy( $htaccess_path, $backup_path );

	// Get security rules
	$security_rules = lhd_get_htaccess_security_rules();

	// Add security rules before WordPress rules
	$wp_start = strpos( $htaccess_content, '# BEGIN WordPress' );
	
	if ( $wp_start !== false ) {
		// Insert before WordPress rules
		$new_content = substr( $htaccess_content, 0, $wp_start ) . 
					   $security_rules . "\n" . 
					   substr( $htaccess_content, $wp_start );
	} else {
		// Append to end
		$new_content = $htaccess_content . "\n" . $security_rules;
	}

	// Write to file
	$result = file_put_contents( $htaccess_path, $new_content );

	if ( $result !== false ) {
		// Success - log backup location
		update_option( 'lhd_htaccess_backup', $backup_path );
	}
}

/**
 * Get .htaccess security rules
 *
 * @return string Security rules for .htaccess
 */
function lhd_get_htaccess_security_rules() {
	$rules = "# BEGIN Lionhead Security\n";
	$rules .= "# Added by Lionhead Digital Custom Functionality Plugin\n";
	$rules .= "# Backup created before modification\n\n";

	// Disable directory browsing
	$rules .= "# Disable directory browsing\n";
	$rules .= "Options -Indexes\n\n";

	// Protect wp-config.php
	$rules .= "# Protect wp-config.php\n";
	$rules .= "<Files wp-config.php>\n";
	$rules .= "    Order allow,deny\n";
	$rules .= "    Deny from all\n";
	$rules .= "</Files>\n\n";

	// Protect .htaccess
	$rules .= "# Protect .htaccess\n";
	$rules .= "<Files .htaccess>\n";
	$rules .= "    Order allow,deny\n";
	$rules .= "    Deny from all\n";
	$rules .= "</Files>\n\n";

	// Protect .htpasswd
	$rules .= "# Protect .htpasswd\n";
	$rules .= "<Files .htpasswd>\n";
	$rules .= "    Order allow,deny\n";
	$rules .= "    Deny from all\n";
	$rules .= "</Files>\n\n";

	// Block access to readme files
	$rules .= "# Block access to readme files\n";
	$rules .= "<FilesMatch \"^(readme|license|changelog|readme\.txt|readme\.html)$\">\n";
	$rules .= "    Order allow,deny\n";
	$rules .= "    Deny from all\n";
	$rules .= "</FilesMatch>\n\n";

	// Block access to sensitive files
	$rules .= "# Block access to sensitive files\n";
	$rules .= "<FilesMatch \"\\.(htaccess|htpasswd|ini|log|sh|sql|bak|backup|old)$\">\n";
	$rules .= "    Order allow,deny\n";
	$rules .= "    Deny from all\n";
	$rules .= "</FilesMatch>\n\n";

	// Disable XML-RPC
	$rules .= "# Disable XML-RPC\n";
	$rules .= "<Files xmlrpc.php>\n";
	$rules .= "    Order allow,deny\n";
	$rules .= "    Deny from all\n";
	$rules .= "</Files>\n\n";

	// Block suspicious query strings
	$rules .= "# Block suspicious query strings\n";
	$rules .= "<IfModule mod_rewrite.c>\n";
	$rules .= "    RewriteEngine On\n";
	$rules .= "    RewriteCond %{QUERY_STRING} (<|%3C).*script.*(>|%3E) [NC,OR]\n";
	$rules .= "    RewriteCond %{QUERY_STRING} GLOBALS(=|[|%[0-9A-Z]{0,2}) [OR]\n";
	$rules .= "    RewriteCond %{QUERY_STRING} _REQUEST(=|[|%[0-9A-Z]{0,2}) [OR]\n";
	$rules .= "    RewriteCond %{QUERY_STRING} proc/self/environ [OR]\n";
	$rules .= "    RewriteCond %{QUERY_STRING} mosConfig_[a-zA-Z_]{1,21}(=|%3D) [OR]\n";
	$rules .= "    RewriteCond %{QUERY_STRING} base64_encode.*\\(.*\\) [OR]\n";
	$rules .= "    RewriteCond %{QUERY_STRING} (<|%3C).*iframe.*(>|%3E) [NC,OR]\n";
	$rules .= "    RewriteCond %{QUERY_STRING} (<|%3C).*object.*(>|%3E) [NC,OR]\n";
	$rules .= "    RewriteCond %{QUERY_STRING} (<|%3C).*embed.*(>|%3E) [NC]\n";
	$rules .= "    RewriteRule ^(.*)$ - [F,L]\n";
	$rules .= "</IfModule>\n\n";

	// Block bad user agents
	$rules .= "# Block bad user agents\n";
	$rules .= "<IfModule mod_rewrite.c>\n";
	$rules .= "    RewriteCond %{HTTP_USER_AGENT} ^$ [OR]\n";
	$rules .= "    RewriteCond %{HTTP_USER_AGENT} ^(.*)(<|>|'|%0A|%0D|%27|%3C|%3E|%00) [NC,OR]\n";
	$rules .= "    RewriteCond %{HTTP_USER_AGENT} ^(java|curl|python|wget|libwww) [NC,OR]\n";
	$rules .= "    RewriteCond %{HTTP_USER_AGENT} ^(.*)(libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]\n";
	$rules .= "    RewriteCond %{HTTP_USER_AGENT} ^(.*)(;|<|>|'|\"|%0A|%0D|%27|%3C|%3E|%00) [NC]\n";
	$rules .= "    RewriteRule ^(.*)$ - [F,L]\n";
	$rules .= "</IfModule>\n\n";

	// Security headers (if mod_headers is available)
	$rules .= "# Security headers\n";
	$rules .= "<IfModule mod_headers.c>\n";
	$rules .= "    # Prevent clickjacking\n";
	$rules .= "    Header always set X-Frame-Options \"SAMEORIGIN\"\n";
	$rules .= "    # Prevent MIME type sniffing\n";
	$rules .= "    Header always set X-Content-Type-Options \"nosniff\"\n";
	$rules .= "    # XSS Protection\n";
	$rules .= "    Header always set X-XSS-Protection \"1; mode=block\"\n";
	$rules .= "    # Referrer Policy\n";
	$rules .= "    Header always set Referrer-Policy \"strict-origin-when-cross-origin\"\n";
	$rules .= "</IfModule>\n\n";

	$rules .= "# END Lionhead Security\n";
	
	return $rules;
}

/**
 * Add security rules to .htaccess on plugin activation
 * This function is called via register_activation_hook in plugin.php
 */
function lhd_activate_htaccess_security() {
	lhd_add_htaccess_security();
}

/**
 * Add admin menu for security configuration
 */
function lhd_add_security_config_menu() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	add_submenu_page(
		'tools.php',
		__( 'Security Configuration', 'lionhead-oxygen' ),
		__( 'Security Config', 'lionhead-oxygen' ),
		'manage_options',
		'lhd-security-config',
		'lhd_security_config_page'
	);
}
add_action( 'admin_menu', 'lhd_add_security_config_menu' );

/**
 * Security configuration admin page
 */
function lhd_security_config_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle form submission for .htaccess
	if ( isset( $_POST['lhd_add_htaccess'] ) && check_admin_referer( 'lhd_security_config' ) ) {
		lhd_add_htaccess_security();
		echo '<div class="notice notice-success"><p>Security rules added to .htaccess successfully!</p></div>';
	}

	// Handle form submission for wp-config.php
	if ( isset( $_POST['lhd_add_wpconfig'] ) && check_admin_referer( 'lhd_security_config' ) ) {
		$result = lhd_add_wpconfig_security_constants();
		if ( is_wp_error( $result ) ) {
			echo '<div class="notice notice-error"><p><strong>Error:</strong> ' . esc_html( $result->get_error_message() ) . '</p></div>';
		} else {
			echo '<div class="notice notice-success"><p>Security constants added to wp-config.php successfully!</p></div>';
		}
	}

	$htaccess_path = ABSPATH . '.htaccess';
	$htaccess_exists = file_exists( $htaccess_path );
	$htaccess_readable = $htaccess_exists && is_readable( $htaccess_path );
	$security_added = false;
	
	if ( $htaccess_readable ) {
		$htaccess_content = file_get_contents( $htaccess_path );
		$security_added = strpos( $htaccess_content, '# BEGIN Lionhead Security' ) !== false;
	}

	$wpconfig_settings = lhd_get_wpconfig_security_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Security Configuration', 'lionhead-oxygen' ); ?></h1>
		
		<h2><?php esc_html_e( '.htaccess Security Rules', 'lionhead-oxygen' ); ?></h2>
		<p><?php esc_html_e( 'Add security rules to your .htaccess file to protect against common attacks.', 'lionhead-oxygen' ); ?></p>
		
		<?php if ( $htaccess_exists ) : ?>
			<?php if ( $security_added ) : ?>
				<div class="notice notice-success">
					<p><strong>✓ Security rules are already added to .htaccess</strong></p>
				</div>
			<?php else : ?>
				<form method="post">
					<?php wp_nonce_field( 'lhd_security_config' ); ?>
					<p>
						<button type="submit" name="lhd_add_htaccess" class="button button-primary">
							<?php esc_html_e( 'Add Security Rules to .htaccess', 'lionhead-oxygen' ); ?>
						</button>
					</p>
					<p class="description">
						<?php esc_html_e( 'A backup of your .htaccess file will be created before modification.', 'lionhead-oxygen' ); ?>
					</p>
				</form>
			<?php endif; ?>
		<?php else : ?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( '.htaccess file not found. It will be created when you add security rules.', 'lionhead-oxygen' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'lhd_security_config' ); ?>
					<p>
						<button type="submit" name="lhd_add_htaccess" class="button button-primary">
							<?php esc_html_e( 'Create .htaccess with Security Rules', 'lionhead-oxygen' ); ?>
						</button>
					</p>
				</form>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'wp-config.php Security Settings', 'lionhead-oxygen' ); ?></h2>
		<p><?php esc_html_e( 'Automatically add security constants to your wp-config.php file, or manually add them using the code below.', 'lionhead-oxygen' ); ?></p>
		
		<?php
		$wpconfig_path = ABSPATH . 'wp-config.php';
		$wpconfig_exists = file_exists( $wpconfig_path );
		$wpconfig_readable = $wpconfig_exists && is_readable( $wpconfig_path );
		$wpconfig_writable = $wpconfig_exists && is_writable( $wpconfig_path );
		$constants_added = false;
		
		if ( $wpconfig_readable ) {
			$wpconfig_content = file_get_contents( $wpconfig_path );
			$constants_added = strpos( $wpconfig_content, "define( 'DISALLOW_FILE_MODS', true );" ) !== false &&
							   strpos( $wpconfig_content, "define( 'DISALLOW_FILE_EDIT', true );" ) !== false &&
							   strpos( $wpconfig_content, "define( 'AUTOMATIC_UPDATER_DISABLED', true );" ) !== false;
		}
		?>

		<?php if ( $wpconfig_exists ) : ?>
			<?php if ( $constants_added ) : ?>
				<div class="notice notice-success">
					<p><strong>✓ Security constants are already added to wp-config.php</strong></p>
				</div>
			<?php elseif ( $wpconfig_writable ) : ?>
				<form method="post">
					<?php wp_nonce_field( 'lhd_security_config' ); ?>
					<p>
						<button type="submit" name="lhd_add_wpconfig" class="button button-primary">
							<?php esc_html_e( 'Add Security Constants to wp-config.php', 'lionhead-oxygen' ); ?>
						</button>
					</p>
					<p class="description">
						<?php esc_html_e( 'A backup of your wp-config.php file will be created before modification.', 'lionhead-oxygen' ); ?>
					</p>
				</form>
			<?php else : ?>
				<div class="notice notice-warning">
					<p><?php esc_html_e( 'wp-config.php file is not writable. Please check file permissions or add the constants manually.', 'lionhead-oxygen' ); ?></p>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( 'wp-config.php file not found.', 'lionhead-oxygen' ); ?></p>
			</div>
		<?php endif; ?>

		<h3><?php esc_html_e( 'Manual Configuration', 'lionhead-oxygen' ); ?></h3>
		<p><?php esc_html_e( 'If automatic addition is not possible, you can manually add these settings to your wp-config.php file:', 'lionhead-oxygen' ); ?></p>
		
		<div class="card">
			<h3><?php esc_html_e( 'Recommended Settings', 'lionhead-oxygen' ); ?></h3>
			<p><?php esc_html_e( 'Add these lines to your wp-config.php file before the line that says "That\'s all, stop editing!"', 'lionhead-oxygen' ); ?></p>
			
			<?php foreach ( $wpconfig_settings as $key => $setting ) : ?>
				<div style="margin: 20px 0; padding: 15px; background: #f5f5f5; border-left: 4px solid #0073aa;">
					<h4><?php echo esc_html( $key ); ?></h4>
					<p><strong><?php echo esc_html( $setting['description'] ); ?></strong></p>
					<p><code style="background: #fff; padding: 10px; display: block; border: 1px solid #ddd;"><?php echo esc_html( $setting['code'] ); ?></code></p>
					<p class="description"><?php echo esc_html( $setting['location'] ); ?></p>
				</div>
			<?php endforeach; ?>
			
			<div class="notice notice-info">
				<p><strong><?php esc_html_e( 'Important:', 'lionhead-oxygen' ); ?></strong></p>
				<ul style="list-style: disc; margin-left: 20px;">
					<li><?php esc_html_e( 'Always backup your wp-config.php file before making changes', 'lionhead-oxygen' ); ?></li>
					<li><?php esc_html_e( 'Some settings may require additional configuration (like SSL certificate for FORCE_SSL_ADMIN)', 'lionhead-oxygen' ); ?></li>
					<li><?php esc_html_e( 'Test your site after adding these settings', 'lionhead-oxygen' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
	<?php
}

