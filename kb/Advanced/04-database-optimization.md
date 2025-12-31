# Database Optimization

## Optimize Database Performance and Storage

### Database Performance Analysis

#### Step 1: Analyze Current Performance
```
SLOS → Advanced → Database → Performance Analysis
```

**Database Performance Report:**
```json
{
  "database_size": {
    "total_size_mb": 2450,
    "consent_log_size_mb": 1800,
    "cookie_settings_size_mb": 450,
    "audit_log_size_mb": 200
  },
  "query_performance": {
    "slowest_queries": [
      {
        "query_type": "consent_lookup",
        "average_time_ms": 450,
        "call_count": 1250,
        "optimization_potential": "high"
      },
      {
        "query_type": "user_consent_history",
        "average_time_ms": 320,
        "call_count": 890,
        "optimization_potential": "medium"
      }
    ],
    "index_usage": {
      "indexes_used": 8,
      "unused_indexes": 3,
      "missing_indexes": 2
    }
  },
  "table_fragmentation": {
    "consent_log_fragmentation": "35%",
    "cookie_settings_fragmentation": "12%",
    "recommended_optimization": "rebuild_tables"
  }
}
```

#### Step 2: Identify Optimization Opportunities
```sql
-- Analyze slow queries
SELECT
    sql_text,
    exec_count,
    avg_timer_wait / 1000000000 as avg_time_sec,
    rows_examined,
    rows_sent
FROM performance_schema.events_statements_summary_by_digest
WHERE schema_name = DATABASE()
AND avg_timer_wait > 1000000000  -- Queries taking > 1 second
ORDER BY avg_timer_wait DESC
LIMIT 10;

-- Check index usage
SELECT
    object_schema,
    object_name,
    index_name,
    count_read,
    count_fetch,
    count_insert,
    count_update,
    count_delete
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE object_schema = DATABASE()
AND object_name LIKE 'wp_slos_%'
ORDER BY count_read DESC;
```

#### Step 3: Database Configuration Check
```
SLOS → Advanced → Database → Configuration Check
```

**Database Configuration Analysis:**
```json
{
  "innodb_settings": {
    "innodb_buffer_pool_size": "1GB",
    "recommended_buffer_pool": "2GB",
    "innodb_log_file_size": "256MB",
    "innodb_flush_log_at_trx_commit": 1
  },
  "query_cache": {
    "query_cache_size": "256MB",
    "query_cache_type": "ON",
    "query_cache_limit": "1MB"
  },
  "connection_settings": {
    "max_connections": 150,
    "wait_timeout": 28800,
    "interactive_timeout": 28800
  },
  "optimization_recommendations": [
    "Increase innodb_buffer_pool_size",
    "Enable query cache for read-heavy workload",
    "Add composite indexes for common queries"
  ]
}
```

### Index Optimization

#### Step 1: Analyze Current Indexes
```
SLOS → Advanced → Database → Index Analysis
```

**Index Analysis Report:**
```json
{
  "existing_indexes": [
    {
      "table": "wp_slos_consent_log",
      "index": "PRIMARY",
      "columns": ["id"],
      "usage": "high"
    },
    {
      "table": "wp_slos_consent_log",
      "index": "idx_timestamp",
      "columns": ["consent_timestamp"],
      "usage": "high"
    },
    {
      "table": "wp_slos_consent_log",
      "index": "idx_user_id",
      "columns": ["user_id"],
      "usage": "medium"
    }
  ],
  "missing_indexes": [
    {
      "table": "wp_slos_consent_log",
      "suggested_index": "idx_user_timestamp",
      "columns": ["user_id", "consent_timestamp"],
      "benefit": "Improves user consent history queries by 70%"
    },
    {
      "table": "wp_slos_consent_log",
      "suggested_index": "idx_ip_address",
      "columns": ["ip_address"],
      "benefit": "Speeds up IP-based consent lookups"
    }
  ],
  "unused_indexes": [
    {
      "table": "wp_slos_cookie_settings",
      "index": "idx_category_name",
      "last_used": "2024-10-15",
      "recommendation": "consider_removal"
    }
  ]
}
```

