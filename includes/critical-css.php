<?php
/**
 * Critical CSS Generation
 * Generates and inlines critical CSS for pages, posts, and custom post types
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ============================================================================
// CRITICAL CSS STORAGE & RETRIEVAL
// ============================================================================

/**
 * Get critical CSS for current page/post
 *
 * @param int|null $post_id Post ID (null for current post).
 * @return string Critical CSS or empty string.
 */
function lhd_get_critical_css( $post_id = null ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return '';
	}

	// Try to get from post meta first
	$critical_css = get_post_meta( $post_id, '_lhd_critical_css', true );

	if ( ! empty( $critical_css ) ) {
		return $critical_css;
	}

	// Fallback to transient cache
	$cache_key = 'lhd_critical_css_' . $post_id;
	$critical_css = get_transient( $cache_key );

	if ( $critical_css !== false ) {
		// Store in post meta for persistence
		update_post_meta( $post_id, '_lhd_critical_css', $critical_css );
		return $critical_css;
	}

	return '';
}

/**
 * Save critical CSS for a post
 *
 * @param int    $post_id Post ID.
 * @param string $css     Critical CSS content.
 * @return bool Success status.
 */
function lhd_save_critical_css( $post_id, $css ) {
	if ( ! $post_id || empty( $css ) ) {
		return false;
	}

	// Minify CSS
	$css = lhd_minify_css( $css );

	// Save to post meta
	$saved = update_post_meta( $post_id, '_lhd_critical_css', $css );

	// Also cache in transient
	$cache_key = 'lhd_critical_css_' . $post_id;
	set_transient( $cache_key, $css, 30 * DAY_IN_SECONDS );

	return $saved !== false;
}

/**
 * Delete critical CSS for a post
 *
 * @param int $post_id Post ID.
 * @return bool Success status.
 */
function lhd_delete_critical_css( $post_id ) {
	delete_post_meta( $post_id, '_lhd_critical_css' );

	$cache_key = 'lhd_critical_css_' . $post_id;
	delete_transient( $cache_key );

	return true;
}

// ============================================================================
// CSS MINIFICATION
// ============================================================================

/**
 * Minify CSS
 *
 * @param string $css CSS content.
 * @return string Minified CSS.
 */
function lhd_minify_css( $css ) {
	// Remove comments
	$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

	// Remove whitespace
	$css = preg_replace( '/\s+/', ' ', $css );
	$css = preg_replace( '/\s*([{}|:;,])\s*/', '$1', $css );

	// Remove unnecessary spaces
	$css = str_replace( array( '; ', ' {', '{ ', ' }', '} ', ': ', ' :' ), array( ';', '{', '{', '}', '}', ':', ':' ), $css );

	// Remove last semicolon before closing brace
	$css = preg_replace( '/;}/', '}', $css );

	// Trim
	$css = trim( $css );

	return $css;
}

// ============================================================================
// CRITICAL CSS GENERATION
// ============================================================================

/**
 * Generate critical CSS for a post
 * This extracts above-the-fold CSS from the page
 *
 * @param int $post_id Post ID.
 * @return string|false Critical CSS or false on failure.
 */
function lhd_generate_critical_css( $post_id ) {
	if ( ! $post_id ) {
		return false;
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		return false;
	}

	// Get post type
	$post_type = get_post_type( $post_id );

	// Get all enqueued styles
	global $wp_styles;
	$critical_css = '';

	if ( isset( $wp_styles->registered ) ) {
		// Collect CSS from registered stylesheets
		foreach ( $wp_styles->registered as $handle => $style ) {
			// Skip if not a valid stylesheet
			if ( empty( $style->src ) ) {
				continue;
			}

			// Get CSS file path
			$css_url = $style->src;
			$css_path = lhd_get_css_file_path( $css_url );

			if ( $css_path && file_exists( $css_path ) ) {
				// Extract critical CSS from file
				$file_css = file_get_contents( $css_path );
				$critical_css .= lhd_extract_critical_css( $file_css, $post_type );
			}
		}
	}

	// Add theme-specific critical CSS based on post type
	$critical_css .= lhd_get_post_type_critical_css( $post_type );

	// Add inline styles if any
	$critical_css .= lhd_get_inline_critical_css( $post_id );

	return $critical_css;
}

