<?php
/**
 * Assets Management Class
 *
 * Handles all CSS and JavaScript enqueuing for the plugin.
 * Implements conditional loading strategy to load only necessary assets
 * on each page, improving performance and following WordPress best practices.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Core
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Assets
 *
 * Manages asset enqueuing with conditional loading strategy.
 *
 * @since 1.0.0
 */
class Assets {

	/**
	 * Asset version for cache busting.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $version;

	/**
	 * Whether to load minified assets.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private $use_minified;

	/**
	 * Assets URL base path.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $assets_url;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version      = SHAHI_LEGALFLOWSUITE_VERSION;
		$this->use_minified = ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG;
		$this->assets_url   = SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/';

		// Add filter to prevent style caching during development
		add_filter( 'style_loader_tag', array( $this, 'add_nocache_to_styles' ), 10, 4 );
	}

	/**
	 * Get the current page type being displayed.
	 *
	 * Determines which admin page is currently being viewed based on the hook parameter.
	 *
	 * @since 1.0.0
	 * @param string $hook The current admin page hook.
	 * @return string Page type identifier: 'dashboard', 'analytics', 'settings', 'modules', 'module_dashboard', 'accessibility_dashboard', 'accessibility_scanner', or 'generic'
	 */
	private function get_current_page_type( $hook ) {
		if ( $this->is_dashboard_page( $hook ) ) {
			return 'dashboard';
		} elseif ( $this->is_analytics_page( $hook ) ) {
			return 'analytics';
		} elseif ( $this->is_settings_page( $hook ) ) {
			return 'settings';
		} elseif ( $this->is_modules_page( $hook ) ) {
			return 'modules';
		} elseif ( $this->is_module_dashboard_page( $hook ) ) {
			return 'module_dashboard';
		} elseif ( $this->is_accessibility_dashboard_page( $hook ) ) {
			return 'accessibility_dashboard';
		} elseif ( $this->is_accessibility_scanner_page( $hook ) ) {
			return 'accessibility_scanner';
		} elseif ( $this->is_consent_page( $hook ) ) {
			return 'consent';
		}
		return 'generic';
	}

	/**
	 * Check if page needs component library.
	 *
	 * Determines whether the component library (UI components, animations, utilities)
	 * is needed for the current page type.
	 *
	 * @since 1.0.0
	 * @param string $page_type Page type identifier from get_current_page_type().
	 * @return bool True if components needed, false otherwise
	 */
	private function needs_component_library( $page_type ) {
		return in_array(
			$page_type,
			array(
				'dashboard',
				'analytics',
				'analytics_dashboard',
				'settings',
				'modules',
				'module_dashboard',
				'accessibility_dashboard',
				'accessibility_scanner',
				'consent',
			),
			true
		);
	}

	/**
	 * Check if onboarding should be loaded.
	 *
	 * Determines if the onboarding modal should be loaded based on whether
	 * onboarding has been completed by the user.
	 *
	 * @since 1.0.0
	 * @return bool True if onboarding not completed, false otherwise
	 */
	private function should_load_onboarding() {
		return ! get_option( 'shahi_onboarding_completed' );
	}

