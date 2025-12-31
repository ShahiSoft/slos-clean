<?php
/**
 * DSR Portal Module
 *
 * Data Subject Rights portal for GDPR/CCPA/LGPD compliance.
 * Manages DSR request submission, tracking, and fulfillment.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\DSR_Portal
 * @license    GPL-3.0+
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Modules\DSR_Portal;

use ShahiLegalFlowSuite\Modules\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR_Portal Module Class
 *
 * @since 3.0.1
 */
class DSR_Portal extends Module {

	/**
	 * Get module unique key
	 *
	 * @since 3.0.1
	 * @return string Module key
	 */
	public function get_key(): string {
		return 'dsr-portal';
	}

	/**
	 * Get module name
	 *
	 * @since 3.0.1
	 * @return string Module name
	 */
	public function get_name(): string {
		return 'DSR Portal';
	}

	/**
	 * Get module description
	 *
	 * @since 3.0.1
	 * @return string Module description
	 */
	public function get_description(): string {
		return 'Data Subject Rights portal for GDPR, CCPA, LGPD, and other privacy regulations. Manage access requests, erasure, portability, and more.';
	}

	/**
	 * Get module icon
	 *
	 * @since 3.0.1
	 * @return string Icon class
	 */
	public function get_icon(): string {
		return 'dashicons-format-aside';
	}

	/**
	 * Get module category
	 *
	 * @since 3.0.1
	 * @return string Category
	 */
	public function get_category(): string {
		return 'compliance';
	}

	/**
	 * Get module version
	 *
	 * @since 3.0.1
	 * @return string Version
	 */
	public function get_version(): string {
		return '1.0.0';
	}

	/**
	 * Get module dependencies
	 *
	 * @since 3.0.1
	 * @return array Array of module keys this module depends on
	 */
	public function get_dependencies(): array {
		return array();
	}

	/**
	 * Initialize module
	 *
	 * Called when module is enabled. Registers hooks, REST routes, and shortcodes.
	 *
	 * @since 3.0.1
	 * @since 3.0.2 Refactored to register single main page with tabs
	 * @return void
	 */
	public function init(): void {
		// Only proceed if module is enabled
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Register REST API endpoints
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Register shortcodes
		add_action( 'init', array( $this, 'register_shortcodes' ) );

		// Register admin pages
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 20 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
			
			// Initialize DSR_Settings early so settings are registered during admin_init
			new \ShahiLegalFlowSuite\Admin\DSR_Settings();
		}

		// Register frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// Fire initialization hook
		do_action( 'slos_dsr_portal_init' );
	}

	/**
	 * Register REST API routes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_rest_routes(): void {
		do_action( 'slos_dsr_register_rest_routes' );
	}

	/**
	 * Register shortcodes
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_shortcodes(): void {
		do_action( 'slos_dsr_register_shortcodes' );
	}

	/**
	 * Register admin menu
	 *
	 * Registers single main page with tabbed interface for DSR functionality.
	 *
	 * @since 3.0.1
	 * @since 3.0.2 Simplified to register only main page
	 * @return void
	 */
	public function register_admin_menu(): void {
		// Register main DSR page (tabbed interface)
		add_submenu_page(
			'shahi-legalflowsuite',
			__( 'Requests', 'shahi-legalflowsuite' ),
			'📋 ' . __( 'Requests', 'shahi-legalflowsuite' ),
			'slos_manage_dsr',
			'slos-requests',
			array( $this, 'render_main_page' )
		);

		// Fire hook for additional pages (legacy support)
		do_action( 'slos_dsr_register_admin_menu' );
	}

	/**
	 * Render main DSR page with tabs
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_main_page(): void {
		$main_page = new \ShahiLegalFlowSuite\Admin\DSRMainPage();
		$main_page->render();
	}

	/**
	 * Enqueue admin assets
	 *
	 * @since 3.0.1
	 * @param string $hook Admin page hook.
	 * @return void
	 */
	public function enqueue_admin_assets( string $hook ): void {
		// Bail early if hook is missing to avoid deprecated warnings
		if ( empty( $hook ) ) {
			return;
		}

		// Check if we're on a DSR page
		if ( strpos( $hook, 'slos-requests' ) === false && strpos( $hook, 'slos-dsr' ) === false && strpos( $hook, 'dsr' ) === false ) {
			return;
		}

		// Enqueue modern CSS
		wp_enqueue_style(
			'slos-dsr-modern',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/dsr-modern.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		// Enqueue modern JavaScript
		wp_enqueue_script(
			'slos-dsr-modern',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/dsr-modern.js',
			array( 'jquery' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
			true
		);

		// Localize script with useful data
		wp_localize_script(
			'slos-dsr-modern',
			'slosDSR',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'restUrl'      => rest_url( 'shahi-legalflowsuite/v1/dsr' ),
				'nonce'        => wp_create_nonce( 'slos_dsr_nonce' ),
				'dsr_form_url' => home_url( '/data-request/' ),
				'i18n'         => array(
					'confirmDelete' => __( 'Are you sure you want to delete this request?', 'shahi-legalflowsuite' ),
					'saved'         => __( 'Settings saved successfully', 'shahi-legalflowsuite' ),
					'error'         => __( 'An error occurred', 'shahi-legalflowsuite' ),
					'exporting'     => __( 'Preparing export...', 'shahi-legalflowsuite' ),
					'generating'    => __( 'Generating report...', 'shahi-legalflowsuite' ),
				),
			)
		);
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function enqueue_frontend_assets(): void {
		global $post;
		if ( ! isset( $post ) || ! has_shortcode( $post->post_content, 'slos_dsr_form' ) ) {
			return;
		}

		wp_enqueue_style(
			'slos-dsr-form',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/dsr-form.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		wp_enqueue_script(
			'slos-dsr-form',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/dsr-form.js',
			array( 'jquery', 'wp-api-fetch' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
			true
		);

		wp_localize_script(
			'slos-dsr-form',
			'slosDSRForm',
			array(
				'restUrl' => rest_url( 'shahi-legalflowsuite/v1/dsr' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Hook called on module activation
	 *
	 * @since 3.0.1
	 * @return void
	 */
	protected function on_activate(): void {
		do_action( 'slos_dsr_portal_activate' );
		error_log( 'DSR Portal module activated.' );
	}

	/**
	 * Hook called on module deactivation
	 *
	 * @since 3.0.1
	 * @return void
	 */
	protected function on_deactivate(): void {
		do_action( 'slos_dsr_portal_deactivate' );
		error_log( 'DSR Portal module deactivated.' );
	}

	/**
	 * Get module settings URL
	 *
	 * @since 3.0.1
	 * @return string Settings URL
	 */
	public function get_settings_url(): string {
		return admin_url( 'admin.php?page=slos-requests&tab=settings' );
	}
}
