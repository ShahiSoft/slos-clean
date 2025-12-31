# Multi-Site Issues

## Resolve WordPress Multi-Site Network Problems

### Network Activation Problems

#### Step 1: Check Network Requirements
```
SLOS → Multi-Site → Network → Requirements Check
```

**Network Compatibility Report:**
```json
{
  "multisite_enabled": true,
  "network_admin_access": true,
  "super_admin_privileges": true,
  "network_activated_plugins": [
    "slos-legalflowsuite.php"
  ],
  "site_specific_settings": true,
  "shared_tables_configured": false,
  "issues_found": [
    {
      "type": "shared_tables_missing",
      "severity": "high",
      "description": "Shared consent tables not created",
      "fix_required": true
    }
  ]
}
```

#### Step 2: Fix Network Activation
```php
// Network activation fix
function fixNetworkActivation() {
    // Ensure we're in network admin
    if (!is_network_admin()) {
        return false;
    }

    // Create shared tables
    createSharedTables();

    // Set network-wide options
    update_network_option(null, 'slos_network_activated', true);
    update_network_option(null, 'slos_shared_tables_created', true);

    // Configure default settings for all sites
    setNetworkDefaults();

    return true;
}

function createSharedTables() {
    global $wpdb;

    $sharedTables = [
        'slos_consent_log' => "CREATE TABLE {$wpdb->base_prefix}slos_consent_log (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            site_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED,
            consent_categories TEXT,
            consent_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT,
            source VARCHAR(50) DEFAULT 'direct',
            INDEX idx_site_user (site_id, user_id),
            INDEX idx_timestamp (consent_timestamp)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        // Add other shared tables...
    ];

    foreach ($sharedTables as $tableName => $createQuery) {
        $fullTableName = $wpdb->base_prefix . $tableName;
        if ($wpdb->get_var("SHOW TABLES LIKE '$fullTableName'") != $fullTableName) {
            $wpdb->query($createQuery);
        }
    }
}
```

#### Step 3: Verify Network Setup
```sql
-- Verify network setup
SELECT
    'shared_tables' as check_type,
    COUNT(*) as tables_found
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'wp_slos_%'
AND TABLE_NAME NOT LIKE 'wp_2_slos_%';

SELECT
    'network_options' as check_type,
    option_name,
    option_value
FROM wp_options
WHERE option_name LIKE 'slos_%'
ORDER BY option_name;
```

### Site-Specific Settings Conflicts

#### Step 1: Analyze Site Configurations
```
SLOS → Multi-Site → Sites → Configuration Analysis
```

**Site Configuration Report:**
```json
{
  "total_sites": 5,
  "sites_with_slos": 5,
  "configuration_conflicts": [
    {
      "conflict_type": "consent_categories_mismatch",
      "affected_sites": [2, 4],
      "description": "Sites have different consent category definitions",
      "severity": "medium"
    },
    {
      "conflict_type": "banner_position_inconsistent",
      "affected_sites": [1, 3, 5],
      "description": "Different banner positions across sites",
      "severity": "low"
    }
  ],
  "recommended_action": "standardize_settings"
}
```

#### Step 2: Standardize Site Settings
```
SLOS → Multi-Site → Sites → Settings Standardization
```

**Settings Standardization Process:**
```json
{
  "standardization_steps": [
    {
      "step": "analyze_current_settings",
      "description": "Collect settings from all sites",
      "status": "completed"
    },
    {
      "step": "identify_best_practices",
      "description": "Determine optimal settings",
      "status": "completed"
    },
    {
      "step": "apply_network_defaults",
      "description": "Apply standardized settings",
      "status": "in_progress"
    },
    {
      "step": "verify_consistency",
      "description": "Check all sites match standards",
      "status": "pending"
    }
  ],
  "settings_to_standardize": [
    "consent_categories",
    "banner_position",
    "cookie_expiry",
    "privacy_policy_url"
  ]
}
```

