<?php
/**
 * DSR Erasure Service
 *
 * Implements secure data erasure and anonymization pipeline for approved DSR requests.
 * Provides pluggable handlers, dry-run mode, and comprehensive audit logging.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Services
 * @since      3.0.1
 */

namespace ShahiLegalFlowSuite\Services;

use ShahiLegalFlowSuite\Database\Repositories\DSR_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DSR Erasure Service Class
 *
 * Orchestrates data erasure across WordPress core, plugins, and custom data sources.
 *
 * @since 3.0.1
 */
class DSR_Erasure_Service {

	/**
	 * DSR Repository instance
	 *
	 * @var DSR_Repository
	 */
	private $repository;

	/**
	 * Dry-run mode flag
	 *
	 * @var bool
	 */
	private $dry_run = false;

	/**
	 * Audit log entries
	 *
	 * @var array
	 */
	private $audit_log = array();

	/**
	 * Initialize service
	 *
	 * @since 3.0.1
	 */
	public function __construct() {
		$this->repository = new DSR_Repository();

		// Register default erasure handlers
		add_filter( 'slos_dsr_erasure_handlers', array( $this, 'register_core_handlers' ), 10 );

		// Hook into erasure execution action
		add_action( 'slos_dsr_erasure_execute', array( $this, 'process_erasure' ), 10, 2 );
	}

	/**
	 * Register core WordPress erasure handlers
	 *
	 * @since 3.0.1
	 * @param array $handlers Existing handlers
	 * @return array Updated handlers
	 */
	public function register_core_handlers( array $handlers ): array {
		$handlers['wordpress_user'] = array(
			'label'       => __( 'WordPress User Account', 'shahi-legalflowsuite' ),
			'description' => __( 'Anonymize or delete user account and profile data', 'shahi-legalflowsuite' ),
			'callback'    => array( $this, 'erase_wordpress_user' ),
			'priority'    => 100, // High priority - handle last
		);

		$handlers['comments'] = array(
			'label'       => __( 'Comments', 'shahi-legalflowsuite' ),
			'description' => __( 'Anonymize comment author information', 'shahi-legalflowsuite' ),
			'callback'    => array( $this, 'erase_comments' ),
			'priority'    => 20,
		);

		$handlers['consent_records'] = array(
			'label'       => __( 'Consent Records', 'shahi-legalflowsuite' ),
			'description' => __( 'Anonymize consent logs while preserving audit trail', 'shahi-legalflowsuite' ),
			'callback'    => array( $this, 'erase_consent_records' ),
			'priority'    => 30,
		);

		$handlers['user_meta'] = array(
			'label'       => __( 'User Metadata', 'shahi-legalflowsuite' ),
			'description' => __( 'Remove non-essential user metadata', 'shahi-legalflowsuite' ),
			'callback'    => array( $this, 'erase_user_meta' ),
			'priority'    => 40,
		);

		return $handlers;
	}

	/**
	 * Process erasure request
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID
	 * @param object $request    Request object
	 * @return bool True on success
	 */
	public function process_erasure( int $request_id, $request ): bool {
		$this->audit_log = array();

		// Get all registered handlers
		$handlers = apply_filters( 'slos_dsr_erasure_handlers', array() );

		// Sort by priority
		uasort(
			$handlers,
			function ( $a, $b ) {
				return ( $a['priority'] ?? 999 ) - ( $b['priority'] ?? 999 );
			}
		);

		$this->log_audit(
			$request_id,
			'erasure_started',
			array(
				'request_type' => $request->request_type ?? '',
				'user_id'      => $request->user_id ?? null,
				'email'        => $request->requester_email ?? '',
				'dry_run'      => $this->dry_run,
			)
		);

		// Execute each handler
		$success_count = 0;
		$failure_count = 0;

		foreach ( $handlers as $key => $handler ) {
			if ( ! isset( $handler['callback'] ) || ! is_callable( $handler['callback'] ) ) {
				continue;
			}

			try {
				$result = call_user_func( $handler['callback'], $request, $this->dry_run );

				if ( $result ) {
					++$success_count;
					$this->log_audit(
						$request_id,
						'handler_success',
						array(
							'handler' => $key,
							'label'   => $handler['label'] ?? $key,
							'items'   => is_array( $result ) ? count( $result ) : 1,
						)
					);
				} else {
					++$failure_count;
					$this->log_audit(
						$request_id,
						'handler_skipped',
						array(
							'handler' => $key,
							'label'   => $handler['label'] ?? $key,
						)
					);
				}
			} catch ( \Throwable $e ) {
				++$failure_count;
				$this->log_audit(
					$request_id,
					'handler_failed',
					array(
						'handler' => $key,
						'label'   => $handler['label'] ?? $key,
						'error'   => $e->getMessage(),
					)
				);
				error_log( sprintf( 'DSR Erasure: Handler "%s" failed: %s', $key, $e->getMessage() ) );
			}
		}

		$this->log_audit(
			$request_id,
			'erasure_completed',
			array(
				'success_count'  => $success_count,
				'failure_count'  => $failure_count,
				'total_handlers' => count( $handlers ),
				'dry_run'        => $this->dry_run,
			)
		);

		// Update request status if not dry-run
		if ( ! $this->dry_run ) {
			$this->repository->update_status(
				$request_id,
				'completed',
				array(
					'admin_notes' => sprintf(
						'[%s] Erasure completed: %d handlers succeeded, %d failed',
						current_time( 'mysql' ),
						$success_count,
						$failure_count
					),
				)
			);

			// Fire completion hook
			do_action( 'slos_dsr_erasure_completed', $request_id, $request, $this->audit_log );
		}

		return $success_count > 0;
	}

