<?php
/**
 * Database Migration Manager
 *
 * Handles database schema migrations for version updates.
 *
 * @package    ShahiLegalFlowSuite
 * @subpackage Database
 * @license    GPL-3.0+
 */

namespace ShahiLegalFlowSuite\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Migration Manager Class
 *
 * Manages database schema versioning and migrations.
 *
 * @since 1.0.0
 */
class MigrationManager {

	/**
	 * Current database version
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Database version option key
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const VERSION_OPTION = 'shahi_legalops_suite_db_version';

	/**
	 * Migrations directory path
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $migrations_dir;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->migrations_dir = SHAHI_LEGALFLOWSUITE_PLUGIN_DIR . 'includes/Database/Migrations/';
	}

	/**
	 * Check if migration is needed
	 *
	 * @since 1.0.0
	 * @return bool True if migration needed, false otherwise.
	 */
	public function needs_migration() {
		$current_version = get_option( self::VERSION_OPTION, '0.0.0' );
		return version_compare( $current_version, self::DB_VERSION, '<' );
	}

	/**
	 * Run pending migrations
	 *
	 * @since 1.0.0
	 * @return bool True on success, false on failure.
	 */
	public function run_migrations() {
		$current_version = get_option( self::VERSION_OPTION, '0.0.0' );

		if ( ! $this->needs_migration() ) {
			return true;
		}

		// Get all migration files
		$migrations = $this->get_pending_migrations( $current_version );

		if ( empty( $migrations ) ) {
			// No migrations needed, just update version
			return $this->update_version();
		}

		// Run each migration
		foreach ( $migrations as $migration ) {
			if ( ! $this->run_migration_file( $migration ) ) {
				// Migration failed, log error
				error_log( "ShahiLegalFlowSuite: Migration failed - {$migration['file']}" );
				return false;
			}
		}

		// Update database version
		return $this->update_version();
	}

	/**
	 * Get pending migrations
	 *
	 * @since 1.0.0
	 * @param string $current_version Current database version.
	 * @return array Array of pending migration files.
	 */
	private function get_pending_migrations( $current_version ) {
		$migrations = array();

		if ( ! is_dir( $this->migrations_dir ) ) {
			return $migrations;
		}

		$files = glob( $this->migrations_dir . 'migration_*.php' );

		if ( empty( $files ) ) {
			return $migrations;
		}

		foreach ( $files as $file ) {
			$filename = basename( $file );

			// Extract version from filename (e.g., migration_1_1_0.php -> 1.1.0)
			if ( preg_match( '/migration_(\d+)_(\d+)_(\d+)\.php/', $filename, $matches ) ) {
				$version = "{$matches[1]}.{$matches[2]}.{$matches[3]}";

				// Only include migrations newer than current version
				if ( version_compare( $version, $current_version, '>' ) &&
					version_compare( $version, self::DB_VERSION, '<=' ) ) {
					$migrations[] = array(
						'version' => $version,
						'file'    => $file,
					);
				}
			}
		}

		// Sort migrations by version
		usort(
			$migrations,
			function ( $a, $b ) {
				return version_compare( $a['version'], $b['version'] );
			}
		);

		return $migrations;
	}

