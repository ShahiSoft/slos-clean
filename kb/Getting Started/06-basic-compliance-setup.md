# Basic Compliance Setup

## Get Your Site GDPR/CCPA Compliant in 30 Minutes

### Quick Compliance Checklist

#### Step 1: Enable Core Modules
```
SLOS → Dashboard → Module Management
```

**Required Modules:**
- [x] Consent Management (GDPR/CCPA compliance)
- [x] Legal Documents (Privacy Policy, Terms)
- [x] Cookie Scanner (Automated cookie detection)
- [x] DSR Portal (Data Subject Rights)

**Optional but Recommended:**
- [ ] Accessibility Scanner (WCAG compliance)
- [ ] Analytics Integration (Consent tracking)

#### Step 2: Configure Basic Settings
```
SLOS → Settings → General
```

**Essential Settings:**
```json
{
  "company_name": "Your Company Name",
  "website_url": "https://yourwebsite.com",
  "data_controller_email": "privacy@yourcompany.com",
  "data_protection_officer": "DPO Name",
  "jurisdictions": ["GDPR", "CCPA"],
  "retention_period": "2 years"
}
```

### Set Up Cookie Banner

#### Step 1: Choose Banner Template
```
SLOS → Consent Management → Banner Settings → Templates
```

**Recommended Template:** "Modern Dark" or "Clean Light"

**Banner Configuration:**
```json
{
  "position": "bottom",
  "layout": "full-width",
  "animation": "slide-up",
  "show_reject_button": true,
  "show_settings_button": true,
  "auto_hide": false
}
```

#### Step 2: Configure Cookie Categories

**Essential Cookies (Always Required):**
- Purpose: "Website functionality and security"
- Retention: "Session"
- Required: Yes

**Analytics Cookies (Optional):**
- Purpose: "Website analytics and performance"
- Retention: "2 years"
- Required: No

**Marketing Cookies (Optional):**
- Purpose: "Advertising and marketing"
- Retention: "1 year"
- Required: No

### Generate Legal Documents

#### Step 1: Create Privacy Policy
```
SLOS → Legal Documents → Generate → Privacy Policy
```

**Required Information:**
```json
{
  "company_info": {
    "name": "Your Company Name",
    "address": "Company Address",
    "email": "privacy@company.com",
    "phone": "Contact Phone"
  },
  "data_collection": {
    "personal_data": ["name", "email", "IP address"],
    "cookies_used": true,
    "third_parties": ["Google Analytics", "Facebook Pixel"]
  },
  "legal_basis": "consent",
  "retention_period": "2 years"
}
```

#### Step 2: Create Cookie Policy
```
SLOS → Legal Documents → Generate → Cookie Policy
```

**Cookie Policy Content:**
- What cookies are
- Types of cookies used
- How to manage cookies
- Third-party cookies
- Updates to policy

#### Step 3: Create Terms of Service (Optional)
```
SLOS → Legal Documents → Generate → Terms of Service
```

**Basic Terms Include:**
- User responsibilities
- Service usage
- Limitation of liability
- Governing law
- Dispute resolution

### Configure Data Subject Rights

#### Step 1: Set Up DSR Portal
```
SLOS → DSR Portal → Settings → Portal Configuration
```

**Portal Settings:**
```json
{
  "portal_url": "/data-requests",
  "require_verification": true,
  "verification_method": "email",
  "response_time": "30 days",
  "auto_response": true,
  "supported_requests": [
    "access",
    "rectification",
    "erasure",
    "portability",
    "restriction",
    "objection"
  ]
}
```

#### Step 2: Configure Email Templates
```
SLOS → DSR Portal → Settings → Email Templates
```

**Required Templates:**
- Request Received Confirmation
- Verification Email
- Data Export Ready
- Request Completed
- Request Denied

### Run Initial Cookie Scan

#### Step 1: Start Cookie Scanner
```
SLOS → Consent Management → Cookie Scanner → Run Scan
```

**Scan Configuration:**
```json
{
  "scan_type": "full_site",
  "include_subpages": true,
  "max_pages": 100,
  "scan_frequency": "weekly",
  "alert_on_new_cookies": true
}
```

#### Step 2: Review Scan Results
```
SLOS → Consent Management → Cookie Scanner → Results
```

**Review Checklist:**
- [ ] All cookies categorized correctly
- [ ] No uncategorized cookies
- [ ] Cookie descriptions accurate
- [ ] Third-party cookies identified
- [ ] Cookie retention periods set

### Set Up Geo-Targeting

#### Step 1: Configure Regions
```
SLOS → Consent Management → Geo-Targeting → Region Settings
```

