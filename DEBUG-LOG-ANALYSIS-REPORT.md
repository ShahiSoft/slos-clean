# Debug Log Analysis Report

**Generated:** January 11, 2026  
**Plugin:** Shahi LegalOps Suite v3.1.1  
**Log File:** `wp-content/debug.log` (35.6KB, 201 lines)

## Executive Summary

The WordPress debug log contains **201 log entries** with significant noise from development debug messages. The majority (60.7%) are **intentional debug messages** from the FixEngine that should be removed or conditional before production release. Additionally, there are PHP deprecation warnings (31.8%) from WordPress core that need attention.

---

## 📊 Log Entry Breakdown

| Category                       | Count | Percentage | Status                                    |
| ------------------------------ | ----- | ---------- | ----------------------------------------- |
| **SLOS [DEBUG] Messages**      | 122   | 60.7%      | ❌ **REMOVE** - Development only          |
| **PHP Deprecated Warnings**    | 64    | 31.8%      | ⚠️ **FIX** - WordPress core issues        |
| **SLOS [INFO] Messages**       | 4     | 2.0%       | ✅ **KEEP** - Legitimate info logs        |
| **Accessibility Scanner Init** | 11    | 5.5%       | ❌ **REMOVE** - Redundant verbose logging |

---

## 🔍 Why So Many Entries?

### 1. **FixEngine Debug Spam (61 entries per page load)**

Every time a page loads that uses the FixEngine, it logs registration for **61 fixers**:

```
[SLOS FixEngine] [DEBUG] Fixer registered [timestamp=..., fixer_id=animation-pause, ...]
[SLOS FixEngine] [DEBUG] Fixer registered [timestamp=..., fixer_id=aria-attribute, ...]
... (repeats 61 times)
[SLOS FixEngine] [INFO] Auto-discovered fixers [count=61]
[SLOS FixEngine] [INFO] FixEngine initialized [fixers_count=61, categories=14]
```

**Impact:** This happens on **EVERY page load** that initializes the FixEngine module.

### 2. **Accessibility Scanner Verbose Init (1 entry per page load)**

```
SLOS Accessibility Scanner: Registered 73 active checks out of 73 available.
```

**Impact:** This fires on every admin page load or when the scanner is initialized.

### 3. **PHP Deprecation Warnings (WordPress Core)**

Repeated deprecation warnings from WordPress core functions (not from this plugin):

```
PHP Deprecated: strpos(): Passing null to parameter #1 ($haystack) of type string is deprecated
PHP Deprecated: str_replace(): Passing null to parameter #3 ($subject) of type array|string is deprecated
```

**Root Cause:** These are from WordPress core `/wp-includes/functions.php` at lines 7374 and 2196. The plugin may be passing null values to WordPress functions.

---

## 🐛 Errors to Fix (Critical Issues)

### Category 1: **Database Errors** (Production-Critical)

These are legitimate error handlers but should use proper WordPress error logging:

| File                                                      | Lines                  | Issue Type                   | Severity |
| --------------------------------------------------------- | ---------------------- | ---------------------------- | -------- |
| `includes/Database/Repositories/Base_Repository.php`      | 98, 207, 229           | Database operations failing  | 🔴 HIGH  |
| `includes/Database/Repositories/Legal_Doc_Repository.php` | 303, 333, 449          | Save/update/version failures | 🔴 HIGH  |
| `includes/Database/Repositories/DSR_Repository.php`       | 95                     | Request creation failure     | 🔴 HIGH  |
| `includes/Database/MigrationManager.php`                  | 96, 173, 182, 264, 301 | Migration failures           | 🔴 HIGH  |

**Action Required:** Keep these `error_log()` calls but wrap them in `WP_DEBUG_LOG` checks.

---

### Category 2: **Service Errors** (Operational Issues)

These handle runtime errors in services:

| File                                         | Lines                                  | Issue Type                  | Severity             |
| -------------------------------------------- | -------------------------------------- | --------------------------- | -------------------- |
| `includes/Services/DSR_Export_Service.php`   | 126, 134, 178, 217, 482, 491, 699, 835 | Export failures, ZIP errors | 🟡 MEDIUM            |
| `includes/Services/DSR_Erasure_Service.php`  | 183                                    | Handler failures            | 🟡 MEDIUM            |
| `includes/Services/Base_Service.php`         | 78                                     | Generic service errors      | 🟡 MEDIUM            |
| `includes/Services/Consent_Audit_Logger.php` | 112                                    | Audit logging issues        | 🟡 MEDIUM            |
| `includes/Core/Security.php`                 | 631                                    | Security event logging      | 🟢 LOW (intentional) |

**Action Required:** Keep critical error logs, wrap non-critical ones in debug checks.

---

### Category 3: **WordPress Core Deprecation Warnings** (PHP 8.1+)

