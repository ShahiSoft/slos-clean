# Migration Issues

## Fix Problems When Moving from Other Consent Solutions

### Pre-Migration Data Assessment

#### Step 1: Analyze Source Plugin Data
```
SLOS → Migration → Assessment → Source Analysis
```

**Supported Source Plugins:**
```json
{
  "cookie_notice": {
    "detected": true,
    "version": "2.4.2",
    "data_tables": ["wp_cn_cookies"],
    "settings_found": true,
    "estimated_records": 15420
  },
  "gdpr_cookie_consent": {
    "detected": false,
    "version": null,
    "data_tables": [],
    "settings_found": false
  },
  "complianz": {
    "detected": true,
    "version": "6.4.0",
    "data_tables": ["wp_cmplz_cookies", "wp_cmplz_services"],
    "settings_found": true,
    "estimated_records": 8750
  }
}
```

#### Step 2: Check Data Compatibility
```
SLOS → Migration → Assessment → Compatibility Check
```

**Compatibility Report:**
```json
{
  "data_mapping_score": 85,
  "potential_issues": [
    {
      "type": "category_mismatch",
      "severity": "medium",
      "description": "Source plugin uses different cookie categories",
      "solution": "Manual category mapping required"
    },
    {
      "type": "date_format_inconsistency",
      "severity": "low",
      "description": "Consent timestamps in different formats",
      "solution": "Automatic conversion available"
    }
  ],
  "estimated_migration_time": "45 minutes",
  "risk_level": "low"
}
```

### Migration Preparation

#### Step 1: Create Full Backup
```
SLOS → Migration → Backup → Create Migration Backup
```

**Backup Configuration:**
```json
{
  "backup_scope": "full_system",
  "include_source_data": true,
  "include_wordpress_data": true,
  "compression": "gzip",
  "encryption": true,
  "storage_location": "/wp-content/uploads/migration-backups/",
  "retention_days": 30
}
```

#### Step 2: Test Migration Environment
```
SLOS → Migration → Testing → Setup Test Environment
```

**Test Environment Setup:**
- Creates staging copy of site
- Installs SLOS in test environment
- Imports sample data for testing
- Configures test integrations

### Data Export Issues

#### Step 1: Fix Export Permissions
```
SLOS → Migration → Export → Permission Check
```

**Permission Issues:**
```json
{
  "database_permissions": {
    "select_granted": true,
    "file_write_granted": false,
    "issue": "Cannot write export files to /wp-content/uploads/"
  },
  "file_system_fixes": [
    "Change upload directory permissions to 755",
    "Ensure web server user owns upload directory",
    "Check disk space availability (2.3GB required)"
  ]
}
```

#### Step 2: Resolve Export Errors
```bash
# Fix file permissions
sudo chown -R www-data:www-data /var/www/html/wp-content/uploads/
sudo chmod -R 755 /var/www/html/wp-content/uploads/

# Check disk space
df -h /var/www/html/wp-content/uploads/

# Test file creation
touch /var/www/html/wp-content/uploads/test-export.txt
```

#### Step 3: Handle Large Data Sets
```
SLOS → Migration → Export → Large Dataset Handling
```

**Large Dataset Options:**
```json
{
  "chunk_size": 1000,
  "memory_limit": "256M",
  "time_limit": 300,
  "progress_tracking": true,
  "resume_capability": true,
  "compression_during_export": true
}
```

### Data Import Problems

#### Step 1: Validate Import Files
```
SLOS → Migration → Import → File Validation
```

**File Validation Results:**
```json
{
  "consent_data_json": {
    "valid": true,
    "record_count": 15420,
    "size_mb": 45.2,
    "encoding": "UTF-8"
  },
  "cookie_settings_json": {
    "valid": false,
    "error": "Invalid JSON structure at line 1250",
    "fix_available": true
  },
  "user_preferences_csv": {
    "valid": true,
    "record_count": 3240,
    "columns_mapped": 8,
    "data_quality_score": 92
  }
}
```

#### Step 2: Fix Import Errors
```
SLOS → Migration → Import → Error Resolution
```

