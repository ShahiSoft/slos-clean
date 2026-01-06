# PHASE 0: MODAL SYSTEMS DOCUMENTATION
## Accessibility Scanner Module - 5 Modal Systems

**Document Version:** 1.0  
**Created:** January 6, 2026  
**Purpose:** Complete documentation of modal trigger scenarios and interactions  
**Status:** Foundation & Preparation Phase

---

## 🎯 MODAL ARCHITECTURE OVERVIEW

### Modal System Design Principles
All modals follow consistent patterns:
1. **Event Delegation:** Triggers use `$(document).on()` for dynamic elements
2. **Global Objects:** Modals exposed as `window.ModalName` for programmatic access
3. **State Management:** Modal state tracked to prevent conflicts
4. **Proper Cleanup:** ESC key, close button, and background click handlers
5. **Accessibility:** Keyboard navigation, focus management, ARIA attributes

### Modal Interaction Matrix

| Modal | Opens By | Closes By | Can Overlap | AJAX Handlers Used |
|-------|----------|-----------|-------------|--------------------|
| Scan Progress | Button click | Auto/Manual | No | `slos_get_posts_to_scan`, `slos_scan_single_post`, `slos_consolidate_scan_results` |
| Auto-Fix Progress | Button click | Auto/Manual | No | `slos_fix_all_issues`, `slos_check_backup_exists` |
| Scan Details | Button click | Manual | Yes | `slos_get_page_issues` |
| Fix Results | Auto (after fix) | Manual | Yes | None (display only) |
| History Comparison | Button click | Manual | Yes | None (uses stored history) |

---

## 📋 MODAL 1: SCAN PROGRESS MODAL

### Overview
**File:** `assets/js/slos-scan-progress.js`  
**Global Object:** `window.SLOSScanProgress`  
**Purpose:** Display real-time progress during full site scans  
**Status:** ✅ Fully Functional

### Trigger Scenarios

#### Scenario 1.1: Full Site Scan
**Trigger Element:** `.slos-start-scan` button  
**Location:** Tools tab, Content Scanner card  
**Event:** Click  

**JavaScript:**
```javascript
$(document).on('click', '.slos-start-scan', function(e) {
    e.preventDefault();
    window.SLOSScanProgress.show();
});
```

**Flow:**
1. User clicks "Start Full Scan" button
2. Modal opens with "Preparing scan..." message
3. AJAX call to `slos_get_posts_to_scan` fetches post list
4. For each post:
   - AJAX call to `slos_scan_single_post`
   - Progress bar updates: `updateProgress(current, total)`
   - Page status added to log: `addPageStatus(pageId, 'scanning')`
5. On completion:
   - AJAX call to `slos_consolidate_scan_results`
   - Success message displayed
   - Modal closes after 2 seconds (or user clicks "Close")
6. Page refreshes to show new results

#### Scenario 1.2: Quick Scan
**Trigger Element:** `.slos-quick-scan` button  
**Location:** Tools tab, Content Scanner card  
**Event:** Click  

**JavaScript:**
```javascript
$(document).on('click', '.slos-quick-scan', function(e) {
    e.preventDefault();
    window.SLOSScanProgress.show({
        quick: true,
        limit: 10
    });
});
```

**Flow:**
1. User clicks "Quick Scan (10 Recent)"
2. Modal opens with "Quick scan starting..."
3. Fetches only 10 most recent posts
4. Scans proceed as in Scenario 1.1 but faster
5. Results displayed, modal closes

#### Scenario 1.3: Scan Cancellation
**Trigger Element:** `.slos-cancel-scan` button inside modal  
**Location:** Scan Progress Modal  
**Event:** Click  

**JavaScript:**
```javascript
$(document).on('click', '.slos-cancel-scan', function(e) {
    e.preventDefault();
    window.SLOSScanProgress.cancel();
});
```

**Flow:**
1. User clicks "Cancel" button during scan
2. Scan loop interrupted (flag set: `scanCancelled = true`)
3. Current scans complete, new scans prevented
4. Modal displays "Scan cancelled by user"
5. Partial results saved
6. Modal closes after 3 seconds

### Modal Structure

