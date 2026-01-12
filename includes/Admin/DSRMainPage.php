<?php
/**
 * DSR Requests Main Page (Tabbed Interface)
 *
 * Consolidated page for all DSR request management functionality.
 * Includes tabs for Requests, Reports, and Settings.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.2
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Admin\DSRRequests;
use ShahiLegalFlowSuite\Admin\DSRReports;
use ShahiLegalFlowSuite\Admin\DSR_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR Requests Main Page Class
 *
 * Provides tabbed interface for DSR request management.
 *
 * @since 3.0.2
 */
class DSRMainPage {

	/**
	 * Current active tab
	 *
	 * @var string
	 */
	private $current_tab = 'requests';

	/**
	 * DSRRequests instance
	 *
	 * @var DSRRequests
	 */
	private $requests_page;

	/**
	 * DSRReports instance
	 *
	 * @var DSRReports
	 */
	private $reports_page;

	/**
	 * DSR_Settings instance
	 *
	 * @var DSR_Settings
	 */
	private $settings_page;

	/**
	 * Constructor
	 *
	 * @since 3.0.2
	 */
	public function __construct() {
		$this->requests_page = new DSRRequests();
		$this->reports_page  = new DSRReports();
		// DSR_Settings is instantiated in DSR_Portal::init() for early hook registration..
		$this->settings_page = new DSR_Settings();
	}

	/**
	 * Get available tabs
	 *
	 * @since 3.0.2
	 * @return array Tab key => Label pairs
	 */
	private function get_tabs() {
		return array(
			'requests' => __( 'Requests', 'shahi-legalflowsuite' ),
			'reports'  => __( 'Reports', 'shahi-legalflowsuite' ),
			'settings' => __( 'Settings', 'shahi-legalflowsuite' ),
		);
	}

	/**
	 * Render the main page
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render() {
		// Check capability..
		if ( ! current_user_can( 'slos_manage_dsr' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		// Get current tab..
		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'requests';

		// Validate tab..
		$valid_tabs = array_keys( $this->get_tabs() );
		if ( ! in_array( $this->current_tab, $valid_tabs, true ) ) {
			$this->current_tab = 'requests';
		}

		?>
		<style>
		/* DSR Page Header - Glowing White Title */
		.slos-dsr-modern-page .slos-header-title {
			margin: 0;
			font-size: 28px !important;
			font-weight: 700 !important;
			color: #ffffff !important;
			text-shadow: 
				0 0 10px rgba(255, 255, 255, 0.8),
				0 0 20px rgba(255, 255, 255, 0.6),
				0 0 40px rgba(255, 255, 255, 0.4),
				0 0 60px rgba(147, 197, 253, 0.3) !important;
			letter-spacing: 0.5px;
		}
		
		/* DSR Tab Navigation - Blue/White Gradient Style */
		.slos-dsr-modern-page .slos-modern-tabs {
			display: inline-flex;
			gap: 12px;
			background: transparent;
			padding: 0;
			border-radius: 0;
		}
		
