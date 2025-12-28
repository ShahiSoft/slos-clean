<?php
/**
 * PSR-4 Autoloader
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
 * Autoloader Class
 *
 * Handles automatic loading of plugin classes following PSR-4 standard.
 *
 * @since 1.0.0
 */
class Autoloader {


	/**
	 * Register the autoloader
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload classes
	 *
	 * @since 1.0.0
	 * @param string $class The fully-qualified class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		$prefix   = 'ShahiLegalFlowSuite\\';
		$base_dir = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
