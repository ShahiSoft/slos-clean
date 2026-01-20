<?php
/**
 * DSR Service Class
 *
 * Business logic for Data Subject Request (DSR) handling.
 * Supports all 7 GDPR rights (access, rectification, erasure, portability, restriction, object, automated_decision).
 * Enforces SLA deadlines, rate limiting, and secure workflows.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\DSR_Repository;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Service Class
 *
 * Manages DSR lifecycle and business rules.
 *
 * @since 3.0.1
 */
class DSR_Service extends Base_Service {

	/**
	 * DSR repository
	 *
	 * @since 3.0.1
	 * @var DSR_Repository
	 */
	private $repository;

	/**
	 * Allowed request types (7 GDPR rights)
	 *
	 * @since 3.0.1
	 * @var array
	 */
	private $allowed_types = array(
		'access',              // GDPR Article 15 - Right to access.
		'rectification',       // GDPR Article 16 - Right to rectification.
		'erasure',             // GDPR Article 17 - Right to erasure (right to be forgotten).
		'portability',         // GDPR Article 20 - Right to data portability.
		'restriction',         // GDPR Article 18 - Right to restriction of processing.
		'object',              // GDPR Article 21 - Right to object.
		'automated_decision',  // GDPR Article 22 - Right related to automated decision-making.
	);

	/**
	 * Allowed status values
	 *
	 * @since 3.0.1
	 * @var array
	 */
	private $allowed_statuses = array(
		'pending_verification',
		'verified',
		'in_progress',
		'on_hold',
		'completed',
		'rejected',
	);

	/**
	 * Valid status transitions
	 *
	 * @since 3.0.1
	 * @var array
	 */
	private $status_transitions = array(
		'pending_verification' => array( 'verified', 'rejected' ),
		'verified'             => array( 'in_progress', 'rejected' ),
		'in_progress'          => array( 'on_hold', 'completed', 'rejected' ),
		'on_hold'              => array( 'in_progress', 'rejected' ),
		'completed'            => array(), // Terminal state.
		'rejected'             => array(), // Terminal state.
	);

	/**
	 * Rate limit settings (requests per IP/email per hour)
	 *
	 * @since 3.0.1
	 * @var int
	 */
	private $rate_limit = 5;

	/**
	 * Max detail length
	 *
	 * @since 3.0.1
	 * @var int
	 */
	private $max_detail_length = 5000;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 * @param DSR_Repository $repository DSR repository instance.
	 */
	public function __construct( DSR_Repository $repository = null ) {
		parent::__construct();
		$this->repository = $repository ?? new DSR_Repository();
	}

	/**
	 * Submit a new DSR request
	 *
	 * Validates request type, enforces rate limits, creates request with SLA calculation.
	 *
	 * @since 3.0.1
	 * @param int    $user_id WordPress user ID (0 for anonymous).
	 * @param string $type Request type (access, rectification, etc.).
	 * @param string $email Requester email.
	 * @param string $details Request details/description.
	 * @param array  $meta Optional metadata (regulation, user_agent, source).
	 * @return int|false Request ID or false on failure.
	 */
	public function submit_request( int $user_id, string $type, string $email, string $details = '', array $meta = array() ) {
		$this->clear_errors();

		// Validate email..
		if ( ! $this->validate_email( $email, 'email' ) ) {
			return false;
		}

		// Validate request type..
		if ( ! $this->validate_in_list( $type, $this->allowed_types, 'type' ) ) {
			$this->add_validation_error( 'type', sprintf( 'Invalid request type. Allowed: %s', implode( ', ', $this->allowed_types ) ) );
			return false;
		}

		// Validate detail length..
		if ( $this->max_detail_length < strlen( $details ) ) {
			$this->add_validation_error( 'details', sprintf( 'Details exceed maximum length of %d characters', $this->max_detail_length ) );
			return false;
		}

		// Rate limiting check..
		if ( ! $this->check_rate_limit( $email ) ) {
			$this->add_error( 'rate_limit', 'Too many requests. Please try again later.', array( 'email' => $email ) );
			return false;
		}

		// Prepare request data..
		$regulation = $meta['regulation'] ?? $this->detect_regulation();
		$data       = array(
			'request_type' => sanitize_text_field( $type ),
			'email'        => sanitize_email( $email ),
			'user_id'      => $user_id > 0 ? $user_id : null,
			'regulation'   => sanitize_text_field( $regulation ),
			'details'      => sanitize_textarea_field( $details ),
			'source'       => $meta['source'] ?? 'website',
		);

		// Create request via repository (handles SLA, token, hashing)..
		$request_id = $this->repository->create_request( $data );

		if ( ! $request_id ) {
			$this->add_error( 'create_failed', 'Failed to create DSR request', $this->repository->get_errors() );
			return false;
		}

		// Log rate limit attempt..
		$this->log_rate_limit_attempt( $email );

		// Fire hook for email sending and logging..
		do_action( 'slos_dsr_submitted', $request_id, $data );

		$this->add_message( sprintf( 'DSR request submitted successfully (ID: %d). Verification email sent.', $request_id ) );

		return $request_id;
	}

