<?php
/**
 * Migration: Add version columns to wp_slos_consent table
 *
 * Adds banner_version and policy_version columns for tracking
 * which versions of banner config and policies were active at consent time.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Migrations
 * @since       3.1.1
 * @version     3.1.1
 */
// phpcs:ignoreFile WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

namespace ShahiLegalFlowSuite\Database\Migrations;

// Exit if accessed directly...
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_2025_12_30_Add_Version_Columns_To_Consent
 *
 * Adds version tracking columns to consent records for audit trail compliance.
 *
 * @since 3.1.1
 */
class Migration_2025_12_30_Add_Version_Columns_To_Consent {

	/**
	 * Run migration (add columns)
	 *
	 * @since 3.1.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'slos_consent';

		// Check if table exists...
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);

		if ( ! $table_exists ) {
			return false;
		}

		// Check if columns already exist...
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$columns          = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$existing_columns = array_map(
			function ( $col ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				return $col->Field;
			},
			$columns
		);

		$errors = array();

		// Add banner_version column if not exists...
		if ( ! in_array( 'banner_version', $existing_columns, true ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$result = $wpdb->query(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				"ALTER TABLE {$table_name} ADD COLUMN banner_version VARCHAR(50) NULL AFTER metadata"
			);
			if ( false === $result ) {
				$errors[] = 'banner_version';
			}
		}

		// Add policy_version column if not exists...
		if ( ! in_array( 'policy_version', $existing_columns, true ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN policy_version VARCHAR(50) NULL AFTER banner_version" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			);
			if ( false === $result ) {
				$errors[] = 'policy_version';
			}
		}

		// Return false if any errors occurred...
		if ( ! empty( $errors ) ) {
			// Log errors for debugging...
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Migration failed for columns: ' . implode( ', ', $errors ) );
			}
			return false;
		}

		return true;
	}

	/**
	 * Rollback migration (remove columns)
	 *
	 * @since 3.1.1
	 * @return bool True on success, false on failure
	 */
	public static function down() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'slos_consent';

		// Check if table exists...
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);

		if ( ! $table_exists ) {
			return false;
		}

		// Check if columns exist before trying to drop them...
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$columns          = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$existing_columns = array_map(
			function ( $col ) {
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				return $col->Field;
			},
			$columns
		);

		$errors = array();

		// Drop policy_version column if exists (drop in reverse order)...
		if ( in_array( 'policy_version', $existing_columns, true ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} DROP COLUMN policy_version" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			);
			if ( false === $result ) {
				$errors[] = 'policy_version';
			}
		}

		// Drop banner_version column if exists...
		if ( in_array( 'banner_version', $existing_columns, true ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} DROP COLUMN banner_version" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			);
			if ( false === $result ) {
				$errors[] = 'banner_version';
			}
		}

		// Return false if any errors occurred...
		if ( ! empty( $errors ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Migration rollback failed for columns: ' . implode( ', ', $errors ) );
			}
			return false;
		}

		return true;
	}
}
