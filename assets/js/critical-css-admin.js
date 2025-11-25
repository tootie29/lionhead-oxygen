/**
 * Critical CSS Admin Scripts
 */
(function($) {
	'use strict';

	$(document).ready(function() {
		// Regenerate Critical CSS
		$('#lhd-regenerate-critical-css').on('click', function(e) {
			e.preventDefault();
			
			var $button = $(this);
			var postId = $button.data('post-id');
			
			$button.prop('disabled', true).text('Generating...');
			
			$.ajax({
				url: lhdCriticalCss.ajaxUrl,
				type: 'POST',
				data: {
					action: 'lhd_regenerate_critical_css',
					action_type: 'regenerate',
					post_id: postId,
					nonce: lhdCriticalCss.nonce
				},
				success: function(response) {
					if (response.success) {
						alert(response.data.message);
						location.reload();
					} else {
						alert(response.data.message);
						$button.prop('disabled', false).text('Regenerate Critical CSS');
					}
				},
				error: function() {
					alert('An error occurred. Please try again.');
					$button.prop('disabled', false).text('Regenerate Critical CSS');
				}
			});
		});

		// Delete Critical CSS
		$('#lhd-delete-critical-css').on('click', function(e) {
			e.preventDefault();
			
			if (!confirm('Are you sure you want to delete the critical CSS for this post?')) {
				return;
			}
			
			var $button = $(this);
			var postId = $button.data('post-id');
			
			$button.prop('disabled', true).text('Deleting...');
			
			$.ajax({
				url: lhdCriticalCss.ajaxUrl,
				type: 'POST',
				data: {
					action: 'lhd_delete_critical_css',
					action_type: 'delete',
					post_id: postId,
					nonce: lhdCriticalCss.nonce
				},
				success: function(response) {
					if (response.success) {
						alert(response.data.message);
						location.reload();
					} else {
						alert(response.data.message);
						$button.prop('disabled', false).text('Delete Critical CSS');
					}
				},
				error: function() {
					alert('An error occurred. Please try again.');
					$button.prop('disabled', false).text('Delete Critical CSS');
				}
			});
		});
	});
})(jQuery);