	/**
	 * Verify email via token
	 *
	 * Changes status from pending_verification to verified.
	 *
	 * @since 3.0.1
	 * @param string $token Verification token from email.
	 * @return bool True on success, false on failure.
	 */
	public function verify_email( string $token ): bool {
		$this->clear_errors();

		if ( empty( $token ) ) {
			$this->add_validation_error( 'token', 'Verification token is required' );
			return false;
		}

		// Find request by token (only pending_verification)..
		$request = $this->repository->find_by_token( $token );

		if ( ! $request ) {
			$this->add_error( 'invalid_token', 'Invalid or expired verification token', array( 'token' => substr( $token, 0, 10 ) . '...' ) );
			return false;
		}

		// Update status to verified..
		$result = $this->repository->update_status(
			$request->id,
			'verified',
			array(
				'verified_at' => current_time( 'mysql' ),
			)
		);

		if ( ! $result ) {
			$this->add_error( 'verification_failed', 'Failed to verify email', array( 'request_id' => $request->id ) );
			return false;
		}

		// Fire hook for notifications..
		do_action( 'slos_dsr_status_changed', $request->id, 'pending_verification', 'verified' );

		$this->add_message( 'Email verified successfully. Your request is now being processed.' );

		return true;
	}

	/**
	 * Assign request to admin user
	 *
	 * @since 3.0.1
	 * @param int $request_id Request ID.
	 * @param int $assignee User ID to assign to.
	 * @return bool True on success.
	 */
	public function assign_request( int $request_id, int $assignee ): bool {
		$this->clear_errors();

		// Validate request exists..
		$request = $this->repository->find( $request_id );
		if ( ! $request ) {
			$this->add_error( 'not_found', 'Request not found', array( 'request_id' => $request_id ) );
			return false;
		}

		// Validate assignee exists and has capability..
		if ( ! user_can( $assignee, 'manage_options' ) ) {
			$this->add_error( 'invalid_assignee', 'Assignee must have manage_options capability', array( 'assignee' => $assignee ) );
			return false;
		}

		// Update processed_by field..
		$result = $this->repository->update(
			$request_id,
			array(
				'processed_by' => $assignee,
				'updated_at'   => current_time( 'mysql' ),
			)
		);

		if ( ! $result ) {
			$this->add_error( 'assign_failed', 'Failed to assign request', array( 'request_id' => $request_id ) );
			return false;
		}

		$this->add_message( sprintf( 'Request #%d assigned to user #%d', $request_id, $assignee ) );

		return true;
	}

