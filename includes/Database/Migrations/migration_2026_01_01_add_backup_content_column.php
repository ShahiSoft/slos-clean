<?php
/**
 * Migration: Add Content Storage to Accessibility Fix History Table
 *
 * Adds original_content and metadata columns to support full backup/restore
 * functionality in BackupService (Phase 4 refactoring).
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Migrations
 * @version     3.2.0
 * @since       3.2.0
 */

namespace ShahiLegalFlowSuite\Database\Migrations;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_2026_01_01_add_backup_content_column
 *
 * Extends the accessibility fix history table to store actual content for restore operations.
 * Required for Phase 4 BackupService implementation.
 *
 * IMPORTANT: This migration modifies existing table structure. Backup database before running.
 *
 * @since 3.2.0
 */
class Migration_2026_01_01_add_backup_content_column {

	/**
	 * Run migration (add columns)
	 *
	 * Adds two new columns to slos_accessibility_fix_history table:
	 * - original_content: Stores actual content for restore operations
	 * - metadata: Stores additional JSON data (scan results, fix details, etc.)
	 *
	 * @since 3.2.0
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'slos_accessibility_fix_history';

		// Check if table exists
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			error_log( sprintf(
				'Migration Error: Table %s does not exist. Run migration_2025_12_29_accessibility_fix_history_table first.',
				$table_name
			) );
			return false;
		}

		// Check if columns already exist
		$original_content_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'original_content'",
				DB_NAME,
				$table_name
			)
		);

		$metadata_exists = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'metadata'",
				DB_NAME,
				$table_name
			)
		);

		$queries = array();

		// Add original_content column if it doesn't exist
		if ( empty( $original_content_exists ) ) {
			$queries[] = "ALTER TABLE {$table_name} ADD COLUMN original_content LONGTEXT NULL AFTER fixed_count";
		}

		// Add metadata column if it doesn't exist
		if ( empty( $metadata_exists ) ) {
			$queries[] = "ALTER TABLE {$table_name} ADD COLUMN metadata TEXT NULL AFTER created_at";
		}

		// Execute queries
		if ( ! empty( $queries ) ) {
			foreach ( $queries as $query ) {
				$result = $wpdb->query( $query );
				if ( $result === false ) {
					error_log( sprintf(
						'Migration Error: Failed to execute query: %s. Error: %s',
						$query,
						$wpdb->last_error
					) );
					return false;
				}
			}

			error_log( sprintf(
				'Migration Success: Added content storage columns to %s',
				$table_name
			) );
		} else {
			error_log( sprintf(
				'Migration Skipped: Columns already exist in %s',
				$table_name
			) );
		}

		return true;
	}

	/**
	 * Rollback migration (remove columns)
	 *
	 * CAUTION: This will permanently delete backup content data!
	 * Only use this for rollback during development/testing.
	 *
	 * @since 3.2.0
	 * @return bool True on success, false on failure
	 */
	public static function down() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'slos_accessibility_fix_history';

		// Check if table exists
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			return true; // Table doesn't exist, nothing to rollback
		}

		$queries = array(
			"ALTER TABLE {$table_name} DROP COLUMN IF EXISTS original_content",
			"ALTER TABLE {$table_name} DROP COLUMN IF EXISTS metadata",
		);

		foreach ( $queries as $query ) {
			$result = $wpdb->query( $query );
			if ( $result === false ) {
				error_log( sprintf(
					'Migration Rollback Error: Failed to execute query: %s. Error: %s',
					$query,
					$wpdb->last_error
				) );
				return false;
			}
		}

		error_log( sprintf(
			'Migration Rollback Success: Removed content storage columns from %s',
			$table_name
		) );

		return true;
	}

	/**
	 * Verify migration was successful
	 *
	 * Checks that both columns exist and have correct data types.
	 *
	 * @since 3.2.0
	 * @return bool True if migration is complete and valid
	 */
	public static function verify() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'slos_accessibility_fix_history';

		$columns = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COLUMN_NAME, DATA_TYPE 
				FROM INFORMATION_SCHEMA.COLUMNS 
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s 
				AND COLUMN_NAME IN ('original_content', 'metadata')",
				DB_NAME,
				$table_name
			),
			ARRAY_A
		);

		if ( count( $columns ) !== 2 ) {
			return false;
		}

		$column_map = array();
		foreach ( $columns as $column ) {
			$column_map[ $column['COLUMN_NAME'] ] = $column['DATA_TYPE'];
		}

		// Verify data types
		if ( ! isset( $column_map['original_content'] ) || ! in_array( strtolower( $column_map['original_content'] ), array( 'longtext', 'text' ), true ) ) {
			return false;
		}

		if ( ! isset( $column_map['metadata'] ) || ! in_array( strtolower( $column_map['metadata'] ), array( 'text', 'longtext' ), true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get migration information
	 *
	 * @since 3.2.0
	 * @return array Migration metadata
	 */
	public static function info() {
		return array(
			'version'     => '3.2.0',
			'date'        => '2026-01-01',
			'description' => 'Add original_content and metadata columns to slos_accessibility_fix_history table',
			'dependencies' => array( 'migration_2025_12_29_accessibility_fix_history_table' ),
			'reversible'  => true,
			'data_loss_on_rollback' => true,
		);
	}
}
