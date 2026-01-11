<?php
/**
 * DSR REST Controller Class
 *
 * Handles REST API endpoints for Data Subject Request (DSR) management.
 * Provides public endpoints for submission/verification and protected admin endpoints.
 * Supports all 7 GDPR rights with regulation-aware responses.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\API;

use ShahiLegalFlowSuite\Services\DSR_Service;
use ShahiLegalFlowSuite\Services\DSR_Audit_Service;
use ShahiLegalFlowSuite\Database\Repositories\DSR_Repository;
use ShahiLegalFlowSuite\Database\Repositories\DSR_Audit_Log_Repository;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Controller Class
 *
 * REST API endpoints for DSR operations.
 *
 * @since 3.0.1
 */
class DSR_Controller extends Base_REST_Controller {

	/**
	 * DSR service
	 *
	 * @since 3.0.1
	 * @var DSR_Service
	 */
	private $service;

	/**
	 * DSR repository
	 *
	 * @since 3.0.1
	 * @var DSR_Repository
	 */
	private $repository;

	/**
	 * DSR audit service
	 *
	 * @since 3.0.1
	 * @var DSR_Audit_Service
	 */
	private $audit_service;

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 * @param DSR_Service       $service        DSR service instance
	 * @param DSR_Repository    $repository     DSR repository instance
	 * @param DSR_Audit_Service $audit_service  Audit service instance
	 */
	public function __construct( DSR_Service $service = null, DSR_Repository $repository = null, DSR_Audit_Service $audit_service = null ) {
		$this->rest_base  = 'dsr';
		$this->service    = $service ?? new DSR_Service();
		$this->repository = $repository ?? new DSR_Repository();

		if ( null === $audit_service ) {
			$audit_repository    = new DSR_Audit_Log_Repository();
			$this->audit_service = new DSR_Audit_Service( $audit_repository );
		} else {
			$this->audit_service = $audit_service;
		}
	}

