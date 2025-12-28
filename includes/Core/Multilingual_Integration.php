<?php
/**
 * WPML/Polylang Integration Helper
 *
 * Provides integration with WPML and Polylang multilingual plugins.
 * Registers translatable strings and ensures consent module works with these plugins.
 *
 * @package     ShahiLegalopsSuite
 * @subpackage  Core
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalopsSuite\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multilingual_Integration Class
 *
 * Handles WPML and Polylang integration.
 *
 * @since 3.0.1
 */
class Multilingual_Integration {

	/**
	 * Text domain
	 *
	 * @since 3.0.1
	 * @var string
	 */
	const TEXT_DOMAIN = 'shahi-legalops-suite';

	/**
	 * String context for Polylang
	 *
	 * @since 3.0.1
	 * @var string
	 */
	const STRING_CONTEXT = 'Shahi LegalOps Suite - Consent';

	/**
	 * Initialize integration
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		// Register Polylang strings
		add_action( 'init', array( $this, 'register_polylang_strings' ), 20 );

		// WPML hooks
		add_filter( 'wpml_register_string', array( $this, 'register_wpml_string' ), 10, 3 );
	}

	/**
	 * Check if WPML is active
	 *
	 * @since 3.0.1
	 * @return bool True if WPML is active
	 */
	public static function is_wpml_active(): bool {
		return defined( 'ICL_SITEPRESS_VERSION' );
	}

	/**
	 * Check if Polylang is active
	 *
	 * @since 3.0.1
	 * @return bool True if Polylang is active
	 */
	public static function is_polylang_active(): bool {
		return function_exists( 'pll_register_string' );
	}

	/**
	 * Register strings with Polylang
	 *
	 * Only runs if Polylang is active.
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function register_polylang_strings() {
		if ( ! self::is_polylang_active() ) {
			return;
		}

		// Consent Banner Strings
		$this->register_polylang_string( 'banner_heading', __( 'We value your privacy', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'banner_message', __( 'We use cookies to enhance your browsing experience and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'banner_accept_all', __( 'Accept All', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'banner_reject_all', __( 'Reject All', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'banner_customize', __( 'Customize', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'banner_learn_more', __( 'Learn More', self::TEXT_DOMAIN ) );

		// Consent Category Labels
		$this->register_polylang_string( 'category_necessary', __( 'Necessary', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'category_necessary_desc', __( 'Essential cookies required for basic site functionality.', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'category_analytics', __( 'Analytics', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'category_analytics_desc', __( 'Cookies that help us understand how visitors interact with our website.', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'category_marketing', __( 'Marketing', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'category_marketing_desc', __( 'Cookies used to deliver personalized advertisements.', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'category_preferences', __( 'Preferences', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'category_preferences_desc', __( 'Cookies that remember your preferences and settings.', self::TEXT_DOMAIN ) );

		// Preferences Modal Strings
		$this->register_polylang_string( 'preferences_title', __( 'Privacy Preferences', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'preferences_description', __( 'Manage your cookie preferences below. You can enable or disable different types of cookies.', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'preferences_save', __( 'Save Preferences', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'preferences_close', __( 'Close', self::TEXT_DOMAIN ) );

		// Action messages
		$this->register_polylang_string( 'consent_saved', __( 'Your preferences have been saved.', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'consent_updated', __( 'Your consent preferences have been updated.', self::TEXT_DOMAIN ) );
		$this->register_polylang_string( 'consent_withdrawn', __( 'Your consent has been withdrawn.', self::TEXT_DOMAIN ) );
	}

	/**
	 * Register a single string with Polylang
	 *
	 * @since 3.0.1
	 * @param string $name   String identifier
	 * @param string $string String value
	 * @return void
	 */
	private function register_polylang_string( string $name, string $string ) {
		if ( function_exists( 'pll_register_string' ) ) {
			pll_register_string( $name, $string, self::STRING_CONTEXT, false );
		}
	}

