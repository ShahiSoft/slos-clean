/**
 * SLOS Accessibility Auto-Fix JavaScript
 *
 * Provides interactive controls and enhancements for accessibility fixes.
 * Works in conjunction with PHP fixers that add data attributes and HTML elements.
 *
 * @package
 * @subpackage Modules\AccessibilityScanner\Fixes
 * @version    1.0.0
 * @since      3.2.0
 *
 * WCAG 2.1 AA Compliance Features:
 * - 2.1.2 No Keyboard Trap - Escape key handlers
 * - 2.2.1 Timing Adjustable - Time extension controls
 * - 2.2.2 Pause, Stop, Hide - Animation pause controls
 * - 2.4.7 Focus Visible - Focus management utilities
 * - 4.1.3 Status Messages - ARIA live region announcer
 */

(function () {
	'use strict';

	/**
	 * SLOS Accessibility Fixes Module
	 */
	const SLOSAccessibilityFixes = {
		/**
		 * Configuration
		 */
		config: {
			prefersReducedMotion: window.matchMedia(
				'(prefers-reduced-motion: reduce)'
			).matches,
			announceDelay: 100,
			defaultTimeExtension: 300, // 5 minutes in seconds
			focusableSelector:
				'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
		},

		/**
		 * Initialize all accessibility fixes
		 */
		init() {
			// Wait for DOM to be ready
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', () =>
					this.setup()
				);
			} else {
				this.setup();
			}
		},

		/**
		 * Setup all handlers
		 */
		setup() {
			this.initKeyboardTrapHandlers();
			this.initAnimationPauseControls();
			this.initTimingControls();
			this.initFocusManagement();
			this.initARIALiveAnnouncer();
			this.initReducedMotionListener();
			this.initModalFocusTraps();
		},

		/* ============================================================
           4.2.1 Keyboard Trap Escape Handlers (WCAG 2.1.2)
           ============================================================ */

		/**
		 * Initialize keyboard trap escape handlers
		 */
		initKeyboardTrapHandlers() {
			// Handle Escape key for modals and dialogs
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape' || e.keyCode === 27) {
					this.handleEscapeKey(e);
				}
			});

			// Add click handlers to close buttons
			document
				.querySelectorAll('[data-slos-escape-trigger]')
				.forEach((btn) => {
					btn.addEventListener('click', (e) => {
						this.closeParentDialog(e.currentTarget);
					});
				});
		},

		/**
		 * Handle Escape key press
		 *
		 * @param e
		 */
		handleEscapeKey(e) {
			// Find active modal or dialog
			const activeModal = document.querySelector(
				'[data-slos-escape-enabled][role="dialog"]:not([hidden]), ' +
					'[data-slos-escape-enabled][role="alertdialog"]:not([hidden]), ' +
					'[data-slos-escape-enabled].modal:not(.hidden):not([style*="display: none"]), ' +
					'[data-slos-escape-enabled].popup:not(.hidden):not([style*="display: none"])'
			);

			if (activeModal) {
				e.preventDefault();
				this.closeModal(activeModal);
			}
		},

		/**
		 * Close a modal dialog
		 *
		 * @param modal
		 */
		closeModal(modal) {
			// Find close button
			const closeBtn = modal.querySelector(
				'[data-slos-escape-trigger], ' +
					'.close, .modal-close, [aria-label*="close"], [aria-label*="Close"]'
			);

			if (closeBtn) {
				closeBtn.click();
			} else {
				// Try to hide modal directly
				modal.setAttribute('hidden', '');
				modal.style.display = 'none';
				modal.classList.add('hidden');

				// Return focus to trigger element
				const triggerId = modal.getAttribute('data-trigger-element');
				if (triggerId) {
					const trigger = document.getElementById(triggerId);
					if (trigger) trigger.focus();
				}
			}

			// Announce to screen readers
			this.announce('Dialog closed');
		},

		/**
		 * Close parent dialog from close button
		 *
		 * @param button
		 */
		closeParentDialog(button) {
			const dialog = button.closest(
				'[role="dialog"], [role="alertdialog"], .modal, .popup'
			);
			if (dialog) {
				this.closeModal(dialog);
			}
		},

		/* ============================================================
           4.2.2 Animation Pause/Play Toggle (WCAG 2.2.2)
           ============================================================ */

		/**
		 * Initialize animation pause controls
		 */
		initAnimationPauseControls() {
			// Animated GIF pause buttons
			document
				.querySelectorAll('.slos-pause-animation')
				.forEach((btn) => {
					btn.addEventListener('click', (e) =>
						this.toggleAnimation(e.currentTarget)
					);
				});

			// Carousel pause buttons
			document.querySelectorAll('.slos-carousel-pause').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.toggleCarousel(e.currentTarget)
				);
			});

			// Marquee pause buttons
			document.querySelectorAll('.slos-pause-marquee').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.toggleMarquee(e.currentTarget)
				);
			});

			// Auto-pause if prefers-reduced-motion
			if (this.config.prefersReducedMotion) {
				this.pauseAllAnimations();
			}
		},

		/**
		 * Toggle animation pause state
		 *
		 * @param button
		 */
		toggleAnimation(button) {
			const container = button.closest('.slos-animation-container');
			if (!container) return;

			const animated = container.querySelector(
				'[data-animated], [data-slos-animation-state]'
			);
			const isPaused = button.getAttribute('aria-pressed') === 'true';

			if (isPaused) {
				// Resume animation
				if (animated) {
					animated.style.animationPlayState = 'running';
					animated.setAttribute(
						'data-slos-animation-state',
						'running'
					);
				}
				button.setAttribute('aria-pressed', 'false');
				button.textContent = '⏸';
				button.setAttribute('aria-label', 'Pause animation');
				this.announce('Animation resumed');
			} else {
				// Pause animation
				if (animated) {
					animated.style.animationPlayState = 'paused';
					animated.setAttribute(
						'data-slos-animation-state',
						'paused'
					);
				}
				button.setAttribute('aria-pressed', 'true');
				button.textContent = '▶';
				button.setAttribute('aria-label', 'Play animation');
				this.announce('Animation paused');
			}
		},

		/**
		 * Toggle carousel auto-play
		 *
		 * @param button
		 */
		toggleCarousel(button) {
			const carousel = button.closest('[data-pause-control]');
			if (!carousel) return;

			const isPaused = button.getAttribute('aria-pressed') === 'true';

			if (isPaused) {
				// Resume carousel
				carousel.setAttribute('data-paused', 'false');
				button.setAttribute('aria-pressed', 'false');
				button.textContent = '⏸ Pause';
				button.setAttribute('aria-label', 'Pause carousel');
				this.triggerCarouselEvent(carousel, 'play');
				this.announce('Carousel resumed');
			} else {
				// Pause carousel
				carousel.setAttribute('data-paused', 'true');
				button.setAttribute('aria-pressed', 'true');
				button.textContent = '▶ Play';
				button.setAttribute('aria-label', 'Play carousel');
				this.triggerCarouselEvent(carousel, 'pause');
				this.announce('Carousel paused');
			}
		},

		/**
		 * Trigger carousel library events
		 *
		 * @param carousel
		 * @param action
		 */
		triggerCarouselEvent(carousel, action) {
			// Slick
			if (
				carousel.classList.contains('slick-slider') &&
				typeof jQuery !== 'undefined'
			) {
				jQuery(carousel).slick(
					action === 'pause' ? 'slickPause' : 'slickPlay'
				);
			}
			// Swiper
			if (carousel.swiper) {
				carousel.swiper.autoplay[
					action === 'pause' ? 'stop' : 'start'
				]();
			}
			// Owl Carousel
			if (
				typeof jQuery !== 'undefined' &&
				jQuery(carousel).data('owl.carousel')
			) {
				jQuery(carousel).trigger(
					action === 'pause'
						? 'stop.owl.autoplay'
						: 'play.owl.autoplay'
				);
			}
			// Generic data attribute approach
			carousel.dispatchEvent(new CustomEvent('slos-carousel-' + action));
		},

		/**
		 * Toggle marquee pause
		 *
		 * @param button
		 */
		toggleMarquee(button) {
			const marquee = button.closest('.slos-accessible-marquee');
			if (!marquee) return;

			const isPaused = marquee.getAttribute('data-paused') === 'true';

			if (isPaused) {
				marquee.setAttribute('data-paused', 'false');
				button.setAttribute('aria-label', 'Pause scrolling text');
				button.textContent = '⏸';
				this.announce('Scrolling text resumed');
			} else {
				marquee.setAttribute('data-paused', 'true');
				button.setAttribute('aria-label', 'Play scrolling text');
				button.textContent = '▶';
				this.announce('Scrolling text paused');
			}
		},

		/**
		 * Pause all animations (for prefers-reduced-motion)
		 */
		pauseAllAnimations() {
			// Pause GIF animations
			document
				.querySelectorAll('.slos-pause-animation')
				.forEach((btn) => {
					if (btn.getAttribute('aria-pressed') !== 'true') {
						this.toggleAnimation(btn);
					}
				});

			// Pause carousels
			document.querySelectorAll('.slos-carousel-pause').forEach((btn) => {
				if (btn.getAttribute('aria-pressed') !== 'true') {
					this.toggleCarousel(btn);
				}
			});

			// Pause marquees
			document
				.querySelectorAll('.slos-accessible-marquee')
				.forEach((marquee) => {
					marquee.setAttribute('data-paused', 'true');
				});
		},

		/* ============================================================
           4.2.3 Timing Control Extend/Cancel (WCAG 2.2.1)
           ============================================================ */

		/**
		 * Initialize timing controls
		 */
		initTimingControls() {
			// Time extension buttons
			document.querySelectorAll('.slos-extend-time').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.extendTime(e.currentTarget)
				);
			});

			// Cancel refresh buttons
			document.querySelectorAll('.slos-cancel-refresh').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.cancelRefresh(e.currentTarget)
				);
			});

			// Session extend buttons
			document.querySelectorAll('.slos-extend-session').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.extendSession(e.currentTarget)
				);
			});

			// Timer pause buttons
			document.querySelectorAll('.slos-timer-pause').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.toggleTimerPause(e.currentTarget)
				);
			});

			// Timer add time buttons
			document.querySelectorAll('.slos-timer-add').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.addTimerTime(e.currentTarget)
				);
			});

			// Keep visible buttons (for auto-dismiss notifications)
			document.querySelectorAll('.slos-keep-visible').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.keepNotificationVisible(e.currentTarget)
				);
			});

			// Notification close buttons
			document
				.querySelectorAll('.slos-notification-close')
				.forEach((btn) => {
					btn.addEventListener('click', (e) =>
						this.closeNotification(e.currentTarget)
					);
				});

			// Pause auto-advance buttons
			document.querySelectorAll('.slos-pause-advance').forEach((btn) => {
				btn.addEventListener('click', (e) =>
					this.toggleAutoAdvance(e.currentTarget)
				);
			});
		},

		/**
		 * Extend time before refresh/redirect
		 *
		 * @param button
		 */
		extendTime(button) {
			const seconds =
				parseInt(button.getAttribute('data-extend-seconds'), 10) ||
				this.config.defaultTimeExtension;

			// Dispatch event for any listening refresh handlers
			document.dispatchEvent(
				new CustomEvent('slos-extend-time', {
					detail: { seconds },
				})
			);

			// Try to modify meta refresh if exists
			const metaRefresh = document.querySelector(
				'meta[http-equiv="refresh"]'
			);
			if (metaRefresh) {
				const content = metaRefresh.getAttribute('content');
				const match = content.match(/^(\d+)(;.*)?$/);
				if (match) {
					const currentTime = parseInt(match[1], 10);
					const newTime = currentTime + seconds;
					const url = match[2] || '';
					metaRefresh.setAttribute('content', newTime + url);
				}
			}

			// Update warning message if present
			const warning = button.closest('.slos-timing-warning');
			if (warning) {
				const textNode = warning.childNodes[0];
				if (textNode && textNode.nodeType === Node.TEXT_NODE) {
					textNode.textContent = textNode.textContent.replace(
						/(\d+) seconds/,
						(match, num) => parseInt(num, 10) + seconds + ' seconds'
					);
				}
			}

			this.announce(
				'Time extended by ' + Math.floor(seconds / 60) + ' minutes'
			);
		},

		/**
		 * Cancel page refresh
		 *
		 * @param button
		 */
		cancelRefresh(button) {
			// Remove meta refresh
			const metaRefresh = document.querySelector(
				'meta[http-equiv="refresh"]'
			);
			if (metaRefresh) {
				metaRefresh.remove();
			}

			// Hide warning
			const warning = button.closest('.slos-timing-warning');
			if (warning) {
				warning.style.display = 'none';
				warning.setAttribute('hidden', '');
			}

			// Dispatch cancel event
			document.dispatchEvent(new CustomEvent('slos-cancel-refresh'));

			this.announce('Page refresh cancelled');
		},

		/**
		 * Extend session timeout
		 *
		 * @param button
		 */
		extendSession(button) {
			// Try to call WordPress heartbeat or custom session extender
			if (typeof wp !== 'undefined' && wp.heartbeat) {
				wp.heartbeat.connectNow();
			}

			// Dispatch event for custom handlers
			document.dispatchEvent(new CustomEvent('slos-extend-session'));

			// Make AJAX call to extend session (if endpoint exists)
			if (
				typeof slosa11yConfig !== 'undefined' &&
				slosa11yConfig.ajaxUrl
			) {
				fetch(slosa11yConfig.ajaxUrl, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body:
						'action=slos_extend_session&nonce=' +
						(slosa11yConfig.nonce || ''),
				}).catch(() => {});
			}

			// Hide session warning
			const warning = button.closest('[data-slos-timing-control]');
			if (warning) {
				warning.style.display = 'none';
			}

			this.announce('Session extended');
		},

		/**
		 * Toggle timer pause state
		 *
		 * @param button
		 */
		toggleTimerPause(button) {
			const container = button.closest('[data-slos-timing-control]');
			const timer = container
				? container.querySelector('[role="timer"]')
				: null;

			const isPaused = button.getAttribute('aria-pressed') === 'true';

			if (isPaused) {
				button.setAttribute('aria-pressed', 'false');
				button.textContent = '⏸ Pause';
				if (timer) {
					timer.dispatchEvent(new CustomEvent('slos-timer-resume'));
				}
				this.announce('Timer resumed');
			} else {
				button.setAttribute('aria-pressed', 'true');
				button.textContent = '▶ Resume';
				if (timer) {
					timer.dispatchEvent(new CustomEvent('slos-timer-pause'));
				}
				this.announce('Timer paused');
			}
		},

		/**
		 * Add time to timer
		 *
		 * @param button
		 */
		addTimerTime(button) {
			const seconds =
				parseInt(button.getAttribute('data-add-seconds'), 10) || 60;
			const container = button.closest('[data-slos-timing-control]');
			const timer = container
				? container.querySelector('[role="timer"]')
				: null;

			if (timer) {
				timer.dispatchEvent(
					new CustomEvent('slos-timer-add', {
						detail: { seconds },
					})
				);
			}

			this.announce(seconds + ' seconds added to timer');
		},

		/**
		 * Keep notification visible (prevent auto-dismiss)
		 *
		 * @param button
		 */
		keepNotificationVisible(button) {
			const notification = button.closest('[data-slos-timing-control]');
			if (notification) {
				notification.removeAttribute('data-auto-dismiss');
				notification.removeAttribute('data-timeout');
				notification.removeAttribute('data-dismiss-after');
				notification.setAttribute('data-kept-visible', 'true');
			}

			// Remove the keep visible button
			button.style.display = 'none';

			this.announce('Notification will remain visible');
		},

		/**
		 * Close notification
		 *
		 * @param button
		 */
		closeNotification(button) {
			const notification = button.closest(
				'[data-slos-timing-control], .notification, .alert, .notice'
			);
			if (notification) {
				notification.style.display = 'none';
				notification.setAttribute('hidden', '');
			}
			this.announce('Notification closed');
		},

		/**
		 * Toggle auto-advance for wizards/steppers
		 *
		 * @param button
		 */
		toggleAutoAdvance(button) {
			const wizard = button.closest('[data-slos-timing-control]');
			const isPaused = button.getAttribute('aria-pressed') === 'true';

			if (isPaused) {
				button.setAttribute('aria-pressed', 'false');
				button.textContent = 'Pause auto-advance';
				if (wizard) {
					wizard.removeAttribute('data-auto-paused');
					wizard.dispatchEvent(
						new CustomEvent('slos-advance-resume')
					);
				}
				this.announce('Auto-advance resumed');
			} else {
				button.setAttribute('aria-pressed', 'true');
				button.textContent = 'Resume auto-advance';
				if (wizard) {
					wizard.setAttribute('data-auto-paused', 'true');
					wizard.dispatchEvent(new CustomEvent('slos-advance-pause'));
				}
				this.announce('Auto-advance paused');
			}
		},

		/* ============================================================
           4.2.4 Focus Management Utilities (WCAG 2.4.7)
           ============================================================ */

		/**
		 * Initialize focus management
		 */
		initFocusManagement() {
			// Track last focused element before modals
			document.addEventListener('focusin', (e) => {
				if (
					!e.target.closest('[role="dialog"], [role="alertdialog"]')
				) {
					this.lastFocusedElement = e.target;
				}
			});

			// Handle focus-visible polyfill behavior
			this.initFocusVisiblePolyfill();
		},

		/**
		 * Simple focus-visible polyfill for older browsers
		 */
		initFocusVisiblePolyfill() {
			let hadKeyboardEvent = false;
			let hadFocusVisibleRecently = false;
			let hadFocusVisibleRecentlyTimeout = null;

			const inputTypesAllowlist = {
				text: true,
				search: true,
				url: true,
				tel: true,
				email: true,
				password: true,
				number: true,
				date: true,
				month: true,
				week: true,
				time: true,
				datetime: true,
				'datetime-local': true,
			};

			const isValidFocusTarget = (el) => {
				if (
					el.tagName === 'INPUT' &&
					inputTypesAllowlist[el.type] &&
					!el.readOnly
				) {
					return true;
				}
				if (el.tagName === 'TEXTAREA' && !el.readOnly) {
					return true;
				}
				if (el.isContentEditable) {
					return true;
				}
				return false;
			};

			const addFocusVisibleClass = (el) => {
				if (el.classList.contains('slos-focus-visible')) {
					el.classList.add('focus-visible');
				}
			};

			const removeFocusVisibleClass = (el) => {
				if (el.classList.contains('focus-visible')) {
					hadFocusVisibleRecently = true;
					el.classList.remove('focus-visible');
					clearTimeout(hadFocusVisibleRecentlyTimeout);
					hadFocusVisibleRecentlyTimeout = setTimeout(() => {
						hadFocusVisibleRecently = false;
					}, 100);
				}
			};

			const onKeyDown = (e) => {
				if (e.metaKey || e.altKey || e.ctrlKey) return;
				hadKeyboardEvent = true;
			};

			const onPointerDown = () => {
				hadKeyboardEvent = false;
			};

			const onFocus = (e) => {
				if (!e.target.classList) return;
				if (hadKeyboardEvent || isValidFocusTarget(e.target)) {
					addFocusVisibleClass(e.target);
				}
			};

			const onBlur = (e) => {
				if (!e.target.classList) return;
				removeFocusVisibleClass(e.target);
			};

			document.addEventListener('keydown', onKeyDown, true);
			document.addEventListener('mousedown', onPointerDown, true);
			document.addEventListener('pointerdown', onPointerDown, true);
			document.addEventListener('touchstart', onPointerDown, true);
			document.addEventListener('focus', onFocus, true);
			document.addEventListener('blur', onBlur, true);
		},

		/**
		 * Move focus to element
		 *
		 * @param element
		 */
		moveFocus(element) {
			if (!element) return;

			if (element.getAttribute('tabindex') === null) {
				element.setAttribute('tabindex', '-1');
			}
			element.focus();
		},

		/**
		 * Get all focusable elements within container
		 *
		 * @param container
		 */
		getFocusableElements(container) {
			return Array.from(
				container.querySelectorAll(this.config.focusableSelector)
			).filter(
				(el) => !el.hasAttribute('disabled') && el.offsetParent !== null
			);
		},

		/* ============================================================
           4.2.5 ARIA Live Region Announcer (WCAG 4.1.3)
           ============================================================ */

		/**
		 * Initialize ARIA live region for announcements
		 */
		initARIALiveAnnouncer() {
			// Create live region if not exists
			let announcer = document.getElementById('slos-a11y-announcer');
			if (!announcer) {
				announcer = document.createElement('div');
				announcer.id = 'slos-a11y-announcer';
				announcer.setAttribute('role', 'status');
				announcer.setAttribute('aria-live', 'polite');
				announcer.setAttribute('aria-atomic', 'true');
				announcer.className = 'screen-reader-text';
				announcer.style.cssText =
					'position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;';
				document.body.appendChild(announcer);
			}
			this.announcer = announcer;

			// Create assertive announcer for urgent messages
			let assertiveAnnouncer = document.getElementById(
				'slos-a11y-announcer-assertive'
			);
			if (!assertiveAnnouncer) {
				assertiveAnnouncer = document.createElement('div');
				assertiveAnnouncer.id = 'slos-a11y-announcer-assertive';
				assertiveAnnouncer.setAttribute('role', 'alert');
				assertiveAnnouncer.setAttribute('aria-live', 'assertive');
				assertiveAnnouncer.setAttribute('aria-atomic', 'true');
				assertiveAnnouncer.className = 'screen-reader-text';
				assertiveAnnouncer.style.cssText =
					'position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden;';
				document.body.appendChild(assertiveAnnouncer);
			}
			this.assertiveAnnouncer = assertiveAnnouncer;
		},

		/**
		 * Announce message to screen readers
		 *
		 * @param message
		 * @param priority
		 */
		announce(message, priority) {
			const announcer =
				priority === 'assertive'
					? this.assertiveAnnouncer
					: this.announcer;
			if (!announcer) return;

			// Clear and set message (with delay to ensure announcement)
			announcer.textContent = '';
			setTimeout(() => {
				announcer.textContent = message;
			}, this.config.announceDelay);
		},

		/* ============================================================
           4.2.6 Reduced Motion Listener (WCAG 2.3.3)
           ============================================================ */

		/**
		 * Initialize reduced motion media query listener
		 */
		initReducedMotionListener() {
			const mediaQuery = window.matchMedia(
				'(prefers-reduced-motion: reduce)'
			);

			// Handle changes
			const handleChange = (e) => {
				this.config.prefersReducedMotion = e.matches;
				if (e.matches) {
					this.pauseAllAnimations();
					this.announce(
						'Animations paused due to reduced motion preference'
					);
				}
			};

			// Use addEventListener with fallback for older browsers
			if (mediaQuery.addEventListener) {
				mediaQuery.addEventListener('change', handleChange);
			} else if (mediaQuery.addListener) {
				mediaQuery.addListener(handleChange);
			}
		},

		/* ============================================================
           Modal Focus Trap Implementation
           ============================================================ */

		/**
		 * Initialize focus traps for modal dialogs
		 */
		initModalFocusTraps() {
			document
				.querySelectorAll(
					'[data-slos-escape-enabled][role="dialog"], [data-slos-escape-enabled][role="alertdialog"]'
				)
				.forEach((dialog) => {
					this.setupFocusTrap(dialog);
				});

			// Watch for new modals
			const observer = new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					mutation.addedNodes.forEach((node) => {
						if (node.nodeType === Node.ELEMENT_NODE) {
							if (
								node.matches &&
								node.matches(
									'[data-slos-escape-enabled][role="dialog"]'
								)
							) {
								this.setupFocusTrap(node);
							}
							const dialogs =
								node.querySelectorAll &&
								node.querySelectorAll(
									'[data-slos-escape-enabled][role="dialog"]'
								);
							if (dialogs) {
								dialogs.forEach((d) => this.setupFocusTrap(d));
							}
						}
					});
				});
			});

			observer.observe(document.body, { childList: true, subtree: true });
		},

		/**
		 * Setup focus trap for a modal
		 *
		 * @param dialog
		 */
		setupFocusTrap(dialog) {
			if (dialog.hasAttribute('data-slos-focus-trap')) return;
			dialog.setAttribute('data-slos-focus-trap', 'true');

			dialog.addEventListener('keydown', (e) => {
				if (e.key !== 'Tab') return;

				const focusables = this.getFocusableElements(dialog);
				if (focusables.length === 0) return;

				const first = focusables[0];
				const last = focusables[focusables.length - 1];

				if (e.shiftKey && document.activeElement === first) {
					e.preventDefault();
					last.focus();
				} else if (!e.shiftKey && document.activeElement === last) {
					e.preventDefault();
					first.focus();
				}
			});
		},

		/**
		 * Store reference to last focused element
		 */
		lastFocusedElement: null,
	};

	// Initialize
	SLOSAccessibilityFixes.init();

	// Expose globally for external use
	window.SLOSAccessibilityFixes = SLOSAccessibilityFixes;
})();
