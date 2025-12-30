<?php
/**
 * Compliance Export AJAX Handler
 *
 * Handles CSV and PDF export requests for consent records and audit logs.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Ajax
 * @since      3.1.1
 */

namespace ShahiLegalFlowSuite\Ajax;

use ShahiLegalFlowSuite\Services\Consent_Service;
use ShahiLegalFlowSuite\Services\Consent_Audit_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compliance_Export_Ajax Class
 *
 * Provides endpoints for exporting compliance data.
 *
 * @since 3.1.1
 */
class Compliance_Export_Ajax {

	/**
	 * Consent service instance
	 *
	 * @var Consent_Service
	 */
	private $consent_service;

	/**
	 * Audit logger instance
	 *
	 * @var Consent_Audit_Logger
	 */
	private $audit_logger;

	/**
	 * Constructor
	 *
	 * @since 3.1.1
	 */
	public function __construct() {
		require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Services/Consent_Service.php';
		require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Services/Consent_Audit_Logger.php';

		$this->consent_service = new Consent_Service();
		$this->audit_logger    = new Consent_Audit_Logger();
	}

	/**
	 * Register AJAX actions
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function register_actions() {
		add_action( 'wp_ajax_slos_export_consents_csv', array( $this, 'export_consents_csv' ) );
		add_action( 'wp_ajax_slos_export_consents_pdf', array( $this, 'export_consents_pdf' ) );
		add_action( 'wp_ajax_slos_export_audit_logs_csv', array( $this, 'export_audit_logs_csv' ) );
		add_action( 'wp_ajax_slos_get_consent_time_series', array( $this, 'get_consent_time_series' ) );
	}

	/**
	 * Get consent time-series data via AJAX
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function get_consent_time_series() {
		// Verify permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'shahi-legalflowsuite' ) ), 403 );
		}

		// Verify nonce
		check_ajax_referer( 'slos_export_consents', 'nonce' );

		// Get parameters
		$interval   = isset( $_POST['interval'] ) ? sanitize_text_field( wp_unslash( $_POST['interval'] ) ) : 'daily';
		$days_back  = isset( $_POST['days_back'] ) ? absint( $_POST['days_back'] ) : 30;
		$group_by   = isset( $_POST['group_by'] ) ? sanitize_text_field( wp_unslash( $_POST['group_by'] ) ) : 'none';

		// Get time-series data
		$data = $this->consent_service->get_time_series( array(
			'interval'   => $interval,
			'days_back'  => $days_back,
			'group_by'   => $group_by,
		) );

		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'message' => $data->get_error_message() ) );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Export consents to CSV
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function export_consents_csv() {
		// Verify permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'shahi-legalflowsuite' ), 403 );
		}

		// Verify nonce
		check_ajax_referer( 'slos_export_consents', 'nonce' );

		// Get filter parameters
		$days_back = isset( $_GET['days_back'] ) ? absint( $_GET['days_back'] ) : 30;
		$status    = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
		$type      = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';

		// Fetch consent records
		global $wpdb;
		$table = $wpdb->prefix . 'slos_consent';

		$where_clauses = array( "created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)" );
		$where_values  = array( $days_back );

		if ( ! empty( $status ) ) {
			$where_clauses[] = 'status = %s';
			$where_values[]  = $status;
		}

		if ( ! empty( $type ) ) {
			$where_clauses[] = 'type = %s';
			$where_values[]  = $type;
		}

		$where_sql = implode( ' AND ', $where_clauses );

		$query = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT 10000";
		$consents = $wpdb->get_results( $wpdb->prepare( $query, ...$where_values ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Generate CSV
		$filename = 'consent-records-' . gmdate( 'Y-m-d-His' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// Write CSV header
		fputcsv(
			$output,
			array(
				'ID',
				'User ID',
				'Type',
				'Status',
				'Country Code',
				'IP Hash',
				'User Agent',
				'Language',
				'Banner Version',
				'Policy Version',
				'Method',
				'Created At',
				'Updated At',
			)
		);

		// Write data rows
		foreach ( $consents as $consent ) {
			fputcsv(
				$output,
				array(
					$consent['id'],
					$consent['user_id'],
					$consent['type'],
					$consent['status'],
					$consent['country_code'] ?? '',
					$consent['ip_hash'] ?? '',
					$consent['user_agent'] ?? '',
					$consent['language'] ?? '',
					$consent['banner_version'] ?? '',
					$consent['policy_version'] ?? '',
					$consent['method'] ?? '',
					$consent['created_at'],
					$consent['updated_at'],
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Export consents to PDF summary report
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function export_consents_pdf() {
		// Verify permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'shahi-legalflowsuite' ), 403 );
		}

		// Verify nonce
		check_ajax_referer( 'slos_export_consents', 'nonce' );

		// Get filter parameters
		$days_back = isset( $_GET['days_back'] ) ? absint( $_GET['days_back'] ) : 30;

		// Gather statistics
		$stats = $this->gather_consent_statistics( $days_back );

		// Generate PDF using existing dompdf library
		require_once SLOS_PLUGIN_DIR . 'vendor/autoload.php';

		$dompdf = new \Dompdf\Dompdf();
		$html = $this->generate_pdf_html( $stats, $days_back );

		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();

		$filename = 'consent-summary-' . gmdate( 'Y-m-d' ) . '.pdf';

		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo $dompdf->output(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Export audit logs to CSV
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function export_audit_logs_csv() {
		// Verify permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'shahi-legalflowsuite' ), 403 );
		}

		// Verify nonce
		check_ajax_referer( 'slos_export_consents', 'nonce' );

		// Get filter parameters
		$days_back = isset( $_GET['days_back'] ) ? absint( $_GET['days_back'] ) : 30;
		$action    = isset( $_GET['action_type'] ) ? sanitize_text_field( wp_unslash( $_GET['action_type'] ) ) : '';

		// Fetch audit logs
		global $wpdb;
		$table = $wpdb->prefix . 'slos_consent_audit_log';

		$where_clauses = array( "created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)" );
		$where_values  = array( $days_back );

		if ( ! empty( $action ) ) {
			$where_clauses[] = 'action = %s';
			$where_values[]  = $action;
		}

		$where_sql = implode( ' AND ', $where_clauses );

		$query = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT 10000";
		$logs = $wpdb->get_results( $wpdb->prepare( $query, ...$where_values ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Generate CSV
		$filename = 'audit-logs-' . gmdate( 'Y-m-d-His' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		// Write CSV header
		fputcsv(
			$output,
			array(
				'ID',
				'Consent ID',
				'User ID',
				'Action',
				'Old Value',
				'New Value',
				'IP Address',
				'User Agent',
				'Created At',
			)
		);

		// Write data rows
		foreach ( $logs as $log ) {
			fputcsv(
				$output,
				array(
					$log['id'],
					$log['consent_id'] ?? '',
					$log['user_id'] ?? '',
					$log['action'],
					$log['old_value'] ?? '',
					$log['new_value'] ?? '',
					$log['ip_address'] ?? '',
					$log['user_agent'] ?? '',
					$log['created_at'],
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Gather consent statistics for PDF report
	 *
	 * @since 3.1.1
	 * @param int $days_back Number of days to analyze.
	 * @return array Statistics data
	 */
	private function gather_consent_statistics( int $days_back ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'slos_consent';

		$start_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days_back} days" ) );

