/**
 * ShahiLegalFlowSuite - Admin Global JavaScript
 *
 * Core JavaScript utilities loaded on all plugin admin pages.
 * Provides common functionality and helpers.
 *
 * @package
 * @version    3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * Main plugin object
	 */
	window.ShahiLegalFlowSuite = window.ShahiLegalFlowSuite || {};

	/**
	 * Initialize global functionality
	 */
	ShahiLegalFlowSuite.init = function () {
		this.initAlerts();
		this.initTooltips();
		this.initConfirmDialogs();
		this.initFormValidation();
		this.initAjaxHandler();
	};

	/**
	 * Initialize alert dismissal
	 */
	ShahiLegalFlowSuite.initAlerts = function () {
		$(document).on(
			'click',
			'.shahi-alert .shahi-alert-close',
			function (e) {
				e.preventDefault();
				$(this)
					.closest('.shahi-alert')
					.fadeOut(300, function () {
						$(this).remove();
					});
			}
		);
	};

	/**
	 * Initialize tooltips
	 */
	ShahiLegalFlowSuite.initTooltips = function () {
		// Simple tooltip functionality
		$('[data-shahi-tooltip]').each(function () {
			var $elem = $(this);
			var tooltipText = $elem.data('shahi-tooltip');

			$elem.hover(
				function () {
					var $tooltip = $(
						'<div class="shahi-tooltip">' + tooltipText + '</div>'
					);
					$('body').append($tooltip);

					var elemOffset = $elem.offset();
					var elemWidth = $elem.outerWidth();
					let tooltipWidth = $tooltip.outerWidth();

					$tooltip.css({
						top: elemOffset.top - $tooltip.outerHeight() - 8,
						left:
							elemOffset.left + elemWidth / 2 - tooltipWidth / 2,
					});

					$tooltip.fadeIn(200);
				},
				function () {
					$('.shahi-tooltip').fadeOut(200, function () {
						$(this).remove();
					});
				}
			);
		});
	};

	/**
	 * Initialize confirmation dialogs
	 */
	ShahiLegalFlowSuite.initConfirmDialogs = function () {
		$(document).on('click', '[data-shahi-confirm]', function (e) {
			var message =
				$(this).data('shahi-confirm') || shahiTemplate.i18n.confirm;

			if (!confirm(message)) {
				e.preventDefault();
				return false;
			}
		});
	};

	/**
	 * Initialize form validation
	 */
	ShahiLegalFlowSuite.initFormValidation = function () {
		$('.shahi-form-validate').on('submit', function (e) {
			var $form = $(this);
			let isValid = true;

			// Remove existing error messages
			$form.find('.shahi-error-message').remove();
			$form.find('.shahi-input-error').removeClass('shahi-input-error');

			// Check required fields
			$form.find('[required]').each(function () {
				var $field = $(this);
				var value = $field.val().trim();

				if (!value) {
					isValid = false;
					$field.addClass('shahi-input-error');
					$field.after(
						'<span class="shahi-error-message">' +
							shahiTemplate.i18n.required +
							'</span>'
					);
				}
			});

			// Check email fields
			$form.find('[type="email"]').each(function () {
				var $field = $(this);
				var value = $field.val().trim();

				if (value && !ShahiLegalFlowSuite.isValidEmail(value)) {
					isValid = false;
					$field.addClass('shahi-input-error');
					$field.after(
						'<span class="shahi-error-message">Invalid email address</span>'
					);
				}
			});

			if (!isValid) {
				e.preventDefault();

				// Scroll to first error
				var $firstError = $form.find('.shahi-input-error').first();
				if ($firstError.length) {
					$('html, body').animate(
						{
							scrollTop: $firstError.offset().top - 100,
						},
						300
					);
				}
			}

			return isValid;
		});
	};

	/**
	 * Initialize AJAX handler
	 */
	ShahiLegalFlowSuite.initAjaxHandler = function () {
		// Global AJAX error handler
		$(document).ajaxError(function (event, jqxhr, settings, thrownError) {
			if (jqxhr.status === 403) {
				ShahiLegalFlowSuite.showNotice(
					'Permission denied. Please refresh the page.',
					'error'
				);
			} else if (jqxhr.status === 500) {
				ShahiLegalFlowSuite.showNotice(
					shahiTemplate.i18n.error,
					'error'
				);
			}
		});
	};

	/**
	 * Make AJAX request with nonce
	 *
	 * @param {string}   action          AJAX action name
	 * @param {object}   data            Additional data to send
	 * @param {function} successCallback Success callback
	 * @param {function} errorCallback   Error callback
	 */
	ShahiLegalFlowSuite.ajax = function (
		action,
		data,
		successCallback,
		errorCallback
	) {
		data = data || {};
		data.action = action;
		data.nonce = shahiTemplate.nonce;

		$.ajax({
			url: shahiTemplate.ajaxurl,
			type: 'POST',
			data,
			success: function (response) {
				if (response.success) {
					if (typeof successCallback === 'function') {
						successCallback(response.data);
					}
				} else if (typeof errorCallback === 'function') {
							errorCallback( response.data );
						} else {
							ShahiLegalFlowSuite.showNotice(
								response.data.message || shahiTemplate.i18n.error,
								'error'
							);
						}
			},
			error (jqxhr, textStatus, error) {
					if (typeof errorCallback === 'function') {
						errorCallback( {message: error} );
					} else {
						ShahiLegalFlowSuite.showNotice( shahiTemplate.i18n.error, 'error' );
					}
				}
			},
		});
	};

	/**
	 * Show notification message
	 *
	 * @param {string} message  Message to display
	 * @param {string} type     Type: success, error, warning, info
	 * @param {number} duration Duration in ms (0 = permanent)
	 */
	ShahiLegalFlowSuite.showNotice = function (message, type, duration) {
		type = type || 'info';
		duration = duration || 5000;

		let $notice = $(
			'<div class="shahi-alert shahi-alert-' +
				type +
				'">' +
				'<span>' +
				message +
				'</span>' +
				'<button class="shahi-alert-close">&times;</button>' +
				'</div>'
		);

		$('.shahi-legalflowsuite-admin').prepend($notice);
		$notice.fadeIn(300);

		if (duration > 0) {
			setTimeout(function () {
				$notice.fadeOut(300, function () {
					$(this).remove();
				});
			}, duration);
		}
	};

	/**
	 * Show loading overlay
	 */
	ShahiLegalFlowSuite.showLoading = function () {
		if ($('.shahi-loading-overlay').length === 0) {
			let $overlay = $(
				'<div class="shahi-loading-overlay">' +
					'<div class="shahi-spinner"></div>' +
					'</div>'
			);
			$('body').append($overlay);
			$overlay.fadeIn(200);
		}
	};

	/**
	 * Hide loading overlay
	 */
	ShahiLegalFlowSuite.hideLoading = function () {
		$('.shahi-loading-overlay').fadeOut(200, function () {
			$(this).remove();
		});
	};

	/**
	 * Validate email address
	 *
	 * @param {string} email Email to validate
	 * @return {boolean} True if valid
	 */
	ShahiLegalFlowSuite.isValidEmail = function (email) {
		let re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return re.test(email);
	};

	/**
	 * Validate URL
	 *
	 * @param {string} url URL to validate
	 * @return {boolean} True if valid
	 */
	ShahiLegalFlowSuite.isValidUrl = function (url) {
		try {
			new URL(url);
			return true;
		} catch (e) {
			return false;
		}
	};

	/**
	 * Debounce function
	 *
	 * @param {function} func Function to debounce
	 * @param {number}   wait Wait time in ms
	 * @return {function} Debounced function
	 */
	ShahiLegalFlowSuite.debounce = function (func, wait) {
		let timeout;
		return function () {
			let context = this;
			let args = arguments;
			clearTimeout(timeout);
			timeout = setTimeout(function () {
				func.apply(context, args);
			}, wait);
		};
	};

	/**
	 * Format number with commas
	 *
	 * @param {number} num Number to format
	 * @return {string} Formatted number
	 */
	ShahiLegalFlowSuite.formatNumber = function (num) {
		return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	};

	/**
	 * Get URL parameter
	 *
	 * @param {string} name Parameter name
	 * @return {string|null} Parameter value or null
	 */
	ShahiLegalFlowSuite.getUrlParam = function (name) {
		let url = new URL(window.location.href);
		return url.searchParams.get(name);
	};

	/**
	 * Copy text to clipboard
	 *
	 * @param {string} text Text to copy
	 * @return {boolean} True if successful
	 */
	ShahiLegalFlowSuite.copyToClipboard = function (text) {
		let $temp = $('<textarea>');
		$('body').append($temp);
		$temp.val(text).select();
		let success = document.execCommand('copy');
		$temp.remove();

		if (success) {
			ShahiLegalFlowSuite.showNotice(
				'Copied to clipboard!',
				'success',
				2000
			);
		}

		return success;
	};

	/**
	 * Escape HTML
	 *
	 * @param {string} html HTML to escape
	 * @return {string} Escaped HTML
	 */
	ShahiLegalFlowSuite.escapeHtml = function (html) {
		let div = document.createElement('div');
		div.textContent = html;
		return div.innerHTML;
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function () {
		ShahiLegalFlowSuite.init();
	});
})(jQuery);
