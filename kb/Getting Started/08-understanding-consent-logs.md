# Understanding Consent Logs

## Monitor User Consent and Track Compliance

### What Are Consent Logs?

#### Consent Log Overview

**Purpose:**
- Record all user consent decisions
- Track consent changes over time
- Provide audit trail for compliance
- Support data subject rights requests
- Generate compliance reports

**Data Captured:**
```json
{
  "log_id": 12345,
  "user_id": "user_67890",
  "session_id": "session_abc123",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "consent_timestamp": "2025-12-31T10:30:00Z",
  "consent_categories": {
    "essential": true,
    "analytics": true,
    "marketing": false,
    "preferences": true
  },
  "consent_source": "banner",
  "consent_version": "2.1",
  "geo_location": "US-CA",
  "referrer": "https://yourwebsite.com/page1"
}
```

#### Log Types

**Initial Consent:**
- First-time visitor consent
- Banner interaction
- Default consent application
- Import from other systems

**Consent Updates:**
- Preference changes
- Category modifications
- Consent withdrawal
- Re-consent after policy updates

**Automated Logs:**
- Cookie scanning results
- Policy change notifications
- Compliance audit entries
- System maintenance logs

### Access Consent Logs

#### Step 1: Navigate to Logs
```
SLOS → Consent Management → Consent Logs → View Logs
```

**Log Interface Features:**
- Date range filtering
- User search
- Category filtering
- Export options
- Real-time updates

#### Step 2: Filter and Search
```
SLOS → Consent Management → Consent Logs → Filters
```

**Common Filters:**
```json
{
  "date_range": {
    "start": "2025-12-01",
    "end": "2025-12-31"
  },
  "categories": ["analytics", "marketing"],
  "consent_status": "granted",
  "geo_region": "EU",
  "source": "banner"
}
```

### Analyze Consent Patterns

#### Step 1: View Consent Analytics
```
SLOS → Analytics → Consent Analytics → Dashboard
```

**Key Metrics:**
- Overall consent rate: 85%
- Category acceptance rates
- Consent withdrawal rate: 5%
- Average consent time: 45 seconds
- Mobile vs desktop consent rates

#### Step 2: Category Performance

**Analytics Category:**
- Acceptance rate: 78%
- Withdrawal rate: 12%
- Average decision time: 30 seconds
- Peak consent times: 2-4 PM

**Marketing Category:**
- Acceptance rate: 45%
- Withdrawal rate: 25%
- Average decision time: 60 seconds
- Geographic variations: EU (35%), US (55%)

**Preferences Category:**
- Acceptance rate: 92%
- Withdrawal rate: 3%
- Average decision time: 15 seconds
- High acceptance across all regions

### Monitor Consent Compliance

#### Step 1: Check Consent Validity
```
SLOS → Compliance → Consent Validation → Run Check
```

**Validation Rules:**
- Consent age (not older than 2 years)
- Required categories granted
- Valid consent source
- Complete user information
- Geographic compliance

#### Step 2: Identify Issues

**Invalid Consent Records:**
```json
[
  {
    "log_id": 12345,
    "issue": "consent_too_old",
    "consent_date": "2023-12-31",
    "days_old": 731,
    "action_required": "re-consent"
  },
  {
    "log_id": 12346,
    "issue": "missing_category",
    "missing_categories": ["preferences"],
    "action_required": "update_consent"
  }
]
```

### Handle Consent Withdrawals

#### Step 1: Process Withdrawal Requests
```
SLOS → DSR Portal → Requests → Review Withdrawals
```

**Withdrawal Process:**
1. Receive withdrawal request
2. Verify user identity
3. Log withdrawal in consent logs
4. Update user consent status
5. Delete associated data if requested
6. Send confirmation email

#### Step 2: Update Consent Logs

**Withdrawal Log Entry:**
```json
{
  "log_id": 12347,
  "user_id": "user_67890",
  "action": "withdrawal",
  "withdrawal_categories": ["analytics", "marketing"],
  "withdrawal_reason": "privacy_concerns",
  "withdrawal_timestamp": "2025-12-31T14:20:00Z",
  "processed_by": "admin_user",
  "confirmation_sent": true
}
```

### Generate Consent Reports

#### Step 1: Create Compliance Reports
```
SLOS → Reports → Consent → Generate Report
```

**Report Types:**
- Daily consent summary
- Monthly compliance audit
- Quarterly regulatory report
- Annual privacy impact assessment

#### Step 2: Export Log Data
```
SLOS → Consent Management → Consent Logs → Export
```

**Export Formats:**
- CSV for spreadsheet analysis
- JSON for API integration
- PDF for regulatory submission
- XML for legal archives

**Export Configuration:**
```json
{
  "format": "csv",
  "date_range": "last_30_days",
  "include_fields": [
    "user_id",
    "consent_timestamp",
    "consent_categories",
    "ip_address",
    "geo_location"
  ],
  "anonymize_data": true,
  "compression": "gzip"
}
```

### Set Up Consent Monitoring

#### Step 1: Configure Alerts
```
SLOS → Consent Management → Alerts → Consent Monitoring
```

