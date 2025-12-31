# Run Your First Cookie Scan

## Discover and Categorize All Cookies on Your Website

### Understanding Cookie Scanning

#### What the Scanner Does

**Cookie Detection:**
- Automatically finds all cookies set by your website
- Identifies first-party and third-party cookies
- Detects cookies from embedded content (iframes, scripts)
- Scans dynamic content loaded via AJAX

**Cookie Analysis:**
- Determines cookie purpose and category
- Identifies data retention periods
- Flags potentially non-compliant cookies
- Provides cookie management recommendations

**Compliance Reporting:**
- Generates cookie inventory reports
- Creates consent banner categories
- Produces legal documentation updates
- Tracks cookie changes over time

### Prepare for Your First Scan

#### Step 1: Enable Cookie Scanner Module
```
SLOS → Dashboard → Module Management
```

**Required Settings:**
- [x] Cookie Scanner: Enabled
- [x] Consent Management: Enabled (for categorization)
- [x] Database access: Verified

#### Step 2: Configure Scan Settings
```
SLOS → Consent Management → Cookie Scanner → Settings
```

**Basic Configuration:**
```json
{
  "scan_depth": "comprehensive",
  "include_subpages": true,
  "max_pages": 50,
  "scan_frequency": "manual",
  "user_agent": "SLOS Cookie Scanner v3.1.1",
  "timeout": 30,
  "retry_attempts": 3
}
```

#### Step 3: Set Up Scan Permissions
```
SLOS → Consent Management → Cookie Scanner → Permissions
```

**Required Permissions:**
- [x] Read website content
- [x] Access database tables
- [x] Write scan results
- [x] Send notifications

### Run Your First Scan

#### Step 1: Start Manual Scan
```
SLOS → Consent Management → Cookie Scanner → Run Scan
```

**Scan Options:**
```json
{
  "scan_type": "full_site",
  "start_url": "https://yourwebsite.com",
  "crawl_depth": 3,
  "respect_robots_txt": true,
  "scan_javascript": true,
  "scan_iframes": true,
  "follow_redirects": true
}
```

#### Step 2: Monitor Scan Progress
```
SLOS → Consent Management → Cookie Scanner → Scan Status
```

**Progress Indicators:**
- Pages scanned: 0/50
- Cookies found: 0
- Time elapsed: 00:00:00
- Estimated completion: Calculating...

**Real-time Updates:**
- Current page being scanned
- Cookies discovered
- Errors encountered
- Performance metrics

### Review Scan Results

#### Step 1: View Cookie Inventory
```
SLOS → Consent Management → Cookie Scanner → Results → Cookie Inventory
```

**Cookie List Columns:**
- Cookie Name
- Domain
- Category
- Purpose
- Retention Period
- HttpOnly/Secure Flags
- First/Third Party

#### Step 2: Analyze Categories

**Essential Cookies (Usually 5-15):**
```json
[
  {
    "name": "PHPSESSID",
    "domain": ".yourwebsite.com",
    "category": "essential",
    "purpose": "Session management",
    "retention": "Session"
  },
  {
    "name": "wordpress_logged_in_",
    "domain": ".yourwebsite.com",
    "category": "essential",
    "purpose": "User authentication",
    "retention": "2 weeks"
  }
]
```

**Analytics Cookies (Usually 5-10):**
```json
[
  {
    "name": "_ga",
    "domain": ".google-analytics.com",
    "category": "analytics",
    "purpose": "Google Analytics tracking",
    "retention": "2 years"
  },
  {
    "name": "_gid",
    "domain": ".google-analytics.com",
    "category": "analytics",
    "purpose": "Google Analytics session tracking",
    "retention": "24 hours"
  }
]
```

