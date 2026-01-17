<?php
/**
 * Accessibility Scanner Module
 *
 * Core wiring for the Accessibility Scanner: registers checks, hooks, and AJAX
 * endpoints by delegating to dedicated handler classes. Business logic lives in
 * services and handlers to keep this class lean and WordPress-compliant.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use ShahiLegalFlowSuite\Admin\AccessibilityMainPage;
use ShahiLegalFlowSuite\Modules\Module;
use ShahiLegalFlowSuite\Modules\ModuleManager;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Admin\AccessibilitySettings;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Handlers\BackupAjaxHandler;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Handlers\ConfigAjaxHandler;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Handlers\FixAjaxHandler;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Handlers\ScanAjaxHandler;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Handlers\StatementAjaxHandler;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Handlers\ToolsAjaxHandler;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Scanner\ScannerEngine;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\BackupService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\ConfigService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\FixService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\ReportingService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\ScanningService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services\StatementService;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Widget\AccessibilityWidget;

/**
 * Accessibility Scanner Module Class
 */
class AccessibilityScanner extends Module {

	/**
	 * Core scanner engine.
	 *
	 * @var ScannerEngine
	 */
	private $scanner;

	/**
	 * Backup service.
	 *
	 * @var BackupService
	 */
	private $backup_service;

	/**
	 * Scanning service.
	 *
	 * @var ScanningService
	 */
	private $scanning_service;

	/**
	 * Statement service.
	 *
	 * @var StatementService
	 */
	private $statement_service;

	/**
	 * Fix service.
	 *
	 * @var FixService
	 */
	private $fix_service;

	/**
	 * Reporting service.
	 *
	 * @var ReportingService
	 */
	private $reporting_service;

	/**
	 * Config service.
	 *
	 * @var ConfigService
	 */
	private $config_service;

	/**
	 * Scan AJAX handler.
	 *
	 * @var ScanAjaxHandler
	 */
	private $scan_ajax_handler;

	/**
	 * Fix AJAX handler.
	 *
	 * @var FixAjaxHandler
	 */
	private $fix_ajax_handler;

	/**
	 * Statement AJAX handler.
	 *
	 * @var StatementAjaxHandler
	 */
	private $statement_ajax_handler;

	/**
	 * Backup AJAX handler.
	 *
	 * @var BackupAjaxHandler
	 */
	private $backup_ajax_handler;

	/**
	 * Config AJAX handler.
	 *
	 * @var ConfigAjaxHandler
	 */
	private $config_ajax_handler;

	/**
	 * Tools AJAX handler.
	 *
	 * @var ToolsAjaxHandler
	 */
	private $tools_ajax_handler;

	/**
	 * Get module unique key.
	 *
	 * @return string
	 */
	public function get_key() {
		return 'accessibility-scanner';
	}

	/**
	 * Get module name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'Accessibility Scanner Pro';
	}

	/**
	 * Get module description.
	 *
	 * @return string
	 */
	public function get_description() {
		return 'Automated accessibility scanning engine with real-time checks and compliance reporting.';
	}

	/**
	 * Get module icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dashicons-universal-access';
	}

	/**
	 * Get module category.
	 *
	 * @return string
	 */
	public function get_category() {
		return 'compliance';
	}

	/**
	 * Get module settings URL.
	 *
	 * @return string
	 */
	public function get_settings_url() {
		return admin_url( 'admin.php?page=slos-accessibility-settings' );
	}

