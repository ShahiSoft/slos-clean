<?php
/**
 * Compliance Main Page (Tabbed Interface) - V3 Design
 *
 * Premium command center for all consent and compliance functionality.
 * Uses V3 dark theme design system for consistency with Accessibility module.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.2
 * @updated    3.0.3
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Services\Consent_Service;
use ShahiLegalFlowSuite\Services\Consent_Audit_Logger;
use ShahiLegalFlowSuite\Services\Compliance_Score_Calculator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compliance Main Page Class
 *
 * Provides modern tabbed interface for consent and compliance management.
 *
 * @since 3.0.2
 */
class ComplianceMainPage {

	/**
	 * Current active tab
	 *
	 * @var string
	 */
	private $current_tab = 'dashboard';

	/**
	 * Consent service instance
	 *
	 * @var Consent_Service
	 */
	private $consent_service;

	/**
	 * Audit logger instance
	 *
	 * @var Consent_Audit_Logger
	 */
	private $audit_logger;

	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Score calculator instance
	 *
	 * @var Compliance_Score_Calculator
	 */
	private $score_calculator;

	/**
	 * Constructor
	 *
	 * @since 3.0.2
	 */
	public function __construct() {
		$this->consent_service  = new Consent_Service();
		$this->audit_logger     = new Consent_Audit_Logger();
		$this->settings         = new Settings();
		$this->score_calculator = new Compliance_Score_Calculator();

		// Enqueue config sync assets on compliance page.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_config_sync_assets' ) );
	}

	/**
	 * Enqueue config sync assets
	 *
	 * @since 3.1.1
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_config_sync_assets( $hook ) {
		// Check if config sync is dormant.
		if ( defined( 'SLOS_DORMANT_COMPLIANCE_FEATURES' ) &&
			is_array( SLOS_DORMANT_COMPLIANCE_FEATURES ) &&
			in_array( 'config', SLOS_DORMANT_COMPLIANCE_FEATURES, true ) ) {
			return;
		}

		// Only load on compliance page with config tab.
		if ( 'toplevel_page_slos-compliance' !== $hook ) {
			return;
		}

		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selection.

		if ( 'config' !== $current_tab ) {
			return;
		}

		// Enqueue CSS.
		wp_enqueue_style(
			'slos-config-sync',
			SHAHI_LEGALFLOWSUITE_URL . 'assets/css/config-sync.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		// Enqueue JavaScript.
		wp_enqueue_script(
			'slos-config-sync',
			SHAHI_LEGALFLOWSUITE_URL . 'assets/js/config-sync.js',
			array( 'jquery', 'wp-api' ),
			SHAHI_LEGALFLOWSUITE_VERSION,
			true
		);
	}

	/**
	 * Get available tabs with icons
	 *
	 * @since 3.0.3
	 * @return array Tab configuration
	 */
	private function get_tabs() {
		$tabs = array(
			'dashboard' => array(
				'label' => __( 'Dashboard', 'shahi-legalflowsuite' ),
				'icon'  => 'dashicons-chart-area',
			),
			'records'   => array(
				'label' => __( 'Consent Records', 'shahi-legalflowsuite' ),
				'icon'  => 'dashicons-list-view',
			),
			'audit'     => array(
				'label' => __( 'Audit Logs', 'shahi-legalflowsuite' ),
				'icon'  => 'dashicons-visibility',
			),
			'cookies'   => array(
				'label' => __( 'Cookie Scanner', 'shahi-legalflowsuite' ),
				'icon'  => 'dashicons-admin-plugins',
			),
			'banner'    => array(
				'label' => __( 'Banner Config', 'shahi-legalflowsuite' ),
				'icon'  => 'dashicons-format-image',
			),
			'geo'       => array(
				'label' => __( 'Geo Rules', 'shahi-legalflowsuite' ),
				'icon'  => 'dashicons-admin-site-alt3',
			),
			'config'    => array(
				'label' => __( 'Config Sync', 'shahi-legalflowsuite' ),
				'icon'  => 'dashicons-cloud',
			),
		);

		// Filter out dormant tabs.
		if ( defined( 'SLOS_DORMANT_COMPLIANCE_FEATURES' ) && is_array( SLOS_DORMANT_COMPLIANCE_FEATURES ) ) {
			foreach ( SLOS_DORMANT_COMPLIANCE_FEATURES as $dormant_tab ) {
				unset( $tabs[ $dormant_tab ] );
			}
		}

		return $tabs;
	}

