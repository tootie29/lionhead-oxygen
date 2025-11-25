/**
 * Lazy Content Loading
 * Lazy loads sections when they come into viewport
 *
 * @package Lionhead_Oxygen
 */

(function() {
	'use strict';

	// Configuration
	const config = window.lhdLazyContentConfig || {
		rootMargin: '50px',
		threshold: 0.01,
		enableForMobile: true,
		enableForDesktop: true
	};

	// Check if lazy loading should be enabled
	function shouldEnableLazyLoading() {
		// Check device type
		const isMobile = window.innerWidth <= 767;
		const isTablet = window.innerWidth >= 768 && window.innerWidth <= 1023;
		
		if (isMobile && !config.enableForMobile) {
			return false;
		}
		
		if (!isMobile && !isTablet && !config.enableForDesktop) {
			return false;
		}
		
		return true;
	}

	// Initialize lazy loading
	function initLazyContent() {
		if (!shouldEnableLazyLoading()) {
			return;
		}

		// Check if Intersection Observer is supported
		if (!('IntersectionObserver' in window)) {
			// Fallback: Load all sections immediately
			loadAllSections();
			return;
		}

		// Find all sections with lazy loading attribute
		let lazySections = document.querySelectorAll('[data-lazy-content="true"]');
		
		// If no lazy-section found, use .section as fallback
		if (lazySections.length === 0) {
			const sectionElements = document.querySelectorAll('.section');
			if (sectionElements.length === 0) {
				return;
			}
			// Add data-lazy-content attribute to .section elements
			sectionElements.forEach(section => {
				section.setAttribute('data-lazy-content', 'true');
			});
			lazySections = sectionElements;
		}

		// Create Intersection Observer
		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					// Section is in viewport, load it
					loadSection(entry.target);
					// Stop observing this section
					observer.unobserve(entry.target);
				}
			});
		}, {
			rootMargin: config.rootMargin,
			threshold: config.threshold
		});

		// Observe all lazy sections
		lazySections.forEach(section => {
			// Initially hide the section content
			hideSectionContent(section);
			// Start observing
			observer.observe(section);
		});
	}

	/**
	 * Hide section content initially
	 * @param {HTMLElement} section - The section element
	 */
	function hideSectionContent(section) {
		// Add loading class
		section.classList.add('lazy-section-loading');
		
		// Set minimum height to prevent layout shift
		const rect = section.getBoundingClientRect();
		if (rect.height === 0) {
			section.style.minHeight = '200px'; // Default min height
		} else {
			section.style.minHeight = rect.height + 'px';
		}
		
		// Hide content but keep structure
		section.style.opacity = '0';
		section.style.visibility = 'hidden';
	}

	/**
	 * Load section content
	 * @param {HTMLElement} section - The section element
	 */
	function loadSection(section) {
		// Remove loading class
		section.classList.remove('lazy-section-loading');
		section.classList.add('lazy-section-loaded');
		
		// Show content with fade-in animation
		section.style.transition = 'opacity 0.3s ease-in-out';
		section.style.opacity = '1';
		section.style.visibility = 'visible';
		
		// Remove min-height after a short delay
		setTimeout(() => {
			section.style.minHeight = '';
		}, 300);
		
		// Trigger custom event
		const event = new CustomEvent('lazySectionLoaded', {
			detail: { section: section }
		});
		document.dispatchEvent(event);
	}

	/**
	 * Fallback: Load all sections immediately
	 * Used when Intersection Observer is not supported
	 */
	function loadAllSections() {
		const lazySections = document.querySelectorAll('[data-lazy-content="true"]');
		lazySections.forEach(section => {
			loadSection(section);
		});
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLazyContent);
	} else {
		initLazyContent();
	}

	// Re-initialize on dynamic content load (for Oxygen Builder)
	if (typeof jQuery !== 'undefined') {
		jQuery(document).on('oxygenElementsLoaded', function() {
			initLazyContent();
		});
	}

})();

