<?php
/**
 * Settings API Controller
 *
 * Handles REST API endpoints for plugin settings.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\API;

use ShahiLegalFlowSuite\Admin\Settings;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsController
 *
 * REST API controller for settings endpoints.
 *
 * @since 1.0.0
 */
class SettingsController {

	/**
	 * Settings instance
	 *
	 * @since 1.0.0
	 * @var Settings
	 */
	private $settings;

	/**
	 * Initialize controller
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->settings = new Settings();
	}

	/**
	 * Register routes
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		// Get all settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_editor' ),
			)
		);

		// Update settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_admin' ),
				'args'                => array(
					'settings' => array(
						'required' => true,
						'type'     => 'object',
					),
				),
			)
		);

		// Export settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings/export',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'export_settings' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_admin' ),
			)
		);

		// Import settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings/import',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'import_settings' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_admin' ),
				'args'                => array(
					'settings' => array(
						'required' => true,
						'type'     => 'string',
					),
				),
			)
		);

		// Get banner settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings/banner',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_banner_settings' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_editor' ),
			)
		);

		// Update banner settings
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings/banner',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_banner_settings' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_admin' ),
			)
		);

		// Reset banner settings to defaults
		register_rest_route(
			RestAPI::get_namespace(),
			'/settings/banner/reset',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reset_banner_settings' ),
				'permission_callback' => array( 'ShahiLegalFlowSuite\API\RestAPI', 'permission_callback_admin' ),
			)
		);
	}

	/**
	 * Get all settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_settings( $request ) {
		$settings = $this->settings->get_settings();

		return RestAPI::success( $settings, 'Settings retrieved successfully' );
	}

	/**
	 * Update settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function update_settings( $request ) {
		$new_settings = $request->get_param( 'settings' );

		if ( ! is_array( $new_settings ) ) {
			return RestAPI::error( 'Invalid settings format', 400 );
		}

		// Get current settings
		$current_settings = $this->settings->get_settings();

		// Merge and sanitize
		$updated_settings = array_merge(
			$current_settings,
			RestAPI::sanitize_request( $new_settings )
		);

		// Update
		$result = update_option( Settings::OPTION_NAME, $updated_settings );

		if ( $result === false ) {
			return RestAPI::error( 'Failed to update settings', 500 );
		}

		return RestAPI::success(
			$updated_settings,
			'Settings updated successfully'
		);
	}

	/**
	 * Export settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function export_settings( $request ) {
		$json = $this->settings->export_settings();

		if ( empty( $json ) ) {
			return RestAPI::error( 'Failed to export settings', 500 );
		}

		return RestAPI::success(
			array( 'json' => $json ),
			'Settings exported successfully'
		);
	}

	/**
	 * Import settings
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function import_settings( $request ) {
		$json = $request->get_param( 'settings' );

		if ( empty( $json ) ) {
			return RestAPI::error( 'No settings data provided', 400 );
		}

		$result = $this->settings->import_settings( $json );

		if ( ! $result ) {
			return RestAPI::error( 'Failed to import settings. Invalid data format.', 400 );
		}

		return RestAPI::success(
			$this->settings->get_settings(),
			'Settings imported successfully'
		);
	}

	/**
	 * Get banner settings
	 *
	 * @since 3.1.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function get_banner_settings( $request ) {
		$banner_settings = get_option( 'shahi_legalflowsuite_banner_settings', $this->get_default_banner_settings() );

		return RestAPI::success( $banner_settings, 'Banner settings retrieved successfully' );
	}

	/**
	 * Update banner settings
	 *
	 * @since 3.1.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function update_banner_settings( $request ) {
		$new_settings = $request->get_json_params();

		if ( empty( $new_settings ) ) {
			return RestAPI::error( 'No banner settings provided', 400 );
		}

		// Sanitize and validate settings
		$sanitized_settings = $this->sanitize_banner_settings( $new_settings );

		// Get current settings and merge
		$current_settings = get_option( 'shahi_legalflowsuite_banner_settings', array() );
		$updated_settings = array_merge( $current_settings, $sanitized_settings );

		// Update option
		$result = update_option( 'shahi_legalflowsuite_banner_settings', $updated_settings );

		if ( $result === false ) {
			// Check if value didn't change
			$current = get_option( 'shahi_legalflowsuite_banner_settings' );
			if ( $current !== $updated_settings ) {
				return RestAPI::error( 'Failed to update banner settings', 500 );
			}
		}

		return RestAPI::success(
			$updated_settings,
			'Banner settings updated successfully'
		);
	}

	/**
	 * Reset banner settings to defaults
	 *
	 * @since 3.1.0
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error Response
	 */
	public function reset_banner_settings( $request ) {
		$default_settings = $this->get_default_banner_settings();

		update_option( 'shahi_legalflowsuite_banner_settings', $default_settings );

		return RestAPI::success(
			$default_settings,
			'Banner settings reset to defaults successfully'
		);
	}

