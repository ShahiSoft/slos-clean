<?php
/**
 * Geolocation Detection Service
 *
 * Detects visitor geography and maps to regulation regions
 * (e.g., EU/GDPR, US-CA/CCPA, BR/LGPD). Results are cached
 * to minimize external lookups and exposed via REST.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Geo_Service
 */
class Geo_Service extends Base_Service {

    /**
     * Transient cache TTL (seconds)
     *
     * @var int
     */
    private $cache_ttl = 24 * 60 * 60; // 24 hours

    /**
     * Selected provider (ipapi | ipinfo | ip-api)
     * @var string
     */
    private $provider = 'ipapi';

    /**
     * Load settings
     */
    private function load_settings(): void {
        $settings = get_option( 'shahi_legalflowsuite_settings', array() );
        if ( isset( $settings['geolocation_cache_ttl'] ) ) {
            $ttl = (int) $settings['geolocation_cache_ttl'];
            if ( $ttl > 0 ) {
                $this->cache_ttl = $ttl;
            }
        }
        if ( isset( $settings['geolocation_provider'] ) && is_string( $settings['geolocation_provider'] ) ) {
            $prov = $settings['geolocation_provider'];
            if ( in_array( $prov, array( 'ipapi', 'ipinfo', 'ip-api' ), true ) ) {
                $this->provider = $prov;
            }
        }
    }

    /**
     * Detect region for current request
     *
     * @return array{region:string,country?:string,country_code?:string,state?:string,source?:string,cached?:bool,timestamp?:int}
     */
    public function get_region_for_request(): array {
        $this->load_settings();
        $ip = $this->get_client_ip();
        $cache_key = 'slos_geo_' . md5( (string) $ip );

        $cached = get_transient( $cache_key );
        if ( is_array( $cached ) && isset( $cached['region'] ) ) {
            $cached['cached']    = true;
            $cached['timestamp'] = time();
            return $cached;
        }

        $region_data = $this->detect_region_by_provider( $ip );

        if ( ! is_array( $region_data ) || empty( $region_data['region'] ) ) {
            $region_data = $this->detect_region_by_headers();
        }

        if ( empty( $region_data['region'] ) ) {
            $region_data = array( 'region' => 'GLOBAL', 'source' => 'fallback' );
        }

        set_transient( $cache_key, $region_data, $this->cache_ttl );
        $region_data['cached']    = false;
        $region_data['timestamp'] = time();

        return $region_data;
    }

    /**
     * Detect region using external provider (filterable)
     *
     * @param string|null $ip Client IP
     * @return array|null
     */
    public function detect_region_by_provider( ?string $ip = null ): ?array {
        $provider = apply_filters( 'slos_geo_provider', $this->provider ); // ipapi | ipinfo | ip-api
        $url      = null;

        if ( 'ipapi' === $provider ) {
            // https://ipapi.co/json
            $url = 'https://ipapi.co/json/';
        } elseif ( 'ipinfo' === $provider ) {
            // https://ipinfo.io/json
            $url = 'https://ipinfo.io/json';
        } elseif ( 'ip-api' === $provider ) {
            // http://ip-api.com/json
            $url = 'http://ip-api.com/json';
        }

        $url = apply_filters( 'slos_geo_provider_url', $url, $provider, $ip );
        if ( empty( $url ) ) {
            return null;
        }

        $args = array(
            'timeout' => 3,
            'headers' => array(
                'Accept' => 'application/json',
            ),
        );

        $response = wp_remote_get( $url, $args );
        if ( is_wp_error( $response ) ) {
            $this->add_error( 'geo_provider_error', $response->get_error_message() );
            return null;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code > 299 ) {
            $this->add_error( 'geo_provider_http', 'Provider HTTP status ' . $code );
            return null;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );
        if ( ! is_array( $data ) ) {
            $this->add_error( 'geo_provider_parse', 'Invalid provider JSON' );
            return null;
        }

        // Normalize fields
        $country      = $data['country_name'] ?? $data['country'] ?? '';
        $country_code = strtoupper( (string) ( $data['country_code'] ?? $data['country'] ?? $data['countryCode'] ?? '' ) );
        $region_name  = $data['region'] ?? $data['region_name'] ?? '';
        $region_code  = strtoupper( (string) ( $data['region_code'] ?? $data['region'] ?? $data['regionName'] ?? '' ) );

        $region = $this->map_to_regulation_region( $country_code, $region_code );

        return array(
            'region'       => $region,
            'country'      => $country,
            'country_code' => $country_code,
            'state'        => $region_code,
            'source'       => 'provider:' . $provider,
        );
    }

