# Integration Problems

## Fix Issues with Third-Party Services and Plugins

### Google Analytics Integration Issues

#### Step 1: Verify GA4 Setup
```
SLOS → Integrations → Google Analytics → Status Check
```

**GA4 Configuration Check:**
```json
{
  "measurement_id": "G-XXXXXXXXXX",
  "status": "connected",
  "last_sync": "2025-12-31T10:30:00Z",
  "consent_events_tracked": 1250,
  "issues_detected": [
    {
      "type": "consent_mapping",
      "severity": "warning",
      "message": "Analytics consent not properly mapped to GA consent settings"
    }
  ]
}
```

#### Step 2: Fix Consent Mapping
```
SLOS → Integrations → Google Analytics → Consent Settings
```

**GA Consent Configuration:**
```javascript
// Correct GA consent mapping
gtag('consent', 'default', {
  'analytics_storage': '{{SLOS Analytics Consent}}' === 'true' ? 'granted' : 'denied',
  'ad_storage': '{{SLOS Marketing Consent}}' === 'true' ? 'granted' : 'denied',
  'functionality_storage': '{{SLOS Preferences Consent}}' === 'true' ? 'granted' : 'denied',
  'personalization_storage': '{{SLOS Marketing Consent}}' === 'true' ? 'granted' : 'denied',
  'security_storage': 'granted'
});
```

#### Step 3: Test GA Events
```
SLOS → Integrations → Google Analytics → Test Events
```

**Event Testing:**
- [x] Page views tracking
- [x] Consent events firing
- [x] Custom events working
- [x] E-commerce tracking (if enabled)

### Facebook Pixel Integration Issues

#### Step 1: Check Pixel Configuration
```
SLOS → Integrations → Facebook Pixel → Status Check
```

**Pixel Status:**
```json
{
  "pixel_id": "XXXXXXXXXXXXXXXXX",
  "status": "connected",
  "last_event": "2025-12-31T10:25:00Z",
  "events_sent_today": 89,
  "consent_issues": [
    {
      "type": "pixel_loading",
      "severity": "error",
      "message": "Facebook pixel loading without marketing consent"
    }
  ]
}
```

#### Step 2: Fix Consent-Gated Loading
```javascript
// Correct Facebook pixel loading
function loadFacebookPixel() {
  if (window.SLOS && window.SLOS.hasConsent('marketing')) {
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{FACEBOOK_PIXEL_ID}}');
    fbq('track', 'PageView');
  }
}

// Load pixel after consent
document.addEventListener('slos_consent_updated', function(event) {
  if (event.detail.categories.includes('marketing')) {
    loadFacebookPixel();
  }
});
```

#### Step 3: Test Pixel Events
```
SLOS → Integrations → Facebook Pixel → Test Events
```

**Pixel Testing Checklist:**
- [x] Pixel fires only with consent
- [x] Standard events tracked
- [x] Custom events working
- [x] Conversion tracking active

### WordPress Plugin Conflicts

#### Step 1: Detect Plugin Conflicts
```
SLOS → Troubleshooting → Plugin Conflicts → Scan
```

**Conflict Detection Results:**
```json
{
  "conflicting_plugins": [
    {
      "plugin": "wp-rocket/wp-rocket.php",
      "conflict_type": "javascript_loading",
      "severity": "high",
      "description": "Caching plugin interfering with consent banner loading"
    },
    {
      "plugin": "wordpress-seo/wp-seo.php",
      "conflict_type": "cookie_setting",
      "severity": "medium",
      "description": "SEO plugin setting cookies before consent"
    }
  ],
  "recommended_actions": [
    "Configure caching plugin to exclude SLOS scripts",
    "Disable SEO plugin cookie setting until consent given"
  ]
}
```

#### Step 2: Resolve JavaScript Conflicts
```
SLOS → Troubleshooting → Plugin Conflicts → JavaScript Fixes
```

**Common Fixes:**
```javascript
// Fix jQuery conflicts
jQuery.noConflict();
(function($) {
  // SLOS jQuery code here
})(jQuery);

// Fix script loading order
wp_enqueue_script('slos-consent', plugin_dir_url(__FILE__) . 'js/consent.js', array('jquery'), '1.0', true);

// Exclude from caching
// Add to WP Rocket exclude list:
// /wp-content/plugins/shahi-legalflowsuite/.*\.js
// /wp-content/plugins/shahi-legalflowsuite/.*\.css
```

#### Step 3: Test Plugin Compatibility
```
SLOS → Troubleshooting → Plugin Conflicts → Compatibility Test
```

**Compatibility Testing:**
- Banner displays correctly
- Consent saves properly
- No JavaScript errors
- Performance not degraded
- Other plugins still functional

