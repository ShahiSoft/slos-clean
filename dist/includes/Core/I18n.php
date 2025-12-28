<?php
/**
 * Internationalization (i18n) Class
 *
 * Handles all translation and localization functionality for the plugin.
 * Provides methods for loading text domains, managing translations,
 * and ensuring all user-facing strings are properly translatable.
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
 * Class I18n
 *
 * Manages internationalization and localization for the plugin.
 *
 * @since 1.0.0
 */
class I18n {

	/**
	 * The text domain for the plugin.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const TEXT_DOMAIN = 'shahi-legalflowsuite';

	/**
	 * The languages directory path (relative to plugin root).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const LANGUAGES_DIR = 'languages';

	/**
	 * Whether translations have been loaded.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private static $loaded = false;

	/**
	 * Available languages (cached).
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $available_languages = null;

	/**
	 * Load the plugin text domain for translation.
	 *
	 * Loads the plugin's translation files from the languages directory.
	 * Should be called during the 'plugins_loaded' action hook.
	 *
	 * @since 1.0.0
	 * @return bool True if loaded successfully, false otherwise.
	 */
	public static function load_plugin_textdomain() {
		if ( self::$loaded ) {
			return true;
		}

		// Get the languages directory path
		$languages_dir = dirname( plugin_basename( SHAHI_LEGALFLOWSUITE_PLUGIN_FILE ) ) . '/' . self::LANGUAGES_DIR;

		// Load the text domain
		$loaded = load_plugin_textdomain(
			self::TEXT_DOMAIN,
			false,
			$languages_dir
		);

		if ( $loaded ) {
			self::$loaded = true;
		}

		return $loaded;
	}

	/**
	 * Get the text domain.
	 *
	 * Returns the plugin's text domain constant.
	 *
	 * @since 1.0.0
	 * @return string The text domain.
	 */
	public static function get_text_domain() {
		return self::TEXT_DOMAIN;
	}

	/**
	 * Check if translations are loaded.
	 *
	 * @since 1.0.0
	 * @return bool True if loaded, false otherwise.
	 */
	public static function is_loaded() {
		return self::$loaded;
	}

	/**
	 * Translate a string.
	 *
	 * Wrapper for WordPress __() function with automatic text domain.
	 *
	 * @since 1.0.0
	 * @param string $text The text to translate.
	 * @return string Translated text.
	 */
	public static function translate( $text ) {
		return __( $text, self::TEXT_DOMAIN );
	}

	/**
	 * Translate and escape a string for safe HTML output.
	 *
	 * Wrapper for WordPress esc_html__() function with automatic text domain.
	 *
	 * @since 1.0.0
	 * @param string $text The text to translate and escape.
	 * @return string Translated and escaped text.
	 */
	public static function translate_esc( $text ) {
		return esc_html__( $text, self::TEXT_DOMAIN );
	}

	/**
	 * Translate and echo a string.
	 *
	 * Wrapper for WordPress _e() function with automatic text domain.
	 *
	 * @since 1.0.0
	 * @param string $text The text to translate and echo.
	 * @return void
	 */
	public static function echo_translate( $text ) {
		_e( $text, self::TEXT_DOMAIN );
	}

	/**
	 * Translate and echo escaped string for safe HTML output.
	 *
	 * Wrapper for WordPress esc_html_e() function with automatic text domain.
	 *
	 * @since 1.0.0
	 * @param string $text The text to translate, escape, and echo.
	 * @return void
	 */
	public static function echo_translate_esc( $text ) {
		esc_html_e( $text, self::TEXT_DOMAIN );
	}

	/**
	 * Translate with context.
	 *
	 * Wrapper for WordPress _x() function with automatic text domain.
	 * Useful when the same string has different meanings in different contexts.
	 *
	 * @since 1.0.0
	 * @param string $text    The text to translate.
	 * @param string $context Context information for translators.
	 * @return string Translated text.
	 */
	public static function translate_context( $text, $context ) {
		return _x( $text, $context, self::TEXT_DOMAIN );
	}

