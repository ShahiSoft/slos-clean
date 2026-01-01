@echo off
REM BackupService Migration Runner for Windows
REM This script runs the database migration directly

echo.
echo ========================================
echo   BackupService Migration Runner
echo ========================================
echo.

cd /d "%~dp0"

php -r "define('WP_USE_THEMES', false); require_once('../../../wp-load.php'); require_once('includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php'); $m = new migration_2026_01_01_add_backup_content_column(); echo PHP_EOL . '=== Migration Info ===' . PHP_EOL . $m->info() . PHP_EOL . PHP_EOL; echo 'Checking current state...' . PHP_EOL; $v = $m->verify(); if ($v['is_applied']) { echo '✅ Migration already applied!' . PHP_EOL; foreach ($v as $k => $val) { echo '  - ' . $k . ': ' . (is_bool($val) ? ($val ? 'YES' : 'NO') : $val) . PHP_EOL; } echo PHP_EOL . 'No action needed.' . PHP_EOL; exit(0); } echo 'Migration not applied yet.' . PHP_EOL . PHP_EOL; echo 'Running migration...' . PHP_EOL; $result = $m->up(); if ($result) { echo '✅ Migration successful!' . PHP_EOL . PHP_EOL; $v2 = $m->verify(); if ($v2['is_applied']) { echo '✅ Verification passed!' . PHP_EOL; foreach ($v2 as $k => $val) { echo '  - ' . $k . ': ' . (is_bool($val) ? ($val ? 'YES' : 'NO') : $val) . PHP_EOL; } echo PHP_EOL . '=== BackupService is ready! ===' . PHP_EOL; } else { echo '⚠️ Migration ran but verification failed.' . PHP_EOL; } } else { echo '❌ Migration failed!' . PHP_EOL; }"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo Migration completed successfully!
) else (
    echo.
    echo Migration encountered an error. Check logs above.
)

echo.
pause
