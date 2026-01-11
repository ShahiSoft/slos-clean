<?php
/**
 * Module Manager
 *
 * Registry and management system for plugin modules. Handles module
 * registration, activation, deactivation, and dependency resolution.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Manager Class
 *
 * Singleton class that manages all plugin modules. Provides methods for
 * registering modules, checking dependencies, and managing module states.
 *
 * @since 1.0.0
 */
class ModuleManager {


	/**
	 * Singleton instance
	 *
	 * @since 1.0.0
	 * @var ModuleManager
	 */
	private static $instance = null;

	/**
	 * Registered modules
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $modules = array();

	/**
	 * Initialized modules
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $initialized = array();

	/**
	 * Get singleton instance
	 *
	 * @since 1.0.0
	 * @return ModuleManager Singleton instance
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Register default modules
		$this->register_default_modules();

		// Initialize enabled modules
		add_action( 'init', array( $this, 'initialize_modules' ), 5 );
	}

	/**
	 * Register a module
	 *
	 * @since 1.0.0
	 * @param Module $module Module instance to register.
	 * @return bool True on success, false on failure
	 */
	public function register( Module $module ) {
		$key = $module->get_key();

		if ( isset( $this->modules[ $key ] ) ) {
			return false; // Module already registered
		}

		$this->modules[ $key ] = $module;

		return true;
	}

	/**
	 * Unregister a module
	 *
	 * @since 1.0.0
	 * @param string $key Module key to unregister.
	 * @return bool True on success, false if module not found
	 */
	public function unregister( $key ) {
		if ( ! isset( $this->modules[ $key ] ) ) {
			return false;
		}

		// Deactivate if enabled
		if ( $this->modules[ $key ]->is_enabled() ) {
			$this->modules[ $key ]->deactivate();
		}

		unset( $this->modules[ $key ] );
		unset( $this->initialized[ $key ] );

		return true;
	}

	/**
	 * Get a module by key
	 *
	 * @since 1.0.0
	 * @param string $key Module key.
	 * @return Module|null Module instance or null if not found
	 */
	public function get_module( $key ) {
		return isset( $this->modules[ $key ] ) ? $this->modules[ $key ] : null;
	}

	/**
	 * Get all registered modules
	 *
	 * @since 1.0.0
	 * @param bool $enabled_only If true, return only enabled modules.
	 * @return array Array of Module instances
	 */
	public function get_modules( $enabled_only = false ) {
		if ( ! $enabled_only ) {
			return $this->modules;
		}

		return array_filter(
			$this->modules,
			function ( $module ) {
				return $module->is_enabled();
			}
		);
	}

	/**
	 * Get modules by category
	 *
	 * @since 1.0.0
	 * @param string $category Category name.
	 * @param bool   $enabled_only If true, return only enabled modules.
	 * @return array Array of Module instances
	 */
	public function get_modules_by_category( $category, $enabled_only = false ) {
		$modules = $this->get_modules( $enabled_only );

		return array_filter(
			$modules,
			function ( $module ) use ( $category ) {
				return $module->get_category() === $category;
			}
		);
	}

	/**
	 * Check if a module is registered
	 *
	 * @since 1.0.0
	 * @param string $key Module key.
	 * @return bool True if registered, false otherwise
	 */
	public function is_registered( $key ) {
		return isset( $this->modules[ $key ] );
	}

	/**
	 * Check if a module is enabled
	 *
	 * @since 1.0.0
	 * @param string $key Module key.
	 * @return bool True if enabled, false otherwise
	 */
	public function is_enabled( $key ) {
		if ( ! isset( $this->modules[ $key ] ) ) {
			return false;
		}

		return $this->modules[ $key ]->is_enabled();
	}

	/**
	 * Enable a module
	 *
	 * @since 1.0.0
	 * @param string $key Module key.
	 * @return bool True on success, false on failure
	 */
	public function enable_module( $key ) {
		$module = $this->get_module( $key );

		if ( ! $module ) {
			return false;
		}

		// Check dependencies
		if ( ! $module->dependencies_met() ) {
			return false;
		}

		// Activate module
		if ( $module->activate() ) {
			// Initialize if not already initialized
			if ( ! isset( $this->initialized[ $key ] ) ) {
				$module->init();
				$this->initialized[ $key ] = true;
			}

			return true;
		}

		return false;
	}

