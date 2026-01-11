<?php
/**
 * Migration: Add geo rule columns to wp_slos_consent table
 *
 * Adds geo_rule_id, country_code, and region columns for
 * linking consent records to geo-based compliance rules.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Migrations
 * @version     3.0.3
 * @since       3.0.3
 */

namespace ShahiLegalFlowSuite\Database\Migrations;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_2025_12_22_add_geo_rule_to_consent
 *
 * Adds geographic tracking columns to consent records.
 *
 * @since 3.0.3
 */
class Migration_2025_12_22_add_geo_rule_to_consent {

	/**
	 * Run migration (add columns)
	 *
	 * @since 3.0.3
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

		// Add geo_rule_id column if not exists.
		if ( ! in_array( 'geo_rule_id', $existing_columns, true ) ) {
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN geo_rule_id INT UNSIGNED NULL AFTER ip_hash"
			);
			if ( false === $result ) {
				$errors[] = 'geo_rule_id';
			}
		}

		// Add country_code column if not exists.
		if ( ! in_array( 'country_code', $existing_columns, true ) ) {
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN country_code VARCHAR(10) NULL AFTER geo_rule_id"
			);
			if ( false === $result ) {
				$errors[] = 'country_code';
			}
		}

		// Add region column if not exists.
		if ( ! in_array( 'region', $existing_columns, true ) ) {
			$result = $wpdb->query(
				"ALTER TABLE {$table_name} ADD COLUMN region VARCHAR(20) NULL AFTER country_code"
			);
			if ( false === $result ) {
				$errors[] = 'region';
			}
		}

		// Add indexes for efficient filtering.
		$indexes          = $wpdb->get_results( "SHOW INDEX FROM {$table_name}" );
		$existing_indexes = array_map(
			function ( $idx ) {
				return $idx->Key_name;
			},
			$indexes
		);

		if ( ! in_array( 'idx_geo_rule_id', $existing_indexes, true ) && in_array( 'geo_rule_id', $existing_columns, true ) || ! in_array( 'geo_rule_id', $existing_columns, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_geo_rule_id (geo_rule_id)" );
		}

		if ( ! in_array( 'idx_country_code', $existing_indexes, true ) && in_array( 'country_code', $existing_columns, true ) || ! in_array( 'country_code', $existing_columns, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_country_code (country_code)" );
		}

		if ( ! in_array( 'idx_region', $existing_indexes, true ) && in_array( 'region', $existing_columns, true ) || ! in_array( 'region', $existing_columns, true ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD INDEX idx_region (region)" );
		}

		return empty( $errors );
	}

	/**
	 * Rollback migration (drop columns)
	 *
	 * @since 3.0.3
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
			return true;
		}

		// Drop indexes first.
		$wpdb->query( "ALTER TABLE {$table_name} DROP INDEX IF EXISTS idx_region" );
		$wpdb->query( "ALTER TABLE {$table_name} DROP INDEX IF EXISTS idx_country_code" );
		$wpdb->query( "ALTER TABLE {$table_name} DROP INDEX IF EXISTS idx_geo_rule_id" );

		// Drop columns.
		$wpdb->query( "ALTER TABLE {$table_name} DROP COLUMN IF EXISTS region" );
		$wpdb->query( "ALTER TABLE {$table_name} DROP COLUMN IF EXISTS country_code" );
		$wpdb->query( "ALTER TABLE {$table_name} DROP COLUMN IF EXISTS geo_rule_id" );

		return true;
	}
}