**Common Import Fixes:**
```json
{
  "json_syntax_errors": {
    "auto_fix": true,
    "backup_original": true
  },
  "encoding_issues": {
    "convert_to_utf8": true,
    "handle_special_chars": true
  },
  "duplicate_records": {
    "detection_method": "hash_comparison",
    "resolution_strategy": "skip_duplicates"
  },
  "missing_required_fields": {
    "add_defaults": true,
    "field_mapping": {
      "old_field": "new_field"
    }
  }
}
```

#### Step 3: Handle Data Conflicts
```sql
-- Resolve data conflicts during import
INSERT INTO wp_slos_consent_log (user_id, consent_categories, consent_timestamp, source)
SELECT
  COALESCE(slos.user_id, source.user_id) as user_id,
  CASE
    WHEN slos.consent_timestamp > source.consent_timestamp THEN slos.consent_categories
    ELSE source.mapped_categories
  END as consent_categories,
  GREATEST(slos.consent_timestamp, source.consent_timestamp) as consent_timestamp,
  'migrated' as source
FROM source_consent_data source
LEFT JOIN wp_slos_consent_log slos ON slos.user_id = source.user_id
ON DUPLICATE KEY UPDATE
  consent_categories = VALUES(consent_categories),
  consent_timestamp = VALUES(consent_timestamp);
```

### Category Mapping Issues

#### Step 1: Review Category Mappings
```
SLOS → Migration → Categories → Mapping Review
```

**Category Mapping Table:**
```json
{
  "source_plugin": "cookie_notice",
  "mappings": {
    "necessary": {
      "slos_category": "essential",
      "confidence": 100,
      "auto_mapped": true
    },
    "analytics": {
      "slos_category": "analytics",
      "confidence": 95,
      "auto_mapped": true
    },
    "marketing": {
      "slos_category": "marketing",
      "confidence": 90,
      "requires_review": true,
      "reason": "May include social media cookies"
    },
    "unmapped_category": {
      "slos_category": null,
      "confidence": 0,
      "action_required": "manual_mapping"
    }
  }
}
```

#### Step 2: Manual Category Mapping
```
SLOS → Migration → Categories → Manual Mapping
```

**Manual Mapping Interface:**
- Drag and drop category assignment
- Preview affected cookies
- Test mapping with sample data
- Bulk mapping operations
- Save mapping templates

#### Step 3: Validate Mappings
```javascript
// Validate category mappings
function validateCategoryMappings(mappings) {
  const validation = {
    unmapped_cookies: [],
    conflicting_mappings: [],
    data_loss_warnings: []
  };

  // Check for unmapped cookies
  mappings.forEach(mapping => {
    if (!mapping.slos_category) {
      validation.unmapped_cookies.push(mapping.source_category);
    }
  });

  // Check for potential data loss
  mappings.forEach(mapping => {
    if (mapping.confidence < 80) {
      validation.data_loss_warnings.push({
        category: mapping.source_category,
        confidence: mapping.confidence
      });
    }
  });

  return validation;
}
```

### Consent Data Conversion Problems

#### Step 1: Handle Format Inconsistencies
```
SLOS → Migration → Data Conversion → Format Check
```

**Format Conversion Issues:**
```json
{
  "timestamp_formats": [
    {
      "source_format": "Y-m-d H:i:s",
      "target_format": "ISO8601",
      "conversion_success": true,
      "records_affected": 15420
    }
  ],
  "consent_structure": [
    {
      "source_structure": "serialized_array",
      "target_structure": "json_array",
      "conversion_success": true,
      "records_affected": 15420
    }
  ],
  "user_identifiers": [
    {
      "source_type": "email_hash",
      "target_type": "user_id",
      "conversion_success": false,
      "error": "Cannot reverse email hashes",
      "solution": "Use anonymous user IDs"
    }
  ]
}
```

#### Step 2: Fix Conversion Errors
```php
// Handle consent data conversion
function convertConsentData($sourceData, $sourcePlugin) {
    $convertedData = [];

    foreach ($sourceData as $record) {
        $convertedRecord = [
            'user_id' => convertUserId($record['user_id'], $sourcePlugin),
            'consent_timestamp' => convertTimestamp($record['timestamp'], $sourcePlugin),
            'consent_categories' => convertCategories($record['categories'], $sourcePlugin),
            'ip_address' => $record['ip_address'] ?? null,
            'user_agent' => $record['user_agent'] ?? null,
            'source' => 'migrated_from_' . $sourcePlugin
        ];

        // Validate converted record
        if (validateConvertedRecord($convertedRecord)) {
            $convertedData[] = $convertedRecord;
        } else {
            logConversionError($record, $convertedRecord);
        }
    }

    return $convertedData;
}
```