	/**
	 * Initialize module wiring.
	 *
	 * @return void
	 */
	public function init() {
		$this->scanner           = new ScannerEngine();
		$this->backup_service    = new BackupService();
		$this->scanning_service  = new ScanningService( $this->scanner );
		$this->statement_service = new StatementService();
		$this->fix_service       = new FixService();
		$this->reporting_service = new ReportingService();
		$this->config_service    = new ConfigService( $this->scanner );

		$this->scan_ajax_handler      = new ScanAjaxHandler( $this->scanner, $this->scanning_service );
		$this->fix_ajax_handler       = new FixAjaxHandler( $this->scanner, $this->scanning_service, $this->fix_service, $this->backup_service );
		$this->statement_ajax_handler = new StatementAjaxHandler( $this->statement_service );
		$this->backup_ajax_handler    = new BackupAjaxHandler( $this->scanner, $this->backup_service, $this->scanning_service );
		$this->config_ajax_handler    = new ConfigAjaxHandler( $this->config_service, $this->reporting_service );
		$this->tools_ajax_handler     = new ToolsAjaxHandler();

		$this->register_checks();

		// Initialize widget.
		( new AccessibilityWidget() )->init();

		// Initialize settings UI.
		if ( is_admin() ) {
			( new AccessibilitySettings() )->init();
			add_action( 'admin_menu', array( $this, 'register_admin_menus' ), 20 );
		}

		add_action( 'save_post', array( $this, 'run_scan_on_save' ), 10, 3 );
		add_action( 'add_meta_boxes', array( $this, 'add_scan_meta_box' ) );

		// AJAX handlers routed to dedicated classes.
		add_action( 'wp_ajax_slos_get_posts_to_scan', array( $this->scan_ajax_handler, 'ajax_get_posts_to_scan' ) );
		add_action( 'wp_ajax_slos_scan_single_post', array( $this->scan_ajax_handler, 'ajax_scan_single_post' ) );
		add_action( 'wp_ajax_slos_generate_alt_text', array( $this->tools_ajax_handler, 'ajax_generate_alt_text' ) );
		add_action( 'wp_ajax_slos_generate_statement', array( $this->statement_ajax_handler, 'ajax_generate_statement' ) );

		if ( ! ( defined( 'SLOS_DORMANT_AUTOFIX' ) && SLOS_DORMANT_AUTOFIX ) ) {
			add_action( 'wp_ajax_slos_fix_single_issue', array( $this->fix_ajax_handler, 'ajax_fix_single_issue' ) );
			add_action( 'wp_ajax_slos_fix_all_issues', array( $this->fix_ajax_handler, 'ajax_fix_all_issues' ) );
			add_action( 'wp_ajax_slos_toggle_autofix', array( $this->fix_ajax_handler, 'ajax_toggle_autofix' ) );
			add_action( 'wp_ajax_slos_autofix_single', array( $this->fix_ajax_handler, 'ajax_autofix_single_fixer' ) );
			add_action( 'wp_ajax_slos_rollback_fixes', array( $this->backup_ajax_handler, 'ajax_rollback_fixes' ) );
			add_action( 'wp_ajax_slos_check_backup_exists', array( $this->backup_ajax_handler, 'ajax_check_backup_exists' ) );
		}

		add_action( 'wp_ajax_slos_get_page_issues', array( $this->scan_ajax_handler, 'ajax_get_page_issues' ) );
		add_action( 'wp_ajax_slos_get_page_fixable_issues', array( $this->fix_ajax_handler, 'ajax_get_page_fixable_issues' ) );
		add_action( 'wp_ajax_slos_run_full_scan', array( $this->scan_ajax_handler, 'ajax_run_full_scan' ) );
		add_action( 'wp_ajax_slos_consolidate_scan_results', array( $this->scan_ajax_handler, 'ajax_consolidate_scan_results' ) );
		add_action( 'wp_ajax_slos_audit_media_library', array( $this->scan_ajax_handler, 'ajax_audit_media_library' ) );
		add_action( 'wp_ajax_slos_publish_statement', array( $this->statement_ajax_handler, 'ajax_publish_statement' ) );
		add_action( 'wp_ajax_slos_get_detailed_scan_report', array( $this->scan_ajax_handler, 'ajax_get_detailed_scan_report' ) );
		add_action( 'wp_ajax_slos_save_scanner_config', array( $this->config_ajax_handler, 'ajax_save_scanner_config' ) );
		add_action( 'wp_ajax_slos_schedule_email_report', array( $this->config_ajax_handler, 'ajax_schedule_email_report' ) );
		add_action( 'wp_ajax_slos_toggle_widget', array( $this->config_ajax_handler, 'ajax_toggle_widget' ) );
		add_action( 'wp_ajax_slos_save_widget_config', array( $this->config_ajax_handler, 'ajax_save_widget_config' ) );
		add_action( 'wp_ajax_slos_check_color_contrast', array( $this->tools_ajax_handler, 'ajax_check_color_contrast' ) );
		add_action( 'wp_ajax_slos_check_readability', array( $this->tools_ajax_handler, 'ajax_check_readability' ) );
		add_action( 'wp_ajax_slos_check_link_text', array( $this->tools_ajax_handler, 'ajax_check_link_text' ) );

		// Schedule cleanup cron job if not already scheduled.
		if ( ! wp_next_scheduled( 'slos_cleanup_old_backups' ) ) {
			wp_schedule_event( time(), 'daily', 'slos_cleanup_old_backups' );
		}
		add_action( 'slos_cleanup_old_backups', array( $this, 'cron_cleanup_old_backups' ) );

		// Schedule periodic accessibility email reports (daily driver, frequency handled in callback).
		if ( ! wp_next_scheduled( 'slos_send_accessibility_report' ) ) {
			wp_schedule_event( time(), 'daily', 'slos_send_accessibility_report' );
		}
		add_action( 'slos_send_accessibility_report', array( $this, 'cron_send_accessibility_report' ) );
	}

	/**
	 * Check if user has permission to manage accessibility.
	 *
	 * @return bool
	 */
	public function user_can_manage_accessibility() {
		$capability = apply_filters( 'slos_accessibility_capability', 'manage_options' );

		return current_user_can( $capability );
	}

