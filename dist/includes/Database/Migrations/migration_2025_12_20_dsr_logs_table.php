<?php
/**
 * Migration: Create wp_slos_dsr_logs table
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
 * Class Migration_2025_12_20_dsr_logs_table
 *
 * Creates the DSR audit log table for tracking all DSR lifecycle actions.
 * Implements privacy-focused logging with hashed IP/UA for GDPR compliance.
 *
 * @since 3.0.1
 */
class Migration_2025_12_20_dsr_logs_table {

	/**
	 * Run migration (create table)
	 *
	 * @since 3.0.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'slos_dsr_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			request_id BIGINT(20) UNSIGNED NOT NULL,
			action VARCHAR(100) NOT NULL,
			actor_id BIGINT(20) UNSIGNED NULL,
			note LONGTEXT NULL,
			metadata LONGTEXT NULL,
			ip_hash VARCHAR(64) NULL,
			user_agent_hash VARCHAR(64) NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY request_id (request_id),
			KEY action (action),
			KEY actor_id (actor_id),
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
		$table_name = $wpdb->prefix . 'slos_dsr_logs';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		return ! $wpdb->last_error;
	}
}
