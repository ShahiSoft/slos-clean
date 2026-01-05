# Scan Progress Modal - Visual Guide

## Modal States & Flow

### State 1: Initialization
```
┌─────────────────────────────────────────────────┐
│  🔍 Accessibility Scan Progress            [×]  │
│  Scanning your site for accessibility issues    │
├─────────────────────────────────────────────────┤
│  ▓▓░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ 5%         │
│  Fetching pages to scan...                      │
├─────────────────────────────────────────────────┤
│  Page Status                                     │
│  ┌───────────────────────────────────────────┐  │
│  │ ⊖  Home                              │     │  │
│  │ ⊖  About Us                          │     │  │
│  │ ⊖  Services                          │     │  │
│  │ ⊖  Contact                           │     │  │
│  └───────────────────────────────────────────┘  │
├─────────────────────────────────────────────────┤
│  Scanned    Issues    Critical    Clean         │
│    0          0          0          0            │
├─────────────────────────────────────────────────┤
│  [ ×  Cancel Scan ]                             │
└─────────────────────────────────────────────────┘
```

### State 2: Scanning (Active)
```
┌─────────────────────────────────────────────────┐
│  🔍 Accessibility Scan Progress            [×]  │
│  Scanning your site for accessibility issues    │
├─────────────────────────────────────────────────┤
│  ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░░░░░░░ 45%          │
│  Scanning 18 of 40 pages...                     │
├─────────────────────────────────────────────────┤
│  Page Status                                     │
│  ┌───────────────────────────────────────────┐  │
│  │ ✓  Home                        ⚠ 5  ⚡ 2  │  │
│  │ ✓  About Us                             │  │
│  │ ⟳  Services                             │  │  ← Scanning
│  │ ⊖  Contact                              │  │
│  │ ⊖  Blog                                 │  │
│  │ ⊖  Products                             │  │
│  └───────────────────────────────────────────┘  │
├─────────────────────────────────────────────────┤
│  Scanned    Issues    Critical    Clean         │
│   18         47         12          6            │
├─────────────────────────────────────────────────┤
│  [ ×  Cancel Scan ]                             │
└─────────────────────────────────────────────────┘
```

### State 3: Complete (With Issues)
```
┌─────────────────────────────────────────────────┐
│  🔍 Accessibility Scan Progress            [×]  │
│  Scanning your site for accessibility issues    │
├─────────────────────────────────────────────────┤
│  ▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓ 100%          │
│  Scan complete! Found 127 issues across 28 pgs  │
├─────────────────────────────────────────────────┤
│  Page Status                                     │
│  ┌───────────────────────────────────────────┐  │
│  │ ⚠  Home                        ⚠ 15 ⚡ 5  │  │
│  │ ✓  About Us                             │  │
│  │ ⚠  Services                    ⚠ 8  ⚡ 2  │  │
│  │ ✓  Contact                              │  │
│  │ ⚠  Blog                        ⚠ 22 ⚡ 8  │  │
│  │ ✗  Products (Error)                     │  │
│  │ + 34 more pages                          │  │
│  └───────────────────────────────────────────┘  │
├─────────────────────────────────────────────────┤
│  Scanned    Issues    Critical    Clean         │
│   40         127        35         12            │
├─────────────────────────────────────────────────┤
│  [ Dismiss ]              [ 👁 View Results ]   │
└─────────────────────────────────────────────────┘
```

## Status Icons Legend

| Icon | Status | Color | Meaning |
|------|--------|-------|---------|
| ⊖ | Pending | Gray | Waiting to be scanned |
| ⟳ | Scanning | Blue (animated) | Currently scanning |
| ✓ | Success | Green | Scanned, no issues |
| ⚠ | Warning | Yellow | Scanned, issues found |
| ✗ | Error | Red | Scan failed |

## Issue Badges

| Badge | Meaning |
|-------|---------|
| ⚠ 15 | Total issues found |
| ⚡ 5 | Critical issues (subset of total) |

## Progress Bar States

### Processing (animated shimmer)
```
▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░ 35%
```
Background animates left-to-right with gradient shimmer

### Complete
```
▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓ 100%
```
Solid fill, no animation, with glow effect

## Responsive Behavior

### Desktop (> 768px)
- Modal width: 650px
- 4-column statistics grid
- Horizontal action buttons
- Shows up to 10 pages in list

### Tablet/Mobile (< 768px)
- Modal width: 95% of viewport
- 2-column statistics grid
- Vertical action buttons
- Scrollable page list

## Color Scheme

### Background Colors
- **Modal:** #242b24 (dark green-gray)
- **Header:** #2d342d (darker green-gray)
- **List:** #1a1f1a (darkest)