	/**
	 * Add admin note to request
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID.
	 * @param string $note Note content.
	 * @param int    $author User ID of note author.
	 * @return bool True on success.
	 */
	public function add_note( int $request_id, string $note, int $author ): bool {
		$this->clear_errors();

		// Validate request exists..
		$request = $this->repository->find( $request_id );
		if ( ! $request ) {
			$this->add_error( 'not_found', 'Request not found', array( 'request_id' => $request_id ) );
			return false;
		}

		// Validate note content..
		if ( empty( trim( $note ) ) ) {
			$this->add_validation_error( 'note', 'Note content cannot be empty' );
			return false;
		}

		// Prepare note with timestamp and author..
		$existing_notes = ! empty( $request->admin_notes ) ? $request->admin_notes . "\n\n" : '';
		$timestamp      = current_time( 'mysql' );
		$author_name    = get_userdata( $author )->display_name ?? "User #{$author}";
		$new_note       = sprintf( '[%s] %s: %s', $timestamp, $author_name, sanitize_textarea_field( $note ) );

		// Update admin_notes field..
		$result = $this->repository->update(
			$request_id,
			array(
				'admin_notes' => $existing_notes . $new_note,
				'updated_at'  => $timestamp,
			)
		);

		if ( ! $result ) {
			$this->add_error( 'note_failed', 'Failed to add note', array( 'request_id' => $request_id ) );
			return false;
		}

		$this->add_message( 'Note added successfully' );

		return true;
	}

	/**
	 * Transition request to new status
	 *
	 * Validates status transition rules and SLA compliance.
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID.
	 * @param string $new_status Target status.
	 * @param array  $metadata Optional metadata (admin_notes, processed_by).
	 * @return bool True on success.
	 */
	public function transition( int $request_id, string $new_status, array $metadata = array() ): bool {
		$this->clear_errors();

		// Validate request exists..
		$request = $this->repository->find( $request_id );
		if ( ! $request ) {
			$this->add_error( 'not_found', 'Request not found', array( 'request_id' => $request_id ) );
			return false;
		}

		// Validate new status..
		if ( ! $this->validate_in_list( $new_status, $this->allowed_statuses, 'status' ) ) {
			$this->add_validation_error( 'status', sprintf( 'Invalid status. Allowed: %s', implode( ', ', $this->allowed_statuses ) ) );
			return false;
		}

		// Validate transition is allowed..
		if ( ! $this->is_valid_transition( $request->status, $new_status ) ) {
			$this->add_error(
				'invalid_transition',
				sprintf( 'Invalid status transition from "%s" to "%s"', $request->status, $new_status ),
				array(
					'current' => $request->status,
					'target'  => $new_status,
				)
			);
			return false;
		}

		// Check SLA compliance (warn if overdue)..
		if ( 'completed' === $new_status && ! empty( $request->sla_deadline ) ) {
			$deadline = new \DateTime( $request->sla_deadline );
			$now      = new \DateTime();
			if ( $now > $deadline ) {
				$this->add_message( 'Warning: Request completed after SLA deadline' );
			}
		}

		// Store old status for hook..
		$old_status = $request->status;

		// Update status via repository..
		$result = $this->repository->update_status( $request_id, $new_status, $metadata );

		if ( ! $result ) {
			$this->add_error( 'transition_failed', 'Failed to update status', array( 'request_id' => $request_id ) );
			return false;
		}

		// Fire status change hook..
		do_action( 'slos_dsr_status_changed', $request_id, $old_status, $new_status );

		// Fire completion hook if completed..
		if ( 'completed' === $new_status ) {
			do_action( 'slos_dsr_completed', $request_id, $request );
		}

		$this->add_message( sprintf( 'Status changed from "%s" to "%s"', $old_status, $new_status ) );

		return true;
	}

