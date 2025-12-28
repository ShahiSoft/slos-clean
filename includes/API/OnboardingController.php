<?php
/**
 * Onboarding API Controller
 *
 * Handles REST API endpoints for onboarding process.
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
 * Class OnboardingController
 *
 * REST API controller for onboarding endpoints.
 *
 * @since 1.0.0
 */
class OnboardingController {

	/**
	 * Onboarding option name
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_NAME = 'shahi_onboarding_status';

	/**
	 * Register routes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		// Get onboarding status
		register_rest_route(
			RestAPI::get_namespace(),
			'/onboarding/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_status' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
			)
		);

		// Complete onboarding step
		register_rest_route(
			RestAPI::get_namespace(),
			'/onboarding/complete',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'complete_step' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
				'args'                => array(
					'step' => array(
						'required'          => true,
						'type'              => 'integer',
						'minimum'           => 1,
						'maximum'           => 5,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Reset onboarding
		register_rest_route(
			RestAPI::get_namespace(),
			'/onboarding/reset',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reset' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_admin' ),
			)
		);
	}

	/**
	 * Get onboarding status
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_status( $request ) {
		$status = get_option(
			self::OPTION_NAME,
			array(
				'completed'       => false,
				'current_step'    => 1,
				'steps_completed' => array(),
				'dismissed'       => false,
			)
		);

		return RestAPI::success( $status, 'Onboarding status retrieved successfully' );
	}

	/**
	 * Complete onboarding step
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function complete_step( $request ) {
		$step = $request->get_param( 'step' );

		$status = get_option(
			self::OPTION_NAME,
			array(
				'completed'       => false,
				'current_step'    => 1,
				'steps_completed' => array(),
				'dismissed'       => false,
			)
		);

		// Add step to completed steps
		if ( ! in_array( $step, $status['steps_completed'] ) ) {
			$status['steps_completed'][] = $step;
		}

		// Update current step
		if ( $step >= $status['current_step'] ) {
			$status['current_step'] = $step + 1;
		}

		// Check if all steps completed
		if ( count( $status['steps_completed'] ) >= 5 ) {
			$status['completed'] = true;
		}

		update_option( self::OPTION_NAME, $status );

		return RestAPI::success( $status, 'Step completed successfully' );
	}

	/**
	 * Reset onboarding
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function reset( $request ) {
		$status = array(
			'completed'       => false,
			'current_step'    => 1,
			'steps_completed' => array(),
			'dismissed'       => false,
		);

		update_option( self::OPTION_NAME, $status );

		return RestAPI::success( $status, 'Onboarding reset successfully' );
	}
}
