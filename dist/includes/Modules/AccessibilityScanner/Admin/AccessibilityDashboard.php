<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin;

use ShahiLegalFlowSuite\Core\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AccessibilityDashboard {

	private $security;

	public function __construct() {
		$this->security = new Security();
	}

	public function init() {
		// No longer needed - Assets.php handles all asset enqueuing globally
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		$this->render_content();
	}

	/**
	 * Render only the content (for tabbed interface)
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_content() {
		// Get scan stats
		$stats     = $this->get_scan_stats();
		$history   = $this->get_scan_history();
		$last_scan = $this->get_last_scan_results();

		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/accessibility-dashboard.php';
	}

	private function get_scan_stats() {
		// Placeholder for real stats logic
		// In a real implementation, this would query the database
		return array(
			'score'           => get_option( 'slos_accessibility_score', 0 ),
			'issues_total'    => get_option( 'slos_accessibility_issues_total', 0 ),
			'issues_critical' => get_option( 'slos_accessibility_issues_critical', 0 ),
			'issues_warning'  => get_option( 'slos_accessibility_issues_warning', 0 ),
			'pages_scanned'   => get_option( 'slos_accessibility_pages_scanned', 0 ),
		);
	}

	private function get_scan_history() {
		// Placeholder for history
		return get_option( 'slos_accessibility_scan_history', array() );
	}

	private function get_last_scan_results() {
		return get_option( 'slos_last_scan_results', array() );
	}
}

