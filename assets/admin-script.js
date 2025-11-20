/**
 * Promotional Footer Bar - Admin Script
 */

(function($) {
	'use strict';

	var notificationIndex = 0;

	$(document).ready(function() {
		// Initialize
		init();
	});

	function init() {
		// Set initial index based on existing notifications
		updateNotificationIndex();

		// Initialize color pickers
		initColorPickers();

		// Add notification buttons
		$('#pfb-add-notification, .pfb-add-new-top').on('click', addNotification);

		// Remove notification buttons (delegated)
		$(document).on('click', '.pfb-remove-notification', removeNotification);

		// Enable/disable toggle
		$(document).on('change', '.pfb-enable-toggle', toggleNotificationState);

		// Template buttons
		$(document).on('click', '.pfb-use-template', useTemplate);

		// Disable add button if max reached
		checkMaxNotifications();
	}

	function useTemplate(e) {
		e.preventDefault();

		var currentCount = $('.pfb-notification-row').length;
		var maxNotifications = pfbAdmin.maxNotifications || 10;

		if (currentCount >= maxNotifications) {
			alert('Maximum ' + maxNotifications + ' notifications allowed. Please remove a notification first.');
			return;
		}

		// Get template data
		var templateData = $(this).data('template');

		// Get template HTML
		var template = $('#pfb-notification-template').html();

		// Replace placeholder index
		template = template.replace(/__INDEX__/g, notificationIndex);

		// Append to container
		var $newRow = $(template);
		$('#pfb-notifications-container').append($newRow);

		// Populate fields with template data
		$newRow.find('input[name*="[title]"]').val(templateData.title);
		$newRow.find('input[name*="[mobile_title]"]').val(templateData.mobile_title);
		$newRow.find('input[name*="[cta_text]"]').val(templateData.cta_text);
		$newRow.find('input[name*="[cta_url]"]').val(templateData.cta_url);
		$newRow.find('input[name*="[secondary_text]"]').val(templateData.secondary_text);
		$newRow.find('input[name*="[secondary_url]"]').val(templateData.secondary_url);
		$newRow.find('input[name*="[bg_color]"]').val(templateData.bg_color);
		$newRow.find('input[name*="[text_color]"]').val(templateData.text_color);
		$newRow.find('input[name*="[cta_bg_color]"]').val(templateData.cta_bg_color);
		$newRow.find('input[name*="[secondary_bg_color]"]').val(templateData.secondary_bg_color);

		// Initialize color pickers for new row
		initColorPickers();

		// Scroll to new notification
		$('html, body').animate({
			scrollTop: $newRow.offset().top - 100
		}, 500);

		// Increment index
		notificationIndex++;

		// Check if we've reached max
		checkMaxNotifications();

		// Show success message
		var $message = $('<div class="notice notice-info is-dismissible"><p><strong>Template loaded!</strong> Customize and save when ready.</p></div>');
		$('.pfb-admin-wrap h1').after($message);
		setTimeout(function() {
			$message.fadeOut(function() {
				$(this).remove();
			});
		}, 3000);
	}

	function initColorPickers() {
		$('.pfb-color-picker').each(function() {
			var $input = $(this);
			if ($input.hasClass('wp-color-picker-initialized')) {
				return;
			}
			$input.wpColorPicker();
			$input.addClass('wp-color-picker-initialized');
		});
	}

	function updateNotificationIndex() {
		var highestIndex = 0;
		$('.pfb-notification-row').each(function() {
			var currentIndex = parseInt($(this).data('index'));
			if (currentIndex > highestIndex) {
				highestIndex = currentIndex;
			}
		});
		notificationIndex = highestIndex + 1;
	}

	function addNotification(e) {
		e.preventDefault();

		var currentCount = $('.pfb-notification-row').length;
		var maxNotifications = pfbAdmin.maxNotifications || 10;

		if (currentCount >= maxNotifications) {
			alert('Maximum ' + maxNotifications + ' notifications allowed.');
			return;
		}

		// Get template
		var template = $('#pfb-notification-template').html();

		// Replace placeholder index
		template = template.replace(/__INDEX__/g, notificationIndex);

		// Append to container
		var $newRow = $(template);
		$('#pfb-notifications-container').append($newRow);

		// Initialize color pickers for new row
		initColorPickers();

		// Scroll to new notification
		$('html, body').animate({
			scrollTop: $newRow.offset().top - 100
		}, 500);

		// Focus first input
		$newRow.find('input[type="text"]').first().focus();

		// Increment index
		notificationIndex++;

		// Check if we've reached max
		checkMaxNotifications();
	}

	function removeNotification(e) {
		e.preventDefault();

		var $row = $(this).closest('.pfb-notification-row');
		var currentCount = $('.pfb-notification-row').length;

		// Prevent removing if it's the last one
		if (currentCount <= 1) {
			if (confirm('You need at least one notification. This will clear all fields. Continue?')) {
				// Clear all inputs in the last row
				$row.find('input[type="text"], input[type="url"]').val('');
				$row.find('input[type="checkbox"]').prop('checked', true);
				return;
			} else {
				return;
			}
		}

		// Confirm removal
		if (!confirm('Are you sure you want to remove this notification?')) {
			return;
		}

		// Remove with animation
		$row.addClass('pfb-removing');
		setTimeout(function() {
			$row.slideUp(300, function() {
				$(this).remove();
				checkMaxNotifications();
			});
		}, 100);
	}

	function toggleNotificationState() {
		var $checkbox = $(this);
		var $row = $checkbox.closest('.pfb-notification-row');
		var isEnabled = $checkbox.is(':checked');

		$row.attr('data-enabled', isEnabled ? 'true' : 'false');
	}

	function checkMaxNotifications() {
		var currentCount = $('.pfb-notification-row').length;
		var maxNotifications = pfbAdmin.maxNotifications || 10;

		if (currentCount >= maxNotifications) {
			$('#pfb-add-notification').prop('disabled', true).addClass('disabled');
		} else {
			$('#pfb-add-notification').prop('disabled', false).removeClass('disabled');
		}
	}


	// Form validation before submit
	$('#pfb-notifications-form').on('submit', function(e) {
		var hasValidNotification = false;

		$('.pfb-notification-row').each(function() {
			var $row = $(this);
			var isEnabled = $row.find('.pfb-enable-toggle').is(':checked');
			var hasTitle = $row.find('input[name*="[title]"]').val().trim() !== '';

			if (isEnabled && hasTitle) {
				hasValidNotification = true;
				return false; // break loop
			}
		});

		if (!hasValidNotification) {
			alert('Please add at least one enabled notification with a title.');
			e.preventDefault();
			return false;
		}

		return true;
	});

})(jQuery);
