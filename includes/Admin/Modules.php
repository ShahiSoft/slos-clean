<?php
/**
 * Modules Admin Page
 *
 * Manages plugin modules - enabling, disabling, and configuring individual features.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Core\Security;
use ShahiLegalFlowSuite\Modules\ModuleManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modules Page Class
 *
 * Handles the rendering and functionality of the modules management page.
 * Allows users to enable/disable modules and view module information.
 *
 * @since 1.0.0
 */
class Modules {

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Module manager instance
	 *
	 * @since 1.0.0
	 * @var ModuleManager
	 */
	private $module_manager;

	/**
	 * Initialize the modules page
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security       = new Security();
		$this->module_manager = ModuleManager::get_instance();

		// Add AJAX handlers
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'ajax_toggle_module' ) );
	}

	/**
	 * Render the modules page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render() {
		// Verify user capabilities
		if ( ! current_user_can( 'manage_shahi_modules' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		// Handle form submission
		if ( isset( $_POST['shahi_save_modules'] ) ) {
			$this->save_modules();
		}

		// Get modules data
		$modules      = $this->get_available_modules();
		$active_count = count(
			array_filter(
				$modules,
				function ( $module ) {
					return $module['enabled'];
				}
			)
		);

		// Load template
		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/modules.php';
	}

	/**
	 * Get available modules
	 *
	 * Returns an array of all available modules with their current state.
	 * Now uses ModuleManager for module data.
	 *
	 * @since 1.0.0
	 * @return array Available modules
	 */
	private function get_available_modules() {
		// Get modules from ModuleManager
		$modules = $this->module_manager->get_modules();

		$available_modules = array();
		foreach ( $modules as $module ) {
			$available_modules[ $module->get_key() ] = $module->to_array();
		}

		// Fallback: Keep original static modules if no modules registered
		if ( empty( $available_modules ) ) {
			$available_modules = array(
				'analytics'     => array(
					'key'         => 'analytics',
					'name'        => __( 'Analytics Tracking', 'shahi-legalflowsuite' ),
					'description' => __( 'Track user behavior, page views, and plugin usage with detailed analytics.', 'shahi-legalflowsuite' ),
					'icon'        => 'dashicons-chart-line',
					'version'     => '1.0.0',
					'author'      => 'ShahiLegalFlowSuite',
					'category'    => 'tracking',
					'enabled'     => false,
				),
				'notifications' => array(
					'key'         => 'notifications',
					'name'        => __( 'Email Notifications', 'shahi-legalflowsuite' ),
					'description' => __( 'Send automated email notifications for important events and activities.', 'shahi-legalflowsuite' ),
					'icon'        => 'dashicons-email',
					'version'     => '1.0.0',
					'author'      => 'ShahiLegalFlowSuite',
					'category'    => 'communication',
				),
				'cache'         => array(
					'key'         => 'cache',
					'name'        => __( 'Advanced Caching', 'shahi-legalflowsuite' ),
					'description' => __( 'Improve performance with intelligent caching mechanisms for database queries and assets.', 'shahi-legalflowsuite' ),
					'icon'        => 'dashicons-performance',
					'version'     => '1.0.0',
					'author'      => 'ShahiLegalFlowSuite',
					'category'    => 'performance',
				),
				'security'      => array(
					'key'         => 'security',
					'name'        => __( 'Enhanced Security', 'shahi-legalflowsuite' ),
					'description' => __( 'Additional security features including rate limiting, IP blocking, and audit logs.', 'shahi-legalflowsuite' ),
					'icon'        => 'dashicons-shield',
					'version'     => '1.0.0',
					'author'      => 'ShahiLegalFlowSuite',
					'category'    => 'security',
				),
				'api'           => array(
					'key'         => 'api',
					'name'        => __( 'REST API Extension', 'shahi-legalflowsuite' ),
					'description' => __( 'Extended REST API endpoints for external integrations and mobile apps.', 'shahi-legalflowsuite' ),
					'icon'        => 'dashicons-rest-api',
					'version'     => '1.0.0',
					'author'      => 'ShahiLegalFlowSuite',
					'category'    => 'developer',
				),
				'import_export' => array(
					'key'         => 'import_export',
					'name'        => __( 'Import/Export', 'shahi-legalflowsuite' ),
					'description' => __( 'Import and export plugin settings, data, and configurations with ease.', 'shahi-legalflowsuite' ),
					'icon'        => 'dashicons-database-export',
					'version'     => '1.0.0',
					'author'      => 'ShahiLegalFlowSuite',
					'category'    => 'tools',
					'enabled'     => false,
				),
			);

			// Get enabled modules from database for fallback
			$enabled_modules = $this->get_enabled_modules();

			// Merge enabled state with available modules
			foreach ( $available_modules as $key => &$module ) {
				$module['enabled'] = in_array( $key, $enabled_modules, true );
			}
		}

		return $available_modules;
	}