/**
 * Get CSS file path from URL
 *
 * @param string $url CSS file URL.
 * @return string|false File path or false.
 */
function lhd_get_css_file_path( $url ) {
	// Convert URL to path
	$upload_dir = wp_upload_dir();
	$content_url = content_url();
	$plugins_url = plugins_url();

	// Check if it's a content URL
	if ( strpos( $url, $content_url ) === 0 ) {
		$path = str_replace( $content_url, WP_CONTENT_DIR, $url );
		return $path;
	}

	// Check if it's a plugin URL
	if ( strpos( $url, $plugins_url ) === 0 ) {
		$path = str_replace( $plugins_url, WP_PLUGIN_DIR, $url );
		return $path;
	}

	// Check if it's an absolute URL to the site
	$site_url = site_url();
	if ( strpos( $url, $site_url ) === 0 ) {
		$path = str_replace( $site_url, ABSPATH, $url );
		return $path;
	}

	return false;
}

/**
 * Extract critical CSS from full CSS
 * This is a simplified version - in production, you'd use a service like Critical CSS API
 *
 * @param string $css      Full CSS content.
 * @param string $post_type Post type.
 * @return string Critical CSS.
 */
function lhd_extract_critical_css( $css, $post_type = '' ) {
	// This is a basic extraction - for production, consider using:
	// - Critical CSS API (https://www.criticalcss.com/)
	// - Penthouse (https://github.com/pocketjins/penthouse)
	// - Critical npm package

	// Extract common above-the-fold selectors
	$critical_selectors = array(
		'body',
		'html',
		'*',
		'*::before',
		'*::after',
		'.container',
		'.wrapper',
		'header',
		'.header',
		'nav',
		'.nav',
		'.navigation',
		'.menu',
		'.site-header',
		'.site-navigation',
		'h1',
		'h2',
		'h3',
		'.hero',
		'.banner',
		'.above-fold',
		'.site-title',
		'.logo',
	);

	// Post type specific selectors
	$post_type_selectors = array(
		'post' => array( '.entry-content', '.post-content', '.article', '.single-post' ),
		'page' => array( '.page-content', '.entry-content', '.page' ),
	);

	if ( isset( $post_type_selectors[ $post_type ] ) ) {
		$critical_selectors = array_merge( $critical_selectors, $post_type_selectors[ $post_type ] );
	}

	$critical_css = '';

	// Extract CSS rules for critical selectors
	foreach ( $critical_selectors as $selector ) {
		// Match CSS rules for this selector
		$pattern = '/' . preg_quote( $selector, '/' ) . '\s*\{[^}]*\}/i';
		if ( preg_match_all( $pattern, $css, $matches ) ) {
			$critical_css .= implode( "\n", $matches[0] ) . "\n";
		}

		// Also match with variations (spaces, classes, etc.)
		$variations = array(
			$selector . ' ',
			$selector . '.',
			$selector . '#',
			$selector . ':',
		);

		foreach ( $variations as $variation ) {
			$pattern = '/' . preg_quote( $variation, '/' ) . '[^{]*\{[^}]*\}/i';
			if ( preg_match_all( $pattern, $css, $matches ) ) {
				$critical_css .= implode( "\n", $matches[0] ) . "\n";
			}
		}
	}

	// Limit critical CSS size (first 50KB)
	$critical_css = substr( $critical_css, 0, 50000 );

	return $critical_css;
}

/**
 * Get post type specific critical CSS
 *
 * @param string $post_type Post type.
 * @return string Critical CSS.
 */
function lhd_get_post_type_critical_css( $post_type ) {
	$css = '';

	// Base critical CSS for all pages
	$css .= '
		body { margin: 0; padding: 0; }
		* { box-sizing: border-box; }
		img { max-width: 100%; height: auto; }
	';

	// Post type specific CSS
	switch ( $post_type ) {
		case 'post':
			$css .= '
				.entry-header, .post-header { margin-bottom: 1em; }
				.entry-content, .post-content { line-height: 1.6; }
			';
			break;

		case 'page':
			$css .= '
				.page-content { padding: 2em 0; }
			';
			break;

		case 'case_result':
			$css .= '
				.case-result { padding: 1em; }
				.case-result-header { margin-bottom: 1em; }
			';
			break;
	}

	return $css;
}

