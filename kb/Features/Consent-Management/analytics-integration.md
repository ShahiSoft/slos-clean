# Analytics Integration

## Overview

Analytics Integration connects consent management with popular analytics platforms, enabling privacy-compliant tracking of user consent behavior and website interactions.

## Supported Platforms

### Google Analytics 4 (GA4)

**Integration Features:**
- Consent event tracking
- Privacy-compliant measurement
- Consent mode integration
- Custom dimension tracking

**Events Tracked:**
- Consent banner displayed
- Consent granted/denied
- Consent categories selected
- Consent withdrawal
- Time to consent decision

**Custom Dimensions:**
- Consent status
- Consent categories
- Geo region
- Template variant
- A/B test group

### Segment

**Integration Features:**
- Event streaming to Segment
- User trait tracking
- Consent state management
- Destination filtering

**Events Sent:**
- Consent Updated
- Banner Interaction
- Preference Changed
- Withdrawal Completed

**User Traits:**
- Consent status
- Consent timestamp
- Consent categories
- Geo location
- Device information

### Mixpanel

**Integration Features:**
- User profile updates
- Event tracking with properties
- Consent funnel analysis
- A/B test tracking

**Events Tracked:**
- Banner Shown
- Consent Given
- Consent Withdrawn
- Category Selected
- Time Metrics

**User Properties:**
- Consent level
- Consent date
- Region
- Device type
- Browser info

## Event Types

### Consent Events

**Banner Events:**
- `consent_banner_displayed`
- `consent_banner_interaction`
- `consent_banner_dismissed`

**Decision Events:**
- `consent_granted`
- `consent_denied`
- `consent_customized`
- `consent_withdrawn`

**Category Events:**
- `consent_category_accepted`
- `consent_category_rejected`
- `consent_category_updated`

### Performance Events

**Timing Events:**
- `consent_time_to_decision`
- `consent_interaction_duration`
- `banner_load_time`

**Engagement Events:**
- `consent_page_views`
- `consent_scroll_depth`
- `consent_exit_intent`

## Configuration

### Platform Setup

**Google Analytics 4:**
1. Enable GA4 integration in SLOS
2. Enter GA4 Measurement ID
3. Configure consent mode
4. Set up custom dimensions
5. Test event tracking

**Segment:**
1. Enable Segment integration
2. Enter Segment write key
3. Configure event mapping
4. Set up user traits
5. Test data flow

**Mixpanel:**
1. Enable Mixpanel integration
2. Enter Mixpanel project token
3. Configure event properties
4. Set up user profiles
5. Test tracking

### Event Mapping

**Custom Event Names:**
- Rename default events
- Map to existing events
- Create event hierarchies
- Add event prefixes

**Property Configuration:**
- Include/exclude properties
- Rename property keys
- Transform property values
- Add custom properties

## Privacy Compliance

### Consent Mode Integration

**Google Consent Mode:**
- Respects user consent choices
- Modifies tracking behavior
- Maintains privacy compliance
- Supports regional requirements

**Consent States:**
- Granted: Full tracking
- Denied: Limited tracking
- Partial: Category-based tracking

### Data Minimization

**Property Filtering:**
- Remove personal identifiers
- Anonymize IP addresses
- Limit data retention
- Aggregate sensitive data

**Regional Compliance:**
- GDPR data processing
- CCPA opt-out handling
- LGPD consent requirements
- PIPEDA compliance

## Advanced Features

### A/B Testing Integration

**Variant Tracking:**
- Track template performance
- Measure consent rates
- Analyze user behavior
- Optimize banner design

**Test Configuration:**
- Define test variants
- Set traffic distribution
- Configure goals
- Monitor results

### Custom Events

**Hook-Based Events:**
- Custom event triggers
- Dynamic property injection
- Conditional event firing
- Event filtering

**JavaScript API:**
```javascript
// Fire custom consent event
SLOS.consent.trackEvent('custom_interaction', {
  action: 'button_click',
  element: 'privacy_policy_link',
  timestamp: Date.now()
});
```

## Reporting & Analytics

### Consent Analytics Dashboard