#### Step 3: Apply Site-Specific Overrides
```php
// Site-specific settings management
function manageSiteSpecificSettings($siteId, $settings) {
    $networkDefaults = getNetworkDefaults();
    $siteOverrides = getSiteOverrides($siteId);

    // Merge settings with proper precedence
    $finalSettings = array_merge($networkDefaults, $siteOverrides, $settings);

    // Validate settings
    $validation = validateSiteSettings($finalSettings, $siteId);

    if ($validation['valid']) {
        updateSiteSettings($siteId, $finalSettings);
        return ['success' => true];
    } else {
        return [
            'success' => false,
            'errors' => $validation['errors']
        ];
    }
}

function getSiteOverrides($siteId) {
    // Get site-specific settings that override network defaults
    return get_option('slos_site_' . $siteId . '_overrides', []);
}
```

### Shared Table Issues

#### Step 1: Check Shared Table Structure
```
SLOS → Multi-Site → Tables → Structure Check
```

**Shared Table Analysis:**
```json
{
  "shared_tables": [
    {
      "table_name": "wp_slos_consent_log",
      "exists": true,
      "columns_correct": true,
      "indexes_present": true,
      "site_column_exists": true,
      "record_count": 15420
    },
    {
      "table_name": "wp_slos_cookie_settings",
      "exists": true,
      "columns_correct": false,
      "missing_columns": ["site_id"],
      "indexes_present": true,
      "record_count": 450
    }
  ],
  "issues_found": [
    {
      "table": "wp_slos_cookie_settings",
      "issue": "missing_site_id_column",
      "severity": "high",
      "fix_available": true
    }
  ]
}
```

#### Step 2: Fix Shared Table Problems
```sql
-- Add missing site_id column to shared tables
ALTER TABLE wp_slos_cookie_settings
ADD COLUMN site_id BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER id,
ADD INDEX idx_site_id (site_id);

-- Migrate existing data to include site_id
UPDATE wp_slos_cookie_settings
SET site_id = 1
WHERE site_id = 0 OR site_id IS NULL;

-- Update table structure for other shared tables
ALTER TABLE wp_slos_consent_log
MODIFY COLUMN site_id BIGINT UNSIGNED NOT NULL DEFAULT 1;

-- Add site-specific indexes
CREATE INDEX idx_site_timestamp ON wp_slos_consent_log (site_id, consent_timestamp);
CREATE INDEX idx_site_user ON wp_slos_consent_log (site_id, user_id);
```

#### Step 3: Validate Table Integrity
```sql
-- Table integrity validation
SELECT
    table_name,
    table_rows,
    data_length,
    index_length,
    (data_length + index_length) as total_size
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME LIKE 'wp_slos_%'
ORDER BY table_name;

-- Check for orphaned records
SELECT
    'consent_log_without_site' as issue,
    COUNT(*) as count
FROM wp_slos_consent_log
WHERE site_id NOT IN (SELECT blog_id FROM wp_blogs);

SELECT
    'settings_without_site' as issue,
    COUNT(*) as count
FROM wp_slos_cookie_settings
WHERE site_id NOT IN (SELECT blog_id FROM wp_blogs);
```

### Cross-Site Consent Synchronization

#### Step 1: Check Synchronization Status
```
SLOS → Multi-Site → Sync → Status Check
```

**Synchronization Report:**
```json
{
  "sync_enabled": true,
  "last_sync": "2024-12-31T10:00:00Z",
  "sync_method": "real_time",
  "sites_in_sync": 4,
  "sites_out_of_sync": 1,
  "sync_issues": [
    {
      "site_id": 3,
      "issue": "sync_queue_backlog",
      "records_pending": 150,
      "last_successful_sync": "2024-12-30T15:30:00Z"
    }
  ],
  "performance_metrics": {
    "average_sync_time": "0.5s",
    "sync_success_rate": "98%",
    "queue_size": 25
  }
}
```

#### Step 2: Fix Synchronization Problems
```
SLOS → Multi-Site → Sync → Problem Resolution
```

**Sync Issue Resolution:**
```json
{
  "resolution_steps": [
    {
      "issue": "sync_queue_backlog",
      "action": "clear_sync_queue",
      "status": "in_progress",
      "estimated_completion": "2_minutes"
    },
    {
      "issue": "real_time_sync_failures",
      "action": "switch_to_batch_sync",
      "status": "pending",
      "fallback_available": true
    },
    {
      "issue": "cross_site_user_matching",
      "action": "implement_user_id_mapping",
      "status": "pending",
      "complexity": "high"
    }
  ],
  "sync_configuration": {
    "sync_method": "batch",
    "batch_size": 100,
    "sync_interval": 300,
    "error_retry_limit": 3,
    "backoff_multiplier": 2
  }
}
```

