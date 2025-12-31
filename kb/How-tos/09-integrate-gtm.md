# Integrate Google Tag Manager

## Set Up GTM with Consent-Aware Tag Firing

### GTM Setup Prerequisites

#### Step 1: Create GTM Account
```
Visit: https://tagmanager.google.com/
Click: "Create Account"
Account Name: "Your Website Name"
Container Name: "Web Container"
Target Platform: "Web"
```

#### Step 2: Install GTM Container
```
SLOS → Integrations → Google Tag Manager → Container Setup
```

**Add GTM Code to Theme:**
```html
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-XXXXXXX');</script>
<!-- End Google Tag Manager -->
```

**Add GTM Noscript (Body):**
```html
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
```

### Configure Consent Variables

#### Step 1: Create Consent Variables
```
GTM Dashboard → Variables → New → User-Defined Variables
```

**SLOS Consent Variable (Custom JavaScript):**
```javascript
function() {
  if (window.SLOS && window.SLOS.getConsentState) {
    return window.SLOS.getConsentState();
  }
  return {};
}
```

**Category-Specific Variables:**

**Analytics Consent:**
```javascript
function() {
  if (window.SLOS && window.SLOS.hasConsent) {
    return window.SLOS.hasConsent('analytics');
  }
  return false;
}
```

**Marketing Consent:**
```javascript
function() {
  if (window.SLOS && window.SLOS.hasConsent) {
    return window.SLOS.hasConsent('marketing');
  }
  return false;
}
```

**Preferences Consent:**
```javascript
function() {
  if (window.SLOS && window.SLOS.hasConsent) {
    return window.SLOS.hasConsent('preferences');
  }
  return false;
}
```

#### Step 2: Create Consent Triggers

**Consent Granted Trigger:**
```
GTM Dashboard → Triggers → New → Custom Event
Event Name: slos_consent_granted
This trigger fires on: Some Custom Events
```

**Consent Updated Trigger:**
```
GTM Dashboard → Triggers → New → Custom Event
Event Name: slos_consent_updated
This trigger fires on: Some Custom Events
```

**Category-Specific Triggers:**

**Analytics Consent Granted:**
```javascript
function() {
  return {{SLOS Analytics Consent}} === true;
}
```

**Marketing Consent Granted:**
```javascript
function() {
  return {{SLOS Marketing Consent}} === true;
}
```

### Set Up Consent-Aware Tags

#### Step 1: Google Analytics 4 Setup
```
GTM Dashboard → Tags → New → Google Analytics: GA4 Configuration
```

**Configuration:**
```
Tag Name: GA4 Configuration
Measurement ID: G-XXXXXXXXXX
Trigger: Consent Granted (Analytics)
```

**Advanced Settings:**
```javascript
// Only fire when analytics consent is granted
{
  "consentSettings": {
    "analytics_storage": "{{SLOS Analytics Consent}}",
    "ad_storage": "{{SLOS Marketing Consent}}"
  }
}
```

#### Step 2: Google Ads Conversion Tracking
```
GTM Dashboard → Tags → New → Google Ads Conversion Tracking
```

**Configuration:**
```
Tag Name: Google Ads Conversion
Conversion ID: XXXXXXXXX
Conversion Label: XXXXXXXX
Trigger: Marketing Consent Granted
```

#### Step 3: Facebook Pixel Setup
```
GTM Dashboard → Tags → New → Facebook Pixel
```

**Configuration:**
```
Tag Name: Facebook Pixel
Pixel ID: XXXXXXXXXXXXXXXX
Trigger: Marketing Consent Granted
```

**Custom Events:**
```javascript
// Fire only with marketing consent
if ({{SLOS Marketing Consent}}) {
  fbq('track', 'PageView');
  fbq('track', 'Lead');
}
```

### Configure Advanced Event Tracking

#### Step 1: Consent Event Tracking
```
GTM Dashboard → Tags → New → Google Analytics: GA4 Event
```

**Consent Granted Event:**
```javascript
{
  "event_name": "consent_granted",
  "parameters": {
    "consent_categories": "{{SLOS Consent State}}",
    "consent_source": "banner",
    "consent_timestamp": "{{Event Time}}"
  }
}
```

