# Hooks & Filters Reference

## Overview

Shahi LegalFlowSuite provides extensive hooks and filters for developers to customize functionality.

## Action Hooks

### Consent Management

#### slos_before_banner_render
Fires before banner HTML renders.

```php
add_action('slos_before_banner_render', function($template, $variant) {
    // Do something before banner appears
    error_log('Showing ' . $template . ' template');
}, 10, 2);
```

**Parameters:**
- `$template` - Banner template ID (gdpr, ccpa, etc.)
- `$variant` - A/B variant (A or B)

#### slos_consent_accepted
Fires when user accepts consent.

```php
add_action('slos_consent_accepted', function($consent_data) {
    // Send consent to CRM
    your_crm_api_log_consent($consent_data);
}, 10, 1);
```

**Parameters:**
- `$consent_data` - Array with:
  - `id` - Consent record ID
  - `categories` - Accepted categories array
  - `variant` - A/B variant
  - `region` - Geographic region
  - `duration` - Milliseconds to decision

#### slos_consent_rejected
Fires when user rejects consent.

```php
add_action('slos_consent_rejected', function($consent_data) {
    // Log rejection
    error_log('Consent rejected in ' . $consent_data['region']);
}, 10, 1);
```

#### slos_before_cookie_scan
Fires before cookie scan starts.

```php
add_action('slos_before_cookie_scan', function($urls) {
    error_log('Scanning ' . count($urls) . ' URLs');
}, 10, 1);
```

**Parameters:**
- `$urls` - Array of URLs to scan

#### slos_cookie_scan_completed
Fires when cookie scan finishes.

```php
add_action('slos_cookie_scan_completed', function($results) {
    // Process scan results
    $cookie_count = count($results['cookies']);
    error_log('Found ' . $cookie_count . ' cookies');
}, 10, 1);
```

**Parameters:**
- `$results` - Array with scan results

### Accessibility Scanner

#### slos_before_accessibility_scan
Fires before accessibility scan starts.

```php
add_action('slos_before_accessibility_scan', function($page_url) {
    error_log('Starting scan for: ' . $page_url);
}, 10, 1);
```

#### slos_accessibility_issue_found
Fires when accessibility issue detected.

```php
add_action('slos_accessibility_issue_found', function($issue) {
    // Custom logging
    error_log('Found: ' . $issue['type']);
}, 10, 1);
```

**Parameters:**
- `$issue` - Array with:
  - `type` - Issue type (focus_indicator, color_contrast, etc.)
  - `severity` - critical, major, minor
  - `wcag_criterion` - WCAG reference
  - `element` - HTML element causing issue

#### slos_fixer_applied
Fires when auto-fixer applied successfully.

```php
add_action('slos_fixer_applied', function($fixer_name, $result) {
    error_log('Fixer applied: ' . $fixer_name);
    error_log('Result: ' . ($result ? 'Success' : 'Failed'));
}, 10, 2);
```

**Parameters:**
- `$fixer_name` - Name of fixer class
- `$result` - Boolean success/failure

### Legal Documents

#### slos_before_document_generation
Fires before document generated.

```php
add_action('slos_before_document_generation', function($document_type) {
    error_log('Generating: ' . $document_type);
}, 10, 1);
```

**Parameters:**
- `$document_type` - privacy_policy, terms_of_service, etc.

#### slos_document_generated
Fires after document successfully generated.

```php
add_action('slos_document_generated', function($document_id, $type, $content) {
    // Custom processing
    error_log('Document ' . $document_id . ' created');
}, 10, 3);
```

#### slos_before_document_publish
Fires before document published to website.

```php
add_action('slos_before_document_publish', function($document_id, $page_id) {
    error_log('Publishing document to page ' . $page_id);
}, 10, 2);
```

### DSR Requests

#### slos_dsr_request_received
Fires when DSR request submitted.

```php
add_action('slos_dsr_request_received', function($request_id, $request_data) {
    // Send confirmation email
    wp_mail($request_data['email'], 'Request Received', '...');
}, 10, 2);
```

**Parameters:**
- `$request_id` - DSR request ID
- `$request_data` - Request information

#### slos_dsr_request_approved
Fires when DSR request approved by admin.