    /**
     * Header-based heuristic fallback
     *
     * @return array
     */
    public function detect_region_by_headers(): array {
        $accept_lang = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) : '';
        $lang        = substr( $accept_lang, 0, 2 );

        $eu_langs = array( 'en', 'fr', 'de', 'es', 'it', 'nl', 'pl', 'cs', 'sk', 'sl', 'hu', 'ro', 'bg', 'sv', 'fi', 'da', 'et', 'lv', 'lt', 'el', 'pt' );
        $br_langs = array( 'pt' );
        $us_langs = array( 'en' );

        if ( in_array( $lang, $eu_langs, true ) ) {
            return array( 'region' => 'EU', 'source' => 'headers' );
        }
        if ( in_array( $lang, $br_langs, true ) ) {
            return array( 'region' => 'BR', 'source' => 'headers' );
        }
        if ( in_array( $lang, $us_langs, true ) ) {
            return array( 'region' => 'US', 'source' => 'headers' );
        }
        return array( 'region' => 'GLOBAL', 'source' => 'headers' );
    }

    /**
     * Map country/state to regulation region code
     *
     * @param string $country_code ISO country code
     * @param string $state_code   Region/state code
     * @return string Region identifier (EU, US-CA, US, BR, UK, EEA, ZA, AU, GLOBAL)
     */
    public function map_to_regulation_region( string $country_code, string $state_code = '' ): string {
        $country_code = strtoupper( $country_code );
        $state_code   = strtoupper( $state_code );

        $eu_countries = array( 'AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE' );
        $eea_countries = array( 'IS', 'LI', 'NO' );

        if ( in_array( $country_code, $eu_countries, true ) || in_array( $country_code, $eea_countries, true ) || 'GB' === $country_code ) {
            return 'EU'; // Treat UK/EEA as GDPR for consent
        }

        if ( 'US' === $country_code ) {
            if ( 'CA' === $state_code ) {
                return 'US-CA'; // CCPA/CPRA
            }
            // Future: additional state-specific regs can be added via filter
            $us_region = apply_filters( 'slos_geo_us_region', '', $state_code );
            if ( is_string( $us_region ) && ! empty( $us_region ) ) {
                return $us_region;
            }
            return 'US';
        }

        if ( 'BR' === $country_code ) {
            return 'BR'; // LGPD
        }
        if ( 'ZA' === $country_code ) {
            return 'ZA'; // POPIA
        }
        if ( 'AU' === $country_code ) {
            return 'AU'; // Australia Privacy Act
        }
        if ( 'IN' === $country_code ) {
            return 'IN'; // DPDP
        }

        return 'GLOBAL';
    }

    /**
     * Determine client IP
     *
     * @return string
     */
    public function get_client_ip(): string {
        $ip = '';
        $keys = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy list
            'HTTP_X_REAL_IP',        // Nginx proxy
            'REMOTE_ADDR',
        );
        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = sanitize_text_field( wp_unslash( (string) $_SERVER[ $key ] ) );
                break;
            }
        }
        // If we got a list, take first IP
        if ( strpos( $ip, ',' ) !== false ) {
            $parts = explode( ',', $ip );
            $ip    = trim( $parts[0] );
        }
        return $ip;
    }

    /**
     * Choose default banner template based on region
     *
     * @param string $region
     * @return string One of 'eu','ccpa','simple','advanced'
     */
    public function map_region_to_template( string $region ): string {
        $region = strtoupper( $region );
        switch ( $region ) {
            case 'EU':
                return 'eu';
            case 'US-CA':
                return 'ccpa';
            case 'BR':
                return 'advanced'; // show granular options
            default:
                return 'simple';
        }
    }
}
