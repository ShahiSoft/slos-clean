# Migrate from Other Consent Plugins

## Transfer Settings & Data from Existing Cookie Solutions

### Pre-Migration Assessment

#### Step 1: Analyze Current Setup
```
SLOS → Migration → Assessment → Run Analysis
```

**Current Plugin Inventory:**
```php
// Check installed consent plugins
$consent_plugins = [
    'cookie-notice' => is_plugin_active('cookie-notice/cookie-notice.php'),
    'gdpr-cookie-consent' => is_plugin_active('gdpr-cookie-consent/gdpr-cookie-consent.php'),
    'cookiebot' => is_plugin_active('cookiebot/cookiebot.php'),
    'complianz' => is_plugin_active('complianz-gdpr/complianz-gdpr.php'),
    'cookie-law-info' => is_plugin_active('cookie-law-info/cookie-law-info.php'),
    'termly' => is_plugin_active('termly/terms-and-conditions-popup.php'),
    ' iubenda' => is_plugin_active('iubenda-cookie-solution/iubenda-cookie-solution.php')
];

$active_plugins = array_filter($consent_plugins, function($active) {
    return $active;
});
```

#### Step 2: Data Export Preparation

**Cookie Settings Export:**
```php
// Export current cookie settings
function export_cookie_settings() {
    global $wpdb;

    $settings = [];

    // Cookie Notice settings
    if (is_plugin_active('cookie-notice/cookie-notice.php')) {
        $settings['cookie_notice'] = get_option('cookie_notice_options');
    }

    // GDPR Cookie Consent settings
    if (is_plugin_active('gdpr-cookie-consent/gdpr-cookie-consent.php')) {
        $settings['gdpr_cookie'] = get_option('gdpr_cookie_consent_settings');
    }

    // Complianz settings
    if (is_plugin_active('complianz-gdpr/complianz-gdpr.php')) {
        $settings['complianz'] = get_option('complianz_options');
    }

    return $settings;
}
```

**User Consent Data Export:**
```php
// Export existing consent records
function export_consent_data() {
    global $wpdb;

    $consent_data = [];

    // Cookie Notice consent logs
    if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}cookie_notice_log'")) {
        $consent_data['cookie_notice_logs'] = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}cookie_notice_log"
        );
    }

    // GDPR Cookie Consent data
    if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}gdpr_cookie_consent_log'")) {
        $consent_data['gdpr_logs'] = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}gdpr_cookie_consent_log"
        );
    }

    return $consent_data;
}
```

### Create Migration Backup

#### Step 1: Full Site Backup
```bash
# Create complete backup before migration
wp db export pre-migration-backup.sql
tar -czf pre-migration-files.tar.gz wp-content/
```

#### Step 2: Plugin-Specific Backups
```php
// Backup plugin-specific data
function create_migration_backup() {
    $backup_dir = WP_CONTENT_DIR . '/migration-backups/' . date('Y-m-d-H-i-s');
    wp_mkdir_p($backup_dir);

    // Backup settings
    $settings_backup = $backup_dir . '/settings.json';
    file_put_contents($settings_backup, json_encode(export_cookie_settings()));

    // Backup consent data
    $consent_backup = $backup_dir . '/consent-data.json';
    file_put_contents($consent_backup, json_encode(export_consent_data()));

    // Backup customizations
    $custom_backup = $backup_dir . '/customizations.json';
    file_put_contents($custom_backup, json_encode(export_customizations()));

    return $backup_dir;
}
```

### Install Shahi LegalFlowSuite

#### Step 1: Plugin Installation
```
WordPress Admin → Plugins → Add New
Search: "Shahi LegalFlowSuite"
Install and activate
```

#### Step 2: Initial Configuration
```
SLOS → Setup Wizard → Run Setup
```

**Basic Setup:**
- Select compliance regions
- Choose banner style
- Configure essential settings
- Set up admin access

### Run Migration Wizard

#### Step 1: Access Migration Tool
```
SLOS → Migration → Import Data → Start Migration
```

#### Step 2: Select Source Plugin

**Supported Plugins:**
- Cookie Notice
- GDPR Cookie Consent
- Cookiebot
- Complianz
- Cookie Law Info
- Termly
- iubenda

**Migration Selection:**
```javascript
const migrationOptions = {
    source_plugin: 'cookie_notice', // or 'gdpr_cookie_consent', 'complianz', etc.
    import_settings: true,
    import_consent_data: true,
    import_customizations: true,
    preserve_user_preferences: true,
    backup_before_migration: true
};
```

### Import Settings & Configuration

#### Step 1: Banner Settings Migration