### API Integration Issues

#### Step 1: Check API Connectivity
```
SLOS → Integrations → API → Connection Test
```

**API Status Check:**
```json
{
  "endpoints_tested": [
    {
      "endpoint": "/wp-json/slos/v1/consent",
      "status": "success",
      "response_time_ms": 45
    },
    {
      "endpoint": "/wp-json/slos/v1/dsr",
      "status": "error",
      "error": "401 Unauthorized",
      "solution": "Check API authentication"
    }
  ],
  "overall_status": "partial_failure",
  "recommendations": [
    "Verify API keys",
    "Check endpoint permissions",
    "Review authentication settings"
  ]
}
```

#### Step 2: Fix API Authentication
```
SLOS → Integrations → API → Authentication Settings
```

**API Authentication:**
```json
{
  "auth_method": "bearer_token",
  "token_endpoint": "/wp-json/jwt-auth/v1/token",
  "validate_token": true,
  "token_expiry": 3600,
  "rate_limiting": {
    "requests_per_minute": 60,
    "burst_limit": 10
  }
}
```

#### Step 3: Test API Endpoints
```bash
# Test API endpoints
curl -X GET "https://yourwebsite.com/wp-json/slos/v1/consent/status" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"

curl -X POST "https://yourwebsite.com/wp-json/slos/v1/dsr/request" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"type":"access","user_email":"test@example.com"}'
```

### Webhook Integration Problems

#### Step 1: Check Webhook Configuration
```
SLOS → Integrations → Webhooks → Status Check
```

**Webhook Status:**
```json
{
  "webhooks_configured": [
    {
      "name": "consent_events",
      "url": "https://api.yourcompany.com/webhooks/consent",
      "status": "failing",
      "last_error": "Connection timeout",
      "retry_count": 3
    },
    {
      "name": "dsr_events",
      "url": "https://api.yourcompany.com/webhooks/dsr",
      "status": "success",
      "last_success": "2025-12-31T10:20:00Z"
    }
  ],
  "delivery_rate": "75%",
  "average_response_time": "850ms"
}
```

#### Step 2: Fix Webhook Delivery
```
SLOS → Integrations → Webhooks → Retry Failed Deliveries
```

**Webhook Retry Configuration:**
```json
{
  "retry_attempts": 5,
  "retry_delay_seconds": 60,
  "exponential_backoff": true,
  "max_retry_delay": 3600,
  "failure_notification": {
    "enabled": true,
    "email": "webmaster@yourcompany.com",
    "threshold": 10
  }
}
```

#### Step 3: Test Webhook Payloads
```javascript
// Test webhook payload
const testPayload = {
  event: 'consent_granted',
  timestamp: new Date().toISOString(),
  user_id: 'test_user_123',
  categories: ['essential', 'analytics'],
  source: 'banner'
};

fetch('https://api.yourcompany.com/webhooks/consent', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-Webhook-Signature': generateSignature(testPayload)
  },
  body: JSON.stringify(testPayload)
});
```

### CDN and Caching Issues

#### Step 1: Check CDN Configuration
```
SLOS → Troubleshooting → CDN Issues → Scan
```

**CDN Compatibility Check:**
```json
{
  "cdn_detected": "Cloudflare",
  "issues_found": [
    {
      "type": "cookie_caching",
      "severity": "high",
      "description": "CDN caching consent cookies incorrectly"
    },
    {
      "type": "script_loading",
      "severity": "medium",
      "description": "CDN minifying SLOS scripts causing errors"
    }
  ],
  "recommended_fixes": [
    "Exclude consent cookies from CDN caching",
    "Disable script minification for SLOS files",
    "Configure proper cache headers"
  ]
}
```

#### Step 2: Fix CDN Cookie Issues
```
SLOS → Troubleshooting → CDN Issues → Cookie Configuration
```

**CDN Cookie Settings:**
```apache
# Apache .htaccess for Cloudflare
<IfModule mod_headers.c>
  # Don't cache consent cookies
  Header always set Cache-Control "no-cache, no-store, must-revalidate" env=SLOS_CONSENT
  Header always set Pragma "no-cache" env=SLOS_CONSENT
  Header always set Expires "0" env=SLOS_CONSENT
</IfModule>

# Set environment variable for consent requests
SetEnvIf Cookie "slos_consent" SLOS_CONSENT
```

#### Step 3: Configure Cache Exclusions
```nginx
# Nginx configuration for CDN
location ~* \.(js|css)$ {
  # Exclude SLOS files from caching
  location ~* /wp-content/plugins/shahi-legalflowsuite/ {
    add_header Cache-Control "no-cache, no-store, must-revalidate";
    add_header Pragma "no-cache";
    expires 0;
  }
}
```

