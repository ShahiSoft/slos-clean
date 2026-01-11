<?php
/**
 * Migration: Add version columns to wp_slos_consent table
 *
 * Adds banner_version and policy_version columns for tracking
 * which versions of banner config and policies were active at consent time.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Migrations
 * @version     3.1.1
 * @since       3.1.1
 */

namespace ShahiLegalFlowSuite\Database\Migrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_2025_12_30_add_version_columns_to_consent
 *
 * Adds version tracking columns to consent records for audit trail compliance.
 *
 * @since 3.1.1
 */
class Migration_2025_12_30_add_version_columns_to_consent {

	/**
	 * Run migration (add columns)
	 *
	 * @since 3.1.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'slos_consent';

		// Check if table exists.
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);

		if ( ! $table_exists ) {
			return false;
		}

		// Check if columns already exist.
		$columns          = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );
		$existing_columns = array_map(
			function ( $col ) {
				return $col->Field;
			},
			$columns
		);

		$errors = array();

		// Add banner_version column if not exists.
		if ( ! in_array( 'banner_version', $existing_columns, true ) ) {
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN banner_version VARCHAR(50) NULL AFTER metadata"
			);
			if ( false === $result ) {
				$errors[] = 'banner_version';
			}
		}

		// Add policy_version column if not exists.
		if ( ! in_array( 'policy_version', $existing_columns, true ) ) {
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN policy_version VARCHAR(50) NULL AFTER banner_version"
			);
			if ( false === $result ) {
				$errors[] = 'policy_version';
			}
		}

		// Return false if any errors occurred.
		if ( ! empty( $errors ) ) {
			// Log errors for debugging.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
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

		// Check if table exists.
		$table_exists = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);

		if ( ! $table_exists ) {
			return false;
		}

		// Check if columns exist before trying to drop them.
		$columns          = $wpdb->get_results( "SHOW COLUMNS FROM {$table_name}" );
		$existing_columns = array_map(
			function ( $col ) {
				return $col->Field;
			},
			$columns
		);

		$errors = array();

		// Drop policy_version column if exists (drop in reverse order).
		if ( in_array( 'policy_version', $existing_columns, true ) ) {
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} DROP COLUMN policy_version"
			);
			if ( false === $result ) {
				$errors[] = 'policy_version';
			}
		}

		// Drop banner_version column if exists.
		if ( in_array( 'banner_version', $existing_columns, true ) ) {
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} DROP COLUMN banner_version"
			);
			if ( false === $result ) {
				$errors[] = 'banner_version';
			}
		}

		// Return false if any errors occurred.
		if ( ! empty( $errors ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Migration rollback failed for columns: ' . implode( ', ', $errors ) );
			}
			return false;
		}

		return true;
	}
}