**HTML (Dynamic):**
```html
<div id="slos-scan-progress-modal" class="slos-modal" style="display:none;">
    <div class="slos-modal-overlay"></div>
    <div class="slos-modal-content">
        <div class="slos-modal-header">
            <h3>Scanning Site...</h3>
            <button class="slos-modal-close">&times;</button>
        </div>
        <div class="slos-modal-body">
            <div class="slos-progress-container">
                <div class="slos-progress-bar">
                    <div class="slos-progress-fill" style="width: 0%;"></div>
                </div>
                <div class="slos-progress-text">0 / 0 pages scanned</div>
            </div>
            <div class="slos-scan-log">
                <!-- Dynamically populated with page statuses -->
            </div>
        </div>
        <div class="slos-modal-footer">
            <button class="slos-cancel-scan">Cancel</button>
        </div>
    </div>
</div>
```

### Global Object API

**Methods:**
```javascript
window.SLOSScanProgress = {
    show: function(options) {
        // options: { quick: boolean, limit: number }
        // Opens modal and starts scan
    },
    
    hide: function() {
        // Closes modal and cleans up
    },
    
    updateProgress: function(current, total) {
        // Updates progress bar and text
    },
    
    addPageStatus: function(pageId, status, title) {
        // status: 'scanning' | 'complete' | 'error'
        // Adds page to scan log with icon
    },
    
    cancel: function() {
        // Cancels ongoing scan
    },
    
    isOpen: function() {
        // Returns boolean: is modal currently open?
    }
};
```

### Close Triggers

| Trigger | Action | Code Location |
|---------|--------|---------------|
| Close button (×) | Manual close | `.slos-modal-close` click handler |
| ESC key | Manual close | `$(document).keyup(e => e.key === 'Escape')` |
| Background click | Manual close | `.slos-modal-overlay` click handler |
| Scan completion | Auto-close after 2s | `setTimeout(() => this.hide(), 2000)` |
| Scan cancellation | Auto-close after 3s | `setTimeout(() => this.hide(), 3000)` |

### Conflict Prevention
- Only one scan can run at a time
- Checks `isOpen()` before showing
- Disables scan buttons while modal open

---

## 📋 MODAL 2: AUTO-FIX PROGRESS MODAL

### Overview
**File:** `assets/js/slos-autofix-progress.js`  
**Global Object:** `window.SLOSAutoFixProgress`  
**Purpose:** Display real-time progress during auto-fix execution  
**Status:** ✅ Fully Functional

### Trigger Scenarios

#### Scenario 2.1: Fix All Issues (Per Page)
**Trigger Element:** `.slos-fix-all-btn` button  
**Location:** Dashboard results table, Tools "Pages Requiring Attention"  
**Event:** Click  
**Data Attribute:** `data-page-id`

**JavaScript:**
```javascript
$(document).on('click', '.slos-fix-all-btn', function(e) {
    e.preventDefault();
    
    const pageId = $(this).data('page-id');
    const pageName = $(this).closest('tr').find('.page-title').text();
    
    window.SLOSAutoFixProgress.show({
        pageId: pageId,
        pageName: pageName,
        onComplete: function(results) {
            // Callback after fixes complete
            // results: { fixed: [], failed: [], manual: [] }
            showFixResultsModal(results);
        }
    });
});
```

**Flow:**
1. User clicks "Fix All" button on a page row
2. Modal opens with FixEngine branding
3. AJAX call to `slos_check_backup_exists`
4. If no backup:
   - Display "Creating backup..." message
   - Backup created automatically
5. AJAX call to `slos_fix_all_issues` with page ID
6. For each fixer:
   - Progress updates: `updateProgress(percentage, fixerName)`
   - Status shown: "Fixing: MissingAltFixer..."
7. On completion:
   - Results breakdown displayed:
     - ✅ Fixed: X issues
     - ❌ Failed: Y issues
     - 🔧 Manual: Z issues
   - Callback invoked with results
8. Page rescanned automatically
9. Modal closes or shows "View Details" button

#### Scenario 2.2: Auto-Fix Trigger (Generic)
**Trigger Element:** `.slos-autofix-trigger` button  
**Location:** Various (can be added to any page element)  
**Event:** Click  
**Data Attribute:** `data-post-id`

