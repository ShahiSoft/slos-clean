# REST API Documentation

## Overview

Shahi LegalFlowSuite exposes REST API endpoints for programmatic access to compliance data, DSR requests, accessibility results, and more.

## Authentication

All endpoints require authentication:

```
Authorization: Bearer YOUR_JWT_TOKEN
```

Or use WordPress nonce:
```
X-WP-Nonce: YOUR_NONCE
```

### Capabilities Required

- `manage_options` - Full access to all endpoints
- `edit_posts` - Limited read access
- `read` - Public endpoints only

## Base URL

```
https://yoursite.com/wp-json/slos/v1/
```

## Endpoints

### 1. Consent Records

#### List Consents

```
GET /consents
```

**Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Results per page (default: 20, max: 100)
- `filter[geo_rule_id]` - Filter by geo rule
- `filter[region]` - Filter by region (EU, US-CA, BR)
- `filter[status]` - Filter by status (accepted, rejected, customized)
- `filter[date_from]` - Start date (YYYY-MM-DD)
- `filter[date_to]` - End date (YYYY-MM-DD)

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "created_at": "2025-01-15T10:30:00Z",
      "geo_rule_id": "gdpr-eu",
      "region": "EU",
      "template": "gdpr",
      "variant": "A",
      "categories": {
        "essential": true,
        "analytics": true,
        "marketing": false
      },
      "status": "accepted",
      "duration_ms": 3500,
      "user_agent": "Mozilla/5.0...",
      "ip_address": "192.168.1.1",
      "policy_version": "2.0"
    }
  ],
  "total": 1250,
  "pages": 63
}
```

#### Get Consent Details

```
GET /consents/{id}
```

**Response:** Single consent object (see above)

#### Create Consent (Programmatic)

```
POST /consents
```

**Body:**
```json
{
  "geo_rule_id": "gdpr-eu",
  "region": "EU",
  "template": "gdpr",
  "categories": {
    "essential": true,
    "analytics": true
  },
  "status": "accepted"
}
```

### 2. Accessibility Scans

#### List Scans

```
GET /accessibility/scans
```

**Parameters:**
- `page` - Page number
- `per_page` - Results per page
- `filter[status]` - completed, in_progress, failed
- `filter[date_from]` - Start date
- `filter[date_to]` - End date

**Response:**
```json
{
  "data": [
    {
      "id": 456,
      "created_at": "2025-01-15T10:00:00Z",
      "status": "completed",
      "pages_scanned": 45,
      "issues_found": 123,
      "issues_fixed": 112,
      "accessibility_score": 91,
      "estimated_time": "5:30",
      "fixer_coverage": "91%"
    }
  ],
  "total": 25,
  "pages": 2
}
```

#### Get Scan Results

```
GET /accessibility/scans/{id}
```

**Response:**
```json
{
  "id": 456,
  "overall_score": 91,
  "pages": [
    {
      "url": "https://yoursite.com/",
      "score": 95,
      "issues": [
        {
          "type": "focus_indicator",
          "severity": "major",
          "description": "Missing focus indicator",
          "fixer": "FocusIndicatorFixer",
          "status": "fixed"
        }
      ]
    }
  ]
}
```

#### Start New Scan

```
POST /accessibility/scans
```

**Body:**
```json
{
  "url": "https://yoursite.com/",
  "scope": "single",
  "max_pages": 100,
  "auto_fix": true
}
```

### 3. DSR Requests

#### List DSR Requests

```
GET /dsr/requests
```

**Parameters:**
- `page` - Page number
- `per_page` - Results per page
- `filter[status]` - pending, approved, completed, rejected
- `filter[type]` - access, deletion, portability, rectification
- `filter[priority]` - high, normal, low

**Response:**
```json
{
  "data": [
    {
      "id": 789,
      "request_type": "access",
      "requester_email": "user@example.com",
      "status": "pending",
      "created_at": "2025-01-15T09:00:00Z",
      "deadline": "2025-02-14T09:00:00Z",
      "days_remaining": 30,
      "priority": "normal",
      "assigned_to": "admin@site.com"
    }
  ],
  "total": 42,
  "pages": 3
}
```

#### Get DSR Request Details

```
GET /dsr/requests/{id}
```

**Response:**
```json
{
  "id": 789,
  "request_type": "access",
  "requester_email": "user@example.com",
  "requester_name": "John Doe",
  "status": "pending",
  "verification_status": "verified",
  "created_at": "2025-01-15T09:00:00Z",
  "deadline": "2025-02-14T09:00:00Z",
  "assigned_to": "admin@site.com",
  "notes": "High priority - media inquiry",
  "export_format": "json",
  "export_ready": false
}
```

#### Update DSR Request

```
PATCH /dsr/requests/{id}
```

**Body:**
```json
{
  "status": "approved",
  "assigned_to": "support@site.com",
  "notes": "User verified via email"
}
```

#### Export DSR Data

```
GET /dsr/requests/{id}/export
```

**Query Parameters:**
- `format` - json, csv, pdf (default: json)

**Response:** Exported file or export URL

### 4. Legal Documents

#### List Documents

```
GET /documents
```

**Response:**
```json
{
  "data": [
    {
      "id": 101,
      "type": "privacy_policy",
      "version": "2.1",
      "created_at": "2025-01-15T08:00:00Z",
      "updated_at": "2025-01-15T08:00:00Z",
      "effective_date": "2025-02-01T00:00:00Z",
      "status": "published",
      "language": "en",
      "page_id": 42
    }
  ],
  "total": 4
}
```

#### Get Document Content

```
GET /documents/{id}
```

**Response:**
```json
{
  "id": 101,
  "type": "privacy_policy",
  "version": "2.1",
  "content": "# Privacy Policy\n\nWe collect personal data...",
  "status": "published",
  "metadata": {
    "company_name": "Your Company",
    "effective_date": "2025-02-01"
  }
}
```

#### Update Document

```
PATCH /documents/{id}
```

**Body:**
```json
{
  "content": "Updated content...",
  "effective_date": "2025-02-15T00:00:00Z"
}
```

#### Generate Document

```
POST /documents
```

**Body:**
```json
{
  "type": "privacy_policy",
  "template": "standard",
  "language": "en",
  "auto_publish": false
}
```

### 5. Geo Detection

#### Detect User Region

```
GET /geo/region
```

**Response:**
```json
{
  "ip_address": "203.0.113.42",
  "country_code": "AU",
  "country": "Australia",
  "region": "Other",
  "city": "Sydney",
  "latitude": -33.8688,
  "longitude": 151.2093,
  "timezone": "Australia/Sydney"
}
```

### 6. Cookie Inventory

#### List Cookies

```
GET /cookies
```

**Parameters:**
- `filter[category]` - essential, analytics, marketing, preferences
- `filter[vendor]` - Cookie vendor name

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "_ga",
      "vendor": "Google",
      "category": "analytics",
      "provider": "analytics.google.com",
      "purpose": "Traffic analysis",
      "retention": "2 years",
      "privacy_policy": "https://policies.google.com/privacy"
    }
  ],
  "total": 28
}
```

