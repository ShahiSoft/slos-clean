<?php
/**
 * DSR Audit Service
 *
 * Centralized service for logging DSR lifecycle actions with privacy-focused design.
 * Automatically captures IP/UA hashes and provides helper methods for common actions.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\DSR_Audit_Log_Repository;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Audit_Service Class
 *
 * Provides centralized audit logging for all DSR lifecycle actions.
 * Automatically captures environmental data (IP, UA) and fires hooks.
 *
 * @since 3.0.1
 */
class DSR_Audit_Service extends Base_Service {

	/**
	 * Audit log repository
	 *
	 * @var DSR_Audit_Log_Repository
	 */
	private $repository;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 * @param DSR_Audit_Log_Repository $repository Audit log repository instance
	 */
	public function __construct( DSR_Audit_Log_Repository $repository ) {
		parent::__construct();
		$this->repository = $repository;

		// Hook into existing DSR action hooks to auto-log
		add_action( 'slos_dsr_audit_log', array( $this, 'handle_audit_hook' ), 10, 3 );
	}

	/**
	 * Log DSR action with automatic environment capture
	 *
	 * @since 3.0.1
	 * @param int    $request_id DSR request ID
	 * @param string $action     Action type (e.g., 'submit', 'verify', 'status_change')
	 * @param array  $data       Additional data {
	 *     @type int    $actor_id  User ID performing action (null for system/requester)
	 *     @type string $note      Human-readable note
	 *     @type array  $metadata  Additional structured data
	 * }
	 * @return int|false Log ID or false on failure
	 */
	public function log( int $request_id, string $action, array $data = array() ): int|false {
		// Automatically capture IP and user agent
		$data['ip_address'] = $this->get_client_ip();
		$data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

		// Set actor_id to current user if not specified
		if ( ! isset( $data['actor_id'] ) && is_user_logged_in() ) {
			$data['actor_id'] = get_current_user_id();
		}

		$log_id = $this->repository->log_action( $request_id, $action, $data );

		if ( $log_id ) {
			/**
			 * Fires after audit log is created
			 *
			 * @since 3.0.1
			 * @param int    $log_id     Inserted log ID
			 * @param int    $request_id DSR request ID
			 * @param string $action     Action type
			 * @param array  $data       Log data
			 */
			do_action( 'slos_dsr_audit_logged', $log_id, $request_id, $action, $data );
		}

		return $log_id;
	}

	/**
	 * Handle legacy audit hook for backward compatibility
	 *
	 * @since 3.0.1
	 * @param int    $request_id DSR request ID
	 * @param string $action     Action type
	 * @param array  $data       Additional data
	 * @return void
	 */
	public function handle_audit_hook( int $request_id, string $action, array $data = array() ): void {
		$this->log( $request_id, $action, $data );
	}

	/**
	 * Log request submission
	 *
	 * @since 3.0.1
	 * @param int    $request_id DSR request ID
	 * @param string $email      Requester email
	 * @param string $type       Request type
	 * @return int|false Log ID or false on failure
	 */
	public function log_submission( int $request_id, string $email, string $type ): int|false {
		return $this->log( $request_id, 'submit', array(
			'note'     => sprintf( 'Request submitted by %s (Type: %s)', $email, $type ),
			'metadata' => array(
				'email' => $email,
				'type'  => $type,
			),
		) );
	}

	/**
	 * Log email verification
	 *
	 * @since 3.0.1
	 * @param int $request_id DSR request ID
	 * @return int|false Log ID or false on failure
	 */
	public function log_verification( int $request_id ): int|false {
		return $this->log( $request_id, 'verify', array(
			'note' => 'Email address verified successfully',
		) );
	}

	/**
	 * Log status change
	 *
	 * @since 3.0.1
	 * @param int    $request_id  DSR request ID
	 * @param string $old_status  Previous status
	 * @param string $new_status  New status
	 * @param int    $actor_id    User ID making change
	 * @param string $note        Optional note
	 * @return int|false Log ID or false on failure
	 */
	public function log_status_change( int $request_id, string $old_status, string $new_status, int $actor_id = 0, string $note = '' ): int|false {
		$default_note = sprintf( 'Status changed from "%s" to "%s"', $old_status, $new_status );

		return $this->log( $request_id, 'status_change', array(
			'actor_id' => $actor_id ?: null,
			'note'     => ! empty( $note ) ? $note : $default_note,
			'metadata' => array(
				'old_status' => $old_status,
				'new_status' => $new_status,
			),
		) );
	}

