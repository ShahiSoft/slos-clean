/**
 * Legal Acceptance Frontend JavaScript
 *
 * Handles the display and interaction of legal document acceptance
 * banners and modals on the frontend.
 *
 * @package
 * @subpackage Assets
 * @since      3.0.0
 * @version    1.0.0
 */

(function ($) {
	'use strict';

	/**
	 * Legal Acceptance Handler
	 */
	const SlosLegalAcceptance = {
		/**
		 * Configuration
		 */
		config: {},
		documents: [],
		displayType: 'banner',

		/**
		 * Initialize
		 */
		init() {
			// Load configuration from inline JSON
			let dataEl = $( '#slos-acceptance-data' );
			if (dataEl.length) {
				try {
					this.config = JSON.parse( dataEl.text() );
					this.documents = this.config.documents || [];
					this.displayType = this.config.displayType || 'banner';
				} catch (e) {
					console.error( 'Failed to parse acceptance data:', e );
					return;
				}
			}

			if (this.documents.length === 0) {
				return;
			}

			this.bindEvents();
			this.show();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			let self = this;

			// Banner events
			$( '#slos-acceptance-banner' ).on(
				'click',
				'.slos-review-btn',
				function (e) {
					e.preventDefault();
					self.showModal();
				}
			);

			$( '#slos-acceptance-banner' ).on(
				'click',
				'.slos-dismiss-btn',
				function (e) {
					e.preventDefault();
					self.dismissBanner();
				}
			);

			// Modal events
			$( '#slos-acceptance-modal' ).on(
				'click',
				'[data-action="close"]',
				function (e) {
					e.preventDefault();
					self.closeModal();
				}
			);

			$( '#slos-acceptance-modal' ).on(
				'click',
				'.slos-accept-all-btn',
				function (e) {
					e.preventDefault();
					self.acceptAll();
				}
			);

			// View document button
			$( '.slos-view-document-btn' ).on(
				'click',
				function (e) {
					e.preventDefault();
					var docId = $( this ).data( 'doc-id' );
					self.viewDocument( docId );
				}
			);

			// Checkbox change - enable/disable accept button
			$( '#slos-acceptance-modal' ).on(
				'change',
				'.slos-accept-checkbox',
				function () {
					self.updateAcceptButton();
				}
			);

			// Click backdrop to close
			$( '#slos-acceptance-modal' ).on(
				'click',
				'.slos-acceptance-modal-backdrop',
				function (e) {
					if (
						$( e.target ).hasClass( 'slos-acceptance-modal-backdrop' )
					) {
						self.closeModal();
					}
				}
			);

			// ESC key to close modal
			$( document ).on(
				'keydown',
				function (e) {
					if (
					e.key === 'Escape' &&
					$( '#slos-acceptance-modal' ).is( ':visible' )
					) {
						self.closeModal();
					}
				}
			);
		},

		/**
		 * Show banner or modal
		 */
		show() {
			if (this.displayType === 'banner') {
				this.showBanner();
			} else {
				this.showModal();
			}
		},

		/**
		 * Show banner
		 */
		showBanner() {
			$( '#slos-acceptance-banner' ).addClass( 'slos-show' );
		},

		/**
		 * Dismiss banner
		 */
		dismissBanner() {
			let self = this;

			// Warning if documents are required
			if ( ! confirm( this.config.strings.confirmDismiss )) {
				return;
			}

			$( '#slos-acceptance-banner' ).removeClass( 'slos-show' );

			// Set dismissal cookie (24 hours)
			this.setCookie( 'slos_acceptance_dismissed', '1', 1 );
		},

		/**
		 * Show modal
		 */
		showModal() {
			$( '#slos-acceptance-modal' ).fadeIn( 200 );
			$( 'body' ).addClass( 'slos-modal-open' );

			// Focus on first checkbox
			setTimeout(
				function () {
					$( '#slos-acceptance-modal .slos-accept-checkbox:first' ).focus();
				},
				250
			);
		},

		/**
		 * Close modal
		 */
		closeModal() {
			// Warning if documents are required
			if ( ! confirm( this.config.strings.confirmDismiss )) {
				return;
			}

			$( '#slos-acceptance-modal' ).fadeOut( 200 );
			$( 'body' ).removeClass( 'slos-modal-open' );
		},

		/**
		 * Accept all documents
		 */
		acceptAll() {
			let self = this;
			let checkboxes = $( '.slos-accept-checkbox:checked' );

			if (checkboxes.length !== this.documents.length) {
				alert( this.config.strings.selectAll );
				return;
			}

			// Show loading
			this.showLoading( true );

			// Collect document IDs
			let docIds = [];
			checkboxes.each(
				function () {
					docIds.push( parseInt( $( this ).val() ) );
				}
			);

			// Send acceptance for each document
			let promises = [];
			docIds.forEach(
				function (docId) {
					promises.push( self.recordAcceptance( docId ) );
				}
			);

			// Wait for all acceptances to complete
			Promise.all( promises )
				.then(
					function (results) {
						self.onAcceptanceSuccess( results );
					}
				)
				.catch(
					function (error) {
						self.onAcceptanceError( error );
					}
				);
		},

		/**
		 * Record acceptance via REST API
		 *
		 * @param docId
		 */
		recordAcceptance( docId ) {
			let self = this;

			return new Promise(
				function (resolve, reject) {
					$.ajax(
						{
							url: self.config.restUrl + '/' + docId,
							method: 'POST',
							beforeSend( xhr ) {
								xhr.setRequestHeader( 'X-WP-Nonce', self.config.nonce );
							},
							data: JSON.stringify(
								{
									metadata: {
										source: 'frontend_modal',
										url: window.location.href,
										timestamp: new Date().toISOString(),
									},
								}
							),
						contentType: 'application/json',
						success( response ) {
							if (response.success) {
								resolve( response );
							} else {
								reject( response );
							}
							},
							error: function (xhr, status, error) {
								reject( { error: error, status: status } );
							},
						}
					);
				}
			);
		},

		/**
		 * Handle successful acceptance
		 *
		 * @param results
		 */
		onAcceptanceSuccess( results ) {
			this.showLoading( false );

			// Show success message
			this.showSuccessMessage();

			// Remove from display after delay
			setTimeout(
				function () {
					$( '#slos-acceptance-banner' ).fadeOut();
					$( '#slos-acceptance-modal' ).fadeOut();
					$( 'body' ).removeClass( 'slos-modal-open' );
				},
				2000
			);

			// Fire custom event
			$( document ).trigger( 'slos:acceptance:recorded', [results] );
		},

		/**
		 * Handle acceptance error
		 *
		 * @param error
		 */
		onAcceptanceError( error ) {
			this.showLoading( false );

			console.error( 'Acceptance error:', error );
			alert( this.config.strings.error );
		},

		/**
		 * Show success message
		 */
		showSuccessMessage() {
			let $modal = $( '#slos-acceptance-modal' );
			let $body = $modal.find( '.slos-acceptance-modal-body' );

			$body.html(
				'<div class="slos-acceptance-success">' +
					'<span class="dashicons dashicons-yes-alt"></span>' +
					'<h3>' +
					this.config.strings.accepted +
					'</h3>' +
					'</div>'
			);
		},

		/**
		 * Show/hide loading spinner
		 *
		 * @param show
		 */
		showLoading( show ) {
			let $loading = $( '.slos-acceptance-modal-loading' );
			let $footer = $( '.slos-acceptance-modal-footer' );

			if (show) {
				$loading.show();
				$footer.hide();
			} else {
				$loading.hide();
				$footer.show();
			}
		},

		/**
		 * Update accept button state
		 */
		updateAcceptButton() {
			let totalCheckboxes = $( '.slos-accept-checkbox' ).length;
			let checkedCheckboxes = $( '.slos-accept-checkbox:checked' ).length;
			let $acceptBtn = $( '.slos-accept-all-btn' );

			if (checkedCheckboxes === totalCheckboxes) {
				$acceptBtn.prop( 'disabled', false );
			} else {
				$acceptBtn.prop( 'disabled', true );
			}
		},

		/**
		 * View full document
		 *
		 * @param docId
		 */
		viewDocument( docId ) {
			// Build URL to document
			let url = this.config.restUrl.replace(
				'/acceptance',
				'/legaldocs/' + docId
			);

			// Open in new window
			window.open( url, '_blank', 'noopener,noreferrer' );
		},

		/**
		 * Set cookie
		 *
		 * @param name
		 * @param value
		 * @param days
		 */
		setCookie( name, value, days ) {
			let expires = '';
			if (days) {
				let date = new Date();
				date.setTime( date.getTime() + days * 24 * 60 * 60 * 1000 );
				expires = '; expires=' + date.toUTCString();
			}
			document.cookie = name + '=' + (value || '') + expires + '; path=/';
		},

		/**
		 * Get cookie
		 *
		 * @param name
		 */
		getCookie( name ) {
			let nameEQ = name + '=';
			let ca = document.cookie.split( ';' );
			for (let i = 0; i < ca.length; i++) {
				let c = ca[i];
				while (c.charAt( 0 ) === ' ') {
					c = c.substring( 1, c.length );
				}
				if (c.indexOf( nameEQ ) === 0) {
					return c.substring( nameEQ.length, c.length );
				}
			}
			return null;
		},
	};

	/**
	 * Check for pending acceptances on page load
	 */
	const SlosAcceptanceChecker = {
		/**
		 * Initialize
		 */
		init() {
			// Only check if no inline data provided
			if ($( '#slos-acceptance-data' ).length === 0) {
				this.checkPendingAcceptances();
			}
		},

		/**
		 * Check for pending acceptances via REST API
		 */
		checkPendingAcceptances() {
			let self = this;

			// Get REST URL from localized data or construct it
			let restUrl =
				typeof slosAcceptance !== 'undefined'
					? slosAcceptance.restUrl
					: '/wp-json/slos/v1/acceptance/check';

			$.ajax(
				{
					url: restUrl,
					method: 'GET',
					success( response ) {
						if (response.success && response.pending && response.pending.length > 0) {
							self.renderAcceptanceUI( response.pending );
						}
					},
					error( xhr, status, error ) {
						console.error( 'Failed to check pending acceptances:', error );
					}
				},
				}
			);
	},

		/**
		 * Render acceptance UI
		 *
		 * @param documents
		 */
		renderAcceptanceUI( documents ) {
			// Create inline data for SlosLegalAcceptance
			let data = {
				documents,
				displayType: 'modal',
				restUrl: '/wp-json/slos/v1/acceptance',
				nonce:
					typeof slosAcceptance !== 'undefined'
						? slosAcceptance.nonce
						: '',
				strings: {
					accepting: 'Recording acceptance...',
					accepted: 'Thank you for accepting!',
					error: 'An error occurred. Please try again.',
					selectAll: 'Please accept all documents to continue',
					confirmDismiss:
						'You must accept these documents to continue. Are you sure?',
				},
			};

			// Inject data
			$( 'body' ).append(
				'<script type="application/json" id="slos-acceptance-data">' +
					JSON.stringify( data ) +
					'</script>'
			);

			// Load template via AJAX or trigger event for dynamic loading
			// For now, trigger event that theme/plugin can listen to
			$( document ).trigger( 'slos:acceptance:render', [documents] );
	},
};

	/**
	 * Initialize on document ready
	 */
	$( document ).ready(
		function () {
			// Initialize if acceptance data exists
			if ($( '#slos-acceptance-data' ).length) {
				SlosLegalAcceptance.init();
			} else {
				// Check for pending acceptances
				SlosAcceptanceChecker.init();
			}
		}
	);

	// Expose to global scope
	window.SlosLegalAcceptance = SlosLegalAcceptance;
})( jQuery );
