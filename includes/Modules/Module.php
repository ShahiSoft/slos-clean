<?php
/**
 * Base Module Class
 *
 * Abstract base class for all plugin modules. Provides the structure
 * and common functionality that all modules must implement.
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
 * Abstract Module Class
 *
 * All plugin modules must extend this class and implement its abstract methods.
 * This ensures consistency across all modules and provides a standardized API.
 *
 * @since 1.0.0
 */
abstract class Module {

	/**
	 * Module unique key/slug
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $key;

	/**
	 * Module enabled state
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $enabled = false;

	/**
	 * Module settings
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Module dependencies
	 *
	 * Array of module keys that this module depends on.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $dependencies = array();

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->key = $this->get_key();
		$this->load_settings();
	}

	/**
	 * Get module unique key
	 *
	 * Used for identification in database and registration.
	 *
	 * @since 1.0.0
	 * @return string Module key
	 */
	abstract public function get_key();

	/**
	 * Get module name
	 *
	 * Human-readable name displayed in admin interface.
	 *
	 * @since 1.0.0
	 * @return string Module name
	 */
	abstract public function get_name();

	/**
	 * Get module description
	 *
	 * Short description of module functionality.
	 *
	 * @since 1.0.0
	 * @return string Module description
	 */
	abstract public function get_description();

	/**
	 * Get module icon
	 *
	 * Dashicon class or custom icon identifier.
	 *
	 * @since 1.0.0
	 * @return string Icon class (e.g., 'dashicons-chart-line')
	 */
	abstract public function get_icon();

	/**
	 * Get module version
	 *
	 * @since 1.0.0
	 * @return string Version number
	 */
	public function get_version() {
		return '1.0.0';
	}

	/**
	 * Get module author
	 *
	 * @since 1.0.0
	 * @return string Author name
	 */
	public function get_author() {
		return 'ShahiLegalFlowSuite';
	}

	/**
	 * Get module category
	 *
	 * Used for filtering and organization.
	 *
	 * @since 1.0.0
	 * @return string Category (tracking, performance, security, etc.)
	 */
	public function get_category() {
		return 'general';
	}

	/**
	 * Check if module is enabled
	 *
	 * @since 1.0.0
	 * @return bool True if enabled, false otherwise
	 */
	public function is_enabled() {
		return $this->enabled;
	}

	/**
	 * Get module dependencies
	 *
	 * @since 1.0.0
	 * @return array Array of module keys this module depends on
	 */
	public function get_dependencies() {
		return $this->dependencies;
	}

