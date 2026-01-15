/**
 * Legal Document Version History JavaScript
 *
 * Handles version comparison, rollback, filtering, and audit log display.
 *
 * @package
 * @since 3.0.0
 */

(function ($) {
	'use strict';

	const SlosVersionHistory = {
		selectedVersions: [],
		versions: [],

		/**
		 * Initialize
		 */
		init() {
			if (typeof slosVersionData !== 'undefined') {
				this.versions = slosVersionData.versions;
			}

			this.bindEvents();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			const self = this;

			// Tab switching
			$('.nav-tab').on('click', function (e) {
				e.preventDefault();
				self.switchTab($(this).data('tab'));
			});

			// Version comparison checkboxes
			$('.version-compare-checkbox').on('change', function () {
				self.handleCompareSelection();
			});

			// Compare button
			$('#compare-btn').on('click', function () {
				self.compareVersions();
			});

			// View version button
			$('.view-version-btn').on('click', function () {
				const versionId = $(this).data('version-id');
				self.viewVersion(versionId);
			});

			// Rollback button
			$('.rollback-btn').on('click', function () {
				const versionId = $(this).data('version-id');
				const docId = $(this).data('doc-id');
				self.rollbackVersion(docId, versionId);
			});

			// Modal close
			$('.slos-modal-close').on('click', function () {
				$(this).closest('.slos-modal').hide();
			});

			// Click outside modal to close
			$('.slos-modal').on('click', function (e) {
				if ($(e.target).hasClass('slos-modal')) {
					$(this).hide();
				}
			});

			// Status filters
			$('#filter-published, #filter-draft, #filter-archived').on(
				'change',
				function () {
					self.filterVersions();
				}
			);
		},

		/**
		 * Switch between tabs
		 *
		 * @param tab
		 */
		switchTab(tab) {
			$('.nav-tab').removeClass('nav-tab-active');
			$('.nav-tab[data-tab="' + tab + '"]').addClass('nav-tab-active');
			$('.slos-tab-content').removeClass('slos-tab-active');
			$('#' + tab + '-tab, #' + tab + '-log-tab').addClass(
				'slos-tab-active'
			);
		},

		/**
		 * Handle compare selection
		 */
		handleCompareSelection() {
			this.selectedVersions = [];
			$('.version-compare-checkbox:checked').each(
				function () {
					this.selectedVersions.push(parseInt($(this).val()));
				}.bind(this)
			);

			// Enable compare button only if exactly 2 versions selected
			$('#compare-btn').prop(
				'disabled',
				this.selectedVersions.length !== 2
			);
		},

		/**
		 * Compare two versions
		 */
		compareVersions() {
			if (this.selectedVersions.length !== 2) {
				alert(slosVersionHistory.strings.error);
				return;
			}

			const self = this;
			const version1Id = this.selectedVersions[0];
			const version2Id = this.selectedVersions[1];

			// Show modal with loading
			$('#slos-comparison-modal').show();
			$('#comparison-loading').show();
			$('#comparison-content').hide();

			$.ajax({
				url: slosVersionHistory.ajaxUrl,
				type: 'POST',
				data: {
					action: 'slos_legaldoc_compare_versions',
					nonce: slosVersionHistory.nonce,
					version1_id: version1Id,
					version2_id: version2Id,
				},
				success(response) {
					if (response.success) {
						self.renderComparison(response.data);
					} else {
						$('#comparison-content').html(
							'<p class="error">' + response.data.message + '</p>'
						);
						$('#comparison-loading').hide();
						$('#comparison-content').show();
					}
				},
				error() {
					$('#comparison-content').html(
						'<p class="error">' +
							slosVersionHistory.strings.error +
							'</p>'
					);
					$('#comparison-loading').hide();
					$('#comparison-content').show();
				},
			});
		},

		/**
		 * Render comparison result
		 *
		 * @param data
		 */
		renderComparison(data) {
			let html = '<div class="slos-comparison-header">';
			html += '<div class="slos-comparison-info">';
			html += '<div class="slos-version-info slos-version-left">';
			html += '<h3>Version ' + data.version1.version + '</h3>';
			html +=
				'<p><strong>Status:</strong> ' + data.version1.status + '</p>';
			html +=
				'<p><strong>Author:</strong> ' + data.version1.author + '</p>';
			html +=
				'<p><strong>Date:</strong> ' +
				data.version1.created_at +
				'</p>';
			if (data.version1.changelog) {
				html +=
					'<p><strong>Changelog:</strong> ' +
					this.escapeHtml(data.version1.changelog) +
					'</p>';
			}
			html += '</div>';
			html += '<div class="slos-version-info slos-version-right">';
			html += '<h3>Version ' + data.version2.version + '</h3>';
			html +=
				'<p><strong>Status:</strong> ' + data.version2.status + '</p>';
			html +=
				'<p><strong>Author:</strong> ' + data.version2.author + '</p>';
			html +=
				'<p><strong>Date:</strong> ' +
				data.version2.created_at +
				'</p>';
			if (data.version2.changelog) {
				html +=
					'<p><strong>Changelog:</strong> ' +
					this.escapeHtml(data.version2.changelog) +
					'</p>';
			}
			html += '</div>';
			html += '</div>';
			html += '</div>';
			html += '<div class="slos-diff-container">';
			html += data.diff_html;
			html += '</div>';

			$('#comparison-content').html(html);
			$('#comparison-loading').hide();
			$('#comparison-content').show();
		},

		/**
		 * View version content
		 *
		 * @param versionId
		 */
		viewVersion(versionId) {
			const version = this.versions.find(function (v) {
				return v.id == versionId;
			});

			if (!version) {
				alert(slosVersionHistory.strings.error);
				return;
			}

			let html = '<div class="slos-version-details">';
			html += '<div class="slos-version-meta">';
			html += '<p><strong>Version:</strong> ' + version.version + '</p>';
			html +=
				'<p><strong>Status:</strong> <span class="slos-status-badge slos-status-' +
				(version.status || 'draft') +
				'">' +
				(version.status || 'draft') +
				'</span></p>';
			html +=
				'<p><strong>Author:</strong> ' +
				this.escapeHtml(version.author_name) +
				'</p>';
			html += '<p><strong>Date:</strong> ' + version.created_at + '</p>';
			if (version.changelog) {
				html +=
					'<p><strong>Changelog:</strong> ' +
					this.escapeHtml(version.changelog) +
					'</p>';
			}
			html += '</div>';
			html += '<div class="slos-version-content">';
			html += '<h3>Content:</h3>';
			html +=
				'<div class="slos-content-preview">' +
				version.content +
				'</div>';
			html += '</div>';
			html += '</div>';

			$('#version-view-content').html(html);
			$('#slos-version-view-modal').show();
		},

		/**
		 * Rollback to version
		 *
		 * @param docId
		 * @param versionId
		 */
		rollbackVersion(docId, versionId) {
			if (!confirm(slosVersionHistory.strings.confirmRollback)) {
				return;
			}

			const self = this;
			const $btn = $(
				'.rollback-btn[data-version-id="' + versionId + '"]'
			);
			const originalText = $btn.text();

			$btn.prop('disabled', true).text(
				slosVersionHistory.strings.rollingBack
			);

			$.ajax({
				url: slosVersionHistory.ajaxUrl,
				type: 'POST',
				data: {
					action: 'slos_legaldoc_rollback',
					nonce: slosVersionHistory.nonce,
					doc_id: docId,
					version_id: versionId,
				},
				success(response) {
					if (response.success) {
						alert(slosVersionHistory.strings.rollbackSuccess);
						// Reload page to show new version
						window.location.reload();
					} else {
						alert(
							slosVersionHistory.strings.rollbackFailed +
								': ' +
								response.data.message
						);
						$btn.prop('disabled', false).text(originalText);
					}
				},
				error() {
					alert(slosVersionHistory.strings.rollbackFailed);
					$btn.prop('disabled', false).text(originalText);
				},
			});
		},

		/**
		 * Filter versions by status
		 */
		filterVersions() {
			const showPublished = $('#filter-published').is(':checked');
			const showDraft = $('#filter-draft').is(':checked');
			const showArchived = $('#filter-archived').is(':checked');

			$('.slos-version-row').each(function () {
				const status = $(this).data('status');
				let show = false;

				if (status === 'published' && showPublished) {
					show = true;
				}
				if (status === 'draft' && showDraft) {
					show = true;
				}
				if (status === 'archived' && showArchived) {
					show = true;
				}

				$(this).toggle(show);
			});
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
			return String(text).replace(/[&<>"']/g, function (m) {
				return map[m];
			});
		},
	};

	// Initialize on document ready
	$(document).ready(function () {
		if ($('.slos-version-history-wrap').length) {
			SlosVersionHistory.init();
		}
	});
})(jQuery);
