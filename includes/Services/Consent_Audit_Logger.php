<?php
/**
 * Consent Audit Logger Service
 *
 * Handles logging of all consent-related actions for audit trail compliance.
 * Logs every consent grant, withdrawal, import, export, and modification.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Audit_Logger Class
 *
 * Provides comprehensive audit trail logging for consent management.
 *
 * @since 3.0.1
 */
class Consent_Audit_Logger extends Base_Service {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	/**
	 * Log table name
	 *
	 * @since 3.0.1
	 * @var string
	 */
	private $table_name;

	/**
	 * Allowed consent logging methods
	 *
	 * @since 3.1.1
	 * @var array
	 */
	private $allowed_methods = array(
		'banner',              // Consent via banner interaction.
		'preferences_center',  // Consent via preferences center.
		'admin_manual',        // Manual consent entry by admin.
		'api',                 // Programmatic API consent.
		'import',              // Bulk imported consent.
		'website',             // Legacy website method (backward compat).
		'admin',               // Legacy admin method (backward compat).
	);

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		parent::__construct();
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'slos_consent_logs';
	}

	/**
	 * Log a consent action
	 *
	 * @since 3.0.1
	 * @since 3.1.1 Extended method taxonomy with banner, preferences_center, admin_manual, api, import
	 *
	 * @param array $data {
	 *     Log data.
	 *
	 *     @type int    $consent_id     Consent record ID
	 *     @type int    $user_id        User ID
	 *     @type string $purpose        Consent purpose/type
	 *     @type string $action         Action performed (grant|withdraw|update|import|export)
	 *     @type array  $previous_state Previous state before action
	 *     @type array  $new_state      New state after action
	 *     @type string $method         Method used (banner|preferences_center|admin_manual|api|import|website|admin)
	 *     @type string $ip_address     IP address of user
	 *     @type string $user_agent     User agent string
	 * }
	 * @return int|false Log ID on success, false on failure
	 */
	public function log( array $data ) {
		global $wpdb;

		$defaults = array(
			'consent_id'     => 0,
			'user_id'        => 0,
			'purpose'        => '',
			'action'         => '',
			'previous_state' => null,
			'new_state'      => null,
			'method'         => 'website',
			'ip_address'     => '',
			'user_agent'     => '',
		);

		$data = wp_parse_args( $data, $defaults );

		// Validate required fields..
		if ( empty( $data['action'] ) ) {
			return false;
		}

		// Validate method taxonomy..
		if ( ! empty( $data['method'] ) && ! in_array( $data['method'], $this->allowed_methods, true ) ) {
			// Log warning but continue with fallback..
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					sprintf(
						'Invalid consent logging method "%s" provided. Allowed: %s',
						$data['method'],
						implode( ', ', $this->allowed_methods )
					)
				);
			}
			// Fallback to website for unknown methods..
			$data['method'] = 'website';
		}

		// Prepare data for insertion..
		$insert_data = array(
			'consent_id'     => absint( $data['consent_id'] ),
			'user_id'        => absint( $data['user_id'] ),
			'purpose'        => sanitize_text_field( $data['purpose'] ),
			'action'         => sanitize_text_field( $data['action'] ),
			'previous_state' => isset( $data['previous_state'] ) ? wp_json_encode( $data['previous_state'] ) : null,
			'new_state'      => isset( $data['new_state'] ) ? wp_json_encode( $data['new_state'] ) : null,
			'method'         => sanitize_text_field( $data['method'] ),
			'ip_address'     => sanitize_text_field( $data['ip_address'] ),
			'user_agent'     => sanitize_text_field( $data['user_agent'] ),
			'created_at'     => current_time( 'mysql' ),
		);

		// Insert log..
		$result = $wpdb->insert(
			$this->table_name,
			$insert_data,
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			return false;
		}

		$log_id = $wpdb->insert_id;

		// Fire action for extensibility..
		do_action( 'slos_consent_logged', $log_id, $data );

		return $log_id;
	}

	/**
	 * Search/filter audit logs
	 *
	 * @since 3.0.1
	 * @param array $args {
	 *     Search arguments.
	 *
	 *     @type int    $user_id   Filter by user ID
	 *     @type string $purpose   Filter by purpose
	 *     @type string $action    Filter by action
	 *     @type string $date_from Start date (Y-m-d format)
	 *     @type string $date_to   End date (Y-m-d format)
	 *     @type int    $limit     Maximum results (default: 100)
	 *     @type int    $offset    Results offset (default: 0)
	 * }
	 * @return array Array of log records
	 */
	public function search( array $args = array() ) {
		global $wpdb;

		$defaults = array(
			'user_id'   => null,
			'purpose'   => null,
			'action'    => null,
			'date_from' => null,
			'date_to'   => null,
			'limit'     => 100,
			'offset'    => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$where_clauses = array( '1=1' );
		$where_values  = array();

		// User ID filter..
		if ( ! empty( $args['user_id'] ) ) {
			$where_clauses[] = 'user_id = %d';
			$where_values[]  = absint( $args['user_id'] );
		}

		// Purpose filter..
		if ( ! empty( $args['purpose'] ) ) {
			$where_clauses[] = 'purpose = %s';
			$where_values[]  = sanitize_text_field( $args['purpose'] );
		}

		// Action filter..
		if ( ! empty( $args['action'] ) ) {
			$where_clauses[] = 'action = %s';
			$where_values[]  = sanitize_text_field( $args['action'] );
		}

		// Date from filter..
		if ( ! empty( $args['date_from'] ) ) {
			$where_clauses[] = 'created_at >= %s';
			$where_values[]  = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}

		// Date to filter..
		if ( ! empty( $args['date_to'] ) ) {
			$where_clauses[] = 'created_at <= %s';
			$where_values[]  = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}

		$where = implode( ' AND ', $where_clauses );

		// Add limit and offset to values..
		$where_values[] = absint( $args['limit'] );
		$where_values[] = absint( $args['offset'] );

		// Build query..
		$sql = "SELECT * FROM {$this->table_name} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";

		if ( ! empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $where_values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $sql, ARRAY_A );

		// Decode JSON fields..
		foreach ( $results as &$result ) {
			if ( ! empty( $result['previous_state'] ) ) {
				$result['previous_state'] = json_decode( $result['previous_state'], true );
			}
			if ( ! empty( $result['new_state'] ) ) {
				$result['new_state'] = json_decode( $result['new_state'], true );
			}
		}

		return $results;
	}

	/**
	 * Get total count of logs matching filters
	 *
	 * @since 3.0.1
	 * @param array $args Search arguments (same as search method).
	 * @return int Total count.
	 */
	public function count( array $args = array() ): int {
		global $wpdb;

		$defaults = array(
			'user_id'   => null,
			'purpose'   => null,
			'action'    => null,
			'date_from' => null,
			'date_to'   => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$where_clauses = array( '1=1' );
		$where_values  = array();

		// User ID filter..
		if ( ! empty( $args['user_id'] ) ) {
			$where_clauses[] = 'user_id = %d';
			$where_values[]  = absint( $args['user_id'] );
		}

		// Purpose filter..
		if ( ! empty( $args['purpose'] ) ) {
			$where_clauses[] = 'purpose = %s';
			$where_values[]  = sanitize_text_field( $args['purpose'] );
		}

		// Action filter..
		if ( ! empty( $args['action'] ) ) {
			$where_clauses[] = 'action = %s';
			$where_values[]  = sanitize_text_field( $args['action'] );
		}

		// Date from filter..
		if ( ! empty( $args['date_from'] ) ) {
			$where_clauses[] = 'created_at >= %s';
			$where_values[]  = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
		}

		// Date to filter..
		if ( ! empty( $args['date_to'] ) ) {
			$where_clauses[] = 'created_at <= %s';
			$where_values[]  = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
		}

		$where = implode( ' AND ', $where_clauses );

		$sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where}";

		if ( ! empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $where_values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Get log by ID
	 *
	 * @since 3.0.1
	 * @param int $log_id Log ID.
	 * @return array|null Log data or null if not found.
	 */
	public function get_log( int $log_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE id = %d",
			$log_id
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->get_row( $sql, ARRAY_A );

		if ( ! $result ) {
			return null;
		}

		// Decode JSON fields..
		if ( ! empty( $result['previous_state'] ) ) {
			$result['previous_state'] = json_decode( $result['previous_state'], true );
		}
		if ( ! empty( $result['new_state'] ) ) {
			$result['new_state'] = json_decode( $result['new_state'], true );
		}

		return $result;
	}

	/**
	 * Purge old logs based on retention policy
	 *
	 * @since 3.0.1
	 * @param int $days Number of days to retain (default: from settings).
	 * @return int|false Number of deleted rows or false on error.
	 */
	public function purge_old_logs( int $days = null ) {
		global $wpdb;

		if ( null === $days ) {
			$days = (int) get_option( 'consent_log_retention_days', 365 );
		}

		// Safety check: minimum 30 days retention..
		if ( $days < 30 ) {
			$days = 30;
		}

		$sql = $wpdb->prepare(
			"DELETE FROM {$this->table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
			$days
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( $sql );

		if ( false !== $result && $result > 0 ) {
			// Fire action for purge completion..
			do_action( 'slos_consent_logs_purged', $result, $days );
		}

		return $result;
	}

	/**
	 * Get logs for a specific consent ID
	 *
	 * @since 3.0.1
	 * @param int $consent_id Consent ID.
	 * @return array Array of log records.
	 */
	public function get_consent_history( int $consent_id ): array {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->table_name} WHERE consent_id = %d ORDER BY created_at DESC",
			$consent_id
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $sql, ARRAY_A );

		// Decode JSON fields..
		foreach ( $results as &$result ) {
			if ( ! empty( $result['previous_state'] ) ) {
				$result['previous_state'] = json_decode( $result['previous_state'], true );
			}
			if ( ! empty( $result['new_state'] ) ) {
				$result['new_state'] = json_decode( $result['new_state'], true );
			}
		}

		return $results;
	}

	/**
	 * Get logs for a specific user
	 *
	 * @since 3.0.1
	 * @param int $user_id User ID.
	 * @param int $limit   Maximum results (default: 50).
	 * @return array Array of log records.
	 */
	public function get_user_logs( int $user_id, int $limit = 50 ): array {
		return $this->search(
			array(
				'user_id' => $user_id,
				'limit'   => $limit,
			)
		);
	}

	/**
	 * Get recent logs
	 *
	 * @since 3.0.1
	 * @param int $limit Maximum results (default: 20).
	 * @return array Array of log records.
	 */
	public function get_recent( int $limit = 20 ): array {
		return $this->search(
			array(
				'limit' => $limit,
			)
		);
	}

	/**
	 * Get statistics by action type
	 *
	 * @since 3.0.1
	 * @param string $date_from Optional start date (Y-m-d format).
	 * @param string $date_to   Optional end date (Y-m-d format).
	 * @return array Array of action => count pairs.
	 */
	public function get_stats_by_action( string $date_from = null, string $date_to = null ): array {
		global $wpdb;

		$where_clauses = array( '1=1' );
		$where_values  = array();

		if ( ! empty( $date_from ) ) {
			$where_clauses[] = 'created_at >= %s';
			$where_values[]  = $date_from . ' 00:00:00';
		}

		if ( ! empty( $date_to ) ) {
			$where_clauses[] = 'created_at <= %s';
			$where_values[]  = $date_to . ' 23:59:59';
		}

		$where = implode( ' AND ', $where_clauses );

		$sql = "SELECT action, COUNT(*) as count FROM {$this->table_name} WHERE {$where} GROUP BY action";

		if ( ! empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $where_values );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results = $wpdb->get_results( $sql, ARRAY_A );

		$stats = array();
		foreach ( $results as $row ) {
			$stats[ $row['action'] ] = (int) $row['count'];
		}

		return $stats;
	}

	/**
	 * Get allowed consent logging methods
	 *
	 * Returns list of valid method values for consent logging.
	 *
	 * @since 3.1.1
	 * @return array Array of allowed method strings
	 */
	public function get_allowed_methods(): array {
		/**
		 * Filter allowed consent logging methods
		 *
		 * @since 3.1.1
		 * @param array $methods Default allowed methods.
		 * @return array Filtered methods.
		 */
		return apply_filters( 'slos_consent_logging_methods', $this->allowed_methods );
	}
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