	/**
	 * Log request assignment
	 *
	 * @since 3.0.1
	 * @param int $request_id   DSR request ID
	 * @param int $assigned_to  User ID assigned to
	 * @param int $assigned_by  User ID who assigned
	 * @return int|false Log ID or false on failure
	 */
	public function log_assignment( int $request_id, int $assigned_to, int $assigned_by = 0 ): int|false {
		$assignee = get_userdata( $assigned_to );
		$assigner = $assigned_by ? get_userdata( $assigned_by ) : null;

		$note = sprintf(
			'Request assigned to %s%s',
			$assignee ? $assignee->display_name : 'User #' . $assigned_to,
			$assigner ? ' by ' . $assigner->display_name : ''
		);

		return $this->log( $request_id, 'assign', array(
			'actor_id' => $assigned_by ?: null,
			'note'     => $note,
			'metadata' => array(
				'assigned_to' => $assigned_to,
				'assigned_by' => $assigned_by,
			),
		) );
	}

	/**
	 * Log note addition
	 *
	 * @since 3.0.1
	 * @param int    $request_id DSR request ID
	 * @param string $note       Note text
	 * @param int    $actor_id   User ID adding note
	 * @return int|false Log ID or false on failure
	 */
	public function log_note( int $request_id, string $note, int $actor_id = 0 ): int|false {
		return $this->log( $request_id, 'note_added', array(
			'actor_id' => $actor_id ?: null,
			'note'     => $note,
		) );
	}

	/**
	 * Log export generation
	 *
	 * @since 3.0.1
	 * @param int    $request_id DSR request ID
	 * @param string $format     Export format (json, xml, csv, pdf, html)
	 * @param int    $actor_id   User ID generating export
	 * @return int|false Log ID or false on failure
	 */
	public function log_export_generated( int $request_id, string $format, int $actor_id = 0 ): int|false {
		return $this->log( $request_id, 'export_generated', array(
			'actor_id' => $actor_id ?: null,
			'note'     => sprintf( 'Data export generated in %s format', strtoupper( $format ) ),
			'metadata' => array(
				'format'    => $format,
				'timestamp' => current_time( 'mysql' ),
			),
		) );
	}

	/**
	 * Log export download
	 *
	 * @since 3.0.1
	 * @param int    $request_id DSR request ID
	 * @param string $token      Download token
	 * @param string $format     Export format
	 * @return int|false Log ID or false on failure
	 */
	public function log_export_download( int $request_id, string $token, string $format ): int|false {
		return $this->log( $request_id, 'export_downloaded', array(
			'note'     => sprintf( 'Export file downloaded (%s format)', strtoupper( $format ) ),
			'metadata' => array(
				'token'     => substr( $token, 0, 8 ) . '...', // Only log partial token
				'format'    => $format,
				'timestamp' => current_time( 'mysql' ),
			),
		) );
	}

	/**
	 * Log erasure execution
	 *
	 * @since 3.0.1
	 * @param int   $request_id  DSR request ID
	 * @param array $summary     Erasure summary {
	 *     @type int $items_deleted Total items deleted
	 *     @type int $items_failed  Total items failed
	 *     @type int $items_skipped Total items skipped
	 * }
	 * @param int   $actor_id    User ID executing erasure
	 * @return int|false Log ID or false on failure
	 */
	public function log_erasure_executed( int $request_id, array $summary, int $actor_id = 0 ): int|false {
		$note = sprintf(
			'Data erasure executed: %d deleted, %d failed, %d skipped',
			$summary['items_deleted'] ?? 0,
			$summary['items_failed'] ?? 0,
			$summary['items_skipped'] ?? 0
		);

		return $this->log( $request_id, 'erasure_executed', array(
			'actor_id' => $actor_id ?: null,
			'note'     => $note,
			'metadata' => $summary,
		) );
	}

	/**
	 * Log dry-run erasure preview
	 *
	 * @since 3.0.1
	 * @param int   $request_id DSR request ID
	 * @param array $preview    Preview summary
	 * @param int   $actor_id   User ID running preview
	 * @return int|false Log ID or false on failure
	 */
	public function log_erasure_preview( int $request_id, array $preview, int $actor_id = 0 ): int|false {
		$note = sprintf(
			'Erasure preview generated: %d items would be deleted',
			$preview['total_items'] ?? 0
		);

		return $this->log( $request_id, 'erasure_preview', array(
			'actor_id' => $actor_id ?: null,
			'note'     => $note,
			'metadata' => $preview,
		) );
	}

