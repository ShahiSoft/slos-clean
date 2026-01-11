<?php
/**
 * DSR Email Service
 *
 * Comprehensive email notification system for DSR lifecycle events.
 * Includes requester notifications, admin alerts, email throttling,
 * customizable templates, and extensibility hooks.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DSR_Email_Service
 *
 * Comprehensive notification system for DSR lifecycle:
 * - Requester: submission, verification, status updates, completion, rejection, export ready
 * - Admin: new requests, overdue warnings, erasure actions
 * - Email throttling to prevent spam
 * - Filterable templates for customization
 * - Action hooks for logging and extensibility
 *
 * @since 3.0.1
 */
class DSR_Email_Service {

	/**
	 * Transient prefix for throttling
	 *
	 * @since 3.0.1
	 * @var string
	 */
	private const THROTTLE_PREFIX = 'slos_dsr_email_throttle_';

	/**
	 * Email throttle window (seconds) - prevents duplicate emails
	 *
	 * @since 3.0.1
	 * @var int
	 */
	private const THROTTLE_WINDOW = 300; // 5 minutes

	/**
	 * Constructor: register all email hooks
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		// Requester notifications
		add_action( 'slos_dsr_submitted', array( $this, 'send_verification_email' ), 10, 2 );
		add_action( 'slos_dsr_status_changed', array( $this, 'notify_requester_status_change' ), 10, 3 );
		add_action( 'slos_dsr_completed', array( $this, 'notify_requester_completed' ), 10, 2 );
		add_action( 'slos_dsr_export_ready', array( $this, 'notify_requester_export_ready' ), 10, 3 );

		// Admin notifications
		add_action( 'slos_dsr_submitted', array( $this, 'notify_admin_new_request' ), 10, 2 );
		add_action( 'slos_dsr_status_changed', array( $this, 'notify_admin_status_change' ), 10, 3 );
		add_action( 'slos_dsr_erasure_execute', array( $this, 'notify_admin_erasure_action' ), 10, 2 );

		// Overdue warnings (checked via cron)
		add_action( 'slos_dsr_check_overdue', array( $this, 'check_and_notify_overdue' ) );

		// Schedule daily overdue check if not already scheduled
		if ( ! wp_next_scheduled( 'slos_dsr_check_overdue' ) ) {
			wp_schedule_event( time(), 'daily', 'slos_dsr_check_overdue' );
		}
	}

	/**
	 * Send verification email to requester
	 *
	 * Triggered on request submission. Includes verification link.
	 *
	 * @since 3.0.1
	 * @param int   $request_id Request ID.
	 * @param array $data       Request data (includes email, request_type, regulation).
	 * @return bool True if email sent, false otherwise.
	 */
	public function send_verification_email( int $request_id, array $data ): bool {
		$email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
		if ( empty( $email ) || ! is_email( $email ) ) {
			return false;
		}

		// Check throttle
		if ( $this->is_throttled( 'verification', $request_id ) ) {
			return false;
		}

		// Fetch token from repository
		$repo   = new \ShahiLegalFlowSuite\Database\Repositories\DSR_Repository();
		$record = $repo->find( $request_id );
		if ( ! $record || empty( $record->verification_token ) ) {
			return false;
		}

		$template_data = array(
			'request_id'       => $request_id,
			'email'            => $email,
			'request_type'     => isset( $data['request_type'] ) ? $data['request_type'] : 'access',
			'regulation'       => isset( $data['regulation'] ) ? $data['regulation'] : 'GDPR',
			'verification_url' => $this->get_verification_url( $record->verification_token ),
			'site_name'        => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		);

		$subject = $this->get_template( 'verification_subject', $template_data );
		$message = $this->get_template( 'verification_body', $template_data );

		$sent = wp_mail( $email, $subject, $message );

		if ( $sent ) {
			$this->set_throttle( 'verification', $request_id );
			$this->log_email_sent( 'verification', $request_id, $email, $subject );
		}

		return $sent;
	}

