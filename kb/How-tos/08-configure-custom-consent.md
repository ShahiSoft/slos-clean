# Configure Custom Consent Categories

## Create Advanced Cookie Categories & Consent Mechanisms

### Understanding Consent Categories

#### Default Categories Overview

**Essential Cookies (Always Required):**
- Strictly necessary for website functionality
- Cannot be disabled by users
- No consent required under GDPR/ePrivacy

**Analytics Cookies (Optional):**
- Track website usage and performance
- Help improve user experience
- Require explicit consent

**Marketing Cookies (Optional):**
- Used for advertising and retargeting
- Track users across websites
- Require explicit consent

**Preferences Cookies (Optional):**
- Remember user preferences and settings
- Enhance user experience
- Require explicit consent

### Create Custom Categories

#### Step 1: Access Category Management
```
SLOS → Consent Management → Cookie Categories → Custom Categories
```

#### Step 2: Define New Category

**Category Configuration:**
```json
{
  "category_id": "social_media",
  "name": {
    "en": "Social Media Cookies",
    "es": "Cookies de Redes Sociales",
    "fr": "Cookies de Réseaux Sociaux"
  },
  "description": {
    "en": "Enable social media features and integrations",
    "es": "Habilitar funciones y integraciones de redes sociales",
    "fr": "Activer les fonctionnalités et intégrations des réseaux sociaux"
  },
  "required": false,
  "default_state": "denied",
  "cookies": [
    {
      "name": "facebook_pixel",
      "domain": ".facebook.com",
      "purpose": "Facebook advertising and analytics",
      "retention": "2 years"
    },
    {
      "name": "twitter_pixel",
      "domain": ".twitter.com",
      "purpose": "Twitter advertising and analytics",
      "retention": "2 years"
    }
  ]
}
```

#### Step 3: Set Category Rules

**Legal Requirements:**
- Define legal basis (consent, legitimate interest, vital interest)
- Set retention periods
- Specify data processing purposes
- Configure data sharing rules

**Technical Rules:**
- Define cookie patterns to include
- Set blocking mechanisms
- Configure loading conditions
- Set up fallback behaviors

### Configure Advanced Consent Mechanisms

#### Step 1: Granular Consent Options
```
SLOS → Consent Management → Advanced Settings → Granular Consent
```

**Multi-Level Consent:**
```javascript
const consentLevels = {
  'basic': {
    'analytics': false,
    'marketing': false,
    'social': false
  },
  'standard': {
    'analytics': true,
    'marketing': false,
    'social': false
  },
  'full': {
    'analytics': true,
    'marketing': true,
    'social': true
  }
};
```

#### Step 2: Contextual Consent
Set up consent based on user actions:

**Scroll-Based Consent:**
```javascript
// Auto-accept after scrolling
document.addEventListener('scroll', function() {
    if (window.scrollY > 100 && !window.consentGiven) {
        window.SLOS.acceptCategory('analytics');
    }
});
```

**Time-Based Consent:**
```javascript
// Auto-accept after time delay
setTimeout(function() {
    if (!window.consentGiven) {
        window.SLOS.acceptCategory('preferences');
    }
}, 30000); // 30 seconds
```

**Interaction-Based Consent:**
```javascript
// Accept on form interaction
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('focus', function() {
        window.SLOS.acceptCategory('preferences');
    }, { once: true });
});
```

### Set Up Conditional Cookie Loading

#### Step 1: Category-Based Loading
```javascript
// Conditional script loading
function loadScriptBasedOnConsent(category, scriptUrl) {
    if (window.SLOS && window.SLOS.hasConsent(category)) {
        const script = document.createElement('script');
        script.src = scriptUrl;
        document.head.appendChild(script);
    }
}

// Load Google Analytics only with consent
loadScriptBasedOnConsent('analytics', 'https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID');

// Load Facebook Pixel only with consent
loadScriptBasedOnConsent('marketing', 'https://connect.facebook.net/en_US/fbevents.js');
```

#### Step 2: Dynamic Content Blocking

**Block Marketing Content:**
```html
<!-- Blocked until consent -->
<div class="consent-blocked" data-category="marketing">
    <div class="placeholder">
        <p>Marketing content blocked. Accept marketing cookies to view.</p>
        <button onclick="window.SLOS.acceptCategory('marketing')">Accept Marketing Cookies</button>
    </div>
    <div class="content" style="display: none;">
        <!-- Facebook Like Button -->
        <div class="fb-like" data-href="https://example.com" data-width="" data-layout="button_count"></div>
    </div>
</div>
```