	/**
	 * Enqueue admin styles.
	 *
	 * Conditionally loads CSS files based on the current admin page.
	 *
	 * @since 1.0.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_styles( $hook ) {
		// Only load on plugin pages
		if ( ! $this->is_plugin_page( $hook ) ) {
			return;
		}

		$page_type = $this->get_current_page_type( $hook );

		// Global admin styles (loaded on all plugin pages)
		$this->enqueue_style(
			'shahi-admin-global',
			'css/admin-global',
			array(),
			$this->version
		);

		// Inject active theme variables (Neon Aether by default)
		try {
			$theme_css = \ShahiLegalFlowSuite\Core\Theme_Manager::get_instance()->build_css_variables();
			if ( ! empty( $theme_css ) ) {
				wp_add_inline_style( 'shahi-admin-global', $theme_css );
			}
		} catch ( \Throwable $e ) {
			// Fail-safe: do not break admin if theme injection fails
			error_log( 'SLOS Theme variables injection failed: ' . $e->getMessage() );
		}

		// Add inline CSS for admin menu highlighting
		$this->add_admin_menu_style();

		// Component library styles - load only if page needs them
		if ( $this->needs_component_library( $page_type ) ) {
			$this->enqueue_style(
				'shahi-components',
				'css/components',
				array( 'shahi-admin-global' ),
				$this->version
			);

			// Animation library (dependency: components)
			$this->enqueue_style(
				'shahi-animations',
				'css/animations',
				array( 'shahi-admin-global' ),
				$this->version
			);

			// Utility classes (dependency: components)
			$this->enqueue_style(
				'shahi-utilities',
				'css/utilities',
				array( 'shahi-admin-global' ),
				$this->version
			);
		}

		// Onboarding styles - load only if onboarding not completed
		if ( $this->should_load_onboarding() ) {
			$this->enqueue_style(
				'shahi-onboarding',
				'css/onboarding',
				array( 'shahi-components', 'shahi-animations' ),
				$this->version
			);
		}

		// Page-specific styles
		if ( $this->is_dashboard_page( $hook ) ) {
			$this->enqueue_style(
				'shahi-admin-dashboard',
				'css/admin-dashboard',
				array( 'shahi-components' ),
				$this->version
			);
		} elseif ( $this->is_analytics_page( $hook ) ) {
			$this->enqueue_style(
				'shahi-admin-analytics',
				'css/admin-analytics',
				array( 'shahi-components' ),
				$this->version
			);
		} elseif ( $this->is_modules_page( $hook ) ) {
			$this->enqueue_style(
				'shahi-admin-modules',
				'css/admin-modules',
				array( 'shahi-components' ),
				$this->version
			);
		} elseif ( $this->is_module_dashboard_page( $hook ) ) {
			$this->enqueue_style(
				'shahi-admin-module-dashboard',
				'css/admin-module-dashboard',
				array( 'shahi-components' ),
				$this->version
			);
		} elseif ( $this->is_accessibility_dashboard_page( $hook ) ) {
			// Accessibility Dashboard - use the same modern styling as Module Dashboard
			$this->enqueue_style(
				'shahi-admin-module-dashboard',
				'css/admin-module-dashboard',
				array( 'shahi-components' ),
				$this->version
			);

			// Auto-Fix Progress Popup CSS for Dashboard Fix All buttons
			wp_enqueue_style(
				'slos-autofix-progress',
				$this->assets_url . 'css/slos-autofix-progress.css',
				array(),
				$this->version
			);

			// Scan Progress Modal CSS (for dashboard-triggered scans)
			wp_enqueue_style(
				'slos-scan-progress',
				$this->assets_url . 'css/slos-scan-progress.css',
				array(),
				$this->version
			);
		} elseif ( $this->is_settings_page( $hook ) ) {
			$this->enqueue_style(
				'shahi-admin-settings',
				'css/admin-settings',
				array( 'shahi-components' ),
				$this->version
			);

			// Add inline CSS to ensure tabs are visible and bright
			$this->add_settings_tab_style();
		} elseif ( $this->is_accessibility_scanner_page( $hook ) ) {
			// Accessibility Scanner module styles
			$this->enqueue_style(
				'shahi-accessibility-scanner',
				'css/accessibility-scanner/admin',
				array( 'shahi-components' ),
				$this->version
			);

			// Auto-Fix Progress Popup CSS
			wp_enqueue_style(
				'slos-autofix-progress',
				$this->assets_url . 'css/slos-autofix-progress.css',
				array(),
				$this->version
			);

			// Scan Progress Modal CSS
			wp_enqueue_style(
				'slos-scan-progress',
				$this->assets_url . 'css/slos-scan-progress.css',
				array(),
				$this->version
			);
		} elseif ( $this->is_consent_page( $hook ) ) {
			$this->enqueue_style(
				'shahi-admin-consent',
				'css/admin-consent',
				array( 'shahi-components' ),
				$this->version
			);
		}

		// Load RTL styles if needed
		$this->maybe_enqueue_rtl_styles();
	}

	/**
	 * Add inline CSS for settings tabs.
	 *
	 * Ensures tabs are bright and visible with !important declarations.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function add_settings_tab_style() {
		$inline_css = '
        /* Settings tabs - Mac Slate Liquid Theme */
        .shahi-settings-page .shahi-tabs-nav {
            background: #0f172a !important;
            padding: 20px !important;
            border-radius: 12px !important;
            margin-bottom: 20px !important;
        }
        
        .shahi-tab-link,
        .shahi-settings-tab,
        a.shahi-tab-link {
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 14px 24px !important;
            color: #f8fafc !important;
            background: #1e293b !important;
            text-decoration: none !important;
            border-radius: 8px !important;
            font-size: 15px !important;
            font-weight: 500 !important;
            border: 2px solid #334155 !important;
            transition: all 0.3s ease !important;
            margin: 0 4px !important;
        }
        
        .shahi-tab-link .dashicons,
        .shahi-settings-tab .dashicons {
            color: #3b82f6 !important;
            font-size: 20px !important;
        }
        
        .shahi-tab-link:hover,
        .shahi-settings-tab:hover,
        a.shahi-tab-link:hover {
            background: #334155 !important;
            color: #3b82f6 !important;
            border-color: #3b82f6 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2) !important;
        }
        
        .shahi-tab-link.active,
        .shahi-settings-tab.active,
        a.shahi-tab-link.active {
            background: linear-gradient(135deg, #3b82f6 0%, #93c5fd 50%, #ffffff 100%) !important;
            color: #1e3a5f !important;
            font-weight: 700 !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4), 0 0 40px rgba(147, 197, 253, 0.3) !important;
            transform: translateY(-2px) !important;
        }
        