		// Total consents
		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE created_at >= %s",
				$start_date
			)
		);

		// By status
		$by_status = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT status, COUNT(*) as count FROM {$table} WHERE created_at >= %s GROUP BY status",
				$start_date
			),
			ARRAY_A
		);

		// By type
		$by_type = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT type, COUNT(*) as count FROM {$table} WHERE created_at >= %s GROUP BY type",
				$start_date
			),
			ARRAY_A
		);

		// Top countries
		$by_country = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT country_code, COUNT(*) as count FROM {$table} WHERE created_at >= %s AND country_code IS NOT NULL GROUP BY country_code ORDER BY count DESC LIMIT 10",
				$start_date
			),
			ARRAY_A
		);

		// Get time-series data
		$time_series = $this->consent_service->get_time_series(
			array(
				'interval'  => 'daily',
				'days_back' => $days_back,
			)
		);

		return array(
			'total'       => (int) $total,
			'by_status'   => $by_status,
			'by_type'     => $by_type,
			'by_country'  => $by_country,
			'time_series' => $time_series,
			'period'      => $days_back,
		);
	}

	/**
	 * Generate PDF HTML content
	 *
	 * @since 3.1.1
	 * @param array $stats     Statistics data.
	 * @param int   $days_back Period in days.
	 * @return string HTML content
	 */
	private function generate_pdf_html( array $stats, int $days_back ): string {
		$site_name = get_bloginfo( 'name' );
		$date      = gmdate( 'F j, Y' );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="utf-8">
			<title>Consent Summary Report</title>
			<style>
				body {
					font-family: 'Helvetica', 'Arial', sans-serif;
					color: #333;
					line-height: 1.6;
					margin: 40px;
				}
				.header {
					text-align: center;
					margin-bottom: 40px;
					padding-bottom: 20px;
					border-bottom: 3px solid #3b82f6;
				}
				h1 {
					color: #1e40af;
					font-size: 28px;
					margin-bottom: 10px;
				}
				.meta {
					color: #666;
					font-size: 14px;
				}
				.section {
					margin-bottom: 30px;
				}
				h2 {
					color: #1e40af;
					font-size: 20px;
					margin-bottom: 15px;
					padding-bottom: 10px;
					border-bottom: 2px solid #e5e7eb;
				}
				.stat-grid {
					display: table;
					width: 100%;
					margin-bottom: 20px;
				}
				.stat-row {
					display: table-row;
				}
				.stat-label {
					display: table-cell;
					padding: 8px 12px;
					background: #f3f4f6;
					font-weight: 600;
					border-bottom: 1px solid #e5e7eb;
				}
				.stat-value {
					display: table-cell;
					padding: 8px 12px;
					text-align: right;
					border-bottom: 1px solid #e5e7eb;
				}
				.summary-box {
					background: #eff6ff;
					border-left: 4px solid #3b82f6;
					padding: 20px;
					margin-bottom: 20px;
				}
				.summary-number {
					font-size: 48px;
					font-weight: 700;
					color: #1e40af;
					margin-bottom: 5px;
				}
				.footer {
					margin-top: 60px;
					padding-top: 20px;
					border-top: 1px solid #e5e7eb;
					text-align: center;
					font-size: 12px;
					color: #666;
				}
			</style>
		</head>
		<body>
			<div class="header">
				<h1>Consent Management Summary Report</h1>
				<div class="meta">
					<strong><?php echo esc_html( $site_name ); ?></strong><br>
					Generated: <?php echo esc_html( $date ); ?><br>
					Period: Last <?php echo absint( $days_back ); ?> days
				</div>
			</div>

			<div class="section">
				<div class="summary-box">
					<div class="summary-number"><?php echo number_format_i18n( $stats['total'] ); ?></div>
					<div>Total Consent Records</div>
				</div>
			</div>

			<div class="section">
				<h2>Consents by Status</h2>
				<div class="stat-grid">
					<?php foreach ( $stats['by_status'] as $row ) : ?>
					<div class="stat-row">
						<div class="stat-label"><?php echo esc_html( ucfirst( $row['status'] ) ); ?></div>
						<div class="stat-value"><?php echo number_format_i18n( $row['count'] ); ?></div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="section">
				<h2>Consents by Type</h2>
				<div class="stat-grid">
					<?php foreach ( $stats['by_type'] as $row ) : ?>
					<div class="stat-row">
						<div class="stat-label"><?php echo esc_html( ucfirst( $row['type'] ) ); ?></div>
						<div class="stat-value"><?php echo number_format_i18n( $row['count'] ); ?></div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="section">
				<h2>Top Countries</h2>
				<div class="stat-grid">
					<?php foreach ( $stats['by_country'] as $row ) : ?>
					<div class="stat-row">
						<div class="stat-label"><?php echo esc_html( strtoupper( $row['country_code'] ) ); ?></div>
						<div class="stat-value"><?php echo number_format_i18n( $row['count'] ); ?></div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="section">
				<h2>Time Series Summary</h2>
				<p>
					<strong>Average daily consents:</strong> 
					<?php echo number_format_i18n( $stats['time_series']['metadata']['average'] ?? 0, 1 ); ?>
				</p>
				<p>
					<strong>Total in period:</strong> 
					<?php echo number_format_i18n( $stats['time_series']['metadata']['total'] ?? 0 ); ?>
				</p>
			</div>

			<div class="footer">
				<p>
					This report was generated automatically by Shahi LegalOps Suite.<br>
					For detailed analysis, please access the admin dashboard.
				</p>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}
}
