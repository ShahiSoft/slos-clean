/**
 * Interactive UI Components
 *
 * JavaScript for interactive dark futuristic UI components.
 * Includes counter animations, notifications, tooltips, and modal handlers.
 *
 * @package
 * @subpackage Assets/JS
 * @since      1.0.0
 */

(function ($) {
	'use strict';

	/**
	 * ShahiLegalFlowSuite Components Namespace
	 */
	window.ShahiComponents = {
		/**
		 * Initialize all components
		 */
		init() {
			this.initCounters();
			this.initTooltips();
			this.initRippleEffect();
			this.initScrollAnimations();
			this.initNotifications();
		},

		/**
		 * Animated Number Counters
		 *
		 * Animates numbers from 0 to target value
		 * Usage: <span class="shahi-counter" data-target="1234">0</span>
		 */
		initCounters() {
			$( '.shahi-counter' ).each(
				function () {
					const $counter = $( this );
					const target = parseInt(
						$counter.data( 'target' ) || $counter.text()
					);
					const duration = parseInt( $counter.data( 'duration' ) || 2000 );
					let current = 0;
					const increment = target / (duration / 16);

					const updateCounter = function () {
						current += increment;
						if (current < target) {
							$counter.text( Math.floor( current ).toLocaleString() );
							requestAnimationFrame( updateCounter );
						} else {
							$counter.text( target.toLocaleString() );
						}
					};

					// Start animation when element is visible
					var observer = new IntersectionObserver(
						function (entries) {
							if (entries[0].isIntersecting) {
								updateCounter();
								observer.disconnect();
							}
						}
					);

					observer.observe( $counter[0] );
				}
			);
		},

		/**
		 * Initialize Tooltips
		 *
		 * Creates futuristic tooltips with positioning
		 */
		initTooltips() {
			$( '[data-tooltip]' ).each(
				function () {
					const $element = $( this );
					const tooltipText = $element.data( 'tooltip' );
					const position = $element.data( 'tooltip-position' ) || 'top';

					if ( ! $element.hasClass( 'shahi-tooltip' )) {
						$element.addClass(
							'shahi-tooltip shahi-tooltip-' + position
						);

						const $tooltip = $(
							'<span class="shahi-tooltip-content"></span>'
						);
						$tooltip.text( tooltipText );
						$element.append( $tooltip );
					}
				}
			);
		},

		/**
		 * Ripple Effect on Buttons
		 *
		 * Material Design-style ripple effect
		 */
		initRippleEffect() {
			$( document ).on(
				'click',
				'.shahi-button, .shahi-ripple-container',
				function (e) {
					const $button = $( this );

					// Create ripple element
					const $ripple = $( '<span class="shahi-ripple"></span>' );

					// Calculate position
					const rect = this.getBoundingClientRect();
					const size = Math.max( rect.width, rect.height );
					const x = e.clientX - rect.left - size / 2;
					const y = e.clientY - rect.top - size / 2;

					// Set ripple styles
					$ripple.css(
						{
							width: size,
							height: size,
							left: x,
							top: y,
						}
					);

					// Add to button
					$button.append( $ripple );

					// Remove after animation
					setTimeout(
						function () {
							$ripple.remove();
						},
						600
					);
				}
			);
		},

		/**
		 * Scroll-based Animations
		 *
		 * Reveals elements on scroll
		 */
		initScrollAnimations() {
			const observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(
						function (entry) {
							if (entry.isIntersecting) {
								entry.target.classList.add( 'visible' );
							}
						}
					);
				},
				{
					threshold: 0.1,
				}
			);

			$( '.shahi-scroll-fade-in' ).each(
				function () {
					observer.observe( this );
				}
			);
		},

		/**
		 * Notification System
		 */
		notifications: {
			container: null,

			/**
			 * Initialize notification container
			 */
			init() {
				if ( ! this.container) {
					this.container = $(
						'<div class="shahi-notifications-container"></div>'
					);
					$( 'body' ).append( this.container );
				}
			},

			/**
			 * Show notification
			 *
			 * @param {string} message  - Notification message
			 * @param {string} type     - Type: success, error, warning, info
			 * @param {number} duration - Duration in milliseconds
			 */
			show( message, type, duration ) {
				this.init();

				type = type || 'info';
				duration = duration || 5000;

				const icons = {
					success: 'dashicons-yes-alt',
					error: 'dashicons-dismiss',
					warning: 'dashicons-warning',
					info: 'dashicons-info',
				};

				const $notification = $(
					`
					< div class          = "shahi-notification shahi-notification-${type} shahi-notification-enter" >
						< div class      = "shahi-notification-icon" >
							< span class = "dashicons ${icons[type]}" > < / span >
						< / div >
						< div class      = "shahi-notification-content" >
							< p class    = "shahi-notification-message" > ${message} < / p >
						< / div >
						< button class   = "shahi-notification-close" >
							< span class = "dashicons dashicons-no-alt" > < / span >
						< / button >
					< / div >
					`
				);

				this.container.append( $notification );

				// Auto-remove after duration
				setTimeout(
					function () {
						$notification.addClass( 'shahi-notification-exit' );
						setTimeout(
							function () {
								$notification.remove();
							},
							300
						);
					},
					duration
				);

				// Manual close
				$notification
					.find( '.shahi-notification-close' )
					.on(
						'click',
						function () {
							$notification.addClass( 'shahi-notification-exit' );
							setTimeout(
								function () {
									$notification.remove();
								},
								300
							);
						}
					);
			},

			success( message, duration ) {
				this.show( message, 'success', duration );
			},

			error( message, duration ) {
				this.show( message, 'error', duration );
			},

			warning( message, duration ) {
				this.show( message, 'warning', duration );
			},

			info( message, duration ) {
				this.show( message, 'info', duration );
			},
		},

		/**
		 * Initialize Notifications
		 */
		initNotifications() {
			this.notifications.init();
		},

		/**
		 * Progress Bar Animation
		 *
		 * @param {jQuery} $progressBar - Progress bar element
		 * @param {number} targetValue  - Target percentage (0-100)
		 * @param {number} duration     - Animation duration in ms
		 */
		animateProgress( $progressBar, targetValue, duration ) {
			duration = duration || 1000;
			let currentValue = 0;
			const increment = targetValue / (duration / 16);

			const updateProgress = function () {
				currentValue += increment;
				if (currentValue < targetValue) {
					$progressBar.css( 'width', currentValue + '%' );
					requestAnimationFrame( updateProgress );
				} else {
					$progressBar.css( 'width', targetValue + '%' );
				}
			};

			updateProgress();
		},

		/**
		 * Toggle Switch Handler
		 *
		 * Handles toggle switch interactions
		 */
		initToggles() {
			$( document ).on(
				'change',
				'.shahi-toggle-input',
				function () {
					const $input = $( this );
					const $toggle = $input.closest( '.shahi-toggle' );

					if ($input.is( ':checked' )) {
						$toggle.addClass( 'shahi-toggle-active' );
					} else {
						$toggle.removeClass( 'shahi-toggle-active' );
					}

					// Trigger custom event
					$toggle.trigger( 'shahi:toggle', [$input.is( ':checked' )] );
				}
			);
		},

		/**
		 * Confetti Animation
		 *
		 * Creates celebratory confetti effect
		 *
		 * @param {jQuery} $container - Container element
		 * @param {number} count      - Number of confetti pieces
		 */
		confetti( $container, count ) {
			count = count || 50;

			const colors = [
				'#60a5fa',
				'#93c5fd',
				'#10b981',
				'#f59e0b',
				'#ec4899',
				'#8b5cf6',
			];

			for (let i = 0; i < count; i++) {
				const $piece = $( '<div class="shahi-confetti-piece"></div>' );

				$piece.css(
					{
						left: Math.random() * 100 + '%',
						background:
						colors[Math.floor( Math.random() * colors.length )],
						animationDelay: Math.random() * 2 + 's',
						animationDuration: Math.random() * 2 + 2 + 's',
					}
				);

				$container.append( $piece );
			}

			// Clean up after animation
			setTimeout(
				function () {
					$container.find( '.shahi-confetti-piece' ).remove();
				},
				5000
			);
		},

		/**
		 * Loading Overlay
		 *
		 * Shows/hides loading overlay
		 */
		loading: {
			show( message ) {
				message = message || 'Loading...';

				const $overlay = $(
					`
					< div class     = "shahi-loading-overlay" >
						< div class = "shahi-spinner" > < / div >
						< div class = "shahi-loading-text" > ${message} < / div >
					< / div >
					`
				);

				$( 'body' ).append( $overlay );
			},

			hide() {
				$( '.shahi-loading-overlay' ).fadeOut(
					300,
					function () {
						$( this ).remove();
					}
				);
			},
		},

		/**
		 * Skeleton Loader
		 *
		 * Creates skeleton loading placeholders
		 *
		 * @param {jQuery} $container - Container to add skeletons
		 * @param {number} count      - Number of skeleton items
		 */
		createSkeletons( $container, count ) {
			count = count || 3;

			for (let i = 0; i < count; i++) {
				const $skeleton = $(
					`
					< div class         = "shahi-skeleton-item" >
						< div class     = "shahi-skeleton shahi-skeleton-avatar" > < / div >
						< div >
							< div class = "shahi-skeleton shahi-skeleton-title" > < / div >
							< div class = "shahi-skeleton shahi-skeleton-text" > < / div >
							< div class = "shahi-skeleton shahi-skeleton-text" > < / div >
						< / div >
					< / div >
					`
				);

				$container.append( $skeleton );
			}
		},

		/**
		 * Copy to Clipboard
		 *
		 * @param {string}   text     - Text to copy
		 * @param {Function} callback - Success callback
		 */
		copyToClipboard( text, callback ) {
			const $temp = $( '<textarea>' );
			$( 'body' ).append( $temp );
			$temp.val( text ).select();

			try {
				document.execCommand( 'copy' );
				if (callback) {
					callback( true );
				}
				this.notifications.success( 'Copied to clipboard!' );
			} catch (err) {
				if (callback) {
					callback( false );
				}
				this.notifications.error( 'Failed to copy to clipboard' );
			}

			$temp.remove();
		},

		/**
		 * Debounce Function
		 *
		 * @param {Function} func - Function to debounce
		 * @param {number}   wait - Wait time in ms
		 * @return {Function} Debounced function
		 */
		debounce( func, wait ) {
			let timeout;
			return function () {
				const context = this;
				const args = arguments;
				clearTimeout( timeout );
				timeout = setTimeout(
					function () {
						func.apply( context, args );
					},
					wait
				);
			};
		},

		/**
		 * Throttle Function
		 *
		 * @param {Function} func  - Function to throttle
		 * @param {number}   limit - Time limit in ms
		 * @return {Function} Throttled function
		 */
		throttle( func, limit ) {
			let inThrottle;
			return function () {
				const args = arguments;
				const context = this;
				if ( ! inThrottle) {
					func.apply( context, args );
					inThrottle = true;
					setTimeout(
						function () {
							inThrottle = false;
						},
						limit
					);
				}
			};
		},

		/**
		 * Format Number
		 *
		 * @param {number} num      - Number to format
		 * @param {number} decimals - Decimal places
		 * @return {string} Formatted number
		 */
		formatNumber( num, decimals ) {
			decimals = decimals || 0;
			return num.toFixed( decimals ).replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
		},

		/**
		 * Time Ago Format
		 *
		 * @param {Date|string} date - Date to format
		 * @return {string} Time ago string
		 */
		timeAgo( date ) {
			const now = new Date();
			const past = new Date( date );
			const seconds = Math.floor( (now - past) / 1000 );

			const intervals = {
				year: 31536000,
				month: 2592000,
				week: 604800,
				day: 86400,
				hour: 3600,
				minute: 60,
				second: 1,
			};

			for (const key in intervals) {
				const interval = Math.floor( seconds / intervals[key] );
				if (interval >= 1) {
					return (
						interval +
						' ' +
						key +
						(interval > 1 ? 's' : '') +
						' ago'
					);
				}
			}

			return 'just now';
		},
	};

	/**
	 * Initialize on document ready
	 */
	$( document ).ready(
		function () {
			ShahiComponents.init();
		}
	);

	/**
	 * Expose to global scope
	 */
	window.ShahiNotify = ShahiComponents.notifications;
})( jQuery );

/**
 * Add custom CSS for notifications container
 */
(function () {
	'use strict';

	const styles = `
		< style id = "shahi-components-dynamic-styles" >
			.shahi - notifications - container {
				position: fixed;
				top: 32px;
				right: 20px;
				z - index: 999999;
				display: flex;
				flex - direction: column;
				gap: 12px;
				max - width: 400px;
	}

			.shahi - skeleton - item {
				display: flex;
				gap: 16px;
				margin - bottom: 16px;
	}

			@media( max - width: 768px ) {
				.shahi - notifications - container {
					top: 46px;
					right: 10px;
					left: 10px;
					max - width: none;
				}
	}
		< / style >
	`;

	if (document.head) {
		document.head.insertAdjacentHTML( 'beforeend', styles );
	}
})();