	/**
	 * Disable a module
	 *
	 * @since 1.0.0
	 * @param string $key Module key.
	 * @return bool True on success, false on failure
	 */
	public function disable_module( $key ) {
		$module = $this->get_module( $key );

		if ( ! $module ) {
			return false;
		}

		// Check if other modules depend on this one
		$dependents = $this->get_dependent_modules( $key );
		if ( ! empty( $dependents ) ) {
			return false; // Cannot disable if other modules depend on it
		}

		return $module->deactivate();
	}

	/**
	 * Toggle a module (enable or disable)
	 *
	 * @since 1.0.0
	 * @param string $key Module key.
	 * @param bool   $enabled True to enable, false to disable.
	 * @return bool True on success, false on failure
	 */
	public function toggle_module( $key, $enabled ) {
		if ( $enabled ) {
			return $this->enable_module( $key );
		} else {
			return $this->disable_module( $key );
		}
	}

	/**
	 * Get modules that depend on a specific module
	 *
	 * @since 1.0.0
	 * @param string $key Module key.
	 * @return array Array of dependent module keys
	 */
	public function get_dependent_modules( $key ) {
		$dependents = array();

		foreach ( $this->modules as $module_key => $module ) {
			if ( $module->is_enabled() && in_array( $key, $module->get_dependencies(), true ) ) {
				$dependents[] = $module_key;
			}
		}

		return $dependents;
	}

