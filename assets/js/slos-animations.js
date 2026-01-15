/**
 * SLOS Animations - Micro-Animations Library
 *
 * JavaScript animations for Future Visual Enhancements (V3.2) Phase 6.
 * Provides smooth counter animations, scroll-triggered effects, and visual feedback.
 *
 * @package
 * @subpackage Animations
 * @version    3.2.0
 * @since      Phase 6.1
 * @updated    2026-01-04
 */

/* ============================================================================
   PHASE 6.1: COUNTER ANIMATION
   Animated number counter with easing and formatting
   ========================================================================= */

/**
 * SLOSCounter Class
 * Smoothly animates numbers from 0 to target value with easing
 *
 * @class
 * @param {HTMLElement} element           - The element containing the number to animate
 * @param {Object}      options           - Configuration options
 * @param {number}      options.duration  - Animation duration in milliseconds (default: 2000)
 * @param {number}      options.decimals  - Number of decimal places (default: 0)
 * @param {string}      options.prefix    - String to prepend to the value (default: '')
 * @param {string}      options.suffix    - String to append to the value (default: '')
 * @param {string}      options.separator - Thousand separator character (default: ',')
 *
 * @example
 * const counter = new SLOSCounter(element, { duration: 2000, decimals: 2, suffix: '%' });
 * counter.start();
 */
class SLOSCounter {
	constructor(element, options = {}) {
		this.element = element;
		this.target = parseInt(element.dataset.target || element.textContent);
		this.duration = options.duration || 2000;
		this.decimals = options.decimals || 0;
		this.prefix = options.prefix || '';
		this.suffix = options.suffix || '';
		this.separator = options.separator || ',';
	}

	/**
	 * Start the counter animation
	 * Uses requestAnimationFrame for smooth 60fps animation
	 */
	start() {
		const startTime = performance.now();
		const startValue = 0;

		const animate = (currentTime) => {
			const elapsed = currentTime - startTime;
			const progress = Math.min(elapsed / this.duration, 1);

			// Easing function (ease-out-cubic)
			// Creates a fast start that gradually slows down
			const easeProgress = 1 - Math.pow(1 - progress, 3);
			const currentValue =
				startValue + (this.target - startValue) * easeProgress;

			this.element.textContent = this.format(currentValue);

			if (progress < 1) {
				requestAnimationFrame(animate);
			} else {
				// Ensure final value is exactly the target
				this.element.textContent = this.format(this.target);
			}
		};

		requestAnimationFrame(animate);
	}

	/**
	 * Format the counter value with separators, decimals, prefix, and suffix
	 *
	 * @param {number} value - The numeric value to format
	 * @return {string} Formatted string with all decorations
	 */
	format(value) {
		let formatted = value.toFixed(this.decimals);

		// Add thousand separators
		if (this.separator) {
			const parts = formatted.split('.');
			parts[0] = parts[0].replace(
				/\B(?=(\d{3})+(?!\d))/g,
				this.separator
			);
			formatted = parts.join('.');
		}

		return this.prefix + formatted + this.suffix;
	}
}

/* ============================================================================
   AUTO-INITIALIZATION
   Automatically animate counters on page load with scroll detection
   ========================================================================= */

/**
 * Initialize all counters when DOM is ready
 * Uses IntersectionObserver to trigger animations only when elements are visible
 * Ensures animations start at the optimal moment for user engagement
 */
document.addEventListener('DOMContentLoaded', () => {
	const counters = document.querySelectorAll(
		'.slos-stat-value[data-animate="true"]'
	);

	// No counters to animate
	if (counters.length === 0) {
		return;
	}

	// Create IntersectionObserver to trigger animations on scroll
	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					// Element is visible, start animation
					const counter = new SLOSCounter(entry.target);
					counter.start();

					// Stop observing this element (animate only once)
					observer.unobserve(entry.target);
				}
			});
		},
		{
			threshold: 0.5, // Trigger when 50% of element is visible
		}
	);

	// Observe all counter elements
	counters.forEach((counter) => observer.observe(counter));
});

/* ============================================================================
   EXPORTS
   Make SLOSCounter available globally for manual initialization
   ========================================================================= */

// Export for manual initialization or module usage
if (typeof window !== 'undefined') {
	window.SLOSCounter = SLOSCounter;
}