/**
 * Get inline critical CSS from post content
 *
 * @param int $post_id Post ID.
 * @return string Critical CSS.
 */
function lhd_get_inline_critical_css( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}

	$css = '';

	// Extract inline styles from post content
	$content = $post->post_content;

	// Match <style> tags
	if ( preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $content, $matches ) ) {
		foreach ( $matches[1] as $style_content ) {
			$css .= $style_content . "\n";
		}
	}

	return $css;
}

// ============================================================================
// CRITICAL CSS OUTPUT
// ============================================================================

/**
 * Output critical CSS in head
 */
function lhd_output_critical_css() {
	// Only on frontend
	if ( is_admin() ) {
		return;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$critical_css = lhd_get_critical_css( $post_id );

	if ( empty( $critical_css ) ) {
		// Try to generate if not exists
		$critical_css = lhd_generate_critical_css( $post_id );
		if ( $critical_css ) {
			lhd_save_critical_css( $post_id, $critical_css );
		}
	}

	if ( ! empty( $critical_css ) ) {
		echo '<style id="lhd-critical-css">' . esc_html( $critical_css ) . '</style>' . "\n";
	}
}
add_action( 'wp_head', 'lhd_output_critical_css', 1 );

// ============================================================================
// DEFER NON-CRITICAL CSS
// ============================================================================

/**
 * Defer non-critical CSS loading
 */
function lhd_defer_non_critical_css( $tag, $handle, $src ) {
	// Skip if already deferred
	if ( strpos( $tag, 'defer' ) !== false || strpos( $tag, 'async' ) !== false ) {
		return $tag;
	}

	// Skip critical CSS handles (if any are marked)
	$critical_handles = apply_filters( 'lhd_critical_css_handles', array() );
	if ( in_array( $handle, $critical_handles, true ) ) {
		return $tag;
	}

	// Add preload and defer for non-critical CSS
	$tag = str_replace( "rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $tag );
	$tag = str_replace( 'rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $tag );

	// Add noscript fallback
	$noscript = '<noscript>' . str_replace( "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", "rel='stylesheet'", $tag ) . '</noscript>';
	$tag .= $noscript;

	return $tag;
}
add_filter( 'style_loader_tag', 'lhd_defer_non_critical_css', 10, 3 );

/**
 * Add loadCSS polyfill for older browsers
 */
function lhd_add_loadcss_polyfill() {
	if ( is_admin() ) {
		return;
	}

	?>
	<script>
	/*! loadCSS. [c]2017 Filament Group, Inc. MIT License */
	(function(w){"use strict";var loadCSS=function(href,before,media){var doc=w.document;var ss=doc.createElement("link");var ref;if(before){ref=before}else{var refs=(doc.body||doc.getElementsByTagName("head")[0]).childNodes;ref=refs[refs.length-1]}var sheets=doc.styleSheets;ss.rel="stylesheet";ss.href=href;ss.media="only x";function ready(cb){if(doc.body){return cb()}setTimeout(function(){ready(cb)})}ready(function(){ref.parentNode.insertBefore(ss,before?ref:ref.nextSibling)});var onloadcssdefined=function(cb){var resolvedHref=ss.href;var i=sheets.length;while(i--){if(sheets[i].href===resolvedHref){return cb()}}setTimeout(function(){onloadcssdefined(cb)})};ss.onloadcssdefined=onloadcssdefined;onloadcssdefined(function(){if(ss.media!=="all"){ss.media="all"}});return ss};if(typeof exports!=="undefined"){exports.loadCSS=loadCSS}else{w.loadCSS=loadCSS}}(typeof global!=="undefined"?global:this));
	</script>
	<?php
}
add_action( 'wp_head', 'lhd_add_loadcss_polyfill', 0 );

// ============================================================================
// AUTO-GENERATION ON SAVE
// ============================================================================

/**
 * Generate critical CSS when post is saved
 *
 * @param int $post_id Post ID.
 */
function lhd_generate_critical_css_on_save( $post_id ) {
	// Skip autosaves and revisions
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Only for published posts
	$post = get_post( $post_id );
	if ( $post->post_status !== 'publish' ) {
		return;
	}

	// Generate and save critical CSS
	$critical_css = lhd_generate_critical_css( $post_id );
	if ( $critical_css ) {
		lhd_save_critical_css( $post_id, $critical_css );
	}
}
add_action( 'save_post', 'lhd_generate_critical_css_on_save' );

// ============================================================================
// ADMIN INTERFACE
// ============================================================================

/**
 * Add meta box for critical CSS management
 */
function lhd_add_critical_css_meta_box() {
	$post_types = get_post_types( array( 'public' => true ), 'names' );
	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'lhd_critical_css',
			__( 'Critical CSS', 'lionhead-oxygen' ),
			'lhd_critical_css_meta_box_callback',
			$post_type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'lhd_add_critical_css_meta_box' );

/**
 * Critical CSS meta box callback
 *
 * @param WP_Post $post Current post object.
 */
function lhd_critical_css_meta_box_callback( $post ) {
	$critical_css = lhd_get_critical_css( $post->ID );
	$has_css = ! empty( $critical_css );
	?>
	<div class="lhd-critical-css-meta-box">
		<p>
			<strong><?php esc_html_e( 'Status:', 'lionhead-oxygen' ); ?></strong>
			<?php if ( $has_css ) : ?>
				<span style="color: green;"><?php esc_html_e( 'Generated', 'lionhead-oxygen' ); ?></span>
			<?php else : ?>
				<span style="color: orange;"><?php esc_html_e( 'Not Generated', 'lionhead-oxygen' ); ?></span>
			<?php endif; ?>
		</p>
		<p>
			<?php esc_html_e( 'Critical CSS is automatically generated when you save this post.', 'lionhead-oxygen' ); ?>
		</p>
		<p>
			<button type="button" class="button" id="lhd-regenerate-critical-css" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
				<?php esc_html_e( 'Regenerate Critical CSS', 'lionhead-oxygen' ); ?>
			</button>
		</p>
		<p>
			<button type="button" class="button button-link-delete" id="lhd-delete-critical-css" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
				<?php esc_html_e( 'Delete Critical CSS', 'lionhead-oxygen' ); ?>
			</button>
		</p>
		<?php wp_nonce_field( 'lhd_critical_css_action', 'lhd_critical_css_nonce' ); ?>
	</div>
	<?php
}

/**
 * Handle AJAX request to regenerate critical CSS
 */
function lhd_ajax_regenerate_critical_css() {
	check_ajax_referer( 'lhd_critical_css_action', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'lionhead-oxygen' ) ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

	if ( ! $post_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'lionhead-oxygen' ) ) );
	}

	$critical_css = lhd_generate_critical_css( $post_id );
	if ( $critical_css ) {
		lhd_save_critical_css( $post_id, $critical_css );
		wp_send_json_success( array( 'message' => __( 'Critical CSS regenerated successfully.', 'lionhead-oxygen' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Failed to generate critical CSS.', 'lionhead-oxygen' ) ) );
	}
}
add_action( 'wp_ajax_lhd_regenerate_critical_css', 'lhd_ajax_regenerate_critical_css' );

/**
 * Handle AJAX request to delete critical CSS
 */
function lhd_ajax_delete_critical_css() {
	check_ajax_referer( 'lhd_critical_css_action', 'nonce' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'lionhead-oxygen' ) ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

	if ( ! $post_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'lionhead-oxygen' ) ) );
	}

	lhd_delete_critical_css( $post_id );
	wp_send_json_success( array( 'message' => __( 'Critical CSS deleted successfully.', 'lionhead-oxygen' ) ) );
}
add_action( 'wp_ajax_lhd_delete_critical_css', 'lhd_ajax_delete_critical_css' );

/**
 * Enqueue admin scripts for critical CSS management
 */
function lhd_enqueue_critical_css_admin_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	wp_enqueue_script(
		'lhd-critical-css-admin',
		LHD_PLUGIN_URL . 'assets/js/critical-css-admin.js',
		array( 'jquery' ),
		LHD_PLUGIN_VERSION,
		true
	);

	wp_localize_script(
		'lhd-critical-css-admin',
		'lhdCriticalCss',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'lhd_critical_css_action' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'lhd_enqueue_critical_css_admin_scripts' );

