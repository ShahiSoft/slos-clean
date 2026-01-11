<?php
/**
 * Theme Manager
 *
 * Centralizes theme variable output using config/themes.php.
 * Provides CSS variable injection for the Neon Aether theme (default).
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Core
 * @since       4.2.0
 */

namespace ShahiLegalFlowSuite\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Theme_Manager {

	/**
	 * Singleton instance
	 *
	 * @var Theme_Manager|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Theme_Manager
	 */
	public static function get_instance(): Theme_Manager {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get active theme key
	 *
	 * @return string Theme key
	 */
	public function get_active_theme(): string {
		// Allow override via option; default to Neon Aether
		$theme = get_option( 'shahi_admin_theme', 'mac-slate-liquid' );
		return is_string( $theme ) && ! empty( $theme ) ? $theme : 'neon-aether';
	}

	/**
	 * Get variables for a theme
	 *
	 * @param string|null $theme Theme key.
	 * @return array<string,string> CSS variable map
	 */
	public function get_theme_variables( ?string $theme = null ): array {
		$themes = $this->load_themes_config();
		$key    = $theme ?: $this->get_active_theme();

		if ( isset( $themes[ $key ]['variables'] ) && is_array( $themes[ $key ]['variables'] ) ) {
			return $themes[ $key ]['variables'];
		}

		// Fallback to Neon Aether
		if ( isset( $themes['neon-aether']['variables'] ) ) {
			return $themes['neon-aether']['variables'];
		}

		return array();
	}

	/**
	 * Build CSS string with :root variables for injection
	 *
	 * @return string CSS content
	 */
	public function build_css_variables(): string {
		$vars = $this->get_theme_variables();
		if ( empty( $vars ) ) {
			return '';
		}

		$lines = array( ':root {' );
		foreach ( $vars as $name => $value ) {
			// Ensure variable naming and values are valid strings
			if ( is_string( $name ) && is_string( $value ) && 0 === strpos( $name, '--' ) ) {
				$lines[] = sprintf( '  %s: %s;', $name, $value );
			}
		}
		$lines[] = '}';

		return implode( "\n", $lines );
	}

	/**
	 * Load themes configuration from config/themes.php
	 *
	 * @return array
	 */
	private function load_themes_config(): array {
		$config_path = defined( 'SHAHI_LEGALFLOWSUITE_PLUGIN_DIR' )
			? SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'config/themes.php'
			: dirname( __DIR__, 2 ) . '/config/themes.php';

		if ( file_exists( $config_path ) ) {
			$themes = require $config_path;
			return is_array( $themes ) ? $themes : array();
		}

		return array();
	}
}
