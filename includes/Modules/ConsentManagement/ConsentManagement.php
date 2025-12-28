<?php
/**
 * Consent Management Module
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Modules\ConsentManagement
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalopsSuite\Modules\ConsentManagement;

use ShahiLegalopsSuite\Modules\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent Management Module Class
 *
 * @since 3.0.1
 */
class ConsentManagement extends Module {

	/**
	 * Get module unique key
	 *
	 * @since 3.0.1
	 * @return string Module key
	 */
	public function get_key() {
		return 'consent-management';
	}

	/**
	 * Get module name
	 *
	 * @since 3.0.1
	 * @return string Module name
	 */
	public function get_name() {
		return 'Consent Management';
	}

	/**
	 * Get module description
	 *
	 * @since 3.0.1
	 * @return string Module description
	 */
	public function get_description() {
		return 'GDPR-compliant consent management system with audit logs, user preferences, and compliance tracking.';
	}

	/**
	 * Get module icon
	 *
	 * @since 3.0.1
	 * @return string Icon class
	 */
	public function get_icon() {
		return 'dashicons-shield-alt';
	}

	/**
	 * Get module category
	 *
	 * @since 3.0.1
	 * @return string Category
	 */
	public function get_category() {
		return 'compliance';
	}

	/**
	 * Initialize module
	 *
	 * @since 3.0.1
	 * @since 3.0.2 Refactored to register single main page with tabs
	 * @return void
	 */
	public function init() {
		// Only proceed if module is enabled
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Register admin pages
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );
		}

		// Register REST API endpoints
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Register shortcodes
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register admin menu
	 *
	 * Registers single main page with tabbed interface for Compliance functionality.
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function register_admin_menu() {
		// Register main Compliance page (tabbed interface)
		add_submenu_page(
			'shahi-legalops-suite',
			__( 'Compliance', 'shahi-legalops-suite' ),
			'🛡️ ' . __( 'Compliance', 'shahi-legalops-suite' ),
			'manage_shahi_template',
			'slos-compliance',
			array( $this, 'render_main_page' )
		);
	}

	/**
	 * Render main Compliance page with tabs
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_main_page() {
		$main_page = new \ShahiLegalopsSuite\Admin\ComplianceMainPage();
		$main_page->render();
	}

	/**
	 * Register REST API routes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_rest_routes() {
		$controllers = array(
			new \ShahiLegalopsSuite\API\Consent_REST_Controller(),
			new \ShahiLegalopsSuite\API\Consent_Export_Controller(),
			new \ShahiLegalopsSuite\API\Consent_Log_Controller(),
			new \ShahiLegalopsSuite\API\Cookie_REST_Controller(),
			new \ShahiLegalopsSuite\API\Geo_REST_Controller(),
			new \ShahiLegalopsSuite\API\Settings_REST_Controller(),
		);

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}

	/**
	 * Register shortcodes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_shortcodes() {
		$shortcode = new \ShahiLegalopsSuite\Shortcodes\Consent_Preferences_Shortcode();
		if ( method_exists( $shortcode, 'register' ) ) {
			$shortcode->register();
		} elseif ( method_exists( $shortcode, 'init' ) ) {
			$shortcode->init();
		}
	}

	/**
	 * Get module version
	 *
	 * @since 3.0.1
	 * @return string Version
	 */
	public function get_version() {
		return '1.0.0';
	}

	/**
	 * Get module dependencies
	 *
	 * @since 3.0.1
	 * @return array Array of module keys this module depends on
	 */
	public function get_dependencies() {
		return array();
	}

	/**
	 * Get module settings URL
	 *
	 * Returns the admin URL for Consent Management settings (Banner Settings tab).
	 *
	 * @since 3.1.1
	 * @return string Settings URL
	 */
	public function get_settings_url() {
		return admin_url( 'admin.php?page=slos-compliance&tab=banner-settings' );
	}
}