	/**
	 * Run a single migration file
	 *
	 * @since 1.0.0
	 * @param array $migration Migration info array.
	 * @return bool True on success, false on failure.
	 */
	private function run_migration_file( $migration ) {
		global $wpdb;

		if ( ! file_exists( $migration['file'] ) ) {
			return false;
		}

		// Include the migration file
		require_once $migration['file'];

		// Migration files should define an up() function
		if ( ! function_exists( 'up' ) ) {
			error_log( "ShahiLegalFlowSuite: Migration file missing up() function - {$migration['file']}" );
			return false;
		}

		// Run the migration
		try {
			up( $wpdb );
			return true;
		} catch ( \Exception $e ) {
			error_log( 'ShahiLegalFlowSuite: Migration exception - ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Update database version
	 *
	 * @since 1.0.0
	 * @return bool True on success.
	 */
	private function update_version() {
		return update_option( self::VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Get current database version
	 *
	 * @since 1.0.0
	 * @return string Current database version.
	 */
	public function get_current_version() {
		return get_option( self::VERSION_OPTION, '0.0.0' );
	}

	/**
	 * Get target database version
	 *
	 * @since 1.0.0
	 * @return string Target database version.
	 */
	public function get_target_version() {
		return self::DB_VERSION;
	}

	/**
	 * Rollback to a specific version
	 *
	 * @since 1.0.0
	 * @param string $version Version to rollback to.
	 * @return bool True on success, false on failure.
	 */
	public function rollback( $version ) {
		$current_version = $this->get_current_version();

		if ( version_compare( $version, $current_version, '>=' ) ) {
			// Cannot rollback to same or newer version
			return false;
		}

		// Get migrations between target and current
		$files     = glob( $this->migrations_dir . 'migration_*.php' );
		$rollbacks = array();

		foreach ( $files as $file ) {
			$filename = basename( $file );

			if ( preg_match( '/migration_(\d+)_(\d+)_(\d+)\.php/', $filename, $matches ) ) {
				$migration_version = "{$matches[1]}.{$matches[2]}.{$matches[3]}";

				// Include migrations newer than target but <= current
				if ( version_compare( $migration_version, $version, '>' ) &&
					version_compare( $migration_version, $current_version, '<=' ) ) {
					$rollbacks[] = array(
						'version' => $migration_version,
						'file'    => $file,
					);
				}
			}
		}

		// Sort in reverse order for rollback
		usort(
			$rollbacks,
			function ( $a, $b ) {
				return version_compare( $b['version'], $a['version'] );
			}
		);

		// Run rollbacks
		foreach ( $rollbacks as $rollback ) {
			if ( ! $this->run_rollback_file( $rollback ) ) {
				error_log( "ShahiLegalFlowSuite: Rollback failed - {$rollback['file']}" );
				return false;
			}
		}

		// Update version
		return update_option( self::VERSION_OPTION, $version );
	}

	/**
	 * Run a rollback migration
	 *
	 * @since 1.0.0
	 * @param array $migration Migration info array.
	 * @return bool True on success, false on failure.
	 */
	private function run_rollback_file( $migration ) {
		global $wpdb;

		if ( ! file_exists( $migration['file'] ) ) {
			return false;
		}

		// Include the migration file
		require_once $migration['file'];

		// Migration files can optionally define a down() function
		if ( ! function_exists( 'down' ) ) {
			// If no down() function, just continue
			return true;
		}

		// Run the rollback
		try {
			down( $wpdb );
			return true;
		} catch ( \Exception $e ) {
			error_log( 'ShahiLegalFlowSuite: Rollback exception - ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get migration history
	 *
	 * @since 1.0.0
	 * @return array Array of migration information.
	 */
	public function get_migration_history() {
		$current_version = $this->get_current_version();
		$files           = glob( $this->migrations_dir . 'migration_*.php' );
		$history         = array();

		if ( empty( $files ) ) {
			return $history;
		}

		foreach ( $files as $file ) {
			$filename = basename( $file );

			if ( preg_match( '/migration_(\d+)_(\d+)_(\d+)\.php/', $filename, $matches ) ) {
				$version = "{$matches[1]}.{$matches[2]}.{$matches[3]}";
				$applied = version_compare( $version, $current_version, '<=' );

				$history[] = array(
					'version' => $version,
					'file'    => $filename,
					'applied' => $applied,
					'path'    => $file,
				);
			}
		}

		// Sort by version
		usort(
			$history,
			function ( $a, $b ) {
				return version_compare( $a['version'], $b['version'] );
			}
		);

		return $history;
	}
}

