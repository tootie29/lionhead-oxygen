<?php
/**
 * Security Functions
 * Prevents hacking and malware attacks
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ============================================================================
// FILE EDITING PROTECTION
// ============================================================================

/**
 * Disable file editing in WordPress admin
 * Prevents hackers from editing theme/plugin files through admin
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// ============================================================================
// VERSION INFORMATION HIDING
// ============================================================================

/**
 * Remove WordPress version from head
 * Hides version info from potential attackers
 */
function lhd_remove_version_info() {
	return '';
}
add_filter( 'the_generator', 'lhd_remove_version_info' );

/**
 * Remove WordPress version from scripts and styles
 */
function lhd_remove_version_from_assets( $src ) {
	if ( strpos( $src, 'ver=' ) !== false ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'script_loader_src', 'lhd_remove_version_from_assets', 9999 );
add_filter( 'style_loader_src', 'lhd_remove_version_from_assets', 9999 );

/**
 * Remove WordPress version from RSS feeds
 */
remove_action( 'wp_head', 'wp_generator' );

// ============================================================================
// LOGIN SECURITY
// ============================================================================

/**
 * Hide login errors to prevent username enumeration
 */
function lhd_hide_login_errors() {
	return __( 'Invalid username or password.', 'lionhead-oxygen' );
}
add_filter( 'login_errors', 'lhd_hide_login_errors' );

/**
 * Disable login by email (username only)
 * Prevents email-based attacks
 */
remove_filter( 'authenticate', 'wp_authenticate_email_password', 20 );

/**
 * Limit login attempts (basic protection)
 */
function lhd_limit_login_attempts() {
	$ip = lhd_get_user_ip();
	$transient_key = 'lhd_login_attempts_' . md5( $ip );
	$attempts = get_transient( $transient_key );

	if ( $attempts === false ) {
		set_transient( $transient_key, 1, 15 * MINUTE_IN_SECONDS );
		return;
	}

	if ( $attempts >= 5 ) {
		wp_die(
			__( 'Too many login attempts. Please try again in 15 minutes.', 'lionhead-oxygen' ),
			__( 'Login Blocked', 'lionhead-oxygen' ),
			array( 'response' => 403 )
		);
	}

	set_transient( $transient_key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
}
add_action( 'wp_login_failed', 'lhd_limit_login_attempts' );

/**
 * Clear login attempts on successful login
 */
function lhd_clear_login_attempts( $user_login ) {
	$ip = lhd_get_user_ip();
	$transient_key = 'lhd_login_attempts_' . md5( $ip );
	delete_transient( $transient_key );
}
add_action( 'wp_login', 'lhd_clear_login_attempts' );

/**
 * Get user's real IP address
 */
function lhd_get_user_ip() {
	$ip_keys = array(
		'HTTP_CF_CONNECTING_IP', // Cloudflare
		'HTTP_X_REAL_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_CLIENT_IP',
		'REMOTE_ADDR',
	);

	foreach ( $ip_keys as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
			// Handle comma-separated IPs (from proxies)
			if ( strpos( $ip, ',' ) !== false ) {
				$ip = explode( ',', $ip );
				$ip = trim( $ip[0] );
			}
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}

	return '0.0.0.0';
}

// ============================================================================
// XML-RPC PROTECTION
// ============================================================================

/**
 * Disable XML-RPC to prevent brute force attacks
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove XML-RPC from head
 */
remove_action( 'wp_head', 'rsd_link' );

/**
 * Block XML-RPC requests
 */
function lhd_block_xmlrpc_requests() {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		wp_die(
			__( 'XML-RPC is disabled for security reasons.', 'lionhead-oxygen' ),
			__( 'Forbidden', 'lionhead-oxygen' ),
			array( 'response' => 403 )
		);
	}
}
add_action( 'init', 'lhd_block_xmlrpc_requests' );

// ============================================================================
// SECURITY HEADERS
// ============================================================================

/**
 * Add security headers to prevent various attacks
 */
function lhd_add_security_headers() {
	if ( ! is_admin() ) {
		// Prevent clickjacking
		header( 'X-Frame-Options: SAMEORIGIN' );

		// Prevent MIME type sniffing
		header( 'X-Content-Type-Options: nosniff' );

		// Enable XSS protection
		header( 'X-XSS-Protection: 1; mode=block' );

		// Referrer policy
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );

		// Content Security Policy (basic)
		header( "Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' data: https:; frame-ancestors 'self'" );

		// Permissions Policy (formerly Feature Policy)
		header( "Permissions-Policy: geolocation=(), microphone=(), camera=()" );
	}
}
add_action( 'send_headers', 'lhd_add_security_headers' );

// ============================================================================
// DIRECTORY BROWSING PROTECTION
// ============================================================================

/**
 * Disable directory browsing
 */
function lhd_disable_directory_browsing() {
	if ( ! is_admin() ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		// Block common sensitive files
		$blocked_files = array(
			'readme.html',
			'readme.txt',
			'license.txt',
			'wp-config.php',
			'.htaccess',
			'.htpasswd',
		);

		foreach ( $blocked_files as $file ) {
			if ( strpos( $request_uri, $file ) !== false ) {
				wp_die(
					__( 'Access denied.', 'lionhead-oxygen' ),
					__( 'Forbidden', 'lionhead-oxygen' ),
					array( 'response' => 403 )
				);
			}
		}
	}
}
add_action( 'init', 'lhd_disable_directory_browsing' );

