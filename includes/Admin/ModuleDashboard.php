<?php
/**
 * Module Dashboard Admin Page
 *
 * Premium module management dashboard with advanced visualizations and controls.
 * Features interactive cards, usage statistics, and enhanced module management.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Core\Security;
use ShahiLegalopsSuite\Modules\ModuleManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Dashboard Page Class
 *
 * Premium dashboard for module management with advanced features:
 * - 3D card animations
 * - Usage statistics and analytics
 * - Quick actions and bulk operations
 * - Module dependency visualization
 * - Performance metrics
 *
 * @since 1.0.0
 */
class ModuleDashboard {

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
	 * Initialize the module dashboard
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security       = new Security();
		$this->module_manager = ModuleManager::get_instance();

		// Register AJAX handlers immediately (not lazy)
		$this->register_ajax_handlers();
	}

	/**
	 * Register AJAX handlers
	 *
	 * @since 3.0.1
	 * @return void
	 */
	private function register_ajax_handlers() {
		add_action( 'wp_ajax_shahi_toggle_module_premium', array( $this, 'ajax_toggle_module' ) );
		add_action( 'wp_ajax_shahi_get_module_stats', array( $this, 'ajax_get_module_stats' ) );
		add_action( 'wp_ajax_shahi_bulk_module_action', array( $this, 'ajax_bulk_action' ) );
	}

	/**
	 * Render the module dashboard
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render() {
		// Verify user capabilities
		if ( ! current_user_can( 'manage_shahi_modules' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'shahi-legalops-suite' ) );
		}

		// Enqueue module dashboard styles
		wp_enqueue_style(
			'shahi-legalops-suite-dashboard',
			SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/css/admin-dashboard-new.css',
			array(),
			SHAHI_LEGALOPS_SUITE_VERSION
		);
		wp_enqueue_style(
			'shahi-legalops-suite-modules',
			SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/css/admin-modules-new.css',
			array( 'shahi-legalops-suite-dashboard' ),
			SHAHI_LEGALOPS_SUITE_VERSION
		);

		// Get modules data with enhanced statistics
		$modules = $this->get_modules_with_stats();

		// Calculate dashboard metrics
		$stats = $this->calculate_dashboard_stats( $modules );

		// Load template
		include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/module-dashboard.php';
	}

	/**
	 * Get modules with enhanced statistics
	 *
	 * @since 1.0.0
	 * @return array Modules with statistics
	 */
	private function get_modules_with_stats() {
		$module_objects = $this->module_manager->get_modules();

		$modules = array();

		// Convert module objects to arrays first
		foreach ( $module_objects as $module_obj ) {
			$slug   = $module_obj->get_key();
			$module = $module_obj->to_array();

			// Enhance each module with additional data
			$module['slug']              = $slug;
			$module['usage_count']       = $this->get_module_usage_count( $slug );
			$module['last_used']         = $this->get_module_last_used( $slug );
			$module['performance_score'] = $this->calculate_performance_score( $slug );
			$module['dependencies']      = $this->get_module_dependencies( $slug );
			$module['status_class']      = $module['enabled'] ? 'active' : 'inactive';
			$module['category']          = $this->get_module_category( $slug );
			$module['priority']          = $this->get_module_priority( $slug );

			$modules[ $slug ] = $module;
		}

		return $modules;
	}

	/**
	 * Calculate dashboard statistics
	 *
	 * @since 1.0.0
	 * @param array $modules Module data
	 * @return array Dashboard statistics
	 */
	private function calculate_dashboard_stats( $modules ) {
		$total    = count( $modules );
		$active   = count(
			array_filter(
				$modules,
				function ( $m ) {
					return $m['enabled'];
				}
			)
		);
		$inactive = $total - $active;

		// Calculate average performance score
		$perf_scores     = array_column( $modules, 'performance_score' );
		$avg_performance = ! empty( $perf_scores ) ? round( array_sum( $perf_scores ) / count( $perf_scores ) ) : 0;

		// Get most used modules
		$most_used = $this->get_most_used_modules( $modules, 3 );

		return array(
			'total'           => $total,
			'active'          => $active,
			'inactive'        => $inactive,
			'activation_rate' => $total > 0 ? round( ( $active / $total ) * 100 ) : 0,
			'avg_performance' => $avg_performance,
			'most_used'       => $most_used,
		);
	}

	/**
	 * Get module usage count
	 *
	 * @since 1.0.0
	 * @param string $module_slug Module slug
	 * @return int Usage count
	 */
	private function get_module_usage_count( $module_slug ) {
		// Get from transient/option
		$counts = get_option( 'shahi_module_usage_counts', array() );
		return isset( $counts[ $module_slug ] ) ? (int) $counts[ $module_slug ] : 0;
	}

	/**
	 * Get module last used timestamp
	 *
	 * @since 1.0.0
	 * @param string $module_slug Module slug
	 * @return string|null Last used timestamp
	 */
	private function get_module_last_used( $module_slug ) {
		$last_used = get_option( 'shahi_module_last_used', array() );
		return isset( $last_used[ $module_slug ] ) ? $last_used[ $module_slug ] : null;
	}

	/**
	 * Calculate performance score for module
	 *
	 * @since 1.0.0
	 * @param string $module_slug Module slug
	 * @return int Performance score (0-100)
	 */
	private function calculate_performance_score( $module_slug ) {
		// Calculate based on module status and health metrics
		// For now, return a simple score based on enabled status
		$module_manager = ModuleManager::get_instance();
		$module         = $module_manager->get_module( $module_slug );

		if ( ! $module ) {
			return 0;
		}

		// Base score for enabled modules
		$score = $module->is_enabled() ? 85 : 50;

		// Future: Add more sophisticated metrics
		// - Configuration completeness
		// - Error rates
		// - Resource usage
		// - User engagement

		return $score;
	}

	/**
	 * Get module dependencies
	 *
	 * @since 1.0.0
	 * @param string $module_slug Module slug
	 * @return array Dependencies
	 */
	private function get_module_dependencies( $module_slug ) {
		// Define module dependencies
		$dependencies = array(
			'page-builder'   => array( 'custom-post-types', 'widgets' ),
			'seo-tools'      => array( 'custom-post-types' ),
			'social-sharing' => array(),
		);

		return isset( $dependencies[ $module_slug ] ) ? $dependencies[ $module_slug ] : array();
	}

	/**
	 * Get module category
	 *
	 * @since 1.0.0
	 * @param string $module_slug Module slug
	 * @return string Category
	 */
	private function get_module_category( $module_slug ) {
		$categories = array(
			'custom-post-types' => 'Content',
			'custom-taxonomies' => 'Content',
			'widgets'           => 'UI Components',
			'shortcodes'        => 'UI Components',
			'custom-fields'     => 'Content',
			'page-builder'      => 'Design',
			'seo-tools'         => 'Marketing',
			'social-sharing'    => 'Marketing',
		);

		return isset( $categories[ $module_slug ] ) ? $categories[ $module_slug ] : 'General';
	}

	/**
	 * Get module priority
	 *
	 * @since 1.0.0
	 * @param string $module_slug Module slug
	 * @return string Priority level
	 */
	private function get_module_priority( $module_slug ) {
		$priorities = array(
			'custom-post-types' => 'high',
			'custom-taxonomies' => 'high',
			'widgets'           => 'medium',
			'shortcodes'        => 'medium',
			'custom-fields'     => 'high',
			'page-builder'      => 'medium',
			'seo-tools'         => 'high',
			'social-sharing'    => 'low',
		);

		return isset( $priorities[ $module_slug ] ) ? $priorities[ $module_slug ] : 'medium';
	}

	/**
	 * Get most used modules
	 *
	 * @since 1.0.0
	 * @param array $modules All modules
	 * @param int   $limit Number of modules to return
	 * @return array Most used modules
	 */
	private function get_most_used_modules( $modules, $limit = 3 ) {
		usort(
			$modules,
			function ( $a, $b ) {
				return $b['usage_count'] - $a['usage_count'];
			}
		);

		return array_slice( $modules, 0, $limit );
	}

	/**
	 * AJAX handler for toggling modules
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_toggle_module() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_module_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shahi-legalops-suite' ) ) );
		}

		if ( ! current_user_can( 'manage_shahi_modules' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'shahi-legalops-suite' ) ) );
		}

		$module_slug = isset( $_POST['module'] ) ? sanitize_text_field( $_POST['module'] ) : '';
		// Handle enabled: "1"/"0" from JS, or boolean from other callers
		$enabled_raw = $_POST['enabled'] ?? '';
		$enabled     = ( $enabled_raw === '1' || $enabled_raw === 1 || $enabled_raw === true || $enabled_raw === 'true' );

		if ( empty( $module_slug ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid module.', 'shahi-legalops-suite' ) ) );
		}

		// Toggle module
		$result = $this->module_manager->toggle_module( $module_slug, $enabled );

		if ( $result ) {
			// Update usage count
			$this->increment_module_usage( $module_slug );

			wp_send_json_success(
				array(
					'message' => $enabled ? __( 'Module activated successfully.', 'shahi-legalops-suite' ) : __( 'Module deactivated successfully.', 'shahi-legalops-suite' ),
					'enabled' => $enabled,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to toggle module.', 'shahi-legalops-suite' ) ) );
		}
	}

	/**
	 * AJAX handler for getting module statistics
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_get_module_stats() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_module_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shahi-legalops-suite' ) ) );
		}

		if ( ! current_user_can( 'manage_shahi_modules' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'shahi-legalops-suite' ) ) );
		}

		$module_slug = isset( $_POST['module'] ) ? sanitize_text_field( $_POST['module'] ) : '';

		if ( empty( $module_slug ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid module.', 'shahi-legalops-suite' ) ) );
		}

		$stats = array(
			'usage_count'       => $this->get_module_usage_count( $module_slug ),
			'last_used'         => $this->get_module_last_used( $module_slug ),
			'performance_score' => $this->calculate_performance_score( $module_slug ),
		);

		wp_send_json_success( $stats );
	}

	/**
	 * AJAX handler for bulk module actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_bulk_action() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_module_dashboard' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'shahi-legalops-suite' ) ) );
		}

		if ( ! current_user_can( 'manage_shahi_modules' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'shahi-legalops-suite' ) ) );
		}

		$action  = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
		$modules = isset( $_POST['modules'] ) ? (array) $_POST['modules'] : array();

		if ( empty( $action ) || empty( $modules ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid bulk action.', 'shahi-legalops-suite' ) ) );
		}

		$results = array();
		foreach ( $modules as $module_slug ) {
			$enabled                 = ( $action === 'enable' );
			$results[ $module_slug ] = $this->module_manager->toggle_module( sanitize_text_field( $module_slug ), $enabled );
		}

		$success_count = count( array_filter( $results ) );

		wp_send_json_success(
			array(
				'message' => sprintf( __( '%d modules updated successfully.', 'shahi-legalops-suite' ), $success_count ),
				'results' => $results,
			)
		);
	}

	/**
	 * Increment module usage count
	 *
	 * @since 1.0.0
	 * @param string $module_slug Module slug
	 * @return void
	 */
	private function increment_module_usage( $module_slug ) {
		$counts                 = get_option( 'shahi_module_usage_counts', array() );
		$counts[ $module_slug ] = isset( $counts[ $module_slug ] ) ? $counts[ $module_slug ] + 1 : 1;
		update_option( 'shahi_module_usage_counts', $counts );

		$last_used                 = get_option( 'shahi_module_last_used', array() );
		$last_used[ $module_slug ] = current_time( 'mysql' );
		update_option( 'shahi_module_last_used', $last_used );
	}
}
