<?php
/**
 * WP-CLI Commands for FixEngine Management
 *
 * @package SLOSLegalFlowSuite
 * @subpackage FixEngine
 * @since 3.1.2
 */

namespace SLOSLegalFlowSuite\CLI;

use ShahiLegalFlowSuite\FixEngine\FeatureFlags;
use ShahiLegalFlowSuite\FixEngine\Logger;
use ShahiLegalFlowSuite\FixEngine\CanonicalIds;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\AccessibilityScanner;
use ShahiLegalFlowSuite\Modules\AccessibilityScanner\FixEngine\Bootstrap as FixEngineBootstrap;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FixEngine WP-CLI Commands
 */
class FixEngineCommand {

	/**
	 * Get feature flags status
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine status
	 *
	 * @when after_wp_load
	 */
	public function status( $args, $assoc_args ) {
		$status = FeatureFlags::get_status();

		\WP_CLI::line( 'FixEngine Feature Flags Status' );
		\WP_CLI::line( '==============================' );
		\WP_CLI::line( '' );
		\WP_CLI::line( 'FixEngine Enabled:   ' . ( $status['fixengine_enabled'] ? 'YES' : 'NO' ) );
		\WP_CLI::line( 'Migration Locked:    ' . ( $status['migration_locked'] ? 'YES' : 'NO' ) );
		\WP_CLI::line( 'Legacy Fallback:     ' . ( $status['legacy_fallback'] ? 'ENABLED' : 'DISABLED' ) );
		\WP_CLI::line( 'Environment:         ' . $status['environment'] );
	}

	/**
	 * Enable FixEngine
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine enable
	 *
	 * @when after_wp_load
	 */
	public function enable( $args, $assoc_args ) {
		if ( FeatureFlags::enable_fixengine() ) {
			Logger::info( 'FixEngine enabled via WP-CLI' );
			\WP_CLI::success( 'FixEngine enabled' );
		} else {
			\WP_CLI::error( 'Failed to enable FixEngine' );
		}
	}

	/**
	 * Disable FixEngine
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine disable
	 *
	 * @when after_wp_load
	 */
	public function disable( $args, $assoc_args ) {
		if ( FeatureFlags::disable_fixengine() ) {
			Logger::warning( 'FixEngine disabled via WP-CLI' );
			\WP_CLI::success( 'FixEngine disabled' );
		} else {
			\WP_CLI::error( 'Failed to disable FixEngine' );
		}
	}

	/**
	 * Lock migrations
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine lock
	 *
	 * @when after_wp_load
	 */
	public function lock( $args, $assoc_args ) {
		if ( FeatureFlags::lock_migration() ) {
			Logger::warning( 'Migration locked via WP-CLI' );
			\WP_CLI::success( 'Migration locked' );
		} else {
			\WP_CLI::error( 'Failed to lock migration' );
		}
	}

	/**
	 * Unlock migrations
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine unlock
	 *
	 * @when after_wp_load
	 */
	public function unlock( $args, $assoc_args ) {
		if ( FeatureFlags::unlock_migration() ) {
			Logger::info( 'Migration unlocked via WP-CLI' );
			\WP_CLI::success( 'Migration unlocked' );
		} else {
			\WP_CLI::error( 'Failed to unlock migration' );
		}
	}

	/**
	 * Enable legacy fallback
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine fallback-enable
	 *
	 * @when after_wp_load
	 */
	public function fallback_enable( $args, $assoc_args ) {
		if ( FeatureFlags::enable_legacy_fallback() ) {
			Logger::warning( 'Legacy fallback enabled via WP-CLI' );
			\WP_CLI::success( 'Legacy fallback enabled' );
		} else {
			\WP_CLI::error( 'Failed to enable legacy fallback' );
		}
	}

	/**
	 * Disable legacy fallback
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine fallback-disable
	 *
	 * @when after_wp_load
	 */
	public function fallback_disable( $args, $assoc_args ) {
		if ( FeatureFlags::disable_legacy_fallback() ) {
			Logger::info( 'Legacy fallback disabled via WP-CLI' );
			\WP_CLI::success( 'Legacy fallback disabled' );
		} else {
			\WP_CLI::error( 'Failed to disable legacy fallback' );
		}
	}

