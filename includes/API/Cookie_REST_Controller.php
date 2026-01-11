<?php
/**
 * Cookie Scanner REST Controller
 *
 * Registers endpoints for cookie categories, patterns, reporting scan results,
 * and retrieving/clearing the last inventory.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  API
 * @version     3.0.1
 */

namespace ShahiLegalFlowSuite\API;

use WP_REST_Request;
use ShahiLegalFlowSuite\Services\Cookie_Scanner_Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cookie_REST_Controller extends Base_REST_Controller {

	/** @var Cookie_Scanner_Service */
	private $service;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->rest_base = 'cookies';
		$this->service   = new Cookie_Scanner_Service();
	}

	/**
	 * Register routes
	 */
	public function register_routes() {
		// GET /cookies/categories
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/categories',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_categories' ),
					'permission_callback' => array( $this, 'check_read_permission' ),
				),
			)
		);

		// GET /cookies/patterns
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/patterns',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_patterns' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);

		// POST /cookies/report
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/report',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'report_scan' ),
					'permission_callback' => array( $this, 'check_create_permission' ),
					'args'                => array(
						'cookies'            => array( 'required' => false ),
						'localStorageKeys'   => array( 'required' => false ),
						'sessionStorageKeys' => array( 'required' => false ),
						'url'                => array( 'required' => false ),
						'userAgent'          => array( 'required' => false ),
					),
				),
			)
		);

		// POST /cookies/scan - Trigger server-side scan
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/scan',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'trigger_scan' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
					'args'                => array(
						'site_url' => array( 'required' => false ),
					),
				),
			)
		);

		// GET /cookies/inventory
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/inventory',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_inventory' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'clear_inventory' ),
					'permission_callback' => array( $this, 'check_admin_permission' ),
				),
			)
		);
	}

	/**
	 * Get categories
	 */
	public function get_categories( WP_REST_Request $request ) {
		$cats = $this->service->get_categories();
		return $this->success_response( $cats );
	}

	/**
	 * Get default patterns (admin only)
	 */
	public function get_patterns( WP_REST_Request $request ) {
		$patterns = $this->service->get_default_patterns();
		return $this->success_response( $patterns );
	}

	/**
	 * Report scan results from client
	 */
	public function report_scan( WP_REST_Request $request ) {
		$payload = array(
			'cookies'            => $request->get_param( 'cookies' ),
			'localStorageKeys'   => $request->get_param( 'localStorageKeys' ),
			'sessionStorageKeys' => $request->get_param( 'sessionStorageKeys' ),
			'url'                => $request->get_param( 'url' ),
			'userAgent'          => $request->get_param( 'userAgent' ),
		);

		$processed = $this->service->process_scan_report( $payload );
		if ( $this->service->has_errors() ) {
			return $this->error_response( 'scan_error', __( 'Failed to process scan report', 'shahi-legalflowsuite' ), 400, array( 'errors' => $this->service->get_errors() ) );
		}
		return $this->success_response( $processed, __( 'Scan report received', 'shahi-legalflowsuite' ), 201 );
	}

	/**
	 * Get last inventory
	 */
	public function get_inventory( WP_REST_Request $request ) {
		$inv = $this->service->get_inventory();
		return $this->success_response( $inv );
	}

	/**
	 * Clear stored inventory
	 */
	public function clear_inventory( WP_REST_Request $request ) {
		$ok = $this->service->clear_inventory();
		if ( ! $ok ) {
			return $this->error_response( 'clear_failed', __( 'Failed to clear inventory', 'shahi-legalflowsuite' ), 500 );
		}
		return $this->success_response( array( 'cleared' => true ), __( 'Inventory cleared', 'shahi-legalflowsuite' ) );
	}

	/**
	 * Trigger server-side cookie scan
	 *
	 * Performs an actual HTTP request to the site to detect cookies
	 * from Set-Cookie headers and analyzes common cookie patterns.
	 *
	 * @since 3.0.3
	 * @param WP_REST_Request $request Request object
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function trigger_scan( WP_REST_Request $request ) {
		$site_url = $request->get_param( 'site_url' );

		if ( empty( $site_url ) ) {
			$site_url = home_url();
		}

		// Record scan start metadata
		$this->service->start_scan(
			'manual',
			array(
				'pages_to_scan'  => array( $site_url ),
				'coverage_level' => 'basic',
			)
		);

		$detected_cookies = array();
		$errors           = array();

		// Perform HTTP request to detect Set-Cookie headers
		$response = wp_remote_get(
			$site_url,
			array(
				'timeout'     => 30,
				'sslverify'   => false,
				'redirection' => 3,
				'headers'     => array(
					'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			// Get Set-Cookie headers
			$headers     = wp_remote_retrieve_headers( $response );
			$set_cookies = array();

			// Headers can be array or Requests_Utility_CaseInsensitiveDictionary
			if ( is_array( $headers ) ) {
				$set_cookies = isset( $headers['set-cookie'] ) ? (array) $headers['set-cookie'] : array();
			} elseif ( is_object( $headers ) && method_exists( $headers, 'getAll' ) ) {
				$all_headers = $headers->getAll();
				$set_cookies = isset( $all_headers['set-cookie'] ) ? (array) $all_headers['set-cookie'] : array();
			}

			foreach ( $set_cookies as $cookie_string ) {
				$parsed = $this->parse_set_cookie_header( $cookie_string );
				if ( $parsed ) {
					$classification     = $this->service->classify_cookie( $parsed['name'] );
					$detected_cookies[] = array(
						'id'          => sanitize_title( $parsed['name'] ) . '-' . time(),
						'name'        => $parsed['name'],
						'value'       => substr( $parsed['value'], 0, 50 ), // Truncate value for privacy
						'domain'      => $parsed['domain'] ?? wp_parse_url( $site_url, PHP_URL_HOST ),
						'path'        => $parsed['path'] ?? '/',
						'expires'     => $parsed['expires'] ?? '',
						'secure'      => $parsed['secure'] ?? false,
						'httponly'    => $parsed['httponly'] ?? false,
						'category'    => $classification['category'],
						'vendor'      => $classification['vendor'],
						'description' => $classification['description'],
						'status'      => 'categorized',
						'detected_at' => current_time( 'mysql' ),
					);
				}
			}
		}

		// Also scan for common first-party cookies by checking known patterns
		// These would typically be set by JavaScript on page load
		$common_wp_cookies = array(
			'wordpress_test_cookie' => array(
				'category'    => 'necessary',
				'vendor'      => 'WordPress',
				'description' => 'WordPress test cookie',
			),
			'wordpress_logged_in'   => array(
				'category'    => 'necessary',
				'vendor'      => 'WordPress',
				'description' => 'WordPress login session',
			),
			'wp-settings'           => array(
				'category'    => 'necessary',
				'vendor'      => 'WordPress',
				'description' => 'WordPress user settings',
			),
		);

		// Check if any WordPress cookies might be set (based on user being logged in)
		if ( is_user_logged_in() ) {
			foreach ( $common_wp_cookies as $name => $info ) {
				// Only add if not already detected
				$exists = array_filter(
					$detected_cookies,
					function ( $c ) use ( $name ) {
						return strpos( $c['name'], $name ) !== false;
					}
				);
				if ( empty( $exists ) ) {
					$detected_cookies[] = array(
						'id'          => sanitize_title( $name ) . '-' . time(),
						'name'        => $name,
						'value'       => '[session]',
						'domain'      => wp_parse_url( $site_url, PHP_URL_HOST ),
						'path'        => '/',
						'expires'     => '',
						'secure'      => is_ssl(),
						'httponly'    => true,
						'category'    => $info['category'],
						'vendor'      => $info['vendor'],
						'description' => $info['description'],
						'status'      => 'categorized',
						'detected_at' => current_time( 'mysql' ),
					);
				}
			}
		}

		// Save detected cookies to database
		if ( ! empty( $detected_cookies ) ) {
			update_option( 'slos_detected_cookies', $detected_cookies );
			update_option( 'slos_cookie_scan_time', current_time( 'mysql' ) );
		}

		// Complete scan metadata
		$this->service->complete_scan(
			array(
				'cookies_found' => count( $detected_cookies ),
				'errors'        => $errors,
			)
		);

		/* translators: %d: number of cookies detected */
		return $this->success_response(
			$detected_cookies,
			sprintf( __( 'Cookie scan completed. %d cookies detected.', 'shahi-legalflowsuite' ), count( $detected_cookies ) )
		);
	}

	/**
	 * Parse Set-Cookie header string
	 *
	 * @param string $cookie_string Raw Set-Cookie header value
	 * @return array|null Parsed cookie data or null if invalid
	 */
	private function parse_set_cookie_header( $cookie_string ) {
		if ( empty( $cookie_string ) ) {
			return null;
		}

		$parts = explode( ';', $cookie_string );
		$main  = array_shift( $parts );

		// Parse name=value
		$equals_pos = strpos( $main, '=' );
		if ( $equals_pos === false ) {
			return null;
		}

		$name  = trim( substr( $main, 0, $equals_pos ) );
		$value = trim( substr( $main, $equals_pos + 1 ) );

		if ( empty( $name ) ) {
			return null;
		}

		$cookie = array(
			'name'     => $name,
			'value'    => $value,
			'domain'   => '',
			'path'     => '/',
			'expires'  => '',
			'secure'   => false,
			'httponly' => false,
			'samesite' => '',
		);

		// Parse attributes
		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( empty( $part ) ) {
				continue;
			}

			$attr_parts = explode( '=', $part, 2 );
			$attr_name  = strtolower( trim( $attr_parts[0] ) );
			$attr_value = isset( $attr_parts[1] ) ? trim( $attr_parts[1] ) : '';

			switch ( $attr_name ) {
				case 'domain':
					$cookie['domain'] = ltrim( $attr_value, '.' );
					break;
				case 'path':
					$cookie['path'] = $attr_value;
					break;
				case 'expires':
					$cookie['expires'] = $attr_value;
					break;
				case 'max-age':
					$cookie['expires'] = gmdate( 'Y-m-d H:i:s', time() + intval( $attr_value ) );
					break;
				case 'secure':
					$cookie['secure'] = true;
					break;
				case 'httponly':
					$cookie['httponly'] = true;
					break;
				case 'samesite':
					$cookie['samesite'] = $attr_value;
					break;
			}
		}

		return $cookie;
	}
}
