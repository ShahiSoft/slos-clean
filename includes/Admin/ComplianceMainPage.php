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
	}

	/**
	 * Get available tabs with icons
	 *
	 * @since 3.0.3
	 * @return array Tab configuration
	 */
	private function get_tabs() {
		return array(
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
		);
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

		// Calculate multi-dimensional readiness score
		$readiness = $this->score_calculator->calculate();

		// Calculate legacy acceptance-based score for backward compatibility
		$legacy_acceptance_score = $total > 0 ? round( ( $accepted / $total ) * 100 ) : 100;

		// Get legal documents status
		$legal_docs_stats = $this->get_legal_docs_stats();

		return array(
			// Consent activity metrics
			'total'            => $total,
			'accepted'         => $accepted,
			'rejected'         => $rejected,
			'withdrawn'        => $withdrawn,
			'pending'          => $pending,
			'by_type'          => $stats['by_type'] ?? array(),
			'acceptance_rate'  => $total > 0 ? round( ( $accepted / $total ) * 100, 1 ) : 0,
			'rejection_rate'   => $total > 0 ? round( ( $rejected / $total ) * 100, 1 ) : 0,

			// Multi-dimensional readiness score (new)
			'compliance_score' => $readiness['score'],
			'grade'            => $readiness['grade'],
			'grade_class'      => $readiness['grade_class'],
			'grade_text'       => $readiness['label'],
			'dimensions'       => $readiness['dimensions'],

			// Legacy metrics
			'legacy_acceptance_score' => $legacy_acceptance_score,

			// Legal documents status (3.1.1)
			'legal_docs' => $legal_docs_stats,
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
		$cards = $hub_service->get_document_cards();

		// Focus on core compliance documents
		$required_docs = array( 'cookie-policy', 'privacy-policy', 'accessibility-statement' );
		$total = count( $required_docs );
		$published = 0;
		$stale = 0;

		$docs_status = array();

		foreach ( $cards as $card ) {
			if ( in_array( $card['id'], $required_docs, true ) ) {
				$is_published = ( $card['status'] ?? 'not_generated' ) === 'published';
				$is_stale = ! empty( $card['doc_id'] ) && get_post_meta( $card['doc_id'], '_slos_needs_regeneration', true );

				if ( $is_published ) {
					$published++;
				}
				if ( $is_stale ) {
					$stale++;
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
	 * Get recent consent activity
	 *
	 * @since 3.0.3
	 * @param int $limit Number of records
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
		// Check capability
		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		// Get current tab
		$this->current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';

		// Validate tab
		$valid_tabs = array_keys( $this->get_tabs() );
		if ( ! in_array( $this->current_tab, $valid_tabs, true ) ) {
			$this->current_tab = 'dashboard';
		}

		// Gather data for templates
		$stats           = $this->get_dashboard_stats();
		$recent_activity = $this->get_recent_activity( 10 );
		$tabs            = $this->get_tabs();
		$current_tab     = $this->current_tab;
		$current_url     = admin_url( 'admin.php?page=slos-compliance' );

		// Include main template
		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/main.php';
	}

	/**
	 * Render tab content based on current tab
	 *
	 * @since 3.0.3
	 * @param array $stats Dashboard statistics
	 * @param array $recent_activity Recent consent activity
	 * @return void
	 */
	public function render_tab_content( $stats, $recent_activity ) {
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

			default:
				include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/compliance/tabs/dashboard.php';
		}
	}
}