	/**
	 * Notify requester on status change
	 *
	 * Sends appropriate message based on new status.
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID.
	 * @param string $old_status Old status.
	 * @param string $new_status New status.
	 * @return bool True if email sent.
	 */
	public function notify_requester_status_change( int $request_id, string $old_status, string $new_status ): bool {
		// Check if requester notifications enabled
		if ( ! $this->is_notification_enabled( 'requester_status_change' ) ) {
			return false;
		}

		// Skip if moving to completed (handled by separate method)
		if ( $new_status === 'completed' ) {
			return false;
		}

		// Check throttle
		if ( $this->is_throttled( 'status_change', $request_id ) ) {
			return false;
		}

		// Load record
		$repo   = new \ShahiLegalFlowSuite\Database\Repositories\DSR_Repository();
		$record = $repo->find( $request_id );
		if ( ! $record || empty( $record->email ) ) {
			return false;
		}

		$template_data = array(
			'request_id'   => $request_id,
			'email'        => $record->email,
			'request_type' => $record->request_type,
			'old_status'   => $old_status,
			'new_status'   => $new_status,
			'status_url'   => $this->get_status_url( $record->status_token ),
			'site_name'    => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		);

		// Special handling for specific status transitions
		$template_key = 'status_change_' . $new_status;
		if ( ! $this->template_exists( $template_key . '_subject' ) ) {
			$template_key = 'status_change_generic';
		}

		$subject = $this->get_template( $template_key . '_subject', $template_data );
		$message = $this->get_template( $template_key . '_body', $template_data );

		$sent = wp_mail( sanitize_email( $record->email ), $subject, $message );

		if ( $sent ) {
			$this->set_throttle( 'status_change', $request_id );
			$this->log_email_sent( 'status_change_' . $new_status, $request_id, $record->email, $subject );
		}

		return $sent;
	}

	/**
	 * Notify requester on completion
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID.
	 * @param object $request    Request object.
	 * @return bool True if email sent.
	 */
	public function notify_requester_completed( int $request_id, $request ): bool {
		if ( ! $this->is_notification_enabled( 'requester_completed' ) ) {
			return false;
		}

		if ( empty( $request->email ) ) {
			return false;
		}

		// Check throttle
		if ( $this->is_throttled( 'completed', $request_id ) ) {
			return false;
		}

		$template_data = array(
			'request_id'   => $request_id,
			'email'        => $request->email,
			'request_type' => $request->request_type,
			'status_url'   => $this->get_status_url( $request->status_token ),
			'site_name'    => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		);

		$subject = $this->get_template( 'completed_subject', $template_data );
		$message = $this->get_template( 'completed_body', $template_data );

		$sent = wp_mail( sanitize_email( $request->email ), $subject, $message );

		if ( $sent ) {
			$this->set_throttle( 'completed', $request_id );
			$this->log_email_sent( 'completed', $request_id, $request->email, $subject );
		}

		return $sent;
	}

	/**
	 * Notify requester when export is ready
	 *
	 * @since 3.0.1
	 * @param int    $request_id   Request ID.
	 * @param string $export_token Export token.
	 * @param object $request      Request object.
	 * @return bool True if email sent.
	 */
	public function notify_requester_export_ready( int $request_id, string $export_token, $request ): bool {
		if ( empty( $request->email ) ) {
			return false;
		}

		// Check throttle
		if ( $this->is_throttled( 'export_ready', $request_id ) ) {
			return false;
		}

		$download_url = add_query_arg(
			array( 'slos_dsr_download' => $export_token ),
			home_url()
		);

		$template_data = array(
			'request_id'   => $request_id,
			'email'        => $request->email,
			'request_type' => $request->request_type,
			'download_url' => $download_url,
			'expires_days' => 7,
			'site_name'    => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		);

		$subject = $this->get_template( 'export_ready_subject', $template_data );
		$message = $this->get_template( 'export_ready_body', $template_data );

		$sent = wp_mail( sanitize_email( $request->email ), $subject, $message );

		if ( $sent ) {
			$this->set_throttle( 'export_ready', $request_id );
			$this->log_email_sent( 'export_ready', $request_id, $request->email, $subject );
		}

		return $sent;
	}

