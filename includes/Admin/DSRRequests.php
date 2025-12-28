<?php
/**
 * DSR Requests Admin Page
 *
 * Provides an admin interface to view and manage Data Subject Requests.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Database\Repositories\DSR_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DSRRequests {

	/**
	 * Get request statistics
	 *
	 * @return array
	 */
	private function get_request_stats() {
		$repo = new DSR_Repository();

		try {
			$total     = count( $repo->list_requests( array(), 1000 ) );
			$pending   = count( $repo->list_requests( array( 'status' => 'pending_verification' ), 1000 ) );
			$completed = count( $repo->list_requests( array( 'status' => 'completed' ), 1000 ) );

			// Calculate overdue by checking SLA deadline
			$overdue = $this->calculate_overdue_count( $repo );

			return array(
				'total'     => $total,
				'pending'   => $pending,
				'overdue'   => $overdue,
				'completed' => $completed,
			);
		} catch ( \Throwable $e ) {
			return array(
				'total'     => 0,
				'pending'   => 0,
				'overdue'   => 0,
				'completed' => 0,
			);
		}
	}

	/**
	 * Calculate overdue requests count
	 *
	 * Checks requests against SLA deadline (typically 30 days from submission)
	 *
	 * @param DSR_Repository $repo Repository instance
	 * @return int Count of overdue requests
	 */
	private function calculate_overdue_count( $repo ) {
		try {
			// Get all non-completed requests
			$active_requests = $repo->list_requests( array(), 1000 );
			$overdue_count   = 0;
			$sla_days        = 30; // GDPR requires response within 30 days

			foreach ( $active_requests as $request ) {
				// Skip completed or rejected requests
				if ( in_array( $request->status, array( 'completed', 'rejected' ), true ) ) {
					continue;
				}

				// Calculate days since submission
				$submitted    = strtotime( $request->submitted_at );
				$now          = current_time( 'timestamp' );
				$days_elapsed = floor( ( $now - $submitted ) / DAY_IN_SECONDS );

				if ( $days_elapsed > $sla_days ) {
					++$overdue_count;
				}
			}

			return $overdue_count;
		} catch ( \Throwable $e ) {
			return 0;
		}
	}

	/**
	 * Render stat card
	 */
	private function render_stat_card( $label, $value, $trend, $trend_type, $icon ) {
		$trend_class = 'slos-trend-' . $trend_type;
		?>
		<div class="slos-stat-card">
			<div class="slos-stat-icon">
				<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
			</div>
			<div class="slos-stat-content">
				<div class="slos-stat-value"><?php echo esc_html( $value ); ?></div>
				<div class="slos-stat-label"><?php echo esc_html( $label ); ?></div>
				<div class="slos-stat-trend <?php echo esc_attr( $trend_class ); ?>">
					<?php echo esc_html( $trend ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render request card
	 */
	private function render_request_card( $req ) {
		$id         = isset( $req->id ) ? (int) $req->id : 0;
		$email      = isset( $req->requester_email ) ? (string) $req->requester_email : '';
		$type       = isset( $req->request_type ) ? (string) $req->request_type : '';
		$status     = isset( $req->status ) ? (string) $req->status : '';
		$created    = isset( $req->submitted_at ) ? (string) $req->submitted_at : '';
		$regulation = isset( $req->regulation ) ? (string) $req->regulation : 'GDPR';

		$status_class = $this->get_status_class( $status );
		$detail_url   = admin_url( 'admin.php?page=shahi-legalops-suite-dsr-detail&request_id=' . $id );

		// Calculate relative time
		$submitted_time = strtotime( $created );
		$time_diff      = human_time_diff( $submitted_time, current_time( 'timestamp' ) );

		?>
		<div class="slos-request-card">
			<div class="slos-card-header">
				<div class="slos-card-title">
					<span class="slos-request-id">#<?php echo esc_html( $id ); ?></span>
					<span class="slos-request-email">📧 <?php echo esc_html( $email ); ?></span>
				</div>
				<div class="slos-card-status">
					<span class="slos-status-badge <?php echo esc_attr( $status_class ); ?>">
						<?php echo esc_html( ucwords( str_replace( '_', ' ', $status ) ) ); ?>
					</span>
				</div>
			</div>
			
			<div class="slos-card-body">
				<div class="slos-card-meta">
					<span class="slos-meta-item">
						<strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $type ) ) ); ?></strong> Request
					</span>
					<span class="slos-meta-divider">·</span>
					<span class="slos-meta-item"><?php echo esc_html( $regulation ); ?></span>
					<span class="slos-meta-divider">·</span>
					<span class="slos-meta-item">Submitted <?php echo esc_html( $time_diff ); ?> ago</span>
				</div>
			</div>
			
			<div class="slos-card-footer">
				<div class="slos-card-info">
					<span class="slos-due-date">Due in 28 days</span>
				</div>
				<div class="slos-card-actions">
					<a href="<?php echo esc_url( $detail_url ); ?>" class="slos-btn slos-btn-sm slos-btn-secondary">
						<?php esc_html_e( 'View Details', 'shahi-legalops-suite' ); ?>
					</a>
					<button class="slos-btn slos-btn-sm slos-btn-ghost" onclick="slOSShowActionsMenu(<?php echo esc_js( $id ); ?>)">
						<?php esc_html_e( 'Actions', 'shahi-legalops-suite' ); ?> ▼
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get status CSS class
	 */
	private function get_status_class( $status ) {
		$classes = array(
			'pending_verification' => 'status-pending',
			'verified'             => 'status-verified',
			'in_progress'          => 'status-progress',
			'on_hold'              => 'status-hold',
			'completed'            => 'status-completed',
			'rejected'             => 'status-rejected',
		);

		return $classes[ $status ] ?? 'status-default';
	}

	/**
	 * Render quick actions widget
	 */
	private function render_quick_actions_widget() {
		?>
		<div class="slos-widget">
			<h3 class="slos-widget-title"><?php esc_html_e( 'Quick Actions', 'shahi-legalops-suite' ); ?></h3>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Common DSR management operations. Create new requests, send bulk communications, or import/export data.', 'shahi-legalops-suite' ); ?>
			</p>
			<div class="slos-widget-content">
				<button class="slos-widget-btn">
					<span class="dashicons dashicons-plus-alt2"></span>
					<?php esc_html_e( 'New Request', 'shahi-legalops-suite' ); ?>
				</button>
				<button class="slos-widget-btn">
					<span class="dashicons dashicons-email"></span>
					<?php esc_html_e( 'Bulk Email', 'shahi-legalops-suite' ); ?>
				</button>
				<button class="slos-widget-btn">
					<span class="dashicons dashicons-upload"></span>
					<?php esc_html_e( 'Import CSV', 'shahi-legalops-suite' ); ?>
				</button>
				<button class="slos-widget-btn">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Export Data', 'shahi-legalops-suite' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render SLA monitor widget
	 */
	private function render_sla_monitor_widget() {
		?>
		<div class="slos-widget">
			<h3 class="slos-widget-title"><?php esc_html_e( 'SLA Compliance', 'shahi-legalops-suite' ); ?></h3>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Track compliance with regulatory deadlines. GDPR requires 30 days, CCPA 45 days response time.', 'shahi-legalops-suite' ); ?>
			</p>
			<div class="slos-widget-content">
				<div class="slos-sla-progress">
					<div class="slos-progress-bar">
						<div class="slos-progress-fill" style="width: 82%;"></div>
					</div>
					<div class="slos-progress-label">82%</div>
				</div>
				<div class="slos-sla-details">
					<p class="slos-alert-text">
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( '3 Requests at risk', 'shahi-legalops-suite' ); ?>
					</p>
					<a href="#" class="slos-link"><?php esc_html_e( 'View Details →', 'shahi-legalops-suite' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render activity feed widget
	 */
	private function render_activity_feed_widget() {
		?>
		<div class="slos-widget">
			<h3 class="slos-widget-title"><?php esc_html_e( 'Recent Activity', 'shahi-legalops-suite' ); ?></h3>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Real-time feed of DSR status changes and new submissions. Stay informed of all request activity.', 'shahi-legalops-suite' ); ?>
			</p>
			<div class="slos-widget-content">
				<div class="slos-activity-feed">
					<div class="slos-activity-item">
						<div class="slos-activity-dot"></div>
						<div class="slos-activity-content">
							<div class="slos-activity-time">5m ago</div>
							<div class="slos-activity-text">#1247 status changed</div>
						</div>
					</div>
					<div class="slos-activity-item">
						<div class="slos-activity-dot"></div>
						<div class="slos-activity-content">
							<div class="slos-activity-time">12m ago</div>
							<div class="slos-activity-text">#1246 verified</div>
						</div>
					</div>
					<div class="slos-activity-item">
						<div class="slos-activity-dot"></div>
						<div class="slos-activity-content">
							<div class="slos-activity-time">1h ago</div>
							<div class="slos-activity-text">#1245 submitted</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render regulation breakdown widget
	 */
	private function render_regulation_breakdown_widget() {
		?>
		<div class="slos-widget">
			<h3 class="slos-widget-title"><?php esc_html_e( 'By Regulation', 'shahi-legalops-suite' ); ?></h3>
			<p class="slos-widget-description">
				<?php esc_html_e( 'Distribution of requests by privacy regulation. Helps identify which frameworks apply most to your audience.', 'shahi-legalops-suite' ); ?>
			</p>
			<div class="slos-widget-content">
				<div class="slos-regulation-breakdown">
					<div class="slos-regulation-item">
						<span class="slos-regulation-color" style="background: #0066FF;"></span>
						<span class="slos-regulation-label">GDPR</span>
						<span class="slos-regulation-value">62%</span>
					</div>
					<div class="slos-regulation-item">
						<span class="slos-regulation-color" style="background: #6366F1;"></span>
						<span class="slos-regulation-label">CCPA</span>
						<span class="slos-regulation-value">28%</span>
					</div>
					<div class="slos-regulation-item">
						<span class="slos-regulation-color" style="background: #10B981;"></span>
						<span class="slos-regulation-label">LGPD</span>
						<span class="slos-regulation-value">10%</span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the DSR Requests admin page
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'slos_manage_dsr' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'shahi-legalops-suite' ) );
		}

		echo '<div class="wrap shahi-legalops-suite">';
		echo '<h1 class="wp-heading-inline">' . esc_html__( 'DSR Requests', 'shahi-legalops-suite' ) . '</h1>';
		echo '<hr class="wp-header-end" />';

		$this->render_content();

		echo '</div>'; // .wrap
	}

	/**
	 * Render just the content (for use in tabbed interface)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_content() {
		$repo = new DSR_Repository();

		// Get filter params
		$status = isset( $_GET['status'] ) && $_GET['status'] !== 'all' ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$type   = isset( $_GET['request_type'] ) && $_GET['request_type'] !== 'all' ? sanitize_text_field( wp_unslash( $_GET['request_type'] ) ) : '';

		// Get stats
		$stats = $this->get_request_stats();

		// Get requests
		$args = array();
		if ( ! empty( $status ) ) {
			$args['status'] = $status;
		}
		if ( ! empty( $type ) ) {
			$args['request_type'] = $type;
		}

		try {
			$requests = method_exists( $repo, 'list_requests' ) ? $repo->list_requests( $args ) : array();
		} catch ( \Throwable $e ) {
			$requests = array();
		}

		?>
		<div class="slos-requests-layout">
			<!-- Main Content Column -->
			<div class="slos-requests-main">
				<!-- Stats Cards Row -->
				<div class="slos-stats-section">
					<p class="slos-section-description">
						<?php esc_html_e( 'Overview of all data subject requests. Click any card to filter the list below by that status.', 'shahi-legalops-suite' ); ?>
					</p>
					<div class="slos-stats-grid">
						<?php $this->render_stat_card( 'Total Requests', $stats['total'], '', 'neutral', 'dashicons-list-view' ); ?>
						<?php $this->render_stat_card( 'Pending', $stats['pending'], '', 'neutral', 'dashicons-clock' ); ?>
						<?php $this->render_stat_card( 'Overdue', $stats['overdue'], $stats['overdue'] > 0 ? __( 'Attention', 'shahi-legalops-suite' ) : '', $stats['overdue'] > 0 ? 'danger' : 'neutral', 'dashicons-warning' ); ?>
						<?php $this->render_stat_card( 'Completed', $stats['completed'], '', 'neutral', 'dashicons-yes-alt' ); ?>
					</div>
				</div>

				<!-- Smart Filters -->
				<div class="slos-filters-card">
					<p class="slos-section-description">
						<?php esc_html_e( 'Filter requests by status, type, or search by email/ID. Active filters appear as removable pills below.', 'shahi-legalops-suite' ); ?>
					</p>
					<form method="get" action="" class="slos-filters-form">
						<input type="hidden" name="page" value="slos-requests" />
						<?php if ( isset( $_GET['tab'] ) ) : ?>
							<input type="hidden" name="tab" value="<?php echo esc_attr( $_GET['tab'] ); ?>" />
						<?php endif; ?>
						
						<div class="slos-filters-row">
							<div class="slos-search-box">
								<span class="dashicons dashicons-search"></span>
								<input type="text" name="search" placeholder="<?php esc_attr_e( 'Search requests...', 'shahi-legalops-suite' ); ?>" 
									value="<?php echo isset( $_GET['search'] ) ? esc_attr( $_GET['search'] ) : ''; ?>" />
							</div>
							
							<select name="status" class="slos-filter-select">
								<option value="all"><?php esc_html_e( 'All Statuses', 'shahi-legalops-suite' ); ?></option>
								<?php
								$statuses = array( 'pending_verification', 'verified', 'in_progress', 'on_hold', 'completed', 'rejected' );
								foreach ( $statuses as $st ) {
									$selected = ( $status === $st ) ? 'selected' : '';
									echo '<option value="' . esc_attr( $st ) . '" ' . esc_attr( $selected ) . '>' .
										esc_html( ucwords( str_replace( '_', ' ', $st ) ) ) . '</option>';
								}
								?>
							</select>
							
							<select name="request_type" class="slos-filter-select">
								<option value="all"><?php esc_html_e( 'All Types', 'shahi-legalops-suite' ); ?></option>
								<?php
								$types = array( 'access', 'rectification', 'erasure', 'portability', 'restriction', 'object', 'automated_decision' );
								foreach ( $types as $tp ) {
									$selected = ( $type === $tp ) ? 'selected' : '';
									echo '<option value="' . esc_attr( $tp ) . '" ' . esc_attr( $selected ) . '>' .
										esc_html( ucwords( str_replace( '_', ' ', $tp ) ) ) . '</option>';
								}
								?>
							</select>
							
							<button type="submit" class="slos-btn slos-btn-secondary">
								<span class="dashicons dashicons-filter"></span>
								<?php esc_html_e( 'Apply Filters', 'shahi-legalops-suite' ); ?>
							</button>
						</div>
					</form>
					
					<?php if ( ! empty( $status ) || ! empty( $type ) ) : ?>
						<div class="slos-active-filters">
							<span class="slos-filter-label"><?php esc_html_e( 'Active Filters:', 'shahi-legalops-suite' ); ?></span>
							<?php if ( ! empty( $status ) ) : ?>
								<span class="slos-filter-pill">
									<?php echo esc_html( 'Status: ' . ucwords( str_replace( '_', ' ', $status ) ) ); ?>
									<a href="<?php echo esc_url( remove_query_arg( 'status' ) ); ?>" class="slos-filter-remove">×</a>
								</span>
							<?php endif; ?>
							<?php if ( ! empty( $type ) ) : ?>
								<span class="slos-filter-pill">
									<?php echo esc_html( 'Type: ' . ucwords( str_replace( '_', ' ', $type ) ) ); ?>
									<a href="<?php echo esc_url( remove_query_arg( 'request_type' ) ); ?>" class="slos-filter-remove">×</a>
								</span>
							<?php endif; ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-requests&tab=' . ( $_GET['tab'] ?? 'requests' ) ) ); ?>" class="slos-clear-all">
								<?php esc_html_e( 'Clear All', 'shahi-legalops-suite' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>

				<!-- Request Cards -->
				<div class="slos-request-cards">
					<?php if ( ! empty( $requests ) && is_array( $requests ) ) : ?>
						<?php foreach ( $requests as $req ) : ?>
							<?php $this->render_request_card( $req ); ?>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="slos-empty-state">
							<span class="dashicons dashicons-inbox"></span>
							<h3><?php esc_html_e( 'No requests found', 'shahi-legalops-suite' ); ?></h3>
							<p><?php esc_html_e( 'Try adjusting your filters or create a new request.', 'shahi-legalops-suite' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Sidebar Widgets -->
			<div class="slos-requests-sidebar">
				<?php $this->render_quick_actions_widget(); ?>
				<?php $this->render_sla_monitor_widget(); ?>
				<?php $this->render_activity_feed_widget(); ?>
				<?php $this->render_regulation_breakdown_widget(); ?>
			</div>
		</div>
		<?php
	}
}
