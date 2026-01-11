/**
 * ShahiLegalFlowSuite - AJAX Helper
 *
 * Centralized AJAX utilities for consistent handling of AJAX requests
 * across all admin pages.
 *
 * @package    ShahiLegalFlowSuite
 * @version     3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * Global AJAX Helper
	 */
	window.ShahiAjax = {

		/**
		 * Base AJAX settings
		 */
		defaults: {
			type: 'POST',
			url: ajaxurl || '/wp-admin/admin-ajax.php',
			dataType: 'json',
			timeout: 30000
		},

		/**
		 * Toggle module enabled/disabled
		 *
		 * @param {string} moduleId - Module identifier
		 * @param {boolean} enabled - Enable or disable
		 * @returns {Promise}
		 */
		toggleModule: function (moduleId, enabled) {
			return this.request(
				'shahi_toggle_module',
				{
					module_id: moduleId,
					enabled: enabled,
					_wpnonce: window.shahiData ? .nonces ? .toggle_module || ''
				}
			);
		},

		/**
		 * Save module settings
		 *
		 * @param {string} moduleId - Module identifier
		 * @param {Object} settings - Settings object
		 * @returns {Promise}
		 */
		saveModuleSettings: function (moduleId, settings) {
			return this.request(
				'shahi_save_module_settings',
				{
					module_id: moduleId,
					settings: settings,
					_wpnonce: window.shahiData ? .nonces ? .save_module_settings || ''
				}
			);
		},

		/**
		 * Get analytics data
		 *
		 * @param {string} period - Time period (7days, 30days, 90days)
		 * @returns {Promise}
		 */
		getAnalytics: function (period) {
			return this.request(
				'shahi_get_analytics',
				{
					period: period || '30days',
					_wpnonce: window.shahiData ? .nonces ? .analytics || ''
				}
			);
		},

		/**
		 * Export analytics data
		 *
		 * @param {string} startDate - Start date (YYYY-MM-DD)
		 * @param {string} endDate - End date (YYYY-MM-DD)
		 * @returns {Promise}
		 */
		exportAnalytics: function (startDate, endDate) {
			return this.request(
				'shahi_export_analytics',
				{
					start_date: startDate,
					end_date: endDate,
					_wpnonce: window.shahiData ? .nonces ? .export_analytics || ''
				}
			);
		},

		/**
		 * Refresh dashboard stats
		 *
		 * @returns {Promise}
		 */
		refreshStats: function () {
			return this.request(
				'shahi_refresh_stats',
				{
					_wpnonce: window.shahiData ? .nonces ? .dashboard || ''
				}
			);
		},

		/**
		 * Complete checklist item
		 *
		 * @param {string} itemId - Checklist item identifier
		 * @returns {Promise}
		 */
		completeChecklistItem: function (itemId) {
			return this.request(
				'shahi_complete_checklist_item',
				{
					item_id: itemId,
					_wpnonce: window.shahiData ? .nonces ? .complete_checklist || ''
				}
			);
		},

		/**
		 * Save onboarding step
		 *
		 * @param {number} step - Step number
		 * @param {Object} data - Step data
		 * @returns {Promise}
		 */
		saveOnboardingStep: function (step, data) {
			return this.request(
				'shahi_save_onboarding_step',
				{
					step: step,
					data: data,
					_wpnonce: window.shahiData ? .nonces ? .onboarding || ''
				}
			);
		},

		/**
		 * Complete onboarding
		 *
		 * @param {Object} data - Final data
		 * @returns {Promise}
		 */
		completeOnboarding: function (data) {
			return this.request(
				'shahi_complete_onboarding',
				{
					data: data,
					_wpnonce: window.shahiData ? .nonces ? .onboarding || ''
				}
			);
		},

		/**
		 * Save settings
		 *
		 * @param {Object} settings - Settings by category
		 * @returns {Promise}
		 */
		saveSettings: function (settings) {
			return this.request(
				'shahi_save_settings',
				{
					settings: settings,
					_wpnonce: window.shahiData ? .nonces ? .save_settings || ''
				}
			);
		},

		/**
		 * Reset settings
		 *
		 * @param {string} category - Category to reset (optional)
		 * @returns {Promise}
		 */
		resetSettings: function (category) {
			var data = {
				_wpnonce: window.shahiData ? .nonces ? .reset_settings || ''
			};

			if (category) {
				data.category = category;
			}

			return this.request( 'shahi_reset_settings', data );
		},

		/**
		 * Make AJAX request
		 *
		 * @param {string} action - WordPress AJAX action
		 * @param {Object} data - Request data
		 * @returns {Promise}
		 */
		request: function (action, data) {
			var self = this;

			return new Promise(
				function (resolve, reject) {
					var settings = $.extend(
						{},
						self.defaults,
						{
							data: $.extend( { action: action }, data ),
							success: function (response) {
								if (response.success) {
									resolve( response.data );
								} else {
									reject( new Error( response.data ? .message || 'Request failed' ) );
								}
							},
							error: function (xhr, status, error) {
								var message = 'AJAX request failed';

								if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
									message = xhr.responseJSON.data.message;
								} else if (error) {
									message = error;
								}

								reject( new Error( message ) );
							}
						}
					);

					$.ajax( settings );
				}
			);
		},

		/**
		 * Show success notification
		 *
		 * @param {string} message - Success message
		 */
		showSuccess: function (message) {
			this.showNotice( message, 'success' );
		},

		/**
		 * Show error notification
		 *
		 * @param {string} message - Error message
		 */
		showError: function (message) {
			this.showNotice( message, 'error' );
		},

		/**
		 * Show notice
		 *
		 * @param {string} message - Notice message
		 * @param {string} type - Notice type (success, error, warning, info)
		 */
		showNotice: function (message, type) {
			type = type || 'info';

			// Create notice element
			var $notice = $( '<div>' )
				.addClass( 'notice notice-' + type + ' is-dismissible shahi-ajax-notice' )
				.html( '<p>' + message + '</p>' )
				.hide();

			// Add dismiss button
			$notice.append(
				$( '<button>' )
					.attr( 'type', 'button' )
					.addClass( 'notice-dismiss' )
					.html( '<span class="screen-reader-text">Dismiss this notice.</span>' )
					.on(
						'click',
						function () {
							$notice.fadeOut(
								300,
								function () {
									$( this ).remove();
								}
							);
						}
					)
			);

			// Insert at top of page
			if ($( '.wrap' ).length) {
				$( '.wrap' ).prepend( $notice );
			} else {
				$( '#wpbody-content' ).prepend( $notice );
			}

			// Fade in
			$notice.fadeIn( 300 );

			// Auto-dismiss after 5 seconds
			setTimeout(
				function () {
					$notice.fadeOut(
						300,
						function () {
							$( this ).remove();
						}
					);
				},
				5000
			);
		}
	};

})( jQuery );
