<?php
/**
 * FixEngine Bootstrap
 * 
 * Initializes and registers the FixEngine system.
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Bootstrap
 * 
 * Handles initialization and registration of the FixEngine.
 */
final class Bootstrap {

	/** @var FixEngine */
	private static $engine;

	/** @var FixEngineAjaxHandler */
	private static $ajax_handler;

	/** @var bool */
	private static $initialized = false;

	/**
	 * Initialize the FixEngine system
	 */
	public static function init(): void {
		if ( self::$initialized ) {
			return;
		}

		// Load dependencies
		self::load_dependencies();

		// Create instances
		self::$engine = new FixEngine();
		self::$ajax_handler = new FixEngineAjaxHandler( self::$engine );

		// Register AJAX handlers
		self::$ajax_handler->register();

		// Register admin scripts
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );

		// Activation hook for database setup
		register_activation_hook( SLOS_PLUGIN_FILE ?? __FILE__, [ __CLASS__, 'on_activation' ] );

		self::$initialized = true;
	}

	/**
	 * Load all FixEngine dependencies
	 */
	private static function load_dependencies(): void {
		$base_dir = __DIR__;

		// Load core interfaces and contracts first
		$contract_files = [
			'FixerInterface.php',
		];

		foreach ( $contract_files as $file ) {
			$path = $base_dir . '/' . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}

		// Load core classes
		$core_files = [
			'FixResult.php',
			'AbstractFixer.php',
			'FixerCollection.php',
			'FixSession.php',
			'FixHistoryRepository.php',
			'FixEngine.php',
			'FixEngineAjaxHandler.php',
		];

		foreach ( $core_files as $file ) {
			$path = $base_dir . '/' . $file;
			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}

		// Load all fixers
		$fixers_dir = $base_dir . '/Fixers';
		if ( is_dir( $fixers_dir ) ) {
			$fixer_files = glob( $fixers_dir . '/*.php' );
			if ( is_array( $fixer_files ) ) {
				foreach ( $fixer_files as $fixer_file ) {
					require_once $fixer_file;
				}
			}
		}
	}

	/**
	 * Get the FixEngine instance
	 *
	 * @return FixEngine
	 */
	public static function get_engine(): FixEngine {
		if ( ! self::$initialized ) {
			self::init();
		}
		return self::$engine;
	}

	/**
	 * Get the AJAX handler
	 *
	 * @return FixEngineAjaxHandler
	 */
	public static function get_ajax_handler(): FixEngineAjaxHandler {
		if ( ! self::$initialized ) {
			self::init();
		}
		return self::$ajax_handler;
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook_suffix
	 */
	public static function enqueue_scripts( string $hook_suffix ): void {
		// Only on relevant pages
		if ( strpos( $hook_suffix, 'accessibility' ) === false && 
			 strpos( $hook_suffix, 'scanner' ) === false &&
			 strpos( $hook_suffix, 'slos' ) === false ) {
			return;
		}

		// Localize data for JavaScript
		wp_localize_script(
			'slos-autofix-progress', // Existing script handle
			'slosFixEngine',
			self::$ajax_handler->get_localize_data()
		);
	}

	/**
	 * Handle plugin activation
	 */
	public static function on_activation(): void {
		if ( ! self::$initialized ) {
			self::init();
		}

		// Create database table
		$repository = new FixHistoryRepository();
		$repository->create_table();
	}

	/**
	 * Get data for scanner page (fixer list)
	 *
	 * @return array
	 */
	public static function get_scanner_data(): array {
		$engine = self::get_engine();
		$engine->initialize();

		return [
			'fixers'      => $engine->get_fixers_array(),
			'categories'  => $engine->get_categories(),
			'grouped'     => $engine->get_fixers_by_category(),
			'count'       => $engine->get_fixer_count(),
		];
	}
}

// Auto-initialize when this file is loaded
add_action( 'init', [ Bootstrap::class, 'init' ], 5 );
