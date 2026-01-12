<?php
/**
 * Accessibility Main Page (Tabbed Interface)
 *
 * Consolidated page for all accessibility scanning functionality.
 * Includes tabs for Scanner, Dashboard, and Settings.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.2
 */

namespace ShahiLegalFlowSuite\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Main Page Class
 *
 * Provides tabbed interface for accessibility scanner management.
 *
 * @since 3.0.2
 */
class AccessibilityMainPage {

	/**
	 * Current active tab
	 *
	 * @var string
	 */
	private $current_tab = 'scanner';

	/**
	 * Scanner page instance
	 *
	 * @var \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\ScannerPage
	 */
	private $scanner_page;

	/**
	 * Dashboard page instance
	 *
	 * @var \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\AccessibilityDashboard
	 */
	private $dashboard_page;

	/**
	 * Settings page instance
	 *
	 * @var \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\AccessibilitySettings
	 */
	private $settings_page;

	/**
	 * Constructor
	 *
	 * @since 3.0.2
	 */
	public function __construct() {
		// Initialize page instances..
		$this->scanner_page   = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\ScannerPage();
		$this->dashboard_page = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\AccessibilityDashboard();
		$this->settings_page  = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\AccessibilitySettings();
	}

	/**
	 * Get available tabs
	 *
	 * @since 3.0.2
	 * @return array Tab key => Label pairs
	 */
	private function get_tabs() {
		return array(
			'tools'     => __( 'Tools & Scanner', 'shahi-legalflowsuite' ),
			'dashboard' => __( 'Dashboard & Reports', 'shahi-legalflowsuite' ),
			'settings'  => __( 'Settings', 'shahi-legalflowsuite' ),
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
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		// Get current tab..
		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'tools';

		// Validate tab..
		$valid_tabs = array_keys( $this->get_tabs() );
		if ( ! in_array( $this->current_tab, $valid_tabs, true ) ) {
			$this->current_tab = 'tools';
		}

		?>
		<style>
		/* Accessibility Center Custom Styles */
		.slos-accessibility-main-page {
			background: #0f172a;
			margin: -20px -20px 0 -20px;
			padding: 24px;
			min-height: calc(100vh - 32px);
		}
		
		.slos-accessibility-main-page h1.slos-page-title {
			color: #ffffff;
			font-size: 28px;
			font-weight: 700;
			margin: 0 0 24px 0;
			text-shadow: 
				0 0 10px rgba(255, 255, 255, 0.8),
				0 0 20px rgba(255, 255, 255, 0.6),
				0 0 40px rgba(255, 255, 255, 0.4),
				0 0 60px rgba(147, 197, 253, 0.3);
			letter-spacing: 0.5px;
		}
		
		.slos-accessibility-main-page .slos-tab-nav {
			display: flex;
			gap: 12px;
			margin-bottom: 24px;
			padding: 0;
			border: none;
			background: transparent;
			flex-wrap: wrap;
		}
		
		.slos-accessibility-main-page .slos-tab-link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 12px 20px;
			color: #f8fafc;
			background: #1e293b;
			text-decoration: none;
			font-size: 14px;
			font-weight: 500;
			border: 2px solid #334155;
			border-radius: 8px;
			transition: all 0.2s ease;
			white-space: nowrap;
		}
		
		.slos-accessibility-main-page .slos-tab-link:hover {
			background: #475569;
			color: #3b82f6;
			border-color: #3b82f6;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
		}
		
		.slos-accessibility-main-page .slos-tab-link.active {
			background: linear-gradient(135deg, #3b82f6 0%, #93c5fd 50%, #ffffff 100%) !important;
			color: #1e3a5f !important;
			font-weight: 700;
			border-color: #3b82f6;
			box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4), 0 0 40px rgba(147, 197, 253, 0.3);
			transform: translateY(-2px);
		}
		
		.slos-accessibility-main-page .slos-tab-link .dashicons {
			font-size: 18px;
			width: 18px;
			height: 18px;
		}
		
		.slos-accessibility-main-page .slos-tab-content {
			background: transparent;
		}
		
		.slos-accessibility-main-page hr.wp-header-end {
			display: none;
		}
		</style>
		
		<div class="wrap slos-accessibility-main-page">
			<h1 class="slos-page-title">
				<?php echo esc_html__( 'Accessibility Center', 'shahi-legalflowsuite' ); ?>
			</h1>
			
			<?php $this->render_tabs(); ?>
			
			<hr class="wp-header-end">
			
			<div class="tab-content slos-tab-content">
				<?php $this->render_tab_content(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render tab navigation
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_tabs() {
		$tabs        = $this->get_tabs();
		$current_url = admin_url( 'admin.php?page=slos-accessibility' );

		// Tab icons..
		$tab_icons = array(
			'tools'     => 'dashicons-admin-tools',
			'dashboard' => 'dashicons-chart-area',
			'settings'  => 'dashicons-admin-generic',
		);

		echo '<nav class="slos-tab-nav" aria-label="' . esc_attr__( 'Accessibility sections', 'shahi-legalflowsuite' ) . '">';

		foreach ( $tabs as $tab_key => $tab_label ) {
			$active_class = ( $this->current_tab === $tab_key ) ? 'active' : '';
			$tab_url      = add_query_arg( 'tab', $tab_key, $current_url );
			$icon         = isset( $tab_icons[ $tab_key ] ) ? $tab_icons[ $tab_key ] : 'dashicons-admin-generic';

			printf(
				'<a href="%s" class="slos-tab-link %s"><span class="dashicons %s"></span>%s</a>',
				esc_url( $tab_url ),
				esc_attr( $active_class ),
				esc_attr( $icon ),
				esc_html( $tab_label )
			);
		}

		echo '</nav>';
	}

	/**
	 * Render current tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_tab_content() {
		switch ( $this->current_tab ) {
			case 'tools':
				$this->render_tools_tab();
				break;

			case 'dashboard':
				$this->render_dashboard_tab();
				break;

			case 'settings':
				$this->render_settings_tab();
				break;

			default:
				$this->render_tools_tab();
		}
	}

	/**
	 * Render Tools tab content (formerly Scanner)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_tools_tab() {
		echo '<div class="slos-tab-pane slos-tools-pane">';

		// Render tools page content..
		if ( $this->scanner_page && method_exists( $this->scanner_page, 'render_content' ) ) {
			$this->scanner_page->render_content();
		} else {
			// Fallback - display placeholder content..
			?>
			<div style="background: #1e293b; border-radius: 8px; padding: 40px; text-align: center; color: #cbd5e1;">
				<h2 style="color: #f1f5f9; margin-bottom: 16px;">🛠️ Tools & Scanner</h2>
				<p style="font-size: 16px; margin-bottom: 24px;">The Scanner tools interface will be available here.</p>
				<p style="font-size: 14px; color: #94a3b8;">This section is currently being developed. Please check the Dashboard & Reports tab for accessibility insights.</p>
			</div>
			<?php
		}

		echo '</div>';
	}

	/**
	 * Render Dashboard tab content
	 *
	 * @since 3.0.2
	 * @return void
	 */
	private function render_dashboard_tab() {
		echo '<div class="slos-tab-pane slos-dashboard-pane">';

		// Render dashboard page content..
		if ( method_exists( $this->dashboard_page, 'render_content' ) ) {
			$this->dashboard_page->render_content();
		} else {
			echo '<p>' . esc_html__( 'Dashboard and reports will be displayed here.', 'shahi-legalflowsuite' ) . '</p>';
		}

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

		// Render settings page content..
		if ( method_exists( $this->settings_page, 'render_content' ) ) {
			$this->settings_page->render_content();
		} else {
			// Link to settings page..
			?>
			<div class="slos-settings-wrapper">
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-accessibility-settings' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Configure Accessibility Settings', 'shahi-legalflowsuite' ); ?>
					</a>
				</p>
				<p class="description">
					<?php esc_html_e( 'Configure accessibility scanner rules, automatic fixes, and compliance requirements.', 'shahi-legalflowsuite' ); ?>
				</p>
			</div>
			<?php
		}

		echo '</div>';
	}
}
