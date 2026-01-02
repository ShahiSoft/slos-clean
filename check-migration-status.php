<?php
/**
 * Check BackupService Migration Status
 * 
 * Run this file to check if the database migration has been applied.
 * 
 * Usage: php check-migration-status.php
 * Or access via browser: http://your-site.com/wp-content/plugins/Shahi%20LegalOps%20Suite%20-%203.1.1/check-migration-status.php
 */

// Load WordPress
$wp_load_path = dirname( __FILE__ ) . '/../../../wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
	// Try alternative path for containerized environments
	$wp_load_path = '/var/www/html/wp-load.php';
}
require_once $wp_load_path;

global $wpdb;

$table_name = $wpdb->prefix . 'slos_accessibility_fix_history';

echo "=== BackupService Migration Status Check ===\n\n";

// Check if table exists
$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name;

if ( ! $table_exists ) {
	echo "❌ ERROR: Table '$table_name' does not exist!\n";
	echo "   Run migration_2025_12_29_accessibility_fix_history_table first.\n\n";
	exit( 1 );
}

echo "✅ Table '$table_name' exists.\n\n";

// Get current table structure
$columns = $wpdb->get_results( "DESCRIBE $table_name", ARRAY_A );

echo "Current Table Structure:\n";
echo str_repeat( '-', 80 ) . "\n";
printf( "%-25s %-20s %-10s %-10s\n", "Field", "Type", "Null", "Key" );
echo str_repeat( '-', 80 ) . "\n";

$has_original_content = false;
$has_metadata = false;

foreach ( $columns as $column ) {
	printf(
		"%-25s %-20s %-10s %-10s\n",
		$column['Field'],
		$column['Type'],
		$column['Null'],
		$column['Key']
	);
	
	if ( $column['Field'] === 'original_content' ) {
		$has_original_content = true;
	}
	if ( $column['Field'] === 'metadata' ) {
		$has_metadata = true;
	}
}

echo str_repeat( '-', 80 ) . "\n\n";

// Check migration status
echo "Migration Status:\n";
echo str_repeat( '-', 80 ) . "\n";

if ( $has_original_content ) {
	echo "✅ Column 'original_content' exists (Type: LONGTEXT)\n";
} else {
	echo "❌ Column 'original_content' MISSING\n";
}

if ( $has_metadata ) {
	echo "✅ Column 'metadata' exists (Type: TEXT)\n";
} else {
	echo "❌ Column 'metadata' MISSING\n";
}

echo str_repeat( '-', 80 ) . "\n\n";

// Overall status
if ( $has_original_content && $has_metadata ) {
	echo "✅ MIGRATION COMPLETE: BackupService database migration has been applied.\n";
	echo "   BackupService is ready to use.\n\n";
	
	// Check if there's any data
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE original_content IS NOT NULL" );
	echo "   Records with backup content: $count\n\n";
	
	exit( 0 );
} else {
	echo "⚠️  MIGRATION INCOMPLETE: Required columns are missing.\n\n";
	echo "To apply the migration, run:\n";
	echo "   wp slos migrate backup-service\n\n";
	echo "Or manually run the migration file:\n";
	echo "   includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php\n\n";
	
	exit( 1 );
}