```php
add_action('slos_dsr_request_approved', function($request_id) {
    error_log('DSR approved: ' . $request_id);
    // Start processing
}, 10, 1);
```

#### slos_dsr_export_ready
Fires when user data export ready for download.

```php
add_action('slos_dsr_export_ready', function($request_id, $file_path) {
    // Notify user
    error_log('Export ready: ' . $file_path);
}, 10, 2);
```

#### slos_before_data_export
Fires before data export process starts.

```php
add_action('slos_before_data_export', function($user_id, $format) {
    error_log('Exporting user ' . $user_id . ' as ' . $format);
}, 10, 2);
```

## Filter Hooks

### Consent Data Filters

#### slos_consent_data
Modify consent data before saving.

```php
add_filter('slos_consent_data', function($consent_data) {
    // Add custom metadata
    $consent_data['custom_field'] = 'custom_value';
    return $consent_data;
});
```

**Parameters:**
- `$consent_data` - Consent array to modify

**Returns:** Modified consent array

#### slos_consent_categories
Modify available consent categories.

```php
add_filter('slos_consent_categories', function($categories) {
    // Add custom category
    $categories['custom'] = [
        'label' => 'Custom Tracking',
        'description' => 'Our custom tracking'
    ];
    return $categories;
});
```

**Returns:** Modified categories array

#### slos_cookie_inventory
Modify cookie list before displaying.

```php
add_filter('slos_cookie_inventory', function($cookies) {
    // Filter out certain cookies
    return array_filter($cookies, function($cookie) {
        return !in_array($cookie['name'], ['internal_tracking']);
    });
});
```

**Returns:** Modified cookies array

### Scanner Filters

#### slos_pages_to_scan
Modify list of URLs to scan.

```php
add_filter('slos_pages_to_scan', function($urls) {
    // Add custom URLs
    $urls[] = 'https://yoursite.com/custom-page';
    return $urls;
});
```

**Returns:** Modified URLs array

#### slos_accessibility_fixers
Modify which fixers to run.

```php
add_filter('slos_accessibility_fixers', function($fixers) {
    // Disable specific fixer
    unset($fixers['focus_indicator']);
    return $fixers;
});
```

**Returns:** Modified fixers array

#### slos_fixer_configuration
Modify fixer settings.

```php
add_filter('slos_fixer_configuration', function($config, $fixer_name) {
    if ($fixer_name === 'touch_target') {
        $config['min_size'] = 50; // Override 44px minimum
    }
    return $config;
}, 10, 2);
```

**Parameters:**
- `$config` - Fixer configuration array
- `$fixer_name` - Name of fixer

**Returns:** Modified configuration

### Document Filters

#### slos_document_content
Modify document content before rendering.

```php
add_filter('slos_document_content', function($content, $document_type) {
    // Add custom section
    $content .= "\n\n## Custom Section\n\nCustom content here.";
    return $content;
}, 10, 2);
```

**Parameters:**
- `$content` - Document content
- `$document_type` - privacy_policy, etc.

**Returns:** Modified content

#### slos_document_variables
Modify template variables.

```php
add_filter('slos_document_variables', function($variables) {
    // Add custom variable
    $variables['custom_date'] = date('Y-m-d');
    return $variables;
});
```

**Returns:** Modified variables array

#### slos_pdf_export_options
Modify PDF export settings.

```php
add_filter('slos_pdf_export_options', function($options) {
    $options['font_size'] = 11;
    $options['margin'] = 20;
    return $options;
});
```

**Returns:** Modified options

### DSR Filters

#### slos_dsr_export_data
Add custom data to DSR export.

```php
add_filter('slos_dsr_export_data', function($export_data, $user_id) {
    // Add custom data source
    $export_data['custom_plugin_data'] = get_user_meta($user_id, 'custom_key');
    return $export_data;
}, 10, 2);
```

**Parameters:**
- `$export_data` - Data array
- `$user_id` - User requesting export

**Returns:** Modified data array

#### slos_dsr_export_format
Modify data export format options.

```php
add_filter('slos_dsr_export_format', function($formats) {
    // Add custom format
    $formats['xml'] = 'XML Format';
    return $formats;
});
```

