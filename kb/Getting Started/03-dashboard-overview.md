# Dashboard Overview

## Main Dashboard

The SLOS Dashboard provides a complete overview of your compliance status.

Access via: **WordPress Admin** → **SLOS** → **Dashboard**

## Dashboard Sections

### Compliance Score Card
- **Overall Compliance Score** - Multi-dimensional compliance readiness (0-100%)
- **Dimensions Breakdown:**
  - Cookies Scanning (25%)
  - Legal Docs (25%)
  - Geo Rules (15%)
  - Consent Metadata (15%)
  - Scanning Freshness (10%)
  - Banner Config (10%)
- **Score Trend** - Historical compliance graph
- **Action Items** - Top recommendations to improve score

⚠️ *Disclaimer: Score measures configuration readiness, not legal compliance. Consult legal counsel.*

### Quick Stats
- **Consent Records** - Total consents collected
- **Cookies Scanned** - Number of cookies detected
- **Pages Scanned** - Accessibility scanning coverage
- **Open DSR Requests** - Data subject rights pending

### Module Status
- **Consent Management** - Enabled/Disabled, status
- **Accessibility Scanner** - Enabled/Disabled, status
- **Legal Documents** - Enabled/Disabled, status
- **DSR Portal** - Enabled/Disabled, status

### Recent Activity
- Last compliance scan
- Recent consent changes
- Latest DSR requests
- Document generation history

### Alerts & Notifications
- ⚠️ Warnings (config incomplete, expiring items)
- ℹ️ Information (tips, feature announcements)
- ✓ Successes (completed scans, approved requests)

## Navigation

From the dashboard, quick-access buttons lead to:
- **Consent Manager** - Manage cookie settings
- **Accessibility Scanner** - Run accessibility scans
- **Document Generator** - Create legal documents
- **DSR Portal** - View data requests
- **Settings** - Configure modules

## Customization

### Dashboard Widgets
- Toggle widgets on/off via **SLOS** → **Settings** → **Dashboard**
- Reorder widgets by dragging
- Collapse/expand individual sections

### Export Reports
- Click **Export Report** to download dashboard data as PDF
- Includes compliance scores, charts, and recommendations

## Real-Time Updates

Dashboard auto-refreshes every 60 seconds. Manual refresh:
- Click **Refresh** button in top right
- Or press F5 in browser

## Mobile View

Dashboard is fully responsive and works on mobile devices with touch-optimized controls.

## Permissions

Only users with `manage_options` capability (Administrators) can access the dashboard.
