/**
 * DSR Modern UI JavaScript
 * Interactive features for the modern DSR interface
 *
 * @version 3.0.2
 */

(function ($) {
	'use strict';

	const DSRModern = {
		/**
		 * Initialize all features
		 */
		init: function () {
			this.quickDateFilters();
			this.searchDebounce();
			this.cardActions();
			this.chartInit();
			this.formValidation();
			this.toasts();
			this.headerActions();

			console.log( 'DSR Modern UI initialized' );
		},

		/**
		 * Header action buttons
		 */
		headerActions: function () {
			// New Request button
			$( '#slos-new-request' ).on(
				'click',
				function (e) {
					e.preventDefault();
					// Redirect to DSR form page or open modal
					const dsrFormUrl = typeof slosDSR !== 'undefined' && slosDSR.dsr_form_url
					? slosDSR.dsr_form_url
					: window.location.origin + '/data-request/';
					window.open( dsrFormUrl, '_blank' );
					DSRModern.showToast( 'Opening DSR submission form...', 'info' );
				}
			);

			// Export Requests button
			$( '#slos-export-requests' ).on(
				'click',
				function (e) {
					e.preventDefault();
					// Get current filters and build export URL
					const params = new URLSearchParams( window.location.search );
					params.set( 'action', 'export_csv' );
					params.set( '_wpnonce', typeof slosDSR !== 'undefined' ? slosDSR.nonce : '' );

					const exportUrl      = window.location.pathname + '?' + params.toString();
					window.location.href = exportUrl;
					DSRModern.showToast( 'Preparing export...', 'info' );
				}
			);

			// Generate Report button
			$( '#slos-generate-report' ).on(
				'click',
				function (e) {
					e.preventDefault();
					// Trigger report generation with current date range
					const startDate = $( '#start_date' ).val() || '';
					const endDate   = $( '#end_date' ).val() || '';

					DSRModern.showToast( 'Generating report for ' + startDate + ' to ' + endDate + '...', 'info' );

					// Submit the date range form to regenerate report
					if (startDate && endDate) {
						$( '.slos-date-range-form' ).submit();
					} else {
						DSRModern.showToast( 'Please select a date range first', 'warning' );
					}
				}
			);

			// Header Save Settings button (in header, different from form submit)
			$( '#slos-save-settings' ).on(
				'click',
				function (e) {
					e.preventDefault();
					// Trigger the form submit
					$( '.slos-settings-form' ).submit();
				}
			);
		},

		/**
		 * Quick date filter buttons
		 */
		quickDateFilters: function () {
			$( '.slos-quick-filter' ).on(
				'click',
				function (e) {
					e.preventDefault();
					const days      = $( this ).data( 'days' );
					const endDate   = new Date();
					const startDate = new Date();
					startDate.setDate( startDate.getDate() - days );

					$( '#start_date' ).val( startDate.toISOString().split( 'T' )[0] );
					$( '#end_date' ).val( endDate.toISOString().split( 'T' )[0] );

					$( '.slos-quick-filter' ).removeClass( 'active' );
					$( this ).addClass( 'active' );
				}
			);
		},

		/**
		 * Search with debounce
		 */
		searchDebounce: function () {
			let searchTimeout;
			$( 'input[name="search"]' ).on(
				'input',
				function () {
					clearTimeout( searchTimeout );
					const $input = $( this );

					searchTimeout = setTimeout(
						function () {
							// Show loading state
							$input.addClass( 'loading' );

							// In a real implementation, this would trigger an AJAX search
							setTimeout(
								function () {
									$input.removeClass( 'loading' );
								},
								500
							);
						},
						300
					);
				}
			);
		},

		/**
		 * Card interaction effects
		 */
		cardActions: function () {
			// Card hover effects are handled by CSS

			// Quick action menu toggle
			$( '.slos-card-actions button' ).on(
				'click',
				function (e) {
					e.stopPropagation();
					// Toggle action menu (implement dropdown logic here)
				}
			);

			// Card click to expand/preview
			$( '.slos-request-card' ).on(
				'click',
				function (e) {
					if ( ! $( e.target ).closest( '.slos-card-actions' ).length) {
						// Could open a slide-over panel here
						console.log( 'Card clicked:', $( this ).find( '.slos-request-id' ).text() );
					}
				}
			);
		},

		/**
		 * Initialize charts if Chart.js is available
		 */
		chartInit: function () {
			if (typeof Chart === 'undefined') {
				console.log( 'Chart.js not loaded - charts disabled' );
				return;
			}

			// Trend chart
			const trendCanvas = document.getElementById( 'slos-trend-chart' );
			if (trendCanvas) {
				const ctx = trendCanvas.getContext( '2d' );
				new Chart(
					ctx,
					{
						type: 'line',
						data: {
							labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
							datasets: [{
								label: 'Requests',
								data: [12, 19, 15, 25],
								borderColor: '#0066FF',
								backgroundColor: 'rgba(0, 102, 255, 0.1)',
								tension: 0.4,
								fill: true
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: {
								legend: {
									display: false
								}
							},
							scales: {
								y: {
									beginAtZero: true,
									grid: {
										color: '#F3F4F6'
									}
								},
								x: {
									grid: {
										display: false
									}
								}
							}
						}
					}
				);
			}
		},

		/**
		 * Form validation
		 */
		formValidation: function () {
			$( '.slos-settings-form' ).on(
				'submit',
				function (e) {
					let valid = true;

					// Validate required fields
					$( this ).find( '[required]' ).each(
						function () {
							if ( ! $( this ).val()) {
								valid = false;
								$( this ).addClass( 'error' );
							} else {
								$( this ).removeClass( 'error' );
							}
						}
					);

					if ( ! valid) {
						e.preventDefault();
						DSRModern.showToast( 'Please fill in all required fields', 'error' );
					}
				}
			);
		},

		/**
		 * Toast notifications
		 */
		toasts: function () {
			// Create toast container if it doesn't exist
			if ( ! $( '#slos-toast-container' ).length) {
				$( 'body' ).append( '<div id="slos-toast-container" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;"></div>' );
			}
		},

		/**
		 * Show toast notification
		 */
		showToast: function (message, type = 'info') {
			const icons = {
				success: '✓',
				error: '✗',
				warning: '⚠',
				info: 'ℹ'
			};

			const colors = {
				success: '#10B981',
				error: '#EF4444',
				warning: '#F59E0B',
				info: '#0066FF'
			};

			const toast            = $( '<div>' )
				.addClass( 'slos-toast' )
				.css(
					{
						background: '#fff',
						padding: '12px 16px',
						borderRadius: '8px',
						boxShadow: '0 10px 15px rgba(0, 0, 0, 0.1)',
						marginBottom: '10px',
						display: 'flex',
						alignItems: 'center',
						gap: '12px',
						minWidth: '300px',
						borderLeft: `4px solid ${colors[type]}`
					}
				)
				.html(
					`
					< span style   = "font-size: 18px;" > ${icons[type]} < / span >
					< span style   = "flex: 1; color: #374151; font-size: 14px;" > ${message} < / span >
					< button style = "background: none; border: none; cursor: pointer; color: #9CA3AF; font-size: 18px;" > & times; < / button >
					`
				);

			toast.find( 'button' ).on(
				'click',
				function () {
					toast.fadeOut(
						function () {
							$( this ).remove();
						}
					);
				}
			);

			$( '#slos-toast-container' ).append( toast );

			// Auto-remove after 5 seconds
			setTimeout(
				function () {
					toast.fadeOut(
						function () {
							$( this ).remove();
						}
					);
				},
				5000
			);
		},

		/**
		 * Stat card click to filter
		 */
		statCardFilter: function () {
			$( '.slos-stat-card' ).on(
				'click',
				function () {
					const label = $( this ).find( '.slos-stat-label' ).text().toLowerCase();

					// Apply filter based on card clicked
					if (label.includes( 'pending' )) {
						$( 'select[name="status"]' ).val( 'pending_verification' ).change();
					} else if (label.includes( 'completed' )) {
						$( 'select[name="status"]' ).val( 'completed' ).change();
					}

					// Submit filter form
					$( '.slos-filters-form' ).submit();
				}
			);
		}
	};

	// Initialize on document ready
	$( document ).ready(
		function () {
			DSRModern.init();
		}
	);

	// Expose to global scope for external access
	window.DSRModern = DSRModern;

})( jQuery );

// Global action menu function (called from PHP)
function slOSShowActionsMenu(requestId) {
	console.log( 'Show actions menu for request:', requestId );
	// Implement dropdown/modal logic here
	if (window.DSRModern) {
		window.DSRModern.showToast( 'Action menu for request #' + requestId, 'info' );
	}
}
