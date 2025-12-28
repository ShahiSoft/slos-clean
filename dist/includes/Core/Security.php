<?php
/**
 * Security Layer
 *
 * Provides comprehensive security utilities for the plugin.
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
 * Security Class
 *
 * Centralized security utilities for nonce verification, capability checks,
 * input sanitization, output escaping, and general security functions.
 *
 * @since 1.0.0
 */
class Security {

	/**
	 * Nonce action prefix
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const NONCE_PREFIX = 'shahi_legalflowsuite_';

	/**
	 * Generate a nonce
	 *
	 * @since 1.0.0
	 * @param string $action Action name for nonce.
	 * @return string Generated nonce.
	 */
	public static function generate_nonce( $action ) {
		return wp_create_nonce( self::NONCE_PREFIX . $action );
	}

	/**
	 * Verify a nonce
	 *
	 * @since 1.0.0
	 * @param string $nonce Nonce value to verify.
	 * @param string $action Action name for nonce.
	 * @return bool True if nonce is valid, false otherwise.
	 */
	public static function verify_nonce( $nonce, $action ) {
		return wp_verify_nonce( $nonce, self::NONCE_PREFIX . $action ) !== false;
	}

	/**
	 * Verify nonce or die
	 *
	 * @since 1.0.0
	 * @param string $nonce Nonce value to verify.
	 * @param string $action Action name for nonce.
	 * @return void Dies if nonce is invalid.
	 */
	public static function verify_nonce_or_die( $nonce, $action ) {
		if ( ! self::verify_nonce( $nonce, $action ) ) {
			wp_die(
				esc_html__( 'Security check failed. Please refresh the page and try again.', 'shahi-legalflowsuite' ),
				esc_html__( 'Security Error', 'shahi-legalflowsuite' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Get nonce field HTML
	 *
	 * @since 1.0.0
	 * @param string $action Action name for nonce.
	 * @param string $name Optional. Nonce field name. Default '_wpnonce'.
	 * @param bool   $referer Optional. Whether to add referer field. Default true.
	 * @param bool   $echo Optional. Whether to echo or return. Default true.
	 * @return string Nonce field HTML.
	 */
	public static function nonce_field( $action, $name = '_wpnonce', $referer = true, $echo = true ) {
		return wp_nonce_field( self::NONCE_PREFIX . $action, $name, $referer, $echo );
	}

	/**
	 * Get nonce URL
	 *
	 * @since 1.0.0
	 * @param string $url URL to add nonce to.
	 * @param string $action Action name for nonce.
	 * @param string $name Optional. Nonce parameter name. Default '_wpnonce'.
	 * @return string URL with nonce parameter.
	 */
	public static function nonce_url( $url, $action, $name = '_wpnonce' ) {
		return wp_nonce_url( $url, self::NONCE_PREFIX . $action, $name );
	}

	/**
	 * Check if current user has capability
	 *
	 * @since 1.0.0
	 * @param string $capability Capability to check.
	 * @return bool True if user has capability, false otherwise.
	 */
	public static function check_capability( $capability ) {
		return current_user_can( $capability );
	}

	/**
	 * Check capability or die
	 *
	 * @since 1.0.0
	 * @param string $capability Capability to check.
	 * @return void Dies if user lacks capability.
	 */
	public static function check_capability_or_die( $capability ) {
		if ( ! self::check_capability( $capability ) ) {
			wp_die(
				esc_html__( 'You do not have permission to perform this action.', 'shahi-legalflowsuite' ),
				esc_html__( 'Permission Denied', 'shahi-legalflowsuite' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Check if user can manage plugin
	 *
	 * @since 1.0.0
	 * @return bool True if user can manage plugin.
	 */
	public static function can_manage_plugin() {
		return self::check_capability( 'manage_options' );
	}

	/**
	 * Sanitize input data based on type
	 *
	 * @since 1.0.0
	 * @param mixed  $data Data to sanitize.
	 * @param string $type Type of sanitization.
	 * @return mixed Sanitized data.
	 */
	public static function sanitize_input( $data, $type = 'text' ) {
		switch ( $type ) {
			case 'text':
			case 'string':
				return sanitize_text_field( $data );

			case 'textarea':
				return sanitize_textarea_field( $data );

			case 'email':
				return sanitize_email( $data );

			case 'url':
				return esc_url_raw( $data );

			case 'int':
			case 'integer':
				return absint( $data );

			case 'float':
			case 'number':
				return floatval( $data );

			case 'bool':
			case 'boolean':
				return (bool) $data;

			case 'key':
			case 'slug':
				return sanitize_key( $data );

			case 'title':
				return sanitize_title( $data );

			case 'filename':
				return sanitize_file_name( $data );

			case 'html':
				return wp_kses_post( $data );

			case 'json':
				return json_decode( $data, true );

			case 'array':
				return is_array( $data ) ? array_map( 'sanitize_text_field', $data ) : array();

			default:
				return sanitize_text_field( $data );
		}
	}

	/**
	 * Sanitize array recursively
	 *
	 * @since 1.0.0
	 * @param array  $data Array to sanitize.
	 * @param string $type Type of sanitization for values.
	 * @return array Sanitized array.
	 */
	public static function sanitize_array( $data, $type = 'text' ) {
		if ( ! is_array( $data ) ) {
			return array();
		}

		$sanitized = array();
		foreach ( $data as $key => $value ) {
			$clean_key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $clean_key ] = self::sanitize_array( $value, $type );
			} else {
				$sanitized[ $clean_key ] = self::sanitize_input( $value, $type );
			}
		}

		return $sanitized;
	}

	/**
	 * Escape output data based on context
	 *
	 * @since 1.0.0
	 * @param mixed  $data Data to escape.
	 * @param string $context Escape context.
	 * @return string Escaped data.
	 */
	public static function escape_output( $data, $context = 'html' ) {
		switch ( $context ) {
			case 'html':
				return esc_html( $data );

			case 'attr':
			case 'attribute':
				return esc_attr( $data );

			case 'url':
				return esc_url( $data );

			case 'js':
			case 'javascript':
				return esc_js( $data );

			case 'textarea':
				return esc_textarea( $data );

			case 'json':
				return wp_json_encode( $data );

			default:
				return esc_html( $data );
		}
	}

	/**
	 * Validate AJAX request
	 *
	 * Verifies nonce, checks capabilities, and validates referer.
	 *
	 * @since 1.0.0
	 * @param string $action Action name for nonce.
	 * @param string $capability Optional. Required capability. Default 'manage_options'.
	 * @param string $nonce_key Optional. Nonce parameter name. Default 'nonce'.
	 * @return bool True if valid, false otherwise.
	 */
	public static function validate_ajax_request( $action, $capability = 'manage_options', $nonce_key = 'nonce' ) {
		// Check if it's an AJAX request
		if ( ! wp_doing_ajax() ) {
			return false;
		}

		// Verify nonce
		$nonce = isset( $_REQUEST[ $nonce_key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_key ] ) ) : '';
		if ( ! self::verify_nonce( $nonce, $action ) ) {
			return false;
		}

		// Check capability
		if ( ! self::check_capability( $capability ) ) {
			return false;
		}

		// Check referer
		if ( ! check_ajax_referer( self::NONCE_PREFIX . $action, $nonce_key, false ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validate AJAX request or send error
	 *
	 * @since 1.0.0
	 * @param string $action Action name for nonce.
	 * @param string $capability Optional. Required capability. Default 'manage_options'.
	 * @param string $nonce_key Optional. Nonce parameter name. Default 'nonce'.
	 * @return void Sends JSON error and dies if invalid.
	 */
	public static function validate_ajax_or_die( $action, $capability = 'manage_options', $nonce_key = 'nonce' ) {
		if ( ! self::validate_ajax_request( $action, $capability, $nonce_key ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security validation failed.', 'shahi-legalflowsuite' ),
				),
				403
			);
		}
	}

	/**
	 * Check if URL is safe
	 *
	 * @since 1.0.0
	 * @param string $url URL to check.
	 * @param array  $allowed_hosts Optional. Allowed hosts.
	 * @return bool True if URL is safe, false otherwise.
	 */
	public static function is_safe_url( $url ) {
		if ( empty( $url ) ) {
			return false;
		}

		// Check if it's a relative URL
		if ( strpos( $url, '://' ) === false ) {
			return true;
		}

		// Validate URL format
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		// Check allowed protocols
		$allowed_protocols = array( 'http', 'https' );
		$protocol          = parse_url( $url, PHP_URL_SCHEME );

		if ( ! in_array( $protocol, $allowed_protocols, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitize URL and validate
	 *
	 * @since 1.0.0
	 * @param string $url URL to sanitize.
	 * @return string|false Sanitized URL or false if invalid.
	 */
	public static function sanitize_url( $url ) {
		$url = esc_url_raw( $url );

		if ( ! self::is_safe_url( $url ) ) {
			return false;
		}

		return $url;
	}

	/**
	 * Prevent CSRF attacks
	 *
	 * @since 1.0.0
	 * @param string $action Action to verify.
	 * @return void Dies if CSRF detected.
	 */
	public static function prevent_csrf( $action ) {
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		self::verify_nonce_or_die( $nonce, $action );
	}

	/**
	 * Get user IP address
	 *
	 * @since 1.0.0
	 * @return string User IP address.
	 */
	public static function get_user_ip() {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// Validate IP address
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		}

		return '';
	}

	/**
	 * Get client IP address (alias for get_user_ip)
	 *
	 * @since 1.0.0
	 * @return string Client IP address.
	 */
	public static function get_client_ip() {
		return self::get_user_ip();
	}

	/**
	 * Anonymize IP address for privacy
	 *
	 * @since 1.0.0
	 * @param string $ip IP address to anonymize.
	 * @return string Anonymized IP address.
	 */
	public static function anonymize_ip( $ip ) {
		if ( empty( $ip ) ) {
			return '';
		}

		// IPv4
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$parts    = explode( '.', $ip );
			$parts[3] = '0';
			return implode( '.', $parts );
		}

		// IPv6
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			$parts                        = explode( ':', $ip );
			$parts[ count( $parts ) - 1 ] = '0';
			return implode( ':', $parts );
		}

		return $ip;
	}

	/**
	 * Validate uploaded file
	 *
	 * @since 1.0.0
	 * @param array $file $_FILES array element.
	 * @param array $allowed_types Optional. Allowed MIME types.
	 * @param int   $max_size Optional. Max file size in bytes. Default 2MB.
	 * @return bool|string True if valid, error message if invalid.
	 */
	public static function validate_file_upload( $file, $allowed_types = array(), $max_size = 2097152 ) {
		// Check if file was uploaded
		if ( ! isset( $file['error'] ) || is_array( $file['error'] ) ) {
			return __( 'Invalid file upload.', 'shahi-legalflowsuite' );
		}

		// Check for upload errors
		switch ( $file['error'] ) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return __( 'File size exceeds limit.', 'shahi-legalflowsuite' );
			case UPLOAD_ERR_NO_FILE:
				return __( 'No file was uploaded.', 'shahi-legalflowsuite' );
			default:
				return __( 'Unknown upload error.', 'shahi-legalflowsuite' );
		}

		// Check file size
		if ( $file['size'] > $max_size ) {
			return sprintf(
				/* translators: %s: maximum file size */
				__( 'File size exceeds maximum of %s.', 'shahi-legalflowsuite' ),
				size_format( $max_size )
			);
		}

		// Check MIME type
		if ( ! empty( $allowed_types ) ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE );
			$mime  = finfo_file( $finfo, $file['tmp_name'] );
			finfo_close( $finfo );

			if ( ! in_array( $mime, $allowed_types, true ) ) {
				return __( 'Invalid file type.', 'shahi-legalflowsuite' );
			}
		}

		return true;
	}

	/**
	 * Generate secure random token
	 *
	 * @since 1.0.0
	 * @param int $length Optional. Token length. Default 32.
	 * @return string Random token.
	 */
	public static function generate_token( $length = 32 ) {
		return bin2hex( random_bytes( $length / 2 ) );
	}

	/**
	 * Hash data securely
	 *
	 * @since 1.0.0
	 * @param string $data Data to hash.
	 * @return string Hashed data.
	 */
	public static function hash_data( $data ) {
		return wp_hash( $data );
	}

	/**
	 * Verify hashed data
	 *
	 * @since 1.0.0
	 * @param string $data Original data.
	 * @param string $hash Hash to verify against.
	 * @return bool True if hash matches, false otherwise.
	 */
	public static function verify_hash( $data, $hash ) {
		return hash_equals( $hash, self::hash_data( $data ) );
	}

	/**
	 * Sanitize SQL LIKE query
	 *
	 * @since 1.0.0
	 * @param string $query Query string.
	 * @return string Sanitized query.
	 */
	public static function sanitize_like_query( $query ) {
		global $wpdb;
		return '%' . $wpdb->esc_like( $query ) . '%';
	}

	/**
	 * Check if request is from admin area
	 *
	 * @since 1.0.0
	 * @return bool True if admin request, false otherwise.
	 */
	public static function is_admin_request() {
		return is_admin() && ! wp_doing_ajax();
	}

	/**
	 * Check if current page is plugin page
	 *
	 * @since 1.0.0
	 * @param string $page Optional. Specific page slug to check.
	 * @return bool True if plugin page, false otherwise.
	 */
	public static function is_plugin_page( $page = null ) {
		if ( ! is_admin() ) {
			return false;
		}

		$current_screen = get_current_screen();
		if ( ! $current_screen ) {
			return false;
		}

		$plugin_pages = array(
			'toplevel_page_shahi-legalflowsuite',
			'shahi-legalflowsuite_page_shahi-analytics',
			'shahi-legalflowsuite_page_shahi-modules',
			'shahi-legalflowsuite_page_shahi-settings',
		);

		if ( $page ) {
			return strpos( $current_screen->id, $page ) !== false;
		}

		return in_array( $current_screen->id, $plugin_pages, true );
	}

	/**
	 * Rate limit check
	 *
	 * @since 1.0.0
	 * @param string $action Action identifier.
	 * @param int    $limit Number of allowed attempts.
	 * @param int    $window Time window in seconds.
	 * @return bool True if within rate limit, false if exceeded.
	 */
	public static function check_rate_limit( $action, $limit = 10, $window = 60 ) {
		$transient_key = 'shahi_ratelimit_' . md5( $action . self::get_user_ip() );
		$attempts      = get_transient( $transient_key );

		if ( $attempts === false ) {
			set_transient( $transient_key, 1, $window );
			return true;
		}

		if ( $attempts >= $limit ) {
			return false;
		}

		set_transient( $transient_key, $attempts + 1, $window );
		return true;
	}

	/**
	 * Log security event
	 *
	 * @since 1.0.0
	 * @param string $event Event type.
	 * @param array  $data Event data.
	 * @return void
	 */
	public static function log_security_event( $event, $data = array() ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'event'     => $event,
			'user_id'   => get_current_user_id(),
			'ip'        => self::get_user_ip(),
			'data'      => $data,
		);

		error_log( '[ShahiLegalFlowSuite Security] ' . wp_json_encode( $log_entry ) );
	}
}
