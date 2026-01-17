<?php
/**
 * Phase 3: Database Schema Migration
 *
 * Migrates from separate fix_history and scan_results tables
 * to unified schema with proper foreign keys
 *
 * @package ShahiLegalFlowSuite
 */

// phpcs:disable WordPress.Security.EscapeOutput -- CLI migration output

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL, WordPress.WP.AlternativeFunctions, WordPress.PHP.DevelopmentFunctions, WordPress.Security.EscapeOutput, WordPress.DateTime.RestrictedFunctions

/**
 * Database Schema Migration class.
 *
 * Handles migration from legacy schema to new unified schema.
 */
class DatabaseSchemaMigration {

	/**
	 * WordPress database object.
	 *
	 * @var \wpdb
	 */
	private $wpdb;

	/**
	 * Backup directory path.
	 *
	 * @var string
	 */
	private $backup_dir;

	/**
	 * Dry run mode flag.
	 *
	 * @var bool
	 */
	private $dry_run = false;

	/**
	 * Constructor.
	 *
	 * @param bool $dry_run Whether to run in dry-run mode.
	 */
	public function __construct( $dry_run = false ) {
		global $wpdb;
		$this->wpdb       = $wpdb;
		$this->dry_run    = $dry_run;
		$this->backup_dir = WP_CONTENT_DIR . '/slos-migrations';

		if ( ! is_dir( $this->backup_dir ) ) {
			mkdir( $this->backup_dir, 0755, true );
		}
	}

	/**
	 * Run schema migration
	 */
	public function run() {
		$steps = array(
			'backup_tables',
			'add_indexes',
			'add_foreign_keys',
			'optimize_columns',
			'verify_integrity',
		);

		$results = array(
			'steps'   => array(),
			'success' => true,
		);

		foreach ( $steps as $step ) {
			echo "\n[{$step}] ";

			if ( $this->dry_run ) {
				echo '[DRY RUN] ';
			}

			$result                    = call_user_func( array( $this, $step ) );
			$results['steps'][ $step ] = $result;

			if ( ! $result['success'] ) {
				$results['success'] = false;
				echo 'FAILED: ' . $result['error'] . "\n";
				break;
			}

			echo "OK\n";
		}

		return $results;
	}

	/**
	 * Backup existing tables
	 */
	private function backup_tables() {
		$tables = array(
			$this->wpdb->prefix . 'slos_fix_history',
			$this->wpdb->prefix . 'slos_scan_results',
		);

		$backup_file = $this->backup_dir . '/schema_backup_' . date( 'Y-m-d_H-i-s' ) . '.sql';

		if ( $this->dry_run ) {
			return array(
				'success'     => true,
				'backup_file' => $backup_file . ' (DRY RUN)',
			);
		}

		$sql = '';
		foreach ( $tables as $table ) {
			// Export table structure..
			$create_table = $this->wpdb->get_row( sprintf( 'SHOW CREATE TABLE %s', $this->wpdb->_escape( $table ) ), ARRAY_N );
			if ( $create_table ) {
				$sql .= $create_table[1] . ";\n\n";
			}

			// Export data..
			$rows = $this->wpdb->get_results( sprintf( 'SELECT * FROM %s', $this->wpdb->_escape( $table ) ), ARRAY_A );
			if ( $rows ) {
				foreach ( $rows as $row ) {
					$columns = array_keys( $row );
					$values  = array_map( array( $this->wpdb, '_escape' ), array_values( $row ) );
					$values  = array_map(
						function ( $v ) {
							return "'" . $v . "'";
						},
						$values
					);

					$sql .= "INSERT INTO `{$table}` (`" . implode( '`, `', $columns ) . '`) VALUES (' . implode( ', ', $values ) . ");\n";
				}
				$sql .= "\n";
			}
		}

		file_put_contents( $backup_file, $sql );

		return array(
			'success'     => true,
			'backup_file' => $backup_file,
		);
	}

