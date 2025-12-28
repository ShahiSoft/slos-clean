<?php
/**
 * Locale Helper
 *
 * Provides locale management functionality for multi-language legal documents.
 * Supports 25+ languages with RTL detection and fallback logic.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Helpers
 * @since      4.0.0
 */

namespace ShahiLegalFlowSuite\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Locale_Helper
 *
 * @since 4.0.0
 */
class Locale_Helper {

    /**
     * Supported locales
     *
     * @since 4.0.0
     * @var array
     */
    private static $supported_locales = array(
        'en_US' => array( 'name' => 'English (United States)', 'rtl' => false ),
        'en_GB' => array( 'name' => 'English (United Kingdom)', 'rtl' => false ),
        'fr_FR' => array( 'name' => 'Français (France)', 'rtl' => false ),
        'de_DE' => array( 'name' => 'Deutsch (Deutschland)', 'rtl' => false ),
        'es_ES' => array( 'name' => 'Español (España)', 'rtl' => false ),
        'it_IT' => array( 'name' => 'Italiano (Italia)', 'rtl' => false ),
        'pt_PT' => array( 'name' => 'Português (Portugal)', 'rtl' => false ),
        'pt_BR' => array( 'name' => 'Português (Brasil)', 'rtl' => false ),
        'nl_NL' => array( 'name' => 'Nederlands (Nederland)', 'rtl' => false ),
        'pl_PL' => array( 'name' => 'Polski (Polska)', 'rtl' => false ),
        'el_GR' => array( 'name' => 'Ελληνικά (Ελλάδα)', 'rtl' => false ),
        'cs_CZ' => array( 'name' => 'Čeština (Česko)', 'rtl' => false ),
        'hu_HU' => array( 'name' => 'Magyar (Magyarország)', 'rtl' => false ),
        'ro_RO' => array( 'name' => 'Română (România)', 'rtl' => false ),
        'sv_SE' => array( 'name' => 'Svenska (Sverige)', 'rtl' => false ),
        'no_NO' => array( 'name' => 'Norsk (Norge)', 'rtl' => false ),
        'da_DK' => array( 'name' => 'Dansk (Danmark)', 'rtl' => false ),
        'fi_FI' => array( 'name' => 'Suomi (Suomi)', 'rtl' => false ),
        'uk_UA' => array( 'name' => 'Українська (Україна)', 'rtl' => false ),
        'ru_RU' => array( 'name' => 'Русский (Россия)', 'rtl' => false ),
        'ja_JP' => array( 'name' => '日本語 (日本)', 'rtl' => false ),
        'ko_KR' => array( 'name' => '한국어 (대한민국)', 'rtl' => false ),
        'zh_CN' => array( 'name' => '简体中文 (中国)', 'rtl' => false ),
        'zh_TW' => array( 'name' => '繁體中文 (台灣)', 'rtl' => false ),
        'ar'    => array( 'name' => 'العربية', 'rtl' => true ),
        'he_IL' => array( 'name' => 'עברית (ישראל)', 'rtl' => true ),
    );

    /**
     * Get all supported locales
     *
     * @since 4.0.0
     * @return array Associative array of locale code => locale data
     */
    public static function get_supported_locales() {
        return apply_filters( 'slos_supported_locales', self::$supported_locales );
    }

    /**
     * Get locale name
     *
     * @since 4.0.0
     * @param string $locale Locale code
     * @return string Locale name or code if not found
     */
    public static function get_locale_name( $locale ) {
        $locales = self::get_supported_locales();
        return isset( $locales[ $locale ]['name'] ) ? $locales[ $locale ]['name'] : $locale;
    }

    /**
     * Check if locale is RTL
     *
     * @since 4.0.0
     * @param string $locale Locale code
     * @return bool True if RTL, false otherwise
     */
    public static function is_rtl( $locale ) {
        $locales = self::get_supported_locales();
        return isset( $locales[ $locale ]['rtl'] ) && $locales[ $locale ]['rtl'];
    }

    /**
     * Get current locale
     *
     * Falls back to site locale if not explicitly set.
     *
     * @since 4.0.0
     * @return string Locale code
     */
    public static function get_current_locale() {
        // Check for explicit locale in query string (admin context)
        if ( isset( $_GET['locale'] ) ) {
            $locale = sanitize_text_field( wp_unslash( $_GET['locale'] ) );
            if ( self::is_supported( $locale ) ) {
                return $locale;
            }
        }

        // Use WordPress locale
        $wp_locale = get_locale();
        
        // If WordPress locale is supported, use it
        if ( self::is_supported( $wp_locale ) ) {
            return $wp_locale;
        }

        // Default to en_US
        return 'en_US';
    }

    /**
     * Check if locale is supported
     *
     * @since 4.0.0
     * @param string $locale Locale code
     * @return bool True if supported, false otherwise
     */
    public static function is_supported( $locale ) {
        $locales = self::get_supported_locales();
        return isset( $locales[ $locale ] );
    }

    /**
     * Validate and sanitize locale
     *
     * @since 4.0.0
     * @param string $locale Locale code
     * @return string Valid locale code or en_US if invalid
     */
    public static function validate_locale( $locale ) {
        $locale = sanitize_text_field( $locale );
        return self::is_supported( $locale ) ? $locale : 'en_US';
    }

    /**
     * Get fallback locale
     *
     * Returns the fallback locale for a given locale.
     * For regional variants, falls back to base language.
     * For non-English, falls back to en_US.
     *
     * @since 4.0.0
     * @param string $locale Locale code
     * @return string Fallback locale code
     */
    public static function get_fallback_locale( $locale ) {
        // If it's a regional variant, try base language
        if ( strpos( $locale, '_' ) !== false ) {
            list( $lang, $region ) = explode( '_', $locale, 2 );
            
            // Try base language with different region
            $base_variants = array(
                'pt_BR' => 'pt_PT',
                'pt_PT' => 'en_US',
                'en_GB' => 'en_US',
                'zh_TW' => 'zh_CN',
                'zh_CN' => 'en_US',
            );
            
            if ( isset( $base_variants[ $locale ] ) ) {
                return $base_variants[ $locale ];
            }
        }

        // Default fallback to en_US
        return 'en_US';
    }

    /**
     * Get locale direction (ltr or rtl)
     *
     * @since 4.0.0
     * @param string $locale Locale code
     * @return string 'rtl' or 'ltr'
     */
    public static function get_direction( $locale ) {
        return self::is_rtl( $locale ) ? 'rtl' : 'ltr';
    }

    /**
     * Get locale for dropdown/select
     *
     * @since 4.0.0
     * @return array Array of locale code => locale name
     */
    public static function get_locale_options() {
        $locales = self::get_supported_locales();
        $options = array();
        
        foreach ( $locales as $code => $data ) {
            $options[ $code ] = $data['name'];
        }
        
        return $options;
    }

    /**
     * Get HTML dir attribute for locale
     *
     * @since 4.0.0
     * @param string $locale Locale code
     * @return string HTML dir attribute value
     */
    public static function get_html_dir_attribute( $locale ) {
        return self::get_direction( $locale );
    }

    /**
     * Get HTML lang attribute for locale
     *
     * @since 4.0.0
     * @param string $locale Locale code (e.g., en_US)
     * @return string HTML lang attribute value (e.g., en-US)
     */
    public static function get_html_lang_attribute( $locale ) {
        return str_replace( '_', '-', $locale );
    }
}
