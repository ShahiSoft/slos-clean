# Database Issues

## Fix Common Database Problems and Performance Issues

### Database Connection Problems

#### Step 1: Check Database Credentials
```
SLOS → Settings → Database → Connection Test
```

**Connection Test Results:**
```json
{
  "connection_status": "failed",
  "error_message": "Access denied for user 'wp_user'@'localhost'",
  "error_code": 1045,
  "suggestions": [
    "Verify database username and password",
    "Check user permissions on database",
    "Ensure database server is running",
    "Check firewall settings"
  ]
}
```

#### Step 2: Verify Database Configuration
```
SLOS → Settings → Database → Configuration
```

**Database Settings:**
```php
// wp-config.php database settings
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASSWORD', 'your_database_password');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
```

#### Step 3: Test Database Permissions
```sql
-- Test database permissions
SHOW GRANTS FOR 'wp_user'@'localhost';

-- Required permissions for SLOS
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX
ON your_database_name.* TO 'wp_user'@'localhost';
```

### Table Creation and Structure Issues

#### Step 1: Check Table Existence
```
SLOS → Settings → Database → Table Status
```

**Required SLOS Tables:**
```sql
-- Check if tables exist
SHOW TABLES LIKE 'wp_slos_%';

-- Expected tables:
-- wp_slos_consent_log
-- wp_slos_dsr_requests
-- wp_slos_cookie_scan_results
-- wp_slos_audit_log
-- wp_slos_settings
```

#### Step 2: Repair Corrupted Tables
```
SLOS → Settings → Database → Repair Tables
```

**Table Repair Process:**
```sql
-- Repair corrupted tables
REPAIR TABLE wp_slos_consent_log;
REPAIR TABLE wp_slos_dsr_requests;
REPAIR TABLE wp_slos_cookie_scan_results;

-- Check table structure
DESCRIBE wp_slos_consent_log;
DESCRIBE wp_slos_dsr_requests;
```

#### Step 3: Recreate Missing Tables
```
SLOS → Settings → Database → Recreate Tables
```

**Table Recreation:**
- Backs up existing data
- Drops corrupted tables
- Recreates tables with proper structure
- Restores data from backup
- Validates data integrity

### Data Corruption Issues

#### Step 1: Identify Corrupted Records
```
SLOS → Settings → Database → Data Integrity Check
```

**Integrity Check Results:**
```json
{
  "corrupted_records": [
    {
      "table": "wp_slos_consent_log",
      "record_id": 12345,
      "issue": "invalid_json_in_consent_categories",
      "fix_available": true
    },
    {
      "table": "wp_slos_dsr_requests",
      "record_id": 67890,
      "issue": "missing_required_fields",
      "fix_available": true
    }
  ],
  "total_corrupted": 2,
  "repair_recommended": true
}
```

#### Step 2: Repair Corrupted Data
```
SLOS → Settings → Database → Repair Data
```

**Data Repair Options:**
```json
{
  "repair_invalid_json": true,
  "fix_missing_fields": true,
  "remove_orphaned_records": true,
  "validate_foreign_keys": true,
  "create_backup_before_repair": true
}
```

#### Step 3: Data Validation
```sql
-- Validate consent log data
SELECT
    id,
    user_id,
    consent_timestamp,
    JSON_VALID(consent_categories) as valid_json,
    CASE
        WHEN consent_timestamp > NOW() THEN 'future_date'
        WHEN consent_timestamp < '2020-01-01' THEN 'too_old'
        ELSE 'valid'
    END as date_check
FROM wp_slos_consent_log
WHERE JSON_VALID(consent_categories) = 0
   OR consent_timestamp > NOW()
   OR consent_timestamp < '2020-01-01';
```

### Performance Optimization

#### Step 1: Database Index Check
```
SLOS → Settings → Database → Index Optimization
```

**Missing Indexes:**
```sql
-- Add performance indexes
CREATE INDEX idx_consent_user_timestamp ON wp_slos_consent_log (user_id, consent_timestamp);
CREATE INDEX idx_consent_categories ON wp_slos_consent_log (consent_categories(255));
CREATE INDEX idx_dsr_status_timestamp ON wp_slos_dsr_requests (status, created_timestamp);
CREATE INDEX idx_cookie_domain ON wp_slos_cookie_scan_results (domain, scan_date);
```

