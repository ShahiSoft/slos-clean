/**
 * ShahiLegalFlowSuite - Dashboard Page JavaScript
 *
 * Futuristic dashboard with animated counters, hover effects,
 * smooth transitions, collapsible sections, and AJAX widget refresh.
 *
 * @package
 * @version    3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * Dashboard module
	 */
	const ShahiDashboard = {
		/**
		 * Initialize dashboard
		 */
		init() {
			console.log('ShahiLegalFlowSuite Dashboard: Initializing...');

			this.initAnimatedCounters();
			this.initRefreshButton();
			this.initActivityFeed();
			this.initQuickActions();
			this.initCollapsibleSections();
			this.initOnboardingTrigger();
			this.initTooltips();

			console.log(
				'ShahiLegalFlowSuite Dashboard: Initialized successfully'
			);
		},

		/**
		 * Initialize animated stat counters
		 * Animates numbers from 0 to target value on page load
		 */
		initAnimatedCounters() {
			const $counters = $('.shahi-stat-number[data-value]');

			if ($counters.length === 0) {
				return;
			}

			$counters.each(function () {
				const $counter = $(this);
				const targetValue = parseInt($counter.attr('data-value')) || 0;
				let currentValue = 0;
				const duration = 2000; // 2 seconds
				const steps = 60;
				const increment = targetValue / steps;
				const stepDuration = duration / steps;
				let stepCount = 0;

				// Only animate numbers, not time strings
				if (isNaN(targetValue)) {
					return;
				}

				$counter.text('0');

				var interval = setInterval(function () {
					stepCount++;
					currentValue = Math.min(
						Math.round(increment * stepCount),
						targetValue
					);
					$counter.text(currentValue.toLocaleString());

					if (stepCount >= steps || currentValue >= targetValue) {
						clearInterval(interval);
						$counter.text(targetValue.toLocaleString());
					}
				}, stepDuration);
			});
		},

		/**
		 * Initialize refresh stats button
		 */
		initRefreshButton() {
			const self = this;

			$('[data-action="refresh"]').on('click', function (e) {
				e.preventDefault();
				self.refreshStats($(this));
			});
		},

		/**
		 * Refresh dashboard stats via AJAX
		 *
		 * @param $button
		 */
		refreshStats($button) {
			const self = this;

			// Add loading state
			$button.addClass('shahi-loading');
			$button.prop('disabled', true);

			// Simulate AJAX call (replace with actual AJAX when endpoints are ready)
			setTimeout(function () {
				// Re-animate counters
				self.initAnimatedCounters();

				// Remove loading state
				$button.removeClass('shahi-loading');
				$button.prop('disabled', false);

				// Show success notification
				if (typeof window.ShahiNotify !== 'undefined') {
					window.ShahiNotify.success(
						'Dashboard stats refreshed successfully!'
					);
				}
			}, 1000);

			/* Actual AJAX implementation (uncomment when endpoint is ready)
			$.ajax({
				url: shahiDashboard.ajaxUrl,
				type: 'POST',
				data: {
					action: 'shahi_refresh_dashboard_stats',
					nonce: shahiDashboard.nonce
				},
				success: function(response) {
					if (response.success && response.data.stats) {
						// Update stat values
						$.each(response.data.stats, function(key, value) {
							var $stat = $('[data-stat="' + key + '"]');
							if ($stat.length) {
								$stat.attr('data-value', value);
							}
						});

						// Re-animate counters
						self.initAnimatedCounters();

						if (typeof window.ShahiNotify !== 'undefined') {
							window.ShahiNotify.success('Dashboard stats refreshed!');
						}
					}
				},
				error: function() {
					if (typeof window.ShahiNotify !== 'undefined') {
						window.ShahiNotify.error('Failed to refresh stats. Please try again.');
					}
				},
				complete: function() {
					$button.removeClass('shahi-loading');
					$button.prop('disabled', false);
				}
			});
			*/
		},

		/**
		 * Initialize activity feed
		 */
		initActivityFeed() {
			const $activityItems = $('.shahi-activity-item');

			// Add fade-in animation to activity items
			$activityItems.each(function (index) {
				$(this)
					.css({
						opacity: 0,
						transform: 'translateX(-20px)',
					})
					.delay(index * 50)
					.animate(
						{
							opacity: 1,
						},
						300,
						function () {
							$(this).css('transform', 'translateX(0)');
						}
					);
			});
		},

		/**
		 * Initialize quick actions with hover effects
		 */
		initQuickActions() {
			const $quickActions = $('.shahi-quick-action');

			$quickActions.on('mouseenter', function () {
				$(this).find('.shahi-action-icon').addClass('shahi-wobble');
			});

			$quickActions.on('mouseleave', function () {
				$(this).find('.shahi-action-icon').removeClass('shahi-wobble');
			});
		},

		/**
		 * Initialize collapsible sections
		 */
		initCollapsibleSections() {
			// Add collapse toggle to card headers (if needed in future)
			$('.shahi-card-header').on('dblclick', function () {
				const $cardBody = $(this).next('.shahi-card-body');
				$cardBody.slideToggle(300);
				$(this).toggleClass('shahi-collapsed');
			});
		},

		/**
		 * Initialize onboarding trigger
		 */
		initOnboardingTrigger() {
			$('.shahi-trigger-onboarding').on('click', function (e) {
				e.preventDefault();

				// Reset onboarding and show modal
				if (typeof window.ShahiOnboarding !== 'undefined') {
					window.ShahiOnboarding.show();
				} else {
					console.warn('ShahiOnboarding module not loaded');

					// Fallback: reload page to trigger onboarding
					if (
						confirm(
							'This will reset the onboarding wizard. Continue?'
						)
					) {
						$.ajax({
							url: shahiDashboard.ajaxUrl,
							type: 'POST',
							data: {
								action: 'shahi_restart_onboarding',
								nonce: shahiDashboard.nonce,
							},
							success() {
								window.location.reload();
							},
						});
					}
				}
			});
		},

		/**
		 * Initialize tooltips
		 */
		initTooltips() {
			// Use ShahiComponents tooltip if available
			if (
				typeof window.ShahiComponents !== 'undefined' &&
				typeof window.ShahiComponents.initTooltips === 'function'
			) {
				window.ShahiComponents.initTooltips();
			}
		},

		/**
		 * Format number with thousands separator
		 *
		 * @param num
		 */
		formatNumber(num) {
			return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
		},

		/**
		 * Calculate progress percentage
		 */
		calculateProgress() {
			const $checklistItems = $('.shahi-checklist-item');
			const totalItems = $checklistItems.length;
			const completedItems =
				$checklistItems.filter('.shahi-completed').length;

			if (totalItems === 0) {
				return 0;
			}

			return Math.round((completedItems / totalItems) * 100);
		},

		/**
		 * Update progress bar (if exists)
		 */
		updateProgressBar() {
			const progress = this.calculateProgress();
			const $progressBar = $('.shahi-getting-started-progress');

			if ($progressBar.length) {
				$progressBar.css('width', progress + '%');
				$progressBar.find('.shahi-progress-text').text(progress + '%');
			}
		},
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function () {
		if ($('.shahi-dashboard-page').length > 0) {
			ShahiDashboard.init();
		}
	});

	// Expose to global scope for external access
	window.ShahiDashboard = ShahiDashboard;
})(jQuery);