#### Step 2: Create Optimal Indexes
```sql
-- Add performance indexes
CREATE INDEX idx_user_timestamp ON wp_slos_consent_log (user_id, consent_timestamp DESC);
CREATE INDEX idx_ip_timestamp ON wp_slos_consent_log (ip_address, consent_timestamp);
CREATE INDEX idx_categories ON wp_slos_consent_log (consent_categories(100));
CREATE INDEX idx_source ON wp_slos_consent_log (source);

-- Composite indexes for complex queries
CREATE INDEX idx_user_category_timestamp ON wp_slos_consent_log
(user_id, consent_categories(50), consent_timestamp DESC);

-- Cookie settings indexes
CREATE INDEX idx_cookie_domain ON wp_slos_cookie_settings (cookie_domain);
CREATE INDEX idx_category_status ON wp_slos_cookie_settings (category, status);

-- Audit log indexes
CREATE INDEX idx_audit_timestamp ON wp_slos_audit_log (created_at DESC);
CREATE INDEX idx_audit_action ON wp_slos_audit_log (action, created_at DESC);
```

#### Step 3: Remove Unused Indexes
```sql
-- Identify and remove unused indexes
SELECT
    'DROP INDEX ' + i.name + ' ON ' + o.name + ';' as drop_statement
FROM sys.indexes i
INNER JOIN sys.objects o ON i.object_id = o.object_id
WHERE o.name LIKE 'wp_slos_%'
AND i.name LIKE 'idx_%'
AND i.is_primary_key = 0
AND NOT EXISTS (
    SELECT 1
    FROM sys.dm_db_index_usage_stats s
    WHERE s.object_id = i.object_id
    AND s.index_id = i.index_id
    AND s.database_id = DB_ID()
    AND (s.user_seeks > 0 OR s.user_scans > 0 OR s.user_lookups > 0)
);
```

### Table Optimization and Maintenance

#### Step 1: Table Fragmentation Analysis
```
SLOS → Advanced → Database → Table Maintenance
```

**Table Maintenance Report:**
```json
{
  "fragmented_tables": [
    {
      "table": "wp_slos_consent_log",
      "fragmentation_percent": 35,
      "page_count": 1250,
      "recommended_action": "rebuild"
    },
    {
      "table": "wp_slos_audit_log",
      "fragmentation_percent": 28,
      "page_count": 450,
      "recommended_action": "rebuild"
    }
  ],
  "table_statistics": {
    "consent_log_rows": 154200,
    "avg_row_length": 245,
    "data_free_mb": 120,
    "auto_increment_next": 154201
  }
}
```

#### Step 2: Optimize Table Structure
```sql
-- Rebuild fragmented tables
ALTER TABLE wp_slos_consent_log ENGINE = InnoDB;
ALTER TABLE wp_slos_audit_log ENGINE = InnoDB;

-- Optimize table storage
OPTIMIZE TABLE wp_slos_consent_log;
OPTIMIZE TABLE wp_slos_cookie_settings;
OPTIMIZE TABLE wp_slos_audit_log;

-- Update table statistics
ANALYZE TABLE wp_slos_consent_log;
ANALYZE TABLE wp_slos_cookie_settings;
ANALYZE TABLE wp_slos_audit_log;
```

#### Step 3: Implement Partitioning Strategy
```sql
-- Partition consent log by date for better performance
ALTER TABLE wp_slos_consent_log
PARTITION BY RANGE (YEAR(consent_timestamp)) (
    PARTITION p2023 VALUES LESS THAN (2024),
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- Partition audit log by month
ALTER TABLE wp_slos_audit_log
PARTITION BY RANGE (MONTH(created_at)) (
    PARTITION p_jan VALUES LESS THAN (2),
    PARTITION p_feb VALUES LESS THAN (3),
    PARTITION p_mar VALUES LESS THAN (4),
    PARTITION p_apr VALUES LESS THAN (5),
    PARTITION p_may VALUES LESS THAN (6),
    PARTITION p_jun VALUES LESS THAN (7),
    PARTITION p_jul VALUES LESS THAN (8),
    PARTITION p_aug VALUES LESS THAN (9),
    PARTITION p_sep VALUES LESS THAN (10),
    PARTITION p_oct VALUES LESS THAN (11),
    PARTITION p_nov VALUES LESS THAN (12),
    PARTITION p_dec VALUES LESS THAN (13)
);
```

