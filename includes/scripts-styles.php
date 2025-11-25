<?php
/**
 * Scripts & Styles
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Enqueue external scripts and styles
 */
function lhd_enqueue_scripts() {
	if ( ! wp_script_is( 'matchheight-js', 'enqueued' ) ) {
		wp_enqueue_script(
			'matchheight-js',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js',
			array( 'jquery' ),
			'0.7.2',
			true
		);
	}

	if ( ! wp_style_is( 'slick-css', 'enqueued' ) ) {
		wp_enqueue_style(
			'slick-css',
			'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css',
			array(),
			'1.9.0'
		);
	}

	if ( ! wp_style_is( 'slick-theme-css', 'enqueued' ) ) {
		wp_enqueue_style(
			'slick-theme-css',
			'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.min.css',
			array( 'slick-css' ),
			'1.9.0'
		);
	}

	if ( ! wp_script_is( 'slick-js', 'enqueued' ) ) {
		wp_enqueue_script(
			'slick-js',
			'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js',
			array( 'jquery' ),
			'1.9.0',
			true
		);
	}

	if ( ! wp_script_is( 'readmore-js', 'enqueued' ) ) {
		wp_enqueue_script(
			'readmore-js',
			'https://cdnjs.cloudflare.com/ajax/libs/Readmore.js/2.0.2/readmore.min.js',
			array(),
			'2.0.2',
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'lhd_enqueue_scripts' );

/**
 * Check if a script is a WordPress core script
 * Dynamically detects core scripts by checking source path and handle
 *
 * @param string $handle Script handle.
 * @param object $script Script object.
 * @return bool True if core script, false otherwise.
 */
function lhd_is_core_script( $handle, $script ) {
	// Critical scripts that must stay in header (minimal list)
	$critical_scripts = array(
		'jquery',
		'jquery-core',
		'jquery-migrate',
	);

	// Check if it's a critical script
	if ( in_array( $handle, $critical_scripts, true ) ) {
		return true;
	}

	// Check if script source is from WordPress core directories
	if ( isset( $script->src ) ) {
		$src = $script->src;
		
		// Check if source contains WordPress core paths
		$core_paths = array(
			'/wp-includes/',
			'/wp-admin/',
		);

		foreach ( $core_paths as $path ) {
			if ( strpos( $src, $path ) !== false ) {
				return true;
			}
		}

		// Check if it's from WordPress.org CDN or core domains
		$core_domains = array(
			'wordpress.org',
			'wp.com',
			's.w.org',
		);

		foreach ( $core_domains as $domain ) {
			if ( strpos( $src, $domain ) !== false ) {
				return true;
			}
		}
	}

	// Check if handle starts with WordPress core prefixes
	$core_prefixes = array(
		'wp-',
		'wp_',
		'admin-',
	);

	foreach ( $core_prefixes as $prefix ) {
		if ( strpos( $handle, $prefix ) === 0 ) {
			// Additional check: exclude known plugin handles that might start with wp-
			$plugin_handles = array(
				'wp-rocket',
				'wp-super-cache',
				'wp-optimize',
			);
			
			if ( ! in_array( $handle, $plugin_handles, true ) ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Move all plugin and theme scripts to footer for optimization
 * DISABLED - Can break scripts that need to run in head
 */
function lhd_move_scripts_to_footer() {
	// Disabled - moving scripts to footer can break functionality
	// Some scripts need to run in head for proper initialization
	return;
}
// Disabled - causing functionality issues
// add_action( 'wp_enqueue_scripts', 'lhd_move_scripts_to_footer', 999 );

/**
 * Add defer attribute to plugin/theme scripts for better performance
 * DISABLED - Can break scripts that need to execute immediately
 */
function lhd_add_defer_to_scripts( $tag, $handle, $src ) {
	// Disabled - deferring scripts can break functionality
	// Many plugins/themes need scripts to execute immediately
	return $tag;
}
// Disabled - causing functionality issues
// add_filter( 'script_loader_tag', 'lhd_add_defer_to_scripts', 10, 3 );

// ============================================================================
// CSS DEFERRING & PRELOADING
// ============================================================================

/**
 * Check if a stylesheet is critical (should not be deferred)
 *
 * @param string $handle Style handle.
 * @param object $style  Style object.
 * @return bool True if critical, false otherwise.
 */
function lhd_is_critical_stylesheet( $handle, $style ) {
	// Check if marked as critical
	if ( isset( $style->extra['critical'] ) && $style->extra['critical'] ) {
		return true;
	}

	// Critical stylesheets that must load immediately
	$critical_styles = array(
		// Add handles of critical stylesheets here if needed
	);

	if ( in_array( $handle, $critical_styles, true ) ) {
		return true;
	}

	// Check if it's from WordPress core
	if ( isset( $style->src ) ) {
		$src = $style->src;

		// WordPress core paths
		$core_paths = array(
			'/wp-includes/',
			'/wp-admin/',
		);

		foreach ( $core_paths as $path ) {
			if ( strpos( $src, $path ) !== false ) {
				return false; // Core styles can be deferred
			}
		}
	}

	return false;
}

/**
 * Defer non-critical CSS using preload technique
 * Only defers truly non-critical CSS to avoid FOUC and performance issues
 */
function lhd_defer_non_critical_css( $tag, $handle, $href ) {
	// Skip if already processed
	if ( strpos( $tag, 'data-deferred' ) !== false ) {
		return $tag;
	}

	// Skip critical stylesheets
	global $wp_styles;
	$style = isset( $wp_styles->registered[ $handle ] ) ? $wp_styles->registered[ $handle ] : null;
	
	if ( $style && lhd_is_critical_stylesheet( $handle, $style ) ) {
		return $tag;
	}

	// Only defer specific non-critical stylesheets
	// Be very conservative - only defer stylesheets we know are safe
	$safe_to_defer = array(
		'slick-css',
		'slick-theme-css',
		// Add other plugin stylesheet handles here that are truly non-critical
	);

	// Only defer if it's in our safe list
	if ( ! in_array( $handle, $safe_to_defer, true ) ) {
		return $tag;
	}

	// Skip if it's already preload or has onload
	if ( strpos( $tag, 'rel="preload"' ) !== false || strpos( $tag, "rel='preload'" ) !== false ) {
		return $tag;
	}

	// Convert to preload with onload
	$preload_tag = str_replace(
		array( "rel='stylesheet'", 'rel="stylesheet"' ),
		"rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
		$tag
	);

	// Add noscript fallback for users without JavaScript
	$noscript_tag = str_replace(
		"rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
		"rel='stylesheet'",
		$preload_tag
	);
	$noscript = '<noscript>' . $noscript_tag . '</noscript>';

	return $preload_tag . $noscript;
}
// Temporarily disable aggressive CSS deferring - enable only for specific safe stylesheets
// add_filter( 'style_loader_tag', 'lhd_defer_non_critical_css', 10, 3 );

/**
 * Add loadCSS polyfill for older browsers (only if CSS deferring is active)
 * This ensures preloaded stylesheets work in all browsers
 */
function lhd_add_loadcss_polyfill() {
	// Only add if we're actually deferring CSS
	// Disabled for now since we're not aggressively deferring
	return;
	
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
// Disabled - only enable if actively deferring CSS
// add_action( 'wp_head', 'lhd_add_loadcss_polyfill', 1 );

/**
 * Mark critical CSS files
 * Identifies Oxygen and framework CSS that must load immediately
 */
function lhd_mark_critical_css() {
	if ( is_admin() ) {
		return;
	}

	// Get all enqueued styles
	global $wp_styles;
	if ( ! isset( $wp_styles->registered ) || empty( $wp_styles->registered ) ) {
		return;
	}

	// Mark all Oxygen CSS and framework CSS as critical
	foreach ( $wp_styles->registered as $handle => $style ) {
		if ( ! isset( $style->src ) ) {
			continue;
		}

		$src = $style->src;

		// Mark as critical if it's Oxygen CSS, universal CSS, or framework CSS
		if ( strpos( $src, 'oxygen.css' ) !== false ||
			 strpos( $src, 'universal.css' ) !== false ||
			 strpos( $src, '/css/' ) !== false && strpos( $src, 'cache=' ) !== false ||
			 strpos( $src, 'component-framework' ) !== false ) {
			$wp_styles->registered[ $handle ]->extra['critical'] = true;
		}
	}
}
add_action( 'wp_enqueue_scripts', 'lhd_mark_critical_css', 999 );

/**
 * Move non-critical CSS to footer
 * This helps reduce render-blocking CSS
 */
function lhd_move_css_to_footer() {
	global $wp_styles;

	if ( ! isset( $wp_styles->registered ) || empty( $wp_styles->registered ) ) {
		return;
	}

	// Critical styles that must stay in head
	$critical_styles = array(
		// Add any critical style handles here
	);

	foreach ( $wp_styles->registered as $handle => $style ) {
		// Skip critical styles
		if ( in_array( $handle, $critical_styles, true ) ) {
			continue;
		}

		// Skip if already set
		if ( isset( $style->extra['group'] ) ) {
			continue;
		}

		// For non-critical styles, we'll defer them instead of moving to footer
		// CSS in footer can cause FOUC (Flash of Unstyled Content)
	}
}
// Note: We're using defer instead of moving to footer to avoid FOUC
// add_action( 'wp_enqueue_scripts', 'lhd_move_css_to_footer', 999 );

/**
 * Optimize jQuery loading
 * jQuery is often render-blocking, but we need to be careful not to break dependencies
 */
function lhd_optimize_jquery_loading( $tag, $handle, $src ) {
	// Only handle jQuery
	if ( 'jquery' !== $handle && 'jquery-core' !== $handle ) {
		return $tag;
	}

	// Check if jQuery has dependencies that need it early
	global $wp_scripts;
	if ( isset( $wp_scripts->registered[ $handle ] ) ) {
		$script = $wp_scripts->registered[ $handle ];
		
		// If jQuery has many dependents, we might need to keep it blocking
		// But we can still optimize by adding async/defer if safe
		// For now, we'll leave jQuery as-is since many plugins depend on it
		// But we can add preconnect for faster loading
	}

	return $tag;
}
// Uncomment if you want to optimize jQuery (test thoroughly first)
// add_filter( 'script_loader_tag', 'lhd_optimize_jquery_loading', 5, 3 );

/**
 * Check if current request is from mobile device
 * Mobile-only optimizations won't affect desktop
 */
function lhd_is_mobile_device() {
	// Check if mobile user agent
	if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
		return false;
	}

	$user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
	
	// Mobile device patterns
	$mobile_patterns = array(
		'mobile', 'android', 'iphone', 'ipod', 'ipad', 'blackberry',
		'windows phone', 'opera mini', 'iemobile', 'palm', 'smartphone'
	);

	foreach ( $mobile_patterns as $pattern ) {
		if ( strpos( $user_agent, $pattern ) !== false ) {
			return true;
		}
	}

	// Check viewport width (if available via cookie or header)
	if ( isset( $_SERVER['HTTP_X_VIEWPORT_WIDTH'] ) ) {
		$viewport_width = intval( $_SERVER['HTTP_X_VIEWPORT_WIDTH'] );
		if ( $viewport_width > 0 && $viewport_width < 768 ) {
			return true;
		}
	}

	return false;
}

/**
 * Add resource hints for faster CSS/JS loading
 */
function lhd_add_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		// Preconnect to common CDNs
		$urls[] = array(
			'href' => 'https://cdnjs.cloudflare.com',
			'crossorigin' => 'anonymous',
		);
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
			'crossorigin' => 'anonymous',
		);
	}

	if ( 'dns-prefetch' === $relation_type ) {
		// DNS prefetch for external resources
		$urls[] = 'https://cdnjs.cloudflare.com';
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = 'https://fonts.gstatic.com';
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'lhd_add_resource_hints', 10, 2 );

// ============================================================================
// MOBILE-SPECIFIC OPTIMIZATIONS
// ============================================================================

/**
 * Defer non-critical CSS on mobile only
 * DISABLED - Causing website breakage
 */
function lhd_defer_non_critical_css_mobile( $tag, $handle, $href ) {
	// Disabled - was causing website breakage
	return $tag;
}
// Disabled - causing issues
// add_filter( 'style_loader_tag', 'lhd_defer_non_critical_css_mobile', 10, 3 );

/**
 * Add defer attribute to non-critical scripts on mobile only
 * DISABLED - Causing website breakage
 */
function lhd_add_defer_to_scripts_mobile( $tag, $handle, $src ) {
	// Disabled - was causing website breakage
	return $tag;
}
// Disabled - causing issues
// add_filter( 'script_loader_tag', 'lhd_add_defer_to_scripts_mobile', 10, 3 );

/**
 * Add loadCSS polyfill for mobile
 * DISABLED - Causing website breakage
 */
function lhd_add_loadcss_polyfill_mobile() {
	// Disabled - was causing website breakage
	return;
}
// Disabled - causing issues
// add_action( 'wp_head', 'lhd_add_loadcss_polyfill_mobile', 1 );