**Cookie Notice to SLOS:**
```php
// Map Cookie Notice settings to SLOS
function migrate_cookie_notice_settings($cn_settings) {
    return [
        'banner_position' => $cn_settings['position'] === 'top' ? 'top' : 'bottom',
        'banner_style' => $cn_settings['style'] === 'block' ? 'block' : 'classic',
        'accept_button_text' => $cn_settings['button_text'] ?: 'Accept',
        'reject_button_text' => 'Reject',
        'read_more_text' => $cn_settings['link_text'] ?: 'Read More',
        'read_more_link' => $cn_settings['link_url'] ?: '/privacy-policy',
        'colors' => [
            'background' => $cn_settings['colors']['background'] ?: '#000000',
            'text' => $cn_settings['colors']['text'] ?: '#ffffff',
            'buttons' => $cn_settings['colors']['buttons'] ?: '#ffffff'
        ]
    ];
}
```

**GDPR Cookie Consent to SLOS:**
```php
// Map GDPR Cookie Consent settings
function migrate_gdpr_settings($gdpr_settings) {
    return [
        'cookie_categories' => [
            'essential' => [
                'enabled' => true,
                'required' => true,
                'description' => $gdpr_settings['necessary_description']
            ],
            'analytics' => [
                'enabled' => $gdpr_settings['analytics_enabled'],
                'description' => $gdpr_settings['analytics_description']
            ],
            'marketing' => [
                'enabled' => $gdpr_settings['marketing_enabled'],
                'description' => $gdpr_settings['marketing_description']
            ]
        ],
        'geo_targeting' => $gdpr_settings['geo_targeting_enabled'],
        'consent_expiry' => $gdpr_settings['consent_expiry_days'] ?: 365
    ];
}
```

#### Step 2: Cookie Category Mapping

**Category Mapping Table:**
```javascript
const categoryMapping = {
    // Cookie Notice categories
    'cookie_notice': {
        'necessary': 'essential',
        'analytics': 'analytics',
        'marketing': 'marketing'
    },

    // GDPR Cookie Consent categories
    'gdpr_cookie_consent': {
        'necessary': 'essential',
        'analytics': 'analytics',
        'advertisement': 'marketing',
        'functional': 'preferences'
    },

    // Complianz categories
    'complianz': {
        'functional': 'essential',
        'statistics': 'analytics',
        'marketing': 'marketing',
        'preferences': 'preferences'
    },

    // Cookiebot categories
    'cookiebot': {
        'necessary': 'essential',
        'preferences': 'preferences',
        'statistics': 'analytics',
        'marketing': 'marketing'
    }
};
```

### Import User Consent Data

#### Step 1: Consent History Migration
```php
// Import consent records
function import_consent_history($source_data, $source_plugin) {
    global $wpdb;

    $imported = 0;
    $skipped = 0;

    foreach ($source_data as $record) {
        // Map source data to SLOS format
        $slos_record = map_consent_record($record, $source_plugin);

        // Validate record
        if (validate_consent_record($slos_record)) {
            $wpdb->insert(
                $wpdb->prefix . 'slos_consent_log',
                $slos_record
            );
            $imported++;
        } else {
            $skipped++;
            log_skipped_record($record, 'validation_failed');
        }
    }

    return ['imported' => $imported, 'skipped' => $skipped];
}
```

#### Step 2: Consent Record Mapping

**Cookie Notice Record Mapping:**
```php
function map_cookie_notice_record($record) {
    return [
        'user_id' => $record->user_id ?: 0,
        'ip_address' => $record->ip_address,
        'user_agent' => $record->user_agent,
        'consent_categories' => json_encode([
            'essential' => true,
            'analytics' => $record->analytics_accepted,
            'marketing' => $record->marketing_accepted
        ]),
        'consent_timestamp' => strtotime($record->consent_date),
        'consent_expiry' => strtotime($record->consent_date) + (365 * 24 * 60 * 60),
        'source' => 'migrated_from_cookie_notice'
    ];
}
```

**GDPR Cookie Consent Record Mapping:**
```php
function map_gdpr_record($record) {
    return [
        'user_id' => $record->user_id ?: 0,
        'ip_address' => $record->ip,
        'user_agent' => $record->user_agent,
        'consent_categories' => json_encode([
            'essential' => true,
            'analytics' => strpos($record->consent, 'analytics') !== false,
            'marketing' => strpos($record->consent, 'marketing') !== false,
            'preferences' => strpos($record->consent, 'functional') !== false
        ]),
        'consent_timestamp' => strtotime($record->consent_date),
        'consent_expiry' => strtotime($record->consent_date) + ($record->expiry_days * 24 * 60 * 60),
        'source' => 'migrated_from_gdpr_cookie_consent'
    ];
}
```

### Handle Customizations & Integrations

