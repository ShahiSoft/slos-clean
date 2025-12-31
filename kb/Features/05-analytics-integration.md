# Analytics Integration

## Overview

Shahi LegalFlowSuite integrates with major analytics platforms to track consent and accessibility events. Monitor user behavior through your analytics dashboard.

## Supported Platforms

### Google Analytics 4
- **Event Tracking** - All consent events
- **Custom Events** - slos-consent-* events
- **Conversion Tracking** - Track acceptance rates
- **Reporting** - Built-in GA reports

### Google Analytics (Universal Analytics)
- **Event Category** - slos_consent
- **Event Actions** - shown, accepted, rejected
- **Custom Dimensions** - Variant, template, region
- **Goal Tracking** - Acceptance goals

### Segment
- **Event Router** - Route to destinations
- **Traits** - User properties
- **Context** - Request context data
- **Destinations** - 300+ integrations

### Mixpanel
- **Event Tracking** - All events
- **User Profiles** - Track users
- **Cohorts** - Segment by behavior
- **Funnels** - Track conversion paths

### Custom Analytics

Listen to events directly:
```javascript
document.addEventListener('slos-consent-shown', (e) => {
    console.log('Consent shown:', e.detail);
    // Send to your analytics platform
});
```

## Analytics Events

### 1. slos-consent-shown
**Fired:** When banner displays

**Data included:**
- `timestamp` - ISO 8601 time
- `template` - Banner template ID
- `variant` - A/B test variant
- `region` - Detected region (EU, US-CA, etc.)
- `position` - Banner position (top, bottom)
- `theme` - Color theme (dark, light)
- `purposes` - Available cookie purposes
- `policyVersion` - Privacy policy version
- `bannerVersion` - Banner version

### 2. slos-consent-accepted
**Fired:** When user accepts all cookies

**Data included:**
- `timestamp` - ISO 8601 time
- `template` - Banner template used
- `variant` - A/B variant
- `categories` - Accepted categories
- `duration` - Milliseconds to decision
- `policyVersion` - Policy version accepted
- `bannerVersion` - Banner version
- `region` - User region

### 3. slos-consent-rejected
**Fired:** When user rejects non-essential cookies

**Data included:**
- `timestamp` - ISO 8601 time
- `template` - Banner template used
- `variant` - A/B variant
- `categories` - Categories rejected
- `duration` - Milliseconds to decision
- `categoriesAccepted` - Categories accepted
- `policyVersion` - Policy version

### 4. slos-consent-customized
**Fired:** When user makes granular choices

**Data included:**
- `timestamp` - ISO 8601 time
- `template` - Banner template
- `variant` - A/B variant
- `choices` - Detailed category choices
- `duration` - Time to decision
- `analyticsEnabled` - Analytics choice
- `marketingEnabled` - Marketing choice
- `preferencesEnabled` - Preferences choice

### 5. slos-consent-updated
**Fired:** When user updates previous consent

**Data included:**
- `timestamp` - ISO 8601 time
- `previousChoices` - Previous consent data
- `newChoices` - Updated choices
- `changedCategories` - What changed
- `duration` - Time to update

## Event Structure

All events follow this structure:
```json
{
  "type": "slos-consent-shown",
  "timestamp": "2025-01-15T10:30:00.000Z",
  "detail": {
    "template": "gdpr",
    "variant": "A",
    "region": "EU",
    "categories": ["essential", "analytics"],
    "duration": 3500,
    "policyVersion": "2.0",
    "bannerVersion": "4.3.0"
  }
}
```

## Setup Instructions

### Google Analytics 4

1. **Enable GA4 on site**
   - Install Google Analytics gtag.js
   - Verify tracking working

2. **Listen to events**
   ```javascript
   document.addEventListener('slos-consent-shown', (e) => {
       gtag('event', 'consent_shown', e.detail);
   });
   
   document.addEventListener('slos-consent-accepted', (e) => {
       gtag('event', 'consent_accepted', e.detail);
   });
   ```

3. **View in GA4**
   - Events → All Events
   - Filter for "consent_*"

### Segment

1. **Install Segment Analytics.js**
   - Add Segment script to site