        .shahi-tab-link.active .dashicons,
        .shahi-settings-tab.active .dashicons {
            color: #1e3a5f !important;
        }
        ';

		wp_add_inline_style( 'shahi-admin-settings', $inline_css );
	}

	/**
	 * Add inline CSS for admin menu highlighting.
	 *
	 * Ensures current submenu item is highlighted properly.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function add_admin_menu_style() {
		$inline_css = '
        /* WordPress admin menu highlighting - Mac Slate Liquid */
        #adminmenu .wp-submenu li.current > a,
        #adminmenu .wp-submenu li > a.current {
            color: #3b82f6 !important;
            font-weight: 600 !important;
        }
        #adminmenu .wp-submenu li a:hover {
            color: #3b82f6 !important;
        }
        ';

		wp_add_inline_style( 'shahi-admin-global', $inline_css );
	}

	/**
	 * Add inline script for admin menu highlighting.
	 *
	 * Adds JavaScript to properly highlight the current submenu item.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function add_menu_highlight_script() {
		$inline_script = "
        jQuery(document).ready(function($) {
            // Remove all current classes first to avoid conflicts
            $('#adminmenu .wp-submenu li').removeClass('current');
            $('#adminmenu .wp-submenu li a').removeClass('current');
            
            // Get current page parameter
            var urlParams = new URLSearchParams(window.location.search);
            var currentPage = urlParams.get('page');
            
            if (currentPage) {
                // Find and highlight exact match only
                $('#adminmenu .wp-submenu a').each(function() {
                    var href = $(this).attr('href');
                    if (href && href.indexOf('page=' + currentPage) > -1) {
                        $(this).closest('li').addClass('current');
                        $(this).addClass('current');
                        $(this).parent().addClass('current');
                    }
                });
            }
        });
        ";

		wp_add_inline_script( 'shahi-admin-global', $inline_script );
	}

	/**
	 * Enqueue RTL styles if needed.
	 *
	 * Loads RTL (Right-to-Left) stylesheets for languages like Arabic and Hebrew.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	private function maybe_enqueue_rtl_styles() {
		if ( ! is_rtl() ) {
			return;
		}

		// Enqueue consent module RTL styles
		$this->enqueue_style(
			'shahi-consent-rtl',
			'css/consent-rtl',
			array(),
			$this->version
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * Conditionally loads JavaScript files based on the current admin page.
	 *
	 * @since 1.0.0
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on plugin pages
		if ( ! $this->is_plugin_page( $hook ) ) {
			return;
		}

		$page_type = $this->get_current_page_type( $hook );

		// Global admin scripts (loaded on all plugin pages)
		$this->enqueue_script(
			'shahi-admin-global',
			'js/admin-global',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Add inline script for admin menu highlighting
		$this->add_menu_highlight_script();

		// Localize script with common data
		$this->localize_global_script();

		// Component library - load only if page needs it
		if ( $this->needs_component_library( $page_type ) ) {
			$this->enqueue_script(
				'shahi-components',
				'js/components',
				array( 'jquery', 'shahi-admin-global' ),
				$this->version,
				true
			);
		}

		// Onboarding script - load only if onboarding not completed
		if ( $this->should_load_onboarding() ) {
			$this->enqueue_script(
				'shahi-onboarding',
				'js/onboarding',
				array( 'jquery', 'shahi-components' ),
				$this->version,
				true
			);

			$this->localize_onboarding_script();
		}

		// Page-specific scripts
		if ( $this->is_dashboard_page( $hook ) ) {
			$this->enqueue_script(
				'shahi-admin-dashboard',
				'js/admin-dashboard',
				array( 'jquery', 'shahi-components' ),
				$this->version,
				true
			);

			$this->localize_dashboard_script();
		} elseif ( $this->is_modules_page( $hook ) ) {
			$this->enqueue_script(
				'shahi-admin-modules',
				'js/admin-modules',
				array( 'jquery', 'shahi-components' ),
				$this->version,
				true
			);

			$this->localize_modules_script();
		} elseif ( $this->is_module_dashboard_page( $hook ) ) {
			$this->enqueue_script(
				'shahi-admin-module-dashboard',
				'js/admin-module-dashboard',
				array( 'jquery', 'shahi-components' ),
				$this->version,
				true
			);

			$this->localize_module_dashboard_script();
		} elseif ( $this->is_accessibility_dashboard_page( $hook ) ) {
			// Accessibility Dashboard - use the same scripts as Module Dashboard
			$this->enqueue_script(
				'shahi-admin-module-dashboard',
				'js/admin-module-dashboard',
				array( 'jquery', 'shahi-components' ),
				$this->version,
				true
			);

			// Also add accessibility-specific scripts if needed
			// Force non-minified version (minified version doesn't exist yet)
			wp_enqueue_script(
				'slos-scanner-admin',
				$this->assets_url . 'js/slos-scanner-admin.js',
				array( 'jquery' ),
				$this->version,
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

			// Scan Progress Modal assets (dashboard entry points)
			wp_enqueue_script(
				'slos-scan-progress',
				$this->assets_url . 'js/slos-scan-progress.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			$this->localize_scan_progress_script();

			// Auto-Fix Progress Popup Assets for Dashboard Fix All buttons
			// Force non-minified version (minified version doesn't exist yet)
			wp_enqueue_script(
				'slos-autofix-progress',
				$this->assets_url . 'js/slos-autofix-progress.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			// Localize Auto-Fix Progress with fixer data
			$this->localize_autofix_progress_script();
		} elseif ( $this->is_settings_page( $hook ) ) {
			$this->enqueue_script(
				'shahi-admin-settings',
				'js/admin-settings',
				array( 'jquery', 'shahi-components' ),
				$this->version,
				true
			);

			$this->localize_settings_script();

			// Export/Import functionality
			$this->enqueue_script(
				'shahi-admin-export-import',
				'js/admin-export-import',
				array( 'jquery', 'shahi-admin-settings' ),
				$this->version,
				true
			);

			$this->localize_export_import_script();
		} elseif ( $this->is_consent_page( $hook ) ) {
			$this->enqueue_script(
				'shahi-admin-consent',
				'js/admin-consent',
				array( 'jquery', 'shahi-components' ),
				$this->version,
				true
			);

			$this->localize_consent_script();
		} elseif ( $this->is_accessibility_scanner_page( $hook ) ) {
			// Accessibility Scanner module scripts
			$this->enqueue_script(
				'shahi-accessibility-scanner',
				'js/accessibility-scanner/admin',
				array( 'jquery', 'shahi-components', 'wp-i18n' ),
				$this->version,
				true
			);

			$this->localize_accessibility_scanner_script();

			// Also add slos-scanner-admin for Fix All buttons
			// Force non-minified version (minified version doesn't exist yet)
			wp_enqueue_script(
				'slos-scanner-admin',
				$this->assets_url . 'js/slos-scanner-admin.js',
				array( 'jquery' ),
				$this->version,
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

			// Scan Progress Modal assets
			wp_enqueue_script(
				'slos-scan-progress',
				$this->assets_url . 'js/slos-scan-progress.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			$this->localize_scan_progress_script();

			// Auto-Fix Progress Popup Assets
			// Force non-minified version (minified version doesn't exist yet)
			wp_enqueue_script(
				'slos-autofix-progress',
				$this->assets_url . 'js/slos-autofix-progress.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			$this->localize_autofix_progress_script();
		}
	}

	/**
	 * Enqueue a stylesheet.
	 *
	 * @since 1.0.0
	 * @param string $handle    Name of the stylesheet.
	 * @param string $file      Path to the file (relative to assets directory, without extension).
	 * @param array  $deps      Array of dependency handles.
	 * @param string $version   Version number.
	 * @param string $media     Media type.
	 * @return void
	 */
	private function enqueue_style( $handle, $file, $deps = array(), $version = null, $media = 'all' ) {
		$suffix        = $this->use_minified ? '.min' : '';
		$relative_path = $file . $suffix . '.css';
		$file_url      = $this->assets_url . $relative_path;

		// Aggressive cache-bust: use file modification time + file size for maximum freshness
		if ( ! $version ) {
			$file_path = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'assets/' . $relative_path;
			if ( file_exists( $file_path ) ) {
				// Combine mtime and file size to create unique version
				$version = filemtime( $file_path ) . '.' . filesize( $file_path );
			} else {
				$version = $this->version;
			}
		}

		wp_enqueue_style(
			$handle,
			$file_url,
			$deps,
			$version,
			$media
		);
	}

	/**
	 * Enqueue a script.
	 *
	 * @since 1.0.0
	 * @param string $handle      Name of the script.
	 * @param string $file        Path to the file (relative to assets directory, without extension).
	 * @param array  $deps        Array of dependency handles.
	 * @param string $version     Version number.
	 * @param bool   $in_footer   Whether to enqueue in footer.
	 * @return void
	 */
	private function enqueue_script( $handle, $file, $deps = array(), $version = null, $in_footer = true ) {
		$suffix        = $this->use_minified ? '.min' : '';
		$relative_path = $file . $suffix . '.js';
		$file_url      = $this->assets_url . $relative_path;

		// Aggressive cache-bust: use file modification time + file size for maximum freshness
		if ( ! $version ) {
			$file_path = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'assets/' . $relative_path;
			if ( file_exists( $file_path ) ) {
				// Combine mtime and file size to create unique version
				$version = filemtime( $file_path ) . '.' . filesize( $file_path );
			} else {
				$version = $this->version;
			}
		}

		wp_enqueue_script(
			$handle,
			$file_url,
			$deps,
			$version,
			$in_footer
		);
	}

	/**
	 * Localize global script with common data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_global_script() {
		wp_localize_script(
			'shahi-admin-global',
			'shahiTemplate',
			array(
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'nonce'     => Security::generate_nonce( 'shahi_legalflowsuite_ajax' ),
				'pluginUrl' => SHAHI_LEGALFLOWSUITE_PLUGIN_URL,
				'version'   => SHAHI_LEGALFLOWSUITE_VERSION,
				'i18n'      => array(
					'confirm'        => I18n::translate( 'Are you sure?' ),
					'saving'         => I18n::translate( 'Saving...' ),
					'saved'          => I18n::translate( 'Saved!' ),
					'error'          => I18n::translate( 'An error occurred.' ),
					'success'        => I18n::translate( 'Success' ),
					'loading'        => I18n::translate( 'Loading...' ),
					'delete_confirm' => I18n::translate( 'Are you sure you want to delete this item?' ),
					'cannot_undo'    => I18n::translate( 'This action cannot be undone.' ),
				),
			)
		);
	}

	/**
	 * Localize dashboard script with dashboard-specific data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_dashboard_script() {
		wp_localize_script(
			'shahi-admin-dashboard',
			'shahiDashboard',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => Security::generate_nonce( 'shahi_dashboard' ),
				'refreshNonce' => Security::generate_nonce( 'shahi_refresh_stats' ),
				'i18n'         => array(
					'refresh'   => I18n::translate( 'Refresh' ),
					'noData'    => I18n::translate( 'No data available.' ),
					'total'     => I18n::translate( 'Total' ),
					'today'     => I18n::translate( 'Today' ),
					'thisWeek'  => I18n::translate( 'This Week' ),
					'thisMonth' => I18n::translate( 'This Month' ),
				),
			)
		);
	}

	/**
	 * Localize analytics script with analytics-specific data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_analytics_script() {
		wp_localize_script(
			'shahi-analytics-charts',
			'shahiAnalytics',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => Security::generate_nonce( 'shahi_analytics' ),
				'exportNonce' => Security::generate_nonce( 'shahi_export_analytics' ),
				'i18n'        => array(
					'events'      => I18n::translate( 'Events' ),
					'loading'     => I18n::translate( 'Loading...' ),
					'noData'      => I18n::translate( 'No data available' ),
					'export'      => I18n::translate( 'Export' ),
					'exporting'   => I18n::translate( 'Exporting...' ),
					'exported'    => I18n::translate( 'Export completed' ),
					'exportError' => I18n::translate( 'Export failed' ),
				),
			)
		);
	}

	/**
	 * Localize onboarding script with onboarding-specific data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_onboarding_script() {
		wp_localize_script(
			'shahi-onboarding',
			'shahiOnboardingData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => Security::generate_nonce( 'shahi_onboarding' ),
				'i18n'    => array(
					'saving' => I18n::translate( 'Saving...' ),
					'saved'  => I18n::translate( 'Setup completed successfully!' ),
					'error'  => I18n::translate( 'An error occurred. Please try again.' ),
				),
			)
		);
	}

	/**
	 * Localize modules script with modules-specific data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_modules_script() {
		wp_localize_script(
			'shahi-admin-modules',
			'shahiModulesData',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'toggleNonce' => Security::generate_nonce( 'shahi_toggle_module' ),
				'i18n'        => array(
					'enabling'          => I18n::translate( 'Enabling...' ),
					'disabling'         => I18n::translate( 'Disabling...' ),
					'enabled'           => I18n::translate( 'Module enabled successfully.' ),
					'disabled'          => I18n::translate( 'Module disabled successfully.' ),
					'error'             => I18n::translate( 'An error occurred. Please try again.' ),
					'confirmEnableAll'  => I18n::translate( 'Are you sure you want to enable all modules?' ),
					'confirmDisableAll' => I18n::translate( 'Are you sure you want to disable all modules? Some features may become unavailable.' ),
				),
			)
		);
	}

	/**
	 * Localize module dashboard script with dashboard-specific data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_module_dashboard_script() {
		wp_localize_script(
			'shahi-admin-module-dashboard',
			'shahiModuleDashboard',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => Security::generate_nonce( 'shahi_module_dashboard' ),
				'i18n'    => array(
					'enabling'     => I18n::translate( 'Activating...' ),
					'disabling'    => I18n::translate( 'Deactivating...' ),
					'enabled'      => I18n::translate( 'Module activated successfully.' ),
					'disabled'     => I18n::translate( 'Module deactivated successfully.' ),
					'error'        => I18n::translate( 'An error occurred. Please try again.' ),
					'loading'      => I18n::translate( 'Loading...' ),
					'enabledText'  => I18n::translate( 'Enabled' ),
					'disabledText' => I18n::translate( 'Disabled' ),
					'activeText'   => I18n::translate( 'Active' ),
					'inactiveText' => I18n::translate( 'Inactive' ),
				),
			)
		);
	}

	/**
	 * Localize settings script with settings-specific data.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_settings_script() {
		wp_localize_script(
			'shahi-admin-settings',
			'shahi_settings_vars',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => Security::generate_nonce( 'shahi_settings_ajax' ),
				'i18n'    => array(
					'exporting'     => I18n::translate( 'Exporting settings...' ),
					'importing'     => I18n::translate( 'Importing settings...' ),
					'resetting'     => I18n::translate( 'Resetting settings...' ),
					'success'       => I18n::translate( 'Settings saved successfully.' ),
					'error'         => I18n::translate( 'An error occurred. Please try again.' ),
					'confirmReset'  => I18n::translate( 'Are you sure you want to reset all settings to defaults? This cannot be undone.' ),
					'confirmImport' => I18n::translate( 'This will overwrite your current settings. Continue?' ),
				),
			)
		);
	}

	/**
	 * Localize consent admin script with REST and filter data.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	private function localize_consent_script() {
		$service = new \ShahiLegalFlowSuite\Services\Consent_Service();

		wp_localize_script(
			'shahi-admin-consent',
			'slosConsentAdmin',
			array(
				'restUrl' => rest_url( 'slos/v1' ),
				'routes'  => array(
					'consents' => rest_url( 'slos/v1/consents' ),
					'stats'    => rest_url( 'slos/v1/consents/stats' ),
				),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'filters' => array(
					'types'    => $service->get_allowed_types(),
					'statuses' => $service->get_allowed_statuses(),
				),
				'i18n'    => array(
					'loading'       => I18n::translate( 'Loading…' ),
					'noData'        => I18n::translate( 'No consent data available for this view.' ),
					'error'         => I18n::translate( 'Unable to load consent data. Please retry.' ),
					'exportReady'   => I18n::translate( 'Export is ready' ),
					'filterCleared' => I18n::translate( 'Filters reset' ),
					'lastUpdated'   => I18n::translate( 'Last updated' ),
					'view'          => I18n::translate( 'View' ),
				),
			)
		);
	}

	/**
	 * Localize export/import script with translations.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	private function localize_export_import_script() {
		wp_localize_script(
			'shahi-admin-export-import',
			'slosExportImportI18n',
			array(
				'exporting'       => __( 'Exporting consent records...', 'shahi-legalflowsuite' ),
				'exportSuccess'   => __( 'Export completed successfully!', 'shahi-legalflowsuite' ),
				'selectFile'      => __( 'Please select a file to import.', 'shahi-legalflowsuite' ),
				'invalidFileType' => __( 'Invalid file type. Please upload CSV or JSON file.', 'shahi-legalflowsuite' ),
				'readingFile'     => __( 'Reading file...', 'shahi-legalflowsuite' ),
				'noDataFound'     => __( 'No valid data found in file.', 'shahi-legalflowsuite' ),
				'parseError'      => __( 'Error parsing file', 'shahi-legalflowsuite' ),
				'readError'       => __( 'Error reading file.', 'shahi-legalflowsuite' ),
				'importing'       => __( 'Importing %d records...', 'shahi-legalflowsuite' ),
				'imported'        => __( 'Imported', 'shahi-legalflowsuite' ),
				'skipped'         => __( 'Skipped', 'shahi-legalflowsuite' ),
			)
		);
	}

	/**
	 * Check if current page is a plugin page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool True if plugin page, false otherwise.
	 */
	private function is_plugin_page( $hook ) {
		// Check if hook contains our plugin slug
		if ( strpos( $hook, 'shahi-legalflowsuite' ) !== false ) {
			return true;
		}

		// Check if hook contains slos (module pages)
		if ( strpos( $hook, 'slos-' ) !== false ) {
			return true;
		}

		// Check if it's a top-level plugin page
		$plugin_pages = array(
			'toplevel_page_shahi-legalflowsuite',
			'shahi-legalflowsuite_page_shahi-dashboard',
			'shahi-legalflowsuite_page_shahi-modules',
			'shahi-legalflowsuite_page_shahi-settings',
			'shahi-legalflowsuite_page_shahi-analytics',
			'shahi-legalflowsuite_page_shahi-legalflowsuite-consent',
		);

		return in_array( $hook, $plugin_pages, true );
	}

	/**
	 * Check if current page is the dashboard page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool True if dashboard page, false otherwise.
	 */
	private function is_dashboard_page( $hook ) {
		return strpos( $hook, 'shahi-dashboard' ) !== false ||
				$hook === 'toplevel_page_shahi-legalflowsuite';
	}

	/**
	 * Check if current page is the old modules page (legacy).
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool True if old modules page, false otherwise.
	 */
	private function is_modules_page( $hook ) {
		// Legacy modules page - no longer used, V3 module dashboard is used instead
		return false;
	}

	/**
	 * Check if current page is the module dashboard page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool True if module dashboard page, false otherwise.
	 */
	private function is_module_dashboard_page( $hook ) {
		// Match the modules page (shahi-legalflowsuite-modules) which uses the V3 dashboard
		return strpos( $hook, 'shahi-legalflowsuite-modules' ) !== false || strpos( $hook, 'module-dashboard' ) !== false;
	}

	/**
	 * Check if current page is the analytics page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool True if analytics page, false otherwise.
	 */
	private function is_analytics_page( $hook ) {
		return strpos( $hook, 'shahi-analytics' ) !== false && strpos( $hook, 'analytics-dashboard' ) === false;
	}

	/**
	 * Check if current page is the settings page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool True if settings page, false otherwise.
	 */
	private function is_settings_page( $hook ) {
		return strpos( $hook, 'shahi-settings' ) !== false;
	}

	/**
	 * Check if current page is the consent page.
	 *
	 * @since 3.0.1
	 * @param string $hook Current admin page hook.
	 * @return bool True if consent page, false otherwise.
	 */
	private function is_consent_page( $hook ) {
		return strpos( $hook, 'shahi-legalflowsuite-consent' ) !== false || strpos( $hook, 'slos-consent' ) !== false;
	}

	/**
	 * Get asset URL.
	 *
	 * Helper method to get full URL for an asset.
	 *
	 * @since 1.0.0
	 * @param string $path Path relative to assets directory.
	 * @return string Full URL to the asset.
	 */
	public static function get_asset_url( $path ) {
		return SHAHI_LEGALFLOWSUITE_PLUGIN_URL . 'assets/' . ltrim( $path, '/' );
	}

	/**
	 * Get minified asset suffix.
	 *
	 * Returns '.min' if minified assets should be used, empty string otherwise.
	 *
	 * @since 1.0.0
	 * @return string The suffix for minified assets.
	 */
	public static function get_min_suffix() {
		return ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ? '.min' : '';
	}

	/**
	 * Register additional styles (for manual enqueuing).
	 *
	 * Registers styles without enqueueing them, useful for conditional loading.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_styles() {
		$suffix = $this->use_minified ? '.min' : '';

		// Analytics page styles (future use)
		wp_register_style(
			'shahi-admin-analytics',
			$this->assets_url . 'css/admin-analytics' . $suffix . '.css',
			array( 'shahi-admin-global' ),
			$this->version
		);

		// Onboarding styles (future use)
		wp_register_style(
			'shahi-admin-onboarding',
			$this->assets_url . 'css/admin-onboarding' . $suffix . '.css',
			array( 'shahi-admin-global' ),
			$this->version
		);
	}

	/**
	 * Register additional scripts (for manual enqueuing).
	 *
	 * Registers scripts without enqueueing them, useful for conditional loading.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_scripts() {
		$suffix = $this->use_minified ? '.min' : '';

		// Analytics page scripts (future use)
		wp_register_script(
			'shahi-admin-analytics',
			$this->assets_url . 'js/admin-analytics' . $suffix . '.js',
			array( 'jquery', 'shahi-admin-global' ),
			$this->version,
			true
		);

		// Onboarding scripts (future use)
		wp_register_script(
			'shahi-admin-onboarding',
			$this->assets_url . 'js/admin-onboarding' . $suffix . '.js',
			array( 'jquery', 'shahi-admin-global' ),
			$this->version,
			true
		);
	}

	/**
	 * Enqueue frontend styles.
	 *
	 * Loads styles for the public-facing side (if needed in the future).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_frontend_styles() {
		// Frontend styles will be added if public-facing components are created
		// Currently, this is an admin-only plugin
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * Loads scripts for the public-facing side (if needed in the future).
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_frontend_scripts() {
		// Frontend scripts will be added if public-facing components are created
		// Currently, this is an admin-only plugin
	}

	/**
	 * Add no-cache directive to plugin styles for development.
	 *
	 * Prevents browser and server from caching CSS files during development.
	 *
	 * @since 1.0.0
	 * @param string $tag    The link tag for the enqueued style.
	 * @param string $handle The style's registered handle.
	 * @param string $href   The stylesheet's source URL.
	 * @param string $media  The stylesheet's media attribute.
	 * @return string Modified link tag.
	 */
	public function add_nocache_to_styles( $tag, $handle, $href, $media ) {
		// Only apply to our plugin's styles
		if ( strpos( $handle, 'shahi-' ) === 0 ) {
			// Add unique timestamp to force refresh
			$separator = ( strpos( $href, '?' ) !== false ) ? '&' : '?';
			$href      = $href . $separator . 't=' . time();

			// Rebuild tag with no-cache headers
			$tag = sprintf(
				'<link rel="stylesheet" id="%s-css" href="%s" type="text/css" media="%s" />' . "\n",
				esc_attr( $handle ),
				esc_url( $href ),
				esc_attr( $media )
			);
		}

		return $tag;
	}

	/**
	 * Check if current page is an accessibility scanner page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool True if accessibility scanner page, false otherwise.
	 */
	private function is_accessibility_scanner_page( $hook ) {
		// Check if we're on the accessibility page with tools/scanner tab
		// The hook is like: shahi-legalflowsuite_page_slos-accessibility
		if ( strpos( $hook, 'slos-accessibility' ) !== false ) {
			$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'tools';
			return $tab === 'tools' || $tab === 'scanner';
		}
		return false;
	}

	/**     * Check if current page is the accessibility dashboard page.
	 *
	 * @since 1.0.0
	 * @param string $hook Current admin page hook.
	 * @return bool True if accessibility dashboard page, false otherwise.
	 */
	private function is_accessibility_dashboard_page( $hook ) {
		// Check if we're on the main accessibility page with dashboard tab
		// The hook is like: shahi-legalflowsuite_page_slos-accessibility
		if ( strpos( $hook, 'slos-accessibility' ) !== false ) {
			// Check if tab parameter is dashboard (or default to true for all accessibility pages)
			$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'tools';
			return $tab === 'dashboard';
		}
		return false;
	}

	/**     * Localize script data for Accessibility Scanner.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function localize_accessibility_scanner_script() {
		wp_localize_script(
			'shahi-accessibility-scanner',
			'shahiA11y',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'shahi_a11y_nonce' ),
				'strings'        => array(
					'scanRunning'     => __( 'Scan in progress...', 'shahi-legalflowsuite' ),
					'scanComplete'    => __( 'Scan completed successfully', 'shahi-legalflowsuite' ),
					'scanFailed'      => __( 'Scan failed. Please try again.', 'shahi-legalflowsuite' ),
					'fixApplying'     => __( 'Applying fix...', 'shahi-legalflowsuite' ),
					'fixApplied'      => __( 'Fix applied successfully', 'shahi-legalflowsuite' ),
					'fixFailed'       => __( 'Failed to apply fix', 'shahi-legalflowsuite' ),
					'issueIgnored'    => __( 'Issue ignored', 'shahi-legalflowsuite' ),
					'reportExporting' => __( 'Exporting report...', 'shahi-legalflowsuite' ),
					'reportExported'  => __( 'Report exported successfully', 'shahi-legalflowsuite' ),
					'confirmDelete'   => __( 'Are you sure you want to delete this scan?', 'shahi-legalflowsuite' ),
					'confirmReset'    => __( 'Are you sure you want to reset settings to defaults?', 'shahi-legalflowsuite' ),
					'settingsSaved'   => __( 'Settings saved successfully', 'shahi-legalflowsuite' ),
					'settingsFailed'  => __( 'Failed to save settings', 'shahi-legalflowsuite' ),
				),
				'wcagLevels'     => array(
					'A'   => __( 'Level A (Minimum)', 'shahi-legalflowsuite' ),
					'AA'  => __( 'Level AA (Recommended)', 'shahi-legalflowsuite' ),
					'AAA' => __( 'Level AAA (Enhanced)', 'shahi-legalflowsuite' ),
				),
				'severityLevels' => array(
					'critical' => __( 'Critical', 'shahi-legalflowsuite' ),
					'serious'  => __( 'Serious', 'shahi-legalflowsuite' ),
					'moderate' => __( 'Moderate', 'shahi-legalflowsuite' ),
					'minor'    => __( 'Minor', 'shahi-legalflowsuite' ),
				),
			)
		);
	}

	/**
	 * Localize script data for Scan Progress modal.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	private function localize_scan_progress_script() {
		wp_localize_script(
			'slos-scan-progress',
			'slosScanConfig',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'slos_scanner_nonce' ),
				'i18n'    => array(
					'initializing'   => __( 'Initializing...', 'shahi-legalflowsuite' ),
					'fetching'       => __( 'Fetching pages to scan...', 'shahi-legalflowsuite' ),
					'scanning'       => __( 'Scanning', 'shahi-legalflowsuite' ),
					'complete'       => __( 'Scan complete!', 'shahi-legalflowsuite' ),
					'cancelled'      => __( 'Scan cancelled', 'shahi-legalflowsuite' ),
					'error'          => __( 'Error', 'shahi-legalflowsuite' ),
					'noPages'        => __( 'No pages found to scan', 'shahi-legalflowsuite' ),
					'confirmCancel'  => __( 'Are you sure you want to cancel the scan?', 'shahi-legalflowsuite' ),
				),
			)
		);
	}

	/**
	 * Localize script data for Auto-Fix Progress popup.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function localize_autofix_progress_script() {
		// Get fixer list for JS
		$fixers = array();
		if ( class_exists( '\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry' ) ) {
			\ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::init();
			$fixer_ids = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_all_fixer_ids();

			foreach ( $fixer_ids as $id ) {
				$fixer = \ShahiLegalFlowSuite\Modules\AccessibilityScanner\Fixes\FixerRegistry::get_fixer( $id );
				if ( $fixer ) {
					// Get ID and derive name from it (BaseFixer doesn't have get_name)
					$fixer_id = $fixer->get_id();
					// Convert ID like 'missing_alt' to 'Missing Alt'
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
		}

		wp_localize_script(
			'slos-autofix-progress',
			'slosautoFixConfig',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'slos_autofix_nonce' ),
				'fixers'  => $fixers,
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
}