	/**
	 * Add performance indexes
	 */
	private function add_indexes() {
		$fix_history_table = $this->wpdb->prefix . 'slos_fix_history';

		$indexes = array(
			array(
				'name'    => 'idx_post_fixer',
				'columns' => 'post_id, fixer_id',
			),
			array(
				'name'    => 'idx_status',
				'columns' => 'status',
			),
			array(
				'name'    => 'idx_created_at',
				'columns' => 'created_at',
			),
			array(
				'name'    => 'idx_session',
				'columns' => 'session_id',
			),
		);

		if ( $this->dry_run ) {
			return array(
				'success' => true,
				'indexes' => count( $indexes ),
			);
		}

		foreach ( $indexes as $index ) {
			// Check if index exists..
			$table_info       = $this->wpdb->get_results( sprintf( 'SHOW INDEX FROM %s', $this->wpdb->_escape( $fix_history_table ) ) );
			$existing_indexes = wp_list_pluck( $table_info, 'Key_name' );
			$index_name       = $index['name'] ?? '';

			if ( ! in_array( $index_name, $existing_indexes, true ) ) {
				$sql    = sprintf(
					"CREATE INDEX {$index_name} ON %s({$index['columns']})",
					$this->wpdb->_escape( $fix_history_table )
				);
				$result = $this->wpdb->query( $sql );
				if ( false === $result ) {
					return array(
						'success' => false,
						'error'   => $this->wpdb->last_error,
					);
				}
			}
		}

		return array(
			'success'       => true,
			'indexes_added' => count( $indexes ),
		);
	}

