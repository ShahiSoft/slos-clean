<?php
/**
 * Consent Logs Admin Page
 *
 * Handles the consent audit logs admin page.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Admin
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Services\Consent_Audit_Logger;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent_Logs_Page Class
 *
 * Manages the consent logs admin interface.
 *
 * @since 3.0.1
 */
class Consent_Logs_Page {

	/**
	 * Audit logger service
	 *
	 * @since 3.0.1
	 * @var Consent_Audit_Logger
	 */
	private $audit_logger;

	/**
	 * Page slug
	 *
	 * @since 3.0.1
	 * @var string
	 */
	private $page_slug = 'slos-consent-logs';

	/**
	 * Constructor
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		$this->audit_logger = new Consent_Audit_Logger();

		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add menu page
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function add_menu_page() {
		add_submenu_page(
			'shahi-legalops-suite',
			__( 'Consent Logs', 'shahi-legalops-suite' ),
			__( 'Consent Logs', 'shahi-legalops-suite' ),
			'manage_shahi_template',
			$this->page_slug,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue assets
	 *
	 * @since 3.0.1
	 * @param string $hook Current page hook
	 * @return void
	 */
	public function enqueue_assets( string $hook ) {
		// Only load on our page
		if ( 'shahi-legalops-suite_page_' . $this->page_slug !== $hook ) {
			return;
		}

		// Enqueue JavaScript
		wp_enqueue_script(
			'slos-admin-consent-logs',
			SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/js/admin-consent-logs.js',
			array( 'jquery', 'wp-api-request' ),
			SHAHI_LEGALOPS_SUITE_VERSION,
			true
		);

		// Localize script
		wp_localize_script(
			'slos-admin-consent-logs',
			'slosLogsData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'restUrl' => rest_url( 'slos/v1' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'i18n'    => array(
					'no_logs_found'  => __( 'No logs found matching the current filters.', 'shahi-legalops-suite' ),
					'view_details'   => __( 'View Details', 'shahi-legalops-suite' ),
					'grant'          => __( 'Grant', 'shahi-legalops-suite' ),
					'withdraw'       => __( 'Withdraw', 'shahi-legalops-suite' ),
					'update'         => __( 'Update', 'shahi-legalops-suite' ),
					'import'         => __( 'Import', 'shahi-legalops-suite' ),
					'export'         => __( 'Export', 'shahi-legalops-suite' ),
					'previous'       => __( 'Previous', 'shahi-legalops-suite' ),
					'next'           => __( 'Next', 'shahi-legalops-suite' ),
					'items'          => __( 'items', 'shahi-legalops-suite' ),
					'log_id'         => __( 'Log ID', 'shahi-legalops-suite' ),
					'consent_id'     => __( 'Consent ID', 'shahi-legalops-suite' ),
					'user_id'        => __( 'User ID', 'shahi-legalops-suite' ),
					'purpose'        => __( 'Purpose', 'shahi-legalops-suite' ),
					'action'         => __( 'Action', 'shahi-legalops-suite' ),
					'method'         => __( 'Method', 'shahi-legalops-suite' ),
					'ip_address'     => __( 'IP Address', 'shahi-legalops-suite' ),
					'user_agent'     => __( 'User Agent', 'shahi-legalops-suite' ),
					'created_at'     => __( 'Created At', 'shahi-legalops-suite' ),
					'previous_state' => __( 'Previous State', 'shahi-legalops-suite' ),
					'new_state'      => __( 'New State', 'shahi-legalops-suite' ),
				),
			)
		);

		// Enqueue admin CSS (if exists)
		wp_enqueue_style( 'slos-admin-global' );
	}

	/**
	 * Render page
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function render_page() {
		// Check permissions
		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalops-suite' ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Consent Logs', 'shahi-legalops-suite' ) . '</h1>';
		$this->render_content();
		echo '</div>';
	}

	/**
	 * Render just the content (for use in tabbed interface)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_content() {
		// Load template
		$template_path = SHAHI_LEGALOPS_SUITE_PLUGIN_DIR . 'templates/admin/consent-logs.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			echo '<p>' . esc_html__( 'Template not found.', 'shahi-legalops-suite' ) . '</p>';
		}
	}
}
