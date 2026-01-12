<?php
/**
 * System API Controller
 *
 * Handles REST API endpoints for system information and health checks.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\API;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SystemController
 *
 * REST API controller for system endpoints.
 *
 * @since 1.0.0
 */
class SystemController {

	/**
	 * Register routes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		// Health check..
		register_rest_route(
			RestAPI::get_namespace(),
			'/system/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_admin' ),
			)
		);

		// Plugin info..
		register_rest_route(
			RestAPI::get_namespace(),
			'/system/info',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_info' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_admin' ),
			)
		);
	}

	/**
	 * Get system status
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_status( $request ) {
		global $wpdb;

		$status = array(
			'healthy' => true,
			'checks'  => array(),
		);

		// Database check..
		$db_check                     = $wpdb->check_connection();
		$status['checks']['database'] = array(
			'status'  => $db_check ? 'ok' : 'error',
			'message' => $db_check ? 'Database connection OK' : 'Database connection failed',
		);

		if ( ! $db_check ) {
			$status['healthy'] = false;
		}

		// Tables check..
		$required_tables = array(
			$wpdb->prefix . 'shahi_analytics',
		);

		$missing_tables = array();
		foreach ( $required_tables as $table ) {
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
				$missing_tables[] = $table;
			}
		}

		$status['checks']['tables'] = array(
			'status'  => empty( $missing_tables ) ? 'ok' : 'warning',
			'message' => empty( $missing_tables ) ? 'All tables exist' : 'Missing tables: ' . implode( ', ', $missing_tables ),
		);

		// PHP version check..
		$php_version     = PHP_VERSION;
		$php_min_version = '7.4';
		$php_ok          = version_compare( $php_version, $php_min_version, '>=' );

		$status['checks']['php_version'] = array(
			'status'  => $php_ok ? 'ok' : 'warning',
			'message' => "PHP $php_version" . ( $php_ok ? '' : " (minimum $php_min_version recommended)" ),
		);

		// WordPress version check..
		global $wp_version;
		$wp_min_version = '5.8';
		$wp_ok          = version_compare( $wp_version, $wp_min_version, '>=' );

		$status['checks']['wp_version'] = array(
			'status'  => $wp_ok ? 'ok' : 'warning',
			'message' => "WordPress $wp_version" . ( $wp_ok ? '' : " (minimum $wp_min_version recommended)" ),
		);

		// Memory check..
		$memory_limit               = ini_get( 'memory_limit' );
		$status['checks']['memory'] = array(
			'status'  => 'ok',
			'message' => "Memory limit: $memory_limit",
		);

		return RestAPI::success( $status, 'System status retrieved successfully' );
	}

	/**
	 * Get plugin info
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_info( $request ) {
		$info = array(
			'name'        => 'ShahiLegalFlowSuite',
			'version'     => defined( 'SHAHI_LEGALFLOWSUITE_VERSION' ) ? SHAHI_LEGALFLOWSUITE_VERSION : '1.0.0',
			'author'      => 'ShahiLegalFlowSuite Team',
			'description' => 'Advanced WordPress template plugin with modular architecture',
			'php_version' => PHP_VERSION,
			'wp_version'  => get_bloginfo( 'version' ),
			'api_version' => 'v1',
			'endpoints'   => $this->get_available_endpoints(),
		);

		return RestAPI::success( $info, 'Plugin info retrieved successfully' );
	}

	/**
	 * Get available endpoints
	 *
	 * @since 1.0.0
	 * @return array Available endpoints
	 */
	private function get_available_endpoints() {
		return array(
			'analytics'  => array(
				'GET    /analytics/stats',
				'GET    /analytics/events',
				'POST   /analytics/track',
			),
			'modules'    => array(
				'GET    /modules',
				'GET    /modules/{id}',
				'POST   /modules/{id}/enable',
				'POST   /modules/{id}/disable',
				'PUT    /modules/{id}/settings',
			),
			'settings'   => array(
				'GET    /settings',
				'PUT    /settings',
				'POST   /settings/export',
				'POST   /settings/import',
			),
			'onboarding' => array(
				'GET    /onboarding/status',
				'POST   /onboarding/complete',
				'POST   /onboarding/reset',
			),
			'system'     => array(
				'GET    /system/status',
				'GET    /system/info',
			),
		);
	}
}
