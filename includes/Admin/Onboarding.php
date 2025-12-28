<?php
/**
 * Onboarding Controller
 *
 * Handles the multi-step onboarding wizard for first-time users.
 * Collects user preferences and configures the plugin accordingly.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Core\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Onboarding Class
 *
 * Manages the onboarding wizard flow, data collection, and completion tracking.
 *
 * @since 1.0.0
 */
class Onboarding {

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Option name for onboarding completion status
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_COMPLETED = 'shahi_legalflowsuite_onboarding_completed';

	/**
	 * Option name for onboarding data
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_DATA = 'shahi_legalflowsuite_onboarding_data';

	/**
	 * Initialize the onboarding controller
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();
	}

	/**
	 * Check if onboarding should be shown
	 *
	 * @since 1.0.0
	 * @return bool True if onboarding should be displayed
	 */
	public function should_show_onboarding() {
		// Force fresh read from database, bypassing cache
		wp_cache_delete( 'shahi_legalflowsuite_onboarding_completed', 'options' );
		wp_cache_delete( 'shahi_legalflowsuite_onboarding_data', 'options' );

		// Don't show if user doesn't have permission
		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			return false;
		}

		// Don't show if already completed
		if ( get_option( self::OPTION_COMPLETED, false ) ) {
			return false;
		}

