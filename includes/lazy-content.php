<?php
/**
 * Lazy Content Loading
 * Lazy loads sections when they come into viewport
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ============================================================================
// LAZY LOAD SECTIONS
// ============================================================================

/**
 * Add lazy loading attributes to sections with 'lazy-section' class
 * Falls back to '.section' class if no 'lazy-section' found
 * This works by filtering the final HTML output and adding data-lazy-content
 * to any section/div that has the 'lazy-section' or 'section' class
 */
function lhd_add_lazy_section_attributes( $content ) {
	// Only process on frontend
	if ( is_admin() ) {
		return $content;
	}
	
	// First, check if any lazy-section exists
	$has_lazy_section = preg_match( '/class="[^"]*\blazy-section\b[^"]*"/i', $content );
	
	if ( $has_lazy_section ) {
		// Find all elements with 'lazy-section' class and add data-lazy-content attribute
		$pattern = '/<([a-z]+)([^>]*class="[^"]*\blazy-section\b[^"]*"[^>]*)>/i';
	} else {
		// Fallback: Find all elements with 'section' class and add data-lazy-content attribute
		$pattern = '/<([a-z]+)([^>]*class="[^"]*\bsection\b[^"]*"[^>]*)>/i';
	}
	
	$content = preg_replace_callback( $pattern, function( $matches ) {
		$tag = $matches[1];
		$attributes = $matches[2];
		
		// Check if data-lazy-content already exists
		if ( strpos( $attributes, 'data-lazy-content' ) !== false ) {
			return $matches[0]; // Return unchanged
		}
		
		// Add data-lazy-content attribute
		return '<' . $tag . $attributes . ' data-lazy-content="true">';
	}, $content );
	
	return $content;
}
add_filter( 'the_content', 'lhd_add_lazy_section_attributes', 999 );
add_filter( 'oxygen_vsb_render_shortcode', 'lhd_add_lazy_section_attributes', 999 );

/**
 * Enqueue lazy loading script and styles
 */
function lhd_enqueue_lazy_content_assets() {
	// Only load on frontend
	if ( is_admin() ) {
		return;
	}
	
	// Enqueue CSS
	wp_enqueue_style(
		'lhd-lazy-content',
		LHD_PLUGIN_URL . 'assets/css/lazy-content.css',
		array(),
		LHD_PLUGIN_VERSION
	);
	
	// Enqueue JavaScript
	wp_enqueue_script(
		'lhd-lazy-content',
		LHD_PLUGIN_URL . 'assets/js/lazy-content.js',
		array(),
		LHD_PLUGIN_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'lhd_enqueue_lazy_content_assets' );

/**
 * Add inline script for lazy loading initialization
 */
function lhd_add_lazy_content_inline_script() {
	// Only load on frontend
	if ( is_admin() ) {
		return;
	}
	
	?>
	<script>
	// Lazy content configuration
	window.lhdLazyContentConfig = {
		rootMargin: '50px', // Start loading 50px before section enters viewport
		threshold: 0.01,    // Trigger when 1% of section is visible
		enableForMobile: true,
		enableForDesktop: true
	};
	</script>
	<?php
}
add_action( 'wp_head', 'lhd_add_lazy_content_inline_script', 99 );

