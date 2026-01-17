/**
 * Document Hub JavaScript
 *
 * Handles all interactions for the Document Hub dashboard.
 *
 * @package
 * @subpackage Assets
 * @since      4.2.0
 */

(function ($) {
	'use strict';

	const SLOSHub = {
		/**
		 * Current document type being processed
		 */
		currentDocType: null,

		/**
		 * Current document ID
		 */
		currentDocId: null,

		/**
		 * Is this a regeneration?
		 */
		isRegenerate: false,

		/**
		 * Initialize the hub
		 */
		init() {
			this.cacheDom();
			this.bindEvents();
		},

		/**
		 * Cache DOM elements
		 */
		cacheDom() {
			this.$container = $( '.slos-hub-wrap' );
			this.$grid = $( '.slos-hub-grid' );
			this.$modal = $( '#slos-hub-modal' );
			this.$viewModal = $( '#slos-hub-view-modal' );
			this.$historyModal = $( '#slos-hub-history-modal' );
			this.$nonce = $( '#slos-hub-nonce' ).val();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			// Generate button
			$( document ).on(
				'click',
				'.slos-hub-generate-btn',
				this.handleGenerateClick.bind( this )
			);

			// Regenerate button
			$( document ).on(
				'click',
				'.slos-hub-regenerate-btn',
				this.handleRegenerateClick.bind( this )
			);

			// View document button
			$( document ).on(
				'click',
				'.slos-hub-view-btn',
				this.handleViewClick.bind( this )
			);

			// History button
			$( document ).on(
				'click',
				'.slos-hub-history-btn',
				this.handleHistoryClick.bind( this )
			);

			// Download button
			$( document ).on(
				'click',
				'.slos-hub-download-btn',
				this.handleDownloadClick.bind( this )
			);

			// Copy shortcode button
			$( document ).on(
				'click',
				'.slos-hub-copy-shortcode',
				this.handleCopyShortcode.bind( this )
			);

			// Regenerate all button
			$( document ).on(
				'click',
				'.slos-hub-regenerate-all',
				this.handleRegenerateAll.bind( this )
			);

			// Export all button
			$( document ).on(
				'click',
				'.slos-hub-export-all',
				this.handleExportAll.bind( this )
			);

			// Filter buttons
			$( document ).on(
				'click',
				'.slos-hub-filter-btn',
				this.handleFilter.bind( this )
			);

			// Modal close buttons
			$( document ).on(
				'click',
				'.slos-modal__close, .slos-modal__cancel, .slos-modal__backdrop',
				this.closeModals.bind( this )
			);

			// Confirm generate button
			$( document ).on(
				'click',
				'.slos-hub-confirm-generate',
				this.handleConfirmGenerate.bind( this )
			);

			// ESC key to close modals
			$( document ).on( 'keydown', this.handleKeydown.bind( this ) );
		},

		/**
		 * Handle generate button click
		 *
		 * @param e
		 */
		handleGenerateClick( e ) {
			e.preventDefault();
			const $btn = $( e.currentTarget );
			this.currentDocType = $btn.data( 'type' );
			this.isRegenerate = false;

			this.openGenerateModal();
		},

		/**
		 * Handle regenerate button click
		 *
		 * @param e
		 */
		handleRegenerateClick( e ) {
			e.preventDefault();
			const $btn = $( e.currentTarget );
			this.currentDocType = $btn.data( 'type' );
			this.currentDocId = $btn.data( 'doc-id' );
			this.isRegenerate = true;

			// Confirm regeneration
			if ( ! confirm( slosHub.strings.confirmRegenerate )) {
				return;
			}

			this.openGenerateModal();
		},

		/**
		 * Open generate modal and load context
		 */
		openGenerateModal() {
			const $title = this.$modal.find( '.slos-modal__title' );
			const $body = this.$modal.find( '.slos-modal__body' );
			const $confirmBtn = this.$modal.find( '.slos-hub-confirm-generate' );

			$title.text(
				this.isRegenerate
					? slosHub.strings.regenerating
					: slosHub.strings.generating
			);
			$body.html(
				'<div class="slos-modal__loading"><span class="slos-spinner"></span><span>' +
					slosHub.strings.loading +
					'</span></div>'
			);
			$confirmBtn.prop( 'disabled', true );

			this.$modal.addClass( 'slos-modal--active' );

			// Load generation context
			this.loadGenerationContext();
		},

		/**
		 * Load generation context from server
		 */
		loadGenerationContext() {
			const self = this;
			const $body = this.$modal.find( '.slos-modal__body' );
			const $confirmBtn = this.$modal.find( '.slos-hub-confirm-generate' );

			$.ajax(
				{
					url: slosHub.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_gen_get_context',
						nonce: slosHub.nonce,
						doc_type: this.currentDocType,
					},
					success: function (response) {
						console.log( 'SLOS Hub: AJAX response', response );
						if (response.success) {
							try {
								self.renderGenerationContext( response.data );
								$confirmBtn.prop(
									'disabled',
									! response.data.can_generate
								);
							} catch (e) {
								console.error(
									'SLOS Hub: renderGenerationContext error',
									e
								);
								$body.html(
									'<div class="slos-hub-alert slos-hub-alert--error"><span class="dashicons dashicons-warning"></span>JavaScript error: ' +
									e.message +
									'</div>'
								);
							}
						} else {
							$body.html(
								'<div class="slos-hub-alert slos-hub-alert--error"><span class="dashicons dashicons-warning"></span>' +
								(response.data.message ||
									slosHub.strings.generateError) +
								'</div>'
							);
						}
					},
					error( xhr, status, error ) {
						console.error( 'SLOS Hub: AJAX error', status, error );
						$body.html( '<div class="slos-hub-alert slos-hub-alert--error"><span class="dashicons dashicons-warning"></span>' + slosHub.strings.generateError + '</div>' );
					}
				},
				}
			);
	},

		/**
		 * Render generation context in modal
		 *
		 * @param data
		 */
		renderGenerationContext( data ) {
			const $body = this.$modal.find( '.slos-modal__body' );

			// Debug: check if $body exists
			if ( ! $body.length) {
				console.error( 'SLOS Hub: Modal body not found' );
				return;
			}

			let html = '';

			// Document info
			html += '<div class="slos-hub-context">';
			html +=
				'<h4 class="slos-hub-context__title">' +
				(data.document_title || 'Document') +
				'</h4>';

			// Validation status
			if (data.is_valid) {
				html +=
					'<div class="slos-hub-context__status slos-hub-context__status--success">';
				html += '<span class="dashicons dashicons-yes-alt"></span>';
				html +=
					slosHub.strings && slosHub.strings.profileComplete
						? slosHub.strings.profileComplete
						: 'Profile is complete';
				html += '</div>';
			} else {
				html +=
					'<div class="slos-hub-context__status slos-hub-context__status--warning">';
				html += '<span class="dashicons dashicons-warning"></span>';
				html +=
					slosHub.strings && slosHub.strings.profileIncomplete
						? slosHub.strings.profileIncomplete
						: 'Profile is incomplete';
				html += '</div>';

				// Missing fields
				if (data.missing_fields && data.missing_fields.length > 0) {
					html += '<div class="slos-hub-context__missing">';
					html +=
						'<p><strong>' +
						(slosHub.strings && slosHub.strings.missingFields
							? slosHub.strings.missingFields
							: 'Missing fields:') +
						'</strong></p>';
					html += '<ul>';
					data.missing_fields.forEach(
						function (field) {
							html +=
							'<li><span class="dashicons dashicons-no-alt"></span> ' +
							field +
							'</li>';
						}
					);
					html += '</ul>';
					html +=
						'<a href="' +
						(slosHub.profileUrl || '#') +
						'" class="slos-btn slos-btn--secondary slos-btn--sm">';
					html +=
						'<span class="dashicons dashicons-edit"></span>' +
						(slosHub.strings && slosHub.strings.completeProfile
							? slosHub.strings.completeProfile
							: 'Complete Profile');
					html += '</a>';
					html += '</div>';
				}
			}

			// Existing document warning
			if (data.existing_doc_id && this.isRegenerate) {
				html += '<div class="slos-hub-context__warning">';
				html +=
					'<span class="dashicons dashicons-info-outline"></span>';
				html +=
					slosHub.strings && slosHub.strings.regenerateWarning
						? slosHub.strings.regenerateWarning
						: 'This will create a new version.';
				html += '</div>';
			}

			html += '</div>';

			console.log( 'SLOS Hub: Rendering HTML to modal body' );
			$body.html( html );
			console.log( 'SLOS Hub: Modal body updated successfully' );
	},

		/**
		 * Handle confirm generate button
		 *
		 * @param e
		 */
		handleConfirmGenerate( e ) {
			e.preventDefault();
			const self = this;
			const $btn = $( e.currentTarget );

			$btn.addClass( 'slos-btn--loading' ).prop( 'disabled', true );

			$.ajax(
				{
					url: slosHub.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_gen_generate',
						nonce: slosHub.nonce,
						doc_type: this.currentDocType,
					},
					success( response ) {
						if (response.success) {
							self.showToast( slosHub.strings.generated, 'success' );

							// Redirect to edit page or reload
							if (response.data.edit_url) {
								setTimeout(
									function () {
										window.location.href = response.data.edit_url;
									},
									1000
								);
							} else {
								setTimeout(
									function () {
										window.location.reload();
									},
									1000
								);
							}
						} else {
							self.showToast( response.data.message || slosHub.strings.generateError, 'error' );
							$btn.removeClass( 'slos-btn--loading' ).prop( 'disabled', false );
						}
					},
					error() {
						self.showToast( slosHub.strings.generateError, 'error' );
						$btn.removeClass( 'slos-btn--loading' ).prop( 'disabled', false );
					}
				},
				}
			);
},

		/**
		 * Handle view document click
		 *
		 * @param e
		 */
		handleViewClick( e ) {
			e.preventDefault();
			const $btn = $( e.currentTarget );
			const docId = $btn.data( 'doc-id' );
			const self = this;

			const $body = this.$viewModal.find( '.slos-modal__body' );
			const $editBtn = this.$viewModal.find( '.slos-hub-edit-doc' );

			$body.html(
				'<div class="slos-modal__loading"><span class="slos-spinner"></span><span>' +
					slosHub.strings.loading +
					'</span></div>'
			);
			this.$viewModal.addClass( 'slos-modal--active' );

			$.ajax(
				{
					url: slosHub.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_gen_view_document',
						nonce: slosHub.nonce,
						doc_id: docId,
					},
					success( response ) {
						console.log( 'SLOS Hub View: Response received', response );
						if (response.success) {
							console.log( 'SLOS Hub View: Content length:', response.data.html ? response.data.html.length : 0 );
							console.log( 'SLOS Hub View: Word count:', response.data.word_count );
							// Replace entire body content with preview content wrapper
							$body.html( '<div class="slos-hub-preview-content">' + response.data.html + '</div>' );
							self.$viewModal.find( '.slos-modal__title' ).text( response.data.title || slosHub.strings.documentPreview );
							$editBtn.attr( 'href', slosHub.editUrl + '&id=' + docId );
						} else {
							$body.html( '<div class="slos-hub-alert slos-hub-alert--error">' + (response.data.message || slosHub.strings.error) + '</div>' );
						}
					},
					error: function () {
						$body.html(
							'<div class="slos-hub-alert slos-hub-alert--error">' +
							slosHub.strings.error +
							'</div>'
						);
					},
				}
			);
		},

		/**
		 * Handle history button click
		 *
		 * @param e
		 */
		handleHistoryClick( e ) {
			e.preventDefault();
			const $btn = $( e.currentTarget );
			const docId = $btn.data( 'doc-id' );
			const self = this;

			const $body = this.$historyModal.find( '.slos-modal__body' );
			$body.html(
				'<div class="slos-modal__loading"><span class="slos-spinner"></span><span>' +
					slosHub.strings.loading +
					'</span></div>'
			);
			this.$historyModal.addClass( 'slos-modal--active' );

			$.ajax(
				{
					url: slosHub.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_gen_history',
						nonce: slosHub.nonce,
						doc_id: docId,
					},
					success: function (response) {
						if (response.success) {
							self.renderHistory( response.data.versions );
						} else {
							$body.html(
								'<div class="slos-hub-alert slos-hub-alert--error">' +
								(response.data.message ||
									slosHub.strings.error) +
								'</div>'
							);
						}
					},
					error: function () {
						$body.html(
							'<div class="slos-hub-alert slos-hub-alert--error">' +
							slosHub.strings.error +
							'</div>'
						);
					},
				}
			);
		},

		/**
		 * Render version history
		 *
		 * @param versions
		 */
		renderHistory( versions ) {
			const $body = this.$historyModal.find( '.slos-modal__body' );
			let html = '<div class="slos-hub-history-list">';

			if ( ! versions || versions.length === 0) {
				html +=
					'<p class="slos-hub-empty-message">' +
					slosHub.strings.noVersions +
					'</p>';
			} else {
				versions.forEach(
					function (version) {
						html += '<div class="slos-hub-history-item">';
						html +=
						'<span class="slos-hub-history-item__version">v' +
						version.version_num +
						'</span>';
						html += '<div class="slos-hub-history-item__info">';
						html +=
						'<p class="slos-hub-history-item__reason">' +
						(version.change_reason || slosHub.strings.noReason) +
						'</p>';
						html +=
						'<p class="slos-hub-history-item__meta">' +
						version.author_name +
						' • ' +
						version.created_at +
						'</p>';
						html += '</div>';
						html += '</div>';
					}
				);
			}

			html += '</div>';
			$body.html( html );
		},

		/**
		 * Handle download button click
		 *
		 * @param e
		 */
		handleDownloadClick( e ) {
			e.preventDefault();
			const $btn = $( e.currentTarget );
			const docId = $btn.data( 'doc-id' );
			const self = this;

			$btn.addClass( 'slos-btn--loading' );

			$.ajax(
				{
					url: slosHub.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_export_document',
						nonce: slosHub.nonce,
						doc_id: docId,
						format: 'pdf',
					},
					success( response ) {
						if (response.success && response.data.download_url) {
							// Trigger download
							window.location.href = response.data.download_url;
							self.showToast( slosHub.strings.downloadStarted, 'success' );
						} else {
							self.showToast( response.data.message || slosHub.strings.downloadError, 'error' );
						}
					},
					error() {
						self.showToast( slosHub.strings.downloadError, 'error' );
					},
					complete() {
						$btn.removeClass( 'slos-btn--loading' );
					}
				}
			);
		},

		/**
		 * Handle copy shortcode
		 *
		 * @param e
		 */
		handleCopyShortcode( e ) {
			e.preventDefault();
			const $btn = $( e.currentTarget );
			const shortcode = $btn.data( 'shortcode' );

			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard
					.writeText( shortcode )
					.then(
						function () {
							SLOSHub.showToast(
								slosHub.strings.shortcodeCopied,
								'success'
							);
						}
					)
					.catch(
						function () {
							SLOSHub.fallbackCopy( shortcode );
						}
					);
			} else {
				this.fallbackCopy( shortcode );
			}
		},

		/**
		 * Fallback copy method
		 *
		 * @param text
		 */
		fallbackCopy( text ) {
			const $temp = $( '<textarea>' );
			$( 'body' ).append( $temp );
			$temp.val( text ).select();
			document.execCommand( 'copy' );
			$temp.remove();
			this.showToast( slosHub.strings.shortcodeCopied, 'success' );
		},

		/**
		 * Handle regenerate all
		 *
		 * @param e
		 */
		handleRegenerateAll( e ) {
			e.preventDefault();

			if ( ! confirm( slosHub.strings.confirmBulkRegenerate )) {
				return;
			}

			const self = this;
			const $btn = $( e.currentTarget );
			$btn.addClass( 'slos-btn--loading' ).prop( 'disabled', true );

			$.ajax(
				{
					url: slosHub.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_hub_bulk_action',
						nonce: slosHub.nonce,
						bulk_action: 'regenerate_outdated',
					},
					success( response ) {
						if (response.success) {
							self.showToast( response.data.message || slosHub.strings.regenerated, 'success' );
							setTimeout(
								function () {
									window.location.reload();
								},
								1500
							);
						} else {
							self.showToast( response.data.message || slosHub.strings.regenerateError, 'error' );
						}
					},
					error() {
						self.showToast( slosHub.strings.regenerateError, 'error' );
					},
					complete: function () {
						$btn.removeClass( 'slos-btn--loading' ).prop(
							'disabled',
							false
						);
					},
				}
			);
		},

		/**
		 * Handle export all
		 *
		 * @param e
		 */
		handleExportAll( e ) {
			e.preventDefault();
			const self = this;
			const $btn = $( e.currentTarget );

			$btn.addClass( 'slos-btn--loading' ).prop( 'disabled', true );

			// Get all doc IDs from cards
			const docIds = [];
			$( '.slos-card[data-doc-id]' ).each(
				function () {
					const id = $( this ).data( 'doc-id' );
					if (id && id > 0) {
						docIds.push( id );
					}
				}
			);

if (docIds.length === 0) {
	self.showToast( slosHub.strings.noDocsToExport, 'warning' );
	$btn.removeClass( 'slos-btn--loading' ).prop( 'disabled', false );
	return;
}

			$.ajax(
				{
					url: slosHub.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_export_bulk',
						nonce: slosHub.nonce,
						doc_ids: docIds,
						format: 'html',
					},
					success( response ) {
						if (response.success && response.data.download_url) {
							window.location.href = response.data.download_url;
							self.showToast( slosHub.strings.exportSuccess, 'success' );
						} else {
							self.showToast( response.data.message || slosHub.strings.exportError, 'error' );
						}
					},
					error() {
						self.showToast( slosHub.strings.exportError, 'error' );
					},
					complete: function () {
						$btn.removeClass( 'slos-btn--loading' ).prop(
							'disabled',
							false
						);
					},
				}
			);
		},

		/**
		 * Handle filter button click
		 *
		 * @param e
		 */
		handleFilter( e ) {
			e.preventDefault();
			const $btn = $( e.currentTarget );
			const filter = $btn.data( 'filter' );

			// Update active state
			$( '.slos-hub-filter-btn' ).removeClass(
				'slos-hub-filter-btn--active'
			);
			$btn.addClass( 'slos-hub-filter-btn--active' );

			// Filter cards
if (filter === 'all') {
	$( '.slos-card' ).show();
} else {
	$( '.slos-card' ).each(
		function () {
			if ($( this ).data( 'category' ) === filter) {
				$( this ).show();
			} else {
				$( this ).hide();
			}
		}
	);
}
		},

		/**
		 * Close all modals
		 *
		 * @param e
		 */
		closeModals( e ) {
			if (e) {
				// Only close if clicking backdrop, close button, or cancel button
				const $target = $( e.target );
				if (
					! $target.hasClass( 'slos-modal__backdrop' ) &&
					! $target.hasClass( 'slos-modal__close' ) &&
					! $target.hasClass( 'slos-modal__cancel' ) &&
					! $target.closest( '.slos-modal__close' ).length
				) {
					return;
				}
			}

			$( '.slos-modal' ).removeClass( 'slos-modal--active' );
			this.currentDocType = null;
			this.currentDocId = null;
			this.isRegenerate = false;
		},

		/**
		 * Handle ESC key
		 *
		 * @param e
		 */
		handleKeydown( e ) {
			if (e.key === 'Escape') {
				this.closeModals();
			}
		},

		/**
		 * Show toast notification
		 *
		 * @param message
		 * @param type
		 */
		showToast( message, type ) {
			type = type || 'info';

			// Remove existing toasts
			$( '.slos-toast' ).remove();

			const $toast = $(
				'<div class="slos-toast slos-toast--' +
					type +
					'">' +
					message +
					'</div>'
			);
			$( 'body' ).append( $toast );

			// Auto remove after 4 seconds
			setTimeout(
				function () {
					$toast.fadeOut(
						300,
						function () {
							$( this ).remove();
						}
					);
				},
				4000
			);
		},
	};

	// Initialize when document is ready
	$( document ).ready(
		function () {
			SLOSHub.init();
		}
	);
})( jQuery );