**Key Metrics:**
- Consent acceptance rate
- Average time to consent
- Withdrawal rate by category
- Regional consent patterns
- Template performance

**Conversion Tracking:**
- Consent impact on conversions
- Bounce rate changes
- Session duration effects
- Goal completion rates

### Custom Reports

**Event Analysis:**
- Event frequency analysis
- User journey mapping
- Funnel visualization
- Cohort analysis

**Performance Reports:**
- Load time impact
- Event delivery success
- Error rate monitoring
- Platform-specific metrics

## API Integration

### REST API Endpoints

**Analytics Configuration:**
```
GET /wp-json/slos/v1/analytics/config
POST /wp-json/slos/v1/analytics/config
PUT /wp-json/slos/v1/analytics/platforms/{platform}
```

**Event Management:**
```
GET /wp-json/slos/v1/analytics/events
POST /wp-json/slos/v1/analytics/events/custom
GET /wp-json/slos/v1/analytics/events/{event_id}
```

### Webhook Support

**Real-time Events:**
- Consent events
- Platform connection status
- Error notifications
- Performance alerts

**Integration Examples:**
- Slack notifications
- Email alerts
- External dashboards
- Compliance monitoring

## Troubleshooting

### Event Not Tracking

**Common Issues:**
- Platform credentials incorrect
- JavaScript loading issues
- Consent mode conflicts
- Network connectivity

**Solutions:**
- Verify API keys
- Check browser console
- Test platform dashboards
- Review network requests

### Data Not Appearing

**Platform-Specific Issues:**
- GA4: Check measurement ID
- Segment: Verify write key
- Mixpanel: Confirm project token

**Timing Issues:**
- Event queuing delays
- Batch processing
- Platform processing time
- Cache clearing

### Performance Impact

**Optimization:**
- Event batching
- Asynchronous sending
- Error handling
- Resource monitoring

**Monitoring:**
- Event success rates
- Response times
- Error frequencies
- Platform limits

## Best Practices

### Implementation

1. **Test Thoroughly:** Verify all platforms
2. **Monitor Performance:** Track impact on site speed
3. **Privacy First:** Respect user consent choices
4. **Regular Audits:** Review tracking implementation

### Compliance

1. **Transparent Tracking:** Clear privacy notices
2. **User Control:** Easy opt-out mechanisms
3. **Data Minimization:** Only necessary data
4. **Legal Compliance:** Regional law adherence

### Performance

1. **Efficient Tracking:** Minimize payload size
2. **Error Handling:** Graceful failure modes
3. **Resource Management:** Background processing
4. **Monitoring:** Performance tracking

## Security Considerations

### Data Protection

**Secure Transmission:**
- HTTPS-only communication
- API key encryption
- Secure credential storage
- Input validation

**Access Controls:**
- Platform permission management
- User data isolation
- Audit trail logging
- Suspicious activity detection

### Privacy Safeguards

**Data Handling:**
- No personal data in analytics
- Anonymization techniques
- Retention policies
- Deletion capabilities

**Compliance Monitoring:**
- Regular privacy audits
- Data processing records
- User consent verification
- Regulatory reporting

## Custom Integration

### Third-Party Platforms

**Supported Platforms:**
- Adobe Analytics
- Matomo
- Plausible
- Custom analytics

**Integration Process:**
1. Platform evaluation
2. API documentation review
3. Custom code development
4. Testing and validation

### Custom Event Development

**Event Creation:**
```php
// Hook into consent events
add_action('slos_consent_granted', 'custom_analytics_event', 10, 2);
function custom_analytics_event($consent_data, $user_id) {
    // Custom analytics logic
    custom_analytics_track('consent_granted', $consent_data);
}
```

**Property Enhancement:**
```php
// Add custom properties to events
add_filter('slos_analytics_event_properties', 'add_custom_properties', 10, 2);
function add_custom_properties($properties, $event_name) {
    $properties['custom_field'] = get_custom_value();
    return $properties;
}
```

## Related Documentation

- [Consent Management Overview](overview.md)
- [Cookie Banner Templates](cookie-banner-templates.md)
- [Consent Tracking](consent-tracking.md)
- [Regional Compliance](regional-compliance.md)
- [Configuration Guide](configuration.md)