**JavaScript:**
```javascript
$(document).on('click', '.slos-autofix-trigger', function(e) {
    e.preventDefault();
    
    const postId = $(this).data('post-id');
    
    window.SLOSAutoFixProgress.show({
        pageId: postId,
        onComplete: function(results) {
            refreshPageData(postId);
        }
    });
});
```

**Flow:** Same as Scenario 2.1

#### Scenario 2.3: Auto-Fix from Post Editor
**Trigger Element:** `.slos-autofix-post-btn` button  
**Location:** WordPress post editor (if integrated)  
**Event:** Click  

**JavaScript:**
```javascript
$(document).on('click', '.slos-autofix-post-btn', function(e) {
    e.preventDefault();
    
    const postId = wp.data.select('core/editor').getCurrentPostId();
    
    window.SLOSAutoFixProgress.show({
        pageId: postId,
        onComplete: function(results) {
            // Show notification in editor
            wp.data.dispatch('core/notices').createNotice(
                'success',
                'Auto-fix complete: ' + results.fixed.length + ' issues fixed'
            );
        }
    });
});
```

**Flow:** Same as Scenario 2.1 but in Gutenberg editor context

### Modal Structure

**HTML (Dynamic):**
```html
<div id="slos-autofix-progress-modal" class="slos-modal" style="display:none;">
    <div class="slos-modal-overlay"></div>
    <div class="slos-modal-content slos-fixengine-modal">
        <div class="slos-modal-header">
            <div class="fixengine-logo">
                <span class="logo-icon">🔧</span>
                <h3>FixEngine Pro</h3>
            </div>
            <button class="slos-modal-close">&times;</button>
        </div>
        <div class="slos-modal-body">
            <div class="slos-page-info">
                <strong>Fixing:</strong> <span class="page-name"></span>
            </div>
            <div class="slos-progress-container">
                <div class="slos-progress-bar slos-animated">
                    <div class="slos-progress-fill" style="width: 0%;"></div>
                </div>
                <div class="slos-progress-text">Initializing...</div>
            </div>
            <div class="slos-fixer-status">
                <!-- Current fixer name displayed here -->
            </div>
            <div class="slos-results-breakdown" style="display:none;">
                <div class="result-item success">
                    <span class="result-icon">✅</span>
                    <span class="result-label">Fixed:</span>
                    <span class="result-count">0</span>
                </div>
                <div class="result-item error">
                    <span class="result-icon">❌</span>
                    <span class="result-label">Failed:</span>
                    <span class="result-count">0</span>
                </div>
                <div class="result-item manual">
                    <span class="result-icon">🔧</span>
                    <span class="result-label">Manual:</span>
                    <span class="result-count">0</span>
                </div>
            </div>
        </div>
        <div class="slos-modal-footer">
            <!-- Footer content varies by state -->
        </div>
    </div>
</div>
```

### Global Object API

**Methods:**
```javascript
window.SLOSAutoFixProgress = {
    show: function(options) {
        // options: { pageId: number, pageName: string, onComplete: function }
        // Opens modal and starts auto-fix
    },
    
    hide: function() {
        // Closes modal and cleans up
    },
    
    updateProgress: function(percentage, status) {
        // percentage: 0-100
        // status: Current fixer name or status message
    },
    
    showResults: function(data) {
        // data: { fixed: [], failed: [], manual: [] }
        // Displays final results breakdown
    },
    
    isOpen: function() {
        // Returns boolean
    }
};
```

### Configuration Object

**Global:** `window.slosautoFixConfig`

```javascript
var slosautoFixConfig = {
    ajax_url: ajaxurl,
    nonce: '<?php echo wp_create_nonce("slos_autofix_nonce"); ?>',
    strings: {
        creating_backup: 'Creating backup...',
        backup_complete: 'Backup created successfully',
        fixing: 'Fixing issues...',
        complete: 'Auto-fix complete!',
        error: 'An error occurred'
    }
};
```

### Close Triggers

