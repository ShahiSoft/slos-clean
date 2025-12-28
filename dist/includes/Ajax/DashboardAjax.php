<?php
/**
 * Dashboard AJAX Handler
 *
 * Handles AJAX requests for dashboard operations.
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
 * Class DashboardAjax
 *
 * AJAX handler for dashboard-related operations.
 *
 * @since 1.0.0
 */
class DashboardAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_refresh_stats', array( $this, 'refresh_stats' ) );
		add_action( 'wp_ajax_shahi_complete_checklist_item', array( $this, 'complete_checklist_item' ) );
	}

	/**
	 * Refresh dashboard statistics
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function refresh_stats() {
		// Verify request
		AjaxHandler::verify_request( 'shahi_dashboard', 'edit_shahi_settings' );

		global $wpdb;

		// Get module stats
		$modules         = get_option( 'shahi_modules', array() );
		$enabled_modules = array_filter(
			$modules,
			function ( $module ) {
				return isset( $module['enabled'] ) && $module['enabled'];
			}
		);

		// Get analytics stats (if table exists)
		$analytics_table = $wpdb->prefix . 'shahi_analytics';
		$total_events    = 0;
		$events_today    = 0;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) === $analytics_table ) {
			$total_events = intval( $wpdb->get_var( "SELECT COUNT(*) FROM $analytics_table" ) );
			$events_today = intval(
				$wpdb->get_var(
					"SELECT COUNT(*) FROM $analytics_table WHERE DATE(created_at) = CURDATE()"
				)
			);
		}

		// Get user stats
		$total_users = count_users();

		// Get onboarding completion
		$onboarding_completed = get_option( 'shahi_onboarding_completed', false );

		// Get recent activity (last 5 events)
		$recent_activity = array();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $analytics_table ) ) === $analytics_table ) {
			$recent_activity = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT event_type, event_data, created_at 
					 FROM $analytics_table 
					 ORDER BY created_at DESC 
					 LIMIT %d",
					5
				),
				ARRAY_A
			);
		}

		$stats = array(
			'modules'         => array(
				'total'    => count( $modules ),
				'enabled'  => count( $enabled_modules ),
				'disabled' => count( $modules ) - count( $enabled_modules ),
			),
			'analytics'       => array(
				'total_events' => $total_events,
				'events_today' => $events_today,
			),
			'users'           => array(
				'total' => $total_users['total_users'],
			),
			'onboarding'      => array(
				'completed' => $onboarding_completed,
			),
			'recent_activity' => $recent_activity,
		);

		AjaxHandler::success( $stats, 'Dashboard stats refreshed successfully' );
	}

	/**
	 * Complete checklist item
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function complete_checklist_item() {
		// Verify request
		AjaxHandler::verify_request( 'shahi_complete_checklist', 'edit_shahi_settings' );

		// Get item ID
		if ( ! isset( $_POST['item_id'] ) ) {
			AjaxHandler::error( 'Item ID is required' );
		}

		$item_id = sanitize_key( $_POST['item_id'] );

		// Get checklist
		$checklist = get_option( 'shahi_checklist', array() );

		// Add item if not exists
		if ( ! in_array( $item_id, $checklist ) ) {
			$checklist[] = $item_id;
			update_option( 'shahi_checklist', $checklist );

			// Track analytics event
			$this->track_checklist_event( $item_id );
		}

		AjaxHandler::success(
			array( 'checklist' => $checklist ),
			'Checklist item completed successfully'
		);
	}

	/**
	 * Track checklist analytics event
	 *
	 * @since 1.0.0
	 * @param string $item_id Item ID.
	 * @return void
	 */
	private function track_checklist_event( $item_id ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'item_id' => $item_id,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'checklist_completed',
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
