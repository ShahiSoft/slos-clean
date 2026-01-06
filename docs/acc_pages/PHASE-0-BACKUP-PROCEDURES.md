# PHASE 0: BACKUP & ROLLBACK PROCEDURES
## Accessibility Scanner Module - Comprehensive Backup Plan

**Document Version:** 1.0  
**Created:** January 6, 2026  
**Purpose:** Define backup and rollback procedures for safe reorganization  
**Status:** Foundation & Preparation Phase

---

## 🎯 BACKUP STRATEGY

### Backup Scope
This backup covers all aspects of the Accessibility Scanner module to enable complete rollback if needed.

---

## 📦 BACKUP COMPONENTS

### 1. Git Repository Backup

#### A. Commit Current State
```bash
cd "c:\docker-wp\wordpress_data\wp-content\plugins\Shahi LegalOps Suite - 3.1.1"

# Stage all changes
git add -A

# Commit current state
git commit -m "Pre-reorganization stable state - Accessibility Scanner Module baseline

- All 17/20 AJAX handlers functional
- 5 modal systems working correctly
- Dashboard tab 100% complete
- Tools tab 60% complete (6/10 sections)
- V3 Mac Slate Liquid theme consistent
- BackupService operational
- FixEngine fully functional

This commit represents the stable baseline before Phase 0 reorganization begins.
Baseline documentation: docs/acc_pages/PHASE-0-BASELINE-FUNCTIONALITY.md

Date: January 6, 2026
Status: STABLE - Ready for reorganization"

# Create tag for easy rollback
git tag -a pre-reorganization-stable -m "Stable state before accessibility reorganization

Phase: Pre-Phase 0
Date: January 6, 2026
Status: All core features verified working
AJAX Handlers: 17/20 functional
Modal Systems: 5/5 operational
Dashboard: 100% complete
Tools: 60% complete

Use this tag for rollback if reorganization fails:
git checkout pre-reorganization-stable"

# Push to remote (if applicable)
git push origin slos-newfix
git push origin pre-reorganization-stable
```

#### B. Create Backup Branch
```bash
# Create backup branch from current state
git checkout -b backup/pre-reorganization-2026-01-06

# Push backup branch
git push origin backup/pre-reorganization-2026-01-06

# Return to working branch
git checkout slos-newfix
```

### 2. File System Backup

#### A. Create Complete Plugin Backup
```powershell
# Create backup directory
$backupDir = "C:\docker-wp\backups\accessibility-scanner"
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"
$backupPath = "$backupDir\pre-reorganization-$timestamp"

New-Item -ItemType Directory -Path $backupPath -Force

# Copy entire plugin directory
$sourcePath = "C:\docker-wp\wordpress_data\wp-content\plugins\Shahi LegalOps Suite - 3.1.1"
Copy-Item -Path $sourcePath -Destination "$backupPath\plugin-full-backup" -Recurse -Force

# Create backup manifest
@"
BACKUP MANIFEST
===============
Backup Date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Backup Type: Pre-Reorganization Stable State
Plugin Version: 3.1.1
WordPress Version: (check wp-admin)

Backup Contents:
- Full plugin directory
- All PHP files
- All JavaScript files
- All CSS files
- All template files
- All documentation

Backup Location: $backupPath

Restore Instructions:
1. Stop WordPress/Apache
2. Delete current plugin directory
3. Copy backup to: wp-content/plugins/
4. Rename to: Shahi LegalOps Suite - 3.1.1
5. Restart WordPress/Apache
6. Clear WordPress transients
7. Flush rewrite rules

Git Tag: pre-reorganization-stable
Git Branch: backup/pre-reorganization-2026-01-06
"@ | Out-File "$backupPath\BACKUP-MANIFEST.txt"

Write-Host "Backup created at: $backupPath" -ForegroundColor Green
```