Not directly plugin errors, but the plugin is passing null values to WordPress functions:

**Affected WordPress Functions:**

- `strpos()` - Line 7374 in `wp-includes/functions.php`
- `str_replace()` - Line 2196 in `wp-includes/functions.php`

**Action Required:**

1. Audit all plugin code calling WordPress functions
2. Add null checks before passing values
3. Use null coalescing operator (`??`) to provide defaults

---

## 🗑️ Debug Messages to Remove

### Category A: **Development Debug Logs** (100% Remove)

#### **FixEngine Logger** - `includes/Modules/AccessibilityScanner/FixEngine/Logger.php`

- **Line 117:** `error_log( $log_message );`
- **Impact:** Logs every fixer registration (61 per page load!)
- **Action:** Remove or wrap in `WP_DEBUG && WP_DEBUG_LOG` check

#### **AccessibilityFixer Debug Spam** - `includes/Modules/AccessibilityScanner/Fixes/AccessibilityFixer.php`

- **Lines:** 324, 330, 334, 341, 345, 351, 352, 355, 364, 382
- **Impact:** Verbose debugging for every fix operation
- **Action:** Remove all debug `error_log()` calls

#### **Document_Generator Debug** - `includes/Services/Document_Generator.php`

- **Lines:** 322, 323, 324, 343, 436, 440, 444
- **Impact:** Template loading and save debugging
- **Action:** Remove all debug statements

#### **Placeholder_Mapper Debug** - `includes/Services/Placeholder_Mapper.php`

- **Line 639:** Unresolved placeholder logging
- **Action:** Remove or downgrade to admin notice

#### **AccessibilityScanner Init** - `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`

- **Line 1244:** Scanner initialization message
- **Action:** Remove verbose registration count

#### **DSR_Portal Activation Logs** - `includes/Modules/DSR_Portal/DSR_Portal.php`

- **Lines:** 296, 307
- **Action:** Remove module activation/deactivation logs

#### **Document_Hub_Controller Debug** - `includes/Admin/Document_Hub_Controller.php`

- **Lines:** 816, 826, 930, 940
- **Action:** Remove or wrap in debug checks

---

### Category B: **Informational Logs** (Conditional Keep)

These might be useful for debugging but should only run when `WP_DEBUG` is enabled:

| File                                                             | Line               | Message          | Action              |
| ---------------------------------------------------------------- | ------------------ | ---------------- | ------------------- |
| `includes/Core/Cron.php`                                         | 174, 185, 258, 268 | Cron job results | Wrap in debug check |
| `includes/Core/Activator.php`                                    | 214, 350           | Migration status | Wrap in debug check |
| `includes/Modules/AccessibilityScanner/AccessibilityScanner.php` | 3144               | Cleanup results  | Wrap in debug check |
| `includes/API/Base_REST_Controller.php`                          | 398                | REST API errors  | Keep but wrap       |

---

## 📝 Recommendations

### Immediate Actions (Priority 1)

1. **Remove FixEngine Logger Spam**
   - File: `includes/Modules/AccessibilityScanner/FixEngine/Logger.php:117`
   - Change from always logging to conditional:

   ```php
   // Remove or wrap:
   if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
       error_log( $log_message );
   }
   ```

2. **Remove All Development Debug Statements**
   - Search for: `error_log.*SLOS FIX_ISSUE`
   - Search for: `error_log.*Template dir`
   - Delete or comment out all development debugging

3. **Fix Null Parameter Issues**
   - Audit code passing values to WordPress `strpos()` and `str_replace()`
   - Add null coalescing: `strpos( $haystack ?? '', ... )`

### Medium Priority (Priority 2)

4. **Wrap All error_log() in Debug Checks**
   - Keep legitimate error logging
   - Wrap all in: `if ( WP_DEBUG_LOG ) { error_log(...); }`

5. **Use WordPress do_action() for Events**
   - Instead of logging "module activated", fire hooks
   - Let developers opt-in to logging via hook

### Long-term Improvements (Priority 3)

6. **Create Centralized Logger Class**
   - Replace all `error_log()` with `SLOS_Logger::log()`
   - Add log levels: DEBUG, INFO, WARNING, ERROR
   - Respect `WP_DEBUG_LOG` setting

7. **Add Admin Log Viewer**
   - Store logs in database table
   - Provide admin UI to view/filter logs
   - Auto-purge old logs

---

## 🎯 Expected Results After Cleanup

| Metric                      | Current      | After Cleanup | Improvement     |
| --------------------------- | ------------ | ------------- | --------------- |
| **Log Lines Per Page Load** | ~65          | ~0-5          | 92%+ reduction  |
| **Debug Log Size Growth**   | Fast         | Minimal       | Sustainable     |
| **Production Readiness**    | ❌ Not ready | ✅ Ready      | Production-safe |