	/**
	 * Notify admin on new request
	 *
	 * @since 3.0.1
	 * @param int   $request_id Request ID.
	 * @param array $data       Request data.
	 * @return bool True if email sent.
	 */
	public function notify_admin_new_request( int $request_id, array $data ): bool {
		if ( ! $this->is_notification_enabled( 'admin_new_request' ) ) {
			return false;
		}

		// Check throttle
		if ( $this->is_throttled( 'admin_new', $request_id ) ) {
			return false;
		}

		$admin_email = $this->get_admin_email();
		if ( empty( $admin_email ) ) {
			return false;
		}

		$template_data = array(
			'request_id'   => $request_id,
			'request_type' => isset( $data['request_type'] ) ? $data['request_type'] : 'access',
			'regulation'   => isset( $data['regulation'] ) ? $data['regulation'] : 'GDPR',
			'email'        => isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '',
			'admin_url'    => admin_url( 'admin.php?page=slos-dsr-requests' ),
			'site_name'    => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		);

		$subject = $this->get_template( 'admin_new_request_subject', $template_data );
		$message = $this->get_template( 'admin_new_request_body', $template_data );

		$sent = wp_mail( $admin_email, $subject, $message );

		if ( $sent ) {
			$this->set_throttle( 'admin_new', $request_id );
			$this->log_email_sent( 'admin_new_request', $request_id, $admin_email, $subject );
		}

		return $sent;
	}

	/**
	 * Notify admin on status change
	 *
	 * Sends notifications for key status transitions (verified, rejected).
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID.
	 * @param string $old_status Old status.
	 * @param string $new_status New status.
	 * @return bool True if email sent.
	 */
	public function notify_admin_status_change( int $request_id, string $old_status, string $new_status ): bool {
		// Only notify on specific transitions
		$notify_on = array( 'verified', 'rejected' );
		if ( ! in_array( $new_status, $notify_on, true ) ) {
			return false;
		}

		if ( ! $this->is_notification_enabled( 'admin_status_change' ) ) {
			return false;
		}

		// Check throttle
		if ( $this->is_throttled( 'admin_status', $request_id ) ) {
			return false;
		}

		$admin_email = $this->get_admin_email();
		if ( empty( $admin_email ) ) {
			return false;
		}

		// Load record
		$repo   = new \ShahiLegalFlowSuite\Database\Repositories\DSR_Repository();
		$record = $repo->find( $request_id );
		if ( ! $record ) {
			return false;
		}

		$template_data = array(
			'request_id'   => $request_id,
			'request_type' => $record->request_type,
			'old_status'   => $old_status,
			'new_status'   => $new_status,
			'admin_url'    => admin_url( 'admin.php?page=slos-dsr-requests' ),
			'site_name'    => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		);

		$subject = $this->get_template( 'admin_status_change_subject', $template_data );
		$message = $this->get_template( 'admin_status_change_body', $template_data );

		$sent = wp_mail( $admin_email, $subject, $message );

		if ( $sent ) {
			$this->set_throttle( 'admin_status', $request_id );
			$this->log_email_sent( 'admin_status_change', $request_id, $admin_email, $subject );
		}

		return $sent;
	}