	/**
	 * Get default banner settings
	 *
	 * @since 3.1.0
	 * @return array Default banner settings
	 */
	private function get_default_banner_settings() {
		return array(
			'template'               => 'eu',
			'position'               => 'bottom',
			'layout'                 => 'box',
			'theme'                  => 'dark',
			'primary_color'          => '#10b981',
			'text_color'             => '#ffffff',
			'bg_color'               => '#1e293b',
			'show_toggle'            => true,
			'show_categories'        => true,
			'icon_position'          => 'left',
			'animation'              => 'slide',
			'title'                  => __( 'We value your privacy', 'shahi-legalflowsuite' ),
			'message'                => __( 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.', 'shahi-legalflowsuite' ),
			'accept_text'            => __( 'Accept All', 'shahi-legalflowsuite' ),
			'reject_text'            => __( 'Reject All', 'shahi-legalflowsuite' ),
			'settings_text'          => __( 'Cookie Settings', 'shahi-legalflowsuite' ),
			'privacy_url'            => '',
			'learn_more_text'        => __( 'Learn more', 'shahi-legalflowsuite' ),
			'consent_expiry_days'    => 30,
			'grace_period_days'      => 0,
			'category_descriptions'  => array(),
			'vendors'                => array(
				'necessary'    => array(),
				'functional'   => array(),
				'analytics'    => array(),
				'marketing'    => array(),
				'preferences'  => array(),
			),
		);
	}

	/**
	 * Sanitize banner settings
	 *
	 * @since 3.1.0
	 * @param array $settings Settings to sanitize.
	 * @return array Sanitized settings
	 */
	private function sanitize_banner_settings( $settings ) {
		$sanitized = array();

		// Template
		if ( isset( $settings['template'] ) ) {
			$sanitized['template'] = in_array( $settings['template'], array( 'eu', 'gdpr', 'ccpa', 'simple', 'advanced' ), true ) 
				? $settings['template'] 
				: 'eu';
		}

		// Position
		if ( isset( $settings['position'] ) ) {
			$sanitized['position'] = in_array( $settings['position'], array( 'top', 'bottom', 'center' ), true ) 
				? $settings['position'] 
				: 'bottom';
		}

		// Layout
		if ( isset( $settings['layout'] ) ) {
			$sanitized['layout'] = in_array( $settings['layout'], array( 'bar', 'box', 'popup' ), true ) 
				? $settings['layout'] 
				: 'box';
		}

		// Colors
		if ( isset( $settings['primary_color'] ) ) {
			$sanitized['primary_color'] = sanitize_hex_color( $settings['primary_color'] );
		}
		if ( isset( $settings['bg_color'] ) ) {
			$sanitized['bg_color'] = sanitize_hex_color( $settings['bg_color'] );
		}
		if ( isset( $settings['text_color'] ) ) {
			$sanitized['text_color'] = sanitize_hex_color( $settings['text_color'] );
		}

		// Text fields
		$text_fields = array( 'title', 'message', 'accept_text', 'reject_text', 'settings_text', 'learn_more_text' );
		foreach ( $text_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $settings[ $field ] );
			}
		}

		// Boolean fields
		$boolean_fields = array( 'show_toggle', 'show_categories' );
		foreach ( $boolean_fields as $field ) {
			if ( isset( $settings[ $field ] ) ) {
				$sanitized[ $field ] = (bool) $settings[ $field ];
			}
		}

		// Icon position
		if ( isset( $settings['icon_position'] ) ) {
			$sanitized['icon_position'] = in_array( $settings['icon_position'], array( 'left', 'right' ), true ) 
				? $settings['icon_position'] 
				: 'left';
		}

		// Privacy URL
		if ( isset( $settings['privacy_url'] ) ) {
			$sanitized['privacy_url'] = esc_url_raw( $settings['privacy_url'] );
		}

		// Consent expiry days (1-365)
		if ( isset( $settings['consent_expiry_days'] ) ) {
			$sanitized['consent_expiry_days'] = max( 1, min( 365, absint( $settings['consent_expiry_days'] ) ) );
		}

		// Grace period days
		if ( isset( $settings['grace_period_days'] ) ) {
			$sanitized['grace_period_days'] = in_array( absint( $settings['grace_period_days'] ), array( 0, 7, 30 ), true ) 
				? absint( $settings['grace_period_days'] ) 
				: 0;
		}

		// Category descriptions
		if ( isset( $settings['category_descriptions'] ) && is_array( $settings['category_descriptions'] ) ) {
			$sanitized['category_descriptions'] = array();
			foreach ( $settings['category_descriptions'] as $category => $description ) {
				$sanitized['category_descriptions'][ sanitize_key( $category ) ] = sanitize_textarea_field( $description );
			}
		}

		// Vendors
		if ( isset( $settings['vendors'] ) && is_array( $settings['vendors'] ) ) {
			$sanitized['vendors'] = array();
			$valid_categories = array( 'necessary', 'functional', 'analytics', 'marketing', 'preferences' );
			
			foreach ( $valid_categories as $category ) {
				if ( isset( $settings['vendors'][ $category ] ) && is_array( $settings['vendors'][ $category ] ) ) {
					$sanitized['vendors'][ $category ] = array();
					
					foreach ( $settings['vendors'][ $category ] as $vendor ) {
						if ( is_array( $vendor ) ) {
							$sanitized['vendors'][ $category ][] = array(
								'name'    => isset( $vendor['name'] ) ? sanitize_text_field( $vendor['name'] ) : '',
								'purpose' => isset( $vendor['purpose'] ) ? sanitize_text_field( $vendor['purpose'] ) : '',
							);
						}
					}
				} else {
					$sanitized['vendors'][ $category ] = array();
				}
			}
		}

		return $sanitized;
	}
}
