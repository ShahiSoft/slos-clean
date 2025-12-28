<?php
/**
 * Consent Logs Admin Page
 *
 * Handles the consent audit logs admin page.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Admin
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Services\Consent_Audit_Logger;

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
			'shahi-legalflowsuite',
			__( 'Consent Logs', 'shahi-legalflowsuite' ),
			__( 'Consent Logs', 'shahi-legalflowsuite' ),
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
		if ( 'shahi-legalflowsuite_page_' . $this->page_slug !== $hook ) {
			return;
		}

		// Enqueue JavaScript
		wp_enqueue_script(
			'slos-admin-consent-logs',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/admin-consent-logs.js',
			array( 'jquery', 'wp-api-request' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
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
					'no_logs_found'  => __( 'No logs found matching the current filters.', 'shahi-legalflowsuite' ),
					'view_details'   => __( 'View Details', 'shahi-legalflowsuite' ),
					'grant'          => __( 'Grant', 'shahi-legalflowsuite' ),
					'withdraw'       => __( 'Withdraw', 'shahi-legalflowsuite' ),
					'update'         => __( 'Update', 'shahi-legalflowsuite' ),
					'import'         => __( 'Import', 'shahi-legalflowsuite' ),
					'export'         => __( 'Export', 'shahi-legalflowsuite' ),
					'previous'       => __( 'Previous', 'shahi-legalflowsuite' ),
					'next'           => __( 'Next', 'shahi-legalflowsuite' ),
					'items'          => __( 'items', 'shahi-legalflowsuite' ),
					'log_id'         => __( 'Log ID', 'shahi-legalflowsuite' ),
					'consent_id'     => __( 'Consent ID', 'shahi-legalflowsuite' ),
					'user_id'        => __( 'User ID', 'shahi-legalflowsuite' ),
					'purpose'        => __( 'Purpose', 'shahi-legalflowsuite' ),
					'action'         => __( 'Action', 'shahi-legalflowsuite' ),
					'method'         => __( 'Method', 'shahi-legalflowsuite' ),
					'ip_address'     => __( 'IP Address', 'shahi-legalflowsuite' ),
					'user_agent'     => __( 'User Agent', 'shahi-legalflowsuite' ),
					'created_at'     => __( 'Created At', 'shahi-legalflowsuite' ),
					'previous_state' => __( 'Previous State', 'shahi-legalflowsuite' ),
					'new_state'      => __( 'New State', 'shahi-legalflowsuite' ),
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
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Consent Logs', 'shahi-legalflowsuite' ) . '</h1>';
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
		$template_path = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'templates/admin/consent-logs.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		} else {
			echo '<p>' . esc_html__( 'Template not found.', 'shahi-legalflowsuite' ) . '</p>';
		}
	}
}
