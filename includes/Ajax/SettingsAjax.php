<?php
/**
 * Settings AJAX Handler
 *
 * Handles AJAX requests for settings management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SettingsAjax
 *
 * AJAX handler for settings-related operations.
 *
 * @since 1.0.0
 */
class SettingsAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_save_settings', array( $this, 'save_settings' ) );
		add_action( 'wp_ajax_shahi_reset_settings', array( $this, 'reset_settings' ) );
	}

	/**
	 * Save settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_settings() {
		// Verify request
		AjaxHandler::verify_request( 'shahi_save_settings', 'manage_shahi_template' );

		// Get settings data
		if ( ! isset( $_POST['settings'] ) ) {
			AjaxHandler::error( 'Settings data is required' );
		}

		$settings = $_POST['settings'];

		// Sanitize settings
		$sanitized_settings = AjaxHandler::sanitize_data( $settings );

		// Save settings by category
		$saved_categories = array();
		foreach ( $sanitized_settings as $category => $values ) {
			$option_name = 'shahi_settings_' . $category;
			update_option( $option_name, $values );
			$saved_categories[] = $category;
		}

		// Track analytics event
		$this->track_settings_event( 'saved', $saved_categories );

		AjaxHandler::success(
			array( 'settings' => $sanitized_settings ),
			'Settings saved successfully'
		);
	}

	/**
	 * Reset settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function reset_settings() {
		// Verify request
		AjaxHandler::verify_request( 'shahi_reset_settings', 'manage_shahi_template' );

		// Get category to reset (optional)
		$category = isset( $_POST['category'] ) ? sanitize_key( $_POST['category'] ) : '';

		// Define default settings
		$default_settings = $this->get_default_settings();

		if ( ! empty( $category ) ) {
			// Reset specific category
			if ( ! isset( $default_settings[ $category ] ) ) {
				AjaxHandler::error( 'Invalid category' );
			}

			$option_name = 'shahi_settings_' . $category;
			update_option( $option_name, $default_settings[ $category ] );

			$reset_categories = array( $category );
		} else {
			// Reset all settings
			foreach ( $default_settings as $cat => $values ) {
				$option_name = 'shahi_settings_' . $cat;
				update_option( $option_name, $values );
			}

			$reset_categories = array_keys( $default_settings );
		}

		// Track analytics event
		$this->track_settings_event( 'reset', $reset_categories );

		AjaxHandler::success(
			array( 'settings' => $default_settings ),
			'Settings reset successfully'
		);
	}

	/**
	 * Get default settings
	 *
	 * @since 1.0.0
	 * @return array Default settings.
	 */
	private function get_default_settings() {
		return array(
			'general'     => array(
				'site_name'    => get_bloginfo( 'name' ),
				'site_tagline' => get_bloginfo( 'description' ),
				'admin_email'  => get_option( 'admin_email' ),
				'timezone'     => get_option( 'timezone_string' ),
			),
			'modules'     => array(
				'auto_enable'   => false,
				'load_priority' => 'normal',
			),
			'analytics'   => array(
				'enabled'        => true,
				'track_admin'    => false,
				'retention_days' => 90,
			),
			'security'    => array(
				'nonce_lifetime'          => 86400,
				'rate_limiting'           => true,
				'max_requests_per_minute' => 60,
			),
			'performance' => array(
				'cache_enabled' => true,
				'cache_ttl'     => 3600,
				'minify_css'    => false,
				'minify_js'     => false,
			),
		);
	}

	/**
	 * Track settings analytics event
	 *
	 * @since 1.0.0
	 * @param string $action     Action performed.
	 * @param array  $categories Categories affected.
	 * @return void
	 */
	private function track_settings_event( $action, $categories ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'action'     => $action,
				'categories' => $categories,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'settings_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