	/**
	 * Notify admin when erasure action required
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID.
	 * @param object $request    Request object.
	 * @return bool True if email sent.
	 */
	public function notify_admin_erasure_action( int $request_id, $request ): bool {
		if ( ! $this->is_notification_enabled( 'admin_erasure' ) ) {
			return false;
		}

		// Check throttle
		if ( $this->is_throttled( 'admin_erasure', $request_id ) ) {
			return false;
		}

		$admin_email = $this->get_admin_email();
		if ( empty( $admin_email ) ) {
			return false;
		}

		$template_data = array(
			'request_id'   => $request_id,
			'request_type' => $request->request_type,
			'email'        => $request->email,
			'admin_url'    => admin_url( 'admin.php?page=slos-dsr-requests' ),
			'site_name'    => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		);

		$subject = $this->get_template( 'admin_erasure_subject', $template_data );
		$message = $this->get_template( 'admin_erasure_body', $template_data );

		$sent = wp_mail( $admin_email, $subject, $message );

		if ( $sent ) {
			$this->set_throttle( 'admin_erasure', $request_id );
			$this->log_email_sent( 'admin_erasure', $request_id, $admin_email, $subject );
		}

		return $sent;
	}

	/**
	 * Check for overdue requests and send warnings
	 *
	 * Triggered daily via cron. Sends warning when request exceeds 80% of SLA.
	 *
	 * @since 3.0.1
	 * @return int Number of warnings sent.
	 */
	public function check_and_notify_overdue(): int {
		if ( ! $this->is_notification_enabled( 'admin_overdue' ) ) {
			return 0;
		}

		$repo     = new \ShahiLegalFlowSuite\Database\Repositories\DSR_Repository();
		$requests = $repo->find_by_status( array( 'verified', 'in_progress' ) );

		$warnings_sent = 0;
		$now           = current_time( 'timestamp' );

		foreach ( $requests as $request ) {
			// Calculate days remaining
			$due_date  = strtotime( $request->due_date );
			$submitted = strtotime( $request->submitted_at );
			$total_sla = $due_date - $submitted;
			$elapsed   = $now - $submitted;

			// Send warning if > 80% of SLA elapsed
			if ( $elapsed / $total_sla >= 0.8 ) {
				// Check if already warned recently (24 hours)
				$throttle_key = 'overdue_' . $request->id;
				if ( $this->is_throttled( 'overdue', $request->id, 86400 ) ) {
					continue;
				}

				$days_remaining = max( 0, ceil( ( $due_date - $now ) / DAY_IN_SECONDS ) );

				$template_data = array(
					'request_id'     => $request->id,
					'request_type'   => $request->request_type,
					'days_remaining' => $days_remaining,
					'submitted_at'   => $request->submitted_at,
					'due_date'       => $request->due_date,
					'admin_url'      => admin_url( 'admin.php?page=slos-dsr-requests' ),
					'site_name'      => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				);

				$admin_email = $this->get_admin_email();
				$subject     = $this->get_template( 'admin_overdue_subject', $template_data );
				$message     = $this->get_template( 'admin_overdue_body', $template_data );

				if ( wp_mail( $admin_email, $subject, $message ) ) {
					$this->set_throttle( 'overdue', $request->id, 86400 );
					$this->log_email_sent( 'admin_overdue', $request->id, $admin_email, $subject );
					++$warnings_sent;
				}
			}
		}

		return $warnings_sent;
	}
	// ============================================================================
	// HELPER METHODS
	// ============================================================================

	/**
	 * Get email template
	 *
	 * Fetches customizable email templates with filter support.
	 *
	 * @since 3.0.1
	 * @param string $template_key Template key (e.g., 'verification_subject').
	 * @param array  $data         Template data for variable replacement.
	 * @return string Processed template.
	 */
	private function get_template( string $template_key, array $data ): string {
		$templates = $this->get_default_templates();

		$template = isset( $templates[ $template_key ] ) ? $templates[ $template_key ] : '';

		/**
		 * Filter email template
		 *
		 * @since 3.0.1
		 * @param string $template     Template content.
		 * @param string $template_key Template key.
		 * @param array  $data         Template data.
		 */
		$template = apply_filters( 'slos_dsr_email_template', $template, $template_key, $data );
		$template = apply_filters( "slos_dsr_email_template_{$template_key}", $template, $data );

		// Replace variables
		return $this->replace_variables( $template, $data );
	}