	/**
	 * Get dashboard statistics
	 *
	 * @since 3.0.3
	 * @updated 3.1.0 - Added multi-dimensional readiness score
	 * @updated 3.1.1 - Added legal documents status
	 * @return array Dashboard stats
	 */
	private function get_dashboard_stats() {
		$stats = $this->consent_service->get_statistics();

		$total     = array_sum( $stats['by_status'] ?? array() );
		$accepted  = $stats['by_status']['accepted'] ?? 0;
		$rejected  = $stats['by_status']['rejected'] ?? 0;
		$withdrawn = $stats['by_status']['withdrawn'] ?? 0;
		$pending   = $stats['by_status']['pending'] ?? 0;

		// Calculate multi-dimensional readiness score.
		$readiness = $this->score_calculator->calculate();

		// Calculate legacy acceptance-based score for backward compatibility.
		$legacy_acceptance_score = $total > 0 ? round( ( $accepted / $total ) * 100 ) : 100;

		// Get legal documents status.
		$legal_docs_stats = $this->get_legal_docs_stats();

		return array(
			// Consent activity metrics.
			'total'                   => $total,
			'accepted'                => $accepted,
			'rejected'                => $rejected,
			'withdrawn'               => $withdrawn,
			'pending'                 => $pending,
			'by_type'                 => $stats['by_type'] ?? array(),
			'acceptance_rate'         => $total > 0 ? round( ( $accepted / $total ) * 100, 1 ) : 0,
			'rejection_rate'          => $total > 0 ? round( ( $rejected / $total ) * 100, 1 ) : 0,

			// Multi-dimensional readiness score (new).
			'compliance_score'        => $readiness['score'],
			'grade'                   => $readiness['grade'],
			'grade_class'             => $readiness['grade_class'],
			'grade_text'              => $readiness['label'],
			'dimensions'              => $readiness['dimensions'],

			// Legacy metrics.
			'legacy_acceptance_score' => $legacy_acceptance_score,

			// Legal documents status (3.1.1).
			'legal_docs'              => $legal_docs_stats,
		);
	}

	/**
	 * Get legal documents statistics
	 *
	 * @since 3.1.1
	 * @return array Legal documents stats
	 */
	private function get_legal_docs_stats() {
		$hub_service = new \ShahiLegalFlowSuite\Services\Document_Hub_Service();
		$cards       = $hub_service->get_document_cards();

		// Focus on core compliance documents.
		$required_docs = array( 'cookie-policy', 'privacy-policy', 'accessibility-statement' );
		$total         = count( $required_docs );
		$published     = 0;
		$stale         = 0;

		$docs_status = array();

		foreach ( $cards as $card ) {
			if ( in_array( $card['id'], $required_docs, true ) ) {
				$is_published = ( $card['status'] ?? 'not_generated' ) === 'published';
				$is_stale     = ! empty( $card['doc_id'] ) && get_post_meta( $card['doc_id'], '_slos_needs_regeneration', true );

				if ( $is_published ) {
					++$published;
				}
				if ( $is_stale ) {
					++$stale;
				}

				$docs_status[ $card['id'] ] = array(
					'status' => $card['status'],
					'stale'  => $is_stale,
					'title'  => $card['title'],
				);
			}
		}

		return array(
			'total'      => $total,
			'published'  => $published,
			'stale'      => $stale,
			'pending'    => $total - $published,
			'percentage' => $total > 0 ? round( ( $published / $total ) * 100 ) : 0,
			'docs'       => $docs_status,
		);
	}