#### Step 2: Query Optimization
```
SLOS → Settings → Database → Query Analysis
```

**Slow Query Identification:**
```sql
-- Find slow queries
SELECT
    sql_text,
    exec_count,
    avg_timer_wait/1000000000 as avg_time_sec,
    max_timer_wait/1000000000 as max_time_sec
FROM performance_schema.events_statements_summary_by_digest
WHERE sql_text LIKE '%slos_%'
ORDER BY avg_timer_wait DESC
LIMIT 10;
```

#### Step 3: Table Optimization
```sql
-- Optimize tables for better performance
OPTIMIZE TABLE wp_slos_consent_log;
OPTIMIZE TABLE wp_slos_dsr_requests;
OPTIMIZE TABLE wp_slos_cookie_scan_results;

-- Analyze table statistics
ANALYZE TABLE wp_slos_consent_log;
ANALYZE TABLE wp_slos_dsr_requests;
```

### Storage and Disk Space Issues

#### Step 1: Check Database Size
```
SLOS → Settings → Database → Storage Analysis
```

**Database Size Report:**
```sql
-- Check database and table sizes
SELECT
    table_schema,
    table_name,
    ROUND((data_length + index_length) / 1024 / 1024, 2) as size_mb,
    table_rows
FROM information_schema.tables
WHERE table_schema = 'your_database_name'
  AND table_name LIKE 'wp_slos_%'
ORDER BY size_mb DESC;
```

#### Step 2: Data Archiving
```
SLOS → Settings → Database → Data Archiving
```

**Archiving Configuration:**
```json
{
  "archive_older_than_days": 365,
  "archive_tables": [
    "wp_slos_consent_log",
    "wp_slos_audit_log",
    "wp_slos_cookie_scan_results"
  ],
  "compression_enabled": true,
  "archive_location": "/wp-content/uploads/slos-archives/",
  "auto_cleanup_archives": true
}
```

#### Step 3: Log Rotation
```sql
-- Set up automatic log rotation
SET GLOBAL log_bin = 'OFF';
SET GLOBAL general_log = 'OFF';
SET GLOBAL slow_query_log = 'OFF';

-- Configure MySQL log rotation
[mysqld]
log-bin = /var/log/mysql/mysql-bin
expire_logs_days = 7
max_binlog_size = 100M
```

### Backup and Recovery

#### Step 1: Create Database Backup
```
SLOS → Settings → Database → Backup → Create Backup
```

**Backup Options:**
```json
{
  "backup_type": "full",
  "include_data": true,
  "include_structure": true,
  "compression": "gzip",
  "encryption": true,
  "destination": "local",
  "retention_copies": 10
}
```

#### Step 2: Test Backup Restoration
```
SLOS → Settings → Database → Backup → Test Restore
```

**Restore Test Process:**
- Creates temporary database
- Restores backup data
- Validates table structure
- Checks data integrity
- Compares record counts
- Cleans up test environment

#### Step 3: Automated Backup Schedule
```json
{
  "backup_schedule": {
    "frequency": "daily",
    "time": "02:00",
    "type": "incremental",
    "retention_days": 30,
    "remote_backup": {
      "enabled": true,
      "provider": "aws_s3",
      "bucket": "slos-database-backups",
      "region": "us-east-1"
    }
  }
}
```

### Migration and Upgrade Issues

#### Step 1: Pre-Upgrade Backup
```
SLOS → Settings → Database → Migration → Pre-Upgrade Backup
```

**Migration Preparation:**
- Full database backup
- Schema documentation
- Data validation
- Rollback plan creation
- Test environment setup

#### Step 2: Schema Migration
```
SLOS → Settings → Database → Migration → Run Migration
```

