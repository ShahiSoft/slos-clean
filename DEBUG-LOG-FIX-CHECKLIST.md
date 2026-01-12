# Debug Log Fix Checklist

**Plugin:** Shahi LegalOps Suite v3.1.1  
**Date:** January 11, 2026  
**Total Files to Fix:** 27

---

## 🎯 Fix Priority Legend

- 🔴 **REMOVE** - Delete the error_log() entirely (development debug only)
- 🟡 **WRAP** - Keep but wrap in `if ( WP_DEBUG_LOG )` check
- 🟢 **KEEP** - Leave as-is (documentation/examples)

---

## 📁 Files Organized by Action

### Priority 1: REMOVE Development Debug (Highest Impact)

#### 1. ✅ `includes/Modules/AccessibilityScanner/FixEngine/Logger.php`

**Line 117** - 🔴 **REMOVE/WRAP**

```php
// Current:
error_log( $log_message );

// Fix: Wrap in debug check
if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
    error_log( $log_message );
}
```

**Impact:** This single line causes 61+ log entries per page load!

---

#### 2. ✅ `includes/Modules/AccessibilityScanner/Fixes/AccessibilityFixer.php`

**Lines: 324, 330, 334, 341, 345, 351, 352, 355, 364, 382** - 🔴 **REMOVE ALL**

- Line 324: `error_log( "SLOS FIX_ISSUE: Starting fix..." )`
- Line 330: `error_log( "SLOS FIX_ISSUE: Content empty..." )`
- Line 334: `error_log( 'SLOS FIX_ISSUE: Content length=...' )`
- Line 341: `error_log( "SLOS FIX_ISSUE: No fixer found..." )`
- Line 345: `error_log( 'SLOS FIX_ISSUE: Found fixer class=...' )`
- Line 351: `error_log( 'SLOS FIX_ISSUE: Fixer returned fixed_count=...' )`
- Line 352: `error_log( 'SLOS FIX_ISSUE: Content changed=...' )`
- Line 355: `error_log( 'SLOS FIX_ISSUE: Invalid fixer result' )`
- Line 364: `error_log( "SLOS FIX_ISSUE: Fallback applied..." )`
- Line 382: `error_log( 'SLOS FIX_ISSUE: Exception - ...' )` (Keep this one wrapped!)

**Action:** Remove lines 324, 330, 334, 341, 345, 351, 352, 355, 364. Wrap line 382 only.

---

#### 3. ✅ `includes/Services/Document_Generator.php`

**Lines: 322, 323, 324, 343, 436, 440, 444** - 🔴 **REMOVE ALL**

- Line 322: `error_log( 'SLOS Template dir: ...' )`
- Line 323: `error_log( 'SLOS Template file: ...' )`
- Line 324: `error_log( 'SLOS File exists: ...' )`
- Line 343: `error_log( 'SLOS Template NOT FOUND: ...' )` - **Keep wrapped**
- Line 436: `error_log( 'SLOS save_as_draft doc_data: ...' )`
- Line 440: `error_log( 'SLOS save_as_draft result: ...' )`
- Line 444: `error_log( 'SLOS save_as_draft DB error: ...' )` - **Keep wrapped**

**Action:** Remove 322, 323, 324, 436, 440. Wrap 343 and 444.

---

#### 4. ✅ `includes/Services/Placeholder_Mapper.php`

**Line 639** - 🔴 **REMOVE**

```php
// Current:
error_log( "[SLOS] Unresolved placeholder: {$field}" );

// Fix: Remove entirely or convert to admin notice
```

---

#### 5. ✅ `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`

**Line 1244** - 🔴 **REMOVE**
**Line 3144** - 🟡 **WRAP**

```php
// Line 1244 - REMOVE:
error_log( 'SLOS Accessibility Scanner: Registered ...' );

// Line 3144 - WRAP:
if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
    error_log( sprintf( 'SLOS: Cleaned up %d old accessibility fix backups', $count ) );
}
```

---

#### 6. ✅ `includes/Modules/DSR_Portal/DSR_Portal.php`

**Lines: 296, 307** - 🔴 **REMOVE**

```php
// Line 296 - REMOVE:
error_log( 'DSR Portal module activated.' );

// Line 307 - REMOVE:
error_log( 'DSR Portal module deactivated.' );
```

**Reason:** Module activation should fire hooks, not log.

---

#### 7. ✅ `includes/Admin/Document_Hub_Controller.php`

**Lines: 816, 826, 930, 940** - 🔴 **REMOVE or WRAP**

- Lines 816, 826: Error context logging - **WRAP**
- Lines 930, 940: Fatal error logging - **WRAP**

---

### Priority 2: WRAP Legitimate Error Logs

#### 8. ✅ `includes/Database/Repositories/Base_Repository.php`

**Lines: 98, 207, 229** - 🟡 **WRAP**

All database operation failures should remain but wrapped:

```php
if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
    error_log( sprintf( 'Database insert failed in %s: %s', get_class( $this ), $this->wpdb->last_error ) );
}
```

---

#### 9. ✅ `includes/Database/Repositories/Legal_Doc_Repository.php`

**Lines: 303, 333, 449** - 🟡 **WRAP**

Database save/update/version errors - wrap all three.

---

#### 10. ✅ `includes/Database/Repositories/DSR_Repository.php`

