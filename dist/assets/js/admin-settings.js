/**
 * ShahiLegalFlowSuite - Settings Page JavaScript
 *
 * Handles tab navigation, import/export, and interactive features.
 *
 * @package    ShahiLegalFlowSuite
 * @version    3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * Settings Manager
	 */
	const ShahiSettings = {

		/**
		 * Initialize
		 */
		init: function () {
			this.bindEvents();
			this.initTooltips();
		},

		/**
		 * Bind events
		 */
		bindEvents: function () {
			// Export settings
			$( '#shahi-export-settings' ).on( 'click', this.exportSettings.bind( this ) );

			// Import settings
			$( '#shahi-import-settings' ).on( 'click', this.importSettings.bind( this ) );

			// Reset settings
			$( '#shahi-reset-settings' ).on( 'click', this.resetSettings.bind( this ) );

			// Restart onboarding wizard
			$( '#shahi-restart-onboarding' ).on( 'click', this.restartOnboarding.bind( this ) );

			// License activation
			$( '#shahi-activate-license' ).on( 'click', this.activateLicense.bind( this ) );
			$( '#shahi-deactivate-license' ).on( 'click', this.deactivateLicense.bind( this ) );

			// Form submission
			$( 'form[name="shahi_settings_form"]' ).on( 'submit', this.validateForm.bind( this ) );

			// Conditional fields
			this.handleConditionalFields();
		},

		/**
		 * Export settings as JSON
		 */
		exportSettings: function (e) {
			e.preventDefault();

			const button = $( e.currentTarget );
			button.prop( 'disabled', true ).addClass( 'shahi-loading' );

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'shahi_export_settings',
						nonce: shahi_settings_vars.nonce
					},
					success: function (response) {
						if (response.success) {
							// Create download link
							const dataStr        = 'data:text/json;charset=utf-8,' + encodeURIComponent( response.data );
							const downloadAnchor = $( '<a>' );
							const timestamp      = new Date().toISOString().slice( 0, 10 );

							downloadAnchor.attr( 'href', dataStr );
							downloadAnchor.attr( 'download', `shahi - settings - ${timestamp}.json` );
							downloadAnchor[0].click();

							ShahiSettings.showNotice( 'success', 'Settings exported successfully!' );
						} else {
							ShahiSettings.showNotice( 'error', response.data || 'Failed to export settings.' );
						}
					},
					error: function () {
						ShahiSettings.showNotice( 'error', 'An error occurred while exporting settings.' );
					},
					complete: function () {
						button.prop( 'disabled', false ).removeClass( 'shahi-loading' );
					}
				}
			);
		},

		/**
		 * Import settings from JSON file
		 */
		importSettings: function (e) {
			e.preventDefault();

			const fileInput = $( '#shahi-import-file' )[0];
			if ( ! fileInput.files.length) {
				ShahiSettings.showNotice( 'error', 'Please select a JSON file to import.' );
				return;
			}

			const file   = fileInput.files[0];
			const reader = new FileReader();

			reader.onload = function (event) {
				try {
					const settings = JSON.parse( event.target.result );

					if ( ! confirm( 'This will overwrite your current settings. Continue?' )) {
						return;
					}

					const button = $( '#shahi-import-settings' );
					button.prop( 'disabled', true ).addClass( 'shahi-loading' );

					$.ajax(
						{
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'shahi_import_settings',
								nonce: shahi_settings_vars.nonce,
								settings: JSON.stringify( settings )
							},
							success: function (response) {
								if (response.success) {
									ShahiSettings.showNotice( 'success', 'Settings imported successfully! Reloading page...' );
									setTimeout(
										function () {
											location.reload();
										},
										1500
									);
								} else {
									ShahiSettings.showNotice( 'error', response.data || 'Failed to import settings.' );
								}
							},
							error: function () {
								ShahiSettings.showNotice( 'error', 'An error occurred while importing settings.' );
							},
							complete: function () {
								button.prop( 'disabled', false ).removeClass( 'shahi-loading' );
							}
						}
					);
				} catch (error) {
					ShahiSettings.showNotice( 'error', 'Invalid JSON file format.' );
				}
			};

			reader.readAsText( file );
		},

		/**
		 * Reset settings to defaults
		 */
		resetSettings: function (e) {
			e.preventDefault();

			if ( ! confirm( 'Are you sure you want to reset all settings to their defaults? This action cannot be undone!' )) {
				return;
			}

			const button = $( e.currentTarget );
			button.prop( 'disabled', true ).addClass( 'shahi-loading' );

			$.ajax(
				{
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'shahi_reset_settings',
						nonce: shahi_settings_vars.nonce
					},
					success: function (response) {
						if (response.success) {
							ShahiSettings.showNotice( 'success', 'Settings reset successfully! Reloading page...' );
							setTimeout(
								function () {
									location.reload();
								},
								1500
							);
						} else {
							ShahiSettings.showNotice( 'error', response.data || 'Failed to reset settings.' );
						}
					},
					error: function () {
						ShahiSettings.showNotice( 'error', 'An error occurred while resetting settings.' );
					},
					complete: function () {
						button.prop( 'disabled', false ).removeClass( 'shahi-loading' );
					}
				}
			);
		},

		/**
			e.preventDefault();

			if (!confirm('💣 FORCE DELETE: This will delete options & flush ALL caches! Continue?')) {
				return;
			}

			const button = $(e.currentTarget);
			const originalHtml = button.html();

			// Create bomb explosion overlay
			const $bombOverlay = $('<div class="shahi-bomb-overlay">' +
				'<div class="shahi-bomb-container">' +
					'<div class="shahi-bomb">💣</div>' +
					'<div class="shahi-explosion"></div>' +
					'<div class="shahi-bomb-text">DELETING OPTIONS...<br>FLUSHING CACHE...</div>' +
				'</div>' +
			'</div>');

			$('body').append($bombOverlay);

			// Trigger bomb animation after a short delay
			setTimeout(function() {
				$bombOverlay.find('.shahi-bomb').addClass('exploding');
			}, 300);

			button.prop('disabled', true).html('💣 Executing...');

			console.log('🔥 FORCE DELETE initiated...');

			const requestUrl = (typeof shahi_settings_vars !== 'undefined' && shahi_settings_vars.ajaxurl)
				? shahi_settings_vars.ajaxurl
				: (typeof ajaxurl !== 'undefined' ? ajaxurl : '');

			$.ajax({
				url: requestUrl,
				type: 'POST',
				data: {
					action: 'shahi_restart_onboarding',
					nonce: shahi_settings_vars.nonce
				},
				success: function(response) {
					console.log('💥 Force delete response:', response);
					if (response.success) {
						// Show final explosion
						$bombOverlay.find('.shahi-explosion').addClass('active');
						$bombOverlay.find('.shahi-bomb-text').html('💥 KABOOM!<br>Options Deleted!<br>Cache Flushed!');

						setTimeout(function() {
							ShahiSettings.showNotice('success', '✅ Force delete successful! Redirecting...');
							// Add cache-busting parameter to force reload
							const redirectUrl = response.data.redirect || window.location.href;
							const separator = redirectUrl.indexOf('?') === -1 ? '?' : '&';
							const finalUrl = redirectUrl + separator + '_force_reset=' + Date.now();
							console.log('Redirecting to:', finalUrl);
							window.location.replace(finalUrl);
						}, 1500);
					} else {
						console.error('❌ Force delete failed:', response);
						$bombOverlay.remove();
						ShahiSettings.showNotice('error', response.data.message || 'Force delete failed.');
						button.prop('disabled', false).html(originalHtml);
					}
				},
				error: function(xhr, status, error) {
					console.error('❌ AJAX error:', status, error);
					$bombOverlay.remove();
					ShahiSettings.showNotice('error', 'An error occurred during force delete.');
					button.prop('disabled', false).html(originalHtml);
				}
			});
		},

		/**
		 * Activate license
		 * PLACEHOLDER - License system not implemented
		 */
		activateLicense: function (e) {
			e.preventDefault();

			const licenseKey = $( '#license_key' ).val().trim();
			if ( ! licenseKey) {
				ShahiSettings.showNotice( 'error', 'Please enter a license key.' );
				return;
			}

			const button = $( e.currentTarget );
			button.prop( 'disabled', true ).addClass( 'shahi-loading' );

			// MOCK AJAX - License validation not implemented
			setTimeout(
				function () {
					ShahiSettings.showNotice( 'warning', 'License system is not yet implemented. This is a placeholder feature.' );
					button.prop( 'disabled', false ).removeClass( 'shahi-loading' );
				},
				1000
			);
		},

		/**
		 * Deactivate license
		 * PLACEHOLDER - License system not implemented
		 */
		deactivateLicense: function (e) {
			e.preventDefault();

			if ( ! confirm( 'Are you sure you want to deactivate your license?' )) {
				return;
			}

			const button = $( e.currentTarget );
			button.prop( 'disabled', true ).addClass( 'shahi-loading' );

			// MOCK AJAX - License validation not implemented
			setTimeout(
				function () {
					ShahiSettings.showNotice( 'warning', 'License system is not yet implemented. This is a placeholder feature.' );
					button.prop( 'disabled', false ).removeClass( 'shahi-loading' );
				},
				1000
			);
		},

		/**
		 * Validate form before submission
		 */
		*/
		isValidEmail: function (email) {
			const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return re.test( email );
		}
	};

	/**
	 * Initialize on document ready
	 */
	$( document ).ready(
		function () {
			ShahiSettings.init();
		}
	);

})( jQuery );
