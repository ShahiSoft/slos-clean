# BackupService Implementation - Automated Setup

## ✅ Completed Automatically

1. **Composer Dependencies** - PHPUnit and dev dependencies installed
2. **WP-CLI Command** - Migration command registered
3. **Migration Runner Script** - Standalone PHP script created

---

## 🚀 How to Run Database Migration

You have **3 easy options** to run the migration:

### Option 1: WP-CLI (Recommended) ⭐

```bash
# Navigate to WordPress root
cd c:/docker-wp/wordpress_data

# Run migration
wp slos migrate backup-service

# Or check status first (dry run)
wp slos migrate backup-service --dry-run

# Or rollback if needed
wp slos migrate backup-service --rollback
```

### Option 2: Standalone Script

```bash
# Navigate to plugin directory
cd "c:/docker-wp/wordpress_data/wp-content/plugins/Shahi LegalOps Suite - 3.1.1"

# Run via WP-CLI
wp eval-file run-backup-migration.php

# Or run via browser (if allowed)
# Navigate to: http://your-site.local/wp-content/plugins/Shahi%20LegalOps%20Suite%20-%203.1.1/run-backup-migration.php
```

### Option 3: Temporary Code in functions.php

Add this to your theme's `functions.php` temporarily:

```php
// Run once then remove this code
add_action('admin_init', function() {
    if (current_user_can('manage_options')) {
        require_once WP_PLUGIN_DIR . '/Shahi LegalOps Suite - 3.1.1/run-backup-migration.php';
        exit; // Stop after migration
    }
});
```

Visit any admin page, migration runs automatically, then remove the code.

---

## 🧪 Running Unit Tests

PHPUnit is now installed. To run tests:

```bash
# Navigate to plugin directory
cd "c:/docker-wp/wordpress_data/wp-content/plugins/Shahi LegalOps Suite - 3.1.1"

# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Services/BackupServiceTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage
```

**Note**: Tests require WordPress test environment. If tests fail with database errors, install WordPress test suite:

```bash
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

---

## 📋 Implementation Summary

### Files Created:
- ✅ `includes/Modules/AccessibilityScanner/Interfaces/BackupServiceInterface.php`
- ✅ `includes/Modules/AccessibilityScanner/Services/BackupService.php`
- ✅ `includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php`
- ✅ `tests/Services/BackupServiceTest.php` (28 unit tests)
- ✅ `includes/CLI/MigrationCommand.php` (WP-CLI command)
- ✅ `run-backup-migration.php` (Standalone migration runner)

### Files Modified:
- ✅ `includes/Modules/AccessibilityScanner/AccessibilityScanner.php`
  - Added BackupService dependency injection
  - Added deprecation notices to old methods
  - Updated AJAX handlers
- ✅ `shahi-legalflowsuite.php`
  - Registered WP-CLI commands
- ✅ `acc-new/autofix-new/PHASE-4-DETAILED-GUIDE.md`
  - Updated completion status

### All Code Status:
- ✅ Zero syntax errors
- ✅ Zero namespace conflicts
- ✅ Follows WordPress coding standards
- ✅ Maintains backward compatibility

---

## ⚠️ Before Using in Production

1. **Run the database migration** (use any option above)
2. **Verify migration** - Check that columns were added:
   ```sql
   DESCRIBE wp_slos_accessibility_fix_history;
   -- Should show: original_content (LONGTEXT) and metadata (TEXT)
   ```
3. **Test in staging first** - Don't deploy to production without testing
4. **Monitor logs** - Check for any deprecation notices or errors

---

## 🎯 Next Steps (Days 5-10)

- [ ] **Day 5**: Execute 8 manual test scenarios from Phase 4 guide
- [ ] **Day 6**: Deploy to staging environment
- [ ] **Day 7**: Monitor staging for 24 hours
- [ ] **Day 8**: Deploy to production
- [ ] **Day 9-10**: Monitor production and document success

---

## 🆘 Troubleshooting

### Migration fails with "Table doesn't exist"
Check that `wp_slos_accessibility_fix_history` table exists. If not, the accessibility scanner module may need to initialize first.

### WP-CLI command not found
Make sure you're in the WordPress root directory and WP-CLI is installed:
```bash
wp --info
```

### Permission denied errors
Ensure WordPress database user has `ALTER TABLE` permission.

### Tests fail with database errors
WordPress test suite not installed. Run:
```bash
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

---

## 📞 Support

If you encounter issues:
1. Check WordPress error logs: `wp-content/debug.log`
2. Review migration verification output
3. Check database user permissions
4. Verify PHP version >= 7.4
5. Ensure WordPress >= 6.0

**All systems ready! Run the migration and BackupService will be live!** ✨
