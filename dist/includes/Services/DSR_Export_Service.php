<?php
/**
 * DSR Export Service
 *
 * Generates GDPR-compliant data export packages for data portability requests.
 * Collects data from WordPress core, plugins, and registered providers.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Services
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\DSR_Repository;
use ShahiLegalFlowSuite\Services\Consent_Service;
use ShahiLegalFlowSuite\Database\Repositories\Consent_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR Export Service Class
 *
 * Orchestrates data collection, formatting, and secure delivery.
 *
 * @since 3.0.1
 */
class DSR_Export_Service {

	/**
	 * DSR Repository instance
	 *
	 * @var DSR_Repository
	 */
	private $repository;

	/**
	 * Consent Service instance
	 *
	 * @var Consent_Service
	 */
	private $consent_service;

	/**
	 * Maximum export file size (100MB)
	 *
	 * @var int
	 */
	private $max_file_size = 104857600;

	/**
	 * Initialize service
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		$this->repository = new DSR_Repository();

		// Register default data providers
		add_filter( 'slos_dsr_data_providers', array( $this, 'register_core_providers' ), 10 );

		// Hook into export generation action
		add_action( 'slos_dsr_export_ready', array( $this, 'process_export_generation' ), 10, 3 );
	}

	/**
	 * Get consent service instance (lazy initialization)
	 *
	 * @since 3.1.1
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
	 * Register core WordPress data providers
	 *
	 * @since 3.0.1
	 * @param array $providers Existing providers
	 * @return array Updated providers
	 */
	public function register_core_providers( array $providers ): array {
		$providers['wordpress'] = array(
			'label'    => __( 'WordPress Core Data', 'shahi-legalflowsuite' ),
			'callback' => array( $this, 'collect_wordpress_data' ),
			'priority' => 10,
		);

		$providers['comments'] = array(
			'label'    => __( 'Comments', 'shahi-legalflowsuite' ),
			'callback' => array( $this, 'collect_comments' ),
			'priority' => 20,
		);

		$providers['consent'] = array(
			'label'    => __( 'Consent Records', 'shahi-legalflowsuite' ),
			'callback' => array( $this, 'collect_consent_data' ),
			'priority' => 30,
		);

		return $providers;
	}

	/**
	 * Process export generation (hooked to slos_dsr_export_ready)
	 *
	 * @since 3.0.1
	 * @param int    $request_id   Request ID
	 * @param string $export_token Export token
	 * @param object $request      Request object
	 * @return bool True on success
	 */
	public function process_export_generation( int $request_id, string $export_token, $request ): bool {
		try {
			// Collect all data
			$collected_data = $this->collect_all_data( $request );

			if ( empty( $collected_data ) ) {
				error_log( sprintf( 'DSR Export: No data collected for request %d', $request_id ) );
				return false;
			}

			// Generate export package
			$package_path = $this->generate_package( $request_id, $collected_data, $request );

			if ( ! $package_path ) {
				error_log( sprintf( 'DSR Export: Failed to generate package for request %d', $request_id ) );
				return false;
			}

			// Calculate file hash and size
			$file_hash = hash_file( 'sha256', $package_path );
			$file_size = filesize( $package_path );

			// Update request with export metadata
			$this->repository->update(
				$request_id,
				array(
					'export_file_path' => basename( $package_path ),
					'updated_at'       => current_time( 'mysql' ),
				)
			);

			// Store extended metadata in transient (7 days)
			$export_meta              = get_transient( 'slos_dsr_export_' . $request_id ) ?: array();
			$export_meta['file_hash'] = $file_hash;
			$export_meta['file_size'] = $file_size;
			$export_meta['file_path'] = basename( $package_path );
			set_transient( 'slos_dsr_export_' . $request_id, $export_meta, 7 * DAY_IN_SECONDS );

			// Generate download URL
			$download_url = $this->generate_download_url( $request_id, $export_token );

			// Send email with download link
			$this->send_export_email( $request, $download_url, $export_meta );

			// Log audit entry
			do_action(
				'slos_dsr_audit_log',
				$request_id,
				'export_generated',
				array(
					'file_size' => $file_size,
					'file_hash' => $file_hash,
				)
			);

			return true;

		} catch ( \Throwable $e ) {
			error_log( sprintf( 'DSR Export error for request %d: %s', $request_id, $e->getMessage() ) );
			return false;
		}
	}

