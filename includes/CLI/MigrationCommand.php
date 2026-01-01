<?php
/**
 * WP-CLI Command: Run BackupService Migration
 * 
 * Usage:
 *   wp slos migrate backup-service
 *   wp slos migrate backup-service --dry-run
 *   wp slos migrate backup-service --rollback
 * 
 * @package ShahiLegalFlowSuite
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Manage SLOS database migrations
 */
class SLOS_Migration_Command {
	
	/**
	 * Run BackupService database migration
	 * 
	 * ## OPTIONS
	 * 
	 * [--dry-run]
	 * : Check migration status without applying changes
	 * 
	 * [--rollback]
	 * : Rollback the migration (remove added columns)
	 * 
	 * ## EXAMPLES
	 * 
	 *     # Run the migration
	 *     wp slos migrate backup-service
	 * 
	 *     # Check status without changes
	 *     wp slos migrate backup-service --dry-run
	 * 
	 *     # Rollback migration
	 *     wp slos migrate backup-service --rollback
	 * 
	 * @param array $args Positional arguments
	 * @param array $assoc_args Associative arguments
	 */
	public function backup_service( $args, $assoc_args ) {
		// Load migration class
		$migration_file = dirname( __FILE__ ) . '/includes/Database/Migrations/migration_2026_01_01_add_backup_content_column.php';
		
		if ( ! file_exists( $migration_file ) ) {
			WP_CLI::error( 'Migration file not found: ' . $migration_file );
		}
		
		require_once $migration_file;
		$migration = new migration_2026_01_01_add_backup_content_column();
		
		// Display info
		WP_CLI::log( '' );
		WP_CLI::log( WP_CLI::colorize( '%B=== BackupService Database Migration ===%n' ) );
		WP_CLI::log( '' );
		WP_CLI::log( $migration->info() );
		WP_CLI::log( '' );
		
		// Check current state
		WP_CLI::log( 'Verifying current database state...' );
		$verification = $migration->verify();
		
		// Dry run mode
		if ( isset( $assoc_args['dry-run'] ) ) {
			WP_CLI::log( '' );
			WP_CLI::log( WP_CLI::colorize( '%Y[DRY RUN MODE]%n' ) );
			WP_CLI::log( '' );
			
			if ( $verification['is_applied'] ) {
				WP_CLI::success( 'Migration already applied.' );
			} else {
				WP_CLI::log( 'Migration would be applied:' );
				WP_CLI::log( '  - Add column: original_content (LONGTEXT)' );
				WP_CLI::log( '  - Add column: metadata (TEXT)' );
			}
			
			$this->display_verification( $verification );
			return;
		}
		
		// Rollback mode
		if ( isset( $assoc_args['rollback'] ) ) {
			WP_CLI::log( '' );
			WP_CLI::log( WP_CLI::colorize( '%R[ROLLBACK MODE]%n' ) );
			WP_CLI::log( '' );
			
			if ( ! $verification['is_applied'] ) {
				WP_CLI::warning( 'Migration not applied. Nothing to rollback.' );
				return;
			}
			
			WP_CLI::confirm( 'Are you sure you want to rollback? This will remove the backup content columns.', $assoc_args );
			
			WP_CLI::log( 'Rolling back migration...' );
			$result = $migration->down();
			
			if ( $result ) {
				WP_CLI::success( 'Migration rolled back successfully!' );
				$verification = $migration->verify();
				$this->display_verification( $verification );
			} else {
				WP_CLI::error( 'Rollback failed!' );
			}
			
			return;
		}
		
		// Normal migration mode
		if ( $verification['is_applied'] ) {
			WP_CLI::success( 'Migration already applied!' );
			$this->display_verification( $verification );
			WP_CLI::log( '' );
			WP_CLI::log( 'No action needed. BackupService is ready to use.' );
			return;
		}
		
		WP_CLI::log( 'Migration not yet applied.' );
		WP_CLI::log( '' );
		WP_CLI::confirm( 'Proceed with migration?', $assoc_args );
		
		// Run migration
		WP_CLI::log( '' );
		WP_CLI::log( 'Running migration...' );
		
		try {
			$result = $migration->up();
			
			if ( $result ) {
				WP_CLI::success( 'Migration completed!' );
				
				// Verify
				WP_CLI::log( '' );
				WP_CLI::log( 'Verifying migration...' );
				$post_verification = $migration->verify();
				
				if ( $post_verification['is_applied'] ) {
					WP_CLI::success( 'Verification passed!' );
					$this->display_verification( $post_verification );
					WP_CLI::log( '' );
					WP_CLI::success( 'BackupService is now ready to use!' );
				} else {
					WP_CLI::warning( 'Migration ran but verification failed.' );
					WP_CLI::log( 'Please check the database manually.' );
				}
			} else {
				WP_CLI::error( 'Migration failed!' );
			}
		} catch ( Exception $e ) {
			WP_CLI::error( 'Exception: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Display verification results
	 * 
	 * @param array $verification Verification data
	 */
	private function display_verification( $verification ) {
		WP_CLI::log( '' );
		WP_CLI::log( 'Database State:' );
		
		$table_data = array();
		foreach ( $verification as $key => $value ) {
			if ( is_bool( $value ) ) {
				$value = $value ? WP_CLI::colorize( '%G✓ YES%n' ) : WP_CLI::colorize( '%R✗ NO%n' );
			}
			$table_data[] = array(
				'Property' => $key,
				'Value'    => $value,
			);
		}
		
		WP_CLI\Utils\format_items( 'table', $table_data, array( 'Property', 'Value' ) );
	}
}

// Register command
WP_CLI::add_command( 'slos migrate', 'SLOS_Migration_Command' );
