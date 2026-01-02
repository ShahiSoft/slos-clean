# Phase 4: Comprehensive Implementation Guide

**Document Version**: 1.1  
**Created**: January 1, 2026  
**Updated**: January 2, 2026 - FixEngine Activated  
**Status**: Production-Ready Implementation Plan  
**Approach**: One Service at a Time (Strangler Fig Pattern)

---

## Table of Contents

1. [Prerequisites & Setup](#prerequisites--setup)
2. [Service 1: BackupService](#service-1-backupservice)
3. [Service 2: ConsolidationService](#service-2-consolidationservice)
4. [Service 3: StatisticsService](#service-3-statisticsservice)
5. [Service 4: ScannerService](#service-4-scannerservice)
6. [Service 5: FixerService](#service-5-fixerservice)
7. [Controller 1: ScannerAjaxController](#controller-1-scannerajaxcontroller)
8. [Controller 2: FixerAjaxController](#controller-2-fixerajaxcontroller)
9. [Interfaces & Final Integration](#interfaces--final-integration)
10. [Cleanup & Production Deployment](#cleanup--production-deployment)

---

## Prerequisites & Setup

### 1. Environment Setup

✅ **STATUS: COMPLETED** - January 2026

**Development Environment**:
✅ Git repository verified (branch: slos-newfix)
✅ Existing vendor/ directory confirmed
✅ Root composer.json created with PHPUnit dependencies
✅ npm not required for PHP testing

**Testing Framework Setup**:
✅ PHPUnit configured via composer.json
✅ Dependencies: phpunit/phpunit ^9.0, yoast/phpunit-polyfills, brain/monkey, mockery, wp-phpunit
✅ WordPress test suite script created (bin/install-wp-tests.sh)

**Files Created**:
✅ [composer.json](../../composer.json) - Root dependency management
✅ [phpunit.xml](../../phpunit.xml) - PHPUnit configuration
✅ [tests/bootstrap.php](../../tests/bootstrap.php) - Test environment bootstrap
✅ [bin/install-wp-tests.sh](../../bin/install-wp-tests.sh) - WordPress test suite installer
✅ [docs/BASELINE-METRICS.md](../../docs/BASELINE-METRICS.md) - Performance metrics documentation

**Directories Created**:
✅ includes/Modules/AccessibilityScanner/Services/
✅ includes/Modules/AccessibilityScanner/Controllers/
✅ includes/Modules/AccessibilityScanner/Interfaces/
✅ tests/Services/
✅ tests/Controllers/
✅ tests/Integration/
✅ tests/fixtures/
✅ tests/helpers/
✅ tests/results/

**Validation**:
✅ composer.json validated (no errors)
✅ phpunit.xml syntax verified
✅ No namespace conflicts with existing code (ShahiLegalFlowSuite\Modules\AccessibilityScanner)
✅ Baseline metrics documentation template created

**Next Steps**:
- Run `composer install` to install dev dependencies
- Run `bash bin/install-wp-tests.sh wordpress_test root '' localhost latest` to set up WordPress test suite
- Collect baseline performance metrics over 7 days (see [docs/BASELINE-METRICS.md](../../docs/BASELINE-METRICS.md))
- Proceed to Service 1: BackupService implementation

---

### 1. Environment Setup (ORIGINAL INSTRUCTIONS - REFERENCE ONLY)

**Development Environment**:
```bash
# Clone to feature branch
git checkout -b feature/phase-4-architecture-refactor

# Install dependencies
composer install
npm install

# Set up local WordPress testing
wp core download --path=./wordpress-test
wp config create --dbname=test_wp --dbuser=root --dbpass=password --path=./wordpress-test
```

**Testing Framework Setup**:
```bash
# Install PHPUnit
composer require --dev phpunit/phpunit ^9.0
composer require --dev yoast/phpunit-polyfills
composer require --dev brain/monkey

# Install WordPress test suite
bash bin/install-wp-tests.sh wordpress_test root 'password' localhost latest
```

**Create phpunit.xml**:
```xml
<?xml version="1.0"?>
<phpunit
    bootstrap="tests/bootstrap.php"
    backupGlobals="false"
    colors="true"
    convertErrorsToExceptions="true"
    convertNoticesToExceptions="true"
    convertWarningsToExceptions="true"
>
    <testsuites>
        <testsuite name="AccessibilityScanner">
            <directory>tests/Services</directory>
            <directory>tests/Controllers</directory>
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">includes/Modules/AccessibilityScanner</directory>
            <exclude>
                <directory suffix=".php">includes/Modules/AccessibilityScanner/Admin</directory>
            </exclude>
        </whitelist>
    </filter>
    <logging>
        <log type="coverage-html" target="coverage"/>
        <log type="coverage-text" target="php://stdout" showUncoveredFiles="true"/>
    </logging>
</phpunit>
```

### 2. Baseline Metrics Collection

✅ **STATUS: DOCUMENTED** - Template created at [docs/BASELINE-METRICS.md](../../docs/BASELINE-METRICS.md)

**Action Required**: Add metrics collection code to production and monitor for 7 days.

**Performance Benchmarks** (Run in production for 7 days):
```php
// Add to functions.php temporarily
add_action('wp_footer', function() {
    if (is_admin() && current_user_can('manage_options')) {
        global $wpdb;
        $start_time = $_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true);
        $end_time = microtime(true);
        $memory = memory_get_peak_usage(true) / 1024 / 1024;
        
        error_log(sprintf(
            'SLOS_BASELINE: Page=%s, Time=%.3fs, Memory=%.2fMB, Queries=%d',
            $_SERVER['REQUEST_URI'],
            $end_time - $start_time,
            $memory,
            $wpdb->num_queries
        ));
    }
});
```

**Collect Baseline Data**:
- Average scan time per page
- Average autofix time per page
- Memory usage during operations
- Database query counts
- Error rates from logs

### 3. Directory Structure Setup

✅ **STATUS: COMPLETED** - All directories created successfully

```bash
# Create new directories
mkdir -p includes/Modules/AccessibilityScanner/Services
mkdir -p includes/Modules/AccessibilityScanner/Controllers
mkdir -p includes/Modules/AccessibilityScanner/Interfaces
mkdir -p tests/Services
mkdir -p tests/Controllers
mkdir -p tests/Integration
mkdir -p tests/fixtures
```

✅ Created: includes/Modules/AccessibilityScanner/Services/ (with README.md)
✅ Created: includes/Modules/AccessibilityScanner/Controllers/ (with README.md)
✅ Created: includes/Modules/AccessibilityScanner/Interfaces/ (with README.md)
✅ Created: tests/Services/
✅ Created: tests/Controllers/
✅ Created: tests/Integration/
✅ Created: tests/fixtures/
✅ Created: tests/helpers/
✅ Created: tests/results/

---

## Service 0.5: FixEngine Activation (PRE-REQUISITE)

**Completed**: January 2, 2026  
**Status**: ✅ ACTIVATED  
**Timeline**: 2 hours (completed before Service 1 Day 5 testing)

### Background

The FixEngine is a modern SOLID-architecture fixer system with 31 optimized fixers. It was previously disabled due to an obsolete PHP 7.4+ compatibility concern, but the environment runs **PHP 8.3.29**, making it fully compatible.

### What Was Done

1. ✅ **Enabled FixEngine in AccessibilityScanner.php**
   - Removed "temporarily disabled" comment
   - Added FixEngine as primary fixer system
   - Kept FixerRegistry as fallback for backward compatibility
   - Added activation date stamp (January 2, 2026)

2. ✅ **Code Changes**
   - File: [AccessibilityScanner.php#L2068](../../includes/Modules/AccessibilityScanner/AccessibilityScanner.php#L2068)
   - Logic: Try FixEngine first, fallback to FixerRegistry
   - Error handling: Catches exceptions and logs errors
   - No breaking changes: Backward compatible

3. ✅ **Bootstrap Improvements**
   - Fixed dependency loading order in Bootstrap.php
   - Interface loaded before implementations
   - Added null check for glob() result
   - Enhanced error handling

4. ✅ **Verification**
   - Created verification scripts
   - Tested fixer execution
   - Confirmed 31 fixers available
   - Verified SOLID architecture (no duplicates)
   - PHP 8.3.29 compatibility confirmed

### Benefits

- **Modern Architecture**: SOLID principles vs legacy monolith
- **Better Performance**: 31 focused fixers vs 96 with duplicates
- **Type Safety**: Full PHP 8 type hints
- **Maintainability**: Clean, testable code
- **Extensibility**: Easy to add new fixers

### Files Modified

- `includes/Modules/AccessibilityScanner/AccessibilityScanner.php` - Enabled FixEngine
- `includes/Modules/AccessibilityScanner/FixEngine/Bootstrap.php` - Fixed dependency loading

### Files Created

- `FIXENGINE-ACTIVATION-PLAN.md` - Detailed activation documentation
- `verify-fixengine.php` - Verification script
- `test-fixer-execution.php` - Execution test script

### Integration with Phase 4

**Impact on Service 5: FixerService (Weeks 10-12)**:
- FixerService will now wrap **FixEngine** instead of FixerRegistry
- Cleaner abstraction layer
- No migration work needed later
- Modern API from the start

**No Impact on Other Services**:
- Service 1 (BackupService) is independent
- Services 2-4 work with either fixer system
- Controllers can use FixEngine directly

### Next Steps

Continue with **Service 1: BackupService Day 5** testing as planned. FixEngine will handle all auto-fix operations going forward.

---

## Service 1: BackupService (Weeks 1-2)

**STATUS: MIGRATION COMPLETE - READY FOR DAY 5 TESTING** ✅ (2026-01-02)

### Implementation Checklist

- [x] Day 1: Identify all backup methods ✅ COMPLETED (2025-01-01)
- [x] Day 1: Create interface ✅ COMPLETED (2025-01-01)
- [x] Day 2: Implement BackupService ✅ COMPLETED (2025-01-01)
- [x] Day 3: Write unit tests ✅ COMPLETED (2025-01-01)
- [x] Day 4: Integration with AccessibilityScanner ✅ COMPLETED (2025-01-01)
- [x] Day 4.5: Database migration ✅ COMPLETED (2026-01-02)
- [ ] Day 5: Manual testing
- [ ] Day 6: Deploy to staging
- [ ] Day 7: Monitor staging
- [ ] Day 8: Deploy to production
- [ ] Day 9-10: Monitor production & rollback if needed

**Implementation Summary (Days 1-4.5)**:
- ✅ BackupServiceInterface.php created (135 lines, 8 methods)
- ✅ BackupService.php implemented (368 lines, zero errors)
- ✅ BackupServiceTest.php created (28 unit tests)
- ✅ **Database migration APPLIED (2026-01-02)**:
  - ✅ `original_content` column added (LONGTEXT)
  - ✅ `metadata` column added (TEXT)
  - ✅ 68 total records in table
  - ✅ 24 records already have backup content
- ✅ AccessibilityScanner.php integrated:
  - BackupService dependency injection added to init()
  - Deprecation notices added to save_content_backup(), get_content_backup(), cleanup_old_backups(), rollback_content()
  - AJAX handler ajax_check_backup_exists() updated to use BackupService
  - Maintains backward compatibility with post meta during transition
- ✅ Zero syntax errors in all modified files
- ✅ **READY FOR TESTING**: All prerequisites complete

### Day 1: Identify Backup Methods

**Search AccessibilityScanner.php for backup-related code**:

Methods to extract:
1. `save_content_backup()` - Saves original content before fixes
2. `get_content_backup()` - Retrieves backup by post ID
3. `get_latest_backup()` - Gets most recent backup
4. `restore_content_backup()` - Restores from backup
5. `delete_old_backups()` - Cleanup old backups

**Document Current Implementation**:
```bash
# Count lines in backup methods
grep -A 50 "function save_content_backup" includes/Modules/AccessibilityScanner/AccessibilityScanner.php | wc -l
grep -A 30 "function get_content_backup" includes/Modules/AccessibilityScanner/AccessibilityScanner.php | wc -l
```

### Day 1: Create Interface

**File**: `includes/Modules/AccessibilityScanner/Interfaces/BackupServiceInterface.php`

```php
<?php
/**
 * Backup Service Interface
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Interfaces
 * @since      3.2.0
 */

namespace SLOSModules\AccessibilityScanner\Interfaces;

/**
 * Interface for content backup operations
 */
interface BackupServiceInterface {
    
    /**
     * Save content backup before applying fixes
     *
     * @param int    $post_id Post ID to backup
     * @param string $content Original post content
     * @param array  $metadata Additional metadata (optional)
     * @return int|false Backup ID on success, false on failure
     */
    public function save_backup( $post_id, $content, $metadata = array() );
    
    /**
     * Get specific backup by ID
     *
     * @param int $backup_id Backup ID
     * @return array|null Backup data or null if not found
     */
    public function get_backup( $backup_id );
    
    /**
     * Get latest backup for a post
     *
     * @param int $post_id Post ID
     * @return array|null Backup data or null if not found
     */
    public function get_latest_backup( $post_id );
    
    /**
     * Get all backups for a post
     *
     * @param int $post_id Post ID
     * @param int $limit   Maximum number of backups to return (default: 10)
     * @return array Array of backup data
     */
    public function get_backups_by_post( $post_id, $limit = 10 );
    
    /**
     * Restore content from backup
     *
     * @param int      $post_id    Post ID to restore
     * @param int|null $backup_id  Specific backup ID (null = latest)
     * @return array|\WP_Error Restored backup data or error
     */
    public function restore_backup( $post_id, $backup_id = null );
    
    /**
     * Delete old backups
     *
     * @param int $days_to_keep Keep backups from last N days
     * @return int Number of backups deleted
     */
    public function cleanup_old_backups( $days_to_keep = 30 );
    
    /**
     * Check if backup exists for post
     *
     * @param int $post_id Post ID
     * @return bool True if backup exists
     */
    public function has_backup( $post_id );
    
    /**
     * Get backup statistics
     *
     * @return array Statistics about backups
     */
    public function get_statistics();
}
```

### Day 2: Implement BackupService

**File**: `includes/Modules/AccessibilityScanner/Services/BackupService.php`

```php
<?php
/**
 * Backup Service
 *
 * Handles content backup and restore operations for accessibility fixes.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Services
 * @since      3.2.0
 */

namespace SLOSModules\AccessibilityScanner\Services;

use SLOSModules\AccessibilityScanner\Interfaces\BackupServiceInterface;

/**
 * Service class for content backup operations
 */
class BackupService implements BackupServiceInterface {
    
    /**
     * Database table name (without prefix)
     *
     * @var string
     */
    private $table_name = 'slos_accessibility_fix_history';
    
    /**
     * WordPress database instance
     *
     * @var \wpdb
     */
    private $wpdb;
    
    /**
     * Constructor
     *
     * @param \wpdb|null $wpdb WordPress database instance
     */
    public function __construct( $wpdb = null ) {
        global $wpdb as $global_wpdb;
        $this->wpdb = $wpdb ?? $global_wpdb;
    }
    
    /**
     * Get full table name with prefix
     *
     * @return string
     */
    private function get_table_name() {
        return $this->wpdb->prefix . $this->table_name;
    }
    
    /**
     * Save content backup before applying fixes
     *
     * @param int    $post_id  Post ID to backup
     * @param string $content  Original post content
     * @param array  $metadata Additional metadata (optional)
     * @return int|false Backup ID on success, false on failure
     */
    public function save_backup( $post_id, $content, $metadata = array() ) {
        // Validate inputs
        if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
            return false;
        }
        
        if ( ! is_string( $content ) ) {
            return false;
        }
        
        $table = $this->get_table_name();
        
        $data = array(
            'post_id'          => (int) $post_id,
            'original_content' => $content,
            'created_at'       => current_time( 'mysql' ),
            'metadata'         => ! empty( $metadata ) ? wp_json_encode( $metadata ) : null,
        );
        
        $format = array( '%d', '%s', '%s', '%s' );
        
        $result = $this->wpdb->insert( $table, $data, $format );
        
        if ( $result === false ) {
            error_log( sprintf(
                'BackupService: Failed to save backup for post %d. Error: %s',
                $post_id,
                $this->wpdb->last_error
            ) );
            return false;
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get specific backup by ID
     *
     * @param int $backup_id Backup ID
     * @return array|null Backup data or null if not found
     */
    public function get_backup( $backup_id ) {
        if ( empty( $backup_id ) || ! is_numeric( $backup_id ) ) {
            return null;
        }
        
        $table = $this->get_table_name();
        
        $backup = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$table} WHERE id = %d",
                $backup_id
            ),
            ARRAY_A
        );
        
        if ( ! $backup ) {
            return null;
        }
        
        return $this->format_backup_data( $backup );
    }
    
    /**
     * Get latest backup for a post
     *
     * @param int $post_id Post ID
     * @return array|null Backup data or null if not found
     */
    public function get_latest_backup( $post_id ) {
        if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
            return null;
        }
        
        $table = $this->get_table_name();
        
        $backup = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$table} WHERE post_id = %d ORDER BY created_at DESC LIMIT 1",
                $post_id
            ),
            ARRAY_A
        );
        
        if ( ! $backup ) {
            return null;
        }
        
        return $this->format_backup_data( $backup );
    }
    
    /**
     * Get all backups for a post
     *
     * @param int $post_id Post ID
     * @param int $limit   Maximum number of backups to return
     * @return array Array of backup data
     */
    public function get_backups_by_post( $post_id, $limit = 10 ) {
        if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
            return array();
        }
        
        $limit = absint( $limit );
        if ( $limit === 0 ) {
            $limit = 10;
        }
        
        $table = $this->get_table_name();
        
        $backups = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$table} WHERE post_id = %d ORDER BY created_at DESC LIMIT %d",
                $post_id,
                $limit
            ),
            ARRAY_A
        );
        
        if ( ! $backups ) {
            return array();
        }
        
        return array_map( array( $this, 'format_backup_data' ), $backups );
    }
    
    /**
     * Restore content from backup
     *
     * @param int      $post_id   Post ID to restore
     * @param int|null $backup_id Specific backup ID (null = latest)
     * @return array|\WP_Error Restored backup data or error
     */
    public function restore_backup( $post_id, $backup_id = null ) {
        // Get backup
        if ( $backup_id ) {
            $backup = $this->get_backup( $backup_id );
            if ( ! $backup || (int) $backup['post_id'] !== (int) $post_id ) {
                return new \WP_Error(
                    'backup_not_found',
                    __( 'Specified backup not found or does not match post ID.', 'shahi-legalflowsuite' )
                );
            }
        } else {
            $backup = $this->get_latest_backup( $post_id );
            if ( ! $backup ) {
                return new \WP_Error(
                    'no_backup',
                    __( 'No backup found for this post.', 'shahi-legalflowsuite' )
                );
            }
        }
        
        // Restore content
        $result = wp_update_post(
            array(
                'ID'           => $post_id,
                'post_content' => $backup['original_content'],
            ),
            true
        );
        
        if ( is_wp_error( $result ) ) {
            return new \WP_Error(
                'restore_failed',
                sprintf(
                    __( 'Failed to restore content: %s', 'shahi-legalflowsuite' ),
                    $result->get_error_message()
                )
            );
        }
        
        return $backup;
    }
    
    /**
     * Delete old backups
     *
     * @param int $days_to_keep Keep backups from last N days
     * @return int Number of backups deleted
     */
    public function cleanup_old_backups( $days_to_keep = 30 ) {
        $days_to_keep = absint( $days_to_keep );
        if ( $days_to_keep === 0 ) {
            $days_to_keep = 30;
        }
        
        $table = $this->get_table_name();
        $date_threshold = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days_to_keep} days" ) );
        
        $deleted = $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$table} WHERE created_at < %s",
                $date_threshold
            )
        );
        
        if ( $deleted > 0 ) {
            error_log( sprintf(
                'BackupService: Cleaned up %d old backups (older than %d days)',
                $deleted,
                $days_to_keep
            ) );
        }
        
        return (int) $deleted;
    }
    
    /**
     * Check if backup exists for post
     *
     * @param int $post_id Post ID
     * @return bool True if backup exists
     */
    public function has_backup( $post_id ) {
        if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
            return false;
        }
        
        $table = $this->get_table_name();
        
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE post_id = %d",
                $post_id
            )
        );
        
        return (int) $count > 0;
    }
    
    /**
     * Get backup statistics
     *
     * @return array Statistics about backups
     */
    public function get_statistics() {
        $table = $this->get_table_name();
        
        $total = $this->wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
        $total_size = $this->wpdb->get_var( "SELECT SUM(LENGTH(original_content)) FROM {$table}" );
        $unique_posts = $this->wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$table}" );
        $oldest = $this->wpdb->get_var( "SELECT MIN(created_at) FROM {$table}" );
        $newest = $this->wpdb->get_var( "SELECT MAX(created_at) FROM {$table}" );
        
        return array(
            'total_backups'   => (int) $total,
            'total_size_bytes' => (int) $total_size,
            'total_size_mb'   => round( (int) $total_size / 1024 / 1024, 2 ),
            'unique_posts'    => (int) $unique_posts,
            'oldest_backup'   => $oldest,
            'newest_backup'   => $newest,
        );
    }
    
    /**
     * Format backup data for consistent output
     *
     * @param array $backup Raw backup data from database
     * @return array Formatted backup data
     */
    private function format_backup_data( $backup ) {
        if ( ! empty( $backup['metadata'] ) ) {
            $metadata = json_decode( $backup['metadata'], true );
            $backup['metadata'] = is_array( $metadata ) ? $metadata : array();
        } else {
            $backup['metadata'] = array();
        }
        
        return $backup;
    }
}
```

### Day 3: Write Unit Tests

**File**: `tests/Services/BackupServiceTest.php`

```php
<?php
/**
 * BackupService Unit Tests
 *
 * @package ShahiLegalFlowSuite
 * @subpackage Tests
 */

namespace SLOSModules\Tests\Services;

use SLOSModules\AccessibilityScanner\Services\BackupService;
use WP_UnitTestCase;

/**
 * Test BackupService class
 */
class BackupServiceTest extends WP_UnitTestCase {
    
    /**
     * BackupService instance
     *
     * @var BackupService
     */
    private $backup_service;
    
    /**
     * Test post ID
     *
     * @var int
     */
    private $test_post_id;
    
    /**
     * Set up before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->backup_service = new BackupService();
        
        // Create test post
        $this->test_post_id = $this->factory->post->create(
            array(
                'post_title'   => 'Test Post',
                'post_content' => 'Original test content',
                'post_status'  => 'publish',
            )
        );
    }
    
    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        // Delete test post
        if ( $this->test_post_id ) {
            wp_delete_post( $this->test_post_id, true );
        }
        
        // Clean up backups
        global $wpdb;
        $table = $wpdb->prefix . 'slos_accessibility_fix_history';
        $wpdb->query( "TRUNCATE TABLE {$table}" );
        
        parent::tearDown();
    }
    
    /**
     * Test save_backup creates backup
     */
    public function test_save_backup_creates_backup() {
        $content = 'Test content to backup';
        $metadata = array( 'test_key' => 'test_value' );
        
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, $content, $metadata );
        
        $this->assertIsInt( $backup_id );
        $this->assertGreaterThan( 0, $backup_id );
    }
    
    /**
     * Test save_backup validates inputs
     */
    public function test_save_backup_validates_inputs() {
        // Invalid post ID
        $result = $this->backup_service->save_backup( null, 'content' );
        $this->assertFalse( $result );
        
        $result = $this->backup_service->save_backup( 'invalid', 'content' );
        $this->assertFalse( $result );
        
        // Invalid content
        $result = $this->backup_service->save_backup( $this->test_post_id, array() );
        $this->assertFalse( $result );
    }
    
    /**
     * Test get_backup retrieves saved backup
     */
    public function test_get_backup_retrieves_saved_backup() {
        $content = 'Test content';
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, $content );
        
        $backup = $this->backup_service->get_backup( $backup_id );
        
        $this->assertIsArray( $backup );
        $this->assertEquals( $this->test_post_id, $backup['post_id'] );
        $this->assertEquals( $content, $backup['original_content'] );
    }
    
    /**
     * Test get_backup returns null for invalid ID
     */
    public function test_get_backup_returns_null_for_invalid_id() {
        $backup = $this->backup_service->get_backup( 99999 );
        $this->assertNull( $backup );
        
        $backup = $this->backup_service->get_backup( null );
        $this->assertNull( $backup );
    }
    
    /**
     * Test get_latest_backup returns most recent backup
     */
    public function test_get_latest_backup_returns_most_recent() {
        // Create multiple backups
        $backup_id_1 = $this->backup_service->save_backup( $this->test_post_id, 'Content 1' );
        sleep( 1 );
        $backup_id_2 = $this->backup_service->save_backup( $this->test_post_id, 'Content 2' );
        
        $latest = $this->backup_service->get_latest_backup( $this->test_post_id );
        
        $this->assertIsArray( $latest );
        $this->assertEquals( $backup_id_2, $latest['id'] );
        $this->assertEquals( 'Content 2', $latest['original_content'] );
    }
    
    /**
     * Test get_backups_by_post returns all backups
     */
    public function test_get_backups_by_post_returns_all_backups() {
        // Create multiple backups
        $this->backup_service->save_backup( $this->test_post_id, 'Content 1' );
        $this->backup_service->save_backup( $this->test_post_id, 'Content 2' );
        $this->backup_service->save_backup( $this->test_post_id, 'Content 3' );
        
        $backups = $this->backup_service->get_backups_by_post( $this->test_post_id );
        
        $this->assertCount( 3, $backups );
        $this->assertEquals( 'Content 3', $backups[0]['original_content'] ); // Most recent first
    }
    
    /**
     * Test get_backups_by_post respects limit
     */
    public function test_get_backups_by_post_respects_limit() {
        // Create 5 backups
        for ( $i = 1; $i <= 5; $i++ ) {
            $this->backup_service->save_backup( $this->test_post_id, "Content {$i}" );
        }
        
        $backups = $this->backup_service->get_backups_by_post( $this->test_post_id, 3 );
        
        $this->assertCount( 3, $backups );
    }
    
    /**
     * Test restore_backup restores content
     */
    public function test_restore_backup_restores_content() {
        $original_content = 'Original content';
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, $original_content );
        
        // Change post content
        wp_update_post(
            array(
                'ID'           => $this->test_post_id,
                'post_content' => 'Modified content',
            )
        );
        
        // Restore
        $result = $this->backup_service->restore_backup( $this->test_post_id, $backup_id );
        
        $this->assertIsArray( $result );
        $this->assertEquals( $original_content, $result['original_content'] );
        
        // Verify post content restored
        $post = get_post( $this->test_post_id );
        $this->assertEquals( $original_content, $post->post_content );
    }
    
    /**
     * Test restore_backup with latest backup
     */
    public function test_restore_backup_uses_latest_when_no_id_specified() {
        $this->backup_service->save_backup( $this->test_post_id, 'Content 1' );
        sleep( 1 );
        $this->backup_service->save_backup( $this->test_post_id, 'Content 2' );
        
        $result = $this->backup_service->restore_backup( $this->test_post_id );
        
        $this->assertIsArray( $result );
        $this->assertEquals( 'Content 2', $result['original_content'] );
    }
    
    /**
     * Test restore_backup returns error when no backup exists
     */
    public function test_restore_backup_returns_error_when_no_backup() {
        $result = $this->backup_service->restore_backup( $this->test_post_id );
        
        $this->assertWPError( $result );
        $this->assertEquals( 'no_backup', $result->get_error_code() );
    }
    
    /**
     * Test has_backup checks existence
     */
    public function test_has_backup_checks_existence() {
        $this->assertFalse( $this->backup_service->has_backup( $this->test_post_id ) );
        
        $this->backup_service->save_backup( $this->test_post_id, 'Content' );
        
        $this->assertTrue( $this->backup_service->has_backup( $this->test_post_id ) );
    }
    
    /**
     * Test cleanup_old_backups deletes old backups
     */
    public function test_cleanup_old_backups_deletes_old_backups() {
        global $wpdb;
        $table = $wpdb->prefix . 'slos_accessibility_fix_history';
        
        // Create backup and manually set old date
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, 'Old content' );
        
        $old_date = gmdate( 'Y-m-d H:i:s', strtotime( '-45 days' ) );
        $wpdb->update(
            $table,
            array( 'created_at' => $old_date ),
            array( 'id' => $backup_id ),
            array( '%s' ),
            array( '%d' )
        );
        
        // Create recent backup
        $this->backup_service->save_backup( $this->test_post_id, 'Recent content' );
        
        // Cleanup backups older than 30 days
        $deleted = $this->backup_service->cleanup_old_backups( 30 );
        
        $this->assertEquals( 1, $deleted );
        
        // Verify recent backup still exists
        $backups = $this->backup_service->get_backups_by_post( $this->test_post_id );
        $this->assertCount( 1, $backups );
        $this->assertEquals( 'Recent content', $backups[0]['original_content'] );
    }
    
    /**
     * Test get_statistics returns correct data
     */
    public function test_get_statistics_returns_correct_data() {
        // Create backups for multiple posts
        $post_id_2 = $this->factory->post->create();
        
        $this->backup_service->save_backup( $this->test_post_id, 'Content 1' );
        $this->backup_service->save_backup( $this->test_post_id, 'Content 2' );
        $this->backup_service->save_backup( $post_id_2, 'Content 3' );
        
        $stats = $this->backup_service->get_statistics();
        
        $this->assertIsArray( $stats );
        $this->assertEquals( 3, $stats['total_backups'] );
        $this->assertEquals( 2, $stats['unique_posts'] );
        $this->assertArrayHasKey( 'total_size_bytes', $stats );
        $this->assertArrayHasKey( 'oldest_backup', $stats );
        $this->assertArrayHasKey( 'newest_backup', $stats );
        
        wp_delete_post( $post_id_2, true );
    }
    
    /**
     * Test metadata is properly stored and retrieved
     */
    public function test_metadata_is_properly_handled() {
        $metadata = array(
            'scan_before' => array( 'issue1', 'issue2' ),
            'scan_after'  => array( 'issue3' ),
            'fixed_count' => 5,
        );
        
        $backup_id = $this->backup_service->save_backup( $this->test_post_id, 'Content', $metadata );
        $backup = $this->backup_service->get_backup( $backup_id );
        
        $this->assertIsArray( $backup['metadata'] );
        $this->assertEquals( $metadata, $backup['metadata'] );
    }
}
```

### Day 4: Integration with AccessibilityScanner

**Modify AccessibilityScanner.php to use BackupService**:

```php
<?php
// At the top of AccessibilityScanner.php
use SLOSModules\AccessibilityScanner\Services\BackupService;

class AccessibilityScanner {
    
    /**
     * Backup service instance
     *
     * @var BackupService
     */
    private $backup_service;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize backup service
        $this->backup_service = new BackupService();
        
        // ... existing code
    }
    
    /**
     * Save content backup (DEPRECATED - Use BackupService directly)
     *
     * @deprecated 3.2.0 Use BackupService::save_backup() instead
     */
    private function save_content_backup( $post_id, $content, $metadata = array() ) {
        _deprecated_function( __METHOD__, '3.2.0', 'BackupService::save_backup()' );
        return $this->backup_service->save_backup( $post_id, $content, $metadata );
    }
    
    /**
     * Get latest backup (DEPRECATED - Use BackupService directly)
     *
     * @deprecated 3.2.0 Use BackupService::get_latest_backup() instead
     */
    private function get_latest_backup( $post_id ) {
        _deprecated_function( __METHOD__, '3.2.0', 'BackupService::get_latest_backup()' );
        return $this->backup_service->get_latest_backup( $post_id );
    }
    
    /**
     * Restore content backup (DEPRECATED - Use BackupService directly)
     *
     * @deprecated 3.2.0 Use BackupService::restore_backup() instead
     */
    private function restore_content_backup( $post_id, $backup_id = null ) {
        _deprecated_function( __METHOD__, '3.2.0', 'BackupService::restore_backup()' );
        return $this->backup_service->restore_backup( $post_id, $backup_id );
    }
    
    /**
     * AJAX: Check if backup exists
     */
    public function ajax_check_backup_exists() {
        check_ajax_referer( 'slos_scanner_nonce', 'nonce' );
        
        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        if ( ! $post_id ) {
            wp_send_json_error( 'Invalid post ID' );
        }
        
        // Use BackupService directly
        if ( $this->backup_service->has_backup( $post_id ) ) {
            $backup = $this->backup_service->get_latest_backup( $post_id );
            wp_send_json_success(
                array(
                    'has_backup'  => true,
                    'backup_date' => $backup['created_at'] ?? __( 'Unknown', 'shahi-legalflowsuite' ),
                )
            );
        } else {
            wp_send_json_success( array( 'has_backup' => false ) );
        }
    }
}
```

### Day 5: Manual Testing Checklist

**Test Scenarios**:

```markdown
## BackupService Manual Test Plan

### Scenario 1: Basic Backup & Restore
- [ ] Create test post with content "Original Content"
- [ ] Run autofix on post (should create backup)
- [ ] Verify backup exists in database
- [ ] Verify post content changed
- [ ] Click "Rollback" in UI
- [ ] Verify post content restored to "Original Content"
- [ ] Check WordPress admin notices for success message

### Scenario 2: Multiple Backups
- [ ] Create post
- [ ] Run autofix 3 times (should create 3 backups)
- [ ] Verify 3 backups exist in database
- [ ] Verify get_backups_by_post returns 3 backups
- [ ] Restore from middle backup (not latest)
- [ ] Verify correct content restored

### Scenario 3: Backup Cleanup
- [ ] Create 50 test backups with varied dates
- [ ] Run cleanup_old_backups(30)
- [ ] Verify old backups deleted, recent ones kept
- [ ] Check no PHP errors in logs

### Scenario 4: Error Handling
- [ ] Try backup with invalid post ID → should return false
- [ ] Try restore with no backup → should return WP_Error
- [ ] Try restore with invalid backup ID → should return WP_Error
- [ ] Verify all errors logged appropriately

### Scenario 5: Statistics
- [ ] Create 10 backups across 5 posts
- [ ] Call get_statistics()
- [ ] Verify:
  - total_backups = 10
  - unique_posts = 5
  - total_size_bytes > 0
  - oldest/newest dates correct

### Scenario 6: Performance
- [ ] Create post with 10,000 words
- [ ] Time backup creation (should be <1 second)
- [ ] Time backup retrieval (should be <0.1 second)
- [ ] Time restore (should be <2 seconds)
- [ ] Verify memory usage acceptable (<50MB increase)

### Scenario 7: Concurrent Operations
- [ ] Open 2 browser tabs
- [ ] Run autofix in both tabs simultaneously
- [ ] Verify both backups created
- [ ] Verify no database errors
- [ ] Verify no race conditions

### Scenario 8: UI Integration
- [ ] Dashboard shows "Backup Available" indicator
- [ ] Rollback modal displays backup date correctly
- [ ] Rollback button works
- [ ] Success/error messages display correctly
- [ ] Page reloads after restore
```

### Day 6: Deploy to Staging

**Deployment Script**:

```bash
#!/bin/bash
# deploy-backup-service-staging.sh

echo "=== Deploying BackupService to Staging ==="

# 1. Backup current files
echo "1. Creating backup..."
cp -r includes/Modules/AccessibilityScanner includes/Modules/AccessibilityScanner.backup-$(date +%Y%m%d-%H%M%S)

# 2. Copy new files
echo "2. Copying new files..."
cp includes/Modules/AccessibilityScanner/Interfaces/BackupServiceInterface.php /path/to/staging/
cp includes/Modules/AccessibilityScanner/Services/BackupService.php /path/to/staging/
cp includes/Modules/AccessibilityScanner/AccessibilityScanner.php /path/to/staging/

# 3. Run database migrations if needed
echo "3. Running database migrations..."
# (No migrations needed for BackupService - uses existing table)

# 4. Clear caches
echo "4. Clearing caches..."
wp cache flush --path=/path/to/staging

# 5. Run health check
echo "5. Running health check..."
curl https://staging.example.com/wp-admin/admin-ajax.php?action=slos_health_check

echo "=== Deployment Complete ==="
echo "Monitor error logs: tail -f /path/to/staging/wp-content/debug.log"
```

### Day 7: Monitor Staging

**Monitoring Checklist**:

```markdown
## Staging Monitoring (24 hours)

### Hour 0-2 (Critical Period)
- [ ] Check PHP error log every 15 minutes
- [ ] Test all backup operations manually
- [ ] Run automated test suite
- [ ] Check database queries (should be same as before)
- [ ] Verify memory usage unchanged

### Hour 2-8 (Active Monitoring)
- [ ] Check error log every hour
- [ ] Monitor AJAX response times
- [ ] Test with real users if possible
- [ ] Check backup creation during autofix
- [ ] Verify rollback functionality

### Hour 8-24 (Passive Monitoring)
- [ ] Check error log every 4 hours
- [ ] Review any user feedback
- [ ] Check performance metrics
- [ ] Verify no memory leaks
- [ ] Confirm all tests pass

### Decision Point (24 hours)
GO: No errors, performance good, tests pass → Deploy to production
NO-GO: Any errors, performance degraded → Rollback and fix
```

### Day 8: Deploy to Production

**Production Deployment Checklist**:

```markdown
## Production Deployment - BackupService

### Pre-Deployment (30 min before)
- [ ] Announce maintenance window to users
- [ ] Backup entire WordPress installation
- [ ] Backup database
- [ ] Test backups are restorable
- [ ] Prepare rollback script
- [ ] Have team on standby

### Deployment (15 minutes)
- [ ] Enable maintenance mode
- [ ] Deploy files via secure method (rsync, git)
- [ ] Verify file permissions
- [ ] Clear all caches (object, page, CDN)
- [ ] Disable maintenance mode
- [ ] Run smoke tests

### Post-Deployment (2 hours)
- [ ] Monitor error logs continuously
- [ ] Test backup creation
- [ ] Test backup restore
- [ ] Check performance metrics
- [ ] Verify user-facing features work
- [ ] Monitor server resources

### Success Criteria
- [ ] No PHP errors in logs
- [ ] All tests pass
- [ ] Performance unchanged or improved
- [ ] No user complaints
- [ ] Backup/restore working perfectly
```

### Day 9-10: Production Monitoring & Potential Rollback

**If Issues Occur - Rollback Procedure**:

```bash
#!/bin/bash
# rollback-backup-service.sh

echo "=== ROLLING BACK BackupService ===" 

# 1. Enable maintenance mode
wp maintenance-mode activate

# 2. Restore previous files
echo "Restoring previous files..."
cp -r includes/Modules/AccessibilityScanner.backup-TIMESTAMP/* includes/Modules/AccessibilityScanner/

# 3. Clear caches
wp cache flush

# 4. Verify rollback
php -l includes/Modules/AccessibilityScanner/AccessibilityScanner.php

# 5. Disable maintenance mode
wp maintenance-mode deactivate

# 6. Notify team
echo "Rollback complete. Investigating issues..."
```

### Success Metrics

**BackupService considered successful when**:
- ✅ Zero PHP errors related to backup operations
- ✅ All 8 manual test scenarios pass
- ✅ Unit test coverage >90%
- ✅ Performance unchanged (<5% variance)
- ✅ Successful in production for 7 days

---

## Service 2: ConsolidationService (Weeks 3-4)

### Overview

Extract consolidation logic that aggregates scan results for dashboard display.

### Methods to Extract

From AccessibilityScanner.php:
1. `consolidate_scan_results()` - Main consolidation logic
2. `get_all_scanned_posts()` - Get list of scanned posts
3. `calculate_statistics()` - Calculate summary statistics
4. `ajax_consolidate_results()` - AJAX endpoint

### Interface Definition

**File**: `includes/Modules/AccessibilityScanner/Interfaces/ConsolidationServiceInterface.php`

```php
<?php
namespace SLOSModules\AccessibilityScanner\Interfaces;

interface ConsolidationServiceInterface {
    
    /**
     * Consolidate scan results from all scanned posts
     *
     * @param array|null $post_ids Specific post IDs or null for all
     * @return array Consolidation results
     */
    public function consolidate_results( $post_ids = null );
    
    /**
     * Get all posts that have been scanned
     *
     * @return array Array of post IDs
     */
    public function get_scanned_posts();
    
    /**
     * Get dashboard summary data
     *
     * @return array Summary statistics
     */
    public function get_dashboard_summary();
    
    /**
     * Get issues grouped by type
     *
     * @return array Issues by type with counts
     */
    public function get_issues_by_type();
    
    /**
     * Get issues grouped by page
     *
     * @return array Issues by page with counts
     */
    public function get_issues_by_page();
    
    /**
     * Clear cached consolidation data
     *
     * @return bool Success
     */
    public function clear_cache();
}
```

### Implementation Steps

**Same pattern as BackupService**:
- Day 1: Analyze & create interface
- Day 2: Implement ConsolidationService
- Day 3: Write unit tests
- Day 4: Integration with AccessibilityScanner
- Day 5: Manual testing
- Day 6: Deploy to staging
- Day 7: Monitor staging
- Day 8: Deploy to production
- Day 9-10: Monitor & rollback if needed

### Key Test Scenarios

1. Consolidate 100+ posts with various issue counts
2. Handle posts with no issues
3. Handle posts never scanned
4. Verify dashboard displays correct counts
5. Verify performance with large datasets
6. Test cache invalidation

---

## Service 3-5 & Controllers

**Follow same pattern for remaining services**:
- StatisticsService (Weeks 5-6)
- ScannerService (Weeks 7-9)  
- FixerService (Weeks 10-12)
- ScannerAjaxController (Weeks 13-14)
- FixerAjaxController (Weeks 15-16)

Each follows the 10-day cycle:
1. Analysis & Interface
2. Implementation
3. Unit Tests
4. Integration
5. Manual Testing
6. Staging Deployment
7. Staging Monitoring
8. Production Deployment
9-10. Production Monitoring

---

## Final Integration & Cleanup (Weeks 17-20)

### Week 17-18: Interfaces & Final Integration

**Task**: Ensure all services implement interfaces, add dependency injection

**File**: `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`

```php
<?php
namespace SLOSModules\AccessibilityScanner;

use SLOSModules\AccessibilityScanner\Services\BackupService;
use SLOSModules\AccessibilityScanner\Services\ConsolidationService;
use SLOSModules\AccessibilityScanner\Services\StatisticsService;
use SLOSModules\AccessibilityScanner\Services\ScannerService;
use SLOSModules\AccessibilityScanner\Services\FixerService;
use SLOSModules\AccessibilityScanner\Controllers\ScannerAjaxController;
use SLOSModules\AccessibilityScanner\Controllers\FixerAjaxController;

/**
 * Main AccessibilityScanner class (now thin orchestrator)
 */
class AccessibilityScanner {
    
    private $backup_service;
    private $consolidation_service;
    private $statistics_service;
    private $scanner_service;
    private $fixer_service;
    private $scanner_ajax_controller;
    private $fixer_ajax_controller;
    
    /**
     * Constructor with dependency injection
     */
    public function __construct(
        BackupService $backup_service = null,
        ConsolidationService $consolidation_service = null,
        StatisticsService $statistics_service = null,
        ScannerService $scanner_service = null,
        FixerService $fixer_service = null
    ) {
        // Use provided instances or create new ones
        $this->backup_service = $backup_service ?? new BackupService();
        $this->consolidation_service = $consolidation_service ?? new ConsolidationService();
        $this->statistics_service = $statistics_service ?? new StatisticsService();
        $this->scanner_service = $scanner_service ?? new ScannerService();
        $this->fixer_service = $fixer_service ?? new FixerService();
        
        // Initialize controllers with services
        $this->scanner_ajax_controller = new ScannerAjaxController(
            $this->scanner_service,
            $this->consolidation_service,
            $this->statistics_service
        );
        
        $this->fixer_ajax_controller = new FixerAjaxController(
            $this->fixer_service,
            $this->backup_service,
            $this->scanner_service
        );
        
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        $this->scanner_ajax_controller->register_hooks();
        $this->fixer_ajax_controller->register_hooks();
        
        // Remaining initialization...
    }
}
```

### Week 19-20: Cleanup & Documentation

**Tasks**:
1. Remove all deprecated methods
2. Update all documentation
3. Create architecture diagrams
4. Write migration guide
5. Final performance testing
6. Security audit
7. Code review
8. Production deployment

---

## Success Criteria for Phase 4

**Technical Metrics**:
- [ ] Code coverage >80% across all services
- [ ] All services <400 lines each
- [ ] Cyclomatic complexity <10 per method
- [ ] Zero SOLID violations
- [ ] Performance unchanged or improved

**Functional Metrics**:
- [ ] All existing functionality works
- [ ] Zero production errors
- [ ] All tests passing
- [ ] User satisfaction maintained

**Development Metrics**:
- [ ] Time to add new feature reduced
- [ ] Code review time reduced
- [ ] Bug fix time reduced
- [ ] Developer satisfaction improved

---

## Emergency Contacts & Resources

**Development Team**:
- Lead Developer: [Name] - [Email] - [Phone]
- Backend Developer: [Name] - [Email] - [Phone]
- QA Engineer: [Name] - [Email] - [Phone]

**Escalation Path**:
1. Check error logs
2. Run diagnostic tests
3. Contact lead developer
4. Implement rollback if needed
5. Post-mortem analysis

**Resources**:
- Error logs: `/wp-content/debug.log`
- Test suite: `vendor/bin/phpunit`
- Rollback scripts: `/scripts/rollback/`
- Documentation: `/acc-new/autofix-new/`

---

**Document End**

This comprehensive guide provides production-ready implementation details for Phase 4. Follow each service extraction carefully, test thoroughly, and deploy incrementally for zero-downtime refactoring.
