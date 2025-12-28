/*
 * Cookie Scanner Component
 *
 * Scans document cookies, localStorage, and sessionStorage to build an
 * inventory. Submits results to the REST API at /wp-json/slos/v1/cookies/report.
 * Listens for consent updates to re-scan when preferences change.
 */
(function () {
  'use strict';

  function getAllCookies() {
    const cookieStr = document.cookie || '';
    if (!cookieStr) return [];
    return cookieStr.split(';').map((c) => {
      const [rawName, ...rest] = c.split('=');
      const name = (rawName || '').trim();
      const value = rest.join('=');
      return { name, value };
    }).filter((c) => c.name.length > 0);
  }

  function getAllLocalStorageKeys() {
    try {
      const keys = [];
      for (let i = 0; i < localStorage.length; i++) {
        const k = localStorage.key(i);
        if (k) keys.push(k);
      }
      return keys;
    } catch (e) {
      return [];
    }
  }

  function getAllSessionStorageKeys() {
    try {
      const keys = [];
      for (let i = 0; i < sessionStorage.length; i++) {
        const k = sessionStorage.key(i);
        if (k) keys.push(k);
      }
      return keys;
    } catch (e) {
      return [];
    }
  }

  async function submitReport(payload) {
    const apiBase = (window.slosConsentConfig && window.slosConsentConfig.apiUrl) || '/wp-json/slos/v1';
    try {
      const res = await fetch(apiBase + '/cookies/report', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      if (!res.ok) {
        console.warn('[SLOS] Cookie scanner report failed:', res.status);
        return null;
      }
      const data = await res.json();
      console.log('[SLOS] Cookie scanner report success:', data);
      // Dispatch event for admin dashboards or listeners
      window.dispatchEvent(new CustomEvent('slos-cookie-scan-complete', { detail: data }));
      return data;
    } catch (err) {
      console.error('[SLOS] Cookie scanner error:', err);
      return null;
    }
  }

  async function runScan() {
    const payload = {
      cookies: getAllCookies(),
      localStorageKeys: getAllLocalStorageKeys(),
      sessionStorageKeys: getAllSessionStorageKeys(),
      url: window.location.href,
      userAgent: navigator.userAgent
    };
    return submitReport(payload);
  }

  // Expose minimal API
  window.slosCookieScanner = {
    runScan
  };

  // Auto-run on DOM ready
  document.addEventListener('DOMContentLoaded', () => {
    runScan();
  });

  // Re-scan on consent updates
  document.addEventListener('slos-consent-updated', () => {
    runScan();
  });
})();
