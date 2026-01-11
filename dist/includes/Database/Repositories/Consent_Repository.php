<?php
/**
 * Consent Repository
 *
 * Handles database operations for consent records (GDPR/CCPA/LGPD compliance).
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
 * Consent_Repository Class
 *
 * Concrete repository implementation for consent table operations.
 *
 * @since 3.0.1
 */
class Consent_Repository extends Base_Repository {

	/**
	 * Get table name
	 *
	 * @since 3.0.1
	 * @return string Table name without prefix
	 */
	protected function get_table_name(): string {
		return 'slos_consent';
	}

	/**
	 * Find consents by user ID
	 *
	 * @since 3.0.1
	 * @param int   $user_id User ID
	 * @param array $args    Query arguments
	 * @return array Array of consent records
	 */
	public function find_by_user( int $user_id, array $args = array() ): array {
		return $this->find_by( 'user_id', $user_id, $args );
	}

	/**
	 * Find consents by type
	 *
	 * @since 3.0.1
	 * @param string $type Type (necessary, functional, analytics, marketing, personalization)
	 * @param array  $args Query arguments
	 * @return array Array of consent records
	 */
	public function find_by_type( string $type, array $args = array() ): array {
		return $this->find_by( 'type', $type, $args );
	}

	/**
	 * Find consents by status
	 *
	 * @since 3.0.1
	 * @param string $status Status (pending, accepted, rejected, withdrawn)
	 * @param array  $args   Query arguments
	 * @return array Array of consent records
	 */
	public function find_by_status( string $status, array $args = array() ): array {
		return $this->find_by( 'status', $status, $args );
	}