	/**
	 * Register admin menus.
	 *
	 * @return void
	 */
	public function register_admin_menus() {
		$module_manager       = ModuleManager::get_instance();
		$accessibility_module = $module_manager->get_module( 'accessibility-scanner' );

		if ( ! $accessibility_module || ! $accessibility_module->is_enabled() ) {
			return;
		}

		add_submenu_page(
			'shahi-legalflowsuite',
			__( 'Accessibility Scanner', 'shahi-legalflowsuite' ),
			'♿ ' . __( 'Accessibility', 'shahi-legalflowsuite' ),
			'manage_options',
			'slos-accessibility',
			array( $this, 'render_main_page' )
		);

		add_submenu_page(
			null,
			__( 'Accessibility Settings', 'shahi-legalflowsuite' ),
			__( 'Accessibility Settings', 'shahi-legalflowsuite' ),
			'manage_options',
			'slos-accessibility-settings',
			array( new AccessibilitySettings(), 'render' )
		);
	}

	/**
	 * Render main tabbed page.
	 *
	 * @return void
	 */
	public function render_main_page() {
		( new AccessibilityMainPage() )->render();
	}

	/**
	 * Run scan when post is saved.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @param bool     $update  Whether this is an update.
	 *
	 * @return void
	 */
	public function run_scan_on_save( $post_id, $post, $update ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'revision' === $post->post_type ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$content = $post->post_content;
		$results = $this->scanner->scan( $content );

		update_post_meta( $post_id, '_slos_accessibility_scan_results', $results );
		update_post_meta( $post_id, '_slos_accessibility_scan_date', current_time( 'mysql' ) );
	}

	/**
	 * Add meta box to post editor.
	 *
	 * @return void
	 */
	public function add_scan_meta_box() {
		add_meta_box(
			'slos_accessibility_scan_results',
			__( 'Accessibility Scan Results', 'shahi-legalflowsuite' ),
			array( $this, 'render_scan_meta_box' ),
			array( 'post', 'page' ),
			'side',
			'high'
		);
	}

	/**
	 * Render meta box content.
	 *
	 * @param \WP_Post $post Current post object.
	 *
	 * @return void
	 */
	public function render_scan_meta_box( $post ) {
		$results   = get_post_meta( $post->ID, '_slos_accessibility_scan_results', true );
		$last_scan = get_post_meta( $post->ID, '_slos_accessibility_scan_date', true );

		echo '<div class="slos-accessibility-results">';
		if ( $last_scan ) {
			echo '<p><strong>' . esc_html__( 'Last Scan:', 'shahi-legalflowsuite' ) . '</strong> ' . esc_html( $last_scan ) . '</p>';
		}

		if ( empty( $results ) ) {
			echo '<p style="color: green;">' . esc_html__( 'No accessibility issues found!', 'shahi-legalflowsuite' ) . '</p>';
		} else {
			echo '<ul style="list-style: none; padding: 0;">';
			foreach ( $results as $result ) {
				$severity = isset( $result['severity'] ) ? $result['severity'] : 'warning';
				$color    = ( 'critical' === $severity ) ? '#d63638' : '#dba617';
				echo '<li style="margin-bottom: 10px; border-left: 4px solid ' . esc_attr( $color ) . '; padding-left: 10px;">';
				echo '<strong>' . esc_html( $result['description'] ?? '' ) . '</strong>';
				if ( ! empty( $result['issues'] ) && is_array( $result['issues'] ) ) {
					echo '<ul style="margin-top: 5px; padding-left: 15px;">';
					foreach ( $result['issues'] as $issue ) {
						echo '<li>' . esc_html( $issue['message'] ?? '' ) . '</li>';
					}
					echo '</ul>';
				}
				echo '</li>';
			}
			echo '</ul>';
		}
		echo '</div>';
	}

	/**
	 * Register scanner checks based on active settings.
	 *
	 * @return void
	 */
	private function register_checks() {
		$active_checkers = get_option( 'slos_active_checkers', array() );
		$check_mapping   = $this->config_service->get_check_mapping();

		if ( empty( $active_checkers ) ) {
			$active_checkers = array_keys( $check_mapping );
			update_option( 'slos_active_checkers', $active_checkers );
		}

		foreach ( $active_checkers as $checker_key ) {
			if ( isset( $check_mapping[ $checker_key ] ) ) {
				$check_class = $check_mapping[ $checker_key ];
				if ( class_exists( $check_class ) ) {
					$this->scanner->register_check( new $check_class() );
				}
			}
		}
	}

	/**
	 * Cron callback: send periodic accessibility email report.
	 *
	 * @return void
	 */
	public function cron_send_accessibility_report() {
		if ( ! $this->reporting_service instanceof ReportingService ) {
			$this->reporting_service = new ReportingService();
		}

		$this->reporting_service->send_accessibility_report();
	}

	/**
	 * Cron job to cleanup old backups (TTL 7 days).
	 *
	 * @return void
	 */
	public function cron_cleanup_old_backups() {
		$count = $this->backup_service->cleanup_old_backups( 7 );

		if ( $count > 0 ) {
			do_action( 'slos_accessibility_backups_cleaned', $count );
		}
	}

	/**
	 * Get accessibility statistics for Ops Dashboard.
	 *
	 * @return array
	 */
	public function get_ops_statistics(): array {
		if ( ! $this->reporting_service instanceof ReportingService ) {
			$this->reporting_service = new ReportingService();
		}

		return $this->reporting_service->get_ops_statistics();
	}
}
