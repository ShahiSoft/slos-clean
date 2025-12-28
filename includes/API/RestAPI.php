<?php
/**
 * REST API Base Class
 *
 * Handles REST API route registration and initialization.
 * Provides a centralized system for registering all API endpoints.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalopsSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalopsSuite\API;

use ShahiLegalopsSuite\Core\Security;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RestAPI
 *
 * Main REST API registration and management class.
 *
 * @since 1.0.0
 */
class RestAPI {

	/**
	 * API namespace
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const NAMESPACE = 'shahi-legalops-suite/v1';

	/**
	 * Controller instances
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $controllers = array();

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Initialize REST API
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();
		$this->init_controllers();

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Initialize controller instances
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_controllers() {
		$this->controllers = array(
			'analytics'  => new AnalyticsController(),
			'modules'    => new ModulesController(),
			'settings'   => new SettingsController(),
			'onboarding' => new OnboardingController(),
			'system'     => new SystemController(),
		);
		// Consent-related controllers are now registered by the ConsentManagement module
	}

	/**
	 * Register all routes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		foreach ( $this->controllers as $controller ) {
			$controller->register_routes();
		}
	}

	/**
	 * Get API namespace
	 *
	 * @since 1.0.0
	 * @return string API namespace
	 */
	public static function get_namespace() {
		return self::NAMESPACE;
	}

	/**
	 * Permission callback for admin-only endpoints
	 *
	 * @since 1.0.0
	 * @return bool True if user has permission, false otherwise
	 */
	public static function permission_callback_admin() {
		return current_user_can( 'manage_shahi_template' );
	}

	/**
	 * Permission callback for editor-level endpoints
	 *
	 * @since 1.0.0
	 * @return bool True if user has permission, false otherwise
	 */
	public static function permission_callback_editor() {
		return current_user_can( 'edit_shahi_settings' );
	}

	/**
	 * Permission callback for public endpoints (authenticated users)
	 *
	 * @since 1.0.0
	 * @return bool True if user is logged in, false otherwise
	 */
	public static function permission_callback_authenticated() {
		return is_user_logged_in();
	}

	/**
	 * Send success response
	 *
	 * @since 1.0.0
	 * @param mixed  $data    Response data.
	 * @param string $message Optional success message.
	 * @param int    $status  HTTP status code.
	 * @return \WP_REST_Response Response object
	 */
	public static function success( $data = null, $message = '', $status = 200 ) {
		$response = array(
			'success' => true,
			'data'    => $data,
		);

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		return new \WP_REST_Response( $response, $status );
	}

	/**
	 * Send error response
	 *
	 * @since 1.0.0
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 * @param mixed  $data    Optional error data.
	 * @return \WP_Error Error object
	 */
	public static function error( $message, $status = 400, $data = null ) {
		return new \WP_Error(
			'shahi_api_error',
			$message,
			array(
				'status' => $status,
				'data'   => $data,
			)
		);
	}

	/**
	 * Sanitize API request data
	 *
	 * @since 1.0.0
	 * @param array $data Data to sanitize.
	 * @return array Sanitized data
	 */
	public static function sanitize_request( $data ) {
		if ( ! is_array( $data ) ) {
			return array();
		}

		$sanitized = array();

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = self::sanitize_request( $value );
			} elseif ( is_string( $value ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value );
			} else {
				$sanitized[ $key ] = $value;
			}
		}

		return $sanitized;
	}

	/**
	 * Validate required parameters
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @param array            $required Required parameter names.
	 * @return \WP_Error|bool True if valid, WP_Error if validation fails
	 */
	public static function validate_required_params( $request, $required ) {
		foreach ( $required as $param ) {
			if ( ! $request->has_param( $param ) || empty( $request->get_param( $param ) ) ) {
				return self::error(
					sprintf( 'Missing required parameter: %s', $param ),
					400
				);
			}
		}

		return true;
	}
}
