/**
 * DSR Status Portal JavaScript
 *
 * Handles form submission, API calls, and status display for the public status portal.
 *
 * @package
 * @since   3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * DSR Status Handler
	 */
	const DsrStatusHandler = {
		/**
		 * Initialize
		 */
		init() {
			this.cacheElements();
			this.bindEvents();
			this.checkUrlToken();
		},

		/**
		 * Cache DOM elements
		 */
		cacheElements() {
			this.$portal = $('#slos-dsr-status-portal');
			this.$form = $('#slos-dsr-status-form');
			this.$tokenInput = $('#slos-dsr-token');
			this.$result = $('#slos-dsr-status-result');
			this.$loading = $('#slos-dsr-status-loading');
			this.$error = $('#slos-dsr-status-error');
		},

		/**
		 * Bind events
		 */
		bindEvents() {
			this.$form.on('submit', this.handleSubmit.bind(this));
		},

		/**
		 * Check for token in URL
		 */
		checkUrlToken() {
			const urlParams = new URLSearchParams(window.location.search);
			const token = urlParams.get('token');

			if (token) {
				this.$tokenInput.val(token);
				this.checkStatus(token);
			}
		},

		/**
		 * Handle form submission
		 *
		 * @param e
		 */
		handleSubmit(e) {
			e.preventDefault();

			const token = this.$tokenInput.val().trim();

			if (!token) {
				this.showError(slosDsrStatus.i18n.invalidToken);
				return;
			}

			this.checkStatus(token);
		},

		/**
		 * Check status via API
		 *
		 * @param token
		 */
		checkStatus(token) {
			this.hideAll();
			this.$loading.show();

			$.ajax({
				url: slosDsrStatus.apiUrl,
				method: 'GET',
				data: { token },
				headers: {
					'X-WP-Nonce': slosDsrStatus.nonce,
				},
			})
				.done((response) => {
					if (response.success && response.request) {
						this.displayStatus(response.request);
					} else {
						this.showError(slosDsrStatus.i18n.requestFailed);
					}
				})
				.fail((xhr) => {
					if (xhr.status === 404) {
						this.showError(slosDsrStatus.i18n.invalidToken);
					} else {
						this.showError(slosDsrStatus.i18n.requestFailed);
					}
				})
				.always(() => {
					this.$loading.hide();
				});
		},

		/**
		 * Display status information
		 *
		 * @param request
		 */
		displayStatus(request) {
			const statusLabel = this.getStatusLabel(request.status);
			const typeLabel = this.getTypeLabel(request.type);

			let html = '<div class="slos-status-header">';
			html += '<div class="slos-status-info">';
			html += '<h3>' + this.escapeHtml(typeLabel) + '</h3>';
			html += '<p>Request ID: #' + request.id + '</p>';
			html += '</div>';
			html += '<div class="slos-status-badge-wrapper">';
			html +=
				'<span class="slos-status-badge status-' +
				request.status +
				'">' +
				this.escapeHtml(statusLabel) +
				'</span>';
			html += '</div>';
			html += '</div>';

			// Details
			html += '<div class="slos-status-details">';

			html += '<div class="slos-status-detail">';
			html += '<span class="slos-status-detail-label">Submitted</span>';
			html +=
				'<span class="slos-status-detail-value">' +
				this.formatDate(request.submitted_at) +
				'</span>';
			html += '</div>';

			html += '<div class="slos-status-detail">';
			html += '<span class="slos-status-detail-label">Due Date</span>';
			html +=
				'<span class="slos-status-detail-value">' +
				this.formatDate(request.due_date) +
				'</span>';
			html += '</div>';

			html += '<div class="slos-status-detail">';
			html += '<span class="slos-status-detail-label">Regulation</span>';
			html +=
				'<span class="slos-status-detail-value">' +
				this.escapeHtml(request.regulation) +
				'</span>';
			html += '</div>';

			if (request.completed_at) {
				html += '<div class="slos-status-detail">';
				html +=
					'<span class="slos-status-detail-label">Completed</span>';
				html +=
					'<span class="slos-status-detail-value">' +
					this.formatDate(request.completed_at) +
					'</span>';
				html += '</div>';
			}

			html += '</div>';

			// Timeline
			html += this.renderTimeline(request.status);

			// Next steps
			html += this.renderNextSteps(request.status, request.type);

			this.$result.html(html).show();
		},

		/**
		 * Render timeline
		 *
		 * @param currentStatus
		 */
		renderTimeline(currentStatus) {
			const steps = [
				{ key: 'new', label: 'Submitted' },
				{ key: 'pending_verification', label: 'Verification' },
				{ key: 'verified', label: 'Verified' },
				{ key: 'in_progress', label: 'Processing' },
				{ key: 'completed', label: 'Completed' },
			];

			const statusOrder = [
				'new',
				'pending_verification',
				'verified',
				'in_progress',
				'completed',
				'rejected',
			];
			const currentIndex = statusOrder.indexOf(currentStatus);

			let html = '<div class="slos-status-timeline">';
			html += '<h4>Progress</h4>';
			html += '<div class="slos-timeline-steps">';

			steps.forEach((step, index) => {
				const stepIndex = statusOrder.indexOf(step.key);
				let stepClass = '';

				if (stepIndex < currentIndex || currentStatus === 'completed') {
					stepClass = 'completed';
				} else if (stepIndex === currentIndex) {
					stepClass = 'active';
				}

				html += '<div class="slos-timeline-step ' + stepClass + '">';
				html += '<div class="slos-timeline-step-icon">';
				html += stepClass === 'completed' ? '✓' : index + 1;
				html += '</div>';
				html +=
					'<div class="slos-timeline-step-label">' +
					this.escapeHtml(step.label) +
					'</div>';
				html += '</div>';
			});

			html += '</div>';
			html += '</div>';

			return html;
		},

		/**
		 * Render next steps
		 *
		 * @param status
		 * @param type
		 */
		renderNextSteps(status, type) {
			let message = '';

			switch (status) {
				case 'new':
					message =
						'Your request has been received and is awaiting initial review.';
					break;
				case 'pending_verification':
					message =
						'Please check your email for a verification link to confirm your identity.';
					break;
				case 'verified':
					message =
						'Your identity has been verified. Your request is now in the queue for processing.';
					break;
				case 'in_progress':
					if (type === 'access' || type === 'portability') {
						message =
							'We are preparing your data export. You will receive a download link via email when ready.';
					} else if (type === 'deletion' || type === 'erasure') {
						message =
							'We are processing your erasure request. You will be notified when completed.';
					} else {
						message =
							'Your request is currently being processed. You will be notified of any updates.';
					}
					break;
				case 'completed':
					if (type === 'access' || type === 'portability') {
						message =
							'Your data export is ready. Check your email for the download link (valid for 7 days).';
					} else if (type === 'deletion' || type === 'erasure') {
						message =
							'Your data has been anonymized/deleted as requested.';
					} else {
						message =
							'Your request has been completed successfully.';
					}
					break;
				case 'rejected':
					message =
						'Your request could not be processed. Please contact us for more information.';
					break;
				default:
					message =
						'Your request is being processed. Check back later for updates.';
			}

			let html = '<div class="slos-status-next-steps">';
			html += "<h4>What's Next?</h4>";
			html += '<p>' + this.escapeHtml(message) + '</p>';
			html += '</div>';

			return html;
		},

		/**
		 * Show error message
		 *
		 * @param message
		 */
		showError(message) {
			this.hideAll();
			this.$error
				.html(
					'<strong>' +
						slosDsrStatus.i18n.error +
						'</strong><p>' +
						this.escapeHtml(message) +
						'</p>'
				)
				.show();
		},

		/**
		 * Hide all result containers
		 */
		hideAll() {
			this.$result.hide();
			this.$loading.hide();
			this.$error.hide();
		},

		/**
		 * Get status label
		 *
		 * @param status
		 */
		getStatusLabel(status) {
			const key =
				'status' +
				status.charAt(0).toUpperCase() +
				status.slice(1).replace(/_/g, '');
			return slosDsrStatus.i18n[key] || status;
		},

		/**
		 * Get type label
		 *
		 * @param type
		 */
		getTypeLabel(type) {
			const key =
				'type' +
				type.charAt(0).toUpperCase() +
				type.slice(1).replace(/_/g, '');
			return slosDsrStatus.i18n[key] || type;
		},

		/**
		 * Format date
		 *
		 * @param dateString
		 */
		formatDate(dateString) {
			if (!dateString) return 'N/A';

			try {
				const date = new Date(dateString);
				return date.toLocaleDateString(undefined, {
					year: 'numeric',
					month: 'long',
					day: 'numeric',
				});
			} catch (e) {
				return dateString;
			}
		},

		/**
		 * Escape HTML
		 *
		 * @param text
		 */
		escapeHtml(text) {
			const map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;',
			};
			return String(text).replace(/[&<>"']/g, (m) => map[m]);
		},
	};

	// Initialize on document ready
	$(document).ready(function () {
		if ($('#slos-dsr-status-portal').length) {
			DsrStatusHandler.init();
		}
	});
})(jQuery);
