# How to Configure Geo-Targeting for Compliance

## Overview

This guide explains how to set up geo-targeting so the correct compliance template is automatically shown to users based on their location.

## Why Geo-Targeting?

- **EU Visitors** → See GDPR-compliant banner
- **California Visitors** → See CCPA template
- **Brazil Visitors** → See LGPD template
- **Other Regions** → See simple template or your choice
- **Automatic** → No manual configuration per visitor

## Prerequisites

- Consent Management module enabled
- Banner already configured
- Admin access

## How Geo-Detection Works

### Server-Side Detection (Primary)
1. Visitor IP address detected
2. Geo_Service looks up IP location
3. Returns country/region
4. Geo_Rule_Matcher finds matching rule
5. Correct template determined

### Frontend Fallback (Secondary)
1. If server-side fails, uses API
2. `/wp-json/slos/v1/geo/region` endpoint
3. Browser sends visitor IP
4. Returns detected region
5. JavaScript selects template

### Multi-Layer Fallback
1. Server detection
2. API detection  
3. Region mapping
4. Default template

## Step 1: Enable Geo-Targeting

1. Go to **SLOS** → **Consent Management**
2. Click **Settings** tab
3. Scroll to **Geo-Targeting**
4. Check **"Enable Geo-Targeting"**
5. Click **Save Changes**

## Step 2: Set Up Geo Rules

### EU (GDPR) Rule

**Location:** Austria, Belgium, Bulgaria, Croatia, Cyprus, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Hungary, Ireland, Italy, Latvia, Lithuania, Luxembourg, Malta, Netherlands, Poland, Portugal, Romania, Slovakia, Slovenia, Spain, Sweden

1. Go to **Consent Management** → **Geo Rules**
2. Click **Add Rule**
3. Rule Name: "GDPR - EU"
4. Countries: Select all EU countries (or bulk select "EU")
5. Template: Select **"GDPR Template"**
6. Set Effective: Immediately
7. Set Priority: High (executed first)
8. Click **Save Rule**

### California (CCPA) Rule

**Location:** United States - California

1. Click **Add Rule**
2. Rule Name: "CCPA - California"
3. Countries: United States
4. States/Provinces: California
5. Template: Select **"CCPA Template"**
6. Priority: High
7. Click **Save Rule**

### Brazil (LGPD) Rule

**Location:** Brazil

1. Click **Add Rule**
2. Rule Name: "LGPD - Brazil"
3. Countries: Brazil
4. Template: Select **"LGPD Template"** (or Advanced)
5. Priority: High
6. Click **Save Rule**

### Default Rule

**For all other locations:**

1. Click **Add Rule**
2. Rule Name: "Default Template"
3. Countries: All others
4. Template: Select **"Simple Template"**
5. Priority: Low (lowest priority)
6. Click **Save Rule**

## Step 3: Verify Detection Accuracy

### Test Detection

1. **From VPN in EU:**
   - Connect to EU VPN
   - Clear cookies
   - Visit site
   - GDPR banner should show

2. **From California IP:**
   - Connect to California VPN
   - Clear cookies
   - Visit site
   - CCPA banner should show

3. **Browser Dev Tools:**
   - Open Console
   - Look for logs like: "Geo region: EU"
   - Verify template selection

### Check Console Logs

Enable debug logging:
1. Go to **SLOS** → **Settings** → **Advanced**
2. Check **"Debug Logging"**
3. Console will show:
   - Detected region
   - Matched rule
   - Selected template
   - Fallback steps

### Manually Test API

In browser console:
```javascript
fetch('/wp-json/slos/v1/geo/region')
    .then(r => r.json())
    .then(d => console.log('Region:', d));
```

Returns: `{"region": "EU"}` or similar

## Step 4: Test All Templates

### Visual Verification

Test each scenario:

1. **EU Detection:**
   - GDPR-specific text
   - "More options" button
   - Cookie category explanations

2. **CCPA Detection:**
   - "Do Not Sell" language
   - California-specific rights
   - CCPA terminology

3. **Default Detection:**
   - Simple accept/reject
   - Basic information
   - Minimal text

### Verify Consent Recording

After interacting with each template:
1. Go to **Consent Management** → **Consents**
2. Look for recent entries
3. Check `geo_rule_id` field
4. Verify rule ID matches expected rule

