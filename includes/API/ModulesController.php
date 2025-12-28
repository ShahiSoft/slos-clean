<?php
/**
 * Modules API Controller
 *
 * Handles REST API endpoints for module management.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalopsSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalopsSuite\API;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModulesController
 *
 * REST API controller for module management endpoints.
 *
 * @since 1.0.0
 */
class ModulesController {

	/**
	 * Register routes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		// List all modules
		register_rest_route(
			RestAPI::get_namespace(),
			'/modules',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_modules' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_editor' ),
			)
		);

		// Get single module
		register_rest_route(
			RestAPI::get_namespace(),
			'/modules/(?P<id>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_module' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_editor' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// Enable module
		register_rest_route(
			RestAPI::get_namespace(),
			'/modules/(?P<id>[a-zA-Z0-9_-]+)/enable',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'enable_module' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// Disable module
		register_rest_route(
			RestAPI::get_namespace(),
			'/modules/(?P<id>[a-zA-Z0-9_-]+)/disable',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'disable_module' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
				),
			)
		);

		// Update module settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/modules/(?P<id>[a-zA-Z0-9_-]+)/settings',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_module_settings' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
				'args'                => array(
					'id'       => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
					),
					'settings' => array(
						'required' => true,
						'type'     => 'object',
					),
				),
			)
		);
	}

	/**
	 * Get all modules
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_modules( $request ) {
		$modules = get_option( 'shahi_modules', array() );

		if ( empty( $modules ) ) {
			// Return default modules structure
			$modules = $this->get_default_modules();
		}

		return RestAPI::success( $modules, 'Modules retrieved successfully' );
	}

	/**
	 * Get single module
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_module( $request ) {
		$module_id = $request->get_param( 'id' );
		$modules   = get_option( 'shahi_modules', array() );

		if ( empty( $modules ) ) {
			$modules = $this->get_default_modules();
		}

		if ( ! isset( $modules[ $module_id ] ) ) {
			return RestAPI::error( 'Module not found', 404 );
		}

		return RestAPI::success( $modules[ $module_id ], 'Module retrieved successfully' );
	}

	/**
	 * Enable module
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function enable_module( $request ) {
		$module_id = $request->get_param( 'id' );
		$modules   = get_option( 'shahi_modules', array() );

		if ( empty( $modules ) ) {
			$modules = $this->get_default_modules();
		}

		if ( ! isset( $modules[ $module_id ] ) ) {
			return RestAPI::error( 'Module not found', 404 );
		}

		$modules[ $module_id ]['enabled'] = true;
		update_option( 'shahi_modules', $modules );

		return RestAPI::success(
			$modules[ $module_id ],
			'Module enabled successfully'
		);
	}

	/**
	 * Disable module
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function disable_module( $request ) {
		$module_id = $request->get_param( 'id' );
		$modules   = get_option( 'shahi_modules', array() );

		if ( empty( $modules ) ) {
			$modules = $this->get_default_modules();
		}

		if ( ! isset( $modules[ $module_id ] ) ) {
			return RestAPI::error( 'Module not found', 404 );
		}

		$modules[ $module_id ]['enabled'] = false;
		update_option( 'shahi_modules', $modules );

		return RestAPI::success(
			$modules[ $module_id ],
			'Module disabled successfully'
		);
	}

	/**
	 * Update module settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function update_module_settings( $request ) {
		$module_id = $request->get_param( 'id' );
		$settings  = $request->get_param( 'settings' );

		$modules = get_option( 'shahi_modules', array() );

		if ( empty( $modules ) ) {
			$modules = $this->get_default_modules();
		}

		if ( ! isset( $modules[ $module_id ] ) ) {
			return RestAPI::error( 'Module not found', 404 );
		}

		// Merge new settings with existing
		if ( ! isset( $modules[ $module_id ]['settings'] ) ) {
			$modules[ $module_id ]['settings'] = array();
		}

		$modules[ $module_id ]['settings'] = array_merge(
			$modules[ $module_id ]['settings'],
			RestAPI::sanitize_request( $settings )
		);

		update_option( 'shahi_modules', $modules );

		return RestAPI::success(
			$modules[ $module_id ],
			'Module settings updated successfully'
		);
	}

	/**
	 * Get default modules
	 *
	 * @since 1.0.0
	 * @return array Default modules
	 */
	private function get_default_modules() {
		return array(
			'landing_pages' => array(
				'id'          => 'landing_pages',
				'name'        => 'Landing Pages',
				'description' => 'Create custom landing pages',
				'enabled'     => true,
				'settings'    => array(),
			),
			'analytics'     => array(
				'id'          => 'analytics',
				'name'        => 'Analytics',
				'description' => 'Track user interactions',
				'enabled'     => true,
				'settings'    => array(),
			),
			'seo'           => array(
				'id'          => 'seo',
				'name'        => 'SEO Tools',
				'description' => 'Search engine optimization',
				'enabled'     => false,
				'settings'    => array(),
			),
		);
	}
}
