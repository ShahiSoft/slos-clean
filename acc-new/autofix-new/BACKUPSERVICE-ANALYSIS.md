# Service 1: BackupService - Implementation Analysis

**Analysis Date**: January 2026  
**File**: AccessibilityScanner.php (2792 lines)  
**Status**: Day 1 - Method Identification Complete

---

## Existing Backup Methods Found

### 1. `save_content_backup()` - Line 2467
**Current Implementation**: Uses post meta (`_slos_accessibility_content_backup`)
```php
private function save_content_backup( $post_id, $content ) {
    $backup_key = '_slos_accessibility_content_backup';
    $backup = array(
        'content'    => $content,
        'timestamp'  => current_time( 'timestamp' ),
        'created_at' => current_time( 'mysql' ),
    );
    return update_post_meta( $post_id, $backup_key, $backup );
}
```
**Usage**: Lines 1404, 2125, 2365 (3 calls)
**Issues**: 
- Stores in post meta (limited to one backup per post)
- No history tracking
- No metadata support

### 2. `get_content_backup()` - Line 2487
**Current Implementation**: Retrieves from post meta
```php
private function get_content_backup( $post_id ) {
    $backup = get_post_meta( $post_id, '_slos_accessibility_content_backup', true );
    if ( empty( $backup ) || ! is_array( $backup ) ) {
        return false;
    }
    return $backup;
}
```
**Usage**: Lines 2593, 2702 (2 calls + internal use in rollback)

### 3. `delete_content_backup()` - Line 2507
**Current Implementation**: Deletes post meta
```php
private function delete_content_backup( $post_id ) {
    return delete_post_meta( $post_id, '_slos_accessibility_content_backup' );
}
```
**Usage**: Internal use in rollback and cleanup

### 4. `save_fix_history()` - Line 2517
**Current Implementation**: Saves to database table
```php
private function save_fix_history( $post_id, $fixer_id, $fixed_count, $content_before, $content_after, $issues_before = null, $issues_after = null ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'slos_accessibility_fix_history';
    // Saves hashes only, not actual content!
}
```
**Issue**: Saves content hashes but NOT actual content for restore

### 5. `cleanup_old_backups()` - Line 2555
**Current Implementation**: Cleans post meta backups with 7-day TTL
```php
private function cleanup_old_backups( $days = 7 ) {
    // Cleans post meta backups older than $days
}
```
**Usage**: Called by cron job `cron_cleanup_old_backups()`

### 6. `rollback_content()` - Line 2593
**Current Implementation**: Restores from post meta backup
```php
private function rollback_content( $post_id ) {
    $backup = $this->get_content_backup( $post_id );
    // Restores content, re-scans, logs to history
}
```

### 7. AJAX Handlers
- `ajax_check_backup_exists()` - Line 2689
- `ajax_rollback_fixes()` - Line 2658

---

## Database Schema Analysis

### Table: `slos_accessibility_fix_history`
**Created by**: migration_2025_12_29_accessibility_fix_history_table.php

```sql
CREATE TABLE wp_slos_accessibility_fix_history (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    post_id BIGINT(20) UNSIGNED NOT NULL,
    fixer_id VARCHAR(100) NOT NULL,
    fixed_count INT(11) NOT NULL DEFAULT 0,
    content_hash_before VARCHAR(64) NULL,
    content_hash_after VARCHAR(64) NULL,
    issues_before INT(11) NULL,
    issues_after INT(11) NULL,
    user_id BIGINT(20) UNSIGNED NULL,
    action VARCHAR(50) NOT NULL DEFAULT 'auto_fix',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY post_id (post_id),
    KEY fixer_id (fixer_id),
    KEY user_id (user_id),
    KEY created_at (created_at),
    KEY action (action)
)
```

**CRITICAL ISSUE**: Table stores content HASHES only, not actual content!
- Cannot restore from this table alone
- Need to modify table or use different approach

---

## Design Decisions for BackupService

### Decision 1: Data Storage Strategy
**Options**:
1. ✅ **CHOSEN**: Modify existing table to add `original_content` LONGTEXT column
2. ❌ Create new table (conflicts with existing table)
3. ❌ Continue using post meta (Phase 4-detailed-guide specifies database table)

**Rationale**: 
- Phase 4 guide expects database table storage
- Can extend existing table with backward compatibility
- Migration required before BackupService implementation

### Decision 2: Namespace Alignment
**Issue**: Phase 4 guide shows `SLOSModules\AccessibilityScanner\...` namespace
**Existing**: `ShahiLegalFlowSuite\Modules\AccessibilityScanner\...`

**Resolution**: Use EXISTING namespace for consistency:
- `ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\BackupService`
- `ShahiLegalFlowSuite\Modules\AccessibilityScanner\Interfaces\BackupServiceInterface`

### Decision 3: Backward Compatibility
**Approach**: Dual storage during transition
1. BackupService saves to database table
2. Keep old methods as deprecated wrappers
3. Gradually migrate all calls
4. Remove old methods in future version

### Decision 4: Metadata Field
**Guide shows**: JSON-encoded metadata field
**Implementation**: Add `metadata` TEXT NULL column to table