	/**
	 * Get enabled modules from database
	 *
	 * @since 1.0.0
	 * @return array Array of enabled module keys
	 */
	private function get_enabled_modules() {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_modules';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			return array();
		}

		$results = $wpdb->get_results(
			"SELECT module_key FROM $table WHERE is_enabled = 1",
			ARRAY_A
		);

		if ( empty( $results ) ) {
			return array();
		}

		return array_column( $results, 'module_key' );
	}

	/**
	 * Save modules configuration
	 *
	 * Processes the modules form submission and updates the database.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function save_modules() {
		// Verify nonce
		if ( ! isset( $_POST['shahi_modules_nonce'] ) || ! wp_verify_nonce( $_POST['shahi_modules_nonce'], 'shahi_save_modules' ) ) {
			add_settings_error(
				'shahi_modules',
				'invalid_nonce',
				__( 'Security check failed. Please try again.', 'shahi-legalflowsuite' ),
				'error'
			);
			return;
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_shahi_modules' ) ) {
			add_settings_error(
				'shahi_modules',
				'insufficient_permissions',
				__( 'You do not have sufficient permissions to perform this action.', 'shahi-legalflowsuite' ),
				'error'
			);
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'shahi_modules';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			add_settings_error(
				'shahi_modules',
				'table_not_found',
				__( 'Database table not found. Please reactivate the plugin.', 'shahi-legalflowsuite' ),
				'error'
			);
			return;
		}

		// Get enabled modules from POST
		$enabled_modules = isset( $_POST['enabled_modules'] ) && is_array( $_POST['enabled_modules'] )
			? array_map( 'sanitize_text_field', $_POST['enabled_modules'] )
			: array();

		// Get all available modules
		$available_modules = $this->get_available_modules();

		// Update each module's status
		$updated = 0;
		foreach ( $available_modules as $module ) {
			$is_enabled = in_array( $module['key'], $enabled_modules, true ) ? 1 : 0;

			// Check if module exists in database
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $table WHERE module_key = %s",
					$module['key']
				)
			);

			if ( $exists ) {
				// Update existing module
				$result = $wpdb->update(
					$table,
					array(
						'is_enabled'   => $is_enabled,
						'last_updated' => current_time( 'mysql' ),
					),
					array( 'module_key' => $module['key'] ),
					array( '%d', '%s' ),
					array( '%s' )
				);
			} else {
				// Insert new module
				$result = $wpdb->insert(
					$table,
					array(
						'module_key'   => $module['key'],
						'is_enabled'   => $is_enabled,
						'settings'     => '{}',
						'last_updated' => current_time( 'mysql' ),
					),
					array( '%s', '%d', '%s', '%s' )
				);
			}

			if ( $result !== false ) {
				++$updated;

				// Track analytics event
				$this->track_module_event( $module['key'], $is_enabled );
			}
		}

		// Add success message
		add_settings_error(
			'shahi_modules',
			'modules_saved',
			/* translators: %d: number of modules updated */
			sprintf( __( 'Successfully updated %d module(s).', 'shahi-legalflowsuite' ), $updated ),
			'success'
		);
	}

	/**
	 * Track module enable/disable event
	 *
	 * @since 1.0.0
	 * @param string $module_key Module key.
	 * @param int    $is_enabled Whether module is enabled (1) or disabled (0).
	 * @return void
	 */
	private function track_module_event( $module_key, $is_enabled ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if analytics table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_type = $is_enabled ? 'module_enabled' : 'module_disabled';
		$event_data = json_encode(
			array(
				'module_key'  => $module_key,
				'module_name' => $this->get_module_name( $module_key ),
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => $event_type,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $this->security->get_client_ip(),
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Get module name by key
	 *
	 * @since 1.0.0
	 * @param string $module_key Module key.
	 * @return string Module name
	 */
	private function get_module_name( $module_key ) {
		$modules = $this->get_available_modules();
		return isset( $modules[ $module_key ] ) ? $modules[ $module_key ]['name'] : $module_key;
	}

	/**
	 * Get module categories
	 *
	 * Returns an array of module categories for filtering.
	 *
	 * @since 1.0.0
	 * @return array Module categories
	 */
	public function get_module_categories() {
		return array(
			'all'           => __( 'All Modules', 'shahi-legalflowsuite' ),
			'tracking'      => __( 'Tracking', 'shahi-legalflowsuite' ),
			'communication' => __( 'Communication', 'shahi-legalflowsuite' ),
			'performance'   => __( 'Performance', 'shahi-legalflowsuite' ),
			'security'      => __( 'Security', 'shahi-legalflowsuite' ),
			'developer'     => __( 'Developer', 'shahi-legalflowsuite' ),
			'tools'         => __( 'Tools', 'shahi-legalflowsuite' ),
			'marketing'     => __( 'Marketing', 'shahi-legalflowsuite' ),
			'content'       => __( 'Content', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * AJAX handler for toggling module state
	 *
	 * Enables or disables a module via AJAX request.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_toggle_module() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'shahi_toggle_module' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed. Please refresh the page.', 'shahi-legalflowsuite' ),
				)
			);
		}

		// Check user capabilities
		if ( ! current_user_can( 'manage_shahi_modules' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'shahi-legalflowsuite' ),
				)
			);
		}

		// Get module key and enabled state
		$module_key = isset( $_POST['module_key'] ) ? sanitize_text_field( $_POST['module_key'] ) : '';
		$enabled    = isset( $_POST['enabled'] ) && $_POST['enabled'] === 'true';

		if ( empty( $module_key ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Module key is required.', 'shahi-legalflowsuite' ),
				)
			);
		}

		// Toggle module via ModuleManager
		if ( $enabled ) {
			$result = $this->module_manager->enable_module( $module_key );
		} else {
			$result = $this->module_manager->disable_module( $module_key );
		}

		if ( $result ) {
			// Track analytics event
			$this->track_module_event( $module_key, $enabled ? 1 : 0 );

			wp_send_json_success(
				array(
					'message'    => $enabled
						? __( 'Module enabled successfully.', 'shahi-legalflowsuite' )
						: __( 'Module disabled successfully.', 'shahi-legalflowsuite' ),
					'module_key' => $module_key,
					'enabled'    => $enabled,
				)
			);
		} else {
			// Check if failure was due to dependencies
			$module = $this->module_manager->get_module( $module_key );
			if ( $module && ! $module->dependencies_met() ) {
				$dependencies = $module->get_dependencies();
				wp_send_json_error(
					array(
						/* translators: %s: comma-separated list of required module names */
						'message' => sprintf(
							__( 'Cannot enable module. Required dependencies: %s', 'shahi-legalflowsuite' ),
							implode( ', ', $dependencies )
						),
					)
				);
			}

			// Check if failure was due to dependents
			$dependents = $this->module_manager->get_dependent_modules( $module_key );
			if ( ! empty( $dependents ) ) {
				wp_send_json_error(
					array(
						/* translators: %s: comma-separated list of dependent module names */
						'message' => sprintf(
							__( 'Cannot disable module. Other modules depend on it: %s', 'shahi-legalflowsuite' ),
							implode( ', ', $dependents )
						),
					)
				);
			}

			wp_send_json_error(
				array(
					'message' => __( 'Failed to toggle module. Please try again.', 'shahi-legalflowsuite' ),
				)
			);
		}
	}
}