2. **Configure event mapping**
   ```javascript
   document.addEventListener('slos-consent-accepted', (e) => {
       analytics.track('Consent Accepted', e.detail);
   });
   ```

3. **Add Segment destinations**
   - Route events to your tools

### Mixpanel

1. **Install Mixpanel**
   - Add Mixpanel tracking code

2. **Map events**
   ```javascript
   document.addEventListener('slos-consent-shown', (e) => {
       mixpanel.track('Consent Shown', e.detail);
   });
   ```

3. **View events**
   - Mixpanel → Events → Track events

## Metrics to Track

### Acceptance Rate
- (Acceptances / Impressions) × 100
- Target: 60-80%
- Track by variant for A/B testing

### Rejection Rate
- (Rejections / Impressions) × 100
- Indicates design/messaging issues
- Target: 5-15%

### Customization Rate
- (Customizations / Impressions) × 100
- Users want granular control
- Target: 10-30%

### Time to Decision
- Average milliseconds to decide
- Shorter is better
- Target: 2000-5000ms

### Conversion Funnel
1. Banner shown
2. Banner interacted
3. Choice made
4. Cookie set
5. Analytics firing

### A/B Test Results
- Compare variant A vs B
- Acceptance rate differences
- Decision time differences
- Categories selected

### Geographic Performance
- Acceptance by region
- EU vs US-CA vs BR
- Mobile vs desktop
- Browser differences

## Creating Custom Reports

### Dashboard Setup

In Google Analytics:
1. Create custom dashboard
2. Add cards for consent metrics
3. Filter by date range
4. Compare periods
5. Export reports

### Custom Dimensions

Set up custom dimensions:
- `consent_variant` - A or B
- `consent_template` - GDPR, CCPA, etc.
- `consent_region` - Geographic region
- `consent_duration` - Time to decision

### Custom Metrics

Track these metrics:
- Acceptance rate
- Rejection rate
- Customization rate
- Average decision time
- Revenue per consent variant

## Privacy Compliance

### Data Minimization
- Only track necessary events
- Anonymize user data
- No PII in events
- Track by cookie ID, not user

### GDPR Compliance
- Consent for analytics
- Don't fire GA before consent
- Anonymize IPs in GA
- Privacy-first approach

### CCPA Compliance
- Allow opt-out of analytics
- Don't sell analytics data
- Respect Do Not Track
- Privacy policy disclosure

## Performance Impact

Analytics events are:
- **Non-blocking** - Don't delay page load
- **Lightweight** - ~1KB per event
- **Batched** - Sent in groups
- **Async** - No render impact

Event sending:
- Network overhead: <100ms
- CPU overhead: <1%
- Battery impact: Negligible
- CLS impact: None

## Troubleshooting

**Events not appearing?**
- Check analytics setup
- Verify event listener registered
- Check browser console for errors
- Verify event payload structure
- Check analytics account permissions

**Wrong data captured?**
- Verify event payload
- Check timestamp format
- Verify category values
- Review variant assignment

**Performance issues?**
- Reduce event tracking
- Batch events
- Disable in test environment
- Check network latency

## Best Practices

1. **Set Up Early** - Before traffic
2. **Test Tracking** - Verify in development
3. **Monitor Results** - Weekly review
4. **A/B Test** - Compare variants
5. **Optimize Copy** - Based on metrics
6. **Privacy First** - Follow regulations
7. **Document Setup** - For team reference

## Advanced Integration

### Custom Event Handler

```php
add_action('slos_consent_accepted', function($consent_data) {
    // Send to custom analytics
    custom_analytics_track('consent_accepted', $consent_data);
});
```

### Webhook Integration

Send events to external service:
- Configure webhook URL
- Events sent in real-time
- Retry on failure
- Audit log of sends

## Next Steps

1. Enable analytics integration
2. Set up GA4 or other platform
3. Test event tracking
4. Create custom dashboard
5. Monitor performance

## Support

For detailed guides:
- [Setup Google Analytics 4](../How-tos/13-setup-ga4.md)
- [A/B Testing Guide](../How-tos/14-ab-testing.md)
- [Custom Analytics Integration](../How-tos/15-custom-analytics.md)
