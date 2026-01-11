<?php
/**
 * DSR Audit Log Repository
 *
 * Handles database operations for DSR audit logs.
 * Implements privacy-focused logging with hashed IP/UA for GDPR compliance.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Repositories
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Database\Repositories;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Audit_Log_Repository Class
 *
 * Repository for DSR audit log operations with privacy-first design.
 *
 * @since 3.0.1
 */
class DSR_Audit_Log_Repository extends Base_Repository {

	/**
	 * Get table name (without prefix)
	 *
	 * @since 3.0.1
	 * @return string Table name without prefix
	 */
	protected function get_table_name(): string {
		return 'slos_dsr_logs';
	}

	/**
	 * Create audit log entry
	 *
	 * @since 3.0.1
	 * @param int    $request_id DSR request ID
	 * @param string $action     Action performed (e.g., 'submit', 'verify', 'status_change')
	 * @param array  $data       Additional data {
	 *     @type int        $actor_id          User ID performing action (null for system/requester)
	 *     @type string     $note              Human-readable note
	 *     @type array      $metadata          Additional structured data
	 *     @type string     $ip_address        IP address (will be hashed)
	 *     @type string     $user_agent        User agent (will be hashed)
	 * }
	 * @return int|false Inserted log ID or false on failure
	 */
	public function log_action( int $request_id, string $action, array $data = array() ): int|false {
		$defaults = array(
			'actor_id'   => null,
			'note'       => '',
			'metadata'   => array(),
			'ip_address' => '',
			'user_agent' => '',
		);

		$data = wp_parse_args( $data, $defaults );

		// Hash IP and user agent for privacy
		$ip_hash = ! empty( $data['ip_address'] )
			? hash( 'sha256', $data['ip_address'] )
			: null;

		$ua_hash = ! empty( $data['user_agent'] )
			? hash( 'sha256', $data['user_agent'] )
			: null;

		$insert = array(
			'request_id'      => absint( $request_id ),
			'action'          => sanitize_text_field( $action ),
			'actor_id'        => ! empty( $data['actor_id'] ) ? absint( $data['actor_id'] ) : null,
			'note'            => ! empty( $data['note'] ) ? sanitize_textarea_field( $data['note'] ) : null,
			'metadata'        => ! empty( $data['metadata'] ) ? wp_json_encode( $data['metadata'] ) : null,
			'ip_hash'         => $ip_hash,
			'user_agent_hash' => $ua_hash,
			'created_at'      => current_time( 'mysql' ),
		);

		global $wpdb;
		$result = $wpdb->insert( $this->table, $insert );

		if ( false === $result ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Get logs for a specific request
	 *
	 * @since 3.0.1
	 * @param int   $request_id DSR request ID
	 * @param array $args       Query arguments {
	 *     @type string $action    Filter by action type
	 *     @type int    $limit     Number of logs to retrieve
	 *     @type int    $offset    Offset for pagination
	 *     @type string $order     Order direction (ASC or DESC)
	 * }
	 * @return array Array of log entries
	 */
	public function get_logs_by_request( int $request_id, array $args = array() ): array {
		global $wpdb;

		$defaults = array(
			'action' => '',
			'limit'  => 100,
			'offset' => 0,
			'order'  => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$where = $wpdb->prepare( 'request_id = %d', $request_id );

		if ( ! empty( $args['action'] ) ) {
			$where .= $wpdb->prepare( ' AND action = %s', $args['action'] );
		}

		$order_by = sprintf( 'ORDER BY created_at %s', $args['order'] === 'ASC' ? 'ASC' : 'DESC' );
		$limit    = sprintf( 'LIMIT %d OFFSET %d', absint( $args['limit'] ), absint( $args['offset'] ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			"SELECT * FROM {$this->table} WHERE {$where} {$order_by} {$limit}",
			ARRAY_A
		);

		if ( ! $results ) {
			return array();
		}

		// Decode metadata JSON
		foreach ( $results as &$log ) {
			if ( ! empty( $log['metadata'] ) ) {
				$log['metadata'] = json_decode( $log['metadata'], true );
			}
		}

		return $results;
	}

	/**
	 * Get logs with filters (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param array $filters {
	 *     @type int    $request_id    Filter by request ID
	 *     @type string $action        Filter by action type
	 *     @type int    $actor_id      Filter by actor user ID
	 *     @type string $start_date    Filter by start date (YYYY-MM-DD)
	 *     @type string $end_date      Filter by end date (YYYY-MM-DD)
	 *     @type int    $limit         Number of logs to retrieve
	 *     @type int    $offset        Offset for pagination
	 *     @type string $order         Order direction (ASC or DESC)
	 * }
	 * @return array Array with 'logs' and 'total' keys
	 */
	public function get_logs( array $filters = array() ): array {
		global $wpdb;

		$defaults = array(
			'request_id' => 0,
			'action'     => '',
			'actor_id'   => 0,
			'start_date' => '',
			'end_date'   => '',
			'limit'      => 50,
			'offset'     => 0,
			'order'      => 'DESC',
		);

		$filters = wp_parse_args( $filters, $defaults );

		$where = array( '1=1' );

		if ( ! empty( $filters['request_id'] ) ) {
			$where[] = $wpdb->prepare( 'request_id = %d', $filters['request_id'] );
		}

		if ( ! empty( $filters['action'] ) ) {
			$where[] = $wpdb->prepare( 'action = %s', $filters['action'] );
		}

		if ( ! empty( $filters['actor_id'] ) ) {
			$where[] = $wpdb->prepare( 'actor_id = %d', $filters['actor_id'] );
		}

		if ( ! empty( $filters['start_date'] ) ) {
			$where[] = $wpdb->prepare( 'created_at >= %s', $filters['start_date'] . ' 00:00:00' );
		}

		if ( ! empty( $filters['end_date'] ) ) {
			$where[] = $wpdb->prepare( 'created_at <= %s', $filters['end_date'] . ' 23:59:59' );
		}

		$where_clause = implode( ' AND ', $where );
		$order_by     = sprintf( 'ORDER BY created_at %s', $filters['order'] === 'ASC' ? 'ASC' : 'DESC' );
		$limit_clause = sprintf( 'LIMIT %d OFFSET %d', absint( $filters['limit'] ), absint( $filters['offset'] ) );

		// Get total count
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} WHERE {$where_clause}" );

		// Get logs
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$logs = $wpdb->get_results(
			"SELECT * FROM {$this->table} WHERE {$where_clause} {$order_by} {$limit_clause}",
			ARRAY_A
		);

		if ( ! $logs ) {
			$logs = array();
		}

		// Decode metadata JSON
		foreach ( $logs as &$log ) {
			if ( ! empty( $log['metadata'] ) ) {
				$log['metadata'] = json_decode( $log['metadata'], true );
			}
		}

		return array(
			'logs'  => $logs,
			'total' => $total,
		);
	}

	/**
	 * Get log count for a request
	 *
	 * @since 3.0.1
	 * @param int    $request_id DSR request ID
	 * @param string $action     Optional action filter
	 * @return int Log count
	 */
	public function get_log_count( int $request_id, string $action = '' ): int {
		global $wpdb;

		$where = $wpdb->prepare( 'request_id = %d', $request_id );

		if ( ! empty( $action ) ) {
			$where .= $wpdb->prepare( ' AND action = %s', $action );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table} WHERE {$where}" );
	}

	/**
	 * Delete logs for a request (GDPR erasure)
	 *
	 * @since 3.0.1
	 * @param int $request_id DSR request ID
	 * @return int|false Number of rows deleted or false on failure
	 */
	public function delete_logs_by_request( int $request_id ): int|false {
		global $wpdb;

		$result = $wpdb->delete(
			$this->table,
			array( 'request_id' => absint( $request_id ) ),
			array( '%d' )
		);

		return $result;
	}

	/**
	 * Get recent actions for dashboard widget
	 *
	 * @since 3.0.1
	 * @param int $limit Number of recent actions to retrieve
	 * @return array Array of recent log entries
	 */
	public function get_recent_actions( int $limit = 10 ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		if ( ! $results ) {
			return array();
		}

		// Decode metadata JSON
		foreach ( $results as &$log ) {
			if ( ! empty( $log['metadata'] ) ) {
				$log['metadata'] = json_decode( $log['metadata'], true );
			}
		}

		return $results;
	}

	/**
	 * Get action statistics for reporting
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date (YYYY-MM-DD)
	 * @param string $end_date   End date (YYYY-MM-DD)
	 * @return array Action statistics grouped by action type
	 */
	public function get_action_statistics( string $start_date, string $end_date ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT action, COUNT(*) as count 
				FROM {$this->table} 
				WHERE created_at >= %s AND created_at <= %s 
				GROUP BY action 
				ORDER BY count DESC",
				$start_date . ' 00:00:00',
				$end_date . ' 23:59:59'
			),
			ARRAY_A
		);

		return $results ?: array();
	}
}