	/**
	 * Check if template exists
	 *
	 * @since 3.0.1
	 * @param string $template_key Template key.
	 * @return bool True if template exists.
	 */
	private function template_exists( string $template_key ): bool {
		$templates = $this->get_default_templates();
		return isset( $templates[ $template_key ] );
	}

	/**
	 * Get default email templates
	 *
	 * @since 3.0.1
	 * @return array Email templates.
	 */
	private function get_default_templates(): array {
		return array(
			// Verification email
			'verification_subject'              => '[{site_name}] Verify Your Data Subject Request',
			'verification_body'                 => "{site_name}\n\n" .
				"We received a data subject request from this email address.\n\n" .
				"Request Type: {request_type_label}\n" .
				"Regulation: {regulation}\n" .
				"Request ID: #{request_id}\n\n" .
				"To verify this request and allow us to process it, please click:\n" .
				"{verification_url}\n\n" .
				"This link expires in 48 hours. If you did not submit this request, please ignore this email.\n\n" .
				"Thank you,\n{site_name}",

			// Status change - Generic
			'status_change_generic_subject'     => '[{site_name}] Update on Your Data Subject Request',
			'status_change_generic_body'        => "Hello,\n\n" .
				"Your data subject request (ID: #{request_id}) status has been updated.\n\n" .
				"Previous Status: {old_status_label}\n" .
				"New Status: {new_status_label}\n\n" .
				"You can check the current status at:\n{status_url}\n\n" .
				"Thank you,\n{site_name}",

			// Status change - Verified
			'status_change_verified_subject'    => '[{site_name}] Your Request Has Been Verified',
			'status_change_verified_body'       => "Hello,\n\n" .
				"Your data subject request (ID: #{request_id}) has been verified and is now being processed.\n\n" .
				"Request Type: {request_type_label}\n" .
				"Status: Verified\n\n" .
				"We will notify you once your request is completed. You can track progress at:\n" .
				"{status_url}\n\n" .
				"Thank you,\n{site_name}",

			// Status change - In Progress
			'status_change_in_progress_subject' => '[{site_name}] Your Request Is Being Processed',
			'status_change_in_progress_body'    => "Hello,\n\n" .
				"Your data subject request (ID: #{request_id}) is now being actively processed.\n\n" .
				"Request Type: {request_type_label}\n" .
				"Status: In Progress\n\n" .
				"We will notify you once completed. Track progress at:\n{status_url}\n\n" .
				"Thank you,\n{site_name}",

			// Status change - Rejected
			'status_change_rejected_subject'    => '[{site_name}] Update on Your Data Subject Request',
			'status_change_rejected_body'       => "Hello,\n\n" .
				"We regret to inform you that your data subject request (ID: #{request_id}) could not be processed.\n\n" .
				"Request Type: {request_type_label}\n" .
				"Status: Rejected\n\n" .
				"If you have questions or believe this was in error, please contact us.\n\n" .
				"Thank you,\n{site_name}",

			// Completed
			'completed_subject'                 => '[{site_name}] Your Data Subject Request Is Complete',
			'completed_body'                    => "Hello,\n\n" .
				"Your data subject request (ID: #{request_id}) has been successfully completed.\n\n" .
				"Request Type: {request_type_label}\n" .
				"Status: Completed\n\n" .
				"View details at:\n{status_url}\n\n" .
				"Thank you,\n{site_name}",

			// Export ready
			'export_ready_subject'              => '[{site_name}] Your Data Export Is Ready',
			'export_ready_body'                 => "Hello,\n\n" .
				"Your data export (Request ID: #{request_id}) is ready for download.\n\n" .
				"Download your data here:\n{download_url}\n\n" .
				"This link expires in {expires_days} days. The download is a single-use link for security.\n\n" .
				"Thank you,\n{site_name}",

			// Admin - New request
			'admin_new_request_subject'         => '[{site_name}] New Data Subject Request #{request_id}',
			'admin_new_request_body'            => "A new data subject request has been submitted:\n\n" .
				"Request ID: #{request_id}\n" .
				"Type: {request_type_label}\n" .
				"Regulation: {regulation}\n" .
				"Email: {email}\n" .
				"Submitted: {submitted_time}\n\n" .
				"View and manage this request:\n{admin_url}\n\n" .
				'This is an automated notification from {site_name}.',

			// Admin - Status change
			'admin_status_change_subject'       => '[{site_name}] DSR #{request_id} Status: {new_status_label}',
			'admin_status_change_body'          => "Data subject request status changed:\n\n" .
				"Request ID: #{request_id}\n" .
				"Type: {request_type_label}\n" .
				"Previous Status: {old_status_label}\n" .
				"New Status: {new_status_label}\n\n" .
				"View request:\n{admin_url}\n\n" .
				'This is an automated notification from {site_name}.',

			// Admin - Erasure action
			'admin_erasure_subject'             => '[{site_name}] Erasure Request #{request_id} Initiated',
			'admin_erasure_body'                => "A data erasure request has been initiated:\n\n" .
				"Request ID: #{request_id}\n" .
				"Type: {request_type_label}\n" .
				"Email: {email}\n\n" .
				"The automated erasure handlers have been triggered. Please review the request and verify all data has been properly anonymized:\n" .
				"{admin_url}\n\n" .
				'This is an automated notification from {site_name}.',

			// Admin - Overdue warning
			'admin_overdue_subject'             => '[{site_name}] URGENT: DSR #{request_id} Approaching Due Date',
			'admin_overdue_body'                => "ATTENTION: A data subject request is approaching its due date:\n\n" .
				"Request ID: #{request_id}\n" .
				"Type: {request_type_label}\n" .
				"Submitted: {submitted_at}\n" .
				"Due Date: {due_date}\n" .
				"Days Remaining: {days_remaining}\n\n" .
				"Please prioritize this request to avoid SLA breach:\n{admin_url}\n\n" .
				'This is an automated notification from {site_name}.',
		);
	}

