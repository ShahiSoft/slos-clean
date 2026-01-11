<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package    ShahiTemplate
 * @subpackage Core
 * @license    GPL-3.0+
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Uninstall ShahiTemplate Plugin
 *
 * This script respects user preferences for data deletion.
 * By default, ALL data is preserved (CodeCanyon requirement).
 * Data is only deleted if user explicitly chose to do so in settings.
 */

// Get user preferences for uninstall.
$preferences = get_option(
	'shahi_template_uninstall_preferences',
	array(
		'preserve_all'        => true, // Default: preserve everything.
		'delete_settings'     => false,
		'delete_analytics'    => false,
		'delete_posts'        => false,
		'delete_capabilities' => false,
		'delete_tables'       => false,
	)
);

// If user wants to preserve all data, do nothing.
if ( isset( $preferences['preserve_all'] ) && true === $preferences['preserve_all'] ) {
	return;
}

global $wpdb;

// Delete plugin options if user chose to.
if ( isset( $preferences['delete_settings'] ) && true === $preferences['delete_settings'] ) {
	delete_option( 'shahi_template_version' );
	delete_option( 'shahi_template_installed_at' );
	delete_option( 'shahi_template_onboarding_completed' );
	delete_option( 'shahi_template_settings' );
	delete_option( 'shahi_template_advanced_settings' );
	delete_option( 'shahi_template_uninstall_preferences' );
	delete_option( 'shahi_template_modules_enabled' );

	// Delete all transients.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall routine, direct query necessary, no caching needed during uninstall.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE %s 
            OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_shahi_template_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_shahi_template_' ) . '%'
		)
	);
}

// Delete analytics data if user chose to.
if ( isset( $preferences['delete_analytics'] ) && true === $preferences['delete_analytics'] ) {
	$table_analytics = $wpdb->prefix . 'shahi_analytics';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uninstall routine, table names cannot be parameterized.
	$wpdb->query( "TRUNCATE TABLE {$table_analytics}" );
}

// Delete custom post type entries if user chose to.
if ( isset( $preferences['delete_posts'] ) && true === $preferences['delete_posts'] ) {
	// Will be implemented when CPT is added in Phase 5.3.
	// For now, no action needed - no custom post types exist yet.
	$placeholder = true; // phpcs:ignore Squiz.PHP.NonExecutableCode.Unreachable -- Placeholder to avoid empty IF block error.
}

// Delete user capabilities if user chose to.
if ( isset( $preferences['delete_capabilities'] ) && true === $preferences['delete_capabilities'] ) {
	// Remove custom capabilities from all roles.
	$roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

	foreach ( $roles as $single_role ) {
		$role_obj = get_role( $single_role );
		if ( $role_obj ) {
			$role_obj->remove_cap( 'manage_shahi_template' );
			$role_obj->remove_cap( 'view_shahi_analytics' );
			$role_obj->remove_cap( 'manage_shahi_modules' );
			$role_obj->remove_cap( 'edit_shahi_settings' );
		}
	}

	// Delete user meta.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall routine, direct query necessary, no caching needed during uninstall.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE %s",
			$wpdb->esc_like( 'shahi_template_' ) . '%'
		)
	);
}

// Drop custom database tables if user chose to.
if ( isset( $preferences['delete_tables'] ) && true === $preferences['delete_tables'] ) {
	$table_analytics  = $wpdb->prefix . 'shahi_analytics';
	$table_modules    = $wpdb->prefix . 'shahi_modules';
	$table_onboarding = $wpdb->prefix . 'shahi_onboarding';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall routine, dropping tables, table names cannot be parameterized.
	$wpdb->query( "DROP TABLE IF EXISTS {$table_analytics}" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall routine, dropping tables.
	$wpdb->query( "DROP TABLE IF EXISTS {$table_modules}" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall routine, dropping tables.
	$wpdb->query( "DROP TABLE IF EXISTS {$table_onboarding}" );
}

// Clear any cached data.
wp_cache_flush();