	/**
	 * View logs
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<number>]
	 * : Number of log entries to show
	 * ---
	 * default: 50
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine logs
	 *     wp slos fixengine logs --limit=100
	 *
	 * @when after_wp_load
	 */
	public function logs( $args, $assoc_args ) {
		$limit = isset( $assoc_args['limit'] ) ? (int) $assoc_args['limit'] : 50;
		$logs  = Logger::get_logs( $limit );

		if ( empty( $logs ) ) {
			\WP_CLI::line( 'No logs found' );
			return;
		}

		\WP_CLI::line( 'FixEngine Logs' );
		\WP_CLI::line( '==============' );
		\WP_CLI::line( '' );

		foreach ( $logs as $log ) {
			$level_color = $log['level'] === 'error' || $log['level'] === 'critical' ? '%r' : '%y';
			\WP_CLI::line(
				\WP_CLI::colorize(
					sprintf(
						'[%s] %s%s%%n %s',
						$log['timestamp'],
						$level_color,
						strtoupper( $log['level'] ),
						$log['message']
					)
				)
			);

			if ( ! empty( $log['context'] ) ) {
				foreach ( $log['context'] as $key => $value ) {
					if ( ! in_array( $key, array( 'timestamp', 'user_id', 'request_id' ) ) ) {
						\WP_CLI::line( "  $key: $value" );
					}
				}
			}
			\WP_CLI::line( '' );
		}
	}

	/**
	 * Clear logs
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine clear-logs
	 *
	 * @when after_wp_load
	 */
	public function clear_logs( $args, $assoc_args ) {
		if ( Logger::clear_logs() ) {
			\WP_CLI::success( 'Logs cleared' );
		} else {
			\WP_CLI::error( 'Failed to clear logs' );
		}
	}

	/**
	 * Run database backup
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine backup
	 *
	 * @when after_wp_load
	 */
	public function backup( $args, $assoc_args ) {
		\WP_CLI::line( 'Starting database backup...' );

		$script = __DIR__ . '/../../scripts/backup-fixengine.sh';

		if ( file_exists( $script ) ) {
			\WP_CLI::launch( "bash $script" );
			\WP_CLI::success( 'Backup completed' );
		} else {
			\WP_CLI::error( 'Backup script not found' );
		}
	}

	/**
	 * Run ID canonicalization migration
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Preview changes without applying them
	 *
	 * [--rollback]
	 * : Rollback the migration
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine migrate-ids --dry-run
	 *     wp slos fixengine migrate-ids
	 *     wp slos fixengine migrate-ids --rollback
	 *
	 * @when after_wp_load
	 */
	public function migrate_ids( $args, $assoc_args ) {
		require_once __DIR__ . '/../Modules/AccessibilityScanner/FixEngine/Migrations/IdCanonicalizationMigration.php';

		$dry_run  = isset( $assoc_args['dry-run'] );
		$rollback = isset( $assoc_args['rollback'] );

		if ( $rollback ) {
			\WP_CLI::line( 'Rolling back ID canonicalization migration...' );
			$results = \ShahiLegalFlowSuite\FixEngine\Migrations\IdCanonicalizationMigration::rollback();
			\WP_CLI::success( 'Migration rolled back' );
		} else {
			if ( $dry_run ) {
				\WP_CLI::line( 'Running migration in DRY RUN mode...' );
			} else {
				\WP_CLI::line( 'Running ID canonicalization migration...' );
			}

			$results = \ShahiLegalFlowSuite\FixEngine\Migrations\IdCanonicalizationMigration::run( $dry_run );
		}

		\WP_CLI::line( '' );
		\WP_CLI::line( 'Migration Results:' );
		\WP_CLI::line( '==================' );
		\WP_CLI::line( 'Postmeta updated:   ' . $results['postmeta_updated'] );
		\WP_CLI::line( 'Options updated:    ' . $results['options_updated'] );
		\WP_CLI::line( 'History updated:    ' . $results['history_updated'] );
		\WP_CLI::line( 'Total changes:      ' . count( $results['changes'] ) );

		if ( $dry_run ) {
			\WP_CLI::warning( 'DRY RUN - No changes were made' );
		} else {
			\WP_CLI::success( 'Migration completed successfully' );
		}
	}

	/**
	 * Get ID migration report
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine migration-report
	 *
	 * @when after_wp_load
	 */
	public function migration_report( $args, $assoc_args ) {
		require_once __DIR__ . '/../Modules/AccessibilityScanner/FixEngine/Migrations/IdCanonicalizationMigration.php';

		$report = \ShahiLegalFlowSuite\FixEngine\Migrations\IdCanonicalizationMigration::get_report();
		\WP_CLI::line( $report );
	}