#### Recategorize Cookie

```
PATCH /cookies/{id}
```

**Body:**
```json
{
  "category": "analytics",
  "purpose": "Updated purpose"
}
```

### 7. Analytics Events

#### Get Event Statistics

```
GET /analytics/events
```

**Parameters:**
- `filter[event_type]` - shown, accepted, rejected, customized
- `filter[date_from]` - Start date
- `filter[date_to]` - End date
- `group_by` - none, day, week, month, region, variant

**Response:**
```json
{
  "data": [
    {
      "date": "2025-01-15",
      "shown": 1250,
      "accepted": 850,
      "rejected": 150,
      "customized": 250,
      "acceptance_rate": 68,
      "rejection_rate": 12
    }
  ],
  "total_shown": 15000,
  "total_accepted": 10200,
  "acceptance_rate_overall": 68
}
```

#### Get Variant Performance

```
GET /analytics/variants
```

**Response:**
```json
{
  "data": [
    {
      "variant": "A",
      "impressions": 5000,
      "acceptances": 3400,
      "acceptance_rate": 68,
      "avg_time_to_decision": 4200
    },
    {
      "variant": "B",
      "impressions": 5100,
      "acceptances": 3600,
      "acceptance_rate": 71,
      "avg_time_to_decision": 3800
    }
  ]
}
```

