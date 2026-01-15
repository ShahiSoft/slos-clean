/**
 * Export/Import Functionality
 *
 * Handles consent export and import operations via REST API.
 *
 * @package
 * @since 3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * Export/Import Manager
	 */
	const ExportImportManager = {
		/**
		 * Initialize
		 */
		init() {
			this.bindEvents();
		},

		/**
		 * Bind event listeners
		 */
		bindEvents() {
			$('#slos-export-now-btn').on('click', this.handleExport.bind(this));
			$('#slos-import-btn').on('click', this.handleImport.bind(this));
		},

		/**
		 * Handle export button click
		 *
		 * @param e
		 */
		handleExport(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);
			const $status = $('#slos-export-status');

			// Disable button
			$btn.prop('disabled', true).addClass('loading');

			// Show loading message
			$status.html(
				'<div class="notice notice-info inline"><p>' +
					'<span class="dashicons dashicons-update spin"></span> ' +
					slosExportImportI18n.exporting +
					'</p></div>'
			);

			// Get export format from settings
			const format = $('#slos_export_format').val() || 'csv';

			// Build API URL
			const apiUrl =
				window.shahiData?.restUrl ||
				'/wp-json/slos/v1/consents/export/download';
			const exportUrl = `${apiUrl}?format=${format}&_wpnonce=${
				window.shahiData?.nonce || ''
			}`;

			// Create hidden iframe for download
			const $iframe = $('<iframe>', {
				src: exportUrl,
				style: 'display:none;',
			}).appendTo('body');

			// Wait for download to initiate
			setTimeout(() => {
				$btn.prop('disabled', false).removeClass('loading');
				$status.html(
					'<div class="notice notice-success inline"><p>' +
						'<span class="dashicons dashicons-yes"></span> ' +
						slosExportImportI18n.exportSuccess +
						'</p></div>'
				);

				// Remove status message after 5 seconds
				setTimeout(() => {
					$status.fadeOut(() => {
						$status.html('').show();
					});
				}, 5000);

				// Remove iframe
				$iframe.remove();
			}, 2000);
		},

		/**
		 * Handle import button click
		 *
		 * @param e
		 */
		handleImport(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);
			const $status = $('#slos-import-status');
			const fileInput = document.getElementById('slos-import-file');

			if (!fileInput || !fileInput.files.length) {
				$status.html(
					'<div class="notice notice-error inline"><p>' +
						'<span class="dashicons dashicons-warning"></span> ' +
						slosExportImportI18n.selectFile +
						'</p></div>'
				);
				return;
			}

			const file = fileInput.files[0];
			const fileExt = file.name.split('.').pop().toLowerCase();

			// Validate file type
			if (fileExt !== 'csv' && fileExt !== 'json') {
				$status.html(
					'<div class="notice notice-error inline"><p>' +
						'<span class="dashicons dashicons-warning"></span> ' +
						slosExportImportI18n.invalidFileType +
						'</p></div>'
				);
				return;
			}

			// Disable button
			$btn.prop('disabled', true).addClass('loading');

			// Show loading message
			$status.html(
				'<div class="notice notice-info inline"><p>' +
					'<span class="dashicons dashicons-update spin"></span> ' +
					slosExportImportI18n.readingFile +
					'</p></div>'
			);

			// Read file content
			const reader = new FileReader();

			reader.onload = (event) => {
				const content = event.target.result;

				// Parse content based on file type
				let rows;
				try {
					if (fileExt === 'json') {
						const data = JSON.parse(content);
						rows = Array.isArray(data)
							? data
							: data.items || data.rows || [];
					} else {
						rows = this.parseCSV(content);
					}

					if (!rows || rows.length === 0) {
						throw new Error(slosExportImportI18n.noDataFound);
					}

					// Import rows via API
					this.importRows(rows, $btn, $status);
				} catch (error) {
					$btn.prop('disabled', false).removeClass('loading');
					$status.html(
						'<div class="notice notice-error inline"><p>' +
							'<span class="dashicons dashicons-warning"></span> ' +
							slosExportImportI18n.parseError +
							': ' +
							error.message +
							'</p></div>'
					);
				}
			};

			reader.onerror = () => {
				$btn.prop('disabled', false).removeClass('loading');
				$status.html(
					'<div class="notice notice-error inline"><p>' +
						'<span class="dashicons dashicons-warning"></span> ' +
						slosExportImportI18n.readError +
						'</p></div>'
				);
			};

			reader.readAsText(file);
		},

		/**
		 * Parse CSV content to array
		 *
		 * @param content
		 */
		parseCSV(content) {
			const lines = content.split('\n').filter((line) => line.trim());
			if (lines.length < 2) return [];

			const headers = lines[0]
				.split(',')
				.map((h) => h.trim().toLowerCase().replace(/[" ]/g, '_'));
			const rows = [];

			for (let i = 1; i < lines.length; i++) {
				const values = lines[i]
					.split(',')
					.map((v) => v.trim().replace(/^"|"$/g, ''));
				if (values.length === headers.length) {
					const row = {};
					headers.forEach((header, index) => {
						row[header] = values[index];
					});
					rows.push(row);
				}
			}

			return rows;
		},

		/**
		 * Import rows via REST API
		 *
		 * @param rows
		 * @param $btn
		 * @param $status
		 */
		importRows(rows, $btn, $status) {
			$status.html(
				'<div class="notice notice-info inline"><p>' +
					'<span class="dashicons dashicons-update spin"></span> ' +
					slosExportImportI18n.importing.replace('%d', rows.length) +
					'</p></div>'
			);

			// Build API URL
			const apiUrl =
				(window.shahiData?.restUrl || '/wp-json/') +
				'slos/v1/consents/export/import';

			// Send import request
			$.ajax({
				url: apiUrl,
				method: 'POST',
				dataType: 'json',
				contentType: 'application/json',
				beforeSend(xhr) {
					xhr.setRequestHeader(
						'X-WP-Nonce',
						window.shahiData?.nonce || ''
					);
				},
				data: JSON.stringify({ rows }),
				success: (response) => {
					$btn.prop('disabled', false).removeClass('loading');

					if (response.success) {
						$status.html(
							'<div class="notice notice-success inline"><p>' +
								'<span class="dashicons dashicons-yes"></span> ' +
								response.message +
								'<br>' +
								'<strong>' +
								slosExportImportI18n.imported +
								':</strong> ' +
								response.imported +
								' | ' +
								'<strong>' +
								slosExportImportI18n.skipped +
								':</strong> ' +
								response.skipped +
								'</p></div>'
						);

						// Show errors if any
						if (response.errors && response.errors.length > 0) {
							let errorHtml = '<ul style="margin-top: 10px;">';
							response.errors.slice(0, 5).forEach((error) => {
								errorHtml += '<li>' + error + '</li>';
							});
							if (response.errors.length > 5) {
								errorHtml +=
									'<li><em>... and ' +
									(response.errors.length - 5) +
									' more errors</em></li>';
							}
							errorHtml += '</ul>';
							$status.find('p').append(errorHtml);
						}

						// Clear file input
						document.getElementById('slos-import-file').value = '';

						// Remove status message after 10 seconds
						setTimeout(() => {
							$status.fadeOut(() => {
								$status.html('').show();
							});
						}, 10000);
					} else {
						$status.html(
							'<div class="notice notice-error inline"><p>' +
								'<span class="dashicons dashicons-warning"></span> ' +
								'Import failed: ' +
								(response.message || 'Unknown error') +
								'</p></div>'
						);
					}
				},
				error: (xhr, status, error) => {
					$btn.prop('disabled', false).removeClass('loading');
					$status.html(
						'<div class="notice notice-error inline"><p>' +
							'<span class="dashicons dashicons-warning"></span> ' +
							'Import failed: ' +
							error +
							'</p></div>'
					);
				},
			});
		},
	};

	// Initialize on document ready
	$(document).ready(() => {
		ExportImportManager.init();
	});
})(jQuery);