	/**
	 * Get erasure preview (dry-run)
	 *
	 * @since 3.0.1
	 * @param int $request_id Request ID
	 * @return array Preview data
	 */
	public function get_erasure_preview( int $request_id ): array {
		$request = $this->repository->find( $request_id );

		if ( ! $request ) {
			return array(
				'error' => __( 'Request not found', 'shahi-legalflowsuite' ),
			);
		}

		// Enable dry-run mode
		$this->dry_run = true;

		// Process erasure in dry-run mode
		$this->process_erasure( $request_id, $request );

		// Disable dry-run mode
		$this->dry_run = false;

		return array(
			'request_id' => $request_id,
			'dry_run'    => true,
			'audit_log'  => $this->audit_log,
			'summary'    => $this->generate_preview_summary(),
		);
	}

	/**
	 * Generate preview summary from audit log
	 *
	 * @since 3.0.1
	 * @return array Summary data
	 */
	private function generate_preview_summary(): array {
		$summary = array(
			'total_handlers' => 0,
			'items_affected' => 0,
			'handlers'       => array(),
		);

		foreach ( $this->audit_log as $entry ) {
			if ( $entry['action'] === 'handler_success' ) {
				++$summary['total_handlers'];
				$summary['items_affected'] += $entry['data']['items'] ?? 0;
				$summary['handlers'][]      = $entry['data']['label'] ?? 'Unknown';
			}
		}

		return $summary;
	}

	/**
	 * Erase WordPress user data
	 *
	 * @since 3.0.1
	 * @param object $request Request object
	 * @param bool   $dry_run Dry-run mode
	 * @return array|false Affected items or false
	 */
	public function erase_wordpress_user( $request, bool $dry_run = false ) {
		if ( empty( $request->user_id ) ) {
			return false;
		}

		$user = get_userdata( $request->user_id );
		if ( ! $user ) {
			return false;
		}

		$items = array(
			'user_id'      => $user->ID,
			'user_login'   => $user->user_login,
			'user_email'   => $user->user_email,
			'display_name' => $user->display_name,
		);

		if ( $dry_run ) {
			return array( $items );
		}

		// Anonymize user data
		$anon_email = sprintf( 'deleted-%d@anonymized.local', $user->ID );
		$anon_login = sprintf( 'deleted_user_%d', $user->ID );

		wp_update_user(
			array(
				'ID'           => $user->ID,
				'user_email'   => $anon_email,
				'user_login'   => $anon_login,
				'display_name' => __( 'Deleted User', 'shahi-legalflowsuite' ),
				'first_name'   => '',
				'last_name'    => '',
				'description'  => '',
				'user_url'     => '',
			)
		);

		return array( $items );
	}

	/**
	 * Erase comments data
	 *
	 * @since 3.0.1
	 * @param object $request Request object
	 * @param bool   $dry_run Dry-run mode
	 * @return array|false Affected items or false
	 */
	public function erase_comments( $request, bool $dry_run = false ) {
		$comments = array();

		// Get comments by user ID or email
		if ( ! empty( $request->user_id ) ) {
			$comments = get_comments(
				array(
					'user_id' => $request->user_id,
					'status'  => 'all',
				)
			);
		} elseif ( ! empty( $request->requester_email ) ) {
			$comments = get_comments(
				array(
					'author_email' => $request->requester_email,
					'status'       => 'all',
				)
			);
		}

		if ( empty( $comments ) ) {
			return false;
		}

		$items = array();
		foreach ( $comments as $comment ) {
			$items[] = array(
				'comment_ID'           => $comment->comment_ID,
				'comment_author'       => $comment->comment_author,
				'comment_author_email' => $comment->comment_author_email,
			);

			if ( ! $dry_run ) {
				wp_update_comment(
					array(
						'comment_ID'           => $comment->comment_ID,
						'comment_author'       => __( 'Anonymous', 'shahi-legalflowsuite' ),
						'comment_author_email' => 'deleted@anonymized.local',
						'comment_author_url'   => '',
						'comment_author_IP'    => wp_privacy_anonymize_ip( $comment->comment_author_IP ),
					)
				);
			}
		}

		return $items;
	}

