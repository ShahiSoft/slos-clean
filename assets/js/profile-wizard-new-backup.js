/**
 * Company Profile Wizard JavaScript
 *
 * Handles step navigation, auto-save functionality, field validation,
 * and dynamic field interactions for the company profile wizard.
 *
 * @package
 * @subpackage  Assets/JS
 * @version     4.1.0
 * @since       4.1.0
 */

/* global slosProfileWizard, jQuery, wp */

(function ($) {
	'use strict';

	/**
	 * Profile Wizard Controller
	 */
	const ProfileWizard = {
		/**
		 * Configuration and state
		 */
		config: {
			autoSaveDelay: 2000,
			ajaxUrl: slosProfileWizard?.ajaxUrl || '/wp-admin/admin-ajax.php',
			nonce: slosProfileWizard?.nonce || '',
			steps: slosProfileWizard?.steps || {},
			i18n: slosProfileWizard?.i18n || {},
		},

		state: {
			currentStep: 1,
			totalSteps: 11,
			isDirty: false,
			isSaving: false,
			autoSaveTimer: null,
			profile: {},
			completion: {},
		},

		/**
		 * DOM element cache
		 */
		elements: {},

		/**
		 * Initialize the wizard
		 */
		init() {
			this.cacheElements();
			this.bindEvents();
			this.loadInitialState();
			this.initializeTags();
			this.initializeCookieList();

			console.log('[Profile Wizard] Initialized');
		},

		/**
		 * Cache DOM elements
		 */
		cacheElements() {
			this.elements = {
				container: $('.slos-wizard-container'),
				form: $('#slos-wizard-form'),
				stepNav: $('.slos-wizard-nav'),
				stepItems: $('.slos-step-item'),
				stepPanels: $('.slos-step-panel'),
				prevBtn: $('#slos-prev-btn'),
				nextBtn: $('#slos-next-btn'),
				saveBtn: $('#slos-save-btn'),
				finishBtn: $('#slos-finish-btn'),
				saveStatus: $('#slos-save-status'),
				progressFill: $('.slos-progress-fill'),
				progressPercent: $('.slos-progress-percentage'),
				currentStepInput: $('#slos-current-step'),
				completionModal: $('#slos-completion-modal'),
			};
		},

		/**
		 * Bind event listeners
		 */
		bindEvents() {
			// Navigation events
			this.elements.stepItems.on('click', (e) =>
				this.handleStepNavClick(e)
			);
			this.elements.prevBtn.on('click', () => this.previousStep());
			this.elements.nextBtn.on('click', () => this.nextStep());
			this.elements.saveBtn.on('click', () => this.saveCurrentStep());
			this.elements.finishBtn?.on('click', () => this.finishWizard());

			// Form change events for auto-save
			this.elements.form.on('change', 'input, select, textarea', (e) =>
				this.handleFieldChange(e)
			);
			this.elements.form.on(
				'input',
				'input[type="text"], input[type="email"], input[type="url"], textarea',
				this.debounce((e) => this.handleFieldChange(e), 500)
			);

			// Modal events
			$('.slos-modal-close').on('click', () => this.closeModal());
			this.elements.completionModal.on('click', (e) => {
				if ($(e.target).hasClass('slos-modal')) {
					this.closeModal();
				}
			});

			// Keyboard navigation
			$(document).on('keydown', (e) => this.handleKeyboardNav(e));

			// Prevent accidental navigation
			$(window).on('beforeunload', (e) => {
				if (this.state.isDirty) {
					e.preventDefault();
					e.returnValue = '';
				}
			});
		},

		/**
		 * Load initial state from server
		 */
		loadInitialState() {
			const currentStep =
				parseInt(this.elements.currentStepInput.val(), 10) || 1;
			this.state.currentStep = currentStep;
			this.updateStepDisplay(currentStep);
		},

		/**
		 * Handle step navigation click
		 *
		 * @param {Event} e Click event
		 */
		handleStepNavClick(e) {
			const $item = $(e.currentTarget);
			const stepNum = parseInt($item.data('step'), 10);

			if (stepNum !== this.state.currentStep) {
				this.goToStep(stepNum);
			}
		},

		/**
		 * Go to a specific step
		 *
		 * @param {number} stepNum Step number to navigate to
		 */
		async goToStep(stepNum) {
			if (stepNum < 1 || stepNum > this.state.totalSteps) {
				return;
			}

			// Save current step before navigating
			if (this.state.isDirty) {
				await this.saveCurrentStep(true);
			}

			this.state.currentStep = stepNum;
			this.updateStepDisplay(stepNum);
			this.elements.currentStepInput.val(stepNum);

			// Scroll to top of content
			this.elements.container.find('.slos-wizard-content').scrollTop(0);
		},

		/**
		 * Move to previous step
		 */
		previousStep() {
			if (this.state.currentStep > 1) {
				this.goToStep(this.state.currentStep - 1);
			}
		},

		/**
		 * Move to next step
		 */
		async nextStep() {
			// Validate current step before proceeding
			const isValid = this.validateCurrentStep();

			if (!isValid) {
				this.showValidationErrors();
				return;
			}

			if (this.state.currentStep < this.state.totalSteps) {
				await this.goToStep(this.state.currentStep + 1);
			}
		},

		/**
		 * Update step display (navigation and panels)
		 *
		 * @param {number} stepNum Active step number
		 */
		updateStepDisplay(stepNum) {
			// Update navigation items
			this.elements.stepItems
				.removeClass('slos-step-active')
				.filter(`[data-step="${stepNum}"]`)
				.addClass('slos-step-active');

			// Update panels
			this.elements.stepPanels
				.removeClass('slos-step-active')
				.filter(`[data-step="${stepNum}"]`)
				.addClass('slos-step-active');

			// Update navigation buttons
			this.elements.prevBtn.prop('disabled', stepNum <= 1);

			// Show/hide next vs finish button
			if (stepNum >= this.state.totalSteps) {
				this.elements.nextBtn?.hide();
				this.elements.finishBtn?.show();
			} else {
				this.elements.nextBtn?.show();
				this.elements.finishBtn?.hide();
			}

			// Update step-based progress bar
			this.updateStepProgress(stepNum);
		},

		/**
		 * Update step-based progress (steps completed / total steps)
		 *
		 * @param {number} stepNum Current step number
		 */
		updateStepProgress(stepNum) {
			// Calculate progress based on completed steps (step - 1 completed + current in progress)
			const completedSteps = this.elements.stepItems.filter(
				'.slos-step-complete'
			).length;
			const percentage = Math.round(
				(completedSteps / this.state.totalSteps) * 100
			);

			this.elements.progressFill.css('width', percentage + '%');
			this.elements.progressPercent.text(percentage + '%');
		},

		/**
		 * Handle field change for auto-save
		 *
		 * @param {Event} e Change event
		 */
		handleFieldChange(e) {
			this.state.isDirty = true;
			this.clearFieldError(e.target);
			this.scheduleAutoSave();
		},

		/**
		 * Schedule auto-save after delay
		 */
		scheduleAutoSave() {
			if (this.state.autoSaveTimer) {
				clearTimeout(this.state.autoSaveTimer);
			}

			this.showSaveStatus('pending');

			this.state.autoSaveTimer = setTimeout(() => {
				this.saveCurrentStep(true);
			}, this.config.autoSaveDelay);
		},

		/**
		 * Save current step data
		 *
		 * @param {boolean} silent Whether to show success message
		 * @return {Promise} Save promise
		 */
		async saveCurrentStep(silent = false) {
			if (this.state.isSaving) {
				return;
			}

			this.state.isSaving = true;
			this.showSaveStatus('saving');

			const stepData = this.collectStepData();

			try {
				const response = await $.ajax({
					url: this.config.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_profile_save_step',
						nonce: this.config.nonce,
						step: this.state.currentStep,
						data: stepData,
					},
				});

				if (response.success) {
					this.state.isDirty = false;
					this.showSaveStatus('saved');
					this.updateProgress(response.data?.completion);
					this.updateStepCompletion(
						this.state.currentStep,
						response.data?.step_valid
					);

					if (!silent) {
						this.showNotice(this.config.i18n.saved, 'success');
					}
				} else {
					this.showSaveStatus('error');
					this.showNotice(
						response.data?.message || this.config.i18n.error,
						'error'
					);
				}
			} catch (error) {
				console.error('[Profile Wizard] Save error:', error);
				this.showSaveStatus('error');
				this.showNotice(this.config.i18n.error, 'error');
			} finally {
				this.state.isSaving = false;
			}
		},

		/**
		 * Collect form data for current step
		 *
		 * @return {Object} Step data
		 */
		collectStepData() {
			const $panel = this.elements.stepPanels.filter('.slos-step-active');
			const data = {};

			// Standard inputs
			$panel
				.find(
					'input:not([type="checkbox"]):not([type="radio"]), select, textarea'
				)
				.each(function () {
					const $field = $(this);
					const name = $field.attr('name');

					if (
						name &&
						!name.endsWith('[]') &&
						!$field.hasClass('slos-tags-input-field')
					) {
						data[name] = $field.val();
					}
				});

			// Radio buttons
			$panel.find('input[type="radio"]:checked').each(function () {
				const name = $(this).attr('name');
				data[name] = $(this).val();
			});

			// Checkboxes (multi-value)
			$panel.find('.slos-checkbox-group').each(function () {
				const $group = $(this);
				const name = $group
					.find('input[type="checkbox"]')
					.first()
					.attr('name');
				if (name) {
					const baseName = name.replace('[]', '');
					data[baseName] = [];
					$group
						.find('input[type="checkbox"]:checked')
						.each(function () {
							data[baseName].push($(this).val());
						});
				}
			});

			// Tags fields
			$panel.find('.slos-tags-input').each(function () {
				const $container = $(this);
				const $hidden = $container.find('input[type="hidden"]');
				const name = $hidden.attr('name');
				const value = $hidden.val();
				if (name) {
					data[name] = value ? value.split(',') : [];
				}
			});

			// Cookie list
			$panel.find('.slos-cookie-list').each(function () {
				const $list = $(this);
				const fieldName = $list.data('field');
				const cookies = [];

				$list.find('.slos-cookie-row').each(function () {
					const $row = $(this);
					const cookie = {
						name: $row.find('input[name$="[name]"]').val(),
						purpose: $row.find('input[name$="[purpose]"]').val(),
						duration: $row.find('input[name$="[duration]"]').val(),
					};
					if (cookie.name) {
						cookies.push(cookie);
					}
				});

				data[fieldName] = cookies;
			});

			return data;
		},

		/**
		 * Validate current step fields
		 *
		 * @return {boolean} Whether step is valid
		 */
		validateCurrentStep() {
			const $panel = this.elements.stepPanels.filter('.slos-step-active');
			let isValid = true;

			// Check required fields
			$panel.find('[required]').each((_, field) => {
				const $field = $(field);
				const value = $field.val()?.trim();

				if (!value) {
					this.showFieldError($field, this.config.i18n.required);
					isValid = false;
				}
			});

			// Validate email fields
			$panel.find('input[type="email"]').each((_, field) => {
				const $field = $(field);
				const value = $field.val()?.trim();

				if (value && !this.isValidEmail(value)) {
					this.showFieldError($field, this.config.i18n.invalidEmail);
					isValid = false;
				}
			});

			// Validate URL fields
			$panel.find('input[type="url"]').each((_, field) => {
				const $field = $(field);
				const value = $field.val()?.trim();

				if (value && !this.isValidUrl(value)) {
					this.showFieldError($field, this.config.i18n.invalidUrl);
					isValid = false;
				}
			});

			return isValid;
		},

		/**
		 * Show validation errors to user
		 */
		showValidationErrors() {
			const $firstError = this.elements.form
				.find('.slos-field-error:visible')
				.first();

			if ($firstError.length) {
				$firstError
					.closest('.slos-field-group')
					.find('input, select, textarea')
					.first()
					.focus();
			}

			this.showNotice(this.config.i18n.validationFailed, 'warning');
		},

		/**
		 * Show field error
		 *
		 * @param {jQuery} $field  Field element
		 * @param {string} message Error message
		 */
		showFieldError($field, message) {
			const $group = $field.closest('.slos-field-group');
			$group.addClass('slos-has-error');

			const $error = $group.find('.slos-field-error');
			$error.text(message).show();
		},

		/**
		 * Clear field error
		 *
		 * @param {Element} field Field element
		 */
		clearFieldError(field) {
			const $group = $(field).closest('.slos-field-group');
			$group.removeClass('slos-has-error');
			$group.find('.slos-field-error').hide().text('');
		},

		/**
		 * Update progress bar
		 *
		 * @param {Object} completion Completion data
		 */
		updateProgress(completion) {
			if (!completion) return;

			const percentage = completion.percentage || 0;
			this.elements.progressFill.css('width', percentage + '%');
			this.elements.progressPercent.text(percentage + '%');
			this.state.completion = completion;
		},

		/**
		 * Update step completion status
		 *
		 * @param {number}  stepNum Step number
		 * @param {boolean} isValid Whether step is valid
		 */
		updateStepCompletion(stepNum, isValid) {
			const $item = this.elements.stepItems.filter(
				`[data-step="${stepNum}"]`
			);

			if (isValid) {
				$item.addClass('slos-step-complete');
				$item
					.find('.slos-step-number')
					.html('<span class="dashicons dashicons-yes"></span>');
				$item.find('.slos-step-status').text(this.config.i18n.complete);
			} else {
				$item.removeClass('slos-step-complete');
				$item.find('.slos-step-number').text(stepNum);
			}

			// Update progress bar after step completion change
			this.updateStepProgress(this.state.currentStep);
		},

		/**
		 * Show save status indicator
		 *
		 * @param {string} status Status type: pending, saving, saved, error
		 */
		showSaveStatus(status) {
			const $status = this.elements.saveStatus;

			$status.removeClass(
				'slos-status-pending slos-status-saving slos-status-saved slos-status-error'
			);

			switch (status) {
				case 'pending':
					$status
						.addClass('slos-status-pending')
						.html(
							'<span class="dashicons dashicons-edit"></span> ' +
								this.config.i18n.unsavedChanges
						);
					break;
				case 'saving':
					$status
						.addClass('slos-status-saving')
						.html(
							'<span class="dashicons dashicons-update spin"></span> ' +
								this.config.i18n.saving
						);
					break;
				case 'saved':
					$status
						.addClass('slos-status-saved')
						.html(
							'<span class="dashicons dashicons-saved"></span> ' +
								this.config.i18n.saved
						);
					setTimeout(() => $status.fadeOut(), 3000);
					break;
				case 'error':
					$status
						.addClass('slos-status-error')
						.html(
							'<span class="dashicons dashicons-warning"></span> ' +
								this.config.i18n.saveError
						);
					break;
			}

			$status.fadeIn();
		},

		/**
		 * Finish wizard
		 */
		async finishWizard() {
			// Final validation
			const isValid = this.validateCurrentStep();

			if (!isValid) {
				this.showValidationErrors();
				return;
			}

			// Save final step
			this.showSaveStatus('saving');
			await this.saveCurrentStep(true);

			// Validate entire profile
			try {
				const response = await $.ajax({
					url: this.config.ajaxUrl,
					type: 'POST',
					data: {
						action: 'slos_profile_validate',
						nonce: this.config.nonce,
					},
				});

				if (response.success && response.data?.is_valid) {
					// Profile is complete - redirect to Document Hub
					this.showNotice(
						this.config.i18n.profileComplete ||
							'Profile saved successfully! Redirecting...',
						'success'
					);
					setTimeout(() => {
						window.location.href =
							slosProfileWizard?.documentHubUrl ||
							'/wp-admin/admin.php?page=slos-documents';
					}, 1000);
				} else {
					// Profile incomplete but still save and redirect
					const missingCount =
						response.data?.missing_fields?.length || 0;
					if (missingCount > 0) {
						this.showNotice(
							(
								this.config.i18n.incompleteProfileSaved ||
								'Profile saved with {count} optional fields remaining. Redirecting...'
							).replace('{count}', missingCount),
							'warning'
						);
					} else {
						this.showNotice(
							this.config.i18n.profileComplete ||
								'Profile saved successfully! Redirecting...',
							'success'
						);
					}
					setTimeout(() => {
						window.location.href =
							slosProfileWizard?.documentHubUrl ||
							'/wp-admin/admin.php?page=slos-documents';
					}, 1500);
				}
			} catch (error) {
				console.error('[Profile Wizard] Validation error:', error);
				this.showNotice(this.config.i18n.error, 'error');
			}
		},

		/**
		 * Show completion modal
		 */
		showCompletionModal() {
			this.elements.completionModal.addClass('slos-modal-open');
			$('body').addClass('slos-modal-active');
		},

		/**
		 * Close modal
		 */
		closeModal() {
			$('.slos-modal').removeClass('slos-modal-open');
			$('body').removeClass('slos-modal-active');
		},

		/**
		 * Initialize tags input fields
		 */
		initializeTags() {
			const wizard = this;

			$('.slos-tags-input').each(function () {
				const $container = $(this);
				const $input = $container.find('.slos-tags-input-field');
				const $hidden = $container.find('input[type="hidden"]');
				const $tagsContainer = $container.find('.slos-tags-container');
				const $suggestions = $container.find('.slos-tags-suggestions');
				const suggestionsData = $container.data('suggestions') || [];

				// Add tag
				const addTag = (tag) => {
					tag = tag.trim();
					if (!tag) return;

					// Check for duplicate
					const currentTags = $hidden.val()
						? $hidden.val().split(',')
						: [];
					if (currentTags.includes(tag)) return;

					// Add tag element
					$tagsContainer.append(
						`<span class="slos-tag">${wizard.escapeHtml(
							tag
						)}<button type="button" class="slos-tag-remove">&times;</button></span>`
					);

					// Update hidden value
					currentTags.push(tag);
					$hidden.val(currentTags.join(',')).trigger('change');
					$input.val('');
				};

				// Remove tag
				$tagsContainer.on('click', '.slos-tag-remove', function () {
					const $tag = $(this).closest('.slos-tag');
					const tagText = $tag.text().slice(0, -1); // Remove × character
					const currentTags = $hidden.val()
						? $hidden.val().split(',')
						: [];
					const newTags = currentTags.filter((t) => t !== tagText);
					$hidden.val(newTags.join(',')).trigger('change');
					$tag.remove();
				});

				// Input events
				$input.on('keydown', function (e) {
					if (e.key === 'Enter' || e.key === ',') {
						e.preventDefault();
						addTag($input.val());
					}
				});

				// Suggestions
				$input.on('input', function () {
					const query = $input.val().toLowerCase();
					if (query.length < 2) {
						$suggestions.hide();
						return;
					}

					const matches = suggestionsData.filter((s) =>
						s.toLowerCase().includes(query)
					);
					if (matches.length) {
						$suggestions
							.html(
								matches
									.map(
										(m) =>
											`<div class="slos-suggestion">${wizard.escapeHtml(
												m
											)}</div>`
									)
									.join('')
							)
							.show();
					} else {
						$suggestions.hide();
					}
				});

				$suggestions.on('click', '.slos-suggestion', function () {
					addTag($(this).text());
					$suggestions.hide();
				});

				// Hide suggestions on blur
				$input.on('blur', function () {
					setTimeout(() => $suggestions.hide(), 200);
				});
			});
		},

		/**
		 * Initialize cookie list fields
		 */
		initializeCookieList() {
			const wizard = this;

			$('.slos-cookie-list').each(function () {
				const $list = $(this);
				const fieldName = $list.data('field');

				// Add cookie row
				$list.find('.slos-add-cookie').on('click', function () {
					const $tbody = $list.find('tbody');
					const newIndex = $tbody.find('.slos-cookie-row').length;

					const newRow = `
						<tr class="slos-cookie-row" data-index="${newIndex}">
							<td><input type="text" name="${fieldName}[${newIndex}][name]" class="slos-field-input" placeholder="cookie_name"></td>
							<td><input type="text" name="${fieldName}[${newIndex}][purpose]" class="slos-field-input" placeholder="Purpose description"></td>
							<td><input type="text" name="${fieldName}[${newIndex}][duration]" class="slos-field-input" placeholder="Session, 1 year, etc."></td>
							<td><button type="button" class="slos-btn slos-btn-sm slos-btn-danger slos-remove-cookie">
								<span class="dashicons dashicons-trash"></span></button></td>
						</tr>
					`;

					$tbody.append(newRow);
					$tbody
						.find('.slos-cookie-row')
						.last()
						.find('input')
						.first()
						.focus();
					wizard.state.isDirty = true;
					wizard.scheduleAutoSave();
				});

				// Remove cookie row
				$list.on('click', '.slos-remove-cookie', function () {
					$(this).closest('.slos-cookie-row').remove();
					wizard.state.isDirty = true;
					wizard.scheduleAutoSave();
				});

				// Track changes
				$list.on('change input', 'input', function () {
					wizard.state.isDirty = true;
					wizard.scheduleAutoSave();
				});
			});
		},

		/**
		 * Handle keyboard navigation
		 *
		 * @param {Event} e Keyboard event
		 */
		handleKeyboardNav(e) {
			// Only handle if not in input/textarea
			if ($(e.target).is('input, textarea, select')) {
				return;
			}

			switch (e.key) {
				case 'ArrowLeft':
					this.previousStep();
					break;
				case 'ArrowRight':
					this.nextStep();
					break;
			}
		},

		/**
		 * Show notice message
		 *
		 * @param {string} message Notice message
		 * @param {string} type    Notice type: success, error, warning, info
		 */
		showNotice(message, type = 'info') {
			// Remove existing notices
			$('.slos-wizard-notice').remove();

			const iconMap = {
				success: 'yes',
				error: 'dismiss',
				warning: 'warning',
				info: 'info',
			};

			const $notice = $(`
				<div class="slos-wizard-notice slos-notice-${type}">
					<span class="dashicons dashicons-${iconMap[type]}"></span>
					<span class="slos-notice-message">${this.escapeHtml(message)}</span>
					<button type="button" class="slos-notice-dismiss">&times;</button>
				</div>
			`);

			this.elements.container.prepend($notice);

			// Auto-dismiss after 5 seconds
			setTimeout(() => {
				$notice.fadeOut(() => $notice.remove());
			}, 5000);

			// Manual dismiss
			$notice.find('.slos-notice-dismiss').on('click', () => {
				$notice.fadeOut(() => $notice.remove());
			});
		},

		/**
		 * Escape HTML
		 *
		 * @param {string} str String to escape
		 * @return {string} Escaped string
		 */
		escapeHtml(str) {
			const div = document.createElement('div');
			div.textContent = str;
			return div.innerHTML;
		},

		/**
		 * Validate email format
		 *
		 * @param {string} email Email address
		 * @return {boolean} Whether email is valid
		 */
		isValidEmail(email) {
			return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
		},

		/**
		 * Validate URL format
		 *
		 * @param {string} url URL string
		 * @return {boolean} Whether URL is valid
		 */
		isValidUrl(url) {
			try {
				new URL(url);
				return true;
			} catch {
				return false;
			}
		},

		/**
		 * Debounce function
		 *
		 * @param {Function} func Function to debounce
		 * @param {number}   wait Wait time in ms
		 * @return {Function} Debounced function
		 */
		debounce(func, wait) {
			let timeout;
			return function (...args) {
				clearTimeout(timeout);
				timeout = setTimeout(() => func.apply(this, args), wait);
			};
		},
	};

	/**
	 * sprintf implementation for JavaScript
	 *
	 * @param {string} format Format string
	 * @param {...*}   args   Arguments
	 * @return {string} Formatted string
	 */
	function sprintf(format, ...args) {
		let i = 0;
		return format.replace(/%[sd]/g, () => args[i++] ?? '');
	}

	// Initialize on document ready
	$(document).ready(() => {
		if ($('.slos-profile-wizard-wrap').length) {
			ProfileWizard.init();
		}
	});

	// Export for external access
	window.SLOSProfileWizard = ProfileWizard;
})(jQuery);