**Consent Updated Event:**
```javascript
{
  "event_name": "consent_updated",
  "parameters": {
    "updated_categories": "{{SLOS Consent State}}",
    "update_source": "portal",
    "update_timestamp": "{{Event Time}}"
  }
}
```

#### Step 2: Custom Consent Events

**Category-Specific Events:**
```javascript
// Analytics consent event
{
  "event_name": "analytics_consent",
  "parameters": {
    "consent_granted": "{{SLOS Analytics Consent}}",
    "consent_timestamp": "{{Event Time}}"
  }
}

// Marketing consent event
{
  "event_name": "marketing_consent",
  "parameters": {
    "consent_granted": "{{SLOS Marketing Consent}}",
    "consent_timestamp": "{{Event Time}}"
  }
}
```

### Implement Consent Blocking

#### Step 1: Tag Sequencing
```
GTM Dashboard → Tags → [Your Tag] → Advanced Settings → Tag Sequencing
```

**Setup Tag:**
```
Fire a tag before [Your Tag] fires: Consent Check Tag
```

**Consent Check Tag:**
```javascript
function() {
  var consent = {{SLOS Consent State}};
  if (consent.analytics && consent.marketing) {
    return true;
  }
  return false;
}
```

#### Step 2: Blocking Triggers

**Block Marketing Tags:**
```javascript
// Block trigger for marketing tags
function() {
  return !{{SLOS Marketing Consent}};
}
```

**Block Analytics Tags:**
```javascript
// Block trigger for analytics tags
function() {
  return !{{SLOS Analytics Consent}};
}
```

### Set Up Data Layer Integration

#### Step 1: Push Consent Events to Data Layer
```javascript
// SLOS consent event listener
document.addEventListener('slos_consent_granted', function(event) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        'event': 'slos_consent_granted',
        'consent_categories': event.detail.categories,
        'consent_timestamp': event.detail.timestamp
    });
});

document.addEventListener('slos_consent_updated', function(event) {
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
        'event': 'slos_consent_updated',
        'updated_categories': event.detail.categories,
        'update_timestamp': event.detail.timestamp
    });
});
```

#### Step 2: Data Layer Variables

**Consent Categories Variable:**
```javascript
function() {
  return {{Data Layer Variable - consent_categories}} || [];
}
```

**Consent Timestamp Variable:**
```javascript
function() {
  return {{Data Layer Variable - consent_timestamp}} || null;
}
```

### Configure Cross-Domain Consent

#### Step 1: Cross-Domain Tracking
```
GTM Dashboard → Variables → New → Google Analytics Settings
```

**Cross-Domain Configuration:**
```javascript
{
  "allowLinker": true,
  "linkerDomains": ["example.com", "blog.example.com", "shop.example.com"],
  "consentSettings": {
    "analytics_storage": "{{SLOS Analytics Consent}}",
    "ad_storage": "{{SLOS Marketing Consent}}"
  }
}
```

#### Step 2: Consent Synchronization
```javascript
// Sync consent across domains
function syncConsentAcrossDomains() {
    var consent = {{SLOS Consent State}};
    var domains = ['example.com', 'blog.example.com', 'shop.example.com'];

    domains.forEach(function(domain) {
        document.cookie = 'slos_consent=' + JSON.stringify(consent) +
                         '; domain=' + domain + '; path=/; max-age=31536000';
    });
}
```

### Set Up Enhanced Ecommerce

#### Step 1: Ecommerce Consent Configuration
```
GTM Dashboard → Tags → New → Google Analytics: GA4 Event
```

**Purchase Event (Consent-Aware):**
```javascript
{
  "event_name": "purchase",
  "parameters": {
    "currency": "USD",
    "value": "{{Purchase Value}}",
    "items": {{Purchase Items}}
  },
  "consent_required": ["analytics", "marketing"]
}
```

#### Step 2: Ecommerce Triggers
```javascript
// Only fire ecommerce events with proper consent
function() {
  var consent = {{SLOS Consent State}};
  var requiredConsent = {{Tag Consent Requirements}};

  return requiredConsent.every(function(category) {
    return consent[category] === true;
  });
}
```

### Implement Server-Side Tagging

