<?php
/**
 * Cron Management Class
 *
 * Handles scheduled tasks and cron events for the plugin.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Core
 * @version     3.0.1
 * @since       3.0.1
 */

namespace ShahiLegalFlowSuite\Core;

use ShahiLegalFlowSuite\Services\Consent_Export_Service;
use ShahiLegalFlowSuite\Services\Consent_Audit_Logger;

// Exit if accessed directly..
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cron Class
 *
 * Manages cron schedules and event execution.
 *
 * @since 3.0.1
 */
class Cron {

	/**
	 * Initialize cron hooks
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function __construct() {
		// Schedule events on plugin activation..
		add_action( 'slos_plugin_activated', array( $this, 'schedule_events' ) );

		// Clear events on plugin deactivation..
		add_action( 'slos_plugin_deactivated', array( $this, 'clear_events' ) );

		// Register cron event handlers..
		add_action( 'slos_export_consents_weekly', array( $this, 'run_weekly_export' ) );
		add_action( 'slos_export_consents_daily', array( $this, 'run_daily_export' ) );
		add_action( 'slos_purge_consent_logs', array( $this, 'run_log_purge' ) );

		// Add custom cron intervals..
		add_filter( 'cron_schedules', array( $this, 'add_cron_intervals' ) );

		// Initialize schedules on init if not already scheduled..
		add_action( 'init', array( $this, 'init_schedules' ) );
	}

	/**
	 * Add custom cron intervals.
	 *
	 * @since 3.0.1
	 * @param array $schedules Existing schedules.
	 * @return array Modified schedules.
	 */
	public function add_cron_intervals( array $schedules ): array {
		// Add weekly schedule if not exists..
		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = array(
				'interval' => WEEK_IN_SECONDS,
				'display'  => __( 'Once Weekly', 'shahi-legalflowsuite' ),
			);
		}

		return $schedules;
	}

	/**
	 * Schedule all cron events
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function schedule_events() {
		$this->schedule_export_events();
		$this->schedule_log_purge();
	}

	/**
	 * Schedule export events
	 *
	 * @since 3.0.1
	 * @return void
	 */
	private function schedule_export_events() {
		$enabled = get_option( 'slos_scheduled_exports_enabled', false );

		if ( ! $enabled ) {
			return;
		}

		$frequency = get_option( 'slos_export_frequency', 'weekly' );

		// Clear existing schedules..
		wp_clear_scheduled_hook( 'slos_export_consents_weekly' );
		wp_clear_scheduled_hook( 'slos_export_consents_daily' );

		// Schedule based on frequency..
		switch ( $frequency ) {
			case 'daily':
				if ( ! wp_next_scheduled( 'slos_export_consents_daily' ) ) {
					wp_schedule_event( time(), 'daily', 'slos_export_consents_daily' );
				}
				break;

			case 'weekly':
			default:
				if ( ! wp_next_scheduled( 'slos_export_consents_weekly' ) ) {
					wp_schedule_event( time(), 'weekly', 'slos_export_consents_weekly' );
				}
				break;
		}
	}

	/**
	 * Initialize schedules on init
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function init_schedules() {
		$this->schedule_export_events();
		$this->schedule_log_purge();
	}

	/**
	 * Schedule log purge event
	 *
	 * @since 3.0.1
	 * @return void
	 */
	private function schedule_log_purge() {
		$enabled = get_option( 'slos_log_purge_enabled', true );

		if ( ! $enabled ) {
			return;
		}

		// Schedule daily log purge if not already scheduled..
		if ( ! wp_next_scheduled( 'slos_purge_consent_logs' ) ) {
			wp_schedule_event( time(), 'daily', 'slos_purge_consent_logs' );
		}
	}

	/**
	 * Run consent log purge
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function run_log_purge() {
		$enabled = get_option( 'slos_log_purge_enabled', true );

		if ( ! $enabled ) {
			return;
		}

		$logger        = new Consent_Audit_Logger();
		$retention     = (int) get_option( 'slos_log_retention_days', 365 );
		$deleted_count = $logger->purge_old_logs( $retention );

		if ( false !== $deleted_count ) {
			// Log success..
			do_action( 'slos_cron_purge_success', $deleted_count, $retention );

			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					sprintf(
						'SLOS: Purged %d consent log(s) older than %d days',
						$deleted_count,
						$retention
					)
				);
			}
		} else {
			// Log failure..
			do_action( 'slos_cron_purge_failed', $retention );

			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'SLOS: Consent log purge failed' );
			}
		}
	}

	/**
	 * Clear all scheduled events
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function clear_events() {
		wp_clear_scheduled_hook( 'slos_export_consents_weekly' );
		wp_clear_scheduled_hook( 'slos_export_consents_daily' );
		wp_clear_scheduled_hook( 'slos_purge_consent_logs' );
	}

	/**
	 * Run weekly consent export
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function run_weekly_export() {
		$this->run_export( 'weekly' );
	}

	/**
	 * Run daily consent export
	 *
	 * @since 3.0.1
	 * @return void
	 */
	public function run_daily_export() {
		$this->run_export( 'daily' );
	}

	/**
	 * Execute consent export.
	 *
	 * @since 3.0.1
	 * @param string $frequency Export frequency (weekly|daily).
	 * @return void
	 */
	private function run_export( string $frequency ) {
		$enabled = get_option( 'slos_scheduled_exports_enabled', false );

		if ( ! $enabled ) {
			return;
		}

		$service = new Consent_Export_Service();
		$format  = get_option( 'slos_export_format', 'csv' );

		$args = array(
			'format' => $format,
			'limit'  => get_option( 'slos_export_limit', 10000 ),
		);

		// Apply filters for date range..
		$date_range = get_option( 'slos_export_date_range', 'all' );
		if ( 'last_7_days' === $date_range ) {
			$args['date_from'] = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		} elseif ( 'last_30_days' === $date_range ) {
			$args['date_from'] = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		}

		// Run export..
		$result = $service->run_scheduled_export( $args );

		if ( $result ) {
			// Log success..
			do_action( 'slos_cron_export_success', $frequency, $args );

			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					sprintf(
						'SLOS: Scheduled %s export completed successfully',
						$frequency
					)
				);
			}
		} else {
			// Log failure..
			do_action( 'slos_cron_export_failed', $frequency, $args );

			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					sprintf(
						'SLOS: Scheduled %s export failed',
						$frequency
					)
				);
			}
		}
	}

	/**
	 * Get scheduled event info.
	 *
	 * @since 3.0.1
	 * @param string $hook Hook name.
	 * @return array|false Event info or false if not scheduled.
	 */
	public static function get_scheduled_event( string $hook ) {
		$timestamp = wp_next_scheduled( $hook );

		if ( ! $timestamp ) {
			return false;
		}

		return array(
			'hook'      => $hook,
			'timestamp' => $timestamp,
			'next_run'  => gmdate( 'Y-m-d H:i:s', $timestamp ),
			'time_diff' => human_time_diff( time(), $timestamp ),
		);
	}

	/**
	 * Get all scheduled exports
	 *
	 * @since 3.0.1
	 * @return array Scheduled export events
	 */
	public static function get_scheduled_exports(): array {
		$events = array();

		$weekly = self::get_scheduled_event( 'slos_export_consents_weekly' );
		if ( $weekly ) {
			$events['weekly'] = $weekly;
		}

		$daily = self::get_scheduled_event( 'slos_export_consents_daily' );
		if ( $daily ) {
			$events['daily'] = $daily;
		}

		return $events;
	}
}
