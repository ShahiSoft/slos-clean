/**
 * Document Generate JavaScript
 *
 * Handles all interactions for the Generate Documents tab.
 * Includes modal management, AJAX requests, and UI updates.
 *
 * @package
 * @subpackage Assets
 * @since      4.2.0
 */

(function ($) {
	'use strict';

	const SLOSDocGenerate = {
		/**
		 * Current document type being processed
		 */
		currentType: null,

		/**
		 * Current document ID (for regeneration)
		 */
		currentDocId: 0,

		/**
		 * Profile data cache
		 */
		profileData: null,

		/**
		 * Initialize
		 */
		init() {
			this.bindEvents();
		},

		/**
		 * Bind UI events
		 */
		bindEvents() {
			// Generate button click
			$(document).on(
				'click',
				'.slos-gen-btn-generate',
				this.handleGenerate.bind(this)
			);

			// Regenerate button click
			$(document).on(
				'click',
				'.slos-gen-btn-regenerate',
				this.handleRegenerate.bind(this)
			);

			// View document button (preview in modal)
			$(document).on(
				'click',
				'.slos-gen-btn-view',
				this.handleViewDocument.bind(this)
			);

			// View history button
			$(document).on(
				'click',
				'.slos-gen-btn-history',
				this.handleViewHistory.bind(this)
			);

			// Modal close
			$(document).on(
				'click',
				'.slos-gen-modal__close, #slos-gen-btn-modal-cancel',
				this.closeModal.bind(this)
			);
			$(document).on(
				'click',
				'.slos-gen-modal__backdrop',
				this.closeModal.bind(this)
			);

			// Preview button
			$(document).on(
				'click',
				'#slos-gen-btn-preview',
				this.handlePreview.bind(this)
			);

			// Generate button in modal
			$(document).on(
				'click',
				'#slos-gen-btn-generate',
				this.handleGenerateDocument.bind(this)
			);

			// Copy shortcode
			$(document).on(
				'click',
				'.slos-gen-copy-shortcode',
				this.handleCopyShortcode.bind(this)
			);

			// Field checkbox toggle (include/exclude)
			$(document).on(
				'change',
				'.slos-gen-field__checkbox',
				this.handleFieldToggle.bind(this)
			);

			// Inline edit buttons
			$(document).on(
				'click',
				'.slos-gen-field__btn--edit',
				this.handleInlineEdit.bind(this)
			);
			$(document).on(
				'click',
				'.slos-gen-field__btn--save',
				this.handleInlineSave.bind(this)
			);
			$(document).on(
				'click',
				'.slos-gen-field__btn--cancel',
				this.handleInlineCancel.bind(this)
			);

			// Add value button (for missing optional fields)
			$(document).on(
				'click',
				'.slos-gen-field__btn--add',
				this.handleAddValue.bind(this)
			);

			// Remove/exclude button
			$(document).on(
				'click',
				'.slos-gen-field__btn--remove',
				this.handleRemoveField.bind(this)
			);

			// History modal actions
			$(document).on(
				'click',
				'.slos-gen-timeline-item__btn--restore',
				this.handleVersionRestore.bind(this)
			);
			$(document).on(
				'click',
				'.slos-gen-timeline-item__btn--compare',
				this.handleVersionCompare.bind(this)
			);

			// Bulk actions
			$(document).on(
				'click',
				'.slos-gen-btn-regenerate-all',
				this.handleRegenerateAll.bind(this)
			);
			$(document).on(
				'click',
				'.slos-gen-btn-export-all',
				this.handleExportAll.bind(this)
			);
			$(document).on(
				'click',
				'.slos-gen-btn-clear-drafts',
				this.handleClearDrafts.bind(this)
			);

			// Download button
			$(document).on(
				'click',
				'.slos-gen-btn-download',
				this.handleDownload.bind(this)
			);

			// ESC key to close modal
			$(document).on('keydown', this.handleKeydown.bind(this));

			// Enter key to save inline edit
			$(document).on(
				'keydown',
				'.slos-gen-field__input',
				this.handleInputKeydown.bind(this)
			);
		},

		/**
		 * Handle generate button click
		 *
		 * @param e
		 */
		handleGenerate(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const type = $btn.data('type');

			if (!type) return;

			this.currentType = type;
			this.currentDocId = 0;
			this.openModal(type);
		},

		/**
		 * Handle regenerate button click
		 *
		 * @param e
		 */
		handleRegenerate(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const type = $btn.data('type');
			const docId = $btn.data('doc-id');

			if (!type) return;

			// Confirm regeneration
			if (!confirm(SLOSDocGen.i18n.confirm_regenerate)) {
				return;
			}

			this.currentType = type;
			this.currentDocId = docId || 0;
			this.openModal(type);
		},

		/**
		 * Handle View Document button - preview existing document in modal
		 *
		 * @param e
		 */
		handleViewDocument(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const docId = $btn.data('doc-id');
			const docType = $btn.data('type');

			if (!docId) return;

			// Get document title from card
			const $card = $btn.closest('.slos-gen-card');
			const docTitle = $card.find('.slos-gen-card__title').text();

			// Show loading state
			$btn.prop('disabled', true).text('Loading...');

			// Fetch and display document content
			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_gen_view_document',
					nonce: $('#slos-gen-nonce').val() || SLOSDocGen.nonce,
					doc_id: docId,
				},
				timeout: 15000,
				success: (response) => {
					if (response.success && response.data.html) {
						this.showDocumentPreviewModal(
							response.data.html,
							docTitle,
							response.data.word_count
						);
					} else {
						this.showError(
							response.data?.message || 'Failed to load document'
						);
					}
				},
				error: (xhr, status, error) => {
					if (status === 'timeout') {
						this.showError('Request timed out. Please try again.');
					} else {
						this.showError(
							'Failed to load document: ' +
								(xhr.responseJSON?.data?.message || error)
						);
					}
				},
				complete: () => {
					$btn.prop('disabled', false).html('👁 View');
				},
			});
		},

		/**
		 * Show document preview modal for View button
		 *
		 * @param html
		 * @param title
		 * @param wordCount
		 */
		showDocumentPreviewModal(html, title, wordCount) {
			// Create/reuse view modal (separate from generation preview modal)
			let $viewModal = $('#slos-gen-view-modal');
			if ($viewModal.length === 0) {
				$viewModal = $(`
					<div id="slos-gen-view-modal" class="slos-gen-modal">
						<div class="slos-gen-modal__backdrop"></div>
						<div class="slos-gen-modal__container slos-gen-modal__container--wide">
							<div class="slos-gen-modal__content">
								<div class="slos-gen-modal__header">
									<h2 class="slos-gen-modal__title" id="slos-gen-view-title">Document Preview</h2>
									<button type="button" class="slos-gen-modal__close" aria-label="Close">
										<span class="dashicons dashicons-no-alt"></span>
									</button>
								</div>
								<div class="slos-gen-modal__body slos-gen-document-preview" id="slos-gen-view-content">
								</div>
								<div class="slos-gen-modal__footer">
									<div class="slos-gen-modal__footer-left">
										<span id="slos-gen-view-stats" class="slos-gen-preview-stats"></span>
									</div>
									<div class="slos-gen-modal__footer-right">
										<button type="button" class="button" id="slos-gen-view-close">Close</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				`);
				$('body').append($viewModal);

				// Bind close events
				$viewModal
					.find(
						'.slos-gen-modal__close, #slos-gen-view-close, .slos-gen-modal__backdrop'
					)
					.on('click', () => {
						$viewModal.fadeOut(200);
					});
			}

			// Set content
			$('#slos-gen-view-title').text(title || 'Document Preview');
			$('#slos-gen-view-content').html(html);
			$('#slos-gen-view-stats').text(
				wordCount ? `Word count: ${wordCount}` : ''
			);

			// Show modal
			$viewModal.fadeIn(200);
		},

		/**
		 * Handle Add value button for empty optional fields
		 *
		 * @param e
		 */
		handleAddValue(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const $field = $btn.closest('.slos-gen-field');
			const $input = $field.find('.slos-gen-field__input');
			const $placeholder = $field.find('.slos-gen-field__placeholder');
			const $actions = $field.find('.slos-gen-field__actions');

			// Switch to edit mode
			$placeholder.hide();
			$input.show().focus();
			$field.addClass('slos-gen-field--editing');

			// Toggle action buttons
			$actions.find('.slos-gen-field__btn--add').hide();
			$actions
				.find(
					'.slos-gen-field__btn--save, .slos-gen-field__btn--cancel'
				)
				.show();
		},

		/**
		 * Handle Remove/exclude field button
		 *
		 * @param e
		 */
		handleRemoveField(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const $field = $btn.closest('.slos-gen-field');
			const $checkbox = $field.find('.slos-gen-field__checkbox');

			// Uncheck the checkbox to exclude the field
			$checkbox.prop('checked', false);
			$field.addClass('slos-gen-field--excluded');

			// Update summary
			this.updateSummary();

			this.showNotice('info', 'Field excluded from this generation');
		},

		/**
		 * Handle input keydown (Enter to save, Escape to cancel)
		 *
		 * @param e
		 */
		handleInputKeydown(e) {
			if (e.keyCode === 13) {
				// Enter
				e.preventDefault();
				$(e.currentTarget)
					.closest('.slos-gen-field')
					.find('.slos-gen-field__btn--save')
					.click();
			} else if (e.keyCode === 27) {
				// Escape
				e.preventDefault();
				$(e.currentTarget)
					.closest('.slos-gen-field')
					.find('.slos-gen-field__btn--cancel')
					.click();
			}
		},

		/**
		 * Open review modal
		 *
		 * @param type
		 */
		openModal(type) {
			const $modal = $('#slos-gen-modal');
			const $card = $('.slos-gen-card[data-type="' + type + '"]');

			// Set document info
			const icon = $card.find('.slos-gen-card__icon').text();
			const title = $card.find('.slos-gen-card__title').text();
			const description = $card
				.find('.slos-gen-card__description')
				.text();

			$('#slos-gen-doc-icon').text(icon);
			$('#slos-gen-doc-title').text(title);
			$('#slos-gen-doc-description').text(description);

			// Show modal
			$modal.fadeIn(200);
			$('body').addClass('slos-modal-open');

			// Load profile data
			this.loadProfileData();
		},

		/**
		 * Close modal
		 */
		closeModal() {
			const $modal = $('#slos-gen-modal');
			$modal.fadeOut(200);
			$('body').removeClass('slos-modal-open');

			// Reset modal state
			setTimeout(() => {
				$('#slos-gen-modal-loading').show();
				$('#slos-gen-modal-error').hide();
				$('#slos-gen-field-groups').hide();
				$('#slos-gen-btn-preview, #slos-gen-btn-generate').prop(
					'disabled',
					true
				);
			}, 200);
		},

		/**
		 * Load profile data via AJAX
		 */
		loadProfileData() {
			$('#slos-gen-modal-loading').show();
			$('#slos-gen-modal-error').hide();
			$('#slos-gen-field-groups').hide();

			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_gen_get_context',
					nonce: $('#slos-gen-nonce').val(),
					doc_type: this.currentType,
				},
				timeout: 10000, // 10 second timeout
				success: (response) => {
					if (response.success) {
						this.profileData = response.data;
						this.renderFields();
					} else {
						// Handle validation errors (missing mandatory fields)
						if (response.data && response.data.missing_fields) {
							this.showMissingFieldsError(
								response.data.missing_fields
							);
						} else {
							this.showModalError(
								response.data?.message || SLOSDocGen.i18n.error
							);
						}
					}
				},
				error: (xhr, status, error) => {
					console.error('AJAX error:', error);

					if (status === 'timeout') {
						this.showModalError(
							'Request timed out. Please try again.'
						);
					} else if (xhr.status === 0) {
						this.showModalError(
							'Network error. Check your connection.'
						);
					} else {
						this.showModalError(
							'Server error: ' +
								(xhr.responseJSON?.data?.message || error)
						);
					}
				},
			});
		},

		/**
		 * Show missing mandatory fields error with profile links
		 *
		 * @param missingFields
		 */
		showMissingFieldsError(missingFields) {
			const $container = $('#slos-gen-modal-error-msg');

			let html = '<div class="slos-gen-error-detailed">';
			html += '<h3>⚠️ Required Fields Missing</h3>';
			html +=
				'<p>Complete these required fields before generating documents:</p>';
			html += '<ul class="slos-gen-missing-fields-list">';

			missingFields.forEach((field) => {
				const profileUrl =
					SLOSDocGen.profileUrl + '&step=' + (field.step || 1);
				html += '<li>';
				html += '<strong>' + field.label + '</strong>';
				if (field.step) {
					html +=
						' <span class="slos-gen-field-step">(Step ' +
						field.step +
						')</span> ';
				}
				html +=
					'<a href="' +
					profileUrl +
					'" class="button button-small button-primary">Complete Now →</a>';
				html += '</li>';
			});

			html += '</ul>';
			html +=
				'<button type="button" class="button button-secondary" onclick="jQuery(\'#slos-gen-modal\').fadeOut(200)">Close</button>';
			html += '</div>';

			$container.html(html);
			$('#slos-gen-modal-loading').hide();
			$('#slos-gen-modal-error').show();
		},

		/**
		 * Show error in modal (for modal-specific errors)
		 *
		 * @param message
		 */
		showModalError(message) {
			$('#slos-gen-modal-loading').hide();
			$('#slos-gen-modal-error-msg').html(
				'<span class="dashicons dashicons-warning"></span> ' + message
			);
			$('#slos-gen-modal-error').show();
		},

		/**
		 * Render field groups
		 */
		renderFields() {
			const data = this.profileData;
			const $container = $('#slos-gen-fields-container');

			$container.empty();

			// Profile completion banner
			if (data.validation.completion_percentage < 100) {
				const missingCount = data.validation.missing_fields.length;
				$('#slos-gen-profile-banner-text').html(
					`<strong>${missingCount} required field${
						missingCount !== 1 ? 's' : ''
					} missing.</strong> Complete your profile for accurate document generation.`
				);
				$('#slos-gen-profile-banner')
					.addClass('slos-gen-alert--warning')
					.show();
			} else {
				$('#slos-gen-profile-banner').hide();
			}

			// Group fields by wizard step
			const groups = this.groupFieldsByStep(data.fields);

			// Render each group
			Object.keys(groups).forEach((groupName) => {
				const fields = groups[groupName];
				const $group = this.renderFieldGroup(
					groupName,
					fields,
					data.validation.missing_fields
				);
				$container.append($group);
			});

			// Update summary
			this.updateSummary();

			// Show field groups
			$('#slos-gen-modal-loading').hide();
			$('#slos-gen-field-groups').show();

			// Enable buttons
			$('#slos-gen-btn-preview, #slos-gen-btn-generate').prop(
				'disabled',
				false
			);
		},

		/**
		 * Group fields by wizard step
		 *
		 * @param fields
		 */
		groupFieldsByStep(fields) {
			const stepGroups = {
				'Company Information': [],
				'Contact & Location': [],
				'Legal & Compliance': [],
				'Data Collection': [],
				'User Rights': [],
				'Security & Cookies': [],
				'Business Operations': [],
				'Additional Information': [],
			};

			// Map fields to groups (simplified - should match wizard steps)
			Object.keys(fields).forEach((key) => {
				const field = fields[key];

				// Determine group based on field key
				if (
					[
						'company_name',
						'company_type',
						'industry',
						'description',
					].includes(key)
				) {
					stepGroups['Company Information'].push({ key, ...field });
				} else if (
					[
						'email',
						'phone',
						'address',
						'city',
						'country',
						'postal_code',
					].includes(key)
				) {
					stepGroups['Contact & Location'].push({ key, ...field });
				} else if (
					[
						'jurisdiction',
						'registration_number',
						'dpo_name',
						'dpo_email',
					].includes(key)
				) {
					stepGroups['Legal & Compliance'].push({ key, ...field });
				} else if (
					[
						'data_collected',
						'collection_methods',
						'data_purposes',
					].includes(key)
				) {
					stepGroups['Data Collection'].push({ key, ...field });
				} else if (['user_rights', 'data_retention'].includes(key)) {
					stepGroups['User Rights'].push({ key, ...field });
				} else if (
					[
						'security_measures',
						'cookie_types',
						'third_party_services',
					].includes(key)
				) {
					stepGroups['Security & Cookies'].push({ key, ...field });
				} else if (
					[
						'payment_methods',
						'refund_policy',
						'dispute_resolution',
					].includes(key)
				) {
					stepGroups['Business Operations'].push({ key, ...field });
				} else {
					stepGroups['Additional Information'].push({
						key,
						...field,
					});
				}
			});

			// Remove empty groups
			Object.keys(stepGroups).forEach((key) => {
				if (stepGroups[key].length === 0) {
					delete stepGroups[key];
				}
			});

			return stepGroups;
		},

		/**
		 * Render field group
		 *
		 * @param groupName
		 * @param fields
		 * @param missingFields
		 */
		renderFieldGroup(groupName, fields, missingFields) {
			const $group = $('<div class="slos-gen-field-group">');

			// Group title
			const $title = $('<h4 class="slos-gen-field-group__title">').text(
				groupName
			);
			$group.append($title);

			// Render fields
			fields.forEach((field) => {
				const $field = this.renderField(field, missingFields);
				$group.append($field);
			});

			return $group;
		},

		/**
		 * Render individual field with inline edit and checkbox toggle
		 * Per Tab Plan Section 2: Pre-Generation Data Review Modal
		 *
		 * @param field
		 * @param missingFields
		 */
		renderField(field, missingFields) {
			const isMissing = missingFields.includes(field.key);
			const isMandatory = field.mandatory || false;
			const value = field.value || '';
			const hasValue =
				value !== '' && value !== null && value !== undefined;

			const classes = ['slos-gen-field'];
			if (isMissing) classes.push('slos-gen-field--missing');
			if (isMandatory) classes.push('slos-gen-field--mandatory');
			if (!hasValue) classes.push('slos-gen-field--empty');

			const $field = $('<div>')
				.addClass(classes.join(' '))
				.attr('data-field', field.key);

			// Checkbox toggle for include/exclude (Per Tab Plan: ☑/☐ Toggle)
			const checkboxId = 'slos-field-check-' + field.key;
			const $checkboxWrapper = $(
				'<div class="slos-gen-field__checkbox-wrapper">'
			);
			const $checkbox = $(
				'<input type="checkbox" class="slos-gen-field__checkbox">'
			)
				.attr('id', checkboxId)
				.prop('checked', hasValue && !isMissing)
				.prop('disabled', isMandatory && isMissing); // Disable if mandatory and missing

			// Visual checkbox label for better UX
			const $checkboxLabel = $(
				'<label class="slos-gen-field__checkbox-label">'
			)
				.attr('for', checkboxId)
				.attr(
					'title',
					hasValue
						? 'Click to exclude this field'
						: 'Click to include this field'
				);

			$checkboxWrapper.append($checkbox, $checkboxLabel);
			$field.append($checkboxWrapper);

			// Content wrapper
			const $content = $('<div class="slos-gen-field__content">');

			// Label with badges
			const $labelRow = $('<div class="slos-gen-field__label-row">');
			const $label = $('<span class="slos-gen-field__label-text">').text(
				field.label || field.key
			);
			$labelRow.append($label);

			// Badges
			const $badges = $('<div class="slos-gen-field__badges">');
			if (isMandatory) {
				$badges.append(
					$(
						'<span class="slos-gen-field__badge slos-gen-field__badge--mandatory">'
					).text('Required')
				);
			}
			if (isMissing && isMandatory) {
				$badges.append(
					$(
						'<span class="slos-gen-field__badge slos-gen-field__badge--missing">'
					).text('Missing')
				);
			}
			if (!hasValue && !isMandatory) {
				$badges.append(
					$(
						'<span class="slos-gen-field__badge slos-gen-field__badge--optional">'
					).text('Optional')
				);
			}
			$labelRow.append($badges);
			$content.append($labelRow);

			// Value display/edit area
			const $valueWrapper = $(
				'<div class="slos-gen-field__value-wrapper">'
			);

			if (hasValue) {
				// Has value - show with edit capability
				const $value = $('<div class="slos-gen-field__value">')
					.text(this.formatFieldValue(value))
					.attr('data-original', value);

				// Hidden input for inline editing
				const $input = $(
					'<input type="text" class="slos-gen-field__input">'
				)
					.val(value)
					.attr('data-field', field.key)
					.hide();

				$valueWrapper.append($value, $input);
			} else {
				// No value - show placeholder with Add button
				const $placeholder = $(
					'<span class="slos-gen-field__placeholder">'
				).text('[Not provided]');

				// Hidden input for adding value
				const $input = $(
					'<input type="text" class="slos-gen-field__input slos-gen-field__input--add">'
				)
					.attr('placeholder', 'Enter ' + (field.label || field.key))
					.attr('data-field', field.key)
					.hide();

				$valueWrapper.append($placeholder, $input);
			}

			$content.append($valueWrapper);

			// Actions (Edit/Add/Save/Cancel buttons)
			const $actions = $('<div class="slos-gen-field__actions">');

			if (hasValue) {
				// Edit and Remove buttons for fields with values
				$actions.append(
					$(
						'<button type="button" class="slos-gen-field__btn slos-gen-field__btn--edit" title="Edit value">'
					).html('✏️ <span>Edit</span>'),
					$(
						'<button type="button" class="slos-gen-field__btn slos-gen-field__btn--remove" title="Exclude field">'
					).html('🗑️')
				);
			} else if (!isMandatory) {
				// Add button for optional empty fields
				$actions.append(
					$(
						'<button type="button" class="slos-gen-field__btn slos-gen-field__btn--add" title="Add value">'
					).html('➕ <span>Add</span>')
				);
			}

			// Save/Cancel buttons (hidden by default, shown during editing)
			$actions.append(
				$(
					'<button type="button" class="slos-gen-field__btn slos-gen-field__btn--save" title="Save changes" style="display:none;">'
				).html('✓ <span>Save</span>'),
				$(
					'<button type="button" class="slos-gen-field__btn slos-gen-field__btn--cancel" title="Cancel editing" style="display:none;">'
				).html('✕ <span>Cancel</span>')
			);

			$content.append($actions);
			$field.append($content);

			return $field;
		},

		/**
		 * Format field value for display
		 *
		 * @param value
		 */
		formatFieldValue(value) {
			if (Array.isArray(value)) {
				return value.join(', ');
			}
			if (typeof value === 'object') {
				return JSON.stringify(value);
			}
			if (typeof value === 'string' && value.length > 100) {
				return value.substring(0, 100) + '...';
			}
			return value;
		},

		/**
		 * Handle field checkbox toggle - include/exclude field from generation
		 *
		 * @param e
		 */
		handleFieldToggle(e) {
			const $checkbox = $(e.currentTarget);
			const $field = $checkbox.closest('.slos-gen-field');
			const isChecked = $checkbox.is(':checked');

			// Update field visual state
			if (isChecked) {
				$field.removeClass('slos-gen-field--excluded');
			} else {
				$field.addClass('slos-gen-field--excluded');
			}

			// Update summary stats
			this.updateSummary();
		},

		/**
		 * Update summary stats with excluded count
		 */
		updateSummary() {
			const totalFields = $('.slos-gen-field').length;
			const includedFields = $(
				'.slos-gen-field__checkbox:checked'
			).length;
			const excludedFields = $(
				'.slos-gen-field:not(.slos-gen-field--missing) .slos-gen-field__checkbox:not(:checked)'
			).length;
			const missingFields = $(
				'.slos-gen-field--missing.slos-gen-field--mandatory'
			).length;

			$('#slos-gen-total-fields').text(totalFields);
			$('#slos-gen-included-fields').text(includedFields);
			$('#slos-gen-excluded-fields').text(excludedFields);
			$('#slos-gen-missing-fields').text(missingFields);

			// Disable generate button if mandatory fields are missing
			const hasMissingMandatory = missingFields > 0;
			$('#slos-gen-btn-generate').prop('disabled', hasMissingMandatory);
			$('#slos-gen-btn-preview').prop('disabled', false); // Preview always available
		},

		/**
		 * Handle preview button
		 *
		 * @param e
		 */
		handlePreview(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);
			const originalText = $btn.text();

			// Disable button and show loading
			$btn.prop('disabled', true).text(
				SLOSDocGen.i18n.loading || '⏳ Generating Preview...'
			);

			// Show progress bar if exists
			$('#slos-gen-preview-progress').show();

			// Get included fields
			const includedFields = [];
			$('.slos-gen-field__checkbox:checked').each(function () {
				includedFields.push(
					$(this).closest('.slos-gen-field').data('field')
				);
			});

			// Get field overrides if any
			const overrides = this.collectFieldOverrides();

			// Set timeout for preview (20 seconds)
			const timeoutId = setTimeout(() => {
				this.showNotice(
					'error',
					'Preview generation timed out. Try again or reduce profile complexity.'
				);
				$btn.prop('disabled', false).text(originalText);
				$('#slos-gen-preview-progress').hide();
			}, 20000);

			// AJAX request for preview
			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_gen_preview',
					nonce:
						$('#slos-gen-nonce').val() ||
						SLOSDocGen.nonce ||
						SLOSDocGen.nonces?.modal,
					doc_type: this.currentType,
					included_fields: includedFields,
					overrides: JSON.stringify(overrides),
				},
				timeout: 20000,
				success: (response) => {
					clearTimeout(timeoutId);
					$('#slos-gen-preview-progress').hide();

					if (response.success) {
						// Show preview modal or open in new window
						this.showPreviewModal(
							response.data.html,
							response.data.word_count
						);
					} else {
						this.showError(
							response.data?.message ||
								'Preview generation failed'
						);
					}
				},
				error: (xhr, status, error) => {
					clearTimeout(timeoutId);
					$('#slos-gen-preview-progress').hide();
					console.error('Preview error:', error);

					if (status === 'timeout') {
						this.showError(
							'Preview request timed out. Try reducing profile data.'
						);
					} else if (xhr.status === 0) {
						this.showError('Network error. Check your connection.');
					} else {
						this.showError(
							'Preview failed: ' +
								(xhr.responseJSON?.data?.message || error)
						);
					}
				},
				complete: () => {
					$btn.prop('disabled', false).text(originalText);
				},
			});
		},

		/**
		 * Show preview modal
		 *
		 * @param html
		 * @param wordCount
		 */
		showPreviewModal(html, wordCount) {
			// Create preview modal if it doesn't exist
			let $previewModal = $('#slos-gen-preview-modal');
			if ($previewModal.length === 0) {
				$previewModal = $(`
					<div id="slos-gen-preview-modal" class="slos-gen-modal">
						<div class="slos-gen-modal__backdrop"></div>
						<div class="slos-gen-modal__container slos-gen-modal__container--wide">
							<div class="slos-gen-modal__content">
								<div class="slos-gen-modal__header">
									<h2 class="slos-gen-modal__title">Document Preview</h2>
									<button type="button" class="slos-gen-modal__close">✕</button>
								</div>
								<div class="slos-gen-modal__body" id="slos-gen-preview-content">
								</div>
								<div class="slos-gen-modal__footer">
									<div class="slos-gen-modal__footer-left">
										<span id="slos-gen-preview-stats"></span>
									</div>
									<div class="slos-gen-modal__footer-right">
										<button type="button" class="button button-secondary" id="slos-gen-preview-close">Close</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				`);
				$('body').append($previewModal);

				// Bind close events
				$previewModal
					.find(
						'.slos-gen-modal__close, #slos-gen-preview-close, .slos-gen-modal__backdrop'
					)
					.on('click', () => {
						$previewModal.fadeOut(200);
					});
			}

			// Set content and stats
			$('#slos-gen-preview-content').html(html);
			$('#slos-gen-preview-stats').text(
				`Word count: ${wordCount || 'N/A'}`
			);

			// Show preview modal
			$previewModal.fadeIn(200);
		},

		/**
		 * Collect field overrides from inline edits
		 * Now uses input fields instead of contenteditable
		 */
		collectFieldOverrides() {
			const overrides = {};

			// Collect from modified input fields
			$(
				'.slos-gen-field__input--modified, .slos-gen-field--modified .slos-gen-field__input'
			).each(function () {
				const $input = $(this);
				const $field = $input.closest('.slos-gen-field');
				const fieldKey = $field.data('field');
				const value = $input.val().trim();

				// Only include if field is checked (included) and has value
				const isIncluded = $field
					.find('.slos-gen-field__checkbox')
					.is(':checked');
				if (value && isIncluded) {
					overrides[fieldKey] = value;
				}
			});

			// Also collect from any fields that were edited and saved (check display value vs input value)
			$('.slos-gen-field--modified').each(function () {
				const $field = $(this);
				const fieldKey = $field.data('field');
				const $input = $field.find('.slos-gen-field__input');
				const value = $input.val().trim();

				const isIncluded = $field
					.find('.slos-gen-field__checkbox')
					.is(':checked');
				if (value && isIncluded && !overrides[fieldKey]) {
					overrides[fieldKey] = value;
				}
			});

			return overrides;
		},

		/**
		 * Handle generate document
		 *
		 * @param e
		 */
		handleGenerateDocument(e) {
			e.preventDefault();

			const $btn = $('#slos-gen-btn-generate');

			// Prevent double-submit
			if ($btn.hasClass('submitting')) {
				return false;
			}

			const originalText = $btn.text();

			// Disable all modal buttons
			$('.slos-gen-modal button').prop('disabled', true);
			$btn.addClass('submitting').text(
				SLOSDocGen.i18n.generating || '⏳ Generating...'
			);

			// Show progress indicator
			$('#slos-gen-progress').show();

			// Get included fields
			const includedFields = [];
			$('.slos-gen-field__checkbox:checked').each(function () {
				includedFields.push(
					$(this).closest('.slos-gen-field').data('field')
				);
			});

			// Get field overrides
			const overrides = this.collectFieldOverrides();

			// Get change reason if provided
			const changeReason =
				$('#slos-gen-change-reason').val() || 'Generated from profile';

			// AJAX request for generation
			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_gen_generate',
					nonce:
						$('#slos-gen-nonce').val() ||
						SLOSDocGen.nonce ||
						SLOSDocGen.nonces?.modal,
					doc_type: this.currentType,
					doc_id: this.currentDocId,
					included_fields: includedFields,
					overrides: JSON.stringify(overrides),
					change_reason: changeReason,
				},
				timeout: 30000, // 30 second timeout
				success: (response) => {
					$('#slos-gen-progress').hide();

					if (response.success) {
						this.showSuccess(
							response.data?.message ||
								SLOSDocGen.i18n.success ||
								'Document generated successfully!'
						);
						this.closeModal();

						// Redirect to edit page if provided, otherwise reload
						if (response.data && response.data.edit_url) {
							setTimeout(() => {
								window.location.href = response.data.edit_url;
							}, 1000);
						} else {
							setTimeout(() => {
								window.location.reload();
							}, 1000);
						}
					} else {
						this.showError(
							response.data?.message || 'Generation failed'
						);
						$('.slos-gen-modal button').prop('disabled', false);
						$btn.removeClass('submitting').text(originalText);
					}
				},
				error: (xhr, status, error) => {
					$('#slos-gen-progress').hide();
					$('.slos-gen-modal button').prop('disabled', false);
					$btn.removeClass('submitting').text(originalText);

					console.error('Generation error:', error);

					if (status === 'timeout') {
						this.showError(
							'Generation timed out. Document may be large. Check Documents list.'
						);
					} else if (xhr.status === 0) {
						this.showError(
							'Network error. Check your connection and try again.'
						);
					} else {
						this.showError(
							'Generation failed: ' +
								(xhr.responseJSON?.data?.message || error)
						);
					}
				},
			});
		},

		/**
		 * Handle copy shortcode
		 *
		 * @param e
		 */
		handleCopyShortcode(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const shortcode = $btn.data('shortcode');

			// Copy to clipboard
			if (navigator.clipboard) {
				navigator.clipboard.writeText(shortcode).then(() => {
					this.showSuccess('Shortcode copied to clipboard!');
					$btn.text('✓');
					setTimeout(() => $btn.text('📋'), 2000);
				});
			} else {
				// Fallback for older browsers
				const $temp = $('<textarea>');
				$('body').append($temp);
				$temp.val(shortcode).select();
				document.execCommand('copy');
				$temp.remove();
				this.showSuccess('Shortcode copied to clipboard!');
			}
		},

		/**
		 * Handle regenerate all
		 *
		 * @param e
		 */
		handleRegenerateAll(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);

			if (
				!confirm(
					'Regenerate all existing documents? This will create new draft versions using your current profile data.'
				)
			) {
				return;
			}

			const originalHtml = $btn.html();
			$btn.prop('disabled', true).html(
				'<span class="slos-gen-action-link__icon">⏳</span><span class="slos-gen-action-link__text">Regenerating...</span>'
			);

			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_hub_bulk_action',
					nonce: SLOSDocGen.hubNonce || $('#slos-gen-nonce').val(),
					bulk_action: 'regenerate_outdated',
				},
				timeout: 60000,
				success: (response) => {
					if (response.success) {
						this.showSuccess(
							response.data?.message ||
								'Documents regenerated successfully'
						);
						if (response.data?.refresh) {
							setTimeout(() => window.location.reload(), 1500);
						}
					} else {
						this.showError(
							response.data?.message || 'Regeneration failed'
						);
					}
				},
				error: (xhr, status, error) => {
					if (status === 'timeout') {
						this.showError(
							'Request timed out. Some documents may have been regenerated.'
						);
					} else {
						this.showError(
							'Regeneration failed: ' +
								(xhr.responseJSON?.data?.message || error)
						);
					}
				},
				complete: () => {
					$btn.prop('disabled', false).html(originalHtml);
				},
			});
		},

		/**
		 * Handle export all
		 *
		 * @param e
		 */
		handleExportAll(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);
			const originalHtml = $btn.html();
			$btn.prop('disabled', true).html(
				'<span class="slos-gen-action-link__icon">⏳</span><span class="slos-gen-action-link__text">Exporting...</span>'
			);

			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_hub_bulk_action',
					nonce: SLOSDocGen.hubNonce || $('#slos-gen-nonce').val(),
					bulk_action: 'export_all',
				},
				timeout: 60000,
				success: (response) => {
					if (response.success) {
						this.showSuccess(
							response.data?.message || 'Export ready'
						);

						// Trigger download if URL provided
						if (response.data?.download_url) {
							window.location.href = response.data.download_url;
						}
					} else {
						this.showError(
							response.data?.message || 'Export failed'
						);
					}
				},
				error: (xhr, status, error) => {
					if (status === 'timeout') {
						this.showError(
							'Export request timed out. Please try again.'
						);
					} else {
						this.showError(
							'Export failed: ' +
								(xhr.responseJSON?.data?.message || error)
						);
					}
				},
				complete: () => {
					$btn.prop('disabled', false).html(originalHtml);
				},
			});
		},

		/**
		 * Handle clear drafts
		 *
		 * @param e
		 */
		handleClearDrafts(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);

			if (
				!confirm(
					'Delete all draft documents? This action cannot be undone.'
				)
			) {
				return;
			}

			const originalHtml = $btn.html();
			$btn.prop('disabled', true).html(
				'<span class="slos-gen-action-link__icon">⏳</span><span class="slos-gen-action-link__text">Clearing...</span>'
			);

			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_gen_clear_drafts',
					nonce: $('#slos-gen-nonce').val(),
				},
				timeout: 30000,
				success: (response) => {
					if (response.success) {
						this.showSuccess(
							response.data?.message || 'Drafts cleared'
						);
						setTimeout(() => window.location.reload(), 1500);
					} else {
						this.showError(
							response.data?.message || 'Failed to clear drafts'
						);
					}
				},
				error: (xhr, status, error) => {
					this.showError(
						'Failed to clear drafts: ' +
							(xhr.responseJSON?.data?.message || error)
					);
				},
				complete: () => {
					$btn.prop('disabled', false).html(originalHtml);
				},
			});
		},

		/**
		 * Handle download button click
		 *
		 * @param e
		 */
		handleDownload(e) {
			e.preventDefault();

			const $btn = $(e.currentTarget);
			const docId = $btn.data('doc-id');

			if (!docId) return;

			const originalText = $btn.text();
			$btn.prop('disabled', true).text('⏳');

			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_export_document',
					nonce: SLOSDocGen.hubNonce || $('#slos-gen-nonce').val(),
					doc_id: docId,
					format: 'html',
				},
				timeout: 30000,
				success: (response) => {
					if (response.success && response.data?.download_url) {
						// Trigger download
						window.location.href = response.data.download_url;
						this.showSuccess('Download started');
					} else {
						this.showError(
							response.data?.message || 'Download failed'
						);
					}
				},
				error: (xhr, status, error) => {
					this.showError(
						'Download failed: ' +
							(xhr.responseJSON?.data?.message || error)
					);
				},
				complete: () => {
					$btn.prop('disabled', false).text(originalText);
				},
			});
		},

		/**
		 * Handle keyboard events
		 *
		 * @param e
		 */
		handleKeydown(e) {
			// ESC key closes modal
			if (e.keyCode === 27 && $('#slos-gen-modal').is(':visible')) {
				this.closeModal();
			}
		},

		/**
		 * Show notification
		 *
		 * @param type
		 * @param message
		 */
		showNotice(type, message) {
			const $notice = $(
				'<div class="slos-gen-notice slos-gen-notice--' + type + '">'
			).text(message);
			$('#slos-gen-notices').append($notice);

			// Auto-remove after 5 seconds
			setTimeout(() => {
				$notice.fadeOut(300, function () {
					$(this).remove();
				});
			}, 5000);
		},

		/**
		 * Show error notice with dashicons
		 *
		 * @param message
		 */
		showError(message) {
			// Remove any existing notices
			$('.slos-gen-notice').remove();

			// Create error notice with dashicon
			const $notice = $(
				'<div class="slos-gen-notice slos-gen-notice--error">'
			)
				.html(
					'<span class="dashicons dashicons-warning"></span> ' +
						message
				)
				.appendTo('#slos-gen-notices');

			// Auto-dismiss after 8 seconds
			setTimeout(function () {
				$notice.fadeOut(300, function () {
					$(this).remove();
				});
			}, 8000);
		},

		/**
		 * Show success notice with dashicons
		 *
		 * @param message
		 */
		showSuccess(message) {
			// Remove any existing notices
			$('.slos-gen-notice').remove();

			// Create success notice with dashicon
			const $notice = $(
				'<div class="slos-gen-notice slos-gen-notice--success">'
			)
				.html(
					'<span class="dashicons dashicons-yes"></span> ' + message
				)
				.appendTo('#slos-gen-notices');

			// Auto-dismiss after 5 seconds
			setTimeout(function () {
				$notice.fadeOut(300, function () {
					$(this).remove();
				});
			}, 5000);
		},

		/**
		 * Handle inline edit button click
		 *
		 * @param e
		 */
		handleInlineEdit(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const $field = $btn.closest('.slos-gen-field');
			const $value = $field.find('.slos-gen-field__value');
			const $input = $field.find('.slos-gen-field__input');
			const $actions = $field.find('.slos-gen-field__actions');

			// Store original value
			const originalValue = $value.text();
			$input.val(originalValue).data('original', originalValue);

			// Switch to edit mode
			$value.hide();
			$input.show().focus().select();
			$field.addClass('slos-gen-field--editing');

			// Toggle action buttons
			$actions
				.find(
					'.slos-gen-field__btn--edit, .slos-gen-field__btn--remove, .slos-gen-field__btn--add'
				)
				.hide();
			$actions
				.find(
					'.slos-gen-field__btn--save, .slos-gen-field__btn--cancel'
				)
				.show();
		},

		/**
		 * Handle inline save button click
		 *
		 * @param e
		 */
		handleInlineSave(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const $field = $btn.closest('.slos-gen-field');
			const $value = $field.find('.slos-gen-field__value');
			const $input = $field.find('.slos-gen-field__input');
			const $actions = $field.find('.slos-gen-field__actions');
			const $placeholder = $field.find('.slos-gen-field__placeholder');

			const newValue = $input.val().trim();
			const originalValue = $input.data('original') || '';
			const wasEmpty = $field.hasClass('slos-gen-field--empty');

			// Update display value
			if (newValue) {
				if (wasEmpty) {
					// Was empty, now has value - create display element
					$placeholder.hide();
					if ($value.length === 0) {
						const $newValue = $(
							'<div class="slos-gen-field__value">'
						).text(newValue);
						$field
							.find('.slos-gen-field__value-wrapper')
							.prepend($newValue);
					} else {
						$value.text(newValue).show();
					}
					$field.removeClass(
						'slos-gen-field--empty slos-gen-field--missing'
					);

					// Update checkbox
					$field
						.find('.slos-gen-field__checkbox')
						.prop('checked', true)
						.prop('disabled', false);

					// Update actions to show Edit/Remove instead of Add
					$actions.find('.slos-gen-field__btn--add').hide();
					$actions
						.find(
							'.slos-gen-field__btn--edit, .slos-gen-field__btn--remove'
						)
						.show();
				} else {
					$value.text(newValue).show();
				}

				// Mark as modified if changed
				if (newValue !== originalValue) {
					$field.addClass('slos-gen-field--modified');
					$input.addClass('slos-gen-field__input--modified');
				}
			} else {
				// Empty value - restore original or show placeholder
				if (originalValue) {
					$value.text(originalValue).show();
				} else {
					$placeholder.show();
				}
			}

			// Exit edit mode
			$input.hide();
			$field.removeClass('slos-gen-field--editing');
			$actions
				.find(
					'.slos-gen-field__btn--save, .slos-gen-field__btn--cancel'
				)
				.hide();
			$actions
				.find(
					'.slos-gen-field__btn--edit, .slos-gen-field__btn--remove'
				)
				.show();

			// Update summary
			this.updateSummary();

			if (newValue !== originalValue) {
				this.showSuccess(
					'Field value updated (will be used in this generation only)'
				);
			}
		},

		/**
		 * Handle inline cancel button click
		 *
		 * @param e
		 */
		handleInlineCancel(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const $field = $btn.closest('.slos-gen-field');
			const $value = $field.find('.slos-gen-field__value');
			const $input = $field.find('.slos-gen-field__input');
			const $actions = $field.find('.slos-gen-field__actions');
			const $placeholder = $field.find('.slos-gen-field__placeholder');

			// Restore original state
			const originalValue = $input.data('original') || '';

			if (originalValue) {
				$value.text(originalValue).show();
				$input.val(originalValue);
			} else {
				$placeholder.show();
			}

			// Exit edit mode
			$input.hide();
			$field.removeClass('slos-gen-field--editing');
			$actions
				.find(
					'.slos-gen-field__btn--save, .slos-gen-field__btn--cancel'
				)
				.hide();

			// Show appropriate buttons based on field state
			if ($field.hasClass('slos-gen-field--empty')) {
				$actions.find('.slos-gen-field__btn--add').show();
			} else {
				$actions
					.find(
						'.slos-gen-field__btn--edit, .slos-gen-field__btn--remove'
					)
					.show();
			}
		},

		/**
		 * Handle view history button
		 *
		 * @param e
		 */
		handleViewHistory(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const docId = $btn.data('doc-id');
			const docType = $btn.data('type');

			if (!docId) return;

			// Store current doc ID for restore operations
			this.currentDocId = docId;
			this.currentType = docType;

			this.openHistoryModal(docId, docType);
		},

		/**
		 * Open history modal
		 *
		 * @param docId
		 * @param docType
		 */
		openHistoryModal(docId, docType) {
			// Create history modal if it doesn't exist
			let $historyModal = $('#slos-gen-history-modal');
			if ($historyModal.length === 0) {
				$historyModal = $(`
					<div id="slos-gen-history-modal" class="slos-gen-modal">
						<div class="slos-gen-modal__backdrop"></div>
						<div class="slos-gen-modal__container slos-gen-modal__container--wide">
							<div class="slos-gen-modal__content">
								<div class="slos-gen-modal__header">
									<h2 class="slos-gen-modal__title">Document History</h2>
									<button type="button" class="slos-gen-modal__close">✕</button>
								</div>
								<div class="slos-gen-modal__body">
									<div id="slos-gen-history-loading">Loading history...</div>
									<div id="slos-gen-history-content" style="display:none;"></div>
								</div>
							</div>
						</div>
					</div>
				`);
				$('body').append($historyModal);

				// Bind close events
				$historyModal
					.find('.slos-gen-modal__close, .slos-gen-modal__backdrop')
					.on('click', () => {
						$historyModal.fadeOut(200);
					});
			}

			// Show modal and load history
			$historyModal.fadeIn(200);
			$('#slos-gen-history-loading').show();
			$('#slos-gen-history-content').hide();

			// Load history via AJAX
			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_gen_history',
					nonce: $('#slos-gen-nonce').val(),
					doc_id: docId,
				},
				timeout: 10000,
				success: (response) => {
					if (response.success && response.data.versions) {
						this.renderHistoryTimeline(response.data.versions);
					} else {
						$('#slos-gen-history-content').html(
							'<p>No version history found.</p>'
						);
					}
					$('#slos-gen-history-loading').hide();
					$('#slos-gen-history-content').show();
				},
				error: (xhr, status, error) => {
					$('#slos-gen-history-loading').hide();
					const errorMsg =
						status === 'timeout'
							? 'Request timed out.'
							: 'Failed to load history.';
					$('#slos-gen-history-content')
						.html('<p class="error">' + errorMsg + '</p>')
						.show();
				},
			});
		},

		/**
		 * Render history timeline
		 *
		 * @param versions
		 */
		renderHistoryTimeline(versions) {
			const $container = $('#slos-gen-history-content');
			$container.empty();

			const $timeline = $('<div class="slos-gen-history-timeline">');

			versions.forEach((version, index) => {
				const isCurrent = index === 0;
				const $item = $(`
					<div class="slos-gen-timeline-item ${
						isCurrent ? 'slos-gen-timeline-item--current' : ''
					}">
						<div class="slos-gen-timeline-item__header">
							<span class="slos-gen-timeline-item__version ${
								isCurrent
									? 'slos-gen-timeline-item__version--current'
									: ''
							}">
								Version ${version.version_num}
							</span>
						</div>
						<div class="slos-gen-timeline-item__meta">
							<span class="slos-gen-timeline-item__meta-item">
								📅 ${version.created_at}
							</span>
							${
								version.author
									? `<span class="slos-gen-timeline-item__meta-item">👤 ${version.author}</span>`
									: ''
							}
						</div>
						${
							version.changelog
								? `<p class="slos-gen-timeline-item__notes">${version.changelog}</p>`
								: ''
						}
						<div class="slos-gen-timeline-item__actions">
							${
								!isCurrent
									? `<button type="button" class="slos-gen-timeline-item__btn slos-gen-timeline-item__btn--primary slos-gen-timeline-item__btn--restore" data-version-id="${version.id}" data-version-num="${version.version_num}">Restore</button>`
									: ''
							}
							${
								index < versions.length - 1
									? `<button type="button" class="slos-gen-timeline-item__btn slos-gen-timeline-item__btn--compare" data-version-id="${
											version.id
									  }" data-compare-id="${
											versions[index + 1].id
									  }">Compare</button>`
									: ''
							}
						</div>
					</div>
				`);
				$timeline.append($item);
			});

			$container.append($timeline);
		},

		/**
		 * Handle version restore
		 *
		 * @param e
		 */
		handleVersionRestore(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const versionId = $btn.data('version-id');
			const versionNum = $btn.data('version-num');

			// Confirmation dialog
			if (
				!confirm(
					`Restore version ${versionNum}? This will create a new draft from that version's content.`
				)
			) {
				return;
			}

			const originalText = $btn.text();
			$btn.prop('disabled', true).text('Restoring...');

			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_gen_restore',
					nonce: $('#slos-gen-nonce').val(),
					version_id: versionId,
					doc_id: this.currentDocId,
				},
				timeout: 15000,
				success: (response) => {
					if (response.success) {
						this.showSuccess(
							response.data?.message ||
								'Version restored successfully'
						);

						// Redirect to edit page if provided
						if (response.data && response.data.edit_url) {
							setTimeout(() => {
								window.location.href = response.data.edit_url;
							}, 1000);
						} else {
							setTimeout(() => {
								window.location.reload();
							}, 1000);
						}
					} else {
						this.showError(
							response.data?.message || 'Restore failed'
						);
						$btn.prop('disabled', false).text(originalText);
					}
				},
				error: (xhr, status, error) => {
					$btn.prop('disabled', false).text(originalText);

					if (status === 'timeout') {
						this.showError(
							'Restore request timed out. Please try again.'
						);
					} else {
						this.showError(
							'Restore failed: ' +
								(xhr.responseJSON?.data?.message || error)
						);
					}
				},
			});
		},

		/**
		 * Handle version comparison
		 *
		 * @param e
		 */
		handleVersionCompare(e) {
			e.preventDefault();
			const $btn = $(e.currentTarget);
			const versionId = $btn.data('version-id');
			const compareId = $btn.data('compare-id');

			// Show loading notice
			this.showNotice('info', 'Loading version comparison...');

			// Load comparison via AJAX
			$.ajax({
				url: SLOSDocGen.ajaxurl,
				type: 'POST',
				data: {
					action: 'slos_gen_compare_versions',
					nonce: $('#slos-gen-nonce').val(),
					version_id: versionId,
					compare_id: compareId,
				},
				timeout: 15000,
				success: (response) => {
					if (response.success) {
						this.showComparisonModal(response.data);
					} else {
						this.showError(
							response.data?.message || 'Comparison failed'
						);
					}
				},
				error: (xhr, status, error) => {
					if (status === 'timeout') {
						this.showError(
							'Comparison request timed out. Please try again.'
						);
					} else {
						this.showError(
							'Comparison failed: ' +
								(xhr.responseJSON?.data?.message || error)
						);
					}
				},
			});
		},

		/**
		 * Show version comparison modal
		 *
		 * @param data
		 */
		showComparisonModal(data) {
			// Create comparison modal if it doesn't exist
			let $compareModal = $('#slos-gen-compare-modal');
			if ($compareModal.length === 0) {
				$compareModal = $(`
					<div id="slos-gen-compare-modal" class="slos-gen-modal">
						<div class="slos-gen-modal__backdrop"></div>
						<div class="slos-gen-modal__container slos-gen-modal__container--wide">
							<div class="slos-gen-modal__content">
								<div class="slos-gen-modal__header">
									<h2 class="slos-gen-modal__title">Version Comparison</h2>
									<button type="button" class="slos-gen-modal__close">✕</button>
								</div>
								<div class="slos-gen-modal__body" id="slos-gen-compare-content">
								</div>
								<div class="slos-gen-modal__footer">
									<button type="button" class="button button-secondary" id="slos-gen-compare-close">Close</button>
								</div>
							</div>
						</div>
					</div>
				`);
				$('body').append($compareModal);

				// Bind close events
				$compareModal
					.find(
						'.slos-gen-modal__close, #slos-gen-compare-close, .slos-gen-modal__backdrop'
					)
					.on('click', () => {
						$compareModal.fadeOut(200);
					});
			}

			// Render comparison - data contains diff_html, version1_at, version2_at from PHP
			const html = `
				<div class="slos-gen-comparison-header">
					<p class="slos-gen-comparison-dates">
						<span>Comparing versions from:</span>
						<strong>${data.version2_at || 'Unknown'}</strong> → <strong>${
				data.version1_at || 'Unknown'
			}</strong>
					</p>
				</div>
				<div class="slos-gen-comparison-diff">
					${data.diff_html || '<p>No differences to display.</p>'}
				</div>
			`;

			$('#slos-gen-compare-content').html(html);
			$compareModal.fadeIn(200);
		},
	};

	// Initialize on document ready
	$(document).ready(function () {
		SLOSDocGenerate.init();
	});

	// Global AJAX error handler for document generation requests
	$(document).ajaxError(function (event, xhr, settings, thrownError) {
		// Only handle our AJAX requests
		if (settings.url === SLOSDocGen.ajaxurl && settings.data) {
			const action =
				typeof settings.data === 'string'
					? new URLSearchParams(settings.data).get('action')
					: settings.data.action;

			if (action && action.startsWith('slos_gen_')) {
				console.error('SLOS AJAX Error:', {
					action,
					xhr,
					status: xhr.status,
					thrownError,
					settings,
				});

				// Don't show error if already handled by specific error callback
				// Check if the request has suppressGlobalError flag
				if (!settings.suppressGlobalError) {
					// Only show if no specific error handler already displayed a message
					if (xhr.status !== 0 && thrownError !== 'timeout') {
						SLOSDocGenerate.showError(
							'Unexpected error. Please try again or contact support.'
						);
					}
				}
			}
		}
	});
})(jQuery);
