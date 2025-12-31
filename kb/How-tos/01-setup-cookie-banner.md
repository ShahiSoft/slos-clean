# How to Set Up Your Cookie Consent Banner

## Overview

This guide walks you through setting up and customizing the cookie consent banner for your website.

## Prerequisites

- Shahi LegalFlowSuite installed and activated
- Consent Management module enabled
- Admin access to WordPress

## Step 1: Navigate to Consent Settings

1. Log in to WordPress admin
2. Go to **SLOS** → **Consent Management**
3. Click **Settings** tab

## Step 2: Choose Banner Template

Select one of four templates:

### Simple Template
- Basic accept/reject buttons
- Minimal text
- Best for: Simple compliance
- Variants: Light, Dark

### GDPR Template (EU)
- Detailed GDPR language
- Cookie categories explained
- "More options" button for granular control
- Best for: EU sites

### CCPA Template (California)
- California privacy notice format
- "Do Not Sell My Personal Information"
- Specific CCPA wording
- Best for: California-facing sites

### Advanced Template
- All categories customizable
- Detailed explanations
- Full consent management
- Best for: Complex privacy needs

## Step 3: Customize Banner Text

### Required Text Fields

**Banner Title**
- Default: "We use cookies"
- Max 100 characters
- Makes banner headline

**Banner Description**
- Explain cookie usage
- Default: Generic privacy text
- 200-500 characters recommended
- Max 1000 characters

**Accept Button Text**
- Default: "Accept All"
- Max 50 characters

**Reject Button Text**
- Default: "Reject All"
- Max 50 characters

**Settings Button Text**
- Default: "Cookie Preferences"
- Max 50 characters

**Save Preferences Button**
- Default: "Save Preferences"
- Max 50 characters

### Category Descriptions

For each cookie category:

**Essential/Necessary**
- Explain required cookies
- Add to all templates

**Analytics**
- Explain analytics purposes
- Describe data collection

**Marketing**
- Explain marketing cookies
- Describe tracking used

**Preferences**
- Explain preference cookies
- User preference tracking

## Step 4: Configure Banner Appearance

### Position
- **Top** - Banner at page top
- **Bottom** - Banner at bottom (recommended)
- **Bottom-Left** - Corner position
- **Bottom-Right** - Corner position

### Theme
- **Light** - White background, dark text
- **Dark** - Dark background, light text
- **Auto** - Detect from site

### Colors
- **Primary Color** - Buttons, highlights (default: cyan)
- **Secondary Color** - Text, borders (default: white)
- **Accept Button Color** - Custom accept button color
- **Reject Button Color** - Custom reject button color

### Size
- **Normal** - Standard banner height
- **Compact** - Smaller, more discrete
- **Full** - Large, attention-grabbing

### Display Delay
- How many milliseconds before banner shows
- Default: 1000ms (1 second)
- Adjust to let content load first

## Step 5: Link to Privacy Policy

1. Go to **Banner Links**
2. Enter URL for Privacy Policy
3. Enter URL for Cookie Policy
4. Enter URL for Terms of Service
5. Links open in banner footer

## Step 6: Configure Cookie Scanner

### Enable Scanner
1. Check **"Enable Cookie Scanner"**
2. Choose scan frequency:
   - Daily
   - Weekly
   - Monthly
   - Manual only

### Scan Settings
- **Max URLs to Scan** - How many pages to scan (default: 100)
- **Deep Scan** - Scan subdirectories
- **Background Scan** - Run at off-peak times

### Cookie Categorization
1. Scanner finds cookies
2. Auto-categorizes by type
3. You can recategorize manually
4. Mark as "essential" if required

## Step 7: Test the Banner

### Desktop Testing

1. Open your website in a new browser
2. Clear all cookies (DevTools → Storage)
3. Refresh page
4. Banner should appear
5. Test buttons:
   - Click Accept → Check cookie set
   - Click Reject → Check limited cookies
   - Click Settings → Check preferences

### Mobile Testing

1. Open site on mobile device
2. Clear cookies and cache
3. Verify banner appears correctly
4. Test touch interactions
5. Verify responsive layout
6. Test on both portrait/landscape

### Clearing Test Cookies

In Chrome DevTools:
1. Open DevTools (F12)
2. Go to Application → Storage
3. Click Cookies → Your site
4. Select all and delete
5. Refresh page

## Step 8: Customize Additional Options

### Geo-Targeting (Optional)

1. Go to **Consent Management** → **Geo Rules**
2. Enable geo-targeting
3. Set rules:
   - EU visitors → GDPR template
   - California → CCPA template
   - Brazil → LGPD template
4. Save settings

### A/B Testing (Optional)

1. Go to **Consent Management** → **A/B Testing**
2. Enable A/B testing
3. Choose variants to test
4. Variant A: Your current banner
5. Variant B: Alternative design
6. Track results in analytics

### Analytics Events (Optional)

1. Go to **Consent Management** → **Analytics**
2. Select platform (GA4, Mixpanel, Segment, etc.)
3. Enable event tracking
4. Events: shown, accepted, rejected, customized
5. Configure custom events

## Step 9: Review Legal Compliance

**Before publishing:**

1. ✓ Privacy Policy updated
2. ✓ Cookie Policy created
3. ✓ GDPR/CCPA language included
4. ✓ Clear explanations of cookies
5. ✓ Proper links to policies

## Step 10: Deploy to Live Site

### Enable Banner on Frontend

1. Go to **Consent Management** → **Settings**
2. Check **"Enable Banner on Frontend"**
3. Check **"Auto-Load Banner"**
4. Save settings

### Verify Deployment

1. Visit your site (not logged in)
2. Clear browser cookies first
3. Banner should appear on first visit
4. Test all functionality
5. Check console for errors

## Troubleshooting

### Banner Not Showing

**Solutions:**
- Verify module is enabled
- Check "Enable Banner on Frontend" is checked
- Clear WordPress cache
- Clear browser cookies
- Check browser console for JavaScript errors
- Verify no CSS conflicts

### Banner Text Not Appearing

**Solutions:**
- Check text is saved
- Verify text length limits
- Check for special characters
- Clear site cache
- Reload page

### Colors Not Applying

**Solutions:**
- Verify color format (hex codes)
- Clear browser cache
- Hard refresh (Ctrl+F5)
- Check for CSS overrides
- Verify no conflicting plugins

### Cookies Not Setting

**Solutions:**
- Verify "Accept" button works
- Check browser allows cookies
- Test in private/incognito mode
- Review console errors
- Verify domain matches

## Best Practices

1. **Clear Language** - Users understand what they're consenting to
2. **Honest About Tracking** - Don't minimize data collection
3. **Easy Reject** - "Reject All" as easy as "Accept All"
4. **Mobile Optimized** - Works perfectly on mobile
5. **Legal Review** - Have lawyer review content
6. **Regular Updates** - Keep policies current
7. **Test Everything** - Before going live
8. **Monitor Analytics** - Track acceptance rates

## Next Steps

1. Customize banner for your site
2. Add Privacy Policy and Cookie Policy
3. Test thoroughly
4. Set up analytics (optional)
5. Monitor acceptance rates
6. Optimize based on metrics

## Related Articles

- [Cookie Scanner Setup](02-configure-cookie-scanner.md)
- [Privacy Policy Generation](../Features/03-legal-documents.md)
- [Analytics Integration](../Features/05-analytics-integration.md)
