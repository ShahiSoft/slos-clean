<?php
/**
 * Fired during plugin activation
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Core
 * @license    GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activator Class
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class Activator {


	/**
	 * Activate the plugin
	 *
	 * Creates database tables, sets default options, and performs initial setup.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function activate() {
		global $wpdb;

		// Create custom database tables
		self::create_tables();

		// Set default options
		self::set_default_options();

		// Set installation timestamp
		if ( ! get_option( 'shahi_legalops_suite_installed_at' ) ) {
			add_option( 'shahi_legalops_suite_installed_at', current_time( 'mysql' ) );
		}

		// Set version
		update_option( 'shahi_legalops_suite_version', SHAHI_LEGALFLOWSUITE_VERSION );

		// Add custom capabilities
		\ShahiLegalFlowSuite\Admin\MenuManager::add_capabilities();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Add performance indexes
		self::add_analytics_indexes();
	}

	/**
	 * Create custom database tables
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Run compliance module migrations
		self::run_compliance_migrations();

		// Analytics table
		$table_analytics = $wpdb->prefix . 'shahi_analytics';
		$sql_analytics   = "CREATE TABLE IF NOT EXISTS {$table_analytics} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_data longtext,
            user_id bigint(20) UNSIGNED DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent varchar(255) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset_collate};";

		dbDelta( $sql_analytics );

		// Modules table
		$table_modules = $wpdb->prefix . 'shahi_modules';
		$sql_modules   = "CREATE TABLE IF NOT EXISTS {$table_modules} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            module_key varchar(100) NOT NULL,
            is_enabled tinyint(1) NOT NULL DEFAULT 1,
            settings longtext,
            last_updated datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY module_key (module_key)
        ) {$charset_collate};";

		dbDelta( $sql_modules );

		// Onboarding table
		$table_onboarding = $wpdb->prefix . 'shahi_onboarding';
		$sql_onboarding   = "CREATE TABLE IF NOT EXISTS {$table_onboarding} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            step_completed varchar(100) NOT NULL,
            data_collected longtext,
            completed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY step_completed (step_completed)
        ) {$charset_collate};";

		dbDelta( $sql_onboarding );

		// Accessibility Scanner tables (Option C - high performance storage)
		$table_scans = $wpdb->prefix . 'slos_a11y_scans';
		$sql_scans   = "CREATE TABLE IF NOT EXISTS {$table_scans} (
            post_id bigint(20) UNSIGNED NOT NULL,
            page_title varchar(255) NOT NULL,
            url text NULL,
            score int(11) NOT NULL DEFAULT 100,
            issues_count int(11) NOT NULL DEFAULT 0,
            critical_count int(11) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'passed',
            last_scan datetime NULL,
            autofix_enabled tinyint(1) NOT NULL DEFAULT 0,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (post_id),
            KEY status (status),
            KEY score (score)
        ) {$charset_collate};";

		dbDelta( $sql_scans );

		$table_issues = $wpdb->prefix . 'slos_a11y_issues';
		$sql_issues   = "CREATE TABLE IF NOT EXISTS {$table_issues} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            checker_id varchar(100) NOT NULL,
            severity varchar(20) NOT NULL DEFAULT 'warning',
            message text NULL,
            element text NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY checker_id (checker_id),
            KEY severity (severity)
        ) {$charset_collate};";

		dbDelta( $sql_issues );

		// Legal Documents Tables
		$table_docs = $wpdb->prefix . 'slos_legal_docs';
		$sql_docs   = "CREATE TABLE IF NOT EXISTS {$table_docs} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			type varchar(50) NOT NULL,
			title varchar(255) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'draft',
			profile_version int(11) NOT NULL DEFAULT 1,
			locale varchar(10) NOT NULL DEFAULT 'en_US',
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY type (type),
			KEY status (status)
		) {$charset_collate};";

		dbDelta( $sql_docs );

		$table_versions = $wpdb->prefix . 'slos_legal_doc_versions';
		$sql_versions   = "CREATE TABLE IF NOT EXISTS {$table_versions} (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			doc_id bigint(20) UNSIGNED NOT NULL,
			content longtext,
			profile_version int(11) NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY doc_id (doc_id)
		) {$charset_collate};";

		dbDelta( $sql_versions );
	}

	/**
	 * Run compliance module migrations
	 *
	 * Executes all database migrations for compliance modules (consent, DSR, documents, etc.)
	 *
	 * @since 3.0.1
	 * @return void
	 */
	private static function run_compliance_migrations() {
		// Load migration runner
		$runner_file = dirname( __DIR__ ) . '/Database/Migrations/Runner.php';

		if ( file_exists( $runner_file ) ) {
			require_once $runner_file;

			// Run all migrations
			$results = \ShahiLegalFlowSuite\Database\Migrations\Runner::run_all();

			// Log results
			foreach ( $results as $migration => $status ) {
				if ( $status !== 'OK' ) {
					error_log( sprintf( 'Migration %s failed with status: %s', $migration, $status ) );
				}
			}
		}
	}

	/**
	 * Set default plugin options
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function set_default_options() {
		// General settings
		if ( ! get_option( 'shahi_legalops_suite_settings' ) ) {
			$default_settings = array(
				'plugin_enabled' => true,
				'admin_email'    => get_option( 'admin_email' ),
				'date_format'    => get_option( 'date_format' ),
				'time_format'    => get_option( 'time_format' ),
			);
			add_option( 'shahi_legalops_suite_settings', $default_settings );
		}

		// Advanced settings
		if ( ! get_option( 'shahi_legalops_suite_advanced_settings' ) ) {
			$advanced_settings = array(
				'debug_mode'     => false,
				'custom_css'     => '',
				'custom_js'      => '',
				'developer_mode' => false,
				'api_rate_limit' => 100,
			);
			add_option( 'shahi_legalops_suite_advanced_settings', $advanced_settings );
		}

		// Uninstall preferences (preserve data by default)
		if ( ! get_option( 'shahi_legalops_suite_uninstall_preferences' ) ) {
			$uninstall_preferences = array(
				'preserve_all'        => true,
				'delete_settings'     => false,
				'delete_analytics'    => false,
				'delete_posts'        => false,
				'delete_capabilities' => false,
				'delete_tables'       => false,
			);
			add_option( 'shahi_legalops_suite_uninstall_preferences', $uninstall_preferences );
		}

		// Enabled modules
		if ( ! get_option( 'shahi_legalops_suite_modules_enabled' ) ) {
			$modules_enabled = array(
				'analytics' => true,
				'security'  => true,
			);
			add_option( 'shahi_legalops_suite_modules_enabled', $modules_enabled );
		}

		// Onboarding completion status
		if ( ! get_option( 'shahi_legalops_suite_onboarding_completed' ) ) {
			add_option( 'shahi_legalops_suite_onboarding_completed', false );
		}
	}

	/**
	 * Add performance indexes to analytics tables
	 *
	 * Creates non-blocking indexes on frequently-queried columns to improve performance.
	 * Checks for index existence before creating to avoid errors.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function add_analytics_indexes() {
		global $wpdb;

		$events_table    = $wpdb->prefix . 'shahi_analytics_events';
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if analytics_events table exists and add indexes
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $events_table ) ) === $events_table ) {
			self::create_index_if_not_exists( $events_table, 'idx_event_time', array( 'event_time' ) );
			self::create_index_if_not_exists( $events_table, 'idx_user_id', array( 'user_id' ) );
			self::create_index_if_not_exists( $events_table, 'idx_event_type', array( 'event_type' ) );
			self::create_index_if_not_exists( $events_table, 'idx_event_type_time', array( 'event_type', 'event_time' ) );
		}

		// Check if analytics table exists and add index
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $analytics_table ) ) === $analytics_table ) {
			self::create_index_if_not_exists( $analytics_table, 'idx_created_at', array( 'created_at' ) );
		}
	}

	/**
	 * Create database index if it doesn't exist
	 *
	 * Safely creates an index on a table by first checking if it already exists.
	 * MySQL doesn't support IF NOT EXISTS for ADD INDEX, so we check manually.
	 *
	 * @since 3.0.1
	 * @param string $table_name The table name (with prefix).
	 * @param string $index_name The name of the index to create.
	 * @param array  $columns    Array of column names for the index.
	 * @return bool True if index was created or already exists, false on error.
	 */
	private static function create_index_if_not_exists( $table_name, $index_name, $columns ) {
		global $wpdb;

		// Check if index already exists
		$index_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW INDEX FROM %i WHERE Key_name = %s',
				$table_name,
				$index_name
			)
		);

		// Index already exists, skip creation
		if ( ! empty( $index_exists ) ) {
			return true;
		}

		// Build column list for index - escape each column name
		$escaped_columns = array();
		foreach ( $columns as $column ) {
			$escaped_columns[] = '`' . esc_sql( $column ) . '`';
		}
		$column_list = implode( ', ', $escaped_columns );

		// Create the index
		$sql = "ALTER TABLE `{$table_name}` ADD INDEX `{$index_name}` ({$column_list})";

		$result = $wpdb->query( $sql );

		// Log any errors
		if ( $result === false && ! empty( $wpdb->last_error ) ) {
			error_log(
				sprintf(
					'[Shahi LegalFlowSuite] Failed to create index %s on table %s: %s',
					$index_name,
					$table_name,
					$wpdb->last_error
				)
			);
			return false;
		}

		return true;
	}
}
