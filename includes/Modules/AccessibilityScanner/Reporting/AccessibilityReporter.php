<?php
/**
 * Accessibility report exports and email delivery.
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\Reporting
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Reporting;

use Dompdf\Dompdf;
use Dompdf\Options;
use WP_Query;
use function __;
use function admin_url;
use function check_ajax_referer;
use function current_time;
use function current_user_can;
use function date_i18n;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_bloginfo;
use function get_option;
use function get_permalink;
use function get_post_meta;
use function get_site_url;
use function gmdate;
use function is_email;
use function wp_mail;
use function wp_send_json_error;
use function wp_send_json_success;
use function wp_unslash;
use function sanitize_text_field;
use function wp_json_encode;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles export and reporting for accessibility scans.
 */
class AccessibilityReporter {

	/**
	 * Hook registrations.
	 */
	public function __construct() {
		add_action( 'wp_ajax_slos_export_report', array( $this, 'ajax_export_report' ) );
		add_action( 'wp_ajax_slos_export_pdf', array( $this, 'ajax_export_pdf' ) );
		add_action( 'wp_ajax_slos_export_csv', array( $this, 'ajax_export_csv' ) );
		add_action( 'wp_ajax_slos_export_json', array( $this, 'ajax_export_json' ) );
		add_action( 'wp_ajax_slos_send_test_report', array( $this, 'ajax_send_test_report' ) );
	}

