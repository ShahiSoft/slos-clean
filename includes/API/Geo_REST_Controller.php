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

		// POST /geo/presets/{preset_key}/apply (admin only)
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
							'validate_callback' => function( $value ) {
								return in_array( $value, array( 'EU', 'UK', 'US-CA', 'BR', 'ROW' ), true );
							},
						),
					),
				),
			)
		);

		// GET /geo/presets (admin only)
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

		// GET /geo/rules (admin only) - List all rules
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rules',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_rules' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		// POST /geo/rules (admin only) - Create new rule
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rules',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_rule' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		// PUT /geo/rules/{id} (admin only) - Update rule
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rules/(?P<id>[\w-]+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_rule' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		// DELETE /geo/rules/{id} (admin only) - Delete rule
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rules/(?P<id>[\w-]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_rule' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		// POST /geo/rules/{id}/duplicate (admin only) - Duplicate rule
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rules/(?P<id>[\w-]+)/duplicate',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'duplicate_rule' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		// POST /geo/rules/{id}/toggle (admin only) - Toggle rule active status
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/rules/(?P<id>[\w-]+)/toggle',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'toggle_rule' ),
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
		// Also include suggested template for convenience
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

		require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Services/Geo_Rule_Matcher.php';
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
		require_once SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Services/Geo_Rule_Matcher.php';
		$matcher = new \ShahiLegalFlowSuite\Services\Geo_Rule_Matcher();

		$presets = $matcher->get_all_presets();
		$stats   = $matcher->get_preset_stats();

		// Enrich with application status.
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

	/**
	 * Get all geo rules
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_rules( WP_REST_Request $request ) {
		$rules = get_option( 'slos_geo_rules', array() );
		return $this->success_response( array_values( $rules ) );
	}

	/**
	 * Create a new geo rule
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function create_rule( WP_REST_Request $request ) {
		$rules = get_option( 'slos_geo_rules', array() );
		
		// Generate unique ID
		$id = 'rule_' . uniqid();
		
		// Sanitize and prepare rule data
		$rule = array(
			'id'               => $id,
			'name'             => sanitize_text_field( $request->get_param( 'name' ) ),
			'region'           => sanitize_text_field( $request->get_param( 'name' ) ),
			'regulation'       => sanitize_text_field( $request->get_param( 'regulation' ) ),
			'framework'        => sanitize_text_field( $request->get_param( 'regulation' ) ),
			'countries'        => array_map( 'sanitize_text_field', (array) $request->get_param( 'countries' ) ),
			'default_consent'  => sanitize_text_field( $request->get_param( 'default_consent' ) ),
			'consent_mode'     => sanitize_text_field( $request->get_param( 'default_consent' ) ),
			'show_banner'      => (bool) $request->get_param( 'show_banner' ),
			'status'           => 'active',
			'active'           => true,
			'created_at'       => current_time( 'mysql' ),
			'updated_at'       => current_time( 'mysql' ),
		);
		
		$rules[ $id ] = $rule;
		update_option( 'slos_geo_rules', $rules );
		
		return $this->success_response(
			array(
				'rule'    => $rule,
				'message' => __( 'Geo rule created successfully', 'shahi-legalflowsuite' ),
			)
		);
	}

	/**
	 * Update an existing geo rule
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function update_rule( WP_REST_Request $request ) {
		$id    = $request->get_param( 'id' );
		$rules = get_option( 'slos_geo_rules', array() );
		
		if ( ! isset( $rules[ $id ] ) ) {
			return $this->error_response( __( 'Rule not found', 'shahi-legalflowsuite' ), 'not_found', 404 );
		}
		
		// Update rule data
		$rules[ $id ]['name']            = sanitize_text_field( $request->get_param( 'name' ) );
		$rules[ $id ]['region']          = sanitize_text_field( $request->get_param( 'name' ) );
		$rules[ $id ]['regulation']      = sanitize_text_field( $request->get_param( 'regulation' ) );
		$rules[ $id ]['framework']       = sanitize_text_field( $request->get_param( 'regulation' ) );
		$rules[ $id ]['countries']       = array_map( 'sanitize_text_field', (array) $request->get_param( 'countries' ) );
		$rules[ $id ]['default_consent'] = sanitize_text_field( $request->get_param( 'default_consent' ) );
		$rules[ $id ]['consent_mode']    = sanitize_text_field( $request->get_param( 'default_consent' ) );
		$rules[ $id ]['show_banner']     = (bool) $request->get_param( 'show_banner' );
		$rules[ $id ]['updated_at']      = current_time( 'mysql' );
		
		update_option( 'slos_geo_rules', $rules );
		
		return $this->success_response(
			array(
				'rule'    => $rules[ $id ],
				'message' => __( 'Geo rule updated successfully', 'shahi-legalflowsuite' ),
			)
		);
	}

	/**
	 * Delete a geo rule
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function delete_rule( WP_REST_Request $request ) {
		$id    = $request->get_param( 'id' );
		$rules = get_option( 'slos_geo_rules', array() );
		
		if ( ! isset( $rules[ $id ] ) ) {
			return $this->error_response( __( 'Rule not found', 'shahi-legalflowsuite' ), 'not_found', 404 );
		}
		
		unset( $rules[ $id ] );
		update_option( 'slos_geo_rules', $rules );
		
		return $this->success_response(
			array( 'message' => __( 'Geo rule deleted successfully', 'shahi-legalflowsuite' ) )
		);
	}

	/**
	 * Duplicate a geo rule
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function duplicate_rule( WP_REST_Request $request ) {
		$id    = $request->get_param( 'id' );
		$rules = get_option( 'slos_geo_rules', array() );
		
		if ( ! isset( $rules[ $id ] ) ) {
			return $this->error_response( __( 'Rule not found', 'shahi-legalflowsuite' ), 'not_found', 404 );
		}
		
		// Create duplicate with new ID
		$new_id = 'rule_' . uniqid();
		$new_rule = $rules[ $id ];
		$new_rule['id'] = $new_id;
		$new_rule['name'] = $new_rule['name'] . ' (Copy)';
		$new_rule['region'] = $new_rule['region'] . ' (Copy)';
		$new_rule['created_at'] = current_time( 'mysql' );
		$new_rule['updated_at'] = current_time( 'mysql' );
		
		$rules[ $new_id ] = $new_rule;
		update_option( 'slos_geo_rules', $rules );
		
		return $this->success_response(
			array(
				'rule'    => $new_rule,
				'message' => __( 'Geo rule duplicated successfully', 'shahi-legalflowsuite' ),
			)
		);
	}

	/**
	 * Toggle a geo rule's active status
	 *
	 * @since 3.1.1
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function toggle_rule( WP_REST_Request $request ) {
		$id    = $request->get_param( 'id' );
		$rules = get_option( 'slos_geo_rules', array() );
		
		if ( ! isset( $rules[ $id ] ) ) {
			return $this->error_response( __( 'Rule not found', 'shahi-legalflowsuite' ), 'not_found', 404 );
		}
		
		// Toggle status
		$current_status = $rules[ $id ]['status'] ?? 'active';
		$new_status = ( $current_status === 'active' ) ? 'inactive' : 'active';
		
		$rules[ $id ]['status'] = $new_status;
		$rules[ $id ]['active'] = ( $new_status === 'active' );
		$rules[ $id ]['updated_at'] = current_time( 'mysql' );
		
		update_option( 'slos_geo_rules', $rules );
		
		return $this->success_response(
			array(
				'rule'    => $rules[ $id ],
				'message' => sprintf(
					/* translators: %s: New status */
					__( 'Geo rule %s', 'shahi-legalflowsuite' ),
					$new_status === 'active' ? __( 'activated', 'shahi-legalflowsuite' ) : __( 'deactivated', 'shahi-legalflowsuite' )
				),
			)
		);
	}
}
