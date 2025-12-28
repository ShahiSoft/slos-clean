<?php
/**
 * Legal Document Repository
 *
 * Handles storage, retrieval, and management of legal documents
 * including Privacy Policies, Terms of Service, and Cookie Policies.
 *
 * @package     ShahiLegalFlowSuite
 * @subpackage  Database\Repositories
 * @version     4.1.0
 * @since       4.1.0
 */

namespace ShahiLegalFlowSuite\Database\Repositories;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Legal_Doc_Repository
 *
 * Provides CRUD operations for legal documents.
 *
 * @since 4.1.0
 */
class Legal_Doc_Repository extends Base_Repository {

	/**
	 * Table name for documents
	 */
	const TABLE_NAME = 'slos_legal_docs';

	/**
	 * Table name for versions
	 */
	const VERSIONS_TABLE = 'slos_legal_doc_versions';

	/**
	 * Versions table with prefix
	 *
	 * @var string
	 */
	protected $versions_table;

	/**
	 * Constructor
	 *
	 * @since 4.1.0
	 */
	public function __construct() {
		parent::__construct();
		$this->versions_table = $this->wpdb->prefix . self::VERSIONS_TABLE;
		$this->ensure_tables_exist();
	}

	/**
	 * Get table name without prefix
	 *
	 * @since 4.1.0
	 * @return string
	 */
	protected function get_table_name(): string {
		return self::TABLE_NAME;
	}

	/**
	 * Ensure required tables exist
	 *
	 * @since 4.1.0
	 * @return void
	 */
	protected function ensure_tables_exist(): void {
		if ( ! $this->tables_exist() ) {
			$this->create_tables();
		}
	}

	/**
	 * Check if tables exist
	 *
	 * @since 4.1.0
	 * @return bool
	 */
	protected function tables_exist(): bool {
		$docs_exists = $this->wpdb->get_var(
			$this->wpdb->prepare( 'SHOW TABLES LIKE %s', $this->table )
		) === $this->table;

		$versions_exists = $this->wpdb->get_var(
			$this->wpdb->prepare( 'SHOW TABLES LIKE %s', $this->versions_table )
		) === $this->versions_table;

		return $docs_exists && $versions_exists;
	}

	/**
	 * Create database tables
	 *
	 * @since 4.1.0
	 * @return bool
	 */
	public function create_tables(): bool {
		$charset_collate = $this->wpdb->get_charset_collate();

		// Documents table
		$docs_sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			doc_type VARCHAR(50) NOT NULL,
			title VARCHAR(255) NOT NULL,
			content LONGTEXT NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'draft',
			locale VARCHAR(10) NOT NULL DEFAULT 'en_US',
			version VARCHAR(20) NOT NULL DEFAULT '1.0',
			profile_version INT(10) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			created_by BIGINT(20) UNSIGNED DEFAULT NULL,
			updated_by BIGINT(20) UNSIGNED DEFAULT NULL,
			metadata LONGTEXT DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY idx_doc_type_locale (doc_type, locale),
			KEY idx_status (status),
			KEY idx_updated_at (updated_at),
			KEY idx_doc_type (doc_type)
		) {$charset_collate};";