	/**
	 * Get timeline for a request
	 *
	 * @since 3.0.1
	 * @param int   $request_id DSR request ID
	 * @param array $args       Query arguments
	 * @return array Array of log entries with formatted data
	 */
	public function get_timeline( int $request_id, array $args = array() ): array {
		$logs = $this->repository->get_logs_by_request( $request_id, $args );

		// Enhance logs with user data
		foreach ( $logs as &$log ) {
			if ( ! empty( $log['actor_id'] ) ) {
				$user = get_userdata( $log['actor_id'] );
				$log['actor_name'] = $user ? $user->display_name : 'Unknown User';
			} else {
				$log['actor_name'] = 'System';
			}

			// Format action label
			$log['action_label'] = $this->get_action_label( $log['action'] );
		}

		return $logs;
	}

	/**
	 * Get logs with filters (admin)
	 *
	 * @since 3.0.1
	 * @param array $filters Filter parameters
	 * @return array Array with 'logs' and 'total' keys
	 */
	public function get_logs( array $filters = array() ): array {
		$result = $this->repository->get_logs( $filters );

		// Enhance logs with user data
		foreach ( $result['logs'] as &$log ) {
			if ( ! empty( $log['actor_id'] ) ) {
				$user = get_userdata( $log['actor_id'] );
				$log['actor_name'] = $user ? $user->display_name : 'Unknown User';
			} else {
				$log['actor_name'] = 'System';
			}

			// Format action label
			$log['action_label'] = $this->get_action_label( $log['action'] );
		}

		return $result;
	}

	/**
	 * Get action statistics for reporting
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date (YYYY-MM-DD)
	 * @param string $end_date   End date (YYYY-MM-DD)
	 * @return array Action statistics
	 */
	public function get_action_statistics( string $start_date, string $end_date ): array {
		return $this->repository->get_action_statistics( $start_date, $end_date );
	}

	/**
	 * Get recent activity for dashboard
	 *
	 * @since 3.0.1
	 * @param int $limit Number of recent actions
	 * @return array Recent log entries
	 */
	public function get_recent_activity( int $limit = 10 ): array {
		$logs = $this->repository->get_recent_actions( $limit );

		// Enhance with user data
		foreach ( $logs as &$log ) {
			if ( ! empty( $log['actor_id'] ) ) {
				$user = get_userdata( $log['actor_id'] );
				$log['actor_name'] = $user ? $user->display_name : 'Unknown User';
			} else {
				$log['actor_name'] = 'System';
			}

			$log['action_label'] = $this->get_action_label( $log['action'] );
		}

		return $logs;
	}

	/**
	 * Delete logs for a request (GDPR erasure)
	 *
	 * @since 3.0.1
	 * @param int $request_id DSR request ID
	 * @return int|false Number of logs deleted or false on failure
	 */
	public function delete_logs( int $request_id ): int|false {
		return $this->repository->delete_logs_by_request( $request_id );
	}

	/**
	 * Get human-readable action label
	 *
	 * @since 3.0.1
	 * @param string $action Action type
	 * @return string Formatted label
	 */
	private function get_action_label( string $action ): string {
		$labels = array(
			'submit'             => __( 'Request Submitted', 'shahi-legalflowsuite' ),
			'verify'             => __( 'Email Verified', 'shahi-legalflowsuite' ),
			'status_change'      => __( 'Status Changed', 'shahi-legalflowsuite' ),
			'assign'             => __( 'Request Assigned', 'shahi-legalflowsuite' ),
			'note_added'         => __( 'Note Added', 'shahi-legalflowsuite' ),
			'export_generated'   => __( 'Export Generated', 'shahi-legalflowsuite' ),
			'export_downloaded'  => __( 'Export Downloaded', 'shahi-legalflowsuite' ),
			'erasure_executed'   => __( 'Erasure Executed', 'shahi-legalflowsuite' ),
			'erasure_preview'    => __( 'Erasure Preview', 'shahi-legalflowsuite' ),
		);

		return $labels[ $action ] ?? ucwords( str_replace( '_', ' ', $action ) );
	}

	/**
	 * Get client IP address (respects proxy headers)
	 *
	 * @since 3.0.1
	 * @return string Client IP address
	 */
	private function get_client_ip(): string {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare
			'HTTP_X_REAL_IP',        // Nginx proxy
			'HTTP_X_FORWARDED_FOR',  // Standard proxy
			'REMOTE_ADDR',           // Direct connection
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = $_SERVER[ $key ];
				// Handle comma-separated IPs (take first)
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = explode( ',', $ip )[0];
				}
				return trim( $ip );
			}
		}

		return '0.0.0.0';
	}
}
