/**
 * Consent Preferences UI
 *
 * User-facing interface for managing consent preferences.
 * Features:
 * - View current consent choices
 * - Update consent preferences via toggles
 * - View consent history timeline
 * - Download consent data (GDPR Article 15)
 *
 * @package
 * @since 3.0.1
 */

(function () {
	'use strict';

	/**
	 * Consent Preferences Application
	 */
	class ConsentPreferences {
		/**
		 * Constructor
		 */
		constructor() {
			this.config = window.slosConsentPrefs || {};
			this.api = this.config.apiUrl || '/wp-json/slos/v1';
			this.userId = this.config.userId || 0;
			this.sessionId = this.config.sessionId || '';
			this.isLoggedIn = this.config.isLoggedIn || false;
			this.i18n = this.config.i18n || {};
			this.settings = this.config.config || {};
			this.container = document.getElementById(
				'slos-consent-preferences'
			);
			this.loadingEl = document.querySelector('.slos-consent-loading');

			this.state = {
				consents: {},
				purposes: [],
				history: [],
				isLoading: false,
				isSaving: false,
				showHistory: false,
			};

			this.init();
		}

		/**
		 * Initialize application
		 */
		async init() {
			if (!this.container) {
				console.error('[SLOS Consent Preferences] Container not found');
				return;
			}

			try {
				await this.loadPurposes();
				await this.loadConsents();

				if (this.settings.showHistory && this.isLoggedIn) {
					await this.loadHistory();
				}

				this.render();
				this.hideLoading();
			} catch (error) {
				console.error(
					'[SLOS Consent Preferences] Initialization failed:',
					error
				);
				this.showError(this.t('errorLoading'));
				this.hideLoading();
			}
		}

		/**
		 * Load available consent purposes
		 */
		async loadPurposes() {
			try {
				const response = await this.apiRequest(
					'GET',
					'/consents/purposes'
				);
				this.state.purposes = response.data || [];
			} catch (error) {
				console.error(
					'[SLOS Consent Preferences] Failed to load purposes:',
					error
				);
				// Fallback to default purposes
				this.state.purposes = [
					{
						id: 'necessary',
						name: this.t('necessary'),
						description: this.t('necessaryDesc'),
						required: true,
					},
					{
						id: 'functional',
						name: this.t('functional'),
						description: this.t('functionalDesc'),
						required: false,
					},
					{
						id: 'analytics',
						name: this.t('analytics'),
						description: this.t('analyticsDesc'),
						required: false,
					},
					{
						id: 'marketing',
						name: this.t('marketing'),
						description: this.t('marketingDesc'),
						required: false,
					},
					{
						id: 'advertising',
						name: this.t('advertising'),
						description: this.t('advertisingDesc'),
						required: false,
					},
					{
						id: 'personalization',
						name: this.t('personalization'),
						description: this.t('personalizationDesc'),
						required: false,
					},
				];
			}
		}

		/**
		 * Load user's current consents
		 */
		async loadConsents() {
			try {
				let endpoint = '';
				if (this.userId > 0) {
					endpoint = `/consents/user/${this.userId}`;
				} else {
					// For non-logged-in users, check via session
					endpoint = `/consents/check?session_id=${this.sessionId}`;
				}

				const response = await this.apiRequest('GET', endpoint);
				this.state.consents = response.data?.consents || {};
			} catch (error) {
				console.error(
					'[SLOS Consent Preferences] Failed to load consents:',
					error
				);
				this.state.consents = {};
			}
		}

		/**
		 * Load consent history
		 */
		async loadHistory() {
			if (!this.isLoggedIn) {
				return;
			}

			try {
				const response = await this.apiRequest(
					'GET',
					`/consents/logs?user_id=${this.userId}`
				);
				this.state.history = response.data || [];
			} catch (error) {
				console.error(
					'[SLOS Consent Preferences] Failed to load history:',
					error
				);
				this.state.history = [];
			}
		}

		/**
		 * Render the UI
		 */
		render() {
			if (!this.container) return;

			this.container.innerHTML = `
				<div class="slos-pref-card">
					${this.renderHeader()}
					${this.renderPurposesList()}
					${this.renderActions()}
					${this.settings.showHistory ? this.renderHistory() : ''}
					${this.renderGDPRNotice()}
				</div>
			`;

			this.bindEvents();
		}

		/**
		 * Render header section
		 */
		renderHeader() {
			return `
				<div class="slos-pref-header">
					<h3 class="slos-pref-title">${this.t('privacyChoices')}</h3>
					<p class="slos-pref-description">${this.t('manageConsent')}</p>
				</div>
			`;
		}

		/**
		 * Render purposes list with toggles
		 */
		renderPurposesList() {
			const purposesHtml = this.state.purposes
				.map((purpose) => {
					return this.renderPurpose(purpose);
				})
				.join('');

			return `
				<div class="slos-pref-list">
					${purposesHtml}
				</div>
			`;
		}

		/**
		 * Render single purpose item
		 *
		 * @param purpose
		 */
		renderPurpose(purpose) {
			const purposeId = purpose.id || purpose;
			const purposeName = purpose.name || this.capitalize(purposeId);
			const purposeDesc =
				purpose.description || this.getPurposeDescription(purposeId);
			const isRequired = purpose.required || purposeId === 'necessary';
			const isEnabled =
				this.state.consents[purposeId] === true || isRequired;

			return `
				<label class="slos-pref-item ${
					isRequired ? 'slos-required' : ''
				}" data-purpose="${purposeId}">
					<div class="slos-pref-info">
						<div class="slos-pref-name">
							<strong>${purposeName}</strong>
							${
								isRequired
									? `<span class="slos-badge slos-badge-required">${this.t(
											'required'
									  )}</span>`
									: ''
							}
						</div>
						<div class="slos-pref-desc">${purposeDesc}</div>
					</div>
					<div class="slos-pref-toggle">
						<label class="slos-toggle-switch">
							<input 
								type="checkbox" 
								data-purpose="${purposeId}" 
								${isEnabled ? 'checked' : ''} 
								${isRequired ? 'disabled' : ''}
							>
							<span class="slos-toggle-slider"></span>
						</label>
					</div>
				</label>
			`;
		}

		/**
		 * Render action buttons
		 */
		renderActions() {
			return `
				<div class="slos-pref-actions">
					<button class="slos-btn slos-btn-primary" data-action="save">
						<span class="slos-btn-text">${this.t('savePreferences')}</span>
						<span class="slos-btn-spinner" style="display:none;"></span>
					</button>
					<button class="slos-btn slos-btn-secondary" data-action="accept-all">
						${this.t('acceptAll')}
					</button>
					<button class="slos-btn slos-btn-ghost" data-action="reject-all">
						${this.t('rejectAll')}
					</button>
					${
						this.settings.showDownload
							? `
						<button class="slos-btn slos-btn-ghost" data-action="download">
							${this.t('downloadData')}
						</button>
					`
							: ''
					}
				</div>
			`;
		}

		/**
		 * Render consent history
		 */
		renderHistory() {
			if (!this.isLoggedIn) {
				return `
					<div class="slos-pref-history">
						<h4>${this.t('consentHistory')}</h4>
						<p class="slos-notice">${this.t('loginRequired')}</p>
					</div>
				`;
			}

			const historyItems = this.state.history
				.slice(0, 10)
				.map((item) => {
					const date = new Date(item.created_at).toLocaleDateString();
					const time = new Date(item.created_at).toLocaleTimeString();
					const action = this.getActionLabel(item.action);
					const purpose = this.capitalize(item.purpose || 'all');

					return `
					<li class="slos-history-item">
						<span class="slos-history-date">${date} ${time}</span>
						<span class="slos-history-action slos-action-${item.action}">${action}</span>
						<span class="slos-history-purpose">${purpose}</span>
					</li>
				`;
				})
				.join('');

			const hasHistory = this.state.history.length > 0;

			return `
				<div class="slos-pref-history ${this.state.showHistory ? 'slos-expanded' : ''}">
					<button class="slos-history-toggle" data-action="toggle-history">
						<span>${
							this.state.showHistory
								? this.t('hideHistory')
								: this.t('viewHistory')
						}</span>
						<span class="slos-icon ${
							this.state.showHistory
								? 'slos-icon-up'
								: 'slos-icon-down'
						}">▼</span>
					</button>
					<div class="slos-history-list" style="${
						this.state.showHistory ? '' : 'display:none;'
					}">
						${
							hasHistory
								? `
							<ul>
								${historyItems}
							</ul>
						`
								: `
							<p class="slos-notice">${this.t('noHistory')}</p>
						`
						}
					</div>
				</div>
			`;
		}

		/**
		 * Render GDPR notice
		 */
		renderGDPRNotice() {
			return `
				<div class="slos-gdpr-notice">
					<p><small>${this.t('gdprNotice')}</small></p>
				</div>
			`;
		}

		/**
		 * Bind event listeners
		 */
		bindEvents() {
			// Save button
			const saveBtn = this.container.querySelector(
				'[data-action="save"]'
			);
			if (saveBtn) {
				saveBtn.addEventListener('click', () => this.handleSave());
			}

			// Accept all button
			const acceptBtn = this.container.querySelector(
				'[data-action="accept-all"]'
			);
			if (acceptBtn) {
				acceptBtn.addEventListener('click', () =>
					this.handleAcceptAll()
				);
			}

			// Reject all button
			const rejectBtn = this.container.querySelector(
				'[data-action="reject-all"]'
			);
			if (rejectBtn) {
				acceptBtn.addEventListener('click', () =>
					this.handleRejectAll()
				);
			}

			// Download button
			const downloadBtn = this.container.querySelector(
				'[data-action="download"]'
			);
			if (downloadBtn) {
				downloadBtn.addEventListener('click', () =>
					this.handleDownload()
				);
			}

			// History toggle
			const historyToggle = this.container.querySelector(
				'[data-action="toggle-history"]'
			);
			if (historyToggle) {
				historyToggle.addEventListener('click', () =>
					this.toggleHistory()
				);
			}
		}

		/**
		 * Handle save preferences
		 */
		async handleSave() {
			if (this.state.isSaving) return;

			this.state.isSaving = true;
			this.showSaving();

			try {
				const checkboxes = this.container.querySelectorAll(
					'input[type="checkbox"][data-purpose]:not([disabled])'
				);
				const updates = [];

				for (const checkbox of checkboxes) {
					const purpose = checkbox.dataset.purpose;
					const isChecked = checkbox.checked;
					const wasChecked = this.state.consents[purpose] === true;

					// Only update if changed
					if (isChecked !== wasChecked) {
						if (isChecked) {
							updates.push(this.grantConsent(purpose));
						} else {
							updates.push(this.withdrawConsent(purpose));
						}
					}
				}

				await Promise.all(updates);

				// Reload consents
				await this.loadConsents();

				// Reload history if visible
				if (this.settings.showHistory && this.isLoggedIn) {
					await this.loadHistory();
				}

				this.showSuccess(this.t('saved'));
				this.render();
			} catch (error) {
				console.error('[SLOS Consent Preferences] Save failed:', error);
				this.showError(this.t('errorSaving'));
			} finally {
				this.state.isSaving = false;
				this.hideSaving();
			}
		}

		/**
		 * Handle accept all
		 */
		async handleAcceptAll() {
			const checkboxes = this.container.querySelectorAll(
				'input[type="checkbox"][data-purpose]:not([disabled])'
			);
			checkboxes.forEach((checkbox) => {
				checkbox.checked = true;
			});
			await this.handleSave();
		}

		/**
		 * Handle reject all (except required)
		 */
		async handleRejectAll() {
			const checkboxes = this.container.querySelectorAll(
				'input[type="checkbox"][data-purpose]:not([disabled])'
			);
			checkboxes.forEach((checkbox) => {
				checkbox.checked = false;
			});
			await this.handleSave();
		}

		/**
		 * Handle download data
		 */
		async handleDownload() {
			try {
				const response = await this.apiRequest(
					'GET',
					`/consents/export/${this.userId}`
				);
				const data = response.data || {};

				const blob = new Blob([JSON.stringify(data, null, 2)], {
					type: 'application/json',
				});
				const url = URL.createObjectURL(blob);
				const link = document.createElement('a');
				link.href = url;
				link.download = `consent-data-${
					this.userId
				}-${Date.now()}.json`;
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
				URL.revokeObjectURL(url);

				this.showSuccess(this.t('downloadReady'));
			} catch (error) {
				console.error(
					'[SLOS Consent Preferences] Download failed:',
					error
				);
				this.showError(this.t('errorDownload'));
			}
		}

		/**
		 * Toggle history visibility
		 */
		toggleHistory() {
			this.state.showHistory = !this.state.showHistory;
			const historyList =
				this.container.querySelector('.slos-history-list');
			const icon = this.container.querySelector(
				'.slos-history-toggle .slos-icon'
			);
			const toggleBtn = this.container.querySelector(
				'.slos-history-toggle span:first-child'
			);

			if (this.state.showHistory) {
				historyList.style.display = '';
				icon.classList.remove('slos-icon-down');
				icon.classList.add('slos-icon-up');
				toggleBtn.textContent = this.t('hideHistory');
			} else {
				historyList.style.display = 'none';
				icon.classList.remove('slos-icon-up');
				icon.classList.add('slos-icon-down');
				toggleBtn.textContent = this.t('viewHistory');
			}
		}

		/**
		 * Grant consent for purpose
		 *
		 * @param purpose
		 */
		async grantConsent(purpose) {
			return await this.apiRequest('POST', '/consents', {
				user_id: this.userId,
				session_id: this.sessionId,
				purpose,
				action: 'grant',
			});
		}

		/**
		 * Withdraw consent for purpose
		 *
		 * @param purpose
		 */
		async withdrawConsent(purpose) {
			// Find consent ID for this purpose
			return await this.apiRequest('POST', '/consents', {
				user_id: this.userId,
				session_id: this.sessionId,
				purpose,
				action: 'withdraw',
			});
		}

		/**
		 * Make API request
		 *
		 * @param method
		 * @param endpoint
		 * @param data
		 */
		async apiRequest(method, endpoint, data = null) {
			const url = `${this.api}${endpoint}`;
			const options = {
				method,
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': this.config.nonce || '',
				},
			};

			if (data && (method === 'POST' || method === 'PUT')) {
				options.body = JSON.stringify(data);
			}

			const response = await fetch(url, options);

			if (!response.ok) {
				throw new Error(
					`API request failed: ${response.status} ${response.statusText}`
				);
			}

			return await response.json();
		}

		/**
		 * Show loading state
		 */
		showLoading() {
			if (this.loadingEl) {
				this.loadingEl.style.display = 'block';
			}
		}

		/**
		 * Hide loading state
		 */
		hideLoading() {
			if (this.loadingEl) {
				this.loadingEl.style.display = 'none';
			}
		}

		/**
		 * Show saving state
		 */
		showSaving() {
			const saveBtn = this.container.querySelector(
				'[data-action="save"]'
			);
			if (saveBtn) {
				saveBtn.disabled = true;
				saveBtn.querySelector('.slos-btn-text').textContent =
					this.t('saving');
				const spinner = saveBtn.querySelector('.slos-btn-spinner');
				if (spinner) spinner.style.display = 'inline-block';
			}
		}

		/**
		 * Hide saving state
		 */
		hideSaving() {
			const saveBtn = this.container.querySelector(
				'[data-action="save"]'
			);
			if (saveBtn) {
				saveBtn.disabled = false;
				saveBtn.querySelector('.slos-btn-text').textContent =
					this.t('savePreferences');
				const spinner = saveBtn.querySelector('.slos-btn-spinner');
				if (spinner) spinner.style.display = 'none';
			}
		}

		/**
		 * Show success message
		 *
		 * @param message
		 */
		showSuccess(message) {
			this.showNotification(message, 'success');
		}

		/**
		 * Show error message
		 *
		 * @param message
		 */
		showError(message) {
			this.showNotification(message, 'error');
		}

		/**
		 * Show notification
		 *
		 * @param message
		 * @param type
		 */
		showNotification(message, type = 'info') {
			const notification = document.createElement('div');
			notification.className = `slos-notification slos-notification-${type}`;
			notification.textContent = message;
			notification.style.cssText =
				'position:fixed;top:20px;right:20px;z-index:9999;padding:12px 24px;border-radius:4px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.15);';

			if (type === 'success') {
				notification.style.borderLeft = '4px solid #4CAF50';
			} else if (type === 'error') {
				notification.style.borderLeft = '4px solid #f44336';
			}

			document.body.appendChild(notification);

			setTimeout(() => {
				notification.remove();
			}, 5000);
		}

		/**
		 * Get translation string
		 *
		 * @param key
		 */
		t(key) {
			return this.i18n[key] || key;
		}

		/**
		 * Capitalize string
		 *
		 * @param str
		 */
		capitalize(str) {
			if (!str) return '';
			return str.charAt(0).toUpperCase() + str.slice(1);
		}

		/**
		 * Get purpose description
		 *
		 * @param purpose
		 */
		getPurposeDescription(purpose) {
			const key = `${purpose}Desc`;
			return this.t(key);
		}

		/**
		 * Get action label for history
		 *
		 * @param action
		 */
		getActionLabel(action) {
			if (action === 'grant' || action === 'granted') {
				return this.t('historyGranted');
			} else if (action === 'withdraw' || action === 'withdrawn') {
				return this.t('historyWithdrawn');
			} else if (action === 'update' || action === 'updated') {
				return this.t('historyUpdated');
			}
			return this.capitalize(action);
		}
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => {
			new ConsentPreferences();
		});
	} else {
		new ConsentPreferences();
	}
})();