		// Versions table
		$versions_sql = "CREATE TABLE IF NOT EXISTS {$this->versions_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			doc_id BIGINT(20) UNSIGNED NOT NULL,
			version_number VARCHAR(20) NOT NULL,
			content LONGTEXT NOT NULL,
			metadata LONGTEXT DEFAULT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			created_by BIGINT(20) UNSIGNED DEFAULT NULL,
			PRIMARY KEY (id),
			KEY idx_doc_id (doc_id),
			KEY idx_version_number (version_number),
			KEY idx_created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $docs_sql );
		dbDelta( $versions_sql );

		return $this->tables_exist();
	}

	/**
	 * Find document by type
	 *
	 * @since 4.1.0
	 * @param string $doc_type Document type (e.g., 'privacy_policy').
	 * @param string $locale   Locale code (default: current locale).
	 * @return object|null Document object or null
	 */
	public function find_by_type( string $doc_type, string $locale = '' ): ?object {
		if ( empty( $locale ) ) {
			$locale = get_locale();
		}

		// Try exact locale match first
		$doc = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE doc_type = %s AND locale = %s LIMIT 1",
				$doc_type,
				$locale
			)
		);

		// Fallback to en_US if not found
		if ( ! $doc && 'en_US' !== $locale ) {
			$doc = $this->wpdb->get_row(
				$this->wpdb->prepare(
					"SELECT * FROM {$this->table} WHERE doc_type = %s AND locale = 'en_US' LIMIT 1",
					$doc_type
				)
			);
		}

		if ( $doc && ! empty( $doc->metadata ) ) {
			$doc->metadata = json_decode( $doc->metadata, true );
		}

		return $doc;
	}

	/**
	 * Find document by ID
	 *
	 * @since 4.1.0
	 * @param int $id Document ID.
	 * @return object|null Document object or null
	 */
	public function find_by_id( int $id ): ?object {
		$doc = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d LIMIT 1",
				$id
			)
		);

		if ( $doc && ! empty( $doc->metadata ) ) {
			$doc->metadata = json_decode( $doc->metadata, true );
		}

		return $doc;
	}

	/**
	 * Get all documents
	 *
	 * @since 4.1.0
	 * @param string $status Filter by status (optional).
	 * @param string $locale Filter by locale (optional).
	 * @return array Array of document objects
	 */
	public function get_all( string $status = '', string $locale = '' ): array {
		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $status ) ) {
			$where[]  = 'status = %s';
			$params[] = $status;
		}

		if ( ! empty( $locale ) ) {
			$where[]  = 'locale = %s';
			$params[] = $locale;
		}

		$where_clause = implode( ' AND ', $where );

		if ( ! empty( $params ) ) {
			$query = $this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE {$where_clause} ORDER BY doc_type ASC, updated_at DESC",
				...$params
			);
		} else {
			$query = "SELECT * FROM {$this->table} WHERE {$where_clause} ORDER BY doc_type ASC, updated_at DESC";
		}

		$docs = $this->wpdb->get_results( $query );

		foreach ( $docs as &$doc ) {
			if ( ! empty( $doc->metadata ) ) {
				$doc->metadata = json_decode( $doc->metadata, true );
			}
		}

		return $docs;
	}

	/**
	 * Save document (create or update)
	 *
	 * @since 4.1.0
	 * @param array $data Document data.
	 * @return int|false Document ID on success, false on failure
	 */
	public function save( array $data ) {
		$now     = current_time( 'mysql' );
		$user_id = get_current_user_id();

		// Prepare metadata
		if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
			$data['metadata'] = wp_json_encode( $data['metadata'] );
		}

		// Check if document exists for this type/locale
		$existing = null;
		if ( ! empty( $data['id'] ) ) {
			$existing = $this->find_by_id( (int) $data['id'] );
		} elseif ( ! empty( $data['doc_type'] ) ) {
			$locale   = $data['locale'] ?? get_locale();
			$existing = $this->find_by_type( $data['doc_type'], $locale );
		}

		if ( $existing ) {
			// Update existing document
			$update_data = array(
				'title'           => $data['title'] ?? $existing->title,
				'content'         => $data['content'] ?? $existing->content,
				'status'          => $data['status'] ?? $existing->status,
				'version'         => $data['version'] ?? $this->increment_version( $existing->version ),
				'profile_version' => $data['profile_version'] ?? ( (int) get_option( 'slos_profile_version', 0 ) ),
				'updated_at'      => $now,
				'updated_by'      => $user_id,
				'metadata'        => $data['metadata'] ?? null,
			);

			$result = $this->wpdb->update(
				$this->table,
				$update_data,
				array( 'id' => $existing->id ),
				$this->get_format( $update_data ),
				array( '%d' )
			);

			if ( false === $result ) {
				error_log( 'Legal_Doc_Repository::save() update failed: ' . $this->wpdb->last_error );
				return false;
			}

			return (int) $existing->id;

		} else {
			// Create new document
			$insert_data = array(
				'doc_type'        => $data['doc_type'],
				'title'           => $data['title'] ?? $this->get_default_title( $data['doc_type'] ),
				'content'         => $data['content'] ?? '',
				'status'          => $data['status'] ?? 'draft',
				'locale'          => $data['locale'] ?? get_locale(),
				'version'         => $data['version'] ?? '1.0',
				'profile_version' => $data['profile_version'] ?? ( (int) get_option( 'slos_profile_version', 0 ) ),
				'created_at'      => $now,
				'updated_at'      => $now,
				'created_by'      => $user_id,
				'updated_by'      => $user_id,
				'metadata'        => $data['metadata'] ?? null,
			);

			$result = $this->wpdb->insert(
				$this->table,
				$insert_data,
				$this->get_format( $insert_data )
			);

			if ( false === $result ) {
				error_log( 'Legal_Doc_Repository::save() insert failed: ' . $this->wpdb->last_error );
				return false;
			}

			return (int) $this->wpdb->insert_id;
		}
	}

	/**
	 * Delete document
	 *
	 * @since 4.1.0
	 * @param int $id Document ID.
	 * @return bool True on success
	 */
	public function delete( int $id ): bool {
		// Delete versions first
		$this->wpdb->delete(
			$this->versions_table,
			array( 'doc_id' => $id ),
			array( '%d' )
		);

		// Delete document
		$result = $this->wpdb->delete(
			$this->table,
			array( 'id' => $id ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Update document status
	 *
	 * @since 4.1.0
	 * @param int    $id     Document ID.
	 * @param string $status New status.
	 * @return bool True on success
	 */
	public function update_status( int $id, string $status ): bool {
		$result = $this->wpdb->update(
			$this->table,
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
				'updated_by' => get_current_user_id(),
			),
			array( 'id' => $id ),
			array( '%s', '%s', '%d' ),
			array( '%d' )
		);

		return false !== $result;
	}

	/**
	 * Create version snapshot
	 *
	 * @since 4.1.0
	 * @param int   $doc_id    Document ID.
	 * @param array $data      Version data (content, metadata).
	 * @param bool  $auto_prune Whether to auto-prune old versions.
	 * @return int|false Version ID on success
	 */
	public function create_version( int $doc_id, array $data, bool $auto_prune = true ): mixed {
		$doc = $this->find_by_id( $doc_id );
		if ( ! $doc ) {
			return false;
		}

		$user_id = get_current_user_id();
		$user    = get_userdata( $user_id );

		$compliance_settings = get_option( 'slos_compliance_settings', array() );
		$gdpr_enabled        = false;
		if ( is_array( $compliance_settings ) && array_key_exists( 'gdpr', $compliance_settings ) ) {
			$gdpr_enabled = (bool) $compliance_settings['gdpr'];
		}

		// Build version metadata
		$metadata = array(
			'version'         => $data['version'] ?? $doc->version,
			'author'          => $user ? $user->display_name : 'System',
			'author_id'       => $user_id,
			'timestamp'       => current_time( 'c' ),
			'change_reason'   => $data['change_reason'] ?? 'Document updated',
			'profile_version' => (int) get_option( 'slos_profile_version', 0 ),
			'gdpr_enabled'    => $gdpr_enabled,
		);

		if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
			$metadata = array_merge( $metadata, $data['metadata'] );
		}

		$version_column = $this->version_column();

		$insert_data = array(
			'doc_id'            => $doc_id,
			$version_column     => $data['version'] ?? $doc->version,
			'content'           => $data['content'] ?? $doc->content,
			'metadata'          => wp_json_encode( $metadata ),
			'created_at'        => current_time( 'mysql' ),
			'created_by'        => $user_id,
		);

		$insert_formats = array( '%d', '%s', '%s', '%s', '%s', '%d' );

		$result = $this->wpdb->insert(
			$this->versions_table,
			$insert_data,
			$insert_formats
		);

		if ( false === $result ) {
			error_log( 'Legal_Doc_Repository::create_version() failed: ' . $this->wpdb->last_error );
			return false;
		}

		$version_id = (int) $this->wpdb->insert_id;

		// Auto-prune old versions (keep latest 10)
		if ( $auto_prune ) {
			$this->prune_versions( $doc_id, 10 );
		}

		return $version_id;
	}

	/**
	 * Get versions for a document
	 *
	 * @since 4.1.0
	 * @param int $doc_id Document ID.
	 * @param int $limit  Max versions to return.
	 * @return array Array of version objects
	 */
	public function get_versions( int $doc_id, int $limit = 20 ): array {
		$versions = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->versions_table} 
				 WHERE doc_id = %d 
				 ORDER BY created_at DESC 
				 LIMIT %d",
				$doc_id,
				$limit
			)
		);

		foreach ( $versions as &$version ) {
			if ( ! empty( $version->metadata ) ) {
				$version->metadata = json_decode( $version->metadata, true );
			}
		}

		return $versions;
	}

	/**
	 * Get specific version
	 *
	 * @since 4.1.0
	 * @param int $version_id Version ID.
	 * @return object|null Version object or null
	 */
	public function get_version( int $version_id ): ?object {
		$version = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->versions_table} WHERE id = %d LIMIT 1",
				$version_id
			)
		);

		if ( $version && ! empty( $version->metadata ) ) {
			$version->metadata = json_decode( $version->metadata, true );
		}

		return $version;
	}

	/**
	 * Prune old versions
	 *
	 * @since 4.1.0
	 * @param int $doc_id     Document ID.
	 * @param int $keep_count Number of versions to keep.
	 * @return int Number of versions deleted
	 */
	public function prune_versions( int $doc_id, int $keep_count = 10 ): int {
		// Get IDs of versions to delete
		$versions_to_delete = $this->wpdb->get_col(
			$this->wpdb->prepare(
				"SELECT id FROM {$this->versions_table} 
				 WHERE doc_id = %d 
				 ORDER BY created_at DESC 
				 LIMIT 999999 OFFSET %d",
				$doc_id,
				$keep_count
			)
		);

		if ( empty( $versions_to_delete ) ) {
			return 0;
		}

		$ids_string = implode( ',', array_map( 'intval', $versions_to_delete ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$this->wpdb->query( "DELETE FROM {$this->versions_table} WHERE id IN ({$ids_string})" );

		return count( $versions_to_delete );
	}

	/**
	 * Get outdated documents
	 *
	 * Documents where profile_version < current profile version.
	 *
	 * @since 4.1.0
	 * @return array Array of outdated document objects
	 */
	public function get_outdated(): array {
		$current_profile_version = (int) get_option( 'slos_profile_version', 0 );

		if ( 0 === $current_profile_version ) {
			return array();
		}

		$docs = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} 
				 WHERE profile_version < %d 
				 AND status IN ('draft', 'published')
				 ORDER BY doc_type ASC",
				$current_profile_version
			)
		);

		foreach ( $docs as &$doc ) {
			if ( ! empty( $doc->metadata ) ) {
				$doc->metadata = json_decode( $doc->metadata, true );
			}
		}

		return $docs;
	}

	/**
	 * Check if document is outdated
	 *
	 * @since 4.1.0
	 * @param int $doc_id Document ID.
	 * @return bool True if outdated
	 */
	public function is_outdated( int $doc_id ): bool {
		$doc = $this->find_by_id( $doc_id );
		if ( ! $doc ) {
			return false;
		}

		$current_profile_version = (int) get_option( 'slos_profile_version', 0 );

		return $current_profile_version > (int) $doc->profile_version;
	}

	/**
	 * Get document counts by status
	 *
	 * @since 4.1.0
	 * @return array Counts per status
	 */
	public function get_counts_by_status(): array {
		$results = $this->wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status"
		);

		$counts = array(
			'draft'     => 0,
			'published' => 0,
			'total'     => 0,
		);

		foreach ( $results as $row ) {
			$counts[ $row->status ] = (int) $row->count;
			$counts['total']       += (int) $row->count;
		}

		// Add outdated count
		$counts['outdated'] = count( $this->get_outdated() );

		return $counts;
	}

	/**
	 * Increment version number
	 *
	 * @since 4.1.0
	 * @param string $current_version Current version string.
	 * @param string $type            Increment type: 'minor' or 'major'.
	 * @return string New version string
	 */
	protected function increment_version( string $current_version, string $type = 'minor' ): string {
		$parts = explode( '.', $current_version );
		$major = (int) ( $parts[0] ?? 1 );
		$minor = (int) ( $parts[1] ?? 0 );

		if ( 'major' === $type ) {
			$major++;
			$minor = 0;
		} else {
			$minor++;
		}

		return "{$major}.{$minor}";
	}

	/**
	 * Get default title for document type
	 *
	 * @since 4.1.0
	 * @param string $doc_type Document type.
	 * @return string Default title
	 */
	protected function get_default_title( string $doc_type ): string {
		$titles = array(
			'privacy_policy'   => __( 'Privacy Policy', 'shahi-legalflowsuite' ),
			'terms_of_service' => __( 'Terms of Service', 'shahi-legalflowsuite' ),
			'cookie_policy'    => __( 'Cookie Policy', 'shahi-legalflowsuite' ),
			'gdpr_addendum'    => __( 'GDPR Addendum', 'shahi-legalflowsuite' ),
			'ccpa_notice'      => __( 'CCPA Privacy Notice', 'shahi-legalflowsuite' ),
			'dpa'              => __( 'Data Processing Agreement', 'shahi-legalflowsuite' ),
		);

		return $titles[ $doc_type ] ?? ucwords( str_replace( '_', ' ', $doc_type ) );
	}

	/**
	 * Get format array for wpdb queries
	 *
	 * @since 4.1.0
	 * @param array $data Data array.
	 * @return array Format array
	 */
	protected function get_format( array $data ): array {
		$format = array();

		foreach ( $data as $key => $value ) {
			if ( is_int( $value ) ) {
				$format[] = '%d';
			} elseif ( is_float( $value ) ) {
				$format[] = '%f';
			} else {
				$format[] = '%s';
			}
		}

		return $format;
	}

	/**
	 * Determine available version column for legacy tables.
	 *
	 * @since 4.2.0
	 * @return string Column name to use for version storage.
	 */
	private function version_column(): string {
		$column = 'version_number';

		$column_exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SHOW COLUMNS FROM {$this->versions_table} LIKE %s",
				$column
			)
		);

		if ( empty( $column_exists ) ) {
			// Fall back to legacy column name if present.
			$legacy_column = $this->wpdb->get_var(
				$this->wpdb->prepare(
					"SHOW COLUMNS FROM {$this->versions_table} LIKE %s",
					'Version'
				)
			);

			if ( ! empty( $legacy_column ) ) {
				return 'Version';
			}

			return 'version';
		}

		return $column;
	}
}
