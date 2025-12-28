<?php
/**
 * Migration: Create wp_slos_form_issues table
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
 * Class Migration_2025_12_20_form_issues_table
 *
 * Creates the form compliance issues tracking table.
 *
 * @since 3.0.1
 */
class Migration_2025_12_20_form_issues_table {

	/**
	 * Run migration (create table)
	 *
	 * @since 3.0.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'slos_form_issues';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			form_id INT(11) NOT NULL,
			issue_type VARCHAR(50) NOT NULL,
			severity VARCHAR(20) NOT NULL,
			resolved_at DATETIME NULL,
			metadata LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY form_id (form_id),
			KEY issue_type (issue_type),
			KEY severity (severity),
			KEY resolved_at (resolved_at),
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
		$table_name = $wpdb->prefix . 'slos_form_issues';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		return ! $wpdb->last_error;
	}
}
