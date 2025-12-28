<?php
/**
 * Migration: Create wp_slos_dsr_requests table
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
 * Class Migration_2025_12_20_dsr_requests_table
 *
 * Creates the DSR (Data Subject Rights) requests table for GDPR Article 15-22 compliance.
 *
 * @since 3.0.1
 */
class Migration_2025_12_20_dsr_requests_table {

	/**
	 * Run migration (create table)
	 *
	 * @since 3.0.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'slos_dsr_requests';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			request_type VARCHAR(50) NOT NULL,
			email VARCHAR(255) NOT NULL,
			status VARCHAR(50) NOT NULL,
			request_date DATETIME NOT NULL,
			due_date DATETIME NOT NULL,
			completed_date DATETIME NULL,
			data_export LONGBLOB NULL,
			metadata LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY request_type (request_type),
			KEY email (email),
			KEY status (status),
			KEY request_date (request_date),
			KEY due_date (due_date)
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
		$table_name = $wpdb->prefix . 'slos_dsr_requests';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		return ! $wpdb->last_error;
	}
}
