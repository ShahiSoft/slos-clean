/**
 * Onboarding Modal JavaScript
 *
 * Interactive functionality for multi-step onboarding wizard.
 *
 * @package
 * @subpackage Assets/JS
 * @since      1.0.0
 */

(function ($) {
	'use strict';

	/**
	 * Onboarding wizard controller
	 */
	window.ShahiOnboarding = {
		/**
		 * Current step number
		 */
		currentStep: 1,

		/**
		 * Total number of steps
		 */
		totalSteps: 5,

		/**
		 * Purpose-based module recommendations
		 */
		moduleRecommendations: {
			ecommerce: ['analytics', 'cache', 'security'],
			blog: ['analytics', 'cache'],
			business: ['analytics', 'security', 'notifications'],
			portfolio: ['analytics', 'cache'],
			membership: ['analytics', 'security', 'notifications'],
			other: ['analytics'],
		},

		/**
		 * Initialize the onboarding wizard
		 */
		init() {
			this.cacheElements();

			// Ensure only first step is active on init.
			this.$steps.removeClass('active');
			$('.shahi-onboarding-step[data-step="1"]').addClass('active');

			this.bindEvents();
			this.updateProgress();
			this.updateButtons();
		},

		/**
		 * Cache DOM elements
		 */
		cacheElements() {
			this.$overlay = $('#shahi-onboarding-overlay');
			this.$modal = $('.shahi-onboarding-modal');
			this.$steps = $('.shahi-onboarding-step');
			this.$progressFill = $('#shahi-progress-fill');
			this.$currentStepText = $('#shahi-current-step');
			this.$prevBtn = $('#shahi-prev-btn');
			this.$nextBtn = $('#shahi-next-btn');
			this.$finishBtn = $('#shahi-finish-btn');
			this.$skipBtn = $('#shahi-onboarding-skip');
		},

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			const self = this;

			// Navigation buttons
			this.$prevBtn.on('click', function () {
				self.previousStep();
			});

			this.$nextBtn.on('click', function () {
				self.nextStep();
			});

			this.$finishBtn.on('click', function () {
				self.finish();
			});

			// Skip button
			this.$skipBtn.on('click', function () {
				self.skip();
			});

			// Purpose selection - auto-recommend modules
			$('input[name="purpose"]').on('change', function () {
				self.recommendModules($(this).val());
			});

			// Premium module card toggle functionality
			$(document).on('change', '.shahi-onboarding-module-toggle', function () {
				const $toggle = $(this);
				const $card = $toggle.closest('.shahi-onboarding-module-card');

				if ($toggle.is(':checked')) {
					$card.addClass('active');
				} else {
					$card.removeClass('active');
				}
			});

			// Card click to toggle (anywhere on card)
			$(document).on('click', '.shahi-onboarding-module-card', function (e) {
				// Don't toggle if clicking directly on the checkbox/label
				if (
					$(e.target).is('.shahi-onboarding-module-toggle') ||
					$(e.target).closest('.shahi-toggle-switch-premium').length
				) {
					return;
				}

				const $card = $(this);
				const $toggle = $card.find('.shahi-onboarding-module-toggle');

				$toggle.prop('checked', !$toggle.is(':checked')).trigger('change');
			});

			// ESC key to close
			$(document).on('keyup', function (e) {
				if (e.key === 'Escape' && self.$overlay.is(':visible')) {
					self.skip();
				}
			});
		},

		/**
		 * Go to previous step
		 */
		previousStep() {
			if (this.currentStep > 1) {
				this.currentStep--;
				this.showStep(this.currentStep);
			}
		},

		/**
		 * Go to next step
		 */
		nextStep() {
			// Validate current step
			if (!this.validateStep(this.currentStep)) {
				return;
			}

			if (this.currentStep < this.totalSteps) {
				this.currentStep++;
				this.showStep(this.currentStep);
			}
		},

		/**
		 * Show specific step
		 *
		 * @param stepNumber
		 */
		showStep(stepNumber) {
			const self = this;

			// Hide all steps
			this.$steps.removeClass('active');

			// Show target step with animation
			setTimeout(function () {
				$('.shahi-onboarding-step[data-step="' + stepNumber + '"]').addClass(
					'active'
				);
			}, 50);

			// Update progress
			this.updateProgress();

			// Update button states
			this.updateButtons();

			// Trigger confetti on final step
			if (stepNumber === this.totalSteps) {
				this.triggerConfetti();
			}
		},

		/**
		 * Update progress indicator
		 */
		updateProgress() {
			const percentage = (this.currentStep / this.totalSteps) * 100;

			this.$progressFill.css('width', percentage + '%');
			this.$currentStepText.text(this.currentStep);
		},

		/**
		 * Update button states
		 */
		updateButtons() {
			// Previous button
			if (this.currentStep === 1) {
				this.$prevBtn.prop('disabled', true);
			} else {
				this.$prevBtn.prop('disabled', false);
			}

			// Next/Finish buttons
			if (this.currentStep === this.totalSteps) {
				this.$nextBtn.hide();
				this.$finishBtn.show();
			} else {
				this.$nextBtn.show();
				this.$finishBtn.hide();
			}
		},

		/**
		 * Validate step before proceeding
		 *
		 * @param stepNumber
		 */
		validateStep(stepNumber) {
			let isValid = true;

			switch (stepNumber) {
				case 2: // Purpose selection
					if ($('input[name="purpose"]:checked').length === 0) {
						this.showNotice('Please select your website purpose.', 'warning');
						isValid = false;
					}
					break;

				case 3: // Module selection
					if ($('input[name="modules[]"]:checked').length === 0) {
						this.showNotice('Please select at least one module.', 'warning');
						isValid = false;
					}
					break;
			}

			return isValid;
		},

		/**
		 * Recommend modules based on purpose
		 *
		 * @param purpose
		 */
		recommendModules(purpose) {
			const recommended = this.moduleRecommendations[purpose] || [];

			// Uncheck all modules first and remove active class from cards
			$('input[name="modules[]"]').prop('checked', false);
			$('.shahi-onboarding-module-card').removeClass('active');

			// Check recommended modules and add active class to their cards
			recommended.forEach(function (moduleKey) {
				const $checkbox = $(
					'input[name="modules[]"][value="' + moduleKey + '"]'
				);
				$checkbox.prop('checked', true);
				$checkbox.closest('.shahi-onboarding-module-card').addClass('active');
			});
		},

		/**
		 * Finish onboarding
		 */
		finish() {
			const self = this;

			// Collect data
			const data = {
				action: 'shahi_save_onboarding',
				nonce: shahiOnboardingData.nonce,
				purpose: $('input[name="purpose"]:checked').val(),
				modules: $('input[name="modules[]"]:checked')
					.map(function () {
						return $(this).val();
					})
					.get(),
				settings: {
					enable_analytics: $('input[name="settings[enable_analytics]"]').is(
						':checked'
					)
						? 1
						: 0,
					enable_notifications: $(
						'input[name="settings[enable_notifications]"]'
					).is(':checked')
						? 1
						: 0,
				},
			};

			// Show loading state
			this.$finishBtn
				.prop('disabled', true)
				.html(
					'<span class="dashicons dashicons-update spin-animation"></span> Saving...'
				);

			// Send AJAX request
			$.ajax({
				url: shahiOnboardingData.ajaxUrl,
				type: 'POST',
				data,
				success(response) {
					if (response.success) {
						self.showNotice('Setup completed successfully!', 'success');

						// Close modal after delay
						setTimeout(function () {
							self.closeModal();
						}, 2000);
					} else {
						self.showNotice(
							response.data.message || 'Failed to save settings.',
							'error'
						);
						self.$finishBtn
							.prop('disabled', false)
							.html(
								'Get Started <span class="dashicons dashicons-arrow-right-alt2"></span>'
							);
					}
				},
				error() {
					self.showNotice('An error occurred. Please try again.', 'error');
					self.$finishBtn
						.prop('disabled', false)
						.html(
							'Get Started <span class="dashicons dashicons-arrow-right-alt2"></span>'
						);
				},
			});
		},

		/**
		 * Skip onboarding
		 */
		skip() {
			const self = this;

			if (
				!confirm(
					'Are you sure you want to skip the onboarding wizard? You can run it again from Settings.'
				)
			) {
				return;
			}

			// Send skip request
			$.ajax({
				url: shahiOnboardingData.ajaxUrl,
				type: 'POST',
				data: {
					action: 'shahi_skip_onboarding',
					nonce: shahiOnboardingData.nonce,
				},
				success(response) {
					if (response.success) {
						self.closeModal();
					}
				},
			});
		},

		/**
		 * Close modal
		 */
		closeModal() {
			this.$overlay.fadeOut(300, function () {
				$(this).remove();
			});
		},

		/**
		 * Trigger confetti animation
		 */
		triggerConfetti() {
			const $confetti = $('#shahi-confetti');

			if ($confetti.find('.confetti-piece').length > 0) {
				return; // Already triggered
			}

			// Create confetti pieces
			const colors = [
				'#60a5fa',
				'#3b82f6',
				'#10b981',
				'#f59e0b',
				'#ec4899',
				'#8b5cf6',
			];

			for (let i = 0; i < 50; i++) {
				const $piece = $('<div class="confetti-piece"></div>');
				$piece.css({
					left: Math.random() * 100 + '%',
					background: colors[Math.floor(Math.random() * colors.length)],
					animationDelay: Math.random() * 2 + 's',
					animationDuration: Math.random() * 2 + 2 + 's',
				});
				$confetti.append($piece);
			}
		},

		/**
		 * Show notice message
		 *
		 * @param message
		 * @param type
		 */
		showNotice(message, type) {
			// Remove existing notices
			$('.shahi-onboarding-notice').remove();

			// Create notice element
			const typeClass =
				type === 'success'
					? 'notice-success'
					: type === 'error'
					? 'notice-error'
					: 'notice-warning';

			const $notice = $(
				'<div class="shahi-onboarding-notice ' +
					typeClass +
					'">' +
					message +
					'</div>'
			);

			// Add to modal
			this.$modal.prepend($notice);

			// Auto-remove after 5 seconds
			setTimeout(function () {
				$notice.fadeOut(300, function () {
					$(this).remove();
				});
			}, 5000);
		},
	};

	/**
	 * Initialize when document is ready
	 */
	$(document).ready(function () {
		console.log('SHAHI ONBOARDING JS: jQuery document.ready fired');
		console.log('SHAHI ONBOARDING JS: Looking for #shahi-onboarding-overlay');
		// Check if onboarding overlay exists
		const $overlay = $('#shahi-onboarding-overlay');
		console.log('SHAHI ONBOARDING JS: Overlay found:', $overlay.length > 0);
		if ($overlay.length) {
			console.log('SHAHI ONBOARDING JS: Calling ShahiOnboarding.init()');
			ShahiOnboarding.init();
		} else {
			console.log(
				'SHAHI ONBOARDING JS: No overlay found - onboarding not displayed'
			);
		}
	});
})(jQuery);

