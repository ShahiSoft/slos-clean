<?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( wp_unslash( <?php
/**
 * Module AJAX Handler
 *
 * Handles AJAX requests for module management.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Ajax
 * @version     3.0.1
 * @since       1.0.0
 * @author      ShahiLegalFlowSuite Team
 * @license     GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Ajax;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ModuleAjax
 *
 * AJAX handler for module-related operations.
 *
 * @since 1.0.0
 */
class ModuleAjax {

	/**
	 * Register AJAX actions
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_ajax_actions() {
		add_action( 'wp_ajax_shahi_toggle_module', array( $this, 'toggle_module' ) );
		add_action( 'wp_ajax_shahi_save_module_settings', array( $this, 'save_module_settings' ) );
	}

	/**
	 * Toggle module enabled/disabled status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function toggle_module() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_toggle_module', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$enabled   = isset( $_POST['enabled'] ) ? filter_var( $_POST['enabled'], FILTER_VALIDATE_BOOLEAN ) : true;

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Toggle module..
		$modules[ $module_id ]['enabled'] = $enabled;
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, $enabled ? 'enabled' : 'disabled' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			sprintf( 'Module %s successfully', $enabled ? 'enabled' : 'disabled' )
		);
	}

	/**
	 * Save module settings
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_module_settings() {
		// Verify request..
		AjaxHandler::verify_request( 'shahi_save_module_settings', 'manage_shahi_template' );

		// Get module ID..
		if ( ! isset( $_POST['module_id'] ) ) {
			AjaxHandler::error( 'Module ID is required' );
		}

		$module_id = sanitize_key( $_POST['module_id'] );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
POST['module_id'] ) );
		$settings  = isset( $_POST['settings'] ) ? $_POST['settings'] : array();

		// Get modules..
		$modules = get_option( 'shahi_modules', array() );

		if ( ! isset( $modules[ $module_id ] ) ) {
			AjaxHandler::error( 'Module not found' );
		}

		// Sanitize and save settings..
		$modules[ $module_id ]['settings'] = AjaxHandler::sanitize_data( $settings );
		update_option( 'shahi_modules', $modules );

		// Track analytics event..
		$this->track_module_event( $module_id, 'settings_updated' );

		AjaxHandler::success(
			array( 'module' => $modules[ $module_id ] ),
			'Module settings saved successfully'
		);
	}

	/**
	 * Track module analytics event
	 *
	 * @since 1.0.0
	 * @param string $module_id Module ID.
	 * @param string $action    Action performed.
	 * @return void
	 */
	private function track_module_event( $module_id, $action ) {
		global $wpdb;
		$analytics_table = $wpdb->prefix . 'shahi_analytics';

		// Check if table exists..
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$analytics_table'" ) !== $analytics_table ) {
			return;
		}

		$event_data = json_encode(
			array(
				'module_id' => $module_id,
				'action'    => $action,
			)
		);

		$wpdb->insert(
			$analytics_table,
			array(
				'event_type' => 'module_' . $action,
				'event_data' => $event_data,
				'user_id'    => get_current_user_id(),
				'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
				'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 255 ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}
}
