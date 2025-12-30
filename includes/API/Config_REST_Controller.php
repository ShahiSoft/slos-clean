<?php
/**
 * Config REST Controller Class
 *
 * Handles REST API endpoints for configuration export/import operations.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage API
 * @since      3.1.1
 */

namespace ShahiLegalFlowSuite\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use ShahiLegalFlowSuite\Services\Config_Sync_Service;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Config_REST_Controller Class
 *
 * REST API endpoints for config export/import.
 *
 * @since 3.1.1
 */
class Config_REST_Controller extends Base_REST_Controller {

	/**
	 * Config sync service
	 *
	 * @var Config_Sync_Service
	 */
	private $sync_service;

	/**
	 * Constructor
	 *
	 * @since 3.1.1
	 */
	public function __construct() {
		$this->rest_base    = 'config';
		$this->sync_service = new Config_Sync_Service();
	}

	/**
	 * Register REST API routes
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function register_routes() {
		// Export configuration
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/export',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'export_config' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => $this->get_export_params(),
				),
			)
		);

		// Import configuration
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/import',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'import_config' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => $this->get_import_params(),
				),
			)
		);

		// List available exports
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/exports',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'list_exports' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// Delete export file
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/exports/(?P<filename>[a-zA-Z0-9_\-\.]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_export' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// Validate configuration
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/validate',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'validate_config' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'profile' => array(
							'required' => true,
							'type'     => 'object',
						),
					),
				),
			)
		);

		// Compare configurations
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/compare',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'compare_configs' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'filename' => array(
							'required' => true,
							'type'     => 'string',
						),
					),
				),
			)
		);

		// Download export file
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/download/(?P<filename>[a-zA-Z0-9_\-\.]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'download_export' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
	}

	/**
	 * Export configuration
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function export_config( WP_REST_Request $request ) {
		$options  = $request->get_param( 'options' ) ?: array();
		$metadata = array(
			'name'        => $request->get_param( 'name' ) ?: 'Compliance Config ' . gmdate( 'Y-m-d' ),
			'description' => $request->get_param( 'description' ) ?: '',
		);

		$to_file = $request->get_param( 'to_file' ) ?? false;

		if ( $to_file ) {
			$filename = $request->get_param( 'filename' ) ?: 'slos-config';
			$result   = $this->sync_service->export_to_file( $options, $metadata, $filename );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return new WP_REST_Response(
				array(
					'success'  => true,
					'file'     => basename( $result ),
					'path'     => $result,
					'size'     => filesize( $result ),
					'url'      => $this->get_download_url( basename( $result ) ),
					'message'  => __( 'Configuration exported successfully', 'shahi-legalflowsuite' ),
				),
				200
			);
		}

		$profile = $this->sync_service->export_config( $options, $metadata );

		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		return new WP_REST_Response(
			array(
				'success'  => true,
				'profile'  => $profile,
				'message'  => __( 'Configuration exported successfully', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Import configuration
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function import_config( WP_REST_Request $request ) {
		$profile  = $request->get_param( 'profile' );
		$filename = $request->get_param( 'filename' );
		$options  = array(
			'merge'             => $request->get_param( 'merge' ) ?? false,
			'dry_run'           => $request->get_param( 'dry_run' ) ?? false,
			'selected_settings' => $request->get_param( 'selected_settings' ) ?? array(),
		);

		// Import from file if filename provided
		if ( ! empty( $filename ) ) {
			$upload_dir = wp_upload_dir();
			$filepath   = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports/' . basename( $filename );

			$result = $this->sync_service->import_from_file( $filepath, $options );
		} else {
			// Import from provided profile
			if ( empty( $profile ) ) {
				return new WP_Error(
					'missing_profile',
					__( 'Configuration profile or filename is required', 'shahi-legalflowsuite' ),
					array( 'status' => 400 )
				);
			}

			$result = $this->sync_service->import_config( $profile, $options );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$success_count = count( $result['imported'] );
		$error_count   = count( $result['errors'] );
		$warning_count = count( $result['warnings'] );

		return new WP_REST_Response(
			array(
				'success'  => $error_count === 0,
				'results'  => $result,
				'summary'  => array(
					'imported' => $success_count,
					'errors'   => $error_count,
					'warnings' => $warning_count,
					'skipped'  => count( $result['skipped'] ),
				),
				'message'  => $options['dry_run']
					? __( 'Dry run completed - no changes made', 'shahi-legalflowsuite' )
					: sprintf(
						/* translators: 1: success count, 2: error count */
						__( 'Import completed: %1$d imported, %2$d errors', 'shahi-legalflowsuite' ),
						$success_count,
						$error_count
					),
			),
			$error_count === 0 ? 200 : 207 // 207 Multi-Status if partial success
		);
	}

	/**
	 * List available export files
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response
	 */
	public function list_exports( WP_REST_Request $request ) {
		$exports = $this->sync_service->get_available_exports();

		return new WP_REST_Response(
			array(
				'success' => true,
				'exports' => $exports,
				'count'   => count( $exports ),
			),
			200
		);
	}

	/**
	 * Delete export file
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function delete_export( WP_REST_Request $request ) {
		$filename = $request->get_param( 'filename' );

		$result = $this->sync_service->delete_export( $filename );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Export file deleted successfully', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Validate configuration profile
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function validate_config( WP_REST_Request $request ) {
		$profile = $request->get_param( 'profile' );

		$validation = slos_validate_config_profile( $profile );

		if ( is_wp_error( $validation ) ) {
			return new WP_REST_Response(
				array(
					'valid'   => false,
					'errors'  => $validation->get_error_data(),
					'message' => $validation->get_error_message(),
				),
				200 // Return 200 with validation results, not an error status
			);
		}

		return new WP_REST_Response(
			array(
				'valid'    => true,
				'warnings' => $validation['warnings'],
				'message'  => __( 'Configuration is valid', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Compare configurations
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function compare_configs( WP_REST_Request $request ) {
		$filename = $request->get_param( 'filename' );

		// Get current config
		$current_profile = $this->sync_service->export_config();

		if ( is_wp_error( $current_profile ) ) {
			return $current_profile;
		}

		// Get imported config
		$upload_dir     = wp_upload_dir();
		$filepath       = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports/' . basename( $filename );
		$imported_json  = file_get_contents( $filepath );

		if ( false === $imported_json ) {
			return new WP_Error(
				'file_read_failed',
				__( 'Failed to read import file', 'shahi-legalflowsuite' ),
				array( 'status' => 500 )
			);
		}

		$imported_profile = json_decode( $imported_json, true );

		if ( null === $imported_profile ) {
			return new WP_Error(
				'json_parse_failed',
				__( 'Failed to parse import file', 'shahi-legalflowsuite' ),
				array( 'status' => 400 )
			);
		}

		$diff = $this->sync_service->compare_configs( $current_profile, $imported_profile );

		return new WP_REST_Response(
			array(
				'success' => true,
				'diff'    => $diff,
				'summary' => array(
					'added'    => count( $diff['added'] ),
					'removed'  => count( $diff['removed'] ),
					'changed'  => count( $diff['changed'] ),
					'unchanged' => count( $diff['unchanged'] ),
				),
			),
			200
		);
	}

	/**
	 * Download export file
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object
	 * @return void
	 */
	public function download_export( WP_REST_Request $request ) {
		$filename   = $request->get_param( 'filename' );
		$upload_dir = wp_upload_dir();
		$filepath   = trailingslashit( $upload_dir['basedir'] ) . 'slos-exports/' . basename( $filename );

		if ( ! file_exists( $filepath ) ) {
			wp_die( esc_html__( 'File not found', 'shahi-legalflowsuite' ), 404 );
		}

		// Set headers for download
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="' . basename( $filepath ) . '"' );
		header( 'Content-Length: ' . filesize( $filepath ) );

		// Output file
		readfile( $filepath );
		exit;
	}

	/**
	 * Get export parameters
	 *
	 * @since 3.1.1
	 * @return array Parameters
	 */
	private function get_export_params() {
		return array(
			'name' => array(
				'type'        => 'string',
				'default'     => '',
				'description' => __( 'Profile name', 'shahi-legalflowsuite' ),
			),
			'description' => array(
				'type'        => 'string',
				'default'     => '',
				'description' => __( 'Profile description', 'shahi-legalflowsuite' ),
			),
			'options' => array(
				'type'        => 'array',
				'default'     => array(),
				'description' => __( 'Options to export', 'shahi-legalflowsuite' ),
			),
			'to_file' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Export to file', 'shahi-legalflowsuite' ),
			),
			'filename' => array(
				'type'        => 'string',
				'default'     => 'slos-config',
				'description' => __( 'Filename for export', 'shahi-legalflowsuite' ),
			),
		);
	}

	/**
	 * Get import parameters
	 *
	 * @since 3.1.1
	 * @return array Parameters
	 */
	private function get_import_params() {
		return array(
			'profile' => array(
				'type'        => 'object',
				'description' => __( 'Configuration profile to import', 'shahi-legalflowsuite' ),
			),
			'filename' => array(
				'type'        => 'string',
				'description' => __( 'Filename to import from', 'shahi-legalflowsuite' ),
			),
			'merge' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Merge with existing settings', 'shahi-legalflowsuite' ),
			),
			'dry_run' => array(
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Test import without making changes', 'shahi-legalflowsuite' ),
			),
			'selected_settings' => array(
				'type'        => 'array',
				'default'     => array(),
				'description' => __( 'Specific settings to import', 'shahi-legalflowsuite' ),
			),
		);
	}

	/**
	 * Get download URL for export file
	 *
	 * @since 3.1.1
	 * @param string $filename Filename
	 * @return string Download URL
	 */
	private function get_download_url( $filename ) {
		return rest_url( $this->namespace . '/' . $this->rest_base . '/download/' . $filename );
	}
}
