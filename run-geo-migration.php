<?php
/**
 * Helper Script: Run Geo Rules Migration
 *
 * This script manually runs the migration_2025_12_22_add_geo_rule_to_consent migration
 * to add country_code, region, and geo_rule_id columns to the wp_slos_consent table.
 *
 * Usage: wp eval-file run-geo-migration.php
 * Or: Access via wp-admin/admin.php?page=slos-run-geo-migration (requires admin)
 *
 * @package ShahiLegalFlowSuite
 * @since   3.1.1
 */

// Load WordPress if not already loaded
if ( ! defined( 'ABSPATH' ) ) {
	// Try to load WordPress
	$wp_load = dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';
	if ( file_exists( $wp_load ) ) {
		require_once $wp_load;
	} else {
		die( 'WordPress not found. Run via WP-CLI: wp eval-file run-geo-migration.php' );
	}
}

// Security check
if ( ! current_user_can( 'manage_options' ) && php_sapi_name() !== 'cli' ) {
	wp_die( 'Unauthorized access. Admin privileges required.' );
}

// Load migration file
require_once __DIR__ . '/includes/Database/Migrations/migration_2025_12_22_add_geo_rule_to_consent.php';

use ShahiLegalFlowSuite\Database\Migrations\Migration_2025_12_22_add_geo_rule_to_consent;

echo "=== Running Geo Rules Migration ===\n\n";

// Check table status before migration
global $wpdb;
$table_name = $wpdb->prefix . 'slos_consent';
$columns = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );
$existing_columns = array_map( function( $col ) { return $col->Field; }, $columns );

echo "Current columns in {$table_name}:\n";
echo "  - " . implode( "\n  - ", $existing_columns ) . "\n\n";

$needs_migration = ! in_array( 'country_code', $existing_columns, true ) ||
				   ! in_array( 'region', $existing_columns, true ) ||
				   ! in_array( 'geo_rule_id', $existing_columns, true );

if ( ! $needs_migration ) {
	echo "✅ Migration already complete! All columns exist:\n";
	echo "  - geo_rule_id\n";
	echo "  - country_code\n";
	echo "  - region\n\n";
	exit( 0 );
}

echo "Running migration...\n";

// Run migration
$result = Migration_2025_12_22_add_geo_rule_to_consent::up();

if ( $result ) {
	echo "\n✅ Migration completed successfully!\n\n";
	
	// Verify columns were added
	$columns_after = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );
	$columns_after_names = array_map( function( $col ) { return $col->Field; }, $columns_after );
	
	echo "New columns in {$table_name}:\n";
	echo "  - " . implode( "\n  - ", $columns_after_names ) . "\n\n";
	
	$added = array_diff( $columns_after_names, $existing_columns );
	if ( ! empty( $added ) ) {
		echo "✅ Added columns: " . implode( ', ', $added ) . "\n";
	}
	
	// Check indexes
	$indexes = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
	$index_names = array_unique( array_map( function( $idx ) { return $idx->Key_name; }, $indexes ) );
	
	$geo_indexes = array_filter( $index_names, function( $name ) {
		return strpos( $name, 'idx_geo' ) !== false || 
			   strpos( $name, 'idx_country' ) !== false || 
			   strpos( $name, 'idx_region' ) !== false;
	});
	
	if ( ! empty( $geo_indexes ) ) {
		echo "✅ Added indexes: " . implode( ', ', $geo_indexes ) . "\n";
	}
	
} else {
	echo "\n❌ Migration failed! Check error logs for details.\n";
	echo "Last DB error: " . $wpdb->last_error . "\n\n";
	exit( 1 );
}

echo "\n=== Migration Complete ===\n";