	/**
	 * Erase consent records
	 *
	 * @since 3.0.1
	 * @param object $request Request object
	 * @param bool   $dry_run Dry-run mode
	 * @return array|false Affected items or false
	 */
	public function erase_consent_records( $request, bool $dry_run = false ) {
		global $wpdb;

		$table = $wpdb->prefix . 'slos_consent_logs';

		// Check if table exists.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return false;
		}

		$user_id = $request->user_id ?? null;
		$email   = $request->requester_email ?? '';

		// Query consent logs
		$where  = array();
		$values = array();

		if ( $user_id ) {
			$where[]  = 'user_id = %d';
			$values[] = $user_id;
		}

		if ( $email ) {
			$where[]  = 'email = %s';
			$values[] = $email;
		}

		if ( empty( $where ) ) {
			return false;
		}

		$where_sql = implode( ' OR ', $where );
		$sql       = "SELECT * FROM $table WHERE $where_sql";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$records = $wpdb->get_results( $wpdb->prepare( $sql, $values ) );

		if ( empty( $records ) ) {
			return false;
		}

		$items = array();
		foreach ( $records as $record ) {
			$items[] = array(
				'id'     => $record->id ?? 0,
				'email'  => $record->email ?? '',
				'action' => $record->action ?? '',
			);

			if ( ! $dry_run ) {
				// Anonymize consent record (preserve audit but remove PII)
				$wpdb->update(
					$table,
					array(
						'user_id'    => null,
						'email'      => 'deleted@anonymized.local',
						'ip_address' => isset( $record->ip_address ) ? wp_privacy_anonymize_ip( $record->ip_address ) : '',
					),
					array( 'id' => $record->id ),
					array( '%d', '%s', '%s' ),
					array( '%d' )
				);
			}
		}

		return $items;
	}

	/**
	 * Erase user metadata
	 *
	 * @since 3.0.1
	 * @param object $request Request object
	 * @param bool   $dry_run Dry-run mode
	 * @return array|false Affected items or false
	 */
	public function erase_user_meta( $request, bool $dry_run = false ) {
		if ( empty( $request->user_id ) ) {
			return false;
		}

		$user = get_userdata( $request->user_id );
		if ( ! $user ) {
			return false;
		}

		// Get all user meta
		$all_meta = get_user_meta( $request->user_id );

		// Exclude essential WordPress meta
		$preserve = array(
			'wp_capabilities',
			'wp_user_level',
			'locale',
			'rich_editing',
			'syntax_highlighting',
			'comment_shortcuts',
			'admin_color',
			'use_ssl',
		);

		$items = array();
		foreach ( $all_meta as $key => $values ) {
			// Skip WordPress internal meta and preserved keys
			if ( str_starts_with( $key, 'wp_' ) || in_array( $key, $preserve, true ) ) {
				continue;
			}

			$items[] = array(
				'meta_key'   => $key,
				'meta_value' => maybe_serialize( $values[0] ),
			);

			if ( ! $dry_run ) {
				delete_user_meta( $request->user_id, $key );
			}
		}

		return ! empty( $items ) ? $items : false;
	}

	/**
	 * Log audit entry
	 *
	 * @since 3.0.1
	 * @param int    $request_id Request ID
	 * @param string $action     Action name
	 * @param array  $data       Additional data
	 * @return void
	 */
	private function log_audit( int $request_id, string $action, array $data = array() ): void {
		$entry = array(
			'request_id' => $request_id,
			'action'     => $action,
			'timestamp'  => current_time( 'mysql' ),
			'user_id'    => get_current_user_id(),
			'data'       => $data,
		);

		$this->audit_log[] = $entry;

		// Fire audit log hook for external storage
		do_action( 'slos_dsr_audit_log', $request_id, $action, $data );
	}

	/**
	 * Get audit log
	 *
	 * @since 3.0.1
	 * @return array Audit log entries
	 */
	public function get_audit_log(): array {
		return $this->audit_log;
	}

	/**
	 * Set dry-run mode
	 *
	 * @since 3.0.1
	 * @param bool $enabled Enable dry-run
	 * @return void
	 */
	public function set_dry_run( bool $enabled ): void {
		$this->dry_run = $enabled;
	}

	/**
	 * Check if in dry-run mode
	 *
	 * @since 3.0.1
	 * @return bool True if dry-run enabled
	 */
	public function is_dry_run(): bool {
		return $this->dry_run;
	}
}
