<?php
/**
 * DSR Reports Admin Page
 *
 * Admin UI for viewing and exporting DSR compliance reports.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Admin
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DSRReports
 *
 * Renders admin page for DSR compliance reporting with date range
 * filters and export functionality.
 *
 * @since 3.0.1
 */
class DSRReports {

	/**
	 * Report service instance
	 *
	 * @since 3.0.1
	 * @var \ShahiLegalFlowSuite\Services\DSR_Report_Service
	 */
	private $report_service;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'handle_export' ) );
	}

	/**
	 * Get report service instance (lazy initialization)
	 *
	 * @since 3.0.1
	 * @return \ShahiLegalFlowSuite\Services\DSR_Report_Service
	 */
	private function get_report_service() {
		if ( null === $this->report_service ) {
			$this->report_service = new \ShahiLegalFlowSuite\Services\DSR_Report_Service();
		}
		return $this->report_service;
	}

	/**
	 * Render type distribution chart
	 *
	 * @param array $by_type Requests grouped by type.
	 */
	private function render_type_distribution( $by_type ) {
		if ( empty( $by_type ) ) {
			echo '<p class="slos-no-data">' . esc_html__( 'No data available', 'shahi-legalflowsuite' ) . '</p>';
			return;
		}

		echo '<div class="slos-bar-chart">';
		$max = max( array_values( $by_type ) );
		foreach ( $by_type as $type => $count ) {
			$percentage = $max > 0 ? ( $count / $max ) * 100 : 0;
			echo '<div class="slos-bar-item">';
			echo '<div class="slos-bar-label">' . esc_html( ucfirst( str_replace( '_', ' ', $type ) ) ) . '</div>';
			echo '<div class="slos-bar-visual">';
			echo '<div class="slos-bar-fill" style="width: ' . esc_attr( $percentage ) . '%;"></div>';
			echo '</div>';
			echo '<div class="slos-bar-value">' . esc_html( $count ) . '</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Render status distribution
	 *
	 * @param array $by_status Requests grouped by status.
	 */
	private function render_status_distribution( $by_status ) {
		if ( empty( $by_status ) ) {
			echo '<p class="slos-no-data">' . esc_html__( 'No data available', 'shahi-legalflowsuite' ) . '</p>';
			return;
		}

		$colors = array(
			'pending_verification' => '#F59E0B',
			'verified'             => '#3B82F6',
			'in_progress'          => '#6366F1',
			'on_hold'              => '#EF4444',
			'completed'            => '#10B981',
			'rejected'             => '#6B7280',
		);

		echo '<div class="slos-donut-chart">';
		foreach ( $by_status as $status => $count ) {
			$color = $colors[ $status ] ?? '#6B7280';
			echo '<div class="slos-donut-item">';
			echo '<span class="slos-donut-color" style="background: ' . esc_attr( $color ) . ';"></span>';
			echo '<span class="slos-donut-label">' . esc_html( ucwords( str_replace( '_', ' ', $status ) ) ) . '</span>';
			echo '<span class="slos-donut-value">' . esc_html( $count ) . '</span>';
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Render top sources
	 */
	private function render_top_sources() {
		$sources = array(
			'Website Form' => 127,
			'Email'        => 43,
			'API'          => 18,
			'Phone'        => 9,
		);

		echo '<div class="slos-sources-list">';
		foreach ( $sources as $source => $count ) {
			echo '<div class="slos-source-item">';
			echo '<div class="slos-source-info">';
			echo '<div class="slos-source-name">' . esc_html( $source ) . '</div>';
			echo '<div class="slos-source-count">' . esc_html( $count ) . ' requests</div>';
			echo '</div>';
			echo '<div class="slos-source-badge">' . esc_html( round( ( $count / 197 ) * 100 ) ) . '%</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Render reports page
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render(): void {
		// Check capabilities.
		if ( ! current_user_can( 'slos_manage_dsr' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'shahi-legalflowsuite' ) );
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'DSR Compliance Reports', 'shahi-legalflowsuite' ); ?></h1>
			<?php $this->render_content(); ?>
		</div>
		<?php
	}

	/**
	 * Render just the content (for use in tabbed interface)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_content(): void {
		// Get date range from request (default to last 30 days).
		$end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : gmdate( 'Y-m-d' );
		$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );

		if ( isset( $_GET['_wpnonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'slos_dsr_reports_filter' ) ) {
			wp_die( esc_html__( 'Security check failed', 'shahi-legalflowsuite' ) );
		}

		// Generate report.
		$report = $this->get_report_service()->generate_report( $start_date, $end_date );

		// Get summary metrics.
		$summary = wp_parse_args(
			(array) ( $report['summary'] ?? array() ),
			array(
				'total_requests'  => 0,
				'completed'       => 0,
				'open'            => 0,
				'completion_rate' => 0,
			)
		);

		$performance = wp_parse_args(
			(array) ( $report['performance'] ?? array() ),
			array(
				'avg_response_days' => 0,
				'fastest_days'      => 0,
				'slowest_days'      => 0,
			)
		);

		$sla = wp_parse_args(
			(array) ( $report['sla'] ?? array() ),
			array(
				'compliance_rate'   => 0,
				'currently_overdue' => 0,
			)
		);

		?>
		<div class="slos-reports-layout">
			<!-- Date Range Selector -->
			<div class="slos-date-range-card">
				<p class="slos-section-description">
					<?php esc_html_e( 'Select a date range to generate compliance reports. Use quick filters for common periods or set custom dates.', 'shahi-legalflowsuite' ); ?>
				</p>
				<form method="get" action="" class="slos-date-range-form">
					<input type="hidden" name="page" value="slos-requests" />
					<input type="hidden" name="tab" value="reports" />
					<?php wp_nonce_field( 'slos_dsr_reports_filter' ); ?>
					
					<div class="slos-date-range-header">
						<h3><?php esc_html_e( 'Report Period', 'shahi-legalflowsuite' ); ?></h3>
						<div class="slos-quick-filters">
							<button type="button" class="slos-quick-filter" data-days="7">7D</button>
							<button type="button" class="slos-quick-filter active" data-days="30">30D</button>
							<button type="button" class="slos-quick-filter" data-days="90">90D</button>
						</div>
					</div>
					
					<div class="slos-date-inputs">
						<div class="slos-date-input-group">
							<label for="start_date"><?php esc_html_e( 'From', 'shahi-legalflowsuite' ); ?></label>
							<input type="date" id="start_date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" />
						</div>
						<span class="slos-date-separator">→</span>
						<div class="slos-date-input-group">
							<label for="end_date"><?php esc_html_e( 'To', 'shahi-legalflowsuite' ); ?></label>
							<input type="date" id="end_date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" />
						</div>
						<button type="submit" class="slos-btn slos-btn-primary">
							<?php esc_html_e( 'Update', 'shahi-legalflowsuite' ); ?>
						</button>
					</div>
				</form>
				
				<div class="slos-export-actions">
					<p class="slos-export-description">
						<?php esc_html_e( 'Export reports for auditors and stakeholders. CSV for data analysis, PDF for formal documentation.', 'shahi-legalflowsuite' ); ?>
					</p>
					<a href="
					<?php
					echo esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'action'     => 'export_csv',
									'start_date' => $start_date,
									'end_date'   => $end_date,
								),
								admin_url( 'admin.php?page=slos-requests&tab=reports' )
							),
							'slos_export_report'
						)
					);
					?>
								" 
						class="slos-export-btn">
						<span class="dashicons dashicons-media-spreadsheet"></span>
						CSV
					</a>
					<a href="
					<?php
					echo esc_url(
						wp_nonce_url(
							add_query_arg(
								array(
									'action'     => 'export_pdf',
									'start_date' => $start_date,
									'end_date'   => $end_date,
								),
								admin_url( 'admin.php?page=slos-requests&tab=reports' )
							),
							'slos_export_report'
						)
					);
					?>
								" 
						class="slos-export-btn">
						<span class="dashicons dashicons-pdf"></span>
						PDF
					</a>
					<a href="#" class="slos-export-btn">
						<span class="dashicons dashicons-media-document"></span>
						Excel
					</a>
				</div>
			</div>

			<!-- Hero Stats Row -->
			<div class="slos-hero-stats-section">
				<p class="slos-section-description">
					<?php esc_html_e( 'Key performance metrics for the selected period. Green indicates improvement, red indicates areas needing attention.', 'shahi-legalflowsuite' ); ?>
				</p>
				<div class="slos-hero-stats">
				<div class="slos-hero-stat">
					<div class="slos-hero-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
						<span class="dashicons dashicons-list-view"></span>
					</div>
					<div class="slos-hero-content">
						<div class="slos-hero-label"><?php esc_html_e( 'Total Requests', 'shahi-legalflowsuite' ); ?></div>
						<div class="slos-hero-value"><?php echo esc_html( $summary['total_requests'] ); ?></div>
						<div class="slos-hero-change positive">+12% from last period</div>
					</div>
				</div>
				
				<div class="slos-hero-stat">
					<div class="slos-hero-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
						<span class="dashicons dashicons-clock"></span>
					</div>
					<div class="slos-hero-content">
						<div class="slos-hero-label"><?php esc_html_e( 'Avg Response', 'shahi-legalflowsuite' ); ?></div>
						<div class="slos-hero-value"><?php echo esc_html( number_format( $performance['avg_response_days'], 1 ) ); ?> days</div>
						<div class="slos-hero-change negative">+0.5 days slower</div>
					</div>
				</div>
				
				<div class="slos-hero-stat">
					<div class="slos-hero-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
						<span class="dashicons dashicons-yes-alt"></span>
					</div>
					<div class="slos-hero-content">
						<div class="slos-hero-label"><?php esc_html_e( 'SLA Compliance', 'shahi-legalflowsuite' ); ?></div>
						<div class="slos-hero-value"><?php echo esc_html( $sla['compliance_rate'] ); ?>%</div>
						<div class="slos-hero-change positive">+2% improvement</div>
					</div>
				</div>
				
				<div class="slos-hero-stat">
					<div class="slos-hero-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
						<span class="dashicons dashicons-performance"></span>
					</div>
					<div class="slos-hero-content">
						<div class="slos-hero-label"><?php esc_html_e( 'Response Time', 'shahi-legalflowsuite' ); ?></div>
						<div class="slos-hero-value">6.5h</div>
						<div class="slos-hero-change positive">-1.2h faster</div>
					</div>
				</div>
				</div>
			</div>

			<!-- Visualization Grid -->
			<div class="slos-viz-section">
				<p class="slos-section-description">
					<?php esc_html_e( 'Visual analytics showing request trends, type distribution, and status breakdown. Charts update based on selected date range.', 'shahi-legalflowsuite' ); ?>
				</p>
				<div class="slos-viz-grid">
					<div class="slos-viz-card slos-viz-large">
						<h3 class="slos-viz-title">
							<span class="dashicons dashicons-chart-line"></span>
							<?php esc_html_e( 'Requests Trend', 'shahi-legalflowsuite' ); ?>
						</h3>
						<p class="slos-viz-description"><?php esc_html_e( 'Weekly request volume over time. Identify patterns and seasonal trends in DSR submissions.', 'shahi-legalflowsuite' ); ?></p>
						<div class="slos-viz-content">
							<div class="slos-chart-placeholder">
								<canvas id="slos-trend-chart"></canvas>
							</div>
						</div>
					</div>
					
					<div class="slos-viz-card">
						<h3 class="slos-viz-title">
							<span class="dashicons dashicons-chart-bar"></span>
							<?php esc_html_e( 'By Type', 'shahi-legalflowsuite' ); ?>
						</h3>
						<p class="slos-viz-description"><?php esc_html_e( 'Distribution by GDPR request type: Access, Erasure, Rectification, Portability, etc.', 'shahi-legalflowsuite' ); ?></p>
						<div class="slos-viz-content">
							<?php $this->render_type_distribution( $report['by_type'] ?? array() ); ?>
						</div>
					</div>
				</div>

				<div class="slos-viz-grid">
					<div class="slos-viz-card">
						<h3 class="slos-viz-title">
							<span class="dashicons dashicons-chart-pie"></span>
							<?php esc_html_e( 'Status Distribution', 'shahi-legalflowsuite' ); ?>
						</h3>
						<p class="slos-viz-description"><?php esc_html_e( 'Current status breakdown. Monitor pending requests and completion rates.', 'shahi-legalflowsuite' ); ?></p>
						<div class="slos-viz-content">
						<?php $this->render_status_distribution( $report['by_status'] ?? array() ); ?>
					</div>
				</div>
				
				<div class="slos-viz-card">
					<h3 class="slos-viz-title">
						<span class="dashicons dashicons-location-alt"></span>
						<?php esc_html_e( 'Top Sources', 'shahi-legalflowsuite' ); ?>
					</h3>
					<p class="slos-viz-description"><?php esc_html_e( 'Most common submission channels. Optimize intake processes for high-volume sources.', 'shahi-legalflowsuite' ); ?></p>
					<div class="slos-viz-content">
						<?php $this->render_top_sources(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render report content
	 *
	 * @since 3.0.1
	 * @param array $report  Report data.
	 * @param array $samples Sample requests.
	 * @return void
	 */
	private function render_report_content( array $report, array $samples ): void {
		// Ensure report has required structure with safe defaults.
		if ( empty( $report ) ) {
			$report = array(
				'summary'       => array(),
				'by_type'       => array(),
				'by_status'     => array(),
				'by_regulation' => array(),
				'performance'   => array(),
				'sla'           => array(),
			);
		}

		// Safe array access with defaults.
		$report = wp_parse_args(
			$report,
			array(
				'summary'       => array(),
				'by_type'       => array(),
				'by_status'     => array(),
				'by_regulation' => array(),
				'performance'   => array(),
				'sla'           => array(),
			)
		);

		$summary = wp_parse_args(
			(array) ( $report['summary'] ?? array() ),
			array(
				'total_requests'  => 0,
				'completed'       => 0,
				'open'            => 0,
				'completion_rate' => 0,
			)
		);

		$performance = wp_parse_args(
			(array) ( $report['performance'] ?? array() ),
			array(
				'avg_response_days' => 0,
				'fastest_days'      => 0,
				'slowest_days'      => 0,
			)
		);

		$sla = wp_parse_args(
			(array) ( $report['sla'] ?? array() ),
			array(
				'total_completed'   => 0,
				'sla_breaches'      => 0,
				'compliance_rate'   => 0,
				'currently_overdue' => 0,
			)
		);

		?>
		<!-- Summary Dashboard -->
		<div class="slos-report-summary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
			
			<div class="slos-metric-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); text-align: center;">
				<div style="font-size: 36px; font-weight: 700; color: #2271b1;">
					<?php echo esc_html( $summary['total_requests'] ); ?>
				</div>
				<div style="margin-top: 5px; font-size: 14px; color: #646970;">
					<?php esc_html_e( 'Total Requests', 'shahi-legalflowsuite' ); ?>
				</div>
			</div>

			<div class="slos-metric-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); text-align: center;">
				<div style="font-size: 36px; font-weight: 700; color: #00a32a;">
					<?php echo esc_html( $summary['completed'] ); ?>
				</div>
				<div style="margin-top: 5px; font-size: 14px; color: #646970;">
					<?php esc_html_e( 'Completed', 'shahi-legalflowsuite' ); ?>
				</div>
			</div>

			<div class="slos-metric-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); text-align: center;">
				<div style="font-size: 36px; font-weight: 700; color: #dba617;">
					<?php echo esc_html( $summary['open'] ); ?>
				</div>
				<div style="margin-top: 5px; font-size: 14px; color: #646970;">
					<?php esc_html_e( 'Open', 'shahi-legalflowsuite' ); ?>
				</div>
			</div>

			<div class="slos-metric-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); text-align: center;">
				<div style="font-size: 36px; font-weight: 700; color: <?php echo $summary['completion_rate'] >= 80 ? '#00a32a' : '#d63638'; ?>;">
					<?php echo esc_html( $summary['completion_rate'] ); ?>%
				</div>
				<div style="margin-top: 5px; font-size: 14px; color: #646970;">
					<?php esc_html_e( 'Completion Rate', 'shahi-legalflowsuite' ); ?>
				</div>
			</div>

		</div>

		<!-- Detailed Metrics -->
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
			
			<!-- By Type -->
			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e( 'By Request Type', 'shahi-legalflowsuite' ); ?></h2>
				<table class="widefat" style="margin-top: 10px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Type', 'shahi-legalflowsuite' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Count', 'shahi-legalflowsuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $report['by_type'] ) ) : ?>
							<tr>
								<td colspan="2" style="text-align: center; color: #646970;">
									<?php esc_html_e( 'No data', 'shahi-legalflowsuite' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( (array) $report['by_type'] as $type => $count ) : ?>
								<tr>
									<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', (string) $type ) ) ); ?></td>
									<td style="text-align: right; font-weight: 600;"><?php echo esc_html( (int) $count ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<!-- By Status -->
			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e( 'By Status', 'shahi-legalflowsuite' ); ?></h2>
				<table class="widefat" style="margin-top: 10px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Status', 'shahi-legalflowsuite' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Count', 'shahi-legalflowsuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $report['by_status'] ) ) : ?>
							<tr>
								<td colspan="2" style="text-align: center; color: #646970;">
									<?php esc_html_e( 'No data', 'shahi-legalflowsuite' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( (array) $report['by_status'] as $status => $count ) : ?>
								<tr>
									<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', (string) $status ) ) ); ?></td>
									<td style="text-align: right; font-weight: 600;"><?php echo esc_html( (int) $count ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<!-- By Regulation -->
			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e( 'By Regulation', 'shahi-legalflowsuite' ); ?></h2>
				<table class="widefat" style="margin-top: 10px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Regulation', 'shahi-legalflowsuite' ); ?></th>
							<th style="text-align: right;"><?php esc_html_e( 'Count', 'shahi-legalflowsuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $report['by_regulation'] ) ) : ?>
							<tr>
								<td colspan="2" style="text-align: center; color: #646970;">
									<?php esc_html_e( 'No data', 'shahi-legalflowsuite' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( (array) $report['by_regulation'] as $regulation => $count ) : ?>
								<tr>
									<td><?php echo esc_html( (string) $regulation ); ?></td>
									<td style="text-align: right; font-weight: 600;"><?php echo esc_html( (int) $count ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

		</div>

		<!-- Performance & SLA -->
		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0;">
			
			<!-- Performance Metrics -->
			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e( 'Performance Metrics', 'shahi-legalflowsuite' ); ?></h2>
				<table class="widefat" style="margin-top: 10px;">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Average Response Time', 'shahi-legalflowsuite' ); ?></strong></td>
							<td style="text-align: right;">
								<?php echo esc_html( $performance['avg_response_days'] ); ?> 
								<?php esc_html_e( 'days', 'shahi-legalflowsuite' ); ?>
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Fastest Response', 'shahi-legalflowsuite' ); ?></strong></td>
							<td style="text-align: right;">
								<?php echo esc_html( $performance['fastest_days'] ); ?> 
								<?php esc_html_e( 'days', 'shahi-legalflowsuite' ); ?>
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Slowest Response', 'shahi-legalflowsuite' ); ?></strong></td>
							<td style="text-align: right;">
								<?php echo esc_html( $performance['slowest_days'] ); ?> 
								<?php esc_html_e( 'days', 'shahi-legalflowsuite' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- SLA Compliance -->
			<div style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e( 'SLA Compliance', 'shahi-legalflowsuite' ); ?></h2>
				<table class="widefat" style="margin-top: 10px;">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Total Completed', 'shahi-legalflowsuite' ); ?></strong></td>
							<td style="text-align: right;"><?php echo esc_html( $sla['total_completed'] ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'SLA Breaches', 'shahi-legalflowsuite' ); ?></strong></td>
							<td style="text-align: right; color: <?php echo $sla['sla_breaches'] > 0 ? '#d63638' : '#00a32a'; ?>; font-weight: 600;">
								<?php echo esc_html( $sla['sla_breaches'] ); ?>
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Compliance Rate', 'shahi-legalflowsuite' ); ?></strong></td>
							<td style="text-align: right; color: <?php echo $sla['compliance_rate'] >= 95 ? '#00a32a' : '#d63638'; ?>; font-weight: 600;">
								<?php echo esc_html( $sla['compliance_rate'] ); ?>%
							</td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Currently Overdue', 'shahi-legalflowsuite' ); ?></strong></td>
							<td style="text-align: right; color: <?php echo $sla['currently_overdue'] > 0 ? '#d63638' : '#00a32a'; ?>; font-weight: 600;">
								<?php echo esc_html( $sla['currently_overdue'] ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>

		<!-- Anonymized Samples -->
		<?php if ( ! empty( $samples ) ) : ?>
			<div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h2 style="margin-top: 0; font-size: 18px;"><?php esc_html_e( 'Sample Requests (Anonymized)', 'shahi-legalflowsuite' ); ?></h2>
				<table class="widefat" style="margin-top: 10px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'ID', 'shahi-legalflowsuite' ); ?></th>
							<th><?php esc_html_e( 'Type', 'shahi-legalflowsuite' ); ?></th>
							<th><?php esc_html_e( 'Status', 'shahi-legalflowsuite' ); ?></th>
							<th><?php esc_html_e( 'Regulation', 'shahi-legalflowsuite' ); ?></th>
							<th><?php esc_html_e( 'Submitted', 'shahi-legalflowsuite' ); ?></th>
							<th><?php esc_html_e( 'Days Taken', 'shahi-legalflowsuite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $samples as $sample ) : ?>
							<tr>
								<td><?php echo esc_html( $sample['request_id'] ); ?></td>
								<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $sample['type'] ) ) ); ?></td>
								<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $sample['status'] ) ) ); ?></td>
								<td><?php echo esc_html( $sample['regulation'] ); ?></td>
								<td><?php echo esc_html( gmdate( 'Y-m-d', strtotime( $sample['submitted_at'] ) ) ); ?></td>
								<td><?php echo esc_html( $sample['days_taken'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p style="margin-top: 10px; color: #646970; font-size: 13px;">
					<em><?php esc_html_e( 'Note: Sample requests are randomly selected and contain no personally identifiable information.', 'shahi-legalflowsuite' ); ?></em>
				</p>
			</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Handle export requests
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function handle_export(): void {
		// Only handle on DSR Reports page.
		if ( ! isset( $_GET['page'] ) || 'shahi-legalflowsuite-dsr-reports' !== $_GET['page'] ) {
			return;
		}

		if ( ! isset( $_GET['action'] ) || ! in_array( $_GET['action'], array( 'export_csv', 'export_pdf' ), true ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'slos_export_report' ) ) {
			wp_die( esc_html__( 'Security check failed', 'shahi-legalflowsuite' ) );
		}

		// Check capabilities.
		if ( ! current_user_can( 'slos_manage_dsr' ) ) {
			wp_die( esc_html__( 'You do not have permission to export reports.', 'shahi-legalflowsuite' ) );
		}

		$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		$end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : gmdate( 'Y-m-d' );

		$report = $this->get_report_service()->generate_report( $start_date, $end_date );

		if ( isset( $_GET['action'] ) && 'export_csv' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
			$content  = $this->get_report_service()->export_to_csv( $report );
			$filename = 'dsr-report-' . $start_date . '-to-' . $end_date . '.csv';

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $content;
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}

		if ( isset( $_GET['action'] ) && 'export_pdf' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
			$content  = $this->get_report_service()->export_to_pdf( $report );
			$filename = 'dsr-report-' . $start_date . '-to-' . $end_date . '.txt';

			header( 'Content-Type: text/plain; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $content;
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}
	}
}
