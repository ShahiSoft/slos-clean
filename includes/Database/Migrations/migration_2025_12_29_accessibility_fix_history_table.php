<?php
/**
 * Migration: Create wp_slos_accessibility_fix_history table
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Migrations
 * @version     3.1.1
 * @since       3.1.1
 */

namespace ShahiLegalFlowSuite\Database\Migrations;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_2025_12_29_accessibility_fix_history_table
 *
 * Creates the accessibility fix history table for tracking all auto-fix operations.
 * Enables rollback/undo functionality and audit trails for accessibility fixes.
 *
 * @since 3.1.1
 */
class Migration_2025_12_29_accessibility_fix_history_table {

	/**
	 * Run migration (create table)
	 *
	 * @since 3.1.1
	 * @return bool True on success, false on failure
	 */
	public static function up() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'slos_accessibility_fix_history';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			fixer_id VARCHAR(100) NOT NULL,
			fixed_count INT(11) NOT NULL DEFAULT 0,
			content_hash_before VARCHAR(64) NULL,
			content_hash_after VARCHAR(64) NULL,
			issues_before INT(11) NULL,
			issues_after INT(11) NULL,
			user_id BIGINT(20) UNSIGNED NULL,
			action VARCHAR(50) NOT NULL DEFAULT 'auto_fix',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY fixer_id (fixer_id),
			KEY user_id (user_id),
			KEY created_at (created_at),
			KEY action (action)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		return ! $wpdb->last_error;
	}

	/**
	 * Rollback migration (drop table)
	 *
	 * @since 3.1.1
	 * @return bool True on success, false on failure
	 */
	public static function down() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'slos_accessibility_fix_history';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		return ! $wpdb->last_error;
	}
}