#### Step 1: Set Up Server Container
```
GTM Dashboard → Admin → Container → New → Server
```

#### Step 2: Configure Server-Side Tags

**Server-Side GA4:**
```javascript
// Server-side tag configuration
{
  "consentSettings": {
    "analytics_storage": "{{Client Consent - Analytics}}",
    "ad_storage": "{{Client Consent - Marketing}}"
  }
}
```

#### Step 3: Client-Side Integration
```javascript
// Send consent data to server
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
  'event': 'client_consent',
  'consent': {{SLOS Consent State}}
});
```

### Test GTM Integration

#### Step 1: GTM Debug Mode
```
GTM Dashboard → Preview
Enter your website URL
Click: "Connect"
```

**Debug Checklist:**
- [ ] GTM container loads correctly
- [ ] Consent variables populate
- [ ] Tags fire based on consent
- [ ] Events track properly
- [ ] Cross-domain works
- [ ] Server-side tags function

#### Step 2: Consent Testing

**Test Analytics Consent:**
- Accept only analytics cookies
- Check GA4 events fire
- Verify marketing tags blocked

**Test Marketing Consent:**
- Accept only marketing cookies
- Check ad pixels fire
- Verify analytics tags blocked

**Test Full Consent:**
- Accept all categories
- Verify all tags fire
- Check event parameters

#### Step 3: Real-Time Monitoring
```
Google Analytics → Real-Time → Events
Google Tag Manager → Debug Console
```

### Monitor GTM Performance

#### Step 1: Tag Performance
```
GTM Dashboard → Admin → Container → Settings → Performance
```

**Performance Metrics:**
- Tag load times
- Consent check delays
- Event processing times
- Error rates

#### Step 2: Consent Analytics

**Consent Event Tracking:**
```javascript
// Track consent decisions
gtag('event', 'consent_decision', {
  'consent_type': 'analytics',
  'consent_granted': true,
  'page_location': window.location.href
});
```

**Tag Firing Analysis:**
- Tags fired per session
- Consent blocking effectiveness
- Performance impact
- Error tracking

### Advanced GTM Features

#### Step 1: Custom Templates
```
GTM Dashboard → Templates → New
```

**Consent-Aware Template:**
```javascript
// Custom tag template with consent checking
const hasConsent = data.consent_category ?
  require('getCookieValues')('slos_' + data.consent_category)[0] === 'true' :
  true;

if (hasConsent) {
  // Fire tag logic here
}
```

#### Step 2: Built-in Consent Integration
```javascript
// Use GTM's built-in consent
gtag('consent', 'default', {
  'analytics_storage': {{SLOS Analytics Consent}} ? 'granted' : 'denied',
  'ad_storage': {{SLOS Marketing Consent}} ? 'granted' : 'denied',
  'functionality_storage': {{SLOS Preferences Consent}} ? 'granted' : 'denied',
  'personalization_storage': {{SLOS Preferences Consent}} ? 'granted' : 'denied',
  'security_storage': 'granted'
});
```

### Troubleshooting GTM Issues

#### Common Problems

**Tags Not Firing:**
- Check consent variables
- Verify trigger conditions
- Test in debug mode
- Check for JavaScript errors

**Consent Not Syncing:**
- Verify data layer pushes
- Check variable configurations
- Test cross-domain settings
- Review cookie policies

**Performance Issues:**
- Optimize tag loading
- Reduce unnecessary triggers
- Cache consent decisions
- Minimize custom scripts

**Debugging Steps:**
```javascript
// Debug GTM data layer
console.log('Data Layer:', window.dataLayer);

// Debug consent state
console.log('SLOS Consent:', window.SLOS.getConsentState());

// Debug GTM variables
console.log('GTM Variables:', google_tag_manager[{{Container ID}}].dataLayer.get());
```

### Maintenance and Updates

#### Step 1: Regular GTM Audits
- Review tag configurations quarterly
- Update consent mappings
- Audit tag performance
- Clean up unused tags

#### Step 2: Version Control
- Use GTM workspaces for changes
- Test in staging environment
- Document tag changes
- Backup container versions

### Support Resources
- Google Tag Manager Help Center
- Google Analytics for Developers
- Consent management guides
- Performance optimization tips