	/**
	 * Get Operations Dashboard Statistics
	 *
	 * Aggregates data from all compliance modules:
	 * - Consent statistics
	 * - Cookie scanner status
	 * - DSR queue metrics
	 * - Accessibility issues
	 * - Overall Ops Readiness Score
	 *
	 * @since 3.1.1 Phase 2.1
	 * @return array {
	 *     @type array  $consent       Consent module statistics
	 *     @type array  $cookies       Cookie scanner statistics
	 *     @type array  $dsr           DSR operations statistics
	 *     @type array  $accessibility Accessibility scanner statistics
	 *     @type int    $ops_score     Overall ops readiness score (0-100)
	 *     @type string $ops_grade     Letter grade for ops readiness
	 *     @type string $ops_label     Human-readable label
	 *     @type array  $dimensions    All 8 dimension scores
	 * }
	 */
	private function get_ops_dashboard_stats() {
		// 1. Get Consent statistics (from existing method).
		$consent_stats = $this->consent_service->get_statistics();
		$consent_total = array_sum( $consent_stats['by_status'] ?? array() );

		$consent = array(
			'total_consents'  => $consent_total,
			'accepted'        => $consent_stats['by_status']['accepted'] ?? 0,
			'rejected'        => $consent_stats['by_status']['rejected'] ?? 0,
			'withdrawn'       => $consent_stats['by_status']['withdrawn'] ?? 0,
			'pending'         => $consent_stats['by_status']['pending'] ?? 0,
			'acceptance_rate' => $consent_total > 0 ? round( ( ( $consent_stats['by_status']['accepted'] ?? 0 ) / $consent_total ) * 100, 1 ) : 0,
			'by_type'         => $consent_stats['by_type'] ?? array(),
			'recent_consents' => $consent_stats['recent_consents'] ?? array(),
		);

		// 2. Get Cookie scanner statistics.
		$cookie_inventory = get_option( 'slos_cookie_inventory', array() );
		$detected_cookies = get_option( 'slos_detected_cookies', array() );
		$cookies_data     = ! empty( $cookie_inventory ) ? $cookie_inventory : $detected_cookies;
		$cookie_scan_time = get_option( 'slos_cookie_scan_time', null );

		$total_cookies         = count( $cookies_data );
		$categorized_cookies   = 0;
		$uncategorized_cookies = 0;

		foreach ( $cookies_data as $cookie ) {
			$category = isset( $cookie['category'] ) ? strtolower( $cookie['category'] ) : 'unknown';
			$status   = isset( $cookie['status'] ) ? strtolower( $cookie['status'] ) : '';

			if ( ! empty( $category ) && 'unknown' !== $category && 'uncategorized' !== $status ) {
				++$categorized_cookies;
			} else {
				++$uncategorized_cookies;
			}
		}

		$cookies = array(
			'total_cookies'         => $total_cookies,
			'categorized_cookies'   => $categorized_cookies,
			'uncategorized_cookies' => $uncategorized_cookies,
			'last_scan_time'        => $cookie_scan_time,
			'categorization_rate'   => $total_cookies > 0 ? round( ( $categorized_cookies / $total_cookies ) * 100, 1 ) : 0,
		);

		// 3. Get DSR statistics.
		$dsr_service = new \ShahiLegalFlowSuite\Services\DSR_Service();
		$dsr         = $dsr_service->get_ops_statistics();

		// 4. Get Accessibility statistics.
		$accessibility = array(
			'total_issues'        => 0,
			'critical_issues'     => 0,
			'warning_issues'      => 0,
			'notice_issues'       => 0,
			'pages_scanned'       => 0,
			'accessibility_score' => 0,
			'pass_rate'           => 100.0,
			'last_scan_time'      => '',
			'hours_since_scan'    => null,
			'scan_freshness'      => 'never',
			'by_severity'         => array(
				'critical' => 0,
				'warning'  => 0,
				'notice'   => 0,
			),
		);

		$module_manager       = \ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();
		$accessibility_module = $module_manager ? $module_manager->get_module( 'accessibility-scanner' ) : null;

		if ( ! $accessibility_module && class_exists( '\\ShahiLegalFlowSuite\\Modules\\AccessibilityScanner\\AccessibilityScanner' ) ) {
			$accessibility_module = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\AccessibilityScanner();
		}

		if ( $accessibility_module && method_exists( $accessibility_module, 'get_ops_statistics' ) ) {
			$accessibility = $accessibility_module->get_ops_statistics();
		}

		// 5. Get Consent UX Checker statistics (Phase 2.3).
		$consent_ux_checker = new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\ConsentUxChecker();
		$consent_ux         = $consent_ux_checker->get_summary_stats();

		// 6. Calculate overall Ops Readiness Score.
		$ops_readiness = $this->score_calculator->calculate_ops_readiness();

		return array(
			'consent'         => $consent,
			'cookies'         => $cookies,
			'dsr'             => $dsr,
			'accessibility'   => $accessibility,
			'consent_ux'      => $consent_ux,
			'ops_score'       => $ops_readiness['score'],
			'ops_grade'       => $ops_readiness['grade'],
			'ops_grade_class' => $ops_readiness['grade_class'],
			'ops_label'       => $ops_readiness['label'],
			'dimensions'      => $ops_readiness['dimensions'],
		);
	}

