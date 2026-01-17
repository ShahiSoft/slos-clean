<?php
/**
 * Backup Service
 *
 * Handles content backup and restore operations for accessibility fixes.
 * Part of Phase 4 architecture refactoring following SOLID principles.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Services
 * @since      3.2.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Services;

use ShahiLegalFlowSuite\Modules\AccessibilityScanner\Interfaces\BackupServiceInterface;

/**
 * Service class for content backup operations
 *
 * Provides centralized backup/restore functionality for accessibility fixes.
 * Stores backups in database table with full content for reliable restore.
 *
 * @since 3.2.0
 */
class BackupService implements BackupServiceInterface {

	/**
	 * Database table name (without prefix)
	 *
	 * @var string
	 */
	private $table_name = 'slos_accessibility_fix_history';

	/**
	 * WordPress database instance
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
	/**
	 * Constructor
	 *
	 * @since 3.2.0
	 *
	 * @param \wpdb|null $wpdb_instance WordPress database instance (for dependency injection/testing).
	 */
	public function __construct( $wpdb_instance = null ) {
		global $wpdb;
		$this->wpdb = $wpdb_instance ?? $wpdb;
	}

	/**
	 * Get full table name with prefix.
	 *
	 * @since 3.2.0
	 *
	 * @return string Full table name with WordPress prefix
	 */
	private function get_table_name() {
		return $this->wpdb->prefix . $this->table_name;
	}

	/**
	 * Save content backup before applying fixes.
	 *
	 * @since 3.2.0
	 *
	 * @param int    $post_id  Post ID to backup.
	 * @param string $content  Original post content.
	 * @param array  $metadata Additional metadata (optional).
	 * @return int|false Backup ID on success, false on failure.
	 */
	public function save_backup( $post_id, $content, $metadata = array() ) {
		// Validate inputs.
		if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
			return false;
		}

		if ( ! is_string( $content ) ) {
			return false;
		}

		$table = $this->get_table_name();

		$data = array(
			'post_id'          => (int) $post_id,
			'original_content' => $content,
			'fixer_id'         => $metadata['fixer_id'] ?? 'backup',
			'fixed_count'      => $metadata['fixed_count'] ?? 0,
			'user_id'          => get_current_user_id(),
			'action'           => 'backup',
			'created_at'       => current_time( 'mysql' ),
			'metadata'         => ! empty( $metadata ) ? wp_json_encode( $metadata ) : null,
		);

		$format = array( '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s' );

		$result = $this->wpdb->insert( $table, $data, $format );

		if ( false === $result ) {
			if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log(
					sprintf(
						'BackupService: Failed to save backup for post %d. Error: %s',
						$post_id,
						$this->wpdb->last_error
					)
				);
			}
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Get specific backup by ID.
	 *
	 * @since 3.2.0
	 *
	 * @param int $backup_id Backup ID.
	 * @return array|null Backup data or null if not found.
	 */
	public function get_backup( $backup_id ) {
		if ( empty( $backup_id ) || ! is_numeric( $backup_id ) ) {
			return null;
		}

		$table = $this->get_table_name();

		$backup = $this->wpdb->get_row(
			$this->wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- esc_sql() used for table name
				'SELECT * FROM ' . esc_sql( $table ) . ' WHERE id = %d',
				$backup_id
			),
			ARRAY_A
		);

		if ( ! $backup ) {
			return null;
		}