### Status Colors
- **Pending:** #94a3b8 (gray)
- **Scanning:** #3b82f6 (blue)
- **Success:** #4ade80 (green)
- **Warning:** #fbbf24 (yellow)
- **Error:** #f87171 (red)

### Accent Colors
- **Primary:** #3b82f6 → #60a5fa (blue gradient)
- **Border:** rgba(107, 142, 78, 0.2)

## Animations

### Modal Entry
```
transform: scale(0.9) translateY(20px) → scale(1) translateY(0)
duration: 300ms
easing: cubic-bezier(0.34, 1.56, 0.64, 1) (back-out)
```

### Progress Bar
```
width: 0% → X%
duration: 400ms
easing: cubic-bezier(0.4, 0, 0.2, 1) (ease-in-out)
```

### Status Icon Change
```
Fade out old → Fade in new
duration: 200ms
```

### Shimmer Effect
```
background-position: -200% → 200%
duration: 2s
repeat: infinite
```

## Accessibility Features

### Keyboard Navigation
```
Tab       → Move to next focusable element
Shift+Tab → Move to previous element
Escape    → Close modal (or cancel if scanning)
Enter     → Activate focused button
```

### Focus Order
```
1. Close button (×)
2. Cancel/Dismiss button
3. View Results button (if shown)
```

### Screen Reader Announcements
```
Opening:   "Scan progress dialog opened"
Starting:  "Starting full site scan"
Progress:  "Scanning X pages"
Complete:  "Scan complete. Found X issues"
Cancelled: "Scan cancelled"
Closing:   "Scan progress dialog closed"
```

### ARIA Attributes
```html
<div role="dialog" 
     aria-modal="true" 
     aria-labelledby="slos-scan-title"
     aria-describedby="slos-scan-desc">
  <h2 id="slos-scan-title">Accessibility Scan Progress</h2>
  <div id="slos-scan-desc">Scanning your site...</div>
</div>

<div role="list" aria-label="List of pages being scanned">
  <div role="listitem">...</div>
</div>

<div aria-live="polite" aria-atomic="true">
  <!-- Dynamic updates announced here -->
</div>
```

## Real-Time Updates Flow

```
User Clicks "Start Full Scan"
        ↓
Modal Opens (fade in)
        ↓
Fetch Pages (AJAX)
        ↓
Display Page List
        ↓
┌──────────────────┐
│ Start Batch Loop │
└──────────────────┘
        ↓
┌─────────────────────────────┐
│ For Each Page (up to 3):    │
│                              │
│ 1. Change status: pending→  │
│    scanning (blue, animated) │
│                              │
│ 2. Send AJAX request         │
│                              │
│ 3. On success:               │
│    - Update status (✓/⚠/✗)  │
│    - Add issue badges        │
│    - Increment counters      │
│    - Update progress bar     │
│                              │
│ 4. Move to next page         │
└─────────────────────────────┘
        ↓
All Pages Complete?
        ↓
Consolidate Results (AJAX)
        ↓
Show "View Results" Button
        ↓
User Clicks → Reload Page
```

## Code Integration Points

### JavaScript
```javascript
// Initialize on document ready
$(document).ready(function() {
    SLOSScanProgress.init();
});

// Trigger from button
$('#slos-start-scan').on('click', function() {
    SLOSScanProgress.start();
});
```

### PHP
```php
// Enqueue assets
wp_enqueue_style('slos-scan-progress');
wp_enqueue_script('slos-scan-progress', [...], ['jquery']);

// AJAX handlers
add_action('wp_ajax_slos_get_posts_to_scan', [...]);
add_action('wp_ajax_slos_scan_single_post', [...]);
add_action('wp_ajax_slos_consolidate_scan_results', [...]);
```

---

## Tips for Customization

### Change Modal Width
```css
.slos-scan-modal {
    max-width: 800px; /* Default: 650px */
}
```

### Change Batch Size (Parallel Scans)
```javascript
SLOSScanProgress.config.batchSize = 5; // Default: 3
```

### Change Max Visible Pages
```javascript
SLOSScanProgress.config.maxVisiblePages = 20; // Default: 10
```

### Custom Status Colors
```css
.slos-scan-page-status.scanning {
    background: rgba(255, 0, 0, 0.2); /* Red instead of blue */
    color: #ff0000;
}
```

### Custom Complete Message
```javascript
// After scan completes
SLOSScanProgress.updateProgress(
    100,
    'Custom message here!'
);
```

---

**Last Updated:** January 5, 2026  
**Version:** 3.2.0
