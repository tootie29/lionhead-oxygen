<?php
/**
 * Image Optimization
 * Optimizes image delivery for better performance and LCP
 *
 * @package Lionhead_Oxygen
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// ============================================================================
// RESPONSIVE IMAGES & SRCSET
// ============================================================================

/**
 * Add responsive image sizes
 * Conservative approach - only add essential sizes
 */
function lhd_add_responsive_image_sizes() {
	// Only add a few essential sizes to avoid too many image generations
	// Mobile devices
	add_image_size( 'lhd-mobile', 400, 0, false );
	
	// Tablet devices
	add_image_size( 'lhd-tablet', 768, 0, false );
	
	// Desktop
	add_image_size( 'lhd-desktop', 1200, 0, false );
}
// Disabled for now - let theme handle image sizes
// add_action( 'after_setup_theme', 'lhd_add_responsive_image_sizes' );

/**
 * Enhance image srcset with custom sizes
 * DISABLED - WordPress already handles srcset well, adding more can cause issues
 */
function lhd_enhance_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	// Let WordPress handle srcset generation - it's already optimized
	return $sources;
}
// Disabled - WordPress srcset is already well optimized
// add_filter( 'wp_calculate_image_srcset', 'lhd_enhance_image_srcset', 10, 5 );

/**
 * Improve sizes attribute for better responsive images
 * DISABLED - Let WordPress calculate sizes automatically
 */
function lhd_improve_image_sizes_attribute( $sizes, $size ) {
	// Let WordPress handle sizes calculation - it's context-aware
	return $sizes;
}
// Disabled - WordPress sizes calculation is already optimized
// add_filter( 'wp_calculate_image_sizes', 'lhd_improve_image_sizes_attribute', 10, 2 );

// ============================================================================
// WEBP & MODERN IMAGE FORMATS
// ============================================================================

/**
 * Serve WebP images when available
 * DISABLED - File existence checks on every request add overhead
 */
function lhd_serve_webp_images( $url, $post_id ) {
	// Disabled - checking file existence on every image request adds overhead
	return $url;
}
// Disabled - adds overhead to every image request
// add_filter( 'wp_get_attachment_image_url', 'lhd_serve_webp_images', 10, 2 );

/**
 * Add WebP to srcset
 */
function lhd_add_webp_to_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
	// Check if WebP is supported
	if ( ! isset( $_SERVER['HTTP_ACCEPT'] ) || strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) === false ) {
		return $sources;
	}

	$upload_dir = wp_upload_dir();
	$modified_sources = array();

	foreach ( $sources as $width => $source ) {
		// Try to find WebP version
		$webp_url = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $source['url'] );
		
		// Check if WebP file exists (basic check - could be improved)
		$modified_sources[ $width ] = $source;
		
		// If WebP exists, prefer it (you'd need to implement file existence check)
		// For now, we'll add type hint
		if ( strpos( $webp_url, '.webp' ) !== false ) {
			$modified_sources[ $width ]['url'] = $webp_url;
		}
	}

	return $modified_sources;
}
// Uncomment if you have WebP conversion set up
// add_filter( 'wp_calculate_image_srcset', 'lhd_add_webp_to_srcset', 20, 5 );

// ============================================================================
// IMAGE COMPRESSION & QUALITY
// ============================================================================

/**
 * Optimize JPEG quality for better compression
 * Conservative approach - only slightly reduce quality
 */
function lhd_optimize_jpeg_quality( $quality, $mime_type ) {
	// Conservative JPEG compression (85% - good balance)
	if ( $mime_type === 'image/jpeg' ) {
		return 85;
	}
	
	// Optimize WebP quality
	if ( $mime_type === 'image/webp' ) {
		return 85;
	}
	
	return $quality;
}
// Disabled for now - let WordPress use default quality
// add_filter( 'wp_editor_set_quality', 'lhd_optimize_jpeg_quality', 10, 2 );
// add_filter( 'jpeg_quality', 'lhd_optimize_jpeg_quality', 10, 2 );

/**
 * Strip image metadata to reduce file size
 */
function lhd_strip_image_metadata( $metadata, $attachment_id, $context ) {
	if ( $context === 'edit' ) {
		// Metadata is needed for editing, don't strip
		return $metadata;
	}

	// Remove unnecessary metadata to reduce file size
	// This is handled by WordPress by default, but we can optimize further
	return $metadata;
}
// Note: WordPress already handles metadata optimization