	/**
	 * Register string with WPML
	 *
	 * @since 3.0.1
	 * @param string $name    String identifier
	 * @param string $value   String value
	 * @param string $context String context
	 * @return string The registered value
	 */
	public function register_wpml_string( $name, $value, $context ) {
		if ( self::is_wpml_active() && function_exists( 'icl_register_string' ) ) {
			icl_register_string( self::STRING_CONTEXT, $name, $value );
		}
		return $value;
	}

	/**
	 * Get translated string (WPML/Polylang compatible)
	 *
	 * @since 3.0.1
	 * @param string $name           String identifier
	 * @param string $default_value  Default value if translation not found
	 * @param string $language_code  Optional language code (WPML only)
	 * @return string Translated string
	 */
	public static function get_translated_string( string $name, string $default_value, string $language_code = null ): string {
		// Try Polylang first
		if ( self::is_polylang_active() && function_exists( 'pll__' ) ) {
			return pll__( $default_value );
		}

		// Try WPML
		if ( self::is_wpml_active() && function_exists( 'icl_t' ) ) {
			return icl_t( self::STRING_CONTEXT, $name, $default_value, false, false, $language_code );
		}

		// Fallback to WordPress translation
		return __( $default_value, self::TEXT_DOMAIN );
	}

	/**
	 * Get current language code
	 *
	 * @since 3.0.1
	 * @return string Language code (e.g., 'en', 'fr', 'ar')
	 */
	public static function get_current_language(): string {
		// Polylang
		if ( self::is_polylang_active() && function_exists( 'pll_current_language' ) ) {
			return pll_current_language();
		}

		// WPML
		if ( self::is_wpml_active() && defined( 'ICL_LANGUAGE_CODE' ) ) {
			return ICL_LANGUAGE_CODE;
		}

		// WordPress locale
		$locale = get_locale();
		return substr( $locale, 0, 2 );
	}

	/**
	 * Get all active languages
	 *
	 * @since 3.0.1
	 * @return array Array of language codes
	 */
	public static function get_active_languages(): array {
		// Polylang
		if ( self::is_polylang_active() && function_exists( 'pll_languages_list' ) ) {
			return pll_languages_list();
		}

		// WPML
		if ( self::is_wpml_active() && function_exists( 'icl_get_languages' ) ) {
			$languages = icl_get_languages( 'skip_missing=0' );
			return array_keys( $languages );
		}

		// Default to current WordPress locale
		$locale = get_locale();
		return array( substr( $locale, 0, 2 ) );
	}

	/**
	 * Check if current site is multilingual
	 *
	 * @since 3.0.1
	 * @return bool True if multilingual plugin is active
	 */
	public static function is_multilingual(): bool {
		return self::is_wpml_active() || self::is_polylang_active();
	}

	/**
	 * Get language switcher HTML
	 *
	 * @since 3.0.1
	 * @param array $args Switcher arguments
	 * @return string Language switcher HTML
	 */
	public static function get_language_switcher( array $args = array() ): string {
		$defaults = array(
			'show_flags'             => true,
			'show_names'             => true,
			'dropdown'               => false,
			'echo'                   => false,
			'hide_if_no_translation' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		// Polylang
		if ( self::is_polylang_active() && function_exists( 'pll_the_languages' ) ) {
			$pll_args = array(
				'dropdown'               => $args['dropdown'] ? 1 : 0,
				'show_flags'             => $args['show_flags'] ? 1 : 0,
				'show_names'             => $args['show_names'] ? 1 : 0,
				'hide_if_no_translation' => $args['hide_if_no_translation'] ? 1 : 0,
				'echo'                   => 0,
			);
			return pll_the_languages( $pll_args );
		}

		// WPML
		if ( self::is_wpml_active() && function_exists( 'wpml_get_language_switcher' ) ) {
			ob_start();
			do_action( 'wpml_add_language_selector' );
			return ob_get_clean();
		}

		return '';
	}
}