	/**
	 * AJAX: Export report dispatcher.
	 *
	 * @return void.
	 */
	public function ajax_export_report() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized access.', 'shahi-legalflowsuite' ) );
		}

		$format  = isset( $_POST['format'] ) ? sanitize_text_field( wp_unslash( $_POST['format'] ) ) : 'csv';
		$results = $this->get_all_scan_results();

		switch ( $format ) {
			case 'csv':
				$this->export_csv( $results );
				break;
			case 'json':
				$this->export_json( $results );
				break;
			case 'html':
				$this->export_html( $results );
				break;
			default:
				wp_send_json_error( __( 'Invalid format.', 'shahi-legalflowsuite' ) );
		}
	}

	/**
	 * AJAX: Export PDF report.
	 *
	 * @since 3.1.1
	 * @return void.
	 */
	public function ajax_export_pdf() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'shahi-legalflowsuite' ) ) );
		}

		$template = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : 'executive';

		try {
			$this->export_pdf( $template );
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX: Export enhanced CSV.
	 *
	 * @since 3.1.1
	 * @return void.
	 */
	public function ajax_export_csv() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'shahi-legalflowsuite' ) ) );
		}

		$results = $this->get_all_scan_results();
		$this->export_csv( $results );
	}

	/**
	 * AJAX: Export JSON.
	 *
	 * @since 3.1.1
	 * @return void.
	 */
	public function ajax_export_json() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'shahi-legalflowsuite' ) ) );
		}

		$results = $this->get_all_scan_results();
		$this->export_json( $results );
	}

	/**
	 * AJAX: Send test report email.
	 *
	 * @since 3.1.1
	 * @return void.
	 */
	public function ajax_send_test_report() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'shahi-legalflowsuite' ) ) );
		}

		$email    = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
		$template = isset( $_POST['template'] ) ? sanitize_text_field( wp_unslash( $_POST['template'] ) ) : 'executive';
		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please provide a valid email address.', 'shahi-legalflowsuite' ) ) );
		}

		$result = $this->send_email_report( $email, $template );

		if ( $result ) {
			wp_send_json_success( array( 'message' => __( 'Test report sent successfully!', 'shahi-legalflowsuite' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to send test report.', 'shahi-legalflowsuite' ) ) );
		}
	}

	/**
	 * Retrieve all posts/pages with accessibility scan results.
	 *
	 * @return array Scan results with metadata.
	 */
	private function get_all_scan_results() {
		// Fetch all posts with scan results.
		// phpcs:disable WordPress.DB.SlowDBQuery
		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => '_slos_accessibility_scan_results',
					'compare' => 'EXISTS',
				),
			),
		);
		// phpcs:enable WordPress.DB.SlowDBQuery

		$query = new WP_Query( $args );
		$data  = array();

		foreach ( $query->posts as $post ) {
			$scan_results = get_post_meta( $post->ID, '_slos_accessibility_scan_results', true );
			if ( ! empty( $scan_results ) ) {
				$data[] = array(
					'post_id'    => $post->ID,
					'post_title' => $post->post_title,
					'post_url'   => get_permalink( $post->ID ),
					'scan_date'  => get_post_meta( $post->ID, '_slos_accessibility_scan_date', true ),
					'results'    => $scan_results,
				);
			}
		}

		return $data;
	}

	/**
	 * Export scan results to CSV.
	 *
	 * @param array $data Results data.
	 * @return void
	 */
	private function export_csv( $data ) {
		$filename = 'accessibility-report-' . gmdate( 'Y-m-d' ) . '.csv';
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );

		// Enhanced CSV headers.
		fputcsv( $output, array( 'Post ID', 'Title', 'URL', 'Post Type', 'Scan Date', 'Overall Score', 'Issue Type', 'Severity', 'WCAG Criterion', 'Description', 'Message', 'Element', 'Line Number', 'Fixable' ) );

		foreach ( $data as $row ) {
			$score = isset( $row['results']['score'] ) ? $row['results']['score'] : 'N/A';

			foreach ( $row['results'] as $check_id => $result ) {
				if ( 'score' === $check_id ) {
					continue;
				}

				if ( ! empty( $result['issues'] ) ) {
					foreach ( $result['issues'] as $issue ) {
						fputcsv(
							$output,
							array(
								$row['post_id'],
								$row['post_title'],
								$row['post_url'],
								isset( $row['post_type'] ) ? $row['post_type'] : 'N/A',
								$row['scan_date'],
								$score,
								$check_id,
								isset( $result['severity'] ) ? $result['severity'] : 'N/A',
								isset( $result['wcag'] ) ? $result['wcag'] : 'N/A',
								isset( $result['description'] ) ? $result['description'] : '',
								isset( $issue['message'] ) ? $issue['message'] : '',
								isset( $issue['element'] ) ? $issue['element'] : 'N/A',
								isset( $issue['line'] ) ? $issue['line'] : 'N/A',
								isset( $result['fixable'] ) && $result['fixable'] ? 'Yes' : 'No',
							)
						);
					}
				}
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Required for CSV output streaming
		fclose( $output );
		exit;
	}

	/**
	 * Export scan results to JSON.
	 *
	 * @param array $data Results data.
	 * @return void
	 */
	private function export_json( $data ) {
		$filename = 'accessibility-report-' . gmdate( 'Y-m-d' ) . '.json';
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$export_data = array(
			'generated_at'   => current_time( 'mysql' ),
			'report_version' => '1.0',
			'site_url'       => get_site_url(),
			'site_name'      => get_bloginfo( 'name' ),
			'statistics'     => get_option( 'slos_scan_statistics', array() ),
			'pages'          => $data,
		);

		echo wp_json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/**
	 * Generate PDF report
	 *
	 * @param string $template Template type (executive|technical).
	 * @throws \Exception If PDF generation fails.
	 */
	private function export_pdf( $template = 'executive' ) {
		if ( ! class_exists( 'Dompdf\Dompdf' ) ) {
			throw new \Exception( esc_html__( 'PDF library not available.', 'shahi-legalflowsuite' ) );
		}

		$data  = $this->get_all_scan_results();
		$stats = get_option( 'slos_scan_statistics', array() );

		$html = ( 'executive' === $template )
			? $this->generate_executive_summary_html( $data, $stats )
			: $this->generate_technical_details_html( $data, $stats );

		$options = new Options();
		$options->set( 'isHtml5ParserEnabled', true );
		$options->set( 'isPhpEnabled', false );
		$options->set( 'isRemoteEnabled', false );

		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();

		$filename = 'accessibility-report-' . $template . '-' . gmdate( 'Y-m-d' ) . '.pdf';
		$dompdf->stream( $filename, array( 'Attachment' => true ) );
		exit;
	}

	/**
	 * Generate Executive Summary HTML for PDF
	 *
	 * @param array $data  Scan results data.
	 * @param array $stats Statistics.
	 * @return string HTML content.
	 */
	private function generate_executive_summary_html( $data, $stats ) {
		$site_name     = get_bloginfo( 'name' );
		$site_url      = get_site_url();
		$date          = date_i18n( get_option( 'date_format' ) );
		$average_score = isset( $stats['average_score'] ) ? intval( $stats['average_score'] ) : 0;
		$total_issues  = isset( $stats['total_issues'] ) ? intval( $stats['total_issues'] ) : 0;

		$average_score  = isset( $stats['average_score'] ) ? intval( $stats['average_score'] ) : 0;
		$total_issues   = isset( $stats['total_issues'] ) ? intval( $stats['total_issues'] ) : 0;
		$total_critical = isset( $stats['total_critical'] ) ? intval( $stats['total_critical'] ) : 0;
		$pages_scanned  = count( $data );

		$html = '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Accessibility Report - Executive Summary</title>
	<style>
		@page { margin: 20mm; }
		body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; font-size: 11pt; }
		h1 { color: #2271b1; font-size: 24pt; margin-bottom: 5mm; border-bottom: 3px solid #2271b1; padding-bottom: 3mm; }
		h2 { color: #2271b1; font-size: 16pt; margin-top: 8mm; margin-bottom: 4mm; border-bottom: 1px solid #ccc; padding-bottom: 2mm; }
		h3 { color: #555; font-size: 13pt; margin-top: 5mm; margin-bottom: 3mm; }
		.header { text-align: center; margin-bottom: 10mm; }
		.subtitle { color: #666; font-size: 12pt; margin-top: 2mm; }
		.meta { color: #999; font-size: 10pt; margin-top: 2mm; }
		.summary-grid { display: table; width: 100%; margin: 5mm 0; }
		.summary-box { display: table-cell; width: 25%; text-align: center; padding: 4mm; background: #f5f5f5; border: 1px solid #ddd; }
		.summary-box.good { background: #e8f5e9; border-color: #4caf50; }
		.summary-box.warning { background: #fff3e0; border-color: #ff9800; }
		.summary-box.critical { background: #ffebee; border-color: #f44336; }
		.summary-value { font-size: 28pt; font-weight: bold; color: #2271b1; display: block; margin-bottom: 2mm; }
		.summary-label { font-size: 10pt; color: #666; display: block; }
		.recommendation { background: #e3f2fd; padding: 4mm; margin: 4mm 0; border-left: 4px solid #2196f3; }
		.page-footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9pt; color: #999; border-top: 1px solid #ddd; padding-top: 2mm; }
	</style>
</head>
<body>
	<div class="header">
		<h1>Website Accessibility Compliance Report</h1>
		<div class="subtitle">Executive Summary</div>
		<div class="meta">' . esc_html( $site_name ) . ' | ' . esc_html( $site_url ) . '</div>
		<div class="meta">Report Generated: ' . esc_html( $date ) . '</div>
	</div>

	<h2>Overall Performance</h2>
	<div class="summary-grid">
		<div class="summary-box ' . ( $average_score >= 80 ? 'good' : ( $average_score >= 60 ? 'warning' : 'critical' ) ) . '">
			<span class="summary-value">' . esc_html( $average_score ) . '%</span>
			<span class="summary-label">Average Score</span>
		</div>
		<div class="summary-box">
			<span class="summary-value">' . esc_html( $pages_scanned ) . '</span>
			<span class="summary-label">Pages Scanned</span>
		</div>
		<div class="summary-box ' . ( $total_issues > 50 ? 'critical' : ( $total_issues > 20 ? 'warning' : 'good' ) ) . '">
			<span class="summary-value">' . esc_html( $total_issues ) . '</span>
			<span class="summary-label">Total Issues</span>
		</div>
		<div class="summary-box ' . ( $total_critical > 10 ? 'critical' : ( $total_critical > 5 ? 'warning' : 'good' ) ) . '">
			<span class="summary-value">' . esc_html( $total_critical ) . '</span>
			<span class="summary-label">Critical Issues</span>
		</div>
	</div>

	<h2>Key Findings</h2>
	<div class="recommendation">
		<strong>Compliance Status:</strong> ';

		if ( $average_score >= 90 ) {
			$html .= 'Excellent - Your website demonstrates strong accessibility compliance.';
		} elseif ( $average_score >= 80 ) {
			$html .= 'Good - Your website meets most accessibility standards with minor improvements needed.';
		} elseif ( $average_score >= 60 ) {
			$html .= 'Fair - Several accessibility issues require attention to meet compliance standards.';
		} else {
			$html .= 'Needs Improvement - Significant accessibility barriers exist that prevent users with disabilities from accessing your content.';
		}

		$html .= '</div>

	<h2>Top Priority Actions</h2>
	<ol>';

		if ( $total_critical > 0 ) {
			$html .= '<li><strong>Address Critical Issues:</strong> ' . esc_html( $total_critical ) . ' critical accessibility barriers must be fixed immediately to prevent legal risk and ensure basic usability.</li>';
		}

		$html .= '<li><strong>Focus on High-Impact Pages:</strong> Prioritize fixing issues on your most visited pages and conversion-critical content.</li>
		<li><strong>Implement Auto-Fix Solutions:</strong> Many common issues can be automatically corrected through the accessibility tools available in your dashboard.</li>
		<li><strong>Regular Monitoring:</strong> Schedule periodic scans to maintain compliance and catch new issues before they impact users.</li>
	</ol>

	<h2>WCAG 2.2 Compliance Overview</h2>
	<p>This report evaluates your website against Web Content Accessibility Guidelines (WCAG) 2.2 Level AA standards, which are required by many accessibility laws including the ADA (Americans with Disabilities Act) and Section 508.</p>

	<div class="page-footer">
		<p>' . esc_html( $site_name ) . ' - Accessibility Report &copy; ' . gmdate( 'Y' ) . '</p>
	</div>
</body>
</html>';

		return $html;
	}

	/**
	 * Generate Technical Details HTML for PDF
	 *
	 * @param array $data  Scan results data.
	 * @param array $stats Statistics.
	 * @return string HTML content.
	 */
	private function generate_technical_details_html( $data, $stats ) {
		$site_name     = get_bloginfo( 'name' );
		$site_url      = get_site_url();
		$date          = date_i18n( get_option( 'date_format' ) );
		$average_score = isset( $stats['average_score'] ) ? intval( $stats['average_score'] ) : 0;
		$total_issues  = isset( $stats['total_issues'] ) ? intval( $stats['total_issues'] ) : 0;

		$html = '<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Accessibility Report - Technical Details</title>
	<style>
		@page { margin: 15mm; }
		body { font-family: "Courier New", monospace; color: #333; line-height: 1.4; font-size: 9pt; }
		h1 { color: #2271b1; font-size: 18pt; margin-bottom: 3mm; border-bottom: 2px solid #2271b1; padding-bottom: 2mm; }
		h2 { color: #2271b1; font-size: 13pt; margin-top: 5mm; margin-bottom: 2mm; border-bottom: 1px solid #ccc; padding-bottom: 1mm; page-break-after: avoid; }
		h3 { color: #555; font-size: 11pt; margin-top: 3mm; margin-bottom: 1mm; page-break-after: avoid; }
		.meta { color: #999; font-size: 8pt; margin-bottom: 5mm; }
		table { width: 100%; border-collapse: collapse; margin: 3mm 0; font-size: 8pt; }
		th { background: #2271b1; color: white; padding: 2mm; text-align: left; font-weight: bold; }
		td { padding: 2mm; border-bottom: 1px solid #ddd; }
		tr:nth-child(even) { background: #f9f9f9; }
		.critical { color: #d32f2f; font-weight: bold; }
		.warning { color: #f57c00; }
		.page-break { page-break-before: always; }
		code { background: #f5f5f5; padding: 1mm 2mm; border: 1px solid #ddd; border-radius: 1mm; }
	</style>
</head>
<body>
	<h1>Website Accessibility Technical Report</h1>
	<div class="meta">' . esc_html( $site_name ) . ' | ' . esc_html( $site_url ) . ' | Generated: ' . esc_html( $date ) . ' | Avg Score: ' . esc_html( $average_score ) . '% | Issues: ' . esc_html( $total_issues ) . '</div>';

		foreach ( $data as $page ) {
			$html .= '<div class="page-break">
				<h2>Page: ' . esc_html( $page['post_title'] ) . '</h2>
				<p><strong>URL:</strong> ' . esc_html( $page['post_url'] ) . '</p>
				<p><strong>Post ID:</strong> ' . esc_html( $page['post_id'] ) . ' | <strong>Scanned:</strong> ' . esc_html( $page['scan_date'] ) . '</p>
				
				<table>
					<thead>
						<tr>
							<th>Severity</th>
							<th>WCAG</th>
							<th>Issue Type</th>
							<th>Description</th>
							<th>Element</th>
						</tr>
					</thead>
					<tbody>';

			if ( ! empty( $page['results'] ) ) {
				foreach ( $page['results'] as $check_id => $result ) {
					if ( 'score' === $check_id || empty( $result['issues'] ) ) {
						continue;
					}

					foreach ( $result['issues'] as $issue ) {
						$severity_class = isset( $result['severity'] ) && 'critical' === $result['severity'] ? 'critical' : 'warning';
						$html          .= '<tr>
							<td class="' . $severity_class . '">' . esc_html( isset( $result['severity'] ) ? strtoupper( $result['severity'] ) : 'N/A' ) . '</td>
							<td>' . esc_html( isset( $result['wcag'] ) ? $result['wcag'] : 'N/A' ) . '</td>
							<td>' . esc_html( $check_id ) . '</td>
							<td>' . esc_html( isset( $issue['message'] ) ? $issue['message'] : '' ) . '</td>
							<td><code>' . esc_html( isset( $issue['element'] ) ? $issue['element'] : 'N/A' ) . '</code></td>
						</tr>';
					}
				}
			} else {
				$html .= '<tr><td colspan="5" style="text-align: center; color: #4caf50;">No accessibility issues found on this page.</td></tr>';
			}

			$html .= '</tbody></table></div>';
		}

		$html .= '</body></html>';

		return $html;
	}

	/**
	 * Send email report
	 *
	 * @param string $email    Recipient email.
	 * @param string $template Report template.
	 * @return bool Success status.
	 */
	public function send_email_report( $email, $template = 'executive' ) {
		$stats          = get_option( 'slos_scan_statistics', array() );
		$average_score  = isset( $stats['average_score'] ) ? intval( $stats['average_score'] ) : 0;
		$total_issues   = isset( $stats['total_issues'] ) ? intval( $stats['total_issues'] ) : 0;
		$total_critical = isset( $stats['total_critical'] ) ? intval( $stats['total_critical'] ) : 0;

		$subject = sprintf(
			/* translators: %s: date */
			__( 'Accessibility Compliance Report - %s', 'shahi-legalflowsuite' ),
			date_i18n( get_option( 'date_format' ) )
		);

		$message = $this->generate_email_template(
			$template,
			array(
				'average_score'  => $average_score,
				'total_issues'   => $total_issues,
				'total_critical' => $total_critical,
			)
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		return wp_mail( $email, $subject, $message, $headers );
	}

	/**
	 * Generate email HTML template
	 *
	 * @param string $template Template type.
	 * @param array  $data     Email data.
	 * @return string HTML content.
	 */
	private function generate_email_template( $template, $data ) {
		$site_name = get_bloginfo( 'name' );
		$site_url  = get_site_url();
		$admin_url = admin_url( 'admin.php?page=slos-accessibility&tab=tools' );

		$html = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<style>
		body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; }
		.header { background: #2271b1; color: white; padding: 20px; text-align: center; }
		.content { background: #ffffff; padding: 30px; border: 1px solid #ddd; }
		.stats { display: table; width: 100%; margin: 20px 0; }
		.stat-box { display: table-cell; text-align: center; padding: 15px; background: #f5f5f5; border: 1px solid #ddd; }
		.stat-value { font-size: 32px; font-weight: bold; color: #2271b1; }
		.stat-label { font-size: 14px; color: #666; }
		.button { display: inline-block; padding: 12px 24px; background: #2271b1; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
		.footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1>Accessibility Report</h1>
			<p>' . esc_html( $site_name ) . '</p>
		</div>
		<div class="content">
			<p>Your scheduled accessibility compliance report is ready:</p>
			
			<div class="stats">
				<div class="stat-box">
					<div class="stat-value">' . esc_html( $data['average_score'] ) . '%</div>
					<div class="stat-label">Average Score</div>
				</div>
				<div class="stat-box">
					<div class="stat-value">' . esc_html( $data['total_issues'] ) . '</div>
					<div class="stat-label">Total Issues</div>
				</div>
				<div class="stat-box">
					<div class="stat-value">' . esc_html( $data['total_critical'] ) . '</div>
					<div class="stat-label">Critical Issues</div>
				</div>
			</div>
			
			<p><strong>Status:</strong> ';

		if ( $data['average_score'] >= 80 ) {
			$html .= 'Good - Your website meets accessibility standards.';
		} elseif ( $data['average_score'] >= 60 ) {
			$html .= 'Fair - Some issues need attention.';
		} else {
			$html .= 'Needs Improvement - Several accessibility barriers exist.';
		}

		$html .= '</p>
			
			<p>For detailed issue breakdown and automated fixes:</p>
			<a href="' . esc_url( $admin_url ) . '" class="button">View Full Report</a>
		</div>
		<div class="footer">
			<p>&copy; ' . gmdate( 'Y' ) . ' ' . esc_html( $site_name ) . ' | <a href="' . esc_url( $site_url ) . '">' . esc_url( $site_url ) . '</a></p>
			<p>This is an automated report from Shahi LegalFlowSuite Accessibility Scanner.</p>
		</div>
	</div>
</body>
</html>';

		return $html;
	}

	/**
	 * Export scan results to HTML.
	 *
	 * @param array $data Results data.
	 * @return void.
	 */
	private function export_html( $data ) {
		$filename = 'accessibility-report-' . gmdate( 'Y-m-d' ) . '.html';
		header( 'Content-Type: text/html' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>Accessibility Report - <?php echo esc_html( gmdate( 'Y-m-d' ) ); ?></title>
			<style>
				body { font-family: sans-serif; line-height: 1.6; color: #333; max-width: 1200px; margin: 0 auto; padding: 20px; }
				h1 { border-bottom: 2px solid #2271b1; padding-bottom: 10px; }
				.page-report { margin-bottom: 40px; border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
				.page-header { background: #f9f9f9; padding: 10px; margin: -20px -20px 20px -20px; border-bottom: 1px solid #ddd; }
				.issue { margin-bottom: 15px; padding-left: 15px; border-left: 4px solid #ddd; }
				.issue.critical { border-left-color: #d63638; }
				.issue.warning { border-left-color: #dba617; }
				table { width: 100%; border-collapse: collapse; }
				th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
			</style>
		</head>
		<body>
			<h1>Accessibility Compliance Report</h1>
			<p>Generated on: <?php echo esc_html( gmdate( 'Y-m-d H:i:s' ) ); ?></p>
			<p>Total Pages Scanned: <?php echo count( $data ); ?></p>

			<?php foreach ( $data as $row ) : ?>
				<div class="page-report">
					<div class="page-header">
						<h2><?php echo esc_html( $row['post_title'] ); ?></h2>
						<p><a href="<?php echo esc_url( $row['post_url'] ); ?>"><?php echo esc_url( $row['post_url'] ); ?></a></p>
						<p>Scan Date: <?php echo esc_html( $row['scan_date'] ); ?></p>
					</div>
					
					<?php if ( empty( $row['results'] ) ) : ?>
						<p style="color: green;">No issues found.</p>
					<?php else : ?>
						<table>
							<thead>
								<tr>
									<th>Severity</th>
									<th>Issue</th>
									<th>Details</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $row['results'] as $check_id => $result ) : ?>
									<?php foreach ( $result['issues'] as $issue ) : ?>
										<tr>
											<td><span style="color: <?php echo 'critical' === $result['severity'] ? '#d63638' : '#dba617'; ?>; font-weight: bold;"><?php echo esc_html( ucfirst( $result['severity'] ) ); ?></span></td>
											<td><?php echo esc_html( $result['description'] ); ?></td>
											<td><?php echo esc_html( $issue['message'] ); ?></td>
										</tr>
									<?php endforeach; ?>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</body>
		</html>
		<?php
		exit;
	}
}

