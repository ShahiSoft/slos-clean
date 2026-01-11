<?php
/**
 * DSR Repository
 *
 * Handles database operations for Data Subject Requests (GDPR/CCPA/LGPD, etc.).
 * Implements lifecycle storage, SLA tracking, and privacy-preserving identifiers.
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
 * DSR_Repository Class
 *
 * Concrete repository implementation for DSR request table operations.
 *
 * @since 3.0.1
 */
class DSR_Repository extends Base_Repository {

	/**
	 * Get table name (without prefix)
	 *
	 * @since 3.0.1
	 * @return string Table name without prefix
	 */
	protected function get_table_name(): string {
		// Align with existing plugin tables using the slos_ prefix
		return 'slos_dsr_requests';
	}

	/**
	 * Get full table name (with prefix)
	 *
	 * @since 3.0.1
	 * @return string Full table name with WordPress prefix
	 */
	public function get_full_table_name(): string {
		return $this->table;
	}

	/**
	 * Create new DSR request with SLA calculation and privacy hashes
	 *
	 * @since 3.0.1
	 * @param array $data { request_type, email, user_id, regulation, details }
	 * @return int|false Inserted request ID or false
	 */
	public function create_request( array $data ) {
		$defaults = array(
			'request_type' => 'access',
			'status'       => 'pending_verification',
			'email'        => '',
			'user_id'      => null,
			'regulation'   => 'GDPR', // GDPR/CCPA/LGPD/UK-GDPR/PIPEDA/POPIA
			'submitted_at' => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		// SLA days per regulation (business days)
		$sla_days     = $this->get_sla_days( $data['regulation'] );
		$due_date     = $this->calculate_due_date( $data['submitted_at'], $sla_days );
		$verification = wp_generate_password( 32, false );
		$ip_hash      = hash( 'sha256', $this->get_client_ip() );
		$ua_hash      = hash( 'sha256', $_SERVER['HTTP_USER_AGENT'] ?? '' );

		$insert = array(
			'request_type'       => sanitize_text_field( $data['request_type'] ),
			'status'             => 'pending_verification',
			'requester_email'    => sanitize_email( $data['email'] ),
			'user_id'            => ! empty( $data['user_id'] ) ? absint( $data['user_id'] ) : null,
			'regulation'         => sanitize_text_field( $data['regulation'] ),
			'verification_token' => sanitize_text_field( $verification ),
			'submitted_at'       => sanitize_text_field( $data['submitted_at'] ),
			'sla_days'           => absint( $sla_days ),
			'sla_deadline'       => sanitize_text_field( $due_date ),
			'ip_hash'            => $ip_hash,
			'user_agent_hash'    => $ua_hash,
		);

		$format = array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s' );

		$result = $this->wpdb->insert( $this->table, $insert, $format );
		if ( false === $result ) {
			error_log( sprintf( 'DSR create_request failed: %s', $this->wpdb->last_error ) );
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Find request by verification token (only pending_verification)
	 *
	 * @since 3.0.1
	 * @param string $token Verification token
	 * @return object|null Record object or null
	 */
	public function find_by_token( string $token ) {
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE verification_token = %s AND status = 'pending_verification'",
				$token
			)
		);
	}

	/**
	 * List requests with filters and pagination
	 *
	 * @since 3.0.1
	 * @param array $filters status, request_type, regulation, date_from, date_to, assignee
	 * @param int   $limit   Rows per page
	 * @param int   $offset  Offset
	 * @return array Array of records
	 */
	public function list_requests( array $filters = array(), int $limit = 50, int $offset = 0 ): array {
		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $filters['status'] ) ) {
			$where[]  = 'status = %s';
			$values[] = sanitize_text_field( $filters['status'] );
		}

		if ( ! empty( $filters['request_type'] ) ) {
			$where[]  = 'request_type = %s';
			$values[] = sanitize_text_field( $filters['request_type'] );
		}