**Returns:** Modified formats array

#### slos_dsr_email_content
Modify DSR notification email.

```php
add_filter('slos_dsr_email_content', function($email_content, $request_id) {
    // Add custom message
    $email_content .= "\n\nCustom footer here.";
    return $email_content;
}, 10, 2);
```

**Returns:** Modified email content

### Settings Filters

#### slos_settings
Modify plugin settings.

```php
add_filter('slos_settings', function($settings) {
    // Force strict GDPR compliance
    $settings['consent_required'] = true;
    $settings['reject_easy_as_accept'] = true;
    return $settings;
});
```

**Returns:** Modified settings array

#### slos_geo_rules
Modify geo-targeting rules.

```php
add_filter('slos_geo_rules', function($rules) {
    // Add custom geo rule
    $rules['custom_region'] = [
        'template' => 'custom_template',
        'countries' => ['US', 'CA'],
        'priority' => 100
    ];
    return $rules;
});
```

**Returns:** Modified rules array

## Filter Examples

### Example 1: Custom Consent Processing

```php
add_action('slos_consent_accepted', function($consent_data) {
    // Track in custom database
    global $wpdb;
    
    $wpdb->insert(
        $wpdb->prefix . 'custom_consents',
        [
            'consent_id' => $consent_data['id'],
            'categories' => json_encode($consent_data['categories']),
            'timestamp' => current_time('mysql')
        ]
    );
});
```

### Example 2: Add Custom Cookie Category

```php
add_filter('slos_consent_categories', function($categories) {
    $categories['retention_optimization'] = [
        'label' => 'Retention & Optimization',
        'description' => 'Cookies to optimize user retention and feature testing',
        'vendors' => ['Intercom', 'Apptentive']
    ];
    return $categories;
});
```

### Example 3: Custom Accessibility Fixer

```php
add_filter('slos_accessibility_fixers', function($fixers) {
    // Register custom fixer
    $fixers['my_custom_fixer'] = 'My\\Custom\\Fixer\\CustomAccessibilityFixer';
    return $fixers;
});
```

### Example 4: Exclude Pages from Scanning

```php
add_filter('slos_pages_to_scan', function($urls) {
    // Don't scan password-protected pages
    $urls = array_filter($urls, function($url) {
        return strpos($url, '/protected/') === false;
    });
    return $urls;
});
```

## Common Patterns

### Log All Consent Events

```php
$events = ['accepted', 'rejected', 'customized'];
foreach ($events as $event) {
    add_action("slos_consent_{$event}", function($data) use ($event) {
        error_log("Consent {$event}: " . json_encode($data));
    });
}
```

### Custom Notification

```php
add_action('slos_dsr_request_received', function($request_id, $data) {
    // Send to Slack
    $message = "New DSR request: {$data['type']} from {$data['email']}";
    your_slack_notify($message);
}, 10, 2);
```

### Modify Scan Behavior

```php
add_filter('slos_pages_to_scan', function($urls) {
    // Only scan top-level pages
    return array_filter($urls, function($url) {
        $path = parse_url($url, PHP_URL_PATH);
        return substr_count($path, '/') <= 2;
    });
});
```

## Plugin Interaction

Hooks for other plugins:

```php
// Listen for plugin loaded
do_action('slos_loaded', SLOS_VERSION);

// Check if module active
if (apply_filters('slos_module_active', false, 'accessibility_scanner')) {
    // Run scanning integration
}
```

## Performance Considerations

- Keep hooks lightweight
- Don't make external API calls in hooks
- Use caching for expensive operations
- Consider async for heavy operations

## Debugging Hooks

Enable hook debugging:

```php
// In wp-config.php (for development)
define('SAVEQUERIES', true);
define('SLOS_DEBUG_HOOKS', true);
```

Then check debug output in logs.

## Next Steps

1. Review available hooks
2. Identify what you need to customize
3. Create custom plugin using hooks
4. Test thoroughly
5. Document changes

## Related

- [REST API Documentation](01-rest-api.md)
- [Developer Guide](03-developer-guide.md)
- [Custom Fixers](04-custom-accessibility-fixers.md)