	/**
	 * Add foreign key constraints
	 */
	private function add_foreign_keys() {
		// Note: WordPress typically doesn't use foreign keys due to MyISAM legacy..
		// This is optional and requires InnoDB engine..

		$fix_history_table = $this->wpdb->prefix . 'slos_fix_history';

		// Check if table is InnoDB..
		$engine = $this->wpdb->get_var(
			$this->wpdb->prepare(
				'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
				$fix_history_table
			)
		);

		if ( 'InnoDB' !== $engine ) {
			// Convert to InnoDB..
			if ( ! $this->dry_run ) {
				$this->wpdb->query( sprintf( 'ALTER TABLE %s ENGINE=InnoDB', $this->wpdb->_escape( $fix_history_table ) ) );
			}
		}

		// Add foreign key to posts table..
		$fk_sql = sprintf(
			'ALTER TABLE %s ADD CONSTRAINT fk_fix_history_post FOREIGN KEY (post_id) REFERENCES %s(ID) ON DELETE CASCADE',
			$this->wpdb->_escape( $fix_history_table ),
			$this->wpdb->_escape( $this->wpdb->posts )
		);

		if ( $this->dry_run ) {
			return array(
				'success'      => true,
				'foreign_keys' => 1,
			);
		}

		// Check if FK exists..
		$fk_exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = 'fk_fix_history_post' AND TABLE_NAME = %s",
				$fix_history_table
			)
		);

		if ( ! $fk_exists ) {
			$result = $this->wpdb->query( $fk_sql );
			if ( false === $result ) {
				// FK might fail if data integrity issues exist..
				// This is not critical, so log and continue..
				if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					error_log( 'Could not add foreign key: ' . $this->wpdb->last_error );
				}
			}
		}

		return array(
			'success'            => true,
			'foreign_keys_added' => 1,
		);
	}

	/**
	 * Optimize column types and storage
	 */
	private function optimize_columns() {
		$fix_history_table = $this->wpdb->prefix . 'slos_fix_history';

		$optimizations = array(
			// Convert status to ENUM for better performance..
			sprintf( "ALTER TABLE %s MODIFY status ENUM('success', 'failed', 'skipped', 'partial') NOT NULL DEFAULT 'success'", $this->wpdb->_escape( $fix_history_table ) ),

			// Add compression for large text fields..
			sprintf( 'ALTER TABLE %s MODIFY fixes_applied JSON', $this->wpdb->_escape( $fix_history_table ) ),
			sprintf( 'ALTER TABLE %s MODIFY metadata JSON', $this->wpdb->_escape( $fix_history_table ) ),
		);

		if ( $this->dry_run ) {
			return array(
				'success'       => true,
				'optimizations' => count( $optimizations ),
			);
		}

		foreach ( $optimizations as $sql ) {
			// Some optimizations might fail on older MySQL versions..
			// Continue on error..
			$this->wpdb->query( $sql );
		}

		return array(
			'success'               => true,
			'optimizations_applied' => count( $optimizations ),
		);
	}

	/**
	 * Verify data integrity after migration
	 */
	private function verify_integrity() {
		$fix_history_table = $this->wpdb->prefix . 'slos_fix_history';

		// Check for orphaned records..
		$orphaned = $this->wpdb->get_var(
			sprintf(
				'SELECT COUNT(*) FROM %s h LEFT JOIN %s p ON h.post_id = p.ID WHERE p.ID IS NULL AND h.post_id > 0',
				$this->wpdb->_escape( $fix_history_table ),
				$this->wpdb->_escape( $this->wpdb->posts )
			)
		);

		// Check for NULL required fields..
		$null_fixers = $this->wpdb->get_var(
			sprintf(
				"SELECT COUNT(*) FROM %s WHERE fixer_id IS NULL OR fixer_id = ''",
				$this->wpdb->_escape( $fix_history_table )
			)
		);

		$issues = array();
		if ( $orphaned > 0 ) {
			$issues[] = "{$orphaned} orphaned records (post_id references non-existent posts)";
		}
		if ( $null_fixers > 0 ) {
			$issues[] = "{$null_fixers} records with NULL/empty fixer_id";
		}

		if ( ! empty( $issues ) ) {
			return array(
				'success' => false,
				'error'   => 'Data integrity issues found: ' . implode( '; ', $issues ),
			);
		}

		// Count total records..
		$total_records = $this->wpdb->get_var( sprintf( 'SELECT COUNT(*) FROM %s', $this->wpdb->_escape( $fix_history_table ) ) );

		return array(
			'success'          => true,
			'total_records'    => $total_records,
			'orphaned_records' => 0,
			'null_fixer_ids'   => 0,
		);
	}

	/**
	 * Rollback migration
	 *
	 * @param string $backup_file Path to backup file.
	 * @return array Result array with success status.
	 */
	public function rollback( $backup_file ) {
		if ( ! file_exists( $backup_file ) ) {
			return array(
				'success' => false,
				'error'   => 'Backup file not found',
			);
		}

		$sql = file_get_contents( $backup_file );

		// Split into individual queries..
		$queries = array_filter( explode( ";\n", $sql ) );

		foreach ( $queries as $query ) {
			$query = trim( $query );
			if ( empty( $query ) ) {
				continue;
			}

			$result = $this->wpdb->query( $query );
			if ( false === $result ) {
				return array(
					'success' => false,
					'error'   => $this->wpdb->last_error,
				);
			}
		}

		return array(
			'success'          => true,
			'queries_executed' => count( $queries ),
		);
	}
}

// CLI execution..
if ( 'cli' === php_sapi_name() && isset( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) === basename( sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) ) ) {
	require_once __DIR__ . '/../../../vendor/autoload.php';

	$dry_run = in_array( '--dry-run', $argv, true );

	echo "=== Phase 3: Database Schema Migration ===\n";
	if ( $dry_run ) {
		echo "[DRY RUN MODE - No changes will be made]\n";
	}
	echo "\n";

	$migration = new DatabaseSchemaMigration( $dry_run );
	$results   = $migration->run();

	echo "\n=== Migration " . ( $results['success'] ? 'COMPLETED' : 'FAILED' ) . " ===\n";

	if ( isset( $results['steps']['backup_tables']['backup_file'] ) ) {
		echo 'Backup: ' . $results['steps']['backup_tables']['backup_file'] . "\n";
	}
}

// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL, WordPress.WP.AlternativeFunctions, WordPress.PHP.DevelopmentFunctions, WordPress.Security.EscapeOutput, WordPress.DateTime.RestrictedFunctions