	/**
	 * Inventory auto-fixable canonical IDs vs implemented FixEngine fixers.
	 *
	 * Lists how many canonical auto-fixable IDs exist in CanonicalIds, how many
	 * concrete FixEngine fixers are currently registered, and which canonical
	 * IDs are missing a fixer implementation.
	 *
	 * This command is read-only and does not change any data.
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine inventory
	 *
	 * @when after_wp_load
	 */
	public function inventory( $args, $assoc_args ) {
		// Get canonical auto-fixable IDs from the registry.
		$auto_fixable_ids = CanonicalIds::get_auto_fixable();
		$auto_fixable_set = array_fill_keys( $auto_fixable_ids, true );
		$total_canonical  = count( $auto_fixable_ids );

		// Initialize FixEngine via Bootstrap and get all registered fixers.
		$engine = FixEngineBootstrap::get_engine();
		$engine->initialize();
		$fixer_array = $engine->get_fixers_array();

		$implemented_ids = array();
		foreach ( $fixer_array as $fixer ) {
			if ( ! isset( $fixer['id'] ) ) {
				continue;
			}
			$implemented_ids[] = $fixer['id'];
		}

		$implemented_set   = array_fill_keys( $implemented_ids, true );
		$total_implemented = count( $implemented_ids );

		// Compute missing canonical IDs that are marked auto-fixable but do
		// not yet have a corresponding FixEngine fixer.
		$missing = array();
		foreach ( $auto_fixable_ids as $id ) {
			if ( ! isset( $implemented_set[ $id ] ) ) {
				$missing[] = $id;
			}
		}

		\WP_CLI::line( 'FixEngine Canonical Fixer Inventory' );
		\WP_CLI::line( '====================================' );
		\WP_CLI::line( '' );
		\WP_CLI::line( sprintf( 'Canonical auto-fixable IDs:   %d', $total_canonical ) );
		\WP_CLI::line( sprintf( 'Implemented FixEngine fixers: %d', $total_implemented ) );
		\WP_CLI::line( sprintf( 'Missing implementations:       %d', count( $missing ) ) );
		\WP_CLI::line( '' );

		if ( empty( $missing ) ) {
			\WP_CLI::success( 'All canonical auto-fixable IDs have corresponding FixEngine fixers.' );
			return;
		}

		\WP_CLI::warning( 'Canonical auto-fixable IDs without a FixEngine fixer:' );
		foreach ( $missing as $id ) {
			$meta      = CanonicalIds::get( $id );
			$humanName = $meta && isset( $meta['name'] ) ? $meta['name'] : '(no name defined)';
			\WP_CLI::line( sprintf( '  - %s (%s)', $id, $humanName ) );
		}
	}

	/**
	 * Validate checker-to-fixer mapping against canonical FixEngine IDs.
	 *
	 * Scans the AccessibilityScanner::get_check_to_fixer_mapping() output and
	 * verifies that each target fixer ID is a valid canonical ID (or alias)
	 * according to CanonicalIds. Intended as a lightweight sanity check during
	 * development and QA.
	 *
	 * ## EXAMPLES
	 *
	 *     wp slos fixengine validate-mapping
	 *
	 * @when after_wp_load
	 */
	public function validate_mapping( $args, $assoc_args ) {
		$scanner = new AccessibilityScanner();
		$mapping = ( new \ReflectionClass( $scanner ) )->getMethod( 'get_check_to_fixer_mapping' );
		$mapping->setAccessible( true );
		$map = $mapping->invoke( $scanner );

		$invalid = array();
		foreach ( $map as $checker_id => $fixer_id ) {
			$canonical = CanonicalIds::canonicalize( $fixer_id );
			if ( null === $canonical ) {
				$invalid[] = array(
					'checker' => $checker_id,
					'fixer'   => $fixer_id,
				);
			}
		}

		if ( empty( $invalid ) ) {
			\WP_CLI::success( 'All checker-to-fixer mappings resolve to valid canonical FixEngine IDs.' );
			return;
		}

		\WP_CLI::warning( 'Found checker mappings that do not resolve to canonical FixEngine IDs:' );
		foreach ( $invalid as $entry ) {
			\WP_CLI::line( sprintf( '  Checker "%s" → Fixer "%s" (INVALID)', $entry['checker'], $entry['fixer'] ) );
		}

		\WP_CLI::error( sprintf( 'Total invalid mappings: %d', count( $invalid ) ) );
	}
}

// Register WP-CLI commands
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WP_CLI::add_command( 'slos fixengine', __NAMESPACE__ . '\\FixEngineCommand' );
}
