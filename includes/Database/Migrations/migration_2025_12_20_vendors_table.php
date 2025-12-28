<?php
/**
 * Migration: Create wp_slos_vendors table
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Database\Migrations
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalopsSuite\Database\Migrations;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_2025_12_20_vendors_table
 *
 * Creates the vendors/processors management table for GDPR Article 28 compliance.
 *
 * @since 3.0.1
 */
class Migration_2025_12_20_vendors_table {

	/**
	 * Run migration (create table)
	 *
	 * @since 3.0.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'slos_vendors';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			category VARCHAR(100) NOT NULL,
			country VARCHAR(2) NOT NULL,
			dpa_url TEXT NULL,
			privacy_policy_url TEXT NULL,
			risk_level VARCHAR(20) NOT NULL,
			metadata LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY name (name(191)),
			KEY category (category),
			KEY country (country),
			KEY risk_level (risk_level)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return ! $wpdb->last_error;
	}

	/**
	 * Rollback migration (drop table)
	 *
	 * @since 3.0.1
	 * @return bool True on success, false on failure
	 */
	public static function down() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'slos_vendors';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		return ! $wpdb->last_error;
	}
}
