# Event Types & Structure

## Event Categories

### Consent Events

#### Banner Interaction Events

##### Banner Display Event
```json
{
  "event": "consent_banner_display",
  "properties": {
    "banner_type": "initial|revisit|customization",
    "trigger_reason": "page_load|scroll|exit_intent|manual",
    "geo_location": "US|EU|BR",
    "language": "en|es|fr",
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

##### Banner Accept Event
```json
{
  "event": "consent_banner_accept",
  "properties": {
    "consent_categories": ["essential", "analytics", "marketing"],
    "accept_method": "accept_all|customize_accept",
    "interaction_time": 15,
    "previous_consent": false,
    "geo_location": "EU",
    "timestamp": "2024-01-15T10:30:15Z"
  }
}
```

##### Banner Reject Event
```json
{
  "event": "consent_banner_reject",
  "properties": {
    "reject_method": "reject_all|customize_reject",
    "interaction_time": 8,
    "rejected_categories": ["analytics", "marketing"],
    "geo_location": "US",
    "timestamp": "2024-01-15T10:30:08Z"
  }
}
```

##### Banner Customize Event
```json
{
  "event": "consent_banner_customize",
  "properties": {
    "customization_time": 45,
    "categories_viewed": ["essential", "analytics", "marketing", "preferences"],
    "final_decision": "partial_accept",
    "geo_location": "BR",
    "timestamp": "2024-01-15T10:30:45Z"
  }
}
```

#### Cookie Category Events

##### Category-Specific Consent
```json
{
  "event": "consent_category_update",
  "properties": {
    "category": "analytics",
    "action": "granted|denied|withdrawn",
    "method": "banner|preference_center|api",
    "previous_status": "denied",
    "geo_location": "EU",
    "timestamp": "2024-01-15T10:31:00Z"
  }
}
```

##### Bulk Category Updates
```json
{
  "event": "consent_bulk_update",
  "properties": {
    "categories_updated": {
      "analytics": "granted",
      "marketing": "denied",
      "preferences": "granted"
    },
    "update_method": "preference_center",
    "update_reason": "user_review",
    "geo_location": "US",
    "timestamp": "2024-01-15T10:32:00Z"
  }
}
```

### Privacy Events

#### Policy Interaction Events

##### Privacy Policy View
```json
{
  "event": "privacy_policy_view",
  "properties": {
    "policy_version": "2.1",
    "view_source": "banner_link|footer_link|direct_url",
    "time_on_page": 180,
    "scroll_depth": 85,
    "sections_viewed": ["data_collection", "user_rights", "contact"],
    "geo_location": "EU",
    "timestamp": "2024-01-15T10:35:00Z"
  }
}
```

##### Cookie Policy Interaction
```json
{
  "event": "cookie_policy_interaction",
  "properties": {
    "policy_version": "1.3",
    "interaction_type": "view|download|print",
    "categories_explored": ["essential", "analytics", "marketing"],
    "questions_asked": ["what_are_cookies", "how_to_manage"],
    "geo_location": "BR",
    "timestamp": "2024-01-15T10:36:00Z"
  }
}
```

#### DSR Portal Events

##### Portal Access Event
```json
{
  "event": "dsr_portal_access",
  "properties": {
    "access_method": "direct_url|banner_link|email_link",
    "user_type": "new|returning",
    "session_duration": 420,
    "pages_viewed": ["request_form", "status_check", "help"],
    "geo_location": "US",
    "timestamp": "2024-01-15T10:40:00Z"
  }
}
```

##### Request Submission Event
```json
{
  "event": "dsr_request_submitted",
  "properties": {
    "request_type": "access|rectification|erasure|portability",
    "identity_method": "email|sms|document",
    "request_complexity": "simple|complex",
    "estimated_completion": "24h|72h|30d",
    "geo_location": "EU",
    "timestamp": "2024-01-15T10:45:00Z"
  }
}
```

### Compliance Events

#### Accessibility Events

##### Accessibility Scan Event
```json
{
  "event": "accessibility_scan_completed",
  "properties": {
    "scan_type": "automated|manual",
    "wcag_level": "AA",
    "issues_found": 12,
    "issues_fixed": 8,
    "scan_duration": 45,
    "page_url": "/products",
    "timestamp": "2024-01-15T11:00:00Z"
  }
}
```

##### Accessibility Improvement Event
```json
{
  "event": "accessibility_improvement_applied",
  "properties": {
    "improvement_type": "alt_text|color_contrast|keyboard_navigation",
    "auto_applied": true,
    "elements_affected": 15,
    "wcag_guideline": "1.1.1",
    "page_url": "/contact",
    "timestamp": "2024-01-15T11:05:00Z"
  }
}
```

#### Legal Document Events

##### Document Generation Event
```json
{
  "event": "legal_document_generated",
  "properties": {
    "document_type": "privacy_policy|cookie_policy|terms",
    "generation_method": "auto|manual",
    "customizations": ["company_info", "geo_clauses"],
    "export_format": "pdf|html",
    "processing_time": 3.2,
    "timestamp": "2024-01-15T11:10:00Z"
  }
}
```

##### Document Publishing Event
```json
{
  "event": "legal_document_published",
  "properties": {
    "document_type": "privacy_policy",
    "publish_method": "wordpress_page|direct_link",
    "previous_version": "2.0",
    "new_version": "2.1",
    "changes_summary": "Updated GDPR clauses",
    "timestamp": "2024-01-15T11:15:00Z"
  }
}
```

### User Journey Events

#### Page Interaction Events

##### Enhanced Page View
```json
{
  "event": "enhanced_page_view",
  "properties": {
    "page_url": "/privacy-policy",
    "page_title": "Privacy Policy",
    "time_on_page": 245,
    "scroll_depth": 92,
    "consent_status": "full_consent",
    "geo_location": "EU",
    "device_type": "desktop",
    "timestamp": "2024-01-15T11:20:00Z"
  }
}
```

##### Content Engagement Event
```json
{
  "event": "content_engagement",
  "properties": {
    "content_type": "privacy_policy|cookie_banner|dsr_form",
    "engagement_type": "read|interact|convert",
    "engagement_depth": 78,
    "time_spent": 180,
    "consent_impact": true,
    "geo_location": "US",
    "timestamp": "2024-01-15T11:25:00Z"
  }
}
```

#### Conversion Events

##### Privacy-Aware Conversion
```json
{
  "event": "privacy_conversion",
  "properties": {
    "conversion_type": "form_submit|purchase|signup",
    "conversion_value": 49.99,
    "consent_status": "full_consent",
    "privacy_tools_used": ["consent_banner", "privacy_policy"],
    "attribution_window": "30d",
    "geo_location": "BR",
    "timestamp": "2024-01-15T11:30:00Z"
  }
}
```

##### Goal Completion Event
```json
{
  "event": "privacy_goal_completed",
  "properties": {
    "goal_name": "privacy_policy_read",
    "goal_category": "engagement",
    "goal_value": 1,
    "consent_required": true,
    "user_segment": "gdpr_compliant",
    "geo_location": "EU",
    "timestamp": "2024-01-15T11:35:00Z"
  }
}
```

## Event Structure Standards

### Common Event Properties

#### Required Properties
```json
{
  "event_id": "unique_event_identifier",
  "timestamp": "ISO_8601_timestamp",
  "user_id": "anonymized_user_identifier",
  "session_id": "session_identifier",
  "geo_location": "country_code",
  "consent_status": "consent_state"
}
```

#### Optional Properties
```json
{
  "user_agent": "browser_user_agent",
  "ip_address": "anonymized_ip",
  "referrer": "referring_url",
  "campaign": "utm_campaign",
  "device_type": "mobile|desktop|tablet",
  "browser_language": "language_code"
}
```

### Event Naming Conventions

#### Naming Rules
- Use snake_case for event names
- Start with category prefix (consent_, privacy_, compliance_)
- Use descriptive, specific names
- Avoid abbreviations
- Keep names under 50 characters

#### Examples
- ✅ `consent_banner_accept`
- ✅ `privacy_policy_view`
- ✅ `dsr_request_submitted`
- ❌ `cba` (too abbreviated)
- ❌ `user_did_something` (too vague)

### Property Naming Conventions

#### Property Standards
- Use snake_case for property names
- Use descriptive names
- Include units in names when applicable (_seconds, _percentage)
- Use consistent data types
- Document all custom properties

#### Data Types
```json
{
  "string_property": "text_value",
  "numeric_property": 42,
  "boolean_property": true,
  "array_property": ["item1", "item2"],
  "object_property": {"key": "value"},
  "timestamp_property": "2024-01-15T10:30:00Z"
}
```

## Platform-Specific Event Mapping

### Google Analytics 4 Mapping

#### GA4 Event Structure
```javascript
gtag('event', 'consent_banner_accept', {
  'consent_categories': 'essential,analytics',
  'interaction_time': 15,
  'geo_location': 'EU',
  'custom_parameter_1': 'value'
});
```

#### GA4 Custom Events
- Map SLOS events to GA4 custom events
- Use custom parameters for additional data
- Set up conversion events for key actions
- Configure custom dimensions for segmentation

### Segment Event Mapping

#### Segment Event Structure
```javascript
analytics.track('Consent Banner Accept', {
  consent_categories: ['essential', 'analytics'],
  interaction_time: 15,
  geo_location: 'EU'
});
```

#### Segment Integration
- Use Segment's tracking methods
- Implement consent-aware destination filters
- Set up transformation functions
- Configure event forwarding rules

### Mixpanel Event Mapping

#### Mixpanel Event Structure
```javascript
mixpanel.track('Consent Banner Accept', {
  'Consent Categories': ['essential', 'analytics'],
  'Interaction Time': 15,
  'Geo Location': 'EU'
});
```

#### Mixpanel Features
- Use Mixpanel's event tracking
- Set up user profiles with consent data
- Create cohorts based on consent status
- Implement A/B testing for consent flows

### Facebook Pixel Event Mapping

#### Facebook Event Structure
```javascript
fbq('track', 'ConsentGiven', {
  content_name: 'Privacy Consent',
  content_category: 'Privacy',
  consent_categories: ['essential', 'analytics']
});
```

#### Facebook Custom Events
- Use Facebook's custom event tracking
- Set up consent-aware conversion tracking
- Create custom audiences based on consent
- Implement privacy-compliant retargeting

## Event Processing Pipeline

### Event Collection

#### Client-Side Collection
- Browser-based event capture
- Consent status validation
- Data anonymization
- Batch processing for efficiency

#### Server-Side Collection
- Server-generated events
- API-based event submission
- Webhook event processing
- Real-time event streaming

### Event Processing

#### Real-Time Processing
- Immediate event validation
- Consent status checking
- Platform-specific formatting
- Real-time platform delivery

#### Batch Processing
- Event aggregation
- Data enrichment
- Duplicate removal
- Batch platform delivery

### Event Storage

#### Event Storage Structure
```json
{
  "event_id": "uuid",
  "event_type": "consent_banner_accept",
  "user_id": "anonymized_id",
  "properties": {},
  "context": {},
  "timestamp": "2024-01-15T10:30:00Z",
  "platform_data": {
    "ga4": "sent",
    "segment": "sent",
    "mixpanel": "queued"
  }
}
```

#### Storage Optimization
- Data compression
- Index optimization
- Retention policies
- Archive strategies

## Event Validation and Quality

### Event Validation

#### Schema Validation
- JSON schema validation
- Property type checking
- Required field validation
- Custom validation rules

#### Data Quality Checks
- Duplicate event detection
- Outlier detection
- Data completeness checks
- Consent status validation

### Quality Metrics

#### Event Quality Metrics
- **Validity Rate:** Percentage of valid events
- **Completeness Score:** Average property completeness
- **Timeliness:** Average processing delay
- **Accuracy:** Event data accuracy rate

#### Monitoring Dashboards
- Real-time quality monitoring
- Alert system for quality issues
- Trend analysis for quality metrics
- Automated quality reports

## Event Debugging and Testing

### Debug Mode

#### Development Debug
```javascript
// Enable debug logging
window.SLOS_DEBUG = true;

