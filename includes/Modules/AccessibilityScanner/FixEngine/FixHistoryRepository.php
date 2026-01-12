<?php
/**
 * Fix History Repository - Database persistence for fix operations
 *
 * @package ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine
 * @since 3.3.0
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FixHistoryRepository
 *
 * Handles database operations for fix history.
 * Single Responsibility: Data persistence.
 * Dependency Inversion: Abstracts database operations.
 */
final class FixHistoryRepository {

	/** @var string */
	private $table_name;

	/** @var \wpdb */
	private $wpdb;

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb       = $wpdb;
		$this->table_name = $wpdb->prefix . 'slos_fix_history';
	}

	/**
	 * Create database table if not exists
	 */
	public function create_table(): void {
		$charset_collate = $this->wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			session_id VARCHAR(36) NOT NULL,
			post_id BIGINT(20) UNSIGNED NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			fixer_id VARCHAR(100) NOT NULL,
			fixes_applied INT(11) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'success',
			details LONGTEXT,
			original_content_hash VARCHAR(64),
			fixed_content_hash VARCHAR(64),
			execution_time DECIMAL(10,4) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY post_id (post_id),
			KEY user_id (user_id),
			KEY fixer_id (fixer_id),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Save a single fix result
	 *
	 * @param string    $session_id
	 * @param int       $post_id
	 * @param int       $user_id
	 * @param FixResult $result
	 * @return int|false Insert ID or false on failure
	 */
	public function save_result( string $session_id, int $post_id, int $user_id, FixResult $result ) {
		$status = 'success';
		if ( ! $result->is_success() ) {
			$status = 'error';
		} elseif ( $result->is_skipped() ) {
			$status = 'skipped';
		}

		$data = array(
			'session_id'            => $session_id,
			'post_id'               => $post_id,
			'user_id'               => $user_id,
			'fixer_id'              => $result->get_fixer_id(),
			'fixes_applied'         => $result->get_fixes_applied(),
			'status'                => $status,
			'details'               => wp_json_encode( $result->get_details() ),
			'original_content_hash' => hash( 'sha256', $result->get_original_content() ),
			'fixed_content_hash'    => hash( 'sha256', $result->get_fixed_content() ),
			'execution_time'        => $result->get_execution_time(),
			'created_at'            => current_time( 'mysql' ),
		);

		$format = array( '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%f', '%s' );

		$inserted = $this->wpdb->insert( $this->table_name, $data, $format );

		return $inserted ? $this->wpdb->insert_id : false;
	}

	/**
	 * Save a complete session
	 *
	 * @param FixSession $session
	 * @return bool
	 */
	public function save_session( FixSession $session ): bool {
		$session_id = $session->get_id();
		$post_id    = $session->get_post_id();
		$user_id    = get_current_user_id();

		foreach ( $session->get_results() as $result ) {
			$this->save_result( $session_id, $post_id, $user_id, $result );
		}

		// Also save session summary to post meta..
		update_post_meta( $post_id, '_slos_last_fix_session', $session->to_array() );
		update_post_meta( $post_id, '_slos_last_fix_date', current_time( 'mysql' ) );

		return true;
	}

	/**
	 * Get fix history for a post
	 *
	 * @param int $post_id
	 * @param int $limit
	 * @return array
	 */
	public function get_history_for_post( int $post_id, int $limit = 50 ): array {
		$sql = $this->wpdb->prepare(
			'SELECT * FROM %i WHERE post_id = %d ORDER BY created_at DESC LIMIT %d',
			$this->table_name,
			$post_id,
			$limit
		);

		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}

	/**
	 * Get fix history for a session
	 *
	 * @param string $session_id
	 * @return array
	 */
	public function get_history_for_session( string $session_id ): array {
		$sql = $this->wpdb->prepare(
			'SELECT * FROM %i WHERE session_id = %s ORDER BY id ASC',
			$this->table_name,
			$session_id
		);

		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}

	/**
	 * Get statistics
	 *
	 * @param int|null $post_id Optional post ID filter
	 * @return array
	 */
	public function get_statistics( ?int $post_id = null ): array {
		$sql = $post_id
			? $this->wpdb->prepare(
				"SELECT 
					COUNT(DISTINCT session_id) as total_sessions,
					COUNT(*) as total_operations,
					SUM(fixes_applied) as total_fixes,
					SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as total_errors,
					SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as total_skipped,
					AVG(execution_time) as avg_execution_time
				FROM %i WHERE post_id = %d",
				$this->table_name,
				$post_id
			)
			: $this->wpdb->prepare(
				"SELECT 
					COUNT(DISTINCT session_id) as total_sessions,
					COUNT(*) as total_operations,
					SUM(fixes_applied) as total_fixes,
					SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as total_errors,
					SUM(CASE WHEN status = 'skipped' THEN 1 ELSE 0 END) as total_skipped,
					AVG(execution_time) as avg_execution_time
				FROM %i",
				$this->table_name
			);

		$result = $this->wpdb->get_row( $sql, ARRAY_A );

		return $result ?: array(
			'total_sessions'     => 0,
			'total_operations'   => 0,
			'total_fixes'        => 0,
			'total_errors'       => 0,
			'total_skipped'      => 0,
			'avg_execution_time' => 0,
		);
	}

	/**
	 * Get recent activity
	 *
	 * @param int $limit
	 * @return array
	 */
	public function get_recent_activity( int $limit = 20 ): array {
		$sql = $this->wpdb->prepare(
			"SELECT h.*, p.post_title 
			FROM %i h 
			LEFT JOIN %i p ON h.post_id = p.ID 
			WHERE h.status = 'success' AND h.fixes_applied > 0
			ORDER BY h.created_at DESC 
			LIMIT %d",
			$this->table_name,
			$this->wpdb->posts,
			$limit
		);

		return $this->wpdb->get_results( $sql, ARRAY_A ) ?: array();
	}

	/**
	 * Clean old history
	 *
	 * @param int $days Days to keep
	 * @return int Number of rows deleted
	 */
	public function clean_old_history( int $days = 90 ): int {
		$sql = $this->wpdb->prepare(
			'DELETE FROM %i WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)',
			$this->table_name,
			$days
		);

		$this->wpdb->query( $sql );

		return $this->wpdb->rows_affected;
	}

	/**
	 * Check if table exists
	 *
	 * @return bool
	 */
	public function table_exists(): bool {
		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$this->table_name
			)
		);

		return $result === $this->table_name;
	}
}
