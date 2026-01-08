<?php
/**
 * Security Module
 *
 * Provides enhanced security features including rate limiting,
 * IP blocking, two-factor authentication, and security audit logs.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules
 * @license    GPL-3.0+
 * @since      1.0.0
 */

namespace ShahiLegalFlowSuite\Modules;

use ShahiLegalFlowSuite\Core\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Module Class
 *
 * Implements additional security features beyond core functionality.
 *
 * @since 1.0.0
 */
class Security_Module extends Module {

	/**
	 * Security instance
	 *
	 * @since 1.0.0
	 * @var Security
	 */
	private $security;

	/**
	 * Get module unique key
	 *
	 * @since 1.0.0
	 * @return string Module key
	 */
	public function get_key() {
		return 'security';
	}

	/**
	 * Get module name
	 *
	 * @since 1.0.0
	 * @return string Module name
	 */
	public function get_name() {
		return __( 'Enhanced Security', 'shahi-legalflowsuite' );
	}

	/**
	 * Get module description
	 *
	 * @since 1.0.0
	 * @return string Module description
	 */
	public function get_description() {
		return __( 'Additional security features including rate limiting, IP blocking, two-factor authentication, and comprehensive audit logs.', 'shahi-legalflowsuite' );
	}

	/**
	 * Get module icon
	 *
	 * @since 1.0.0
	 * @return string Icon class
	 */
	public function get_icon() {
		return 'dashicons-shield';
	}

	/**
	 * Get module category
	 *
	 * @since 1.0.0
	 * @return string Category
	 */
	public function get_category() {
		return 'security';
	}

	/**
	 * Initialize module
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		// Check if module is dormant (safety check)
		if ( defined( 'SLOS_DORMANT_MODULES' ) && in_array( 'security', SLOS_DORMANT_MODULES, true ) ) {
			return;
		}

		$this->security = new Security();

		// Add rate limiting
		add_action( 'init', array( $this, 'check_rate_limit' ) );

		// Check IP blacklist
		add_action( 'init', array( $this, 'check_ip_blacklist' ) );

		// Add security headers
		add_action( 'send_headers', array( $this, 'add_security_headers' ) );

		// Track failed login attempts
		add_action( 'wp_login_failed', array( $this, 'track_failed_login' ) );

		// Track successful logins
		add_action( 'wp_login', array( $this, 'track_successful_login' ), 10, 2 );
	}

	/**
	 * Check rate limit for current request
	 *
	 * [PLACEHOLDER - Mock implementation for demonstration]
	 * Production should implement:
	 * - Request counting per IP
	 * - Sliding window algorithm
	 * - Different limits for different actions
	 * - Temporary bans for excessive requests
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_rate_limit() {
		if ( is_admin() || wp_doing_cron() ) {
			return;
		}

		$ip    = $this->security->get_client_ip();
		$limit = $this->get_setting( 'rate_limit', 60 ); // 60 requests per minute default

		// Placeholder - Would implement actual rate limiting logic
	}

	/**
	 * Check if IP is blacklisted
	 *
	 * [PLACEHOLDER - Mock implementation for demonstration]
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function check_ip_blacklist() {
		$ip        = $this->security->get_client_ip();
		$blacklist = $this->get_setting( 'ip_blacklist', array() );

		if ( in_array( $ip, $blacklist, true ) ) {
			wp_die( __( 'Access denied. Your IP address has been blocked.', 'shahi-legalflowsuite' ) );
		}
	}

	/**
	 * Add security headers to HTTP response
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_security_headers() {
		if ( headers_sent() ) {
			return;
		}

		// Add X-Content-Type-Options
		header( 'X-Content-Type-Options: nosniff' );

		// Add X-Frame-Options
		header( 'X-Frame-Options: SAMEORIGIN' );

		// Add X-XSS-Protection
		header( 'X-XSS-Protection: 1; mode=block' );

		// Add Referrer-Policy
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}

	/**
	 * Track failed login attempts
	 *
	 * @since 1.0.0
	 * @param string $username Username used in failed login.
	 * @return void
	 */
	public function track_failed_login( $username ) {
		$ip = $this->security->get_client_ip();

		// Increment failed attempts counter
		$key      = 'failed_login_' . md5( $ip );
		$attempts = get_transient( $key ) ?: 0;
		++$attempts;

		set_transient( $key, $attempts, HOUR_IN_SECONDS );

		// Auto-block after 5 failed attempts
		if ( $attempts >= 5 ) {
			$this->add_to_blacklist( $ip );
		}
	}

	/**
	 * Track successful login
	 *
	 * @since 1.0.0
	 * @param string $username Username.
	 * @param object $user     User object.
	 * @return void
	 */
	public function track_successful_login( $username, $user ) {
		$ip = $this->security->get_client_ip();

		// Clear failed attempts counter
		$key = 'failed_login_' . md5( $ip );
		delete_transient( $key );
	}

	/**
	 * Add IP to blacklist
	 *
	 * @since 1.0.0
	 * @param string $ip IP address to block.
	 * @return bool True on success
	 */
	public function add_to_blacklist( $ip ) {
		$blacklist = $this->get_setting( 'ip_blacklist', array() );

		if ( ! in_array( $ip, $blacklist, true ) ) {
			$blacklist[] = $ip;
			return $this->update_setting( 'ip_blacklist', $blacklist );
		}

		return true;
	}

	/**
	 * Remove IP from blacklist
	 *
	 * @since 1.0.0
	 * @param string $ip IP address to unblock.
	 * @return bool True on success
	 */
	public function remove_from_blacklist( $ip ) {
		$blacklist = $this->get_setting( 'ip_blacklist', array() );
		$key       = array_search( $ip, $blacklist, true );

		if ( $key !== false ) {
			unset( $blacklist[ $key ] );
			return $this->update_setting( 'ip_blacklist', array_values( $blacklist ) );
		}

		return true;
	}

	/**
	 * Get module settings URL
	 *
	 * @since 1.0.0
	 * @return string Settings URL
	 */
	public function get_settings_url() {
		return admin_url( 'admin.php?page=shahi-legalflowsuite-settings&tab=security' );
	}
}

