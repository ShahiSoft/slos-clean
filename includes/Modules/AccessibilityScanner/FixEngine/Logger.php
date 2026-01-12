<?php
/**
 * FixEngine Logger
 *
 * Structured logging for FixEngine operations with context.
 *
 * @package SLOSLegalFlowSuite
 * @subpackage FixEngine
 * @since 3.1.2
 */

namespace ShahiLegalFlowSuite\FixEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger Class
 *
 * Provides structured logging with context for debugging and monitoring.
 */
class Logger {

	/**
	 * Log levels
	 */
	const LEVEL_DEBUG    = 'debug';
	const LEVEL_INFO     = 'info';
	const LEVEL_WARNING  = 'warning';
	const LEVEL_ERROR    = 'error';
	const LEVEL_CRITICAL = 'critical';

	/**
	 * Log prefix
	 */
	const PREFIX = '[SLOS FixEngine]';

	/**
	 * Log a debug message
	 *
	 * @param string $message The message to log.
	 * @param array  $context Additional context data.
	 */
	public static function debug( string $message, array $context = array() ): void {
		self::log( self::LEVEL_DEBUG, $message, $context );
	}

	/**
	 * Log an info message
	 *
	 * @param string $message The message to log.
	 * @param array  $context Additional context data.
	 */
	public static function info( string $message, array $context = array() ): void {
		self::log( self::LEVEL_INFO, $message, $context );
	}

	/**
	 * Log a warning message
	 *
	 * @param string $message The message to log.
	 * @param array  $context Additional context data.
	 */
	public static function warning( string $message, array $context = array() ): void {
		self::log( self::LEVEL_WARNING, $message, $context );
	}

	/**
	 * Log an error message
	 *
	 * @param string $message The message to log.
	 * @param array  $context Additional context data.
	 */
	public static function error( string $message, array $context = array() ): void {
		self::log( self::LEVEL_ERROR, $message, $context );
	}

	/**
	 * Log a critical message
	 *
	 * @param string $message The message to log.
	 * @param array  $context Additional context data.
	 */
	public static function critical( string $message, array $context = array() ): void {
		self::log( self::LEVEL_CRITICAL, $message, $context );
	}

	/**
	 * Main logging method
	 *
	 * @param string $level   The log level.
	 * @param string $message The message to log.
	 * @param array  $context Additional context data.
	 */
	private static function log( string $level, string $message, array $context = array() ): void {
		// Add standard context..
		$context = array_merge(
			array(
				'timestamp'  => current_time( 'mysql' ),
				'user_id'    => get_current_user_id(),
				'request_id' => self::get_request_id(),
			),
			$context
		);

		// Build log message..
		$log_message = sprintf(
			'%s [%s] %s %s',
			self::PREFIX,
			strtoupper( $level ),
			$message,
			self::format_context( $context )
		);

		// Output to error_log (only if debug logging enabled)..
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( $log_message );
		}

		// Store in option for dashboard (last 100 entries)..
		self::store_log_entry( $level, $message, $context );
	}

	/**
	 * Format context for logging
	 *
	 * @param array $context The context data to format.
	 * @return string
	 */
	private static function format_context( array $context ): string {
		if ( empty( $context ) ) {
			return '';
		}

		$formatted = array();
		foreach ( $context as $key => $value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				$value = wp_json_encode( $value );
			}
			$formatted[] = "$key=$value";
		}

		return '[' . implode( ', ', $formatted ) . ']';
	}

	/**
	 * Get or generate request ID
	 *
	 * @return string
	 */
	private static function get_request_id(): string {
		static $request_id = null;

		if ( null === $request_id ) {
			$request_id = substr( md5( uniqid( '', true ) ), 0, 12 );
		}

		return $request_id;
	}

	/**
	 * Store log entry for dashboard display
	 *
	 * @param string $level   The log level.
	 * @param string $message The log message.
	 * @param array  $context Additional context data.
	 */
	private static function store_log_entry( string $level, string $message, array $context ): void {
		// Only store warnings and above..
		if ( ! in_array( $level, array( self::LEVEL_WARNING, self::LEVEL_ERROR, self::LEVEL_CRITICAL ), true ) ) {
			return;
		}

		$logs = get_option( 'slos_fixengine_logs', array() );

		$logs[] = array(
			'level'     => $level,
			'message'   => $message,
			'context'   => $context,
			'timestamp' => current_time( 'mysql' ),
		);

		// Keep only last 100 entries..
		if ( count( $logs ) > 100 ) {
			$logs = array_slice( $logs, -100 );
		}

		update_option( 'slos_fixengine_logs', $logs, false );
	}

	/**
	 * Get stored logs
	 *
	 * @param int $limit Maximum number of logs to return.
	 * @return array
	 */
	public static function get_logs( int $limit = 50 ): array {
		$logs = get_option( 'slos_fixengine_logs', array() );

		if ( $limit > 0 && count( $logs ) > $limit ) {
			$logs = array_slice( $logs, -$limit );
		}

		return array_reverse( $logs );
	}

	/**
	 * Clear stored logs
	 *
	 * @return bool
	 */
	public static function clear_logs(): bool {
		return delete_option( 'slos_fixengine_logs' );
	}
}