| Trigger | Action | Code Location |
|---------|--------|---------------|
| Close button (×) | Manual close | `.slos-modal-close` click handler |
| ESC key | Disabled during fix | Prevented when `isFixing === true` |
| Background click | Disabled during fix | Prevented when `isFixing === true` |
| Fix completion | Shows "Close" button | User must manually close |

### Conflict Prevention
- Cannot be interrupted once started (ESC disabled)
- Only one auto-fix can run at a time
- BackupService prevents concurrent backups

---

## 📋 MODAL 3: SCAN DETAILS MODAL

### Overview
**File:** Inline in `templates/admin/accessibility-dashboard.php`  
**Modal ID:** `#slos-scan-details-modal`  
**Purpose:** Display detailed issues for a specific page  
**Status:** ✅ Fully Functional

### Trigger Scenarios

#### Scenario 3.1: View Details from Dashboard
**Trigger Element:** `.slos-view-details-btn` button  
**Location:** Dashboard "Scan Results Overview" table  
**Event:** Click  
**Data Attribute:** `data-post-id`

**JavaScript (in slos-scanner-admin.js):**
```javascript
$(document).on('click', '.slos-view-details-btn', function(e) {
    e.preventDefault();
    
    const postId = $(this).data('post-id');
    const postTitle = $(this).closest('tr').find('.page-title strong').text();
    
    openScanDetailsModal(postId, postTitle);
});

function openScanDetailsModal(postId, title) {
    const $modal = $('#slos-scan-details-modal');
    
    // Set title
    $modal.find('.modal-page-title').text(title);
    
    // Show loading
    $modal.find('.modal-body').html('<div class="loading">Loading issues...</div>');
    
    // Open modal
    $modal.fadeIn(200);
    
    // Fetch issues
    $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'slos_get_page_issues',
            nonce: slosScanner.nonce,
            post_id: postId
        },
        success: function(response) {
            if (response.success) {
                populateScanDetails(response.data.issues);
            } else {
                $modal.find('.modal-body').html('<div class="error">' + response.data + '</div>');
            }
        }
    });
}

function populateScanDetails(issues) {
    const $modal = $('#slos-scan-details-modal');
    const $body = $modal.find('.modal-body');
    
    // Group issues by severity
    const grouped = {
        critical: issues.filter(i => i.severity === 'critical'),
        major: issues.filter(i => i.severity === 'major'),
        minor: issues.filter(i => i.severity === 'minor')
    };
    
    let html = '';
    
    ['critical', 'major', 'minor'].forEach(severity => {
        if (grouped[severity].length > 0) {
            html += '<div class="issue-group ' + severity + '">';
            html += '<h4>' + severity.toUpperCase() + ' (' + grouped[severity].length + ')</h4>';
            html += '<ul class="issue-list">';
            
            grouped[severity].forEach(issue => {
                html += '<li class="issue-item">';
                html += '<div class="issue-header">';
                html += '<span class="issue-type">' + issue.type + '</span>';
                html += '<span class="issue-guideline">' + issue.guideline + '</span>';
                html += '</div>';
                html += '<div class="issue-message">' + issue.message + '</div>';
                if (issue.recommendation) {
                    html += '<div class="issue-recommendation">💡 ' + issue.recommendation + '</div>';
                }
                html += '</li>';
            });
            
            html += '</ul></div>';
        }
    });
    
    if (html === '') {
        html = '<div class="no-issues">✅ No issues found on this page!</div>';
    }
    
    $body.html(html);
}
```

**Flow:**
1. User clicks "View Details" button in scan results table
2. Modal opens with loading spinner
3. AJAX call to `slos_get_page_issues` with post ID
4. Issues grouped by severity (Critical/Major/Minor)
5. Each issue displayed with:
   - Issue type
   - WCAG guideline reference
   - Issue message/description
   - Fix recommendation (if available)
6. User can scroll through issues
7. User closes modal manually

### Modal Structure (Template)

