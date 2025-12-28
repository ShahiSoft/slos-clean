/**
 * Consent Signals Script
 *
 * Emits consent signals to GTM, GCM v2, WP Consent API.
 * Runs in footer after banner and blocking scripts.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent
 * @since 1.0.0
 */

(function() {
  'use strict';

  const config = window.complyflowConfig || {};
  const currentConsent = config.currentConsent || {};

  /**
   * Emit GTM DataLayer Event
   */
  function emitGTMEvent() {
    if (typeof window.dataLayer === 'undefined') {
      return;
    }

    const event = {
      event: 'consent_initialized',
      event_category: 'consent',
      event_label: 'page_view_with_consent',
      consent_analytics: !!currentConsent.analytics,
      consent_marketing: !!currentConsent.marketing,
      consent_functional: !!currentConsent.functional,
      consent_necessary: true,
      consent_timestamp: new Date().getTime(),
    };

    window.dataLayer.push(event);
    console.log('[Consent Signals] GTM event pushed:', event);
  }

  /**
   * Emit Google Consent Mode v2
   */
  function emitGCM() {
    if (typeof window.gtag !== 'function') {
      return;
    }

    const gcmPayload = {
      analytics_storage: currentConsent.analytics ? 'granted' : 'denied',
      ad_storage: currentConsent.marketing ? 'granted' : 'denied',
      ad_user_data: currentConsent.marketing && currentConsent.functional ? 'granted' : 'denied',
      ad_personalization: currentConsent.marketing ? 'granted' : 'denied',
      wait_for_update: 500,
    };

    window.gtag('consent', 'default', gcmPayload);
    console.log('[Consent Signals] GCM v2 initialized:', gcmPayload);
  }

  /**
   * Emit WordPress Consent API
   */
  function emitWPConsentAPI() {
    for (const [category, granted] of Object.entries(currentConsent)) {
      // Dispatch custom event for plugins listening to WP Consent API.
      const event = new CustomEvent('wp_consent_category_set', {
        detail: {
          category,
          granted: !!granted,
        },
      });
      document.dispatchEvent(event);
    }

    console.log('[Consent Signals] WordPress Consent API signals emitted');
  }

  /**
   * Emit Custom Consent Events
   */
  function emitCustomEvents() {
    // Fire hooks that other scripts can listen to.
    const event = new CustomEvent('complyflow_consent_ready', {
      detail: {
        consents: currentConsent,
        timestamp: new Date().getTime(),
      },
    });
    document.dispatchEvent(event);

    // Add global access.
    window.complyflowConsents = currentConsent;
  }

  /**
   * Initialize all signals.
   */
  function init() {
    console.log('[Consent Signals] Initializing with consent:', currentConsent);

    // 1. GTM DataLayer.
    emitGTMEvent();

    // 2. Google Consent Mode v2.
    emitGCM();

    // 3. WordPress Consent API.
    emitWPConsentAPI();

    // 4. Custom events.
    emitCustomEvents();

    // 5. Mark as initialized.
    window.complyflowSignalsInitialized = true;
  }

  /**
   * Wait for gtag to be available if needed.
   */
  if (config.waitForGtag && typeof window.gtag === 'undefined') {
    // Poll for gtag (max 5 seconds).
    const startTime = Date.now();
    const pollInterval = setInterval(function() {
      if (typeof window.gtag === 'function' || Date.now() - startTime > 5000) {
        clearInterval(pollInterval);
        init();
      }
    }, 100);
  } else {
    init();
  }
})();