	/**
	 * Collect all data from registered providers
	 *
	 * @since 3.0.1
	 * @param object $request Request object
	 * @return array Collected data organized by provider
	 */
	private function collect_all_data( $request ): array {
		$providers = apply_filters( 'slos_dsr_data_providers', array() );

		// Sort by priority
		uasort(
			$providers,
			function ( $a, $b ) {
				return ( $a['priority'] ?? 999 ) - ( $b['priority'] ?? 999 );
			}
		);

		$collected = array();

		foreach ( $providers as $key => $provider ) {
			if ( ! isset( $provider['callback'] ) || ! is_callable( $provider['callback'] ) ) {
				continue;
			}

			try {
				$data = call_user_func( $provider['callback'], $request );
				if ( ! empty( $data ) ) {
					$collected[ $key ] = array(
						'label' => $provider['label'] ?? ucwords( str_replace( '_', ' ', $key ) ),
						'data'  => $data,
					);
				}
			} catch ( \Throwable $e ) {
				error_log( sprintf( 'DSR Export: Provider "%s" failed: %s', $key, $e->getMessage() ) );
			}
		}

		return $collected;
	}

	/**
	 * Collect WordPress core data (user profile)
	 *
	 * @since 3.0.1
	 * @param object $request Request object
	 * @return array User data
	 */
	public function collect_wordpress_data( $request ): array {
		$data = array();

		// If user_id is set, get user data
		if ( ! empty( $request->user_id ) ) {
			$user = get_userdata( $request->user_id );
			if ( $user ) {
				$data['user_profile'] = array(
					'ID'              => $user->ID,
					'user_login'      => $user->user_login,
					'user_email'      => $user->user_email,
					'user_registered' => $user->user_registered,
					'display_name'    => $user->display_name,
					'first_name'      => $user->first_name,
					'last_name'       => $user->last_name,
					'nickname'        => $user->nickname,
					'description'     => $user->description,
					'roles'           => $user->roles,
				);

				// User meta (exclude sensitive keys)
				$meta_keys = get_user_meta( $user->ID );
				$safe_meta = array();
				$excluded  = array( 'session_tokens', 'password', 'activation_key' );

				foreach ( $meta_keys as $key => $values ) {
					if ( ! in_array( $key, $excluded, true ) && ! str_starts_with( $key, '_' ) ) {
						$safe_meta[ $key ] = maybe_unserialize( $values[0] );
					}
				}

				$data['user_meta'] = $safe_meta;
			}
		}

		return $data;
	}

	/**
	 * Collect comments data
	 *
	 * @since 3.0.1
	 * @param object $request Request object
	 * @return array Comments data
	 */
	public function collect_comments( $request ): array {
		$data = array();

		if ( ! empty( $request->user_id ) ) {
			$comments = get_comments(
				array(
					'user_id' => $request->user_id,
					'status'  => 'all',
				)
			);

			if ( ! empty( $comments ) ) {
				$data['comments'] = array_map(
					function ( $comment ) {
						return array(
							'comment_ID'           => $comment->comment_ID,
							'comment_post_ID'      => $comment->comment_post_ID,
							'comment_author'       => $comment->comment_author,
							'comment_author_email' => $comment->comment_author_email,
							'comment_date'         => $comment->comment_date,
							'comment_content'      => $comment->comment_content,
							'comment_approved'     => $comment->comment_approved,
						);
					},
					$comments
				);
			}
		} else {
			// Use email for non-registered users
			$comments = get_comments(
				array(
					'author_email' => $request->requester_email,
					'status'       => 'all',
				)
			);

			if ( ! empty( $comments ) ) {
				$data['comments'] = array_map(
					function ( $comment ) {
						return array(
							'comment_ID'       => $comment->comment_ID,
							'comment_post_ID'  => $comment->comment_post_ID,
							'comment_author'   => $comment->comment_author,
							'comment_date'     => $comment->comment_date,
							'comment_content'  => $comment->comment_content,
							'comment_approved' => $comment->comment_approved,
						);
					},
					$comments
				);
			}
		}

		return $data;
	}