---

## 📋 Files Requiring Changes

### **Files to Edit** (27 files)

1. `shahi-legalflowsuite.php` - Line 436 (wrap in debug check)
2. `includes/Services/DSR_Export_Service.php` - 8 error_log calls (wrap critical ones)
3. `includes/Services/DSR_Erasure_Service.php` - 1 error_log (wrap)
4. `includes/Services/Document_Generator.php` - 7 error_log calls (REMOVE ALL)
5. `includes/Services/Consent_Audit_Logger.php` - 1 error_log (wrap)
6. `includes/Services/Placeholder_Mapper.php` - 1 error_log (REMOVE)
7. `includes/Services/Base_Service.php` - 1 error_log (wrap)
8. `includes/Modules/DSR_Portal/DSR_Portal.php` - 2 error_log calls (REMOVE)
9. `includes/Modules/AccessibilityScanner/Services/BackupService.php` - 2 error_log calls (wrap)
10. `includes/Modules/AccessibilityScanner/Scanner/ScannerEngine.php` - 1 error_log (wrap)
11. `includes/Modules/AccessibilityScanner/Fixes/AccessibilityFixer.php` - 10 error_log calls (REMOVE ALL)
12. `includes/Modules/AccessibilityScanner/FixEngine/FixEngine.php` - 1 error_log (wrap)
13. `includes/Modules/AccessibilityScanner/FixEngine/Logger.php` - 1 error_log (REMOVE/WRAP) ⚠️ **HIGHEST IMPACT**
14. `includes/Modules/AccessibilityScanner/FixEngine/Migrations/DatabaseSchemaMigration.php` - 1 error_log (wrap)
15. `includes/Modules/AccessibilityScanner/AccessibilityScanner.php` - 2 error_log calls (REMOVE line 1244, wrap 3144)
16. `includes/Database/Repositories/Legal_Doc_Repository.php` - 3 error_log calls (wrap)
17. `includes/Database/Repositories/DSR_Repository.php` - 1 error_log (wrap)
18. `includes/Database/Repositories/Base_Repository.php` - 3 error_log calls (wrap)
19. `includes/Database/MigrationManager.php` - 5 error_log calls (wrap)
20. `includes/Database/Migrations/migration_2025_12_30_add_version_columns_to_consent.php` - 2 error_log calls (wrap)
21. `includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php` - 6 error_log calls (wrap)
22. `includes/Core/Security.php` - 1 error_log (wrap)
23. `includes/Core/Cron.php` - 4 error_log calls (wrap)
24. `includes/Core/Activator.php` - 2 error_log calls (wrap)
25. `includes/API/Base_REST_Controller.php` - 1 error_log (wrap)
26. `includes/Admin/Document_Hub_Controller.php` - 4 error_log calls (REMOVE or wrap)
27. `includes/Core/Hooks.php` - Lines 56, 87 (these are in documentation examples, keep as-is)

### **Files to Leave As-Is** (1 file)

- `includes/Core/Hooks.php` - Lines 56, 87 are example code in documentation strings

---

## 🔧 Suggested Helper Function

Add this to a utility file:

```php
<?php
/**
 * Conditional error logger for SLOS
 * Only logs when WP_DEBUG_LOG is enabled
 *
 * @param string $message Log message
 * @param string $level   Log level: 'error', 'warning', 'info', 'debug'
 */
function slos_log( $message, $level = 'error' ) {
    if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
        return; // Don't log in production unless explicitly enabled
    }

    // Only log debug/info when WP_DEBUG is also enabled
    if ( in_array( $level, [ 'debug', 'info' ], true ) && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
        return;
    }

    $prefix = strtoupper( $level );
    error_log( "[SLOS {$prefix}] {$message}" );
}
```

**Usage:**

```php
// Instead of: error_log('Some message');
slos_log( 'Some message', 'debug' );  // Only logs if WP_DEBUG && WP_DEBUG_LOG
slos_log( 'Critical error', 'error' ); // Logs if WP_DEBUG_LOG (even without WP_DEBUG)
```

---

## ✅ Summary

**Total Debug Entries:** 201  
**Development Debug Spam:** ~122 (60%)  
**Legitimate Errors:** ~15 (7%)  
**External PHP Deprecations:** 64 (32%)

**Action Items:**

- ❌ Remove: ~40 error_log() calls (development debugging)
- ⚠️ Wrap: ~34 error_log() calls (legitimate errors, wrap in debug checks)
- ✅ Keep As-Is: 2 (documentation examples)
- 🔧 Fix Upstream: Investigate null parameter issues causing PHP deprecations

---

**Next Steps:** Would you like me to automatically fix these issues by:

1. Creating the helper logging function
2. Removing all development debug statements
3. Wrapping legitimate error logs in debug checks
4. Creating a pull request with all changes?
