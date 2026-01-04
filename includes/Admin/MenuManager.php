<?php
/**
 * Menu Manager Class
 *
 * Handles all admin menu registration and navigation for the plugin.
 * Provides centralized menu management with capability-based access control.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Admin
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Admin;

use ShahiLegalFlowSuite\Core\Security;
use ShahiLegalFlowSuite\Admin\Consent;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Manager Class
 *
 * Registers admin menus, submenus, and manages navigation structure.
 * Implements capability-based access control for all menu items.
 *
 * @since 1.0.0
 */
class MenuManager {

	/**
	 * Main menu slug
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const MENU_SLUG = 'shahi-legalflowsuite';

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Dashboard page instance
	 *
	 * @since 1.0.0
	 * @var Dashboard
	 */
	private $dashboard;

	/**
	 * Module Dashboard page instance
	 *
	 * @since 1.0.0
	 * @var ModuleDashboard
	 */
	private $module_dashboard;

	/**
	 * DSR Requests page instance
	 *
	 * @since 3.0.1
	 * @var DSRRequests
	 */
	private $dsr_requests;

	/**
	 * DSR Reports page instance
	 *
	 * @since 3.0.1
	 * @var DSRReports
	 */
	private $dsr_reports;

	/**
	 * DSR Request Detail page instance
	 *
	 * @since 3.0.1
	 * @var DSRRequestDetail
	 */
	private $dsr_detail;

	/**
	 * Settings page instance
	 *
	 * @since 1.0.0
	 * @var Settings
	 */
	private $settings;

	/**
	 * Consent manager page instance
	 *
	 * @since 3.0.1
	 * @var Consent
	 */
	private $consent;

	/**
	 * Initialize the menu manager
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->security = new Security();

		// Initialize ModuleDashboard eagerly because it has AJAX handlers
		// that need to be registered before any AJAX requests
		$this->module_dashboard = new ModuleDashboard();

		// Other page controllers are initialized lazily when needed (see get_* methods)
	}

	/**
	 * Get or create Dashboard instance
	 *
	 * @since 3.0.1
	 * @return Dashboard
	 */
	private function get_dashboard() {
		if ( null === $this->dashboard ) {
			$this->dashboard = new Dashboard();
		}
		return $this->dashboard;
	}

	/**
	 * Get or create ModuleDashboard instance
	 *
	 * @since 3.0.1
	 * @return ModuleDashboard
	 */
	private function get_module_dashboard() {
		// ModuleDashboard is already initialized in constructor
		return $this->module_dashboard;
	}

	/**
	 * Get or create Settings instance
	 *
	 * @since 3.0.1
	 * @return Settings
	 */
	private function get_settings() {
		if ( null === $this->settings ) {
			$this->settings = new Settings();
		}
		return $this->settings;
	}

	/**
	 * Get or create DSRRequests instance
	 *
	 * @since 3.0.1
	 * @return DSRRequests
	 */
	private function get_dsr_requests() {
		if ( null === $this->dsr_requests ) {
			$this->dsr_requests = new DSRRequests();
		}
		return $this->dsr_requests;
	}

	/**
	 * Get or create DSRReports instance
	 *
	 * @since 3.0.1
	 * @return DSRReports
	 */
	private function get_dsr_reports() {
		if ( null === $this->dsr_reports ) {
			$this->dsr_reports = new DSRReports();
		}
		return $this->dsr_reports;
	}

	/**
	 * Get or create DSRRequestDetail instance
	 *
	 * @since 3.0.1
	 * @return DSRRequestDetail
	 */
	private function get_dsr_detail() {
		if ( null === $this->dsr_detail ) {
			$this->dsr_detail = new DSRRequestDetail();
		}
		return $this->dsr_detail;
	}

	/**
	 * Get or create Consent instance
	 *
	 * @since 3.0.1
	 * @return Consent
	 */
	private function get_consent() {
		if ( null === $this->consent ) {
			$this->consent = new Consent();
		}
		return $this->consent;
	}