### Theme Integration Problems

#### Step 1: Check Theme Compatibility
```
SLOS → Troubleshooting → Theme Issues → Compatibility Check
```

**Theme Analysis:**
```json
{
  "theme_name": "Avada",
  "compatibility_score": 85,
  "issues_detected": [
    {
      "type": "css_conflict",
      "severity": "low",
      "description": "Theme styles slightly affecting banner appearance"
    },
    {
      "type": "javascript_conflict",
      "severity": "medium",
      "description": "Theme scripts interfering with consent functionality"
    }
  ],
  "recommended_actions": [
    "Add theme-specific CSS overrides",
    "Adjust script loading order",
    "Test with theme's optimization features disabled"
  ]
}
```

#### Step 2: Add Theme-Specific Fixes
```
SLOS → Troubleshooting → Theme Issues → Custom Fixes
```

**Theme-Specific CSS:**
```css
/* Avada theme fixes */
.fusion-body .slos-consent-banner {
  z-index: 10001; /* Above Avada elements */
}

.fusion-body .slos-consent-banner .banner-content {
  max-width: 1200px; /* Match theme container */
}

/* Divi theme fixes */
.et_pb_section .slos-consent-banner {
  position: fixed !important;
}

.et_pb_section .slos-consent-banner .banner-buttons {
  margin-top: 1rem;
}
```

#### Step 3: Test Theme Integration
```
SLOS → Troubleshooting → Theme Issues → Integration Test
```

**Integration Testing:**
- Banner displays correctly
- Theme styles don't conflict
- Responsive design works
- Theme builder compatibility
- Performance not affected

### Multi-Site Network Issues

#### Step 1: Check Network Configuration
```
SLOS → Troubleshooting → Multi-Site → Network Check
```

**Multi-Site Analysis:**
```json
{
  "network_activated": true,
  "sites_count": 5,
  "shared_tables": true,
  "issues_detected": [
    {
      "type": "table_sharing",
      "severity": "high",
      "description": "Consent logs not properly separated by site"
    },
    {
      "type": "settings_sync",
      "severity": "medium",
      "description": "Settings not syncing across network sites"
    }
  ]
}
```

#### Step 2: Fix Multi-Site Table Issues
```
SLOS → Troubleshooting → Multi-Site → Table Configuration
```

**Multi-Site Table Setup:**
```php
// Ensure proper table prefixes for multi-site
global $wpdb;
if (is_multisite()) {
    $wpdb->slos_consent_log = $wpdb->base_prefix . 'slos_consent_log';
    $wpdb->slos_dsr_requests = $wpdb->base_prefix . 'slos_dsr_requests';
} else {
    $wpdb->slos_consent_log = $wpdb->prefix . 'slos_consent_log';
    $wpdb->slos_dsr_requests = $wpdb->prefix . 'slos_dsr_requests';
}
```

#### Step 3: Configure Network Settings
```
SLOS → Troubleshooting → Multi-Site → Network Settings
```

**Network Configuration:**
```json
{
  "network_mode": "coordinated",
  "shared_settings": [
    "cookie_categories",
    "legal_documents",
    "compliance_rules"
  ],
  "site_specific_settings": [
    "banner_appearance",
    "portal_customization",
    "notification_recipients"
  ],
  "data_isolation": "table_prefix",
  "sync_frequency": "hourly"
}
```

### Monitoring Integration Health

#### Step 1: Set Up Integration Monitoring
```
SLOS → Integrations → Monitoring → Configure
```

**Integration Health Checks:**
```json
{
  "google_analytics": {
    "check_frequency": "5_minutes",
    "alert_on_failure": true,
    "failure_threshold": 3
  },
  "facebook_pixel": {
    "check_frequency": "10_minutes",
    "alert_on_failure": true,
    "failure_threshold": 2
  },
  "api_endpoints": {
    "check_frequency": "1_minute",
    "alert_on_failure": true,
    "failure_threshold": 5
  },
  "webhooks": {
    "check_frequency": "15_minutes",
    "alert_on_failure": true,
    "failure_threshold": 2
  }
}
```

#### Step 2: Integration Dashboard
```
SLOS → Dashboard → Integrations → Health Monitor
```

**Health Dashboard Widgets:**
- Service status indicators
- Response time graphs
- Error rate charts
- Data flow diagrams
- Alert history

### Support Resources

#### Documentation
- [Integration Setup](../Advanced/04-custom-integrations.md)
- [API Documentation](../Advanced/01-rest-api.md)
- [Webhook Configuration](../Advanced/04-custom-integrations.md)

#### Help
- Integration troubleshooting FAQ
- Third-party service guides
- Plugin compatibility database
- Technical support forum