#### Step 1: Custom CSS Migration
```php
// Migrate custom CSS
function migrate_custom_css($source_plugin) {
    $custom_css = '';

    switch ($source_plugin) {
        case 'cookie_notice':
            $settings = get_option('cookie_notice_options');
            if (!empty($settings['css'])) {
                $custom_css = $settings['css'];
            }
            break;

        case 'gdpr_cookie_consent':
            $settings = get_option('gdpr_cookie_consent_settings');
            if (!empty($settings['custom_css'])) {
                $custom_css = $settings['custom_css'];
            }
            break;

        case 'complianz':
            $settings = get_option('complianz_options');
            if (!empty($settings['custom_css'])) {
                $custom_css = $settings['custom_css'];
            }
            break;
    }

    // Convert CSS to SLOS format
    $slos_css = convert_css_to_slos($custom_css, $source_plugin);

    // Save to SLOS
    update_option('slos_custom_css', $slos_css);

    return $slos_css;
}
```

#### Step 2: Integration Migration

**Google Analytics Integration:**
```php
function migrate_ga_integration($source_plugin) {
    $ga_settings = [];

    switch ($source_plugin) {
        case 'cookie_notice':
            $ga_settings = get_option('cookie_notice_ga_settings');
            break;

        case 'gdpr_cookie_consent':
            $ga_settings = get_option('gdpr_ga_settings');
            break;

        case 'cookiebot':
            $ga_settings = get_option('cookiebot_ga_settings');
            break;
    }

    // Configure SLOS GA integration
    if (!empty($ga_settings['tracking_id'])) {
        update_option('slos_ga_tracking_id', $ga_settings['tracking_id']);
        update_option('slos_ga_anonymize_ip', $ga_settings['anonymize_ip']);
    }
}
```

**Facebook Pixel Integration:**
```php
function migrate_facebook_pixel($source_plugin) {
    $fb_settings = [];

    switch ($source_plugin) {
        case 'gdpr_cookie_consent':
            $fb_settings = get_option('gdpr_facebook_settings');
            break;

        case 'cookiebot':
            $fb_settings = get_option('cookiebot_facebook_settings');
            break;
    }

    // Configure SLOS Facebook integration
    if (!empty($fb_settings['pixel_id'])) {
        update_option('slos_facebook_pixel_id', $fb_settings['pixel_id']);
    }
}
```

### Test Migration Results

#### Step 1: Functionality Testing

**Banner Testing:**
- [ ] Banner appears correctly
- [ ] All buttons work
- [ ] Consent saves properly
- [ ] Categories display accurately

**Cookie Testing:**
- [ ] Cookies set based on consent
- [ ] Analytics tracking works with consent
- [ ] Marketing pixels fire with consent
- [ ] Cookie blocking functions

**Data Testing:**
- [ ] Consent history imported
- [ ] User preferences preserved
- [ ] Settings migrated correctly
- [ ] Customizations applied

#### Step 2: Consent Validation
```javascript
// Validate migrated consent data
function validate_migrated_consent() {
    const validation = {
        total_records: getTotalConsentRecords(),
        valid_records: 0,
        invalid_records: 0,
        categories_mapped: 0,
        issues: []
    };

    // Check each record
    getAllConsentRecords().forEach(record => {
        if (validateConsentRecord(record)) {
            validation.valid_records++;
            if (record.categories_mapped) {
                validation.categories_mapped++;
            }
        } else {
            validation.invalid_records++;
            validation.issues.push({
                record_id: record.id,
                issue: getValidationError(record)
            });
        }
    });

    return validation;
}
```

### Handle Migration Issues

#### Step 1: Common Problems & Solutions

**Missing Consent Data:**
```php
// Repair missing consent data
function repair_missing_consent() {
    global $wpdb;

    // Find records with missing categories
    $missing_records = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}slos_consent_log
        WHERE consent_categories IS NULL OR consent_categories = ''
    ");

    foreach ($missing_records as $record) {
        // Apply default consent based on migration source
        $default_consent = get_default_consent_for_source($record->source);
        $wpdb->update(
            $wpdb->prefix . 'slos_consent_log',
            ['consent_categories' => json_encode($default_consent)],
            ['id' => $record->id]
        );
    }
}
```

**Broken Customizations:**
```php
// Fix broken custom CSS
function fix_broken_css() {
    $current_css = get_option('slos_custom_css');
    $fixed_css = '';

    // Remove incompatible selectors
    $fixed_css = preg_replace('/\.cookie-notice-container/', '.slos-consent-banner', $current_css);
    $fixed_css = preg_replace('/\.gdpr-cookie-consent/', '.slos-consent-banner', $fixed_css);

    // Update incompatible properties
    $fixed_css = str_replace('float:', 'position:', $fixed_css);

    update_option('slos_custom_css', $fixed_css);
}
```

