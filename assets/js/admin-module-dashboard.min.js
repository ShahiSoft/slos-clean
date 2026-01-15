/**
 * Module Dashboard - Premium JavaScript
 *
 * Interactive functionality for the premium module dashboard.
 * Handles filtering, searching, toggling, and animations.
 *
 * @package
 * @subpackage  Assets/JS
 * @version     3.0.1
 */

(function ($) {
	'use strict';

	/**
	 * Module Dashboard Controller
	 */
	const ModuleDashboard = {
		/**
		 * Initialize the dashboard
		 */
		init() {
			this.cacheDom();
			this.bindEvents();
			this.initAnimations();
		},

		/**
		 * Cache DOM elements
		 */
		cacheDom() {
			// Premium layout selectors
			this.$grid = $('.shahi-modules-grid-premium');
			this.$cards = $('.shahi-module-card-premium');
			this.$search = $('#shahi-module-search');
			this.$searchClear = $('.shahi-search-clear');
			this.$filterBtns = $('.shahi-filter-btn');
			this.$viewBtns = $('.shahi-view-btn');
			this.$toggles = $('.shahi-module-toggle-input');
			this.$bulkEnable = $('.shahi-bulk-enable');
			this.$bulkDisable = $('.shahi-bulk-disable');
			this.$emptyState = $('.shahi-empty-state');
			this.$loadingOverlay = $('.shahi-loading-overlay');
			this.$refreshBtn = $('[data-action="refresh"]');
			this.$infoBtns = $('.shahi-info-btn');
		},

		/**
		 * Module detailed descriptions for info popups
		 */
		moduleDescriptions: {
			'consent-management': {
				title: 'Consent Management',
				shortDesc:
					'GDPR-compliant consent management system with audit logs, user preferences, and compliance tracking.',
				fullDesc:
					'A comprehensive consent management platform that helps your website comply with GDPR, CCPA, and other privacy regulations. This module provides a customizable cookie consent banner that appears to visitors, allowing them to choose which types of cookies to accept.',
				features: [
					'Customizable consent banner with multiple templates and themes',
					'Granular cookie categories (Necessary, Analytics, Marketing, Preferences)',
					'Automatic script blocking until consent is given',
					'Consent audit logging with timestamps and IP addresses',
					'User preference center for managing consent choices',
					'Geolocation-based banner display rules',
					'Integration with Google Tag Manager and analytics platforms',
					'Consent export for compliance audits',
				],
				settingsPage: 'slos-compliance',
			},
			'dsr-portal': {
				title: 'DSR Portal (Data Subject Requests)',
				shortDesc:
					'Handle GDPR data subject requests including access, deletion, and portability requests.',
				fullDesc:
					'The DSR Portal module provides a complete workflow system for managing Data Subject Access Requests (DSARs) as required by GDPR Article 15-22. It enables visitors to submit requests for accessing, correcting, or deleting their personal data.',
				features: [
					'Public-facing request submission form with customizable fields',
					'Identity verification workflow before processing requests',
					'Automated email notifications at each stage of the process',
					'Request tracking dashboard with status management',
					'Configurable SLA timers (default 30 days per GDPR)',
					'Data export in portable formats (JSON, CSV)',
					'Request type support: Access, Deletion, Rectification, Portability',
					'Audit trail for all request activities',
				],
				settingsPage: 'slos-requests',
			},
			'legal-docs': {
				title: 'Legal Documents Generator',
				shortDesc:
					'Generate and manage legal documents for your website including Privacy Policy and Terms of Service.',
				fullDesc:
					'The Legal Documents module helps you create professional, legally-compliant documents for your website. Using your Company Profile data, it generates customized Privacy Policies, Terms of Service, Cookie Policies, and other legal documents.',
				features: [
					'Auto-generated Privacy Policy based on your data practices',
					'Terms of Service document with customizable clauses',
					'Cookie Policy synchronized with Consent Management settings',
					'GDPR-specific disclosures and rights information',
					'Document versioning and change history',
					'PDF export for record-keeping',
					'Automatic placeholders filled from Company Profile',
					'Multi-language document support with WPML integration',
				],
				settingsPage: 'slos-documents',
			},
			'accessibility-scanner': {
				title: 'Accessibility Scanner',
				shortDesc:
					'Scan your website for WCAG accessibility issues and generate compliance reports.',
				fullDesc:
					'A comprehensive accessibility auditing tool that scans your WordPress content for WCAG 2.1 compliance issues. It helps identify barriers that may prevent users with disabilities from accessing your website content.',
				features: [
					'Automated scanning of pages, posts, and custom post types',
					'WCAG 2.1 Level A, AA, and AAA checks',
					'Issue categorization by severity (Error, Warning, Notice)',
					'Detailed remediation guidance for each issue',
					'Alt text quality analysis for images',
					'Heading structure and semantic HTML validation',
					'Color contrast checking for text readability',
					'Form accessibility validation (labels, ARIA)',
					'Accessibility statement generator',
					'Scheduled automatic scans',
				],
				settingsPage: 'slos-accessibility-settings',
			},
		},

		/**
		 * Bind event handlers
		 */
		bindEvents() {
			this.$search.on('input', this.handleSearch.bind(this));
			this.$searchClear.on('click', this.clearSearch.bind(this));
			this.$filterBtns.on('click', this.handleFilter.bind(this));
			this.$viewBtns.on('click', this.handleViewChange.bind(this));
			this.$toggles.on('change', this.handleToggle.bind(this));
			this.$bulkEnable.on('click', () => this.handleBulkAction('enable'));
			this.$bulkDisable.on('click', () =>
				this.handleBulkAction('disable')
			);
			this.$refreshBtn.on('click', this.handleRefresh.bind(this));

			// Use event delegation for info buttons (more reliable)
			$(document).on(
				'click',
				'.shahi-info-btn',
				this.handleInfoClick.bind(this)
			);

			// Card hover effect
			this.$cards.on('mouseenter', this.handleCardHover.bind(this));
			this.$cards.on('mousemove', this.handleCardMouseMove.bind(this));
			this.$cards.on('mouseleave', this.handleCardLeave.bind(this));
		},

		/**
		 * Initialize animations
		 */
		initAnimations() {
			// Stagger card entrance animation
			this.$cards.each((index, card) => {
				setTimeout(() => {
					$(card)
						.css({
							opacity: 0,
							transform: 'translateY(30px) scale(0.95)',
						})
						.animate(
							{
								opacity: 1,
							},
							400,
							() => {
								$(card).css({
									transform: 'translateY(0) scale(1)',
									transition:
										'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)',
								});
							}
						);
				}, index * 50);
			});
		},

		/**
		 * Handle search input
		 *
		 * @param e
		 */
		handleSearch(e) {
			const query = $(e.target).val().toLowerCase().trim();

			// Show/hide clear button
			this.$searchClear.toggle(query.length > 0);

			// Filter cards
			let visibleCount = 0;

			this.$cards.each((index, card) => {
				const $card = $(card);
				const title = $card
					.find('.shahi-module-title')
					.text()
					.toLowerCase();
				const description = $card
					.find('.shahi-module-description')
					.text()
					.toLowerCase();
				const category = ($card.data('category') || '').toLowerCase();

				const matches =
					title.includes(query) ||
					description.includes(query) ||
					category.includes(query);

				if (matches) {
					$card.fadeIn(300);
					visibleCount++;
				} else {
					$card.fadeOut(300);
				}
			});

			// Show empty state if no results
			this.$emptyState.toggle(visibleCount === 0);
		},

		/**
		 * Clear search
		 */
		clearSearch() {
			this.$search.val('').trigger('input');
			this.$searchClear.hide();
		},

		/**
		 * Handle refresh button click
		 */
		handleRefresh() {
			this.showLoading();
			// Reload the page to refresh module data from server
			window.location.reload();
		},

		/**
		 * Handle info button click - show module details
		 *
		 * @param e
		 */
		handleInfoClick(e) {
			e.preventDefault();
			e.stopPropagation();

			const $btn = $(e.currentTarget);
			const $card = $btn.closest('.shahi-module-card-premium');
			const moduleSlug = $btn.data('module-slug') || $card.data('module');
			const moduleName = $card.find('.shahi-module-title').text();
			const moduleCategory = $card.data('category') || 'compliance';
			const moduleStatus = $card.data('status');
			const usageCount =
				$card.find('.shahi-stat-value').first().text() || '0';
			const perfScore =
				$card.find('.shahi-stat-value').last().text() || '0%';

			// Get detailed description from our descriptions object
			const moduleInfo = this.moduleDescriptions[moduleSlug] || {
				title: moduleName,
				shortDesc: $card.find('.shahi-module-description').text(),
				fullDesc:
					'This module extends the functionality of Shahi LegalFlowSuite.',
				features: ['Module functionality as described above'],
			};

			// Build features list HTML
			const featuresHtml = moduleInfo.features
				.map(
					(feature) =>
						`<li><span class="dashicons dashicons-yes-alt"></span>${feature}</li>`
				)
				.join('');

			// Create info modal with detailed content
			const modalHtml = `
                <div class="shahi-v3-info-modal-overlay">
                    <div class="shahi-v3-info-modal shahi-v3-info-modal-detailed">
                        <div class="shahi-v3-info-modal-header">
                            <div class="shahi-v3-info-modal-title-wrap">
                                <span class="shahi-v3-info-modal-icon dashicons dashicons-admin-plugins"></span>
                                <div>
                                    <h3>${moduleInfo.title}</h3>
                                    <span class="shahi-v3-info-modal-category">${moduleCategory}</span>
                                </div>
                            </div>
                            <button type="button" class="shahi-v3-info-modal-close">&times;</button>
                        </div>
                        <div class="shahi-v3-info-modal-body">
                            <div class="shahi-v3-info-section">
                                <h4>Overview</h4>
                                <p class="shahi-v3-info-full-desc">${
									moduleInfo.fullDesc
								}</p>
                            </div>
                            
                            <div class="shahi-v3-info-section">
                                <h4>Key Features</h4>
                                <ul class="shahi-v3-info-features">
                                    ${featuresHtml}
                                </ul>
                            </div>
                            
                            <div class="shahi-v3-info-stats-grid">
                                <div class="shahi-v3-info-stat-box">
                                    <span class="shahi-v3-info-stat-icon dashicons dashicons-chart-bar"></span>
                                    <div class="shahi-v3-info-stat-content">
                                        <span class="shahi-v3-info-stat-value">${usageCount}</span>
                                        <span class="shahi-v3-info-stat-label">Total Uses</span>
                                    </div>
                                </div>
                                <div class="shahi-v3-info-stat-box">
                                    <span class="shahi-v3-info-stat-icon dashicons dashicons-performance"></span>
                                    <div class="shahi-v3-info-stat-content">
                                        <span class="shahi-v3-info-stat-value">${perfScore}</span>
                                        <span class="shahi-v3-info-stat-label">Performance</span>
                                    </div>
                                </div>
                                <div class="shahi-v3-info-stat-box">
                                    <span class="shahi-v3-info-stat-icon dashicons dashicons-flag"></span>
                                    <div class="shahi-v3-info-stat-content">
                                        <span class="shahi-v3-info-stat-value status-${moduleStatus}">${
				moduleStatus === 'active' ? 'Active' : 'Inactive'
			}</span>
                                        <span class="shahi-v3-info-stat-label">Status</span>
                                    </div>
                                </div>
                                <div class="shahi-v3-info-stat-box">
                                    <span class="shahi-v3-info-stat-icon dashicons dashicons-category"></span>
                                    <div class="shahi-v3-info-stat-content">
                                        <span class="shahi-v3-info-stat-value">${moduleCategory}</span>
                                        <span class="shahi-v3-info-stat-label">Category</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="shahi-v3-info-modal-footer">
                            <button type="button" class="shahi-v3-btn shahi-v3-btn-secondary shahi-v3-info-modal-close-btn">Close</button>
                        </div>
                    </div>
                </div>
            `;

			const $modal = $(modalHtml);
			$('body').append($modal);

			// Animate in
			setTimeout(() => $modal.addClass('show'), 10);

			// Close handlers
			const closeModal = () => {
				$modal.removeClass('show');
				setTimeout(() => $modal.remove(), 300);
			};

			$modal
				.find(
					'.shahi-v3-info-modal-close, .shahi-v3-info-modal-close-btn'
				)
				.on('click', closeModal);
			$modal.on('click', function (e) {
				if ($(e.target).hasClass('shahi-v3-info-modal-overlay')) {
					closeModal();
				}
			});

			// Close on Escape key
			$(document).on('keydown.infoModal', function (e) {
				if (e.key === 'Escape') {
					closeModal();
					$(document).off('keydown.infoModal');
				}
			});
		},

		/**
		 * Handle filter button click
		 *
		 * @param e
		 */
		handleFilter(e) {
			const $btn = $(e.currentTarget);
			const filter = $btn.data('filter');

			// Update active state
			this.$filterBtns.removeClass('active');
			$btn.addClass('active');

			// Apply filter
			let visibleCount = 0;

			this.$cards.each((index, card) => {
				const $card = $(card);
				const status = $card.data('status');

				let show = false;

				if (filter === 'all') {
					show = true;
				} else if (filter === 'active' && status === 'active') {
					show = true;
				} else if (filter === 'inactive' && status === 'inactive') {
					show = true;
				}

				if (show) {
					$card.fadeIn(300);
					visibleCount++;
				} else {
					$card.fadeOut(300);
				}
			});

			// Show empty state if no results
			this.$emptyState.toggle(visibleCount === 0);
		},

		/**
		 * Handle view change (grid/list)
		 *
		 * @param e
		 */
		handleViewChange(e) {
			const $btn = $(e.currentTarget);
			const view = $btn.data('view');

			// Update active state
			this.$viewBtns.removeClass('active');
			$btn.addClass('active');

			// Apply view
			this.$grid.attr('data-view', view);

			// Re-trigger entrance animation
			if (view === 'list') {
				this.$cards.each((index, card) => {
					$(card).css({
						animation: `slideInLeft 0.4s ease ${index * 30}ms both`,
					});
				});
			} else {
				this.$cards.each((index, card) => {
					$(card).css({
						animation: `fadeInUp 0.4s ease ${index * 30}ms both`,
					});
				});
			}
		},

		/**
		 * Handle module toggle
		 *
		 * @param e
		 */
		handleToggle(e) {
			const $toggle = $(e.target);
			const $card = $toggle.closest('.shahi-module-card-premium');
			const moduleSlug = $toggle.data('module');
			const isEnabled = $toggle.is(':checked');

			// Disable toggle during request
			$toggle.prop('disabled', true);

			// Show loading
			this.showLoading();

			// Send AJAX request
			$.ajax({
				url: shahiModuleDashboard.ajaxUrl,
				type: 'POST',
				data: {
					action: 'shahi_toggle_module_premium',
					nonce: shahiModuleDashboard.nonce,
					module: moduleSlug,
					enabled: isEnabled ? 1 : 0,
				},
				success: (response) => {
					if (response.success) {
						// Update card state
						if (isEnabled) {
							$card.removeClass('inactive').addClass('active');
							$card.attr('data-status', 'active');
						} else {
							$card.removeClass('active').addClass('inactive');
							$card.attr('data-status', 'inactive');
						}

						// Update toggle label text
						const $label = $card.find('.shahi-toggle-label');
						if ($label.length) {
							$label.text(
								isEnabled
									? shahiModuleDashboard.i18n.enabledText ||
											'Enabled'
									: shahiModuleDashboard.i18n.disabledText ||
											'Disabled'
							);
						}

						// Update status badge
						const $statusSpan = $card.find(
							'.shahi-module-status-badge span'
						);
						if ($statusSpan.length) {
							if (isEnabled) {
								$statusSpan
									.removeClass('shahi-status-inactive')
									.addClass('shahi-status-active');
								$statusSpan
									.contents()
									.filter(function () {
										return this.nodeType === 3;
									})
									.last()
									.replaceWith(
										shahiModuleDashboard.i18n.activeText ||
											'Active'
									);
							} else {
								$statusSpan
									.removeClass('shahi-status-active')
									.addClass('shahi-status-inactive');
								$statusSpan
									.contents()
									.filter(function () {
										return this.nodeType === 3;
									})
									.last()
									.replaceWith(
										shahiModuleDashboard.i18n
											.inactiveText || 'Inactive'
									);
							}
						}

						// Show success notification
						this.showNotification(response.data.message, 'success');

						// Update stats
						this.updateStats();

						// Reload page to ensure UI reflects saved state
						setTimeout(() => window.location.reload(), 500);
					} else {
						// Revert toggle on error
						$toggle.prop('checked', !isEnabled);
						this.showNotification(
							response.data.message || 'An error occurred',
							'error'
						);
					}
				},
				error: () => {
					// Revert toggle on error
					$toggle.prop('checked', !isEnabled);
					this.showNotification(
						'Connection error. Please try again.',
						'error'
					);
				},
				complete: () => {
					$toggle.prop('disabled', false);
					this.hideLoading();
				},
			});
		},

		/**
		 * Handle bulk actions
		 *
		 * @param action
		 */
		handleBulkAction(action) {
			const modulesToToggle = [];
			const enableAction = action === 'enable';

			// Get all modules that need toggling
			this.$cards.each((index, card) => {
				const $card = $(card);
				const status = $card.data('status');
				const moduleSlug = $card.data('module');

				// Only toggle if status doesn't match desired action
				if (
					(enableAction && status === 'inactive') ||
					(!enableAction && status === 'active')
				) {
					modulesToToggle.push(moduleSlug);
				}
			});

			if (modulesToToggle.length === 0) {
				this.showNotification(
					enableAction
						? 'All modules are already enabled'
						: 'All modules are already disabled',
					'info'
				);
				return;
			}

			// Confirm action
			const confirmMsg = enableAction
				? `Enable ${modulesToToggle.length} module(s)?`
				: `Disable ${modulesToToggle.length} module(s)?`;

			if (!confirm(confirmMsg)) {
				return;
			}

			// Show loading
			this.showLoading();

			// Send AJAX request
			$.ajax({
				url: shahiModuleDashboard.ajaxUrl,
				type: 'POST',
				data: {
					action: 'shahi_bulk_module_action',
					nonce: shahiModuleDashboard.nonce,
					action_type: action,
					modules: modulesToToggle,
				},
				success: (response) => {
					if (response.success) {
						// Update all affected cards
						modulesToToggle.forEach((slug) => {
							const $card = this.$cards.filter(
								`[data-module="${slug}"]`
							);
							const $toggle = $card.find(
								'.shahi-module-toggle-input'
							);

							if (enableAction) {
								$card
									.removeClass('inactive')
									.addClass('active');
								$card.attr('data-status', 'active');
								$toggle.prop('checked', true);
							} else {
								$card
									.removeClass('active')
									.addClass('inactive');
								$card.attr('data-status', 'inactive');
								$toggle.prop('checked', false);
							}
						});

						this.showNotification(response.data.message, 'success');
						this.updateStats();
					} else {
						this.showNotification(
							response.data.message || 'An error occurred',
							'error'
						);
					}
				},
				error: () => {
					this.showNotification(
						'Connection error. Please try again.',
						'error'
					);
				},
				complete: () => {
					this.hideLoading();
				},
			});
		},

		/**
		 * Handle card hover effect
		 *
		 * @param e
		 */
		handleCardHover(e) {
			const $card = $(e.currentTarget);
			$card.addClass('is-hovered');
		},

		/**
		 * Handle card mouse move (3D effect)
		 *
		 * @param e
		 */
		handleCardMouseMove(e) {
			const $card = $(e.currentTarget);
			const rect = e.currentTarget.getBoundingClientRect();
			const x = e.clientX - rect.left;
			const y = e.clientY - rect.top;
			const centerX = rect.width / 2;
			const centerY = rect.height / 2;
			const rotateX = (y - centerY) / 20;
			const rotateY = (centerX - x) / 20;

			$card.css({
				transform: `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`,
			});

			// Update glow position
			const $glow = $card.find('.shahi-card-glow');
			$glow.css({
				top: `${y - rect.height}px`,
				left: `${x - rect.width}px`,
			});
		},

		/**
		 * Handle card leave
		 *
		 * @param e
		 */
		handleCardLeave(e) {
			const $card = $(e.currentTarget);
			$card.removeClass('is-hovered');
			$card.css({
				transform: '',
			});
		},

		/**
		 * Update statistics
		 */
		updateStats() {
			const total = this.$cards.length;
			const active = this.$cards.filter('.active').length;
			const inactive = total - active;
			const activationRate =
				total > 0 ? Math.round((active / total) * 100) : 0;

			// Update stat cards
			$(
				'.shahi-stats-row .shahi-stat-card:nth-child(1) .shahi-stat-number'
			).text(total);
			$(
				'.shahi-stats-row .shahi-stat-card:nth-child(2) .shahi-stat-number'
			).text(active);
			$(
				'.shahi-stats-row .shahi-stat-card:nth-child(3) .shahi-stat-number'
			).text(inactive);

			// Update activation rate badge
			$(
				'.shahi-stats-row .shahi-stat-card:nth-child(2) .shahi-trend-up'
			).text(activationRate + '%');

			// Update filter counts
			$('.shahi-filter-btn[data-filter="all"] .shahi-filter-count').text(
				total
			);
			$(
				'.shahi-filter-btn[data-filter="active"] .shahi-filter-count'
			).text(active);
			$(
				'.shahi-filter-btn[data-filter="inactive"] .shahi-filter-count'
			).text(inactive);
		},

		/**
		 * Show loading overlay
		 */
		showLoading() {
			this.$loadingOverlay.fadeIn(200);
		},

		/**
		 * Hide loading overlay
		 */
		hideLoading() {
			this.$loadingOverlay.fadeOut(200);
		},

		/**
		 * Show notification
		 *
		 * @param message
		 * @param type
		 */
		showNotification(message, type = 'info') {
			// Create notification element
			const $notification = $('<div>', {
				class: `shahi-notification shahi-notification-${type}`,
				html: `
                    <span class="shahi-notification-icon dashicons ${this.getNotificationIcon(
						type
					)}"></span>
                    <span class="shahi-notification-message">${message}</span>
                `,
			});

			// Add to page
			$('body').append($notification);

			// Animate in
			setTimeout(() => {
				$notification.addClass('show');
			}, 10);

			// Auto-remove after 3 seconds
			setTimeout(() => {
				$notification.removeClass('show');
				setTimeout(() => {
					$notification.remove();
				}, 300);
			}, 3000);
		},

		/**
		 * Get notification icon based on type
		 *
		 * @param type
		 */
		getNotificationIcon(type) {
			const icons = {
				success: 'dashicons-yes-alt',
				error: 'dashicons-dismiss',
				warning: 'dashicons-warning',
				info: 'dashicons-info',
			};
			return icons[type] || icons.info;
		},
	};

	/**
	 * Initialize on document ready
	 */
	$(document).ready(() => {
		// Support module dashboard layout
		if (
			$('.shahi-module-dashboard').length ||
			$('.shahi-modules-grid-premium').length
		) {
			ModuleDashboard.init();
		}
	});
})(jQuery);
