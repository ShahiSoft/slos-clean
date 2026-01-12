<?php
/**
 * Geolocation REST Controller
 *
 * Public endpoint to get visitor region for consent template selection.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 */

namespace ShahiLegalFlowSuite\API;

use WP_REST_Request;
use ShahiLegalFlowSuite\Services\Geo_Service;

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
		// GET /geo/region (public)..
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

		// POST /geo/presets/{preset_key}/apply (admin only)..
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/presets/(?P<preset_key>[a-zA-Z0-9_-]+)/apply',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'apply_preset' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
					'args'                => array(
						'preset_key' => array(
							'required'          => true,
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value ) {
								return in_array( $value, array( 'EU', 'UK', 'US-CA', 'BR', 'ROW' ), true );
							},
						),
					),
				),
			)
		);

		// GET /geo/presets (admin only)..
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/presets',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_presets' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);
	}

	/**
	 * Get region info
	 */
	public function get_region( WP_REST_Request $request ) {
		$data = $this->service->get_region_for_request();
		// Also include suggested template for convenience..
		$data['template'] = $this->service->map_region_to_template( $data['region'] );
		return $this->success_response( $data );
	}

	/**
	 * Apply a regional preset
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function apply_preset( WP_REST_Request $request ) {
		$preset_key = $request->get_param( 'preset_key' );

		require_once SLOS_PLUGIN_DIR . 'includes/Services/Geo_Rule_Matcher.php';
		$matcher = new \ShahiLegalFlowSuite\Services\Geo_Rule_Matcher();

		$result = $matcher->apply_preset( $preset_key );

		if ( is_wp_error( $result ) ) {
			return $this->error_response(
				$result->get_error_message(),
				$result->get_error_code(),
				400
			);
		}

		return $this->success_response(
			array(
				'rule'    => $result,
				'message' => sprintf(
					/* translators: %s: Preset label */
					__( 'Successfully applied preset: %s', 'shahi-legalflowsuite' ),
					$result['name']
				),
			)
		);
	}

	/**
	 * Get all presets with application status
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_presets( WP_REST_Request $request ) {
		require_once SLOS_PLUGIN_DIR . 'includes/Services/Geo_Rule_Matcher.php';
		$matcher = new \ShahiLegalFlowSuite\Services\Geo_Rule_Matcher();

		$presets = $matcher->get_all_presets();
		$stats   = $matcher->get_preset_stats();

		// Enrich with application status...
		$enriched = array();
		foreach ( $presets as $key => $preset ) {
			$preset['key']        = $key;
			$preset['is_applied'] = $matcher->is_preset_applied( $key );
			$enriched[ $key ]     = $preset;
		}

		return $this->success_response(
			array(
				'presets' => $enriched,
				'stats'   => $stats,
			)
		);
	}

	/**
	 * Check admin permissions
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	public function check_admin_permissions() {
		return current_user_can( 'manage_options' );
	}
}