#### Step 3: Implement User ID Mapping
```php
// Cross-site user ID mapping
function mapCrossSiteUserIds($sourceSiteId, $targetSiteId, $userId) {
    global $wpdb;

    // Check if user exists on target site
    $targetUserId = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->base_prefix}{$targetSiteId}_users WHERE ID = %d",
        $userId
    ));

    if (!$targetUserId) {
        // User doesn't exist on target site, create mapping record
        $wpdb->insert(
            "{$wpdb->base_prefix}slos_user_mapping",
            [
                'source_site_id' => $sourceSiteId,
                'target_site_id' => $targetSiteId,
                'source_user_id' => $userId,
                'mapping_status' => 'pending',
                'created_at' => current_time('mysql')
            ]
        );
        return false;
    }

    // Create or update mapping
    $wpdb->replace(
        "{$wpdb->base_prefix}slos_user_mapping",
        [
            'source_site_id' => $sourceSiteId,
            'target_site_id' => $targetSiteId,
            'source_user_id' => $userId,
            'target_user_id' => $targetUserId,
            'mapping_status' => 'active',
            'updated_at' => current_time('mysql')
        ]
    );

    return $targetUserId;
}
```

### Network Administration Challenges

#### Step 1: Set Up Network Admin Access
```
SLOS → Multi-Site → Admin → Access Setup
```

**Network Admin Configuration:**
```json
{
  "network_admin_users": [
    {
      "user_id": 1,
      "username": "superadmin",
      "sites_managed": "all",
      "permissions": ["full_access"]
    }
  ],
  "admin_menu_location": "network_admin",
  "bulk_operations_enabled": true,
  "site_specific_admin_override": false,
  "audit_logging": true
}
```

#### Step 2: Configure Bulk Operations
```
SLOS → Multi-Site → Admin → Bulk Operations
```

**Bulk Operation Settings:**
```json
{
  "available_operations": [
    "update_all_sites",
    "sync_consent_settings",
    "bulk_export_data",
    "mass_consent_reset",
    "network_wide_backup"
  ],
  "operation_queue": [
    {
      "operation": "update_all_sites",
      "target_sites": "all",
      "status": "pending",
      "scheduled_time": "2024-12-31T11:00:00Z"
    }
  ],
  "progress_tracking": true,
  "rollback_capability": true
}
```

#### Step 3: Implement Audit Logging
```php
// Network-wide audit logging
function logNetworkAction($action, $details, $siteId = null) {
    global $wpdb;

    $logEntry = [
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id(),
        'action' => $action,
        'details' => json_encode($details),
        'site_id' => $siteId ?: get_current_blog_id(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];

    $wpdb->insert("{$wpdb->base_prefix}slos_network_audit_log", $logEntry);

    // Check for suspicious activity
    checkForSuspiciousActivity($logEntry);
}

function checkForSuspiciousActivity($logEntry) {
    // Implement suspicious activity detection
    $suspiciousPatterns = [
        'mass_data_deletion' => $logEntry['action'] === 'bulk_delete' && $logEntry['details']['record_count'] > 1000,
        'unauthorized_access' => !current_user_can('manage_network'),
        'rapid_setting_changes' => // Check for rapid changes across multiple sites
    ];

    foreach ($suspiciousPatterns as $pattern => $condition) {
        if ($condition) {
            triggerNetworkAlert($pattern, $logEntry);
        }
    }
}
```

### Performance Issues in Multi-Site

#### Step 1: Analyze Network Performance
```
SLOS → Multi-Site → Performance → Network Analysis
```

**Network Performance Report:**
```json
{
  "total_sites": 5,
  "average_response_time": "1.2s",
  "slowest_site": {
    "site_id": 3,
    "response_time": "3.5s",
    "issue": "large_consent_database"
  },
  "database_performance": {
    "shared_table_queries": "fast",
    "cross_site_joins": "slow",
    "recommended_optimization": "add_site_indexes"
  },
  "caching_efficiency": {
    "cache_hit_rate": "75%",
    "cache_misses": 1250,
    "recommended_improvement": "implement_network_cache"
  }
}
```