	/**
	 * Get active consents for user
	 *
	 * @since 3.0.1
	 * @param int $user_id User ID
	 * @return array Array of accepted consent records
	 */
	public function get_active_consents( int $user_id ): array {
		return $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE user_id = %d AND status = 'accepted' ORDER BY created_at DESC",
				$user_id
			)
		);
	}

	/**
	 * Check if user has active consent for specific type
	 *
	 * @since 3.0.1
	 * @param int    $user_id User ID
	 * @param string $type    Consent type
	 * @return bool True if has active consent
	 */
	public function has_consent( int $user_id, string $type ): bool {
		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d AND type = %s AND status = 'accepted'",
				$user_id,
				$type
			)
		);

		return $count > 0;
	}

	/**
	 * Withdraw consent
	 *
	 * @since 3.0.1
	 * @param int $consent_id Consent ID
	 * @return bool True on success
	 */
	public function withdraw( int $consent_id ): bool {
		return $this->update(
			$consent_id,
			array(
				'status' => 'withdrawn',
			)
		);
	}

	/**
	 * Get consent statistics by type
	 *
	 * @since 3.0.1
	 * @return array Array of statistics (type => count)
	 */
	public function get_stats_by_type(): array {
		$results = $this->wpdb->get_results(
			"SELECT type, COUNT(*) as count FROM {$this->table} WHERE status = 'accepted' GROUP BY type"
		);

		$stats = array();
		foreach ( $results as $row ) {
			$stats[ $row->type ] = (int) $row->count;
		}

		return $stats;
	}

	/**
	 * Get consent statistics by status
	 *
	 * @since 3.0.1
	 * @return array Array of statistics (status => count)
	 */
	public function get_stats_by_status(): array {
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
	 * Find consents by IP hash
	 *
	 * @since 3.0.1
	 * @param string $ip_hash SHA256 hash of IP address
	 * @param array  $args    Query arguments
	 * @return array Array of consent records
	 */
	public function find_by_ip_hash( string $ip_hash, array $args = array() ): array {
		return $this->find_by( 'ip_hash', $ip_hash, $args );
	}

	/**
	 * Get recent consents
	 *
	 * @since 3.0.1
	 * @param int $limit Number of records to retrieve
	 * @return array Array of recent consent records
	 */
	public function get_recent( int $limit = 10 ): array {
		return $this->find_all(
			array(
				'limit'    => $limit,
				'order_by' => 'created_at',
				'order'    => 'DESC',
			)
		);
	}

	/**
	 * Count consents by user
	 *
	 * @since 3.0.1
	 * @param int $user_id User ID
	 * @return int Total consent count for user
	 */
	public function count_by_user( int $user_id ): int {
		return $this->count( array( 'user_id' => $user_id ) );
	}

	/**
	 * Find consents for export with filters
	 *
	 * @since 3.0.1
	 * @param array $args {
	 *     Export filters
	 *
	 *     @type int    $user_id   Filter by user ID
	 *     @type string $type      Filter by consent type
	 *     @type string $status    Filter by status
	 *     @type string $date_from Start date (Y-m-d format)
	 *     @type string $date_to   End date (Y-m-d format)
	 *     @type int    $limit     Maximum records to export (default: 10000)
	 * }
	 * @return array Array of consent records for export
	 */
	public function find_export( array $args = array() ): array {
		$defaults = array(
			'user_id'   => null,
			'type'      => null,
			'status'    => null,
			'date_from' => null,
			'date_to'   => null,
			'limit'     => 10000,
		);

		$args = wp_parse_args( $args, $defaults );

		$where_clauses = array( '1=1' );
		$where_values  = array();

		// User ID filter
		if ( ! empty( $args['user_id'] ) ) {
			$where_clauses[] = 'user_id = %d';
			$where_values[]  = (int) $args['user_id'];
		}

		// Type filter
		if ( ! empty( $args['type'] ) ) {
			$where_clauses[] = 'type = %s';
			$where_values[]  = sanitize_text_field( $args['type'] );
		}

		// Status filter
		if ( ! empty( $args['status'] ) ) {
			$where_clauses[] = 'status = %s';
			$where_values[]  = sanitize_text_field( $args['status'] );
		}

		// Date from filter
		if ( ! empty( $args['date_from'] ) ) {
			$where_clauses[] = 'created_at >= %s';
			$where_values[]  = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}

		// Date to filter
		if ( ! empty( $args['date_to'] ) ) {
			$where_clauses[] = 'created_at <= %s';
			$where_values[]  = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}

		$where = implode( ' AND ', $where_clauses );
		$limit = absint( $args['limit'] );

		$sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT {$limit}";

		if ( ! empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $this->wpdb->prepare( $sql, $where_values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $this->wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Find consents by email
	 *
	 * Searches for consent records associated with an email address.
	 * Looks up user by email and returns all consents for that user_id,
	 * as well as guest consents where email is stored in metadata.
	 *
	 * @since 3.1.1 Phase 2.2
	 * @param string $email Email address to search for
	 * @param array  $args  Optional query arguments
	 * @return array Array of consent records
	 */
	public function find_by_email( string $email, array $args = array() ): array {
		global $wpdb;

		// Sanitize email
		$email = sanitize_email( $email );
		if ( empty( $email ) ) {
			return array();
		}

		$consents = array();

		// 1. Find consents via user_id (registered users)
		$user = get_user_by( 'email', $email );
		if ( $user ) {
			$consents = array_merge( $consents, $this->find_by_user( $user->ID, $args ) );
		}

		// 2. Find consents via metadata (guest consents with email stored)
		// Look for email in JSON metadata field
		$metadata_results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} 
				WHERE metadata LIKE %s 
				ORDER BY created_at DESC",
				'%' . $wpdb->esc_like( $email ) . '%'
			),
			ARRAY_A
		);

		// Verify email is actually in metadata (not just a substring match)
		foreach ( $metadata_results as $record ) {
			$metadata = isset( $record['metadata'] ) ? json_decode( $record['metadata'], true ) : array();
			if ( is_array( $metadata ) && isset( $metadata['email'] ) && $metadata['email'] === $email ) {
				// Check if this consent isn't already in our results (avoid duplicates)
				$already_included = false;
				foreach ( $consents as $existing ) {
					if ( isset( $existing['id'] ) && $existing['id'] == $record['id'] ) {
						$already_included = true;
						break;
					}
				}
				if ( ! $already_included ) {
					$consents[] = $record;
				}
			}
		}

		// Sort by created_at DESC
		usort(
			$consents,
			function ( $a, $b ) {
				$a_time = strtotime( $a['created_at'] ?? '' );
				$b_time = strtotime( $b['created_at'] ?? '' );
				return $b_time - $a_time;
			}
		);

		return $consents;
	}
}
