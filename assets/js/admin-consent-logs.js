/**
 * Consent Logs Admin JavaScript
 *
 * Handles consent log viewing, filtering, and pagination.
 *
 * @package
 * @since   3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * Consent Logs Admin Class
	 */
	const ConsentLogsAdmin = {
		/**
		 * Current page
		 */
		currentPage: 1,

		/**
		 * Logs per page
		 */
		perPage: 20,

		/**
		 * Total logs count
		 */
		totalLogs: 0,

		/**
		 * Initialize
		 */
		init() {
			this.bindEvents();
			this.loadLogs();
		},

		/**
		 * Bind events
		 */
		bindEvents() {
			// Filter form submission
			$('#slos-logs-filter-form').on('submit', (e) => {
				e.preventDefault();
				this.currentPage = 1;
				this.loadLogs();
			});

			// Pagination clicks
			$(document).on('click', '.slos-page-link', (e) => {
				e.preventDefault();
				const page = $(e.currentTarget).data('page');
				if (page) {
					this.currentPage = page;
					this.loadLogs();
				}
			});

			// View log details
			$(document).on('click', '.slos-view-log', (e) => {
				e.preventDefault();
				const logId = $(e.currentTarget).data('log-id');
				this.viewLogDetails(logId);
			});

			// Close modal
			$('.slos-modal-close, .slos-modal').on('click', (e) => {
				if (e.target === e.currentTarget) {
					$('#slos-log-details-modal').hide();
				}
			});

			// Escape key to close modal
			$(document).on('keyup', (e) => {
				if (e.key === 'Escape') {
					$('#slos-log-details-modal').hide();
				}
			});
		},

		/**
		 * Load logs with current filters
		 */
		loadLogs() {
			const params = this.getFilterParams();
			params.page = this.currentPage;
			params.per_page = this.perPage;

			$('#slos-logs-loading').show();
			$('#slos-logs-table').hide();
			$('#slos-logs-pagination').hide();

			wp.apiRequest({
				path: '/slos/v1/consents/logs?' + $.param(params),
				method: 'GET',
			})
				.done((data, status, xhr) => {
					this.totalLogs = parseInt(
						xhr.getResponseHeader('X-WP-Total') || 0
					);
					this.renderLogs(data);
					this.renderPagination();
				})
				.fail((error) => {
					console.error('Failed to load logs:', error);
					this.showError(
						'Failed to load consent logs. Please try again.'
					);
				})
				.always(() => {
					$('#slos-logs-loading').hide();
				});
		},

		/**
		 * Get filter parameters from form
		 */
		getFilterParams() {
			const params = {};

			const dateFrom = $('#date_from').val();
			const dateTo = $('#date_to').val();
			const action = $('#action_filter').val();
			const purpose = $('#purpose_filter').val();
			const userId = $('#user_id_filter').val();

			if (dateFrom) params.date_from = dateFrom;
			if (dateTo) params.date_to = dateTo;
			if (action) params.action = action;
			if (purpose) params.purpose = purpose;
			if (userId) params.user_id = userId;

			return params;
		},

		/**
		 * Render logs table
		 *
		 * @param logs
		 */
		renderLogs(logs) {
			const tbody = $('#slos-logs-tbody');
			tbody.empty();

			if (logs.length === 0) {
				tbody.append(`
					<tr>
						<td colspan="8" style="text-align:center; padding:40px;">
							${slosLogsData.i18n.no_logs_found}
						</td>
					</tr>
				`);
			} else {
				logs.forEach((log) => {
					tbody.append(this.renderLogRow(log));
				});
			}

			$('#slos-logs-table').show();
		},

		/**
		 * Render single log row
		 *
		 * @param log
		 */
		renderLogRow(log) {
			const actionBadge = this.getActionBadge(log.action);
			const dateTime = new Date(log.created_at).toLocaleString();

			return `
				<tr>
					<td>${log.id}</td>
					<td>${dateTime}</td>
					<td>${log.user_id || 'N/A'}</td>
					<td>${this.escapeHtml(log.purpose || 'N/A')}</td>
					<td>${actionBadge}</td>
					<td>${this.escapeHtml(log.method || 'N/A')}</td>
					<td>${this.escapeHtml(log.ip_address || 'N/A')}</td>
					<td>
						<button type="button" 
							class="button button-small slos-view-log" 
							data-log-id="${log.id}">
							${slosLogsData.i18n.view_details}
						</button>
					</td>
				</tr>
			`;
		},

		/**
		 * Get action badge HTML
		 *
		 * @param action
		 */
		getActionBadge(action) {
			const labels = {
				grant: slosLogsData.i18n.grant,
				withdraw: slosLogsData.i18n.withdraw,
				update: slosLogsData.i18n.update,
				import: slosLogsData.i18n.import,
				export: slosLogsData.i18n.export,
			};

			const label = labels[action] || action;
			return `<span class="slos-action-badge ${action}">${label}</span>`;
		},

		/**
		 * Render pagination
		 */
		renderPagination() {
			const totalPages = Math.ceil(this.totalLogs / this.perPage);

			if (totalPages <= 1) {
				return;
			}

			let html = '';

			// Previous
			if (this.currentPage > 1) {
				html += `<a class="button slos-page-link" data-page="${
					this.currentPage - 1
				}">&laquo; ${slosLogsData.i18n.previous}</a> `;
			}

			// Page numbers
			for (let i = 1; i <= totalPages; i++) {
				if (
					i === 1 ||
					i === totalPages ||
					(i >= this.currentPage - 2 && i <= this.currentPage + 2)
				) {
					const active =
						i === this.currentPage ? 'button-primary' : '';
					html += `<a class="button ${active} slos-page-link" data-page="${i}">${i}</a> `;
				} else if (
					i === this.currentPage - 3 ||
					i === this.currentPage + 3
				) {
					html += '<span>...</span> ';
				}
			}

			// Next
			if (this.currentPage < totalPages) {
				html += `<a class="button slos-page-link" data-page="${
					this.currentPage + 1
				}">${slosLogsData.i18n.next} &raquo;</a>`;
			}

			$('#slos-logs-nav').html(html);
			$('#slos-logs-count').text(
				`${this.totalLogs} ${slosLogsData.i18n.items}`
			);
			$('#slos-logs-pagination').show();
		},

		/**
		 * View log details in modal
		 *
		 * @param logId
		 */
		viewLogDetails(logId) {
			wp.apiRequest({
				path: `/slos/v1/consents/logs/${logId}`,
				method: 'GET',
			})
				.done((log) => {
					this.renderLogDetails(log);
					$('#slos-log-details-modal').show();
				})
				.fail((error) => {
					console.error('Failed to load log details:', error);
					this.showError('Failed to load log details.');
				});
		},

		/**
		 * Render log details in modal
		 *
		 * @param log
		 */
		renderLogDetails(log) {
			const content = $('#slos-log-details-content');
			const dateTime = new Date(log.created_at).toLocaleString();

			let html = `
				<table class="slos-log-details-table">
					<tr>
						<th>${slosLogsData.i18n.log_id}</th>
						<td>${log.id}</td>
					</tr>
					<tr>
						<th>${slosLogsData.i18n.consent_id}</th>
						<td>${log.consent_id || 'N/A'}</td>
					</tr>
					<tr>
						<th>${slosLogsData.i18n.user_id}</th>
						<td>${log.user_id || 'N/A'}</td>
					</tr>
					<tr>
						<th>${slosLogsData.i18n.purpose}</th>
						<td>${this.escapeHtml(log.purpose || 'N/A')}</td>
					</tr>
					<tr>
						<th>${slosLogsData.i18n.action}</th>
						<td>${this.getActionBadge(log.action)}</td>
					</tr>
					<tr>
						<th>${slosLogsData.i18n.method}</th>
						<td>${this.escapeHtml(log.method || 'N/A')}</td>
					</tr>
					<tr>
						<th>${slosLogsData.i18n.ip_address}</th>
						<td>${this.escapeHtml(log.ip_address || 'N/A')}</td>
					</tr>
					<tr>
						<th>${slosLogsData.i18n.user_agent}</th>
						<td>${this.escapeHtml(log.user_agent || 'N/A')}</td>
					</tr>
					<tr>
						<th>${slosLogsData.i18n.created_at}</th>
						<td>${dateTime}</td>
					</tr>
			`;

			if (log.previous_state) {
				html += `
					<tr>
						<th>${slosLogsData.i18n.previous_state}</th>
						<td>
							<div class="slos-log-details-json">
								<pre>${JSON.stringify(log.previous_state, null, 2)}</pre>
							</div>
						</td>
					</tr>
				`;
			}

			if (log.new_state) {
				html += `
					<tr>
						<th>${slosLogsData.i18n.new_state}</th>
						<td>
							<div class="slos-log-details-json">
								<pre>${JSON.stringify(log.new_state, null, 2)}</pre>
							</div>
						</td>
					</tr>
				`;
			}

			html += '</table>';

			content.html(html);
		},

		/**
		 * Show error message
		 *
		 * @param message
		 */
		showError(message) {
			const notice = $(`
				<div class="notice notice-error is-dismissible">
					<p>${message}</p>
				</div>
			`);

			$('.slos-consent-logs h1').after(notice);

			setTimeout(() => {
				notice.fadeOut(() => notice.remove());
			}, 5000);
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
	$(document).ready(() => {
		if ($('.slos-consent-logs').length) {
			ConsentLogsAdmin.init();
		}
	});
})(jQuery);