	/**
	 * Generate export package for data portability requests
	 *
	 * Creates secure tokenized download URL with time limit.
	 *
	 * @since 3.0.1
	 * @param int $request_id Request ID.
	 * @return string|false Export token or false on failure.
	 */
	public function generate_export_package( int $request_id ) {
		$this->clear_errors();

		// Validate request exists and is correct type..
		$request = $this->repository->find( $request_id );
		if ( ! $request ) {
			$this->add_error( 'not_found', 'Request not found', array( 'request_id' => $request_id ) );
			return false;
		}

		// Verify request is verified or in_progress..
		if ( ! in_array( $request->status, array( 'verified', 'in_progress', 'completed' ), true ) ) {
			$this->add_error( 'invalid_status', 'Request must be verified before export', array( 'status' => $request->status ) );
			return false;
		}

		// Generate secure export token (valid for 7 days)..
		$export_token = wp_generate_password( 32, false );
		$expires_at   = gmdate( 'Y-m-d H:i:s', strtotime( '+7 days' ) );

		// Store export metadata..
		$export_meta = array(
			'export_token'   => $export_token,
			'export_expires' => $expires_at,
			'export_created' => current_time( 'mysql' ),
		);

		// Update request with export data..
		$result = $this->repository->update(
			$request_id,
			array(
				'export_file_path' => 'pending', // Will be updated by export generation task.
				'updated_at'       => current_time( 'mysql' ),
			)
		);

		if ( ! $result ) {
			$this->add_error( 'export_failed', 'Failed to initialize export', array( 'request_id' => $request_id ) );
			return false;
		}

		// Store export token in transient (7-day expiry)..
		set_transient( 'slos_dsr_export_' . $request_id, $export_meta, 7 * DAY_IN_SECONDS );

		// Fire hook for export generation (async task)..
		do_action( 'slos_dsr_export_ready', $request_id, $export_token, $request );

		$this->add_message( 'Export package generation initiated. Download link will be sent via email.' );

		return $export_token;
	}

	/**
	 * Execute erasure request
	 *
	 * Marks request for erasure and fires anonymization callbacks.
	 *
	 * @since 3.0.1
	 * @param int $request_id Request ID.
	 * @return bool True on success.
	 */
	public function execute_erasure( int $request_id ): bool {
		$this->clear_errors();

		// Validate request exists and is erasure type..
		$request = $this->repository->find( $request_id );
		if ( ! $request ) {
			$this->add_error( 'not_found', 'Request not found', array( 'request_id' => $request_id ) );
			return false;
		}

		if ( 'erasure' !== $request->request_type ) {
			$this->add_error( 'invalid_type', 'Request must be erasure type', array( 'type' => $request->request_type ) );
			return false;
		}

		// Verify request is verified..
		if ( ! in_array( $request->status, array( 'verified', 'in_progress' ), true ) ) {
			$this->add_error( 'invalid_status', 'Request must be verified before erasure', array( 'status' => $request->status ) );
			return false;
		}

		// Fire erasure hook (will trigger anonymization callbacks)..
		do_action( 'slos_dsr_erasure_execute', $request_id, $request );

		// Update status to in_progress (will be completed after erasure)..
		$this->transition(
			$request_id,
			'in_progress',
			array(
				'admin_notes' => sprintf( '[%s] Erasure initiated', current_time( 'mysql' ) ),
			)
		);

		$this->add_message( 'Erasure process initiated. Data anonymization in progress.' );

		return true;
	}

