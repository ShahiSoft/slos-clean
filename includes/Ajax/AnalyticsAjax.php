<?php
/**
 * Analytics AJAX Handler
 *
 * Handles AJAX requests for analytics data.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalopsSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalopsSuite\Ajax;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AnalyticsAjax
 *
 * AJAX handler for analytics-related operations.
 *
 * @since 1.0.0
 */
class AnalyticsAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_get_analytics', array( $this, 'get_analytics' ) );
		add_action( 'wp_ajax_shahi_export_analytics', array( $this, 'export_analytics' ) );
	}

	/**
	 * Get analytics data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_analytics() {
		// Verify request
		AjaxHandler::verify_request( 'shahi_analytics', 'edit_shahi_settings' );

		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			AjaxHandler::error( 'Analytics table not found' );
		}

		// Get parameters
		$period = isset( $_POST['period'] ) ? sanitize_text_field( $_POST['period'] ) : '30days';

		// Calculate date condition
		$date_condition = '';
		switch ( $period ) {
			case '7days':
				$date_condition = 'AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
				break;
			case '30days':
				$date_condition = 'AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
				break;
			case '90days':
				$date_condition = 'AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)';
				break;
		}

		// Get total events
		$total_events = $wpdb->get_var(
			"SELECT COUNT(*) FROM $analytics_table WHERE 1=1 $date_condition"
		);

		// Get events by type
		$events_by_type = $wpdb->get_results(
			"SELECT event_type, COUNT(*) as count 
             FROM $analytics_table 
             WHERE 1=1 $date_condition 
             GROUP BY event_type 
             ORDER BY count DESC 
             LIMIT 10",
			ARRAY_A
		);

		// Get unique users
		$unique_users = $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) FROM $analytics_table WHERE user_id > 0 $date_condition"
		);

		// Get daily stats (last 7 days)
		$daily_stats = $wpdb->get_results(
			"SELECT DATE(created_at) as date, COUNT(*) as count 
             FROM $analytics_table 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
             GROUP BY DATE(created_at) 
             ORDER BY date ASC",
			ARRAY_A
		);

		$analytics = array(
			'total_events'   => intval( $total_events ),
			'unique_users'   => intval( $unique_users ),
			'events_by_type' => $events_by_type,
			'daily_stats'    => $daily_stats,
			'period'         => $period,
		);

		AjaxHandler::success( $analytics, 'Analytics data retrieved successfully' );
	}

	/**
	 * Export analytics data
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function export_analytics() {
		// Verify request
		AjaxHandler::verify_request( 'shahi_export_analytics', 'manage_shahi_template' );

		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			AjaxHandler::error( 'Analytics table not found' );
		}

		// Get date range
		$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : '';
		$end_date   = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : '';

		$where = array( '1=1' );
		if ( ! empty( $start_date ) ) {
			$where[] = $wpdb->prepare( 'created_at >= %s', $start_date );
		}
		if ( ! empty( $end_date ) ) {
			$where[] = $wpdb->prepare( 'created_at <= %s', $end_date );
		}

		$where_clause = implode( ' AND ', $where );

		// Get events
		$events = $wpdb->get_results(
			"SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM $analytics_table WHERE $where_clause ORDER BY created_at DESC LIMIT 10000",
			ARRAY_A
		);

		// Format for CSV
		$csv_data   = array();
		$csv_data[] = array( 'ID', 'Event Type', 'Event Data', 'User ID', 'IP Address', 'User Agent', 'Created At' );

		foreach ( $events as $event ) {
			$csv_data[] = array(
				$event['id'],
				$event['event_type'],
				$event['event_data'],
				$event['user_id'],
				$event['ip_address'],
				$event['user_agent'],
				$event['created_at'],
			);
		}

		AjaxHandler::success(
			array(
				'csv_data'      => $csv_data,
				'total_records' => count( $events ),
			),
			'Analytics data exported successfully'
		);
	}
}
