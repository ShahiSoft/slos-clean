<?php
/**
 * Analytics API Controller
 *
 * Handles REST API endpoints for analytics data.
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
 * Class AnalyticsController
 *
 * REST API controller for analytics endpoints.
 *
 * @since 1.0.0
 */
class AnalyticsController {

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Initialize controller
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();
	}

	/**
	 * Register routes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		// Get analytics stats
		register_rest_route(
			RestAPI::get_namespace(),
			'/analytics/stats',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_editor' ),
				'args'                => array(
					'period' => array(
						'type'              => 'string',
						'default'           => '30days',
						'enum'              => array( '7days', '30days', '90days', 'all' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Get events
		register_rest_route(
			RestAPI::get_namespace(),
			'/analytics/events',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_events' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_editor' ),
				'args'                => array(
					'event_type' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'limit'      => array(
						'type'              => 'integer',
						'default'           => 100,
						'minimum'           => 1,
						'maximum'           => 1000,
						'sanitize_callback' => 'absint',
					),
					'offset'     => array(
						'type'              => 'integer',
						'default'           => 0,
						'minimum'           => 0,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Track new event
		register_rest_route(
			RestAPI::get_namespace(),
			'/analytics/track',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'track_event' ),
				'permission_callback' => array( 'ShahiLegalopsSuite\API\RestAPI', 'permission_callback_authenticated' ),
				'args'                => array(
					'event_type' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'event_data' => array(
						'type'    => 'object',
						'default' => array(),
					),
				),
			)
		);
	}

	/**
	 * Get analytics stats
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_stats( $request ) {
		global $wpdb;

		$period          = $request->get_param( 'period' );
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return RestAPI::error( 'Analytics table not found', 404 );
		}

		// Calculate date range
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
			default:
				$date_condition = '';
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
             ORDER BY count DESC",
			ARRAY_A
		);

		// Get unique users
		$unique_users = $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) FROM $analytics_table WHERE user_id > 0 $date_condition"
		);

		// Get recent events
		$recent_events = $wpdb->get_results(
			"SELECT event_type, created_at 
             FROM $analytics_table 
             WHERE 1=1 $date_condition 
             ORDER BY created_at DESC 
             LIMIT 10",
			ARRAY_A
		);

		$stats = array(
			'period'         => $period,
			'total_events'   => intval( $total_events ),
			'unique_users'   => intval( $unique_users ),
			'events_by_type' => $events_by_type,
			'recent_events'  => $recent_events,
		);

		return RestAPI::success( $stats, 'Analytics stats retrieved successfully' );
	}

	/**
	 * Get events
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_events( $request ) {
		global $wpdb;

		$event_type = $request->get_param( 'event_type' );
		$limit      = $request->get_param( 'limit' );
		$offset     = $request->get_param( 'offset' );

		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return RestAPI::error( 'Analytics table not found', 404 );
		}

		// Build query
		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $event_type ) ) {
			$where[]  = 'event_type = %s';
			$values[] = $event_type;
		}

		$where_clause = implode( ' AND ', $where );

		// Get total count
		$total_query = "SELECT COUNT(*) FROM $analytics_table WHERE $where_clause";
		if ( ! empty( $values ) ) {
			$total_query = $wpdb->prepare( $total_query, $values );
		}
		$total = $wpdb->get_var( $total_query );

		// Get events
		$query    = "SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM $analytics_table WHERE $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$values[] = $limit;
		$values[] = $offset;

		$events = $wpdb->get_results(
			$wpdb->prepare( $query, $values ),
			ARRAY_A
		);

		// Parse JSON event_data
		foreach ( $events as &$event ) {
			if ( ! empty( $event['event_data'] ) ) {
				$event['event_data'] = json_decode( $event['event_data'], true );
			}
		}

		$response = array(
			'events' => $events,
			'total'  => intval( $total ),
			'limit'  => $limit,
			'offset' => $offset,
		);

		return RestAPI::success( $response, 'Events retrieved successfully' );
	}

	/**
	 * Track new event
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function track_event( $request ) {
		global $wpdb;

		$event_type = $request->get_param( 'event_type' );
		$event_data = $request->get_param( 'event_data' );

		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return RestAPI::error( 'Analytics table not found', 404 );
		}

		// Insert event
		$result = $wpdb->insert(
			$analytics_table,
			array(
				'event_type' => $event_type,
				'event_data' => json_encode( $event_data ),
				'user_id'    => get_current_user_id(),
				'ip_address' => $this->security->get_client_ip(),
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);

		if ( $result === false ) {
			return RestAPI::error( 'Failed to track event', 500 );
		}

		return RestAPI::success(
			array( 'event_id' => $wpdb->insert_id ),
			'Event tracked successfully',
			201
		);
	}
}