	/**
	 * Collect consent records
	 *
	 * @since 3.0.1
	 * @param object $request Request object
	 * @return array Consent data
	 */
	public function collect_consent_data( $request ): array {
		global $wpdb;

		$data  = array();
		$email = $request->requester_email;

		// Collect from new consent system (slos_consent table)
		if ( ! empty( $email ) ) {
			$consents = $this->get_consent_service()->get_by_email( $email );

			if ( ! empty( $consents ) ) {
				$data['consent_records'] = array_map(
					function ( $consent ) {
						return array(
							'id'         => $consent['id'] ?? 0,
							'type'       => $consent['type'] ?? '',
							'status'     => $consent['status'] ?? '',
							'user_name'  => $consent['user_name'] ?? '',
							'user_email' => $consent['user_email'] ?? '',
							'metadata'   => $consent['metadata'] ?? array(),
							'created_at' => $consent['created_at'] ?? '',
							'updated_at' => $consent['updated_at'] ?? '',
						);
					},
					$consents
				);
			}
		}

		// Collect from legacy consent_logs table (if exists)
		$table = $wpdb->prefix . 'slos_consent_logs';

		// Check if table exists.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			// Query consent logs
			$where  = array();
			$values = array();

			if ( $user_id ) {
				$where[]  = 'user_id = %d';
				$values[] = $user_id;
			}

			if ( $email ) {
				$where[]  = 'email = %s';
				$values[] = $email;
			}

			if ( ! empty( $where ) ) {
				$where_sql = implode( ' OR ', $where );
				$sql       = "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC";

                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$results = $wpdb->get_results( $wpdb->prepare( $sql, $values ) );

				if ( ! empty( $results ) ) {
					$data['consent_logs'] = array_map(
						function ( $row ) {
							return array(
								'id'          => $row->id,
								'action'      => $row->action ?? '',
								'preferences' => maybe_unserialize( $row->preferences ?? '' ),
								'created_at'  => $row->created_at ?? '',
								'ip_address'  => isset( $row->ip_address ) ? wp_privacy_anonymize_ip( $row->ip_address ) : '',
							);
						},
						$results
					);
				}
			}
		}

