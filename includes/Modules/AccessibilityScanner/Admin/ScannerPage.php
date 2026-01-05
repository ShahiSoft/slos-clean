<?php
namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Tools Page
 * 
 * Provides hands-on tools for checking and improving accessibility.
 * Redesigned with V3 modern dark theme.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Admin
 * @since      3.0.2
 */
class ScannerPage {

	/**
	 * Page Hook Suffix
	 *
	 * @var string
	 */
	private $page_hook;

	/**
	 * Initialize the page
	 *
	 * @deprecated 3.0.2 Menu registration now handled by AccessibilityScanner module
	 */
	public function init() {
		// Menu registration removed - handled by module's register_admin_menus()
		// Kept for backward compatibility if directly instantiated
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'slos_scanner_fixes', 'slos_active_fixes' );
	}

	/**
	 * Register the admin menu page
	 */
	public function register_page() {
		$this->page_hook = add_submenu_page(
			'shahi-legalflowsuite',
			'Accessibility Scanner',
			'Accessibility Scanner',
			'manage_options',
			'slos-accessibility-scanner',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_assets( $hook ) {
		// If page hook is not set yet, try to guess it or return
		if ( ! $this->page_hook && $hook !== 'shahi-legalflowsuite_page_slos-accessibility-scanner' ) {
			return;
		}

		// If page hook is set, check against it
		if ( $this->page_hook && $hook !== $this->page_hook ) {
			return;
		}

		wp_enqueue_script(
			'slos-scanner-admin',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/slos-scanner-admin.js',
			array( 'jquery' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
			true
		);

		wp_localize_script(
			'slos-scanner-admin',
			'slosScanner',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'slos_scanner_nonce' ),
			)
		);

		wp_enqueue_style(
			'slos-scanner-admin',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/slos-scanner-admin.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		// Auto-Fix Progress Popup Assets
		wp_enqueue_style(
			'slos-autofix-progress',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/slos-autofix-progress.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		wp_enqueue_script(
			'slos-autofix-progress',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/slos-autofix-progress.js',
			array( 'jquery' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
			true
		);

		// Scan Progress Modal Assets
		wp_enqueue_style(
			'slos-scan-progress',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/slos-scan-progress.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		wp_enqueue_script(
			'slos-scan-progress',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/js/slos-scan-progress.js',
			array( 'jquery' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
			true
		);

		// Localize Scan Progress with configuration
		wp_localize_script(
			'slos-scan-progress',
			'slosScanConfig',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'slos_scanner_nonce' ),
				'i18n'    => array(
					'initializing' => __( 'Initializing...', 'shahi-legalflowsuite' ),
					'fetching'     => __( 'Fetching pages to scan...', 'shahi-legalflowsuite' ),
					'scanning'     => __( 'Scanning', 'shahi-legalflowsuite' ),
					'complete'     => __( 'Scan complete!', 'shahi-legalflowsuite' ),
					'cancelled'    => __( 'Scan cancelled', 'shahi-legalflowsuite' ),
					'error'        => __( 'Error', 'shahi-legalflowsuite' ),
					'noPages'      => __( 'No pages found to scan', 'shahi-legalflowsuite' ),
					'confirmCancel' => __( 'Are you sure you want to cancel the scan?', 'shahi-legalflowsuite' ),
				),
			)
		);

		// Localize Auto-Fix Progress with fixer data
		wp_localize_script(
			'slos-autofix-progress',
			'slosautoFixConfig',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'slos_autofix_nonce' ),
				'fixers'  => $this->get_fixer_list_for_js(),
				'i18n'    => array(
					'processing'  => __( 'Processing...', 'shahi-legalflowsuite' ),
					'complete'    => __( 'Complete!', 'shahi-legalflowsuite' ),
					'cancelled'   => __( 'Cancelled', 'shahi-legalflowsuite' ),
					'error'       => __( 'Error', 'shahi-legalflowsuite' ),
					'noIssues'    => __( 'No issues found', 'shahi-legalflowsuite' ),
					'fixedIssues' => __( 'Fixed %d issue(s)', 'shahi-legalflowsuite' ),
				),
			)
		);
	}

	/**
	 * Get list of fixers for JavaScript
	 *
	 * @since 3.2.0
	 * @return array
	 */
	private function get_fixer_list_for_js() {
		// TEMPORARY: FixEngine disabled due to critical method signature mismatch
		// All 31 fixers have incompatible signatures causing PHP Fatal Errors
		// Using stable FixerRegistry until FixEngine is refactored
		
		// Use FixerRegistry
		if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry' ) ) {
			\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();
			$fixer_ids = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_all_fixer_ids();

			$fixers = array();
			foreach ( $fixer_ids as $id ) {
				$fixer = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_fixer( $id );
				if ( $fixer ) {
					// Get ID and derive name from it (BaseFixer doesn't have get_name)
					$fixer_id = $fixer->get_id();
					$fixer_name = ucwords( str_replace( array( '-', '_' ), ' ', $fixer_id ) );
					
					// Get description safely
					$description = '';
					if ( method_exists( $fixer, 'get_description' ) ) {
						$description = $fixer->get_description();
					}
					
					$fixers[] = array(
						'id'          => $fixer_id,
						'name'        => $fixer_name,
						'description' => $description,
					);
				}
			}
			return $fixers;
		}

		// Fallback: return empty array if registry not available
		return array();
	}

	/**
	 * Render the page content
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1>Accessibility Tools</h1>
			<?php $this->render_content(); ?>
		</div>
		<?php
	}

	/**
	 * Render only the content (for tabbed interface)
	 * 
	 * V3 Modern Design - Tools & Scanner Tab
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render_content() {
		// Get current stats
		$stats = get_option( 'slos_scan_statistics', array() );
		$score = isset( $stats['average_score'] ) ? intval( $stats['average_score'] ) : 0;
		$total_issues = isset( $stats['total_issues'] ) ? intval( $stats['total_issues'] ) : 0;
		$critical_issues = isset( $stats['total_critical'] ) ? intval( $stats['total_critical'] ) : 0;
		$last_scan = get_option( 'slos_last_scan_time', '' );
		$wcag_level = get_option( 'slos_wcag_level', 'AA' );
		$scan_post_types = get_option( 'slos_scan_post_types', array( 'post', 'page' ) );
		if ( ! is_array( $scan_post_types ) ) {
			$scan_post_types = array( 'post', 'page' );
		}
		$active_checkers = get_option( 'slos_active_checkers', array() );
		$report_settings = get_option( 'slos_accessibility_report_settings', array() );
		$report_email = isset( $report_settings['email'] ) ? $report_settings['email'] : '';
		$report_frequency = isset( $report_settings['frequency'] ) ? $report_settings['frequency'] : '';
		$report_last_sent = isset( $report_settings['last_sent'] ) ? $report_settings['last_sent'] : '';
		$next_report_ts = wp_next_scheduled( 'slos_send_accessibility_report' );
		
		// Get scan results for Pages Requiring Attention section
		$scan_results = get_option('slos_last_scan_results', []);
		if (empty($scan_results)) {
			$scan_results = [];
		}
		
		// Get widget settings
		$widget_enabled = get_option('slos_widget_enabled', true);
		$widget_position = get_option('slos_widget_position', 'bottom-right');
		$widget_color = get_option('slos_widget_color', 'blue');
		
		// Get grade from score
		$grade = $this->get_grade_from_score( $score );
		?>
		<style>
			/* V3 Tools Page Styles - Mac Slate Liquid Theme */
			.slos-tools-container {
				--slos-bg-primary: #0f172a;
				--slos-bg-card: #1e293b;
				--slos-bg-input: #0f172a;
				--slos-border: #334155;
				--slos-border-light: #475569;
				--slos-text-primary: #f8fafc;
				--slos-text-secondary: #94a3b8;
				--slos-text-muted: #64748b;
				--slos-accent: #3b82f6;
				--slos-accent-hover: #2563eb;
				--slos-success: #22c55e;
				--slos-warning: #f59e0b;
				--slos-error: #ef4444;
				--slos-info: #06b6d4;
				
				background: var(--slos-bg-primary);
				padding: 24px;
				margin: -20px -20px 0 -20px;
				min-height: calc(100vh - 100px);
			}
			
			.slos-tools-grid {
				display: grid;
				grid-template-columns: repeat(2, 1fr);
				gap: 24px;
				max-width: 1400px;
			}
			
			.slos-tools-card {
				background: var(--slos-bg-card);
				border: 1px solid var(--slos-border);
				border-radius: 12px;
				overflow: hidden;
			}
			
			.slos-tools-card.full-width {
				grid-column: 1 / -1;
			}

			/* Debug panel styles */
			.slos-debug-panel {
				font-size: 12px;
				color: var(--slos-text-secondary);
			}
			.slos-debug-panel h4 {
				margin-top: 0;
				margin-bottom: 8px;
				font-size: 13px;
				color: var(--slos-text-primary);
			}
			.slos-debug-grid {
				display: grid;
				grid-template-columns: repeat(3, minmax(0, 1fr));
				gap: 16px;
			}
			.slos-debug-section {
				background: rgba(15,23,42,0.6);
				border: 1px dashed var(--slos-border);
				border-radius: 8px;
				padding: 12px 14px;
			}
			.slos-debug-list {
				margin: 0;
				padding-left: 16px;
				list-style: disc;
			}
			.slos-debug-list li {
				margin: 0 0 4px 0;
			}
			.slos-debug-label {
				color: var(--slos-text-muted);
			}
			.slos-debug-value {
				color: var(--slos-text-primary);
				font-weight: 500;
			}
			.slos-debug-badge {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				padding: 2px 8px;
				border-radius: 999px;
				font-size: 11px;
				background: rgba(15,23,42,0.9);
				border: 1px solid var(--slos-border);
			}
			.slos-debug-badge.ok {
				border-color: var(--slos-success);
				color: var(--slos-success);
			}
			.slos-debug-badge.warn {
				border-color: var(--slos-warning);
				color: var(--slos-warning);
			}
			
			.slos-card-header {
				padding: 20px 24px;
				border-bottom: 1px solid var(--slos-border);
				display: flex;
				align-items: center;
				justify-content: space-between;
			}
			
			.slos-card-header h3 {
				color: var(--slos-text-primary);
				font-size: 16px;
				font-weight: 600;
				margin: 0;
				display: flex;
				align-items: center;
				gap: 10px;
			}
			
			.slos-card-header h3 .dashicons {
				color: var(--slos-accent);
				font-size: 20px;
				width: 20px;
				height: 20px;
			}
			
			.slos-card-header .badge {
				background: var(--slos-accent);
				color: white;
				padding: 2px 8px;
				border-radius: 10px;
				font-size: 11px;
				font-weight: 500;
			}
			
			.slos-card-body {
				padding: 24px;
			}
			
			/* Score Circle */
			.slos-score-display {
				display: flex;
				align-items: center;
				gap: 32px;
			}
			
			.slos-score-circle {
				position: relative;
				width: 140px;
				height: 140px;
			}
			
			.slos-score-circle svg {
				transform: rotate(-90deg);
				width: 140px;
				height: 140px;
			}
			
			.slos-score-circle .bg-circle {
				fill: none;
				stroke: var(--slos-border);
				stroke-width: 12;
			}
			
			.slos-score-circle .score-circle {
				fill: none;
				stroke: var(--slos-accent);
				stroke-width: 12;
				stroke-linecap: round;
				transition: stroke-dashoffset 1s ease-out;
			}
			
			.slos-score-circle.grade-a .score-circle { stroke: var(--slos-success); }
			.slos-score-circle.grade-b .score-circle { stroke: #84cc16; }
			.slos-score-circle.grade-c .score-circle { stroke: var(--slos-warning); }
			.slos-score-circle.grade-d .score-circle { stroke: #f97316; }
			.slos-score-circle.grade-f .score-circle { stroke: var(--slos-error); }
			
			.slos-score-value {
				position: absolute;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				text-align: center;
			}
			
			.slos-score-value .number {
				font-size: 36px;
				font-weight: 700;
				color: var(--slos-text-primary);
				line-height: 1;
			}
			
			.slos-score-value .label {
				font-size: 12px;
				color: var(--slos-text-muted);
				text-transform: uppercase;
			}
			
			.slos-score-details {
				flex: 1;
			}
			
			.slos-score-grade {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				margin-bottom: 16px;
			}
			
			.slos-grade-badge {
				width: 48px;
				height: 48px;
				border-radius: 12px;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 24px;
				font-weight: 700;
				color: white;
			}
			
			.slos-grade-badge.grade-a { background: var(--slos-success); }
			.slos-grade-badge.grade-b { background: #84cc16; }
			.slos-grade-badge.grade-c { background: var(--slos-warning); }
			.slos-grade-badge.grade-d { background: #f97316; }
			.slos-grade-badge.grade-f { background: var(--slos-error); }
			
			.slos-grade-info .grade-label {
				font-size: 14px;
				font-weight: 600;
				color: var(--slos-text-primary);
			}
			
			.slos-grade-info .grade-desc {
				font-size: 12px;
				color: var(--slos-text-muted);
			}
			
			.slos-compliance-badges {
				display: flex;
				gap: 8px;
				flex-wrap: wrap;
				margin-top: 16px;
			}
			
			.slos-compliance-badge {
				display: inline-flex;
				align-items: center;
				gap: 6px;
				padding: 6px 12px;
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 6px;
				font-size: 12px;
				color: var(--slos-text-secondary);
			}
			
			.slos-compliance-badge.active {
				border-color: var(--slos-success);
				color: var(--slos-success);
			}
			
			.slos-compliance-badge .dashicons {
				font-size: 14px;
				width: 14px;
				height: 14px;
			}
			
			/* Quick Stats */
			.slos-quick-stats {
				display: grid;
				grid-template-columns: repeat(3, 1fr);
				gap: 16px;
				margin-top: 20px;
				padding-top: 20px;
				border-top: 1px solid var(--slos-border);
			}
			
			.slos-quick-stat {
				text-align: center;
			}
			
			.slos-quick-stat .value {
				font-size: 24px;
				font-weight: 700;
				color: var(--slos-text-primary);
			}
			
			.slos-quick-stat .value.critical { color: var(--slos-error); }
			.slos-quick-stat .value.warning { color: var(--slos-warning); }
			
			.slos-quick-stat .label {
				font-size: 12px;
				color: var(--slos-text-muted);
				margin-top: 4px;
			}
			
			/* Tool Cards */
			.slos-tool-item {
				display: flex;
				align-items: flex-start;
				gap: 16px;
				padding: 16px;
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 8px;
				margin-bottom: 12px;
				transition: all 0.2s ease;
			}
			
			.slos-tool-item:last-child {
				margin-bottom: 0;
			}
			
			.slos-tool-item:hover {
				border-color: var(--slos-accent);
				background: rgba(59, 130, 246, 0.05);
			}
			
			.slos-tool-icon {
				width: 44px;
				height: 44px;
				border-radius: 10px;
				display: flex;
				align-items: center;
				justify-content: center;
				flex-shrink: 0;
			}
			
			.slos-tool-icon.contrast { background: linear-gradient(135deg, #1a1a1a 50%, #ffffff 50%); }
			.slos-tool-icon.alt-text { background: var(--slos-info); }
			.slos-tool-icon.readability { background: var(--slos-success); }
			.slos-tool-icon.links { background: var(--slos-warning); }
			.slos-tool-icon.headings { background: var(--slos-accent); }
			
			.slos-tool-icon .dashicons {
				color: white;
				font-size: 22px;
				width: 22px;
				height: 22px;
			}
			
			.slos-tool-content {
				flex: 1;
			}
			
			.slos-tool-content h4 {
				margin: 0 0 4px 0;
				font-size: 14px;
				font-weight: 600;
				color: var(--slos-text-primary);
			}
			
			.slos-tool-content p {
				margin: 0 0 12px 0;
				font-size: 13px;
				color: var(--slos-text-muted);
			}
			
			.slos-tool-inputs {
				display: flex;
				gap: 8px;
				flex-wrap: wrap;
			}
			
			.slos-tool-input {
				flex: 1;
				min-width: 100px;
				padding: 8px 12px;
				background: var(--slos-bg-card);
				border: 1px solid var(--slos-border);
				border-radius: 6px;
				color: var(--slos-text-primary);
				font-size: 13px;
			}
			
			.slos-tool-input:focus {
				outline: none;
				border-color: var(--slos-accent);
			}
			
			.slos-tool-input::placeholder {
				color: var(--slos-text-muted);
			}
			
			.slos-tool-btn {
				padding: 8px 16px;
				background: var(--slos-accent);
				color: white;
				border: none;
				border-radius: 6px;
				font-size: 13px;
				font-weight: 500;
				cursor: pointer;
				transition: background 0.2s;
				white-space: nowrap;
			}
			
			.slos-tool-btn:hover {
				background: var(--slos-accent-hover);
			}
			
			.slos-tool-result {
				margin-top: 12px;
				padding: 12px;
				background: var(--slos-bg-card);
				border-radius: 6px;
				font-size: 13px;
				display: none;
			}
			
			.slos-tool-result.show {
				display: block;
			}
			
			.slos-tool-result.pass {
				border-left: 3px solid var(--slos-success);
				color: var(--slos-success);
			}
			
			.slos-tool-result.fail {
				border-left: 3px solid var(--slos-error);
				color: var(--slos-error);
			}
			
			/* Scanner Card */
			.slos-scanner-section {
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 8px;
				padding: 20px;
				margin-bottom: 16px;
			}
			
			.slos-scanner-section:last-child {
				margin-bottom: 0;
			}
			
			.slos-scanner-section h4 {
				margin: 0 0 8px 0;
				font-size: 14px;
				font-weight: 600;
				color: var(--slos-text-primary);
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.slos-scanner-section p {
				margin: 0 0 16px 0;
				font-size: 13px;
				color: var(--slos-text-muted);
			}
			
			.slos-scanner-controls {
				display: flex;
				gap: 12px;
				align-items: center;
			}
			
			.slos-btn-primary {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				padding: 12px 24px;
				background: linear-gradient(135deg, var(--slos-accent), var(--slos-accent-hover));
				color: white;
				border: none;
				border-radius: 8px;
				font-size: 14px;
				font-weight: 600;
				cursor: pointer;
				transition: all 0.2s;
			}
			
			.slos-btn-primary:hover {
				transform: translateY(-1px);
				box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
			}
			
			.slos-btn-secondary {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				padding: 12px 24px;
				background: transparent;
				color: var(--slos-text-secondary);
				border: 1px solid var(--slos-border);
				border-radius: 8px;
				font-size: 14px;
				font-weight: 500;
				cursor: pointer;
				transition: all 0.2s;
			}
			
			.slos-btn-secondary:hover {
				border-color: var(--slos-accent);
				color: var(--slos-accent);
			}
			
			.slos-progress-wrapper {
				margin-top: 16px;
				display: none;
			}
			
			.slos-progress-wrapper.show {
				display: block;
			}
			
			.slos-progress-bar {
				height: 8px;
				background: var(--slos-border);
				border-radius: 4px;
				overflow: hidden;
			}
			
			.slos-progress-fill {
				height: 100%;
				background: linear-gradient(90deg, var(--slos-accent), var(--slos-info));
				border-radius: 4px;
				transition: width 0.3s ease;
				width: 0%;
			}
			
			.slos-progress-text {
				display: flex;
				justify-content: space-between;
				margin-top: 8px;
				font-size: 12px;
				color: var(--slos-text-muted);
			}
			
			/* Statement Generator */
			.slos-statement-form {
				display: grid;
				gap: 16px;
			}
			
			.slos-form-group {
				display: flex;
				flex-direction: column;
				gap: 6px;
			}
			
			.slos-form-group label {
				font-size: 13px;
				font-weight: 500;
				color: var(--slos-text-secondary);
			}
			
			.slos-form-group input,
			.slos-form-group textarea,
			.slos-form-group select {
				padding: 10px 14px;
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 6px;
				color: var(--slos-text-primary);
				font-size: 14px;
			}
			
			.slos-form-group input:focus,
			.slos-form-group textarea:focus,
			.slos-form-group select:focus {
				outline: none;
				border-color: var(--slos-accent);
			}
			
			.slos-form-group textarea {
				min-height: 100px;
				resize: vertical;
			}
			
			.slos-form-row {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 16px;
			}
			
			.slos-shortcode-box {
				display: flex;
				align-items: center;
				gap: 8px;
				padding: 12px;
				background: var(--slos-bg-input);
				border: 1px dashed var(--slos-border);
				border-radius: 6px;
				margin-top: 16px;
			}
			
			.slos-shortcode-box code {
				flex: 1;
				padding: 8px 12px;
				background: var(--slos-bg-card);
				border-radius: 4px;
				font-family: 'Monaco', 'Consolas', monospace;
				font-size: 13px;
				color: var(--slos-info);
			}
			
			.slos-copy-btn {
				padding: 8px 12px;
				background: var(--slos-bg-card);
				border: 1px solid var(--slos-border);
				border-radius: 4px;
				color: var(--slos-text-secondary);
				cursor: pointer;
				transition: all 0.2s;
			}
			
			.slos-copy-btn:hover {
				border-color: var(--slos-accent);
				color: var(--slos-accent);
			}
			
			/* Last Scan Info */
			.slos-last-scan {
				display: flex;
				align-items: center;
				gap: 8px;
				padding: 8px 12px;
				background: var(--slos-bg-input);
				border-radius: 6px;
				font-size: 12px;
				color: var(--slos-text-muted);
			}
			
			.slos-last-scan .dashicons {
				font-size: 14px;
				width: 14px;
				height: 14px;
			}
			
			/* Pages Requiring Attention */
			.slos-pages-attention {
				width: 100%;
				overflow-x: auto;
				overflow-y: hidden;
				background: var(--slos-bg-card);
			}
			
			.slos-page-header {
				display: grid;
				grid-template-columns: minmax(200px, 2fr) 80px 80px 110px minmax(220px, 1.6fr);
				gap: 16px;
				padding: 14px 20px;
				background: var(--slos-bg-input);
				border-bottom: 1px solid var(--slos-border);
				font-size: 12px;
				font-weight: 600;
				color: var(--slos-text-secondary);
				text-transform: uppercase;
				letter-spacing: 0.5px;
				min-width: 880px;
			}
			
			.slos-page-row {
				display: grid;
				grid-template-columns: minmax(200px, 2fr) 80px 80px 110px minmax(220px, 1.6fr);
				gap: 16px;
				padding: 14px 20px;
				border-bottom: 1px solid var(--slos-border);
				align-items: center;
				transition: background 0.2s;
				min-width: 880px;
			}
			
			.slos-page-row:hover {
				background: rgba(59, 130, 246, 0.05);
			}
			
			.slos-page-name {
				color: var(--slos-text-primary);
				font-weight: 500;
			}
			
			.slos-page-issues,
			.slos-page-score {
				text-align: center;
				font-weight: 600;
			}
			
			.slos-priority-badge {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				padding: 4px 12px;
				border-radius: 12px;
				font-size: 11px;
				font-weight: 600;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}
			
			.slos-priority-badge.high {
				background: rgba(239, 68, 68, 0.15);
				color: var(--slos-error);
				border: 1px solid rgba(239, 68, 68, 0.3);
			}
			
			.slos-priority-badge.medium {
				background: rgba(245, 158, 11, 0.15);
				color: var(--slos-warning);
				border: 1px solid rgba(245, 158, 11, 0.3);
			}
			
			.slos-priority-badge.low {
				background: rgba(6, 182, 212, 0.15);
				color: var(--slos-info);
				border: 1px solid rgba(6, 182, 212, 0.3);
			}
			
			.slos-page-actions {
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.slos-page-actions button {
				display: inline-flex;
				align-items: center;
				gap: 4px;
				padding: 6px 12px;
				border: 1px solid var(--slos-border);
				border-radius: 6px;
				background: var(--slos-bg-input);
				color: var(--slos-text-secondary);
				font-size: 12px;
				cursor: pointer;
				transition: all 0.2s;
			}
			
			.slos-page-actions button:hover {
				border-color: var(--slos-accent);
				color: var(--slos-accent);
			}
			
			.slos-fix-all-btn {
				background: linear-gradient(135deg, var(--slos-success), #16a34a) !important;
				color: white !important;
				border: none !important;
			}
			
			.slos-rollback-btn {
				background: linear-gradient(135deg, var(--slos-warning), #d97706) !important;
				color: white !important;
				border: none !important;
			}
			
			.slos-autofix-toggle {
				position: relative;
				display: inline-block;
				width: 48px;
				height: 24px;
			}
			
			.slos-autofix-toggle input {
				opacity: 0;
				width: 0;
				height: 0;
			}
			
			.slos-autofix-slider {
				position: absolute;
				cursor: pointer;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background-color: var(--slos-border);
				transition: 0.3s;
				border-radius: 24px;
			}
			
			.slos-autofix-slider:before {
				position: absolute;
				content: "";
				height: 18px;
				width: 18px;
				left: 3px;
				bottom: 3px;
				background-color: white;
				transition: 0.3s;
				border-radius: 50%;
			}
			
			.slos-autofix-checkbox:checked + .slos-autofix-slider {
				background-color: var(--slos-success);
			}
			
			.slos-autofix-checkbox:checked + .slos-autofix-slider:before {
				transform: translateX(24px);
			}
			
			/* Widget Preview */
			.slos-widget-preview-box {
				display: flex;
				align-items: center;
				gap: 16px;
				padding: 20px;
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 12px;
				margin-bottom: 16px;
			}
			
			.slos-widget-mock {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 60px;
				height: 60px;
				background: linear-gradient(135deg, var(--slos-accent), var(--slos-info));
				border-radius: 12px;
				color: white;
			}
			
			.slos-widget-mock .dashicons {
				font-size: 32px;
				width: 32px;
				height: 32px;
			}
			
			.slos-widget-info {
				flex: 1;
			}
			
			.slos-widget-info h4 {
				margin: 0 0 4px;
				color: var(--slos-text-primary);
				font-size: 15px;
				font-weight: 600;
			}
			
			.slos-widget-info p {
				margin: 0;
				color: var(--slos-text-muted);
				font-size: 13px;
			}
			
			.slos-widget-toggle {
				position: relative;
				display: inline-block;
				width: 56px;
				height: 28px;
			}
			
			.slos-widget-toggle input {
				opacity: 0;
				width: 0;
				height: 0;
			}
			
			.slos-widget-slider {
				position: absolute;
				cursor: pointer;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background-color: var(--slos-border);
				transition: 0.3s;
				border-radius: 28px;
			}
			
			.slos-widget-slider:before {
				position: absolute;
				content: "";
				height: 22px;
				width: 22px;
				left: 3px;
				bottom: 3px;
				background-color: white;
				transition: 0.3s;
				border-radius: 50%;
			}
			
			#slos-widget-toggle:checked + .slos-widget-slider {
				background-color: var(--slos-success);
			}
			
			#slos-widget-toggle:checked + .slos-widget-slider:before {
				transform: translateX(28px);
			}
			
			/* Modal Styles */
			.slos-modal {
				position: fixed;
				inset: 0;
				z-index: 100000;
			}
			
			.slos-modal-overlay {
				position: absolute;
				inset: 0;
				background: rgba(0, 0, 0, 0.8);
				backdrop-filter: blur(4px);
			}
			
			.slos-modal-content {
				position: relative;
				max-width: 800px;
				max-height: 80vh;
				margin: 5vh auto;
				background: var(--slos-bg-card);
				border: 1px solid var(--slos-border);
				border-radius: 16px;
				display: flex;
				flex-direction: column;
				box-shadow: 0 25px 50px rgba(0, 0, 0, 0.6);
			}
			
			.slos-modal-header {
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 20px 24px;
				border-bottom: 1px solid var(--slos-border);
			}
			
			.slos-modal-header h3 {
				margin: 0;
				color: var(--slos-text-primary);
				font-size: 18px;
				font-weight: 600;
				display: flex;
				align-items: center;
				gap: 8px;
			}
			
			.slos-modal-close {
				background: transparent;
				border: none;
				color: var(--slos-text-muted);
				font-size: 28px;
				line-height: 1;
				cursor: pointer;
				padding: 0;
				width: 32px;
				height: 32px;
				transition: color 0.2s;
			}
			
			.slos-modal-close:hover {
				color: var(--slos-text-primary);
			}
			
			.slos-modal-body {
				flex: 1;
				overflow-y: auto;
				padding: 24px;
			}
			
			/* Scanner Configuration */
			.slos-config-section {
				margin-bottom: 24px;
			}
			
			.slos-config-section h4 {
				margin: 0 0 12px;
				color: var(--slos-text-primary);
				font-size: 14px;
				font-weight: 600;
			}
			
			.slos-config-select {
				width: 100%;
				padding: 10px 14px;
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 6px;
				color: var(--slos-text-primary);
				font-size: 14px;
			}
			
			.slos-checkbox-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
				gap: 12px;
			}
			
			.slos-checkbox-label {
				display: flex;
				align-items: center;
				gap: 8px;
				padding: 8px 12px;
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 6px;
				cursor: pointer;
				transition: all 0.2s;
			}
			
			.slos-checkbox-label:hover {
				border-color: var(--slos-accent);
			}
			
			.slos-checkbox-label input {
				margin: 0;
			}
			
			.slos-checkbox-label span {
				color: var(--slos-text-secondary);
				font-size: 13px;
			}
			
			.slos-checker-list {
				display: grid;
				gap: 8px;
			}
			
			.slos-checker-toggle {
				display: flex;
				align-items: center;
				gap: 12px;
				padding: 12px;
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 6px;
				transition: background 0.2s;
			}
			
			.slos-checker-toggle:hover {
				background: rgba(59, 130, 246, 0.05);
			}
			
			.slos-checker-checkbox {
				position: absolute;
				opacity: 0;
			}
			
			.slos-checker-slider {
				position: relative;
				display: inline-block;
				width: 44px;
				height: 24px;
				background-color: var(--slos-border);
				border-radius: 24px;
				transition: 0.3s;
			}
			
			.slos-checker-slider:before {
				position: absolute;
				content: "";
				height: 18px;
				width: 18px;
				left: 3px;
				bottom: 3px;
				background-color: white;
				border-radius: 50%;
				transition: 0.3s;
			}
			
			.slos-checker-checkbox:checked + .slos-checker-slider {
				background-color: var(--slos-success);
			}
			
			.slos-checker-checkbox:checked + .slos-checker-slider:before {
				transform: translateX(20px);
			}
			
			.slos-checker-label {
				flex: 1;
				color: var(--slos-text-secondary);
				font-size: 13px;
			}
			
			/* Export & Reporting */
			.slos-export-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
				gap: 20px;
			}
			
			.slos-export-option {
				display: grid;
				grid-template-columns: auto 1fr;
				grid-template-rows: auto auto;
				grid-template-areas:
					"icon button"
					"text text";
				column-gap: 16px;
				row-gap: 10px;
				padding: 20px;
				background: var(--slos-bg-input);
				border: 1px solid var(--slos-border);
				border-radius: 12px;
				transition: all 0.2s;
			}
			.slos-export-option > .slos-export-icon {
				grid-area: icon;
			}
			.slos-export-option > .slos-export-info {
				grid-area: text;
			}
			.slos-export-option > button {
				grid-area: button;
				justify-self: end;
			}
			
			.slos-export-option:hover {
				border-color: var(--slos-accent);
				box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
			}
			
			.slos-export-icon {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 60px;
				height: 60px;
				border-radius: 12px;
				color: white;
				flex-shrink: 0;
			}
			
			.slos-export-icon.pdf {
				background: linear-gradient(135deg, #ef4444, #dc2626);
			}
			
			.slos-export-icon.csv {
				background: linear-gradient(135deg, #22c55e, #16a34a);
			}
			
			.slos-export-icon.json {
				background: linear-gradient(135deg, #f59e0b, #d97706);
			}
			
			.slos-export-icon.email {
				background: linear-gradient(135deg, #3b82f6, #2563eb);
			}
			
			.slos-export-icon .dashicons {
				font-size: 32px;
				width: 32px;
				height: 32px;
			}
			
			.slos-export-info {
				flex: 1;
			}
			
			.slos-export-info h4 {
				margin: 0 0 4px;
				color: var(--slos-text-primary);
				font-size: 15px;
				font-weight: 600;
			}
			
			.slos-export-info p {
				margin: 0;
				color: var(--slos-text-muted);
				font-size: 12px;
			}
			
			@media (max-width: 1200px) {
				.slos-tools-grid {
					grid-template-columns: 1fr;
				}
				.slos-export-grid {
					grid-template-columns: 1fr;
				}
			}
		</style>
		
		<div class="slos-tools-container">
			<div class="slos-tools-grid">
				
				<!-- Card 1: Quick Accessibility Checks -->
				<div class="slos-tools-card">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-admin-tools"></span>
							<?php esc_html_e( 'Quick Accessibility Checks', 'shahi-legalflowsuite' ); ?>
						</h3>
					</div>
					<div class="slos-card-body">
						<!-- Color Contrast Checker -->
						<div class="slos-tool-item">
							<div class="slos-tool-icon contrast">
								<span class="dashicons dashicons-art"></span>
							</div>
							<div class="slos-tool-content">
								<h4><?php esc_html_e( 'Color Contrast Checker', 'shahi-legalflowsuite' ); ?></h4>
								<p><?php esc_html_e( 'Quickly test two hex colors (for text and background) to see if they meet WCAG contrast ratios for normal and large text before you apply them to your theme.', 'shahi-legalflowsuite' ); ?></p>
								<div class="slos-tool-inputs">
									<input type="text" class="slos-tool-input" id="slos-fg-color" placeholder="#000000" maxlength="7">
									<input type="text" class="slos-tool-input" id="slos-bg-color" placeholder="#ffffff" maxlength="7">
									<button type="button" class="slos-tool-btn" id="slos-check-contrast">
										<?php esc_html_e( 'Check', 'shahi-legalflowsuite' ); ?>
									</button>
								</div>
								<div class="slos-tool-result" id="slos-contrast-result"></div>
							</div>
						</div>
						
						<!-- Readability Score -->
						<div class="slos-tool-item">
							<div class="slos-tool-icon readability">
								<span class="dashicons dashicons-book"></span>
							</div>
							<div class="slos-tool-content">
								<h4><?php esc_html_e( 'Readability Score', 'shahi-legalflowsuite' ); ?></h4>
								<p><?php esc_html_e( 'Paste any paragraph or page copy to estimate its reading level, so you can keep legal content understandable for non‑experts while staying complete.', 'shahi-legalflowsuite' ); ?></p>
								<div class="slos-tool-inputs">
									<input type="text" class="slos-tool-input" id="slos-readability-text" placeholder="<?php esc_attr_e( 'Paste your text here...', 'shahi-legalflowsuite' ); ?>" style="flex: 3;">
									<button type="button" class="slos-tool-btn" id="slos-check-readability">
										<?php esc_html_e( 'Analyze', 'shahi-legalflowsuite' ); ?>
									</button>
								</div>
								<div class="slos-tool-result" id="slos-readability-result"></div>
							</div>
						</div>
						
						<!-- Link Text Validator -->
						<div class="slos-tool-item">
							<div class="slos-tool-icon links">
								<span class="dashicons dashicons-admin-links"></span>
							</div>
							<div class="slos-tool-content">
								<h4><?php esc_html_e( 'Link Text Validator', 'shahi-legalflowsuite' ); ?></h4>
								<p><?php esc_html_e( 'Review example link text (for buttons or inline links) to check if it is specific and descriptive enough for screen‑reader users and people using keyboard navigation.', 'shahi-legalflowsuite' ); ?></p>
								<div class="slos-tool-inputs">
									<input type="text" class="slos-tool-input" id="slos-link-text" placeholder="<?php esc_attr_e( 'e.g., Click here, Read more...', 'shahi-legalflowsuite' ); ?>" style="flex: 3;">
									<button type="button" class="slos-tool-btn" id="slos-check-link">
										<?php esc_html_e( 'Validate', 'shahi-legalflowsuite' ); ?>
									</button>
								</div>
								<div class="slos-tool-result" id="slos-link-result"></div>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Card 2: Content Scanner -->
				<div class="slos-tools-card">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-search"></span>
							<?php esc_html_e( 'Content Scanner', 'shahi-legalflowsuite' ); ?>
						</h3>
						<?php if ( $last_scan ) : ?>
						<span class="slos-last-scan">
							<span class="dashicons dashicons-clock"></span>
							<?php printf( esc_html__( 'Last scan: %s', 'shahi-legalflowsuite' ), esc_html( human_time_diff( strtotime( $last_scan ) ) . ' ago' ) ); ?>
						</span>
						<?php endif; ?>
					</div>
					<div class="slos-card-body">
						<!-- Full Site Scan -->
						<div class="slos-scanner-section">
							<h4>
								<span class="dashicons dashicons-welcome-view-site"></span>
								<?php esc_html_e( 'Full Site Scan', 'shahi-legalflowsuite' ); ?>
							</h4>
							<p><?php esc_html_e( 'Run a full accessibility review across the selected post types using your current scanner configuration. Use this when you want an updated site‑wide baseline before fixing issues.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-scanner-controls">
								<button type="button" class="slos-btn-primary" id="slos-start-scan">
									<span class="dashicons dashicons-controls-play"></span>
									<?php esc_html_e( 'Start Full Scan', 'shahi-legalflowsuite' ); ?>
								</button>
								<button type="button" class="slos-btn-secondary" id="slos-quick-scan">
									<span class="dashicons dashicons-update"></span>
									<?php esc_html_e( 'Quick Scan', 'shahi-legalflowsuite' ); ?>
								</button>
							</div>
							<div class="slos-progress-wrapper" id="slos-scan-progress-wrapper">
								<div class="slos-progress-bar">
									<div class="slos-progress-fill" id="slos-progress-bar"></div>
								</div>
								<div class="slos-progress-text">
									<span id="slos-scan-status"><?php esc_html_e( 'Initializing...', 'shahi-legalflowsuite' ); ?></span>
									<span id="slos-scan-progress">0%</span>
								</div>
							</div>
						</div>
						
						<!-- Single URL Scan -->
						<div class="slos-scanner-section">
							<h4>
								<span class="dashicons dashicons-admin-links"></span>
								<?php esc_html_e( 'Single URL Scan', 'shahi-legalflowsuite' ); ?>
							</h4>
							<p><?php esc_html_e( 'Scan a single page (on this site or an external URL) to validate changes before you run a broader scan or share a draft with stakeholders.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-tool-inputs">
								<input type="url" class="slos-tool-input" id="slos-single-url" placeholder="https://yoursite.com/page" style="flex: 3;">
								<button type="button" class="slos-tool-btn" id="slos-scan-url">
									<?php esc_html_e( 'Scan URL', 'shahi-legalflowsuite' ); ?>
								</button>
							</div>
						</div>
						
						<!-- Media Library Audit -->
						<div class="slos-scanner-section">
							<h4>
								<span class="dashicons dashicons-format-gallery"></span>
								<?php esc_html_e( 'Media Library Audit', 'shahi-legalflowsuite' ); ?>
							</h4>
							<p><?php esc_html_e( 'Scan the Media Library to locate images missing descriptive alt text so you can quickly fix common accessibility blockers without editing each page individually.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-scanner-controls">
								<button type="button" class="slos-btn-secondary" id="slos-audit-media">
									<span class="dashicons dashicons-images-alt2"></span>
									<?php esc_html_e( 'Audit Media Library', 'shahi-legalflowsuite' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Card 3: Accessibility Statement Generator -->
				<div class="slos-tools-card">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-media-document"></span>
							<?php esc_html_e( 'Accessibility Statement Generator', 'shahi-legalflowsuite' ); ?>
						</h3>
					</div>
					<div class="slos-card-body">
						<div class="slos-statement-form">
							<p style="margin-top:0; margin-bottom:8px; font-size:13px; color:var(--slos-text-muted);">
								<?php esc_html_e( 'Use this generator to create an accessibility statement that explains your commitment, current conformance level, and contact channel for issues. You can publish it as a standalone page and reference it from your footer or legal center.', 'shahi-legalflowsuite' ); ?>
							</p>
							<div class="slos-form-row">
								<div class="slos-form-group">
									<label for="slos-org-name"><?php esc_html_e( 'Organization Name', 'shahi-legalflowsuite' ); ?></label>
									<input type="text" id="slos-org-name" placeholder="<?php esc_attr_e( 'Your Company Name', 'shahi-legalflowsuite' ); ?>">
								</div>
								<div class="slos-form-group">
									<label for="slos-contact-email"><?php esc_html_e( 'Contact Email', 'shahi-legalflowsuite' ); ?></label>
									<input type="email" id="slos-contact-email" placeholder="accessibility@company.com">
								</div>
							</div>
							<div class="slos-form-row">
								<div class="slos-form-group">
									<label for="slos-wcag-target"><?php esc_html_e( 'WCAG Conformance Target', 'shahi-legalflowsuite' ); ?></label>
									<select id="slos-wcag-target">
										<option value="A">WCAG 2.2 Level A</option>
										<option value="AA" selected>WCAG 2.2 Level AA</option>
										<option value="AAA">WCAG 2.2 Level AAA</option>
									</select>
								</div>
								<div class="slos-form-group">
									<label for="slos-statement-date"><?php esc_html_e( 'Statement Date', 'shahi-legalflowsuite' ); ?></label>
									<input type="date" id="slos-statement-date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
								</div>
							</div>
							<div class="slos-form-group">
								<label for="slos-commitment"><?php esc_html_e( 'Accessibility Commitment (Optional)', 'shahi-legalflowsuite' ); ?></label>
								<textarea id="slos-commitment" placeholder="<?php esc_attr_e( 'We are committed to ensuring digital accessibility for people with disabilities...', 'shahi-legalflowsuite' ); ?>"></textarea>
							</div>
							<div class="slos-scanner-controls">
								<button type="button" class="slos-btn-primary" id="slos-generate-statement">
									<span class="dashicons dashicons-admin-page"></span>
									<?php esc_html_e( 'Generate Statement', 'shahi-legalflowsuite' ); ?>
								</button>
								<button type="button" class="slos-btn-secondary" id="slos-publish-statement">
									<span class="dashicons dashicons-upload"></span>
									<?php esc_html_e( 'Publish to Page', 'shahi-legalflowsuite' ); ?>
								</button>
							</div>
							<div class="slos-shortcode-box">
								<code>[slos_accessibility_statement]</code>
								<button type="button" class="slos-copy-btn" data-copy="[slos_accessibility_statement]">
									<span class="dashicons dashicons-admin-page"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Card 4: Accessibility Widget (shares row with Statement Generator) -->
				<div class="slos-tools-card">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-visibility"></span>
							<?php echo esc_html__('Accessibility Widget', 'shahi-legalflowsuite'); ?>
						</h3>
					</div>
					<div class="slos-card-body">
						<div class="slos-widget-preview-box">
							<div class="slos-widget-mock">
								<span class="dashicons dashicons-universal-access"></span>
							</div>
							<div class="slos-widget-info">
								<h4><?php esc_html_e('Frontend Widget', 'shahi-legalflowsuite'); ?></h4>
								<p><?php esc_html_e('Enable a floating accessibility toolbar so visitors can adjust text size, contrast and other assistive features without leaving the page.', 'shahi-legalflowsuite'); ?></p>
							</div>
							<label class="slos-widget-toggle">
								<input type="checkbox" id="slos-widget-toggle" <?php checked($widget_enabled); ?>>
								<span class="slos-widget-slider"></span>
							</label>
						</div>
						<div class="slos-action-row" style="margin-top: 16px;">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=slos-accessibility&tab=settings' ) ); ?>" class="slos-btn-secondary">
								<span class="dashicons dashicons-admin-settings"></span>
								<?php esc_html_e( 'Configure Widget Features', 'shahi-legalflowsuite' ); ?>
							</a>
						</div>
					</div>
				</div>
				
				<!-- Card 5: Pages Requiring Attention (Full Width) -->
				<div class="slos-tools-card full-width">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-flag"></span>
							<?php echo esc_html__('Pages Requiring Attention', 'shahi-legalflowsuite'); ?>
						</h3>
						<span class="badge"><?php echo esc_html(count($scan_results)); ?> <?php esc_html_e('pages', 'shahi-legalflowsuite'); ?></span>
					</div>
					<div class="slos-card-body" style="padding: 0;">
						<p style="margin:16px 20px 0; font-size:13px; color:var(--slos-text-muted);">
							<?php esc_html_e( 'These are the highest‑priority pages based on the most recent scan. Start here when planning remediation work or assigning fixes to your content team.', 'shahi-legalflowsuite' ); ?>
						</p>
						<div class="slos-pages-attention">
							<?php if (empty($scan_results)): ?>
							<div style="text-align: center; padding: 40px; color: var(--slos-text-muted);">
								<span class="dashicons dashicons-yes-alt" style="font-size: 48px; color: var(--slos-success); margin-bottom: 12px; display: block;"></span>
								<p><?php esc_html_e('No pages with accessibility issues. Great job!', 'shahi-legalflowsuite'); ?></p>
							</div>
							<?php else: ?>
								<!-- Column Headers -->
								<div class="slos-page-header">
									<span><?php esc_html_e('Page Title', 'shahi-legalflowsuite'); ?></span>
									<span><?php esc_html_e('Issues', 'shahi-legalflowsuite'); ?></span>
									<span><?php esc_html_e('Score', 'shahi-legalflowsuite'); ?></span>
									<span><?php esc_html_e('Priority', 'shahi-legalflowsuite'); ?></span>
									<span><?php esc_html_e('Actions', 'shahi-legalflowsuite'); ?></span>
								</div>
								<?php 
								// Sort by issues count descending
								usort($scan_results, function($a, $b) {
									return ($b['issues_count'] ?? 0) - ($a['issues_count'] ?? 0);
								});
								
								foreach (array_slice($scan_results, 0, 10) as $result): 
									$issues_count = isset($result['issues_count']) ? $result['issues_count'] : 0;
									$score = isset($result['score']) ? $result['score'] : 100;
									$priority = $issues_count > 5 ? 'high' : ($issues_count > 2 ? 'medium' : 'low');
									$post_id = isset($result['post_id']) ? $result['post_id'] : 0;
									$autofix_enabled = isset($result['autofix_enabled']) ? $result['autofix_enabled'] : false;
								?>
								<div class="slos-page-row" data-post-id="<?php echo esc_attr($post_id); ?>">
									<span class="slos-page-name"><?php echo esc_html($result['page'] ?? 'Unknown Page'); ?></span>
									<span class="slos-page-issues"><?php echo esc_html($issues_count); ?></span>
									<span class="slos-page-score"><?php echo esc_html($score); ?>%</span>
									<span class="slos-priority-badge <?php echo esc_attr($priority); ?>">
										<?php echo esc_html(ucfirst($priority)); ?>
									</span>
									<div class="slos-page-actions">
										<button type="button" class="slos-view-details-btn" data-post-id="<?php echo esc_attr($post_id); ?>" title="<?php esc_attr_e('View detailed scan report', 'shahi-legalflowsuite'); ?>">
											<span class="dashicons dashicons-visibility"></span>
											<?php esc_html_e('Details', 'shahi-legalflowsuite'); ?>
										</button>
										<button type="button" class="slos-fix-btn slos-fix-all-btn" data-post-id="<?php echo esc_attr($post_id); ?>" title="<?php esc_attr_e('Fix all issues on this page', 'shahi-legalflowsuite'); ?>">
											<span class="dashicons dashicons-admin-tools"></span>
											<?php esc_html_e('Fix All', 'shahi-legalflowsuite'); ?>
										</button>
										<button type="button" class="slos-rollback-btn" data-post-id="<?php echo esc_attr($post_id); ?>" title="<?php esc_attr_e('Undo recent fixes and restore previous content', 'shahi-legalflowsuite'); ?>" style="display:none;">
											<span class="dashicons dashicons-undo"></span>
											<?php esc_html_e('Rollback', 'shahi-legalflowsuite'); ?>
										</button>
										<label class="slos-autofix-toggle" title="<?php esc_attr_e('Enable auto-fix for this page', 'shahi-legalflowsuite'); ?>">
											<input type="checkbox" class="slos-autofix-checkbox" data-post-id="<?php echo esc_attr($post_id); ?>" <?php checked($autofix_enabled); ?>>
											<span class="slos-autofix-slider"></span>
										</label>
										<a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>" class="slos-fix-link" title="<?php esc_attr_e('Edit post manually', 'shahi-legalflowsuite'); ?>">
											<?php esc_html_e('Edit', 'shahi-legalflowsuite'); ?>
											<span class="dashicons dashicons-arrow-right-alt2"></span>
										</a>
									</div>
								</div>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
				
				<!-- Card 6: Scanner Configuration -->
				<div class="slos-tools-card">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-admin-settings"></span>
							<?php echo esc_html__('Scanner Configuration', 'shahi-legalflowsuite'); ?>
						</h3>
					</div>
					<div class="slos-card-body">
							<p style="margin-top:0; margin-bottom:12px; font-size:13px; color:var(--slos-text-muted);">
								<?php esc_html_e( 'Control what the scanner looks at and which checks run. These settings apply to all future scans so you can focus on the most relevant content and WCAG rules for your organization.', 'shahi-legalflowsuite' ); ?>
							</p>
							<form id="slos-scanner-config-form">
							<div class="slos-config-section">
									<h4><?php esc_html_e('WCAG Compliance Level', 'shahi-legalflowsuite'); ?></h4>
								<div class="slos-form-group">
									<select id="slos-wcag-level" name="wcag_level" class="slos-config-select">
										<option value="A" <?php selected(get_option('slos_wcag_level', 'AA'), 'A'); ?>>WCAG 2.1 Level A</option>
										<option value="AA" <?php selected(get_option('slos_wcag_level', 'AA'), 'AA'); ?>>WCAG 2.1 Level AA</option>
										<option value="AAA" <?php selected(get_option('slos_wcag_level', 'AA'), 'AAA'); ?>>WCAG 2.1 Level AAA</option>
									</select>
								</div>
							</div>
							
							<div class="slos-config-section">
								<h4><?php esc_html_e('Post Types to Scan', 'shahi-legalflowsuite'); ?></h4>
								<p style="margin:4px 0 8px; font-size:12px; color:var(--slos-text-muted);">
									<?php esc_html_e( 'Choose which content types are included when you run a full site scan (for example: posts, pages, legal templates, or custom resources).', 'shahi-legalflowsuite' ); ?>
								</p>
								<div class="slos-checkbox-grid">
									<?php
									$post_types = get_post_types(array('public' => true), 'objects');
									$enabled_types = get_option('slos_scan_post_types', array('post', 'page'));
									foreach ($post_types as $post_type):
										if (in_array($post_type->name, array('attachment', 'revision', 'nav_menu_item'))) continue;
									?>
									<label class="slos-checkbox-label">
										<input type="checkbox" name="scan_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $enabled_types)); ?>>
										<span><?php echo esc_html($post_type->label); ?></span>
									</label>
									<?php endforeach; ?>
								</div>
							</div>
							
							<div class="slos-config-section">
								<h4><?php esc_html_e('Active Accessibility Checkers', 'shahi-legalflowsuite'); ?></h4>
								<p style="margin:4px 0 8px; font-size:12px; color:var(--slos-text-muted);">
									<?php esc_html_e( 'Turn individual check families on or off depending on your current priorities. For example, you might focus on images and forms first, then expand to tables and multimedia.', 'shahi-legalflowsuite' ); ?>
								</p>
								<div class="slos-checker-list">
									<?php
									$checkers = array(
										'images' => __('Image Alt Text', 'shahi-legalflowsuite'),
										'headings' => __('Heading Structure', 'shahi-legalflowsuite'),
										'links' => __('Link Text & Titles', 'shahi-legalflowsuite'),
										'forms' => __('Form Labels & ARIA', 'shahi-legalflowsuite'),
										'color_contrast' => __('Color Contrast', 'shahi-legalflowsuite'),
										'tables' => __('Table Structure', 'shahi-legalflowsuite'),
										'multimedia' => __('Video/Audio Captions', 'shahi-legalflowsuite'),
										'keyboard' => __('Keyboard Navigation', 'shahi-legalflowsuite'),
									);
									$active_checkers = get_option('slos_active_checkers', array_keys($checkers));
									foreach ($checkers as $key => $label):
									?>
									<label class="slos-checker-toggle">
										<input type="checkbox" name="active_checkers[]" value="<?php echo esc_attr($key); ?>" class="slos-checker-checkbox" <?php checked(in_array($key, $active_checkers)); ?>>
										<span class="slos-checker-slider"></span>
										<span class="slos-checker-label"><?php echo esc_html($label); ?></span>
									</label>
									<?php endforeach; ?>
								</div>
							</div>
							
							<div class="slos-action-row">
								<button type="submit" class="slos-btn-primary" id="slos-save-config">
									<span class="dashicons dashicons-yes"></span>
									<?php esc_html_e('Save Configuration', 'shahi-legalflowsuite'); ?>
								</button>
								<button type="button" class="slos-btn-secondary" id="slos-reset-config">
									<span class="dashicons dashicons-undo"></span>
									<?php esc_html_e('Reset to Defaults', 'shahi-legalflowsuite'); ?>
								</button>
							</div>
						</form>
					</div>
				</div>
				
				<!-- Card 7: Export & Reporting (shares row with Scanner Configuration) -->
				<div class="slos-tools-card">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-download"></span>
							<?php echo esc_html__('Export & Reporting', 'shahi-legalflowsuite'); ?>
						</h3>
					</div>
					<div class="slos-card-body">
							<p style="margin-top:0; margin-bottom:12px; font-size:13px; color:var(--slos-text-muted);">
								<?php esc_html_e( 'Export your latest scan results for sharing, offline review, or integration with reporting workflows. All exports are based on the most recent completed scan.', 'shahi-legalflowsuite' ); ?>
							</p>
							<div class="slos-export-grid">
							<div class="slos-export-option">
								<div class="slos-export-icon pdf">
									<span class="dashicons dashicons-media-document"></span>
								</div>
								<div class="slos-export-info">
									<h4><?php esc_html_e('PDF Report', 'shahi-legalflowsuite'); ?></h4>
									<p><?php esc_html_e('Download a formatted, presentation‑ready report with key metrics, issue breakdowns, and high‑level recommendations suitable for stakeholders and compliance records.', 'shahi-legalflowsuite'); ?></p>
								</div>
								<button type="button" class="slos-btn-primary slos-export-btn" data-format="pdf">
									<span class="dashicons dashicons-download"></span>
									<?php esc_html_e('Export PDF', 'shahi-legalflowsuite'); ?>
								</button>
							</div>
							
							<div class="slos-export-option">
								<div class="slos-export-icon csv">
									<span class="dashicons dashicons-media-spreadsheet"></span>
								</div>
								<div class="slos-export-info">
									<h4><?php esc_html_e('CSV Data', 'shahi-legalflowsuite'); ?></h4>
									<p><?php esc_html_e('Export a row‑by‑row list of issues so you can filter, sort, and pivot in Excel, Google Sheets or BI tools as part of your accessibility workstream.', 'shahi-legalflowsuite'); ?></p>
								</div>
								<button type="button" class="slos-btn-primary slos-export-btn" data-format="csv">
									<span class="dashicons dashicons-download"></span>
									<?php esc_html_e('Export CSV', 'shahi-legalflowsuite'); ?>
								</button>
							</div>
							
							<div class="slos-export-option">
								<div class="slos-export-icon json">
									<span class="dashicons dashicons-media-code"></span>
								</div>
								<div class="slos-export-info">
									<h4><?php esc_html_e('JSON Export', 'shahi-legalflowsuite'); ?></h4>
									<p><?php esc_html_e('Generate a machine‑readable export suitable for custom integrations, APIs or CI pipelines that track accessibility over time.', 'shahi-legalflowsuite'); ?></p>
								</div>
								<button type="button" class="slos-btn-primary slos-export-btn" data-format="json">
									<span class="dashicons dashicons-download"></span>
									<?php esc_html_e('Export JSON', 'shahi-legalflowsuite'); ?>
								</button>
							</div>
							
							<div class="slos-export-option">
								<div class="slos-export-icon email">
									<span class="dashicons dashicons-email-alt"></span>
								</div>
								<div class="slos-export-info">
									<h4><?php esc_html_e('Email Reports', 'shahi-legalflowsuite'); ?></h4>
									<p><?php esc_html_e('Set up automatic weekly or monthly email summaries so key stakeholders receive accessibility status updates without logging into WordPress.', 'shahi-legalflowsuite'); ?></p>
								</div>
								<button type="button" class="slos-btn-secondary" id="slos-schedule-email">
									<span class="dashicons dashicons-calendar-alt"></span>
									<?php esc_html_e('Schedule Reports', 'shahi-legalflowsuite'); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Debug Panel: Scanner & Email Configuration (Full Width) -->
				<div class="slos-tools-card full-width">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-info"></span>
							<?php esc_html_e( 'Scanner Debug & Config Status', 'shahi-legalflowsuite' ); ?>
						</h3>
						<span class="badge"><?php esc_html_e( 'Read-only debug info', 'shahi-legalflowsuite' ); ?></span>
					</div>
					<div class="slos-card-body slos-debug-panel">
						<p style="margin-top:0; margin-bottom:14px;">
							<?php esc_html_e( 'Use this read‑only view to confirm what the scanner will use on the next run and how email reports are configured. If results look unexpected, compare these values with the Scanner Configuration and Export & Reporting cards above.', 'shahi-legalflowsuite' ); ?>
						</p>
						<div class="slos-debug-grid">
							<div class="slos-debug-section">
								<h4><?php esc_html_e( 'Scanner Configuration', 'shahi-legalflowsuite' ); ?></h4>
								<ul class="slos-debug-list">
									<li>
										<span class="slos-debug-label"><?php esc_html_e( 'WCAG Level:', 'shahi-legalflowsuite' ); ?> </span>
										<span class="slos-debug-value">WCAG 2.1 Level <?php echo esc_html( $wcag_level ); ?></span>
									</li>
									<li>
										<span class="slos-debug-label"><?php esc_html_e( 'Post Types to Scan:', 'shahi-legalflowsuite' ); ?> </span>
										<span class="slos-debug-value">
											<?php
												if ( empty( $scan_post_types ) ) {
													esc_html_e( 'None (defaults to Posts & Pages)', 'shahi-legalflowsuite' );
												} else {
													$post_type_labels = array();
													foreach ( $scan_post_types as $pt ) {
														$ptype_obj = get_post_type_object( $pt );
														$post_type_labels[] = $ptype_obj && isset( $ptype_obj->label ) ? $ptype_obj->label : $pt;
													}
													echo esc_html( implode( ', ', $post_type_labels ) );
												}
											?>
										</span>
									</li>
									<li>
										<span class="slos-debug-label"><?php esc_html_e( 'Active Checkers:', 'shahi-legalflowsuite' ); ?> </span>
										<span class="slos-debug-value">
											<?php
												if ( empty( $active_checkers ) ) {
													esc_html_e( 'All available checks (default)', 'shahi-legalflowsuite' );
												} else {
													echo esc_html( implode( ', ', $active_checkers ) );
												}
											?>
										</span>
									</li>
								</ul>
							</div>
							<div class="slos-debug-section">
								<h4><?php esc_html_e( 'Email Report Schedule', 'shahi-legalflowsuite' ); ?></h4>
								<ul class="slos-debug-list">
									<li>
										<span class="slos-debug-label"><?php esc_html_e( 'Email:', 'shahi-legalflowsuite' ); ?> </span>
										<span class="slos-debug-value"><?php echo $report_email ? esc_html( $report_email ) : esc_html__( 'Not configured', 'shahi-legalflowsuite' ); ?></span>
									</li>
									<li>
										<span class="slos-debug-label"><?php esc_html_e( 'Frequency:', 'shahi-legalflowsuite' ); ?> </span>
										<span class="slos-debug-value">
											<?php
												if ( ! $report_frequency ) {
													esc_html_e( 'Not configured', 'shahi-legalflowsuite' );
												} elseif ( 'monthly' === $report_frequency ) {
													esc_html_e( 'Monthly', 'shahi-legalflowsuite' );
												} else {
													esc_html_e( 'Weekly', 'shahi-legalflowsuite' );
												}
											?>
										</span>
									</li>
									<li>
										<span class="slos-debug-label"><?php esc_html_e( 'Last Sent:', 'shahi-legalflowsuite' ); ?> </span>
										<span class="slos-debug-value"><?php echo $report_last_sent ? esc_html( $report_last_sent ) : esc_html__( 'Never', 'shahi-legalflowsuite' ); ?></span>
									</li>
								</ul>
							</div>
							<div class="slos-debug-section">
								<h4><?php esc_html_e( 'Cron Status', 'shahi-legalflowsuite' ); ?></h4>
								<p style="margin:0 0 8px 0;">
									<?php if ( $next_report_ts ) : ?>
										<span class="slos-debug-badge ok">
											<span class="dashicons dashicons-yes"></span>
											<?php esc_html_e( 'Scheduled', 'shahi-legalflowsuite' ); ?>
										</span>
									<?php else : ?>
										<span class="slos-debug-badge warn">
											<span class="dashicons dashicons-warning"></span>
											<?php esc_html_e( 'No cron event found', 'shahi-legalflowsuite' ); ?>
										</span>
									<?php endif; ?>
								</p>
								<p style="margin:0;" class="slos-debug-label">
									<?php if ( $next_report_ts ) : ?>
										<?php
											/* translators: %s: date/time */
											printf(
												esc_html__( 'Next run: %s', 'shahi-legalflowsuite' ),
												esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_report_ts ) )
											);
										?>
									<?php else : ?>
										<?php esc_html_e( 'WordPress cron must be running for scheduled email reports.', 'shahi-legalflowsuite' ); ?>
									<?php endif; ?>
								</p>
							</div>
						</div>
					</div>
				</div>
				
			</div>
		</div>
		
		<!-- Scan Details Modal -->
		<div id="slos-scan-modal" class="slos-modal" style="display: none;">
			<div class="slos-modal-overlay"></div>
			<div class="slos-modal-content">
				<div class="slos-modal-header">
					<h3><span class="dashicons dashicons-chart-bar"></span> <?php esc_html_e('Scan Details', 'shahi-legalflowsuite'); ?></h3>
					<button type="button" class="slos-modal-close">&times;</button>
				</div>
				<div class="slos-modal-body" id="slos-modal-body">
					<!-- Content loaded dynamically -->
				</div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			// Color Contrast Checker
			$('#slos-check-contrast').on('click', function() {
				var fg = $('#slos-fg-color').val() || '#000000';
				var bg = $('#slos-bg-color').val() || '#ffffff';
				
				// Simple contrast calculation
				var ratio = calculateContrastRatio(fg, bg);
				var result = $('#slos-contrast-result');
				
				result.removeClass('pass fail').addClass('show');
				
				if (ratio >= 4.5) {
					result.addClass('pass').html(
						'<strong>✓ Pass (AA Normal)</strong> - Contrast ratio: ' + ratio.toFixed(2) + ':1' +
						(ratio >= 7 ? '<br><strong>✓ Pass (AAA)</strong>' : '')
					);
				} else if (ratio >= 3) {
					result.addClass('pass').html('<strong>✓ Pass (AA Large Text Only)</strong> - Contrast ratio: ' + ratio.toFixed(2) + ':1');
				} else {
					result.addClass('fail').html('<strong>✗ Fail</strong> - Contrast ratio: ' + ratio.toFixed(2) + ':1. Minimum required: 4.5:1 for normal text.');
				}
			});
			
			// Readability Score
			$('#slos-check-readability').on('click', function() {
				var text = $('#slos-readability-text').val();
				if (!text) return;
				
				var words = text.split(/\s+/).length;
				var sentences = text.split(/[.!?]+/).filter(Boolean).length || 1;
				var syllables = countSyllables(text);
				
				// Flesch-Kincaid Grade Level
				var grade = 0.39 * (words / sentences) + 11.8 * (syllables / words) - 15.59;
				grade = Math.max(0, Math.round(grade * 10) / 10);
				
				var result = $('#slos-readability-result');
				result.addClass('show');
				
				var levelText = grade <= 6 ? 'Easy (Elementary)' : 
							   grade <= 8 ? 'Standard (Middle School)' : 
							   grade <= 12 ? 'Difficult (High School)' : 'Very Difficult (College+)';
				
				result.removeClass('pass fail').addClass(grade <= 8 ? 'pass' : 'fail');
				result.html('<strong>Grade Level: ' + grade + '</strong> - ' + levelText + '<br>WCAG recommends lower secondary education level (grade 7-9) for broad accessibility.');
			});
			
			// Link Text Validator
			$('#slos-check-link').on('click', function() {
				var linkText = $('#slos-link-text').val().toLowerCase().trim();
				if (!linkText) return;
				
				var genericPhrases = ['click here', 'click', 'here', 'read more', 'more', 'learn more', 'link', 'this link', 'go', 'see more', 'details', 'info'];
				var result = $('#slos-link-result');
				result.addClass('show');
				
				if (genericPhrases.includes(linkText) || linkText.length < 4) {
					result.removeClass('pass').addClass('fail');
					result.html('<strong>✗ Not Descriptive</strong> - Link text should describe the destination. Avoid generic phrases like "click here" or "read more".');
				} else {
					result.removeClass('fail').addClass('pass');
					result.html('<strong>✓ Looks Good</strong> - Link text appears to be descriptive. Ensure it makes sense out of context.');
				}
			});
			
			// Copy shortcode
			$('.slos-copy-btn').on('click', function() {
				var text = $(this).data('copy');
				navigator.clipboard.writeText(text);
				$(this).find('.dashicons').removeClass('dashicons-admin-page').addClass('dashicons-yes');
				setTimeout(() => {
					$(this).find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-admin-page');
				}, 2000);
			});
			
			// Helper functions
			function calculateContrastRatio(fg, bg) {
				var l1 = getLuminance(hexToRgb(fg));
				var l2 = getLuminance(hexToRgb(bg));
				var lighter = Math.max(l1, l2);
				var darker = Math.min(l1, l2);
				return (lighter + 0.05) / (darker + 0.05);
			}
			
			function hexToRgb(hex) {
				hex = hex.replace('#', '');
				if (hex.length === 3) {
					hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
				}
				return {
					r: parseInt(hex.substring(0, 2), 16),
					g: parseInt(hex.substring(2, 4), 16),
					b: parseInt(hex.substring(4, 6), 16)
				};
			}
			
			function getLuminance(rgb) {
				var a = [rgb.r, rgb.g, rgb.b].map(function(v) {
					v /= 255;
					return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
				});
				return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
			}
			
			function countSyllables(text) {
				text = text.toLowerCase().replace(/[^a-z]/g, ' ');
				var words = text.split(/\s+/).filter(Boolean);
				var count = 0;
				words.forEach(function(word) {
					word = word.replace(/(?:[^laeiouy]es|ed|[^laeiouy]e)$/, '');
					word = word.replace(/^y/, '');
					var matches = word.match(/[aeiouy]{1,2}/g);
					count += matches ? matches.length : 1;
				});
				return count;
			}
			
			// ============================================
			// FULL SITE SCAN - Start Full Scan Button with Progress Modal
			// ============================================
			$('#slos-start-scan').on('click', function() {
				// Use the new SLOSScanProgress modal
				if (typeof SLOSScanProgress !== 'undefined') {
					SLOSScanProgress.start({
						onComplete: function(results) {
							console.log('Scan completed:', results);
							// Results are automatically displayed in the modal
							// Optionally reload the page to update dashboard
							setTimeout(function() {
								displayScanResults(results, 
									results.reduce(function(sum, r) { return sum + r.issues; }, 0),
									results.reduce(function(sum, r) { return sum + r.critical; }, 0)
								);
							}, 1000);
						}
					});
				} else {
					alert('<?php echo esc_js( __( 'Scan progress module not loaded. Please refresh the page.', 'shahi-legalflowsuite' ) ); ?>');
				}
			});
			
			// Display scan results in the results area
			function displayScanResults(results, totalIssues, criticalIssues) {
				var $resultsArea = $('#slos-scan-results');
				
				if (results.length === 0) {
					$resultsArea.html('<p style="color: var(--slos-text-muted); text-align: center; padding: 40px 0;">No issues found! Your site is accessible.</p>');
					return;
				}
				
				// Sort by issues count
				results.sort(function(a, b) { return b.issues - a.issues; });
				
				var html = '<div style="margin-bottom: 16px; padding: 12px; background: var(--slos-bg-input); border-radius: 8px; display: flex; gap: 24px; flex-wrap: wrap; align-items: center;">';
				html += '<div><strong style="color: var(--slos-text-primary);">' + results.length + '</strong> <span style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Pages Scanned', 'shahi-legalflowsuite' ) ); ?></span></div>';
				html += '<div><strong style="color: var(--slos-warning);">' + totalIssues + '</strong> <span style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Total Issues', 'shahi-legalflowsuite' ) ); ?></span></div>';
				html += '<div><strong style="color: var(--slos-error);">' + criticalIssues + '</strong> <span style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Critical', 'shahi-legalflowsuite' ) ); ?></span></div>';
				html += '<div style="margin-left: auto;"><button type="button" class="slos-btn-primary slos-fix-all-pages" style="padding: 8px 16px; font-size: 13px;"><span class="dashicons dashicons-admin-tools"></span> <?php echo esc_js( __( 'Fix All Pages', 'shahi-legalflowsuite' ) ); ?></button></div>';
				html += '</div>';
				
				html += '<table style="width: 100%; border-collapse: collapse;">';
				html += '<thead><tr style="border-bottom: 1px solid var(--slos-border);">';
				html += '<th style="text-align: left; padding: 12px; color: var(--slos-text-secondary); font-weight: 500;"><?php echo esc_js( __( 'Page', 'shahi-legalflowsuite' ) ); ?></th>';
				html += '<th style="text-align: center; padding: 12px; color: var(--slos-text-secondary); font-weight: 500;"><?php echo esc_js( __( 'Issues', 'shahi-legalflowsuite' ) ); ?></th>';
				html += '<th style="text-align: center; padding: 12px; color: var(--slos-text-secondary); font-weight: 500;"><?php echo esc_js( __( 'Score', 'shahi-legalflowsuite' ) ); ?></th>';
				html += '<th style="text-align: center; padding: 12px; color: var(--slos-text-secondary); font-weight: 500;"><?php echo esc_js( __( 'Actions', 'shahi-legalflowsuite' ) ); ?></th>';
				html += '</tr></thead><tbody>';
				
				// Store results globally for fix all functionality
				window.slosScanResults = results;
				
				results.slice(0, 20).forEach(function(r, idx) {
					var scoreColor = r.score >= 90 ? 'var(--slos-success)' : r.score >= 70 ? 'var(--slos-warning)' : 'var(--slos-error)';
					html += '<tr style="border-bottom: 1px solid var(--slos-border);" data-result-idx="' + idx + '" data-post-id="' + (r.post_id || '') + '">';
					html += '<td style="padding: 12px; color: var(--slos-text-primary);">' + r.title + '</td>';
					html += '<td style="text-align: center; padding: 12px; color: ' + (r.critical > 0 ? 'var(--slos-error)' : 'var(--slos-warning)') + ';" class="slos-issues-cell">' + r.issues + '</td>';
					html += '<td style="text-align: center; padding: 12px; color: ' + scoreColor + ';">' + r.score + '%</td>';
					html += '<td style="text-align: center; padding: 12px;">';
					if (r.post_id && r.issues > 0) {
						html += '<button type="button" class="slos-fix-page-btn" data-post-id="' + r.post_id + '" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: linear-gradient(135deg, var(--slos-success), #16a34a); color: white; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;"><span class="dashicons dashicons-admin-tools" style="font-size: 14px;"></span> <?php echo esc_js( __( 'Fix', 'shahi-legalflowsuite' ) ); ?></button>';
					} else if (r.issues === 0) {
						html += '<span style="color: var(--slos-success);"><span class="dashicons dashicons-yes-alt"></span></span>';
					} else {
						html += '-';
					}
					html += '</td>';
					html += '</tr>';
				});
				
				html += '</tbody></table>';
				
				if (results.length > 20) {
					html += '<p style="text-align: center; margin-top: 12px; color: var(--slos-text-muted);"><?php echo esc_js( __( 'Showing top 20 results. View Dashboard for full report.', 'shahi-legalflowsuite' ) ); ?></p>';
				}
				
				$resultsArea.html(html);
				
				// Bind fix button handlers
				bindFixButtonHandlers();
			}
			
			// Bind Fix button handlers for scan results
			function bindFixButtonHandlers() {
				// Single page fix
				$('.slos-fix-page-btn').off('click').on('click', function() {
					var $btn = $(this);
					var postId = $btn.data('post-id');
					var $row = $btn.closest('tr');
					
					if (!postId) return;
					
					$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin" style="font-size: 14px;"></span> <?php echo esc_js( __( 'Fixing...', 'shahi-legalflowsuite' ) ); ?>');
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'slos_fix_all_issues',
							nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>',
							post_id: postId
						},
						success: function(response) {
							if (response.success) {
								var data = response.data;
								var fixedCount = data.total_issues_fixed || 0;
								var newIssues = data.new_issues_count || 0;
								var manualRequired = data.manual_required || 0;
								
								// Update button based on result
								if (fixedCount > 0) {
									$btn.html('<span class="dashicons dashicons-yes" style="font-size: 14px;"></span> <?php echo esc_js( __( 'Fixed!', 'shahi-legalflowsuite' ) ); ?>').css('background', 'var(--slos-success)');
								} else if (manualRequired > 0) {
									$btn.html('<span class="dashicons dashicons-warning" style="font-size: 14px;"></span> <?php echo esc_js( __( 'Manual', 'shahi-legalflowsuite' ) ); ?>').css('background', 'var(--slos-warning)');
								}
								
								// Update issues count with new value from server
								var $issuesCell = $row.find('.slos-issues-cell');
								$issuesCell.text(newIssues);
								
								if (newIssues === 0) {
									$issuesCell.css('color', 'var(--slos-success)');
								}
								
								// Show modal with full details
								showScannerNotification(
									data.message, 
									fixedCount > 0 ? 'success' : 'warning', 
									data.manual_fix_guidance,
									data.fixed_details || [],
									data.failed_details || []
								);
								
								setTimeout(function() {
									$btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools" style="font-size: 14px;"></span> <?php echo esc_js( __( 'Fix', 'shahi-legalflowsuite' ) ); ?>').css('background', 'linear-gradient(135deg, var(--slos-success), #16a34a)');
								}, 3000);
							} else {
								var errorMsg = response.data && response.data.message ? response.data.message : '<?php echo esc_js( __( 'Unknown error', 'shahi-legalflowsuite' ) ); ?>';
								showScannerNotification(errorMsg, 'error', response.data && response.data.guidance ? response.data.guidance : null);
								$btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools" style="font-size: 14px;"></span> <?php echo esc_js( __( 'Fix', 'shahi-legalflowsuite' ) ); ?>');
							}
						},
						error: function() {
							showScannerNotification('<?php echo esc_js( __( 'Network error.', 'shahi-legalflowsuite' ) ); ?>', 'error');
							$btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools" style="font-size: 14px;"></span> <?php echo esc_js( __( 'Fix', 'shahi-legalflowsuite' ) ); ?>');
						}
					});
				});
				
				// Fix all pages
				$('.slos-fix-all-pages').off('click').on('click', function() {
					var $btn = $(this);
					
					if (!window.slosScanResults || window.slosScanResults.length === 0) {
						showScannerNotification('<?php echo esc_js( __( 'No pages to fix.', 'shahi-legalflowsuite' ) ); ?>', 'warning');
						return;
					}
					
					var pagesWithIssues = window.slosScanResults.filter(function(r) { return r.post_id && r.issues > 0; });
					
					if (pagesWithIssues.length === 0) {
						showScannerNotification('<?php echo esc_js( __( 'No pages with issues to fix.', 'shahi-legalflowsuite' ) ); ?>', 'info');
						return;
					}
					
					if (!confirm('<?php echo esc_js( __( 'This will attempt to fix issues on', 'shahi-legalflowsuite' ) ); ?> ' + pagesWithIssues.length + ' <?php echo esc_js( __( 'pages. Continue?', 'shahi-legalflowsuite' ) ); ?>')) {
						return;
					}
					
					$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js( __( 'Fixing...', 'shahi-legalflowsuite' ) ); ?>');
					
					var completed = 0;
					var totalFixed = 0;
					var totalManual = 0;
					var allGuidance = [];
					
					function fixNextPage(idx) {
						if (idx >= pagesWithIssues.length) {
							// Show final results
							var msg = totalFixed + ' <?php echo esc_js( __( 'issues fixed across', 'shahi-legalflowsuite' ) ); ?> ' + pagesWithIssues.length + ' <?php echo esc_js( __( 'pages', 'shahi-legalflowsuite' ) ); ?>';
							if (totalManual > 0) {
								msg += '. ' + totalManual + ' <?php echo esc_js( __( 'issues require manual attention', 'shahi-legalflowsuite' ) ); ?>';
							}
							
							$btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> <?php echo esc_js( __( 'Done!', 'shahi-legalflowsuite' ) ); ?>');
							showScannerNotification(msg, totalFixed > 0 ? 'success' : 'warning', allGuidance.length > 0 ? allGuidance.slice(0, 3) : null);
							
							setTimeout(function() {
								$btn.html('<span class="dashicons dashicons-admin-tools"></span> <?php echo esc_js( __( 'Fix All Pages', 'shahi-legalflowsuite' ) ); ?>');
							}, 3000);
							return;
						}
						
						var page = pagesWithIssues[idx];
						$btn.html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js( __( 'Fixing', 'shahi-legalflowsuite' ) ); ?> ' + (idx + 1) + '/' + pagesWithIssues.length + '...');
						
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'slos_fix_all_issues',
								nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>',
								post_id: page.post_id
							},
							success: function(response) {
								if (response.success) {
									totalFixed += response.data.total_issues_fixed || 0;
									totalManual += response.data.manual_required || 0;
									if (response.data.manual_fix_guidance) {
										allGuidance = allGuidance.concat(response.data.manual_fix_guidance);
									}
								}
							},
							complete: function() {
								completed++;
								fixNextPage(idx + 1);
							}
						});
					}
					
					fixNextPage(0);
				});
			}
			
			/**
			 * Show Fix Results Modal - Centered popup with full details
			 */
			function showScannerNotification(message, type, guidance, fixedDetails, failedDetails) {
				$('.slos-fix-results-modal-overlay').remove();
				
				var iconClass = type === 'success' ? 'yes-alt' : (type === 'error' ? 'dismiss' : (type === 'warning' ? 'warning' : 'info-outline'));
				var headerBg = type === 'success' ? 'linear-gradient(135deg, #22c55e, #16a34a)' : (type === 'error' ? 'linear-gradient(135deg, #ef4444, #dc2626)' : (type === 'warning' ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, #3b82f6, #2563eb)'));
				var headerTitle = type === 'success' ? '<?php echo esc_js( __( 'Fix Complete', 'shahi-legalflowsuite' ) ); ?>' : (type === 'error' ? '<?php echo esc_js( __( 'Fix Failed', 'shahi-legalflowsuite' ) ); ?>' : (type === 'warning' ? '<?php echo esc_js( __( 'Manual Fixes Required', 'shahi-legalflowsuite' ) ); ?>' : '<?php echo esc_js( __( 'Information', 'shahi-legalflowsuite' ) ); ?>'));
				
				var html = '<div class="slos-fix-results-modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 100002; display: flex; align-items: center; justify-content: center; padding: 20px;">';
				html += '<div class="slos-fix-results-modal" style="background: #334155; border: 1px solid #475569; border-radius: 16px; width: 100%; max-width: 600px; max-height: 80vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px rgba(0,0,0,0.6); overflow: hidden;">';
				
				// Modal Header
				html += '<div style="background: ' + headerBg + '; padding: 20px 24px; display: flex; align-items: center; gap: 16px;">';
				html += '<span class="dashicons dashicons-' + iconClass + '" style="color: white; font-size: 32px; width: 32px; height: 32px;"></span>';
				html += '<div style="flex: 1;"><h2 style="margin: 0; color: white; font-size: 20px; font-weight: 600;">' + headerTitle + '</h2>';
				html += '<p style="margin: 4px 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">' + message + '</p></div>';
				html += '<button type="button" class="slos-close-fix-modal" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 20px; line-height: 1; transition: background 0.2s;">&times;</button>';
				html += '</div>';
				
				// Modal Body - Scrollable
				html += '<div style="flex: 1; overflow-y: auto; padding: 24px; background: #334155;">';
				
				// Fixed Issues Section
				if (fixedDetails && fixedDetails.length > 0) {
					html += '<div style="margin-bottom: 24px;">';
					html += '<h3 style="margin: 0 0 12px; color: #22c55e; font-size: 16px; display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js( __( 'Automatically Fixed', 'shahi-legalflowsuite' ) ); ?> (' + fixedDetails.length + ')</h3>';
					html += '<div style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.4); border-radius: 8px; padding: 12px;">';
					fixedDetails.forEach(function(item) {
						html += '<div style="padding: 8px 0; border-bottom: 1px solid rgba(34, 197, 94, 0.2);">';
						html += '<div style="font-weight: 500; color: #f1f5f9;">' + (item.title || item.type || 'Issue') + '</div>';
						if (item.description) {
							html += '<div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">' + item.description + '</div>';
						}
						html += '</div>';
					});
					html += '</div></div>';
				}
				
				// Failed Issues Section
				if (failedDetails && failedDetails.length > 0) {
					html += '<div style="margin-bottom: 24px;">';
					html += '<h3 style="margin: 0 0 12px; color: #ef4444; font-size: 16px; display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-dismiss"></span> <?php echo esc_js( __( 'Could Not Fix', 'shahi-legalflowsuite' ) ); ?> (' + failedDetails.length + ')</h3>';
					html += '<div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 8px; padding: 12px;">';
					failedDetails.forEach(function(item) {
						html += '<div style="padding: 8px 0; border-bottom: 1px solid rgba(239, 68, 68, 0.2);">';
						html += '<div style="font-weight: 500; color: #f1f5f9;">' + (item.title || item.type || 'Issue') + '</div>';
						if (item.reason) {
							html += '<div style="font-size: 12px; color: #fca5a5; margin-top: 4px;">' + item.reason + '</div>';
						}
						html += '</div>';
					});
					html += '</div></div>';
				}
				
				// Manual Fix Guidance Section
				if (guidance && guidance.length > 0) {
					html += '<div>';
					html += '<h3 style="margin: 0 0 12px; color: #f59e0b; font-size: 16px; display: flex; align-items: center; gap: 8px;"><span class="dashicons dashicons-edit"></span> <?php echo esc_js( __( 'Manual Fixes Required', 'shahi-legalflowsuite' ) ); ?> (' + guidance.length + ')</h3>';
					
					guidance.forEach(function(guide, idx) {
						html += '<div style="background: #475569; border: 1px solid #64748b; border-radius: 8px; padding: 16px; margin-bottom: 12px;">';
						html += '<div style="display: flex; align-items: flex-start; gap: 12px;">';
						html += '<span style="background: #f59e0b; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0;">' + (idx + 1) + '</span>';
						html += '<div style="flex: 1;">';
						html += '<h4 style="margin: 0 0 8px; color: #f1f5f9; font-size: 14px; font-weight: 600;">' + guide.title + '</h4>';
						html += '<p style="margin: 0 0 12px; color: #cbd5e1; font-size: 13px;">' + guide.description + '</p>';
						
						if (guide.steps && guide.steps.length > 0) {
							html += '<div style="background: #1e293b; border-radius: 6px; padding: 12px;">';
							html += '<div style="font-weight: 500; color: #cbd5e1; font-size: 12px; margin-bottom: 8px;"><?php echo esc_js( __( 'How to fix:', 'shahi-legalflowsuite' ) ); ?></div>';
							html += '<ol style="margin: 0; padding-left: 20px; color: #e2e8f0; font-size: 13px;">';
							guide.steps.forEach(function(step) {
								html += '<li style="margin-bottom: 6px;">' + step + '</li>';
							});
							html += '</ol>';
							if (guide.tip) {
								html += '<div style="margin-top: 10px; padding: 8px 12px; background: rgba(59, 130, 246, 0.2); border-radius: 4px; font-size: 12px; color: #93c5fd;"><span class="dashicons dashicons-lightbulb" style="font-size: 14px; margin-right: 4px;"></span> ' + guide.tip + '</div>';
							}
							html += '</div>';
						}
						html += '</div></div></div>';
					});
					html += '</div>';
				}
				
				// No issues case
				if ((!guidance || guidance.length === 0) && (!fixedDetails || fixedDetails.length === 0) && (!failedDetails || failedDetails.length === 0)) {
					html += '<div style="text-align: center; padding: 40px 20px;">';
					html += '<span class="dashicons dashicons-' + iconClass + '" style="font-size: 48px; width: 48px; height: 48px; color: ' + (type === 'success' ? '#22c55e' : (type === 'error' ? '#ef4444' : '#f59e0b')) + '; display: block; margin: 0 auto 16px;"></span>';
					html += '<p style="color: #f1f5f9; font-size: 16px; margin: 0;">' + message + '</p>';
					html += '</div>';
				}
				
				html += '</div>';
				
				// Modal Footer
				html += '<div style="padding: 16px 24px; border-top: 1px solid #64748b; background: #334155; display: flex; justify-content: flex-end; gap: 12px;">';
				html += '<button type="button" class="slos-close-fix-modal" style="padding: 10px 24px; background: #475569; border: 1px solid #64748b; border-radius: 8px; color: #f1f5f9; font-size: 14px; font-weight: 500; cursor: pointer; transition: background 0.2s;"><?php echo esc_js( __( 'Close', 'shahi-legalflowsuite' ) ); ?></button>';
				html += '</div>';
				
				html += '</div></div>';
				
				var $modal = $(html);
				$('body').append($modal);
				
				// Close handlers
				$modal.find('.slos-close-fix-modal').on('click', function() {
					$modal.fadeOut(200, function() { $(this).remove(); });
				});
				
				$modal.on('click', function(e) {
					if ($(e.target).hasClass('slos-fix-results-modal-overlay')) {
						$modal.fadeOut(200, function() { $(this).remove(); });
					}
				});
				
				// ESC key to close
				$(document).on('keydown.fixModal', function(e) {
					if (e.key === 'Escape') {
						$modal.fadeOut(200, function() { $(this).remove(); });
						$(document).off('keydown.fixModal');
					}
				});
			}
			
			// ============================================
			// QUICK SCAN - Quick Scan Button
			// ============================================
			$('#slos-quick-scan').on('click', function() {
				var $btn = $(this);
				var $progressWrapper = $('#slos-scan-progress-wrapper');
				var $progressBar = $('#slos-progress-bar');
				var $scanStatus = $('#slos-scan-status');
				var $scanProgress = $('#slos-scan-progress');
				
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js( __( 'Scanning...', 'shahi-legalflowsuite' ) ); ?>');
				$progressWrapper.addClass('show');
				$scanStatus.text('<?php echo esc_js( __( 'Running quick scan (recent posts only)...', 'shahi-legalflowsuite' ) ); ?>');
				$progressBar.css('width', '30%');
				$scanProgress.text('30%');
				
				// Quick scan - only scan recent 10 posts
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_get_posts_to_scan',
						nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>',
						limit: 10
					},
					success: function(response) {
						if (!response.success || !response.data || response.data.length === 0) {
							$scanStatus.text('<?php echo esc_js( __( 'No pages found.', 'shahi-legalflowsuite' ) ); ?>');
							$btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php echo esc_js( __( 'Quick Scan', 'shahi-legalflowsuite' ) ); ?>');
							$progressWrapper.removeClass('show');
							return;
						}
						
						var pages = response.data.slice(0, 10);
						var completed = 0;
						var total = pages.length;
						
						$progressBar.css('width', '50%');
						$scanProgress.text('50%');
						
						pages.forEach(function(page, index) {
							$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
									action: 'slos_scan_single_post',
									nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>',
									post_id: page.id
								},
								complete: function() {
									completed++;
									var pct = 50 + Math.round((completed / total) * 50);
									$progressBar.css('width', pct + '%');
									$scanProgress.text(pct + '%');
									
									if (completed >= total) {
										$scanStatus.text('<?php echo esc_js( __( 'Quick scan complete!', 'shahi-legalflowsuite' ) ); ?>');
										$btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php echo esc_js( __( 'Quick Scan', 'shahi-legalflowsuite' ) ); ?>');
										setTimeout(function() {
											$progressWrapper.removeClass('show');
										}, 2000);
									}
								}
							});
						});
					},
					error: function() {
						$scanStatus.text('<?php echo esc_js( __( 'Error during quick scan.', 'shahi-legalflowsuite' ) ); ?>');
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php echo esc_js( __( 'Quick Scan', 'shahi-legalflowsuite' ) ); ?>');
					}
				});
			});
			
			// ============================================
			// SINGLE URL SCAN - Scan URL Button
			// ============================================
			$('#slos-scan-url').on('click', function() {
				var $btn = $(this);
				var url = $('#slos-single-url').val().trim();
				
				if (!url) {
					alert('<?php echo esc_js( __( 'Please enter a URL to scan.', 'shahi-legalflowsuite' ) ); ?>');
					return;
				}
				
				// Validate URL
				try {
					new URL(url);
				} catch(e) {
					alert('<?php echo esc_js( __( 'Please enter a valid URL.', 'shahi-legalflowsuite' ) ); ?>');
					return;
				}
				
				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Scanning...', 'shahi-legalflowsuite' ) ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_scan_single_post',
						nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>',
						url: url
					},
					success: function(response) {
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Scan URL', 'shahi-legalflowsuite' ) ); ?>');
						
						var $resultsArea = $('#slos-scan-results');
						
						if (response.success && response.data) {
							var d = response.data;
							var html = '<div style="background: var(--slos-bg-input); border-radius: 8px; padding: 20px;">';
							html += '<h4 style="color: var(--slos-text-primary); margin: 0 0 12px;"><?php echo esc_js( __( 'URL Scan Results', 'shahi-legalflowsuite' ) ); ?></h4>';
							html += '<p style="color: var(--slos-text-muted); margin: 0 0 12px; word-break: break-all;">' + url + '</p>';
							html += '<div style="display: flex; gap: 24px;">';
							html += '<div><strong style="color: var(--slos-warning);">' + (d.issues_count || 0) + '</strong> <span style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Issues', 'shahi-legalflowsuite' ) ); ?></span></div>';
							html += '<div><strong style="color: var(--slos-error);">' + (d.critical_count || 0) + '</strong> <span style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Critical', 'shahi-legalflowsuite' ) ); ?></span></div>';
							html += '<div><strong style="color: var(--slos-success);">' + (d.score || 100) + '%</strong> <span style="color: var(--slos-text-muted);"><?php echo esc_js( __( 'Score', 'shahi-legalflowsuite' ) ); ?></span></div>';
							html += '</div></div>';
							$resultsArea.html(html);
						} else {
							$resultsArea.html('<p style="color: var(--slos-error); padding: 20px; text-align: center;"><?php echo esc_js( __( 'Failed to scan URL. Make sure it\'s a valid page on your site.', 'shahi-legalflowsuite' ) ); ?></p>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Scan URL', 'shahi-legalflowsuite' ) ); ?>');
						alert('<?php echo esc_js( __( 'Error scanning URL. Please try again.', 'shahi-legalflowsuite' ) ); ?>');
					}
				});
			});
			
			// ============================================
			// MEDIA LIBRARY AUDIT - Audit Media Button
			// ============================================
			$('#slos-audit-media').on('click', function() {
				var $btn = $(this);
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js( __( 'Auditing...', 'shahi-legalflowsuite' ) ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_audit_media_library',
						nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>'
					},
					success: function(response) {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-images-alt2"></span> <?php echo esc_js( __( 'Audit Media Library', 'shahi-legalflowsuite' ) ); ?>');
						
						var $resultsArea = $('#slos-scan-results');
						
						if (response.success && response.data) {
							var d = response.data;
							var html = '<div style="background: var(--slos-bg-input); border-radius: 8px; padding: 20px;">';
							html += '<h4 style="color: var(--slos-text-primary); margin: 0 0 12px;"><?php echo esc_js( __( 'Media Library Audit Results', 'shahi-legalflowsuite' ) ); ?></h4>';
							html += '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px;">';
							html += '<div style="text-align: center; padding: 12px; background: var(--slos-bg-card); border-radius: 6px;"><div style="font-size: 24px; font-weight: 700; color: var(--slos-text-primary);">' + (d.total_images || 0) + '</div><div style="color: var(--slos-text-muted); font-size: 12px;"><?php echo esc_js( __( 'Total Images', 'shahi-legalflowsuite' ) ); ?></div></div>';
							html += '<div style="text-align: center; padding: 12px; background: var(--slos-bg-card); border-radius: 6px;"><div style="font-size: 24px; font-weight: 700; color: var(--slos-error);">' + (d.missing_alt || 0) + '</div><div style="color: var(--slos-text-muted); font-size: 12px;"><?php echo esc_js( __( 'Missing Alt Text', 'shahi-legalflowsuite' ) ); ?></div></div>';
							html += '<div style="text-align: center; padding: 12px; background: var(--slos-bg-card); border-radius: 6px;"><div style="font-size: 24px; font-weight: 700; color: var(--slos-success);">' + (d.with_alt || 0) + '</div><div style="color: var(--slos-text-muted); font-size: 12px;"><?php echo esc_js( __( 'With Alt Text', 'shahi-legalflowsuite' ) ); ?></div></div>';
							html += '</div>';
							
							if (d.missing_images && d.missing_images.length > 0) {
								html += '<p style="color: var(--slos-text-secondary); margin: 12px 0 8px;"><?php echo esc_js( __( 'Images Missing Alt Text:', 'shahi-legalflowsuite' ) ); ?></p>';
								html += '<div style="max-height: 200px; overflow-y: auto;">';
								d.missing_images.slice(0, 20).forEach(function(img) {
									html += '<div style="display: flex; align-items: center; gap: 12px; padding: 8px; border-bottom: 1px solid var(--slos-border);">';
									html += '<img src="' + img.thumbnail + '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">';
									html += '<span style="color: var(--slos-text-primary); flex: 1;">' + (img.title || 'Untitled') + '</span>';
									html += '<a href="' + img.edit_url + '" target="_blank" style="color: var(--slos-accent); text-decoration: none; font-size: 13px;"><?php echo esc_js( __( 'Edit', 'shahi-legalflowsuite' ) ); ?></a>';
									html += '</div>';
								});
								html += '</div>';
							}
							html += '</div>';
							$resultsArea.html(html);
						} else {
							$resultsArea.html('<p style="color: var(--slos-success); padding: 20px; text-align: center;"><span class="dashicons dashicons-yes-alt" style="font-size: 32px;"></span><br><?php echo esc_js( __( 'All images in your media library have alt text!', 'shahi-legalflowsuite' ) ); ?></p>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-images-alt2"></span> <?php echo esc_js( __( 'Audit Media Library', 'shahi-legalflowsuite' ) ); ?>');
						alert('<?php echo esc_js( __( 'Error auditing media library.', 'shahi-legalflowsuite' ) ); ?>');
					}
				});
			});
			
			// ============================================
			// STATEMENT GENERATOR - Generate Statement Button
			// ============================================
			$('#slos-generate-statement').on('click', function() {
				var $btn = $(this);
				var orgName = $('#slos-org-name').val().trim();
				var contactEmail = $('#slos-contact-email').val().trim();
				var wcagTarget = $('#slos-wcag-target').val();
				var statementDate = $('#slos-statement-date').val();
				var commitment = $('#slos-commitment').val().trim();
				
				if (!orgName) {
					alert('<?php echo esc_js( __( 'Please enter your organization name.', 'shahi-legalflowsuite' ) ); ?>');
					return;
				}
				
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js( __( 'Generating...', 'shahi-legalflowsuite' ) ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_generate_statement',
						nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>',
						org_name: orgName,
						contact_email: contactEmail,
						wcag_target: wcagTarget,
						statement_date: statementDate,
						commitment: commitment
					},
					success: function(response) {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-page"></span> <?php echo esc_js( __( 'Generate Statement', 'shahi-legalflowsuite' ) ); ?>');
						
						if (response.success && response.data && response.data.statement) {
							var $resultsArea = $('#slos-scan-results');
							var html = '<div style="background: var(--slos-bg-input); border-radius: 8px; padding: 20px;">';
							html += '<h4 style="color: var(--slos-text-primary); margin: 0 0 12px;"><?php echo esc_js( __( 'Generated Accessibility Statement', 'shahi-legalflowsuite' ) ); ?></h4>';
							html += '<div style="background: var(--slos-bg-card); padding: 16px; border-radius: 6px; max-height: 300px; overflow-y: auto; border: 1px solid var(--slos-border);">';
							html += '<div style="color: var(--slos-text-primary); line-height: 1.6;">' + response.data.statement + '</div>';
							html += '</div>';
							html += '<div style="margin-top: 12px; display: flex; gap: 12px;">';
							html += '<button type="button" class="slos-btn-secondary slos-copy-statement" data-statement="' + encodeURIComponent(response.data.statement_raw || response.data.statement) + '"><span class="dashicons dashicons-admin-page"></span> <?php echo esc_js( __( 'Copy to Clipboard', 'shahi-legalflowsuite' ) ); ?></button>';
							html += '</div></div>';
							$resultsArea.html(html);
							
							// Bind copy handler
							$('.slos-copy-statement').on('click', function() {
								var text = decodeURIComponent($(this).data('statement'));
								navigator.clipboard.writeText(text.replace(/<[^>]+>/g, ''));
								$(this).html('<span class="dashicons dashicons-yes"></span> <?php echo esc_js( __( 'Copied!', 'shahi-legalflowsuite' ) ); ?>');
								setTimeout(function() {
									$('.slos-copy-statement').html('<span class="dashicons dashicons-admin-page"></span> <?php echo esc_js( __( 'Copy to Clipboard', 'shahi-legalflowsuite' ) ); ?>');
								}, 2000);
							});
						} else {
							alert('<?php echo esc_js( __( 'Error generating statement.', 'shahi-legalflowsuite' ) ); ?>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-page"></span> <?php echo esc_js( __( 'Generate Statement', 'shahi-legalflowsuite' ) ); ?>');
						alert('<?php echo esc_js( __( 'Error generating statement.', 'shahi-legalflowsuite' ) ); ?>');
					}
				});
			});
			
			// ============================================
			// PUBLISH STATEMENT - Publish to Page Button
			// ============================================
			$('#slos-publish-statement').on('click', function() {
				var $btn = $(this);
				var orgName = $('#slos-org-name').val().trim();
				
				if (!orgName) {
					alert('<?php echo esc_js( __( 'Please fill out the form and generate a statement first.', 'shahi-legalflowsuite' ) ); ?>');
					return;
				}
				
				if (!confirm('<?php echo esc_js( __( 'This will create a new page called "Accessibility Statement". Continue?', 'shahi-legalflowsuite' ) ); ?>')) {
					return;
				}
				
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js( __( 'Publishing...', 'shahi-legalflowsuite' ) ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_publish_statement',
						nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>',
						org_name: $('#slos-org-name').val(),
						contact_email: $('#slos-contact-email').val(),
						wcag_target: $('#slos-wcag-target').val(),
						statement_date: $('#slos-statement-date').val(),
						commitment: $('#slos-commitment').val()
					},
					success: function(response) {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> <?php echo esc_js( __( 'Publish to Page', 'shahi-legalflowsuite' ) ); ?>');
						
						if (response.success && response.data && response.data.page_url) {
							alert('<?php echo esc_js( __( 'Accessibility Statement page created!', 'shahi-legalflowsuite' ) ); ?>');
							window.open(response.data.page_url, '_blank');
						} else {
							alert(response.data || '<?php echo esc_js( __( 'Error publishing statement.', 'shahi-legalflowsuite' ) ); ?>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> <?php echo esc_js( __( 'Publish to Page', 'shahi-legalflowsuite' ) ); ?>');
						alert('<?php echo esc_js( __( 'Error publishing statement.', 'shahi-legalflowsuite' ) ); ?>');
					}
				});
			});
			
			// Spinning animation for loading states
			$('<style>.slos-spin { animation: slos-spin 1s linear infinite; } @keyframes slos-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>').appendTo('head');
			
			// Modal close handlers
			$('.slos-modal-close, .slos-modal-overlay').on('click', function() {
				$('.slos-modal').hide();
			});
			
			// Widget toggle handler (moved from Dashboard)
			$('#slos-widget-toggle').on('change', function() {
				var enabled = $(this).is(':checked');
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_toggle_widget',
						nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
						enabled: enabled
					}
				});
			});
			
			// Fix All Issues button handler (from Pages Requiring Attention)
			// This is now handled by slos-scanner-admin.js with progress modal
			// The handler in slos-scanner-admin.js listens for .slos-fix-all-btn clicks
			// and triggers the SLOSAutoFixProgress modal
			
			// View Details button handler (from Pages Requiring Attention)
			$('.slos-view-details-btn').on('click', function() {
				var postId = $(this).data('post-id');
				if (!postId) return;
				
				// Show loading
				$('#slos-modal-body').html('<div style="text-align: center; padding: 40px;"><span class="dashicons dashicons-update slos-spin" style="font-size: 48px; color: var(--slos-accent);"></span><p style="margin-top: 16px; color: var(--slos-text-muted);"><?php echo esc_js(__('Loading scan details...', 'shahi-legalflowsuite')); ?></p></div>');
				$('#slos-scan-modal').show();
				
				// Load scan details via AJAX
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_get_scan_details',
						nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
						post_id: postId
					},
					success: function(response) {
						if (response.success && response.data) {
							$('#slos-modal-body').html(response.data.html || '<p><?php echo esc_js(__('No details available', 'shahi-legalflowsuite')); ?></p>');
						} else {
							$('#slos-modal-body').html('<p style="color: var(--slos-error);"><?php echo esc_js(__('Error loading details', 'shahi-legalflowsuite')); ?></p>');
						}
					},
					error: function() {
						$('#slos-modal-body').html('<p style="color: var(--slos-error);"><?php echo esc_js(__('Error loading details', 'shahi-legalflowsuite')); ?></p>');
					}
				});
			});
			
			// Autofix checkbox handler (from Pages Requiring Attention)
			$('.slos-autofix-checkbox').on('change', function() {
				var postId = $(this).data('post-id');
				var enabled = $(this).is(':checked');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_toggle_autofix',
						nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
						post_id: postId,
						enabled: enabled
					}
				});
			});
			
			// Rollback button handler (from Pages Requiring Attention)
			$('.slos-rollback-btn').on('click', function() {
				var postId = $(this).data('post-id');
				if (!confirm('<?php echo esc_js(__('Are you sure you want to rollback recent fixes? This cannot be undone.', 'shahi-legalflowsuite')); ?>')) {
					return;
				}
				
				var $btn = $(this);
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js(__('Rolling back...', 'shahi-legalflowsuite')); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_rollback_fixes',
						nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
						post_id: postId
					},
					success: function(response) {
						if (response.success) {
							$btn.hide();
							alert('<?php echo esc_js(__('Fixes rolled back successfully!', 'shahi-legalflowsuite')); ?>');
							location.reload();
						} else {
							$btn.prop('disabled', false).html('<span class="dashicons dashicons-undo"></span> <?php echo esc_js(__('Rollback', 'shahi-legalflowsuite')); ?>');
							alert(response.data || '<?php echo esc_js(__('Error rolling back fixes', 'shahi-legalflowsuite')); ?>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-undo"></span> <?php echo esc_js(__('Rollback', 'shahi-legalflowsuite')); ?>');
						alert('<?php echo esc_js(__('Error rolling back fixes', 'shahi-legalflowsuite')); ?>');
					}
				});
			});
			
			// Scanner Configuration Form Handler
			$('#slos-scanner-config-form').on('submit', function(e) {
				e.preventDefault();
				
				var $btn = $('#slos-save-config');
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js(__('Saving...', 'shahi-legalflowsuite')); ?>');
				
				var formData = {
					action: 'slos_save_scanner_config',
					nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
					wcag_level: $('#slos-wcag-level').val(),
					scan_post_types: $('input[name="scan_post_types[]"]:checked').map(function() { return $(this).val(); }).get(),
					active_checkers: $('input[name="active_checkers[]"]:checked').map(function() { return $(this).val(); }).get()
				};
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					success: function(response) {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> <?php echo esc_js(__('Save Configuration', 'shahi-legalflowsuite')); ?>');
						if (response.success) {
							alert('<?php echo esc_js(__('Configuration saved successfully!', 'shahi-legalflowsuite')); ?>');
						} else {
							alert(response.data || '<?php echo esc_js(__('Error saving configuration', 'shahi-legalflowsuite')); ?>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> <?php echo esc_js(__('Save Configuration', 'shahi-legalflowsuite')); ?>');
						alert('<?php echo esc_js(__('Error saving configuration', 'shahi-legalflowsuite')); ?>');
					}
				});
			});
			
			// Reset Configuration Handler
			$('#slos-reset-config').on('click', function() {
				if (!confirm('<?php echo esc_js(__('Reset to default configuration? This will restore all default settings.', 'shahi-legalflowsuite')); ?>')) {
					return;
				}
				
				// Reset to defaults
				$('#slos-wcag-level').val('AA');
				$('input[name="scan_post_types[]"]').each(function() {
					$(this).prop('checked', ['post', 'page'].indexOf($(this).val()) !== -1);
				});
				$('input[name="active_checkers[]"]').prop('checked', true);
			});
			
			// Export Button Handlers (moved from Dashboard, already exist)
			$('.slos-export-btn').on('click', function() {
				var format = $(this).data('format');
				var $btn = $(this);
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js(__('Generating...', 'shahi-legalflowsuite')); ?>');
				
				// Trigger export download
				window.location.href = ajaxurl + '?action=slos_export_report&format=' + format + '&nonce=<?php echo wp_create_nonce('slos_export_nonce'); ?>';
				
				// Re-enable button after delay
				setTimeout(function() {
					$btn.prop('disabled', false).html('<span class="dashicons dashicons-download"></span> <?php echo esc_js(__('Export', 'shahi-legalflowsuite')); ?> ' + format.toUpperCase());
				}, 2000);
			});
			
			// Schedule Email Reports Handler
			$('#slos-schedule-email').on('click', function() {
				var email = prompt('<?php echo esc_js(__('Enter email address for scheduled reports:', 'shahi-legalflowsuite')); ?>');
				if (!email) return;
				
				var frequency = prompt('<?php echo esc_js(__('Enter frequency (weekly or monthly):', 'shahi-legalflowsuite')); ?>', 'weekly');
				if (!frequency) return;
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_schedule_email_report',
						nonce: '<?php echo wp_create_nonce('slos_scanner_nonce'); ?>',
						email: email,
						frequency: frequency
					},
					success: function(response) {
						if (response.success) {
							alert('<?php echo esc_js(__('Email reports scheduled successfully!', 'shahi-legalflowsuite')); ?>');
						} else {
							alert(response.data || '<?php echo esc_js(__('Error scheduling reports', 'shahi-legalflowsuite')); ?>');
						}
					},
					error: function() {
						alert('<?php echo esc_js(__('Error scheduling reports', 'shahi-legalflowsuite')); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}
	
	/**
	 * Get letter grade from score
	 *
	 * @param int $score Accessibility score
	 * @return string Letter grade
	 */
	private function get_grade_from_score( $score ) {
		if ( $score >= 90 ) return 'A';
		if ( $score >= 80 ) return 'B';
		if ( $score >= 70 ) return 'C';
		if ( $score >= 60 ) return 'D';
		return 'F';
	}
	
	/**
	 * Get grade label
	 *
	 * @param string $grade Letter grade
	 * @return string Grade label
	 */
	private function get_grade_label( $grade ) {
		$labels = array(
			'A' => __( 'Excellent', 'shahi-legalflowsuite' ),
			'B' => __( 'Good', 'shahi-legalflowsuite' ),
			'C' => __( 'Fair', 'shahi-legalflowsuite' ),
			'D' => __( 'Poor', 'shahi-legalflowsuite' ),
			'F' => __( 'Needs Work', 'shahi-legalflowsuite' ),
		);
		return isset( $labels[ $grade ] ) ? $labels[ $grade ] : $labels['F'];
	}
	
	/**
	 * Get grade description
	 *
	 * @param string $grade Letter grade
	 * @return string Grade description
	 */
	private function get_grade_description( $grade ) {
		$descriptions = array(
			'A' => __( 'Meets WCAG 2.2 AA standards', 'shahi-legalflowsuite' ),
			'B' => __( 'Minor issues to address', 'shahi-legalflowsuite' ),
			'C' => __( 'Several issues need attention', 'shahi-legalflowsuite' ),
			'D' => __( 'Significant accessibility gaps', 'shahi-legalflowsuite' ),
			'F' => __( 'Critical issues present', 'shahi-legalflowsuite' ),
		);
		return isset( $descriptions[ $grade ] ) ? $descriptions[ $grade ] : $descriptions['F'];
	}
}