**Alert Types:**
- [x] Low consent rates (< 50%)
- [x] High withdrawal rates (> 20%)
- [x] Invalid consent records
- [x] Geographic compliance issues
- [x] Policy change notifications

#### Step 2: Automated Reviews
```
SLOS → Compliance → Automated Review → Schedule
```

**Review Schedule:**
```json
{
  "frequency": "weekly",
  "review_types": [
    "consent_validity",
    "category_compliance",
    "geographic_coverage",
    "withdrawal_patterns"
  ],
  "alert_thresholds": {
    "invalid_consent_percentage": 5,
    "withdrawal_rate_increase": 10
  }
}
```

### Handle Data Subject Rights

#### Step 1: Access Request Processing
```
SLOS → DSR Portal → Requests → Access Requests
```

**Access Request Workflow:**
1. Verify user identity
2. Query consent logs
3. Gather all user data
4. Generate data export
5. Send secure download link
6. Log access request

#### Step 2: Data Export Generation

**Consent Data Export:**
```json
{
  "export_id": "dsr_12345",
  "user_id": "user_67890",
  "request_type": "access",
  "data_categories": {
    "consent_logs": [
      {
        "timestamp": "2025-01-15T10:30:00Z",
        "categories": ["essential", "analytics"],
        "source": "banner"
      },
      {
        "timestamp": "2025-06-20T14:15:00Z",
        "categories": ["essential", "analytics", "marketing"],
        "source": "portal"
      }
    ],
    "consent_withdrawals": [
      {
        "timestamp": "2025-12-31T14:20:00Z",
        "categories": ["marketing"],
        "reason": "privacy_concerns"
      }
    ]
  },
  "export_format": "json",
  "secure_link_expiry": "2026-01-14T14:20:00Z"
}
```

### Troubleshoot Log Issues

#### Common Problems

**Logs Not Recording:**
- Check database connectivity
- Verify table permissions
- Review error logs
- Test consent submission

**Missing Consent Data:**
- Check data retention settings
- Verify backup integrity
- Review migration history
- Test data export

**Invalid Log Entries:**
- Validate data format
- Check for corrupted records
- Review input sanitization
- Update validation rules

**Performance Issues:**
- Optimize database queries
- Implement log rotation
- Use indexing strategies
- Archive old logs

### Optimize Log Performance

#### Step 1: Database Optimization
```
SLOS → Advanced → Database → Log Optimization
```

**Optimization Strategies:**
```sql
-- Add indexes for common queries
CREATE INDEX idx_consent_user_timestamp ON slos_consent_logs (user_id, consent_timestamp);
CREATE INDEX idx_consent_category ON slos_consent_logs (consent_categories);
CREATE INDEX idx_consent_geo ON slos_consent_logs (geo_location);

-- Partition large log tables
PARTITION BY RANGE (YEAR(consent_timestamp)) (
  PARTITION p2023 VALUES LESS THAN (2024),
  PARTITION p2024 VALUES LESS THAN (2025),
  PARTITION p2025 VALUES LESS THAN (2026)
);
```

#### Step 2: Log Rotation
```
SLOS → Advanced → System → Log Management
```

**Rotation Settings:**
```json
{
  "rotation_frequency": "monthly",
  "retention_period": "7_years",
  "compression": "gzip",
  "archive_location": "/logs/archive/",
  "auto_cleanup": true
}
```

### Security Considerations

#### Step 1: Data Protection
```
SLOS → Security → Data Protection → Log Security
```

**Security Measures:**
- [x] Data encryption at rest
- [x] Access logging
- [x] IP address anonymization
- [x] Secure export links
- [x] Audit trail integrity

#### Step 2: Privacy Compliance
- Implement data minimization
- Regular privacy impact assessments
- Secure data disposal procedures
- Cross-border transfer safeguards

### Advanced Log Analysis

#### Step 1: Custom Queries
```
SLOS → Advanced → Analytics → Custom Queries
```

**Sample Queries:**
```sql
-- Consent rate by category over time
SELECT
  DATE(consent_timestamp) as date,
  SUM(CASE WHEN JSON_CONTAINS(consent_categories, 'analytics') THEN 1 ELSE 0 END) as analytics_consent,
  COUNT(*) as total_consent
FROM slos_consent_logs
WHERE consent_timestamp >= '2025-01-01'
GROUP BY DATE(consent_timestamp);

-- Geographic consent patterns
SELECT
  geo_location,
  AVG(CASE WHEN JSON_CONTAINS(consent_categories, 'marketing') THEN 1 ELSE 0 END) as marketing_rate,
  COUNT(*) as total_users
FROM slos_consent_logs
GROUP BY geo_location
ORDER BY marketing_rate DESC;
```

#### Step 2: Automated Insights
```
SLOS → Analytics → Insights → Consent Patterns
```

**Automated Analysis:**
- Consent trend identification
- Anomaly detection
- Predictive modeling
- Compliance risk assessment

### Support Resources

#### Documentation
- [Consent Management Overview](../Features/Consent-Management/consent-tracking.md)
- [DSR Portal Configuration](../Features/DSR-Portal/configuration.md)
- [Analytics Integration](../Features/Analytics-Integration/overview.md)

#### Help
- Consent log FAQ
- Compliance consultation
- Technical support
- Legal guidance