// ============================================================================
// IMAGE DELIVERY OPTIMIZATION
// ============================================================================

/**
 * Add fetchpriority to LCP images
 * DISABLED - Already handled in performance-optimization.php, duplicate can cause issues
 */
function lhd_add_fetchpriority_to_images( $attr, $attachment, $size ) {
	// Disabled - duplicate functionality, let performance-optimization.php handle it
	return $attr;
}
// Disabled - duplicate functionality
// add_filter( 'wp_get_attachment_image_attributes', 'lhd_add_fetchpriority_to_images', 20, 3 );

/**
 * Optimize image delivery with proper attributes
 * DISABLED - Modifying image attributes can interfere with Oxygen
 */
function lhd_optimize_image_delivery( $attr, $attachment, $size ) {
	// Disabled - modifying image attributes can interfere with Oxygen's image handling
	return $attr;
}
// Disabled - can interfere with Oxygen
// add_filter( 'wp_get_attachment_image_attributes', 'lhd_optimize_image_delivery', 15, 3 );

// ============================================================================
// IMAGE CDN & DELIVERY OPTIMIZATION
// ============================================================================

/**
 * Add image CDN support
 * DISABLED - Filtering every image URL adds overhead
 */
function lhd_image_cdn_url( $url, $post_id, $size ) {
	// Disabled - filtering every image URL adds overhead
	return $url;
}
// Disabled - adds overhead
// add_filter( 'wp_get_attachment_image_url', 'lhd_image_cdn_url', 10, 3 );

/**
 * Optimize image URLs for CDN delivery
 * Example: Add Cloudflare or other CDN optimization
 */
function lhd_optimize_image_urls( $url ) {
	// If using Cloudflare or similar CDN, you can add optimizations here
	// Example: Add auto-format or quality parameters
	
	// For Cloudflare Images or similar services
	if ( defined( 'LHD_CDN_DOMAIN' ) && LHD_CDN_DOMAIN ) {
		$url = str_replace( site_url(), LHD_CDN_DOMAIN, $url );
	}
	
	return $url;
}
// Uncomment and configure if using CDN
// add_filter( 'wp_get_attachment_image_url', 'lhd_optimize_image_urls', 5 );

// ============================================================================
// IMAGE PRELOADING FOR CRITICAL IMAGES
// ============================================================================

/**
 * Preload critical images with proper format hints
 * DISABLED - Duplicate preloading (already handled in performance-optimization.php)
 */
function lhd_preload_critical_images() {
	// Disabled - LCP preloading is already handled in performance-optimization.php
	return;
}
// Disabled - duplicate functionality
// add_action( 'wp_head', 'lhd_preload_critical_images', 2 );

// ============================================================================
// IMAGE LAZY LOADING OPTIMIZATION
// ============================================================================

/**
 * Optimize lazy loading for non-LCP images
 * DISABLED - Native lazy loading is sufficient, extra JS adds overhead
 */
function lhd_optimize_lazy_loading() {
	// Disabled - native browser lazy loading is enough
	return;
}
// Disabled - adds unnecessary JavaScript overhead
// add_action( 'wp_footer', 'lhd_optimize_lazy_loading', 1 );

// ============================================================================
// IMAGE SIZE OPTIMIZATION
// ============================================================================

/**
 * Limit maximum image dimensions and compress on upload
 * DISABLED - Can cause performance issues during upload
 */
function lhd_limit_image_dimensions( $file ) {
	// Disabled - causing performance degradation
	// Let WordPress handle image processing normally
	return $file;
	
	/*
	// Original code - disabled for now
	$image = getimagesize( $file['tmp_name'] );
	
	if ( $image ) {
		$max_width = 2400;  // Keep original max
		$max_height = 2400;
		
		// Only resize if extremely large
		if ( $image[0] > 4000 || $image[1] > 4000 ) {
			$editor = wp_get_image_editor( $file['tmp_name'] );
			if ( ! is_wp_error( $editor ) ) {
				$editor->resize( $max_width, $max_height, false );
				$saved = $editor->save( $file['tmp_name'] );
				if ( ! is_wp_error( $saved ) ) {
					$file['size'] = filesize( $file['tmp_name'] );
				}
			}
		}
	}
	
	return $file;
	*/
}
// Disabled - can slow down uploads and cause issues
// add_filter( 'wp_handle_upload_prefilter', 'lhd_limit_image_dimensions' );

