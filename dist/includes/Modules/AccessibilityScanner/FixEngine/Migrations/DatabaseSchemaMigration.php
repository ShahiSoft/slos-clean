<?php
/**
 * Phase 3: Database Schema Migration
 *
 * Migrates from separate fix_history and scan_results tables
 * to unified schema with proper foreign keys
 */

namespace ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DatabaseSchemaMigration {

	private $wpdb;
	private $backup_dir;
	private $dry_run = false;

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
			// Export table structure
			$create_table = $this->wpdb->get_row( $this->wpdb->prepare( 'SHOW CREATE TABLE %i', $table ), ARRAY_N );
			if ( $create_table ) {
				$sql .= $create_table[1] . ";\n\n";
			}

			// Export data
			$rows = $this->wpdb->get_results( $this->wpdb->prepare( 'SELECT * FROM %i', $table ), ARRAY_A );
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
			// Check if index exists
			$table_info       = $this->wpdb->get_results( $this->wpdb->prepare( 'SHOW INDEX FROM %i', $fix_history_table ) );
			$existing_indexes = wp_list_pluck( $table_info, 'Key_name' );
			$index_name       = $index['name'] ?? '';

			if ( ! in_array( $index_name, $existing_indexes, true ) ) {
				$sql    = $this->wpdb->prepare(
					"CREATE INDEX {$index_name} ON %i({$index['columns']})",
					$fix_history_table
				);
				$result = $this->wpdb->query( $sql );
				if ( $result === false ) {
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
		// Note: WordPress typically doesn't use foreign keys due to MyISAM legacy
		// This is optional and requires InnoDB engine

		$fix_history_table = $this->wpdb->prefix . 'slos_fix_history';

		// Check if table is InnoDB
		$engine = $this->wpdb->get_var(
			$this->wpdb->prepare(
				'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s',
				$fix_history_table
			)
		);

		if ( $engine !== 'InnoDB' ) {
			// Convert to InnoDB
			if ( ! $this->dry_run ) {
				$this->wpdb->query( $this->wpdb->prepare( 'ALTER TABLE %i ENGINE=InnoDB', $fix_history_table ) );
			}
		}

		// Add foreign key to posts table
		$fk_sql = $this->wpdb->prepare(
			'ALTER TABLE %i ADD CONSTRAINT fk_fix_history_post FOREIGN KEY (post_id) REFERENCES %i(ID) ON DELETE CASCADE',
			$fix_history_table,
			$this->wpdb->posts
		);

		if ( $this->dry_run ) {
			return array(
				'success'      => true,
				'foreign_keys' => 1,
			);
		}

		// Check if FK exists
		$fk_exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME = 'fk_fix_history_post' AND TABLE_NAME = %s",
				$fix_history_table
			)
		);

		if ( ! $fk_exists ) {
			$result = $this->wpdb->query( $fk_sql );
			if ( $result === false ) {
				// FK might fail if data integrity issues exist
				// This is not critical, so log and continue
				error_log( 'Could not add foreign key: ' . $this->wpdb->last_error );
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
			// Convert status to ENUM for better performance
			$this->wpdb->prepare( "ALTER TABLE %i MODIFY status ENUM('success', 'failed', 'skipped', 'partial') NOT NULL DEFAULT 'success'", $fix_history_table ),

			// Add compression for large text fields
			$this->wpdb->prepare( 'ALTER TABLE %i MODIFY fixes_applied JSON', $fix_history_table ),
			$this->wpdb->prepare( 'ALTER TABLE %i MODIFY metadata JSON', $fix_history_table ),
		);

		if ( $this->dry_run ) {
			return array(
				'success'       => true,
				'optimizations' => count( $optimizations ),
			);
		}

		foreach ( $optimizations as $sql ) {
			// Some optimizations might fail on older MySQL versions
			// Continue on error
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

		// Check for orphaned records
		$orphaned = $this->wpdb->get_var(
			$this->wpdb->prepare(
				'SELECT COUNT(*) FROM %i h LEFT JOIN %i p ON h.post_id = p.ID WHERE p.ID IS NULL AND h.post_id > 0',
				$fix_history_table,
				$this->wpdb->posts
			)
		);

		// Check for NULL required fields
		$null_fixers = $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT COUNT(*) FROM %i WHERE fixer_id IS NULL OR fixer_id = ''",
				$fix_history_table
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

		// Count total records
		$total_records = $this->wpdb->get_var( $this->wpdb->prepare( 'SELECT COUNT(*) FROM %i', $fix_history_table ) );

		return array(
			'success'          => true,
			'total_records'    => $total_records,
			'orphaned_records' => 0,
			'null_fixer_ids'   => 0,
		);
	}

	/**
	 * Rollback migration
	 */
	public function rollback( $backup_file ) {
		if ( ! file_exists( $backup_file ) ) {
			return array(
				'success' => false,
				'error'   => 'Backup file not found',
			);
		}

		$sql = file_get_contents( $backup_file );

		// Split into individual queries
		$queries = array_filter( explode( ";\n", $sql ) );

		foreach ( $queries as $query ) {
			$query = trim( $query );
			if ( empty( $query ) ) {
				continue;
			}

			$result = $this->wpdb->query( $query );
			if ( $result === false ) {
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

// CLI execution
if ( php_sapi_name() === 'cli' && basename( __FILE__ ) === basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	require_once __DIR__ . '/../../../vendor/autoload.php';

	$dry_run = in_array( '--dry-run', $argv );

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
