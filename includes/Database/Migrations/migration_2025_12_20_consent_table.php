<?php
/**
 * Migration: Create wp_slos_consent table
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Migrations
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Database\Migrations;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_2025_12_20_consent_table
 *
 * Creates the consent tracking table for GDPR/CCPA/LGPD compliance.
 *
 * @since 3.0.1
 */
class Migration_2025_12_20_consent_table {

	/**
	 * Run migration (create table)
	 *
	 * @since 3.0.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'slos_consent';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NULL,
			ip_hash VARCHAR(64) NULL,
			type VARCHAR(50) NOT NULL,
			status VARCHAR(20) NOT NULL,
			metadata LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY type (type),
			KEY status (status),
			KEY ip_hash (ip_hash),
			KEY created_at (created_at)
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
		$table_name = $wpdb->prefix . 'slos_consent';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		return ! $wpdb->last_error;
	}
}