**HTML (in accessibility-dashboard.php):**
```html
<div id="slos-scan-details-modal" class="slos-modal" style="display:none;">
    <div class="slos-modal-overlay"></div>
    <div class="slos-modal-content slos-wide-modal">
        <div class="slos-modal-header">
            <h3>
                <span class="dashicons dashicons-visibility"></span>
                Scan Details: <span class="modal-page-title"></span>
            </h3>
            <button class="slos-modal-close">&times;</button>
        </div>
        <div class="slos-modal-body">
            <!-- Content dynamically populated via JS -->
        </div>
        <div class="slos-modal-footer">
            <a href="#" class="slos-btn-secondary edit-page-link" target="_blank">
                <span class="dashicons dashicons-edit"></span>
                Edit Page
            </a>
            <button type="button" class="slos-btn-primary slos-modal-close">
                Close
            </button>
        </div>
    </div>
</div>
```

### Close Triggers

| Trigger | Action | Code Location |
|---------|--------|---------------|
| Close button (×) | Manual close | `.slos-modal-close` click handler |
| ESC key | Manual close | `$(document).keyup()` handler |
| Background click | Manual close | `.slos-modal-overlay` click handler |
| Close button (footer) | Manual close | `.slos-modal-close` in footer |

### Conflict Prevention
- Can overlap with Fix Results modal (both can be open)
- Z-index management prevents stacking issues

---

## 📋 MODAL 4: FIX RESULTS MODAL

### Overview
**File:** Function in `assets/js/slos-scanner-admin.js`  
**Function:** `showScannerNotification(message, type, guidance, fixed, failed)`  
**Purpose:** Display results after auto-fix completion  
**Status:** ✅ Fully Functional

### Trigger Scenarios

#### Scenario 4.1: After Auto-Fix Completes
**Trigger:** Callback from Auto-Fix Progress Modal  
**Event:** Auto-Fix completion

**JavaScript:**
```javascript
// Called from Auto-Fix Progress Modal onComplete callback
window.SLOSAutoFixProgress.show({
    pageId: 123,
    onComplete: function(results) {
        showScannerNotification(
            'Auto-fix complete!',
            'success',
            results.guidance || [],
            results.fixed || [],
            results.failed || []
        );
    }
});

function showScannerNotification(message, type, guidance, fixed, failed) {
    // type: 'success' | 'error' | 'warning' | 'info'
    
    let html = '<div class="slos-notification-modal slos-notification-' + type + '">';
    html += '<div class="notification-icon">';
    
    switch(type) {
        case 'success':
            html += '✅';
            break;
        case 'error':
            html += '❌';
            break;
        case 'warning':
            html += '⚠️';
            break;
        case 'info':
            html += 'ℹ️';
            break;
    }
    
    html += '</div>';
    html += '<div class="notification-content">';
    html += '<h3>' + message + '</h3>';
    
    // Fixed issues
    if (fixed && fixed.length > 0) {
        html += '<div class="results-section success">';
        html += '<h4>✅ Fixed Issues (' + fixed.length + ')</h4>';
        html += '<ul>';
        fixed.forEach(function(issue) {
            html += '<li>' + issue.message + '</li>';
        });
        html += '</ul></div>';
    }
    
    // Failed issues
    if (failed && failed.length > 0) {
        html += '<div class="results-section error">';
        html += '<h4>❌ Failed Fixes (' + failed.length + ')</h4>';
        html += '<ul>';
        failed.forEach(function(issue) {
            html += '<li>' + issue.message;
            if (issue.error) {
                html += '<br><span class="error-detail">' + issue.error + '</span>';
            }
            html += '</li>';
        });
        html += '</ul></div>';
    }
    
    // Manual fix guidance
    if (guidance && guidance.length > 0) {
        html += '<div class="results-section manual">';
        html += '<h4>🔧 Manual Fixes Required (' + guidance.length + ')</h4>';
        html += '<ul>';
        guidance.forEach(function(item) {
            html += '<li><strong>' + item.issue + '</strong><br>';
            html += item.steps.join('<br>');
            html += '</li>';
        });
        html += '</ul></div>';
    }
    
    html += '</div>';
    html += '<div class="notification-actions">';
    html += '<button class="slos-btn-primary close-notification">Close</button>';
    html += '</div>';
    html += '</div>';
    
    // Create modal wrapper
    const $modal = $('<div id="slos-fix-results-modal" class="slos-modal"></div>');
    $modal.html('<div class="slos-modal-overlay"></div>' + html);
    
    // Append to body
    $('body').append($modal);
    
    // Show modal
    $modal.fadeIn(200);
    
    // Close handler
    $modal.on('click', '.close-notification, .slos-modal-overlay', function() {
        $modal.fadeOut(200, function() {
            $modal.remove();
        });
    });
}
```