	/**
	 * Translate with context and escape.
	 *
	 * Wrapper for WordPress esc_html_x() function with automatic text domain.
	 *
	 * @since 1.0.0
	 * @param string $text    The text to translate and escape.
	 * @param string $context Context information for translators.
	 * @return string Translated and escaped text.
	 */
	public static function translate_context_esc( $text, $context ) {
		return esc_html_x( $text, $context, self::TEXT_DOMAIN );
	}

	/**
	 * Translate plural forms.
	 *
	 * Wrapper for WordPress _n() function with automatic text domain.
	 *
	 * @since 1.0.0
	 * @param string $single The singular form of the text.
	 * @param string $plural The plural form of the text.
	 * @param int    $number The number to compare for singular/plural.
	 * @return string Translated text (singular or plural).
	 */
	public static function translate_plural( $single, $plural, $number ) {
		return _n( $single, $plural, $number, self::TEXT_DOMAIN );
	}

	/**
	 * Translate plural forms with context.
	 *
	 * Wrapper for WordPress _nx() function with automatic text domain.
	 *
	 * @since 1.0.0
	 * @param string $single  The singular form of the text.
	 * @param string $plural  The plural form of the text.
	 * @param int    $number  The number to compare for singular/plural.
	 * @param string $context Context information for translators.
	 * @return string Translated text (singular or plural).
	 */
	public static function translate_plural_context( $single, $plural, $number, $context ) {
		return _nx( $single, $plural, $number, $context, self::TEXT_DOMAIN );
	}

	/**
	 * Get available translation languages.
	 *
	 * Returns an array of available language codes that have translations.
	 *
	 * @since 1.0.0
	 * @return array Array of available language codes.
	 */
	public static function get_available_languages() {
		// Return cached result if available
		if ( self::$available_languages !== null ) {
			return self::$available_languages;
		}

		// Get the languages directory path
		$languages_path = SHAHI_LEGALFLOWSUITE_PATH . self::LANGUAGES_DIR;

		// Check if directory exists
		if ( ! is_dir( $languages_path ) ) {
			self::$available_languages = array();
			return self::$available_languages;
		}

		// Get all .mo files in the languages directory
		$mo_files = glob( $languages_path . '/' . self::TEXT_DOMAIN . '-*.mo' );

		if ( empty( $mo_files ) ) {
			self::$available_languages = array();
			return self::$available_languages;
		}

		// Extract language codes from filenames
		$languages = array();
		foreach ( $mo_files as $mo_file ) {
			$filename      = basename( $mo_file, '.mo' );
			$language_code = str_replace( self::TEXT_DOMAIN . '-', '', $filename );
			$languages[]   = $language_code;
		}

		// Cache the result
		self::$available_languages = $languages;

		return $languages;
	}

	/**
	 * Check if a specific language is available.
	 *
	 * @since 1.0.0
	 * @param string $language_code The language code to check (e.g., 'en_US', 'fr_FR').
	 * @return bool True if available, false otherwise.
	 */
	public static function is_language_available( $language_code ) {
		$available = self::get_available_languages();
		return in_array( $language_code, $available, true );
	}

	/**
	 * Get the current WordPress locale.
	 *
	 * @since 1.0.0
	 * @return string The current locale (e.g., 'en_US').
	 */
	public static function get_current_locale() {
		return get_locale();
	}

	/**
	 * Get the path to the languages directory.
	 *
	 * @since 1.0.0
	 * @return string Absolute path to the languages directory.
	 */
	public static function get_languages_path() {
		return SHAHI_LEGALFLOWSUITE_PATH . self::LANGUAGES_DIR;
	}

	/**
	 * Get the path to the .pot template file.
	 *
	 * @since 1.0.0
	 * @return string Absolute path to the .pot file.
	 */
	public static function get_pot_file_path() {
		return self::get_languages_path() . '/' . self::TEXT_DOMAIN . '.pot';
	}

	/**
	 * Check if the .pot template file exists.
	 *
	 * @since 1.0.0
	 * @return bool True if exists, false otherwise.
	 */
	public static function pot_file_exists() {
		return file_exists( self::get_pot_file_path() );
	}

