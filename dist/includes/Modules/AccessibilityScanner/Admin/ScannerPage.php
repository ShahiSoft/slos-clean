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
		// Initialize registry if needed
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
			
			@media (max-width: 1200px) {
				.slos-tools-grid {
					grid-template-columns: 1fr;
				}
			}
		</style>
		
		<div class="slos-tools-container">
			<div class="slos-tools-grid">
				
				<!-- Card 1: WCAG Compliance Status -->
				<div class="slos-tools-card">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'WCAG Compliance Status', 'shahi-legalflowsuite' ); ?>
						</h3>
						<span class="badge"><?php echo esc_html( 'WCAG 2.2 ' . $wcag_level ); ?></span>
					</div>
					<div class="slos-card-body">
						<div class="slos-score-display">
							<div class="slos-score-circle grade-<?php echo esc_attr( strtolower( $grade ) ); ?>">
								<svg viewBox="0 0 140 140">
									<circle class="bg-circle" cx="70" cy="70" r="60"></circle>
									<circle class="score-circle" cx="70" cy="70" r="60" 
										stroke-dasharray="377" 
										stroke-dashoffset="<?php echo esc_attr( 377 - ( 377 * $score / 100 ) ); ?>">
									</circle>
								</svg>
								<div class="slos-score-value">
									<div class="number"><?php echo esc_html( $score ); ?></div>
									<div class="label"><?php esc_html_e( 'Score', 'shahi-legalflowsuite' ); ?></div>
								</div>
							</div>
							<div class="slos-score-details">
								<div class="slos-score-grade">
									<div class="slos-grade-badge grade-<?php echo esc_attr( strtolower( $grade ) ); ?>">
										<?php echo esc_html( $grade ); ?>
									</div>
									<div class="slos-grade-info">
										<div class="grade-label"><?php echo esc_html( $this->get_grade_label( $grade ) ); ?></div>
										<div class="grade-desc"><?php echo esc_html( $this->get_grade_description( $grade ) ); ?></div>
									</div>
								</div>
								<div class="slos-compliance-badges">
									<span class="slos-compliance-badge <?php echo $score >= 80 ? 'active' : ''; ?>">
										<span class="dashicons dashicons-<?php echo $score >= 80 ? 'yes' : 'minus'; ?>"></span>
										ADA
									</span>
									<span class="slos-compliance-badge <?php echo $score >= 80 ? 'active' : ''; ?>">
										<span class="dashicons dashicons-<?php echo $score >= 80 ? 'yes' : 'minus'; ?>"></span>
										Section 508
									</span>
									<span class="slos-compliance-badge <?php echo $score >= 80 ? 'active' : ''; ?>">
										<span class="dashicons dashicons-<?php echo $score >= 80 ? 'yes' : 'minus'; ?>"></span>
										EAA
									</span>
								</div>
							</div>
						</div>
						<div class="slos-quick-stats">
							<div class="slos-quick-stat">
								<div class="value"><?php echo esc_html( $total_issues ); ?></div>
								<div class="label"><?php esc_html_e( 'Total Issues', 'shahi-legalflowsuite' ); ?></div>
							</div>
							<div class="slos-quick-stat">
								<div class="value critical"><?php echo esc_html( $critical_issues ); ?></div>
								<div class="label"><?php esc_html_e( 'Critical', 'shahi-legalflowsuite' ); ?></div>
							</div>
							<div class="slos-quick-stat">
								<div class="value"><?php echo esc_html( max( 0, $total_issues - $critical_issues ) ); ?></div>
								<div class="label"><?php esc_html_e( 'Auto-Fixable', 'shahi-legalflowsuite' ); ?></div>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Card 2: Quick Accessibility Checks -->
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
								<p><?php esc_html_e( 'Check if two colors meet WCAG contrast requirements.', 'shahi-legalflowsuite' ); ?></p>
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
								<p><?php esc_html_e( 'Check the reading level of your content.', 'shahi-legalflowsuite' ); ?></p>
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
								<p><?php esc_html_e( 'Check if link text is descriptive enough.', 'shahi-legalflowsuite' ); ?></p>
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
				
				<!-- Card 3: Content Scanner -->
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
							<p><?php esc_html_e( 'Scan all posts, pages, and custom post types for accessibility issues.', 'shahi-legalflowsuite' ); ?></p>
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
							<p><?php esc_html_e( 'Scan a specific page or URL for accessibility issues.', 'shahi-legalflowsuite' ); ?></p>
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
							<p><?php esc_html_e( 'Find images missing alt text in your media library.', 'shahi-legalflowsuite' ); ?></p>
							<div class="slos-scanner-controls">
								<button type="button" class="slos-btn-secondary" id="slos-audit-media">
									<span class="dashicons dashicons-images-alt2"></span>
									<?php esc_html_e( 'Audit Media Library', 'shahi-legalflowsuite' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Card 4: Accessibility Statement Generator -->
				<div class="slos-tools-card">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-media-document"></span>
							<?php esc_html_e( 'Accessibility Statement Generator', 'shahi-legalflowsuite' ); ?>
						</h3>
					</div>
					<div class="slos-card-body">
						<div class="slos-statement-form">
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
				
				<!-- Card 5: Scan Results (Full Width) -->
				<div class="slos-tools-card full-width">
					<div class="slos-card-header">
						<h3>
							<span class="dashicons dashicons-list-view"></span>
							<?php esc_html_e( 'Scan Results', 'shahi-legalflowsuite' ); ?>
						</h3>
					</div>
					<div class="slos-card-body">
						<div id="slos-scan-results">
							<p style="color: var(--slos-text-muted); text-align: center; padding: 40px 0;">
								<span class="dashicons dashicons-search" style="font-size: 48px; width: 48px; height: 48px; display: block; margin: 0 auto 16px;"></span>
								<?php esc_html_e( 'No scan results yet. Run a scan to see accessibility issues.', 'shahi-legalflowsuite' ); ?>
							</p>
						</div>
					</div>
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
			// FULL SITE SCAN - Start Full Scan Button
			// ============================================
			$('#slos-start-scan').on('click', function() {
				var $btn = $(this);
				var $progressWrapper = $('#slos-scan-progress-wrapper');
				var $progressBar = $('#slos-progress-bar');
				var $scanStatus = $('#slos-scan-status');
				var $scanProgress = $('#slos-scan-progress');
				var $resultsArea = $('#slos-scan-results');
				
				$btn.prop('disabled', true).html('<span class="dashicons dashicons-update slos-spin"></span> <?php echo esc_js( __( 'Scanning...', 'shahi-legalflowsuite' ) ); ?>');
				$progressWrapper.addClass('show');
				$scanStatus.text('<?php echo esc_js( __( 'Fetching pages to scan...', 'shahi-legalflowsuite' ) ); ?>');
				
				// Fetch all posts to scan
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'slos_get_posts_to_scan',
						nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>'
					},
					success: function(response) {
						if (!response.success || !response.data || response.data.length === 0) {
							$scanStatus.text('<?php echo esc_js( __( 'No pages found to scan.', 'shahi-legalflowsuite' ) ); ?>');
							$btn.prop('disabled', false).html('<span class="dashicons dashicons-controls-play"></span> <?php echo esc_js( __( 'Start Full Scan', 'shahi-legalflowsuite' ) ); ?>');
							return;
						}
						
						var pages = response.data;
						var total = pages.length;
						var completed = 0;
						var issues = 0;
						var critical = 0;
						var results = [];
						
						$scanStatus.text('<?php echo esc_js( __( 'Scanning', 'shahi-legalflowsuite' ) ); ?> 0 / ' + total + ' <?php echo esc_js( __( 'pages...', 'shahi-legalflowsuite' ) ); ?>');
						
						// Scan pages sequentially with batch processing
						var BATCH_SIZE = 3;
						var queue = pages.slice();
						var active = 0;
						
						function processNext() {
							while (active < BATCH_SIZE && queue.length > 0) {
								var page = queue.shift();
								active++;
								scanPage(page);
							}
						}
						
						function scanPage(page) {
							$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
									action: 'slos_scan_single_post',
									nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>',
									post_id: page.id
								},
								success: function(res) {
									if (res.success && res.data) {
										issues += res.data.issues_count || 0;
										critical += res.data.critical_count || 0;
										results.push({
											post_id: page.id,
											title: page.title,
											issues: res.data.issues_count || 0,
											critical: res.data.critical_count || 0,
											score: res.data.score || 100
										});
									}
								},
								complete: function() {
									completed++;
									active--;
									var pct = Math.round((completed / total) * 100);
									$progressBar.css('width', pct + '%');
									$scanProgress.text(pct + '%');
									$scanStatus.text('<?php echo esc_js( __( 'Scanning', 'shahi-legalflowsuite' ) ); ?> ' + completed + ' / ' + total + ' <?php echo esc_js( __( 'pages...', 'shahi-legalflowsuite' ) ); ?>');
									
									if (completed >= total) {
										finishScan();
									} else {
										processNext();
									}
								}
							});
						}
						
						function finishScan() {
							// Consolidate results
							$.ajax({
								url: ajaxurl,
								type: 'POST',
								data: {
									action: 'slos_consolidate_scan_results',
									nonce: '<?php echo wp_create_nonce( 'slos_scanner_nonce' ); ?>'
								},
								complete: function() {
									$scanStatus.text('<?php echo esc_js( __( 'Scan complete!', 'shahi-legalflowsuite' ) ); ?> ' + issues + ' <?php echo esc_js( __( 'issues found.', 'shahi-legalflowsuite' ) ); ?>');
									$btn.prop('disabled', false).html('<span class="dashicons dashicons-controls-play"></span> <?php echo esc_js( __( 'Start Full Scan', 'shahi-legalflowsuite' ) ); ?>');
									
									// Display results
									displayScanResults(results, issues, critical);
								}
							});
						}
						
						processNext();
					},
					error: function() {
						$scanStatus.text('<?php echo esc_js( __( 'Error starting scan. Please try again.', 'shahi-legalflowsuite' ) ); ?>');
						$btn.prop('disabled', false).html('<span class="dashicons dashicons-controls-play"></span> <?php echo esc_js( __( 'Start Full Scan', 'shahi-legalflowsuite' ) ); ?>');
					}
				});
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

