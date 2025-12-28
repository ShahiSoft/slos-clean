<?php
/**
 * Consent Log REST API Controller
 *
 * Handles REST API endpoints for consent audit log viewing and filtering.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\API;

use ShahiLegalFlowSuite\Services\Consent_Audit_Logger;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Log_Controller Class
 *
 * REST API endpoints for consent audit logs.
 *
 * @since 3.0.1
 */
class Consent_Log_Controller extends Base_REST_Controller {

	/**
	 * Audit logger service
	 *
	 * @since 3.0.1
	 * @var Consent_Audit_Logger
	 */
	private $audit_logger;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 * @param Consent_Audit_Logger $audit_logger Audit logger instance
	 */
	public function __construct( Consent_Audit_Logger $audit_logger = null ) {
		$this->namespace    = 'slos/v1';
		$this->rest_base    = 'consents/logs';
		$this->audit_logger = $audit_logger ?? new Consent_Audit_Logger();
	}

	/**
	 * Register API routes
	 *
	 * @since 3.0.1
	 */
	public function register_routes() {
		// GET /slos/v1/consents/logs - Get logs with filters
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_logs' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => $this->get_logs_args(),
				),
			)
		);

		// GET /slos/v1/consents/logs/{id} - Get single log
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_log' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'id' => array(
							'description' => 'Log ID',
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);

		// GET /slos/v1/consents/logs/stats - Get log statistics
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/stats',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_stats' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'date_from' => array(
							'description' => 'Start date (Y-m-d format)',
							'type'        => 'string',
							'format'      => 'date',
						),
						'date_to'   => array(
							'description' => 'End date (Y-m-d format)',
							'type'        => 'string',
							'format'      => 'date',
						),
					),
				),
			)
		);

		// GET /slos/v1/consents/{consent_id}/history - Get history for specific consent
		register_rest_route(
			$this->namespace,
			'/consents/(?P<consent_id>[\d]+)/history',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_consent_history' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'consent_id' => array(
							'description' => 'Consent ID',
							'type'        => 'integer',
							'required'    => true,
						),
					),
				),
			)
		);
	}

	/**
	 * Get logs with filters
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object or error
	 */
	public function get_logs( WP_REST_Request $request ) {
		// Get filter parameters
		$args = array(
			'user_id'   => $request->get_param( 'user_id' ),
			'purpose'   => $request->get_param( 'purpose' ),
			'action'    => $request->get_param( 'action' ),
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
			'limit'     => $request->get_param( 'per_page' ) ?? 20,
			'offset'    => ( $request->get_param( 'page' ) - 1 ) * ( $request->get_param( 'per_page' ) ?? 20 ),
		);

		// Remove null values
		$args = array_filter(
			$args,
			function ( $value ) {
				return null !== $value;
			}
		);

		// Get logs
		$logs  = $this->audit_logger->search( $args );
		$total = $this->audit_logger->count( $args );

		// Prepare response
		$response = rest_ensure_response( $logs );

		// Add pagination headers
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / ( $args['limit'] ?? 20 ) ) );

		return $response;
	}

	/**
	 * Get single log by ID
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error Response object or error
	 */
	public function get_log( WP_REST_Request $request ) {
		$log_id = (int) $request->get_param( 'id' );

		$log = $this->audit_logger->get_log( $log_id );

		if ( ! $log ) {
			return new WP_Error(
				'log_not_found',
				'Log not found',
				array( 'status' => 404 )
			);
		}

		return rest_ensure_response( $log );
	}

	/**
	 * Get log statistics
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function get_stats( WP_REST_Request $request ) {
		$date_from = $request->get_param( 'date_from' );
		$date_to   = $request->get_param( 'date_to' );

		$stats = $this->audit_logger->get_stats_by_action( $date_from, $date_to );

		return rest_ensure_response(
			array(
				'by_action' => $stats,
				'total'     => array_sum( $stats ),
				'period'    => array(
					'from' => $date_from ?? 'all_time',
					'to'   => $date_to ?? 'now',
				),
			)
		);
	}

	/**
	 * Get consent history
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response Response object
	 */
	public function get_consent_history( WP_REST_Request $request ) {
		$consent_id = (int) $request->get_param( 'consent_id' );

		$history = $this->audit_logger->get_consent_history( $consent_id );

		return rest_ensure_response(
			array(
				'consent_id' => $consent_id,
				'history'    => $history,
				'count'      => count( $history ),
			)
		);
	}

	/**
	 * Get arguments for logs endpoint
	 *
	 * @since 3.0.1
	 * @return array Arguments array
	 */
	private function get_logs_args(): array {
		return array(
			'user_id'   => array(
				'description' => 'Filter by user ID',
				'type'        => 'integer',
			),
			'purpose'   => array(
				'description' => 'Filter by consent purpose/type',
				'type'        => 'string',
			),
			'action'    => array(
				'description' => 'Filter by action (grant, withdraw, update, import, export)',
				'type'        => 'string',
				'enum'        => array( 'grant', 'withdraw', 'update', 'import', 'export' ),
			),
			'date_from' => array(
				'description' => 'Start date (Y-m-d format)',
				'type'        => 'string',
				'format'      => 'date',
			),
			'date_to'   => array(
				'description' => 'End date (Y-m-d format)',
				'type'        => 'string',
				'format'      => 'date',
			),
			'page'      => array(
				'description' => 'Current page',
				'type'        => 'integer',
				'default'     => 1,
			),
			'per_page'  => array(
				'description' => 'Results per page',
				'type'        => 'integer',
				'default'     => 20,
				'maximum'     => 100,
			),
		);
	}

	/**
	 * Check admin permission
	 *
	 * @since 3.0.1
	 * @param \WP_REST_Request $request Request object
	 * @return bool True if user has permission
	 */
	public function check_admin_permission( $request = null ): bool {
		return current_user_can( 'manage_shahi_template' );
	}
}