### Query Optimization

#### Step 1: Optimize Slow Queries
```
SLOS → Advanced → Database → Query Optimization
```

**Query Optimization Analysis:**
```json
{
  "slow_queries": [
    {
      "original_query": "SELECT * FROM wp_slos_consent_log WHERE user_id = ? ORDER BY consent_timestamp DESC LIMIT 10",
      "execution_time": "450ms",
      "optimized_query": "SELECT id, consent_categories, consent_timestamp, ip_address FROM wp_slos_consent_log WHERE user_id = ? ORDER BY consent_timestamp DESC LIMIT 10",
      "improvement": "60% faster"
    },
    {
      "original_query": "SELECT COUNT(*) FROM wp_slos_consent_log WHERE consent_timestamp >= ? AND consent_categories LIKE '%analytics%'",
      "execution_time": "320ms",
      "optimized_query": "SELECT COUNT(*) FROM wp_slos_consent_log WHERE consent_timestamp >= ? AND FIND_IN_SET('analytics', consent_categories)",
      "improvement": "75% faster"
    }
  ],
  "query_patterns": {
    "consent_history": "High frequency, needs optimization",
    "bulk_exports": "Memory intensive, needs chunking",
    "real_time_lookups": "Latency sensitive, needs caching"
  }
}
```

#### Step 2: Implement Query Caching
```php
// Query result caching
class DatabaseQueryCache {
    private $cache_group = 'slos_db_queries';
    private $cache_expiration = 300; // 5 minutes

    public function getCachedQuery($query, $params = []) {
        $cache_key = $this->generateCacheKey($query, $params);
        return wp_cache_get($cache_key, $this->cache_group);
    }

    public function setCachedQuery($query, $params = [], $results) {
        $cache_key = $this->generateCacheKey($query, $params);
        wp_cache_set($cache_key, $results, $this->cache_group, $this->cache_expiration);
    }

    private function generateCacheKey($query, $params) {
        return 'query_' . md5($query . serialize($params));
    }

    public function invalidateCache($table = null) {
        if ($table) {
            // Invalidate cache for specific table
            wp_cache_delete("table_{$table}", $this->cache_group);
        } else {
            // Clear all query cache
            wp_cache_flush_group($this->cache_group);
        }
    }
}

// Usage example
$cache = new DatabaseQueryCache();
$results = $cache->getCachedQuery("user_consent_history", [$user_id]);

if (!$results) {
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT id, consent_categories, consent_timestamp
         FROM wp_slos_consent_log
         WHERE user_id = %d
         ORDER BY consent_timestamp DESC
         LIMIT 10",
        $user_id
    ));
    $cache->setCachedQuery("user_consent_history", [$user_id], $results);
}
```

#### Step 3: Database Connection Optimization
```php
// Connection pooling and optimization
class DatabaseConnectionManager {
    private static $instance = null;
    private $connections = [];

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection($type = 'read') {
        if (!isset($this->connections[$type])) {
            $this->connections[$type] = $this->createConnection($type);
        }
        return $this->connections[$type];
    }

    private function createConnection($type) {
        global $wpdb;

        // For read operations, use replica if available
        if ($type === 'read' && defined('DB_READ_HOST')) {
            return new wpdb(DB_USER, DB_PASSWORD, DB_NAME, DB_READ_HOST);
        }

        return $wpdb;
    }

    public function optimizeConnection($connection) {
        // Set optimal connection settings
        $connection->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES'");
        $connection->query("SET SESSION innodb_lock_wait_timeout = 50");
        $connection->query("SET SESSION max_execution_time = 30000");
    }
}
```