**Integration Failures:**
```php
// Fix broken integrations
function fix_broken_integrations() {
    // Reconfigure Google Analytics
    if (get_option('slos_ga_tracking_id')) {
        // Verify GA settings
        $ga_settings = verify_ga_settings();
        if (!$ga_settings['valid']) {
            // Reconfigure GA integration
            reconfigure_ga_integration();
        }
    }

    // Reconfigure Facebook Pixel
    if (get_option('slos_facebook_pixel_id')) {
        $fb_settings = verify_facebook_settings();
        if (!$fb_settings['valid']) {
            reconfigure_facebook_integration();
        }
    }
}
```

### Post-Migration Cleanup

#### Step 1: Remove Old Plugins
```php
// Safely deactivate old plugins
function cleanup_old_plugins() {
    $plugins_to_remove = [
        'cookie-notice/cookie-notice.php',
        'gdpr-cookie-consent/gdpr-cookie-consent.php',
        'complianz-gdpr/complianz-gdpr.php',
        'cookie-law-info/cookie-law-info.php'
    ];

    foreach ($plugins_to_remove as $plugin) {
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin);
            log_plugin_deactivation($plugin);
        }
    }
}
```

#### Step 2: Database Cleanup
```php
// Clean up old database tables
function cleanup_old_tables() {
    global $wpdb;

    $tables_to_drop = [
        $wpdb->prefix . 'cookie_notice_log',
        $wpdb->prefix . 'gdpr_cookie_consent_log',
        $wpdb->prefix . 'complianz_cookies',
        $wpdb->prefix . 'cookie_law_info_log'
    ];

    foreach ($tables_to_drop as $table) {
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'")) {
            $wpdb->query("DROP TABLE $table");
            log_table_dropped($table);
        }
    }
}
```

### Monitor Migration Success

#### Step 1: Performance Monitoring
```php
// Monitor post-migration performance
function monitor_migration_performance() {
    $metrics = [
        'consent_save_time' => measure_consent_save_time(),
        'banner_load_time' => measure_banner_load_time(),
        'cookie_blocking_efficiency' => measure_cookie_blocking(),
        'user_complaints' => count_user_complaints()
    ];

    // Compare with pre-migration benchmarks
    $comparison = compare_with_baselines($metrics);

    if ($comparison['degraded']) {
        send_performance_alert($comparison);
    }

    return $metrics;
}
```

#### Step 2: User Feedback Collection
```javascript
// Collect user feedback on migration
function collect_migration_feedback() {
    // Add feedback form to banner
    const feedbackForm = `
        <div class="slos-migration-feedback">
            <p>How was your experience with the new consent system?</p>
            <select id="migration-rating">
                <option value="">Please rate</option>
                <option value="5">Excellent</option>
                <option value="4">Good</option>
                <option value="3">Average</option>
                <option value="2">Poor</option>
                <option value="1">Very Poor</option>
            </select>
            <button onclick="submitMigrationFeedback()">Submit</button>
        </div>
    `;

    // Display feedback form
    showMigrationFeedback(feedbackForm);
}
```

### Rollback Procedures

#### Step 1: Emergency Rollback
```php
// Emergency rollback function
function emergency_rollback() {
    // Deactivate SLOS
    deactivate_plugins('shahi-legalflowsuite/shahi-legalflowsuite.php');

    // Restore backup
    $backup_dir = get_latest_backup();
    if ($backup_dir) {
        restore_from_backup($backup_dir);
    }

    // Reactivate original plugin
    $original_plugin = get_original_plugin();
    activate_plugin($original_plugin);

    // Log rollback
    log_emergency_rollback();
}
```

#### Step 2: Gradual Rollback
```php
// Gradual rollback with data preservation
function gradual_rollback() {
    // Keep SLOS data but hide interface
    update_option('slos_rollback_mode', true);

    // Restore original plugin interface
    restore_original_interface();

    // Maintain dual consent systems temporarily
    enable_dual_consent_mode();

    // Schedule complete rollback
    wp_schedule_single_event(time() + 86400, 'complete_rollback'); // 24 hours
}
```

### Migration Support Resources

#### Documentation Links
- Migration troubleshooting guide
- Plugin comparison matrix
- Data mapping reference
- Rollback procedures

#### Support Contacts
- Technical support team
- Migration specialists
- Plugin compatibility experts
- Data recovery services

#### Community Resources
- User forums
- Migration case studies
- Best practices guides
- Video tutorials

### Success Metrics

#### Migration KPIs
- Consent data migration accuracy (>99%)
- User consent preservation rate (>95%)
- System performance maintained (±10%)
- User complaints (<5% increase)
- Support tickets (<10% increase)

#### Long-term Success
- Compliance maintained
- User experience improved
- Administrative burden reduced
- Feature adoption increased
- Support costs decreased