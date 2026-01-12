<?php
/**
 * Example Migration File - Version 1.0.0
 *
 * This file serves as a template for future database migrations.
 * Copy this file and rename it to migration_X_Y_Z.php where X.Y.Z is the version.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Database/Migrations
 * @license    GPL-3.0+
 */

// Prevent direct access..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration Up Function
 *
 * This function is called when upgrading the database.
 * Add your table creation, column additions, or data updates here.
 *
 * @param wpdb $wpdb WordPress database object.
 * @return void
 */
function up( $wpdb ) {
	// This is the initial migration - tables are created in Activator.php..
	// This file is here as an example for future migrations..

	// Example: Add a new column..
	// $table = $wpdb->prefix . 'shahi_analytics';..
	// $wpdb->query("ALTER TABLE {$table} ADD COLUMN new_field varchar(255) DEFAULT NULL");..

	// Example: Create a new table..
	// $charset_collate = $wpdb->get_charset_collate();..
	// $table = $wpdb->prefix . 'shahi_new_table';..
	// $sql = "CREATE TABLE IF NOT EXISTS {$table} (..
	// id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,..
	// data longtext,..
	// PRIMARY KEY  (id)..
	// ) {$charset_collate};";..
	// require_once ABSPATH . 'wp-admin/includes/upgrade.php';..
	// dbDelta($sql);..
}

/**
 * Migration Down Function (Optional)
 *
 * This function is called when rolling back the database.
 * Reverse the changes made in up() function.
 *
 * @param wpdb $wpdb WordPress database object.
 * @return void
 */
function down( $wpdb ) {
	// Example: Remove the column added in up()..
	// $table = $wpdb->prefix . 'shahi_analytics';..
	// $wpdb->query("ALTER TABLE {$table} DROP COLUMN new_field");..

	// Example: Drop the table created in up()..
	// $table = $wpdb->prefix . 'shahi_new_table';..
	// $wpdb->query("DROP TABLE IF EXISTS {$table}");..
}
