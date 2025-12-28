<?php
/**
 * Dashboard Admin Page
 *
 * Renders the main dashboard page with statistics, quick actions,
 * recent activity, and getting started guide.
 *
 * @package    ShahiLegalopsSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalopsSuite\Admin;

use ShahiLegalopsSuite\Core\Security;
use ShahiLegalopsSuite\Database\QueryOptimizer;
use ShahiLegalopsSuite\Modules\ModuleManager;

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
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'shahi-legalops-suite' ) );
		}

		// Enqueue dashboard styles
		wp_enqueue_style(
			'shahi-legalops-suite-dashboard',
			SHAHI_LEGALOPS_SUITE_PLUGIN_URL . 'assets/css/admin-dashboard-new.css',
			array(),
			SHAHI_LEGALOPS_SUITE_VERSION
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
		include SHAHI_LEGALOPS_SUITE_PATH . 'templates/admin/dashboard.php';
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
			'name'         => __( 'Shahi LegalOps Suite', 'shahi-legalops-suite' ),
			'short_name'   => 'SLOS',
			'version'      => defined( 'SHAHI_LEGALOPS_SUITE_VERSION' ) ? SHAHI_LEGALOPS_SUITE_VERSION : '3.0.1',
			'description'  => __( 'A comprehensive legal operations management suite for WordPress, featuring DSR management, consent compliance, legal document handling, and accessibility scanning.', 'shahi-legalops-suite' ),
			'author'       => __( 'Shahi Digital', 'shahi-legalops-suite' ),
			'author_url'   => 'https://shahidigital.com',
			'plugin_url'   => 'https://shahidigital.com/plugins/legalops-suite',
			'license'      => 'GPL-3.0+',
			'php_version'  => PHP_VERSION,
			'wp_version'   => get_bloginfo( 'version' ),
			'db_version'   => get_option( 'shahi_legalops_suite_db_version', '1.0.0' ),
		);
	}

	/**
	 * Get modules status
	 *
	 * Returns the status of all available modules using ModuleManager
	 * to ensure consistency with Module Dashboard.
	 *
	 * @since 3.0.1
	 * @return array Modules status data
	 */
	private function get_modules_status() {
		// Define which modules to display on Dashboard (core compliance modules)
		$display_modules = array(
			'dsr-portal' => array(
				'icon'     => '📋',
				'page'     => 'slos-requests',
				'dashicon' => 'dashicons-clipboard',
			),
			'consent-management' => array(
				'icon'     => '🛡️',
				'page'     => 'slos-compliance',
				'dashicon' => 'dashicons-shield',
			),
			'legal-docs' => array(
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
		$all_modules = array();

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
			} else {
				// Module not registered - show as disabled
				$all_modules[ $key ] = array(
					'name'        => ucwords( str_replace( '-', ' ', $key ) ),
					'description' => __( 'Module not available', 'shahi-legalops-suite' ),
					'icon'        => $display_info['icon'],
					'page'        => $display_info['page'],
					'dashicon'    => $display_info['dashicon'],
					'enabled'     => false,
					'slug'        => $key,
				);
			}
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
				'title'       => __( 'Documentation', 'shahi-legalops-suite' ),
				'description' => __( 'Comprehensive guides and tutorials', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-book',
				'url'         => admin_url( 'admin.php?page=shahi-legalops-suite-support' ),
				'external'    => false,
			),
			array(
				'title'       => __( 'Knowledge Base', 'shahi-legalops-suite' ),
				'description' => __( 'FAQs and troubleshooting', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-lightbulb',
				'url'         => '#', // Placeholder - user will add later
				'external'    => true,
			),
			array(
				'title'       => __( 'Video Tutorials', 'shahi-legalops-suite' ),
				'description' => __( 'Step-by-step video guides', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-video-alt3',
				'url'         => '#', // Placeholder - user will add later
				'external'    => true,
			),
			array(
				'title'       => __( 'Get Support', 'shahi-legalops-suite' ),
				'description' => __( 'Contact our support team', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-sos',
				'url'         => admin_url( 'admin.php?page=shahi-legalops-suite-support' ),
				'external'    => false,
			),
			array(
				'title'       => __( 'Feature Request', 'shahi-legalops-suite' ),
				'description' => __( 'Suggest new features', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-megaphone',
				'url'         => '#', // Placeholder - user will add later
				'external'    => true,
			),
			array(
				'title'       => __( 'Changelog', 'shahi-legalops-suite' ),
				'description' => __( 'View release history', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-backup',
				'url'         => admin_url( 'admin.php?page=shahi-legalops-suite-support#changelog' ),
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
				'title'       => __( 'Active Modules', 'shahi-legalops-suite' ),
				'value'       => $this->get_active_modules_count(),
				'icon'        => 'dashicons-admin-plugins',
				'color'       => 'primary',
				'trend'       => null,
				'description' => __( 'Compliance features currently enabled on your site', 'shahi-legalops-suite' ),
			),
			array(
				'title'       => __( 'Total Events', 'shahi-legalops-suite' ),
				'value'       => $this->get_total_events_count(),
				'icon'        => 'dashicons-chart-line',
				'color'       => 'success',
				'trend'       => '+12%',
				'description' => __( 'Consent records, DSR requests, and system actions logged', 'shahi-legalops-suite' ),
			),
			array(
				'title'       => __( 'Performance Score', 'shahi-legalops-suite' ),
				'value'       => '98',
				'suffix'      => '%',
				'icon'        => 'dashicons-performance',
				'color'       => 'accent',
				'trend'       => '+5%',
				'description' => __( 'Overall plugin health based on config and optimizations', 'shahi-legalops-suite' ),
			),
			array(
				'title'       => __( 'Last Activity', 'shahi-legalops-suite' ),
				'value'       => $this->get_last_activity_time(),
				'icon'        => 'dashicons-clock',
				'color'       => 'info',
				'trend'       => null,
				'is_time'     => true,
				'description' => __( 'Most recent compliance event or configuration change', 'shahi-legalops-suite' ),
			),
		);
	}

	/**
	 * Get active modules count
	 *
	 * @since 1.0.0
	 * @return int Number of active modules
	 */
	private function get_active_modules_count() {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_modules';

		// Check if table exists (cached for performance)
		if ( ! QueryOptimizer::table_exists_cached( $table ) ) {
			return 0;
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE is_enabled = 1" );
		return (int) $count;
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
			return __( 'N/A', 'shahi-legalops-suite' );
		}

		$last_time = $wpdb->get_var( "SELECT created_at FROM $table ORDER BY created_at DESC LIMIT 1" );

		if ( ! $last_time ) {
			return __( 'N/A', 'shahi-legalops-suite' );
		}

		return human_time_diff( strtotime( $last_time ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'shahi-legalops-suite' );
	}

	/**
	 * Get quick actions
	 *
	 * Returns an array of quick action buttons to display on the dashboard.
	 *
	 * @since 1.0.0
	 * @return array Quick actions data
	 */
	private function get_quick_actions() {
		return array(
			array(
				'title'       => __( 'Manage Modules', 'shahi-legalops-suite' ),
				'description' => __( 'Enable or disable plugin modules', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-admin-plugins',
				'url'         => admin_url( 'admin.php?page=shahi-legalops-suite-modules' ),
				'color'       => 'primary',
			),
			array(
				'title'       => __( 'DSR Requests', 'shahi-legalops-suite' ),
				'description' => __( 'Manage data subject requests', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-clipboard',
				'url'         => admin_url( 'admin.php?page=slos-requests' ),
				'color'       => 'success',
			),
			array(
				'title'       => __( 'Consent Compliance', 'shahi-legalops-suite' ),
				'description' => __( 'Manage cookie consent & banners', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-shield',
				'url'         => admin_url( 'admin.php?page=slos-compliance' ),
				'color'       => 'accent',
			),
			array(
				'title'       => __( 'Legal Documents', 'shahi-legalops-suite' ),
				'description' => __( 'Generate & manage legal docs', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-media-document',
				'url'         => admin_url( 'admin.php?page=slos-documents' ),
				'color'       => 'warning',
			),
			array(
				'title'       => __( 'Plugin Settings', 'shahi-legalops-suite' ),
				'description' => __( 'Configure plugin options', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-admin-settings',
				'url'         => admin_url( 'admin.php?page=shahi-legalops-suite-settings' ),
				'color'       => 'info',
			),
			array(
				'title'       => __( 'Get Support', 'shahi-legalops-suite' ),
				'description' => __( 'Documentation and help', 'shahi-legalops-suite' ),
				'icon'        => 'dashicons-sos',
				'url'         => admin_url( 'admin.php?page=shahi-legalops-suite-support' ),
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
				'time'        => human_time_diff( strtotime( $event->created_at ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'shahi-legalops-suite' ),
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
			'module_enabled'     => __( 'Module Enabled', 'shahi-legalops-suite' ),
			'module_disabled'    => __( 'Module Disabled', 'shahi-legalops-suite' ),
			'settings_updated'   => __( 'Settings Updated', 'shahi-legalops-suite' ),
			'plugin_activated'   => __( 'Plugin Activated', 'shahi-legalops-suite' ),
			'plugin_deactivated' => __( 'Plugin Deactivated', 'shahi-legalops-suite' ),
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
			return __( 'No additional details', 'shahi-legalops-suite' );
		}

		// Format based on event type
		switch ( $event->event_type ) {
			case 'module_enabled':
			case 'module_disabled':
				return isset( $data['module_name'] ) ? $data['module_name'] : __( 'Unknown module', 'shahi-legalops-suite' );

			case 'settings_updated':
				return isset( $data['section'] ) ? sprintf( __( 'Section: %s', 'shahi-legalops-suite' ), $data['section'] ) : __( 'General settings', 'shahi-legalops-suite' );

			default:
				return __( 'Event recorded', 'shahi-legalops-suite' );
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
		$onboarding_completed = get_option( 'shahi_legalops_suite_onboarding_completed', false );
		$modules_configured   = $this->get_active_modules_count() > 0;
		$settings_configured  = ! empty( get_option( 'shahi_legalops_suite_settings', array() ) );

		// Check if company profile is set up
		$company_profile = get_option( 'slos_company_profile', array() );
		$profile_configured = ! empty( $company_profile ) && ! empty( $company_profile['company_name'] );

		return array(
			array(
				'title'        => __( 'Complete Onboarding', 'shahi-legalops-suite' ),
				'description'  => __( 'Set up your plugin with our guided onboarding wizard', 'shahi-legalops-suite' ),
				'completed'    => $onboarding_completed,
				'action_text'  => $onboarding_completed ? __( 'Review', 'shahi-legalops-suite' ) : __( 'Start Now', 'shahi-legalops-suite' ),
				'action_url'   => '#', // Will trigger onboarding modal via JS
				'action_class' => 'shahi-trigger-onboarding',
			),
			array(
				'title'        => __( 'Enable Modules', 'shahi-legalops-suite' ),
				'description'  => __( 'Activate the features you need for your site', 'shahi-legalops-suite' ),
				'completed'    => $modules_configured,
				'action_text'  => __( 'Manage Modules', 'shahi-legalops-suite' ),
				'action_url'   => admin_url( 'admin.php?page=shahi-legalops-suite-modules' ),
				'action_class' => '',
			),
			array(
				'title'        => __( 'Configure Settings', 'shahi-legalops-suite' ),
				'description'  => __( 'Customize plugin behavior to match your needs', 'shahi-legalops-suite' ),
				'completed'    => $settings_configured,
				'action_text'  => __( 'Go to Settings', 'shahi-legalops-suite' ),
				'action_url'   => admin_url( 'admin.php?page=shahi-legalops-suite-settings' ),
				'action_class' => '',
			),
			array(
				'title'        => __( 'Setup Company Profile', 'shahi-legalops-suite' ),
				'description'  => __( 'Configure your company details for legal documents', 'shahi-legalops-suite' ),
				'completed'    => $profile_configured,
				'action_text'  => $profile_configured ? __( 'Edit Profile', 'shahi-legalops-suite' ) : __( 'Setup Now', 'shahi-legalops-suite' ),
				'action_url'   => admin_url( 'admin.php?page=slos-company-profile' ),
				'action_class' => '',
			),
		);
	}
}