/**
 * CSS for inline notices and animations
 */
(function () {
	'use strict';

	// Inject dynamic styles
	const styles = `
		< style id = "shahi-onboarding-dynamic-styles" >
			.shahi - onboarding - notice {
				padding: 15px 20px;
				margin - bottom: 20px;
				border - radius: 8px;
				font - size: 14px;
				animation: slideDown 0.3s ease;
	}

			.shahi - onboarding - notice.notice - success {
				background: rgba( 16, 185, 129, 0.1 );
				border: 1px solid rgba( 16, 185, 129, 0.3 );
				color: #10b981;
	}

			.shahi - onboarding - notice.notice - error {
				background: rgba( 255, 68, 68, 0.1 );
				border: 1px solid rgba( 255, 68, 68, 0.3 );
				color: #ff4444;
	}

			.shahi - onboarding - notice.notice - warning {
				background: rgba( 255, 187, 0, 0.1 );
				border: 1px solid rgba( 255, 187, 0, 0.3 );
				color: #ffbb00;
	}

			@keyframes slideDown {
				from {
					opacity: 0;
					transform: translateY( -10px );
				}
				to {
					opacity: 1;
					transform: translateY( 0 );
				}
	}

			.spin - animation {
				animation: spin 1s linear infinite;
	}

			@keyframes spin {
				to {
					transform: rotate( 360deg );
				}
	}
		< / style >
	`;

	if (document.head) {
		document.head.insertAdjacentHTML('beforeend', styles);
	}

	// Auto-initialize on DOM ready.
	$(document).ready(function () {
		if ('undefined' !== typeof window.ShahiOnboarding) {
			window.ShahiOnboarding.init();
		}
	});
})(jQuery);
