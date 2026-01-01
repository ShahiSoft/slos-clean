**Manual Migration Instructions - BackupService**

Since WP-CLI is not installed and direct PHP execution has database connection issues, here are your **simple options**:

---

## ✅ Option 1: Direct SQL (Simplest - Recommended) ⭐

Run these 2 SQL commands in phpMyAdmin or your MySQL client:

```sql
ALTER TABLE `wp_slos_accessibility_fix_history` 
ADD COLUMN `original_content` LONGTEXT NULL AFTER `content_hash_after`;

ALTER TABLE `wp_slos_accessibility_fix_history` 
ADD COLUMN `metadata` TEXT NULL AFTER `original_content`;
```

**Verify it worked:**
```sql
DESCRIBE `wp_slos_accessibility_fix_history`;
```

You should see `original_content` and `metadata` columns. ✅ Done!

---

## ✅ Option 2: Admin Page (Alternative)

1. **Copy this code** to your theme's `functions.php` temporarily:

```php
// TEMPORARY: Run BackupService Migration (Remove after running once)
add_action('admin_notices', function() {
    if (!current_user_can('manage_options')) return;
    
    if (get_transient('slos_backup_migration_done')) {
        echo '<div class="notice notice-success"><p>✅ Migration completed!</p></div>';
        return;
    }
    
    if (!isset($_GET['run_backup_migration'])) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>BackupService Migration Required</strong></p>';
        echo '<p><a href="' . admin_url('?run_backup_migration=1') . '" class="button button-primary">Run Migration Now</a></p>';
        echo '</div>';
        return;
    }
    
    require_once WP_PLUGIN_DIR . '/Shahi LegalOps Suite - 3.1.1/includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php';
    
    $class = 'ShahiLegalFlowSuite\\Database\\Migrations\\migration_2026_01_01_add_backup_content_column';
    $migration = new $class();
    
    echo '<div class="notice notice-info"><p><strong>Running Migration...</strong><br>';
    
    $verify = $migration->verify();
    if ($verify['is_applied']) {
        echo '✅ Already applied!<br>';
        set_transient('slos_backup_migration_done', 1, DAY_IN_SECONDS * 30);
    } else {
        $result = $migration->up();
        if ($result && $migration->verify()['is_applied']) {
            echo '✅ Migration Successful!<br>';
            set_transient('slos_backup_migration_done', 1, DAY_IN_SECONDS * 30);
        } else {
            echo '❌ Failed! Check logs.<br>';
        }
    }
    echo '</p></div>';
});
```

2. Visit any admin page, click "Run Migration Now", then remove the code

---

## ✅ Option 3: Docker Exec (If using Docker)

If your WordPress is in Docker, run this in PowerShell:

```powershell
# Replace 'wordpress-container' with your actual container name
docker exec -it wordpress-container wp eval-file wp-content/plugins/Shahi\ LegalOps\ Suite\ -\ 3.1.1/run-backup-migration.php
```

---

## 📋 Status Summary

**What's Ready:**
- ✅ Composer dependencies installed (PHPUnit ready)
- ✅ BackupService class created (zero errors)
- ✅ BackupServiceInterface created
- ✅ Unit tests created (28 tests)
- ✅ AccessibilityScanner integrated
- ✅ Migration file created

**What's Needed:**
- ⚠️ **Run database migration** (use Option 1, 2, or 3 above)

**After Migration:**
- Test backup creation
- Run unit tests: `vendor/bin/phpunit tests/Services/BackupServiceTest.php`
- Verify in production

---

## 🔍 Verify Migration Success

After running migration, verify with this SQL:

```sql
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'wp_slos_accessibility_fix_history' 
AND COLUMN_NAME IN ('original_content', 'metadata');
```

Should return 2 rows:
- `original_content` | `longtext` | `YES`
- `metadata` | `text` | `YES`

---

## 💡 Why Direct PHP Didn't Work

Running PHP from command line outside WordPress requires:
1. Database credentials to be accessible
2. WordPress constants to be defined
3. Proper autoloading

The admin page method (Option 1) is easier because WordPress is already loaded.

---

**Recommendation**: Use **Option 1** (Direct SQL) - it's instant and guaranteed to work! 🎯

Just run the 2 ALTER TABLE commands in phpMyAdmin. Takes 30 seconds!
