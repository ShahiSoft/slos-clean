/**
 * DSR Form JavaScript
 *
 * Handles DSR form submission, validation, and user interaction.
 * Integrates with REST API for request submission.
 *
 * @package
 * @version 3.0.1
 * @since 3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * DSR Form Handler
	 */
	const SlosDsrForm = {
		/**
		 * Initialize form handler
		 */
		init() {
			this.form = $('#slos-dsr-form');
			if (this.form.length === 0) {
				return;
			}

			this.wrapper = $('.slos-dsr-form-wrapper');
			this.submitButton = $('#slos-dsr-submit');
			this.successMessage = $('.slos-dsr-success');
			this.errorMessage = $('.slos-dsr-error');

			// Submission throttling
			this.lastSubmitTime = 0;
			this.submitThrottle = 2000; // 2 seconds between submissions

			// Bind events
			this.bindEvents();

			// Update SLA notice on regulation change
			this.updateSlaNotice();

			// Initialize request type descriptions
			this.initRequestTypeDescriptions();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			const self = this;

			// Form submission
			this.form.on('submit', function (e) {
				e.preventDefault();
				self.handleSubmit();
			});

			// Real-time email validation
			$('#slos-dsr-email').on('blur', function () {
				self.validateEmail($(this));
			});

			// Request type change - show description
			$('#slos-dsr-type').on('change', function () {
				self.updateRequestTypeDescription($(this).val());
			});

			// Regulation change - update SLA notice
			$('#slos-dsr-regulation').on('change', function () {
				self.updateSlaNotice();
			});

			// Character counter for details textarea
			$('#slos-dsr-details').on('input', function () {
				self.updateCharacterCount($(this));
			});

			// Clear errors on input
			this.form
				.find('input, select, textarea')
				.on('input change', function () {
					self.clearFieldError($(this));
				});

			// File upload validation
			$('#slos-dsr-identity').on('change', function () {
				self.validateFile($(this));
			});
		},

		/**
		 * Handle form submission
		 */
		handleSubmit() {
			const self = this;

			// Check throttle
			const now = Date.now();
			if (now - this.lastSubmitTime < this.submitThrottle) {
				this.showError(slosDsrForm.i18n.rateLimitError);
				return;
			}

			// Hide previous messages
			this.hideMessages();

			// Validate form
			if (!this.validateForm()) {
				return;
			}

			// Update UI
			this.setSubmitting(true);
			this.lastSubmitTime = now;

			// Gather form data
			const formData = this.getFormData();

			// Submit via REST API
			$.ajax({
				url: this.wrapper.data('api-url') + '/submit',
				method: 'POST',
				data: JSON.stringify(formData),
				contentType: 'application/json',
				dataType: 'json',
				beforeSend(xhr) {
					xhr.setRequestHeader(
						'X-WP-Nonce',
						self.wrapper.data('nonce')
					);
				},
				success(response) {
					self.handleSuccess(response);
				},
				error(xhr, status, error) {
					self.handleError(xhr, status, error);
				},
				complete() {
					self.setSubmitting(false);
				},
			});
		},

		/**
		 * Get form data
		 */
		getFormData() {
			return {
				email: $('#slos-dsr-email').val().trim(),
				request_type: $('#slos-dsr-type').val(),
				regulation: $('#slos-dsr-regulation').val(),
				details: $('#slos-dsr-details').val().trim(),
				user_id: 0, // Will be set server-side if logged in
			};
		},

		/**
		 * Validate entire form
		 */
		validateForm() {
			let isValid = true;

			// Validate name
			const name = $('#slos-dsr-name');
			if (name.val().trim() === '') {
				this.showFieldError(name, slosDsrForm.i18n.nameRequired);
				isValid = false;
			}

			// Validate email
			const email = $('#slos-dsr-email');
			if (email.val().trim() === '') {
				this.showFieldError(email, slosDsrForm.i18n.emailRequired);
				isValid = false;
			} else if (!this.isValidEmail(email.val())) {
				this.showFieldError(email, slosDsrForm.i18n.emailInvalid);
				isValid = false;
			}

			// Validate request type
			const type = $('#slos-dsr-type');
			if (type.val() === '') {
				this.showFieldError(type, slosDsrForm.i18n.typeRequired);
				isValid = false;
			}

			// Validate attestation
			const attestation = $('#slos-dsr-attestation');
			if (!attestation.is(':checked')) {
				this.showFieldError(
					attestation,
					slosDsrForm.i18n.attestationRequired
				);
				isValid = false;
			}

			return isValid;
		},

		/**
		 * Validate email field
		 *
		 * @param $field
		 */
		validateEmail($field) {
			const email = $field.val().trim();

			if (email === '') {
				this.showFieldError($field, slosDsrForm.i18n.emailRequired);
				return false;
			}

			if (!this.isValidEmail(email)) {
				this.showFieldError($field, slosDsrForm.i18n.emailInvalid);
				return false;
			}

			this.clearFieldError($field);
			return true;
		},

		/**
		 * Check if email is valid
		 *
		 * @param email
		 */
		isValidEmail(email) {
			const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return regex.test(email);
		},

		/**
		 * Validate file upload
		 *
		 * @param $field
		 */
		validateFile($field) {
			const file = $field[0].files[0];

			if (!file) {
				return true;
			}

			// Check file size (5MB max)
			const maxSize = 5 * 1024 * 1024;
			if (file.size > maxSize) {
				this.showFieldError($field, 'File size must be less than 5MB.');
				$field.val('');
				return false;
			}

			// Check file type
			const allowedTypes = [
				'application/pdf',
				'image/jpeg',
				'image/jpg',
				'image/png',
			];
			if (!allowedTypes.includes(file.type)) {
				this.showFieldError(
					$field,
					'Only PDF, JPG, and PNG files are allowed.'
				);
				$field.val('');
				return false;
			}

			this.clearFieldError($field);
			return true;
		},

		/**
		 * Show field error
		 *
		 * @param $field
		 * @param message
		 */
		showFieldError($field, message) {
			const errorId = $field.attr('id') + '-error';
			const $error = $('#' + errorId);

			$field.addClass('slos-field-invalid').attr('aria-invalid', 'true');
			$error.text(message).show();

			// Focus on first error
			if (!this.form.find('.slos-field-invalid').not($field).length) {
				$field.focus();
			}
		},

		/**
		 * Clear field error
		 *
		 * @param $field
		 */
		clearFieldError($field) {
			const errorId = $field.attr('id') + '-error';
			const $error = $('#' + errorId);

			$field
				.removeClass('slos-field-invalid')
				.attr('aria-invalid', 'false');
			$error.text('').hide();
		},

		/**
		 * Handle successful submission
		 *
		 * @param response
		 */
		handleSuccess(response) {
			// Scroll to top of form
			this.scrollToTop();

			// Hide form
			this.form.hide();

			// Show success message
			this.successMessage.show();

			// Update SLA notice in success message
			const regulation = $('#slos-dsr-regulation').val();
			const slaNotice = this.getSlaNotice(regulation);
			$('.slos-dsr-success .slos-dsr-sla-notice').html(slaNotice);

			// Reset form (in case user wants to submit another)
			this.form[0].reset();
			this.updateCharacterCount($('#slos-dsr-details'));
		},

		/**
		 * Handle submission error
		 *
		 * @param xhr
		 * @param status
		 * @param error
		 */
		handleError(xhr, status, error) {
			let errorMessage = slosDsrForm.i18n.errorGeneric;

			if (xhr.responseJSON && xhr.responseJSON.message) {
				errorMessage = xhr.responseJSON.message;
			} else if (xhr.responseJSON && xhr.responseJSON.code) {
				// Handle specific error codes
				if (xhr.responseJSON.code === 'rate_limit_exceeded') {
					errorMessage = slosDsrForm.i18n.rateLimitError;
				}
			} else if (status === 'timeout' || status === 'error') {
				errorMessage = slosDsrForm.i18n.networkError;
			}

			this.showError(errorMessage);
			this.scrollToTop();
		},

		/**
		 * Show error message
		 *
		 * @param message
		 */
		showError(message) {
			$('.slos-dsr-error-message').text(message);
			this.errorMessage.show();
		},

		/**
		 * Hide all messages
		 */
		hideMessages() {
			this.successMessage.hide();
			this.errorMessage.hide();
		},

		/**
		 * Set submitting state
		 *
		 * @param isSubmitting
		 */
		setSubmitting(isSubmitting) {
			if (isSubmitting) {
				this.submitButton
					.prop('disabled', true)
					.attr('aria-busy', 'true');
				this.submitButton.find('.slos-btn-text').hide();
				this.submitButton.find('.slos-btn-spinner').show();
			} else {
				this.submitButton
					.prop('disabled', false)
					.attr('aria-busy', 'false');
				this.submitButton.find('.slos-btn-text').show();
				this.submitButton.find('.slos-btn-spinner').hide();
			}
		},

		/**
		 * Update SLA notice
		 */
		updateSlaNotice() {
			const regulation = $('#slos-dsr-regulation').val();
			const slaText = this.getSlaNotice(regulation);
			$('.slos-sla-days').html(slaText);
		},

		/**
		 * Get SLA notice for regulation
		 *
		 * @param regulation
		 */
		getSlaNotice(regulation) {
			const slaMap = {
				GDPR: this.wrapper.data('sla-gdpr'),
				CCPA: this.wrapper.data('sla-ccpa'),
				LGPD: this.wrapper.data('sla-lgpd'),
				'UK-GDPR': this.wrapper.data('sla-gdpr'),
				PIPEDA: this.wrapper.data('sla-gdpr'),
				POPIA: this.wrapper.data('sla-gdpr'),
			};

			const days = slaMap[regulation] || 30;
			return slosDsrForm.i18n.slaNotice.replace(
				'%s',
				'<strong>' + days + '</strong>'
			);
		},

		/**
		 * Initialize request type descriptions
		 */
		initRequestTypeDescriptions() {
			const self = this;

			// Populate descriptions data
			$('#slos-dsr-type option').each(function () {
				const value = $(this).val();
				if (value && slosDsrForm.requestTypes[value]) {
					$(this).data(
						'description',
						slosDsrForm.requestTypes[value].description
					);
				}
			});
		},

		/**
		 * Update request type description
		 *
		 * @param type
		 */
		updateRequestTypeDescription(type) {
			const $help = $('.slos-request-type-description');

			if (type && slosDsrForm.requestTypes[type]) {
				$help.text(slosDsrForm.requestTypes[type].description).show();
			} else {
				$help.text('').hide();
			}
		},

		/**
		 * Update character count
		 *
		 * @param $textarea
		 */
		updateCharacterCount($textarea) {
			const current = $textarea.val().length;
			const $counter = $('.slos-char-current');
			$counter.text(current);

			// Warn if approaching limit
			if (current > 4500) {
				$counter.addClass('slos-char-warning');
			} else {
				$counter.removeClass('slos-char-warning');
			}
		},

		/**
		 * Scroll to top of form
		 */
		scrollToTop() {
			$('html, body').animate(
				{
					scrollTop: this.wrapper.offset().top - 100,
				},
				500
			);
		},
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function () {
		SlosDsrForm.init();
	});
})(jQuery);
