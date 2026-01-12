<?php
/**
 * Settings REST Controller Class
 *
 * Handles REST API endpoints for plugin settings management.
 * Provides endpoints for banner, geo rules, and other settings.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.3
 * @since       3.0.3
 */

namespace ShahiLegalFlowSuite\API;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings_REST_Controller Class
 *
 * REST API endpoints for settings operations.
 *
 * @since 3.0.3
 */
class Settings_REST_Controller extends Base_REST_Controller {

	/**
	 * Constructor
	 *
	 * @since 3.0.3
	 */
	public function __construct() {
		$this->rest_base = 'settings';
	}

	/**
	 * Register REST API routes
	 *
	 * @since 3.0.3
	 * @return void
	 */
	public function register_routes() {
		// Banner settings..
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/banner',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_banner_settings' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_banner_settings' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// Banner settings reset..
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/banner/reset',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'reset_banner_settings' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// Geo rules..
		register_rest_route(
			$this->namespace,
			'/geo/rules',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_geo_rules' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_geo_rule' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// Single geo rule..
		register_rest_route(
			$this->namespace,
			'/geo/rules/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_geo_rule' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_geo_rule' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_geo_rule' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// Duplicate geo rule..
		register_rest_route(
			$this->namespace,
			'/geo/rules/(?P<id>[\d]+)/duplicate',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'duplicate_geo_rule' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// Toggle geo rule..
		register_rest_route(
			$this->namespace,
			'/geo/rules/(?P<id>[\d]+)/toggle',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'toggle_geo_rule' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
	}

	/**
	 * Check admin permission
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return bool|WP_Error
	 */
	public function check_admin_permission( WP_REST_Request $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to manage settings.', 'shahi-legalflowsuite' ),
				array( 'status' => 403 )
			);
		}
		return true;
	}

	/**
	 * Get default banner settings
	 *
	 * @since 3.0.3
	 * @updated 3.1.1 - Added category descriptions and vendor support
	 * @return array
	 */
	private function get_default_banner_settings() {
		return array(
			'template'              => 'eu', // Phase 2.1.2: Banner template (eu, ccpa, simple, advanced)
			'position'              => 'bottom',
			'layout'                => 'bar',
			'bg_color'              => '#1a1a2e',
			'text_color'            => '#ffffff',
			'primary_color'         => '#3b82f6',
			'title'                 => __( 'We value your privacy', 'shahi-legalflowsuite' ),
			'message'               => __( 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.', 'shahi-legalflowsuite' ),
			'accept_text'           => __( 'Accept All', 'shahi-legalflowsuite' ),
			'reject_text'           => __( 'Reject All', 'shahi-legalflowsuite' ),
			'settings_text'         => __( 'Cookie Settings', 'shahi-legalflowsuite' ),
			'icon_position'         => 'left', // Phase 1.4.3: Floating icon position (left or right)
			'show_reject'           => true,
			'show_settings'         => true,
			'auto_hide'             => false,
			'blur_background'       => false,
			// Phase 1.4: Category descriptions..
			'show_descriptions'     => true,
			'category_descriptions' => array(
				'necessary'   => __( 'Essential cookies required for the website to function. Cannot be disabled.', 'shahi-legalflowsuite' ),
				'functional'  => __( 'Cookies that enhance functionality such as live chat, videos, and social media.', 'shahi-legalflowsuite' ),
				'analytics'   => __( 'Cookies that help us understand how visitors interact with our website.', 'shahi-legalflowsuite' ),
				'marketing'   => __( 'Cookies used for advertising and retargeting based on your interests.', 'shahi-legalflowsuite' ),
				'preferences' => __( 'Cookies that remember your settings and preferences for a better experience.', 'shahi-legalflowsuite' ),
			),
			// Phase 1.4: Vendor/service transparency..
			'show_vendors'          => false,
			'vendors'               => array(
				'analytics'   => array(
					array(
						'name'    => 'Google Analytics',
						'purpose' => 'Website analytics',
					),
				),
				'marketing'   => array(
					array(
						'name'    => 'Facebook Pixel',
						'purpose' => 'Ad targeting',
					),
					array(
						'name'    => 'Google Ads',
						'purpose' => 'Advertisement',
					),
				),
				'functional'  => array(),
				'preferences' => array(),
			),
			// Phase 2.3: Privacy & Consent Expiry Settings..
			'privacy_url'           => '', // Phase 2.3.1: Privacy policy URL
			'learn_more_text'       => __( 'Learn more', 'shahi-legalflowsuite' ), // Phase 2.3.2: Learn more link text
			'consent_expiry_days'   => 30, // Phase 2.3.3: Consent expiry in days (default 30)
			'grace_period_days'     => 0, // Phase 3.4.1: Re-consent grace period in days (default 0 = immediate)
		);
	}

	/**
	 * Get banner settings
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response
	 */
	public function get_banner_settings( WP_REST_Request $request ) {
		$defaults = $this->get_default_banner_settings();
		$settings = get_option( 'slos_banner_settings', array() );
		$settings = wp_parse_args( $settings, $defaults );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $settings,
			),
			200
		);
	}

	/**
	 * Update banner settings
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_banner_settings( WP_REST_Request $request ) {
		$params = $request->get_json_params();

		if ( empty( $params ) ) {
			$params = $request->get_params();
		}

		$defaults = $this->get_default_banner_settings();
		$current  = get_option( 'slos_banner_settings', array() );

		// Sanitize and merge settings..
		$settings = array(
			'template'              => isset( $params['template'] ) ? sanitize_text_field( $params['template'] ) : ( $current['template'] ?? $defaults['template'] ), // Phase 2.1.2: Template selection
			'position'              => isset( $params['position'] ) ? sanitize_text_field( $params['position'] ) : ( $current['position'] ?? $defaults['position'] ),
			'layout'                => isset( $params['layout'] ) ? sanitize_text_field( $params['layout'] ) : ( $current['layout'] ?? $defaults['layout'] ),
			'bg_color'              => isset( $params['bg_color'] ) ? sanitize_hex_color( $params['bg_color'] ) : ( $current['bg_color'] ?? $defaults['bg_color'] ),
			'text_color'            => isset( $params['text_color'] ) ? sanitize_hex_color( $params['text_color'] ) : ( $current['text_color'] ?? $defaults['text_color'] ),
			'primary_color'         => isset( $params['primary_color'] ) ? sanitize_hex_color( $params['primary_color'] ) : ( $current['primary_color'] ?? $defaults['primary_color'] ),
			'title'                 => isset( $params['title'] ) ? sanitize_text_field( $params['title'] ) : ( $current['title'] ?? $defaults['title'] ),
			'message'               => isset( $params['message'] ) ? wp_kses_post( $params['message'] ) : ( $current['message'] ?? $defaults['message'] ),
			'accept_text'           => isset( $params['accept_text'] ) ? sanitize_text_field( $params['accept_text'] ) : ( $current['accept_text'] ?? $defaults['accept_text'] ),
			'reject_text'           => isset( $params['reject_text'] ) ? sanitize_text_field( $params['reject_text'] ) : ( $current['reject_text'] ?? $defaults['reject_text'] ),
			'settings_text'         => isset( $params['settings_text'] ) ? sanitize_text_field( $params['settings_text'] ) : ( $current['settings_text'] ?? $defaults['settings_text'] ),
			'icon_position'         => isset( $params['icon_position'] ) ? sanitize_text_field( $params['icon_position'] ) : ( $current['icon_position'] ?? $defaults['icon_position'] ?? 'left' ), // Phase 1.4.3: Icon position
			'show_reject'           => isset( $params['show_reject'] ) ? (bool) $params['show_reject'] : ( $current['show_reject'] ?? $defaults['show_reject'] ),
			'show_settings'         => isset( $params['show_settings'] ) ? (bool) $params['show_settings'] : ( $current['show_settings'] ?? $defaults['show_settings'] ),
			'auto_hide'             => isset( $params['auto_hide'] ) ? (bool) $params['auto_hide'] : ( $current['auto_hide'] ?? $defaults['auto_hide'] ),
			'blur_background'       => isset( $params['blur_background'] ) ? (bool) $params['blur_background'] : ( $current['blur_background'] ?? $defaults['blur_background'] ),
			// Phase 1.4: Category descriptions and vendors..
			'show_descriptions'     => isset( $params['show_descriptions'] ) ? (bool) $params['show_descriptions'] : ( $current['show_descriptions'] ?? $defaults['show_descriptions'] ),
			'category_descriptions' => isset( $params['category_descriptions'] ) ? $this->sanitize_descriptions( $params['category_descriptions'] ) : ( $current['category_descriptions'] ?? $defaults['category_descriptions'] ),
			'show_vendors'          => isset( $params['show_vendors'] ) ? (bool) $params['show_vendors'] : ( $current['show_vendors'] ?? $defaults['show_vendors'] ),
			'vendors'               => isset( $params['vendors'] ) ? $this->sanitize_vendors( $params['vendors'] ) : ( $current['vendors'] ?? $defaults['vendors'] ),
			// Phase 2.3: Privacy & Consent Expiry Settings..
			'privacy_url'           => isset( $params['privacy_url'] ) ? esc_url_raw( $params['privacy_url'] ) : ( $current['privacy_url'] ?? $defaults['privacy_url'] ), // Phase 2.3.1: Privacy URL
			'learn_more_text'       => isset( $params['learn_more_text'] ) ? sanitize_text_field( $params['learn_more_text'] ) : ( $current['learn_more_text'] ?? $defaults['learn_more_text'] ), // Phase 2.3.2: Learn more text
			'consent_expiry_days'   => isset( $params['consent_expiry_days'] ) ? absint( $params['consent_expiry_days'] ) : ( $current['consent_expiry_days'] ?? $defaults['consent_expiry_days'] ), // Phase 2.3.3: Consent expiry days
			'grace_period_days'     => isset( $params['grace_period_days'] ) ? absint( $params['grace_period_days'] ) : ( $current['grace_period_days'] ?? $defaults['grace_period_days'] ), // Phase 3.4.1: Grace period days
		);

		$updated = update_option( 'slos_banner_settings', $settings );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Banner settings saved successfully.', 'shahi-legalflowsuite' ),
				'data'    => $settings,
			),
			200
		);
	}

	/**
	 * Reset banner settings to defaults
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response
	 */
	public function reset_banner_settings( WP_REST_Request $request ) {
		$defaults = $this->get_default_banner_settings();
		update_option( 'slos_banner_settings', $defaults );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Banner settings reset to defaults.', 'shahi-legalflowsuite' ),
				'data'    => $defaults,
			),
			200
		);
	}

	/**
	 * Sanitize category descriptions
	 *
	 * @since 3.1.1
	 * @param array $descriptions Category descriptions
	 * @return array
	 */
	private function sanitize_descriptions( $descriptions ) {
		if ( ! is_array( $descriptions ) ) {
			return array();
		}

		$sanitized          = array();
		$allowed_categories = array( 'necessary', 'functional', 'analytics', 'marketing', 'preferences' );

		foreach ( $descriptions as $category => $description ) {
			if ( in_array( $category, $allowed_categories, true ) ) {
				$sanitized[ $category ] = wp_kses_post( $description );
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize vendors data
	 *
	 * @since 3.1.1
	 * @param array $vendors Vendors data
	 * @return array
	 */
	private function sanitize_vendors( $vendors ) {
		if ( ! is_array( $vendors ) ) {
			return array();
		}

		$sanitized          = array();
		$allowed_categories = array( 'necessary', 'functional', 'analytics', 'marketing', 'preferences' );

		foreach ( $vendors as $category => $vendor_list ) {
			if ( ! in_array( $category, $allowed_categories, true ) ) {
				continue;
			}

			if ( ! is_array( $vendor_list ) ) {
				$sanitized[ $category ] = array();
				continue;
			}

			$sanitized[ $category ] = array();

			foreach ( $vendor_list as $vendor ) {
				if ( ! is_array( $vendor ) ) {
					continue;
				}

				$sanitized[ $category ][] = array(
					'name'    => isset( $vendor['name'] ) ? sanitize_text_field( $vendor['name'] ) : '',
					'purpose' => isset( $vendor['purpose'] ) ? sanitize_text_field( $vendor['purpose'] ) : '',
				);
			}
		}

		return $sanitized;
	}

	/**
	 * Get all geo rules
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response
	 */
	public function get_geo_rules( WP_REST_Request $request ) {
		$rules = get_option( 'slos_geo_rules', array() );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array_values( $rules ),
				'total'   => count( $rules ),
			),
			200
		);
	}

	/**
	 * Get single geo rule
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_geo_rule( WP_REST_Request $request ) {
		$id    = (int) $request->get_param( 'id' );
		$rules = get_option( 'slos_geo_rules', array() );

		if ( ! isset( $rules[ $id ] ) ) {
			return new WP_Error(
				'rule_not_found',
				__( 'Geo rule not found.', 'shahi-legalflowsuite' ),
				array( 'status' => 404 )
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $rules[ $id ],
			),
			200
		);
	}

	/**
	 * Create geo rule
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_geo_rule( WP_REST_Request $request ) {
		$params = $request->get_json_params();

		if ( empty( $params ) ) {
			$params = $request->get_params();
		}

		$rules = get_option( 'slos_geo_rules', array() );

		// Generate new ID..
		$new_id = empty( $rules ) ? 1 : max( array_keys( $rules ) ) + 1;

		$rule = array(
			'id'               => $new_id,
			'name'             => sanitize_text_field( $params['name'] ?? '' ),
			'framework'        => sanitize_text_field( $params['framework'] ?? 'gdpr' ),
			'consent_mode'     => sanitize_text_field( $params['consent_mode'] ?? 'opt-in' ),
			'countries'        => array_map( 'sanitize_text_field', (array) ( $params['countries'] ?? array() ) ),
			'require_explicit' => (bool) ( $params['require_explicit'] ?? true ),
			'show_reject'      => (bool) ( $params['show_reject'] ?? true ),
			'record_proof'     => (bool) ( $params['record_proof'] ?? true ),
			'allow_withdraw'   => (bool) ( $params['allow_withdraw'] ?? true ),
			'active'           => true,
			'created_at'       => current_time( 'mysql' ),
			'updated_at'       => current_time( 'mysql' ),
		);

		$rules[ $new_id ] = $rule;
		update_option( 'slos_geo_rules', $rules );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Geo rule created successfully.', 'shahi-legalflowsuite' ),
				'data'    => $rule,
			),
			201
		);
	}

	/**
	 * Update geo rule
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_geo_rule( WP_REST_Request $request ) {
		$id     = (int) $request->get_param( 'id' );
		$params = $request->get_json_params();

		if ( empty( $params ) ) {
			$params = $request->get_params();
		}

		$rules = get_option( 'slos_geo_rules', array() );

		if ( ! isset( $rules[ $id ] ) ) {
			return new WP_Error(
				'rule_not_found',
				__( 'Geo rule not found.', 'shahi-legalflowsuite' ),
				array( 'status' => 404 )
			);
		}

		$existing = $rules[ $id ];

		$rule = array(
			'id'               => $id,
			'name'             => isset( $params['name'] ) ? sanitize_text_field( $params['name'] ) : $existing['name'],
			'framework'        => isset( $params['framework'] ) ? sanitize_text_field( $params['framework'] ) : $existing['framework'],
			'consent_mode'     => isset( $params['consent_mode'] ) ? sanitize_text_field( $params['consent_mode'] ) : $existing['consent_mode'],
			'countries'        => isset( $params['countries'] ) ? array_map( 'sanitize_text_field', (array) $params['countries'] ) : $existing['countries'],
			'require_explicit' => isset( $params['require_explicit'] ) ? (bool) $params['require_explicit'] : $existing['require_explicit'],
			'show_reject'      => isset( $params['show_reject'] ) ? (bool) $params['show_reject'] : $existing['show_reject'],
			'record_proof'     => isset( $params['record_proof'] ) ? (bool) $params['record_proof'] : $existing['record_proof'],
			'allow_withdraw'   => isset( $params['allow_withdraw'] ) ? (bool) $params['allow_withdraw'] : $existing['allow_withdraw'],
			'active'           => $existing['active'] ?? true,
			'created_at'       => $existing['created_at'] ?? current_time( 'mysql' ),
			'updated_at'       => current_time( 'mysql' ),
		);

		$rules[ $id ] = $rule;
		update_option( 'slos_geo_rules', $rules );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Geo rule updated successfully.', 'shahi-legalflowsuite' ),
				'data'    => $rule,
			),
			200
		);
	}

	/**
	 * Delete geo rule
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_geo_rule( WP_REST_Request $request ) {
		$id    = (int) $request->get_param( 'id' );
		$rules = get_option( 'slos_geo_rules', array() );

		if ( ! isset( $rules[ $id ] ) ) {
			return new WP_Error(
				'rule_not_found',
				__( 'Geo rule not found.', 'shahi-legalflowsuite' ),
				array( 'status' => 404 )
			);
		}

		unset( $rules[ $id ] );
		update_option( 'slos_geo_rules', $rules );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Geo rule deleted successfully.', 'shahi-legalflowsuite' ),
			),
			200
		);
	}

	/**
	 * Duplicate geo rule
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function duplicate_geo_rule( WP_REST_Request $request ) {
		$id    = (int) $request->get_param( 'id' );
		$rules = get_option( 'slos_geo_rules', array() );

		if ( ! isset( $rules[ $id ] ) ) {
			return new WP_Error(
				'rule_not_found',
				__( 'Geo rule not found.', 'shahi-legalflowsuite' ),
				array( 'status' => 404 )
			);
		}

		$original = $rules[ $id ];
		$new_id   = max( array_keys( $rules ) ) + 1;

		$new_rule               = $original;
		$new_rule['id']         = $new_id;
		$new_rule['name']       = $original['name'] . ' ' . __( '(Copy)', 'shahi-legalflowsuite' );
		$new_rule['created_at'] = current_time( 'mysql' );
		$new_rule['updated_at'] = current_time( 'mysql' );

		$rules[ $new_id ] = $new_rule;
		update_option( 'slos_geo_rules', $rules );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Geo rule duplicated successfully.', 'shahi-legalflowsuite' ),
				'data'    => $new_rule,
			),
			201
		);
	}

	/**
	 * Toggle geo rule active state
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function toggle_geo_rule( WP_REST_Request $request ) {
		$id    = (int) $request->get_param( 'id' );
		$rules = get_option( 'slos_geo_rules', array() );

		if ( ! isset( $rules[ $id ] ) ) {
			return new WP_Error(
				'rule_not_found',
				__( 'Geo rule not found.', 'shahi-legalflowsuite' ),
				array( 'status' => 404 )
			);
		}

		$rules[ $id ]['active']     = ! ( $rules[ $id ]['active'] ?? true );
		$rules[ $id ]['updated_at'] = current_time( 'mysql' );

		update_option( 'slos_geo_rules', $rules );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => $rules[ $id ]['active']
					? __( 'Geo rule activated.', 'shahi-legalflowsuite' )
					: __( 'Geo rule deactivated.', 'shahi-legalflowsuite' ),
				'data'    => $rules[ $id ],
			),
			200
		);
	}
}
