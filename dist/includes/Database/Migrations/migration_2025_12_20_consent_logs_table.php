<?php
/**
 * Migration: Create wp_slos_consent_logs table
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
 * Class Migration_2025_12_20_consent_logs_table
 *
 * Creates the consent audit log table for tracking all consent changes.
 * Required for GDPR/CCPA compliance evidence and audit trails.
 *
 * @since 3.0.1
 */
class Migration_2025_12_20_consent_logs_table {

	/**
	 * Run migration (create table)
	 *
	 * @since 3.0.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'slos_consent_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			consent_id BIGINT(20) UNSIGNED NULL,
			user_id BIGINT(20) UNSIGNED NULL,
			purpose VARCHAR(100) NULL,
			action VARCHAR(50) NOT NULL,
			previous_state LONGTEXT NULL,
			new_state LONGTEXT NULL,
			method VARCHAR(50) NULL,
			ip_address VARCHAR(45) NULL,
			user_agent VARCHAR(255) NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY consent_id (consent_id),
			KEY user_id (user_id),
			KEY action (action),
			KEY purpose (purpose),
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
		$table_name = $wpdb->prefix . 'slos_consent_logs';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		return ! $wpdb->last_error;
	}
}
