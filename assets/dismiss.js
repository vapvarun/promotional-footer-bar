/**
 * Promotional Footer Bar - Dismiss Functionality
 */

(function() {
	'use strict';

	// Wait for DOM to be ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		var footer = document.getElementById('pfb-sticky-footer');
		if (!footer) {
			return;
		}

		var closeBtn = footer.querySelector('.pfb-close');
		if (!closeBtn) {
			return;
		}

		closeBtn.addEventListener('click', function(e) {
			e.preventDefault();
			dismissNotification(footer);
		});
	}

	function dismissNotification(footer) {
		var cookieName = footer.getAttribute('data-cookie');
		if (!cookieName) {
			return;
		}

		// Set cookie for 24 hours
		var expires = new Date();
		expires.setTime(expires.getTime() + (24 * 60 * 60 * 1000));
		document.cookie = cookieName + '=1; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';

		// Fade out and remove
		footer.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
		footer.style.opacity = '0';
		footer.style.transform = 'translateY(100%)';

		setTimeout(function() {
			if (footer.parentNode) {
				footer.parentNode.removeChild(footer);
			}
		}, 300);
	}
})();
