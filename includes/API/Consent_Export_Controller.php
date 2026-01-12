<?php
/**
 * Consent Export/Import REST Controller
 *
 * Handles REST API endpoints for consent export and import operations.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\API;

use ShahiLegalFlowSuite\Services\Consent_Export_Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Export_Controller Class
 *
 * REST API endpoints for consent export/import.
 *
 * @since 3.0.1
 */
class Consent_Export_Controller extends Base_REST_Controller {

	/**
	 * REST base
	 *
	 * @since 3.0.1
	 * @var string
	 */
	protected $rest_base = 'consents/export';

	/**
	 * Export service instance
	 *
	 * @since 3.0.1
	 * @var Consent_Export_Service
	 */
	private $service;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		$this->service = new Consent_Export_Service();
	}

	/**
	 * Register REST routes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_routes() {
		// Export endpoint..
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'export' ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
					'args'                => $this->get_export_args(),
				),
			)
		);

		// Import endpoint..
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/import',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'import' ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
				),
			)
		);

		// Download endpoint (triggers file download)..
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/download',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'download' ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
					'args'                => $this->get_export_args(),
				),
			)
		);
	}

	/**
	 * Export consents
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object or error
	 */
	public function export( WP_REST_Request $request ) {
		$args = array(
			'format'    => $request->get_param( 'format' ),
			'user_id'   => $request->get_param( 'user_id' ),
			'type'      => $request->get_param( 'type' ),
			'status'    => $request->get_param( 'status' ),
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
			'limit'     => $request->get_param( 'limit' ),
		);

		// Remove null values..
		$args = array_filter( $args, fn( $value ) => ! is_null( $value ) );

		try {
			$data = $this->service->export( $args );

			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => $data,
					'format'  => $args['format'] ?? 'csv',
				),
				200
			);
		} catch ( \Exception $e ) {
			return new WP_Error(
				'export_failed',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Download export file
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return void Sends file download headers and exits
	 */
	public function download( WP_REST_Request $request ) {
		$args = array(
			'format'    => $request->get_param( 'format' ),
			'user_id'   => $request->get_param( 'user_id' ),
			'type'      => $request->get_param( 'type' ),
			'status'    => $request->get_param( 'status' ),
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
			'limit'     => $request->get_param( 'limit' ),
		);

		// Remove null values..
		$args = array_filter( $args, fn( $value ) => ! is_null( $value ) );

		$data   = $this->service->export( $args );
		$format = $args['format'] ?? 'csv';

		// Set appropriate content type..
		$content_types = array(
			'csv'  => 'text/csv',
			'json' => 'application/json',
			'pdf'  => 'text/html', // HTML for PDF conversion
		);

		$content_type = $content_types[ $format ] ?? 'text/plain';

		// Set download headers..
		header( 'Content-Type: ' . $content_type );
		header( 'Content-Disposition: attachment; filename="consent-export-' . gmdate( 'Y-m-d-His' ) . '.' . $format . '"' );
		header( 'Pragma: no-cache' );

		echo $data;
		exit;
	}

	/**
	 * Import consents
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object or error
	 */
	public function import( WP_REST_Request $request ) {
		$body = $request->get_json_params();
		$rows = $body['rows'] ?? array();

		if ( empty( $rows ) ) {
			return new WP_Error(
				'no_data',
				__( 'No rows provided for import', 'shahi-legalflowsuite' ),
				array( 'status' => 400 )
			);
		}

		try {
			$result = $this->service->import( $rows );

			return new WP_REST_Response(
				array(
					'success'  => true,
					'imported' => $result['imported'],
					'skipped'  => $result['skipped'],
					'errors'   => $result['errors'],
					'message'  => sprintf(
						/* translators: 1: Imported count, 2: Skipped count */
						__( 'Import completed. %1$d records imported, %2$d skipped.', 'shahi-legalflowsuite' ),
						$result['imported'],
						$result['skipped']
					),
				),
				200
			);
		} catch ( \Exception $e ) {
			return new WP_Error(
				'import_failed',
				$e->getMessage(),
				array( 'status' => 500 )
			);
		}
	}

	/**
	 * Get export endpoint arguments
	 *
	 * @since 3.0.1
	 * @return array Argument definitions
	 */
	private function get_export_args(): array {
		return array(
			'format'    => array(
				'description'       => __( 'Export format: csv, json, or pdf', 'shahi-legalflowsuite' ),
				'type'              => 'string',
				'default'           => 'csv',
				'enum'              => array( 'csv', 'json', 'pdf' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'user_id'   => array(
				'description'       => __( 'Filter by user ID', 'shahi-legalflowsuite' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'type'      => array(
				'description'       => __( 'Filter by consent type', 'shahi-legalflowsuite' ),
				'type'              => 'string',
				'enum'              => array( 'necessary', 'functional', 'analytics', 'marketing', 'personalization' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'status'    => array(
				'description'       => __( 'Filter by status', 'shahi-legalflowsuite' ),
				'type'              => 'string',
				'enum'              => array( 'pending', 'accepted', 'rejected', 'withdrawn' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'date_from' => array(
				'description'       => __( 'Filter from date (Y-m-d format)', 'shahi-legalflowsuite' ),
				'type'              => 'string',
				'format'            => 'date',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'date_to'   => array(
				'description'       => __( 'Filter to date (Y-m-d format)', 'shahi-legalflowsuite' ),
				'type'              => 'string',
				'format'            => 'date',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'limit'     => array(
				'description'       => __( 'Maximum records to export', 'shahi-legalflowsuite' ),
				'type'              => 'integer',
				'default'           => 10000,
				'sanitize_callback' => 'absint',
			),
		);
	}

	/**
	 * Admin permissions check
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return bool|WP_Error True if authorized, error otherwise
	 */
	public function admin_permissions_check( WP_REST_Request $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You are not currently logged in.', 'shahi-legalflowsuite' ),
				array( 'status' => 401 )
			);
		}

		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'shahi-legalflowsuite' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}
}
