<?php
/**
 * Compliance Main Page (Tabbed Interface) - V3 Design
 *
 * Premium command center for all consent and compliance functionality.
 * Uses V3 dark theme design system for consistency with Accessibility module.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      3.0.2
 * @updated    3.0.3
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Services\Consent_Service;
use ShahiLegalopsSuite\Services\Consent_Audit_Logger;

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
	 * Constructor
	 *
	 * @since 3.0.2
	 */
	public function __construct() {
		$this->consent_service = new Consent_Service();
		$this->audit_logger    = new Consent_Audit_Logger();
		$this->settings        = new Settings();
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
				'label' => __( 'Dashboard', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-chart-area',
			),
			'records'   => array(
				'label' => __( 'Consent Records', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-list-view',
			),
			'audit'     => array(
				'label' => __( 'Audit Logs', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-visibility',
			),
			'cookies'   => array(
				'label' => __( 'Cookie Scanner', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-admin-plugins',
			),
			'banner'    => array(
				'label' => __( 'Banner Config', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-format-image',
			),
			'geo'       => array(
				'label' => __( 'Geo Rules', 'shahi-legalops-suite' ),
				'icon'  => 'dashicons-admin-site-alt3',
			),
		);
	}

	/**
	 * Get dashboard statistics
	 *
	 * @since 3.0.3
	 * @return array Dashboard stats
	 */
	private function get_dashboard_stats() {
		$stats = $this->consent_service->get_statistics();

		$total     = array_sum( $stats['by_status'] ?? array() );
		$accepted  = $stats['by_status']['accepted'] ?? 0;
		$rejected  = $stats['by_status']['rejected'] ?? 0;
		$withdrawn = $stats['by_status']['withdrawn'] ?? 0;
		$pending   = $stats['by_status']['pending'] ?? 0;

		// Calculate compliance score (simplified)
		$compliance_score = $total > 0 ? round( ( $accepted / $total ) * 100 ) : 100;

		// Determine grade
		if ( $compliance_score >= 90 ) {
			$grade      = 'A';
			$grade_text = __( 'Excellent', 'shahi-legalops-suite' );
		} elseif ( $compliance_score >= 80 ) {
			$grade      = 'B';
			$grade_text = __( 'Good', 'shahi-legalops-suite' );
		} elseif ( $compliance_score >= 70 ) {
			$grade      = 'C';
			$grade_text = __( 'Fair', 'shahi-legalops-suite' );
		} elseif ( $compliance_score >= 60 ) {
			$grade      = 'D';
			$grade_text = __( 'Needs Work', 'shahi-legalops-suite' );
		} else {
			$grade      = 'F';
			$grade_text = __( 'Critical', 'shahi-legalops-suite' );
		}

		return array(
			'total'            => $total,
			'accepted'         => $accepted,
			'rejected'         => $rejected,
			'withdrawn'        => $withdrawn,
			'pending'          => $pending,
			'by_type'          => $stats['by_type'] ?? array(),
			'compliance_score' => $compliance_score,
			'grade'            => $grade,
			'grade_text'       => $grade_text,
			'acceptance_rate'  => $total > 0 ? round( ( $accepted / $total ) * 100, 1 ) : 0,
			'rejection_rate'   => $total > 0 ? round( ( $rejected / $total ) * 100, 1 ) : 0,
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
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'shahi-legalops-suite' ) );
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
		include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/compliance/main.php';
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
				include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/compliance/tabs/dashboard.php';
				break;

			case 'records':
				include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/compliance/tabs/records.php';
				break;

			case 'audit':
				include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/compliance/tabs/audit-logs.php';
				break;

			case 'cookies':
				include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/compliance/tabs/cookie-scanner.php';
				break;

			case 'banner':
				$settings = $this->settings->get_settings();
				include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/compliance/tabs/banner-config.php';
				break;

			case 'geo':
				include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/compliance/tabs/geo-rules.php';
				break;

			default:
				include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/compliance/tabs/dashboard.php';
		}
	}
}
