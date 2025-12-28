<?php
/**
 * Fired during plugin deactivation
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Core
 * @license    GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivator Class
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since 1.0.0
 */
class Deactivator {

	/**
	 * Deactivate the plugin
	 *
	 * Cleans up temporary data but preserves user data.
	 * All permanent data is only removed on uninstall if user chooses to.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function deactivate() {
		// Clear transients
		self::clear_transients();

		// Remove custom capabilities
		\ShahiLegalFlowSuite\Admin\MenuManager::remove_capabilities();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Note: We do NOT delete any user data here
		// Data deletion only happens in uninstall.php if user chooses to
	}

	/**
	 * Clear all plugin transients
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private static function clear_transients() {
		global $wpdb;

		// Delete all transients with our prefix
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_shahi_legalflowsuite_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_shahi_legalflowsuite_' ) . '%'
			)
		);
	}
}