	/**
	 * Register all admin menus
	 *
	 * Simplified menu structure with 4 core items.
	 * Module-specific pages are registered by their respective modules.
	 *
	 * @since 1.0.0
	 * @since 3.0.2 Refactored to clean menu structure (4 core + module pages)
	 * @return void
	 */
	public function register_menus() {
		// Main menu - Dashboard (default page)
		add_menu_page(
			__( 'SLOS Dashboard', 'shahi-legalflowsuite' ),
			__( 'SLOS', 'shahi-legalflowsuite' ),
			'manage_shahi_template',
			self::MENU_SLUG,
			array( $this->get_dashboard(), 'render' ),
			$this->get_menu_icon(),
			30
		);

		// Remove the auto-generated first submenu item (prevents "All Items" from showing)
		// We'll add Dashboard explicitly below
		remove_submenu_page( self::MENU_SLUG, self::MENU_SLUG );

		// Dashboard submenu (explicit registration to avoid "All Items")
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'shahi-legalflowsuite' ),
			'📊 ' . __( 'Dashboard', 'shahi-legalflowsuite' ),
			'manage_shahi_template',
			self::MENU_SLUG,
			array( $this->get_dashboard(), 'render' )
		);

		// Module-specific pages are registered by their respective module classes at priority 20
		// They will appear here: Requests, Compliance, Documents, Accessibility
		// See: DSR_Portal, ConsentManagement, LegalDocs, AccessibilityScanner modules

		// Modules submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Modules', 'shahi-legalflowsuite' ),
			'🧩 ' . __( 'Modules', 'shahi-legalflowsuite' ),
			'manage_shahi_modules',
			self::MENU_SLUG . '-modules',
			array( $this->get_module_dashboard(), 'render' )
		);

		// Settings submenu
		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'shahi-legalflowsuite' ),
			'⚙️ ' . __( 'Settings', 'shahi-legalflowsuite' ),
			'edit_shahi_settings',
			self::MENU_SLUG . '-settings',
			array( $this->get_settings(), 'render' )
		);

		// Module-specific pages are now registered by their respective module classes
		// See: DSR_Portal, ConsentManagement, LegalDocs, AccessibilityScanner modules

		// Hidden utility pages (not visible in menu, accessible via direct URL)

		// DSR Request Detail page (hidden)
		add_submenu_page(
			null,
			__( 'DSR Request Detail', 'shahi-legalflowsuite' ),
			__( 'DSR Request Detail', 'shahi-legalflowsuite' ),
			'slos_manage_dsr',
			self::MENU_SLUG . '-dsr-detail',
			array( $this->get_dsr_detail(), 'render' )
		);

		// Banner Settings page (hidden, linked from Compliance module)
		add_submenu_page(
			null,
			__( 'Banner Settings', 'shahi-legalflowsuite' ),
			__( 'Banner Settings', 'shahi-legalflowsuite' ),
			'edit_shahi_settings',
			self::MENU_SLUG . '-banner-settings',
			array( $this->get_settings(), 'render_banner_settings' )
		);

		// Debug Onboarding page (hidden, only when WP_DEBUG is true)
		if ( current_user_can( 'manage_options' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			add_submenu_page(
				null,
				__( 'Debug Onboarding', 'shahi-legalflowsuite' ),
				__( 'Debug Onboarding', 'shahi-legalflowsuite' ),
				'manage_options',
				self::MENU_SLUG . '-debug-onboarding',
				function () {
					include SHAHI_LEGALFLOWSUITE_PATH . 'debug-onboarding.php';
				}
			);
		}
	}

	/**
	 * Add custom capabilities
	 *
	 * This method should be called on plugin activation to add
	 * custom capabilities to administrator role.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_capabilities() {
		$role = get_role( 'administrator' );

		if ( $role ) {
			$capabilities = array(
				'manage_shahi_template',
				'manage_shahi_modules',
				'edit_shahi_settings',
				'slos_manage_dsr',
			);

			foreach ( $capabilities as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove custom capabilities
	 *
	 * This method should be called on plugin deactivation to remove
	 * custom capabilities from administrator role.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function remove_capabilities() {
		$role = get_role( 'administrator' );

		if ( $role ) {
			$capabilities = array(
				'manage_shahi_template',
				'manage_shahi_modules',
				'edit_shahi_settings',
				'slos_manage_dsr',
			);

			foreach ( $capabilities as $cap ) {
				$role->remove_cap( $cap );
			}
		}
	}

	/**
	 * Get menu icon SVG (base64 encoded)
	 *
	 * Returns a custom SVG icon for the main menu item.
	 * The icon is a futuristic geometric shape that matches the dark theme.
	 *
	 * @since 1.0.0
	 * @return string Base64 encoded SVG icon
	 */
	private function get_menu_icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
            <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="currentColor" opacity="0.3"/>
            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Get current admin page
	 *
	 * Determines which page is currently being viewed based on the
	 * page parameter in the URL.
	 *
	 * @since 1.0.0
	 * @return string Current page slug or empty string
	 */
	public function get_current_page() {
		if ( ! is_admin() ) {
			return '';
		}

		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		// Check if it's one of our pages
		$valid_pages = array(
			self::MENU_SLUG,
			self::MENU_SLUG . '-modules',
			self::MENU_SLUG . '-settings',
			self::MENU_SLUG . '-support',
			// Module pages (registered by modules themselves)
			'slos-requests',
			'slos-compliance',
			'slos-documents',
			'slos-accessibility',
		);

		return in_array( $page, $valid_pages, true ) ? $page : '';
	}

	/**
	 * Check if current page is a plugin page
	 *
	 * @since 1.0.0
	 * @return bool True if on a plugin page, false otherwise
	 */
	public function is_plugin_page() {
		return ! empty( $this->get_current_page() );
	}

	/**
	 * Highlight parent menu item
	 *
	 * Ensures the main menu item is highlighted when on any submenu page.
	 *
	 * @since 1.0.0
	 * @param string $parent_file The parent file.
	 * @return string Modified parent file
	 */
	public function highlight_menu( $parent_file ) {
		if ( $this->is_plugin_page() ) {
			$parent_file = self::MENU_SLUG;
		}

		return $parent_file;
	}

	/**
	 * Highlight submenu item
	 *
	 * Ensures the correct submenu item is highlighted on plugin pages.
	 *
	 * @since 3.0.1
	 * @param string $submenu_file The submenu file.
	 * @return string Modified submenu file
	 */
	public function highlight_submenu( $submenu_file ) {
		$current_page = $this->get_current_page();

		if ( $current_page && $this->is_plugin_page() ) {
			$submenu_file = $current_page;
		}

		return $submenu_file;
	}

	/**
	 * Add admin body classes
	 *
	 * Adds custom CSS classes to the admin body for styling purposes.
	 *
	 * @since 1.0.0
	 * @param string $classes Existing body classes.
	 * @return string Modified body classes
	 */
	public function add_body_classes( $classes ) {
		if ( $this->is_plugin_page() ) {
			// Add plugin-specific class
			$classes .= ' shahi-legalflowsuite-admin';
			
			// Add page-specific class
			$page     = $this->get_current_page();
			$classes .= ' shahi-page-' . str_replace( self::MENU_SLUG . '-', '', $page );
			
			// Add SLOS admin page class for background patterns (Phase 7)
			$classes .= ' slos-admin-page';
		}

		return $classes;
	}

	/**
	 * Get breadcrumb navigation
	 *
	 * Generates breadcrumb navigation for the current page.
	 *
	 * @since 1.0.0
	 * @return array Breadcrumb items
	 */
	public function get_breadcrumbs() {
		$breadcrumbs  = array();
		$current_page = $this->get_current_page();

		// Always start with Dashboard
		$breadcrumbs[] = array(
			'title'  => __( 'Dashboard', 'shahi-legalflowsuite' ),
			'url'    => admin_url( 'admin.php?page=' . self::MENU_SLUG ),
			'active' => $current_page === self::MENU_SLUG,
		);

		// Add current page if not dashboard
		if ( $current_page !== self::MENU_SLUG ) {
			$page_titles = array(
				self::MENU_SLUG . '-modules'  => __( 'Modules', 'shahi-legalflowsuite' ),
				self::MENU_SLUG . '-settings' => __( 'Settings', 'shahi-legalflowsuite' ),
				self::MENU_SLUG . '-support'  => __( 'Support & Docs', 'shahi-legalflowsuite' ),
				// Module pages
				'slos-requests'               => __( 'Requests', 'shahi-legalflowsuite' ),
				'slos-compliance'             => __( 'Compliance', 'shahi-legalflowsuite' ),
				'slos-documents'              => __( 'Documents', 'shahi-legalflowsuite' ),
				'slos-accessibility'          => __( 'Accessibility', 'shahi-legalflowsuite' ),
			);

			if ( isset( $page_titles[ $current_page ] ) ) {
				$breadcrumbs[] = array(
					'title'  => $page_titles[ $current_page ],
					'url'    => admin_url( 'admin.php?page=' . $current_page ),
					'active' => true,
				);
			}
		}

		return $breadcrumbs;
	}

	/**
	 * Render breadcrumb navigation
	 *
	 * Outputs the breadcrumb navigation HTML.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_breadcrumbs() {
		$breadcrumbs = $this->get_breadcrumbs();

		if ( empty( $breadcrumbs ) ) {
			return;
		}

		echo '<nav class="shahi-breadcrumbs">';
		echo '<ul class="shahi-breadcrumb-list">';

		foreach ( $breadcrumbs as $index => $item ) {
			$class = $item['active'] ? 'active' : '';
			echo '<li class="shahi-breadcrumb-item ' . esc_attr( $class ) . '">';

			if ( $item['active'] ) {
				echo '<span>' . esc_html( $item['title'] ) . '</span>';
			} else {
				echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a>';
			}

			if ( $index < count( $breadcrumbs ) - 1 ) {
				echo '<span class="shahi-breadcrumb-separator">/</span>';
			}

			echo '</li>';
		}

		echo '</ul>';
		echo '</nav>';
	}
}