#### Step 2: Optimize Multi-Site Performance
```sql
-- Add performance indexes for multi-site
CREATE INDEX idx_site_consent_timestamp ON wp_slos_consent_log (site_id, consent_timestamp);
CREATE INDEX idx_site_user_consent ON wp_slos_consent_log (site_id, user_id, consent_timestamp DESC);
CREATE INDEX idx_site_ip ON wp_slos_consent_log (site_id, ip_address);

-- Optimize shared table queries
ALTER TABLE wp_slos_consent_log
ADD PARTITION BY HASH(site_id) PARTITIONS 10;

-- Add composite indexes for common queries
CREATE INDEX idx_site_category_timestamp ON wp_slos_consent_log
(site_id, consent_categories(50), consent_timestamp);
```

#### Step 3: Implement Network Caching
```php
// Network-wide caching strategy
class NetworkCacheManager {
    private $cacheGroup = 'slos_network';

    public function getNetworkData($key, $siteId = null) {
        $cacheKey = $siteId ? "site_{$siteId}_{$key}" : "network_{$key}";
        return wp_cache_get($cacheKey, $this->cacheGroup);
    }

    public function setNetworkData($key, $data, $siteId = null, $expiration = 300) {
        $cacheKey = $siteId ? "site_{$siteId}_{$key}" : "network_{$key}";
        wp_cache_set($cacheKey, $data, $this->cacheGroup, $expiration);

        // Also cache in network context for cross-site access
        if ($siteId) {
            $networkKey = "network_{$key}";
            $networkData = $this->getNetworkData($key) ?: [];
            $networkData[$siteId] = $data;
            wp_cache_set($networkKey, $networkData, $this->cacheGroup, $expiration);
        }
    }

    public function clearNetworkCache($key = null, $siteId = null) {
        if ($key && $siteId) {
            wp_cache_delete("site_{$siteId}_{$key}", $this->cacheGroup);
        } elseif ($key) {
            // Clear from all sites
            $sites = get_sites();
            foreach ($sites as $site) {
                wp_cache_delete("site_{$site->blog_id}_{$key}", $this->cacheGroup);
            }
            wp_cache_delete("network_{$key}", $this->cacheGroup);
        } else {
            wp_cache_flush_group($this->cacheGroup);
        }
    }
}
```

### Backup and Recovery for Multi-Site

#### Step 1: Configure Network Backups
```
SLOS → Multi-Site → Backup → Network Configuration
```

**Network Backup Strategy:**
```json
{
  "backup_scope": "network_wide",
  "backup_frequency": "daily",
  "backup_components": [
    "shared_tables",
    "site_specific_tables",
    "plugin_settings",
    "uploaded_files"
  ],
  "retention_policy": {
    "daily_backups": 7,
    "weekly_backups": 4,
    "monthly_backups": 12
  },
  "storage_locations": [
    "local_server",
    "remote_storage",
    "cloud_backup"
  ]
}
```

#### Step 2: Implement Network Recovery
```
SLOS → Multi-Site → Recovery → Network Recovery
```

**Network Recovery Procedures:**
```json
{
  "recovery_scenarios": [
    {
      "scenario": "shared_table_corruption",
      "recovery_method": "table_restore",
      "impact": "all_sites_affected",
      "downtime_expected": "30_minutes"
    },
    {
      "scenario": "single_site_data_loss",
      "recovery_method": "site_specific_restore",
      "impact": "one_site_affected",
      "downtime_expected": "5_minutes"
    },
    {
      "scenario": "complete_network_failure",
      "recovery_method": "full_network_restore",
      "impact": "all_sites_affected",
      "downtime_expected": "2_hours"
    }
  ],
  "recovery_testing": {
    "last_test": "2024-12-15T10:00:00Z",
    "test_results": "passed",
    "recommendations": "test_monthly"
  }
}
```

### Support Resources

#### Documentation
- [Multi-Site Setup Guide](../How-tos/13-multisite-setup.md)
- [Network Administration](../Advanced/08-network-administration.md)
- [Cross-Site Synchronization](../Advanced/09-cross-site-sync.md)

#### Help
- Multi-site specialists
- Network architecture consultation
- Performance optimization services
- Emergency recovery team