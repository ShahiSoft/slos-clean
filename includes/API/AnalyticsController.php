<?php
/**
 * Analytics API Controller
 *
 * Handles REST API endpoints for analytics data.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\API;

use ShahiLegalFlowSuite\Core\Security;

// Exit if accessed directly..
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
		// Get analytics stats..
		register_rest_route(
			RestAPI::get_namespace(),
			'/analytics/stats',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_editor' ),
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

		// Get events..
		register_rest_route(
			RestAPI::get_namespace(),
			'/analytics/events',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_events' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_editor' ),
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

		// Track new event..
		register_rest_route(
			RestAPI::get_namespace(),
			'/analytics/track',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'track_event' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_authenticated' ),
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
		$period          = is_string( $period ) ? sanitize_text_field( $period ) : '';
		$allowed_periods = array( '7days', '30days', '90days', 'all' );
		if ( ! in_array( $period, $allowed_periods, true ) ) {
			$period = '30days';
		}

		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $analytics_table ) ) !== $analytics_table ) {
			return RestAPI::error( 'Analytics table not found', 404 );
		}

		// Calculate date range as an interval in days.
		$interval_days = 0;
		switch ( $period ) {
			case '7days':
				$interval_days = 7;
				break;
			case '30days':
				$interval_days = 30;
				break;
			case '90days':
				$interval_days = 90;
				break;
			case 'all':
			default:
				$interval_days = 0;
		}

		$table_sql = esc_sql( $analytics_table );

		// Get total events...
		if ( $interval_days > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$total_events = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %s WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)',
					$table_sql,
					$interval_days
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$total_events = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %s WHERE 1=1', $table_sql ) );
		}

		// Get events by type..
		if ( $interval_days > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$events_by_type = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT event_type, COUNT(*) as count 
             FROM %s 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) 
             GROUP BY event_type 
             ORDER BY count DESC',
					$table_sql,
					$interval_days
				),
				ARRAY_A
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$events_by_type = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT event_type, COUNT(*) as count 
             FROM %s 
             WHERE 1=1 
             GROUP BY event_type 
             ORDER BY count DESC',
					$table_sql
				),
				ARRAY_A
			);
		}

		// Get unique users..
		if ( $interval_days > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$unique_users = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(DISTINCT user_id) FROM %s WHERE user_id > 0 AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)',
					$table_sql,
					$interval_days
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$unique_users = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT user_id) FROM %s WHERE user_id > 0', $table_sql ) );
		}

		// Get recent events..
		if ( $interval_days > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$recent_events = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT event_type, created_at 
             FROM %s 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) 
             ORDER BY created_at DESC 
             LIMIT 10',
					$table_sql,
					$interval_days
				),
				ARRAY_A
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$recent_events = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT event_type, created_at 
             FROM %s 
             WHERE 1=1 
             ORDER BY created_at DESC 
             LIMIT 10',
					$table_sql
				),
				ARRAY_A
			);
		}

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
		$event_type = is_string( $event_type ) ? sanitize_text_field( $event_type ) : '';
		$limit      = absint( $request->get_param( 'limit' ) );
		$offset     = absint( $request->get_param( 'offset' ) );

		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists...
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $analytics_table ) ) !== $analytics_table ) {
			return RestAPI::error( 'Analytics table not found', 404 );
		}

		$table_sql = esc_sql( $analytics_table );

		// Get total count...
		if ( ! empty( $event_type ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$total = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %s WHERE event_type = %s',
					$table_sql,
					$event_type
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$total = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %s WHERE 1=1', $table_sql ) );
		}

		// Get events...
		if ( ! empty( $event_type ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$events = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM %s WHERE event_type = %s ORDER BY created_at DESC LIMIT %d OFFSET %d',
					$table_sql,
					$event_type,
					$limit,
					$offset
				),
				ARRAY_A
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- analytics stats do not require caching.
			$events = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT id, event_type, event_data, user_id, ip_address, user_agent, created_at FROM %s WHERE 1=1 ORDER BY created_at DESC LIMIT %d OFFSET %d',
					$table_sql,
					$limit,
					$offset
				),
				ARRAY_A
			);
		}

		// Parse JSON event_data..
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

		// Check if table exists...
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $analytics_table ) ) !== $analytics_table ) {
			return RestAPI::error( 'Analytics table not found', 404 );
		}

		// Insert event...
		$result = $wpdb->insert(
			$analytics_table,
			array(
				'event_type' => $event_type,
				'event_data' => wp_json_encode( $event_data ),
				'user_id'    => get_current_user_id(),
				'ip_address' => $this->security->get_client_ip(),
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ), 0, 255 ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $_SERVER input is sanitized with wp_unslash and substr
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			return RestAPI::error( 'Failed to track event', 500 );
		}

		return RestAPI::success(
			array( 'event_id' => $wpdb->insert_id ),
			'Event tracked successfully',
			201
		);
	}
}
