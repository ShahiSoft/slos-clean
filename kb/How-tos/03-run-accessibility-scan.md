# How to Run Accessibility Scans

## Overview

This guide shows how to use the Accessibility Scanner to find and automatically fix WCAG 2.1 AA issues on your site.

## Prerequisites

- Accessibility Scanner module enabled
- Admin access to WordPress
- Site with pages to scan

## Step 1: Access Scanner

1. Log in to WordPress admin
2. Go to **SLOS** → **Accessibility Scanner**
3. View accessibility dashboard

## Step 2: Choose Scan Type

### Single Page Scan

Scan one specific page:

1. Enter page URL in **"Scan Single Page"** field
2. Click **Scan Page**
3. Progress popup appears
4. Real-time fixer status shown
5. Results displayed when complete

### Full Site Scan

Scan all pages on site:

1. Click **Scan All Pages**
2. Choose scan depth:
   - **Standard** - Main pages
   - **Deep** - All subpages
   - **Deep+** - All pages including archives
3. Choose max pages to scan (default: 100)
4. Click **Start Scan**
5. Background scan begins
6. Auto-refreshes with progress

### Custom URL Scan

Scan specific URLs only:

1. Go to **Advanced** → **Custom Scan**
2. Enter URLs (one per line):
   ```
   https://yoursite.com/page-1
   https://yoursite.com/page-2
   https://yoursite.com/about
   ```
3. Click **Scan Selected**

## Step 3: Monitor Scan Progress

### Progress Popup

During scan shows:
- **Overall Progress** - % complete
- **Current Page** - Currently scanning
- **Fixers Running** - Active auto-fixers
- **Fixes Applied** - Total fixes so far
- **Time Elapsed** - How long running
- **Estimated Time** - Time remaining

### Real-Time Fixer Status

See each fixer:
- ✓ Success - Issue detected and fixed
- ⊗ Failed - Issue couldn't be auto-fixed
- ○ Skipped - Not applicable to page
- ⟳ Running - Currently executing

### Cancel Scan

Click **Cancel Scan** to stop:
- Current page completes
- Results saved so far
- Can resume later

## Step 4: Review Scan Results

### Accessibility Dashboard

After scan completes:

1. **Overall Score** - Accessibility % (0-100%)
2. **Pages Scanned** - How many pages processed
3. **Issues Found** - Total issues detected
4. **Auto-Fixed** - Issues fixed automatically
5. **Remaining** - Manual fixes needed

### Score Breakdown

See scores by category:
- **Focus & Navigation** - Keyboard access
- **Vision & Color** - Visual accessibility
- **Touch & Motor** - Motor control
- **Language & Structure** - Content structure
- **ARIA & Roles** - Semantic markup
- **Forms & Input** - Form accessibility
- **Media** - Video, audio, images
- **Animations** - Animation controls
- **Viewport** - Responsive design

### Page-by-Page Results

Click **View Detailed Results**:

| Page | Issues | Fixed | Remaining | Score |
|------|--------|-------|-----------|-------|
| Homepage | 12 | 11 | 1 | 92% |
| About | 8 | 8 | 0 | 100% |
| Blog | 15 | 13 | 2 | 87% |

### Issue Details

Click issue to see:
- **WCAG Criterion** - Which rule violated
- **Severity** - Critical, major, minor
- **Location** - Which element
- **Description** - What's wrong
- **How to Fix** - Manual fix guidance
- **Fixer Applied** - Which auto-fixer ran
- **Status** - Success or failure reason

## Step 5: Apply Auto-Fixes

### Auto-Fixes Already Applied

Progress popup showed fixes as they ran:
- Real-time detection
- Automatic application
- No manual action needed

### Re-Run Auto-Fixes

On specific page:

1. Go to page details
2. Click **Re-Run Auto-Fixers**
3. Selects latest applicable fixers
4. Applies updated versions
5. Shows updated results

### Run Specific Fixer

Fix specific issue:

1. Click issue detail
2. Shows "Fixer Applied: [Name]"
3. Click **Retry Fixer**
4. Runs fixer again with updated settings
5. Shows success/failure

## Step 6: Manual Fixes for Remaining Issues

### Find Manual Fixes

For auto-fixable issues:

1. Issue shows **Status: Failed**
2. Click **Learn How to Fix**
3. Shows step-by-step manual fix
4. Often includes code examples

### Common Manual Fixes

**Add Missing Alt Text**
```html
<!-- Before -->
<img src="chart.png">

<!-- After -->
<img src="chart.png" alt="Monthly sales chart 2025">
```