	/**
	 * Get translation statistics.
	 *
	 * Returns information about available translations and coverage.
	 *
	 * @since 1.0.0
	 * @return array Translation statistics.
	 */
	public static function get_translation_stats() {
		$stats = array(
			'text_domain'         => self::TEXT_DOMAIN,
			'loaded'              => self::$loaded,
			'current_locale'      => self::get_current_locale(),
			'available_languages' => self::get_available_languages(),
			'languages_count'     => count( self::get_available_languages() ),
			'pot_file_exists'     => self::pot_file_exists(),
			'languages_directory' => self::get_languages_path(),
		);

		return $stats;
	}

	/**
	 * Register custom language directory.
	 *
	 * Allows loading translations from a custom directory (useful for dev/testing).
	 *
	 * @since 1.0.0
	 * @param string $path Absolute path to custom languages directory.
	 * @return bool True on success, false on failure.
	 */
	public static function register_custom_language_path( $path ) {
		if ( ! is_dir( $path ) ) {
			return false;
		}

		// Unload current translations if loaded
		if ( self::$loaded ) {
			unload_textdomain( self::TEXT_DOMAIN );
			self::$loaded = false;
		}

		// Load from custom path
		$relative_path = str_replace( WP_PLUGIN_DIR . '/', '', $path );

		$loaded = load_plugin_textdomain(
			self::TEXT_DOMAIN,
			false,
			$relative_path
		);

		if ( $loaded ) {
			self::$loaded = true;
			// Clear cached languages
			self::$available_languages = null;
		}

		return $loaded;
	}

	/**
	 * Validate text domain usage in code.
	 *
	 * Scans a file or code snippet to ensure proper text domain usage.
	 * Useful for development and quality assurance.
	 *
	 * @since 1.0.0
	 * @param string $code The code to validate.
	 * @return array Validation results with issues found.
	 */
	public static function validate_text_domain( $code ) {
		$issues = array();

		// Pattern to find translation functions
		$pattern = '/(__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e|_x|_ex|_n|_nx)\s*\(/';

		if ( preg_match_all( $pattern, $code, $matches, PREG_OFFSET_CAPTURE ) ) {
			foreach ( $matches[0] as $match ) {
				$position = $match[1];
				$context  = substr( $code, $position, 200 );

				// Check if text domain is present
				if ( strpos( $context, self::TEXT_DOMAIN ) === false ) {
					$issues[] = array(
						'position' => $position,
						'context'  => substr( $context, 0, 100 ),
						'message'  => 'Missing or incorrect text domain',
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Get translation function aliases for convenience.
	 *
	 * Returns an array of shorthand aliases for common translation functions.
	 * This is for documentation purposes only.
	 *
	 * @since 1.0.0
	 * @return array Translation function reference.
	 */
	public static function get_function_reference() {
		return array(
			'Basic Translation'   => array(
				'__($text, $domain)'         => 'Translate and return',
				'_e($text, $domain)'         => 'Translate and echo',
				'esc_html__($text, $domain)' => 'Translate and escape for HTML',
				'esc_html_e($text, $domain)' => 'Translate, escape for HTML, and echo',
				'esc_attr__($text, $domain)' => 'Translate and escape for attribute',
				'esc_attr_e($text, $domain)' => 'Translate, escape for attribute, and echo',
			),
			'Context Translation' => array(
				'_x($text, $context, $domain)'         => 'Translate with context',
				'_ex($text, $context, $domain)'        => 'Translate with context and echo',
				'esc_html_x($text, $context, $domain)' => 'Translate with context and escape for HTML',
				'esc_attr_x($text, $context, $domain)' => 'Translate with context and escape for attribute',
			),
			'Plural Translation'  => array(
				'_n($single, $plural, $number, $domain)' => 'Translate plural',
				'_nx($single, $plural, $number, $context, $domain)' => 'Translate plural with context',
				'_n_noop($single, $plural, $domain)'     => 'Register plural for later translation',
				'_nx_noop($single, $plural, $context, $domain)' => 'Register plural with context',
			),
		);
	}
}
