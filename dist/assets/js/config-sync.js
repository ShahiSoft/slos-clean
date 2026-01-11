/**
 * Config Sync JavaScript - Multi-Site Configuration Management
 *
 * Handles export/import UI interactions and REST API calls.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Assets/JS
 * @since      3.1.1
 */

(function ($) {
	'use strict';

	// REST API endpoint base
	const API_BASE = window.wpApiSettings ? .root + 'slos/v1/config';
	const NONCE    = window.wpApiSettings ? .nonce;

	/**
	 * Initialize config sync UI
	 */
	function init() {
		bindExportForm();
		bindImportForm();
		bindFileUpload();
		bindExportActions();
		bindCheckboxActions();
	}

	/**
	 * Bind export form submission
	 */
	function bindExportForm() {
		$( '#slos-export-form' ).on(
			'submit',
			function (e) {
				e.preventDefault();

				const $form  = $( this );
				const action = $( 'button[type="submit"]:focus', $form ).val() ||
							$( e.originalEvent.submitter ).val();

				const name        = $( '#slos-export-name' ).val().trim();
				const description = $( '#slos-export-description' ).val().trim();
				const options     = $( 'input[name="options[]"]:checked' )
				.map(
					function () {
						return $( this ).val(); }
				)
				.get();

				// Validation
				if ( ! name) {
					showStatus( '#slos-export-status', 'error', 'Profile name is required.' );
					return;
				}

				if (options.length === 0) {
					showStatus( '#slos-export-status', 'error', 'Please select at least one setting to export.' );
					return;
				}

				// Determine action
				if (action === 'export_file') {
					exportToFile( name, description, options );
				} else if (action === 'export_json') {
					exportToJSON( name, description, options );
				}
			}
		);
	}

	/**
	 * Export configuration to file
	 */
	function exportToFile(name, description, options) {
		const $btn = $( '#slos-export-file-btn' );
		setButtonLoading( $btn, true );
		showStatus( '#slos-export-status', 'info', 'Exporting configuration...' );

		$.ajax(
			{
				url: API_BASE + '/export',
				method: 'POST',
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce', NONCE );
				},
				contentType: 'application/json',
				data: JSON.stringify(
					{
						name: name,
						description: description,
						options: options,
						to_file: true
					}
				),
			success: function (response) {
				if (response.success && response.url) {
					showStatus(
						'#slos-export-status',
						'success',
						'Export successful! Downloading file...'
					);

					// Trigger download
					window.location.href = response.url;

					// Refresh exports table
					setTimeout(
						function () {
							refreshExportsTable();
						},
						1000
					);

					// Reset form
					$( '#slos-export-form' )[0].reset();
					$( 'input[name="options[]"]' ).prop( 'checked', true );
				} else {
					showStatus(
						'#slos-export-status',
						'error',
						response.message || 'Export failed.'
					);
				}
			},
				error: function (xhr) {
					const errorMsg = xhr.responseJSON ? .message || 'Export failed. Please try again.';
					showStatus( '#slos-export-status', 'error', errorMsg );
				},
				complete: function () {
					setButtonLoading( $btn, false );
				}
			}
		);
	}

	/**
	 * Export configuration to JSON (clipboard)
	 */
	function exportToJSON(name, description, options) {
		const $btn = $( '#slos-export-json-btn' );
		setButtonLoading( $btn, true );
		showStatus( '#slos-export-status', 'info', 'Generating JSON...' );

		$.ajax(
			{
				url: API_BASE + '/export',
				method: 'POST',
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce', NONCE );
				},
				contentType: 'application/json',
				data: JSON.stringify(
					{
						name: name,
						description: description,
						options: options,
						to_file: false
					}
				),
			success: function (response) {
				if (response.success && response.profile) {
					const json = JSON.stringify( response.profile, null, 2 );

					// Copy to clipboard
					copyToClipboard( json ).then(
						function () {
							showStatus(
								'#slos-export-status',
								'success',
								'Configuration copied to clipboard!'
							);
						}
					).catch(
						function () {
							// Fallback: show in textarea for manual copy
							showJSONModal( json );
							showStatus(
								'#slos-export-status',
								'info',
								'Configuration generated. Please copy from the modal.'
							);
						}
					);
				} else {
					showStatus(
						'#slos-export-status',
						'error',
						response.message || 'Export failed.'
					);
				}
			},
				error: function (xhr) {
					const errorMsg = xhr.responseJSON ? .message || 'Export failed. Please try again.';
					showStatus( '#slos-export-status', 'error', errorMsg );
				},
				complete: function () {
					setButtonLoading( $btn, false );
				}
			}
		);
	}

	/**
	 * Bind import form interactions
	 */
	function bindImportForm() {
		$( '#slos-validate-btn' ).on( 'click', validateConfig );
		$( '#slos-compare-btn' ).on( 'click', compareConfig );
		$( '#slos-import-form' ).on(
			'submit',
			function (e) {
				e.preventDefault();
				importConfig();
			}
		);

		// Enable import button when file or JSON is provided
		$( '#slos-import-file, #slos-import-json' ).on(
			'change input',
			function () {
				const hasFile = $( '#slos-import-file' )[0].files.length > 0;
				const hasJSON = $( '#slos-import-json' ).val().trim().length > 0;
				$( '#slos-import-btn' ).prop( 'disabled', ! hasFile && ! hasJSON );
			}
		);
	}

	/**
	 * Validate configuration
	 */
	function validateConfig() {
		const profile = getImportProfile();
		if ( ! profile) {
			showStatus( '#slos-import-status', 'error', 'Please provide a configuration file or JSON.' );
			return;
		}

		const $btn = $( '#slos-validate-btn' );
		setButtonLoading( $btn, true );
		showStatus( '#slos-import-status', 'info', 'Validating configuration...' );

		$.ajax(
			{
				url: API_BASE + '/validate',
				method: 'POST',
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce', NONCE );
				},
				contentType: 'application/json',
				data: JSON.stringify( { profile: profile } ),
				success: function (response) {
					if (response.valid) {
						showStatus( '#slos-import-status', 'success', 'Configuration is valid!' );
						showValidationResults( response );
					} else {
						showStatus( '#slos-import-status', 'warning', 'Configuration has validation issues.' );
						showValidationResults( response );
					}
				},
				error: function (xhr) {
					const errorMsg = xhr.responseJSON ? .message || 'Validation failed.';
					showStatus( '#slos-import-status', 'error', errorMsg );
				},
				complete: function () {
					setButtonLoading( $btn, false );
				}
			}
		);
	}

	/**
	 * Compare configuration with current
	 */
	function compareConfig() {
		const profile = getImportProfile();
		if ( ! profile) {
			showStatus( '#slos-import-status', 'error', 'Please provide a configuration file or JSON.' );
			return;
		}

		const $btn = $( '#slos-compare-btn' );
		setButtonLoading( $btn, true );
		showStatus( '#slos-import-status', 'info', 'Comparing configurations...' );

		$.ajax(
			{
				url: API_BASE + '/compare',
				method: 'POST',
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce', NONCE );
				},
				contentType: 'application/json',
				data: JSON.stringify( { profile: profile } ),
				success: function (response) {
					if (response.success && response.diff) {
						showStatus( '#slos-import-status', 'success', 'Comparison complete.' );
						showComparisonResults( response.diff, response.summary );
					} else {
						showStatus(
							'#slos-import-status',
							'error',
							response.message || 'Comparison failed.'
						);
					}
				},
				error: function (xhr) {
					const errorMsg = xhr.responseJSON ? .message || 'Comparison failed.';
					showStatus( '#slos-import-status', 'error', errorMsg );
				},
				complete: function () {
					setButtonLoading( $btn, false );
				}
			}
		);
	}

	/**
	 * Import configuration
	 */
	function importConfig() {
		const profile = getImportProfile();
		if ( ! profile) {
			showStatus( '#slos-import-status', 'error', 'Please provide a configuration file or JSON.' );
			return;
		}

		const merge  = $( '#slos-merge-option' ).is( ':checked' );
		const dryRun = $( '#slos-dry-run-option' ).is( ':checked' );

		const $btn = $( '#slos-import-btn' );
		setButtonLoading( $btn, true );
		showStatus(
			'#slos-import-status',
			'info',
			dryRun ? 'Running dry-run import...' : 'Importing configuration...'
		);

		$.ajax(
			{
				url: API_BASE + '/import',
				method: 'POST',
				beforeSend: function (xhr) {
					xhr.setRequestHeader( 'X-WP-Nonce', NONCE );
				},
				contentType: 'application/json',
				data: JSON.stringify(
					{
						profile: profile,
						merge: merge,
						dry_run: dryRun
					}
				),
			success: function (response) {
				if (response.success) {
					const isDryRun = response.results ? .dry_run || false;
					const message  = isDryRun ?
					'Dry-run complete. No changes were made.' :
					'Import successful!';

					showStatus( '#slos-import-status', 'success', message );
					showImportResults( response.results, response.summary );

					if ( ! isDryRun) {
						// Clear form on successful import
						setTimeout(
							function () {
								resetImportForm();
							},
							3000
						);
					}
				} else {
					showStatus(
						'#slos-import-status',
						'error',
						response.message || 'Import failed.'
					);
				}
			},
				error: function (xhr) {
					const errorMsg = xhr.responseJSON ? .message || 'Import failed.';
					showStatus( '#slos-import-status', 'error', errorMsg );
				},
				complete: function () {
					setButtonLoading( $btn, false );
				}
			}
		);
	}

	/**
	 * Get import profile from file or JSON textarea
	 */
	function getImportProfile() {
		const jsonText = $( '#slos-import-json' ).val().trim();

		if (jsonText) {
			try {
				return JSON.parse( jsonText );
			} catch (e) {
				showStatus( '#slos-import-status', 'error', 'Invalid JSON format.' );
				return null;
			}
		}

		// TODO: Handle file upload (requires reading file content)
		// For now, prioritize JSON textarea
		return null;
	}

	/**
	 * Bind file upload interactions
	 */
	function bindFileUpload() {
		const $dropzone  = $( '#slos-file-dropzone' );
		const $fileInput = $( '#slos-import-file' );
		const $browseBtn = $( '#slos-browse-btn' );
		const $removeBtn = $( '#slos-remove-file' );

		// Browse button
		$browseBtn.on(
			'click',
			function (e) {
				e.preventDefault();
				$fileInput.click();
			}
		);

		// File input change
		$fileInput.on(
			'change',
			function () {
				const file = this.files[0];
				if (file) {
					handleFile( file );
				}
			}
		);

		// Drag and drop
		$dropzone.on(
			'dragover',
			function (e) {
				e.preventDefault();
				e.stopPropagation();
				$( this ).addClass( 'dragover' );
			}
		);

		$dropzone.on(
			'dragleave',
			function (e) {
				e.preventDefault();
				e.stopPropagation();
				$( this ).removeClass( 'dragover' );
			}
		);

		$dropzone.on(
			'drop',
			function (e) {
				e.preventDefault();
				e.stopPropagation();
				$( this ).removeClass( 'dragover' );

				const files = e.originalEvent.dataTransfer.files;
				if (files.length > 0) {
					const file = files[0];
					if (file.type === 'application/json' || file.name.endsWith( '.json' )) {
						$fileInput[0].files = files;
						handleFile( file );
					} else {
						showStatus( '#slos-import-status', 'error', 'Please upload a JSON file.' );
					}
				}
			}
		);

		// Remove file
		$removeBtn.on(
			'click',
			function () {
				$fileInput.val( '' );
				$( '.slos-upload-placeholder' ).show();
				$( '#slos-file-info' ).hide();
				$( '#slos-import-btn' ).prop( 'disabled', true );
			}
		);
	}

	/**
	 * Handle uploaded file
	 */
	function handleFile(file) {
		$( '.slos-upload-placeholder' ).hide();
		$( '#slos-file-info' ).show();
		$( '#slos-file-info .filename' ).text( file.name );

		// Read file content and populate JSON textarea
		const reader  = new FileReader();
		reader.onload = function (e) {
			try {
				const json = JSON.parse( e.target.result );
				$( '#slos-import-json' ).val( JSON.stringify( json, null, 2 ) );
				$( '#slos-import-btn' ).prop( 'disabled', false );
			} catch (error) {
				showStatus( '#slos-import-status', 'error', 'Invalid JSON file.' );
				$( '#slos-import-file' ).val( '' );
				$( '.slos-upload-placeholder' ).show();
				$( '#slos-file-info' ).hide();
			}
		};
		reader.readAsText( file );
	}

	/**
	 * Bind export table actions
	 */
	function bindExportActions() {
		// Download export
		$( document ).on(
			'click',
			'.slos-download-export',
			function () {
				const filename       = $( this ).data( 'filename' );
				const downloadUrl    = API_BASE + '/download/' + encodeURIComponent( filename );
				window.location.href = downloadUrl;
			}
		);

		// Import from export
		$( document ).on(
			'click',
			'.slos-import-export',
			function () {
				const filename = $( this ).data( 'filename' );

				// Load export into import form
				$.ajax(
					{
						url: API_BASE + '/download/' + encodeURIComponent( filename ),
						method: 'GET',
						beforeSend: function (xhr) {
							xhr.setRequestHeader( 'X-WP-Nonce', NONCE );
						},
						success: function (profile) {
							$( '#slos-import-json' ).val( JSON.stringify( profile, null, 2 ) );
							$( '#slos-import-btn' ).prop( 'disabled', false );

							// Scroll to import section
							$( 'html, body' ).animate(
								{
									scrollTop: $( '#slos-import-form' ).offset().top - 100
								},
								500
							);
						},
						error: function () {
							alert( 'Failed to load export.' );
						}
					}
				);
			}
		);

		// Compare export
		$( document ).on(
			'click',
			'.slos-compare-export',
			function () {
				const filename = $( this ).data( 'filename' );

				$.ajax(
					{
						url: API_BASE + '/download/' + encodeURIComponent( filename ),
						method: 'GET',
						beforeSend: function (xhr) {
							xhr.setRequestHeader( 'X-WP-Nonce', NONCE );
						},
						success: function (profile) {
							$( '#slos-import-json' ).val( JSON.stringify( profile, null, 2 ) );
							compareConfig();
						},
						error: function () {
							alert( 'Failed to load export.' );
						}
					}
				);
			}
		);

		// Delete export
		$( document ).on(
			'click',
			'.slos-delete-export',
			function () {
				const filename = $( this ).data( 'filename' );
				const $row     = $( this ).closest( 'tr' );

				if ( ! confirm( 'Are you sure you want to delete this export?' )) {
					return;
				}

				$.ajax(
					{
						url: API_BASE + '/exports/' + encodeURIComponent( filename ),
						method: 'DELETE',
						beforeSend: function (xhr) {
							xhr.setRequestHeader( 'X-WP-Nonce', NONCE );
						},
						success: function (response) {
							if (response.success) {
								$row.fadeOut(
									300,
									function () {
										$( this ).remove();

										// Show empty state if no more exports
										if ($( '#slos-exports-table tbody tr' ).length === 0) {
											location.reload();
										}
									}
								);
							} else {
								alert( response.message || 'Failed to delete export.' );
							}
						},
						error: function () {
							alert( 'Failed to delete export.' );
						}
					}
				);
			}
		);

		// Refresh exports
		$( '#slos-refresh-exports' ).on( 'click', refreshExportsTable );
	}

	/**
	 * Bind checkbox select all/none
	 */
	function bindCheckboxActions() {
		$( '#slos-select-all' ).on(
			'click',
			function () {
				$( 'input[name="options[]"]' ).prop( 'checked', true );
			}
		);

		$( '#slos-select-none' ).on(
			'click',
			function () {
				$( 'input[name="options[]"]' ).prop( 'checked', false );
			}
		);
	}

	/**
	 * Show validation results
	 */
	function showValidationResults(response) {
		const $box     = $( '#slos-validation-results' );
		const $content = $box.find( '.slos-results-content' );

		let html = '';

		if (response.valid) {
			html += '<div class="slos-result-item slos-success">';
			html += '<span class="dashicons dashicons-yes-alt"></span>';
			html += '<span>Configuration is valid and ready to import.</span>';
			html += '</div>';
		}

		if (response.errors && response.errors.length > 0) {
			response.errors.forEach(
				function (error) {
					html += '<div class="slos-result-item slos-error">';
					html += '<span class="dashicons dashicons-dismiss"></span>';
					html += '<span>' + escapeHtml( error ) + '</span>';
					html += '</div>';
				}
			);
		}

		if (response.warnings && response.warnings.length > 0) {
			response.warnings.forEach(
				function (warning) {
					html += '<div class="slos-result-item slos-warning">';
					html += '<span class="dashicons dashicons-warning"></span>';
					html += '<span>' + escapeHtml( warning ) + '</span>';
					html += '</div>';
				}
			);
		}

		$content.html( html );
		$box.slideDown();
	}

	/**
	 * Show comparison results
	 */
	function showComparisonResults(diff, summary) {
		const $box     = $( '#slos-comparison-results' );
		const $content = $box.find( '.slos-results-content' );

		let html = '<div class="slos-comparison-summary">';
		html    += '<span class="slos-stat"><strong>' + (summary ? .added || 0) + '</strong> added</span>';
		html    += '<span class="slos-stat"><strong>' + (summary ? .removed || 0) + '</strong> removed</span>';
		html    += '<span class="slos-stat"><strong>' + (summary ? .changed || 0) + '</strong> changed</span>';
		html    += '<span class="slos-stat"><strong>' + (summary ? .unchanged || 0) + '</strong> unchanged</span>';
		html    += '</div>';

		// Added settings
		if (diff.added && Object.keys( diff.added ).length > 0) {
			html += '<h5>Added Settings</h5>';
			for (const key in diff.added) {
				html += '<div class="slos-result-item slos-added">';
				html += '<span class="dashicons dashicons-plus-alt"></span>';
				html += '<span>' + escapeHtml( key ) + '</span>';
				html += '</div>';
			}
		}

		// Removed settings
		if (diff.removed && Object.keys( diff.removed ).length > 0) {
			html += '<h5>Removed Settings</h5>';
			for (const key in diff.removed) {
				html += '<div class="slos-result-item slos-removed">';
				html += '<span class="dashicons dashicons-minus"></span>';
				html += '<span>' + escapeHtml( key ) + '</span>';
				html += '</div>';
			}
		}

		// Changed settings
		if (diff.changed && Object.keys( diff.changed ).length > 0) {
			html += '<h5>Changed Settings</h5>';
			for (const key in diff.changed) {
				const change = diff.changed[key];
				html        += '<div class="slos-result-item slos-changed">';
				html        += '<span class="dashicons dashicons-update"></span>';
				html        += '<div>';
				html        += '<strong>' + escapeHtml( key ) + '</strong><br>';
				html        += '<small>Old: ' + escapeHtml( JSON.stringify( change.old ).substring( 0, 100 ) ) + '</small><br>';
				html        += '<small>New: ' + escapeHtml( JSON.stringify( change.new ).substring( 0, 100 ) ) + '</small>';
				html        += '</div>';
				html        += '</div>';
			}
		}

		$content.html( html );
		$box.slideDown();
	}

	/**
	 * Show import results
	 */
	function showImportResults(results, summary) {
		const $box     = $( '#slos-import-results' );
		const $content = $box.find( '.slos-results-content' );

		let html = '<div class="slos-import-summary">';
		html    += '<span class="slos-stat slos-success"><strong>' + (summary ? .imported || 0) + '</strong> imported</span>';
		html    += '<span class="slos-stat slos-muted"><strong>' + (summary ? .skipped || 0) + '</strong> skipped</span>';
		html    += '<span class="slos-stat slos-error"><strong>' + (summary ? .errors || 0) + '</strong> errors</span>';
		html    += '<span class="slos-stat slos-warning"><strong>' + (summary ? .warnings || 0) + '</strong> warnings</span>';
		html    += '</div>';

		// Imported
		if (results.imported && Object.keys( results.imported ).length > 0) {
			html += '<h5>Successfully Imported</h5>';
			for (const key in results.imported) {
				html += '<div class="slos-result-item slos-success">';
				html += '<span class="dashicons dashicons-yes-alt"></span>';
				html += '<span>' + escapeHtml( key ) + ': ' + escapeHtml( results.imported[key] ) + '</span>';
				html += '</div>';
			}
		}

		// Errors
		if (results.errors && Object.keys( results.errors ).length > 0) {
			html += '<h5>Errors</h5>';
			for (const key in results.errors) {
				html += '<div class="slos-result-item slos-error">';
				html += '<span class="dashicons dashicons-dismiss"></span>';
				html += '<span>' + escapeHtml( key ) + ': ' + escapeHtml( results.errors[key] ) + '</span>';
				html += '</div>';
			}
		}

		// Warnings
		if (results.warnings && Object.keys( results.warnings ).length > 0) {
			html += '<h5>Warnings</h5>';
			for (const key in results.warnings) {
				const warnings = results.warnings[key];
				if (Array.isArray( warnings )) {
					warnings.forEach(
						function (warning) {
							html += '<div class="slos-result-item slos-warning">';
							html += '<span class="dashicons dashicons-warning"></span>';
							html += '<span>' + escapeHtml( key ) + ': ' + escapeHtml( warning ) + '</span>';
							html += '</div>';
						}
					);
				}
			}
		}

		// Skipped
		if (results.skipped && Object.keys( results.skipped ).length > 0) {
			html += '<h5>Skipped</h5>';
			for (const key in results.skipped) {
				html += '<div class="slos-result-item slos-muted">';
				html += '<span class="dashicons dashicons-minus"></span>';
				html += '<span>' + escapeHtml( key ) + ': ' + escapeHtml( results.skipped[key] ) + '</span>';
				html += '</div>';
			}
		}

		$content.html( html );
		$box.slideDown();
	}

	/**
	 * Refresh exports table
	 */
	function refreshExportsTable() {
		location.reload();
	}

	/**
	 * Reset import form
	 */
	function resetImportForm() {
		$( '#slos-import-form' )[0].reset();
		$( '#slos-import-file' ).val( '' );
		$( '.slos-upload-placeholder' ).show();
		$( '#slos-file-info' ).hide();
		$( '#slos-import-btn' ).prop( 'disabled', true );
		$( '#slos-validation-results, #slos-comparison-results, #slos-import-results' ).slideUp();
	}

	/**
	 * Show status message
	 */
	function showStatus(selector, type, message) {
		const $status = $( selector );
		$status.removeClass( 'slos-success slos-error slos-warning slos-info' );
		$status.addClass( 'slos-' + type );
		$status.html( message );
		$status.slideDown();

		if (type === 'success' || type === 'info') {
			setTimeout(
				function () {
					$status.slideUp();
				},
				5000
			);
		}
	}

	/**
	 * Set button loading state
	 */
	function setButtonLoading($btn, loading) {
		if (loading) {
			$btn.prop( 'disabled', true );
			$btn.data( 'original-html', $btn.html() );
			$btn.html( '<span class="dashicons dashicons-update slos-spin"></span>' );
		} else {
			$btn.prop( 'disabled', false );
			$btn.html( $btn.data( 'original-html' ) );
		}
	}

	/**
	 * Copy text to clipboard
	 */
	function copyToClipboard(text) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			return navigator.clipboard.writeText( text );
		}

		// Fallback for older browsers
		return new Promise(
			function (resolve, reject) {
				const $temp = $( '<textarea>' );
				$( 'body' ).append( $temp );
				$temp.val( text ).select();
				try {
					document.execCommand( 'copy' );
					$temp.remove();
					resolve();
				} catch (err) {
					$temp.remove();
					reject( err );
				}
			}
		);
	}

	/**
	 * Show JSON modal (fallback for clipboard failure)
	 */
	function showJSONModal(json) {
		const $modal = $(
			'<div class="slos-json-modal"><div class="slos-modal-content">' +
			'<h3>Configuration JSON</h3>' +
			'<textarea readonly>' + escapeHtml( json ) + '</textarea>' +
			'<button class="slos-btn slos-btn-primary slos-close-modal">Close</button>' +
			'</div></div>'
		);

		$( 'body' ).append( $modal );
		$modal.fadeIn();

		$modal.find( '.slos-close-modal, .slos-json-modal' ).on(
			'click',
			function (e) {
				if (e.target === this) {
					$modal.fadeOut(
						function () {
							$modal.remove();
						}
					);
				}
			}
		);

		$modal.find( 'textarea' ).select();
	}

	/**
	 * Escape HTML
	 */
	function escapeHtml(text) {
		if (typeof text !== 'string') {
			text = String( text );
		}
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return text.replace(
			/[&<>"']/g,
			function (m) {
				return map[m]; }
		);
	}

	// Initialize on document ready
	$( document ).ready( init );

})( jQuery );