**Fix Image Descriptions**
```html
<!-- Before -->
<img src="report.jpg" alt="report">

<!-- After -->
<img src="report.jpg" alt="Quarterly financial report showing 20% revenue growth">
```

**Add Video Captions**
```html
<!-- Before -->
<video src="demo.mp4"></video>

<!-- After -->
<video src="demo.mp4">
    <track kind="captions" src="demo.vtt" srclang="en">
</video>
```

**Add Form Labels**
```html
<!-- Before -->
<input type="email" placeholder="Email">

<!-- After -->
<label for="email">Email Address</label>
<input id="email" type="email">
```

### Prioritize Fixes

Manual fixes by priority:
1. **Critical** - Blocks access
2. **Major** - Significantly reduces usability
3. **Minor** - Minor accessibility improvement

## Step 7: Generate Report

### Export Report

1. Click **Export Report**
2. Choose format:
   - **PDF** - Professional report
   - **CSV** - Spreadsheet data
   - **JSON** - Technical data
3. File downloads
4. Share with team

### PDF Report Contents

- Executive summary
- Overall scores
- Issue breakdown
- Page-by-page details
- Fixer coverage
- Recommendations
- Timestamp and compliance note

### Share Report

Email report to:
- Team members
- Client
- Compliance officer
- Legal team

## Step 8: Schedule Regular Scans

### Automatic Scanning

1. Go to **Settings** → **Scanning Schedule**
2. **Enable Scheduled Scans** - Toggle on
3. Choose frequency:
   - Daily (recommended)
   - Weekly
   - Monthly
   - Custom schedule
4. Choose scan time (off-peak)
5. Choose scan scope:
   - Single page (homepage)
   - Full site
   - Custom URL list
6. Save settings

### Auto-Fix on Schedule

1. Check **Auto-Apply Fixers**
2. Fixers run automatically
3. Results saved
4. Email notification sent
5. Dashboard updated

### Notifications

Configure who gets notified:
1. Go to **Settings** → **Notifications**
2. Add email addresses
3. Choose notification types:
   - Scan completed
   - Issues found
   - All fixes applied
   - Manual review needed
4. Save settings

## Step 9: Trending Over Time

### Track Improvement

View accessibility trends:
1. Go to **Reports** → **Trending**
2. See accessibility score over time
3. Chart shows improvement/decline
4. Identify regression issues

### Before/After

Compare specific scans:
1. Go to **Reports** → **Compare**
2. Select two scan dates
3. Shows what changed
4. New issues vs fixed issues
5. Category improvements

## Step 10: Test in Browser

### Manual Testing

While scanner runs automatically, also test:

1. **Keyboard Navigation**
   - Tab through page
   - Enter key on buttons
   - Arrow keys in menus
   - Escape closes modals

2. **Screen Reader**
   - Use NVDA (Windows) or JAWS
   - VoiceOver (Mac)
   - Test page flow
   - Check landmark navigation

3. **Zoom & Scale**
   - Zoom to 200%
   - Content still readable
   - Layout doesn't break
   - All buttons accessible

4. **Color Contrast**
   - Use color contrast analyzer
   - Check all text on backgrounds
   - Verify 4.5:1 ratio for normal text
   - 3:1 for large text

## Troubleshooting

### Scan Won't Start

**Solutions:**
- Check module enabled
- Verify site URL accessible
- Check PHP timeout settings
- Clear WordPress cache

### Scan Stops Mid-Way

**Solutions:**
- Increase PHP memory limit
- Increase max execution time
- Run partial scans
- Check server logs

### Fixers Not Applying

**Solutions:**
- Refresh page
- Clear browser cache
- Try running again
- Check console errors
- Disable conflicting plugins

### Inaccurate Results

**Solutions:**
- JavaScript-heavy pages scan worse
- Single Page Apps need config
- Dynamic content may not scan
- Run manual tests too

## Best Practices

1. **Scan Regularly** - Weekly minimum
2. **Test Manually** - Scanner misses some issues
3. **Document Fixes** - Track manual changes
4. **Team Training** - Educate on accessibility
5. **Prioritize Fixes** - Critical first
6. **Monitor Trends** - Track score over time
7. **Set Targets** - Aim for 95%+ score
8. **Celebrate Progress** - Acknowledge improvements

## Next Steps

1. Run first full site scan
2. Review results
3. Schedule regular scans
4. Fix high-priority issues
5. Test manually
6. Monitor over time

## Related Articles

- [Understanding Scanner Reports](04-interpret-reports.md)
- [Manual Accessibility Fixes](05-manual-fixes.md)
- [WCAG Compliance Guide](../Advanced/03-wcag-compliance.md)
