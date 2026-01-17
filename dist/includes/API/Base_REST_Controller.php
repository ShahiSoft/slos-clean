<?php
/**
 * Base REST Controller Class
 *
 * Provides common functionality for REST API endpoints.
 * Handles authentication, authorization, validation, and response formatting.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\API;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Base_REST_Controller Class
 *
 * Extends WordPress REST Controller with common functionality.
 *
 * @since 3.0.1
 */
abstract class Base_REST_Controller extends WP_REST_Controller {

	/**
	 * API namespace
	 *
	 * @since 3.0.1
	 * @var string
	 */
	protected $namespace = 'slos/v1';

	/**
	 * Resource name
	 *
	 * @since 3.0.1
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Register routes
	 *
	 * Child controllers should override; default prevents abstract override errors.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_routes() {
		// Intentionally empty; concrete in subclasses...
	}

	/**
	 * Check if user is authenticated
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if authenticated, WP_Error otherwise
	 */
	public function check_authentication( WP_REST_Request $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_not_logged_in',
				__( 'You are not currently logged in.', 'shahi-legalflowsuite' ),
				array( 'status' => 401 )
			);
		}

		return true;
	}

	/**
	 * Check if user has required capability
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @param string          $capability Required capability.
	 * @return bool|WP_Error True if authorized, WP_Error otherwise
	 */
	protected function check_permission( WP_REST_Request $request, string $capability = 'read' ) {
		$auth = $this->check_authentication( $request );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! current_user_can( $capability ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to do that.', 'shahi-legalflowsuite' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Check if user can read resource
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if authorized, WP_Error otherwise
	 */
	public function check_read_permission( WP_REST_Request $request ) {
		return $this->check_permission( $request, 'read' );
	}

	/**
	 * Check if user can create resource
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if authorized, WP_Error otherwise
	 */
	public function check_create_permission( WP_REST_Request $request ) {
		return $this->check_permission( $request, 'edit_posts' );
	}

	/**
	 * Check if user can update resource
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if authorized, WP_Error otherwise
	 */
	public function check_update_permission( WP_REST_Request $request ) {
		return $this->check_permission( $request, 'edit_posts' );
	}

	/**
	 * Check if user can delete resource
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if authorized, WP_Error otherwise
	 */
	public function check_delete_permission( WP_REST_Request $request ) {
		return $this->check_permission( $request, 'delete_posts' );
	}

	/**
	 * Check if user is admin
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if admin, WP_Error otherwise
	 */
	public function check_admin_permission( WP_REST_Request $request ) {
		return $this->check_permission( $request, 'manage_options' );
	}

	/**
	 * Send success response
	 *
	 * @since 3.0.1
	 * @param mixed  $data Response data.
	 * @param string $message Success message.
	 * @param int    $status HTTP status code.
	 * @return WP_REST_Response Response object
	 */
	protected function success_response( $data = null, string $message = '', int $status = 200 ): WP_REST_Response {
		$response = array(
			'success' => true,
		);

		if ( ! empty( $message ) ) {
			$response['message'] = $message;
		}

		if ( null !== $data ) {
			$response['data'] = $data;
		}

		return new WP_REST_Response( $response, $status );
	}

	/**
	 * Send error response
	 *
	 * @since 3.0.1
	 * @param string $code Error code.
	 * @param string $message Error message.
	 * @param int    $status HTTP status code.
	 * @param array  $data Additional error data.
	 * @return WP_Error Error object
	 */
	protected function error_response( string $code, string $message, int $status = 400, array $data = array() ): WP_Error {
		$error_data = array_merge( array( 'status' => $status ), $data );
		return new WP_Error( $code, $message, $error_data );
	}

	/**
	 * Validate required parameter
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param Parameter name.
	 * @return bool|WP_Error True if valid, WP_Error otherwise
	 */
	protected function validate_required_param( WP_REST_Request $request, string $param ) {
		$value = $request->get_param( $param );

		if ( empty( $value ) && '0' !== $value ) {
			return $this->error_response(
				'missing_param',
				/* translators: %s: parameter name */
				sprintf( __( 'Missing required parameter: %s', 'shahi-legalflowsuite' ), $param ),
				400
			);
		}

		return true;
	}

	/**
	 * Validate integer parameter
	 *
	 * @since 3.0.1
	 * @param mixed  $value Parameter value.
	 * @param string $param Parameter name.
	 * @return bool|WP_Error True if valid, WP_Error otherwise
	 */
	protected function validate_integer_param( $value, string $param ) {
		if ( ! is_numeric( $value ) || $value !== (int) $value ) {
			return $this->error_response(
				'invalid_param',
				/* translators: %s: parameter name */
				sprintf( __( 'Invalid integer parameter: %s', 'shahi-legalflowsuite' ), $param ),
				400
			);
		}

		return true;
	}

	/**
	 * Validate email parameter
	 *
	 * @since 3.0.1
	 * @param string $value Email value.
	 * @param string $param Parameter name.
	 * @return bool|WP_Error True if valid, WP_Error otherwise
	 */
	protected function validate_email_param( string $value, string $param ) {
		if ( ! is_email( $value ) ) {
			return $this->error_response(
				'invalid_param',
				/* translators: %s: parameter name */
				sprintf( __( 'Invalid email parameter: %s', 'shahi-legalflowsuite' ), $param ),
				400
			);
		}

		return true;
	}

	/**
	 * Sanitize text parameter
	 *
	 * @since 3.0.1
	 * @param string $value Parameter value.
	 * @return string Sanitized value
	 */
	protected function sanitize_text_param( string $value ): string {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize textarea parameter
	 *
	 * @since 3.0.1
	 * @param string $value Parameter value.
	 * @return string Sanitized value
	 */
	protected function sanitize_textarea_param( string $value ): string {
		return sanitize_textarea_field( $value );
	}

	/**
	 * Prepare pagination parameters
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @return array Pagination parameters
	 */
	protected function prepare_pagination_params( WP_REST_Request $request ): array {
		$page_param     = $request->get_param( 'page' );
		$page           = $page_param ? absint( $page_param ) : 1;
		$per_page_param = $request->get_param( 'per_page' );
		$per_page       = $per_page_param ? absint( $per_page_param ) : 25;
		$per_page       = min( $per_page, 100 ); // Cap at 100.

		$orderby_param = $request->get_param( 'orderby' );
		$order_by      = $orderby_param ? sanitize_text_field( $orderby_param ) : 'created_at';
		$order         = strtoupper( sanitize_text_field( $request->get_param( 'order' ) ) ) === 'ASC' ? 'ASC' : 'DESC';

		return array(
			'page'     => $page,
			'per_page' => $per_page,
			'offset'   => ( $page - 1 ) * $per_page,
			'order_by' => $order_by,
			'order'    => $order,
		);
	}

	/**
	 * Prepare pagination links
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @param int             $total Total items.
	 * @param int             $per_page Items per page.
	 * @return array Pagination links
	 */
	protected function prepare_pagination_links( WP_REST_Request $request, int $total, int $per_page ): array {
		$total_pages   = ceil( $total / $per_page );
		$current_param = $request->get_param( 'page' );
		$current       = $current_param ? absint( $current_param ) : 1;
		$base          = add_query_arg( $request->get_query_params(), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		$links = array();

		if ( $current > 1 ) {
			$links['prev'] = add_query_arg( 'page', $current - 1, $base );
		}

		if ( $current < $total_pages ) {
			$links['next'] = add_query_arg( 'page', $current + 1, $base );
		}

		return $links;
	}

	/**
	 * Add pagination headers to response
	 *
	 * @since 3.0.1
	 * @param WP_REST_Response $response Response object.
	 * @param int              $total Total items.
	 * @param int              $per_page Items per page.
	 * @return WP_REST_Response Modified response
	 */
	protected function add_pagination_headers( WP_REST_Response $response, int $total, int $per_page ): WP_REST_Response {
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Get collection parameters
	 *
	 * @since 3.0.1
	 * @return array Collection parameters schema
	 */
	public function get_collection_params(): array {
		return array(
			'page'     => array(
				'description'       => __( 'Current page of the collection.', 'shahi-legalflowsuite' ),
				'type'              => 'integer',
				'default'           => 1,
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description'       => __( 'Maximum number of items to be returned in result set.', 'shahi-legalflowsuite' ),
				'type'              => 'integer',
				'default'           => 10,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
			),
			'search'   => array(
				'description'       => __( 'Limit results to those matching a string.', 'shahi-legalflowsuite' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}

	/**
	 * Log API request
	 *
	 * @since 3.0.1
	 * @param WP_REST_Request $request Request object.
	 * @param string          $action Action performed.
	 * @return void
	 */
	protected function log_request( WP_REST_Request $request, string $action ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging only when WP_DEBUG is enabled
			error_log(
				sprintf(
					'[SLOS API] %s - %s %s - User: %d',
					$action,
					$request->get_method(),
					$request->get_route(),
					get_current_user_id()
				)
			);
		}
	}
}
