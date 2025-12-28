<?php
/**
 * Onboarding AJAX Handler
 *
 * Handles AJAX requests for onboarding process.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OnboardingAjax
 *
 * AJAX handler for onboarding-related operations.
 *
 * @since 1.0.0
 */
class OnboardingAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_save_onboarding_step', array( $this, 'save_onboarding_step' ) );
		add_action( 'wp_ajax_shahi_complete_onboarding', array( $this, 'complete_onboarding' ) );
	}

	/**
	 * Save onboarding step data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_onboarding_step() {
		// Verify request
		AjaxHandler::verify_request( 'shahi_onboarding', 'manage_shahi_template' );

		// Get step number
		if ( ! isset( $_POST['step'] ) ) {
			AjaxHandler::error( 'Step number is required' );
		}

		$step = intval( $_POST['step'] );
		$data = isset( $_POST['data'] ) ? $_POST['data'] : array();

		// Get current onboarding data
		$onboarding_data = get_option( 'shahi_onboarding_data', array() );

		// Save step data
		$onboarding_data[ $step ] = AjaxHandler::sanitize_data( $data );
		update_option( 'shahi_onboarding_data', $onboarding_data );

		// Track analytics event
		$this->track_onboarding_event( 'step_completed', $step );

		AjaxHandler::success(
			array(
				'step' => $step,
				'data' => $onboarding_data[ $step ],
			),
			sprintf( 'Step %d saved successfully', $step )
		);
	}

	/**
	 * Complete onboarding process
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function complete_onboarding() {
		// Verify request
		AjaxHandler::verify_request( 'shahi_onboarding', 'manage_shahi_template' );

		// Get final data
		$data = isset( $_POST['data'] ) ? $_POST['data'] : array();

		// Save completion flag
		update_option( 'shahi_onboarding_completed', true );

		// Save final data
		if ( ! empty( $data ) ) {
			$onboarding_data          = get_option( 'shahi_onboarding_data', array() );
			$onboarding_data['final'] = AjaxHandler::sanitize_data( $data );
			update_option( 'shahi_onboarding_data', $onboarding_data );
		}

		// Track analytics event
		$this->track_onboarding_event( 'completed', 'final' );

		AjaxHandler::success(
			array( 'completed' => true ),
			'Onboarding completed successfully'
		);
	}

	/**
	 * Track onboarding analytics event
	 *
	 * @since 1.0.0
	 * @param string     $action Action performed.
	 * @param int|string $step   Step number or identifier.
	 * @return void
	 */
	private function track_onboarding_event( $action, $step ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'action' => $action,
				'step'   => $step,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'onboarding_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