	/**
	 * Replace template variables
	 *
	 * @since 3.0.1
	 * @param string $template Template with {variables}.
	 * @param array  $data     Data for replacement.
	 * @return string Processed template.
	 */
	private function replace_variables( string $template, array $data ): string {
		// Add computed fields
		$data['request_type_label'] = isset( $data['request_type'] ) ? ucfirst( str_replace( '_', ' ', $data['request_type'] ) ) : 'Access';
		$data['old_status_label']   = isset( $data['old_status'] ) ? ucfirst( str_replace( '_', ' ', $data['old_status'] ) ) : '';
		$data['new_status_label']   = isset( $data['new_status'] ) ? ucfirst( str_replace( '_', ' ', $data['new_status'] ) ) : '';
		$data['submitted_time']     = isset( $data['submitted_at'] ) ? $data['submitted_at'] : current_time( 'mysql' );

		// Replace all {variable} patterns
		foreach ( $data as $key => $value ) {
			$template = str_replace( '{' . $key . '}', (string) $value, $template );
		}

		return $template;
	}

	/**
	 * Get verification URL
	 *
	 * @since 3.0.1
	 * @param string $token Verification token.
	 * @return string Verification URL.
	 */
	private function get_verification_url( string $token ): string {
		$settings        = get_option( 'slos_dsr_settings', array() );
		$verify_page_url = isset( $settings['verify_page_url'] ) ? esc_url_raw( $settings['verify_page_url'] ) : '';

		if ( $verify_page_url ) {
			return add_query_arg( array( 'token' => rawurlencode( $token ) ), $verify_page_url );
		}

		// Default to REST endpoint
		return add_query_arg( array( 'token' => rawurlencode( $token ) ), rest_url( 'slos/v1/dsr/verify' ) );
	}

