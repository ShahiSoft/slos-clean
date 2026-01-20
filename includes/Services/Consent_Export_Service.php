<?php
/**
 * Consent Export/Import Service
 *
 * Handles export and import operations for consent records.
 * Supports CSV, JSON, and PDF formats for data export.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\Consent_Repository;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Export_Service Class
 *
 * Provides export/import functionality for consent records.
 *
 * @since 3.0.1
 */
class Consent_Export_Service extends Base_Service {

	/**
	 * Consent repository instance
	 *
	 * @since 3.0.1
	 * @var Consent_Repository
	 */
	protected $repository;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		parent::__construct();
		$this->repository = new Consent_Repository();
	}

	/**
	 * Export consents to specified format
	 *
	 * @since 3.0.1
	 * @param array $args {
	 *     Export arguments.
	 *
	 *     @type string $format    Export format: csv|json|pdf (default: csv)
	 *     @type int    $user_id   Filter by user ID
	 *     @type string $type      Filter by consent type
	 *     @type string $status    Filter by status
	 *     @type string $date_from Start date (Y-m-d format)
	 *     @type string $date_to   End date (Y-m-d format)
	 *     @type int    $limit     Maximum records (default: 10000)
	 * }
	 * @return string|array Export content or file path
	 */
	public function export( array $args = array() ) {
		$defaults = array(
			'format'    => get_option( 'slos_export_format', 'csv' ),
			'user_id'   => null,
			'type'      => null,
			'status'    => null,
			'date_from' => null,
			'date_to'   => null,
			'limit'     => 10000,
		);

		$args = wp_parse_args( $args, $defaults );

		// Validate format..
		$allowed_formats = array( 'csv', 'json', 'pdf' );
		if ( ! in_array( $args['format'], $allowed_formats, true ) ) {
			$args['format'] = 'csv';
		}

		// Get consent records..
		$items = $this->repository->find_export( $args );

		// Apply filter for custom processing..
		$items = apply_filters( 'slos_export_items', $items, $args );

		// Export based on format..
		switch ( $args['format'] ) {
			case 'json':
				return $this->export_json( $items );
			case 'pdf':
				return $this->export_pdf( $items );
			case 'csv':
			default:
				return $this->export_csv( $items );
		}
	}

	/**
	 * Export to CSV format
	 *
	 * @since 3.0.1
	 * @param array $items Consent records.
	 * @return string CSV content
	 */
	private function export_csv( array $items ): string {
		// Use output buffering instead of fopen for CSV generation.
		ob_start();
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Using PHP output stream for CSV, WP_Filesystem is not appropriate.
		$fh = fopen( 'php://output', 'w' );

		if ( false === $fh ) {
			ob_end_clean();
			return '';
		}

		// CSV Header..
		fputcsv(
			$fh,
			array(
				'ID',
				'User ID',
				'Type',
				'Status',
				'IP Hash',
				'User Agent',
				'Metadata',
				'Created At',
				'Updated At',
			)
		);

		// CSV Rows..
		foreach ( $items as $item ) {
			fputcsv(
				$fh,
				array(
					$item['id'] ?? '',
					$item['user_id'] ?? '',
					$item['type'] ?? '',
					$item['status'] ?? '',
					$item['ip_hash'] ?? '',
					$item['user_agent'] ?? '',
					! empty( $item['metadata'] ) ? wp_json_encode( maybe_unserialize( $item['metadata'] ) ) : '',
					$item['created_at'] ?? '',
					$item['updated_at'] ?? '',
				)
			);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing PHP output stream handle.
		fclose( $fh );
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Export to JSON format
	 *
	 * @since 3.0.1
	 * @param array $items Consent records.
	 * @return string JSON content
	 */
	private function export_json( array $items ): string {
		// Unserialize metadata for proper JSON encoding..
		$processed_items = array_map(
			function ( $item ) {
				if ( ! empty( $item['metadata'] ) ) {
					$item['metadata'] = maybe_unserialize( $item['metadata'] );
				}
				return $item;
			},
			$items
		);

		return wp_json_encode(
			array(
				'exported_at' => current_time( 'mysql' ),
				'total'       => count( $processed_items ),
				'items'       => $processed_items,
			),
			JSON_PRETTY_PRINT
		);
	}

	/**
	 * Export to PDF format (HTML for PDF rendering)
	 *
	 * @since 3.0.1
	 * @param array $items Consent records.
	 * @return string HTML content for PDF conversion
	 */
	private function export_pdf( array $items ): string {
		$html  = '<!DOCTYPE html>';
		$html .= '<html><head><meta charset="UTF-8">';
		$html .= '<title>Consent Export</title>';
		$html .= '<style>';
		$html .= 'body { font-family: Arial, sans-serif; font-size: 12px; }';
		$html .= 'h1 { color: #333; margin-bottom: 20px; }';
		$html .= 'table { width: 100%; border-collapse: collapse; margin-top: 10px; }';
		$html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
		$html .= 'th { background-color: #f4f4f4; font-weight: bold; }';
		$html .= 'tr:nth-child(even) { background-color: #f9f9f9; }';
		$html .= '.meta { font-size: 10px; color: #666; margin-bottom: 20px; }';
		$html .= '</style></head><body>';

		$html .= '<h1>Consent Export Report</h1>';
		$html .= '<div class="meta">';
		$html .= '<strong>Generated:</strong> ' . esc_html( current_time( 'mysql' ) ) . '<br>';
		$html .= '<strong>Total Records:</strong> ' . count( $items );
		$html .= '</div>';

		$html .= '<table>';
		$html .= '<thead><tr>';
		$html .= '<th>ID</th><th>User ID</th><th>Type</th><th>Status</th>';
		$html .= '<th>IP Hash</th><th>Created At</th>';
		$html .= '</tr></thead><tbody>';

		foreach ( $items as $item ) {
			$html .= '<tr>';
			$html .= '<td>' . esc_html( $item['id'] ?? '' ) . '</td>';
			$html .= '<td>' . esc_html( $item['user_id'] ?? '' ) . '</td>';
			$html .= '<td>' . esc_html( $item['type'] ?? '' ) . '</td>';
			$html .= '<td>' . esc_html( $item['status'] ?? '' ) . '</td>';
			$html .= '<td>' . esc_html( substr( $item['ip_hash'] ?? '', 0, 16 ) . '...' ) . '</td>';
			$html .= '<td>' . esc_html( $item['created_at'] ?? '' ) . '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody></table>';
		$html .= '</body></html>';

		return $html;
	}

	/**
	 * Import consents from array data
	 *
	 * @since 3.0.1
	 * @param array $data Array of consent rows to import.
	 * @return array Summary with imported and skipped counts
	 */
	public function import( array $data ): array {
		$imported = 0;
		$skipped  = 0;
		$errors   = array();

		foreach ( $data as $index => $row ) {
			$validated = $this->validate_import_row( $row );

			if ( is_wp_error( $validated ) ) {
				++$skipped;
				$errors[] = sprintf(
					/* translators: 1: Row index, 2: Error message */
					__( 'Row %1$d: %2$s', 'shahi-legalflowsuite' ),
					$index + 1,
					$validated->get_error_message()
				);
				continue;
			}

			// Prepare consent data..
			$consent_data = array(
				'user_id'    => (int) $row['user_id'],
				'type'       => sanitize_text_field( $row['type'] ),
				'status'     => sanitize_text_field( $row['status'] ?? 'pending' ),
				'ip_hash'    => sanitize_text_field( $row['ip_hash'] ?? '' ),
				'user_agent' => sanitize_text_field( $row['user_agent'] ?? '' ),
				'metadata'   => ! empty( $row['metadata'] ) ? maybe_serialize( $row['metadata'] ) : '',
				'created_at' => ! empty( $row['created_at'] ) ? $row['created_at'] : current_time( 'mysql' ),
			);

			// Create consent record..
			$result = $this->repository->create( $consent_data );

			if ( $result ) {
				++$imported;

				// Fire action for successful import..
				do_action( 'slos_consent_imported', $result, $consent_data );
			} else {
				++$skipped;
				$errors[] = sprintf(
					/* translators: %d: Row index */
					__( 'Row %d: Failed to create record', 'shahi-legalflowsuite' ),
					$index + 1
				);
			}
		}

		return array(
			'imported' => $imported,
			'skipped'  => $skipped,
			'errors'   => $errors,
		);
	}

	/**
	 * Validate import row data
	 *
	 * @since 3.0.1
	 * @param array $row Row data to validate.
	 * @return true|\WP_Error True if valid, WP_Error otherwise
	 */
	private function validate_import_row( array $row ) {
		// Required fields..
		if ( empty( $row['user_id'] ) ) {
			return new \WP_Error(
				'missing_user_id',
				__( 'Missing required field: user_id', 'shahi-legalflowsuite' )
			);
		}

		if ( empty( $row['type'] ) ) {
			return new \WP_Error(
				'missing_type',
				__( 'Missing required field: type', 'shahi-legalflowsuite' )
			);
		}

		// Validate user exists..
		$user = get_user_by( 'id', (int) $row['user_id'] );
		if ( ! $user ) {
			return new \WP_Error(
				'invalid_user',
				__( 'User ID does not exist', 'shahi-legalflowsuite' )
			);
		}

		// Validate consent type..
		$allowed_types = array( 'necessary', 'functional', 'analytics', 'marketing', 'personalization' );
		if ( ! in_array( $row['type'], $allowed_types, true ) ) {
			return new \WP_Error(
				'invalid_type',
				__( 'Invalid consent type', 'shahi-legalflowsuite' )
			);
		}

		// Validate status..
		$allowed_statuses = array( 'pending', 'accepted', 'rejected', 'withdrawn' );
		if ( ! empty( $row['status'] ) && ! in_array( $row['status'], $allowed_statuses, true ) ) {
			return new \WP_Error(
				'invalid_status',
				__( 'Invalid status value', 'shahi-legalflowsuite' )
			);
		}

		return true;
	}

	/**
	 * Parse CSV file to array
	 *
	 * @since 3.0.1
	 * @param string $file_path Path to CSV file.
	 * @return array|WP_Error Array of rows or WP_Error on failure
	 */
	public function parse_csv( string $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return new \WP_Error(
				'file_not_found',
				__( 'CSV file not found', 'shahi-legalflowsuite' )
			);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Direct file access needed for CSV parsing.
		$fh = fopen( $file_path, 'r' );
		if ( false === $fh ) {
			return new \WP_Error(
				'file_open_failed',
				__( 'Failed to open CSV file', 'shahi-legalflowsuite' )
			);
		}

		$rows   = array();
		$header = fgetcsv( $fh );

		if ( false === $header ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing file opened above.
			fclose( $fh );
			return new \WP_Error(
				'invalid_csv',
				__( 'Invalid CSV format', 'shahi-legalflowsuite' )
			);
		}

		// Normalize headers..
		$header = array_map( 'strtolower', $header );
		$header = array_map(
			function ( $h ) {
				return str_replace( ' ', '_', trim( $h ) );
			},
			$header
		);

		// phpcs:ignore -- Standard PHP pattern for reading CSV.
		while ( ( $data = fgetcsv( $fh ) ) !== false ) {
			if ( count( $data ) === count( $header ) ) {
				$rows[] = array_combine( $header, $data );
			}
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Closing file opened above.
		fclose( $fh );

		return $rows;
	}

	/**
	 * Schedule export for cron execution
	 *
	 * @since 3.0.1
	 * @param array $args Export arguments.
	 * @return bool True if scheduled successfully
	 */
	public function schedule_export( array $args = array() ): bool {
		$enabled = get_option( 'slos_scheduled_exports_enabled', false );

		if ( ! $enabled ) {
			return false;
		}

		if ( ! wp_next_scheduled( 'slos_export_consents_weekly' ) ) {
			wp_schedule_event( time(), 'weekly', 'slos_export_consents_weekly', array( $args ) );
			return true;
		}

		return false;
	}

	/**
	 * Execute scheduled export
	 *
	 * @since 3.0.1
	 * @param array $args Export arguments.
	 * @return bool True if export successful
	 */
	public function run_scheduled_export( array $args = array() ): bool {
		$format = get_option( 'slos_export_format', 'csv' );
		$args   = wp_parse_args( $args, array( 'format' => $format ) );

		$data = $this->export( $args );

		if ( empty( $data ) ) {
			return false;
		}

		// Save to uploads directory..
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports/';

		if ( ! file_exists( $export_dir ) ) {
			wp_mkdir_p( $export_dir );
		}

		$filename = sprintf(
			'consent-export-%s.%s',
			gmdate( 'Y-m-d-His' ),
			$args['format']
		);

		$file_path = $export_dir . $filename;

		// Use WP_Filesystem for file operations.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$result = $wp_filesystem->put_contents( $file_path, $data );

		if ( false !== $result ) {
			// Fire action for successful export..
			do_action( 'slos_scheduled_export_completed', $file_path, $args );

			// Optional: Send email notification to admin..
			$this->send_export_notification( $file_path );

			return true;
		}

		return false;
	}

	/**
	 * Send export notification email
	 *
	 * @since 3.0.1
	 * @param string $file_path Path to export file.
	 * @return bool True if email sent
	 */
	private function send_export_notification( string $file_path ): bool {
		$send_email = get_option( 'slos_export_email_notification', false );

		if ( ! $send_email ) {
			return false;
		}

		$admin_email = get_option( 'admin_email' );
		$subject     = __( 'Consent Export Completed', 'shahi-legalflowsuite' );
		$message     = sprintf(
			/* translators: %s: File path */
			__( 'Your scheduled consent export has been completed. File saved to: %s', 'shahi-legalflowsuite' ),
			$file_path
		);

		return wp_mail( $admin_email, $subject, $message );
	}
}
