<?php
/**
 * Migration Runner
 *
 * Orchestrates running all database migrations.
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
 * Class Runner
 *
 * Manages execution of all database migrations.
 *
 * @since 3.0.1
 */
class Runner {

	/**
	 * Run all migrations (create tables)
	 *
	 * @since 3.0.1
	 * @return array Results of each migration
	 */
	public static function run_all() {
		$migrations_dir = __DIR__;

		$migrations = array(
			'migration_2025_12_20_consent_table'          => 'Migration_2025_12_20_consent_table',
			'migration_2025_12_20_consent_logs_table'     => 'Migration_2025_12_20_consent_logs_table',
			'migration_2025_12_20_dsr_requests_table'     => 'Migration_2025_12_20_dsr_requests_table',
			'migration_2025_12_20_documents_table'        => 'Migration_2025_12_20_documents_table',
			'migration_2025_12_20_trackers_table'         => 'Migration_2025_12_20_trackers_table',
			'migration_2025_12_20_vendors_table'          => 'Migration_2025_12_20_vendors_table',
			'migration_2025_12_20_form_submissions_table' => 'Migration_2025_12_20_form_submissions_table',
			'migration_2025_12_20_form_issues_table'      => 'Migration_2025_12_20_form_issues_table',
			'migration_2025_12_29_accessibility_fix_history_table' => 'Migration_2025_12_29_accessibility_fix_history_table',
			'Migration_Company_Profile'                   => 'Migration_Company_Profile',
		);

		$results = array();
		foreach ( $migrations as $file_name => $class_name ) {
			$file_path = $migrations_dir . '/' . $file_name . '.php';

			if ( file_exists( $file_path ) ) {
				require_once $file_path;

				$class = __NAMESPACE__ . '\\' . $class_name;
				if ( class_exists( $class ) ) {
					$success                = $class::up();
					$results[ $class_name ] = $success ? 'OK' : 'FAILED';
				} else {
					$results[ $class_name ] = 'CLASS NOT FOUND';
				}
			} else {
				$results[ $class_name ] = 'FILE NOT FOUND: ' . $file_path;
			}
		}

		return $results;
	}

	/**
	 * Rollback all migrations (drop tables)
	 *
	 * @since 3.0.1
	 * @return array Results of each migration rollback
	 */
	public static function rollback_all() {
		$migrations_dir = __DIR__;

		// Reverse order to handle dependencies
		$migrations = array(
			'Migration_Company_Profile'                   => 'Migration_Company_Profile',
			'migration_2025_12_29_accessibility_fix_history_table' => 'Migration_2025_12_29_accessibility_fix_history_table',
			'migration_2025_12_20_form_issues_table'      => 'Migration_2025_12_20_form_issues_table',
			'migration_2025_12_20_form_submissions_table' => 'Migration_2025_12_20_form_submissions_table',
			'migration_2025_12_20_vendors_table'          => 'Migration_2025_12_20_vendors_table',
			'migration_2025_12_20_trackers_table'         => 'Migration_2025_12_20_trackers_table',
			'migration_2025_12_20_documents_table'        => 'Migration_2025_12_20_documents_table',
			'migration_2025_12_20_dsr_requests_table'     => 'Migration_2025_12_20_dsr_requests_table',
			'migration_2025_12_20_consent_logs_table'     => 'Migration_2025_12_20_consent_logs_table',
			'migration_2025_12_20_consent_table'          => 'Migration_2025_12_20_consent_table',
		);

		$results = array();
		foreach ( $migrations as $file_name => $class_name ) {
			$file_path = $migrations_dir . '/' . $file_name . '.php';

			if ( file_exists( $file_path ) ) {
				require_once $file_path;

				$class = __NAMESPACE__ . '\\' . $class_name;
				if ( class_exists( $class ) ) {
					$success                = $class::down();
					$results[ $class_name ] = $success ? 'DROPPED' : 'FAILED';
				} else {
					$results[ $class_name ] = 'CLASS NOT FOUND';
				}
			} else {
				$results[ $class_name ] = 'FILE NOT FOUND';
			}
		}

		return $results;
	}

	/**
	 * Check if all migrations have been run
	 *
	 * @since 3.0.1
	 * @return bool True if all tables exist, false otherwise
	 */
	public static function is_migrated() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'slos_consent',
			$wpdb->prefix . 'slos_dsr_requests',
			$wpdb->prefix . 'slos_documents',
			$wpdb->prefix . 'slos_trackers',
			$wpdb->prefix . 'slos_vendors',
			$wpdb->prefix . 'slos_form_submissions',
			$wpdb->prefix . 'slos_form_issues',
			$wpdb->prefix . 'slos_accessibility_fix_history',
			$wpdb->prefix . 'slos_company_profile',
		);

		foreach ( $tables as $table ) {
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get list of tables that exist
	 *
	 * @since 3.0.1
	 * @return array List of existing table names
	 */
	public static function get_existing_tables() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'slos_consent',
			$wpdb->prefix . 'slos_dsr_requests',
			$wpdb->prefix . 'slos_documents',
			$wpdb->prefix . 'slos_trackers',
			$wpdb->prefix . 'slos_vendors',
			$wpdb->prefix . 'slos_form_submissions',
			$wpdb->prefix . 'slos_form_issues',
			$wpdb->prefix . 'slos_accessibility_fix_history',
			$wpdb->prefix . 'slos_company_profile',
		);

		$existing = array();
		foreach ( $tables as $table ) {
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
				$existing[] = $table;
			}
		}

		return $existing;
	}
}
