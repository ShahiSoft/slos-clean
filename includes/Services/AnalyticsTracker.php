<?php
/**
 * Analytics Tracker Service
 *
 * Service for tracking analytics events, page views, user interactions,
 * and performance metrics throughout the plugin.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Services
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\QueryOptimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnalyticsTracker Class
 *
 * Provides methods for tracking various analytics events
 * and storing them in the database for later analysis.
 *
 * @since 1.0.0
 */
class AnalyticsTracker {

	/**
	 * Track an analytics event
	 *
	 * @since 1.0.0
	 * @param string $event_type Type of event.
	 * @param array  $event_data Additional event data.
	 * @param int    $user_id    User ID (optional, defaults to current user).
	 * @return int|false Event ID on success, false on failure.
	 */
	public static function track_event( $event_type, $event_data = array(), $user_id = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists (cached for performance)..
		if ( ! QueryOptimizer::table_exists_cached( $table ) ) {
			return false;
		}

		// Get user ID..
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}

		// Prepare data..
		$data = array(
			'event_type' => sanitize_text_field( $event_type ),
			'event_data' => wp_json_encode( $event_data ),
			'user_id'    => $user_id > 0 ? $user_id : null,
			'ip_address' => self::get_client_ip(),
			'user_agent' => self::get_user_agent(),
			'created_at' => current_time( 'mysql' ),
		);

		// Insert event..
		$result = $wpdb->insert( $table, $data );

		if ( $result === false ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Track a page view
	 *
	 * @since 1.0.0
	 * @param string $page_slug Page slug/identifier.
	 * @param array  $meta      Additional metadata.
	 * @return int|false Event ID on success, false on failure.
	 */
	public static function track_page_view( $page_slug, $meta = array() ) {
		$event_data = array_merge(
			array(
				'page'     => $page_slug,
				'url'      => self::get_current_url(),
				'referrer' => wp_get_referer(),
			),
			$meta
		);

		return self::track_event( 'page_view', $event_data );
	}

	/**
	 * Track a user interaction (click, form submission, etc.)
	 *
	 * @since 1.0.0
	 * @param string $action Action performed.
	 * @param string $target Target element/component.
	 * @param array  $meta   Additional metadata.
	 * @return int|false Event ID on success, false on failure.
	 */
	public static function track_interaction( $action, $target, $meta = array() ) {
		$event_data = array_merge(
			array(
				'action' => $action,
				'target' => $target,
			),
			$meta
		);

		return self::track_event( 'user_interaction', $event_data );
	}

	/**
	 * Track module usage
	 *
	 * @since 1.0.0
	 * @param string $module_id Module identifier.
	 * @param string $action    Action performed (enabled, disabled, configured).
	 * @param array  $meta      Additional metadata.
	 * @return int|false Event ID on success, false on failure.
	 */
	public static function track_module_usage( $module_id, $action, $meta = array() ) {
		$event_data = array_merge(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			),
			$meta
		);

		$event_type = $action === 'enabled' ? 'module_enabled' :
						( $action === 'disabled' ? 'module_disabled' : 'module_action' );

		return self::track_event( $event_type, $event_data );
	}

	/**
	 * Track performance metric
	 *
	 * @since 1.0.0
	 * @param string $metric_name Name of the metric.
	 * @param mixed  $value       Metric value.
	 * @param string $unit        Unit of measurement.
	 * @return int|false Event ID on success, false on failure.
	 */
	public static function track_performance( $metric_name, $value, $unit = '' ) {
		$event_data = array(
			'metric' => $metric_name,
			'value'  => $value,
			'unit'   => $unit,
		);

		return self::track_event( 'performance_metric', $event_data );
	}

	/**
	 * Track error or exception
	 *
	 * @since 1.0.0
	 * @param string $error_type  Type of error.
	 * @param string $message     Error message.
	 * @param array  $context     Error context.
	 * @return int|false Event ID on success, false on failure.
	 */
	public static function track_error( $error_type, $message, $context = array() ) {
		$event_data = array_merge(
			array(
				'error_type' => $error_type,
				'message'    => $message,
				'file'       => isset( $context['file'] ) ? $context['file'] : '',
				'line'       => isset( $context['line'] ) ? $context['line'] : '',
			),
			$context
		);

		return self::track_event( 'error_log', $event_data );
	}

	/**
	 * Track AJAX request
	 *
	 * @since 1.0.0
	 * @param string $action AJAX action name.
	 * @param bool   $success Whether the request was successful.
	 * @param array  $meta   Additional metadata.
	 * @return int|false Event ID on success, false on failure.
	 */
	public static function track_ajax_request( $action, $success = true, $meta = array() ) {
		$event_data = array_merge(
			array(
				'ajax_action' => $action,
				'success'     => $success,
			),
			$meta
		);

		return self::track_event( 'ajax_request', $event_data );
	}

	/**
	 * Track settings update
	 *
	 * @since 1.0.0
	 * @param string $section Settings section.
	 * @param array  $changes Changed values.
	 * @return int|false Event ID on success, false on failure.
	 */
	public static function track_settings_update( $section, $changes = array() ) {
		$event_data = array(
			'section' => $section,
			'changes' => $changes,
		);

		return self::track_event( 'settings_updated', $event_data );
	}

	/**
	 * Get client IP address
	 *
	 * @since 1.0.0
	 * @return string Client IP address
	 */
	private static function get_client_ip() {
		$ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					$ip = trim( $ip );

					if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
						return $ip;
					}
				}
			}
		}

		return 'UNKNOWN';
	}

	/**
	 * Get user agent string
	 *
	 * @since 1.0.0
	 * @return string User agent
	 */
	private static function get_user_agent() {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ?
				sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) :
				'Unknown';
	}

	/**
	 * Get current URL
	 *
	 * @since 1.0.0
	 * @return string Current URL
	 */
	private static function get_current_url() {
		$protocol = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		$host     = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : '';
		$uri      = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';

		return $protocol . '://' . $host . $uri;
	}

	/**
	 * Get analytics summary for a date range
	 *
	 * @since 1.0.0
	 * @param string $start_date Start date (Y-m-d H:i:s).
	 * @param string $end_date   End date (Y-m-d H:i:s).
	 * @return array Analytics summary
	 */
	public static function get_summary( $start_date, $end_date ) {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists (cached for performance)..
		if ( ! QueryOptimizer::table_exists_cached( $table ) ) {
			return array();
		}

		// Total events..
		$total_events = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		// Unique users..
		$unique_users = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) FROM $table WHERE created_at BETWEEN %s AND %s AND user_id IS NOT NULL",
				$start_date,
				$end_date
			)
		);

		// Event types breakdown..
		$event_types = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_type, COUNT(*) as count FROM $table WHERE created_at BETWEEN %s AND %s GROUP BY event_type ORDER BY count DESC",
				$start_date,
				$end_date
			)
		);

		return array(
			'total_events' => (int) $total_events,
			'unique_users' => (int) $unique_users,
			'event_types'  => $event_types,
			'start_date'   => $start_date,
			'end_date'     => $end_date,
		);
	}

	/**
	 * Clean old analytics data
	 *
	 * @since 1.0.0
	 * @param int $days Number of days to keep (default: 90).
	 * @return int Number of rows deleted
	 */
	public static function clean_old_data( $days = 90 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists (cached for performance)..
		if ( ! QueryOptimizer::table_exists_cached( $table ) ) {
			return 0;
		}

		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table WHERE created_at < %s",
				$cutoff_date
			)
		);

		return $deleted === false ? 0 : $deleted;
	}
}