**Marketing Cookies (Usually 10-50):**
```json
[
  {
    "name": "fr",
    "domain": ".facebook.com",
    "category": "marketing",
    "purpose": "Facebook advertising",
    "retention": "3 months"
  },
  {
    "name": "NID",
    "domain": ".google.com",
    "category": "marketing",
    "purpose": "Google advertising",
    "retention": "6 months"
  }
]
```

### Categorize Uncategorized Cookies

#### Step 1: Review Uncategoried Cookies
```
SLOS → Consent Management → Cookie Scanner → Results → Uncategoried
```

**Categorization Process:**
1. Read cookie description
2. Determine primary purpose
3. Assign appropriate category
4. Set retention period
5. Add legal basis

#### Step 2: Manual Categorization

**For Each Uncategoried Cookie:**
```json
{
  "cookie_name": "custom_tracking_id",
  "domain": ".yourwebsite.com",
  "category": "analytics", // essential, analytics, marketing, preferences
  "purpose": "Custom analytics tracking",
  "retention": "1 year",
  "legal_basis": "consent",
  "data_shared": false,
  "third_party": false
}
```

#### Step 3: Bulk Categorization
```
SLOS → Consent Management → Cookies → Bulk Actions
```

**Bulk Operations:**
- Assign category to multiple cookies
- Set retention periods
- Mark as first/third party
- Flag for legal review

### Update Consent Banner

#### Step 1: Sync Categories with Banner
```
SLOS → Consent Management → Banner Settings → Categories
```

**Banner Category Mapping:**
```json
{
  "essential": {
    "enabled": true,
    "required": true,
    "description": "Required for website functionality",
    "cookies": ["PHPSESSID", "wordpress_*", "wp-*"]
  },
  "analytics": {
    "enabled": true,
    "required": false,
    "description": "Help us improve our website",
    "cookies": ["_ga", "_gid", "_gat", "custom_analytics"]
  },
  "marketing": {
    "enabled": true,
    "required": false,
    "description": "Used for advertising and marketing",
    "cookies": ["fr", "NID", "ads/*", "doubleclick"]
  }
}
```

#### Step 2: Update Banner Text
```
SLOS → Consent Management → Banner Settings → Content
```

**Dynamic Cookie Count:**
```javascript
// Automatically update cookie counts
const cookieCounts = {
  essential: getCookieCount('essential'),
  analytics: getCookieCount('analytics'),
  marketing: getCookieCount('marketing')
};

// Update banner text
document.querySelector('.cookie-count-essential').textContent = cookieCounts.essential;
document.querySelector('.cookie-count-analytics').textContent = cookieCounts.analytics;
document.querySelector('.cookie-count-marketing').textContent = cookieCounts.marketing;
```

### Generate Cookie Policy

#### Step 1: Auto-Generate from Scan
```
SLOS → Legal Documents → Generate → Cookie Policy
```

**Policy Sections Created:**
- Introduction to cookies
- Types of cookies used
- Detailed cookie list with descriptions
- Cookie management instructions
- Third-party cookie information

#### Step 2: Review and Customize
```
SLOS → Legal Documents → Edit → Cookie Policy
```

**Customization Options:**
- Add company-specific language
- Include additional legal disclaimers
- Add cookie preference center links
- Customize cookie descriptions

### Set Up Ongoing Monitoring

#### Step 1: Configure Automated Scans
```
SLOS → Consent Management → Cookie Scanner → Schedule
```

**Recommended Schedule:**
```json
{
  "frequency": "weekly",
  "day_of_week": "monday",
  "time": "02:00",
  "scan_type": "incremental",
  "alert_on_changes": true,
  "alert_recipients": ["privacy@yourcompany.com"]
}
```

#### Step 2: Configure Alerts
```
SLOS → Consent Management → Cookie Scanner → Alerts
```

**Alert Types:**
- [x] New cookies detected
- [x] Cookies removed
- [x] Category changes required
- [x] Scan failures
- [x] Compliance issues

### Test Cookie Management

#### Step 1: Test Consent Functionality
```
SLOS → Consent Management → Test Mode
```