#### Step 3: Data Quality Assurance
```sql
-- Quality assurance queries
SELECT
    COUNT(*) as total_records,
    SUM(CASE WHEN consent_categories IS NULL THEN 1 ELSE 0 END) as null_categories,
    SUM(CASE WHEN consent_timestamp IS NULL THEN 1 ELSE 0 END) as null_timestamps,
    SUM(CASE WHEN JSON_VALID(consent_categories) = 0 THEN 1 ELSE 0 END) as invalid_json
FROM wp_slos_consent_log
WHERE source LIKE 'migrated_from_%';
```

### Post-Migration Validation

#### Step 1: Compare Data Counts
```
SLOS → Migration → Validation → Data Comparison
```

**Migration Validation Report:**
```json
{
  "source_records": 15420,
  "imported_records": 15415,
  "failed_imports": 5,
  "data_integrity": "98.5%",
  "category_distribution": {
    "essential": 15420,
    "analytics": 12450,
    "marketing": 8930,
    "preferences": 6780
  },
  "date_range": {
    "oldest_record": "2023-01-15",
    "newest_record": "2025-12-31"
  }
}
```

#### Step 2: Test Functionality
```
SLOS → Migration → Validation → Functional Testing
```

**Functional Tests:**
- [x] Consent banner displays correctly
- [x] Existing consents are recognized
- [x] New consents are recorded
- [x] Cookie categories work properly
- [x] Historical data is accessible
- [x] Reports include migrated data

### Rollback Procedures

#### Step 1: Emergency Rollback
```
SLOS → Migration → Rollback → Emergency Rollback
```

**Rollback Process:**
- Deactivate SLOS plugin
- Restore from pre-migration backup
- Reactivate source plugin
- Verify source plugin functionality
- Notify users of temporary disruption

#### Step 2: Partial Rollback
```
SLOS → Migration → Rollback → Selective Rollback
```

**Selective Rollback Options:**
```json
{
  "rollback_scope": "data_only",
  "preserve_settings": true,
  "preserve_customizations": true,
  "restore_source_plugin": false,
  "data_cleanup": {
    "remove_slos_tables": false,
    "archive_slos_data": true
  }
}
```

### Performance Issues During Migration

#### Step 1: Optimize Migration Performance
```
SLOS → Migration → Performance → Optimization Settings
```

**Performance Optimizations:**
```json
{
  "batch_size": 500,
  "memory_limit": "512M",
  "max_execution_time": 600,
  "disable_indexes_during_import": true,
  "rebuild_indexes_after_import": true,
  "progress_reporting": true
}
```

#### Step 2: Monitor Migration Progress
```
SLOS → Migration → Performance → Progress Monitor
```

**Migration Metrics:**
- Records processed per minute
- Memory usage
- Database connection status
- Error rate
- Estimated completion time

### Multi-Site Migration Issues

#### Step 1: Network Migration Setup
```
SLOS → Migration → Multi-Site → Network Configuration
```

**Multi-Site Migration:**
```json
{
  "network_migration": true,
  "site_count": 5,
  "shared_data_tables": true,
  "site_specific_settings": true,
  "sequential_migration": true,
  "rollback_per_site": true
}
```

#### Step 2: Site-by-Site Migration
```
SLOS → Migration → Multi-Site → Site Migration
```

**Per-Site Process:**
- Backup individual site
- Migrate site-specific data
- Test site functionality
- Move to next site
- Network-wide validation

### Support Resources

#### Documentation
- [Migration Guide](../How-tos/11-migrate-consent-plugin.md)
- [Data Export Procedures](../Advanced/05-database-optimization.md)
- [Import Troubleshooting](../Troubleshooting/03-database-issues.md)

#### Help
- Migration support hotline
- Data recovery services
- Plugin compatibility experts
- Emergency rollback assistance