		// Only show on plugin pages
		if ( ! $this->is_plugin_page() ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if current page is a plugin page
	 *
	 * @since 1.0.0
	 * @return bool True if on a plugin page
	 */
	private function is_plugin_page() {
		if ( ! is_admin() ) {
			return false;
		}

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		return strpos( $page, 'shahi-legalflowsuite' ) === 0;
	}

	/**
	 * Render the onboarding modal
	 *
	 * Outputs the onboarding modal HTML if conditions are met.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_modal() {
		if ( ! $this->should_show_onboarding() ) {
			return;
		}

		include SHAHI_LEGALFLOWSUITE_PATH . 'templates/admin/onboarding-modal.php';
	}

	/**
	 * Get onboarding steps data
	 *
	 * @since 1.0.0
	 * @return array Onboarding steps configuration
	 */
	public function get_steps() {
		return array(
			'welcome'       => array(
				'title'    => __( 'Welcome to ShahiLegalFlowSuite', 'shahi-legalflowsuite' ),
				'subtitle' => __( 'Let\'s get you set up in just a few steps', 'shahi-legalflowsuite' ),
				'icon'     => 'dashicons-welcome-learn-more',
			),
			'purpose'       => array(
				'title'    => __( 'What will you use this plugin for?', 'shahi-legalflowsuite' ),
				'subtitle' => __( 'Help us customize your experience', 'shahi-legalflowsuite' ),
				'icon'     => 'dashicons-admin-tools',
			),
			'features'      => array(
				'title'    => __( 'Choose Your Features', 'shahi-legalflowsuite' ),
				'subtitle' => __( 'Enable the modules that fit your needs', 'shahi-legalflowsuite' ),
				'icon'     => 'dashicons-admin-plugins',
			),
			'configuration' => array(
				'title'    => __( 'Basic Configuration', 'shahi-legalflowsuite' ),
				'subtitle' => __( 'Set up your preferences', 'shahi-legalflowsuite' ),
				'icon'     => 'dashicons-admin-settings',
			),
			'complete'      => array(
				'title'    => __( 'All Set!', 'shahi-legalflowsuite' ),
				'subtitle' => __( 'You\'re ready to start using ShahiLegalFlowSuite', 'shahi-legalflowsuite' ),
				'icon'     => 'dashicons-yes-alt',
			),
		);
	}

	/**
	 * Get purpose options
	 *
	 * @since 1.0.0
	 * @return array Purpose options for step 2
	 */
	public function get_purpose_options() {
		return array(
			'ecommerce'  => array(
				'label'       => __( 'E-commerce', 'shahi-legalflowsuite' ),
				'description' => __( 'Online store or product catalog', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-cart',
			),
			'blog'       => array(
				'label'       => __( 'Blog / Content', 'shahi-legalflowsuite' ),
				'description' => __( 'Publishing articles and content', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-edit-page',
			),
			'business'   => array(
				'label'       => __( 'Business Site', 'shahi-legalflowsuite' ),
				'description' => __( 'Company or service website', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-building',
			),
			'portfolio'  => array(
				'label'       => __( 'Portfolio', 'shahi-legalflowsuite' ),
				'description' => __( 'Showcase work or projects', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-portfolio',
			),
			'membership' => array(
				'label'       => __( 'Membership', 'shahi-legalflowsuite' ),
				'description' => __( 'Community or member area', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-groups',
			),
			'other'      => array(
				'label'       => __( 'Other', 'shahi-legalflowsuite' ),
				'description' => __( 'Something else', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-grid-view',
			),
		);
	}

	/**
	 * Get recommended modules based on purpose
	 *
	 * @since 1.0.0
	 * @param string $purpose User's selected purpose.
	 * @return array Recommended module keys
	 */
	public function get_recommended_modules( $purpose = '' ) {
		$recommendations = array(
			'ecommerce'  => array( 'cache', 'security', 'notifications' ),
			'blog'       => array( 'seo', 'cache', 'notifications' ),
			'business'   => array( 'security', 'notifications', 'api' ),
			'portfolio'  => array( 'cache', 'notifications', 'security' ),
			'membership' => array( 'security', 'notifications', 'api' ),
			'other'      => array( 'notifications', 'cache', 'security' ),
		);

		return isset( $recommendations[ $purpose ] ) ? $recommendations[ $purpose ] : array( 'notifications', 'cache', 'security' );
	}

	/**
	 * Get available modules for selection
	 *
	 * @since 1.0.0
	 * @return array Available modules
	 */
	public function get_available_modules() {
		return array(
			'notifications' => array(
				'name'        => __( 'Email Notifications', 'shahi-legalflowsuite' ),
				'description' => __( 'Automated email alerts for events', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-email',
			),
			'cache'         => array(
				'name'        => __( 'Advanced Caching', 'shahi-legalflowsuite' ),
				'description' => __( 'Improve performance with caching', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-performance',
			),
			'security'      => array(
				'name'        => __( 'Enhanced Security', 'shahi-legalflowsuite' ),
				'description' => __( 'Additional security features', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-shield',
			),
			'api'           => array(
				'name'        => __( 'REST API', 'shahi-legalflowsuite' ),
				'description' => __( 'Extended API endpoints', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-rest-api',
			),
			'import_export' => array(
				'name'        => __( 'Import/Export', 'shahi-legalflowsuite' ),
				'description' => __( 'Backup and restore settings', 'shahi-legalflowsuite' ),
				'icon'        => 'dashicons-database-export',
			),
		);
	}

	/**
	 * Save onboarding data
	 *
	 * Processes the onboarding form submission and saves user preferences.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_onboarding() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! Security::verify_nonce( $_POST['nonce'], 'shahi_onboarding' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed.', 'shahi-legalflowsuite' ),
				)
			);
		}

		// Check permissions
		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Insufficient permissions.', 'shahi-legalflowsuite' ),
				)
			);
		}

		// Get submitted data
		$purpose  = isset( $_POST['purpose'] ) ? sanitize_text_field( $_POST['purpose'] ) : '';
		$modules  = isset( $_POST['modules'] ) && is_array( $_POST['modules'] )
			? array_map( 'sanitize_text_field', $_POST['modules'] )
			: array();
		$settings = isset( $_POST['settings'] ) && is_array( $_POST['settings'] )
			? $_POST['settings']
			: array();

		// Sanitize settings
		$sanitized_settings = array(
			'enable_analytics'     => false,
			'enable_notifications' => isset( $settings['enable_notifications'] ) ? (bool) $settings['enable_notifications'] : false,
		);

		// Save onboarding data
		$onboarding_data = array(
			'purpose'         => $purpose,
			'modules_enabled' => $modules,
			'settings'        => $sanitized_settings,
			'completed_at'    => current_time( 'mysql' ),
			'user_id'         => get_current_user_id(),
		);

		update_option( self::OPTION_DATA, $onboarding_data );
		update_option( self::OPTION_COMPLETED, true );

		// Enable selected modules in database
		$this->enable_modules( $modules );

		// Apply basic settings
		$this->apply_settings( $sanitized_settings );

		// Track analytics event
		$this->track_onboarding_completion( $onboarding_data );

		wp_send_json_success(
			array(
				'message'  => __( 'Onboarding completed successfully!', 'shahi-legalflowsuite' ),
				'redirect' => admin_url( 'admin.php?page=shahi-legalflowsuite' ),
			)
		);
	}

	/**
	 * Enable selected modules
	 *
	 * @since 1.0.0
	 * @param array $modules Array of module keys to enable.
	 * @return void
	 */
	private function enable_modules( $modules ) {
		if ( empty( $modules ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'shahi_modules';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			return;
		}

		foreach ( $modules as $module_key ) {
			// Check if module exists
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $table WHERE module_key = %s",
					$module_key
				)
			);

			if ( $exists ) {
				// Update existing module
				$wpdb->update(
					$table,
					array(
						'is_enabled'   => 1,
						'last_updated' => current_time( 'mysql' ),
					),
					array( 'module_key' => $module_key ),
					array( '%d', '%s' ),
					array( '%s' )
				);
			} else {
				// Insert new module
				$wpdb->insert(
					$table,
					array(
						'module_key'   => $module_key,
						'is_enabled'   => 1,
						'settings'     => '{}',
						'last_updated' => current_time( 'mysql' ),
					),
					array( '%s', '%d', '%s', '%s' )
				);
			}
		}
	}

	/**
	 * Apply basic settings from onboarding
	 *
	 * @since 1.0.0
	 * @param array $settings Settings to apply.
	 * @return void
	 */
	private function apply_settings( $settings ) {
		$current_settings = get_option( 'shahi_legalflowsuite_settings', array() );

		// Merge with onboarding preferences
		if ( isset( $settings['enable_analytics'] ) ) {
			$current_settings['enable_analytics'] = $settings['enable_analytics'];
		}

		if ( isset( $settings['enable_notifications'] ) ) {
			$current_settings['enable_email_notifications'] = $settings['enable_notifications'];
		}

		update_option( 'shahi_legalflowsuite_settings', $current_settings );
	}

	/**
	 * Track onboarding completion event
	 *
	 * @since 1.0.0
	 * @param array $data Onboarding data.
	 * @return void
	 */
	private function track_onboarding_completion( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'purpose'       => $data['purpose'],
				'modules_count' => count( $data['modules_enabled'] ),
				'modules'       => $data['modules_enabled'],
			)
		);

		$wpdb->insert(
			$table,
			array(
				'event_type' => 'onboarding_completed',
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $this->security->get_client_ip(),
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Skip onboarding
	 *
	 * Marks onboarding as completed without collecting data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function skip_onboarding() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! Security::verify_nonce( $_POST['nonce'], 'shahi_onboarding' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed.', 'shahi-legalflowsuite' ),
				)
			);
		}

		// Check permissions
		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Insufficient permissions.', 'shahi-legalflowsuite' ),
				)
			);
		}

		// Mark as completed without data
		update_option( self::OPTION_COMPLETED, true );
		update_option(
			self::OPTION_DATA,
			array(
				'skipped'      => true,
				'completed_at' => current_time( 'mysql' ),
			)
		);

		wp_send_json_success(
			array(
				'message'  => __( 'Onboarding skipped.', 'shahi-legalflowsuite' ),
				'redirect' => admin_url( 'admin.php?page=shahi-legalflowsuite' ),
			)
		);
	}

	/**
	 * Reset onboarding
	 *
	 * Allows users to re-run the onboarding wizard.
	 *
	 * @since 1.0.0
	 * @return bool True on success
	 */
	public function reset_onboarding() {
		if ( ! current_user_can( 'manage_shahi_template' ) ) {
			return false;
		}

		delete_option( self::OPTION_COMPLETED );
		delete_option( self::OPTION_DATA );

		return true;
	}

	/**
	 * Get onboarding data
	 *
	 * @since 1.0.0
	 * @return array Saved onboarding data
	 */
	public function get_onboarding_data() {
		return get_option( self::OPTION_DATA, array() );
	}

	/**
	 * Check if onboarding is completed
	 *
	 * @since 1.0.0
	 * @return bool True if completed
	 */
	public function is_completed() {
		return (bool) get_option( self::OPTION_COMPLETED, false );
	}
}