	/**
	 * Check if module has unmet dependencies
	 *
	 * @since 1.0.0
	 * @return bool True if dependencies are met, false otherwise
	 */
	public function dependencies_met() {
		if ( empty( $this->dependencies ) ) {
			return true;
		}

		$module_manager = ModuleManager::get_instance();

		foreach ( $this->dependencies as $dependency_key ) {
			$dependency = $module_manager->get_module( $dependency_key );
			if ( ! $dependency || ! $dependency->is_enabled() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Activate module
	 *
	 * Called when module is enabled. Override to add custom activation logic.
	 *
	 * @since 1.0.0
	 * @return bool True on success, false on failure
	 */
	public function activate() {
		if ( ! $this->dependencies_met() ) {
			return false;
		}

		$this->enabled = true;
		$this->save_enabled_state( true );
		$this->on_activate();

		return true;
	}

	/**
	 * Deactivate module
	 *
	 * Called when module is disabled. Override to add custom deactivation logic.
	 *
	 * @since 1.0.0
	 * @return bool True on success, false on failure
	 */
	public function deactivate() {
		$this->enabled = false;
		$this->save_enabled_state( false );
		$this->on_deactivate();

		return true;
	}

	/**
	 * Hook called on module activation
	 *
	 * Override in child classes to add custom activation logic.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function on_activate() {
		// Override in child classes..
	}

	/**
	 * Hook called on module deactivation
	 *
	 * Override in child classes to add custom deactivation logic.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function on_deactivate() {
		// Override in child classes..
	}

	/**
	 * Initialize module
	 *
	 * Called when module is loaded. Override to add hooks, filters, etc.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	abstract public function init();

	/**
	 * Get module settings URL
	 *
	 * Returns the admin URL for module-specific settings page.
	 * Return empty string if module has no settings page.
	 *
	 * @since 1.0.0
	 * @return string Settings URL or empty string
	 */
	public function get_settings_url() {
		return '';
	}

	/**
	 * Get module setting
	 *
	 * @since 1.0.0
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value if setting not found.
	 * @return mixed Setting value
	 */
	public function get_setting( $key, $default = null ) {
		return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
	}

	/**
	 * Update module setting
	 *
	 * @since 1.0.0
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return bool True on success, false on failure
	 */
	public function update_setting( $key, $value ) {
		$this->settings[ $key ] = $value;
		return $this->save_settings();
	}

	/**
	 * Load settings from database
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function load_settings() {
		global $wpdb;
		$table = $wpdb->prefix . 'shahi_modules';

		$state_loaded = false;
		$db_result    = null;

		// First, check the option (authoritative source for enabled state)..
		$modules_option = get_option( 'shahi_modules', array() );
		if ( isset( $modules_option[ $this->key ] ) ) {
			$module_state = $modules_option[ $this->key ];
			if ( isset( $module_state['enabled'] ) ) {
				$this->enabled = (bool) $module_state['enabled'];
				$state_loaded  = true;
			}
			if ( isset( $module_state['settings'] ) && is_array( $module_state['settings'] ) ) {
				$this->settings = $module_state['settings'];
			}
		}

		// If state found in option, we're done - option is authoritative..
		if ( $state_loaded ) {
			return;
		}

		// Fallback: Check database table only if option had nothing...
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			$db_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT is_enabled, settings FROM %i WHERE module_key = %s',
					$table,
					$this->key
				),
				ARRAY_A
			);

			if ( $db_result ) {
				$this->enabled  = (bool) $db_result['is_enabled'];
				$this->settings = ! empty( $db_result['settings'] ) ? json_decode( $db_result['settings'], true ) : array();
			}
		}
	}

	/**
	 * Save settings to database
	 *
	 * @since 1.0.0
	 * @return bool True on success, false on failure
	 */
	protected function save_settings() {
		global $wpdb;
		$table    = $wpdb->prefix . 'shahi_modules';
		$db_saved = true;

		// Check if table exists...
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			$db_saved = false;
		} else {
			$result = $wpdb->update(
				$table,
				array(
					'settings'     => wp_json_encode( $this->settings ),
					'last_updated' => current_time( 'mysql' ),
				),
				array( 'module_key' => $this->key ),
				array( '%s', '%s' ),
				array( '%s' )
			);

			$db_saved = $result !== false;
		}

		// Persist to option as a secondary store to avoid losing state when the table is missing..
		$option_before  = get_option( 'shahi_modules', array() );
		$modules_option = $option_before;
		if ( ! isset( $modules_option[ $this->key ] ) ) {
			$modules_option[ $this->key ] = array();
		}

		$modules_option[ $this->key ]['enabled']  = $this->enabled;
		$modules_option[ $this->key ]['settings'] = $this->settings;
		$option_saved                             = update_option( 'shahi_modules', $modules_option );
		$option_ok                                = $option_saved || $modules_option === $option_before; // unchanged still fine

		return $db_saved || $option_ok;
	}

	/**
	 * Save enabled state to database
	 *
	 * @since 1.0.0
	 * @param bool $enabled Whether module is enabled.
	 * @return bool True on success, false on failure
	 */
	protected function save_enabled_state( $enabled ) {
		global $wpdb;
		$table    = $wpdb->prefix . 'shahi_modules';
		$db_saved = false;

		// Check if table exists...
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			// Check if record exists...
			$exists = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %i WHERE module_key = %s',
					$table,
					$this->key
				)
			);

			if ( $exists ) {
				// Update existing record..
				$result = $wpdb->update(
					$table,
					array(
						'is_enabled'   => $enabled ? 1 : 0,
						'last_updated' => current_time( 'mysql' ),
					),
					array( 'module_key' => $this->key ),
					array( '%d', '%s' ),
					array( '%s' )
				);
			} else {
				// Insert new record..
				$result = $wpdb->insert(
					$table,
					array(
						'module_key'   => $this->key,
						'is_enabled'   => $enabled ? 1 : 0,
						'settings'     => wp_json_encode( $this->settings ),
						'last_updated' => current_time( 'mysql' ),
					),
					array( '%s', '%d', '%s', '%s' )
				);
			}

			$db_saved = $result !== false;
		}

		// Persist to option as fallback to avoid losing state if the DB table is missing..
		$option_before  = get_option( 'shahi_modules', array() );
		$modules_option = $option_before;
		if ( ! isset( $modules_option[ $this->key ] ) ) {
			$modules_option[ $this->key ] = array();
		}
		$modules_option[ $this->key ]['enabled']  = (bool) $enabled;
		$modules_option[ $this->key ]['settings'] = $this->settings;
		$option_saved                             = update_option( 'shahi_modules', $modules_option );
		$option_ok                                = $option_saved || $modules_option === $option_before;

		return $db_saved || $option_ok;
	}

	/**
	 * Check if module is premium/pro
	 *
	 * @since 1.0.0
	 * @return bool True if premium, false if free
	 */
	public function is_premium() {
		return false;
	}

	/**
	 * Get module data as array
	 *
	 * Used for rendering in templates and AJAX responses.
	 *
	 * @since 1.0.0
	 * @return array Module data
	 */
	public function to_array() {
		return array(
			'key'              => $this->get_key(),
			'name'             => $this->get_name(),
			'description'      => $this->get_description(),
			'icon'             => $this->get_icon(),
			'version'          => $this->get_version(),
			'author'           => $this->get_author(),
			'category'         => $this->get_category(),
			'enabled'          => $this->is_enabled(),
			'dependencies'     => $this->get_dependencies(),
			'dependencies_met' => $this->dependencies_met(),
			'settings_url'     => $this->get_settings_url(),
			'is_premium'       => $this->is_premium(),
		);
	}
}