**Primary Regions:**
```json
{
  "gdpr_countries": [
    "AT", "BE", "BG", "HR", "CY", "CZ", "DK", "EE", "FI", "FR",
    "DE", "GR", "HU", "IE", "IT", "LV", "LT", "LU", "MT", "NL",
    "PL", "PT", "RO", "SK", "SI", "ES", "SE", "GB", "IS", "LI", "NO"
  ],
  "ccpa_states": ["CA"],
  "lgpd_countries": ["BR"],
  "pipeda_provinces": ["CA"]
}
```

#### Step 2: Set Regional Rules
```
SLOS → Consent Management → Geo-Targeting → Regional Rules
```

**GDPR Settings:**
- Consent required for all non-essential cookies
- Data subject rights must be honored
- Privacy policy must be detailed

**CCPA Settings:**
- "Do Not Sell" option required
- Data sale opt-out available
- Privacy policy must include CCPA rights

### Configure Notifications

#### Step 1: Set Up Admin Alerts
```
SLOS → Settings → Notifications → Admin Alerts
```

**Essential Alerts:**
- [x] New DSR requests
- [x] Cookie scan completed
- [x] Compliance violations
- [x] System errors
- [x] Security issues

#### Step 2: Configure Email Settings
```
SLOS → Settings → Notifications → Email Settings
```

**Email Configuration:**
```json
{
  "smtp_host": "your-smtp-server.com",
  "smtp_port": 587,
  "smtp_secure": "tls",
  "smtp_username": "noreply@yourcompany.com",
  "smtp_password": "your-password",
  "from_email": "noreply@yourcompany.com",
  "from_name": "Your Company Privacy Team"
}
```

### Test Your Setup

#### Step 1: Banner Functionality Test

**Test Checklist:**
- [ ] Banner appears on page load
- [ ] Accept button works
- [ ] Reject button works
- [ ] Settings button opens preferences
- [ ] Consent is saved in browser
- [ ] Cookies are set/unset based on consent

#### Step 2: Document Accessibility Test

**Test Checklist:**
- [ ] Privacy Policy loads correctly
- [ ] Cookie Policy accessible
- [ ] Terms of Service available
- [ ] All documents are printable
- [ ] Mobile-friendly display

#### Step 3: DSR Portal Test

**Test Checklist:**
- [ ] Portal page loads
- [ ] Form submission works
- [ ] Email verification sent
- [ ] Admin notification received
- [ ] Request appears in dashboard

### Final Compliance Check

#### Step 1: Run Compliance Audit
```
SLOS → Compliance → Audit → Run Audit
```

**Audit Checklist:**
- [ ] Cookie banner compliant
- [ ] Privacy policy comprehensive
- [ ] DSR portal functional
- [ ] Cookie scanning active
- [ ] Geo-targeting configured
- [ ] Notifications working

#### Step 2: Document Your Setup

**Compliance Documentation:**
```json
{
  "compliance_date": "2025-12-31",
  "gdpr_compliant": true,
  "ccpa_compliant": true,
  "audit_frequency": "quarterly",
  "responsible_person": "Privacy Officer Name",
  "last_review": "2025-12-31",
  "next_review": "2026-03-31"
}
```

### Going Live

#### Step 1: Enable Production Mode
```
SLOS → Settings → General → Production Mode
```

**Production Settings:**
- Enable all compliance features
- Set up monitoring
- Configure backups
- Enable alerts

#### Step 2: Monitor Initial Usage
```
SLOS → Analytics → Dashboard
```

**Monitor For:**
- Consent rates
- DSR request volume
- Cookie scan results
- Error logs
- Performance metrics

### Common Setup Issues

#### Banner Not Appearing
- Check if module is enabled
- Verify theme compatibility
- Check for JavaScript conflicts
- Review browser console errors

#### Documents Not Generating
- Ensure company information is complete
- Check template selection
- Verify file permissions
- Review error logs

#### DSR Portal Not Working
- Confirm page creation
- Check email configuration
- Verify database tables
- Test form submission

### Next Steps

#### Week 1: Monitor and Adjust
- Watch consent analytics
- Review DSR requests
- Monitor cookie scans
- Adjust banner settings if needed

#### Month 1: Full Compliance Review
- Complete comprehensive audit
- Review all legal documents
- Test all DSR processes
- Update policies as needed

#### Ongoing: Maintenance
- Regular cookie scans
- Policy updates
- Compliance training
- System backups

### Support Resources

#### Documentation
- [Cookie Banner Setup](../How-tos/01-setup-cookie-banner.md)
- [Privacy Policy Generation](../How-tos/04-generate-privacy-policy.md)
- [DSR Portal Configuration](../Features/DSR-Portal/configuration.md)

#### Help
- Plugin support forums
- Compliance consultation
- Legal review services
- Training resources