**Block Social Widgets:**
```html
<!-- Twitter Timeline -->
<div class="consent-blocked" data-category="social">
    <div class="placeholder">
        <p>Social media content blocked. Accept social cookies to view.</p>
    </div>
    <div class="content" style="display: none;">
        <a class="twitter-timeline" href="https://twitter.com/example">Tweets by example</a>
    </div>
</div>
```

### Configure Vendor Management

#### Step 1: Register Third-Party Vendors
```
SLOS → Consent Management → Vendors → Add Vendor
```

**Vendor Configuration:**
```json
{
  "vendor_id": "google_analytics",
  "name": "Google Analytics",
  "category": "analytics",
  "privacy_policy": "https://policies.google.com/privacy",
  "cookie_table": [
    {
      "name": "_ga",
      "domain": ".example.com",
      "purpose": "Google Analytics tracking",
      "retention": "2 years"
    },
    {
      "name": "_gid",
      "domain": ".example.com",
      "purpose": "Google Analytics session tracking",
      "retention": "24 hours"
    }
  ],
  "data_processing": {
    "purposes": ["analytics", "measurement"],
    "legal_basis": "consent",
    "data_shared": ["IP address", "user agent", "referrer"]
  }
}
```

#### Step 2: Vendor Consent Management

**IAB TCF 2.0 Integration:**
```javascript
// Check IAB consent for vendor
function hasIABConsent(vendorId) {
    if (window.__tcfapi) {
        window.__tcfapi('getTCData', 2, function(tcData, success) {
            if (success && tcData.vendor.consents[vendorId]) {
                return true;
            }
        });
    }
    return false;
}
```

### Set Up Consent Withdrawal

#### Step 1: Withdrawal Mechanisms
```
SLOS → Consent Management → Withdrawal Settings
```

**One-Click Withdrawal:**
```html
<!-- Footer withdrawal link -->
<a href="#" onclick="window.SLOS.showWithdrawalModal()">Withdraw Consent</a>
```

**Category-Specific Withdrawal:**
```javascript
// Withdraw specific category
function withdrawCategory(category) {
    window.SLOS.withdrawConsent(category);
    // Reload page or update UI
    location.reload();
}
```

#### Step 2: Withdrawal Confirmation
```javascript
// Withdrawal confirmation modal
function showWithdrawalConfirm(category) {
    const confirmed = confirm(`Are you sure you want to withdraw consent for ${category}? This may affect website functionality.`);
    if (confirmed) {
        window.SLOS.withdrawConsent(category);
        // Clear related cookies
        clearCategoryCookies(category);
    }
}
```

### Configure Advanced Cookie Policies

#### Step 1: Cookie Expiry Management
```javascript
const cookiePolicies = {
    'essential': {
        'expiry': '1 year',
        'secure': true,
        'httpOnly': true
    },
    'analytics': {
        'expiry': '2 years',
        'secure': true,
        'sameSite': 'Lax'
    },
    'marketing': {
        'expiry': '1 year',
        'secure': true,
        'sameSite': 'None'
    }
};
```

#### Step 2: Cross-Domain Consent
Handle consent across subdomains and related domains:

```javascript
// Set consent cookie for all subdomains
function setCrossDomainConsent(category, consented) {
    const domains = ['.example.com', '.blog.example.com', '.shop.example.com'];

    domains.forEach(domain => {
        document.cookie = `slos_${category}=${consented}; domain=${domain}; path=/; max-age=31536000; secure; samesite=lax`;
    });
}
```

### Implement Consent Auditing

#### Step 1: Consent Log Configuration
```
SLOS → Consent Management → Audit Settings
```

**Audit Data Collection:**
```javascript
// Log consent changes
function logConsentChange(category, action, source) {
    const logEntry = {
        timestamp: new Date().toISOString(),
        category: category,
        action: action, // 'granted', 'withdrawn', 'updated'
        source: source, // 'banner', 'portal', 'api'
        user_id: getUserId(),
        ip_address: getClientIP(),
        user_agent: navigator.userAgent
    };

    // Send to audit endpoint
    fetch('/wp-json/slos/v1/audit/consent', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(logEntry)
    });
}
```

#### Step 2: Consent History Tracking
Store complete consent history for compliance:

```php
// Consent history storage
function store_consent_history($user_id, $consent_data) {
    global $wpdb;

    $wpdb->insert(
        $wpdb->prefix . 'slos_consent_history',
        array(
            'user_id' => $user_id,
            'consent_data' => json_encode($consent_data),
            'timestamp' => current_time('mysql'),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        )
    );
}
```

### Set Up Consent Validation

#### Step 1: Consent Proof Requirements
Configure requirements for valid consent:

**GDPR Compliance:**
- Consent must be freely given
- Consent must be specific
- Consent must be informed
- Consent must be unambiguous
- Consent must be withdrawable

**Validation Rules:**
```javascript
function validateConsent(consentData) {
    const required = ['timestamp', 'categories', 'source', 'version'];

    for (const field of required) {
        if (!consentData[field]) {
            return { valid: false, error: `Missing required field: ${field}` };
        }
    }

    // Check consent age (must be recent)
    const consentAge = Date.now() - new Date(consentData.timestamp).getTime();
    if (consentAge > 365 * 24 * 60 * 60 * 1000) { // 1 year
        return { valid: false, error: 'Consent is too old' };
    }

    return { valid: true };
}
```

#### Step 2: Consent Versioning
Track consent policy versions:

```json
{
  "consent_version": "2.1",
  "policy_version": "2024.01",
  "changes": [
    "Added social media category",
    "Updated retention periods",
    "Enhanced withdrawal process"
  ],
  "effective_date": "2024-01-15"
}
```

### Test Custom Categories

#### Step 1: Category Testing Checklist

**Functionality Testing:**
- [ ] Categories appear in banner correctly
- [ ] Consent choices save properly
- [ ] Cookies load/unload based on consent
- [ ] Withdrawal works for each category
- [ ] Cross-domain consent syncs

**Compliance Testing:**
- [ ] Legal basis properly documented
- [ ] Cookie table accurate and complete
- [ ] Vendor information correct
- [ ] Audit logs capture all changes
- [ ] Consent validation passes

**User Experience Testing:**
- [ ] Banner loads quickly with custom categories
- [ ] Category descriptions clear and helpful
- [ ] Mobile experience optimized
- [ ] Accessibility features work
- [ ] Multi-language support functions

#### Step 2: Performance Testing
- Test page load times with different consent combinations
- Verify cookie blocking doesn't break functionality
- Check memory usage with multiple categories
- Test concurrent consent changes

### Monitor Category Performance

#### Step 1: Consent Analytics
Track category-specific metrics:

**Acceptance Rates:**
```javascript
// Track category acceptance
function trackCategoryAcceptance(category) {
    gtag('event', 'consent_update', {
        'category': category,
        'action': 'accepted',
        'timestamp': new Date().toISOString()
    });
}
```

**Usage Patterns:**
- Most/least accepted categories
- Consent changes over time
- Withdrawal rates by category
- Geographic variations

#### Step 2: Optimization Opportunities
- Identify underperforming categories
- A/B test category descriptions
- Optimize banner placement
- Improve consent flow

### Maintenance and Updates

#### Step 1: Regular Category Review
- Review category usage quarterly
- Update cookie lists as vendors change
- Refresh legal basis documentation
- Audit consent logs for issues

#### Step 2: Category Lifecycle Management
- Deprecate unused categories
- Archive old consent data
- Update vendor information
- Refresh cookie policies

### Troubleshooting Custom Categories

#### Common Issues

**Categories Not Appearing:**
- Check category configuration
- Verify banner template includes custom categories
- Test with different user roles
- Check for JavaScript errors

**Consent Not Saving:**
- Verify database table structure
- Check AJAX endpoints
- Test cookie settings
- Review server logs

**Cookies Not Blocking:**
- Check script loading conditions
- Verify category mapping
- Test with different browsers
- Review network requests

**Performance Issues:**
- Optimize category loading
- Reduce cookie table size
- Cache consent decisions
- Minimize DOM manipulation

#### Advanced Debugging
```javascript
// Debug consent state
console.log('Current consent state:', window.SLOS.getConsentState());

// Debug category configuration
console.log('Category config:', window.SLOS.getCategoryConfig('custom_category'));

// Debug cookie blocking
console.log('Blocked scripts:', window.SLOS.getBlockedScripts());
```

### Support Resources
- Consent management best practices
- Cookie compliance guides
- Vendor management documentation
- Performance optimization tips