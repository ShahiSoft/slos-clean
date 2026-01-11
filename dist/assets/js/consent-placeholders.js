/**
 * Consent Placeholders - Blocked Content Management
 *
 * Replaces iframes and embeds with consent placeholders until user grants permission.
 * Provides "Enable [Category]" buttons to prompt consent.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Assets/Frontend
 * @since      3.1.1
 */

(function() {
	'use strict';

	/**
	 * Consent Placeholders Class
	 */
	class ConsentPlaceholders {
		/**
		 * Constructor
		 */
		constructor() {
			this.config = window.slosConsentConfig || {};
			this.placeholders = [];
			this.consentStatus = {};

			this.init();
		}

		/**
		 * Initialize placeholders
		 */
		init() {
			// Wait for DOM ready
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', () => this.scanAndReplace());
			} else {
				this.scanAndReplace();
			}

			// Listen for consent changes
			document.addEventListener('slosConsentUpdated', (e) => {
				this.handleConsentUpdate(e.detail);
			});

			// Check stored consent on load
			this.loadConsentStatus();
		}

		/**
		 * Load consent status from localStorage
		 */
		loadConsentStatus() {
			try {
				const stored = localStorage.getItem('slos_consent_preferences');
				if (stored) {
					this.consentStatus = JSON.parse(stored);
				}
			} catch (e) {
				// Ignore localStorage errors
			}
		}

		/**
		 * Scan page and replace blocked embeds
		 */
		scanAndReplace() {
			// Find all placeholder containers
			const placeholderEls = document.querySelectorAll('[data-slos-placeholder]');
			
			placeholderEls.forEach(el => {
				const category = el.getAttribute('data-slos-placeholder');
				const embedUrl = el.getAttribute('data-slos-embed-url');
				const embedType = el.getAttribute('data-slos-embed-type') || 'iframe';
				
				if (!category) return;

				// Check if consent is granted
				if (this.hasConsent(category)) {
					this.loadEmbed(el, embedUrl, embedType);
				} else {
					this.renderPlaceholder(el, category, embedUrl, embedType);
				}
			});

			// Also scan for auto-blocked iframes
			this.scanAutoBlockedIframes();
		}

		/**
		 * Scan for iframes that should be auto-blocked
		 */
		scanAutoBlockedIframes() {
			// Common third-party domains that require consent
			const blockedDomains = {
				'youtube.com': 'marketing',
				'youtu.be': 'marketing',
				'vimeo.com': 'marketing',
				'facebook.com': 'marketing',
				'twitter.com': 'marketing',
				'instagram.com': 'marketing',
				'tiktok.com': 'marketing',
				'google.com/maps': 'functional',
				'googletagmanager.com': 'analytics',
				'doubleclick.net': 'marketing',
			};

			const iframes = document.querySelectorAll('iframe:not([data-slos-processed])');
			
			iframes.forEach(iframe => {
				const src = iframe.src || '';
				let category = null;

				// Check if iframe matches blocked domains
				for (const [domain, cat] of Object.entries(blockedDomains)) {
					if (src.includes(domain)) {
						category = cat;
						break;
					}
				}

				if (category && !this.hasConsent(category)) {
					// Replace iframe with placeholder
					const placeholder = document.createElement('div');
					placeholder.setAttribute('data-slos-placeholder', category);
					placeholder.setAttribute('data-slos-embed-url', src);
					placeholder.setAttribute('data-slos-embed-type', 'iframe');
					placeholder.className = iframe.className || '';
					
					// Copy dimensions if available
					if (iframe.width) placeholder.style.width = iframe.width + (iframe.width.toString().includes('%') ? '' : 'px');
					if (iframe.height) placeholder.style.height = iframe.height + (iframe.height.toString().includes('%') ? '' : 'px');
					
					iframe.parentNode.replaceChild(placeholder, iframe);
					
					this.renderPlaceholder(placeholder, category, src, 'iframe');
				}

				iframe.setAttribute('data-slos-processed', 'true');
			});
		}

		/**
		 * Check if consent is granted for category
		 *
		 * @param {string} category Category name
		 * @return {boolean}
		 */
		hasConsent(category) {
			return this.consentStatus[category] === true;
		}

		/**
		 * Render placeholder HTML
		 *
		 * @param {HTMLElement} container Container element
		 * @param {string} category Category name
		 * @param {string} embedUrl Embed URL
		 * @param {string} embedType Embed type
		 */
		renderPlaceholder(container, category, embedUrl, embedType) {
			const categoryLabels = {
				'necessary': 'Essential',
				'functional': 'Functional',
				'analytics': 'Analytics',
				'marketing': 'Marketing',
				'preferences': 'Preferences'
			};

			const label = categoryLabels[category] || category.charAt(0).toUpperCase() + category.slice(1);
			
			const html = `
				<div class="slos-embed-placeholder" data-category="${category}">
					<div class="slos-placeholder-content">
						<div class="slos-placeholder-icon">
							<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<rect x="2" y="2" width="20" height="20" rx="2" ry="2"></rect>
								<circle cx="12" cy="12" r="3"></circle>
							</svg>
						</div>
						<h3 class="slos-placeholder-title">Content Blocked</h3>
						<p class="slos-placeholder-message">
							This content requires <strong>${label}</strong> cookies to be enabled.
						</p>
						<button class="slos-enable-btn" data-category="${category}" type="button">
							Enable ${label} Cookies
						</button>
						<p class="slos-placeholder-privacy">
							<a href="/privacy-policy" class="slos-privacy-link">Learn about our privacy policy</a>
						</p>
					</div>
				</div>
			`;

			container.innerHTML = html;

			// Store reference
			this.placeholders.push({
				container: container,
				category: category,
				embedUrl: embedUrl,
				embedType: embedType
			});

			// Bind enable button
			const enableBtn = container.querySelector('.slos-enable-btn');
			if (enableBtn) {
				enableBtn.addEventListener('click', () => {
					this.promptConsent(category, container);
				});
			}
		}

		/**
		 * Prompt user for consent
		 *
		 * @param {string} category Category name
		 * @param {HTMLElement} container Container element
		 */
		promptConsent(category, container) {
			// Trigger consent banner to open with specific category
			const event = new CustomEvent('slosRequestConsent', {
				detail: { category: category }
			});
			document.dispatchEvent(event);

			// Alternatively, if banner API is available
			if (window.slosConsentBanner && typeof window.slosConsentBanner.openPreferences === 'function') {
				window.slosConsentBanner.openPreferences(category);
			}
		}

		/**
		 * Handle consent update
		 *
		 * @param {Object} consents Updated consent status
		 */
		handleConsentUpdate(consents) {
			this.consentStatus = consents;

			// Reload embeds that now have consent
			this.placeholders.forEach(placeholder => {
				if (this.hasConsent(placeholder.category)) {
					this.loadEmbed(
						placeholder.container,
						placeholder.embedUrl,
						placeholder.embedType
					);
				}
			});
		}

		/**
		 * Load embed content
		 *
		 * @param {HTMLElement} container Container element
		 * @param {string} embedUrl Embed URL
		 * @param {string} embedType Embed type
		 */
		loadEmbed(container, embedUrl, embedType) {
			if (!embedUrl) return;

			if (embedType === 'iframe') {
				const iframe = document.createElement('iframe');
				iframe.src = embedUrl;
				iframe.frameBorder = '0';
				iframe.allowFullscreen = true;
				iframe.setAttribute('loading', 'lazy');
				
				// Copy dimensions from container
				if (container.style.width) iframe.style.width = container.style.width;
				if (container.style.height) iframe.style.height = container.style.height;
				
				// Copy classes
				if (container.className) {
					const classes = container.className.split(' ').filter(c => !c.startsWith('slos-'));
					if (classes.length) iframe.className = classes.join(' ');
				}
				
				container.innerHTML = '';
				container.appendChild(iframe);
			} else if (embedType === 'script') {
				// For script embeds, evaluate the script content
				const script = document.createElement('script');
				script.innerHTML = embedUrl; // embedUrl contains script content in this case
				container.innerHTML = '';
				container.appendChild(script);
			}
		}
	}

	/**
	 * Initialize when ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => {
			window.slosPlaceholders = new ConsentPlaceholders();
		});
	} else {
		window.slosPlaceholders = new ConsentPlaceholders();
	}

})();