**Line 95** - 🟡 **WRAP**

DSR request creation failure - wrap.

---

#### 11. ✅ `includes/Services/DSR_Export_Service.php`

**Lines: 126, 134, 178, 217, 482, 491, 699, 835** - 🟡 **WRAP**

All export service errors - wrap all 8 instances.

---

#### 12. ✅ `includes/Services/DSR_Erasure_Service.php`

**Line 183** - 🟡 **WRAP**

Handler failure - wrap.

---

#### 13. ✅ `includes/Services/Consent_Audit_Logger.php`

**Line 112** - 🟡 **WRAP**

Audit logging error - wrap.

---

#### 14. ✅ `includes/Services/Base_Service.php`

**Line 78** - 🟡 **WRAP**

Generic service error - wrap.

---

#### 15. ✅ `includes/Modules/AccessibilityScanner/Services/BackupService.php`

**Lines: 102, 292** - 🟡 **WRAP**

Backup operation errors - wrap both.

---

#### 16. ✅ `includes/Modules/AccessibilityScanner/Scanner/ScannerEngine.php`

**Line 204** - 🟡 **WRAP**

Scanner check errors - wrap.

---

#### 17. ✅ `includes/Modules/AccessibilityScanner/FixEngine/FixEngine.php`

**Line 225** - 🟡 **WRAP**

FixEngine errors - wrap.

---

#### 18. ✅ `includes/Modules/AccessibilityScanner/FixEngine/Migrations/DatabaseSchemaMigration.php`

**Line 234** - 🟡 **WRAP**

Foreign key creation error - wrap.

---

#### 19. ✅ `includes/Database/MigrationManager.php`

**Lines: 96, 173, 182, 264, 301** - 🟡 **WRAP**

All migration errors - wrap all 5 instances.

---

#### 20. ✅ `includes/Database/Migrations/migration_2025_12_30_add_version_columns_to_consent.php`

**Lines: 88, 153** - 🟡 **WRAP**

Migration-specific errors - wrap both.

---

#### 21. ✅ `includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php`

**Lines: 50, 95, 106, 113, 151, 162** - 🟡 **WRAP**

Migration backup errors - wrap all 6 instances.

---

#### 22. ✅ `includes/Core/Security.php`

**Line 631** - 🟡 **WRAP**

Security event logging - wrap.

---

#### 23. ✅ `includes/Core/Cron.php`

**Lines: 174, 185, 258, 268** - 🟡 **WRAP**

Cron job errors - wrap all 4 instances.

---

#### 24. ✅ `includes/Core/Activator.php`

**Lines: 214, 350** - 🟡 **WRAP**

Activation/migration errors - wrap both.

---

#### 25. ✅ `includes/API/Base_REST_Controller.php`

**Line 398** - 🟡 **WRAP**

REST API errors - wrap.

---

#### 26. ✅ `shahi-legalflowsuite.php`

**Line 436** - 🟡 **WRAP**

Script blocker initialization error - wrap.

```php
// Current:
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    error_log( 'SLOS Script Blocker init error: ' . $e->getMessage() );
}

// Already wrapped! ✅ No change needed
```

---

### Priority 3: Keep As-Is (Documentation)

#### 27. ✅ `includes/Core/Hooks.php`

**Lines: 56, 87** - 🟢 **KEEP**

These are code examples in documentation strings, not actual logging calls.

---

## 📊 Summary Statistics

| Action     | Count     | Files    |
| ---------- | --------- | -------- |
| **REMOVE** | ~20 lines | 7 files  |
| **WRAP**   | ~54 lines | 20 files |
| **KEEP**   | 2 lines   | 1 file   |
| **Total**  | 76 lines  | 27 files |

---

## 🔧 Automated Fix Script

Use this search/replace pattern:

### Find:

```regex
error_log\((.*?)\);
```

### Replace (for wrapping):

```php
if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
    error_log($1);
}
```

### Files to SKIP (remove instead):

- `includes/Modules/AccessibilityScanner/FixEngine/Logger.php` (line 117)
- `includes/Modules/AccessibilityScanner/Fixes/AccessibilityFixer.php` (lines 324-364)
- `includes/Services/Document_Generator.php` (lines 322-324, 436, 440)
- `includes/Services/Placeholder_Mapper.php` (line 639)
- `includes/Modules/AccessibilityScanner/AccessibilityScanner.php` (line 1244)
- `includes/Modules/DSR_Portal/DSR_Portal.php` (lines 296, 307)

---

## ✅ Validation Checklist

After fixes:

- [ ] Search for `error_log(` in all PHP files
- [ ] Verify no unwrapped development debug logs
- [ ] Test in development (WP_DEBUG=true) - should still see errors
- [ ] Test in production (WP_DEBUG=false) - should see no debug spam
- [ ] Check debug.log size after fixes (should be minimal)
- [ ] Run PHPCS to ensure code standards compliance

---

## 🎯 Expected Outcome

**Before:**

- 201 lines in debug.log
- 122 debug entries per request cycle
- ~65 log lines per page load

**After:**

- 0-5 lines in debug.log for normal operations
- Only legitimate errors logged
- 95%+ reduction in log noise

---

**Status:** Ready for implementation
**Estimated Time:** 30-45 minutes for manual fixes  
**Risk Level:** Low (only affects logging, no functional changes)
