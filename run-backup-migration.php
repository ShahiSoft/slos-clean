<?php
/**
 * Database Migration Runner - BackupService Content Column
 * 
 * Run this file once via:
 * 1. WP-CLI: wp eval-file run-backup-migration.php
 * 2. Browser: Navigate to this file URL (if allowed)
 * 3. Include in WordPress: Add require_once to functions.php temporarily
 * 
 * @package ShahiLegalFlowSuite
 */

// Security check - can only run from WordPress context
if ( ! defined( 'ABSPATH' ) ) {
	// Try to load WordPress
	$wp_load_paths = array(
		__DIR__ . '/../../../../wp-load.php',
		__DIR__ . '/../../../wp-load.php',
		__DIR__ . '/../../wp-load.php',
	);
	
	$loaded = false;
	foreach ( $wp_load_paths as $path ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			$loaded = true;
			break;
		}
	}
	
	if ( ! $loaded ) {
		die( 'Error: Could not load WordPress. Please run via WP-CLI: wp eval-file run-backup-migration.php' );
	}
}

// Check user permissions
if ( ! current_user_can( 'manage_options' ) && ! defined( 'WP_CLI' ) ) {
	wp_die( 'Insufficient permissions to run database migration.' );
}

// Load the migration class
require_once __DIR__ . '/includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php';

$migration = new migration_2026_01_01_add_backup_content_column();

echo "=== BackupService Database Migration ===\n\n";

// Display migration info
echo "Migration Info:\n";
echo $migration->info() . "\n\n";

// Verify current state
echo "Verifying current database state...\n";
$verification = $migration->verify();

if ( $verification['is_applied'] ) {
	echo "✅ Migration already applied!\n";
	echo "Current state:\n";
	foreach ( $verification as $key => $value ) {
		if ( is_bool( $value ) ) {
			$value = $value ? 'YES' : 'NO';
		}
		echo "  - {$key}: {$value}\n";
	}
	echo "\nNo action needed.\n";
	exit( 0 );
}

echo "Migration not yet applied. Proceeding with migration...\n\n";

// Run the migration
echo "Running migration...\n";
try {
	$result = $migration->up();
	
	if ( $result ) {
		echo "✅ Migration completed successfully!\n\n";
		
		// Verify again
		echo "Verifying migration...\n";
		$post_verification = $migration->verify();
		
		if ( $post_verification['is_applied'] ) {
			echo "✅ Verification passed! Migration successful.\n\n";
			echo "Database state:\n";
			foreach ( $post_verification as $key => $value ) {
				if ( is_bool( $value ) ) {
					$value = $value ? 'YES' : 'NO';
				}
				echo "  - {$key}: {$value}\n";
			}
			
			echo "\n=== Migration Complete ===\n";
			echo "BackupService is now ready to use!\n";
		} else {
			echo "⚠️ Warning: Migration ran but verification failed.\n";
			echo "Please check the database manually.\n";
		}
	} else {
		echo "❌ Migration failed!\n";
		echo "Error: " . ( $migration->get_last_error() ?? 'Unknown error' ) . "\n";
		echo "\nPlease check:\n";
		echo "1. Database connection\n";
		echo "2. User permissions (ALTER TABLE)\n";
		echo "3. WordPress error logs\n";
	}
} catch ( Exception $e ) {
	echo "❌ Exception during migration: " . $e->getMessage() . "\n";
	echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Migration Runner Complete ===\n";
