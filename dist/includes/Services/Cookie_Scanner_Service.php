<?php
/**
 * Cookie Scanner Service
 *
 * Provides cookie and storage key classification, inventory management,
 * and processing of client-side scan reports. Designed to work with
 * consent categories and the REST API.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cookie_Scanner_Service extends Base_Service {

	/**
	 * Option key for last scan inventory
	 *
	 * @var string
	 */
	private $option_key_inventory = 'slos_cookie_inventory';

	/**
	 * Option key for scan metadata
	 *
	 * @var string
	 */
	private $option_key_scan_meta = 'slos_cookie_scan_meta';

	/**
	 * Get supported cookie categories
	 *
	 * @return array
	 */
	public function get_categories(): array {
		return array(
			array(
				'id'       => 'necessary',
				'label'    => __( 'Necessary', 'shahi-legalflowsuite' ),
				'required' => true,
			),
			array(
				'id'       => 'functional',
				'label'    => __( 'Functional', 'shahi-legalflowsuite' ),
				'required' => false,
			),
			array(
				'id'       => 'analytics',
				'label'    => __( 'Analytics', 'shahi-legalflowsuite' ),
				'required' => false,
			),
			array(
				'id'       => 'marketing',
				'label'    => __( 'Marketing', 'shahi-legalflowsuite' ),
				'required' => false,
			),
			array(
				'id'       => 'preferences',
				'label'    => __( 'Preferences', 'shahi-legalflowsuite' ),
				'required' => false,
			),
			array(
				'id'       => 'personalization',
				'label'    => __( 'Personalization', 'shahi-legalflowsuite' ),
				'required' => false,
			),
		);
	}

	/**
	 * Default cookie naming patterns mapped to categories and vendors
	 *
	 * @return array
	 */
	public function get_default_patterns(): array {
		return array(
			// Necessary
			array(
				'pattern'     => '/^PHPSESSID$/',
				'category'    => 'necessary',
				'vendor'      => 'PHP',
				'description' => 'Session identifier',
			),
			array(
				'pattern'     => '/^wordpress_[a-f0-9]+/i',
				'category'    => 'necessary',
				'vendor'      => 'WordPress',
				'description' => 'WordPress auth/session',
			),
			array(
				'pattern'     => '/^wp-settings-/',
				'category'    => 'necessary',
				'vendor'      => 'WordPress',
				'description' => 'User settings',
			),
			array(
				'pattern'     => '/^woocommerce_/',
				'category'    => 'necessary',
				'vendor'      => 'WooCommerce',
				'description' => 'Cart/session',
			),

			// Analytics
			array(
				'pattern'     => '/^_ga$/',
				'category'    => 'analytics',
				'vendor'      => 'Google Analytics',
				'description' => 'Client ID',
			),
			array(
				'pattern'     => '/^_gid$/',
				'category'    => 'analytics',
				'vendor'      => 'Google Analytics',
				'description' => 'Session ID',
			),
			array(
				'pattern'     => '/^_gat(_.*)?$/',
				'category'    => 'analytics',
				'vendor'      => 'Google Analytics',
				'description' => 'Throttle',
			),
			array(
				'pattern'     => '/^_hj.*/',
				'category'    => 'analytics',
				'vendor'      => 'Hotjar',
				'description' => 'Hotjar analytics',
			),
			array(
				'pattern'     => '/^ajs_/',
				'category'    => 'analytics',
				'vendor'      => 'Segment',
				'description' => 'Segment analytics',
			),

			// Marketing
			array(
				'pattern'     => '/^_fbp$/',
				'category'    => 'marketing',
				'vendor'      => 'Facebook',
				'description' => 'Facebook Pixel',
			),
			array(
				'pattern'     => '/^fr$/',
				'category'    => 'marketing',
				'vendor'      => 'Facebook',
				'description' => 'Facebook cookie',
			),
			array(
				'pattern'     => '/^IDE$/',
				'category'    => 'marketing',
				'vendor'      => 'Google Ads',
				'description' => 'DoubleClick ID',
			),
			array(
				'pattern'     => '/^gcl_au$/',
				'category'    => 'marketing',
				'vendor'      => 'Google Ads',
				'description' => 'Ad click',
			),
			array(
				'pattern'     => '/^_gcl_/',
				'category'    => 'marketing',
				'vendor'      => 'Google Ads',
				'description' => 'Ad click',
			),

			// Functional / Preferences
			array(
				'pattern'     => '/^intercom.*/',
				'category'    => 'functional',
				'vendor'      => 'Intercom',
				'description' => 'Chat widget',
			),
			array(
				'pattern'     => '/^hs.*/',
				'category'    => 'functional',
				'vendor'      => 'HubSpot',
				'description' => 'HubSpot functionality',
			),
			array(
				'pattern'     => '/^sf.*/',
				'category'    => 'functional',
				'vendor'      => 'Salesforce',
				'description' => 'Salesforce functionality',
			),
			array(
				'pattern'     => '/^pll_language$/',
				'category'    => 'preferences',
				'vendor'      => 'Polylang',
				'description' => 'Language preference',
			),
		);
	}

	/**
	 * Classify a cookie name into category and vendor
	 *
	 * @param string $name Cookie name
	 * @return array Result with category and vendor
	 */
	public function classify_cookie( string $name ): array {
		$patterns = $this->get_default_patterns();
		foreach ( $patterns as $rule ) {
			if ( preg_match( $rule['pattern'], $name ) ) {
				return array(
					'category'    => $rule['category'],
					'vendor'      => $rule['vendor'],
					'description' => $rule['description'],
					'matched'     => $rule['pattern'],
				);
			}
		}
		// Fallback heuristics
		if ( stripos( $name, 'lang' ) !== false ) {
			return array(
				'category'    => 'preferences',
				'vendor'      => 'Unknown',
				'description' => 'Language preference',
				'matched'     => 'heuristic:lang',
			);
		}
		if ( stripos( $name, 'track' ) !== false || stripos( $name, 'analytics' ) !== false ) {
			return array(
				'category'    => 'analytics',
				'vendor'      => 'Unknown',
				'description' => 'Tracking/analytics',
				'matched'     => 'heuristic:analytics',
			);
		}
		return array(
			'category'    => 'functional',
			'vendor'      => 'Unknown',
			'description' => 'Unclassified functional',
			'matched'     => 'heuristic:functional',
		);
	}

	/**
	 * Process scan report from client
	 *
	 * @param array $report Report data: cookies[], localStorageKeys[], sessionStorageKeys[], url, userAgent
	 * @return array Processed inventory breakdown
	 */
	public function process_scan_report( array $report ): array {
		$cookies      = isset( $report['cookies'] ) ? (array) $report['cookies'] : array();
		$local_keys   = isset( $report['localStorageKeys'] ) ? (array) $report['localStorageKeys'] : array();
		$session_keys = isset( $report['sessionStorageKeys'] ) ? (array) $report['sessionStorageKeys'] : array();
		$url          = isset( $report['url'] ) ? sanitize_text_field( $report['url'] ) : '';
		$user_agent   = isset( $report['userAgent'] ) ? sanitize_text_field( $report['userAgent'] ) : '';
		$timestamp    = time();

		$inventory = array(
			'summary'   => array(
				'totalCookies'     => count( $cookies ),
				'totalLocalKeys'   => count( $local_keys ),
				'totalSessionKeys' => count( $session_keys ),
				'scannedAt'        => $timestamp,
				'url'              => $url,
			),
			'cookies'   => array(),
			'storage'   => array(
				'localStorage'   => array(),
				'sessionStorage' => array(),
			),
			'breakdown' => array(
				'necessary'       => array(),
				'functional'      => array(),
				'analytics'       => array(),
				'marketing'       => array(),
				'preferences'     => array(),
				'personalization' => array(),
			),
		);

		foreach ( $cookies as $cookie ) {
			$name  = isset( $cookie['name'] ) ? sanitize_text_field( $cookie['name'] ) : '';
			$value = isset( $cookie['value'] ) ? sanitize_text_field( $cookie['value'] ) : '';
			if ( '' === $name ) {
				continue;
			}
			$classification         = $this->classify_cookie( $name );
			$item                   = array(
				'name'        => $name,
				'value'       => $value,
				'category'    => $classification['category'],
				'vendor'      => $classification['vendor'],
				'description' => $classification['description'],
				'matched'     => $classification['matched'],
			);
			$inventory['cookies'][] = $item;
			$inventory['breakdown'][ $classification['category'] ][] = $item;
		}

		foreach ( $local_keys as $key ) {
			$key                                    = sanitize_text_field( $key );
			$inventory['storage']['localStorage'][] = array(
				'key'      => $key,
				'category' => $this->classify_storage_key( $key ),
			);
		}

		foreach ( $session_keys as $key ) {
			$key                                      = sanitize_text_field( $key );
			$inventory['storage']['sessionStorage'][] = array(
				'key'      => $key,
				'category' => $this->classify_storage_key( $key ),
			);
		}

		// Attach environment metadata
		$inventory['environment'] = array(
			'userAgent' => $user_agent,
			'ipHash'    => $this->hash_ip( $this->get_user_ip() ),
		);

		// Persist last inventory in option for admin review
		update_option( $this->option_key_inventory, wp_json_encode( $inventory ), false );

		return $inventory;
	}

	/**
	 * Classify storage key according to patterns
	 *
	 * @param string $key Storage key
	 * @return string Category id
	 */
	public function classify_storage_key( string $key ): string {
		$k = strtolower( $key );
		if ( false !== strpos( $k, 'consent' ) ) {
			return 'preferences';
		}
		if ( false !== strpos( $k, 'ga' ) || false !== strpos( $k, 'analytics' ) ) {
			return 'analytics';
		}
		if ( false !== strpos( $k, 'ad' ) || false !== strpos( $k, 'gclid' ) ) {
			return 'marketing';
		}
		return 'functional';
	}

	/**
	 * Start a new scan and record metadata
	 *
	 * @since 3.1.1
	 * @param string $scan_type Type of scan: 'manual', 'auto', 'scheduled'
	 * @param array  $options   Scan options (e.g., pages_to_scan, coverage_level)
	 * @return bool Success
	 */
	public function start_scan( string $scan_type = 'manual', array $options = array() ): bool {
		$metadata = array(
			'scan_type'      => $scan_type,
			'started_at'     => current_time( 'mysql' ),
			'completed_at'   => null,
			'status'         => 'in_progress',
			'pages_scanned'  => isset( $options['pages_to_scan'] ) ? count( $options['pages_to_scan'] ) : 1,
			'coverage_level' => $options['coverage_level'] ?? 'basic',
			'options'        => $options,
		);

		return update_option( $this->option_key_scan_meta, $metadata, false );
	}

	/**
	 * Complete a scan and update metadata
	 *
	 * @since 3.1.1
	 * @param array $results Scan results with cookie counts, errors, etc.
	 * @return bool Success
	 */
	public function complete_scan( array $results = array() ): bool {
		$metadata = $this->get_scan_metadata();

		if ( empty( $metadata ) ) {
			return false;
		}

		$started_at   = isset( $metadata['started_at'] ) ? strtotime( $metadata['started_at'] ) : time();
		$completed_at = current_time( 'mysql' );
		$duration     = time() - $started_at;

		$metadata['completed_at']  = $completed_at;
		$metadata['status']        = 'completed';
		$metadata['duration']      = $duration;
		$metadata['cookies_found'] = $results['cookies_found'] ?? 0;
		$metadata['errors']        = $results['errors'] ?? array();
		$metadata['results']       = $results;

		// Update scan time option for backward compatibility
		update_option( 'slos_cookie_scan_time', $completed_at, false );

		$success = update_option( $this->option_key_scan_meta, $metadata, false );

		// Fire action for document staleness detection
		if ( $success ) {
			$cookies = get_option( 'slos_cookie_inventory', array() );
			do_action( 'slos_cookies_updated', $cookies );
		}

		return $success;
	}

	/**
	 * Get scan metadata
	 *
	 * @since 3.1.1
	 * @return array Scan metadata or empty array if none
	 */
	public function get_scan_metadata(): array {
		$metadata = get_option( $this->option_key_scan_meta, array() );
		return is_array( $metadata ) ? $metadata : array();
	}

	/**
	 * Clear scan metadata
	 *
	 * @since 3.1.1
	 * @return bool Success
	 */
	public function clear_scan_metadata(): bool {
		return delete_option( $this->option_key_scan_meta );
	}

	/**
	 * Get last stored inventory
	 *
	 * @return array
	 */
	public function get_inventory(): array {
		$json = get_option( $this->option_key_inventory, '' );
		if ( empty( $json ) ) {
			return array();
		}
		$data = json_decode( $json, true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Clear stored inventory
	 *
	 * @return bool
	 */
	public function clear_inventory(): bool {
		return delete_option( $this->option_key_inventory );
	}
}
