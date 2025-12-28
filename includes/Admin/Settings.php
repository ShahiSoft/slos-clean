<?php
/**
 * Settings Admin Page
 *
 * Manages plugin settings with a tabbed interface for different configuration sections.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Core\Security;
use ShahiLegalopsSuite\Database\QueryOptimizer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Page Class
 *
 * Handles the rendering and functionality of the settings page.
 * Provides a tabbed interface for organizing different setting groups.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Settings cache for current request
	 *
	 * Stores settings array in memory to avoid redundant get_option() calls.
	 * Cache is request-scoped and automatically cleared after page load.
	 *
	 * @since 1.0.0
	 * @var array|null
	 */
	private $settings_cache = null;

	/**
	 * Settings option name
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_NAME = 'shahi_legalops_suite_settings';

	/**
	 * Initialize the settings page
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();

		// Register AJAX handlers
		add_action( 'wp_ajax_shahi_export_settings', array( $this, 'ajax_export_settings' ) );
		add_action( 'wp_ajax_shahi_import_settings', array( $this, 'ajax_import_settings' ) );
		add_action( 'wp_ajax_shahi_reset_settings', array( $this, 'ajax_reset_settings' ) );
		add_action( 'wp_ajax_shahi_restart_onboarding', array( $this, 'ajax_restart_onboarding' ) );
	}

	/**
	 * Render the settings page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render() {
		// Verify user capabilities
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'shahi-legalops-suite' ) );
		}

		// Handle form submission
		if ( isset( $_POST['shahi_save_settings'] ) ) {
			$this->save_settings();
		}

		// Get current settings
		$settings = $this->get_settings();

		// Get active tab
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

		// Get tabs
		$tabs = $this->get_tabs();

		// Validate active tab
		if ( ! isset( $tabs[ $active_tab ] ) ) {
			$active_tab = 'general';
		}

		// Load template
		include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/settings.php';
	}

	/**
	 * Render banner-only settings page (accessed from Consent & Compliance)
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_banner_settings() {
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'shahi-legalops-suite' ) );
		}

		if ( isset( $_POST['shahi_save_settings'] ) ) {
			$this->save_settings();
		}

		$settings = $this->get_settings();

		include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/banner-settings.php';
	}

	/**
	 * Get settings tabs
	 *
	 * @since 1.0.0
	 * @return array Settings tabs
	 */
	private function get_tabs() {
		return array(
			'general'       => array(
				'title' => __( 'General', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-admin-generic',
			),
			'notifications' => array(
				'title' => __( 'Notifications', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-email',
			),
			'performance'   => array(
				'title' => __( 'Performance', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-performance',
			),
			'security'      => array(
				'title' => __( 'Security', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-shield',
			),
			'privacy'       => array(
				'title' => __( 'Privacy', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-lock',
			),
			'advanced'      => array(
				'title' => __( 'Advanced', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-admin-tools',
			),
			'import_export' => array(
				'title' => __( 'Import/Export', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-database-export',
			),
			'uninstall'     => array(
				'title' => __( 'Uninstall', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-trash',
			),
		);
	}

	/**
	 * Get current settings
	 *
	 * @since 1.0.0
	 * @return array Current settings
	 */
	public function get_settings() {
		// Return cached settings if available (improves performance on same page load)
		if ( null !== $this->settings_cache ) {
			return $this->settings_cache;
		}

		$defaults             = $this->get_default_settings();
		$saved                = get_option( self::OPTION_NAME, array() );
		$this->settings_cache = wp_parse_args( $saved, $defaults );

		return $this->settings_cache;
	}

	/**
	 * Get default settings
	 *
	 * @since 1.0.0
	 * @return array Default settings
	 */
	private function get_default_settings() {
		return array(
			// General settings
			'plugin_name'                => __( 'ShahiLegalopsSuite', 'shahi-legalops-suite' ),
			'enable_debug'               => false,
			'delete_data_on_uninstall'   => false,

			// Privacy settings - Geolocation
			'enable_geolocation_detection' => true,
			'geolocation_provider'         => 'ipapi', // ipapi | ipinfo | ip-api
			'geolocation_cache_ttl'        => 86400,
			'geolocation_override_region'  => '', // '', 'EU','US-CA','US','BR','GLOBAL'

			// Privacy settings - Consent Banner
			'consent_banner_template'      => 'eu', // eu | ccpa | simple | advanced
			'consent_banner_position'      => 'bottom', // bottom | top
			'consent_banner_theme'         => 'light', // light | dark | auto
			'consent_banner_primary_color' => '#4CAF50',
			'consent_banner_accept_color'  => '#4CAF50',
			'consent_banner_reject_color'  => '#f44336',

			// Consent Banner - Behavior
			'show_accept_selected'         => true,
			'enable_reduced_motion'        => true,
			'block_scripts_until_consent'  => true,
			'banner_region_scope'          => 'GLOBAL', // '', 'EU','US-CA','US','BR','GLOBAL'
			'reprompt_interval_days'       => 180,
			'hide_banner_for_bots'         => true,

			// Privacy settings - Banner Text
			'consent_banner_heading'       => __( 'We value your privacy', 'shahi-legalops-suite' ),
			'consent_banner_message'       => __( 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic.', 'shahi-legalops-suite' ),
			'consent_accept_button_text'   => __( 'Accept All', 'shahi-legalops-suite' ),
			'consent_reject_button_text'   => __( 'Reject All', 'shahi-legalops-suite' ),
			'consent_settings_button_text' => __( 'Cookie Settings', 'shahi-legalops-suite' ),
			'consent_privacy_policy_text'  => __( 'Privacy Policy', 'shahi-legalops-suite' ),
			'privacy_policy_url'           => '',
			'learn_more_url'               => '',

			// Privacy settings - Cookie Scanner
			'cookie_scanner_auto_scan'     => true,
			'cookie_scanner_frequency'     => 'daily', // daily | weekly | monthly
			'cookie_scanner_last_run'      => '',

			// Privacy settings - Data Retention
			'consent_retention_days'       => 365, // GDPR recommends 12-24 months
			'consent_log_retention_days'   => 365,
			'auto_delete_expired'          => false,

			// Privacy settings - Additional Controls
			'respect_dnt'                  => true,
			'show_preferences_link'        => true,
			'preferences_show_history'     => true,
			'preferences_show_download'    => true,

			// Export/Import settings
			'slos_export_format'               => 'csv',
			'slos_scheduled_exports_enabled'   => false,
			'slos_export_frequency'            => 'weekly',
			'slos_export_email_notification'   => false,
			'slos_export_limit'                => 10000,
			'slos_export_date_range'           => 'all',

			// Analytics settings
			'enable_analytics'           => true,
			'track_logged_in_users'      => true,
			'analytics_retention_days'   => 90,
			'anonymize_ip'               => true,

			// Notification settings
			'enable_email_notifications' => true,
			'notification_email'         => get_option( 'admin_email' ),
			'notify_on_error'            => true,
			'notify_on_module_change'    => false,

			// Performance settings
			'enable_caching'             => true,
			'cache_duration'             => 3600,
			'enable_minification'        => true,
			'lazy_load_assets'           => true,

			// Security settings
			'enable_rate_limiting'       => true,
			'ip_blacklist'               => '',
			'file_upload_restrictions'   => true,
			'two_factor_auth'            => false,
			'activity_logging'           => true,

			// Advanced settings
			'api_enabled'                => false,
			'api_key'                    => '',
			'rate_limit_enabled'         => true,
			'rate_limit_requests'        => 100,
			'rate_limit_window'          => 60,

			// Uninstall settings
			'preserve_landing_pages'     => false,
			'preserve_analytics_data'    => false,
			'preserve_settings'          => false,
			'preserve_user_capabilities' => false,
			'complete_cleanup'           => true,

			// License settings
			'license_key'                => '',
			'license_status'             => 'inactive',
			'license_expires'            => '',
		);
	}

	/**
	 * Save settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function save_settings() {
		// Verify nonce
		if ( ! isset( $_POST['shahi_settings_nonce'] ) || ! wp_verify_nonce( $_POST['shahi_settings_nonce'], 'shahi_save_settings' ) ) {
			add_settings_error(
				'shahi_settings',
				'invalid_nonce',
				__( 'Security check failed. Please try again.', 'shahi-legalops-suite' ),
				'error'
			);
			return;
		}

		// Check user capabilities
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			add_settings_error(
				'shahi_settings',
				'insufficient_permissions',
				__( 'You do not have sufficient permissions to perform this action.', 'shahi-legalops-suite' ),
				'error'
			);
			return;
		}

		// Get current settings
		$settings = $this->get_settings();

		// Update general settings

				// Update privacy settings
				$settings['enable_geolocation_detection'] = isset( $_POST['enable_geolocation_detection'] );
				if ( isset( $_POST['geolocation_provider'] ) ) {
					$provider                        = sanitize_text_field( $_POST['geolocation_provider'] );
					$allowed_providers               = array( 'ipapi', 'ipinfo', 'ip-api' );
					$settings['geolocation_provider'] = in_array( $provider, $allowed_providers, true ) ? $provider : 'ipapi';
				}
				if ( isset( $_POST['geolocation_cache_ttl'] ) ) {
					$ttl                             = absint( $_POST['geolocation_cache_ttl'] );
					$settings['geolocation_cache_ttl'] = max( 60, min( 7 * 24 * 60 * 60, $ttl ) ); // 60s..7d
				}
				if ( isset( $_POST['geolocation_override_region'] ) ) {
					$region                            = strtoupper( sanitize_text_field( $_POST['geolocation_override_region'] ) );
					$allowed_regions                   = array( '', 'EU', 'US-CA', 'US', 'BR', 'GLOBAL' );
					$settings['geolocation_override_region'] = in_array( $region, $allowed_regions, true ) ? $region : '';
				}

				// Update consent banner settings
				if ( isset( $_POST['consent_banner_template'] ) ) {
					$template                          = sanitize_text_field( $_POST['consent_banner_template'] );
					$allowed_templates                 = array( 'eu', 'ccpa', 'simple', 'advanced' );
					$settings['consent_banner_template'] = in_array( $template, $allowed_templates, true ) ? $template : 'eu';
				}
				if ( isset( $_POST['consent_banner_position'] ) ) {
					$position                          = sanitize_text_field( $_POST['consent_banner_position'] );
					$allowed_positions                 = array( 'bottom', 'top' );
					$settings['consent_banner_position'] = in_array( $position, $allowed_positions, true ) ? $position : 'bottom';
				}
				if ( isset( $_POST['consent_banner_theme'] ) ) {
					$theme                          = sanitize_text_field( $_POST['consent_banner_theme'] );
					$allowed_themes                 = array( 'light', 'dark', 'auto' );
					$settings['consent_banner_theme'] = in_array( $theme, $allowed_themes, true ) ? $theme : 'light';
				}
				if ( isset( $_POST['consent_banner_primary_color'] ) ) {
					$settings['consent_banner_primary_color'] = sanitize_hex_color( $_POST['consent_banner_primary_color'] );
				}
				if ( isset( $_POST['consent_banner_accept_color'] ) ) {
					$settings['consent_banner_accept_color'] = sanitize_hex_color( $_POST['consent_banner_accept_color'] );
				}
				if ( isset( $_POST['consent_banner_reject_color'] ) ) {
					$settings['consent_banner_reject_color'] = sanitize_hex_color( $_POST['consent_banner_reject_color'] );
				}

				// Consent banner behavior
				$settings['show_accept_selected']        = isset( $_POST['show_accept_selected'] );
				$settings['enable_reduced_motion']       = isset( $_POST['enable_reduced_motion'] );
				$settings['block_scripts_until_consent'] = isset( $_POST['block_scripts_until_consent'] );
				if ( isset( $_POST['banner_region_scope'] ) ) {
					$scope                         = strtoupper( sanitize_text_field( $_POST['banner_region_scope'] ) );
					$allowed_regions               = array( '', 'EU', 'US-CA', 'US', 'BR', 'GLOBAL' );
					$settings['banner_region_scope'] = in_array( $scope, $allowed_regions, true ) ? $scope : 'GLOBAL';
				}
				if ( isset( $_POST['reprompt_interval_days'] ) ) {
					$days                             = absint( $_POST['reprompt_interval_days'] );
					$settings['reprompt_interval_days'] = max( 7, min( 1095, $days ) ); // 1 week .. 3 years
				}
				$settings['hide_banner_for_bots'] = isset( $_POST['hide_banner_for_bots'] );

				// Update consent banner text
				if ( isset( $_POST['consent_banner_heading'] ) ) {
					$settings['consent_banner_heading'] = sanitize_text_field( $_POST['consent_banner_heading'] );
				}
				if ( isset( $_POST['consent_banner_message'] ) ) {
					$settings['consent_banner_message'] = sanitize_textarea_field( $_POST['consent_banner_message'] );
				}
				if ( isset( $_POST['consent_accept_button_text'] ) ) {
					$settings['consent_accept_button_text'] = sanitize_text_field( $_POST['consent_accept_button_text'] );
				}
				if ( isset( $_POST['consent_reject_button_text'] ) ) {
					$settings['consent_reject_button_text'] = sanitize_text_field( $_POST['consent_reject_button_text'] );
				}
				if ( isset( $_POST['consent_settings_button_text'] ) ) {
					$settings['consent_settings_button_text'] = sanitize_text_field( $_POST['consent_settings_button_text'] );
				}
				if ( isset( $_POST['consent_privacy_policy_text'] ) ) {
					$settings['consent_privacy_policy_text'] = sanitize_text_field( $_POST['consent_privacy_policy_text'] );
				}
				if ( isset( $_POST['privacy_policy_url'] ) ) {
					$settings['privacy_policy_url'] = esc_url_raw( $_POST['privacy_policy_url'] );
				}
				if ( isset( $_POST['learn_more_url'] ) ) {
					$settings['learn_more_url'] = esc_url_raw( $_POST['learn_more_url'] );
				}

				// Update cookie scanner settings
				$settings['cookie_scanner_auto_scan'] = isset( $_POST['cookie_scanner_auto_scan'] );
				if ( isset( $_POST['cookie_scanner_frequency'] ) ) {
					$frequency                           = sanitize_text_field( $_POST['cookie_scanner_frequency'] );
					$allowed_frequencies                 = array( 'daily', 'weekly', 'monthly' );
					$settings['cookie_scanner_frequency'] = in_array( $frequency, $allowed_frequencies, true ) ? $frequency : 'daily';
				}

				// Update data retention settings
				if ( isset( $_POST['consent_retention_days'] ) ) {
					$days                            = absint( $_POST['consent_retention_days'] );
					$settings['consent_retention_days'] = max( 30, min( 1095, $days ) ); // 30 days to 3 years
				}
				if ( isset( $_POST['consent_log_retention_days'] ) ) {
					$days                                = absint( $_POST['consent_log_retention_days'] );
					$settings['consent_log_retention_days'] = max( 30, min( 1095, $days ) ); // 30 days to 3 years
				}
				$settings['auto_delete_expired'] = isset( $_POST['auto_delete_expired'] );

				// Additional privacy controls
				$settings['respect_dnt']               = isset( $_POST['respect_dnt'] );
				$settings['show_preferences_link']     = isset( $_POST['show_preferences_link'] );
				$settings['preferences_show_history']  = isset( $_POST['preferences_show_history'] );
				$settings['preferences_show_download'] = isset( $_POST['preferences_show_download'] );

				// Update export/import settings
				if ( isset( $_POST['slos_export_format'] ) ) {
					$format                        = sanitize_text_field( $_POST['slos_export_format'] );
					$allowed_formats               = array( 'csv', 'json', 'pdf' );
					$settings['slos_export_format'] = in_array( $format, $allowed_formats, true ) ? $format : 'csv';
				}
				$settings['slos_scheduled_exports_enabled'] = isset( $_POST['slos_scheduled_exports_enabled'] );
				if ( isset( $_POST['slos_export_frequency'] ) ) {
					$frequency                         = sanitize_text_field( $_POST['slos_export_frequency'] );
					$allowed_frequencies               = array( 'daily', 'weekly' );
					$settings['slos_export_frequency'] = in_array( $frequency, $allowed_frequencies, true ) ? $frequency : 'weekly';
				}
				$settings['slos_export_email_notification'] = isset( $_POST['slos_export_email_notification'] );

		if ( isset( $_POST['plugin_name'] ) ) {
			$settings['plugin_name'] = sanitize_text_field( $_POST['plugin_name'] );
		}
		$settings['enable_debug']             = isset( $_POST['enable_debug'] );
		$settings['delete_data_on_uninstall'] = isset( $_POST['delete_data_on_uninstall'] );

		// Update analytics settings
		$settings['enable_analytics']      = isset( $_POST['enable_analytics'] );
		$settings['track_logged_in_users'] = isset( $_POST['track_logged_in_users'] );
		if ( isset( $_POST['analytics_retention_days'] ) ) {
			$settings['analytics_retention_days'] = absint( $_POST['analytics_retention_days'] );
		}
		$settings['anonymize_ip'] = isset( $_POST['anonymize_ip'] );

		// Update notification settings
		$settings['enable_email_notifications'] = isset( $_POST['enable_email_notifications'] );
		if ( isset( $_POST['notification_email'] ) ) {
			$email                          = sanitize_email( $_POST['notification_email'] );
			$settings['notification_email'] = is_email( $email ) ? $email : get_option( 'admin_email' );
		}
		$settings['notify_on_error']         = isset( $_POST['notify_on_error'] );
		$settings['notify_on_module_change'] = isset( $_POST['notify_on_module_change'] );

		// Update performance settings
		$settings['enable_caching'] = isset( $_POST['enable_caching'] );
		if ( isset( $_POST['cache_duration'] ) ) {
			$settings['cache_duration'] = absint( $_POST['cache_duration'] );
		}
		$settings['enable_minification'] = isset( $_POST['enable_minification'] );
		$settings['lazy_load_assets']    = isset( $_POST['lazy_load_assets'] );

		// Update security settings
		$settings['enable_rate_limiting'] = isset( $_POST['enable_rate_limiting'] );
		if ( isset( $_POST['ip_blacklist'] ) ) {
			$settings['ip_blacklist'] = sanitize_textarea_field( $_POST['ip_blacklist'] );
		}
		$settings['file_upload_restrictions'] = isset( $_POST['file_upload_restrictions'] );
		$settings['two_factor_auth']          = isset( $_POST['two_factor_auth'] );
		$settings['activity_logging']         = isset( $_POST['activity_logging'] );

		// Update advanced settings
		$settings['api_enabled'] = isset( $_POST['api_enabled'] );
		if ( isset( $_POST['api_key'] ) ) {
			$settings['api_key'] = sanitize_text_field( $_POST['api_key'] );
		}
		$settings['rate_limit_enabled'] = isset( $_POST['rate_limit_enabled'] );
		if ( isset( $_POST['rate_limit_requests'] ) ) {
			$settings['rate_limit_requests'] = absint( $_POST['rate_limit_requests'] );
		}
		if ( isset( $_POST['rate_limit_window'] ) ) {
			$settings['rate_limit_window'] = absint( $_POST['rate_limit_window'] );
		}

		// Update uninstall settings
		$settings['preserve_landing_pages']     = isset( $_POST['preserve_landing_pages'] );
		$settings['preserve_analytics_data']    = isset( $_POST['preserve_analytics_data'] );
		$settings['preserve_settings']          = isset( $_POST['preserve_settings'] );
		$settings['preserve_user_capabilities'] = isset( $_POST['preserve_user_capabilities'] );
		$settings['complete_cleanup']           = isset( $_POST['complete_cleanup'] );

		// Update license settings
		if ( isset( $_POST['license_key'] ) ) {
			$settings['license_key'] = sanitize_text_field( $_POST['license_key'] );
		}

		// Save settings
		$updated = update_option( self::OPTION_NAME, $settings );

		// Invalidate settings cache when settings are updated
		if ( $updated ) {
			$this->settings_cache = null;
		}

		if ( $updated ) {
			// Track analytics event
			$this->track_settings_event();

			add_settings_error(
				'shahi_settings',
				'settings_saved',
				__( 'Settings saved successfully.', 'shahi-legalops-suite' ),
				'success'
			);
		} else {
			add_settings_error(
				'shahi_settings',
				'settings_unchanged',
				__( 'Settings unchanged or failed to save.', 'shahi-legalops-suite' ),
				'info'
			);
		}
	}

	/**
	 * Track settings update event
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function track_settings_event() {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if analytics table exists (cached for performance)
		if ( ! QueryOptimizer::table_exists_cached( $analytics_table ) ) {
			return;
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

		$event_data = json_encode(
			array(
				'section'   => $tab,
				'timestamp' => current_time( 'mysql' ),
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'settings_updated',
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $this->security->get_client_ip(),
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Generate API key
	 *
	 * Generates a random API key for REST API authentication.
	 *
	 * @since 1.0.0
	 * @return string Generated API key
	 */
	public static function generate_api_key() {
		return wp_generate_password( 32, false );
	}

	/**
	 * Reset settings to defaults
	 *
	 * This method would be called via AJAX to reset all settings.
	 *
	 * @since 1.0.0
	 * @return bool True on success, false on failure
	 */
	public function reset_settings() {
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			return false;
		}

		$defaults = $this->get_default_settings();
		return update_option( self::OPTION_NAME, $defaults );
	}

	/**
	 * Export settings
	 *
	 * Exports current settings as JSON.
	 *
	 * @since 1.0.0
	 * @return string JSON encoded settings
	 */
	public function export_settings() {
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			return '';
		}

		$settings = $this->get_settings();
		return json_encode( $settings, JSON_PRETTY_PRINT );
	}

	/**
	 * Import settings
	 *
	 * Imports settings from JSON.
	 *
	 * @since 1.0.0
	 * @param string $json JSON encoded settings.
	 * @return bool True on success, false on failure
	 */
	public function import_settings( $json ) {
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			return false;
		}

		$settings = json_decode( $json, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $settings ) ) {
			return false;
		}

		// Validate settings structure
		$defaults  = $this->get_default_settings();
		$validated = array();

		foreach ( $defaults as $key => $default_value ) {
			if ( isset( $settings[ $key ] ) ) {
				$validated[ $key ] = $settings[ $key ];
			}
		}

		return update_option( self::OPTION_NAME, $validated );
	}

	/**
	 * AJAX handler for exporting settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_export_settings() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_settings_ajax' ) ) {
			wp_send_json_error( 'Security check failed.' );
		}

		// Check capabilities
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			wp_send_json_error( 'Insufficient permissions.' );
		}

		$settings = $this->export_settings();

		if ( empty( $settings ) ) {
			wp_send_json_error( 'Failed to export settings.' );
		}

		wp_send_json_success( $settings );
	}

	/**
	 * AJAX handler for importing settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_import_settings() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_settings_ajax' ) ) {
			wp_send_json_error( 'Security check failed.' );
		}

		// Check capabilities
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			wp_send_json_error( 'Insufficient permissions.' );
		}

		if ( ! isset( $_POST['settings'] ) ) {
			wp_send_json_error( 'No settings data provided.' );
		}

		$json   = wp_unslash( $_POST['settings'] );
		$result = $this->import_settings( $json );

		if ( $result ) {
			wp_send_json_success( 'Settings imported successfully.' );
		} else {
			wp_send_json_error( 'Failed to import settings. Invalid data format.' );
		}
	}

	/**
	 * AJAX handler for resetting settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_reset_settings() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_settings_ajax' ) ) {
			wp_send_json_error( 'Security check failed.' );
		}

		// Check capabilities
		if ( ! current_user_can( 'edit_shahi_settings' ) ) {
			wp_send_json_error( 'Insufficient permissions.' );
		}

		$result = $this->reset_settings();

		if ( $result ) {
			wp_send_json_success( 'Settings reset successfully.' );
		} else {
			wp_send_json_error( 'Failed to reset settings.' );
		}
	}

	/**
	 * AJAX handler for restarting onboarding wizard
	 * Uses nuclear force delete + cache flush approach
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_restart_onboarding() {
		// Verify nonce using Security class (handles prefix automatically)
		if ( ! Security::verify_nonce( $_POST['nonce'] ?? '', 'shahi_settings_ajax' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed.', 'shahi-legalops-suite' ),
				)
			);
		}

		// Check capabilities (align with settings page access)
		if ( ! current_user_can( 'manage_shahi_template' ) && ! current_user_can( 'edit_shahi_settings' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Insufficient permissions.', 'shahi-legalops-suite' ),
				)
			);
		}

		// Step 1: Delete via WordPress API
		delete_option( 'shahi_legalops_suite_onboarding_completed' );
		delete_option( 'shahi_legalops_suite_onboarding_data' );

		// Step 2: Direct database deletion (bypass all caching)
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN ('shahi_legalops_suite_onboarding_completed', 'shahi_legalops_suite_onboarding_data')" );

		// Step 3: Flush ALL caches (nuclear approach)
		wp_cache_flush();

		// Step 4: Verify deletion
		$still_completed = get_option( 'shahi_legalops_suite_onboarding_completed', false );

		if ( $still_completed ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to reset onboarding wizard. Please try clearing your cache.', 'shahi-legalops-suite' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Onboarding wizard has been reset successfully.', 'shahi-legalops-suite' ),
				'redirect' => admin_url( 'admin.php?page=shahi-legalops-suite' ),
			)
		);
	}
}