	/**
	 * Get recent consent activity
	 *
	 * @since 3.0.3
	 * @param int $limit Number of records.
	 * @return array Recent activity
	 */
	private function get_recent_activity( $limit = 10 ) {
		return $this->consent_service->get_recent_consents( $limit );
	}

	/**
	 * Render the main page
	 *
	 * @since 3.0.2
	 * @return void
	 */
	public function render() {
		// Check capability.
		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		// Get current tab.
		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selection.

		// Validate tab.
		$valid_tabs = array_keys( $this->get_tabs() );
		if ( ! in_array( $this->current_tab, $valid_tabs, true ) ) {
			$this->current_tab = 'dashboard';
		}

		// Gather data for templates..
		$stats           = $this->get_dashboard_stats();
		$ops_stats       = $this->get_ops_dashboard_stats();
		$recent_activity = $this->get_recent_activity( 10 );
		$tabs            = $this->get_tabs();
		$current_tab     = $this->current_tab;
		$current_url     = admin_url( 'admin.php?page=slos-compliance' );

		// Include main template..
		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/main.php';
	}

	/**
	 * Render tab content based on current tab
	 *
	 * @since 3.0.3
	 * @param array $stats Dashboard statistics.
	 * @param array $recent_activity Recent consent activity.
	 * @return void
	 */
	public function render_tab_content( $stats, $recent_activity ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- Passed to included templates.
		// Check if current tab is dormant..
		if ( defined( 'SLOS_DORMANT_COMPLIANCE_FEATURES' ) &&
			is_array( SLOS_DORMANT_COMPLIANCE_FEATURES ) &&
			in_array( $this->current_tab, SLOS_DORMANT_COMPLIANCE_FEATURES, true ) ) {
			// Redirect to dashboard if accessing dormant tab..
			include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/dashboard.php';
			return;
		}

		switch ( $this->current_tab ) {
			case 'dashboard':
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/dashboard.php';
				break;

			case 'records':
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/records.php';
				break;

			case 'audit':
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/audit-logs.php';
				break;

			case 'cookies':
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/cookie-scanner.php';
				break;

			case 'banner':
				$settings = $this->settings->get_settings();
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/banner-config.php';
				break;

			case 'geo':
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/geo-rules.php';
				break;

			case 'config':
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/config-sync.php';
				break;

			default:
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/dashboard.php';
		}
	}
}
