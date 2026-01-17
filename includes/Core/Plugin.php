<?php
/**
 * Main Plugin Class
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Core
 * @license    GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Core;

use function add_action;
use function get_current_user_id;
use function get_role;
use function is_multisite;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 *
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks
	 *
	 * @since 1.0.0
	 * @var Loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $version;

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->plugin_name = 'shahi-legalflowsuite';
		$this->version     = SHAHI_LEGALFLOWSUITE_VERSION;

		$this->loader = new Loader();

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function load_dependencies() {
		// Dependencies are auto-loaded via PSR-4 autoloader.
		// Additional manual requires can be added here if needed.
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function set_locale() {
		// Load translations immediately since we're already in plugins_loaded hook.
		\ShahiLegalFlowSuite\Core\I18n::load_plugin_textdomain();
	}

	/**
	 * Ensure capabilities exist
	 *
	 * Checks if custom capabilities are present and adds them if missing.
	 * This ensures capabilities work even if plugin was activated before capabilities were added.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function ensure_capabilities() {
		$role = get_role( 'administrator' );

		if ( $role && ( ! $role->has_cap( 'manage_shahi_template' ) || ! $role->has_cap( 'slos_manage_dsr' ) || ! $role->has_cap( 'manage_shahi_modules' ) ) ) {
			\ShahiLegalFlowSuite\Admin\MenuManager::add_capabilities();
		}
	}

	/**
	 * Define admin-specific hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_admin_hooks() {
		// Ensure capabilities exist even if added post-activation.
		$this->ensure_capabilities();

		// Initialize Assets Manager.
		$assets = new Assets();
		$this->loader->add_action( 'admin_enqueue_scripts', $assets, 'enqueue_admin_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $assets, 'enqueue_admin_scripts' );

		// Initialize Menu Manager.
		$menu_manager = new \ShahiLegalFlowSuite\Admin\MenuManager();
		$this->loader->add_action( 'admin_menu', $menu_manager, 'register_menus' );
		$this->loader->add_filter( 'parent_file', $menu_manager, 'highlight_menu' );
		$this->loader->add_filter( 'submenu_file', $menu_manager, 'highlight_submenu' );
		$this->loader->add_filter( 'admin_body_class', $menu_manager, 'add_body_classes' );

		// Initialize Network Compliance Dashboard (multisite only).
		if ( is_multisite() ) {
			$network_dashboard = new \ShahiLegalFlowSuite\Admin\Network_Compliance_Dashboard();
			$network_dashboard->init();
		}

		// Initialize Onboarding.
		$onboarding = new \ShahiLegalFlowSuite\Admin\Onboarding();
		$this->loader->add_action( 'admin_footer', $onboarding, 'render_modal' );
		$this->loader->add_action( 'wp_ajax_shahi_save_onboarding', $onboarding, 'save_onboarding' );
		$this->loader->add_action( 'wp_ajax_shahi_skip_onboarding', $onboarding, 'skip_onboarding' );

		// Defer initialization of managers that register hooks in their constructors.
		// These will self-initialize when their hooks fire.
		add_action(
			'init',
			function () {
				// REST API.
				new \ShahiLegalFlowSuite\API\RestAPI();

				// AJAX Handler.
				new \ShahiLegalFlowSuite\Ajax\AjaxHandler();

				// Post Type Manager.
				new \ShahiLegalFlowSuite\PostTypes\PostTypeManager();

				// Widget Manager.
				new \ShahiLegalFlowSuite\Widgets\WidgetManager();

				// Shortcode Manager.
				new \ShahiLegalFlowSuite\Shortcodes\ShortcodeManager();

				// Cron Manager.
				new Cron();
			},
			5
		);

		// Initialize Module Manager (singleton, can be called early).
		\ShahiLegalFlowSuite\Modules\ModuleManager::get_instance();

		// Initialize Consent UX Auto-Scanner (Phase 2.3).
		add_action(
			'init',
			function () {
				// Load the ConsentUxAutoScanner which has instantiation at end of file.
				require_once SHAHI_LEGALFLOWSUITE_PATH . 'includes/Modules/AccessibilityScanner/ConsentUxAutoScanner.php';
			},
			10
		);
	}

	/**
	 * Define public-facing hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function define_public_hooks() {
		// Initialize DSR audit service (must be early to catch all events).
		add_action(
			'init',
			function () {
				$audit_repository = new \ShahiLegalFlowSuite\Database\Repositories\DSR_Audit_Log_Repository();
				$audit_service    = new \ShahiLegalFlowSuite\Services\DSR_Audit_Service( $audit_repository );

				// Hook into DSR lifecycle events for automatic logging.
				add_action(
					'slos_dsr_submitted',
					function ( $request_id, $data ) use ( $audit_service ) {
						$audit_service->log_submission( $request_id, $data['email'] ?? '', $data['request_type'] ?? '' );
					},
					10,
					2
				);

				add_action(
					'slos_dsr_status_changed',
					function ( $request_id, $old_status, $new_status ) use ( $audit_service ) {
						$actor_id = get_current_user_id();
						$audit_service->log_status_change( $request_id, $old_status, $new_status, $actor_id );
					},
					10,
					3
				);
			},
			8
		); // Priority 8 to run before other DSR services

		// Initialize DSR email notifications service.
		add_action(
			'init',
			function () {
				new \ShahiLegalFlowSuite\Services\DSR_Email_Service();
			},
			9
		);

		// Initialize DSR export service.
		add_action(
			'init',
			function () {
				$export_service = new \ShahiLegalFlowSuite\Services\DSR_Export_Service();
				// Register download handler.
				add_action( 'template_redirect', array( $export_service, 'handle_download_request' ), 1 );
			},
			9
		);

		// Initialize DSR erasure service.
		add_action(
			'init',
			function () {
				new \ShahiLegalFlowSuite\Services\DSR_Erasure_Service();
			},
			9
		);

		// Initialize DSR report service (compliance reporting with cron).
		add_action(
			'init',
			function () {
				new \ShahiLegalFlowSuite\Services\DSR_Report_Service();
			},
			9
		);

		// Public assets (will be added in Phase 1.5).
		// $assets = new Assets();
		// $this->loader->add_action('wp_enqueue_scripts', $assets, 'enqueue_public_assets');
	}

	/**
	 * Run the loader to execute all hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Get the plugin name
	 *
	 * @since 1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Get the version number
	 *
	 * @since 1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the loader
	 *
	 * @since 1.0.0
	 * @return Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
}
