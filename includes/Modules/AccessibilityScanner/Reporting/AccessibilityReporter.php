<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Reporting;

class AccessibilityReporter {

	public function __construct() {
		add_action( 'wp_ajax_slos_export_report', array( $this, 'ajax_export_report' ) );
	}

	public function ajax_export_report() {
		check_ajax_referer( 'slos_scanner_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$format  = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'csv';
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
				wp_send_json_error( 'Invalid format' );
		}
	}

	private function get_all_scan_results() {
		// Fetch all posts with scan results
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

		$query = new \WP_Query( $args );
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

	private function export_csv( $data ) {
		$filename = 'accessibility-report-' . date( 'Y-m-d' ) . '.csv';
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'Post ID', 'Title', 'URL', 'Scan Date', 'Issue Type', 'Severity', 'Description', 'Message' ) );

		foreach ( $data as $row ) {
			foreach ( $row['results'] as $check_id => $result ) {
				foreach ( $result['issues'] as $issue ) {
					fputcsv(
						$output,
						array(
							$row['post_id'],
							$row['post_title'],
							$row['post_url'],
							$row['scan_date'],
							$check_id,
							$result['severity'],
							$result['description'],
							$issue['message'],
						)
					);
				}
			}
		}

		fclose( $output );
		exit;
	}

	private function export_json( $data ) {
		$filename = 'accessibility-report-' . date( 'Y-m-d' ) . '.json';
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		echo json_encode( $data, JSON_PRETTY_PRINT );
		exit;
	}

	private function export_html( $data ) {
		$filename = 'accessibility-report-' . date( 'Y-m-d' ) . '.html';
		header( 'Content-Type: text/html' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>Accessibility Report - <?php echo date( 'Y-m-d' ); ?></title>
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
			<p>Generated on: <?php echo date( 'Y-m-d H:i:s' ); ?></p>
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
											<td><span style="color: <?php echo $result['severity'] === 'critical' ? '#d63638' : '#dba617'; ?>; font-weight: bold;"><?php echo esc_html( ucfirst( $result['severity'] ) ); ?></span></td>
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