		return $this->format_backup_data( $backup );
	}

	/**
	 * Get latest backup for a post.
	 *
	 * @since 3.2.0
	 *
	 * @param int $post_id Post ID.
	 * @return array|null Backup data or null if not found.
	 */
	public function get_latest_backup( $post_id ) {
		if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
			return null;
		}

		$table = $this->get_table_name();

		$backup = $this->wpdb->get_row(
			$this->wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- esc_sql() used for table name
				'SELECT * FROM ' . esc_sql( $table ) . ' WHERE post_id = %d ORDER BY created_at DESC LIMIT 1',
				$post_id
			),
			ARRAY_A
		);

		if ( ! $backup ) {
			return null;
		}

		return $this->format_backup_data( $backup );
	}

	/**
	 * Get all backups for a post.
	 *
	 * @since 3.2.0
	 *
	 * @param int $post_id Post ID.
	 * @param int $limit   Maximum number of backups to return.
	 * @return array Array of backup data.
	 */
	public function get_backups_by_post( $post_id, $limit = 10 ) {
		if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
			return array();
		}

		$limit = absint( $limit );
		if ( 0 === $limit ) {
			$limit = 10;
		}

		$table = $this->get_table_name();

		$backups = $this->wpdb->get_results(
			$this->wpdb->prepare(
				' SELECT * FROM ' . esc_sql( $table ) . ' WHERE post_id = %d ORDER BY created_at DESC LIMIT %d',
				$post_id,
				$limit
			),
			ARRAY_A
		);

		if ( ! $backups ) {
			return array();
		}

		return array_map( array( $this, 'format_backup_data' ), $backups );
	}

	/**
	 * Restore content from backup.
	 *
	 * @since 3.2.0
	 *
	 * @param int      $post_id   Post ID to restore.
	 * @param int|null $backup_id Specific backup ID (null = latest).
	 * @return array|\WP_Error Restored backup data or error
	 */
	public function restore_backup( $post_id, $backup_id = null ) {
		// Get backup.
		if ( $backup_id ) {
			$backup = $this->get_backup( $backup_id );
			if ( ! $backup || (int) $backup['post_id'] !== (int) $post_id ) {
				return new \WP_Error(
					'backup_not_found',
					__( 'Specified backup not found or does not match post ID.', 'shahi-legalflowsuite' )
				);
			}
		} else {
			$backup = $this->get_latest_backup( $post_id );
			if ( ! $backup ) {
				return new \WP_Error(
					'no_backup',
					__( 'No backup found for this post.', 'shahi-legalflowsuite' )
				);
			}
		}

		// Restore content..
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $backup['original_content'],
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				'restore_failed',
				sprintf(
					/* translators: %s: error message from wp_update_post. */
					__( 'Failed to restore content: %s', 'shahi-legalflowsuite' ),
					$result->get_error_message()
				)
			);
		}

		return $backup;
	}

	/**
	 * Delete old backups.
	 *
	 * @since 3.2.0
	 *
	 * @param int $days_to_keep Keep backups from last N days.
	 * @return int Number of backups deleted.
	 */
	public function cleanup_old_backups( $days_to_keep = 30 ) {
		$days_to_keep = absint( $days_to_keep );
		if ( 0 === $days_to_keep ) {
			$days_to_keep = 30;
		}

		$table          = $this->get_table_name();
		$date_threshold = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days_to_keep} days" ) );

		$deleted = $this->wpdb->query(
			$this->wpdb->prepare(
				'DELETE FROM ' . esc_sql( $table ) . ' WHERE created_at < %s',
				$date_threshold
			)
		);

		if ( $deleted > 0 && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				sprintf(
					'BackupService: Deleted %d backups older than %d days.',
					$deleted,
					$days_to_keep
				)
			);
		}

		return (int) $deleted;
	}

	/**
	 * Check if backup exists for post.
	 *
	 * @since 3.2.0
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if backup exists.
	 */
	public function has_backup( $post_id ) {
		if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
			return false;
		}

		$table = $this->get_table_name();

		$count = $this->wpdb->get_var(
			$this->wpdb->prepare(
				'SELECT COUNT(*) FROM ' . esc_sql( $table ) . ' WHERE post_id = %d',
				$post_id
			)
		);

		return (int) $count > 0;
	}

	/**
	 * Get backup statistics.
	 *
	 * @since 3.2.0
	 *
	 * @return array Statistics about backups.
	 */
	public function get_statistics() {
		$table = $this->get_table_name();

		$total        = $this->wpdb->get_var( 'SELECT COUNT(*) FROM ' . esc_sql( $table ) );
		$total_size   = $this->wpdb->get_var( 'SELECT SUM(LENGTH(original_content)) FROM ' . esc_sql( $table ) );
		$unique_posts = $this->wpdb->get_var( 'SELECT COUNT(DISTINCT post_id) FROM ' . esc_sql( $table ) );
		$oldest       = $this->wpdb->get_var( 'SELECT MIN(created_at) FROM ' . esc_sql( $table ) );
		$newest       = $this->wpdb->get_var( 'SELECT MAX(created_at) FROM ' . esc_sql( $table ) );

		return array(
			'total_backups'    => (int) $total,
			'total_size_bytes' => (int) $total_size,
			'total_size_mb'    => round( (int) $total_size / 1024 / 1024, 2 ),
			'unique_posts'     => (int) $unique_posts,
			'oldest_backup'    => $oldest,
			'newest_backup'    => $newest,
		);
	}

	/**
	 * Format backup data for consistent output.
	 *
	 * Decodes JSON metadata and ensures consistent array structure.
	 *
	 * @since 3.2.0
	 *
	 * @param array $backup Raw backup data from database.
	 * @return array Formatted backup data.
	 */
	private function format_backup_data( $backup ) {
		if ( ! empty( $backup['metadata'] ) ) {
			$metadata           = json_decode( $backup['metadata'], true );
			$backup['metadata'] = is_array( $metadata ) ? $metadata : array();
		} else {
			$backup['metadata'] = array();
		}

		return $backup;
	}
}

// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
