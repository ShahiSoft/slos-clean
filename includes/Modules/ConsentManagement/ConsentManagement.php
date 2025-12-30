<?php
/**
 * Consent Management Module
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\ConsentManagement
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Modules\ConsentManagement;

use ShahiLegalFlowSuite\Modules\Module;

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

		// Register Gutenberg blocks
		add_action( 'init', array( $this, 'register_blocks' ) );

		// Enqueue block editor assets
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
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
			'shahi-legalflowsuite',
			__( 'Compliance', 'shahi-legalflowsuite' ),
			'🛡️ ' . __( 'Compliance', 'shahi-legalflowsuite' ),
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
		$main_page = new \ShahiLegalFlowSuite\Admin\ComplianceMainPage();
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
			new \ShahiLegalFlowSuite\API\Consent_REST_Controller(),
			new \ShahiLegalFlowSuite\API\Consent_Export_Controller(),
			new \ShahiLegalFlowSuite\API\Consent_Log_Controller(),
			new \ShahiLegalFlowSuite\API\Cookie_REST_Controller(),
			new \ShahiLegalFlowSuite\API\Geo_REST_Controller(),
			new \ShahiLegalFlowSuite\API\Settings_REST_Controller(),
			new \ShahiLegalFlowSuite\API\Config_REST_Controller(),
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
		$shortcode = new \ShahiLegalFlowSuite\Shortcodes\Consent_Preferences_Shortcode();
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

	/**
	 * Register Gutenberg blocks
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function register_blocks() {
		// Register embed placeholder block
		if ( function_exists( 'register_block_type' ) ) {
			register_block_type(
				'slos/embed-placeholder',
				array(
					'editor_script'   => 'slos-embed-placeholder-block',
					'render_callback' => array( $this, 'render_embed_placeholder_block' ),
				)
			);
		}
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'slos-embed-placeholder-block',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/blocks/embed-placeholder-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
			false
		);
	}

	/**
	 * Render embed placeholder block
	 *
	 * @since 3.1.1
	 * @param array $attributes Block attributes
	 * @return string Rendered block output
	 */
	public function render_embed_placeholder_block( $attributes ) {
		// Use shortcode to render
		$shortcode = new \ShahiLegalFlowSuite\Shortcodes\Embed_Placeholder_Shortcode();
		return $shortcode->render( $attributes );
	}
}

