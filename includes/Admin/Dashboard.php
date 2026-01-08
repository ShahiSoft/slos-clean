<?php
/**
 * Dashboard Admin Page
 *
 * Renders the main dashboard page with statistics, quick actions,
 * recent activity, and getting started guide.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Core\Security;
use ShahiLegalFlowSuite\Database\QueryOptimizer;
use ShahiLegalFlowSuite\Modules\ModuleManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Page Class
 *
 * Handles the rendering and functionality of the main dashboard page.
 * Displays statistics, quick actions, and recent activity.
 *
 * @since 1.0.0
 */
class Dashboard {

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Initialize the dashboard
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();
	}

	/**
	 * Render the dashboard page
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render() {
		// Verify user capabilities
		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'shahi-legalflowsuite' ) );
		}

		// Enqueue dashboard styles
		wp_enqueue_style(
			'shahi-legalflowsuite-dashboard',
			SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/css/admin-dashboard-new.css',
			array(),
			SHAHI_LEGALFLOWSUITE_VERSION
		);

		// Get dashboard data
		$plugin_info     = $this->get_plugin_info();
		$stats           = $this->get_statistics();
		$modules_status  = $this->get_modules_status();
		$quick_actions   = $this->get_quick_actions();
		$recent_activity = $this->get_recent_activity();
		$getting_started = $this->get_getting_started_items();
		$support_links   = $this->get_support_links();

		// Load template
		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/dashboard.php';
	}

	/**
	 * Get plugin information
	 *
	 * Returns plugin metadata for display.
	 *
	 * @since 3.0.1
	 * @return array Plugin information
	 */
	private function get_plugin_info() {
		return array(
			'name'        => __( 'Shahi LegalFlowSuite', 'shahi-legalflowsuite' ),
			'short_name'  => 'SLOS',
			'version'     => defined( 'SHAHI_LEGALFLOWSUITE_VERSION' ) ? SHAHI_LEGALFLOWSUITE_VERSION : '3.0.1',
			'description' => __( 'A comprehensive legal operations management suite for WordPress, featuring DSR management, consent compliance, legal document handling, and accessibility scanning.', 'shahi-legalflowsuite' ),
			'author'      => __( 'Shahi Digital', 'shahi-legalflowsuite' ),
			'author_url'  => 'https://shahidigital.com',
			'plugin_url'  => 'https://shahidigital.com/plugins/legalflowsuite',
			'license'     => 'GPL-3.0+',
			'php_version' => PHP_VERSION,
			'wp_version'  => get_bloginfo( 'version' ),
			'db_version'  => get_option( 'shahi_legalflowsuite_db_version', '1.0.0' ),
		);
	}

	/**
	 * Get modules status
	 *
	 * Returns the status of all available modules using ModuleManager
	 * to ensure consistency with Module Dashboard.
	 * Only displays active (non-dormant) modules.
	 *
	 * @since 3.0.1
	 * @return array Modules status data
	 */
	private function get_modules_status() {
		// Define which modules to display on Dashboard (active modules only)
		// Excludes dormant modules: dsr-portal, security
		$display_modules = array(
			'consent-management'    => array(
				'icon'     => '🛡️',
				'page'     => 'slos-compliance',
				'dashicon' => 'dashicons-shield',
			),
			'legal-docs'            => array(
				'icon'     => '📄',
				'page'     => 'slos-documents',
				'dashicon' => 'dashicons-media-document',
			),
			'accessibility-scanner' => array(
				'icon'     => '♿',
				'page'     => 'slos-accessibility',
				'dashicon' => 'dashicons-universal-access',
			),
		);

		// Get modules from ModuleManager (same source as Module Dashboard)
		$module_manager = ModuleManager::get_instance();
		$all_modules    = array();

		foreach ( $display_modules as $key => $display_info ) {
			$module_obj = $module_manager->get_module( $key );

			if ( $module_obj ) {
				// Module exists - get real data from ModuleManager
				$all_modules[ $key ] = array(
					'name'        => $module_obj->get_name(),
					'description' => $module_obj->get_description(),
					'icon'        => $display_info['icon'],
					'page'        => $display_info['page'],
					'dashicon'    => $display_info['dashicon'],
					'enabled'     => $module_obj->is_enabled(),
					'slug'        => $key,
				);
			}
			// Skip unregistered modules (they shouldn't appear on dashboard)
		}

		return $all_modules;
	}

	/**
	 * Get support links
	 *
	 * Returns support and resource links.
	 *
	 * @since 3.0.1
	 * @return array Support links data
	 */
	private function get_support_links() {
		return array(
			array(
				'title'       => __( 'Documentation', 'shahi-legalflowsuite' ),
				'description' => __( 'Comprehensive guides and tutorials', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-book',
				'url'         => admin_url( 'admin.php?page=shahi-legalflowsuite-support' ),
				'external'    => false,
			),
			array(
				'title'       => __( 'Knowledge Base', 'shahi-legalflowsuite' ),
				'description' => __( 'FAQs and troubleshooting', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-lightbulb',
				'url'         => '#', // Placeholder - user will add later
				'external'    => true,
			),
			array(
				'title'       => __( 'Video Tutorials', 'shahi-legalflowsuite' ),
				'description' => __( 'Step-by-step video guides', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-video-alt3',
				'url'         => '#', // Placeholder - user will add later
				'external'    => true,
			),
			array(
				'title'       => __( 'Get Support', 'shahi-legalflowsuite' ),
				'description' => __( 'Contact our support team', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-sos',
				'url'         => admin_url( 'admin.php?page=shahi-legalflowsuite-support' ),
				'external'    => false,
			),
			array(
				'title'       => __( 'Feature Request', 'shahi-legalflowsuite' ),
				'description' => __( 'Suggest new features', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-megaphone',
				'url'         => '#', // Placeholder - user will add later
				'external'    => true,
			),
			array(
				'title'       => __( 'Changelog', 'shahi-legalflowsuite' ),
				'description' => __( 'View release history', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-backup',
				'url'         => admin_url( 'admin.php?page=shahi-legalflowsuite-support#changelog' ),
				'external'    => false,
			),
		);
	}

	/**
	 * Get dashboard statistics
	 *
	 * Returns an array of statistics to display on the dashboard.
	 *
	 * @since 1.0.0
	 * @return array Statistics data
	 */
	private function get_statistics() {
		return array(
			array(
				'title'       => __( 'Active Modules', 'shahi-legalflowsuite' ),
				'value'       => $this->get_active_modules_count(),
				'icon'        => 'dashicons-admin-plugins',
				'color'       => 'primary',
				'trend'       => null,
				'description' => __( 'Compliance features currently enabled and running', 'shahi-legalflowsuite' ),
			),
			array(
				'title'       => __( 'Total Events', 'shahi-legalflowsuite' ),
				'value'       => $this->get_total_events_count(),
				'icon'        => 'dashicons-chart-line',
				'color'       => 'success',
				'trend'       => null,
				'description' => __( 'Consent records, audit logs, and system actions tracked', 'shahi-legalflowsuite' ),
			),
			array(
				'title'       => __( 'Performance Score', 'shahi-legalflowsuite' ),
				'value'       => $this->get_performance_score(),
				'suffix'      => '%',
				'icon'        => 'dashicons-performance',
				'color'       => 'accent',
				'trend'       => null,
				'description' => __( 'System health based on active modules and configuration', 'shahi-legalflowsuite' ),
			),
			array(
				'title'       => __( 'Last Activity', 'shahi-legalflowsuite' ),
				'value'       => $this->get_last_activity_time(),
				'icon'        => 'dashicons-clock',
				'color'       => 'info',
				'trend'       => null,
				'is_time'     => true,
				'description' => __( 'Most recent compliance event or system change', 'shahi-legalflowsuite' ),
			),
		);
	}

	/**
	 * Get active modules count
	 *
	 * Uses ModuleManager to get accurate count of registered and enabled modules.
	 * This ensures dormant modules (DSR, Security) are excluded from the count.
	 *
	 * @since 1.0.0
	 * @return int Number of active modules
	 */
	private function get_active_modules_count() {
		// Use ModuleManager for accurate count (excludes dormant/unregistered modules)
		$module_manager = ModuleManager::get_instance();
		$stats          = $module_manager->get_statistics();
		
		return (int) $stats['enabled'];
	}

	/**
	 * Get total events count
	 *
	 * @since 1.0.0
	 * @return int Number of tracked events
	 */
	private function get_total_events_count() {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists (cached for performance)
		if ( ! QueryOptimizer::table_exists_cached( $table ) ) {
			return 0;
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		return (int) $count;
	}

	/**
	 * Get last activity time
	 *
	 * @since 1.0.0
	 * @return string Formatted time or "N/A"
	 */
	private function get_last_activity_time() {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists (cached for performance)
		if ( ! QueryOptimizer::table_exists_cached( $table ) ) {
			return __( 'N/A', 'shahi-legalflowsuite' );
		}

		$last_time = $wpdb->get_var( "SELECT created_at FROM $table ORDER BY created_at DESC LIMIT 1" );

		if ( ! $last_time ) {
			return __( 'N/A', 'shahi-legalflowsuite' );
		}

		return human_time_diff( strtotime( $last_time ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'shahi-legalflowsuite' );
	}

	/**
	 * Get performance score
	 *
	 * Calculate overall plugin health score based on:
	 * - Active modules count (only registered, non-dormant modules)
	 * - Configuration completeness
	 * - Database health
	 *
	 * @since 3.0.1
	 * @return int Performance score (0-100)
	 */
	private function get_performance_score() {
		$score = 0;

		// Factor 1: Active modules (max 40 points)
		// Only count registered modules (excludes dormant DSR/Security)
		$module_manager = ModuleManager::get_instance();
		$stats          = $module_manager->get_statistics();
		if ( $stats['total'] > 0 ) {
			$enabled_ratio = $stats['enabled'] / $stats['total'];
			$score        += (int) ( $enabled_ratio * 40 );
		}

		// Factor 2: Configuration completeness (max 30 points)
		// Check if company profile is configured
		$profile_repo = \ShahiLegalFlowSuite\Database\Repositories\Company_Profile_Repository::get_instance();
		$profile      = $profile_repo->get_profile();

		// Check if profile has meaningful data (not just defaults)
		$completion = $profile_repo->get_completion_percentage();
		if ( $completion > 10 ) { // At least 10% configured
			$score += 30;
		}

		// Factor 3: Database health (max 30 points)
		// Check active module tables only (exclude dormant DSR table)
		global $wpdb;
		$tables = array(
			$wpdb->prefix . 'slos_consents',      // Consent Management
			$wpdb->prefix . 'slos_documents',     // Legal Documents
			$wpdb->prefix . 'slos_scan_results',  // Accessibility Scanner
		);

		$healthy_tables = 0;
		foreach ( $tables as $table ) {
			if ( QueryOptimizer::table_exists_cached( $table ) ) {
				++$healthy_tables;
			}
		}
		$score += (int) ( ( $healthy_tables / count( $tables ) ) * 30 );

		return min( 100, $score );
	}

	/**
	 * Get quick actions
	 *
	 * Returns an array of quick action buttons to display on the dashboard.
	 * Only shows actions for active (non-dormant) modules.
	 *
	 * @since 1.0.0
	 * @return array Quick actions data
	 */
	private function get_quick_actions() {
		return array(
			array(
				'title'       => __( 'Manage Modules', 'shahi-legalflowsuite' ),
				'description' => __( 'Enable or disable plugin modules', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-admin-plugins',
				'url'         => admin_url( 'admin.php?page=shahi-legalflowsuite-modules' ),
				'color'       => 'primary',
			),
			array(
				'title'       => __( 'Accessibility Scanner', 'shahi-legalflowsuite' ),
				'description' => __( 'Scan & fix accessibility issues', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-universal-access',
				'url'         => admin_url( 'admin.php?page=slos-accessibility' ),
				'color'       => 'success',
			),
			array(
				'title'       => __( 'Consent Compliance', 'shahi-legalflowsuite' ),
				'description' => __( 'Manage cookie consent & banners', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-shield',
				'url'         => admin_url( 'admin.php?page=slos-compliance' ),
				'color'       => 'accent',
			),
			array(
				'title'       => __( 'Legal Documents', 'shahi-legalflowsuite' ),
				'description' => __( 'Generate & manage legal docs', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-media-document',
				'url'         => admin_url( 'admin.php?page=slos-documents' ),
				'color'       => 'warning',
			),
			array(
				'title'       => __( 'Plugin Settings', 'shahi-legalflowsuite' ),
				'description' => __( 'Configure plugin options', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-admin-settings',
				'url'         => admin_url( 'admin.php?page=shahi-legalflowsuite-settings' ),
				'color'       => 'info',
			),
			array(
				'title'       => __( 'Get Support', 'shahi-legalflowsuite' ),
				'description' => __( 'Documentation and help', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-sos',
				'url'         => admin_url( 'admin.php?page=shahi-legalflowsuite-support' ),
				'color'       => 'purple',
			),
		);
	}

	/**
	 * Get recent activity
	 *
	 * Returns recent activity events to display on the dashboard.
	 *
	 * @since 1.0.0
	 * @param int $limit Number of items to return.
	 * @return array Recent activity data
	 */
	private function get_recent_activity( $limit = 10 ) {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists (cached for performance)
		if ( ! QueryOptimizer::table_exists_cached( $table ) ) {
			return array();
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT event_type, event_data, created_at FROM $table ORDER BY created_at DESC LIMIT %d",
				$limit
			)
		);

		if ( empty( $results ) ) {
			return array();
		}

		$activity = array();
		foreach ( $results as $event ) {
			$activity[] = array(
				'title'       => $this->format_event_title( $event->event_type ),
				'description' => $this->format_event_description( $event ),
				'time'        => human_time_diff( strtotime( $event->created_at ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'shahi-legalflowsuite' ),
				'icon'        => $this->get_event_icon( $event->event_type ),
				'type'        => $event->event_type,
			);
		}

		return $activity;
	}

	/**
	 * Format event title
	 *
	 * @since 1.0.0
	 * @param string $event_type Event type.
	 * @return string Formatted title
	 */
	private function format_event_title( $event_type ) {
		$titles = array(
			'module_enabled'     => __( 'Module Enabled', 'shahi-legalflowsuite' ),
			'module_disabled'    => __( 'Module Disabled', 'shahi-legalflowsuite' ),
			'settings_updated'   => __( 'Settings Updated', 'shahi-legalflowsuite' ),
			'plugin_activated'   => __( 'Plugin Activated', 'shahi-legalflowsuite' ),
			'plugin_deactivated' => __( 'Plugin Deactivated', 'shahi-legalflowsuite' ),
		);

		return isset( $titles[ $event_type ] ) ? $titles[ $event_type ] : ucwords( str_replace( '_', ' ', $event_type ) );
	}

	/**
	 * Format event description
	 *
	 * @since 1.0.0
	 * @param object $event Event object.
	 * @return string Formatted description
	 */
	private function format_event_description( $event ) {
		$data = json_decode( $event->event_data, true );

		if ( empty( $data ) ) {
			return __( 'No additional details', 'shahi-legalflowsuite' );
		}

		// Format based on event type
		switch ( $event->event_type ) {
			case 'module_enabled':
			case 'module_disabled':
				return isset( $data['module_name'] ) ? $data['module_name'] : __( 'Unknown module', 'shahi-legalflowsuite' );

			case 'settings_updated':
				return isset( $data['section'] ) ? sprintf( __( 'Section: %s', 'shahi-legalflowsuite' ), $data['section'] ) : __( 'General settings', 'shahi-legalflowsuite' );

			default:
				return __( 'Event recorded', 'shahi-legalflowsuite' );
		}
	}

	/**
	 * Get event icon
	 *
	 * @since 1.0.0
	 * @param string $event_type Event type.
	 * @return string Dashicon class
	 */
	private function get_event_icon( $event_type ) {
		$icons = array(
			'module_enabled'     => 'dashicons-yes-alt',
			'module_disabled'    => 'dashicons-dismiss',
			'settings_updated'   => 'dashicons-admin-settings',
			'plugin_activated'   => 'dashicons-plugins-checked',
			'plugin_deactivated' => 'dashicons-plugins-checked',
		);

		return isset( $icons[ $event_type ] ) ? $icons[ $event_type ] : 'dashicons-marker';
	}

	/**
	 * Get getting started items
	 *
	 * Returns a checklist of getting started tasks.
	 *
	 * @since 1.0.0
	 * @return array Getting started items
	 */
	private function get_getting_started_items() {
		$onboarding_completed = get_option( 'shahi_legalflowsuite_onboarding_completed', false );
		$modules_configured   = $this->get_active_modules_count() > 0;
		$settings_configured  = ! empty( get_option( 'shahi_legalflowsuite_settings', array() ) );

		// Check if company profile is set up
		$company_profile    = get_option( 'slos_company_profile', array() );
		$profile_configured = ! empty( $company_profile ) && ! empty( $company_profile['company_name'] );

		return array(
			array(
				'title'        => __( 'Complete Onboarding', 'shahi-legalflowsuite' ),
				'description'  => __( 'Set up your plugin with our guided onboarding wizard', 'shahi-legalflowsuite' ),
				'completed'    => $onboarding_completed,
				'action_text'  => $onboarding_completed ? __( 'Review', 'shahi-legalflowsuite' ) : __( 'Start Now', 'shahi-legalflowsuite' ),
				'action_url'   => '#', // Will trigger onboarding modal via JS
				'action_class' => 'shahi-trigger-onboarding',
			),
			array(
				'title'        => __( 'Enable Modules', 'shahi-legalflowsuite' ),
				'description'  => __( 'Activate the features you need for your site', 'shahi-legalflowsuite' ),
				'completed'    => $modules_configured,
				'action_text'  => __( 'Manage Modules', 'shahi-legalflowsuite' ),
				'action_url'   => admin_url( 'admin.php?page=shahi-legalflowsuite-modules' ),
				'action_class' => '',
			),
			array(
				'title'        => __( 'Configure Settings', 'shahi-legalflowsuite' ),
				'description'  => __( 'Customize plugin behavior to match your needs', 'shahi-legalflowsuite' ),
				'completed'    => $settings_configured,
				'action_text'  => __( 'Go to Settings', 'shahi-legalflowsuite' ),
				'action_url'   => admin_url( 'admin.php?page=shahi-legalflowsuite-settings' ),
				'action_class' => '',
			),
			array(
				'title'        => __( 'Setup Company Profile', 'shahi-legalflowsuite' ),
				'description'  => __( 'Configure your company details for legal documents', 'shahi-legalflowsuite' ),
				'completed'    => $profile_configured,
				'action_text'  => $profile_configured ? __( 'Edit Profile', 'shahi-legalflowsuite' ) : __( 'Setup Now', 'shahi-legalflowsuite' ),
				'action_url'   => admin_url( 'admin.php?page=slos-company-profile' ),
				'action_class' => '',
			),
		);
	}
}