**Flow:**
1. Auto-fix completes in Auto-Fix Progress Modal
2. `onComplete` callback invoked with results
3. `showScannerNotification()` called with categorized results
4. Modal dynamically created and appended to DOM
5. Displays three sections:
   - ✅ Fixed Issues (green)
   - ❌ Failed Fixes (red, with error details)
   - 🔧 Manual Fixes Required (yellow, with step-by-step guidance)
6. User reviews results
7. User clicks "Close" button
8. Modal fades out and removes from DOM

### Modal Structure (Dynamic)

**HTML (Generated):**
```html
<div id="slos-fix-results-modal" class="slos-modal">
    <div class="slos-modal-overlay"></div>
    <div class="slos-notification-modal slos-notification-success">
        <div class="notification-icon">✅</div>
        <div class="notification-content">
            <h3>Auto-fix complete!</h3>
            
            <div class="results-section success">
                <h4>✅ Fixed Issues (5)</h4>
                <ul>
                    <li>Missing alt text added to 3 images</li>
                    <li>Empty links removed (2 instances)</li>
                </ul>
            </div>
            
            <div class="results-section error">
                <h4>❌ Failed Fixes (1)</h4>
                <ul>
                    <li>Color contrast adjustment failed
                        <br><span class="error-detail">Unable to determine text color</span>
                    </li>
                </ul>
            </div>
            
            <div class="results-section manual">
                <h4>🔧 Manual Fixes Required (2)</h4>
                <ul>
                    <li><strong>Heading hierarchy broken</strong><br>
                        1. Review heading structure<br>
                        2. Ensure no heading levels are skipped<br>
                        3. Use sequential h1 → h2 → h3 order
                    </li>
                </ul>
            </div>
        </div>
        <div class="notification-actions">
            <button class="slos-btn-primary close-notification">Close</button>
        </div>
    </div>
</div>
```

### Close Triggers

| Trigger | Action | Code Location |
|---------|--------|---------------|
| Close button | Manual close | `.close-notification` click handler |
| Background click | Manual close | `.slos-modal-overlay` click handler |
| ESC key | Manual close | Can be added if needed |

### Conflict Prevention
- Modal is dynamically created on-demand
- Removed from DOM after closing (no persistent element)
- Can appear while other modals open (but typically after they close)

---

## 📋 MODAL 5: HISTORY COMPARISON MODAL

### Overview
**File:** Inline in `templates/admin/accessibility-dashboard.php`  
**Modal ID:** `#slos-scan-modal`  
**Purpose:** Compare two scan results for trend analysis  
**Status:** ✅ Fully Functional

### Trigger Scenarios

#### Scenario 5.1: Compare Scans from History
**Trigger Element:** `.slos-compare-scans` button  
**Location:** Dashboard "Scan History & Trends" table  
**Event:** Click  
**Data Attributes:** `data-scan-1`, `data-scan-2`