**Migration Steps:**
```sql
-- Add new columns safely
ALTER TABLE wp_slos_consent_log
ADD COLUMN IF NOT EXISTS geo_location VARCHAR(10) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS consent_version VARCHAR(10) DEFAULT '1.0';

-- Update existing records
UPDATE wp_slos_consent_log
SET consent_version = '2.0'
WHERE consent_version IS NULL;

-- Create new indexes
CREATE INDEX IF NOT EXISTS idx_consent_geo ON wp_slos_consent_log (geo_location);
```

#### Step 3: Data Migration
```php
// Migrate data between versions
function migrate_consent_data() {
    global $wpdb;

    // Migrate old format to new format
    $old_records = $wpdb->get_results("
        SELECT * FROM wp_slos_consent_log
        WHERE consent_categories LIKE 'a:%'
    ");

    foreach ($old_records as $record) {
        $old_categories = unserialize($record->consent_categories);
        $new_categories = json_encode($old_categories);

        $wpdb->update(
            'wp_slos_consent_log',
            ['consent_categories' => $new_categories],
            ['id' => $record->id]
        );
    }
}
```

### Monitoring and Alerts

#### Step 1: Set Up Database Monitoring
```
SLOS → Settings → Database → Monitoring → Configure
```

**Monitoring Metrics:**
```json
{
  "connection_pool_usage": {
    "alert_threshold": 80,
    "alert_frequency": "immediate"
  },
  "slow_query_count": {
    "alert_threshold": 10,
    "alert_frequency": "hourly"
  },
  "disk_space_usage": {
    "alert_threshold": 85,
    "alert_frequency": "daily"
  },
  "table_lock_waits": {
    "alert_threshold": 5,
    "alert_frequency": "immediate"
  }
}
```

#### Step 2: Performance Dashboard
```
SLOS → Dashboard → Database → Performance Monitor
```

**Dashboard Widgets:**
- Query response times
- Connection pool status
- Table sizes and growth
- Index usage statistics
- Backup status
- Error rates

### Troubleshooting Tools

#### Step 1: Database Diagnostic Tools
```
SLOS → Tools → Database → Diagnostics
```

**Diagnostic Reports:**
```json
{
  "connection_test": {
    "status": "passed",
    "response_time_ms": 45
  },
  "table_integrity": {
    "status": "passed",
    "checked_tables": 15,
    "corrupted_tables": 0
  },
  "index_analysis": {
    "status": "warning",
    "missing_indexes": 3,
    "unused_indexes": 1
  },
  "performance_metrics": {
    "avg_query_time_ms": 120,
    "slow_queries_count": 5,
    "cache_hit_ratio": 0.85
  }
}
```

#### Step 2: Query Profiler
```
SLOS → Tools → Database → Query Profiler
```

**Profiling Results:**
```sql
-- Profile slow queries
SET profiling = 1;
-- Run your query
SHOW PROFILES;
SHOW PROFILE FOR QUERY 1;
SET profiling = 0;
```

### Emergency Recovery

#### Step 1: Emergency Database Repair
```
SLOS → Emergency → Database → Force Repair
```

**Emergency Repair Options:**
- Force table repair (may lose data)
- Rebuild from last good backup
- Reset to default state
- Contact support for recovery

#### Step 2: Data Recovery Service
```
SLOS → Emergency → Database → Recovery Service
```

**Recovery Options:**
- Professional data recovery
- Backup restoration service
- Emergency support hotline
- On-site recovery assistance

### Prevention Best Practices

#### Regular Maintenance
- Weekly integrity checks
- Monthly optimization runs
- Quarterly backup testing
- Annual performance audits

#### Monitoring Setup
- Real-time alerting
- Performance dashboards
- Automated reporting
- Trend analysis

#### Security Measures
- Regular security updates
- Access control auditing
- Encryption at rest
- Backup encryption

### Support Resources

#### Documentation
- [Database Configuration](../Advanced/05-database-optimization.md)
- [Performance Optimization](../Troubleshooting/02-performance-optimization.md)
- [Backup Procedures](../Advanced/06-security-hardening.md)

#### Help
- Database troubleshooting FAQ
- MySQL optimization guide
- Emergency recovery procedures
- Technical support contact