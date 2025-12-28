<?php
/**
 * Settings API Controller
 *
 * Handles REST API endpoints for plugin settings.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalopsSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalopsSuite\API;

use ShahiLegalopsSuite\Admin\Settings;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsController
 *
 * REST API controller for settings endpoints.
 *
 * @since 1.0.0
 */
class SettingsController {

	/**
	 * Settings instance
	 *
	 * @since 1.0.0
	 * @var Settings
	 */
	private $settings;

	/**
	 * Initialize controller
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->settings = new Settings();
	}

	/**
	 * Register routes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		// Get all settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_editor' ),
			)
		);

		// Update settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
				'args'                => array(
					'settings' => array(
						'required' => true,
						'type'     => 'object',
					),
				),
			)
		);

		// Export settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings/export',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_settings' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
			)
		);

		// Import settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings/import',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_settings' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
				'args'                => array(
					'settings' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			)
		);
	}

	/**
	 * Get all settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_settings( $request ) {
		$settings = $this->settings->get_settings();

		return RestAPI::success( $settings, 'Settings retrieved successfully' );
	}

	/**
	 * Update settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function update_settings( $request ) {
		$new_settings = $request->get_param( 'settings' );

		if ( ! is_array( $new_settings ) ) {
			return RestAPI::error( 'Invalid settings format', 400 );
		}

		// Get current settings
		$current_settings = $this->settings->get_settings();

		// Merge and sanitize
		$updated_settings = array_merge(
			$current_settings,
			RestAPI::sanitize_request( $new_settings )
		);

		// Update
		$result = update_option( Settings::OPTION_NAME, $updated_settings );

		if ( $result === false ) {
			return RestAPI::error( 'Failed to update settings', 500 );
		}

		return RestAPI::success(
			$updated_settings,
			'Settings updated successfully'
		);
	}

	/**
	 * Export settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function export_settings( $request ) {
		$json = $this->settings->export_settings();

		if ( empty( $json ) ) {
			return RestAPI::error( 'Failed to export settings', 500 );
		}

		return RestAPI::success(
			array( 'json' => $json ),
			'Settings exported successfully'
		);
	}

	/**
	 * Import settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function import_settings( $request ) {
		$json = $request->get_param( 'settings' );

		if ( empty( $json ) ) {
			return RestAPI::error( 'No settings data provided', 400 );
		}

		$result = $this->settings->import_settings( $json );

		if ( ! $result ) {
			return RestAPI::error( 'Failed to import settings. Invalid data format.', 400 );
		}

		return RestAPI::success(
			$this->settings->get_settings(),
			'Settings imported successfully'
		);
	}
}