### 8. Settings & Config

#### Get Plugin Settings

```
GET /settings
```

**Response:**
```json
{
  "modules": {
    "consent_management": true,
    "accessibility_scanner": true,
    "legal_documents": true,
    "dsr_portal": true
  },
  "banner_template": "gdpr",
  "geo_targeting_enabled": true,
  "company_profile": {
    "name": "Your Company",
    "website": "https://yoursite.com"
  }
}
```

#### Update Settings

```
PATCH /settings
```

**Body:**
```json
{
  "banner_template": "advanced",
  "geo_targeting_enabled": true
}
```

### 9. System Info

#### Get System Status

```
GET /system/status
```

**Response:**
```json
{
  "plugin_version": "3.5.0",
  "wordpress_version": "6.7",
  "php_version": "8.1.0",
  "database": {
    "type": "MySQL",
    "version": "8.0.28"
  },
  "modules": {
    "consent_management": "active",
    "accessibility_scanner": "active"
  },
  "database_tables": {
    "consents": 15000,
    "scans": 250,
    "dsr_requests": 42
  }
}
```

## Error Handling

All errors return consistent format:

```json
{
  "code": "invalid_permission",
  "message": "User does not have permission to access this resource",
  "data": {
    "status": 403
  }
}
```

### Common Error Codes

| Code | Status | Meaning |
|------|--------|---------|
| `invalid_permission` | 403 | Insufficient permissions |
| `invalid_request` | 400 | Bad request format |
| `not_found` | 404 | Resource not found |
| `invalid_auth` | 401 | Authentication failed |
| `server_error` | 500 | Server error |

## Webhooks

### Register Webhook

```
POST /webhooks
```

**Body:**
```json
{
  "event": "consent.accepted",
  "url": "https://yourapp.com/webhook",
  "active": true
}
```

### Events

- `consent.shown` - Banner displayed
- `consent.accepted` - User accepted consent
- `consent.rejected` - User rejected consent
- `dsr.request_submitted` - DSR request received
- `dsr.request_approved` - DSR request approved
- `scan.completed` - Accessibility scan finished
- `document.published` - Document published

### Webhook Payload

```json
{
  "event": "consent.accepted",
  "timestamp": "2025-01-15T10:30:00Z",
  "data": {
    "id": 123,
    "geo_rule_id": "gdpr-eu",
    "categories": ["essential", "analytics"]
  }
}
```

## Rate Limiting

- Authenticated: 1000 requests/hour
- Public: 100 requests/hour

Headers returned:
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1642256400
```

## Pagination

List endpoints support pagination:

**Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)

**Response Headers:**
```
X-Total-Items: 1250
X-Total-Pages: 63
Link: <...?page=2>; rel="next"
```

## API Examples

### Example 1: Get Consent Rate

```bash
curl -X GET "https://yoursite.com/wp-json/slos/v1/analytics/events?group_by=day" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Example 2: Process DSR Request

```bash
curl -X PATCH "https://yoursite.com/wp-json/slos/v1/dsr/requests/789" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "approved",
    "assigned_to": "support@site.com"
  }'
```

### Example 3: JavaScript Integration

```javascript
// Get acceptance rates
fetch('/wp-json/slos/v1/analytics/events?filter[event_type]=accepted', {
  headers: {
    'X-WP-Nonce': slosNonce
  }
})
.then(r => r.json())
.then(data => console.log(data.total_accepted));
```

## Next Steps

- Explore specific endpoints
- Build custom integrations
- Create dashboards
- Automate compliance workflows

## Related

- [Hooks & Filters](02-hooks-filters.md)
- [Developer Guide](03-developer-guide.md)
