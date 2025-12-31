/**
 * Consent Banner Component
 *
 * Displays and manages user consent preferences.
 * Supports 4 templates: EU/GDPR, CCPA, Simple, Advanced
 * Fully integrated with REST API and localStorage
 *
 * @package ShahiLegalFlowSuite
 * @subpackage Frontend
 * @version 3.0.1
 * @since 3.0.1
 */

(function() {
    'use strict';

    /**
     * Consent Banner Class
     */
    class ConsentBanner {
        /**
         * Constructor
         */
        constructor() {
            this.config = window.slosConsentConfig || {};
            this.apiUrl = this.config.apiUrl || '/wp-json/shahi-legalflowsuite/v1/consents';
            this.routes = this.config.routes || {};
            this.userId = this.config.userId || 0;
            this.region = this.config.region || null;
            this.bannerTemplate = this.config.template || 'eu';
            this.position = this.config.position || 'bottom';
            this.theme = this.config.theme || 'light';
            this.purposes = [];
            this.consents = {};
            this.locale = this.config.locale || (navigator.language || 'en').substring(0, 2);
            this.translations = window.slosConsentI18n || {};
            
            // Phase 0.4.1: Privacy policy URL
            this.privacyUrl = this.config.privacyUrl || '';
            
            // Phase 2.3.2: Learn more link text
            this.learnMoreText = this.config.learnMoreText || 'Learn more';
            
            // Phase 2.3.3: Consent expiry days
            this.consentExpiryDays = this.config.consentExpiryDays || 30;
            
            // Phase 3.4.1: Grace period for re-consent (days)
            this.gracePeriodDays = this.config.gracePeriodDays || 0;
            
            // Phase 1.4.1: Floating icon position (left or right)
            this.iconPosition = this.config.iconPosition || 'left';
            
            // Phase 1.4: Category descriptions and vendors
            this.showDescriptions = this.config.showDescriptions !== undefined ? this.config.showDescriptions : true;
            this.categoryDescriptions = this.config.categoryDescriptions || {};
            this.showVendors = this.config.showVendors || false;
            this.vendors = this.config.vendors || {};
            
            // Phase 3.1.1 & 3.1.2: Version tracking for re-consent triggers
            this.policyVersion = this.config.policyVersion || '1.0';
            this.bannerVersion = this.config.bannerVersion || this.generateBannerVersion();
            
            // Phase 4.2.1, 4.2.3: A/B testing variant assignment
            this.variant = this.assignVariant();

            this.init();
        }

        /**
         * Initialize banner
         *
         * @return {Promise<void>}
         */
        async init() {
            // Resolve region and adjust template if needed
            await this.resolveRegionAndTemplate();

            // Check if consent already given
            const hasConsent = await this.checkConsent('analytics');
            if (hasConsent && !this.shouldShowAgain()) {
                return; // Don't show banner
            }

            // Load valid purposes
            await this.loadPurposes();

            // Show banner
            this.showBanner();

            // Bind events
            this.bindEvents();
        }

        /**
         * Resolve region via config or REST and adjust template
         * Phase 4.1: Enhanced to use geo rule matching data from backend
         */
        async resolveRegionAndTemplate() {
            try {
                // Phase 4.1.1, 4.1.2: Check if matchingRule is provided from backend (server-side geo detection)
                if (this.config.matchingRule && this.config.matchingRule.id) {
                    // Use server-side matched rule (already has template, consent mode, etc.)
                    console.log('[SLOS] Using geo rule from backend:', this.config.matchingRule.name);
                    // Template already set from backend via config.template
                    // No need to fetch from API - server already did the matching
                    return;
                }

                // Phase 4.1.1: Frontend geo detection fallback (if backend didn't detect)
                if (!this.region) {
                    const geoUrl = (this.routes && this.routes.geo) || (this.config.apiUrl ? this.config.apiUrl.replace('/consents', '') + '/geo/region' : '/wp-json/shahi-legalflowsuite/v1/geo/region');
                    const resp = await fetch(geoUrl, { headers: { 'Accept': 'application/json' } });
                    const payload = await resp.json();
                    if (payload && payload.data && payload.data.region) {
                        this.region = payload.data.region;
                        console.log('[SLOS] Detected region from API:', this.region);
                        
                        // Phase 4.1.2: Apply geo-suggested template if no admin override
                        if (!this.config.template && payload.data.template) {
                            this.bannerTemplate = payload.data.template;
                            console.log('[SLOS] Using geo-suggested template:', this.bannerTemplate);
                        }
                    }
                } else {
                    // Phase 4.1.4: Fallback - region provided but template not, map it
                    if (!this.config.template) {
                        this.bannerTemplate = this.mapRegionToTemplate(this.region);
                        console.log('[SLOS] Using fallback template for region', this.region + ':', this.bannerTemplate);
                    }
                }
            } catch (e) {
                // Phase 4.1.4: Graceful degradation - non-fatal, keep defaults
                console.warn('[SLOS] Geo resolve failed, using defaults:', e);
            }
        }

        /**
         * Map region to default banner template
         * Phase 4.1.4: Fallback mapping when no geo rule matches
         * @param {string} region
         * @return {string}
         */
        mapRegionToTemplate(region) {
            const r = (region || '').toUpperCase();
            switch (r) {
                case 'EU':
                    return 'eu';
                case 'US-CA':
                    return 'ccpa';
                case 'BR':
                    return 'advanced';
                default:
                    return 'simple';
            }
        }

        /**
         * Load valid purposes from API
         *
         * @return {Promise<void>}
         */
        async loadPurposes() {
            try {
                const response = await fetch(`${this.apiUrl}/purposes`);
                const data = await response.json();
                
                if (data.data && data.data.purposes) {
                    this.purposes = data.data.purposes;
                } else {
                    // Fallback to default purposes
                    this.purposes = ['necessary', 'functional', 'analytics', 'marketing', 'preferences'];
                }
            } catch (error) {
                console.error('Failed to load purposes:', error);
                this.purposes = ['necessary', 'functional', 'analytics', 'marketing', 'preferences'];
            }
        }

        /**
         * Check if user has consent for a purpose
         *
         * @param {string} purpose The consent purpose
         * @return {Promise<boolean>}
         */
        async checkConsent(purpose) {
            // Check localStorage first
            const stored = this.getFromLocalStorage(purpose);
            if (stored !== null) {
                return stored;
            }

            // If logged in, check database
            if (this.userId > 0) {
                try {
                    const response = await fetch(`${this.apiUrl}/check?user_id=${this.userId}&type=${purpose}`);
                    const data = await response.json();
                    return data.data && data.data.has_consent === true;
                } catch (error) {
                    console.error('Error checking consent:', error);
                    return false;
                }
            }

            return false;
        }

        /**
         * Get consent from localStorage
         *
         * @param {string} purpose The consent purpose
         * @return {boolean|null}
         */
        getFromLocalStorage(purpose) {
            try {
                const consents = JSON.parse(localStorage.getItem('slos_consents') || '{}');
                if (consents[purpose] && consents[purpose].granted !== undefined) {
                    return consents[purpose].granted;
                }
            } catch (error) {
                // Ignore localStorage errors
            }
            return null;
        }

        /**
         * Show consent banner (0.2.1 & 0.2.2)
         * Uses double rAF to ensure smooth animation without FOUC
         * Phase 4.3.1: Dispatch slos-consent-shown event
         */
        showBanner() {
            const banner = this.createBanner();
            document.body.appendChild(banner);

            // Double requestAnimationFrame ensures banner is painted before animation starts
            // This prevents Flash of Unstyled Content (FOUC) and ensures smooth slide-in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    banner.classList.add('slos-banner-visible');
                });
            });
            
            // Phase 4.3.1: Dispatch analytics event when banner is shown
            document.dispatchEvent(new CustomEvent('slos-consent-shown', {
                detail: {
                    timestamp: new Date().toISOString(),
                    template: this.bannerTemplate,
                    variant: this.variant,
                    region: this.region,
                    position: this.position,
                    theme: this.theme,
                    purposes: this.purposes,
                    policyVersion: this.policyVersion,
                    bannerVersion: this.bannerVersion
                },
                bubbles: true
            }));
            
            console.log('[SLOS] Banner shown - Variant:', this.variant, 'Template:', this.bannerTemplate);
        }

        /**
         * Create banner DOM element
         *
         * @return {HTMLElement}
         */
        createBanner() {
            const div = document.createElement('div');
            div.id = 'slos-consent-banner';
            // Phase 1.4.2: Add position class for floating icon positioning
            div.className = `slos-banner slos-banner-${this.position} slos-banner-${this.theme} slos-template-${this.bannerTemplate} icon-position-${this.iconPosition}`;
            div.setAttribute('role', 'dialog');
            div.setAttribute('aria-label', this.t('bannerTitle', 'Consent Preferences'));

            div.innerHTML = this.getBannerHTML();

            return div;
        }

        /**
         * Get banner HTML based on template type
         *
         * @return {string}
         */
        getBannerHTML() {
            switch (this.bannerTemplate) {
                case 'eu':
                case 'gdpr':
                    return this.getEUBannerHTML();
                case 'ccpa':
                    return this.getCCPABannerHTML();
                case 'simple':
                    return this.getSimpleBannerHTML();
                case 'advanced':
                    return this.getAdvancedBannerHTML();
                default:
                    return this.getEUBannerHTML();
            }
        }

        /**
         * Get EU/GDPR banner HTML (with granular options)
         *
         * @return {string}
         */
        getEUBannerHTML() {
            const purposeOptions = this.purposes.map(purpose => {
                const isRequired = purpose === 'necessary' || purpose === 'functional';
                const description = this.getCategoryDescription(purpose);
                const vendorList = this.showVendors ? this.getVendorList(purpose) : '';
                
                return `
                    <div class="slos-consent-option" data-category="${purpose}">
                        <div class="slos-consent-header">
                            <label class="slos-consent-label">
                                <span class="slos-purpose-label">${this.formatPurpose(purpose)}</span>
                                ${isRequired ? '<span class="slos-required">' + this.t('required', '(Required)') + '</span>' : ''}
                                <input type="checkbox" 
                                       class="slos-consent-checkbox" 
                                       data-purpose="${purpose}"
                                       role="switch"
                                       aria-checked="${isRequired ? 'true' : 'false'}"
                                       ${isRequired ? 'checked disabled' : ''}
                                       aria-label="${this.formatPurpose(purpose)}">
                            </label>
                            ${(this.showDescriptions && description) || vendorList ? 
                                `<button class="slos-expand-toggle" data-purpose="${purpose}" aria-expanded="false" aria-label="Show details for ${this.formatPurpose(purpose)}">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>` : ''}
                        </div>
                        ${(this.showDescriptions && description) || vendorList ? 
                            `<div class="slos-consent-details" id="slos-details-${purpose}" aria-hidden="true">
                                ${this.showDescriptions && description ? `<p class="slos-category-description">${description}</p>` : ''}
                                ${vendorList}
                            </div>` : ''}
                    </div>
                `;
            }).join('');

            return `
                <div class="slos-banner-content" role="main">
                    <button class="slos-banner-close" data-action="dismiss" type="button" aria-label="${this.t('closeBanner', 'Close banner')}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="slos-banner-header">
                        <h2 class="slos-banner-title">${this.t('euTitle', 'We value your privacy')}</h2>
                    </div>
                    <div class="slos-banner-body">
                        <p class="slos-banner-message">
                            ${this.t('euMessage', 'We use cookies to enhance your experience. Click "Accept" to consent or customize your preferences.')}
                            ${this.privacyUrl ? ` <a href="${this.privacyUrl}" class="slos-privacy-link" target="_blank" rel="noopener noreferrer">${this.learnMoreText}</a>` : ''}
                        </p>
                        
                        <div class="slos-consent-options" id="slos-consent-options">
                            ${purposeOptions}
                        </div>
                    </div>
                    <div class="slos-banner-footer">
                        <button class="slos-btn slos-btn-accept-all" data-action="accept-all" type="button">${this.t('acceptAll', 'Accept All')}</button>
                        <button class="slos-btn slos-btn-reject-all" data-action="reject-all" type="button">${this.t('rejectAll', 'Reject')}</button>
                        <a href="#" class="slos-settings-link" data-action="toggle-options">${this.t('customize', 'Customize')}</a>
                    </div>
                </div>
            `;
        }

        /**
         * Get CCPA banner HTML (opt-out focus)
         *
         * @return {string}
         */
        getCCPABannerHTML() {
            return `
                <div class="slos-banner-content" role="main">
                    <button class="slos-banner-close" data-action="dismiss" type="button" aria-label="${this.t('closeBanner', 'Close banner')}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="slos-banner-body">
                        <p class="slos-banner-message">
                            ${this.t('ccpaMessage', 'We use cookies. CA residents have the right to opt-out.')}
                            ${this.privacyUrl ? ` <a href="${this.privacyUrl}" class="slos-privacy-link" target="_blank" rel="noopener noreferrer">${this.learnMoreText}</a>` : ''}
                        </p>
                    </div>
                    <div class="slos-banner-footer">
                        <button class="slos-btn slos-btn-accept-all" data-action="accept-all" type="button">${this.t('accept', 'Accept')}</button>
                        <button class="slos-btn slos-btn-reject-all" data-action="do-not-sell" type="button">${this.t('doNotSell', 'Do Not Sell')}</button>
                    </div>
                </div>
            `;
        }

        /**
         * Get simple banner HTML (just accept/reject)
         *
         * @return {string}
         */
        getSimpleBannerHTML() {
            return `
                <div class="slos-banner-content" role="main">
                    <button class="slos-banner-close" data-action="dismiss" type="button" aria-label="${this.t('closeBanner', 'Close banner')}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <div class="slos-banner-body">
                        <p class="slos-banner-message">
                            ${this.t('simpleMessage', 'We use cookies for the best experience.')}
                            ${this.privacyUrl ? ` <a href="${this.privacyUrl}" class="slos-privacy-link" target="_blank" rel="noopener noreferrer">${this.learnMoreText}</a>` : ''}
                        </p>
                    </div>
                    <div class="slos-banner-footer">
                        <button class="slos-btn slos-btn-accept" data-action="accept-all" type="button">${this.t('accept', 'OK')}</button>
                        <button class="slos-btn slos-btn-reject" data-action="reject-all" type="button">${this.t('decline', 'No')}</button>
                    </div>
                </div>
            `;
        }

        /**
         * Get advanced banner HTML (detailed options)
         *
         * @return {string}
         */
        getAdvancedBannerHTML() {
            // Similar to EU but with more detailed descriptions
            return this.getEUBannerHTML();
        }

        /**
         * Bind event handlers
         */
        bindEvents() {
            const banner = document.getElementById('slos-consent-banner');
            if (!banner) return;

            // Close/Dismiss button (0.3.3)
            banner.querySelectorAll('[data-action="dismiss"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.dismissBanner();
                });
            });

            // Accept all
            banner.querySelectorAll('[data-action="accept-all"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.acceptAll(e); // Phase 1.5: Pass event for button reference
                });
            });

            // Reject all / Do not sell
            banner.querySelectorAll('[data-action="reject-all"], [data-action="do-not-sell"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.rejectAll(e); // Phase 1.5: Pass event for button reference
                });
            });

            // Accept selected
            banner.querySelectorAll('[data-action="accept-selected"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.acceptSelected(e); // Phase 1.5: Pass event for button reference
                });
            });

            // Toggle options panel
            banner.querySelectorAll('[data-action="toggle-options"]').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleOptions();
                });
            });

            // Phase 1.4: Expand/collapse category details
            banner.querySelectorAll('.slos-expand-toggle').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleCategoryDetails(btn.getAttribute('data-purpose'));
                });
            });

            // Phase 1.3.3 & 1.3.4: Toggle checkboxes - update aria-checked and keyboard support
            banner.querySelectorAll('.slos-consent-checkbox').forEach(checkbox => {
                // Update aria-checked on change (Phase 1.3.3)
                checkbox.addEventListener('change', (e) => {
                    e.target.setAttribute('aria-checked', e.target.checked ? 'true' : 'false');
                });

                // Phase 1.3.4: Keyboard support (Space/Enter key)
                checkbox.addEventListener('keydown', (e) => {
                    // Space (32) or Enter (13) toggles the checkbox
                    if (e.keyCode === 32 || e.keyCode === 13) {
                        e.preventDefault();
                        if (!checkbox.disabled) {
                            checkbox.checked = !checkbox.checked;
                            checkbox.setAttribute('aria-checked', checkbox.checked ? 'true' : 'false');
                            // Trigger change event for any other listeners
                            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                });
            });
        }

        /**
         * Toggle consent options panel visibility
         */
        toggleOptions() {
            const options = document.getElementById('slos-consent-options');
            const footer = document.querySelector('.slos-banner-footer');
            if (options) {
                options.classList.toggle('slos-expanded');
                // Phase 1.2.4: Show save selected button when options expanded
                if (options.classList.contains('slos-expanded')) {
                    // Add accept-selected button if not exists
                    if (!footer.querySelector('[data-action="accept-selected"]')) {
                        const saveBtn = document.createElement('button');
                        saveBtn.className = 'slos-btn slos-btn-accept-selected';
                        saveBtn.setAttribute('data-action', 'accept-selected');
                        saveBtn.setAttribute('type', 'button');
                        saveBtn.textContent = this.t('savePreferences', 'Save Selected');
                        saveBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.acceptSelected();
                        });
                        // Phase 1.2.4: Insert before Customize link for proper hierarchy
                        footer.insertBefore(saveBtn, footer.querySelector('.slos-settings-link'));
                    }
                } else {
                    // Remove save button when collapsed
                    const saveBtn = footer.querySelector('[data-action="accept-selected"]');
                    if (saveBtn) {
                        saveBtn.remove();
                    }
                }
            }
        }

        /**
         * Disable all banner buttons to prevent double-submit
         * Phase 1.5.1
         *
         * @param {HTMLElement} clickedButton The button that was clicked
         * @return {void}
         */
        disableBannerButtons(clickedButton) {
            const banner = document.getElementById('slos-consent-banner');
            if (!banner) return;

            const buttons = banner.querySelectorAll('button');
            buttons.forEach(button => {
                button.disabled = true;
                button.style.pointerEvents = 'none';
                button.style.opacity = '0.6';
            });

            // Store original state of clicked button for restoration
            if (clickedButton) {
                clickedButton.dataset.originalText = clickedButton.innerHTML;
            }
        }

        /**
         * Re-enable all banner buttons after save completes
         * Phase 1.5.3
         *
         * @return {void}
         */
        enableBannerButtons() {
            const banner = document.getElementById('slos-consent-banner');
            if (!banner) return;

            const buttons = banner.querySelectorAll('button');
            buttons.forEach(button => {
                button.disabled = false;
                button.style.pointerEvents = '';
                button.style.opacity = '';

                // Restore original text if stored
                if (button.dataset.originalText) {
                    button.innerHTML = button.dataset.originalText;
                    delete button.dataset.originalText;
                }
            });
        }

        /**
         * Show loading indicator on clicked button
         * Phase 1.5.2
         *
         * @param {HTMLElement} button The button to show loading on
         * @return {void}
         */
        showButtonLoading(button) {
            if (!button) return;

            const originalText = button.innerHTML;
            button.dataset.originalText = originalText;
            button.innerHTML = '<span class="slos-spinner" style="display: inline-block; width: 14px; height: 14px; border: 2px solid currentColor; border-top-color: transparent; border-radius: 50%; animation: slos-spin 0.6s linear infinite; margin-right: 6px;"></span> Saving...';
        }

        /**
         * Accept all consents
         * Phase 1.5: Added loading states and button disabling
         *
         * @param {Event} event The click event (optional)
         * @return {Promise<void>}
         */
        async acceptAll(event) {
            const clickedButton = event?.target;

            // Phase 1.5.1: Disable all buttons
            this.disableBannerButtons(clickedButton);

            // Phase 1.5.2: Show loading on clicked button
            if (clickedButton) {
                this.showButtonLoading(clickedButton);
            }

            try {
                // Mark all as accepted
                for (const purpose of this.purposes) {
                    this.consents[purpose] = true;
                }

                // Save to database and localStorage
                for (const purpose of this.purposes) {
                    await this.grantConsent(purpose);
                }

                // Phase 3.3.2: Generate and show consent receipt
                const receipt = this.generateConsentReceipt();
                
                // Phase 4.3.2: Dispatch consent accepted event
                const acceptedCategories = this.purposes.filter(p => this.consents[p] === true);
                const startTime = performance.now();
                const duration = startTime; // Time from page load to consent
                
                document.dispatchEvent(new CustomEvent('slos-consent-accepted', {
                    detail: {
                        timestamp: new Date().toISOString(),
                        action: 'accept_all',
                        categories: acceptedCategories,
                        categoriesCount: acceptedCategories.length,
                        variant: this.variant,
                        template: this.bannerTemplate,
                        region: this.region,
                        duration: Math.round(duration),
                        policyVersion: this.policyVersion,
                        bannerVersion: this.bannerVersion
                    },
                    bubbles: true
                }));
                
                this.hideBanner();
                this.emitConsentSignals();
                this.reloadScripts();
                
                // Show receipt notification with download option
                this.showReceiptNotification(receipt);
            } catch (error) {
                console.error('Error accepting all consents:', error);
                // Phase 1.5.3: Re-enable buttons on error
                this.enableBannerButtons();
            }
        }

        /**
         * Reject all consents (except required)
         * Phase 1.5: Added loading states and button disabling
         *
         * @param {Event} event The click event (optional)
         * @return {Promise<void>}
         */
        async rejectAll(event) {
            const clickedButton = event?.target;

            // Phase 1.5.1: Disable all buttons
            this.disableBannerButtons(clickedButton);

            // Phase 1.5.2: Show loading on clicked button
            if (clickedButton) {
                this.showButtonLoading(clickedButton);
            }

            try {
                // Only accept required/functional, reject everything else
                for (const purpose of this.purposes) {
                    if (purpose === 'necessary' || purpose === 'functional') {
                        await this.grantConsent(purpose);
                        this.consents[purpose] = true;
                    } else {
                        await this.rejectConsent(purpose);
                        this.consents[purpose] = false;
                    }
                }

                // Phase 3.3.2: Generate and show consent receipt
                const receipt = this.generateConsentReceipt();
                
                // Phase 4.3.3: Dispatch consent rejected event
                const rejectedCategories = this.purposes.filter(p => this.consents[p] === false);
                const acceptedCategories = this.purposes.filter(p => this.consents[p] === true);
                
                document.dispatchEvent(new CustomEvent('slos-consent-rejected', {
                    detail: {
                        timestamp: new Date().toISOString(),
                        action: 'reject_all',
                        categoriesRejected: rejectedCategories,
                        categoriesAccepted: acceptedCategories,
                        variant: this.variant,
                        template: this.bannerTemplate,
                        region: this.region,
                        policyVersion: this.policyVersion,
                        bannerVersion: this.bannerVersion
                    },
                    bubbles: true
                }));
                
                this.hideBanner();
                this.emitConsentSignals();
                this.reloadScripts();
                
                // Show receipt notification with download option
                this.showReceiptNotification(receipt);
            } catch (error) {
                console.error('Error rejecting consents:', error);
                // Phase 1.5.3: Re-enable buttons on error
                this.enableBannerButtons();
            }
        }

        /**
         * Accept only selected consents
         * Phase 1.5: Added loading states and button disabling
         *
         * @param {Event} event The click event (optional)
         * @return {Promise<void>}
         */
        async acceptSelected(event) {
            const clickedButton = event?.target;

            // Phase 1.5.1: Disable all buttons
            this.disableBannerButtons(clickedButton);

            // Phase 1.5.2: Show loading on clicked button
            if (clickedButton) {
                this.showButtonLoading(clickedButton);
            }

            try {
                const checkboxes = document.querySelectorAll('.slos-consent-checkbox:checked');
                const selectedPurposes = Array.from(checkboxes).map(cb => cb.dataset.purpose);

                // Always ensure required are accepted
                if (!selectedPurposes.includes('necessary')) {
                    selectedPurposes.push('necessary');
                }
                if (!selectedPurposes.includes('functional')) {
                    selectedPurposes.push('functional');
                }

                // Save selected
                for (const purpose of selectedPurposes) {
                    await this.grantConsent(purpose);
                    this.consents[purpose] = true;
                }

                // Mark unselected as rejected (send to server)
                for (const purpose of this.purposes) {
                    if (!selectedPurposes.includes(purpose)) {
                        await this.rejectConsent(purpose);
                        this.consents[purpose] = false;
                    }
                }

                // Phase 3.3.2: Generate and show consent receipt
                const receipt = this.generateConsentReceipt();
                
                // Phase 4.3.4: Dispatch consent customized event
                const acceptedCategories = this.purposes.filter(p => this.consents[p] === true);
                const rejectedCategories = this.purposes.filter(p => this.consents[p] === false);
                
                document.dispatchEvent(new CustomEvent('slos-consent-customized', {
                    detail: {
                        timestamp: new Date().toISOString(),
                        action: 'customize',
                        categoriesAccepted: acceptedCategories,
                        categoriesRejected: rejectedCategories,
                        totalCategories: this.purposes.length,
                        variant: this.variant,
                        template: this.bannerTemplate,
                        region: this.region,
                        policyVersion: this.policyVersion,
                        bannerVersion: this.bannerVersion
                    },
                    bubbles: true
                }));
                
                this.hideBanner();
                this.emitConsentSignals();
                this.reloadScripts();
                
                // Show receipt notification with download option
                this.showReceiptNotification(receipt);
            } catch (error) {
                console.error('Error accepting selected consents:', error);
                // Phase 1.5.3: Re-enable buttons on error
                this.enableBannerButtons();
            }
        }

        /**
         * Grant consent for a purpose via API
         * Phase 3.2: Enhanced with banner_version, policy_version, and categories_accepted
         *
         * @param {string} purpose The consent purpose
         * @return {Promise<boolean>}
         */
        async grantConsent(purpose) {
            try {
                const bannerElement = document.getElementById('slos-consent-banner');
                const consentText = bannerElement?.querySelector('p.slos-banner-message')?.textContent || 'Consent granted via banner';

                // Phase 3.2: Collect categories_accepted for audit logging
                const categoriesAccepted = this.purposes.filter(p => this.consents[p] === true);

                const response = await fetch(`${this.apiUrl}/grant`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: this.userId,
                        purpose: purpose,
                        consent_text: consentText,
                        consent_method: 'explicit',
                        source: 'banner',
                        geo_rule_id: this.config.geoRuleId || null,
                        country_code: this.config.countryCode || '',
                        region: this.region || '',
                        // Phase 3.2: Enhanced audit logging fields
                        banner_version: this.bannerVersion,
                        policy_version: this.policyVersion,
                        categories_accepted: categoriesAccepted,
                        variant: this.variant // Phase 4.2.2: Include A/B variant in API call
                    })
                });

                const data = await response.json();
                if (data.success || data.data) {
                    this.saveToLocalStorage(purpose, true);
                    return true;
                }
            } catch (error) {
                console.error(`Failed to grant ${purpose} consent:`, error);
            }

            return false;
        }

        /**
         * Reject consent via REST API
         * Phase 3.2: Enhanced with banner_version, policy_version, and categories_accepted
         *
         * @param {string} purpose The consent purpose
         * @return {Promise<boolean>}
         */
        async rejectConsent(purpose) {
            try {
                // Phase 3.2: Collect categories_accepted for audit logging (even for rejections)
                const categoriesAccepted = this.purposes.filter(p => this.consents[p] === true);

                const response = await fetch(`${this.apiUrl}/reject`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: this.userId,
                        purpose: purpose,
                        source: 'banner',
                        geo_rule_id: this.config.geoRuleId || null,
                        country_code: this.config.countryCode || '',
                        region: this.region || '',
                        // Phase 3.2: Enhanced audit logging fields
                        banner_version: this.bannerVersion,
                        policy_version: this.policyVersion,
                        categories_accepted: categoriesAccepted,
                        variant: this.variant // Phase 4.2.2: Include A/B variant in API call
                    })
                });

                const data = await response.json();
                if (data.success || data.data) {
                    this.saveToLocalStorage(purpose, false);
                    return true;
                }
            } catch (error) {
                console.error(`Failed to reject ${purpose} consent:`, error);
            }

            return false;
        }

        /**
         * Save consent to localStorage
         * Phase 3.1: Enhanced to save consent metadata for re-consent triggers
         *
         * @param {string} purpose The consent purpose
         * @param {boolean} granted Whether consent was granted
         */
        saveToLocalStorage(purpose, granted = true) {
            try {
                const consents = JSON.parse(localStorage.getItem('slos_consents') || '{}');
                consents[purpose] = {
                    granted: granted,
                    timestamp: Date.now()
                };
                localStorage.setItem('slos_consents', JSON.stringify(consents));
                
                // Phase 3.1: Save consent metadata for re-consent tracking
                this.saveConsentMetadata();
            } catch (error) {
                console.error('Failed to save to localStorage:', error);
            }
        }
        
        /**
         * Phase 3.1.1, 3.1.2, 3.1.3: Save consent metadata for re-consent trigger logic
         * Stores policy version, banner version, accepted categories, and timestamp
         */
        saveConsentMetadata() {
            try {
                const metadata = {
                    policyVersion: this.policyVersion,
                    bannerVersion: this.bannerVersion,
                    categoriesAccepted: this.purposes.filter(p => this.consents[p] === true),
                    timestamp: Date.now(),
                    template: this.bannerTemplate,
                    region: this.region,
                    gracePeriodDays: this.gracePeriodDays, // Phase 3.4.2: Store grace period for audit trail
                    variant: this.variant // Phase 4.2.2: Store variant for analytics and audit
                };
                localStorage.setItem('slos_consent_meta', JSON.stringify(metadata));
                console.log('[SLOS] Consent metadata saved:', metadata);
            } catch (error) {
                console.error('[SLOS] Failed to save consent metadata:', error);
            }
        }
        
        /**
         * Phase 3.1.2: Generate banner version hash from configuration
         * Creates a simple hash of key banner settings to detect configuration changes
         *
         * @return {string} Banner version hash
         */
        generateBannerVersion() {
            const configString = JSON.stringify({
                template: this.bannerTemplate,
                purposes: this.purposes.sort(),
                position: this.position,
                theme: this.theme,
                expiryDays: this.consentExpiryDays
            });
            
            // Simple hash function (not cryptographic, just for change detection)
            let hash = 0;
            for (let i = 0; i < configString.length; i++) {
                const char = configString.charCodeAt(i);
                hash = ((hash << 5) - hash) + char;
                hash = hash & hash; // Convert to 32-bit integer
            }
            
            return Math.abs(hash).toString(36);
        }
        
        /**
         * Phase 4.2.3: Assign A/B testing variant
         * Assigns variant from config or generates random A/B split
         *
         * @return {string} Variant identifier (e.g., 'A', 'B', 'control', 'variant1')
         */
        assignVariant() {
            // Use explicitly configured variant if provided
            if (this.config.variant) {
                return this.config.variant;
            }
            
            // Check localStorage for persistent variant assignment
            try {
                const stored = localStorage.getItem('slos_ab_variant');
                if (stored) {
                    return stored;
                }
            } catch (e) {
                console.warn('[SLOS] Could not read variant from localStorage:', e);
            }
            
            // Generate random A/B split (50/50)
            const variant = Math.random() < 0.5 ? 'A' : 'B';
            
            // Persist variant assignment
            try {
                localStorage.setItem('slos_ab_variant', variant);
            } catch (e) {
                console.warn('[SLOS] Could not store variant in localStorage:', e);
            }
            
            return variant;
        }

        /**
         * Emit consent signals (Google Consent Mode v2, etc.)
         */
        emitConsentSignals() {
            // Google Consent Mode v2 signals
            if (window.gtag && typeof window.gtag === 'function') {
                const state = this.buildConsentModeState();
                window.gtag('consent', 'update', state);
            }

            // Custom event for other scripts to listen
            // Phase 4.2.2: Include variant in event payload
            document.dispatchEvent(new CustomEvent('slos-consent-updated', {
                detail: { 
                    consents: this.consents, 
                    purposes: this.purposes,
                    variant: this.variant,
                    template: this.bannerTemplate,
                    region: this.region
                }
            }));

            // WordPress Consent API compatibility
            for (const [purpose, granted] of Object.entries(this.consents)) {
                document.dispatchEvent(new CustomEvent('wp_consent_category_set', {
                    detail: { 
                        category: purpose, 
                        granted: granted,
                        variant: this.variant // Phase 4.2.2: Include variant
                    }
                }));
            }
        }

        /**
         * Generate consent receipt data structure
         * Phase 3.3.1: Creates comprehensive receipt with timestamp, categories, versions, user agent
         *
         * @return {Object} Consent receipt object
         */
        generateConsentReceipt() {
            const timestamp = new Date().toISOString();
            const acceptedCategories = this.purposes.filter(p => this.consents[p] === true);
            const rejectedCategories = this.purposes.filter(p => this.consents[p] === false);

            return {
                receiptId: `slos-receipt-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                timestamp: timestamp,
                humanReadableDate: new Date().toLocaleString(),
                consentGiven: acceptedCategories.length > 0,
                
                // Categories
                categoriesAccepted: acceptedCategories,
                categoriesRejected: rejectedCategories,
                totalCategories: this.purposes.length,
                
                // Version information
                policyVersion: this.policyVersion,
                bannerVersion: this.bannerVersion,
                bannerTemplate: this.bannerTemplate,
                
                // User context
                userAgent: navigator.userAgent,
                language: navigator.language || 'en',
                region: this.region || 'UNKNOWN',
                
                // Configuration
                consentExpiryDays: this.consentExpiryDays,
                privacyPolicyUrl: this.privacyUrl,
                
                // Compliance information
                complianceFramework: this.getComplianceFramework(),
                legalBasis: 'consent',
                
                // Metadata
                websiteUrl: window.location.origin,
                pageUrl: window.location.href,
                platform: 'Shahi LegalOps Suite',
                pluginVersion: this.config.pluginVersion || '3.1.1'
            };
        }

        /**
         * Get compliance framework based on template
         * Phase 3.3.1: Helper to determine applicable compliance framework
         *
         * @return {string} Compliance framework name
         */
        getComplianceFramework() {
            switch (this.bannerTemplate) {
                case 'eu':
                case 'gdpr':
                    return 'GDPR (General Data Protection Regulation)';
                case 'ccpa':
                    return 'CCPA (California Consumer Privacy Act)';
                case 'simple':
                    return 'Generic Cookie Notice';
                case 'advanced':
                    return 'Multi-jurisdiction (GDPR/CCPA/LGPD)';
                default:
                    return 'Cookie Consent Framework';
            }
        }

        /**
         * Download consent receipt as JSON file
         * Phase 3.3.3: Generates and triggers browser download of consent receipt
         *
         * @param {Object} receipt Consent receipt object
         * @return {void}
         */
        downloadConsentReceipt(receipt) {
            try {
                const receiptData = receipt || this.generateConsentReceipt();
                const json = JSON.stringify(receiptData, null, 2);
                const blob = new Blob([json], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                
                const link = document.createElement('a');
                link.href = url;
                link.download = `consent-receipt-${Date.now()}.json`;
                link.style.display = 'none';
                
                document.body.appendChild(link);
                link.click();
                
                // Cleanup
                setTimeout(() => {
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                }, 100);
                
                console.log('[SLOS] Consent receipt downloaded:', receiptData.receiptId);
            } catch (error) {
                console.error('[SLOS] Failed to download consent receipt:', error);
            }
        }

        /**
         * Show consent receipt notification with download link
         * Phase 3.3.2: Displays success message with optional download link
         *
         * @param {Object} receipt Consent receipt object
         * @return {void}
         */
        showReceiptNotification(receipt) {
            // Remove any existing notifications
            const existingNotification = document.getElementById('slos-receipt-notification');
            if (existingNotification) {
                existingNotification.remove();
            }

            // Create notification element
            const notification = document.createElement('div');
            notification.id = 'slos-receipt-notification';
            notification.className = 'slos-receipt-notification';
            notification.innerHTML = `
                <div class="slos-receipt-content">
                    <span class="slos-receipt-icon">✓</span>
                    <div class="slos-receipt-text">
                        <strong>Consent preferences saved</strong>
                        <p>Your privacy choices have been recorded.</p>
                    </div>
                    <button class="slos-receipt-download" title="Download consent receipt">
                        <span class="dashicons dashicons-download"></span>
                        Download Receipt
                    </button>
                    <button class="slos-receipt-close" title="Close">&times;</button>
                </div>
            `;

            // Add styles if not already present
            if (!document.getElementById('slos-receipt-styles')) {
                const style = document.createElement('style');
                style.id = 'slos-receipt-styles';
                style.textContent = `
                    .slos-receipt-notification {
                        position: fixed;
                        bottom: 24px;
                        right: 24px;
                        background: #ffffff;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                        padding: 16px;
                        max-width: 400px;
                        z-index: 999999;
                        animation: slos-slide-in 0.3s ease-out;
                    }
                    .slos-receipt-content {
                        display: flex;
                        align-items: flex-start;
                        gap: 12px;
                    }
                    .slos-receipt-icon {
                        flex-shrink: 0;
                        width: 24px;
                        height: 24px;
                        background: #10b981;
                        color: #ffffff;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 14px;
                        font-weight: bold;
                    }
                    .slos-receipt-text {
                        flex: 1;
                    }
                    .slos-receipt-text strong {
                        display: block;
                        margin-bottom: 4px;
                        color: #111827;
                        font-size: 14px;
                    }
                    .slos-receipt-text p {
                        margin: 0;
                        color: #6b7280;
                        font-size: 13px;
                    }
                    .slos-receipt-download {
                        flex-shrink: 0;
                        background: #3b82f6;
                        color: #ffffff;
                        border: none;
                        border-radius: 6px;
                        padding: 8px 12px;
                        font-size: 13px;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        transition: background 0.2s;
                    }
                    .slos-receipt-download:hover {
                        background: #2563eb;
                    }
                    .slos-receipt-download .dashicons {
                        font-size: 16px;
                        width: 16px;
                        height: 16px;
                    }
                    .slos-receipt-close {
                        flex-shrink: 0;
                        background: none;
                        border: none;
                        color: #9ca3af;
                        font-size: 24px;
                        line-height: 1;
                        cursor: pointer;
                        padding: 0;
                        width: 24px;
                        height: 24px;
                        transition: color 0.2s;
                    }
                    .slos-receipt-close:hover {
                        color: #4b5563;
                    }
                    @keyframes slos-slide-in {
                        from {
                            transform: translateX(400px);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                    @keyframes slos-slide-out {
                        from {
                            transform: translateX(0);
                            opacity: 1;
                        }
                        to {
                            transform: translateX(400px);
                            opacity: 0;
                        }
                    }
                    @media (max-width: 640px) {
                        .slos-receipt-notification {
                            bottom: 12px;
                            right: 12px;
                            left: 12px;
                            max-width: none;
                        }
                    }
                `;
                document.head.appendChild(style);
            }

            document.body.appendChild(notification);

            // Bind events
            const downloadBtn = notification.querySelector('.slos-receipt-download');
            const closeBtn = notification.querySelector('.slos-receipt-close');

            downloadBtn.addEventListener('click', () => {
                this.downloadConsentReceipt(receipt);
            });

            closeBtn.addEventListener('click', () => {
                notification.style.animation = 'slos-slide-out 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            });

            // Auto-dismiss after 10 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.animation = 'slos-slide-out 0.3s ease-out';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 10000);
        }

        /**
         * Build Google Consent Mode v2 state
         *
         * @return {Object}
         */
        buildConsentModeState() {
            return {
                ad_storage: this.consents.marketing ? 'granted' : 'denied',
                analytics_storage: this.consents.analytics ? 'granted' : 'denied',
                ad_user_data: this.consents.marketing ? 'granted' : 'denied',
                ad_personalization: this.consents.personalization ? 'granted' : 'denied',
                functionality_storage: this.consents.functional ? 'granted' : 'denied',
                personalization_storage: this.consents.preferences ? 'granted' : 'denied'
            };
        }

        /**
         * Hide banner with animation
         */
        hideBanner() {
            const banner = document.getElementById('slos-consent-banner');
            if (banner) {
                banner.classList.remove('slos-banner-visible');
                setTimeout(() => {
                    if (banner.parentNode) {
                        banner.remove();
                    }
                }, 300); // Match animation duration
            }
        }

        /**
         * Dismiss banner without consent (0.3.3)
         * Stores dismissal timestamp in localStorage and hides banner
         */
        dismissBanner() {
            try {
                const dismissalData = {
                    timestamp: Date.now(),
                    dismissed: true
                };
                localStorage.setItem('slos_banner_dismissed', JSON.stringify(dismissalData));
            } catch (error) {
                // Ignore localStorage errors
            }
            this.hideBanner();
        }

        /**
         * Reload scripts that need consent
         */
        reloadScripts() {
            // Trigger event for script blocker
            document.dispatchEvent(new CustomEvent('slos-consents-accepted', {
                detail: { consents: this.consents }
            }));

            // Reload page if configured
            if (this.config.reloadOnConsent) {
                location.reload();
            }
        }

        /**
         * Phase 3.1.4: Check if banner should show again (comprehensive re-consent logic)
         * Checks 4 conditions: policy version change, banner config change, new categories, expiry
         *
         * @return {boolean}
         */
        shouldShowAgain() {
            try {
                const metadata = JSON.parse(localStorage.getItem('slos_consent_meta') || '{}');
                
                // If no metadata stored, this is first visit - don't show again
                if (!metadata.timestamp) {
                    return false;
                }
                
                // Phase 3.4.2: Check if within grace period for re-consent triggers
                // Grace period only applies to policy/config changes, NOT to natural expiry or new categories
                const gracePeriodMs = this.gracePeriodDays * 24 * 60 * 60 * 1000;
                const lastConsentTime = metadata.timestamp;
                const timeSinceConsent = Date.now() - lastConsentTime;
                const isWithinGracePeriod = gracePeriodMs > 0 && timeSinceConsent < gracePeriodMs;
                
                // Phase 3.1.1: Check if policy version changed
                if (metadata.policyVersion && metadata.policyVersion !== this.policyVersion) {
                    // Grace period applies: if grace period is active, allow existing consent to continue
                    if (isWithinGracePeriod) {
                        console.log('[SLOS] Policy version changed, but within grace period. Days remaining:', 
                            Math.ceil((gracePeriodMs - timeSinceConsent) / (24 * 60 * 60 * 1000)));
                        return false; // Don't show banner yet
                    }
                    console.log('[SLOS] Re-consent required: Policy version changed (grace period expired or disabled)');
                    return true;
                }
                
                // Phase 3.1.2: Check if banner configuration changed
                if (metadata.bannerVersion && metadata.bannerVersion !== this.bannerVersion) {
                    // Grace period applies: if grace period is active, allow existing consent to continue
                    if (isWithinGracePeriod) {
                        console.log('[SLOS] Banner configuration changed, but within grace period. Days remaining:', 
                            Math.ceil((gracePeriodMs - timeSinceConsent) / (24 * 60 * 60 * 1000)));
                        return false; // Don't show banner yet
                    }
                    console.log('[SLOS] Re-consent required: Banner configuration changed (grace period expired or disabled)');
                    return true;
                }
                
                // Phase 3.1.3: Check if new categories were added
                // NEW CATEGORIES: Grace period does NOT apply - must obtain consent immediately for new data processing
                const storedCategories = metadata.categoriesAccepted || [];
                const newCategories = this.purposes.filter(p => !storedCategories.includes(p));
                if (newCategories.length > 0) {
                    console.log('[SLOS] Re-consent required: New categories added (immediate, no grace period):', newCategories);
                    return true;
                }
                
                // Phase 3.1.4: Check if consent expired (using configurable expiry days)
                // NATURAL EXPIRY: Grace period does NOT apply - consent must be refreshed
                const expiryDays = this.consentExpiryDays || 30;
                const daysSince = (Date.now() - metadata.timestamp) / (1000 * 60 * 60 * 24);
                if (daysSince > expiryDays) {
                    console.log('[SLOS] Re-consent required: Consent expired after', expiryDays, 'days (no grace period)');
                    return true;
                }
                
            } catch (error) {
                console.error('[SLOS] Error checking re-consent:', error);
            }
            
            return false;
        }

        /**
         * Get translated string
         *
         * @param {string} key Translation key
         * @param {string} fallback Fallback text
         * @return {string}
         */
        t(key, fallback) {
            return this.translations[key] || fallback;
        }

        /**
         * Format purpose name for display
         *
         * @param {string} purpose The purpose
         * @return {string}
         */
        formatPurpose(purpose) {
            const formatted = {
                'necessary': this.t('purposeNecessary', 'Necessary'),
                'functional': this.t('purposeFunctional', 'Functional'),
                'analytics': this.t('purposeAnalytics', 'Analytics'),
                'marketing': this.t('purposeMarketing', 'Marketing'),
                'preferences': this.t('purposePreferences', 'Preferences'),
                'personalization': this.t('purposePersonalization', 'Personalization')
            };

            return formatted[purpose] || purpose.charAt(0).toUpperCase() + purpose.slice(1);
        }

        /**
         * Get purpose description
         *
         * @param {string} purpose The purpose
         * @return {string}
         */
        getPurposeDescription(purpose) {
            const descriptions = {
                'necessary': this.t('descNecessary', 'Required for the website to function properly'),
                'functional': this.t('descFunctional', 'Enables enhanced functionality and personalization'),
                'analytics': this.t('descAnalytics', 'Helps us understand how visitors use our website'),
                'marketing': this.t('descMarketing', 'Used to deliver relevant ads and marketing campaigns'),
                'preferences': this.t('descPreferences', 'Remembers your preferences and settings'),
                'personalization': this.t('descPersonalization', 'Delivers personalized content based on your interests')
            };

            return descriptions[purpose] || '';
        }

        /**
         * Get category description from config
         *
         * @since 3.1.1
         * @param {string} category Category name
         * @return {string}
         */
        getCategoryDescription(category) {
            return this.categoryDescriptions[category] || this.getPurposeDescription(category);
        }

        /**
         * Get vendor list HTML for category
         *
         * @since 3.1.1
         * @param {string} category Category name
         * @return {string}
         */
        getVendorList(category) {
            const vendorData = this.vendors[category];
            if (!vendorData || !Array.isArray(vendorData) || vendorData.length === 0) {
                return '';
            }

            const vendorItems = vendorData.map(vendor => {
                return `
                    <li class="slos-vendor-item">
                        <span class="slos-vendor-name">${vendor.name}</span>
                        ${vendor.purpose ? `<span class="slos-vendor-purpose">${vendor.purpose}</span>` : ''}
                    </li>
                `;
            }).join('');

            return `
                <div class="slos-vendor-list">
                    <strong class="slos-vendor-heading">${this.t('services', 'Services & Vendors')}:</strong>
                    <ul class="slos-vendors">
                        ${vendorItems}
                    </ul>
                </div>
            `;
        }

        /**
         * Toggle category details visibility
         *
         * @since 3.1.1
         * @param {string} purpose Purpose/category name
         */
        toggleCategoryDetails(purpose) {
            const detailsEl = document.getElementById(`slos-details-${purpose}`);
            const toggleBtn = document.querySelector(`.slos-expand-toggle[data-purpose="${purpose}"]`);
            
            if (!detailsEl || !toggleBtn) return;

            const isExpanded = detailsEl.getAttribute('aria-hidden') === 'false';
            
            detailsEl.setAttribute('aria-hidden', isExpanded ? 'true' : 'false');
            detailsEl.style.display = isExpanded ? 'none' : 'block';
            
            toggleBtn.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
            toggleBtn.querySelector('.dashicons').classList.toggle('dashicons-arrow-down-alt2', isExpanded);
            toggleBtn.querySelector('.dashicons').classList.toggle('dashicons-arrow-up-alt2', !isExpanded);
        }
    }

    /**
     * Initialize banner when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new ConsentBanner();
        });
    } else {
        new ConsentBanner();
    }
})();

