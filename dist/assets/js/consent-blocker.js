/**
 * Consent Blocker Script
 *
 * Runs in <head> to intercept and block tracking scripts before execution.
 * Prevents data leakage before user gives consent.
 *
 * @package ShahiLegalOpsSuite\Modules\Consent
 * @since 1.0.0
 */

(function() {
  'use strict';

  // Configuration passed from PHP.
  const config = window.complyflowConfig || {};
  const blockingRules = config.blockingRules || [];
  const currentConsent = config.currentConsent || {};
  const sessionId = config.sessionId || '';

  // Queue for blocked scripts.
  const blockedScripts = [];

  // Override script loading to check rules.
  const originalFetch = window.fetch;
  const originalXHR = window.XMLHttpRequest;

  /**
   * Check if a URL matches any blocking rule.
   */
  function shouldBlockUrl(url) {
    if (!url || !blockingRules.length) return null;

    for (const rule of blockingRules) {
      if (matchesPattern(url, rule.pattern)) {
        const category = rule.category;
        const hasConsent = currentConsent[category];

        if (!hasConsent) {
          return rule;
        }
      }
    }
    return null;
  }

  /**
   * Test if URL matches pattern (substring or regex).
   */
  function matchesPattern(url, pattern) {
    if (!pattern) return false;

    // Regex pattern (starts with /).
    if (pattern.startsWith('/') && pattern.includes('/')) {
      try {
        const match = pattern.match(/^\/(.*)\/([gimuy]*)$/);
        if (match) {
          const regex = new RegExp(match[1], match[2]);
          return regex.test(url);
        }
      } catch (e) {
        console.warn('[Consent Blocker] Invalid regex pattern:', pattern);
      }
    }

    // Substring match.
    return url.includes(pattern);
  }

  /**
   * Intercept fetch() calls for tracking APIs.
   */
  window.fetch = function(...args) {
    const url = args[0] || '';
    const rule = shouldBlockUrl(url);

    if (rule) {
      console.log(`[Consent Blocker] Blocked fetch to ${url}`, rule);
      // Return rejected promise to prevent API call.
      return Promise.reject(new Error(`Blocked by consent: ${rule.id}`));
    }

    return originalFetch.apply(this, args);
  };

  /**
   * Intercept XMLHttpRequest for tracking.
   */
  const XHROpen = XMLHttpRequest.prototype.open;
  XMLHttpRequest.prototype.open = function(method, url) {
    const rule = shouldBlockUrl(url);

    if (rule) {
      console.log(`[Consent Blocker] Blocked XHR to ${url}`, rule);
      this._consentBlocked = true;
      this._blockingRule = rule;
    }

    return XHROpen.apply(this, arguments);
  };

  // Prevent blocked XHR from sending.
  const XHRSend = XMLHttpRequest.prototype.send;
  XMLHttpRequest.prototype.send = function() {
    if (this._consentBlocked) {
      console.log(`[Consent Blocker] Prevented XHR send for rule: ${this._blockingRule.id}`);
      return; // Don't send blocked request.
    }
    return XHRSend.apply(this, arguments);
  };

  /**
   * Observer for dynamically injected scripts.
   */
  if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        if (mutation.type === 'childList') {
          mutation.addedNodes.forEach(function(node) {
            // Check for script tags.
            if (node.nodeName === 'SCRIPT' && node.src) {
              const rule = shouldBlockUrl(node.src);
              if (rule) {
                console.log(`[Consent Blocker] Removed dynamically injected script: ${node.src}`);
                // Remove blocked script from DOM.
                node.parentNode.removeChild(node);
                // Queue for later replay.
                blockedScripts.push({
                  scriptTag: node.outerHTML,
                  url: node.src,
                  rule: rule,
                });
              }
            }

            // Check for iframes.
            if (node.nodeName === 'IFRAME' && node.src) {
              const rule = shouldBlockUrl(node.src);
              if (rule && rule.action === 'replace_with_placeholder') {
                console.log(`[Consent Blocker] Replaced iframe with placeholder: ${node.src}`);
                // Store original src.
                node.dataset.consentSrc = node.src;
                // Clear src to prevent loading.
                node.src = 'about:blank';
                node.classList.add('complyflow-iframe-blocked');
              }
            }
          });
        }
      });
    });

    // Start observing.
    observer.observe(document.documentElement, {
      childList: true,
      subtree: true,
    });
  }

  /**
   * Store blocked scripts for later replay.
   */
  window.complyflowBlockedScripts = blockedScripts;

  /**
   * Replay blocked scripts when consent is granted.
   */
  window.complyflowReplayScripts = function(updatedConsent) {
    if (!blockedScripts.length) return;

    console.log(`[Consent Blocker] Replaying ${blockedScripts.length} blocked scripts`);

    for (const blocked of blockedScripts) {
      const category = blocked.rule.category;
      if (updatedConsent[category]) {
        // Recreate and inject script.
        const script = document.createElement('script');
        script.src = blocked.url;
        script.async = true;
        script.dataset.consentReplayed = true;
        document.head.appendChild(script);

        console.log(`[Consent Blocker] Replayed script: ${blocked.url}`);
      }
    }
  };

  /**
   * Re-enable blocked iframes when consent granted.
   */
  window.complyflowEnableIframes = function(updatedConsent) {
    const blockedIframes = document.querySelectorAll('iframe.complyflow-iframe-blocked');

    blockedIframes.forEach(function(iframe) {
      const rule = iframe.dataset.consentRule;
      if (!rule) return;

      // Try to parse rule from attribute.
      const consentSrc = iframe.dataset.consentSrc;
      if (consentSrc && updatedConsent[rule]) {
        iframe.src = consentSrc;
        iframe.classList.remove('complyflow-iframe-blocked');
        console.log(`[Consent Blocker] Re-enabled iframe: ${consentSrc}`);
      }
    });
  };

  // Debug mode.
  if (config.debug) {
    console.log('[Consent Blocker] Initialized', {
      blockingRules,
      currentConsent,
      sessionId,
    });
  }
})();