// Log all events
window.SLOS_EVENTS = [];

// Console output for events
console.log('SLOS Event:', eventName, properties);
```

#### Platform Debug Tools
- **GA4 Debug:** Use GA4 debug mode
- **Segment Debug:** Use Segment debugger
- **Mixpanel Debug:** Enable Mixpanel debug
- **Facebook Debug:** Use Facebook pixel helper

### Testing Framework

#### Event Testing
```javascript
// Test event firing
slosAnalytics.track('test_event', {
  test_property: 'test_value'
});

// Verify event in platform
// Check console logs
// Validate data accuracy
```

#### Integration Testing
- Test end-to-end event flow
- Verify platform delivery
- Check data transformation
- Validate consent filtering

## Performance Considerations

### Event Processing Performance

#### Optimization Techniques
- Event batching for efficiency
- Asynchronous processing
- Caching strategies
- Compression techniques

#### Performance Metrics
- **Processing Latency:** Average event processing time
- **Throughput:** Events processed per second
- **Error Rate:** Failed event processing rate
- **Resource Usage:** CPU and memory usage

### Scalability Considerations

#### High Volume Handling
- Horizontal scaling
- Load balancing
- Queue management
- Auto-scaling rules

#### Global Distribution
- CDN integration
- Regional processing
- Data localization
- Cross-region replication

## Security and Privacy

### Event Security

#### Data Protection
- Event data encryption
- Secure transmission
- Access control
- Audit logging

#### Privacy Protection
- Data anonymization
- Consent validation
- Retention controls
- Deletion capabilities

### Compliance Considerations

#### GDPR Compliance
- Lawful processing basis
- Data minimization
- Purpose limitation
- User rights implementation

#### CCPA Compliance
- Data collection disclosure
- Opt-out processing
- Deletion rights
- Non-discrimination

## Related Documentation

- [Overview](overview.md)
- [Platform Setup](platform-setup.md)
- [Metrics & Reporting](metrics-reporting.md)
- [Privacy Compliance](privacy-compliance.md)