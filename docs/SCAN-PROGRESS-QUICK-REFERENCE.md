# Scan Progress Modal - Quick Reference

## For Developers

### Basic Usage

```javascript
// Start a full site scan
SLOSScanProgress.start({
    onComplete: function(results) {
        console.log('Scan completed!', results);
        // results is an array of:
        // { post_id, title, issues, critical, score }
    }
});
```

### Configuration Options

```javascript
// Override default config
SLOSScanProgress.config = {
    animationDuration: 300,    // Modal animation speed (ms)
    scrollBehavior: 'smooth',  // Scroll behavior
    maxVisiblePages: 10,       // Max pages shown in list
    pollingInterval: 100,      // Update frequency (ms)
    announceDelay: 500,        // Screen reader delay (ms)
    ajaxTimeout: 30000,        // Request timeout (ms)
    maxRetries: 2,             // Failed request retries
    retryDelay: 1000,          // Retry delay (ms)
    batchSize: 3               // Parallel scan requests
};
```

### Methods

#### start(options)
Start a full site scan with optional callbacks.

```javascript
SLOSScanProgress.start({
    onComplete: function(results) {
        // Called when scan finishes
    }
});
```

#### cancel()
Cancel the current scan.

```javascript
SLOSScanProgress.cancel();
```

#### close()
Close the modal (auto-cancels if scanning).

```javascript
SLOSScanProgress.close();
```

### Events

Listen to scan events:

```javascript
// Manual event listening (if needed)
$(document).on('slos-scan-started', function() {
    console.log('Scan started');
});

$(document).on('slos-scan-completed', function(e, results) {
    console.log('Scan completed', results);
});

$(document).on('slos-scan-cancelled', function() {
    console.log('Scan cancelled');
});
```

### State Access

Access current scan state:

```javascript
// Check if scanning
if (SLOSScanProgress.state.isScanning) {
    console.log('Currently scanning...');
}

// Get statistics
console.log('Scanned:', SLOSScanProgress.state.scannedPages);
console.log('Total Issues:', SLOSScanProgress.state.totalIssues);
console.log('Critical:', SLOSScanProgress.state.criticalIssues);

// Get results
console.log('Results:', SLOSScanProgress.state.scanResults);
```

### Customization

#### Custom Styling

Override CSS variables in your theme:

```css
.slos-scan-modal {
    --slos-surface-secondary: #your-color;
    --slos-border-primary: #your-color;
    --slos-text-primary: #your-color;
    /* See slos-scan-progress.css for all variables */
}
```

#### Custom Status Icons

Modify the `getStatusIcon()` method:

```javascript
SLOSScanProgress.getStatusIcon = function(status) {
    const icons = {
        pending: '<span class="custom-icon"></span>',
        scanning: '<span class="custom-icon"></span>',
        // etc.
    };
    return icons[status] || icons.pending;
};
```

### Integration with Other Features

#### Trigger from Custom Button

```javascript
$('#my-custom-scan-button').on('click', function() {
    SLOSScanProgress.start({
        onComplete: function(results) {
            // Custom handling
            myCustomResultsDisplay(results);
        }
    });
});
```

#### Programmatic Scan

```javascript
// Start scan programmatically
function runScheduledScan() {
    SLOSScanProgress.start({
        onComplete: function(results) {
            // Send results to server
            $.post(ajaxurl, {
                action: 'save_scan_history',
                results: results
            });
        }
    });
}
```

### WordPress Integration

#### Add to Admin Menu

```php
add_action('admin_menu', function() {
    add_menu_page(
        'Quick Scan',
        'Quick Scan',
        'manage_options',
        'quick-scan',
        function() {
            ?>
            <div class="wrap">
                <h1>Quick Accessibility Scan</h1>
                <button type="button" class="button button-primary" 
                        onclick="SLOSScanProgress.start()">
                    Start Scan
                </button>
            </div>
            <?php
        }
    );
});
```

#### Enqueue in Custom Pages

```php
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'my-custom-page') {
        wp_enqueue_style('slos-scan-progress');
        wp_enqueue_script('slos-scan-progress');
    }
});
```

### AJAX Endpoint Reference

#### Get Posts to Scan
```
Action: slos_get_posts_to_scan
Nonce: slos_scanner_nonce
Response: [{id, title}, ...]
```

#### Scan Single Post
```
Action: slos_scan_single_post
Nonce: slos_scanner_nonce
Data: {post_id}
Response: {issues_count, critical_count, score, ...}
```

#### Consolidate Results
```
Action: slos_consolidate_scan_results
Nonce: slos_scanner_nonce
Response: {message}
```

### Accessibility Features

- **Keyboard Navigation:** Full keyboard support
- **Screen Readers:** ARIA live regions announce updates
- **Focus Management:** Focus trapped in modal
- **ESC Key:** Closes modal (with confirmation if scanning)
- **Status Messages:** Real-time announcements

### Troubleshooting

#### Modal Not Showing

```javascript
// Check if initialized
if (typeof SLOSScanProgress === 'undefined') {
    console.error('SLOSScanProgress not loaded');
}

// Manually initialize
SLOSScanProgress.init();
```

#### Scan Not Starting

```javascript
// Check AJAX configuration
console.log('Ajax URL:', ajaxurl);
console.log('Nonce:', slosScanner.nonce);

// Check permissions
// User must have 'manage_options' capability
```

#### Performance Issues

```javascript
// Reduce batch size for slower servers
SLOSScanProgress.config.batchSize = 1;

// Increase timeout for slow pages
SLOSScanProgress.config.ajaxTimeout = 60000; // 60 seconds
```

### Example: Complete Custom Implementation

```javascript
// Custom scan with all options
jQuery(document).ready(function($) {
    $('#custom-scan-button').on('click', function() {
        // Configure
        SLOSScanProgress.config.batchSize = 5;
        SLOSScanProgress.config.maxVisiblePages = 20;
        
        // Start with callbacks
        SLOSScanProgress.start({
            onComplete: function(results) {
                // Filter critical issues
                const critical = results.filter(r => r.critical > 0);
                
                // Log results
                console.log('Total pages:', results.length);
                console.log('Pages with critical:', critical.length);
                
                // Display custom notification
                alert(
                    'Scan complete!\n' +
                    'Total issues: ' + 
                    results.reduce((sum, r) => sum + r.issues, 0)
                );
                
                // Optionally close modal after 3 seconds
                setTimeout(function() {
                    SLOSScanProgress.close();
                }, 3000);
            }
        });
    });
});
```

---

## Files Reference

**CSS:** `assets/css/slos-scan-progress.css`  
**JS:** `assets/js/slos-scan-progress.js`  
**PHP:** `includes/Modules/AccessibilityScanner/Admin/ScannerPage.php`

## Support

For issues or questions, refer to:
- Main documentation: `docs/SCAN-AUDIT-REPORT.md`
- Code comments in source files
- WordPress Codex for AJAX patterns