**Testing Scenarios:**
1. Accept all cookies
2. Reject all cookies
3. Accept only essential
4. Custom preferences
5. Withdraw consent

#### Step 2: Verify Cookie Behavior

**With Consent Granted:**
- [ ] Analytics cookies set
- [ ] Marketing pixels load
- [ ] Tracking scripts execute
- [ ] User preferences saved

**With Consent Denied:**
- [ ] Analytics cookies blocked
- [ ] Marketing pixels disabled
- [ ] Tracking scripts prevented
- [ ] Graceful degradation

### Handle Special Cases

#### Step 1: Embedded Content Cookies
```
SLOS → Consent Management → Cookie Scanner → Embedded Content
```

**Common Embedded Content:**
- YouTube videos
- Vimeo players
- Social media widgets
- Chat widgets
- Payment processors

**Handling Strategy:**
```javascript
// Lazy load embedded content
function loadEmbeddedContent(category, contentId) {
  if (window.SLOS.hasConsent(category)) {
    loadContent(contentId);
  } else {
    showPlaceholder(contentId);
  }
}
```

#### Step 2: Dynamic Cookies
```
SLOS → Consent Management → Cookie Scanner → Dynamic Cookies
```

**JavaScript-Generated Cookies:**
- User interaction cookies
- Form submission cookies
- Shopping cart cookies
- Preference cookies

**Detection Methods:**
- JavaScript analysis
- Network request monitoring
- DOM manipulation tracking
- Event listener monitoring

### Generate Compliance Reports

#### Step 1: Create Cookie Audit Report
```
SLOS → Reports → Compliance → Cookie Audit
```

**Report Contents:**
- Executive summary
- Cookie inventory
- Categorization status
- Compliance gaps
- Recommendations

#### Step 2: Schedule Regular Reports
```
SLOS → Reports → Schedule → Cookie Compliance
```

**Reporting Schedule:**
- Weekly: Cookie changes
- Monthly: Full compliance audit
- Quarterly: Comprehensive review
- Annually: Regulatory compliance

### Troubleshooting Scan Issues

#### Common Problems

**Scan Not Starting:**
- Check module activation
- Verify permissions
- Review server resources
- Check network connectivity

**Incomplete Results:**
- Increase timeout settings
- Reduce scan depth
- Check for blocked pages
- Review error logs

**Cookies Not Detected:**
- Enable JavaScript scanning
- Check iframe permissions
- Review cookie settings
- Test with different browsers

**Categorization Errors:**
- Update cookie database
- Review vendor documentation
- Manual categorization
- Community resources

### Performance Optimization

#### Step 1: Optimize Scan Settings
```
SLOS → Consent Management → Cookie Scanner → Performance
```

**Optimization Settings:**
```json
{
  "concurrent_scans": 2,
  "page_load_timeout": 15,
  "resource_timeout": 10,
  "cache_results": true,
  "compress_data": true
}
```

#### Step 2: Resource Management
- Schedule scans during low-traffic hours
- Limit scan scope for large sites
- Use incremental scanning
- Monitor server resources

### Next Steps

#### Immediate Actions
- Review and finalize cookie categories
- Update consent banner
- Publish cookie policy
- Test consent functionality

#### Ongoing Tasks
- Monitor cookie changes
- Regular compliance audits
- Update cookie policies
- Train staff on procedures

#### Advanced Features
- Set up cookie preference center
- Implement consent analytics
- Configure third-party integrations
- Automate compliance workflows

### Support Resources

#### Documentation
- [Consent Management Overview](../Features/Consent-Management/overview.md)
- [Cookie Scanner Configuration](../Features/Consent-Management/cookie-scanner.md)
- [Banner Customization](../How-tos/01-setup-cookie-banner.md)

#### Help
- Cookie scanning FAQ
- Vendor cookie documentation
- Compliance consultation
- Technical support