	/**
	 * Initialize enabled modules
	 *
	 * Called on WordPress 'init' hook. Initializes all enabled modules
	 * that haven't been initialized yet.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function initialize_modules() {
		foreach ( $this->modules as $key => $module ) {
			if ( $module->is_enabled() && ! isset( $this->initialized[ $key ] ) ) {
				$module->init();
				$this->initialized[ $key ] = true;
			}
		}
	}

	/**
	 * Determine if a module already has stored state (DB table or option fallback).
	 *
	 * This prevents auto-enabling modules when a previous state exists but the
	 * database table is missing (e.g., activation not run in the environment).
	 *
	 * @param string $key Module key.
	 * @return bool True if state exists in DB or options.
	 */
	private function module_record_exists( $key ) {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_modules';

		$db_record_exists = false;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			$db_record_exists = (bool) $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %i WHERE module_key = %s',
					$table,
					$key
				)
			);
		}

		$option_modules   = get_option( 'shahi_modules', array() );
		$option_has_state = isset( $option_modules[ $key ] );

		return $db_record_exists || $option_has_state;
	}

	/**
	 * Register default modules
	 *
	 * Registers all built-in modules that come with the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_default_modules() {
		// Compliance Dashboard Module (Phase 2.1)
		if ( class_exists( 'ShahiLegalFlowSuite\Modules\ComplianceDashboard\ComplianceDashboard' ) ) {
			$compliance_dashboard = new \ShahiLegalFlowSuite\Modules\ComplianceDashboard\ComplianceDashboard();
			if ( $this->register( $compliance_dashboard ) && ! $this->module_record_exists( 'compliance-dashboard' ) ) {
				// First time - enable by default
				$this->enable_module( 'compliance-dashboard' );
			}
		}

		// Security Module - DORMANT (temporarily disabled, data preserved)
		// Uncomment to reactivate or remove 'security' from SLOS_DORMANT_MODULES constant
		// if (class_exists('ShahiLegalFlowSuite\Modules\Security_Module')) {
		// $this->register(new Security_Module());
		// }

		// Accessibility Scanner Module (Keep - legal requirement for ADA/WCAG compliance)
		if ( class_exists( 'ShahiLegalFlowSuite\Modules\AccessibilityScanner\AccessibilityScanner' ) ) {
			$this->register( new \ShahiLegalFlowSuite\Modules\AccessibilityScanner\AccessibilityScanner() );
		}

		// Consent Management Module (GDPR compliance)
		if ( class_exists( 'ShahiLegalFlowSuite\Modules\ConsentManagement\ConsentManagement' ) ) {
			$consent_module = new \ShahiLegalFlowSuite\Modules\ConsentManagement\ConsentManagement();
			if ( $this->register( $consent_module ) && ! $this->module_record_exists( 'consent-management' ) ) {
				$this->enable_module( 'consent-management' );
			}
		}

		// DSR Portal Module - DORMANT (temporarily disabled, data preserved)
		// Uncomment to reactivate or remove 'dsr-portal' from SLOS_DORMANT_MODULES constant
		// if (class_exists('ShahiLegalFlowSuite\Modules\DSR_Portal\DSR_Portal')) {
		// $dsr_module = new \ShahiLegalFlowSuite\Modules\DSR_Portal\DSR_Portal();
		// if ($this->register($dsr_module) && !$this->module_record_exists('dsr-portal')) {
		// $this->enable_module('dsr-portal');
		// }
		// }

		// Legal Documents Module (New)
		if ( class_exists( 'ShahiLegalFlowSuite\Modules\LegalDocs\LegalDocuments' ) ) {
			$legaldocs_module = new \ShahiLegalFlowSuite\Modules\LegalDocs\LegalDocuments();
			if ( $this->register( $legaldocs_module ) && ! $this->module_record_exists( 'legal-docs' ) ) {
				$this->enable_module( 'legal-docs' );
			}
		}

		/**
		 * Allow third-party code to register custom modules
		 *
		 * @since 1.0.0
		 * @param ModuleManager $manager Module manager instance.
		 */
		do_action( 'shahi_legalflowsuite_register_modules', $this );
	}

	/**
	 * Get module statistics
	 *
	 * Returns statistics about registered and enabled modules.
	 *
	 * @since 1.0.0
	 * @return array Statistics array
	 */
	public function get_statistics() {
		$total    = count( $this->modules );
		$enabled  = count( $this->get_modules( true ) );
		$disabled = $total - $enabled;

		// Count by category
		$by_category = array();
		foreach ( $this->modules as $module ) {
			$category = $module->get_category();
			if ( ! isset( $by_category[ $category ] ) ) {
				$by_category[ $category ] = 0;
			}
			++$by_category[ $category ];
		}

		return array(
			'total'       => $total,
			'enabled'     => $enabled,
			'disabled'    => $disabled,
			'by_category' => $by_category,
			'initialized' => count( $this->initialized ),
		);
	}

	/**
	 * Bulk enable modules
	 *
	 * @since 1.0.0
	 * @param array $keys Array of module keys to enable.
	 * @return array Array with 'success' and 'failed' keys
	 */
	public function bulk_enable( array $keys ) {
		$result = array(
			'success' => array(),
			'failed'  => array(),
		);

		foreach ( $keys as $key ) {
			if ( $this->enable_module( $key ) ) {
				$result['success'][] = $key;
			} else {
				$result['failed'][] = $key;
			}
		}

		return $result;
	}

	/**
	 * Bulk disable modules
	 *
	 * @since 1.0.0
	 * @param array $keys Array of module keys to disable.
	 * @return array Array with 'success' and 'failed' keys
	 */
	public function bulk_disable( array $keys ) {
		$result = array(
			'success' => array(),
			'failed'  => array(),
		);

		foreach ( $keys as $key ) {
			if ( $this->disable_module( $key ) ) {
				$result['success'][] = $key;
			} else {
				$result['failed'][] = $key;
			}
		}

		return $result;
	}

	/**
	 * Export module configuration
	 *
	 * Returns module states and settings as JSON.
	 *
	 * @since 1.0.0
	 * @return string JSON string
	 */
	public function export_configuration() {
		$config = array();

		foreach ( $this->modules as $key => $module ) {
			$config[ $key ] = array(
				'enabled'  => $module->is_enabled(),
				'settings' => $module->get_setting( 'all', array() ),
			);
		}

		return wp_json_encode( $config, JSON_PRETTY_PRINT );
	}

	/**
	 * Import module configuration
	 *
	 * Imports module states and settings from JSON.
	 *
	 * @since 1.0.0
	 * @param string $json JSON configuration string.
	 * @return bool True on success, false on failure
	 */
	public function import_configuration( $json ) {
		$config = json_decode( $json, true );

		if ( ! is_array( $config ) ) {
			return false;
		}

		foreach ( $config as $key => $data ) {
			$module = $this->get_module( $key );

			if ( ! $module ) {
				continue;
			}

			// Set enabled state
			if ( isset( $data['enabled'] ) ) {
				if ( $data['enabled'] ) {
					$this->enable_module( $key );
				} else {
					$this->disable_module( $key );
				}
			}

			// Import settings
			if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
				foreach ( $data['settings'] as $setting_key => $setting_value ) {
					$module->update_setting( $setting_key, $setting_value );
				}
			}
		}

		return true;
	}
}
