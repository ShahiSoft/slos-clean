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
            this.apiUrl = this.config.apiUrl || '/wp-json/slos/v1/consents';
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
         */
        async resolveRegionAndTemplate() {
            try {
                if (!this.region) {
                    const geoUrl = (this.routes && this.routes.geo) || (this.config.apiUrl ? this.config.apiUrl.replace('/consents', '') + '/geo/region' : '/wp-json/slos/v1/geo/region');
                    const resp = await fetch(geoUrl, { headers: { 'Accept': 'application/json' } });
                    const payload = await resp.json();
                    if (payload && payload.data && payload.data.region) {
                        this.region = payload.data.region;
                        if (!this.config.template && payload.data.template) {
                            this.bannerTemplate = payload.data.template;
                        }
                    }
                } else {
                    // If region provided but template not, map it
                    if (!this.config.template) {
                        this.bannerTemplate = this.mapRegionToTemplate(this.region);
                    }
                }
            } catch (e) {
                // Non-fatal: keep defaults
                // console.warn('Geo resolve failed', e);
            }
        }

        /**
         * Map region to default banner template
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
         * Show consent banner
         */
        showBanner() {
            const banner = this.createBanner();
            document.body.appendChild(banner);

            // Trigger animation
            setTimeout(() => {
                banner.classList.add('slos-banner-visible');
            }, 100);
        }

        /**
         * Create banner DOM element
         *
         * @return {HTMLElement}
         */
        createBanner() {
            const div = document.createElement('div');
            div.id = 'slos-consent-banner';
            div.className = `slos-banner slos-banner-${this.position} slos-banner-${this.theme} slos-template-${this.bannerTemplate}`;
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
                return `
                    <div class="slos-consent-option">
                        <label class="slos-consent-label">
                            <span class="slos-purpose-label">${this.formatPurpose(purpose)}</span>
                            ${isRequired ? '<span class="slos-required">' + this.t('required', '(Required)') + '</span>' : ''}
                            <input type="checkbox" 
                                   class="slos-consent-checkbox" 
                                   data-purpose="${purpose}"
                                   ${isRequired ? 'checked disabled' : ''}
                                   aria-label="${this.formatPurpose(purpose)}">
                        </label>
                    </div>
                `;
            }).join('');

            return `
                <div class="slos-banner-content" role="main">
                    <div class="slos-banner-header">
                        <h2 class="slos-banner-title">${this.t('euTitle', 'We value your privacy')}</h2>
                    </div>
                    <div class="slos-banner-body">
                        <p class="slos-banner-message">${this.t('euMessage', 'We use cookies to enhance your experience. Click "Accept" to consent or customize your preferences.')}</p>
                        
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
                    <div class="slos-banner-body">
                        <p class="slos-banner-message">${this.t('ccpaMessage', 'We use cookies. CA residents have the right to opt-out.')}</p>
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
                    <div class="slos-banner-body">
                        <p class="slos-banner-message">${this.t('simpleMessage', 'We use cookies for the best experience.')}</p>
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

            // Accept all
            banner.querySelectorAll('[data-action="accept-all"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.acceptAll();
                });
            });

            // Reject all / Do not sell
            banner.querySelectorAll('[data-action="reject-all"], [data-action="do-not-sell"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.rejectAll();
                });
            });

            // Accept selected
            banner.querySelectorAll('[data-action="accept-selected"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.acceptSelected();
                });
            });

            // Toggle options panel
            banner.querySelectorAll('[data-action="toggle-options"]').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.toggleOptions();
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
                // Show save selected button when expanded
                if (options.classList.contains('slos-expanded')) {
                    // Add accept-selected button if not exists
                    if (!footer.querySelector('[data-action="accept-selected"]')) {
                        const saveBtn = document.createElement('button');
                        saveBtn.className = 'slos-btn slos-btn-accept-selected';
                        saveBtn.setAttribute('data-action', 'accept-selected');
                        saveBtn.setAttribute('type', 'button');
                        saveBtn.textContent = this.t('savePreferences', 'Save');
                        saveBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            this.acceptSelected();
                        });
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
         * Accept all consents
         *
         * @return {Promise<void>}
         */
        async acceptAll() {
            // Mark all as accepted
            for (const purpose of this.purposes) {
                this.consents[purpose] = true;
            }

            // Save to database and localStorage
            for (const purpose of this.purposes) {
                await this.grantConsent(purpose);
            }

            this.hideBanner();
            this.emitConsentSignals();
            this.reloadScripts();
        }

        /**
         * Reject all consents (except required)
         *
         * @return {Promise<void>}
         */
        async rejectAll() {
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

            this.hideBanner();
            this.emitConsentSignals();
        }

        /**
         * Accept only selected consents
         *
         * @return {Promise<void>}
         */
        async acceptSelected() {
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

            this.hideBanner();
            this.emitConsentSignals();
            this.reloadScripts();
        }

        /**
         * Grant consent for a purpose via API
         *
         * @param {string} purpose The consent purpose
         * @return {Promise<boolean>}
         */
        async grantConsent(purpose) {
            try {
                const bannerElement = document.getElementById('slos-consent-banner');
                const consentText = bannerElement?.querySelector('p.slos-banner-message')?.textContent || 'Consent granted via banner';

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
                        region: this.region || ''
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
         *
         * @param {string} purpose The consent purpose
         * @return {Promise<boolean>}
         */
        async rejectConsent(purpose) {
            try {
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
                        region: this.region || ''
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
            } catch (error) {
                console.error('Failed to save to localStorage:', error);
            }
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
            document.dispatchEvent(new CustomEvent('slos-consent-updated', {
                detail: { consents: this.consents, purposes: this.purposes }
            }));

            // WordPress Consent API compatibility
            for (const [purpose, granted] of Object.entries(this.consents)) {
                document.dispatchEvent(new CustomEvent('wp_consent_category_set', {
                    detail: { category: purpose, granted: granted }
                }));
            }
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
         * Check if banner should be shown again
         *
         * @return {boolean}
         */
        shouldShowAgain() {
            try {
                const consents = JSON.parse(localStorage.getItem('slos_consents') || '{}');
                const lastConsent = Object.values(consents)[0];
                
                if (lastConsent && lastConsent.timestamp) {
                    const daysPassed = (Date.now() - lastConsent.timestamp) / (1000 * 60 * 60 * 24);
                    return daysPassed > 30;
                }
            } catch (error) {
                // Ignore
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

