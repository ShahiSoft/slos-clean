<?php
// Quick migration runner
require_once '/var/www/html/wp-load.php';
require_once '/var/www/html/wp-content/plugins/Shahi LegalOps Suite - 3.1.1/includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php';

echo "Running migration...\n";
$result = \ShahiLegalFlowSuite\Database\Migrations\Migration_2026_01_01_add_backup_content_column::up();

if ($result) {
    echo "SUCCESS: Migration completed!\n";
    global $wpdb;
    $table = $wpdb->prefix . 'slos_accessibility_fix_history';
    $columns = $wpdb->get_results("DESCRIBE $table", ARRAY_A);
    foreach ($columns as $col) {
        if (in_array($col['Field'], ['original_content', 'metadata'])) {
            echo "  - {$col['Field']}: {$col['Type']}\n";
        }
    }
} else {
    echo "FAILED: Check error logs\n";
    global $wpdb;
    echo "Error: " . $wpdb->last_error . "\n";
}