**JavaScript (in dashboard template):**
```javascript
$(document).on('click', '.slos-compare-scans', function(e) {
    e.preventDefault();
    
    const scan1Id = $(this).data('scan-1');
    const scan2Id = $(this).data('scan-2');
    
    openComparisonModal(scan1Id, scan2Id);
});

function openComparisonModal(scan1Id, scan2Id) {
    // Retrieve scan data from stored history
    const scanHistory = <?php echo json_encode(get_option('slos_accessibility_scan_history', [])); ?>;
    
    const scan1 = scanHistory.find(s => s.id === scan1Id);
    const scan2 = scanHistory.find(s => s.id === scan2Id);
    
    if (!scan1 || !scan2) {
        alert('Scan data not found');
        return;
    }
    
    const $modal = $('#slos-scan-modal');
    
    // Populate comparison data
    const scoreDiff = scan2.score - scan1.score;
    const issuesDiff = scan2.total_issues - scan1.total_issues;
    
    $modal.find('.scan1-date').text(formatDate(scan1.date));
    $modal.find('.scan1-score').text(scan1.score + '%');
    $modal.find('.scan1-issues').text(scan1.total_issues);
    
    $modal.find('.scan2-date').text(formatDate(scan2.date));
    $modal.find('.scan2-score').text(scan2.score + '%');
    $modal.find('.scan2-issues').text(scan2.total_issues);
    
    // Show trend indicators
    if (scoreDiff > 0) {
        $modal.find('.score-trend').html('<span class="improvement">↑ +' + scoreDiff + '%</span>');
    } else if (scoreDiff < 0) {
        $modal.find('.score-trend').html('<span class="regression">↓ ' + scoreDiff + '%</span>');
    } else {
        $modal.find('.score-trend').html('<span class="neutral">→ No change</span>');
    }
    
    if (issuesDiff < 0) {
        $modal.find('.issues-trend').html('<span class="improvement">↓ ' + Math.abs(issuesDiff) + ' fewer issues</span>');
    } else if (issuesDiff > 0) {
        $modal.find('.issues-trend').html('<span class="regression">↑ +' + issuesDiff + ' more issues</span>');
    } else {
        $modal.find('.issues-trend').html('<span class="neutral">→ Same</span>');
    }
    
    // Open modal
    $modal.fadeIn(200);
}
```

**Flow:**
1. User clicks "Compare" button in scan history table
2. Button has `data-scan-1` (older) and `data-scan-2` (newer) attributes
3. Scan data retrieved from `slos_accessibility_scan_history` option
4. Modal populated with comparison:
   - Scan 1 date, score, total issues
   - Scan 2 date, score, total issues
   - Score trend (improved ↑, regressed ↓, neutral →)
   - Issues trend (fewer/more/same)
5. User reviews comparison
6. User closes modal

### Modal Structure (Template)

**HTML (in accessibility-dashboard.php):**
```html
<div id="slos-scan-modal" class="slos-modal" style="display:none;">
    <div class="slos-modal-overlay"></div>
    <div class="slos-modal-content slos-comparison-modal">
        <div class="slos-modal-header">
            <h3>
                <span class="dashicons dashicons-chart-line"></span>
                Scan Comparison
            </h3>
            <button class="slos-modal-close">&times;</button>
        </div>
        <div class="slos-modal-body">
            <div class="comparison-grid">
                <div class="comparison-column">
                    <h4>Previous Scan</h4>
                    <div class="scan-date scan1-date"></div>
                    <div class="scan-metric">
                        <label>Score:</label>
                        <span class="scan1-score"></span>
                    </div>
                    <div class="scan-metric">
                        <label>Total Issues:</label>
                        <span class="scan1-issues"></span>
                    </div>
                </div>
                
                <div class="comparison-separator">
                    <span class="comparison-icon">⚖️</span>
                </div>
                
                <div class="comparison-column">
                    <h4>Latest Scan</h4>
                    <div class="scan-date scan2-date"></div>
                    <div class="scan-metric">
                        <label>Score:</label>
                        <span class="scan2-score"></span>
                    </div>
                    <div class="scan-metric">
                        <label>Total Issues:</label>
                        <span class="scan2-issues"></span>
                    </div>
                </div>
            </div>
            
            <div class="comparison-trends">
                <h4>Analysis</h4>
                <div class="trend-item">
                    <label>Score Trend:</label>
                    <span class="score-trend"></span>
                </div>
                <div class="trend-item">
                    <label>Issues Trend:</label>
                    <span class="issues-trend"></span>
                </div>
            </div>
        </div>
        <div class="slos-modal-footer">
            <button type="button" class="slos-btn-primary slos-modal-close">
                Close
            </button>
        </div>
    </div>
</div>
```

### Close Triggers

| Trigger | Action | Code Location |
|---------|--------|---------------|
| Close button (×) | Manual close | `.slos-modal-close` click handler |
| ESC key | Manual close | `$(document).keyup()` handler |
| Background click | Manual close | `.slos-modal-overlay` click handler |

