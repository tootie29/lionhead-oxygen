<?php
/**
 * Performance Optimization
 * Detects and mitigates forced reflows and other performance issues
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ============================================================================
// FORCED REFLOW DETECTION & MITIGATION
// ============================================================================

/**
 * Add JavaScript to detect and mitigate forced reflows
 * DISABLED - Adding overhead and causing performance regression
 */
function lhd_add_reflow_detection() {
	// Disabled - was causing performance issues
	return;
}
// Disabled - causing performance regression
// add_action( 'wp_head', 'lhd_add_reflow_detection', 1 );

// ============================================================================
// DEFER NON-CRITICAL JAVASCRIPT EXECUTION
// ============================================================================

/**
 * Defer JavaScript execution until after page load
 * DISABLED - Can interfere with critical JavaScript execution
 */
function lhd_defer_javascript_execution() {
	// Disabled - can cause issues with scripts that need to run early
	return;
}
// Disabled - causing performance regression
// add_action( 'wp_head', 'lhd_defer_javascript_execution', 2 );

// ============================================================================
// OPTIMIZE IMAGE LOADING
// ============================================================================

/**
 * Add lazy loading attributes to images
 * DISABLED - Let WordPress/theme handle lazy loading
 */
function lhd_add_lazy_loading_images( $attr, $attachment, $size ) {
	// Disabled - let WordPress core or theme handle lazy loading
	// Adding our own can conflict with existing implementations
	return $attr;
}
// Disabled - can conflict with WordPress core lazy loading
// add_filter( 'wp_get_attachment_image_attributes', 'lhd_add_lazy_loading_images', 10, 3 );

/**
 * Check if an image is likely to be the LCP element
 *
 * @param WP_Post $attachment Attachment post object.
 * @param array   $attr        Image attributes.
 * @return bool True if likely LCP image.
 */
function lhd_is_lcp_image( $attachment, $attr = array() ) {
	// Featured image on single posts/pages is likely LCP
	if ( is_singular() && has_post_thumbnail() ) {
		$featured_id = get_post_thumbnail_id();
		if ( $attachment->ID == $featured_id ) {
			return true;
		}
	}

	// First image in post content is likely LCP
	if ( is_singular() ) {
		global $post;
		if ( $post && isset( $post->post_content ) ) {
			// Check if this is the first image in content
			preg_match_all( '/<img[^>]+>/i', $post->post_content, $matches );
			if ( ! empty( $matches[0] ) ) {
				// Extract attachment ID from first image
				$first_img = $matches[0][0];
				if ( preg_match( '/wp-image-(\d+)/i', $first_img, $img_match ) ) {
					if ( isset( $img_match[1] ) && $attachment->ID == $img_match[1] ) {
						return true;
					}
				}
			}
		}
	}

	// Hero images (check by class or size)
	if ( isset( $attr['class'] ) ) {
		$lcp_classes = array( 'hero', 'banner', 'featured-image', 'lcp', 'above-fold' );
		foreach ( $lcp_classes as $class ) {
			if ( strpos( $attr['class'], $class ) !== false ) {
				return true;
			}
		}
	}

	// Large images (likely LCP candidates)
	if ( isset( $attr['width'] ) && $attr['width'] > 800 ) {
		return true;
	}

	// Check image size - full size or large images are likely LCP
	$large_sizes = array( 'full', 'large', 'hero', 'banner' );
	if ( isset( $attr['data-size'] ) && in_array( $attr['data-size'], $large_sizes, true ) ) {
		return true;
	}

	return false;
}

/**
 * Add lazy loading to post content images
 * DISABLED - WordPress core handles this, our implementation can conflict
 */
function lhd_add_lazy_loading_content_images( $content ) {
	// Disabled - WordPress core already handles lazy loading for content images
	return $content;
}
// Disabled - WordPress core handles this
// add_filter( 'the_content', 'lhd_add_lazy_loading_content_images', 99 );

// ============================================================================
// OPTIMIZE FONT LOADING
// ============================================================================

/**
 * Preload critical fonts
 * DISABLED - Can add unnecessary preloads
 */
function lhd_preload_critical_fonts() {
	// Disabled - let fonts load normally
	return;
}
// Disabled - can add overhead
// add_action( 'wp_head', 'lhd_preload_critical_fonts', 3 );

/**
 * Preload LCP image for faster loading
 * Desktop: Uses full size (unchanged - desktop score stays at 81)
 * Mobile: Uses medium_large size for faster loading (improves mobile score)
 * 
 * Note: lhd_is_mobile_device() is defined in scripts-styles.php
 */