	/**
	 * Calculate due date based on SLA
	 *
	 * Uses business days (excludes weekends).
	 *
	 * @since 3.0.1
	 * @param string $created_at Start datetime (MySQL format).
	 * @param int    $sla_days Number of business days.
	 * @return string Due date (MySQL format).
	 */
	public function calculate_due_date( string $created_at, int $sla_days ): string {
		$date  = new \DateTime( $created_at );
		$added = 0;

		while ( $added < $sla_days ) {
			$date->modify( '+1 day' );
			// Skip weekends (Saturday=6, Sunday=7)..
			if ( (int) $date->format( 'N' ) < 6 ) {
				++$added;
			}
		}

		return $date->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Get request timeline (audit log)
	 *
	 * Aggregates all status changes, notes, and actions.
	 *
	 * @since 3.0.1
	 * @param int $request_id Request ID.
	 * @return array Timeline entries.
	 */
	public function get_timeline( int $request_id ): array {
		$request = $this->repository->find( $request_id );
		if ( ! $request ) {
			return array();
		}

		$timeline = array();

		// Submitted event..
		$timeline[] = array(
			'event'     => 'submitted',
			'timestamp' => $request->submitted_at,
			'actor'     => $request->user_id ? "User #{$request->user_id}" : 'Anonymous',
			'details'   => sprintf( 'Request type: %s, Regulation: %s', $request->request_type, $request->regulation ),
		);

		// Verified event..
		if ( ! empty( $request->verified_at ) ) {
			$timeline[] = array(
				'event'     => 'verified',
				'timestamp' => $request->verified_at,
				'actor'     => 'System',
				'details'   => 'Email verified successfully',
			);
		}

		// Status changes (parse from admin_notes if available)..
		if ( ! empty( $request->admin_notes ) ) {
			$notes = explode( "\n\n", $request->admin_notes );
			foreach ( $notes as $note ) {
				if ( preg_match( '/\[([^\]]+)\]\s*(.+):\s*(.+)/', $note, $matches ) ) {
					$timeline[] = array(
						'event'     => 'note',
						'timestamp' => $matches[1],
						'actor'     => $matches[2],
						'details'   => $matches[3],
					);
				}
			}
		}

		// Completed event..
		if ( ! empty( $request->completed_at ) ) {
			$timeline[] = array(
				'event'     => 'completed' === $request->status ? 'completed' : 'rejected',
				'timestamp' => $request->completed_at,
				'actor'     => $request->processed_by ? "User #{$request->processed_by}" : 'System',
				'details'   => sprintf( 'Final status: %s', $request->status ),
			);
		}

		// Sort by timestamp descending..
		usort(
			$timeline,
			function ( $a, $b ) {
				return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
			}
		);

		return $timeline;
	}

	/**
	 * Get DSR settings
	 *
	 * @since 3.0.1
	 * @return array Settings
	 */
	private function get_settings(): array {
		$defaults = array(
			'sla_gdpr'    => 30,
			'sla_uk-gdpr' => 30,
			'sla_ccpa'    => 45,
			'sla_lgpd'    => 15,
			'sla_pipeda'  => 30,
			'sla_popia'   => 30,
			'rate_limit'  => 5,
		);

		$settings = get_option( 'slos_dsr_settings', array() );

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Detect regulation based on user location
	 *
	 * @since 3.0.1
	 * @return string Regulation code
	 */
	private function detect_regulation(): string {
		// Default to GDPR (can be enhanced with geo-location service)..
		return apply_filters( 'slos_dsr_detect_regulation', 'GDPR' );
	}

	/**
	 * Check rate limit for email/IP
	 *
	 * @since 3.0.1
	 * @param string $email Email address.
	 * @return bool True if under limit.
	 */
	private function check_rate_limit( string $email ): bool {
		$email_hash    = hash( 'sha256', $email );
		$transient_key = 'slos_dsr_rate_' . $email_hash;
		$attempts      = get_transient( $transient_key );

		if ( false === $attempts ) {
			return true; // No attempts recorded.
		}

		return (int) $attempts < $this->rate_limit;
	}

	/**
	 * Log rate limit attempt
	 *
	 * @since 3.0.1
	 * @param string $email Email address.
	 * @return void
	 */
	private function log_rate_limit_attempt( string $email ): void {
		$email_hash    = hash( 'sha256', $email );
		$transient_key = 'slos_dsr_rate_' . $email_hash;
		$attempts      = get_transient( $transient_key );

		if ( false === $attempts ) {
			$attempts = 0;
		}

		++$attempts;
		set_transient( $transient_key, $attempts, HOUR_IN_SECONDS );
	}

	/**
	 * Validate status transition
	 *
	 * @since 3.0.1
	 * @param string $current_status Current status.
	 * @param string $new_status Target status.
	 * @return bool True if valid transition.
	 */
	private function is_valid_transition( string $current_status, string $new_status ): bool {
		// Same status is always valid..
		if ( $current_status === $new_status ) {
			return true;
		}

		// Check allowed transitions..
		if ( ! isset( $this->status_transitions[ $current_status ] ) ) {
			return false;
		}

		return in_array( $new_status, $this->status_transitions[ $current_status ], true );
	}

	/**
	 * Validate email
	 *
	 * @since 3.0.1
	 * @param string $email Email address.
	 * @param string $field Field name for error reporting.
	 * @return bool True if valid.
	 */
	protected function validate_email( string $email, string $field = 'email' ): bool {
		if ( empty( $email ) ) {
			$this->add_validation_error( $field, 'Email is required' );
			return false;
		}

		if ( ! is_email( $email ) ) {
			$this->add_validation_error( $field, 'Invalid email address' );
			return false;
		}

		return true;
	}

	/**
	 * Validate value in allowed list
	 *
	 * @since 3.0.1
	 * @param mixed  $value Value to check.
	 * @param array  $allowed Allowed values.
	 * @param string $field_name Field name for error reporting.
	 * @return bool True if valid.
	 */
	protected function validate_in_list( $value, array $allowed, string $field_name ): bool {
		if ( ! in_array( $value, $allowed, true ) ) {
			$this->add_validation_error( $field_name, sprintf( 'Invalid %s. Allowed: %s', $field_name, implode( ', ', $allowed ) ) );
			return false;
		}

		return true;
	}

	/**
	 * Get DSR statistics for Ops Dashboard
	 *
	 * Returns summary stats: open requests, SLA compliance, queue breakdown.
	 *
	 * @since 3.1.1 (Phase 2.1)
	 * @return array DSR statistics
	 */
	public function get_ops_statistics(): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		// Open requests (non-terminal statuses)..
		$open_statuses = array( 'pending_verification', 'verified', 'in_progress', 'on_hold' );

		// Build placeholders for dynamic IN() list and prepare safely.
		$placeholders = implode( ', ', array_fill( 0, count( $open_statuses ), '%s' ) );
		$query        = 'SELECT COUNT(*) FROM `' . esc_sql( $table ) . '` WHERE status IN (' . $placeholders . ')';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Placeholders and values are built dynamically and prepared below.
		$prepare_args = array_merge( array( $query ), $open_statuses );
		$prepared     = call_user_func_array( array( $wpdb, 'prepare' ), $prepare_args );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Table name is safe and values were prepared above.
		$open_count = $wpdb->get_var( $prepared );

		// Total requests..
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safe, from repository; real-time statistics do not require caching.
		$total_count = $wpdb->get_var( 'SELECT COUNT(*) FROM `' . esc_sql( $table ) . '`' );

		// Completed requests..
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safe, from repository; real-time statistics do not require caching.
		$completed_count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM `' . esc_sql( $table ) . '` WHERE status = %s',
				'completed'
			)
		);
		// Note: Table uses completed_date and due_date, not completed_at and sla_deadline..
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safe, from repository; real-time statistics do not require caching.
		$sla_compliant = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM `' . esc_sql( $table ) . '` WHERE status = %s AND completed_date IS NOT NULL AND completed_date <= due_date',
				'completed'
			)
		);

		$sla_compliance_rate = $completed_count > 0
			? round( ( $sla_compliant / $completed_count ) * 100, 1 )
			: 100;

		// Queue breakdown by status..
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safe, from repository; real-time statistics do not require caching.
		$queue_breakdown = $wpdb->get_results(
			sprintf( 'SELECT status, COUNT(*) as count FROM `%s` GROUP BY status', esc_sql( $table ) ),
			ARRAY_A
		);

		$by_status = array();
		foreach ( $queue_breakdown as $row ) {
			$by_status[ $row['status'] ] = (int) $row['count'];
		}

		// Requests by type (last 30 days)..
		// Note: Table uses request_date, not submitted_at..
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safe, from repository; real-time statistics do not require caching.
		$by_type = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT request_type, COUNT(*) as count FROM `' . esc_sql( $table ) . '` WHERE request_date >= %s GROUP BY request_type',
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			),
			ARRAY_A
		);

		$type_breakdown = array();
		foreach ( $by_type as $row ) {
			$type_breakdown[ $row['request_type'] ] = (int) $row['count'];
		}

		// Overdue requests (past SLA deadline, not completed/rejected)..
		// Note: Table uses due_date, not sla_deadline..
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Table name is safe, from repository; real-time statistics do not require caching.
		$overdue = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM `' . esc_sql( $table ) . '` WHERE status NOT IN (%s, %s) AND due_date < %s',
				'completed',
				'rejected',
				current_time( 'mysql' )
			)
		);

		return array(
			'open_requests'       => (int) $open_count,
			'total_requests'      => (int) $total_count,
			'completed_requests'  => (int) $completed_count,
			'overdue_requests'    => (int) $overdue,
			'sla_compliance_rate' => (float) $sla_compliance_rate,
			'by_status'           => $by_status,
			'by_type'             => $type_breakdown,
		);
	}
}