// ============================================================================
// SUSPICIOUS QUERY STRING PROTECTION
// ============================================================================

/**
 * Block suspicious query strings and patterns
 */
function lhd_block_suspicious_queries() {
	if ( is_admin() ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$query_string = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';

	// Suspicious patterns
	$suspicious_patterns = array(
		'<script',
		'javascript:',
		'base64',
		'eval(',
		'exec(',
		'../',
		'..\\',
		'union select',
		'select *',
		'insert into',
		'drop table',
		'wp-config',
		'etc/passwd',
		'boot.ini',
		'cmd=',
		'phpinfo',
		'<?php',
		'<iframe',
		'<object',
		'<embed',
	);

	$combined = strtolower( $request_uri . ' ' . $query_string );

	foreach ( $suspicious_patterns as $pattern ) {
		if ( strpos( $combined, $pattern ) !== false ) {
			wp_die(
				__( 'Suspicious activity detected. Access denied.', 'lionhead-oxygen' ),
				__( 'Forbidden', 'lionhead-oxygen' ),
				array( 'response' => 403 )
			);
		}
	}
}
add_action( 'init', 'lhd_block_suspicious_queries', 1 );

// ============================================================================
// AUTHOR SCANNING PROTECTION
// ============================================================================

/**
 * Block author enumeration attempts
 */
function lhd_block_author_scanning() {
	if ( ! is_admin() && isset( $_GET['author'] ) ) {
		$author_id = absint( $_GET['author'] );
		if ( $author_id > 0 ) {
			wp_safe_redirect( home_url(), 301 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'lhd_block_author_scanning' );

// ============================================================================
// UNNECESSARY HEADERS REMOVAL
// ============================================================================

/**
 * Remove unnecessary headers that reveal information
 */
function lhd_remove_unnecessary_headers() {
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
}
add_action( 'init', 'lhd_remove_unnecessary_headers' );

// ============================================================================
// ADMIN SECURITY
// ============================================================================

/**
 * Change admin login URL (optional - can be enabled if needed)
 * Uncomment to enable custom login URL
 */
/*
function lhd_custom_login_url() {
	if ( strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ) {
		wp_safe_redirect( home_url( '/custom-login/' ), 301 );
		exit;
	}
}
add_action( 'init', 'lhd_custom_login_url' );
*/

/**
 * Disable user registration if not needed
 */
if ( ! get_option( 'users_can_register' ) ) {
	add_filter( 'option_users_can_register', '__return_false' );
}

// ============================================================================
// FILE UPLOAD SECURITY
// ============================================================================

/**
 * Restrict dangerous file types from uploads
 */
function lhd_restrict_file_uploads( $file ) {
	$dangerous_extensions = array(
		'php',
		'php3',
		'php4',
		'php5',
		'phtml',
		'pl',
		'py',
		'jsp',
		'asp',
		'sh',
		'cgi',
		'exe',
		'scr',
		'bat',
		'cmd',
		'com',
		'pif',
		'vbs',
		'js',
	);

	$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

	if ( in_array( $file_extension, $dangerous_extensions, true ) ) {
		$file['error'] = __( 'This file type is not allowed for security reasons.', 'lionhead-oxygen' );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'lhd_restrict_file_uploads' );

// ============================================================================
// DATABASE SECURITY
// ============================================================================

/**
 * Change database table prefix (if not already changed)
 * This is informational - actual prefix change requires manual database update
 */
function lhd_check_table_prefix() {
	global $wpdb;
	
	// Warn if using default prefix (informational only)
	if ( $wpdb->prefix === 'wp_' ) {
		// Log warning but don't break site
		// Actual prefix change requires manual database update
	}
}

// ============================================================================
// PINGBACK PROTECTION
// ============================================================================

/**
 * Disable pingbacks to prevent DDoS attacks
 */
function lhd_disable_pingbacks( $methods ) {
	unset( $methods['pingback.ping'] );
	unset( $methods['pingback.extensions.getPingbacks'] );
	return $methods;
}
add_filter( 'xmlrpc_methods', 'lhd_disable_pingbacks' );

/**
 * Remove pingback header
 */
function lhd_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
}
add_filter( 'wp_headers', 'lhd_remove_pingback_header' );

// ============================================================================
// COMMENTS SECURITY
// ============================================================================

/**
 * Sanitize comment data
 */
function lhd_sanitize_comments( $commentdata ) {
	// Remove potential XSS vectors
	$commentdata['comment_content'] = wp_kses_post( $commentdata['comment_content'] );
	$commentdata['comment_author'] = sanitize_text_field( $commentdata['comment_author'] );
	$commentdata['comment_author_email'] = sanitize_email( $commentdata['comment_author_email'] );
	$commentdata['comment_author_url'] = esc_url_raw( $commentdata['comment_author_url'] );

	return $commentdata;
}
add_filter( 'preprocess_comment', 'lhd_sanitize_comments' );

// ============================================================================
// ADMIN NOTICES
// ============================================================================

/**
 * Security status notice (optional - can be removed if not needed)
 */
function lhd_security_status_notice() {
	if ( current_user_can( 'manage_options' ) ) {
		$screen = get_current_screen();
		if ( $screen && 'dashboard' === $screen->id ) {
			// Security is active - no action needed
			// This is just a placeholder for future security dashboard
		}
	}
}
// Uncomment to enable security dashboard notices
// add_action( 'admin_notices', 'lhd_security_status_notice' );