		if ( ! empty( $filters['regulation'] ) ) {
			$where[]  = 'regulation = %s';
			$values[] = sanitize_text_field( $filters['regulation'] );
		}

		if ( ! empty( $filters['date_from'] ) ) {
			$where[]  = 'submitted_at >= %s';
			$values[] = sanitize_text_field( $filters['date_from'] );
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where[]  = 'submitted_at <= %s';
			$values[] = sanitize_text_field( $filters['date_to'] );
		}

		$where_sql = implode( ' AND ', $where );

		$sql      = "SELECT * FROM {$this->table} WHERE {$where_sql} ORDER BY submitted_at DESC LIMIT %d OFFSET %d";
		$values[] = $limit;
		$values[] = $offset;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$prepared = $this->wpdb->prepare( $sql, $values );
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $this->wpdb->get_results( $prepared );
	}

	/**
	 * Update request status with optional metadata
	 *
	 * @since 3.0.1
	 * @param int    $id     Request ID
	 * @param string $status New status
	 * @param array  $metadata Optional metadata (processed_by, admin_notes)
	 * @return bool True on success
	 */
	public function update_status( int $id, string $status, array $metadata = array() ): bool {
		$data = array(
			'status'     => sanitize_text_field( $status ),
			'updated_at' => current_time( 'mysql' ),
		);

		if ( 'verified' === $status ) {
			$data['verified_at'] = current_time( 'mysql' );
		} elseif ( 'completed' === $status || 'rejected' === $status ) {
			$data['completed_at'] = current_time( 'mysql' );
		}

		if ( ! empty( $metadata['processed_by'] ) ) {
			$data['processed_by'] = absint( $metadata['processed_by'] );
		}
		if ( ! empty( $metadata['admin_notes'] ) ) {
			$data['admin_notes'] = sanitize_text_field( $metadata['admin_notes'] );
		}

		return $this->update( $id, $data );
	}

	/**
	 * Statistics grouped by status
	 *
	 * @since 3.0.1
	 * @return array status => count
	 */
	public function stats_by_status(): array {
		$results = $this->wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status"
		);

		$stats = array();
		foreach ( $results as $row ) {
			$stats[ $row->status ] = (int) $row->count;
		}
		return $stats;
	}

	/**
	 * Statistics grouped by request type
	 *
	 * @since 3.0.1
	 * @return array type => count
	 */
	public function stats_by_type(): array {
		$results = $this->wpdb->get_results(
			"SELECT request_type, COUNT(*) as count FROM {$this->table} GROUP BY request_type"
		);

		$stats = array();
		foreach ( $results as $row ) {
			$stats[ $row->request_type ] = (int) $row->count;
		}
		return $stats;
	}

	/**
	 * SLA days by regulation
	 *
	 * @since 3.0.1
	 * @param string $regulation Regulation code
	 * @return int SLA days
	 */
	private function get_sla_days( string $regulation ): int {
		$defaults = array(
			'GDPR'    => 30,
			'UK-GDPR' => 30,
			'CCPA'    => 45,
			'LGPD'    => 15,
			'PIPEDA'  => 30,
			'POPIA'   => 30,
		);
		return isset( $defaults[ $regulation ] ) ? (int) $defaults[ $regulation ] : 30;
	}

	/**
	 * Calculate due date using business days
	 *
	 * @since 3.0.1
	 * @param string $start  Start datetime (mysql)
	 * @param int    $days   Business days
	 * @return string Due date (mysql)
	 */
	private function calculate_due_date( string $start, int $days ): string {
		$date  = new \DateTime( $start );
		$added = 0;
		while ( $added < $days ) {
			$date->modify( '+1 day' );
			if ( (int) $date->format( 'N' ) < 6 ) { // Mon-Fri
				++$added;
			}
		}
		return $date->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Get client IP (best-effort) for hashing
	 *
	 * @since 3.0.1
	 * @return string IP address or empty
	 */
	private function get_client_ip(): string {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			$ip  = trim( $ips[0] );
		}
		return $ip;
	}
}