		return $data;
	}

	/**
	 * Generate export package (JSON, CSV, XML, PDF, ZIP)
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID
	 * @param array  $data       Collected data
	 * @param object $request    Request object
	 * @return string|false Package file path or false
	 */
	private function generate_package( int $request_id, array $data, $request ) {
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports';

		// Create exports directory with protection
		if ( ! file_exists( $export_dir ) ) {
			wp_mkdir_p( $export_dir );

			// Add index.php to prevent directory listing
			file_put_contents( $export_dir . '/index.php', '<?php // Silence is golden' );

			// Add .htaccess for Apache
			file_put_contents( $export_dir . '/.htaccess', 'Deny from all' );
		}

		$timestamp = gmdate( 'Y-m-d_H-i-s' );
		$basename  = sprintf( 'dsr-export-%d-%s', $request_id, $timestamp );
		$temp_dir  = $export_dir . '/' . $basename;

		wp_mkdir_p( $temp_dir );

		// Metadata for package
		$metadata = array(
			'generated_at'    => current_time( 'mysql' ),
			'request_id'      => $request_id,
			'request_type'    => $request->request_type ?? 'access',
			'regulation'      => $request->regulation ?? 'GDPR',
			'sla_deadline'    => $request->sla_deadline ?? '',
			'processor'       => get_bloginfo( 'name' ),
			'processor_email' => get_option( 'admin_email' ),
		);

		// Generate JSON
		$json_data = array(
			'metadata' => $metadata,
			'data'     => $data,
		);
		file_put_contents( $temp_dir . '/export.json', wp_json_encode( $json_data, JSON_PRETTY_PRINT ) );

		// Generate CSV files (one per data section)
		foreach ( $data as $key => $section ) {
			if ( isset( $section['data'] ) && is_array( $section['data'] ) ) {
				$this->generate_csv( $temp_dir . '/' . $key . '.csv', $section['data'] );
			}
		}

		// Generate XML
		$this->generate_xml( $temp_dir . '/export.xml', $json_data );

		// Generate PDF summary
		$this->generate_pdf_summary( $temp_dir . '/summary.pdf', $metadata, $data );

		// Create README
		$this->generate_readme( $temp_dir . '/README.txt', $metadata );

		// Create ZIP package
		$zip_path = $export_dir . '/' . $basename . '.zip';

		if ( ! $this->create_zip( $temp_dir, $zip_path ) ) {
			error_log( 'DSR Export: Failed to create ZIP package' );
			return false;
		}

		// Clean up temp directory
		$this->remove_directory( $temp_dir );

		// Check file size
		if ( filesize( $zip_path ) > $this->max_file_size ) {
			error_log( sprintf( 'DSR Export: Package exceeds max size (%d bytes)', filesize( $zip_path ) ) );
		}

		return $zip_path;
	}

	/**
	 * Generate CSV file from data array
	 *
	 * @since 3.0.1
	 * @param string $path Target file path
	 * @param array  $data Data to convert
	 * @return bool Success status
	 */
	private function generate_csv( string $path, array $data ): bool {
		$handle = fopen( $path, 'w' );
		if ( ! $handle ) {
			return false;
		}

		// Flatten nested arrays for CSV
		$rows = $this->flatten_for_csv( $data );

		if ( ! empty( $rows ) ) {
			// Write headers
			fputcsv( $handle, array_keys( $rows[0] ) );

			// Write rows
			foreach ( $rows as $row ) {
				fputcsv( $handle, $row );
			}
		}

		fclose( $handle );
		return true;
	}

	/**
	 * Flatten nested array for CSV export
	 *
	 * @since 3.0.1
	 * @param array $data Data to flatten
	 * @return array Flattened rows
	 */
	private function flatten_for_csv( array $data ): array {
		$rows = array();

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				// Check if indexed array of objects/arrays
				if ( isset( $value[0] ) && is_array( $value[0] ) ) {
					foreach ( $value as $item ) {
						$rows[] = $this->flatten_array( $item );
					}
				} else {
					// Single associative array
					$rows[] = $this->flatten_array( $value );
				}
			} else {
				$rows[] = array( $key => $value );
			}
		}

		return $rows;
	}

	/**
	 * Flatten single array (non-recursive for CSV)
	 *
	 * @since 3.0.1
	 * @param array $arr Array to flatten
	 * @return array Flattened array
	 */
	private function flatten_array( array $arr ): array {
		$result = array();

		foreach ( $arr as $key => $value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				$result[ $key ] = wp_json_encode( $value );
			} else {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Generate XML file
	 *
	 * @since 3.0.1
	 * @param string $path Target file path
	 * @param array  $data Data to convert
	 * @return bool Success status
	 */
	private function generate_xml( string $path, array $data ): bool {
		$xml = new \SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><export></export>' );
		$this->array_to_xml( $data, $xml );
		return (bool) file_put_contents( $path, $xml->asXML() );
	}

	/**
	 * Convert array to XML recursively
	 *
	 * @since 3.0.1
	 * @param array             $data   Data array
	 * @param \SimpleXMLElement $xml    XML object
	 * @param string            $parent Parent key
	 * @return void
	 */
	private function array_to_xml( array $data, \SimpleXMLElement $xml, string $parent = '' ): void {
		foreach ( $data as $key => $value ) {
			$key = is_numeric( $key ) ? ( $parent ?: 'item' ) : $key;
			$key = preg_replace( '/[^a-zA-Z0-9_]/', '_', $key );

			if ( is_array( $value ) ) {
				$child = $xml->addChild( $key );
				$this->array_to_xml( $value, $child, $key );
			} else {
				$xml->addChild( $key, htmlspecialchars( (string) $value ) );
			}
		}
	}

	/**
	 * Generate PDF summary
	 *
	 * @since 3.0.1
	 * @param string $path     Target file path
	 * @param array  $metadata Metadata
	 * @param array  $data     Collected data
	 * @return bool Success status
	 */
	private function generate_pdf_summary( string $path, array $metadata, array $data ): bool {
		// Simple text-based summary (PDF libraries not included by default)
		// For production, integrate TCPDF, mPDF, or similar
		$content  = "DATA SUBJECT REQUEST EXPORT SUMMARY\n";
		$content .= str_repeat( '=', 80 ) . "\n\n";

		$content .= 'Request ID: ' . ( $metadata['request_id'] ?? 'N/A' ) . "\n";
		$content .= 'Request Type: ' . ( $metadata['request_type'] ?? 'N/A' ) . "\n";
		$content .= 'Regulation: ' . ( $metadata['regulation'] ?? 'N/A' ) . "\n";
		$content .= 'Generated: ' . ( $metadata['generated_at'] ?? 'N/A' ) . "\n";
		$content .= 'Processor: ' . ( $metadata['processor'] ?? 'N/A' ) . "\n";
		$content .= 'Contact: ' . ( $metadata['processor_email'] ?? 'N/A' ) . "\n";
		$content .= "\n" . str_repeat( '=', 80 ) . "\n\n";

		$content .= "DATA SECTIONS INCLUDED:\n\n";
		foreach ( $data as $key => $section ) {
			$label    = $section['label'] ?? ucwords( str_replace( '_', ' ', $key ) );
			$count    = is_array( $section['data'] ?? null ) ? count( $section['data'] ) : 0;
			$content .= sprintf( "- %s (%d records)\n", $label, $count );
		}

		$content .= "\n" . str_repeat( '=', 80 ) . "\n";
		$content .= "For detailed data, please refer to the JSON, CSV, and XML files included in this package.\n";

		return (bool) file_put_contents( $path, $content );
	}

	/**
	 * Generate README file
	 *
	 * @since 3.0.1
	 * @param string $path     Target file path
	 * @param array  $metadata Metadata
	 * @return bool Success status
	 */
	private function generate_readme( string $path, array $metadata ): bool {
		$content  = "DATA SUBJECT REQUEST EXPORT\n";
		$content .= str_repeat( '=', 80 ) . "\n\n";

		$content .= "This package contains your personal data as requested under data protection regulations.\n\n";

		$content .= "REQUEST DETAILS:\n";
		$content .= '- Request ID: ' . ( $metadata['request_id'] ?? 'N/A' ) . "\n";
		$content .= '- Type: ' . ( $metadata['request_type'] ?? 'N/A' ) . "\n";
		$content .= '- Regulation: ' . ( $metadata['regulation'] ?? 'N/A' ) . "\n";
		$content .= '- Generated: ' . ( $metadata['generated_at'] ?? 'N/A' ) . "\n\n";

		$content .= "FILES INCLUDED:\n";
		$content .= "- export.json - Complete data in JSON format\n";
		$content .= "- *.csv - Data tables in CSV format\n";
		$content .= "- export.xml - Complete data in XML format\n";
		$content .= "- summary.pdf - Human-readable summary\n";
		$content .= "- README.txt - This file\n\n";

		$content .= "SECURITY NOTE:\n";
		$content .= "This file contains personal data. Please store it securely and delete it when no longer needed.\n";
		$content .= "Download link expires in 7 days from generation.\n\n";

		$content .= "CONTACT:\n";
		$content .= 'Processor: ' . ( $metadata['processor'] ?? 'N/A' ) . "\n";
		$content .= 'Email: ' . ( $metadata['processor_email'] ?? 'N/A' ) . "\n";

		return (bool) file_put_contents( $path, $content );
	}

	/**
	 * Create ZIP archive from directory
	 *
	 * @since 3.0.1
	 * @param string $source_dir Source directory
	 * @param string $zip_path   Target ZIP path
	 * @return bool Success status
	 */
	private function create_zip( string $source_dir, string $zip_path ): bool {
		if ( ! class_exists( 'ZipArchive' ) ) {
			error_log( 'DSR Export: ZipArchive class not available' );
			return false;
		}

		$zip = new \ZipArchive();

		if ( $zip->open( $zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) !== true ) {
			return false;
		}

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $source_dir ),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ( $files as $file ) {
			if ( ! $file->isDir() ) {
				$file_path     = $file->getRealPath();
				$relative_path = substr( $file_path, strlen( $source_dir ) + 1 );
				$zip->addFile( $file_path, $relative_path );
			}
		}

		return $zip->close();
	}

	/**
	 * Remove directory recursively
	 *
	 * @since 3.0.1
	 * @param string $dir Directory path
	 * @return bool Success status
	 */
	private function remove_directory( string $dir ): bool {
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		$files = array_diff( scandir( $dir ), array( '.', '..' ) );

		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->remove_directory( $path ) : unlink( $path );
		}

		return rmdir( $dir );
	}

	/**
	 * Generate download URL with token
	 *
	 * @since 3.0.1
	 * @param int    $request_id   Request ID
	 * @param string $export_token Export token
	 * @return string Download URL
	 */
	private function generate_download_url( int $request_id, string $export_token ): string {
		return add_query_arg(
			array(
				'slos_dsr_download' => $request_id,
				'token'             => $export_token,
			),
			home_url()
		);
	}

	/**
	 * Send export ready email
	 *
	 * @since 3.0.1
	 * @param object $request      Request object
	 * @param string $download_url Download URL
	 * @param array  $export_meta  Export metadata
	 * @return bool Success status
	 */
	private function send_export_email( $request, string $download_url, array $export_meta ): bool {
		$to      = $request->requester_email;
		$subject = sprintf(
			__( 'Your Data Export is Ready - Request #%d', 'shahi-legalflowsuite' ),
			$request->id ?? 0
		);

		$size_mb = isset( $export_meta['file_size'] ) ? round( $export_meta['file_size'] / 1048576, 2 ) : 0;
		$expires = $export_meta['export_expires'] ?? '';

		$message = sprintf(
			__( "Your data export package is ready for download.\n\nRequest ID: %1\$d\nFile Size: %2\$s MB\nExpires: %3\$s\n\nDownload Link:\n%4\$s\n\nThis link is valid for 7 days and can only be used once.\n\nIf you did not request this export, please contact us immediately.", 'shahi-legalflowsuite' ),
			$request->id ?? 0,
			$size_mb,
			$expires,
			$download_url
		);

		return wp_mail( $to, $subject, $message );
	}

	/**
	 * Handle download request
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function handle_download_request(): void {
		if ( ! isset( $_GET['slos_dsr_download'], $_GET['token'] ) ) {
			return;
		}

		$request_id = absint( $_GET['slos_dsr_download'] );
		$token      = sanitize_text_field( wp_unslash( $_GET['token'] ) );

		// Verify token
		$export_meta = get_transient( 'slos_dsr_export_' . $request_id );

		if ( ! $export_meta || ! isset( $export_meta['export_token'] ) || $export_meta['export_token'] !== $token ) {
			wp_die( esc_html__( 'Invalid or expired download link.', 'shahi-legalflowsuite' ), 403 );
		}

		// Check expiry
		if ( isset( $export_meta['export_expires'] ) && strtotime( $export_meta['export_expires'] ) < time() ) {
			wp_die( esc_html__( 'Download link has expired.', 'shahi-legalflowsuite' ), 403 );
		}

		// Get file path
		$upload_dir = wp_upload_dir();
		$export_dir = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports';
		$file_name  = $export_meta['file_path'] ?? '';
		$file_path  = $export_dir . '/' . $file_name;

		if ( ! file_exists( $file_path ) ) {
			wp_die( esc_html__( 'Export file not found.', 'shahi-legalflowsuite' ), 404 );
		}

		// Verify hash
		if ( isset( $export_meta['file_hash'] ) ) {
			$current_hash = hash_file( 'sha256', $file_path );
			if ( $current_hash !== $export_meta['file_hash'] ) {
				error_log( sprintf( 'DSR Export: Hash mismatch for request %d', $request_id ) );
				wp_die( esc_html__( 'File integrity check failed.', 'shahi-legalflowsuite' ), 500 );
			}
		}

		// Log download
		do_action(
			'slos_dsr_audit_log',
			$request_id,
			'export_downloaded',
			array(
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			)
		);

		// Delete token (single use)
		delete_transient( 'slos_dsr_export_' . $request_id );

		// Stream file
		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		readfile( $file_path );

		// Delete file after download
		unlink( $file_path );

		exit;
	}
}