		.slos-dsr-modern-page .slos-tab-link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 12px 20px;
			border-radius: 8px;
			font-weight: 500;
			font-size: 14px;
			color: #f8fafc !important;
			background: #1e293b !important;
			text-decoration: none;
			border: 2px solid #334155 !important;
			transition: all 0.2s ease;
		}
		
		.slos-dsr-modern-page .slos-tab-link:hover {
			background: #475569 !important;
			color: #3b82f6 !important;
			border-color: #3b82f6 !important;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
		}
		
		.slos-dsr-modern-page .slos-tab-link.active {
			background: linear-gradient(135deg, #3b82f6 0%, #93c5fd 50%, #ffffff 100%) !important;
			color: #1e3a5f !important;
			font-weight: 700 !important;
			border-color: #3b82f6 !important;
			box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4), 0 0 40px rgba(147, 197, 253, 0.3) !important;
			transform: translateY(-2px);
		}
		</style>
		
		<div class="wrap slos-dsr-modern-page">
			<!-- Sticky Header Bar -->
			<div class="slos-dsr-header-bar">
				<div class="slos-header-left">
					<span class="slos-header-icon">📋</span>
					<h1 class="slos-header-title">
						<?php echo esc_html__( 'Data Subject Requests', 'shahi-legalflowsuite' ); ?>
					</h1>
				</div>
				
				<div class="slos-header-center">
					<?php $this->render_modern_tabs(); ?>
				</div>
				
				<div class="slos-header-right">
					<?php $this->render_header_actions(); ?>
					<span class="slos-header-icon-btn" title="<?php esc_attr_e( 'Notifications', 'shahi-legalflowsuite' ); ?>">
						<span class="dashicons dashicons-bell"></span>
						<span class="slos-notification-badge">3</span>
					</span>
					<span class="slos-header-icon-btn" title="<?php esc_attr_e( 'Analytics', 'shahi-legalflowsuite' ); ?>">
						<span class="dashicons dashicons-chart-bar"></span>
					</span>
				</div>
			</div>
			
			<!-- Main Content Area -->
			<div class="slos-dsr-content-wrapper">
				<?php $this->render_tab_content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render modern tab navigation
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_modern_tabs() {
		$tabs        = $this->get_tabs();
		$current_url = admin_url( 'admin.php?page=slos-requests' );

		echo '<nav class="slos-modern-tabs" aria-label="' . esc_attr__( 'Secondary menu', 'shahi-legalflowsuite' ) . '">';

		foreach ( $tabs as $tab_key => $tab_label ) {
			$active_class = ( $this->current_tab === $tab_key ) ? 'active' : '';
			$tab_url      = add_query_arg( 'tab', $tab_key, $current_url );

			printf(
				'<a href="%s" class="slos-tab-link %s">%s</a>',
				esc_url( $tab_url ),
				esc_attr( $active_class ),
				esc_html( $tab_label )
			);
		}

		echo '</nav>';
	}

	/**
	 * Render header action buttons
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_header_actions() {
		switch ( $this->current_tab ) {
			case 'requests':
				echo '<button class="slos-btn slos-btn-primary" id="slos-new-request">
					<span class="dashicons dashicons-plus-alt2"></span> ' . esc_html__( 'New Request', 'shahi-legalflowsuite' ) . '
				</button>';
				echo '<button class="slos-btn slos-btn-secondary" id="slos-export-requests">
					<span class="dashicons dashicons-download"></span> ' . esc_html__( 'Export', 'shahi-legalflowsuite' ) . '
				</button>';
				break;
			case 'reports':
				echo '<button class="slos-btn slos-btn-secondary" id="slos-generate-report">
					<span class="dashicons dashicons-chart-area"></span> ' . esc_html__( 'Generate Report', 'shahi-legalflowsuite' ) . '
				</button>';
				break;
			case 'settings':
				echo '<button class="slos-btn slos-btn-primary" id="slos-save-settings">
					<span class="dashicons dashicons-yes"></span> ' . esc_html__( 'Save Settings', 'shahi-legalflowsuite' ) . '
				</button>';
				break;
		}
	}

	/**
	 * Render current tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_tab_content() {
		switch ( $this->current_tab ) {
			case 'requests':
				$this->render_requests_tab();
				break;

			case 'reports':
				$this->render_reports_tab();
				break;

			case 'settings':
				$this->render_settings_tab();
				break;

			default:
				$this->render_requests_tab();
		}
	}

	/**
	 * Render Requests tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_requests_tab() {
		echo '<div class="slos-tab-pane slos-requests-pane">';

		// Use existing DSRRequests render logic..
		$this->requests_page->render_content();

		echo '</div>';
	}

	/**
	 * Render Reports tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_reports_tab() {
		echo '<div class="slos-tab-pane slos-reports-pane">';

		// Use existing DSRReports render logic..
		$this->reports_page->render_content();

		echo '</div>';
	}

	/**
	 * Render Settings tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_settings_tab() {
		echo '<div class="slos-tab-pane slos-settings-pane">';

		// Use existing DSR_Settings render logic..
		$this->settings_page->render_page_content();

		echo '</div>';
	}
}