/**
 * Generate additional image sizes on upload
 * DISABLED - Can cause performance issues and slow down uploads
 */
function lhd_generate_additional_image_sizes( $metadata, $attachment_id ) {
	// Disabled - let WordPress handle size generation normally
	// Generating too many sizes can slow down uploads and cause performance issues
	return $metadata;
}
// Disabled - causing performance degradation
// add_filter( 'wp_generate_attachment_metadata', 'lhd_generate_additional_image_sizes', 10, 2 );

/**
 * Get image size dimensions
 */
function lhd_get_image_size_dimensions( $size_name ) {
	$sizes = array(
		'lhd-mobile'        => array( 'width' => 400, 'height' => 0 ),
		'lhd-tablet-small' => array( 'width' => 600, 'height' => 0 ),
		'lhd-tablet'       => array( 'width' => 768, 'height' => 0 ),
		'lhd-desktop'      => array( 'width' => 1200, 'height' => 0 ),
		'lhd-desktop-large' => array( 'width' => 1600, 'height' => 0 ),
		'lhd-retina'       => array( 'width' => 1920, 'height' => 0 ),
	);
	
	return isset( $sizes[ $size_name ] ) ? $sizes[ $size_name ] : array( 'width' => 0, 'height' => 0 );
}

// ============================================================================
// IMAGE DELIVERY HEADERS
// ============================================================================

/**
 * Add cache headers for images
 * DISABLED - Can interfere with server/CDN cache headers
 */
function lhd_add_image_cache_headers() {
	// Disabled - let server/CDN handle cache headers
	return;
}
// Disabled - can conflict with existing cache headers
// add_action( 'send_headers', 'lhd_add_image_cache_headers' );

// ============================================================================
// BULK IMAGE COMPRESSION
// ============================================================================

/**
 * Compress existing images on demand
 * Can be called via admin or cron to optimize existing images
 */
function lhd_compress_existing_image( $attachment_id ) {
	$file_path = get_attached_file( $attachment_id );
	
	if ( ! $file_path || ! file_exists( $file_path ) ) {
		return false;
	}
	
	$editor = wp_get_image_editor( $file_path );
	
	if ( is_wp_error( $editor ) ) {
		return false;
	}
	
	// Get current file size
	$original_size = filesize( $file_path );
	
	// Set quality for compression
	$editor->set_quality( 80 );
	
	// Save with compression
	$saved = $editor->save( $file_path );
	
	if ( is_wp_error( $saved ) ) {
		return false;
	}
	
	// Get new file size
	$new_size = filesize( $file_path );
	$savings = $original_size - $new_size;
	
	// Regenerate all sizes with new quality
	$metadata = wp_generate_attachment_metadata( $attachment_id, $file_path );
	wp_update_attachment_metadata( $attachment_id, $metadata );
	
	return array(
		'original_size' => $original_size,
		'new_size'      => $new_size,
		'savings'       => $savings,
		'savings_percent' => round( ( $savings / $original_size ) * 100, 2 ),
	);
}

// ============================================================================
// ADMIN NOTICES & RECOMMENDATIONS
// ============================================================================

/**
 * Show image optimization recommendations in admin
 */
function lhd_image_optimization_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'upload' !== $screen->id ) {
		return;
	}

	// Check if image optimization plugin is active
	$optimization_plugins = array(
		'ewww-image-optimizer/ewww-image-optimizer.php',
		'shortpixel-image-optimiser/wp-shortpixel.php',
		'imagify/imagify.php',
		'smush/smush.php',
	);

	$has_optimizer = false;
	foreach ( $optimization_plugins as $plugin ) {
		if ( is_plugin_active( $plugin ) ) {
			$has_optimizer = true;
			break;
		}
	}

	if ( ! $has_optimizer ) {
		?>
		<div class="notice notice-info">
			<p>
				<strong><?php esc_html_e( 'Image Optimization Recommendation:', 'lionhead-oxygen' ); ?></strong>
				<?php esc_html_e( 'Consider installing an image optimization plugin (like EWWW Image Optimizer, ShortPixel, or Imagify) to automatically compress and optimize uploaded images.', 'lionhead-oxygen' ); ?>
			</p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'lhd_image_optimization_admin_notice' );