	/**
	 * Register REST API routes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_routes() {
		// Public: Submit request
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/submit',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'submit_request' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'email'        => array(
						'description'       => __( 'Email address for verification', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'format'            => 'email',
						'required'          => true,
						'validate_callback' => array( $this, 'validate_email_param' ),
						'sanitize_callback' => 'sanitize_email',
					),
					'request_type' => array(
						'description' => __( 'Type of DSR (access, rectification, erasure, etc.)', 'shahi-legalflowsuite' ),
						'type'        => 'string',
						'required'    => true,
						'enum'        => array( 'access', 'rectification', 'erasure', 'portability', 'restriction', 'object', 'automated_decision' ),
					),
					'regulation'   => array(
						'description' => __( 'Regulation under which request is made', 'shahi-legalflowsuite' ),
						'type'        => 'string',
						'default'     => 'GDPR',
						'enum'        => array( 'GDPR', 'CCPA', 'LGPD', 'UK-GDPR', 'PIPEDA', 'POPIA' ),
					),
					'details'      => array(
						'description'       => __( 'Additional details about the request', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'user_id'      => array(
						'description' => __( 'WordPress user ID (if logged in)', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'default'     => 0,
					),
				),
			)
		);

		// Public: Verify email
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/verify',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'verify_email' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'token' => array(
						'description'       => __( 'Email verification token', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Public: Check status
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/status',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'check_status' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'token' => array(
						'description'       => __( 'Request tracking token', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Admin: List requests
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'list_requests' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'status'       => array(
						'description'       => __( 'Filter by status', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'request_type' => array(
						'description'       => __( 'Filter by request type', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'regulation'   => array(
						'description'       => __( 'Filter by regulation', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'         => array(
						'description' => __( 'Page number', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'default'     => 1,
						'minimum'     => 1,
					),
					'per_page'     => array(
						'description' => __( 'Items per page', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'default'     => 20,
						'minimum'     => 1,
						'maximum'     => 100,
					),
				),
			)
		);

		// Admin: Get single request
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_request' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'DSR request ID', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Admin: Assign request
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/assign',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'assign_request' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id'       => array(
						'description' => __( 'DSR request ID', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'assignee' => array(
						'description' => __( 'User ID of assignee', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Admin: Add note
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/note',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'add_note' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id'   => array(
						'description' => __( 'DSR request ID', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'note' => array(
						'description'       => __( 'Admin note text', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);

		// Admin: Update status
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/status',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_status' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id'       => array(
						'description' => __( 'DSR request ID', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'status'   => array(
						'description' => __( 'New status', 'shahi-legalflowsuite' ),
						'type'        => 'string',
						'required'    => true,
						'enum'        => array( 'pending_verification', 'verified', 'in_progress', 'on_hold', 'completed', 'rejected' ),
					),
					'notes'    => array(
						'description'       => __( 'Admin notes about status change', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'metadata' => array(
						'description' => __( 'Additional metadata', 'shahi-legalflowsuite' ),
						'type'        => 'object',
						'default'     => array(),
					),
				),
			)
		);

		// Admin: Generate export package
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/export',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'generate_export' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'DSR request ID', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Admin: Execute erasure
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/erase',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'execute_erasure' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id'      => array(
						'description' => __( 'DSR request ID', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'dry_run' => array(
						'description' => __( 'Preview mode - do not execute', 'shahi-legalflowsuite' ),
						'type'        => 'boolean',
						'default'     => false,
					),
				),
			)
		);

		// Admin: Get timeline
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/timeline',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_timeline' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'DSR request ID', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Admin: Get statistics
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_statistics' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
			)
		);

		// Admin: Get audit logs
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/logs',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_audit_logs' ),
				'permission_callback' => array( $this, 'check_admin_permission' ),
				'args'                => array(
					'request_id' => array(
						'description'       => __( 'Filter by request ID', 'shahi-legalflowsuite' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'action'     => array(
						'description'       => __( 'Filter by action type', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'actor_id'   => array(
						'description'       => __( 'Filter by actor user ID', 'shahi-legalflowsuite' ),
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'start_date' => array(
						'description'       => __( 'Filter by start date (YYYY-MM-DD)', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'end_date'   => array(
						'description'       => __( 'Filter by end date (YYYY-MM-DD)', 'shahi-legalflowsuite' ),
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'       => array(
						'description' => __( 'Page number', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'default'     => 1,
						'minimum'     => 1,
					),
					'per_page'   => array(
						'description' => __( 'Items per page', 'shahi-legalflowsuite' ),
						'type'        => 'integer',
						'default'     => 50,
						'minimum'     => 1,
						'maximum'     => 100,
					),
				),
			)
		);
	}

	/**
	 * Submit new DSR request (public endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function submit_request( WP_REST_Request $request ) {
		$user_id      = $request->get_param( 'user_id' ) ?: 0;
		$email        = $request->get_param( 'email' );
		$request_type = $request->get_param( 'request_type' );
		$regulation   = $request->get_param( 'regulation' );
		$details      = $request->get_param( 'details' );

		// Submit via service
		$result = $this->service->submit_request(
			$user_id,
			$request_type,
			$email,
			$details,
			array( 'regulation' => $regulation )
		);

		if ( false === $result ) {
			// Get errors from service
			$errors = $this->service->get_errors();
			if ( ! empty( $errors ) ) {
				$first_error = reset( $errors );
				return $this->error_response(
					$first_error['code'] ?? 'submission_failed',
					$first_error['message'] ?? __( 'Failed to submit request. Please try again.', 'shahi-legalflowsuite' ),
					400
				);
			}

			return $this->error_response(
				'submission_failed',
				__( 'Failed to submit request. Please try again.', 'shahi-legalflowsuite' ),
				400
			);
		}

		// Retrieve created request
		$dsr_request = $this->repository->find( $result );

		if ( ! $dsr_request ) {
			return $this->error_response(
				'request_not_found',
				__( 'Request created but could not be retrieved.', 'shahi-legalflowsuite' ),
				500
			);
		}

		// Return success with request details
		return new WP_REST_Response(
			array(
				'success'      => true,
				'message'      => __( 'Request submitted successfully. Please check your email to verify.', 'shahi-legalflowsuite' ),
				'request_id'   => $result,
				'request_hash' => $dsr_request->request_hash ?? '',
				'due_date'     => $dsr_request->due_date ?? '',
				'sla_days'     => $dsr_request->sla_days ?? 30,
				'regulation'   => $dsr_request->regulation ?? 'GDPR',
			),
			201
		);
	}

	/**
	 * Verify email via token (public endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function verify_email( WP_REST_Request $request ) {
		$token = $request->get_param( 'token' );

		// Verify via service
		$result = $this->service->verify_email( $token );

		if ( false === $result ) {
			$errors = $this->service->get_errors();
			if ( ! empty( $errors ) ) {
				$first_error = reset( $errors );
				return $this->error_response(
					$first_error['code'] ?? 'verification_failed',
					$first_error['message'] ?? __( 'Invalid or expired verification token.', 'shahi-legalflowsuite' ),
					400
				);
			}

			return $this->error_response(
				'verification_failed',
				__( 'Invalid or expired verification token.', 'shahi-legalflowsuite' ),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Email verified successfully. Your request is now being processed.', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Check request status via token (public endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function check_status( WP_REST_Request $request ) {
		$token = $request->get_param( 'token' );

		// Find by token
		$dsr_request = $this->repository->find_by_token( $token );

		if ( ! $dsr_request ) {
			return $this->error_response(
				'invalid_token',
				__( 'Invalid or expired token.', 'shahi-legalflowsuite' ),
				404
			);
		}

		// Prepare response data (limited public view)
		$response_data = array(
			'id'           => $dsr_request->id,
			'type'         => $dsr_request->request_type,
			'status'       => $dsr_request->status,
			'submitted_at' => $dsr_request->submitted_at,
			'due_date'     => $dsr_request->due_date,
			'regulation'   => $dsr_request->regulation,
			'sla_days'     => $dsr_request->sla_days,
		);

		// Add completion date if completed
		if ( 'completed' === $dsr_request->status && ! empty( $dsr_request->completed_at ) ) {
			$response_data['completed_at'] = $dsr_request->completed_at;
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'request' => $response_data,
			),
			200
		);
	}

	/**
	 * List DSR requests (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function list_requests( WP_REST_Request $request ) {
		// Build filters
		$filters = array();

		if ( $request->get_param( 'status' ) ) {
			$filters['status'] = $request->get_param( 'status' );
		}

		if ( $request->get_param( 'request_type' ) ) {
			$filters['request_type'] = $request->get_param( 'request_type' );
		}

		if ( $request->get_param( 'regulation' ) ) {
			$filters['regulation'] = $request->get_param( 'regulation' );
		}

		// Pagination
		$page     = $request->get_param( 'page' );
		$per_page = $request->get_param( 'per_page' );
		$offset   = ( $page - 1 ) * $per_page;

		// Get requests
		$requests = $this->repository->list_requests( $filters, $per_page, $offset );

		// Get statistics
		$stats = $this->repository->stats_by_status();

		// Get total count for pagination
		$total = $this->repository->count_requests( $filters );

		return new WP_REST_Response(
			array(
				'success'   => true,
				'requests'  => $requests,
				'stats'     => $stats,
				'page'      => $page,
				'per_page'  => $per_page,
				'total'     => $total,
				'max_pages' => ceil( $total / $per_page ),
			),
			200
		);
	}

	/**
	 * Get single DSR request (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function get_request( WP_REST_Request $request ) {
		$id = (int) $request->get_param( 'id' );

		// Find request
		$dsr_request = $this->repository->find( $id );

		if ( ! $dsr_request ) {
			return $this->error_response(
				'request_not_found',
				__( 'Request not found.', 'shahi-legalflowsuite' ),
				404
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'request' => $dsr_request,
			),
			200
		);
	}

	/**
	 * Assign request to admin user (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function assign_request( WP_REST_Request $request ) {
		$id       = (int) $request->get_param( 'id' );
		$assignee = (int) $request->get_param( 'assignee' );

		// Assign via service
		$result = $this->service->assign_request( $id, $assignee );

		if ( false === $result ) {
			$errors = $this->service->get_errors();
			if ( ! empty( $errors ) ) {
				$first_error = reset( $errors );
				return $this->error_response(
					$first_error['code'] ?? 'assignment_failed',
					$first_error['message'] ?? __( 'Failed to assign request.', 'shahi-legalflowsuite' ),
					400
				);
			}

			return $this->error_response(
				'assignment_failed',
				__( 'Failed to assign request.', 'shahi-legalflowsuite' ),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Request assigned successfully.', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Add note to request (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function add_note( WP_REST_Request $request ) {
		$id   = (int) $request->get_param( 'id' );
		$note = $request->get_param( 'note' );

		// Get current user
		$author = get_current_user_id();

		// Add note via service
		$result = $this->service->add_note( $id, $note, $author );

		if ( false === $result ) {
			$errors = $this->service->get_errors();
			if ( ! empty( $errors ) ) {
				$first_error = reset( $errors );
				return $this->error_response(
					$first_error['code'] ?? 'note_failed',
					$first_error['message'] ?? __( 'Failed to add note.', 'shahi-legalflowsuite' ),
					400
				);
			}

			return $this->error_response(
				'note_failed',
				__( 'Failed to add note.', 'shahi-legalflowsuite' ),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Note added successfully.', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Update request status (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function update_status( WP_REST_Request $request ) {
		$id       = (int) $request->get_param( 'id' );
		$status   = $request->get_param( 'status' );
		$notes    = $request->get_param( 'notes' );
		$metadata = $request->get_param( 'metadata' );

		// Build metadata
		if ( ! is_array( $metadata ) ) {
			$metadata = array();
		}

		$metadata['processed_by'] = get_current_user_id();

		if ( ! empty( $notes ) ) {
			$metadata['notes'] = $notes;
		}

		// Update via service
		$result = $this->service->transition( $id, $status, $metadata );

		if ( false === $result ) {
			$errors = $this->service->get_errors();
			if ( ! empty( $errors ) ) {
				$first_error = reset( $errors );
				return $this->error_response(
					$first_error['code'] ?? 'status_update_failed',
					$first_error['message'] ?? __( 'Failed to update status.', 'shahi-legalflowsuite' ),
					400
				);
			}

			return $this->error_response(
				'status_update_failed',
				__( 'Failed to update status.', 'shahi-legalflowsuite' ),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Status updated successfully.', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Generate export package (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function generate_export( WP_REST_Request $request ) {
		$id = (int) $request->get_param( 'id' );

		// Generate via service
		$token = $this->service->generate_export_package( $id );

		if ( false === $token ) {
			$errors = $this->service->get_errors();
			if ( ! empty( $errors ) ) {
				$first_error = reset( $errors );
				return $this->error_response(
					$first_error['code'] ?? 'export_failed',
					$first_error['message'] ?? __( 'Failed to generate export package.', 'shahi-legalflowsuite' ),
					400
				);
			}

			return $this->error_response(
				'export_failed',
				__( 'Failed to generate export package.', 'shahi-legalflowsuite' ),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Export package generation initiated. Token generated.', 'shahi-legalflowsuite' ),
				'token'   => $token,
			),
			200
		);
	}

	/**
	 * Execute erasure request (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function execute_erasure( WP_REST_Request $request ) {
		$id      = (int) $request->get_param( 'id' );
		$dry_run = (bool) $request->get_param( 'dry_run' );

		// If dry-run, get preview
		if ( $dry_run ) {
			$erasure_service = new \ShahiLegalFlowSuite\Services\DSR_Erasure_Service();
			$preview         = $erasure_service->get_erasure_preview( $id );

			if ( isset( $preview['error'] ) ) {
				return $this->error_response(
					'preview_failed',
					$preview['error'],
					400
				);
			}

			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => __( 'Erasure preview generated (dry-run mode).', 'shahi-legalflowsuite' ),
					'preview' => $preview,
				),
				200
			);
		}

		// Execute via service
		$result = $this->service->execute_erasure( $id );

		if ( false === $result ) {
			$errors = $this->service->get_errors();
			if ( ! empty( $errors ) ) {
				$first_error = reset( $errors );
				return $this->error_response(
					$first_error['code'] ?? 'erasure_failed',
					$first_error['message'] ?? __( 'Failed to execute erasure.', 'shahi-legalflowsuite' ),
					400
				);
			}

			return $this->error_response(
				'erasure_failed',
				__( 'Failed to execute erasure.', 'shahi-legalflowsuite' ),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Erasure executed successfully. Data has been anonymized.', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Get request timeline/audit log (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function get_timeline( WP_REST_Request $request ) {
		$id = (int) $request->get_param( 'id' );

		// Get timeline via service
		$timeline = $this->service->get_timeline( $id );

		if ( false === $timeline ) {
			$errors = $this->service->get_errors();
			if ( ! empty( $errors ) ) {
				$first_error = reset( $errors );
				return $this->error_response(
					$first_error['code'] ?? 'timeline_failed',
					$first_error['message'] ?? __( 'Failed to retrieve timeline.', 'shahi-legalflowsuite' ),
					400
				);
			}

			return $this->error_response(
				'timeline_failed',
				__( 'Failed to retrieve timeline.', 'shahi-legalflowsuite' ),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success'  => true,
				'timeline' => $timeline,
			),
			200
		);
	}

	/**
	 * Get DSR statistics (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function get_statistics( WP_REST_Request $request ) {
		// Get stats from repository
		$stats_by_status     = $this->repository->stats_by_status();
		$stats_by_type       = $this->repository->stats_by_type();
		$stats_by_regulation = $this->repository->stats_by_regulation();
		$overdue_requests    = $this->repository->overdue_requests();

		return new WP_REST_Response(
			array(
				'success'      => true,
				'stats'        => array(
					'by_status'     => $stats_by_status,
					'by_type'       => $stats_by_type,
					'by_regulation' => $stats_by_regulation,
					'overdue_count' => is_array( $overdue_requests ) ? count( $overdue_requests ) : 0,
				),
				'overdue_list' => $overdue_requests,
			),
			200
		);
	}

	/**
	 * Get audit logs with filters (admin endpoint)
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return WP_REST_Response|WP_Error Response or error
	 */
	public function get_audit_logs( WP_REST_Request $request ) {
		$filters = array(
			'request_id' => $request->get_param( 'request_id' ) ?: 0,
			'action'     => $request->get_param( 'action' ) ?: '',
			'actor_id'   => $request->get_param( 'actor_id' ) ?: 0,
			'start_date' => $request->get_param( 'start_date' ) ?: '',
			'end_date'   => $request->get_param( 'end_date' ) ?: '',
			'limit'      => $request->get_param( 'per_page' ) ?: 50,
			'offset'     => ( ( $request->get_param( 'page' ) ?: 1 ) - 1 ) * ( $request->get_param( 'per_page' ) ?: 50 ),
			'order'      => 'DESC',
		);

		$result = $this->audit_service->get_logs( $filters );

		return new WP_REST_Response(
			array(
				'success' => true,
				'logs'    => $result['logs'],
				'total'   => $result['total'],
				'page'    => $request->get_param( 'page' ) ?: 1,
				'pages'   => ceil( $result['total'] / ( $request->get_param( 'per_page' ) ?: 50 ) ),
			),
			200
		);
	}

