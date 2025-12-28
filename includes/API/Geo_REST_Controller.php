<?php
/**
 * Geolocation REST Controller
 *
 * Public endpoint to get visitor region for consent template selection.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  API
 * @version     3.0.1
 */

namespace ShahiLegalopsSuite\API;

use WP_REST_Request;
use ShahiLegalopsSuite\Services\Geo_Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Geo_REST_Controller extends Base_REST_Controller {

	/** @var Geo_Service */
	private $service;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->rest_base = 'geo';
		$this->service   = new Geo_Service();
	}

	/**
	 * Register routes
	 */
	public function register_routes() {
		// GET /geo/region (public)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/region',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_region' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Get region info
	 */
	public function get_region( WP_REST_Request $request ) {
		$data = $this->service->get_region_for_request();
		// Also include suggested template for convenience
		$data['template'] = $this->service->map_region_to_template( $data['region'] );
		return $this->success_response( $data );
	}
}
