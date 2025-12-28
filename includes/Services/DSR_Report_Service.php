<?php
/**
 * DSR Report Service
 *
 * Generates compliance reports for DSR handling with metrics,
 * statistics, and export capabilities (CSV, PDF).
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
 * Class DSR_Report_Service
 *
 * Compliance reporting for DSR requests:
 * - Period metrics (total, by type, by status, by regulation)
 * - Performance metrics (average response time, SLA compliance)
 * - Export formats: CSV, PDF
 * - Scheduled monthly summaries
 *
 * @since 3.0.1
 */
class DSR_Report_Service extends Base_Service {

	/**
	 * Repository instance
	 *
	 * @since 3.0.1
	 * @var \ShahiLegalFlowSuite\Database\Repositories\DSR_Repository
	 */
	private $repository;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		parent::__construct();
		$this->repository = new \ShahiLegalFlowSuite\Database\Repositories\DSR_Repository();

		// Schedule monthly report email if not already scheduled
		if ( ! wp_next_scheduled( 'slos_dsr_monthly_report' ) ) {
			wp_schedule_event( time(), 'monthly', 'slos_dsr_monthly_report' );
		}

		add_action( 'slos_dsr_monthly_report', array( $this, 'send_monthly_report' ) );
	}

	/**
	 * Generate comprehensive report for date range
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @return array Report data.
	 */
	public function generate_report( string $start_date, string $end_date ): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		// Ensure valid date range
		$start_date = gmdate( 'Y-m-d 00:00:00', strtotime( $start_date ) );
		$end_date   = gmdate( 'Y-m-d 23:59:59', strtotime( $end_date ) );

		// Build report structure
		$report = array(
			'period'      => array(
				'start' => $start_date,
				'end'   => $end_date,
			),
			'summary'     => $this->get_summary_metrics( $start_date, $end_date ),
			'by_type'     => $this->get_metrics_by_type( $start_date, $end_date ),
			'by_status'   => $this->get_metrics_by_status( $start_date, $end_date ),
			'by_regulation' => $this->get_metrics_by_regulation( $start_date, $end_date ),
			'performance' => $this->get_performance_metrics( $start_date, $end_date ),
			'sla'         => $this->get_sla_metrics( $start_date, $end_date ),
			'generated_at' => current_time( 'mysql' ),
		);

		/**
		 * Filter DSR report data
		 *
		 * @since 3.0.1
		 * @param array  $report     Report data.
		 * @param string $start_date Start date.
		 * @param string $end_date   End date.
		 */
		return apply_filters( 'slos_dsr_report_data', $report, $start_date, $end_date );
	}

	/**
	 * Get summary metrics
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Summary metrics.
	 */
	private function get_summary_metrics( string $start_date, string $end_date ): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		// Total requests in period
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE submitted_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		);

		// Completed requests
		$completed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE submitted_at BETWEEN %s AND %s AND status = 'completed'",
				$start_date,
				$end_date
			)
		);

		// Open requests (not completed or rejected)
		$open = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE submitted_at BETWEEN %s AND %s AND status NOT IN ('completed', 'rejected')",
				$start_date,
				$end_date
			)
		);

		// Rejected requests
		$rejected = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE submitted_at BETWEEN %s AND %s AND status = 'rejected'",
				$start_date,
				$end_date
			)
		);

		// Completion rate
		$completion_rate = $total > 0 ? round( ( $completed / $total ) * 100, 2 ) : 0;

		return array(
			'total_requests'  => $total,
			'completed'       => $completed,
			'open'            => $open,
			'rejected'        => $rejected,
			'completion_rate' => $completion_rate,
		);
	}

	/**
	 * Get metrics by request type
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Metrics by type.
	 */
	private function get_metrics_by_type( string $start_date, string $end_date ): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT request_type, COUNT(*) as count 
				FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				GROUP BY request_type 
				ORDER BY count DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		$metrics = array();
		foreach ( $results as $row ) {
			$metrics[ $row['request_type'] ] = (int) $row['count'];
		}

		return $metrics;
	}

	/**
	 * Get metrics by status
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Metrics by status.
	 */
	private function get_metrics_by_status( string $start_date, string $end_date ): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT status, COUNT(*) as count 
				FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				GROUP BY status 
				ORDER BY count DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		$metrics = array();
		foreach ( $results as $row ) {
			$metrics[ $row['status'] ] = (int) $row['count'];
		}

		return $metrics;
	}

	/**
	 * Get metrics by regulation
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Metrics by regulation.
	 */
	private function get_metrics_by_regulation( string $start_date, string $end_date ): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT regulation, COUNT(*) as count 
				FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				GROUP BY regulation 
				ORDER BY count DESC",
				$start_date,
				$end_date
			),
			ARRAY_A
		);

		$metrics = array();
		foreach ( $results as $row ) {
			$metrics[ $row['regulation'] ] = (int) $row['count'];
		}

		return $metrics;
	}

	/**
	 * Get performance metrics
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array Performance metrics.
	 */
	private function get_performance_metrics( string $start_date, string $end_date ): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		// Average response time for completed requests (in days)
		$avg_response = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG(DATEDIFF(completed_at, submitted_at)) 
				FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				AND status = 'completed' 
				AND completed_at IS NOT NULL",
				$start_date,
				$end_date
			)
		);

		// Fastest response time
		$fastest = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MIN(DATEDIFF(completed_at, submitted_at)) 
				FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				AND status = 'completed' 
				AND completed_at IS NOT NULL",
				$start_date,
				$end_date
			)
		);

		// Slowest response time
		$slowest = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(DATEDIFF(completed_at, submitted_at)) 
				FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				AND status = 'completed' 
				AND completed_at IS NOT NULL",
				$start_date,
				$end_date
			)
		);

		return array(
			'avg_response_days' => $avg_response ? round( (float) $avg_response, 2 ) : 0,
			'fastest_days'      => $fastest ? (int) $fastest : 0,
			'slowest_days'      => $slowest ? (int) $slowest : 0,
		);
	}

	/**
	 * Get SLA metrics
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @return array SLA metrics.
	 */
	private function get_sla_metrics( string $start_date, string $end_date ): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		// Total completed requests
		$total_completed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				AND status = 'completed'",
				$start_date,
				$end_date
			)
		);

		// SLA breaches (completed after due date)
		$breaches = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				AND status = 'completed' 
				AND completed_at > sla_deadline",
				$start_date,
				$end_date
			)
		);

		// SLA compliance rate
		$compliance_rate = $total_completed > 0 ? round( ( ( $total_completed - $breaches ) / $total_completed ) * 100, 2 ) : 100;

		// Currently overdue (open requests past deadline)
		$currently_overdue = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} 
				WHERE status NOT IN ('completed', 'rejected') 
				AND sla_deadline < %s",
				current_time( 'mysql' )
			)
		);

		return array(
			'total_completed'   => $total_completed,
			'sla_breaches'      => $breaches,
			'compliance_rate'   => $compliance_rate,
			'currently_overdue' => $currently_overdue,
		);
	}

	/**
	 * Export report to CSV
	 *
	 * @since 3.0.1
	 * @param array $report Report data.
	 * @return string CSV content.
	 */
	public function export_to_csv( array $report ): string {
		$csv = array();

		// Header
		$csv[] = 'DSR Compliance Report';
		$csv[] = 'Period: ' . $report['period']['start'] . ' to ' . $report['period']['end'];
		$csv[] = 'Generated: ' . $report['generated_at'];
		$csv[] = '';

		// Summary section
		$csv[] = 'SUMMARY METRICS';
		$csv[] = 'Metric,Value';
		$csv[] = 'Total Requests,' . $report['summary']['total_requests'];
		$csv[] = 'Completed,' . $report['summary']['completed'];
		$csv[] = 'Open,' . $report['summary']['open'];
		$csv[] = 'Rejected,' . $report['summary']['rejected'];
		$csv[] = 'Completion Rate,' . $report['summary']['completion_rate'] . '%';
		$csv[] = '';

		// By Type
		$csv[] = 'REQUESTS BY TYPE';
		$csv[] = 'Type,Count';
		foreach ( $report['by_type'] as $type => $count ) {
			$csv[] = ucfirst( str_replace( '_', ' ', $type ) ) . ',' . $count;
		}
		$csv[] = '';

		// By Status
		$csv[] = 'REQUESTS BY STATUS';
		$csv[] = 'Status,Count';
		foreach ( $report['by_status'] as $status => $count ) {
			$csv[] = ucfirst( str_replace( '_', ' ', $status ) ) . ',' . $count;
		}
		$csv[] = '';

		// By Regulation
		$csv[] = 'REQUESTS BY REGULATION';
		$csv[] = 'Regulation,Count';
		foreach ( $report['by_regulation'] as $regulation => $count ) {
			$csv[] = $regulation . ',' . $count;
		}
		$csv[] = '';

		// Performance
		$csv[] = 'PERFORMANCE METRICS';
		$csv[] = 'Metric,Value';
		$csv[] = 'Average Response Time,' . $report['performance']['avg_response_days'] . ' days';
		$csv[] = 'Fastest Response,' . $report['performance']['fastest_days'] . ' days';
		$csv[] = 'Slowest Response,' . $report['performance']['slowest_days'] . ' days';
		$csv[] = '';

		// SLA
		$csv[] = 'SLA COMPLIANCE';
		$csv[] = 'Metric,Value';
		$csv[] = 'Total Completed,' . $report['sla']['total_completed'];
		$csv[] = 'SLA Breaches,' . $report['sla']['sla_breaches'];
		$csv[] = 'Compliance Rate,' . $report['sla']['compliance_rate'] . '%';
		$csv[] = 'Currently Overdue,' . $report['sla']['currently_overdue'];

		return implode( "\n", $csv );
	}

	/**
	 * Export report to PDF (simplified text-based format)
	 *
	 * @since 3.0.1
	 * @param array $report Report data.
	 * @return string PDF content (text format for basic implementation).
	 */
	public function export_to_pdf( array $report ): string {
		// For basic implementation, return formatted text
		// In production, integrate with PDF library like TCPDF or FPDF
		$pdf_content = array();

		$pdf_content[] = '═══════════════════════════════════════════════════════════════';
		$pdf_content[] = '           DSR COMPLIANCE REPORT';
		$pdf_content[] = '═══════════════════════════════════════════════════════════════';
		$pdf_content[] = '';
		$pdf_content[] = 'Period:     ' . gmdate( 'F j, Y', strtotime( $report['period']['start'] ) ) . ' - ' . gmdate( 'F j, Y', strtotime( $report['period']['end'] ) );
		$pdf_content[] = 'Generated:  ' . gmdate( 'F j, Y g:i A', strtotime( $report['generated_at'] ) );
		$pdf_content[] = '';

		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = 'SUMMARY METRICS';
		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = sprintf( 'Total Requests:      %d', $report['summary']['total_requests'] );
		$pdf_content[] = sprintf( 'Completed:           %d', $report['summary']['completed'] );
		$pdf_content[] = sprintf( 'Open:                %d', $report['summary']['open'] );
		$pdf_content[] = sprintf( 'Rejected:            %d', $report['summary']['rejected'] );
		$pdf_content[] = sprintf( 'Completion Rate:     %.2f%%', $report['summary']['completion_rate'] );
		$pdf_content[] = '';

		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = 'REQUESTS BY TYPE';
		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		foreach ( $report['by_type'] as $type => $count ) {
			$pdf_content[] = sprintf( '%-30s %d', ucfirst( str_replace( '_', ' ', $type ) ), $count );
		}
		$pdf_content[] = '';

		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = 'REQUESTS BY STATUS';
		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		foreach ( $report['by_status'] as $status => $count ) {
			$pdf_content[] = sprintf( '%-30s %d', ucfirst( str_replace( '_', ' ', $status ) ), $count );
		}
		$pdf_content[] = '';

		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = 'REQUESTS BY REGULATION';
		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		foreach ( $report['by_regulation'] as $regulation => $count ) {
			$pdf_content[] = sprintf( '%-30s %d', $regulation, $count );
		}
		$pdf_content[] = '';

		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = 'PERFORMANCE METRICS';
		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = sprintf( 'Average Response Time:   %.2f days', $report['performance']['avg_response_days'] );
		$pdf_content[] = sprintf( 'Fastest Response:        %d days', $report['performance']['fastest_days'] );
		$pdf_content[] = sprintf( 'Slowest Response:        %d days', $report['performance']['slowest_days'] );
		$pdf_content[] = '';

		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = 'SLA COMPLIANCE';
		$pdf_content[] = '───────────────────────────────────────────────────────────────';
		$pdf_content[] = sprintf( 'Total Completed:         %d', $report['sla']['total_completed'] );
		$pdf_content[] = sprintf( 'SLA Breaches:            %d', $report['sla']['sla_breaches'] );
		$pdf_content[] = sprintf( 'Compliance Rate:         %.2f%%', $report['sla']['compliance_rate'] );
		$pdf_content[] = sprintf( 'Currently Overdue:       %d', $report['sla']['currently_overdue'] );
		$pdf_content[] = '';

		$pdf_content[] = '═══════════════════════════════════════════════════════════════';
		$pdf_content[] = 'Report generated by Shahi LegalFlowSuite';
		$pdf_content[] = '═══════════════════════════════════════════════════════════════';

		/**
		 * Filter PDF content
		 *
		 * @since 3.0.1
		 * @param string $content PDF content.
		 * @param array  $report  Report data.
		 */
		return apply_filters( 'slos_dsr_report_pdf_content', implode( "\n", $pdf_content ), $report );
	}

	/**
	 * Send monthly report email to admin
	 *
	 * @since 3.0.1
	 * @return bool True if email sent.
	 */
	public function send_monthly_report(): bool {
		// Generate report for previous month
		$start_date = gmdate( 'Y-m-01', strtotime( 'first day of last month' ) );
		$end_date   = gmdate( 'Y-m-t', strtotime( 'last day of last month' ) );

		$report = $this->generate_report( $start_date, $end_date );

		// Check if there are any requests in the period
		if ( $report['summary']['total_requests'] === 0 ) {
			return false; // Don't send empty report
		}

		// Get admin email
		$settings    = get_option( 'slos_dsr_settings', array() );
		$admin_email = isset( $settings['admin_email'] ) ? sanitize_email( $settings['admin_email'] ) : get_option( 'admin_email' );

		// Build email content
		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$month     = gmdate( 'F Y', strtotime( $start_date ) );

		$subject = sprintf( '[%s] Monthly DSR Compliance Report - %s', $site_name, $month );

		$message = sprintf(
			"Monthly DSR Compliance Report\n" .
			"Period: %s\n" .
			"Generated: %s\n\n" .
			"SUMMARY\n" .
			"────────\n" .
			"Total Requests:     %d\n" .
			"Completed:          %d\n" .
			"Open:               %d\n" .
			"Rejected:           %d\n" .
			"Completion Rate:    %.2f%%\n\n" .
			"PERFORMANCE\n" .
			"────────\n" .
			"Avg Response Time:  %.2f days\n" .
			"Fastest Response:   %d days\n" .
			"Slowest Response:   %d days\n\n" .
			"SLA COMPLIANCE\n" .
			"────────\n" .
			"Total Completed:    %d\n" .
			"SLA Breaches:       %d\n" .
			"Compliance Rate:    %.2f%%\n" .
			"Currently Overdue:  %d\n\n" .
			"View detailed report in admin: %s\n\n" .
			"This is an automated monthly report from %s.",
			$month,
			$report['generated_at'],
			$report['summary']['total_requests'],
			$report['summary']['completed'],
			$report['summary']['open'],
			$report['summary']['rejected'],
			$report['summary']['completion_rate'],
			$report['performance']['avg_response_days'],
			$report['performance']['fastest_days'],
			$report['performance']['slowest_days'],
			$report['sla']['total_completed'],
			$report['sla']['sla_breaches'],
			$report['sla']['compliance_rate'],
			$report['sla']['currently_overdue'],
			admin_url( 'admin.php?page=slos-dsr-reports' ),
			$site_name
		);

		// Attach CSV export
		$csv_content  = $this->export_to_csv( $report );
		$upload_dir   = wp_upload_dir();
		$temp_file    = $upload_dir['basedir'] . '/dsr-report-' . gmdate( 'Y-m', strtotime( $start_date ) ) . '.csv';

		file_put_contents( $temp_file, $csv_content );

		$attachments = array( $temp_file );

		$sent = wp_mail( $admin_email, $subject, $message, array(), $attachments );

		// Clean up temp file
		if ( file_exists( $temp_file ) ) {
			unlink( $temp_file );
		}

		/**
		 * Fires after monthly report is sent
		 *
		 * @since 3.0.1
		 * @param bool   $sent       Whether email was sent.
		 * @param array  $report     Report data.
		 * @param string $admin_email Admin email.
		 */
		do_action( 'slos_dsr_monthly_report_sent', $sent, $report, $admin_email );

		return $sent;
	}

	/**
	 * Get anonymized sample requests for report
	 *
	 * @since 3.0.1
	 * @param string $start_date Start date.
	 * @param string $end_date   End date.
	 * @param int    $limit      Number of samples.
	 * @return array Sample requests.
	 */
	public function get_anonymized_samples( string $start_date, string $end_date, int $limit = 5 ): array {
		global $wpdb;
		$table = $this->repository->get_full_table_name();

		$samples = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, request_type, status, regulation, submitted_at, completed_at, 
				DATEDIFF(COALESCE(completed_at, NOW()), submitted_at) as days_taken
				FROM {$table} 
				WHERE submitted_at BETWEEN %s AND %s 
				ORDER BY RAND() 
				LIMIT %d",
				$start_date,
				$end_date,
				$limit
			),
			ARRAY_A
		);

		// Anonymize: only include non-identifying data
		return array_map(
			function ( $sample ) {
				return array(
					'request_id'   => $sample['id'],
					'type'         => $sample['request_type'],
					'status'       => $sample['status'],
					'regulation'   => $sample['regulation'],
					'submitted_at' => $sample['submitted_at'],
					'completed_at' => $sample['completed_at'],
					'days_taken'   => (int) $sample['days_taken'],
				);
			},
			$samples
		);
	}
}