	/**
	 * Validate email parameter
	 *
	 * @since 3.0.1
	 * @param mixed           $value   Parameter value
	 * @param WP_REST_Request $request REST request object
	 * @param string          $param   Parameter name
	 * @return bool|WP_Error True if valid, WP_Error otherwise
	 */
	public function validate_email_param( $value, $request, $param ) {
		if ( ! is_email( $value ) ) {
			return new WP_Error(
				'invalid_email',
				/* translators: %s: parameter name */
				sprintf( __( 'Invalid email address: %s', 'shahi-legalflowsuite' ), $param ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Check admin permission with DSR-specific capability
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request REST request object
	 * @return bool|WP_Error True if authorized, WP_Error otherwise
	 */
	public function check_admin_permission( WP_REST_Request $request = null ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You must be logged in to access this resource.', 'shahi-legalflowsuite' ),
				array( 'status' => 401 )
			);
		}

		// Check for admin or DSR-specific capability
		if ( current_user_can( 'manage_options' ) || current_user_can( 'slos_manage_dsr' ) ) {
			return true;
		}

		return new WP_Error(
			'rest_forbidden',
			__( 'Sorry, you are not allowed to access this resource.', 'shahi-legalflowsuite' ),
			array( 'status' => 403 )
		);
	}
}
