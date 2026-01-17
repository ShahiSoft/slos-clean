<?php
/**
 * Base Service Class
 *
 * Provides business logic layer between controllers and repositories.
 * Services handle validation, business rules, and coordinate between multiple repositories.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Services
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Base_Service Class
 *
 * Provides common service functionality and error handling.
 *
 * @since 3.0.1
 */
abstract class Base_Service {

	/**
	 * Base constructor
	 *
	 * Present so child services can safely call parent::__construct() without fatals.
	 */
	public function __construct() {
	}

	/**
	 * Error messages
	 *
	 * @since 3.0.1
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Validation errors
	 *
	 * @since 3.0.1
	 * @var array
	 */
	protected $validation_errors = array();

	/**
	 * Success messages
	 *
	 * @since 3.0.1
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Add error message.
	 *
	 * @since 3.0.1
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param mixed  $data    Optional error data.
	 * @return void
	 */
	protected function add_error( string $code, string $message, $data = null ): void {
		$this->errors[] = array(
			'code'    => $code,
			'message' => $message,
			'data'    => $data,
		);

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( sprintf( 'Service Error [%s]: %s', $code, $message ) );
		}
	}

	/**
	 * Add validation error.
	 *
	 * @since 3.0.1
	 * @param string $field   Field name.
	 * @param string $message Error message.
	 * @return void
	 */
	protected function add_validation_error( string $field, string $message ): void {
		$this->validation_errors[ $field ] = $message;
	}

	/**
	 * Add success message.
	 *
	 * @since 3.0.1
	 * @param string $message Success message.
	 * @return void
	 */
	protected function add_message( string $message ): void {
		$this->messages[] = $message;
	}

	/**
	 * Check if service has errors
	 *
	 * @since 3.0.1
	 * @return bool True if errors exist
	 */
	public function has_errors(): bool {
		return ! empty( $this->errors ) || ! empty( $this->validation_errors );
	}

	/**
	 * Get all errors
	 *
	 * @since 3.0.1
	 * @return array Array of error objects
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	/**
	 * Get validation errors
	 *
	 * @since 3.0.1
	 * @return array Array of validation errors
	 */
	public function get_validation_errors(): array {
		return $this->validation_errors;
	}

	/**
	 * Get success messages
	 *
	 * @since 3.0.1
	 * @return array Array of success messages
	 */
	public function get_messages(): array {
		return $this->messages;
	}

	/**
	 * Clear all errors and messages
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function clear_errors(): void {
		$this->errors            = array();
		$this->validation_errors = array();
		$this->messages          = array();
	}

	/**
	 * Sanitize string input.
	 *
	 * @since 3.0.1
	 * @param string $value Input value.
	 * @return string Sanitized value.
	 */
	protected function sanitize_string( string $value ): string {
		return sanitize_text_field( $value );
	}

	/**
	 * Sanitize email input.
	 *
	 * @since 3.0.1
	 * @param string $email Email address.
	 * @return string Sanitized email.
	 */
	protected function sanitize_email( string $email ): string {
		return sanitize_email( $email );
	}

	/**
	 * Sanitize textarea input.
	 *
	 * @since 3.0.1
	 * @param string $value Input value.
	 * @return string Sanitized value.
	 */
	protected function sanitize_textarea( string $value ): string {
		return sanitize_textarea_field( $value );
	}

	/**
	 * Sanitize URL input.
	 *
	 * @since 3.0.1
	 * @param string $url URL value.
	 * @return string Sanitized URL.
	 */
	protected function sanitize_url( string $url ): string {
		return esc_url_raw( $url );
	}

	/**
	 * Validate required field.
	 *
	 * @since 3.0.1
	 * @param mixed  $value      Field value.
	 * @param string $field_name Field name for error message.
	 * @return bool True if valid.
	 */
	protected function validate_required( $value, string $field_name ): bool {
		if ( empty( $value ) && '0' !== $value ) {
			$this->add_validation_error( $field_name, sprintf( '%s is required', ucfirst( $field_name ) ) );
			return false;
		}
		return true;
	}

	/**
	 * Validate email format
	 *
	 * @since 3.0.1
	 * @param string $email      Email address.
	 * @param string $field_name Field name for error message.
	 * @return bool True if valid.
	 */
	protected function validate_email( string $email, string $field_name = 'email' ): bool {
		if ( ! is_email( $email ) ) {
			$this->add_validation_error( $field_name, 'Invalid email address' );
			return false;
		}
		return true;
	}

	/**
	 * Validate string length.
	 *
	 * @since 3.0.1
	 * @param string $value      String value.
	 * @param int    $min        Minimum length.
	 * @param int    $max        Maximum length.
	 * @param string $field_name Field name for error message.
	 * @return bool True if valid.
	 */
	protected function validate_length( string $value, int $min, int $max, string $field_name ): bool {
		$length = strlen( $value );

		if ( $length < $min ) {
			$this->add_validation_error( $field_name, sprintf( '%s must be at least %d characters', ucfirst( $field_name ), $min ) );
			return false;
		}

		if ( $length > $max ) {
			$this->add_validation_error( $field_name, sprintf( '%s must not exceed %d characters', ucfirst( $field_name ), $max ) );
			return false;
		}

		return true;
	}

	/**
	 * Validate numeric value.
	 *
	 * @since 3.0.1
	 * @param mixed  $value      Numeric value.
	 * @param string $field_name Field name for error message.
	 * @return bool True if valid.
	 */
	protected function validate_numeric( $value, string $field_name ): bool {
		if ( ! is_numeric( $value ) ) {
			$this->add_validation_error( $field_name, sprintf( '%s must be a number', ucfirst( $field_name ) ) );
			return false;
		}
		return true;
	}

	/**
	 * Validate value is in allowed list.
	 *
	 * @since 3.0.1
	 * @param mixed  $value      Value to check.
	 * @param array  $allowed    Allowed values.
	 * @param string $field_name Field name for error message.
	 * @return bool True if valid.
	 */
	protected function validate_in_list( $value, array $allowed, string $field_name ): bool {
		if ( ! in_array( $value, $allowed, true ) ) {
			$this->add_validation_error( $field_name, sprintf( '%s must be one of: %s', ucfirst( $field_name ), implode( ', ', $allowed ) ) );
			return false;
		}
		return true;
	}

	/**
	 * Validate user capability.
	 *
	 * @since 3.0.1
	 * @param string $capability Required capability.
	 * @param int    $user_id    Optional user ID (defaults to current user).
	 * @return bool True if user has capability.
	 */
	protected function validate_capability( string $capability, int $user_id = 0 ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$user = get_user_by( 'id', $user_id );

		if ( ! $user || ! $user->has_cap( $capability ) ) {
			$this->add_error( 'permission_denied', 'You do not have permission to perform this action' );
			return false;
		}

		return true;
	}

	/**
	 * Validate user is logged in.
	 *
	 * @since 3.0.1
	 * @return bool True if user is logged in.
	 */
	protected function validate_logged_in(): bool {
		if ( ! is_user_logged_in() ) {
			$this->add_error( 'not_logged_in', 'You must be logged in to perform this action' );
			return false;
		}
		return true;
	}

	/**
	 * Hash IP address for privacy compliance.
	 *
	 * @since 3.0.1
	 * @param string $ip_address IP address.
	 * @return string Hashed IP address.
	 */
	protected function hash_ip( string $ip_address ): string {
		return hash( 'sha256', $ip_address );
	}

	/**
	 * Get current user IP address.
	 *
	 * @since 3.0.1
	 * @return string IP address.
	 */
	protected function get_user_ip(): string {
		$ip = '';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return sanitize_text_field( $ip );
	}

	/**
	 * Get current user agent.
	 *
	 * @since 3.0.1
	 * @return string User agent string.
	 */
	protected function get_user_agent(): string {
		return isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	}

	/**
	 * Prepare metadata array for storage.
	 *
	 * @since 3.0.1
	 * @param array $metadata Metadata array.
	 * @return string JSON encoded metadata.
	 */
	protected function prepare_metadata( array $metadata ): string {
		return wp_json_encode( $metadata );
	}

	/**
	 * Parse stored metadata.
	 *
	 * @since 3.0.1
	 * @param string $metadata_json JSON encoded metadata.
	 * @return array Parsed metadata array.
	 */
	protected function parse_metadata( string $metadata_json ): array {
		$metadata = json_decode( $metadata_json, true );
		return is_array( $metadata ) ? $metadata : array();
	}
}