#### B. Critical Files Archive
```powershell
# Create compressed archive of critical files only
$criticalFiles = @(
    "includes/Admin/AccessibilityMainPage.php",
    "includes/Modules/AccessibilityScanner/AccessibilityScanner.php",
    "dist/includes/Modules/AccessibilityScanner/Admin/ScannerPage.php",
    "templates/admin/accessibility-dashboard.php",
    "templates/admin/accessibility-settings.php",
    "assets/js/slos-scanner-admin.js",
    "assets/js/slos-scan-progress.js",
    "assets/js/slos-autofix-progress.js",
    "assets/css/slos-scanner-admin.css"
)

$criticalBackupPath = "$backupPath\critical-files"
New-Item -ItemType Directory -Path $criticalBackupPath -Force

foreach ($file in $criticalFiles) {
    $source = Join-Path $sourcePath $file
    $dest = Join-Path $criticalBackupPath $file
    $destDir = Split-Path $dest -Parent
    
    if (-not (Test-Path $destDir)) {
        New-Item -ItemType Directory -Path $destDir -Force
    }
    
    if (Test-Path $source) {
        Copy-Item -Path $source -Destination $dest -Force
        Write-Host "Backed up: $file" -ForegroundColor Cyan
    }
}
```

### 3. Database Backup

#### A. Export WordPress Options
```php
<?php
/**
 * Export Accessibility Scanner Options
 * Save as: backup-scanner-options.php
 * Run once via: php backup-scanner-options.php
 */

// Bootstrap WordPress
require_once('c:/docker-wp/wordpress_data/wp-load.php');

$options_to_backup = [
    'slos_last_scan_results',
    'slos_scan_statistics',
    'slos_accessibility_scan_history',
    'slos_wcag_level',
    'slos_active_checkers',
    'slos_scan_frequency',
    'slos_scan_post_types',
    'slos_widget_position',
    'slos_widget_color_scheme',
];

$backup_data = [];

foreach ($options_to_backup as $option_name) {
    $value = get_option($option_name);
    if ($value !== false) {
        $backup_data[$option_name] = $value;
    }
}

$backup_file = __DIR__ . '/backups/accessibility-scanner/options-backup-' . date('Y-m-d_H-i-s') . '.json';
$backup_dir = dirname($backup_file);

if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));

echo "Options backup saved to: $backup_file\n";
echo "Total options backed up: " . count($backup_data) . "\n";

// Create SQL export as well
$sql_backup = "-- Accessibility Scanner Options Backup\n";
$sql_backup .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($backup_data as $option_name => $value) {
    $escaped_value = esc_sql(maybe_serialize($value));
    $sql_backup .= "DELETE FROM wp_options WHERE option_name = '$option_name';\n";
    $sql_backup .= "INSERT INTO wp_options (option_name, option_value, autoload) VALUES ('$option_name', '$escaped_value', 'yes');\n\n";
}

$sql_file = str_replace('.json', '.sql', $backup_file);
file_put_contents($sql_file, $sql_backup);

echo "SQL backup saved to: $sql_file\n";
```

#### B. Export Post Meta
```php
<?php
/**
 * Export Accessibility Scanner Post Meta
 * Save as: backup-scanner-postmeta.php
 */

require_once('c:/docker-wp/wordpress_data/wp-load.php');

global $wpdb;

$meta_keys = [
    '_slos_accessibility_scan_results',
    '_slos_backup_%',
    '_slos_autofix_enabled',
    '_slos_last_scan_date',
    '_slos_priority_level',
];

$all_meta = [];

foreach ($meta_keys as $meta_key) {
    $like_key = str_replace('%', '', $meta_key);
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE meta_key LIKE %s",
        $wpdb->esc_like($like_key) . '%'
    ));
    
    $all_meta = array_merge($all_meta, $results);
}

$backup_file = __DIR__ . '/backups/accessibility-scanner/postmeta-backup-' . date('Y-m-d_H-i-s') . '.json';

$backup_data = [];
foreach ($all_meta as $meta) {
    $backup_data[] = [
        'post_id' => $meta->post_id,
        'meta_key' => $meta->meta_key,
        'meta_value' => maybe_unserialize($meta->meta_value),
    ];
}

file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));

echo "Post meta backup saved to: $backup_file\n";
echo "Total meta entries backed up: " . count($backup_data) . "\n";

// Create SQL export
$sql_backup = "-- Accessibility Scanner Post Meta Backup\n";
$sql_backup .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($all_meta as $meta) {
    $post_id = intval($meta->post_id);
    $meta_key = esc_sql($meta->meta_key);
    $meta_value = esc_sql($meta->meta_value);
    
    $sql_backup .= "DELETE FROM wp_postmeta WHERE post_id = $post_id AND meta_key = '$meta_key';\n";
    $sql_backup .= "INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES ($post_id, '$meta_key', '$meta_value');\n\n";
}

$sql_file = str_replace('.json', '.sql', $backup_file);
file_put_contents($sql_file, $sql_backup);

echo "SQL backup saved to: $sql_file\n";
```