## Step 5: Handle Special Cases

### IP Geolocation Limitations

Some IPs are hard to detect:
- VPN users
- Proxy users
- Corporate networks
- ISP non-standard routing

**Solution:**
- Add fallback detection
- Allow manual region selection
- Use secondary detection methods

### Adding Manual Region Override

Allow users to override detected region:

1. Edit banner text to add:
   "Not from [Region]? [Change Region]"
2. Link changes browser selection
3. User selects correct region
4. Browser stores preference

### Whitelist/Blacklist Countries

Create country lists:

1. Go to **Geo Rules** → **Settings**
2. **Whitelist:** Countries that MUST use specific template
3. **Blacklist:** Countries excluded from rules
4. High priority enforced
5. Save settings

## Step 6: Monitor Geo Detection

### Dashboard Metrics

In **Consent Management** → **Dashboard:**
- Regional distribution of consents
- Template impressions by region
- Acceptance rate by template
- Geo-detection accuracy

### Create Report

Export geo data:
1. Go to **Reports** → **Geo Analysis**
2. Select date range
3. View by region, country, template
4. Download CSV report
5. Share with compliance team

### Common Metrics

- Consents per region
- Template impressions by region
- Acceptance rates by template
- A/B variant distribution by region
- Time-to-decision by region

## Step 7: Troubleshooting Geo Rules

### Banner Still Shows Wrong Template

**Check:**
1. Verify rule is enabled
2. Check rule priority
3. Verify template exists
4. Clear browser cookies
5. Check console logs for errors

**Solution:**
- Increase rule priority
- Disable conflicting rules
- Update template settings

### Geo Detection Not Working

**Check:**
1. Verify geolocation service enabled
2. Test API endpoint manually
3. Check server logs
4. Verify user IP visible to server

**Solution:**
- Enable debug logging
- Check WordPress logs
- Verify server permissions
- Test with different IPs

### Wrong Region Detected

**Likely Causes:**
- User on VPN
- Proxy server
- ISP location mismatch
- Geolocation database outdated

**Solutions:**
- Allow manual override
- Use multiple detection methods
- Update geolocation database
- Document as known issue

## Step 8: Compliance Verification

### GDPR Compliance

For EU rule:
- ✓ Template has GDPR language
- ✓ Consent is explicit
- ✓ Rejection easy as acceptance
- ✓ Granular control available
- ✓ Consent recorded with geo data

### CCPA Compliance

For California rule:
- ✓ Template shows CCPA language
- ✓ "Do Not Sell" clearly displayed
- ✓ Rights explained
- ✓ Opt-out possible
- ✓ Consent tracked

### LGPD Compliance

For Brazil rule:
- ✓ Portuguese language preferred
- ✓ LGPD rights displayed
- ✓ Consent explicit
- ✓ Data storage explained
- ✓ International transfer notice

## Best Practices

1. **Test Thoroughly** - Use VPNs to test all regions
2. **Keep Rules Simple** - Don't over-complicate
3. **Prioritize Correctly** - Specific rules first
4. **Monitor Accuracy** - Check for misdetections
5. **Document Rules** - Keep notes on setup
6. **Review Regularly** - Update rules quarterly
7. **Get Legal Review** - Ensure compliance
8. **Have Fallback** - Default rule for unknowns

## Advanced: Custom Geo Rules

Create custom rules beyond pre-built:

1. Go to **Geo Rules** → **Advanced**
2. **Custom Rule Name** - Describe the rule
3. **Conditions:**
   - Country codes
   - Region/state codes
   - IP ranges
   - Custom parameters
4. **Action:** Select template
5. **Priority:** Set priority
6. **Save Rule**

Example: Japan-specific rule
```
Condition: Country = JP
Template: Detailed template (heavy tracking)
Priority: High
```

## Next Steps

1. Enable geo-targeting
2. Create rules for your regions
3. Test with VPNs
4. Monitor detection accuracy
5. Adjust rules as needed
6. Review compliance quarterly

## Related Articles

- [Setup Cookie Banner](01-setup-cookie-banner.md)
- [A/B Testing Guide](03-ab-testing-setup.md)
- [Analytics Integration](../Features/05-analytics-integration.md)