### Data Archiving Strategy

#### Step 1: Analyze Data Retention Needs
```
SLOS → Advanced → Database → Data Archiving
```

**Data Archiving Analysis:**
```json
{
  "retention_requirements": {
    "consent_data": "7_years_gdpr",
    "audit_logs": "3_years",
    "cookie_scans": "2_years",
    "performance_logs": "1_year"
  },
  "data_volume_analysis": {
    "oldest_record": "2020-01-15",
    "records_older_than_2_years": 45000,
    "records_older_than_5_years": 12000,
    "estimated_archive_size_mb": 850
  },
  "archiving_strategy": {
    "method": "partition_based",
    "frequency": "quarterly",
    "compression": "gzip",
    "storage": "separate_database"
  }
}
```

#### Step 2: Implement Automated Archiving
```sql
-- Create archive tables
CREATE TABLE wp_slos_consent_log_archive LIKE wp_slos_consent_log;
CREATE TABLE wp_slos_audit_log_archive LIKE wp_slos_audit_log;

-- Add archive metadata columns
ALTER TABLE wp_slos_consent_log_archive
ADD COLUMN archive_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN archive_reason VARCHAR(100) DEFAULT 'retention_policy';

-- Automated archiving procedure
DELIMITER //

CREATE PROCEDURE archive_old_consent_data()
BEGIN
    DECLARE archive_cutoff DATE;
    SET archive_cutoff = DATE_SUB(CURDATE(), INTERVAL 2 YEAR);

    -- Move old data to archive
    INSERT INTO wp_slos_consent_log_archive
    SELECT *, NOW(), 'retention_policy_2_years'
    FROM wp_slos_consent_log
    WHERE consent_timestamp < archive_cutoff;

    -- Remove archived data from main table
    DELETE FROM wp_slos_consent_log
    WHERE consent_timestamp < archive_cutoff;

    -- Optimize main table after archiving
    OPTIMIZE TABLE wp_slos_consent_log;
END //

DELIMITER ;
```

#### Step 3: Set Up Archive Maintenance
```php
// Archive maintenance scheduler
function scheduleArchiveMaintenance() {
    if (!wp_next_scheduled('slos_archive_maintenance')) {
        wp_schedule_event(time(), 'weekly', 'slos_archive_maintenance');
    }
}

add_action('slos_archive_maintenance', 'performArchiveMaintenance');

function performArchiveMaintenance() {
    global $wpdb;

    // Compress old archive partitions
    $wpdb->query("ALTER TABLE wp_slos_consent_log_archive PARTITION p2020, p2021 COMPRESS");

    // Update archive statistics
    updateArchiveStatistics();

    // Clean up temporary archive files
    cleanupTemporaryArchives();

    // Send maintenance report
    sendMaintenanceReport();
}

function updateArchiveStatistics() {
    global $wpdb;

    $stats = $wpdb->get_row("
        SELECT
            COUNT(*) as total_archived_records,
            MIN(consent_timestamp) as oldest_record,
            MAX(consent_timestamp) as newest_record,
            SUM(LENGTH(consent_categories)) as data_size_bytes
        FROM wp_slos_consent_log_archive
    ");

    update_option('slos_archive_stats', $stats);
}
```

### Performance Monitoring Setup

#### Step 1: Configure Performance Monitoring
```
SLOS → Advanced → Database → Performance Monitoring
```

**Performance Monitoring Configuration:**
```json
{
  "monitoring_metrics": [
    "query_execution_time",
    "connection_count",
    "table_lock_waits",
    "slow_query_count",
    "cache_hit_ratio",
    "index_usage_stats"
  ],
  "alert_thresholds": {
    "slow_query_threshold_ms": 1000,
    "max_connections_threshold": 80,
    "lock_wait_threshold_ms": 5000
  },
  "monitoring_schedule": {
    "real_time": "query_performance",
    "hourly": "connection_stats",
    "daily": "index_analysis",
    "weekly": "table_maintenance"
  }
}
```