---

## 🔄 ROLLBACK PROCEDURES

### Scenario 1: Rollback During Development (Git Only)

**Use Case:** Issues found during Phase 1-5, need to return to baseline

```bash
# Check current status
git status

# Discard all uncommitted changes
git reset --hard HEAD

# Return to tagged stable state
git checkout pre-reorganization-stable

# Verify you're on the tag
git describe --exact-match

# Create new working branch from stable state if needed
git checkout -b fix/rollback-investigation

# Or return to original branch and reset
git checkout slos-newfix
git reset --hard pre-reorganization-stable
```

### Scenario 2: Rollback After Deployment (Full Restore)

**Use Case:** Issues found in production, need complete restoration

#### Step 1: Stop WordPress Services
```powershell
# Stop Apache/IIS
Stop-Service -Name "Apache2.4" -ErrorAction SilentlyContinue

# Or stop Docker containers if applicable
docker-compose down
```

#### Step 2: Restore Plugin Files
```powershell
$backupPath = "C:\docker-wp\backups\accessibility-scanner\pre-reorganization-2026-01-06"
$pluginPath = "C:\docker-wp\wordpress_data\wp-content\plugins\Shahi LegalOps Suite - 3.1.1"

# Remove current plugin directory
Remove-Item -Path $pluginPath -Recurse -Force

# Restore from backup
Copy-Item -Path "$backupPath\plugin-full-backup" -Destination $pluginPath -Recurse -Force

Write-Host "Plugin files restored from backup" -ForegroundColor Green
```

#### Step 3: Restore Database (Options)
```bash
# Using PHP script
cd /path/to/backup
php restore-scanner-options.php options-backup-2026-01-06_XX-XX-XX.json

# OR using SQL file
mysql -u root -p wordpress_db < options-backup-2026-01-06_XX-XX-XX.sql
```

**Restore Script (restore-scanner-options.php):**
```php
<?php
require_once('c:/docker-wp/wordpress_data/wp-load.php');

if ($argc < 2) {
    die("Usage: php restore-scanner-options.php <backup-file.json>\n");
}

$backup_file = $argv[1];

if (!file_exists($backup_file)) {
    die("Backup file not found: $backup_file\n");
}

$backup_data = json_decode(file_get_contents($backup_file), true);

if (!$backup_data) {
    die("Invalid backup file format\n");
}

$restored = 0;
foreach ($backup_data as $option_name => $value) {
    update_option($option_name, $value);
    $restored++;
    echo "Restored: $option_name\n";
}

echo "\nTotal options restored: $restored\n";
```

#### Step 4: Restore Database (Post Meta)
```php
<?php
require_once('c:/docker-wp/wordpress_data/wp-load.php');

$backup_file = $argv[1];
$backup_data = json_decode(file_get_contents($backup_file), true);

$restored = 0;
foreach ($backup_data as $meta) {
    update_post_meta(
        $meta['post_id'],
        $meta['meta_key'],
        $meta['meta_value']
    );
    $restored++;
}

echo "Total post meta restored: $restored\n";
```

#### Step 5: Clear WordPress Caches
```php
<?php
require_once('c:/docker-wp/wordpress_data/wp-load.php');

// Clear transients
global $wpdb;
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%'");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_site_transient_%'");

// Flush rewrite rules
flush_rewrite_rules(true);

// Clear object cache if available
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
}

echo "WordPress caches cleared\n";
```

#### Step 6: Restart Services
```powershell
# Restart Apache/IIS
Start-Service -Name "Apache2.4"

# Or restart Docker containers
docker-compose up -d
```

#### Step 7: Verification
```bash
# Access WordPress admin
# Navigate to: Accessibility Scanner module
# Verify:
# - Tools tab loads correctly
# - Dashboard displays data
# - AJAX handlers respond (test a scan)
# - Modals open correctly
# - No console errors
```

### Scenario 3: Partial Rollback (Specific Files)

**Use Case:** One feature broken, others working