function lhd_preload_lcp_image() {
	if ( is_admin() || ! is_singular() ) {
		return;
	}

	// Quick check for featured image (most common LCP)
	if ( has_post_thumbnail() ) {
		$thumbnail_id = get_post_thumbnail_id();
		
		// Desktop: full size (unchanged - no impact on desktop performance)
		// Mobile: medium_large size (faster loading on mobile, improves LCP)
		if ( function_exists( 'lhd_is_mobile_device' ) && lhd_is_mobile_device() ) {
			$size = 'medium_large';
		} else {
			$size = 'full'; // Desktop unchanged
		}
		
		$image_url = wp_get_attachment_image_url( $thumbnail_id, $size );
		if ( $image_url ) {
			echo '<link rel="preload" as="image" href="' . esc_url( $image_url ) . '" fetchpriority="high" />' . "\n";
			return;
		}
	}
}
add_action( 'wp_head', 'lhd_preload_lcp_image', 1 );

/**
 * Get LCP image URL
 * Identifies the most likely LCP image on the page
 *
 * @return string|false Image URL or false if not found.
 */
function lhd_get_lcp_image_url() {
	// Featured image on single posts/pages
	if ( is_singular() && has_post_thumbnail() ) {
		$thumbnail_id = get_post_thumbnail_id();
		$image_url = wp_get_attachment_image_url( $thumbnail_id, 'full' );
		if ( $image_url ) {
			return $image_url;
		}
	}

	// First image in post content
	if ( is_singular() ) {
		global $post;
		if ( $post && isset( $post->post_content ) ) {
			// Extract first image from content
			if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches ) ) {
				$image_url = $matches[1];
				// Convert relative URL to absolute
				if ( strpos( $image_url, 'http' ) !== 0 ) {
					$image_url = site_url( $image_url );
				}
				return $image_url;
			}
		}
	}

	// Hero image from custom fields (common in Oxygen)
	$hero_image = get_post_meta( get_the_ID(), 'hero_image', true );
	if ( $hero_image ) {
		if ( is_numeric( $hero_image ) ) {
			$image_url = wp_get_attachment_image_url( $hero_image, 'full' );
			if ( $image_url ) {
				return $image_url;
			}
		} elseif ( filter_var( $hero_image, FILTER_VALIDATE_URL ) ) {
			return $hero_image;
		}
	}

	// Check for Oxygen background image
	$oxygen_bg = get_post_meta( get_the_ID(), 'ct_builder_shortcodes', true );
	if ( $oxygen_bg && preg_match( '/background-image:\s*url\(["\']?([^"\']+)["\']?\)/i', $oxygen_bg, $bg_match ) ) {
		$image_url = $bg_match[1];
		if ( strpos( $image_url, 'http' ) !== 0 ) {
			$image_url = site_url( $image_url );
		}
		return $image_url;
	}

	// Allow filtering for custom LCP image detection
	$lcp_image = apply_filters( 'lhd_lcp_image_url', false );
	if ( $lcp_image ) {
		return $lcp_image;
	}

	return false;
}

// ============================================================================
// REDUCE LAYOUT SHIFTS
// ============================================================================

/**
 * Add aspect ratio to images to prevent layout shifts
 * DISABLED - Adding inline styles can cause rendering issues
 */
function lhd_add_image_aspect_ratio( $attr, $attachment, $size ) {
	// Disabled - adding inline styles can interfere with Oxygen styling
	return $attr;
}
// Disabled - can interfere with Oxygen
// add_filter( 'wp_get_attachment_image_attributes', 'lhd_add_image_aspect_ratio', 10, 3 );

// ============================================================================
// OPTIMIZE THIRD-PARTY SCRIPTS
// ============================================================================

/**
 * Delay third-party script execution
 * DISABLED - Can interfere with script execution timing
 */
function lhd_delay_third_party_scripts() {
	// Disabled - delaying scripts can break functionality
	return;
}
// Disabled - can cause timing issues
// add_action( 'wp_footer', 'lhd_delay_third_party_scripts', 1 );

// ============================================================================
// PERFORMANCE MONITORING
// ============================================================================

/**
 * Add performance monitoring (only in debug mode)
 * DISABLED - Performance observers add overhead
 */
function lhd_add_performance_monitoring() {
	// Disabled - performance observers add overhead and can slow down page
	return;
}
// Disabled - adds overhead
// add_action( 'wp_footer', 'lhd_add_performance_monitoring', 999 );

// ============================================================================
// OPTIMIZE CSS ANIMATIONS
// ============================================================================

/**
 * Add CSS to optimize animations and prevent reflows
 * DISABLED - CSS containment can cause rendering issues
 */
function lhd_add_animation_optimization_css() {
	// Disabled - CSS containment can cause rendering issues with Oxygen
	return;
}
// Disabled - can interfere with Oxygen rendering
// add_action( 'wp_head', 'lhd_add_animation_optimization_css', 10 );

