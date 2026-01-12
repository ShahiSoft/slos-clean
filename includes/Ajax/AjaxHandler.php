<?php
/**
 * AJAX Handler System
 *
 * Central handler for all AJAX requests in the plugin.
 * Provides consistent response format, security, and error handling.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

use ShahiLegalFlowSuite\Core\Security;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AjaxHandler
 *
 * Main AJAX handler registration and management class.
 *
 * @since 1.0.0
 */
class AjaxHandler {

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Handler instances
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $handlers = array();

	/**
	 * Initialize AJAX handler
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();
		$this->init_handlers();
		$this->register_hooks();
	}

	/**
	 * Initialize handler instances
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function init_handlers() {
		$this->handlers = array(
			'module'     => new ModuleAjax(),
			'analytics'  => new AnalyticsAjax(),
			'dashboard'  => new DashboardAjax(),
			'onboarding' => new OnboardingAjax(),
			'settings'   => new SettingsAjax(),
		);
	}

	/**
	 * Register WordPress AJAX hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_hooks() {
		foreach ( $this->handlers as $handler ) {
			$handler->register_ajax_actions();
		}
	}

	/**
	 * Send success response
	 *
	 * @since 1.0.0
	 * @param mixed  $data    Response data.
	 * @param string $message Optional success message.
	 * @return void
	 */
	public static function success( $data = null, $message = '' ) {
		$response = array(
			'success' => true,
			'data'    => $data,
		);

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		wp_send_json_success( $response );
	}

	/**
	 * Send error response
	 *
	 * @since 1.0.0
	 * @param string $message Error message.
	 * @param mixed  $data    Optional error data.
	 * @return void
	 */
	public static function error( $message, $data = null ) {
		$response = array(
			'message' => $message,
		);

		if ( $data !== null ) {
			$response['data'] = $data;
		}

		wp_send_json_error( $response );
	}

	/**
	 * Verify nonce and capability
	 *
	 * @since 1.0.0
	 * @param string $nonce_action Nonce action name.
	 * @param string $capability   Required capability.
	 * @return bool True if verified, dies with error if not
	 */
	public static function verify_request( $nonce_action, $capability = 'manage_options' ) {
		// Verify nonce..
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], $nonce_action ) ) {
			self::error( 'Security check failed' );
			return false;
		}

		// Check capability..
		if ( ! current_user_can( $capability ) ) {
			self::error( 'Insufficient permissions' );
			return false;
		}

		return true;
	}

	/**
	 * Sanitize AJAX request data
	 *
	 * @since 1.0.0
	 * @param array $data Data to sanitize.
	 * @return array Sanitized data
	 */
	public static function sanitize_data( $data ) {
		if ( ! is_array( $data ) ) {
			return array();
		}

		$sanitized = array();

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized[ $key ] = self::sanitize_data( $value );
			} elseif ( is_string( $value ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value );
			} else {
				$sanitized[ $key ] = $value;
			}
		}

		return $sanitized;
	}
}