#### Step 2: Implement Performance Alerts
```php
// Database performance monitoring
class DatabasePerformanceMonitor {
    private $alerts = [];

    public function monitorQueryPerformance() {
        global $wpdb;

        // Check for slow queries
        $slow_queries = $wpdb->get_results("
            SELECT
                sql_text,
                exec_count,
                avg_timer_wait / 1000000000 as avg_time_sec
            FROM performance_schema.events_statements_summary_by_digest
            WHERE schema_name = DATABASE()
            AND avg_timer_wait > 1000000000
            ORDER BY avg_timer_wait DESC
            LIMIT 5
        ");

        foreach ($slow_queries as $query) {
            if ($query->avg_time_sec > 1) { // Alert on queries > 1 second
                $this->addAlert('slow_query', [
                    'query' => substr($query->sql_text, 0, 100) . '...',
                    'avg_time' => $query->avg_time_sec,
                    'exec_count' => $query->exec_count
                ]);
            }
        }
    }

    public function monitorConnections() {
        global $wpdb;

        $connection_count = $wpdb->get_var("
            SELECT COUNT(*)
            FROM information_schema.processlist
            WHERE db = DATABASE()
        ");

        $max_connections = ini_get('mysqli.max_connections') ?: 150;

        if ($connection_count > ($max_connections * 0.8)) {
            $this->addAlert('high_connection_count', [
                'current' => $connection_count,
                'max' => $max_connections,
                'percentage' => ($connection_count / $max_connections) * 100
            ]);
        }
    }

    private function addAlert($type, $data) {
        $this->alerts[] = [
            'type' => $type,
            'data' => $data,
            'timestamp' => current_time('mysql'),
            'severity' => $this->calculateSeverity($type, $data)
        ];
    }

    private function calculateSeverity($type, $data) {
        switch ($type) {
            case 'slow_query':
                return $data['avg_time'] > 5 ? 'critical' : 'warning';
            case 'high_connection_count':
                return $data['percentage'] > 90 ? 'critical' : 'warning';
            default:
                return 'info';
        }
    }

    public function getAlerts() {
        return $this->alerts;
    }

    public function sendAlerts() {
        $alerts = $this->getAlerts();
        if (empty($alerts)) return;

        $email_content = "Database Performance Alerts:\n\n";
        foreach ($alerts as $alert) {
            $email_content .= "[{$alert['severity']}] {$alert['type']}: " .
                            json_encode($alert['data']) . "\n";
        }

        wp_mail(get_option('admin_email'), 'SLOS Database Performance Alert', $email_content);
    }
}
```

#### Step 3: Database Backup Optimization
```bash
# Optimized backup script
#!/bin/bash

# Database backup with compression and progress
mysqldump \
    --user=${DB_USER} \
    --password=${DB_PASSWORD} \
    --host=${DB_HOST} \
    --single-transaction \
    --quick \
    --compress \
    --databases ${DB_NAME} \
    --tables wp_slos_consent_log wp_slos_cookie_settings wp_slos_audit_log \
    --where="consent_timestamp >= DATE_SUB(NOW(), INTERVAL 1 YEAR)" \
    | gzip > slos_backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Verify backup integrity
gunzip -c slos_backup_*.sql.gz | mysql \
    --user=${DB_USER} \
    --password=${DB_PASSWORD} \
    --host=${DB_HOST} \
    -e "SELECT COUNT(*) FROM wp_slos_consent_log;" ${DB_NAME}

echo "Backup completed and verified"
```

### Support Resources

#### Documentation
- [Database Schema Reference](../Advanced/01-rest-api.md#database-schema)
- [Performance Tuning Guide](../Troubleshooting/02-performance-optimization.md)
- [Backup Procedures](../How-tos/12-plugin-updates.md#backup-strategy)

#### Help
- Database optimization specialists
- Performance tuning consultation
- Emergency database recovery
- Query optimization experts