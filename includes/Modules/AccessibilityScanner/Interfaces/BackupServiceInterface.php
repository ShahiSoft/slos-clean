<?php
/**
 * Backup Service Interface
 *
 * Defines contract for content backup and restore operations in the Accessibility Scanner.
 * Part of Phase 4 architecture refactoring following SOLID principles.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Modules\AccessibilityScanner\Interfaces
 * @since      3.2.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\Interfaces;

/**
 * Interface for content backup operations
 *
 * Provides methods for saving, retrieving, and restoring content backups
 * created during accessibility fix operations.
 *
 * @since 3.2.0
 */
interface BackupServiceInterface {

	/**
	 * Save content backup before applying fixes
	 *
	 * Creates a backup of post content in the database before any fixes are applied.
	 * This allows rollback/restore operations if needed.
	 *
	 * @since 3.2.0
	 *
	 * @param int    $post_id  Post ID to backup
	 * @param string $content  Original post content
	 * @param array  $metadata Additional metadata (optional). Can include:
	 *                         - scan_before: Array of issues before fixing
	 *                         - scan_after: Array of issues after fixing
	 *                         - fixer_id: ID of fixer that was applied
	 *                         - fixed_count: Number of fixes applied
	 * @return int|false Backup ID on success, false on failure
	 */
	public function save_backup( $post_id, $content, $metadata = array() );

	/**
	 * Get specific backup by ID
	 *
	 * Retrieves a backup record by its unique ID.
	 *
	 * @since 3.2.0
	 *
	 * @param int $backup_id Backup ID
	 * @return array|null Backup data array or null if not found. Array contains:
	 *                    - id: Backup ID
	 *                    - post_id: Post ID
	 *                    - original_content: Backed up content
	 *                    - created_at: Backup timestamp
	 *                    - metadata: Additional metadata array
	 */
	public function get_backup( $backup_id );

	/**
	 * Get latest backup for a post
	 *
	 * Retrieves the most recent backup for a specific post.
	 *
	 * @since 3.2.0
	 *
	 * @param int $post_id Post ID
	 * @return array|null Backup data or null if not found
	 */
	public function get_latest_backup( $post_id );

	/**
	 * Get all backups for a post
	 *
	 * Retrieves all backups for a specific post, ordered by date descending (newest first).
	 *
	 * @since 3.2.0
	 *
	 * @param int $post_id Post ID
	 * @param int $limit   Maximum number of backups to return (default: 10)
	 * @return array Array of backup data, empty array if none found
	 */
	public function get_backups_by_post( $post_id, $limit = 10 );

	/**
	 * Restore content from backup
	 *
	 * Restores post content from a backup. If no backup ID is specified,
	 * uses the most recent backup. After restore, re-scans the post and
	 * updates dashboard statistics.
	 *
	 * @since 3.2.0
	 *
	 * @param int      $post_id    Post ID to restore
	 * @param int|null $backup_id  Specific backup ID (null = latest)
	 * @return array|\WP_Error Restored backup data on success, WP_Error on failure
	 */
	public function restore_backup( $post_id, $backup_id = null );

	/**
	 * Delete old backups
	 *
	 * Removes backups older than specified number of days.
	 * Typically called by cron job for maintenance.
	 *
	 * @since 3.2.0
	 *
	 * @param int $days_to_keep Keep backups from last N days (default: 30)
	 * @return int Number of backups deleted
	 */
	public function cleanup_old_backups( $days_to_keep = 30 );

	/**
	 * Check if backup exists for post
	 *
	 * Quick check to determine if any backups exist for a post.
	 * Useful for UI to show/hide restore button.
	 *
	 * @since 3.2.0
	 *
	 * @param int $post_id Post ID
	 * @return bool True if backup exists, false otherwise
	 */
	public function has_backup( $post_id );

	/**
	 * Get backup statistics
	 *
	 * Returns aggregate statistics about all backups in the system.
	 * Useful for dashboard and reporting.
	 *
	 * @since 3.2.0
	 *
	 * @return array Statistics array containing:
	 *               - total_backups: Total number of backups
	 *               - total_size_bytes: Total storage used (bytes)
	 *               - total_size_mb: Total storage used (MB)
	 *               - unique_posts: Number of unique posts with backups
	 *               - oldest_backup: Timestamp of oldest backup
	 *               - newest_backup: Timestamp of newest backup
	 */
	public function get_statistics();
}
