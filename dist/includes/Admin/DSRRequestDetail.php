<?php
/**
 * DSR Request Detail Admin Page
 *
 * Provides detailed view of a single DSR request including audit timeline,
 * SLA countdown, status management, and download events.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Database\Repositories\DSR_Repository;
use ShahiLegalFlowSuite\Services\DSR_Audit_Service;
use ShahiLegalFlowSuite\Database\Repositories\DSR_Audit_Log_Repository;
use ShahiLegalFlowSuite\Services\Consent_Service;
use ShahiLegalFlowSuite\Database\Repositories\Consent_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR Request Detail Page Controller
 */
class DSRRequestDetail {

	/**
	 * DSR repository
	 *
	 * @var DSR_Repository
	 */
	private $repository;

	/**
	 * Audit service
	 *
	 * @var DSR_Audit_Service
	 */
	private $audit_service;

	/**
	 * Consent service
	 *
	 * @var Consent_Service
	 */
	private $consent_service;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Dependencies will be lazy-loaded when needed
	}

	/**
	 * Get repository instance (lazy initialization)
	 *
	 * @return DSR_Repository
	 */
	private function get_repository() {
		if ( null === $this->repository ) {
			$this->repository = new DSR_Repository();
		}
		return $this->repository;
	}

	/**
	 * Get audit service instance (lazy initialization)
	 *
	 * @return DSR_Audit_Service
	 */
	private function get_audit_service() {
		if ( null === $this->audit_service ) {
			$audit_repository    = new DSR_Audit_Log_Repository();
			$this->audit_service = new DSR_Audit_Service( $audit_repository );
		}
		return $this->audit_service;
	}

	/**
	 * Get consent service instance (lazy initialization)
	 *
	 * @return Consent_Service
	 */
	private function get_consent_service() {
		if ( null === $this->consent_service ) {
			$consent_repository    = new Consent_Repository();
			$this->consent_service = new Consent_Service( $consent_repository );
		}
		return $this->consent_service;
	}

	/**
	 * Render the DSR Request Detail page
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'slos_manage_dsr' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'shahi-legalflowsuite' ) );
		}

		$request_id = isset( $_GET['request_id'] ) ? absint( $_GET['request_id'] ) : 0;

		if ( ! $request_id ) {
			wp_die( esc_html__( 'Invalid request ID.', 'shahi-legalflowsuite' ) );
		}

		// Get request data
		$request = $this->get_repository()->find_by_id( $request_id );

		if ( ! $request ) {
			wp_die( esc_html__( 'Request not found.', 'shahi-legalflowsuite' ) );
		}

		// Get audit timeline
		$timeline = $this->get_audit_service()->get_timeline( $request_id, array( 'order' => 'DESC' ) );

		echo '<div class="wrap shahi-legalflowsuite">';
		/* translators: %d: DSR request ID number */
		echo '<h1 class="wp-heading-inline">' . sprintf( esc_html__( 'DSR Request #%d', 'shahi-legalflowsuite' ), absint( $request_id ) ) . '</h1>';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=' . MenuManager::MENU_SLUG . '-dsr-requests' ) ) . '" class="page-title-action">' . esc_html__( '← Back to Requests', 'shahi-legalflowsuite' ) . '</a>';
		echo '<hr class="wp-header-end" />';

		// Two-column layout
		echo '<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">';

		// Left column: Request details
		echo '<div>';
		$this->render_request_details( $request );
		$this->render_consent_history( $request );
		$this->render_audit_timeline( $timeline );
		echo '</div>';

		// Right column: SLA countdown and actions
		echo '<div>';
		$this->render_sla_panel( $request );
		$this->render_actions_panel( $request );
		echo '</div>';

		echo '</div>'; // grid

		echo '</div>'; // .wrap
	}

	/**
	 * Render request details section
	 *
	 * @param object $request Request data
	 * @return void
	 */
	private function render_request_details( $request ): void {
		echo '<div class="postbox">';
		echo '<h2 class="hndle"><span>' . esc_html__( 'Request Details', 'shahi-legalflowsuite' ) . '</span></h2>';
		echo '<div class="inside">';

		echo '<table class="form-table">';

		echo '<tr>';
		echo '<th scope="row">' . esc_html__( 'Email', 'shahi-legalflowsuite' ) . '</th>';
		echo '<td>' . esc_html( $request->requester_email ?? '' ) . '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th scope="row">' . esc_html__( 'Request Type', 'shahi-legalflowsuite' ) . '</th>';
		echo '<td><strong>' . esc_html( ucwords( str_replace( '_', ' ', $request->request_type ?? '' ) ) ) . '</strong></td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th scope="row">' . esc_html__( 'Status', 'shahi-legalflowsuite' ) . '</th>';
		echo '<td>' . wp_kses_post( $this->get_status_badge( $request->status ?? '' ) ) . '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th scope="row">' . esc_html__( 'Regulation', 'shahi-legalflowsuite' ) . '</th>';
		echo '<td>' . esc_html( $request->regulation ?? 'GDPR' ) . '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th scope="row">' . esc_html__( 'Submitted', 'shahi-legalflowsuite' ) . '</th>';
		echo '<td>' . esc_html( $request->submitted_at ?? '' ) . '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<th scope="row">' . esc_html__( 'Due Date', 'shahi-legalflowsuite' ) . '</th>';
		echo '<td>' . esc_html( $request->due_date ?? '' ) . '</td>';
		echo '</tr>';

		if ( ! empty( $request->completed_at ) ) {
			echo '<tr>';
			echo '<th scope="row">' . esc_html__( 'Completed', 'shahi-legalflowsuite' ) . '</th>';
			echo '<td>' . esc_html( $request->completed_at ) . '</td>';
			echo '</tr>';
		}

		if ( ! empty( $request->details ) ) {
			echo '<tr>';
			echo '<th scope="row">' . esc_html__( 'Details', 'shahi-legalflowsuite' ) . '</th>';
			echo '<td>' . esc_html( $request->details ) . '</td>';
			echo '</tr>';
		}

		echo '</table>';

		echo '</div>'; // .inside
		echo '</div>'; // .postbox
	}

	/**
	 * Render consent history section
	 *
	 * @param object $request Request data
	 * @return void
	 */
	private function render_consent_history( $request ): void {
		echo '<div class="postbox" style="margin-top: 20px;">';
		echo '<h2 class="hndle"><span>' . esc_html__( 'Consent History', 'shahi-legalflowsuite' ) . '</span></h2>';
		echo '<div class="inside">';

		// Fetch consents by email
		$consents = $this->get_consent_service()->get_by_email( $request->requester_email ?? '' );

		if ( empty( $consents ) ) {
			echo '<p>' . esc_html__( 'No consent records found for this email address.', 'shahi-legalflowsuite' ) . '</p>';
		} else {
			echo '<div class="consent-timeline-container" style="position: relative; padding-left: 30px;">';

			foreach ( $consents as $consent ) {
				$this->render_consent_entry( $consent );
			}

			echo '</div>'; // .consent-timeline-container
		}

		echo '</div>'; // .inside
		echo '</div>'; // .postbox
	}

	/**
	 * Render single consent entry
	 *
	 * @param array $consent Consent record data
	 * @return void
	 */
	private function render_consent_entry( array $consent ): void {
		$status       = $consent['status'] ?? '';
		$status_color = $status === 'accepted' ? '#46b450' : ( $status === 'rejected' ? '#dc3232' : '#f18500' );
		$icon         = $status === 'accepted' ? 'dashicons-yes' : ( $status === 'rejected' ? 'dashicons-no' : 'dashicons-minus' );

		echo '<div class="consent-entry" style="position: relative; padding: 15px 0; border-left: 2px solid #ddd;">';

		// Icon
		echo '<div style="position: absolute; left: -11px; top: 15px; width: 20px; height: 20px; border-radius: 50%; background: #fff; border: 2px solid ' . esc_attr( $status_color ) . '; display: flex; align-items: center; justify-content: center;">';
		echo '<span class="dashicons ' . esc_attr( $icon ) . '" style="font-size: 12px; width: 12px; height: 12px; color: ' . esc_attr( $status_color ) . ';"></span>';
		echo '</div>';

		// Content
		echo '<div style="margin-left: 20px;">';
		echo '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">';
		echo '<strong>' . esc_html( ucwords( str_replace( '_', ' ', $consent['type'] ?? '' ) ) ) . '</strong>';
		echo '<span style="font-size: 12px; color: #666;">' . esc_html( $this->format_relative_time( $consent['created_at'] ?? '' ) ) . '</span>';
		echo '</div>';

		// Status badge
		echo '<div style="margin: 5px 0;">';
		echo '<span style="display: inline-block; padding: 2px 8px; background: ' . esc_attr( $status_color ) . '; color: white; border-radius: 3px; font-size: 11px; font-weight: 600;">' . esc_html( ucfirst( $status ) ) . '</span>';
		echo '</div>';

		// User info
		if ( ! empty( $consent['user_name'] ) ) {
			echo '<div style="font-size: 12px; color: #666; margin-top: 5px;">';
			echo '<span class="dashicons dashicons-admin-users" style="font-size: 14px; vertical-align: middle;"></span> ';
			echo esc_html( $consent['user_name'] );
			if ( ! empty( $consent['user_email'] ) ) {
				echo ' (' . esc_html( $consent['user_email'] ) . ')';
			}
			echo '</div>';
		}

		// Metadata (if present)
		if ( ! empty( $consent['metadata'] ) && is_array( $consent['metadata'] ) ) {
			echo '<details style="margin-top: 8px; font-size: 12px;">';
			echo '<summary style="cursor: pointer; color: #0073aa;">' . esc_html__( 'View Metadata', 'shahi-legalflowsuite' ) . '</summary>';
			echo '<pre style="background: #f5f5f5; padding: 8px; margin-top: 5px; border-radius: 3px; overflow-x: auto; font-size: 11px;">';
			echo esc_html( wp_json_encode( $consent['metadata'], JSON_PRETTY_PRINT ) );
			echo '</pre>';
			echo '</details>';
		}

		echo '</div>'; // content
		echo '</div>'; // .consent-entry
	}

	/**
	 * Render audit timeline section
	 *
	 * @param array $timeline Timeline entries
	 * @return void
	 */
	private function render_audit_timeline( array $timeline ): void {
		echo '<div class="postbox" style="margin-top: 20px;">';
		echo '<h2 class="hndle"><span>' . esc_html__( 'Audit Timeline', 'shahi-legalflowsuite' ) . '</span></h2>';
		echo '<div class="inside">';

		if ( empty( $timeline ) ) {
			echo '<p>' . esc_html__( 'No audit entries found.', 'shahi-legalflowsuite' ) . '</p>';
		} else {
			echo '<div class="timeline-container" style="position: relative; padding-left: 30px;">';

			foreach ( $timeline as $entry ) {
				$this->render_timeline_entry( $entry );
			}

			echo '</div>'; // .timeline-container
		}

		echo '</div>'; // .inside
		echo '</div>'; // .postbox
	}

	/**
	 * Render single timeline entry
	 *
	 * @param array $entry Timeline entry data
	 * @return void
	 */
	private function render_timeline_entry( array $entry ): void {
		$action_class = sanitize_html_class( $entry['action'] ?? '' );
		$icon         = $this->get_action_icon( $entry['action'] ?? '' );

		echo '<div class="timeline-entry" style="position: relative; padding: 15px 0; border-left: 2px solid #ddd;">';

		// Icon
		echo '<div style="position: absolute; left: -11px; top: 15px; width: 20px; height: 20px; border-radius: 50%; background: #fff; border: 2px solid ' . esc_attr( $this->get_action_color( $entry['action'] ?? '' ) ) . '; display: flex; align-items: center; justify-content: center;">';
		echo '<span class="dashicons ' . esc_attr( $icon ) . '" style="font-size: 12px; width: 12px; height: 12px; color: ' . esc_attr( $this->get_action_color( $entry['action'] ?? '' ) ) . ';"></span>';
		echo '</div>';

		// Content
		echo '<div style="margin-left: 20px;">';
		echo '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">';
		echo '<strong>' . esc_html( $entry['action_label'] ?? '' ) . '</strong>';
		echo '<span style="font-size: 12px; color: #666;">' . esc_html( $this->format_relative_time( $entry['created_at'] ?? '' ) ) . '</span>';
		echo '</div>';

		if ( ! empty( $entry['note'] ) ) {
			echo '<p style="margin: 5px 0; color: #444;">' . esc_html( $entry['note'] ) . '</p>';
		}

		if ( ! empty( $entry['actor_name'] ) ) {
			echo '<div style="font-size: 12px; color: #666;">';
			echo '<span class="dashicons dashicons-admin-users" style="font-size: 14px; vertical-align: middle;"></span> ';
			echo esc_html( $entry['actor_name'] );
			echo '</div>';
		}

		// Metadata (if present)
		if ( ! empty( $entry['metadata'] ) && is_array( $entry['metadata'] ) ) {
			echo '<details style="margin-top: 8px; font-size: 12px;">';
			echo '<summary style="cursor: pointer; color: #0073aa;">' . esc_html__( 'View Details', 'shahi-legalflowsuite' ) . '</summary>';
			echo '<pre style="background: #f5f5f5; padding: 8px; margin-top: 5px; border-radius: 3px; overflow-x: auto;">';
			echo esc_html( wp_json_encode( $entry['metadata'], JSON_PRETTY_PRINT ) );
			echo '</pre>';
			echo '</details>';
		}

		echo '</div>'; // content
		echo '</div>'; // .timeline-entry
	}

	/**
	 * Render SLA panel
	 *
	 * @param object $request Request data
	 * @return void
	 */
	private function render_sla_panel( $request ): void {
		echo '<div class="postbox">';
		echo '<h2 class="hndle"><span>' . esc_html__( 'SLA Status', 'shahi-legalflowsuite' ) . '</span></h2>';
		echo '<div class="inside">';

		$due_date = strtotime( $request->due_date ?? '' );
		$now      = current_time( 'timestamp' );
		$diff     = $due_date - $now;

		if ( ! empty( $request->completed_at ) ) {
			// Request completed
			$completed_date = strtotime( $request->completed_at );
			$completed_diff = $completed_date - strtotime( $request->submitted_at ?? '' );
			$completed_days = floor( $completed_diff / DAY_IN_SECONDS );

			echo '<div style="text-align: center; padding: 20px;">';
			echo '<div class="dashicons dashicons-yes-alt" style="font-size: 48px; width: 48px; height: 48px; color: #46b450;"></div>';
			echo '<h3 style="margin: 10px 0 5px;">' . esc_html__( 'Completed', 'shahi-legalflowsuite' ) . '</h3>';
			/* translators: %d: number of days taken to resolve */
			echo '<p style="color: #666;">' . sprintf( esc_html__( 'Resolved in %d days', 'shahi-legalflowsuite' ), absint( $completed_days ) ) . '</p>';
			echo '</div>';
		} elseif ( $diff < 0 ) {
			// Overdue
			$overdue_days = abs( floor( $diff / DAY_IN_SECONDS ) );
			echo '<div style="text-align: center; padding: 20px; background: #fff3cd; border-radius: 4px;">';
			echo '<div class="dashicons dashicons-warning" style="font-size: 48px; width: 48px; height: 48px; color: #dc3232;"></div>';
			echo '<h3 style="margin: 10px 0 5px; color: #dc3232;">' . esc_html__( 'Overdue', 'shahi-legalflowsuite' ) . '</h3>';
			/* translators: %d: number of days past due date */
			echo '<p style="color: #666;">' . sprintf( esc_html__( '%d days past due date', 'shahi-legalflowsuite' ), absint( $overdue_days ) ) . '</p>';
			echo '</div>';
		} else {
			// Countdown
			$days_remaining = ceil( $diff / DAY_IN_SECONDS );
			$color          = $days_remaining <= 7 ? '#f18500' : '#46b450';

			echo '<div style="text-align: center; padding: 20px;">';
			echo '<div class="dashicons dashicons-clock" style="font-size: 48px; width: 48px; height: 48px; color: ' . esc_attr( $color ) . ';"></div>';			/* translators: %d: number of days remaining until due date */			echo '<h3 style="margin: 10px 0 5px; color: ' . esc_attr( $color ) . ';">' . sprintf( esc_html__( '%d Days', 'shahi-legalflowsuite' ), absint( $days_remaining ) ) . '</h3>';
			echo '<p style="color: #666;">' . esc_html__( 'Until due date', 'shahi-legalflowsuite' ) . '</p>';
			echo '<p style="font-size: 12px; color: #999;">' . esc_html( date_i18n( get_option( 'date_format' ), $due_date ) ) . '</p>';
			echo '</div>';
		}

		echo '</div>'; // .inside
		echo '</div>'; // .postbox
	}

	/**
	 * Render actions panel
	 *
	 * @param object $request Request data
	 * @return void
	 */
	private function render_actions_panel( $request ): void {
		echo '<div class="postbox" style="margin-top: 20px;">';
		echo '<h2 class="hndle"><span>' . esc_html__( 'Quick Actions', 'shahi-legalflowsuite' ) . '</span></h2>';
		echo '<div class="inside">';

		// Status change
		echo '<div style="margin-bottom: 15px;">';
		echo '<label style="display: block; margin-bottom: 5px; font-weight: 600;">' . esc_html__( 'Change Status', 'shahi-legalflowsuite' ) . '</label>';
		echo '<select id="dsr-status-change" style="width: 100%;">';
		$statuses = array(
			'pending_verification' => __( 'Pending Verification', 'shahi-legalflowsuite' ),
			'verified'             => __( 'Verified', 'shahi-legalflowsuite' ),
			'in_progress'          => __( 'In Progress', 'shahi-legalflowsuite' ),
			'completed'            => __( 'Completed', 'shahi-legalflowsuite' ),
			'rejected'             => __( 'Rejected', 'shahi-legalflowsuite' ),
		);
		foreach ( $statuses as $value => $label ) {
			$selected = ( $request->status ?? '' ) === $value ? ' selected' : '';
			echo '<option value="' . esc_attr( $value ) . '"' . esc_attr( $selected ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
		echo '<button type="button" class="button button-primary" style="margin-top: 5px; width: 100%;" onclick="updateDSRStatus(' . absint( $request->id ?? 0 ) . ')">' . esc_html__( 'Update Status', 'shahi-legalflowsuite' ) . '</button>';
		echo '</div>';

		// Add note
		echo '<div style="margin-bottom: 15px;">';
		echo '<label style="display: block; margin-bottom: 5px; font-weight: 600;">' . esc_html__( 'Add Note', 'shahi-legalflowsuite' ) . '</label>';
		echo '<textarea id="dsr-note" rows="3" style="width: 100%;"></textarea>';
		echo '<button type="button" class="button" style="margin-top: 5px; width: 100%;" onclick="addDSRNote(' . absint( $request->id ?? 0 ) . ')">' . esc_html__( 'Add Note', 'shahi-legalflowsuite' ) . '</button>';
		echo '</div>';

		// Export data
		if ( in_array( $request->status ?? '', array( 'verified', 'in_progress', 'completed' ), true ) ) {
			echo '<div style="margin-bottom: 15px;">';
			echo '<label style="display: block; margin-bottom: 5px; font-weight: 600;">' . esc_html__( 'Export Data', 'shahi-legalflowsuite' ) . '</label>';
			echo '<button type="button" class="button" style="width: 100%; margin-bottom: 5px;" onclick="generateExport(' . absint( $request->id ?? 0 ) . ', \'json\')">' . esc_html__( 'Generate JSON Export', 'shahi-legalflowsuite' ) . '</button>';
			echo '<button type="button" class="button" style="width: 100%;" onclick="generateExport(' . absint( $request->id ?? 0 ) . ', \'pdf\')">' . esc_html__( 'Generate PDF Export', 'shahi-legalflowsuite' ) . '</button>';
			echo '</div>';
		}

		// Delete request (danger zone)
		echo '<div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;">';
		echo '<button type="button" class="button button-link-delete" style="width: 100%;" onclick="deleteDSRRequest(' . absint( $request->id ?? 0 ) . ')">' . esc_html__( 'Delete Request', 'shahi-legalflowsuite' ) . '</button>';
		echo '</div>';

		echo '</div>'; // .inside
		echo '</div>'; // .postbox

		// JavaScript for AJAX actions
		$this->render_inline_script( $request );
	}

	/**
	 * Render inline JavaScript for AJAX actions
	 *
	 * @param object $request Request data
	 * @return void
	 */
	private function render_inline_script( $request ): void {
		?>
		<script>
		function updateDSRStatus(requestId) {
			const status = document.getElementById('dsr-status-change').value;
			if (!status) return;

			if (!confirm('<?php echo esc_js( __( 'Are you sure you want to update the status?', 'shahi-legalflowsuite' ) ); ?>')) {
				return;
			}

			fetch('<?php echo esc_url( rest_url( 'slos/v1/dsr/' ) ); ?>' + requestId + '/status', {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
				},
				body: JSON.stringify({ status: status })
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert('<?php echo esc_js( __( 'Status updated successfully!', 'shahi-legalflowsuite' ) ); ?>');
					location.reload();
				} else {
					alert('<?php echo esc_js( __( 'Failed to update status.', 'shahi-legalflowsuite' ) ); ?>');
				}
			});
		}

		function addDSRNote(requestId) {
			const note = document.getElementById('dsr-note').value;
			if (!note) return;

			// Note: This would need a dedicated endpoint or we can simulate by status update with note
			alert('<?php echo esc_js( __( 'Note functionality requires REST API endpoint.', 'shahi-legalflowsuite' ) ); ?>');
		}

		function generateExport(requestId, format) {
			alert('<?php echo esc_js( __( 'Export functionality already implemented in DSR Export Service.', 'shahi-legalflowsuite' ) ); ?>');
		}

		function deleteDSRRequest(requestId) {
			if (!confirm('<?php echo esc_js( __( 'Are you sure you want to delete this request? This action cannot be undone.', 'shahi-legalflowsuite' ) ); ?>')) {
				return;
			}

			fetch('<?php echo esc_url( rest_url( 'slos/v1/dsr/' ) ); ?>' + requestId, {
				method: 'DELETE',
				headers: {
					'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
				}
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					alert('<?php echo esc_js( __( 'Request deleted successfully!', 'shahi-legalflowsuite' ) ); ?>');
					window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=' . MenuManager::MENU_SLUG . '-dsr-requests' ) ); ?>';
				} else {
					alert('<?php echo esc_js( __( 'Failed to delete request.', 'shahi-legalflowsuite' ) ); ?>');
				}
			});
		}
		</script>
		<?php
	}

	/**
	 * Get status badge HTML
	 *
	 * @param string $status Status value
	 * @return string Badge HTML
	 */
	private function get_status_badge( string $status ): string {
		$colors = array(
			'pending_verification' => '#f18500',
			'verified'             => '#0073aa',
			'in_progress'          => '#0073aa',
			'completed'            => '#46b450',
			'rejected'             => '#dc3232',
		);

		$color = $colors[ $status ] ?? '#666';
		$label = ucwords( str_replace( '_', ' ', $status ) );

		return '<span style="display: inline-block; padding: 4px 10px; background: ' . esc_attr( $color ) . '; color: white; border-radius: 3px; font-size: 12px; font-weight: 600;">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Get action icon class
	 *
	 * @param string $action Action type
	 * @return string Dashicon class
	 */
	private function get_action_icon( string $action ): string {
		$icons = array(
			'submit'            => 'dashicons-edit',
			'verify'            => 'dashicons-yes',
			'status_change'     => 'dashicons-update',
			'assign'            => 'dashicons-admin-users',
			'note_added'        => 'dashicons-admin-comments',
			'export_generated'  => 'dashicons-download',
			'export_downloaded' => 'dashicons-cloud-download',
			'erasure_executed'  => 'dashicons-trash',
			'erasure_preview'   => 'dashicons-visibility',
		);

		return $icons[ $action ] ?? 'dashicons-marker';
	}

	/**
	 * Get action color
	 *
	 * @param string $action Action type
	 * @return string Color hex code
	 */
	private function get_action_color( string $action ): string {
		$colors = array(
			'submit'            => '#0073aa',
			'verify'            => '#46b450',
			'status_change'     => '#f18500',
			'assign'            => '#0073aa',
			'note_added'        => '#666',
			'export_generated'  => '#0073aa',
			'export_downloaded' => '#0073aa',
			'erasure_executed'  => '#dc3232',
			'erasure_preview'   => '#666',
		);

		return $colors[ $action ] ?? '#666';
	}

	/**
	 * Format relative time (e.g., "2 hours ago")
	 *
	 * @param string $datetime Datetime string
	 * @return string Formatted relative time
	 */
	private function format_relative_time( string $datetime ): string {
		$timestamp = strtotime( $datetime );
		$diff      = current_time( 'timestamp' ) - $timestamp;

		if ( $diff < MINUTE_IN_SECONDS ) {
			return __( 'Just now', 'shahi-legalflowsuite' );
		} elseif ( $diff < HOUR_IN_SECONDS ) {
			$minutes = floor( $diff / MINUTE_IN_SECONDS );
			/* translators: %d: number of minutes */
			return sprintf( _n( '%d minute ago', '%d minutes ago', $minutes, 'shahi-legalflowsuite' ), $minutes );
		} elseif ( $diff < DAY_IN_SECONDS ) {
			$hours = floor( $diff / HOUR_IN_SECONDS );
			/* translators: %d: number of hours */
			return sprintf( _n( '%d hour ago', '%d hours ago', $hours, 'shahi-legalflowsuite' ), $hours );
		} elseif ( $diff < WEEK_IN_SECONDS ) {
			$days = floor( $diff / DAY_IN_SECONDS );
			/* translators: %d: number of days */
			return sprintf( _n( '%d day ago', '%d days ago', $days, 'shahi-legalflowsuite' ), $days );
		} else {
			return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
		}
	}
}