### Conflict Prevention
- Can be open alongside other read-only modals
- Data retrieved from stored history (no AJAX conflicts)

---

## 🔄 MODAL INTERACTION FLOWS

### Flow 1: Scan → View Details
1. User starts full scan (Modal 1 opens)
2. Scan completes, Modal 1 closes
3. User clicks "View Details" on result
4. Modal 3 opens with issue list

### Flow 2: View Details → Fix → View Results
1. User clicks "View Details" (Modal 3 opens)
2. User reviews issues, closes Modal 3
3. User clicks "Fix All" (Modal 2 opens)
4. Auto-fix runs, Modal 2 closes
5. Modal 4 opens automatically with results

### Flow 3: Fix → Rollback
1. User clicks "Fix All" (Modal 2 opens)
2. Fixes complete, Modal 2 closes
3. User unhappy with results, clicks "Rollback"
4. Confirmation dialog appears (browser native or custom)
5. Rollback executes (no modal, notification only)

### Flow 4: Compare History
1. User views scan history in Dashboard
2. User clicks "Compare" button
3. Modal 5 opens with side-by-side comparison
4. User reviews trends, closes modal

---

## 🧪 MODAL TESTING CHECKLIST

### General Modal Tests (All Modals)

| Test # | Test Case | Modal 1 | Modal 2 | Modal 3 | Modal 4 | Modal 5 |
|--------|-----------|---------|---------|---------|---------|---------|
| M.1 | Opens on trigger click | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| M.2 | Close button (×) works | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| M.3 | ESC key closes modal | 🔄 | ⚠️ No* | 🔄 | 🔄 | 🔄 |
| M.4 | Background click closes | 🔄 | ⚠️ No* | 🔄 | 🔄 | 🔄 |
| M.5 | Content scrollable if long | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| M.6 | No console errors on open | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| M.7 | Focus trapped inside modal | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |
| M.8 | ARIA attributes present | 🔄 | 🔄 | 🔄 | 🔄 | 🔄 |

**Note:** Modal 2 (Auto-Fix) disables ESC/background close during fix to prevent interruption

### Specific Modal Tests

#### Modal 1: Scan Progress
- [ ] Progress bar animates smoothly
- [ ] Page status updates in real-time
- [ ] Cancel button stops scan
- [ ] Auto-closes after completion
- [ ] Handles scan errors gracefully

#### Modal 2: Auto-Fix Progress
- [ ] Backup creation shown
- [ ] Fixer names displayed during execution
- [ ] Results breakdown accurate
- [ ] Cannot be closed during fix
- [ ] onComplete callback fires

#### Modal 3: Scan Details
- [ ] Issues grouped by severity
- [ ] WCAG guidelines displayed
- [ ] Empty state shown if no issues
- [ ] Edit link opens post editor
- [ ] Scrollable for many issues

#### Modal 4: Fix Results
- [ ] Fixed issues shown in green
- [ ] Failed fixes shown in red
- [ ] Manual guidance provided
- [ ] Modal removes from DOM on close
- [ ] Can appear after Modal 2

#### Modal 5: History Comparison
- [ ] Two scans compared correctly
- [ ] Trend indicators accurate (↑↓→)
- [ ] Dates formatted correctly
- [ ] Score difference calculated
- [ ] Issue difference calculated

---

## ✅ DOCUMENTATION COMPLETION CHECKLIST

### Modal Systems Documented
- [x] Modal 1: Scan Progress (3 scenarios)
- [x] Modal 2: Auto-Fix Progress (3 scenarios)
- [x] Modal 3: Scan Details (1 scenario)
- [x] Modal 4: Fix Results (1 scenario)
- [x] Modal 5: History Comparison (1 scenario)

### Documentation Includes
- [x] Trigger elements and selectors
- [x] Event types and data attributes
- [x] JavaScript code examples
- [x] HTML structure
- [x] Close triggers
- [x] Global object APIs
- [x] Interaction flows
- [x] Conflict prevention
- [x] Testing checklists

---

**Document Status:** ✅ COMPLETE  
**Total Modal Scenarios:** 9  
**Last Updated:** January 6, 2026