---

## Required Database Migration

### Migration: Add Content Storage to History Table

**File**: `includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php`

```sql
ALTER TABLE wp_slos_accessibility_fix_history 
ADD COLUMN original_content LONGTEXT NULL AFTER fixed_count,
ADD COLUMN metadata TEXT NULL AFTER created_at;
```

**This migration must run BEFORE BackupService is created.**

---

## Implementation Plan Adjustments

### Additional Task: Database Migration
**New Task 0**: Create and run database migration to add content storage

### Modified BackupService Methods
Based on existing usage patterns:

1. **save_backup()** - Maps to save_content_backup() + save_fix_history()
2. **get_backup()** - New functionality (get by ID)
3. **get_latest_backup()** - Maps to get_content_backup()
4. **get_backups_by_post()** - New functionality (get all for post)
5. **restore_backup()** - Maps to rollback_content()
6. **cleanup_old_backups()** - Maps to existing cleanup_old_backups()
7. **has_backup()** - New convenience method
8. **get_statistics()** - New reporting method

---

## Code Impact Analysis

### Files to Modify
1. ✅ **NEW**: `includes/Modules/AccessibilityScanner/Interfaces/BackupServiceInterface.php`
2. ✅ **NEW**: `includes/Modules/AccessibilityScanner/Services/BackupService.php`
3. ✅ **NEW**: `tests/Services/BackupServiceTest.php`
4. ✅ **NEW**: `includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php`
5. ⚠️ **MODIFY**: `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`
   - Lines to update: 1404, 2125, 2365, 2467-2730
   - Add: BackupService dependency injection
   - Add: Deprecation notices
   - Update: AJAX handlers

### Backward Compatibility Strategy
```php
// Keep old methods as deprecated wrappers
private function save_content_backup( $post_id, $content ) {
    _deprecated_function( __METHOD__, '3.2.0', 'BackupService::save_backup()' );
    return $this->backup_service->save_backup( $post_id, $content );
}
```

---

## Risk Assessment

### High Risk Items
1. ❌ **BLOCKER**: Database migration must succeed before any BackupService code
2. ⚠️ **HIGH**: Namespace mismatch in guide vs existing code
3. ⚠️ **MEDIUM**: Existing table structure doesn't support content storage

### Mitigations
1. ✅ Create migration first, test thoroughly
2. ✅ Use existing namespace consistently  
3. ✅ Add columns via migration with NULL defaults (no data loss)

---

## Testing Strategy

### Unit Tests (25+ tests required)
Based on BackupServiceTest.php in guide:
1. ✅ test_save_backup_creates_backup
2. ✅ test_save_backup_validates_inputs (3 sub-tests)
3. ✅ test_get_backup_retrieves_saved_backup
4. ✅ test_get_backup_returns_null_for_invalid_id (2 sub-tests)
5. ✅ test_get_latest_backup_returns_most_recent
6. ✅ test_get_backups_by_post_returns_all_backups
7. ✅ test_get_backups_by_post_respects_limit
8. ✅ test_restore_backup_restores_content
9. ✅ test_restore_backup_uses_latest_when_no_id_specified
10. ✅ test_restore_backup_returns_error_when_no_backup
11. ✅ test_has_backup_checks_existence
12. ✅ test_cleanup_old_backups_deletes_old_backups
13. ✅ test_get_statistics_returns_correct_data
14. ✅ test_metadata_is_properly_handled

**Total**: 14 test methods covering 25+ assertions

### Integration Tests
1. Test with actual AccessibilityScanner.php integration
2. Test AJAX handlers with BackupService
3. Test cron job cleanup with BackupService

### Manual Tests (8 scenarios from guide)
1. Basic Backup & Restore
2. Multiple Backups
3. Backup Cleanup
4. Error Handling
5. Statistics
6. Performance
7. Concurrent Operations
8. UI Integration

---

## Implementation Order (Corrected)

**Day 0** (PREREQUISITE):
1. Create database migration
2. Run migration
3. Verify table structure

**Day 1**:
1. ✅ Analyze existing methods (COMPLETE)
2. Create BackupServiceInterface
3. Document namespace strategy

**Day 2**:
1. Implement BackupService class
2. Manual method testing

**Day 3**:
1. Write 25+ unit tests
2. Run tests, fix failures

**Day 4**:
1. Integrate into AccessibilityScanner
2. Add deprecation notices
3. Update AJAX handlers

**Day 5**:
1. Execute 8 manual test scenarios
2. Verify zero errors

**Days 6-10**: Staging, Production, Monitoring (as per guide)

---

## Next Steps

1. ✅ **COMPLETE**: Day 1 analysis
2. ⏭️ **NEXT**: Create database migration (Day 0 prerequisite)
3. ⏭️ **THEN**: Create BackupServiceInterface (Day 1)
4. ⏭️ **THEN**: Implement BackupService (Day 2)

---

**Analysis Status**: ✅ COMPLETE  
**Ready for**: Database Migration Creation  
**Blockers**: None (migration is prerequisite, not blocker)