```bash
# Restore specific file from Git tag
git show pre-reorganization-stable:path/to/file.php > path/to/file.php

# Example: Restore ScannerPage.php only
git show pre-reorganization-stable:dist/includes/Modules/AccessibilityScanner/Admin/ScannerPage.php > dist/includes/Modules/AccessibilityScanner/Admin/ScannerPage.php

# Verify restoration
git diff path/to/file.php
```

---

## 🧪 VERIFICATION PROCEDURES

### Post-Rollback Checklist

#### 1. File System Verification
```powershell
# Verify critical files exist
$criticalFiles = @(
    "includes/Admin/AccessibilityMainPage.php",
    "includes/Modules/AccessibilityScanner/AccessibilityScanner.php",
    "templates/admin/accessibility-dashboard.php"
)

foreach ($file in $criticalFiles) {
    $fullPath = "C:\docker-wp\wordpress_data\wp-content\plugins\Shahi LegalOps Suite - 3.1.1\$file"
    if (Test-Path $fullPath) {
        Write-Host "✓ $file exists" -ForegroundColor Green
    } else {
        Write-Host "✗ $file MISSING" -ForegroundColor Red
    }
}
```

#### 2. Database Verification
```php
<?php
require_once('c:/docker-wp/wordpress_data/wp-load.php');

$required_options = [
    'slos_last_scan_results',
    'slos_scan_statistics',
    'slos_accessibility_scan_history',
];

foreach ($required_options as $option) {
    $value = get_option($option);
    if ($value !== false) {
        echo "✓ $option exists\n";
    } else {
        echo "✗ $option MISSING\n";
    }
}
```

#### 3. Functional Verification
- [ ] Navigate to Tools tab - loads without errors
- [ ] Navigate to Dashboard tab - displays data
- [ ] Navigate to Settings tab - loads correctly
- [ ] Click "Start Full Scan" - modal opens
- [ ] Click "Fix All" on a page - modal opens
- [ ] Click "View Details" - modal opens with data
- [ ] Check browser console - no JavaScript errors
- [ ] Test one AJAX handler manually

---

## 📋 BACKUP SCHEDULE

### Phase 0 (Foundation)
- ✅ **Before Phase 0 starts:** Full backup + Git tag (THIS DOCUMENT)
- ✅ **After Phase 0 completes:** Commit with tag `phase-0-complete`

### Phase 1 (Pages Requiring Attention)
- 📅 **Before Phase 1 starts:** Commit with tag `pre-phase-1`
- 📅 **After each major change:** Commit with descriptive message
- 📅 **After Phase 1 completes:** Commit with tag `phase-1-complete`

### Phase 2-4 (Features)
- 📅 **Before each phase:** Create tag
- 📅 **After each phase:** Create tag
- 📅 **Daily:** Commit work in progress with descriptive messages

### Phase 5 (QA & Deploy)
- 📅 **Before deployment:** Final backup tag `pre-production-deploy`
- 📅 **After deployment:** Tag `v3.1.2-accessibility-reorganization`

---

## 🚨 EMERGENCY CONTACT

### If Rollback Fails

1. **Check Git reflog:**
   ```bash
   git reflog
   # Find commit before issues
   git reset --hard <commit-hash>
   ```

2. **Restore from file system backup:**
   - Use PowerShell scripts above
   - Manual copy if scripts fail

3. **Contact Support:**
   - Document exact error messages
   - Note steps taken
   - Provide Git commit hash
   - Include browser console errors

---

## ✅ BACKUP COMPLETION CHECKLIST

### Pre-Reorganization Backup
- [ ] Git repository committed
- [ ] Git tag `pre-reorganization-stable` created
- [ ] Backup branch created
- [ ] File system full backup completed
- [ ] Critical files archived
- [ ] WordPress options exported (JSON + SQL)
- [ ] Post meta exported (JSON + SQL)
- [ ] Backup manifest created
- [ ] Rollback procedures documented
- [ ] Verification scripts tested
- [ ] Backup location recorded

### Backup Validation
- [ ] Git tag accessible: `git checkout pre-reorganization-stable`
- [ ] File backup accessible and extractable
- [ ] Database backups readable (JSON valid)
- [ ] SQL files syntax-valid
- [ ] Restore scripts executable
- [ ] All critical files present in backup

---

**Backup Status:** Ready to Execute  
**Rollback Confidence:** HIGH  
**Estimated Recovery Time:** 15-30 minutes  
**Last Updated:** January 6, 2026
