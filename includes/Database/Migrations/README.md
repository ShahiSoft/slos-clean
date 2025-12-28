# Database Migrations

This directory contains database migration files for the Shahi LegalFlowSuite plugin.

## Migration Files

- **migration_2025_12_20_consent_table.php** - User consent tracking (GDPR/CCPA/LGPD)
- **migration_2025_12_20_dsr_requests_table.php** - Data subject request management (GDPR Article 15-22)
- **migration_2025_12_20_documents_table.php** - Legal document versioning (privacy policies, terms)
- **migration_2025_12_20_trackers_table.php** - Third-party tracker inventory
- **migration_2025_12_20_vendors_table.php** - Vendor/processor management (GDPR Article 28)
- **migration_2025_12_20_form_submissions_table.php** - Form submission tracking
- **migration_2025_12_20_form_issues_table.php** - Form compliance issue tracking

## Running Migrations

Migrations are run automatically on plugin activation.

To manually run migrations via WordPress CLI:

```bash
wp eval '
require_once "includes/Database/Migrations/Runner.php";
$results = \ShahiLegalFlowSuite\Database\Migrations\Runner::run_all();
var_dump($results);
'
```

Or via PHP (in admin context):

```php
require_once plugin_dir_path( __FILE__ ) . 'includes/Database/Migrations/Runner.php';
$results = \ShahiLegalFlowSuite\Database\Migrations\Runner::run_all();
```

## Checking Migration Status

To check if all migrations have been run:

```php
$is_migrated = \ShahiLegalFlowSuite\Database\Migrations\Runner::is_migrated();
if ( $is_migrated ) {
    echo 'All tables created successfully';
} else {
    echo 'Some tables are missing';
}
```

To get list of existing tables:

```php
$existing = \ShahiLegalFlowSuite\Database\Migrations\Runner::get_existing_tables();
print_r( $existing );
```

## Rollback Migrations

To drop all tables (use with caution - data will be lost):

```bash
wp eval '
require_once "includes/Database/Migrations/Runner.php";
$results = \ShahiLegalFlowSuite\Database\Migrations\Runner::rollback_all();
var_dump($results);
'
```

## Migration Pattern

Each migration class follows this pattern:

```php
namespace ShahiLegalFlowSuite\Database\Migrations;

class Migration_YYYY_MM_DD_table_name {
    
    // Creates the table
    public static function up() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slos_table_name';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            // ... columns ...
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        
        return ! $wpdb->last_error;
    }
    
    // Drops the table
    public static function down() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'slos_table_name';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        return ! $wpdb->last_error;
    }
}
```

## Table Schemas

### wp_slos_consent
Tracks user consent choices for GDPR/CCPA/LGPD compliance.

- **Columns**: id, user_id, ip_hash, type, status, metadata, created_at, updated_at
- **Indexes**: user_id, type, status, ip_hash, created_at
- **Purpose**: Store consent records with flexible metadata for various regulations

### wp_slos_dsr_requests
Manages Data Subject Rights requests (GDPR Article 15-22).

- **Columns**: id, request_type, email, status, request_date, due_date, completed_date, data_export, metadata, created_at, updated_at
- **Indexes**: request_type, email, status, request_date, due_date
- **Purpose**: Track and process rights requests (access, deletion, rectification, etc.)

### wp_slos_documents
Stores legal documents with version control.

- **Columns**: id, type, content, version, published_at, previous_version_id, metadata, created_at, updated_at
- **Indexes**: type, version, published_at, previous_version_id
- **Purpose**: Manage privacy policies, terms of service, cookie policies with history

### wp_slos_trackers
Inventory of third-party trackers and scripts.

- **Columns**: id, type, name, category, provider, script_url, cookie_names, description, metadata, created_at, updated_at
- **Indexes**: type, category, provider
- **Purpose**: Track scripts, cookies, and pixels for consent management

### wp_slos_vendors
Vendor/processor registry for GDPR Article 28.

- **Columns**: id, name, category, country, dpa_url, privacy_policy_url, risk_level, metadata, created_at, updated_at
- **Indexes**: name, category, country, risk_level
- **Purpose**: Manage DPAs, SCCs, and vendor risk assessment

### wp_slos_form_submissions
Tracks form submissions for compliance.

- **Columns**: id, form_id, form_type, user_id, email, data, created_at, updated_at
- **Indexes**: form_id, form_type, user_id, email, created_at
- **Purpose**: Monitor form data collection (CF7, WPForms, Gravity Forms, etc.)

### wp_slos_form_issues
Logs form compliance issues.

- **Columns**: id, form_id, issue_type, severity, resolved_at, metadata, created_at
- **Indexes**: form_id, issue_type, severity, resolved_at, created_at
- **Purpose**: Track missing consent fields, privacy link issues, retention problems

## Notes

- All tables use the `wp_slos_` prefix (where `wp_` is the WordPress table prefix)
- Tables use `LONGTEXT` for JSON storage (compatible with all MySQL versions)
- All timestamps use `DATETIME` with automatic `CURRENT_TIMESTAMP` defaults
- Foreign key relationships are optional and not enforced for WordPress compatibility
- Indexes are optimized for common query patterns
- Character set and collation match WordPress site settings