	/**
	 * Get status URL
	 *
	 * @since 3.0.1
	 * @param string $token Status token.
	 * @return string Status URL.
	 */
	private function get_status_url( string $token ): string {
		$settings        = get_option( 'slos_dsr_settings', array() );
		$status_page_url = isset( $settings['status_page_url'] ) ? esc_url_raw( $settings['status_page_url'] ) : '';

		if ( $status_page_url ) {
			return add_query_arg( array( 'token' => rawurlencode( $token ) ), $status_page_url );
		}

		// Default to home with token
		return add_query_arg( array( 'token' => rawurlencode( $token ) ), home_url( '/dsr-status/' ) );
	}

	/**
	 * Get admin email
	 *
	 * @since 3.0.1
	 * @return string Admin email.
	 */
	private function get_admin_email(): string {
		$settings = get_option( 'slos_dsr_settings', array() );
		$email    = isset( $settings['admin_email'] ) ? sanitize_email( $settings['admin_email'] ) : '';

		if ( empty( $email ) ) {
			$email = get_option( 'admin_email' );
		}

		return $email;
	}

	/**
	 * Check if notification is enabled
	 *
	 * @since 3.0.1
	 * @param string $notification_type Notification type.
	 * @return bool True if enabled.
	 */
	private function is_notification_enabled( string $notification_type ): bool {
		$settings = get_option( 'slos_dsr_settings', array() );

		// Map notification types to settings keys
		$setting_key = str_replace( 'admin_', 'notify_admin_', $notification_type );
		$setting_key = str_replace( 'requester_', 'notify_requester_', $setting_key );

		// Default: enable all notifications unless explicitly disabled
		return isset( $settings[ $setting_key ] ) ? (bool) $settings[ $setting_key ] : true;
	}

	/**
	 * Check if email is throttled
	 *
	 * Prevents duplicate emails within throttle window.
	 *
	 * @since 3.0.1
	 * @param string $type       Email type.
	 * @param int    $request_id Request ID.
	 * @param int    $window     Throttle window in seconds (default: 5 minutes).
	 * @return bool True if throttled.
	 */
	private function is_throttled( string $type, int $request_id, int $window = null ): bool {
		$window = $window ?? self::THROTTLE_WINDOW;
		$key    = self::THROTTLE_PREFIX . $type . '_' . $request_id;

		return false !== get_transient( $key );
	}

	/**
	 * Set email throttle
	 *
	 * @since 3.0.1
	 * @param string $type       Email type.
	 * @param int    $request_id Request ID.
	 * @param int    $window     Throttle window in seconds (default: 5 minutes).
	 * @return void
	 */
	private function set_throttle( string $type, int $request_id, int $window = null ): void {
		$window = $window ?? self::THROTTLE_WINDOW;
		$key    = self::THROTTLE_PREFIX . $type . '_' . $request_id;

		set_transient( $key, time(), $window );
	}

	/**
	 * Log email sent
	 *
	 * Fires action hook for logging and extensibility.
	 *
	 * @since 3.0.1
	 * @param string $type       Email type.
	 * @param int    $request_id Request ID.
	 * @param string $to         Recipient email.
	 * @param string $subject    Email subject.
	 * @return void
	 */
	private function log_email_sent( string $type, int $request_id, string $to, string $subject ): void {
		/**
		 * Fires after a DSR email is sent
		 *
		 * @since 3.0.1
		 * @param string $type       Email type (verification, status_change, etc.).
		 * @param int    $request_id Request ID.
		 * @param string $to         Recipient email address.
		 * @param string $subject    Email subject.
		 * @param int    $timestamp  Unix timestamp.
		 */
		do_action( 'slos_dsr_email_sent', $type, $request_id, $to, $subject, time() );
	}
}
