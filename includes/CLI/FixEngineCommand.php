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

if (!defined('ABSPATH')) {
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
    public function status($args, $assoc_args) {
        $status = FeatureFlags::get_status();
        
        \WP_CLI::line('FixEngine Feature Flags Status');
        \WP_CLI::line('==============================');
        \WP_CLI::line('');
        \WP_CLI::line('FixEngine Enabled:   ' . ($status['fixengine_enabled'] ? 'YES' : 'NO'));
        \WP_CLI::line('Migration Locked:    ' . ($status['migration_locked'] ? 'YES' : 'NO'));
        \WP_CLI::line('Legacy Fallback:     ' . ($status['legacy_fallback'] ? 'ENABLED' : 'DISABLED'));
        \WP_CLI::line('Environment:         ' . $status['environment']);
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
    public function enable($args, $assoc_args) {
        if (FeatureFlags::enable_fixengine()) {
            Logger::info('FixEngine enabled via WP-CLI');
            \WP_CLI::success('FixEngine enabled');
        } else {
            \WP_CLI::error('Failed to enable FixEngine');
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
    public function disable($args, $assoc_args) {
        if (FeatureFlags::disable_fixengine()) {
            Logger::warning('FixEngine disabled via WP-CLI');
            \WP_CLI::success('FixEngine disabled');
        } else {
            \WP_CLI::error('Failed to disable FixEngine');
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
    public function lock($args, $assoc_args) {
        if (FeatureFlags::lock_migration()) {
            Logger::warning('Migration locked via WP-CLI');
            \WP_CLI::success('Migration locked');
        } else {
            \WP_CLI::error('Failed to lock migration');
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
    public function unlock($args, $assoc_args) {
        if (FeatureFlags::unlock_migration()) {
            Logger::info('Migration unlocked via WP-CLI');
            \WP_CLI::success('Migration unlocked');
        } else {
            \WP_CLI::error('Failed to unlock migration');
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
    public function fallback_enable($args, $assoc_args) {
        if (FeatureFlags::enable_legacy_fallback()) {
            Logger::warning('Legacy fallback enabled via WP-CLI');
            \WP_CLI::success('Legacy fallback enabled');
        } else {
            \WP_CLI::error('Failed to enable legacy fallback');
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
    public function fallback_disable($args, $assoc_args) {
        if (FeatureFlags::disable_legacy_fallback()) {
            Logger::info('Legacy fallback disabled via WP-CLI');
            \WP_CLI::success('Legacy fallback disabled');
        } else {
            \WP_CLI::error('Failed to disable legacy fallback');
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
    public function logs($args, $assoc_args) {
        $limit = isset($assoc_args['limit']) ? (int) $assoc_args['limit'] : 50;
        $logs = Logger::get_logs($limit);
        
        if (empty($logs)) {
            \WP_CLI::line('No logs found');
            return;
        }
        
        \WP_CLI::line('FixEngine Logs');
        \WP_CLI::line('==============');
        \WP_CLI::line('');
        
        foreach ($logs as $log) {
            $level_color = $log['level'] === 'error' || $log['level'] === 'critical' ? '%r' : '%y';
            \WP_CLI::line(
                \WP_CLI::colorize(
                    sprintf(
                        '[%s] %s%s%%n %s',
                        $log['timestamp'],
                        $level_color,
                        strtoupper($log['level']),
                        $log['message']
                    )
                )
            );
            
            if (!empty($log['context'])) {
                foreach ($log['context'] as $key => $value) {
                    if (!in_array($key, ['timestamp', 'user_id', 'request_id'])) {
                        \WP_CLI::line("  $key: $value");
                    }
                }
            }
            \WP_CLI::line('');
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
    public function clear_logs($args, $assoc_args) {
        if (Logger::clear_logs()) {
            \WP_CLI::success('Logs cleared');
        } else {
            \WP_CLI::error('Failed to clear logs');
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
    public function backup($args, $assoc_args) {
        \WP_CLI::line('Starting database backup...');
        
        $script = dirname(__FILE__) . '/../../scripts/backup-fixengine.sh';
        
        if (file_exists($script)) {
            \WP_CLI::launch("bash $script");
            \WP_CLI::success('Backup completed');
        } else {
            \WP_CLI::error('Backup script not found');
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
    public function migrate_ids($args, $assoc_args) {
        require_once dirname(__FILE__) . '/../Modules/AccessibilityScanner/FixEngine/Migrations/IdCanonicalizationMigration.php';
        
        $dry_run = isset($assoc_args['dry-run']);
        $rollback = isset($assoc_args['rollback']);
        
        if ($rollback) {
            \WP_CLI::line('Rolling back ID canonicalization migration...');
            $results = \ShahiLegalFlowSuite\FixEngine\Migrations\IdCanonicalizationMigration::rollback();
            \WP_CLI::success('Migration rolled back');
        } else {
            if ($dry_run) {
                \WP_CLI::line('Running migration in DRY RUN mode...');
            } else {
                \WP_CLI::line('Running ID canonicalization migration...');
            }
            
            $results = \ShahiLegalFlowSuite\FixEngine\Migrations\IdCanonicalizationMigration::run($dry_run);
        }
        
        \WP_CLI::line('');
        \WP_CLI::line('Migration Results:');
        \WP_CLI::line('==================');
        \WP_CLI::line('Postmeta updated:   ' . $results['postmeta_updated']);
        \WP_CLI::line('Options updated:    ' . $results['options_updated']);
        \WP_CLI::line('History updated:    ' . $results['history_updated']);
        \WP_CLI::line('Total changes:      ' . count($results['changes']));
        
        if ($dry_run) {
            \WP_CLI::warning('DRY RUN - No changes were made');
        } else {
            \WP_CLI::success('Migration completed successfully');
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
    public function migration_report($args, $assoc_args) {
        require_once dirname(__FILE__) . '/../Modules/AccessibilityScanner/FixEngine/Migrations/IdCanonicalizationMigration.php';
        
        $report = \ShahiLegalFlowSuite\FixEngine\Migrations\IdCanonicalizationMigration::get_report();
        \WP_CLI::line($report);
    }
}

// Register WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('slos fixengine', __NAMESPACE__ . '\\FixEngineCommand');
}
